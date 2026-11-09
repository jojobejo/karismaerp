<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header py-2">
            <div class="container-fluid">
                <div class="row mb-1 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold h5 text-dark">
                            <i class="fas fa-address-book text-primary mr-2"></i> Data Customers
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right small mb-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Data Customers</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- CARD UTAMA APLIKASI (Layout Berbasis Bootstrap & Flexbox) -->
                <div class="card card-outline card-primary cust-main-card shadow-sm mb-0">
                    <div class="card-body p-0 d-flex flex-column flex-md-row" style="height: 100%;">

                        <!-- ============================================== -->
                        <!-- PANEL KIRI: DAFTAR PILIHAN CUSTOMER            -->
                        <!-- ============================================== -->
                        <div class="cust-sidebar bg-light border-right d-flex flex-column">

                            <!-- Search Bar Header Panel Kiri -->
                            <div class="p-2 bg-white border-bottom">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0">
                                            <i class="fas fa-search text-muted" id="searchIcon"></i>
                                            <span class="spinner-border spinner-border-sm text-primary d-none" id="searchSpinner" role="status"></span>
                                        </span>
                                    </div>
                                    <input type="text" id="searchCustomer" class="form-control border-left-0" placeholder="Ketik nama kios, kode, rute..." autocomplete="off" style="color: #1e293b !important; background-color: #ffffff !important;">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary border-left-0 d-none" id="btnClearSearch" title="Reset pencarian">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                    <small class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">PILIHAN CUSTOMER</small>
                                    <span class="badge badge-primary" id="badgeCountCustomer"><?= number_format((int)$total_customers, 0, ',', '.') ?> Total</span>
                                </div>
                            </div>

                            <!-- List Item Container (Scrollable) -->
                            <div id="customerList" class="cust-list-container flex-grow-1 overflow-auto p-2" style="scrollbar-width: thin;">
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $c): ?>
                                        <?php
                                            $label = trim((string)($c->nama_kios ?: $c->nama_customer ?: ''));
                                            if ($label === '') $label = '— Tanpa Nama —';
                                            $phone = trim((string)($c->telp1 ?: '-'));
                                            $initial = strtoupper(mb_substr($label, 0, 1, 'UTF-8'));
                                        ?>
                                        <div class="cust-item card mb-1 border cursor-pointer"
                                             data-id="<?= $c->id ?>"
                                             data-label="<?= htmlspecialchars($label) ?>">
                                            <div class="card-body p-2 d-flex align-items-center">
                                                <div class="cust-avatar bg-primary text-white rounded font-weight-bold mr-2 d-flex align-items-center justify-content-center">
                                                    <?= htmlspecialchars($initial) ?>
                                                </div>
                                                <div class="cust-info flex-grow-1 overflow-hidden" style="min-width: 0;">
                                                    <div class="font-weight-bold text-truncate small text-dark cust-title">
                                                        <?= htmlspecialchars($label) ?>
                                                    </div>
                                                    <div class="d-flex align-items-center" style="gap: 4px;">
                                                        <?php if (!empty($c->kd_customer)): ?>
                                                            <span class="badge badge-success px-1" style="font-size: 9px;"><?= htmlspecialchars($c->kd_customer) ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($c->kd_rute)): ?>
                                                            <span class="badge badge-secondary px-1" style="font-size: 9px;"><?= htmlspecialchars($c->kd_rute) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-muted text-truncate" style="font-size: 11px;">
                                                        <i class="fas fa-phone-alt mr-1"></i><?= htmlspecialchars($phone) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted small" id="emptyStateCust">
                                        <i class="fas fa-info-circle mb-1"></i> Belum ada data customer.
                                    </div>
                                <?php endif; ?>

                                <!-- Loading More Indicator -->
                                <div id="loadingMore" class="text-center py-2 d-none">
                                    <span class="spinner-border spinner-border-sm text-primary"></span>
                                    <small class="text-muted ml-1">Memuat data berikutnya...</small>
                                </div>
                            </div>

                            <!-- Footer List Panel Kiri: Informasi Data (Tanpa Tombol Action) -->
                            <div class="p-2 bg-white border-top text-center">
                                <small class="text-muted">
                                    <i class="fas fa-database text-primary mr-1"></i>
                                    Menampilkan <span id="displayedCountText"><?= count($customers) ?></span> customer
                                </small>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- PANEL KANAN: FORM DETAIL & TOOLBAR AKSI        -->
                        <!-- ============================================== -->
                        <div class="cust-detail-panel flex-grow-1 d-flex flex-column bg-white" style="min-width: 0;">

                            <!-- 1. Header Panel Kanan: Judul Customer & Toolbar Aksi Sisi Kanan -->
                            <div class="cust-header-bar p-3 bg-white border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                                <div class="d-flex align-items-center" style="gap: 12px; min-width: 0;">
                                    <div class="cust-header-icon bg-light text-primary rounded p-2 text-center" style="width: 44px; height: 44px;">
                                        <i class="fas fa-store fa-lg mt-1" id="headerIcon"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <h2 class="h6 font-weight-bold text-dark text-truncate mb-0" id="formTitle">
                                            Pilih Customer di Sisi Kiri
                                        </h2>
                                        <small class="text-muted text-truncate d-block" id="headerSubtitle">
                                            Klik salah satu customer dari daftar untuk melihat atau mengedit informasi lengkap.
                                        </small>
                                    </div>
                                </div>

                                <!-- TOOLBAR AKSI DI SISI KANAN: TAMBAH, EDIT, HAPUS, REFRESH -->
                                <div class="cust-actions d-flex align-items-center" style="gap: 6px;">
                                    <button type="button" class="btn btn-primary btn-sm font-weight-bold" onclick="newCustomer()" title="Tambah data customer baru">
                                        <i class="fas fa-plus mr-1"></i> Tambah
                                    </button>
                                    <button type="button" id="btnEditHeader" class="btn btn-outline-primary btn-sm font-weight-bold" onclick="editCustomer()" disabled title="Edit customer yang sedang dipilih">
                                        <i class="fas fa-pencil-alt mr-1"></i> Edit
                                    </button>
                                    <button type="button" id="btnDeleteHeader" class="btn btn-outline-danger btn-sm font-weight-bold" onclick="deleteCustomer()" disabled title="Hapus customer yang sedang dipilih">
                                        <i class="fas fa-trash-alt mr-1"></i> Hapus
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="location.reload()" title="Muat ulang halaman">
                                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Form Customer (Flex column dengan body scroll & footer sticky di bawah) -->
                            <form id="formCustomer" onsubmit="saveCustomer(event)" class="d-flex flex-column flex-grow-1" style="min-height: 0; margin-bottom: 0;">
                                <input type="hidden" name="id" id="custId">

                                <!-- Form Body: Scrollable -->
                                <div class="cust-form-body flex-grow-1 overflow-auto p-3 bg-light" style="scrollbar-width: thin;">
                                    <!-- Nav Tabs Bootstrap Standar -->
                                    <ul class="nav nav-tabs mb-3" id="customerTabs">
                                        <li class="nav-item">
                                            <a class="nav-link active font-weight-bold" data-toggle="tab" href="#tabUmum">
                                                <i class="fas fa-id-card text-primary mr-1"></i> Informasi Umum & Finansial
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link font-weight-bold" data-toggle="tab" href="#tabAlamat">
                                                <i class="fas fa-map-marker-alt text-danger mr-1"></i> Alamat & Operasional Kios
                                            </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content">
                                        <!-- TAB 1: INFORMASI UMUM & FINANSIAL -->
                                        <div class="tab-pane fade show active" id="tabUmum">
                                            <div class="row">
                                                <!-- Kolom Kiri: Identitas Customer -->
                                                <div class="col-md-6 mb-3">
                                                    <div class="card card-default border mb-0">
                                                        <div class="card-header bg-white py-2">
                                                            <strong class="text-primary small text-uppercase">
                                                                <i class="fas fa-user-tag mr-1"></i> Identitas Customer
                                                            </strong>
                                                        </div>
                                                        <div class="card-body p-3">
                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm">ID / Kode Customer :</label>
                                                                <div class="col-sm-7">
                                                                    <input type="text" name="kd_customer" id="kd_customer" class="form-control form-control-sm field-input" placeholder="Kode Customer" readonly>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm font-weight-bold">Nama Kios / Usaha :</label>
                                                                <div class="col-sm-7">
                                                                    <input type="text" name="nama_kios" id="nama_kios" class="form-control form-control-sm field-input font-weight-bold" placeholder="Nama Kios" readonly required>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm">Kontak Person (Nama) :</label>
                                                                <div class="col-sm-7">
                                                                    <input type="text" name="nama_customer" id="nama_customer" class="form-control form-control-sm field-input" placeholder="Nama Pemilik" readonly>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm">Tipe Relasi :</label>
                                                                <div class="col-sm-7">
                                                                    <select name="tipe" id="tipe" class="form-control form-control-sm field-input" disabled>
                                                                        <option value="Customer">Customer</option>
                                                                        <option value="Supplier">Supplier</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm">No. Telepon 1 :</label>
                                                                <div class="col-sm-7">
                                                                    <input type="text" name="telp1" id="telp1" class="form-control form-control-sm field-input" placeholder="08xxxxxxxxxx" readonly>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm">No. Telepon 2 :</label>
                                                                <div class="col-sm-7">
                                                                    <input type="text" name="telp2" id="telp2" class="form-control form-control-sm field-input" placeholder="Nomor cadangan" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Kolom Kanan: Plafon Kredit, Sales & Rute -->
                                                <div class="col-md-6 mb-3">
                                                    <div class="card card-default border mb-0">
                                                        <div class="card-header bg-white py-2">
                                                            <strong class="text-success small text-uppercase">
                                                                <i class="fas fa-hand-holding-usd mr-1"></i> Plafon Kredit & Rute
                                                            </strong>
                                                        </div>
                                                        <div class="card-body p-3">
                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm font-weight-bold">Batas Kredit (Plafon) :</label>
                                                                <div class="col-sm-7">
                                                                    <div class="input-group input-group-sm">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text font-weight-bold">Rp</span>
                                                                        </div>
                                                                        <input type="text" name="plafon_aktif" id="plafon_aktif" class="form-control form-control-sm field-input text-right font-weight-bold text-success" placeholder="0" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm">Klasifikasi (Sales) :</label>
                                                                <div class="col-sm-7">
                                                                    <input type="text" name="nama_sales" id="nama_sales" class="form-control form-control-sm field-input" placeholder="Nama sales" readonly>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm">Rute Pengiriman :</label>
                                                                <div class="col-sm-7">
                                                                    <input type="text" name="kd_rute" id="kd_rute" class="form-control form-control-sm field-input" placeholder="Kode rute" readonly>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm">Regional / Wilayah :</label>
                                                                <div class="col-sm-7">
                                                                    <input type="text" name="regional" id="regional" class="form-control form-control-sm field-input" placeholder="Wilayah regional" readonly>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm">Jam Operasional :</label>
                                                                <div class="col-sm-7">
                                                                    <input type="text" name="jam_buka_tutup" id="jam_buka_tutup" class="form-control form-control-sm field-input" placeholder="Contoh: 08.00 - 17.00" readonly>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row mb-2">
                                                                <label class="col-sm-5 col-form-label col-form-label-sm">Karakteristik Kios :</label>
                                                                <div class="col-sm-7">
                                                                    <input type="text" name="karakteristik_kios" id="karakteristik_kios" class="form-control form-control-sm field-input" placeholder="Karakteristik kios" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- TAB 2: ALAMAT & OPERASIONAL -->
                                        <div class="tab-pane fade" id="tabAlamat">
                                            <div class="card card-default border mb-0">
                                                <div class="card-header bg-white py-2">
                                                    <strong class="text-danger small text-uppercase">
                                                        <i class="fas fa-map-marker-alt mr-1"></i> Alamat & Lokasi Kios
                                                    </strong>
                                                </div>
                                                <div class="card-body p-3">
                                                    <div class="form-group row mb-0">
                                                        <label class="col-sm-3 col-form-label col-form-label-sm">Alamat Lengkap Kios :</label>
                                                        <div class="col-sm-9">
                                                            <textarea name="alamat_kios" id="alamat_kios" class="form-control field-input" rows="5" placeholder="Masukkan alamat lengkap toko, patokan jalan, atau catatan pengiriman..." readonly style="resize:none;"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. FOOTER STICKY DI PALING BAWAH HALAMAN -->
                                <!-- Memuat Tombol Baru (Kiri), Batal & Rekam (Kanan) -->
                                <div class="cust-footer-bar p-3 bg-white border-top d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="button" class="btn btn-outline-primary btn-sm px-3 font-weight-bold" onclick="newCustomer()">
                                            <i class="fas fa-plus mr-1"></i> Baru
                                        </button>
                                    </div>
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <button type="button" id="btnBatal" class="btn btn-secondary btn-sm px-4 font-weight-bold" onclick="cancelEdit()" disabled>
                                            <i class="fas fa-times mr-1"></i> Batal
                                        </button>
                                        <button type="submit" id="btnRekam" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm" disabled>
                                            <i class="fas fa-save mr-1"></i> Rekam
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div><!-- /card cust-main-card -->
            </div>
        </section>
    </div>

</div><!-- /wrapper -->

<style>
    /* Styling Ringan Berbasis Bootstrap 4 Sistem Karisma */
    .cust-main-card {
        height: calc(100vh - 130px);
        min-height: 560px;
    }

    .cust-sidebar {
        width: 320px;
        min-width: 290px;
        max-width: 360px;
        height: 100%;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .cust-avatar {
        width: 36px;
        height: 36px;
        font-size: 14px;
        flex-shrink: 0;
    }

    .cust-item {
        background-color: #ffffff;
        border: 1px solid #dee2e6 !important;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .cust-item:hover {
        background-color: #f1f5f9;
        border-color: #adb5bd !important;
    }

    .cust-item.active-item {
        background-color: #e3f2fd !important;
        border-color: #007bff !important;
        border-left: 4px solid #007bff !important;
    }

    .cust-item.active-item .cust-title {
        color: #007bff !important;
    }

    .field-input[readonly],
    .field-input[disabled] {
        background-color: #f8f9fa !important;
        cursor: default;
    }

    .field-input:not([readonly]):not([disabled]) {
        background-color: #ffffff !important;
    }

    .field-input:not([readonly]):not([disabled]):focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
    }

    @media (max-width: 767.98px) {
        .cust-main-card {
            height: auto;
        }

        .cust-sidebar {
            width: 100%;
            max-width: 100%;
            height: 320px;
        }
    }
</style>

<script>
    let currentId = null;
    let isEditing = false;
    let currentOffset = 50;
    let isLoadingMore = false;
    let hasMoreData = true;
    let searchDebounceTimer = null;
    let currentSearchQuery = '';

    $(document).ready(function() {

        // ============================================================
        // Pencarian Cepat via AJAX (Debounce 250ms)
        // ============================================================
        $('#searchCustomer').on('input', function() {
            const q = $(this).val().trim();
            currentSearchQuery = q;

            if (q.length > 0) {
                $('#btnClearSearch').removeClass('d-none');
            } else {
                $('#btnClearSearch').addClass('d-none');
            }

            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function() {
                fetchCustomerList(q, 0, true);
            }, 250);
        });

        $('#btnClearSearch').on('click', function() {
            $('#searchCustomer').val('');
            $(this).addClass('d-none');
            currentSearchQuery = '';
            fetchCustomerList('', 0, true);
            $('#searchCustomer').focus();
        });

        // ============================================================
        // Infinite Scroll pada List Customer (Muat Otomatis saat Scroll Dasar)
        // ============================================================
        $('#customerList').on('scroll', function() {
            if (isLoadingMore || !hasMoreData) return;

            const container = $(this);
            if (container.scrollTop() + container.innerHeight() >= container[0].scrollHeight - 60) {
                $('#loadingMore').removeClass('d-none');
                fetchCustomerList(currentSearchQuery, currentOffset, false);
                setTimeout(function() { $('#loadingMore').addClass('d-none'); }, 600);
            }
        });

        // ============================================================
        // Pilih Customer dari List (Delegated Event)
        // ============================================================
        $(document).on('click', '.cust-item', function(e) {
            e.preventDefault();
            if (isEditing) {
                if (!confirm('Ada perubahan yang belum disimpan. Lanjut pindah customer?')) return;
                disableForm();
            }

            $('.cust-item').removeClass('active-item');
            $(this).addClass('active-item');

            const id = $(this).attr('data-id') || $(this).data('id');
            if (id) {
                loadDetail(id);
            }
        });

        // Format angka ribuan pada field plafon saat diedit
        $('#plafon_aktif').on('input', function() {
            let val = $(this).val().replace(/\D/g, '');
            if (val) {
                val = parseInt(val, 10).toLocaleString('id-ID');
            }
            $(this).val(val);
        });

    }); // end document.ready

    // ============================================================
    // Fungsi Fetch Customer List dari Server via AJAX
    // ============================================================
    function fetchCustomerList(query, offset, resetList) {
        if (resetList) {
            currentOffset = 0;
            hasMoreData = true;
            $('#searchIcon').addClass('d-none');
            $('#searchSpinner').removeClass('d-none');
        }

        isLoadingMore = true;

        $.ajax({
            url: "<?= base_url('data_customers/get_list') ?>",
            type: "GET",
            data: {
                q: query,
                offset: offset,
                limit: 50
            },
            dataType: "json",
            success: function(res) {
                $('#searchIcon').removeClass('d-none');
                $('#searchSpinner').addClass('d-none');
                isLoadingMore = false;

                if (!res || !res.data) return;

                hasMoreData = res.has_more;
                currentOffset = offset + res.data.length;

                if (resetList) {
                    $('#customerList .cust-item').remove();
                }

                if (res.data.length === 0 && resetList) {
                    if ($('#emptyStateCust').length === 0) {
                        $('#customerList').prepend('<div class="text-center py-4 text-muted small" id="emptyStateCust"><i class="fas fa-search mb-1 d-block text-secondary"></i> Tidak ditemukan customer dengan kata kunci tersebut.</div>');
                    }
                    $('#displayedCountText').text('0');
                    return;
                }

                $('#emptyStateCust').remove();

                let html = '';
                $.each(res.data, function(i, c) {
                    const label = (c.nama_kios || c.nama_customer || '— Tanpa Nama —').trim();
                    const phone = (c.telp1 || '-').trim();
                    const initial = label.charAt(0).toUpperCase() || 'C';
                    const activeClass = (currentId && currentId == c.id) ? 'active-item' : '';

                    html += `
                        <div class="cust-item card mb-1 border cursor-pointer ${activeClass}"
                             data-id="${c.id}"
                             data-label="${escapeHtml(label)}">
                            <div class="card-body p-2 d-flex align-items-center">
                                <div class="cust-avatar bg-primary text-white rounded font-weight-bold mr-2 d-flex align-items-center justify-content-center">
                                    ${escapeHtml(initial)}
                                </div>
                                <div class="cust-info flex-grow-1 overflow-hidden" style="min-width: 0;">
                                    <div class="font-weight-bold text-truncate small text-dark cust-title">
                                        ${escapeHtml(label)}
                                    </div>
                                    <div class="d-flex align-items-center" style="gap: 4px;">
                                        ${c.kd_customer ? `<span class="badge badge-success px-1" style="font-size: 9px;">${escapeHtml(c.kd_customer)}</span>` : ''}
                                        ${c.kd_rute ? `<span class="badge badge-secondary px-1" style="font-size: 9px;">${escapeHtml(c.kd_rute)}</span>` : ''}
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size: 11px;">
                                        <i class="fas fa-phone-alt mr-1"></i>${escapeHtml(phone)}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                if (resetList) {
                    $('#customerList').html(html);
                } else {
                    $('#customerList').append(html);
                }

                $('#displayedCountText').text($('#customerList .cust-item').length);
            },
            error: function() {
                $('#searchIcon').removeClass('d-none');
                $('#searchSpinner').addClass('d-none');
                isLoadingMore = false;
            }
        });
    }

    // ============================================================
    // Load Detail Customer via AJAX
    // ============================================================
    function loadDetail(id) {
        if (!id) return;
        currentId = id;

        // Feedback loading
        $('#formTitle').html('<span class="spinner-border spinner-border-sm text-primary mr-1"></span> Memuat data customer...');
        $('#headerSubtitle').html('<span class="text-muted small">Mengambil informasi detail...</span>');

        $.ajax({
            url: "<?= base_url('data_customers/get_detail') ?>",
            type: "GET",
            data: { id: id },
            dataType: "json",
            success: function(data) {
                if (!data) {
                    Swal.fire('Error', 'Data customer tidak ditemukan.', 'error');
                    $('#formTitle').text('Pilih Customer di Sisi Kiri');
                    return;
                }
                fillForm(data);
                disableForm();

                const label = data.nama_kios || data.nama_customer || '— Tanpa Nama —';
                $('#formTitle').text(label);

                let subInfo = [];
                if (data.kd_customer) subInfo.push('Kode: <strong class="text-primary">' + escapeHtml(data.kd_customer) + '</strong>');
                if (data.nama_sales) subInfo.push('Sales: <strong>' + escapeHtml(data.nama_sales) + '</strong>');
                if (data.kd_rute) subInfo.push('Rute: <strong>' + escapeHtml(data.kd_rute) + '</strong>');
                if (data.regional) subInfo.push('Wilayah: <strong>' + escapeHtml(data.regional) + '</strong>');

                $('#headerSubtitle').html(subInfo.length ? subInfo.join(' &middot; ') : 'Detail data customer');
                $('#headerIcon').attr('class', 'fas fa-store fa-lg mt-1 text-primary');

                // Aktifkan tombol Edit dan Hapus di Header Kanan
                $('#btnEditHeader').prop('disabled', false);
                $('#btnDeleteHeader').prop('disabled', false);
            },
            error: function(xhr, status, err) {
                $('#formTitle').text('Gagal memuat data');
                $('#headerSubtitle').html('<span class="text-danger small">Gagal mengambil detail customer dari server.</span>');
                Swal.fire('Error', 'Gagal memuat detail data customer.', 'error');
            }
        });
    }

    function fillForm(data) {
        $('#custId').val(data.id || '');
        $('#kd_customer').val(data.kd_customer || '');
        $('#nama_kios').val(data.nama_kios || '');
        $('#nama_sales').val(data.nama_sales || '');
        $('#nama_customer').val(data.nama_customer || '');
        $('#kd_rute').val(data.kd_rute || '');
        $('#telp1').val(data.telp1 || '');
        $('#telp2').val(data.telp2 || '');

        const plafon = parseFloat(data.plafon_aktif) || 0;
        $('#plafon_aktif').val(plafon > 0 ? plafon.toLocaleString('id-ID') : '0');

        $('#regional').val(data.regional || '');
        $('#jam_buka_tutup').val(data.jam_buka_tutup || '');
        $('#karakteristik_kios').val(data.karakteristik_kios || '');
        $('#alamat_kios').val(data.alamat_kios || '');
        $('#tipe').val('Customer');
    }

    function clearForm() {
        $('#formCustomer')[0].reset();
        $('#custId').val('');
        $('#tipe').val('Customer');
        $('#formTitle').text('— Tambah Customer Baru —');
        $('#headerSubtitle').html('<span class="text-success font-weight-bold">Mode Penginputan Data Baru</span>');
        $('#headerIcon').attr('class', 'fas fa-user-plus fa-lg mt-1 text-success');
    }

    // ============================================================
    // Aktifkan / Nonaktifkan Form
    // ============================================================
    function enableForm() {
        isEditing = true;
        $('.field-input').each(function() {
            if ($(this).is('select')) {
                $(this).prop('disabled', false);
            } else {
                $(this).prop('readonly', false);
            }
        });

        $('#btnRekam').prop('disabled', false);
        $('#btnBatal').prop('disabled', false);
    }

    function disableForm() {
        isEditing = false;
        $('.field-input').each(function() {
            if ($(this).is('select')) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('readonly', true);
            }
        });

        $('#btnRekam').prop('disabled', true);
        $('#btnBatal').prop('disabled', true);
    }

    // ============================================================
    // Aksi Tombol Pengelolaan
    // ============================================================
    function newCustomer() {
        $('.cust-item').removeClass('active-item');
        currentId = null;
        $('#btnEditHeader').prop('disabled', true);
        $('#btnDeleteHeader').prop('disabled', true);

        clearForm();
        enableForm();
        $('#customerTabs a[href="#tabUmum"]').tab('show');
        $('#kd_customer').focus();
    }

    function editCustomer() {
        if (!currentId) {
            Swal.fire('Perhatian', 'Pilih customer yang ingin diedit dari daftar terlebih dahulu!', 'warning');
            return;
        }
        enableForm();
        $('#customerTabs a[href="#tabUmum"]').tab('show');
        $('#nama_kios').focus();
    }

    function cancelEdit() {
        if (currentId) {
            loadDetail(currentId);
        } else {
            clearForm();
            disableForm();
            $('#formTitle').text('Pilih Customer di Sisi Kiri');
            $('#headerSubtitle').html('<span class="text-muted small">Klik salah satu customer dari daftar untuk melihat atau mengedit informasi lengkap.</span>');
            $('#headerIcon').attr('class', 'fas fa-store fa-lg mt-1 text-primary');
        }
    }

    function deleteCustomer() {
        if (!currentId) {
            Swal.fire('Perhatian', 'Pilih customer yang ingin dihapus terlebih dahulu!', 'warning');
            return;
        }

        const namaKios = $('#nama_kios').val() || $('#nama_customer').val() || 'Customer ini';

        Swal.fire({
            title: 'Hapus Customer?',
            html: 'Yakin ingin menghapus <strong>' + escapeHtml(namaKios) + '</strong>?<br><small class="text-danger">Tindakan ini tidak dapat dibatalkan!</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post("<?= base_url('data_customers/delete') ?>", { id: currentId }, function(res) {
                    if (res && res.status === 'success') {
                        Swal.fire('Berhasil!', 'Data customer berhasil dihapus.', 'success')
                            .then(function() { location.reload(); });
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menghapus data.', 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
                });
            }
        });
    }

    function saveCustomer(e) {
        e.preventDefault();

        const rawPlafon = ($('#plafon_aktif').val() || '').toString().replace(/\./g, '').replace(',', '.');
        $('#plafon_aktif').val(rawPlafon);

        $.post("<?= base_url('data_customers/store') ?>", $('#formCustomer').serialize(), function(res) {
            if (res && res.status === 'success') {
                Swal.fire('Berhasil!', 'Data customer berhasil disimpan.', 'success')
                    .then(function() { location.reload(); });
            } else {
                Swal.fire('Gagal', res.message || 'Gagal menyimpan data.', 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>
