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
                        <i class="fas fa-clipboard-list text-primary mr-2"></i> Request Paket Bundling #<?= htmlspecialchars($request['no_request']) ?>
                    </h1>
                    <p class="text-muted mb-0 small">Detail komposisi, kalkulasi kebutuhan komponen, dan progres realisasi Logistik</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= site_url('purchasing/bundling/request') ?>" class="btn btn-outline-secondary font-weight-bold shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
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
                elseif ($request['status'] == 'BATAL') $badgeClass = 'badge-danger';
                
                $sisa = max(0, (float)$request['qty_request'] - (float)$request['qty_realisasi']);
                $percent = ((float)$request['qty_request'] > 0) ? min(100, round(((float)$request['qty_realisasi'] / (float)$request['qty_request']) * 100)) : 0;
            ?>

            <!-- Kartu Status & Info Utama -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <span class="text-muted small d-block">Nama Paket Bundling</span>
                                    <h4 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($request['nama_paket']) ?></h4>
                                    <span class="badge badge-light border text-muted"><?= htmlspecialchars($request['kode_paket']) ?></span>
                                </div>
                                <div class="col-sm-6 mb-3 text-sm-right">
                                    <span class="text-muted small d-block">Status Request</span>
                                    <span class="badge <?= $badgeClass ?> px-3 py-2 font-weight-bold" style="font-size: 0.95rem; border-radius: 8px;">
                                        <?= str_replace('_', ' ', $request['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row pt-2">
                                <div class="col-md-3 col-6 mb-2">
                                    <span class="text-muted small d-block">Tanggal Request</span>
                                    <strong><?= date('d M Y', strtotime($request['tanggal_request'])) ?></strong>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <span class="text-muted small d-block">Dibuat Oleh</span>
                                    <strong><?= htmlspecialchars($request['user_request']) ?></strong>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <span class="text-muted small d-block">Gudang Asal Bahan</span>
                                    <strong><?= htmlspecialchars($request['nama_gudang_asal'] ?: 'Gdg. Induk') ?></strong>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <span class="text-muted small d-block">Gudang Tujuan Rakit</span>
                                    <strong class="text-primary"><?= htmlspecialchars($request['nama_gudang_tujuan'] ?: 'Gdg. Bundling') ?></strong>
                                </div>
                            </div>
                            <?php if (!empty($request['keterangan'])): ?>
                                <div class="alert alert-light border mt-3 mb-0 small text-muted">
                                    <i class="fas fa-sticky-note mr-1"></i> <strong>Catatan:</strong> <?= htmlspecialchars($request['keterangan']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Kartu Progres Kuantitas -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <span class="text-white-50 small text-uppercase font-weight-bold">Target Pemenuhan Paket</span>
                                <div class="d-flex align-items-baseline mt-2">
                                    <h2 class="font-weight-bold text-white mb-0 mr-2"><?= number_format((float)$request['qty_realisasi'], 0, ',', '.') ?></h2>
                                    <span class="text-white-50">/ <?= number_format((float)$request['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?></span>
                                </div>
                                <div class="progress my-3" style="height: 8px; background: rgba(255,255,255,0.2);">
                                    <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-white-50">
                                    <span>Tercapai: <strong class="text-white"><?= $percent ?>%</strong></span>
                                    <span>Sisa: <strong class="text-warning"><?= number_format($sisa, 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?></strong></span>
                                </div>
                            </div>
                            <div class="pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                                <small class="text-white-50 d-block">
                                    <i class="fas fa-truck-loading mr-1"></i> Dikerjakan oleh Divisi Logistik Gudang
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Estimasi Modal & HPP Paket Purchasing -->
            <?php
                $calcEstHpp1Paket = (float)($request['estimasi_hpp_per_paket'] ?? 0);
                $calcEstModalTotal = (float)($request['estimasi_total_modal'] ?? 0);
                $sumHpp1Paket = 0;
                $sumTotalModal = 0;
                foreach ($stock_status as $stk) {
                    $sumHpp1Paket += (float)($stk['subtotal_hpp_per_paket'] ?? 0);
                    $sumTotalModal += (float)($stk['total_modal_kebutuhan'] ?? 0);
                }
                if ($calcEstHpp1Paket <= 0) $calcEstHpp1Paket = $sumHpp1Paket;
                if ($calcEstModalTotal <= 0) $calcEstModalTotal = $sumTotalModal;
            ?>
            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid #059669 !important;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small font-weight-bold text-uppercase d-block">
                                    <i class="fas fa-tag text-success mr-1"></i> Estimasi Modal / HPP per 1 Paket
                                </span>
                                <h3 class="font-weight-bold text-dark mb-0 mt-1">
                                    Rp <?= number_format($calcEstHpp1Paket, 2, ',', '.') ?>
                                    <small class="text-muted font-weight-normal" style="font-size: 0.9rem;">/ <?= htmlspecialchars($request['satuan']) ?></small>
                                </h3>
                                <small class="text-muted">Total akumulasi biaya komponen untuk merakit 1 paket</small>
                            </div>
                            <div class="rounded-circle bg-light p-3 text-success">
                                <i class="fas fa-calculator fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid #2563eb !important;">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small font-weight-bold text-uppercase d-block">
                                    <i class="fas fa-coins text-primary mr-1"></i> Total Estimasi Modal Request
                                </span>
                                <h3 class="font-weight-bold text-primary mb-0 mt-1">
                                    Rp <?= number_format($calcEstModalTotal, 2, ',', '.') ?>
                                </h3>
                                <small class="text-muted">Modal pengadaan untuk target <?= number_format((float)$request['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?> paket</small>
                            </div>
                            <div class="rounded-circle bg-light p-3 text-primary">
                                <i class="fas fa-wallet fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Komposisi & Total Kebutuhan Komponen beserta HPP Modal -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="font-weight-bold text-dark m-0">
                        <i class="fas fa-cubes text-primary mr-2"></i> Rincian Komposisi, Kebutuhan Bahan & Estimasi Modal HPP
                    </h6>
                    <span class="badge badge-light border text-muted px-2 py-1 small mt-2 mt-sm-0">
                        <i class="fas fa-info-circle mr-1"></i> HPP dihitung dari riwayat pembelian LPB / PO terakhir
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.90rem;">
                            <thead style="background: #f1f5f9; color: #334155;">
                                <tr>
                                    <th class="py-3 px-3">Komponen Barang</th>
                                    <th class="py-3 text-center">Isi / 1 Paket</th>
                                    <th class="py-3 text-right">HPP Satuan</th>
                                    <th class="py-3 text-right bg-light text-success font-weight-bold">Modal / 1 Paket</th>
                                    <th class="py-3 text-center bg-light text-primary font-weight-bold">Total Qty Kebutuhan</th>
                                    <th class="py-3 text-right bg-light text-primary font-weight-bold">Total Modal Kebutuhan</th>
                                    <th class="py-3 text-center text-success font-weight-bold">Terpakai</th>
                                    <th class="py-3 text-center text-danger font-weight-bold">Sisa Butuh</th>
                                    <th class="py-3 text-center">Stok Induk</th>
                                    <th class="py-3 text-center">Stok Bundling</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stock_status as $stk): ?>
                                    <tr>
                                        <td class="px-3">
                                            <div class="font-weight-bold text-dark"><?= htmlspecialchars($stk['nama_barang']) ?></div>
                                            <small class="text-muted">Kode: <?= htmlspecialchars($stk['kode_barang']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="font-weight-bold text-dark">
                                                <?= number_format($stk['qty_per_paket'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                            </div>
                                            <?php if (!empty($stk['is_innerbox'])): ?>
                                                <span class="badge badge-warning text-dark px-2 py-1 mt-1 font-weight-bold" style="font-size: 0.76rem;" title="Komposisi Innerbox">
                                                    <i class="fas fa-box mr-1"></i> <?= number_format($stk['qty_innerbox'], 0) ?> Box (@ <?= number_format($stk['isi_per_innerbox'], 0) ?> <?= htmlspecialchars($stk['satuan']) ?>)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right text-muted">
                                            Rp <?= number_format((float)($stk['hpp_satuan'] ?? 0), 2, ',', '.') ?>
                                        </td>
                                        <td class="text-right font-weight-bold text-success bg-light">
                                            Rp <?= number_format((float)($stk['subtotal_hpp_per_paket'] ?? 0), 2, ',', '.') ?>
                                        </td>
                                        <td class="text-center bg-light text-primary">
                                            <div class="font-weight-bold" style="font-size: 1.05rem;">
                                                <?= number_format($stk['qty_total_kebutuhan'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                            </div>
                                            <?php if (!empty($stk['is_innerbox'])): ?>
                                                <span class="badge badge-warning text-dark px-2 py-1 mt-1 font-weight-bold" style="font-size: 0.76rem;" title="Total Kebutuhan Kemasan Innerbox">
                                                    <i class="fas fa-boxes mr-1"></i> <?= number_format($stk['total_innerbox_kebutuhan'], 0, ',', '.') ?> Box
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right font-weight-bold bg-light text-primary">
                                            Rp <?= number_format((float)($stk['total_modal_kebutuhan'] ?? 0), 2, ',', '.') ?>
                                        </td>
                                        <td class="text-center font-weight-bold text-success">
                                            <?= number_format($stk['qty_terpenuhi'], 2, ',', '.') ?>
                                        </td>
                                        <td class="text-center font-weight-bold text-danger">
                                            <?= number_format($stk['sisa_kebutuhan'], 2, ',', '.') ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                                                <?= number_format($stk['stok_gudang_induk'], 2, ',', '.') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info font-weight-bold px-2 py-1">
                                                <?= number_format($stk['stok_gudang_bundling'], 2, ',', '.') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot style="background: #f8fafc; font-size: 0.92rem;">
                                <tr>
                                    <th class="py-3 px-3 font-weight-bold text-dark" colspan="3">Total Estimasi Modal Paket:</th>
                                    <th class="py-3 text-right text-success font-weight-bold" style="font-size: 1rem;">
                                        Rp <?= number_format($calcEstHpp1Paket, 2, ',', '.') ?>
                                    </th>
                                    <th class="py-3 text-center font-weight-bold text-primary">
                                        <?= number_format((float)$request['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?>
                                    </th>
                                    <th class="py-3 text-right text-primary font-weight-bold" style="font-size: 1.05rem;">
                                        Rp <?= number_format($calcEstModalTotal, 2, ',', '.') ?>
                                    </th>
                                    <th colspan="4"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Histori Realisasi Perakitan (Jika Ada) -->
            <?php if (!empty($request['assemblies'])): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                        <h6 class="font-weight-bold text-dark m-0">
                            <i class="fas fa-history text-success mr-2"></i> Riwayat Realisasi Pembuatan oleh Logistik
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                <thead style="background: #f1f5f9; color: #334155;">
                                    <tr>
                                        <th class="py-3 px-3">No. Perakitan (Assembly)</th>
                                        <th class="py-3">Tanggal</th>
                                        <th class="py-3 text-center">Qty Dibuat</th>
                                        <th class="py-3">No. Lot Paket</th>
                                        <th class="py-3">Expired Date</th>
                                        <th class="py-3 text-right">Nilai HPP / Paket</th>
                                        <th class="py-3 text-right">Total Nilai HPP</th>
                                        <th class="py-3">Petugas Logistik</th>
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
                                            <td class="text-right font-weight-bold text-primary">
                                                Rp <?= number_format((float)$asm['total_nilai_hpp'], 2, ',', '.') ?>
                                            </td>
                                            <td><?= htmlspecialchars($asm['user_input']) ?></td>
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
<!-- /.content-wrapper -->
</div>
<!-- /.wrapper -->
