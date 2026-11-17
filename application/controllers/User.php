<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 */

class User extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model("M_User");
        $this->load->library('form_validation');
    }

    function index()
    {
        if ($this->session->userdata('status') != "login") {
            redirect('login');
        }

        $data["user"] = $this->M_User->getAll();

        $this->load->view("partial/katalog/header");
        $this->load->view("content/user/dashboard", $data);
        $this->load->view("partial/katalog/footer");
    }
    public function AddUser()
    {
        $kode_user  = $this->input->post("id_user_isi");
        $nama_user  = $this->input->post("nama_isi");
        $username   = $this->input->post("username_isi");
        $pass = $this->input->post("password_isi");
        $password = password_hash($pass, PASSWORD_DEFAULT);
        $hak_akses  = $this->input->post("hak_akses_isi");

        $data = array(
            'id_user'   => $kode_user,
            'nama_user'   => $nama_user,
            'hak_akses'  => $hak_akses,
            'user_status'  => '1',
            'username'    => $username,
            'password'    => $password
        );

        $this->M_User->adddata($data);
        redirect('user');
    }
}
