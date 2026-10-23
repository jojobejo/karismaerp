<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_PortalSsoClients extends CI_Controller
{
    private $currentFormToken;
    private $portalSsoLocalConfigPath;
    public function __construct()
    {
        parent::__construct();
        $this->portalSsoLocalConfigPath = APPPATH . 'config/portal_sso_local.php';
        if (is_file($this->portalSsoLocalConfigPath)) $this->config->load('portal_sso_local', true);
        $this->load->model('M_PortalAccess', 'portalAccess');
        $this->load->model('M_PortalSso', 'portalSso');
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }
        if (!$this->session->userdata('is_admin_dashboard')) show_error('Akses hanya untuk administrator.', 403);
    }

    private function verify_form_token()
    {
        $expected = (string)$this->session->userdata('portal_sso_admin_csrf');
        $provided = (string)$this->input->post('form_token', true);
        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) show_error('Token formulir tidak valid. Muat ulang halaman.', 403);
        $this->currentFormToken = bin2hex(random_bytes(32));
        $this->session->set_userdata('portal_sso_admin_csrf', $this->currentFormToken);
    }

    private function respond($success, $message, $data = [])
    {
        if ($this->input->is_ajax_request()) {
            $data['form_token'] = $this->currentFormToken;
            return $this->output->set_content_type('application/json', 'utf-8')->set_output(json_encode([
                'status' => (bool)$success,
                'message' => $message,
                'data' => $data,
            ], JSON_UNESCAPED_SLASHES));
        }
        $this->session->set_flashdata('portal_sso_notice', $message);
        if (isset($data['secret'])) $this->session->set_flashdata('portal_sso_secret_once', ['app_code' => $data['app_code'], 'secret' => $data['secret']]);
        redirect('admin/portal-sso-clients');
    }

    private function secret_encryption_key()
    {
        $key = getenv('PORTAL_SSO_ENCRYPTION_KEY');
        if ($key === false || $key === '') $key = (string)$this->config->item('encryption_key', 'portal_sso_local');
        if ($key === '') $key = (string)$this->config->item('encryption_key');
        if (strlen($key) < 32 || !function_exists('openssl_encrypt')) return false;
        return hash('sha256', $key, true);
    }

    private function has_external_encryption_key()
    {
        $environmentKey = getenv('PORTAL_SSO_ENCRYPTION_KEY');
        if ($environmentKey !== false && $environmentKey !== '') return true;
        $applicationKey = (string)$this->config->item('encryption_key');
        return $applicationKey !== '';
    }

    public function generate_encryption_key()
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();

        if ($this->secret_encryption_key() !== false) {
            return $this->respond(false, 'Kunci enkripsi sudah aktif. Kunci tidak dapat diganti dari halaman ini karena secret yang ada bergantung padanya.');
        }
        if ($this->has_external_encryption_key()) {
            return $this->respond(false, 'PORTAL_SSO_ENCRYPTION_KEY atau encryption_key sudah terisi tetapi tidak valid. Periksa konfigurasi minimal 32 karakter terlebih dahulu.');
        }
        if (!function_exists('random_bytes') || !function_exists('openssl_encrypt')) {
            return $this->respond(false, 'Server memerlukan random_bytes dan ekstensi OpenSSL untuk membuat kunci.');
        }
        if (is_file($this->portalSsoLocalConfigPath)) {
            return $this->respond(false, 'Berkas kunci lokal sudah ada tetapi belum dapat digunakan. Periksa konfigurasi tersebut secara manual; berkas tidak akan ditimpa.');
        }

        $key = bin2hex(random_bytes(32));
        $contents = "<?php\ndefined('BASEPATH') or exit('No direct script access allowed');\n\$config['encryption_key'] = " . var_export($key, true) . ";\n";
        $handle = @fopen($this->portalSsoLocalConfigPath, 'x');
        if ($handle === false) {
            return $this->respond(false, 'Kunci gagal disimpan. Pastikan folder application/config dapat ditulis oleh proses PHP.');
        }
        $written = fwrite($handle, $contents);
        fflush($handle);
        if (function_exists('fsync')) fsync($handle);
        fclose($handle);
        if ($written !== strlen($contents)) {
            @unlink($this->portalSsoLocalConfigPath);
            return $this->respond(false, 'Penyimpanan kunci tidak lengkap. Pastikan folder application/config dapat ditulis dan coba kembali.');
        }
        @chmod($this->portalSsoLocalConfigPath, 0600);

        $this->config->load('portal_sso_local', true);
        return $this->respond(true, 'Kunci enkripsi berhasil dibuat dan disimpan permanen untuk server ini. Cadangkan berkas konfigurasi lokal beserta backup database secara aman.', ['encryption_key_configured' => true]);
    }

    private function encrypt_secret($secret)
    {
        $key = $this->secret_encryption_key();
        if ($key === false) return false;
        $nonce = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($secret, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);
        return $ciphertext === false ? false : base64_encode($nonce . $tag . $ciphertext);
    }

    private function decrypt_secret($encrypted)
    {
        $key = $this->secret_encryption_key();
        $payload = base64_decode((string)$encrypted, true);
        if ($key === false || $payload === false || strlen($payload) < 29) return false;
        $secret = openssl_decrypt(substr($payload, 28), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr($payload, 0, 12), substr($payload, 12, 16));
        return $secret === false ? false : $secret;
    }

    public function index()
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) show_error('Tabel integrasi SSO belum diterapkan. Lihat docs/database/portal_sso_20261022.sql.', 500);
        if (!$this->db->field_exists('client_secret_encrypted', 'tb_portal_sso_client')) show_error('Migration tampilan ulang secret belum diterapkan. Jalankan docs/database/portal_sso_secret_reveal_20261022.sql.', 500);
        if (!$this->session->userdata('portal_sso_admin_csrf')) $this->session->set_userdata('portal_sso_admin_csrf', bin2hex(random_bytes(32)));
        $data = [
            'page_title' => 'Registrasi Klien SSO Portal',
            'apps' => $this->portalAccess->apps(),
            'clients' => $this->portalSso->clients(),
            'form_token' => $this->session->userdata('portal_sso_admin_csrf'),
            'notice' => $this->session->flashdata('portal_sso_notice'),
            'secret_once' => $this->session->flashdata('portal_sso_secret_once'),
            'sso_key_configured' => $this->secret_encryption_key() !== false,
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/portal_sso_clients.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function save()
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        $appCode = trim((string)$this->input->post('app_code', true));
        $redirectUri = trim((string)$this->input->post('redirect_uri', true));
        $apps = $this->portalAccess->apps();
        $parts = parse_url($redirectUri);
        if (!isset($apps[$appCode]) || !$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])) {
            return $this->respond(false, 'Aplikasi harus dipilih dan callback harus URL HTTPS yang valid tanpa kredensial atau fragment.');
        }
        $secret = bin2hex(random_bytes(32));
        $encrypted = $this->encrypt_secret($secret);
        if ($encrypted === false) return $this->respond(false, 'Kunci enkripsi belum dikonfigurasi. Atur PORTAL_SSO_ENCRYPTION_KEY minimal 32 karakter sebelum membuat secret.');
        $saved = $this->portalSso->save_client($appCode, $redirectUri, hash('sha256', $secret), $encrypted, (int)$this->session->userdata('id'));
        return $this->respond($saved, $saved ? 'Klien berhasil disimpan. Secret tersimpan terenkripsi dan dapat dibuka melalui PIN admin.' : 'Klien gagal disimpan. Pastikan migrasi database telah diterapkan.', $saved ? ['app_code' => $appCode, 'redirect_uri' => $redirectUri, 'secret' => $secret] : []);
    }

    public function rotate($appCode)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        $secret = bin2hex(random_bytes(32));
        $encrypted = $this->encrypt_secret($secret);
        if ($encrypted === false) return $this->respond(false, 'Kunci enkripsi belum dikonfigurasi. Atur PORTAL_SSO_ENCRYPTION_KEY minimal 32 karakter sebelum merotasi secret.');
        $saved = $this->portalSso->set_client_secret($appCode, hash('sha256', $secret), $encrypted, (int)$this->session->userdata('id'));
        return $this->respond($saved, $saved ? 'Secret klien berhasil dirotasi. Perbarui secret pada server klien.' : 'Rotasi secret gagal.', $saved ? ['app_code' => $appCode, 'secret' => $secret] : []);
    }

    public function reveal($appCode)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        $now = time();
        $lockUntil = (int)$this->session->userdata('portal_sso_pin_lock_until');
        if ($lockUntil > $now) return $this->respond(false, 'Terlalu banyak percobaan PIN. Coba kembali beberapa menit lagi.');
        $pin = (string)$this->input->post('pin', true);
        $expectedPin = getenv('PORTAL_SSO_KEY_PIN');
        if ($expectedPin === false || $expectedPin === '') $expectedPin = '898989';
        if (!hash_equals(hash('sha256', (string)$expectedPin), hash('sha256', $pin))) {
            $attempts = (int)$this->session->userdata('portal_sso_pin_attempts') + 1;
            $this->session->set_userdata('portal_sso_pin_attempts', $attempts);
            if ($attempts >= 5) {
                $this->session->set_userdata('portal_sso_pin_lock_until', $now + 300);
                $this->session->set_userdata('portal_sso_pin_attempts', 0);
            }
            return $this->respond(false, 'PIN salah.');
        }
        $this->session->unset_userdata(['portal_sso_pin_attempts', 'portal_sso_pin_lock_until']);
        $row = $this->portalSso->client_secret_encrypted($appCode);
        if (!$row || empty($row['client_secret_encrypted'])) return $this->respond(false, 'Secret lama tidak dapat dipulihkan. Rotasi secret terlebih dahulu untuk membuat secret yang dapat ditampilkan.');
        $secret = $this->decrypt_secret($row['client_secret_encrypted']);
        if ($secret === false) return $this->respond(false, 'Secret tidak dapat dibuka. Pastikan PORTAL_SSO_ENCRYPTION_KEY sama dengan kunci saat secret dibuat.');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Pragma: no-cache');
        log_message('info', 'Secret integrasi SSO ditampilkan oleh admin id ' . (int)$this->session->userdata('id') . ' untuk app_code ' . preg_replace('/[^A-Za-z0-9_-]/', '', $appCode));
        return $this->respond(true, 'Secret berhasil dibuka. Jangan bagikan secret ini.', ['app_code' => $appCode, 'secret' => $secret]);
    }

    public function toggle($appCode)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        $enabled = (int)$this->input->post('enabled') === 1;
        $saved = $this->portalSso->set_client_enabled($appCode, $enabled, (int)$this->session->userdata('id'));
        return $this->respond($saved, $saved ? 'Status integrasi klien diperbarui.' : 'Status integrasi gagal diperbarui.');
    }
}
