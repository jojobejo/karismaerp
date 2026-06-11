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
    $activity_logs = $activity_logs ?? [];
    $selected_wilayah = $selected_wilayah ?? '';
    $wilayah_options = $wilayah_options ?? [];
    ?>

    <div class="content-wrapper so-activity-page">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-7">
                        <h1 class="m-0">Log Aktifitas Stock Opname</h1>
                    </div>
                    <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('admin/stockopname/monitoring') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Monitoring
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <style>
                    .so-activity-page{background:#f5f7fb}
                    .sa-panel{background:#fff;border:1px solid #e1e7ef;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06)}
                    .sa-panel-header{padding:14px 16px;border-bottom:1px solid #e8edf3;display:flex;align-items:center;justify-content:space-between;gap:12px}
                    .sa-title{font-weight:800;color:#1f2937;margin:0;font-size:16px}
                    .sa-muted{color:#64748b;font-size:12px}
                    .sa-filter{display:grid;grid-template-columns:minmax(180px,260px) auto auto;gap:8px;align-items:center}
                    .sa-code{font-family:monospace;font-size:12px;background:#f8fafc;border:1px solid #dbe5ef;border-radius:6px;padding:4px 7px;color:#334155;white-space:nowrap}
                    .sa-badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 9px;font-size:12px;font-weight:800;background:#eef2ff;color:#3730a3}
                    .table td,.table th{vertical-align:middle}.btn i{margin-right:5px}
                    @media(max-width:768px){.content-header h1{font-size:22px}.sa-panel-header{align-items:flex-start;flex-direction:column}.sa-filter{grid-template-columns:1fr;width:100%}}
                </style>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="sa-panel">
                            <div class="sa-panel-header">
                                <div>
                                    <h2 class="sa-title">Daftar Log Aktifitas</h2>
                                    <div class="sa-muted"><?= number_format(count($activity_logs), 0, ',', '.') ?> data ditampilkan</div>
                                </div>
                                <form method="get" action="<?= base_url('admin/stockopname/monitoring/activity-log') ?>" class="sa-filter">
                                    <input list="wilayahList" type="text" name="wilayah" value="<?= $so_e($selected_wilayah) ?>" class="form-control form-control-sm" placeholder="Filter wilayah">
                                    <datalist id="wilayahList">
                                        <?php foreach ($wilayah_options as $row) : ?>
                                            <?php $wilayah = $row['wilayah'] ?? ''; ?>
                                            <?php if (trim((string)$wilayah) !== '') : ?>
                                                <option value="<?= $so_e($wilayah) ?>"></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </datalist>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="<?= base_url('admin/stockopname/monitoring/activity-log') ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                </form>
                            </div>
                            <div class="table-responsive p-3">
                                <table class="table table-sm table-bordered table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Exp Date</th>
                                            <th>Lot</th>
                                            <th class="text-right">Pcs</th>
                                            <th class="text-right">Box</th>
                                            <th class="text-right">Qty</th>
                                            <th>Input By</th>
                                            <th>Wilayah</th>
                                            <th>Tim</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($activity_logs)) : ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted py-4">Belum ada log aktifitas untuk filter ini.</td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php foreach ($activity_logs as $row) : ?>
                                            <tr>
                                                <td><?= $so_e($row['created_at'] ?? $row['input_at'] ?? '-') ?></td>
                                                <td><span class="sa-code"><?= $so_e($row['kode_barang'] ?? '-') ?></span></td>
                                                <td><?= $so_e($row['nama_barang'] ?? '-') ?></td>
                                                <td><?= $so_e($row['expired_date'] ?? '-') ?></td>
                                                <td><?= $so_e($row['no_lot'] ?? '-') ?></td>
                                                <td class="text-right"><?= number_format((int)($row['qty_pcs'] ?? 0), 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((int)($row['qty_box'] ?? 0), 0, ',', '.') ?></td>
                                                <td class="text-right font-weight-bold"><?= number_format((int)($row['qty'] ?? 0), 0, ',', '.') ?></td>
                                                <td><?= $so_e($row['input_by'] ?? '-') ?></td>
                                                <td><span class="sa-badge"><?= $so_e($row['wilayah'] ?? '-') ?></span></td>
                                                <td><?= $so_e($row['tim_opname'] ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
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
