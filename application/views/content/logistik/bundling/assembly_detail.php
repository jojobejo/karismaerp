<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <!-- Navbar -->
    <?php $this->load->view('partial/main/navbar') ?>
    <!-- Main Sidebar Container -->
    <?php $this->load->view('partial/main/sidebar') ?>

<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-file-invoice text-success mr-2"></i> Bukti Perakitan Paket #<?= htmlspecialchars($assembly['no_assembly']) ?>
                    </h1>
                    <p class="text-muted mb-0 small">Rincian mutasi stok persediaan assembly dan perhitungan HPP paket</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= site_url('logistik/bundling/history') ?>" class="btn btn-outline-secondary font-weight-bold shadow-sm mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Riwayat
                    </a>
                    <button type="button" class="btn btn-outline-primary font-weight-bold shadow-sm" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Cetak Bukti
                    </button>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <span class="text-muted small d-block">Nomor Transaksi</span>
                            <h5 class="font-weight-bold text-primary mb-0"><?= htmlspecialchars($assembly['no_assembly']) ?></h5>
                            <?php if (!empty($assembly['no_request'])): ?>
                                <small class="text-muted">Ref Request: <strong><?= htmlspecialchars($assembly['no_request']) ?></strong></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <span class="text-muted small d-block">Tanggal Transaksi</span>
                            <strong><?= date('d M Y', strtotime($assembly['tanggal'])) ?></strong>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <span class="text-muted small d-block">Petugas Logistik</span>
                            <strong><?= htmlspecialchars($assembly['user_input']) ?></strong>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <span class="text-muted small d-block">Lokasi Gudang</span>
                            <span class="badge badge-primary px-2 py-1"><?= htmlspecialchars($assembly['nama_gudang'] ?: 'Gdg. Bundling') ?></span>
                        </div>
                    </div>


                    <div class="alert alert-success border-0 p-3 mt-2 mb-0" style="border-radius: 8px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-success small font-weight-bold text-uppercase d-block">Produk Paket yang Berhasil Dibuat</span>
                                <h4 class="font-weight-bold text-dark m-0"><?= htmlspecialchars($assembly['nama_paket']) ?></h4>
                                <small class="text-muted">Kode: <?= htmlspecialchars($assembly['kode_paket']) ?> | Lot: <?= htmlspecialchars($assembly['no_lot_paket']) ?> | Exp: <?= $assembly['expired_date_paket'] ?: '-' ?></small>
                            </div>
                            <div class="text-right">
                                <h3 class="font-weight-bold text-success mb-0">+<?= number_format((float)$assembly['qty_assembly'], 0, ',', '.') ?> <?= htmlspecialchars($assembly['satuan']) ?></h3>
                                <small class="text-muted font-weight-bold">HPP: Rp <?= number_format((float)$assembly['hpp_per_paket'], 2, ',', '.') ?> / <?= htmlspecialchars($assembly['satuan']) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Rincian Komponen Terpakai -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                    <h6 class="font-weight-bold text-dark m-0">
                        <i class="fas fa-cubes text-danger mr-2"></i> Komponen yang Dipotong dari Stok (OUT)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                            <thead style="background: #f1f5f9; color: #334155;">
                                <tr>
                                    <th class="py-3 px-3">Kode & Nama Komponen</th>
                                    <th class="py-3">No. Lot</th>
                                    <th class="py-3">Expired Date</th>
                                    <th class="py-3 text-center text-danger font-weight-bold">Qty Keluar (OUT)</th>
                                    <th class="py-3 text-right">HPP Satuan</th>
                                    <th class="py-3 text-right">Total Nilai HPP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($assembly['details'])): ?>
                                    <?php foreach ($assembly['details'] as $d): ?>
                                        <tr>
                                            <td class="px-3">
                                                <div class="font-weight-bold text-dark"><?= htmlspecialchars($d['nama_barang_komponen']) ?></div>
                                                <div class="d-flex align-items-center flex-wrap mt-1">
                                                    <small class="text-muted mr-2"><?= htmlspecialchars($d['kode_barang_komponen']) ?></small>
                                                    <?php if (!empty($d['is_innerbox'])): ?>
                                                        <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 mr-1" title="Komponen dikemas dalam Innerbox">
                                                            <i class="fas fa-box mr-1"></i> Kemasan Innerbox (@ <?= number_format((float)$d['isi_per_innerbox'], 0) ?> <?= htmlspecialchars($d['satuan']) ?>)
                                                        </span>
                                                        <?php if (!empty($d['total_innerbox_kebutuhan']) && (float)$d['total_innerbox_kebutuhan'] > 0): ?>
                                                            <span class="badge badge-info text-white font-weight-bold px-2 py-1">
                                                                Total: <?= number_format((float)$d['total_innerbox_kebutuhan'], 0) ?> Box
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><span class="badge badge-light border font-weight-bold"><?= htmlspecialchars($d['no_lot']) ?></span></td>
                                            <td><?= $d['expired_date'] ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?></td>
                                            <td class="text-center font-weight-bold text-danger">
                                                -<?= number_format((float)$d['qty_digunakan'], 2, ',', '.') ?> <?= htmlspecialchars($d['satuan']) ?>
                                            </td>
                                            <td class="text-right">Rp <?= number_format((float)$d['hpp_satuan'], 2, ',', '.') ?></td>
                                            <td class="text-right font-weight-bold text-dark">Rp <?= number_format((float)$d['total_hpp'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot style="background: #f8fafc;">
                                <tr>
                                    <td colspan="5" class="text-right font-weight-bold py-3">Total Akumulasi HPP Paket:</td>
                                    <td class="text-right font-weight-bold text-success py-3" style="font-size: 1.05rem;">
                                        Rp <?= number_format((float)$assembly['total_nilai_hpp'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- /.content-wrapper -->
</div>
<!-- /.wrapper -->
