<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Keuangan extends CI_Controller
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

        $this->load->view("partial/katalog/header");
        $this->load->view("content/katalog/dashboardkeu");
        $this->load->view("partial/katalog/footerkeu");
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
            if (!file_exists($imagePath))
                $imagePath = "images/Karisma.png";
            $row[] = '<img src="' . $imagePath . '" style="width:100px; height:100px">';
            $row[] =
                '<a href="' . base_url('') . 'pricelist?id=' . $field->kode_barang . '" class="btn btn-primary btn-sm " target="_blank">
            <i class="fa fa-solid fa-eye"></i></a>' . ' ' . '
            <a href="#" class="btn btn-warning btn-sm " data-toggle="modal" data-target="#editbarang' . $field->id_barang . '">
            <i class="fa fa-solid fa-pencil-alt"></i></a>' . ' ' . '
            <a href="#" class="btn btn-danger btn-sm " data-toggle="modal" data-target="#hapus' . $field->id_barang . '">
            <i class="fa fa-solid fa-trash"></i></a>';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->M_Katalog->count_all(),
            "recordsFiltered" => $this->M_Katalog->count_filtered(),
            "data" => $data,
        );
        //output dalam format JSON
        echo json_encode($output);
    }

    public function barang_edit($id)
    {
        $data = $this->M_Katalog->get_by_id($id);
        echo json_encode($data);
    }

    public function ajax_update_barang()
    {
        $nama_barang = $this->input->post();
        $produk_fokus  = $this->input->post();
        $nama_suplier  = $this->input->post();
        $katagori_produk = $this->input->post();
        $bahan_aktif     = $this->input->post();

        $data = array(
            'nama_barang'   => $nama_barang,
            'nama_suplier'  => $nama_suplier,
            'poduk_fokus'   => $produk_fokus,
            'kelompok'      => $katagori_produk,
            'bahan_aktif'   => $bahan_aktif
        );
        $this->M_Katalog->update_barang_edited(array('id_barang' => $this->input->post('id_barang')), $data);
        echo json_encode(array("status" => TRUE));
    }


    public function add()
    {
        $kd_barang  = $this->input->post("kode_barang_isi");
        $nama_bar   = $this->input->post("nama_barang_isi");
        $prdk_fks   = $this->input->post("produk_fokus_isi");
        $nama_sup   = $this->input->post("nama_suplier_isi");
        $ktg_prodk  = $this->input->post("katagori_isi");
        $bhn_aktif_isi  = $this->input->post("bhn_aktif_isi");

        $data = array(
            'kode_barang'   => $kd_barang,
            'nama_barang'   => $nama_bar,
            'nama_suplier'  => $nama_sup,
            'produk_fokus'  => $prdk_fks,
            'kelompok'      => $ktg_prodk,
            'bahan_aktif'    => $bhn_aktif_isi,
            'gbr_produk'    => '-',
            'gbr_promo1'    => '-',
            'gbr_promo2'    => '-',
            'gbr_promo3'    => '-'
        );

        $this->M_Katalog->adddata($data);
        redirect('dashboard');
    }

    public function editKat()
    {
        $id_barang  = $this->input->post("id_bar");
        $kd_barang  = $this->input->post("kode_barang_isi");
        $nama_bar   = $this->input->post("nama_barang_isi");
        $prdk_fks   = $this->input->post("produk_fokus_isi");
        $nama_sup   = $this->input->post("nama_suplier_isi");
        $ktg_prodk  = $this->input->post("katagori_isi");

        $data = array(
            'id_barang'     => $id_barang,
            'kode_barang'   => $kd_barang,
            'nama_barang'   => $nama_bar,
            'nama_suplier'  => $nama_sup,
            'produk_fokus'  => $prdk_fks,
            'bahan_aktif'    => $ktg_prodk
        );

        $this->M_Katalog->editdataKat($data, $id_barang);
        redirect('keuangan');
    }

    public function deleteDat($hapus)
    {
        $id_barang = $hapus;


        $this->M_Katalog->barangdel($id_barang);

        redirect("keuangan");
    }
}
