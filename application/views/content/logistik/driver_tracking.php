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
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>

            <!-- /.content-header -->
            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <a href="<?= base_url('dashboard') ?>"><i class="fas fa-play fa-flip-horizontal mr-2"></i></a>
                                <h3 class="card-title">Driver List</h3>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="row">
                                <a href="<?= base_url('export_excel')?>" class="btn btn-success m-2 ml-3">
                                    <i class="fas fa-file-excel"></i>
                                    Export Data Excel
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tb_det_list_driver_order" class="table table-bordered table-striped" style="text-align: center;">
                                <thead>
                                    <tr>
                                        <th>Nama Driver</th>
                                        <th>Total Keberangkatan Driver </th>
                                        <th>Total Driver Pending</th>
                                        <th style="text-align: center;">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($driver as $d) : ?>
                                        <tr>
                                            <th><?= $d->nama_driver ?></th>
                                            <th><?= $d->d_ready ?></th>
                                            <th><?= $d->d_pending ?></th>
                                            <th style="text-align: center;">
                                                <a href="<?= base_url('det_driver_tracking/') . $d->kd_driver ?>" class=" btn btn-primary btn-sm ">
                                                    <i class=" fa fa-solid fa-search"></i> Detail Perjalanan
                                                </a>
                                            </th>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

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