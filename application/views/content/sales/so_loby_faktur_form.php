<!-- views/content/sales/so_loby_faktur_form.php -->
<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
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
                        <h1 class="m-0">
                            <i class="fas fa-file-invoice-dollar mr-2 text-success"></i> Proses Faktur Penjualan Loby
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order_loby') ?>">Sales Order Loby</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order_loby/detail/' . $so['id_so']) ?>"><?= html_escape($so['no_so']) ?></a></li>
                            <li class="breadcrumb-item active">Proses Faktur</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-1"></i> <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info shadow-sm">
                    <h5><i class="icon fas fa-info-circle"></i> Alur Faktur Penjualan Loby:</h5>
                    Memproses faktur ini akan <strong>langsung memotong stok fisik gudang (OUT)</strong>, memposting <strong>jurnal akuntansi penjualan</strong>, dan mendaftarkan tagihan ke modul <strong>Keuangan & Pembayaran (<a href="<?= base_url('keuangan/pembayaran') ?>" target="_blank" class="text-white font-weight-bold">/keuangan/pembayaran</a>)</strong> dengan status siap pelunasan CASH.
                </div>

                <form action="<?= base_url('sales_order_loby/simpan_faktur/' . $so['id_so']) ?>" method="post" id="form-faktur-loby">
                    <div class="row">
                        <!-- HEADER FAKTUR -->
                        <div class="col-md-6">
                            <div class="card card-outline card-success shadow-sm h-100">
                                <div class="card-header py-2">
                                    <h3 class="card-title font-weight-bold text-success"><i class="fas fa-file-invoice mr-1"></i> Data Faktur Penjualan</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label font-weight-bold">No. Faktur <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm font-weight-bold text-primary" name="no_faktur" value="<?= html_escape($no_faktur) ?>" required>
                                            <small class="text-muted">Nomor faktur di-generate otomatis, dapat disesuaikan jika diperlukan.</small>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label font-weight-bold">Tanggal Faktur <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="date" class="form-control form-control-sm" name="tanggal_faktur" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label font-weight-bold">Catatan Faktur</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control form-control-sm" name="catatan" rows="2">Penjualan Langsung Loby (CASH)</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- REFERENSI SO -->
                        <div class="col-md-6">
                            <div class="card card-outline card-primary shadow-sm h-100">
                                <div class="card-header py-2">
                                    <h3 class="card-title font-weight-bold"><i class="fas fa-link mr-1 text-primary"></i> Referensi Sales Order Loby</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-muted">No. SO Loby</label>
                                        <div class="col-sm-8 font-weight-bold pt-1 text-primary"><?= html_escape($so['no_so']) ?></div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-muted">Customer</label>
                                        <div class="col-sm-8 font-weight-bold pt-1"><?= html_escape($so['customer_name'] ?: $so['nama_customer']) ?> (<?= html_escape($so['kd_customer']) ?>)</div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-muted">Gudang Stok</label>
                                        <div class="col-sm-8 font-weight-bold pt-1"><?= html_escape($so['gudang_id']) ?></div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-muted">Metode Pembayaran</label>
                                        <div class="col-sm-8 pt-1">
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-money-bill-wave mr-1"></i> CASH</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ITEMS TABLE CARD -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-white py-2">
                            <h3 class="card-title font-weight-bold text-dark m-0">
                                <i class="fas fa-boxes mr-1 text-primary"></i> Rincian Barang yang Akan Difakturkan & Dikeluarkan
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover m-0">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Lot / Exp Date</th>
                                            <th style="width: 110px;">Qty Faktur</th>
                                            <th style="width: 90px;">Satuan</th>
                                            <th style="width: 140px;">Harga Satuan</th>
                                            <th style="width: 80px;">Disc (%)</th>
                                            <th style="width: 150px;">Total Tagihan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $grandTotal = 0;
                                        $totalQty = 0;
                                        $no = 1;
                                        foreach ($details as $d): 
                                            $grandTotal += (float)$d['total_harga'];
                                            $totalQty += (float)$d['qty'];
                                        ?>
                                            <tr>
                                                <td class="text-center align-middle"><?= $no++ ?></td>
                                                <td class="font-weight-bold text-primary align-middle"><?= html_escape($d['kd_barang']) ?></td>
                                                <td class="align-middle"><?= html_escape($d['nama_barang']) ?></td>
                                                <td class="text-center align-middle">
                                                    <div><small class="badge badge-light border">Lot: <?= html_escape($d['no_lot'] ?: '-') ?></small></div>
                                                    <div><small class="text-muted">Exp: <?= html_escape($d['expired_date'] ?: '-') ?></small></div>
                                                </td>
                                                <td class="text-right font-weight-bold align-middle"><?= number_format((float)$d['qty'], 2, ',', '.') ?></td>
                                                <td class="text-center align-middle"><?= html_escape($d['satuan']) ?></td>
                                                <td class="text-right align-middle">Rp <?= number_format((float)$d['hrg_satuan'], 0, ',', '.') ?></td>
                                                <td class="text-right align-middle"><?= (float)$d['disc'] > 0 ? (float)$d['disc'] . '%' : '-' ?></td>
                                                <td class="text-right font-weight-bold align-middle">Rp <?= number_format((float)$d['total_harga'], 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="4" class="text-right font-weight-bold">TOTAL ITEM:</th>
                                            <th class="text-right font-weight-bold"><?= number_format($totalQty, 2, ',', '.') ?></th>
                                            <th colspan="3" class="text-right font-weight-bold" style="font-size: 15px;">GRAND TOTAL FAKTUR (CASH):</th>
                                            <th class="text-right font-weight-bold text-success" style="font-size: 16px;">Rp <?= number_format($grandTotal, 0, ',', '.') ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between py-3">
                            <a href="<?= base_url('sales_order_loby/detail/' . $so['id_so']) ?>" class="btn btn-secondary font-weight-bold">
                                <i class="fas fa-arrow-left mr-1"></i> Batal & Kembali
                            </a>
                            <button type="submit" class="btn btn-success btn-lg font-weight-bold px-4 shadow" onclick="return confirm('Konfirmasi terbitkan Faktur Penjualan Loby? Stok akan dipotong dan jurnal penjualan akan dibuat secara otomatis.')">
                                <i class="fas fa-check-circle mr-1"></i> Terbitkan Faktur & Selesaikan
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </section>
    </div>

    <?php $this->load->view('partial/main/footer') ?>
</div>
</body>
