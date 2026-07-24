<?php
$ch = curl_init('http://localhost/karismaerp/ics/ajax_finalize_tmp_po_received');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'no_po'       => 'Q001/KIU/VII/2026A',
    'kd_po'       => '1',
    'kd_suplier'  => 'SYNGE01',
    'nosj'        => 'SJ-TEST-002',
    'tgl_sj'      => '2026-07-24',
    'no_invoice'  => 'INV-001',
    'jenis_lpb'   => 'LPB CP',
    'gudang_id'   => 'GDG01',
    'keterangan'  => 'Test AJAX'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP $httpCode\n";
echo "Response:\n$response\n";
