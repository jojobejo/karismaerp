<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Controller C_ReportJurnal
 * Menyajikan laporan jurnal akuntansi lengkap dengan kolom:
 * No. | Ref. | Date | No. Bukti | No. Bukti | No. Perk | Descriptions AKUN | Descriptions | Debit | Credit
 * serta fitur ekspor Excel (.xlsx) dan CSV.
 */
class C_ReportJurnal extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('logged_in')) {
            redirect('Auth');
            return;
        }
        $this->load->model('M_Journal');
    }

    /**
     * Tampilan Halaman Utama Report Jurnal
     */
    public function index()
    {
        $start_date   = $this->input->get('start_date')   ?: date('Y-m-01');
        $end_date     = $this->input->get('end_date')     ?: date('Y-m-d');
        $jenis        = $this->input->get('jenis')        ?: '';
        $kategori     = $this->input->get('kategori')     ?: '';
        $status_lunas = $this->input->get('status_lunas') ?: 'all_paid';

        // Ambil master jenis jurnal untuk filter
        $jenis_jurnals = [];
        if ($this->db->table_exists('tbkeu_jenis_jurnal')) {
            $jenis_jurnals = $this->db->order_by('kode_jenis_jurnal', 'ASC')->get('tbkeu_jenis_jurnal')->result_array();
        }

        $data['page_title']    = 'KARISMA - REPORT JURNAL TRANSAKSI';
        $data['start_date']    = $start_date;
        $data['end_date']      = $end_date;
        $data['jenis']         = $jenis;
        $data['kategori']      = $kategori;
        $data['status_lunas']  = $status_lunas;
        $data['jenis_jurnals'] = $jenis_jurnals;

        $this->load->view('partial/main/header.php', $data);
        $this->load->view('content/keuangan/laporan/report_jurnal.php', $data);
        $this->load->view('partial/main/footer.php');
    }

    /**
     * API AJAX untuk Memuat Data Jurnal Terfilter
     */
    public function get_data_json()
    {
        $start_date   = $this->input->post('start_date')   ?: $this->input->get('start_date')   ?: date('Y-m-01');
        $end_date     = $this->input->post('end_date')     ?: $this->input->get('end_date')     ?: date('Y-m-d');
        $jenis        = $this->input->post('jenis')        ?: $this->input->get('jenis')        ?: '';
        $kategori     = $this->input->post('kategori')     ?: $this->input->get('kategori')     ?: '';
        $status_lunas = $this->input->post('status_lunas') ?: $this->input->get('status_lunas') ?: 'all_paid';
        $keyword      = trim((string)($this->input->post('keyword') ?: $this->input->get('keyword') ?: ''));

        $rows = $this->_fetch_journal_records($start_date, $end_date, $jenis, $kategori, $status_lunas, $keyword);

        $formatted = [];
        $total_debit = 0.0;
        $total_kredit = 0.0;
        $no = 1;

        foreach ($rows as $r) {
            $deb = (float)$r['debit'];
            $kre = (float)$r['kredit'];
            $total_debit  += $deb;
            $total_kredit += $kre;

            // Format Ref: CR (Cash Receipt), CD (Cash Disbursement), JU (Jurnal Umum)
            $kode_jenis = strtoupper(trim((string)$r['kode_jenis_jurnal']));
            $ref = 'CR';
            if ($kode_jenis === 'KK' || $kode_jenis === 'CD' || $kode_jenis === 'BK') {
                $ref = 'CD';
            } elseif ($kode_jenis === 'JU' || $kode_jenis === 'GL') {
                $ref = 'JU';
            } elseif ($kode_jenis === 'MR' || $kode_jenis === 'KM') {
                $ref = 'CR';
            } else {
                $ref = !empty($kode_jenis) ? $kode_jenis : 'CR';
            }

            // Format No. Perk: 5 digit -> 3-2 (contoh: 11010 -> 110-10, 13099 -> 130-99)
            $no_perk = $this->_format_no_perk($r['kode_akun']);

            // Kategori Jurnal (Jurnal Asli Z, Jurnal Acak Q, Jurnal A, Jurnal Umum)
            $kat_info = $this->_detect_kategori_jurnal($r);

            $formatted[] = [
                'no'               => $no++,
                'id_jurnal'        => (int)$r['id_jurnal'],
                'ref'              => $ref,
                'date'             => !empty($r['tanggal_transaksi']) ? date('d/m/Y', strtotime($r['tanggal_transaksi'])) : '-',
                'raw_date'         => $r['tanggal_transaksi'],
                'no_bukti_prefix'  => !empty($kode_jenis) ? $kode_jenis : substr((string)$r['nomor_jurnal'], 0, 2),
                'no_bukti'         => $r['nomor_jurnal'],
                'no_perk'          => $no_perk,
                'desc_akun'        => $r['nama_akun'] ?: '-',
                'description'      => $r['keterangan_detail'] ?: ($r['keterangan_jurnal'] ?: '-'),
                'source_no'        => $r['source_no'] ?: '-',
                'kategori_code'    => $kat_info['code'],
                'kategori'         => $kat_info['kategori'],
                'short_label'      => $kat_info['short_label'],
                'badge_class'      => $kat_info['badge_class'],
                'excel_color'      => $kat_info['excel_color'],
                'debit'            => $deb,
                'credit'           => $kre,
                'debit_formatted'  => $deb > 0 ? number_format($deb, 2, ',', '.') : '-',
                'credit_formatted' => $kre > 0 ? number_format($kre, 2, ',', '.') : '-',
                'status'           => $r['status'] ?: 'POSTED'
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'                => true,
                'data'                  => $formatted,
                'total_rows'            => count($formatted),
                'total_debit'           => $total_debit,
                'total_kredit'          => $total_kredit,
                'total_debit_formatted' => number_format($total_debit, 2, ',', '.'),
                'total_kredit_formatted'=> number_format($total_kredit, 2, ',', '.')
            ]));
    }

    /**
     * Export ke Excel (.xlsx) dengan Format Kolom Sesuai Permintaan
     */
    public function export_excel()
    {
        @error_reporting(0);
        if (file_exists(FCPATH . 'vendor/autoload.php')) {
            require_once FCPATH . 'vendor/autoload.php';
        }

        $start_date   = $this->input->get('start_date')   ?: date('Y-m-01');
        $end_date     = $this->input->get('end_date')     ?: date('Y-m-d');
        $jenis        = $this->input->get('jenis')        ?: '';
        $kategori     = $this->input->get('kategori')     ?: '';
        $status_lunas = $this->input->get('status_lunas') ?: 'all_paid';
        $keyword      = trim((string)($this->input->get('keyword') ?: ''));

        $rows = $this->_fetch_journal_records($start_date, $end_date, $jenis, $kategori, $status_lunas, $keyword);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report Jurnal');

        // Judul Laporan
        $sheet->setCellValue('A1', 'KARISMA ERP - REPORT JURNAL TRANSAKSI');
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($start_date)) . ' s/d ' . date('d/m/Y', strtotime($end_date)));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

        // Panduan Warna (Legend) agar Mudah Membedakan Jurnal Asli Z, Q Acak, dan Jurnal A
        $sheet->setCellValue('A3', 'PANDUAN WARNA:');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle('A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('B3', '■ Jurnal Asli (Z - Faktur Induk)');
        $sheet->mergeCells('B3:C3');
        $sheet->getStyle('B3:C3')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('1B4F72');
        $sheet->getStyle('B3:C3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F4F8');
        $sheet->getStyle('B3:C3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('A9CCE3');
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('E3', '■ Jurnal Acak (Q - Faktur Pecahan H)');
        $sheet->mergeCells('E3:F3');
        $sheet->getStyle('E3:F3')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('7D6608');
        $sheet->getStyle('E3:F3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFDE7');
        $sheet->getStyle('E3:F3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('F9E79F');
        $sheet->getStyle('E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('H3', '■ Jurnal (A) Kas Masuk (Potongan KIU / Tax)');
        $sheet->mergeCells('H3:J3');
        $sheet->getStyle('H3:J3')->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('78281F');
        $sheet->getStyle('H3:J3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFEBEE');
        $sheet->getStyle('H3:J3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('F5B7B1');
        $sheet->getStyle('H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(3)->setRowHeight(22);

        // Header Kolom:
        // No. | Ref. | Date | No. Bukti | No. Bukti | Kategori Jurnal | Ref Faktur | No. Perk | Descriptions AKUN | Descriptions | Debit | Credit
        $headers = [
            'A5' => 'No.',
            'B5' => 'Ref.',
            'C5' => 'Date',
            'D5' => 'No. Bukti',
            'E5' => 'No. Bukti',
            'F5' => 'Kategori Jurnal',
            'G5' => 'Ref Faktur',
            'H5' => 'No. Perk',
            'I5' => 'Descriptions AKUN',
            'J5' => 'Descriptions',
            'K5' => 'Debit',
            'L5' => 'Credit'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => '000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D6DBDF']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'A6ACAF']
                ]
            ]
        ];
        $sheet->getStyle('A5:L5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        $rowIdx = 6;
        $no = 1;
        $total_debit = 0.0;
        $total_kredit = 0.0;

        foreach ($rows as $r) {
            $deb = (float)$r['debit'];
            $kre = (float)$r['kredit'];
            $total_debit  += $deb;
            $total_kredit += $kre;

            $kode_jenis = strtoupper(trim((string)$r['kode_jenis_jurnal']));
            $ref = 'CR';
            if ($kode_jenis === 'KK' || $kode_jenis === 'CD' || $kode_jenis === 'BK') {
                $ref = 'CD';
            } elseif ($kode_jenis === 'JU' || $kode_jenis === 'GL') {
                $ref = 'JU';
            } elseif ($kode_jenis === 'MR' || $kode_jenis === 'KM') {
                $ref = 'CR';
            } else {
                $ref = !empty($kode_jenis) ? $kode_jenis : 'CR';
            }

            $no_perk = $this->_format_no_perk($r['kode_akun']);
            $dateStr = !empty($r['tanggal_transaksi']) ? date('d/m/Y', strtotime($r['tanggal_transaksi'])) : '-';
            $prefix = !empty($kode_jenis) ? $kode_jenis : substr((string)$r['nomor_jurnal'], 0, 2);
            $raw_desc = $r['keterangan_detail'] ?: ($r['keterangan_jurnal'] ?: '-');

            // Deteksi Kategori Jurnal
            $kat_info = $this->_detect_kategori_jurnal($r);
            $formatted_desc = $raw_desc;

            $sheet->setCellValue('A' . $rowIdx, $no++);
            $sheet->setCellValue('B' . $rowIdx, $ref);
            $sheet->setCellValue('C' . $rowIdx, $dateStr);
            $sheet->setCellValue('D' . $rowIdx, $prefix);
            $sheet->setCellValue('E' . $rowIdx, $r['nomor_jurnal']);
            $sheet->setCellValue('F' . $rowIdx, $kat_info['kategori']);
            $sheet->setCellValue('G' . $rowIdx, $r['source_no'] ?: '-');
            $sheet->setCellValueExplicit('H' . $rowIdx, $no_perk, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('I' . $rowIdx, $r['nama_akun'] ?: '-');
            $sheet->setCellValue('J' . $rowIdx, $formatted_desc);
            $sheet->setCellValue('K' . $rowIdx, $deb);
            $sheet->setCellValue('L' . $rowIdx, $kre);

            // Styling warna latar baris sesuai jenis jurnal
            $bgColor = $kat_info['excel_color'] ?? 'FFFFFF';
            if ($bgColor !== 'FFFFFF') {
                $sheet->getStyle('A' . $rowIdx . ':L' . $rowIdx)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($bgColor);
            }

            // Highlight khusus kolom Kategori Jurnal
            $sheet->getStyle('F' . $rowIdx)->getFont()->setBold(true)->getColor()->setRGB($kat_info['excel_text_color']);

            // Alignment
            $sheet->getStyle('A' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('L' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');

            $rowIdx++;
        }

        // Baris Total
        $sheet->setCellValue('A' . $rowIdx, 'TOTAL KESELURUHAN');
        $sheet->mergeCells('A' . $rowIdx . ':J' . $rowIdx);
        $sheet->setCellValue('K' . $rowIdx, $total_debit);
        $sheet->setCellValue('L' . $rowIdx, $total_kredit);

        $sheet->getStyle('A' . $rowIdx . ':L' . $rowIdx)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('K' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('L' . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $rowIdx . ':L' . $rowIdx)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EAEDED');

        // Border seluruh tabel
        $contentRange = 'A5:L' . $rowIdx;
        $sheet->getStyle($contentRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('BDC3C7');

        // AutoFilter dari baris header agar user bisa memfilter Kategori di Excel
        $sheet->setAutoFilter('A5:L' . ($rowIdx - 1));

        // Auto width kolom
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Report_Jurnal_' . date('Ymd', strtotime($start_date)) . '_' . date('Ymd', strtotime($end_date)) . '.xlsx';

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Export ke format CSV
     */
    public function export_csv()
    {
        @error_reporting(0);
        $start_date   = $this->input->get('start_date')   ?: date('Y-m-01');
        $end_date     = $this->input->get('end_date')     ?: date('Y-m-d');
        $jenis        = $this->input->get('jenis')        ?: '';
        $kategori     = $this->input->get('kategori')     ?: '';
        $status_lunas = $this->input->get('status_lunas') ?: 'all_paid';
        $keyword      = trim((string)($this->input->get('keyword') ?: ''));

        $rows = $this->_fetch_journal_records($start_date, $end_date, $jenis, $kategori, $status_lunas, $keyword);

        $filename = 'Report_Jurnal_' . date('Ymd', strtotime($start_date)) . '_' . date('Ymd', strtotime($end_date)) . '.csv';

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'No.',
            'Ref.',
            'Date',
            'No. Bukti Prefix',
            'No. Bukti Jurnal',
            'Kategori Jurnal',
            'Ref Faktur',
            'No. Perk',
            'Descriptions AKUN',
            'Descriptions',
            'Debit',
            'Credit'
        ]);

        $no = 1;
        foreach ($rows as $r) {
            $kode_jenis = strtoupper(trim((string)$r['kode_jenis_jurnal']));
            $ref = 'CR';
            if ($kode_jenis === 'KK' || $kode_jenis === 'CD' || $kode_jenis === 'BK') {
                $ref = 'CD';
            } elseif ($kode_jenis === 'JU' || $kode_jenis === 'GL') {
                $ref = 'JU';
            } elseif ($kode_jenis === 'MR' || $kode_jenis === 'KM') {
                $ref = 'CR';
            } else {
                $ref = !empty($kode_jenis) ? $kode_jenis : 'CR';
            }

            $no_perk = $this->_format_no_perk($r['kode_akun']);
            $dateStr = !empty($r['tanggal_transaksi']) ? date('d/m/Y', strtotime($r['tanggal_transaksi'])) : '-';
            $prefix = !empty($kode_jenis) ? $kode_jenis : substr((string)$r['nomor_jurnal'], 0, 2);
            $raw_desc = $r['keterangan_detail'] ?: ($r['keterangan_jurnal'] ?: '-');

            $kat_info = $this->_detect_kategori_jurnal($r);
            $formatted_desc = $raw_desc;

            fputcsv($output, [
                $no++,
                $ref,
                $dateStr,
                $prefix,
                $r['nomor_jurnal'],
                $kat_info['kategori'],
                $r['source_no'] ?: '-',
                $no_perk,
                $r['nama_akun'] ?: '-',
                $formatted_desc,
                (float)$r['debit'],
                (float)$r['kredit']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Query data jurnal dari database
     */
    private function _fetch_journal_records($start_date, $end_date, $jenis = '', $kategori = '', $status_lunas = 'all_paid', $keyword = '')
    {
        $this->db->select('
            j.id_jurnal,
            j.nomor_jurnal,
            j.tanggal_transaksi,
            j.keterangan AS keterangan_jurnal,
            j.source_module,
            j.source_type,
            j.source_id,
            j.source_no,
            j.status,
            jj.kode_jenis_jurnal,
            jj.nama_jenis_jurnal,
            d.id_jurnal_detail,
            d.nomor_baris,
            d.id_akun,
            d.keterangan AS keterangan_detail,
            d.debit,
            d.kredit,
            a.kode_akun,
            a.nama_akun
        ');
        $this->db->from('tbkeu_jurnal j');
        $this->db->join('tbkeu_jenis_jurnal jj', 'jj.id_jenis_jurnal = j.id_jenis_jurnal', 'left');
        $this->db->join('tbkeu_jurnal_detail d', 'd.id_jurnal = j.id_jurnal', 'inner');
        $this->db->join('tbkeu_akun a', 'a.id_akun = d.id_akun', 'left');

        // Filter Status Pelunasan:
        // all_paid: Setiap faktur yang sudah pernah ada pembayaran (total_pembayaran > 0)
        // lunas: Hanya faktur yang sudah lunas 100% (total_pembayaran >= total_tagihan)
        // sebagian: Faktur yang baru dibayar sebagian (total_pembayaran < total_tagihan AND total_pembayaran > 0)
        $where_lunas = "WHERE fp.total_pembayaran > 0";
        if ($status_lunas === 'lunas') {
            $where_lunas = "WHERE fp.total_pembayaran >= (ft.total_tagihan - 0.01) AND ft.total_tagihan > 0";
        } elseif ($status_lunas === 'sebagian') {
            $where_lunas = "WHERE fp.total_pembayaran < (ft.total_tagihan - 0.01) AND fp.total_pembayaran > 0";
        }

        $subquery_lunas_z = "
            SELECT f.no_faktur
            FROM tbso_faktur_penjualan f
            JOIN (
                SELECT id_faktur, COALESCE(SUM(total_harga), 0) AS total_tagihan
                FROM tbso_faktur_detail
                GROUP BY id_faktur
            ) ft ON ft.id_faktur = f.id_faktur
            JOIN (
                SELECT id_faktur,
                       COALESCE(SUM(
                           CASE
                               WHEN COALESCE(status, 'POSTED') IN ('UNPOST', 'DRAFT') THEN 0
                               WHEN COALESCE(status_bg, 'not_bg') = 'pending' THEN 0
                               WHEN COALESCE(status_kasir, 'valid') = 'pending_kasir' THEN 0
                               ELSE (jumlah_pembayaran + jumlah_diskon)
                           END
                       ), 0) AS total_pembayaran
                FROM tbkeu_pembayaran_faktur
                GROUP BY id_faktur
            ) fp ON fp.id_faktur = f.id_faktur
            {$where_lunas}
        ";

        $subquery_lunas_h = "
            SELECT p.no_faktur
            FROM tbso_faktur_z_pecah p
            JOIN ({$subquery_lunas_z}) lz ON lz.no_faktur = p.parent_no_faktur
        ";

        $this->db->where("(
            (j.source_no LIKE 'Z%' AND j.source_no IN ({$subquery_lunas_z}))
            OR
            (j.source_no LIKE 'H%' AND j.source_no IN ({$subquery_lunas_h}))
            OR
            (j.source_no NOT LIKE 'Z%' AND j.source_no NOT LIKE 'H%')
        )", null, false);

        if (!empty($start_date)) {
            $this->db->where('j.tanggal_transaksi >=', $start_date);
        }
        if (!empty($end_date)) {
            $this->db->where('j.tanggal_transaksi <=', $end_date);
        }

        if (!empty($jenis)) {
            $this->db->where('jj.kode_jenis_jurnal', $jenis);
        }

        if (!empty($kategori)) {
            if ($kategori === 'z_asli') {
                $this->db->like('j.source_no', 'Z', 'after');
                $this->db->not_like('j.keterangan', 'PND u/Pot');
            } elseif ($kategori === 'q_acak') {
                $this->db->like('j.source_no', 'H', 'after');
            } elseif ($kategori === 'a_pot') {
                $this->db->like('j.keterangan', 'PND u/Pot');
            }
        }

        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('j.nomor_jurnal', $keyword);
            $this->db->or_like('j.keterangan', $keyword);
            $this->db->or_like('d.keterangan', $keyword);
            $this->db->or_like('a.kode_akun', $keyword);
            $this->db->or_like('a.nama_akun', $keyword);
            $this->db->or_like('j.source_no', $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('j.tanggal_transaksi', 'ASC');
        $this->db->order_by('j.id_jurnal', 'ASC');
        $this->db->order_by('d.nomor_baris', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Format Kode Akun 5 Digit menjadi Standar 3-2 (misal 11010 -> 110-10)
     */
    private function _format_no_perk($kode)
    {
        $clean = trim((string)$kode);
        if (strpos($clean, '-') !== false) {
            return $clean;
        }
        if (strlen($clean) === 5) {
            return substr($clean, 0, 3) . '-' . substr($clean, 3, 2);
        }
        return $clean ?: '-';
    }

    /**
     * Deteksi Kategori Jurnal (Jurnal Asli Z, Jurnal Acak Q, Jurnal A Kas Masuk, Jurnal Umum)
     */
    private function _detect_kategori_jurnal($r)
    {
        $src_no = strtoupper(trim((string)($r['source_no'] ?? '')));
        $ket    = (string)($r['keterangan_jurnal'] ?? '');
        $ket_d  = (string)($r['keterangan_detail'] ?? '');
        $prefix = strtoupper(trim((string)($r['kode_jenis_jurnal'] ?? '')));

        // 1. Jurnal A (Kas Masuk Potongan/Tax Penjualan)
        if (stripos($ket, 'PND u/Pot') !== false || stripos($ket_d, 'PND u/Pot') !== false || ($prefix === 'KM' && (stripos($ket, 'Tax') !== false || stripos($ket, 'Pot') !== false))) {
            return [
                'code'             => 'a_pot',
                'kategori'         => 'Jurnal (A) Kas Masuk',
                'short_label'      => '[JURNAL A]',
                'badge_class'      => 'badge-danger',
                'excel_color'      => 'FFEBEE', // Soft Pink / Red
                'excel_text_color' => '78281F'
            ];
        }

        // 2. Jurnal Acak Q (Faktur Pecahan H)
        if (substr($src_no, 0, 1) === 'H' || stripos($ket, 'Pecahan') !== false) {
            return [
                'code'             => 'q_acak',
                'kategori'         => 'Jurnal Acak (Q) Pecahan',
                'short_label'      => '[JURNAL Q ACAK]',
                'badge_class'      => 'badge-warning text-dark',
                'excel_color'      => 'FFFDE7', // Soft Yellow / Amber
                'excel_text_color' => '7D6608'
            ];
        }

        // 3. Jurnal Asli Z (Faktur Induk Z)
        if (substr($src_no, 0, 1) === 'Z') {
            return [
                'code'             => 'z_asli',
                'kategori'         => 'Jurnal Asli (Z)',
                'short_label'      => '[JURNAL ASLI Z]',
                'badge_class'      => 'badge-primary',
                'excel_color'      => 'E8F4F8', // Soft Blue / Cyan
                'excel_text_color' => '1B4F72'
            ];
        }

        return [
            'code'             => 'umum',
            'kategori'         => 'Jurnal ' . ($prefix ?: 'Umum'),
            'short_label'      => '[JURNAL ' . ($prefix ?: 'UMUM') . ']',
            'badge_class'      => 'badge-secondary',
            'excel_color'      => 'FFFFFF',
            'excel_text_color' => '000000'
        ];
    }
}

