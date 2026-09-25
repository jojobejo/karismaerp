<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <!-- Navbar -->
    <?php $this->load->view('partial/main/navbar') ?>
    <!-- Main Sidebar Container -->
    <?php $this->load->view('partial/main/sidebar') ?>

<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-handshake text-primary mr-2"></i> Barang Konsinyasi
                    </h1>
                    <p class="text-muted mb-0 small">Pengelolaan menyeluruh barang titipan supplier: penerimaan barang fisik ke gudang & penyelesaian tagihan terjual</p>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-outline-primary shadow-sm font-weight-bold mr-2" id="btnSyncKonsinyasi">
                        <i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Penjualan
                    </button>
                    <button type="button" class="btn btn-success shadow-sm font-weight-bold" id="btnTambahPenerimaan">
                        <i class="fas fa-plus-circle mr-1"></i> Penerimaan Konsinyasi Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- Summary Cards -->
            <div class="row mb-3">
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border-left: 5px solid #f59e0b !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted font-weight-bold small text-uppercase">Terjual (Menunggu Tagihan)</span>
                                    <h3 class="mb-0 font-weight-bold text-warning mt-1"><?= number_format($stats['pending_qty'], 2) ?> <span class="small font-weight-normal text-muted">pcs</span></h3>
                                    <small class="text-muted"><?= $stats['pending_count'] ?> item transaksi SO/Faktur</small>
                                </div>
                                <div class="p-3 bg-warning-light rounded-circle text-warning" style="background: #fef3c7;">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border-left: 5px solid #10b981 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted font-weight-bold small text-uppercase">Selesai Difakturkan (Billed)</span>
                                    <h3 class="mb-0 font-weight-bold text-success mt-1"><?= number_format($stats['billed_qty'], 2) ?> <span class="small font-weight-normal text-muted">pcs</span></h3>
                                    <small class="text-muted">Total Hutang: <strong>Rp <?= number_format($stats['billed_hutang'], 2) ?></strong></small>
                                </div>
                                <div class="p-3 rounded-circle text-success" style="background: #d1fae5;">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-lg-4">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border-left: 5px solid #3b82f6 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted font-weight-bold small text-uppercase">Lokasi Gudang Fisik</span>
                                    <h4 class="mb-0 font-weight-bold text-primary mt-1">Gdg. Konsiyasi</h4>
                                    <small class="text-muted">Stok fisik titipan terisolasi (Non-Neraca di awal)</small>
                                </div>
                                <div class="p-3 rounded-circle text-primary" style="background: #dbeafe;">
                                    <i class="fas fa-warehouse fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2 TAB UTAMA MODUL BARANG KONSINYASI -->
            <ul class="nav nav-pills mb-3 p-2 bg-white rounded shadow-sm border" style="gap: 8px;">
                <li class="nav-item">
                    <a class="nav-link font-weight-bold px-4 py-2 <?= ($active_tab === 'penerimaan') ? 'active bg-success text-white shadow-sm' : 'text-dark' ?>" 
                       href="<?= site_url('purchasing/konsinyasi?tab=penerimaan') ?>" style="border-radius: 8px;">
                        <i class="fas fa-boxes mr-2"></i> 1. Penerimaan Barang Konsinyasi (Stok Fisik)
                        <span class="badge <?= ($active_tab === 'penerimaan') ? 'badge-light text-success' : 'badge-secondary' ?> ml-2"><?= count($penerimaan_list) ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold px-4 py-2 <?= ($active_tab === 'penyelesaian') ? 'active bg-primary text-white shadow-sm' : 'text-dark' ?>" 
                       href="<?= site_url('purchasing/konsinyasi?tab=penyelesaian') ?>" style="border-radius: 8px;">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> 2. Penyelesaian Tagihan Terjual (Settlement)
                        <span class="badge <?= ($active_tab === 'penyelesaian') ? 'badge-light text-primary' : 'badge-warning text-dark' ?> ml-2"><?= $stats['pending_count'] ?></span>
                    </a>
                </li>
            </ul>

            <?php if ($active_tab === 'penerimaan'): ?>
            <!-- ========================================================================= -->
            <!-- TAB 1: DAFTAR PENERIMAAN BARANG KONSINYASI (INVENTORY FISIK) -->
            <!-- ========================================================================= -->
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form method="get" action="<?= site_url('purchasing/konsinyasi') ?>" class="row align-items-end">
                        <input type="hidden" name="tab" value="penerimaan">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-muted mb-1">Filter Supplier Konsinyasi</label>
                            <select name="p_kd_suplier" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()">
                                <option value="SEMUA">Semua Supplier</option>
                                <?php foreach ($all_suppliers as $sup): ?>
                                    <option value="<?= htmlspecialchars($sup['kd_suplier']) ?>" <?= ($filters_p['kd_suplier'] == $sup['kd_suplier']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sup['nama_suplier']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold text-muted mb-1">Cari No. Penerimaan / Surat Jalan</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="p_search" class="form-control" placeholder="Cari nomor tanda terima, surat jalan, supplier..." value="<?= htmlspecialchars($filters_p['search']) ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-success" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-right pt-2">
                            <?php if (!empty($filters_p['search']) || $filters_p['kd_suplier'] !== 'SEMUA'): ?>
                                <a href="<?= site_url('purchasing/konsinyasi?tab=penerimaan') ?>" class="btn btn-sm btn-outline-danger btn-block">
                                    <i class="fas fa-times-circle mr-1"></i> Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-dolly-flatbed text-success mr-2"></i> Riwayat Penerimaan Barang Konsinyasi dari Supplier
                    </h6>
                    <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm" onclick="$('#btnTambahPenerimaan').click()">
                        <i class="fas fa-plus mr-1"></i> Penerimaan Baru
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                            <thead style="background: #f1f5f9; color: #475569;">
                                <tr>
                                    <th class="py-3 px-3">No. Penerimaan</th>
                                    <th class="py-3">Tgl Masuk</th>
                                    <th class="py-3">Supplier Konsinyasi</th>
                                    <th class="py-3">Gudang</th>
                                    <th class="py-3">Surat Jalan</th>
                                    <th class="py-3 text-center">Jml Item</th>
                                    <th class="py-3 text-right">Total Qty Fisik</th>
                                    <th class="py-3 text-center">Status Stok</th>
                                    <th class="py-3 text-center" style="width: 110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($penerimaan_list)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3 text-black-50 d-block"></i>
                                            Belum ada dokumen penerimaan barang konsinyasi mandiri. Silakan klik <strong>Penerimaan Konsinyasi Baru</strong> untuk input.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($penerimaan_list as $pm): ?>
                                        <tr>
                                            <td class="px-3 font-weight-bold text-success">
                                                <?= htmlspecialchars($pm['nomor_masuk']) ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($pm['tanggal_masuk'])) ?></td>
                                            <td>
                                                <strong class="text-dark"><?= htmlspecialchars($pm['nama_suplier']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($pm['kd_suplier']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-light border text-dark"><?= htmlspecialchars($pm['nama_gudang'] ?: 'Gdg. Konsinyasi') ?></span>
                                            </td>
                                            <td>
                                                <?= !empty($pm['no_surat_jalan']) ? htmlspecialchars($pm['no_surat_jalan']) : '-' ?>
                                                <?php if (!empty($pm['tgl_surat_jalan'])): ?>
                                                    <br><small class="text-muted"><?= date('d/m/Y', strtotime($pm['tgl_surat_jalan'])) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center font-weight-bold"><?= (int) $pm['total_item'] ?> item</td>
                                            <td class="text-right font-weight-bold text-primary"><?= number_format($pm['total_qty'], 2) ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-success px-2 py-1 shadow-xs">
                                                    <i class="fas fa-check mr-1"></i> Fisik Masuk
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-info btn-sm btnViewPenerimaan" data-id="<?= $pm['id_masuk'] ?>">
                                                    <i class="fas fa-eye mr-1"></i> Detail
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- ========================================================================= -->
            <!-- TAB 2: PENYELESAIAN TAGIHAN BARANG TERJUAL (SETTLEMENT) -->
            <!-- ========================================================================= -->
            <!-- Tab Navigasi Status & Filter Settlement -->
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-body p-0">
                    <!-- Tab header -->
                    <div class="d-flex border-bottom" style="border-radius: 12px 12px 0 0; overflow: hidden;">
                        <a href="<?= site_url('purchasing/konsinyasi?tab=penyelesaian') ?>&status=PENDING&kd_suplier=<?= urlencode($filters['kd_suplier']) ?>&search=<?= urlencode($filters['search']) ?>"
                           class="px-4 py-3 font-weight-bold text-decoration-none d-flex align-items-center gap-2
                           <?= ($filters['status'] === 'PENDING') ? 'text-warning border-bottom border-warning' : 'text-muted' ?>"
                           style="border-bottom: <?= ($filters['status'] === 'PENDING') ? '3px solid #f59e0b' : '3px solid transparent' ?>; background: <?= ($filters['status'] === 'PENDING') ? '#fffbeb' : 'white' ?>; margin-bottom: -1px;">
                            <i class="fas fa-clock mr-2"></i> Menunggu Input Tagihan
                            <span class="badge badge-warning text-dark ml-2"><?= $stats['pending_count'] ?></span>
                        </a>
                        <a href="<?= site_url('purchasing/konsinyasi?tab=penyelesaian') ?>&status=BILLED&kd_suplier=<?= urlencode($filters['kd_suplier']) ?>&search=<?= urlencode($filters['search']) ?>"
                           class="px-4 py-3 font-weight-bold text-decoration-none d-flex align-items-center
                           <?= ($filters['status'] === 'BILLED') ? 'text-success' : 'text-muted' ?>"
                           style="border-bottom: <?= ($filters['status'] === 'BILLED') ? '3px solid #10b981' : '3px solid transparent' ?>; background: <?= ($filters['status'] === 'BILLED') ? '#f0fdf4' : 'white' ?>; margin-bottom: -1px;">
                            <i class="fas fa-history mr-2"></i> Riwayat Sudah Ditagih
                            <span class="badge badge-success ml-2"><?= $stats['billed_count'] ?></span>
                        </a>
                        <a href="<?= site_url('purchasing/konsinyasi?tab=penyelesaian') ?>&status=SEMUA&kd_suplier=<?= urlencode($filters['kd_suplier']) ?>&search=<?= urlencode($filters['search']) ?>"
                           class="px-4 py-3 font-weight-bold text-decoration-none d-flex align-items-center
                           <?= ($filters['status'] === 'SEMUA') ? 'text-primary' : 'text-muted' ?>"
                           style="border-bottom: <?= ($filters['status'] === 'SEMUA') ? '3px solid #3b82f6' : '3px solid transparent' ?>; background: <?= ($filters['status'] === 'SEMUA') ? '#eff6ff' : 'white' ?>; margin-bottom: -1px;">
                            <i class="fas fa-list mr-2"></i> Semua
                        </a>
                    </div>

                    <!-- Filter dalam tab -->
                    <form method="get" action="<?= site_url('purchasing/konsinyasi') ?>" class="row align-items-end p-3">
                        <input type="hidden" name="tab" value="penyelesaian">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status']) ?>">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-muted mb-1">Supplier Konsinyasi</label>
                            <select name="kd_suplier" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()">
                                <option value="SEMUA">Semua Supplier</option>
                                <?php foreach ($suppliers as $sup): ?>
                                    <option value="<?= htmlspecialchars($sup['kd_suplier']) ?>" <?= ($filters['kd_suplier'] == $sup['kd_suplier']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sup['nama_suplier']) ?> (<?= $sup['total_transaksi'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold text-muted mb-1">Pencarian Barang / Customer / SO / Invoice Supplier</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama barang, customer, no SO, no invoice supplier..." value="<?= htmlspecialchars($filters['search']) ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-right pt-2">
                            <?php if (!empty($filters['search']) || $filters['kd_suplier'] !== 'SEMUA'): ?>
                                <a href="<?= site_url('purchasing/konsinyasi?tab=penyelesaian') ?>&status=<?= $filters['status'] ?>" class="btn btn-sm btn-outline-danger btn-block">
                                    <i class="fas fa-times-circle mr-1"></i> Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Tabel Data Settlement Konsinyasi -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <?php if ($filters['status'] === 'BILLED'): ?>
                                <i class="fas fa-history text-success mr-1"></i> Riwayat Barang Konsinyasi Sudah Ditagih
                            <?php elseif ($filters['status'] === 'PENDING'): ?>
                                <i class="fas fa-clock text-warning mr-1"></i> Barang Konsinyasi Menunggu Input Tagihan Supplier
                            <?php else: ?>
                                <i class="fas fa-list text-muted mr-1"></i> Semua Daftar Barang Konsinyasi
                            <?php endif; ?>
                        </h6>
                        <span class="badge badge-light border text-muted px-2 py-1">
                            Total: <?= count($settlements) ?> Transaksi
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.90rem;">
                            <thead style="background: #f1f5f9; color: #334155;">
                                <tr>
                                    <th class="py-3 px-3">No. Settlement</th>
                                    <th class="py-3">Tgl Terjual</th>
                                    <th class="py-3">Supplier Konsinyasi</th>
                                    <th class="py-3">Penjualan ke Customer</th>
                                    <th class="py-3">Barang & Batch/Lot</th>
                                    <th class="py-3 text-right">Qty Terjual</th>
                                    <th class="py-3 text-right">Harga Jual</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3">Tagihan Beli Supplier</th>
                                    <th class="py-3 text-center" style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($settlements)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="fas fa-clipboard-check fa-3x mb-3 text-black-50 d-block"></i>
                                            Tidak ada data barang konsinyasi yang ditemukan pada filter ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($settlements as $s): ?>
                                        <tr>
                                            <td class="px-3 font-weight-bold text-primary">
                                                <?= htmlspecialchars($s['no_settlement']) ?>
                                                <?php if (!empty($s['nomor_lpb_asal'])): ?>
                                                    <br><small class="text-muted"><i class="fas fa-barcode"></i> LPB: <?= htmlspecialchars($s['nomor_lpb_asal']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($s['tanggal_settlement'])) ?>
                                            </td>
                                            <td>
                                                <strong class="text-dark"><?= htmlspecialchars($s['nama_suplier']) ?></strong>
                                                <br><small class="text-muted">Kode: <?= htmlspecialchars($s['kd_suplier']) ?></small>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-dark"><?= htmlspecialchars($s['customer_name']) ?></span>
                                                <br><small class="text-muted">SO: <?= htmlspecialchars($s['no_so']) ?><?= !empty($s['no_faktur']) ? ' | Inv: ' . htmlspecialchars($s['no_faktur']) : '' ?></small>
                                            </td>
                                            <td>
                                                <strong class="text-dark"><?= htmlspecialchars($s['nama_barang']) ?></strong>
                                                <br><small class="text-muted">Lot: <?= htmlspecialchars($s['no_lot'] ?: '-') ?> | ED: <?= !empty($s['expired_date']) ? date('d/m/Y', strtotime($s['expired_date'])) : '-' ?></small>
                                            </td>
                                            <td class="text-right font-weight-bold">
                                                <?= number_format($s['qty_net'], 2) ?> <?= htmlspecialchars($s['satuan']) ?>
                                            </td>
                                            <td class="text-right text-muted">
                                                Rp <?= number_format($s['hrg_jual'], 2) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($s['status'] === 'PENDING'): ?>
                                                    <span class="badge badge-warning text-dark px-2 py-1 shadow-xs">
                                                        <i class="fas fa-clock mr-1"></i> Menunggu Invoice
                                                    </span>
                                                <?php elseif ($s['status'] === 'BILLED'): ?>
                                                    <span class="badge badge-success px-2 py-1 shadow-xs">
                                                        <i class="fas fa-check-circle mr-1"></i> Terjurnal (Billed)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary px-2 py-1">
                                                        <?= htmlspecialchars($s['status']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($s['status'] === 'BILLED'): ?>
                                                    <div class="small">
                                                        <strong class="text-success" style="font-size: 0.95rem;">Rp <?= number_format($s['total_tagihan_beli'], 2) ?></strong>
                                                        <?php if ($s['tipe_pajak'] === 'INCLUDE'): ?>
                                                            <span class="badge badge-success-light text-success font-weight-bold ml-1" style="background:#d1fae5;">Inc. PPN</span>
                                                        <?php elseif ($s['tipe_pajak'] === 'EXCLUDE'): ?>
                                                            <span class="badge badge-primary-light text-primary font-weight-bold ml-1" style="background:#dbeafe;">Exc. PPN</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary ml-1">Non-PPN</span>
                                                        <?php endif; ?>
                                                        <br><span class="text-dark">Inv: <?= htmlspecialchars($s['no_invoice_supplier']) ?></span>
                                                        <br><span class="text-muted">DPP: Rp <?= number_format($s['subtotal_beli'], 2) ?> | PPN: Rp <?= number_format($s['nilai_ppn'], 2) ?></span>
                                                        <?php if (!empty($s['nomor_jurnal'])): ?>
                                                            <br><span class="badge badge-info mt-1"><i class="fas fa-book mr-1"></i><?= htmlspecialchars($s['nomor_jurnal']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small font-italic">Belum diinput</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($s['status'] === 'PENDING'): ?>
                                                    <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm btnProcessSettlement" data-id="<?= $s['id_settlement'] ?>">
                                                        <i class="fas fa-file-invoice-dollar mr-1"></i> Input Tagihan
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm btnViewSettlement" data-id="<?= $s['id_settlement'] ?>">
                                                        <i class="fas fa-eye mr-1"></i> Detail
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>
</div>

<!-- Modal Form Penyelesaian Konsinyasi (Input Invoice Supplier) -->
<div class="modal fade" id="modalSettlement" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title font-weight-bold" id="modalTitle">
                    <i class="fas fa-file-invoice-dollar mr-2"></i> Input Tagihan Supplier Konsinyasi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formSettlement">
                <input type="hidden" name="id_settlement" id="set_id_settlement">
                <div class="modal-body p-4">
                    <!-- Ringkasan Info Penjualan -->
                    <div class="p-3 mb-3 bg-light rounded border">
                        <div class="row small mb-1">
                            <div class="col-4 text-muted">Barang:</div>
                            <div class="col-8 font-weight-bold text-dark" id="txt_nama_barang">-</div>
                        </div>
                        <div class="row small mb-1">
                            <div class="col-4 text-muted">Supplier:</div>
                            <div class="col-8 font-weight-bold text-dark" id="txt_nama_suplier">-</div>
                        </div>
                        <div class="row small mb-1">
                            <div class="col-4 text-muted">Qty Terjual:</div>
                            <div class="col-8 font-weight-bold text-primary" id="txt_qty_terjual">-</div>
                        </div>
                        <div class="row small">
                            <div class="col-4 text-muted">Customer:</div>
                            <div class="col-8 text-dark" id="txt_customer_name">-</div>
                        </div>
                    </div>

                    <!-- Input Invoice Resmi Supplier -->
                    <div class="row">
                        <div class="col-md-7 form-group mb-3">
                            <label class="small font-weight-bold text-dark">Nomor Faktur / Invoice Supplier <span class="text-danger">*</span></label>
                            <input type="text" name="no_invoice_supplier" id="no_invoice_supplier" class="form-control" placeholder="Contoh: INV-SUPP-2026/09/101" required>
                        </div>
                        <div class="col-md-5 form-group mb-3">
                            <label class="small font-weight-bold text-dark">Tanggal Invoice <span class="text-danger">*</span></label>
                            <input type="date" name="tgl_invoice_supplier" id="tgl_invoice_supplier" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <!-- Pilihan Tipe Harga dari Supplier -->
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-dark d-block mb-1">
                            Tipe Harga dari Faktur Supplier <span class="text-danger">*</span>
                        </label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <label class="btn btn-outline-primary active btn-sm font-weight-bold py-2" id="lbl_tipe_exclude">
                                <input type="radio" name="tipe_pajak" id="tipe_exclude" value="EXCLUDE" checked>
                                <i class="fas fa-tag mr-1"></i> Exclude PPN
                            </label>
                            <label class="btn btn-outline-success btn-sm font-weight-bold py-2" id="lbl_tipe_include">
                                <input type="radio" name="tipe_pajak" id="tipe_include" value="INCLUDE">
                                <i class="fas fa-receipt mr-1"></i> Include PPN
                            </label>
                            <label class="btn btn-outline-secondary btn-sm font-weight-bold py-2" id="lbl_tipe_non_ppn">
                                <input type="radio" name="tipe_pajak" id="tipe_non_ppn" value="NON_PPN">
                                <i class="fas fa-ban mr-1"></i> Non-PPN
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1" id="tipe_pajak_help">
                            *Pilih <strong>Include PPN</strong> jika harga dari supplier sudah termasuk PPN. Sistem otomatis mengekstrak DPP tanpa Anda hitung manual.
                        </small>
                    </div>

                    <input type="hidden" name="ppn_persen" id="ppn_persen" value="11">
                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-dark" id="label_hrg_satuan">Harga Satuan Exclude PPN (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="any" min="1" name="hrg_satuan_input" id="hrg_satuan_input" class="form-control font-weight-bold text-right" placeholder="0" required>
                    </div>

                    <!-- Live Breakdown Box -->
                    <div class="p-3 mb-3 rounded" style="background: #f1f5f9; border: 1px solid #cbd5e1;">
                        <span class="small font-weight-bold text-muted text-uppercase d-block mb-2">
                            <i class="fas fa-calculator mr-1"></i> Rincian Otomatis Sistem
                        </span>
                        <div class="d-flex justify-content-between small mb-1" id="box_preview_hrg_dpp">
                            <span class="text-muted">Harga Satuan DPP (Sebelum Pajak):</span>
                            <span class="font-weight-bold text-dark" id="preview_hrg_satuan_dpp">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1" id="box_preview_hrg_inc">
                            <span class="text-muted">Harga Satuan Include PPN:</span>
                            <span class="font-weight-bold text-primary" id="preview_hrg_satuan_inc">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Subtotal DPP (Masuk HPP):</span>
                            <span class="font-weight-bold text-dark" id="preview_dpp">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Nilai PPN Masukan:</span>
                            <span class="font-weight-bold text-dark" id="preview_ppn">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                            <strong class="text-dark">Total:</strong>
                            <strong class="text-success" style="font-size: 1.15rem;" id="preview_total_tagihan">Rp 0</strong>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-muted">Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan" id="catatan" class="form-control form-control-sm" rows="2" placeholder="Catatan pembelian konsinyasi..."></textarea>
                    </div>

                    <div class="alert alert-info mt-3 py-2 px-3 small mb-0">
                        <i class="fas fa-info-circle mr-1"></i> Sistem otomatis menjurnal: <strong>[Debit] HPP</strong> (sebesar DPP) + <strong>[Debit] PPN Masukan</strong> = <strong>[Kredit] Utang Konsinyasi</strong> (sebesar Total Tagihan).
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4" id="btnSubmitSettlement">
                        <i class="fas fa-check mr-1"></i> Posting Jurnal Pembelian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Riwayat Settlement (BILLED) -->
<div class="modal fade" id="modalDetailSettlement" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #10b981, #059669); border-radius: 14px 14px 0 0;">
                <h5 class="modal-title font-weight-bold text-white">
                    <i class="fas fa-history mr-2"></i> Detail Riwayat Settlement Konsinyasi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="modalDetailBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Penerimaan Barang Konsinyasi Baru (Mandiri Non-LPB) -->
<div class="modal fade" id="modalTambahPenerimaan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header border-0 bg-success text-white" style="border-radius: 14px 14px 0 0;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-boxes mr-2"></i> Penerimaan Barang Titipan Konsinyasi Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTambahPenerimaan">
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Form ini digunakan untuk mencatat <strong>fisik barang titipan supplier</strong> yang masuk ke Gudang Konsinyasi secara mandiri. Transaksi ini <strong>TIDAK masuk ke tabel LPB</strong> dan <strong>TIDAK menimbulkan hutang</strong> sampai barang tersebut terjual ke customer.
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group mb-3">
                            <label class="small font-weight-bold text-dark">Supplier Konsinyasi <span class="text-danger">*</span></label>
                            <select name="kd_suplier" id="in_kd_suplier" class="form-control select2bs4" required>
                                <option value="">-- Pilih Supplier --</option>
                                <?php foreach ($all_suppliers as $sup): ?>
                                    <option value="<?= htmlspecialchars($sup['kd_suplier']) ?>" data-nama="<?= htmlspecialchars($sup['nama_suplier']) ?>">
                                        <?= htmlspecialchars($sup['nama_suplier']) ?> (<?= htmlspecialchars($sup['kd_suplier']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="nama_suplier" id="in_nama_suplier">
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-dark">Tanggal Penerimaan Fisik <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_masuk" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3 form-group mb-3">
                            <label class="small font-weight-bold text-dark">Lokasi Gudang</label>
                            <input type="text" class="form-control" value="Gdg. Konsiyasi (ID: 13)" readonly style="background: #f1f5f9;">
                            <input type="hidden" name="gudang_id" value="13">
                        </div>
                        <div class="col-md-2 form-group mb-3">
                            <label class="small font-weight-bold text-dark">No. Surat Jalan</label>
                            <input type="text" name="no_surat_jalan" class="form-control" placeholder="Contoh: SJ-0012/SUPP">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 form-group mb-0">
                            <label class="small font-weight-bold text-dark">Tgl Surat Jalan</label>
                            <input type="date" name="tgl_surat_jalan" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-8 form-group mb-0">
                            <label class="small font-weight-bold text-dark">Catatan / Keterangan Penerimaan</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Catatan tambahan tanda terima konsinyasi...">
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Tabel Item Barang Masuk -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="font-weight-bold text-dark mb-0">
                            <i class="fas fa-list-ol mr-1 text-success"></i> Rincian Barang Konsinyasi yang Diterima
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-success font-weight-bold" id="btnAddItemRow">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris Barang
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="tableItemPenerimaan" style="font-size: 0.9rem;">
                            <thead class="bg-light text-dark font-weight-bold">
                                <tr>
                                    <th style="width: 35%;">Pilih Barang <span class="text-danger">*</span></th>
                                    <th style="width: 15%;">Jumlah (Qty) <span class="text-danger">*</span></th>
                                    <th style="width: 12%;">Satuan</th>
                                    <th style="width: 18%;">No. Lot / Batch</th>
                                    <th style="width: 15%;">Expired Date</th>
                                    <th style="width: 5%;" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyItemPenerimaan">
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][kd_barang]" class="form-control form-control-sm select-barang" required>
                                            <option value="">-- Pilih Barang --</option>
                                            <?php foreach ($all_barangs as $brg): ?>
                                                <option value="<?= htmlspecialchars($brg['kode_barang']) ?>"
                                                        data-nama="<?= htmlspecialchars($brg['nama_barang']) ?>"
                                                        data-satuan="<?= htmlspecialchars($brg['satuan'] ?: 'PCS') ?>">
                                                    <?= htmlspecialchars($brg['nama_barang']) ?> (<?= htmlspecialchars($brg['kode_barang']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="items[0][nama_barang]" class="in-nama-barang">
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0.001" name="items[0][qty]" class="form-control form-control-sm text-right font-weight-bold" placeholder="0.00" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][satuan]" class="form-control form-control-sm in-satuan" value="PCS" readonly style="background: #f8fafc;">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][no_lot]" class="form-control form-control-sm" placeholder="No. Batch / Lot">
                                    </td>
                                    <td>
                                        <input type="date" name="items[0][expired_date]" class="form-control form-control-sm">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btnRemoveRow" disabled><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success font-weight-bold px-4" id="btnSubmitPenerimaan">
                        <i class="fas fa-save mr-1"></i> Simpan Penerimaan Fisik
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Dokumen Penerimaan Konsinyasi -->
<div class="modal fade" id="modalDetailPenerimaan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header border-0 bg-info text-white" style="border-radius: 14px 14px 0 0;">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-file-alt mr-2"></i> Detail Dokumen Penerimaan Konsinyasi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="modalDetailPenerimaanBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2">Memuat data dokumen...</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var currentQtyNet = 0;
    var currentSatuan = '';

    // Tombol Sinkronisasi Penjualan Konsinyasi
    $('#btnSyncKonsinyasi').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyinkronkan...');

        $.ajax({
            url: '<?= site_url("purchasing/konsinyasi_settlement/sync") ?>',
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Penjualan');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sinkronisasi Selesai',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    alert(res.message);
                    location.reload();
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Penjualan');
                alert('Terjadi kesalahan saat sinkronisasi.');
            }
        });
    });

    // Buka Modal Input Tagihan (PENDING)
    $('.btnProcessSettlement').on('click', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '<?= site_url("purchasing/konsinyasi_settlement/detail") ?>',
            type: 'GET',
            data: { id_settlement: id },
            dataType: 'json',
            success: function(res) {
                if (res.status && res.data) {
                    var d = res.data;
                    $('#set_id_settlement').val(d.id_settlement);
                    $('#txt_nama_barang').text(d.nama_barang + ' (' + (d.no_lot || '-') + ')');
                    $('#txt_nama_suplier').text(d.nama_suplier);
                    $('#txt_qty_terjual').text(parseFloat(d.qty_net).toFixed(2) + ' ' + d.satuan);
                    $('#txt_customer_name').text(d.customer_name + ' (SO: ' + d.no_so + ')');

                    currentQtyNet = parseFloat(d.qty_net) || 0;
                    currentSatuan = d.satuan || '';
                    $('#hrg_satuan_input').val('');
                    $('#no_invoice_supplier').val('');

                    // Reset ke default Exclude PPN
                    $('input[name="tipe_pajak"][value="EXCLUDE"]').prop('checked', true);
                    $('#lbl_tipe_exclude').addClass('active').siblings().removeClass('active');
                    $('#ppn_persen').val('11');

                    calculateSettlementLive();
                    $('#catatan').val('');

                    $('#modalSettlement').modal('show');
                } else {
                    alert(res.message || 'Gagal mengambil data.');
                }
            }
        });
    });

    // Buka Modal Detail Riwayat (BILLED)
    $('.btnViewSettlement').on('click', function() {
        var id = $(this).data('id');
        $('#modalDetailBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="text-muted mt-2">Memuat data...</p></div>');
        $('#modalDetailSettlement').modal('show');

        $.ajax({
            url: '<?= site_url("purchasing/konsinyasi_settlement/detail") ?>',
            type: 'GET',
            data: { id_settlement: id },
            dataType: 'json',
            success: function(res) {
                if (res.status && res.data) {
                    var d = res.data;
                    var fmt = function(n) { return 'Rp ' + parseFloat(n || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
                    var tipeBadge = '';
                    if (d.tipe_pajak === 'INCLUDE') tipeBadge = '<span class="badge badge-success ml-1">Include PPN</span>';
                    else if (d.tipe_pajak === 'EXCLUDE') tipeBadge = '<span class="badge badge-primary ml-1">Exclude PPN</span>';
                    else tipeBadge = '<span class="badge badge-secondary ml-1">Non-PPN</span>';

                    var html = '';
                    // Informasi Penjualan
                    html += '<div class="row mb-3">';
                    html += '<div class="col-md-6">';
                    html += '<div class="card border-0 bg-light" style="border-radius:10px;">';
                    html += '<div class="card-body p-3">';
                    html += '<h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-shopping-cart text-primary mr-2"></i>Info Penjualan</h6>';
                    html += '<table class="table table-sm table-borderless mb-0 small">';
                    html += '<tr><td class="text-muted" style="width:45%">No. Settlement:</td><td class="font-weight-bold text-primary">' + (d.no_settlement || '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">Barang:</td><td class="font-weight-bold">' + (d.nama_barang || '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">No. Lot / Batch:</td><td>' + (d.no_lot || '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">Customer:</td><td>' + (d.customer_name || '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">No. SO:</td><td>' + (d.no_so || '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">No. Faktur:</td><td>' + (d.no_faktur || '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">Qty Terjual:</td><td class="font-weight-bold text-primary">' + parseFloat(d.qty_net || 0).toFixed(2) + ' ' + (d.satuan || '') + '</td></tr>';
                    html += '<tr><td class="text-muted">Harga Jual:</td><td>' + fmt(d.hrg_jual) + ' / ' + (d.satuan || 'pcs') + '</td></tr>';
                    html += '</table>';
                    html += '</div></div>';
                    html += '</div>';

                    // Informasi Tagihan Supplier
                    html += '<div class="col-md-6">';
                    html += '<div class="card border-0 bg-light" style="border-radius:10px;">';
                    html += '<div class="card-body p-3">';
                    html += '<h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-file-invoice-dollar text-success mr-2"></i>Tagihan Supplier ' + tipeBadge + '</h6>';
                    html += '<table class="table table-sm table-borderless mb-0 small">';
                    html += '<tr><td class="text-muted" style="width:50%">Supplier:</td><td class="font-weight-bold">' + (d.nama_suplier || '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">No. Invoice Supplier:</td><td class="font-weight-bold text-dark">' + (d.no_invoice_supplier || '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">Tgl. Invoice:</td><td>' + (d.tgl_invoice_supplier ? new Date(d.tgl_invoice_supplier).toLocaleDateString("id-ID", {day:"2-digit",month:"long",year:"numeric"}) : '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">Harga Satuan (Input):</td><td class="font-weight-bold">' + fmt(d.hrg_satuan_input) + '</td></tr>';
                    html += '<tr><td class="text-muted">Harga Satuan DPP:</td><td>' + fmt(d.hrg_beli_satuan) + '</td></tr>';
                    html += '<tr><td class="text-muted">PPN (' + parseFloat(d.ppn_persen || 0).toFixed(0) + '%):</td><td>' + fmt(d.nilai_ppn) + '</td></tr>';
                    html += '</table>';
                    html += '</div></div>';
                    html += '</div>';
                    html += '</div>';

                    // Ringkasan Total
                    html += '<div class="p-3 rounded" style="background: #f0fdf4; border: 1px solid #a7f3d0;">';
                    html += '<div class="row">';
                    html += '<div class="col-md-4 text-center">';
                    html += '<div class="small text-muted">Subtotal DPP (Masuk HPP)</div>';
                    html += '<div class="font-weight-bold text-dark" style="font-size:1.05rem;">' + fmt(d.subtotal_beli) + '</div>';
                    html += '</div>';
                    html += '<div class="col-md-4 text-center">';
                    html += '<div class="small text-muted">Nilai PPN Masukan</div>';
                    html += '<div class="font-weight-bold text-dark" style="font-size:1.05rem;">' + fmt(d.nilai_ppn) + '</div>';
                    html += '</div>';
                    html += '<div class="col-md-4 text-center border-left">';
                    html += '<div class="small text-muted font-weight-bold text-uppercase">Total Tagihan</div>';
                    html += '<div class="font-weight-bold text-success" style="font-size:1.3rem;">' + fmt(d.total_tagihan_beli) + '</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';

                    // Jurnal & Info Posting
                    if (d.nomor_jurnal) {
                        html += '<div class="mt-3 d-flex align-items-center">';
                        html += '<span class="badge badge-info px-3 py-2"><i class="fas fa-book mr-1"></i>Jurnal: ' + d.nomor_jurnal + '</span>';
                        if (d.settled_at) {
                            html += '<small class="text-muted ml-3"><i class="fas fa-user-check mr-1"></i>Diposting oleh <strong>' + (d.settled_by || '-') + '</strong> pada ' + new Date(d.settled_at).toLocaleString("id-ID") + '</small>';
                        }
                        html += '</div>';
                    }
                    if (d.catatan) {
                        html += '<div class="mt-2 p-2 bg-light rounded small text-muted"><i class="fas fa-sticky-note mr-1"></i>' + d.catatan + '</div>';
                    }

                    $('#modalDetailBody').html(html);
                } else {
                    $('#modalDetailBody').html('<div class="alert alert-danger">Gagal memuat data detail.</div>');
                }
            },
            error: function() {
                $('#modalDetailBody').html('<div class="alert alert-danger">Terjadi kesalahan koneksi.</div>');
            }
        });
    });

    // Event ganti tipe pajak atau ketik harga satuan
    $('input[name="tipe_pajak"]').on('change', function() {
        calculateSettlementLive();
    });
    $('#hrg_satuan_input').on('input change', function() {
        calculateSettlementLive();
    });

    function calculateSettlementLive() {
        var tipe = $('input[name="tipe_pajak"]:checked').val() || 'EXCLUDE';
        var hrg = parseFloat($('#hrg_satuan_input').val()) || 0;
        var ppnPersen = (tipe === 'NON_PPN') ? 0 : 11;
        $('#ppn_persen').val(ppnPersen);

        var satuanText = currentSatuan ? ' / ' + currentSatuan : '';

        if (tipe === 'NON_PPN') {
            $('#label_hrg_satuan').text('Harga Satuan Non-PPN (Rp) *');
            $('#tipe_pajak_help').html('Harga murni tanpa PPN. Jurnal HPP dan Utang dicatat sebesar harga ini.');
            $('#box_preview_hrg_dpp span:first').text('Harga Satuan (Non-PPN):');
            $('#box_preview_hrg_inc').hide();
        } else if (tipe === 'INCLUDE') {
            $('#label_hrg_satuan').text('Harga Satuan Include PPN (Rp) *');
            $('#tipe_pajak_help').html('Ketikkan harga include di faktur supplier. <strong>Sistem otomatis mengekstrak DPP dan PPN Masukan</strong>.');
            $('#box_preview_hrg_dpp span:first').text('Harga Satuan DPP (Sebelum Pajak):');
            $('#box_preview_hrg_inc').show();
        } else {
            // EXCLUDE
            $('#label_hrg_satuan').text('Harga Satuan Exclude PPN (Rp) *');
            $('#tipe_pajak_help').html('Ketikkan harga DPP sebelum PPN. PPN akan ditambahkan otomatis ke total tagihan.');
            $('#box_preview_hrg_dpp span:first').text('Harga Satuan DPP (Sebelum Pajak):');
            $('#box_preview_hrg_inc').show();
        }

        var hrgSatuanDpp = 0;
        var hrgSatuanInc = 0;
        var totalTagihan = 0;
        var subtotalDpp = 0;
        var nilaiPpn = 0;

        if (tipe === 'INCLUDE') {
            hrgSatuanInc = hrg;
            var divider = 1 + (ppnPersen / 100);
            hrgSatuanDpp = (ppnPersen > 0) ? (hrg / divider) : hrg;

            totalTagihan = currentQtyNet * hrgSatuanInc;
            subtotalDpp = (ppnPersen > 0) ? (totalTagihan / divider) : totalTagihan;
            nilaiPpn = totalTagihan - subtotalDpp;
        } else if (tipe === 'NON_PPN') {
            hrgSatuanDpp = hrg;
            hrgSatuanInc = hrg;
            subtotalDpp = currentQtyNet * hrg;
            nilaiPpn = 0;
            totalTagihan = subtotalDpp;
        } else {
            // EXCLUDE
            hrgSatuanDpp = hrg;
            hrgSatuanInc = hrg * (1 + (ppnPersen / 100));

            subtotalDpp = currentQtyNet * hrgSatuanDpp;
            nilaiPpn = (subtotalDpp * ppnPersen) / 100;
            totalTagihan = subtotalDpp + nilaiPpn;
        }

        $('#preview_hrg_satuan_dpp').text('Rp ' + hrgSatuanDpp.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + satuanText);
        $('#preview_hrg_satuan_inc').text('Rp ' + hrgSatuanInc.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + satuanText);
        $('#preview_dpp').text('Rp ' + subtotalDpp.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#preview_ppn').text('Rp ' + nilaiPpn.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#preview_total_tagihan').text('Rp ' + totalTagihan.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    // Submit Form Settlement
    $('#formSettlement').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitSettlement');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

        $.ajax({
            url: '<?= site_url("purchasing/konsinyasi_settlement/post") ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Posting Jurnal Pembelian');
                if (res.status) {
                    $('#modalSettlement').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diposting!',
                            text: res.message,
                            confirmButtonText: 'OK'
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        alert(res.message);
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Posting',
                            text: res.message
                        });
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Posting Jurnal Pembelian');
                alert('Terjadi kesalahan koneksi.');
            }
        });
    });

    // =========================================================================
    // HANDLER PENERIMAAN BARANG KONSINYASI MANDIRI
    // =========================================================================
    var barangsData = <?= json_encode($all_barangs) ?>;
    var rowIdx = 1;

    // Buka Modal Penerimaan Baru
    $('#btnTambahPenerimaan').on('click', function() {
        $('#formTambahPenerimaan')[0].reset();
        $('#tbodyItemPenerimaan').find('tr:gt(0)').remove();
        $('#in_nama_suplier').val('');
        $('#modalTambahPenerimaan').modal('show');
    });

    // Otomatis isi hidden input nama_suplier saat pilih supplier
    $('#in_kd_suplier').on('change', function() {
        var opt = $(this).find('option:selected');
        $('#in_nama_suplier').val(opt.data('nama') || '');
    });

    // Otomatis isi satuan dan nama_barang saat memilih barang di baris
    $(document).on('change', '.select-barang', function() {
        var $row = $(this).closest('tr');
        var opt = $(this).find('option:selected');
        $row.find('.in-nama-barang').val(opt.data('nama') || '');
        $row.find('.in-satuan').val(opt.data('satuan') || 'PCS');
    });

    // Tambah baris barang baru
    $('#btnAddItemRow').on('click', function() {
        var optionsHtml = '<option value="">-- Pilih Barang --</option>';
        for (var i = 0; i < barangsData.length; i++) {
            var b = barangsData[i];
            optionsHtml += '<option value="' + b.kode_barang + '" data-nama="' + b.nama_barang + '" data-satuan="' + (b.satuan || 'PCS') + '">' + b.nama_barang + ' (' + b.kode_barang + ')</option>';
        }

        var newRow = '<tr class="item-row">' +
            '<td>' +
                '<select name="items[' + rowIdx + '][kd_barang]" class="form-control form-control-sm select-barang" required>' +
                    optionsHtml +
                '</select>' +
                '<input type="hidden" name="items[' + rowIdx + '][nama_barang]" class="in-nama-barang">' +
            '</td>' +
            '<td><input type="number" step="any" min="0.001" name="items[' + rowIdx + '][qty]" class="form-control form-control-sm text-right font-weight-bold" placeholder="0.00" required></td>' +
            '<td><input type="text" name="items[' + rowIdx + '][satuan]" class="form-control form-control-sm in-satuan" value="PCS" readonly style="background: #f8fafc;"></td>' +
            '<td><input type="text" name="items[' + rowIdx + '][no_lot]" class="form-control form-control-sm" placeholder="No. Batch / Lot"></td>' +
            '<td><input type="date" name="items[' + rowIdx + '][expired_date]" class="form-control form-control-sm"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btnRemoveRow"><i class="fas fa-trash"></i></button></td>' +
        '</tr>';

        $('#tbodyItemPenerimaan').append(newRow);
        rowIdx++;
        updateRemoveButtons();
    });

    // Hapus baris item
    $(document).on('click', '.btnRemoveRow', function() {
        if ($('#tbodyItemPenerimaan tr').length > 1) {
            $(this).closest('tr').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        var rows = $('#tbodyItemPenerimaan tr');
        if (rows.length <= 1) {
            rows.find('.btnRemoveRow').prop('disabled', true);
        } else {
            rows.find('.btnRemoveRow').prop('disabled', false);
        }
    }

    // Submit Simpan Penerimaan Baru
    $('#formTambahPenerimaan').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitPenerimaan');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: '<?= site_url("purchasing/konsinyasi/ajax_save_penerimaan") ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Penerimaan Fisik');
                if (res.status) {
                    $('#modalTambahPenerimaan').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Penerimaan Disimpan!',
                            text: res.message,
                            confirmButtonText: 'OK'
                        }).then(function() {
                            window.location.href = '<?= site_url("purchasing/konsinyasi?tab=penerimaan") ?>';
                        });
                    } else {
                        alert(res.message);
                        window.location.href = '<?= site_url("purchasing/konsinyasi?tab=penerimaan") ?>';
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan',
                            text: res.message
                        });
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Penerimaan Fisik');
                alert('Terjadi kesalahan koneksi.');
            }
        });
    });

    // Detail Dokumen Penerimaan Konsinyasi
    $(document).on('click', '.btnViewPenerimaan', function() {
        var id = $(this).data('id');
        $('#modalDetailPenerimaanBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="text-muted mt-2">Memuat data dokumen...</p></div>');
        $('#modalDetailPenerimaan').modal('show');

        $.ajax({
            url: '<?= site_url("purchasing/konsinyasi/ajax_detail_penerimaan") ?>',
            type: 'GET',
            data: { id_masuk: id },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    var d = res.data;
                    var html = '';
                    html += '<div class="row mb-3">';
                    html += '<div class="col-md-6">';
                    html += '<table class="table table-sm table-borderless small mb-0">';
                    html += '<tr><td class="text-muted" style="width:40%;">No. Penerimaan:</td><td class="font-weight-bold text-success">' + d.nomor_masuk + '</td></tr>';
                    html += '<tr><td class="text-muted">Tanggal Masuk:</td><td class="font-weight-bold">' + new Date(d.tanggal_masuk).toLocaleDateString("id-ID", {day:"2-digit",month:"long",year:"numeric"}) + '</td></tr>';
                    html += '<tr><td class="text-muted">Gudang Penyimpanan:</td><td><span class="badge badge-light border">' + (d.nama_gudang || 'Gdg. Konsinyasi') + '</span></td></tr>';
                    html += '</table></div>';
                    html += '<div class="col-md-6">';
                    html += '<table class="table table-sm table-borderless small mb-0">';
                    html += '<tr><td class="text-muted" style="width:40%;">Supplier:</td><td class="font-weight-bold text-dark">' + d.nama_suplier + ' (' + d.kd_suplier + ')</td></tr>';
                    html += '<tr><td class="text-muted">Surat Jalan:</td><td>' + (d.no_surat_jalan || '-') + '</td></tr>';
                    html += '<tr><td class="text-muted">Dicatat oleh:</td><td>' + (d.created_by || '-') + ' pada ' + new Date(d.created_at).toLocaleString("id-ID") + '</td></tr>';
                    html += '</table></div></div>';

                    html += '<h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-boxes text-success mr-1"></i> Rincian Barang Fisik yang Masuk</h6>';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-sm table-bordered mb-0 small">';
                    html += '<thead class="bg-light font-weight-bold"><tr><th>Kode</th><th>Nama Barang</th><th class="text-right">Qty</th><th>Satuan</th><th>Batch/Lot</th><th>Expired Date</th></tr></thead>';
                    html += '<tbody>';
                    if (d.items && d.items.length > 0) {
                        for (var j = 0; j < d.items.length; j++) {
                            var it = d.items[j];
                            html += '<tr>';
                            html += '<td class="font-weight-bold">' + it.kd_barang + '</td>';
                            html += '<td>' + it.nama_barang + '</td>';
                            html += '<td class="text-right font-weight-bold text-primary">' + parseFloat(it.qty).toLocaleString("id-ID", {minimumFractionDigits: 2}) + '</td>';
                            html += '<td>' + (it.satuan || 'PCS') + '</td>';
                            html += '<td>' + (it.no_lot || '-') + '</td>';
                            html += '<td>' + (it.expired_date ? new Date(it.expired_date).toLocaleDateString("id-ID") : '-') + '</td>';
                            html += '</tr>';
                        }
                    }
                    html += '</tbody></table></div>';

                    if (d.keterangan) {
                        html += '<div class="mt-3 p-2 bg-light rounded small text-muted"><i class="fas fa-sticky-note mr-1"></i>Catatan: ' + d.keterangan + '</div>';
                    }

                    $('#modalDetailPenerimaanBody').html(html);
                } else {
                    $('#modalDetailPenerimaanBody').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
            },
            error: function() {
                $('#modalDetailPenerimaanBody').html('<div class="alert alert-danger">Terjadi kesalahan koneksi.</div>');
            }
        });
    });
});
</script>
