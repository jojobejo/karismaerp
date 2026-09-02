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
                                                    if ($stPersiapan === 'SUDAH_DISIAPKAN') {
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
                                                        <td class="text-center font-weight-bold">
                                                            <?= (int)$row['total_item'] ?>
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
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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
                                        <th style="width: 50px;">No</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>No Lot</th>
                                        <th>Expired Date</th>
                                        <th style="width: 100px;">Qty Retur</th>
                                        <th>Alasan Retur Item</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_detail_items">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">Memuat data...</td>
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
            <div class="modal-dialog modal-dialog-centered" role="document">
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
                            
                            <div class="alert alert-secondary py-2 mb-3">
                                <span class="text-muted small d-block">Nomor Retur Pembelian:</span>
                                <strong id="form_noretur_display" class="text-primary font-weight-bold">-</strong>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Status Persiapan Fisik Barang <span class="text-danger">*</span></label>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="radio_belum" name="status_persiapan" value="BELUM_DISIAPKAN" class="custom-control-input">
                                    <label class="custom-control-label font-weight-normal" for="radio_belum">
                                        <span class="badge badge-secondary px-2 py-1"><i class="fas fa-clock mr-1"></i> Belum Disiapkan</span> 
                                        <span class="text-muted small ml-1">&mdash; Barang belum disiapkan atau masih di rak gudang.</span>
                                    </label>
                                </div>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="radio_sedang" name="status_persiapan" value="SEDANG_DISIAPKAN" class="custom-control-input">
                                    <label class="custom-control-label font-weight-normal" for="radio_sedang">
                                        <span class="badge badge-info px-2 py-1"><i class="fas fa-spinner fa-spin mr-1"></i> Sedang Disiapkan</span> 
                                        <span class="text-muted small ml-1">&mdash; Barang sedang dicari / dikumpulkan di area transit retur.</span>
                                    </label>
                                </div>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="radio_sudah" name="status_persiapan" value="SUDAH_DISIAPKAN" class="custom-control-input">
                                    <label class="custom-control-label font-weight-normal" for="radio_sudah">
                                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Sudah Disiapkan</span> 
                                        <span class="text-muted small ml-1">&mdash; Barang lengkap disiapkan dan siap diambil/dikirim ke supplier.</span>
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

            // Modal Detail Barang
            $('.btn-detail-barang').on('click', function () {
                var idRetur = $(this).data('id');
                var noRetur = $(this).data('noretur');
                var supplier = $(this).data('supplier');

                $('#modal_noretur_title').text(noRetur);
                $('#modal_supplier_title').text(supplier);
                $('#tbody_detail_items').html('<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>Memuat daftar barang...</td></tr>');
                $('#modalDetailBarang').modal('show');

                $.ajax({
                    url: "<?= base_url('ics/retur/pembelian/monitoring/detail/') ?>" + idRetur,
                    type: "GET",
                    dataType: "json",
                    success: function (res) {
                        if (res.status && res.items && res.items.length > 0) {
                            var html = '';
                            $.each(res.items, function (index, item) {
                                html += '<tr>';
                                html += '<td class="text-center font-weight-bold">' + (index + 1) + '</td>';
                                html += '<td><code>' + (item.kd_barang || '-') + '</code></td>';
                                html += '<td class="font-weight-bold">' + (item.nama_barang || item.kd_barang) + '</td>';
                                html += '<td class="text-center">' + (item.no_lot || '-') + '</td>';
                                html += '<td class="text-center">' + (item.expired_date ? item.expired_date : '-') + '</td>';
                                html += '<td class="text-right font-weight-bold text-primary">' + parseFloat(item.qty_retur || 0).toLocaleString('id-ID', {minimumFractionDigits: 2}) + ' ' + (item.satuan || 'PCS') + '</td>';
                                html += '<td><small class="text-muted">' + (item.alasan_retur || '-') + '</small></td>';
                                html += '</tr>';
                            });
                            $('#tbody_detail_items').html(html);
                        } else {
                            $('#tbody_detail_items').html('<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada data item barang.</td></tr>');
                        }
                    },
                    error: function () {
                        $('#tbody_detail_items').html('<tr><td colspan="7" class="text-center text-danger py-3">Gagal mengambil detail barang dari server.</td></tr>');
                    }
                });
            });

            <?php if (!empty($can_update_persiapan)) : ?>
            // Modal Update Persiapan (Logistik / Adm LPB)
            $('.btn-update-persiapan').on('click', function () {
                var idRetur = $(this).data('id');
                var noRetur = $(this).data('noretur');
                var currentStatus = $(this).data('status') || 'BELUM_DISIAPKAN';
                var catatan = $(this).data('catatan') || '';

                $('#form_id_retur_pembelian').val(idRetur);
                $('#form_noretur_display').text(noRetur);
                $('#form_catatan_persiapan').val(catatan);

                $('input[name="status_persiapan"][value="' + currentStatus + '"]').prop('checked', true);
                $('#modalUpdatePersiapan').modal('show');
            });

            // Submit Form Update Persiapan via AJAX
            $('#form_update_persiapan').on('submit', function (e) {
                e.preventDefault();
                var $btn = $('#btn_save_persiapan');
                var formData = $(this).serialize();
                var idRetur = $('#form_id_retur_pembelian').val();
                var selectedStatus = $('input[name="status_persiapan"]:checked').val();
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
                            
                            // Update UI baris tabel secara dinamis
                            var $row = $('#row_retur_' + idRetur);
                            var badgeClass = 'status-persiapan-belum';
                            var icon = 'fas fa-clock';
                            var textStatus = 'Belum Disiapkan';

                            if (selectedStatus === 'SUDAH_DISIAPKAN') {
                                badgeClass = 'status-persiapan-sudah';
                                icon = 'fas fa-check-circle';
                                textStatus = 'Sudah Disiapkan';
                            } else if (selectedStatus === 'SEDANG_DISIAPKAN') {
                                badgeClass = 'status-persiapan-sedang';
                                icon = 'fas fa-spinner fa-spin';
                                textStatus = 'Sedang Disiapkan';
                            }

                            $row.find('.cell-status-persiapan').html(
                                '<span class="badge-status-lg ' + badgeClass + '">' +
                                '<i class="' + icon + ' mr-1"></i> ' + textStatus +
                                '</span>'
                            );

                            var disiapkanOleh = res.data && res.data.disiapkan_oleh ? res.data.disiapkan_oleh : 'Anda';
                            var nowStr = 'Baru saja';
                            $row.find('.cell-catatan-persiapan').html(
                                '<div class="text-primary font-weight-bold"><i class="fas fa-user-check mr-1"></i> ' + disiapkanOleh + ' <span class="text-muted font-weight-normal">(' + nowStr + ')</span></div>' +
                                '<div class="text-muted mt-1 text-catatan">' + (catatan ? catatan.replace(/\n/g, '<br>') : '<span class="text-muted italic">Tidak ada catatan</span>') + '</div>'
                            );

                            // Update data attributes tombol update
                            var $btnUpdate = $row.find('.btn-update-persiapan');
                            $btnUpdate.data('status', selectedStatus);
                            $btnUpdate.data('catatan', catatan);

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
