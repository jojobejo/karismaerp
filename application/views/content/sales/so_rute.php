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
    .route-strip {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 8px;
        white-space: nowrap;
    }
    .route-strip .route-card {
        flex: 0 0 245px;
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
    #tabelSORute_wrapper {
        padding: 12px;
    }
    .route-bulk-bar {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: flex-end;
    }
    .route-bulk-bar select {
        max-width: 120px;
    }
    .route-move-bar {
        flex-wrap: nowrap;
    }
    .route-move-bar select {
        flex: 0 0 130px;
        max-width: 130px;
    }
    .route-move-bar .btn {
        white-space: nowrap;
    }
    .route-toolbar {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .route-filter-bar {
        justify-content: flex-start;
        margin-bottom: 0;
    }
    .route-filter-bar select {
        max-width: 180px;
    }
    .route-select-cell {
        width: 34px;
    }
    .route-action-cell {
        width: 54px;
    }
    .route-status-badge {
        display: inline-flex;
        justify-content: center;
        min-width: 104px;
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
                            <i class="fas fa-map-marked-alt mr-2"></i> Penentuan Rute Sales Order
                        </h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                            <li class="breadcrumb-item active">Penentuan Rute SO</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $selected_route_name = '-';
        $is_all_so_mode = !empty($is_all_so_mode) || empty($selected_rute);
        foreach ($routes as $r) {
            if (($r['kd_rute'] ?? '') === $selected_rute) {
                $selected_route_name = $r['nama_rute'] ?: $r['kd_rute'];
                break;
            }
        }
        if ($is_all_so_mode) {
            $selected_route_name = 'Semua SO Open/Partial';
        }
        $selected_customer_rute = trim((string)($selected_customer_rute ?? ''));
        $customer_route_options = $customer_route_options ?? [];
        if ($is_all_so_mode && $selected_customer_rute !== '') {
            $selected_route_name .= ' - Rute Customer ' . $selected_customer_rute;
        }
        $total_so = count($sales_orders);
        $all_so_count = isset($all_so_count) ? (int)$all_so_count : $total_so;
        $loading_routes = array_values(array_filter($routes, function ($route) {
            return (int)($route['total_so'] ?? 0) > 0;
        }));
        $pct_ton = $batas_tonase > 0 ? min(100, round(($total_tonase / $batas_tonase) * 100, 1)) : 0;
        $pct_kub = $batas_kubikasi > 0 ? min(100, round(($total_kubikasi / $batas_kubikasi) * 100, 1)) : 0;
        $ton_bar = $total_tonase > $batas_tonase ? 'danger' : ($pct_ton >= 80 ? 'warning' : 'success');
        $kub_bar = $total_kubikasi > $batas_kubikasi ? 'danger' : ($pct_kub >= 80 ? 'warning' : 'info');
        $badge_map = [
            'draft'              => 'secondary',
            'open'               => 'primary',
            'sedang_verifikasi'  => 'warning',
            'siap_faktur'        => 'info',
            'partial'            => 'warning',
            'completed'          => 'success',
            'cancelled'          => 'danger',
        ];
        $label_map = [
            'draft'              => 'Draft',
            'open'               => 'Open',
            'sedang_verifikasi'  => 'Verifikasi',
            'siap_faktur'        => 'Siap Faktur',
            'partial'            => 'Partial',
            'completed'          => 'Completed',
            'cancelled'          => 'Cancelled',
        ];
        ?>

        <section class="content">
            <div class="container-fluid">
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="mb-3">
                    <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke SO
                    </a>
                    <?php if (!empty($selected_rute) && !empty($sales_orders)): ?>
                    <button type="button"
                            class="btn btn-success btn-sm ml-1"
                            id="btnConfirmSoRuteLoading"
                            data-rute="<?= htmlspecialchars($selected_rute, ENT_QUOTES, 'UTF-8') ?>"
                            <?= ($total_tonase > $batas_tonase || $total_kubikasi > $batas_kubikasi) ? 'disabled title="Tonase atau kubikasi melebihi batas maksimal"' : '' ?>>
                        <i class="fas fa-check-circle mr-1"></i> Konfirmasi Siap Loading
                    </button>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card card-outline card-primary">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-map-marked-alt mr-1"></i> Rute Loading
                                </h3>
                            </div>
                            <div class="card-body p-0 route-strip">
                                <a href="<?= base_url('sales_order/so_rute') ?>" class="route-card <?= $is_all_so_mode ? 'active' : '' ?>">
                                    <div class="route-code"><i class="fas fa-list"></i></div>
                                    <div class="route-meta text-truncate" title="Semua SO Open/Partial">
                                        Semua SO Open/Partial
                                    </div>
                                    <div class="route-summary">
                                        <span class="badge badge-dark"><?= (int)$all_so_count ?></span>
                                    </div>
                                </a>
                                <?php if (empty($loading_routes)): ?>
                                    <div class="route-strip-empty text-muted">
                                        <i class="fas fa-route mr-1"></i>
                                        Belum ada rute loading
                                        <small class="ml-1">Pilih SO lalu tetapkan rutenya.</small>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($loading_routes as $r):
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

                    <div class="col-12">
                        <div class="row quota-box">
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-<?= $ton_bar ?>"><i class="fas fa-weight-hanging"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text"><?= $is_all_so_mode ? 'Tonase Semua SO Open/Partial' : 'Tonase Rute' ?></span>
                                        <span class="info-box-number">
                                            <?= number_format($total_tonase, 3) ?><?= $is_all_so_mode ? '' : ' / ' . number_format($batas_tonase, 0) ?> ton
                                        </span>
                                        <?php if ($is_all_so_mode): ?>
                                            <small class="text-muted">Pilih SO lalu tetapkan rute untuk menghitung kapasitas loading.</small>
                                        <?php else: ?>
                                        <div class="progress mt-1">
                                            <div class="progress-bar bg-<?= $ton_bar ?>" style="width:<?= $pct_ton ?>%">
                                                <?= $pct_ton ?>%
                                            </div>
                                        </div>
                                        <small class="<?= $sisa_tonase < 0 ? 'text-danger' : 'text-muted' ?>">
                                            Sisa: <?= number_format($sisa_tonase, 3) ?> ton
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-<?= $kub_bar ?>"><i class="fas fa-cubes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text"><?= $is_all_so_mode ? 'Kubikasi Semua SO Open/Partial' : 'Kubikasi Rute' ?></span>
                                        <span class="info-box-number">
                                            <?= number_format($total_kubikasi, 4) ?><?= $is_all_so_mode ? '' : ' / ' . number_format($batas_kubikasi, 0) ?> m3
                                        </span>
                                        <?php if ($is_all_so_mode): ?>
                                            <small class="text-muted">Buka rute untuk melihat sisa kubikasi.</small>
                                        <?php else: ?>
                                        <div class="progress mt-1">
                                            <div class="progress-bar bg-<?= $kub_bar ?>" style="width:<?= $pct_kub ?>%">
                                                <?= $pct_kub ?>%
                                            </div>
                                        </div>
                                        <small class="<?= $sisa_kubikasi < 0 ? 'text-danger' : 'text-muted' ?>">
                                            Sisa: <?= number_format($sisa_kubikasi, 4) ?> m3
                                        </small>
                                        <?php endif; ?>
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
                                <div class="route-toolbar">
                                    <div>
                                        <?php if ($is_all_so_mode): ?>
                                        <form method="get" action="<?= base_url('sales_order/so_rute') ?>" class="route-bulk-bar route-filter-bar">
                                            <select name="customer_rute" class="form-control form-control-sm">
                                                <option value="">Semua rute customer</option>
                                                <?php foreach ($customer_route_options as $route_option): ?>
                                                    <option value="<?= htmlspecialchars($route_option['kd_rute']) ?>"
                                                        <?= ($route_option['kd_rute'] === $selected_customer_rute) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($route_option['kd_rute']) ?> (<?= (int)$route_option['total_so'] ?> SO)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-info btn-sm">
                                                <i class="fas fa-filter mr-1"></i> Filter
                                            </button>
                                            <?php if ($selected_customer_rute !== ''): ?>
                                                <a href="<?= base_url('sales_order/so_rute') ?>" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-times mr-1"></i> Reset
                                                </a>
                                            <?php endif; ?>
                                            <span class="small text-muted">
                                                Tonase: <b><?= number_format($total_tonase, 3) ?> ton</b>,
                                                Kubikasi: <b><?= number_format($total_kubikasi, 4) ?> m3</b>
                                            </span>
                                        </form>
                                        <?php endif; ?>
                                    </div>

                                    <form id="bulkMoveForm"
                                          method="post"
                                          action="<?= base_url('sales_order/bulk_update_so_rute') ?>"
                                          class="route-bulk-bar route-move-bar">
                                        <input type="hidden" name="current_rute" value="<?= htmlspecialchars($selected_rute) ?>">
                                        <select name="kd_rute" class="form-control form-control-sm" required>
                                            <option value="">Pilih rute</option>
                                            <?php foreach ($routes as $route_option): ?>
                                                <option value="<?= htmlspecialchars($route_option['kd_rute']) ?>"
                                                    <?= ($route_option['kd_rute'] === $selected_rute) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($route_option['kd_rute']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm" id="btnBulkMove" disabled>
                                            <i class="fas fa-exchange-alt mr-1"></i> <?= $is_all_so_mode ? 'Tetapkan Rute' : 'Pindahkan' ?>
                                        </button>
                                    </form>
                                </div>

                                <table class="table table-bordered table-hover table-sm" id="tabelSORute">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="text-center route-select-cell">
                                                <input type="checkbox" id="checkAllSo">
                                            </th>
                                            <th>No SO</th>
                                            <th>Tanggal</th>
                                            <th>Customer</th>
                                            <th>Rute SO</th>
                                            <th>Regional</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Item</th>
                                            <th class="text-right">Qty Order</th>
                                            <th class="text-right">Qty Faktur</th>
                                            <th class="text-right">Outstanding</th>
                                            <th class="text-center">Progress</th>
                                            <th class="text-center route-action-cell">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($sales_orders)): ?>
                                            <tr>
                                                <td colspan="13" class="text-center text-muted py-4">
                                                    <?= $is_all_so_mode ? 'Tidak ada Sales Order Open/Partial atau sisa verifikasi' : 'Tidak ada Sales Order untuk rute ini' ?>
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
                                                $qty_tidak_terkirim = (float)($so['total_qty_tidak_terkirim'] ?? 0);
                                                $logistik_note = trim((string)($so['verifikasi_loading_notes'] ?? ''));
                                                $effective_rute = $so['kd_rute'] ?? '';
                                                $customer_rute = $so['customer_kd_rute'] ?? '';
                                                $pct = $qty_order > 0 ? min(100, round(($qty_faktur / $qty_order) * 100, 1)) : 0;
                                                $bar_color = $status === 'completed' || $pct >= 100
                                                    ? 'success'
                                                    : ($status === 'cancelled' ? 'danger' : ($pct > 0 ? 'warning' : 'secondary'));
                                            ?>
                                                <tr>
                                                    <td class="text-center route-select-cell">
                                                        <?php if (in_array($status, ['open', 'partial'], true)): ?>
                                                            <input type="checkbox"
                                                                   class="check-so-route"
                                                                   name="id_so[]"
                                                                   value="<?= (int)$so['id_so'] ?>"
                                                                   form="bulkMoveForm">
                                                        <?php else: ?>
                                                            <span class="text-muted" title="SO ini hanya ditampilkan sebagai informasi sisa barang yang tidak ikut difakturkan.">-</span>
                                                        <?php endif; ?>
                                                    </td>
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
                                                        <?php if (!empty($effective_rute)): ?>
                                                            <span class="badge badge-info"><?= htmlspecialchars($effective_rute) ?></span>
                                                            <?php if (!empty($so['nama_rute']) && $so['nama_rute'] !== $effective_rute): ?>
                                                                <br><small class="text-muted"><?= htmlspecialchars($so['nama_rute']) ?></small>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Belum ditentukan</span>
                                                            <?php if (!empty($customer_rute)): ?>
                                                                <br><small class="text-muted">Rute Customer: <?= htmlspecialchars($customer_rute) ?></small>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= !empty($so['regional']) ? htmlspecialchars($so['regional']) : '<span class="text-muted">-</span>' ?>
                                                        <?php if (!$is_all_so_mode && $customer_rute !== '' && $customer_rute !== $effective_rute): ?>
                                                            <br><small class="text-muted">Master: <?= htmlspecialchars($customer_rute) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-<?= $badge ?> <?= ($status === 'partial' || $badge === 'warning') ? 'text-white' : '' ?> route-status-badge px-2 py-1"><?= htmlspecialchars($label) ?></span>
                                                        <?php if ($qty_tidak_terkirim > 0): ?>
                                                            <br><small class="text-danger font-weight-bold">
                                                                <?= number_format($qty_tidak_terkirim, 2) ?> tidak ikut faktur
                                                            </small>
                                                        <?php endif; ?>
                                                        <?php if ($logistik_note !== ''): ?>
                                                            <div class="small text-left mt-1 p-1 border rounded bg-light" style="white-space:normal; min-width:160px;">
                                                                <i class="fas fa-sticky-note text-warning mr-1"></i>
                                                                <?= nl2br(htmlspecialchars($logistik_note, ENT_QUOTES, 'UTF-8')) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center"><?= number_format((int)$so['jumlah_item']) ?></td>
                                                    <td class="text-right"><?= number_format($qty_order, 2) ?></td>
                                                    <td class="text-right text-success font-weight-bold"><?= number_format($qty_faktur, 2) ?></td>
                                                    <td class="text-right <?= $qty_outstanding > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                                        <?= number_format($qty_outstanding, 2) ?>
                                                    </td>
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
                                                    <td class="text-center route-action-cell">
                                                        <?php if (in_array($status, ['open', 'partial'], true) && !empty($effective_rute)): ?>
                                                            <form method="post"
                                                                  action="<?= base_url('sales_order/reset_so_rute') ?>"
                                                                  class="d-inline resetRouteForm">
                                                                <input type="hidden" name="id_so" value="<?= (int)$so['id_so'] ?>">
                                                                <input type="hidden" name="current_rute" value="<?= htmlspecialchars($selected_rute, ENT_QUOTES, 'UTF-8') ?>">
                                                                <button type="submit"
                                                                        class="btn btn-outline-danger btn-xs"
                                                                        title="Kembalikan ke Semua SO Open/Partial">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
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
    function salesToast(type, message) {
        if (window.Swal) {
            Swal.fire({ toast:true, position:'top-end', icon:type || 'info', title:message || '', timer:2600, showConfirmButton:false });
        } else {
            alert(message || '');
        }
    }

    function setButtonLoading(button, loading, text) {
        if (!button) return;
        var $btn = $(button);
        if (loading) {
            if (!$btn.data('original-html')) $btn.data('original-html', $btn.html());
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>' + (text || 'Memproses'));
        } else {
            $btn.prop('disabled', false).html($btn.data('original-html'));
        }
    }

    var tableSoRute = $('#tabelSORute').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[2, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 12] }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable: "Tidak ada Sales Order",
            paginate: { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });

    function updateBulkMoveState() {
        var checkedCount = $('.check-so-route:checked').length;
        $('#btnBulkMove').prop('disabled', checkedCount === 0);

        var visibleBoxes = $('.check-so-route:visible');
        var visibleChecked = $('.check-so-route:visible:checked');
        $('#checkAllSo').prop('checked', visibleBoxes.length > 0 && visibleBoxes.length === visibleChecked.length);
    }

    $('#checkAllSo').on('change', function () {
        $('.check-so-route:visible').prop('checked', this.checked);
        updateBulkMoveState();
    });

    $(document).on('change', '.check-so-route', updateBulkMoveState);
    tableSoRute.on('draw', updateBulkMoveState);

    $('#bulkMoveForm').on('submit', function (e) {
        if ($('.check-so-route:checked').length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu SO yang akan dipindahkan.');
        }
    });

    $('.resetRouteForm').on('submit', function (e) {
        var ok = confirm('Kembalikan SO ini ke Semua SO Open/Partial?');
        if (!ok) e.preventDefault();
    });

    $('#btnConfirmSoRuteLoading').on('click', function () {
        var rute = $(this).data('rute');
        if (!rute) return;

        var btn = this;
        var askNote;
        if (window.Swal) {
            askNote = Swal.fire({
                title: 'Siap Loading Rute ' + rute + '?',
                text: 'Semua SO Open pada rute ini akan berubah menjadi Verifikasi.',
                input: 'textarea',
                inputLabel: 'Catatan Sales',
                inputPlaceholder: 'Catatan untuk verifikasi (opsional)',
                inputAttributes: { rows: 3 },
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, siap loading',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#16a34a'
            }).then(function(result) {
                return result.isConfirmed ? { ok: true, note: result.value || '' } : { ok: false };
            });
        } else {
            askNote = Promise.resolve(confirm('Konfirmasi Siap Loading rute ' + rute + '?'))
                .then(function(ok) {
                    return { ok: ok, note: ok ? (prompt('Catatan Sales untuk verifikasi (opsional):', '') || '') : '' };
                });
        }

        askNote.then(function(result) {
            if (!result.ok) return;
            setButtonLoading(btn, true, 'Konfirmasi');
            $.ajax({
                url: '<?= base_url("sales_order/confirm_so_rute_loading") ?>',
                type: 'POST',
                dataType: 'json',
                data: { kd_rute: rute, note: result.note || '' },
                success: function (res) {
                    salesToast(res.msg === 'success' ? 'success' : 'error', res.message || 'Selesai');
                    if (res.msg === 'success') {
                        setTimeout(function(){ window.location.reload(); }, 800);
                    }
                },
                error: function () {
                    salesToast('error', 'Terjadi kesalahan koneksi.');
                },
                complete: function () {
                    setButtonLoading(btn, false);
                }
            });
        });
    });
});
</script>
