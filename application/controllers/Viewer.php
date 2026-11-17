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

    function index()
    {
        if ($this->session->userdata('status') != "login") {
            redirect('login');
        }

        $data['tot_gbr'] = $this->M_Katalog->get_total()->result();
        $data['statistik'] = $this->M_Katalog->getStatistik();

        $this->load->view("partial/viewer/header");
        $this->load->view("content/viewer/dashboard", $data);
        $this->load->view("partial/viewer/footer");
    }

    function getBarang()
    {
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
