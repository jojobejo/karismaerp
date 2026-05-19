<!-- views/content/sales/faktur_detail.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>
    
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>
                        Faktur: <strong><?= htmlspecialchars($faktur['no_faktur']) ?></strong>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('sales_order/detail/' . $so['id_so']) ?>">
                                <?= htmlspecialchars($so['no_so']) ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($faktur['no_faktur']) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- Tombol Aksi -->
            <div class="mb-3">
                <a href="<?= base_url('sales_order/detail/' . $so['id_so']) ?>"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali ke SO
                </a>
                <button class="btn btn-info btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak Faktur
                </button>
            </div>

            <div class="row">
                <!-- Info Faktur -->
                <div class="col-md-5">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi Faktur</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="40%">No. Faktur</td>
                                    <td><strong><?= htmlspecialchars($faktur['no_faktur']) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Dari SO</td>
                                    <td>
                                        <a href="<?= base_url('sales_order/detail/' . $so['id_so']) ?>">
                                            <?= htmlspecialchars($so['no_so']) ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tanggal Faktur</td>
                                    <td><?= date('d/m/Y', strtotime($faktur['tanggal_faktur'])) ?></td>
                                </tr>
                                <?php if (!empty($faktur['tanggal_jatuh_tempo'])): ?>
                                <tr>
                                    <td class="text-muted">Tgl Jatuh Tempo</td>
                                    <td><?= date('d/m/Y', strtotime($faktur['tanggal_jatuh_tempo'])) ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Customer</td>
                                    <td><strong><?= htmlspecialchars($faktur['customer_name']) ?></strong></td>
                                </tr>
                                <?php if (!empty($faktur['salesman'])): ?>
                                <tr>
                                    <td class="text-muted">Salesman</td>
                                    <td><?= htmlspecialchars($faktur['salesman']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($faktur['cara_pembayaran'])): ?>
                                <tr>
                                    <td class="text-muted">Cara Pembayaran</td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars(ucfirst($faktur['cara_pembayaran'])) ?></span></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Gudang</td>
                                    <td><?= htmlspecialchars($faktur['gudang_id']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td>
                                        <?php
                                        $fs_badge = ['confirmed' => 'success', 'draft' => 'secondary', 'cancelled' => 'danger'];
                                        $fs_label = ['confirmed' => 'Confirmed', 'draft' => 'Draft', 'cancelled' => 'Cancelled'];
                                        ?>
                                        <span class="badge badge-<?= $fs_badge[$faktur['status']] ?? 'secondary' ?>">
                                            <?= $fs_label[$faktur['status']] ?? $faktur['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Total Tonase</td>
                                    <td><?= number_format($faktur['total_tonase'], 3) ?> ton</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Total Kubikasi</td>
                                    <td><?= number_format($faktur['total_kubikasi'], 4) ?> m³</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Dibuat oleh</td>
                                    <td><?= htmlspecialchars($faktur['create_by']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tanggal Buat</td>
                                    <td><?= date('d/m/Y H:i', strtotime($faktur['create_at'])) ?></td>
                                </tr>
                                <?php if (!empty($faktur['catatan'])): ?>
                                <tr>
                                    <td class="text-muted">Catatan</td>
                                    <td><?= nl2br(htmlspecialchars($faktur['catatan'])) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Item Faktur -->
                <div class="col-md-7">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list-ul mr-1"></i> Item Faktur</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Barang</th>
                                        <th>Lot / Exp</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Harga</th>
                                        <th class="text-right">Disc</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $grand_total = 0;
                                    foreach ($details as $i => $d):
                                        $isi     = max(1, (int)($d['isi_per_box'] ?? 1));
                                        $qty_box = floor($d['qty'] / $isi);
                                        $qty_pcs = fmod($d['qty'], $isi);
                                        $grand_total += (float)$d['total_harga'];
                                    ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($d['nama_barang']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($d['kd_barang']) ?></small>
                                        </td>
                                        <td>
                                            <small>
                                                <?php if (!empty($d['no_lot'])): ?>
                                                    Lot: <code><?= htmlspecialchars($d['no_lot']) ?></code><br>
                                                <?php endif; ?>
                                                Exp: <?= !empty($d['expired_date']) ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?>
                                            </small>
                                        </td>
                                        <td class="text-right">
                                            <?= $qty_box > 0 ? $qty_box . ' box' : '' ?>
                                            <?= $qty_pcs > 0 ? ($qty_box > 0 ? '+ ' : '') . (int)$qty_pcs . ' pcs' : '' ?>
                                            <?php if ($qty_box == 0): ?>
                                                <?= number_format($d['qty']) ?> pcs
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            Rp <?= number_format($d['hrg_satuan'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-right">
                                            <?= $d['disc'] > 0 ? $d['disc'] . '%' : '-' ?>
                                        </td>
                                        <td class="text-right">
                                            <strong>Rp <?= number_format($d['total_harga'], 0, ',', '.') ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <th colspan="6" class="text-right">Total Nilai Faktur:</th>
                                        <th class="text-right">
                                            <strong>Rp <?= number_format($grand_total, 0, ',', '.') ?></strong>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
<footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
