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
            'user_name' => trim((string)($this->session->userdata('nama_user') ?: $this->session->userdata('nama') ?: $this->session->userdata('username'))),
            'is_admin_user' => strtolower(trim((string)$this->session->userdata('username'))) === 'admin',
        ];

        $this->load->view('portal/index', $data);
    }
}
