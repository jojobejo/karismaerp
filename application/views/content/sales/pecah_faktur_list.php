<!-- views/content/sales/pecah_faktur_list.php -->
<style>
    /* Reset gaya tabel agar solid standar (TIDAK SEPERTI CARD / SALES ORDER) */
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
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02) !important;
    }

    .pecah-faktur-summary .info-box {
        min-height: 84px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-radius: 8px;
        transition: transform 0.15s ease;
    }
    .pecah-faktur-summary .info-box:hover {
        transform: translateY(-2px);
    }
    .pecah-faktur-summary .info-box-icon {
        flex: 0 0 64px;
        width: 64px;
        height: 64px;
        min-height: 64px;
        max-height: 64px;
        border-radius: 8px;
        font-size: 26px;
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
    #tableFakturZ_wrapper, #tableFakturH_wrapper {
        padding: 0 4px 6px;
    }
    #tableFakturZ_wrapper .dataTables_filter input, #tableFakturH_wrapper .dataTables_filter input {
        height: 32px;
        border-radius: 4px;
        padding: 3px 8px;
    }

    /* Kios Grouping Styles */
    .kios-group-card {
        border: 1px solid #dbe2ea;
        border-radius: 8px;
        margin-bottom: 12px;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        transition: all 0.2s ease-in-out;
        overflow: hidden;
    }
    .table td, .table th {
        vertical-align: middle;
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
                        <h1 class="m-0 text-dark font-weight-bold">
                            <i class="fas fa-cut text-warning mr-2"></i> Modul Pecah Faktur Z
                        </h1>
                        <p class="text-muted small mb-0 mt-1">
                            Daftar seluruh Faktur Z untuk pemecahan faktur induk ke faktur turunan per customer penerima.
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">Pecah Faktur</li>
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

                <!-- Tombol Navigasi Cepat -->
                <?php $is_admkeu_only = (strtolower(trim((string)$this->session->userdata('username'))) === 'admkeu'); ?>
                <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <?php if (!$is_admkeu_only): ?>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary btn-sm mr-1">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                        </a>
                        <a href="<?= base_url('sales_order/admin_sc/activity_log') ?>" class="btn btn-outline-info btn-sm mr-1">
                            <i class="fas fa-history mr-1"></i> Log Aktivitas Faktur
                        </a>
                        <?php endif; ?>
                        <a href="<?= base_url('sales_order/customer_acak') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-users mr-1"></i> Master Customer Acak
                        </a>
                        <a href="<?= base_url('master_barang') ?>" class="btn btn-outline-primary btn-sm ml-1">
                            <i class="fas fa-boxes mr-1"></i> Master Barang
                        </a>
                        <button type="button" class="btn btn-primary btn-sm ml-1" data-toggle="modal" data-target="#modalImportMasterBarang">
                            <i class="fas fa-file-excel mr-1"></i> Import Master Barang
                        </button>
                        <button type="button" class="btn btn-success btn-sm ml-1" data-toggle="modal" data-target="#modalImportFakturZ">
                            <i class="fas fa-file-import mr-1"></i> Import Faktur Z
                        </button>
                    </div>
                    <div>
                        <span class="text-muted small">
                            <i class="fas fa-clock mr-1"></i> <?= date('d M Y H:i') ?>
                        </span>
                    </div>
                </div>

                <!-- Ringkasan Statistik -->
                <div class="row pecah-faktur-summary mb-2">
                    <div class="col-6 col-md-3">
                        <div class="info-box bg-white">
                            <span class="info-box-icon bg-primary elevation-1">
                                <i class="fas fa-file-invoice"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Total Faktur Z</span>
                                <span class="info-box-number text-primary font-weight-bold" style="font-size: 20px;">
                                    <?= number_format($stat['total']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-box bg-white">
                            <span class="info-box-icon bg-warning elevation-1 text-white">
                                <i class="fas fa-cut"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Induk Belum Dipecah</span>
                                <span class="info-box-number text-warning font-weight-bold" style="font-size: 20px;">
                                    <?= number_format($stat['belum_dipecah']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-box bg-white">
                            <span class="info-box-icon bg-success elevation-1">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Induk Sudah Dipecah</span>
                                <span class="info-box-number text-success font-weight-bold" style="font-size: 20px;">
                                    <?= number_format($stat['sudah_dipecah']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-box bg-white">
                            <span class="info-box-icon bg-info elevation-1">
                                <i class="fas fa-code-branch"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Faktur Turunan (Pecahan)</span>
                                <span class="info-box-number text-info font-weight-bold" style="font-size: 20px;">
                                    <?= number_format($stat['turunan']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Filter -->
                <div class="card card-outline card-secondary shadow-sm mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title font-weight-bold text-muted small text-uppercase">
                            <i class="fas fa-filter mr-1"></i> Filter Data Faktur Z
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <form action="<?= base_url('sales_order/pecah_faktur') ?>" method="get" id="formFilterFakturZ">
                            <div class="row align-items-end">
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <label class="small font-weight-bold mb-1">Dari Tanggal Faktur</label>
                                    <input type="date" name="date1" class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($filter['date1'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <label class="small font-weight-bold mb-1">Sampai Tanggal Faktur</label>
                                    <input type="date" name="date2" class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($filter['date2'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <label class="small font-weight-bold mb-1">Status Pemecahan</label>
                                    <select name="status_pecah" class="form-control form-control-sm">
                                        <option value="all" <?= ($filter['status_pecah'] ?? '') === 'all' ? 'selected' : '' ?>>Semua Tipe</option>
                                        <option value="belum_dipecah" <?= ($filter['status_pecah'] ?? '') === 'belum_dipecah' ? 'selected' : '' ?>>Induk Belum Dipecah</option>
                                        <option value="sudah_dipecah" <?= ($filter['status_pecah'] ?? '') === 'sudah_dipecah' ? 'selected' : '' ?>>Induk Sudah Dipecah</option>
                                        <option value="turunan" <?= ($filter['status_pecah'] ?? '') === 'turunan' ? 'selected' : '' ?>>Faktur Turunan</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2">
                                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                                        <i class="fas fa-search mr-1"></i> Terapkan
                                    </button>
                                    <a href="<?= base_url('sales_order/pecah_faktur') ?>" class="btn btn-default btn-sm mr-1">
                                        <i class="fas fa-undo mr-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Faktur Z & Faktur Pecahan H -->
                <div class="card card-outline card-primary card-outline-tabs shadow-sm">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="custom-tabs-faktur" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active font-weight-bold" id="tab-faktur-z" data-toggle="pill" href="#content-faktur-z" role="tab" aria-controls="content-faktur-z" aria-selected="true">
                                    <i class="fas fa-hourglass-start text-warning mr-1"></i> Faktur Z Induk (Belum Dipecah)
                                    <span class="badge badge-warning ml-1"><?= count($fakturs_belum_dipecah ?? []) ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold" id="tab-faktur-z-selesai" data-toggle="pill" href="#content-faktur-z-selesai" role="tab" aria-controls="content-faktur-z-selesai" aria-selected="false">
                                    <i class="fas fa-check-circle text-success mr-1"></i> Faktur Z Selesai Dipecah
                                    <span class="badge badge-success ml-1"><?= count($fakturs_sudah_dipecah ?? []) ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold" id="tab-faktur-h" data-toggle="pill" href="#content-faktur-h" role="tab" aria-controls="content-faktur-h" aria-selected="false">
                                    <i class="fas fa-tag text-info mr-1"></i> Faktur Pecahan Kode H
                                    <span class="badge badge-info ml-1"><?= count($fakturs_h) ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="custom-tabs-faktur-content">
                            <!-- TAB 1: DAFTAR KIOS DENGAN FAKTUR Z BELUM DIPECAS -->
                            <div class="tab-pane fade show active" id="content-faktur-z" role="tabpanel" aria-labelledby="tab-faktur-z">
                                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <span class="font-weight-bold text-dark" style="font-size: 15px;">
                                            <i class="fas fa-store text-warning mr-1"></i> Daftar Kios Pemilik Faktur Z (Belum Dipecah)
                                        </span>
                                        <span class="text-muted small ml-2 d-none d-md-inline">
                                            Hanya menampilkan kios-kios yang masih memiliki Faktur Z induk belum dipecah.
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge badge-warning font-weight-bold p-2 border text-dark mr-2" style="font-size: 12px;">
                                            Total: <strong><?= count($kios_grouped_belum ?? []) ?></strong> Kios (<strong><?= count($fakturs_belum_dipecah ?? []) ?></strong> Faktur Z Belum Dipecah)
                                        </span>
                                        <a href="<?= base_url('sales_order/export_faktur_z_belum?' . http_build_query($filter ?? [])) ?>" 
                                           class="btn btn-success btn-sm font-weight-bold shadow-sm"
                                           title="Export Daftar Faktur Z Induk (Belum Dipecah) per Faktur ke Excel (.xlsx)">
                                            <i class="fas fa-file-excel mr-1"></i> Export Excel (.xlsx)
                                        </a>
                                    </div>
                                </div>

                                <div class="table-responsive p-3">
                                    <table class="table table-hover table-striped mb-0 text-nowrap" id="tableKiosList">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="40" class="text-center">No</th>
                                                <th>Nama Kios</th>
                                                <th>Kode Customer & Pemilik</th>
                                                <th>Rute</th>
                                                <th class="text-center">Faktur Z Belum Dipecah</th>
                                                <th class="text-right">Total Nilai (Belum Dipecah)</th>
                                                <th>Status</th>
                                                <th width="150" class="text-center no-sort">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($kios_grouped_belum)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center py-5 text-muted">
                                                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i><br>
                                                        <h5 class="font-weight-bold text-success">Tidak Ada Faktur Z yang Belum Dipecah</h5>
                                                        <span class="text-muted">Seluruh Faktur Z induk telah selesai dipecah. Silakan periksa tab <strong>Faktur Z Selesai Dipecah</strong>.</span>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $no = 1; foreach ($kios_grouped_belum as $kios_name => $kg): ?>
                                                    <tr>
                                                        <td class="text-center align-middle font-weight-bold text-muted"><?= $no++ ?></td>
                                                        <td class="align-middle">
                                                            <div class="d-flex align-items-center">
                                                                <div class="bg-warning text-white rounded p-2 mr-2 text-center" style="width: 34px; height: 34px; line-height: 18px;">
                                                                    <i class="fas fa-store"></i>
                                                                </div>
                                                                <div>
                                                                    <a href="<?= base_url('sales_order/pecah_faktur_kios/' . $kg['kd_customer']) ?>" 
                                                                       class="font-weight-bold text-dark" style="font-size: 15px;"
                                                                       title="Buka Faktur Z Kios <?= htmlspecialchars($kg['nama_kios']) ?>">
                                                                        <?= htmlspecialchars($kg['nama_kios']) ?>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle">
                                                            <span class="font-weight-bold text-dark d-block">
                                                                <?= htmlspecialchars($kg['nama_customer']) ?>
                                                            </span>
                                                            <span class="text-muted small">
                                                                Kode: <strong class="text-primary"><?= htmlspecialchars($kg['kd_customer']) ?></strong>
                                                            </span>
                                                        </td>
                                                        <td class="align-middle">
                                                            <?php if (!empty($kg['kd_rute'])): ?>
                                                                <span class="badge badge-light border text-muted">
                                                                    <i class="fas fa-route mr-1 text-info"></i><?= htmlspecialchars($kg['kd_rute']) ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted small">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-warning badge-pill font-weight-bold px-3 py-1 text-dark" style="font-size: 12px;">
                                                                <i class="fas fa-file-invoice mr-1"></i> <?= (int)$kg['belum_dipecah'] ?> Faktur Z
                                                            </span>
                                                            <div class="small text-muted mt-1">
                                                                <?= number_format($kg['total_qty_belum'] ?? $kg['total_qty']) ?> pcs (<?= (int)($kg['total_barang_belum'] ?? $kg['total_barang']) ?> item)
                                                            </div>
                                                        </td>
                                                        <td class="text-right align-middle font-weight-bold" style="font-size: 14px;">
                                                            Rp <?= number_format((float)($kg['total_nilai_belum'] ?? $kg['total_nilai']), 0, ',', '.') ?>
                                                        </td>
                                                        <td class="align-middle">
                                                            <span class="badge badge-warning badge-status-pecah d-inline-block">
                                                                <i class="fas fa-hourglass-start mr-1"></i> <?= $kg['belum_dipecah'] ?> Belum Dipecah
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <a href="<?= base_url('sales_order/pecah_faktur_kios/' . $kg['kd_customer']) ?>" 
                                                               class="btn btn-warning btn-sm font-weight-bold shadow-sm"
                                                               title="Buka Halaman Faktur Z Kios <?= htmlspecialchars($kg['nama_kios']) ?>">
                                                                <i class="fas fa-folder-open mr-1"></i> Buka Faktur Z
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 2: DAFTAR FAKTUR Z YANG TELAH SELESAI DI PECAH -->
                            <div class="tab-pane fade" id="content-faktur-z-selesai" role="tabpanel" aria-labelledby="tab-faktur-z-selesai">
                                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <span class="font-weight-bold text-dark" style="font-size: 15px;">
                                            <i class="fas fa-check-circle text-success mr-1"></i> Daftar Faktur Z yang Telah Selesai Dipecah
                                        </span>
                                        <span class="text-muted small ml-2 d-none d-md-inline">
                                            Seluruh Faktur Z induk yang sudah tuntas dipecah menjadi faktur turunan (Kode H).
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="badge badge-success font-weight-bold p-2 border mr-2" style="font-size: 12px;">
                                            Total: <strong><?= count($fakturs_sudah_dipecah ?? []) ?></strong> Faktur Z Selesai Dipecah
                                        </span>
                                        <a href="<?= base_url('sales_order/export_faktur_z_selesai?' . http_build_query($filter ?? [])) ?>" 
                                           class="btn btn-success btn-sm font-weight-bold shadow-sm"
                                           title="Export Daftar Faktur Z yang Telah Selesai Dipecah ke Excel (.xlsx)">
                                            <i class="fas fa-file-excel mr-1"></i> Export Excel (.xlsx)
                                        </a>
                                    </div>
                                </div>

                                <div class="table-responsive p-3">
                                    <table class="table table-hover table-striped mb-0 text-nowrap" id="tableFakturZSudahPecah">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="40" class="text-center">No</th>
                                                <th>No. Faktur Z</th>
                                                <th>Tanggal</th>
                                                <th>No. SO</th>
                                                <th>Kios / Customer</th>
                                                <th class="text-center">Total Item</th>
                                                <th class="text-right">Grand Total (Awal)</th>
                                                <th class="text-right bg-light">Netto (-20%)</th>
                                                <th class="text-center">Status</th>
                                                <th width="120" class="text-center no-sort">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($fakturs_sudah_dipecah)): ?>
                                                <tr>
                                                    <td colspan="10" class="text-center py-5 text-muted">
                                                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i><br>
                                                        Belum ada Faktur Z yang selesai dipecah.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $no = 1; foreach ($fakturs_sudah_dipecah as $fs): ?>
                                                    <?php
                                                    $gt_asli = (float)($fs['grand_total'] ?? 0);
                                                    $netto_20 = round($gt_asli * 0.8, 2);
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
                                                        <td class="align-middle">
                                                            <span class="font-weight-bold text-dark d-block">
                                                                <?= htmlspecialchars(!empty($fs['nama_kios']) ? $fs['nama_kios'] : ($fs['display_customer_name'] ?? $fs['customer_name'] ?? '-')) ?>
                                                            </span>
                                                            <span class="text-muted small">
                                                                Kode: <strong class="text-primary"><?= htmlspecialchars($fs['kd_customer'] ?? '-') ?></strong>
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-light border font-weight-bold">
                                                                <?= (int)$fs['total_barang'] ?> item (<?= number_format((float)$fs['total_qty']) ?> pcs)
                                                            </span>
                                                        </td>
                                                        <td class="text-right align-middle font-weight-bold text-dark">
                                                            Rp <?= number_format($gt_asli, 0, ',', '.') ?>
                                                        </td>
                                                        <td class="text-right align-middle bg-light font-weight-bold text-success">
                                                            Rp <?= number_format($netto_20, 0, ',', '.') ?>
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
                                                                <a href="<?= base_url('sales_order/pecah_faktur_kios/' . $fs['kd_customer']) ?>"
                                                                   class="btn btn-outline-secondary btn-sm"
                                                                   title="Buka Kios">
                                                                    <i class="fas fa-store"></i>
                                                                </a>
                                                                <a href="<?= base_url('sales_order/reset_pecah_faktur_induk/' . $fs['id_faktur']) ?>"
                                                                   class="btn btn-outline-danger btn-sm"
                                                                   title="Reset Pemecahan (Kembalikan ke status Belum Dipecah)"
                                                                   onclick="return confirm('Apakah Anda yakin ingin mereset pemecahan Faktur Z <?= htmlspecialchars($fs['no_faktur']) ?>? Seluruh faktur pecahan Kode H dari faktur ini akan dihapus dan kuantitas barang dikembalikan.')">
                                                                    <i class="fas fa-undo"></i>
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

                            <!-- TAB 2: FAKTUR PECAHAN KODE H -->
                            <div class="tab-pane fade" id="content-faktur-h" role="tabpanel" aria-labelledby="tab-faktur-h">
                                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="d-flex align-items-center flex-wrap mr-auto mb-2 mb-md-0">
                                        <button type="button" 
                                                id="btnToggleSelectAllFakturH" 
                                                class="btn btn-outline-secondary btn-sm font-weight-bold mr-2 shadow-sm" 
                                                title="Pilih / Batalkan pilihan semua faktur pecahan yang tampil di halaman ini">
                                            <i class="far fa-check-square mr-1"></i> Pilih Semua
                                        </button>
                                        <button type="button" 
                                                id="btnLunasiBatchFakturH" 
                                                class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2" 
                                                style="display:none;" 
                                                title="Lunasi seluruh faktur pecahan belum lunas yang sedang dicentang & buat jurnal Q + A">
                                            <i class="fas fa-check-double mr-1"></i> Lunasi Terpilih (<span id="countSelectedUnpaidFakturH">0</span>)
                                        </button>
                                        <button type="button" 
                                                id="btnUnpostPelunasanBatchFakturH" 
                                                class="btn btn-warning btn-sm font-weight-bold shadow-sm mr-2 text-dark" 
                                                style="display:none;" 
                                                title="Batalkan pelunasan faktur pecahan yang sedang dicentang & hapus jurnal terkait">
                                            <i class="fas fa-undo mr-1"></i> Unpost Bayar Terpilih (<span id="countSelectedLunasFakturH">0</span>)
                                        </button>
                                        <button type="button" 
                                                id="btnUnpostBatchFakturH" 
                                                class="btn btn-danger btn-sm font-weight-bold shadow-sm mr-2" 
                                                style="display:none;" 
                                                title="Unpost seluruh faktur pecahan yang sedang dicentang">
                                            <i class="fas fa-undo mr-1"></i> Unpost Terpilih (<span id="countSelectedFakturH">0</span>)
                                        </button>
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <?php
                                            $totalH = count($fakturs_h);
                                            $lunasH = 0;
                                            $belumH = 0;
                                            foreach ($fakturs_h as $fh) {
                                                if (($fh['status_bayar'] ?? '') === 'lunas') {
                                                    $lunasH++;
                                                } else {
                                                    $belumH++;
                                                }
                                            }
                                        ?>
                                        <span class="badge badge-secondary p-2 mr-1" title="Total Faktur Pecahan H">
                                            Total: <strong><?= $totalH ?></strong>
                                        </span>
                                        <span class="badge badge-success p-2 mr-1" title="Faktur Pecahan H yang Sudah Lunas">
                                            Lunas: <strong><?= $lunasH ?></strong>
                                        </span>
                                        <span class="badge badge-warning p-2 mr-3 text-dark" title="Faktur Pecahan H yang Belum Lunas">
                                            Belum: <strong><?= $belumH ?></strong>
                                        </span>

                                        <button type="button" 
                                                class="btn btn-purple text-white btn-sm font-weight-bold shadow-sm mr-2" 
                                                style="background-color: #6f42c1; border-color: #6f42c1;" 
                                                data-toggle="modal" 
                                                data-target="#modalImportPelunasanFakturZ" 
                                                title="Import file pelunasan berdasarkan Nomor Faktur Z Induk dari Excel / CSV (otomatis mengalokasikan ke Faktur Pecahan H & membuat Jurnal Q/A)">
                                            <i class="fas fa-file-invoice-dollar mr-1"></i> Import Pelunasan Faktur Z
                                        </button>

                                        <a href="<?= base_url('sales_order/sinkronkan_jurnal_penjualan_faktur_h') ?>" 
                                           class="btn btn-outline-info btn-sm font-weight-bold shadow-sm mr-2"
                                           id="btnSyncJurnalPenjualanH"
                                           title="Sinkronkan Jurnal Penjualan resmi Faktur Pecahan H dan sesuaikan status Jurnal Faktur Z Induk agar masuk Neraca & Laba Rugi"
                                           onclick="return confirm('Apakah Anda ingin memproses dan memastikan seluruh Faktur Pecahan H memiliki Jurnal Penjualan resmi yang diakui di Laporan Keuangan?')">
                                            <i class="fas fa-sync-alt mr-1"></i> Sinkron Jurnal Penjualan H
                                        </a>

                                        <a href="<?= base_url('sales_order/export_faktur_z_pecah_lunas') ?>" 
                                           class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2"
                                           id="btnExportFakturZLunas"
                                           title="Export Faktur Pecahan H yang Sudah Lunas beserta Jurnal Q & A ke Excel (Format Report Jurnal)">
                                            <i class="fas fa-file-excel mr-1"></i> Export Lunas & Jurnal
                                        </a>

                                        <a href="<?= base_url('sales_order/cetak_semua_faktur_pecah?' . http_build_query(array_filter($filter ?? []))) ?>" 
                                           target="_blank"
                                           class="btn btn-primary btn-sm font-weight-bold shadow-sm"
                                           id="btnCetakSemuaFakturH"
                                           title="Cetak Semua Faktur Pecahan Kode H Sekaligus ke Printer Dot-Matrix Epson LX-310">
                                            <i class="fas fa-print mr-1"></i> Cetak Semua (LX-310)
                                        </a>
                                    </div>
                                </div>
                                <div class="table-responsive p-3">
                                    <table class="table table-hover table-striped mb-0 text-nowrap" id="tableFakturH">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="35" class="text-center no-sort">
                                                    <input type="checkbox" id="checkAllFakturH" title="Centang / Hilangkan centang semua pada halaman ini" style="cursor:pointer; width:16px; height:16px;">
                                                </th>
                                                <th width="40" class="text-center">No</th>
                                                <th>No. Faktur Pecahan</th>
                                                <th>Faktur Induk Z</th>
                                                <th>Tanggal</th>
                                                <th>Customer Penerima</th>
                                                <th class="text-center">Total Item</th>
                                                <th class="text-right">Total Nilai</th>
                                                <th class="text-center">Status Bayar</th>
                                                <th class="text-center">Jurnal Penjualan</th>
                                                <th class="text-center">Jurnal Pelunasan</th>
                                                <th width="120" class="text-center no-sort">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($fakturs_h)): ?>
                                                <tr>
                                                    <td colspan="12" class="text-center py-5 text-muted">
                                                        <i class="fas fa-inbox fa-2x mb-2 text-warning"></i><br>
                                                        Belum ada faktur pecahan (kode H) yang dibuat.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $no = 1; foreach ($fakturs_h as $fh): 
                                                    $isLunas = ($fh['status_bayar'] ?? '') === 'lunas';
                                                ?>
                                                    <tr data-id-pecah="<?= (int)$fh['id_pecah'] ?>" data-status-bayar="<?= $isLunas ? 'lunas' : 'belum_lunas' ?>">
                                                        <td class="text-center align-middle">
                                                            <input type="checkbox" class="check-item-faktur-h" value="<?= (int)$fh['id_pecah'] ?>" data-no-faktur="<?= htmlspecialchars($fh['no_faktur']) ?>" data-status-bayar="<?= $isLunas ? 'lunas' : 'belum_lunas' ?>" style="cursor:pointer; width:16px; height:16px;">
                                                        </td>
                                                        <td class="text-center align-middle font-weight-bold text-muted"><?= $no++ ?></td>
                                                        <td class="align-middle">
                                                            <a href="<?= base_url('sales_order/detail_faktur_pecah/' . $fh['id_pecah']) ?>" 
                                                               class="badge badge-warning text-dark font-weight-bold shadow-sm" style="font-size: 12px; padding: 5px 9px;">
                                                                <i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($fh['no_faktur']) ?>
                                                            </a>
                                                        </td>
                                                        <td class="align-middle">
                                                            <a href="<?= base_url('sales_order/detail_faktur/' . $fh['parent_id_faktur']) ?>" 
                                                               class="badge badge-primary font-weight-bold" style="font-size: 11px;">
                                                                <i class="fas fa-file-invoice mr-1"></i> <?= htmlspecialchars($fh['parent_no_faktur']) ?>
                                                            </a>
                                                        </td>
                                                        <td class="align-middle">
                                                            <?= !empty($fh['tanggal_faktur']) ? date('d/m/Y', strtotime($fh['tanggal_faktur'])) : '-' ?>
                                                        </td>
                                                        <td class="align-middle">
                                                            <span class="font-weight-bold text-dark d-block">
                                                                <?= htmlspecialchars($fh['customer_name'] ?? '-') ?>
                                                            </span>
                                                            <span class="text-muted small">
                                                                Kode: <?= htmlspecialchars($fh['kd_customer'] ?? '-') ?>
                                                                <?php if (!empty($fh['kontak_person'])): ?>
                                                                    | Kontak: <strong><?= htmlspecialchars($fh['kontak_person']) ?></strong>
                                                                <?php endif; ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-light border font-weight-bold">
                                                                <?= (int)$fh['total_barang'] ?> item (<?= number_format((float)$fh['total_qty']) ?> pcs)
                                                            </span>
                                                        </td>
                                                        <td class="text-right align-middle font-weight-bold text-success">
                                                            Rp <?= number_format((float)$fh['grand_total'], 0, ',', '.') ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <?php if ($isLunas): ?>
                                                                <span class="badge badge-success px-2 py-1 shadow-sm" title="Faktur H telah lunas pada <?= !empty($fh['tanggal_bayar']) ? date('d/m/Y', strtotime($fh['tanggal_bayar'])) : '' ?>">
                                                                    <i class="fas fa-check-circle mr-1"></i> LUNAS
                                                                </span>
                                                                <span class="d-block text-muted small mt-1" style="font-size: 10px;">
                                                                    <?= !empty($fh['metode_pembayaran']) ? htmlspecialchars($fh['metode_pembayaran']) : '' ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary px-2 py-1" title="Faktur H belum lunas">
                                                                    <i class="fas fa-clock mr-1"></i> Belum Lunas
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <?php if (!empty($fh['nomor_jurnal_penjualan'])): ?>
                                                                <a href="javascript:void(0)" 
                                                                   class="badge badge-info text-white font-weight-bold shadow-sm d-block mb-1 btn-view-jurnal-penjualan-h" 
                                                                   data-id-pecah="<?= (int)$fh['id_pecah'] ?>"
                                                                   title="Klik untuk melihat Jurnal Penjualan (SJ)">
                                                                    <i class="fas fa-file-invoice mr-1"></i> <?= htmlspecialchars($fh['nomor_jurnal_penjualan']) ?>
                                                                </a>
                                                                <?php if (!empty($fh['nomor_jurnal_hpp'])): ?>
                                                                    <a href="javascript:void(0)" 
                                                                       class="badge badge-secondary font-weight-bold shadow-sm d-block btn-view-jurnal-penjualan-h" 
                                                                       data-id-pecah="<?= (int)$fh['id_pecah'] ?>"
                                                                       title="Klik untuk melihat Jurnal HPP & Persediaan">
                                                                        <i class="fas fa-boxes mr-1"></i> <?= htmlspecialchars($fh['nomor_jurnal_hpp']) ?>
                                                                    </a>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <a href="javascript:void(0)" 
                                                                   class="badge badge-outline-info text-info border font-weight-bold btn-view-jurnal-penjualan-h" 
                                                                   data-id-pecah="<?= (int)$fh['id_pecah'] ?>"
                                                                   title="Klik untuk membuat Jurnal Penjualan">
                                                                    <i class="fas fa-plus-circle mr-1"></i> Buat Jurnal
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <?php if ($isLunas && (!empty($fh['nomor_jurnal_q']) || !empty($fh['nomor_jurnal_a']))): ?>
                                                                <?php if (!empty($fh['nomor_jurnal_q'])): ?>
                                                                    <a href="javascript:void(0)" 
                                                                       class="badge badge-warning text-dark font-weight-bold shadow-sm d-block mb-1 btn-view-jurnal-pecah" 
                                                                       data-id-pecah="<?= (int)$fh['id_pecah'] ?>"
                                                                       title="Klik untuk melihat Jurnal Acak (Q)">
                                                                        <i class="fas fa-book mr-1"></i> <?= htmlspecialchars($fh['nomor_jurnal_q']) ?>
                                                                    </a>
                                                                <?php endif; ?>
                                                                <?php if (!empty($fh['nomor_jurnal_a'])): ?>
                                                                    <a href="javascript:void(0)" 
                                                                       class="badge badge-danger font-weight-bold shadow-sm d-block btn-view-jurnal-pecah" 
                                                                       data-id-pecah="<?= (int)$fh['id_pecah'] ?>"
                                                                       title="Klik untuk melihat Jurnal Kas Masuk Potongan (A)">
                                                                        <i class="fas fa-file-invoice mr-1"></i> <?= htmlspecialchars($fh['nomor_jurnal_a']) ?>
                                                                    </a>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="text-muted small">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="<?= base_url('sales_order/detail_faktur_pecah/' . $fh['id_pecah']) ?>"
                                                                   class="btn btn-default btn-sm border"
                                                                   title="Lihat Rincian Faktur Pecahan">
                                                                    <i class="fas fa-eye text-primary"></i>
                                                                </a>
                                                                <a href="<?= base_url('sales_order/cetak_faktur_pecah/' . $fh['id_pecah']) ?>"
                                                                   target="_blank"
                                                                   class="btn btn-outline-info btn-sm"
                                                                   title="Cetak Faktur (Printer LX-310)">
                                                                    <i class="fas fa-print"></i>
                                                                </a>
                                                                <?php if ($isLunas): ?>
                                                                    <a href="<?= base_url('sales_order/unpost_pelunasan_faktur_z_pecah/' . $fh['id_pecah']) ?>"
                                                                       class="btn btn-outline-warning btn-sm text-dark"
                                                                       title="Batalkan Pelunasan Faktur Pecahan & Hapus Jurnal Terkait"
                                                                       onclick="return confirm('Apakah Anda yakin ingin membatalkan (unpost) pelunasan Faktur Pecahan <?= htmlspecialchars($fh['no_faktur']) ?>?\n\nJurnal Pembayaran Acak (Q) dan Jurnal Kas Masuk (A) terkait akan otomatis dihapus dari pembukuan.')">
                                                                        <i class="fas fa-undo text-warning mr-1"></i> Unpost Bayar
                                                                    </a>
                                                                <?php else: ?>
                                                                    <button type="button"
                                                                            class="btn btn-success btn-sm font-weight-bold btn-open-lunasi-pecah-single"
                                                                            data-id="<?= (int)$fh['id_pecah'] ?>"
                                                                            data-no-faktur="<?= htmlspecialchars($fh['no_faktur']) ?>"
                                                                            data-parent-no="<?= htmlspecialchars($fh['parent_no_faktur'] ?? '') ?>"
                                                                            data-customer="<?= htmlspecialchars($fh['customer_name'] ?? '') ?>"
                                                                            data-tanggal="<?= htmlspecialchars(!empty($fh['tanggal_faktur']) ? date('Y-m-d', strtotime($fh['tanggal_faktur'])) : date('Y-m-d')) ?>"
                                                                            data-nominal="<?= (float)$fh['grand_total'] ?>"
                                                                            title="Lunasi Faktur Pecahan H ini & Buat Jurnal Q + A">
                                                                        <i class="fas fa-check mr-1"></i> Lunasi
                                                                    </button>
                                                                    <a href="<?= base_url('sales_order/hapus_faktur_pecah/' . $fh['id_pecah']) ?>"
                                                                       class="btn btn-outline-danger btn-sm"
                                                                       title="Unpost / Batalkan Faktur Pecahan ini"
                                                                       onclick="return confirm('Apakah Anda yakin ingin meng-unpost / membatalkan Faktur Pecahan <?= htmlspecialchars($fh['no_faktur']) ?>?\n\nKuantitas barang akan otomatis dikembalikan ke Faktur Z Induk (<?= htmlspecialchars($fh['parent_no_faktur']) ?>).')">
                                                                        <i class="fas fa-undo text-danger mr-1"></i> Unpost
                                                                    </a>
                                                                <?php endif; ?>
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

    
    <!-- Modal Pelunasan 1 Faktur Pecahan H (Single) -->
    <div class="modal fade" id="modalLunasiSingleFakturPecah" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content shadow-lg border-0">
                <form action="<?= base_url('sales_order/lunasi_faktur_z_pecah') ?>" method="post" id="formLunasiSingleFakturPecah">
                    <input type="hidden" name="id_pecah" id="singleLunasiPecahId">
                    <input type="hidden" name="redirect_to" value="sales_order/pecah_faktur#content-faktur-h">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-check-circle mr-1"></i> Lunasi Faktur Pecahan H: <span id="singleLunasiPecahNoFakturLabel"></span>
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2 small text-muted">
                            Customer: <strong class="text-dark" id="singleLunasiPecahCustomerLabel">-</strong>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold">Tanggal Pelunasan</label>
                            <input type="date" name="tanggal_pembayaran" id="singleLunasiPecahTanggal" class="form-control form-control-sm" required>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold">Nominal Pelunasan (Netto Faktur H)</label>
                            <input type="number" step="0.01" name="jumlah_bayar" id="singleLunasiPecahNominal" class="form-control form-control-sm font-weight-bold text-success" required>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold">Metode Pembayaran (Akun Kas/Bank Jurnal Q)</label>
                            <select name="metode_pembayaran" id="singleLunasiPecahMetode" class="form-control form-control-sm">
                                <option value="Q Mandiri" selected>Q Mandiri (12014)</option>
                                <option value="Q Kas Ayat Silang">Q Kas Ayat Silang</option>
                                <option value="Q Kas">Q Kas (11010)</option>
                                <option value="Q BCA">Q BCA</option>
                                <option value="Q BRI">Q BRI</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold">Skema Potongan (Untuk Jurnal A)</label>
                            <select name="skema_potongan" id="singleLunasiPecahSkema" class="form-control form-control-sm">
                                <option value="20" selected>Potongan 20% (Tax) — Netto 80%</option>
                                <option value="40">Potongan 40% (20% Tax + 20% Fee Petugas) — Netto 60%</option>
                                <option value="0">Tanpa Potongan (0%) — Hanya Jurnal Q</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold">No. Faktur Z Induk (Referensi Jurnal A)</label>
                            <input type="text" name="parent_no_faktur" id="singleLunasiPecahParentNo" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="alert alert-light border py-2 mb-0 small" id="singleLunasiPecahPreviewBox">
                            <!-- Preview perhitungan Bruto & Potongan -->
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm" id="btnSubmitLunasiSinglePecah">
                            <i class="fas fa-check mr-1"></i> Simpan Pelunasan & Buat Jurnal Q + A
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Pelunasan Batch Faktur Pecahan H (Terpilih via Checkbox) -->
    <div class="modal fade" id="modalLunasiBatchFakturPecah" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content shadow-lg border-0">
                <form action="<?= base_url('sales_order/lunasi_faktur_z_pecah_batch') ?>" method="post" id="formLunasiBatchFakturPecah">
                    <input type="hidden" name="ids" id="batchLunasiPecahIdsInput">
                    <input type="hidden" name="redirect_to" value="sales_order/pecah_faktur#content-faktur-h">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-check-double mr-1"></i> Lunasi Batch (<span id="batchLunasiPecahCountLabel">0</span> Faktur H Terpilih)
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small">
                            Seluruh Faktur Pecahan H yang dicentang akan dilunasi penuh (100% sesuai nominal tagihan masing-masing) dan otomatis dibuatkan <strong>Jurnal Pembayaran Acak (Q)</strong> serta <strong>Jurnal Kas Masuk (A)</strong>.
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold">Tanggal Pelunasan</label>
                            <input type="date" name="tanggal_pembayaran" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                            <small class="text-muted">Jika dikosongkan, otomatis mengikuti tanggal faktur masing-masing.</small>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold">Metode Pembayaran (Akun Kas/Bank Jurnal Q)</label>
                            <select name="metode_pembayaran" class="form-control form-control-sm">
                                <option value="Q Mandiri" selected>Q Mandiri (12014)</option>
                                <option value="Q Kas Ayat Silang">Q Kas Ayat Silang</option>
                                <option value="Q Kas">Q Kas (11010)</option>
                                <option value="Q BCA">Q BCA</option>
                                <option value="Q BRI">Q BRI</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Skema Potongan (Untuk Jurnal A)</label>
                            <select name="skema_potongan" class="form-control form-control-sm">
                                <option value="20" selected>Potongan 20% (Tax) — Netto 80%</option>
                                <option value="40">Potongan 40% (20% Tax + 20% Fee Petugas) — Netto 60%</option>
                                <option value="0">Tanpa Potongan (0%) — Hanya Jurnal Q</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm" id="btnSubmitLunasiBatchPecah">
                            <i class="fas fa-check-double mr-1"></i> Proses Pelunasan Batch & Buat Jurnal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import Pelunasan Faktur Pecahan H (Sistem) -->
    <div class="modal fade" id="modalImportPelunasanFakturZ" tabindex="-1" role="dialog" aria-labelledby="modalImportPelunasanFakturZLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content shadow-lg border-0">
                <form action="<?= base_url('sales_order/import_pelunasan_faktur_z_pecah') ?>" method="post" enctype="multipart/form-data" id="formImportPelunasanFakturZ">
                    <input type="hidden" name="redirect_to" value="sales_order/pecah_faktur#content-faktur-h">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title font-weight-bold" id="modalImportPelunasanFakturZLabel">
                            <i class="fas fa-check-double mr-1"></i> Import Pelunasan Berdasarkan Faktur Z (Otomatis Alokasi ke Faktur H & Jurnal Q/A)
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <strong>Cara Kerja:</strong> Pelunasan diinput per <strong>Nomor Faktur Z (Induk)</strong>. Nominal yang dibayarkan akan dialokasikan ke Faktur Pecahan H di bawah Faktur Z tersebut secara berurutan.<br>
                            <span class="text-muted">Contoh: Faktur Z senilai Rp 2.000.000 dipecah menjadi 2 Faktur H (@ Rp 1.000.000). Jika Faktur Z dibayar Rp 1.000.000, maka sistem melunasi dan membuat Jurnal Q &amp; A hanya untuk <strong>1 Faktur H</strong>.</span>
                        </div>
                        <div class="form-group mb-0">
                            <label for="filePelunasanZ" class="font-weight-bold text-dark">Pilih File Pelunasan Faktur Z (.xlsx / .xls / .csv)</label>
                            <div class="custom-file">
                                <input type="file" name="file_pelunasan_h" id="filePelunasanZ" class="custom-file-input" accept=".xlsx,.xls,.csv" required>
                                <label class="custom-file-label" for="filePelunasanZ" id="labelFilePelunasanZ">Pilih file Excel / CSV Pelunasan Faktur Z...</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light d-flex justify-content-between">
                        <div>
                            <a href="<?= base_url('sales_order/template_import_pelunasan_faktur_z_pecah?mode=unpaid') ?>" class="btn btn-outline-success btn-sm font-weight-bold shadow-sm">
                                <i class="fas fa-download mr-1"></i> Unduh Template Faktur Z Belum Lunas (.xlsx)
                            </a>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm" id="btnSubmitImportPelunasanZ">
                                <i class="fas fa-check-double mr-1"></i> Proses Import Pelunasan & Buat Jurnal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Jurnal Pelunasan Faktur Pecahan H (Sistem) -->
    <div class="modal fade" id="modalDetailJurnalPelunasanH" tabindex="-1" role="dialog" aria-labelledby="modalDetailJurnalPelunasanHLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="modalDetailJurnalPelunasanHLabel">
                        <i class="fas fa-book mr-2" style="color: #6f42c1;"></i> Jurnal Pelunasan Faktur Pecahan: <span id="jurnalPecahNoFaktur" class="badge badge-warning text-dark font-weight-bold ml-1"></span>
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-3" id="bodyDetailJurnalPelunasanH">
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin fa-2x mb-2 text-purple"></i><br>
                        Memuat rincian jurnal...
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Jurnal Penjualan Faktur Pecahan H (Sistem) -->
    <div class="modal fade" id="modalDetailJurnalPenjualanH" tabindex="-1" role="dialog" aria-labelledby="modalDetailJurnalPenjualanHLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="modalDetailJurnalPenjualanHLabel">
                        <i class="fas fa-file-invoice text-info mr-2"></i> Jurnal Penjualan Resmi Faktur Pecahan: <span id="jurnalPenjualanPecahNoFaktur" class="badge badge-info text-white font-weight-bold ml-1"></span>
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-3" id="bodyDetailJurnalPenjualanH">
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin fa-2x mb-2 text-info"></i><br>
                        Memuat rincian jurnal penjualan...
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi & Penentuan Jumlah Faktur Pecahan -->
    <div class="modal fade" id="modalBatchSplit" tabindex="-1" role="dialog" aria-labelledby="modalBatchSplitLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold" id="modalBatchSplitLabel">
                        <i class="fas fa-cut mr-1"></i> Pemecahan Faktur Z
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?= base_url('sales_order/split_faktur_batch') ?>" method="post" id="formModalBatchSplit">
                    <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap">
                                <small class="text-muted">Faktur Z yang akan dipecah:</small>
                                <div>
                                    <span class="badge badge-info font-weight-bold mr-1" id="modalTotalQtyBadge" style="font-size: 12px;">Total Qty: 0 pcs</span>
                                    <span class="badge badge-primary font-weight-bold" id="modalTotalNilaiBadge" style="font-size: 12px;">Netto: Rp 0</span>
                                </div>
                            </div>
                            <div id="modalListSelectedFaktur" class="font-weight-bold text-primary" style="max-height: 70px; overflow-y: auto;">
                                -
                            </div>
                        </div>

                        <!-- Card Rincian Nominal Faktur Awal & Potongan 20% -->
                        <div class="card card-outline card-warning shadow-none border mb-3">
                            <div class="card-header py-1 px-3 bg-light">
                                <h6 class="card-title font-weight-bold text-dark mb-0" style="font-size: 12.5px;">
                                    <i class="fas fa-calculator text-warning mr-1"></i> Rincian Nilai Faktur Z & Potongan 20%:
                                </h6>
                            </div>
                            <div class="card-body p-2 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                                    <span class="small text-muted font-weight-bold">
                                        <i class="fas fa-file-invoice-dollar mr-1 text-secondary"></i> Nominal Faktur Awal (Sebelum Potongan):
                                    </span>
                                    <span class="font-weight-bold text-dark" id="modalNominalAwal" style="font-size: 13.5px;">
                                        Rp 0
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                                    <span class="small text-danger font-weight-bold">
                                        <i class="fas fa-tag mr-1 text-danger"></i> Potongan / Diskon Faktur Z (20%):
                                    </span>
                                    <span class="font-weight-bold text-danger" id="modalNominalPotongan" style="font-size: 13px;">
                                        - Rp 0
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-1">
                                    <span class="small text-success font-weight-bold">
                                        <i class="fas fa-check-circle mr-1 text-success"></i> Nominal Faktur Setelah Potongan (Netto):
                                    </span>
                                    <strong class="font-weight-bold text-success" id="modalNominalNetto" style="font-size: 15px;">
                                        Rp 0
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <!-- Skema Potongan Harga -->
                        <div class="card border-0 mb-3" style="background-color: #fff9e6; border-left: 4px solid #ffc107 !important;">
                            <div class="card-body p-2">
                                <label class="font-weight-bold text-dark d-block mb-1 small">
                                    <i class="fas fa-percentage text-warning mr-1"></i> Skema Potongan Harga:
                                </label>
                                <div class="custom-control custom-radio mb-1">
                                    <input type="radio" id="listPotongan20" name="pakai_potongan_20" class="custom-control-input radio-potongan-list" value="20" checked>
                                    <label class="custom-control-label font-weight-bold text-dark small" for="listPotongan20" style="cursor:pointer;">
                                        Pakai Potongan 20% <span class="badge badge-success ml-1">Standar Netto (80%)</span>
                                    </label>
                                </div>
                                <div class="custom-control custom-radio mb-1">
                                    <input type="radio" id="listPotongan40" name="pakai_potongan_20" class="custom-control-input radio-potongan-list" value="40">
                                    <label class="custom-control-label font-weight-bold text-dark small" for="listPotongan40" style="cursor:pointer;">
                                        Pakai Potongan 40% <span class="badge badge-warning text-dark ml-1">Netto (60%) - Tax & Fee</span>
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="listPotongan0" name="pakai_potongan_20" class="custom-control-input radio-potongan-list" value="0">
                                    <label class="custom-control-label font-weight-bold text-dark small" for="listPotongan0" style="cursor:pointer;">
                                        Tanpa Potongan (100% Penuh) <span class="badge badge-secondary ml-1">Harga Utuh</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Parameter Auto Split & Plafon Maksimal Rp 25 Juta -->
                        <input type="hidden" name="auto_split" value="1">
                        <input type="hidden" name="max_nominal" id="inputMaxNominal" value="25000000">

                        <div class="card border-0 mb-3" style="background-color: #f0f7ff; border-left: 4px solid #007bff !important;">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small font-weight-bold text-uppercase" style="font-size: 11px;">
                                            <i class="fas fa-shield-alt text-primary mr-1"></i> Maksimal Nilai per Faktur Pecahan (Kode H)
                                        </div>
                                        <div class="font-weight-bold text-primary mt-1" style="font-size: 15px;">
                                            Rp 25.000.000 <span class="badge badge-primary font-weight-normal ml-1" style="font-size: 10px;">Tetap / Otomatis</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-light border text-muted px-2 py-1 small">Plafon Maksimal</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rangkuman Aturan Sistem -->
                        <div class="alert alert-light border small text-dark mb-0 py-2 px-3">
                            <h6 class="font-weight-bold mb-1 text-primary" style="font-size: 12px;">
                                <i class="fas fa-info-circle mr-1"></i> Alur Pemeriksaan Sebelum Diterbitkan:
                            </h6>
                            <ul class="pl-3 mb-0" style="line-height: 1.5; font-size: 11.5px;">
                                <li>Data membuka halaman <strong>Pemeriksaan Batch Split</strong> terlebih dahulu.</li>
                                <li><strong>Isolasi Terjaga:</strong> Barang dipecah per faktur induk (tidak tercampur).</li>
                                <li><strong>Batas Rp 25 Juta:</strong>
                                    Faktur Z &le; Rp 25 Juta otomatis menjadi <strong>1 Faktur H</strong>.
                                    Faktur Z &gt; Rp 25 Juta otomatis dipecah proporsional (&le; Rp 25 Juta).
                                </li>
                                <li><strong>Customer Acak:</strong> Kontak penerima otomatis dialokasikan dari master customer acak.</li>
                            </ul>
                        </div>

                        <div id="modalHiddenFakturInputs">
                            <!-- Dynamic hidden inputs id_faktur[] -->
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold px-3 shadow-sm">
                            <i class="fas fa-arrow-right mr-1"></i> Lanjut Pemecahan Faktur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalImportFakturZ" tabindex="-1" role="dialog" aria-labelledby="modalImportFakturZLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="<?= base_url('sales_order/import_faktur_z') ?>" method="post" enctype="multipart/form-data" id="formImportFakturZ">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalImportFakturZLabel">
                            <i class="fas fa-file-import mr-1"></i> Import Faktur Z Eksternal
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label for="fileImportFakturZ" class="font-weight-bold">File Excel (.xlsx atau .xls)</label>
                            <div class="custom-file">
                                <input type="file" name="file_faktur_z" id="fileImportFakturZ" class="custom-file-input" accept=".xlsx,.xls" required>
                                <label class="custom-file-label" for="fileImportFakturZ">Pilih file template yang telah diisi</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= base_url('sales_order/template_import_faktur_z') ?>" class="btn btn-outline-success btn-sm mr-auto">
                            <i class="fas fa-download mr-1"></i> Unduh Template
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm" id="btnImportFakturZ">
                            <i class="fas fa-upload mr-1"></i> Proses Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalImportMasterBarang" tabindex="-1" role="dialog" aria-labelledby="modalImportMasterBarangLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="<?= base_url('sales_order/import_master_barang') ?>" method="post" enctype="multipart/form-data" id="formImportMasterBarang">
                    <input type="hidden" name="redirect_to" value="sales_order/pecah_faktur">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalImportMasterBarangLabel">
                            <i class="fas fa-boxes mr-1"></i> Import Data Master Barang
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label for="fileImportMasterBarang" class="font-weight-bold">File Excel / CSV (.xlsx, .xls, atau .csv)</label>
                            <div class="custom-file">
                                <input type="file" name="file_master_barang" id="fileImportMasterBarang" class="custom-file-input" accept=".xlsx,.xls,.csv" required>
                                <label class="custom-file-label" for="fileImportMasterBarang">Pilih file template Master Barang yang telah diisi</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= base_url('sales_order/template_import_master_barang') ?>" class="btn btn-outline-primary btn-sm mr-auto">
                            <i class="fas fa-download mr-1"></i> Unduh Template Master Barang
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnImportMasterBarang">
                            <i class="fas fa-upload mr-1"></i> Proses Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>

<script>
function formatRupiahJs(num) {
    return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

var fileImportFakturZ = document.getElementById('fileImportFakturZ');
if (fileImportFakturZ) {
    fileImportFakturZ.addEventListener('change', function() {
        var fileName = this.files && this.files[0] ? this.files[0].name : 'Pilih file template yang telah diisi';
        var label = this.parentNode.querySelector('.custom-file-label');
        if (label) label.textContent = fileName;
    });
}

var formImportFakturZ = document.getElementById('formImportFakturZ');
if (formImportFakturZ) {
    formImportFakturZ.addEventListener('submit', function() {
        var button = document.getElementById('btnImportFakturZ');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
        }
    });
}

var fileImportMasterBarang = document.getElementById('fileImportMasterBarang');
if (fileImportMasterBarang) {
    fileImportMasterBarang.addEventListener('change', function() {
        var fileName = this.files && this.files[0] ? this.files[0].name : 'Pilih file template Master Barang yang telah diisi';
        var label = this.parentNode.querySelector('.custom-file-label');
        if (label) label.textContent = fileName;
    });
}

var formImportMasterBarang = document.getElementById('formImportMasterBarang');
if (formImportMasterBarang) {
    formImportMasterBarang.addEventListener('submit', function(e) {
        if (!window.fetch || !window.FormData) {
            var btnFallback = document.getElementById('btnImportMasterBarang');
            if (btnFallback) {
                btnFallback.disabled = true;
                btnFallback.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
            }
            return;
        }
        e.preventDefault();
        var button = document.getElementById('btnImportMasterBarang');
        var originalHtml = button ? button.innerHTML : '<i class="fas fa-upload mr-1"></i> Proses Import';
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Import...';
        }

        var formData = new FormData(formImportMasterBarang);
        formData.append('ajax', '1');

        fetch(formImportMasterBarang.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(resp) {
            if (button) {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
            if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
                window.jQuery('#modalImportMasterBarang').modal('hide');
            }
            var isOk = resp && resp.status;
            var title = isOk ? 'Import Master Barang Berhasil' : 'Import Master Barang Gagal';
            var htmlMsg = (resp && (resp.html_message || resp.message)) || 'Proses import selesai.';
            var plainMsg = (resp && resp.message) || 'Proses import selesai.';

            // Tampilkan banner alert di bagian atas halaman
            var container = document.querySelector('section.content .container-fluid');
            if (container) {
                var alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-' + (isOk ? 'success' : 'danger') + ' alert-dismissible fade show shadow-sm';
                alertDiv.innerHTML = '<i class="fas fa-' + (isOk ? 'check-circle' : 'exclamation-circle') + ' mr-1"></i> ' +
                    htmlMsg +
                    '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
                container.insertBefore(alertDiv, container.firstChild);
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: isOk ? 'success' : 'error',
                    title: title,
                    html: htmlMsg,
                    confirmButtonText: 'OK'
                });
            } else {
                alert(title + '\n\n' + plainMsg);
            }
        })
        .catch(function(err) {
            if (button) {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
            var errMsg = 'Terjadi kesalahan saat mengunggah atau memproses file Master Barang.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Import Master Barang Gagal',
                    text: errMsg
                });
            } else {
                alert('Import Master Barang Gagal: ' + errMsg);
            }
        });
    });
}

// Variabel penampung total nilai & qty saat modal dibuka
window.currentModalTotalNilai       = 0;
window.currentModalTotalNominalAwal = 0;
window.currentModalTotalQty         = 0;

function getListSkemaPotonganVal() {
    var r40 = document.getElementById("listPotongan40");
    if (r40 && r40.checked) return 40;
    var r0 = document.getElementById("listPotongan0");
    if (r0 && r0.checked) return 0;
    return 20;
}

function recalculateListModalValues() {
    var skema = getListSkemaPotonganVal();
    var nominalAwal = window.currentModalTotalNominalAwal || 0;
    var factor = (skema === 40) ? 0.6 : ((skema === 0) ? 1.0 : 0.8);
    var totalNilai = Math.round(nominalAwal * factor);
    var potongan = nominalAwal - totalNilai;

    window.currentModalTotalNilai = totalNilai;

    var totalBadgeEl = document.getElementById("modalTotalNilaiBadge");
    var nomPotonganEl = document.getElementById("modalNominalPotongan");
    var nomNettoEl = document.getElementById("modalNominalNetto");
    var recTextEl = document.getElementById("modalRecommendationText");

    if (totalBadgeEl) totalBadgeEl.innerText = "Netto: " + formatRupiahJs(totalNilai);
    if (nomNettoEl) nomNettoEl.innerText = formatRupiahJs(totalNilai);

    if (nomPotonganEl) {
        if (skema === 40) {
            nomPotonganEl.innerText = "- " + formatRupiahJs(potongan) + " (40% - Tax & Fee)";
            nomPotonganEl.className = "font-weight-bold text-danger";
        } else if (skema === 20) {
            nomPotonganEl.innerText = "- " + formatRupiahJs(potongan) + " (20%)";
            nomPotonganEl.className = "font-weight-bold text-danger";
        } else {
            nomPotonganEl.innerText = "Rp 0 (0% - Tanpa Potongan)";
            nomPotonganEl.className = "font-weight-bold text-muted";
        }
    }

    if (recTextEl) {
        var labelPot = (skema === 40) ? "potongan 40% (Tax & Fee)" : ((skema === 20) ? "potongan 20%" : "tanpa potongan");
        recTextEl.innerHTML = "Faktur awal senilai <strong>" + formatRupiahJs(nominalAwal) + "</strong> menggunakan " + labelPot + " (-" + formatRupiahJs(potongan) + ") menjadi <strong>" + formatRupiahJs(totalNilai) + "</strong>. Kuantitas total <strong>" + (window.currentModalTotalQty || 0).toLocaleString("id-ID") + " pcs</strong> akan didistribusikan ke faktur pecahan (Kode Awalan H).";
    }

    var inputPecah = document.getElementById("inputJumlahPecah");
    if (inputPecah) {
        var defaultPecah = Math.max(1, Math.ceil(totalNilai / 25000000));
        inputPecah.value = defaultPecah;
    }

    updateModalEstimasi();
}

function updateModalEstimasi() {
    var inputEl = document.getElementById('inputJumlahPecah');
    var estQtyEl = document.getElementById('modalEstimasiQty');
    var estNilaiEl = document.getElementById('modalEstimasiNilai');
    var num = parseInt(inputEl ? inputEl.value : 1) || 1;
    if (num < 1) num = 1;

    var estQty = window.currentModalTotalQty / num;
    var estNilai = window.currentModalTotalNilai / num;

    var formattedQty = (estQty % 1 === 0) ? estQty.toLocaleString('id-ID') : estQty.toFixed(2);
    if (estQtyEl) estQtyEl.innerText = formattedQty + ' pcs / faktur';
    if (estNilaiEl) estNilaiEl.innerText = formatRupiahJs(estNilai) + ' / faktur';
}

// Fungsi Menutup Modal Pemecahan Faktur
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
    var bd = document.getElementById('customBackdropBatch');
    if (bd) bd.remove();
    var defaultBds = document.querySelectorAll('.modal-backdrop');
    for (var j = 0; j < defaultBds.length; j++) {
        defaultBds[j].remove();
    }
};

// Fungsi Membuka Modal Pemecahan Faktur Z dari tombol Aksi "Pecah"
window.openSplitModalSingle = function(fid, noFaktur, totalNilai, nominalAwal, totalQty) {
    nominalAwal = parseFloat(nominalAwal) || 0;
    totalNilai = parseFloat(totalNilai) || (nominalAwal * 0.8);
    totalQty = parseFloat(totalQty) || 0;
    var potongan = Math.max(0, nominalAwal - totalNilai);

    window.currentModalTotalNilai       = totalNilai;
    window.currentModalTotalNominalAwal = nominalAwal;
    window.currentModalTotalQty         = totalQty;

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

    var totalBadgeEl = document.getElementById('modalTotalNilaiBadge');
    var totalQtyBadgeEl = document.getElementById('modalTotalQtyBadge');
    var headerQtyBadgeEl = document.getElementById('modalHeaderQtyBadge');
    var recTextEl = document.getElementById('modalRecommendationText');
    var inputPecah = document.getElementById('inputJumlahPecah');

    var nomAwalEl = document.getElementById('modalNominalAwal');
    var nomPotonganEl = document.getElementById('modalNominalPotongan');
    var nomNettoEl = document.getElementById('modalNominalNetto');

    if (totalBadgeEl) totalBadgeEl.innerText = 'Netto: ' + formatRupiahJs(totalNilai);
    if (totalQtyBadgeEl) totalQtyBadgeEl.innerText = 'Total Qty: ' + totalQty.toLocaleString('id-ID') + ' pcs';
    if (headerQtyBadgeEl) headerQtyBadgeEl.innerText = totalQty.toLocaleString('id-ID') + ' pcs';

    if (nomAwalEl) nomAwalEl.innerText = formatRupiahJs(nominalAwal);
    if (nomPotonganEl) nomPotonganEl.innerText = '- ' + formatRupiahJs(potongan) + ' (20%)';
    if (nomNettoEl) nomNettoEl.innerText = formatRupiahJs(totalNilai);

    if (recTextEl) {
        recTextEl.innerHTML = 'Faktur awal senilai <strong>' + formatRupiahJs(nominalAwal) + '</strong> mendapatkan potongan 20% (-' + formatRupiahJs(potongan) + ') menjadi <strong>' + formatRupiahJs(totalNilai) + '</strong>. Kuantitas total <strong>' + totalQty.toLocaleString('id-ID') + ' pcs</strong> akan didistribusikan ke faktur pecahan (Kode Awalan H).';
    }

    if (inputPecah) {
        var defaultPecah = Math.max(1, Math.ceil(totalNilai / 25000000));
        inputPecah.min = 1;
        inputPecah.value = defaultPecah;
        if (!inputPecah.hasAttribute('data-has-calc-listener')) {
            inputPecah.setAttribute('data-has-calc-listener', '1');
            inputPecah.addEventListener('input', updateModalEstimasi);
        }
    }

    updateModalEstimasi();

    // Tampilkan modal
    if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
        window.jQuery('#modalBatchSplit').modal('show');
    } else {
        var modal = document.getElementById('modalBatchSplit');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
            var existingBd = document.getElementById('customBackdropBatch');
            if (!existingBd) {
                var backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'customBackdropBatch';
                document.body.appendChild(backdrop);
            }
        }
    }
};

// Event listener klik tombol Pecah di kolom aksi (menggunakan delegasi dokumen agar tetap aktif setelah paging/filter DataTables)
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

    if (e.target.closest && e.target.closest('[data-dismiss="modal"]')) {
        window.closeBatchSplitModal();
    }
});

// Setup DataTables secara aman
// Listener radio potongan pada modal daftar faktur
document.addEventListener("DOMContentLoaded", function() {
    var radios = document.querySelectorAll(".radio-potongan-list");
    radios.forEach(function(r) {
        r.addEventListener("change", function() {
            recalculateListModalValues();
        });
    });
});

function setupDataTablesFaktur() {
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
        var $ = window.jQuery;
        var dataTableConfig = {
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": false,
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, "Semua"]],
            "columnDefs": [
                { "orderable": false, "targets": "no-sort" }
            ],
            "language": {
                "search": "Cari kios / data:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "emptyTable": "Belum ada data yang tersedia",
                "zeroRecords": "Tidak ada data yang cocok",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Lanjut",
                    "previous": "Sebelum"
                }
            },
            "order": [[0, "asc"]]
        };

        // Bersihkan baris placeholder colspan agar DataTables tetap dapat menampilkan search & lengthMenu tanpa error saat data kosong
        var clearColspanPlaceholder = function(selector) {
            if ($(selector + ' tbody tr td[colspan]').length > 0) {
                $(selector + ' tbody').empty();
            }
        };

        if (!$.fn.DataTable.isDataTable('#tableKiosList')) {
            clearColspanPlaceholder('#tableKiosList');
            $('#tableKiosList').DataTable(dataTableConfig);
        }

        if (!$.fn.DataTable.isDataTable('#tableFakturZSudahPecah')) {
            clearColspanPlaceholder('#tableFakturZSudahPecah');
            $('#tableFakturZSudahPecah').DataTable(dataTableConfig);
        }

        if (!$.fn.DataTable.isDataTable('#tableFakturH')) {
            clearColspanPlaceholder('#tableFakturH');
            var dataTableConfigH = $.extend({}, dataTableConfig, {
                "order": [[1, "asc"]],
                "columnDefs": [
                    { "orderable": false, "targets": "no-sort" }
                ],
                "language": $.extend({}, dataTableConfig.language, {
                    "emptyTable": "Belum ada faktur pecahan (kode H) yang dibuat."
                })
            });
            $('#tableFakturH').DataTable(dataTableConfigH);
            // Sinkronkan checkbox dan tombol aksi batch saat DataTables berpindah halaman atau difilter
            $('#tableFakturH').on('draw.dt', function() {
                updateSelectedFakturHState();
            });
        }
    }
}

// Fungsi update status tombol aksi unpost terpilih & checkbox master
function updateSelectedFakturHState() {
    if (!window.jQuery) return;
    var $ = window.jQuery;
    var totalSelected = 0;
    var totalLunas = 0;
    var totalBelum = 0;

    var countCheckboxes = function($items) {
        $items.each(function() {
            totalSelected++;
            var st = $(this).attr('data-status-bayar');
            if (st === 'lunas') {
                totalLunas++;
            } else {
                totalBelum++;
            }
        });
    };

    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturH')) {
        var dt = $('#tableFakturH').DataTable();
        countCheckboxes(dt.$('.check-item-faktur-h:checked'));

        // Cek apakah seluruh baris di halaman aktif saat ini tercentang
        var currentPageCheckboxes = $(dt.rows({ page: 'current' }).nodes()).find('.check-item-faktur-h');
        var currentPageChecked = currentPageCheckboxes.filter(':checked');
        var allCurrentChecked = currentPageCheckboxes.length > 0 && currentPageCheckboxes.length === currentPageChecked.length;
        $('#checkAllFakturH').prop('checked', allCurrentChecked);
    } else {
        countCheckboxes($('.check-item-faktur-h:checked'));
        var totalCurrent = $('.check-item-faktur-h:visible').length;
        var checkedCurrent = $('.check-item-faktur-h:visible:checked').length;
        $('#checkAllFakturH').prop('checked', totalCurrent > 0 && totalCurrent === checkedCurrent);
    }

    if (totalBelum > 0) {
        $('#countSelectedFakturH').text(totalBelum);
        $('#btnUnpostBatchFakturH').fadeIn(150);
        $('#countSelectedUnpaidFakturH').text(totalBelum);
        $('#btnLunasiBatchFakturH').fadeIn(150);
    } else {
        $('#countSelectedFakturH').text(0);
        $('#btnUnpostBatchFakturH').fadeOut(150);
        $('#countSelectedUnpaidFakturH').text(0);
        $('#btnLunasiBatchFakturH').fadeOut(150);
    }

    if (totalLunas > 0) {
        $('#countSelectedLunasFakturH').text(totalLunas);
        $('#btnUnpostPelunasanBatchFakturH').fadeIn(150);
    } else {
        $('#countSelectedLunasFakturH').text(0);
        $('#btnUnpostPelunasanBatchFakturH').fadeOut(150);
    }
}

// Fungsi mengambil ID Faktur Pecahan H yang sedang tampil di halaman DataTables saat ini
function getVisibleFakturHIds() {
    var ids = [];
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable && window.jQuery.fn.DataTable.isDataTable('#tableFakturH')) {
        var dt = window.jQuery('#tableFakturH').DataTable();
        // Mengambil DOM nodes dari baris yang berada pada halaman aktif / sedang ditampilkan saat ini
        var rows = dt.rows({ page: 'current' }).nodes();
        for (var i = 0; i < rows.length; i++) {
            var id = window.jQuery(rows[i]).attr('data-id-pecah');
            if (id) {
                ids.push(id);
            }
        }
    } else {
        // Fallback jika DataTable belum terpasang
        window.jQuery('#tableFakturH tbody tr:visible').each(function() {
            var id = window.jQuery(this).attr('data-id-pecah');
            if (id) {
                ids.push(id);
            }
        });
    }
    return ids;
}

// Event handler Export Excel Faktur Pecahan H sesuai baris yang tampil di layar
document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery) {
        var $ = window.jQuery;

        // Auto-switch tab berdasarkan hash URL (misal: #content-faktur-h)
        if (window.location.hash) {
            var targetTab = $('.nav-tabs a[href="' + window.location.hash + '"]');
            if (targetTab.length) {
                targetTab.tab('show');
            }
        }
        $('.nav-tabs a[data-toggle="pill"], .nav-tabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, null, e.target.getAttribute('href'));
            }
            if ($.fn.DataTable) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            }
        });

        // Handler klik tombol Export Excel Faktur Pecahan H
        
        // =========================================================================
        // JAVASCRIPT LOGIC PELUNASAN FAKTUR PECAHAN H (SISTEM)
        // =========================================================================

        // Preview perhitungan Bruto & Potongan di Modal Single Pelunasan Faktur Pecahan H
        function updateSingleLunasiPecahPreview() {
            var netto = parseFloat($('#singleLunasiPecahNominal').val() || 0);
            var skema = parseInt($('#singleLunasiPecahSkema').val() || 20, 10);
            var pembagi = (skema === 40) ? 0.6 : ((skema === 20) ? 0.8 : 1.0);
            var bruto = (pembagi > 0 && pembagi < 1.0) ? Math.round(netto / pembagi) : netto;
            var potongan = Math.max(0, bruto - netto);

            var html = '<strong>Simulasi Jurnal yang Akan Dibuat:</strong><br>' +
                '&bull; <strong>Jurnal Acak (Q):</strong> Debit Kas/Bank = <strong>Rp ' + bruto.toLocaleString('id-ID') + '</strong> | Kredit Piutang (13099) = <strong>Rp ' + netto.toLocaleString('id-ID') + '</strong>';
            if (potongan > 0) {
                html += ' | Kredit Q Kas (11010) = <strong>Rp ' + potongan.toLocaleString('id-ID') + '</strong><br>' +
                    '&bull; <strong>Jurnal Kas Masuk (A):</strong> Debit A Kas (11030) = <strong>Rp ' + potongan.toLocaleString('id-ID') + '</strong> | Kredit PND Pot Penj KIU = <strong>Rp ' + potongan.toLocaleString('id-ID') + '</strong>';
            } else {
                html += '<br>&bull; <strong>Jurnal Kas Masuk (A):</strong> <em>Tidak dibuat (Skema Tanpa Potongan 0%)</em>';
            }
            $('#singleLunasiPecahPreviewBox').html(html);
        }

        $('#singleLunasiPecahNominal, #singleLunasiPecahSkema').on('input change', updateSingleLunasiPecahPreview);

        // Buka Modal Pelunasan 1 Faktur Pecahan H
        $(document).on('click', '.btn-open-lunasi-pecah-single', function() {
            var btn = $(this);
            $('#singleLunasiPecahId').val(btn.data('id'));
            $('#singleLunasiPecahNoFakturLabel').text(btn.data('no-faktur'));
            $('#singleLunasiPecahCustomerLabel').text(btn.data('customer'));
            $('#singleLunasiPecahTanggal').val(btn.data('tanggal') || '<?= date("Y-m-d") ?>');
            $('#singleLunasiPecahNominal').val(btn.data('nominal'));
            $('#singleLunasiPecahParentNo').val(btn.data('parent-no') || '');
            updateSingleLunasiPecahPreview();
            $('#modalLunasiSingleFakturPecah').modal('show');
        });

        // Buka Modal Pelunasan Batch Faktur Pecahan H
        $(document).on('click', '#btnLunasiBatchFakturH', function(e) {
            e.preventDefault();
            var selectedIds = [];
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturH')) {
                var dt = $('#tableFakturH').DataTable();
                dt.$('.check-item-faktur-h:checked').each(function() {
                    if ($(this).attr('data-status-bayar') !== 'lunas') {
                        selectedIds.push($(this).val());
                    }
                });
            } else {
                $('.check-item-faktur-h:checked').each(function() {
                    if ($(this).attr('data-status-bayar') !== 'lunas') {
                        selectedIds.push($(this).val());
                    }
                });
            }

            if (selectedIds.length === 0) {
                alert('Pilih minimal 1 Faktur Pecahan H yang belum lunas untuk diproses pelunasan.');
                return;
            }

            $('#batchLunasiPecahIdsInput').val(selectedIds.join(','));
            $('#batchLunasiPecahCountLabel').text(selectedIds.length);
            $('#modalLunasiBatchFakturPecah').modal('show');
        });

        $('#formLunasiSingleFakturPecah').on('submit', function() {
            $('#btnSubmitLunasiSinglePecah').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan Pelunasan...');
        });

        $('#formLunasiBatchFakturPecah').on('submit', function() {
            $('#btnSubmitLunasiBatchPecah').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Pelunasan Batch...');
        });

        $('#formImportPelunasanFakturZ').on('submit', function() {
            $('#btnSubmitImportPelunasanZ').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Import Pelunasan & Buat Jurnal...');
        });

        $(document).on('click', '#btnExportFakturH', function(e) {
            e.preventDefault();
            var ids = getVisibleFakturHIds();

            if (ids.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Tidak Ditemukan',
                        text: 'Tidak ada data Faktur Pecahan Kode H yang sedang ditampilkan pada tabel saat ini untuk diexport.'
                    });
                } else {
                    alert('Tidak ada data Faktur Pecahan Kode H yang sedang ditampilkan pada tabel saat ini untuk diexport.');
                }
                return false;
            }

            var $btn = $(this);
            var oriHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengekspor ' + ids.length + ' faktur...');

            var form = $('<form>', {
                'action': '<?= base_url("sales_order/export_faktur_pecah") ?>',
                'method': 'POST',
                'style': 'display:none;'
            });

            form.append($('<input>', {
                'type': 'hidden',
                'name': 'id_pecah_list',
                'value': ids.join(',')
            }));

            $('body').append(form);
            form.submit();

            setTimeout(function() {
                form.remove();
                $btn.prop('disabled', false).html(oriHtml);
            }, 2500);
        });

        // Handler klik tombol Cetak Semua Faktur H (LX-310) sesuai baris yang tampil
        $(document).on('click', '#btnCetakSemuaFakturH', function(e) {
            var ids = getVisibleFakturHIds();
            if (ids.length > 0) {
                e.preventDefault();
                var form = $('<form>', {
                    'action': '<?= base_url("sales_order/cetak_semua_faktur_pecah") ?>',
                    'method': 'POST',
                    'target': '_blank',
                    'style': 'display:none;'
                });

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'id_pecah',
                    'value': ids.join(',')
                }));

                $('body').append(form);
                form.submit();
                setTimeout(function() {
                    form.remove();
                }, 2000);
            }
        });

        // Checkbox Master: Centang / Batalkan semua pada halaman saat ini
        $(document).on('change', '#checkAllFakturH', function() {
            var isChecked = $(this).is(':checked');
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturH')) {
                var dt = $('#tableFakturH').DataTable();
                var currentNodes = dt.rows({ page: 'current' }).nodes();
                $(currentNodes).find('.check-item-faktur-h').prop('checked', isChecked);
            } else {
                $('.check-item-faktur-h:visible').prop('checked', isChecked);
            }
            updateSelectedFakturHState();
        });

        // Tombol Toggle "Pilih Semua" pada halaman saat ini
        $(document).on('click', '#btnToggleSelectAllFakturH', function(e) {
            e.preventDefault();
            var dt = $.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturH') ? $('#tableFakturH').DataTable() : null;
            var currentCheckboxes = dt ? $(dt.rows({ page: 'current' }).nodes()).find('.check-item-faktur-h') : $('.check-item-faktur-h:visible');
            var isAllChecked = currentCheckboxes.length > 0 && currentCheckboxes.length === currentCheckboxes.filter(':checked').length;
            currentCheckboxes.prop('checked', !isAllChecked);
            updateSelectedFakturHState();
        });

        // Perubahan centang pada checkbox baris per faktur
        $(document).on('change', '.check-item-faktur-h', function() {
            updateSelectedFakturHState();
        });

        // Handler klik Unpost Batch (Beberapa / Seluruh faktur pecahan yang dipilih)
        $(document).on('click', '#btnUnpostBatchFakturH', function(e) {
            e.preventDefault();
            var selectedIds = [];
            var selectedNames = [];

            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturH')) {
                var dt = $('#tableFakturH').DataTable();
                dt.$('.check-item-faktur-h:checked').each(function() {
                    selectedIds.push($(this).val());
                    var name = $(this).attr('data-no-faktur');
                    if (name) selectedNames.push(name);
                });
            } else {
                $('.check-item-faktur-h:checked').each(function() {
                    selectedIds.push($(this).val());
                    var name = $(this).attr('data-no-faktur');
                    if (name) selectedNames.push(name);
                });
            }

            if (selectedIds.length === 0) {
                var noDataMsg = 'Pilih minimal 1 faktur pecahan yang ingin di-unpost.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Belum Ada Faktur Dipilih', text: noDataMsg });
                } else {
                    alert(noDataMsg);
                }
                return;
            }

            var confirmPrompt = function() {
                var $btn = $('#btnUnpostBatchFakturH');
                var oriHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Unpost...');

                var form = $('<form>', {
                    'action': '<?= base_url("sales_order/unpost_faktur_pecah_batch") ?>',
                    'method': 'POST',
                    'style': 'display:none;'
                });

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'id_pecah_list',
                    'value': selectedIds.join(',')
                }));

                $('body').append(form);
                form.submit();
            };

            var confirmText = 'Apakah Anda yakin ingin meng-unpost / membatalkan ' + selectedIds.length + ' faktur pecahan yang dipilih?\n\nKuantitas barang akan otomatis dikembalikan ke Faktur Z Induk terkait.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Unpost ' + selectedIds.length + ' Faktur Pecahan?',
                    text: 'Kuantitas barang dari seluruh faktur pecahan yang dipilih akan otomatis dikembalikan ke Faktur Z Induk masing-masing.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-undo mr-1"></i> Ya, Unpost Sekarang',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        confirmPrompt();
                    }
                });
            } else {
                if (confirm(confirmText)) {
                    confirmPrompt();
                }
            }
        });

        // Custom file input label untuk Import Pelunasan Faktur Pecahan H
        $(document).on('change', '#filePelunasanZ', function() {
            var fileName = $(this).val().split('\\').pop();
            $('#labelFilePelunasanZ').text(fileName || 'Pilih file pelunasan...');
        });

        // Handler klik Unpost Pelunasan Batch Faktur Pecahan H
        $(document).on('click', '#btnUnpostPelunasanBatchFakturH', function(e) {
            e.preventDefault();
            var selectedIds = [];
            var selectedNames = [];

            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturH')) {
                var dt = $('#tableFakturH').DataTable();
                dt.$('.check-item-faktur-h:checked').each(function() {
                    if ($(this).attr('data-status-bayar') === 'lunas') {
                        selectedIds.push($(this).val());
                        var name = $(this).attr('data-no-faktur');
                        if (name) selectedNames.push(name);
                    }
                });
            } else {
                $('.check-item-faktur-h:checked').each(function() {
                    if ($(this).attr('data-status-bayar') === 'lunas') {
                        selectedIds.push($(this).val());
                        var name = $(this).attr('data-no-faktur');
                        if (name) selectedNames.push(name);
                    }
                });
            }

            if (selectedIds.length === 0) {
                var noDataMsg = 'Pilih minimal 1 faktur pecahan yang berstatus Lunas untuk dibatalkan pelunasannya.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Belum Ada Faktur Lunas Dipilih', text: noDataMsg });
                } else {
                    alert(noDataMsg);
                }
                return;
            }

            var confirmPrompt = function() {
                var $btn = $('#btnUnpostPelunasanBatchFakturH');
                var oriHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Unpost Pelunasan...');

                var form = $('<form>', {
                    'action': '<?= base_url("sales_order/unpost_pelunasan_faktur_z_pecah_batch") ?>',
                    'method': 'POST',
                    'style': 'display:none;'
                });

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'ids',
                    'value': selectedIds.join(',')
                }));

                $('body').append(form);
                form.submit();
            };

            var confirmText = 'Apakah Anda yakin ingin membatalkan (unpost) pelunasan ' + selectedIds.length + ' Faktur Pecahan H terpilih?\n\nJurnal Pembayaran Acak (Q) dan Jurnal Kas Masuk (A) terkait akan otomatis dihapus dari pembukuan.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Unpost Pelunasan ' + selectedIds.length + ' Faktur H?',
                    text: 'Status faktur akan kembali menjadi Belum Lunas dan seluruh ayat jurnal pelunasan (Q & A) terkait akan dihapus dari sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e0a800',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-undo mr-1"></i> Ya, Batalkan Pelunasan',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        confirmPrompt();
                    }
                });
            } else {
                if (confirm(confirmText)) {
                    confirmPrompt();
                }
            }
        });

        // Helper bersama untuk merender kartu rincian ayat jurnal Faktur Pecahan H
        var renderFakturHJournalCard = function(j, badgeClass, title, icon) {
            // M_Journal::accounting_journal_detail_with_accounts() mengembalikan kunci 'journal'
            var h = j ? (j.journal || j.header) : null;
            if (!h) return '';

            var rowsHtml = '';
            var totD = 0, totK = 0;

            if (j.details && j.details.length > 0) {
                $.each(j.details, function(i, d) {
                    var deb = parseFloat(d.debit || 0);
                    var kre = parseFloat(d.kredit || 0);
                    totD += deb;
                    totK += kre;
                    rowsHtml += '<tr>' +
                        '<td class="text-center align-middle">' + (i + 1) + '</td>' +
                        '<td class="text-center align-middle font-weight-bold text-monospace" style="font-size: 11px;">' + (d.kode_akun || '-') + '</td>' +
                        '<td class="align-middle font-weight-bold">' + (d.nama_akun || '-') + '</td>' +
                        '<td class="align-middle small text-muted">' + (d.keterangan || '-') + '</td>' +
                        '<td class="text-right align-middle text-success font-weight-bold">' + (deb > 0 ? ('Rp ' + deb.toLocaleString('id-ID')) : '-') + '</td>' +
                        '<td class="text-right align-middle text-danger font-weight-bold">' + (kre > 0 ? ('Rp ' + kre.toLocaleString('id-ID')) : '-') + '</td>' +
                    '</tr>';
                });
            } else {
                rowsHtml = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada baris ayat jurnal.</td></tr>';
            }

            return '' +
                '<div class="card mb-3 shadow-sm border">' +
                    '<div class="card-header bg-light d-flex justify-content-between align-items-center py-2">' +
                        '<span class="font-weight-bold ' + badgeClass + ' px-2 py-1 rounded" style="font-size: 12px;">' +
                            '<i class="' + icon + ' mr-1"></i> ' + title + ' — ' + (h.nomor_jurnal || '-') +
                        '</span>' +
                        '<span class="small text-muted">' +
                            'Tanggal: <strong>' + (h.tanggal_transaksi || '-') + '</strong> | Ref: <strong>' + (h.source_no || '-') + '</strong> | Status: <span class="badge badge-success">' + (h.status || 'POSTED') + '</span>' +
                        '</span>' +
                    '</div>' +
                    '<div class="card-body p-0">' +
                        '<div class="table-responsive">' +
                            '<table class="table table-sm table-striped table-hover mb-0" style="font-size: 12px;">' +
                                '<thead class="thead-light">' +
                                    '<tr>' +
                                        '<th width="35" class="text-center">#</th>' +
                                        '<th width="90" class="text-center">Kode Akun</th>' +
                                        '<th>Nama Akun</th>' +
                                        '<th>Keterangan</th>' +
                                        '<th width="140" class="text-right">Debit</th>' +
                                        '<th width="140" class="text-right">Kredit</th>' +
                                    '</tr>' +
                                '</thead>' +
                                '<tbody>' + rowsHtml + '</tbody>' +
                                '<tfoot class="bg-light font-weight-bold">' +
                                    '<tr>' +
                                        '<td colspan="4" class="text-right">TOTAL:</td>' +
                                        '<td class="text-right text-success">Rp ' + totD.toLocaleString('id-ID') + '</td>' +
                                        '<td class="text-right text-danger">Rp ' + totK.toLocaleString('id-ID') + '</td>' +
                                    '</tr>' +
                                '</tfoot>' +
                            '</table>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        };

        // Handler klik Badge Nomor Jurnal untuk melihat Rincian Ayat Jurnal Pelunasan
        $(document).on('click', '.btn-view-jurnal-pecah', function(e) {
            e.preventDefault();
            var idPecah = $(this).attr('data-id-pecah');
            if (!idPecah) return;

            $('#jurnalPecahNoFaktur').text('Memuat...');
            $('#bodyDetailJurnalPelunasanH').html('<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 text-purple"></i><br>Memuat rincian jurnal...</div>');
            $('#modalDetailJurnalPelunasanH').modal('show');

            $.getJSON('<?= base_url("sales_order/jurnal_pelunasan_faktur_z_pecah_json/") ?>' + idPecah, function(res) {
                if (res.status === 'success') {
                    var f = res.faktur;
                    $('#jurnalPecahNoFaktur').text(f.no_faktur);

                    var contentHtml = '';
                    contentHtml += renderFakturHJournalCard(res.jurnal_q, 'badge-warning text-dark', 'Jurnal Pembayaran Acak (Q)', 'fas fa-book');
                    contentHtml += renderFakturHJournalCard(res.jurnal_a, 'badge-danger text-white', 'Jurnal Kas Masuk Potongan (A)', 'fas fa-file-invoice-dollar');
                    if (!contentHtml) {
                        contentHtml = '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle mr-1"></i> Data rincian jurnal belum tersedia untuk faktur ini.</div>';
                    }

                    $('#bodyDetailJurnalPelunasanH').html(contentHtml);
                } else {
                    $('#bodyDetailJurnalPelunasanH').html('<div class="alert alert-danger"><i class="fas fa-times-circle mr-1"></i> ' + (res.message || 'Gagal memuat jurnal pelunasan.') + '</div>');
                }
            }).fail(function() {
                $('#bodyDetailJurnalPelunasanH').html('<div class="alert alert-danger"><i class="fas fa-times-circle mr-1"></i> Gagal menghubungi server.</div>');
            });
        });

        // Handler klik Badge Nomor Jurnal Penjualan untuk melihat Rincian Jurnal Penjualan & HPP Faktur H
        $(document).on('click', '.btn-view-jurnal-penjualan-h', function(e) {
            e.preventDefault();
            var idPecah = $(this).attr('data-id-pecah');
            if (!idPecah) return;

            $('#jurnalPenjualanPecahNoFaktur').text('Memuat...');
            $('#bodyDetailJurnalPenjualanH').html('<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 text-info"></i><br>Memuat rincian jurnal penjualan...</div>');
            $('#modalDetailJurnalPenjualanH').modal('show');

            $.getJSON('<?= base_url("sales_order/jurnal_penjualan_faktur_z_pecah_json/") ?>' + idPecah, function(res) {
                if (res.status === 'success') {
                    var f = res.faktur;
                    $('#jurnalPenjualanPecahNoFaktur').text(f.no_faktur);

                    var contentHtml = '';
                    contentHtml += renderFakturHJournalCard(res.jurnal_penjualan, 'badge-info text-white', 'Jurnal Penjualan Resmi (SJ)', 'fas fa-file-invoice');
                    contentHtml += renderFakturHJournalCard(res.jurnal_hpp, 'badge-secondary text-white', 'Jurnal HPP & Persediaan (Goods Issue)', 'fas fa-boxes');
                    if (!contentHtml) {
                        contentHtml = '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle mr-1"></i> Data jurnal penjualan belum tersedia untuk faktur pecahan ini.</div>';
                    }

                    $('#bodyDetailJurnalPenjualanH').html(contentHtml);
                } else {
                    $('#bodyDetailJurnalPenjualanH').html('<div class="alert alert-danger"><i class="fas fa-times-circle mr-1"></i> ' + (res.message || 'Gagal memuat jurnal penjualan.') + '</div>');
                }
            }).fail(function() {
                $('#bodyDetailJurnalPenjualanH').html('<div class="alert alert-danger"><i class="fas fa-times-circle mr-1"></i> Gagal menghubungi server.</div>');
            });
        });
    }
});

// Jalankan setup setelah seluruh window dan footer script ter-load sempurna
if (document.readyState === 'complete') {
    setupDataTablesFaktur();
} else {
    window.addEventListener('load', setupDataTablesFaktur);
}
</script>
