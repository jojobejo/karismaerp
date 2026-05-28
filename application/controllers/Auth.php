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

        $check_username = $this->M_Auth->cek_username($username)->num_rows();
        if ($check_username > 0) {

            $check_password = $this->M_Auth->cek_password($username);

            foreach ($check_password as $key) {
                if ($key->username == $username && password_verify($password, $key->password)) {
                    $akses_lv = (int)($key->akses_lv ?? 0);
                    $jobdesk = strtoupper(trim((string)($key->jobdesk ?? '')));
                    $username_login = strtolower(trim((string)($key->username ?? '')));
                    $is_admin_dashboard = ($username_login === 'admin' || ($akses_lv === 1 && $jobdesk === 'ADMIN'));

                    if (isset($key->status) && (int)$key->status !== 1) {
                        $this->M_Auth->log_login($key, 'blocked', 'User nonaktif');
                        $this->session->set_flashdata("gagal", "User nonaktif. Hubungi administrator.");
                        redirect('Auth');
                        return;
                    }

                    $data_session = array(
                        'id'            => $key->id,
                        'id_karyawan'   => $key->id,
                        'nik'           => $key->nik,
                        'username'      => $key->username,
                        'departemen'    => $key->departemen,
                        'lv'            => $akses_lv,
                        'akses_lv'      => $akses_lv,
                        'akses_lv_id'   => $key->akses_lv_id ?? $akses_lv,
                        'jobdesk'       => $jobdesk,
                        'jobdesk_id'    => $key->jobdesk_id ?? null,
                        'nama'          => $key->nm_karyawan,
                        'tim'           => $key->tim,
                        'wilayah'       => $key->wilayah,
                        'is_admin_dashboard' => $is_admin_dashboard,
                        'logged_in'     => true
                    );

                    $this->session->set_userdata($data_session);
                    $this->M_Auth->update_last_login($key->id);
                    $this->M_Auth->log_login($key, 'success', 'Login berhasil');

                    if ($is_admin_dashboard) {
                        redirect('dashboard');
                    } else if ($jobdesk == 'LOGISTIK') {
                        redirect('logistik');
                    } else if ($jobdesk == 'ADMINICS') {
                        redirect('ics/ics_diffrent');
                    } else if ($jobdesk == 'ADMINKEU') {
                        redirect('keuangan');
                    } else if ($jobdesk == 'ADMINPURCHASING') {
                        redirect('keuangan');
                    } else if ($jobdesk == 'DIREKTUR') {
                        redirect('dashboard');
                    } else if ($jobdesk == 'ADMINGA') {
                        redirect('schedule_direktur');
                    } else if ($jobdesk == 'ADMINKEUTC') {
                        redirect('keuangan');
                    } else if ($jobdesk == 'STOCKOPNAME') {
                        redirect('stockopname');
                    } else if ($jobdesk == 'SALESONLINE') {
                        redirect('stock');
                    } else if ($jobdesk == 'SALESCOUNTER') {
                        redirect('sales_report');
                    } else if ($jobdesk == 'SALES') {
                        redirect('kiu_katalog');
                    } else if ($jobdesk == 'DISTRIBUSI') {
                        redirect('logistik/distibusi');
                    } else if ($jobdesk == 'ADMINLOGLPB') {
                        redirect('ics/icspo');
                    } else if ($jobdesk == 'ADMIN PO') {
                        redirect('ics/icspo');
                    } else if ($jobdesk == 'ADMLOG') {
                        redirect('checker');
                    } else if ($jobdesk == 'CHECKER') {
                        redirect('checker');
                    } else if ($jobdesk == 'MANAGERWH') {
                        redirect('checker');
                    } else if ($jobdesk == 'SALESCK') {
                        redirect('checker');
                    } else if ($jobdesk == 'DIREKTURCK') {
                        redirect('checker/dashboard');
                    } else if ($jobdesk == 'MANAGERCK') {
                        redirect('checker');
                    } else if ($jobdesk == 'SC') {
                        redirect('sales_order');
                    } else if ($jobdesk == 'SUPERADMIN') {
                        redirect('hrd/penilaian_lingkungan/admin');
                    } else if ($jobdesk == 'KARYAWAN') {
                        redirect('mobile-erp');
                    } else {
                        redirect('dashboard');
                    }
                } else {
                    $this->M_Auth->log_login((object)['username' => $username], 'failed', 'Password salah');
                    $this->session->set_flashdata("gagal", "username / password salah!!!");
                    redirect('Auth');
                }
            }
        } else {
            $this->M_Auth->log_login((object)['username' => $username], 'failed', 'Username tidak ditemukan');
            $this->session->set_flashdata("gagal", "username salah");
            redirect('Auth');
        }
    }

    function logout()
    {
        $this->session->set_flashdata("logout", "Berhasil Log Out");
        $this->session->sess_destroy();
        redirect('Auth');
    }
}
