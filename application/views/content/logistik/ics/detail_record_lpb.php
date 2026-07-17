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
                            border-color: #243cff;
                            vertical-align: middle;
                            white-space: nowrap;
                        }

                        .lpb-table th,
                        .lpb-table td {
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
                            <a href="<?= !empty($is_admin_po) ? base_url('ics/icspo') : base_url('ics/detail_po?no_po=' . urlencode($no_po ?? '') . '&kd_suplier=' . urlencode($kd_suplier ?? '')) ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Detail PO
                            </a>
                        </div>
                    </div>

                    <div class="lpb-hero mb-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h3 class="mb-1 font-weight-bold">Record Semua Data LPB</h3>
                            </div>
                            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                                <button type="button" class="btn btn-success mb-2" id="btnPrintAllLpb">
                                    <i class="fas fa-print mr-1"></i> Cetak Semua Faktur LPB
                                </button>
                                <br>
                                <div class="h3 font-weight-bold mb-1"><?= htmlspecialchars($no_po ?? '-') ?></div>
                                <div class="small">No PO: <?= htmlspecialchars($kd_po ?? '-') ?></div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($is_admin_po)) : ?>
                    <div class="lpb-panel mb-4" id="prePoAdjustmentPanel">
                        <div class="lpb-panel-header">
                            <div>
                                <h3 class="card-title mb-0 font-weight-bold">LPB Invoice & Adjustment Harga</h3>
                            </div>
                            <div class="d-flex align-items-center" style="gap:8px; flex-wrap:wrap;">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnHistoryInvoiceAll">
                                    <i class="fas fa-file-invoice mr-1"></i> History Invoice
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" id="btnHistoryDiskonAll">
                                    <i class="fas fa-tags mr-1"></i> History Diskon
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm" id="btnHistoryAdjustmentAll">
                                    <i class="fas fa-history mr-1"></i> History Adjustment
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnReloadPrePoAdjustment">
                                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="lpb-panel-body">
                            <div id="prePoAdjustmentLoading" class="lpb-loading-state">
                                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                                <div>Memuat data PRE PO...</div>
                            </div>
                            <div id="prePoAdjustmentContainer" style="display:none;"></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-3 mb-4">
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

                        <div class="col-lg-9 mb-4">
                            <div class="lpb-panel h-100">
                                <div class="lpb-panel-header">
                                    <div>
                                        <h3 class="card-title mb-0 font-weight-bold">Detail LPB</h3>
                                    </div>
                                    <div class="d-flex align-items-center" style="gap:10px;">
                                        <button type="button" class="btn btn-primary btn-sm" id="btnUpdateInvoice">
                                            <i class="fas fa-file-invoice mr-1"></i> Update Invoice
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm" id="btnUpdateFaktur">
                                            <i class="fas fa-receipt mr-1"></i> Update Faktur
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnPrintSelectedLpb">
                                            <i class="fas fa-print mr-1"></i> Cetak Faktur LPB
                                        </button>
                                        <div class="text-muted small" id="selectedLpbText">Belum ada LPB dipilih</div>
                                    </div>
                                </div>
                                <div class="lpb-panel-body">
                                    <div id="lpbDetailLoading" class="lpb-loading-state" style="display:none;">
                                        <i class="fas fa-spinner fa-spin fa-2x text-success mb-2"></i>
                                        <div>Memuat detail LPB...</div>
                                    </div>

                                    <div id="lpbDetailEmpty" class="lpb-empty-state">
                                        <i class="fas fa-receipt fa-2x mb-2"></i>
                                        <div>Pilih salah satu LPB di panel kiri untuk melihat detailnya.</div>
                                    </div>

                                    <div id="lpbDetailWrap" style="display:none;">
                                        <div class="lpb-detail-grid" id="lpbDetailHeaderGrid"></div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover lpb-table" id="lpbDetailTable">
                                                <thead>
                                                    <tr>
                                                        <th>Kode Barang</th>
                                                        <th>Nama Barang</th>
                                                        <th>No Lot</th>
                                                        <th class="text-center">Expired Date</th>
                                                        <th class="text-right">Qty Order</th>
                                                        <th class="text-right">Qty LPB</th>
                                                        <th class="text-right">Total Harga</th>
                                                        <th class="text-right">Harga Satuan</th>
                                                        <th class="text-center">#</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                        <div class="lpb-table-actions" id="lpbPurchasingVerifyActions" style="display:none;">
                                            <span class="text-muted small" id="lpbBulkVerifyInfo"></span>
                                            <button type="button" class="btn btn-success btn-sm" id="btnBulkAcceptLpbPrice">
                                                <i class="fas fa-save mr-1"></i> Rekam
                                            </button>
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
                    <h5 class="modal-title" id="modalUpdateJenisLpbLabel">Edit Jenis PO / LPB</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="jenisIdLpb" name="id_lpb">
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
                    <div class="form-group" id="jenisNomorLpbCurrentGroup">
                        <label>Nomor LPB Saat Ini</label>
                        <input type="text" class="form-control" id="jenisNomorLpbCurrent" readonly>
                    </div>
                    <div class="alert alert-info mb-0" id="jenisLpbFormatInfo">
                        Format mengikuti bulan, tahun, dan urutan berdasarkan jenis yang dipilih.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitJenisLpb">
                        <i class="fas fa-save mr-1"></i> Simpan Jenis PO
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
                        <label>Barang</label>
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
                                <input type="number" class="form-control" id="lpbPriceQty" readonly>
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
                    <div class="alert alert-info mb-0">
                        Harga yang tersimpan saat ini akan menjadi harga sebelumnya, dan harga baru akan menjadi harga aktif detail LPB.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnSubmitLpbPrice">
                        <i class="fas fa-save mr-1"></i> Simpan Harga
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function() {
            var kdPo = '<?= htmlspecialchars($kd_po ?? '', ENT_QUOTES) ?>';
            var canManagePoInvoice = <?= !empty($is_admin_po) ? 'true' : 'false' ?>;
            var allRows = [];
            var selectedIdLpb = 0;
            var selectedHeader = null;
            var selectedDetailRows = [];
            var selectedPurchasingRows = [];
            var detailViewMode = 'lpb';
            var purchasingEditMode = false;
            var isSubmittingAdjustment = false;
            var isSubmittingInvoice = false;
            var isSubmittingFaktur = false;
            var isSubmittingJenisLpb = false;
            var isSubmittingLpbPrice = false;
            var isAcceptingLpbPrice = false;
            var isBulkAcceptingLpbPrice = false;

            function escHtml(value) {
                return $('<div>').text(value == null ? '' : value).html();
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
                var code = parseInt(status, 10) || 1;
                var map = {
                    1: {
                        label: 'S1 Draft',
                        badge: 'badge-danger'
                    },
                    2: {
                        label: 'S2 Nomor Ada',
                        badge: 'badge-warning'
                    },
                    3: {
                        label: 'S3 Invoice Ada',
                        badge: 'badge-info'
                    },
                    4: {
                        label: 'S4 Siap Jurnal',
                        badge: 'badge-success'
                    }
                };

                return map[code] || map[1];
            }

            function loadPrePoAdjustment() {
                if (!canManagePoInvoice) {
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
                    '<div class="lpb-list-item js-lpb-item" data-id="' + escHtml(row.id_lpb) + '" data-search="' + escHtml((nomorLpb + ' ' + invoice + ' ' + kodeFaktur + ' ' + jenisLpb + ' status ' + (row.status_lpb || 1) + ' ' + nomorSj).toLowerCase()) + '">' +
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

            function renderList(rows, options) {
                options = options || {};
                var container = $('#lpbListContainer');
                container.empty();

                if (!rows || rows.length === 0) {
                    $('#lpbListLoading').hide();
                    $('#lpbListWrap').hide();
                    $('#lpbListEmpty').show();
                    resetDetailState();
                    updateStats([]);
                    return;
                }

                $.each(rows, function(_, row) {
                    container.append(buildListItem(row));
                });

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
                detailViewMode = 'lpb';
                purchasingEditMode = false;
                updateDetailViewButton();
                $('#selectedLpbText').text('Belum ada LPB dipilih');
                $('#lpbDetailLoading').hide();
                $('#lpbDetailWrap').hide();
                $('#lpbDetailEmpty').show();
                $('#lpbDetailHeaderGrid').empty();
                $('#lpbDetailTable tbody').empty();
                selectedPurchasingRows = [];
                updateBulkAcceptButton();
            }

            function selectListItem(idLpb) {
                selectedIdLpb = parseInt(idLpb, 10) || 0;
                $('.js-lpb-item').removeClass('active');
                $('.js-lpb-item[data-id="' + selectedIdLpb + '"]').addClass('active');
            }

            function updateDetailViewButton() {
                return;
            }

            function setDetailTableHead(columns) {
                var html = '<tr>';
                $.each(columns, function(_, col) {
                    html += '<th' + (col.className ? ' class="' + col.className + '"' : '') + '>' + escHtml(col.label) + '</th>';
                });
                html += '</tr>';
                $('#lpbDetailTable thead').html(html);
            }

            function renderDetailHeader(header) {
                var html = '';
                var nomorJenisLpb = (header.nomor_lpb || 'Nomor LPB belum dibuat') + ' / ' + (header.jenis_lpb || 'Jenis LPB belum ditentukan');
                var statusInfo = lpbStatusInfo(header.status_lpb);
                var boxes = [{
                        label: 'Nomor / Jenis LPB',
                        value: nomorJenisLpb
                    },
                    {
                        label: 'Status LPB',
                        value: statusInfo.label
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

            function renderDetailTable(rows) {
                selectedPurchasingRows = $.map(rows || [], function(row) {
                    return {
                        id_detail_lpb: row.id_detail_lpb || 0,
                        kd_barang: row.kd_barang || '',
                        nama_barang: row.nama_barang || '-',
                        no_lot: row.no_lot || '-',
                        expired_date: formatDateId(row.expired_date),
                        qty_order: row.qty_order || 0,
                        qty_lpb: row.qty_diterima || 0,
                        total_harga: row.total_harga || 0,
                        harga_satuan: row.harga_satuan || 0,
                        harga_terverifikasi: row.harga_terverifikasi || 0
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
                        showLpbDetailView();
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
                        kd_po: kdPo
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
                        updateDetailViewButton();
                        renderPurchasingHeader(res.header || {});
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
                var acceptableRows = getBulkAcceptableRows();
                var allVerified = selectedPurchasingRows.length > 0 && acceptableRows.length === 0;
                var columns = [{
                        label: 'Kode Barang'
                    },
                    {
                        label: 'Nama Barang'
                    },
                    {
                        label: 'No Lot'
                    },
                    {
                        label: 'Expired Date',
                        className: 'text-center'
                    },
                    {
                        label: 'Qty Order',
                        className: 'text-right'
                    },
                    {
                        label: 'Qty LPB',
                        className: 'text-right'
                    },
                    {
                        label: 'Total Harga',
                        className: 'text-right'
                    },
                    {
                        label: 'Harga Satuan',
                        className: 'text-right'
                    },
                    {
                        label: '#',
                        className: 'text-center'
                    }
                ];

                setDetailTableHead(columns);

                var tbody = $('#lpbDetailTable tbody');
                tbody.empty();

                if (!rows || rows.length === 0) {
                    tbody.html('<tr><td colspan="9" class="text-center text-muted">Detail LPB kosong.</td></tr>');
                    updateBulkAcceptButton();
                    return;
                }

                $.each(rows, function(index, row) {
                    var hargaSatuanAktif = row.harga_satuan || row.harga_satuan_exclude || 0;
                    var totalHargaAktif = row.total_harga || row.total_harga_exclude || 0;
                    var hargaVerified = parseInt(row.harga_terverifikasi || 0, 10) === 1;
                    var actionColumn = (hargaVerified && !purchasingEditMode) ? (
                        '<td class="text-center"><span class="badge badge-success">Accepted</span></td>'
                    ) : (
                        '<td class="text-center">' +
                        '<button type="button" class="btn btn-warning btn-sm js-open-lpb-price" title="Update harga LPB" ' +
                        'data-id-detail="' + escHtml(row.id_detail_lpb || 0) + '" ' +
                        'data-kd-barang="' + escHtml(row.kd_barang || '') + '" ' +
                        'data-nama-barang="' + escHtml(row.nama_barang || '-') + '" ' +
                        'data-qty="' + escHtml(row.qty_lpb || 0) + '" ' +
                        'data-harga-satuan="' + escHtml(hargaSatuanAktif) + '" ' +
                        'data-total-harga="' + escHtml(totalHargaAktif) + '">' +
                        '<i class="fas fa-pencil-alt"></i>' +
                        '</button>' +
                        '</td>'
                    );

                    tbody.append(
                        '<tr>' +
                        '<td>' + escHtml(row.kd_barang || '-') + '</td>' +
                        '<td>' + escHtml(row.nama_barang || '-') + '</td>' +
                        '<td>' + escHtml(row.no_lot || '-') + '</td>' +
                        '<td class="text-center">' + escHtml(row.expired_date || '-') + '</td>' +
                        '<td class="text-right">' + escHtml(formatNumber(row.qty_order || 0)) + '</td>' +
                        '<td class="text-right">' + escHtml(formatNumber(row.qty_lpb || 0)) + '</td>' +
                        '<td class="text-right">' + escHtml(formatRupiah(totalHargaAktif)) + '</td>' +
                        '<td class="text-right">' + escHtml(formatRupiah(hargaSatuanAktif)) + '</td>' +
                        actionColumn +
                        '</tr>'
                    );
                });

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
                $('#selectedLpbText').text('Nomor LPB: ' + ((selectedHeader || {}).nomor_lpb || 'belum dibuat'));
            }

            function getBulkAcceptableRows() {
                return $.grep(selectedPurchasingRows || [], function(row) {
                    var idDetail = parseInt(row.id_detail_lpb || 0, 10);
                    var hargaVerified = parseInt(row.harga_terverifikasi || 0, 10) === 1;
                    var hargaSatuanAktif = parseFloat(row.harga_satuan || row.harga_satuan_exclude || 0);
                    var totalHargaAktif = parseFloat(row.total_harga || row.total_harga_exclude || 0);

                    return idDetail > 0 && !hargaVerified && hargaSatuanAktif > 0 && totalHargaAktif > 0;
                });
            }

            function updateBulkAcceptButton() {
                if (!selectedPurchasingRows || selectedPurchasingRows.length === 0) {
                    $('#lpbPurchasingVerifyActions').hide();
                    return;
                }

                var rows = selectedPurchasingRows || [];
                var acceptableRows = getBulkAcceptableRows();
                var allVerified = rows.length > 0 && acceptableRows.length === 0;
                var isEditButton = allVerified;

                $('#lpbPurchasingVerifyActions').show();
                $('#lpbBulkVerifyInfo').text('');
                $('#btnBulkAcceptLpbPrice')
                    .toggleClass('btn-success', !isEditButton)
                    .toggleClass('btn-warning', isEditButton)
                    .prop('disabled', (purchasingEditMode && allVerified) || (!isEditButton && acceptableRows.length === 0) || isBulkAcceptingLpbPrice)
                    .html(isBulkAcceptingLpbPrice ? '<i class="fas fa-spinner fa-spin mr-1"></i> Rekam...' : (isEditButton ? '<i class="fas fa-pencil-alt mr-1"></i> Edit' : '<i class="fas fa-save mr-1"></i> Rekam'));
            }

            function bulkAcceptDisplayedLpbPrices() {
                var rows = selectedPurchasingRows || [];
                var acceptableRows = getBulkAcceptableRows();
                var allVerified = rows.length > 0 && acceptableRows.length === 0;

                if (allVerified && !purchasingEditMode) {
                    purchasingEditMode = true;
                    renderPurchasingTable(selectedPurchasingRows);
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
                        loadDetail(selectedIdLpb);
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
                $('#jenisLpbSelect').val(activeHeader.jenis_lpb || 'LPB CP');
                if (!$('#jenisLpbSelect').val()) {
                    $('#jenisLpbSelect').val('LPB CP');
                }
                var currentNomorLpb = $.trim(activeHeader.nomor_lpb || '');
                if (currentNomorLpb !== '') {
                    $('#jenisNomorLpbCurrentGroup').show();
                    $('#jenisNomorLpbCurrent').val(currentNomorLpb);
                } else {
                    $('#jenisNomorLpbCurrentGroup').hide();
                    $('#jenisNomorLpbCurrent').val('');
                }
                updateJenisLpbFormatInfo();
                $('#modalUpdateJenisLpb').modal('show');
            }

            function openUpdateLpbPriceModal(btn) {
                var idDetail = btn.data('id-detail') || 0;
                var kdBarang = btn.attr('data-kd-barang') || '';
                var namaBarang = btn.attr('data-nama-barang') || '-';
                var hargaSatuan = parseFloat(btn.attr('data-harga-satuan')) || 0;
                var totalHarga = parseFloat(btn.attr('data-total-harga')) || 0;

                if (!idDetail) {
                    Swal.fire('Validasi', 'Detail LPB tidak valid.', 'warning');
                    return;
                }

                $('#lpbPriceIdDetail').val(idDetail);
                $('#lpbPriceBarang').val(kdBarang + ' - ' + namaBarang);
                $('#lpbPriceQty').val(btn.attr('data-qty') || 0);
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

            $('#btnBulkAcceptLpbPrice').on('click', function() {
                bulkAcceptDisplayedLpbPrices();
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

            $('#lpbPriceHargaBaru').on('input', function() {
                updateLpbPriceTotal();
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
                    url: '<?= base_url('ics/ajax_update_lpb_type') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_lpb: $('#jenisIdLpb').val(),
                        jenis_lpb: $('#jenisLpbSelect').val()
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Update jenis LPB gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalUpdateJenisLpb').modal('hide');
                        Swal.fire('Berhasil', (res.message || 'Jenis LPB berhasil diperbarui.') + (res.nomor_lpb ? ' Nomor LPB: ' + res.nomor_lpb : ''), 'success');
                        loadList();
                        if (selectedIdLpb) {
                            loadDetail(selectedIdLpb);
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan jenis LPB.', 'error');
                    },
                    complete: function() {
                        isSubmittingJenisLpb = false;
                        $('#btnSubmitJenisLpb').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Jenis PO');
                    }
                });
            });

            $('#formUpdateLpbPrice').on('submit', function(e) {
                e.preventDefault();

                if (isSubmittingLpbPrice) {
                    return;
                }

                isSubmittingLpbPrice = true;
                $('#btnSubmitLpbPrice').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_update_lpb_detail_price') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id_detail_lpb: $('#lpbPriceIdDetail').val(),
                        harga_satuan_baru: $('#lpbPriceHargaBaru').val()
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Update harga detail LPB gagal disimpan.', 'error');
                            return;
                        }

                        $('#modalUpdateLpbPrice').modal('hide');
                        Swal.fire('Berhasil', res.message || 'Harga detail LPB berhasil diperbarui.', 'success');
                        if (selectedIdLpb) {
                            loadDetail(selectedIdLpb);
                        }
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan harga detail LPB.', 'error');
                    },
                    complete: function() {
                        isSubmittingLpbPrice = false;
                        $('#btnSubmitLpbPrice').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Harga');
                    }
                });
            });

            $('#btnPrintSelectedLpb').on('click', function() {
                printSelectedLpb();
            });

            $('#btnPrintAllLpb').on('click', function() {
                printAllLpb();
            });

            if (canManagePoInvoice) {
                loadPrePoAdjustment();
            }
            loadList();
        });
    </script>
</body>
