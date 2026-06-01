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
    .route-card .route-code {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.15;
        min-width: 42px;
    }
    .route-card .route-meta {
        color: #6c757d;
        flex: 1 1 auto;
        font-size: 10.5px;
        line-height: 1.2;
        min-width: 0;
    }
    .route-card .route-summary {
        align-items: center;
        display: flex;
        flex: 0 0 auto;
        gap: 7px;
    }
    .route-card .route-tonase {
        color: #15803d;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.15;
    }
    .route-card .route-metric {
        align-items: center;
        display: flex;
        gap: 3px;
    }
    .route-card .route-ring {
        --ring-pct: 0%;
        --ring-color: #198754;
        background: conic-gradient(var(--ring-color) var(--ring-pct), #e5e7eb 0);
        border-radius: 50%;
        display: grid;
        flex: 0 0 18px;
        height: 18px;
        place-items: center;
        position: relative;
        width: 18px;
    }
    .route-card .route-ring::before {
        background: #fff;
        border-radius: 50%;
        content: "";
        inset: 3px;
        position: absolute;
    }
    .route-card.active .route-ring::before {
        background: #eaf4ff;
    }
    .route-card .badge {
        font-size: 10px;
        line-height: 1.1;
        padding: 2px 5px;
    }
    .route-card .route-summary .badge {
        white-space: nowrap;
    }
    #tabelSoSiapLoading_wrapper {
        padding: 12px;
    }
    .quota-card .progress {
        height: 14px;
        border-radius: 7px;
    }
    .quota-card .progress-bar {
        font-size: 11px;
        line-height: 14px;
    }
    .quota-card .quota-info {
        white-space: normal;
    }
    .so-action-group {
        display: inline-flex;
        gap: 4px;
        justify-content: center;
        white-space: nowrap;
    }
    .so-action-group form {
        display: inline-block;
        margin: 0;
    }
    .so-action-group .btn {
        align-items: center;
        display: inline-flex;
        height: 30px;
        justify-content: center;
        padding: 0;
        width: 30px;
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
    $total_so_verified = 0;
    foreach ($so_list as $so) {
        $total_tonase += (float)($so->total_tonase ?? 0);
        $total_kubikasi += (float)($so->total_kubikasi ?? 0);
        $total_qty += (float)($so->total_qty_order ?? 0);
        $total_outstanding += (float)($so->total_qty_outstanding ?? 0);
        if ((int)($so->jumlah_item ?? 0) > 0 && (int)($so->jumlah_item_terverifikasi ?? 0) >= (int)$so->jumlah_item) {
            $total_so_verified++;
        }
    }

    $batas_ton = 6;
    $batas_kub = 9;
    $pct_ton = $batas_ton > 0 ? min(($total_tonase / $batas_ton) * 100, 100) : 0;
    $pct_kub = $batas_kub > 0 ? min(($total_kubikasi / $batas_kub) * 100, 100) : 0;
    $color_ton = $total_tonase > $batas_ton ? 'danger' : 'success';
    $color_kub = $total_kubikasi > $batas_kub ? 'danger' : 'info';
    $sisa_ton = max(0, $batas_ton - $total_tonase);
    $sisa_kub = max(0, $batas_kub - $total_kubikasi);
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
                    <a href="<?= base_url('logistik/so_siap_loading/tambah') ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i>Tambah SO
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
                                        $route_tonase = (float)($route->total_tonase ?? 0);
                                        $route_pct_ton = $batas_ton > 0 ? min(100, round(($route_tonase / $batas_ton) * 100, 1)) : 0;
                                        $route_ton_color = $route_tonase > $batas_ton ? '#dc3545' : ($route_pct_ton >= 80 ? '#f59e0b' : '#198754');
                                    ?>
                                        <a href="<?= $route_url ?>" class="route-card <?= $active ? 'active' : '' ?>">
                                            <div class="route-code"><?= htmlspecialchars($route->kd_rute) ?></div>
                                            <div class="route-meta text-truncate" title="<?= htmlspecialchars($route->nama_rute) ?>">
                                                <?= htmlspecialchars($route->nama_rute) ?>
                                            </div>
                                            <div class="route-summary">
                                                <span class="route-metric">
                                                    <span class="route-tonase">
                                                        <?= number_format($route_tonase, 3) ?> ton
                                                    </span>
                                                    <span title="Tonase <?= number_format($route_tonase, 3) ?> / <?= number_format($batas_ton, 0) ?> ton (<?= number_format($route_pct_ton, 1) ?>%)">
                                                        <span class="route-ring" style="--ring-pct:<?= $route_pct_ton ?>%;--ring-color:<?= $route_ton_color ?>;"></span>
                                                    </span>
                                                </span>
                                                <span class="badge badge-primary"><?= (int)$route->total_so ?></span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="row mt-0 mb-3">
                            <div class="col-md-6">
                                <div class="card card-outline card-<?= $color_ton ?> quota-card mb-0">
                                    <div class="card-header py-2">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-weight mr-1"></i> Tonase
                                            <?php if ($total_tonase > $batas_ton): ?>
                                                <span class="badge badge-danger ml-1">Melebihi!</span>
                                            <?php endif; ?>
                                        </h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-<?= $color_ton ?> progress-bar-striped"
                                                 role="progressbar"
                                                 style="width: <?= number_format($pct_ton, 2) ?>%"
                                                 title="<?= number_format($pct_ton, 1) ?>%">
                                                <?php if ($pct_ton >= 20): ?>
                                                    <?= number_format($pct_ton, 1) ?>%
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="row text-center small quota-info">
                                            <div class="col-4">
                                                <div class="text-muted">Terpakai</div>
                                                <div class="font-weight-bold text-<?= $color_ton ?>">
                                                    <?= number_format($total_tonase, 3) ?> ton
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted">Batas</div>
                                                <div class="font-weight-bold">
                                                    <?= number_format($batas_ton, 1) ?> ton
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted">Sisa</div>
                                                <div class="font-weight-bold text-<?= $sisa_ton > 0 ? 'success' : 'danger' ?>">
                                                    <?= $sisa_ton > 0
                                                        ? number_format($sisa_ton, 3) . ' ton'
                                                        : '<i class="fas fa-exclamation-triangle"></i> Penuh' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-outline card-<?= $color_kub ?> quota-card mb-0">
                                    <div class="card-header py-2">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-cube mr-1"></i> Kubikasi
                                            <?php if ($total_kubikasi > $batas_kub): ?>
                                                <span class="badge badge-danger ml-1">Melebihi!</span>
                                            <?php endif; ?>
                                        </h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-<?= $color_kub ?> progress-bar-striped"
                                                 role="progressbar"
                                                 style="width: <?= number_format($pct_kub, 2) ?>%"
                                                 title="<?= number_format($pct_kub, 1) ?>%">
                                                <?php if ($pct_kub >= 20): ?>
                                                    <?= number_format($pct_kub, 1) ?>%
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="row text-center small quota-info">
                                            <div class="col-4">
                                                <div class="text-muted">Terpakai</div>
                                                <div class="font-weight-bold text-<?= $color_kub ?>">
                                                    <?= number_format($total_kubikasi, 4) ?> m3
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted">Batas</div>
                                                <div class="font-weight-bold">
                                                    <?= number_format($batas_kub, 1) ?> m3
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-muted">Sisa</div>
                                                <div class="font-weight-bold text-<?= $sisa_kub > 0 ? 'success' : 'danger' ?>">
                                                    <?= $sisa_kub > 0
                                                        ? number_format($sisa_kub, 4) . ' m3'
                                                        : '<i class="fas fa-exclamation-triangle"></i> Penuh' ?>
                                                </div>
                                            </div>
                                        </div>
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
                                    <span class="badge badge-light"><?= number_format($total_so) ?> SO</span>
                                    <span class="badge badge-success ml-1"><?= number_format($total_so_verified) ?> terverifikasi</span>
                                    <span class="badge badge-light"><?= number_format($total_qty, 2) ?> qty</span>
                                    <span class="badge badge-warning ml-1"><?= number_format($total_outstanding, 2) ?> outstanding</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($selected_rute) && !empty($so_list)): ?>
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                                        <div class="small text-muted">
                                            Verifikasi barang selesai:
                                            <strong><?= number_format($total_so_verified) ?></strong> dari
                                            <strong><?= number_format($total_so) ?></strong> SO.
                                        </div>
                                        <form method="post"
                                              action="<?= base_url('logistik/so_siap_loading/siap_faktur') ?>"
                                              onsubmit="return confirm('Ubah semua SO rute <?= htmlspecialchars($selected_rute, ENT_QUOTES, 'UTF-8') ?> menjadi siap difakturkan?');">
                                            <input type="hidden" name="current_rute" value="<?= htmlspecialchars($selected_rute) ?>">
                                            <button type="submit"
                                                    class="btn btn-sm btn-success"
                                                    <?= ($total_so === 0 || $total_so_verified < $total_so) ? 'disabled' : '' ?>>
                                                <i class="fas fa-file-invoice-dollar mr-1"></i> Jadikan Siap Faktur
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                <table id="tabelSoSiapLoading" class="table table-bordered table-striped table-hover table-sm">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width:40px">No</th>
                                            <th>No SO</th>
                                            <th>Tanggal SO</th>
                                            <th>Customer</th>
                                            <th>Regional</th>
                                            <th class="text-center">Item</th>
                                            <th class="text-right">Qty</th>
                                            <th class="text-right">Outstanding</th>
                                            <th class="text-center">Verifikasi</th>
                                            <th class="text-right">Tonase</th>
                                            <th class="text-right">Kubikasi</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($so_list)): ?>
                                            <tr>
                                                <td colspan="12" class="text-center text-muted py-4">
                                                    Tidak ada Sales Order siap loading untuk rute ini.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($so_list as $so): ?>
                                                <tr>
                                                    <td class="text-center rownum"></td>
                                                    <td class="font-weight-bold">
                                                        <?= htmlspecialchars($so->no_so) ?>
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
                                                    <td>
                                                        <?= !empty($so->regional) ? htmlspecialchars($so->regional) : '<span class="text-muted">-</span>' ?>
                                                    </td>
                                                    <td class="text-center"><?= number_format((int)$so->jumlah_item) ?></td>
                                                    <td class="text-right"><?= number_format((float)$so->total_qty_order, 2) ?></td>
                                                    <td class="text-right"><?= number_format((float)$so->total_qty_outstanding, 2) ?></td>
                                                    <td class="text-center">
                                                        <?php
                                                        $is_verified = (int)$so->jumlah_item > 0 && (int)$so->jumlah_item_terverifikasi >= (int)$so->jumlah_item;
                                                        ?>
                                                        <span class="badge badge-<?= $is_verified ? 'success' : 'warning' ?>">
                                                            <?= number_format((int)$so->jumlah_item_terverifikasi) ?>/<?= number_format((int)$so->jumlah_item) ?>
                                                        </span>
                                                        <?php if ((float)($so->total_qty_tidak_terkirim ?? 0) > 0): ?>
                                                            <br><small class="text-danger"><?= number_format((float)$so->total_qty_tidak_terkirim, 2) ?> tidak terkirim</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-right"><?= number_format((float)$so->total_tonase, 3) ?> ton</td>
                                                    <td class="text-right"><?= number_format((float)$so->total_kubikasi, 4) ?> m3</td>
                                                    <td class="text-center">
                                                        <div class="so-action-group" role="group">
                                                            <a href="<?= base_url('logistik/so_siap_loading/verifikasi/' . $so->id_so) ?>"
                                                               class="btn btn-sm btn-info"
                                                               title="Detail dan Verifikasi Barang">
                                                                <i class="fas fa-clipboard-list"></i>
                                                            </a>
                                                            <form method="post"
                                                                  action="<?= base_url('logistik/so_siap_loading/kembalikan/' . $so->id_so) ?>"
                                                                  onsubmit="return confirm('Kembalikan SO <?= htmlspecialchars($so->no_so, ENT_QUOTES, 'UTF-8') ?> ke status Open?');">
                                                                <input type="hidden" name="current_rute" value="<?= htmlspecialchars($selected_rute) ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Kembalikan ke Open">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
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
            { orderable: false, targets: [0, 11] },
            { className: 'text-center', targets: [0, 5, 8, 11] }
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
