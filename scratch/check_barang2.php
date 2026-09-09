<?php
require 'index.php';
$ci = &get_instance();
$ci->load->database();

echo "--- SAMPLE ISI & KEMASAN TBPO_BARANG ---\n";
$rows = $ci->db->query("SELECT kode_barang, nama_barang, satuan, isi, kemasan FROM tbpo_barang WHERE isi > 1 OR kemasan > 1 LIMIT 10")->result_array();
print_r($rows);

echo "--- TBPO_SATUAN ---\n";
$satuan = $ci->db->query("SELECT * FROM tbpo_satuan LIMIT 10")->result_array();
print_r($satuan);

echo "--- TBERP_STOCK_BATCH STRUCTURE ---\n";
$sb_cols = $ci->db->query("DESCRIBE tberp_stock_batch")->result_array();
foreach ($sb_cols as $c) {
    echo $c['Field'] . " (" . $c['Type'] . ")\n";
}

echo "--- CARI BARANG 'NK' ATAU 'PADI' ATAU 'ROUNDUP' ---\n";
$nk = $ci->db->query("SELECT kode_barang, nama_barang, satuan, isi, kemasan FROM tbpo_barang WHERE nama_barang LIKE '%NK%' OR nama_barang LIKE '%Padi%' OR nama_barang LIKE '%Roundup%' LIMIT 10")->result_array();
print_r($nk);
