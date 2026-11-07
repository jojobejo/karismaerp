<?php
$f = file_get_contents('application/views/content/keuangan/pembayaran_faktur_detail.php');
$lines = explode("\n", $f);
foreach ($lines as $i => $l) {
    if (strpos($l, 'modal-faktur-h') !== false || strpos($l, 'modal-h-tbody') !== false) {
        echo ($i + 1) . ": " . trim($l) . "\n";
    }
}

