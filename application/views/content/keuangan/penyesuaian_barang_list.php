<!-- application/views/content/keuangan/penyesuaian_barang_list.php -->
<!-- Halaman List Pemakaian / Penyesuaian Barang (Zahir Style) -->
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
    .pb-top-toolbar input[type="date"] {
        font-size: 13px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 6px 10px;
        height: 34px;
        outline: none;
        background: #fff;
    }

    .pb-top-toolbar input[type="text"]:focus,
    .pb-top-toolbar input[type="date"]:focus {
        border-color: var(--zahir-blue);
        box-shadow: 0 0 0 2px rgba(18, 127, 173, 0.15);
    }

    .pb-top-toolbar .search-box {
        width: 220px;
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
    .btn-zahir-info { background: #17a2b8; color: #fff; }
    .btn-zahir-info:hover:not(:disabled) { background: #138496; color: #fff; }

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
    .status-posted {
        color: #28a745;
        font-size: 16px;
    }

    .status-draft {
        color: #8c9ba5;
        font-size: 12px;
        font-style: italic;
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
        min-width: 170px;
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

    /* Loading overlay */
    .loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .loading-overlay.hidden { display: none; }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <!-- Navbar & Sidebar -->
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="pb-container">
            <div class="zahir-card">
                <!-- Header Title -->
                <div class="pb-header-title">
                    <h2><i class="fas fa-sliders-h mr-2"></i> Pemakaian / Penyesuaian Barang</h2>
                </div>

                <!-- Toolbar Atas -->
                <div class="pb-top-toolbar">
                    <input type="text" id="searchInput" class="search-box" placeholder="Search..." />
                    <label style="font-size:13px; margin:0 4px 0 10px; font-weight:500;">Dari:</label>
                    <input type="date" id="dateFrom" />
                    <label style="font-size:13px; margin:0 4px 0 10px; font-weight:500;">Sampai:</label>
                    <input type="date" id="dateTo" />
                    <div class="pb-top-toolbar-right">
                        <button class="btn-zahir btn-zahir-primary" onclick="loadData()"><i class="fas fa-sync-alt"></i> Update</button>
                        <button class="btn-zahir btn-zahir-secondary" onclick="toggleFilter()"><i class="fas fa-filter"></i> Filter</button>
                    </div>
                </div>

                <!-- Area Tabel Full Height -->
                <div class="pb-table-container">
                    <div id="loadingOverlay" class="loading-overlay hidden">
                        <i class="fas fa-spinner fa-spin fa-2x" style="color:var(--zahir-blue)"></i>
                    </div>
                    <table class="zahir-table" id="pbTable">
                        <colgroup>
                            <col style="width: 140px;">
                            <col style="width: 180px;">
                            <col style="width: auto;">
                            <col style="width: 180px;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Referensi</th>
                                <th>Keterangan</th>
                                <th style="text-align:right">Nilai</th>
                            </tr>
                        </thead>
                        <tbody id="pbTableBody">
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="fas fa-inbox"></i><br>
                                    Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Toolbar Bawah Full Width & Di Bawah Layar -->
                <div class="pb-bottom-bar">
                    <button class="btn-zahir btn-zahir-primary" onclick="doNew()">Baru</button>
                    <button class="btn-zahir btn-zahir-primary" onclick="doDelete()" id="btnDelete" disabled>Hapus</button>
                    <button class="btn-zahir btn-zahir-primary" onclick="doUnpost()" id="btnUnpost" disabled>Unpost</button>
                    <div class="pb-bottom-right">
                        <button class="btn-zahir btn-zahir-primary" onclick="doPerincian()" id="btnPerincian" disabled>Perincian</button>
                        <button class="btn-zahir btn-zahir-primary" onclick="doPrint()" id="btnPrint" disabled>Cetak</button>
                        <button class="btn-zahir btn-zahir-primary" onclick="doClose()">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Context Menu Klik Kanan -->
<div id="context-menu" class="context-menu" style="display: none;">
    <ul>
        <li id="ctx-detail-jurnal"><i class="fas fa-book-open"></i> Detail Jurnal</li>
        <li id="ctx-edit"><i class="fas fa-edit"></i> Edit Transaksi</li>
        <li id="ctx-print"><i class="fas fa-print"></i> Cetak Bukti</li>
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
                        <strong style="color:var(--zahir-blue); font-size:14px;" id="jurnalTipe">IJ</strong> &emsp;
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
                            <tr><td colspan="5" class="text-center text-muted" style="padding:20px;">Memuat data jurnal...</td></tr>
                        </tbody>
                        <tfoot id="jurnalTableFoot" style="background:#f8fafc; font-weight:600;">
                            <!-- Total Debit & Kredit -->
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="padding: 10px 20px; background:#f8fafc; border-top:1px solid #e2e8f0;">
                <button class="btn-zahir btn-zahir-info" onclick="printJurnal()"><i class="fas fa-print"></i> Print</button>
                <button class="btn-zahir btn-zahir-secondary" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
var BASE = '<?= base_url(); ?>';
var selectedId = null;
var selectedRow = null;
var allData = [];
var contextMenuId = null;

$(document).ready(function() {
    loadData();

    // Sembunyikan context menu saat klik di mana saja
    $(document).click(function() {
        $('#context-menu').hide();
    });

    // Event Klik Kanan pada Baris Tabel
    $(document).on('contextmenu', '#pbTableBody tr', function(e) {
        var id = $(this).data('id');
        if (!id) return;

        e.preventDefault();
        selectRow(this, id);
        contextMenuId = id;

        $('#context-menu').css({
            display: 'block',
            left: e.pageX + 'px',
            top: e.pageY + 'px'
        });
    });

    // Klik menu "Detail Jurnal" di context menu
    $('#ctx-detail-jurnal').click(function() {
        if (!contextMenuId) return;
        showDetailJurnalModal(contextMenuId);
    });

    // Klik menu "Edit Transaksi" di context menu
    $('#ctx-edit').click(function() {
        if (!contextMenuId) return;
        openEdit(contextMenuId);
    });

    // Klik menu "Cetak Bukti" di context menu
    $('#ctx-print').click(function() {
        if (!contextMenuId) return;
        window.open(BASE + 'persediaan/penyesuaian_barang/print_receipt/' + contextMenuId, '_blank');
    });
});

// Muat data dari server
function loadData() {
    $('#loadingOverlay').removeClass('hidden');
    selectedId = null;
    selectedRow = null;
    updateButtons();

    $.post(BASE + 'persediaan/penyesuaian_barang/get_data', {
        search: $('#searchInput').val(),
        date_from: $('#dateFrom').val(),
        date_to: $('#dateTo').val()
    }, function(res) {
        $('#loadingOverlay').addClass('hidden');
        if (res.success) {
            allData = res.data;
            renderTable(res.data);
        }
    }, 'json').fail(function() {
        $('#loadingOverlay').addClass('hidden');
        $('#pbTableBody').html('<tr><td colspan="4" class="empty-state"><i class="fas fa-exclamation-triangle"></i><br>Gagal memuat data</td></tr>');
    });
}

// Render baris tabel
function renderTable(data) {
    if (!data || data.length === 0) {
        $('#pbTableBody').html('<tr><td colspan="4" class="empty-state"><i class="fas fa-inbox"></i><br>Tidak ada data</td></tr>');
        return;
    }

    var html = '';
    $.each(data, function(i, row) {
        var isPosted = row.status === 'POSTED';
        var checkIcon = isPosted ? ' <i class="fas fa-check" style="font-size: 13px; margin-left: 8px; color: #1e293b;"></i>' : '';

        html += '<tr data-id="' + row.id_penyesuaian + '" onclick="selectRow(this, ' + row.id_penyesuaian + ')" ondblclick="openEdit(' + row.id_penyesuaian + ')">';
        html += '<td>' + row.tanggal_formatted + '</td>';
        html += '<td>' + escHtml(row.no_referensi) + '</td>';
        html += '<td title="' + escAttr(row.keterangan || '') + '">' + escHtml(row.keterangan || '') + '</td>';
        html += '<td style="text-align:right">' + row.nilai_formatted + checkIcon + '</td>';
        html += '</tr>';
    });

    $('#pbTableBody').html(html);
}

// Pilih baris
function selectRow(tr, id) {
    $('#pbTable tbody tr').removeClass('selected');
    $(tr).addClass('selected');
    selectedId = id;
    selectedRow = allData.find(function(r) { return r.id_penyesuaian == id; });
    updateButtons();
}

// Update state tombol
function updateButtons() {
    var hasSelection = selectedId !== null;
    $('#btnDelete').prop('disabled', !hasSelection);
    $('#btnUnpost').prop('disabled', !hasSelection || (selectedRow && selectedRow.status !== 'POSTED'));
    $('#btnPerincian').prop('disabled', !hasSelection);
    $('#btnPrint').prop('disabled', !hasSelection);
}

// Baru
function doNew() {
    window.location.href = BASE + 'persediaan/penyesuaian_barang/form';
}

// Edit (double klik)
function openEdit(id) {
    window.location.href = BASE + 'persediaan/penyesuaian_barang/form/' + id;
}

// Hapus
function doDelete() {
    if (!selectedId) return;
    if (!confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) return;

    $.post(BASE + 'persediaan/penyesuaian_barang/delete', {
        id_penyesuaian: selectedId
    }, function(res) {
        alert(res.message);
        if (res.success) loadData();
    }, 'json');
}

// Unpost
function doUnpost() {
    if (!selectedId) return;
    if (!confirm('Unpost transaksi ini? Jurnal dan perubahan stok akan dibatalkan.')) return;

    $.post(BASE + 'persediaan/penyesuaian_barang/unpost', {
        id_penyesuaian: selectedId
    }, function(res) {
        alert(res.message);
        if (res.success) loadData();
    }, 'json');
}

// Tombol Perincian
function doPerincian() {
    if (!selectedId) return;
    showDetailJurnalModal(selectedId);
}

// Tampilkan modal perincian detail jurnal
function showDetailJurnalModal(idPenyesuaian) {
    $('#modalJurnal').modal('show');
    $('#jurnalTipe').text('IJ');
    $('#jurnalTanggal').text('Memuat...');
    $('#jurnalKeterangan').text('-');
    $('#jurnalUser').text('-');
    $('#jurnalTableBody').html('<tr><td colspan="5" class="text-center text-muted" style="padding:20px;"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat detail jurnal...</td></tr>');
    $('#jurnalTableFoot').empty();

    $.getJSON(BASE + 'persediaan/penyesuaian_barang/detail_jurnal_ajax/' + idPenyesuaian, function(res) {
        if (!res.success) {
            $('#jurnalTableBody').html('<tr><td colspan="5" class="text-center text-muted" style="padding:20px;">' + (res.message || 'Detail jurnal tidak ditemukan.') + '</td></tr>');
            return;
        }

        var h = res.header || {};
        var details = res.details || [];

        $('#jurnalTipe').text(h.journal_type || 'IJ');
        
        var dateFormatted = '-';
        if (h.tanggal_transaksi) {
            var parts = h.tanggal_transaksi.split('-');
            if (parts.length === 3) dateFormatted = parts[2] + '/' + parts[1] + '/' + parts[0];
            else dateFormatted = h.tanggal_transaksi;
        }
        $('#jurnalTanggal').text(dateFormatted);
        $('#jurnalKeterangan').text(h.keterangan || '-');
        $('#jurnalUser').text(res.user || '-');

        if (!details.length) {
            $('#jurnalTableBody').html('<tr><td colspan="5" class="text-center text-muted" style="padding:20px;">Tidak ada baris jurnal.</td></tr>');
            return;
        }

        var html = '';
        var totDebit = 0;
        var totKredit = 0;

        $.each(details, function(i, d) {
            var debit = parseFloat(d.debit) || 0;
            var kredit = parseFloat(d.kredit) || 0;
            totDebit += debit;
            totKredit += kredit;

            html += '<tr>';
            html += '<td>' + escHtml(d.nomor_dokumen || h.nomor_jurnal || '-') + '</td>';
            html += '<td><strong>' + escHtml(d.kode_akun || '') + '</strong></td>';
            html += '<td>' + escHtml(d.nama_akun || '') + '</td>';
            html += '<td style="text-align:right">' + (debit > 0 ? formatNumber(debit) : '') + '</td>';
            html += '<td style="text-align:right">' + (kredit > 0 ? formatNumber(kredit) : '') + '</td>';
            html += '</tr>';
        });

        $('#jurnalTableBody').html(html);

        var footHtml = '<tr>';
        footHtml += '<td colspan="3" style="text-align:right">Total:</td>';
        footHtml += '<td style="text-align:right">' + formatNumber(totDebit) + '</td>';
        footHtml += '<td style="text-align:right">' + formatNumber(totKredit) + '</td>';
        footHtml += '</tr>';
        $('#jurnalTableFoot').html(footHtml);
    }).fail(function() {
        $('#jurnalTableBody').html('<tr><td colspan="5" class="text-center text-muted" style="padding:20px;">Gagal menghubungi server.</td></tr>');
    });
}

// Cetak
function doPrint() {
    if (!selectedId) return;
    window.open(BASE + 'persediaan/penyesuaian_barang/print_receipt/' + selectedId, '_blank');
}

// Tutup (kembali ke dashboard)
function doClose() {
    window.location.href = BASE + 'dashboard';
}

// Filter toggle
function toggleFilter() {
    loadData();
}

// Search enter
$('#searchInput').on('keypress', function(e) {
    if (e.which === 13) loadData();
});

// Utility
function escHtml(str) {
    if (!str) return '';
    return $('<span>').text(str).html();
}

function escAttr(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function formatNumber(val) {
    var num = parseFloat(val) || 0;
    return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function printJurnal() {
    var content = document.getElementById('modalJurnal').innerHTML;
    var win = window.open('', '_blank');
    win.document.write('<html><head><title>Detail Jurnal</title>');
    win.document.write('<style>body{font-family:Arial,sans-serif;font-size:12px;padding:20px} table{width:100%;border-collapse:collapse} th,td{border:1px solid #ccc;padding:6px 8px} th{background:#127fad;color:#fff}</style>');
    win.document.write('</head><body>');
    win.document.write('<h3>Detail Jurnal Penyesuaian Barang</h3>');
    win.document.write(content);
    win.document.write('</body></html>');
    win.document.close();
    win.print();
}
</script>
</body>
