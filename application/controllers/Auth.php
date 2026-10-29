<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('M_Auth');
    }

    function index()
    {
        $this->load->view("partial/login/header");
        $this->load->view("content/login/body");
        $this->load->view("partial/login/footer");
    }

    public function forgot_password()
    {
        $username = trim((string) $this->input->post('username', true));
        if ($username === '') {
            $this->session->set_flashdata('gagal', 'Username wajib diisi untuk mengajukan reset password.');
            redirect('Auth');
            return;
        }
        $candidates = $this->M_Auth->get_auth_candidates($username);
        if (empty($candidates)) {
            $this->session->set_flashdata('gagal', 'Username tidak ditemukan. Periksa kembali username Anda.');
            redirect('Auth');
            return;
        }
        $candidate = $candidates[0];
        if (isset($candidate->status) && (int)$candidate->status !== 1) {
            $this->session->set_flashdata('gagal', 'Akun nonaktif. Silakan hubungi administrator.');
            redirect('Auth');
            return;
        }
        $ok = $this->M_Auth->request_password_reset($candidate);
        $this->session->set_flashdata($ok ? 'notice' : 'gagal', $ok
            ? 'Permintaan reset password sudah dikirim ke administrator.'
            : 'Permintaan reset belum dapat disimpan. Silakan coba kembali.');
        redirect('Auth');
    }

    private function login_failure($type, $message, $redirectMessage = null)
    {
        $acceptHeader = (string)$this->input->server('HTTP_ACCEPT');
        if ($this->input->is_ajax_request() || stripos($acceptHeader, 'application/json') !== false) {
            return $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'type' => $type, 'message' => $message]));
        }
        $this->session->set_flashdata('login_error_type', $type);
        $this->session->set_flashdata('gagal', $redirectMessage ?: $message);
        redirect('Auth');
    }

    function process()
    {
        $username = trim((string) $this->input->post('user_isi'));
        $password = (string) $this->input->post('pass_isi');
        // Autentikasi route auth hanya menggunakan kredensial dari tb_users.
        $tbUsers = $this->M_Auth->get_tb_users_user($username);
        $auth_candidates = $tbUsers ? [$tbUsers] : [];
        $key = null;

        if (empty($auth_candidates)) {
            $this->M_Auth->log_login((object)['username' => $username], 'failed', 'Username tidak ditemukan');
            $this->login_failure('username', 'Username tidak ditemukan. Periksa kembali username Anda.');
            return;
        }

        $activeCandidates = array_values(array_filter($auth_candidates, function ($candidate) {
            return !isset($candidate->status) || (int)$candidate->status === 1;
        }));
        if (empty($activeCandidates)) {
            $this->M_Auth->log_login($auth_candidates[0], 'blocked', 'User nonaktif');
            $this->login_failure('inactive', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
            return;
        }

        foreach ($activeCandidates as $candidate) {
            if ($this->M_Auth->verify_password($password, $candidate->password ?? '')) {
                $key = $candidate;
                break;
            }
        }

        if (!$key) {
            $this->M_Auth->log_login($activeCandidates[0], 'failed', 'Password salah');
            $this->login_failure('password', 'Password yang Anda masukkan salah. Silakan coba lagi.');
            return;
        }

        $auth_source = $key->auth_source ?? 'tb_karyawan';
        $akses_lv = (int)($key->akses_lv ?? $key->level ?? 0);
        $jobdesk_hrd = strtolower(trim((string)($key->jobdesk_hrd ?? '')));
        $jobdesk = strtoupper(trim((string)($key->jobdesk ?? $key->jabatan ?? '')));
        $username_login = strtolower(trim((string)($key->username ?? '')));
        $is_admin_dashboard = ($username_login === 'admin' || ($akses_lv === 1 && $jobdesk === 'ADMIN'));
        $nama = $key->nm_karyawan ?? $key->nama_lngkp ?? $key->nama_user ?? $key->username;
        $departemen = $key->departemen ?? $key->departement ?? null;

        $data_session = array(
            'id'            => $key->id,
            'id_karyawan'   => $auth_source === 'tb_karyawan' ? $key->id : null,
            'auth_source'   => $auth_source,
            'nik'           => $key->nik ?? null,
            'username'      => $key->username,
            'departemen'    => $departemen,
            'lv'            => $akses_lv,
            'akses_lv'      => $akses_lv,
            'akses_lv_id'   => $key->akses_lv_id ?? $akses_lv,
            'jobdesk'       => $jobdesk,
            'jobdesk_hrd'   => $jobdesk_hrd,
            'jobdesk_id'    => $key->jobdesk_id ?? null,
            'nama'          => $nama,
            'nama_user'     => $nama,
            'tim'           => $key->tim ?? null,
            'wilayah'       => $key->wilayah ?? null,
            'is_admin_dashboard' => $is_admin_dashboard,
            'logged_in'     => true
        );

        $this->session->sess_regenerate(true);
        $this->session->set_userdata($data_session);
        if ($this->M_Auth->password_should_be_hashed($key->password ?? '')) {
            $this->M_Auth->update_password_hash($key->id, $auth_source, $password);
        }
        $this->M_Auth->update_last_login($key->id, $auth_source);
        $this->M_Auth->log_login($key, 'success', 'Login berhasil');
        $pendingSso = $this->session->userdata('portal_sso_pending');
        if (is_array($pendingSso) && !empty($pendingSso['client_id']) && !empty($pendingSso['redirect_uri']) && !empty($pendingSso['state']) && !empty($pendingSso['code_challenge'])) {
            $this->session->unset_userdata('portal_sso_pending');
            redirect('sso/authorize?' . http_build_query([
                'client_id' => $pendingSso['client_id'],
                'redirect_uri' => $pendingSso['redirect_uri'],
                'state' => $pendingSso['state'],
                'code_challenge' => $pendingSso['code_challenge'],
                'code_challenge_method' => $pendingSso['code_challenge_method'],
            ], '', '&', PHP_QUERY_RFC3986));
            return;
        }
        redirect('portal');
    }

    function logout()
    {
        $this->session->set_flashdata("logout", "Berhasil Log Out");
        $this->session->sess_destroy();
        redirect('Auth');
    }
}
