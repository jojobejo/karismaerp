<!-- controller/auth.php -->
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
                    $data_session = array(
                        'id'            => $key->id,
                        'nik'           => $key->nik,
                        'departemen'    => $key->departemen,
                        'lv'            => $key->akses_lv,
                        'jobdesk'       => $key->jobdesk,
                        'nama'          => $key->nm_karyawan,
                        'tim'           => $key->tim,
                        'wilayah'       => $key->wilayah
                    );
                    $this->session->set_userdata('logged_in', true);
                    $this->session->set_userdata($data_session);
                    if ($key->jobdesk == 'LOGISTIK') {
                        redirect('logistik');
                    } else if ($key->jobdesk == 'ADMINICS') {
                        redirect('ics/ics_diffrent');
                    } else if ($key->jobdesk == 'ADMINKEU') {
                        redirect('keuangan');
                    } else if ($key->jobdesk == 'ADMINPURCHASING') {
                        redirect('keuangan');
                    } else if ($key->jobdesk == 'DIREKTUR') {
                        redirect('dashboard');
                    } else if ($key->jobdesk == 'ADMINGA') {
                        redirect('schedule_direktur');
                    } else if ($key->jobdesk == 'ADMINKEUTC') {
                        redirect('keuangan');
                    } else if ($key->jobdesk == 'STOCKOPNAME') {
                        redirect('stockopname');
                    } else if ($key->jobdesk == 'ADMIN') {
                        redirect('extravaganza');
                    } else if ($key->jobdesk == 'SALESONLINE') {
                        redirect('stock');
                    } else if ($key->jobdesk == 'SALESCOUNTER') {
                        redirect('sales_report');
                    } else if ($key->jobdesk == 'SALES') {
                        redirect('kiu_katalog');
                    } else if ($key->jobdesk == 'DISTRIBUSI') {
                        redirect('logistik/distibusi');
                    } else if ($key->jobdesk == 'ADMINLOGLPB') {
                        redirect('ics/icspo');
                    } else if ($key->jobdesk == 'KADEPKS') {
                        redirect('kmt/dashboard');
                    } else if ($key->jobdesk == 'ABM') {
                        redirect('kmt/dashboard');
                    } else if ($key->jobdesk == 'ADMKEU') {
                        redirect('kmt/dashboard');
                    }
                } else {
                    $this->session->set_flashdata("gagal", "username / password salah!!!");
                    redirect('Auth');
                }
            }
        } else {
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
