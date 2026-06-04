<!-- views/content/sales/detail_do_sales.php -->
<style>
    .sales-do-line-table {
        border-collapse: collapse;
        border-spacing: 0;
        color: #111;
        font-size: 13px;
        box-shadow: none !important;
        margin-bottom: 0;
        width: 100%;
    }
    .sales-do-line-table,
    .sales-do-line-table * {
        border-radius: 0 !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }
    .sales-do-line-table th,
    .sales-do-line-table td {
        background: transparent;
        border: 1px solid #111;
        color: #111;
        padding: 5px 7px;
        text-align: center;
        vertical-align: middle;
    }
    .sales-do-line-table thead th {
        background: #343a40;
        color: #fff;
        font-weight: 700;
    }
    .sales-do-plain-info {
        color: #111;
        font-size: 14px;
        margin-bottom: 18px;
    }
    .sales-do-plain-row {
        display: flex;
        line-height: 1.6;
    }
    .sales-do-plain-label {
        flex: 0 0 110px;
        font-weight: 400;
    }
    .sales-do-plain-value {
        flex: 1 1 auto;
    }
</style>
<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Detail Delivery Order — Sales</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order/list_do') ?>">List DO Sales</a></li>
                            <li class="breadcrumb-item active">
                                <?= isset($kdo[0]) ? htmlspecialchars($kdo[0]->kd_do) : 'Detail DO' ?>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php foreach ($kdo as $k) : ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Detail Delivery Order — Sales View</h4>
                    </div>
                    <div class="card-body">

                        <!-- Info DO -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="sales-do-plain-info">
                                    <div class="sales-do-plain-row">
                                        <div class="sales-do-plain-label">Kode DO</div>
                                        <div class="sales-do-plain-value">: <?= htmlspecialchars($k->kd_do, ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <div class="sales-do-plain-row">
                                        <div class="sales-do-plain-label">Regional</div>
                                        <div class="sales-do-plain-value">: <?= htmlspecialchars($k->regional, ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <div class="sales-do-plain-row">
                                        <div class="sales-do-plain-label">Total Faktur</div>
                                        <div class="sales-do-plain-value">: <?= htmlspecialchars($k->totalfaktur, ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <div class="sales-do-plain-row">
                                        <div class="sales-do-plain-label">Total Barang</div>
                                        <div class="sales-do-plain-value">: <?= htmlspecialchars($k->total_barang, ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </div>

                                <?php
                                $tonase   = (float)$k->total_tonase_faktur;
                                $kubikasi = (float)$k->total_kubikasi;
                                $max_ton  = 7;
                                $max_kub  = 9;

                                $pct_ton = min(100, round(($tonase   / $max_ton) * 100, 1));
                                $pct_kub = min(100, round(($kubikasi / $max_kub) * 100, 1));

                                $sisa_ton = max(0, round($max_ton - $tonase,   3));
                                $sisa_kub = max(0, round($max_kub - $kubikasi, 4));

                                $bar_ton = $pct_ton >= 100 ? 'danger' : ($pct_ton >= 80 ? 'warning' : 'success');
                                $bar_kub = $pct_kub >= 100 ? 'danger' : ($pct_kub >= 80 ? 'warning' : 'success');
                                ?>

                                <!-- TONASE -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><strong><i class="fas fa-weight mr-1"></i>Tonase</strong></span>
                                        <span>
                                            <strong><?= $tonase ?></strong> / <?= $max_ton ?> TON
                                            <?php if ($pct_ton >= 100): ?>
                                                <span class="badge badge-danger ml-1">PENUH</span>
                                            <?php elseif ($pct_ton >= 80): ?>
                                                <span class="badge badge-warning ml-1">HAMPIR PENUH</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="progress" style="height:20px;border-radius:4px;">
                                        <div class="progress-bar bg-<?= $bar_ton ?> progress-bar-striped"
                                            role="progressbar"
                                            style="width:<?= $pct_ton ?>%;font-size:12px;line-height:20px;"
                                            aria-valuenow="<?= $pct_ton ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?= $pct_ton ?>%
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1" style="font-size:12px;color:#6c757d;">
                                        <span>Terpakai: <?= $tonase ?> TON</span>
                                        <span>Sisa: <strong class="text-<?= $sisa_ton <= 0 ? 'danger' : 'success' ?>"><?= $sisa_ton ?> TON</strong></span>
                                    </div>
                                </div>

                                <!-- KUBIKASI -->
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><strong><i class="fas fa-cube mr-1"></i>Kubikasi</strong></span>
                                        <span>
                                            <strong><?= $kubikasi ?></strong> / <?= $max_kub ?> m³
                                            <?php if ($pct_kub >= 100): ?>
                                                <span class="badge badge-danger ml-1">PENUH</span>
                                            <?php elseif ($pct_kub >= 80): ?>
                                                <span class="badge badge-warning ml-1">HAMPIR PENUH</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="progress" style="height:20px;border-radius:4px;">
                                        <div class="progress-bar bg-<?= $bar_kub ?> progress-bar-striped"
                                            role="progressbar"
                                            style="width:<?= $pct_kub ?>%;font-size:12px;line-height:20px;"
                                            aria-valuenow="<?= $pct_kub ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?= $pct_kub ?>%
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1" style="font-size:12px;color:#6c757d;">
                                        <span>Terpakai: <?= $kubikasi ?> m³</span>
                                        <span>Sisa: <strong class="text-<?= $sisa_kub <= 0 ? 'danger' : 'success' ?>"><?= $sisa_kub ?> m³</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Detail Barang — VIEW ONLY -->
                        <table class="sales-do-line-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kios</th>
                                    <th>Regional</th>
                                    <th>Rute</th>
                                    <th>Kode Faktur</th>
                                    <th>Tgl Input</th>
                                    <th>Nama Barang</th>
                                    <th>No Lot</th>
                                    <th>Box</th>
                                    <th>Pcs</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $prev_faktur   = null;
                                $rowspan_count = [];
                                $printed       = [];
                                $no            = 1;

                                foreach ($data_list as $row) {
                                    if (!isset($rowspan_count[$row->kd_faktur])) {
                                        $rowspan_count[$row->kd_faktur] = 0;
                                    }
                                    $rowspan_count[$row->kd_faktur]++;
                                }

                                foreach ($data_list as $row) :
                                    $show = !in_array($row->kd_faktur, $printed);
                                    if ($show) $printed[] = $row->kd_faktur;
                                ?>
                                <tr>
                                    <?php if ($show) : ?>
                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $no++ ?></td>
                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->nama_kios ?></td>
                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->regional ?></td>
                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_rute ?></td>
                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_faktur ?></td>
                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->tgl_transaksi ?></td>
                                    <?php endif; ?>
                                    <td><?= $row->nm_barang ?></td>
                                    <td><?= $row->no_lot ?></td>
                                    <td><?= $row->qty_box ?></td>
                                    <td><?= $row->qty_pcs ?></td>
                                    <td><?= $row->qty ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
    </footer>
</div>

