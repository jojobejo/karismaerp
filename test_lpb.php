<?php
define('BASEPATH', 'TRUE');
require 'index.php';
$CI =& get_instance();
$CI->load->model('M_Logistik');

$tmpRows = $CI->M_Logistik->get_tmp_po_received_posting_rows('Q001/KIU/VII/2026A', 'SYNGE01');
if (empty($tmpRows)) {
    echo "No tmp rows found\n";
    exit;
}

$payload = [
    'no_po'       => 'Q001/KIU/VII/2026A',
    'kd_po'       => $tmpRows[0]['kd_po'],
    'kd_suplier'  => 'SYNGE01',
    'nosj'        => 'SJ-TEST-001',
    'tgl_sj'      => '2026-07-24',
    'no_invoice'  => '-',
    'jenis_lpb'   => 'LPB CP',
    'gudang_id'   => 'GUDANG-001',
    'keterangan'  => 'Test',
    'dilakukan_oleh' => 'System',
    'checker_name' => 'System',
    'checker_by' => 'System'
];

$CI->db->trans_begin();
$idLpb = $CI->M_Logistik->create_lpb_from_tmp($payload, $tmpRows);

if (!$idLpb || $CI->db->trans_status() === FALSE) {
    echo "FAILED!\n";
    print_r($CI->db->error());
    $CI->db->trans_rollback();
} else {
    echo "SUCCESS: $idLpb\n";
    $CI->db->trans_rollback();
}
