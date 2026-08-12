<?php
$ch = curl_init('http://karismaerp.test/keuangan/kas_keluar/save');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'tanggal' => '2026-08-12',
    'id_akun_kas' => 1,
    'penerima' => 'Test',
    'memo' => 'Testing Error',
    'no_referensi' => 'AKK001',
    'nilai' => 1000,
    'details' => [
        ['id_akun' => 2, 'nilai' => 1000]
    ],
    'post_now' => 1
]));
$response = curl_exec($ch);
echo "HTTP Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo "Error: " . curl_error($ch) . "\n";
echo "Response: " . $response . "\n";
