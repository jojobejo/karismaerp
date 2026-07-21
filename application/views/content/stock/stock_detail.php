<?php
$item = $detail['item'] ?? [];
$perGudang = $detail['per_gudang'] ?? [];
$lotRows = $detail['lot_rows'] ?? [];
$movementRows = $detail['movement_summary'] ?? [];
$ledgerRows = $detail['ledger_history'] ?? [];
$adjustmentRows = array_merge($detail['adjustment_rows'] ?? [], $detail['adjustment_out_rows'] ?? []);
$reservationRows = $detail['reservation_rows'] ?? [];
$reconciliationRows = $detail['reconciliation_rows'] ?? [];

$e = function($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
};
$n = function($value) {
    return number_format((float)($value ?? 0), 0, ',', '.');
};
$badgeClass = function($tipe) {
    $tipe = strtoupper((string)$tipe);
    if (in_array($tipe, ['IN', 'SALDO_AWAL', 'ADJIN', 'RJUAL'], true)) return 'success';
    if (in_array($tipe, ['OUT', 'ADJOUT', 'RBELI'], true)) return 'danger';
    if (in_array($tipe, ['RESERVE', 'RELEASE'], true)) return 'warning';
    return 'secondary';
};
?>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <style>
        .stock-detail .small-box{box-shadow:none;border:1px solid #e5e7eb}
        .stock-detail .table td,.stock-detail .table th{vertical-align:middle}
        .stock-detail .module-card{height:100%;border:1px solid #e5e7eb;box-shadow:none}
        .stock-detail .module-title{font-weight:800;margin-bottom:2px}
        .stock-detail .module-subtitle{font-size:12px;color:#6b7280}
        .stock-detail .stock-code{font-weight:800;color:#111827}
    </style>

    <div class="content-wrapper stock-detail">
        <section class="content pt-3">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                    <a href="<?= base_url('stock') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <div class="text-muted small">
                        Sumber data: tberp_stock_ledger<?= $gudang_id !== '' ? ' | Gudang ' . $e($gudang_id) : '' ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-lg-7">
                                <div class="stock-code"><?= $e($kd_barang) ?></div>
                                <h3 class="mb-1 font-weight-bold"><?= $e($item['nama_barang'] ?? '-') ?></h3>
                                <div class="text-muted">
                                    Satuan <?= $e($item['satuan'] ?? '-') ?> |
                                    Isi box <?= $n($item['isi_per_box'] ?? 1) ?> pcs |
                                    Batch/Lot <?= $n($item['total_batch'] ?? count($lotRows)) ?>
                                </div>
                            </div>
                            <div class="col-lg-5 mt-3 mt-lg-0">
                                <div class="row">
                                    <div class="col-4">
                                        <div class="small-box bg-primary mb-0">
                                            <div class="inner">
                                                <h4><?= $n($item['qty'] ?? 0) ?></h4>
                                                <p>Qty</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small-box bg-success mb-0">
                                            <div class="inner">
                                                <h4><?= $n($item['qty_box'] ?? 0) ?></h4>
                                                <p>Qty Box</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small-box bg-info mb-0">
                                            <div class="inner">
                                                <h4><?= $n($item['qty_pcs'] ?? 0) ?></h4>
                                                <p>Qty Pcs</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card module-card">
                            <div class="card-body">
                                <div class="module-title"><i class="fas fa-history text-primary mr-1"></i> Histori Transaksi</div>
                                <div class="module-subtitle">Ledger masuk, keluar, retur, dan mutasi.</div>
                                <h4 class="mt-3 mb-0"><?= $n(count($ledgerRows)) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card module-card">
                            <div class="card-body">
                                <div class="module-title"><i class="fas fa-sliders-h text-warning mr-1"></i> Adjustment</div>
                                <div class="module-subtitle">ADJIN dan ADJOUT dari ledger.</div>
                                <h4 class="mt-3 mb-0"><?= $n(count($adjustmentRows)) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card module-card">
                            <div class="card-body">
                                <div class="module-title"><i class="fas fa-lock text-info mr-1"></i> Reservation</div>
                                <div class="module-subtitle">RESERVE dan RELEASE.</div>
                                <h4 class="mt-3 mb-0"><?= $n(count($reservationRows)) ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card module-card">
                            <div class="card-body">
                                <div class="module-title"><i class="fas fa-balance-scale text-danger mr-1"></i> Rekonsiliasi</div>
                                <div class="module-subtitle">Selisih batch vs ledger.</div>
                                <h4 class="mt-3 mb-0"><?= $n(count($reconciliationRows)) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="mb-0 font-weight-bold">Saldo Per Gudang</h5></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Gudang</th>
                                    <th>Tipe</th>
                                    <th class="text-right">SKU</th>
                                    <th class="text-right">Lot</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Reserved</th>
                                    <th class="text-right">Expired</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!$perGudang): ?>
                                <tr><td colspan="7" class="text-center text-muted">Tidak ada data.</td></tr>
                            <?php else: foreach ($perGudang as $row): ?>
                                <tr>
                                    <td><?= $e($row['nama_gudang'] ?? '-') ?></td>
                                    <td><?= $e($row['tipe_gudang'] ?? '-') ?></td>
                                    <td class="text-right"><?= $n($row['total_sku'] ?? 0) ?></td>
                                    <td class="text-right"><?= $n($row['total_batch'] ?? 0) ?></td>
                                    <td class="text-right font-weight-bold"><?= $n($row['qty_on_hand'] ?? 0) ?></td>
                                    <td class="text-right"><?= $n($row['qty_reserved'] ?? 0) ?></td>
                                    <td class="text-right"><?= $n($row['expired_batch'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="mb-0 font-weight-bold">Lot dan Expired</h5></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Gudang</th>
                                    <th>No Lot</th>
                                    <th>Expired</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Qty Box</th>
                                    <th class="text-right">Qty Pcs</th>
                                    <th>Ledger Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!$lotRows): ?>
                                <tr><td colspan="7" class="text-center text-muted">Tidak ada data.</td></tr>
                            <?php else: foreach ($lotRows as $row): ?>
                                <tr>
                                    <td><?= $e($row['nama_gudang'] ?? $row['gudang_id'] ?? '-') ?></td>
                                    <td><?= $e($row['no_lot'] ?? '-') ?></td>
                                    <td><?= $e($row['expired_date'] ?? '-') ?></td>
                                    <td class="text-right font-weight-bold"><?= $n($row['qty'] ?? 0) ?></td>
                                    <td class="text-right"><?= $n($row['qty_box'] ?? 0) ?></td>
                                    <td class="text-right"><?= $n($row['qty_pcs'] ?? 0) ?></td>
                                    <td><small><?= $e($row['last_ledger_at'] ?? '-') ?></small></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="mb-0 font-weight-bold">Ringkasan Movement</h5></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Tipe</th>
                                    <th class="text-right">Transaksi</th>
                                    <th class="text-right">Total Qty</th>
                                    <th class="text-right">Dampak Fisik</th>
                                    <th class="text-right">Dampak Reserved</th>
                                    <th>Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!$movementRows): ?>
                                <tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr>
                            <?php else: foreach ($movementRows as $row): ?>
                                <tr>
                                    <td><span class="badge badge-<?= $badgeClass($row['tipe'] ?? '') ?>"><?= $e($row['tipe'] ?? '-') ?></span></td>
                                    <td class="text-right"><?= $n($row['total_transaksi'] ?? 0) ?></td>
                                    <td class="text-right"><?= $n($row['total_qty'] ?? 0) ?></td>
                                    <td class="text-right"><?= $n($row['signed_physical_qty'] ?? 0) ?></td>
                                    <td class="text-right"><?= $n($row['signed_reserved_qty'] ?? 0) ?></td>
                                    <td><small><?= $e($row['last_at'] ?? '-') ?></small></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="mb-0 font-weight-bold">Histori Stock Transaksi</h5></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Tipe</th>
                                    <th>Gudang</th>
                                    <th>No Lot</th>
                                    <th>Expired</th>
                                    <th class="text-right">Qty</th>
                                    <th>Ref</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!$ledgerRows): ?>
                                <tr><td colspan="7" class="text-center text-muted">Tidak ada data.</td></tr>
                            <?php else: foreach ($ledgerRows as $row): ?>
                                <tr>
                                    <td><small><?= $e($row['created_at'] ?? '-') ?></small></td>
                                    <td><span class="badge badge-<?= $badgeClass($row['tipe'] ?? '') ?>"><?= $e($row['tipe'] ?? '-') ?></span></td>
                                    <td><?= $e($row['gudang_id'] ?? '-') ?></td>
                                    <td><?= $e($row['no_lot'] ?? '-') ?></td>
                                    <td><?= $e($row['expired_date'] ?? '-') ?></td>
                                    <td class="text-right font-weight-bold"><?= $n($row['qty'] ?? 0) ?></td>
                                    <td><small><?= $e(($row['ref_type'] ?? '-') . ' / ' . ($row['ref_no'] ?? '-')) ?></small></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0 font-weight-bold">Adjustment</h5></div>
                            <div class="card-body table-responsive">
                                <table class="table table-sm table-bordered table-striped mb-0">
                                    <thead class="thead-dark">
                                        <tr><th>Waktu</th><th>Tipe</th><th>Gudang</th><th class="text-right">Qty</th><th>Ref</th></tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!$adjustmentRows): ?>
                                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data.</td></tr>
                                    <?php else: foreach ($adjustmentRows as $row): ?>
                                        <tr>
                                            <td><small><?= $e($row['created_at'] ?? '-') ?></small></td>
                                            <td><span class="badge badge-<?= $badgeClass($row['tipe'] ?? '') ?>"><?= $e($row['tipe'] ?? '-') ?></span></td>
                                            <td><?= $e($row['gudang_id'] ?? '-') ?></td>
                                            <td class="text-right"><?= $n($row['qty'] ?? 0) ?></td>
                                            <td><small><?= $e(($row['ref_type'] ?? '-') . ' / ' . ($row['ref_no'] ?? '-')) ?></small></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0 font-weight-bold">Reservation dan Release</h5></div>
                            <div class="card-body table-responsive">
                                <table class="table table-sm table-bordered table-striped mb-0">
                                    <thead class="thead-dark">
                                        <tr><th>Waktu</th><th>Tipe</th><th>Gudang</th><th class="text-right">Qty</th><th>Ref</th></tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!$reservationRows): ?>
                                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data.</td></tr>
                                    <?php else: foreach ($reservationRows as $row): ?>
                                        <tr>
                                            <td><small><?= $e($row['created_at'] ?? '-') ?></small></td>
                                            <td><span class="badge badge-<?= $badgeClass($row['tipe'] ?? '') ?>"><?= $e($row['tipe'] ?? '-') ?></span></td>
                                            <td><?= $e($row['gudang_id'] ?? '-') ?></td>
                                            <td class="text-right"><?= $n($row['qty'] ?? 0) ?></td>
                                            <td><small><?= $e(($row['ref_type'] ?? '-') . ' / ' . ($row['ref_no'] ?? '-')) ?></small></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="mb-0 font-weight-bold">Rekonsiliasi Batch vs Ledger</h5></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Gudang</th>
                                    <th>No Lot</th>
                                    <th>Expired</th>
                                    <th class="text-right">Batch OH</th>
                                    <th class="text-right">Ledger OH</th>
                                    <th class="text-right">Diff OH</th>
                                    <th class="text-right">Diff Reserved</th>
                                    <th>Ledger Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!$reconciliationRows): ?>
                                <tr><td colspan="8" class="text-center text-muted">Tidak ada selisih.</td></tr>
                            <?php else: foreach ($reconciliationRows as $row): ?>
                                <tr>
                                    <td><?= $e($row['gudang_id'] ?? '-') ?></td>
                                    <td><?= $e($row['no_lot'] ?? '-') ?></td>
                                    <td><?= $e($row['expired_date'] ?? '-') ?></td>
                                    <td class="text-right"><?= $n($row['batch_qty_on_hand'] ?? 0) ?></td>
                                    <td class="text-right"><?= $n($row['ledger_qty_on_hand'] ?? 0) ?></td>
                                    <td class="text-right font-weight-bold text-danger"><?= $n($row['diff_on_hand'] ?? 0) ?></td>
                                    <td class="text-right"><?= $n($row['diff_reserved'] ?? 0) ?></td>
                                    <td><small><?= $e($row['last_ledger_at'] ?? '-') ?></small></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
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
