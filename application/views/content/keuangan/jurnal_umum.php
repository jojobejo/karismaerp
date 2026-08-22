<!-- application/views/content/keuangan/jurnal_umum.php -->
<style>
    :root {
        --zahir-blue: #127fad;
        --zahir-dark-blue: #0f6c94;
        --zahir-light-bg: #f5f8fa;
        --zahir-card-border: #e1e8ed;
        --zahir-text: #2c3e50;
    }

    body.hold-transition {
        background-color: var(--zahir-light-bg);
    }

    .buku-besar-container {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--zahir-text);
        padding: 20px;
    }

    .buku-besar-header {
        background: linear-gradient(135deg, var(--zahir-blue) 0%, #3197c5 100%);
        color: #fff;
        padding: 18px 24px;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 4px 15px rgba(18, 127, 173, 0.15);
    }

    .buku-besar-header h2 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .zahir-card {
        background: #fff;
        border: 1px solid var(--zahir-card-border);
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .zahir-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
    }

    .zahir-table thead th {
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

    .zahir-table tbody td {
        padding: 12px 15px;
        font-size: 13px;
        border-bottom: 1px solid #eef2f5;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .zahir-table td.text-right-no-ellipsis {
        overflow: visible;
        text-overflow: clip;
        white-space: nowrap;
    }

    .zahir-table tbody tr {
        cursor: pointer;
    }

    .zahir-table tbody tr:hover td {
        background-color: #e3f2fd !important;
    }

    .zahir-table tbody tr.selected td {
        background-color: #bbdefb !important;
        font-weight: bold;
    }

    .btn-zahir {
        font-weight: 600;
        font-size: 14px;
        padding: 8px 20px;
        border-radius: 4px;
        transition: all 0.2s ease;
        text-transform: capitalize;
    }

    .btn-zahir-primary {
        background-color: var(--zahir-blue);
        border-color: var(--zahir-blue);
        color: #fff;
    }

    .btn-zahir-primary:hover {
        background-color: var(--zahir-dark-blue);
        border-color: var(--zahir-dark-blue);
        color: #fff;
        transform: translateY(-1px);
    }

    .btn-zahir-secondary {
        background-color: #f1f3f5;
        border-color: #dee2e6;
        color: #495057;
    }

    .btn-zahir-secondary:hover {
        background-color: #e2e6ea;
        border-color: #dae0e5;
        color: #212529;
    }

    .btn-zahir-danger {
        background-color: #e63946;
        border-color: #e63946;
        color: #fff;
    }

    .btn-zahir-danger:hover {
        background-color: #c1121f;
        border-color: #c1121f;
        color: #fff;
        transform: translateY(-1px);
    }

    .footer-actions {
        background-color: #f8fafc;
        border-top: 1px solid var(--zahir-card-border);
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-group-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        padding: 24px;
        background-color: #f8fafc;
        border-bottom: 1px solid var(--zahir-card-border);
    }

    .form-zahir-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
        padding: 8px 12px;
        font-size: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-zahir-control:focus {
        border-color: var(--zahir-blue);
        box-shadow: 0 0 0 3px rgba(18, 127, 173, 0.15);
        outline: none;
    }

    .badge-status {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 99px;
        text-transform: uppercase;
    }

    .badge-status-posted {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-status-draft {
        background-color: #fff3cd;
        color: #856404;
    }

    /* Modal Styling */
    .zahir-modal .modal-content {
        border-radius: 8px;
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }

    .zahir-modal .modal-header {
        background-color: var(--zahir-blue);
        color: #fff;
        border-radius: 8px 8px 0 0;
    }

    /* Form Table specific style */
    .form-table input[type="text"], .form-table input[type="number"], .form-table select {
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        padding: 6px;
    }

    .form-table input[type="text"]:focus, .form-table input[type="number"]:focus, .form-table select:focus {
        border-color: var(--zahir-blue);
        background: #fff;
        outline: none;
    }

    .total-summary-box {
        text-align: right;
        padding: 20px 24px;
        font-size: 15px;
        font-weight: 600;
    }

    .total-summary-box .row-summary {
        margin-bottom: 6px;
    }

    .total-summary-box .balans-text {
        font-size: 18px;
        font-weight: 700;
        color: var(--zahir-blue);
    }

    /* Context Menu Styles */
    .context-menu {
        position: absolute;
        background: #fff;
        border: 1px solid #ccc;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border-radius: 4px;
        z-index: 1000;
        min-width: 150px;
    }
    .context-menu ul {
        list-style: none;
        padding: 5px 0;
        margin: 0;
    }
    .context-menu li {
        padding: 8px 15px;
        cursor: pointer;
        font-size: 13px;
        color: #333;
    }
    .context-menu li:hover {
        background: var(--zahir-light-bg);
        color: var(--zahir-blue);
    }
</style>

<?php 
$has_id = !empty($this->input->get('id'));
?>
<?php if ($has_id): ?>
<style id="hold-preloader-style">
    .preloader {
        height: 100vh !important;
        opacity: 1 !important;
        display: flex !important;
    }
</style>
<?php endif; ?>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="KarismaLogo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="buku-besar-container">
            <!-- SECTION 1: DAFTAR TRANSAKSI JURNAL -->
            <div id="view-list" class="zahir-card" <?= $has_id ? 'style="display: none;"' : '' ?>>
                <div class="buku-besar-header d-flex justify-content-between align-items-center">
                    <h2>Daftar Transaksi Jurnal</h2>
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <input type="text" id="search-input" class="form-control form-zahir-control" placeholder="Search..." style="width: 220px; height: 34px; border-radius: 4px; font-size: 13px;">
                        <button type="button" class="btn btn-sm btn-light font-weight-bold" id="btn-refresh" title="Update data" style="font-size: 13px; height: 34px; padding: 0 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; color: var(--zahir-text);">
                            <i class="fas fa-sync-alt"></i> Update
                        </button>
                        <button type="button" class="btn btn-sm btn-light font-weight-bold" id="btn-filter" title="Filter data" style="font-size: 13px; height: 34px; padding: 0 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; color: var(--zahir-text);">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 680px; min-height: 550px;">
                    <table class="table zahir-table" id="table-jurnal">
                        <thead>
                            <tr>
                                <th style="width: 15%">Tanggal</th>
                                <th style="width: 25%">Nomor Referensi</th>
                                <th style="width: 40%">Keterangan</th>
                                <th style="width: 20%" class="text-right">Nilai</th>
                                <th style="width: 5px"></th>
                            </tr>
                        </thead>
                        <tbody id="list-jurnal-body">
                            <!-- Rows loaded via Ajax -->
                        </tbody>
                    </table>
                </div>

                <div class="footer-actions">
                    <div style="gap: 10px; display: flex;">
                        <button type="button" class="btn btn-zahir btn-zahir-primary" id="btn-baru-jurnal">
                            <i class="fas fa-plus mr-1"></i> Baru
                        </button>
                        <button type="button" class="btn btn-zahir btn-zahir-danger" id="btn-hapus-jurnal" disabled>
                            <i class="fas fa-trash mr-1"></i> Hapus
                        </button>
                    </div>
                    <div style="gap: 10px; display: flex;">
                        <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-perincian" disabled>
                            <i class="fas fa-info-circle mr-1"></i> Perincian
                        </button>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-zahir btn-zahir-secondary">
                            <i class="fas fa-times mr-1"></i> Tutup
                        </a>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: INPUT TRANSAKSI JURNAL UMUM -->
            <div id="view-form" class="zahir-card" <?= $has_id ? 'style="display: block;"' : 'style="display: none;"' ?>>
                <div class="buku-besar-header">
                    <h2>Jurnal Umum</h2>
                </div>

                <form id="form-jurnal-umum">
                    <div class="form-group-grid">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Referensi :</label>
                            <input type="text" name="referensi" id="form-ref" class="form-control form-zahir-control" value="<?= html_escape($next_ref) ?>" required>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Tanggal :</label>
                            <input type="date" name="tanggal" id="form-tanggal" class="form-control form-zahir-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group mb-0" style="grid-column: span 2;">
                            <label class="small font-weight-bold">Keterangan :</label>
                            <input type="text" name="keterangan" id="form-keterangan" class="form-control form-zahir-control" placeholder="Jurnal Umum..." value="Jurnal Umum" required>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-bordered form-table mb-0" id="table-form-lines">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 25%">Kode Akun</th>
                                    <th style="width: 35%">Nama Akun</th>
                                    <th style="width: 20%" class="text-right">Debit</th>
                                    <th style="width: 20%" class="text-right">Kredit</th>
                                    <th style="width: 45px" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="form-lines-body">
                                <!-- Dynamically added rows -->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-start">
                        <div class="p-3">
                            <button type="button" class="btn btn-xs btn-outline-primary" id="btn-add-line">
                                <i class="fas fa-plus mr-1"></i> Tambah Baris Akun
                            </button>
                        </div>
                        <div class="total-summary-box">
                            <div class="row-summary">Total Debit: <span id="summary-total-debit">Rp 0,00</span></div>
                            <div class="row-summary">Total Kredit: <span id="summary-total-kredit">Rp 0,00</span></div>
                            <div class="balans-text">Balans: <span id="summary-balans">Rp 0,00</span></div>
                        </div>
                    </div>

                    <div class="footer-actions">
                        <div style="gap: 10px; display: flex;">
                            <button type="button" class="btn btn-zahir btn-zahir-danger" id="btn-clear-form">
                                <i class="fas fa-undo mr-1"></i> Rekam Ulang
                            </button>
                        </div>
                        <div style="gap: 10px; display: flex;">
                            <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-batal-form">
                                Batal
                            </button>
                            <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-save-draft">
                                Rekam Draft
                            </button>
                            <button type="submit" class="btn btn-zahir btn-zahir-primary" id="btn-save-posted">
                                Rekam
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Context Menu -->
<div id="context-menu-jurnal" class="context-menu" style="display:none;">
    <ul>
        <li id="menu-detail-jurnal-list"><i class="fas fa-info-circle mr-2"></i> Detail Jurnal</li>
    </ul>
</div>

<!-- MODAL: DAFTAR AKUN (PERKIRAAN) -->
<div class="modal fade zahir-modal" id="modal-pilih-akun" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between" style="padding: 12px 20px;">
                <h5 class="modal-title" style="font-size: 18px;"><i class="fas fa-list mr-2"></i> Daftar Akun (Perkiraan)</h5>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <input type="text" id="account-search-modal" class="form-control form-zahir-control" placeholder="Cari Kode atau Nama Akun..." style="width: 250px; background: #fff; border-radius: 4px; height: 35px;">
                </div>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 400px; min-height: 250px;">
                    <table class="table zahir-table" id="table-pilih-akun">
                        <thead>
                            <tr>
                                <th style="width: 30%">Kode</th>
                                <th style="width: 70%">Nama Akun</th>
                            </tr>
                        </thead>
                        <tbody id="pilih-akun-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between" style="padding: 12px 20px;">
                <div>
                    <button type="button" class="btn btn-zahir btn-zahir-danger" style="padding: 5px 15px;">Hapus</button>
                </div>
                <div style="gap: 8px; display: flex;">
                    <button type="button" class="btn btn-zahir btn-zahir-secondary" style="padding: 5px 15px;">Baru</button>
                    <button type="button" class="btn btn-zahir btn-zahir-secondary" style="padding: 5px 15px;">Edit</button>
                    <button type="button" class="btn btn-zahir btn-zahir-secondary" style="padding: 5px 15px;">Update</button>
                    <button type="button" class="btn btn-zahir btn-zahir-secondary" style="padding: 5px 15px;" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-zahir btn-zahir-primary" id="btn-modal-ok-akun" style="padding: 5px 15px;">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: PERINCIAN JURNAL -->
<div class="modal fade zahir-modal" id="modal-perincian" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle mr-2"></i> Perincian Transaksi Jurnal</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light d-flex justify-content-between border-bottom">
                    <div>
                        <strong>No. Ref:</strong> <span id="detail-ref"></span><br>
                        <strong>Tanggal:</strong> <span id="detail-tanggal"></span>
                    </div>
                    <div class="text-right">
                        <strong>Status:</strong> <span id="detail-status"></span><br>
                        <strong>Keterangan:</strong> <span id="detail-keterangan"></span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="detail-lines-body">
                            <!-- Loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: FILTER DATA (Zahir Style) -->
<div class="modal fade zahir-modal" id="modalFilter" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;" role="document">
        <div class="modal-content" style="border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 15px 35px rgba(0,0,0,0.15);">
            <div class="modal-header d-flex align-items-center justify-content-between" style="background: #fff; border-bottom: none; padding: 16px 20px 0 20px;">
                <h5 class="modal-title font-weight-bold" style="color: #1e293b; font-size: 17px;">Filter Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                    <span aria-hidden="true" style="font-size: 22px; color: #64748b;">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 12px 20px 16px 20px;">
                <!-- Tab Standar Zahir -->
                <div class="mb-4">
                    <div style="display: inline-block; background-color: var(--zahir-blue); color: #fff; font-size: 13px; font-weight: 600; padding: 6px 18px; border-radius: 4px;">
                        Standar
                    </div>
                </div>

                <form id="form-filter-data">
                    <!-- Tanggal -->
                    <div class="form-group row align-items-center mb-3">
                        <label class="col-sm-3 col-form-label text-sm-right font-weight-500" style="font-size: 13px; color: #334155;">Tanggal :</label>
                        <div class="col-sm-9">
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <div class="d-flex align-items-center" style="gap: 5px; flex: 1;">
                                    <span style="font-size: 12px; color: #64748b; white-space: nowrap;">Dari :</span>
                                    <input type="date" id="filter-date-from" class="form-control form-zahir-control" style="height: 32px; font-size: 12px; padding: 3px 6px;">
                                </div>
                                <div>
                                    <button type="button" id="btn-copy-date" class="btn btn-sm btn-light" title="Terapkan tanggal yang sama pada kolom Hingga" style="height: 28px; width: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 4px; color: #475569; cursor: pointer; background: #f8fafc; font-weight: bold; transition: all 0.15s;">
                                        <i class="fas fa-chevron-right" style="font-size: 11px;"></i>
                                    </button>
                                </div>
                                <div class="d-flex align-items-center" style="gap: 5px; flex: 1;">
                                    <span style="font-size: 12px; color: #64748b; white-space: nowrap;">Hingga :</span>
                                    <input type="date" id="filter-date-to" class="form-control form-zahir-control" style="height: 32px; font-size: 12px; padding: 3px 6px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-group row align-items-center mb-4">
                        <label class="col-sm-3 col-form-label text-sm-right font-weight-500" style="font-size: 13px; color: #334155;">Status :</label>
                        <div class="col-sm-9">
                            <select id="filter-status" class="form-control form-zahir-control" style="height: 32px; font-size: 13px; padding: 3px 8px; width: 140px;">
                                <option value="Semua">Semua</option>
                                <option value="Posted">Posted</option>
                                <option value="Unposted">Unposted</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end" style="gap: 10px; border-top: 1px solid #eef2f5; padding-top: 14px;">
                        <button type="button" class="btn btn-zahir btn-zahir-primary" data-dismiss="modal" style="min-width: 80px; font-size: 13px; padding: 5px 14px;">
                            <u>B</u>atal
                        </button>
                        <button type="button" class="btn btn-zahir btn-zahir-primary" id="btn-apply-filter" style="min-width: 80px; font-size: 13px; padding: 5px 14px;">
                            <u>O</u>K
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        let selectedJournalId = null;
        let accountsData = <?= json_encode($accounts) ?>;
        let activeFormRowId = null;
        let selectedModalAccount = null;
        
        let currentFilter = {
            date_from: '',
            date_to: '',
            status: 'Semua'
        };

        // Load Jurnal List
        function loadJurnalList() {
            let searchVal = $('#search-input').val();
            $.ajax({
                url: '<?= base_url("buku_besar/jurnal_umum_list") ?>',
                type: 'GET',
                data: { 
                    search: searchVal,
                    date_from: currentFilter.date_from,
                    date_to: currentFilter.date_to,
                    status: currentFilter.status
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        let html = '';
                        if (res.data.length === 0) {
                            html = '<tr><td colspan="5" class="text-center text-muted">Belum ada transaksi jurnal.</td></tr>';
                        } else {
                            res.data.forEach(function(row) {
                                let check = row.status === 'POSTED' ? '<i class="fas fa-check text-success" title="Posted"></i>' : '<span class="badge badge-warning">Draft</span>';
                                html += `<tr data-id="${row.id_jurnal}">
                                    <td>${row.tanggal_formatted}</td>
                                    <td>${row.nomor_jurnal}</td>
                                    <td>${row.keterangan}</td>
                                    <td class="text-right-no-ellipsis text-right font-weight-bold">${row.nilai_formatted}</td>
                                    <td class="text-center">${check}</td>
                                </tr>`;
                            });
                        }
                        $('#list-jurnal-body').html(html);
                        resetSelection();
                    }
                }
            });
        }

        $('#search-input').on('keyup input', function() {
            loadJurnalList();
        });

        // Copy tanggal Dari ke Hingga saat tombol > diklik
        $('#btn-copy-date').click(function() {
            let dateFrom = $('#filter-date-from').val();
            if (dateFrom) {
                $('#filter-date-to').val(dateFrom);
            }
        });

        $('#btn-filter').click(function() {
            $('#filter-date-from').val(currentFilter.date_from);
            $('#filter-date-to').val(currentFilter.date_to);
            $('#filter-status').val(currentFilter.status || 'Semua');
            $('#modalFilter').modal('show');
        });

        $('#btn-apply-filter').click(function() {
            currentFilter.date_from = $('#filter-date-from').val();
            currentFilter.date_to = $('#filter-date-to').val();
            currentFilter.status = $('#filter-status').val();
            $('#modalFilter').modal('hide');
            loadJurnalList();
        });

        loadJurnalList();

        // Hide context menu on click anywhere
        $(document).click(function() {
            $('#context-menu-jurnal').hide();
        });

        // Right-click on row -> show context menu
        let contextMenuJurnalId = null;
        $(document).on('contextmenu', '#list-jurnal-body tr', function(e) {
            let id = $(this).data('id');
            if (!id) return; // baris kosong / "Belum ada transaksi"

            e.preventDefault();
            contextMenuJurnalId = id;

            $('#context-menu-jurnal').css({
                display: "block",
                left: e.pageX,
                top: e.pageY
            });
        });

        // Klik "Detail Jurnal" di context menu -> tampilkan modal perincian
        $('#menu-detail-jurnal-list').click(function() {
            if (!contextMenuJurnalId) return;
            showDetailJurnalModal(contextMenuJurnalId);
        });

        function showDetailJurnalModal(idJurnal) {
            $.ajax({
                url: '<?= base_url("buku_besar/jurnal_umum_detail") ?>',
                type: 'POST',
                data: { id_jurnal: idJurnal },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        let journal = res.data.journal;
                        let details = res.data.details;

                        $('#detail-ref').text(journal.nomor_jurnal || '-');

                        let dateObj = new Date(journal.tanggal_transaksi);
                        let d = String(dateObj.getDate()).padStart(2, '0');
                        let m = String(dateObj.getMonth() + 1).padStart(2, '0');
                        let y = dateObj.getFullYear();
                        $('#detail-tanggal').text(`${d}/${m}/${y}`);

                        $('#detail-keterangan').text(journal.keterangan || '-');

                        let statusBadge = journal.status === 'POSTED'
                            ? '<span class="badge-status badge-status-posted">POSTED</span>'
                            : '<span class="badge-status badge-status-draft">DRAFT</span>';
                        $('#detail-status').html(statusBadge);

                        let html = '';
                        details.forEach(function(row) {
                            let debitVal = parseFloat(row.debit) > 0
                                ? new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(row.debit)
                                : '';
                            let kreditVal = parseFloat(row.kredit) > 0
                                ? new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(row.kredit)
                                : '';

                            html += `<tr>
                                <td>${row.kode_akun || '-'}</td>
                                <td>${row.nama_akun || '-'}</td>
                                <td class="text-right">${debitVal}</td>
                                <td class="text-right">${kreditVal}</td>
                            </tr>`;
                        });

                        $('#detail-lines-body').html(html);
                        $('#modal-perincian').modal('show');
                    } else {
                        Swal.fire('Gagal!', res.message || 'Gagal memuat detail jurnal.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                }
            });
        }

        // Check if there is an 'id' query parameter in the URL (e.g. from Buku Besar drilldown)
        const urlParams = new URLSearchParams(window.location.search);
        const journalId = urlParams.get('id');
        if (journalId) {
            // Give a short delay to ensure list is initialized, then load the form
            setTimeout(function() {
                openJournalInForm(journalId);
            }, 300);
        }

        $('#btn-refresh').click(function() {
            loadJurnalList();
        });

        // Open journal in form view
        function openJournalInForm(id) {
            // Animasi preloader ditahan oleh CSS #hold-preloader-style
            // Jika memanggil ini secara manual (bukan drilldown), panggil preloader baru
            if (!$('#hold-preloader-style').length) {
                $('.preloader').css('height', '100vh');
            }
            
            $.ajax({
                url: '<?= base_url("buku_besar/jurnal_umum_detail") ?>',
                type: 'POST',
                data: { 
                    id_jurnal: id
                },
                dataType: 'json',
                success: function(res) {
                    if ($('#hold-preloader-style').length) {
                        setTimeout(function() {
                            $('#hold-preloader-style').remove();
                        }, 100);
                    } else {
                        setTimeout(function() {
                            $('.preloader').css('height', 0);
                        }, 200);
                    }
                    
                    if (res.success && res.data) {
                        let journal = res.data.journal;
                        let details = res.data.details;
                        
                        let populateData = function() {
                            clearForm();
                            $('#form-ref').val(journal.nomor_jurnal);
                            $('#form-tanggal').val(journal.tanggal_transaksi);
                            $('#form-keterangan').val(journal.keterangan);
                            
                            details.forEach(function(line) {
                                let rowId = rowCounter++;
                                let debitVal = (parseFloat(line.debit) > 0) ? parseFloat(line.debit).toFixed(2) : '';
                                let kreditVal = (parseFloat(line.kredit) > 0) ? parseFloat(line.kredit).toFixed(2) : '';
                                let rowHtml = `<tr id="form-row-${rowId}" class="form-row-line">
                                    <td>
                                        <input type="text" class="form-control input-pilih-akun" readonly placeholder="- Pilih Akun -" style="cursor: pointer; background: #fff;" value="${line.kode_akun || ''}">
                                        <input type="hidden" name="lines[${rowId}][id_akun]" class="input-id-akun" required value="${line.id_akun}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control text-nama-akun" readonly placeholder="-" style="cursor: pointer; background: #fff;" value="${line.nama_akun || ''}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="lines[${rowId}][debit]" class="form-control text-right input-debit" value="${debitVal}" placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="lines[${rowId}][kredit]" class="form-control text-right input-kredit" value="${kreditVal}" placeholder="0.00">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-link text-danger btn-remove-line" data-id="${rowId}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>`;
                                $('#form-lines-body').append(rowHtml);
                            });
                            
                            calculateFormTotals();
                        };

                        if ($('#view-form').is(':visible')) {
                            populateData();
                        } else {
                            $('#view-list').fadeOut(200, function() {
                                $('#view-form').fadeIn(200);
                                populateData();
                            });
                        }
                    } else {
                        Swal.fire('Gagal!', 'Gagal memuat detail jurnal.', 'error');
                    }
                },
                error: function() {
                    if ($('#hold-preloader-style').length) {
                        $('#hold-preloader-style').remove();
                    } else {
                        $('.preloader').css('height', 0);
                    }
                    Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                }
            });
        }

        // Row selection logic (1 click)
        $(document).on('click', '#list-jurnal-body tr', function() {
            let id = $(this).data('id');
            if(!id) return;
            
            $('#list-jurnal-body tr').removeClass('selected');
            $(this).addClass('selected');
            selectedJournalId = id;
            $('#btn-hapus-jurnal, #btn-perincian').prop('disabled', false);
        });

        // Open logic (2 clicks / double click)
        $(document).on('dblclick', '#list-jurnal-body tr', function() {
            let id = $(this).data('id');
            if(!id) return;
            openJournalInForm(id);
        });

        function resetSelection() {
            selectedJournalId = null;
            $('#btn-hapus-jurnal, #btn-perincian').prop('disabled', true);
        }

        // Show perincian in form view
        $('#btn-perincian').click(function() {
            if(!selectedJournalId) return;
            openJournalInForm(selectedJournalId);
        });

        // Delete journal
        $('#btn-hapus-jurnal').click(function() {
            if(!selectedJournalId) return;
            
            Swal.fire({
                title: 'Hapus Transaksi?',
                text: "Apakah Anda yakin ingin menghapus transaksi jurnal ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e63946',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url("buku_besar/jurnal_umum_delete") ?>',
                        type: 'POST',
                        data: { id_jurnal: selectedJournalId },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Terhapus!', res.message, 'success');
                                loadJurnalList();
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        // Switch to form view
        $('#btn-baru-jurnal').click(function() {
            $('#view-list').fadeOut(200, function() {
                $('#view-form').fadeIn(200);
                clearForm();
                addNewLine();
                addNewLine();
            });
        });

        // Switch back to list or return to Buku Besar
        $('#btn-batal-form').click(function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('id')) {
                window.location.href = '<?= base_url("keuangan/buku_besar") ?>';
            } else {
                $('#view-form').fadeOut(200, function() {
                    $('#view-list').fadeIn(200);
                    loadJurnalList();
                });
            }
        });

        // Add row to form
        let rowCounter = 0;
        function addNewLine() {
            let rowId = rowCounter++;

            let rowHtml = `<tr id="form-row-${rowId}" class="form-row-line">
                <td>
                    <input type="text" class="form-control input-pilih-akun" readonly placeholder="- Pilih Akun -" style="cursor: pointer; background: #fff;">
                    <input type="hidden" name="lines[${rowId}][id_akun]" class="input-id-akun" required>
                </td>
                <td>
                    <input type="text" class="form-control text-nama-akun" readonly placeholder="-" style="cursor: pointer; background: #fff;">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="lines[${rowId}][debit]" class="form-control text-right input-debit" placeholder="0.00">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="lines[${rowId}][kredit]" class="form-control text-right input-kredit" placeholder="0.00">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger btn-remove-line" data-id="${rowId}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>`;
            
            $('#form-lines-body').append(rowHtml);
        }

        $('#btn-add-line').click(function() {
            addNewLine();
        });

        $(document).on('click', '.btn-remove-line', function() {
            let id = $(this).data('id');
            $(`#form-row-${id}`).remove();
            calculateFormTotals();
        });

        // Open Choose Account Modal
        $(document).on('click focus', '.input-pilih-akun, .text-nama-akun', function() {
            activeFormRowId = $(this).closest('tr').attr('id');
            $('#account-search-modal').val('');
            renderModalAccounts('');
            $('#modal-pilih-akun').modal('show');
        });

        // Render accounts list in modal
        function renderModalAccounts(search) {
            search = (search || '').toLowerCase();
            let html = '';
            let filtered = accountsData.filter(function(acc) {
                return acc.kode_akun.toLowerCase().includes(search) || acc.nama_akun.toLowerCase().includes(search);
            });
            
            if (filtered.length === 0) {
                html = '<tr><td colspan="2" class="text-center text-muted py-3">Tidak ada akun perkiraan.</td></tr>';
            } else {
                filtered.forEach(function(acc) {
                    html += `<tr data-id="${acc.id_akun}" data-kode="${acc.kode_akun}" data-nama="${acc.nama_akun}">
                        <td>${acc.kode_akun}</td>
                        <td>${acc.nama_akun}</td>
                    </tr>`;
                });
            }
            $('#pilih-akun-body').html(html);
            selectedModalAccount = null;
        }

        // Search inside modal
        $('#account-search-modal').on('input', function() {
            renderModalAccounts($(this).val());
        });

        // Select account row in modal (single click to apply)
        $(document).on('click', '#pilih-akun-body tr', function() {
            let id = $(this).data('id');
            if(!id) return;
            selectedModalAccount = {
                id: id,
                kode: $(this).data('kode'),
                nama: $(this).data('nama')
            };
            applySelectedAccount();
        });

        // Apply selected account to active row
        function applySelectedAccount() {
            if (selectedModalAccount && activeFormRowId) {
                let $row = $(`#${activeFormRowId}`);
                $row.find('.input-pilih-akun').val(selectedModalAccount.kode);
                $row.find('.input-id-akun').val(selectedModalAccount.id);
                $row.find('.text-nama-akun').val(selectedModalAccount.nama);
                $('#modal-pilih-akun').modal('hide');
                calculateFormTotals();
            }
        }

        $('#btn-modal-ok-akun').click(function() {
            applySelectedAccount();
        });

        // Recalculate totals on input change
        $(document).on('input change', '.input-debit, .input-kredit', function() {
            calculateFormTotals();
        });

        function calculateFormTotals() {
            let totalDebit = 0;
            let totalKredit = 0;

            $('.input-debit').each(function() {
                totalDebit += parseFloat($(this).val()) || 0;
            });

            $('.input-kredit').each(function() {
                totalKredit += parseFloat($(this).val()) || 0;
            });

            let balans = totalDebit - totalKredit;

            $('#summary-total-debit').text('Rp ' + totalDebit.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#summary-total-kredit').text('Rp ' + totalKredit.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#summary-balans').text('Rp ' + balans.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            if (Math.abs(balans) < 0.01) {
                $('#summary-balans').css('color', '#28a745');
            } else {
                $('#summary-balans').css('color', '#dc3545');
            }
        }

        function fetchNextRef() {
            $.ajax({
                url: '<?= base_url("buku_besar/jurnal_umum_next_ref") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success && res.next_ref) {
                        $('#form-ref').val(res.next_ref);
                    }
                }
            });
        }

        function clearForm() {
            $('#form-lines-body').empty();
            $('#form-keterangan').val('Jurnal Umum');
            $('#form-tanggal').val('<?= date("Y-m-d") ?>');
            rowCounter = 0;
            calculateFormTotals();
            fetchNextRef();
        }

        $('#btn-clear-form').click(function() {
            clearForm();
            addNewLine();
            addNewLine();
        });

        // Submit form (draft or posted)
        function submitForm(postNow) {
            let formData = $('#form-jurnal-umum').serializeArray();
            formData.push({ name: 'post_now', value: postNow ? 1 : 0 });

            Swal.fire({
                title: 'Sedang memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            $.ajax({
                url: '<?= base_url("buku_besar/jurnal_umum_store") ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(res) {
                    Swal.close();
                    if (res.success) {
                        Swal.fire('Berhasil!', res.message, 'success').then(function() {
                            $('#btn-batal-form').click();
                        });
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                }
            });
        }

        $('#btn-save-draft').click(function(e) {
            e.preventDefault();
            submitForm(false);
        });

        $('#form-jurnal-umum').submit(function(e) {
            e.preventDefault();
            submitForm(true);
        });
    });
</script>
</body>
