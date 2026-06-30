<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid d-flex align-items-center justify-content-between">
                <h1 class="m-0"><?= html_escape($page_heading ?? 'Barang Pending') ?></h1>
                <div>
                    <a href="<?= base_url('admin/stockopname/barang-pending/export-csv') ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </a>
                    <a href="<?= base_url('admin/stockopname/master_opname') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Master Opname
                    </a>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <style>
                    .pending-panel{background:#fff;border:1px solid #e2e8f0;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06)}
                    .pending-panel .card-header{background:#fff;border-bottom:1px solid #e2e8f0;font-weight:700}
                    .pending-stat{border:1px solid #e2e8f0;border-radius:8px;background:#fff;padding:12px}
                    .pending-stat span{display:block;color:#64748b;font-size:12px}.pending-stat strong{font-size:20px}
                    .pending-code{font-family:monospace;font-weight:700}.pending-table-wrap{max-height:620px;overflow:auto}
                </style>
                <div class="row mb-3">
                    <div class="col-md-3 mb-2"><div class="pending-stat"><span>Total Item</span><strong id="statTotalItem"><?= (int)($summary['total_item'] ?? 0) ?></strong></div></div>
                    <div class="col-md-3 mb-2"><div class="pending-stat"><span>Total Qty</span><strong id="statTotalQty"><?= (int)($summary['total_qty'] ?? 0) ?></strong></div></div>
                    <div class="col-md-3 mb-2"><div class="pending-stat"><span>Total Qty PCS</span><strong id="statTotalPcs"><?= (int)($summary['total_qty_pcs'] ?? 0) ?></strong></div></div>
                    <div class="col-md-3 mb-2"><div class="pending-stat"><span>Total Qty Box</span><strong id="statTotalBox"><?= (int)($summary['total_qty_box'] ?? 0) ?></strong></div></div>
                </div>
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <div class="card pending-panel">
                            <div class="card-header"><i class="fas fa-box-open mr-1"></i> Input Barang Pending</div>
                            <form id="formBarangPending" class="card-body">
                                <input type="hidden" name="id" id="pendingId">
                                <div class="form-group">
                                    <label>Kode barang</label>
                                    <input name="kode_barang" id="kodeBarang" class="form-control pending-code" maxlength="50" required>
                                </div>
                                <div class="form-group">
                                    <label>Nama barang</label>
                                    <textarea name="nama_barang" id="namaBarang" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="col-7 form-group">
                                        <label>Expired date</label>
                                        <input name="expired_date" id="expiredDate" type="date" class="form-control" required>
                                    </div>
                                    <div class="col-5 form-group">
                                        <label>Lot</label>
                                        <input name="no_lot" id="noLot" class="form-control" maxlength="100" placeholder="Opsional">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-4 form-group"><label>Qty</label><input name="qty" id="qty" type="number" min="0" step="1" class="form-control" value="0" required></div>
                                    <div class="col-4 form-group"><label>Qty PCS</label><input name="qty_pcs" id="qtyPcs" type="number" min="0" step="1" class="form-control" value="0" required></div>
                                    <div class="col-4 form-group"><label>Qty Box</label><input name="qty_box" id="qtyBox" type="number" min="0" step="1" class="form-control" value="0" required></div>
                                </div>
                                <div class="d-flex">
                                    <button type="submit" id="btnSimpanPending" class="btn btn-primary flex-fill mr-2"><i class="fas fa-save"></i> Simpan</button>
                                    <button type="button" id="btnResetPending" class="btn btn-outline-secondary"><i class="fas fa-undo"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-8 mb-3">
                        <div class="card pending-panel">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Data Pending</span>
                                <div class="input-group input-group-sm" style="max-width:360px">
                                    <input type="search" class="form-control" id="searchPending" placeholder="Cari barang pending">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-primary" type="button" id="btnCariPending"><i class="fas fa-search"></i></button>
                                        <button class="btn btn-outline-secondary" type="button" id="btnRefreshPending"><i class="fas fa-sync-alt"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="pending-table-wrap">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Expired</th>
                                            <th>Lot</th>
                                            <th>Nama Barang</th>
                                            <th class="text-right">Qty</th>
                                            <th class="text-right">PCS</th>
                                            <th class="text-right">Box</th>
                                            <th>Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="barangPendingRows"><tr><td colspan="9" class="text-center text-muted p-4">Memuat data...</td></tr></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <footer class="main-footer"><strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong></footer>
</div>
<script>
window.addEventListener('load', function () {
    var pendingRows = {};
    function esc(value) { return String(value === null || value === undefined ? '' : value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
    function notify(icon, message) { if (typeof Swal !== 'undefined') { Swal.fire({toast:true,position:'top-end',icon:icon,title:message,showConfirmButton:false,timer:2600}); } else { alert(message); } }
    function resetForm() {
        $('#formBarangPending')[0].reset();
        $('#pendingId').val('');
        $('#qty,#qtyPcs,#qtyBox').val(0);
        $('#btnSimpanPending').html('<i class="fas fa-save"></i> Simpan');
    }
    function updateSummary(summary) {
        summary = summary || {};
        $('#statTotalItem').text(summary.total_item || 0);
        $('#statTotalQty').text(summary.total_qty || 0);
        $('#statTotalPcs').text(summary.total_qty_pcs || 0);
        $('#statTotalBox').text(summary.total_qty_box || 0);
    }
    function loadRows(resetSearch) {
        if (resetSearch) { $('#searchPending').val(''); }
        $('#barangPendingRows').html('<tr><td colspan="9" class="text-center text-muted p-4">Memuat data...</td></tr>');
        $.getJSON('<?= base_url('admin/stockopname/barang-pending/list') ?>', {keyword: $.trim($('#searchPending').val())}, function (res) {
            if (!res.status) { notify('error', res.message || 'Gagal memuat data'); return; }
            updateSummary(res.data.summary);
            var rows = res.data.rows || [];
            pendingRows = {};
            if (!rows.length) { $('#barangPendingRows').html('<tr><td colspan="9" class="text-center text-muted p-4">Belum ada data pending.</td></tr>'); return; }
            $('#barangPendingRows').html(rows.map(function (row) {
                pendingRows[row.id] = row;
                var badge = parseInt(row.master_id || 0, 10) > 0 ? '<span class="badge badge-success">Masuk master</span>' : '<span class="badge badge-warning">Belum ada master</span>';
                return '<tr>' +
                    '<td class="pending-code">' + esc(row.kode_barang) + '</td>' +
                    '<td>' + esc(row.expired_date) + '</td>' +
                    '<td>' + esc(row.no_lot || '-') + '</td>' +
                    '<td>' + esc(row.nama_barang) + '</td>' +
                    '<td class="text-right">' + esc(row.qty) + '</td>' +
                    '<td class="text-right">' + esc(row.qty_pcs) + '</td>' +
                    '<td class="text-right">' + esc(row.qty_box) + '</td>' +
                    '<td>' + badge + '</td>' +
                    '<td class="text-center"><button type="button" class="btn btn-outline-primary btn-sm btn-edit-pending" data-id="' + esc(row.id) + '"><i class="fas fa-edit"></i></button> <button type="button" class="btn btn-outline-danger btn-sm btn-delete-pending" data-id="' + esc(row.id) + '"><i class="fas fa-trash"></i></button></td>' +
                    '</tr>';
            }).join(''));
        }).fail(function () {
            $('#barangPendingRows').html('<tr><td colspan="9" class="text-center text-danger p-4">Gagal memuat data pending.</td></tr>');
        });
    }
    $('#btnResetPending').on('click', resetForm);
    $('#btnRefreshPending').on('click', function () { loadRows(true); });
    $('#btnCariPending').on('click', function () { loadRows(false); });
    $('#searchPending').on('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); loadRows(false); } });
    $('#barangPendingRows').on('click', '.btn-edit-pending', function () {
        var row = pendingRows[$(this).data('id')];
        if (!row) { notify('error', 'Data pending tidak ditemukan.'); return; }
        $('#pendingId').val(row.id);
        $('#kodeBarang').val(row.kode_barang);
        $('#namaBarang').val(row.nama_barang);
        $('#expiredDate').val(row.expired_date);
        $('#noLot').val(row.no_lot);
        $('#qty').val(row.qty);
        $('#qtyPcs').val(row.qty_pcs);
        $('#qtyBox').val(row.qty_box);
        $('#btnSimpanPending').html('<i class="fas fa-save"></i> Update');
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
    $('#barangPendingRows').on('click', '.btn-delete-pending', function () {
        var id = $(this).data('id');
        var runDelete = function () {
            $.ajax({url:'<?= base_url('admin/stockopname/barang-pending/delete') ?>',type:'POST',dataType:'json',data:{id:id}})
                .done(function (res) { notify(res.status ? 'success' : 'error', res.message || 'Data diproses'); if (res.status) { resetForm(); loadRows(false); } })
                .fail(function () { notify('error', 'Server gagal menghapus data pending'); });
        };
        if (typeof Swal !== 'undefined') {
            Swal.fire({icon:'warning',title:'Hapus barang pending?',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal'}).then(function (result) { if (result.isConfirmed) { runDelete(); } });
        } else if (confirm('Hapus barang pending?')) {
            runDelete();
        }
    });
    $('#formBarangPending').on('submit', function (event) {
        event.preventDefault();
        var $button = $('#btnSimpanPending'), original = $button.html();
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan');
        $.ajax({url:'<?= base_url('admin/stockopname/barang-pending/save') ?>',type:'POST',dataType:'json',data:$(this).serialize()})
            .done(function (res) {
                if (!res.status) { notify('error', res.message || 'Gagal menyimpan data'); return; }
                notify('success', res.message || 'Barang pending berhasil disimpan');
                resetForm();
                loadRows(false);
            })
            .fail(function (xhr) { notify('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Server gagal menyimpan data'); })
            .always(function () {
                $button.prop('disabled', false).html($('#pendingId').val() ? original : '<i class="fas fa-save"></i> Simpan');
            });
    });
    loadRows(false);
});
</script>
