<?php
defined('BASEPATH') or exit('No direct script access allowed');

$barang = $barang ?? [];
$qrcode = $qrcode ?? ['exists' => false, 'url' => '', 'path' => ''];
$scanValue = $scan_value ?? '';

function so_print_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= so_print_e($page_title ?? 'Print QRCode') ?></title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#eef2f7;color:#111827;font-family:Arial,Helvetica,sans-serif}
        .page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .label{width:96mm;min-height:72mm;background:#fff;border:1px solid #d1d5db;padding:8mm;text-align:center}
        .label-title{font-size:16px;font-weight:700;line-height:1.25;margin:0 0 4mm;word-break:break-word}
        .meta{font-size:12px;color:#374151;line-height:1.45;margin-bottom:5mm}
        .qr-wrap{display:flex;align-items:center;justify-content:center;margin-bottom:4mm}
        .qr-wrap img{width:42mm;height:42mm;object-fit:contain}
        .scan-value{font-size:13px;font-weight:700;letter-spacing:0;color:#111827}
        .empty{border:1px dashed #9ca3af;color:#6b7280;padding:18mm 8mm;font-size:14px}
        @media print{
            body{background:#fff}
            .page{min-height:auto;padding:0;display:block}
            .label{border:0;width:100%;min-height:auto;padding:8mm}
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="label">
            <h1 class="label-title"><?= so_print_e($barang['nama_barang'] ?? '-') ?></h1>
            <div class="meta">
                <div>Kode: <?= so_print_e($barang['kode_barang'] ?? '-') ?></div>
                <div>Exp: <?= so_print_e($barang['expired_date'] ?? '-') ?> | Lot: <?= so_print_e($barang['no_lot'] ?? '-') ?></div>
            </div>

            <?php if (!empty($qrcode['exists']) && !empty($qrcode['url'])): ?>
                <div class="qr-wrap">
                    <img src="<?= so_print_e($qrcode['url']) ?>" alt="QRCode <?= so_print_e($scanValue) ?>">
                </div>
                <div class="scan-value"><?= so_print_e($scanValue) ?></div>
            <?php else: ?>
                <div class="empty">QRCode belum tergenerate.</div>
            <?php endif; ?>
        </section>
    </main>

    <script>
    window.addEventListener('load', function () {
        window.print();
    });
    </script>
</body>
</html>
