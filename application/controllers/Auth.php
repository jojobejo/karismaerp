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

    function process()
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
            $this->M_Auth->log_login((object)['username' => $username], 'failed', $message);
            $this->session->set_flashdata("gagal", empty($users) ? "username salah" : "username / password salah!!!");
            redirect('Auth');
            return;
        }

        if ((isset($key->status) && (int)$key->status !== 1)
            || (isset($key->status_karyawan) && strtoupper(trim((string)$key->status_karyawan)) !== 'AKTIF')) {
            $this->M_Auth->log_login($key, 'blocked', 'User nonaktif');
            $this->session->set_flashdata("gagal", "User nonaktif. Hubungi administrator.");
            redirect('Auth');
            return;
        }

        $jobdesk = strtoupper(trim((string)($key->jabatan ?? $key->jobdesk ?? $key->jobdesk_hrd ?? '')));
        $data_session = array(
            'id'                => $key->id,
            'id_karyawan'       => $key->id_karyawan ?? null,
            'nik'               => $key->nik ?? null,
            'username'          => $key->username,
            'departemen'        => $key->departemen ?? $key->departement ?? $key->bagian ?? '',
            'lv'                => (int)($key->akses_lv ?? $key->level ?? 1),
            'akses_lv'          => (int)($key->akses_lv ?? $key->level ?? 1),
            'akses_lv_id'       => (int)($key->akses_lv_id ?? $key->akses_lv ?? $key->level ?? 1),
            'jobdesk'           => $jobdesk,
            'jobdesk_hrd'       => $key->jobdesk_hrd ?? null,
            'jobdesk_id'        => $key->jobdesk_id ?? null,
            'nama'              => $key->nama_lngkp ?? $key->nm_karyawan ?? '',
            'tim'               => $key->tim ?? null,
            'wilayah'           => $key->wilayah ?? null,
            'default_redirect'  => $key->default_redirect ?? null,
            'logged_in'         => true
        );
        $this->session->set_userdata($data_session);
        $this->M_Auth->update_last_login($key->id, $key->auth_table ?? 'tb_users');
        $this->M_Auth->log_login($key, 'success', 'Login berhasil');

        if (strtolower(trim((string)$key->username)) === 'superadmin') {
            redirect('hrd/penilaian_lingkungan/admin');
            return;
        }

        // Only explicitly supported landing routes may override jobdesk defaults.
        if (isset($key->default_redirect) && trim((string)$key->default_redirect) === 'penilaian_lingkungan') {
            redirect('penilaian_lingkungan');
            return;
        }

        if ($key->username === 'admin') {
            redirect('dashboard_penilaian');
        } else if ($jobdesk === 'LOGISTIK') {
            redirect('logistik');
        } else if ($jobdesk === 'ADMINICS') {
            redirect('ics/ics_diffrent');
        } else if ($jobdesk === 'ADMINKEU') {
            redirect('keuangan');
        } else if ($jobdesk === 'ADMINPURCHASING') {
            redirect('keuangan');
        } else if ($jobdesk === 'DIREKTUR') {
            redirect('dashboard');
        } else if ($jobdesk === 'ADMINGA') {
            redirect('schedule_direktur');
        } else if ($jobdesk === 'ADMINKEUTC') {
            redirect('keuangan');
        } else if ($jobdesk === 'STOCKOPNAME') {
            redirect('stockopname');
        } else if ($jobdesk === 'ADMIN') {
            redirect('penilaian_lingkungan');
        } else if ($jobdesk === 'SALESONLINE') {
            redirect('stock');
        } else if ($jobdesk === 'SALESCOUNTER') {
            redirect('sales_report');
        } else if ($jobdesk === 'SALES') {
            redirect('kiu_katalog');
        } else if ($jobdesk === 'DISTRIBUSI') {
            redirect('logistik/distibusi');
        } else if ($jobdesk === 'ADMINLOGLPB') {
            redirect('ics/icspo');
        } else if ($jobdesk === 'ADMIN PO') {
            redirect('ics/icspo');
        } else if ($jobdesk === 'ADMLOG') {
            redirect('checker');
        } else if ($jobdesk === 'CHECKER') {
            redirect('checker');
        } else if ($jobdesk === 'MANAGERWH') {
            redirect('checker');
        } else if ($jobdesk === 'SALESCK') {
            redirect('checker');
        } else if ($jobdesk === 'DIREKTURCK') {
            redirect('checker/dashboard');
        } else if ($jobdesk === 'MANAGERCK') {
            redirect('checker');
        } else if ($jobdesk === 'SC') {
            redirect('sales_order');
        } else if ($jobdesk === 'SUPERADMIN') {
            redirect('penilaian_lingkungan');
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
