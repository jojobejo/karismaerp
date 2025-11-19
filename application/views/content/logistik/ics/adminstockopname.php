<style>
    .small-chart {
        width: 50% !important;
        height: auto;
        margin: auto;
        display: block;
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
        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">

                    <div class="row">
                        <!-- <div class="col-auto">
                            <a href="<?= base_url('trackingtim/1') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Tracking Stock TIM 1</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('trackingtim/2') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Tracking Stock TIM 2</a>
                        </div> -->
                        <!-- <div class="col-auto">
                            <a href="<?= base_url('stockopname') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Stock Opname</a>
                        </div> -->
                        <div class="col-auto">
                            <a href="<?= base_url('compare_opname') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-book-open"></i> Stock Opname Compare</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('opname_datapending') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Data Pending</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('request_opname_admin') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-notes-medical"></i> Request Input</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('final_result') ?>" class="btn btn-md btn-success w-100 mb-3"><i class="fa-solid fa-trophy"></i> Final Result</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <!-- BY ALL BARANG -->
                            <h1 style="text-align: center;">RESULT ALL BARANG</h1>
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h3 style="text-align: center;">TIM 1</h3>
                                    <canvas id="pieChartTim1" class="small-chart"></canvas>
                                    <div class="text-center">
                                        <span class="mx-2">All Barang : <?= $stat_t1['total_barang'] ?></span>
                                        <span class="mx-2">Total Match : <?= $stat_t1['total_match'] ?></span>
                                        <span class="mx-2">Total Not : <?= $stat_t1['total_notmatch'] ?></span>
                                    </div>
                                    <div class="text-center">
                                        <span class="mx-2">Match : <?= $stat_t1['persen_match'] ?> %</span>
                                        <span class="mx-2">Not Match : <?= $stat_t1['persen_notmatch'] ?> % </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h3 style="text-align: center;">TIM 2</h3>
                                    <canvas id="pieChartTim2" class="small-chart"></canvas>
                                    <div class="text-center">
                                        <span class="mx-2">All Barang : <?= $stat_t2['total_barang'] ?></span>
                                        <span class="mx-2">Total Match : <?= $stat_t2['total_match'] ?></span>
                                        <span class="mx-2">Total Not : <?= $stat_t2['total_notmatch'] ?></span>
                                    </div>
                                    <div class="text-center">
                                        <span class="mx-2">Match : <?= $stat_t2['persen_match'] ?> %</span>
                                        <span class="mx-2">Not Match : <?= $stat_t2['persen_notmatch'] ?> % </span>
                                    </div>
                                </div>
                            </div>
                            <!-- BY EXPIRED DATE -->
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h1 style="text-align: center;">RESULT FEFO</h1>
                            <div class="row mt-4">

                                <div class="col-md-6">
                                    <h3 style="text-align: center;">TIM 1</h3>
                                    <canvas id="pieChartTim1exp" class="small-chart"></canvas>
                                    <div class="text-center">
                                        <span class="mx-2">All Barang : <?= $statexp_t1['total_barang'] ?></span>
                                        <span class="mx-2">Total Match : <?= $statexp_t1['total_match'] ?></span>
                                        <span class="mx-2">Total Not : <?= $statexp_t1['total_notmatch'] ?></span>
                                    </div>
                                    <div class="text-center">
                                        <span class="mx-2">Match : <?= $statexp_t1['persen_match'] ?> %</span>
                                        <span class="mx-2">Not Match : <?= $statexp_t1['persen_notmatch'] ?> % </span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h3 style="text-align: center;">TIM 2</h3>
                                    <canvas id="pieChartTim2exp" class="small-chart"></canvas>
                                    <div class="text-center">
                                        <span class="mx-2">All Barang : <?= $statexp_t2['total_barang'] ?></span>
                                        <span class="mx-2">Total Match : <?= $statexp_t2['total_match'] ?></span>
                                        <span class="mx-2">Total Not : <?= $statexp_t2['total_notmatch'] ?></span>
                                    </div>
                                    <div class="text-center">
                                        <span class="mx-2">Match : <?= $statexp_t2['persen_match'] ?> %</span>
                                        <span class="mx-2">Not Match : <?= $statexp_t2['persen_notmatch'] ?> % </span>
                                    </div>
                                </div>

                            </div>
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

    