<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-history text-primary mr-2"></i> Riwayat Perakitan & Pembongkaran Bundling
                    </h1>
                    <p class="text-muted mb-0 small">Audit trail lengkap mutasi persediaan perakitan paket dan pembongkaran eceran</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= site_url('logistik/bundling') ?>" class="btn btn-outline-secondary font-weight-bold shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Ke Monitoring Logistik
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Tabs Navigasi -->
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active font-weight-bold px-4 shadow-sm" id="tab-assembly" data-toggle="pill" href="#content-assembly" role="tab">
                        <i class="fas fa-hammer mr-1 text-success"></i> Riwayat Pembuatan Paket (Assembly)
                    </a>
                </li>
                <li class="nav-item ml-2">
                    <a class="nav-link font-weight-bold px-4 shadow-sm" id="tab-disassembly" data-toggle="pill" href="#content-disassembly" role="tab">
                        <i class="fas fa-box-open mr-1 text-warning"></i> Riwayat Pembongkaran (Unbundling)
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                <!-- Tab 1: Perakitan -->
                <div class="tab-pane fade show active" id="content-assembly" role="tabpanel">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                                    <thead style="background: #f1f5f9; color: #334155;">
                                        <tr>
                                            <th class="py-3 px-3">No. Assembly</th>
                                            <th class="py-3">Tanggal</th>
                                            <th class="py-3">Paket Bundling</th>
                                            <th class="py-3 text-center">Qty Dibuat</th>
                                            <th class="py-3">No. Lot</th>
                                            <th class="py-3">Expired Date</th>
                                            <th class="py-3 text-right">HPP / Paket</th>
                                            <th class="py-3">Petugas</th>
                                            <th class="py-3 text-center" style="width: 100px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($assemblies)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-5 text-muted">
                                                    Belum ada riwayat pembuatan paket bundling.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($assemblies as $a): ?>
                                                <tr>
                                                    <td class="px-3 font-weight-bold text-primary">
                                                        <a href="<?= site_url('logistik/bundling/assembly_detail/' . $a['id_assembly']) ?>">
                                                            <?= htmlspecialchars($a['no_assembly']) ?>
                                                        </a>
                                                        <?php if (!empty($a['no_request'])): ?>
                                                            <div class="small text-muted font-weight-normal">Ref: <?= htmlspecialchars($a['no_request']) ?></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($a['tanggal'])) ?></td>
                                                    <td>
                                                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($a['nama_paket']) ?></div>
                                                        <span class="badge badge-light border text-muted"><?= htmlspecialchars($a['kode_paket']) ?></span>
                                                    </td>
                                                    <td class="text-center font-weight-bold text-success">
                                                        +<?= number_format((float)$a['qty_assembly'], 0, ',', '.') ?> <?= htmlspecialchars($a['satuan']) ?>
                                                    </td>
                                                    <td><span class="badge badge-light border font-weight-bold"><?= htmlspecialchars($a['no_lot_paket']) ?></span></td>
                                                    <td><?= $a['expired_date_paket'] ? date('d/m/Y', strtotime($a['expired_date_paket'])) : '-' ?></td>
                                                    <td class="text-right font-weight-bold text-dark">
                                                        Rp <?= number_format((float)$a['hpp_per_paket'], 2, ',', '.') ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($a['user_input']) ?></td>
                                                    <td class="text-center">
                                                        <a href="<?= site_url('logistik/bundling/assembly_detail/' . $a['id_assembly']) ?>" class="btn btn-sm btn-outline-primary shadow-sm" title="Lihat Bukti Transaksi">
                                                            <i class="fas fa-file-invoice"></i> Detail
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

                <!-- Tab 2: Pembongkaran -->
                <div class="tab-pane fade" id="content-disassembly" role="tabpanel">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                                    <thead style="background: #f1f5f9; color: #334155;">
                                        <tr>
                                            <th class="py-3 px-3">No. Disassembly</th>
                                            <th class="py-3">Tanggal</th>
                                            <th class="py-3">Paket yang Dibongkar</th>
                                            <th class="py-3 text-center">Qty Dibongkar</th>
                                            <th class="py-3">Alasan Pembongkaran</th>
                                            <th class="py-3">Petugas</th>
                                            <th class="py-3 text-center" style="width: 100px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($disassemblies)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    Belum ada riwayat pembongkaran paket bundling.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($disassemblies as $d): ?>
                                                <tr>
                                                    <td class="px-3 font-weight-bold text-primary">
                                                        <a href="<?= site_url('logistik/bundling/disassembly_detail/' . $d['id_disassembly']) ?>">
                                                            <?= htmlspecialchars($d['no_disassembly']) ?>
                                                        </a>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
                                                    <td>
                                                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($d['nama_paket']) ?></div>
                                                        <span class="badge badge-light border text-muted"><?= htmlspecialchars($d['kode_paket']) ?></span>
                                                    </td>
                                                    <td class="text-center font-weight-bold text-danger">
                                                        -<?= number_format((float)$d['qty_disassembly'], 0, ',', '.') ?> <?= htmlspecialchars($d['satuan']) ?>
                                                    </td>
                                                    <td class="small text-muted"><?= htmlspecialchars($d['alasan']) ?></td>
                                                    <td><?= htmlspecialchars($d['user_input']) ?></td>
                                                    <td class="text-center">
                                                        <a href="<?= site_url('logistik/bundling/disassembly_detail/' . $d['id_disassembly']) ?>" class="btn btn-sm btn-outline-primary shadow-sm" title="Lihat Bukti Transaksi">
                                                            <i class="fas fa-file-invoice"></i> Detail
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
            </div>
        </div>
    </section>
</div>
