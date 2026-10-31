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
        $this->load->model('M_PortalSsoClientAccounts', 'clientAccounts');
        $this->load->model('M_PortalSsoLoginHistory', 'loginHistory');
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

    private function valid_https_uri($uri)
    {
        $parts = parse_url((string)$uri);
        return $parts && strtolower($parts['scheme'] ?? '') === 'https' && !empty($parts['host'])
            && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['fragment']);
    }

    public function index()
    {
        if (!$this->db->table_exists('tb_portal_sso_client')) show_error('Tabel integrasi SSO belum diterapkan. Lihat docs/database/portal_sso_20261022.sql.', 500);
        if (!$this->db->field_exists('client_secret_encrypted', 'tb_portal_sso_client')) show_error('Migration tampilan ulang secret belum diterapkan. Jalankan docs/database/portal_sso_secret_reveal_20261022.sql.', 500);
        if (!$this->db->field_exists('launch_uri', 'tb_portal_sso_client')) show_error('Kolom URL peluncuran belum diterapkan. Jalankan docs/database/portal_sso_launch_uri_20261024.sql.', 500);
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

    public function accounts_index()
    {
        if ($this->input->method(true) !== 'GET') show_error('Metode tidak diizinkan.', 405);
        $apps = $this->portalAccess->apps();
        $clients = $this->portalSso->clients();
        foreach ($clients as &$client) {
            $client['display_name'] = $apps[$client['app_code']]['name'] ?? $client['app_code'];
            $client['account_count'] = $this->clientAccounts->count_for_client($client['app_code']);
        }
        unset($client);
        $data = [
            'page_title' => 'Daftar Akun Klien',
            'clients' => $clients,
            'account_table_ready' => $this->clientAccounts->table_ready(),
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/portal_sso_client_accounts_index.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function login_history()
    {
        if ($this->input->method(true) !== 'GET') show_error('Metode tidak diizinkan.', 405);
        $apps = $this->portalAccess->apps();
        $usersByKey = [];
        foreach ($this->portalAccess->users() as $user) $usersByKey[$user['auth_source'] . ':' . (int)$user['id']] = $user;
        $history = $this->loginHistory->recent(500);
        foreach ($history as &$entry) {
            $entry['app_name'] = $apps[$entry['app_code']]['name'] ?? $entry['app_code'];
            $userKey = $entry['auth_source'] . ':' . (int)$entry['user_id'];
            $entry['portal_user'] = $usersByKey[$userKey]['display_name'] ?? $entry['auth_source'] . ' #' . (int)$entry['user_id'];
        }
        unset($entry);
        $data = [
            'page_title' => 'Histori Login Akun Klien',
            'history_ready' => $this->loginHistory->table_ready(),
            'history' => $history,
            'summary' => $this->loginHistory->summary(),
            'apps' => $apps,
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/portal_sso_login_history.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function accounts($appCode)
    {
        if ($this->input->method(true) !== 'GET') show_error('Metode tidak diizinkan.', 405);
        $client = $this->portalSso->client_by_code($appCode);
        if (!$client) show_404();
        if (!$this->session->userdata('portal_sso_admin_csrf')) $this->session->set_userdata('portal_sso_admin_csrf', bin2hex(random_bytes(32)));
        $apps = $this->portalAccess->apps();
        $allUsers = $this->portalAccess->users();
        $usersByKey = [];
        $picUsersByUsername = [];
        foreach ($allUsers as $user) {
            $usersByKey[$user['auth_source'] . ':' . (int)$user['id']] = $user;
            $usernameKey = function_exists('mb_strtolower')
                ? mb_strtolower(trim((string)$user['username']), 'UTF-8')
                : strtolower(trim((string)$user['username']));
            if ($usernameKey === '') $usernameKey = $user['auth_source'] . ':' . (int)$user['id'];
            if (!isset($picUsersByUsername[$usernameKey])) $picUsersByUsername[$usernameKey] = $user;
        }
        $accounts = $this->clientAccounts->all_for_client($appCode);
        foreach ($accounts as &$account) {
            $picKey = (string)$account['pic_auth_source'] . ':' . (int)$account['pic_user_id'];
            $account['pic'] = $usersByKey[$picKey] ?? null;
        }
        unset($account);
        $pendingImport = $this->session->userdata('portal_sso_pending_client_import');
        if ($pendingImport && ((int)$pendingImport['expires_at'] < time() || !is_file($pendingImport['path']))) {
            if (!empty($pendingImport['path']) && is_file($pendingImport['path'])) @unlink($pendingImport['path']);
            $this->session->unset_userdata('portal_sso_pending_client_import');
            $pendingImport = null;
        }
        $data = [
            'page_title' => 'Daftar Akun Klien - ' . ($apps[$appCode]['name'] ?? $appCode),
            'client' => $client,
            'app' => $apps[$appCode] ?? ['name' => $appCode],
            'accounts' => $accounts,
            'users' => array_values($picUsersByUsername),
            'form_token' => $this->session->userdata('portal_sso_admin_csrf'),
            'notice' => $this->session->flashdata('portal_sso_accounts_notice'),
            'account_table_ready' => $this->clientAccounts->table_ready(),
            'encryption_key_configured' => $this->secret_encryption_key() !== false,
            'pending_import' => $pendingImport && $pendingImport['app_code'] === $appCode ? $pendingImport : null,
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/portal_sso_client_accounts.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    private function account_notice($appCode, $message)
    {
        $this->session->set_flashdata('portal_sso_accounts_notice', $message);
        redirect('admin/portal-sso-clients/accounts/' . rawurlencode($appCode));
    }

    private function read_csv_row($handle, $delimiter)
    {
        return fgetcsv($handle, 0, $delimiter, '"', '');
    }

    public function upload_accounts($appCode)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        if (!$this->portalSso->client_by_code($appCode)) show_404();
        if (!$this->clientAccounts->table_ready()) return $this->account_notice($appCode, 'Tabel akun klien belum tersedia. Terapkan migration database terlebih dahulu.');
        foreach (glob(rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'karisma_portal_import_*.csv') ?: [] as $temporaryFile) {
            if (is_file($temporaryFile) && filemtime($temporaryFile) < time() - 1800) @unlink($temporaryFile);
        }
        if (empty($_FILES['account_csv']) || (int)$_FILES['account_csv']['error'] !== UPLOAD_ERR_OK) return $this->account_notice($appCode, 'Pilih berkas CSV yang valid untuk diunggah.');
        $upload = $_FILES['account_csv'];
        if ((int)$upload['size'] > 5 * 1024 * 1024 || strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION)) !== 'csv') return $this->account_notice($appCode, 'Berkas harus berformat CSV dengan ukuran maksimal 5 MB.');
        $delimiter = (string)$this->input->post('delimiter', true);
        if (!in_array($delimiter, [',', ';'], true)) $delimiter = ',';
        $token = bin2hex(random_bytes(24));
        $path = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'karisma_portal_import_' . hash('sha256', session_id() . $token) . '.csv';
        if (!is_uploaded_file($upload['tmp_name']) || !move_uploaded_file($upload['tmp_name'], $path)) return $this->account_notice($appCode, 'Berkas CSV gagal disimpan sementara.');
        @chmod($path, 0600);
        $handle = fopen($path, 'rb');
        $headers = $handle ? $this->read_csv_row($handle, $delimiter) : false;
        if (is_resource($handle)) fclose($handle);
        if (!is_array($headers) || count($headers) < 2) {
            @unlink($path);
            return $this->account_notice($appCode, 'CSV tidak memiliki baris header yang valid.');
        }
        foreach ($headers as &$header) $header = trim(preg_replace('/^\xEF\xBB\xBF/', '', (string)$header));
        unset($header);
        $oldPending = $this->session->userdata('portal_sso_pending_client_import');
        if ($oldPending && !empty($oldPending['path']) && is_file($oldPending['path'])) @unlink($oldPending['path']);
        $this->session->set_userdata('portal_sso_pending_client_import', [
            'token' => $token,
            'path' => $path,
            'app_code' => $appCode,
            'headers' => $headers,
            'delimiter' => $delimiter,
            'expires_at' => time() + 1800,
        ]);
        return $this->account_notice($appCode, 'Header CSV berhasil dibaca. Pilih kolom username dan password untuk melanjutkan impor.');
    }

    public function import_accounts($appCode)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        $pending = $this->session->userdata('portal_sso_pending_client_import');
        if (!$pending) return $this->account_notice($appCode, 'Sesi upload CSV tidak berlaku. Unggah kembali berkasnya.');
        if ($pending['app_code'] !== $appCode || (int)$pending['expires_at'] < time() || !is_file($pending['path'])) {
            if (!empty($pending['path']) && is_file($pending['path'])) @unlink($pending['path']);
            $this->session->unset_userdata('portal_sso_pending_client_import');
            return $this->account_notice($appCode, 'Sesi upload CSV tidak berlaku. Unggah kembali berkasnya.');
        }
        $uploadToken = (string)$this->input->post('upload_token', true);
        if ($uploadToken === '' || !hash_equals((string)$pending['token'], $uploadToken)) return $this->account_notice($appCode, 'Token pemetaan CSV tidak valid. Unggah kembali berkasnya.');
        if (!$this->portalSso->client_by_code($appCode)) show_404();
        if (!$this->clientAccounts->table_ready()) return $this->account_notice($appCode, 'Tabel akun klien belum tersedia. Terapkan migration database terlebih dahulu.');
        if ($this->secret_encryption_key() === false) return $this->account_notice($appCode, 'Kunci enkripsi SSO belum aktif. Atur kunci server sebelum mengimpor password.');
        $usernameColumn = filter_var($this->input->post('username_column'), FILTER_VALIDATE_INT);
        $passwordColumn = filter_var($this->input->post('password_column'), FILTER_VALIDATE_INT);
        $headerCount = count($pending['headers']);
        if ($usernameColumn === false || $passwordColumn === false || $usernameColumn < 0 || $passwordColumn < 0 || $usernameColumn >= $headerCount || $passwordColumn >= $headerCount || $usernameColumn === $passwordColumn) return $this->account_notice($appCode, 'Pemetaan kolom username dan password tidak valid.');

        $handle = fopen($pending['path'], 'rb');
        if (!$handle) return $this->account_notice($appCode, 'Berkas sementara tidak dapat dibaca. Unggah kembali CSV.');
        $this->read_csv_row($handle, $pending['delimiter']);
        $records = [];
        $seen = [];
        $skipped = 0;
        $lineCount = 0;
        while (($row = $this->read_csv_row($handle, $pending['delimiter'])) !== false) {
            $lineCount++;
            if ($lineCount > 10000) {
                fclose($handle);
                return $this->account_notice($appCode, 'CSV melebihi batas 10.000 baris data. Pecah berkas lalu unggah kembali.');
            }
            $username = trim((string)($row[$usernameColumn] ?? ''));
            $password = (string)($row[$passwordColumn] ?? '');
            if ($username === '' || $password === '') {
                $skipped++;
                continue;
            }
            if (strlen($username) > 190 || strlen($password) > 4096) {
                fclose($handle);
                return $this->account_notice($appCode, 'Username maksimal 190 byte dan password maksimal 4.096 byte. Perbaiki CSV; database belum diubah.');
            }
            $key = function_exists('mb_strtolower') ? mb_strtolower($username, 'UTF-8') : strtolower($username);
            if (isset($seen[$key])) {
                fclose($handle);
                return $this->account_notice($appCode, 'CSV berisi username duplikat. Hapus duplikat lalu unggah kembali; database belum diubah.');
            }
            $seen[$key] = true;
            $encryptedPassword = $this->encrypt_secret($password);
            if ($encryptedPassword === false) {
                fclose($handle);
                return $this->account_notice($appCode, 'Password gagal dienkripsi. Pastikan konfigurasi kunci enkripsi server valid.');
            }
            $records[] = ['username' => $username, 'password_encrypted' => $encryptedPassword];
        }
        fclose($handle);
        if (empty($records)) return $this->account_notice($appCode, 'Tidak ada baris dengan username dan password yang lengkap.');

        $this->db->trans_begin();
        $inserted = 0;
        $updated = 0;
        foreach ($records as $record) {
            $exists = $this->db->where(['app_code' => $appCode, 'username' => $record['username']])->count_all_results('tb_portal_sso_client_account') > 0;
            if (!$this->clientAccounts->save_imported_account($appCode, $record['username'], $record['password_encrypted'], (int)$this->session->userdata('id'))) break;
            if ($exists) $updated++;
            else $inserted++;
        }
        $saved = $this->db->trans_status();
        if ($saved) $this->db->trans_commit();
        else $this->db->trans_rollback();
        @unlink($pending['path']);
        $this->session->unset_userdata('portal_sso_pending_client_import');
        if (!$saved) return $this->account_notice($appCode, 'Impor gagal disimpan. Transaksi dibatalkan; periksa struktur tabel dan log database.');
        return $this->account_notice($appCode, 'Impor selesai: ' . $inserted . ' akun baru, ' . $updated . ' password diperbarui, ' . $skipped . ' baris kosong dilewati.');
    }

    public function assign_account_pic($appCode, $accountId)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        if (!$this->portalSso->client_by_code($appCode)) show_404();
        $source = trim((string)$this->input->post('pic_auth_source', true));
        $userId = (int)$this->input->post('pic_user_id');
        if ($source === '' && $userId === 0) {
            $source = null;
            $userId = null;
        } else {
            $known = false;
            foreach ($this->portalAccess->users() as $user) {
                if ($user['auth_source'] === $source && (int)$user['id'] === $userId) $known = true;
            }
            if (!$known) return $this->account_notice($appCode, 'PIC yang dipilih tidak ditemukan.');
        }
        $saved = $this->clientAccounts->assign_pic($appCode, $accountId, $source, $userId, (int)$this->session->userdata('id'));
        return $this->account_notice($appCode, $saved ? 'PIC akun berhasil diperbarui.' : 'Perubahan PIC gagal disimpan.');
    }

    public function reveal_account_password($appCode, $accountId)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        $expectedPin = getenv('PORTAL_SSO_KEY_PIN');
        $pin = (string)$this->input->post('pin', true);
        if ($expectedPin === false || $expectedPin === '') return $this->respond(false, 'PIN pengungkapan belum dikonfigurasi pada server.');
        $lockUntil = (int)$this->session->userdata('portal_sso_pin_lock_until');
        if ($lockUntil > time()) return $this->respond(false, 'Terlalu banyak percobaan PIN. Coba kembali beberapa menit lagi.');
        if (!hash_equals(hash('sha256', (string)$expectedPin), hash('sha256', $pin))) {
            $attempts = (int)$this->session->userdata('portal_sso_pin_attempts') + 1;
            $this->session->set_userdata('portal_sso_pin_attempts', $attempts);
            if ($attempts >= 5) {
                $this->session->set_userdata('portal_sso_pin_lock_until', time() + 300);
                $this->session->set_userdata('portal_sso_pin_attempts', 0);
            }
            return $this->respond(false, 'PIN salah.');
        }
        $this->session->unset_userdata(['portal_sso_pin_attempts', 'portal_sso_pin_lock_until']);
        $row = $this->clientAccounts->encrypted_password($appCode, $accountId);
        $password = $row ? $this->decrypt_secret($row['password_encrypted']) : false;
        if ($password === false) return $this->respond(false, 'Password akun tidak dapat dibuka. Periksa kunci enkripsi server.');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Pragma: no-cache');
        log_message('info', 'Password akun klien ditampilkan oleh admin id ' . (int)$this->session->userdata('id') . ' untuk app_code ' . preg_replace('/[^A-Za-z0-9_-]/', '', $appCode) . ' dan account_id ' . (int)$accountId);
        return $this->respond(true, 'Password berhasil dibuka.', ['username' => $row['username'], 'password' => $password]);
    }

    public function delete_account($appCode, $accountId)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        if (!$this->portalSso->client_by_code($appCode)) show_404();
        return $this->account_notice($appCode, $this->clientAccounts->delete_account($appCode, $accountId) ? 'Akun klien berhasil dihapus.' : 'Akun tidak ditemukan atau gagal dihapus.');
    }

    public function save()
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        if (!$this->db->field_exists('launch_uri', 'tb_portal_sso_client')) show_error('Kolom URL peluncuran belum diterapkan. Jalankan docs/database/portal_sso_launch_uri_20261024.sql.', 500);
        $appCode = strtolower(trim((string)$this->input->post('app_code', true)));
        $redirectUri = trim((string)$this->input->post('redirect_uri', true));
        $launchUri = trim((string)$this->input->post('launch_uri', true));
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{1,49}$/', $appCode) || !$this->valid_https_uri($redirectUri) || !$this->valid_https_uri($launchUri)) {
            return $this->respond(false, 'app_code wajib 2–50 karakter huruf kecil, diawali huruf/angka dan hanya berisi huruf, angka, garis bawah, atau tanda hubung. URL callback dan peluncuran harus HTTPS valid.');
        }
        $secret = bin2hex(random_bytes(32));
        $encrypted = $this->encrypt_secret($secret);
        if ($encrypted === false) return $this->respond(false, 'Kunci enkripsi belum dikonfigurasi. Atur PORTAL_SSO_ENCRYPTION_KEY minimal 32 karakter sebelum membuat secret.');
        $saved = $this->portalSso->save_client($appCode, $redirectUri, $launchUri, hash('sha256', $secret), $encrypted, (int)$this->session->userdata('id'));
        return $this->respond($saved, $saved ? 'Klien berhasil disimpan. Secret tersimpan terenkripsi dan dapat dibuka melalui PIN admin.' : 'Klien gagal disimpan. Pastikan migrasi database telah diterapkan.', $saved ? ['app_code' => $appCode, 'redirect_uri' => $redirectUri, 'launch_uri' => $launchUri, 'secret' => $secret] : []);
    }

    public function edit($appCode)
    {
        if ($this->input->method(true) !== 'GET') show_error('Metode tidak diizinkan.', 405);
        $client = $this->portalSso->client_by_code($appCode);
        if (!$client) show_404();
        unset($client['client_secret_hash'], $client['client_secret_encrypted']);
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        return $this->output->set_content_type('application/json', 'utf-8')->set_output(json_encode([
            'success' => true,
            'data' => $client,
        ], JSON_UNESCAPED_SLASHES));
    }

    public function update($appCode)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        $newAppCode = strtolower(trim((string)$this->input->post('app_code', true)));
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{1,49}$/', $newAppCode)) return $this->respond(false, 'app_code wajib 2–50 karakter huruf kecil dan hanya berisi huruf, angka, garis bawah, atau tanda hubung.');
        $redirectUri = trim((string)$this->input->post('redirect_uri', true));
        $launchUri = trim((string)$this->input->post('launch_uri', true));
        if (!$this->valid_https_uri($redirectUri) || !$this->valid_https_uri($launchUri)) {
            return $this->respond(false, 'URL callback dan URL peluncuran harus HTTPS yang valid tanpa kredensial atau fragment.');
        }
        $saved = $newAppCode === $appCode
            ? $this->portalSso->update_client_uris($appCode, $redirectUri, $launchUri, (int)$this->session->userdata('id'))
            : $this->portalSso->rename_client_code($appCode, $newAppCode, $redirectUri, $launchUri, (int)$this->session->userdata('id'));
        $client = $saved ? $this->portalSso->client_by_code($newAppCode) : null;
        return $this->respond($saved, $saved ? 'Konfigurasi klien berhasil diperbarui tanpa mengubah secret.' : 'Klien tidak ditemukan, app_code tujuan sudah dipakai, sudah memiliki aturan akses, atau perubahan gagal.', $saved ? [
            'app_code' => $newAppCode,
            'previous_app_code' => $appCode,
            'redirect_uri' => $client['redirect_uri'],
            'launch_uri' => $client['launch_uri'],
        ] : []);
    }

    public function delete($appCode)
    {
        if ($this->input->method(true) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $this->verify_form_token();
        if (!isset($this->portalAccess->apps()[$appCode])) return $this->respond(false, 'Kode aplikasi tidak valid.');
        $deleted = $this->portalSso->delete_client($appCode);
        return $this->respond($deleted, $deleted ? 'Integrasi ' . $appCode . ' dan kode SSO terkait berhasil dihapus.' : 'Klien tidak ditemukan atau penghapusan gagal.', $deleted ? ['app_code' => $appCode] : []);
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
