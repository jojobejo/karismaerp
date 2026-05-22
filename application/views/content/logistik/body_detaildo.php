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
                        <div class="row">
                            <?php if ($this->session->userdata('jobdesk') == 'ADMINKEUTC') : ?>
                                <a href="<?= base_url('keuangan') ?>" class="btn btn-primary mb-2 ml-2"><i class="fas fa-arrow-circle-left"></i></a>
                            <?php elseif ($this->session->userdata('jobdesk') == 'LOGISTIK') : ?>
                                <a href="<?= base_url('logistik') ?>" class="btn btn-primary mb-2 ml-2"><i class="fas fa-arrow-circle-left"></i></a>
                            <?php endif; ?>
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
                                    <div class="row mb-4">
                                        <div class="col-auto">
                                            <h2>Detail Orders</h2>
                                        </div>
                                        <div class="col-auto">
                                            <?php if ($d->status == '1') : ?>
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <span class="btn btn-warning disabled">Draft</span>
                                                    </div>
                                                    <div class="col-auto">
                                                        <?php foreach ($kdo as $k) : ?>
                                                            <a href="<?= base_url('list_faktur/') . $k->kd_do ?>" class="btn btn-info">
                                                                <i class="fas fa-plus"></i> Tambah Faktur
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                            <?php elseif ($d->status == '2') : ?>
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <span class="btn btn-info disabled">
                                                            <i class="fas fa-clock"></i> Menunggu Konfirmasi Sales
                                                        </span>
                                                    </div>
                                                    <div class="col-auto">
                                                        <button type="button" class="btn btn-warning" id="btnunpost" data-kd="<?= $d->kd_do ?>">
                                                            <i class="fas fa-redo"></i> REPOST
                                                        </button>
                                                    </div>
                                                    <div class="col-auto">
                                                        <?php foreach ($kdo as $k) : ?>
                                                            <a href="<?= base_url('list_faktur/') . $k->kd_do ?>" class="btn btn-info">
                                                                <i class="fas fa-plus"></i> Tambah Faktur
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                            <?php elseif ($d->status == '3') : ?>
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <span class="btn btn-success disabled">
                                                            <i class="fas fa-check-circle"></i> Siap Loading
                                                        </span>
                                                    </div>
                                                    <div class="col-auto">
                                                        <button type="button" class="btn btn-warning" id="btnunpost" data-kd="<?= $d->kd_do ?>">
                                                            <i class="fas fa-redo"></i> REPOST
                                                        </button>
                                                    </div>
                                                    <div class="col-auto">
                                                        <?php foreach ($kdo as $k) : ?>
                                                            <a href="<?= base_url('list_faktur/') . $k->kd_do ?>" class="btn btn-info">
                                                                <i class="fas fa-plus"></i> Tambah Faktur
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="col-auto">
                                                        <small class="text-muted">
                                                            Dikonfirmasi oleh: <strong><?= htmlspecialchars($d->sales_confirm_by ?? '-') ?></strong>
                                                        </small>
                                                    </div>
                                                </div>

                                            <?php elseif ($d->status == '4') : ?>
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <span class="btn btn-primary disabled">
                                                            <i class="fas fa-truck-loading"></i> Is Loading
                                                        </span>
                                                    </div>
                                                </div>

                                            <?php elseif ($d->status == '5') : ?>
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <span class="btn btn-dark disabled">
                                                            <i class="fas fa-truck"></i> On Delivery
                                                        </span>
                                                    </div>
                                                </div>

                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($d->sales_confirm_note)) : ?>
                                        <div class="alert alert-info py-2 mb-3">
                                            <strong><i class="fas fa-sticky-note mr-1"></i> Catatan Sales:</strong>
                                            <?= nl2br(htmlspecialchars($d->sales_confirm_note, ENT_QUOTES, 'UTF-8')) ?>
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
                                        <?php foreach ($dostatus as $ds) : $date = date('d/m/Y') ?>
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
                                        <div><a href="#" data-toggle="modal" data-target="#edited_rute" class="btn btn-warning btn-sm ml-2"><i class="fas fa-pencil-alt"></i></a></div>
                                    </div>

                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Customer</div>
                                        <div>: <?= $k->totalfaktur ?></div>
                                    </div>

                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Barang</div>
                                        <div>: <?= $k->total_barang ?></div>
                                    </div>

                                    <!-- ============================================================
                                        TONASE & KUBIKASI — dengan progress bar & kuota
                                    ============================================================ -->
                                    <?php
                                        // Batas default (sesuai konstanta di M_SalesOrder)
                                        $batas_ton = 6;    // ton
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

                                    <?php if ($this->session->userdata('jobdesk') == 'LOGISTIK' && ($d->status == '1' || $d->status == '2' || $d->status == '3')) : ?>
                                        <?php
                                            $tgl_pengiriman = '';
                                            if (!empty($d->tgl_pengiriman) && $d->tgl_pengiriman !== '0000-00-00') {
                                                $tgl_pengiriman_ts = strtotime($d->tgl_pengiriman);
                                                $tgl_pengiriman = $tgl_pengiriman_ts ? date('Y-m-d', $tgl_pengiriman_ts) : '';
                                            }

                                            $is_luar = false;
                                            if (!empty($d->driver) && !empty($d->nolambung)) {
                                                $driver_exists = false;
                                                foreach ($driver as $drv_check) {
                                                    if ((string)$drv_check->kd_driver === (string)$d->driver) {
                                                        $driver_exists = true;
                                                        break;
                                                    }
                                                }

                                                $truck_exists = false;
                                                foreach ($truck as $trk_check) {
                                                    if ((string)$trk_check->id === (string)$d->nolambung) {
                                                        $truck_exists = true;
                                                        break;
                                                    }
                                                }

                                                $is_luar = !$driver_exists || !$truck_exists;
                                            }
                                        ?>
                                        <div class="border rounded p-3 mb-3">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group mb-2">
                                                        <label for="tgl_isi">Tanggal Pengiriman</label>
                                                        <input type="date" class="form-control" name="tgl_isi" id="tgl_isi" value="<?= htmlspecialchars($tgl_pengiriman, ENT_QUOTES, 'UTF-8') ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group mb-2">
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
                                                    <div class="form-group mb-2">
                                                        <label for="driver_isi">Driver</label>
                                                        <select class="form-control" name="driver_isi" id="driver_isi">
                                                            <option value="">Pilih Driver</option>
                                                            <?php foreach ($driver as $drv) : ?>
                                                                <option value="<?= htmlspecialchars($drv->kd_driver, ENT_QUOTES, 'UTF-8') ?>" <?= (string)$d->driver === (string)$drv->kd_driver ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($drv->nama_driver, ENT_QUOTES, 'UTF-8') ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 d-none" id="input_driver_luar_wrapper">
                                                    <div class="form-group mb-2">
                                                        <label for="driver_luar_isi">Driver Luar</label>
                                                        <input type="text" class="form-control" name="driver_luar_isi" id="driver_luar_isi" value="<?= $is_luar ? htmlspecialchars($d->driver, ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="Nama driver">
                                                    </div>
                                                </div>
                                                <div class="col-md-3" id="select_truck_wrapper">
                                                    <div class="form-group mb-2">
                                                        <label for="truck_isi">Kendaraan</label>
                                                        <select class="form-control" name="truck_isi" id="truck_isi">
                                                            <option value="">Pilih Kendaraan</option>
                                                            <?php foreach ($truck as $trk) : ?>
                                                                <option value="<?= htmlspecialchars($trk->id, ENT_QUOTES, 'UTF-8') ?>" <?= (string)$d->nolambung === (string)$trk->id ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($trk->noplat, ENT_QUOTES, 'UTF-8') ?><?= !empty($trk->nm_truk) ? ' - ' . htmlspecialchars($trk->nm_truk, ENT_QUOTES, 'UTF-8') : '' ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 d-none" id="input_truck_luar_wrapper">
                                                    <div class="form-group mb-2">
                                                        <label for="truck_luar_isi">Kendaraan Luar</label>
                                                        <input type="text" class="form-control" name="truck_luar_isi" id="truck_luar_isi" value="<?= $is_luar ? htmlspecialchars($d->nolambung, ENT_QUOTES, 'UTF-8') : '' ?>" placeholder="No. plat / kendaraan">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <!-- END FORM -->
                                <?php if ($this->session->userdata('jobdesk') == 'LOGISTIK') : ?>
                                    <table class="table table-bordered" id="tb_checker_do">
                                        <thead>
                                            <tr>
                                               <?php if ($d->status == '1' || $d->status == '2' || $d->status == '3') : ?>
                                                    <th rowspan="2">#</th>
                                                <?php endif; ?>

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

                                            foreach ($data_list as $row) {
                                                if (!isset($rowspan_count[$row->kd_faktur])) {
                                                    $rowspan_count[$row->kd_faktur] = 0;
                                                }
                                                $rowspan_count[$row->kd_faktur]++;
                                            }

                                            $printed_faktur = [];
                                            foreach ($data_list as $row) :
                                                $show_faktur_info = !in_array($row->kd_faktur, $printed_faktur);
                                                if ($show_faktur_info) {
                                                    $printed_faktur[] = $row->kd_faktur;
                                                    $norut_counter = 1;
                                                }
                                            ?>
                                                <tr>
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
                                                        <?php if ($d->status == '1' || $d->status == '2' || $d->status == '3') : ?>
                                                            <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>">
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <a href="<?= base_url('cancel_fk/' . $row->kd_faktur . '/' . $row->kd_do) ?>" class="btn btn-sm btn-block btn-danger"><i class="fas fa-times-circle"></i></a>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <button type="button" class="btn btn-sm btn-block btn-primary btn-edit-note-faktur" data-toggle="modal" data-target="#modal_note_faktur" data-kd_faktur="<?= htmlspecialchars($row->kd_faktur, ENT_QUOTES, 'UTF-8') ?>" data-note_faktur="<?= htmlspecialchars($row->note_faktur, ENT_QUOTES, 'UTF-8') ?>">
                                                                            <i class="fas fa-envelope"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        <?php elseif ($d->status == '2') : ?>
                                                        <?php endif; ?>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->nama_kios ?><br><?= "(" . $telp1 . "/" . $telp2 . ")" ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->regional ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_rute ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_faktur ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->tgl_transaksi ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->note_faktur != '' ? $row->note_faktur : '-' ?></td>

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

                                            <?php if ($bisa_edit) : ?>

                                                <!-- Rekam / Simpan -->
                                                <div class="col">
                                                    <button type="button" class="btn btn-success w-100 mt-3" id="draftpost">
                                                        <i class="fas fa-check-double"></i>
                                                        <?= ($d->status == '3') ? 'Rekam Order' : (($d->status == '2') ? 'Perbarui & Kirim' : 'Rekam Draft Order') ?>
                                                    </button>
                                                </div>
                                            <?php elseif ($sudah_siap) : ?>
                                                <div class="col">
                                                    <button type="button" class="btn btn-info btn-block mt-3"
                                                            id="btnPrintOrder1" data-kd="<?= $k->kd_do ?>">
                                                        <i class="fas fa-print"></i> Print DO
                                                    </button>
                                                </div>
                                                <div class="col">
                                                    <button type="button" class="btn btn-primary btn-block mt-3"
                                                            id="btnPrintRegis1" data-kd="<?= $k->kd_do ?>">
                                                        <i class="fas fa-print"></i> Print Register
                                                    </button>
                                                </div>  
                                                <div class="col">
                                                    <button type="button" class="btn btn-warning btn-block mt-3"
                                                            id="btnPrintChecker1" data-kd="<?= $k->kd_do ?>">
                                                        <i class="fas fa-print"></i> Print Checker
                                                    </button>
                                                </div>

                                            <?php else : ?>

                                                <div class="col">
                                                    <div class="alert alert-info mt-3 mb-0">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        Menunggu konfirmasi Sales. Tombol print akan tersedia setelah Sales mengkonfirmasi Siap Loading.
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
                $("#driver_isi, #truck_isi").prop("required", false);
                $("#driver_luar_isi, #truck_luar_isi").prop("required", true);
            } else {
                $("#select_driver_wrapper, #select_truck_wrapper").removeClass("d-none");
                $("#input_driver_luar_wrapper, #input_truck_luar_wrapper").addClass("d-none");
                $("#driver_isi, #truck_isi").prop("required", true);
                $("#driver_luar_isi, #truck_luar_isi").prop("required", false);
            }
        }

        function resetFieldBorder() {
            $(".form-control").css("border", "");
        }

        function validatePengirimanInput(tgl_krim, driver, platno) {
            var valid = true;
            var luar = isExpedisiLuar();

            if (!tgl_krim) {
                $("#tgl_isi").css("border", "2px solid red");
                valid = false;
            }

            if (!driver) {
                if (luar) {
                    $("#driver_luar_isi").css("border", "2px solid red");
                } else {
                    $("#driver_isi").css("border", "2px solid red");
                }
                valid = false;
            }

            if (!platno) {
                if (luar) {
                    $("#truck_luar_isi").css("border", "2px solid red");
                } else {
                    $("#truck_isi").css("border", "2px solid red");
                }
                valid = false;
            }

            return valid;
        }

        $(document).ready(function() {
            togglePengirimanFields();
            $("input[name='jenis_pengiriman']").on("change", togglePengirimanFields);

            $("#modal_note_faktur").on("show.bs.modal", function(event) {
                var button = $(event.relatedTarget);
                var kdFaktur = button.data("kd_faktur") || "";
                var noteFaktur = button.data("note_faktur") || "";

                $("#modal_kd_faktur").val(kdFaktur);
                $("#modal_note_faktur_input").val(noteFaktur);
            });

            $("#draftpost").on('click', function() {
                var kd_do = $("#do_isi").val().trim();
                var tgl_krim = $("#tgl_isi").val();
                var platno = getTruckValue();
                var driver = getDriverValue();
                var jenis_pengiriman = getJenisPengiriman();

                resetFieldBorder();

                if (!validatePengirimanInput(tgl_krim, driver, platno)) {
                    alert("Lengkapi semua field terlebih dahulu.");
                    return;
                }

                $.ajax({
                    url: "<?= base_url('rekam_order_check') ?>",
                    type: "POST",
                    data: {
                        kd_do: kd_do,
                        tgl_krim: tgl_krim,
                        platno: platno,
                        driver: driver,
                        jenis_pengiriman: jenis_pengiriman
                    },
                    dataType: "JSON",
                    success: function(data) {
                        console.log(data);
                        if (data.msg === "success") {
                            alert('Data berhasil direkam');
                            window.location.href = "<?= base_url('detail_do/') ?>" + kd_do;
                        } else {
                            alert(data.message || 'Ada kesalahan data');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Terjadi kesalahan: ' + error);
                    }
                });
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

        $("#btnPrintOrder").on('click', function() {
            var kd_do = $(this).data('kd');
            var tgl_krim = $("#tgl_isi").val();
            var driver = getDriverValue();
            var platno = getTruckValue();

            resetFieldBorder();

            if (!validatePengirimanInput(tgl_krim, driver, platno)) {
                alert("Lengkapi semua field terlebih dahulu sebelum print.");
                return;
            }

            var printUrl = "<?= base_url('print_do/') ?>" + kd_do +
                "?tgl_kirim=" + encodeURIComponent(tgl_krim) +
                "&driver=" + encodeURIComponent(driver) +
                "&plat=" + encodeURIComponent(platno);

            window.open(printUrl, "_blank");
        });


        $("#btnPrintRegis").on('click', function() {
            var kd_do = $(this).data('kd');
            var tgl_krim = $("#tgl_isi").val();
            var driver = getDriverValue();
            var platno = getTruckValue();

            resetFieldBorder();
            if (!validatePengirimanInput(tgl_krim, driver, platno)) {
                alert("Lengkapi semua field terlebih dahulu sebelum print.");
                return;
            }
            var printUrl = "<?= base_url('print_regis/') ?>" + kd_do +
                "?tgl_kirim=" + encodeURIComponent(tgl_krim) +
                "&driver=" + encodeURIComponent(driver) +
                "&plat=" + encodeURIComponent(platno);
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

        $("#btnPrintChecker").on('click', function() {
            var kd_do = $(this).data('kd');
            var tgl_krim = $("#tgl_isi").val();
            var driver = getDriverValue();
            var platno = getTruckValue();

            resetFieldBorder();
            if (!validatePengirimanInput(tgl_krim, driver, platno)) {
                alert("Lengkapi semua field terlebih dahulu sebelum print.");
                return;
            }
            var printUrl = "<?= base_url('print_checker/') ?>" + kd_do +
                "?tgl_kirim=" + encodeURIComponent(tgl_krim) +
                "&driver=" + encodeURIComponent(driver) +
                "&plat=" + encodeURIComponent(platno);
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
