<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mutasi Kasir - Karisma ERP</title>
    <style>
        /* Reset & Base */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
        }
        .page {
            width: 210mm;
            margin: 0 auto;
            padding: 10mm 12mm;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2mm;
        }
        .header-table td { vertical-align: top; padding: 1px 3px; }
        .judul-kiri { font-size: 12px; font-weight: bold; }
        .judul-sub { font-size: 10px; }
        .header-right { text-align: right; }
        .badge-periode {
            display: inline-block;
            border: 1px solid #000;
            padding: 1px 5px;
            font-weight: bold;
            font-size: 10px;
        }

        /* Divider */
        hr { border: none; border-top: 1.5px solid #000; margin: 2mm 0; }
        hr.thin { border-top: 0.5px solid #888; }

        /* Main table */
        table.mutasi {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table.mutasi th {
            border: 1px solid #000;
            background: #d9d9d9;
            text-align: center;
            padding: 2px 4px;
            font-weight: bold;
            font-size: 10px;
        }
        table.mutasi td {
            border: 1px solid #aaa;
            padding: 2px 4px;
            vertical-align: top;
        }
        table.mutasi td.num { text-align: right; }
        table.mutasi td.ctr { text-align: center; }
        table.mutasi tr.group-date td {
            background: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #000;
            padding: 2px 5px;
        }
        table.mutasi tr.saldo-awal td { background: #eaf4ff; font-weight: bold; }
        table.mutasi tr.total-row td {
            background: #222;
            color: #fff;
            font-weight: bold;
            border: 1px solid #000;
        }
        table.mutasi tr.total-row td.num { color: #fff; }

        /* Footer summary */
        .footer-summary {
            margin-top: 4mm;
            width: 100%;
        }
        .footer-summary table {
            border-collapse: collapse;
            font-size: 10px;
        }
        .footer-summary table td, .footer-summary table th {
            padding: 2px 6px;
            border: 1px solid #999;
        }
        .footer-summary .label-box {
            background: #e8e8e8;
            font-weight: bold;
        }

        .text-success { color: #1a7a2e; }
        .text-danger  { color: #a00; }
        .text-primary { color: #003399; }

        /* Print */
        @media print {
            @page { size: A4 portrait; margin: 8mm; }
            .no-print { display: none !important; }
            body { font-size: 10px; }
        }

        /* Screen-only print btn */
        .print-bar {
            text-align: center;
            padding: 10px;
            background: #f5f5f5;
            margin-bottom: 5mm;
        }
        .print-bar button {
            padding: 6px 20px;
            background: #1a5dbb;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .print-bar button:hover { background: #1348a0; }
    </style>
</head>
<body>

<?php
// Helper format rupiah
function fmt_rp_print($angka) {
    return $angka != 0 ? number_format((float)$angka, 0, ',', '.') : '-';
}
?>

<div class="print-bar no-print">
    <button onclick="window.print()">🖨 Cetak Laporan</button>
    <button onclick="window.close()" style="background:#888; margin-left:8px;">✕ Tutup</button>
</div>

<div class="page">

    <!-- ============ HEADER ============ -->
    <table class="header-table">
        <tr>
            <td style="width:40%;">
                <div class="judul-kiri">Jenis Buku Harian</div>
                <div class="judul-sub">
                    <?php if ($saldo_kasir): ?>
                        <?= htmlspecialchars($saldo_kasir->nama_akun) ?>
                    <?php else: ?>
                        Kas Kecil
                    <?php endif; ?>
                </div>
            </td>
            <td style="width:30%; text-align:center;">
                <div style="font-size:10px; border:1px solid #aaa; padding:2px 6px; display:inline-block;">
                    No Perk:
                    <?= $saldo_kasir ? htmlspecialchars($saldo_kasir->kode_akun) : '-' ?>
                </div>
            </td>
            <td style="width:30%; text-align:right;">
                <div style="font-size:10px;">
                    Periode<br>
                    <strong><?= date('d-M-y', strtotime($tanggal_awal)) ?>
                        <?php if ($tanggal_awal !== $tanggal_akhir): ?>
                            s/d <?= date('d-M-y', strtotime($tanggal_akhir)) ?>
                        <?php endif; ?>
                    </strong>
                </div>
                <div style="margin-top:3px; font-size:10px;">
                    Dicetak: <?= date('d/m/Y H:i') ?>
                </div>
            </td>
        </tr>
    </table>
    <hr>

    <!-- ============ TABEL MUTASI ============ -->
    <table class="mutasi">
        <thead>
            <tr>
                <th style="width:28px;">No</th>
                <th style="width:55px;">No Bkt</th>
                <th>Uraian</th>
                <th style="width:95px;">Debet</th>
                <th style="width:95px;">Kredit</th>
                <th style="width:100px;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <!-- Saldo Awal -->
            <tr class="saldo-awal">
                <td class="ctr">-</td>
                <td class="ctr">-</td>
                <td><strong>Mutasi Saldo</strong></td>
                <td class="num"><strong><?= number_format($saldo_awal, 0, ',', '.') ?></strong></td>
                <td class="num">-</td>
                <td class="num"><strong class="text-primary"><?= number_format($saldo_awal, 0, ',', '.') ?></strong></td>
            </tr>

            <?php if (empty($transaksi)): ?>
                <tr>
                    <td colspan="6" class="ctr" style="padding:8px; color:#666;">
                        Tidak ada transaksi pada periode ini.
                    </td>
                </tr>
            <?php else: ?>
                <?php
                $no = 1;
                $last_date = '';
                foreach ($transaksi as $row):
                    if ($row['tanggal'] !== $last_date):
                        $last_date = $row['tanggal'];
                ?>
                    <!-- Pemisah tanggal -->
                    <tr class="group-date">
                        <td colspan="6">
                            <?= date('l, d F Y', strtotime($row['tanggal'])) ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <td class="ctr"><?= $no++ ?></td>
                    <td class="ctr" style="font-size:9px;"><?= htmlspecialchars($row['no_transaksi']) ?></td>
                    <td>
                        <?= htmlspecialchars($row['pilihan'] ?? '-') ?>
                        <?php if (!empty($row['id_ref'])): ?>
                            <span style="font-weight:bold; color:#1a7a2e;"> [Kas Masuk Ref: <?= htmlspecialchars($row['ref_no_transaksi'] ?? '') ?>]</span>
                        <?php endif; ?>
                        <?php if ($row['jenis_transaksi'] === 'kas_keluar' && !empty($row['is_settled'])): ?>
                            <span style="font-weight:bold; color:#1a7a2e;"> ✓</span>
                        <?php endif; ?>
                        <?php if (!empty($row['keterangan'])): ?>
                            <span style="color:#555;"> - <?= htmlspecialchars($row['keterangan']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($row['nama_user'])): ?>
                            <br><span style="font-size:9px; color:#888;"><?= htmlspecialchars($row['nama_user']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="num <?= $row['debit'] > 0 ? 'text-success' : '' ?>">
                        <?= $row['debit'] > 0 ? number_format($row['debit'], 0, ',', '.') : '-' ?>
                    </td>
                    <td class="num <?= $row['kredit'] > 0 ? 'text-danger' : '' ?>">
                        <?= $row['kredit'] > 0 ? number_format($row['kredit'], 0, ',', '.') : '-' ?>
                    </td>
                    <td class="num text-primary">
                        <strong><?= number_format($row['saldo_berjalan'], 0, ',', '.') ?></strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Baris Total -->
            <tr class="total-row">
                <td colspan="3" class="num">Saldo Kas Buku</td>
                <td class="num"><?= number_format($total_debit, 0, ',', '.') ?></td>
                <td class="num"><?= number_format($total_kredit, 0, ',', '.') ?></td>
                <td class="num"><?= number_format($saldo_akhir, 0, ',', '.') ?></td>
            </tr>
            <tr class="total-row" style="background:#444;">
                <td colspan="3" class="num">Saldo Kas Fisik</td>
                <td colspan="2"></td>
                <td class="num"><?= number_format($saldo_akhir, 0, ',', '.') ?></td>
            </tr>
            <tr class="total-row" style="background:#666;">
                <td colspan="3" class="num">Selisih Kas</td>
                <td colspan="2"></td>
                <td class="num">-</td>
            </tr>
        </tbody>
    </table>

    <hr class="thin" style="margin-top:4mm;">

    <!-- ============ FOOTER SUMMARY ============ -->
    <div class="footer-summary">
        <table style="width:48%; display:inline-table; vertical-align:top; margin-right:2%;">
            <thead>
                <tr>
                    <th colspan="3" style="background:#d9d9d9; text-align:center;">Setoran</th>
                </tr>
                <tr>
                    <th style="width:50%;">Sumber</th>
                    <th colspan="2">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="label-box">Total Kas Masuk</td>
                    <td>Rp</td>
                    <td style="text-align:right;"><?= number_format($total_debit, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td class="label-box">Total Kas Keluar</td>
                    <td>Rp</td>
                    <td style="text-align:right;"><?= number_format($total_kredit, 0, ',', '.') ?></td>
                </tr>
                <tr style="font-weight:bold;">
                    <td class="label-box">Saldo Akhir</td>
                    <td>Rp</td>
                    <td style="text-align:right; color:#003399;"><?= number_format($saldo_akhir, 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>

        <table style="width:48%; display:inline-table; vertical-align:top;">
            <thead>
                <tr>
                    <th colspan="3" style="background:#d9d9d9; text-align:center;">Ringkasan Kas</th>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <th colspan="2">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="label-box">Saldo Awal</td>
                    <td>Rp</td>
                    <td style="text-align:right;"><?= number_format($saldo_awal, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td class="label-box">+ Kas Masuk</td>
                    <td>Rp</td>
                    <td style="text-align:right; color:#1a7a2e;"><?= number_format($total_debit, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td class="label-box">- Kas Keluar</td>
                    <td>Rp</td>
                    <td style="text-align:right; color:#a00;"><?= number_format($total_kredit, 0, ',', '.') ?></td>
                </tr>
                <tr style="font-weight:bold;">
                    <td class="label-box">= Saldo Akhir</td>
                    <td>Rp</td>
                    <td style="text-align:right; color:#003399;"><?= number_format($saldo_akhir, 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <hr class="thin" style="margin-top:6mm;">
    <div style="font-size:9px; color:#888; text-align:right; margin-top:2mm;">
        Karisma ERP &mdash; Laporan Mutasi Kasir &mdash; Dicetak: <?= date('d/m/Y H:i:s') ?>
    </div>

</div><!-- .page -->
</body>
</html>
