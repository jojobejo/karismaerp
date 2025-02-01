<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('M_Logistik');
        $this->load->model('M_Hrd');
    }

    public function index()
    {

        $lvuser     = $this->session->userdata('lv');
        $jobdesk    = $this->session->userdata('jobdesk');

        // LV-1 = ADMIN
        // LV-2 = karyawan
        // LV-3 = Kadep
        // LV-4 = kusus
        // LV-5 = Direktur

        if ($lvuser == '1' && $jobdesk == 'LOGISTIK') {
            $data['page_title'] = 'KARISMA';
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/logistik/body.php', $data);
            $this->load->view('partial/main/footer.php');
        } elseif ($lvuser == '1' && $jobdesk == 'ADMINKEU') {
            $data['page_title'] = 'KARISMA';
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/dashboard.php', $data);
            $this->load->view('partial/main/footer.php');
        } elseif ($lvuser == '1' && $jobdesk == 'ADMINGA') {
            $data['page_title']  = 'Schedule Direktur';
            $data['getschedule'] = $this->M_Hrd->getdataschedule()->result();
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/schedule/body.php', $data);
            $this->load->view('content/schedule/ajaxschedule.php', $data);
            $this->load->view('partial/main/footer.php');
        } elseif ($lvuser == '5' && $jobdesk == 'DIREKTUR') {
            $data['page_title'] = 'KARISMA';
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/dashboard.php', $data);
            $this->load->view('partial/main/footer.php');
        } else {
            $data['page_title'] = 'KARISMA';
            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/body-karyawan.php', $data);
            $this->load->view('partial/main/footer.php');
        }
    }
}
