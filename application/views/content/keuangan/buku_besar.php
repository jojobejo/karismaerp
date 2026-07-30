<!-- application/views/content/keuangan/buku_besar.php -->
<style>
    :root {
        --zahir-blue: #127fad;
        --zahir-dark-blue: #0f6c94;
        --zahir-light-bg: #f4f6f9;
        --zahir-card-border: #d2d6de;
        --zahir-text: #333333;
        --zahir-header-bg: #127fad;
    }

    body.hold-transition {
        background-color: var(--zahir-light-bg);
    }

    .buku-besar-wrapper {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--zahir-text);
        padding: 15px;
    }

    .zahir-card {
        background: #fff;
        border: 1px solid var(--zahir-card-border);
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        margin-bottom: 20px;
    }

    .zahir-top-bar {
        background-color: #ffffff;
        border-bottom: 1px solid var(--zahir-card-border);
        padding: 10px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .zahir-top-bar h1 {
        font-size: 20px;
        font-weight: 600;
        margin: 0;
        color: #111;
    }

    .zahir-toolbar {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .toolbar-link {
        color: var(--zahir-blue);
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        font-size: 13px;
    }

    .toolbar-link:hover {
        color: var(--zahir-dark-blue);
        text-decoration: underline;
    }

    .zahir-table-container {
        height: 500px;
        max-height: 65vh;
        background-color: transparent;
        overflow-y: auto;
    }

    .zahir-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    .zahir-table th {
        background-color: var(--zahir-blue) !important;
        color: #fff !important;
        font-weight: 500;
        padding: 12px 15px;
        font-size: 13px;
        text-transform: capitalize;
        letter-spacing: 0.3px;
        border: none;
        position: -webkit-sticky;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .zahir-table td {
        padding: 12px 15px;
        font-size: 13px;
        border-bottom: 1px solid #eef2f5;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        background-color: #fff;
    }

    .zahir-table td.text-right-no-ellipsis {
        overflow: visible;
        text-overflow: clip;
        white-space: nowrap;
    }

    .zahir-table tbody tr.account-header-row td {
        background-color: #ffffff !important;
        font-weight: bold;
        font-size: 14px;
        color: var(--zahir-blue);
        border-top: 2px solid var(--zahir-blue);
        padding: 10px 12px;
    }

    .zahir-table tbody tr.empty-row td {
        background-color: #ffffff;
        text-align: center;
        padding: 30px;
        color: #888;
        font-style: italic;
    }

    /* Drilldown rows: clickable transaction rows */
    .zahir-table tbody tr.ledger-drilldown-row {
        cursor: pointer;
    }
    .zahir-table tbody tr.ledger-drilldown-row:hover td {
        background-color: #e3f2fd !important;
        transition: background-color 0.15s ease;
    }
    .zahir-table tbody tr.ledger-drilldown-row:active td {
        background-color: #b5d9f5 !important;
    }

    .zahir-footer-summary {
        background-color: var(--zahir-blue);
        color: #fff;
        padding: 0;
        font-size: 13px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px 40px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px dotted rgba(255,255,255,0.3);
        padding-bottom: 3px;
    }

    .summary-item strong {
        font-size: 14px;
    }

    .zahir-action-bar {
        background-color: #f4f6f9;
        border-top: 1px solid var(--zahir-card-border);
        padding: 12px 15px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-zahir {
        font-size: 13px;
        font-weight: 500;
        padding: 5px 25px;
        border-radius: 3px;
        min-width: 90px;
    }

    .btn-zahir-primary {
        background-color: var(--zahir-blue);
        border: 1px solid var(--zahir-dark-blue);
        color: #fff;
    }

    .btn-zahir-primary:hover {
        background-color: var(--zahir-dark-blue);
        color: #fff;
    }

    .btn-zahir-secondary {
        background-color: #ffffff;
        border: 1px solid var(--zahir-card-border);
        color: #333;
    }

    .btn-zahir-secondary:hover {
        background-color: #e6e6e6;
        color: #333;
    }

    /* Modal dialog custom styling to match desktop look */
    .zahir-modal .modal-content {
        border-radius: 4px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        border: 1px solid #999;
    }

    .zahir-modal .modal-header {
        background-color: #f0f0f0;
        color: #333;
        border-bottom: 1px solid #d2d6de;
        padding: 10px 15px;
    }

    .zahir-modal .modal-title {
        font-size: 16px;
        font-weight: bold;
    }

    .zahir-modal .modal-body {
        padding: 20px;
        background-color: #fcfcfc;
    }

    .zahir-modal .modal-footer {
        background-color: #f0f0f0;
        border-top: 1px solid #d2d6de;
        padding: 10px 15px;
    }

    .filter-tab-nav {
        border-bottom: 1px solid #d2d6de;
        margin-bottom: 15px;
        display: flex;
        gap: 5px;
    }

    .filter-tab-btn {
        padding: 6px 15px;
        font-size: 13px;
        cursor: pointer;
        background-color: #e6e6e6;
        border: 1px solid #d2d6de;
        border-bottom: none;
        border-radius: 4px 4px 0 0;
        margin-bottom: -1px;
    }

    .filter-tab-btn.active {
        background-color: var(--zahir-blue);
        color: white;
        border-color: var(--zahir-blue);
        font-weight: 500;
    }

    .filter-row {
        display: grid;
        grid-template-columns: 100px 1fr auto 1fr;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .filter-row label {
        margin: 0;
        font-size: 13px;
        font-weight: 500;
        text-align: right;
        padding-right: 10px;
    }

    .input-lookup-wrap {
        display: flex;
        align-items: center;
    }

    .input-lookup-wrap input {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        font-size: 13px;
        height: 30px;
    }

    .btn-lookup-trigger {
        border: 1px solid #ced4da;
        border-left: none;
        background-color: #e6e6e6;
        height: 30px;
        padding: 2px 8px;
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .btn-lookup-trigger:hover {
        background-color: #dcdcdc;
    }

    .account-table-select {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #d2d6de;
        background: #fff;
    }

    .account-table-select table {
        width: 100%;
        margin-bottom: 0;
    }

    .account-table-select tr {
        cursor: pointer;
    }

    .account-table-select tr:hover td {
        background-color: #e3f2fd;
    }

    .account-table-select tr.selected td {
        background-color: #bbdefb;
        font-weight: bold;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        #print-area, #print-area * {
            visibility: visible;
        }
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .zahir-action-bar, .zahir-top-bar, .modal {
            display: none !important;
        }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="KarismaLogo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="buku-besar-wrapper">
    <div class="zahir-card" id="print-area">
        <!-- Top Bar -->
        <div class="zahir-top-bar">
            <div>
                <h1>Buku Besar</h1>
                <span style="font-size: 12px; color: #555;" id="filtered-account-label">Silakan pilih filter data terlebih dahulu</span>
            </div>
            <div class="zahir-toolbar">
                <a class="toolbar-link" id="toolbar-filter"><i class="fas fa-filter mr-1"></i> Filter</a>
                <a class="toolbar-link" id="toolbar-update"><i class="fas fa-sync-alt mr-1"></i> Update</a>
            </div>
        </div>

        <!-- Table Container -->
        <div class="zahir-table-container">
            <table class="zahir-table" id="table-ledger-body">
                <thead>
                    <tr>
                        <th style="width: 10%">Tanggal</th>
                        <th style="width: 5%">Tp</th>
                        <th style="width: 15%">No Referensi</th>
                        <th style="width: 30%">Catatan</th>
                        <th style="width: 8%">Departemen</th>
                        <th style="width: 13.5%; text-align: right;">Debit</th>
                        <th style="width: 13.5%; text-align: right;">Kredit</th>
                        <th style="width: 5%; text-align: right;">Job</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="empty-row">
                        <td colspan="8">Tidak ada data. Klik Filter untuk memuat data Buku Besar.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Summary — lebar kolom sama dengan tabel atas agar lurus -->
        <div class="zahir-footer-summary" style="padding: 0;">

            <!-- Single Mode Summary -->
            <div id="summary-single-mode" style="padding-right: 17px;">
                <table style="width:100%; table-layout:fixed; border-collapse:collapse;">
                    <colgroup>
                        <col style="width:10%">
                        <col style="width:5%">
                        <col style="width:15%">
                        <col style="width:30%">
                        <col style="width:8%">
                        <col style="width:13.5%">
                        <col style="width:13.5%">
                        <col style="width:5%">
                    </colgroup>
                    <!-- Baris 1: Saldo Awal | Jumlah Debit | Jumlah Kredit -->
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.2);">
                        <td colspan="3" style="padding: 7px 12px; font-size:12px; color: rgba(255,255,255,0.85);">
                            Saldo Awal:&nbsp;<strong id="sum-saldo-awal" style="color:#fff;">Rp 0.00</strong>
                        </td>
                        <td colspan="2" style="padding: 7px 12px; font-size:12px; color: rgba(255,255,255,0.85); text-align:right;">
                            Jumlah Debit
                        </td>
                        <td style="padding: 7px 12px; font-size:13px; font-weight:bold; color:#fff; text-align:right;">
                            <span id="sum-jumlah-debit">Rp 0.00</span>
                        </td>
                        <td style="padding: 7px 12px; font-size:13px; font-weight:bold; color:#fff; text-align:right;">
                            <span id="sum-jumlah-kredit">Rp 0.00</span>
                        </td>
                        <td style="padding: 7px 12px; font-size:12px; color: rgba(255,255,255,0.7); text-align:right;">
                            Jumlah Kredit
                        </td>
                    </tr>
                    <!-- Baris 2: Saldo Akhir | Mutasi -->
                    <tr>
                        <td colspan="3" style="padding: 6px 12px; font-size:12px; color: rgba(255,255,255,0.85);">
                            Saldo Akhir:&nbsp;<strong id="sum-saldo-akhir" style="color:#fff;">Rp 0.00</strong>
                        </td>
                        <td colspan="2" style="padding: 6px 12px; font-size:12px; color: rgba(255,255,255,0.85); text-align:right;">
                            Mutasi
                        </td>
                        <td colspan="2" style="padding: 6px 12px; font-size:13px; font-weight:bold; color:#ffe082; text-align:right; padding-right: 12px;">
                            <span id="sum-mutasi">Rp 0.00</span>
                        </td>
                        <td style="padding: 6px 12px;"></td>
                    </tr>
                </table>
            </div>

            <!-- Combined Mode Summary -->
            <div id="summary-combined-mode" style="display: none; padding-right: 17px;">
                <table style="width:100%; table-layout:fixed; border-collapse:collapse;">
                    <colgroup>
                        <col style="width:10%">
                        <col style="width:5%">
                        <col style="width:15%">
                        <col style="width:30%">
                        <col style="width:8%">
                        <col style="width:13.5%">
                        <col style="width:13.5%">
                        <col style="width:5%">
                    </colgroup>
                    <!-- Baris Jumlah: lurus di bawah kolom Debit & Kredit -->
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.2);">
                        <td colspan="5" style="padding: 7px 12px; font-size:12px; font-weight:500; color: rgba(255,255,255,0.85); text-align:right;">
                            Jumlah
                        </td>
                        <td style="padding: 7px 12px; font-size:13px; font-weight:bold; color:#fff; text-align:right;">
                            <span id="comb-sum-debit">Rp 0.00</span>
                        </td>
                        <td style="padding: 7px 12px; font-size:13px; font-weight:bold; color:#fff; text-align:right;">
                            <span id="comb-sum-kredit">Rp 0.00</span>
                        </td>
                        <td style="padding: 7px 12px;"></td>
                    </tr>
                    <!-- Baris Mutasi -->
                    <tr>
                        <td colspan="5" style="padding: 6px 12px; font-size:12px; font-weight:500; color: rgba(255,255,255,0.85); text-align:right;">
                            Mutasi
                        </td>
                        <td colspan="2" style="padding: 6px 12px; font-size:13px; font-weight:bold; color:#ffe082; text-align:right; padding-right: 12px;">
                            <span id="comb-sum-mutasi">Rp 0.00</span>
                        </td>
                        <td style="padding: 6px 12px;"></td>
                    </tr>
                </table>
            </div>

        </div>
    </div>

    <!-- Actions Footer -->
    <div class="zahir-action-bar">
        <button type="button" class="btn btn-zahir btn-zahir-primary" id="btn-cetak"><i class="fas fa-print mr-1"></i> Cetak</button>
        <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-tutup">Tutup</button>
    </div>
</div>

<!-- MODAL FILTER DATA -->
<div class="modal fade zahir-modal" id="modalFilter" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formFilterBukuBesar">
                <input type="hidden" name="filter_type" id="filter-type" value="standar">
                <div class="modal-body">
                    <div class="filter-tab-nav">
                        <div class="filter-tab-btn active" id="tab-standar">Standar</div>
                        <div class="filter-tab-btn" id="tab-pencarian">Pencarian</div>
                    </div>

                    <!-- STANDAR FILTER GROUP -->
                    <div id="group-standar">
                        <!-- Date Range Filter -->
                        <div class="filter-row">
                            <label>Tanggal :</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">Dari :</span></div>
                                <input type="date" class="form-control" name="date_from" id="filter-date-from" value="<?= date('Y-m-01') ?>">
                            </div>
                            <i class="fas fa-chevron-right text-muted px-2"></i>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text">Hingga :</span></div>
                                <input type="date" class="form-control" name="date_to" id="filter-date-to" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <!-- Account Range Filter -->
                        <div class="filter-row">
                            <label>Akun :</label>
                            <div class="input-lookup-wrap w-100">
                                <input type="text" class="form-control form-control-sm" name="account_from" id="filter-account-from" value="<?= $min_account ?>" placeholder="Dari Akun">
                                <button type="button" class="btn-lookup-trigger" data-target-input="#filter-account-from"><i class="fas fa-list"></i></button>
                            </div>
                            <i class="fas fa-chevron-right text-muted px-2"></i>
                            <div class="input-lookup-wrap w-100">
                                <input type="text" class="form-control form-control-sm" name="account_to" id="filter-account-to" value="<?= $max_account ?>" placeholder="Hingga Akun">
                                <button type="button" class="btn-lookup-trigger" data-target-input="#filter-account-to"><i class="fas fa-list"></i></button>
                            </div>
                        </div>

                        <!-- Department Range Filter -->
                        <div class="filter-row">
                            <label>Departemen :</label>
                            <select class="form-control form-control-sm" name="dept_from" id="filter-dept-from">
                                <option value="0">0</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['nama_departemen']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-right text-muted px-2"></i>
                            <select class="form-control form-control-sm" name="dept_to" id="filter-dept-to">
                                <option value="999999999">999999999</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['nama_departemen']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Project Range Filter (Placeholder) -->
                        <div class="filter-row">
                            <label>Proyek :</label>
                            <input type="text" class="form-control form-control-sm" value="0" disabled>
                            <i class="fas fa-chevron-right text-muted px-2"></i>
                            <input type="text" class="form-control form-control-sm" value="999999999" disabled>
                        </div>
                    </div>

                    <!-- PENCARIAN FILTER GROUP -->
                    <div id="group-pencarian" style="display: none;">
                        <div class="filter-row" style="grid-template-columns: 140px 1fr;">
                            <label style="text-align: left;">Keterangan Jurnal :</label>
                            <input type="text" class="form-control form-control-sm" name="search_keterangan" id="filter-search-keterangan" placeholder="Masukkan kata kunci keterangan">
                        </div>
                        <div class="filter-row" style="grid-template-columns: 140px 1fr;">
                            <label style="text-align: left;">Nomor Jurnal :</label>
                            <input type="text" class="form-control form-control-sm" name="search_nomor" id="filter-search-nomor" placeholder="Masukkan nomor referensi/jurnal">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-zahir btn-zahir-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-zahir btn-zahir-primary" id="btn-submit-filter">OK</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL LOOKUP AKUN -->
<div class="modal fade zahir-modal" id="modalLookupAkun" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Akun (Perkiraan)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group input-group-sm mb-3">
                    <input type="text" class="form-control" id="search-lookup-akun" placeholder="Cari Kode atau Nama Akun...">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
                
                <div class="account-table-select">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Akun</th>
                            </tr>
                        </thead>
                        <tbody id="lookup-akun-body">
                            <!-- Rows loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-zahir btn-zahir-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-zahir btn-zahir-primary" id="btn-confirm-akun">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let activeTargetInput = null;



    // Tab toggles
    $('.filter-tab-btn').click(function() {
        $('.filter-tab-btn').removeClass('active');
        $(this).addClass('active');
        
        if ($(this).attr('id') === 'tab-standar') {
            $('#filter-type').val('standar');
            $('#group-standar').show();
            $('#group-pencarian').hide();
        } else {
            $('#filter-type').val('pencarian');
            $('#group-standar').hide();
            $('#group-pencarian').show();
        }
    });

    // Filter toolbar click
    $('#toolbar-filter').click(function() {
        $('#modalFilter').modal('show');
    });

    // Update toolbar click
    $('#toolbar-update').click(function() {
        $('#formFilterBukuBesar').submit();
    });

    // Close button
    $('#btn-tutup').click(function() {
        window.location.href = "<?= base_url('dashboard') ?>";
    });

    // Cetak button
    $('#btn-cetak').click(function() {
        window.print();
    });

    // Lookup triggers
    $('.btn-lookup-trigger').click(function() {
        activeTargetInput = $(this).data('target-input');
        $('#search-lookup-akun').val('');
        loadAccountsLookup('');
        $('#modalLookupAkun').modal('show');
    });

    // Search lookup account
    $('#search-lookup-akun').on('keyup input', function() {
        loadAccountsLookup($(this).val());
    });

    // Select row in lookup table
    $(document).on('click', '#lookup-akun-body tr', function() {
        $('#lookup-akun-body tr').removeClass('selected');
        $(this).addClass('selected');
    });

    // Double click row in lookup table to confirm selection immediately
    $(document).on('dblclick', '#lookup-akun-body tr', function() {
        confirmAccountSelection($(this));
    });

    // Confirm button in lookup modal
    $('#btn-confirm-akun').click(function() {
        let selectedRow = $('#lookup-akun-body tr.selected');
        if (selectedRow.length > 0) {
            confirmAccountSelection(selectedRow);
        } else {
            $('#modalLookupAkun').modal('hide');
        }
    });

    function confirmAccountSelection(rowElement) {
        let code = rowElement.data('code');
        if (activeTargetInput && code) {
            $(activeTargetInput).val(code);
        }
        $('#modalLookupAkun').modal('hide');
    }

    function loadAccountsLookup(searchQuery) {
        $.ajax({
            url: "<?= base_url('keuangan/buku_besar/accounts') ?>",
            type: "GET",
            data: { search: searchQuery },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    let html = '';
                    if (response.data.length > 0) {
                        response.data.forEach(function(row) {
                            html += `<tr data-code="${row.kode_akun}">
                                <td style="width: 30%; font-weight: bold; color: var(--zahir-blue);">${row.kode_akun}</td>
                                <td>${row.nama_akun}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="2" class="text-center text-muted">Akun tidak ditemukan.</td></tr>';
                    }
                    $('#lookup-akun-body').html(html);
                }
            }
        });
    }

    // Submit filter form
    $('#formFilterBukuBesar').submit(function(e) {
        e.preventDefault();
        $('#btn-submit-filter').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
        
        let formArray = $(this).serializeArray();
        let formData = $(this).serialize();
        
        // Simpan filter ke session storage agar persisten saat di-refresh
        sessionStorage.setItem('bukuBesarFilter', JSON.stringify(formArray));

        $.ajax({
            url: "<?= base_url('keuangan/buku_besar/data') ?>",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response) {
                $('#btn-submit-filter').prop('disabled', false).html('OK');
                $('#modalFilter').modal('hide');

                if (response.success && response.data.length > 0) {
                    renderLedgerTable(response);
                } else {
                    $('#table-ledger-body tbody').html('<tr class="empty-row"><td colspan="8">Tidak ada data untuk filter yang dipilih.</td></tr>');
                    clearSummaries();
                    $('#filtered-account-label').text('Tidak ada data.');
                }
            },
            error: function() {
                $('#btn-submit-filter').prop('disabled', false).html('OK');
                alert('Gagal memuat data Buku Besar. Pastikan schema database accounting Anda sudah siap.');
            }
        });
    });

    function formatRupiah(value) {
        return 'Rp ' + parseFloat(value).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function clearSummaries() {
        $('#sum-saldo-awal').text(formatRupiah(0));
        $('#sum-saldo-akhir').text(formatRupiah(0));
        $('#sum-jumlah-debit').text(formatRupiah(0));
        $('#sum-jumlah-kredit').text(formatRupiah(0));
        $('#sum-mutasi').text(formatRupiah(0));
    }

    // -------------------------------------------------------
    // Build Drilldown URL based on source_module / source_type
    // -------------------------------------------------------
    function buildDrilldownUrl(m) {
        let base = '<?= base_url() ?>';
        let mod  = (m.source_module || '').toUpperCase();
        let type = (m.source_type  || '').toUpperCase();
        let sid  = m.source_id  || '';
        let sno  = m.source_no  || '';
        let ref  = m.no_referensi || '';

        // --- PENJUALAN (Faktur) ---
        if (mod === 'SALES' && type === 'FAKTUR_PENJUALAN') {
            // source_id is the faktur no (e.g. DINV2507260001)
            // Open faktur detail
            return base + 'sales_order/detail_faktur/' + encodeURIComponent(sid || ref);
        }
        // --- PEMBAYARAN PIUTANG ---
        if (mod === 'KEUANGAN' && (type === 'PEMBAYARAN_FAKTUR' || type === 'PEMBAYARAN')) {
            // source_no is the faktur number (e.g. DINV2507260001)
            let fakturNo = sno || sid;
            return base + 'keuangan/pembayaran/bayar/' + encodeURIComponent(fakturNo);
        }
        // --- PEMBELIAN (LPB) ---
        if (mod === 'LOGISTIK' && (type === 'LPB_FINAL' || type === 'LPB' || type === 'PEMBELIAN')) {
            return base + 'ics/detail_record_lpb?id=' + encodeURIComponent(sid);
        }
        // --- RETUR PENJUALAN ---
        if (mod === 'SALES' && type === 'RETUR_PENJUALAN') {
            return base + 'retur_penjualan/detail/' + encodeURIComponent(sid);
        }
        // --- RETUR PEMBELIAN ---
        if (mod === 'LOGISTIK' && type === 'RETUR_PEMBELIAN') {
            return base + 'ics/retur/detail_retur/' + encodeURIComponent(sid);
        }
        // --- MANUAL / JURNAL UMUM ---
        if (type === 'MANUAL' || mod === 'MANUAL' || type === 'JURNAL_UMUM' || type === 'GJ' || type === 'JU') {
            return base + 'buku_besar/jurnal_umum?id=' + encodeURIComponent(m.id_jurnal || '');
        }
        // Fallback: open accounting journal detail
        if (m.id_jurnal) {
            return base + 'accounting/journal-detail?id=' + encodeURIComponent(m.id_jurnal);
        }
        return null;
    }

    // Build a clickable table row for a mutation
    function buildMutationRow(m, rowStyle) {
        let url = buildDrilldownUrl(m);
        let clickable = url ? 'class="ledger-drilldown-row" data-url="' + url + '" title="Klik 2x untuk membuka transaksi"' : '';
        return `<tr ${clickable} style="${rowStyle || ''}">
            <td>${m.tanggal_formatted}</td>
            <td style="text-align: center; color: var(--zahir-blue); font-weight: bold;">${m.tp}</td>
            <td>${m.no_referensi}</td>
            <td>${m.catatan}${url ? ' <span style="color:#aaa;font-size:10px;" title="Double-click untuk buka transaksi">&#x1F517;</span>' : ''}</td>
            <td>${m.departemen}</td>
            <td class="text-right-no-ellipsis" style="text-align: right;">${m.debit > 0 ? formatRupiah(m.debit) : 'Rp 0.00'}</td>
            <td class="text-right-no-ellipsis" style="text-align: right;">${m.kredit > 0 ? formatRupiah(m.kredit) : 'Rp 0.00'}</td>
            <td></td>
        </tr>`;
    }

    function renderLedgerTable(response) {
        let data = response.data;
        let filters = response.filters;
        let isCombined = response.is_combined;

        let tbodyHtml = '';

        if (isCombined) {
            // Show combined summary, hide single summary
            $('#summary-single-mode').hide();
            $('#summary-combined-mode').show();

            // Populate combined summary values
            $('#comb-sum-debit').text(formatRupiah(response.total_debit));
            $('#comb-sum-kredit').text(formatRupiah(response.total_kredit));
            $('#comb-sum-mutasi').text(formatRupiah(response.mutasi));

            // Render mutations in a flat list
            data.forEach(function(m) {
                tbodyHtml += buildMutationRow(m);
            });

            $('#filtered-account-label').text('Buku Besar (Campuran Akun) | Periode: ' + formatDateIndo(filters.date_from) + ' s.d ' + formatDateIndo(filters.date_to));
        } else {
            // Single account mode
            // Show single summary, hide combined summary
            $('#summary-single-mode').show();
            $('#summary-combined-mode').hide();

            let totalSaldoAwal = 0;
            let totalSaldoAkhir = 0;
            let grandDebit = 0;
            let grandKredit = 0;
            let accountLabels = [];

            data.forEach(function(accData) {
                let acc = accData.account;
                accountLabels.push(acc.nama_akun + ` (${acc.kode_akun})`);
                totalSaldoAwal += accData.saldo_awal;
                totalSaldoAkhir += accData.saldo_akhir;
                grandDebit += accData.total_debit;
                grandKredit += accData.total_kredit;

                // Render Header row for this Account
                tbodyHtml += `<tr class="account-header-row">
                    <td colspan="8">${acc.kode_akun} - ${acc.nama_akun}</td>
                </tr>`;

                // Render Saldo Awal row for this Account
                tbodyHtml += `<tr>
                    <td>${formatDateIndo(filters.date_from)}</td>
                    <td></td>
                    <td></td>
                    <td style="font-weight: 500; font-style: italic;">[ Saldo Awal ]</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>`;

                // Render Mutations
                if (accData.mutations.length > 0) {
                    accData.mutations.forEach(function(m) {
                        tbodyHtml += buildMutationRow(m);
                    });
                } else {
                    tbodyHtml += `<tr>
                        <td colspan="8" style="text-align: center; color: #888; font-style: italic; padding: 12px;">Tidak ada transaksi selama periode ini.</td>
                    </tr>`;
                }
            });

            // Update Labels & Summaries
            if (filters.filter_type === 'pencarian') {
                let searchParts = [];
                if (filters.search_keterangan) searchParts.push('Keterangan: "' + filters.search_keterangan + '"');
                if (filters.search_nomor) searchParts.push('Nomor Jurnal: "' + filters.search_nomor + '"');
                $('#filtered-account-label').text('Pencarian Jurnal | ' + (searchParts.length > 0 ? searchParts.join(' & ') : 'Semua data'));
            } else {
                let labelText = accountLabels.join(', ');
                if (labelText.length > 80) {
                    labelText = labelText.substring(0, 80) + '...';
                }
                $('#filtered-account-label').text(labelText + ' | Periode: ' + formatDateIndo(filters.date_from) + ' s.d ' + formatDateIndo(filters.date_to));
            }

            $('#sum-saldo-awal').text(formatRupiah(totalSaldoAwal));
            $('#sum-saldo-akhir').text(formatRupiah(totalSaldoAkhir));
            $('#sum-jumlah-debit').text(formatRupiah(grandDebit));
            $('#sum-jumlah-kredit').text(formatRupiah(grandKredit));
            
            let netMutasi = totalSaldoAkhir - totalSaldoAwal;
            $('#sum-mutasi').text(formatRupiah(netMutasi));
        }

        $('#table-ledger-body tbody').html(tbodyHtml);

        // --- Attach double-click drilldown ---
        $('#table-ledger-body').off('dblclick', '.ledger-drilldown-row');
        $('#table-ledger-body').on('dblclick', '.ledger-drilldown-row', function() {
            let url = $(this).data('url');
            if (url) {
                window.location.href = url;
            }
        });
    }

    function formatDateIndo(dateStr) {
        if (!dateStr) return '';
        let parts = dateStr.split('-');
        if (parts.length === 3) {
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }
        return dateStr;
    }
    
    // Cek filter yang disimpan (setelah event handler .submit terdaftar)
    let savedFilter = sessionStorage.getItem('bukuBesarFilter');
    if (savedFilter) {
        try {
            let filterArray = JSON.parse(savedFilter);
            filterArray.forEach(function(item) {
                let el = $('#formFilterBukuBesar [name="' + item.name + '"]');
                if (el.length) {
                    el.val(item.value);
                }
            });

            // Update UI Tab berdasarkan filter_type
            let filterType = $('#filter-type').val();
            $('.filter-tab-btn').removeClass('active');
            if (filterType === 'standar') {
                $('#tab-standar').addClass('active');
                $('#group-standar').show();
                $('#group-pencarian').hide();
            } else {
                $('#tab-pencarian').addClass('active');
                $('#group-standar').hide();
                $('#group-pencarian').show();
            }
            
            // Trigger submit form melalui jQuery
            $('#formFilterBukuBesar').trigger('submit');
        } catch (e) {
            $('#modalFilter').modal('show');
        }
    } else {
        // Show filter modal on page load jika tidak ada filter yg tersimpan
        $('#modalFilter').modal('show');
    }
});
</script>
    </div> <!-- end content-wrapper -->
    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div> <!-- end wrapper -->
</body>
