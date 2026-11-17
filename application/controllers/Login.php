<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 */

class Login extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Loginmodel');
    }
    function index()
    {
        $this->load->view("partial/login/header");
        $this->load->view("partial/login/body");
        $this->load->view("partial/login/footer");
    }

    function process()
    {
        $username = $this->input->post('user_isi');
        $password = $this->input->post('pass_isi');

        $check_username = $this->Loginmodel->cek_username($username)->num_rows();
        if ($check_username > 0) {

            $check_password = $this->Loginmodel->cek_password($username);

            foreach ($check_password as $key) {
                if ($key->username == $username && password_verify($password, $key->password) && $key->hak_akses == "1") {
                    $data_session = array(
                        'id_user'       => $key->id_user,
                        'nama_user'     => $key->nama_user,
                        'hak_akses'     => $key->hak_akses,
                        'status'        => "login"
                    );

                    $this->session->set_userdata($data_session);
                    redirect('dashboard');
                } else if ($key->username == $username && password_verify($password, $key->password) && $key->hak_akses == '2') {
                    $data_session = array(
                        'id_user'       => $key->id_user,
                        'nama_user'     => $key->nama_user,
                        'hak_akses'     => $key->hak_akses,
                        'status'        => "login"
                    );
                    $this->session->set_userdata($data_session);
                    redirect('sales');
                } else if ($key->username == $username && password_verify($password, $key->password) && $key->hak_akses == '3') {
                    $data_session = array(
                        'id_user'       => $key->id_user,
                        'nama_user'     => $key->nama_user,
                        'hak_akses'     => $key->hak_akses,
                        'status'        => "login"
                    );
                    $this->session->set_userdata($data_session);
                    redirect('keuangan');
                } else if (
                    $key->username == $username
                    && password_verify($password, $key->password)
                    && $key->hak_akses == '4'
                ) {

                    $data_session = array(
                        'id_user'   => $key->id_user,
                        'nama_user' => $key->nama_user,
                        'hak_akses' => $key->hak_akses,
                        'status'    => "login"
                    );

                    $this->session->set_userdata($data_session);
                    redirect('sales');
                } else if ($key->username == $username && password_verify($password, $key->password) && $key->hak_akses == '5') {
                    $data_session = array(
                        'id_user'       => $key->id_user,
                        'nama_user'     => $key->nama_user,
                        'hak_akses'     => $key->hak_akses,
                        'status'        => "login"
                    );
                    $this->session->set_userdata($data_session);
                    redirect('viewer');
                } else {
                    $this->session->set_flashdata("gagal", "username / password salah!!!");
                    redirect('login');
                }
            }
        } else {
            $this->session->set_flashdata("gagal", "username salah");
            redirect('login');
        }
    }
    function logout()
    {
        // $id_tmp = $this->session->userdata("id_tmp");
        // $this->Loginmodel->hapus_tmp($id_tmp);
        $this->session->sess_destroy();
        redirect("login");
    }
}
