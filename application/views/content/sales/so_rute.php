<!-- views/content/sales/so_rute.php -->
<style>
    .route-card {
        align-items: center;
        color: #1f2937;
        display: flex;
        gap: 8px;
        justify-content: space-between;
        border: 1px solid #dee2e6;
        border-left: 3px solid #ced4da;
        border-radius: 4px;
        padding: 7px 8px;
        margin-bottom: 4px;
        background: #fff;
        text-decoration: none;
    }
    .route-card:hover {
        color: #1f2937;
        border-left-color: #007bff;
        background: #f8fbff;
        text-decoration: none;
    }
    .route-card.active {
        border-color: #007bff;
        border-left-color: #007bff;
        background: #eaf4ff;
    }
    .route-card .route-code {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.15;
        min-width: 42px;
    }
    .route-card .route-meta {
        flex: 1 1 auto;
        font-size: 10.5px;
        color: #6c757d;
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
        display: flex;
        align-items: center;
        gap: 3px;
    }
    .route-card .route-ring {
        --ring-pct: 0%;
        --ring-color: #198754;
        position: relative;
        display: grid;
        place-items: center;
        flex: 0 0 18px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: conic-gradient(var(--ring-color) var(--ring-pct), #e5e7eb 0);
    }
    .route-card .route-ring::before {
        content: "";
        position: absolute;
        inset: 3px;
        border-radius: 50%;
        background: #fff;
    }
    .route-card.active .route-ring::before {
        background: #eaf4ff;
    }
    .route-card .badge {
        font-size: 10px;
        padding: 2px 5px;
        line-height: 1.1;
    }
    .quota-box .progress {
        height: 18px;
        border-radius: 4px;
    }
    .quota-box .progress-bar {
        font-size: 11px;
        line-height: 18px;
    }
    .quota-box .info-box {
        align-items: center;
        min-height: 82px;
    }
    .quota-box .info-box-icon {
        flex: 0 0 64px;
        width: 64px;
        height: 64px;
        min-height: 64px;
        max-height: 64px;
        align-self: center;
        border-radius: 8px;
        margin-left: 8px;
        font-size: 28px;
    }
    .quota-box .info-box-icon > i {
        line-height: 64px;
    }
    #tabelSORute_wrapper {
        padding: 12px;
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
                    <div class="col-sm-7">
                        <h1 class="m-0">
                            <i class="fas fa-map-marked-alt mr-2"></i> Sales Order per Rute
                        </h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                            <li class="breadcrumb-item active">SO per Rute</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $selected_route_name = '-';
        foreach ($routes as $r) {
            if (($r['kd_rute'] ?? '') === $selected_rute) {
                $selected_route_name = $r['nama_rute'] ?: $r['kd_rute'];
                break;
            }
        }
        $total_so = count($sales_orders);
        $pct_ton = $batas_tonase > 0 ? min(100, round(($total_tonase / $batas_tonase) * 100, 1)) : 0;
        $pct_kub = $batas_kubikasi > 0 ? min(100, round(($total_kubikasi / $batas_kubikasi) * 100, 1)) : 0;
        $ton_bar = $total_tonase > $batas_tonase ? 'danger' : ($pct_ton >= 80 ? 'warning' : 'success');
        $kub_bar = $total_kubikasi > $batas_kubikasi ? 'danger' : ($pct_kub >= 80 ? 'warning' : 'info');
        $badge_map = [
            'draft'     => 'secondary',
            'open'      => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];
        $label_map = [
            'draft'     => 'Draft',
            'open'      => 'Open',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
        ?>

        <section class="content">
            <div class="container-fluid">
                <div class="mb-3">
                    <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke SO
                    </a>
                    <a href="<?= base_url('sales_order/faktur_rute') ?>" class="btn btn-success btn-sm ml-1">
                        <i class="fas fa-route mr-1"></i> Faktur per Rute
                    </a>
                </div>

                <div class="row">
                    <div class="col-lg-3">
                        <div class="card card-outline card-primary">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-map-marked-alt mr-1"></i> Pilih Rute
                                </h3>
                            </div>
                            <div class="card-body p-1" style="max-height:620px; overflow:auto;">
                                <?php if (empty($routes)): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Tidak ada master rute
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($routes as $r):
                                        $active = ($r['kd_rute'] === $selected_rute);
                                        $route_url = base_url('sales_order/so_rute?rute=' . rawurlencode($r['kd_rute']));
                                        $route_tonase = (float)($r['total_tonase'] ?? 0);
                                        $route_pct_ton = $batas_tonase > 0 ? min(100, round(($route_tonase / $batas_tonase) * 100, 1)) : 0;
                                        $route_ton_color = $route_tonase > $batas_tonase ? '#dc3545' : ($route_pct_ton >= 80 ? '#f59e0b' : '#198754');
                                    ?>
                                        <a href="<?= $route_url ?>" class="route-card <?= $active ? 'active' : '' ?>">
                                            <div class="route-code"><?= htmlspecialchars($r['kd_rute']) ?></div>
                                            <div class="route-meta text-truncate" title="<?= htmlspecialchars($r['nama_rute']) ?>">
                                                <?= htmlspecialchars($r['nama_rute']) ?>
                                            </div>
                                            <div class="route-summary">
                                                <span class="route-metric">
                                                    <span class="route-tonase">
                                                        <?= number_format($route_tonase, 3) ?> ton
                                                    </span>
                                                    <span title="Tonase <?= number_format($route_tonase, 3) ?> / <?= number_format($batas_tonase, 0) ?> ton (<?= number_format($route_pct_ton, 1) ?>%)">
                                                        <span class="route-ring" style="--ring-pct:<?= $route_pct_ton ?>%;--ring-color:<?= $route_ton_color ?>;"></span>
                                                    </span>
                                                </span>
                                                <span class="badge badge-primary"><?= (int)$r['total_so'] ?></span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="row quota-box">
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-<?= $ton_bar ?>"><i class="fas fa-weight-hanging"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tonase SO</span>
                                        <span class="info-box-number">
                                            <?= number_format($total_tonase, 3) ?> / <?= number_format($batas_tonase, 0) ?> ton
                                        </span>
                                        <div class="progress mt-1">
                                            <div class="progress-bar bg-<?= $ton_bar ?>" style="width:<?= $pct_ton ?>%">
                                                <?= $pct_ton ?>%
                                            </div>
                                        </div>
                                        <small class="<?= $sisa_tonase < 0 ? 'text-danger' : 'text-muted' ?>">
                                            Sisa: <?= number_format($sisa_tonase, 3) ?> ton
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-<?= $kub_bar ?>"><i class="fas fa-cubes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Kubikasi SO</span>
                                        <span class="info-box-number">
                                            <?= number_format($total_kubikasi, 4) ?> / <?= number_format($batas_kubikasi, 0) ?> m3
                                        </span>
                                        <div class="progress mt-1">
                                            <div class="progress-bar bg-<?= $kub_bar ?>" style="width:<?= $pct_kub ?>%">
                                                <?= $pct_kub ?>%
                                            </div>
                                        </div>
                                        <small class="<?= $sisa_kubikasi < 0 ? 'text-danger' : 'text-muted' ?>">
                                            Sisa: <?= number_format($sisa_kubikasi, 4) ?> m3
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title">
                                    <i class="fas fa-list mr-2"></i>
                                    Sales Order - <?= htmlspecialchars($selected_route_name) ?>
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-light"><?= number_format($total_so) ?> SO</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <div class="small text-muted">Total Qty Order</div>
                                        <div class="font-weight-bold"><?= number_format($total_qty_order, 2) ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="small text-muted">Total Qty Faktur</div>
                                        <div class="font-weight-bold text-success"><?= number_format($total_qty_faktur, 2) ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="small text-muted">Outstanding</div>
                                        <div class="font-weight-bold <?= $total_qty_outstanding > 0 ? 'text-danger' : 'text-muted' ?>">
                                            <?= number_format($total_qty_outstanding, 2) ?>
                                        </div>
                                    </div>
                                </div>

                                <table class="table table-bordered table-hover table-sm" id="tabelSORute">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>No SO</th>
                                            <th>Tanggal</th>
                                            <th>Customer</th>
                                            <th>Regional</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Item</th>
                                            <th class="text-right">Qty Order</th>
                                            <th class="text-right">Qty Faktur</th>
                                            <th class="text-right">Outstanding</th>
                                            <th class="text-right">Tonase</th>
                                            <th class="text-right">Kubikasi</th>
                                            <th class="text-center">Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($sales_orders)): ?>
                                            <tr>
                                                <td colspan="12" class="text-center text-muted py-4">
                                                    Tidak ada Sales Order untuk rute ini
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($sales_orders as $so):
                                                $status = $so['status'] ?? '';
                                                $badge = $badge_map[$status] ?? 'secondary';
                                                $label = $label_map[$status] ?? $status;
                                                $qty_order = (float)($so['total_qty_order'] ?? 0);
                                                $qty_faktur = (float)($so['total_qty_faktur'] ?? 0);
                                                $qty_outstanding = (float)($so['total_qty_outstanding'] ?? 0);
                                                $pct = $qty_order > 0 ? min(100, round(($qty_faktur / $qty_order) * 100, 1)) : 0;
                                                $bar_color = $status === 'completed' || $pct >= 100
                                                    ? 'success'
                                                    : ($status === 'cancelled' ? 'danger' : ($pct > 0 ? 'warning' : 'secondary'));
                                            ?>
                                                <tr>
                                                    <td class="font-weight-bold">
                                                        <a href="<?= base_url('sales_order/detail/' . $so['id_so']) ?>">
                                                            <?= htmlspecialchars($so['no_so']) ?>
                                                        </a>
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <?= !empty($so['tanggal_transaksi']) ? date('d/m/Y', strtotime($so['tanggal_transaksi'])) : '-' ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($so['customer_name'] ?: ($so['nama_customer'] ?? '-')) ?>
                                                        <?php if (!empty($so['nama_kios'])): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($so['nama_kios']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= !empty($so['regional']) ? htmlspecialchars($so['regional']) : '<span class="text-muted">-</span>' ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-<?= $badge ?> px-2 py-1"><?= htmlspecialchars($label) ?></span>
                                                    </td>
                                                    <td class="text-center"><?= number_format((int)$so['jumlah_item']) ?></td>
                                                    <td class="text-right"><?= number_format($qty_order, 2) ?></td>
                                                    <td class="text-right text-success font-weight-bold"><?= number_format($qty_faktur, 2) ?></td>
                                                    <td class="text-right <?= $qty_outstanding > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                                        <?= number_format($qty_outstanding, 2) ?>
                                                    </td>
                                                    <td class="text-right"><?= number_format((float)$so['total_tonase'], 3) ?> ton</td>
                                                    <td class="text-right"><?= number_format((float)$so['total_kubikasi'], 4) ?> m3</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="progress flex-grow-1 mr-1" style="height:16px; border-radius:3px;">
                                                                <div class="progress-bar bg-<?= $bar_color ?>"
                                                                     style="width:<?= $pct ?>%; font-size:10px; line-height:16px;">
                                                                    <?= $pct > 15 ? $pct . '%' : '' ?>
                                                                </div>
                                                            </div>
                                                            <small class="text-nowrap font-weight-bold text-<?= $bar_color === 'secondary' ? 'muted' : $bar_color ?>" style="min-width:38px;">
                                                                <?= $pct ?>%
                                                            </small>
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
    $('#tabelSORute').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[1, 'desc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable: "Tidak ada Sales Order",
            paginate: { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });
});
</script>
