<!-- views/content/sales/pecah_faktur_kios_detail.php -->
<style>
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
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02) !important;
    }
    .badge-faktur-z {
        font-size: 12px;
        padding: 5px 9px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .badge-status-pecah {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .child-faktur-pill {
        display: inline-block;
        font-size: 11px;
        padding: 2px 7px;
        margin: 2px 2px 2px 0;
        border-radius: 4px;
        background-color: #e8f4f8;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    .child-faktur-pill:hover {
        background-color: #d1ecf1;
        text-decoration: underline;
    }
    .kios-profile-card {
        border-left: 5px solid #007bff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
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
                    <div class="col-sm-7">
                        <h1 class="m-0 text-dark font-weight-bold">
                            <i class="fas fa-store text-primary mr-2"></i> Faktur Z: <?= htmlspecialchars($nama_kios) ?>
                        </h1>
                        <p class="text-muted small mb-0 mt-1">
                            Daftar Faktur Z yang terdaftar di bawah kios <strong><?= htmlspecialchars($nama_kios) ?></strong>.
                        </p>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order/pecah_faktur') ?>">Pecah Faktur</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($nama_kios) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Flash Messages -->
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show shadow-sm">
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-circle' : 'exclamation-triangle') ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- Tombol Navigasi Kembali & Aksi Cepat -->
                <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <a href="<?= base_url('sales_order/pecah_faktur') ?>" class="btn btn-secondary btn-sm mr-2 shadow-sm font-weight-bold">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Kios
                        </a>
                        <a href="<?= base_url('sales_order/customer_acak') ?>" class="btn btn-outline-primary btn-sm mr-2">
                            <i class="fas fa-users mr-1"></i> Master Customer Acak
                        </a>
                        <a href="<?= base_url('sales_order/export_faktur_pecah?kd_customer=' . $kd_customer) ?>" class="btn btn-outline-success btn-sm font-weight-bold mr-2 shadow-sm" title="Export seluruh Faktur Pecahan Kode H milik kios ini ke Excel (.xlsx)">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel (.xlsx)
                        </a>
                        <?php if (!empty($stat['belum_dipecah']) && $stat['belum_dipecah'] > 0): ?>
                            <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalAutoSplitKios">
                                <i class="fas fa-magic mr-1"></i> Pecahkan Semua Faktur Z (<?= $stat['belum_dipecah'] ?> Faktur)
                            </button>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="text-muted small">
                            <i class="fas fa-clock mr-1"></i> <?= date('d M Y H:i') ?>
                        </span>
                    </div>
                </div>

                <!-- Card Profil Informasi Kios -->
                <div class="card bg-white kios-profile-card mb-3">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded p-3 mr-3 shadow-sm" style="font-size: 24px;">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-1 font-weight-bold text-dark"><?= htmlspecialchars($nama_kios) ?></h4>
                                        <div class="text-muted small">
                                            <span>Kode Customer: <strong class="text-primary font-weight-bold"><?= htmlspecialchars($customer['kd_customer'] ?? $kd_customer) ?></strong></span>
                                            &bull; <span>Pemilik: <strong><?= htmlspecialchars($customer['nama_customer'] ?? '-') ?></strong></span>
                                            <?php if (!empty($customer['kd_rute'])): ?>
                                                &bull; <span class="badge badge-light border"><i class="fas fa-route mr-1 text-info"></i>Rute: <?= htmlspecialchars($customer['kd_rute']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($customer['alamat_kios']) && $customer['alamat_kios'] !== '-'): ?>
                                            <div class="text-muted small mt-1">
                                                <i class="fas fa-map-marker-alt text-danger mr-1"></i> <?= htmlspecialchars($customer['alamat_kios']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <div class="row text-center">
                                    <div class="col-3 border-right">
                                        <span class="text-muted small d-block">Total Faktur Z</span>
                                        <span class="font-weight-bold text-primary" style="font-size: 18px;">
                                            <?= number_format($stat['total']) ?>
                                        </span>
                                    </div>
                                    <div class="col-3 border-right">
                                        <span class="text-muted small d-block">Belum Pecah</span>
                                        <span class="font-weight-bold text-warning" style="font-size: 18px;">
                                            <?= number_format($stat['belum_dipecah']) ?>
                                        </span>
                                    </div>
                                    <div class="col-3 border-right">
                                        <span class="text-muted small d-block">Total Awal</span>
                                        <span class="font-weight-bold text-dark" style="font-size: 14px;">
                                            Rp <?= number_format($stat['total_nilai'], 0, ',', '.') ?>
                                        </span>
                                    </div>
                                    <div class="col-3">
                                        <span class="text-muted small d-block font-weight-bold text-success">Netto (-20%)</span>
                                        <span class="font-weight-bold text-success" style="font-size: 14px;">
                                            Rp <?= number_format($stat['total_nilai'] * 0.8, 0, ',', '.') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Faktur Z Milik Kios Ini (Pemisahan Tab Belum vs Selesai Dipecah) -->
                <div class="card card-outline card-primary card-outline-tabs shadow-sm">
                    <div class="card-header p-0 border-bottom-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap px-3 pt-2">
                            <ul class="nav nav-tabs border-bottom-0" id="custom-tabs-kios-faktur" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active font-weight-bold" id="tab-kios-belum" data-toggle="pill" href="#content-kios-belum" role="tab" aria-controls="content-kios-belum" aria-selected="true">
                                        <i class="fas fa-hourglass-start text-warning mr-1"></i> Faktur Z Belum Dipecah
                                        <span class="badge badge-warning ml-1"><?= count($kios_fakturs_belum) ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold" id="tab-kios-sudah" data-toggle="pill" href="#content-kios-sudah" role="tab" aria-controls="content-kios-sudah" aria-selected="false">
                                        <i class="fas fa-check-circle text-success mr-1"></i> Faktur Z Selesai Dipecah
                                        <span class="badge badge-success ml-1"><?= count($kios_fakturs_sudah) ?></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="pb-2 d-flex flex-wrap align-items-center">
                                <?php if (!empty($stat['belum_dipecah']) && $stat['belum_dipecah'] > 0): ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold mr-2 shadow-sm" id="btnToggleSelectAllFakturZ" title="Pilih / Batalkan semua faktur Z pada halaman ini">
                                        <i class="far fa-check-square mr-1"></i> Pilih Semua
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-sm mr-2" id="btnPecahSelectedFakturZ" style="display:none;" title="Pecah beberapa faktur Z yang dicentang sekaligus">
                                        <i class="fas fa-cut mr-1"></i> Pecah Terpilih (<span id="countSelectedFakturZ">0</span>)
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm font-weight-bold shadow-sm mr-2" id="btnPindahKiosSelectedFakturZ" style="display:none;" title="Pindahkan faktur-faktur Z yang dicentang ke kios/toko lain (koreksi salah import)">
                                        <i class="fas fa-exchange-alt mr-1"></i> Pindah Kios Terpilih (<span id="countSelectedPindahKios">0</span>)
                                    </button>
                                    <a href="<?= base_url('sales_order/export_faktur_z_belum?kd_customer=' . rawurlencode($kd_customer)) ?>" 
                                       class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2"
                                       title="Export Daftar Faktur Z Belum Dipecah milik kios ini ke Excel (.xlsx)">
                                        <i class="fas fa-file-excel mr-1"></i> Export Excel (.xlsx)
                                    </a>
                                    <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalAutoSplitKios">
                                        <i class="fas fa-magic mr-1"></i> Pecahkan Semua (<?= $stat['belum_dipecah'] ?>)
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="custom-tabs-kios-content">
                            <!-- TAB 1: FAKTUR Z BELUM DIPECAS -->
                            <div class="tab-pane fade show active" id="content-kios-belum" role="tabpanel" aria-labelledby="tab-kios-belum">
                                <div class="table-responsive p-3">
                                    <table class="table table-hover table-striped mb-0 text-nowrap" id="tableFakturZKiosBelum">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="35" class="text-center no-sort">
                                                    <input type="checkbox" id="checkAllFakturZKios" title="Pilih Semua Faktur Z pada halaman ini" style="cursor:pointer; width:16px; height:16px;">
                                                </th>
                                                <th width="40" class="text-center">No</th>
                                                <th>No. Faktur Z</th>
                                                <th>Tanggal</th>
                                                <th>No. SO</th>
                                                <th class="text-center">Total Item</th>
                                                <th class="text-right">Grand Total (Awal)</th>
                                                <th class="text-right bg-light">Setelah Potong 20%</th>
                                                <th class="text-center">Status Faktur</th>
                                                <th width="155" class="text-center no-sort">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($kios_fakturs_belum)): ?>
                                                <tr>
                                                    <td colspan="10" class="text-center py-5 text-muted">
                                                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>
                                                        <h5 class="font-weight-bold text-success">Seluruh Faktur Z Kios Ini Telah Selesai Dipecah</h5>
                                                        <span class="text-muted">Tidak ada Faktur Z yang belum dipecah. Silakan buka tab <strong>Faktur Z Selesai Dipecah</strong>.</span>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $no = 1; foreach ($kios_fakturs_belum as $f): ?>
                                                    <?php 
                                                    $can_split        = !empty($f['can_split']);
                                                    $grand_total_asli = (float)($f['grand_total'] ?? 0); 
                                                    $tot_netto_h      = round($grand_total_asli * 0.8, 2);
                                                    $potongan_20      = round($grand_total_asli * 0.2, 2);
                                                    $tot_qty          = (float)($f['total_qty'] ?? 0);
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle">
                                                            <input type="checkbox" class="check-item-faktur-z" 
                                                                   value="<?= (int)$f['id_faktur'] ?>" 
                                                                   data-no-faktur="<?= htmlspecialchars($f['no_faktur']) ?>" 
                                                                   data-nominal-awal="<?= $grand_total_asli ?>" 
                                                                   data-total-qty="<?= $tot_qty ?>" 
                                                                   <?= $can_split ? '' : 'disabled' ?> 
                                                                   style="cursor:pointer; width:16px; height:16px;">
                                                        </td>
                                                        <td class="text-center align-middle font-weight-bold text-muted"><?= $no++ ?></td>
                                                        <td class="align-middle">
                                                            <a href="<?= base_url('sales_order/detail_faktur/' . $f['id_faktur']) ?>" 
                                                               class="badge badge-primary badge-faktur-z text-white shadow-sm"
                                                               title="Klik untuk melihat Detail Faktur Z">
                                                                <i class="fas fa-file-invoice mr-1"></i> <?= htmlspecialchars($f['no_faktur']) ?>
                                                            </a>
                                                        </td>
                                                        <td class="align-middle">
                                                            <?= !empty($f['tanggal_faktur']) ? date('d/m/Y', strtotime($f['tanggal_faktur'])) : '-' ?>
                                                        </td>
                                                        <td class="align-middle font-weight-bold text-dark">
                                                            <?= htmlspecialchars($f['no_so']) ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-light border font-weight-bold">
                                                                <?= (int)$f['total_barang'] ?> item (<?= number_format((float)$f['total_qty']) ?> pcs)
                                                            </span>
                                                        </td>
                                                        <td class="text-right align-middle font-weight-bold text-dark">
                                                            Rp <?= number_format($grand_total_asli, 0, ',', '.') ?>
                                                        </td>
                                                        <td class="text-right align-middle bg-light">
                                                            <span class="font-weight-bold text-success" style="font-size: 14px;">
                                                                Rp <?= number_format($tot_netto_h, 0, ',', '.') ?>
                                                            </span>
                                                            <div class="small text-danger" style="font-size: 11px;">
                                                                <i class="fas fa-tag mr-1"></i>-20% (- Rp <?= number_format($potongan_20, 0, ',', '.') ?>)
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <?php 
                                                             $st = strtolower((string)$f['status']);
                                                             $stClass = 'secondary';
                                                             if ($st === 'confirmed') $stClass = 'primary';
                                                             elseif ($st === 'selesai' || $st === 'selesai_do') $stClass = 'success';
                                                             elseif ($st === 'proses_do') $stClass = 'warning';
                                                             elseif ($st === 'cancelled') $stClass = 'danger';
                                                             ?>
                                                            <span class="badge badge-<?= $stClass ?> text-uppercase" style="font-size: 11px;">
                                                                <?= htmlspecialchars($f['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <div class="btn-group btn-group-sm">
                                                                <?php if ($can_split): ?>
                                                                    <button type="button"
                                                                            class="btn btn-warning btn-sm font-weight-bold shadow-sm btn-pecah-faktur"
                                                                            data-id="<?= (int)$f['id_faktur'] ?>"
                                                                            data-no-faktur="<?= htmlspecialchars($f['no_faktur']) ?>"
                                                                            data-total-nilai="<?= $tot_netto_h ?>"
                                                                            data-nominal-awal="<?= $grand_total_asli ?>"
                                                                            data-total-qty="<?= $tot_qty ?>"
                                                                            title="Pecah Faktur Z ini">
                                                                        <i class="fas fa-cut mr-1"></i> Pecah
                                                                    </button>
                                                                    <button type="button"
                                                                            class="btn btn-info btn-sm font-weight-bold shadow-sm btn-ubah-kios-faktur"
                                                                            data-id="<?= (int)$f['id_faktur'] ?>"
                                                                            data-no-faktur="<?= htmlspecialchars($f['no_faktur']) ?>"
                                                                            data-kd-customer="<?= htmlspecialchars($f['kd_customer']) ?>"
                                                                            title="Ubah Kios / Pindahkan Faktur Z ini ke Toko Lain (Koreksi Salah Import)">
                                                                        <i class="fas fa-exchange-alt"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                                <a href="<?= base_url('sales_order/detail_faktur/' . $f['id_faktur']) ?>"
                                                                   class="btn btn-default btn-sm border"
                                                                   title="Lihat Detail Faktur Z">
                                                                    <i class="fas fa-eye text-primary"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 2: FAKTUR Z SELESAI DIPECAS -->
                            <div class="tab-pane fade" id="content-kios-sudah" role="tabpanel" aria-labelledby="tab-kios-sudah">
                                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <span class="font-weight-bold text-dark">
                                            <i class="fas fa-check-circle text-success mr-1"></i> Faktur Z Selesai Dipecah
                                        </span>
                                        <span class="text-muted small ml-1">
                                            Daftar faktur Z milik kios ini yang telah selesai dipecah menjadi faktur pecahan Kode H.
                                        </span>
                                    </div>
                                    <?php if (!empty($kios_fakturs_sudah)): ?>
                                        <div class="d-flex align-items-center mt-2 mt-md-0">
                                            <a href="<?= base_url('sales_order/cetak_semua_faktur_pecah?kd_customer=' . $kd_customer) ?>" 
                                               target="_blank"
                                               class="btn btn-primary btn-sm font-weight-bold shadow-sm mr-2"
                                               title="Cetak Semua Faktur Pecahan Kode H milik kios ini ke Printer Dot-Matrix LX-310">
                                                <i class="fas fa-print mr-1"></i> Cetak Semua Faktur H (LX-310)
                                            </a>
                                            <a href="<?= base_url('sales_order/export_faktur_pecah?kd_customer=' . $kd_customer) ?>" 
                                               class="btn btn-success btn-sm font-weight-bold shadow-sm"
                                               title="Export Seluruh Faktur Pecahan Kode H milik kios ini ke Excel (.xlsx)">
                                                <i class="fas fa-file-excel mr-1"></i> Export Excel (.xlsx)
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="table-responsive p-3">
                                    <table class="table table-hover table-striped mb-0 text-nowrap" id="tableFakturZKiosSudah">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="40" class="text-center">No</th>
                                                <th>No. Faktur Z</th>
                                                <th>Tanggal</th>
                                                <th>No. SO</th>
                                                <th class="text-center">Total Item</th>
                                                <th class="text-right">Grand Total (Awal)</th>
                                                <th class="text-right bg-light">Netto (-20%)</th>
                                                <th class="text-center">Status</th>
                                                <th width="100" class="text-center no-sort">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($kios_fakturs_sudah)): ?>
                                                <tr>
                                                    <td colspan="9" class="text-center py-5 text-muted">
                                                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i><br>
                                                        Belum ada Faktur Z milik kios ini yang selesai dipecah.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $no = 1; foreach ($kios_fakturs_sudah as $fs): ?>
                                                    <?php 
                                                    $grand_total_asli = (float)($fs['grand_total'] ?? 0); 
                                                    $tot_netto_h      = round($grand_total_asli * 0.8, 2);
                                                    ?>
                                                    <tr>
                                                        <td class="text-center align-middle font-weight-bold text-muted"><?= $no++ ?></td>
                                                        <td class="align-middle">
                                                            <a href="<?= base_url('sales_order/detail_faktur/' . $fs['id_faktur']) ?>" 
                                                               class="badge badge-primary badge-faktur-z text-white shadow-sm"
                                                               title="Klik untuk melihat Detail Faktur Z">
                                                                <i class="fas fa-file-invoice mr-1"></i> <?= htmlspecialchars($fs['no_faktur']) ?>
                                                            </a>
                                                        </td>
                                                        <td class="align-middle">
                                                            <?= !empty($fs['tanggal_faktur']) ? date('d/m/Y', strtotime($fs['tanggal_faktur'])) : '-' ?>
                                                        </td>
                                                        <td class="align-middle font-weight-bold text-dark">
                                                            <?= htmlspecialchars($fs['no_so']) ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-light border font-weight-bold">
                                                                <?= (int)$fs['total_barang'] ?> item (<?= number_format((float)$fs['total_qty']) ?> pcs)
                                                            </span>
                                                        </td>
                                                        <td class="text-right align-middle font-weight-bold text-dark">
                                                            Rp <?= number_format($grand_total_asli, 0, ',', '.') ?>
                                                        </td>
                                                        <td class="text-right align-middle bg-light font-weight-bold text-success">
                                                            Rp <?= number_format($tot_netto_h, 0, ',', '.') ?>
                                                        </td>

                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-success badge-status-pecah font-weight-bold">
                                                                <i class="fas fa-check-circle mr-1"></i> Selesai Dipecah
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="<?= base_url('sales_order/detail_faktur/' . $fs['id_faktur']) ?>"
                                                                   class="btn btn-default btn-sm border"
                                                                   title="Lihat Detail Faktur Z">
                                                                    <i class="fas fa-eye text-primary"></i>
                                                                </a>
                                                                <a href="<?= base_url('sales_order/cetak_semua_faktur_pecah?parent_id_faktur=' . $fs['id_faktur']) ?>"
                                                                   target="_blank"
                                                                   class="btn btn-default btn-sm border"
                                                                   title="Cetak Seluruh Faktur H dari Faktur Z ini (LX-310)">
                                                                    <i class="fas fa-print text-info"></i>
                                                                </a>
                                                                <a href="<?= base_url('sales_order/reset_pecah_faktur_induk/' . $fs['id_faktur']) ?>"
                                                                   class="btn btn-outline-danger btn-sm"
                                                                   title="Reset Pemecahan (Kembalikan ke status Belum Dipecah)"
                                                                   onclick="return confirm('Apakah Anda yakin ingin mereset pemecahan Faktur Z <?= htmlspecialchars($fs['no_faktur']) ?>? Seluruh faktur pecahan Kode H dari faktur ini akan dihapus dan kuantitas barang dikembalikan.')">
                                                                    <i class="fas fa-undo mr-1"></i> Reset
                                                                </a>
                                                            </div>
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
                </div>

            </div>
        </section>
    </div>

    <!-- MODAL PECAH SEMUA FAKTUR Z KIOS SEKALIGUS (AUTO-SPLIT) -->
    <div class="modal fade" id="modalAutoSplitKios" tabindex="-1" role="dialog" aria-labelledby="modalAutoSplitKiosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark py-2">
                    <h5 class="modal-title font-weight-bold" id="modalAutoSplitKiosLabel">
                        <i class="fas fa-magic mr-1"></i> Pecahkan Semua Faktur Z Kios Sekaligus
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?= base_url('sales_order/split_faktur_batch') ?>" method="post" id="formAutoSplitKios">
                    <!-- Kirim seluruh ID Faktur Z milik kios ini yang siap dipecah -->
                    <?php foreach ($kios_fakturs as $kf): ?>
                        <?php if (!empty($kf['can_split'])): ?>
                            <input type="hidden" name="id_faktur[]" value="<?= (int)$kf['id_faktur'] ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <input type="hidden" name="auto_split" value="1">
                    <input type="hidden" name="kd_kios" value="<?= htmlspecialchars($kd_customer) ?>">

                    <div class="modal-body p-3">
                        <!-- Ringkasan Kios & Data Faktur yang Diproses -->
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                                    <div class="bg-primary text-white rounded p-2 mr-2 text-center" style="width: 34px; height: 34px; line-height: 18px;">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div>
                                        <strong class="text-dark d-block"><?= htmlspecialchars($nama_kios) ?></strong>
                                        <span class="text-muted small">Kode Customer: <strong><?= htmlspecialchars($kd_customer) ?></strong></span>
                                    </div>
                                </div>
                                <div class="row text-center mt-2">
                                    <div class="col-6 border-right">
                                        <span class="text-muted small d-block">Faktur Siap Dipecah:</span>
                                        <strong class="font-weight-bold text-warning" style="font-size: 18px;">
                                            <?= (int)($stat['belum_dipecah'] ?? 0) ?> Faktur Z
                                        </strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted small d-block">Total Netto (-20%):</span>
                                        <strong class="font-weight-bold text-success" style="font-size: 16px;">
                                            Rp <?= number_format(($stat['total_nilai'] ?? 0) * 0.8, 0, ',', '.') ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Plafon Maksimal Nilai Faktur H (Tetap Rp 25 Juta, Tanpa Kolom Input) -->
                        <input type="hidden" name="max_nominal" id="inputMaxNominal" value="25000000">
                        <div class="card border-0 mb-3" style="background-color: #f0f7ff; border-left: 4px solid #007bff !important;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small font-weight-bold text-uppercase">
                                            <i class="fas fa-shield-alt text-primary mr-1"></i> Maksimal Nilai per Faktur Pecahan (Kode H)
                                        </div>
                                        <div class="font-weight-bold text-primary mt-1" style="font-size: 17px;">
                                            Rp 25.000.000 <span class="badge badge-primary font-weight-normal ml-1" style="font-size: 11px;">Tetap / Otomatis</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-light border text-muted px-2 py-1">Plafon Maksimal</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opsi Pilihan Potongan 20% vs Tanpa Potongan -->
                        <div class="card border-0 mb-3" style="background-color: #fff9e6; border-left: 4px solid #ffc107 !important;">
                            <div class="card-body p-3">
                                <label class="font-weight-bold text-dark d-block mb-2">
                                    <i class="fas fa-percentage text-warning mr-1"></i> Skema Potongan Harga:
                                </label>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="autoPotongan20" name="pakai_potongan_20" class="custom-control-input" value="1" checked>
                                    <label class="custom-control-label font-weight-bold text-dark" for="autoPotongan20" style="cursor:pointer;">
                                        Pakai Potongan 20% <span class="badge badge-success ml-1">Standar Netto (80%)</span>
                                    </label>
                                    <small class="form-text text-muted pl-4">Harga satuan barang dipotong 20% untuk faktur pecahan H.</small>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="autoPotongan0" name="pakai_potongan_20" class="custom-control-input" value="0">
                                    <label class="custom-control-label font-weight-bold text-dark" for="autoPotongan0" style="cursor:pointer;">
                                        Tanpa Potongan (100% Penuh) <span class="badge badge-secondary ml-1">Harga Utuh</span>
                                    </label>
                                    <small class="form-text text-muted pl-4">Harga satuan barang tetap 100% sama dengan Faktur Z asli tanpa pemotongan margin/pajak 20%.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Rangkuman Aturan Sistem -->
                        <div class="alert alert-light border small text-dark mb-0">
                            <h6 class="font-weight-bold mb-2 text-primary" style="font-size: 12.5px;">
                                <i class="fas fa-info-circle mr-1"></i> Alur Pemeriksaan Sebelum Diterbitkan:
                            </h6>
                            <ul class="pl-3 mb-0" style="line-height: 1.6;">
                                <li>
                                    Data <strong>tidak langsung disimpan</strong>, melainkan akan membuka halaman <strong>Pemeriksaan Batch Split</strong> terlebih dahulu.
                                </li>
                                <li>
                                    <strong>Isolasi Terjaga:</strong> Barang dipecah per faktur induk. Barang antar Faktur Z <u>tidak akan tercampur</u>.
                                </li>
                                <li>
                                    <strong>Batas Rp 25 Juta:</strong>
                                    <ul class="pl-3">
                                        <li>Faktur Z &le; Rp 25 Juta otomatis menjadi <strong>1 Faktur H</strong>.</li>
                                        <li>Faktur Z &gt; Rp 25 Juta otomatis dipecah proporsional menjadi <strong>multiple Faktur H</strong> (&le; Rp 25 Juta).</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong>Customer Acak:</strong> Kontak penerima otomatis dialokasikan bergantian dari master customer acak kios ini.
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold px-3 shadow-sm">
                            <i class="fas fa-search mr-1"></i> Periksa Rincian Faktur Pecahan &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PECAH FAKTUR Z -->
    <div class="modal fade" id="modalBatchSplit" tabindex="-1" role="dialog" aria-labelledby="modalBatchSplitLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark py-2">
                    <h5 class="modal-title font-weight-bold" id="modalBatchSplitLabel">
                        <i class="fas fa-cut mr-1"></i> Pemecahan Faktur Z
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close" onclick="closeBatchSplitModal()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?= base_url('sales_order/split_faktur_batch') ?>" method="post" id="formBatchSplit">
                    <div class="modal-body p-3">
                        <div class="alert alert-info py-2 px-3 mb-3 small">
                            <i class="fas fa-info-circle mr-1"></i>
                            Faktur pecahan baru berawalan <strong>kode H</strong> akan dibuat di tabel terpisah tanpa memotong stok dan tanpa menjurnal ulang.
                        </div>

                        <!-- Card Pilihan Skema Potongan -->
                        <div class="card border-0 mb-3" style="background-color: #fff9e6; border-left: 4px solid #ffc107 !important;">
                            <div class="card-body p-3">
                                <label class="font-weight-bold text-dark d-block mb-2">
                                    <i class="fas fa-percentage text-warning mr-1"></i> Skema Potongan Harga:
                                </label>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="batchPotongan20" name="pakai_potongan_20" class="custom-control-input radio-potongan-batch" value="1" checked>
                                    <label class="custom-control-label font-weight-bold text-dark" for="batchPotongan20" style="cursor:pointer;">
                                        Pakai Potongan 20% <span class="badge badge-success ml-1">Standar Netto (80%)</span>
                                    </label>
                                    <small class="form-text text-muted pl-4">Harga satuan barang dipotong 20% pada faktur pecahan H.</small>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="batchPotongan0" name="pakai_potongan_20" class="custom-control-input radio-potongan-batch" value="0">
                                    <label class="custom-control-label font-weight-bold text-dark" for="batchPotongan0" style="cursor:pointer;">
                                        Tanpa Potongan (100% Penuh) <span class="badge badge-secondary ml-1">Harga Utuh</span>
                                    </label>
                                    <small class="form-text text-muted pl-4">Harga satuan barang tetap 100% utuh tanpa pemotongan margin/pajak 20%.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Card Detail Potongan 20% Netto H -->
                        <div class="card border-warning mb-3 bg-light">
                            <div class="card-body p-3">
                                <div class="row align-items-center mb-1">
                                    <div class="col-6 text-muted small">Nominal Faktur Asli:</div>
                                    <div class="col-6 text-right font-weight-bold text-dark" id="modalNominalAwal">Rp 0</div>
                                </div>
                                <div class="row align-items-center mb-1">
                                    <div class="col-6 text-muted small">Potongan Pajak/Margin (20%):</div>
                                    <div class="col-6 text-right font-weight-bold text-danger" id="modalNominalPotongan">- Rp 0</div>
                                </div>
                                <hr class="my-1">
                                <div class="row align-items-center">
                                    <div class="col-6 font-weight-bold text-dark">Total Netto Pecahan (H):</div>
                                    <div class="col-6 text-right font-weight-bold text-primary" style="font-size: 16px;" id="modalNominalNetto">Rp 0</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase mb-1">Faktur Z yang Dipilih:</label>
                            <div id="modalListSelectedFaktur" class="d-flex flex-wrap p-2 border rounded bg-white" style="max-height: 80px; overflow-y: auto;"></div>
                            <div id="modalHiddenFakturInputs"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1">
                                <i class="fas fa-layer-group text-warning mr-1"></i> Mau Dipecah Menjadi Berapa Faktur?
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light font-weight-bold"><i class="fas fa-hashtag"></i></span>
                                </div>
                                <input type="number" name="jumlah_pecah" id="inputJumlahPecah" class="form-control font-weight-bold text-center" 
                                       value="2" min="2" max="50" required style="font-size: 18px;">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-light font-weight-bold">Faktur Baru (Kode H)</span>
                                </div>
                            </div>
                            <small class="form-text text-muted mt-1">
                                <i class="fas fa-calculator mr-1"></i> Kuantitas total <strong><span id="modalHeaderQtyBadge">0 pcs</span></strong> akan didistribusikan secara rata.
                            </small>
                        </div>

                        <!-- Card Estimasi Per Faktur -->
                        <div class="card bg-light border-0 rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted font-weight-bold text-uppercase">Estimasi Kuantitas / Faktur:</span>
                                <span class="font-weight-bold text-dark" id="modalEstQty">0 pcs / faktur</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="small text-muted font-weight-bold text-uppercase">Estimasi Nilai Netto / Faktur:</span>
                                <span class="font-weight-bold text-success" id="modalEstNilai">Rp 0 / faktur</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" onclick="closeBatchSplitModal()">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold shadow-sm">
                            <i class="fas fa-arrow-right mr-1"></i> Lanjut Distribusi Penerima
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL UBAH KIOS / CUSTOMER FAKTUR Z -->
    <div class="modal fade" id="modalUbahCustomerFakturZ" tabindex="-1" role="dialog" aria-labelledby="modalUbahCustomerLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white py-2">
                    <h5 class="modal-title font-weight-bold" id="modalUbahCustomerLabel">
                        <i class="fas fa-exchange-alt mr-1"></i> Pindahkan Kios / Customer Faktur Z
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?= base_url('sales_order/ubah_customer_faktur_z') ?>" method="post" id="formUbahCustomerFakturZ">
                    <div class="modal-body p-3">
                        <div class="alert alert-light border small text-dark mb-3">
                            <i class="fas fa-info-circle text-info mr-1"></i>
                            Gunakan fitur ini jika terjadi <strong>salah mengisi kode customer saat import Faktur Z</strong>. Faktur yang dipindahkan akan langsung dialihkan ke kios tujuan tanpa perlu di-import ulang.
                        </div>

                        <div class="mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase mb-1">Faktur Z yang Akan Dipindahkan:</label>
                            <div id="modalUbahCustListFaktur" class="d-flex flex-wrap p-2 border rounded bg-white" style="max-height: 85px; overflow-y: auto;"></div>
                            <div id="modalUbahCustHiddenInputs"></div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase mb-1">Kios / Customer Saat Ini:</label>
                            <input type="text" class="form-control form-control-sm bg-light font-weight-bold text-dark" id="modalUbahCustCurrent" readonly value="<?= htmlspecialchars(($customer['nama_kios'] ?? $customer['nama_customer']) . ' (' . $kd_customer . ')') ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1">
                                <i class="fas fa-store text-info mr-1"></i> Pilih Kios / Customer Tujuan (Baru): <span class="text-danger">*</span>
                            </label>
                            <select name="kd_customer_baru" id="selectKdCustomerBaru" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Cari & Pilih Kios / Customer Tujuan --</option>
                                <?php if (!empty($all_customers)): ?>
                                    <?php foreach ($all_customers as $ac): ?>
                                        <?php 
                                        $display_label = (!empty($ac['nama_kios']) && $ac['nama_kios'] !== $ac['nama_customer'])
                                            ? $ac['nama_kios'] . ' (' . $ac['nama_customer'] . ')'
                                            : $ac['nama_customer'];
                                        $is_curr = ($ac['kd_customer'] === $kd_customer);
                                        ?>
                                        <option value="<?= htmlspecialchars($ac['kd_customer']) ?>" <?= $is_curr ? 'disabled' : '' ?>>
                                            [<?= htmlspecialchars($ac['kd_customer']) ?>] <?= htmlspecialchars($display_label) ?> <?= $is_curr ? '(Kios Saat Ini)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted mt-1">
                                Ketik kode customer atau nama toko/kios untuk mencari.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-info btn-sm font-weight-bold shadow-sm" id="btnSubmitUbahCustomer">
                            <i class="fas fa-check mr-1"></i> Simpan & Pindahkan Faktur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $this->load->view('partial/main/footer') ?>
</div>

<script>
window.currentModalTotalNilai = 0;
window.currentModalTotalNominalAwal = 0;
window.currentModalTotalQty = 0;

function formatRupiahJs(num) {
    num = Math.round(num);
    return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function isPakaiPotongan20() {
    var radio0 = document.getElementById('batchPotongan0');
    if (radio0 && radio0.checked) {
        return false;
    }
    return true;
}

function recalculateModalValues() {
    var pakaiPotongan = isPakaiPotongan20();
    var nominalAwal   = window.currentModalTotalNominalAwal || 0;
    var factor        = pakaiPotongan ? 0.8 : 1.0;
    var totalNilai    = Math.round(nominalAwal * factor);
    var potongan      = pakaiPotongan ? Math.round(nominalAwal * 0.2) : 0;

    window.currentModalTotalNilai = totalNilai;

    var nomAwalEl     = document.getElementById('modalNominalAwal');
    var nomPotonganEl = document.getElementById('modalNominalPotongan');
    var nomNettoEl    = document.getElementById('modalNominalNetto');
    var headerQtyEl   = document.getElementById('modalHeaderQtyBadge');

    if (nomAwalEl) nomAwalEl.innerText = formatRupiahJs(nominalAwal);
    if (nomPotonganEl) {
        if (pakaiPotongan) {
            nomPotonganEl.innerText = '- ' + formatRupiahJs(potongan) + ' (20%)';
            nomPotonganEl.className = 'col-6 text-right font-weight-bold text-danger';
        } else {
            nomPotonganEl.innerText = 'Rp 0 (0% - Tanpa Potongan)';
            nomPotonganEl.className = 'col-6 text-right font-weight-bold text-muted';
        }
    }
    if (nomNettoEl) nomNettoEl.innerText = formatRupiahJs(totalNilai);
    if (headerQtyEl) headerQtyEl.innerText = (window.currentModalTotalQty || 0).toLocaleString('id-ID') + ' pcs';

    updateModalEstimasi();
}

function updateModalEstimasi() {
    var inputPecah = document.getElementById('inputJumlahPecah');
    var estQtyEl   = document.getElementById('modalEstQty');
    var estNilaiEl = document.getElementById('modalEstNilai');

    var num = parseInt(inputPecah ? inputPecah.value : 2, 10);
    if (isNaN(num) || num < 2) num = 2;

    var estQty   = window.currentModalTotalQty / num;
    var estNilai = window.currentModalTotalNilai / num;

    var formattedQty = (estQty % 1 === 0) ? estQty.toLocaleString('id-ID') : estQty.toFixed(2);
    if (estQtyEl) estQtyEl.innerText = formattedQty + ' pcs / faktur';
    if (estNilaiEl) estNilaiEl.innerText = formatRupiahJs(estNilai) + ' / faktur';
}

window.closeBatchSplitModal = function() {
    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
        window.jQuery('#modalBatchSplit').modal('hide');
    }
    var modal = document.getElementById('modalBatchSplit');
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
    document.body.classList.remove('modal-open');
    var defaultBds = document.querySelectorAll('.modal-backdrop');
    for (var j = 0; j < defaultBds.length; j++) {
        defaultBds[j].remove();
    }
};

window.openSplitModalSingle = function(fid, noFaktur, totalNilai, nominalAwal, totalQty) {
    nominalAwal = parseFloat(nominalAwal) || 0;
    totalQty    = parseFloat(totalQty) || 0;

    window.currentModalTotalNominalAwal = nominalAwal;
    window.currentModalTotalQty         = totalQty;

    // Reset pilihan potongan ke default (20%)
    var radio20 = document.getElementById('batchPotongan20');
    if (radio20) radio20.checked = true;

    var titleEl = document.getElementById('modalBatchSplitLabel');
    if (titleEl) {
        titleEl.innerHTML = '<i class="fas fa-cut mr-1"></i> Pemecahan Faktur Z: <u>' + noFaktur + '</u>';
    }

    var listEl = document.getElementById('modalListSelectedFaktur');
    if (listEl) {
        listEl.innerHTML = '<span class="badge badge-primary mr-1 mb-1" style="font-size:12px; padding:4px 7px;"><i class="fas fa-file-invoice mr-1"></i>' + noFaktur + '</span>';
    }

    var hiddenContainer = document.getElementById('modalHiddenFakturInputs');
    if (hiddenContainer) {
        hiddenContainer.innerHTML = '<input type="hidden" name="id_faktur[]" value="' + fid + '">';
    }

    recalculateModalValues();

    var inputPecah = document.getElementById('inputJumlahPecah');
    if (inputPecah) {
        inputPecah.value = 2;
        if (!inputPecah.hasAttribute('data-has-calc-listener')) {
            inputPecah.setAttribute('data-has-calc-listener', '1');
            inputPecah.addEventListener('input', updateModalEstimasi);
        }
    }

    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
        window.jQuery('#modalBatchSplit').modal('show');
    } else {
        var modal = document.getElementById('modalBatchSplit');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        }
    }
};

// Buka modal Ubah Customer untuk 1 faktur
window.openUbahCustomerSingle = function(fid, noFaktur, kdCustomer) {
    var titleEl = document.getElementById('modalUbahCustomerLabel');
    if (titleEl) {
        titleEl.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> Pindahkan Faktur Z: <u>' + noFaktur + '</u>';
    }

    var listEl = document.getElementById('modalUbahCustListFaktur');
    if (listEl) {
        listEl.innerHTML = '<span class="badge badge-primary mr-1 mb-1" style="font-size:12px; padding:4px 7px;"><i class="fas fa-file-invoice mr-1"></i>' + noFaktur + '</span>';
    }

    var hiddenContainer = document.getElementById('modalUbahCustHiddenInputs');
    if (hiddenContainer) {
        hiddenContainer.innerHTML = '<input type="hidden" name="id_faktur[]" value="' + fid + '">';
    }

    if (window.jQuery) {
        $('#selectKdCustomerBaru').val('').trigger('change');
        $('#modalUbahCustomerFakturZ').modal('show');
    }
};

// Buka modal Ubah Customer untuk faktur-faktur yang dicentang
function openUbahCustomerModalSelected() {
    var checkedItems = document.querySelectorAll('.check-item-faktur-z:checked');
    if (checkedItems.length === 0) {
        alert('Silakan pilih minimal 1 Faktur Z yang ingin dipindahkan.');
        return;
    }

    var badgesHtml = '';
    var hiddenInputsHtml = '';

    checkedItems.forEach(function(item) {
        var fid = item.value;
        var noFaktur = item.getAttribute('data-no-faktur') || ('#' + fid);
        badgesHtml += '<span class="badge badge-primary mr-1 mb-1" style="font-size:12px; padding:4px 7px;"><i class="fas fa-file-invoice mr-1"></i>' + noFaktur + '</span>';
        hiddenInputsHtml += '<input type="hidden" name="id_faktur[]" value="' + fid + '">';
    });

    var titleEl = document.getElementById('modalUbahCustomerLabel');
    if (titleEl) {
        titleEl.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i> Pindahkan <strong>' + checkedItems.length + ' Faktur Z Terpilih</strong>';
    }

    var listEl = document.getElementById('modalUbahCustListFaktur');
    if (listEl) {
        listEl.innerHTML = badgesHtml;
    }

    var hiddenContainer = document.getElementById('modalUbahCustHiddenInputs');
    if (hiddenContainer) {
        hiddenContainer.innerHTML = hiddenInputsHtml;
    }

    if (window.jQuery) {
        $('#selectKdCustomerBaru').val('').trigger('change');
        $('#modalUbahCustomerFakturZ').modal('show');
    }
}

// Fungsi update status checkbox pilihan faktur Z
function updateSelectedFakturZState() {
    var checkItems = document.querySelectorAll('.check-item-faktur-z:not(:disabled)');
    var checkedItems = document.querySelectorAll('.check-item-faktur-z:checked');
    var count = checkedItems.length;

    var countBadge = document.getElementById('countSelectedFakturZ');
    if (countBadge) countBadge.innerText = count;

    var countPindahBadge = document.getElementById('countSelectedPindahKios');
    if (countPindahBadge) countPindahBadge.innerText = count;

    var btnPecah = document.getElementById('btnPecahSelectedFakturZ');
    if (btnPecah) {
        btnPecah.style.display = count > 0 ? 'inline-block' : 'none';
    }

    var btnPindah = document.getElementById('btnPindahKiosSelectedFakturZ');
    if (btnPindah) {
        btnPindah.style.display = count > 0 ? 'inline-block' : 'none';
    }

    var masterCheck = document.getElementById('checkAllFakturZKios');
    if (masterCheck) {
        if (checkItems.length > 0 && count === checkItems.length) {
            masterCheck.checked = true;
            masterCheck.indeterminate = false;
        } else if (count > 0) {
            masterCheck.checked = false;
            masterCheck.indeterminate = true;
        } else {
            masterCheck.checked = false;
            masterCheck.indeterminate = false;
        }
    }
}

// Buka modal batch split untuk faktur-faktur yang dicentang
function openBatchSplitModalSelected() {
    var checkedItems = document.querySelectorAll('.check-item-faktur-z:checked');
    if (checkedItems.length === 0) {
        alert('Silakan pilih minimal 1 Faktur Z terlebih dahulu.');
        return;
    }

    var totalNominalAwal = 0;
    var totalQty = 0;
    var badgesHtml = '';
    var hiddenInputsHtml = '';

    checkedItems.forEach(function(item) {
        var fid = item.value;
        var noFaktur = item.getAttribute('data-no-faktur') || ('#' + fid);
        var nomAwal = parseFloat(item.getAttribute('data-nominal-awal')) || 0;
        var qty = parseFloat(item.getAttribute('data-total-qty')) || 0;

        totalNominalAwal += nomAwal;
        totalQty += qty;

        badgesHtml += '<span class="badge badge-primary mr-1 mb-1" style="font-size:12px; padding:4px 7px;"><i class="fas fa-file-invoice mr-1"></i>' + noFaktur + '</span>';
        hiddenInputsHtml += '<input type="hidden" name="id_faktur[]" value="' + fid + '">';
    });

    window.currentModalTotalNominalAwal = totalNominalAwal;
    window.currentModalTotalQty         = totalQty;

    // Reset pilihan potongan ke default (20%)
    var radio20 = document.getElementById('batchPotongan20');
    if (radio20) radio20.checked = true;

    var titleEl = document.getElementById('modalBatchSplitLabel');
    if (titleEl) {
        titleEl.innerHTML = '<i class="fas fa-cut mr-1"></i> Pemecahan Batch: <strong>' + checkedItems.length + ' Faktur Z Terpilih</strong>';
    }

    var listEl = document.getElementById('modalListSelectedFaktur');
    if (listEl) {
        listEl.innerHTML = badgesHtml;
    }

    var hiddenContainer = document.getElementById('modalHiddenFakturInputs');
    if (hiddenContainer) {
        hiddenContainer.innerHTML = hiddenInputsHtml;
    }

    recalculateModalValues();

    var inputPecah = document.getElementById('inputJumlahPecah');
    if (inputPecah) {
        inputPecah.value = 2;
        if (!inputPecah.hasAttribute('data-has-calc-listener')) {
            inputPecah.setAttribute('data-has-calc-listener', '1');
            inputPecah.addEventListener('input', updateModalEstimasi);
        }
    }

    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
        window.jQuery('#modalBatchSplit').modal('show');
    } else {
        var modal = document.getElementById('modalBatchSplit');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Listener radio pilihan potongan pada modal batch
    var radiosPotongan = document.querySelectorAll('.radio-potongan-batch');
    radiosPotongan.forEach(function(radio) {
        radio.addEventListener('change', function() {
            recalculateModalValues();
        });
    });

    // Checkbox Master
    var masterCheck = document.getElementById('checkAllFakturZKios');
    if (masterCheck) {
        masterCheck.addEventListener('change', function() {
            var checkItems = document.querySelectorAll('.check-item-faktur-z:not(:disabled)');
            checkItems.forEach(function(item) {
                item.checked = masterCheck.checked;
            });
            updateSelectedFakturZState();
        });
    }

    // Tombol Toggle Pilih Semua di Toolbar
    var btnToggle = document.getElementById('btnToggleSelectAllFakturZ');
    if (btnToggle) {
        btnToggle.addEventListener('click', function(e) {
            e.preventDefault();
            var checkItems = document.querySelectorAll('.check-item-faktur-z:not(:disabled)');
            var checkedItems = document.querySelectorAll('.check-item-faktur-z:checked');
            var shouldCheck = checkedItems.length !== checkItems.length;

            checkItems.forEach(function(item) {
                item.checked = shouldCheck;
            });
            updateSelectedFakturZState();
        });
    }

    // Tombol Pecah Terpilih
    var btnPecahSelected = document.getElementById('btnPecahSelectedFakturZ');
    if (btnPecahSelected) {
        btnPecahSelected.addEventListener('click', function(e) {
            e.preventDefault();
            openBatchSplitModalSelected();
        });
    }

    // Tombol Pindah Kios Terpilih
    var btnPindahSelected = document.getElementById('btnPindahKiosSelectedFakturZ');
    if (btnPindahSelected) {
        btnPindahSelected.addEventListener('click', function(e) {
            e.preventDefault();
            openUbahCustomerModalSelected();
        });
    }

    // Inisialisasi select2 modal ubah customer jika tersedia
    if (window.jQuery && typeof $.fn.select2 === 'function') {
        $('#selectKdCustomerBaru').select2({
            dropdownParent: $('#modalUbahCustomerFakturZ'),
            placeholder: '-- Cari & Pilih Kios / Customer Tujuan --',
            allowClear: true
        });
    }
});

document.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('check-item-faktur-z')) {
        updateSelectedFakturZState();
    }
});

document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-pecah-faktur');
    if (btn) {
        e.preventDefault();
        var fid = btn.getAttribute('data-id');
        var noFaktur = btn.getAttribute('data-no-faktur') || ('#' + fid);
        var nominalAwal = parseFloat(btn.getAttribute('data-nominal-awal')) || 0;
        var totalNilai = parseFloat(btn.getAttribute('data-total-nilai')) || (nominalAwal * 0.8);
        var totalQty = parseFloat(btn.getAttribute('data-total-qty')) || 0;

        window.openSplitModalSingle(fid, noFaktur, totalNilai, nominalAwal, totalQty);
    }

    var btnUbah = e.target.closest('.btn-ubah-kios-faktur');
    if (btnUbah) {
        e.preventDefault();
        var fid = btnUbah.getAttribute('data-id');
        var noFaktur = btnUbah.getAttribute('data-no-faktur') || ('#' + fid);
        var kdCust = btnUbah.getAttribute('data-kd-customer') || '';
        window.openUbahCustomerSingle(fid, noFaktur, kdCust);
    }

    if (e.target.closest && e.target.closest('[data-dismiss="modal"]')) {
        window.closeBatchSplitModal();
    }
});

// Setup DataTables
if (window.jQuery) {
    $(function() {
        var baseDtConfig = {
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": false,
            "pageLength": 25,
            "columnDefs": [
                { "orderable": false, "targets": "no-sort" }
            ],
            "language": {
                "search": "Cari data:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Tidak ada faktur yang cocok",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ faktur",
                "infoEmpty": "Menampilkan 0 faktur",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Lanjut",
                    "previous": "Sebelum"
                }
            }
        };

        if (!$.fn.DataTable.isDataTable('#tableFakturZKiosBelum')) {
            var configBelum = $.extend({}, baseDtConfig, {
                "order": [[1, "asc"]] // Urut berdasarkan No (kolom ke-1), karena kolom ke-0 adalah checkbox
            });
            var tableBelum = $('#tableFakturZKiosBelum').DataTable(configBelum);

            // Re-sync status checkbox saat ganti halaman / pencarian DataTables
            tableBelum.on('draw', function() {
                updateSelectedFakturZState();
            });
        }

        if (!$.fn.DataTable.isDataTable('#tableFakturZKiosSudah')) {
            var configSudah = $.extend({}, baseDtConfig, {
                "order": [[0, "asc"]]
            });
            $('#tableFakturZKiosSudah').DataTable(configSudah);
        }
    });
}
</script>
</body>
</html>
