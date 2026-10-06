<?php
$db = new mysqli('127.0.0.1', 'root', '', 'kiucoid_karismaerp_local');
if ($db->connect_error) {
    die("Koneksi gagal: " . $db->connect_error);
}

$sqlFile = __DIR__ . '/reset_transaksi_ke_awal_kosongan.sql';
if (!file_exists($sqlFile)) {
    die("File SQL tidak ditemukan: $sqlFile");
}

$sql = file_get_contents($sqlFile);

echo "=========================================================\n";
echo "MENGEKSEKUSI RESET TRANSAKSI KE AWAL KOSONGAN\n";
echo "=========================================================\n";

$db->query("SET FOREIGN_KEY_CHECKS = 0");

$statements = array_filter(array_map('trim', explode(';', $sql)));
$successCount = 0;
$errorCount = 0;

foreach ($statements as $stmt) {
    if (empty($stmt) || strpos($stmt, '--') === 0) continue;
    if ($db->query($stmt)) {
        $successCount++;
    } else {
        $errorCount++;
        echo "Error pada query: $stmt\n";
        echo "Pesan: " . $db->error . "\n";
    }
}

$db->query("SET FOREIGN_KEY_CHECKS = 1");

echo "\nEksekusi Selesai: $successCount sukses, $errorCount error.\n\n";

// Verifikasi sisa baris
echo "=== HASIL VERIFIKASI ROW COUNT SETELAH RESET ===\n";
$keyTables = [
    'tbkeu_jurnal' => 'Jurnal Transaksi GL',
    'tbkeu_jurnal_detail' => 'Detail Jurnal Transaksi GL',
    'tbkeu_posting_exception' => 'Posting Exceptions',
    'tbkeu_closing_period' => 'Closing Periods',
    'tbkeu_pembayaran' => 'Pembayaran Header',
    'tbkeu_pembayaran_faktur' => 'Pembayaran Faktur Customer',
    'tbso_sales_order' => 'Sales Order',
    'tbso_faktur_penjualan' => 'Faktur Penjualan',
    'tb_do' => 'Delivery Order (DO)',
    'tbrp_retur_penjualan_header' => 'Retur Penjualan',
    'tbpo_po' => 'Purchase Order (PO)',
    'tb_lpb' => 'Penerimaan Barang (LPB)',
    'tb_retur_pembelian' => 'Retur Pembelian',
    'tb_konsinyasi_settlement' => 'Konsinyasi Settlement',
    'tberp_stock_ledger' => 'Stock Ledger (Kartu Stok)',
    'tberp_stock_batch' => 'Stock Batch (Lot)',
];

$allZero = true;
foreach ($keyTables as $tbl => $label) {
    $res = $db->query("SELECT COUNT(*) as c FROM `$tbl`");
    $cnt = $res ? $res->fetch_assoc()['c'] : -1;
    printf("%-30s : %d baris\n", "$label ($tbl)", $cnt);
    if ($cnt > 0) $allZero = false;
}

echo "\n=== STATUS PERIODE FISKAL ===\n";
$resP = $db->query("SELECT id_periode, kode_periode, nama_periode, status FROM tbkeu_periode_fiskal");
while ($r = $resP->fetch_assoc()) {
    echo "ID: {$r['id_periode']} | Kode: {$r['kode_periode']} | Status: {$r['status']}\n";
}

echo "\n=== STATUS MASTER DATA (HARUS TETAP UTUH) ===\n";
$masterTables = [
    'tbpo_barang' => 'Master Barang',
    'tb_customer' => 'Master Customer',
    'tb_suplier' => 'Master Supplier',
    'tb_user' => 'Master User Login',
    'tb_gudang' => 'Master Gudang',
    'tbkeu_akun' => 'Master Akun COA',
    'tbkeu_mapping_akun' => 'Master Mapping Akun',
];
foreach ($masterTables as $tbl => $label) {
    $res = $db->query("SELECT COUNT(*) as c FROM `$tbl`");
    $cnt = $res ? $res->fetch_assoc()['c'] : 0;
    printf("%-30s : %d baris [AMAN]\n", "$label ($tbl)", $cnt);
}

$db->close();
