<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller Autentikasi Lokal & SSO Portal KARISMA ERP
 * Aplikasi Klien: POS Security & HRD (pos_security)
 */
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

        $loggedIn = $this->session->userdata('status') === 'is_login';
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
                    'kduser' => (string) $this->session->userdata('kduser'),
                    'nama_user' => (string) $this->session->userdata('nama_user'),
                    'departemen' => (string) $this->session->userdata('departemen'),
                    'subdepartemen' => (string) $this->session->userdata('subdepartemen'),
                    'akses_lv' => (int) $this->session->userdata('akses_lv'),
                    'auth_via' => (string) ($this->session->userdata('auth_via') ?: 'local'),
                ],
            ], JSON_UNESCAPED_SLASHES));
    }

    /**
     * Memproses login menggunakan username dan password lokal (POST /process).
     */
    public function process()
    {
        $username = $this->input->post('user_isi');
        $password = $this->input->post('pass_isi');

        $check_username = $this->M_Auth->cek_username($username)->num_rows();
        if ($check_username > 0) {
            $check_password = $this->M_Auth->cek_password($username);
            $matchedUser = null;

            foreach ($check_password as $key) {
                if ($key->username == $username && password_verify((string) $password, (string) $key->password)) {
                    $matchedUser = $key;
                    break;
                }
            }

            if ($matchedUser) {
                $isInactive = (isset($matchedUser->status) && (string) $matchedUser->status !== '' && (int) $matchedUser->status !== 1)
                    || (isset($matchedUser->user_status) && (string) $matchedUser->user_status !== '' && (int) $matchedUser->user_status !== 1);

                if ($isInactive) {
                    $this->M_Auth->log_login($matchedUser, 'blocked', 'User lokal nonaktif');
                    $this->session->set_flashdata('gagal', 'User nonaktif. Hubungi administrator.');
                    redirect('Auth');
                    return;
                }

                $this->clear_local_identity_session();
                $this->rotate_session_id();
                $this->establish_local_session($matchedUser, ['auth_via' => 'local']);
                $this->M_Auth->log_login($matchedUser, 'success', 'Login lokal berhasil');

                $this->redirect_authenticated_user($matchedUser);
                return;
            }

            $this->M_Auth->log_login((object) ['username' => $username], 'failed', 'Password salah');
            $this->session->set_flashdata('gagal', 'username / password salah!!!');
            redirect('Auth');
        } else {
            $this->M_Auth->log_login((object) ['username' => $username], 'failed', 'Username tidak ditemukan');
            $this->session->set_flashdata('gagal', 'username salah');
            redirect('Auth');
        }
    }

    /**
     * Mengakhiri session lokal dan merotasi session ID tanpa memengaruhi session Portal.
     */
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
        $dataSession = array_merge([
            'id' => $key->id,
            'username' => $key->username,
            'kduser' => $key->kode_user ?? null,
            'subdepartemen' => $key->sub_departemen ?? null,
            'nama_user' => $key->nama_user ?? '',
            'akses_lv' => $key->akses_lv,
            'departemen' => $key->departemen ?? '',
            'status' => 'is_login',
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
            'username',
            'kduser',
            'subdepartemen',
            'nama_user',
            'akses_lv',
            'departemen',
            'status',
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
        $this->M_Auth->log_login($target, $status, $auditMessage);
        $this->clear_local_identity_session();
        $this->session->set_flashdata('gagal', $userMessage);
        redirect('Auth');
    }

    /**
     * Mengarahkan user yang sudah terautentikasi ke halaman yang sesuai dengan role/departemen lokal.
     */
    private function redirect_authenticated_user($key)
    {
        if (in_array(strtolower((string) $key->username), ['alif', 'supri', 'marisa'], true)) {
            redirect('hrd_lap_paket_pos');
        } elseif ($key->username === 'yunika' && $key->departemen === 'LOGISTIK') {
            redirect('hrd_lap_expedisi');
        } elseif ($key->departemen == 'KEUANGAN') {
            redirect('hrd_lap_paket_pos');
        } elseif ($key->departemen == 'MIA') {
            redirect('hrd_lap_paket_pos');
        } elseif ($key->departemen == 'HRD & GA') {
            redirect('hrd_lap_paket_pos');
        } else {
            redirect('dashboard');
        }
    }
}
