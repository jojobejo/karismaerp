<?php
/**
 * laba_rugi_excel.php
 * Template unduh Excel (.xls) Laporan Laba Rugi bergaya Zahir Accounting.
 */
$isCompare = in_array($report_mode, ['perbandingan_2', 'perbandingan_4'], true);
$colSpanAll = ($report_mode === 'perbandingan_4') ? 6 : (($report_mode === 'standar') ? 3 : 4);
$groups = $data['groups'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($report_title) ?></title>
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #000; }
        .hdr-center { text-align: center; }
        .company-name { font-size: 14pt; font-weight: bold; }
        .report-name { font-size: 16pt; font-weight: bold; color: #008cba; }
        .period-name { font-size: 11pt; font-weight: bold; }
        .ak-tbl { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .ak-tbl th, .ak-tbl td { padding: 4px 8px; vertical-align: middle; }
        .th-title { border-bottom: 1.5pt solid #000; font-weight: bold; text-align: right; }
        .sec-title { font-weight: bold; color: #0284c7; }
        .sec-sub { font-weight: bold; color: #0284c7; padding-left: 15px; }
        .cell-code { text-align: left; padding-left: 25px; }
        .cell-name { text-align: left; }
        .cell-num { text-align: right; mso-number-format:"\#\,\#\#0\.00"; }
        .subtotal-title { font-weight: bold; color: #0284c7; padding-left: 15px; }
        .subtotal-num { font-weight: bold; color: #0284c7; text-align: right; border-top: 1pt solid #000; mso-number-format:"\#\,\#\#0\.00"; }
        .grandtotal-title { font-weight: bold; color: #0284c7; }
        .grandtotal-num { font-weight: bold; color: #0284c7; text-align: right; border-top: 1pt solid #0284c7; border-bottom: 1pt solid #0284c7; mso-number-format:"\#\,\#\#0\.00"; }
        .netincome-title { font-weight: bold; font-size: 12pt; color: #0369a1; }
        .netincome-num { font-weight: bold; font-size: 12pt; color: #0369a1; text-align: right; border-top: 1pt solid #0369a1; border-bottom: 3pt double #0369a1; mso-number-format:"\#\,\#\#0\.00"; }
    </style>
</head>
<body>
    <div class="hdr-center">
        <div class="company-name"><?= htmlspecialchars($company_name) ?></div>
        <div class="report-name"><?= htmlspecialchars($report_title) ?></div>
        <?php if (!$isCompare): ?>
            <div class="period-name"><?= htmlspecialchars($period_label) ?></div>
        <?php endif; ?>
    </div>

    <table class="ak-tbl" border="0">
        <thead>
            <?php if ($report_mode === 'standar'): ?>
                <tr>
                    <th colspan="2"></th>
                    <th class="th-title">Saldo (IDR)</th>
                </tr>
            <?php elseif ($report_mode === 'ytd'): ?>
                <tr>
                    <th colspan="2"></th>
                    <th class="th-title">Saldo (IDR)</th>
                    <th class="th-title">Year to Date (IDR)</th>
                </tr>
            <?php elseif ($report_mode === 'anggaran'): ?>
                <tr>
                    <th colspan="2"></th>
                    <th class="th-title">Realisasi (IDR)</th>
                    <th class="th-title">Anggaran (IDR)</th>
                </tr>
            <?php elseif ($report_mode === 'perbandingan_2'): ?>
                <tr>
                    <th colspan="2"></th>
                    <th class="th-title"><?= htmlspecialchars($p1_label) ?> (IDR)</th>
                    <th class="th-title"><?= htmlspecialchars($p2_label) ?> (IDR)</th>
                </tr>
            <?php elseif ($report_mode === 'perbandingan_4'): ?>
                <tr>
                    <th colspan="2"></th>
                    <th colspan="2" class="th-title" style="text-align:center;"><?= htmlspecialchars($p1_label) ?></th>
                    <th colspan="2" class="th-title" style="text-align:center;"><?= htmlspecialchars($p2_label) ?></th>
                </tr>
                <tr>
                    <th colspan="2"></th>
                    <th class="th-title">Realisasi (IDR)</th>
                    <th class="th-title">Anggaran (IDR)</th>
                    <th class="th-title">Realisasi (IDR)</th>
                    <th class="th-title">Anggaran (IDR)</th>
                </tr>
            <?php endif; ?>
        </thead>
        <tbody>
            <?php
            $renderRow = function($val1, $val2 = 0.0, $val3 = 0.0, $val4 = 0.0, $cls = 'cell-num') use ($report_mode) {
                if ($report_mode === 'standar') {
                    echo '<td class="' . $cls . '">' . number_format($val1, 2, '.', '') . '</td>';
                } elseif (in_array($report_mode, ['ytd', 'anggaran', 'perbandingan_2'], true)) {
                    echo '<td class="' . $cls . '">' . number_format($val1, 2, '.', '') . '</td>';
                    echo '<td class="' . $cls . '">' . number_format($val2, 2, '.', '') . '</td>';
                } elseif ($report_mode === 'perbandingan_4') {
                    echo '<td class="' . $cls . '">' . number_format($val1, 2, '.', '') . '</td>';
                    echo '<td class="' . $cls . '">' . number_format($val2, 2, '.', '') . '</td>';
                    echo '<td class="' . $cls . '">' . number_format($val3, 2, '.', '') . '</td>';
                    echo '<td class="' . $cls . '">' . number_format($val4, 2, '.', '') . '</td>';
                }
            };

            // 1. PENDAPATAN
            ?>
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-title">Pendapatan</td></tr>
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-sub">Pendapatan Usaha</td></tr>
            <?php if (!empty($groups['pendapatan_usaha'])): ?>
                <?php foreach ($groups['pendapatan_usaha'] as $r): ?>
                    <tr>
                        <td class="cell-code"><?= $r['kode_tampil'] ?></td>
                        <td class="cell-name"><?= htmlspecialchars($r['nama_akun']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $renderRow($r['saldo_p1'], ($report_mode === 'perbandingan_4' ? $r['budget_p1'] : $r['saldo_p2']), $r['saldo_p2'], $r['budget_p2']);
                        } else {
                            $renderRow($r['saldo'], ($report_mode === 'ytd' ? $r['ytd'] : $r['budget']));
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td colspan="2" class="subtotal-title">Total Pendapatan Usaha</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_pendapatan_usaha'];
                    $s2 = $data['summary_p2']['total_pendapatan_usaha'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'subtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_pendapatan_usaha'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_pendapatan_usaha'] : 0.0), 0, 0, 'subtotal-num');
                }
                ?>
            </tr>

            <tr><td colspan="<?= $colSpanAll ?>" class="sec-sub">Pendapatan Lain</td></tr>
            <?php if (!empty($groups['pendapatan_lain'])): ?>
                <?php foreach ($groups['pendapatan_lain'] as $r): ?>
                    <tr>
                        <td class="cell-code"><?= $r['kode_tampil'] ?></td>
                        <td class="cell-name"><?= htmlspecialchars($r['nama_akun']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $renderRow($r['saldo_p1'], ($report_mode === 'perbandingan_4' ? $r['budget_p1'] : $r['saldo_p2']), $r['saldo_p2'], $r['budget_p2']);
                        } else {
                            $renderRow($r['saldo'], ($report_mode === 'ytd' ? $r['ytd'] : $r['budget']));
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td colspan="2" class="subtotal-title">Total Pendapatan Lain</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_pendapatan_lain'];
                    $s2 = $data['summary_p2']['total_pendapatan_lain'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'subtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_pendapatan_lain'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_pendapatan_lain'] : 0.0), 0, 0, 'subtotal-num');
                }
                ?>
            </tr>
            <tr>
                <td colspan="2" class="grandtotal-title">Total Pendapatan</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_pendapatan'];
                    $s2 = $data['summary_p2']['total_pendapatan'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'grandtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_pendapatan'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_pendapatan'] : 0.0), 0, 0, 'grandtotal-num');
                }
                ?>
            </tr>

            <!-- 2. BEBAN ATAS PENDAPATAN -->
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-title">Beban Atas Pendapatan</td></tr>
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-sub">Beban atas Pendapatan</td></tr>
            <?php if (!empty($groups['beban_atas_pendapatan'])): ?>
                <?php foreach ($groups['beban_atas_pendapatan'] as $r): ?>
                    <tr>
                        <td class="cell-code"><?= $r['kode_tampil'] ?></td>
                        <td class="cell-name"><?= htmlspecialchars($r['nama_akun']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $renderRow($r['saldo_p1'], ($report_mode === 'perbandingan_4' ? $r['budget_p1'] : $r['saldo_p2']), $r['saldo_p2'], $r['budget_p2']);
                        } else {
                            $renderRow($r['saldo'], ($report_mode === 'ytd' ? $r['ytd'] : $r['budget']));
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td colspan="2" class="subtotal-title">Total Beban atas Pendapatan</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_beban_pokok'];
                    $s2 = $data['summary_p2']['total_beban_pokok'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'subtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_beban_pokok'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_pokok'] : 0.0), 0, 0, 'subtotal-num');
                }
                ?>
            </tr>
            <tr>
                <td colspan="2" class="grandtotal-title">Total Beban Atas Pendapatan</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_beban_pokok'];
                    $s2 = $data['summary_p2']['total_beban_pokok'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'grandtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_beban_pokok'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_pokok'] : 0.0), 0, 0, 'grandtotal-num');
                }
                ?>
            </tr>

            <!-- LABA/RUGI KOTOR -->
            <tr>
                <td colspan="2" class="grandtotal-title">Laba/Rugi Kotor</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['laba_rugi_kotor'];
                    $s2 = $data['summary_p2']['laba_rugi_kotor'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'grandtotal-num');
                } else {
                    $renderRow($data['summary_cur']['laba_rugi_kotor'], ($report_mode === 'ytd' ? $data['summary_ytd']['laba_rugi_kotor'] : 0.0), 0, 0, 'grandtotal-num');
                }
                ?>
            </tr>

            <!-- 3. BEBAN OPERASIONAL -->
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-title">Beban Operasional</td></tr>
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-sub">Beban Pemasaran Dan Penjualan</td></tr>
            <?php if (!empty($groups['beban_pemasaran_penjualan'])): ?>
                <?php foreach ($groups['beban_pemasaran_penjualan'] as $r): ?>
                    <tr>
                        <td class="cell-code"><?= $r['kode_tampil'] ?></td>
                        <td class="cell-name"><?= htmlspecialchars($r['nama_akun']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $renderRow($r['saldo_p1'], ($report_mode === 'perbandingan_4' ? $r['budget_p1'] : $r['saldo_p2']), $r['saldo_p2'], $r['budget_p2']);
                        } else {
                            $renderRow($r['saldo'], ($report_mode === 'ytd' ? $r['ytd'] : $r['budget']));
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td colspan="2" class="subtotal-title">Total Beban Pemasaran Dan Penjualan</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_beban_pemasaran'];
                    $s2 = $data['summary_p2']['total_beban_pemasaran'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'subtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_beban_pemasaran'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_pemasaran'] : 0.0), 0, 0, 'subtotal-num');
                }
                ?>
            </tr>

            <tr><td colspan="<?= $colSpanAll ?>" class="sec-sub">Biaya Adm & Umum</td></tr>
            <?php if (!empty($groups['biaya_adm_umum'])): ?>
                <?php foreach ($groups['biaya_adm_umum'] as $r): ?>
                    <tr>
                        <td class="cell-code"><?= $r['kode_tampil'] ?></td>
                        <td class="cell-name"><?= htmlspecialchars($r['nama_akun']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $renderRow($r['saldo_p1'], ($report_mode === 'perbandingan_4' ? $r['budget_p1'] : $r['saldo_p2']), $r['saldo_p2'], $r['budget_p2']);
                        } else {
                            $renderRow($r['saldo'], ($report_mode === 'ytd' ? $r['ytd'] : $r['budget']));
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td colspan="2" class="subtotal-title">Total Biaya Adm & Umum</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_biaya_adm_umum'];
                    $s2 = $data['summary_p2']['total_biaya_adm_umum'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'subtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_biaya_adm_umum'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_biaya_adm_umum'] : 0.0), 0, 0, 'subtotal-num');
                }
                ?>
            </tr>
            <tr>
                <td colspan="2" class="grandtotal-title">Total Beban Operasional</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_beban_operasional'];
                    $s2 = $data['summary_p2']['total_beban_operasional'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'grandtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_beban_operasional'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_operasional'] : 0.0), 0, 0, 'grandtotal-num');
                }
                ?>
            </tr>

            <!-- LABA/RUGI OPERASI -->
            <tr>
                <td colspan="2" class="grandtotal-title">Laba/Rugi Operasi</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['laba_rugi_operasi'];
                    $s2 = $data['summary_p2']['laba_rugi_operasi'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'grandtotal-num');
                } else {
                    $renderRow($data['summary_cur']['laba_rugi_operasi'], ($report_mode === 'ytd' ? $data['summary_ytd']['laba_rugi_operasi'] : 0.0), 0, 0, 'grandtotal-num');
                }
                ?>
            </tr>

            <!-- 4. BEBAN NON OPERASIONAL -->
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-title">Beban Non Operasional</td></tr>
            <tr>
                <td colspan="2" class="grandtotal-title">Total Beban Non Operasional</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_beban_non_ops'];
                    $s2 = $data['summary_p2']['total_beban_non_ops'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'grandtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_beban_non_ops'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_non_ops'] : 0.0), 0, 0, 'grandtotal-num');
                }
                ?>
            </tr>
            <tr>
                <td colspan="2" class="grandtotal-title">Laba/Rugi Operasi</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['laba_rugi_operasi'];
                    $s2 = $data['summary_p2']['laba_rugi_operasi'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'grandtotal-num');
                } else {
                    $renderRow($data['summary_cur']['laba_rugi_operasi'], ($report_mode === 'ytd' ? $data['summary_ytd']['laba_rugi_operasi'] : 0.0), 0, 0, 'grandtotal-num');
                }
                ?>
            </tr>

            <!-- 5. PENDAPATAN LAIN -->
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-title">Pendapatan Lain</td></tr>
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-sub">Pendapatan Luar Usaha</td></tr>
            <?php if (!empty($groups['pendapatan_luar_usaha'])): ?>
                <?php foreach ($groups['pendapatan_luar_usaha'] as $r): ?>
                    <tr>
                        <td class="cell-code"><?= $r['kode_tampil'] ?></td>
                        <td class="cell-name"><?= htmlspecialchars($r['nama_akun']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $renderRow($r['saldo_p1'], ($report_mode === 'perbandingan_4' ? $r['budget_p1'] : $r['saldo_p2']), $r['saldo_p2'], $r['budget_p2']);
                        } else {
                            $renderRow($r['saldo'], ($report_mode === 'ytd' ? $r['ytd'] : $r['budget']));
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td colspan="2" class="subtotal-title">Total Pendapatan Luar Usaha</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_pendapatan_luar'];
                    $s2 = $data['summary_p2']['total_pendapatan_luar'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'subtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_pendapatan_luar'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_pendapatan_luar'] : 0.0), 0, 0, 'subtotal-num');
                }
                ?>
            </tr>
            <tr>
                <td colspan="2" class="grandtotal-title">Total Pendapatan Lain</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_pendapatan_lain_all'];
                    $s2 = $data['summary_p2']['total_pendapatan_lain_all'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'grandtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_pendapatan_lain_all'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_pendapatan_lain_all'] : 0.0), 0, 0, 'grandtotal-num');
                }
                ?>
            </tr>

            <!-- 6. BEBAN LAIN -->
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-title">Beban Lain</td></tr>
            <tr><td colspan="<?= $colSpanAll ?>" class="sec-sub">Beban Luar Usaha</td></tr>
            <?php if (!empty($groups['beban_luar_usaha'])): ?>
                <?php foreach ($groups['beban_luar_usaha'] as $r): ?>
                    <tr>
                        <td class="cell-code"><?= $r['kode_tampil'] ?></td>
                        <td class="cell-name"><?= htmlspecialchars($r['nama_akun']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $renderRow($r['saldo_p1'], ($report_mode === 'perbandingan_4' ? $r['budget_p1'] : $r['saldo_p2']), $r['saldo_p2'], $r['budget_p2']);
                        } else {
                            $renderRow($r['saldo'], ($report_mode === 'ytd' ? $r['ytd'] : $r['budget']));
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td colspan="2" class="subtotal-title">Total Beban Luar Usaha</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_beban_luar'];
                    $s2 = $data['summary_p2']['total_beban_luar'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'subtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_beban_luar'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_luar'] : 0.0), 0, 0, 'subtotal-num');
                }
                ?>
            </tr>

            <tr><td colspan="<?= $colSpanAll ?>" class="sec-sub">Beban Pajak</td></tr>
            <?php if (!empty($groups['beban_pajak'])): ?>
                <?php foreach ($groups['beban_pajak'] as $r): ?>
                    <tr>
                        <td class="cell-code"><?= $r['kode_tampil'] ?></td>
                        <td class="cell-name"><?= htmlspecialchars($r['nama_akun']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $renderRow($r['saldo_p1'], ($report_mode === 'perbandingan_4' ? $r['budget_p1'] : $r['saldo_p2']), $r['saldo_p2'], $r['budget_p2']);
                        } else {
                            $renderRow($r['saldo'], ($report_mode === 'ytd' ? $r['ytd'] : $r['budget']));
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td colspan="2" class="subtotal-title">Total Beban Pajak</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_beban_pajak'];
                    $s2 = $data['summary_p2']['total_beban_pajak'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'subtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_beban_pajak'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_pajak'] : 0.0), 0, 0, 'subtotal-num');
                }
                ?>
            </tr>
            <tr>
                <td colspan="2" class="grandtotal-title">Total Beban Lain</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['total_beban_lain_all'];
                    $s2 = $data['summary_p2']['total_beban_lain_all'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'grandtotal-num');
                } else {
                    $renderRow($data['summary_cur']['total_beban_lain_all'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_lain_all'] : 0.0), 0, 0, 'grandtotal-num');
                }
                ?>
            </tr>

            <!-- 7. LABA/RUGI BERSIH -->
            <tr>
                <td colspan="2" class="netincome-title">Laba/Rugi Bersih</td>
                <?php 
                if ($isCompare) {
                    $s1 = $data['summary_p1']['laba_rugi_bersih'];
                    $s2 = $data['summary_p2']['laba_rugi_bersih'];
                    $renderRow($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0, 'netincome-num');
                } else {
                    $renderRow($data['summary_cur']['laba_rugi_bersih'], ($report_mode === 'ytd' ? $data['summary_ytd']['laba_rugi_bersih'] : 0.0), 0, 0, 'netincome-num');
                }
                ?>
            </tr>
        </tbody>
    </table>
</body>
</html>
