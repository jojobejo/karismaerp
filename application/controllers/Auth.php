<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Auth');
        $this->load->library('Portal_sso');
    }

    public function index()
    {
        $this->load->view('partial/login/header');
        $this->load->view('content/login/body');
        $this->load->view('partial/login/footer');
    }

    /**
     * Memulai alur SSO Authorization Code + PKCE S256 ke Portal KARISMA ERP (GET /sso/login).
     */
    public function sso_login()
    {
        if ($this->input->method(true) !== 'GET') {
            show_error('Metode permintaan tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        if (!$this->portal_sso->isConfigured()) {
            $this->fail_sso_login(null, 'failed', 'Konfigurasi SSO belum lengkap', 'Layanan SSO belum terkonfigurasi dengan lengkap.');
            return;
        }

        // Bersihkan identitas lokal lama dan rotasi session ID sebelum memulai transaksi SSO baru
        $this->clear_local_identity_session();
        $this->rotate_session_id();

        try {
            $authRequest = $this->portal_sso->createAuthorizationRequest();
        } catch (Throwable $e) {
            $this->fail_sso_login(null, 'failed', 'Gagal membuat parameter PKCE SSO', 'Gagal memulai sesi keamanan SSO. Silakan coba kembali.');
            return;
        }

        $sessionKey = (string) $this->portal_sso->getConfig('session_key', 'portal_sso');
        $this->session->set_userdata($sessionKey, [
            'state' => $authRequest['state'],
            'code_verifier' => $authRequest['code_verifier'],
            'issued_at' => $authRequest['issued_at'],
            'expires_at' => $authRequest['expires_at'],
        ]);

        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate')
            ->set_header('Pragma: no-cache');

        redirect($authRequest['authorize_url']);
    }

    /**
     * Menerima callback otorisasi dari Portal SSO, memvalidasi state, menukar code,
     * memeriksa akun lokal yang ditetapkan, mencatat login-event, dan membentuk session lokal (GET /sso/callback).
     */
    public function sso_callback()
    {
        if ($this->input->method(true) !== 'GET') {
            show_error('Metode permintaan tidak diizinkan.', 405, 'Method Not Allowed');
            return;
        }

        $this->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate')
            ->set_header('Pragma: no-cache');

        // Konsumsi transaksi SSO dari session sebelum menukar authorization code (sekali pakai)
        $sessionKey = (string) $this->portal_sso->getConfig('session_key', 'portal_sso');
        $transaction = $this->session->userdata($sessionKey);
        $this->session->unset_userdata($sessionKey);

        // Pastikan tidak ada identitas lokal lama yang tersisa apabila proses gagal di tengah jalan
        $this->clear_local_identity_session();

        $portalError = trim((string) $this->input->get('error', true));
        if ($portalError !== '') {
            $safeMessage = $this->portal_sso->translatePortalError($portalError, 403);
            $this->fail_sso_login(null, 'blocked', 'SSO ditolak oleh Portal: ' . $portalError, $safeMessage);
            return;
        }

        $code = trim((string) $this->input->get('code', true));
        $state = (string) $this->input->get('state', true);

        $stateCheck = $this->portal_sso->validateCallbackTransaction($transaction, $state);
        if (!$stateCheck['valid']) {
            $this->fail_sso_login(null, 'failed', 'Validasi state/session callback SSO gagal', $stateCheck['error']);
            return;
        }

        if ($code === '') {
            $this->fail_sso_login(null, 'failed', 'Authorization code kosong pada callback SSO', 'Kode otorisasi SSO tidak ditemukan. Silakan ulangi login dari Portal.');
            return;
        }

        $tokenResult = $this->portal_sso->exchangeAuthorizationCode(
            $code,
            $stateCheck['code_verifier'],
            $stateCheck['state']
        );

        if (!$tokenResult['success']) {
            $errorCode = isset($tokenResult['error_code']) ? (string) $tokenResult['error_code'] : 'token_failed';
            $this->fail_sso_login(null, 'failed', 'Pertukaran token SSO gagal: ' . $errorCode, $tokenResult['message']);
            return;
        }

        $claims = $tokenResult['claims'];
        $portalBaseUrl = (string) $this->portal_sso->getConfig('portal_base_url', 'https://portal.karismaerp.com');
        $accountResolution = $this->M_Auth->resolve_sso_local_account($portalBaseUrl, $claims);

        if (!$accountResolution['ok']) {
            $logTarget = isset($accountResolution['user']) && is_object($accountResolution['user'])
                ? $accountResolution['user']
                : (object) ['username' => $claims['client_account_username'] ?? null];
            $reason = isset($accountResolution['reason']) ? (string) $accountResolution['reason'] : 'account_invalid';
            $logStatus = ($reason === 'account_inactive') ? 'blocked' : 'failed';
            $this->fail_sso_login($logTarget, $logStatus, 'Validasi akun lokal SSO gagal: ' . $reason, $accountResolution['message']);
            return;
        }

        $localUser = $accountResolution['user'];

        // Kirim konfirmasi histori login ke Portal sebelum mengaktifkan session lokal
        $loginEvent = $this->portal_sso->recordLoginEvent(
            $claims['user']['sub'],
            $claims['client_account_username'],
            $this->input->ip_address(),
            $this->input->user_agent()
        );

        if (!$loginEvent['success']) {
            $errorCode = isset($loginEvent['error_code']) ? (string) $loginEvent['error_code'] : 'login_event_failed';
            $this->fail_sso_login(
                $localUser,
                'failed',
                'Konfirmasi login-event SSO gagal: ' . $errorCode,
                $loginEvent['message']
            );
            return;
        }

        // Rotasi ID session dan tetapkan session lokal sesuai akun serta role lokal
        $this->rotate_session_id();
        $this->establish_local_session($localUser, [
            'auth_via' => 'portal_sso',
            'sso_sub' => $claims['user']['sub'],
            'sso_portal_username' => $claims['user']['username'],
            'sso_portal_name' => $claims['user']['name'],
        ]);

        $this->M_Auth->update_last_login($localUser->id, $localUser->auth_table ?? 'tb_users');
        $this->M_Auth->log_login($localUser, 'success', 'Login SSO Portal berhasil (' . $claims['user']['sub'] . ')');

        $this->redirect_authenticated_user($localUser);
    }

    /**
     * Endpoint informasi profil session lokal tanpa membocorkan secret/token Portal (GET /api/session).
     */
    public function session_info()
    {
        if ($this->input->method(true) !== 'GET') {
            return $this->output
                ->set_header('Cache-Control: no-store')
                ->set_status_header(405)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(['success' => false, 'error' => 'method_not_allowed']));
        }

        $loggedIn = (bool) $this->session->userdata('logged_in');
        if (!$loggedIn) {
            return $this->output
                ->set_header('Cache-Control: no-store')
                ->set_status_header(401)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode([
                    'success' => false,
                    'authenticated' => false,
                ]));
        }

        return $this->output
            ->set_header('Cache-Control: no-store')
            ->set_status_header(200)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'success' => true,
                'authenticated' => true,
                'data' => [
                    'id' => (int) $this->session->userdata('id'),
                    'username' => (string) $this->session->userdata('username'),
                    'nama' => (string) $this->session->userdata('nama'),
                    'departemen' => (string) $this->session->userdata('departemen'),
                    'jobdesk' => (string) $this->session->userdata('jobdesk'),
                    'jobdesk_hrd' => (string) $this->session->userdata('jobdesk_hrd'),
                    'lv' => (int) $this->session->userdata('lv'),
                    'auth_via' => (string) ($this->session->userdata('auth_via') ?: 'local'),
                ],
            ], JSON_UNESCAPED_SLASHES));
    }

    public function process()
    {
        $username = $this->input->post('user_isi');
        $password = $this->input->post('pass_isi');

        $users = $this->M_Auth->cek_password($username);
        $key = null;
        foreach ($users as $user) {
            if ($user->username === $username && password_verify($password, $user->password)) {
                $key = $user;
                break;
            }
        }

        if (!$key) {
            $message = empty($users) ? 'Username tidak ditemukan' : 'Password salah';
            $this->M_Auth->log_login((object) ['username' => $username], 'failed', $message);
            $this->session->set_flashdata('gagal', empty($users) ? 'username salah' : 'username / password salah!!!');
            redirect('Auth');
            return;
        }

        if ((isset($key->status) && (int) $key->status !== 1)
            || (isset($key->status_karyawan) && strtoupper(trim((string) $key->status_karyawan)) !== 'AKTIF')
        ) {
            $this->M_Auth->log_login($key, 'blocked', 'User nonaktif');
            $this->session->set_flashdata('gagal', 'User nonaktif. Hubungi administrator.');
            redirect('Auth');
            return;
        }

        $this->clear_local_identity_session();
        $this->rotate_session_id();
        $this->establish_local_session($key, ['auth_via' => 'local']);

        $this->M_Auth->update_last_login($key->id, $key->auth_table ?? 'tb_users');
        $this->M_Auth->log_login($key, 'success', 'Login berhasil');

        $this->redirect_authenticated_user($key);
    }

    public function logout()
    {
        $this->clear_local_identity_session();
        $this->rotate_session_id();
        $this->session->set_flashdata('logout', 'Berhasil Log Out');
        $this->session->sess_destroy();

        $acceptHeader = (string) $this->input->server('HTTP_ACCEPT');
        if ($this->input->is_ajax_request() || stripos($acceptHeader, 'application/json') !== false) {
            $this->output
                ->set_header('Cache-Control: no-store')
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode(['success' => true, 'message' => 'Berhasil Log Out']));
            return;
        }

        redirect('Auth');
    }

    /**
     * Membentuk data session lokal berdasarkan akun lokal yang telah tervalidasi.
     */
    private function establish_local_session($key, array $extraSession = [])
    {
        $jobdesk = strtoupper(trim((string) ($key->jabatan ?? $key->jobdesk ?? $key->jobdesk_hrd ?? '')));
        $dataSession = array_merge([
            'id' => $key->id,
            'id_karyawan' => $key->id_karyawan ?? null,
            'nik' => $key->nik ?? null,
            'username' => $key->username,
            'departemen' => $key->departemen ?? $key->departement ?? $key->bagian ?? '',
            'lv' => (int) ($key->akses_lv ?? $key->level ?? 1),
            'akses_lv' => (int) ($key->akses_lv ?? $key->level ?? 1),
            'akses_lv_id' => (int) ($key->akses_lv_id ?? $key->akses_lv ?? $key->level ?? 1),
            'jobdesk' => $jobdesk,
            'jobdesk_hrd' => $key->jobdesk_hrd ?? null,
            'jobdesk_id' => $key->jobdesk_id ?? null,
            'nama' => $key->nama_lngkp ?? $key->nm_karyawan ?? '',
            'tim' => $key->tim ?? null,
            'wilayah' => $key->wilayah ?? null,
            'default_redirect' => $key->default_redirect ?? null,
            'auth_source' => $key->auth_table ?? 'tb_users',
            'logged_in' => true,
        ], $extraSession);

        $this->session->set_userdata($dataSession);
    }

    /**
     * Menghapus seluruh atribut identitas session lokal agar tidak tertinggal saat pergantian user.
     */
    private function clear_local_identity_session()
    {
        $keys = [
            'id',
            'id_karyawan',
            'nik',
            'username',
            'departemen',
            'lv',
            'akses_lv',
            'akses_lv_id',
            'jobdesk',
            'jobdesk_hrd',
            'jobdesk_id',
            'nama',
            'tim',
            'wilayah',
            'default_redirect',
            'auth_source',
            'auth_via',
            'sso_sub',
            'sso_portal_username',
            'sso_portal_name',
            'logged_in',
        ];
        $this->session->unset_userdata($keys);
    }

    /**
     * Merotasi ID session saat autentikasi atau logout.
     */
    private function rotate_session_id()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->session->sess_regenerate(true);
        }
    }

    /**
     * Mencatat kegagalan SSO tanpa menyertakan data rahasia dan mengembalikan pengguna ke halaman login.
     */
    private function fail_sso_login($userObject, $status, $auditMessage, $userMessage)
    {
        $target = is_object($userObject) ? $userObject : (object) ['username' => null];
        $this->M_Auth->log_login($target, $status, $auditMessage);
        $this->clear_local_identity_session();
        $this->session->set_flashdata('gagal', $userMessage);
        redirect('Auth');
    }

    /**
     * Mengarahkan user yang sudah terautentikasi ke halaman yang sesuai dengan role lokal.
     */
    private function redirect_authenticated_user($key)
    {
        $username = strtolower(trim((string) ($key->username ?? '')));
        $defaultRedirect = trim((string) ($key->default_redirect ?? ''));
        $jobdeskHrd = strtolower(trim((string) ($key->jobdesk_hrd ?? '')));
        $jobdesk = strtoupper(trim((string) ($key->jabatan ?? $key->jobdesk ?? $key->jobdesk_hrd ?? '')));

        if ($username === 'superadmin') {
            redirect('hrd/penilaian_lingkungan/admin');
            return;
        }

        if ($defaultRedirect === 'dashboard_penilaian'
            || $jobdeskHrd === 'direksi_readonly'
            || in_array($username, ['direktur1', 'direktur2', 'direktur3'], true)
        ) {
            redirect('dashboard_penilaian');
            return;
        }

        if ($defaultRedirect === 'penilaian_lingkungan' || $jobdeskHrd === 'inputer_laporan') {
            redirect('penilaian_lingkungan');
            return;
        }

        if ($key->username === 'admin') {
            redirect('dashboard_penilaian');
        } elseif ($jobdesk === 'LOGISTIK') {
            redirect('logistik');
        } elseif ($jobdesk === 'ADMINICS') {
            redirect('ics/ics_diffrent');
        } elseif ($jobdesk === 'ADMINKEU') {
            redirect('keuangan');
        } elseif ($jobdesk === 'ADMINPURCHASING') {
            redirect('keuangan');
        } elseif ($jobdesk === 'DIREKTUR') {
            redirect('dashboard');
        } elseif ($jobdesk === 'ADMINGA') {
            redirect('schedule_direktur');
        } elseif ($jobdesk === 'ADMINKEUTC') {
            redirect('keuangan');
        } elseif ($jobdesk === 'STOCKOPNAME') {
            redirect('stockopname');
        } elseif ($jobdesk === 'ADMIN') {
            redirect('penilaian_lingkungan');
        } elseif ($jobdesk === 'SALESONLINE') {
            redirect('stock');
        } elseif ($jobdesk === 'SALESCOUNTER') {
            redirect('sales_report');
        } elseif ($jobdesk === 'SALES') {
            redirect('kiu_katalog');
        } elseif ($jobdesk === 'DISTRIBUSI') {
            redirect('logistik/distibusi');
        } elseif ($jobdesk === 'ADMINLOGLPB') {
            redirect('ics/icspo');
        } elseif ($jobdesk === 'ADMIN PO') {
            redirect('ics/icspo');
        } elseif ($jobdesk === 'ADMLOG') {
            redirect('checker');
        } elseif ($jobdesk === 'CHECKER') {
            redirect('checker');
        } elseif ($jobdesk === 'MANAGERWH') {
            redirect('checker');
        } elseif ($jobdesk === 'SALESCK') {
            redirect('checker');
        } elseif ($jobdesk === 'DIREKTURCK') {
            redirect('checker/dashboard');
        } elseif ($jobdesk === 'MANAGERCK') {
            redirect('checker');
        } elseif ($jobdesk === 'SC') {
            redirect('sales_order');
        } elseif ($jobdesk === 'SUPERADMIN') {
            redirect('penilaian_lingkungan');
        } else {
            redirect('penilaian_lingkungan');
        }
    }
}
