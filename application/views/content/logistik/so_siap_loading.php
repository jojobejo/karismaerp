<!-- views/content/logistik/so_siap_loading.php -->
<style>
    .route-card {
        align-items: center;
        background: #fff;
        border: 1px solid #dee2e6;
        border-left: 3px solid #ced4da;
        border-radius: 4px;
        color: #1f2937;
        display: flex;
        gap: 8px;
        justify-content: space-between;
        margin-bottom: 4px;
        padding: 7px 8px;
        text-decoration: none;
    }
    .route-card:hover {
        background: #f8fbff;
        border-left-color: #007bff;
        color: #1f2937;
        text-decoration: none;
    }
    .route-card.active {
        background: #eaf4ff;
        border-color: #007bff;
        border-left-color: #007bff;
    }
    .route-code {
        font-size: 13px;
        font-weight: 700;
        min-width: 54px;
    }
    .route-meta {
        color: #6c757d;
        flex: 1 1 auto;
        font-size: 10.5px;
        min-width: 0;
    }
    .route-tonase {
        color: #15803d;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }
    #tabelSoSiapLoading_wrapper {
        padding: 12px;
    }
    .summary-box .info-box {
        min-height: 78px;
    }
    .summary-box .info-box-icon {
        align-items: center;
        display: flex;
        height: 58px;
        justify-content: center;
        margin-left: 8px;
        width: 58px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <?php
    $selected_route_name = '-';
    foreach ($routes as $route) {
        if ((string)$route->kd_rute === (string)$selected_rute) {
            $selected_route_name = $route->nama_rute ?: $route->kd_rute;
            break;
        }
    }

    $total_so = count($so_list);
    $total_tonase = 0;
    $total_kubikasi = 0;
    $total_qty = 0;
    $total_outstanding = 0;
    foreach ($so_list as $so) {
        $total_tonase += (float)($so->total_tonase ?? 0);
        $total_kubikasi += (float)($so->total_kubikasi ?? 0);
        $total_qty += (float)($so->total_qty_order ?? 0);
        $total_outstanding += (float)($so->total_qty_outstanding ?? 0);
    }
    ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-7">
                        <h1 class="m-0 text-dark" style="font-size:1.3rem;">
                            <i class="fas fa-clipboard-check mr-2"></i>Sales Order Siap Loading
                        </h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('logistik') ?>">Logistik</a></li>
                            <li class="breadcrumb-item active">SO Siap Loading</li>
                        </ol>
                    </div>
                </div>

                <div class="mb-2">
                    <a href="<?= base_url('logistik') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Delivery Order
                    </a>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php if ($this->session->flashdata('msg')): ?>
                    <div class="alert alert-info alert-dismissible fade show">
                        <?= $this->session->flashdata('msg') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-lg-3">
                        <div class="card card-outline card-primary">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-route mr-1"></i>Pilih Rute
                                </h3>
                            </div>
                            <div class="card-body p-1" style="max-height:620px; overflow:auto;">
                                <?php if (empty($routes)): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Belum ada SO siap loading
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($routes as $route):
                                        $active = (string)$route->kd_rute === (string)$selected_rute;
                                        $route_url = base_url('logistik/so_siap_loading?rute=' . rawurlencode($route->kd_rute));
                                    ?>
                                        <a href="<?= $route_url ?>" class="route-card <?= $active ? 'active' : '' ?>">
                                            <div class="route-code"><?= htmlspecialchars($route->kd_rute) ?></div>
                                            <div class="route-meta text-truncate" title="<?= htmlspecialchars($route->nama_rute) ?>">
                                                <?= htmlspecialchars($route->nama_rute) ?>
                                            </div>
                                            <div class="text-right">
                                                <div class="route-tonase"><?= number_format((float)$route->total_tonase, 3) ?> ton</div>
                                                <span class="badge badge-primary"><?= (int)$route->total_so ?> SO</span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="row summary-box">
                            <div class="col-md-4">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-file-invoice"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">SO Rute <?= htmlspecialchars($selected_rute ?: '-') ?></span>
                                        <span class="info-box-number"><?= number_format($total_so) ?> SO</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-success"><i class="fas fa-weight-hanging"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tonase Rute</span>
                                        <span class="info-box-number"><?= number_format($total_tonase, 3) ?> ton</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-info"><i class="fas fa-cubes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Kubikasi Rute</span>
                                        <span class="info-box-number"><?= number_format($total_kubikasi, 4) ?> m3</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title">
                                    <i class="fas fa-list mr-2"></i>SO Sedang Verifikasi - <?= htmlspecialchars($selected_route_name) ?>
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-light"><?= number_format($total_qty, 2) ?> qty</span>
                                    <span class="badge badge-warning ml-1"><?= number_format($total_outstanding, 2) ?> outstanding</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="tabelSoSiapLoading" class="table table-bordered table-striped table-hover table-sm">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width:40px">No</th>
                                            <th>No SO</th>
                                            <th>Tanggal SO</th>
                                            <th>Customer</th>
                                            <th class="text-center">Item</th>
                                            <th class="text-right">Qty</th>
                                            <th class="text-right">Outstanding</th>
                                            <th class="text-right">Tonase</th>
                                            <th class="text-right">Kubikasi</th>
                                            <th>Konfirmasi Sales</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($so_list)): ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted py-4">
                                                    Tidak ada Sales Order siap loading untuk rute ini.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($so_list as $so): ?>
                                                <tr>
                                                    <td class="text-center rownum"></td>
                                                    <td class="font-weight-bold">
                                                        <a href="<?= base_url('sales_order/detail/' . $so->id_so) ?>">
                                                            <?= htmlspecialchars($so->no_so) ?>
                                                        </a>
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <?= !empty($so->tanggal_transaksi) ? date('d/m/Y', strtotime($so->tanggal_transaksi)) : '-' ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($so->customer_name ?: ($so->nama_customer ?? '-')) ?>
                                                        <?php if (!empty($so->nama_kios)): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($so->nama_kios) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center"><?= number_format((int)$so->jumlah_item) ?></td>
                                                    <td class="text-right"><?= number_format((float)$so->total_qty_order, 2) ?></td>
                                                    <td class="text-right"><?= number_format((float)$so->total_qty_outstanding, 2) ?></td>
                                                    <td class="text-right"><?= number_format((float)$so->total_tonase, 3) ?> ton</td>
                                                    <td class="text-right"><?= number_format((float)$so->total_kubikasi, 4) ?> m3</td>
                                                    <td>
                                                        <?= !empty($so->update_by) ? htmlspecialchars($so->update_by) : '-' ?>
                                                        <?php if (!empty($so->update_at)): ?>
                                                            <br><small class="text-muted"><?= date('d/m/Y H:i', strtotime($so->update_at)) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <form method="post"
                                                              action="<?= base_url('logistik/so_siap_loading/kembalikan/' . $so->id_so) ?>"
                                                              onsubmit="return confirm('Kembalikan SO <?= htmlspecialchars($so->no_so, ENT_QUOTES, 'UTF-8') ?> ke status Open?');">
                                                            <input type="hidden" name="current_rute" value="<?= htmlspecialchars($selected_rute) ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Kembalikan ke Open">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
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
    var table = $('#tabelSoSiapLoading').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        columnDefs: [
            { orderable: false, targets: [0, 10] },
            { className: 'text-center', targets: [0, 4, 10] }
        ],
        order: [[2, 'desc']],
        drawCallback: function () {
            var api = this.api();
            api.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }
    });
});
</script>
