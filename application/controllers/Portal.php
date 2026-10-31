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
            $app['account_assignment_required'] = $this->clientAccounts->requires_assignment($appCode);
            $app['account_assigned_by_admin'] = $app['account_assignment_required']
                && $this->clientAccounts->assignment_table_ready()
                && (bool)$this->clientAccounts->assignment_for_user($appCode, $authSource, $userId);
            $app['work_account_credentials'] = $app['account_assigned_by_admin']
                ? $this->clientAccounts->assignment_for_user($appCode, $authSource, $userId)
                : null;
        }
        unset($app);

        $data = [
            'apps' => $apps,
            'app_count' => count($apps),
            'user_name' => trim((string)($this->session->userdata('nama_user') ?: $this->session->userdata('nama') ?: $this->session->userdata('username'))),
            'is_admin_user' => strtolower(trim((string)$this->session->userdata('username'))) === 'admin',
        ];

        $this->load->view('portal/index', $data);
    }
}
