<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Viewer extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model("M_Katalog");
        $this->load->library('form_validation');
    }

    private function _require_access($isJson = false)
    {
        if ($this->session->userdata('status') !== 'login') {
            if ($isJson || $this->input->is_ajax_request()) {
                $this->output->set_status_header(401)->set_content_type('application/json');
                echo json_encode(['status' => false, 'message' => 'Sesi login telah berakhir.']);
                exit;
            }
            redirect('login');
            exit;
        }
        $hakAkses = (int) $this->session->userdata('hak_akses');
        if (!in_array($hakAkses, [1, 5], true)) {
            if ($isJson || $this->input->is_ajax_request()) {
                $this->output->set_status_header(403)->set_content_type('application/json');
                echo json_encode(['status' => false, 'message' => 'Akses tidak diizinkan.']);
                exit;
            }
            show_error('Akses tidak diizinkan.', 403, 'Forbidden');
            exit;
        }
    }

    function index()
    {
        $this->_require_access();

        $data['tot_gbr'] = $this->M_Katalog->get_total()->result();
        $data['statistik'] = $this->M_Katalog->getStatistik();

        $this->load->view("partial/viewer/header");
        $this->load->view("content/viewer/dashboard", $data);
        $this->load->view("partial/viewer/footer");
    }

    function getBarang()
    {
        $this->_require_access(true);
        $list = $this->M_Katalog->get_datatables();
        $data = array();

        foreach ($list as $field) {
            $row = array();
            $row[] = $field->kode_barang;
            $row[] = $field->produk_fokus;
            $row[] = $field->nama_barang;
            $row[] = $field->nama_suplier;
            $row[] = $field->kelompok;
            $row[] = $field->bahan_aktif;

            $imagePath = "images/produk/" . $field->gbr_produk;
            if (!file_exists($imagePath)) $imagePath = "images/Karisma.png";
            $row[] = '<img src="' . $imagePath . '" style="width:80px; height:80px; object-fit:cover;">';

            $shopee_badge    = $field->shopee    
                ? '<span class="badge badge-success mb-1"><i class="fa fa-check"></i> Shopee</span>'    
                : '<span class="badge badge-secondary mb-1">Shopee -</span>';
            $tokopedia_badge = $field->tokopedia 
                ? '<span class="badge badge-success mb-1"><i class="fa fa-check"></i> Tokopedia</span>' 
                : '<span class="badge badge-secondary mb-1">Tokopedia -</span>';
            $kiushop_badge   = $field->kiushop   
                ? '<span class="badge badge-success mb-1"><i class="fa fa-check"></i> KiuShop</span>'   
                : '<span class="badge badge-secondary mb-1">KiuShop -</span>';
            $row[] = $shopee_badge . '<br>' . $tokopedia_badge . '<br>' . $kiushop_badge;

            $row[] = '
                <a href="' . base_url('pricelist?id=' . $field->kode_barang) . '" 
                class="btn btn-primary btn-sm" target="_blank">
                    <i class="fa fa-eye"></i>
                </a>
            ';

            $data[] = $row;
        }

        $output = array(
            "draw"            => $_POST['draw'],
            "recordsTotal"    => $this->M_Katalog->count_all(),
            "recordsFiltered" => $this->M_Katalog->count_filtered(),
            "data"            => $data,
        );
        echo json_encode($output);
    }
}
