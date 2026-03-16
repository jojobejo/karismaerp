<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Others extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) redirect('login');
        $this->load->model('M_Kmt');
        $this->load->library('form_validation');
    }

    private function get_id_wilayah_filter() {
        return ((int)$this->session->userdata('lv') === 3)
            ? (int)$this->session->userdata('id_wilayah') : null;
    }

    // ----------------------------------------------------------------
    // INDEX
    // ----------------------------------------------------------------
    public function index() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list        = $this->M_Kmt->get_others_list($filter);
        $total_biaya = array_sum(array_column($list, 'total_biaya'));

        $data = [
            'page_title'   => 'Data Others KMT CORN',
            'list'         => $list,
            'total_biaya'  => $total_biaya,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'tahun'        => $tahun,
            'bulan'        => $bulan,
            'id_wilayah'   => $id_wilayah,
            'nama_bulan'   => ['','Januari','Februari','Maret','April','Mei','Juni',
                               'Juli','Agustus','September','Oktober','November','Desember'],
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/others/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    // ----------------------------------------------------------------
    // TAMBAH
    // ----------------------------------------------------------------
    public function tambah() {
        $data = [
            'page_title'      => 'Tambah Data Others',
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'        => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('id_wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/others/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->form_validation->set_rules('tanggal',   'Tanggal', 'required');
        $this->form_validation->set_rules('id_wilayah','Wilayah', 'required|integer');
        $this->form_validation->set_rules('uraian',    'Uraian',  'required');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }

        $tgl = $this->input->post('tanggal');
        $insert = [
            'id_wilayah'  => (int)$this->input->post('id_wilayah'),
            'bulan'       => (int)date('m', strtotime($tgl)),
            'tahun'       => (int)date('Y', strtotime($tgl)),
            'tanggal'     => $tgl,
            'uraian'      => $this->input->post('uraian'),
            'total_biaya' => (float)str_replace('.', '', $this->input->post('total_biaya') ?? 0),
            'created_by'  => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_others($insert)) {
            $this->session->set_flashdata('success', 'Data others berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/others');
    }

    // ----------------------------------------------------------------
    // EDIT
    // ----------------------------------------------------------------
    public function edit($id) {
        $row = $this->M_Kmt->get_others_by_id($id);
        if (!$row) { show_404(); return; }

        $data = [
            'page_title'      => 'Edit Data Others',
            'row'             => $row,
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'        => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('id_wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/others/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update($id) {
        $tgl = $this->input->post('tanggal');
        $update = [
            'id_wilayah'  => (int)$this->input->post('id_wilayah'),
            'bulan'       => (int)date('m', strtotime($tgl)),
            'tahun'       => (int)date('Y', strtotime($tgl)),
            'tanggal'     => $tgl,
            'uraian'      => $this->input->post('uraian'),
            'total_biaya' => (float)str_replace('.', '', $this->input->post('total_biaya') ?? 0),
        ];

        if ($this->M_Kmt->update_others($id, $update)) {
            $this->session->set_flashdata('success', 'Data others berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/others');
    }

    // ----------------------------------------------------------------
    // HAPUS
    // ----------------------------------------------------------------
    public function hapus($id) {
        if ($this->M_Kmt->delete_others($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/others');
    }

    // ----------------------------------------------------------------
    // EXPORT EXCEL
    // ----------------------------------------------------------------
    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_others_list($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Others KMT');

        // Judul
        $sheet->setCellValue('A1', 'Rekap Others KMT CORN - Tahun ' . $tahun);
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Header
        $headers = ['NO', 'TANGGAL', 'WILAYAH', 'URAIAN', 'TOTAL BIAYA'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . '3', $h);
        }
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        // Data
        $no = 1; $numrow = 4;
        $grand_total = 0;
        foreach ($list as $row) {
            $grand_total += (float)$row['total_biaya'];
            $sheet->setCellValue('A' . $numrow, $no);
            $sheet->setCellValue('B' . $numrow, date('d/m/Y', strtotime($row['tanggal'])));
            $sheet->setCellValue('C' . $numrow, $row['nama_wilayah'] ?? '-');
            $sheet->setCellValue('D' . $numrow, $row['uraian']);
            $sheet->setCellValue('E' . $numrow, $row['total_biaya']);
            $sheet->getStyle('A' . $numrow . ':E' . $numrow)->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
                'alignment' => ['vertical' => 'center'],
            ]);
            $sheet->getStyle('E' . $numrow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A' . $numrow)->getAlignment()->setHorizontal('center');
            $no++; $numrow++;
        }

        // Baris total
        $sheet->setCellValue('D' . $numrow, 'TOTAL');
        $sheet->setCellValue('E' . $numrow, $grand_total);
        $sheet->getStyle('D' . $numrow . ':E' . $numrow)->applyFromArray([
            'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);
        $sheet->getStyle('E' . $numrow)->getNumberFormat()->setFormatCode('#,##0');

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(13);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(45);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getPageSetup()->setOrientation('landscape');

        $filename = 'Others_KMT_' . $tahun . ($bulan ? '_Bln' . $bulan : '') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}