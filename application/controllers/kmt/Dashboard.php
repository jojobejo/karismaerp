<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();

        // Cek login
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $this->load->model('M_Kmt');
    }

    public function index() {
        $lv    = (int)$this->session->userdata('lv');
        $tahun = $this->input->get('tahun') ?? date('Y');

        // ABM (lv 3) paksa wilayah dari session, tidak bisa diubah via GET
        if ($lv === 3) {
            $id_wilayah = (int)$this->session->userdata('wilayah');
        } else {
            $id_wilayah = $this->input->get('id_wilayah');
            $id_wilayah = $id_wilayah ? (int)$id_wilayah : null;
        }

        $data['title']            = 'Dashboard KMT CORN';
        $data['tahun']            = $tahun;
        $data['id_wilayah']       = $id_wilayah;
        $data['lv']               = $lv;
        $data['wilayah_list']     = $this->M_Kmt->get_wilayah();
        $data['ytd']              = $this->M_Kmt->get_ytd($tahun, $id_wilayah);
        $data['summary']          = $this->M_Kmt->get_summary_cards($tahun, $id_wilayah);

        // ABM hanya lihat wilayahnya, selain ABM lihat semua
        $data['cost_per_wilayah'] = $this->M_Kmt->get_cost_per_hasil_wilayah($tahun, $id_wilayah);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    public function export() {
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $id_wilayah = $this->input->get('id_wilayah') ?? null;

        if ((int)$this->session->userdata('akses_lv') === 3) {
            $id_wilayah = (int)$this->session->userdata('id_wilayah');
        }
        $id_wilayah = $id_wilayah ? (int)$id_wilayah : null;

        $ytd             = $this->M_Kmt->get_ytd($tahun, $id_wilayah);
        $cost_per_wilayah= $this->M_Kmt->get_cost_per_hasil_wilayah($tahun);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ============================================================
        // SHEET 1 — YTD Bulanan
        // ============================================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('YTD Bulanan');

        // Judul
        $sheet1->setCellValue('A1', 'Dashboard YTD KMT CORN - Tahun ' . $tahun);
        $sheet1->mergeCells('A1:I1');
        $sheet1->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Header
        $headers = ['BULAN','OMSET','OPERASIONAL','DCA','PERALATAN','OTHERS','GAJI','TOTAL BIAYA','COST/HASIL (%)'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet1->setCellValue($col . '3', $h);
        }
        $sheet1->getStyle('A3:I3')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        // Data YTD
        $grand = array_fill_keys(['omset','operasional','dca','peralatan','others','gaji','total_biaya'], 0);
        $numrow = 4;
        foreach ($ytd as $row) {
            foreach (array_keys($grand) as $k) $grand[$k] += $row[$k];

            $sheet1->setCellValue('A' . $numrow, $row['bulan']);
            $sheet1->setCellValue('B' . $numrow, $row['omset']);
            $sheet1->setCellValue('C' . $numrow, $row['operasional']);
            $sheet1->setCellValue('D' . $numrow, $row['dca']);
            $sheet1->setCellValue('E' . $numrow, $row['peralatan']);
            $sheet1->setCellValue('F' . $numrow, $row['others']);
            $sheet1->setCellValue('G' . $numrow, $row['gaji']);
            $sheet1->setCellValue('H' . $numrow, $row['total_biaya']);
            $sheet1->setCellValue('I' . $numrow, $row['cost_per_hasil'] > 0 ? $row['cost_per_hasil'] . '%' : '-');

            $sheet1->getStyle('A' . $numrow . ':I' . $numrow)->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
                'alignment' => ['vertical' => 'center'],
            ]);
            // Format angka kolom B-H
            $sheet1->getStyle('B' . $numrow . ':H' . $numrow)
                ->getNumberFormat()->setFormatCode('#,##0');

            $numrow++;
        }

        // Baris TOTAL
        $cph_grand = $grand['omset'] > 0
            ? round($grand['total_biaya'] / $grand['omset'] * 100, 1) . '%'
            : '-';

        $sheet1->setCellValue('A' . $numrow, 'TOTAL');
        $sheet1->setCellValue('B' . $numrow, $grand['omset']);
        $sheet1->setCellValue('C' . $numrow, $grand['operasional']);
        $sheet1->setCellValue('D' . $numrow, $grand['dca']);
        $sheet1->setCellValue('E' . $numrow, $grand['peralatan']);
        $sheet1->setCellValue('F' . $numrow, $grand['others']);
        $sheet1->setCellValue('G' . $numrow, $grand['gaji']);
        $sheet1->setCellValue('H' . $numrow, $grand['total_biaya']);
        $sheet1->setCellValue('I' . $numrow, $cph_grand);
        $sheet1->getStyle('A' . $numrow . ':I' . $numrow)->applyFromArray([
            'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin']],
        ]);
        $sheet1->getStyle('B' . $numrow . ':H' . $numrow)
            ->getNumberFormat()->setFormatCode('#,##0');

        // Lebar kolom sheet1
        $sheet1->getColumnDimension('A')->setWidth(8);
        foreach (['B','C','D','E','F','G','H'] as $col) {
            $sheet1->getColumnDimension($col)->setWidth(18);
        }
        $sheet1->getColumnDimension('I')->setWidth(14);
        $sheet1->getPageSetup()->setOrientation('landscape');

        // ============================================================
        // SHEET 2 — Cost Per Hasil Per Wilayah
        // ============================================================
        $spreadsheet->createSheet();
        $sheet2 = $spreadsheet->getSheet(1);
        $sheet2->setTitle('Cost Per Wilayah');

        $sheet2->setCellValue('A1', 'Cost / Hasil Per Wilayah - Tahun ' . $tahun);
        $sheet2->mergeCells('A1:F1');
        $sheet2->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $headers2 = ['WILAYAH','Q1 (Jan-Mar)','Q2 (Apr-Jun)','Q3 (Jul-Sep)','Q4 (Okt-Des)','TOTAL'];
        foreach ($headers2 as $i => $h) {
            $col = chr(65 + $i);
            $sheet2->setCellValue($col . '3', $h);
        }
        $sheet2->getStyle('A3:F3')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
        ]);

        $numrow2 = 4;
        foreach ($cost_per_wilayah as $cw) {
            $sheet2->setCellValue('A' . $numrow2, $cw['wilayah']);
            $sheet2->setCellValue('B' . $numrow2, $cw['q1'] > 0 ? $cw['q1'] . '%' : '-');
            $sheet2->setCellValue('C' . $numrow2, $cw['q2'] > 0 ? $cw['q2'] . '%' : '-');
            $sheet2->setCellValue('D' . $numrow2, $cw['q3'] > 0 ? $cw['q3'] . '%' : '-');
            $sheet2->setCellValue('E' . $numrow2, $cw['q4'] > 0 ? $cw['q4'] . '%' : '-');
            $sheet2->setCellValue('F' . $numrow2, $cw['total'] > 0 ? $cw['total'] . '%' : '-');
            $sheet2->getStyle('A' . $numrow2 . ':F' . $numrow2)->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ]);
            $sheet2->getStyle('A' . $numrow2)->getAlignment()->setHorizontal('left');
            $numrow2++;
        }

        foreach (['A'=>25,'B'=>15,'C'=>15,'D'=>15,'E'=>15,'F'=>12] as $col => $w) {
            $sheet2->getColumnDimension($col)->setWidth($w);
        }
        $sheet2->getPageSetup()->setOrientation('landscape');

        // ============================================================
        // Output file
        // ============================================================
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Dashboard_YTD_KMT_' . $tahun . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}