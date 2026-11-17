<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Autentikasi Lokal & SSO Portal KARISMA ERP
 * Aplikasi Klien: Katalog Produk (katalog_produk)
 */
class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Loginmodel');
        $this->load->library('Portal_sso');
    }

    public function index()
    {
        $this->load->view('partial/login/header');
        $this->load->view('partial/login/body');
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
        $accountResolution = $this->Loginmodel->resolve_sso_local_account($portalBaseUrl, $claims);

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

        // Rotasi ID session dan tetapkan session lokal sesuai akun serta hak_akses lokal
        $this->rotate_session_id();
        $this->establish_local_session($localUser, [
            'auth_via' => 'portal_sso',
            'sso_sub' => $claims['user']['sub'],
            'sso_portal_username' => $claims['user']['username'],
            'sso_portal_name' => $claims['user']['name'],
        ]);

        $this->Loginmodel->log_login($localUser, 'success', 'Login SSO Portal berhasil (' . $claims['user']['sub'] . ')');

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

        $isLoggedIn = ($this->session->userdata('status') === 'login');
        if (!$isLoggedIn) {
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
                    'id_user' => (string) $this->session->userdata('id_user'),
                    'username' => (string) $this->session->userdata('username'),
                    'nama_user' => (string) $this->session->userdata('nama_user'),
                    'hak_akses' => (int) $this->session->userdata('hak_akses'),
                    'auth_via' => (string) ($this->session->userdata('auth_via') ?: 'local'),
                ],
            ], JSON_UNESCAPED_SLASHES));
    }

    /**
     * Memproses login lokal menggunakan form username & password.
     */
    public function process()
    {
        $username = trim((string) $this->input->post('user_isi'));
        $password = (string) $this->input->post('pass_isi');

        $checkUsername = $this->Loginmodel->cek_username($username)->num_rows();
        if ($checkUsername <= 0) {
            $this->Loginmodel->log_login((object) ['username' => $username], 'failed', 'Username tidak ditemukan');
            $this->session->set_flashdata('gagal', 'username salah');
            redirect('login');
            return;
        }

        $candidates = $this->Loginmodel->cek_password($username);
        $matchedUser = null;
        foreach ($candidates as $key) {
            if ((string) $key->username === $username && password_verify($password, (string) $key->password)) {
                $matchedUser = $key;
                break;
            }
        }

        if (!$matchedUser) {
            $this->Loginmodel->log_login((object) ['username' => $username], 'failed', 'Password salah');
            $this->session->set_flashdata('gagal', 'username / password salah!!!');
            redirect('login');
            return;
        }

        if (isset($matchedUser->user_status) && (int) $matchedUser->user_status !== 1) {
            $this->Loginmodel->log_login($matchedUser, 'blocked', 'User nonaktif');
            $this->session->set_flashdata('gagal', 'Akun sedang nonaktif. Hubungi administrator.');
            redirect('login');
            return;
        }

        $hakAkses = (int) ($matchedUser->hak_akses ?? 0);
        if (!in_array($hakAkses, [1, 2, 3, 4, 5], true)) {
            $this->Loginmodel->log_login($matchedUser, 'failed', 'Hak akses tidak valid');
            $this->session->set_flashdata('gagal', 'Hak akses akun tidak valid.');
            redirect('login');
            return;
        }

        $this->clear_local_identity_session();
        $this->rotate_session_id();
        $this->establish_local_session($matchedUser, ['auth_via' => 'local']);

        $this->Loginmodel->log_login($matchedUser, 'success', 'Login lokal berhasil');

        $this->redirect_authenticated_user($matchedUser);
    }

    /**
     * Mengakhiri session lokal aplikasi Katalog Produk dan merotasi session ID (/logout atau /auth/logout).
     */
    public function logout()
    {
        $this->clear_local_identity_session();
        $this->rotate_session_id();
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

        redirect('login');
    }

    /**
     * Membentuk data session lokal berdasarkan akun tb_user yang telah tervalidasi.
     */
    private function establish_local_session($user, array $extraSession = [])
    {
        $dataSession = array_merge([
            'id_user' => (string) $user->id_user,
            'username' => (string) $user->username,
            'nama_user' => (string) $user->nama_user,
            'hak_akses' => (string) $user->hak_akses,
            'status' => 'login',
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
            'id_user',
            'username',
            'nama_user',
            'hak_akses',
            'status',
            'logged_in',
            'auth_via',
            'sso_sub',
            'sso_portal_username',
            'sso_portal_name',
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
        $this->Loginmodel->log_login($target, $status, $auditMessage);
        $this->clear_local_identity_session();
        $this->session->set_flashdata('gagal', $userMessage);
        redirect('login');
    }

    /**
     * Mengarahkan user yang sudah terautentikasi ke dashboard sesuai hak_akses lokal.
     */
    private function redirect_authenticated_user($user)
    {
        $hakAkses = (string) ($user->hak_akses ?? '');

        if ($hakAkses === '1') {
            redirect('dashboard');
        } elseif ($hakAkses === '2') {
            redirect('sales');
        } elseif ($hakAkses === '3') {
            redirect('keuangan');
        } elseif ($hakAkses === '4') {
            redirect('sales');
        } elseif ($hakAkses === '5') {
            redirect('viewer');
        } else {
            redirect('dashboard');
        }
    }
}
