<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <?php
    $so_e = function ($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    };
    $summary = $summary ?? [];
    $modules = $modules ?? [];
    $teams = $teams ?? [];
    $exceptions = $exceptions ?? [];
    $timeline = $timeline ?? [];
    ?>

    <div class="content-wrapper opname-monitoring">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-7">
                        <h1 class="m-0">Opname Monitoring</h1>
                    </div>
                    <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('admin/stockopname') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Stockopname
                        </a>
                        <a href="<?= base_url('admin/stockopname/input') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-mobile-alt"></i> Input Opname
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <style>
                    .opname-monitoring{background:#f5f7fb}
                    .om-panel{background:#fff;border:1px solid #e1e7ef;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06)}
                    .om-panel-header{padding:14px 16px;border-bottom:1px solid #e8edf3;display:flex;align-items:center;justify-content:space-between;gap:10px}
                    .om-title{font-weight:800;color:#1f2937;margin:0;font-size:16px}
                    .om-muted{color:#64748b;font-size:12px}
                    .om-stat{background:#fff;border:1px solid #e1e7ef;border-radius:8px;padding:16px;min-height:104px}
                    .om-stat-label{font-size:12px;text-transform:uppercase;color:#64748b;font-weight:800}
                    .om-stat-value{font-size:25px;line-height:1.1;font-weight:850;color:#111827;margin-top:8px}
                    .om-stat.blue{border-left:4px solid #2563eb}.om-stat.green{border-left:4px solid #16a34a}.om-stat.orange{border-left:4px solid #f59e0b}.om-stat.red{border-left:4px solid #dc2626}
                    .om-module-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
                    .om-module{background:#fff;border:1px solid #e1e7ef;border-radius:8px;padding:14px;min-height:148px}
                    .om-module-head{display:flex;align-items:flex-start;gap:10px}
                    .om-icon{width:36px;height:36px;border-radius:8px;background:#eef6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;flex:0 0 36px}
                    .om-module-name{font-size:14px;font-weight:800;color:#1f2937;margin:0}
                    .om-module-note{font-size:12px;color:#64748b;margin:8px 0 0}
                    .om-badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 9px;font-size:12px;font-weight:800;background:#eef2ff;color:#3730a3}
                    .om-badge.review{background:#ffedd5;color:#9a3412}.om-badge.ready{background:#dcfce7;color:#166534}.om-badge.waiting{background:#fee2e2;color:#991b1b}
                    .om-action-row{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}
                    .om-action{border:1px solid #d9e2ec;border-radius:8px;background:#fff;padding:12px;text-align:left;color:#1f2937;width:100%}
                    .om-action i{color:#2563eb;margin-right:7px}.om-action span{font-weight:800;font-size:13px}
                    .progress{height:8px;border-radius:99px}.table td,.table th{vertical-align:middle}.btn i{margin-right:5px}
                    @media(max-width:1200px){.om-module-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.om-action-row{grid-template-columns:repeat(3,minmax(0,1fr))}}
                    @media(max-width:768px){.om-module-grid,.om-action-row{grid-template-columns:1fr}.content-header h1{font-size:22px}.om-panel-header{align-items:flex-start;flex-direction:column}}
                </style>

                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="om-stat blue">
                            <div class="om-stat-label">Sesi Aktif</div>
                            <div class="om-stat-value"><?= $so_e($summary['session'] ?? '-') ?></div>
                            <div class="om-muted mt-2"><?= $so_e($summary['warehouse'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="om-stat green">
                            <div class="om-stat-label">Progress</div>
                            <div class="om-stat-value"><?= (int)($summary['progress'] ?? 0) ?>%</div>
                            <div class="progress mt-2"><div class="progress-bar bg-success" style="width:<?= (int)($summary['progress'] ?? 0) ?>%"></div></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="om-stat orange">
                            <div class="om-stat-label">Selisih Review</div>
                            <div class="om-stat-value"><?= number_format((int)($summary['variance_item'] ?? 0), 0, ',', '.') ?></div>
                            <div class="om-muted mt-2">Item butuh validasi</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="om-stat red">
                            <div class="om-stat-label">Approval Pending</div>
                            <div class="om-stat-value"><?= number_format((int)($summary['pending_approval'] ?? 0), 0, ',', '.') ?></div>
                            <div class="om-muted mt-2">Koreksi menunggu approval</div>
                        </div>
                    </div>
                </div>

                <div class="om-action-row mb-3">
                    <button class="om-action" type="button"><i class="fas fa-lock"></i><span>Lock Cutoff</span></button>
                    <button class="om-action" type="button"><i class="fas fa-user-plus"></i><span>Assign Tim</span></button>
                    <button class="om-action" type="button"><i class="fas fa-sync-alt"></i><span>Sync Scan</span></button>
                    <button class="om-action" type="button"><i class="fas fa-file-export"></i><span>Export Result</span></button>
                    <button class="om-action" type="button"><i class="fas fa-check-double"></i><span>Finalisasi</span></button>
                </div>

                <div class="mb-3">
                    <div class="om-module-grid">
                        <?php foreach ($modules as $module) : ?>
                            <?php
                            $statusClass = strtolower(str_replace(' ', '-', (string)($module['status'] ?? '')));
                            if (strpos($statusClass, 'review') !== false) {
                                $statusClass = 'review';
                            } elseif (strpos($statusClass, 'waiting') !== false) {
                                $statusClass = 'waiting';
                            } else {
                                $statusClass = 'ready';
                            }
                            ?>
                            <div class="om-module">
                                <div class="om-module-head">
                                    <div class="om-icon"><i class="fas <?= $so_e($module['icon'] ?? 'fa-cube') ?>"></i></div>
                                    <div>
                                        <h2 class="om-module-name"><?= $so_e($module['name'] ?? '-') ?></h2>
                                        <div class="om-muted"><?= $so_e($module['owner'] ?? '-') ?> | <?= $so_e($module['metric'] ?? '-') ?></div>
                                    </div>
                                </div>
                                <p class="om-module-note"><?= $so_e($module['note'] ?? '-') ?></p>
                                <div class="mt-3"><span class="om-badge <?= $so_e($statusClass) ?>"><?= $so_e($module['status'] ?? '-') ?></span></div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7 mb-3">
                        <div class="om-panel h-100">
                            <div class="om-panel-header">
                                <h2 class="om-title">Team Progress</h2>
                                <span class="om-muted">Dummy monitoring area</span>
                            </div>
                            <div class="table-responsive p-3">
                                <table class="table table-sm table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tim</th>
                                            <th>Area</th>
                                            <th>Inputer</th>
                                            <th>Progress</th>
                                            <th>Sync</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($teams as $team) : ?>
                                            <tr>
                                                <td class="font-weight-bold"><?= $so_e($team['team'] ?? '-') ?></td>
                                                <td><?= $so_e($team['area'] ?? '-') ?></td>
                                                <td><?= $so_e($team['inputer'] ?? '-') ?></td>
                                                <td style="min-width:150px">
                                                    <div class="d-flex justify-content-between"><span><?= (int)($team['progress'] ?? 0) ?>%</span></div>
                                                    <div class="progress"><div class="progress-bar bg-primary" style="width:<?= (int)($team['progress'] ?? 0) ?>%"></div></div>
                                                </td>
                                                <td><?= $so_e($team['last_sync'] ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 mb-3">
                        <div class="om-panel h-100">
                            <div class="om-panel-header">
                                <h2 class="om-title">Activity Timeline</h2>
                                <span class="om-muted">Demo log</span>
                            </div>
                            <div class="p-3">
                                <?php foreach ($timeline as $row) : ?>
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="om-badge ready mr-2"><?= $so_e($row['time'] ?? '-') ?></div>
                                        <div>
                                            <div class="font-weight-bold"><?= $so_e($row['event'] ?? '-') ?></div>
                                            <div class="om-muted"><?= $so_e($row['type'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="om-panel">
                            <div class="om-panel-header">
                                <h2 class="om-title">Exception Center</h2>
                                <span class="om-muted">Calon modul validasi selisih, lot, expired, dan status input</span>
                            </div>
                            <div class="table-responsive p-3">
                                <table class="table table-sm table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Barang</th>
                                            <th>Expired</th>
                                            <th>Lot</th>
                                            <th>Issue</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($exceptions as $row) : ?>
                                            <tr>
                                                <td class="font-weight-bold"><?= $so_e($row['kode'] ?? '-') ?></td>
                                                <td><?= $so_e($row['barang'] ?? '-') ?></td>
                                                <td><?= $so_e($row['exp'] ?? '-') ?></td>
                                                <td><?= $so_e($row['lot'] ?? '-') ?></td>
                                                <td><?= $so_e($row['issue'] ?? '-') ?></td>
                                                <td><span class="om-badge review"><?= $so_e($row['status'] ?? '-') ?></span></td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>
</div>
