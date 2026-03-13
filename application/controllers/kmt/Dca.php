<?php
// ================================================================
// controllers/content/kmt/Dca.php
// ================================================================
defined('BASEPATH') OR exit('No direct script access allowed');

class Dca extends CI_Controller {

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

        $list        = $this->M_Kmt->get_dca_list($filter);
        $total_biaya = array_sum(array_column($list, 'total_biaya'));

        $data = [
            'title'        => 'Data DCA KMT CORN',
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
        $this->load->view('content/kmt/dca/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function tambah() {
        $data = [
            'title'        => 'Tambah Data DCA',
            'wilayah_list' => $this->M_Kmt->get_wilayah(),
            'lv'     => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];
        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function simpan() {
        $this->form_validation->set_rules('tanggal_dca','Tanggal', 'required');
        $this->form_validation->set_rules('id_wilayah', 'Wilayah', 'required|integer');
        $this->form_validation->set_rules('uraian',     'Uraian',  'required');

        if ($this->form_validation->run() === FALSE) {
            $this->tambah(); return;
        }

        $tgl     = $this->input->post('tanggal_dca');
        $real    = (float)str_replace('.','', $this->input->post('real_biaya') ?? 0);
        $um      = (float)str_replace('.','', $this->input->post('um') ?? 0);
        $refund  = (float)str_replace('.','', $this->input->post('refund') ?? 0);

        $insert = [
            'tanggal_dca' => $tgl,
            'bulan'       => (int)date('m', strtotime($tgl)),
            'tahun'       => (int)date('Y', strtotime($tgl)),
            'id_wilayah'  => (int)$this->input->post('id_wilayah'),
            'abm'         => $this->input->post('abm'),
            'uraian'      => $this->input->post('uraian'),
            'um'          => $um,
            'refund'      => $refund,
            'real_biaya'  => $real,
            'total_biaya' => $real - $refund,
            'created_by'  => $this->session->userdata('id_user'),
        ];

        if ($this->M_Kmt->insert_dca($insert)) {
            $this->session->set_flashdata('success', 'Data DCA berhasil disimpan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data.');
        }
        redirect('kmt/dca');
    }

    public function edit($id) {
        $row = $this->M_Kmt->get_dca_by_id($id);
        if (!$row) { show_404(); return; }
        $data = [
            'title'           => 'Edit Data DCA',
            'row'             => $row,
            'wilayah_list'    => $this->M_Kmt->get_wilayah(),
            'lv'        => (int)$this->session->userdata('lv'),
            'id_wilayah_user' => $this->session->userdata('wilayah'),
        ];

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/dca/form', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function update($id) {
        $tgl    = $this->input->post('tanggal_dca');
        $real   = (float)str_replace('.','', $this->input->post('real_biaya') ?? 0);
        $refund = (float)str_replace('.','', $this->input->post('refund') ?? 0);

        $update = [
            'tanggal_dca' => $tgl,
            'bulan'       => (int)date('m', strtotime($tgl)),
            'tahun'       => (int)date('Y', strtotime($tgl)),
            'id_wilayah'  => (int)$this->input->post('id_wilayah'),
            'abm'         => $this->input->post('abm'),
            'uraian'      => $this->input->post('uraian'),
            'um'          => (float)str_replace('.','', $this->input->post('um') ?? 0),
            'refund'      => $refund,
            'real_biaya'  => $real,
            'total_biaya' => $real - $refund,
        ];

        if ($this->M_Kmt->update_dca($id, $update)) {
            $this->session->set_flashdata('success', 'Data DCA berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('kmt/dca');
    }

    public function hapus($id) {
        if ($this->M_Kmt->delete_dca($id)) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('kmt/dca');
    }

    public function export() {
        include APPPATH . 'third_party/PHPExcel/PHPExcel.php';

        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bulan      = $this->input->get('bulan')      ?? '';
        $id_wilayah = $this->input->get('id_wilayah') ?? $this->get_id_wilayah_filter();

        $filter = ['tahun' => $tahun];
        if ($bulan)      $filter['bulan']      = $bulan;
        if ($id_wilayah) $filter['id_wilayah'] = $id_wilayah;

        $list = $this->M_Kmt->get_dca_list($filter);

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
        $excel->setActiveSheetIndex(0)->setCellValue('A1', 'Rekap DCA KMT CORN - Tahun ' . $tahun);
        $excel->getActiveSheet()->mergeCells('A1:I1');
        $excel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $headers = ['NO','TANGGAL','WILAYAH','ABM','URAIAN','UM','REFUND','REAL BIAYA','TOTAL'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $excel->setActiveSheetIndex(0)->setCellValue($col . '3', $h);
            $excel->getActiveSheet()->getStyle($col . '3')->applyFromArray($style_col);
        }

        $no = 1; $numrow = 4;
        foreach ($list as $row) {
            $excel->setActiveSheetIndex(0)->setCellValue('A' . $numrow, $no);
            $excel->setActiveSheetIndex(0)->setCellValue('B' . $numrow, date('d/m/Y', strtotime($row['tanggal_dca'])));
            $excel->setActiveSheetIndex(0)->setCellValue('C' . $numrow, $row['nama_wilayah'] ?? '-');
            $excel->setActiveSheetIndex(0)->setCellValue('D' . $numrow, $row['abm'] ?? '-');
            $excel->setActiveSheetIndex(0)->setCellValue('E' . $numrow, $row['uraian']);
            $excel->setActiveSheetIndex(0)->setCellValue('F' . $numrow, $row['um']);
            $excel->setActiveSheetIndex(0)->setCellValue('G' . $numrow, $row['refund']);
            $excel->setActiveSheetIndex(0)->setCellValue('H' . $numrow, $row['real_biaya']);
            $excel->setActiveSheetIndex(0)->setCellValue('I' . $numrow, $row['total_biaya']);
            foreach (['A','B','C','D','E','F','G','H','I'] as $col) {
                $excel->getActiveSheet()->getStyle($col . $numrow)->applyFromArray($style_row);
            }
            $no++; $numrow++;
        }

        $widths = ['A'=>5,'B'=>12,'C'=>15,'D'=>20,'E'=>40,'F'=>15,'G'=>15,'H'=>15,'I'=>15];
        foreach ($widths as $col => $w) $excel->getActiveSheet()->getColumnDimension($col)->setWidth($w);
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
        $excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $excel->getActiveSheet(0)->setTitle('DCA KMT');
        $excel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="DCA_KMT_' . $tahun . '.xlsx"');
        header('Cache-Control: max-age=0');
        $write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $write->save('php://output');
    }
}
