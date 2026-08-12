<!-- application/views/content/keuangan/kas_keluar_list.php -->
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
    }

    .zahir-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
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
    }

    .zahir-table tbody td {
        padding: 12px 15px;
        font-size: 13px;
        border-bottom: 1px solid #eef2f5;
        vertical-align: middle;
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
        padding: 8px 24px;
        border-radius: 4px;
        transition: all 0.2s;
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

    .btn-zahir-danger {
        background-color: #e74c3c;
        border: 1px solid #c0392b;
        color: #fff;
    }

    .btn-zahir-danger:hover {
        background-color: #c0392b;
        color: #fff;
    }

    .btn-zahir-secondary {
        background-color: #fff;
        border: 1px solid #cbd5e1;
        color: #64748b;
    }

    .btn-zahir-secondary:hover {
        background-color: #f8fafc;
        color: #475569;
    }

    .footer-actions {
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 15px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .zahir-modal .modal-content {
        border-radius: 8px;
        border: none;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
    }

    .zahir-modal .modal-header {
        background-color: var(--zahir-blue);
        color: #fff;
        border-radius: 8px 8px 0 0;
    }

    .action-links {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding: 10px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13px;
    }

    .action-links a {
        color: var(--zahir-blue);
        text-decoration: none;
        font-weight: 500;
    }

    .action-links a:hover {
        text-decoration: underline;
        color: var(--zahir-dark-blue);
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

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="buku-besar-container">
            <div class="zahir-card">
                <div class="buku-besar-header d-flex justify-content-between align-items-center">
                    <h2>Kas Keluar</h2>
                    <div class="d-flex align-items-center" style="gap: 12px;">
                        <input type="text" id="search-input" class="form-control form-zahir-control" placeholder="Cari..." style="width: 240px; height: 35px; border-radius: 4px;">
                        <button type="button" class="btn btn-sm btn-light" id="btn-refresh" title="Refresh data">
                            <i class="fas fa-sync-alt text-dark"></i>
                        </button>
                    </div>
                </div>

                <div class="action-links">
                    <a href="javascript:void(0)" id="link-mata-uang">Mata Uang</a>
                    <a href="javascript:void(0)" id="link-update">Update</a>
                    <a href="javascript:void(0)" id="link-filter"><i class="fas fa-filter"></i> Filter</a>
                </div>

                <div class="table-responsive" style="max-height: 580px; min-height: 450px;">
                    <table class="table zahir-table" id="table-kas-keluar">
                        <thead>
                            <tr>
                                <th style="width: 15%">Tanggal</th>
                                <th style="width: 20%">Referensi</th>
                                <th style="width: 20%">Penerima</th>
                                <th style="width: 25%">Keterangan</th>
                                <th style="width: 15%; text-align: right;">Nilai</th>
                                <th style="width: 5%; text-align: center;"><i class="fas fa-check"></i></th>
                            </tr>
                        </thead>
                        <tbody id="list-kas-keluar-body">
                            <!-- Rows loaded via Ajax -->
                        </tbody>
                    </table>
                </div>

                <div class="footer-actions">
                    <div style="gap: 10px; display: flex;">
                        <button type="button" class="btn btn-zahir btn-zahir-primary" id="btn-baru">
                            <i class="fas fa-plus mr-1"></i> Baru
                        </button>
                        <button type="button" class="btn btn-zahir btn-zahir-danger" id="btn-hapus" disabled>
                            <i class="fas fa-trash mr-1"></i> Hapus
                        </button>
                    </div>
                    <div style="gap: 10px; display: flex;">
                        <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-perincian" disabled>
                            <i class="fas fa-info-circle mr-1"></i> Perincian
                        </button>
                        <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-cetak" disabled>
                            <i class="fas fa-print mr-1"></i> Cetak
                        </button>
                        <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-tutup">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Context Menu -->
    <div id="context-menu" class="context-menu" style="display:none;">
        <ul>
            <li id="menu-detail-jurnal"><i class="fas fa-file-invoice-dollar mr-2"></i> Detail Jurnal</li>
        </ul>
    </div>

    <!-- Modal Detail Jurnal -->
    <div class="modal fade zahir-modal" id="modalDetailJurnal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2" style="border-bottom: 2px solid #333;">
                        <div class="d-flex" style="gap: 20px; font-weight: bold; font-size: 15px;">
                            <span id="jurnal-kode"></span>
                            <span id="jurnal-tanggal"></span>
                        </div>
                        <div style="font-weight: bold; font-size: 15px;" id="jurnal-keterangan">
                        </div>
                    </div>
                    
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-borderless" style="font-size: 13px;">
                            <tbody id="jurnal-detail-body">
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top: 1px solid #ddd; font-size: 12px;">
                        <div>
                            Diinput oleh : <span id="jurnal-user"></span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-primary" id="btn-print-jurnal" style="width: 80px;">Print</button>
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" style="width: 80px;">Batal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FILTER DATA -->
    <div class="modal fade zahir-modal" id="modalFilter" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formFilterKasKeluar">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label text-right">Tanggal :</label>
                            <div class="col-sm-4">
                                <input type="date" class="form-control form-control-sm" name="date_from" id="filter-date-from" value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="col-sm-1 text-center mt-1">
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                            <div class="col-sm-4">
                                <input type="date" class="form-control form-control-sm" name="date_to" id="filter-date-to" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label text-right">Departemen :</label>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" id="filter-dept-from" disabled>
                                    <option value="0">0</option>
                                </select>
                            </div>
                            <div class="col-sm-1 text-center mt-1">
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" id="filter-dept-to" disabled>
                                    <option value="999999999">999999999</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label text-right">Proyek :</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm" value="0" disabled>
                            </div>
                            <div class="col-sm-1 text-center mt-1">
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm" value="999999999" disabled>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label text-right">Status :</label>
                            <div class="col-sm-9">
                                <select class="form-control form-control-sm" name="status" id="filter-status">
                                    <option value="Semua">Semua</option>
                                    <option value="DRAFT">DRAFT</option>
                                    <option value="POSTED">POSTED</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="btn-submit-filter">OK</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let selectedId = null;
    let selectedStatus = null;
    let contextMenuRowId = null;
    let contextMenuRowStatus = null;

    // Load data initially
    loadData();

    // Hide context menu on click anywhere
    $(document).click(function() {
        $('#context-menu').hide();
    });

    // Right-click on row
    $(document).on('contextmenu', '#list-kas-keluar-body tr', function(e) {
        if ($(this).hasClass('empty-row')) return;
        
        e.preventDefault();
        contextMenuRowId = $(this).data('id');
        contextMenuRowStatus = $(this).data('status');
        
        // Show context menu
        $('#context-menu').css({
            display: "block",
            left: e.pageX,
            top: e.pageY
        });
    });

    // Detail Jurnal Click
    $('#menu-detail-jurnal').click(function() {
        if (contextMenuRowStatus !== 'POSTED') {
            alert('Transaksi ini belum diposting ke jurnal.');
            return;
        }

        $.ajax({
            url: "<?= base_url('keuangan/kas_keluar/detail_jurnal_ajax/') ?>" + contextMenuRowId,
            type: "GET",
            dataType: "json",
            success: function(res) {
                if (res.success) {
                    let header = res.header;
                    let details = res.details;
                    
                    $('#jurnal-kode').text(header.journal_type || 'CD');
                    
                    let dateObj = new Date(header.tanggal_transaksi);
                    let d = String(dateObj.getDate()).padStart(2, '0');
                    let m = String(dateObj.getMonth() + 1).padStart(2, '0');
                    let y = dateObj.getFullYear();
                    $('#jurnal-tanggal').text(`${d}/${m}/${y}`);
                    
                    $('#jurnal-keterangan').text(header.keterangan || '-');
                    $('#jurnal-user').text(res.user || '-');

                    let html = '';
                    details.forEach(function(row) {
                        let isDebit = parseFloat(row.debit) > 0;
                        let val = isDebit ? parseFloat(row.debit) : parseFloat(row.kredit);
                        let valStr = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 6 }).format(val);
                        
                        let debitCol = isDebit ? valStr : '';
                        let kreditCol = !isDebit ? valStr : '';

                        html += `<tr>
                            <td style="width: 15%;">${row.nomor_dokumen || header.nomor_jurnal}</td>
                            <td style="width: 10%;">${row.kode_akun}</td>
                            <td style="width: 35%;">${row.nama_akun}</td>
                            <td style="width: 20%; text-align: right;">${debitCol}</td>
                            <td style="width: 20%; text-align: right;">${kreditCol}</td>
                        </tr>`;
                    });
                    
                    $('#jurnal-detail-body').html(html);
                    $('#modalDetailJurnal').modal('show');
                } else {
                    alert(res.message || 'Gagal memuat detail jurnal');
                }
            }
        });
    });

    // Print button on detail modal
    $('#btn-print-jurnal').click(function() {
        // Optional implementation for print if needed
        alert('Fitur Print Jurnal belum tersedia.');
    });

    // Refresh button
    $('#btn-refresh, #link-update').click(function() {
        loadData();
    });

    // Search keyup
    $('#search-input').on('keyup', function() {
        loadData();
    });

    // Filter link click
    $('#link-filter').click(function() {
        $('#modalFilter').modal('show');
    });

    // Submit filter
    $('#formFilterKasKeluar').submit(function(e) {
        e.preventDefault();
        loadData();
        $('#modalFilter').modal('hide');
    });

    // Close button
    $('#btn-tutup').click(function() {
        window.location.href = "<?= base_url('dashboard') ?>";
    });

    // Baru button
    $('#btn-baru').click(function() {
        window.location.href = "<?= base_url('keuangan/kas_keluar/form') ?>";
    });

    // Row selection
    $(document).on('click', '#list-kas-keluar-body tr', function() {
        if ($(this).hasClass('empty-row')) return;

        $('#list-kas-keluar-body tr').removeClass('selected');
        $(this).addClass('selected');
        
        selectedId = $(this).data('id');
        selectedStatus = $(this).data('status');

        $('#btn-hapus').prop('disabled', false);
        $('#btn-perincian').prop('disabled', false);
        $('#btn-cetak').prop('disabled', selectedStatus !== 'POSTED');
    });

    // Double click to open detail/form
    $(document).on('dblclick', '#list-kas-keluar-body tr', function() {
        if ($(this).hasClass('empty-row')) return;
        let id = $(this).data('id');
        window.location.href = "<?= base_url('keuangan/kas_keluar/form/') ?>" + id;
    });

    // Detail button
    $('#btn-perincian').click(function() {
        if (selectedId) {
            window.location.href = "<?= base_url('keuangan/kas_keluar/form/') ?>" + selectedId;
        }
    });

    // Cetak button
    $('#btn-cetak').click(function() {
        if (selectedId && selectedStatus === 'POSTED') {
            window.open("<?= base_url('keuangan/kas_keluar/print_receipt/') ?>" + selectedId, '_blank');
        }
    });

    // Hapus button
    $('#btn-hapus').click(function() {
        if (!selectedId) return;
        
        let confirmMsg = 'Apakah Anda yakin ingin menghapus transaksi ini?';
        if (selectedStatus === 'POSTED') {
            confirmMsg = 'Peringatan: Transaksi ini sudah terposting ke Jurnal. Menghapusnya juga akan menghapus Jurnal terkait. Apakah Anda yakin?';
        }

        if (confirm(confirmMsg)) {
            $.ajax({
                url: "<?= base_url('keuangan/kas_keluar/delete') ?>",
                type: "POST",
                data: { id_kas_keluar: selectedId },
                dataType: "json",
                success: function(res) {
                    if (res.success) {
                        alert(res.message);
                        loadData();
                        clearSelection();
                    } else {
                        alert(res.message);
                    }
                }
            });
        }
    });

    function clearSelection() {
        selectedId = null;
        selectedStatus = null;
        $('#btn-hapus').prop('disabled', true);
        $('#btn-perincian').prop('disabled', true);
        $('#btn-cetak').prop('disabled', true);
    }

    function loadData() {
        let search = $('#search-input').val();
        let date_from = $('#filter-date-from').val();
        let date_to = $('#filter-date-to').val();
        let status = $('#filter-status').val();

        $.ajax({
            url: "<?= base_url('keuangan/kas_keluar/get_data') ?>",
            type: "POST",
            data: {
                search: search,
                date_from: date_from,
                date_to: date_to,
                status: status
            },
            dataType: "json",
            success: function(res) {
                if (res.success) {
                    let html = '';
                    if (res.data.length > 0) {
                        res.data.forEach(function(row) {
                            let checkmark = row.status === 'POSTED' ? '<i class="fas fa-check text-success"></i>' : '';
                            let textStyle = row.status === 'DRAFT' ? 'style="color: #94a3b8; font-style: italic;"' : '';
                            html += `<tr data-id="${row.id_kas_keluar}" data-status="${row.status}" ${textStyle}>
                                <td>${row.tanggal_formatted}</td>
                                <td style="font-weight: bold; color: var(--zahir-blue);">${row.no_referensi}</td>
                                <td>${escapeHtml(row.penerima)}</td>
                                <td>${escapeHtml(row.memo)}</td>
                                <td style="text-align: right; font-weight: bold;">${row.nilai_formatted}</td>
                                <td style="text-align: center;">${checkmark}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr class="empty-row"><td colspan="6" class="text-center text-muted">Tidak ada data Kas Keluar.</td></tr>';
                    }
                    $('#list-kas-keluar-body').html(html);
                    clearSelection();
                }
            }
        });
    }

    function escapeHtml(string) {
        if (!string) return '';
        return String(string).replace(/[&<>"']/g, function (s) {
            return {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;"
            }[s];
        });
    }
});
</script>
</body>
