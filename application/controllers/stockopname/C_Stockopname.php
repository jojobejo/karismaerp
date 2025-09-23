<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Stockopname extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('M_Stockopname');
        $this->load->helper('stock_helper');
        $this->load->helper('login_auth');
        is_logged_in();
    }

    public function index()
    {
        $data['page_title']     = 'KARISMA - LOGISTIK';
        // $data['resultopname']   = $this->M_Stockopname->get_result_opname();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/dashboard_opname.php', $data);
        $this->load->view('partial/main/footeropname.php');
    }

    public function compare_opname()
    {
        $data['page_title']         = 'KARISMA - ICS';
        $data['compare_all']        = $this->M_Stockopname->get_stockopname();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/stockopname/compare_opname.php', $data);
        $this->load->view('partial/main/footer.php');
        // $this->load->view('content/logistik/ics/ajaxics.php', $data);
    }
}
