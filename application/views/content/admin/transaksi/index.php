<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <!-- Navbar -->
    <?php $this->load->view('partial/main/navbar') ?>

    <!-- Main Sidebar Container -->
    <?php $this->load->view('partial/main/sidebar') ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper bg-light">
        <!-- Content Header -->
        <div class="content-header pt-3 pb-2 border-bottom bg-white shadow-sm mb-3">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-7">
                        <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.65rem;">
                            <i class="fas fa-exchange-alt text-teal mr-2"></i>Semua Transaksi & Sinkronisasi Jurnal
                        </h1>
                        <p class="text-muted small mb-0 mt-1">
                            Pusat audit, manipulasi, repost, edit, dan sinkronisasi otomatis seluruh transaksi ERP dengan buku besar akuntansi.
                        </p>
                    </div>
                    <div class="col-sm-5 text-right">
                        <span class="badge badge-primary px-3 py-2 mr-2" style="font-size: 0.85rem;">
                            <i class="fas fa-user-shield mr-1"></i> Admin Access Only
                        </span>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">

                <!-- STATISTIC SUMMARY CARDS -->
                <div class="row mb-3">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-info shadow-sm mb-2">
                            <div class="inner">
                                <h3 id="stat-total-count">0</h3>
                                <p class="mb-0">Total Transaksi Terfilter</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-success shadow-sm mb-2">
                            <div class="inner">
                                <h3 id="stat-total-nominal">Rp 0</h3>
                                <p class="mb-0">Total Nominal Transaksi</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-teal shadow-sm mb-2">
                            <div class="inner">
                                <h3 id="stat-total-posted">0</h3>
                                <p class="mb-0">Transaksi Terposting Jurnal</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-warning shadow-sm mb-2">
                            <div class="inner">
                                <h3 id="stat-total-unposted">0</h3>
                                <p class="mb-0">Belum Ada Jurnal / Unposted</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MAIN CARD -->
                <div class="card card-outline card-teal shadow-sm">
                    <!-- CATEGORY TABS & ACTION BUTTONS -->
                    <div class="card-header p-2 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap">
                        <ul class="nav nav-pills custom-trans-tabs" id="trans-category-tabs">
                            <?php if (empty($is_admpnj_only)): ?>
                            <li class="nav-item">
                                <a class="nav-link active font-weight-bold" href="javascript:void(0)" data-category="all">
                                    <i class="fas fa-th-list mr-1"></i> Semua Transaksi
                                </a>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link <?= !empty($is_admpnj_only) ? 'active' : '' ?> font-weight-bold" href="javascript:void(0)" data-category="penjualan">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> Penjualan (Faktur)
                                </a>
                            </li>
                            <?php if (empty($is_admpnj_only)): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0)" data-category="pembelian">
                                    <i class="fas fa-shopping-cart mr-1"></i> Pembelian (LPB)
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0)" data-category="pembayaran_customer">
                                    <i class="fas fa-cash-register mr-1"></i> Pembayaran Customer
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0)" data-category="pembayaran_supplier">
                                    <i class="fas fa-money-check-alt mr-1"></i> Pembayaran Supplier
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0)" data-category="retur_penjualan">
                                    <i class="fas fa-undo-alt mr-1"></i> Retur Penjualan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="javascript:void(0)" data-category="retur_pembelian">
                                    <i class="fas fa-reply-all mr-1"></i> Retur Pembelian
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <div class="ml-auto my-1">
                            <button type="button" class="btn btn-outline-teal btn-sm font-weight-bold shadow-sm" id="btn-open-activity-log">
                                <i class="fas fa-history mr-1"></i> Activity Log
                            </button>
                        </div>
                    </div>

                    <!-- FILTER BAR -->
                    <div class="card-body bg-light border-bottom py-3">
                        <form id="form-filter-transaksi" class="row align-items-end">
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Tanggal Mulai:</label>
                                <input type="date" class="form-control form-control-sm" id="filter-date-from" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Tanggal Sampai:</label>
                                <input type="date" class="form-control form-control-sm" id="filter-date-to" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Status Transaksi/Jurnal:</label>
                                <select class="form-control form-control-sm" id="filter-status">
                                    <option value="all">Semua Status</option>
                                    <option value="POSTED">Sudah Posting Jurnal</option>
                                    <option value="UNPOSTED">Belum / Butuh Repost</option>
                                    <option value="CANCELLED">Batal / Ditolak / Void</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Pencarian Cepat:</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="filter-search" placeholder="No Dok, Ref, Customer, Jurnal...">
                                    <div class="input-group-append">
                                        <button class="btn btn-teal text-white" type="submit" title="Cari">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-12 mb-2">
                                <div class="d-flex" style="gap: 6px;">
                                    <button type="submit" class="btn btn-teal btn-sm text-white flex-fill font-weight-bold" id="btn-apply-filter" title="Terapkan Filter">
                                        <i class="fas fa-filter mr-1"></i> Terapkan Filter
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-bold" id="btn-reset-filter" title="Reset Filter">
                                        <i class="fas fa-undo mr-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- TABLE CONTENT -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered mb-0" id="table-semua-transaksi">
                                <thead class="bg-dark text-white text-center">
                                    <tr>
                                        <th style="width: 40px;">No</th>
                                        <th style="width: 100px;">Tanggal</th>
                                        <th>No Dokumen / Ref</th>
                                        <th>Kategori Transaksi</th>
                                        <th>Pihak / Entitas</th>
                                        <th class="text-right">Nominal (Rp)</th>
                                        <th style="width: 100px;">Status Trans</th>
                                        <th style="width: 150px;">Status & No Jurnal</th>
                                        <th style="width: 130px;">Aksi Admin</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-transaksi">
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-spinner fa-spin fa-2x text-teal mb-2"></i>
                                            <div>Memuat data seluruh transaksi...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TABLE FOOTER / PAGINATION -->
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
                        <div class="small text-muted" id="pagination-info">
                            Menampilkan 0 data
                        </div>
                        <div class="btn-group btn-group-sm" id="pagination-controls">
                            <button class="btn btn-outline-secondary" id="btn-prev-page" disabled>
                                <i class="fas fa-chevron-left"></i> Sebelumnya
                            </button>
                            <button class="btn btn-outline-secondary" id="btn-next-page" disabled>
                                Berikutnya <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: DETAIL TRANSAKSI & JURNAL BREAKDOWN                                 -->
<!-- ========================================================================= -->
<div class="modal fade" id="modal-detail-transaksi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title font-weight-bold" id="detail-modal-title">
                    <i class="fas fa-info-circle text-teal mr-2"></i>Detail Transaksi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 bg-light">
                <!-- HEADER INFO BOX -->
                <div class="card card-body bg-white shadow-sm p-3 mb-3 border">
                    <div class="row" id="detail-header-info">
                        <!-- Filled by JS -->
                    </div>
                </div>

                <!-- DETAIL TABS: ITEMS & JOURNAL -->
                <ul class="nav nav-tabs mb-2" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tab-detail-items" id="tab-link-detail-items">
                            <i class="fas fa-boxes mr-1"></i> Rincian Item / Baris Transaksi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" data-toggle="tab" href="#tab-detail-journal">
                            <i class="fas fa-book mr-1"></i> Jurnal Akuntansi (<span id="detail-journal-no-badge">-</span>)
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Tab Items / Payment Content Area -->
                    <div class="tab-pane fade show active" id="tab-detail-items">
                        <div id="detail-items-content">
                            <!-- Filled dynamically by JS based on category (Goods vs Payments) -->
                        </div>
                    </div>

                    <!-- Tab Journal -->
                    <div class="tab-pane fade" id="tab-detail-journal">
                        <div class="alert alert-info py-2 mb-2 small" id="journal-balance-alert">
                            <i class="fas fa-balance-scale mr-1"></i> Buku Besar Akuntansi: Periksa kesesuaian debit dan kredit.
                        </div>
                        <div class="table-responsive bg-white rounded border shadow-sm">
                            <table class="table table-sm table-bordered table-hover mb-0" id="table-detail-journal-lines">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th style="width: 40px;">No</th>
                                        <th style="width: 140px;">Kode Akun</th>
                                        <th>Nama Akun (COA)</th>
                                        <th>Keterangan Baris</th>
                                        <th class="text-right" style="width: 160px;">Debit (Rp)</th>
                                        <th class="text-right" style="width: 160px;">Kredit (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filled by JS -->
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="4" class="text-right">TOTAL:</td>
                                        <td class="text-right text-success" id="tot-journal-debit">Rp 0</td>
                                        <td class="text-right text-primary" id="tot-journal-kredit">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 bg-white">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EDIT TRANSAKSI DENGAN AUTO-SYNC JURNAL                              -->
<!-- ========================================================================= -->
<div class="modal fade" id="modal-edit-transaksi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form id="form-edit-transaksi">
                <div class="modal-header bg-teal text-white py-2">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-edit mr-2"></i>Edit Transaksi & Sinkronisasi Jurnal Akuntansi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3 bg-light">
                    <div class="alert alert-warning py-2 mb-3 small">
                        <i class="fas fa-sync-alt mr-1"></i>
                        <strong>Sinkronisasi Jurnal Otomatis Aktif:</strong> Mengedit data transaksi di sini (harga, kuantiti, diskon, tanggal) akan secara otomatis menghitung ulang nilai transaksi dan memperbarui/memposting ulang jurnal akuntansi terkait di Buku Besar.
                    </div>

                    <input type="hidden" id="edit-category" name="category">
                    <input type="hidden" id="edit-id-transaksi" name="id_transaksi">

                    <!-- HEADER FORM -->
                    <div class="card card-body bg-white shadow-sm p-3 mb-3 border">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="small font-weight-bold">Nomor Dokumen:</label>
                                <input type="text" class="form-control form-control-sm bg-light" id="edit-no-dokumen" readonly>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small font-weight-bold">Tanggal Transaksi:</label>
                                <input type="date" class="form-control form-control-sm" id="edit-tanggal-transaksi" name="tanggal_transaksi" required>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="small font-weight-bold">Entitas / Customer / Supplier:</label>
                                <input type="text" class="form-control form-control-sm bg-light" id="edit-nama-entitas" name="nama_entitas">
                            </div>
                            <div class="col-md-12 mb-0">
                                <label class="small font-weight-bold">Catatan / Keterangan Transaksi:</label>
                                <input type="text" class="form-control form-control-sm" id="edit-keterangan" name="keterangan">
                            </div>
                        </div>
                    </div>

                    <!-- DYNAMIC ITEMS / DETAIL EDIT AREA -->
                    <div id="edit-items-container">
                        <!-- Rendered by JS based on transaction category -->
                    </div>
                </div>
                <div class="modal-footer py-2 bg-white">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal text-white btn-sm font-weight-bold" id="btn-save-edit">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan & Sinkronkan Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: ACTIVITY LOG (AUDIT TRAIL)                                         -->
<!-- ========================================================================= -->
<div class="modal fade" id="modal-activity-log" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-teal text-white py-2">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-history mr-2"></i>Activity Log / Riwayat Audit Perubahan Transaksi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 bg-light">
                <!-- Filter Bar in Modal -->
                <div class="card card-body bg-white shadow-sm border p-2 mb-3">
                    <form id="form-filter-activity-log" class="row align-items-end">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="small font-weight-bold mb-1">Tanggal Mulai:</label>
                            <input type="date" class="form-control form-control-sm" id="log-filter-date-from" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="small font-weight-bold mb-1">Tanggal Sampai:</label>
                            <input type="date" class="form-control form-control-sm" id="log-filter-date-to" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4 col-sm-6 mb-2">
                            <label class="small font-weight-bold mb-1">Pencarian Log:</label>
                            <input type="text" class="form-control form-control-sm" id="log-filter-search" placeholder="No Faktur, No SO, User, Keterangan...">
                        </div>
                        <div class="col-md-2 col-sm-12 mb-2">
                            <button type="submit" class="btn btn-teal btn-sm btn-block text-white font-weight-bold">
                                <i class="fas fa-search mr-1"></i> Cari Log
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table Log -->
                <div class="table-responsive bg-white rounded border shadow-sm" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-sm table-bordered table-hover mb-0" id="table-activity-log">
                        <thead class="bg-light text-center sticky-top">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th style="width: 155px;">Waktu & Tanggal</th>
                                <th style="width: 165px;">Pelaku / User</th>
                                <th style="width: 140px;">No Faktur / SO</th>
                                <th>Customer</th>
                                <th style="width: 130px;">Jenis Aksi</th>
                                <th>Keterangan / Catatan</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-activity-log">
                            <!-- Filled dynamically by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2 bg-white">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- STYLES & JAVASCRIPT LOGIC                                                 -->
<!-- ========================================================================= -->
<style>
.custom-trans-tabs .nav-link {
    color: #495057;
    border-radius: 6px;
    padding: 7px 14px;
    font-size: 13.5px;
    margin-right: 4px;
    margin-bottom: 4px;
    transition: all 0.2s ease;
}
.custom-trans-tabs .nav-link:hover {
    background-color: #e9ecef;
    color: #20c997;
}
.custom-trans-tabs .nav-link.active {
    background-color: #20c997 !important;
    color: #fff !important;
    box-shadow: 0 2px 5px rgba(32, 201, 151, 0.35);
}
.btn-teal {
    background-color: #20c997;
    border-color: #20c997;
}
.btn-teal:hover {
    background-color: #17a57a;
    border-color: #17a57a;
}
.btn-outline-teal {
    color: #20c997;
    border-color: #20c997;
}
.btn-outline-teal:hover {
    color: #fff;
    background-color: #20c997;
    border-color: #20c997;
}
.text-teal {
    color: #20c997 !important;
}
.badge-trans-type {
    font-size: 11.5px;
    padding: 5px 9px;
    border-radius: 4px;
}
.table td, .table th {
    vertical-align: middle !important;
}
</style>

<script>
$(document).ready(function() {
    let currentCategory = '<?= !empty($is_admpnj_only) ? "penjualan" : "all" ?>';
    let currentPageLimit = 50;
    let currentPageOffset = 0;
    let totalRecords = 0;
    let currentAjax = null;

    // Load initial data
    loadTransactions();

    // Tab Switching with event delegation
    $('#trans-category-tabs').on('click', '.nav-link', function(e) {
        e.preventDefault();
        $('#trans-category-tabs .nav-link').removeClass('active');
        $(this).addClass('active');
        currentCategory = $(this).attr('data-category') || 'all';
        currentPageOffset = 0;
        loadTransactions();
    });

    // Form Filter Submit
    $('#form-filter-transaksi').on('submit', function(e) {
        e.preventDefault();
        currentPageOffset = 0;
        loadTransactions();
    });

    // Reset Filter
    $('#btn-reset-filter').on('click', function() {
        $('#filter-date-from').val('<?= date('Y-m-d') ?>');
        $('#filter-date-to').val('<?= date('Y-m-d') ?>');
        $('#filter-status').val('all');
        $('#filter-search').val('');
        currentPageOffset = 0;
        loadTransactions();
    });

    // Pagination buttons
    $('#btn-prev-page').on('click', function() {
        if (currentPageOffset >= currentPageLimit) {
            currentPageOffset -= currentPageLimit;
            loadTransactions();
        }
    });

    $('#btn-next-page').on('click', function() {
        if (currentPageOffset + currentPageLimit < totalRecords) {
            currentPageOffset += currentPageLimit;
            loadTransactions();
        }
    });

    // Main Function to Fetch Transactions
    function loadTransactions() {
        if (currentAjax && currentAjax.readyState !== 4) {
            currentAjax.abort();
        }

        $('#tbody-transaksi').html(`
            <tr>
                <td colspan="9" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-teal mb-2"></i>
                    <div>Memuat data transaksi...</div>
                </td>
            </tr>
        `);

        const params = {
            category: currentCategory,
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val(),
            status: $('#filter-status').val(),
            search: $('#filter-search').val(),
            limit: currentPageLimit,
            offset: currentPageOffset
        };

        currentAjax = $.ajax({
            url: "<?= base_url('admin/transaksi/data') ?>",
            type: "POST",
            data: params,
            dataType: "json",
            success: function(res) {
                if (res.success && res.data) {
                    renderTable(res.data.data);
                    renderSummary(res.data.summary);
                    renderPagination(res.data.total);
                } else {
                    $('#tbody-transaksi').html(`<tr><td colspan="9" class="text-center py-4 text-danger">${res.message || 'Gagal memuat data transaksi.'}</td></tr>`);
                }
            },
            error: function(xhr, status, error) {
                if (status === 'abort') return;
                const errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan server saat mengambil data.';
                $('#tbody-transaksi').html(`<tr><td colspan="9" class="text-center py-4 text-danger">${escapeHtml(errMsg)}</td></tr>`);
            }
        });
    }

    // Render Table Rows
    function renderTable(rows) {
        if (!rows || rows.length === 0) {
            $('#tbody-transaksi').html(`
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-2 text-secondary"></i>
                        <div>Tidak ada data transaksi yang sesuai filter.</div>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        rows.forEach(function(row, idx) {
            const no = currentPageOffset + idx + 1;
            const categoryBadge = getCategoryBadge(row.trans_category, row.trans_category_label);
            const nominal = formatRupiah(row.total_nominal);
            const statusBadge = getStatusBadge(row.status_transaksi);
            const journalBadge = getJournalBadge(row);

            html += `
                <tr>
                    <td class="text-center font-weight-bold">${no}</td>
                    <td class="text-center">${row.tanggal_transaksi || '-'}</td>
                    <td>
                        <div class="font-weight-bold text-dark">${escapeHtml(row.no_dokumen)}</div>
                        ${row.no_referensi ? `<small class="text-muted"><i class="fas fa-link mr-1"></i>${escapeHtml(row.no_referensi)}</small>` : ''}
                    </td>
                    <td>${categoryBadge}</td>
                    <td>
                        <div class="font-weight-600">${escapeHtml(row.nama_entitas || '-')}</div>
                        <small class="text-muted d-block text-truncate" style="max-width: 220px;" title="${escapeHtml(row.keterangan || '')}">${escapeHtml(row.keterangan || '')}</small>
                    </td>
                    <td class="text-right font-weight-bold text-dark">${nominal}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">${journalBadge}</td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-info btn-xs btn-detail" data-category="${row.trans_category}" data-id="${row.id_transaksi}" title="Lihat Detail & Jurnal">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-teal text-white btn-xs btn-edit" data-category="${row.trans_category}" data-id="${row.id_transaksi}" title="Edit & Sinkronkan Jurnal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-warning btn-xs btn-repost" data-category="${row.trans_category}" data-id="${row.id_transaksi}" title="Repost Transaksi & Jurnal">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-xs btn-delete" data-category="${row.trans_category}" data-id="${row.id_transaksi}" title="Hapus Transaksi & Bersihkan Jurnal">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        $('#tbody-transaksi').html(html);
    }

    // Render Top Statistic Summary Cards
    function renderSummary(summary) {
        if (!summary) return;
        $('#stat-total-count').text(Number(summary.total_count || 0).toLocaleString('id-ID'));
        $('#stat-total-nominal').text(formatRupiah(summary.total_nominal || 0));
        $('#stat-total-posted').text(Number(summary.total_posted || 0).toLocaleString('id-ID'));
        $('#stat-total-unposted').text(Number(summary.total_unposted || 0).toLocaleString('id-ID'));
    }

    // Render Pagination Controls
    function renderPagination(total) {
        totalRecords = total;
        const from = totalRecords > 0 ? currentPageOffset + 1 : 0;
        const to = Math.min(currentPageOffset + currentPageLimit, totalRecords);
        $('#pagination-info').text(`Menampilkan ${from} - ${to} dari ${Number(totalRecords).toLocaleString('id-ID')} transaksi`);

        $('#btn-prev-page').prop('disabled', currentPageOffset <= 0);
        $('#btn-next-page').prop('disabled', currentPageOffset + currentPageLimit >= totalRecords);
    }

    // Badge Helpers
    function getCategoryBadge(cat, label) {
        let bg = 'secondary';
        switch (cat) {
            case 'penjualan': bg = 'primary'; break;
            case 'pembelian': bg = 'success'; break;
            case 'pembayaran_customer': bg = 'info'; break;
            case 'pembayaran_supplier': bg = 'indigo'; break;
            case 'retur_penjualan': bg = 'warning text-dark'; break;
            case 'retur_pembelian': bg = 'orange text-white'; break;
            case 'kas_masuk': bg = 'teal text-white'; break;
            case 'kas_keluar': bg = 'danger'; break;
            case 'penyesuaian_barang': bg = 'purple text-white'; break;
            case 'jurnal_umum': bg = 'dark'; break;
        }
        return `<span class="badge badge-${bg} badge-trans-type">${escapeHtml(label || cat)}</span>`;
    }

    function getStatusBadge(status) {
        const s = String(status || '').toLowerCase();
        if (s === 'posted' || s === 'done' || s === 'selesai' || s === 'valid' || s === 'confirmed') {
            return `<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>${status}</span>`;
        } else if (s === 'draft' || s === 'pending' || s === 'diajukan' || s === 'menunggu_verifikasi') {
            return `<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-clock mr-1"></i>${status}</span>`;
        } else if (s === 'cancelled' || s === 'ditolak' || s === 'void') {
            return `<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>${status}</span>`;
        }
        return `<span class="badge badge-secondary px-2 py-1">${status || '-'}</span>`;
    }

    function getJournalBadge(row) {
        if (row.id_jurnal && row.status_jurnal === 'POSTED') {
            return `
                <a href="javascript:void(0)" class="badge badge-success px-2 py-1 btn-view-journal" data-category="${row.trans_category}" data-id="${row.id_transaksi}" title="Klik untuk lihat detail jurnal">
                    <i class="fas fa-book mr-1"></i>${escapeHtml(row.nomor_jurnal || 'POSTED')}
                </a>
            `;
        } else if (row.status_jurnal === 'REVERSED' || row.status_jurnal === 'VOID') {
            return `<span class="badge badge-danger px-2 py-1"><i class="fas fa-undo mr-1"></i>${row.status_jurnal}</span>`;
        }
        return `<span class="badge badge-secondary px-2 py-1"><i class="fas fa-question-circle mr-1"></i>UNPOSTED</span>`;
    }

    // Detail Button Click
    $(document).on('click', '.btn-detail, .btn-view-journal', function() {
        const cat = $(this).data('category');
        const id = $(this).data('id');

        $.ajax({
            url: `<?= base_url('admin/transaksi/detail/') ?>${cat}/${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    showDetailModal(res.data);
                } else {
                    Swal.fire('Error', res.message || 'Gagal memuat detail transaksi.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan saat memuat detail.', 'error');
            }
        });
    });

    // Populate and Show Detail Modal
    function showDetailModal(data) {
        const h = data.header || {};
        const cat = data.category || '';
        const docNo = h.no_dokumen || h.no_faktur || h.nomor_lpb || h.no_retur || h.no_retur_pembelian || (h.id_transaksi ? '#' + h.id_transaksi : '-');

        $('#detail-modal-title').html(`<i class="fas fa-info-circle text-teal mr-2"></i>Detail Transaksi: <strong>${escapeHtml(docNo)}</strong>`);

        let headerHtml = `
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block font-weight-bold">Kategori Transaksi:</small>
                <div>${getCategoryBadge(cat, cat.toUpperCase().replace('_', ' '))}</div>
            </div>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block font-weight-bold">Tanggal Transaksi:</small>
                <div class="font-weight-600 text-dark">${h.tanggal_transaksi || h.tanggal_faktur || h.tgl_sj || h.tanggal_pembayaran || h.tanggal_retur || '-'}</div>
            </div>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block font-weight-bold">Pihak / Entitas Terkait:</small>
                <div class="font-weight-bold text-dark">${escapeHtml(h.nama_entitas || h.nama_customer || h.nama_suplier || '-')}</div>
            </div>
            <div class="col-md-3 mb-2">
                <small class="text-muted d-block font-weight-bold">Status Transaksi:</small>
                <div>${getStatusBadge(h.status_transaksi || h.status || 'POSTED')}</div>
            </div>
        `;
        $('#detail-header-info').html(headerHtml);

        // Check if Payment vs Goods
        const isPayment = (cat === 'pembayaran_customer' || cat === 'pembayaran_supplier');
        if (isPayment) {
            $('#tab-link-detail-items').html('<i class="fas fa-money-check-alt mr-1"></i> Rincian Pembayaran');
        } else {
            $('#tab-link-detail-items').html('<i class="fas fa-boxes mr-1"></i> Rincian Item Barang');
        }

        let contentHtml = '';

        if (cat === 'pembayaran_supplier') {
            contentHtml = `
                <div class="card card-body bg-light border p-3 mb-2">
                    <h6 class="font-weight-bold text-teal mb-3"><i class="fas fa-receipt mr-1"></i> Ringkasan Pelunasan Hutang Supplier</h6>
                    <div class="row">
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Nomor Dokumen Bayar:</small><span class="font-weight-bold text-dark">${escapeHtml(h.no_dokumen || '-')}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Tanggal Pembayaran:</small><span class="text-dark">${h.tanggal_transaksi || '-'}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Supplier Penerima:</small><span class="font-weight-bold text-dark">${escapeHtml(h.nama_entitas || '-')}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Metode Pembayaran:</small><span class="badge badge-info py-1 px-2 font-13">${escapeHtml(h.payment_method || 'Kas/Bank')}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Total Pembayaran Hutang:</small><span class="font-weight-bold text-primary font-16">${formatRupiah(h.total_nominal || h.amount || 0)}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Status Pembayaran:</small>${getStatusBadge(h.status_transaksi || 'POSTED')}</div>
                        <div class="col-md-12 mt-1"><small class="text-muted d-block font-weight-bold">Catatan / Keterangan:</small><div class="bg-white p-2 rounded border">${escapeHtml(h.keterangan || '-')}</div></div>
                    </div>
                </div>
            `;
        } else if (cat === 'pembayaran_customer') {
            contentHtml = `
                <div class="card card-body bg-light border p-3 mb-2">
                    <h6 class="font-weight-bold text-teal mb-3"><i class="fas fa-hand-holding-usd mr-1"></i> Ringkasan Pelunasan Piutang Customer</h6>
                    <div class="row">
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Nomor Dokumen Bayar:</small><span class="font-weight-bold text-dark">${escapeHtml(h.no_dokumen || '-')}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">No. Faktur Penjualan:</small><span class="font-weight-bold text-teal">${escapeHtml(h.no_referensi || h.no_faktur || '-')}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Customer Pembayar:</small><span class="font-weight-bold text-dark">${escapeHtml(h.nama_entitas || '-')}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Tanggal Pembayaran:</small><span class="text-dark">${h.tanggal_transaksi || '-'}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Metode Pembayaran:</small><span class="badge badge-info py-1 px-2 font-13">${escapeHtml(h.metode_pembayaran || 'Kas/Bank')}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Status Kasir:</small>${getStatusBadge(h.status_transaksi || 'POSTED')}</div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Jumlah Bayar Tunai/Transfer:</small><span class="font-weight-bold text-success font-14">${formatRupiah(h.jumlah_pembayaran || 0)}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Potongan / Diskon Bayar:</small><span class="font-weight-bold text-warning font-14">${formatRupiah(h.jumlah_diskon || 0)}</span></div>
                        <div class="col-md-4 mb-2"><small class="text-muted d-block font-weight-bold">Total Pelunasan Piutang:</small><span class="font-weight-bold text-dark font-16">${formatRupiah(h.total_nominal || 0)}</span></div>
                        <div class="col-md-12 mt-1"><small class="text-muted d-block font-weight-bold">Catatan / Keterangan:</small><div class="bg-white p-2 rounded border">${escapeHtml(h.keterangan || '-')}</div></div>
                    </div>
                </div>
            `;
        } else {
            // Goods Tables (Penjualan, LPB, Retur Jual, Retur Beli)
            let itemRows = '';
            const items = data.items || [];
            if (items.length > 0) {
                items.forEach((it, idx) => {
                    const kdBarang = it.kd_barang || it.kd_barang_master || it.kode_barang || '-';
                    const namaBarang = it.nama_barang || it.nm_barang || it.keterangan || '-';
                    const qty = Number(it.qty || it.qty_diterima || it.qty_retur || 0);
                    const satuan = it.satuan || it.satuan_master || 'pcs';
                    const harga = Number(it.harga_satuan || it.hrg_satuan || it.harga || 0);
                    const diskon = (it.diskon_persen || it.disc ? (it.diskon_persen || it.disc) + '%' : '') + (it.diskon_rp ? ' (Rp ' + Number(it.diskon_rp).toLocaleString('id-ID') + ')' : '');
                    const subtotal = Number(it.subtotal || it.subtotal_after_disc || it.total_harga || it.total || (qty * harga));

                    itemRows += `
                        <tr>
                            <td class="text-center font-weight-bold">${idx + 1}</td>
                            <td class="text-center"><span class="badge badge-secondary py-1 px-2 font-12">${escapeHtml(kdBarang)}</span></td>
                            <td>
                                <strong>${escapeHtml(namaBarang)}</strong>
                                ${it.no_lot || it.no_batch ? `<small class="d-block text-muted">Lot/Batch: ${escapeHtml(it.no_lot || it.no_batch)} ${it.expired_date || it.exp_date ? ' | Exp: ' + (it.expired_date || it.exp_date) : ''}</small>` : ''}
                            </td>
                            <td class="text-right font-weight-bold">${qty.toLocaleString('id-ID')}</td>
                            <td class="text-center">${escapeHtml(satuan)}</td>
                            <td class="text-right font-weight-600">${formatRupiah(harga)}</td>
                            <td class="text-right">${diskon || '-'}</td>
                            <td class="text-right font-weight-bold text-dark">${formatRupiah(subtotal)}</td>
                        </tr>
                    `;
                });
            } else {
                itemRows = `<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data rincian item barang.</td></tr>`;
            }

            contentHtml = `
                <div class="table-responsive bg-white rounded border shadow-sm">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th style="width: 140px;">Kode Barang</th>
                                <th>Nama Barang</th>
                                <th style="width: 90px;" class="text-right">Qty</th>
                                <th style="width: 80px;">Satuan</th>
                                <th style="width: 150px;" class="text-right">Harga Satuan (Rp)</th>
                                <th style="width: 120px;" class="text-right">Diskon</th>
                                <th style="width: 160px;" class="text-right">Subtotal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemRows}
                        </tbody>
                    </table>
                </div>
            `;
        }

        $('#detail-items-content').html(contentHtml);

        // Render Journal Table
        let jRows = '';
        const jLines = data.journal_lines || [];
        let totDeb = 0;
        let totKre = 0;

        if (jLines.length > 0) {
            $('#detail-journal-no-badge').text(data.journal ? data.journal.nomor_jurnal : 'POSTED');
            jLines.forEach((jl, idx) => {
                const deb = Number(jl.debit || 0);
                const kre = Number(jl.kredit || 0);
                totDeb += deb;
                totKre += kre;

                jRows += `
                    <tr>
                        <td class="text-center font-weight-bold">${idx + 1}</td>
                        <td class="font-weight-bold text-teal text-center">${escapeHtml(jl.kode_akun || '-')}</td>
                        <td><strong>${escapeHtml(jl.nama_akun || '-')}</strong></td>
                        <td><small>${escapeHtml(jl.keterangan || '-')}</small></td>
                        <td class="text-right font-weight-bold text-success">${formatRupiah(deb)}</td>
                        <td class="text-right font-weight-bold text-primary">${formatRupiah(kre)}</td>
                    </tr>
                `;
            });
        } else {
            $('#detail-journal-no-badge').text('Belum Terposting');
            jRows = `<tr><td colspan="6" class="text-center text-muted py-3">Belum ada baris jurnal akuntansi untuk transaksi ini.</td></tr>`;
        }

        $('#table-detail-journal-lines tbody').html(jRows);
        $('#tot-journal-debit').text(formatRupiah(totDeb));
        $('#tot-journal-kredit').text(formatRupiah(totKre));

        $('#modal-detail-transaksi').modal('show');
    }

    // Edit Button Click
    $(document).on('click', '.btn-edit', function() {
        const cat = $(this).data('category');
        const id = $(this).data('id');

        $.ajax({
            url: "<?= base_url('admin/transaksi/edit-data') ?>/" + encodeURIComponent(cat) + "/" + encodeURIComponent(id),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showEditModal(res);
                } else {
                    Swal.fire('Error', res.message || 'Gagal mengambil form edit.', 'error');
                }
            },
            error: function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat memuat form edit.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // Populate Edit Modal
    function showEditModal(res) {
        const h = res.header || {};
        const cat = res.category || '';
        const items = res.items || [];

        $('#edit-category').val(cat);
        $('#edit-id-transaksi').val(h.id_faktur || h.id_lpb || h.id_pembayaran || h.id_retur || h.id_retur_pembelian || '');
        $('#edit-no-dokumen').val(h.no_faktur || h.nomor_lpb || h.no_dokumen || h.no_retur || h.no_retur_pembelian || h.no_referensi || '');
        $('#edit-tanggal-transaksi').val(h.tanggal_faktur || h.tgl_sj || h.tanggal_pembayaran || h.tanggal_retur || h.tanggal || '');
        $('#edit-nama-entitas').val(h.nama_customer || h.customer_name || h.nama_suplier || h.diterima_dari || h.dibayar_kepada || '');
        $('#edit-keterangan').val(h.catatan || h.keterangan || h.alasan_retur || '');

        let itemHtml = '';

        if (cat === 'penjualan' || cat === 'faktur_penjualan') {
            itemHtml = `
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header py-2 font-weight-bold bg-white">
                        <i class="fas fa-boxes mr-1"></i> Edit Item Barang Faktur Penjualan
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th>Barang</th>
                                    <th style="width: 110px;">Qty</th>
                                    <th style="width: 160px;">Harga Satuan (Rp)</th>
                                    <th style="width: 100px;">Diskon %</th>
                                    <th style="width: 130px;">Diskon Rp</th>
                                    <th style="width: 150px;">Subtotal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            items.forEach((it, idx) => {
                const itemId = it.id || it.id_faktur_detail || idx;
                const itemHarga = Number(it.hrg_satuan || it.harga_satuan || 0);
                const itemQty = Number(it.qty || 0);
                const itemDisc = Number(it.disc || it.diskon_persen || 0);
                const itemDiscRp = Number(it.diskon_rp || (itemQty * itemHarga * (itemDisc / 100)));
                const itemSubtotal = Number(it.subtotal_after_disc || it.total_harga || (itemQty * itemHarga - itemDiscRp));

                itemHtml += `
                    <tr class="edit-item-row">
                        <input type="hidden" name="items[${idx}][id_faktur_detail]" value="${itemId}">
                        <td><strong>${escapeHtml(it.nama_barang || it.kd_barang)}</strong><br><small class="text-muted">${escapeHtml(it.kd_barang || '')}</small></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right edit-qty" name="items[${idx}][qty]" value="${itemQty}" required></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right edit-harga" name="items[${idx}][harga_satuan]" value="${itemHarga}" required></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right edit-disc-pct" name="items[${idx}][diskon_persen]" value="${itemDisc}"></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right edit-disc-rp" name="items[${idx}][diskon_rp]" value="${itemDiscRp}"></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right font-weight-bold text-teal edit-subtotal" name="items[${idx}][total_harga]" value="${itemSubtotal}" required></td>
                    </tr>
                `;
            });
            itemHtml += `</tbody></table></div></div>`;
        } else if (cat === 'pembelian' || cat === 'lpb') {
            itemHtml = `
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header py-2 font-weight-bold bg-white">
                        <i class="fas fa-boxes mr-1"></i> Edit Item Penerimaan Barang (LPB)
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th>Nama Barang</th>
                                    <th style="width: 130px;">Qty Diterima</th>
                                    <th style="width: 180px;">Harga Satuan Beli (Rp)</th>
                                    <th style="width: 180px;">Total (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            items.forEach((it, idx) => {
                const itemId = it.id_detail_lpb || it.id_lpb_detail || idx;
                const itemQty = Number(it.qty_diterima || 0);
                const itemHarga = Number(it.harga_satuan || 0);
                const itemTotal = Number(it.total_harga || (itemQty * itemHarga));

                itemHtml += `
                    <tr class="edit-item-row">
                        <input type="hidden" name="items[${idx}][id_detail_lpb]" value="${itemId}">
                        <td><strong>${escapeHtml(it.nama_barang || it.kd_barang)}</strong><br><small class="text-muted">${escapeHtml(it.kd_barang || '')}</small></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right edit-qty" name="items[${idx}][qty_diterima]" value="${itemQty}" required></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right edit-harga" name="items[${idx}][harga_satuan]" value="${itemHarga}" required></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right font-weight-bold text-success edit-subtotal" name="items[${idx}][total_harga]" value="${itemTotal}" required></td>
                    </tr>
                `;
            });
            itemHtml += `</tbody></table></div></div>`;
        } else if (cat === 'retur_penjualan') {
            itemHtml = `
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header py-2 font-weight-bold bg-white">
                        <i class="fas fa-boxes mr-1"></i> Edit Item Retur Penjualan
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th>Nama Barang</th>
                                    <th style="width: 130px;">Qty Retur</th>
                                    <th style="width: 180px;">Harga Satuan (Rp)</th>
                                    <th style="width: 180px;">Subtotal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            items.forEach((it, idx) => {
                const itemId = it.id_retur_detail || idx;
                const itemQty = Number(it.qty_retur || 0);
                const itemHarga = Number(it.harga_satuan || 0);
                const itemTotal = itemQty * itemHarga;

                itemHtml += `
                    <tr class="edit-item-row">
                        <input type="hidden" name="items[${idx}][id_retur_detail]" value="${itemId}">
                        <td><strong>${escapeHtml(it.nama_barang || it.nm_barang || 'Barang Retur')}</strong>${it.kd_barang || it.kd_barang_master ? `<br><small class="text-muted">${escapeHtml(it.kd_barang || it.kd_barang_master)}</small>` : ''}</td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right edit-qty" name="items[${idx}][qty_retur]" value="${itemQty}" required></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right edit-harga" name="items[${idx}][harga_satuan]" value="${itemHarga}" required></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right font-weight-bold text-warning edit-subtotal" name="items[${idx}][subtotal]" value="${itemTotal}" required></td>
                    </tr>
                `;
            });
            itemHtml += `</tbody></table></div></div>`;
        } else if (cat === 'retur_pembelian') {
            itemHtml = `
                <div class="card card-outline card-orange shadow-sm">
                    <div class="card-header py-2 font-weight-bold bg-white text-dark">
                        <i class="fas fa-boxes mr-1"></i> Edit Item Retur Pembelian
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th>Nama Barang</th>
                                    <th style="width: 130px;">Qty Retur</th>
                                    <th style="width: 180px;">Harga Satuan (Rp)</th>
                                    <th style="width: 180px;">Total (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            items.forEach((it, idx) => {
                const itemId = it.id_detail_retur_pembelian || it.id_retur_pembelian_detail || idx;
                const itemQty = Number(it.qty_retur || 0);
                const itemHarga = Number(it.harga_satuan || 0);
                const itemTotal = Number(it.total || (itemQty * itemHarga));

                itemHtml += `
                    <tr class="edit-item-row">
                        <input type="hidden" name="items[${idx}][id_detail_retur_pembelian]" value="${itemId}">
                        <td><strong>${escapeHtml(it.nama_barang || it.nm_barang || 'Barang')}</strong>${it.kd_barang || it.kode_barang ? `<br><small class="text-muted">${escapeHtml(it.kd_barang || it.kode_barang)}</small>` : ''}</td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right edit-qty" name="items[${idx}][qty_retur]" value="${itemQty}" required></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right edit-harga" name="items[${idx}][harga_satuan]" value="${itemHarga}" required></td>
                        <td><input type="number" step="any" class="form-control form-control-sm text-right font-weight-bold text-orange edit-subtotal" name="items[${idx}][total]" value="${itemTotal}" required></td>
                    </tr>
                `;
            });
            itemHtml += `</tbody></table></div></div>`;
        } else if (cat === 'pembayaran_customer') {
            itemHtml = `
                <div class="card card-body bg-white shadow-sm border p-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Jumlah Pembayaran (Rp):</label>
                            <input type="number" step="any" class="form-control form-control-sm font-weight-bold text-success" name="total_nominal" value="${h.jumlah_pembayaran}" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Jumlah Diskon / Potongan (Rp):</label>
                            <input type="number" step="any" class="form-control form-control-sm" name="jumlah_diskon" value="${h.jumlah_diskon || 0}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold">Metode Pembayaran:</label>
                            <input type="text" class="form-control form-control-sm" name="metode_pembayaran" value="${h.metode_pembayaran || 'Kas'}">
                        </div>
                    </div>
                </div>
            `;
        } else if (cat === 'pembayaran_supplier') {
            itemHtml = `
                <div class="card card-body bg-white shadow-sm border p-3">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label class="small font-weight-bold">Total Pembayaran Hutang (Rp):</label>
                            <input type="number" step="any" class="form-control form-control-sm font-weight-bold text-primary" name="total_nominal" value="${h.amount}" required>
                        </div>
                    </div>
                </div>
            `;
        }

        $('#edit-items-container').html(itemHtml);
        $('#modal-edit-transaksi').modal('show');
    }

    // Auto-calculate subtotal in edit modal
    $(document).on('input', '.edit-qty, .edit-harga, .edit-disc-pct, .edit-disc-rp', function() {
        const row = $(this).closest('.edit-item-row');
        const qty = parseFloat(row.find('.edit-qty').val()) || 0;
        const harga = parseFloat(row.find('.edit-harga').val()) || 0;
        const discPct = parseFloat(row.find('.edit-disc-pct').val()) || 0;
        let discRp = parseFloat(row.find('.edit-disc-rp').val()) || 0;

        let subtotal = qty * harga;
        if (discPct > 0) {
            discRp = Math.round((subtotal * (discPct / 100)) * 100) / 100;
            row.find('.edit-disc-rp').val(discRp);
        }
        let total = Math.max(0, subtotal - discRp);
        row.find('.edit-subtotal').val(total);
    });

    // When Subtotal is directly modified
    $(document).on('input', '.edit-subtotal', function() {
        const row = $(this).closest('.edit-item-row');
        const subtotal = parseFloat($(this).val()) || 0;
        const qty = parseFloat(row.find('.edit-qty').val()) || 0;
        const discPct = parseFloat(row.find('.edit-disc-pct').val()) || 0;
        const discRp = parseFloat(row.find('.edit-disc-rp').val()) || 0;

        // Jika tidak ada diskon dan qty > 0, otomatis sesuaikan harga satuan
        if (discPct === 0 && discRp === 0 && qty > 0) {
            const harga = Math.round((subtotal / qty) * 100) / 100;
            row.find('.edit-harga').val(harga);
        }
    });

    // Form Edit Submit
    $('#form-edit-transaksi').on('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Simpan Perubahan?',
            text: 'Perubahan pada transaksi ini akan langsung menghitung ulang dan menyinkronkan seluruh jurnal akuntansi terkait.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#20c997',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Ya, Simpan & Sinkronkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#btn-save-edit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                const formData = $('#form-edit-transaksi').serialize();

                $.ajax({
                    url: "<?= base_url('admin/transaksi/update') ?>",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(res) {
                        $('#btn-save-edit').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan & Sinkronkan Jurnal');
                        if (res.success) {
                            $('#modal-edit-transaksi').modal('hide');
                            Swal.fire('Berhasil!', res.message, 'success');
                            loadTransactions();
                        } else {
                            Swal.fire('Gagal!', res.message || 'Terjadi kesalahan saat menyimpan.', 'error');
                        }
                    },
                    error: function(xhr) {
                        $('#btn-save-edit').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Perubahan & Sinkronkan Jurnal');
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });

    // Repost Button Click
    $(document).on('click', '.btn-repost', function() {
        const cat = $(this).data('category');
        const id = $(this).data('id');

        Swal.fire({
            title: 'Posting Ulang Transaksi?',
            text: 'Jurnal lama akan dibersihkan dan jurnal baru akan diposting kembali sesuai data transaksi terkini.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-sync-alt text-dark"></i> <span class="text-dark">Ya, Repost Transaksi</span>',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Repost...',
                    text: 'Sedang meregenerasi jurnal akuntansi...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "<?= base_url('admin/transaksi/repost') ?>",
                    type: "POST",
                    data: { category: cat, id_transaksi: id },
                    dataType: "json",
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Berhasil Repost!', res.message, 'success');
                            loadTransactions();
                        } else {
                            Swal.fire('Gagal Repost!', res.message || 'Gagal memposting ulang.', 'error');
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });

    // Delete Button Click
    $(document).on('click', '.btn-delete', function() {
        const cat = $(this).data('category');
        const id = $(this).data('id');

        Swal.fire({
            title: 'Hapus Transaksi & Jurnal?',
            text: 'Tindakan ini akan menghapus transaksi dan membersihkan seluruh jurnal akuntansi terkait secara permanen.',
            icon: 'error',
            input: 'text',
            inputPlaceholder: 'Tuliskan alasan penghapusan / void...',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus Bersih',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value) {
                    return 'Alasan penghapusan wajib diisi untuk audit log!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus Transaksi...',
                    text: 'Membersihkan data transaksi dan jurnal akuntansi...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "<?= base_url('admin/transaksi/delete') ?>",
                    type: "POST",
                    data: { category: cat, id_transaksi: id, reason: result.value },
                    dataType: "json",
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Terhapus!', res.message, 'success');
                            loadTransactions();
                        } else {
                            Swal.fire('Gagal!', res.message || 'Gagal menghapus transaksi.', 'error');
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });

    // =========================================================================
    // ACTIVITY LOG / AUDIT TRAIL LOGIC
    // =========================================================================
    $('#btn-open-activity-log').on('click', function() {
        $('#modal-activity-log').modal('show');
        loadActivityLogs();
    });

    $('#form-filter-activity-log').on('submit', function(e) {
        e.preventDefault();
        loadActivityLogs();
    });

    function loadActivityLogs() {
        $('#tbody-activity-log').html(`
            <tr>
                <td colspan="7" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-teal mb-2"></i>
                    <div>Memuat riwayat activity log...</div>
                </td>
            </tr>
        `);

        const params = {
            date_from: $('#log-filter-date-from').val(),
            date_to: $('#log-filter-date-to').val(),
            search: $('#log-filter-search').val(),
            limit: 100,
            offset: 0
        };

        $.ajax({
            url: "<?= base_url('admin/transaksi/activity-logs') ?>",
            type: "POST",
            data: params,
            dataType: "json",
            success: function(res) {
                if (res.success && res.data) {
                    renderActivityLogs(res.data.data);
                } else {
                    $('#tbody-activity-log').html(`<tr><td colspan="7" class="text-center py-4 text-danger">${res.message || 'Gagal memuat activity log.'}</td></tr>`);
                }
            },
            error: function(xhr) {
                const errMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan server saat mengambil activity log.';
                $('#tbody-activity-log').html(`<tr><td colspan="7" class="text-center py-4 text-danger">${escapeHtml(errMsg)}</td></tr>`);
            }
        });
    }

    function renderActivityLogs(rows) {
        if (!rows || rows.length === 0) {
            $('#tbody-activity-log').html(`
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2 text-secondary"></i>
                        <div>Tidak ada riwayat aktivitas yang sesuai filter.</div>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        rows.forEach((row, idx) => {
            let badgeAksi = '<span class="badge badge-secondary">LOG</span>';
            const aksi = String(row.aksi || '').toUpperCase();
            if (aksi.indexOf('EDIT') !== -1) {
                badgeAksi = `<span class="badge badge-warning font-weight-bold"><i class="fas fa-edit mr-1"></i>${escapeHtml(aksi)}</span>`;
            } else if (aksi.indexOf('REPOST') !== -1) {
                badgeAksi = `<span class="badge badge-info font-weight-bold"><i class="fas fa-sync-alt mr-1"></i>${escapeHtml(aksi)}</span>`;
            } else if (aksi.indexOf('DELETE') !== -1 || aksi.indexOf('VOID') !== -1) {
                badgeAksi = `<span class="badge badge-danger font-weight-bold"><i class="fas fa-trash mr-1"></i>${escapeHtml(aksi)}</span>`;
            } else {
                badgeAksi = `<span class="badge badge-primary font-weight-bold">${escapeHtml(aksi)}</span>`;
            }

            const custName = row.master_customer_name || row.fp_customer_name || '-';
            const noDok = row.no_faktur || row.no_so || '-';
            const ip = row.ip_address ? `<br><small class="text-muted"><i class="fas fa-network-wired mr-1"></i>${escapeHtml(row.ip_address)}</small>` : '';

            html += `
                <tr>
                    <td class="text-center font-weight-bold text-muted">${idx + 1}</td>
                    <td class="text-center small"><i class="far fa-clock text-teal mr-1"></i>${escapeHtml(row.created_at || '-')}</td>
                    <td class="font-weight-bold text-dark small"><i class="fas fa-user-edit mr-1 text-secondary"></i>${escapeHtml(row.dilakukan_oleh || 'System')}${ip}</td>
                    <td class="text-center"><span class="badge badge-light border text-dark font-weight-bold">${escapeHtml(noDok)}</span></td>
                    <td class="small text-secondary">${escapeHtml(custName)}</td>
                    <td class="text-center">${badgeAksi}</td>
                    <td class="small text-dark">${escapeHtml(row.keterangan || '-')}</td>
                </tr>
            `;
        });

        $('#tbody-activity-log').html(html);
    }

    // Format Rupiah Helper
    function formatRupiah(amount) {
        const num = parseFloat(amount) || 0;
        return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    // Escape HTML Helper
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
</script>
