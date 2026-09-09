<?php
require 'index.php';
$ci = &get_instance();
$ci->load->model('M_Bundling');
$ci->M_Bundling->ensure_bundling_schema();
echo "SCHEMA ENSURED SUCCESSFULLY.\n";

echo "--- COLUMNS IN tberp_bundling_formula_detail ---\n";
$cols1 = $ci->db->query("DESCRIBE tberp_bundling_formula_detail")->result_array();
foreach ($cols1 as $c) echo $c['Field'] . " (" . $c['Type'] . ")\n";

echo "--- COLUMNS IN tberp_bundling_request_detail ---\n";
$cols2 = $ci->db->query("DESCRIBE tberp_bundling_request_detail")->result_array();
foreach ($cols2 as $c) echo $c['Field'] . " (" . $c['Type'] . ")\n";
