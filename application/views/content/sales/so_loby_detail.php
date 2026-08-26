<!-- views/content/sales/so_loby_detail.php -->
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
                            <i class="fas fa-store mr-2 text-primary"></i> Detail SO Loby: <?= html_escape($so['no_so']) ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order_loby') ?>">Sales Order Loby</a></li>
                            <li class="breadcrumb-item active"><?= html_escape($so['no_so']) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- FLASH MESSAGE -->
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-circle' : 'info-circle') ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- HEADER INFO CARD -->
                <div class="card card-outline card-primary shadow-sm mb-3">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold text-dark m-0">
                            <i class="fas fa-info-circle mr-1 text-primary"></i> Informasi Transaksi SO Loby
                        </h3>
                        <div>
                            <?php if ($so['status'] === 'completed' || !empty($fakturs)): ?>
                                <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 13px;">
                                    <i class="fas fa-check-double mr-1"></i> INVOICED / COMPLETED
                                </span>
                            <?php elseif ($so['status'] === 'cancelled'): ?>
                                <span class="badge badge-danger px-3 py-2 font-weight-bold" style="font-size: 13px;">
                                    <i class="fas fa-times-circle mr-1"></i> CANCELLED
                                </span>
                            <?php else: ?>
                                <span class="badge badge-info px-3 py-2 font-weight-bold" style="font-size: 13px;">
                                    <i class="fas fa-clock mr-1"></i> OPEN (SIAP FAKTUR)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <table class="table table-sm table-borderless m-0">
                                    <tr>
                                        <td class="text-muted" style="width: 130px;">No. SO Loby</td>
                                        <td class="font-weight-bold text-primary">: <?= html_escape($so['no_so']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tanggal</td>
                                        <td class="font-weight-bold">: <?= date('d F Y', strtotime($so['tanggal_transaksi'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Metode Bayar</td>
                                        <td>: <span class="badge badge-success px-2 py-1"><i class="fas fa-money-bill-wave mr-1"></i> CASH</span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <table class="table table-sm table-borderless m-0">
                                    <tr>
                                        <td class="text-muted" style="width: 130px;">Customer</td>
                                        <td class="font-weight-bold">: <?= html_escape($so['customer_name'] ?: $so['nama_customer']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Kode Customer</td>
                                        <td>: <?= html_escape($so['kd_customer']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Regional / Kios</td>
                                        <td>: <?= html_escape($so['regional'] ?: '-') ?> / <?= html_escape($so['nama_kios'] ?: '-') ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <table class="table table-sm table-borderless m-0">
                                    <tr>
                                        <td class="text-muted" style="width: 130px;">Gudang Stok</td>
                                        <td class="font-weight-bold">: <?= html_escape($so['gudang_id']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Dibuat Oleh</td>
                                        <td>: <?= html_escape($so['create_by']) ?> (<?= date('d/m/Y H:i', strtotime($so['create_at'])) ?>)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Catatan</td>
                                        <td>: <?= html_escape($so['catatan'] ?: '-') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ITEMS TABLE CARD -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h3 class="card-title font-weight-bold text-dark m-0">
                            <i class="fas fa-boxes mr-1 text-primary"></i> Rincian Barang yang Diambil di Loby
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
                                        <th>No. Lot</th>
                                        <th>Expired Date</th>
                                        <th style="width: 100px;">Qty Order</th>
                                        <th style="width: 90px;">Satuan</th>
                                        <th style="width: 130px;">Harga Satuan</th>
                                        <th style="width: 80px;">Disc (%)</th>
                                        <th style="width: 140px;">Total Harga</th>
                                        <th style="width: 100px;">Qty Faktur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $grandTotal = 0;
                                    $totalQty = 0;
                                    $no = 1;
                                    if (!empty($details)): 
                                        foreach ($details as $d): 
                                            $grandTotal += (float)$d['total_harga'];
                                            $totalQty += (float)$d['qty'];
                                    ?>
                                        <tr>
                                            <td class="text-center align-middle"><?= $no++ ?></td>
                                            <td class="font-weight-bold text-primary align-middle"><?= html_escape($d['kd_barang']) ?></td>
                                            <td class="align-middle"><?= html_escape($d['nama_barang']) ?></td>
                                            <td class="text-center align-middle"><small class="badge badge-light border"><?= html_escape($d['no_lot'] ?: '-') ?></small></td>
                                            <td class="text-center align-middle"><?= html_escape($d['expired_date'] ?: '-') ?></td>
                                            <td class="text-right font-weight-bold align-middle"><?= number_format((float)$d['qty'], 2, ',', '.') ?></td>
                                            <td class="text-center align-middle"><?= html_escape($d['satuan']) ?></td>
                                            <td class="text-right align-middle">Rp <?= number_format((float)$d['hrg_satuan'], 0, ',', '.') ?></td>
                                            <td class="text-right align-middle"><?= (float)$d['disc'] > 0 ? (float)$d['disc'] . '%' : '-' ?></td>
                                            <td class="text-right font-weight-bold align-middle">Rp <?= number_format((float)$d['total_harga'], 0, ',', '.') ?></td>
                                            <td class="text-center align-middle">
                                                <?php if ((float)$d['qty_faktur'] >= (float)$d['qty']): ?>
                                                    <span class="badge badge-success"><?= number_format((float)$d['qty_faktur'], 2, ',', '.') ?> (Lengkap)</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning"><?= number_format((float)$d['qty_faktur'], 2, ',', '.') ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <th colspan="5" class="text-right font-weight-bold">TOTAL:</th>
                                        <th class="text-right font-weight-bold"><?= number_format($totalQty, 2, ',', '.') ?></th>
                                        <th colspan="3" class="text-right font-weight-bold" style="font-size: 15px;">GRAND TOTAL (CASH):</th>
                                        <th class="text-right font-weight-bold text-primary" style="font-size: 16px;">Rp <?= number_format($grandTotal, 0, ',', '.') ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- FAKTUR CARD IF AVAILABLE -->
                <?php if (!empty($fakturs)): ?>
                    <div class="card card-outline card-success shadow-sm mb-3">
                        <div class="card-header py-2">
                            <h3 class="card-title font-weight-bold text-success">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> Faktur Penjualan Loby
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered m-0">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th>No. Faktur</th>
                                            <th>Tanggal Faktur</th>
                                            <th>Status Faktur</th>
                                            <th>Status Pembayaran</th>
                                            <th>Dibuat Oleh</th>
                                            <th style="width: 200px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($fakturs as $f): ?>
                                            <tr class="text-center align-middle">
                                                <td class="font-weight-bold text-primary align-middle"><?= html_escape($f['no_faktur']) ?></td>
                                                <td class="align-middle"><?= date('d/m/Y', strtotime($f['tanggal_faktur'])) ?></td>
                                                <td class="align-middle">
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Selesai DO (Siap Bayar)</span>
                                                </td>
                                                <td class="align-middle">
                                                    <a href="<?= base_url('keuangan/pembayaran') ?>" target="_blank" class="badge badge-info px-2 py-1">
                                                        <i class="fas fa-external-link-alt mr-1"></i> Terintegrasi Keuangan
                                                    </a>
                                                </td>
                                                <td class="align-middle"><?= html_escape($f['create_by']) ?></td>
                                                <td class="align-middle">
                                                    <a href="<?= base_url('sales_order_loby/detail_faktur/' . $f['id_faktur']) ?>" class="btn btn-sm btn-info font-weight-bold">
                                                        <i class="fas fa-eye mr-1"></i> Detail Faktur
                                                    </a>
                                                    <a href="<?= base_url('sales_order_loby/print_faktur/' . $f['id_faktur']) ?>" target="_blank" class="btn btn-sm btn-secondary font-weight-bold">
                                                        <i class="fas fa-print mr-1"></i> Cetak Faktur
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- ACTION FOOTER -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body d-flex justify-content-between align-items-center py-3">
                        <a href="<?= base_url('sales_order_loby') ?>" class="btn btn-secondary font-weight-bold">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar SO Loby
                        </a>
                        <div>
                            <?php if (empty($fakturs) && $so['status'] !== 'cancelled'): ?>
                                <a href="<?= base_url('sales_order_loby/edit/' . $so['id_so']) ?>" class="btn btn-warning font-weight-bold mr-2">
                                    <i class="fas fa-edit mr-1"></i> Edit SO
                                </a>
                                <a href="<?= base_url('sales_order_loby/cancel/' . $so['id_so']) ?>" class="btn btn-danger font-weight-bold mr-2" onclick="return confirm('Apakah Anda yakin ingin membatalkan SO Loby ini? Stok ter-reserve akan dilepas.')">
                                    <i class="fas fa-times mr-1"></i> Batalkan SO
                                </a>
                                <a href="<?= base_url('sales_order_loby/form_faktur/' . $so['id_so']) ?>" class="btn btn-success btn-lg font-weight-bold px-4 shadow">
                                    <i class="fas fa-file-invoice-dollar mr-1"></i> Proses Faktur Sekarang
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <?php $this->load->view('partial/main/footer') ?>
</div>
</body>
