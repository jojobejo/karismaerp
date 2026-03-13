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
            'title'        => 'Data Retur KMT CORN',
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
            'title'           => 'Tambah Data Retur',
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
            'title'           => 'Edit Data Retur',
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
        include APPPATH . 'third_party/PHPExcel/PHPExcel.php';

        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_retur_list($filter);

        $style_col = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '1F3864')),
            'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
        );
        $style_row = array(
            'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
            'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN),'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
        );

        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0)->setCellValue('A1', 'Rekap Retur KMT CORN - Tahun ' . $tahun);
        $excel->getActiveSheet()->mergeCells('A1:I1');
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $headers = ['NO','TGL RETUR','WILAYAH','NO RETUR','NAMA TOKO','PRODUK','QTY','NILAI RETUR','KETERANGAN'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $excel->setActiveSheetIndex(0)->setCellValue($col . '3', $h);
            $excel->getActiveSheet()->getStyle($col . '3')->applyFromArray($style_col);
        }

        $no = 1; $numrow = 4;
        foreach ($list as $row) {
            $excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $no);
            $excel->setActiveSheetIndex(0)->setCellValue('B' . $numrow, date('d/m/Y', strtotime($row['tanggal_retur'])));
            $excel->setActiveSheetIndex(0)->setCellValue('C' . $numrow, $row['nama_wilayah'] ?? '-');
            $excel->setActiveSheetIndex(0)->setCellValue('D' . $numrow, $row['no_retur'] ?? '-');
            $excel->setActiveSheetIndex(0)->setCellValue('E' . $numrow, $row['nama_toko']);
            $excel->setActiveSheetIndex(0)->setCellValue('F' . $numrow, $row['produk']);
            $excel->setActiveSheetIndex(0)->setCellValue('G' . $numrow, $row['quantity']);
            $excel->setActiveSheetIndex(0)->setCellValue('H' . $numrow, $row['nilai_retur']);
            $excel->setActiveSheetIndex(0)->setCellValue('I' . $numrow, $row['keterangan'] ?? '-');
            foreach (['A','B','C','D','E','F','G','H','I'] as $col) {
                $excel->getActiveSheet()->getStyle($col . $numrow)->applyFromArray($style_row);
            }
            $no++; $numrow++;
        }

        $widths = ['A'=>5,'B'=>12,'C'=>15,'D'=>15,'E'=>30,'F'=>25,'G'=>8,'H'=>18,'I'=>30];
        foreach ($widths as $col => $w) $excel->getActiveSheet()->getColumnDimension($col)->setWidth($w);
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
        $excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $excel->getActiveSheet(0)->setTitle('Retur KMT');
        $excel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Retur_KMT_' . $tahun . '.xlsx"');
        header('Cache-Control: max-age=0');
        $write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $write->save('php://output');
    }
}
