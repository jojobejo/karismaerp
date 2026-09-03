<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
    .monitoring-card {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .badge-status-lg {
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
    }
    .status-persiapan-belum {
        background-color: #6c757d;
        color: #fff;
    }
    .status-persiapan-sedang {
        background-color: #17a2b8;
        color: #fff;
    }
    .status-persiapan-sudah {
        background-color: #28a745;
        color: #fff;
    }
    .status-persiapan-selesai {
        background-color: #007bff;
        color: #fff;
    }
    .table-monitoring th {
        vertical-align: middle !important;
        font-size: 12px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .table-monitoring td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .summary-item-text {
        max-width: 260px;
        white-space: normal;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-size: 12px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">
                    <div class="container-fluid">
                        <!-- Navigation Bar -->
                        <div class="row mb-3">
                            <div class="col-auto">
                                <a class="btn btn-secondary" href="<?= base_url('dashboard') ?>">
                                    <i class="fas fa-home mr-1"></i> Dashboard
                                </a>
                            </div>
                            <?php if (!empty($is_admlpb_user)) : ?>
                                <div class="col-auto">
                                    <a class="btn btn-primary" href="<?= base_url('ics/icspo') ?>">
                                        <i class="fas fa-warehouse mr-1"></i> Data LPB
                                    </a>
                                </div>
                            <?php else : ?>
                                <div class="col-auto">
                                    <a class="btn btn-primary" href="<?= base_url('ics/retur') ?>">
                                        <i class="fas fa-arrow-left mr-1"></i> Dashboard Retur
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <a class="btn btn-success" href="<?= base_url('ics/retur/pembelian') ?>">
                                        <i class="fas fa-plus-circle mr-1"></i> Input Retur
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Main Card -->
                        <div class="card monitoring-card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 font-weight-bold">
                                    <i class="fas fa-tasks mr-2"></i> Monitoring Progres Retur Pembelian
                                </h5>
                                <div class="card-tools">
                                    <span class="badge badge-light px-3 py-2 font-weight-bold">
                                        <?= count($monitoring_rows ?? []) ?> Dokumen Retur
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-light border mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3 text-primary">
                                            <i class="fas fa-info-circle fa-2x"></i>
                                        </div>
                                        <div>
                                            <strong>Alur Monitoring Persiapan Retur:</strong>
                                            <p class="mb-0 text-muted small">
                                                Purchasing membuat dokumen Retur Pembelian &rarr; Bagian Logistik/Adm LPB menyiapkan fisik barang di gudang &rarr; Adm LPB memperbarui status persiapan barang &rarr; Purchasing dapat langsung memonitor apakah barang sudah siap dikembalikan ke supplier.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Filter Section -->
                                <form method="GET" action="<?= base_url('ics/retur/pembelian/monitoring') ?>" class="mb-4">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <label class="small text-muted font-weight-bold">Status Persiapan Barang</label>
                                            <select name="status_persiapan" class="form-control form-control-sm">
                                                <option value="">-- Semua Status Persiapan --</option>
                                                <option value="BELUM_DISIAPKAN" <?= ($filters['status_persiapan'] ?? '') === 'BELUM_DISIAPKAN' ? 'selected' : '' ?>>Belum Disiapkan</option>
                                                <option value="SEDANG_DISIAPKAN" <?= ($filters['status_persiapan'] ?? '') === 'SEDANG_DISIAPKAN' ? 'selected' : '' ?>>Sedang Disiapkan</option>
                                                <option value="SUDAH_DISIAPKAN" <?= ($filters['status_persiapan'] ?? '') === 'SUDAH_DISIAPKAN' ? 'selected' : '' ?>>Sudah Disiapkan</option>
                                                <option value="SELESAI" <?= ($filters['status_persiapan'] ?? '') === 'SELESAI' ? 'selected' : '' ?>>Selesai</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-sm-6 mb-2">
                                            <label class="small text-muted font-weight-bold">Status Dokumen</label>
                                            <select name="status_dokumen" class="form-control form-control-sm">
                                                <option value="">-- Semua Status --</option>
                                                <option value="DRAFT" <?= ($filters['status_dokumen'] ?? '') === 'DRAFT' ? 'selected' : '' ?>>DRAFT</option>
                                                <option value="SUBMITTED" <?= ($filters['status_dokumen'] ?? '') === 'SUBMITTED' ? 'selected' : '' ?>>SUBMITTED</option>
                                                <option value="PURCHASING_VERIFIED" <?= ($filters['status_dokumen'] ?? '') === 'PURCHASING_VERIFIED' ? 'selected' : '' ?>>PURCHASING VERIFIED</option>
                                                <option value="ACCOUNTING_VERIFIED" <?= ($filters['status_dokumen'] ?? '') === 'ACCOUNTING_VERIFIED' ? 'selected' : '' ?>>ACCOUNTING VERIFIED</option>
                                                <option value="POSTED" <?= ($filters['status_dokumen'] ?? '') === 'POSTED' ? 'selected' : '' ?>>POSTED</option>
                                                <option value="VOID" <?= ($filters['status_dokumen'] ?? '') === 'VOID' ? 'selected' : '' ?>>VOID</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-sm-6 mb-2">
                                            <label class="small text-muted font-weight-bold">Dari Tanggal</label>
                                            <input type="date" name="start_date" class="form-control form-control-sm" value="<?= html_escape($filters['start_date'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-2 col-sm-6 mb-2">
                                            <label class="small text-muted font-weight-bold">Sampai Tanggal</label>
                                            <input type="date" name="end_date" class="form-control form-control-sm" value="<?= html_escape($filters['end_date'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <label class="small text-muted font-weight-bold">Cari Dokumen / Supplier</label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="search" class="form-control" placeholder="No Retur / LPB / Supplier..." value="<?= html_escape($filters['search'] ?? '') ?>">
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                                    <a href="<?= base_url('ics/retur/pembelian/monitoring') ?>" class="btn btn-secondary" title="Reset Filter"><i class="fas fa-undo"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <!-- Table Section -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped table-monitoring" id="table_monitoring_retur">
                                        <thead class="bg-primary text-white text-center">
                                            <tr>
                                                <th style="width: 90px;">Tanggal</th>
                                                <th>No Retur</th>
                                                <th>No LPB / PO</th>
                                                <th>Supplier</th>
                                                <th>Barang yang Diretur</th>
                                                <th style="width: 70px;">Item</th>
                                                <th style="width: 80px;">Qty Total</th>
                                                <th style="width: 100px;">Status Dokumen</th>
                                                <th style="width: 150px;">Status Persiapan Barang</th>
                                                <th>Keterangan Persiapan</th>
                                                <th style="width: 120px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($monitoring_rows)) : ?>
                                                <?php foreach ($monitoring_rows as $row) : ?>
                                                    <?php
                                                    $stPersiapan = $row['status_persiapan'] ?? 'BELUM_DISIAPKAN';
                                                    if ($stPersiapan === 'SELESAI') {
                                                        $badgePersiapan = 'status-persiapan-selesai';
                                                        $iconPersiapan = 'fas fa-check-double';
                                                        $textPersiapan = 'Selesai';
                                                    } elseif ($stPersiapan === 'SUDAH_DISIAPKAN') {
                                                        $badgePersiapan = 'status-persiapan-sudah';
                                                        $iconPersiapan = 'fas fa-check-circle';
                                                        $textPersiapan = 'Sudah Disiapkan';
                                                    } elseif ($stPersiapan === 'SEDANG_DISIAPKAN') {
                                                        $badgePersiapan = 'status-persiapan-sedang';
                                                        $iconPersiapan = 'fas fa-spinner fa-spin';
                                                        $textPersiapan = 'Sedang Disiapkan';
                                                    } else {
                                                        $badgePersiapan = 'status-persiapan-belum';
                                                        $iconPersiapan = 'fas fa-clock';
                                                        $textPersiapan = 'Belum Disiapkan';
                                                    }

                                                    $badgeDoc = 'secondary';
                                                    if ($row['status'] === 'POSTED') $badgeDoc = 'success';
                                                    if (in_array($row['status'], ['DRAFT', 'SUBMITTED'], true)) $badgeDoc = 'warning';
                                                    if (in_array($row['status'], ['PURCHASING_VERIFIED', 'ACCOUNTING_VERIFIED'], true)) $badgeDoc = 'info';
                                                    if (in_array($row['status'], ['VOID', 'POSTING_EXCEPTION'], true)) $badgeDoc = 'danger';
                                                    ?>
                                                    <tr id="row_retur_<?= (int)$row['id_retur_pembelian'] ?>">
                                                        <td class="text-center font-weight-bold text-nowrap">
                                                            <?= date('d/m/Y', strtotime($row['tanggal_retur'])) ?>
                                                        </td>
                                                        <td class="font-weight-bold text-primary text-nowrap">
                                                            <?= html_escape($row['no_retur_pembelian']) ?>
                                                        </td>
                                                        <td class="small">
                                                            <div><strong>LPB:</strong> <?= html_escape($row['nomor_lpb'] ?: '-') ?></div>
                                                            <div class="text-muted"><strong>PO:</strong> <?= html_escape($row['no_po'] ?: ($row['kd_po'] ?: '-')) ?></div>
                                                        </td>
                                                        <td class="font-weight-bold">
                                                            <?= html_escape($row['nama_suplier'] ?: ($row['kd_supplier'] ?: '-')) ?>
                                                        </td>
                                                        <td>
                                                            <div class="summary-item-text text-muted" title="<?= html_escape($row['ringkasan_barang'] ?: '-') ?>">
                                                                <?= html_escape($row['ringkasan_barang'] ?: '-') ?>
                                                            </div>
                                                        </td>
                                                        <td class="text-center font-weight-bold cell-item-col" id="cell_item_<?= (int)$row['id_retur_pembelian'] ?>">
                                                            <div class="text-item-count"><?= (int)$row['total_item'] ?> Item</div>
                                                            <?php 
                                                                $totSiap = (int)($row['total_item_disiapkan'] ?? 0);
                                                                $totItem = (int)$row['total_item'];
                                                                $badgeSiap = 'secondary';
                                                                if ($totSiap === $totItem && $totItem > 0) {
                                                                    $badgeSiap = 'success';
                                                                } elseif ($totSiap > 0) {
                                                                    $badgeSiap = 'info';
                                                                }
                                                            ?>
                                                            <span class="badge badge-<?= $badgeSiap ?> px-1 py-0 cell-item-siap-badge <?= ($totItem <= 1 && $totSiap === 0) ? 'd-none' : '' ?>" style="font-size: 11px;" title="<?= $totSiap ?> dari <?= $totItem ?> item telah disiapkan">
                                                                <i class="fas fa-check-square mr-1"></i><span class="siap-count"><?= $totSiap ?></span>/<?= $totItem ?> Siap
                                                            </span>
                                                        </td>
                                                        <td class="text-right font-weight-bold text-primary">
                                                            <?= number_format((float)$row['total_qty_retur'], 2, ',', '.') ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-<?= $badgeDoc ?> px-2 py-1"><?= html_escape($row['status']) ?></span>
                                                        </td>
                                                        <td class="text-center cell-status-persiapan">
                                                            <span class="badge-status-lg <?= $badgePersiapan ?>">
                                                                <i class="<?= $iconPersiapan ?> mr-1"></i> <?= $textPersiapan ?>
                                                            </span>
                                                        </td>
                                                        <td class="small cell-catatan-persiapan">
                                                            <?php if (!empty($row['disiapkan_oleh'])) : ?>
                                                                <div class="text-primary font-weight-bold">
                                                                    <i class="fas fa-user-check mr-1"></i> <?= html_escape($row['disiapkan_oleh']) ?>
                                                                    <?php if (!empty($row['disiapkan_at'])) : ?>
                                                                        <span class="text-muted font-weight-normal">(<?= date('d/m/y H:i', strtotime($row['disiapkan_at'])) ?>)</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div class="text-muted mt-1 text-catatan">
                                                                <?= !empty($row['catatan_persiapan']) ? nl2br(html_escape($row['catatan_persiapan'])) : '<span class="text-muted italic">Tidak ada catatan</span>' ?>
                                                            </div>
                                                        </td>
                                                        <td class="text-center text-nowrap">
                                                            <button type="button" class="btn btn-sm btn-outline-primary btn-detail-barang mb-1" 
                                                                    data-id="<?= (int)$row['id_retur_pembelian'] ?>"
                                                                    data-noretur="<?= html_escape($row['no_retur_pembelian']) ?>"
                                                                    data-supplier="<?= html_escape($row['nama_suplier'] ?: $row['kd_supplier']) ?>">
                                                                <i class="fas fa-eye mr-1"></i> Detail
                                                            </button>
                                                            <?php if (!empty($can_update_persiapan)) : ?>
                                                                <button type="button" class="btn btn-sm btn-success btn-update-persiapan mb-1"
                                                                        data-id="<?= (int)$row['id_retur_pembelian'] ?>"
                                                                        data-noretur="<?= html_escape($row['no_retur_pembelian']) ?>"
                                                                        data-status="<?= html_escape($stPersiapan) ?>"
                                                                        data-catatan="<?= html_escape($row['catatan_persiapan'] ?? '') ?>">
                                                                    <i class="fas fa-edit mr-1"></i> Update
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <tr>
                                                    <td colspan="11" class="text-center text-muted py-4">
                                                        <i class="fas fa-inbox fa-3x mb-2 d-block text-gray"></i>
                                                        Tidak ada data retur pembelian yang sesuai filter.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Modal Detail Barang -->
        <div class="modal fade" id="modalDetailBarang" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-boxes mr-2"></i> Detail Barang yang Diretur: <span id="modal_noretur_title"></span>
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-3">
                        <div class="mb-3 p-2 bg-light border rounded">
                            <span class="text-muted">Supplier:</span> <strong id="modal_supplier_title" class="text-primary">-</strong>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" id="table_modal_items">
                                <thead class="thead-light text-center">
                                    <tr>
                                        <th style="width: 40px;">No</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>No Lot</th>
                                        <th>Expired Date</th>
                                        <th style="width: 110px;">Qty Retur</th>
                                        <th style="width: 140px;">Kesiapan Fisik</th>
                                        <th>Alasan Retur Item</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_detail_items">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">Memuat data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Update Status Persiapan (Khusus Adm LPB / Logistik) -->
        <?php if (!empty($can_update_persiapan)) : ?>
        <div class="modal fade" id="modalUpdatePersiapan" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-clipboard-check mr-2"></i> Update Persiapan Barang Retur
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="form_update_persiapan">
                        <div class="modal-body">
                            <input type="hidden" id="form_id_retur_pembelian" name="id_retur_pembelian">
                            <input type="hidden" name="has_items_submitted" value="1">
                            
                            <div class="d-flex justify-content-between align-items-center alert alert-secondary py-2 mb-3">
                                <div>
                                    <span class="text-muted small d-block">Nomor Retur Pembelian:</span>
                                    <strong id="form_noretur_display" class="text-primary font-weight-bold">-</strong>
                                </div>
                                <div>
                                    <span class="badge badge-info px-2 py-1" id="badge_persiapan_count">Memuat item...</span>
                                </div>
                            </div>

                            <!-- Section Checklist Item -->
                            <div class="form-group mb-3" id="section_items_persiapan">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="font-weight-bold text-dark mb-0">
                                        <i class="fas fa-boxes text-success mr-1"></i> Kesiapan Fisik Per Item Barang:
                                    </label>
                                    <div id="wrapper_item_bulk_buttons" style="display: none;">
                                        <button type="button" class="btn btn-xs btn-outline-success mr-1" id="btn_check_all_items">
                                            <i class="fas fa-check-double mr-1"></i> Ceklis Semua
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="btn_uncheck_all_items">
                                            <i class="fas fa-times mr-1"></i> Batal Semua
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive border rounded" style="max-height: 220px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0" id="table_items_persiapan">
                                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                                            <tr>
                                                <th style="width: 70px;" class="text-center">Ceklis</th>
                                                <th>Nama & Kode Barang</th>
                                                <th>No Lot / Exp</th>
                                                <th class="text-right" style="width: 110px;">Qty Retur</th>
                                                <th class="text-center" style="width: 130px;">Kesiapan Fisik</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_items_persiapan">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-3">
                                                    <i class="fas fa-spinner fa-spin mr-1"></i> Memuat item barang retur...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle text-info mr-1"></i> Klik centang/ceklis pada masing-masing barang yang telah disiapkan di area gudang.
                                </small>
                            </div>

                            <input type="hidden" id="form_status_persiapan" name="status_persiapan" value="BELUM_DISIAPKAN">

                            <div class="form-group mb-3">
                                <label class="font-weight-bold d-block">Status Persiapan Dokumen Retur</label>
                                
                                <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-2">
                                    <span class="small text-muted"><i class="fas fa-magic text-primary mr-1"></i> Status Kesiapan Fisik (Otomatis):</span>
                                    <span id="label_status_otomatis_badge" class="badge badge-secondary px-2 py-1 font-weight-bold">
                                        <i class="fas fa-clock mr-1"></i> Belum Disiapkan
                                    </span>
                                </div>

                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" id="check_selesai" class="custom-control-input">
                                    <label class="custom-control-label font-weight-normal" for="check_selesai">
                                        <span class="badge badge-primary px-2 py-1"><i class="fas fa-check-double mr-1"></i> Selesai</span> 
                                        <span class="text-muted small ml-1">&mdash; Persiapan dan serah terima fisik barang retur telah selesai.</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold" for="form_catatan_persiapan">Catatan / Lokasi Persiapan Barang</label>
                                <textarea id="form_catatan_persiapan" name="catatan_persiapan" class="form-control" rows="3" placeholder="Contoh: Barang sudah dikumpulkan di Palet Retur A-02, siap diambil oleh ekspedisi/supplier."></textarea>
                                <small class="text-muted">Catatan ini dapat langsung dibaca oleh bagian Purchasing.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success" id="btn_save_persiapan">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <footer class="main-footer">
            <strong>Copyright &copy; 2026 <a href="https://kiu.co.id">PT. KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
        </footer>
    </div>

    <!-- Scripts -->
    <script>
        $(document).ready(function () {
            // Inisialisasi DataTable jika tersedia
            if ($.fn.DataTable && $('#table_monitoring_retur tbody tr').length > 1) {
                $('#table_monitoring_retur').DataTable({
                    "paging": true,
                    "ordering": true,
                    "info": true,
                    "searching": false,
                    "pageLength": 25,
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                    }
                });
            }

            var canUpdatePersiapan = <?= !empty($can_update_persiapan) ? 'true' : 'false' ?>;

            function updateTableRowPersiapan(idRetur, status, totalItem, totalSiap, disiapkanOleh, catatan) {
                var $row = $('#row_retur_' + idRetur);
                if ($row.length === 0) return;

                var badgeClass = 'status-persiapan-belum';
                var icon = 'fas fa-clock';
                var textStatus = 'Belum Disiapkan';

                if (status === 'SELESAI') {
                    badgeClass = 'status-persiapan-selesai';
                    icon = 'fas fa-check-double';
                    textStatus = 'Selesai';
                } else if (status === 'SUDAH_DISIAPKAN') {
                    badgeClass = 'status-persiapan-sudah';
                    icon = 'fas fa-check-circle';
                    textStatus = 'Sudah Disiapkan';
                } else if (status === 'SEDANG_DISIAPKAN') {
                    badgeClass = 'status-persiapan-sedang';
                    icon = 'fas fa-spinner fa-spin';
                    textStatus = 'Sedang Disiapkan';
                }

                $row.find('.cell-status-persiapan').html(
                    '<span class="badge-status-lg ' + badgeClass + '">' +
                    '<i class="' + icon + ' mr-1"></i> ' + textStatus +
                    '</span>'
                );

                if (typeof totalItem !== 'undefined' && typeof totalSiap !== 'undefined') {
                    var badgeItemClass = totalSiap === totalItem && totalItem > 0 ? 'badge-success' : (totalSiap > 0 ? 'badge-info' : 'badge-secondary');
                    var $itemBadge = $row.find('.cell-item-siap-badge');
                    if (totalItem > 1 || totalSiap > 0) {
                        $itemBadge.removeClass('badge-success badge-info badge-secondary d-none')
                                  .addClass('badge-' + badgeItemClass)
                                  .html('<i class="fas fa-check-square mr-1"></i>' + totalSiap + '/' + totalItem + ' Siap')
                                  .attr('title', totalSiap + ' dari ' + totalItem + ' item telah disiapkan');
                    }
                }

                if (typeof disiapkanOleh !== 'undefined') {
                    var disiapkanText = disiapkanOleh || 'Anda';
                    var nowStr = 'Baru saja';
                    $row.find('.cell-catatan-persiapan').html(
                        '<div class="text-primary font-weight-bold"><i class="fas fa-user-check mr-1"></i> ' + disiapkanText + ' <span class="text-muted font-weight-normal">(' + nowStr + ')</span></div>' +
                        '<div class="text-muted mt-1 text-catatan">' + (catatan ? catatan.replace(/\n/g, '<br>') : '<span class="text-muted italic">Tidak ada catatan</span>') + '</div>'
                    );
                }

                var $btnUpdate = $row.find('.btn-update-persiapan');
                $btnUpdate.data('status', status);
                if (typeof catatan !== 'undefined') {
                    $btnUpdate.data('catatan', catatan);
                }
            }

            // Modal Detail Barang
            $('.btn-detail-barang').on('click', function () {
                var idRetur = $(this).data('id');
                var noRetur = $(this).data('noretur');
                var supplier = $(this).data('supplier');

                $('#modal_noretur_title').text(noRetur);
                $('#modal_supplier_title').text(supplier);
                $('#tbody_detail_items').html('<tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>Memuat daftar barang...</td></tr>');
                $('#modalDetailBarang').modal('show');

                $.ajax({
                    url: "<?= base_url('ics/retur/pembelian/monitoring/detail/') ?>" + idRetur,
                    type: "GET",
                    dataType: "json",
                    success: function (res) {
                        if (res.status && res.items && res.items.length > 0) {
                            var html = '';
                            $.each(res.items, function (index, item) {
                                var isSiap = parseInt(item.is_disiapkan) === 1;
                                var statusFisikHtml = '';
                                if (canUpdatePersiapan) {
                                    if (isSiap) {
                                        statusFisikHtml = '<button type="button" class="btn btn-xs btn-success btn-toggle-item-inline" data-id="' + item.id_detail_retur_pembelian + '" data-retur="' + idRetur + '" data-state="0" title="Klik untuk membatalkan status siap"><i class="fas fa-check-circle mr-1"></i> Disiapkan</button>';
                                    } else {
                                        statusFisikHtml = '<button type="button" class="btn btn-xs btn-outline-secondary btn-toggle-item-inline" data-id="' + item.id_detail_retur_pembelian + '" data-retur="' + idRetur + '" data-state="1" title="Klik untuk menandai sudah disiapkan"><i class="fas fa-clock mr-1"></i> Belum Siap</button>';
                                    }
                                } else {
                                    if (isSiap) {
                                        statusFisikHtml = '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Disiapkan</span>';
                                    } else {
                                        statusFisikHtml = '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-clock mr-1"></i> Belum Siap</span>';
                                    }
                                }

                                html += '<tr id="detail_item_row_' + item.id_detail_retur_pembelian + '" class="' + (isSiap ? 'table-success' : '') + '">';
                                html += '<td class="text-center font-weight-bold">' + (index + 1) + '</td>';
                                html += '<td><code>' + (item.kd_barang || '-') + '</code></td>';
                                html += '<td class="font-weight-bold">' + (item.nama_barang || item.kd_barang) + '</td>';
                                html += '<td class="text-center">' + (item.no_lot || '-') + '</td>';
                                html += '<td class="text-center">' + (item.expired_date ? item.expired_date : '-') + '</td>';
                                html += '<td class="text-right font-weight-bold text-primary">' + parseFloat(item.qty_retur || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + ' ' + (item.satuan || 'PCS') + '</td>';
                                html += '<td class="text-center cell-item-toggle">' + statusFisikHtml + '</td>';
                                html += '<td><small class="text-muted">' + (item.alasan_retur || '-') + '</small></td>';
                                html += '</tr>';
                            });
                            $('#tbody_detail_items').html(html);
                        } else {
                            $('#tbody_detail_items').html('<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data item barang.</td></tr>');
                        }
                    },
                    error: function () {
                        $('#tbody_detail_items').html('<tr><td colspan="8" class="text-center text-danger py-3">Gagal mengambil detail barang dari server.</td></tr>');
                    }
                });
            });

            // Toggle item persiapan langsung dari modal detail barang
            $(document).on('click', '.btn-toggle-item-inline', function () {
                var $btn = $(this);
                var idDetail = $btn.data('id');
                var idRetur = $btn.data('retur');
                var nextState = $btn.data('state');

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "<?= base_url('ics/retur/pembelian/monitoring/toggle_item') ?>",
                    type: "POST",
                    data: {
                        id_detail_retur_pembelian: idDetail,
                        is_disiapkan: nextState
                    },
                    dataType: "json",
                    success: function (res) {
                        $btn.prop('disabled', false);
                        if (res && res.status) {
                            var isNowSiap = parseInt(res.data.is_disiapkan) === 1;
                            var $row = $('#detail_item_row_' + idDetail);
                            if (isNowSiap) {
                                $row.addClass('table-success');
                                $btn.removeClass('btn-outline-secondary').addClass('btn-success').data('state', 0).html('<i class="fas fa-check-circle mr-1"></i> Disiapkan');
                            } else {
                                $row.removeClass('table-success');
                                $btn.removeClass('btn-success').addClass('btn-outline-secondary').data('state', 1).html('<i class="fas fa-clock mr-1"></i> Belum Siap');
                            }

                            // Update baris tabel monitoring
                            updateTableRowPersiapan(idRetur, res.data.status_persiapan, res.data.total_item, res.data.total_item_disiapkan);
                        } else {
                            alert(res.message || 'Gagal mengubah status item.');
                        }
                    },
                    error: function () {
                        $btn.prop('disabled', false);
                        alert('Gagal menghubungi server.');
                    }
                });
            });

            <?php if (!empty($can_update_persiapan)) : ?>
            function renderModalUpdateItems(items) {
                var html = '';
                var total = items.length;
                var siapCount = 0;

                if (total === 0) {
                    html = '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada detail item barang retur.</td></tr>';
                    $('#tbody_items_persiapan').html(html);
                    $('#wrapper_item_bulk_buttons').hide();
                    $('#badge_persiapan_count').text('0 Item');
                    return;
                }

                $.each(items, function (idx, item) {
                    var isSiap = parseInt(item.is_disiapkan) === 1;
                    if (isSiap) siapCount++;

                    html += '<tr class="item-prep-row ' + (isSiap ? 'table-success' : '') + '" data-id="' + item.id_detail_retur_pembelian + '">';
                    html += '<td class="text-center align-middle">';
                    html += '<div class="custom-control custom-checkbox">';
                    html += '<input type="checkbox" class="custom-control-input chk-item-persiapan" id="chk_prep_' + item.id_detail_retur_pembelian + '" name="items_disiapkan[]" value="' + item.id_detail_retur_pembelian + '" ' + (isSiap ? 'checked' : '') + '>';
                    html += '<label class="custom-control-label font-weight-bold" for="chk_prep_' + item.id_detail_retur_pembelian + '"></label>';
                    html += '</div>';
                    html += '</td>';
                    html += '<td class="align-middle">';
                    html += '<span class="font-weight-bold d-block">' + (item.nama_barang || item.kd_barang) + '</span>';
                    html += '<small class="text-muted"><code>' + (item.kd_barang || '-') + '</code></small>';
                    html += '</td>';
                    html += '<td class="align-middle small">';
                    html += '<div><strong>Lot:</strong> ' + (item.no_lot || '-') + '</div>';
                    html += '<div class="text-muted"><strong>Exp:</strong> ' + (item.expired_date || '-') + '</div>';
                    html += '</td>';
                    html += '<td class="text-right align-middle font-weight-bold text-primary">';
                    html += parseFloat(item.qty_retur || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + ' ' + (item.satuan || 'PCS');
                    html += '</td>';
                    html += '<td class="text-center align-middle col-item-status">';
                    if (isSiap) {
                        html += '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Disiapkan</span>';
                    } else {
                        html += '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-clock mr-1"></i> Belum Siap</span>';
                    }
                    html += '</td>';
                    html += '</tr>';
                });

                $('#tbody_items_persiapan').html(html);
                $('#wrapper_item_bulk_buttons').show();
                updateCounterSummary(siapCount, total);
                updateStatusPersiapanUI();
            }

            function updateCounterSummary(siapCount, total) {
                var badgeClass = siapCount === total && total > 0 ? 'badge-success' : (siapCount > 0 ? 'badge-info' : 'badge-secondary');
                $('#badge_persiapan_count').attr('class', 'badge px-2 py-1 ' + badgeClass)
                    .html('<i class="fas fa-check-square mr-1"></i> ' + siapCount + ' dari ' + total + ' item disiapkan');
            }

            function updateStatusPersiapanUI() {
                var isSelesai = $('#check_selesai').is(':checked');
                var total = $('.chk-item-persiapan').length;
                var checkedCount = $('.chk-item-persiapan:checked').length;
                var autoStatus = 'BELUM_DISIAPKAN';
                var autoText = 'Belum Disiapkan';
                var autoClass = 'badge-secondary';
                var autoIcon = 'fas fa-clock';

                if (checkedCount === 0) {
                    autoStatus = 'BELUM_DISIAPKAN';
                    autoText = 'Belum Disiapkan';
                    autoClass = 'badge-secondary';
                    autoIcon = 'fas fa-clock';
                } else if (checkedCount === total && total > 0) {
                    autoStatus = 'SUDAH_DISIAPKAN';
                    autoText = 'Sudah Disiapkan';
                    autoClass = 'badge-success';
                    autoIcon = 'fas fa-check-circle';
                } else {
                    autoStatus = 'SEDANG_DISIAPKAN';
                    autoText = 'Sedang Disiapkan';
                    autoClass = 'badge-info';
                    autoIcon = 'fas fa-spinner fa-spin';
                }

                if (isSelesai) {
                    $('#form_status_persiapan').val('SELESAI');
                    $('#label_status_otomatis_badge').attr('class', 'badge badge-primary px-2 py-1 font-weight-bold')
                        .html('<i class="fas fa-check-double mr-1"></i> Selesai');
                } else {
                    $('#form_status_persiapan').val(autoStatus);
                    $('#label_status_otomatis_badge').attr('class', 'badge ' + autoClass + ' px-2 py-1 font-weight-bold')
                        .html('<i class="' + autoIcon + ' mr-1"></i> ' + autoText);
                }
            }

            // Handler checkbox Selesai
            $(document).on('change', '#check_selesai', function () {
                updateStatusPersiapanUI();
            });

            // Checkbox change handler inside modal
            $(document).on('change', '.chk-item-persiapan', function () {
                var $chk = $(this);
                var $tr = $chk.closest('tr');
                var isChecked = $chk.is(':checked');

                if (isChecked) {
                    $tr.addClass('table-success');
                    $tr.find('.col-item-status').html('<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Disiapkan</span>');
                } else {
                    $tr.removeClass('table-success');
                    $tr.find('.col-item-status').html('<span class="badge badge-secondary px-2 py-1"><i class="fas fa-clock mr-1"></i> Belum Siap</span>');
                }

                var total = $('.chk-item-persiapan').length;
                var checkedCount = $('.chk-item-persiapan:checked').length;
                updateCounterSummary(checkedCount, total);
                updateStatusPersiapanUI();
            });

            // Bulk buttons
            $('#btn_check_all_items').on('click', function () {
                $('.chk-item-persiapan').prop('checked', true).trigger('change');
            });
            $('#btn_uncheck_all_items').on('click', function () {
                $('.chk-item-persiapan').prop('checked', false).trigger('change');
            });

            // Modal Update Persiapan (Logistik / Adm LPB)
            $('.btn-update-persiapan').on('click', function () {
                var idRetur = $(this).data('id');
                var noRetur = $(this).data('noretur');
                var currentStatus = $(this).data('status') || 'BELUM_DISIAPKAN';
                var catatan = $(this).data('catatan') || '';

                $('#form_id_retur_pembelian').val(idRetur);
                $('#form_noretur_display').text(noRetur);
                $('#form_catatan_persiapan').val(catatan);

                if (currentStatus === 'SELESAI') {
                    $('#check_selesai').prop('checked', true);
                    $('#form_status_persiapan').val('SELESAI');
                    $('#label_status_otomatis_badge').attr('class', 'badge badge-primary px-2 py-1 font-weight-bold')
                        .html('<i class="fas fa-check-double mr-1"></i> Selesai');
                } else {
                    $('#check_selesai').prop('checked', false);
                    $('#form_status_persiapan').val(currentStatus);
                }

                $('#tbody_items_persiapan').html('<tr><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat item barang retur...</td></tr>');
                $('#wrapper_item_bulk_buttons').hide();
                $('#badge_persiapan_count').attr('class', 'badge badge-info px-2 py-1').text('Memuat item...');
                $('#modalUpdatePersiapan').modal('show');

                // Muat daftar detail item untuk checklist
                $.ajax({
                    url: "<?= base_url('ics/retur/pembelian/monitoring/detail/') ?>" + idRetur,
                    type: "GET",
                    dataType: "json",
                    success: function (res) {
                        if (res && res.status && res.items) {
                            renderModalUpdateItems(res.items);
                        } else {
                            $('#tbody_items_persiapan').html('<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data item.</td></tr>');
                        }
                    },
                    error: function () {
                        $('#tbody_items_persiapan').html('<tr><td colspan="5" class="text-center text-danger py-3">Gagal memuat item dari server.</td></tr>');
                    }
                });
            });

            // Submit Form Update Persiapan via AJAX
            $('#form_update_persiapan').on('submit', function (e) {
                e.preventDefault();
                var $btn = $('#btn_save_persiapan');
                var formData = $(this).serialize();
                var idRetur = $('#form_id_retur_pembelian').val();
                var selectedStatus = $('#form_status_persiapan').val();
                var catatan = $('#form_catatan_persiapan').val();

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: "<?= base_url('ics/retur/pembelian/monitoring/update_status') ?>",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function (res) {
                        $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
                        if (res && (res.status === true || res.success === true)) {
                            $('#modalUpdatePersiapan').modal('hide');
                            
                            var totalItem = res.data && typeof res.data.total_item !== 'undefined' ? res.data.total_item : undefined;
                            var totalSiap = res.data && typeof res.data.total_item_disiapkan !== 'undefined' ? res.data.total_item_disiapkan : undefined;
                            var disiapkanOleh = res.data && res.data.disiapkan_oleh ? res.data.disiapkan_oleh : 'Anda';

                            updateTableRowPersiapan(idRetur, selectedStatus, totalItem, totalSiap, disiapkanOleh, catatan);

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message || 'Status persiapan barang berhasil diperbarui!',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            } else {
                                alert(res.message || 'Status persiapan barang berhasil diperbarui!');
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message || 'Terjadi kesalahan saat memperbarui status.'
                                });
                            } else {
                                alert(res.message || 'Terjadi kesalahan.');
                            }
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');
                        var msg = 'Gagal menghubungi server.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Kesalahan Server',
                                text: msg
                            });
                        } else {
                            alert(msg);
                        }
                    }
                });
            });
            <?php endif; ?>
        });
    </script>
</body>
