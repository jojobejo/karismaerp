<!-- views/content/sales/admin_sc_so_list.php -->
<style>
    #tabelAdminScSO_wrapper {
        padding: 0 6px 6px;
    }
    #tabelAdminScSO_wrapper .row:first-child,
    #tabelAdminScSO_wrapper .row:last-child {
        margin-left: 0;
        margin-right: 0;
        padding: 4px 8px;
    }
    #tabelAdminScSO_wrapper .dataTables_filter input {
        height: 30px;
        max-width: 180px;
        padding: 3px 8px;
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
                        <h1 class="m-0">
                            <i class="fas fa-file-invoice-dollar mr-2"></i> Admin SC - SO Siap/Partial Faktur
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">Admin SC</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-circle' : 'exclamation-triangle') ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="card card-outline card-secondary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter</h3>
                    </div>
                    <div class="card-body py-2">
                        <form action="<?= base_url('sales_order/admin_sc') ?>" method="post">
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
                                <div class="col-md-4">
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
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-success btn-sm mr-1">
                                        <i class="fas fa-search mr-1"></i> Tampil
                                    </button>
                                    <a href="<?= base_url('sales_order/admin_sc') ?>" class="btn btn-secondary btn-sm">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i> Daftar Sales Order Siap/Partial Faktur
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($so_list) ?> SO</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover table-sm" id="tabelAdminScSO">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No SO</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Rute</th>
                                    <th>Sales</th>
                                    <th class="text-center">Item</th>
                                    <th class="text-right">Qty Siap Faktur</th>
                                    <th class="text-right">Tidak Terkirim</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center no-sort" style="min-width:120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($so_list)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Tidak ada Sales Order siap/partial faktur.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($so_list as $row): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('sales_order/detail/' . $row['id_so'] . '?from=admin_sc') ?>" class="font-weight-bold">
                                                    <?= htmlspecialchars($row['no_so']) ?>
                                                </a>
                                            </td>
                                            <td class="text-nowrap">
                                                <?= !empty($row['tanggal_transaksi']) ? date('d/m/Y', strtotime($row['tanggal_transaksi'])) : '-' ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($row['customer_name'] ?: ($row['nama_customer'] ?? '-')) ?>
                                                <?php if (!empty($row['nama_kios'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($row['nama_kios']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= !empty($row['kd_rute']) ? htmlspecialchars($row['kd_rute']) : htmlspecialchars($row['customer_kd_rute'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['create_by'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <?= number_format((int)($row['jumlah_item_siap_faktur'] ?? 0)) ?> /
                                                <?= number_format((int)($row['jumlah_item'] ?? 0)) ?>
                                            </td>
                                            <td class="text-right font-weight-bold text-success">
                                                <?= number_format((float)($row['total_qty_siap_faktur'] ?? 0), 2) ?>
                                            </td>
                                            <td class="text-right <?= (float)($row['total_qty_tidak_terkirim'] ?? 0) > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                                <?= number_format((float)($row['total_qty_tidak_terkirim'] ?? 0), 2) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (($row['status'] ?? '') === 'partial'): ?>
                                                    <span class="badge badge-warning px-2 py-1">Partial</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info px-2 py-1">Siap Faktur</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="<?= base_url('sales_order/admin_sc/pilih_barang/' . $row['id_so']) ?>"
                                                   class="btn btn-sm btn-success" title="Pilih barang untuk faktur">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </a>
                                                <a href="<?= base_url('sales_order/detail/' . $row['id_so'] . '?from=admin_sc') ?>"
                                                   class="btn btn-sm btn-info" title="Detail SO">
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
    $('#tabelAdminScSO').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[1, 'desc']],
        columnDefs: [
            { orderable: false, targets: [9] }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable: "Tidak ada Sales Order siap/partial faktur",
            paginate: { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });
});
</script>
