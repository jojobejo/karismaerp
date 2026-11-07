<?php
$dbName = 'kiucoid_karismaerp_local';
$pdo = new PDO("mysql:host=localhost;dbname=$dbName", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "=== 1. RINGKASAN DATABASE ===\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Total Tabel: " . count($tables) . "\n\n";

$checkTables = [
    'tbpo_po'                      => 'PO Header',
    'tbpo_detail_po'               => 'PO Detail',
    'tb_lpb'                       => 'LPB Header',
    'tb_lpb_detail'                => 'LPB Detail',
    'tbso_sales_order'             => 'Sales Order (SO)',
    'tbso_faktur_penjualan'        => 'Faktur Penjualan',
    'tbso_faktur_detail'           => 'Faktur Penjualan Detail',
    'tbkeu_pembayaran_faktur'      => 'Pembayaran Faktur',
    'tbrp_spr_header'              => 'SPR Header',
    'tbrp_spr_detail'              => 'SPR Detail',
    'tbrp_retur_penjualan_header'  => 'Retur Penjualan Header',
    'tbrp_retur_penjualan_detail'  => 'Retur Penjualan Detail',
    'tbrb_retur_pembelian_header'  => 'Retur Pembelian Header',
    'tbrb_retur_pembelian_detail'  => 'Retur Pembelian Detail',
    'tbkeu_kas_keluar'             => 'Kas Keluar',
    'tbkeu_warkat'                 => 'Warkat BG / Cek',
    'tbkeu_jurnal'                 => 'Jurnal Akuntansi Header',
    'tbkeu_jurnal_detail'          => 'Jurnal Akuntansi Detail',
    'tb_stok'                      => 'Kartu Stok',
    'tb_batch'                     => 'Batch / Lot Stok',
    'tbpo_barang'                  => 'Master Barang',
    'tb_customer'                  => 'Master Customer',
    'tb_suplier'                   => 'Master Supplier'
];

echo "=== 2. JUMLAH DATA PER TABEL TRANSAKSI & MASTER ===\n";
foreach ($checkTables as $table => $label) {
    if (in_array($table, $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo sprintf(" - %-30s (%s): %d baris\n", $table, $label, $count);
    } else {
        echo sprintf(" - %-30s (%s): [TIDAK DITEMUKAN]\n", $table, $label);
    }
}

echo "\n=== 3. RINCIAN DATA PO (PURCHASE ORDER) ===\n";
if (in_array('tbpo_po', $tables)) {
    $pos = $pdo->query("SELECT id_po, kd_po, no_po, tgl_transaksi, kd_suplier, jml_item, total_harga_include, status FROM tbpo_po")->fetchAll();
    foreach ($pos as $po) {
        echo "PO #{$po['id_po']} | {$po['no_po']} | Tgl: {$po['tgl_transaksi']} | Suplier: {$po['kd_suplier']} | Item: {$po['jml_item']} | Total Inc: " . number_format($po['total_harga_include'], 2) . " | Status: {$po['status']}\n";
    }
}

echo "\n=== 4. RINCIAN DATA LPB (PENERIMAAN BARANG) ===\n";
if (in_array('tb_lpb', $tables)) {
    $lpbs = $pdo->query("SELECT id_lpb, nomor_lpb, no_po, tgl_sj, nosj, jenis_lpb, status_lpb FROM tb_lpb")->fetchAll();
    foreach ($lpbs as $l) {
        echo "LPB #{$l['id_lpb']} | No: {$l['nomor_lpb']} | Ref PO: {$l['no_po']} | Tgl SJ: {$l['tgl_sj']} | Jenis: {$l['jenis_lpb']} | Status: {$l['status_lpb']}\n";
    }
}

echo "\n=== 5. RINCIAN DATA FAKTUR PENJUALAN ===\n";
if (in_array('tbso_faktur_penjualan', $tables)) {
    $invoices = $pdo->query("SELECT id_faktur, no_faktur, no_so, kd_customer, customer_name, tanggal_faktur, status, salesman FROM tbso_faktur_penjualan")->fetchAll();
    foreach ($invoices as $inv) {
        echo "Faktur #{$inv['id_faktur']} | {$inv['no_faktur']} | SO: {$inv['no_so']} | Cust: {$inv['kd_customer']} - {$inv['customer_name']} | Tgl: {$inv['tanggal_faktur']} | Status: {$inv['status']} | Sales: {$inv['salesman']}\n";
    }
}

echo "\n=== 6. RINCIAN DATA JURNAL AKUNTANSI ===\n";
if (in_array('tbkeu_jurnal', $tables)) {
    $journals = $pdo->query("SELECT id_jurnal, nomor_jurnal, tanggal_transaksi, keterangan, source_module, source_no, total_debit, total_kredit, status FROM tbkeu_jurnal ORDER BY tanggal_transaksi, id_jurnal")->fetchAll();
    $totalDeb = 0;
    $totalKre = 0;
    foreach ($journals as $j) {
        $totalDeb += $j['total_debit'];
        $totalKre += $j['total_kredit'];
        echo "Jurnal #{$j['id_jurnal']} | {$j['nomor_jurnal']} | {$j['tanggal_transaksi']} | Modul: {$j['source_module']} ({$j['source_no']}) | D: " . number_format($j['total_debit'], 2) . " | K: " . number_format($j['total_kredit'], 2) . " | Ket: {$j['keterangan']}\n";
    }
    echo "--- TOTAL JURNAL: Debit = " . number_format($totalDeb, 2) . " | Kredit = " . number_format($totalKre, 2) . " | Balance: " . ($totalDeb == $totalKre ? "SEIMBANG (MATCH)" : "TIDAK SEIMBANG") . " ---\n";
}
