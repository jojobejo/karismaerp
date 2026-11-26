<style>
    .select2-container { width: 100% !important; }
    .select2-selection--single {
        height: calc(2.25rem + 2px) !important;
        padding: .375rem .75rem;
    }
    .select2-selection__rendered {
        line-height: 1.5 !important;
    }
    .return-table th, .return-table td { vertical-align: middle !important; }
    .status-pill { font-size: 11px; letter-spacing: 0.3px; padding: 4px 8px; }
    .draft-settlement-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 15px;
    }
    .draft-settlement-box.disabled-panel {
        opacity: 0.65;
        pointer-events: none;
    }
    .info-gudang-badge {
        font-size: 12px;
        padding: 5px 10px;
        border-radius: 4px;
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
                    <div class="row mb-2 align-items-center">
                        <div class="col-auto">
                            <a href="<?= base_url('ics/retur') ?>" class="btn btn-primary"><i class="fas fa-home"></i></a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/retur/pembelian/monitoring') ?>" class="btn btn-info"><i class="fas fa-tasks mr-1"></i> Progres/Monitor Retur</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/retur/pembelian/adjustment') ?>" class="btn btn-warning"><i class="fas fa-balance-scale mr-1"></i> Adjustment Harga LPB</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/mutasi_barang/input?tujuangdg=' . (int)($gudang_retur_id ?? 0)) ?>" class="btn btn-outline-primary">
                                <i class="fas fa-exchange-alt mr-1"></i> Mutasi ke <?= html_escape($gudang_retur_nama ?? 'Gdg. Retur Pembelian') ?>
                            </a>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="fas fa-undo-alt mr-1"></i> Modul Retur Pembelian</strong>
                            </div>
                            <div>
                                <span class="badge badge-light text-primary info-gudang-badge">
                                    <i class="fas fa-warehouse mr-1"></i> Sumber Stok: <strong><?= html_escape($gudang_retur_nama ?? 'Gdg. Retur Pembelian') ?></strong> (ID: <?= (int)($gudang_retur_id ?? 0) ?>)
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="gudang_retur_id" value="<?= (int)($gudang_retur_id ?? 0) ?>">

                            <div class="row">
                                <!-- KOLOM SEBELAH KIRI: Form Pilih Supplier, Cari Barang (Gudang Retur Pembelian), Nested No Lot & Expired Date, Qty Satuan Kecil -->
                                <div class="col-lg-4">
                                    <div class="card border mb-0">
                                        <div class="card-header bg-light py-2">
                                            <strong class="text-dark"><i class="fas fa-edit mr-1 text-primary"></i> Form Input Barang Retur</strong>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label class="font-weight-bold">1. Pilih Supplier <span class="text-danger">*</span></label>
                                                <select id="select_supplier_retur" class="form-control"></select>
                                                <small class="form-text text-muted" id="supplier_lock_hint">Pilih supplier tujuan retur pembelian terlebih dahulu.</small>
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">2. Cari Nama Barang <span class="text-danger">*</span></label>
                                                <select id="select_barang_retur" class="form-control" disabled></select>
                                                <input type="hidden" id="kode_barang_retur" value="">
                                                <small class="form-text text-muted">Menampilkan barang berdasarkan stok tersedia di <strong><?= html_escape($gudang_retur_nama ?? 'Gdg. Retur Pembelian') ?></strong>.</small>
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">3. No Lot <span class="text-danger">*</span></label>
                                                <select id="select_lot_retur" class="form-control" disabled></select>
                                                <small class="form-text text-muted">No Lot berdasarkan nama barang &amp; gudang retur pembelian.</small>
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">4. Expired Date <span class="text-danger">*</span></label>
                                                <select id="select_exp_retur" class="form-control" disabled></select>
                                                <small class="form-text text-muted">Menyesuaikan dengan No Lot yang dipilih.</small>
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">5. Qty Retur (Satuan Kecil) <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" id="qty_retur_input" class="form-control" min="0.01" step="0.01" placeholder="Masukkan qty satuan kecil..." disabled>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text font-weight-bold bg-light" id="label_satuan_kecil">PCS</span>
                                                    </div>
                                                </div>
                                                <small class="form-text text-info font-weight-bold" id="hint_stok_batch">Stok tersedia batch: -</small>
                                            </div>

                                            <button type="button" id="btn_add_draft_item" class="btn btn-primary btn-block font-weight-bold" disabled>
                                                <i class="fas fa-plus-circle mr-1"></i> Buat / Tambah ke Draft
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- KOLOM SEBELAH KANAN: Tabel Draft Sesuai Kolom Input + Form Jenis Penyelesaian, Alasan Retur & Tombol Posting Data -->
                                <div class="col-lg-8">
                                    <div class="card border mb-0">
                                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="text-dark"><i class="fas fa-clipboard-list mr-1 text-primary"></i> Draft Item Retur Pembelian</strong>
                                                <span class="badge badge-primary ml-2" id="badge_draft_count">0 Item</span>
                                            </div>
                                            <div>
                                                <button type="button" id="btn_clear_draft" class="btn btn-xs btn-outline-danger d-none">
                                                    <i class="fas fa-trash-alt mr-1"></i> Kosongkan Draft
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="text-muted small">Supplier Aktif Draft:</span>
                                                    <strong class="text-primary ml-1" id="draft_active_supplier_label">- Belum ada draft -</strong>
                                                </div>
                                            </div>

                                            <!-- Tabel kolom sebelah kanan disesuaikan dengan kolom yang diinput -->
                                            <div class="table-responsive mb-3">
                                                <table id="draft_retur_detail_table" class="table table-bordered table-striped table-sm return-table mb-0">
                                                    <thead class="thead-light text-center">
                                                        <tr>
                                                            <th style="width: 45px;">No</th>
                                                            <th>Kode Barang</th>
                                                            <th>Nama Barang</th>
                                                            <th>No Lot</th>
                                                            <th>Expired Date</th>
                                                            <th>Qty (Satuan Kecil)</th>
                                                            <th style="width: 75px;">#</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted py-3">Belum ada barang di dalam draft. Silakan pilih supplier &amp; tambahkan barang di sebelah kiri.</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Panel Penyelesaian & Alasan Retur (Diinput Setelah Pembuatan Draft) -->
                                            <div id="panel_penyelesaian_draft" class="draft-settlement-box disabled-panel">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="font-weight-bold text-dark mb-0">
                                                        <i class="fas fa-file-signature text-success mr-1"></i> Penyelesaian &amp; Posting Retur Pembelian
                                                    </h6>
                                                    <span class="badge badge-warning" id="badge_status_draft_hint">Buat draft barang terlebih dahulu</span>
                                                </div>
                                                <hr class="mt-1 mb-3">

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Tanggal Retur <span class="text-danger">*</span></label>
                                                            <input type="date" id="tanggal_retur" class="form-control" value="<?= date('Y-m-d') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Jenis Penyelesaian <span class="text-danger">*</span></label>
                                                            <select id="jenis_penyelesaian" class="form-control">
                                                                <option value="POTONG_HUTANG">Potong Hutang Usaha</option>
                                                                <option value="KLAIM_SUPPLIER">Klaim Supplier / Menunggu Refund</option>
                                                                <option value="REFUND_KAS_BANK">Refund Kas / Bank</option>
                                                                <option value="REPLACEMENT">Barang Pengganti (Replacement)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="font-weight-bold">Alasan Retur <span class="text-danger">*</span></label>
                                                    <textarea id="alasan_retur" class="form-control" rows="2" placeholder="Masukkan alasan retur pembelian (contoh: kemasan rusak, barang mendekati expired, dll)..."></textarea>
                                                </div>

                                                <button type="button" id="btn_post_draft_retur" class="btn btn-success btn-block font-weight-bold">
                                                    <i class="fas fa-check-circle mr-1"></i> Posting Data Retur Pembelian
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Riwayat / Daftar Retur Pembelian -->
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong><i class="fas fa-history mr-1"></i> Daftar Retur Pembelian</strong>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="retur_pembelian_table" class="table table-bordered table-striped table-sm">
                                    <thead class="bg-primary text-white text-center">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>No Retur</th>
                                            <th>Supplier</th>
                                            <th>Gudang</th>
                                            <th>Jenis Penyelesaian</th>
                                            <th>Alasan Retur</th>
                                            <th>Item</th>
                                            <th>Total Qty</th>
                                            <th>Status</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (($retur_pembelian_rows ?? []) as $row) : ?>
                                            <tr>
                                                <td class="text-center text-nowrap"><?= html_escape($row['tanggal_retur']) ?></td>
                                                <td class="font-weight-bold text-primary text-nowrap"><?= html_escape($row['no_retur_pembelian']) ?></td>
                                                <td><?= html_escape($row['nama_suplier'] ?: $row['kd_supplier']) ?></td>
                                                <td class="text-center"><?= html_escape($row['nama_gudang'] ?: ('Gudang #' . $row['gudang_id'])) ?></td>
                                                <td class="text-center"><?= html_escape($row['jenis_penyelesaian']) ?></td>
                                                <td><?= html_escape($row['alasan_retur'] ?: '-') ?></td>
                                                <td class="text-center"><?= (int)$row['total_item'] ?></td>
                                                <td class="text-right font-weight-bold"><?= number_format((float)($row['total_qty_retur'] ?? 0), 2, ',', '.') ?></td>
                                                <td class="text-center">
                                                    <?php
                                                    $badge = 'secondary';
                                                    if ($row['status'] === 'POSTED') $badge = 'success';
                                                    if (in_array($row['status'], ['DRAFT', 'SUBMITTED'], true)) $badge = 'warning';
                                                    if (in_array($row['status'], ['PURCHASING_VERIFIED', 'ACCOUNTING_VERIFIED'], true)) $badge = 'info';
                                                    if (in_array($row['status'], ['VOID', 'POSTING_EXCEPTION'], true)) $badge = 'danger';
                                                    ?>
                                                    <span class="badge badge-<?= $badge ?> status-pill"><?= html_escape($row['status']) ?></span>
                                                </td>
                                                <td class="text-nowrap text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-primary js-btn-view-detail"
                                                            data-id="<?= (int)$row['id_retur_pembelian'] ?>"
                                                            data-no="<?= html_escape($row['no_retur_pembelian']) ?>"
                                                            data-supplier="<?= html_escape($row['nama_suplier'] ?: $row['kd_supplier']) ?>">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </button>
                                                    <?php if ($row['status'] === 'DRAFT') : ?>
                                                        <button type="button" class="btn btn-sm btn-primary js-retur-action" data-action="submit" data-id="<?= (int)$row['id_retur_pembelian'] ?>">Submit</button>
                                                    <?php endif; ?>
                                                    <?php if (in_array($row['status'], ['DRAFT', 'SUBMITTED'], true)) : ?>
                                                        <button type="button" class="btn btn-sm btn-info js-retur-action" data-action="verify_purchasing" data-id="<?= (int)$row['id_retur_pembelian'] ?>">Purchasing</button>
                                                    <?php endif; ?>
                                                    <?php if ($row['status'] === 'PURCHASING_VERIFIED') : ?>
                                                        <button type="button" class="btn btn-sm btn-info js-retur-action" data-action="verify_accounting" data-id="<?= (int)$row['id_retur_pembelian'] ?>">Accounting</button>
                                                    <?php endif; ?>
                                                    <?php if ($row['status'] === 'ACCOUNTING_VERIFIED') : ?>
                                                        <button type="button" class="btn btn-sm btn-success js-retur-action" data-action="post" data-id="<?= (int)$row['id_retur_pembelian'] ?>">Post</button>
                                                    <?php endif; ?>
                                                    <?php if ($row['status'] === 'POSTED') : ?>
                                                        <button type="button" class="btn btn-sm btn-danger js-retur-action" data-action="void" data-id="<?= (int)$row['id_retur_pembelian'] ?>">Void</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Modal Detail Item Retur Pembelian -->
        <div class="modal fade" id="modalDetailReturPembelian" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-boxes mr-1"></i> Detail Barang Retur: <span id="modal_detail_no_retur"></span>
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <strong>Supplier:</strong> <span id="modal_detail_supplier"></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm mb-0" id="modal_table_detail_items">
                                <thead class="thead-light text-center">
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>No Lot</th>
                                        <th>Expired Date</th>
                                        <th>Qty (Satuan Kecil)</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>
