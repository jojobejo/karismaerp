<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-boxes text-primary mr-2"></i> Realisasi Paket Bundling #<?= htmlspecialchars($request['no_request']) ?>
                    </h1>
                    <p class="text-muted mb-0 small">Pengecekan stok komponen, mutasi bahan, dan eksekusi perakitan paket</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= site_url('logistik/bundling') ?>" class="btn btn-outline-secondary font-weight-bold shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Antrean
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php 
                $badgeClass = 'badge-secondary';
                if ($request['status'] == 'MENUNGGU_PROSES') $badgeClass = 'badge-warning text-dark';
                elseif ($request['status'] == 'PROSES_SEBAGIAN') $badgeClass = 'badge-info';
                elseif ($request['status'] == 'SELESAI') $badgeClass = 'badge-success';
                
                $sisa = max(0, (float)$request['qty_request'] - (float)$request['qty_realisasi']);
                $percent = ((float)$request['qty_request'] > 0) ? min(100, round(((float)$request['qty_realisasi'] / (float)$request['qty_request']) * 100)) : 0;
            ?>

            <!-- Ringkasan Paket & Tombol Tindakan -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="text-muted small d-block">Paket Bundling yang Diminta</span>
                                    <h4 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($request['nama_paket']) ?></h4>
                                    <span class="badge badge-light border text-muted"><?= htmlspecialchars($request['kode_paket']) ?></span>
                                </div>
                                <span class="badge <?= $badgeClass ?> px-3 py-2 font-weight-bold" style="font-size: 0.95rem; border-radius: 8px;">
                                    <?= str_replace('_', ' ', $request['status']) ?>
                                </span>
                            </div>
                            <div class="row pt-2 border-top">
                                <div class="col-md-3 col-6 mb-2">
                                    <span class="text-muted small d-block">Tanggal Request</span>
                                    <strong><?= date('d/m/Y', strtotime($request['tanggal_request'])) ?></strong>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <span class="text-muted small d-block">User Purchasing</span>
                                    <strong><?= htmlspecialchars($request['user_request']) ?></strong>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <span class="text-muted small d-block">Gudang Asal Bahan</span>
                                    <strong><?= htmlspecialchars($request['nama_gudang_asal'] ?: 'Gdg. Induk') ?></strong>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <span class="text-muted small d-block">Gudang Perakitan</span>
                                    <strong class="text-primary"><?= htmlspecialchars($request['nama_gudang_tujuan'] ?: 'Gdg. Bundling') ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #fff;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <span class="text-muted small font-weight-bold text-uppercase">Status Pemenuhan</span>
                                <div class="d-flex align-items-baseline mt-1 mb-2">
                                    <h3 class="font-weight-bold text-success mb-0 mr-2"><?= number_format((float)$request['qty_realisasi'], 0, ',', '.') ?></h3>
                                    <span class="text-muted">/ <?= number_format((float)$request['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?></span>
                                </div>
                                <div class="progress mb-2" style="height: 7px;">
                                    <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                                </div>
                                <div class="small text-muted d-flex justify-content-between">
                                    <span>Tercapai: <strong><?= $percent ?>%</strong></span>
                                    <span>Sisa: <strong class="text-danger"><?= number_format($sisa, 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?></strong></span>
                                </div>
                            </div>

                            <div class="pt-3 border-top mt-3">
                                <?php if ($sisa > 0): ?>
                                    <div class="btn-group d-flex" role="group">
                                        <a href="<?= site_url('logistik/bundling/mutasi_bahan/' . $request['id_request']) ?>" class="btn btn-outline-primary font-weight-bold">
                                            <i class="fas fa-dolly mr-1"></i> Mutasi Bahan
                                        </a>
                                        <a href="<?= site_url('logistik/bundling/assembly/' . $request['id_request']) ?>" class="btn btn-success font-weight-bold shadow">
                                            <i class="fas fa-hammer mr-1"></i> Buat Paket
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success m-0 py-2 text-center small font-weight-bold">
                                        <i class="fas fa-check-circle mr-1"></i> Request Ini Telah Terealisasi Penuh
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Kebutuhan Komponen & Kesiapan Stok Fisik -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="font-weight-bold text-dark m-0">
                            <i class="fas fa-clipboard-check text-primary mr-2"></i> Pengecekan Ketersediaan Stok Komponen
                        </h6>
                        <small class="text-muted">Pastikan komponen sudah berada di Gudang Bundling sebelum proses perakitan dilakukan.</small>
                    </div>
                    <?php if ($sisa > 0): ?>
                        <a href="<?= site_url('logistik/bundling/mutasi_bahan/' . $request['id_request']) ?>" class="btn btn-sm btn-outline-primary font-weight-bold">
                            <i class="fas fa-exchange-alt mr-1"></i> Mutasi Bahan dari Gudang Induk
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                            <thead style="background: #f1f5f9; color: #334155;">
                                <tr>
                                    <th class="py-3 px-3">Komponen Barang</th>
                                    <th class="py-3 text-center">Isi / 1 Paket</th>
                                    <th class="py-3 text-center">Total Kebutuhan</th>
                                    <th class="py-3 text-center text-success">Sudah Dirakit</th>
                                    <th class="py-3 text-center text-danger">Sisa Kebutuhan</th>
                                    <th class="py-3 text-center">Stok Gdg. Induk (Bisa Dimutasi)</th>
                                    <th class="py-3 text-center bg-light text-primary font-weight-bold">Stok Gdg. Bundling (Siap Dirakit)</th>
                                    <th class="py-3 text-center">Status Kesiapan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stock_status as $stk): ?>
                                    <?php 
                                        $isReady = ($stk['stok_gudang_bundling'] >= $stk['sisa_kebutuhan']);
                                    ?>
                                    <tr>
                                        <td class="px-3">
                                            <div class="font-weight-bold text-dark"><?= htmlspecialchars($stk['nama_barang']) ?></div>
                                            <small class="text-muted">Kode: <?= htmlspecialchars($stk['kode_barang']) ?></small>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            <?= number_format($stk['qty_per_paket'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            <?= number_format($stk['qty_total_kebutuhan'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                        </td>
                                        <td class="text-center font-weight-bold text-success">
                                            <?= number_format($stk['qty_terpenuhi'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                        </td>
                                        <td class="text-center font-weight-bold text-danger">
                                            <?= number_format($stk['sisa_kebutuhan'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                                                <?= number_format($stk['stok_gudang_induk'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center bg-light font-weight-bold text-primary" style="font-size: 1.05rem;">
                                            <?= number_format($stk['stok_gudang_bundling'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($stk['sisa_kebutuhan'] <= 0): ?>
                                                <span class="badge badge-success px-2 py-1 font-weight-bold"><i class="fas fa-check"></i> Selesai</span>
                                            <?php elseif ($isReady): ?>
                                                <span class="badge badge-success px-2 py-1 font-weight-bold"><i class="fas fa-check-double"></i> Stok Siap</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold">
                                                    <i class="fas fa-exclamation-triangle"></i> Kurang <?= number_format($stk['kekurangan_di_bundling'], 2, ',', '.') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Histori Perakitan yang Telah Dibuat -->
            <?php if (!empty($request['assemblies'])): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                        <h6 class="font-weight-bold text-dark m-0">
                            <i class="fas fa-history text-success mr-2"></i> Riwayat Perakitan yang Telah Dilakukan
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                <thead style="background: #f1f5f9; color: #334155;">
                                    <tr>
                                        <th class="py-3 px-3">No. Perakitan</th>
                                        <th class="py-3">Tanggal</th>
                                        <th class="py-3 text-center">Qty Dibuat</th>
                                        <th class="py-3">No. Lot Paket</th>
                                        <th class="py-3">Expired Date</th>
                                        <th class="py-3 text-right">Nilai HPP</th>
                                        <th class="py-3">Petugas</th>
                                        <th class="py-3 text-center" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($request['assemblies'] as $asm): ?>
                                        <tr>
                                            <td class="px-3 font-weight-bold text-primary"><?= htmlspecialchars($asm['no_assembly']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($asm['tanggal'])) ?></td>
                                            <td class="text-center font-weight-bold text-success">
                                                +<?= number_format((float)$asm['qty_assembly'], 0, ',', '.') ?> <?= htmlspecialchars($asm['satuan']) ?>
                                            </td>
                                            <td><span class="badge badge-light border font-weight-bold"><?= htmlspecialchars($asm['no_lot_paket']) ?></span></td>
                                            <td><?= $asm['expired_date_paket'] ? date('d/m/Y', strtotime($asm['expired_date_paket'])) : '-' ?></td>
                                            <td class="text-right font-weight-bold text-dark">
                                                Rp <?= number_format((float)$asm['hpp_per_paket'], 2, ',', '.') ?>
                                            </td>
                                            <td><?= htmlspecialchars($asm['user_input']) ?></td>
                                            <td class="text-center">
                                                <a href="<?= site_url('logistik/bundling/assembly_detail/' . $asm['id_assembly']) ?>" class="btn btn-sm btn-outline-primary" title="Lihat Bukti Perakitan">
                                                    <i class="fas fa-receipt"></i> Bukti
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
