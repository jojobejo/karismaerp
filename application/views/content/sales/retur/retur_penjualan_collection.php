<!-- views/content/sales/retur/retur_penjualan_collection.php -->
<!-- Team Collection: tentukan retur ini akan dipotongkan ke faktur customer yang mana -->
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
                            <i class="fas fa-hand-holding-usd mr-2 text-primary"></i>
                            Team Collection: Proses Retur
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan/retur') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Proses Collection</li>
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

                <form action="<?= base_url('retur_penjualan/retur/collection_simpan/' . $retur['id_retur']) ?>" method="post" id="formCollection">
                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow" style="border:2px solid #007bff;">
                                <div class="card-header bg-primary text-white py-2">
                                    <h3 class="card-title">
                                        <i class="fas fa-hand-holding-usd mr-1"></i>
                                        Proses Collection: <?= htmlspecialchars($retur['no_retur']) ?>
                                    </h3>
                                </div>
                                <div class="card-body">

                                    <!-- INFO RINGKAS -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="border rounded p-2 h-100" style="background:#f8f9fa;">
                                                <div class="small font-weight-bold text-muted mb-1">Info Retur</div>
                                                <table class="table table-sm table-borderless mb-0" style="font-size:12px;">
                                                    <tr><td class="py-0 font-weight-bold">No. Retur</td><td class="py-0"><?= htmlspecialchars($retur['no_retur']) ?></td></tr>
                                                    <tr><td class="py-0 font-weight-bold">No. SPR</td><td class="py-0"><a href="<?= base_url('retur_penjualan/detail/' . $retur['id_spr']) ?>" class="text-danger"><?= htmlspecialchars($retur['no_spr']) ?></a></td></tr>
                                                    <tr><td class="py-0 font-weight-bold">Customer</td><td class="py-0"><strong><?= htmlspecialchars($retur['nama_customer'] ?: ($retur['nama_customer_master'] ?? '-')) ?></strong></td></tr>
                                                    <tr><td class="py-0 font-weight-bold">Sales</td><td class="py-0"><?= htmlspecialchars($retur['nama_sales'] ?? '-') ?></td></tr>
                                                    <tr><td class="py-0 font-weight-bold">Tgl. Retur</td><td class="py-0"><?= $retur['tanggal_retur'] ? date('d/m/Y', strtotime($retur['tanggal_retur'])) : '-' ?></td></tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border border-success rounded p-2 h-100" style="background:#f0fff4;">
                                                <div class="small font-weight-bold text-success mb-1">Nilai Retur (diverifikasi Admin Stock)</div>
                                                <?php
                                                $total_retur = 0;
                                                foreach ($retur_detail as $d) $total_retur += (float)$d['qty_retur'] * (float)$d['harga_satuan'];
                                                ?>
                                                <div style="font-size:22px; font-weight:700; color:#27ae60;">Rp <?= number_format($total_retur, 0, ',', '.') ?></div>
                                                <div class="small text-muted mt-1">Diverifikasi oleh: <?= htmlspecialchars($retur['admin_stock_by_retur'] ?? '-') ?></div>
                                                <?php if ($retur['catatan_admin_stock']): ?>
                                                <div class="small text-muted">Catatan: <?= htmlspecialchars($retur['catatan_admin_stock']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TABEL DETAIL (read only) -->
                                    <h6 class="font-weight-bold mb-2"><i class="fas fa-boxes mr-1 text-success"></i> Detail Barang</h6>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm" style="font-size:12px;">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">No.</th>
                                                    <th>Nama Barang</th>
                                                    <th>No. Faktur</th>
                                                    <th class="text-right">Qty</th>
                                                    <th class="text-right">Harga</th>
                                                    <th class="text-right">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($retur_detail as $i => $d): ?>
                                                <tr>
                                                    <td class="text-center"><?= $i + 1 ?></td>
                                                    <td><?= htmlspecialchars($d['nama_barang'] ?? '-') ?></td>
                                                    <td class="small"><?= htmlspecialchars($d['no_faktur'] ?? '-') ?></td>
                                                    <td class="text-right"><?= number_format((float)$d['qty_retur'], 3) ?></td>
                                                    <td class="text-right">Rp <?= number_format((float)$d['harga_satuan'], 0, ',', '.') ?></td>
                                                    <td class="text-right font-weight-bold text-success">Rp <?= number_format((float)$d['qty_retur'] * (float)$d['harga_satuan'], 0, ',', '.') ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- FORM COLLECTION -->
                                    <h6 class="font-weight-bold mb-2">
                                        <i class="fas fa-file-invoice-dollar mr-1 text-primary"></i>
                                        Tentukan Potongan Faktur Customer
                                    </h6>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-1">
                                            No. Faktur yang Dipotong <span class="text-danger">*</span>
                                            <small class="text-muted font-weight-normal">(faktur customer yang akan dikurangi)</small>
                                        </label>
                                        <input type="text" class="form-control" name="no_faktur_potong"
                                               id="noFakturPotong"
                                               placeholder="Masukkan no. faktur customer yang akan dipotong retur ini..."
                                               required>
                                        <small class="text-muted">Contoh: FK/001/2026/..., bisa lebih dari satu pisahkan dengan koma</small>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-1">
                                            Catatan Collection
                                            <small class="text-muted font-weight-normal">(wajib)</small>
                                        </label>
                                        <textarea class="form-control" name="catatan_collection" id="catatanCollection" rows="3"
                                                  placeholder="Keterangan potongan, tanggal pengurangan di sistem, dll..." required></textarea>
                                    </div>

                                </div>
                                <div class="card-footer d-flex justify-content-between">
                                    <a href="<?= base_url('retur_penjualan/retur/detail/' . $retur['id_retur']) ?>" class="btn btn-light">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check-circle"></i> Selesaikan Retur Penjualan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- SIDEBAR INFO -->
                        <div class="col-lg-4">
                            <div class="card shadow border-primary" style="border-width:2px !important;">
                                <div class="card-header bg-primary text-white py-2">
                                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Panduan</h3>
                                </div>
                                <div class="card-body small">
                                    <p><i class="fas fa-lightbulb text-warning mr-1"></i> <strong>Panduan Team Collection:</strong></p>
                                    <ol class="pl-3">
                                        <li class="mb-2">Cari faktur customer yang masih outstanding/belum lunas</li>
                                        <li class="mb-2">Masukkan no. faktur yang akan dikurangi nilainya sebesar total retur penjualan</li>
                                        <li class="mb-2">Isi catatan lengkap untuk audit trail</li>
                                        <li class="mb-2">Setelah disimpan, status retur akan menjadi <strong>Selesai</strong></li>
                                    </ol>
                                    <hr>
                                    <div class="font-weight-bold mb-1">Total yang harus dipotongkan:</div>
                                    <div class="text-center py-2">
                                        <span style="font-size:20px; font-weight:700; color:#007bff;">Rp <?= number_format($total_retur, 0, ',', '.') ?></span>
                                    </div>
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
    $('#formCollection').on('submit', function(e) {
        var faktur  = $('#noFakturPotong').val().trim();
        var catatan = $('#catatanCollection').val().trim();
        if (!faktur) { e.preventDefault(); alert('No. faktur yang dipotong wajib diisi.'); return; }
        if (!catatan) { e.preventDefault(); alert('Catatan collection wajib diisi.'); return; }
        if (!confirm('Yakin menyelesaikan retur penjualan ini? Nilai Rp <?= number_format($total_retur, 0, ',', '.') ?> akan dipotongkan pada faktur yang ditentukan.')) {
            e.preventDefault();
        }
    });
});
</script>
