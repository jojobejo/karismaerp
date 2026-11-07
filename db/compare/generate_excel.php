<?php
/**
 * Skrip untuk menggenerate laporan perbandingan database dalam format .xlsx
 * Menggunakan ZipArchive PHP, tanpa library eksternal.
 */

class SimpleXLSXGen {
    private $sheets = [];
    
    // Menambahkan sheet ke dalam Excel
    public function addSheet($name, $data) {
        $this->sheets[] = ['name' => $name, 'data' => $data];
    }
    
    // Menyimpan file .xlsx ke path yang ditentukan
    public function save($filename) {
        $zip = new ZipArchive();
        if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            die("Error: Tidak dapat membuat file XLSX di $filename");
        }
        
        $zip->addFromString('[Content_Types].xml', $this->buildContentTypes());
        $zip->addFromString('_rels/.rels', $this->buildRels());
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRels());
        $zip->addFromString('xl/styles.xml', $this->buildStyles());
        
        foreach ($this->sheets as $index => $sheet) {
            $sheetId = $index + 1;
            $zip->addFromString("xl/worksheets/sheet{$sheetId}.xml", $this->buildWorksheet($sheet['data']));
        }
        
        $zip->close();
    }
    
    private function buildContentTypes() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $xml .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $xml .= '<Default Extension="xml" ContentType="application/xml"/>';
        $xml .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $xml .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        foreach ($this->sheets as $index => $sheet) {
            $sheetId = $index + 1;
            $xml .= '<Override PartName="/xl/worksheets/sheet'.$sheetId.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $xml .= '</Types>';
        return $xml;
    }
    
    private function buildRels() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $xml .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
        $xml .= '</Relationships>';
        return $xml;
    }
    
    private function buildWorkbook() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $xml .= '<sheets>';
        foreach ($this->sheets as $index => $sheet) {
            $sheetId = $index + 1;
            $name = htmlspecialchars($sheet['name'], ENT_XML1 | ENT_QUOTES);
            $xml .= '<sheet name="'.$name.'" sheetId="'.$sheetId.'" r:id="rId'.$sheetId.'"/>';
        }
        $xml .= '</sheets>';
        $xml .= '</workbook>';
        return $xml;
    }
    
    private function buildWorkbookRels() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach ($this->sheets as $index => $sheet) {
            $sheetId = $index + 1;
            $xml .= '<Relationship Id="rId'.$sheetId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$sheetId.'.xml"/>';
        }
        $stylesId = count($this->sheets) + 1;
        $xml .= '<Relationship Id="rId'.$stylesId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        $xml .= '</Relationships>';
        return $xml;
    }
    
    private function buildStyles() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        // Fonts: 0 = Normal, 1 = Header (Bold, Putih)
        $xml .= '<fonts count="2">';
        $xml .= '<font><sz val="11"/><name val="Calibri"/></font>';
        $xml .= '<font><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><b/></font>';
        $xml .= '</fonts>';
        // Fills: 0 = None, 1 = Gray125, 2 = Biru Tua (Header), 3 = Abu-abu muda (Alt Row)
        $xml .= '<fills count="4">';
        $xml .= '<fill><patternFill patternType="none"/></fill>';
        $xml .= '<fill><patternFill patternType="gray125"/></fill>';
        $xml .= '<fill><patternFill patternType="solid"><fgColor rgb="FF1F4E78"/><bgColor indexed="64"/></patternFill></fill>';
        $xml .= '<fill><patternFill patternType="solid"><fgColor rgb="FFEEEEEE"/><bgColor indexed="64"/></patternFill></fill>';
        $xml .= '</fills>';
        $xml .= '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>';
        $xml .= '<cellXfs count="3">';
        $xml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'; // 0 = Normal
        $xml .= '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'; // 1 = Header
        $xml .= '<xf numFmtId="0" fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1"/>'; // 2 = Alt row
        $xml .= '</cellXfs>';
        $xml .= '</styleSheet>';
        return $xml;
    }
    
    private function buildWorksheet($data) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetData>';
        
        $rowNum = 1;
        foreach ($data as $rIndex => $row) {
            $xml .= '<row r="'.$rowNum.'">';
            $colNum = 1;
            foreach ($row as $cell) {
                // Tentukan styling: baris 1 header, baris genap alternate
                $s = 0;
                if ($rIndex === 0) {
                    $s = 1; // Header (Biru Tua)
                } elseif ($rIndex % 2 === 1) {
                    $s = 2; // Alternate (Abu-abu)
                }
                
                $colLetter = $this->getColumnLetter($colNum);
                $ref = $colLetter . $rowNum;
                $value = htmlspecialchars((string)$cell, ENT_XML1 | ENT_QUOTES);
                
                if (is_numeric($cell) && !preg_match('/^0[0-9]/', $cell)) {
                    $xml .= '<c r="'.$ref.'" s="'.$s.'"><v>'.$value.'</v></c>';
                } else {
                    $xml .= '<c r="'.$ref.'" s="'.$s.'" t="inlineStr"><is><t>'.$value.'</t></is></c>';
                }
                $colNum++;
            }
            $xml .= '</row>';
            $rowNum++;
        }
        
        $xml .= '</sheetData>';
        $xml .= '</worksheet>';
        return $xml;
    }
    
    private function getColumnLetter($n) {
        $letter = '';
        while ($n > 0) {
            $mod = ($n - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $n = intval(($n - $mod) / 26);
        }
        return $letter;
    }
}

// Persiapkan Data
$sheet1 = [
    ['Parameter', 'Keterangan'],
    ['Judul', 'Laporan Perbandingan Database KarismaERP'],
    ['Database 1', 'karismaerp_bram.sql (270 KB) - MariaDB/XAMPP'],
    ['Database 2', 'karismaerp_yoga.sql (268 KB) - MySQL 8.0+'],
    ['Tanggal', '8 Agustus 2026'],
    ['Total Tabel Bram', '264'],
    ['Total Tabel Yoga', '267'],
    ['Tabel Eksklusif Bram', '9'],
    ['Tabel Eksklusif Yoga', '12'],
    ['Tabel Sama', '255'],
    ['Tabel Beda Kolom', '11'],
    ['Catatan Utama', 'Perbedaan utama disebabkan oleh MariaDB vs MySQL 8.0+ (display width int(11) vs int, collation utf8mb4_general_ci vs utf8mb4_0900_ai_ci, dsb)']
];

$sheet2 = [
    ['No', 'Nama Tabel', 'Deskripsi', 'Jumlah Kolom'],
    [1, 'tb_kpi_history_bck', 'Backup History KPI', 17],
    [2, 'tb_loading_kk_bck', 'Backup Loading Kapal Kontainer', 25],
    [3, 'tb_loading_lk_bck', 'Backup Loading LK', 27],
    [4, 'tb_lpb_manual_log', 'Log LPB Manual', 11],
    [5, 'tblpb_faktur_pajak', 'Faktur Pajak LPB', 7],
    [6, 'tbpo_formula_variable', 'Variable Formula PO', 10],
    [7, 'tbpo_realisasi_detail_po_nk', 'Detail Realisasi PO NK', 19],
    [8, 'tbpo_realisasi_harganyata_log', 'Log Realisasi Harga Nyata', 8],
    [9, 'tbpo_realisasi_po_nk', 'Header Realisasi PO NK', 6]
];

$sheet3 = [
    ['No', 'Nama Tabel', 'Deskripsi', 'Jumlah Kolom'],
    [1, 'tb_kasbon', 'Pengajuan Kasbon', 9],
    [2, 'tb_pengajuan_od', 'Pengajuan Overdue', 24],
    [3, 'tb_pengajuan_od_faktur', 'Faktur Pengajuan OD', 7],
    [4, 'tb_ss_simulasi', 'Simulasi Suggestion System', 7],
    [5, 'tb_ss_verified', 'Verifikasi SS', 6],
    [6, 'tbkeu_kas_keluar', 'Kas Keluar', 14],
    [7, 'tbkeu_kas_keluar_detail', 'Detail Kas Keluar', 5],
    [8, 'tbkeu_kas_masuk', 'Kas Masuk', 14],
    [9, 'tbkeu_kas_masuk_detail', 'Detail Kas Masuk', 5],
    [10, 'tbpo_barang_akun', 'Mapping Barang ke Akun', 7],
    [11, 'tbso_cancel_partial_request', 'Request Cancel Partial SO', 9],
    [12, 'tbso_faktur_jurnal', 'Jurnal Faktur SO', 7]
];

$sheet4 = [
    ['No', 'Nama Tabel', 'Nama Kolom', 'Status', 'Tipe Data', 'Keterangan'],
    [1, 'tb_karyawan', 'faktur_prefix', 'Hanya Yoga', 'varchar(4)', 'Prefix faktur per karyawan'],
    [2, 'tb_lpb', 'tgl_perubahan_invoice', 'Hanya Bram', 'date', 'Tanggal perubahan invoice'],
    [3, 'tb_lpb', 'tgl_riil_invoice', 'Hanya Bram', 'date', 'Tanggal riil invoice'],
    [4, 'tb_lpb', 'source_type', 'Hanya Bram', 'varchar(20) DEFAULT \'PO\'', 'Tipe sumber'],
    [5, 'tb_lpb', 'manual_ref_no', 'Hanya Bram', 'varchar(50)', 'Referensi manual'],
    [6, 'tb_lpb_detail', 'sales_disc', 'Hanya Bram', 'decimal(15,2)', 'Diskon sales'],
    [7, 'tb_lpb_detail', 'cbd', 'Hanya Bram', 'decimal(15,2)', 'CBD'],
    [8, 'tb_lpb_detail', 'foc', 'Hanya Bram', 'decimal(15,2)', 'Free of Charge'],
    [9, 'tb_lpb_detail', 'insentif_cn', 'Hanya Bram', 'decimal(15,2)', 'Insentif CN'],
    [10, 'tb_lpb_detail', 'dpp', 'Hanya Bram', 'decimal(15,2)', 'Dasar Pengenaan Pajak'],
    [11, 'tb_lpb_detail', 'ppn_11', 'Hanya Bram', 'decimal(15,2)', 'PPN 11%'],
    [12, 'tb_lpb_detail', 'ppn_12', 'Hanya Bram', 'decimal(15,2)', 'PPN 12%'],
    [13, 'tb_lpb_detail', 'dpp_nilai_lain', 'Hanya Bram', 'decimal(15,2)', 'DPP Nilai Lain'],
    [14, 'tb_penilaian_karakter_assignment', 'bulan', 'Hanya Yoga', 'varchar(7)', 'Periode penilaian'],
    [15, 'tb_ss', 'tipe_ss', 'Hanya Yoga', 'enum(\'umum\',\'teknis\')', 'Tipe SS'],
    [16, 'tbkeu_pembayaran_faktur', 'jumlah_diskon', 'Hanya Yoga', 'decimal(16,2)', 'Jumlah diskon'],
    [17, 'tbkeu_pembayaran_faktur', 'cara_pembayaran', 'Hanya Yoga', 'varchar(50)', 'Cara pembayaran'],
    [18, 'tbkeu_pembayaran_faktur', 'no_bg', 'Hanya Yoga', 'varchar(50)', 'Nomor BG'],
    [19, 'tbkeu_pembayaran_faktur', 'nama_bank', 'Hanya Yoga', 'varchar(100)', 'Nama bank'],
    [20, 'tbkeu_pembayaran_faktur', 'status_kasir', 'Hanya Yoga', 'varchar(20)', 'Status kasir'],
    [21, 'tbkeu_pembayaran_faktur', 'kasir_approved_by', 'Hanya Yoga', 'varchar(100)', 'Kasir approver'],
    [22, 'tbkeu_pembayaran_faktur', 'kasir_approved_at', 'Hanya Yoga', 'datetime', 'Waktu approval'],
    [23, 'tbpo_barang', 'produsen', 'Hanya Bram', 'varchar(150)', 'Nama produsen'],
    [24, 'tbpo_barang', 'spesifikasi_merk', 'Hanya Bram', 'varchar(255)', 'Spesifikasi merk'],
    [25, 'tbpo_barang', 'golongan', 'Hanya Bram', 'varchar(100)', 'Golongan barang'],
    [26, 'tbpo_barang', 'kelompok', 'Hanya Bram', 'varchar(100)', 'Kelompok barang'],
    [27, 'tbpo_barang', 'komposisi', 'Hanya Bram', 'text', 'Komposisi'],
    [28, 'tbpo_barang', 'grup', 'Hanya Bram', 'varchar(100)', 'Grup barang'],
    [29, 'tbpo_barang', 'kubikasi', 'Hanya Yoga', 'decimal(12,6)', 'Kubikasi m3'],
    [30, 'tbpo_formula_result', 'id_variable', 'Hanya Yoga', 'int', 'FK Variable'],
    [31, 'tbpo_formula_result', 'variable_key', 'Hanya Yoga', 'varchar(100)', 'Key variabel'],
    [32, 'tbpo_formula_result', 'variable_label', 'Hanya Yoga', 'varchar(150)', 'Label variabel'],
    [33, 'tbpo_formula_result', 'input_type', 'Hanya Yoga', 'enum', 'Tipe input'],
    [34, 'tbpo_formula_result', 'unit', 'Hanya Yoga', 'varchar(50)', 'Satuan'],
    [35, 'tbpo_formula_result', 'default_value', 'Hanya Yoga', 'decimal(20,6)', 'Nilai default'],
    [36, 'tbpo_formula_result', 'is_required', 'Hanya Yoga', 'tinyint', 'Wajib diisi'],
    [37, 'tbpo_formula_result', 'sort_order', 'Hanya Yoga', 'int', 'Urutan'],
    [38, 'tbpo_po', 'tgl_perubahan_po', 'Hanya Bram', 'date', 'Tanggal perubahan PO'],
    [39, 'tbpo_po', 'top', 'Hanya Bram', 'int(11)', 'Term of Payment (hari)'],
    [40, 'tbrp_retur_penjualan_header', 'total_nilai_retur', 'Hanya Yoga', 'decimal(15,2)', 'Total nilai retur'],
    [41, 'tbrp_retur_penjualan_header', 'sisa_saldo_retur', 'Hanya Yoga', 'decimal(15,2)', 'Sisa saldo'],
    [42, 'tbrp_retur_penjualan_header', 'gudang_id', 'Hanya Yoga', 'int', 'FK Gudang'],
    [43, 'tbrp_retur_penjualan_header', '[27 kolom approval...]', 'Hanya Yoga', 'various', 'Multi-level approval retur'],
    [70, 'tbrp_spr_header', 'mngsc_by', 'Hanya Yoga', 'varchar(150)', 'Approval manager SC'],
    [71, 'tbrp_spr_header', 'mngsc_at', 'Hanya Yoga', 'datetime', 'Waktu approval'],
    [72, 'tbrp_spr_header', 'mngsc_catatan', 'Hanya Yoga', 'text', 'Catatan'],
    [73, 'tbrp_spr_header', 'kadepub_by', 'Hanya Yoga', 'varchar(150)', 'Approval kadep UB'],
    [74, 'tbrp_spr_header', 'kadepub_at', 'Hanya Yoga', 'datetime', 'Waktu approval'],
    [75, 'tbrp_spr_header', 'kadepub_catatan', 'Hanya Yoga', 'text', 'Catatan'],
    [76, 'tbrp_spr_header', 'admretur_by', 'Hanya Yoga', 'varchar(150)', 'Approval admin retur'],
    [77, 'tbrp_spr_header', 'admretur_at', 'Hanya Yoga', 'datetime', 'Waktu approval'],
    [78, 'tbrp_spr_header', 'admretur_catatan', 'Hanya Yoga', 'text', 'Catatan']
];

$sheet5 = [
    ['Daftar Catatan Teknis', 'Keterangan'],
    ['Perbedaan Platform', 'Bram menggunakan MariaDB (XAMPP), Yoga menggunakan MySQL 8.0+'],
    ['Collation', 'Bram utf8mb4_general_ci, Yoga utf8mb4_0900_ai_ci (tidak berdampak fungsional)'],
    ['Display Width', 'MariaDB masih menampilkan int(11), MySQL 8.0+ menghapusnya'],
    ['Default Values', 'MariaDB DEFAULT 0, MySQL DEFAULT \'0\' (tidak berdampak fungsional)'],
    ['Function Casing', 'current_timestamp() vs CURRENT_TIMESTAMP (tidak berdampak fungsional)'],
    ['Nullable Text', 'MariaDB text DEFAULT NULL, MySQL text (tidak berdampak fungsional)']
];

// Pastikan direktori tujuan ada
$outputDir = 'c:/xampp/htdocs/karismaerp/db/compare';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// Generate File
$excel = new SimpleXLSXGen();
$excel->addSheet('Ringkasan', $sheet1);
$excel->addSheet('Tabel Eksklusif Bram', $sheet2);
$excel->addSheet('Tabel Eksklusif Yoga', $sheet3);
$excel->addSheet('Perbedaan Kolom', $sheet4);
$excel->addSheet('Catatan Teknis', $sheet5);

$outputPath = $outputDir . '/DB_Comparison_Report.xlsx';
$excel->save($outputPath);

echo "Berhasil! File Excel telah di-generate di: " . $outputPath . "\n";
?>
