<!-- views/content/sales/so_loby_list.php -->
<style>
    #tabelSOLoby_wrapper {
        padding: 0 6px 6px;
    }
    #tabelSOLoby_wrapper .row:first-child,
    #tabelSOLoby_wrapper .row:last-child {
        margin-left: 0;
        margin-right: 0;
        padding: 4px 8px;
    }
    .so-status-badge {
        display: inline-flex;
        justify-content: center;
        min-width: 90px;
        font-weight: 700;
        font-size: 11px;
    }
    .panel-loby-header {
        background: linear-gradient(135deg, #1788b8 0%, #0d5f83 100%);
        color: #fff;
        border-radius: 4px 4px 0 0;
        padding: 12px 18px;
    }
    .btn-action-group {
        display: inline-flex;
        gap: 4px;
    }
</style>
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
                        <h1 class="m-0"><i class="fas fa-store mr-2 text-primary"></i> Sales Order Loby</h1>
                        <small class="text-muted">Transaksi penjualan langsung / cash walk-in customer di Loby</small>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales</a></li>
                            <li class="breadcrumb-item active">Sales Order Loby</li>
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

                <!-- FILTER PANEL -->
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title font-weight-bold text-dark"><i class="fas fa-filter mr-1 text-primary"></i> Filter Pencarian</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <form method="get" action="<?= base_url('sales_order_loby') ?>">
                            <div class="row align-items-end">
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Dari Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="date_start" value="<?= html_escape($filter['date_start'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Sampai Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="date_end" value="<?= html_escape($filter['date_end'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small font-weight-bold">Status SO / Faktur</label>
                                    <select class="form-control form-control-sm" name="status">
                                        <option value="">-- Semua Status --</option>
                                        <option value="un-invoiced" <?= ($filter['status'] ?? '') === 'un-invoiced' ? 'selected' : '' ?>>Belum Difakturkan (Open)</option>
                                        <option value="invoiced" <?= ($filter['status'] ?? '') === 'invoiced' ? 'selected' : '' ?>>Sudah Difakturkan (Completed)</option>
                                        <option value="cancelled" <?= ($filter['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <button type="submit" class="btn btn-sm btn-primary mr-1"><i class="fas fa-search mr-1"></i> Filter</button>
                                    <a href="<?= base_url('sales_order_loby') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-undo mr-1"></i> Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- MAIN TABLE CARD -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <h3 class="card-title font-weight-bold text-dark m-0">
                            <i class="fas fa-list mr-1 text-primary"></i> Daftar Transaksi Sales Order Loby
                        </h3>
                        <div class="card-tools m-0">
                            <a href="<?= base_url('sales_order_loby/create') ?>" class="btn btn-primary btn-sm font-weight-bold">
                                <i class="fas fa-plus-circle mr-1"></i> Buat SO Loby Baru
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tabelSOLoby" class="table table-hover table-striped table-bordered m-0" style="width:100%">
                                <thead class="bg-light">
                                    <tr class="text-center">
                                        <th style="width: 50px;">No</th>
                                        <th>No. SO Loby</th>
                                        <th>Tanggal</th>
                                        <th>Customer</th>
                                        <th>User Loby</th>
                                        <th>Metode Bayar</th>
                                        <th>Total Transaksi</th>
                                        <th>Status SO</th>
                                        <th>No. Faktur</th>
                                        <th style="width: 170px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($so_list)): ?>
                                        <?php $no = 1; foreach ($so_list as $row): ?>
                                            <tr>
                                                <td class="text-center align-middle"><?= $no++ ?></td>
                                                <td class="align-middle font-weight-bold text-primary">
                                                    <a href="<?= base_url('sales_order_loby/detail/' . $row['id_so']) ?>"><?= html_escape($row['no_so']) ?></a>
                                                </td>
                                                <td class="text-center align-middle"><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                                <td class="align-middle">
                                                    <div class="font-weight-bold"><?= html_escape($row['customer_name'] ?: $row['nama_customer']) ?></div>
                                                    <small class="text-muted"><?= html_escape($row['kd_customer']) ?></small>
                                                </td>
                                                <td class="align-middle"><?= html_escape($row['create_by']) ?></td>
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-money-bill-wave mr-1"></i> CASH</span>
                                                </td>
                                                <td class="text-right align-middle font-weight-bold">
                                                    Rp <?= number_format((float)$row['grand_total_so'], 0, ',', '.') ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php if ($row['status'] === 'completed' || !empty($row['is_invoiced'])): ?>
                                                        <span class="badge badge-success so-status-badge py-1">INVOICED</span>
                                                    <?php elseif ($row['status'] === 'cancelled'): ?>
                                                        <span class="badge badge-danger so-status-badge py-1">CANCELLED</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-info so-status-badge py-1">SO CREATED</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php if (!empty($row['no_faktur'])): ?>
                                                        <a href="<?= base_url('sales_order_loby/detail_faktur/' . $row['id_faktur']) ?>" class="badge badge-primary px-2 py-1">
                                                            <i class="fas fa-file-invoice mr-1"></i> <?= html_escape($row['no_faktur']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">- Belum Faktur -</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <div class="btn-action-group">
                                                        <a href="<?= base_url('sales_order_loby/detail/' . $row['id_so']) ?>" class="btn btn-xs btn-info" title="Detail SO">
                                                            <i class="fas fa-eye"></i> Detail
                                                        </a>

                                                        <?php if (empty($row['is_invoiced']) && $row['status'] !== 'cancelled'): ?>
                                                            <a href="<?= base_url('sales_order_loby/form_faktur/' . $row['id_so']) ?>" class="btn btn-xs btn-success font-weight-bold" title="Proses Faktur Langsung">
                                                                <i class="fas fa-file-invoice-dollar"></i> Faktur
                                                            </a>
                                                            <a href="<?= base_url('sales_order_loby/edit/' . $row['id_so']) ?>" class="btn btn-xs btn-warning" title="Edit SO">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        <?php endif; ?>

                                                        <?php if (!empty($row['id_faktur'])): ?>
                                                            <a href="<?= base_url('sales_order_loby/print_faktur/' . $row['id_faktur']) ?>" target="_blank" class="btn btn-xs btn-secondary" title="Cetak Faktur Penjualan">
                                                                <i class="fas fa-print"></i> Cetak
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
        </section>
    </div>

    <?php $this->load->view('partial/main/footer') ?>
</div>

<script>
$(document).ready(function() {
    $('#tabelSOLoby').DataTable({
        "order": [[2, "desc"], [1, "desc"]],
        "pageLength": 25,
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "info": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
            "paginate": {
                "first": "Awal",
                "last": "Akhir",
                "next": "Lanjut",
                "previous": "Sebelumnya"
            },
            "emptyTable": "Belum ada transaksi Sales Order Loby"
        }
    });
});
</script>
</body>
