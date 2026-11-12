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
        if ($this->uri->segment(1) === null) {
            redirect('Auth');
            return;
        }
        $this->load->view("partial/login/header");
        $this->load->view("content/login/body");
        $this->load->view("partial/login/footer");
    }

    function process()
    {
        $username = trim((string) $this->input->post('user_isi'));
        $password = (string) $this->input->post('pass_isi');
        $auth_candidates = method_exists($this->M_Auth, 'get_auth_candidates')
            ? $this->M_Auth->get_auth_candidates($username)
            : array_filter([$this->M_Auth->get_auth_user($username)]);
        $key = null;

        if (empty($auth_candidates)) {
            $this->M_Auth->log_login((object)['username' => $username], 'failed', 'Username tidak ditemukan');
            $this->session->set_flashdata("gagal", "username salah");
            redirect('Auth');
            return;
        }

        foreach ($auth_candidates as $candidate) {
            if ($this->M_Auth->verify_password($password, $candidate->password ?? '')) {
                $key = $candidate;
                break;
            }
        }

        if (!$key) {
            $this->M_Auth->log_login($auth_candidates[0], 'failed', 'Password salah');
            $this->session->set_flashdata("gagal", "username / password salah!!!");
            redirect('Auth');
            return;
        }

        if (isset($key->status) && (int)$key->status !== 1) {
            $this->M_Auth->log_login($key, 'blocked', 'User nonaktif');
            $this->session->set_flashdata("gagal", "User nonaktif. Hubungi administrator.");
            redirect('Auth');
            return;
        }

        // Di tb_users semakin tinggi angka level maka semakin tinggi jabatan (Admin = 99, Direksi = 5, Kadep = 4, Manager = 3, Koordinator = 2, Karyawan = 1)
        $level = (int)($key->level ?? 1);
        // Jobdesk mengikuti kolom bagian pada tb_users
        $bagian = strtoupper(trim((string)($key->bagian ?? '')));
        $username_login = strtolower(trim((string)($key->username ?? '')));
        $is_admin_dashboard = ($username_login === 'admin' || $bagian === 'ADMIN' || $level >= 99);
        $nama_lngkp = $key->nama_lngkp ?? $key->username;
        $departement = trim((string)($key->departement ?? ''));

        $data_session = array(
            'id'            => $key->id,
            'id_karyawan'   => $key->id,
            'nik'           => $key->nik ?? null,
            'username'      => $key->username,
            'departemen'    => $departement,
            'departement'   => $departement,
            'lv'            => $level,
            'akses_lv'      => $level,
            'akses_lv_id'   => $level,
            'jobdesk'       => $bagian,
            'nama'          => $nama_lngkp,
            'nm_karyawan'   => $nama_lngkp,
            'nama_user'     => $nama_lngkp,
            'tim'           => $key->tim ?? null,
            'wilayah'       => $key->wilayah ?? null,
            'is_admin_dashboard' => $is_admin_dashboard,
            'logged_in'     => true
        );

        $this->session->set_userdata($data_session);
        if ($this->M_Auth->password_should_be_hashed($key->password ?? '')) {
            $this->M_Auth->update_password_hash($key->id, 'tb_users', $password);
        }
        $this->M_Auth->update_last_login($key->id, 'tb_users');
        $this->M_Auth->log_login($key, 'success', 'Login berhasil');

        // Seluruh user yang berhasil login langsung diarahkan ke halaman Dashboard;
        // pembatasan akses modul/menu dikendalikan terpusat melalui hak akses menu Dashboard.
        redirect('dashboard');
    }

    function logout()
    {
        $this->session->set_flashdata("logout", "Berhasil Log Out");
        $this->session->sess_destroy();
        redirect('Auth');
    }
}
