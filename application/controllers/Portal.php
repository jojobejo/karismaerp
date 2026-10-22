<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Portal extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_PortalAccess', 'portalAccess');
    }

    public function index()
    {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('gagal', 'Sesi Anda berakhir. Silakan login ulang.');
            redirect('Auth');
            return;
        }

        $authSource = (string) $this->session->userdata('auth_source');
        $userId = (int) $this->session->userdata('id');
        $apps = $this->portalAccess->apps_for_user($authSource, $userId);

        $data = [
            'apps' => $apps,
            'app_count' => count($apps),
            'is_admin' => (bool) $this->session->userdata('is_admin_dashboard'),
        ];

        $this->load->view('portal/index', $data);
    }
}
