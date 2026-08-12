<?php
define('BASEPATH', __DIR__ . '/system/');
define('APPPATH', __DIR__ . '/application/');
define('ENVIRONMENT', 'development');

// Untuk menghindari masalah routing dan $_SERVER['REQUEST_URI']
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once(BASEPATH . 'core/CodeIgniter.php');

$CI =& get_instance();
$CI->load->model('M_Kasbon');

$user = ['id' => 87, 'nama' => 'manager_sc', 'username' => 'mngsc', 'jobdesk' => 'MNGSC'];
$kasbon = $CI->M_Kasbon->get_kasbon_for_user($user);

echo "Jumlah kasbon ditemukan: " . count($kasbon) . "\n";
foreach ($kasbon as $k) {
    echo "- No: {$k['no_kasbon']} | Status: {$k['status']} | App1: {$k['approver_1']}\n";
}
echo "\nLast Query:\n" . $CI->db->last_query() . "\n";
