<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Maintenance extends CI_Controller
{


    function __construct()
    {
        parent::__construct();
        $this->require_authenticated_session();
        $this->load->model('M_Maintenance');
    }

    /**
     * Memastikan setiap route modul Maintenance hanya diakses oleh session lokal yang terotorisasi.
     */
    private function require_authenticated_session()
    {
        $isLoggedIn = $this->session->userdata('status') === 'is_login'
            && (int) $this->session->userdata('id') > 0
            && trim((string) $this->session->userdata('username')) !== ''
            && in_array((int) $this->session->userdata('akses_lv'), [1, 2, 3, 4, 5], true);

        if ($isLoggedIn) {
            return;
        }

        $this->session->set_flashdata('gagal', 'Silakan login terlebih dahulu.');
        redirect('Auth');
        exit;
    }

    public function index()
    {
        $data['page_title'] = 'KIU MAINTENANCE';

        $this->load->view('partial/main/header.php',$data);
        $this->load->view('content/body.php', $data);
        $this->load->view('partial/main/footer.php');
    }
    
}
