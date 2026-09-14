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
                        <i class="fas fa-history text-primary mr-2"></i> Riwayat Pembuatan Paket Bundling (Assembly)
                    </h1>
                    <p class="text-muted mb-0 small">Audit trail lengkap mutasi persediaan realisasi perakitan paket bundling</p>
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
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                    <h6 class="font-weight-bold text-dark m-0">
                        <i class="fas fa-hammer text-success mr-2"></i> Daftar Realisasi Pembuatan Paket (Assembly)
                    </h6>
                </div>
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
                                    <th class="py-3">Petugas</th>
                                    <th class="py-3 text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($assemblies)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            Belum ada riwayat perakitan paket bundling yang dilakukan.
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
                                                    <small class="text-muted d-block">Req: <?= htmlspecialchars($a['no_request']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($a['tanggal'])) ?></td>
                                            <td>
                                                <div class="font-weight-bold text-dark"><?= htmlspecialchars($a['nama_paket']) ?></div>
                                                <span class="badge badge-light border text-muted"><?= htmlspecialchars($a['kode_paket']) ?></span>
                                            </td>
                                            <td class="text-center font-weight-bold text-success" style="font-size: 0.95rem;">
                                                +<?= number_format((float)$a['qty_assembly'], 0, ',', '.') ?> <?= htmlspecialchars($a['satuan']) ?>
                                            </td>
                                            <td><span class="badge badge-light border font-weight-bold"><?= htmlspecialchars($a['no_lot_paket']) ?></span></td>
                                            <td><?= $a['expired_date_paket'] ? date('d/m/Y', strtotime($a['expired_date_paket'])) : '-' ?></td>
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
    </section>
</div>
<!-- /.content-wrapper -->
</div>
<!-- /.wrapper -->
