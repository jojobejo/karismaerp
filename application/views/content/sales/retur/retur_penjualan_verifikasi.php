<!-- views/content/sales/retur/retur_penjualan_verifikasi.php -->
<!-- Admin Stock verifikasi Retur Penjualan: koreksi harga/qty, lalu setuju/tolak -->
<style>
    .table-rp-ver th { background:#f8f9fa; font-size:12px; border:1px solid #dee2e6; }
    .table-rp-ver td { font-size:12px; border:1px solid #dee2e6; vertical-align:middle; }
    .input-koreksi { width:120px; }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-boxes mr-2 text-info"></i>
                            Admin Stock: Verifikasi Retur Penjualan
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan/retur') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Verifikasi</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php foreach (['success'=>'success','error'=>'danger'] as $k=>$c): ?>
                    <?php if ($msg = $this->session->flashdata($k)): ?>
                        <div class="alert alert-<?= $c ?> alert-dismissible">
                            <?= $msg ?>
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <form action="<?= base_url('retur_penjualan/retur/verifikasi_simpan/' . $retur['id_retur']) ?>" method="post" id="formVerifikasi">
                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow" style="border:2px solid #17a2b8;">
                                <div class="card-header bg-info text-white py-2">
                                    <h3 class="card-title">
                                        <i class="fas fa-boxes mr-1"></i>
                                        Verifikasi: <?= htmlspecialchars($retur['no_retur']) ?>
                                        <small class="ml-2" style="font-size:11px; opacity:.8;">SPR: <?= htmlspecialchars($retur['no_spr']) ?></small>
                                    </h3>
                                </div>
                                <div class="card-body">

                                    <!-- INFO -->
                                    <table class="table table-sm table-borderless mb-3" style="font-size:13px;">
                                        <tr>
                                            <td class="font-weight-bold" style="width:130px;">Customer</td>
                                            <td>: <strong><?= htmlspecialchars($retur['nama_customer'] ?: ($retur['nama_customer_master'] ?? '-')) ?></strong></td>
                                            <td class="font-weight-bold" style="width:120px;">Tgl. Retur</td>
                                            <td>: <?= $retur['tanggal_retur'] ? date('d/m/Y', strtotime($retur['tanggal_retur'])) : '-' ?></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Sales</td>
                                            <td>: <?= htmlspecialchars($retur['nama_sales'] ?? '-') ?></td>
                                            <td class="font-weight-bold">Dibuat oleh</td>
                                            <td>: <?= htmlspecialchars($retur['create_by_retur']) ?></td>
                                        </tr>
                                    </table>

                                    <p class="small text-info mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Koreksi qty atau harga satuan jika diperlukan, kemudian pilih tindakan di sebelah kanan.
                                    </p>

                                    <!-- TABEL BARANG (editable) -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm table-rp-ver" id="tblVerifikasi">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" style="width:36px;">No.</th>
                                                    <th>Nama Barang</th>
                                                    <th>No. Faktur</th>
                                                    <th>No. Batch</th>
                                                    <th style="width:100px;">Qty Retur <span class="text-danger">*</span></th>
                                                    <th style="width:130px;">Harga Satuan <span class="text-danger">*</span></th>
                                                    <th style="width:120px;">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($retur_detail as $i => $d): ?>
                                                <tr class="ver-row">
                                                    <td class="text-center"><?= $i + 1 ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($d['nama_barang'] ?? '-') ?>
                                                        <input type="hidden" name="id_retur_detail[]" value="<?= $d['id_retur_detail'] ?>">
                                                    </td>
                                                    <td class="small"><?= htmlspecialchars($d['no_faktur'] ?? '-') ?></td>
                                                    <td class="small"><?= htmlspecialchars($d['no_batch'] ?? '-') ?></td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm input-koreksi qty-ver"
                                                               name="qty_retur[]"
                                                               value="<?= number_format((float)$d['qty_retur'], 3, '.', '') ?>"
                                                               min="0.001" step="0.001" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm input-koreksi harga-ver"
                                                               name="harga_satuan[]"
                                                               value="<?= number_format((float)$d['harga_satuan'], 0, '.', '') ?>"
                                                               min="0" step="1" required>
                                                    </td>
                                                    <td class="text-right font-weight-bold text-success total-ver-cell">
                                                        Rp <?= number_format((float)$d['qty_retur'] * (float)$d['harga_satuan'], 0, ',', '.') ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr style="background:#f0fff4;">
                                                    <td colspan="6" class="text-right font-weight-bold">Total:</td>
                                                    <td class="text-right font-weight-bold text-success" id="grandTotalVer">-</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <?php if ($retur['catatan_logistik']): ?>
                                    <div class="small text-muted mt-2">
                                        <i class="fas fa-truck-loading mr-1"></i>
                                        <strong>Catatan Logistik:</strong> <?= nl2br(htmlspecialchars($retur['catatan_logistik'])) ?>
                                    </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>

                        <!-- FORM KEPUTUSAN -->
                        <div class="col-lg-4">
                            <div class="card shadow border-info" style="border-width:2px !important;">
                                <div class="card-header bg-info text-white py-2">
                                    <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Keputusan Admin Stock</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-1">Tindakan <span class="text-danger">*</span></label>
                                        <div>
                                            <div class="custom-control custom-radio mb-2">
                                                <input type="radio" id="aksi_setuju" name="aksi" value="setuju" class="custom-control-input" required>
                                                <label class="custom-control-label text-success" for="aksi_setuju">
                                                    <i class="fas fa-check-circle"></i> <strong>Setuju — Teruskan ke Collection</strong>
                                                </label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="aksi_tolak" name="aksi" value="tolak" class="custom-control-input">
                                                <label class="custom-control-label text-danger" for="aksi_tolak">
                                                    <i class="fas fa-times-circle"></i> <strong>Tolak — Kembalikan</strong>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-1">
                                            Catatan Admin Stock
                                            <small class="text-muted font-weight-normal">(wajib jika ditolak)</small>
                                        </label>
                                        <textarea class="form-control" name="catatan_admin_stock" id="catatanAdminStock" rows="4"
                                                  placeholder="Catatan verifikasi barang & harga..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-info btn-block">
                                        <i class="fas fa-save"></i> Simpan Keputusan
                                    </button>
                                    <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-light btn-block mt-2">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(document).ready(function() {
    function formatRp(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

    function hitungVer() {
        var grand = 0;
        $('.ver-row').each(function() {
            var qty   = parseFloat($(this).find('.qty-ver').val()) || 0;
            var harga = parseFloat($(this).find('.harga-ver').val()) || 0;
            var total = qty * harga;
            $(this).find('.total-ver-cell').text(formatRp(total));
            grand += total;
        });
        $('#grandTotalVer').text(formatRp(grand));
    }

    $(document).on('input', '.qty-ver, .harga-ver', hitungVer);
    hitungVer();

    $('#formVerifikasi').on('submit', function(e) {
        var aksi    = $('input[name="aksi"]:checked').val();
        var catatan = $('#catatanAdminStock').val().trim();
        if (!aksi) { e.preventDefault(); alert('Pilih tindakan terlebih dahulu.'); return; }
        if (aksi === 'tolak' && !catatan) { e.preventDefault(); alert('Catatan wajib diisi jika ditolak.'); $('#catatanAdminStock').focus(); return; }
        if (!confirm(aksi === 'tolak' ? 'Yakin menolak retur ini?' : 'Yakin menyetujui dan meneruskan ke Team Collection?')) { e.preventDefault(); }
    });
});
</script>
