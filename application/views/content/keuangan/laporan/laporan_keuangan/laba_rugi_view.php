<?php
/**
 * laba_rugi_view.php
 * Tampilan Laporan Laba Rugi serbaguna bergaya Zahir Accounting.
 * Mendukung mode:
 * 1. 'standar'        : 1 kolom Saldo IDR
 * 2. 'ytd'            : 2 kolom Saldo IDR & Year to Date IDR
 * 3. 'perbandingan_2' : 2 kolom Periode 1 vs Periode 2
 * 4. 'anggaran'       : 2 kolom Realisasi vs Anggaran
 * 5. 'perbandingan_4' : 4 kolom Perbandingan Realisasi & Anggaran 2 Periode
 */
$back_url = base_url('laporan/keuangan');
$isCompare = in_array($report_mode, ['perbandingan_2', 'perbandingan_4'], true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <style>
        .lr-page-container {
            font-family: 'Segoe UI', Tahoma, Helvetica, Arial, sans-serif;
            background: #eef2f6;
            min-height: 100vh;
            padding: 24px 16px 48px;
            color: #1e293b;
        }
        .lr-toolbar {
            max-width: 960px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .lr-sheet {
            max-width: 960px;
            margin: 0 auto;
            background: #ffffff;
            padding: 36px 48px 48px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            border-radius: 4px;
            position: relative;
        }
        .lr-timestamp {
            font-size: 11px;
            color: #64748b;
            position: absolute;
            top: 24px;
            left: 48px;
        }
        .lr-header-center {
            text-align: center;
            margin-bottom: 24px;
        }
        .lr-company {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .lr-title {
            font-size: 24px;
            font-weight: 700;
            color: #008cba;
            margin: 4px 0 2px;
        }
        .lr-period {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-top: 4px;
        }
        .lr-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            line-height: 1.45;
        }
        .lr-table th {
            text-align: right;
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            padding: 4px 8px;
            border-bottom: 1px solid #cbd5e1;
            white-space: nowrap;
        }
        .lr-table th.th-left {
            text-align: left;
        }
        .lr-table td {
            padding: 2.5px 8px;
            vertical-align: middle;
        }
        /* Styling Hierarki Pos Akun Zahir */
        .lr-sec-main {
            font-size: 13.5px;
            font-weight: 700;
            color: #0284c7;
            padding-top: 14px !important;
            padding-bottom: 2px !important;
        }
        .lr-sec-sub {
            font-size: 12.5px;
            font-weight: 700;
            color: #0284c7;
            padding-left: 20px !important;
            padding-top: 4px !important;
            padding-bottom: 2px !important;
        }
        .lr-account-code {
            width: 75px;
            padding-left: 36px !important;
            color: #334155;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .lr-account-name {
            color: #1e293b;
        }
        .lr-amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            color: #0f172a;
            min-width: 120px;
        }
        .lr-amount.negative {
            color: #b91c1c;
        }
        .lr-subtotal-title {
            padding-left: 20px !important;
            font-weight: 700;
            color: #0284c7;
            padding-top: 4px !important;
            padding-bottom: 4px !important;
        }
        .lr-subtotal-amount {
            text-align: right;
            font-weight: 700;
            color: #0284c7;
            border-top: 1px solid #94a3b8;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .lr-grandtotal-title {
            font-weight: 700;
            color: #0284c7;
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }
        .lr-grandtotal-amount {
            text-align: right;
            font-weight: 700;
            color: #0284c7;
            border-top: 1px solid #0284c7;
            border-bottom: 1px solid #0284c7;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            padding-top: 6px !important;
            padding-bottom: 6px !important;
        }
        .lr-netincome-title {
            font-weight: 800;
            color: #0369a1;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            font-size: 13.5px;
        }
        .lr-netincome-amount {
            text-align: right;
            font-weight: 800;
            color: #0369a1;
            border-top: 1px solid #0369a1;
            border-bottom: 3px double #0369a1;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            font-size: 13.5px;
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }
        .lr-footer {
            margin-top: 32px;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            text-align: left;
        }

        /* Print Mode */
        @media print {
            body { background: #fff !important; }
            .lr-page-container { background: #fff !important; padding: 0 !important; }
            .lr-toolbar { display: none !important; }
            .lr-sheet {
                box-shadow: none !important;
                padding: 10mm 15mm !important;
                max-width: 100% !important;
            }
            .sidebar, .main-header, .navbar, .main-footer { display: none !important; }
        }
    </style>
</head>
<body>
<div class="content-wrapper" style="margin-left: 0 !important; background: #eef2f6;">
    <div class="lr-page-container">
        <!-- Action Toolbar -->
        <div class="lr-toolbar">
            <div class="d-flex align-items-center gap-2">
                <a href="<?= $back_url ?>" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <span class="badge badge-info p-2" style="font-size: 13px;">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    <?php if ($isCompare): ?>
                        <?= date('d M Y', strtotime($p1_start)) ?> s/d <?= date('d M Y', strtotime($p1_end)) ?> &nbsp;|&nbsp; 
                        <?= date('d M Y', strtotime($p2_start)) ?> s/d <?= date('d M Y', strtotime($p2_end)) ?>
                    <?php else: ?>
                        <?= date('d M Y', strtotime($start_date)) ?> s/d <?= date('d M Y', strtotime($end_date)) ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-info btn-sm mr-2" onclick="openZahirFilter('<?= $report_mode ?>')">
                    <i class="fas fa-filter mr-1"></i> Filter Data
                </button>
                <a href="<?= $export_url ?>" class="btn btn-success btn-sm mr-2">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Cetak / PDF
                </button>
            </div>
        </div>

        <!-- Report Canvas Sheet -->
        <div class="lr-sheet">
            <div class="lr-timestamp"><?= $print_time ?></div>

            <div class="lr-header-center">
                <div class="lr-company"><?= htmlspecialchars($company_name) ?></div>
                <div class="lr-title"><?= htmlspecialchars($report_title) ?></div>
                <?php if (!$isCompare): ?>
                    <div class="lr-period"><?= htmlspecialchars($period_label) ?></div>
                <?php endif; ?>
            </div>

            <table class="lr-table">
                <thead>
                    <?php if ($report_mode === 'standar'): ?>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th class="lr-amount">Saldo<br><span style="font-weight:normal; font-size:11px;">IDR</span></th>
                        </tr>
                    <?php elseif ($report_mode === 'ytd'): ?>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th class="lr-amount">Saldo<br><span style="font-weight:normal; font-size:11px;">IDR</span></th>
                            <th class="lr-amount">Year to Date<br><span style="font-weight:normal; font-size:11px;">IDR</span></th>
                        </tr>
                    <?php elseif ($report_mode === 'anggaran'): ?>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th class="lr-amount">Realisasi<br><span style="font-weight:normal; font-size:11px;">IDR</span></th>
                            <th class="lr-amount">Anggaran<br><span style="font-weight:normal; font-size:11px;">IDR</span></th>
                        </tr>
                    <?php elseif ($report_mode === 'perbandingan_2'): ?>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th class="lr-amount"><?= htmlspecialchars($p1_label) ?><br><span style="font-weight:normal; font-size:11px;">IDR</span></th>
                            <th class="lr-amount"><?= htmlspecialchars($p2_label) ?><br><span style="font-weight:normal; font-size:11px;">IDR</span></th>
                        </tr>
                    <?php elseif ($report_mode === 'perbandingan_4'): ?>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th colspan="2" style="text-align:center; border-bottom:1px solid #cbd5e1;"><?= htmlspecialchars($p1_label) ?></th>
                            <th colspan="2" style="text-align:center; border-bottom:1px solid #cbd5e1;"><?= htmlspecialchars($p2_label) ?></th>
                        </tr>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th class="lr-amount">Realisasi (IDR)</th>
                            <th class="lr-amount">Anggaran (IDR)</th>
                            <th class="lr-amount">Realisasi (IDR)</th>
                            <th class="lr-amount">Anggaran (IDR)</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php
                    // Helper renderer untuk baris angka
                    $renderCells = function($val1, $val2 = null, $val3 = null, $val4 = null) use ($report_mode) {
                        $fmt = function($v) {
                            $isNeg = ($v < -0.0001);
                            $cls = $isNeg ? ' negative' : '';
                            return '<td class="lr-amount' . $cls . '">' . number_format($v, 2, '.', ',') . '</td>';
                        };

                        if ($report_mode === 'standar') {
                            echo $fmt($val1);
                        } elseif (in_array($report_mode, ['ytd', 'anggaran', 'perbandingan_2'], true)) {
                            echo $fmt($val1);
                            echo $fmt($val2 ?? 0.0);
                        } elseif ($report_mode === 'perbandingan_4') {
                            echo $fmt($val1);
                            echo $fmt($val2 ?? 0.0);
                            echo $fmt($val3 ?? 0.0);
                            echo $fmt($val4 ?? 0.0);
                        }
                    };

                    $renderSubtotalCells = function($val1, $val2 = null, $val3 = null, $val4 = null) use ($report_mode) {
                        $fmt = function($v) {
                            $isNeg = ($v < -0.0001);
                            $cls = $isNeg ? ' negative' : '';
                            return '<td class="lr-subtotal-amount' . $cls . '">' . number_format($v, 2, '.', ',') . '</td>';
                        };

                        if ($report_mode === 'standar') {
                            echo $fmt($val1);
                        } elseif (in_array($report_mode, ['ytd', 'anggaran', 'perbandingan_2'], true)) {
                            echo $fmt($val1);
                            echo $fmt($val2 ?? 0.0);
                        } elseif ($report_mode === 'perbandingan_4') {
                            echo $fmt($val1);
                            echo $fmt($val2 ?? 0.0);
                            echo $fmt($val3 ?? 0.0);
                            echo $fmt($val4 ?? 0.0);
                        }
                    };

                    $renderGrandtotalCells = function($val1, $val2 = null, $val3 = null, $val4 = null) use ($report_mode) {
                        $fmt = function($v) {
                            $isNeg = ($v < -0.0001);
                            $cls = $isNeg ? ' negative' : '';
                            return '<td class="lr-grandtotal-amount' . $cls . '">' . number_format($v, 2, '.', ',') . '</td>';
                        };

                        if ($report_mode === 'standar') {
                            echo $fmt($val1);
                        } elseif (in_array($report_mode, ['ytd', 'anggaran', 'perbandingan_2'], true)) {
                            echo $fmt($val1);
                            echo $fmt($val2 ?? 0.0);
                        } elseif ($report_mode === 'perbandingan_4') {
                            echo $fmt($val1);
                            echo $fmt($val2 ?? 0.0);
                            echo $fmt($val3 ?? 0.0);
                            echo $fmt($val4 ?? 0.0);
                        }
                    };

                    $renderNetIncomeCells = function($val1, $val2 = null, $val3 = null, $val4 = null) use ($report_mode) {
                        $fmt = function($v) {
                            $isNeg = ($v < -0.0001);
                            $cls = $isNeg ? ' negative' : '';
                            return '<td class="lr-netincome-amount' . $cls . '">' . number_format($v, 2, '.', ',') . '</td>';
                        };

                        if ($report_mode === 'standar') {
                            echo $fmt($val1);
                        } elseif (in_array($report_mode, ['ytd', 'anggaran', 'perbandingan_2'], true)) {
                            echo $fmt($val1);
                            echo $fmt($val2 ?? 0.0);
                        } elseif ($report_mode === 'perbandingan_4') {
                            echo $fmt($val1);
                            echo $fmt($val2 ?? 0.0);
                            echo $fmt($val3 ?? 0.0);
                            echo $fmt($val4 ?? 0.0);
                        }
                    };

                    // Data shortcut
                    $groups = $data['groups'];
                    $colSpanAll = ($report_mode === 'perbandingan_4') ? 6 : (($report_mode === 'standar') ? 3 : 4);

                    // ==========================================
                    // 1. PENDAPATAN
                    // ==========================================
                    ?>
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-main">Pendapatan</td>
                    </tr>
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-sub">Pendapatan Usaha</td>
                    </tr>
                    <?php if (!empty($groups['pendapatan_usaha'])): ?>
                        <?php foreach ($groups['pendapatan_usaha'] as $row): ?>
                            <tr>
                                <td class="lr-account-code"><?= $row['kode_tampil'] ?></td>
                                <td class="lr-account-name"><?= htmlspecialchars($row['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderCells($row['saldo_p1'], ($report_mode === 'perbandingan_4' ? $row['budget_p1'] : $row['saldo_p2']), $row['saldo_p2'], $row['budget_p2']);
                                } else {
                                    $renderCells($row['saldo'], ($report_mode === 'ytd' ? $row['ytd'] : $row['budget']));
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="2" class="lr-subtotal-title">Total Pendapatan Usaha</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_pendapatan_usaha'];
                            $s2 = $data['summary_p2']['total_pendapatan_usaha'];
                            $renderSubtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderSubtotalCells($data['summary_cur']['total_pendapatan_usaha'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_pendapatan_usaha'] : 0.0));
                        }
                        ?>
                    </tr>

                    <?php if (!empty($groups['pendapatan_lain']) || $report_mode === 'standar' || $report_mode === 'ytd'): ?>
                        <tr>
                            <td colspan="<?= $colSpanAll ?>" class="lr-sec-sub">Pendapatan Lain</td>
                        </tr>
                        <?php if (!empty($groups['pendapatan_lain'])): ?>
                            <?php foreach ($groups['pendapatan_lain'] as $row): ?>
                                <tr>
                                    <td class="lr-account-code"><?= $row['kode_tampil'] ?></td>
                                    <td class="lr-account-name"><?= htmlspecialchars($row['nama_akun']) ?></td>
                                    <?php 
                                    if ($isCompare) {
                                        $renderCells($row['saldo_p1'], ($report_mode === 'perbandingan_4' ? $row['budget_p1'] : $row['saldo_p2']), $row['saldo_p2'], $row['budget_p2']);
                                    } else {
                                        $renderCells($row['saldo'], ($report_mode === 'ytd' ? $row['ytd'] : $row['budget']));
                                    }
                                    ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr>
                            <td colspan="2" class="lr-subtotal-title">Total Pendapatan Lain</td>
                            <?php 
                            if ($isCompare) {
                                $s1 = $data['summary_p1']['total_pendapatan_lain'];
                                $s2 = $data['summary_p2']['total_pendapatan_lain'];
                                $renderSubtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                            } else {
                                $renderSubtotalCells($data['summary_cur']['total_pendapatan_lain'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_pendapatan_lain'] : 0.0));
                            }
                            ?>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td colspan="2" class="lr-grandtotal-title">Total Pendapatan</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_pendapatan'];
                            $s2 = $data['summary_p2']['total_pendapatan'];
                            $renderGrandtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderGrandtotalCells($data['summary_cur']['total_pendapatan'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_pendapatan'] : 0.0));
                        }
                        ?>
                    </tr>

                    <!-- ==========================================
                         2. BEBAN ATAS PENDAPATAN
                         ========================================== -->
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-main">Beban Atas Pendapatan</td>
                    </tr>
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-sub">Beban atas Pendapatan</td>
                    </tr>
                    <?php if (!empty($groups['beban_atas_pendapatan'])): ?>
                        <?php foreach ($groups['beban_atas_pendapatan'] as $row): ?>
                            <tr>
                                <td class="lr-account-code"><?= $row['kode_tampil'] ?></td>
                                <td class="lr-account-name"><?= htmlspecialchars($row['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderCells($row['saldo_p1'], ($report_mode === 'perbandingan_4' ? $row['budget_p1'] : $row['saldo_p2']), $row['saldo_p2'], $row['budget_p2']);
                                } else {
                                    $renderCells($row['saldo'], ($report_mode === 'ytd' ? $row['ytd'] : $row['budget']));
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="2" class="lr-subtotal-title">Total Beban atas Pendapatan</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_beban_pokok'];
                            $s2 = $data['summary_p2']['total_beban_pokok'];
                            $renderSubtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderSubtotalCells($data['summary_cur']['total_beban_pokok'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_pokok'] : 0.0));
                        }
                        ?>
                    </tr>
                    <tr>
                        <td colspan="2" class="lr-grandtotal-title">Total Beban Atas Pendapatan</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_beban_pokok'];
                            $s2 = $data['summary_p2']['total_beban_pokok'];
                            $renderGrandtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderGrandtotalCells($data['summary_cur']['total_beban_pokok'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_pokok'] : 0.0));
                        }
                        ?>
                    </tr>

                    <!-- ==========================================
                         LABA/RUGI KOTOR
                         ========================================== -->
                    <tr>
                        <td colspan="2" class="lr-grandtotal-title">Laba/Rugi Kotor</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['laba_rugi_kotor'];
                            $s2 = $data['summary_p2']['laba_rugi_kotor'];
                            $renderGrandtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderGrandtotalCells($data['summary_cur']['laba_rugi_kotor'], ($report_mode === 'ytd' ? $data['summary_ytd']['laba_rugi_kotor'] : 0.0));
                        }
                        ?>
                    </tr>

                    <!-- ==========================================
                         3. BEBAN OPERASIONAL
                         ========================================== -->
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-main">Beban Operasional</td>
                    </tr>
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-sub">Beban Pemasaran Dan Penjualan</td>
                    </tr>
                    <?php if (!empty($groups['beban_pemasaran_penjualan'])): ?>
                        <?php foreach ($groups['beban_pemasaran_penjualan'] as $row): ?>
                            <tr>
                                <td class="lr-account-code"><?= $row['kode_tampil'] ?></td>
                                <td class="lr-account-name"><?= htmlspecialchars($row['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderCells($row['saldo_p1'], ($report_mode === 'perbandingan_4' ? $row['budget_p1'] : $row['saldo_p2']), $row['saldo_p2'], $row['budget_p2']);
                                } else {
                                    $renderCells($row['saldo'], ($report_mode === 'ytd' ? $row['ytd'] : $row['budget']));
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="2" class="lr-subtotal-title">Total Beban Pemasaran Dan Penjualan</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_beban_pemasaran'];
                            $s2 = $data['summary_p2']['total_beban_pemasaran'];
                            $renderSubtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderSubtotalCells($data['summary_cur']['total_beban_pemasaran'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_pemasaran'] : 0.0));
                        }
                        ?>
                    </tr>

                    <!-- BIAYA ADM & UMUM -->
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-sub">Biaya Adm & Umum</td>
                    </tr>
                    <?php if (!empty($groups['biaya_adm_umum'])): ?>
                        <?php foreach ($groups['biaya_adm_umum'] as $row): ?>
                            <tr>
                                <td class="lr-account-code"><?= $row['kode_tampil'] ?></td>
                                <td class="lr-account-name"><?= htmlspecialchars($row['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderCells($row['saldo_p1'], ($report_mode === 'perbandingan_4' ? $row['budget_p1'] : $row['saldo_p2']), $row['saldo_p2'], $row['budget_p2']);
                                } else {
                                    $renderCells($row['saldo'], ($report_mode === 'ytd' ? $row['ytd'] : $row['budget']));
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="2" class="lr-subtotal-title">Total Biaya Adm & Umum</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_biaya_adm_umum'];
                            $s2 = $data['summary_p2']['total_biaya_adm_umum'];
                            $renderSubtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderSubtotalCells($data['summary_cur']['total_biaya_adm_umum'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_biaya_adm_umum'] : 0.0));
                        }
                        ?>
                    </tr>

                    <tr>
                        <td colspan="2" class="lr-grandtotal-title">Total Beban Operasional</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_beban_operasional'];
                            $s2 = $data['summary_p2']['total_beban_operasional'];
                            $renderGrandtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderGrandtotalCells($data['summary_cur']['total_beban_operasional'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_operasional'] : 0.0));
                        }
                        ?>
                    </tr>

                    <!-- LABA/RUGI OPERASI -->
                    <tr>
                        <td colspan="2" class="lr-grandtotal-title">Laba/Rugi Operasi</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['laba_rugi_operasi'];
                            $s2 = $data['summary_p2']['laba_rugi_operasi'];
                            $renderGrandtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderGrandtotalCells($data['summary_cur']['laba_rugi_operasi'], ($report_mode === 'ytd' ? $data['summary_ytd']['laba_rugi_operasi'] : 0.0));
                        }
                        ?>
                    </tr>

                    <!-- ==========================================
                         4. BEBAN NON OPERASIONAL
                         ========================================== -->
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-main">Beban Non Operasional</td>
                    </tr>
                    <?php if (!empty($groups['beban_non_operasional'])): ?>
                        <?php foreach ($groups['beban_non_operasional'] as $row): ?>
                            <tr>
                                <td class="lr-account-code"><?= $row['kode_tampil'] ?></td>
                                <td class="lr-account-name"><?= htmlspecialchars($row['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderCells($row['saldo_p1'], ($report_mode === 'perbandingan_4' ? $row['budget_p1'] : $row['saldo_p2']), $row['saldo_p2'], $row['budget_p2']);
                                } else {
                                    $renderCells($row['saldo'], ($report_mode === 'ytd' ? $row['ytd'] : $row['budget']));
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="2" class="lr-grandtotal-title">Total Beban Non Operasional</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_beban_non_ops'];
                            $s2 = $data['summary_p2']['total_beban_non_ops'];
                            $renderGrandtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderGrandtotalCells($data['summary_cur']['total_beban_non_ops'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_non_ops'] : 0.0));
                        }
                        ?>
                    </tr>
                    <tr>
                        <td colspan="2" class="lr-grandtotal-title">Laba/Rugi Operasi</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['laba_rugi_operasi'];
                            $s2 = $data['summary_p2']['laba_rugi_operasi'];
                            $renderGrandtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderGrandtotalCells($data['summary_cur']['laba_rugi_operasi'], ($report_mode === 'ytd' ? $data['summary_ytd']['laba_rugi_operasi'] : 0.0));
                        }
                        ?>
                    </tr>

                    <!-- ==========================================
                         5. PENDAPATAN LAIN
                         ========================================== -->
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-main">Pendapatan Lain</td>
                    </tr>
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-sub">Pendapatan Luar Usaha</td>
                    </tr>
                    <?php if (!empty($groups['pendapatan_luar_usaha'])): ?>
                        <?php foreach ($groups['pendapatan_luar_usaha'] as $row): ?>
                            <tr>
                                <td class="lr-account-code"><?= $row['kode_tampil'] ?></td>
                                <td class="lr-account-name"><?= htmlspecialchars($row['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderCells($row['saldo_p1'], ($report_mode === 'perbandingan_4' ? $row['budget_p1'] : $row['saldo_p2']), $row['saldo_p2'], $row['budget_p2']);
                                } else {
                                    $renderCells($row['saldo'], ($report_mode === 'ytd' ? $row['ytd'] : $row['budget']));
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="2" class="lr-subtotal-title">Total Pendapatan Luar Usaha</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_pendapatan_luar'];
                            $s2 = $data['summary_p2']['total_pendapatan_luar'];
                            $renderSubtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderSubtotalCells($data['summary_cur']['total_pendapatan_luar'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_pendapatan_luar'] : 0.0));
                        }
                        ?>
                    </tr>
                    <tr>
                        <td colspan="2" class="lr-grandtotal-title">Total Pendapatan Lain</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_pendapatan_lain_all'];
                            $s2 = $data['summary_p2']['total_pendapatan_lain_all'];
                            $renderGrandtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderGrandtotalCells($data['summary_cur']['total_pendapatan_lain_all'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_pendapatan_lain_all'] : 0.0));
                        }
                        ?>
                    </tr>

                    <!-- ==========================================
                         6. BEBAN LAIN
                         ========================================== -->
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-main">Beban Lain</td>
                    </tr>
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-sub">Beban Luar Usaha</td>
                    </tr>
                    <?php if (!empty($groups['beban_luar_usaha'])): ?>
                        <?php foreach ($groups['beban_luar_usaha'] as $row): ?>
                            <tr>
                                <td class="lr-account-code"><?= $row['kode_tampil'] ?></td>
                                <td class="lr-account-name"><?= htmlspecialchars($row['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderCells($row['saldo_p1'], ($report_mode === 'perbandingan_4' ? $row['budget_p1'] : $row['saldo_p2']), $row['saldo_p2'], $row['budget_p2']);
                                } else {
                                    $renderCells($row['saldo'], ($report_mode === 'ytd' ? $row['ytd'] : $row['budget']));
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="2" class="lr-subtotal-title">Total Beban Luar Usaha</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_beban_luar'];
                            $s2 = $data['summary_p2']['total_beban_luar'];
                            $renderSubtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderSubtotalCells($data['summary_cur']['total_beban_luar'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_luar'] : 0.0));
                        }
                        ?>
                    </tr>

                    <!-- BEBAN PAJAK -->
                    <tr>
                        <td colspan="<?= $colSpanAll ?>" class="lr-sec-sub">Beban Pajak</td>
                    </tr>
                    <?php if (!empty($groups['beban_pajak'])): ?>
                        <?php foreach ($groups['beban_pajak'] as $row): ?>
                            <tr>
                                <td class="lr-account-code"><?= $row['kode_tampil'] ?></td>
                                <td class="lr-account-name"><?= htmlspecialchars($row['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderCells($row['saldo_p1'], ($report_mode === 'perbandingan_4' ? $row['budget_p1'] : $row['saldo_p2']), $row['saldo_p2'], $row['budget_p2']);
                                } else {
                                    $renderCells($row['saldo'], ($report_mode === 'ytd' ? $row['ytd'] : $row['budget']));
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr>
                        <td colspan="2" class="lr-subtotal-title">Total Beban Pajak</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_beban_pajak'];
                            $s2 = $data['summary_p2']['total_beban_pajak'];
                            $renderSubtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderSubtotalCells($data['summary_cur']['total_beban_pajak'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_pajak'] : 0.0));
                        }
                        ?>
                    </tr>
                    <tr>
                        <td colspan="2" class="lr-grandtotal-title">Total Beban Lain</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['total_beban_lain_all'];
                            $s2 = $data['summary_p2']['total_beban_lain_all'];
                            $renderGrandtotalCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderGrandtotalCells($data['summary_cur']['total_beban_lain_all'], ($report_mode === 'ytd' ? $data['summary_ytd']['total_beban_lain_all'] : 0.0));
                        }
                        ?>
                    </tr>

                    <!-- ==========================================
                         7. LABA/RUGI BERSIH
                         ========================================== -->
                    <tr>
                        <td colspan="2" class="lr-netincome-title">Laba/Rugi Bersih</td>
                        <?php 
                        if ($isCompare) {
                            $s1 = $data['summary_p1']['laba_rugi_bersih'];
                            $s2 = $data['summary_p2']['laba_rugi_bersih'];
                            $renderNetIncomeCells($s1, ($report_mode === 'perbandingan_4' ? 0.0 : $s2), $s2, 0.0);
                        } else {
                            $renderNetIncomeCells($data['summary_cur']['laba_rugi_bersih'], ($report_mode === 'ytd' ? $data['summary_ytd']['laba_rugi_bersih'] : 0.0));
                        }
                        ?>
                    </tr>
                </tbody>
            </table>

            <div class="lr-footer">
                Halaman : 1
            </div>
        </div>
    </div>
</div>

<?php 
$filter_action_url = $filter_action;
include APPPATH . 'views/content/keuangan/laporan/laporan_keuangan/_modal_filter_zahir.php'; 
?>
</body>
</html>
