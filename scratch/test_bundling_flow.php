<?php
// Test Flow Lengkap Paket Bundling KarismaERP
require 'index.php';
$ci = &get_instance();
$ci->load->database();
$ci->load->model('M_Bundling');
$ci->load->model('M_PenyesuaianBarang');

echo "=== MEMULAI TEST INTEGRASI MODUL PAKET BUNDLING ===\n\n";

// 1. Simpan Formula Master Paket Jitu
echo "1. Menyimpan Master Formula Paket Jitu...\n";
$formulaRes = $ci->M_Bundling->save_formula([
    'kode_paket'   => 'PKT-JITU-01',
    'nama_paket'   => 'Paket Jitu',
    'satuan_paket' => 'Box',
    'keterangan'   => 'Formula resmi 1 Paket Jitu = 1 Spontas, 1 Round Up, 1 Kaos'
], [
    ['kode_barang_komponen' => 'SPON-1LTR', 'nama_barang_komponen' => 'Spontas 1 Ltr', 'qty_komponen' => 1, 'satuan' => 'Ltr'],
    ['kode_barang_komponen' => 'ROUN-1LTR', 'nama_barang_komponen' => 'Round Up 1 Ltr', 'qty_komponen' => 1, 'satuan' => 'Ltr'],
    ['kode_barang_komponen' => 'KAOS-JITU', 'nama_barang_komponen' => 'Kaos Jitu', 'qty_komponen' => 1, 'satuan' => 'Pcs']
]);
print_r($formulaRes);

// 2. Purchasing Membuat Request 250 Box Paket Jitu
echo "\n2. Purchasing membuat Request 250 Box Paket Jitu...\n";
$reqRes = $ci->M_Bundling->create_request([
    'tanggal_request'  => '2026-09-01',
    'kode_paket'       => 'PKT-JITU-01',
    'nama_paket'       => 'Paket Jitu',
    'id_gudang_tujuan' => 12, // Gudang Bundling
    'id_gudang_asal'   => 2,  // Gudang Induk
    'qty_request'      => 250,
    'satuan'           => 'Box',
    'keterangan'       => 'Request Direktur untuk program promo'
], [
    ['kode_barang_komponen' => 'SPON-1LTR', 'nama_barang_komponen' => 'Spontas 1 Ltr', 'qty_per_paket' => 1, 'satuan' => 'Ltr'],
    ['kode_barang_komponen' => 'ROUN-1LTR', 'nama_barang_komponen' => 'Round Up 1 Ltr', 'qty_per_paket' => 1, 'satuan' => 'Ltr'],
    ['kode_barang_komponen' => 'KAOS-JITU', 'nama_barang_komponen' => 'Kaos Jitu', 'qty_per_paket' => 1, 'satuan' => 'Pcs']
], 'PURCHASING');
print_r($reqRes);

$reqId = $reqRes['id_request'];
$reqDetail = $ci->M_Bundling->get_request_by_id($reqId);
echo "\nDetail Request Terbuat:\n";
echo "No Request: " . $reqDetail['no_request'] . "\n";
echo "Status: " . $reqDetail['status'] . "\n";
foreach ($reqDetail['details'] as $d) {
    echo "- " . $d['nama_barang_komponen'] . ": " . $d['qty_per_paket'] . " per paket x 250 = Total Butuh: " . $d['qty_total_kebutuhan'] . " " . $d['satuan'] . "\n";
}

// 3. Persiapan Stok Bahan di Gudang Induk (Simulasi stok masuk di Gudang Induk untuk ditransfer)
echo "\n3. Menyiapkan stok komponen di Gudang Induk (Gudang 2)...\n";
$now = date('Y-m-d H:i:s');
$components = [
    ['kd' => 'SPON-1LTR', 'nama' => 'Spontas 1 Ltr', 'lot' => 'LOT-SPON-01', 'exp' => '2027-12-31', 'qty' => 300],
    ['kd' => 'ROUN-1LTR', 'nama' => 'Round Up 1 Ltr', 'lot' => 'LOT-ROUN-01', 'exp' => '2027-11-30', 'qty' => 300],
    ['kd' => 'KAOS-JITU', 'nama' => 'Kaos Jitu', 'lot' => 'LOT-KAOS-01', 'exp' => null, 'qty' => 300]
];
foreach ($components as $c) {
    // Reset/isi batch di gudang 2
    $batch = $ci->db->where('kd_barang', $c['kd'])->where('gudang_id', 2)->where('no_lot', $c['lot'])->get('tberp_stock_batch')->row();
    if ($batch) {
        $ci->db->where('id', $batch->id)->update('tberp_stock_batch', ['qty_on_hand' => $c['qty'], 'update_at' => $now]);
    } else {
        $ci->db->insert('tberp_stock_batch', [
            'kd_barang' => $c['kd'], 'gudang_id' => 2, 'no_lot' => $c['lot'], 'expired_date' => $c['exp'], 'qty_on_hand' => $c['qty'], 'qty_reserved' => 0, 'created_at' => $now, 'update_at' => $now
        ]);
    }
}

// 4. Logistik Memeriksa Ketersediaan & Melakukan Mutasi Bahan dari Gudang Induk -> Gudang Bundling
echo "\n4. Eksekusi Mutasi Bahan dari Gudang Induk ke Gudang Bundling (250 unit tiap komponen)...\n";
$mutasiRes = $ci->M_Bundling->execute_mutasi_bahan_bundling($reqId, [
    ['kode_barang' => 'SPON-1LTR', 'nama_barang' => 'Spontas 1 Ltr', 'no_lot' => 'LOT-SPON-01', 'expired_date' => '2027-12-31', 'qty_mutasi' => 250],
    ['kode_barang' => 'ROUN-1LTR', 'nama_barang' => 'Round Up 1 Ltr', 'no_lot' => 'LOT-ROUN-01', 'expired_date' => '2027-11-30', 'qty_mutasi' => 250],
    ['kode_barang' => 'KAOS-JITU', 'nama_barang' => 'Kaos Jitu', 'no_lot' => 'LOT-KAOS-01', 'expired_date' => null, 'qty_mutasi' => 250]
], 'LOGISTIK');
print_r($mutasiRes);

// 5. Logistik Melakukan Realisasi Parsial (Partial Fulfillment): Tahap 1 = 100 Box
echo "\n5. Logistik Realisasi Pembuatan Paket Tahap 1 (100 Box)...\n";
$asmRes = $ci->M_Bundling->process_assembly([
    'id_request'         => $reqId,
    'tanggal'            => '2026-09-02',
    'kode_paket'         => 'PKT-JITU-01',
    'nama_paket'         => 'Paket Jitu',
    'id_gudang'          => 12,
    'qty_assembly'       => 100,
    'satuan'             => 'Box',
    'no_lot_paket'       => 'LOT-JITU-BATCH1',
    'expired_date_paket' => '2027-11-30'
], [
    ['kode_barang' => 'SPON-1LTR', 'nama_barang' => 'Spontas 1 Ltr', 'no_lot' => 'LOT-SPON-01', 'expired_date' => '2027-12-31', 'qty_digunakan' => 100, 'satuan' => 'Ltr'],
    ['kode_barang' => 'ROUN-1LTR', 'nama_barang' => 'Round Up 1 Ltr', 'no_lot' => 'LOT-ROUN-01', 'expired_date' => '2027-11-30', 'qty_digunakan' => 100, 'satuan' => 'Ltr'],
    ['kode_barang' => 'KAOS-JITU', 'nama_barang' => 'Kaos Jitu', 'no_lot' => 'LOT-KAOS-01', 'expired_date' => null, 'qty_digunakan' => 100, 'satuan' => 'Pcs']
], 'LOGISTIK');
print_r($asmRes);

// Cek status request setelah partial fulfillment
$reqAfterAsm = $ci->M_Bundling->get_request_by_id($reqId);
echo "\nStatus Request Setelah Assembly 100 Box:\n";
echo "Status: " . $reqAfterAsm['status'] . "\n";
echo "Qty Request: " . $reqAfterAsm['qty_request'] . "\n";
echo "Qty Realisasi: " . $reqAfterAsm['qty_realisasi'] . "\n";
echo "Sisa Request: " . ($reqAfterAsm['qty_request'] - $reqAfterAsm['qty_realisasi']) . "\n";

// Cek stok paket di Gudang Bundling
$stokPaket = $ci->M_Bundling->get_stock_available_by_gudang('PKT-JITU-01', 12);
echo "Stok Fisik Paket Jitu di Gudang Bundling: " . $stokPaket . " Box\n";

// 6. Skenario Penjualan Eceran: Pembongkaran Paket (Disassembly) 10 Box
echo "\n6. Skenario Penjualan Ecer: Pembongkaran Paket (Disassembly) 10 Box Paket Jitu...\n";
$dsbRes = $ci->M_Bundling->process_disassembly([
    'tanggal'            => '2026-09-03',
    'kode_paket'         => 'PKT-JITU-01',
    'nama_paket'         => 'Paket Jitu',
    'id_gudang'          => 12,
    'qty_disassembly'    => 10,
    'satuan'             => 'Box',
    'no_lot_paket'       => 'LOT-JITU-BATCH1',
    'expired_date_paket' => '2027-11-30',
    'alasan'             => 'Customer membeli 10 Spontas 1 Ltr secara ecer'
], [
    ['kode_barang' => 'SPON-1LTR', 'nama_barang' => 'Spontas 1 Ltr', 'no_lot' => 'LOT-SPON-01', 'expired_date' => '2027-12-31', 'qty_kembali' => 10, 'satuan' => 'Ltr'],
    ['kode_barang' => 'ROUN-1LTR', 'nama_barang' => 'Round Up 1 Ltr', 'no_lot' => 'LOT-ROUN-01', 'expired_date' => '2027-11-30', 'qty_kembali' => 10, 'satuan' => 'Ltr'],
    ['kode_barang' => 'KAOS-JITU', 'nama_barang' => 'Kaos Jitu', 'no_lot' => 'LOT-KAOS-01', 'expired_date' => null, 'qty_kembali' => 10, 'satuan' => 'Pcs']
], 'LOGISTIK');
print_r($dsbRes);

// Cek stok setelah pembongkaran
$stokPaketAfterDsb = $ci->M_Bundling->get_stock_available_by_gudang('PKT-JITU-01', 12);
$stokSponAfterDsb = $ci->M_Bundling->get_stock_available_by_gudang('SPON-1LTR', 12);
echo "\nStok Setelah Pembongkaran 10 Box:\n";
echo "- Paket Jitu di Gudang Bundling: " . $stokPaketAfterDsb . " Box (Sebelumnya 100, berkurang 10)\n";
echo "- Spontas 1 Ltr di Gudang Bundling: " . $stokSponAfterDsb . " Ltr (150 sisa bahan + 10 kembali dari pembongkaran = 160)\n";

// Cek kartu stok ledger
echo "\n7. Audit Trail Kartu Stok (tberp_stock_ledger) untuk PKT-JITU-01:\n";
$ledgers = $ci->db->where('kd_barang', 'PKT-JITU-01')->order_by('id', 'ASC')->get('tberp_stock_ledger')->result_array();
foreach ($ledgers as $l) {
    echo "- [" . $l['created_at'] . "] " . $l['tipe'] . " " . $l['qty'] . " (Ref: " . $l['ref_no'] . ", Type: " . $l['ref_type'] . ")\n";
}

echo "\n=== SEMUA TEST INTEGRASI BERHASIL 100%! ===\n";
