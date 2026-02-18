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
        $data['page_title']             = 'KARISMA - LOGISTIK';
        $data['faktur']                 = $this->M_Distribusi->persentase_faktur();
        $data['total_tonase']           = $this->M_Distribusi->tonase_all_do_done();
        $data['all_rute']               = $this->M_Distribusi->all_rute();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/distribusi/distribusi.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/logistik/distribusi/ajax_distribusi.php');
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

    public function driver_ready()
    {
        $tanggal = $this->input->post('tanggal');
        $rute    = $this->input->post('rute');

        $data = $this->M_Distribusi->get_driver_ready($tanggal, $rute);

        echo json_encode($data);
    }


    public function detail_list_faktur()
    {
        $data['page_title']         = 'KARISMA - LOGISTIK';
        $data['total_tonase']       = $this->M_Distribusi->total_tonase_by_rute();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/detail_list_faktur.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function list_faktur_status()
    {
        $data['page_title'] = 'KARISMA - LOGISTIK';

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/distribusi/list_faktur_status.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/logistik/distribusi/ajax_list_faktur_status.php');
    }

    public function ajax_list_faktur_status()
    {
        $status = trim((string) $this->input->post('status'));
        if (!in_array($status, ['1', '3'], true)) {
            $status = '1';
        }

        $data = $this->M_Distribusi->get_list_faktur_by_status($status);

        echo json_encode([
            'status' => true,
            'data' => $data
        ]);
    }

    public function list_do_status_2()
    {
        $data['page_title'] = 'KARISMA - LOGISTIK';
        $data['listdo_status2'] = [];

        $listdo = $this->M_Logistik->getdo();
        foreach ($listdo as $row) {
            if (isset($row->status) && (string) $row->status === '2') {
                $data['listdo_status2'][] = $row;
            }
        }

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/distribusi/list_do_status_2.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail_rute($kd)
    {
        $data['page_title'] = 'KARISMA - LOGISTIK';

        $data['faktur'] = $this->M_Distribusi->get_faktur_byrute($kd);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/distribusi/detail_rute.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function detail_tonase_by_rute($rute)
    {
        $data['page_title'] = 'KARISMA - LOGISTIK';
        $data['rute'] = $rute;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/logistik/distribusi/detail_tonase.php', $data);
        $this->load->view('partial/main/footer.php');
        $this->load->view('content/logistik/distribusi/ajax_detail_tonase.php', $data);
    }

    public function ajax_detail_tonase_by_rute()
    {
        $rute = trim((string) $this->input->post('rute'));
        $data = $this->M_Distribusi->detail_tonase_rute($rute);

        echo json_encode([
            'status' => true,
            'data' => $data
        ]);
    }
}
