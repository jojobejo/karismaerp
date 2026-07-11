<?php /* views/content/sales/retur/retur_verifikasi.php */ ?>
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
                        <h1 class="m-0"><i class="fas fa-clipboard-check mr-2 text-warning"></i> Admin Stock: Verifikasi Retur</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan/retur') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Verifikasi</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <div class="card shadow mb-3">
                    <div class="card-header bg-warning py-2">
                        <h3 class="card-title font-weight-bold">Verifikasi Retur: <?= htmlspecialchars($retur['no_retur']) ?></h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless" style="font-size:13px;">
                            <tr>
                                <td style="width:130px;" class="font-weight-bold">No. Retur</td>
                                <td>: <?= htmlspecialchars($retur['no_retur']) ?></td>
                                <td style="width:130px;" class="font-weight-bold">Dari SPR</td>
                                <td>: <?= htmlspecialchars($retur['no_spr'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Customer</td>
                                <td>: <?= htmlspecialchars($retur['nama_customer'] ?: $retur['nama_customer_master'] ?: '-') ?></td>
                                <td class="font-weight-bold">Tanggal</td>
                                <td>: <?= date('d/m/Y', strtotime($retur['tanggal_retur'])) ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Dibuat Oleh</td>
                                <td>: <?= htmlspecialchars($retur['create_by_retur'] ?? '-') ?></td>
                                <td class="font-weight-bold">Sales</td>
                                <td>: <?= htmlspecialchars($retur['nama_sales'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <form method="post" action="<?= base_url('retur_penjualan/retur/verifikasi_simpan/' . $retur['id_retur']) ?>">
                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <?php endif; ?>

                    <!-- TABEL DETAIL (bisa diedit qty dan harga) -->
                    <div class="card shadow mb-3">
                        <div class="card-header bg-dark text-white py-2">
                            <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Detail Barang (Koreksi jika perlu)</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="text-center" style="width:40px;">No.</th>
                                            <th>Nama Barang</th>
                                            <th>No. Faktur</th>
                                            <th>No. Batch</th>
                                            <th class="text-center">Exp. Date</th>
                                            <th class="text-center" style="width:100px;">Qty Retur</th>
                                            <th class="text-right" style="width:130px;">Harga Satuan</th>
                                            <th class="text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $total = 0; foreach ($retur_detail as $i => $d):
                                            $subtotal = (float)$d['qty_retur'] * (float)$d['harga_satuan'];
                                            $total += $subtotal;
                                        ?>
                                        <tr>
                                            <td class="text-center"><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($d['nama_barang'] ?? '-') ?>
                                                <input type="hidden" name="id_retur_detail[]" value="<?= $d['id_retur_detail'] ?>">
                                            </td>
                                            <td><?= htmlspecialchars($d['no_faktur'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($d['no_batch'] ?? '-') ?></td>
                                            <td class="text-center"><?= !empty($d['expired_date']) ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?></td>
                                            <td>
                                                <input type="number" name="qty_retur[]" class="form-control form-control-sm text-right qty-input"
                                                       value="<?= (float)$d['qty_retur'] ?>" min="0.001" step="0.001">
                                            </td>
                                            <td>
                                                <input type="number" name="harga_satuan[]" class="form-control form-control-sm text-right harga-input"
                                                       value="<?= (float)$d['harga_satuan'] ?>" min="0" step="0.01">
                                            </td>
                                            <td class="text-right subtotal-cell">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-secondary">
                                            <td colspan="7" class="text-right font-weight-bold">TOTAL:</td>
                                            <td class="text-right font-weight-bold" id="total-val">Rp <?= number_format($total, 0, ',', '.') ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- KEPUTUSAN -->
                    <div class="card shadow">
                        <div class="card-header bg-secondary text-white py-2">
                            <h3 class="card-title"><i class="fas fa-gavel mr-1"></i> Keputusan Admin Stock</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Catatan Verifikasi</label>
                                <textarea name="catatan_admin_stock" class="form-control" rows="3" placeholder="Catatan verifikasi barang..."></textarea>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <div>
                                <button type="submit" name="aksi" value="tolak"
                                        class="btn btn-danger mr-2"
                                        onclick="return confirm('Tolak Retur ini?')">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                                <button type="submit" name="aksi" value="setuju"
                                        class="btn btn-success"
                                        onclick="return confirm('Setujui dan lanjutkan ke Team Collection?')">
                                    <i class="fas fa-check"></i> Setuju → Collection
                                </button>
                            </div>
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
