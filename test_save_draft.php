<?php
$ch = curl_init('http://localhost/karismaerp/ics/ajax_save_tmp_po_received');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'kd_po'      => 'PO-TEST',
    'kd_suplier' => 'SYNGE01',
    'kd_barang'  => 'BRG-001',
    'rows'       => [
        [
            'id_tmp_recieved' => -1,
            'qty_diterima' => 5,
            'satuan' => 'PCS',
            'no_lot' => 'LOT-01',
            'expired_date' => '2026-12-31',
            'harga_satuan' => 1000,
            'harga_satuan_kecil' => 1000,
            'total_harga' => 5000
        ]
    ]
]));
$response = curl_exec($ch);
echo "Save Draft Response: $response\n";
