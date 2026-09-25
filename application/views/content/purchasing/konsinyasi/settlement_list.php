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
                <div class="col-sm-7">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-handshake text-primary mr-2"></i> Penyelesaian Barang Konsinyasi
                    </h1>
                    <p class="text-muted mb-0 small">Rekonsiliasi barang titipan supplier yang telah terjual dan pengakuan hutang resmi dari invoice supplier</p>
                </div>
                <div class="col-sm-5 text-right">
                    <button type="button" class="btn btn-outline-primary shadow-sm font-weight-bold mr-2" id="btnSyncKonsinyasi">
                        <i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Penjualan
                    </button>
                    <a href="<?= site_url('ics/lpb_manual') ?>" class="btn btn-success shadow-sm font-weight-bold">
                        <i class="fas fa-plus-circle mr-1"></i> Input LPB Konsinyasi
                    </a>
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

            <!-- Filter Bar -->
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form method="get" action="<?= site_url('purchasing/konsinyasi_settlement') ?>" class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-muted mb-1">Status Settlement</label>
                            <select name="status" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()">
                                <option value="PENDING" <?= ($filters['status'] == 'PENDING') ? 'selected' : '' ?>>⏳ Menunggu Tagihan Supplier (Pending)</option>
                                <option value="BILLED" <?= ($filters['status'] == 'BILLED') ? 'selected' : '' ?>>✅ Selesai Dijurnal (Billed)</option>
                                <option value="SEMUA" <?= ($filters['status'] == 'SEMUA') ? 'selected' : '' ?>>Semua Status</option>
                            </select>
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-muted mb-1">Pencarian Barang / Customer / SO / Invoice</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama barang, customer, no SO..." value="<?= htmlspecialchars($filters['search']) ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-right pt-2">
                            <?php if (!empty($filters['search']) || $filters['status'] !== 'PENDING' || $filters['kd_suplier'] !== 'SEMUA'): ?>
                                <a href="<?= site_url('purchasing/konsinyasi_settlement') ?>" class="btn btn-sm btn-outline-danger btn-block">
                                    <i class="fas fa-times-circle mr-1"></i> Reset Filter
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
                            <i class="fas fa-list text-muted mr-1"></i> Daftar Barang Konsinyasi Terjual
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

                    <div class="row">
                        <div class="col-md-7 form-group mb-3">
                            <label class="small font-weight-bold text-dark" id="label_hrg_satuan">Harga Satuan Exclude PPN (Rp) <span class="text-danger">*</span></label>
                            <input type="number" step="any" min="1" name="hrg_satuan_input" id="hrg_satuan_input" class="form-control font-weight-bold text-right" placeholder="0" required>
                        </div>
                        <div class="col-md-5 form-group mb-3">
                            <label class="small font-weight-bold text-dark">Tarif PPN</label>
                            <select name="ppn_persen" id="ppn_persen" class="form-control font-weight-bold">
                                <option value="11" selected>PPN 11%</option>
                                <option value="12">PPN 12%</option>
                                <option value="0">Non-PPN (0%)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Live Breakdown Box -->
                    <div class="p-3 mb-3 rounded" style="background: #f1f5f9; border: 1px solid #cbd5e1;">
                        <span class="small font-weight-bold text-muted text-uppercase d-block mb-2">
                            <i class="fas fa-calculator mr-1"></i> Rincian Otomatis Sistem
                        </span>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Subtotal DPP (Masuk HPP):</span>
                            <span class="font-weight-bold text-dark" id="preview_dpp">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Nilai PPN Masukan:</span>
                            <span class="font-weight-bold text-dark" id="preview_ppn">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                            <strong class="text-dark">Total Hutang ke Supplier:</strong>
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

<script>
$(document).ready(function() {
    var currentQtyNet = 0;

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

    // Buka Modal Input Tagihan
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
                    $('#hrg_satuan_input').val('');
                    $('#no_invoice_supplier').val('');
                    
                    // Reset ke default Exclude PPN 11%
                    $('input[name="tipe_pajak"][value="EXCLUDE"]').prop('checked', true);
                    $('#lbl_tipe_exclude').addClass('active').siblings().removeClass('active');
                    $('#ppn_persen').val('11').prop('disabled', false);

                    calculateSettlementLive();
                    $('#catatan').val('');

                    $('#modalSettlement').modal('show');
                } else {
                    alert(res.message || 'Gagal mengambil data.');
                }
            }
        });
    });

    // Event ganti tipe pajak atau ketik harga / ganti tarif PPN
    $('input[name="tipe_pajak"]').on('change', function() {
        calculateSettlementLive();
    });
    $('#hrg_satuan_input, #ppn_persen').on('input change', function() {
        calculateSettlementLive();
    });

    function calculateSettlementLive() {
        var tipe = $('input[name="tipe_pajak"]:checked').val() || 'EXCLUDE';
        var hrg = parseFloat($('#hrg_satuan_input').val()) || 0;
        var ppnPersen = parseFloat($('#ppn_persen').val()) || 0;

        if (tipe === 'NON_PPN') {
            $('#ppn_persen').val('0').prop('disabled', true);
            ppnPersen = 0;
            $('#label_hrg_satuan').text('Harga Satuan Non-PPN (Rp) *');
            $('#tipe_pajak_help').html('Harga murni tanpa PPN. Jurnal HPP dan Utang dicatat sebesar harga ini.');
        } else if (tipe === 'INCLUDE') {
            if ($('#ppn_persen').val() == '0') {
                $('#ppn_persen').val('11');
                ppnPersen = 11;
            }
            $('#ppn_persen').prop('disabled', false);
            $('#label_hrg_satuan').text('Harga Satuan Include PPN (Rp) *');
            $('#tipe_pajak_help').html('Ketikkan harga include di faktur supplier. <strong>Sistem otomatis mengekstrak DPP dan PPN Masukan</strong>.');
        } else {
            // EXCLUDE
            if ($('#ppn_persen').val() == '0') {
                $('#ppn_persen').val('11');
                ppnPersen = 11;
            }
            $('#ppn_persen').prop('disabled', false);
            $('#label_hrg_satuan').text('Harga Satuan Exclude PPN (Rp) *');
            $('#tipe_pajak_help').html('Ketikkan harga DPP sebelum PPN. PPN akan ditambahkan otomatis ke total tagihan.');
        }

        var totalTagihan = 0;
        var subtotalDpp = 0;
        var nilaiPpn = 0;

        if (tipe === 'INCLUDE') {
            totalTagihan = currentQtyNet * hrg;
            var divider = 1 + (ppnPersen / 100);
            subtotalDpp = (ppnPersen > 0) ? (totalTagihan / divider) : totalTagihan;
            nilaiPpn = totalTagihan - subtotalDpp;
        } else if (tipe === 'NON_PPN') {
            subtotalDpp = currentQtyNet * hrg;
            nilaiPpn = 0;
            totalTagihan = subtotalDpp;
        } else {
            subtotalDpp = currentQtyNet * hrg;
            nilaiPpn = (subtotalDpp * ppnPersen) / 100;
            totalTagihan = subtotalDpp + nilaiPpn;
        }

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
});
</script>
