<link rel="stylesheet" href="<?= base_url('assets/dist/css/retur-custom.css') ?>"><?php /* views/content/sales/retur/retur_collection.php */ ?>
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-handshake mr-2 text-info"></i> Team Collection: Proses Retur</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan/retur') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Proses Collection</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- INFO RETUR -->
                <div class="card shadow mb-3">
                    <div class="card-header bg-info text-white py-2">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-file-invoice mr-1"></i> Retur: <?= htmlspecialchars($retur['no_retur']) ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                 <table class="table table-sm table-borderless" style="font-size:13px;">
                                     <tr><td class="font-weight-bold" style="width:130px;">No. Retur</td><td>: <?= htmlspecialchars($retur['no_retur']) ?></td></tr>
                                     <tr><td class="font-weight-bold">Dari SPR</td><td>: <?= htmlspecialchars($retur['no_spr'] ?? '-') ?></td></tr>
                                     <tr><td class="font-weight-bold">Customer</td><td>: <strong><?= htmlspecialchars($retur['nama_customer'] ?: $retur['nama_customer_master'] ?: '-') ?></strong></td></tr>
                                     <tr>
                                         <td class="font-weight-bold">Tipe Retur</td>
                                         <td>: 
                                             <?php if (($retur['tipe_retur'] ?? 'biasa') === 'replace'): ?>
                                                 <span class="badge badge-success px-2 py-1">REPLACE (Ganti Barang)</span>
                                             <?php elseif (($retur['tipe_retur'] ?? 'biasa') === 'service'): ?>
                                                 <span class="badge badge-warning px-2 py-1">SERVICE (Servis Barang)</span>
                                             <?php else: ?>
                                                 <span class="badge badge-secondary px-2 py-1">RETUR (Refund/Potong Faktur)</span>
                                             <?php endif; ?>
                                         </td>
                                     </tr>
                                 </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless" style="font-size:13px;">
                                    <tr><td class="font-weight-bold" style="width:130px;">Tanggal</td><td>: <?= date('d/m/Y', strtotime($retur['tanggal_retur'])) ?></td></tr>
                                    <tr><td class="font-weight-bold">Sales</td><td>: <?= htmlspecialchars($retur['nama_sales'] ?? '-') ?></td></tr>
                                    <tr><td class="font-weight-bold">Dibuat Oleh</td><td>: <?= htmlspecialchars($retur['create_by_retur'] ?? '-') ?></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABEL BARANG -->
                <div class="card mb-3">
                    <div class="card-header bg-dark text-white py-2">
                        <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Detail Barang Retur</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center" style="width:40px;">No.</th>
                                        <th>Nama Barang</th>
                                        <th>Satuan</th>
                                        <th>No. Faktur</th>
                                        <th>No. Batch</th>
                                        <th class="text-center">Exp. Date</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($retur_detail as $i => $d):
                                        $subtotal = (float)$d['qty_retur'] * (float)$d['harga_satuan'];
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($d['nama_barang'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($d['satuan'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($d['no_faktur'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($d['no_batch'] ?? '-') ?></td>
                                        <td class="text-center"><?= !empty($d['expired_date']) ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?></td>
                                        <td class="text-center"><?= number_format((float)$d['qty_retur'], 3) ?></td>
                                        <td class="text-right">Rp <?= number_format((float)$d['harga_satuan'], 0, ',', '.') ?></td>
                                        <td class="text-right">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-dark">
                                        <td colspan="8" class="text-right font-weight-bold">TOTAL NILAI RETUR:</td>
                                        <td class="text-right font-weight-bold">Rp <?= number_format($total_retur, 0, ',', '.') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- FORM COLLECTION -->
                <form method="post" action="<?= base_url('retur_penjualan/retur/collection_simpan/' . $retur['id_retur']) ?>">
                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <?php endif; ?>
                    <div class="card shadow">
                        <div class="card-header bg-info text-white py-2">
                            <h3 class="card-title"><i class="fas fa-file-signature mr-1"></i> Penyerahan Surat Retur & Alokasi Faktur Potong</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success py-2 mb-3 small">
                                <i class="fas fa-check-circle mr-1"></i>
                                <strong>Status Retur: Approved.</strong> Saldo customer untuk retur ini sudah aktif (cair). Silakan masukkan nomor faktur tujuan pemotongan di bawah ini untuk diserahkan ke bagian Keuangan/Kasir.
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">No. Faktur Potong <span class="text-danger">*</span></label>
                                <input type="text" name="no_faktur_potong" class="form-control" required placeholder="Isi No. Faktur Potong yang akan dikurangi nilainya...">
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Catatan Penyerahan (Collection)</label>
                                <textarea name="catatan_collection" class="form-control" rows="3" placeholder="Catatan/keterangan penyerahan surat retur..."></textarea>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-secondary mr-2">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success"
                                    onclick="return confirm('Serahkan surat retur ini ke Kasir?')">
                                <i class="fas fa-paper-plane mr-1"></i> Serahkan ke Kasir
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
