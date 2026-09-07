<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-file-invoice text-warning mr-2"></i> Bukti Pembongkaran Paket #<?= htmlspecialchars($disassembly['no_disassembly']) ?>
                    </h1>
                    <p class="text-muted mb-0 small">Rincian mutasi persediaan disassembly untuk pemenuhan penjualan eceran</p>
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
                            <h5 class="font-weight-bold text-warning mb-0"><?= htmlspecialchars($disassembly['no_disassembly']) ?></h5>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <span class="text-muted small d-block">Tanggal Pembongkaran</span>
                            <strong><?= date('d M Y', strtotime($disassembly['tanggal'])) ?></strong>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <span class="text-muted small d-block">Petugas Logistik</span>
                            <strong><?= htmlspecialchars($disassembly['user_input']) ?></strong>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <span class="text-muted small d-block">Lokasi Gudang</span>
                            <span class="badge badge-primary px-2 py-1"><?= htmlspecialchars($disassembly['nama_gudang'] ?: 'Gdg. Bundling') ?></span>
                        </div>
                    </div>

                    <div class="alert alert-danger border-0 p-3 mt-2 mb-3" style="border-radius: 8px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-danger small font-weight-bold text-uppercase d-block">Paket Bundling yang Dibongkar (OUT)</span>
                                <h4 class="font-weight-bold text-dark m-0"><?= htmlspecialchars($disassembly['nama_paket']) ?></h4>
                                <small class="text-muted">Kode: <?= htmlspecialchars($disassembly['kode_paket']) ?> | Lot: <?= htmlspecialchars($disassembly['no_lot_paket']) ?></small>
                            </div>
                            <div class="text-right">
                                <h3 class="font-weight-bold text-danger mb-0">-<?= number_format((float)$disassembly['qty_disassembly'], 0, ',', '.') ?> <?= htmlspecialchars($disassembly['satuan']) ?></h3>
                                <small class="text-muted font-weight-bold">Total Nilai: Rp <?= number_format((float)$disassembly['total_nilai_hpp'], 2, ',', '.') ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="fas fa-info-circle text-primary mr-1"></i>
                        <strong>Alasan Pembongkaran:</strong> <?= htmlspecialchars($disassembly['alasan']) ?>
                    </div>
                </div>
            </div>

            <!-- Tabel Komponen yang Dikembalikan ke Stok Bebas -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                    <h6 class="font-weight-bold text-dark m-0">
                        <i class="fas fa-undo-alt text-success mr-2"></i> Komponen yang Kembali Menjadi Stok Bebas Eceran (IN)
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
                                    <th class="py-3 text-center text-success font-weight-bold">Qty Masuk (IN)</th>
                                    <th class="py-3 text-right">HPP Satuan</th>
                                    <th class="py-3 text-right">Total Nilai HPP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($disassembly['details'])): ?>
                                    <?php foreach ($disassembly['details'] as $d): ?>
                                        <tr>
                                            <td class="px-3">
                                                <div class="font-weight-bold text-dark"><?= htmlspecialchars($d['nama_barang_komponen']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($d['kode_barang_komponen']) ?></small>
                                            </td>
                                            <td><span class="badge badge-light border font-weight-bold"><?= htmlspecialchars($d['no_lot']) ?></span></td>
                                            <td><?= $d['expired_date'] ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?></td>
                                            <td class="text-center font-weight-bold text-success">
                                                +<?= number_format((float)$d['qty_kembali'], 2, ',', '.') ?> <?= htmlspecialchars($d['satuan']) ?>
                                            </td>
                                            <td class="text-right">Rp <?= number_format((float)$d['hpp_satuan'], 2, ',', '.') ?></td>
                                            <td class="text-right font-weight-bold text-dark">Rp <?= number_format((float)$d['total_hpp'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
