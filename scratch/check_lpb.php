<?php
require 'index.php';
$ci = &get_instance();
$ci->load->database();
$cols = $ci->db->query("DESCRIBE tb_lpb_detail")->result_array();
foreach ($cols as $c) {
    echo $c['Field'] . " (" . $c['Type'] . ")\n";
}
