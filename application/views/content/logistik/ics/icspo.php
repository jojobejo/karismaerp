<!-- icspo.php -->
<?php
$lastSyncTime = !empty($last_sync['sync_time']) ? $last_sync['sync_time'] : '-';
$lastSyncInserted = isset($last_sync['inserted']) ? (int) $last_sync['inserted'] : 0;
$lastSyncUpdated = isset($last_sync['updated']) ? (int) $last_sync['updated'] : 0;
$lastSyncSkipped = isset($last_sync['skipped']) ? (int) $last_sync['skipped'] : 0;
$isAdminPo = !empty($is_admin_po);
$canSyncPo = !empty($can_sync_po);
$jobdeskUpper = strtoupper(trim((string) $this->session->userdata('jobdesk')));
$lvInt = (int) $this->session->userdata('lv');
$showLpbActions = !$isAdminPo && ($lvInt >= 1) && $jobdeskUpper !== 'ADMINICS';
$showLogistikPanel = !isset($show_logistik_panel) || !empty($show_logistik_panel);
$showPurchasingPanel = !isset($show_purchasing_panel) || !empty($show_purchasing_panel);
$canLpbManual = !empty($can_lpb_manual);
$canLpbReport = !empty($can_lpb_report);
$isAdmlpbUser = !empty($is_admlpb_user);
$canLpbRevision = !empty($can_lpb_revision) && !$isAdmlpbUser;
$lpbRevisionBadgeCount = isset($lpb_revision_badge_count) ? (int) $lpb_revision_badge_count : 0;
$canViewLpbNominal = !isset($can_view_lpb_nominal) || !empty($can_view_lpb_nominal);
$isDataLpbPage = !empty($is_data_lpb_page);
$hideSupplierCode = !empty($hide_lpb_supplier_code);
$hideLastInput = !empty($hide_lpb_last_input);
$lpbTableColspan = 10 - ($hideSupplierCode ? 1 : 0) - ($hideLastInput ? 1 : 0);
$panelCount = ($showLogistikPanel ? 2 : 0) + ($showPurchasingPanel ? 1 : 0) + 1;
$showPanelTabs = $panelCount > 1;
$isManualTabActive = (!empty($active_tab) && $active_tab === 'lpb-manual');
$isPoSupplierTabActive = (!empty($active_tab) && $active_tab === 'po-supplier' && $showLogistikPanel);
$isLogistikActive = !$isManualTabActive && !$isPoSupplierTabActive && $showLogistikPanel;
$isPurchasingActive = !$isManualTabActive && !$isPoSupplierTabActive && !$showLogistikPanel && $showPurchasingPanel;

$logistikPanelClass = $showPanelTabs ? ($isLogistikActive ? 'tab-pane fade show active' : 'tab-pane fade') : '';
$poSupplierPanelClass = $showPanelTabs ? ($isPoSupplierTabActive ? 'tab-pane fade show active' : 'tab-pane fade') : '';
$purchasingPanelClass = $showPanelTabs ? ($isPurchasingActive ? 'tab-pane fade show active' : 'tab-pane fade') : '';
$manualPanelClass = $showPanelTabs ? ($isManualTabActive ? 'tab-pane fade show active' : 'tab-pane fade') : '';

$unpostedManualCount = 0;
if (!empty($lpb_manual)) {
    foreach ($lpb_manual as $mCheck) {
        $st = $mCheck['status_lpb'] ?? null;
        if ($st === null || (string) $st !== '1') {
            $unpostedManualCount++;
        }
    }
}
$panelTitle = $isDataLpbPage
    ? 'Data LPB'
    : ($showPurchasingPanel && !$showLogistikPanel
    ? 'Data LPB Purchasing'
    : ($isAdminPo ? 'Data PO Invoice Pending' : 'Data LPB (Laporan Penerimaan Barang)'));

$formatDate = function ($dateStr) {
    $dateStr = trim((string) $dateStr);
    if ($dateStr === '' || $dateStr === '-' || $dateStr === '0000-00-00' || $dateStr === '0000-00-00 00:00:00') {
        return '-';
    }
    $timestamp = strtotime($dateStr);
    return $timestamp ? date('d/m/Y', $timestamp) : $dateStr;
};
?>
<style>
    .table tbody tr.table-warning td,
    .table-hover tbody tr.table-warning:hover td {
        background-color: #fff9db !important;
    }

    .table tbody tr.table-success td,
    .table-hover tbody tr.table-success:hover td {
        background-color: #e6fcf5 !important;
    }

    .nav-tabs .nav-link {
        font-size: 14px;
        font-weight: 600;
        color: #495057;
        padding: 10px 20px;
        border-radius: 8px 8px 0 0;
        transition: all .2s ease;
    }

    .nav-tabs .nav-link.active {
        color: #007bff;
        border-color: #dee2e6 #dee2e6 #fff;
        border-bottom: 3px solid #007bff;
        background-color: #fff;
    }
    .sync-summary-card {
        border: 0;
        border-radius: 12px;
        box-shadow: 0 10px 24px rgba(13, 110, 253, 0.08);
    }

    .sync-stat-box {
        border-radius: 10px;
        padding: 16px;
        color: #fff;
        min-height: 100%;
    }

    .sync-stat-box h4 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
    }

    .sync-stat-box p {
        margin: 4px 0 0;
        font-size: 13px;
        opacity: .9;
    }

    .sync-stat-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #4f8cff 100%);
    }

    .sync-stat-success {
        background: linear-gradient(135deg, #198754 0%, #38c172 100%);
    }

    .sync-stat-warning {
        background: linear-gradient(135deg, #fd7e14 0%, #f6ad55 100%);
    }

    .sync-stat-dark {
        background: linear-gradient(135deg, #343a40 0%, #4b5563 100%);
    }

    .sync-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
    }

    .sync-meta {
        font-size: 13px;
        color: #6c757d;
    }

    .sync-meta strong {
        color: #212529;
    }

    .table-sync-result td,
    .table-sync-result th {
        vertical-align: middle;
        white-space: nowrap;
    }

    .po-progress-wrap {
        min-width: 150px;
        max-width: 170px;
        margin: 0 auto;
    }

    .po-progress-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .po-progress-track {
        width: 100%;
        height: 9px;
        border-radius: 999px;
        overflow: hidden;
        background: #e9ecef;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.08);
    }

    .po-progress-fill {
        height: 100%;
        border-radius: 999px;
        transition: width .3s ease;
    }

    .po-progress-fill.is-danger {
        background: linear-gradient(90deg, #dc3545 0%, #ff6b6b 100%);
    }

    .po-progress-fill.is-warning {
        background: linear-gradient(90deg, #f39c12 0%, #f6c23e 100%);
    }

    .po-progress-fill.is-success {
        background: linear-gradient(90deg, #28a745 0%, #5ad67d 100%);
    }

    .po-progress-note {
        margin-top: 4px;
        font-size: 10px;
        color: #6c757d;
    }

    .lpb-status-actions {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        white-space: nowrap;
    }

    .lpb-status-actions .btn {
        width: 31px;
        height: 31px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .lpb-filter-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }

    .lpb-filter-toolbar .btn {
        min-width: 94px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">
                    <?php if (!$isDataLpbPage && !$isAdminPo && ($lvInt >= 1) && !in_array($jobdeskUpper, ['ADMINLOGLPB', 'ADMLPB', 'ADMLPB2', 'ADMIN LPB & GUDANG BENIH'], true)) : ?>
                        <div class="row">
                            <div class="col-auto">
                                <a href="<?= base_url('ics/ics_diffrent') ?>" class="btn btn-md btn-primary w-100 mb-3">
                                    <i class="fas fa-arrow-left"></i>
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('ics/icsdo') ?>" class="btn btn-md btn-primary w-100 mb-3">
                                    <i class="fas fa-minus-circle"></i> Data DO
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('ics/icspo') ?>" class="btn btn-md btn-secondary w-100 mb-3">
                                    <i class="fas fa-plus-circle"></i> Data LPB
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url($isAdmlpbUser ? 'ics/retur/pembelian/monitoring' : 'ics/retur') ?>" class="btn btn-md btn-primary w-100 mb-3">
                                    <i class="fas fa-plus-circle"></i> Data Retur
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title">
                                <i class="fas fa-plus-circle mr-2"></i> <?= $panelTitle ?>
                            </h3>
                        </div>

                        <div class="card-body">
                            <div class="container-fluid px-0">
                                <div class="row mb-3" id="button-row-lpb">
                                    <?php if ($isDataLpbPage && $isAdmlpbUser) : ?>
                                        <div class="col-md-1 col-sm-3 col-4 mb-2">
                                            <a class="btn btn-secondary btn-block" href="<?= base_url('dashboard') ?>" title="Kembali Dashboard">
                                                <i class="fas fa-home"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($canLpbManual) : ?>
                                        <div class="col-md-2 col-sm-6 mb-2">
                                            <a class="btn btn-success btn-block" href="<?= base_url('ics/lpb_manual') ?>">
                                                <i class="fas fa-keyboard"></i> Input LPB Manual
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <a class="btn btn-warning btn-block" href="<?= base_url($isAdmlpbUser ? 'ics/retur/pembelian/monitoring' : 'ics/retur') ?>">
                                            <i class="fas fa-undo"></i> Retur
                                        </a>
                                    </div>
                                    <?php if ($canLpbReport) : ?>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <a class="btn btn-info btn-block" href="<?= base_url('ics/lpb_report') ?>">
                                            <i class="fas fa-chart-bar"></i> Laporan LPB
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($canLpbRevision) : ?>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <a class="btn btn-danger btn-block" href="<?= base_url('ics/lpb_revision') ?>">
                                            <i class="fas fa-exclamation-triangle"></i> List Revisi Harga LPB
                                            <span class="badge badge-light ml-1"><?= $lpbRevisionBadgeCount ?></span>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($canSyncPo && $showLpbActions) : ?>
                                        <div class="col-md-2 col-sm-6 mb-2">
                                            <a class="btn btn-success btn-block" href="<?= base_url('data_lpb_zahir') ?>">
                                                <i class="fas fa-file-csv"></i> Data LPB
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                </div>

                                <?php if (!$isDataLpbPage && !$canSyncPo && $this->session->userdata('lv') == '2') : ?>
                                    <div class="row mb-3">
                                        <div class="col-md-2 col-sm-6 mb-2">
                                            <button class="btn btn-success btn-block" data-toggle="modal" data-target="#modalImportCSV">
                                                <i class="fas fa-file-csv"></i> Import CSV
                                            </button>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="modalImportCSV" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <form action="<?= base_url('ics/import_csv') ?>" method="post" enctype="multipart/form-data">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success">
                                                        <h5 class="modal-title">Import Data PO dari CSV</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Pilih File CSV</label>
                                                            <input type="file" name="file_csv" class="form-control" required accept=".csv">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success">Import</button>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($showPanelTabs) : ?>
                                <ul class="nav nav-tabs mb-3" id="poPanelTabs" role="tablist">
                                    <?php if ($showLogistikPanel) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $isLogistikActive ? 'active' : '' ?>" id="logistik-tab" data-toggle="tab" href="#logistik-panel" role="tab" aria-controls="logistik-panel" aria-selected="<?= $isLogistikActive ? 'true' : 'false' ?>">
                                            <i class="fas fa-file-invoice mr-1"></i> Data PO
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $isPoSupplierTabActive ? 'active' : '' ?>" id="po-supplier-tab" data-toggle="tab" href="#po-supplier-panel" role="tab" aria-controls="po-supplier-panel" aria-selected="<?= $isPoSupplierTabActive ? 'true' : 'false' ?>">
                                            <i class="fas fa-truck-loading mr-1 text-primary"></i> Data PO Supplier
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($showPurchasingPanel) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $isPurchasingActive ? 'active' : '' ?>" id="purchasing-tab" data-toggle="tab" href="#purchasing-panel" role="tab" aria-controls="purchasing-panel" aria-selected="<?= $isPurchasingActive ? 'true' : 'false' ?>">
                                            <i class="fas fa-clipboard-list mr-1"></i> Data LPB
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $isManualTabActive ? 'active' : '' ?>" id="lpb-manual-tab" data-toggle="tab" href="#lpb-manual-panel" role="tab" aria-controls="lpb-manual-panel" aria-selected="<?= $isManualTabActive ? 'true' : 'false' ?>">
                                            <i class="fas fa-keyboard mr-1 text-success"></i> LPB Manual
                                            <?php if ($unpostedManualCount > 0) : ?>
                                                <span class="badge badge-warning text-dark ml-1" title="<?= $unpostedManualCount ?> LPB Manual belum diposting"><?= $unpostedManualCount ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                </ul>
                                <?php endif; ?>

                                <div class="<?= $showPanelTabs ? 'tab-content' : '' ?>"<?= $showPanelTabs ? ' id="poPanelTabsContent"' : '' ?>>
                                    <?php if ($showLogistikPanel) : ?>
                                    <div class="<?= $logistikPanelClass ?>" id="logistik-panel" role="tabpanel" aria-labelledby="logistik-tab">
                                        <div class="lpb-filter-toolbar mb-3" id="lpbStatusFilter">
                                            <button type="button" class="btn btn-primary btn-sm active" data-filter="all">Semua</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="belum">
                                                <i class="fas fa-times mr-1"></i> Belum
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="partial">
                                                <i class="fas fa-clock mr-1"></i> Partial
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="done">
                                                <i class="fas fa-check mr-1"></i> Done
                                            </button>
                                        </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="idtb_ics_po">
                                        <thead class="thead-dark text-center">
                                            <tr>
                                                <th>No PO</th>
                                                <th>Tgl Transaksi</th>
                                                <?php if (!$hideSupplierCode) : ?>
                                                <th>Kode Supplier</th>
                                                <?php endif; ?>
                                                <th>Nama Supplier</th>
                                                <th class="text-center">Total Barang Order</th>
                                                <th class="text-center">Total Barang Diterima</th>
                                                <th class="text-center">Progress</th>
                                                <?php if (!$hideLastInput) : ?>
                                                <th class="text-center">Input Terakhir</th>
                                                <?php endif; ?>
                                                <th class="text-center">Status</th>
                                                <th class="text-center" style="width:90px;">#</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($lpb)) : ?>
                                                <?php foreach ($lpb as $row) :
                                                    $jumlah_barang_order    = (int) ($row['total_barang_order'] ?? 0);
                                                    $jumlah_barang_diterima = (int) ($row['total_barang_diterima'] ?? 0);
                                                    $jumlah_qty_order       = (float) ($row['total_qty_order'] ?? 0);
                                                    $jumlah_qty_diterima    = (float) ($row['total_qty_diterima'] ?? 0);
                                                    $status                 = strtolower(trim((string) ($row['status'] ?? '')));
                                                    $progressRaw            = (float) ($row['progress_persen'] ?? 0);
                                                    $progress               = max(0, min(100, $progressRaw));
                                                    $progressText           = rtrim(rtrim(number_format($progress, 2, '.', ''), '0'), '.');

                                                    if (floor($jumlah_qty_order) == $jumlah_qty_order) {
                                                        $jumlah_qty_order = (int) $jumlah_qty_order;
                                                    }

                                                    if (floor($jumlah_qty_diterima) == $jumlah_qty_diterima) {
                                                        $jumlah_qty_diterima = (int) $jumlah_qty_diterima;
                                                    }

                                                    if ($status === 'done') {
                                                        $rowClass = 'table-success';
                                                        $progressClass = 'is-success';
                                                        $badge = '<span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Done</span>';
                                                    } elseif ($status === 'partial') {
                                                        $rowClass = 'table-warning';
                                                        $progressClass = 'is-warning';
                                                        $badge = '<span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i> Partial</span>';
                                                    } else {
                                                        $rowClass = '';
                                                        $progressClass = 'is-danger';
                                                        $badge = '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i> Belum</span>';
                                                    }
                                                ?>
                                                    <tr class="<?= $rowClass ?>" data-lpb-status="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">
                                                        <td><?= htmlspecialchars($row['no_po'] ?? '') ?></td>
                                                        <td data-order="<?= htmlspecialchars($row['tgl_transaksi'] ?? '') ?>"><?= htmlspecialchars($formatDate($row['tgl_transaksi'] ?? '')) ?></td>
                                                        <?php if (!$hideSupplierCode) : ?>
                                                        <td><?= htmlspecialchars($row['kdsupp'] ?? '') ?></td>
                                                        <?php endif; ?>
                                                        <td><?= htmlspecialchars($row['nm_suplier'] ?? '-') ?></td>
                                                        <td class="text-center font-weight-bold"><?= $jumlah_barang_order ?></td>
                                                        <td class="text-center font-weight-bold <?= $jumlah_barang_diterima > 0 ? 'text-success' : 'text-danger' ?>">
                                                            <?= $jumlah_barang_diterima ?>
                                                        </td>
                                                        <td>
                                                            <div class="po-progress-wrap">
                                                                <div class="po-progress-label">
                                                                    <span><?= $progressText ?>%</span>
                                                                    <span><?= $jumlah_qty_diterima ?> / <?= $jumlah_qty_order ?></span>
                                                                </div>
                                                                <div class="po-progress-track">
                                                                    <div class="po-progress-fill <?= $progressClass ?>" style="width: <?= $progress ?>%;"></div>
                                                                </div>
                                                                <div class="po-progress-note">Berdasarkan qty diterima</div>
                                                            </div>
                                                        </td>
                                                        <?php if (!$hideLastInput) : ?>
                                                        <td class="text-center"><?= htmlspecialchars($row['input_terakhir'] ?? '-') ?></td>
                                                        <?php endif; ?>
                                                        <td class="text-center"><?= $badge ?></td>
                                                        <td class="text-center">
                                                            <a href="<?= base_url('ics/detail_po?no_po=' . urlencode($row['no_po']) . '&kd_suplier=' . urlencode($row['kdsupp'] ?? '')) ?>" class="btn btn-info btn-sm" target="_blank">
                                                                <i class="fas fa-eye"></i> Detail
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                    </div>

                                    <div class="<?= $poSupplierPanelClass ?>" id="po-supplier-panel" role="tabpanel" aria-labelledby="po-supplier-tab">
                                        <div class="card border shadow-sm mb-3" style="border-radius: 10px; background: #f8fafc;">
                                            <div class="card-body py-3 px-3">
                                                <div class="row align-items-end">
                                                    <div class="col-lg-5 col-md-7 mb-2 mb-md-0">
                                                        <label for="filterPoSupplierSelect" class="font-weight-bold mb-1" style="font-size: 13px; color: #334155;">
                                                            <i class="fas fa-building text-primary mr-1"></i> Pilih Supplier (PO)
                                                        </label>
                                                        <select id="filterPoSupplierSelect" class="form-control select2" style="width: 100%;" data-placeholder="-- Cari & Pilih Supplier --">
                                                            <option value="">-- Cari & Pilih Supplier --</option>
                                                            <?php if (!empty($po_suppliers)) : ?>
                                                                <?php foreach ($po_suppliers as $supp) :
                                                                    $kdSuppOpt = trim((string) ($supp['kdsupp'] ?? ''));
                                                                    $nmSuppOpt = trim((string) ($supp['nm_suplier'] ?? $kdSuppOpt));
                                                                    $totalPoOpt = (int) ($supp['total_po'] ?? 0);
                                                                    $labelOpt = ($hideSupplierCode || $kdSuppOpt === '' || $kdSuppOpt === $nmSuppOpt)
                                                                        ? $nmSuppOpt
                                                                        : ($kdSuppOpt . ' - ' . $nmSuppOpt);
                                                                ?>
                                                                    <option value="<?= htmlspecialchars($kdSuppOpt, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <?= htmlspecialchars($labelOpt) ?> (<?= $totalPoOpt ?> PO)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-3 col-md-5 mb-2 mb-lg-0 d-flex align-items-center" style="gap: 8px;">
                                                        <button type="button" id="btnReloadPoSupplier" class="btn btn-primary btn-sm px-3" disabled>
                                                            <i class="fas fa-sync-alt mr-1"></i> Muat Data
                                                        </button>
                                                        <button type="button" id="btnResetPoSupplier" class="btn btn-outline-secondary btn-sm px-3" disabled>
                                                            <i class="fas fa-redo mr-1"></i> Reset
                                                        </button>
                                                    </div>
                                                    <div class="col-lg-4 col-md-12 text-lg-right mt-2 mt-lg-0">
                                                        <div id="poSupplierSummaryBadges" class="d-none">
                                                            <span class="badge badge-primary px-2 py-1 mr-1" style="font-size: 12px;">
                                                                Total PO: <strong id="summarySupplierTotalPo">0</strong>
                                                            </span>
                                                            <span class="badge badge-info px-2 py-1 mr-1" style="font-size: 12px;">
                                                                Total Barang: <strong id="summarySupplierTotalItem">0</strong>
                                                            </span>
                                                            <span class="badge badge-success px-2 py-1" style="font-size: 12px;">
                                                                Diterima: <strong id="summarySupplierTotalReceived">0</strong> / <strong id="summarySupplierTotalOrder">0</strong>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                            <div class="lpb-filter-toolbar" id="poSupplierStatusFilter">
                                                <button type="button" class="btn btn-primary btn-sm active" data-filter="all">Semua</button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="belum">
                                                    <i class="fas fa-times mr-1"></i> Belum
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="partial">
                                                    <i class="fas fa-clock mr-1"></i> Partial
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="done">
                                                    <i class="fas fa-check mr-1"></i> Done
                                                </button>
                                            </div>
                                            <div id="poSupplierLoadingIndicator" class="text-primary font-weight-bold d-none" style="font-size: 13px;">
                                                <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data barang PO supplier...
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="idtb_ics_po_supplier" style="width: 100%;">
                                                <thead class="thead-dark text-center">
                                                    <tr>
                                                        <th>No PO</th>
                                                        <th>Tanggal Transaksi</th>
                                                        <th>Nama Barang</th>
                                                        <th class="text-center">Qty Barang</th>
                                                        <th class="text-center">Qty Barang Diterima</th>
                                                        <th class="text-center">Progress</th>
                                                        <th class="text-center">Status Input Terakhir</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center" style="width:90px;">#</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($showPurchasingPanel) : ?>
                                    <div class="<?= $purchasingPanelClass ?>" id="purchasing-panel" role="tabpanel" aria-labelledby="purchasing-tab">
                                        <div class="lpb-filter-toolbar mb-3" id="purchasingStatusFilter">
                                            <button type="button" class="btn btn-primary btn-sm active" data-filter="all">
                                                Semua
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="invoice">
                                                <i class="fas fa-file-invoice mr-1"></i> Belum Invoice
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="pajak">
                                                <i class="fas fa-percent mr-1"></i> Belum Pajak
                                            </button>
                                            <!-- Disembunyikan sementara hingga alur afirmasi harga diaktifkan kembali -->
                                            <button type="button" class="btn btn-outline-secondary btn-sm d-none" data-filter="uang">
                                                <i class="fas fa-check-double mr-1"></i> Belum Afirmasi Harga
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="idtb_ics_po_purchasing">
                                                <thead class="thead-dark text-center">
                                                    <tr>
                                                        <th>Tgl LPB</th>
                                                        <th>NO LPB</th>
                                                        <th>Tgl PO</th>
                                                        <th>No PO</th>
                                                        <th>Tgl SJ</th>
                                                        <th>No SJ</th>
                                                        <th>No Invoice</th>
                                                        <th>No FP</th>
                                                        <th>Suplier</th>
                                                        <?php if ($canViewLpbNominal) : ?>
                                                        <th class="text-right">Grand Total</th>
                                                        <?php else : ?>
                                                        <th class="text-center">Status Harga</th>
                                                        <?php endif; ?>
                                                        <th class="text-center">Status Data</th>
                                                        <th class="text-center">Satatus Barang</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($lpb_purchasing)) : ?>
                                                        <?php foreach ($lpb_purchasing as $row) :
                                                            $progressStatus = strtolower(trim((string) ($row['progress_status'] ?? 'belum')));
                                                            $totalVerified = (int) ($row['total_verified'] ?? 0);
                                                            $totalDetail = (int) ($row['total_detail'] ?? 0);
                                                            $invoiceValue = trim((string) ($row['no_invoice'] ?? ''));
                                                            $fakturValue = trim((string) ($row['kode_faktur_pajak'] ?? ''));
                                                            $hasInvoice = $invoiceValue !== '' && $invoiceValue !== '-';
                                                            $hasFaktur = $fakturValue !== '' && $fakturValue !== '-';
                                                            $isFakturPajakSederhana = (int) ($row['is_faktur_pajak_sederhana'] ?? 0) === 1;
                                                            $isTanpaFakturPajak = (int) ($row['is_tanpa_faktur_pajak'] ?? 0) === 1;
                                                            $isVerified = $progressStatus === 'done' || ($totalDetail > 0 && $totalVerified >= $totalDetail);
                                                            $invoiceQtyDiff = (float) ($row['invoice_qty_diff'] ?? 0);
                                                            $fakturQtyDiff = (float) ($row['faktur_qty_diff'] ?? 0);
                                                            $isInvoiceComplete = $isFakturPajakSederhana || ($hasInvoice && $invoiceQtyDiff <= 0.0001);
                                                            $isFakturComplete = $isFakturPajakSederhana || $isTanpaFakturPajak || ($hasFaktur && $fakturQtyDiff <= 0.0001);
                                                            // Penanda untuk filter tombol Belum Invoice / Belum Pajak (hanya ikon abu-abu yang dianggap belum ada)
                                                            $hasInvoiceData = $isFakturPajakSederhana || $hasInvoice;
                                                            $hasFakturData = $isFakturPajakSederhana || $isTanpaFakturPajak || $hasFaktur;
                                                            $invoiceBtnClass = $isInvoiceComplete
                                                                ? 'btn-success'
                                                                : ($hasInvoice ? 'btn-warning text-dark' : 'btn-light text-secondary border');
                                                            $fakturBtnClass = $isFakturComplete
                                                                ? 'btn-success'
                                                                : ($hasFaktur ? 'btn-warning text-dark' : 'btn-light text-secondary border');
                                                            $invoiceTitle = $isFakturPajakSederhana
                                                                ? 'Faktur Pajak Sederhana'
                                                                : (!$hasInvoice
                                                                    ? 'Invoice belum ada'
                                                                    : ($isInvoiceComplete ? 'Invoice lengkap (selisih qty 0)' : 'Invoice masih ada selisih qty ' . rtrim(rtrim(number_format($invoiceQtyDiff, 4, '.', ''), '0'), '.')));
                                                            $fakturTitle = $isFakturPajakSederhana
                                                                ? 'Faktur Pajak Sederhana'
                                                                : ($isTanpaFakturPajak
                                                                    ? 'Supplier tidak menerbitkan faktur pajak'
                                                                    : (!$hasFaktur
                                                                        ? 'Pajak/Faktur belum ada'
                                                                        : ($isFakturComplete ? 'Pajak/Faktur lengkap (selisih qty 0)' : 'Pajak/Faktur masih ada selisih qty ' . rtrim(rtrim(number_format($fakturQtyDiff, 4, '.', ''), '0'), '.'))));
                                                            $verifiedBtnClass = $isVerified ? 'btn-success' : 'btn-light text-secondary border';
                                                            $hasSalesTransaction = (int) ($row['has_sales_transaction'] ?? 0) === 1;
                                                            $hasActiveJournal = (int) ($row['has_active_lpb_journal'] ?? 0) === 1;
                                                            $statusLpbRaw = $row['status_lpb'] ?? null;
                                                            if ($statusLpbRaw === null || $statusLpbRaw === '') {
                                                                $statusBarangBadge = '<span class="badge badge-secondary px-2 py-1">DRAFT</span>';
                                                            } elseif ((string) $statusLpbRaw === '0') {
                                                                $statusBarangBadge = '<span class="badge badge-warning px-2 py-1">UNPOST</span>';
                                                            } else {
                                                                $statusBarangBadge = '<span class="badge badge-success px-2 py-1">POST</span>';
                                                            }
                                                            $tglSjValue = trim((string) ($row['tgl_sj'] ?? ''));
                                                            if ($tglSjValue === '' || $tglSjValue === '0000-00-00') {
                                                                $tglSjValue = '-';
                                                            }
                                                            $detailUrl = base_url('ics/detail_record_lpb?kd_po=' . urlencode($row['kd_po'] ?? '') . '&no_po=' . urlencode($row['no_po'] ?? '') . '&kd_suplier=' . urlencode($row['kd_suplier'] ?? '') . (!empty($row['id_lpb']) ? '&id_lpb=' . urlencode($row['id_lpb']) : ''));
                                                            $salesTitle = $hasSalesTransaction
                                                                ? 'Sudah ada ' . (int) ($row['sales_invoice_count'] ?? 0) . ' faktur penjualan: ' . (string) ($row['sales_invoice_sample'] ?? '-')
                                                                : 'Belum ada transaksi penjualan dari LPB ini';
                                                            $journalTitle = $hasActiveJournal
                                                                ? 'Jurnal LPB POSTED: ' . (string) ($row['lpb_journal_sample'] ?? '-')
                                                                : 'Belum ada jurnal LPB POSTED aktif';
                                                            $isManualRow = (int) ($row['is_manual_lpb'] ?? 0) === 1;
                                                        ?>
                                                        <tr data-has-invoice="<?= $hasInvoiceData ? '1' : '0' ?>" data-has-faktur="<?= $hasFakturData ? '1' : '0' ?>" data-is-verified="<?= $isVerified ? '1' : '0' ?>" data-has-sales="<?= $hasSalesTransaction ? '1' : '0' ?>">
                                                            <td data-order="<?= htmlspecialchars($row['tgl_lpb'] ?? '') ?>"><?= htmlspecialchars($formatDate($row['tgl_lpb'] ?? '')) ?></td>
                                                            <td>
                                                                <a href="<?= $detailUrl ?>" class="font-weight-bold" target="_blank">
                                                                    <?= htmlspecialchars($row['nomor_lpb'] ?? '-') ?>
                                                                </a>
                                                                <?php if ($isManualRow) : ?>
                                                                    <span class="badge badge-info ml-1" style="font-size: 10px;" title="LPB Manual diposting"><i class="fas fa-keyboard"></i> MANUAL</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td data-order="<?= htmlspecialchars($row['tgl_po'] ?? '') ?>"><?= htmlspecialchars($isManualRow ? '-' : $formatDate($row['tgl_po'] ?? '')) ?></td>
                                                            <td>
                                                                <?php if ($isManualRow) : ?>
                                                                    <span class="badge badge-light border text-monospace"><?= htmlspecialchars($row['manual_ref_no'] ?? $row['no_po'] ?? '-') ?></span>
                                                                <?php else : ?>
                                                                    <?= htmlspecialchars($row['no_po'] ?? '') ?>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td data-order="<?= htmlspecialchars($row['tgl_sj'] ?? '') ?>"><?= htmlspecialchars($formatDate($row['tgl_sj'] ?? '')) ?></td>
                                                            <td><?= htmlspecialchars($row['nosj'] ?? '-') ?></td>
                                                            <td><?= htmlspecialchars($hasInvoice ? $invoiceValue : '-') ?></td>
                                                            <td><?= htmlspecialchars($hasFaktur ? $fakturValue : '-') ?></td>
                                                            <td><?= htmlspecialchars($row['nama_suplier'] ?? '-') ?></td>
                                                            <?php if ($canViewLpbNominal) : ?>
                                                            <td class="text-right"><?= 'Rp ' . number_format((float) ($row['grand_total_lpb'] ?? 0), 0, ',', '.') ?></td>
                                                            <?php else : ?>
                                                            <td class="text-center">
                                                                <span class="badge <?= $isVerified ? 'badge-success' : 'badge-warning' ?> px-2 py-1">
                                                                    <?= $isVerified ? 'Harga tersedia' : 'Menunggu accounting' ?>
                                                                </span>
                                                            </td>
                                                            <?php endif; ?>
                                                            <td class="text-center">
                                                                <div class="lpb-status-actions">
                                                                    <button type="button" class="btn btn-sm <?= $invoiceBtnClass ?>" title="<?= htmlspecialchars($invoiceTitle, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <i class="fas fa-file-invoice"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm <?= $fakturBtnClass ?>" title="<?= htmlspecialchars($fakturTitle, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <i class="fas fa-percent"></i>
                                                                    </button>
                                                                    <!-- Disembunyikan sementara hingga alur afirmasi harga diaktifkan kembali -->
                                                                    <button type="button" class="btn btn-sm d-none <?= $verifiedBtnClass ?>" title="<?= $isVerified ? 'Afirmasi harga selesai' : 'Afirmasi harga belum selesai' ?>">
                                                                        <i class="fas fa-check-double"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="lpb-status-actions">
                                                                    <?= $statusBarangBadge ?>
                                                                    <button type="button" class="btn btn-sm <?= $hasSalesTransaction ? 'btn-warning' : 'btn-light text-secondary border' ?>" title="<?= htmlspecialchars($salesTitle, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <i class="fas fa-cash-register"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm <?= $hasActiveJournal ? 'btn-danger' : 'btn-light text-secondary border' ?>" title="<?= htmlspecialchars($journalTitle, ENT_QUOTES, 'UTF-8') ?>">
                                                                        <i class="fas fa-balance-scale"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <div class="<?= $manualPanelClass ?>" id="lpb-manual-panel" role="tabpanel" aria-labelledby="lpb-manual-tab">
                                        <div class="alert alert-light border py-2 px-3 mb-3 d-flex align-items-center" style="font-size: 13px; border-radius: 8px; background: #f8fafc;">
                                            <i class="fas fa-info-circle text-primary mr-2" style="font-size: 16px;"></i>
                                            <span>Tab ini menampilkan antrean LPB Manual yang <strong>belum diposting</strong> (Draft / Unpost). LPB Manual yang telah diverifikasi &amp; diposting oleh Purchasing otomatis digabungkan ke tab <strong>Data LPB</strong>.</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                                            <div class="lpb-filter-toolbar" id="lpbManualStatusFilter">
                                                <button type="button" class="btn btn-primary btn-sm active" data-filter="all">
                                                    <i class="fas fa-clock mr-1"></i> Belum Diposting (Draft / Unpost)
                                                    <?php if ($unpostedManualCount > 0) : ?>
                                                        <span class="badge badge-warning text-dark ml-1"><?= $unpostedManualCount ?></span>
                                                    <?php endif; ?>
                                                </button>
                                            </div>
                                            <?php if ($canLpbManual) : ?>
                                                <div>
                                                    <a href="<?= base_url('ics/lpb_manual') ?>" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                                                        <i class="fas fa-plus mr-1"></i> Input LPB Manual Baru
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="table-responsive">
                                             <table class="table table-bordered table-hover" id="idtb_ics_lpb_manual" style="width:100%;">
                                                <thead class="thead-dark text-center">
                                                    <tr>
                                                        <th>Tgl LPB</th>
                                                        <th>No. LPB</th>
                                                        <th>Ref Manual</th>
                                                        <th>Suplier</th>
                                                        <th>No. SJ</th>
                                                        <th>Tgl SJ</th>
                                                        <th class="text-center">Total Item</th>
                                                        <th class="text-center">Total Qty</th>
                                                        <?php if ($canViewLpbNominal) : ?>
                                                            <th class="text-right">Grand Total</th>
                                                        <?php endif; ?>
                                                        <th class="text-center">Status Barang</th>
                                                        <th>Keterangan</th>
                                                        <th class="text-center" style="width: 110px;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (!empty($lpb_manual)) : ?>
                                                        <?php foreach ($lpb_manual as $mRow) :
                                                            $statusLpbRaw = $mRow['status_lpb'] ?? null;
                                                            if ($statusLpbRaw === null || $statusLpbRaw === '') {
                                                                $statusFilterKey = 'draft';
                                                                $statusBarangBadge = '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-pencil-alt mr-1"></i> DRAFT</span>';
                                                            } elseif ((string) $statusLpbRaw === '0') {
                                                                $statusFilterKey = 'draft';
                                                                $statusBarangBadge = '<span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i> UNPOST</span>';
                                                            } else {
                                                                $statusFilterKey = 'post';
                                                                $statusBarangBadge = '<span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> POST</span>';
                                                            }
                                                            $detailManualUrl = base_url('ics/detail_record_lpb?id_lpb=' . (int) $mRow['id_lpb'] . '&kd_po=' . urlencode($mRow['kd_po'] ?? '') . '&no_po=' . urlencode($mRow['no_po'] ?? ''));
                                                            $printManualUrl = base_url('ics/print_lpb_record/' . (int) $mRow['id_lpb']);
                                                        ?>
                                                        <tr data-lpb-status="<?= $statusFilterKey ?>">
                                                            <td data-order="<?= htmlspecialchars($mRow['tgl_lpb'] ?? '') ?>"><?= htmlspecialchars($formatDate($mRow['tgl_lpb'] ?? '')) ?></td>
                                                            <td class="font-weight-bold text-primary">
                                                                <a href="<?= $detailManualUrl ?>" target="_blank" title="Buka Detail LPB Manual">
                                                                    <?= htmlspecialchars($mRow['nomor_lpb'] ?? '-') ?>
                                                                </a>
                                                            </td>
                                                            <td><span class="badge badge-light border font-weight-bold"><?= htmlspecialchars($mRow['manual_ref_no'] ?? $mRow['kd_po'] ?? '-') ?></span></td>
                                                            <td><?= htmlspecialchars($mRow['nama_suplier'] ?? '-') ?></td>
                                                            <td><?= htmlspecialchars($mRow['nosj'] ?? '-') ?></td>
                                                            <td data-order="<?= htmlspecialchars($mRow['tgl_sj'] ?? '') ?>"><?= htmlspecialchars($formatDate($mRow['tgl_sj'] ?? '')) ?></td>
                                                            <td class="text-center font-weight-bold"><?= number_format((int) ($mRow['total_detail'] ?? 0)) ?></td>
                                                            <td class="text-center font-weight-bold text-success"><?= number_format((float) ($mRow['total_qty'] ?? 0), 2, ',', '.') ?></td>
                                                            <?php if ($canViewLpbNominal) : ?>
                                                                <td class="text-right font-weight-bold"><?= 'Rp ' . number_format((float) ($mRow['grand_total_lpb'] ?? 0), 0, ',', '.') ?></td>
                                                            <?php endif; ?>
                                                            <td class="text-center"><?= $statusBarangBadge ?></td>
                                                            <td><small class="text-muted"><?= htmlspecialchars($mRow['keterangan'] ?? '-') ?></small></td>
                                                            <td class="text-center">
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="<?= $detailManualUrl ?>" class="btn btn-info btn-sm" target="_blank" title="Lihat Rincian Barang LPB Manual">
                                                                        <i class="fas fa-eye"></i> Detail
                                                                    </a>
                                                                    <a href="<?= $printManualUrl ?>" class="btn btn-outline-secondary btn-sm" target="_blank" title="Cetak Bukti LPB">
                                                                        <i class="fas fa-print"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
        </footer>
        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', function() {
            var $ = window.jQuery || window.$;
            if (!$ || !$.fn || !$.fn.DataTable) {
                return;
            }

            $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
                var tables = $.fn.dataTable.tables({ visible: true, api: true });
                tables.columns.adjust();
                if (tables.responsive && typeof tables.responsive.recalc === 'function') {
                    tables.responsive.recalc();
                }
            });

            <?php if ($showLogistikPanel) : ?>
            var lpbStatusFilter = 'all';

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (!settings.nTable || settings.nTable.id !== 'idtb_ics_po') {
                    return true;
                }

                if (lpbStatusFilter === 'all') {
                    return true;
                }

                var rowData = settings.aoData[dataIndex];
                if (!rowData || !rowData.nTr) {
                    return true;
                }
                var $row = $(rowData.nTr);
                return String($row.attr('data-lpb-status') || '') === lpbStatusFilter;
            });

            var lpbTable = $('#idtb_ics_po').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    zeroRecords: 'Tidak ada data ditemukan',
                    emptyTable: 'Belum ada data LPB',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });

            $(document).on('click', '#lpbStatusFilter button[data-filter]', function() {
                lpbStatusFilter = $(this).attr('data-filter') || 'all';
                $('#lpbStatusFilter button[data-filter]')
                    .removeClass('btn-primary active')
                    .addClass('btn-outline-secondary');
                $(this)
                    .removeClass('btn-outline-secondary')
                    .addClass('btn-primary active');
                lpbTable.draw();
            });

            // Inisialisasi Select2 & DataTable untuk Tab Data PO Supplier
            if ($.fn.select2) {
                $('#filterPoSupplierSelect').select2({
                    theme: 'bootstrap4',
                    placeholder: '-- Cari & Pilih Supplier --',
                    allowClear: true,
                    width: '100%'
                });
            }

            var poSupplierStatusFilter = 'all';

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (!settings.nTable || settings.nTable.id !== 'idtb_ics_po_supplier') {
                    return true;
                }

                if (poSupplierStatusFilter === 'all') {
                    return true;
                }

                var rowData = settings.aoData[dataIndex];
                if (!rowData || !rowData.nTr) {
                    return true;
                }
                var $row = $(rowData.nTr);
                return String($row.attr('data-lpb-status') || '') === poSupplierStatusFilter;
            });

            var poSupplierTable = $('#idtb_ics_po_supplier').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [
                    [1, 'desc'],
                    [0, 'desc']
                ],
                columnDefs: [
                    {
                        className: 'text-center font-weight-bold',
                        targets: [3, 4]
                    },
                    {
                        className: 'text-center',
                        targets: [5, 6, 7, 8]
                    },
                    {
                        orderable: false,
                        targets: -1
                    }
                ],
                createdRow: function(row, data) {
                    var st = (data && data._status) ? String(data._status) : '';
                    $(row).attr('data-lpb-status', st);
                    if (st === 'done') {
                        $(row).addClass('table-success');
                    } else if (st === 'partial') {
                        $(row).addClass('table-warning');
                    }
                },
                columns: [
                    { data: 'no_po_html' },
                    {
                        data: 'tgl_transaksi_display',
                        render: function(data, type, row) {
                            if (type === 'sort' || type === 'type') {
                                return row.tgl_transaksi_raw || '';
                            }
                            return data;
                        }
                    },
                    { data: 'nama_barang_html' },
                    { data: 'qty_barang_html' },
                    { data: 'qty_diterima_html' },
                    { data: 'progress_html' },
                    { data: 'input_terakhir_html' },
                    { data: 'status_html' },
                    { data: 'action_html' }
                ],
                language: {
                    search: 'Cari Barang / No PO:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    zeroRecords: 'Tidak ada data barang PO yang cocok dengan filter',
                    emptyTable: 'Silakan pilih Supplier terlebih dahulu untuk menampilkan data barang PO',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });

            function escapeHtmlStr(str) {
                return String(str == null ? '' : str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function formatDateId(dateStr) {
                var s = String(dateStr == null ? '' : dateStr).trim();
                if (!s || s === '-' || s === '0000-00-00' || s === '0000-00-00 00:00:00') {
                    return '-';
                }
                var parts = s.split(' ')[0].split('-');
                if (parts.length === 3) {
                    return parts[2] + '/' + parts[1] + '/' + parts[0];
                }
                return s;
            }

            function formatNumberClean(val) {
                var num = parseFloat(val);
                if (isNaN(num)) {
                    return '0';
                }
                if (Math.floor(num) === num) {
                    return num.toLocaleString('id-ID');
                }
                return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            }

            function loadPoSupplierItems(kdSuplier) {
                var kd = String(kdSuplier == null ? '' : kdSuplier).trim();
                if (!kd) {
                    poSupplierTable.clear().draw();
                    $('#poSupplierSummaryBadges').addClass('d-none');
                    $('#btnReloadPoSupplier, #btnResetPoSupplier').prop('disabled', true);
                    return;
                }

                $('#btnReloadPoSupplier, #btnResetPoSupplier').prop('disabled', false);
                $('#poSupplierLoadingIndicator').removeClass('d-none');

                $.ajax({
                    url: '<?= base_url('ics/ajax_get_po_supplier_items') ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        kd_suplier: kd,
                        date1: '<?= htmlspecialchars((string) ($date1 ?? ''), ENT_QUOTES, 'UTF-8') ?>',
                        date2: '<?= htmlspecialchars((string) ($date2 ?? ''), ENT_QUOTES, 'UTF-8') ?>'
                    },
                    success: function(res) {
                        $('#poSupplierLoadingIndicator').addClass('d-none');
                        if (!res || res.status !== 'success') {
                            poSupplierTable.clear().draw();
                            $('#poSupplierSummaryBadges').addClass('d-none');
                            return;
                        }

                        var rows = Array.isArray(res.rows) ? res.rows : [];
                        var tableRows = [];

                        for (var i = 0; i < rows.length; i++) {
                            var r = rows[i] || {};
                            var noPo = String(r.no_po || '');
                            var kdSupp = String(r.kdsupp || kd);
                            var kdBarang = String(r.kd_barang || '');
                            var namaBarang = String(r.nama_barang || '-');
                            var tglRaw = String(r.tgl_transaksi || '');
                            var qtyOrderNum = parseFloat(r.qty_barang || 0) || 0;
                            var qtyRcvNum = parseFloat(r.qty_barang_diterima || 0) || 0;
                            var progressRaw = parseFloat(r.progress_persen || 0) || 0;
                            var progress = Math.max(0, Math.min(100, progressRaw));
                            var progressText = Number(progress.toFixed(2)).toString();
                            var status = String(r.status || 'belum').toLowerCase().trim();
                            var inputTerakhir = String(r.input_terakhir || '-');

                            var progressClass = 'is-danger';
                            var badgeHtml = '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i> Belum</span>';
                            if (status === 'done') {
                                progressClass = 'is-success';
                                badgeHtml = '<span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Done</span>';
                            } else if (status === 'partial') {
                                progressClass = 'is-warning';
                                badgeHtml = '<span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i> Partial</span>';
                            }

                            var qtyOrderFmt = formatNumberClean(qtyOrderNum);
                            var qtyRcvFmt = formatNumberClean(qtyRcvNum);

                            var progressHtml = '<div class="po-progress-wrap">' +
                                '<div class="po-progress-label">' +
                                    '<span>' + escapeHtmlStr(progressText) + '%</span>' +
                                    '<span>' + escapeHtmlStr(qtyRcvFmt) + ' / ' + escapeHtmlStr(qtyOrderFmt) + '</span>' +
                                '</div>' +
                                '<div class="po-progress-track">' +
                                    '<div class="po-progress-fill ' + progressClass + '" style="width: ' + progress + '%;"></div>' +
                                '</div>' +
                                '<div class="po-progress-note">Berdasarkan qty diterima</div>' +
                            '</div>';

                            var detailUrl = '<?= base_url('ics/detail_po') ?>?no_po=' + encodeURIComponent(noPo) + '&kd_suplier=' + encodeURIComponent(kdSupp);
                            var actionHtml = '<a href="' + detailUrl + '" class="btn btn-info btn-sm" target="_blank">' +
                                '<i class="fas fa-eye"></i> Detail' +
                            '</a>';

                            var namaBarangHtml = '<div class="font-weight-bold">' + escapeHtmlStr(namaBarang) + '</div>' +
                                (kdBarang ? '<small class="text-muted">' + escapeHtmlStr(kdBarang) + '</small>' : '');

                            tableRows.push({
                                _status: status,
                                no_po_html: '<span class="font-weight-bold">' + escapeHtmlStr(noPo) + '</span>',
                                tgl_transaksi_raw: tglRaw,
                                tgl_transaksi_display: escapeHtmlStr(formatDateId(tglRaw)),
                                nama_barang_html: namaBarangHtml,
                                qty_barang_html: escapeHtmlStr(qtyOrderFmt),
                                qty_diterima_html: '<span class="' + (qtyRcvNum > 0 ? 'text-success' : 'text-danger') + '">' + escapeHtmlStr(qtyRcvFmt) + '</span>',
                                progress_html: progressHtml,
                                input_terakhir_html: escapeHtmlStr(inputTerakhir),
                                status_html: badgeHtml,
                                action_html: actionHtml
                            });
                        }

                        poSupplierTable.clear().rows.add(tableRows).draw();

                        var summary = res.summary || {};
                        $('#summarySupplierTotalPo').text(formatNumberClean(summary.total_po || 0));
                        $('#summarySupplierTotalItem').text(formatNumberClean(summary.total_item || rows.length));
                        $('#summarySupplierTotalOrder').text(formatNumberClean(summary.total_qty_order || 0));
                        $('#summarySupplierTotalReceived').text(formatNumberClean(summary.total_qty_diterima || 0));
                        $('#poSupplierSummaryBadges').removeClass('d-none');
                    },
                    error: function() {
                        $('#poSupplierLoadingIndicator').addClass('d-none');
                        poSupplierTable.clear().draw();
                        $('#poSupplierSummaryBadges').addClass('d-none');
                    }
                });
            }

            $(document).on('change', '#filterPoSupplierSelect', function() {
                loadPoSupplierItems($(this).val());
            });

            $(document).on('click', '#btnReloadPoSupplier', function() {
                loadPoSupplierItems($('#filterPoSupplierSelect').val());
            });

            $(document).on('click', '#btnResetPoSupplier', function() {
                $('#filterPoSupplierSelect').val('').trigger('change');
                poSupplierStatusFilter = 'all';
                $('#poSupplierStatusFilter button[data-filter]')
                    .removeClass('btn-primary active')
                    .addClass('btn-outline-secondary');
                $('#poSupplierStatusFilter button[data-filter="all"]')
                    .removeClass('btn-outline-secondary')
                    .addClass('btn-primary active');
                poSupplierTable.clear().draw();
            });

            $(document).on('click', '#poSupplierStatusFilter button[data-filter]', function() {
                poSupplierStatusFilter = $(this).attr('data-filter') || 'all';
                $('#poSupplierStatusFilter button[data-filter]')
                    .removeClass('btn-primary active')
                    .addClass('btn-outline-secondary');
                $(this)
                    .removeClass('btn-outline-secondary')
                    .addClass('btn-primary active');
                poSupplierTable.draw();
            });
            <?php endif; ?>

            <?php if ($showPurchasingPanel) : ?>
            var purchasingStatusFilter = 'all';

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (!settings.nTable || settings.nTable.id !== 'idtb_ics_po_purchasing') {
                    return true;
                }

                if (purchasingStatusFilter === 'all') {
                    return true;
                }

                var rowData = settings.aoData[dataIndex];
                if (!rowData || !rowData.nTr) {
                    return true;
                }
                var $row = $(rowData.nTr);

                if (purchasingStatusFilter === 'invoice') {
                    return String($row.attr('data-has-invoice')) === '0';
                }

                if (purchasingStatusFilter === 'pajak') {
                    return String($row.attr('data-has-faktur')) === '0';
                }

                if (purchasingStatusFilter === 'uang') {
                    return String($row.attr('data-is-verified')) === '0';
                }

                return true;
            });

            var purchasingTable = $('#idtb_ics_po_purchasing').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    zeroRecords: 'Tidak ada data ditemukan',
                    emptyTable: 'Belum ada data purchasing',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });

            $(document).on('click', '#purchasingStatusFilter button[data-filter]', function() {
                purchasingStatusFilter = $(this).attr('data-filter') || 'all';
                $('#purchasingStatusFilter button[data-filter]')
                    .removeClass('btn-primary active')
                    .addClass('btn-outline-secondary');
                $(this)
                    .removeClass('btn-outline-secondary')
                    .addClass('btn-primary active');
                purchasingTable.draw();
            });
            <?php endif; ?>

            // Inisialisasi DataTable LPB Manual
            var lpbManualStatusFilter = 'all';

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (!settings.nTable || settings.nTable.id !== 'idtb_ics_lpb_manual') {
                    return true;
                }

                if (lpbManualStatusFilter === 'all') {
                    return true;
                }

                var rowData = settings.aoData[dataIndex];
                if (!rowData || !rowData.nTr) {
                    return true;
                }
                var $row = $(rowData.nTr);
                return String($row.attr('data-lpb-status') || '') === lpbManualStatusFilter;
            });

            var lpbManualTable = $('#idtb_ics_lpb_manual').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
                language: {
                    search: 'Cari LPB Manual:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    zeroRecords: 'Tidak ada data LPB Manual ditemukan',
                    emptyTable: 'Belum ada data LPB Manual',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });

            $(document).on('click', '#lpbManualStatusFilter button[data-filter]', function() {
                lpbManualStatusFilter = $(this).attr('data-filter') || 'all';
                $('#lpbManualStatusFilter button[data-filter]')
                    .removeClass('btn-primary active')
                    .addClass('btn-outline-secondary');
                $(this)
                    .removeClass('btn-outline-secondary')
                    .addClass('btn-primary active');
                lpbManualTable.draw();
            });

            <?php if (!empty($active_tab) && $active_tab === 'lpb-manual') : ?>
                $('#lpb-manual-tab').tab('show');
            <?php elseif (!empty($active_tab) && $active_tab === 'po-supplier' && $showLogistikPanel) : ?>
                $('#po-supplier-tab').tab('show');
            <?php endif; ?>
        });
    </script>
</body>
