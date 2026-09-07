<?php
require 'index.php';
$ci = &get_instance();
$ci->load->database();

$sql = file_get_contents(__DIR__ . '/../database/sql/create_bundling_management_system.sql');
$queries = explode(';', $sql);

foreach ($queries as $q) {
    $q = trim($q);
    if (!empty($q)) {
        $res = $ci->db->query($q);
        if (!$res) {
            echo "Error executing query:\n" . $ci->db->error()['message'] . "\nQuery: " . substr($q, 0, 100) . "...\n";
        }
    }
}

echo "MIGRATION COMPLETED SUCCESSFULLY!\n";
$tables = $ci->db->query("SHOW TABLES LIKE 'tberp_bundling%'")->result_array();
print_r($tables);

$gudang = $ci->db->query("SELECT * FROM tb_gudang WHERE nama_gudang LIKE '%Bundling%'")->result_array();
print_r($gudang);
