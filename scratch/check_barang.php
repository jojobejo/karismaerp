<?php
require 'index.php';
$ci = &get_instance();
$ci->load->database();
$cols = $ci->db->query("DESCRIBE tbpo_barang")->result_array();
foreach ($cols as $c) {
    echo $c['Field'] . " (" . $c['Type'] . ")\n";
}

echo "\n--- PADI NK / INNERBOX SAMPLE ---\n";
$samples = $ci->db->query("SELECT kode_barang, nama_barang, satuan FROM tbpo_barang WHERE nama_barang LIKE '%padi%' OR nama_barang LIKE '%nk%' OR nama_barang LIKE '%roundup%' LIMIT 10")->result_array();
print_r($samples);

echo "\n--- TABLES LIKE SATUAN / KONVERSI / KEMASAN ---\n";
$tables = $ci->db->query("SHOW TABLES LIKE '%satuan%'")->result_array();
print_r($tables);
$tables2 = $ci->db->query("SHOW TABLES LIKE '%konversi%'")->result_array();
print_r($tables2);
$tables3 = $ci->db->query("SHOW TABLES LIKE '%kemasan%'")->result_array();
print_r($tables3);
