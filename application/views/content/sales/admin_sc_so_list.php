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
                <?php
                    $breadcrumb_filter = array_filter([
                        'date1'       => $filter['date1'] ?? '',
                        'date2'       => $filter['date2'] ?? '',
                        'customer_id' => $filter['customer_id'] ?? '',
                    ], function($v) { return $v !== '' && $v !== null; });
                    $admin_sc_route_url = base_url('sales_order/admin_sc'
                        . (!empty($breadcrumb_filter) ? '?' . http_build_query($breadcrumb_filter) : ''));
                ?>
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                            <?= !empty($selected_rute)
                                ? 'Admin SC - Rute ' . htmlspecialchars($selected_rute)
                                : 'Admin SC - Rute Siap Faktur' ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <?php if (!empty($selected_rute)): ?>
                                <li class="breadcrumb-item"><a href="<?= $admin_sc_route_url ?>">Admin SC</a></li>
                                <li class="breadcrumb-item active">Rute <?= htmlspecialchars($selected_rute) ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active">Admin SC</li>
                            <?php endif; ?>
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
                            <?php if (!empty($selected_rute)): ?>
                                <input type="hidden" name="rute" value="<?= htmlspecialchars($selected_rute, ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
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

                <?php
                    $route_count = isset($route_summary) ? count($route_summary) : 0;
                    $active_query = array_filter([
                        'date1'       => $filter['date1'] ?? '',
                        'date2'       => $filter['date2'] ?? '',
                        'customer_id' => $filter['customer_id'] ?? '',
                    ], function($v) { return $v !== '' && $v !== null; });
                ?>

                <?php if (empty($selected_rute)): ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-route mr-2"></i> Rute Siap Faktur
                        </h3>
                        <div class="card-tools">
                            <a href="<?= base_url('sales_order/admin_sc/faktur') ?>" class="btn btn-light btn-xs mr-1">
                                <i class="fas fa-file-invoice mr-1"></i> Faktur Selesai
                            </a>
                            <span class="badge badge-light"><?= $route_count ?> rute</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover table-sm" id="tabelAdminScSO">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Rute</th>
                                    <th class="text-center">Total SO</th>
                                    <th class="text-center">Sudah Faktur</th>
                                    <th class="text-center">Belum Faktur</th>
                                    <th class="text-right">Qty Siap Faktur</th>
                                    <th class="text-right">Tidak Terkirim</th>
                                    <th class="text-center no-sort" style="min-width:86px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($route_summary)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Tidak ada rute dengan Sales Order siap faktur.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($route_summary as $row):
                                        $rute_query = array_merge($active_query, ['rute' => $row['kd_rute']]);
                                    ?>
                                        <tr>
                                            <td class="font-weight-bold">
                                                <?= htmlspecialchars($row['kd_rute']) ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary px-2 py-1"><?= number_format((int)$row['total_so']) ?></span>
                                            </td>
                                            <td class="text-center text-success font-weight-bold">
                                                <?= number_format((int)$row['total_sudah_faktur']) ?>
                                            </td>
                                            <td class="text-center <?= (int)$row['total_belum_faktur'] > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                                <?= number_format((int)$row['total_belum_faktur']) ?>
                                            </td>
                                            <td class="text-right font-weight-bold text-success">
                                                <?= number_format((float)($row['total_qty_siap_faktur'] ?? 0), 2) ?>
                                            </td>
                                            <td class="text-right <?= (float)($row['total_qty_tidak_terkirim'] ?? 0) > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                                <?= number_format((float)($row['total_qty_tidak_terkirim'] ?? 0), 2) ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= base_url('sales_order/admin_sc?' . http_build_query($rute_query)) ?>"
                                                   class="btn btn-sm btn-success" title="Lihat SO rute">
                                                    <i class="fas fa-arrow-right mr-1"></i> Fakturkan
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <div class="mb-2">
                    <a href="<?= base_url('sales_order/admin_sc?' . http_build_query($active_query)) ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Rute
                    </a>
                    <span class="badge badge-primary ml-1 px-2 py-1">Rute <?= htmlspecialchars($selected_rute) ?></span>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i> SO Siap Faktur - Rute <?= htmlspecialchars($selected_rute) ?>
                        </h3>
                        <div class="card-tools">
                            <a href="<?= base_url('sales_order/admin_sc/faktur') ?>" class="btn btn-light btn-xs mr-1">
                                <i class="fas fa-file-invoice mr-1"></i> Faktur Selesai
                            </a>
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
                                    <th>Sales</th>
                                    <th class="text-center">Item</th>
                                    <th class="text-right">Qty Siap Faktur</th>
                                    <th class="text-right">Tidak Terkirim</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center no-sort" style="min-width:56px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($so_list)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Tidak ada Sales Order siap faktur pada rute ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($so_list as $row): ?>
                                        <tr>
                                            <td><span class="font-weight-bold"><?= htmlspecialchars($row['no_so']) ?></span></td>
                                            <td class="text-nowrap">
                                                <?= !empty($row['tanggal_transaksi']) ? date('d/m/Y', strtotime($row['tanggal_transaksi'])) : '-' ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($row['customer_name'] ?: ($row['nama_customer'] ?? '-')) ?>
                                                <?php if (!empty($row['nama_kios'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($row['nama_kios']) ?></small>
                                                <?php endif; ?>
                                            </td>
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
                                                <span class="badge badge-info px-2 py-1">Siap Faktur</span>
                                                <?php if ((int)($row['jumlah_faktur'] ?? 0) > 0): ?>
                                                    <br><small class="text-success"><?= (int)$row['jumlah_faktur'] ?> faktur</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="<?= base_url('sales_order/admin_sc/pilih_barang/' . $row['id_so']) ?>"
                                                class="btn btn-sm btn-success" title="Pilih barang untuk faktur">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger btn-kembalikan-so ml-1"
                                                        data-id="<?= (int)$row['id_so'] ?>"
                                                        data-noso="<?= htmlspecialchars($row['no_so']) ?>"
                                                        title="Kembalikan ke Sales">
                                                    <i class="fas fa-reply"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>

    <!-- Modal Kembalikan SO ke Sales -->
    <div class="modal fade" id="modalKembalikanSO" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-reply mr-1"></i>Kembalikan SO ke Sales
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Perhatian! SO akan dikembalikan ke Sales .
                    </div>
                    <p>Yakin mengembalikan SO <strong id="kembalikan-no-so"></strong> ke Sales?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btn-konfirmasi-kembalikan">
                        <i class="fas fa-reply mr-1"></i>Ya, Kembalikan ke Sales
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    var isRouteDetail = <?= !empty($selected_rute) ? 'true' : 'false' ?>;
    $('#tabelAdminScSO').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: isRouteDetail ? [[1, 'desc']] : [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [isRouteDetail ? 8 : 6] }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable: "Tidak ada Sales Order siap faktur",
            paginate: { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });
});
// ── Kembalikan SO ke Sales ─────────────────────────────
var kembalikanIdSO = 0;

$(document).on('click', '.btn-kembalikan-so', function() {
    kembalikanIdSO = $(this).data('id');
    $('#kembalikan-no-so').text($(this).data('noso'));
    $('#btn-konfirmasi-kembalikan')
        .prop('disabled', false)
        .html('<i class="fas fa-reply mr-1"></i>Ya, Kembalikan ke Sales');
    $('#modalKembalikanSO').modal('show');
});

$('#btn-konfirmasi-kembalikan').on('click', function() {
    if (!kembalikanIdSO) return;

    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...');

    $.ajax({
        url: '<?= base_url("sales_order/admin_sc/kembalikan_so_ke_sales") ?>',
        type: 'POST',
        data: { id_so: kembalikanIdSO },
        dataType: 'JSON',
        success: function(res) {
            $('#modalKembalikanSO').modal('hide');
            if (res.status) {
                alert('✅ ' + res.message);
                location.reload();
            } else {
                alert('❌ ' + (res.message || 'Gagal mengembalikan SO.'));
                btn.prop('disabled', false)
                   .html('<i class="fas fa-reply mr-1"></i>Ya, Kembalikan ke Sales');
            }
        },
        error: function() {
            alert('Terjadi kesalahan koneksi.');
            btn.prop('disabled', false)
               .html('<i class="fas fa-reply mr-1"></i>Ya, Kembalikan ke Sales');
        }
    });
});
</script>
