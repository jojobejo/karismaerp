<?php
/**
 * neraca_view.php
 * Tampilan Laporan Neraca serbaguna bergaya Zahir Accounting.
 * Mendukung mode:
 * 1. 'standar'        : 1 kolom Saldo IDR
 * 2. '12_bulan'       : 12 kolom bulan (1 - YYYY s/d 12 - YYYY)
 * 3. 'perbandingan_2' : 2 kolom Periode 1 vs Periode 2
 * 4. 'anggaran'       : 2 kolom Realisasi vs Anggaran
 * 5. 'perbandingan_4' : 4 kolom Perbandingan Realisasi & Anggaran 2 Periode
 */
$back_url = base_url('laporan/keuangan');
$isCompare = in_array($report_mode, ['perbandingan_2', 'perbandingan_4'], true);
$is12Bulan = ($report_mode === '12_bulan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <style>
        .nc-page-container {
            font-family: 'Segoe UI', Tahoma, Helvetica, Arial, sans-serif;
            background: #eef2f6;
            min-height: 100vh;
            padding: 24px 16px 48px;
            color: #1e293b;
        }
        .nc-toolbar {
            max-width: <?= $is12Bulan ? '1280px' : '960px' ?>;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .nc-sheet {
            max-width: <?= $is12Bulan ? '1280px' : '960px' ?>;
            margin: 0 auto;
            background: #ffffff;
            padding: 36px 48px 48px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            border-radius: 4px;
            position: relative;
        }
        .nc-timestamp {
            font-size: 11px;
            color: #64748b;
            position: absolute;
            top: 24px;
            left: 48px;
        }
        .nc-header-center {
            text-align: center;
            margin-bottom: 24px;
        }
        .nc-company {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .nc-title {
            font-size: 24px;
            font-weight: 700;
            color: #008cba;
            margin: 4px 0 2px;
        }
        .nc-period {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-top: 4px;
        }
        .nc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: <?= $is12Bulan ? '11px' : '12.5px' ?>;
            line-height: 1.45;
        }
        .nc-table th {
            text-align: right;
            font-size: <?= $is12Bulan ? '10.5px' : '12px' ?>;
            font-weight: 600;
            color: #0f172a;
            padding: 4px 6px;
            border-bottom: 1px solid #cbd5e1;
            white-space: nowrap;
        }
        .nc-table th.th-left {
            text-align: left;
        }
        .nc-table td {
            padding: 2.5px 6px;
            vertical-align: middle;
        }
        .nc-sec-main {
            font-size: 13.5px;
            font-weight: 700;
            color: #0284c7;
            padding-top: 14px !important;
            padding-bottom: 2px !important;
        }
        .nc-sec-sub {
            font-size: 12.5px;
            font-weight: 700;
            color: #0284c7;
            padding-left: 18px !important;
            padding-top: 4px !important;
            padding-bottom: 2px !important;
        }
        .nc-account-code {
            width: 70px;
            padding-left: 32px !important;
            color: #334155;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .nc-account-name {
            color: #1e293b;
        }
        .nc-amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            color: #0f172a;
            min-width: <?= $is12Bulan ? '72px' : '110px' ?>;
        }
        .nc-amount.negative {
            color: #0f172a;
        }
        .nc-subtotal-title {
            padding-left: 18px !important;
            font-weight: 700;
            color: #0284c7;
            padding-top: 4px !important;
            padding-bottom: 4px !important;
        }
        .nc-subtotal-amount {
            text-align: right;
            font-weight: 700;
            color: #0284c7;
            border-top: 1px solid #cbd5e1;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .nc-grandtotal-title {
            font-weight: 700;
            color: #0284c7;
            padding-top: 8px !important;
            padding-bottom: 8px !important;
            font-size: 13px;
        }
        .nc-grandtotal-amount {
            text-align: right;
            font-weight: 700;
            color: #0284c7;
            border-top: 1px solid #0284c7;
            border-bottom: 1px solid #0284c7;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            padding-top: 6px !important;
            padding-bottom: 6px !important;
            font-size: 13px;
        }
        .nc-final-title {
            font-weight: 800;
            color: #0284c7;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            font-size: 13.5px;
        }
        .nc-final-amount {
            text-align: right;
            font-weight: 800;
            color: #0284c7;
            border-top: 1px solid #0284c7;
            border-bottom: 2px solid #0284c7;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            font-size: 13.5px;
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }
        .nc-footer {
            margin-top: 32px;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
        }

        /* Print styling */
        @media print {
            body { background: #fff !important; }
            .nc-page-container { background: #fff !important; padding: 0 !important; }
            .nc-toolbar { display: none !important; }
            .nc-sheet {
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
    <div class="nc-page-container">
        <!-- Action Toolbar -->
        <div class="nc-toolbar">
            <div class="d-flex align-items-center gap-2">
                <a href="<?= $back_url ?>" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <span class="badge badge-info p-2" style="font-size: 13px;">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    <?php if ($isCompare): ?>
                        <?= htmlspecialchars($p1_label) ?> &nbsp;|&nbsp; <?= htmlspecialchars($p2_label) ?>
                    <?php elseif ($is12Bulan): ?>
                        Tahun <?= htmlspecialchars($year) ?> (12 Bulan)
                    <?php else: ?>
                        <?= htmlspecialchars($period_label) ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-info btn-sm mr-2" onclick="openPeriodeFilter('<?= $report_mode ?>')">
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

        <!-- Sheet Canvas -->
        <div class="nc-sheet">
            <div class="nc-timestamp"><?= $print_time ?></div>

            <div class="nc-header-center">
                <div class="nc-company"><?= htmlspecialchars($company_name) ?></div>
                <div class="nc-title"><?= htmlspecialchars($report_title) ?></div>
                <div class="nc-period">
                    <?php if ($isCompare): ?>
                        <div style="display: flex; justify-content: flex-end; gap: 40px; padding-right: 10px;">
                            <span><?= htmlspecialchars($p1_label) ?></span>
                            <span><?= htmlspecialchars($p2_label) ?></span>
                        </div>
                    <?php elseif ($is12Bulan): ?>
                        <?= htmlspecialchars($period_label) ?>
                    <?php else: ?>
                        <?= htmlspecialchars($period_label) ?>
                    <?php endif; ?>
                </div>
            </div>

            <table class="nc-table">
                <thead>
                    <?php if ($report_mode === 'standar'): ?>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th class="nc-amount">IDR</th>
                        </tr>
                    <?php elseif ($report_mode === 'anggaran'): ?>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th class="nc-amount">Realisasi<br><span style="font-weight:normal; font-size:11px;">IDR</span></th>
                            <th class="nc-amount">Anggaran<br><span style="font-weight:normal; font-size:11px;">IDR</span></th>
                        </tr>
                    <?php elseif ($report_mode === 'perbandingan_2'): ?>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th class="nc-amount">IDR</th>
                            <th class="nc-amount">IDR</th>
                        </tr>
                    <?php elseif ($report_mode === 'perbandingan_4'): ?>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th colspan="2" style="text-align:center; border-bottom:1px solid #cbd5e1;"><?= htmlspecialchars($p1_label) ?></th>
                            <th colspan="2" style="text-align:center; border-bottom:1px solid #cbd5e1;"><?= htmlspecialchars($p2_label) ?></th>
                        </tr>
                        <tr>
                            <th colspan="2" class="th-left"></th>
                            <th class="nc-amount">Realisasi (IDR)</th>
                            <th class="nc-amount">Anggaran (IDR)</th>
                            <th class="nc-amount">Realisasi (IDR)</th>
                            <th class="nc-amount">Anggaran (IDR)</th>
                        </tr>
                    <?php elseif ($report_mode === '12_bulan'): ?>
                        <tr>
                            <th colspan="2" class="th-left">Nama Rekening</th>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <th class="nc-amount"><?= $m ?> - <?= $year ?></th>
                            <?php endfor; ?>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php
                    // Helper formatter
                    $fmt = function($v) {
                        return number_format((float)$v, 2, '.', ',');
                    };

                    // Render helper untuk baris angka berdasarkan mode
                    $renderCells = function($val1, $val2 = null, $val3 = null, $val4 = null) use ($report_mode, $fmt) {
                        if ($report_mode === 'standar') {
                            echo '<td class="nc-amount">' . $fmt($val1) . '</td>';
                        } elseif ($report_mode === 'anggaran') {
                            echo '<td class="nc-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-amount">' . $fmt($val2 ?? 0) . '</td>';
                        } elseif ($report_mode === 'perbandingan_2') {
                            echo '<td class="nc-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-amount">' . $fmt($val2 ?? 0) . '</td>';
                        } elseif ($report_mode === 'perbandingan_4') {
                            echo '<td class="nc-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-amount">' . $fmt($val2 ?? 0) . '</td>';
                            echo '<td class="nc-amount">' . $fmt($val3 ?? 0) . '</td>';
                            echo '<td class="nc-amount">' . $fmt($val4 ?? 0) . '</td>';
                        }
                    };

                    $renderSubtotalCells = function($val1, $val2 = null, $val3 = null, $val4 = null) use ($report_mode, $fmt) {
                        if ($report_mode === 'standar') {
                            echo '<td class="nc-subtotal-amount">' . $fmt($val1) . '</td>';
                        } elseif ($report_mode === 'anggaran') {
                            echo '<td class="nc-subtotal-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-subtotal-amount">' . $fmt($val2 ?? 0) . '</td>';
                        } elseif ($report_mode === 'perbandingan_2') {
                            echo '<td class="nc-subtotal-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-subtotal-amount">' . $fmt($val2 ?? 0) . '</td>';
                        } elseif ($report_mode === 'perbandingan_4') {
                            echo '<td class="nc-subtotal-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-subtotal-amount">' . $fmt($val2 ?? 0) . '</td>';
                            echo '<td class="nc-subtotal-amount">' . $fmt($val3 ?? 0) . '</td>';
                            echo '<td class="nc-subtotal-amount">' . $fmt($val4 ?? 0) . '</td>';
                        }
                    };

                    $renderGrandTotalCells = function($val1, $val2 = null, $val3 = null, $val4 = null) use ($report_mode, $fmt) {
                        if ($report_mode === 'standar') {
                            echo '<td class="nc-grandtotal-amount">' . $fmt($val1) . '</td>';
                        } elseif ($report_mode === 'anggaran') {
                            echo '<td class="nc-grandtotal-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-grandtotal-amount">' . $fmt($val2 ?? 0) . '</td>';
                        } elseif ($report_mode === 'perbandingan_2') {
                            echo '<td class="nc-grandtotal-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-grandtotal-amount">' . $fmt($val2 ?? 0) . '</td>';
                        } elseif ($report_mode === 'perbandingan_4') {
                            echo '<td class="nc-grandtotal-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-grandtotal-amount">' . $fmt($val2 ?? 0) . '</td>';
                            echo '<td class="nc-grandtotal-amount">' . $fmt($val3 ?? 0) . '</td>';
                            echo '<td class="nc-grandtotal-amount">' . $fmt($val4 ?? 0) . '</td>';
                        }
                    };

                    $renderFinalCells = function($val1, $val2 = null, $val3 = null, $val4 = null) use ($report_mode, $fmt) {
                        if ($report_mode === 'standar') {
                            echo '<td class="nc-final-amount">' . $fmt($val1) . '</td>';
                        } elseif ($report_mode === 'anggaran') {
                            echo '<td class="nc-final-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-final-amount">' . $fmt($val2 ?? 0) . '</td>';
                        } elseif ($report_mode === 'perbandingan_2') {
                            echo '<td class="nc-final-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-final-amount">' . $fmt($val2 ?? 0) . '</td>';
                        } elseif ($report_mode === 'perbandingan_4') {
                            echo '<td class="nc-final-amount">' . $fmt($val1) . '</td>';
                            echo '<td class="nc-final-amount">' . $fmt($val2 ?? 0) . '</td>';
                            echo '<td class="nc-final-amount">' . $fmt($val3 ?? 0) . '</td>';
                            echo '<td class="nc-final-amount">' . $fmt($val4 ?? 0) . '</td>';
                        }
                    };
                    ?>

                    <?php if (!$is12Bulan): ?>
                        <!-- ========================= HARTA ========================= -->
                        <tr>
                            <td colspan="2" class="nc-sec-main">Harta</td>
                            <?php $renderCells(null); ?>
                        </tr>
                        <?php foreach ($data['sections']['harta'] as $sKey => $sec): ?>
                            <?php 
                            $hasItems = !empty($sec['items']);
                            if (!$hasItems && empty($sec['force_total'])) continue;
                            ?>
                            <?php if ($hasItems): ?>
                                <tr>
                                    <td colspan="2" class="nc-sec-sub"><?= htmlspecialchars($sec['title']) ?></td>
                                    <?php $renderCells(null); ?>
                                </tr>
                                <?php foreach ($sec['items'] as $item): ?>
                                    <tr>
                                        <td class="nc-account-code"><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                        <td class="nc-account-name"><?= htmlspecialchars($item['nama_akun']) ?></td>
                                        <?php 
                                        if ($isCompare) {
                                            $renderCells($item['saldo_p1'], $item['budget_p1'], $item['saldo_p2'], $item['budget_p2']);
                                        } else {
                                            $renderCells($item['saldo'], $item['budget']);
                                        }
                                        ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Subtotal Section -->
                            <tr>
                                <td colspan="2" class="nc-subtotal-title"><?= htmlspecialchars($sec['total_title']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderSubtotalCells($sec['total_p1'], $sec['budget_p1'], $sec['total_p2'], $sec['budget_p2']);
                                } else {
                                    $renderSubtotalCells($sec['total'], 0.0);
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Grand Total Harta -->
                        <tr>
                            <td colspan="2" class="nc-grandtotal-title">Total Harta</td>
                            <?php 
                            if ($isCompare) {
                                $renderGrandTotalCells($data['totals_p1']['harta'], 0.0, $data['totals_p2']['harta'], 0.0);
                            } else {
                                $renderGrandTotalCells($data['totals']['harta'], 0.0);
                            }
                            ?>
                        </tr>

                        <!-- ========================= KEWAJIBAN ========================= -->
                        <tr>
                            <td colspan="2" class="nc-sec-main" style="padding-top: 24px !important;">Kewajiban</td>
                            <?php $renderCells(null); ?>
                        </tr>
                        <?php foreach ($data['sections']['kewajiban'] as $sKey => $sec): ?>
                            <?php 
                            $hasItems = !empty($sec['items']);
                            if (!$hasItems && empty($sec['force_total'])) continue;
                            ?>
                            <?php if ($hasItems): ?>
                                <tr>
                                    <td colspan="2" class="nc-sec-sub"><?= htmlspecialchars($sec['title']) ?></td>
                                    <?php $renderCells(null); ?>
                                </tr>
                                <?php foreach ($sec['items'] as $item): ?>
                                    <tr>
                                        <td class="nc-account-code"><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                        <td class="nc-account-name"><?= htmlspecialchars($item['nama_akun']) ?></td>
                                        <?php 
                                        if ($isCompare) {
                                            $renderCells($item['saldo_p1'], $item['budget_p1'], $item['saldo_p2'], $item['budget_p2']);
                                        } else {
                                            $renderCells($item['saldo'], $item['budget']);
                                        }
                                        ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Subtotal Section -->
                            <tr>
                                <td colspan="2" class="nc-subtotal-title"><?= htmlspecialchars($sec['total_title']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderSubtotalCells($sec['total_p1'], $sec['budget_p1'], $sec['total_p2'], $sec['budget_p2']);
                                } else {
                                    $renderSubtotalCells($sec['total'], 0.0);
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Grand Total Kewajiban -->
                        <tr>
                            <td colspan="2" class="nc-grandtotal-title">Total Kewajiban</td>
                            <?php 
                            if ($isCompare) {
                                $renderGrandTotalCells($data['totals_p1']['kewajiban'], 0.0, $data['totals_p2']['kewajiban'], 0.0);
                            } else {
                                $renderGrandTotalCells($data['totals']['kewajiban'], 0.0);
                            }
                            ?>
                        </tr>

                        <!-- ========================= MODAL ========================= -->
                        <tr>
                            <td colspan="2" class="nc-sec-main" style="padding-top: 24px !important;">Modal</td>
                            <?php $renderCells(null); ?>
                        </tr>
                        <?php foreach ($data['sections']['modal'] as $sKey => $sec): ?>
                            <?php $hasItems = !empty($sec['items']); ?>
                            <tr>
                                <td colspan="2" class="nc-sec-sub"><?= htmlspecialchars($sec['title']) ?></td>
                                <?php $renderCells(null); ?>
                            </tr>
                            <?php if ($hasItems): ?>
                                <?php foreach ($sec['items'] as $item): ?>
                                    <tr>
                                        <td class="nc-account-code"><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                        <td class="nc-account-name"><?= htmlspecialchars($item['nama_akun']) ?></td>
                                        <?php 
                                        if ($isCompare) {
                                            $renderCells($item['saldo_p1'], $item['budget_p1'], $item['saldo_p2'], $item['budget_p2']);
                                        } else {
                                            $renderCells($item['saldo'], $item['budget']);
                                        }
                                        ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Subtotal Section Modal/Laba -->
                            <tr>
                                <td colspan="2" class="nc-subtotal-title"><?= htmlspecialchars($sec['total_title']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $renderSubtotalCells($sec['total_p1'], $sec['budget_p1'], $sec['total_p2'], $sec['budget_p2']);
                                } else {
                                    $renderSubtotalCells($sec['total'], 0.0);
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Grand Total Modal -->
                        <tr>
                            <td colspan="2" class="nc-grandtotal-title">Total Modal</td>
                            <?php 
                            if ($isCompare) {
                                $renderGrandTotalCells($data['totals_p1']['modal'], 0.0, $data['totals_p2']['modal'], 0.0);
                            } else {
                                $renderGrandTotalCells($data['totals']['modal'], 0.0);
                            }
                            ?>
                        </tr>

                        <!-- Total Kewajiban dan Modal -->
                        <tr>
                            <td colspan="2" class="nc-final-title">Total Kewajiban dan Modal</td>
                            <?php 
                            if ($isCompare) {
                                $renderFinalCells($data['totals_p1']['kewajiban_dan_modal'], 0.0, $data['totals_p2']['kewajiban_dan_modal'], 0.0);
                            } else {
                                $renderFinalCells($data['totals']['kewajiban_dan_modal'], 0.0);
                            }
                            ?>
                        </tr>

                    <?php else: ?>
                        <!-- ========================= MODE 12 BULAN ========================= -->
                        <!-- HARTA -->
                        <tr>
                            <td colspan="2" class="nc-sec-main">Harta</td>
                            <?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?>
                        </tr>
                        <?php foreach ($data['sections']['harta'] as $sKey => $sec): ?>
                            <?php 
                            $hasItems = !empty($sec['consolidated_items']);
                            if (!$hasItems && empty($sec['force_total'])) continue;
                            ?>
                            <?php if ($hasItems): ?>
                                <tr>
                                    <td colspan="2" class="nc-sec-sub"><?= htmlspecialchars($sec['title']) ?></td>
                                    <?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?>
                                </tr>
                                <?php foreach ($sec['consolidated_items'] as $item): ?>
                                    <tr>
                                        <td class="nc-account-code"><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                        <td class="nc-account-name"><?= htmlspecialchars($item['nama_akun']) ?></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <td class="nc-amount"><?= $fmt($item['monthly'][$m] ?? 0) ?></td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Subtotal -->
                            <tr>
                                <td colspan="2" class="nc-subtotal-title"><?= htmlspecialchars($sec['total_title']) ?></td>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <td class="nc-subtotal-amount"><?= $fmt($sec['monthly_totals'][$m] ?? 0) ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Grand Total Harta -->
                        <tr>
                            <td colspan="2" class="nc-grandtotal-title">Total Harta</td>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <td class="nc-grandtotal-amount"><?= $fmt($data['monthly_totals']['harta'][$m] ?? 0) ?></td>
                            <?php endfor; ?>
                        </tr>

                        <!-- KEWAJIBAN -->
                        <tr>
                            <td colspan="2" class="nc-sec-main" style="padding-top: 24px !important;">Kewajiban</td>
                            <?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?>
                        </tr>
                        <?php foreach ($data['sections']['kewajiban'] as $sKey => $sec): ?>
                            <?php 
                            $hasItems = !empty($sec['consolidated_items']);
                            if (!$hasItems && empty($sec['force_total'])) continue;
                            ?>
                            <?php if ($hasItems): ?>
                                <tr>
                                    <td colspan="2" class="nc-sec-sub"><?= htmlspecialchars($sec['title']) ?></td>
                                    <?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?>
                                </tr>
                                <?php foreach ($sec['consolidated_items'] as $item): ?>
                                    <tr>
                                        <td class="nc-account-code"><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                        <td class="nc-account-name"><?= htmlspecialchars($item['nama_akun']) ?></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <td class="nc-amount"><?= $fmt($item['monthly'][$m] ?? 0) ?></td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Subtotal -->
                            <tr>
                                <td colspan="2" class="nc-subtotal-title"><?= htmlspecialchars($sec['total_title']) ?></td>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <td class="nc-subtotal-amount"><?= $fmt($sec['monthly_totals'][$m] ?? 0) ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Grand Total Kewajiban -->
                        <tr>
                            <td colspan="2" class="nc-grandtotal-title">Total Kewajiban</td>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <td class="nc-grandtotal-amount"><?= $fmt($data['monthly_totals']['kewajiban'][$m] ?? 0) ?></td>
                            <?php endfor; ?>
                        </tr>

                        <!-- MODAL -->
                        <tr>
                            <td colspan="2" class="nc-sec-main" style="padding-top: 24px !important;">Modal</td>
                            <?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?>
                        </tr>
                        <?php foreach ($data['sections']['modal'] as $sKey => $sec): ?>
                            <?php $hasItems = !empty($sec['consolidated_items']); ?>
                            <tr>
                                <td colspan="2" class="nc-sec-sub"><?= htmlspecialchars($sec['title']) ?></td>
                                <?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?>
                            </tr>
                            <?php if ($hasItems): ?>
                                <?php foreach ($sec['consolidated_items'] as $item): ?>
                                    <tr>
                                        <td class="nc-account-code"><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                        <td class="nc-account-name"><?= htmlspecialchars($item['nama_akun']) ?></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <td class="nc-amount"><?= $fmt($item['monthly'][$m] ?? 0) ?></td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Subtotal Modal/Laba -->
                            <tr>
                                <td colspan="2" class="nc-subtotal-title"><?= htmlspecialchars($sec['total_title']) ?></td>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <td class="nc-subtotal-amount"><?= $fmt($sec['monthly_totals'][$m] ?? 0) ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Grand Total Modal -->
                        <tr>
                            <td colspan="2" class="nc-grandtotal-title">Total Modal</td>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <td class="nc-grandtotal-amount"><?= $fmt($data['monthly_totals']['modal'][$m] ?? 0) ?></td>
                            <?php endfor; ?>
                        </tr>

                        <!-- Total Kewajiban dan Modal -->
                        <tr>
                            <td colspan="2" class="nc-final-title">Total Kewajiban dan Modal</td>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <td class="nc-final-amount"><?= $fmt($data['monthly_totals']['kewajiban_dan_modal'][$m] ?? 0) ?></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="nc-footer">
                <span>Halaman : 1</span>
                <span>Dicetak: <?= $print_time ?></span>
            </div>
        </div>
    </div>
</div>

<?php 
// Include modal filter Zahir "Pilih Periode Akuntansi"
include APPPATH . 'views/content/keuangan/laporan/laporan_keuangan/_modal_filter_periode.php'; 
?>
</body>
</html>

