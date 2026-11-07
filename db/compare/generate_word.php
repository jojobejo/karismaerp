<?php
// Skrip PHP untuk membuat laporan perbandingan database dalam format Word (.docx)
// Dibuat menggunakan ZipArchive dengan format OpenXML standar.

$dir = __DIR__;
$outputFile = $dir . '/DB_Comparison_Report.docx';

// Fungsi bantuan untuk header XML
function get_xml_header() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
}

// Fungsi untuk membuat paragraf Word
function p($text, $style = 'Normal', $bold = false) {
    $xml = '<w:p>';
    if ($style !== 'Normal') {
        $xml .= '<w:pPr><w:pStyle w:val="' . $style . '"/></w:pPr>';
    }
    $xml .= '<w:r>';
    if ($bold) {
        $xml .= '<w:rPr><w:b/></w:rPr>';
    }
    $xml .= '<w:t>' . htmlspecialchars($text) . '</w:t></w:r></w:p>';
    return $xml;
}

// Fungsi untuk membuat tabel Word
function table($headers, $rows) {
    $xml = '<w:tbl>';
    $xml .= '<w:tblPr><w:tblStyle w:val="TableGrid"/><w:tblW w:w="0" w:type="auto"/></w:tblPr>';
    
    // Header tabel
    $xml .= '<w:tr>';
    foreach ($headers as $h) {
        $xml .= '<w:tc><w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="D9D9D9"/></w:tcPr><w:p><w:r><w:rPr><w:b/></w:rPr><w:t>' . htmlspecialchars($h) . '</w:t></w:r></w:p></w:tc>';
    }
    $xml .= '</w:tr>';
    
    // Baris data tabel
    foreach ($rows as $row) {
        $xml .= '<w:tr>';
        foreach ($row as $cell) {
            $xml .= '<w:tc><w:p><w:r><w:t>' . htmlspecialchars($cell) . '</w:t></w:r></w:p></w:tc>';
        }
        $xml .= '</w:tr>';
    }
    
    $xml .= '</w:tbl>';
    return $xml;
}

// 1. Konten [Content_Types].xml
$content_types = get_xml_header() . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>';

// 2. Konten _rels/.rels
$rels = get_xml_header() . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>';

// 3. Konten word/_rels/document.xml.rels
$document_rels = get_xml_header() . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';

// 4. Konten word/styles.xml (menyertakan style untuk heading dan tabel border)
$styles = get_xml_header() . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults>
    <w:rPrDefault>
      <w:rPr>
        <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>
        <w:sz w:val="22"/>
        <w:szCs w:val="22"/>
      </w:rPr>
    </w:rPrDefault>
  </w:docDefaults>
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading1">
    <w:name w:val="heading 1"/>
    <w:basedOn w:val="Normal"/>
    <w:pPr>
      <w:spacing w:before="240" w:after="120"/>
    </w:pPr>
    <w:rPr>
      <w:b/>
      <w:color w:val="2E74B5"/>
      <w:sz w:val="32"/>
    </w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading2">
    <w:name w:val="heading 2"/>
    <w:basedOn w:val="Normal"/>
    <w:pPr>
      <w:spacing w:before="200" w:after="100"/>
    </w:pPr>
    <w:rPr>
      <w:b/>
      <w:color w:val="2E74B5"/>
      <w:sz w:val="28"/>
    </w:rPr>
  </w:style>
  <w:style w:type="table" w:styleId="TableGrid">
    <w:name w:val="Table Grid"/>
    <w:tblPr>
      <w:tblBorders>
        <w:top w:val="single" w:sz="4" w:space="0" w:color="auto"/>
        <w:left w:val="single" w:sz="4" w:space="0" w:color="auto"/>
        <w:bottom w:val="single" w:sz="4" w:space="0" w:color="auto"/>
        <w:right w:val="single" w:sz="4" w:space="0" w:color="auto"/>
        <w:insideH w:val="single" w:sz="4" w:space="0" w:color="auto"/>
        <w:insideV w:val="single" w:sz="4" w:space="0" w:color="auto"/>
      </w:tblBorders>
    </w:tblPr>
  </w:style>
</w:styles>';

// 5. Menyusun isi dokumen utama (word/document.xml)
$doc_body = "";
$doc_body .= p("LAPORAN PERBANDINGAN DATABASE KARISMAERP", "Heading1", true);
$doc_body .= p("Database 1: karismaerp_bram.sql (270 KB) - MariaDB/XAMPP");
$doc_body .= p("Database 2: karismaerp_yoga.sql (268 KB) - MySQL 8.0+");
$doc_body .= p("Tanggal: 8 Agustus 2026");
$doc_body .= p("");

$doc_body .= p("1. RINGKASAN EKSEKUTIF", "Heading1");
$doc_body .= table(["Metrik", "DB Bram", "DB Yoga", "Selisih"], [
    ["Total Tabel", "264", "267", "+3 (Yoga)"],
    ["Tabel Eksklusif", "9", "12", "-"],
    ["Tabel Sama", "255", "255", "-"],
    ["Tabel Beda Kolom", "11", "11", "-"]
]);
$doc_body .= p("Catatan penting: Perbedaan utama disebabkan platform berbeda (MariaDB vs MySQL 8.0+). Perbedaan sintaks (display width, collation, quoting) TIDAK berdampak fungsional.");
$doc_body .= p("");

$doc_body .= p("2. TABEL EKSKLUSIF DB BRAM (9 tabel)", "Heading1");
$doc_body .= table(["No", "Nama Tabel", "Deskripsi", "Jumlah Kolom"], [
    ["1", "tb_kpi_history_bck", "Backup History KPI", "17"],
    ["2", "tb_loading_kk_bck", "Backup Loading Kapal Kontainer", "25"],
    ["3", "tb_loading_lk_bck", "Backup Loading LK", "27"],
    ["4", "tb_lpb_manual_log", "Log LPB Manual", "11"],
    ["5", "tblpb_faktur_pajak", "Faktur Pajak LPB", "7"],
    ["6", "tbpo_formula_variable", "Variable Formula PO", "10"],
    ["7", "tbpo_realisasi_detail_po_nk", "Detail Realisasi PO NK", "19"],
    ["8", "tbpo_realisasi_harganyata_log", "Log Realisasi Harga Nyata", "8"],
    ["9", "tbpo_realisasi_po_nk", "Header Realisasi PO NK", "6"]
]);
$doc_body .= p("");

$doc_body .= p("3. TABEL EKSKLUSIF DB YOGA (12 tabel)", "Heading1");
$doc_body .= table(["No", "Nama Tabel", "Deskripsi", "Jumlah Kolom"], [
    ["1", "tb_kasbon", "Pengajuan Kasbon", "9"],
    ["2", "tb_pengajuan_od", "Pengajuan Overdue", "24"],
    ["3", "tb_pengajuan_od_faktur", "Faktur Pengajuan OD", "7"],
    ["4", "tb_ss_simulasi", "Simulasi Suggestion System", "7"],
    ["5", "tb_ss_verified", "Verifikasi SS", "6"],
    ["6", "tbkeu_kas_keluar", "Kas Keluar", "14"],
    ["7", "tbkeu_kas_keluar_detail", "Detail Kas Keluar", "5"],
    ["8", "tbkeu_kas_masuk", "Kas Masuk", "14"],
    ["9", "tbkeu_kas_masuk_detail", "Detail Kas Masuk", "5"],
    ["10", "tbpo_barang_akun", "Mapping Barang ke Akun", "7"],
    ["11", "tbso_cancel_partial_request", "Request Cancel Partial SO", "9"],
    ["12", "tbso_faktur_jurnal", "Jurnal Faktur SO", "7"]
]);
$doc_body .= p("");

$doc_body .= p("4. PERBEDAAN KOLOM PADA TABEL YANG SAMA (11 tabel)", "Heading1");

$doc_body .= p("4.1 tb_karyawan", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["faktur_prefix", "Hanya Yoga", "varchar(4)", "Prefix faktur per karyawan"]
]);
$doc_body .= p("");

$doc_body .= p("4.2 tb_lpb", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["tgl_perubahan_invoice", "Hanya Bram", "date", "Tanggal perubahan invoice"],
    ["tgl_riil_invoice", "Hanya Bram", "date", "Tanggal riil invoice"],
    ["source_type", "Hanya Bram", "varchar(20) DEFAULT 'PO'", "Tipe sumber"],
    ["manual_ref_no", "Hanya Bram", "varchar(50)", "Referensi manual"]
]);
$doc_body .= p("");

$doc_body .= p("4.3 tb_lpb_detail", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["sales_disc, cbd, foc, insentif_cn, dpp, ppn_11, ppn_12, dpp_nilai_lain", "Semua Hanya Bram", "decimal(15,2)", "Kolom perpajakan"]
]);
$doc_body .= p("");

$doc_body .= p("4.4 tb_penilaian_karakter_assignment", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["bulan", "Hanya Yoga", "varchar(7)", "Periode penilaian"]
]);
$doc_body .= p("");

$doc_body .= p("4.5 tb_ss", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["tipe_ss", "Hanya Yoga", "enum('umum','teknis')", "Tipe SS"]
]);
$doc_body .= p("");

$doc_body .= p("4.6 tbkeu_pembayaran_faktur", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["jumlah_diskon, cara_pembayaran, no_bg, nama_bank, status_kasir, kasir_approved_by, kasir_approved_at", "Semua Hanya Yoga", "various", "Fitur pembayaran extended"]
]);
$doc_body .= p("");

$doc_body .= p("4.7 tbpo_barang", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["produsen, spesifikasi_merk, golongan, kelompok, komposisi, grup", "Hanya Bram", "various", "Data tambahan barang"],
    ["kubikasi", "Hanya Yoga", "decimal(12,6)", "Kubikasi m3"]
]);
$doc_body .= p("");

$doc_body .= p("4.8 tbpo_formula_result", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["id_variable, variable_key, variable_label, input_type, unit, default_value, is_required, sort_order", "Semua Hanya Yoga", "various", "Di Bram ada di tabel terpisah tbpo_formula_variable"]
]);
$doc_body .= p("");

$doc_body .= p("4.9 tbpo_po", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["tgl_perubahan_po", "Hanya Bram", "date", "Tanggal perubahan PO"],
    ["top", "Hanya Bram", "int(11)", "Term of Payment"]
]);
$doc_body .= p("");

$doc_body .= p("4.10 tbrp_retur_penjualan_header", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["total_nilai_retur, sisa_saldo_retur, gudang_id", "Hanya Yoga", "various", "Data retur"],
    ["27 kolom approval chain (admretur, kadepub...)", "Semua Hanya Yoga", "various", "Multi-level approval"]
]);
$doc_body .= p("");

$doc_body .= p("4.11 tbrp_spr_header", "Heading2");
$doc_body .= table(["Kolom", "Status", "Tipe Data", "Deskripsi"], [
    ["mngsc_by, mngsc_at, mngsc_catatan", "Hanya Yoga", "various", "Approval manager SC"],
    ["kadepub_by, kadepub_at, kadepub_catatan", "Hanya Yoga", "various", "Approval kadep UB"],
    ["admretur_by, admretur_at, admretur_catatan", "Hanya Yoga", "various", "Approval admin retur"]
]);
$doc_body .= p("");

$doc_body .= p("5. CATATAN TEKNIS", "Heading1");
$doc_body .= p("Daftar perbedaan platform:");
$doc_body .= p("- Platform: Bram=MariaDB (XAMPP), Yoga=MySQL 8.0+");
$doc_body .= p("- Collation: utf8mb4_general_ci vs utf8mb4_0900_ai_ci");
$doc_body .= p("- Display Width: int(11) vs int");
$doc_body .= p("- Default Values: DEFAULT 0 vs DEFAULT '0'");
$doc_body .= p("- Function Casing: current_timestamp() vs CURRENT_TIMESTAMP");
$doc_body .= p("- Semua perbedaan di atas TIDAK berdampak fungsional");

$document_xml = get_xml_header() . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>' . $doc_body . '
    <w:sectPr>
      <w:pgSz w:w="11906" w:h="16838"/>
      <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="708" w:footer="708" w:gutter="0"/>
    </w:sectPr>
  </w:body>
</w:document>';

// Membuat ZIP archive sebagai file DOCX
$zip = new ZipArchive();
if ($zip->open($outputFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Gagal membuat file docx.\n");
}

$zip->addFromString('[Content_Types].xml', $content_types);
$zip->addFromString('_rels/.rels', $rels);
$zip->addFromString('word/_rels/document.xml.rels', $document_rels);
$zip->addFromString('word/styles.xml', $styles);
$zip->addFromString('word/document.xml', $document_xml);

$zip->close();

echo "Berhasil membuat file laporan DOCX di: " . $outputFile . "\n";
