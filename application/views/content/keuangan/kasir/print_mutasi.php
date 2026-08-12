<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mutasi Kas Harian - <?= date('d/m/Y', strtotime($tanggal)) ?></title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 11px; color: #000; background: #fff; margin: 0; padding: 0; }
        .container { width: 100%; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; }
        .header-tbl td { padding: 2px 4px; vertical-align: top; }
        .mutasi-tbl th, .mutasi-tbl td { border: 1px solid #000; padding: 3px 5px; }
        .mutasi-tbl th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-weight-bold { font-weight: bold; }
        .section-title { background-color: #eee; font-weight: bold; text-transform: uppercase; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="container">
    
    <!-- Header Dokumen Buku Harian (seperti Excel) -->
    <table class="header-tbl" style="border-bottom: 2px solid #000; margin-bottom: 8px;">
        <tr>
            <td style="width: 25%;"><strong>Jenis Buku Harian:</strong><br>Kas/Gab</td>
            <td style="width: 25%;"><strong>No Perk:</strong><br>102</td>
            <td style="width: 25%;"><strong>Periode:</strong><br><?= date('d-M-y', strtotime($tanggal)) ?></td>
            <td style="width: 25%; text-align: right;"><strong>Halaman:</strong><br>01/</td>
        </tr>
    </table>

    <!-- Tabel Utama -->
    <table class="mutasi-tbl">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 110px;">No Bkt</th>
                <th>Uraian</th>
                <th style="width: 110px;" class="text-right">Debet</th>
                <th style="width: 110px;" class="text-right">Kredit</th>
                <th style="width: 120px;" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $saldo = (float)$saldo_awal;
            $total_debet = 0;
            $total_kredit = 0;
            ?>

            <!-- Saldo Awal -->
            <tr class="font-weight-bold">
                <td class="text-center">-</td>
                <td>-</td>
                <td><strong>Mutasi Saldo (Saldo Awal)</strong></td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right"><?= number_format($saldo, 0, ',', '.') ?></td>
            </tr>

            <!-- Section 1: Kas Keluar / UM Keluar Outstanding -->
            <tr>
                <td colspan="6" class="section-title">--- DAFTAR KAS KELUAR / UM KELUAR (OUTSTANDING) ---</td>
            </tr>

            <?php if (!empty($kas_keluar_outstanding)): ?>
                <?php foreach ($kas_keluar_outstanding as $kk): 
                    $nominal_kredit = (float)$kk['nominal'];
                    $saldo -= $nominal_kredit;
                    $total_kredit += $nominal_kredit;
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($kk['no_transaksi']) ?></td>
                    <td><?= htmlspecialchars($kk['pilihan']) ?></td>
                    <td class="text-right">-</td>
                    <td class="text-right"><?= number_format($nominal_kredit, 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($saldo, 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center" style="font-style: italic;">Tidak ada Kas Keluar outstanding</td>
                </tr>
            <?php endif; ?>

            <!-- Section 2: Kas Masuk Harian -->
            <tr>
                <td colspan="6" class="section-title">--- DAFTAR KAS MASUK HARIAN ---</td>
            </tr>

            <?php if (!empty($kas_masuk_harian)): ?>
                <?php foreach ($kas_masuk_harian as $km): 
                    $nominal_debet  = (float)$km['nominal'];
                    $nominal_kredit = (float)($km['nominal_kredit_induk'] ?? 0);
                    $saldo += ($nominal_debet - $nominal_kredit);
                    $total_debet  += $nominal_debet;
                    $total_kredit += $nominal_kredit;
                    $no_bkt = !empty($km['no_bukti']) ? $km['no_bukti'] : $km['no_transaksi'];
                ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($no_bkt) ?></td>
                    <td><?= htmlspecialchars($km['pilihan']) ?></td>
                    <td class="text-right"><?= number_format($nominal_debet, 0, ',', '.') ?></td>
                    <td class="text-right"><?= $nominal_kredit > 0 ? number_format($nominal_kredit, 0, ',', '.') : '-' ?></td>
                    <td class="text-right"><?= number_format($saldo, 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center" style="font-style: italic;">Tidak ada Kas Masuk pada tanggal ini</td>
                </tr>
            <?php endif; ?>

            <!-- Summary -->
            <tr class="font-weight-bold" style="border-top: 2px solid #000;">
                <td colspan="3" class="text-right">TOTAL MUTASI:</td>
                <td class="text-right"><?= number_format($total_debet, 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($total_kredit, 0, ',', '.') ?></td>
                <td class="text-right"><?= number_format($saldo, 0, ',', '.') ?></td>
            </tr>
            <tr class="font-weight-bold" style="border-top: 2px solid #000; background-color: #eee;">
                <td colspan="3" class="text-right">SALDO AKHIR KAS BUKU:</td>
                <td colspan="3" class="text-right" style="font-size: 13px;">
                    Rp <?= number_format($saldo, 0, ',', '.') ?>
                </td>
            </tr>
        </tbody>
    </table>

</div>

</body>
</html>
