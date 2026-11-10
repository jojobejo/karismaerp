<?php
/**
 * neraca_excel.php
 * Template export Excel untuk semua jenis Laporan Neraca & Neraca Saldo.
 */
$isCompare = in_array($report_mode, ['perbandingan_2', 'perbandingan_4'], true);
$isSaldo   = ($report_mode === 'saldo');
$is12Bulan = ($report_mode === '12_bulan');

$fmt = function($v) {
    return number_format((float)$v, 2, '.', ',');
};
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($report_title) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; }
        .title-company { font-size: 14pt; font-weight: bold; text-align: center; text-transform: uppercase; }
        .title-report { font-size: 16pt; font-weight: bold; text-align: center; color: #008cba; }
        .title-period { font-size: 11pt; font-weight: bold; text-align: center; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th { background-color: #f1f5f9; border: 1px solid #94a3b8; padding: 6px; font-weight: bold; }
        td { padding: 4px 6px; }
        .num { text-align: right; mso-number-format: "\#\,\#\#0\.00"; }
        .sec-main { font-weight: bold; font-size: 12pt; color: #0284c7; }
        .sec-sub { font-weight: bold; color: #0284c7; padding-left: 15px; }
        .subtotal { font-weight: bold; color: #0284c7; border-top: 1px solid #94a3b8; }
        .grandtotal { font-weight: bold; color: #0284c7; border-top: 2px solid #0284c7; border-bottom: 2px solid #0284c7; }
        .finaltotal { font-weight: bold; color: #0284c7; border-top: 2px solid #0284c7; border-bottom: 3px double #0284c7; }
    </style>
</head>
<body>
    <div class="title-company"><?= htmlspecialchars($company_name) ?></div>
    <div class="title-report"><?= htmlspecialchars($report_title) ?></div>
    <div class="title-period"><?= htmlspecialchars($period_label ?? '') ?></div>

    <?php if ($isSaldo): ?>
        <!-- NERACA SALDO EXCEL -->
        <table border="1">
            <thead>
                <tr>
                    <th rowspan="2" colspan="2">Kode Rekening</th>
                    <th colspan="2">Saldo Awal</th>
                    <th colspan="2">Transaksi Bulan Ini</th>
                    <th colspan="2">Saldo Akhir</th>
                </tr>
                <tr>
                    <th>Debet</th>
                    <th>Kredit</th>
                    <th>Debet</th>
                    <th>Kredit</th>
                    <th>Debet</th>
                    <th>Kredit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['items'] as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['kode_tampil']) ?></td>
                        <td><?= htmlspecialchars($it['nama_akun']) ?></td>
                        <td class="num"><?= $it['awal_debet'] ? $fmt($it['awal_debet']) : '' ?></td>
                        <td class="num"><?= $it['awal_kredit'] ? $fmt($it['awal_kredit']) : '' ?></td>
                        <td class="num"><?= $it['mutasi_debet'] ? $fmt($it['mutasi_debet']) : '' ?></td>
                        <td class="num"><?= $it['mutasi_kredit'] ? $fmt($it['mutasi_kredit']) : '' ?></td>
                        <td class="num"><?= $it['akhir_debet'] ? $fmt($it['akhir_debet']) : '' ?></td>
                        <td class="num"><?= $it['akhir_kredit'] ? $fmt($it['akhir_kredit']) : '' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="grandtotal">
                    <td colspan="2">Total:</td>
                    <td class="num"><?= $fmt($data['grand_total']['awal_debet']) ?></td>
                    <td class="num"><?= $fmt($data['grand_total']['awal_kredit']) ?></td>
                    <td class="num"><?= $fmt($data['grand_total']['mutasi_debet']) ?></td>
                    <td class="num"><?= $fmt($data['grand_total']['mutasi_kredit']) ?></td>
                    <td class="num"><?= $fmt($data['grand_total']['akhir_debet']) ?></td>
                    <td class="num"><?= $fmt($data['grand_total']['akhir_kredit']) ?></td>
                </tr>
            </tfoot>
        </table>

    <?php elseif ($is12Bulan): ?>
        <!-- NERACA 12 BULAN EXCEL -->
        <table border="1">
            <thead>
                <tr>
                    <th colspan="2">Nama Rekening</th>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <th><?= $m ?> - <?= $year ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <!-- HARTA -->
                <tr><td colspan="2" class="sec-main">Harta</td><?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?></tr>
                <?php foreach ($data['sections']['harta'] as $sKey => $sec): ?>
                    <?php if (!empty($sec['consolidated_items'])): ?>
                        <tr><td colspan="2" class="sec-sub"><?= htmlspecialchars($sec['title']) ?></td><?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?></tr>
                        <?php foreach ($sec['consolidated_items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                <td><?= htmlspecialchars($item['nama_akun']) ?></td>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <td class="num"><?= $fmt($item['monthly'][$m] ?? 0) ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr class="subtotal">
                        <td colspan="2"><?= htmlspecialchars($sec['total_title']) ?></td>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <td class="num"><?= $fmt($sec['monthly_totals'][$m] ?? 0) ?></td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
                <tr class="grandtotal">
                    <td colspan="2">Total Harta</td>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <td class="num"><?= $fmt($data['monthly_totals']['harta'][$m] ?? 0) ?></td>
                    <?php endfor; ?>
                </tr>

                <!-- KEWAJIBAN -->
                <tr><td colspan="2" class="sec-main">Kewajiban</td><?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?></tr>
                <?php foreach ($data['sections']['kewajiban'] as $sKey => $sec): ?>
                    <?php if (!empty($sec['consolidated_items'])): ?>
                        <tr><td colspan="2" class="sec-sub"><?= htmlspecialchars($sec['title']) ?></td><?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?></tr>
                        <?php foreach ($sec['consolidated_items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                <td><?= htmlspecialchars($item['nama_akun']) ?></td>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <td class="num"><?= $fmt($item['monthly'][$m] ?? 0) ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr class="subtotal">
                        <td colspan="2"><?= htmlspecialchars($sec['total_title']) ?></td>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <td class="num"><?= $fmt($sec['monthly_totals'][$m] ?? 0) ?></td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
                <tr class="grandtotal">
                    <td colspan="2">Total Kewajiban</td>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <td class="num"><?= $fmt($data['monthly_totals']['kewajiban'][$m] ?? 0) ?></td>
                    <?php endfor; ?>
                </tr>

                <!-- MODAL -->
                <tr><td colspan="2" class="sec-main">Modal</td><?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?></tr>
                <?php foreach ($data['sections']['modal'] as $sKey => $sec): ?>
                    <?php if (!empty($sec['consolidated_items'])): ?>
                        <tr><td colspan="2" class="sec-sub"><?= htmlspecialchars($sec['title']) ?></td><?php for ($m = 1; $m <= 12; $m++): ?><td></td><?php endfor; ?></tr>
                        <?php foreach ($sec['consolidated_items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                <td><?= htmlspecialchars($item['nama_akun']) ?></td>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <td class="num"><?= $fmt($item['monthly'][$m] ?? 0) ?></td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr class="subtotal">
                        <td colspan="2"><?= htmlspecialchars($sec['total_title']) ?></td>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <td class="num"><?= $fmt($sec['monthly_totals'][$m] ?? 0) ?></td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
                <tr class="grandtotal">
                    <td colspan="2">Total Modal</td>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <td class="num"><?= $fmt($data['monthly_totals']['modal'][$m] ?? 0) ?></td>
                    <?php endfor; ?>
                </tr>
                <tr class="finaltotal">
                    <td colspan="2">Total Kewajiban dan Modal</td>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <td class="num"><?= $fmt($data['monthly_totals']['kewajiban_dan_modal'][$m] ?? 0) ?></td>
                    <?php endfor; ?>
                </tr>
            </tbody>
        </table>

    <?php else: ?>
        <!-- NERACA STANDAR, ANGGARAN, PERBANDINGAN 2 & 4 KOLOM EXCEL -->
        <table border="1">
            <thead>
                <?php if ($report_mode === 'standar'): ?>
                    <tr>
                        <th colspan="2">Nama Rekening</th>
                        <th>IDR</th>
                    </tr>
                <?php elseif ($report_mode === 'anggaran'): ?>
                    <tr>
                        <th colspan="2">Nama Rekening</th>
                        <th>Realisasi (IDR)</th>
                        <th>Anggaran (IDR)</th>
                    </tr>
                <?php elseif ($report_mode === 'perbandingan_2'): ?>
                    <tr>
                        <th colspan="2">Nama Rekening</th>
                        <th><?= htmlspecialchars($p1_label) ?> (IDR)</th>
                        <th><?= htmlspecialchars($p2_label) ?> (IDR)</th>
                    </tr>
                <?php elseif ($report_mode === 'perbandingan_4'): ?>
                    <tr>
                        <th colspan="2">Nama Rekening</th>
                        <th colspan="2"><?= htmlspecialchars($p1_label) ?></th>
                        <th colspan="2"><?= htmlspecialchars($p2_label) ?></th>
                    </tr>
                    <tr>
                        <th colspan="2"></th>
                        <th>Realisasi (IDR)</th>
                        <th>Anggaran (IDR)</th>
                        <th>Realisasi (IDR)</th>
                        <th>Anggaran (IDR)</th>
                    </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php
                $printCells = function($v1, $v2 = 0.0, $v3 = 0.0, $v4 = 0.0, $cssClass = 'num') use ($report_mode, $fmt) {
                    if ($report_mode === 'standar') {
                        echo '<td class="' . $cssClass . '">' . $fmt($v1) . '</td>';
                    } elseif ($report_mode === 'anggaran') {
                        echo '<td class="' . $cssClass . '">' . $fmt($v1) . '</td>';
                        echo '<td class="' . $cssClass . '">' . $fmt($v2) . '</td>';
                    } elseif ($report_mode === 'perbandingan_2') {
                        echo '<td class="' . $cssClass . '">' . $fmt($v1) . '</td>';
                        echo '<td class="' . $cssClass . '">' . $fmt($v2) . '</td>';
                    } elseif ($report_mode === 'perbandingan_4') {
                        echo '<td class="' . $cssClass . '">' . $fmt($v1) . '</td>';
                        echo '<td class="' . $cssClass . '">' . $fmt($v2) . '</td>';
                        echo '<td class="' . $cssClass . '">' . $fmt($v3) . '</td>';
                        echo '<td class="' . $cssClass . '">' . $fmt($v4) . '</td>';
                    }
                };
                ?>

                <!-- HARTA -->
                <tr><td colspan="2" class="sec-main">Harta</td><?php $printCells(0,0,0,0,''); ?></tr>
                <?php foreach ($data['sections']['harta'] as $sKey => $sec): ?>
                    <?php if (!empty($sec['items'])): ?>
                        <tr><td colspan="2" class="sec-sub"><?= htmlspecialchars($sec['title']) ?></td><?php $printCells(0,0,0,0,''); ?></tr>
                        <?php foreach ($sec['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                <td><?= htmlspecialchars($item['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $printCells($item['saldo_p1'], $item['budget_p1'], $item['saldo_p2'], $item['budget_p2']);
                                } else {
                                    $printCells($item['saldo'], $item['budget']);
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr class="subtotal">
                        <td colspan="2"><?= htmlspecialchars($sec['total_title']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $printCells($sec['total_p1'], $sec['budget_p1'], $sec['total_p2'], $sec['budget_p2'], 'subtotal num');
                        } else {
                            $printCells($sec['total'], 0.0, 0.0, 0.0, 'subtotal num');
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
                <tr class="grandtotal">
                    <td colspan="2">Total Harta</td>
                    <?php 
                    if ($isCompare) {
                        $printCells($data['totals_p1']['harta'], 0.0, $data['totals_p2']['harta'], 0.0, 'grandtotal num');
                    } else {
                        $printCells($data['totals']['harta'], 0.0, 0.0, 0.0, 'grandtotal num');
                    }
                    ?>
                </tr>

                <!-- KEWAJIBAN -->
                <tr><td colspan="2" class="sec-main">Kewajiban</td><?php $printCells(0,0,0,0,''); ?></tr>
                <?php foreach ($data['sections']['kewajiban'] as $sKey => $sec): ?>
                    <?php if (!empty($sec['items'])): ?>
                        <tr><td colspan="2" class="sec-sub"><?= htmlspecialchars($sec['title']) ?></td><?php $printCells(0,0,0,0,''); ?></tr>
                        <?php foreach ($sec['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                <td><?= htmlspecialchars($item['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $printCells($item['saldo_p1'], $item['budget_p1'], $item['saldo_p2'], $item['budget_p2']);
                                } else {
                                    $printCells($item['saldo'], $item['budget']);
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr class="subtotal">
                        <td colspan="2"><?= htmlspecialchars($sec['total_title']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $printCells($sec['total_p1'], $sec['budget_p1'], $sec['total_p2'], $sec['budget_p2'], 'subtotal num');
                        } else {
                            $printCells($sec['total'], 0.0, 0.0, 0.0, 'subtotal num');
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
                <tr class="grandtotal">
                    <td colspan="2">Total Kewajiban</td>
                    <?php 
                    if ($isCompare) {
                        $printCells($data['totals_p1']['kewajiban'], 0.0, $data['totals_p2']['kewajiban'], 0.0, 'grandtotal num');
                    } else {
                        $printCells($data['totals']['kewajiban'], 0.0, 0.0, 0.0, 'grandtotal num');
                    }
                    ?>
                </tr>

                <!-- MODAL -->
                <tr><td colspan="2" class="sec-main">Modal</td><?php $printCells(0,0,0,0,''); ?></tr>
                <?php foreach ($data['sections']['modal'] as $sKey => $sec): ?>
                    <?php if (!empty($sec['items'])): ?>
                        <tr><td colspan="2" class="sec-sub"><?= htmlspecialchars($sec['title']) ?></td><?php $printCells(0,0,0,0,''); ?></tr>
                        <?php foreach ($sec['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['kode_tampil']) ?></td>
                                <td><?= htmlspecialchars($item['nama_akun']) ?></td>
                                <?php 
                                if ($isCompare) {
                                    $printCells($item['saldo_p1'], $item['budget_p1'], $item['saldo_p2'], $item['budget_p2']);
                                } else {
                                    $printCells($item['saldo'], $item['budget']);
                                }
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr class="subtotal">
                        <td colspan="2"><?= htmlspecialchars($sec['total_title']) ?></td>
                        <?php 
                        if ($isCompare) {
                            $printCells($sec['total_p1'], $sec['budget_p1'], $sec['total_p2'], $sec['budget_p2'], 'subtotal num');
                        } else {
                            $printCells($sec['total'], 0.0, 0.0, 0.0, 'subtotal num');
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
                <tr class="grandtotal">
                    <td colspan="2">Total Modal</td>
                    <?php 
                    if ($isCompare) {
                        $printCells($data['totals_p1']['modal'], 0.0, $data['totals_p2']['modal'], 0.0, 'grandtotal num');
                    } else {
                        $printCells($data['totals']['modal'], 0.0, 0.0, 0.0, 'grandtotal num');
                    }
                    ?>
                </tr>
                <tr class="finaltotal">
                    <td colspan="2">Total Kewajiban dan Modal</td>
                    <?php 
                    if ($isCompare) {
                        $printCells($data['totals_p1']['kewajiban_dan_modal'], 0.0, $data['totals_p2']['kewajiban_dan_modal'], 0.0, 'finaltotal num');
                    } else {
                        $printCells($data['totals']['kewajiban_dan_modal'], 0.0, 0.0, 0.0, 'finaltotal num');
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>

