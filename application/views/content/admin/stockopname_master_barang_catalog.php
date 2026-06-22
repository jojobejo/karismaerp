<?php
defined('BASEPATH') or exit('No direct script access allowed');
$nextCode = $next_kode_barang_system ?? 'KIUBR00001';
?>
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid d-flex align-items-center justify-content-between">
                <h1 class="m-0"><?= html_escape($page_heading ?? 'Master Barang') ?></h1>
                <a href="<?= base_url('admin/stockopname/master_opname') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Master Opname</a>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <style>
                    .catalog-panel{background:#fff;border:1px solid #e2e8f0;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06)}
                    .catalog-panel .card-header{background:#fff;border-bottom:1px solid #e2e8f0;font-weight:700}.catalog-code{font-family:monospace;font-size:15px;font-weight:700}.catalog-table-wrap{max-height:570px;overflow:auto}
                </style>
                <div class="row">
                    <div class="col-lg-4 mb-3">
                        <div class="card catalog-panel h-100">
                            <div class="card-header"><i class="fas fa-plus-circle mr-1"></i> Tambah Barang</div>
                            <form id="formMasterBarangCatalog" class="card-body">
                                <div class="form-group">
                                    <label>Kode barang</label>
                                    <input name="kd_barang" class="form-control" maxlength="25" required autofocus placeholder="Contoh: QPROD01">
                                </div>
                                <div class="form-group">
                                    <label>Kode barang system</label>
                                    <input class="form-control catalog-code" id="kodeBarangSystem" readonly value="<?= html_escape($nextCode) ?>">
                                    <small class="form-text text-muted">Dibuat otomatis saat disimpan berdasarkan kode terakhir.</small>
                                </div>
                                <div class="form-group">
                                    <label>Nama barang</label>
                                    <textarea name="nama_barang" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="form-row">
                                    <div class="col-4 form-group"><label>Panjang</label><input name="p" type="number" min="1" step="1" class="form-control" required></div>
                                    <div class="col-4 form-group"><label>Lebar</label><input name="l" type="number" min="1" step="1" class="form-control" required></div>
                                    <div class="col-4 form-group"><label>Tinggi</label><input name="t" type="number" min="1" step="1" class="form-control" required></div>
                                </div>
                                <button class="btn btn-primary btn-block" id="btnSimpanMasterBarang" type="submit"><i class="fas fa-save"></i> Simpan Master Barang</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-8 mb-3">
                        <div class="card catalog-panel h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Data Master Barang Terbaru</span>
                                <div class="input-group input-group-sm" style="max-width:340px">
                                    <input type="search" class="form-control" id="searchKodeBarang" placeholder="Cari kode barang...">
                                    <div class="input-group-append"><button class="btn btn-outline-primary" type="button" id="btnCariKodeBarang"><i class="fas fa-search"></i> Cari</button><button class="btn btn-outline-secondary" type="button" id="btnRefreshCatalog" title="Reset pencarian"><i class="fas fa-sync-alt"></i></button></div>
                                </div>
                            </div>
                            <div class="catalog-table-wrap">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>Kode</th><th>Kode System</th><th>Nama Barang</th><th class="text-right">P</th><th class="text-right">L</th><th class="text-right">T</th></tr></thead>
                                    <tbody id="masterBarangCatalogRows"><tr><td colspan="6" class="text-center text-muted p-4">Memuat data...</td></tr></tbody>
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
    function esc(value) { return String(value === null || value === undefined ? '' : value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;'); }
    function notify(icon, message) { if (typeof Swal !== 'undefined') { Swal.fire({toast:true,position:'top-end',icon:icon,title:message,showConfirmButton:false,timer:2500}); } else { alert(message); } }
    function loadRows(resetSearch) {
        if (resetSearch) { $('#searchKodeBarang').val(''); }
        var kodeBarang = $.trim($('#searchKodeBarang').val());
        $('#masterBarangCatalogRows').html('<tr><td colspan="6" class="text-center text-muted p-4">Memuat data...</td></tr>');
        $.getJSON('<?= base_url('admin/stockopname/master_barang/list') ?>', {kd_barang: kodeBarang}, function (res) {
            if (!res.status) { notify('error', res.message || 'Gagal memuat data'); return; }
            var rows = res.data || [];
            if (!rows.length) { $('#masterBarangCatalogRows').html('<tr><td colspan="6" class="text-center text-muted p-4">Belum ada data.</td></tr>'); return; }
            $('#masterBarangCatalogRows').html(rows.map(function (row) {
                return '<tr><td>' + esc(row.kd_barang) + '</td><td class="catalog-code">' + esc(row.kode_barang_system) + '</td><td>' + esc(row.nama_barang) + '</td><td class="text-right">' + esc(row.p) + '</td><td class="text-right">' + esc(row.l) + '</td><td class="text-right">' + esc(row.t) + '</td></tr>';
            }).join(''));
        }).fail(function () { $('#masterBarangCatalogRows').html('<tr><td colspan="6" class="text-center text-danger p-4">Gagal memuat data.</td></tr>'); });
    }
    $('#btnRefreshCatalog').on('click', function () { loadRows(true); });
    $('#btnCariKodeBarang').on('click', function () { loadRows(false); });
    $('#searchKodeBarang').on('keydown', function (event) { if (event.key === 'Enter') { event.preventDefault(); loadRows(false); } });
    $('#formMasterBarangCatalog').on('submit', function (event) {
        event.preventDefault();
        var $button = $('#btnSimpanMasterBarang'), original = $button.html();
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan');
        $.ajax({url:'<?= base_url('admin/stockopname/master_barang/create') ?>',type:'POST',dataType:'json',data:$(this).serialize()})
            .done(function (res) {
                if (!res.status) { notify('error', res.message || 'Gagal menyimpan data'); return; }
                $('#formMasterBarangCatalog')[0].reset();
                $('#kodeBarangSystem').val(res.data.next_kode_barang_system || '');
                notify('success', res.message || 'Master barang berhasil disimpan');
                loadRows();
            })
            .fail(function (xhr) { notify('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Server gagal menyimpan data'); })
            .always(function () { $button.prop('disabled', false).html(original); });
    });
    loadRows();
});
</script>
