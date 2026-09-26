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
                        <i class="fas fa-handshake text-primary mr-2"></i> Penyelesaian Barang Konsinyasi
                    </h1>
                    <p class="text-muted mb-0 small">Pengelolaan penyelesaian tagihan konsinyasi: pengakuan hutang dan HPP atas barang titipan supplier yang telah laku/dibayar pelanggan (Penerimaan fisik barang melalui PO &amp; LPB di Logistik)</p>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-primary shadow-sm font-weight-bold" id="btnSyncKonsinyasi">
                        <i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Penjualan Konsinyasi
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
                                    <span class="text-muted font-weight-bold small text-uppercase">Alur Penerimaan Fisik</span>
                                    <h5 class="mb-0 font-weight-bold text-primary mt-1">PO &amp; LPB Konsinyasi</h5>
                                    <small class="text-muted">Masuk melalui Logistik (Data LPB: ics/data_lpb)</small>
                                </div>
                                <div class="p-3 rounded-circle text-primary" style="background: #dbeafe;">
                                    <i class="fas fa-dolly-flatbed fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- DAFTAR PENYELESAIAN TAGIHAN BARANG KONSINYASI (SETTLEMENT) -->
            <!-- ========================================================================= -->
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
                                        <tr class="context-settlement-row" 
                                            data-id="<?= (int) $s['id_settlement'] ?>" 
                                            data-status="<?= htmlspecialchars($s['status']) ?>"
                                            data-no-settle="<?= htmlspecialchars($s['no_settlement']) ?>"
                                            data-has-journal="<?= (!empty($s['id_jurnal_pembelian']) || !empty($s['nomor_jurnal'])) ? '1' : '0' ?>"
                                            data-no-jurnal="<?= htmlspecialchars($s['nomor_jurnal'] ?? '') ?>"
                                            title="Klik kanan baris ini untuk opsi transaksi / lihat jurnal">
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
                                                            <br><a href="javascript:void(0)" class="badge badge-info mt-1 py-1 px-2 text-white shadow-xs" onclick="openModalViewJournal(<?= (int) $s['id_settlement'] ?>)" title="Klik untuk lihat voucher jurnal (Bisa juga Klik Kanan baris ini)"><i class="fas fa-book mr-1"></i><?= htmlspecialchars($s['nomor_jurnal']) ?></a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small font-italic">Belum diinput</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($s['status'] === 'PENDING'): ?>
                                                    <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm btnProcessSettlement" id="btn-settle-<?= (int) $s['id_settlement'] ?>" data-id="<?= (int) $s['id_settlement'] ?>" onclick="openModalInputTagihan(<?= (int) $s['id_settlement'] ?>)">
                                                        <i class="fas fa-file-invoice-dollar mr-1"></i> Input Tagihan
                                                    </button>
                                                <?php else: ?>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary font-weight-bold btnViewSettlement" id="btn-view-<?= (int) $s['id_settlement'] ?>" data-id="<?= (int) $s['id_settlement'] ?>" onclick="openModalDetailSettlement(<?= (int) $s['id_settlement'] ?>)" title="Lihat Detail Settlement">
                                                            <i class="fas fa-eye mr-1"></i> Detail
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info font-weight-bold" onclick="openModalViewJournal(<?= (int) $s['id_settlement'] ?>)" title="Lihat Jurnal Pembelian (Atau Klik Kanan baris)">
                                                            <i class="fas fa-book"></i> Jurnal
                                                        </button>
                                                    </div>
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

            <!-- Hint Klik Kanan untuk pengguna -->
            <div class="text-muted small text-right mt-1 mb-3">
                <i class="fas fa-mouse text-primary mr-1"></i> <em>Tip: Klik kanan pada baris tabel barang yang sudah ditagih untuk langsung membuka menu <strong>Lihat Jurnal Pembelian</strong>.</em>
            </div>

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
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
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

<!-- Modal Lihat Jurnal Pembelian Konsinyasi -->
<div class="modal fade" id="modalViewJournal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 14px 14px 0 0;">
                <div class="d-flex align-items-center">
                    <div class="p-2 rounded mr-3" style="background: rgba(255,255,255,0.1);">
                        <i class="fas fa-book fa-lg text-info"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0">Voucher Jurnal Pembelian Konsinyasi</h5>
                        <small class="text-white-50">Pengakuan Beban Pokok Pendapatan (HPP) &amp; Hutang Supplier Konsinyasi</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="modalViewJournalBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2">Memuat voucher jurnal...</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 d-flex justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-check-circle text-success mr-1"></i> Transaksi terdaftar sah pada General Ledger (GL) Karisma ERP
                </small>
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Context Menu Klik Kanan Transaksi Konsinyasi -->
<div id="settlementContextMenu" class="shadow-lg" style="display: none; position: absolute; z-index: 1055; min-width: 230px; background: #ffffff; border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 10px 30px rgba(15,23,42,0.18);">
    <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between" style="background: #f8fafc; border-radius: 10px 10px 0 0;">
        <span class="font-weight-bold text-dark small text-truncate mr-2" id="ctxMenuHeader">
            <i class="fas fa-receipt text-primary mr-1"></i> Opsi Transaksi
        </span>
        <span class="badge badge-success px-2 py-1" id="ctxMenuStatus">BILLED</span>
    </div>
    <div class="py-1">
        <a class="dropdown-item py-2 px-3 d-flex align-items-center ctx-item-journal text-info font-weight-bold" href="javascript:void(0)" onclick="ctxTriggerViewJournal()" style="font-size: 0.90rem;">
            <i class="fas fa-book mr-2 fa-fw"></i> Lihat Jurnal Pembelian
        </a>
        <a class="dropdown-item py-2 px-3 d-flex align-items-center ctx-item-detail text-dark" href="javascript:void(0)" onclick="ctxTriggerViewDetail()" style="font-size: 0.90rem;">
            <i class="fas fa-eye mr-2 fa-fw text-secondary"></i> Detail Settlement
        </a>
        <a class="dropdown-item py-2 px-3 d-flex align-items-center ctx-item-input text-warning font-weight-bold" href="javascript:void(0)" onclick="ctxTriggerInputTagihan()" style="display: none; font-size: 0.90rem;">
            <i class="fas fa-file-invoice-dollar mr-2 fa-fw text-warning"></i> Input Tagihan Supplier
        </a>
    </div>
</div>

<script>
// Helper modal aman
function showModalSafe(modalId) {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal === 'function') {
        jQuery('#' + modalId).modal('show');
    } else {
        var el = document.getElementById(modalId);
        if (el) {
            el.classList.add('show');
            el.style.display = 'block';
            el.removeAttribute('aria-hidden');
            el.setAttribute('aria-modal', 'true');
            if (!document.querySelector('.modal-backdrop')) {
                var bd = document.createElement('div');
                bd.className = 'modal-backdrop fade show';
                document.body.appendChild(bd);
            }
        }
    }
}

function hideModalSafe(modalId) {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.modal === 'function') {
        jQuery('#' + modalId).modal('hide');
    } else {
        var el = document.getElementById(modalId);
        if (el) {
            el.classList.remove('show');
            el.style.display = 'none';
            el.setAttribute('aria-hidden', 'true');
            el.removeAttribute('aria-modal');
            var bds = document.querySelectorAll('.modal-backdrop');
            bds.forEach(function(b) { b.remove(); });
        }
    }
}

// Global state
var currentQtyNet = 0;
var currentSatuan = '';
var selectedContextId = null;
var selectedContextStatus = null;

// Fungsi kalkulasi rincian live
window.calculateSettlementLive = function() {
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
};

// Global function untuk tombol "Input Tagihan"
window.openModalInputTagihan = function(id) {
    var $btn = $('#btn-settle-' + id);
    var oldHtml = $btn.html();
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...');

    $.ajax({
        url: '<?= site_url("purchasing/konsinyasi/ajax_detail") ?>',
        type: 'GET',
        data: { id_settlement: id },
        dataType: 'json',
        success: function(res) {
            $btn.prop('disabled', false).html(oldHtml);
            if (res && res.status && res.data) {
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

                window.calculateSettlementLive();
                $('#catatan').val('');

                showModalSafe('modalSettlement');
            } else {
                var msg = (res && res.message) ? res.message : 'Gagal mengambil data transaksi settlement.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: msg });
                } else {
                    alert(msg);
                }
            }
        },
        error: function(xhr, status, error) {
            $btn.prop('disabled', false).html(oldHtml);
            var errMsg = 'Terjadi kesalahan sistem saat mengambil data tagihan (' + status + ').';
            if (xhr.status === 401) {
                errMsg = 'Sesi login telah berakhir. Silakan login kembali.';
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: errMsg });
            } else {
                alert(errMsg);
            }
        }
    });
};

// Global function untuk tombol "Detail"
window.openModalDetailSettlement = function(id) {
    $('#modalDetailBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="text-muted mt-2">Memuat data...</p></div>');
    showModalSafe('modalDetailSettlement');

    $.ajax({
        url: '<?= site_url("purchasing/konsinyasi/ajax_detail") ?>',
        type: 'GET',
        data: { id_settlement: id },
        dataType: 'json',
        success: function(res) {
            if (res && res.status && res.data) {
                var d = res.data;
                var fmt = function(n) { return 'Rp ' + parseFloat(n || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
                var tipeBadge = '';
                if (d.tipe_pajak === 'INCLUDE') tipeBadge = '<span class="badge badge-success ml-1">Include PPN</span>';
                else if (d.tipe_pajak === 'EXCLUDE') tipeBadge = '<span class="badge badge-primary ml-1">Exclude PPN</span>';
                else tipeBadge = '<span class="badge badge-secondary ml-1">Non-PPN</span>';

                var html = '';
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

                if (d.nomor_jurnal) {
                    html += '<div class="mt-3 d-flex align-items-center justify-content-between p-2 rounded bg-light border">';
                    html += '<span class="badge badge-info px-3 py-2" style="font-size:0.9rem; cursor:pointer;" onclick="openModalViewJournal(' + d.id_settlement + ')"><i class="fas fa-book mr-1"></i>Jurnal: ' + d.nomor_jurnal + ' <small>(Klik untuk rincian)</small></span>';
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
                $('#modalDetailBody').html('<div class="alert alert-danger">Gagal memuat data detail settlement.</div>');
            }
        },
        error: function(xhr, status, error) {
            $('#modalDetailBody').html('<div class="alert alert-danger">Terjadi kesalahan koneksi (' + status + ').</div>');
        }
    });
};

// Global function untuk tombol "Lihat Jurnal"
window.openModalViewJournal = function(id) {
    $('#settlementContextMenu').hide();
    $('#modalViewJournalBody').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><p class="text-muted mt-2">Memuat rincian voucher jurnal...</p></div>');
    showModalSafe('modalViewJournal');

    $.ajax({
        url: '<?= site_url("purchasing/konsinyasi/ajax_view_journal") ?>',
        type: 'GET',
        data: { id_settlement: id },
        dataType: 'json',
        success: function(res) {
            if (res && res.status && res.header) {
                var h = res.header;
                var s = res.settlement || {};
                var details = res.details || [];
                var fmt = function(n) { return 'Rp ' + parseFloat(n || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

                var html = '';

                // Header Card Ringkasan Jurnal
                html += '<div class="card border-0 bg-light mb-3" style="border-radius:10px;">';
                html += '<div class="card-body p-3">';
                html += '<div class="row align-items-center">';
                html += '<div class="col-md-6">';
                html += '<span class="text-muted small text-uppercase font-weight-bold">Nomor Jurnal Akuntansi</span>';
                html += '<h4 class="font-weight-bold text-primary mb-1"><i class="fas fa-file-invoice mr-2"></i>' + (h.nomor_jurnal || '-') + '</h4>';
                html += '<small class="text-muted">Tipe: <strong>' + (h.journal_type || 'PJ') + ' (Pembelian)</strong> | Tanggal: <strong>' + (h.tanggal_transaksi ? new Date(h.tanggal_transaksi).toLocaleDateString("id-ID", {day:"2-digit",month:"long",year:"numeric"}) : '-') + '</strong></small>';
                html += '</div>';
                html += '<div class="col-md-6 text-md-right mt-2 mt-md-0">';
                html += '<span class="text-muted small text-uppercase font-weight-bold">Status Jurnal</span><br>';
                html += '<span class="badge badge-success px-3 py-1 font-weight-bold shadow-xs"><i class="fas fa-check-double mr-1"></i>POSTED (Terposting)</span>';
                html += '<div class="small text-muted mt-1">Source: <strong>' + (h.source_type || 'CONSIGNMENT_SETTLEMENT') + '</strong></div>';
                html += '</div>';
                html += '</div>';

                // Keterangan Jurnal
                html += '<div class="mt-2 pt-2 border-top small text-muted">';
                html += '<i class="fas fa-align-left mr-1"></i> <strong>Keterangan:</strong> ' + (h.keterangan || '-') + '<br>';
                html += '<i class="fas fa-link mr-1"></i> Ref: Settlement <strong>' + (s.no_settlement || '-') + '</strong> | Inv Supplier: <strong>' + (s.no_invoice_supplier || '-') + '</strong> | Supplier: <strong>' + (s.nama_suplier || '-') + '</strong>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                // Tabel Rincian Debit / Kredit
                html += '<div class="table-responsive">';
                html += '<table class="table table-bordered table-striped align-middle mb-0" style="font-size:0.88rem;">';
                html += '<thead style="background:#1e293b; color:#fff;">';
                html += '<tr>';
                html += '<th class="text-center py-2" style="width:40px;">No</th>';
                html += '<th class="py-2" style="width:110px;">Kode Akun</th>';
                html += '<th class="py-2">Nama Akun & Keterangan</th>';
                html += '<th class="text-right py-2" style="width:150px;">Debit (Rp)</th>';
                html += '<th class="text-right py-2" style="width:150px;">Kredit (Rp)</th>';
                html += '</tr>';
                html += '</thead>';
                html += '<tbody>';

                var totalDebit = 0;
                var totalKredit = 0;

                if (details.length > 0) {
                    $.each(details, function(idx, row) {
                        var d = parseFloat(row.debit || 0);
                        var k = parseFloat(row.kredit || 0);
                        totalDebit += d;
                        totalKredit += k;

                        html += '<tr>';
                        html += '<td class="text-center font-weight-bold text-muted">' + (row.nomor_baris || (idx + 1)) + '</td>';
                        html += '<td><span class="badge badge-light border font-weight-bold px-2 py-1 text-dark">' + (row.kode_akun || '-') + '</span></td>';
                        html += '<td>';
                        html += '<strong class="text-dark">' + (row.nama_akun || row.keterangan || '-') + '</strong>';
                        if (row.keterangan && row.keterangan !== row.nama_akun) {
                            html += '<br><small class="text-muted">' + row.keterangan + '</small>';
                        }
                        html += '</td>';
                        html += '<td class="text-right font-weight-bold text-dark">' + (d > 0 ? fmt(d) : '-') + '</td>';
                        html += '<td class="text-right font-weight-bold text-dark">' + (k > 0 ? fmt(k) : '-') + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="5" class="text-center py-3 text-muted">Detail baris jurnal tidak ditemukan.</td></tr>';
                }

                html += '</tbody>';
                html += '<tfoot style="background:#f8fafc; font-size:0.95rem;">';
                html += '<tr>';
                html += '<th colspan="3" class="text-right font-weight-bold text-dark">TOTAL:</th>';
                html += '<th class="text-right font-weight-bold text-primary">' + fmt(totalDebit) + '</th>';
                html += '<th class="text-right font-weight-bold text-primary">' + fmt(totalKredit) + '</th>';
                html += '</tr>';

                var isBalanced = Math.abs(totalDebit - totalKredit) < 0.01;
                html += '<tr>';
                html += '<th colspan="3" class="text-right small text-muted">Status Keseimbangan:</th>';
                html += '<th colspan="2" class="text-right">';
                if (isBalanced) {
                    html += '<span class="badge badge-success px-3 py-1 font-weight-bold"><i class="fas fa-check mr-1"></i>BALANCE (Seimbang)</span>';
                } else {
                    html += '<span class="badge badge-danger px-3 py-1 font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>SELISIH (Unbalanced)</span>';
                }
                html += '</th>';
                html += '</tr>';

                html += '</tfoot>';
                html += '</table>';
                html += '</div>';

                $('#modalViewJournalBody').html(html);
            } else {
                var errMsg = (res && res.message) ? res.message : 'Voucher jurnal belum terbentuk untuk transaksi ini.';
                $('#modalViewJournalBody').html('<div class="alert alert-warning py-3 text-center"><i class="fas fa-info-circle fa-2x mb-2 d-block text-warning"></i><strong>' + errMsg + '</strong></div>');
            }
        },
        error: function(xhr, status, error) {
            var msg = xhr.status === 401 ? 'Sesi berakhir, silakan login kembali.' : 'Terjadi kesalahan sistem saat memuat jurnal.';
            $('#modalViewJournalBody').html('<div class="alert alert-danger py-3 text-center"><i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>' + msg + '</div>');
        }
    });
};

// Handlers untuk Context Menu
window.ctxTriggerViewJournal = function() {
    if (selectedContextId) {
        window.openModalViewJournal(selectedContextId);
    }
};

window.ctxTriggerViewDetail = function() {
    if (selectedContextId) {
        $('#settlementContextMenu').hide();
        window.openModalDetailSettlement(selectedContextId);
    }
};

window.ctxTriggerInputTagihan = function() {
    if (selectedContextId) {
        $('#settlementContextMenu').hide();
        window.openModalInputTagihan(selectedContextId);
    }
};

// Event bindings saat DOM siap
$(document).ready(function() {
    // Tombol Sinkronisasi Penjualan Konsinyasi
    $('#btnSyncKonsinyasi').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyinkronkan...');

        $.ajax({
            url: '<?= site_url("purchasing/konsinyasi/ajax_sync") ?>',
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Penjualan Konsinyasi');
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
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Penjualan Konsinyasi');
                var msg = xhr.status === 401 ? 'Sesi berakhir, silakan login kembali.' : 'Terjadi kesalahan saat sinkronisasi.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                } else {
                    alert(msg);
                }
            }
        });
    });

    // Delegation click untuk tombol Input Tagihan & Detail
    $(document).on('click', '.btnProcessSettlement', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) {
            window.openModalInputTagihan(id);
        }
    });

    $(document).on('click', '.btnViewSettlement', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) {
            window.openModalDetailSettlement(id);
        }
    });

    // =========================================================================
    // CONTEXT MENU (KLIK KANAN PADA BARIS BARANG KONSINYASI)
    // =========================================================================
    $(document).on('contextmenu', 'tr.context-settlement-row', function(e) {
        e.preventDefault();
        var $tr = $(this);
        var id = $tr.data('id');
        var status = $tr.data('status');
        var noSettle = $tr.data('no-settle');
        var hasJournal = $tr.data('has-journal');
        var noJurnal = $tr.data('no-jurnal');

        selectedContextId = id;
        selectedContextStatus = status;

        // Atur header context menu
        $('#ctxMenuHeader').html('<i class="fas fa-receipt mr-1 text-primary"></i> ' + (noSettle || 'Opsi'));
        if (status === 'BILLED') {
            $('#ctxMenuStatus').removeClass('badge-warning').addClass('badge-success').text('BILLED');
            $('.ctx-item-journal').show();
            if (noJurnal) {
                $('.ctx-item-journal').html('<i class="fas fa-book mr-2 fa-fw text-info"></i> Lihat Jurnal: <strong>' + noJurnal + '</strong>');
            } else {
                $('.ctx-item-journal').html('<i class="fas fa-book mr-2 fa-fw text-info"></i> Lihat Jurnal Pembelian');
            }
            $('.ctx-item-input').hide();
        } else {
            $('#ctxMenuStatus').removeClass('badge-success').addClass('badge-warning').text('PENDING');
            if (hasJournal == '1') {
                $('.ctx-item-journal').show().html('<i class="fas fa-book mr-2 fa-fw text-info"></i> Lihat Jurnal Pembelian');
            } else {
                $('.ctx-item-journal').hide();
            }
            $('.ctx-item-input').show();
        }

        // Posisi menu pada koordinat mouse
        var x = e.pageX;
        var y = e.pageY;

        // Deteksi batas layar kanan & bawah agar tidak terpotong
        var menuW = 240;
        var menuH = 140;
        var winW = $(window).width();
        var winH = $(window).height();

        if (x + menuW > winW) {
            x = winW - menuW - 15;
        }

        $('#settlementContextMenu').css({
            top: y + 'px',
            left: x + 'px',
            display: 'block'
        });
    });

    // Sembunyikan context menu saat klik kiri di sembarang tempat atau saat scroll
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#settlementContextMenu').length) {
            $('#settlementContextMenu').hide();
        }
    });

    $(window).on('scroll', function() {
        $('#settlementContextMenu').hide();
    });

    // Close modal listener untuk data-dismiss
    $(document).on('click', '[data-dismiss="modal"]', function() {
        var $m = $(this).closest('.modal');
        if ($m.length) {
            hideModalSafe($m.attr('id'));
        }
    });

    // Event ganti tipe pajak atau ketik harga satuan
    $(document).on('change', 'input[name="tipe_pajak"]', function() {
        window.calculateSettlementLive();
    });
    $(document).on('input change', '#hrg_satuan_input', function() {
        window.calculateSettlementLive();
    });

    // Submit Form Settlement
    $('#formSettlement').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitSettlement');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

        $.ajax({
            url: '<?= site_url("purchasing/konsinyasi/ajax_post_settlement") ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Posting Jurnal Pembelian');
                if (res && res.status) {
                    hideModalSafe('modalSettlement');
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
                    var errMsg = (res && res.message) ? res.message : 'Gagal memproses jurnal pembelian.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Posting',
                            text: errMsg
                        });
                    } else {
                        alert(errMsg);
                    }
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Posting Jurnal Pembelian');
                var errMsg = xhr.status === 401 ? 'Sesi login telah berakhir.' : 'Terjadi kesalahan sistem (' + status + ').';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: errMsg });
                } else {
                    alert(errMsg);
                }
            }
        });
    });
});
</script>
