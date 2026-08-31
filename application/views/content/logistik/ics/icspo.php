<!-- icspo.php -->
<?php
$lastSyncTime = !empty($last_sync['sync_time']) ? $last_sync['sync_time'] : '-';
$lastSyncInserted = isset($last_sync['inserted']) ? (int) $last_sync['inserted'] : 0;
$lastSyncUpdated = isset($last_sync['updated']) ? (int) $last_sync['updated'] : 0;
$lastSyncSkipped = isset($last_sync['skipped']) ? (int) $last_sync['skipped'] : 0;
$isAdminPo = !empty($is_admin_po);
$canSyncPo = !empty($can_sync_po);
$showLpbActions = !$isAdminPo && $this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') != 'ADMINICS';
$showLogistikPanel = !isset($show_logistik_panel) || !empty($show_logistik_panel);
$showPurchasingPanel = !isset($show_purchasing_panel) || !empty($show_purchasing_panel);
$canLpbManual = !empty($can_lpb_manual);
$canLpbReport = !empty($can_lpb_report);
$canViewLpbNominal = !isset($can_view_lpb_nominal) || !empty($can_view_lpb_nominal);
$isDataLpbPage = !empty($is_data_lpb_page);
$isAdmlpbUser = !empty($is_admlpb_user);
$hideSupplierCode = !empty($hide_lpb_supplier_code);
$hideLastInput = !empty($hide_lpb_last_input);
$lpbTableColspan = 10 - ($hideSupplierCode ? 1 : 0) - ($hideLastInput ? 1 : 0);
$showPanelTabs = $showLogistikPanel && $showPurchasingPanel;
$logistikPanelClass = $showPanelTabs ? 'tab-pane fade show active' : '';
$purchasingPanelClass = $showPanelTabs ? 'tab-pane fade' : '';
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
                    <?php if (!$isDataLpbPage && !$isAdminPo && $this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') != 'ADMINLOGLPB') : ?>
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
                                <a href="<?= base_url('ics/retur') ?>" class="btn btn-md btn-primary w-100 mb-3">
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
                                    <?php if ($canLpbManual && (!$isDataLpbPage || !$isAdmlpbUser)) : ?>
                                        <div class="col-md-2 col-sm-6 mb-2">
                                            <a class="btn btn-success btn-block" href="<?= base_url('ics/lpb_manual') ?>">
                                                <i class="fas fa-keyboard"></i> Input LPB Manual
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <a class="btn btn-warning btn-block" href="<?= base_url('ics/retur') ?>">
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
                                    <li class="nav-item">
                                        <a class="nav-link active" id="logistik-tab" data-toggle="tab" href="#logistik-panel" role="tab" aria-controls="logistik-panel" aria-selected="true">
                                            <i class="fas fa-file-invoice mr-1"></i> Data PO
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="purchasing-tab" data-toggle="tab" href="#purchasing-panel" role="tab" aria-controls="purchasing-panel" aria-selected="false">
                                            <i class="fas fa-clipboard-list mr-1"></i> Data LPB
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
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="<?= $lpbTableColspan ?>" class="text-center text-muted">
                                                        <i class="fas fa-inbox mr-1"></i> Tidak ada data
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
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
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="uang">
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
                                                            $isVerified = $progressStatus === 'done' || ($totalDetail > 0 && $totalVerified >= $totalDetail);
                                                            $invoiceBtnClass = $hasInvoice ? 'btn-success' : 'btn-light text-secondary border';
                                                            $fakturBtnClass = $hasFaktur ? 'btn-success' : 'btn-light text-secondary border';
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
                                                            $detailUrl = base_url('ics/detail_record_lpb?kd_po=' . urlencode($row['kd_po'] ?? '') . '&no_po=' . urlencode($row['no_po'] ?? '') . '&kd_suplier=' . urlencode($row['kd_suplier'] ?? ''));
                                                            $salesTitle = $hasSalesTransaction
                                                                ? 'Sudah ada ' . (int) ($row['sales_invoice_count'] ?? 0) . ' faktur penjualan: ' . (string) ($row['sales_invoice_sample'] ?? '-')
                                                                : 'Belum ada transaksi penjualan dari LPB ini';
                                                            $journalTitle = $hasActiveJournal
                                                                ? 'Jurnal LPB POSTED: ' . (string) ($row['lpb_journal_sample'] ?? '-')
                                                                : 'Belum ada jurnal LPB POSTED aktif';
                                                        ?>
                                                        <tr data-has-invoice="<?= $hasInvoice ? '1' : '0' ?>" data-has-faktur="<?= $hasFaktur ? '1' : '0' ?>" data-is-verified="<?= $isVerified ? '1' : '0' ?>" data-has-sales="<?= $hasSalesTransaction ? '1' : '0' ?>">
                                                            <td data-order="<?= htmlspecialchars($row['tgl_lpb'] ?? '') ?>"><?= htmlspecialchars($formatDate($row['tgl_lpb'] ?? '')) ?></td>
                                                            <td>
                                                                <a href="<?= $detailUrl ?>" class="font-weight-bold" target="_blank">
                                                                    <?= htmlspecialchars($row['nomor_lpb'] ?? '-') ?>
                                                                </a>
                                                            </td>
                                                            <td data-order="<?= htmlspecialchars($row['tgl_po'] ?? '') ?>"><?= htmlspecialchars($formatDate($row['tgl_po'] ?? '')) ?></td>
                                                            <td><?= htmlspecialchars($row['no_po'] ?? '') ?></td>
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
                                                                    <button type="button" class="btn btn-sm <?= $invoiceBtnClass ?>" title="<?= $hasInvoice ? 'Invoice sudah ada' : 'Invoice belum ada' ?>">
                                                                        <i class="fas fa-file-invoice"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm <?= $fakturBtnClass ?>" title="<?= $hasFaktur ? 'Pajak/Faktur sudah ada' : 'Pajak/Faktur belum ada' ?>">
                                                                        <i class="fas fa-percent"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm <?= $verifiedBtnClass ?>" title="<?= $isVerified ? 'Afirmasi harga selesai' : 'Afirmasi harga belum selesai' ?>">
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
                                                    <?php else : ?>
                                                        <tr>
                                                            <td colspan="12" class="text-center text-muted">
                                                                <i class="fas fa-inbox mr-1"></i> Tidak ada data purchasing
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endif; ?>
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
        $(document).ready(function() {
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
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

                var $row = $(settings.aoData[dataIndex].nTr);
                return String($row.data('lpb-status')) === lpbStatusFilter;
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

            $('#lpbStatusFilter').on('click', 'button[data-filter]', function() {
                lpbStatusFilter = $(this).data('filter') || 'all';
                $('#lpbStatusFilter button[data-filter]')
                    .removeClass('btn-primary active')
                    .addClass('btn-outline-secondary');
                $(this)
                    .removeClass('btn-outline-secondary')
                    .addClass('btn-primary active');
                lpbTable.draw();
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

                var $row = $(settings.aoData[dataIndex].nTr);

                if (purchasingStatusFilter === 'invoice') {
                    return String($row.data('has-invoice')) === '0';
                }

                if (purchasingStatusFilter === 'pajak') {
                    return String($row.data('has-faktur')) === '0';
                }

                if (purchasingStatusFilter === 'uang') {
                    return String($row.data('is-verified')) === '0';
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

            $('#purchasingStatusFilter').on('click', 'button[data-filter]', function() {
                purchasingStatusFilter = $(this).data('filter') || 'all';
                $('#purchasingStatusFilter button[data-filter]')
                    .removeClass('btn-primary active')
                    .addClass('btn-outline-secondary');
                $(this)
                    .removeClass('btn-outline-secondary')
                    .addClass('btn-primary active');
                purchasingTable.draw();
            });
            <?php endif; ?>
        });
    </script>
</body>
