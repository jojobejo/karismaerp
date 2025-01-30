<?php
defined('BASEPATH') or exit('No direct script access allowed');

class C_Keuangan extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('M_Keuangan');
        $this->load->model('M_Stockbuffer');
        $this->load->model('M_Bufferstockglobal');
        $this->load->helper(array('form', 'url'));
        $this->load->library('upload');
        $this->load->database();
    }

    public function index()
    {
        $data['page_title']     = 'KARISMA - KEUANGAN';
        $data['count_gudang']   = $this->M_Keuangan->get_data_gdg();
        $data['updated']        = $this->M_Keuangan->get_updated();

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

        $gdgid   = $this->input->post('gdgid');

        if (!empty($data) && $gdgid != '1') {
            $this->update_data();
            $this->M_Keuangan->insert_batch($data);
            $this->session->set_flashdata('message', 'Data imported successfully.');
        } else if (!empty($data) && $gdgid == '1') {
            $this->update_data();
            $this->M_Keuangan->batch_global($data);
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

    public function truncateitm($kd, $id)
    {
        $this->M_Keuangan->truncateitm($id);
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
            $row[] = $field->qty_box;
            $row[] = $field->qty_pcs;

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

    public function get_data_global()
    {
        $list = $this->M_Bufferstockglobal->get_datatables();
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $field) {
            $no++;
            $row = array();

            $row[] = $field->nmsuplier;
            $row[] = $field->nmbarang;
            $row[] = $field->satuan;
            $row[] = $field->qty;
            $row[] = $field->qty_box;
            $row[] = $field->qty_pcs;

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->M_Bufferstockglobal->count_all(),
            "recordsFiltered" => $this->M_Bufferstockglobal->count_filtered(),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
    }

    public function list_stock_minimum($id)
    {
        if ($id == '1') {
            $gudangid = $id;
            $gudang = 'STOCK MINIMUM - Global';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock']          = $this->M_Keuangan->get_stock_global();

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_minimum.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } elseif ($id == '2') {
            $gudangid = $id;
            $gudang = 'STOCK MINIMUM - Induk';
            $gdg    = 'Gdg. Induk';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['gdg']            = $gdg;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock']          = $this->M_Keuangan->get_stockmin_gdg($gdg);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_minimum.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } elseif ($id == '3') {
            $gudangid = $id;
            $gudang = 'STOCK MINIMUM - Rusak';
            $gdg    = 'Gdg. Rusak';
            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangid']       = $gudangid;
            $data['gdg']            = $gdg;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock']          = $this->M_Keuangan->get_stockmin_gdg($gdg);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_minimum.php', $data);
            $this->load->view('partial/main/footergdg.php');
        }
    }

    public function stock_suplier($gdg, $id)
    {
        if ($id == '1') {
            $kd = 'BASFI01';
        } elseif ($id == '2') {
            $kd = 'SYNGE01';
        } elseif ($id == '3') {
            $kd = 'DUPON01';
        } elseif ($id == '4') {
            $kd = 'BAYER01';
        }

        if ($gdg == '1') {

            $gudangid = $id;
            $suplier = $kd;
            $gudang = 'Global';
            $gudangs = $gdg;

            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangs']        = $gudangs;
            $data['gudangid']       = $gudangid;
            $data['suplier']        = $suplier;
            $data['gdg']            = $gdg;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock_sup']      = $this->M_Keuangan->get_stock_by_sup_global($suplier);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_by_suplier.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } else if ($gdg == '2') {

            $gudangid = $id;
            $suplier = $kd;
            $gudang = 'Gdg. Induk';
            $gudangs = $gdg;

            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangs']        = $gudangs;
            $data['gudangid']       = $gudangid;
            $data['suplier']        = $suplier;
            $data['gdg']            = $gdg;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock_sup']      = $this->M_Keuangan->get_stock_by_sup($suplier, $gudang);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_by_suplier.php', $data);
            $this->load->view('partial/main/footergdg.php');
        } else if ($gdg == '3') {

            $gudangid = $id;
            $suplier = $kd;
            $gudang = 'Gdg. Rusak';
            $gudangs = $gdg;

            $data['page_title']     = 'KARISMA - KEUANGAN';
            $data['gudang']         = $gudang;
            $data['gudangs']        = $gudangs;
            $data['gudangid']       = $gudangid;
            $data['suplier']        = $suplier;
            $data['gdg']            = $gdg;
            $data['updated']        = $this->M_Keuangan->get_last_update($id);
            $data['stock_sup']      = $this->M_Keuangan->get_stock_by_sup($suplier, $gudang);

            $this->load->view('partial/main/header.php', $data);
            $this->load->view('content/keuangan/stock_by_suplier.php', $data);
            $this->load->view('partial/main/footergdg.php');
        }
    }
}
