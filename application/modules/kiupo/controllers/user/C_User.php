<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 *
 */
class C_User extends CI_Controller

{


    function __construct()
    {
        parent::__construct();
        $this->load->model('User/M_User');
        $this->load->library('form_validation');
    }

    public function index()
    {

        $data['title'] = 'User';
        $data["user"] = $this->M_User->getAll();

        $this->load->view('partial/header', $data);
        $this->load->view('partial/sidebar');
        $this->load->view('content/user/body', $data);
        $this->load->view('partial/footer');
        $this->load->view('content/user/datatables');
    }

    public function AddUser()
    {
        $kd_user    = $this->input->post('kode_isi');
        $nama_user  = $this->input->post("nama_isi");
        $username   = $this->input->post("username_isi");
        $pass       = $this->input->post("password_isi");
        $password   = password_hash($pass, PASSWORD_DEFAULT);
        $departemen = $this->input->post("departemen_isi");
        $level       = $this->input->post("level_isi");

        $data = array(
            'kode_user'     => $kd_user,
            'nama_user'     => $nama_user,
            'username'      => $username,
            'password'      => $password,
            'aksess_lv'     => $level,
            'departement'   => $departemen,
        );

        $this->M_User->addUser($data);
        redirect('user');
    }

    public function EditUser()
    {
        if ($this->session->userdata('status') != "is_login" || $this->session->userdata('lv') != "1") {
            redirect("login");
        }

        $id_user    = $this->input->post('id_isi');
        $nama_user  = $this->input->post('nama_isi');
        $username   = $this->input->post("username_isi");
        $pass = $this->input->post("password_isi");
        $password = password_hash($pass, PASSWORD_DEFAULT);
        $role  = $this->input->post("role_isi");
        $sektor = $this->input->post("sektor_isi");
        $team = $this->input->post("team_isi");

        $data = array(
            'id_user'   => $id_user,
            'nama_user' => $nama_user,
            'username'  => $username,
            'password'  => $password,
            'role'      => $role,
            'sektor'    => $sektor,
            'team_opname'    => $team
        );
        $this->Usermodel->editUser($id_user, $data);
        redirect('user');
    }

    public function usersetting()
    {
        if ($this->session->userdata('status') != "is_login") {
            redirect("login");
        }

        $kd_user    = $this->session->userdata('kode');

        $data['title']  = 'Account Setting';
        $data['info']   = $this->M_User->getInfoUser($kd_user)->result();

        $this->load->view('partial/header', $data);
        $this->load->view('partial/sidebar');
        $this->load->view('content/user/bodysetting', $data);
        $this->load->view('partial/footer');
    }
    public function editPassword()
    {
        $passold    = $this->input->post('passbaru');
        $kd_user    = $this->input->post('kduser');
        $password   = password_hash($passold, PASSWORD_DEFAULT);

        $changePass = array(
            'password' => $password
        );

        $this->M_User->editPassword($kd_user, $changePass);

        $this->session->set_flashdata("edited", "Data Berhasil Dirubah");
        redirect('Auth');
    }
    
    public function test()
    {

    }
}
