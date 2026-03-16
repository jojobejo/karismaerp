<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Retur extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) redirect('login');
        $this->load->model('M_Kmt');
        $this->load->library('form_validation');
    }

    private function get_id_wilayah_filter() {
        return ((int)$this->session->userdata('lv') === 3)
            ? (int)$this->session->userdata('wilayah') : null;
    }

    public function index() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list         = $this->M_Kmt->get_retur_list($filter);
        $total_retur  = array_sum(array_column($list, 'nilai_retur'));
        $summary      = $this->M_Kmt->get_summary_retur($tahun, $id_wilayah ?: null);

        $data = [
            'page_title'        => 'Data Retur KMT CORN',
            'list'         => $list,
            'total_retur'  => $total_retur,
            'summary'      => $summary,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'tahun'        => $tahun,
            'bulan'        => $bulan,
            'id_wilayah'   => $id_wilayah,
            'nama_bulan'   => ['','Januari','Februari','Maret','April','Mei','Juni',
                               'Juli','Agustus','September','Oktober','November','Desember'],
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/retur/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function tambah() {
        $data = [
            'page_title'           => 'Tambah Data Retur',
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'        => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/retur/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->form_validation->set_rules('tanggal_retur','Tanggal Retur','required');
        $this->form_validation->set_rules('id_wilayah',   'Wilayah',      'required|integer');
        $this->form_validation->set_rules('nama_toko',    'Nama Toko',    'required');
        $this->form_validation->set_rules('produk',       'Produk',       'required');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }

        $tgl = $this->input->post('tanggal_retur');
        $insert = [
            'id_wilayah'    => (int)$this->input->post('id_wilayah'),
            'bulan'         => (int)date('m', strtotime($tgl)),
            'tahun'         => (int)date('Y', strtotime($tgl)),
            'tanggal_retur' => $tgl,
            'no_retur'      => $this->input->post('no_retur'),
            'nama_toko'     => $this->input->post('nama_toko'),
            'produk'        => $this->input->post('produk'),
            'quantity'      => (float)$this->input->post('quantity'),
            'nilai_retur'   => (float)str_replace('.','', $this->input->post('nilai_retur') ?? 0),
            'keterangan'    => $this->input->post('keterangan'),
            'created_by'    => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_retur($insert)) {
            $this->session->set_flashdata('success', 'Data retur berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/retur');
    }

    public function edit($id) {
        $row = $this->M_Kmt->get_retur_by_id($id);
        if (!$row) { show_404(); return; }
        $data = [
            'page_title'           => 'Edit Data Retur',
            'row'             => $row,
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'        => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];
        $this->load->view('content/kmt/retur/form', $data);
    }

    public function update($id) {
        $tgl = $this->input->post('tanggal_retur');
        $update = [
            'id_wilayah'    => (int)$this->input->post('id_wilayah'),
            'bulan'         => (int)date('m', strtotime($tgl)),
            'tahun'         => (int)date('Y', strtotime($tgl)),
            'tanggal_retur' => $tgl,
            'no_retur'      => $this->input->post('no_retur'),
            'nama_toko'     => $this->input->post('nama_toko'),
            'produk'        => $this->input->post('produk'),
            'quantity'      => (float)$this->input->post('quantity'),
            'nilai_retur'   => (float)str_replace('.','', $this->input->post('nilai_retur') ?? 0),
            'keterangan'    => $this->input->post('keterangan'),
        ];

        if ($this->M_Kmt->update_retur($id, $update)) {
            $this->session->set_flashdata('success', 'Data retur berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/retur');
    }

    public function hapus($id) {
        if ($this->M_Kmt->delete_retur($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/retur');
    }

    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_retur_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Retur');

        $headers = ['No','Tgl Retur','Wilayah','No Retur','Nama Toko','Produk','Qty','Nilai Retur','Keterangan'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
        }

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        foreach ($list as $i => $row) {
            $r = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $r, $i + 1);
            $sheet->setCellValueByColumnAndRow(2, $r, date('d/m/Y', strtotime($row['tanggal_retur'])));
            $sheet->setCellValueByColumnAndRow(3, $r, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $r, $row['no_retur'] ?? '-');
            $sheet->setCellValueByColumnAndRow(5, $r, $row['nama_toko']);
            $sheet->setCellValueByColumnAndRow(6, $r, $row['produk']);
            $sheet->setCellValueByColumnAndRow(7, $r, $row['quantity']);
            $sheet->setCellValueByColumnAndRow(8, $r, $row['nilai_retur']);
            $sheet->setCellValueByColumnAndRow(9, $r, $row['keterangan'] ?? '-');
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Retur_KMT_' . $tahun . ($bulan ? '_Bln'.$bulan : '') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
