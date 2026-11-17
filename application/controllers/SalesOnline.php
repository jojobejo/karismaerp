<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SalesOnline extends CI_Controller
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
        if (!in_array($hakAkses, [1, 4], true)) {
            if ($isJson || $this->input->is_ajax_request()) {
                $this->output->set_status_header(403)->set_content_type('application/json');
                echo json_encode(['status' => false, 'message' => 'Akses tidak diizinkan.']);
                exit;
            }
            redirect('dashboard');
            exit;
        }
    }

    function index()
    {
        $this->_require_access();

        $data['statistik'] = $this->M_Katalog->getStatistik(); // ← tambahan

        $this->load->view("partial/salesonline/header");
        $this->load->view("content/salesonline/dashboard", $data);
        $this->load->view("partial/salesonline/footer");
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
                <button class="btn btn-info btn-sm btn-online"
                    data-id="'        . $field->id_barang   . '"
                    data-kode="'      . $field->kode_barang  . '"
                    data-nama="'      . $field->nama_barang  . '"
                    data-shopee="'    . $field->shopee       . '"
                    data-tokopedia="' . $field->tokopedia    . '"
                    data-kiushop="'   . $field->kiushop      . '">
                    <i class="fa fa-store"></i> Tandai
                </button>
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

    public function updateOnlineShop()
    {
        $this->_require_access();
        $id_barang   = $this->input->post('id_barang');
        $shopee      = $this->input->post('shopee')    ? 1 : 0;
        $tokopedia   = $this->input->post('tokopedia') ? 1 : 0;
        $kiushop     = $this->input->post('kiushop')   ? 1 : 0;

        $data = array(
            'shopee'    => $shopee,
            'tokopedia' => $tokopedia,
            'kiushop'   => $kiushop
        );

        $this->M_Katalog->updateOnlineShop($data, $id_barang);
        redirect('salesonline');
    }
}
