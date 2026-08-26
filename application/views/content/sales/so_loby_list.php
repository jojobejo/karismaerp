<!-- views/content/sales/so_loby_list.php -->
<!-- Halaman List Sales Order Loby (Zahir ERP Style dengan Context Menu & Detail Jurnal) -->
<style>
    :root {
        --zahir-blue: #127fad;
        --zahir-dark-blue: #0f6c94;
        --zahir-light-bg: #f0f4f7;
        --zahir-card-border: #d1dbe3;
        --zahir-text: #2c3e50;
    }

    body.hold-transition {
        background-color: var(--zahir-light-bg);
    }

    .pb-container {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--zahir-text);
        padding: 20px;
    }

    .zahir-card {
        background: #fff;
        border: 1px solid var(--zahir-card-border);
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 120px);
    }

    /* Header Zahir */
    .pb-header-title {
        background: linear-gradient(135deg, var(--zahir-blue) 0%, #3197c5 100%);
        color: #fff;
        padding: 16px 24px;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 4px 15px rgba(18, 127, 173, 0.15);
    }

    .pb-header-title h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        letter-spacing: 0.5px;
    }

    /* Toolbar Atas */
    .pb-top-toolbar {
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f5;
        flex-wrap: wrap;
    }

    .pb-top-toolbar input[type="text"],
    .pb-top-toolbar input[type="date"],
    .pb-top-toolbar select {
        font-size: 13px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 6px 10px;
        height: 34px;
        outline: none;
        background: #fff;
    }

    .pb-top-toolbar input[type="text"]:focus,
    .pb-top-toolbar input[type="date"]:focus,
    .pb-top-toolbar select:focus {
        border-color: var(--zahir-blue);
        box-shadow: 0 0 0 2px rgba(18, 127, 173, 0.15);
    }

    .pb-top-toolbar .search-box {
        width: 240px;
    }

    .pb-top-toolbar-right {
        margin-left: auto;
        display: flex;
        gap: 8px;
    }

    /* Area Tabel Full Height */
    .pb-table-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow-y: auto;
        min-height: 480px;
        background: #fff;
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
        letter-spacing: 0.3px;
        border: none;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .zahir-table tbody td {
        padding: 12px 15px;
        font-size: 13px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .zahir-table tbody tr {
        cursor: pointer;
        transition: background-color 0.15s;
    }

    .zahir-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    .zahir-table tbody tr:hover td {
        background-color: #e3f2fd !important;
    }

    .zahir-table tbody tr.selected td {
        background-color: #bbdefb !important;
        font-weight: 600;
    }

    /* Tombol Zahir */
    .btn-zahir {
        font-size: 13px;
        font-weight: 500;
        padding: 7px 18px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 80px;
        text-decoration: none !important;
    }

    .btn-zahir:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    .btn-zahir-primary { background: var(--zahir-blue); color: #fff; }
    .btn-zahir-primary:hover:not(:disabled) { background: var(--zahir-dark-blue); color: #fff; }
    .btn-zahir-danger { background: #d9534f; color: #fff; }
    .btn-zahir-danger:hover:not(:disabled) { background: #c9302c; color: #fff; }
    .btn-zahir-warning { background: #f0ad4e; color: #fff; }
    .btn-zahir-warning:hover:not(:disabled) { background: #ec971f; color: #fff; }
    .btn-zahir-success { background: #28a745; color: #fff; }
    .btn-zahir-success:hover:not(:disabled) { background: #218838; color: #fff; }
    .btn-zahir-secondary { background: #6c757d; color: #fff; }
    .btn-zahir-secondary:hover:not(:disabled) { background: #5a6268; color: #fff; }
    .btn-zahir-info { background: #0284c7; color: #fff; }
    .btn-zahir-info:hover:not(:disabled) { background: #0369a1; color: #fff; }

    /* Toolbar Bawah Sticky */
    .pb-bottom-bar {
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        border-top: 1px solid #eef2f5;
        margin-top: auto;
        position: sticky;
        bottom: 0;
        z-index: 10;
    }

    .pb-bottom-right {
        margin-left: auto;
        display: flex;
        gap: 10px;
    }

    /* Badge status */
    .status-invoiced {
        color: #28a745;
        font-weight: 700;
        font-size: 12px;
    }

    .status-open {
        color: #0284c7;
        font-weight: 700;
        font-size: 12px;
    }

    .status-cancelled {
        color: #dc2626;
        font-weight: 700;
        font-size: 12px;
    }

    /* Pesan kosong */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #95a5a6;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 12px;
        color: #cbd5e1;
    }

    /* Context Menu Styles */
    .context-menu {
        position: absolute;
        background: #fff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 6px 18px rgba(0,0,0,0.12);
        border-radius: 6px;
        z-index: 9999;
        min-width: 180px;
        overflow: hidden;
    }

    .context-menu ul {
        list-style: none;
        padding: 4px 0;
        margin: 0;
    }

    .context-menu li {
        padding: 8px 16px;
        cursor: pointer;
        font-size: 13px;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.15s, color 0.15s;
    }

    .context-menu li:hover {
        background: #e3f2fd;
        color: var(--zahir-blue);
        font-weight: 500;
    }

    .context-menu li.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .context-menu li.disabled:hover {
        background: transparent;
        color: #94a3b8;
    }

    .context-menu li i {
        width: 16px;
        text-align: center;
        color: var(--zahir-blue);
    }

    /* Modal Detail Jurnal */
    .jurnal-modal .modal-content {
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }

    .jurnal-modal .modal-header {
        background: var(--zahir-blue);
        color: #fff;
        padding: 10px 16px;
    }

    .jurnal-modal .modal-title {
        font-size: 16px;
        font-weight: 600;
    }

    .jurnal-box-header {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        padding: 10px 14px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }

    .jurnal-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .jurnal-table th {
        background-color: #f1f5f9;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 12px;
        border-bottom: 2px solid #cbd5e1;
    }

    .jurnal-table td {
        font-size: 12px;
        padding: 8px 12px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .jurnal-table tr:hover td {
        background-color: #f8fafc;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <!-- Navbar & Sidebar -->
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="pb-container">

            <!-- FLASH MESSAGE -->
            <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $cls): ?>
                <?php if ($msg = $this->session->flashdata($key)): ?>
                    <div class="alert alert-<?= $cls ?> alert-dismissible fade show mb-3">
                        <i class="fas fa-<?= $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-circle' : 'info-circle') ?> mr-1"></i>
                        <?= $msg ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="zahir-card">
                <!-- Header Title -->
                <div class="pb-header-title">
                    <h2><i class="fas fa-store mr-2"></i> Daftar Sales Order Loby</h2>
                </div>

                <!-- Toolbar Atas -->
                <div class="pb-top-toolbar">
                    <form method="get" action="<?= base_url('sales_order_loby') ?>" id="formFilter" class="d-flex align-items-center flex-wrap gap-2 w-100">
                        <input type="text" id="searchInput" class="search-box mr-2" placeholder="Cari No SO / Customer..." value="<?= html_escape($filter['keyword'] ?? '') ?>" />
                        
                        <span class="small font-weight-bold text-muted ml-2">Tanggal:</span>
                        <input type="date" name="date_start" value="<?= html_escape($filter['date_start'] ?? '') ?>" title="Dari Tanggal" />
                        <span class="text-muted">s/d</span>
                        <input type="date" name="date_end" value="<?= html_escape($filter['date_end'] ?? '') ?>" title="Sampai Tanggal" />
                        
                        <span class="small font-weight-bold text-muted ml-2">Status:</span>
                        <select name="status" style="width: 170px;">
                            <option value="">-- Semua Status --</option>
                            <option value="un-invoiced" <?= ($filter['status'] ?? '') === 'un-invoiced' ? 'selected' : '' ?>>Belum Faktur (Open)</option>
                            <option value="invoiced" <?= ($filter['status'] ?? '') === 'invoiced' ? 'selected' : '' ?>>Sudah Faktur (Selesai)</option>
                            <option value="cancelled" <?= ($filter['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>

                        <div class="pb-top-toolbar-right">
                            <button type="submit" class="btn-zahir btn-zahir-primary"><i class="fas fa-filter"></i> Filter</button>
                            <a href="<?= base_url('sales_order_loby') ?>" class="btn-zahir btn-zahir-secondary"><i class="fas fa-undo"></i> Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Area Tabel Full Height -->
                <div class="pb-table-container">
                    <table class="zahir-table" id="soTable">
                        <colgroup>
                            <col style="width: 110px;">
                            <col style="width: 160px;">
                            <col style="width: auto;">
                            <col style="width: 140px;">
                            <col style="width: 85px;">
                            <col style="width: 110px;">
                            <col style="width: 160px;">
                            <col style="width: 150px;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>No. SO Loby</th>
                                <th>Customer</th>
                                <th>User / Kasir</th>
                                <th style="text-align:center;">Bayar</th>
                                <th style="text-align:center;">Status</th>
                                <th>No. Faktur</th>
                                <th style="text-align:right;">Nilai Transaksi</th>
                            </tr>
                        </thead>
                        <tbody id="soTableBody">
                            <?php if (!empty($so_list)): ?>
                                <?php foreach ($so_list as $row): ?>
                                    <tr data-id="<?= $row['id_so'] ?>"
                                        data-no-so="<?= html_escape($row['no_so']) ?>"
                                        data-status="<?= $row['status'] ?>"
                                        data-invoiced="<?= !empty($row['is_invoiced']) ? '1' : '0' ?>"
                                        data-id-faktur="<?= $row['id_faktur'] ?? '' ?>"
                                        data-no-faktur="<?= html_escape($row['no_faktur'] ?? '') ?>">
                                        <td><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                        <td><strong style="color: var(--zahir-blue);"><?= html_escape($row['no_so']) ?></strong></td>
                                        <td>
                                            <strong><?= html_escape($row['customer_name'] ?: $row['nama_customer']) ?></strong>
                                            <span class="text-muted small ml-1">(<?= html_escape($row['kd_customer']) ?>)</span>
                                        </td>
                                        <td><?= html_escape($row['create_by']) ?></td>
                                        <td style="text-align:center;">
                                            <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size:11px;">CASH</span>
                                        </td>
                                        <td style="text-align:center;">
                                            <?php if ($row['status'] === 'completed' || !empty($row['is_invoiced'])): ?>
                                                <span class="status-invoiced"><i class="fas fa-check-circle mr-1"></i>INVOICED</span>
                                            <?php elseif ($row['status'] === 'cancelled'): ?>
                                                <span class="status-cancelled"><i class="fas fa-times-circle mr-1"></i>BATAL</span>
                                            <?php else: ?>
                                                <span class="status-open"><i class="fas fa-clock mr-1"></i>OPEN</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['no_faktur'])): ?>
                                                <span style="color:#0f766e; font-weight:600;"><i class="fas fa-file-invoice mr-1"></i><?= html_escape($row['no_faktur']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted font-italic small">- Belum Faktur -</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:right; font-weight:700; color:#15803d;">
                                            Rp <?= number_format((float)$row['grand_total_so'], 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state">
                                        <i class="fas fa-inbox"></i><br>
                                        Belum ada transaksi Sales Order Loby.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Toolbar Bawah Full Width & Di Bawah Layar -->
                <div class="pb-bottom-bar">
                    <button class="btn-zahir btn-zahir-primary" onclick="doNew()">Baru</button>
                    <button class="btn-zahir btn-zahir-danger" onclick="doDelete()" id="btnDelete" disabled><i class="fas fa-trash"></i> Hapus</button>
                    <button class="btn-zahir btn-zahir-danger" onclick="doCancel()" id="btnCancel" disabled>Batalkan SO</button>
                    <button class="btn-zahir btn-zahir-warning" onclick="doUnpost()" id="btnUnpost" disabled><i class="fas fa-undo"></i> Unpost</button>
                    <div class="pb-bottom-right">
                        <button class="btn-zahir btn-zahir-info" onclick="doDetailJurnal()" id="btnJurnal" disabled>
                            <i class="fas fa-book-open"></i> Jurnal
                        </button>
                        <button class="btn-zahir btn-zahir-primary" onclick="doPerincian()" id="btnPerincian" disabled>Perincian</button>
                        <button class="btn-zahir btn-zahir-success" onclick="doFaktur()" id="btnFaktur" disabled>Faktur</button>
                        <button class="btn-zahir btn-zahir-secondary" onclick="doPrint()" id="btnPrint" disabled>Cetak</button>
                        <button class="btn-zahir btn-zahir-secondary" onclick="doClose()">Tutup</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php $this->load->view('partial/main/footer') ?>
</div>

<!-- Context Menu Klik Kanan -->
<div id="context-menu" class="context-menu" style="display: none;">
    <ul>
        <li id="ctx-detail-jurnal"><i class="fas fa-book-open"></i> Detail Jurnal</li>
        <li id="ctx-unpost"><i class="fas fa-undo"></i> Unpost Transaksi</li>
        <li id="ctx-perincian"><i class="fas fa-eye"></i> Perincian / Detail SO</li>
        <li id="ctx-faktur"><i class="fas fa-file-invoice-dollar"></i> Proses Faktur</li>
        <li id="ctx-edit"><i class="fas fa-edit"></i> Edit SO</li>
        <li id="ctx-print"><i class="fas fa-print"></i> Cetak Faktur</li>
        <li id="ctx-cancel" style="color: #f59e0b;"><i class="fas fa-times" style="color: #f59e0b;"></i> Batalkan SO</li>
        <li id="ctx-delete" style="color: #dc2626;"><i class="fas fa-trash" style="color: #dc2626;"></i> Hapus SO</li>
    </ul>
</div>

<!-- Modal Detail Jurnal (Zahir Style) -->
<div class="modal fade jurnal-modal" id="modalJurnal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-book-open mr-2"></i> Detail Jurnal</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="padding: 16px 20px;">
                <div id="jurnalHeaderBox" class="jurnal-box-header">
                    <div>
                        <strong style="color:var(--zahir-blue); font-size:14px;" id="jurnalTipe">SJ</strong> &emsp;
                        <span id="jurnalTanggal" style="font-weight:500;">-</span> &emsp;
                        <span id="jurnalKeterangan" style="color:#475569;">-</span>
                    </div>
                    <div style="font-size:12px; color:#64748b;">
                        Diinput oleh : <strong id="jurnalUser" style="color:#334155;">-</strong>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="jurnal-table">
                        <thead>
                            <tr>
                                <th style="width: 22%;">No. Dokumen</th>
                                <th style="width: 15%;">Kode Akun</th>
                                <th style="width: 33%;">Nama Akun</th>
                                <th style="width: 15%; text-align: right;">Debit</th>
                                <th style="width: 15%; text-align: right;">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="jurnalTableBody">
                            <tr>
                                <td colspan="5" style="text-align:center; padding:20px; color:#94a3b8;">
                                    Memuat data jurnal...
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background:#f1f5f9; font-weight:700;">
                                <td colspan="3" style="text-align:right;">TOTAL :</td>
                                <td id="jurnalTotalDebit" style="text-align:right; color:#1e293b;">0</td>
                                <td id="jurnalTotalKredit" style="text-align:right; color:#1e293b;">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="padding: 10px 16px; background:#f8fafc; border-top:1px solid #e2e8f0;">
                <button type="button" class="btn-zahir btn-zahir-primary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var BASE = '<?= rtrim(base_url(), "/") . "/" ?>';
var selectedId = null;
var selectedRowData = null;
var contextMenuId = null;
var contextRowData = null;

$(document).ready(function() {
    // Quick search filter
    $('#searchInput').on('keyup', function() {
        var val = $(this).val().toLowerCase();
        $('#soTableBody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    // Row selection (Single Click)
    $('#soTableBody').on('click', 'tr', function(e) {
        if ($(this).find('.empty-state').length) return;
        $('#soTableBody tr').removeClass('selected');
        $(this).addClass('selected');
        selectedId = $(this).data('id');
        selectedRowData = $(this).data();
        updateBottomToolbar();
    });

    // Double Click -> Buka Perincian
    $('#soTableBody').on('dblclick', 'tr', function() {
        if ($(this).find('.empty-state').length) return;
        var id = $(this).data('id');
        if (id) {
            window.location.href = BASE + 'sales_order_loby/detail/' + id;
        }
    });

    // Right Click Context Menu
    $('#soTableBody').on('contextmenu', 'tr', function(e) {
        if ($(this).find('.empty-state').length) return;
        e.preventDefault();

        $('#soTableBody tr').removeClass('selected');
        $(this).addClass('selected');
        selectedId = $(this).data('id');
        selectedRowData = $(this).data();
        contextMenuId = selectedId;
        contextRowData = selectedRowData;
        updateBottomToolbar();

        // Cek status untuk mengaktifkan/menonaktifkan menu item
        var isInvoiced = contextRowData.invoiced == '1' || contextRowData.status === 'completed';
        var isCancelled = contextRowData.status === 'cancelled';

        if (isInvoiced) {
            $('#ctx-unpost').removeClass('disabled');
        } else {
            $('#ctx-unpost').addClass('disabled');
        }

        if (isInvoiced || isCancelled) {
            $('#ctx-faktur').addClass('disabled');
            $('#ctx-edit').addClass('disabled');
            $('#ctx-cancel').addClass('disabled');
        } else {
            $('#ctx-faktur').removeClass('disabled');
            $('#ctx-edit').removeClass('disabled');
            $('#ctx-cancel').removeClass('disabled');
        }

        if (isInvoiced) {
            $('#ctx-delete').addClass('disabled');
        } else {
            $('#ctx-delete').removeClass('disabled');
        }

        if (contextRowData.idFaktur) {
            $('#ctx-print').removeClass('disabled');
        } else {
            $('#ctx-print').addClass('disabled');
        }

        // Tampilkan context menu di posisi kursor
        $('#context-menu').css({
            top: e.pageY + 'px',
            left: e.pageX + 'px'
        }).fadeIn(150);
    });

    // Sembunyikan context menu saat klik sembarang
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#context-menu').length) {
            $('#context-menu').fadeOut(100);
        }
    });

    // Context Menu Handlers
    $('#ctx-detail-jurnal').on('click', function() {
        $('#context-menu').hide();
        if (contextMenuId) showDetailJurnal(contextMenuId);
    });

    $('#ctx-unpost').on('click', function() {
        $('#context-menu').hide();
        if ($(this).hasClass('disabled')) return;
        doUnpost();
    });

    $('#ctx-perincian').on('click', function() {
        $('#context-menu').hide();
        if (contextMenuId) window.location.href = BASE + 'sales_order_loby/detail/' + contextMenuId;
    });

    $('#ctx-faktur').on('click', function() {
        $('#context-menu').hide();
        if ($(this).hasClass('disabled')) return;
        if (contextMenuId) window.location.href = BASE + 'sales_order_loby/form_faktur/' + contextMenuId;
    });

    $('#ctx-edit').on('click', function() {
        $('#context-menu').hide();
        if ($(this).hasClass('disabled')) return;
        if (contextMenuId) window.location.href = BASE + 'sales_order_loby/edit/' + contextMenuId;
    });

    $('#ctx-print').on('click', function() {
        $('#context-menu').hide();
        if ($(this).hasClass('disabled')) return;
        if (contextRowData && contextRowData.idFaktur) {
            window.open(BASE + 'sales_order_loby/print_faktur/' + contextRowData.idFaktur, '_blank');
        }
    });

    $('#ctx-cancel').on('click', function() {
        $('#context-menu').hide();
        if ($(this).hasClass('disabled')) return;
        if (contextMenuId) {
            if (confirm('Yakin ingin membatalkan SO Loby ini?')) {
                window.location.href = BASE + 'sales_order_loby/cancel/' + contextMenuId;
            }
        }
    });

    $('#ctx-delete').on('click', function() {
        $('#context-menu').hide();
        if ($(this).hasClass('disabled')) return;
        doDelete();
    });
});

function updateBottomToolbar() {
    if (!selectedId || !selectedRowData) {
        $('#btnDelete, #btnCancel, #btnUnpost, #btnJurnal, #btnPerincian, #btnFaktur, #btnPrint').prop('disabled', true);
        return;
    }

    var isInvoiced = selectedRowData.invoiced == '1' || selectedRowData.status === 'completed';
    var isCancelled = selectedRowData.status === 'cancelled';

    $('#btnPerincian').prop('disabled', false);
    $('#btnJurnal').prop('disabled', false);

    if (isInvoiced) {
        $('#btnUnpost').prop('disabled', false);
        $('#btnDelete').prop('disabled', true);
    } else {
        $('#btnUnpost').prop('disabled', true);
        $('#btnDelete').prop('disabled', false);
    }

    if (isInvoiced || isCancelled) {
        $('#btnCancel').prop('disabled', true);
        $('#btnFaktur').prop('disabled', true);
    } else {
        $('#btnCancel').prop('disabled', false);
        $('#btnFaktur').prop('disabled', false);
    }

    if (selectedRowData.idFaktur) {
        $('#btnPrint').prop('disabled', false);
    } else {
        $('#btnPrint').prop('disabled', true);
    }
}

function doNew() {
    window.location.href = BASE + 'sales_order_loby/create';
}

function doDelete() {
    var id = selectedId || contextMenuId;
    if (!id) return;
    if (confirm('Yakin ingin MENGHAPUS Sales Order Loby ini secara permanen?\n\nKuantitas reservasi stok akan dilepas dan dikembalikan ke stok fisik gudang.')) {
        window.location.href = BASE + 'sales_order_loby/delete/' + id;
    }
}

function doUnpost() {
    var id = selectedId || contextMenuId;
    if (!id) return;
    if (confirm('Yakin ingin UNPOST transaksi SO Loby ini?\n\nFaktur penjualan dan jurnal akuntansi akan dibatalkan, pemotongan stok fisik akan dikembalikan, dan status SO akan kembali menjadi Draft.')) {
        $.post(BASE + 'sales_order_loby/unpost', { id_so: id }, function(res) {
            if (res && res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert((res && res.message) ? res.message : 'Gagal melakukan unpost.');
            }
        }, 'json').fail(function() {
            alert('Terjadi kesalahan saat memproses unpost ke server.');
        });
    }
}

function doPerincian() {
    if (selectedId) {
        window.location.href = BASE + 'sales_order_loby/detail/' + selectedId;
    }
}

function doFaktur() {
    if (selectedId) {
        window.location.href = BASE + 'sales_order_loby/form_faktur/' + selectedId;
    }
}

function doPrint() {
    if (selectedRowData && selectedRowData.idFaktur) {
        window.open(BASE + 'sales_order_loby/print_faktur/' + selectedRowData.idFaktur, '_blank');
    }
}

function doCancel() {
    if (selectedId) {
        if (confirm('Yakin ingin membatalkan SO Loby ini? Reservasi stok akan dilepas.')) {
            window.location.href = BASE + 'sales_order_loby/cancel/' + selectedId;
        }
    }
}

function doDetailJurnal() {
    if (selectedId) {
        showDetailJurnal(selectedId);
    }
}

function doClose() {
    window.location.href = BASE + 'dashboard';
}

function showDetailJurnal(idSO) {
    $('#jurnalTableBody').html('<tr><td colspan="5" style="text-align:center; padding:20px; color:#94a3b8;"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data jurnal...</td></tr>');
    $('#modalJurnal').modal('show');

    $.getJSON(BASE + 'sales_order_loby/detail_jurnal_ajax/' + idSO, function(res) {
        if (!res.success) {
            $('#jurnalTableBody').html('<tr><td colspan="5" style="text-align:center; padding:20px; color:#d9534f;"><i class="fas fa-exclamation-triangle mr-1"></i> ' + res.message + '</td></tr>');
            $('#jurnalTipe').text('-');
            $('#jurnalTanggal').text('-');
            $('#jurnalKeterangan').text('-');
            $('#jurnalUser').text('-');
            $('#jurnalTotalDebit').text('0');
            $('#jurnalTotalKredit').text('0');
            return;
        }

        var h = res.header || {};
        $('#jurnalTipe').text(h.journal_type || 'SJ');
        $('#jurnalTanggal').text(h.tanggal_transaksi ? formatDateIndo(h.tanggal_transaksi) : '-');
        $('#jurnalKeterangan').text(h.keterangan || '-');
        $('#jurnalUser').text(res.user || '-');

        var rows = '';
        var totD = 0, totK = 0;

        if (res.details && res.details.length > 0) {
            res.details.forEach(function(d) {
                var debit = parseFloat(d.debit || 0);
                var kredit = parseFloat(d.kredit || 0);
                totD += debit;
                totK += kredit;

                rows += '<tr>' +
                    '<td><strong>' + (d.nomor_jurnal || h.nomor_jurnal || '-') + '</strong></td>' +
                    '<td>' + (d.kode_akun || '-') + '</td>' +
                    '<td>' + (d.nama_akun || '-') + '</td>' +
                    '<td style="text-align:right;">' + (debit > 0 ? formatRupiah(debit) : '-') + '</td>' +
                    '<td style="text-align:right;">' + (kredit > 0 ? formatRupiah(kredit) : '-') + '</td>' +
                    '</tr>';
            });
        } else {
            rows = '<tr><td colspan="5" style="text-align:center; padding:20px; color:#94a3b8;">Tidak ada baris jurnal.</td></tr>';
        }

        $('#jurnalTableBody').html(rows);
        $('#jurnalTotalDebit').text(formatRupiah(totD));
        $('#jurnalTotalKredit').text(formatRupiah(totK));
    }).fail(function() {
        $('#jurnalTableBody').html('<tr><td colspan="5" style="text-align:center; padding:20px; color:#d9534f;">Gagal memuat data jurnal dari server.</td></tr>');
    });
}

function formatRupiah(num) {
    return parseFloat(num).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function formatDateIndo(dtStr) {
    if (!dtStr) return '-';
    var d = new Date(dtStr);
    if (isNaN(d.getTime())) return dtStr;
    return ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth()+1)).slice(-2) + '/' + d.getFullYear();
}
</script>
</body>
