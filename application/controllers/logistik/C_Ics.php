<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Opname extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_Opname');
        $this->load->model('M_Barang');
        $this->load->helper('stock_helper');
    }

    public function index()
    {
        $data['title'] = "Stock Opname";
        $data['data_ics'] = $this->M_Opname->getAllICS();
        $this->load->view('partial/header', $data);
        $this->load->view('opname/index', $data);
        $this->load->view('partial/footer');
    }

    public function form_input($nama_barang, $exp_date)
    {
        $data['barang'] = $this->M_Barang->getBarangByNama($nama_barang);
        $data['exp_date'] = $exp_date;
        $this->load->view('opname/modal_input', $data);
    }

    public function save()
    {
        $nama_barang = $this->input->post('nama_barang');
        $exp_date = $this->input->post('exp_date');
        $qty_box = (int)$this->input->post('qty_box');
        $qty_pcs = (int)$this->input->post('qty_pcs');
        $inputer = $this->session->userdata('username');

        $dimensi = $this->M_Barang->getDimensi($nama_barang);
        $total_qty = hitung_qty($qty_box, $qty_pcs, $dimensi);

        $this->M_Opname->insertOpname([
            'nama_barang' => $nama_barang,
            'exp_date' => $exp_date,
            'qty' => $total_qty,
            'inputer' => $inputer,
            'input_at' => date('Y-m-d H:i:s')
        ]);

        $this->M_Opname->logInput([
            'nama_user' => $inputer,
            'nama_barang' => $nama_barang,
            'exp_date' => $exp_date,
            'qty_box' => $qty_box,
            'qty_pcs' => $qty_pcs,
            'qty_total' => $total_qty,
            'keterangan' => 'Stock Opname'
        ]);

        redirect('opname');
    }
}
