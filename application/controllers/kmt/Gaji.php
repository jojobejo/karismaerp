<?php
// ================================================================
// controllers/content/kmt/Gaji.php
// ================================================================
defined('BASEPATH') OR exit('No direct script access allowed');

class Gaji extends CI_Controller {

    private $bulan_cols = [
        'gaji_jan','gaji_feb','gaji_mar','gaji_apr','gaji_mei','gaji_jun',
        'gaji_jul','gaji_agu','gaji_sep','gaji_okt','gaji_nov','gaji_des'
    ];

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) redirect('login');
        // Hanya KADEP yang bisa akses gaji
        if ((int)$this->session->userdata('lv') > 1) {
            $this->session->set_flashdata('error', 'Hanya KADEP yang dapat mengakses menu Gaji.');
            redirect('kmt/dashboard');
        }
        $this->load->model('M_Kmt');
        $this->load->library('form_validation');
    }

    public function index() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $id_wilayah = $this->input->get('id_wilayah') ?? null;

        $filter = ['tahun' => $tahun];
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list    = $this->M_Kmt->get_gaji_list($filter);
        $summary = $this->M_Kmt->get_total_gaji_per_bulan_wilayah($tahun, $id_wilayah ?: null);

        // Hitung total per karyawan
        foreach ($list as &$row) {
            $total = 0;
            foreach ($this->bulan_cols as $col) $total += (float)($row[$col] ?? 0);
            $row['total_gaji'] = $total;
        }

        $data = [
            'title'        => 'Data Gaji KMT CORN',
            'list'         => $list,
            'summary'      => $summary,
            'bulan_cols'   => $this->bulan_cols,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'tahun'        => $tahun,
            'id_wilayah'   => $id_wilayah,
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/gaji/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function tambah() {
        $data = [
            'title'        => 'Tambah Data Gaji',
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'bulan_cols'   => $this->bulan_cols,
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/gaji/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->form_validation->set_rules('nama',       'Nama',    'required');
        $this->form_validation->set_rules('id_wilayah', 'Wilayah', 'required|integer');
        $this->form_validation->set_rules('tahun',      'Tahun',   'required|integer');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }

        $insert = [
            'id_wilayah' => (int)$this->input->post('id_wilayah'),
            'nama'       => $this->input->post('nama'),
            'posisi'     => $this->input->post('posisi'),
            'status'     => $this->input->post('status'),
            'tgl_mulai'  => $this->input->post('tgl_mulai') ?: null,
            'tgl_resign' => $this->input->post('tgl_resign') ?: null,
            'tahun'      => (int)$this->input->post('tahun'),
            'created_by' => $this->session->userdata('id_user'),
        ];

        foreach ($this->bulan_cols as $col) {
            $val = $this->input->post($col);
            $insert[$col] = $val ? (float)str_replace('.','', $val) : 0;
        }

        if ($this->M_Kmt->insert_gaji($insert)) {
            $this->session->set_flashdata('success', 'Data gaji berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/gaji');
    }

    public function edit($id) {
        $row = $this->M_Kmt->get_gaji_by_id($id);
        if (!$row) { show_404(); return; }
        $data = [
            'title'        => 'Edit Data Gaji',
            'row'          => $row,
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'bulan_cols'   => $this->bulan_cols,
            'lv'     => (int)$this->session->userdata('lv'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/gaji/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update($id) {
        $update = [
            'id_wilayah' => (int)$this->input->post('id_wilayah'),
            'nama'       => $this->input->post('nama'),
            'posisi'     => $this->input->post('posisi'),
            'status'     => $this->input->post('status'),
            'tgl_mulai'  => $this->input->post('tgl_mulai') ?: null,
            'tgl_resign' => $this->input->post('tgl_resign') ?: null,
            'tahun'      => (int)$this->input->post('tahun'),
        ];

        foreach ($this->bulan_cols as $col) {
            $val = $this->input->post($col);
            $update[$col] = $val ? (float)str_replace('.','', $val) : 0;
        }

        if ($this->M_Kmt->update_gaji($id, $update)) {
            $this->session->set_flashdata('success', 'Data gaji berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/gaji');
    }

    public function hapus($id) {
        if ($this->M_Kmt->delete_gaji($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/gaji');
    }

    public function export() {
        include APPPATH . 'third_party/PHPExcel/PHPExcel.php';

        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $id_wilayah = $this->input->get('id_wilayah') ?? null;

        $filter = ['tahun' => $tahun];
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_gaji_list($filter);

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
        $excel->setActiveSheetIndex(0)->setCellValue('A1', 'Rekap Gaji KMT CORN - Tahun ' . $tahun);
        $excel->getActiveSheet()->mergeCells('A1:T1');
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $headers = ['NO','WILAYAH','NAMA','POSISI','STATUS','TGL MULAI','TGL RESIGN',
                    'JAN','FEB','MAR','APR','MEI','JUN','JUL','AGU','SEP','OKT','NOV','DES','TOTAL'];
        foreach ($headers as $i => $h) {
            $col = \PHPExcel_Cell::stringFromColumnIndex($i);
            $excel->setActiveSheetIndex(0)->setCellValue($col . '3', $h);
            $excel->getActiveSheet()->getStyle($col . '3')->applyFromArray($style_col);
        }

        $bulan_cols = ['gaji_jan','gaji_feb','gaji_mar','gaji_apr','gaji_mei','gaji_jun',
                    'gaji_jul','gaji_agu','gaji_sep','gaji_okt','gaji_nov','gaji_des'];

        $no = 1; $numrow = 4;
        foreach ($list as $row) {
            $total = 0;
            foreach ($bulan_cols as $bc) $total += (float)($row[$bc] ?? 0);

            $excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $no);
            $excel->setActiveSheetIndex(0)->setCellValue('B' . $numrow, $row['nama_wilayah'] ?? '-');
            $excel->setActiveSheetIndex(0)->setCellValue('C' . $numrow, $row['nama']);
            $excel->setActiveSheetIndex(0)->setCellValue('D' . $numrow, $row['posisi'] ?? '-');
            $excel->setActiveSheetIndex(0)->setCellValue('E' . $numrow, $row['status'] ?? '-');
            $excel->setActiveSheetIndex(0)->setCellValue('F' . $numrow, $row['tgl_mulai'] ?? '-');
            $excel->setActiveSheetIndex(0)->setCellValue('G' . $numrow, $row['tgl_resign'] ?? '-');
            $colIdx = 7; // H = index 7
            foreach ($bulan_cols as $bc) {
                $col = \PHPExcel_Cell::stringFromColumnIndex($colIdx++);
                $excel->setActiveSheetIndex(0)->setCellValue($col . $numrow, $row[$bc] ?? 0);
            }
            $col = \PHPExcel_Cell::stringFromColumnIndex($colIdx);
            $excel->setActiveSheetIndex(0)->setCellValue($col . $numrow, $total);

            for ($c = 0; $c <= 19; $c++) {
                $excel->getActiveSheet()->getStyle(\PHPExcel_Cell::stringFromColumnIndex($c) . $numrow)->applyFromArray($style_row);
            }
            $no++; $numrow++;
        }

        $widths = [5,15,25,15,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,15];
        foreach ($widths as $i => $w) {
            $excel->getActiveSheet()->getColumnDimension(\PHPExcel_Cell::stringFromColumnIndex($i))->setWidth($w);
        }
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
        $excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $excel->getActiveSheet(0)->setTitle('Gaji KMT');
        $excel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Gaji_KMT_' . $tahun . '.xlsx"');
        header('Cache-Control: max-age=0');
        $write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $write->save('php://output');
    }
}
