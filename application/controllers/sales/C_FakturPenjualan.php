<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller C_FakturPenjualan (Legacy - Dialihkan ke Modul Transaksi)
 * Seluruh fungsi manajemen dan edit faktur telah dipusatkan pada Admin Transaksi Hub (admin/transaksi).
 */
class C_FakturPenjualan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        redirect('admin/transaksi');
    }

    public function index()
    {
        redirect('admin/transaksi');
    }

    public function edit_qty($id_faktur = '')
    {
        redirect('admin/transaksi');
    }

    public function update_qty($id_faktur = '')
    {
        redirect('admin/transaksi');
    }

    public function activity_log()
    {
        redirect('admin/transaksi');
    }
}
