<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Keuangan extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('M_Keuangan');
        $this->load->model('M_Stockbuffer');
        $this->load->helper(array('form', 'url'));
        $this->load->library('upload');
        $this->load->database();
    }

    public function index()
    {
        $data['page_title']     = 'KARISMA - KEUANGAN';
        $data['count_gudang']   = $this->M_Keuangan->get_data_gdg();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/body.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function insertmodule()
    {
        $data['page_title']     = 'KARISMA - KEUANGAN';
        $data['kd']             = $this->M_Keuangan->generate_update();
        $data['updated']        = $this->M_Keuangan->get_updated_upload();

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/coba1.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function import()
    {
        $file_data = fopen($_FILES['csv_file']['tmp_name'], 'r');
        fgetcsv($file_data); // Skip header row

        $data = [];
        while ($row = fgetcsv($file_data)) {
            $data[] = [
                'kd_suplier'    => $row[0],
                'kd_barang'     => $row[1],
                'gudang'        => $row[2],
                'qty'           => $row[3]
            ];
        }
        if (!empty($data)) {
            $this->update_data();
            $this->M_Keuangan->insert_batch($data);
            $this->session->set_flashdata('message', 'Data imported successfully.');
        } else {
            $this->session->set_flashdata('message', 'Failed to import data.');
        }
        redirect('insertmodule');
    }

    private function update_data()
    {
        $kd      = $this->input->post('kdgenerates');
        $date    = $this->input->post('dateupload');
        $gdgid   = $this->input->post('gdgid');

        if ($gdgid == 1) {
            $gudang = 'Global';
        } else if ($gdgid == 2) {
            $gudang = 'Gdg. Induk';
        } else if ($gdgid == 3) {
            $gudang = 'Gdg. Rusak';
        }

        $data  = array(
            'kd_update'     => $kd,
            'gudangid'      => $gdgid,
            'gudang'        => $gudang,
            'last_update'   => $date
        );
        $this->M_Keuangan->insertupdate($data);
    }

    public function truncateitm($kd)
    {
        $this->M_Keuangan->truncateitm();
        $this->M_Keuangan->deleteupdateed($kd);
        redirect('keuangan');
    }

    function get_stock_a($id)
    {
        $list = $this->M_Stockbuffer->get_datatables($id);
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $field) {
            $no++;
            $row = array();

            $row[] = $field->nmsuplier;
            $row[] = $field->nmbarang;
            $row[] = $field->satuan;
            $row[] = $field->qty;

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->M_Stockbuffer->count_all($id),
            "recordsFiltered" => $this->M_Stockbuffer->count_filtered($id),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
    }

    public function gudang($id)
    {
        if ($id == '1') {

            $gudangid = $id;
            $gudang = 'Global';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/gudang.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } else if ($id == '2') {

            $gudangid = $id;
            $gudang = 'Gdg. Induk';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/gudang.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } else if ($id == '3') {

            $gudangid = $id;
            $gudang = 'Gdg. Rusak';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/gudang.php', $data);
            $this->load->view('partial/main/footergdg.php');
        }
    }

    public function list_stock_minimum($id)
    {

        $data['page_title']     = 'KARISMA - KEUANGAN';
        $data['gudangid']       = $id;
        $data['updated']        = $this->M_Keuangan->get_last_update($id);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/stock_minimum.php', $data);
        $this->load->view('partial/main/footergdg.php');
    }
}
