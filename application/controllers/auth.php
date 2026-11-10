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

        $auth_source = $key->auth_source ?? 'tb_users';
        // Di tb_users semakin tinggi angka level maka semakin tinggi jabatan (Admin = 99, Direksi = 5, Kadep = 4, Manager = 3, Koordinator = 2, Karyawan = 1)
        $akses_lv = (int)($key->level ?? $key->akses_lv ?? 1);
        $jobdesk_hrd = strtolower(trim((string)($key->jobdesk_hrd ?? '')));
        // Jobdesk mengikuti kolom bagian pada tb_users
        $jobdesk = strtoupper(trim((string)($key->bagian ?? $key->jobdesk ?? $key->jabatan ?? '')));
        $jabatan = strtoupper(trim((string)($key->jabatan ?? '')));
        $username_login = strtolower(trim((string)($key->username ?? '')));
        $is_admin_dashboard = ($username_login === 'admin' || $jobdesk === 'ADMIN' || $akses_lv >= 99);
        $nama = $key->nama_lngkp ?? $key->nm_karyawan ?? $key->nama_user ?? $key->username;
        $departemen = trim((string)($key->departement ?? $key->departemen ?? ''));

        $data_session = array(
            'id'            => $key->id,
            'id_karyawan'   => $key->id,
            'auth_source'   => $auth_source,
            'nik'           => $key->nik ?? null,
            'username'      => $key->username,
            'departemen'    => $departemen,
            'departement'   => $departemen,
            'lv'            => $akses_lv,
            'akses_lv'      => $akses_lv,
            'akses_lv_id'   => $key->akses_lv_id ?? $akses_lv,
            'jobdesk'       => $jobdesk,
            'bagian'        => $jobdesk,
            'jabatan'       => $jabatan,
            'jobdesk_hrd'   => $jobdesk_hrd,
            'jobdesk_id'    => $key->jobdesk_id ?? null,
            'nama'          => $nama,
            'nm_karyawan'   => $nama,
            'nama_user'     => $nama,
            'tim'           => $key->tim ?? null,
            'wilayah'       => $key->wilayah ?? null,
            'is_admin_dashboard' => $is_admin_dashboard,
            'logged_in'     => true
        );

        $this->session->set_userdata($data_session);
        if ($this->M_Auth->password_should_be_hashed($key->password ?? '')) {
            $this->M_Auth->update_password_hash($key->id, $auth_source, $password);
        }
        $this->M_Auth->update_last_login($key->id, $auth_source);
        $this->M_Auth->log_login($key, 'success', 'Login berhasil');

        // Redirect berdasarkan jobdesk (bagian di tb_users) / username
        if ($username_login === 'admkeu') {
            redirect('sales_order/pecah_faktur');
        } else if ($is_admin_dashboard || $jobdesk === 'ADMIN') {
            redirect('dashboard');
        } else if (in_array($jobdesk, ['DIREKSI', 'DIREKTUR', 'DIREKTUR UTAMA', 'DIREKTURUTAMA', 'MT DIREKTUR OPERASIONAL', 'DIREKTUROP', 'DIREKTURCK', 'WAKIL DIREKTUR UTAMA'], true) || $akses_lv >= 5) {
            redirect('dashboard');
        } else if ($jobdesk_hrd === 'direksi_readonly') {
            redirect('dashboard_penilaian');
        } else if (in_array($jobdesk, ['LOGISTIK', 'LOGISTIC', 'ADMIN GUDANG', 'MT KADEP LOGISTIK'], true)) {
            redirect('logistik');
        } else if (in_array($jobdesk, ['ADMINICS', 'ADMIN ICS'], true)) {
            redirect('ics/ics_diffrent');
        } else if (in_array($jobdesk, ['ADMINKEU', 'ADMIN KEUANGAN', 'ADMIN JURNAL'], true)) {
            redirect('keuangan');
        } else if (in_array($jobdesk, ['KIUKEU', 'ADMKEU', 'ADMIN JURNAL PAJAK', 'MT ADMIN PIUTANG'], true)) {
            redirect('keuangan/pembayaran');
        } else if (in_array($jobdesk, ['ADMINPURCHASING', 'ADMIN PURCHASING', 'MT KADEP PURCHASING', 'PURCHASING', 'ADMIN PO KOMERSIL', 'MT ADMIN PO NON KOMERSIL'], true)) {
            redirect('dashboard');
        } else if (in_array($jobdesk, ['ADMINGA', 'ADMIN GA', 'KADEP GA'], true)) {
            redirect('schedule_direktur');
        } else if (in_array($jobdesk, ['ADMINKEUTC', 'ADMIN PEMBAYARAN HUTANG', 'MT MANAGER KEUANGAN'], true)) {
            redirect('dashboard');
        } else if (in_array($jobdesk, ['STOCKOPNAME', 'ADMIN_STOCKOPNAME'], true)) {
            redirect('stockopname');
        } else if (in_array($jobdesk, ['SALESONLINE', 'MT SALES ONLINE'], true)) {
            redirect('stock');
        } else if (in_array($jobdesk, ['DISTRIBUSI', 'ADMIN DISTRIBUSI', 'ADMIN DISTRIBUSI & MANAGER WAREHOUSE'], true)) {
            redirect('checker');
        } else if (in_array($jobdesk, ['ADMINLOGLPB', 'ADMLPB', 'ADMIN LPB & GUDANG BENIH'], true)) {
            redirect('ics/icspo');
        } else if (in_array($jobdesk, ['ADMLOG', 'CHECKER', 'MT CHECKER', 'MT ADMIN LOGISTIK', 'MANAGERWH', 'MT MANAGER WAREHOUSE', 'MT MANAGER DISTRIBUSI', 'SALESCK', 'MANAGERCK'], true)) {
            redirect('checker');
        } else if (in_array($jobdesk, ['ADMINSC', 'ADMIN SALES COUNTER', 'MT ADMIN SALES COUNTER'], true)) {
            redirect('sales_order/admin_sc');
        } else if (in_array($jobdesk, ['SC', 'MT SALES COUNTER', 'SALES COUNTER', 'SALESCOUNTER'], true)) {
            redirect('sales_order');
        } else if (in_array($jobdesk, ['SALES', 'SALES EXECUTIVE', 'MT ADMIN SALES'], true)) {
            redirect('dashboard');
        } else {
            redirect('dashboard');
        }
    }

    function logout()
    {
        $this->session->set_flashdata("logout", "Berhasil Log Out");
        $this->session->sess_destroy();
        redirect('Auth');
    }
}
