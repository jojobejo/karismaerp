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
        if ((int)$this->session->userdata('lv') === 3) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke menu ini.');
            redirect('kmt/dashboard');
        }
    }

    // Wilayah filter sesuai level
    private function get_id_wilayah_filter() {
        return ((int)$this->session->userdata('lv') === 3)
            ? (int)$this->session->userdata('wilayah')
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
            'lv'     => (int)$this->session->userdata('lv'),
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
            'lv'     => (int)$this->session->userdata('lv'),
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
        redirect('kmt/omset/');
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
            'lv'     => (int)$this->session->userdata('lv'),
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
        redirect('kmt/omset');
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
        redirect('kmt/omset');
    }

    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_omset_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Omset');

        // Header
        $headers = ['No','Tanggal','Wilayah','Nama Toko','Kota','Produk',
                    'Sales SO','Qty','Penj Inc PPN Neto'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        // Style header
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Data
        foreach ($list as $i => $row) {
            $r = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $r, date('d/m/Y', strtotime($row['tanggal'])));
            $sheet->setCellValueByColumnAndRow(3, $r, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $r, $row['nama_toko']);
            $sheet->setCellValueByColumnAndRow(5, $r, $row['kota'] ?? '-');
            $sheet->setCellValueByColumnAndRow(6, $r, $row['produk']);
            $sheet->setCellValueByColumnAndRow(7, $r, $row['sales_so'] ?? '-');
            $sheet->setCellValueByColumnAndRow(8, $r, $row['quantity']);
            $sheet->setCellValueByColumnAndRow(9, $r, $row['penj_inc_ppn_neto']);
        }

        // Auto width kolom
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Omset_KMT_' . $tahun . ($bulan ? '_Bln'.$bulan : '') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
