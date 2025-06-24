<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">

                    <div class="row">
                        <div class="col-auto">
                            <a href="<?= base_url('admstocktracking') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-home"></i></a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('exportcsv_track_opname_allbarang') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="far fa-file-excel"></i> Export CSV</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="mt-2">Compare Tim - AllBarang</h5>
                            <table border="1" cellpadding="8" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Tim 1</th>
                                        <th>Tim 2</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Total Barang</strong></td>
                                        <td><?= $stat_t1['total_barang'] ?></td>
                                        <td><?= $stat_t2['total_barang'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Match</strong></td>
                                        <td><?= $stat_t1['total_match'] ?></td>
                                        <td><?= $stat_t2['total_match'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Not Match</strong></td>
                                        <td><?= $stat_t1['total_notmatch'] ?></td>
                                        <td><?= $stat_t2['total_notmatch'] ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Persentase Match</strong></td>
                                        <td><?= $stat_t1['persen_match'] ?>%</td>
                                        <td><?= $stat_t2['persen_match'] ?>%</td>

                                    </tr>
                                    <tr>
                                        <td><strong>Persentase Not Match</strong></td>
                                        <td><?= $stat_t1['persen_notmatch'] ?>%</td>
                                        <td><?= $stat_t2['persen_notmatch'] ?>%</td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered table-sm" id="tb_dash_allbarang">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>TIM 1</th>
                                        <th>TIM 2</th>
                                        <th>Status</th>
                                        <th>#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allbarang as $row) : ?>
                                        <tr>
                                            <td><?= $row->nm_barang ?></td>
                                            <td><?= $row->qty_fisik_tim1 ?></td>
                                            <td><?= $row->qty_fisik_tim2 ?></td>
                                            <td>
                                                <span class="badge <?= $row->status == 'MATCH' ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $row->status ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('detailtrack/') . $row->kd_barang . '/allbarang' ?>" class="badge bg-primary"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>

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