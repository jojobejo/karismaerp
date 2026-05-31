<!-- views/content/sales/so_list.php -->
<style>
    #tabelSO_wrapper {
        padding: 0 6px 6px;
    }

    #tabelSO_wrapper .row:first-child,
    #tabelSO_wrapper .row:last-child {
        margin-left: 0;
        margin-right: 0;
        padding: 4px 8px;
    }

    #tabelSO_wrapper .dataTables_length,
    #tabelSO_wrapper .dataTables_filter,
    #tabelSO_wrapper .dataTables_info,
    #tabelSO_wrapper .dataTables_paginate {
        margin: 0;
        padding-top: 0;
    }

    #tabelSO_wrapper .dataTables_length label,
    #tabelSO_wrapper .dataTables_filter label {
        align-items: center;
        display: flex;
        gap: 6px;
        margin-bottom: 0;
    }

    #tabelSO_wrapper .dataTables_filter label {
        justify-content: flex-end;
    }

    #tabelSO_wrapper .dataTables_filter input {
        margin-left: 0;
        height: 30px;
        padding: 3px 8px;
        max-width: 180px;
    }

    #tabelSO_wrapper .dataTables_length select {
        height: 30px;
        padding: 3px 6px;
    }

    #tabelSO_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 4px;
    }
</style>
<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
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
                    <h1 class="m-0"><i class="fas fa-file-invoice mr-2"></i> Sales Order</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Sales Order</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- FLASH MESSAGE -->
            <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                <?php if ($msg = $this->session->flashdata($key)): ?>
                    <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                        <i class="fas fa-<?= $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-circle' : 'exclamation-triangle') ?> mr-1"></i>
                        <?= $msg ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- TOMBOL AKSI -->
            <div class="row mb-2">
                <div class="col-auto">
                    <a href="<?= base_url('sales_order/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat SO Baru
                    </a>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('sales_order/activity_log') ?>" class="btn btn-info">
                        <i class="fas fa-history"></i> Activity Log
                    </a>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('sales_order/faktur_rute') ?>" class="btn btn-success">
                        <i class="fas fa-route"></i> Faktur per Rute
                    </a>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('sales_order/so_rute') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-map-marked-alt"></i> SO per Rute
                    </a>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('checker') ?>" class="btn btn-warning">
                        <i class="fas fa-warehouse"></i> Activity Warehouse
                    </a>
                </div>
            </div>

            <!-- FILTER -->
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter</h3>
                </div>
                <div class="card-body py-2">
                    <form action="<?= base_url('sales_order') ?>" method="post">
                        <div class="row">
                            <div class="col-md-2">
                                <label class="small mb-0">Dari Tanggal</label>
                                <input type="date" class="form-control form-control-sm" name="date1"
                                       value="<?= htmlspecialchars($filter['date1'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-0">Sampai Tanggal</label>
                                <input type="date" class="form-control form-control-sm" name="date2"
                                       value="<?= htmlspecialchars($filter['date2'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-0">Customer</label>
                                <select name="customer_id" class="form-control form-control-sm">
                                    <option value="">-- Semua Customer --</option>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?= $c['id'] ?>"
                                            <?= ($filter['customer_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['nama_customer']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-0">Status</label>
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">-- Semua Status --</option>
                                    <option value="draft"     <?= ($filter['status'] ?? '') === 'draft'     ? 'selected' : '' ?>>Draft</option>
                                    <option value="open"      <?= ($filter['status'] ?? '') === 'open'      ? 'selected' : '' ?>>Open</option>
                                    <option value="sedang_verifikasi" <?= ($filter['status'] ?? '') === 'sedang_verifikasi' ? 'selected' : '' ?>>Sedang Verifikasi</option>
                                    <option value="siap_faktur" <?= ($filter['status'] ?? '') === 'siap_faktur' ? 'selected' : '' ?>>Siap Faktur</option>
                                    <option value="completed" <?= ($filter['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= ($filter['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-success btn-sm mr-1">
                                    <i class="fas fa-search"></i> Tampil
                                </button>
                                <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary btn-sm">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABEL SO -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i> Daftar Sales Order
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light"><?= count($so_list) ?> SO</span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover table-sm" id="tabelSO">
                        <thead class="thead-dark">
                            <tr>
                                <th>No SO</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th>Rute</th>
                                <th class="text-right">Item Diorder</th>
                                <th class="text-right">Item Selesai</th>
                                <th class="text-right">Outstanding</th>
                                <th class="text-center" style="min-width:170px;">Progress</th>
                                <th class="text-center">Status</th>
                                <th class="text-center no-sort" style="min-width:120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($so_list)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Tidak ada data Sales Order
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $badge_map = [
                                    'draft'              => 'secondary',
                                    'open'               => 'primary',
                                    'sedang_verifikasi'  => 'warning',
                                    'siap_faktur'        => 'info',
                                    'completed'          => 'success',
                                    'cancelled'          => 'danger',
                                ];
                                $label_map = [
                                    'draft'              => 'Draft',
                                    'open'               => 'Open',
                                    'sedang_verifikasi'  => 'Sedang Verifikasi',
                                    'siap_faktur'        => 'Siap Faktur',
                                    'completed'          => 'Completed',
                                    'cancelled'          => 'Cancelled',
                                ];
                                foreach ($so_list as $row):
                                    $badge      = $badge_map[$row['status']] ?? 'secondary';
                                    $label      = $label_map[$row['status']] ?? $row['status'];
                                    $item_diorder  = (int)($row['jumlah_item']          ?? 0);
                                    $item_diterima = (int)($row['jumlah_item_diterima']  ?? 0);
                                    $outstanding   = $item_diorder - $item_diterima;
                                    $pct           = $item_diorder > 0 ? round(($item_diterima / $item_diorder) * 100, 1) : 0;

                                    if ($row['status'] === 'completed' || $pct >= 100) {
                                        $bar_color = 'success';
                                    } elseif ($row['status'] === 'cancelled') {
                                        $bar_color = 'danger';
                                    } elseif ($pct > 0) {
                                        $bar_color = 'warning';
                                    } else {
                                        $bar_color = 'secondary';
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?= base_url('sales_order/detail/' . $row['id_so']) ?>"
                                           class="font-weight-bold">
                                            <?= htmlspecialchars($row['no_so']) ?>
                                        </a>
                                    </td>
                                    <td class="text-nowrap"><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                    <td><?= !empty($row['customer_kd_rute']) ? htmlspecialchars($row['customer_kd_rute']) : '<span class="text-muted">-</span>' ?></td>
                                    <td class="text-center"><?= number_format($item_diorder) ?></td>
                                    <td class="text-center text-success font-weight-bold">
                                        <?= number_format($item_diterima) ?>
                                    </td>
                                    <td class="text-center <?= $outstanding > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                        <?= number_format($outstanding) ?>
                                    </td>

                                    <!-- PROGRESS -->
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-1" style="height:16px; border-radius:3px;">
                                                <div class="progress-bar bg-<?= $bar_color ?>"
                                                     style="width:<?= $pct ?>%; font-size:10px; line-height:16px;">
                                                    <?= $pct > 15 ? $pct . '%' : '' ?>
                                                </div>
                                            </div>
                                            <small class="text-nowrap font-weight-bold text-<?= $bar_color === 'secondary' ? 'muted' : $bar_color ?>"
                                                   style="min-width:38px;">
                                                <?= $pct ?>%
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge badge-<?= $badge ?> px-2 py-1"><?= $label ?></span>
                                    </td>

                                    <!-- TOMBOL AKSI — diperbesar -->
                                    <td class="text-center text-nowrap">
                                        <a href="<?= base_url('sales_order/detail/' . $row['id_so']) ?>"
                                           class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
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
    </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(document).ready(function () {
    $('#tabelSO').DataTable({
        responsive:  true,
        autoWidth:   false,
        pageLength:  25,
        order:       [[1, 'desc']],
        columnDefs:  [
            { orderable: false, targets: [7, 9] }
        ],
        language: {
            search:      "Cari:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable:  "Tidak ada data Sales Order",
            paginate:    { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });
});
</script>
