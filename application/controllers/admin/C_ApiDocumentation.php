<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_ApiDocumentation extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }
        if (!$this->session->userdata('is_admin_dashboard')) show_error('Akses hanya untuk administrator.', 403);
    }

    public function index()
    {
        $data['page_title'] = 'Dokumentasi API Integrasi Portal';
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/admin/api_documentation.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
