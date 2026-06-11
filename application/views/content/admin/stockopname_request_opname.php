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
    $request_logs = $request_logs ?? [];
    ?>

    <div class="content-wrapper so-request-page">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-7">
                        <h1 class="m-0">Request Opname User</h1>
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
                    .so-request-page{background:#f5f7fb}
                    .sr-panel{background:#fff;border:1px solid #e1e7ef;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06)}
                    .sr-panel-header{padding:14px 16px;border-bottom:1px solid #e8edf3;display:flex;align-items:center;justify-content:space-between;gap:12px}
                    .sr-title{font-weight:800;color:#1f2937;margin:0;font-size:16px}
                    .sr-muted{color:#64748b;font-size:12px}
                    .sr-code{font-family:monospace;font-size:12px;background:#f8fafc;border:1px solid #dbe5ef;border-radius:6px;padding:4px 7px;color:#334155;white-space:nowrap}
                    .sr-badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 9px;font-size:12px;font-weight:800;background:#fffbeb;color:#92400e}
                    .table td,.table th{vertical-align:middle}.btn i{margin-right:5px}
                    @media(max-width:768px){.content-header h1{font-size:22px}.sr-panel-header{align-items:flex-start;flex-direction:column}}
                </style>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="sr-panel">
                            <div class="sr-panel-header">
                                <div>
                                    <h2 class="sr-title">Daftar Request Opname</h2>
                                    <div class="sr-muted"><?= number_format(count($request_logs), 0, ',', '.') ?> data request ditampilkan</div>
                                </div>
                                <span class="sr-badge">stockopname_master_manual_item</span>
                            </div>
                            <div class="table-responsive p-3">
                                <table class="table table-sm table-bordered table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Exp Date Request</th>
                                            <th>Lot Request</th>
                                            <th class="text-right">Dimensi</th>
                                            <th>Status</th>
                                            <th>Requested By</th>
                                            <th>Reviewed By</th>
                                            <th>Reviewed At</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($request_logs)) : ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted py-4">Belum ada request opname.</td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php foreach ($request_logs as $row) : ?>
                                            <tr>
                                                <td><?= $so_e($row['requested_at'] ?? $row['created_at'] ?? '-') ?></td>
                                                <td><span class="sr-code"><?= $so_e($row['kode_barang'] ?? '-') ?></span></td>
                                                <td><?= $so_e($row['nama_barang'] ?? '-') ?></td>
                                                <td><?= $so_e($row['expired_date'] ?? '-') ?></td>
                                                <td><?= $so_e($row['no_lot'] ?? '-') ?></td>
                                                <td class="text-right"><?= number_format((int)($row['dimensi'] ?? 0), 0, ',', '.') ?></td>
                                                <td><span class="sr-badge"><?= $so_e($row['status'] ?? '-') ?></span></td>
                                                <td><?= $so_e($row['requested_by'] ?? '-') ?></td>
                                                <td><?= $so_e($row['reviewed_by'] ?? '-') ?></td>
                                                <td><?= $so_e($row['reviewed_at'] ?? '-') ?></td>
                                                <td><?= $so_e($row['review_note'] ?? '-') ?></td>
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
