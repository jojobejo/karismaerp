<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
        }

        $this->load->model('M_Dashboard');
    }

    public function index()
    {

        $context = $this->M_Dashboard->current_user_context();
        $data['page_title'] = 'Dashboard';
        $data['dashboard_context'] = $context;
        $data['dashboard_sections'] = $this->M_Dashboard->module_sections($context);
        $data['dashboard_active_key'] = $this->M_Dashboard->default_active_section($context, $data['dashboard_sections']);

        // Ambil notifikasi request revisi harga LPB khusus untuk ADMPNJ & Admin
        $jobdesk = strtoupper((string)($context['jobdesk'] ?? ''));
        $is_admpnj = !empty($context['is_admin']) || in_array($jobdesk, ['ADMPNJ', 'SC', 'MANAGERSC', 'KADEPSC'], true);
        $data['is_admpnj'] = $is_admpnj;
        $data['lpb_notifications'] = [];
        $data['lpb_notification_count'] = 0;

        if ($is_admpnj) {
            $this->load->model('M_LpbRevisionRequest');
            $data['lpb_notifications'] = $this->M_LpbRevisionRequest->get_active_notifications_for_admpnj();
            $data['lpb_notification_count'] = count($data['lpb_notifications']);
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/dashboard/index.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
