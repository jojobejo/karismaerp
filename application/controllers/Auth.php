<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
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

        $check_username = $this->Loginmodel->cek_username($username)->num_rows();
        if ($check_username > 0) {

            $check_password = $this->Loginmodel->cek_password($username);

            foreach ($check_password as $key) {
                if ($key->username == $username && password_verify($password, $key->password) && $key->role == "admin") {
                    $data_session = array(
                        'id_user'   => $key->id_user,
                        'username'  => $key->username,
                        'nama_user' => $key->nama_user,
                        'role'      => $key->role,
                        'sektor'    => $key->sektor,
                        'status'    => "is_login"
                    );

                    $this->session->set_userdata($data_session);
                    redirect('admin');
                } else if ($key->username == $username && password_verify($password, $key->password) && $key->role == "admin1") {
                    $data_session = array(
                        'id_user'       => $key->id_user,
                        'username'      => $key->username,
                        'nama_user'     => $key->nama_user,
                        'role'          => $key->role,
                        'sektor'        => $key->sektor,
                        'team_opname'   => $key->team_opname,
                        'status'    => "is_login"
                    );
                    $this->session->set_userdata($data_session);
                    redirect('admin');
                } else if ($key->username == $username && password_verify($password, $key->password) && $key->role == "user") {
                    $data_session = array(
                        'id_user'       => $key->id_user,
                        'username'      => $key->username,
                        'nama_user'     => $key->nama_user,
                        'role'          => $key->role,
                        'sektor'        => $key->sektor,
                        'team_opname'   => $key->team_opname,
                        'status'    => "is_login"
                    );
                    $this->session->set_userdata($data_session);
                    redirect('def_user');
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
}