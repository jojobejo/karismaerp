<?php
$isNeraca = ($report_type ?? '') === 'neraca';
$sections = isset($sections) && is_array($sections) ? $sections : [];
$totals = isset($totals) && is_array($totals) ? $totals : [];
$auditNotes = isset($audit_notes) && is_array($audit_notes) ? $audit_notes : [];
$schemaReady = !empty($schema_ready);
$dateFrom = isset($date_from) ? $date_from : date('Y-m-01');
$dateTo = isset($date_to) ? $date_to : date('Y-m-d');
$money = function ($value) {
    $number = (float)$value;
    $formatted = number_format(abs($number), 2, ',', '.');
    return $number < 0 ? '(' . $formatted . ')' : $formatted;
};
?>
<style>
    .jurnal-report-page .content-header { padding: 6px .5rem 0; }
    .jurnal-report-page .page-title-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
    .jurnal-report-page .page-title-left { display: flex; align-items: center; gap: 10px; }
    .jurnal-report-page .page-home-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; background: #1788b8; color: #fff; }
    .jurnal-report-page .page-title { font-size: 30px; font-weight: 700; color: #34495e; margin: 0; }
    .jurnal-report-page .report-actions { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .jurnal-report-page .btn-report-primary { background: #1788b8; border-color: #1788b8; color: #fff; font-weight: 700; }
    .jurnal-report-page .filter-panel, .jurnal-report-page .report-panel, .jurnal-report-page .audit-panel { background: #fff; border: 1px solid #d9e2ec; border-radius: 4px; overflow: hidden; margin-bottom: 14px; }
    .jurnal-report-page .panel-heading { background: #1788b8; color: #fff; padding: 12px 16px; font-weight: 700; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .jurnal-report-page .panel-body { padding: 14px 16px; }
    .jurnal-report-page .filter-grid { display: grid; grid-template-columns: repeat(2, minmax(180px, 1fr)) auto; gap: 10px; align-items: end; }
    .jurnal-report-page .filter-grid label { font-weight: 700; color: #3e4a59; margin-bottom: 4px; }
    .jurnal-report-page .summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
    .jurnal-report-page .summary-box { border: 1px solid #d9e2ec; border-left: 4px solid #1788b8; border-radius: 4px; background: #fff; padding: 12px 14px; min-height: 88px; }
    .jurnal-report-page .summary-label { color: #68778a; font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .jurnal-report-page .summary-value { color: #1f2d3d; font-size: 22px; font-weight: 800; margin-top: 6px; white-space: nowrap; }
    .jurnal-report-page .summary-box.warning { border-left-color: #f39c12; }
    .jurnal-report-page .summary-box.success { border-left-color: #28a745; }
    .jurnal-report-page .report-meta { color: #68778a; font-size: 13px; }
    .jurnal-report-page .report-table-wrap { overflow-x: auto; }
    .jurnal-report-page .report-table { width: 100%; min-width: 920px; margin-bottom: 0; }
    .jurnal-report-page .report-table th { background: #1788b8; color: #fff; border-color: #1788b8; white-space: nowrap; }
    .jurnal-report-page .report-table td { vertical-align: middle; }
    .jurnal-report-page .section-row td { background: #edf8fc; color: #1f2d3d; font-weight: 800; border-top: 2px solid #b9ddea; }
    .jurnal-report-page .total-row td { background: #f6f9fc; font-weight: 800; border-top: 1px solid #d9e2ec; }
    .jurnal-report-page .grand-row td { background: #e9f5ea; font-weight: 900; border-top: 2px solid #98d29f; }
    .jurnal-report-page .money-cell { text-align: right; white-space: nowrap; }
    .jurnal-report-page .audit-list { margin: 0; padding-left: 18px; color: #3e4a59; }
    .jurnal-report-page .audit-list li { margin-bottom: 6px; }
    .jurnal-report-page .empty-state { padding: 30px 12px; text-align: center; color: #68778a; }
    @media (max-width: 991.98px) {
        .jurnal-report-page .page-title-row { align-items: flex-start; flex-direction: column; }
        .jurnal-report-page .report-actions { justify-content: flex-start; }
        .jurnal-report-page .filter-grid, .jurnal-report-page .summary-grid { grid-template-columns: 1fr; }
        .jurnal-report-page .summary-value { font-size: 20px; }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper jurnal-report-page">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header"><div class="container-fluid"></div></div>

            <section class="content">
                <div class="container-fluid">
                    <div class="page-title-row">
                        <div class="page-title-left">
                            <a href="<?= base_url('jurnal') ?>" class="page-home-btn" title="Kembali ke Jurnal"><i class="fas fa-arrow-left"></i></a>
                            <h1 class="page-title"><?= html_escape($report_title) ?></h1>
                        </div>
                        <div class="report-actions">
                            <a href="<?= base_url('jurnal/neraca') ?>" class="btn <?= $isNeraca ? 'btn-report-primary' : 'btn-outline-primary' ?>">
                                <i class="fas fa-balance-scale mr-1"></i> Neraca
                            </a>
                            <a href="<?= base_url('jurnal/laba-rugi') ?>" class="btn <?= !$isNeraca ? 'btn-report-primary' : 'btn-outline-primary' ?>">
                                <i class="fas fa-chart-line mr-1"></i> Laba Rugi
                            </a>
                        </div>
                    </div>

                    <?php if (!$schemaReady) : ?>
                        <div class="alert alert-warning">
                            <strong>Schema accounting belum tersedia.</strong>
                            Laporan membutuhkan tabel akun, klasifikasi akun, jurnal, dan detail jurnal.
                        </div>
                    <?php endif; ?>

                    <div class="filter-panel">
                        <div class="panel-heading">
                            <span>Filter Laporan</span>
                            <span class="report-meta">
                                <?= $isNeraca ? 'Neraca per tanggal cut-off' : 'Laba rugi periode berjalan' ?>
                            </span>
                        </div>
                        <div class="panel-body">
                            <form method="get" action="<?= current_url() ?>">
                                <div class="filter-grid">
                                    <div>
                                        <label for="date_from"><?= $isNeraca ? 'Awal Laba/Rugi Berjalan' : 'Tanggal Awal' ?></label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" value="<?= html_escape($dateFrom) ?>">
                                    </div>
                                    <div>
                                        <label for="date_to"><?= $isNeraca ? 'Cut-off Neraca' : 'Tanggal Akhir' ?></label>
                                        <input type="date" class="form-control" id="date_to" name="date_to" value="<?= html_escape($dateTo) ?>">
                                    </div>
                                    <button type="submit" class="btn btn-report-primary"><i class="fas fa-search mr-1"></i> Tampilkan</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if ($isNeraca) : ?>
                        <?php $balanced = abs((float)($totals['difference'] ?? 0)) < 0.01; ?>
                        <div class="summary-grid">
                            <div class="summary-box">
                                <div class="summary-label">Total Aset</div>
                                <div class="summary-value"><?= $money($totals['asset'] ?? 0) ?></div>
                            </div>
                            <div class="summary-box">
                                <div class="summary-label">Total Kewajiban</div>
                                <div class="summary-value"><?= $money($totals['liability'] ?? 0) ?></div>
                            </div>
                            <div class="summary-box">
                                <div class="summary-label">Total Ekuitas</div>
                                <div class="summary-value"><?= $money($totals['equity'] ?? 0) ?></div>
                            </div>
                            <div class="summary-box <?= $balanced ? 'success' : 'warning' ?>">
                                <div class="summary-label">Selisih Neraca</div>
                                <div class="summary-value"><?= $money($totals['difference'] ?? 0) ?></div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="summary-grid">
                            <div class="summary-box">
                                <div class="summary-label">Total Pendapatan</div>
                                <div class="summary-value"><?= $money($totals['total_revenue'] ?? 0) ?></div>
                            </div>
                            <div class="summary-box">
                                <div class="summary-label">Laba Kotor</div>
                                <div class="summary-value"><?= $money($totals['gross_profit'] ?? 0) ?></div>
                            </div>
                            <div class="summary-box">
                                <div class="summary-label">Laba Operasional</div>
                                <div class="summary-value"><?= $money($totals['operating_profit'] ?? 0) ?></div>
                            </div>
                            <div class="summary-box success">
                                <div class="summary-label">Laba Bersih</div>
                                <div class="summary-value"><?= $money($totals['net_income'] ?? 0) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="report-panel">
                        <div class="panel-heading">
                            <span>Rincian Akun</span>
                            <span class="report-meta">
                                <?= $isNeraca ? 'Sampai ' . html_escape($dateTo) : html_escape($dateFrom) . ' s/d ' . html_escape($dateTo) ?>
                            </span>
                        </div>
                        <div class="report-table-wrap">
                            <table class="table table-bordered table-striped report-table">
                                <thead>
                                    <tr>
                                        <th style="width: 130px;">Kode Akun</th>
                                        <th>Akun / Klasifikasi</th>
                                        <th style="width: 130px;">Saldo Normal</th>
                                        <th class="text-right" style="width: 150px;">Debit</th>
                                        <th class="text-right" style="width: 150px;">Kredit</th>
                                        <th class="text-right" style="width: 170px;">Nilai Laporan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sections)) : ?>
                                        <tr><td colspan="6" class="empty-state">Belum ada jurnal POSTED untuk laporan ini.</td></tr>
                                    <?php endif; ?>

                                    <?php foreach ($sections as $section) : ?>
                                        <tr class="section-row">
                                            <td><?= html_escape($section['alias'] ?: '-') ?></td>
                                            <td colspan="2"><?= html_escape($section['name']) ?></td>
                                            <td class="money-cell"><?= $money($section['debit'] ?? 0) ?></td>
                                            <td class="money-cell"><?= $money($section['kredit'] ?? 0) ?></td>
                                            <td class="money-cell"><?= $money($section['total'] ?? 0) ?></td>
                                        </tr>

                                        <?php foreach ($section['rows'] as $row) : ?>
                                            <tr>
                                                <td><?= html_escape($row['kode_akun'] ?: '-') ?></td>
                                                <td><?= html_escape($row['nama_akun']) ?></td>
                                                <td><?= html_escape($row['saldo_normal'] ?: '-') ?></td>
                                                <td class="money-cell"><?= $money($row['debit'] ?? 0) ?></td>
                                                <td class="money-cell"><?= $money($row['kredit'] ?? 0) ?></td>
                                                <td class="money-cell"><?= $money($row['amount'] ?? 0) ?></td>
                                            </tr>
                                        <?php endforeach; ?>

                                        <?php 
                                        $isHarta = false;
                                        if ($isNeraca) {
                                            $secName = strtolower($section['name'] ?? '');
                                            $secCode = (string)($section['code'] ?? '');
                                            $secCodePrefix = substr($secCode, 0, 1);
                                            if ($secCodePrefix === '1' || strpos($secName, 'harta') !== false || strpos($secName, 'asset') !== false) {
                                                $isHarta = true;
                                            }
                                        }
                                        $totalRowClass = $isHarta ? 'grand-row' : 'total-row';
                                        ?>
                                        <tr class="<?= $totalRowClass ?>">
                                            <td colspan="5">Total <?= html_escape($section['name']) ?></td>
                                            <td class="money-cell"><?= $money($section['total'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php if ($isNeraca && !empty($sections)) : ?>
                                        <tr class="grand-row">
                                            <td colspan="5">Total Kewajiban + Ekuitas</td>
                                            <td class="money-cell"><?= $money($totals['liability_equity'] ?? 0) ?></td>
                                        </tr>
                                    <?php elseif (!$isNeraca && !empty($sections)) : ?>
                                        <tr class="grand-row">
                                            <td colspan="5">Laba Bersih</td>
                                            <td class="money-cell"><?= $money($totals['net_income'] ?? 0) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="audit-panel">
                        <div class="panel-heading">
                            <span>Catatan Audit</span>
                            <span class="report-meta">Sumber: tbkeu_jurnal POSTED</span>
                        </div>
                        <div class="panel-body">
                            <ul class="audit-list">
                                <?php foreach ($auditNotes as $note) : ?>
                                    <li><?= html_escape($note) ?></li>
                                <?php endforeach; ?>
                                <li>Alur data: jurnal umum, detail jurnal, akun, lalu klasifikasi laporan.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
        </footer>
        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>
</body>
