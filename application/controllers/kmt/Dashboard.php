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
        $lv         = (int)$this->session->userdata('lv');
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bln_dari   = $this->input->get('bln_dari')   ?? 1;
        $bln_sampai = $this->input->get('bln_sampai') ?? 12;

        // Validasi range
        $bln_dari   = max(1,  min(12, (int)$bln_dari));
        $bln_sampai = max(1,  min(12, (int)$bln_sampai));
        if ($bln_dari > $bln_sampai) $bln_dari = $bln_sampai;

        if ($lv === 3) {
            $id_wilayah = (int)$this->session->userdata('wilayah');
        } else {
            $id_wilayah = $this->input->get('id_wilayah');
            $id_wilayah = $id_wilayah ? (int)$id_wilayah : null;
        }

        $data['page_title']       = 'Dashboard KMT CORN';
        $data['tahun']            = $tahun;
        $data['bln_dari']         = $bln_dari;
        $data['bln_sampai']       = $bln_sampai;
        $data['id_wilayah']       = $id_wilayah;
        $data['lv']               = $lv;
        $data['wilayah_list']     = $this->M_Kmt->get_wilayah();
        $data['ytd']              = $this->M_Kmt->get_ytd($tahun, $id_wilayah, $bln_dari, $bln_sampai);
        $data['summary']          = $this->M_Kmt->get_summary_cards($tahun, $id_wilayah, $bln_dari, $bln_sampai);
        $data['cost_per_wilayah'] = $this->M_Kmt->get_cost_per_hasil_wilayah($tahun, $id_wilayah, $bln_dari, $bln_sampai);

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/kmt/index', $data);
        $this->load->view('partial/main/footer.php');
    }

    // Ganti method export()
    public function export() {
        $lv         = (int)$this->session->userdata('lv');
        $tahun      = $this->input->get('tahun')      ?? date('Y');
        $bln_dari   = max(1,  min(12, (int)($this->input->get('bln_dari')   ?? 1)));
        $bln_sampai = max(1,  min(12, (int)($this->input->get('bln_sampai') ?? 12)));
        if ($bln_dari > $bln_sampai) $bln_dari = $bln_sampai;

        if ($lv === 3) {
            $id_wilayah = (int)$this->session->userdata('wilayah');
        } else {
            $id_wilayah = $this->input->get('id_wilayah');
            $id_wilayah = $id_wilayah ? (int)$id_wilayah : null;
        }

        $spreadsheet  = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $wilayah_list = $this->M_Kmt->get_wilayah();

        $nama_bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $label_periode = $nama_bulan[$bln_dari] . ' - ' . $nama_bulan[$bln_sampai] . ' ' . $tahun;

        $sheets = [['id' => null, 'nama' => 'Semua Wilayah']];
        foreach ($wilayah_list as $w) {
            $sheets[] = ['id' => (int)$w['id'], 'nama' => $w['nama_wilayah']];
        }

        if ($lv === 3) {
            $sheets = [];
            foreach ($wilayah_list as $w) {
                if ((int)$w['id'] === (int)$this->session->userdata('wilayah')) {
                    $sheets[] = ['id' => (int)$w['id'], 'nama' => $w['nama_wilayah']];
                }
            }
        }

        $sheetIndex = 0;
        foreach ($sheets as $s) {
            $ytd = $this->M_Kmt->get_ytd($tahun, $s['id'], $bln_dari, $bln_sampai);

            if ($sheetIndex === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $spreadsheet->createSheet();
                $sheet = $spreadsheet->getSheet($sheetIndex);
            }

            $sheet->setTitle(substr($s['nama'], 0, 31));

            $sheet->setCellValue('A1', 'Dashboard YTD KMT CORN - ' . $s['nama'] . ' - Periode: ' . $label_periode);
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1F3864']],
                'alignment' => ['horizontal' => 'center'],
            ]);

            $headers = ['BULAN','OMSET','OPERASIONAL','DCA','PERALATAN','OTHERS','GAJI','TOTAL BIAYA','COST/HASIL (%)'];
            foreach ($headers as $i => $h) {
                $sheet->setCellValue(chr(65 + $i) . '3', $h);
            }
            $sheet->getStyle('A3:I3')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
            ]);

            $grand  = array_fill_keys(['omset','operasional','dca','peralatan','others','gaji','total_biaya'], 0);
            $numrow = 4;

            foreach ($ytd as $row) {
                foreach (array_keys($grand) as $k) $grand[$k] += $row[$k];
                $bg = ($row['total_biaya'] > 0 || $row['omset'] > 0) ? 'FFFFFF' : 'F5F5F5';

                $sheet->setCellValue('A' . $numrow, $row['bulan']);
                $sheet->setCellValue('B' . $numrow, $row['omset']       ?: '');
                $sheet->setCellValue('C' . $numrow, $row['operasional'] ?: '');
                $sheet->setCellValue('D' . $numrow, $row['dca']         ?: '');
                $sheet->setCellValue('E' . $numrow, $row['peralatan']   ?: '');
                $sheet->setCellValue('F' . $numrow, $row['others']      ?: '');
                $sheet->setCellValue('G' . $numrow, $row['gaji']        ?: '');
                $sheet->setCellValue('H' . $numrow, $row['total_biaya'] ?: '');
                $sheet->setCellValue('I' . $numrow, $row['cost_per_hasil'] > 0 ? $row['cost_per_hasil'] . '%' : '-');
                $sheet->getStyle('A' . $numrow . ':I' . $numrow)->applyFromArray([
                    'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => $bg]],
                    'borders' => ['allBorders' => ['borderStyle' => 'thin']],
                ]);
                $sheet->getStyle('B' . $numrow . ':H' . $numrow)->getNumberFormat()->setFormatCode('#,##0');
                $numrow++;
            }

            $cph_grand = $grand['omset'] > 0
                ? round($grand['total_biaya'] / $grand['omset'] * 100, 1) . '%' : '-';

            $sheet->setCellValue('A' . $numrow, 'TOTAL');
            $sheet->setCellValue('B' . $numrow, $grand['omset']);
            $sheet->setCellValue('C' . $numrow, $grand['operasional']);
            $sheet->setCellValue('D' . $numrow, $grand['dca']);
            $sheet->setCellValue('E' . $numrow, $grand['peralatan']);
            $sheet->setCellValue('F' . $numrow, $grand['others']);
            $sheet->setCellValue('G' . $numrow, $grand['gaji']);
            $sheet->setCellValue('H' . $numrow, $grand['total_biaya']);
            $sheet->setCellValue('I' . $numrow, $cph_grand);
            $sheet->getStyle('A' . $numrow . ':I' . $numrow)->applyFromArray([
                'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
                'borders' => ['allBorders' => ['borderStyle' => 'thin']],
            ]);
            $sheet->getStyle('B' . $numrow . ':H' . $numrow)->getNumberFormat()->setFormatCode('#,##0');

            if ($s['id'] === null) {
                $numrow += 2;
                $cpw = $this->M_Kmt->get_cost_per_hasil_wilayah($tahun, null, $bln_dari, $bln_sampai);

                $sheet->setCellValue('A' . $numrow, 'COST / HASIL PER WILAYAH - ' . $label_periode);
                $sheet->mergeCells('A' . $numrow . ':F' . $numrow);
                $sheet->getStyle('A' . $numrow)->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F3864']],
                    'alignment' => ['horizontal' => 'center'],
                ]);
                $numrow++;

                foreach (['WILAYAH','Q1 (Jan-Mar)','Q2 (Apr-Jun)','Q3 (Jul-Sep)','Q4 (Okt-Des)','TOTAL'] as $i => $h) {
                    $sheet->setCellValue(chr(65 + $i) . $numrow, $h);
                }
                $sheet->getStyle('A' . $numrow . ':F' . $numrow)->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '374E6E']],
                    'borders'   => ['allBorders' => ['borderStyle' => 'thin']],
                ]);
                $numrow++;

                foreach ($cpw as $cw) {
                    $sheet->setCellValue('A' . $numrow, $cw['wilayah']);
                    $sheet->setCellValue('B' . $numrow, $cw['q1']    > 0 ? $cw['q1']    . '%' : '-');
                    $sheet->setCellValue('C' . $numrow, $cw['q2']    > 0 ? $cw['q2']    . '%' : '-');
                    $sheet->setCellValue('D' . $numrow, $cw['q3']    > 0 ? $cw['q3']    . '%' : '-');
                    $sheet->setCellValue('E' . $numrow, $cw['q4']    > 0 ? $cw['q4']    . '%' : '-');
                    $sheet->setCellValue('F' . $numrow, $cw['total'] > 0 ? $cw['total'] . '%' : '-');
                    $sheet->getStyle('A' . $numrow . ':F' . $numrow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => 'thin']],
                    ]);
                    $numrow++;
                }
            }

            $sheet->getColumnDimension('A')->setWidth(10);
            foreach (['B','C','D','E','F','G','H'] as $col) {
                $sheet->getColumnDimension($col)->setWidth(18);
            }
            $sheet->getColumnDimension('I')->setWidth(14);
            $sheet->getPageSetup()->setOrientation('landscape');
            $sheetIndex++;
        }

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'Dashboard_KMT_' . $tahun . '_' . $nama_bulan[$bln_dari] . '-' . $nama_bulan[$bln_sampai] . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}