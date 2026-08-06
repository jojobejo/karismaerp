<?php
$lpbRecordViewMode = $lpb_record_view_mode ?? (!empty($is_admin_po) ? 'purchasing' : 'logistik');
$showLpbListPanel = $lpbRecordViewMode === 'logistik';
?>
<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">
                    <style>
                        .lpb-hero {
                            background: linear-gradient(135deg, #243cff 0%, #3854ff 55%, #91a2ff 100%);
                            border-radius: 20px;
                            color: #fff;
                            padding: 24px;
                            box-shadow: 0 22px 44px rgba(36, 60, 255, 0.2);
                            overflow: hidden;
                            position: relative;
                        }

                        .lpb-hero::before,
                        .lpb-hero::after {
                            content: '';
                            position: absolute;
                            border-radius: 999px;
                            background: rgba(255, 255, 255, 0.08);
                        }

                        .lpb-hero::before {
                            width: 180px;
                            height: 180px;
                            top: -80px;
                            right: -40px;
                        }

                        .lpb-hero::after {
                            width: 120px;
                            height: 120px;
                            bottom: -40px;
                            left: -20px;
                        }

                        .lpb-stat-card,
                        .lpb-panel {
                            background: #fff;
                            border: 1px solid #dbe4ff;
                            border-radius: 18px;
                            box-shadow: 0 14px 32px rgba(36, 60, 255, 0.08);
                        }

                        .lpb-stat-card {
                            padding: 18px;
                            height: 100%;
                        }

                        .lpb-stat-label {
                            color: #64748b;
                            font-size: 12px;
                            font-weight: 700;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                        }

                        .lpb-stat-value {
                            color: #0f172a;
                            font-size: 28px;
                            font-weight: 800;
                            line-height: 1.1;
                            margin-top: 8px;
                        }

                        .lpb-panel-header {
                            padding: 18px 20px;
                            border-bottom: 1px solid #e2e8f0;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 12px;
                            flex-wrap: wrap;
                        }

                        .lpb-panel-title {
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            flex-wrap: wrap;
                        }

                        .lpb-panel-body {
                            padding: 18px 20px 20px;
                        }

                        .lpb-list-item {
                            border: 1px solid #e2e8f0;
                            border-radius: 10px;
                            padding: 8px 10px;
                            cursor: pointer;
                            transition: all 0.18s ease;
                            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
                        }

                        .lpb-list-item:hover {
                            transform: translateY(-1px);
                            border-color: #91a2ff;
                            box-shadow: 0 10px 22px rgba(36, 60, 255, 0.12);
                        }

                        .lpb-list-item.active {
                            border-color: #243cff;
                            background: linear-gradient(135deg, #eef1ff 0%, #f7f8ff 100%);
                            box-shadow: 0 12px 24px rgba(36, 60, 255, 0.15);
                        }

                        .lpb-list-meta {
                            display: flex;
                            align-items: center;
                            gap: 5px;
                            flex-wrap: wrap;
                            margin-top: 5px;
                        }

                        .lpb-list-meta .badge {
                            display: inline-flex;
                            align-items: center;
                            padding: 3px 7px;
                            font-size: 10px;
                        }

                        .lpb-list-badges {
                            display: flex;
                            align-items: flex-start;
                            justify-content: flex-end;
                            gap: 5px;
                            flex-wrap: wrap;
                            margin-left: 8px;
                        }

                        @media (min-width: 992px) {
                            .lpb-list-column {
                                flex: 0 0 22%;
                                max-width: 22%;
                            }

                            .lpb-detail-column {
                                flex: 0 0 78%;
                                max-width: 78%;
                            }
                        }

                        .lpb-chip {
                            display: inline-flex;
                            align-items: center;
                            gap: 5px;
                            background: #eef1ff;
                            color: #243cff;
                            border-radius: 999px;
                            padding: 2px 7px;
                            font-size: 10px;
                            font-weight: 700;
                        }

                        .lpb-chip.green {
                            background: #e8edff;
                            color: #3049ff;
                        }

                        .lpb-chip.slate {
                            background: #f3f5ff;
                            color: #46557e;
                        }

                        .lpb-detail-grid {
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                            gap: 8px;
                            margin-bottom: 10px;
                        }

                        .lpb-detail-box {
                            border: 1px solid #dbe4ff;
                            border-radius: 14px;
                            padding: 10px 12px;
                            background: #f6f8ff;
                        }

                        .lpb-detail-box .label {
                            font-size: 12px;
                            color: #64748b;
                            text-transform: uppercase;
                            letter-spacing: 0.06em;
                            font-weight: 700;
                        }

                        .lpb-detail-box .value {
                            margin-top: 4px;
                            font-size: 15px;
                            font-weight: 700;
                            color: #0f172a;
                            word-break: break-word;
                        }

                        .lpb-empty-state,
                        .lpb-loading-state {
                            border: 1px dashed #cbd5e1;
                            border-radius: 16px;
                            padding: 32px 18px;
                            text-align: center;
                            color: #64748b;
                            background: #f8fafc;
                        }

                        .lpb-table thead th {
                            background: #243cff;
                            color: #fff;
                            border: 1px solid #1726b8;
                            vertical-align: middle;
                            white-space: nowrap;
                        }

                        .lpb-table th,
                        .lpb-table td {
                            border: 1px solid #cbd5e1;
                            white-space: nowrap;
                        }

                        .lpb-table tfoot th {
                            background: #f3f6ff;
                            border-color: #dbe4ff;
                            color: #0f172a;
                            vertical-align: middle;
                        }

                        .lpb-table-actions {
                            display: flex;
                            justify-content: flex-end;
                            align-items: center;
                            gap: 8px;
                            flex-wrap: wrap;
                            margin-top: 12px;
                            width: 100%;
                        }

                        .lpb-table-actions .btn {
                            min-width: 132px;
                        }

                        .lpb-grand-total-harga {
                            display: flex;
                            justify-content: flex-end;
                            align-items: center;
                            gap: 16px;
                            flex-wrap: wrap;
                            margin-top: 10px;
                            padding: 12px 14px;
                            border: 1px solid #dbe4ff;
                            background: #f3f6ff;
                            color: #0f172a;
                            font-weight: 700;
                        }

                        .lpb-grand-total-harga .label {
                            color: #475569;
                            text-transform: uppercase;
                            letter-spacing: 0.04em;
                            font-size: 12px;
                        }

                        .lpb-grand-total-harga .value {
                            min-width: 190px;
                            text-align: right;
                            font-size: 16px;
                            font-weight: 800;
                        }

                        .lpb-split-invoice-row {
                            border: 1px solid #dbe4ff;
                            background: #f8fafc;
                            padding: 10px;
                            margin-bottom: 8px;
                        }

                        .lpb-split-table input[type="number"] {
                            min-width: 120px;
                        }

                        .lpb-invoice-overview .table th,
                        .lpb-invoice-overview .table td {
                            white-space: nowrap;
                            vertical-align: middle;
                        }

                        .lpb-invoice-overview .js-lpb-invoice-row {
                            cursor: pointer;
                        }

                        .lpb-invoice-overview .js-lpb-invoice-row.active {
                            background: #eef6ff;
                        }

                        .lpb-workflow-actions {
                            margin-left: auto;
                        }

                        .lpb-workflow-actions .btn {
                            min-width: auto;
                        }

                        .lpb-post-actions {
                            justify-content: flex-end;
                            border-top: 1px solid #e2e8f0;
                            padding-top: 12px;
                        }

                        .lpb-post-actions .btn-workflow-main {
                            min-width: 104px;
                        }

                        .lpb-activity-log {
                            border-top: 1px solid #e2e8f0;
                            margin-top: 14px;
                            padding-top: 14px;
                        }

                        .lpb-activity-log .table th,
                        .lpb-activity-log .table td {
                            white-space: nowrap;
                            vertical-align: middle;
                        }

                        .lpb-activity-log .activity-note {
                            min-width: 280px;
                            white-space: normal;
                        }

                        #modalHistoryLpb .table th,
                        #modalHistoryLpb .table td {
                            white-space: nowrap;
                        }

                        #modalHistoryLpb .table td.history-diskon-keterangan {
                            min-width: 360px;
                            white-space: normal;
                        }

                    </style>

                    <div class="row mb-3">
                        <div class="col-auto">
                            <a href="<?= $showLpbListPanel ? base_url('ics/detail_po?no_po=' . urlencode($no_po ?? '') . '&kd_suplier=' . urlencode($kd_suplier ?? '')) : base_url('ics/icspo') ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left mr-1"></i> <?= $showLpbListPanel ? 'Kembali ke Detail PO' : 'Kembali ke Data PO' ?>
                            </a>
                        </div>
                    </div>

                    <div class="lpb-hero mb-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <?php if ($showLpbListPanel) : ?>
                                <h3 class="mb-1 font-weight-bold">Record Semua Data LPB</h3>
                                <?php else : ?>
                                <h3 class="mb-1 font-weight-bold">Nomor PO</h3>
                                <div class="h3 font-weight-bold mb-1"><?= htmlspecialchars($no_po ?? '-') ?></div>
                                <div class="small">PO Komersil : <?= htmlspecialchars($kd_po ?? '-') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                                <button type="button" class="btn btn-success mb-2" id="btnPrintAllLpb">
                                    <i class="fas fa-print mr-1"></i> Cetak Semua Faktur LPB
                                </button>
                                <?php if ($showLpbListPanel) : ?>
                                <br>
                                <div class="h3 font-weight-bold mb-1"><?= htmlspecialchars($no_po ?? '-') ?></div>
                                <div class="small">No PO: <?= htmlspecialchars($kd_po ?? '-') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!$showLpbListPanel) : ?>
                    <div class="d-none" aria-hidden="true">
                        <input type="text" id="lpbSearchInput" value="">
                        <div id="lpbListLoading"></div>
                        <div id="lpbListEmpty"></div>
                        <div id="lpbListWrap">
                            <div id="lpbListContainer"></div>
                        </div>
                        <div id="prePoAdjustmentLoading"></div>
                        <div id="prePoAdjustmentContainer"></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!$showLpbListPanel) : ?>
                    <div class="lpb-panel lpb-invoice-overview mb-4">
                        <div class="lpb-panel-header">
                            <div>
                                <h3 class="card-title mb-0 font-weight-bold">List Invoice LPB per Nomor LPB</h3>
                            </div>
                            <span class="badge badge-light" id="lpbInvoiceOverviewCount">0 invoice</span>
                        </div>
                        <div class="lpb-panel-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover mb-0" id="lpbInvoiceOverviewTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Nomor LPB</th>
                                            <th>Invoice</th>
                                            <th class="text-center">Tanggal Invoice</th>
                                            <th class="text-center">Faktur Pajak</th>
                                            <th class="text-right">Total Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Memuat list invoice LPB...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <?php if ($showLpbListPanel) : ?>
                        <div class="col-lg-3 lpb-list-column mb-4">
                            <div class="lpb-panel h-100">
                                <div class="lpb-panel-header">
                                    <div>
                                        <h3 class="card-title mb-0 font-weight-bold">Daftar LPB</h3>
                                    </div>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="btnReloadLpbPage">
                                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                                    </button>
                                </div>
                                <div class="lpb-panel-body">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="lpbSearchInput" placeholder="Cari invoice / faktur pajak / jenis LPB / nomor SJ">
                                    </div>

                                    <div id="lpbListLoading" class="lpb-loading-state">
                                        <i class="fas fa-spinner fa-spin fa-2x text-success mb-2"></i>
                                        <div>Memuat daftar LPB...</div>
                                    </div>

                                    <div id="lpbListEmpty" class="lpb-empty-state" style="display:none;">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <div>Belum ada record LPB untuk KD PO ini.</div>
                                    </div>

                                    <div id="lpbListWrap" style="display:none;">
                                        <div id="lpbListContainer" class="d-flex flex-column" style="gap:6px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="<?= $showLpbListPanel ? 'col-lg-9 lpb-detail-column' : 'col-lg-12' ?> mb-4">
                            <div class="lpb-panel h-100">
                                <div class="lpb-panel-header">
                                    <div class="lpb-panel-title">
                                        <h3 class="card-title mb-0 font-weight-bold">Detail LPB</h3>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap lpb-workflow-actions" id="lpbWorkflowActions" style="gap:8px; display:none;">
                                        <?php if (!$showLpbListPanel) : ?>
                                        <button type="button" class="btn btn-primary btn-sm" id="btnUpdateInvoice">
                                            <i class="fas fa-file-invoice mr-1"></i> Update Invoice
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" id="btnSplitInvoice">
                                            <i class="fas fa-code-branch mr-1"></i> Pecah Invoice
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm" id="btnUpdateFaktur">
                                            <i class="fas fa-receipt mr-1"></i> Update Faktur
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm" id="btnToggleLpbLog" title="Tampilkan log aktivitas detail PO">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnPrintSelectedLpb">
                                            <i class="fas fa-print mr-1"></i> Cetak Faktur LPB
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($showLpbListPanel) : ?>
                                        <button type="button" class="btn btn-primary btn-sm" id="btnUpdateLpbIdentity">
                                            <i class="fas fa-edit mr-1"></i> Update Nomor & Jenis LPB
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm" id="btnUpdateLpbSj">
                                            <i class="fas fa-truck-loading mr-1"></i> Update SJ
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnPrintSelectedLpb">
                                            <i class="fas fa-print mr-1"></i> Cetak Faktur LPB
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" id="btnToggleLpbLog" title="Tampilkan log aktivitas">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php endif; ?>
                                        <div class="text-muted small d-none" id="selectedLpbText">Belum ada LPB dipilih</div>
                                    </div>
                                </div>
                                <div class="lpb-panel-body">
                                    <div id="lpbDetailLoading" class="lpb-loading-state" style="display:none;">
                                        <i class="fas fa-spinner fa-spin fa-2x text-success mb-2"></i>
                                        <div>Memuat detail LPB...</div>
                                    </div>

                                    <div id="lpbDetailEmpty" class="lpb-empty-state">
                                        <i class="fas fa-receipt fa-2x mb-2"></i>
                                        <div><?= $showLpbListPanel ? 'Pilih salah satu LPB di panel kiri untuk melihat detailnya.' : 'Detail LPB akan tampil otomatis setelah data LPB dimuat.' ?></div>
                                    </div>

                                    <div id="lpbDetailWrap" style="display:none;">
                                        <div class="lpb-detail-grid" id="lpbDetailHeaderGrid"></div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover lpb-table" id="lpbDetailTable">
                                                <thead>
                                                    <tr>
                                                        <?php if ($showLpbListPanel) : ?>
                                                        <th class="text-center" rowspan="2">#</th>
                                                        <?php endif; ?>
                                                        <th rowspan="2">Kode Barang</th>
                                                        <th rowspan="2">Nama Barang</th>
                                                        <th class="text-center" rowspan="2">No Lot</th>
                                                        <th class="text-center" rowspan="2">Expired Date</th>
                                                        <th class="text-center" rowspan="2">Qty In</th>
                                                        <th class="text-center" colspan="2">Qty Satuan</th>
                                                        <?php if (!$showLpbListPanel) : ?>
                                                        <th class="text-right" rowspan="2">Harga Satuan</th>
                                                        <th class="text-right" rowspan="2">DPP</th>
                                                        <th class="text-right" rowspan="2">DPP Nilai Lain</th>
                                                        <th class="text-right" rowspan="2">PPN</th>
                                                        <th class="text-right" rowspan="2">Total Harga</th>
                                                        <th class="text-center" rowspan="2">#</th>
                                                        <?php endif; ?>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-center">BOX</th>
                                                        <th class="text-center">Kg/Ltr</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                        <div class="lpb-grand-total-harga" id="lpbGrandTotalHargaWrap" style="display:none;">
                                            <span class="label">Total DPP</span>
                                            <span class="value" id="lpbTotalDpp">Rp 0</span>
                                            <span class="label">Grand Total Harga</span>
                                            <span class="value" id="lpbGrandTotalHarga">Rp 0</span>
                                        </div>
                                        <?php if ($showLpbListPanel) : ?>
                                        <div class="lpb-table-actions lpb-post-actions" id="lpbPostActions" style="display:none;">
                                            <button type="button" class="btn btn-danger btn-sm btn-workflow-main" id="btnUnpostLpb">
                                                <i class="fas fa-undo mr-1"></i> UNPOST
                                            </button>
                                            <button type="button" class="btn btn-success btn-sm btn-workflow-main" id="btnPostLpb">
                                                <i class="fas fa-save mr-1"></i> Rekam
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                        <div class="lpb-table-actions" id="lpbPurchasingVerifyActions" style="display:none;">
                                            <span class="text-muted small" id="lpbBulkVerifyInfo"></span>
                                            <button type="button" class="btn btn-danger btn-sm" id="btnPurchasingUnpostLpb" style="display:none;">
                                                <i class="fas fa-undo mr-1"></i> UNPOST
                                            </button>
                                            <button type="button" class="btn btn-success btn-sm ml-2" id="btnPurchasingPostLpb" style="display:none;">
                                                <i class="fas fa-save mr-1"></i> Posting Data
                                            </button>
                                        </div>
                                        <div class="lpb-activity-log" id="lpbActivityLogWrap" style="display:none;">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="font-weight-bold mb-0">Log Aktivitas Detail PO</h6>
                                                <span class="badge badge-light" id="lpbActivityLogCount">0 aktivitas</span>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Waktu</th>
                                                            <th>User</th>
                                                            <th>Checker</th>
                                                            <th>Aktivitas</th>
                                                            <th>Status</th>
                                                            <th>Keterangan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="lpbActivityLogBody">
                                                        <tr><td colspan="6" class="text-center text-muted">Belum ada aktivitas.</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
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
        </footer>
        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <?php if (!empty($is_admin_po)) : ?>
    <div class="modal fade" id="modalAdjustmentHarga" tabindex="-1" role="dialog" aria-labelledby="modalAdjustmentHargaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="formAdjustmentHarga" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAdjustmentHargaLabel">Adjustment Harga</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="adjKdBarang" name="kd_barang">
                    <div class="form-group">
                        <label>Barang</label>
                        <input type="text" class="form-control" id="adjNamaBarang" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Harga Lama</label>
                                <input type="text" class="form-control" id="adjHargaLamaText" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Harga Baru</label>
                                <input type="number" min="0" step="1" class="form-control" id="adjHargaBaru" name="harga_satuan_baru" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Qty</label>
                                <input type="number" class="form-control" id="adjQty" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Total Baru</label>
                                <input type="text" class="form-control" id="adjTotalBaruText" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Alasan Adjustment</label>
                        <textarea class="form-control" id="adjAlasan" name="alasan" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btnSubmitAdjustment">
                        <i class="fas fa-save mr-1"></i> Simpan Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalHistoryLpb" tabindex="-1" role="dialog" aria-labelledby="modalHistoryLpbLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHistoryLpbLabel">History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="historyLpbLoading" class="lpb-loading-state" style="display:none;">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                        <div>Memuat history...</div>
                    </div>
                    <div id="historyLpbContent"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="modal fade" id="modalUpdateInvoice" tabindex="-1" role="dialog" aria-labelledby="modalUpdateInvoiceLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="formUpdateInvoice" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUpdateInvoiceLabel">Update Invoice</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="invoiceIdLpb" name="id_lpb">
                    <div class="form-group">
                        <label>No Invoice</label>
                        <input type="text" class="form-control" id="invoiceNo" name="no_invoice" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Tanggal Terbit Invoice</label>
                        <input type="date" class="form-control" id="invoiceTanggalTerbit" name="tanggal_invoice" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitInvoice">
                        <i class="fas fa-save mr-1"></i> Simpan Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalSplitLpbDetail" tabindex="-1" role="dialog" aria-labelledby="modalSplitLpbDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="formSplitLpbDetail" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSplitLpbDetailLabel">Split Qty dan Harga Barang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="splitDetailIdDetail" name="id_detail_lpb">
                    <input type="hidden" id="splitDetailQtyAwalRaw">
                    <input type="hidden" id="splitDetailHargaAwalRaw">
                    <input type="hidden" id="splitDetailTotalAwalRaw">
                    <input type="hidden" id="splitDetailKdBarang">
                    <input type="hidden" id="splitDetailNamaBarang">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2" style="gap:8px;">
                        <div>
                            <div class="text-muted small">Data Acuan</div>
                            <div class="font-weight-bold" id="splitDetailBarang">-</div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddSplitDetailRow">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Qty In Awal</label>
                                <input type="text" class="form-control" id="splitDetailQtyAwal" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Harga Satuan Awal</label>
                                <input type="text" class="form-control" id="splitDetailHargaAwal" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Total Harga Awal</label>
                                <input type="text" class="form-control" id="splitDetailTotalAwal" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Keterangan</label>
                                <input type="text" class="form-control" id="splitDetailKeterangan" name="keterangan" placeholder="Keterangan split">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm lpb-table mb-2" id="splitDetailTable">
                            <thead>
                                <tr>
                                    <th style="min-width:160px;">Baris</th>
                                    <th class="text-right" style="min-width:150px;">Qty</th>
                                    <th class="text-right" style="min-width:170px;">Harga Satuan</th>
                                    <th class="text-right" style="min-width:170px;">Total</th>
                                    <th class="text-center" style="width:70px;">#</th>
                                </tr>
                            </thead>
                            <tbody id="splitDetailRows"></tbody>
                            <tfoot>
                                <tr>
                                    <th>Total Input</th>
                                    <th class="text-right" id="splitDetailTotalQtyInput">0</th>
                                    <th></th>
                                    <th class="text-right" id="splitDetailTotalHargaInput">Rp 0</th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th>Selisih</th>
                                    <th class="text-right" id="splitDetailDiffQty">0</th>
                                    <th></th>
                                    <th class="text-right" id="splitDetailDiffHarga">Rp 0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="alert alert-info mb-0" id="splitDetailInfo">
                        Total qty seluruh baris harus sama dengan data acuan. Selisih pada harga satuan diabaikan dan data disimpan sesuai inputan form.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btnSubmitSplitDetail">
                        <i class="fas fa-code-branch mr-1"></i> Simpan Split
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalSplitInvoice" tabindex="-1" role="dialog" aria-labelledby="modalSplitInvoiceLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <form id="formSplitInvoice" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSplitInvoiceLabel">Pecah LPB Multiple Invoice</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="splitInvoiceIdLpb" name="id_lpb">
                    <div class="alert alert-info">
                        Invoice pertama tetap memakai LPB yang dipilih. Invoice berikutnya dibuat sebagai LPB baru dengan nomor LPB yang sama.
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2" style="gap:8px;">
                        <h6 class="font-weight-bold mb-0">Daftar Invoice</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddSplitInvoice">
                            <i class="fas fa-plus mr-1"></i> Tambah Invoice
                        </button>
                    </div>
                    <div id="splitInvoiceRows"></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered lpb-table lpb-split-table mb-0" id="splitInvoiceAllocationTable">
                            <thead></thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                    <div class="small text-muted mt-2" id="splitInvoiceValidationInfo"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btnSubmitSplitInvoice">
                        <i class="fas fa-save mr-1"></i> Simpan Split Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalUpdateFaktur" tabindex="-1" role="dialog" aria-labelledby="modalUpdateFakturLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="formUpdateFaktur" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUpdateFakturLabel">Update Faktur Pajak</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="fakturIdLpb" name="id_lpb">
                    <div class="form-group">
                        <label>Kode Faktur Pajak</label>
                        <input type="text" class="form-control" id="fakturKodePajak" name="kode_faktur_pajak" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Tanggal Terbit Faktur Pajak</label>
                        <input type="date" class="form-control" id="fakturTanggalTerbit" name="tanggal_faktur_pajak" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info" id="btnSubmitFaktur">
                        <i class="fas fa-save mr-1"></i> Simpan Faktur
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalUpdateJenisLpb" tabindex="-1" role="dialog" aria-labelledby="modalUpdateJenisLpbLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="formUpdateJenisLpb" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUpdateJenisLpbLabel">Update Nomor dan Jenis LPB</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="jenisIdLpb" name="id_lpb">
                    <div class="form-group">
                        <label>Nomor LPB</label>
                        <input type="text" class="form-control" id="jenisNomorLpb" name="nomor_lpb" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis LPB</label>
                        <select class="form-control" id="jenisLpbSelect" name="jenis_lpb" required>
                            <?php foreach (($lpb_type_options ?? []) as $typeKey => $typeInfo) : ?>
                                <option value="<?= htmlspecialchars($typeKey, ENT_QUOTES) ?>" data-example="<?= htmlspecialchars($typeInfo['example'] ?? '', ENT_QUOTES) ?>">
                                    <?= htmlspecialchars($typeInfo['label'] ?? $typeKey) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info mb-0" id="jenisLpbFormatInfo">
                        Format mengikuti bulan, tahun, dan urutan berdasarkan jenis yang dipilih.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitJenisLpb">
                        <i class="fas fa-save mr-1"></i> Simpan LPB
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalUpdateLpbSj" tabindex="-1" role="dialog" aria-labelledby="modalUpdateLpbSjLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="formUpdateLpbSj" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUpdateLpbSjLabel">Update Nomor SJ dan Tanggal SJ</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="sjIdLpb" name="id_lpb">
                    <div class="form-group">
                        <label>Nomor Surat Jalan</label>
                        <input type="text" class="form-control" id="sjNomor" name="nosj" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Tanggal Surat Jalan</label>
                        <input type="date" class="form-control" id="sjTanggal" name="tgl_sj" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info" id="btnSubmitLpbSj">
                        <i class="fas fa-save mr-1"></i> Simpan SJ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalUpdateLpbDetailReceipt" tabindex="-1" role="dialog" aria-labelledby="modalUpdateLpbDetailReceiptLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="formUpdateLpbDetailReceipt" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUpdateLpbDetailReceiptLabel">Edit Detail LPB</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="detailReceiptIdDetail" name="id_detail_lpb">
                    <div class="form-group">
                        <label>Barang</label>
                        <input type="text" class="form-control" id="detailReceiptBarang" readonly>
                    </div>
                    <div class="form-group">
                        <label>No Lot</label>
                        <input type="text" class="form-control" id="detailReceiptNoLot" name="no_lot" required>
                    </div>
                    <div class="form-group">
                        <label>Expired Date</label>
                        <input type="date" class="form-control" id="detailReceiptExpiredDate" name="expired_date" required>
                    </div>
                    <div class="form-group mb-0">
                        <label>Qty Diterima</label>
                        <input type="number" min="0.0001" step="0.0001" class="form-control" id="detailReceiptQty" name="qty_diterima" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning" id="btnSubmitLpbDetailReceipt">
                        <i class="fas fa-save mr-1"></i> Simpan Detail
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalUpdateLpbPrice" tabindex="-1" role="dialog" aria-labelledby="modalUpdateLpbPriceLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="formUpdateLpbPrice" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUpdateLpbPriceLabel">Update Harga Detail LPB</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="lpbPriceIdDetail" name="id_detail_lpb">
                    <div class="form-group">
                        <label>Kode Barang - Nama Barang</label>
                        <input type="text" class="form-control" id="lpbPriceBarang" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Harga Satuan Sebelumnya</label>
                                <input type="text" class="form-control" id="lpbPriceHargaSebelumnya" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Total Harga Sebelumnya</label>
                                <input type="text" class="form-control" id="lpbPriceTotalSebelumnya" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Qty LPB</label>
                                <input type="number" min="0.0001" step="0.0001" class="form-control" id="lpbPriceQty" name="qty_lpb" required>
                                <input type="hidden" id="lpbPriceQtyMax">
                                <input type="hidden" id="lpbPriceQtyOrder">
                                <small class="form-text text-muted" id="lpbPriceQtyInfo"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Harga Satuan Baru</label>
                                <input type="number" min="0" step="0.0001" class="form-control" id="lpbPriceHargaBaru" name="harga_satuan_baru" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Total Harga Baru</label>
                                <input type="text" class="form-control" id="lpbPriceTotalBaruText" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning mb-0">
                        Harga dan Qty LPB yang tersimpan saat ini akan diperbarui. Jika total Qty LPB tidak balance dengan qty yang diterima, sistem akan memberi notifikasi.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnSubmitLpbPrice">
                        <i class="fas fa-save mr-1"></i> Simpan Harga & Qty
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function() {
            var kdPo = '<?= htmlspecialchars($kd_po ?? '', ENT_QUOTES) ?>';
            var canManagePoInvoice = <?= !empty($is_admin_po) ? 'true' : 'false' ?>;
            var showLpbListPanel = <?= $showLpbListPanel ? 'true' : 'false' ?>;
            var showPrePoAdjustmentPanel = false;
            var allRows = [];
            var allInvoiceRows = [];
            var selectedIdLpb = <?= (int) ($initial_id_lpb ?? 0) ?>;
            var scopedIdLpb = selectedIdLpb;
            var selectedHeader = null;
            var selectedDetailRows = [];
            var selectedPurchasingRows = [];
            var selectedActivityLogs = [];
            var detailViewMode = 'lpb';
            var purchasingEditMode = false;
            var isActivityLogVisible = false;
            var isSubmittingAdjustment = false;
            var isSubmittingInvoice = false;
            var isSubmittingSplitInvoice = false;
            var isSubmittingFaktur = false;
            var isSubmittingJenisLpb = false;
            var isSubmittingLpbPrice = false;
            var isSubmittingLpbDetailSplit = false;
            var isSubmittingLpbSj = false;
            var isSubmittingLpbDetailReceipt = false;
            var isChangingLpbStatus = false;
            var isAcceptingLpbPrice = false;
            var isBulkAcceptingLpbPrice = false;
            var splitDetailRowCounter = 0;

            function escHtml(value) {
                return $('<div>').text(value == null ? '' : value).html();
            }

            function escAttr(value) {
                return escHtml(value).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            function formatNumber(value) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                }).format(parseFloat(value) || 0);
            }

            function formatRupiah(value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(parseFloat(value) || 0);
            }

            function formatDateId(value) {
                var raw = (value || '').toString().trim();
                var match = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (!match) {
                    return raw || '-';
                }

                return match[3] + '/' + match[2] + '/' + match[1];
            }

            function formatDateTimeId(value) {
                var raw = (value || '').toString().trim();
                var match = raw.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
                if (!match) {
                    return raw || '-';
                }

                return match[3] + '/' + match[2] + '/' + match[1] + (match[4] ? ' ' + match[4] + ':' + match[5] : '');
            }

            function hasInvoice(value) {
                var invoice = (value || '').toString().trim();
                return invoice !== '' && invoice !== '-';
            }

            function lpbStatusInfo(status) {
                var code = parseInt(status, 10);
                if (code === 0) {
                    return {
                        label: 'UNPOST',
                        badge: 'badge-warning'
                    };
                }

                return {
                    label: 'POST',
                    badge: 'badge-success'
                };
            }

            function isSelectedLpbUnpost() {
                return parseInt((selectedHeader || {}).status_lpb, 10) === 0;
            }

            function isSelectedLpbPost() {
                return !!selectedHeader && !isSelectedLpbUnpost();
            }

            function loadPrePoAdjustment() {
                if (!canManagePoInvoice || !showPrePoAdjustmentPanel) {
                    return;
                }

                $('#prePoAdjustmentLoading').show();
                $('#prePoAdjustmentContainer').hide().empty();

                $.ajax({
                    url: '<?= base_url('ics/ajax_get_pre_po_adjustment') ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        kd_po: kdPo
                    },
                    success: function(res) {
                        $('#prePoAdjustmentLoading').hide();
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Data PRE PO tidak dapat dimuat.', 'error');
                            return;
                        }

                        $('#prePoAdjustmentContainer').html(res.html || '').show();
                    },
                    error: function() {
                        $('#prePoAdjustmentLoading').hide();
                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengambil data PRE PO.', 'error');
                    }
                });
            }

            function refreshPrePoAdjustmentHtml(html) {
                if (!showPrePoAdjustmentPanel) {
                    return;
                }

                if (html) {
                    $('#prePoAdjustmentContainer').html(html).show();
                    $('#prePoAdjustmentLoading').hide();
                    return;
                }

                loadPrePoAdjustment();
            }

            function updateAdjustmentTotal() {
                var qty = parseFloat($('#adjQty').val()) || 0;
                var hargaBaru = parseFloat($('#adjHargaBaru').val()) || 0;
                $('#adjTotalBaruText').val(formatRupiah(qty * hargaBaru));
            }

            function updateLpbPriceTotal() {
                var qty = parseFloat($('#lpbPriceQty').val()) || 0;
                var hargaBaru = parseFloat($('#lpbPriceHargaBaru').val()) || 0;
                $('#lpbPriceTotalBaruText').val(formatRupiah(qty * hargaBaru));
            }

            function addSplitDetailRow(label, qty, harga, removable) {
                splitDetailRowCounter++;
                var rowId = splitDetailRowCounter;
                var rowHtml = '' +
                    '<tr class="js-split-detail-row" data-row-id="' + rowId + '">' +
                    '<td class="align-middle">' +
                    '<input type="text" class="form-control form-control-sm js-split-detail-label" value="' + escAttr(label || ('Split ' + rowId)) + '" readonly>' +
                    '</td>' +
                    '<td><input type="number" min="0.0001" step="0.0001" class="form-control form-control-sm text-right js-split-detail-qty" value="' + escAttr(formatInputNumber(qty || 0)) + '" required></td>' +
                    '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm text-right js-split-detail-harga" value="' + escAttr(harga || 0) + '" required></td>' +
                    '<td class="text-right align-middle js-split-detail-total">Rp 0</td>' +
                    '<td class="text-center align-middle">' +
                    (removable ? '<button type="button" class="btn btn-outline-danger btn-sm js-remove-split-detail-row" title="Hapus baris"><i class="fas fa-trash"></i></button>' : '') +
                    '</td>' +
                    '</tr>';
                $('#splitDetailRows').append(rowHtml);
                updateSplitDetailPreview();
            }

            function collectSplitDetailRows() {
                var rows = [];
                $('#splitDetailRows .js-split-detail-row').each(function(index) {
                    var qty = parseFloat($(this).find('.js-split-detail-qty').val()) || 0;
                    var harga = parseFloat($(this).find('.js-split-detail-harga').val()) || 0;
                    rows.push({
                        label: $(this).find('.js-split-detail-label').val() || ('Baris ' + (index + 1)),
                        qty: qty,
                        harga_satuan: harga,
                        total_harga: qty * harga
                    });
                });
                return rows;
            }

            function updateSplitDetailPreview() {
                var qtyAwal = parseFloat($('#splitDetailQtyAwalRaw').val()) || 0;
                var totalAwal = parseFloat($('#splitDetailTotalAwalRaw').val()) || 0;
                var rows = collectSplitDetailRows();
                var totalQtyInput = 0;
                var totalHargaInput = 0;
                var message = 'Total qty seluruh baris sudah sama dengan data acuan. Selisih pada harga satuan diabaikan dan data disimpan sesuai inputan form.';
                var hasError = false;

                $.each(rows, function(_, row) {
                    totalQtyInput += row.qty;
                    totalHargaInput += row.total_harga;
                });

                $('#splitDetailRows .js-split-detail-row').each(function() {
                    var qty = parseFloat($(this).find('.js-split-detail-qty').val()) || 0;
                    var harga = parseFloat($(this).find('.js-split-detail-harga').val()) || 0;
                    $(this).find('.js-split-detail-total').text(formatRupiah(qty * harga));
                });

                var diffQty = qtyAwal - totalQtyInput;
                var diffHarga = totalAwal - totalHargaInput;

                if (rows.length < 2) {
                    message = 'Minimal harus ada 2 baris hasil split.';
                    hasError = true;
                } else if ($.grep(rows, function(row) { return row.qty <= 0; }).length > 0) {
                    message = 'Setiap baris hasil split harus memiliki qty lebih dari 0.';
                    hasError = true;
                } else if ($.grep(rows, function(row) { return row.harga_satuan < 0; }).length > 0) {
                    message = 'Harga satuan tidak boleh minus.';
                    hasError = true;
                } else if (Math.abs(diffQty) > 0.0001) {
                    message = 'Total qty seluruh baris harus sama dengan Qty In awal.';
                    hasError = true;
                }

                $('#splitDetailTotalQtyInput').text(formatNumber(totalQtyInput));
                $('#splitDetailTotalHargaInput').text(formatRupiah(totalHargaInput));
                $('#splitDetailDiffQty')
                    .toggleClass('text-danger font-weight-bold', Math.abs(diffQty) > 0.0001)
                    .text(formatNumber(diffQty));
                $('#splitDetailDiffHarga')
                    .removeClass('text-warning font-weight-bold')
                    .text(formatRupiah(diffHarga));
                $('#splitDetailInfo')
                    .toggleClass('alert-danger', hasError)
                    .removeClass('alert-warning')
                    .toggleClass('alert-info', !hasError)
                    .text(message);

                return hasError ? message : '';
            }

            function openSplitLpbDetailModal(btn) {
                if (!isSelectedLpbUnpost()) {
                    Swal.fire('Validasi', 'Split detail LPB hanya bisa dilakukan saat status UNPOST.', 'warning');
                    return;
                }

                var qtyAwal = parseFloat(btn.attr('data-qty') || 0) || 0;
                var hargaAwal = parseFloat(btn.attr('data-harga-satuan') || 0) || 0;
                var totalAwal = parseFloat(btn.attr('data-total-harga') || 0) || 0;
                if (totalAwal <= 0 && qtyAwal > 0) {
                    totalAwal = qtyAwal * hargaAwal;
                }

                if (qtyAwal <= 0) {
                    Swal.fire('Validasi', 'Qty In detail LPB tidak valid untuk di-split.', 'warning');
                    return;
                }

                $('#splitDetailIdDetail').val(btn.attr('data-id-detail') || 0);
                $('#splitDetailKdBarang').val(btn.attr('data-kd-barang') || '');
                $('#splitDetailNamaBarang').val(btn.attr('data-nama-barang') || '-');
                $('#modalSplitLpbDetailLabel').text('Split Qty dan Harga Barang - ' + (btn.attr('data-nama-barang') || '-'));
                $('#splitDetailBarang').text((btn.attr('data-kd-barang') || '-') + ' - ' + (btn.attr('data-nama-barang') || '-'));
                $('#splitDetailQtyAwalRaw').val(qtyAwal);
                $('#splitDetailHargaAwalRaw').val(hargaAwal);
                $('#splitDetailTotalAwalRaw').val(totalAwal);
                $('#splitDetailQtyAwal').val(formatNumber(qtyAwal));
                $('#splitDetailHargaAwal').val(formatRupiah(hargaAwal));
                $('#splitDetailTotalAwal').val(formatRupiah(totalAwal));
                $('#splitDetailKeterangan').val('');
                $('#splitDetailRows').empty();
                splitDetailRowCounter = 0;
                addSplitDetailRow('Data Sekarang', qtyAwal, hargaAwal, false);
                addSplitDetailRow('Split 1', 0, hargaAwal, true);
                updateSplitDetailPreview();
                $('#modalSplitLpbDetail').modal('show');
            }

            function validateLpbPriceQtyInput() {
                var qty = parseFloat($('#lpbPriceQty').val()) || 0;
                var qtyMax = parseFloat($('#lpbPriceQtyMax').val()) || 0;

                if (qty <= 0) {
                    return 'Qty LPB harus lebih dari 0.';
                }

                if (qtyMax > 0 && qty > qtyMax + 0.0001) {
                    return 'Qty LPB tidak boleh melebihi maksimum ' + formatNumber(qtyMax) + ' untuk kode barang ini.';
                }

                return '';
            }

            function updateStats(rows) {
                var totalItem = 0;
                var totalQty = 0;

                $.each(rows || [], function(_, row) {
                    totalItem += parseFloat(row.total_item) || 0;
                    totalQty += parseFloat(row.total_qty) || 0;
                });

                $('#statTotalLpb').text(formatNumber((rows || []).length));
                $('#statTotalItem').text(formatNumber(totalItem));
                $('#statTotalQty').text(formatNumber(totalQty));
            }

            function buildListItem(row) {
                var invoice = hasInvoice(row.no_invoice) ? row.no_invoice : 'Tidak ada invoice';
                var invoiceBadge = hasInvoice(row.no_invoice) ?
                    '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Ada invoice</span>' :
                    '<span class="badge badge-danger"><i class="fas fa-exclamation-circle mr-1"></i>Tidak ada invoice</span>';
                var jenisDitentukan = !!row.jenis_lpb;
                var jenisLpb = jenisDitentukan ? row.jenis_lpb : 'Jenis LPB belum ditentukan';
                var statusInfo = lpbStatusInfo(row.status_lpb);
                var statusBadge = '<span class="badge ' + statusInfo.badge + '"><i class="fas fa-tasks mr-1"></i>' + escHtml(statusInfo.label) + '</span>';
                var nomorLpb = row.nomor_lpb ? row.nomor_lpb : 'Nomor LPB belum dibuat';
                var kodeFaktur = row.kode_faktur_pajak ? row.kode_faktur_pajak : 'Faktur pajak belum ada';
                var nomorSj = row.nosj ? row.nosj : 'SJ belum ada';

                return '' +
                    '<div class="lpb-list-item js-lpb-item" data-id="' + escAttr(row.id_lpb) + '" data-search="' + escAttr((nomorLpb + ' ' + invoice + ' ' + kodeFaktur + ' ' + jenisLpb + ' status ' + (row.status_lpb == null ? 1 : row.status_lpb) + ' ' + nomorSj).toLowerCase()) + '">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                    '<div>' +
                    '<div class="font-weight-bold text-dark">' + escHtml(nomorLpb) + '</div>' +
                    '</div>' +
                    '<div class="lpb-list-badges">' + statusBadge + '</div>' +
                    '</div>' +
                    '<div class="lpb-list-meta">' +
                    invoiceBadge +
                    '<span class="lpb-chip"><i class="fas fa-tags"></i> ' + escHtml(jenisLpb) + '</span>' +
                    '<span class="lpb-chip green"><i class="fas fa-receipt"></i> ' + escHtml(kodeFaktur) + '</span>' +
                    '<span class="lpb-chip slate"><i class="fas fa-clock"></i> ' + escHtml(formatDateTimeId(row.input_at)) + '</span>' +
                    '</div>' +
                    '<div class="text-muted small mt-2">' + escHtml(nomorSj) + ' | ' + escHtml(formatDateId(row.tgl_sj)) + '</div>' +
                    '</div>';
            }

            function renderInvoiceOverview(rows) {
                if (showLpbListPanel) {
                    return;
                }

                var tbody = $('#lpbInvoiceOverviewTable tbody');
                tbody.empty();
                var lpbNumbers = {};
                var invoiceCount = 0;

                $.each(rows || [], function(_, row) {
                    if (row.nomor_lpb) {
                        lpbNumbers[row.nomor_lpb] = true;
                    }
                    if (hasInvoice(row.no_invoice)) {
                        invoiceCount++;
                    }
                });

                $('#lpbInvoiceOverviewCount').text(
                    formatNumber(invoiceCount) + ' invoice / ' +
                    formatNumber(Object.keys(lpbNumbers).length) + ' nomor LPB'
                );

                if (!rows || rows.length === 0) {
                    tbody.html('<tr><td colspan="5" class="text-center text-muted">Belum ada nomor LPB terekam untuk PO ini.</td></tr>');
                    return;
                }

                $.each(rows, function(_, row) {
                    var invoice = hasInvoice(row.no_invoice) ? row.no_invoice : '-';
                    var faktur = row.kode_faktur_pajak ? row.kode_faktur_pajak : '-';
                    tbody.append(
                        '<tr class="js-lpb-invoice-row" data-id="' + escAttr(row.id_lpb) + '">' +
                        '<td>' + escHtml(row.nomor_lpb || '-') + '</td>' +
                        '<td>' + escHtml(invoice) + '</td>' +
                        '<td class="text-center">' + escHtml(formatDateId(row.tanggal_invoice)) + '</td>' +
                        '<td class="text-center">' + escHtml(faktur) + '</td>' +
                        '<td class="text-right">' + escHtml(formatNumber(row.total_qty || 0)) + '</td>' +
                        '</tr>'
                    );
                });
            }

            function renderList(rows, options) {
                options = options || {};
                var container = $('#lpbListContainer');
                container.empty();

                if (!rows || rows.length === 0) {
                    $('#lpbListLoading').hide();
                    $('#lpbListWrap').hide();
                    $('#lpbListEmpty').show();
                    renderInvoiceOverview(allInvoiceRows);
                    resetDetailState();
                    updateStats([]);
                    return;
                }

                $.each(rows, function(_, row) {
                    container.append(buildListItem(row));
                });
                renderInvoiceOverview(allInvoiceRows);

                $('#lpbListLoading').hide();
                $('#lpbListEmpty').hide();
                $('#lpbListWrap').show();
                updateStats(rows);

                var targetId = selectedIdLpb || rows[0].id_lpb;
                selectListItem(targetId);
                if (options.skipDetailReload) {
                    return;
                }

                loadDetail(targetId);
            }

            function resetDetailState() {
                selectedIdLpb = 0;
                selectedHeader = null;
                selectedDetailRows = [];
                selectedActivityLogs = [];
                detailViewMode = 'lpb';
                purchasingEditMode = false;
                isActivityLogVisible = false;
                updateDetailViewButton();
                updateWorkflowActions();
                updateActivityLogVisibility();
                renderActivityLogs([]);
                $('#selectedLpbText').text('Belum ada LPB dipilih');
                $('#lpbDetailLoading').hide();
                $('#lpbDetailWrap').hide();
                $('#lpbDetailEmpty').show();
                $('#lpbDetailHeaderGrid').empty();
                $('#lpbDetailTable tbody').empty();
                renderInvoiceOverview(allInvoiceRows);
                $('#lpbGrandTotalHargaWrap').hide();
                $('#lpbTotalDpp').text(formatRupiah(0));
                $('#lpbGrandTotalHarga').text(formatRupiah(0));
                selectedPurchasingRows = [];
                updateBulkAcceptButton();
            }

            function selectListItem(idLpb) {
                selectedIdLpb = parseInt(idLpb, 10) || 0;
                $('.js-lpb-item').removeClass('active');
                $('.js-lpb-item[data-id="' + selectedIdLpb + '"]').addClass('active');
                $('.js-lpb-invoice-row').removeClass('active');
                $('.js-lpb-invoice-row[data-id="' + selectedIdLpb + '"]').addClass('active');
            }

            function updateDetailViewButton() {
                return;
            }

            function buildTh(label, className, attrs) {
                return '<th' + (className ? ' class="' + className + '"' : '') + (attrs ? ' ' + attrs : '') + '>' + escHtml(label) + '</th>';
            }

            function setLpbDetailTableHead(options) {
                options = options || {};
                var html = '<tr>';

                if (options.actionLeft) {
                    html += buildTh('#', 'text-center', 'rowspan="2"');
                }

                html += buildTh('Kode Barang', '', 'rowspan="2"') +
                    buildTh('Nama Barang', '', 'rowspan="2"') +
                    buildTh('No Lot', 'text-center', 'rowspan="2"') +
                    buildTh('Expired Date', 'text-center', 'rowspan="2"') +
                    buildTh('Qty In', 'text-center', 'rowspan="2"') +
                    buildTh('Qty Satuan', 'text-center', 'colspan="2"');

                if (options.priceColumns) {
                    html += buildTh('Harga Satuan', 'text-right', 'rowspan="2"') +
                        buildTh('DPP', 'text-right', 'rowspan="2"') +
                        buildTh('DPP Nilai Lain', 'text-right', 'rowspan="2"') +
                        buildTh('PPN', 'text-right', 'rowspan="2"') +
                        buildTh('Total Harga', 'text-right', 'rowspan="2"');
                }

                if (options.actionRight) {
                    html += buildTh('#', 'text-center', 'rowspan="2"');
                }

                html += '</tr><tr>' +
                    buildTh('BOX', 'text-center') +
                    buildTh('Kg/Ltr', 'text-center') +
                    '</tr>';
                $('#lpbDetailTable thead').html(html);
            }

            function renderDetailHeader(header) {
                var html = '';
                var nomorJenisLpb = (header.nomor_lpb || 'Nomor LPB belum dibuat') + ' / ' + (header.jenis_lpb || 'Jenis LPB belum ditentukan');
                var boxes = [{
                        label: 'Nomor / Jenis LPB',
                        value: nomorJenisLpb
                    },
                    {
                        label: 'Nomor SJ',
                        value: header.nosj || '-'
                    },
                    {
                        label: 'Tanggal SJ',
                        value: formatDateId(header.tgl_sj)
                    },
                    {
                        label: 'No Invoice',
                        value: header.no_invoice || '-'
                    },
                    {
                        label: 'Tgl Terbit Invoice',
                        value: formatDateId(header.tanggal_invoice)
                    },
                    {
                        label: 'Faktur Pajak',
                        value: header.kode_faktur_pajak || '-'
                    },
                    {
                        label: 'Tanggal Terbit Faktur',
                        value: formatDateId(header.tanggal_faktur_pajak)
                    },
                    {
                        label: 'Checker',
                        value: (function() {
                            var cBy = $.trim(header.checker_by || '');
                            var cName = $.trim(header.checker_name || '');
                            if (cBy !== '' && cName !== '' && cBy !== cName) {
                                return cBy + ' (' + cName + ')';
                            }
                            return cBy !== '' ? cBy : (cName !== '' ? cName : '-');
                        })()
                    },
                    {
                        label: 'Transaksi Penjualan',
                        value: parseInt(header.has_sales_transaction || 0, 10) === 1
                            ? formatNumber(header.sales_invoice_count || 0) + ' faktur / Qty ' + formatNumber(header.sales_qty_total || 0)
                            : 'Belum ada'
                    },
                    {
                        label: 'Jurnal LPB',
                        value: parseInt(header.has_active_lpb_journal || 0, 10) === 1
                            ? 'POSTED: ' + (header.lpb_journal_sample || '-')
                            : 'Belum POSTED aktif'
                    }
                ];

                $.each(boxes, function(_, box) {
                    html += '' +
                        '<div class="lpb-detail-box">' +
                        '<div class="label">' + escHtml(box.label) + '</div>' +
                        '<div class="value">' + escHtml(box.value) + '</div>' +
                        '</div>';
                });

                $('#lpbDetailHeaderGrid').html(html);
            }

            function renderPurchasingHeader(header) {
                renderDetailHeader(header || {});
            }

            function updateWorkflowActions() {
                if (!selectedHeader || !selectedIdLpb) {
                    $('#lpbWorkflowActions').hide();
                    $('#lpbPostActions').hide();
                    return;
                }

                $('#lpbWorkflowActions').show();
                var isUnpost = isSelectedLpbUnpost();
                $('#btnUpdateInvoice')
                    .prop('disabled', false)
                    .attr('title', 'Update Invoice');
                $('#btnSplitInvoice')
                    .toggle(isUnpost)
                    .prop('disabled', !isUnpost)
                    .attr('title', isUnpost ? 'Pecah Invoice' : 'UNPOST LPB terlebih dahulu untuk pecah invoice');
                $('#btnUpdateFaktur')
                    .prop('disabled', false)
                    .attr('title', 'Update Faktur');

                if (!showLpbListPanel || detailViewMode !== 'lpb') {
                    $('#lpbPostActions').hide();
                    return;
                }

                $('#lpbPostActions').show();
                $('#btnUnpostLpb').toggle(!isUnpost).prop('disabled', isChangingLpbStatus);
                $('#btnPostLpb').toggle(isUnpost).prop('disabled', isChangingLpbStatus);
                $('#btnUpdateLpbIdentity').toggle(isUnpost);
                $('#btnUpdateLpbSj').toggle(isUnpost);
            }

            function updateActivityLogVisibility() {
                $('#lpbActivityLogWrap').toggle(isActivityLogVisible && !!selectedIdLpb);
                $('#btnToggleLpbLog')
                    .toggleClass('btn-primary', !isActivityLogVisible)
                    .toggleClass('btn-info', isActivityLogVisible)
                    .attr('title', isActivityLogVisible ? 'Sembunyikan log aktivitas' : 'Tampilkan log aktivitas')
                    .html(isActivityLogVisible ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>');
            }

            function renderActivityLogs(rows) {
                rows = rows || [];
                var tbody = $('#lpbActivityLogBody');
                tbody.empty();
                $('#lpbActivityLogCount').text(formatNumber(rows.length) + ' aktivitas');

                if (rows.length === 0) {
                    tbody.html('<tr><td colspan="6" class="text-center text-muted">Belum ada aktivitas.</td></tr>');
                    return;
                }

                $.each(rows, function(_, row) {
                    var beforeStatus = row.status_before || '';
                    var afterStatus = row.status_after || '';
                    var statusText = beforeStatus || afterStatus ? (beforeStatus ? beforeStatus + ' -> ' + afterStatus : afterStatus) : '-';
                    tbody.append(
                        '<tr>' +
                        '<td>' + escHtml(formatDateTimeId(row.dilakukan_pada)) + '</td>' +
                        '<td>' + escHtml(row.dilakukan_oleh || '-') + '</td>' +
                        '<td>' + escHtml(row.checker_name || '-') + '</td>' +
                        '<td><span class="badge badge-light">' + escHtml(row.action_type || '-') + '</span></td>' +
                        '<td>' + escHtml(statusText) + '</td>' +
                        '<td class="activity-note">' + escHtml(row.keterangan || '-') + '</td>' +
                        '</tr>'
                    );
                });
            }

            function splitDetailBadge(row) {
                if (parseInt((row || {}).is_split_detail || 0, 10) !== 1) {
                    return '';
                }

                return ' <span class="badge badge-warning ml-1" title="Data yang pernah di-split"><i class="fas fa-code-branch mr-1"></i>Split</span>';
            }

            function getActiveTotalHarga(row) {
                var total = parseFloat(row.total_harga_display || row.total_harga_exclude || row.total_harga || 0);
                return isNaN(total) ? 0 : total;
            }

            function getActiveDpp(row) {
                var dpp = parseFloat(row.dpp || row.total_harga_exclude || row.total_harga || 0);
                return isNaN(dpp) ? 0 : dpp;
            }

            function renderGrandTotalHarga(rows, shouldShow) {
                if (!shouldShow || !rows || rows.length === 0) {
                    $('#lpbGrandTotalHargaWrap').hide();
                    $('#lpbTotalDpp').text(formatRupiah(0));
                    $('#lpbGrandTotalHarga').text(formatRupiah(0));
                    return;
                }

                var totalDpp = 0;
                var grandTotal = 0;
                $.each(rows, function(_, row) {
                    totalDpp += getActiveDpp(row);
                    grandTotal += getActiveTotalHarga(row);
                });

                $('#lpbTotalDpp').text(formatRupiah(totalDpp));
                $('#lpbGrandTotalHarga').text(formatRupiah(grandTotal));
                $('#lpbGrandTotalHargaWrap').show();
            }

            function renderDetailTable(rows) {
                if (showLpbListPanel) {
                    var canEditDetail = isSelectedLpbUnpost();
                    var logistikColCount = 7 + (canEditDetail ? 1 : 0);
                    setLpbDetailTableHead({
                        actionLeft: canEditDetail
                    });

                    var logistikTbody = $('#lpbDetailTable tbody');
                    logistikTbody.empty();
                    selectedPurchasingRows = [];
                    $('#lpbPurchasingVerifyActions').hide();
                    renderGrandTotalHarga([], false);

                    if (!rows || rows.length === 0) {
                        logistikTbody.html('<tr><td colspan="' + logistikColCount + '" class="text-center text-muted">Detail LPB kosong.</td></tr>');
                        return;
                    }

                    $.each(rows, function(index, row) {
                        var actionColumn = canEditDetail ? (
                            '<td class="text-center">' +
                            '<div class="btn-group btn-group-sm" role="group">' +
                            '<button type="button" class="btn btn-warning btn-sm js-open-lpb-detail-receipt" title="Edit detail LPB" ' +
                            'data-id-detail="' + escAttr(row.id_detail_lpb || 0) + '" ' +
                            'data-kd-barang="' + escAttr(row.kd_barang || '') + '" ' +
                            'data-nama-barang="' + escAttr(row.nama_barang || '-') + '" ' +
                            'data-no-lot="' + escAttr(row.no_lot || '') + '" ' +
                            'data-expired-date="' + escAttr(row.expired_date || '') + '" ' +
                            'data-qty="' + escAttr(row.qty_diterima || row.qty_lpb || 0) + '">' +
                            '<i class="fas fa-pencil-alt"></i>' +
                            '</button>' +
                            '<button type="button" class="btn btn-outline-warning btn-sm js-open-lpb-detail-split" title="Split detail LPB" ' +
                            'data-id-detail="' + escAttr(row.id_detail_lpb || 0) + '" ' +
                            'data-kd-barang="' + escAttr(row.kd_barang || '') + '" ' +
                            'data-nama-barang="' + escAttr(row.nama_barang || '-') + '" ' +
                            'data-qty="' + escAttr(row.qty_in || row.qty_diterima || row.qty_lpb || 0) + '" ' +
                            'data-harga-satuan="' + escAttr(row.harga_satuan_exclude || row.harga_satuan || 0) + '" ' +
                            'data-total-harga="' + escAttr(row.total_harga_exclude || row.total_harga || 0) + '">' +
                            '<i class="fas fa-code-branch"></i>' +
                            '</button>' +
                            '</div>' +
                            '</td>'
                        ) : '';

                        logistikTbody.append(
                            '<tr>' +
                            actionColumn +
                            '<td>' + escHtml(row.kd_barang || '-') + '</td>' +
                            '<td>' + escHtml(row.nama_barang || '-') + splitDetailBadge(row) + '</td>' +
                            '<td class="text-center">' + escHtml(row.no_lot || '-') + '</td>' +
                            '<td class="text-center">' + escHtml(formatDateId(row.expired_date)) + '</td>' +
                            '<td class="text-center">' + escHtml(formatNumber(row.qty_in || 0)) + '</td>' +
                            '<td class="text-center">' + escHtml(formatNumber(row.qty_satuan_box || 0)) + '</td>' +
                            '<td class="text-center">' + escHtml(formatNumber(row.qty_satuan_kg_ltr || 0)) + '</td>' +
                            '</tr>'
                        );
                    });

                    return;
                }

                selectedPurchasingRows = $.map(rows || [], function(row) {
                    return {
                        id_detail_lpb: row.id_detail_lpb || 0,
                        kd_barang: row.kd_barang || '',
                        nama_barang: row.nama_barang || '-',
                        no_lot: row.no_lot || '-',
                        expired_date: formatDateId(row.expired_date),
                        qty_in: row.qty_in || 0,
                        qty_satuan_box: row.qty_satuan_box || 0,
                        qty_satuan_kg_ltr: row.qty_satuan_kg_ltr || 0,
                        qty_satuan_pcs: row.qty_satuan_pcs || row.qty_diterima || 0,
                        qty_lpb: row.qty_diterima || 0,
                        qty_order: row.qty_order || 0,
                        qty_sisa: row.qty_sisa || 0,
                        qty_lpb_total: row.qty_lpb_total || 0,
                        total_harga: row.total_harga || 0,
                        total_harga_display: row.total_harga_display || row.total_harga || 0,
                        harga_satuan: row.harga_satuan || 0,
                        dpp: row.dpp || row.total_harga_exclude || row.total_harga || 0,
                        dpp_nilai_lain: row.dpp_nilai_lain || 0,
                        ppn: row.ppn || 0,
                        total_harga_exclude: row.total_harga_exclude || row.total_harga || 0,
                        harga_satuan_exclude: row.harga_satuan_exclude || row.harga_satuan || 0,
                        harga_satuan_sebelumnya: row.harga_satuan_sebelumnya || 0,
                        total_harga_sebelumnya: row.total_harga_sebelumnya || 0,
                        harga_terverifikasi: row.harga_terverifikasi || 0,
                        is_split_detail: row.is_split_detail || 0
                    };
                });
                purchasingEditMode = false;
                renderPurchasingTable(selectedPurchasingRows);
            }

            function loadDetail(idLpb) {
                if (!idLpb) {
                    resetDetailState();
                    return;
                }

                $('#lpbDetailEmpty').hide();
                $('#lpbDetailWrap').hide();
                $('#lpbDetailLoading').show();
                $('#selectedLpbText').text('Memuat detail LPB...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_get_lpb_record_detail') ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        id_lpb: idLpb
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Detail LPB tidak dapat dimuat.', 'error');
                            resetDetailState();
                            return;
                        }

                        selectedHeader = res.header || {};
                        selectedDetailRows = res.rows || [];
                        selectedActivityLogs = res.logs || [];
                        showLpbDetailView();
                        renderActivityLogs(selectedActivityLogs);
                        updateActivityLogVisibility();
                        $('#lpbDetailLoading').hide();
                        $('#lpbDetailEmpty').hide();
                        $('#lpbDetailWrap').show();
                    },
                    error: function() {
                        resetDetailState();
                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengambil detail LPB.', 'error');
                    }
                });
            }

            function loadList(options) {
                options = options || {};
                $('#lpbListLoading').show();
                $('#lpbListEmpty').hide();
                $('#lpbListWrap').hide();

                $.ajax({
                    url: '<?= base_url('ics/ajax_get_lpb_records_by_kd_po') ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        kd_po: kdPo,
                        id_lpb: scopedIdLpb
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            $('#lpbListLoading').hide();
                            $('#lpbListEmpty').show();
                            updateStats([]);
                            Swal.fire('Gagal', res.message || 'Daftar LPB tidak dapat dimuat.', 'error');
                            return;
                        }

                        allRows = res.rows || [];
                        allInvoiceRows = res.invoice_rows || [];
                        renderList(allRows, options);
                        applySearch();
                    },
                    error: function() {
                        $('#lpbListLoading').hide();
                        $('#lpbListEmpty').show();
                        updateStats([]);
                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengambil daftar LPB.', 'error');
                    }
                });
            }

            function printSelectedLpb() {
                if (!selectedIdLpb) {
                    Swal.fire('Validasi', 'Silakan pilih LPB yang ingin dicetak terlebih dahulu.', 'warning');
                    return;
                }

                window.open('<?= base_url('ics/print_lpb_record/') ?>' + selectedIdLpb, '_blank');
            }

            function printAllLpb() {
                window.open(
                    '<?= base_url('ics/print_lpb_records_all') ?>?kd_po=' + encodeURIComponent(kdPo) + '&no_po=' + encodeURIComponent('<?= htmlspecialchars($no_po ?? '', ENT_QUOTES) ?>'),
                    '_blank'
                );
            }

            function loadPurchasingDetailView() {
                if (!selectedIdLpb) {
                    Swal.fire('Validasi', 'Silakan pilih LPB terlebih dahulu.', 'warning');
                    return;
                }

                $('#lpbDetailEmpty').hide();
                $('#lpbDetailWrap').hide();
                $('#lpbDetailLoading').show();
                $('#selectedLpbText').text('Memuat data purchasing...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_get_purchasing_po_detail') ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        id_lpb: selectedIdLpb
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Data purchasing tidak dapat dimuat.', 'error');
                            showLpbDetailView();
                            return;
                        }

                        detailViewMode = 'purchasing';
                        purchasingEditMode = false;
                        isActivityLogVisible = false;
                        selectedHeader = res.header || {};
                        selectedActivityLogs = res.logs || [];
                        updateDetailViewButton();
                        updateWorkflowActions();
                        renderActivityLogs(selectedActivityLogs);
                        updateActivityLogVisibility();
                        renderPurchasingHeader(selectedHeader);
                        renderPurchasingTable(res.rows || []);
                        $('#selectedLpbText').text('Purchasing LPB: ' + ((selectedHeader || {}).nomor_lpb || 'belum dibuat'));
                        $('#lpbDetailLoading').hide();
                        $('#lpbDetailEmpty').hide();
                        $('#lpbDetailWrap').show();
                    },
                    error: function() {
                        $('#lpbDetailLoading').hide();
                        $('#lpbDetailWrap').show();
                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengambil data purchasing.', 'error');
                    }
                });
            }

            function togglePurchasingView() {
                if (detailViewMode === 'purchasing') {
                    showLpbDetailView();
                    return;
                }

                loadPurchasingDetailView();
            }

            function renderPurchasingTable(rows) {
                selectedPurchasingRows = rows || [];
                var canEditPrice = isSelectedLpbUnpost();
                var colCount = 12 + (canEditPrice ? 1 : 0);
                setLpbDetailTableHead({
                    priceColumns: true,
                    actionRight: canEditPrice
                });

                var tbody = $('#lpbDetailTable tbody');
                tbody.empty();

                if (!rows || rows.length === 0) {
                    tbody.html('<tr><td colspan="' + colCount + '" class="text-center text-muted">Detail LPB kosong.</td></tr>');
                    renderGrandTotalHarga([], false);
                    updateBulkAcceptButton();
                    return;
                }

                $.each(rows, function(index, row) {
                    var hargaSatuanAktif = row.harga_satuan_exclude || row.harga_satuan || 0;
                    var dppAktif = row.dpp || row.total_harga_exclude || row.total_harga || 0;
                    var dppNilaiLainAktif = row.dpp_nilai_lain || (dppAktif * (11 / 12));
                    var ppnAktif = row.ppn || (dppNilaiLainAktif * (12 / 100));
                    var totalHargaEdit = row.total_harga_exclude || row.total_harga || 0;
                    var totalHargaAktif = row.total_harga_display || row.total_harga_exclude || row.total_harga || 0;
                    var actionColumn = canEditPrice ? (
                        '<td class="text-center">' +
                        '<div class="btn-group btn-group-sm" role="group">' +
                        '<button type="button" class="btn btn-warning btn-sm js-open-lpb-price" title="Update harga dan Qty LPB" ' +
                        'data-id-detail="' + escAttr(row.id_detail_lpb || 0) + '" ' +
                        'data-kd-barang="' + escAttr(row.kd_barang || '') + '" ' +
                        'data-nama-barang="' + escAttr(row.nama_barang || '-') + '" ' +
                        'data-qty="' + escAttr(row.qty_lpb || 0) + '" ' +
                        'data-qty-order="' + escAttr(row.qty_order || 0) + '" ' +
                        'data-qty-sisa="' + escAttr(row.qty_sisa || 0) + '" ' +
                        'data-qty-total="' + escAttr(row.qty_lpb_total || 0) + '" ' +
                        'data-harga-satuan="' + escAttr(hargaSatuanAktif) + '" ' +
                        'data-total-harga="' + escAttr(totalHargaEdit) + '">' +
                        '<i class="fas fa-pencil-alt"></i>' +
                        '</button>' +
                        '<button type="button" class="btn btn-outline-warning btn-sm js-open-lpb-detail-split" title="Split detail LPB" ' +
                        'data-id-detail="' + escAttr(row.id_detail_lpb || 0) + '" ' +
                        'data-kd-barang="' + escAttr(row.kd_barang || '') + '" ' +
                        'data-nama-barang="' + escAttr(row.nama_barang || '-') + '" ' +
                        'data-qty="' + escAttr(row.qty_in || row.qty_lpb || 0) + '" ' +
                        'data-harga-satuan="' + escAttr(hargaSatuanAktif) + '" ' +
                        'data-total-harga="' + escAttr(totalHargaEdit) + '">' +
                        '<i class="fas fa-code-branch"></i>' +
                        '</button>' +
                        '</div>' +
                        '</td>'
                    ) : '';

                    tbody.append(
                        '<tr>' +
                        '<td>' + escHtml(row.kd_barang || '-') + '</td>' +
                        '<td>' + escHtml(row.nama_barang || '-') + splitDetailBadge(row) + '</td>' +
                        '<td class="text-center">' + escHtml(row.no_lot || '-') + '</td>' +
                        '<td class="text-center">' + escHtml(row.expired_date || '-') + '</td>' +
                        '<td class="text-center">' + escHtml(formatNumber(row.qty_in || 0)) + '</td>' +
                        '<td class="text-center">' + escHtml(formatNumber(row.qty_satuan_box || 0)) + '</td>' +
                        '<td class="text-center">' + escHtml(formatNumber(row.qty_satuan_kg_ltr || 0)) + '</td>' +
                        '<td class="text-right">' + escHtml(formatRupiah(hargaSatuanAktif)) + '</td>' +
                        '<td class="text-right">' + escHtml(formatRupiah(dppAktif)) + '</td>' +
                        '<td class="text-right">' + escHtml(formatRupiah(dppNilaiLainAktif)) + '</td>' +
                        '<td class="text-right">' + escHtml(formatRupiah(ppnAktif)) + '</td>' +
                        '<td class="text-right">' + escHtml(formatRupiah(totalHargaAktif)) + '</td>' +
                        actionColumn +
                        '</tr>'
                    );
                });

                renderGrandTotalHarga(selectedPurchasingRows, true);
                updateBulkAcceptButton();
            }

            function showLpbDetailView() {
                detailViewMode = 'lpb';
                purchasingEditMode = false;
                updateDetailViewButton();
                selectedPurchasingRows = [];
                updateBulkAcceptButton();
                renderDetailHeader(selectedHeader || {});
                renderDetailTable(selectedDetailRows || []);
                updateWorkflowActions();
                updateActivityLogVisibility();
                $('#selectedLpbText').text('Nomor LPB: ' + ((selectedHeader || {}).nomor_lpb || 'belum dibuat'));
            }

            function getBulkAcceptableRows() {
                return $.grep(selectedPurchasingRows || [], function(row) {
                    var idDetail = parseInt(row.id_detail_lpb || 0, 10);
                    var hargaVerified = parseInt(row.harga_terverifikasi || 0, 10) === 1;
                    var hargaSatuanAktif = parseFloat(row.harga_satuan_exclude || row.harga_satuan || 0);
                    var totalHargaAktif = parseFloat(row.total_harga_exclude || row.total_harga || 0);

                    return idDetail > 0 && !hargaVerified && hargaSatuanAktif > 0 && totalHargaAktif > 0;
                });
            }

            function updateBulkAcceptButton() {
                if (!selectedPurchasingRows || selectedPurchasingRows.length === 0) {
                    $('#lpbPurchasingVerifyActions').hide();
                    return;
                }

                var rows = selectedPurchasingRows || [];
                var isPost = isSelectedLpbPost();
                var isUnpost = isSelectedLpbUnpost();

                $('#lpbPurchasingVerifyActions').show();
                $('#lpbBulkVerifyInfo').text(isPost ? 'LPB berstatus POST.' : (isUnpost ? 'LPB berstatus UNPOST.' : ''));
                $('#btnPurchasingUnpostLpb')
                    .toggle(isPost)
                    .prop('disabled', !isPost || isChangingLpbStatus)
                    .html(isChangingLpbStatus && isPost ? '<i class="fas fa-spinner fa-spin mr-1"></i> UNPOST...' : '<i class="fas fa-undo mr-1"></i> UNPOST');
                $('#btnPurchasingPostLpb')
                    .toggle(isUnpost)
                    .prop('disabled', !isUnpost || isChangingLpbStatus)
                    .html(isChangingLpbStatus && isUnpost ? '<i class="fas fa-spinner fa-spin mr-1"></i> Posting...' : '<i class="fas fa-save mr-1"></i> Posting Data');
            }

            function bulkAcceptDisplayedLpbPrices() {
                var rows = selectedPurchasingRows || [];
                var acceptableRows = getBulkAcceptableRows();
                var allVerified = rows.length > 0 && acceptableRows.length === 0;

                if (allVerified) {
                    if (isSelectedLpbUnpost()) {
                        changeLpbStatus('<?= base_url('ics/ajax_post_lpb') ?>', 'LPB berhasil direkam menjadi POST.');
                        return;
                    }

                    Swal.fire('Info', 'LPB sudah berstatus POST.', 'info');
                    return;
                }

                var ids = $.map(acceptableRows, function(row) {
                    return parseInt(row.id_detail_lpb || 0, 10);
                });

                if (ids.length === 0 || isBulkAcceptingLpbPrice) {
                    return;
                }

                isBulkAcceptingLpbPrice = true;
                updateBulkAcceptButton();

                $.ajax({
                    url: '<?= base_url('ics/ajax_bulk_accept_lpb_detail_price') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_detail_lpb: ids.join(',')
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Bulk verifikasi harga gagal disimpan.', 'error');
                            return;
                        }

                        Swal.fire('Berhasil', res.message || 'Harga detail LPB berhasil diverifikasi.', 'success');
                        if (detailViewMode === 'purchasing') {
                            loadPurchasingDetailView();
                        } else {
                            loadDetail(selectedIdLpb);
                        }
                        loadList({
                            skipDetailReload: true
                        });
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat bulk verifikasi harga.', 'error');
                    },
                    complete: function() {
                        isBulkAcceptingLpbPrice = false;
                        updateBulkAcceptButton();
                    }
                });
            }

            function openUpdateInvoiceModal(header) {
                var activeHeader = header || selectedHeader;
                var activeId = activeHeader ? (activeHeader.id_lpb || selectedIdLpb) : selectedIdLpb;

                if (!activeId || !activeHeader) {
                    Swal.fire('Validasi', 'Silakan pilih LPB yang ingin di-update terlebih dahulu.', 'warning');
                    return;
                }

                $('#invoiceIdLpb').val(activeId);
                $('#invoiceNo').val(hasInvoice(activeHeader.no_invoice) ? activeHeader.no_invoice : '');
                $('#invoiceTanggalTerbit').val(activeHeader.tanggal_invoice || '');
                $('#modalUpdateInvoice').modal('show');
            }

            function getSplitSourceRows() {
                var rows = (detailViewMode === 'purchasing' && selectedPurchasingRows.length > 0) ? selectedPurchasingRows : selectedDetailRows;
                return $.grep(rows || [], function(row) {
                    return parseInt(row.id_detail_lpb || 0, 10) > 0 && getSplitRowQty(row) > 0;
                });
            }

            function getSplitRowQty(row) {
                return parseFloat(row.qty_in || row.qty_lpb || row.qty_diterima || 0) || 0;
            }

            function formatInputNumber(value) {
                var number = parseFloat(value) || 0;
                return parseFloat(number.toFixed(4)).toString();
            }

            function readSplitState() {
                var invoices = [];
                $('#splitInvoiceRows .js-split-invoice-row').each(function() {
                    invoices.push({
                        no_invoice: $(this).find('.js-split-no-invoice').val() || '',
                        tanggal_invoice: $(this).find('.js-split-tanggal-invoice').val() || ''
                    });
                });

                var allocations = {};
                $('#splitInvoiceAllocationTable .js-split-qty').each(function() {
                    var idDetail = parseInt($(this).data('detail-id'), 10) || 0;
                    var index = parseInt($(this).data('index'), 10) || 0;
                    if (!allocations[idDetail]) {
                        allocations[idDetail] = {};
                    }
                    allocations[idDetail][index] = $(this).val();
                });

                return {
                    invoices: invoices,
                    allocations: allocations
                };
            }

            function buildDefaultSplitState() {
                var today = new Date().toISOString().slice(0, 10);
                var invoiceDate = selectedHeader && selectedHeader.tanggal_invoice ? selectedHeader.tanggal_invoice : today;
                var rows = getSplitSourceRows();
                var allocations = {};

                $.each(rows, function(_, row) {
                    var idDetail = parseInt(row.id_detail_lpb || 0, 10);
                    var qty = getSplitRowQty(row);
                    var firstQty = qty / 2;
                    allocations[idDetail] = {
                        0: formatInputNumber(firstQty),
                        1: formatInputNumber(qty - firstQty)
                    };
                });

                return {
                    invoices: [{
                            no_invoice: hasInvoice((selectedHeader || {}).no_invoice) ? selectedHeader.no_invoice : '',
                            tanggal_invoice: invoiceDate
                        },
                        {
                            no_invoice: '',
                            tanggal_invoice: invoiceDate
                        }
                    ],
                    allocations: allocations
                };
            }

            function renderSplitInvoiceRows(state) {
                var wrap = $('#splitInvoiceRows');
                wrap.empty();

                $.each(state.invoices, function(index, invoice) {
                    var removeButton = state.invoices.length > 2 ?
                        '<button type="button" class="btn btn-outline-danger btn-sm js-remove-split-invoice" data-index="' + index + '" title="Hapus invoice"><i class="fas fa-trash"></i></button>' :
                        '';
                    wrap.append(
                        '<div class="lpb-split-invoice-row js-split-invoice-row" data-index="' + index + '">' +
                        '<div class="row align-items-end">' +
                        '<div class="col-md-2">' +
                        '<label>Invoice</label>' +
                        '<div class="font-weight-bold">Invoice ' + (index + 1) + '</div>' +
                        '</div>' +
                        '<div class="col-md-5">' +
                        '<label>No Invoice</label>' +
                        '<input type="text" class="form-control js-split-no-invoice" value="' + escAttr(invoice.no_invoice || '') + '" required>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                        '<label>Tanggal Invoice</label>' +
                        '<input type="date" class="form-control js-split-tanggal-invoice" value="' + escAttr(invoice.tanggal_invoice || '') + '" required>' +
                        '</div>' +
                        '<div class="col-md-1 text-right">' + removeButton + '</div>' +
                        '</div>' +
                        '</div>'
                    );
                });
            }

            function renderSplitInvoiceAllocationTable(state) {
                var rows = getSplitSourceRows();
                var thead = $('#splitInvoiceAllocationTable thead');
                var tbody = $('#splitInvoiceAllocationTable tbody');
                var tfoot = $('#splitInvoiceAllocationTable tfoot');
                var headerHtml = '<tr>' +
                    '<th>Kode Barang</th>' +
                    '<th>Nama Barang</th>' +
                    '<th class="text-center">No Lot</th>' +
                    '<th class="text-right">Qty LPB</th>';

                $.each(state.invoices, function(index) {
                    headerHtml += '<th class="text-right">Qty Invoice ' + (index + 1) + '</th>';
                });
                headerHtml += '<th class="text-right">Selisih</th></tr>';
                thead.html(headerHtml);
                tbody.empty();

                if (rows.length === 0) {
                    tbody.html('<tr><td colspan="' + (5 + state.invoices.length) + '" class="text-center text-muted">Detail LPB kosong.</td></tr>');
                    tfoot.empty();
                    return;
                }

                $.each(rows, function(_, row) {
                    var idDetail = parseInt(row.id_detail_lpb || 0, 10);
                    var qty = getSplitRowQty(row);
                    var rowHtml = '<tr data-detail-id="' + idDetail + '" data-source-qty="' + escAttr(qty) + '">' +
                        '<td>' + escHtml(row.kd_barang || '-') + '</td>' +
                        '<td>' + escHtml(row.nama_barang || '-') + '</td>' +
                        '<td class="text-center">' + escHtml(row.no_lot || '-') + '</td>' +
                        '<td class="text-right js-source-qty">' + escHtml(formatNumber(qty)) + '</td>';

                    $.each(state.invoices, function(index) {
                        var value = state.allocations[idDetail] && state.allocations[idDetail][index] != null ?
                            state.allocations[idDetail][index] :
                            '0';
                        rowHtml += '<td><input type="number" min="0" step="0.0001" class="form-control text-right js-split-qty" data-detail-id="' + idDetail + '" data-index="' + index + '" value="' + escAttr(value) + '"></td>';
                    });

                    rowHtml += '<td class="text-right js-split-diff">0</td></tr>';
                    tbody.append(rowHtml);
                });

                var footerHtml = '<tr><th colspan="4" class="text-right">Total per Invoice</th>';
                $.each(state.invoices, function(index) {
                    footerHtml += '<th class="text-right js-split-invoice-total" data-index="' + index + '">0</th>';
                });
                footerHtml += '<th></th></tr>';
                tfoot.html(footerHtml);
                updateSplitInvoiceValidationInfo();
            }

            function renderSplitInvoiceModal(state) {
                renderSplitInvoiceRows(state);
                renderSplitInvoiceAllocationTable(state);
            }

            function addSplitInvoiceRow() {
                var state = readSplitState();
                var defaultDate = state.invoices.length > 0 ? state.invoices[0].tanggal_invoice : new Date().toISOString().slice(0, 10);
                state.invoices.push({
                    no_invoice: '',
                    tanggal_invoice: defaultDate
                });
                renderSplitInvoiceModal(state);
            }

            function removeSplitInvoiceRow(index) {
                var state = readSplitState();
                if (state.invoices.length <= 2) {
                    return;
                }

                state.invoices.splice(index, 1);
                $.each(state.allocations, function(idDetail, rowAllocations) {
                    var nextAllocations = {};
                    $.each(state.invoices, function(newIndex, _invoice) {
                        var oldIndex = newIndex >= index ? newIndex + 1 : newIndex;
                        nextAllocations[newIndex] = rowAllocations[oldIndex] || '0';
                    });
                    state.allocations[idDetail] = nextAllocations;
                });
                renderSplitInvoiceModal(state);
            }

            function collectSplitPayload() {
                var state = readSplitState();
                var splits = $.map(state.invoices, function(invoice, index) {
                    var details = [];
                    $('#splitInvoiceAllocationTable .js-split-qty[data-index="' + index + '"]').each(function() {
                        details.push({
                            id_detail_lpb: parseInt($(this).data('detail-id'), 10) || 0,
                            qty_diterima: parseFloat($(this).val()) || 0
                        });
                    });

                    return {
                        no_invoice: $.trim(invoice.no_invoice || ''),
                        tanggal_invoice: $.trim(invoice.tanggal_invoice || ''),
                        details: details
                    };
                });

                return {
                    id_lpb: selectedIdLpb,
                    splits: splits
                };
            }

            function validateSplitPayload(payload) {
                var errors = [];
                var invoiceKeys = {};
                var invoiceTotals = [];

                if (!payload.id_lpb) {
                    errors.push('LPB belum dipilih.');
                }
                if (!payload.splits || payload.splits.length < 2) {
                    errors.push('Minimal harus ada 2 invoice.');
                }

                $.each(payload.splits || [], function(index, split) {
                    var invoice = $.trim(split.no_invoice || '');
                    var tanggal = $.trim(split.tanggal_invoice || '');
                    if (!invoice) {
                        errors.push('No Invoice ' + (index + 1) + ' wajib diisi.');
                    }
                    if (!tanggal) {
                        errors.push('Tanggal Invoice ' + (index + 1) + ' wajib diisi.');
                    }
                    var key = invoice.toUpperCase();
                    if (key && invoiceKeys[key]) {
                        errors.push('No invoice tidak boleh duplikat: ' + invoice + '.');
                    }
                    invoiceKeys[key] = true;
                    invoiceTotals[index] = 0;
                    $.each(split.details || [], function(_, detail) {
                        invoiceTotals[index] += parseFloat(detail.qty_diterima || 0) || 0;
                    });
                    if (invoiceTotals[index] <= 0) {
                        errors.push('Invoice ' + (index + 1) + ' harus memiliki qty barang.');
                    }
                });

                $.each(getSplitSourceRows(), function(_, row) {
                    var idDetail = parseInt(row.id_detail_lpb || 0, 10);
                    var sourceQty = getSplitRowQty(row);
                    var allocatedQty = 0;
                    $.each(payload.splits || [], function(_, split) {
                        $.each(split.details || [], function(_, detail) {
                            if ((parseInt(detail.id_detail_lpb || 0, 10) || 0) === idDetail) {
                                allocatedQty += parseFloat(detail.qty_diterima || 0) || 0;
                            }
                        });
                    });
                    if (Math.abs(allocatedQty - sourceQty) > 0.0001) {
                        errors.push('Total qty split barang ' + (row.kd_barang || '-') + ' harus sama dengan qty LPB awal.');
                    }
                });

                return errors;
            }

            function updateSplitInvoiceValidationInfo() {
                var payload = collectSplitPayload();
                var invoiceTotals = [];
                $('#splitInvoiceAllocationTable .js-split-invoice-total').each(function() {
                    invoiceTotals[parseInt($(this).data('index'), 10) || 0] = 0;
                });

                $('#splitInvoiceAllocationTable tbody tr[data-detail-id]').each(function() {
                    var sourceQty = parseFloat($(this).data('source-qty')) || 0;
                    var rowTotal = 0;
                    $(this).find('.js-split-qty').each(function() {
                        var index = parseInt($(this).data('index'), 10) || 0;
                        var qty = parseFloat($(this).val()) || 0;
                        rowTotal += qty;
                        invoiceTotals[index] = (invoiceTotals[index] || 0) + qty;
                    });
                    var diff = sourceQty - rowTotal;
                    $(this).find('.js-split-diff')
                        .toggleClass('text-danger font-weight-bold', Math.abs(diff) > 0.0001)
                        .text(formatNumber(diff));
                });

                $.each(invoiceTotals, function(index, total) {
                    $('#splitInvoiceAllocationTable .js-split-invoice-total[data-index="' + index + '"]').text(formatNumber(total || 0));
                });

                var errors = validateSplitPayload(payload);
                $('#splitInvoiceValidationInfo')
                    .toggleClass('text-danger', errors.length > 0)
                    .toggleClass('text-muted', errors.length === 0)
                    .text(errors.length > 0 ? errors[0] : 'Total qty setiap barang sudah balance.');
                return errors;
            }

            function openSplitInvoiceModal() {
                if (!selectedIdLpb || !selectedHeader) {
                    Swal.fire('Validasi', 'Silakan pilih LPB yang ingin dipecah terlebih dahulu.', 'warning');
                    return;
                }

                if (!isSelectedLpbUnpost()) {
                    Swal.fire('Validasi', 'Pecah LPB hanya bisa dilakukan saat status UNPOST.', 'warning');
                    return;
                }

                if (getSplitSourceRows().length === 0) {
                    Swal.fire('Validasi', 'Detail LPB belum tersedia untuk dipecah.', 'warning');
                    return;
                }

                $('#splitInvoiceIdLpb').val(selectedIdLpb);
                renderSplitInvoiceModal(buildDefaultSplitState());
                $('#modalSplitInvoice').modal('show');
            }

            function openUpdateFakturModal(header) {
                var activeHeader = header || selectedHeader;
                var activeId = activeHeader ? (activeHeader.id_lpb || selectedIdLpb) : selectedIdLpb;

                if (!activeId || !activeHeader) {
                    Swal.fire('Validasi', 'Silakan pilih LPB yang ingin di-update faktur pajaknya terlebih dahulu.', 'warning');
                    return;
                }

                $('#fakturIdLpb').val(activeId);
                $('#fakturKodePajak').val(activeHeader.kode_faktur_pajak || '');
                $('#fakturTanggalTerbit').val(activeHeader.tanggal_faktur_pajak || '');
                $('#modalUpdateFaktur').modal('show');
            }

            function updateJenisLpbFormatInfo() {
                var option = $('#jenisLpbSelect').find('option:selected');
                var example = option.data('example') || '-';
                $('#jenisLpbFormatInfo').html(
                    '<strong>Contoh format:</strong> ' + escHtml(example) +
                    '<br><span>7 = bulan, 26 = tahun, 00001 = urutan berdasarkan jenis yang dipilih.</span>'
                );
            }

            function openUpdateJenisLpbModal(header) {
                var activeHeader = header || selectedHeader;
                var activeId = activeHeader ? (activeHeader.id_lpb || selectedIdLpb) : selectedIdLpb;

                if (!activeId || !activeHeader) {
                    Swal.fire('Validasi', 'Silakan pilih LPB yang ingin di-update terlebih dahulu.', 'warning');
                    return;
                }

                $('#jenisIdLpb').val(activeId);
                $('#jenisNomorLpb').val(activeHeader.nomor_lpb || '');
                $('#jenisLpbSelect').val(activeHeader.jenis_lpb || 'LPB CP');
                if (!$('#jenisLpbSelect').val()) {
                    $('#jenisLpbSelect').val('LPB CP');
                }
                updateJenisLpbFormatInfo();
                $('#modalUpdateJenisLpb').modal('show');
            }

            function openUpdateLpbSjModal(header) {
                var activeHeader = header || selectedHeader;
                var activeId = activeHeader ? (activeHeader.id_lpb || selectedIdLpb) : selectedIdLpb;

                if (!activeId || !activeHeader) {
                    Swal.fire('Validasi', 'Silakan pilih LPB yang ingin di-update SJ terlebih dahulu.', 'warning');
                    return;
                }

                $('#sjIdLpb').val(activeId);
                $('#sjNomor').val(activeHeader.nosj || '');
                $('#sjTanggal').val(activeHeader.tgl_sj || '');
                $('#modalUpdateLpbSj').modal('show');
            }

            function openUpdateLpbDetailReceiptModal(btn) {
                var idDetail = btn.data('id-detail') || 0;
                if (!idDetail) {
                    Swal.fire('Validasi', 'Detail LPB tidak valid.', 'warning');
                    return;
                }
                if (!isSelectedLpbUnpost()) {
                    Swal.fire('Validasi', 'Detail LPB hanya bisa diedit saat status UNPOST.', 'warning');
                    return;
                }

                $('#detailReceiptIdDetail').val(idDetail);
                $('#detailReceiptBarang').val((btn.attr('data-kd-barang') || '') + ' - ' + (btn.attr('data-nama-barang') || '-'));
                $('#detailReceiptNoLot').val(btn.attr('data-no-lot') || '');
                $('#detailReceiptExpiredDate').val(btn.attr('data-expired-date') || '');
                $('#detailReceiptQty').val(btn.attr('data-qty') || 0);
                $('#modalUpdateLpbDetailReceipt').modal('show');
            }

            function openUpdateLpbPriceModal(btn) {
                var idDetail = btn.data('id-detail') || 0;
                var kdBarang = btn.attr('data-kd-barang') || '';
                var namaBarang = btn.attr('data-nama-barang') || '-';
                var qtyLpb = parseFloat(btn.attr('data-qty')) || 0;
                var qtySisa = parseFloat(btn.attr('data-qty-sisa')) || 0;
                var qtyOrder = parseFloat(btn.attr('data-qty-order')) || 0;
                var qtyTotal = parseFloat(btn.attr('data-qty-total')) || 0;
                var hasQtySisa = typeof btn.attr('data-qty-sisa') !== 'undefined' && btn.attr('data-qty-sisa') !== '';
                var qtyMax = qtyTotal > 0 ? qtyTotal : (hasQtySisa ? qtyLpb + qtySisa : 0);
                var hargaSatuan = parseFloat(btn.attr('data-harga-satuan')) || 0;
                var totalHarga = parseFloat(btn.attr('data-total-harga')) || 0;

                if (!idDetail) {
                    Swal.fire('Validasi', 'Detail LPB tidak valid.', 'warning');
                    return;
                }

                $('#lpbPriceIdDetail').val(idDetail);
                $('#lpbPriceBarang').val(kdBarang + ' - ' + namaBarang);
                $('#lpbPriceQty').val(formatInputNumber(qtyLpb));
                $('#lpbPriceQtyMax').val(qtyMax > 0 ? formatInputNumber(qtyMax) : '');
                $('#lpbPriceQtyOrder').val(qtyOrder > 0 ? formatInputNumber(qtyOrder) : '');
                $('#lpbPriceQty').removeAttr('max');
                if (qtyMax > 0) {
                    $('#lpbPriceQty').attr('max', formatInputNumber(qtyMax));
                    $('#lpbPriceQtyInfo').text('Maksimum Qty LPB untuk kode barang ini: ' + formatNumber(qtyMax) + '.');
                } else if (qtyOrder > 0) {
                    $('#lpbPriceQtyInfo').text('Qty diterima/order kode barang ini: ' + formatNumber(qtyOrder) + '.');
                } else {
                    $('#lpbPriceQtyInfo').text('Qty akan divalidasi ulang oleh server saat disimpan.');
                }
                $('#lpbPriceHargaSebelumnya').val(formatRupiah(hargaSatuan));
                $('#lpbPriceTotalSebelumnya').val(formatRupiah(totalHarga));
                $('#lpbPriceHargaBaru').val(hargaSatuan);
                updateLpbPriceTotal();
                $('#modalUpdateLpbPrice').modal('show');
            }

            function loadHistory(title, url, data) {
                $('#modalHistoryLpbLabel').text(title);
                $('#historyLpbContent').empty();
                $('#historyLpbLoading').show();
                $('#modalHistoryLpb').modal('show');

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    data: data,
                    success: function(res) {
                        $('#historyLpbLoading').hide();
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'History tidak dapat dimuat.', 'error');
                            return;
                        }

                        $('#historyLpbContent').html(res.html || '');
                    },
                    error: function() {
                        $('#historyLpbLoading').hide();
                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengambil history.', 'error');
                    }
                });
            }

            function applySearch() {
                var keyword = ($('#lpbSearchInput').val() || '').toLowerCase();
                var visibleCount = 0;

                $('.js-lpb-item').each(function() {
                    var haystack = ($(this).data('search') || '').toString();
                    var matched = keyword === '' || haystack.indexOf(keyword) !== -1;
                    $(this).toggle(matched);
                    if (matched) {
                        visibleCount++;
                    }
                });

                if (visibleCount === 0 && allRows.length > 0) {
                    $('#lpbListWrap').show();
                    $('#lpbListEmpty').hide();
                }
            }

            $(document).on('click', '.js-lpb-item', function() {
                var idLpb = $(this).data('id');
                selectListItem(idLpb);
                loadDetail(idLpb);
            });

            $(document).on('click', '.js-lpb-invoice-row', function() {
                var idLpb = $(this).data('id');
                selectListItem(idLpb);
                loadDetail(idLpb);
            });

            $(document).on('click', '.js-open-adjustment', function() {
                if (!canManagePoInvoice) {
                    return;
                }

                var btn = $(this);
                var kdBarang = btn.attr('data-kd-barang') || '';
                var namaBarang = btn.attr('data-nama-barang') || '-';

                $('#adjKdBarang').val(kdBarang);
                $('#adjNamaBarang').val(kdBarang + ' - ' + namaBarang);
                $('#adjQty').val(btn.data('qty') || 0);
                $('#adjHargaLamaText').val(formatRupiah(btn.data('harga-satuan') || 0));
                $('#adjHargaBaru').val(btn.data('harga-satuan') || 0);
                $('#adjAlasan').val('');
                updateAdjustmentTotal();
                $('#modalAdjustmentHarga').modal('show');
            });

            $(document).on('click', '.js-open-lpb-price', function() {
                openUpdateLpbPriceModal($(this));
            });

            $(document).on('click', '.js-open-lpb-detail-split', function() {
                openSplitLpbDetailModal($(this));
            });

            $(document).on('click', '.js-open-lpb-detail-receipt', function() {
                openUpdateLpbDetailReceiptModal($(this));
            });

            $(document).on('click', '.js-accept-lpb-price', function() {
                var idDetail = $(this).data('id-detail') || 0;

                if (!idDetail || isAcceptingLpbPrice) {
                    return;
                }

                isAcceptingLpbPrice = true;
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Accept...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_accept_lpb_detail_price') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_detail_lpb: idDetail
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Verifikasi harga gagal disimpan.', 'error');
                            return;
                        }

                        Swal.fire('Berhasil', res.message || 'Harga berhasil diverifikasi.', 'success');
                        loadDetail(selectedIdLpb);
                        loadList({
                            skipDetailReload: true
                        });
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat verifikasi harga.', 'error');
                    },
                    complete: function() {
                        isAcceptingLpbPrice = false;
                    }
                });
            });

            $('#btnHistoryInvoiceAll').on('click', function() {
                if (!canManagePoInvoice) {
                    return;
                }

                loadHistory(
                    'History Invoice',
                    '<?= base_url('ics/ajax_history_invoice') ?>', {
                        kd_po: kdPo
                    }
                );
            });

            $('#btnHistoryDiskonAll').on('click', function() {
                if (!canManagePoInvoice) {
                    return;
                }

                loadHistory(
                    'History Diskon',
                    '<?= base_url('ics/ajax_history_diskon') ?>', {
                        kd_po: kdPo
                    }
                );
            });

            $('#btnHistoryAdjustmentAll').on('click', function() {
                if (!canManagePoInvoice) {
                    return;
                }

                loadHistory(
                    'History Adjustment',
                    '<?= base_url('ics/ajax_history_adjustment') ?>', {
                        kd_po: kdPo
                    }
                );
            });

            $('#adjHargaBaru').on('input', function() {
                updateAdjustmentTotal();
            });

            $('#lpbPriceQty, #lpbPriceHargaBaru').on('input', function() {
                updateLpbPriceTotal();
            });

            $('#btnAddSplitDetailRow').on('click', function() {
                var hargaAwal = parseFloat($('#splitDetailHargaAwalRaw').val()) || 0;
                addSplitDetailRow('Split ' + splitDetailRowCounter, 0, hargaAwal, true);
            });

            $('#splitDetailRows').on('click', '.js-remove-split-detail-row', function() {
                $(this).closest('tr').remove();
                updateSplitDetailPreview();
            });

            $('#splitDetailRows').on('input change', '.js-split-detail-qty, .js-split-detail-harga', function() {
                updateSplitDetailPreview();
            });

            $('#formAdjustmentHarga').on('submit', function(e) {
                e.preventDefault();
                if (!canManagePoInvoice) {
                    return;
                }

                if (isSubmittingAdjustment) {
                    return;
                }

                if (!$.trim($('#adjAlasan').val())) {
                    Swal.fire('Validasi', 'Alasan adjustment wajib diisi.', 'warning');
                    return;
                }

                isSubmittingAdjustment = true;
                $('#btnSubmitAdjustment').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_submit_adjustment') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        kd_po: kdPo,
                        kd_barang: $('#adjKdBarang').val(),
                        harga_satuan_baru: $('#adjHargaBaru').val(),
                        alasan: $('#adjAlasan').val()
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Adjustment harga gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalAdjustmentHarga').modal('hide');
                        refreshPrePoAdjustmentHtml(res.html || '');
                        Swal.fire('Berhasil', res.message || 'Adjustment harga berhasil disimpan.', 'success');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan adjustment harga.', 'error');
                    },
                    complete: function() {
                        isSubmittingAdjustment = false;
                        $('#btnSubmitAdjustment').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Adjustment');
                    }
                });
            });

            $('#lpbSearchInput').on('input', function() {
                applySearch();
            });

            $('#btnReloadLpbPage').on('click', function() {
                loadList();
            });

            $('#btnReloadPrePoAdjustment').on('click', function() {
                if (!canManagePoInvoice) {
                    return;
                }

                loadPrePoAdjustment();
            });

            $('#btnUpdateInvoice').on('click', function() {
                openUpdateInvoiceModal();
            });

            $('#btnUpdateFaktur').on('click', function() {
                openUpdateFakturModal();
            });

            $('#btnUpdateJenisPo').on('click', function() {
                openUpdateJenisLpbModal();
            });

            $('#btnUpdateLpbIdentity').on('click', function() {
                openUpdateJenisLpbModal();
            });

            $('#btnUpdateLpbSj').on('click', function() {
                openUpdateLpbSjModal();
            });

            $('#btnToggleLpbLog').on('click', function() {
                if (!selectedIdLpb) {
                    return;
                }

                isActivityLogVisible = !isActivityLogVisible;
                updateActivityLogVisibility();
            });

            function changeLpbStatus(url, successMessage, extraData) {
                if (!selectedIdLpb || isChangingLpbStatus) {
                    return;
                }

                var previousDetailMode = detailViewMode;
                isChangingLpbStatus = true;
                updateWorkflowActions();
                updateBulkAcceptButton();
                var payload = $.extend({
                    id_lpb: selectedIdLpb
                }, extraData || {});

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: payload,
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Status LPB gagal diperbarui.', 'error');
                            return;
                        }

                        Swal.fire('Berhasil', res.message || successMessage, 'success');
                        loadList({
                            skipDetailReload: true
                        });
                        if (previousDetailMode === 'purchasing') {
                            loadPurchasingDetailView();
                        } else {
                            loadDetail(selectedIdLpb);
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat memperbarui status LPB.', 'error');
                    },
                    complete: function() {
                        isChangingLpbStatus = false;
                        updateWorkflowActions();
                        updateBulkAcceptButton();
                    }
                });
            }

            function promptUnpostLpb() {
                if (!selectedIdLpb) {
                    return;
                }

                Swal.fire({
                    title: 'UNPOST LPB?',
                    text: 'Data LPB akan dibuka untuk pembaruan.',
                    icon: 'warning',
                    input: 'textarea',
                    inputLabel: 'Keterangan',
                    inputPlaceholder: 'Tulis alasan UNPOST LPB',
                    inputAttributes: {
                        'aria-label': 'Keterangan UNPOST LPB'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Ya, UNPOST',
                    cancelButtonText: 'Batal',
                    inputValidator: function(value) {
                        if (!$.trim(value || '')) {
                            return 'Keterangan UNPOST wajib diisi.';
                        }
                        return null;
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        changeLpbStatus('<?= base_url('ics/ajax_unpost_lpb') ?>', 'LPB berhasil di-UNPOST.', {
                            keterangan: result.value
                        });
                    }
                });
            }

            $('#btnUnpostLpb').on('click', function() {
                promptUnpostLpb();
            });

            $('#btnPostLpb').on('click', function() {
                changeLpbStatus('<?= base_url('ics/ajax_post_lpb') ?>', 'LPB berhasil direkam menjadi POST.');
            });

            $('#btnPurchasingUnpostLpb').on('click', function() {
                promptUnpostLpb();
            });

            $('#btnPurchasingPostLpb').on('click', function() {
                changeLpbStatus('<?= base_url('ics/ajax_post_lpb') ?>', 'LPB berhasil direkam menjadi POST.');
            });

            $('#btnSplitInvoice').on('click', function() {
                openSplitInvoiceModal();
            });

            $('#btnAddSplitInvoice').on('click', function() {
                addSplitInvoiceRow();
            });

            $('#splitInvoiceRows').on('click', '.js-remove-split-invoice', function() {
                removeSplitInvoiceRow(parseInt($(this).data('index'), 10) || 0);
            });

            $('#splitInvoiceRows').on('input change', '.js-split-no-invoice, .js-split-tanggal-invoice', function() {
                updateSplitInvoiceValidationInfo();
            });

            $('#splitInvoiceAllocationTable').on('input change', '.js-split-qty', function() {
                updateSplitInvoiceValidationInfo();
            });

            $('#formUpdateInvoice').on('submit', function(e) {
                e.preventDefault();

                if (isSubmittingInvoice) {
                    return;
                }

                isSubmittingInvoice = true;
                $('#btnSubmitInvoice').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_update_invoice') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_lpb: $('#invoiceIdLpb').val(),
                        no_invoice: $('#invoiceNo').val(),
                        tanggal_invoice: $('#invoiceTanggalTerbit').val()
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Update invoice gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalUpdateInvoice').modal('hide');
                        Swal.fire('Berhasil', res.message || 'Invoice LPB berhasil diperbarui.', 'success');
                        loadList();
                        if (selectedIdLpb) {
                            loadDetail(selectedIdLpb);
                        }
                        loadPrePoAdjustment();
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan invoice LPB.', 'error');
                    },
                    complete: function() {
                        isSubmittingInvoice = false;
                        $('#btnSubmitInvoice').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Invoice');
                    }
                });
            });

            $('#formSplitInvoice').on('submit', function(e) {
                e.preventDefault();

                if (isSubmittingSplitInvoice) {
                    return;
                }

                var payload = collectSplitPayload();
                var errors = validateSplitPayload(payload);
                if (errors.length > 0) {
                    Swal.fire('Validasi', errors[0], 'warning');
                    updateSplitInvoiceValidationInfo();
                    return;
                }

                isSubmittingSplitInvoice = true;
                $('#btnSubmitSplitInvoice').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_split_lpb_multiple_invoice') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_lpb: payload.id_lpb,
                        splits: JSON.stringify(payload.splits)
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Pecah LPB multiple invoice gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalSplitInvoice').modal('hide');
                        Swal.fire('Berhasil', res.message || 'LPB berhasil dipecah menjadi multiple invoice.', 'success');
                        loadList();
                        if (selectedIdLpb) {
                            loadPurchasingDetailView();
                        }
                        loadPrePoAdjustment();
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan split invoice.', 'error');
                    },
                    complete: function() {
                        isSubmittingSplitInvoice = false;
                        $('#btnSubmitSplitInvoice').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Split Invoice');
                    }
                });
            });

            $('#formUpdateFaktur').on('submit', function(e) {
                e.preventDefault();

                if (isSubmittingFaktur) {
                    return;
                }

                isSubmittingFaktur = true;
                $('#btnSubmitFaktur').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_update_faktur_pajak') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_lpb: $('#fakturIdLpb').val(),
                        kode_faktur_pajak: $('#fakturKodePajak').val(),
                        tanggal_faktur_pajak: $('#fakturTanggalTerbit').val()
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Update faktur pajak gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalUpdateFaktur').modal('hide');
                        Swal.fire('Berhasil', res.message || 'Faktur pajak berhasil diperbarui.', 'success');
                        loadList();
                        if (selectedIdLpb) {
                            loadDetail(selectedIdLpb);
                        }
                        loadPrePoAdjustment();
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan faktur pajak.', 'error');
                    },
                    complete: function() {
                        isSubmittingFaktur = false;
                        $('#btnSubmitFaktur').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Faktur');
                    }
                });
            });

            $('#jenisLpbSelect').on('change', function() {
                updateJenisLpbFormatInfo();
            });

            $('#formUpdateJenisLpb').on('submit', function(e) {
                e.preventDefault();

                if (isSubmittingJenisLpb) {
                    return;
                }

                isSubmittingJenisLpb = true;
                $('#btnSubmitJenisLpb').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_update_lpb_identity') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_lpb: $('#jenisIdLpb').val(),
                        nomor_lpb: $('#jenisNomorLpb').val(),
                        jenis_lpb: $('#jenisLpbSelect').val()
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Update nomor dan jenis LPB gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalUpdateJenisLpb').modal('hide');
                        Swal.fire('Berhasil', (res.message || 'Nomor dan jenis LPB berhasil diperbarui.') + (res.nomor_lpb ? ' Nomor LPB: ' + res.nomor_lpb : ''), 'success');
                        loadList();
                        if (selectedIdLpb) {
                            loadDetail(selectedIdLpb);
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan nomor dan jenis LPB.', 'error');
                    },
                    complete: function() {
                        isSubmittingJenisLpb = false;
                        $('#btnSubmitJenisLpb').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan LPB');
                    }
                });
            });

            $('#formUpdateLpbSj').on('submit', function(e) {
                e.preventDefault();

                if (isSubmittingLpbSj) {
                    return;
                }

                isSubmittingLpbSj = true;
                $('#btnSubmitLpbSj').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_update_lpb_sj') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_lpb: $('#sjIdLpb').val(),
                        nosj: $('#sjNomor').val(),
                        tgl_sj: $('#sjTanggal').val()
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Update SJ gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalUpdateLpbSj').modal('hide');
                        Swal.fire('Berhasil', res.message || 'Nomor SJ dan tanggal SJ berhasil diperbarui.', 'success');
                        loadList();
                        if (selectedIdLpb) {
                            loadDetail(selectedIdLpb);
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan SJ LPB.', 'error');
                    },
                    complete: function() {
                        isSubmittingLpbSj = false;
                        $('#btnSubmitLpbSj').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan SJ');
                    }
                });
            });

            $('#formUpdateLpbDetailReceipt').on('submit', function(e) {
                e.preventDefault();

                if (isSubmittingLpbDetailReceipt) {
                    return;
                }

                isSubmittingLpbDetailReceipt = true;
                $('#btnSubmitLpbDetailReceipt').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_update_lpb_detail_receipt') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_detail_lpb: $('#detailReceiptIdDetail').val(),
                        no_lot: $('#detailReceiptNoLot').val(),
                        expired_date: $('#detailReceiptExpiredDate').val(),
                        qty_diterima: $('#detailReceiptQty').val()
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Update detail LPB gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalUpdateLpbDetailReceipt').modal('hide');
                        Swal.fire('Berhasil', res.message || 'Detail LPB berhasil diperbarui.', 'success');
                        loadList({
                            skipDetailReload: true
                        });
                        if (selectedIdLpb) {
                            loadDetail(selectedIdLpb);
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan detail LPB.', 'error');
                    },
                    complete: function() {
                        isSubmittingLpbDetailReceipt = false;
                        $('#btnSubmitLpbDetailReceipt').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Detail');
                    }
                });
            });

            $('#formSplitLpbDetail').on('submit', function(e) {
                e.preventDefault();

                if (isSubmittingLpbDetailSplit) {
                    return;
                }

                var validationMessage = updateSplitDetailPreview();
                if (validationMessage) {
                    Swal.fire('Validasi Split LPB', validationMessage, 'warning');
                    return;
                }

                isSubmittingLpbDetailSplit = true;
                var previousDetailMode = detailViewMode;
                $('#btnSubmitSplitDetail').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_split_lpb_detail') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_detail_lpb: $('#splitDetailIdDetail').val(),
                        splits: JSON.stringify(collectSplitDetailRows()),
                        keterangan: $('#splitDetailKeterangan').val()
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Split detail LPB gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalSplitLpbDetail').modal('hide');
                        Swal.fire('Berhasil', res.message || 'Detail LPB berhasil di-split.', 'success');
                        loadList({
                            skipDetailReload: true
                        });
                        if (selectedIdLpb) {
                            if (previousDetailMode === 'purchasing') {
                                loadPurchasingDetailView();
                            } else {
                                loadDetail(selectedIdLpb);
                            }
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan split detail LPB.', 'error');
                    },
                    complete: function() {
                        isSubmittingLpbDetailSplit = false;
                        $('#btnSubmitSplitDetail').prop('disabled', false).html('<i class="fas fa-code-branch mr-1"></i> Simpan Split');
                    }
                });
            });

            $('#formUpdateLpbPrice').on('submit', function(e) {
                e.preventDefault();

                if (isSubmittingLpbPrice) {
                    return;
                }

                var qtyMessage = validateLpbPriceQtyInput();
                if (qtyMessage) {
                    Swal.fire('Validasi Qty LPB', qtyMessage, 'warning');
                    return;
                }

                isSubmittingLpbPrice = true;
                var previousDetailMode = detailViewMode;
                $('#btnSubmitLpbPrice').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_update_lpb_detail_price') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_detail_lpb: $('#lpbPriceIdDetail').val(),
                        qty_lpb: $('#lpbPriceQty').val(),
                        harga_satuan_baru: $('#lpbPriceHargaBaru').val()
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Update harga detail LPB gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalUpdateLpbPrice').modal('hide');
                        Swal.fire(res.warning ? 'Berhasil dengan Catatan' : 'Berhasil', res.message || 'Harga dan Qty LPB berhasil diperbarui.', res.warning ? 'warning' : 'success');
                        if (selectedIdLpb) {
                            if (previousDetailMode === 'purchasing') {
                                loadPurchasingDetailView();
                            } else {
                                loadDetail(selectedIdLpb);
                            }
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan harga detail LPB.', 'error');
                    },
                    complete: function() {
                        isSubmittingLpbPrice = false;
                        $('#btnSubmitLpbPrice').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Harga & Qty');
                    }
                });
            });

            $('#btnPrintSelectedLpb').on('click', function() {
                printSelectedLpb();
            });

            $('#btnPrintAllLpb').on('click', function() {
                printAllLpb();
            });

            if (canManagePoInvoice && showPrePoAdjustmentPanel) {
                loadPrePoAdjustment();
            }
            loadList();
        });
    </script>
</body>
