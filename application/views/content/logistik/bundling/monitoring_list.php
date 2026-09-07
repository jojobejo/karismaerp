<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.6rem;">
                        <i class="fas fa-warehouse text-primary mr-2"></i> Monitoring & Realisasi Paket Bundling
                    </h1>
                    <p class="text-muted mb-0 small">Pelaksanaan mutasi bahan, perakitan paket, dan pembongkaran paket untuk penjualan eceran</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= site_url('logistik/bundling/history') ?>" class="btn btn-outline-secondary font-weight-bold shadow-sm mr-2">
                        <i class="fas fa-history mr-1"></i> Riwayat Transaksi
                    </a>
                    <a href="<?= site_url('logistik/bundling/disassembly') ?>" class="btn btn-warning font-weight-bold shadow-sm px-3 text-dark">
                        <i class="fas fa-box-open mr-1"></i> Pembongkaran Paket (Eceran)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Bar -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form method="get" action="<?= site_url('logistik/bundling') ?>" class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-muted mb-1">Status Request</label>
                            <select name="status" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()">
                                <option value="SEMUA" <?= ($filters['status'] == 'SEMUA') ? 'selected' : '' ?>>Semua Status</option>
                                <option value="MENUNGGU_PROSES" <?= ($filters['status'] == 'MENUNGGU_PROSES') ? 'selected' : '' ?>>Menunggu Proses</option>
                                <option value="PROSES_SEBAGIAN" <?= ($filters['status'] == 'PROSES_SEBAGIAN') ? 'selected' : '' ?>>Proses Sebagian (Parsial)</option>
                                <option value="SELESAI" <?= ($filters['status'] == 'SELESAI') ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-muted mb-1">Cari No Request / Nama Paket</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Ketik nomor request / nama paket..." value="<?= htmlspecialchars($filters['search']) ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 text-right pt-2">
                            <?php if (!empty($filters['search']) || $filters['status'] !== 'SEMUA'): ?>
                                <a href="<?= site_url('logistik/bundling') ?>" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-times-circle mr-1"></i> Reset Filter
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Monitoring Logistik -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                            <thead style="background: #f1f5f9; color: #334155;">
                                <tr>
                                    <th class="py-3 px-3">No. Request</th>
                                    <th class="py-3">Tanggal</th>
                                    <th class="py-3">Nama Paket Bundling</th>
                                    <th class="py-3">Gudang Tujuan</th>
                                    <th class="py-3 text-center">Permintaan</th>
                                    <th class="py-3 text-center">Realisasi</th>
                                    <th class="py-3 text-center">Sisa</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-center" style="width: 170px;">Tindakan Logistik</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-box fa-3x mb-3 text-black-50 d-block"></i>
                                            Tidak ada antrean request paket bundling saat ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $r): ?>
                                        <?php 
                                            if ($r['status'] == 'BATAL') continue;
                                            
                                            $badgeClass = 'badge-secondary';
                                            if ($r['status'] == 'MENUNGGU_PROSES') $badgeClass = 'badge-warning text-dark';
                                            elseif ($r['status'] == 'PROSES_SEBAGIAN') $badgeClass = 'badge-info';
                                            elseif ($r['status'] == 'SELESAI') $badgeClass = 'badge-success';
                                            
                                            $sisa = max(0, (float)$r['qty_request'] - (float)$r['qty_realisasi']);
                                            $percent = ((float)$r['qty_request'] > 0) ? min(100, round(((float)$r['qty_realisasi'] / (float)$r['qty_request']) * 100)) : 0;
                                        ?>
                                        <tr>
                                            <td class="px-3 font-weight-bold text-primary">
                                                <a href="<?= site_url('logistik/bundling/detail/' . $r['id_request']) ?>">
                                                    <?= htmlspecialchars($r['no_request']) ?>
                                                </a>
                                                <div class="small text-muted font-weight-normal">Dari: Purchasing (<?= htmlspecialchars($r['user_request']) ?>)</div>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($r['tanggal_request'])) ?></td>
                                            <td>
                                                <div class="font-weight-bold text-dark"><?= htmlspecialchars($r['nama_paket']) ?></div>
                                                <span class="badge badge-light border text-muted"><?= htmlspecialchars($r['kode_paket']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light border text-dark font-weight-bold">
                                                    <?= htmlspecialchars($r['nama_gudang_tujuan'] ?: 'Gdg. Bundling') ?>
                                                </span>
                                            </td>
                                            <td class="text-center font-weight-bold text-dark">
                                                <?= number_format((float)$r['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($r['satuan']) ?>
                                            </td>
                                            <td class="text-center font-weight-bold text-success">
                                                <?= number_format((float)$r['qty_realisasi'], 0, ',', '.') ?> <?= htmlspecialchars($r['satuan']) ?>
                                                <div class="progress mt-1" style="height: 5px;">
                                                    <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                                                </div>
                                            </td>
                                            <td class="text-center font-weight-bold text-danger">
                                                <?= number_format($sisa, 0, ',', '.') ?> <?= htmlspecialchars($r['satuan']) ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $badgeClass ?> px-2 py-1 font-weight-bold" style="font-size: 0.8rem; border-radius: 6px;">
                                                    <?= str_replace('_', ' ', $r['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= site_url('logistik/bundling/detail/' . $r['id_request']) ?>" class="btn btn-sm btn-primary shadow-sm font-weight-bold px-3">
                                                    <i class="fas fa-cog mr-1"></i> Proses Realisasi
                                                </a>
                                            </td>
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
