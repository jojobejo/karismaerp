<!-- views/content/sales/faktur_detail.php -->
<style>
    .invoice-print-header {
        display: none;
    }

    .invoice-print-title {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0;
        margin-bottom: 2px;
    }

    .invoice-print-meta {
        text-align: right;
        font-size: 12px;
    }

    @media print {
        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            background: #fff !important;
            color: #000 !important;
            font-size: 11px;
        }

        .main-header,
        .main-sidebar,
        .main-footer,
        .control-sidebar,
        .preloader,
        .content-header,
        .no-print {
            display: none !important;
        }

        .content-wrapper {
            margin-left: 0 !important;
            min-height: 0 !important;
            background: #fff !important;
        }

        .content,
        .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
        }

        .invoice-print-header {
            display: flex !important;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .row {
            display: block !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .col-md-5,
        .col-md-7 {
            max-width: 100% !important;
            flex: 0 0 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .card {
            border: 0 !important;
            box-shadow: none !important;
            margin-bottom: 10px !important;
        }

        .card-header {
            background: #f2f2f2 !important;
            color: #000 !important;
            border: 1px solid #000 !important;
            padding: 5px 8px !important;
        }

        .card-title {
            font-size: 12px !important;
            font-weight: 700 !important;
        }

        .card-body {
            border: 1px solid #000 !important;
            border-top: 0 !important;
        }

        .table {
            width: 100% !important;
            font-size: 10.5px !important;
            color: #000 !important;
            margin-bottom: 0 !important;
        }

        .table th,
        .table td {
            padding: 4px 5px !important;
            border-color: #333 !important;
            vertical-align: top !important;
        }

        .thead-dark th,
        .thead-light th {
            background: #e9ecef !important;
            color: #000 !important;
        }

        a {
            color: #000 !important;
            text-decoration: none !important;
        }

        .badge {
            border: 1px solid #000 !important;
            background: #fff !important;
            color: #000 !important;
        }
    }
</style>
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
            <?php $customer_kd_rute = $faktur['customer_kd_rute'] ?? $so['customer_kd_rute'] ?? ''; ?>

            <div class="invoice-print-header">
                <div>
                    <div class="invoice-print-title">FAKTUR PENJUALAN</div>
                    <div><strong>PT. Karisma Indoargo Universal</strong></div>
                    <div>No. Faktur: <?= htmlspecialchars($faktur['no_faktur']) ?></div>
                    <div>No. SO: <?= htmlspecialchars($so['no_so']) ?></div>
                </div>
                <div class="invoice-print-meta">
                    <div>Tanggal Faktur: <?= date('d/m/Y', strtotime($faktur['tanggal_faktur'])) ?></div>
                    <?php if (!empty($faktur['tanggal_jatuh_tempo'])): ?>
                        <div>Jatuh Tempo: <?= date('d/m/Y', strtotime($faktur['tanggal_jatuh_tempo'])) ?></div>
                    <?php endif; ?>
                    <div>Customer: <strong><?= htmlspecialchars($faktur['customer_name']) ?></strong></div>
                    <div>Rute / Regional: <strong><?= !empty($customer_kd_rute) ? htmlspecialchars($customer_kd_rute) : '-' ?></strong></div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="mb-3 no-print">
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
                                <tr>
                                    <td class="text-muted">Rute / Regional</td>
                                    <td><strong><?= !empty($customer_kd_rute) ? htmlspecialchars($customer_kd_rute) : '<span class="text-muted">-</span>' ?></strong></td>
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
                                <?php $tempo_hari = $faktur['jtempo'] ?? $faktur['tempo'] ?? null; ?>
                                <?php if ($tempo_hari !== null && $tempo_hari !== ''): ?>
                                <tr>
                                    <td class="text-muted">Tempo</td>
                                    <td><?= (int)$tempo_hari ?> Hari</td>
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
                                    $total_nilai_faktur = 0;
                                    $total_tax = 0;
                                    $grand_total = 0;
                                    $tax_rates = [];
                                    foreach ($details as $i => $d):
                                        $isi     = max(1, (int)($d['isi_per_box'] ?? 1));
                                        $qty_box = floor($d['qty'] / $isi);
                                        $qty_pcs = fmod($d['qty'], $isi);
                                        $nilai_faktur = (float)($d['subtotal_after_disc'] ?? 0);
                                        if ($nilai_faktur <= 0) {
                                            $nilai_faktur = (float)$d['qty'] * (float)$d['hrg_satuan'];
                                            $nilai_faktur = $nilai_faktur * (1 - ((float)($d['disc'] ?? 0) / 100));
                                        }
                                        $tax_rate = (float)($d['pajak'] ?? 0);
                                        $tax_value = $nilai_faktur * ($tax_rate / 100);
                                        $total_nilai_faktur += $nilai_faktur;
                                        $total_tax += $tax_value;
                                        $total_harga = (float)($d['total_harga'] ?? 0);
                                        $grand_total += $total_harga > 0 ? $total_harga : ($nilai_faktur + $tax_value);
                                        if ($tax_rate > 0) {
                                            $tax_rates[(string)$tax_rate] = $tax_rate;
                                        }
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
                                            <strong>Rp <?= number_format($total_nilai_faktur, 0, ',', '.') ?></strong>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="6" class="text-right">
                                            Tax<?= !empty($tax_rates) ? ': ' . implode(', ', array_map(function($rate) { return number_format($rate, 0) . '(%)'; }, $tax_rates)) : ': 0(%)' ?>
                                        </th>
                                        <th class="text-right">
                                            <strong>Rp <?= number_format($total_tax, 0, ',', '.') ?></strong>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="6" class="text-right">Grand Total Harga:</th>
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
