<!-- views/content/logistik/body_detaildo.php -->
<style>
    table {
        font-size: 14px;
        white-space: nowrap;
    }

    th,
    td {
        vertical-align: middle;
        text-align: center;
    }

    .table thead th {
        background-color: #343a40;
        color: #fff;
    }

    .detail-do-heading {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .detail-do-heading h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
    }

    .detail-do-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 6px;
        color: #495057;
        background: #f1f3f5;
        border: 1px solid #dee2e6;
        font-size: 13px;
        font-weight: 600;
    }

    .status-pill.is-warning {
        color: #856404;
        background: #fff8e1;
        border-color: #ffe8a1;
    }

    .status-pill.is-info {
        color: #0c5460;
        background: #e8f6f8;
        border-color: #bee5eb;
    }

    .status-pill.is-success {
        color: #155724;
        background: #eaf6ec;
        border-color: #c3e6cb;
    }

    .status-pill.is-muted {
        color: #495057;
        background: #eef0f2;
        border-color: #d8dde2;
    }

    .status-pill.is-primary {
        color: #084298;
        background: #e7f1ff;
        border-color: #b6d4fe;
    }

    .sales-note-box {
        display: inline-flex;
        align-items: flex-start;
        gap: 10px;
        max-width: 720px;
        margin-bottom: 18px;
        padding: 10px 12px;
        color: #344054;
        background: #f8fafc;
        border: 1px solid #d7dee8;
        border-left: 4px solid #3b82f6;
        border-radius: 6px;
        white-space: normal;
    }

    .sales-note-box i {
        margin-top: 3px;
        color: #2563eb;
    }

    .sales-note-title {
        display: block;
        margin-bottom: 2px;
        font-size: 12px;
        font-weight: 700;
        color: #1f2937;
        text-transform: uppercase;
    }

    .sales-note-text {
        font-size: 13px;
        line-height: 1.45;
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

    .faktur-actions {
        min-width: 92px;
    }

    .faktur-actions .dropdown-menu {
        min-width: 190px;
    }

    .faktur-actions .dropdown-item {
        font-size: 13px;
    }

    .faktur-actions .dropdown-item:disabled {
        color: #adb5bd;
    }

    .delivery-info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(180px, 1fr));
        gap: 10px;
        margin: 14px 0;
    }

    .delivery-info-item {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px 12px;
        background: #fff;
        white-space: normal;
    }

    .delivery-info-label {
        display: block;
        margin-bottom: 4px;
        font-size: 12px;
        color: #6c757d;
        font-weight: 700;
        text-transform: uppercase;
    }

    .delivery-info-value {
        color: #212529;
        font-weight: 600;
    }

    .print-action-bar {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-top: 16px;
        width: 100%;
    }

    .print-action-bar .btn {
        width: 100%;
        min-height: 42px;
    }

    @media (max-width: 767.98px) {
        .delivery-info-grid,
        .print-action-bar {
            grid-template-columns: 1fr;
        }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <?php foreach ($dostatus as $d) : ?>

            <div class="content-wrapper">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6 d-flex align-items-center">
                                <?php if ($this->session->userdata('jobdesk') == 'ADMINKEUTC') : ?>
                                    <a href="<?= base_url('keuangan') ?>" class="btn btn-primary mr-2"><i class="fas fa-arrow-circle-left"></i></a>
                                <?php elseif ($this->session->userdata('jobdesk') == 'LOGISTIK') : ?>
                                    <a href="<?= base_url('logistik') ?>" class="btn btn-primary mr-2"><i class="fas fa-arrow-circle-left"></i></a>
                                <?php endif; ?>
                                <h1 class="m-0">Detail Delivery Order</h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                                    <li class="breadcrumb-item"><a href="<?= base_url('logistik') ?>">Logistik</a></li>
                                    <li class="breadcrumb-item active"><?= htmlspecialchars($d->kd_do ?? 'Detail DO', ENT_QUOTES, 'UTF-8') ?></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="content">

                    <div class="container-fluid">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title">Rencana Pengiriman Barang</h3>
                            </div>

                            <div class="card-body">
                                <?php if ($this->session->userdata('jobdesk') == 'LOGISTIK') : ?>
                                    <?php
                                        $status_meta = [
                                            '1' => ['label' => 'Draft', 'icon' => 'fas fa-file-alt', 'class' => 'is-warning'],
                                            '2' => ['label' => 'Menunggu Konfirmasi Sales', 'icon' => 'fas fa-clock', 'class' => 'is-info'],
                                            '3' => ['label' => 'Proses DO', 'icon' => 'fas fa-clipboard-check', 'class' => 'is-success'],
                                            '4' => ['label' => 'Is Loading', 'icon' => 'fas fa-truck-loading', 'class' => 'is-muted'],
                                            '5' => ['label' => 'On Delivery', 'icon' => 'fas fa-truck', 'class' => 'is-primary'],
                                        ];
                                        $current_status = $status_meta[$d->status] ?? ['label' => '-', 'icon' => 'fas fa-info-circle', 'class' => 'is-muted'];
                                    ?>
                                    <div class="detail-do-heading">
                                        <h2>Detail Orders</h2>
                                        <div class="detail-do-actions">
                                            <span class="status-pill <?= $current_status['class'] ?>">
                                                <i class="<?= $current_status['icon'] ?>"></i>
                                                <?= $current_status['label'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php if (!empty($d->sales_confirm_note)) : ?>
                                        <div class="sales-note-box">
                                            <i class="fas fa-info-circle"></i>
                                            <div>
                                                <div class="sales-note-text">
                                                    <?= nl2br(htmlspecialchars($d->sales_confirm_note, ENT_QUOTES, 'UTF-8')) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($this->session->userdata('jobdesk') == 'ADMINKEUTC') : ?>
                                <?php endif; ?>
                                <?php foreach ($kdo as $k) : ?>
                                    <input type="hidden" id="do_isi" name="do_isi" value="<?= $k->kd_do ?>">
                                    <?php $this->load->view('content/logistik/modal/modal_detail_do'); ?>

                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Kode Faktur</div>
                                        <div>: <?= $k->kd_do ?></div>
                                        <?php foreach ($dostatus as $ds) :
                                            $date = (!empty($ds->tgl_pengiriman) && $ds->tgl_pengiriman !== '0000-00-00')
                                                ? date('d/m/Y', strtotime($ds->tgl_pengiriman))
                                                : date('d/m/Y');
                                        ?>
                                            <div class="col-auto" hidden>
                                                <input type="text" class="form-control" value="<?= $ds->driver ?>" name="print_driver" id="print_driver">
                                                <input type="text" class="form-control" value="<?= $date ?>" name="print_tgl" id="print_tgl">
                                                <input type="text" class="form-control" value="<?= $ds->nolambung ?>" name="print_plat" id="print_plat">
                                                <input type="text" class="form-control" value="<?= $ds->status ?>" name="print_status" id="print_status">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Regional Pengiriman</div>
                                        <div>: <?= $k->regional ?></div>
                                    </div>

                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Customer</div>
                                        <div>: <?= $k->total_customer ?? $k->totalfaktur ?></div>
                                    </div>

                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Faktur</div>
                                        <div>: <?= $k->total_faktur ?? '-' ?></div>
                                    </div>

                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Barang</div>
                                        <div>: <?= $k->total_barang ?></div>
                                    </div>

                                    <?php
                                        $tgl_pengiriman_view = (!empty($k->tgl_pengiriman) && $k->tgl_pengiriman !== '0000-00-00')
                                            ? date('d/m/Y', strtotime($k->tgl_pengiriman))
                                            : '-';
                                        $driver_view = !empty($k->nama_driver) ? $k->nama_driver : (!empty($k->driver) ? $k->driver : '-');
                                        $kendaraan_view = !empty($k->noplat) ? $k->noplat : (!empty($k->nolambung) ? $k->nolambung : '-');
                                        if (!empty($k->nm_truk)) {
                                            $kendaraan_view .= ' - ' . $k->nm_truk;
                                        }
                                    ?>
                                    <div class="delivery-info-grid">
                                        <div class="delivery-info-item">
                                            <span class="delivery-info-label">Tgl Pengiriman</span>
                                            <span class="delivery-info-value"><?= htmlspecialchars($tgl_pengiriman_view, ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                        <div class="delivery-info-item">
                                            <span class="delivery-info-label">Driver</span>
                                            <span class="delivery-info-value"><?= htmlspecialchars($driver_view, ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                        <div class="delivery-info-item">
                                            <span class="delivery-info-label">Kendaraan</span>
                                            <span class="delivery-info-value"><?= htmlspecialchars($kendaraan_view, ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>

                                    <!-- ============================================================
                                        TONASE & KUBIKASI — dengan progress bar & kuota
                                    ============================================================ -->
                                    <?php
                                        // Batas default (sesuai konstanta di M_SalesOrder)
                                        $batas_ton = 7;    // ton
                                        $batas_kub = 9;    // m³

                                        $total_ton = (float)($k->total_tonase_faktur ?? 0);
                                        $total_kub = (float)($k->total_kubikasi      ?? 0);

                                        $pct_ton = $batas_ton > 0 ? min(($total_ton / $batas_ton) * 100, 100) : 0;
                                        $pct_kub = $batas_kub > 0 ? min(($total_kub / $batas_kub) * 100, 100) : 0;

                                        $color_ton = $total_ton > $batas_ton ? 'danger' : 'success';
                                        $color_kub = $total_kub > $batas_kub ? 'danger' : 'info';

                                        $sisa_ton = max(0, $batas_ton - $total_ton);
                                        $sisa_kub = max(0, $batas_kub - $total_kub);
                                    ?>

                                    <div class="row mt-3 mb-3">
                                        <!-- Tonase -->
                                        <div class="col-md-6">
                                            <div class="card card-outline card-<?= $color_ton ?> mb-0">
                                                <div class="card-header py-2">
                                                    <h6 class="card-title mb-0">
                                                        <i class="fas fa-weight mr-1"></i> Tonase
                                                        <?php if ($total_ton > $batas_ton): ?>
                                                            <span class="badge badge-danger ml-1">Melebihi!</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                </div>
                                                <div class="card-body py-2">
                                                    <!-- Progress bar -->
                                                    <div class="progress mb-2" style="height:14px; border-radius:7px;">
                                                        <div class="progress-bar bg-<?= $color_ton ?> progress-bar-striped"
                                                            role="progressbar"
                                                            style="width: <?= number_format($pct_ton, 2) ?>%"
                                                            title="<?= number_format($pct_ton, 1) ?>%">
                                                            <?php if ($pct_ton >= 20): ?>
                                                                <?= number_format($pct_ton, 1) ?>%
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <!-- Info baris -->
                                                    <div class="row text-center small">
                                                        <div class="col-4">
                                                            <div class="text-muted">Terpakai</div>
                                                            <div class="font-weight-bold text-<?= $color_ton ?>">
                                                                <?= number_format($total_ton, 3) ?> ton
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
                                                                    ? number_format($sisa_ton, 3).' ton'
                                                                    : '<i class="fas fa-exclamation-triangle"></i> Penuh' ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Kubikasi -->
                                        <div class="col-md-6">
                                            <div class="card card-outline card-<?= $color_kub ?> mb-0">
                                                <div class="card-header py-2">
                                                    <h6 class="card-title mb-0">
                                                        <i class="fas fa-cube mr-1"></i> Kubikasi
                                                        <?php if ($total_kub > $batas_kub): ?>
                                                            <span class="badge badge-danger ml-1">Melebihi!</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                </div>
                                                <div class="card-body py-2">
                                                    <!-- Progress bar -->
                                                    <div class="progress mb-2" style="height:14px; border-radius:7px;">
                                                        <div class="progress-bar bg-<?= $color_kub ?> progress-bar-striped"
                                                            role="progressbar"
                                                            style="width: <?= number_format($pct_kub, 2) ?>%"
                                                            title="<?= number_format($pct_kub, 1) ?>%">
                                                            <?php if ($pct_kub >= 20): ?>
                                                                <?= number_format($pct_kub, 1) ?>%
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <!-- Info baris -->
                                                    <div class="row text-center small">
                                                        <div class="col-4">
                                                            <div class="text-muted">Terpakai</div>
                                                            <div class="font-weight-bold text-<?= $color_kub ?>">
                                                                <?= number_format($total_kub, 4) ?> m³
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="text-muted">Batas</div>
                                                            <div class="font-weight-bold">
                                                                <?= number_format($batas_kub, 1) ?> m³
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="text-muted">Sisa</div>
                                                            <div class="font-weight-bold text-<?= $sisa_kub > 0 ? 'success' : 'danger' ?>">
                                                                <?= $sisa_kub > 0
                                                                    ? number_format($sisa_kub, 4).' m³'
                                                                    : '<i class="fas fa-exclamation-triangle"></i> Penuh' ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END TONASE KUBIKASI -->

                                <?php endforeach; ?>
                                <!-- END FORM -->
                                <?php if ($this->session->userdata('jobdesk') == 'LOGISTIK') : ?>
                                    <table class="table table-bordered" id="tb_checker_do">
                                        <thead>
                                            <tr>
                                                <th colspan="2">Data Kios</th>
                                                <th rowspan="2">Rute</th>
                                                <th colspan="2">TTB</th>
                                                <!-- <th rowspan="2">No</th> -->
                                                <th rowspan="2">NOTE FAKTUR</th>
                                                <th rowspan="2">Nama Barang</th>
                                                <th rowspan="2">No Lot</th>
                                                <th colspan="2">Qty</th>
                                                <th rowspan="2">Qty</th>
                                            </tr>
                                            <tr>
                                                <th>Nama Kios</th>
                                                <th>Regional</th>
                                                <th>Kode Faktur</th>
                                                <th>Tgl Input</th>
                                                <th>Besar</th>
                                                <th>Kecil</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $prev_norut = null;
                                            $rowspan_count = [];
                                            $norut_counter = 1;
                                            $faktur_order = [];

                                            foreach ($data_list as $row) {
                                                if (!isset($rowspan_count[$row->kd_faktur])) {
                                                    $rowspan_count[$row->kd_faktur] = 0;
                                                }
                                                $rowspan_count[$row->kd_faktur]++;

                                                if (!in_array($row->kd_faktur, $faktur_order)) {
                                                    $faktur_order[] = $row->kd_faktur;
                                                }
                                            }

                                            $printed_faktur = [];
                                            foreach ($data_list as $row) :
                                                $show_faktur_info = !in_array($row->kd_faktur, $printed_faktur);
                                                $faktur_position = array_search($row->kd_faktur, $faktur_order);
                                                if ($show_faktur_info) {
                                                    $printed_faktur[] = $row->kd_faktur;
                                                    $norut_counter = 1;
                                                }
                                            ?>
                                                <tr class="<?= $show_faktur_info ? 'faktur-row' : '' ?>" <?= $show_faktur_info ? 'data-faktur="' . htmlspecialchars($row->kd_faktur, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                                                    <?php if ($show_faktur_info) :
                                                        if ($row->telp1 == NULL || "0") {
                                                            $telp1 = "-";
                                                        } else {
                                                            $telp1 = $row->telp1;
                                                        }

                                                        if ($row->telp2 == NULL || "0") {
                                                            $telp2 = "-";
                                                        } else {
                                                            $telp2 = $row->telp2;
                                                        }
                                                    ?>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->nama_kios ?><br><?= "(" . $telp1 . "/" . $telp2 . ")" ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->regional ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_rute ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_faktur ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->tgl_transaksi ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->note_faktur != '' ? htmlspecialchars($row->note_faktur, ENT_QUOTES, 'UTF-8') : '' ?></td>

                                                    <?php endif; ?>
                                                    <!-- <td><?= $norut_counter++ ?></td> -->
                                                    <td><?= $row->nm_barang ?></td>
                                                    <td>
                                                        <?= $row->no_lot ?> - 
                                                        <?php
                                                            $d_exp = $row->tgl_exp;
                                                            $fmt   = DateTime::createFromFormat('Y-m-d', $d_exp) 
                                                                    ?: DateTime::createFromFormat('m/d/Y', $d_exp);
                                                            echo $fmt ? $fmt->format('d/m/Y') : $d_exp;
                                                        ?>
                                                    </td>
                                                    <td><?= $row->qty_box ?></td>
                                                    <td><?= $row->qty_pcs ?></td>
                                                    <td><?= $row->qty ?></td>

                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php foreach ($kdo as $k) : ?>
                                        <div class="row">

                                            <?php
                                            $bisa_edit  = ($d->status == '1') || ($d->status == '2') || ($d->status == '3');
                                            $sudah_siap = ($d->status == '4') || ($d->status == '5');
                                            ?>

                                            <?php if ($sudah_siap || $bisa_edit) : ?>
                                                <div class="col">
                                                    <div class="print-action-bar">
                                                        <button type="button" class="btn btn-primary" id="btnPrintOrder1" data-kd="<?= $k->kd_do ?>">
                                                            <i class="fas fa-file-alt mr-1"></i> Print DO
                                                        </button>
                                                        <button type="button" class="btn btn-success" id="btnPrintRegis1" data-kd="<?= $k->kd_do ?>">
                                                            <i class="fas fa-clipboard-list mr-1"></i> Print Register
                                                        </button>
                                                        <button type="button" class="btn btn-info" id="btnPrintChecker1" data-kd="<?= $k->kd_do ?>">
                                                            <i class="fas fa-tasks mr-1"></i> Print Checker
                                                        </button>
                                                    </div>
                                                </div>

                                            <?php else : ?>

                                                <div class="col">
                                                    <div class="alert alert-info mt-3 mb-0">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        Menunggu proses DO. Tombol print akan tersedia setelah DO direkam oleh Logistik.
                                                    </div>
                                                </div>

                                            <?php endif; ?>

                                        </div>
                                        <?php endforeach; ?>
                                    <!-- LOGISTIK END -->

                                    <!-- TC START -->
                                <?php elseif ($this->session->userdata('jobdesk') == 'ADMINKEUTC') : ?>
                                    <table class="table table-bordered" id="tb_checker_do">
                                        <thead>
                                            <tr>
                                                <th colspan="2">Data Kios</th>
                                                <th rowspan="2">Rute</th>
                                                <th colspan="3">TTB</th>
                                                <th rowspan="2">Cash / Tempo</th>
                                            </tr>
                                            <tr>
                                                <th>Nama Kios</th>
                                                <th>Regional</th>
                                                <th>Kode Faktur</th>
                                                <th>Tgl Input</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $prev_norut = null;
                                            $rowspan_count = [];
                                            $norut_counter = 1;

                                            foreach ($datatc as $row) {
                                                if (!isset($rowspan_count[$row->kd_faktur])) {
                                                    $rowspan_count[$row->kd_faktur] = 0;
                                                }
                                                $rowspan_count[$row->kd_faktur]++;
                                            }

                                            $printed_faktur = [];
                                            foreach ($datatc as $row) :
                                                $show_faktur_info = !in_array($row->kd_faktur, $printed_faktur);
                                                if ($show_faktur_info) {
                                                    $printed_faktur[] = $row->kd_faktur;
                                                }
                                                if ($row->jtempo == '0') {
                                                    $tempo = '<span class="badge badge-primary">Cash</span>';
                                                } else if ($row->jtempo == '30') {
                                                    $tempo = '<span class="badge badge-warning">' . htmlspecialchars($row->jtempo) . '</span>';
                                                } else {
                                                    $tempo = '<span class="badge badge-success">' . htmlspecialchars($row->jtempo) . '</span>';
                                                }
                                            ?>
                                                <tr>
                                                    <?php if ($show_faktur_info) : ?>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->nama_kios ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->regional ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_rute ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_faktur ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->tgl_transaksi ?></td>
                                                    <?php endif; ?>
                                                    <td><?= format_rupiah($row->nominal_p) ?></td>
                                                    <td><?= $tempo ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                </section>
            </div>
        <?php endforeach; ?>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>
        <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>

    <div class="modal fade" id="modal_note_faktur" tabindex="-1" role="dialog" aria-labelledby="modalNoteFakturLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="<?= current_url(); ?>" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNoteFakturLabel">Update Note Faktur</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="kd_faktur" id="modal_kd_faktur">
                        <div class="form-group">
                            <label for="modal_note_faktur_input">Note Faktur</label>
                            <input type="text" class="form-control" name="note_faktur" id="modal_note_faktur_input" placeholder="Input note faktur" autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#modal_note_faktur").on("show.bs.modal", function(event) {
                var button = $(event.relatedTarget);
                var kdFaktur = button.data("kd_faktur") || "";
                var noteFaktur = button.data("note_faktur") || "";

                $("#modal_kd_faktur").val(kdFaktur);
                $("#modal_note_faktur_input").val(noteFaktur);
            });
        });

        $("#btnunpost").on('click', function() {
            var kd_do = $(this).data('kd');

            $.ajax({
                url: "<?= base_url('do/repost_status') ?>",
                type: "POST",
                data: {
                    kd_do: kd_do,
                    status: "1"
                },
                dataType: "JSON",
                success: function(response) {
                    if (response.msg === "success") {
                        alert("Data berhasil di-repost!");
                        window.location.href = "<?= base_url('detail_do/') ?>" + kd_do;
                    } else {
                        alert(response.message || "Gagal mengubah status");
                    }
                },
                error: function(xhr, status, error) {
                    alert("Terjadi kesalahan: " + error);
                }
            });

            $.ajax({
                url: "<?= base_url('do/delete_ics_do') ?>",
                type: 'POST',
                data: {
                    kd_do: kd_do
                },
                dataType: 'json',
                success: function(response) {
                    if (response.msg === 'success') {
                        alert('Data berhasil dihapus');
                    } else {
                        alert('Gagal: ' + response.message);
                    }
                }
            });
        });



        $("#btnPrintOrder1").on('click', function() {
            var kd_do = $(this).data('kd');
            var plat = $("#print_plat").val();
            var tgl = $("#print_tgl").val();
            var drive = $("#print_driver").val();

            var printUrl = "<?= base_url('print_do/') ?>" + kd_do +
                "?tgl_kirim=" + encodeURIComponent(tgl) +
                "&driver=" + encodeURIComponent(drive) +
                "&plat=" + encodeURIComponent(plat);
            window.open(printUrl, "_blank");
        });

        $("#btnPrintRegis1").on('click', function() {
            var kd_do = $(this).data('kd');
            var status = $("#print_status").val();
            var plat = $("#print_plat").val();
            var tgl = $("#print_tgl").val();
            var drive = $("#print_driver").val();

            var printUrl = "<?= base_url('print_regis/') ?>" + kd_do +
                "?tgl_kirim=" + encodeURIComponent(tgl) +
                "&driver=" + encodeURIComponent(drive) +
                "&plat=" + encodeURIComponent(plat);
            window.open(printUrl, "_blank");
        });

        $("#btnPrintChecker1").on('click', function() {
            var kd_do = $(this).data('kd');
            var status = $("#print_status").val();
            var plat = $("#print_plat").val();
            var tgl = $("#print_tgl").val();
            var drive = $("#print_driver").val();

            var printUrl = "<?= base_url('print_checker/') ?>" + kd_do +
                "?tgl_kirim=" + encodeURIComponent(tgl) +
                "&driver=" + encodeURIComponent(drive) +
                "&plat=" + encodeURIComponent(plat);
            window.open(printUrl, "_blank");

        });
    </script>
