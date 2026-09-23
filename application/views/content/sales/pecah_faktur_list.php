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
                <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary btn-sm mr-1">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                        </a>
                        <a href="<?= base_url('sales_order/admin_sc/activity_log') ?>" class="btn btn-outline-info btn-sm mr-1">
                            <i class="fas fa-history mr-1"></i> Log Aktivitas Faktur
                        </a>
                        <a href="<?= base_url('sales_order/customer_acak') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-users mr-1"></i> Master Customer Acak
                        </a>
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
                                    <a href="<?= base_url('sales_order/pecah_faktur') ?>" class="btn btn-default btn-sm">
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
                                    <i class="fas fa-file-invoice text-primary mr-1"></i> Faktur Z Induk (Stok & Jurnal)
                                    <span class="badge badge-primary ml-1"><?= count($fakturs) ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold" id="tab-faktur-h" data-toggle="pill" href="#content-faktur-h" role="tab" aria-controls="content-faktur-h" aria-selected="false">
                                    <i class="fas fa-cut text-warning mr-1"></i> Faktur Pecahan Kode H (Tabel Terpisah)
                                    <span class="badge badge-warning ml-1"><?= count($fakturs_h) ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="custom-tabs-faktur-content">
                            <!-- TAB 1: FAKTUR Z INDUK -->
                            <div class="tab-pane fade show active" id="content-faktur-z" role="tabpanel" aria-labelledby="tab-faktur-z">
                                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <span class="font-weight-bold text-dark">
                                            <i class="fas fa-file-invoice text-primary mr-1"></i> Daftar Faktur Z Induk
                                        </span>
                                        <span class="text-muted small ml-2 d-none d-md-inline">
                                            Klik tombol <strong>Pecah</strong> pada kolom aksi untuk memecah Faktur Z.
                                        </span>
                                    </div>
                                    <div>
                                        <span class="badge badge-light border font-weight-bold">
                                            Total: <strong><?= count($fakturs) ?></strong> Faktur Z
                                        </span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0 text-nowrap" id="tableFakturZ">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="40" class="text-center">No</th>
                                                <th>No. Faktur Z</th>
                                                <th>Tanggal</th>
                                                <th>No. SO</th>
                                                <th>Customer Asal</th>
                                                <th class="text-center">Total Item</th>
                                                <th class="text-right">Grand Total</th>
                                                <th>Status Pemecahan</th>
                                                <th class="text-center">Status Faktur</th>
                                                <th width="140" class="text-center no-sort">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($fakturs)): ?>
                                                <tr>
                                                    <td colspan="10" class="text-center py-5 text-muted">
                                                        <i class="fas fa-info-circle fa-2x mb-2 text-info"></i><br>
                                                        Belum ada data Faktur Z yang sesuai dengan filter.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $no = 1; foreach ($fakturs as $f): ?>
                                                    <?php 
                                                    $is_child = !empty($f['parent_id_faktur']);
                                                    $is_split_parent = !empty($f['is_split_parent']);
                                                    $can_split = !empty($f['can_split']);
                                                    ?>
                                                    <tr>
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
                                                        <td class="align-middle">
                                                            <span class="text-dark font-weight-bold">
                                                                <?= htmlspecialchars($f['no_so']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="align-middle">
                                                            <span class="font-weight-bold text-dark d-block">
                                                                <?= htmlspecialchars($f['display_customer_name'] ?? $f['customer_name'] ?? '-') ?>
                                                            </span>
                                                            <span class="text-muted small">
                                                                Kode: <?= htmlspecialchars($f['kd_customer'] ?? '-') ?>
                                                                <?= !empty($f['customer_kd_rute']) ? ' &bull; Rute: ' . htmlspecialchars($f['customer_kd_rute']) : '' ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-light border font-weight-bold">
                                                                <?= (int)$f['total_barang'] ?> item (<?= number_format((float)$f['total_qty']) ?> pcs)
                                                            </span>
                                                        </td>
                                                        <td class="text-right align-middle font-weight-bold">
                                                            Rp <?= number_format((float)$f['grand_total'], 0, ',', '.') ?>
                                                        </td>
                                                        <td class="align-middle">
                                                            <?php if ($f['tipe_faktur'] === 'dipecah_sebagian'): ?>
                                                                <span class="badge badge-warning badge-status-pecah d-inline-block mb-1">
                                                                    <i class="fas fa-cut mr-1"></i> Dipecah Sebagian
                                                                </span>
                                                                <div class="small text-muted">
                                                                    Sisa: <strong><?= number_format($f['remaining_split_qty']) ?> pcs</strong>
                                                                </div>
                                                                <?php if (!empty($f['child_fakturs'])): ?>
                                                                    <div class="mt-1">
                                                                        <span class="text-muted small">Turunan (H):</span>
                                                                        <?php foreach ($f['child_fakturs'] as $cf): ?>
                                                                            <?php $cf_url = !empty($cf['id_pecah']) ? base_url('sales_order/detail_faktur_pecah/' . $cf['id_pecah']) : base_url('sales_order/detail_faktur/' . ($cf['id_faktur'] ?? '')); ?>
                                                                            <a href="<?= $cf_url ?>" 
                                                                               class="child-faktur-pill" 
                                                                               title="Customer: <?= htmlspecialchars($cf['customer_name']) ?>">
                                                                                <?= htmlspecialchars($cf['no_faktur']) ?>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php elseif ($f['tipe_faktur'] === 'sudah_dipecah'): ?>
                                                                <span class="badge badge-success badge-status-pecah d-inline-block mb-1">
                                                                    <i class="fas fa-check-circle mr-1"></i> Selesai Dipecah
                                                                </span>
                                                                <?php if (!empty($f['child_fakturs'])): ?>
                                                                    <div class="mt-1">
                                                                        <span class="text-muted small"><?= count($f['child_fakturs']) ?> Pecahan (Kode H):</span>
                                                                        <?php foreach ($f['child_fakturs'] as $cf): ?>
                                                                            <?php $cf_url = !empty($cf['id_pecah']) ? base_url('sales_order/detail_faktur_pecah/' . $cf['id_pecah']) : base_url('sales_order/detail_faktur/' . ($cf['id_faktur'] ?? '')); ?>
                                                                            <a href="<?= $cf_url ?>" 
                                                                               class="child-faktur-pill" 
                                                                               title="Customer: <?= htmlspecialchars($cf['customer_name']) ?>">
                                                                                <?= htmlspecialchars($cf['no_faktur']) ?>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="badge badge-secondary badge-status-pecah">
                                                                    <i class="fas fa-hourglass-start mr-1"></i> Belum Dipecah
                                                                </span>
                                                            <?php endif; ?>
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
                                                                    <?php 
                                                                    $grand_total_asli = (float)($f['grand_total'] ?? 0); 
                                                                    $tot_netto_h      = round($grand_total_asli * 0.8, 2);
                                                                    $tot_qty          = (float)($f['total_qty'] ?? 0);
                                                                    ?>
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

                            <!-- TAB 2: FAKTUR PECAHAN KODE H -->
                            <div class="tab-pane fade" id="content-faktur-h" role="tabpanel" aria-labelledby="tab-faktur-h">
                                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <span class="font-weight-bold text-dark">
                                            <i class="fas fa-shield-alt text-success mr-1"></i> Tabel Terpisah (`tbso_faktur_z_pecah`):
                                        </span>
                                        <span class="text-muted small">
                                            Faktur hasil pecahan berawalan <strong>kode H</strong> murni bersifat administratif pembagian pengiriman/customer, <strong>tidak terjurnal</strong> dan <strong>tidak memotong stok</strong> ulang.
                                        </span>
                                    </div>
                                    <span class="badge badge-warning">
                                        Total: <strong><?= count($fakturs_h) ?></strong> Faktur Pecahan H
                                    </span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0 text-nowrap" id="tableFakturH">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="40" class="text-center">No</th>
                                                <th>No. Faktur Pecahan</th>
                                                <th>Faktur Induk Z</th>
                                                <th>Tanggal</th>
                                                <th>No. SO</th>
                                                <th>Customer Penerima</th>
                                                <th class="text-center">Total Item</th>
                                                <th class="text-right">Total Nilai</th>
                                                <th>Dibuat Oleh</th>
                                                <th width="100" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($fakturs_h)): ?>
                                                <tr>
                                                    <td colspan="10" class="text-center py-5 text-muted">
                                                        <i class="fas fa-inbox fa-2x mb-2 text-warning"></i><br>
                                                        Belum ada faktur pecahan (kode H) yang dibuat.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $no = 1; foreach ($fakturs_h as $fh): ?>
                                                    <tr>
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
                                                        <td class="align-middle font-weight-bold text-dark">
                                                            <?= htmlspecialchars($fh['no_so']) ?>
                                                        </td>
                                                        <td class="align-middle">
                                                            <span class="font-weight-bold text-dark d-block">
                                                                <?= htmlspecialchars($fh['customer_name'] ?? '-') ?>
                                                            </span>
                                                            <span class="text-muted small">
                                                                Kode: <?= htmlspecialchars($fh['kd_customer'] ?? '-') ?>
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
                                                        <td class="align-middle small text-muted">
                                                            <?= htmlspecialchars($fh['create_by'] ?? '-') ?><br>
                                                            <span style="font-size: 10px;"><?= !empty($fh['create_at']) ? date('d/m/y H:i', strtotime($fh['create_at'])) : '' ?></span>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <a href="<?= base_url('sales_order/detail_faktur_pecah/' . $fh['id_pecah']) ?>"
                                                               class="btn btn-default btn-sm border"
                                                               title="Lihat Rincian / Cetak Faktur Pecahan">
                                                                <i class="fas fa-eye text-primary mr-1"></i> Detail
                                                            </a>
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

                        <!-- Card Info Pemecahan Berbasis QTY -->
                        <div class="card card-outline card-info shadow-none border mb-3">
                            <div class="card-body p-2 bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small font-weight-bold text-dark">
                                        <i class="fas fa-boxes text-info mr-1"></i> Pemecahan Berbasis Kuantitas (QTY):
                                    </span>
                                    <span class="badge badge-info font-weight-bold" id="modalHeaderQtyBadge">
                                        0 pcs
                                    </span>
                                </div>
                                <div class="small text-muted mt-1" id="modalRecommendationText">
                                    Kuantitas barang akan didistribusikan ke sejumlah faktur pecahan (Kode Awalan H).
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label class="font-weight-bold text-dark mb-1">
                                Mau dipecah menjadi berapa faktur? <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       name="jumlah_pecah" 
                                       id="inputJumlahPecah" 
                                       class="form-control form-control-lg font-weight-bold text-center" 
                                       value="2" 
                                       min="1" 
                                       max="50" 
                                       required>
                                <div class="input-group-append">
                                    <span class="input-group-text font-weight-bold">Faktur Pecahan (H)</span>
                                </div>
                            </div>
                            <small class="text-muted">Tentukan jumlah faktur turunan yang ingin dibuat dari kuantitas barang di atas.</small>
                        </div>

                        <!-- Estimasi Qty & Nilai Realtime per Faktur -->
                        <div class="p-2 border rounded bg-white mb-2 text-center" id="modalEstimasiBox">
                            <div class="row">
                                <div class="col-6 border-right">
                                    <span class="text-muted small d-block">Rata-rata Qty / Faktur:</span>
                                    <strong class="font-weight-bold text-primary" id="modalEstimasiQty" style="font-size: 15px;">0 pcs</strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted small d-block">Rata-rata Nilai / Faktur:</span>
                                    <strong class="font-weight-bold text-success" id="modalEstimasiNilai" style="font-size: 15px;">Rp 0</strong>
                                </div>
                            </div>
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

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>

<script>
function formatRupiahJs(num) {
    return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Variabel penampung total nilai & qty saat modal dibuka
window.currentModalTotalNilai       = 0;
window.currentModalTotalNominalAwal = 0;
window.currentModalTotalQty         = 0;

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
        inputPecah.value = 2; // Default 2 pecahan
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
            "columnDefs": [
                { "orderable": false, "targets": "no-sort" }
            ],
            "language": {
                "search": "Cari data:",
                "lengthMenu": "Tampilkan _MENU_ data",
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

        if (!$.fn.DataTable.isDataTable('#tableFakturZ')) {
            $('#tableFakturZ').DataTable(dataTableConfig);
        }

        if (!$.fn.DataTable.isDataTable('#tableFakturH')) {
            $('#tableFakturH').DataTable(dataTableConfig);
        }
    }
}

// Jalankan setup setelah seluruh window dan footer script ter-load sempurna
if (document.readyState === 'complete') {
    setupDataTablesFaktur();
} else {
    window.addEventListener('load', setupDataTablesFaktur);
}
</script>
