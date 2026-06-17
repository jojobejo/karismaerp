<!-- views/content/logistik/so_siap_loading.php -->
<style>
    .route-card {
        align-items: flex-start;
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
    .route-card .route-main {
        flex: 1 1 auto;
        min-width: 0;
    }
    .route-card .route-note {
        color: #6b7280;
        display: block;
        font-size: 10.5px;
        line-height: 1.25;
        margin-top: 2px;
        max-width: 100%;
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
        color: #6c757d;
        padding: 18px;
        text-align: center;
        width: 100%;
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
    .route-confirm-note {
        background: #fff8e1;
        border: 1px solid #ffe08a;
        border-radius: 4px;
        color: #4b3b00;
        font-size: 12px;
        line-height: 1.4;
        padding: 8px 10px;
    }
    .loading-plan-card .form-group {
        margin-bottom: 8px;
    }
    .btn-soft {
        color: #495057;
        background: #fff;
        border: 1px solid #ced4da;
    }
    .btn-soft:hover,
    .btn-soft:focus {
        color: #212529;
        background: #f8f9fa;
        border-color: #adb5bd;
    }
    .so-order-actions .dropdown-menu {
        min-width: 170px;
    }
    .so-order-actions .dropdown-item {
        font-size: 13px;
    }
    .so-order-actions .dropdown-item:disabled {
        color: #adb5bd;
    }
    .so-order-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 3px;
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
    $selected_route_note = '';
    $selected_route_confirm_by = '';
    $selected_route_confirm_at = '';
    foreach ($routes as $route) {
        if ((string)$route->kd_rute === (string)$selected_rute) {
            $selected_route_name = $route->nama_rute ?: $route->kd_rute;
            $selected_route_note = trim((string)($route->siap_loading_note ?? ''));
            $selected_route_confirm_by = trim((string)($route->siap_loading_confirm_by ?? ''));
            $selected_route_confirm_at = trim((string)($route->siap_loading_confirm_at ?? ''));
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

    $batas_ton = 7;
    $batas_kub = 9;
    $pct_ton = $batas_ton > 0 ? min(($total_tonase / $batas_ton) * 100, 100) : 0;
    $pct_kub = $batas_kub > 0 ? min(($total_kubikasi / $batas_kub) * 100, 100) : 0;
    $color_ton = $total_tonase > $batas_ton ? 'danger' : 'success';
    $color_kub = $total_kubikasi > $batas_kub ? 'danger' : 'info';
    $sisa_ton = max(0, $batas_ton - $total_tonase);
    $sisa_kub = max(0, $batas_kub - $total_kubikasi);

    $loading_plan = $loading_plan ?? null;
    $tgl_pengiriman = '';
    if (!empty($loading_plan->loading_tgl_pengiriman) && $loading_plan->loading_tgl_pengiriman !== '0000-00-00') {
        $tgl_pengiriman_ts = strtotime($loading_plan->loading_tgl_pengiriman);
        $tgl_pengiriman = $tgl_pengiriman_ts ? date('Y-m-d', $tgl_pengiriman_ts) : '';
    }
    $selected_jenis_pengiriman = $loading_plan->loading_jenis_pengiriman ?? 'expedisi_kantor';
    $selected_driver = (string)($loading_plan->loading_driver ?? '');
    $selected_truck = (string)($loading_plan->loading_nolambung ?? '');
    $is_luar = $selected_jenis_pengiriman === 'expedisi_luar';
    if (!$is_luar && ($selected_driver !== '' || $selected_truck !== '')) {
        $driver_exists = false;
        foreach (($driver ?? []) as $drv_check) {
            if ((string)$drv_check->kd_driver === $selected_driver) {
                $driver_exists = true;
                break;
            }
        }
        $truck_exists = false;
        foreach (($truck ?? []) as $trk_check) {
            if ((string)$trk_check->id === $selected_truck) {
                $truck_exists = true;
                break;
            }
        }
        $is_luar = !$driver_exists || !$truck_exists;
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

                <div class="card card-outline card-primary mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title">
                            <i class="fas fa-route mr-1"></i>Pilih Rute
                        </h3>
                    </div>
                    <div class="card-body p-0 route-strip">
                        <?php if (empty($routes)): ?>
                            <div class="route-strip-empty">
                                <i class="fas fa-inbox mr-1"></i>
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
                                    <div class="route-main">
                                        <div class="route-meta text-truncate" title="<?= htmlspecialchars($route->nama_rute) ?>">
                                            <?= htmlspecialchars($route->nama_rute) ?>
                                        </div>
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

                <div class="row">
                    <div class="col-12">
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
                                <?php if ($selected_route_note !== '' || $selected_route_confirm_by !== '' || $selected_route_confirm_at !== ''): ?>
                                    <div class="route-confirm-note mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <?php if ($selected_route_confirm_by !== '' || $selected_route_confirm_at !== ''): ?>
                                            <div class="mb-1">
                                                Dikonfirmasi oleh:
                                                <strong><?= htmlspecialchars($selected_route_confirm_by !== '' ? $selected_route_confirm_by : '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                                <?php if ($selected_route_confirm_at !== ''): ?>
                                                    <span class="text-muted ml-1"><?= htmlspecialchars($selected_route_confirm_at, ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($selected_route_note !== ''): ?>
                                            <?= nl2br(htmlspecialchars($selected_route_note, ENT_QUOTES, 'UTF-8')) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="row mb-3">
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

                                <?php if (!empty($selected_rute) && !empty($so_list)): ?>
                                    <div class="card card-outline card-info loading-plan-card mb-3">
                                        <div class="card-header py-2">
                                            <h3 class="card-title">
                                                <i class="fas fa-truck mr-1"></i>Plan Pengiriman
                                            </h3>
                                        </div>
                                        <div class="card-body py-2">
                                            <form id="formLoadingPlan"
                                                  method="post"
                                                  action="<?= base_url('logistik/so_siap_loading/siap_faktur') ?>">
                                                <input type="hidden" name="current_rute" id="current_rute" value="<?= htmlspecialchars($selected_rute, ENT_QUOTES, 'UTF-8') ?>">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="tgl_isi">Tanggal Pengiriman</label>
                                                            <input type="date" class="form-control form-control-sm" name="tgl_isi" id="tgl_isi" value="<?= htmlspecialchars($tgl_pengiriman, ENT_QUOTES, 'UTF-8') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Jenis Pengiriman</label>
                                                            <div class="d-flex flex-wrap">
                                                                <div class="custom-control custom-radio mr-3">
                                                                    <input class="custom-control-input" type="radio" id="jenis_kantor" name="jenis_pengiriman" value="expedisi_kantor" <?= !$is_luar ? 'checked' : '' ?>>
                                                                    <label for="jenis_kantor" class="custom-control-label">Ekspedisi Kantor</label>
                                                                </div>
                                                                <div class="custom-control custom-radio">
                                                                    <input class="custom-control-input" type="radio" id="jenis_luar" name="jenis_pengiriman" value="expedisi_luar" <?= $is_luar ? 'checked' : '' ?>>
                                                                    <label for="jenis_luar" class="custom-control-label">Ekspedisi Luar</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3" id="select_driver_wrapper">
                                                        <div class="form-group">
                                                            <label for="driver_isi">Driver</label>
                                                            <select class="form-control form-control-sm" name="driver_isi" id="driver_isi">
                                                                <option value="">Pilih Driver</option>
                                                                <?php foreach (($driver ?? []) as $drv) : ?>
                                                                    <option value="<?= htmlspecialchars($drv->kd_driver, ENT_QUOTES, 'UTF-8') ?>" <?= $selected_driver === (string)$drv->kd_driver ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($drv->nama_driver, ENT_QUOTES, 'UTF-8') ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 d-none" id="input_driver_luar_wrapper">
                                                        <div class="form-group">
                                                            <label for="driver_luar_isi">Driver Luar</label>
                                                            <input type="text" class="form-control form-control-sm" name="driver_luar_isi" id="driver_luar_isi" value="<?= $is_luar ? htmlspecialchars($selected_driver, ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="Nama driver">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3" id="select_truck_wrapper">
                                                        <div class="form-group">
                                                            <label for="truck_isi">Kendaraan</label>
                                                            <select class="form-control form-control-sm" name="truck_isi" id="truck_isi">
                                                                <option value="">Pilih Kendaraan</option>
                                                                <?php foreach (($truck ?? []) as $trk) : ?>
                                                                    <option value="<?= htmlspecialchars($trk->id, ENT_QUOTES, 'UTF-8') ?>" <?= $selected_truck === (string)$trk->id ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($trk->noplat, ENT_QUOTES, 'UTF-8') ?><?= !empty($trk->nm_truk) ? ' - ' . htmlspecialchars($trk->nm_truk, ENT_QUOTES, 'UTF-8') : '' ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 d-none" id="input_truck_luar_wrapper">
                                                        <div class="form-group">
                                                            <label for="truck_luar_isi">Kendaraan Luar</label>
                                                            <input type="text" class="form-control form-control-sm" name="truck_luar_isi" id="truck_luar_isi" value="<?= $is_luar ? htmlspecialchars($selected_truck, ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="No. plat / kendaraan">
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($selected_rute) && !empty($so_list)): ?>
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                                        <div class="small text-muted">
                                            Verifikasi barang selesai:
                                            <strong><?= number_format($total_so_verified) ?></strong> dari
                                            <strong><?= number_format($total_so) ?></strong> SO.
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <table id="tabelSoSiapLoading" class="table table-bordered table-striped table-hover table-sm">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width:40px">No</th>
                                            <th style="width:76px" class="text-center">Urutan</th>
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
                                            <th>Catatan SO</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($so_list)): ?>
                                            <tr>
                                                <td colspan="14" class="text-center text-muted py-4">
                                                    Tidak ada Sales Order siap loading untuk rute ini.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($so_list as $so): ?>
                                                <tr class="so-row" data-so="<?= (int)$so->id_so ?>">
                                                    <td class="text-center rownum"></td>
                                                    <td class="text-center">
                                                        <span class="so-order-label"></span>
                                                        <div class="dropdown so-order-actions">
                                                            <button type="button" class="btn btn-sm btn-soft dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                Aksi
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <button type="button" class="dropdown-item btn-so-order" data-action="up">
                                                                    <i class="fas fa-arrow-up mr-2"></i> Naikkan
                                                                </button>
                                                                <button type="button" class="dropdown-item btn-so-order" data-action="down">
                                                                    <i class="fas fa-arrow-down mr-2"></i> Turunkan
                                                                </button>
                                                                <button type="button" class="dropdown-item btn-so-order" data-action="top">
                                                                    <i class="fas fa-angle-double-up mr-2"></i> Paling atas
                                                                </button>
                                                                <button type="button" class="dropdown-item btn-so-order" data-action="bottom">
                                                                    <i class="fas fa-angle-double-down mr-2"></i> Paling bawah
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </td>
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
                                                    <td style="min-width:180px">
                                                        <?php if (!empty($so->catatan)): ?>
                                                            <div class="small text-dark">
                                                                <i class="fas fa-sticky-note text-warning mr-1"></i>
                                                                <?= nl2br(htmlspecialchars($so->catatan, ENT_QUOTES, 'UTF-8')) ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="so-action-group" role="group">
                                                            <a href="<?= base_url('logistik/so_siap_loading/verifikasi/' . $so->id_so) ?>"
                                                               class="btn btn-sm btn-info"
                                                               title="Detail dan Verifikasi Barang">
                                                                <i class="fas fa-clipboard-list"></i>
                                                            </a>
                                                            <form method="post"
                                                                  action="<?= base_url('logistik/so_siap_loading/kembalikan/' . $so->id_so) ?>"
                                                                  class="return-open-form"
                                                                  data-no-so="<?= htmlspecialchars($so->no_so, ENT_QUOTES, 'UTF-8') ?>">
                                                                <input type="hidden" name="current_rute" value="<?= htmlspecialchars($selected_rute) ?>">
                                                                <input type="hidden" name="catatan_logistik" class="return-open-note">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Kembalikan ke Open/Partial">
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
                                <?php if (!empty($selected_rute) && !empty($so_list)): ?>
                                    <button type="submit"
                                            form="formLoadingPlan"
                                            class="btn btn-success btn-block mt-3"
                                            <?= ($total_so === 0 || $total_so_verified < $total_so) ? 'disabled' : '' ?>
                                            onclick="return confirm('Ubah semua SO rute <?= htmlspecialchars($selected_rute, ENT_QUOTES, 'UTF-8') ?> menjadi siap difakturkan?');">
                                        <i class="fas fa-file-invoice-dollar mr-1"></i> Jadikan Siap Faktur
                                    </button>
                                <?php endif; ?>
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

<div class="modal fade" id="modalReturnOpenNote" tabindex="-1" role="dialog" aria-labelledby="modalReturnOpenNoteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="modalReturnOpenNoteLabel">
                    <i class="fas fa-times-circle mr-1"></i> Kembalikan SO
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Tambahkan catatan untuk <b id="returnOpenNoSo">SO</b>.</p>
                <div class="form-group mb-0">
                    <textarea id="returnOpenNote"
                              class="form-control"
                              rows="4"
                              maxlength="500"
                              placeholder="Tambah Catatan"
                              required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmReturnOpen">
                    <i class="fas fa-check mr-1"></i> Kembalikan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    var pendingReturnOpenForm = null;

    $(".return-open-form").on("submit", function (event) {
        event.preventDefault();

        pendingReturnOpenForm = this;
        $("#returnOpenNoSo").text($(this).data("no-so") || "SO");
        $("#returnOpenNote").val("").removeClass("is-invalid");
        $("#modalReturnOpenNote").modal("show");
    });

    $("#btnConfirmReturnOpen").on("click", function () {
        var note = ($("#returnOpenNote").val() || "").trim();

        if (note === "") {
            $("#returnOpenNote").addClass("is-invalid").focus();
            return;
        }

        if (!pendingReturnOpenForm) {
            return;
        }

        $(pendingReturnOpenForm).find(".return-open-note").val(note);
        pendingReturnOpenForm.submit();
    });

    function getJenisPengiriman() {
        return $("input[name='jenis_pengiriman']:checked").val() || "expedisi_kantor";
    }

    function isExpedisiLuar() {
        return getJenisPengiriman() === "expedisi_luar";
    }

    function getDriverValue() {
        if (isExpedisiLuar()) {
            return ($("#driver_luar_isi").val() || "").trim();
        }
        return $("#driver_isi").val();
    }

    function getTruckValue() {
        if (isExpedisiLuar()) {
            return ($("#truck_luar_isi").val() || "").trim();
        }
        return $("#truck_isi").val();
    }

    function togglePengirimanFields() {
        var luar = isExpedisiLuar();

        if (luar) {
            $("#select_driver_wrapper, #select_truck_wrapper").addClass("d-none");
            $("#input_driver_luar_wrapper, #input_truck_luar_wrapper").removeClass("d-none");
        } else {
            $("#select_driver_wrapper, #select_truck_wrapper").removeClass("d-none");
            $("#input_driver_luar_wrapper, #input_truck_luar_wrapper").addClass("d-none");
        }
    }

    function resetFieldBorder() {
        $("#tgl_isi, #driver_isi, #truck_isi, #driver_luar_isi, #truck_luar_isi").css("border", "");
    }

    function validatePengirimanInput() {
        var valid = true;
        var luar = isExpedisiLuar();

        resetFieldBorder();
        if (!$("#tgl_isi").val()) {
            $("#tgl_isi").css("border", "2px solid red");
            valid = false;
        }
        if (!getDriverValue()) {
            $(luar ? "#driver_luar_isi" : "#driver_isi").css("border", "2px solid red");
            valid = false;
        }
        if (!getTruckValue()) {
            $(luar ? "#truck_luar_isi" : "#truck_isi").css("border", "2px solid red");
            valid = false;
        }

        if (!valid) {
            alert("Lengkapi tanggal pengiriman, driver, dan kendaraan terlebih dahulu.");
        }
        return valid;
    }

    togglePengirimanFields();
    $("input[name='jenis_pengiriman']").on("change", togglePengirimanFields);

    $("#formLoadingPlan").on("submit", function () {
        return validatePengirimanInput();
    });

    if ($("#tabelSoSiapLoading tbody tr.so-row").length === 0) {
        return;
    }

    function getSoRows() {
        return $("#tabelSoSiapLoading tbody tr.so-row");
    }

    function getSoOrder() {
        return getSoRows().map(function () {
            return $(this).data("so");
        }).get();
    }

    function updateSoOrderControls() {
        var rows = getSoRows();
        rows.each(function (index) {
            var row = $(this);
            row.find(".rownum").text(index + 1);
            row.find(".so-order-label").text("Urutan " + (index + 1));
            row.find(".btn-so-order[data-action='up'], .btn-so-order[data-action='top']").prop("disabled", index === 0);
            row.find(".btn-so-order[data-action='down'], .btn-so-order[data-action='bottom']").prop("disabled", index === rows.length - 1);
        });
    }

    function saveSoOrder(previousOrder) {
        $(".btn-so-order").prop("disabled", true);

        $.ajax({
            url: "<?= base_url('logistik/so_siap_loading/update_urutan') ?>",
            type: "POST",
            data: {
                current_rute: $("#current_rute").val(),
                urutan: getSoOrder()
            },
            dataType: "JSON",
            success: function (response) {
                if (response.msg !== "success") {
                    restoreSoOrder(previousOrder);
                    alert(response.message || "Gagal menyimpan urutan SO.");
                }
            },
            error: function (xhr, status, error) {
                restoreSoOrder(previousOrder);
                alert("Terjadi kesalahan: " + error);
            },
            complete: function () {
                updateSoOrderControls();
            }
        });
    }

    function restoreSoOrder(order) {
        var tbody = $("#tabelSoSiapLoading tbody");
        order.forEach(function (idSo) {
            tbody.append(getSoRows().filter(function () {
                return String($(this).data("so")) === String(idSo);
            }));
        });
        updateSoOrderControls();
    }

    $(".btn-so-order").on("click", function () {
        var btn = $(this);
        var row = btn.closest("tr.so-row");
        var rows = getSoRows();
        var previousOrder = getSoOrder();
        var currentIndex = rows.index(row);
        var targetIndex = currentIndex;
        var action = btn.data("action");

        if (action === "up") {
            targetIndex = currentIndex - 1;
        } else if (action === "down") {
            targetIndex = currentIndex + 1;
        } else if (action === "top") {
            targetIndex = 0;
        } else if (action === "bottom") {
            targetIndex = rows.length - 1;
        }

        if (targetIndex < 0 || targetIndex >= rows.length || targetIndex === currentIndex) {
            updateSoOrderControls();
            return;
        }

        if (targetIndex < currentIndex) {
            row.insertBefore(rows.eq(targetIndex));
        } else {
            row.insertAfter(rows.eq(targetIndex));
        }

        updateSoOrderControls();
        saveSoOrder(previousOrder);
    });

    updateSoOrderControls();
});
</script>
