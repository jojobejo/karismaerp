<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Omset extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) redirect('login');
        $this->load->model('M_Kmt');
        $this->load->library('form_validation');
    }

    // Cek akses — ABM tidak bisa input omset
    private function cek_bukan_abm() {
        if ((int)$this->session->userdata('akses_lv') === 3) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke menu ini.');
            redirect('content/kmt/dashboard');
        }
    }

    // Wilayah filter sesuai level
    private function get_id_wilayah_filter() {
        return ((int)$this->session->userdata('akses_lv') === 3)
            ? (int)$this->session->userdata('id_wilayah')
            : null;
    }

    // ----------------------------------------------------------------
    // INDEX - Daftar Omset
    // ----------------------------------------------------------------
    public function index() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_omset_list($filter);

        // Hitung total omset hasil filter
        $total_omset = array_sum(array_column($list, 'penj_inc_ppn_neto'));

        $data = [
            'title'        => 'Data Omset KMT CORN',
            'list'         => $list,
            'total_omset'  => $total_omset,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'tahun'        => $tahun,
            'bulan'        => $bulan,
            'id_wilayah'   => $id_wilayah,
            'nama_bulan'   => ['','Januari','Februari','Maret','April','Mei','Juni',
                               'Juli','Agustus','September','Oktober','November','Desember'],
            'akses_lv'     => (int)$this->session->userdata('akses_lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/omset/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // TAMBAH - Form + Simpan
    // ----------------------------------------------------------------
    public function tambah() {
        $this->cek_bukan_abm(); // ABM tidak bisa input omset

        $data = [
            'title'        => 'Tambah Data Omset',
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'akses_lv'     => (int)$this->session->userdata('akses_lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/omset/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->cek_bukan_abm();

        $this->form_validation->set_rules('tanggal',   'Tanggal',   'required');
        $this->form_validation->set_rules('id_wilayah','Wilayah',   'required|integer');
        $this->form_validation->set_rules('nama_toko', 'Nama Toko', 'required');
        $this->form_validation->set_rules('produk',    'Produk',    'required');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah();
            return;
        }

        $tanggal = $this->input->post('tanggal');
        $qty     = (float)str_replace(',', '.', $this->input->post('quantity'));
        $harga   = (float)str_replace(['.','Rp ','Rp',',' ], ['','','','.'], $this->input->post('harga_inc_ppn'));
        $dpp     = (float)str_replace(['.','Rp ','Rp'],['' ,'',''],$this->input->post('penj_dpp_neto'));
        $ppn     = (float)str_replace(['.','Rp ','Rp'],['' ,'',''],$this->input->post('penj_inc_ppn_neto'));

        $insert = [
            'no_urut'          => $this->input->post('no_urut'),
            'kode'             => $this->input->post('kode'),
            'bulan'            => (int)date('m', strtotime($tanggal)),
            'tahun'            => (int)date('Y', strtotime($tanggal)),
            'tanggal'          => $tanggal,
            'nomor'            => $this->input->post('nomor'),
            'inputer'          => $this->session->userdata('nama'),
            'no_retur'         => $this->input->post('no_retur'),
            'tgl_retur'        => $this->input->post('tgl_retur') ?: null,
            'sales_so'         => $this->input->post('sales_so'),
            'sc'               => $this->input->post('sc'),
            'se'               => $this->input->post('se'),
            'wilayah_se'       => $this->input->post('wilayah_se'),
            'id_wilayah'       => (int)$this->input->post('id_wilayah'),
            'nama_toko'        => $this->input->post('nama_toko'),
            'kota'             => $this->input->post('kota'),
            'merk'             => $this->input->post('merk'),
            'jenis'            => $this->input->post('jenis'),
            'produk'           => $this->input->post('produk'),
            'quantity'         => $qty,
            'unit'             => $this->input->post('unit'),
            'box'              => (float)$this->input->post('box'),
            'ltr_kg'           => (float)$this->input->post('ltr_kg'),
            'harga_inc_ppn'    => $harga,
            'penj_dpp_neto'    => $dpp,
            'penj_inc_ppn_neto'=> $ppn,
            'keterangan'       => $this->input->post('keterangan'),
            'tgl_kirim'        => $this->input->post('tgl_kirim') ?: null,
            'created_by'       => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_omset($insert)) {
            $this->session->set_flashdata('success', 'Data omset berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('content/kmt/omset');
    }

    // ----------------------------------------------------------------
    // EDIT - Form + Update
    // ----------------------------------------------------------------
    public function edit($id) {
        $this->cek_bukan_abm();

        $row = $this->M_Kmt->get_omset_by_id($id);
        if (!$row) { show_404(); return; }

        $data = [
            'title'        => 'Edit Data Omset',
            'row'          => $row,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'akses_lv'     => (int)$this->session->userdata('akses_lv'),
        ];
        $this->load->view('content/kmt/omset/form', $data);
    }

    public function update($id) {
        $this->cek_bukan_abm();

        $tanggal = $this->input->post('tanggal');
        $update  = [
            'no_urut'          => $this->input->post('no_urut'),
            'kode'             => $this->input->post('kode'),
            'bulan'            => (int)date('m', strtotime($tanggal)),
            'tahun'            => (int)date('Y', strtotime($tanggal)),
            'tanggal'          => $tanggal,
            'nomor'            => $this->input->post('nomor'),
            'no_retur'         => $this->input->post('no_retur'),
            'tgl_retur'        => $this->input->post('tgl_retur') ?: null,
            'sales_so'         => $this->input->post('sales_so'),
            'sc'               => $this->input->post('sc'),
            'se'               => $this->input->post('se'),
            'wilayah_se'       => $this->input->post('wilayah_se'),
            'id_wilayah'       => (int)$this->input->post('id_wilayah'),
            'nama_toko'        => $this->input->post('nama_toko'),
            'kota'             => $this->input->post('kota'),
            'merk'             => $this->input->post('merk'),
            'jenis'            => $this->input->post('jenis'),
            'produk'           => $this->input->post('produk'),
            'quantity'         => (float)$this->input->post('quantity'),
            'unit'             => $this->input->post('unit'),
            'box'              => (float)$this->input->post('box'),
            'ltr_kg'           => (float)$this->input->post('ltr_kg'),
            'harga_inc_ppn'    => (float)str_replace('.','', $this->input->post('harga_inc_ppn')),
            'penj_dpp_neto'    => (float)str_replace('.','', $this->input->post('penj_dpp_neto')),
            'penj_inc_ppn_neto'=> (float)str_replace('.','', $this->input->post('penj_inc_ppn_neto')),
            'keterangan'       => $this->input->post('keterangan'),
            'tgl_kirim'        => $this->input->post('tgl_kirim') ?: null,
        ];

        if ($this->M_Kmt->update_omset($id, $update)) {
            $this->session->set_flashdata('success', 'Data omset berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('content/kmt/omset');
    }

    // ----------------------------------------------------------------
    // HAPUS
    // ----------------------------------------------------------------
    public function hapus($id) {
        $this->cek_bukan_abm();
        if ($this->M_Kmt->delete_omset($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('content/kmt/omset');
    }
}
