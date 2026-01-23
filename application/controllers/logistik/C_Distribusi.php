<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Distribusi extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Ics');
        $this->load->model('M_Logistik');
        $this->load->model('M_Distribusi');
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index()
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $data['total_tonase']       = $this->M_Distribusi->tonase_all_do_done();
        $data['all_rute']             = $this->M_Distribusi->all_rute();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/distribusi.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/logistik/ajax_distribusi.php');
    }

    public function get_ploting_rute()
    {
        $rute    = $this->input->post('rute');
        $tanggal = $this->input->post('tanggal');

        $data = $this->M_Distribusi->ploting_rute($rute, $tanggal);

        echo json_encode($data);
    }

    public function driver_rute_matrix()
    {
        $tanggal = $this->input->post('tanggal');
        $result = $this->M_Distribusi->get_driver_rute_matrix($tanggal);
        echo json_encode($result);
    }

    public function detail_list_faktur()
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $data['total_tonase']       = $this->M_Distribusi->total_tonase_by_rute();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/detail_list_faktur.php', $data);
        $this->load->view('partial/main/footer.php');
    }
}
