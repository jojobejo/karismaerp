<?php
// Scratch script cek db
require 'index.php';
$ci = &get_instance();
$ci->load->database();
$tables = $ci->db->query("SHOW TABLES LIKE '%bundling%'")->result_array();
echo "TABLES BUNDLING:\n";
print_r($tables);

$gudang = $ci->db->query("SELECT * FROM tb_gudang")->result_array();
echo "\nGUDANG:\n";
print_r($gudang);
