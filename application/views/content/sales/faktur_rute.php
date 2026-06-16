<!-- views/content/sales/faktur_rute.php -->
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
    .route-card .route-kubikasi {
        color: #0369a1;
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
    .route-strip {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 8px;
        white-space: nowrap;
    }
    .route-strip .route-card {
        flex: 0 0 300px;
        margin-bottom: 0;
        min-height: 48px;
    }
    .route-strip-empty {
        flex: 1 0 260px;
        padding: 8px 12px;
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
    #tabelFakturRute_wrapper {
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
                            <i class="fas fa-route mr-2"></i> Faktur Pengiriman Hari Ini per Rute
                        </h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                            <li class="breadcrumb-item active">Faktur Pengiriman Hari Ini</li>
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
        $total_faktur = count($fakturs);
        $pct_ton = $batas_tonase > 0 ? min(100, round(($total_tonase / $batas_tonase) * 100, 1)) : 0;
        $pct_kub = $batas_kubikasi > 0 ? min(100, round(($total_kubikasi / $batas_kubikasi) * 100, 1)) : 0;
        $ton_bar = $total_tonase > $batas_tonase ? 'danger' : ($pct_ton >= 80 ? 'warning' : 'success');
        $kub_bar = $total_kubikasi > $batas_kubikasi ? 'danger' : ($pct_kub >= 80 ? 'warning' : 'info');
        $today_label = !empty($today) ? date('d/m/Y', strtotime($today)) : date('d/m/Y');
        ?>

        <section class="content">
            <div class="container-fluid">
                <div class="mb-3">
                    <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke SO
                    </a>
                    <span class="badge badge-info ml-1">
                        <i class="fas fa-calendar-day mr-1"></i><?= $today_label ?>
                    </span>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-primary">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-map-marked-alt mr-1"></i> Rute Pengiriman Hari Ini
                                </h3>
                            </div>
                            <div class="card-body p-0 route-strip">
                                <?php if (empty($routes)): ?>
                                    <div class="route-strip-empty text-muted">
                                        <i class="fas fa-inbox mr-1"></i>
                                        Tidak ada rute pengiriman hari ini
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($routes as $r):
                                        $active = ($r['kd_rute'] === $selected_rute);
                                        $route_url = base_url('sales_order/faktur_rute?rute=' . rawurlencode($r['kd_rute']));
                                        $route_tonase = (float)($r['total_tonase'] ?? 0);
                                        $route_kubikasi = (float)($r['total_kubikasi'] ?? 0);
                                        $route_pct_ton = $batas_tonase > 0 ? min(100, round(($route_tonase / $batas_tonase) * 100, 1)) : 0;
                                        $route_pct_kub = $batas_kubikasi > 0 ? min(100, round(($route_kubikasi / $batas_kubikasi) * 100, 1)) : 0;
                                        $route_ton_color = $route_tonase > $batas_tonase ? '#dc3545' : ($route_pct_ton >= 80 ? '#f59e0b' : '#198754');
                                        $route_kub_color = $route_kubikasi > $batas_kubikasi ? '#dc3545' : ($route_pct_kub >= 80 ? '#f59e0b' : '#0ea5e9');
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
                                                <span class="route-metric">
                                                    <span class="route-kubikasi">
                                                        <?= number_format($route_kubikasi, 4) ?> m3
                                                    </span>
                                                    <span title="Kubikasi <?= number_format($route_kubikasi, 4) ?> / <?= number_format($batas_kubikasi, 0) ?> m3 (<?= number_format($route_pct_kub, 1) ?>%)">
                                                        <span class="route-ring" style="--ring-pct:<?= $route_pct_kub ?>%;--ring-color:<?= $route_kub_color ?>;"></span>
                                                    </span>
                                                </span>
                                                <span class="badge badge-primary"><?= (int)$r['total_faktur'] ?></span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row quota-box">
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-<?= $ton_bar ?>"><i class="fas fa-weight-hanging"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tonase</span>
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
                                        <span class="info-box-text">Kubikasi</span>
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
                                    Faktur Selesai DO Hari Ini - <?= htmlspecialchars($selected_route_name) ?>
                                </h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-hover table-sm" id="tabelFakturRute">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>No Faktur</th>
                                            <th>No DO</th>
                                            <th>No SO</th>
                                            <th>Tanggal Faktur</th>
                                            <th>On Delivery</th>
                                            <th>Customer</th>
                                            <th>Rute DO</th>
                                            <th class="text-center">Status DO</th>
                                            <th class="text-center">Barang</th>
                                            <th class="text-right">Qty</th>
                                            <th class="text-right">Tonase</th>
                                            <th class="text-right">Kubikasi</th>
                                            <th class="text-center no-sort">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($fakturs)): ?>
                                            <tr>
                                                <td colspan="13" class="text-center text-muted py-4">
                                                    Tidak ada faktur selesai DO dalam pengiriman hari ini untuk rute ini
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($fakturs as $f): ?>
                                                <?php
                                                $status_do = (string)($f['status_do'] ?? '');
                                                $status_do_label = $status_do === '5' ? 'On Delivery' : ($status_do === '3' ? 'Proses DO' : $status_do);
                                                $status_do_badge = $status_do === '5' ? 'success' : ($status_do === '3' ? 'info' : 'secondary');
                                                ?>
                                                <tr>
                                                    <td class="font-weight-bold">
                                                        <?= htmlspecialchars($f['no_faktur']) ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($f['kd_do'] ?? '-') ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($f['id_so'])): ?>
                                                            <a href="<?= base_url('sales_order/detail/' . $f['id_so']) ?>">
                                                                <?= htmlspecialchars($f['no_so']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($f['no_so']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <?= !empty($f['tanggal_faktur']) ? date('d/m/Y', strtotime($f['tanggal_faktur'])) : '-' ?>
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <?= !empty($f['tanggal_on_delivery']) ? date('d/m/Y H:i', strtotime($f['tanggal_on_delivery'])) : '-' ?>
                                                        <?php if (!empty($f['tgl_pengiriman'])): ?>
                                                            <br><small class="text-muted">Tgl kirim: <?= date('d/m/Y', strtotime($f['tgl_pengiriman'])) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($f['customer_name'] ?: '-') ?>
                                                        <?php if (!empty($f['nama_kios'])): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($f['nama_kios']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= !empty($f['kd_rute']) ? htmlspecialchars($f['kd_rute']) : '<span class="text-muted">-</span>' ?>
                                                        <?php if (!empty($f['nama_rute']) && $f['nama_rute'] !== $f['kd_rute']): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($f['nama_rute']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-<?= $status_do_badge ?>"><?= htmlspecialchars($status_do_label) ?></span>
                                                    </td>
                                                    <td class="text-center"><?= number_format((int)$f['total_barang']) ?></td>
                                                    <td class="text-right"><?= number_format((float)$f['total_qty'], 2) ?></td>
                                                    <td class="text-right"><?= number_format((float)$f['total_tonase'], 3) ?> ton</td>
                                                    <td class="text-right"><?= number_format((float)$f['total_kubikasi'], 4) ?> m3</td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('sales_order/detail_faktur/' . $f['id_faktur']) ?>"
                                                           class="btn btn-sm btn-info" title="Detail Faktur">
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
    $('#tabelFakturRute').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[4, 'desc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable: "Tidak ada faktur",
            paginate: { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });
});
</script>
