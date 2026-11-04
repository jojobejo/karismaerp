<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Portal extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_PortalAccess', 'portalAccess');
        $this->load->model('M_PortalSsoClientAccounts', 'clientAccounts');
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
        foreach ($apps as &$app) {
            $appCode = (string)$app['app_code'];
            $assignment = $this->clientAccounts->assignment_for_user($appCode, $authSource, $userId);
            $app['account_assigned_by_admin'] = (bool)$assignment;
            $app['work_account_credentials'] = $assignment;
        }
        unset($app);

        $data = [
            'apps' => $apps,
            'app_count' => count($apps),
            'user_name' => trim((string)($this->session->userdata('nama_user') ?: $this->session->userdata('nama') ?: $this->session->userdata('username'))),
            'is_admin_user' => (bool)$this->session->userdata('is_admin_dashboard'),
        ];

        $this->load->view('portal/index', $data);
    }
}
