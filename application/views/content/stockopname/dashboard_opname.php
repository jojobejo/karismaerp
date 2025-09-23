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
                        <div class="col-auto">
                            <a href="<?= base_url('dashboard_opname') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-home"></i>Dashboard</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('master_barang') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Master Barang</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('compare_opname') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-book-open"></i> Stock Opname Compare</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('opname_datapending') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Data Pending</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('request_opname_admin') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-notes-medical"></i> Request Input</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h1 style="text-align: center;">Dashboard Opname</h1>
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h3 style="text-align: center;">Allbarang</h3>
                                    <canvas id="pieChartTim1" class="small-chart"></canvas>
                                    <div class="text-center">
                                        <span class="mx-2">All Barang : </span>
                                        <span class="mx-2">Total Match : </span>
                                        <span class="mx-2">Total Not : </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h3 style="text-align: center;">FEFO</h3>
                                    <canvas id="pieChartTim2" class="small-chart"></canvas>
                                    <div class="text-center">
                                        <span class="mx-2">All Barang : </span>
                                        <span class="mx-2">Total Match :</span>
                                        <span class="mx-2">Total Not : </span>
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