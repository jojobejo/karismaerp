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

            <!-- Kartu Estimasi Modal & HPP Paket Purchasing (Komprehensif: Bahan Baku LIFO + Kemasan & Printilan) -->
            <!-- Kartu Estimasi Modal & HPP Paket Purchasing (Komprehensif: Bahan Baku LIFO + Kemasan & Printilan Dinamis) -->
            <?php
                $estHppBahan = (float)($request['estimasi_hpp_bahan_per_paket'] ?? 0);
                $kemasanItems = !empty($request['kemasan_items']) ? $request['kemasan_items'] : [];
                $totalKemasanPerPaket = (float)($request['total_biaya_kemasan_per_paket'] ?? 0);
                $qtyReq = (float)$request['qty_request'];
                $totalKemasanKeseluruhan = (float)($request['total_biaya_kemasan_keseluruhan'] ?? ($totalKemasanPerPaket * $qtyReq));

                $totalHpp1Paket = (float)($request['estimasi_hpp_per_paket'] ?? ($estHppBahan + $totalKemasanPerPaket));
                $totalModalRequest = (float)($request['estimasi_total_modal'] ?? ($totalHpp1Paket * $qtyReq));
            ?>
            <div class="row mb-4">
                <!-- 1. HPP Bahan Baku LIFO -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 5px solid #f59e0b !important; background: #fff;">
                        <div class="card-body p-3">
                            <span class="text-muted small font-weight-bold text-uppercase d-block">
                                <i class="fas fa-boxes text-warning mr-1"></i> HPP Bahan Baku (LIFO)
                            </span>
                            <h4 class="font-weight-bold text-dark mb-0 mt-2">
                                Rp <?= number_format($estHppBahan, 2, ',', '.') ?>
                                <small class="text-muted font-weight-normal" style="font-size: 0.8rem;">/ <?= htmlspecialchars($request['satuan']) ?></small>
                            </h4>
                            <small class="text-muted d-block mt-1">Komponen isi paket dari pembelian terakhir</small>
                        </div>
                    </div>
                </div>

                <!-- 2. Biaya Kemasan & Printilan -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 5px solid #8b5cf6 !important; background: #fff;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="text-muted small font-weight-bold text-uppercase d-block">
                                    <i class="fas fa-box-open text-purple mr-1" style="color: #8b5cf6;"></i> Biaya Kemasan & Printilan
                                </span>
                                <?php if ($totalKemasanPerPaket > 0): ?>
                                    <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold" data-toggle="modal" data-target="#modalEditPackagingCost" title="Ubah Biaya Kemasan & Printilan">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-xs btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalEditPackagingCost" title="Input Biaya Kemasan & Printilan (Opsional)">
                                        <i class="fas fa-plus mr-1"></i> Input Biaya
                                    </button>
                                <?php endif; ?>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-0 mt-2" style="color: #6d28d9 !important;">
                                Rp <?= number_format($totalKemasanPerPaket, 2, ',', '.') ?>
                                <small class="text-muted font-weight-normal" style="font-size: 0.8rem;">/ <?= htmlspecialchars($request['satuan']) ?></small>
                            </h4>
                            <small class="text-muted d-block mt-1"><?= count($kemasanItems) ?> jenis kemasan / printilan</small>
                        </div>
                    </div>
                </div>

                <!-- 3. Total HPP Lengkap per 1 Paket -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 5px solid #059669 !important; background: #f0fdf4;">
                        <div class="card-body p-3">
                            <span class="text-muted small font-weight-bold text-uppercase d-block text-success">
                                <i class="fas fa-calculator text-success mr-1"></i> Estimasi Modal / HPP 1 Paket
                            </span>
                            <h4 class="font-weight-bold text-success mb-0 mt-2">
                                Rp <?= number_format($totalHpp1Paket, 2, ',', '.') ?>
                                <small class="text-muted font-weight-normal" style="font-size: 0.8rem;">/ <?= htmlspecialchars($request['satuan']) ?></small>
                            </h4>
                            <small class="text-muted d-block mt-1">HPP Bahan Baku + Biaya Kemasan Lengkap</small>
                        </div>
                    </div>
                </div>

                <!-- 4. Total Estimasi Modal Seluruh Request -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-left: 5px solid #2563eb !important; background: #eff6ff;">
                        <div class="card-body p-3">
                            <span class="text-muted small font-weight-bold text-uppercase d-block text-primary">
                                <i class="fas fa-wallet text-primary mr-1"></i> Total Modal Request
                            </span>
                            <h4 class="font-weight-bold text-primary mb-0 mt-2">
                                Rp <?= number_format($totalModalRequest, 2, ',', '.') ?>
                            </h4>
                            <small class="text-muted d-block mt-1">Untuk target <?= number_format($qtyReq, 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?> paket</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Rincian Biaya Kemasan & Printilan (Daftar Dinamis Sesuai Kebutuhan) -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff; border: 1px solid #e2e8f0;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                        <div class="d-flex align-items-center">
                            <span class="badge badge-light border text-dark p-2 mr-2" style="font-size: 0.95rem; border-radius: 8px;">
                                <i class="fas fa-pallet text-primary mr-1"></i> <strong>Rincian Modal Kemasan & Printilan per 1 Paket</strong>
                            </span>
                            <small class="text-muted">(Dapat ditambah & disesuaikan tanpa batas sesuai kebutuhan fisik paket)</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalEditPackagingCost">
                            <i class="fas fa-pallet mr-1"></i> Input / Ubah Biaya Kemasan & Printilan
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-2" style="border-radius: 8px; overflow: hidden;">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 45%;">Nama Kemasan / Printilan</th>
                                    <th style="width: 25%;" class="text-right">Biaya per 1 Paket</th>
                                    <th style="width: 25%;" class="text-right">Total Kebutuhan (<?= number_format($qtyReq, 0, ',', '.') ?> Paket)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $noK = 1;
                                    $hasValidItem = false;
                                    foreach ($kemasanItems as $kItem): 
                                        $nomItem = (float)($kItem['nominal'] ?? 0);
                                        $namaItem = trim($kItem['nama'] ?? '');
                                        if ($namaItem === '' && $nomItem <= 0) continue;
                                        $hasValidItem = true;
                                        $totItem = $nomItem * $qtyReq;
                                ?>
                                    <tr>
                                        <td class="text-center font-weight-bold text-muted"><?= $noK++ ?></td>
                                        <td>
                                            <i class="fas fa-box text-secondary mr-2"></i>
                                            <span class="font-weight-bold text-dark"><?= htmlspecialchars($namaItem ?: 'Biaya Kemasan') ?></span>
                                        </td>
                                        <td class="text-right font-weight-bold text-dark">
                                            Rp <?= number_format($nomItem, 2, ',', '.') ?>
                                        </td>
                                        <td class="text-right font-weight-bold" style="color: #6d28d9;">
                                            Rp <?= number_format($totItem, 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (!$hasValidItem): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-box-open fa-2x mb-2 text-secondary d-block"></i>
                                            Belum ada rincian biaya kemasan & printilan untuk request ini.<br>
                                            <button type="button" class="btn btn-sm btn-primary mt-2 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalEditPackagingCost">
                                                <i class="fas fa-plus mr-1"></i> Input Biaya Kemasan & Printilan (Opsional)
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="2" class="text-right text-uppercase small text-muted">Total Kemasan & Printilan:</td>
                                    <td class="text-right font-weight-bold" style="color: #6d28d9; font-size: 1rem;">
                                        Rp <?= number_format($totalKemasanPerPaket, 2, ',', '.') ?> <small class="text-muted font-weight-normal">/ paket</small>
                                    </td>
                                    <td class="text-right font-weight-bold text-primary" style="font-size: 1rem;">
                                        Rp <?= number_format($totalKemasanKeseluruhan, 2, ',', '.') ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Banner Penjelasan Hierarki Kemasan & Struktur Innerbox Multi-Item -->
            <?php if (!empty($request['is_innerbox'])): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #f0f7ff 0%, #f4fbf7 100%); border-left: 5px solid #2563eb !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px; font-size: 1.25rem;">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Skema & Hierarki Kemasan: Paket Innerbox Multi-Item</h5>
                                    <small class="text-muted">Struktur perakitan fisik kemasan bertingkat (Master Box $\rightarrow$ Innerbox $\rightarrow$ Komponen Campuran)</small>
                                </div>
                            </div>
                            <span class="badge badge-primary px-3 py-2 font-weight-bold mt-2 mt-sm-0" style="font-size: 0.88rem; border-radius: 8px;">
                                <i class="fas fa-layer-group mr-1"></i> 1 <?= htmlspecialchars($request['satuan']) ?> Master Box = <?= number_format((float)$request['jumlah_innerbox'], 0) ?> <?= htmlspecialchars($request['satuan_innerbox'] ?: 'Innerbox') ?>
                            </span>
                        </div>

                        <!-- 3 Kotak Alur Fisik -->
                        <div class="row align-items-stretch">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="p-3 bg-white rounded shadow-sm h-100 border">
                                    <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold mb-2">
                                        <i class="fas fa-box-open mr-1"></i> Kardus Kecil (1 Innerbox)
                                    </span>
                                    <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                        Isi Campuran dalam 1 Kardus Kecil:
                                    </div>
                                    <ul class="mb-0 pl-3 mt-2 small font-weight-bold text-secondary">
                                        <?php foreach ($stock_status as $itemInbox): ?>
                                            <li class="mb-1">
                                                <?= htmlspecialchars($itemInbox['nama_barang']) ?>: 
                                                <span class="text-primary"><?= number_format($itemInbox['isi_per_innerbox'], 0) ?> <?= htmlspecialchars($itemInbox['satuan']) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="p-3 bg-white rounded shadow-sm h-100 border">
                                    <span class="badge badge-info px-2 py-1 font-weight-bold mb-2">
                                        <i class="fas fa-box mr-1"></i> Kardus Luar (Master Box)
                                    </span>
                                    <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                        1 Kardus Luar (Master Box) Memuat:
                                    </div>
                                    <div class="mt-2">
                                        <h4 class="text-info font-weight-bold mb-0">
                                            <?= number_format((float)$request['jumlah_innerbox'], 0) ?> <small style="font-size: 0.9rem;">Kardus Kecil (Innerbox)</small>
                                        </h4>
                                        <small class="text-muted d-block mt-1">
                                            Total bahan di dalam 1 kardus luar:
                                            <?php 
                                                $compSum = [];
                                                foreach ($stock_status as $it) {
                                                    $compSum[] = number_format($it['qty_per_paket'], 0) . ' ' . $it['satuan'] . ' ' . $it['nama_barang'];
                                                }
                                                echo htmlspecialchars(implode(', ', $compSum));
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded shadow-sm h-100 border">
                                    <span class="badge badge-success px-2 py-1 font-weight-bold mb-2">
                                        <i class="fas fa-bullseye mr-1"></i> Target Total Perakitan
                                    </span>
                                    <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                        Total Sasaran Permintaan:
                                    </div>
                                    <div class="mt-2">
                                        <h4 class="text-success font-weight-bold mb-0">
                                            <?= number_format((float)$request['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?> <small class="text-muted" style="font-size: 0.85rem;">Master Box</small>
                                        </h4>
                                        <div class="small text-muted mt-1 font-weight-bold">
                                            Setara total: <span class="text-dark"><?= number_format((float)($request['total_innerbox'] ?: ((float)$request['qty_request'] * (float)$request['jumlah_innerbox'])), 0, ',', '.') ?> Kardus Kecil (Innerbox)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabel Komposisi & Total Kebutuhan Komponen beserta HPP Modal -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="font-weight-bold text-dark m-0">
                        <i class="fas fa-cubes text-primary mr-2"></i> Rincian Komposisi, Kebutuhan Bahan & Estimasi Modal HPP
                    </h6>
                    <span class="badge badge-primary px-2 py-1 small mt-2 mt-sm-0 font-weight-bold">
                        <i class="fas fa-tag mr-1"></i> Estimasi Modal: Metode LIFO (Barang Terakhir Masuk/Dibeli)
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" style="font-size: 0.90rem; width: 100%;">
                            <thead style="background: #f1f5f9; color: #334155;">
                                <tr>
                                    <th class="py-3 px-3">Komponen Barang</th>
                                    <?php if (!empty($request['is_innerbox'])): ?>
                                        <th class="py-3 text-center bg-white text-dark font-weight-bold" style="border-left: 2px solid #e2e8f0; border-right: 2px solid #e2e8f0;">
                                            <span class="badge badge-warning text-dark d-block mb-1" style="font-size: 0.72rem;">KARDUS KECIL</span>
                                            Isi / 1 Innerbox
                                        </th>
                                        <th class="py-3 text-center">
                                            <span class="badge badge-info d-block mb-1" style="font-size: 0.72rem;">KARDUS LUAR</span>
                                            Isi / 1 Master Box
                                        </th>
                                    <?php else: ?>
                                        <th class="py-3 text-center">Isi / 1 Paket</th>
                                    <?php endif; ?>
                                    <th class="py-3 text-right">HPP Satuan (LIFO)</th>
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
                                        <?php if (!empty($request['is_innerbox'])): ?>
                                            <td class="text-center font-weight-bold bg-white" style="border-left: 2px solid #e2e8f0; border-right: 2px solid #e2e8f0;">
                                                <div class="text-primary" style="font-size: 1.05rem;">
                                                    <?= number_format($stk['isi_per_innerbox'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                                </div>
                                                <small class="text-muted font-weight-normal">per 1 innerbox</small>
                                            </td>
                                            <td class="text-center font-weight-bold">
                                                <div class="text-dark">
                                                    <?= number_format($stk['qty_per_paket'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                                </div>
                                                <small class="text-muted font-weight-normal"><?= number_format((float)$request['jumlah_innerbox'], 0) ?> innerbox × <?= number_format($stk['isi_per_innerbox'], 0) ?></small>
                                            </td>
                                        <?php else: ?>
                                            <td class="text-center font-weight-bold">
                                                <div class="font-weight-bold text-dark">
                                                    <?= number_format($stk['qty_per_paket'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                        <td class="text-right">
                                            <div class="font-weight-bold text-dark">
                                                Rp <?= number_format((float)($stk['hpp_satuan'] ?? 0), 2, ',', '.') ?>
                                            </div>
                                            <span class="badge badge-light border text-primary font-weight-bold" style="font-size: 0.68rem;" title="Dihitung dari alokasi pembelian terakhir (LIFO)">LIFO</span>
                                        </td>
                                        <td class="text-right font-weight-bold text-success bg-light">
                                            Rp <?= number_format((float)($stk['subtotal_hpp_per_paket'] ?? 0), 2, ',', '.') ?>
                                        </td>
                                        <td class="text-center bg-light text-primary">
                                            <div class="font-weight-bold" style="font-size: 1.05rem;">
                                                <?= number_format($stk['qty_total_kebutuhan'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                            </div>
                                            <?php if (!empty($stk['is_innerbox'])): ?>
                                                <small class="text-muted font-weight-normal d-block">
                                                    (<?= number_format((float)$stk['total_innerbox_kebutuhan'], 0, ',', '.') ?> Innerbox)
                                                </small>
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
                            <?php $colSpanFooter = !empty($request['is_innerbox']) ? 4 : 3; ?>
                            <tfoot style="background: #f8fafc; font-size: 0.90rem;">
                                <tr>
                                    <th class="py-2 px-3 text-muted" colspan="<?= $colSpanFooter ?>">1. Subtotal HPP Bahan Baku (LIFO):</th>
                                    <th class="py-2 text-right text-dark font-weight-bold">
                                        Rp <?= number_format($estHppBahan, 2, ',', '.') ?>
                                    </th>
                                    <th class="py-2 text-center text-muted">
                                        <?= number_format((float)$request['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?>
                                    </th>
                                    <th class="py-2 text-right text-muted font-weight-bold">
                                        Rp <?= number_format($estHppBahan * $qtyReq, 2, ',', '.') ?>
                                    </th>
                                    <th colspan="4"></th>
                                </tr>
                                <tr>
                                    <th class="py-2 px-3 text-muted" colspan="<?= $colSpanFooter ?>">2. Biaya Kemasan & Printilan (Inner, Outer, Stiker Hologram):</th>
                                    <th class="py-2 text-right font-weight-bold" style="color: #6d28d9;">
                                        + Rp <?= number_format($totalKemasanPerPaket, 2, ',', '.') ?>
                                    </th>
                                    <th class="py-2 text-center text-muted">
                                        <?= number_format((float)$request['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?>
                                    </th>
                                    <th class="py-2 text-right font-weight-bold" style="color: #6d28d9;">
                                        + Rp <?= number_format($totalKemasanKeseluruhan, 2, ',', '.') ?>
                                    </th>
                                    <th colspan="4"></th>
                                </tr>
                                <tr style="background: #e6f4ea; border-top: 2px solid #059669;">
                                    <th class="py-3 px-3 font-weight-bold text-success" colspan="<?= $colSpanFooter ?>" style="font-size: 1rem;">
                                        <i class="fas fa-check-circle mr-1"></i> Total Estimasi Modal / HPP per 1 Paket:
                                    </th>
                                    <th class="py-3 text-right text-success font-weight-bold" style="font-size: 1.1rem;">
                                        Rp <?= number_format($totalHpp1Paket, 2, ',', '.') ?>
                                    </th>
                                    <th class="py-3 text-center font-weight-bold text-primary" style="font-size: 1rem;">
                                        <?= number_format((float)$request['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?>
                                    </th>
                                    <th class="py-3 text-right text-primary font-weight-bold" style="font-size: 1.15rem;">
                                        Rp <?= number_format($totalModalRequest, 2, ',', '.') ?>
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

<!-- Modal Edit Biaya Kemasan & Printilan -->
<!-- Modal Edit Biaya Kemasan & Printilan (Repeater Dinamis) -->
<div class="modal fade" id="modalEditPackagingCost" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-pallet mr-2"></i> Input / Ubah Biaya Kemasan & Printilan
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formEditPackagingCost">
                <input type="hidden" name="id_request" value="<?= (int)$request['id_request'] ?>">
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle text-primary mr-1"></i>
                        Sesuaikan daftar biaya kemasan fisik (kardus innerbox, master outerbox) dan printilan lainnya (stiker hologram garansi, lakban segel, plastik wrapping, bubble wrap, dll). 
                        Anda dapat menambah atau mengurangi baris inputan sesuai kebutuhan.
                    </p>

                    <!-- Tabel Inputan Biaya Kemasan Dinamis (Repeater) -->
                    <div class="table-responsive mb-2">
                        <table class="table table-bordered table-sm mb-0" id="tblModalKemasan">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th style="width: 55%;">Nama Biaya Kemasan / Printilan</th>
                                    <th style="width: 35%;" class="text-right">Biaya per 1 Paket (Rp)</th>
                                    <th style="width: 10%; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="kemasanRowsContainer">
                                <!-- Baris dinamis di-render via JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold shadow-sm" id="btnAddKemasanRow">
                            <i class="fas fa-plus-circle mr-1"></i> Tambah Biaya / Printilan
                        </button>
                        <span class="small text-muted">
                            <i class="fas fa-calculator mr-1"></i> Otomatis menghitung Total HPP per Paket
                        </span>
                    </div>

                    <!-- Live Preview Kalkulasi Modal -->
                    <div class="card bg-light border p-3 mt-3 mb-0" style="border-radius: 8px;">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">HPP Bahan Baku Komponen (LIFO):</span>
                            <strong class="text-dark">Rp <?= number_format($estHppBahan, 2, ',', '.') ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">Total Kemasan & Printilan per 1 Paket:</span>
                            <strong class="text-purple" style="color: #6d28d9;" id="lbl_preview_kemasan">Rp <?= number_format($totalKemasanPerPaket, 2, ',', '.') ?></strong>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span class="text-success">Estimasi Total Modal / HPP per 1 Paket:</span>
                            <span class="text-success font-weight-bold" id="lbl_preview_total_hpp" style="font-size: 1.1rem;">Rp <?= number_format($totalHpp1Paket, 2, ',', '.') ?></span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-1">
                            <span>Total Modal Seluruh Request (<?= number_format($qtyReq, 0) ?> Paket):</span>
                            <strong class="text-primary" id="lbl_preview_total_modal">Rp <?= number_format($totalModalRequest, 2, ',', '.') ?></strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" id="btnSimpanBiayaKemasan">
                        <i class="fas fa-save mr-1"></i> Simpan Biaya Kemasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    const qtyPaket = <?= (float)$qtyReq ?>;
    const hppBahanBaku = <?= (float)$estHppBahan ?>;
    const initialKemasanItems = <?= json_encode(!empty($kemasanItems) ? $kemasanItems : [['nama' => '', 'nominal' => 0]]) ?>;

    function formatRupiah(number) {
        return 'Rp ' + Number(number || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function createKemasanRowHtml(index, nama, nominal) {
        nama = nama || '';
        nominal = (nominal !== undefined && nominal !== null && nominal !== '') ? nominal : 0;
        return `
            <tr class="kemasan-row" data-index="${index}">
                <td>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-box text-secondary"></i></span>
                        </div>
                        <input type="text" name="kemasan_items[${index}][nama]" class="form-control inp-nama-kemasan font-weight-bold" placeholder="Contoh: Kardus Innerbox / Stiker Hologram / Outer Box" value="${escapeHtml(nama)}">
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text font-weight-bold">Rp</span>
                        </div>
                        <input type="number" step="any" min="0" name="kemasan_items[${index}][nominal]" class="form-control text-right font-weight-bold text-dark inp-nominal-kemasan" placeholder="0" value="${nominal}">
                    </div>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-xs btn-outline-danger btn-del-kemasan-row" title="Hapus baris ini">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    let rowIndex = 0;
    function renderInitialRows() {
        const $container = $('#kemasanRowsContainer');
        $container.empty();
        rowIndex = 0;

        let itemsToRender = Array.isArray(initialKemasanItems) && initialKemasanItems.length > 0 ? initialKemasanItems : [{nama: '', nominal: 0}];
        itemsToRender.forEach(function(item) {
            $container.append(createKemasanRowHtml(rowIndex, item.nama, item.nominal));
            rowIndex++;
        });

        hitungLiveKemasan();
    }

    function hitungLiveKemasan() {
        let totalKemasanPaket = 0;
        $('#kemasanRowsContainer .kemasan-row').each(function() {
            const nom = parseFloat($(this).find('.inp-nominal-kemasan').val()) || 0;
            totalKemasanPaket += nom;
        });

        const totalHppPaket = hppBahanBaku + totalKemasanPaket;
        const grandTotalModal = totalHppPaket * qtyPaket;

        $('#lbl_preview_kemasan').text(formatRupiah(totalKemasanPaket));
        $('#lbl_preview_total_hpp').text(formatRupiah(totalHppPaket));
        $('#lbl_preview_total_modal').text(formatRupiah(grandTotalModal));
    }

    // Tambah Baris Baru
    $('#btnAddKemasanRow').on('click', function() {
        const $container = $('#kemasanRowsContainer');
        const newRowHtml = createKemasanRowHtml(rowIndex, '', 0);
        $container.append(newRowHtml);
        rowIndex++;
        $container.find('tr:last .inp-nama-kemasan').focus();
        hitungLiveKemasan();
    });

    // Hapus Baris
    $(document).on('click', '.btn-del-kemasan-row', function() {
        const totalRows = $('#kemasanRowsContainer .kemasan-row').length;
        const $row = $(this).closest('.kemasan-row');

        if (totalRows > 1) {
            $row.remove();
        } else {
            // Sisakan 1 kolom inputan saja dengan mengosongkan isinya
            $row.find('.inp-nama-kemasan').val('');
            $row.find('.inp-nominal-kemasan').val(0);
        }
        hitungLiveKemasan();
    });

    // Input nominal atau nama memicu hitung ulang live
    $(document).on('input change', '.inp-nominal-kemasan', hitungLiveKemasan);

    // Initial render
    renderInitialRows();

    // Submit Form
    $('#formEditPackagingCost').on('submit', function(e) {
        e.preventDefault();
        const $btn = $('#btnSimpanBiayaKemasan');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: '<?= site_url("purchasing/bundling/request/update_packaging_cost") ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Biaya Kemasan');
                if (res.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Disimpan',
                        text: res.msg || 'Biaya kemasan dan estimasi modal HPP berhasil diperbarui',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: res.msg || 'Gagal menyimpan biaya kemasan'
                    });
                }
            },
            error: function(xhr, status, err) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Biaya Kemasan');
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Terjadi gangguan saat menyimpan data: ' + err
                });
            }
        });
    });
});
</script>
