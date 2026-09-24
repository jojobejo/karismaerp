<!-- views/content/sales/faktur_pecah_detail.php -->
<?php
$back_url = !empty($back_url) ? $back_url : base_url('sales_order/pecah_faktur');

// Bersihkan nama penerima dari embel-embel nama toko induk dalam tanda kurung
$penerima_clean = !empty($faktur['full_customer_name']) 
    ? $faktur['full_customer_name'] 
    : preg_replace('/\s*\([^)]*\)$/', '', $faktur['customer_name']);

// Bersihkan catatan jika mengandung teks referensi Faktur Z untuk keperluan cetak faktur
$catatan_raw = $faktur['catatan'] ?? '';
$catatan_clean = trim(preg_replace('/Pecahan\s*(Massal|Otomatis)?\s*dari\s*Faktur\s*Z[\s:]*[A-Z0-9_\-\/]*/i', '', $catatan_raw));
?>
<style>
    /* Reset gaya tabel agar solid standar */
    table.table, 
    .table-responsive table.table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
    }
    table.table tbody tr, 
    table.table tfoot tr {
        background: transparent !important;
        box-shadow: none !important;
    }
    table.table tbody td, 
    table.table thead th,
    table.table tfoot td {
        border-radius: 0 !important;
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.04) !important;
        box-shadow: none !important;
    }

    .print-only {
        display: none;
    }

    @media print {
        @page {
            size: A4;
            margin: 10mm 12mm;
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
        .breadcrumb,
        .no-print,
        .d-print-none {
            display: none !important;
        }

        .content-wrapper {
            margin-left: 0 !important;
            min-height: 0 !important;
            background: #fff !important;
            padding: 0 !important;
        }

        .content,
        .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        .print-only {
            display: block !important;
        }

        .row {
            display: flex !important;
            flex-wrap: wrap !important;
            margin-right: -5px !important;
            margin-left: -5px !important;
        }

        .col-md-6 {
            flex: 0 0 50% !important;
            max-width: 50% !important;
            padding-right: 5px !important;
            padding-left: 5px !important;
        }

        .card {
            border: 1px solid #333 !important;
            box-shadow: none !important;
            margin-bottom: 8px !important;
            background: #fff !important;
        }

        .card-header {
            background: #f4f4f4 !important;
            color: #000 !important;
            border-bottom: 1px solid #333 !important;
            padding: 4px 8px !important;
        }

        .card-title {
            font-size: 11.5px !important;
            font-weight: 700 !important;
            color: #000 !important;
        }

        .table-sm th,
        .table-sm td {
            padding: 3px 5px !important;
            font-size: 10.5px !important;
            color: #000 !important;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #333 !important;
        }

        .thead-light th {
            background-color: #eee !important;
            color: #000 !important;
            border-color: #333 !important;
        }

        .text-primary, .text-success, .text-muted, .text-dark {
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
        <div class="content-header d-print-none">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold">
                            <i class="fas fa-file-invoice text-success mr-2"></i>
                            Faktur Pecahan: <strong><?= htmlspecialchars($faktur['no_faktur']) ?></strong>
                        </h1>
                        <p class="text-muted small mb-0 mt-1 d-print-none">
                            Faktur pecahan administratif (Kode H) dari Faktur Z Induk: 
                            <strong><?= htmlspecialchars($faktur['parent_no_faktur']) ?></strong>.
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order/pecah_faktur') ?>">Pecah Faktur</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($faktur['no_faktur']) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- Kop Faktur Khusus Cetak -->
                <div class="print-only mb-3">
                    <div class="d-flex justify-content-between align-items-start pb-2 mb-2" style="border-bottom: 2px solid #000 !important;">
                        <div>
                            <h4 class="font-weight-bold mb-0 text-dark" style="font-size: 18px;">PT. KARISMA INDOAGRO UNIVERSAL</h4>
                            <div style="font-size: 11px;">Distributor Pupuk & Pestisida Pertanian</div>
                        </div>
                        <div class="text-right">
                            <h3 class="font-weight-bold mb-0 text-dark" style="font-size: 18px; letter-spacing: 1px;">FAKTUR PENJUALAN</h3>
                            <div class="font-weight-bold text-dark" style="font-size: 13px;">No: <?= htmlspecialchars($faktur['no_faktur']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Alert Informasi Isolasi Sistem (Khusus Layar Internal) -->
                <div class="alert alert-info border-0 shadow-sm mb-3 d-print-none">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shield-alt fa-2x mr-3 text-info"></i>
                        <div>
                            <h6 class="font-weight-bold mb-1">Informasi Dokumen Pecahan (Kode H)</h6>
                            <p class="mb-0 small">
                                Dokumen ini disimpan pada tabel khusus pemecahan (<strong>tbso_faktur_z_pecah</strong>). 
                                <strong>Tidak menjurnal ulang</strong> dan <strong>tidak memotong stok ulang</strong>, karena stok fisik dan jurnal akuntansi telah dicatat sepenuhnya pada Faktur Z Induk (<strong><?= htmlspecialchars($faktur['parent_no_faktur']) ?></strong>).
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="mb-3 no-print d-print-none">
                    <a href="<?= $back_url ?>" class="btn btn-secondary btn-sm mr-1">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Modul Pecah Faktur
                    </a>
                    <button class="btn btn-info btn-sm mr-1" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Cetak Faktur Pecahan
                    </button>
                    <?php if (!empty($parent_faktur)): ?>
                        <a href="<?= base_url('sales_order/detail_faktur/' . $parent_faktur['id_faktur']) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt mr-1"></i> Buka Faktur Z Induk (<?= htmlspecialchars($parent_faktur['no_faktur']) ?>)
                        </a>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <!-- Info Faktur -->
                    <div class="col-md-6">
                        <div class="card card-outline card-success shadow-sm mb-3">
                            <div class="card-header py-2">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas fa-file-invoice mr-1 d-print-none"></i> Informasi Faktur
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="40%">No. Faktur</td>
                                        <td>
                                            <span class="badge badge-success d-print-none" style="font-size: 13px;"><?= htmlspecialchars($faktur['no_faktur']) ?></span>
                                            <strong class="print-only text-dark font-weight-bold"><?= htmlspecialchars($faktur['no_faktur']) ?></strong>
                                        </td>
                                    </tr>
                                    <!-- Faktur Z Induk disembunyikan saat dicetak -->
                                    <tr class="d-print-none">
                                        <td class="text-muted">Faktur Z Induk</td>
                                        <td>
                                            <?php if (!empty($parent_faktur)): ?>
                                                <a href="<?= base_url('sales_order/detail_faktur/' . $parent_faktur['id_faktur']) ?>" class="font-weight-bold text-primary">
                                                    <?= htmlspecialchars($parent_faktur['no_faktur']) ?>
                                                </a>
                                            <?php else: ?>
                                                <strong><?= htmlspecialchars($faktur['parent_no_faktur']) ?></strong>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <!-- Dari Sales Order disembunyikan saat dicetak -->
                                    <tr class="d-print-none">
                                        <td class="text-muted">Dari Sales Order</td>
                                        <td><strong><?= htmlspecialchars($faktur['no_so']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tanggal Faktur</td>
                                        <td><?= !empty($faktur['tanggal_faktur']) ? date('d/m/Y', strtotime($faktur['tanggal_faktur'])) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tanggal Jatuh Tempo</td>
                                        <td><?= !empty($faktur['tanggal_jatuh_tempo']) ? date('d/m/Y', strtotime($faktur['tanggal_jatuh_tempo'])) : '-' ?></td>
                                    </tr>
                                    <!-- Status Dokumen disembunyikan saat dicetak -->
                                    <tr class="d-print-none">
                                        <td class="text-muted">Status Dokumen</td>
                                        <td><span class="badge badge-primary text-uppercase"><?= htmlspecialchars($faktur['status']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Cara Pembayaran</td>
                                        <td><?= strtoupper(htmlspecialchars($faktur['cara_pembayaran'] ?? '-')) ?> (Tempo: <?= (int)$faktur['tempo'] ?> hari)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Salesman</td>
                                        <td><?= htmlspecialchars($faktur['salesman'] ?? '-') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Info Customer Penerima -->
                    <div class="col-md-6">
                        <div class="card card-outline card-primary shadow-sm mb-3">
                            <div class="card-header py-2">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas fa-user-tag mr-1 d-print-none"></i> Customer Penerima
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="40%">Kode Customer</td>
                                        <td>
                                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 d-print-none" style="font-family: monospace; font-size: 13px;">
                                                <?= htmlspecialchars($faktur['kd_customer']) ?>
                                            </span>
                                            <strong class="print-only text-dark font-weight-bold"><?= htmlspecialchars($faktur['kd_customer']) ?></strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Nama Penerima</td>
                                        <td><strong class="text-primary font-weight-bold"><?= htmlspecialchars($penerima_clean) ?></strong></td>
                                    </tr>
                                    <!-- Kios / Toko Induk disembunyikan saat dicetak -->
                                    <?php if (!empty($faktur['nama_toko'])): ?>
                                    <tr class="d-print-none">
                                        <td class="text-muted">Kios / Toko Induk</td>
                                        <td><strong class="text-dark"><?= htmlspecialchars($faktur['nama_toko']) ?></strong></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($faktur['ca_alamat']) || !empty($faktur['ca_kota'])): ?>
                                    <tr>
                                        <td class="text-muted">Alamat & Kota</td>
                                        <td><?= htmlspecialchars($faktur['ca_alamat'] ?? '-') ?><?= !empty($faktur['ca_kota']) ? ', ' . htmlspecialchars($faktur['ca_kota']) : '' ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($faktur['ca_nik'])): ?>
                                    <tr>
                                        <td class="text-muted">NIK</td>
                                        <td><?= htmlspecialchars($faktur['ca_nik']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($catatan_clean)): ?>
                                    <tr>
                                        <td class="text-muted">Catatan</td>
                                        <td><?= nl2br(htmlspecialchars($catatan_clean)) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="d-print-none">
                                        <td class="text-muted">Dibuat Oleh</td>
                                        <td><?= htmlspecialchars($faktur['create_by'] ?? '-') ?> (<?= !empty($faktur['create_at']) ? date('d/m/Y H:i', strtotime($faktur['create_at'])) : '-' ?>)</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rincian Barang Pecahan -->
                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-boxes mr-1 d-print-none"></i> Rincian Barang Faktur
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="40" class="text-center">No</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>No. Lot</th>
                                        <th class="text-center">Exp. Date</th>
                                        <th class="text-center">Qty (Pcs)</th>
                                        <th class="text-center">Box / Satuan</th>
                                        <th class="text-right">Harga Satuan</th>
                                        <th class="text-center">Disc (%)</th>
                                        <th class="text-right">Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    $grand_qty = 0;
                                    $grand_total = 0;
                                    foreach ($details as $d): 
                                        $grand_qty += (float)$d['qty'];
                                        $grand_total += (float)$d['total_harga'];
                                    ?>
                                        <tr>
                                            <td class="text-center align-middle font-weight-bold"><?= $no++ ?></td>
                                            <td class="align-middle"><code><?= htmlspecialchars($d['kd_barang']) ?></code></td>
                                            <td class="align-middle font-weight-bold"><?= htmlspecialchars($d['nama_barang']) ?></td>
                                            <td class="align-middle"><?= htmlspecialchars($d['no_lot'] ?? '-') ?></td>
                                            <td class="text-center align-middle"><?= !empty($d['expired_date']) ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?></td>
                                            <td class="text-center align-middle font-weight-bold text-primary"><?= number_format((float)$d['qty']) ?></td>
                                            <td class="text-center align-middle">
                                                <?= (float)$d['qty_box'] ?> Box + <?= (float)$d['qty_satuan'] ?> <?= htmlspecialchars($d['satuan'] ?? 'PCS') ?>
                                            </td>
                                            <td class="text-right align-middle">Rp <?= number_format((float)$d['hrg_satuan'], 0, ',', '.') ?></td>
                                            <td class="text-center align-middle"><?= (float)$d['disc'] ?>%</td>
                                            <td class="text-right align-middle font-weight-bold">Rp <?= number_format((float)$d['total_harga'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <th colspan="5" class="text-right font-weight-bold">Total Kuantitas:</th>
                                        <th class="text-center font-weight-bold text-primary"><?= number_format($grand_qty) ?> pcs</th>
                                        <th colspan="3" class="text-right font-weight-bold">Grand Total:</th>
                                        <th class="text-right font-weight-bold text-success" style="font-size: 15px;">Rp <?= number_format($grand_total, 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tanda Tangan Khusus Cetak -->
                <div class="print-only mt-4 pt-2">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="small text-muted mb-1 font-weight-bold">Penerima / Customer:</div>
                            <div style="height: 55px;"></div>
                            <div class="font-weight-bold text-dark pt-1" style="border-top: 1px dashed #333 !important;">
                                ( <?= htmlspecialchars($penerima_clean) ?> )
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted mb-1 font-weight-bold">Salesman:</div>
                            <div style="height: 55px;"></div>
                            <div class="font-weight-bold text-dark pt-1" style="border-top: 1px dashed #333 !important;">
                                ( <?= htmlspecialchars($faktur['salesman'] ?? '...................') ?> )
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="small text-muted mb-1 font-weight-bold">Hormat Kami:</div>
                            <div style="height: 55px;"></div>
                            <div class="font-weight-bold text-dark pt-1" style="border-top: 1px dashed #333 !important;">
                                ( <?= htmlspecialchars($faktur['create_by'] ?? 'Bag. Administrasi') ?> )
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <footer class="main-footer d-print-none">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>

