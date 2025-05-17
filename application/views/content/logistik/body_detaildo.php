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
                            <a href="<?= base_url('logistik') ?>" class="btn btn-primary mb-2 ml-2"><i class="fas fa-arrow-circle-left"></i></a>
                            <!-- <h3>TITLE</h3> -->
                            <h3></h3>
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
                                <div class="row mb-4">
                                    <div class="col-auto">
                                        <h2>Detail Orders</h2>
                                    </div>
                                    <div class="col-auto">
                                        <?php if ($d->status == '1') : ?>
                                            <a href="#" class="btn btn-warning">ON PROGRESS</a>
                                        <?php elseif ($d->status == '2') : ?>
                                            <a href="#" class="btn btn-info">DONE</a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php foreach ($kdo as $k) :
                                    $totton = $k->total_tonase_faktur / 1000;
                                ?>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Kode Faktur</div>
                                        <div>: <?= $k->kd_do ?></div>
                                    </div>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Regional Pengiriman</div>
                                        <div>: <?= $k->regional ?></div>
                                    </div>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Customer</div>
                                        <div>: <?= $k->totalfaktur ?></div>
                                    </div>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Barang</div>
                                        <div>: <?= $k->total_barang ?></div>
                                    </div>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Tonase</div>
                                        <div>: <?= $k->total_tonase_faktur . ' (Kg)' . ' ' . '||' . ' ' . $totton . ' ' . '(Ton)' ?></div>
                                    </div>
                                <?php endforeach; ?>

                                <table class="table table-bordered" id="tb_checker_do">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">#</th>
                                            <th colspan="2">Data Kios</th>
                                            <th rowspan="2">Rute</th>
                                            <th colspan="2">TTB</th>
                                            <th rowspan="2">No</th>
                                            <th rowspan="2">Nama Barang</th>
                                            <th rowspan="2">No Lot</th>
                                            <th colspan="2">Qty</th>
                                            <?php if ($d->status == '1') : ?>
                                                <th rowspan="2">Status</th>
                                                <th rowspan="2">#</th>
                                            <?php elseif ($d->status == '2') : ?>
                                            <?php endif; ?>
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
                                                <?php if ($show_faktur_info) : ?>
                                                    <?php if ($d->status == '1') : ?>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><a href="<?= base_url('cancel_fk/' . $row->kd_faktur . '/' . $row->kd_do) ?>" class="btn btn-sm btn-block btn-danger"><i class="fas fa-times-circle"></i></a></td>
                                                    <?php elseif ($d->status == '2') : ?>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><a href="#" class="btn btn-sm btn-block btn-success"><i class="fas fa-thumbs-up"></i></a></td>
                                                    <?php endif; ?>
                                                    <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->nama_kios ?></td>
                                                    <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->regional ?></td>
                                                    <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_rute ?></td>
                                                    <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_faktur ?></td>
                                                    <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->tgl_inputer ?></td>
                                                <?php endif; ?>
                                                <td><?= $norut_counter++ ?></td>
                                                <td><?= $row->nm_barang ?></td>
                                                <td><?= $row->no_lot ?> - <?= $row->tgl_exp ?></td>
                                                <td><?= $row->qty_box ?></td>
                                                <td><?= $row->qty_pcs ?></td>
                                                <?php if ($d->status == '1') : ?>
                                                    <?php if ($row->status == '2') : ?>
                                                        <td><a href="#" class="btn btn-sm btn-block btn-success"></a></td>
                                                    <?php elseif ($row->status == '3') : ?>
                                                        <td><a href="#" class="btn btn-sm btn-block btn-danger"></a></td>
                                                    <?php elseif ($row->status == '1') : ?>
                                                        <td><a href="#" class="btn btn-sm btn-block btn-warning"></a></td>
                                                    <?php endif; ?>
                                                <?php elseif ($d->status == '2') : ?>
                                                <?php endif; ?>

                                                <?php if ($d->status == '1') : ?>
                                                    <?php if ($row->status == '2') : ?>
                                                        <td>
                                                            <a href="#" class="btn btn-info"><i class="fas fa-thumbs-up"></i></a>
                                                            <a href="<?= base_url('acc_check/' . $row->id . '/' . "3" . '/' . $row->kd_do) ?>" class="btn btn-warning"><i class="fas fa-undo"></i></a>
                                                        </td>
                                                    <?php elseif ($row->status == "3") : ?>
                                                        <td>
                                                            <a href="#" class="btn btn-danger"><i class="fas fa-times-circle"></i></a>
                                                            <a href="<?= base_url('acc_check/' . $row->id . '/' . "3" . '/' . $row->kd_do) ?>" class="btn btn-warning"><i class="fas fa-undo"></i></a>
                                                        </td>
                                                    <?php elseif ($row->status == "1") : ?>
                                                        <td>
                                                            <a href="<?= base_url('acc_check/' . $row->id . '/' . "1" . '/' . $row->kd_do) ?>" class="btn btn-success"><i class="fas fa-check-circle"></i></a>
                                                            <a href="<?= base_url('acc_check/' . $row->id . '/' . "2" . '/' . $row->kd_do) ?>" class="btn btn-danger"><i class="fas fa-times-circle"></i></a>
                                                        </td>
                                                    <?php endif; ?>
                                                <?php elseif ($d->status == '2') : ?>
                                                <?php endif; ?>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php foreach ($doprintsts as $ds) : ?>
                                    <?php if ($ds->status == '2') : ?>
                                        <?php foreach ($kdo as $k) : ?>
                                            <a href="<?= base_url('print_do/' . $k->kd_do) ?>" target="_blank" class="btn btn-success btn-block mt-3 mb3">Print Order</a>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <?php foreach ($kdo as $k) : ?>
                                            <a href="<?= base_url('rekam_order_check/' . $k->kd_do) ?>" class="btn btn-success btn-block mt-3 mb3">Rekam Order</a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        <?php endforeach; ?>

        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->