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
            <?php $this->load->view('content/logistik/modal/modal_do_upload') ?>

            <div class="content-header">
                <div class="container-fluid">
                    <h3>Dashboard Delivery Order</h3>
                    <a href="#" class="btn btn-primary mb-2" data-toggle="modal" data-target="#muploadlog">Update Data DO</a>
                    <a href="<?= base_url() ?>" class="btn btn-success mb-2">Faktur Terkonfirmasi</a>
                    <a href="<?= base_url() ?>" class="btn btn-success mb-2">Faktur Belum Terkonfirmasi</a>
                </div>
            </div>

            <!-- /.content-header -->
            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <table id="dailyod" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <td>FAKTUR</td>
                                        <td>NAMA CUSTOMER</td>
                                        <td>KIOS</td>
                                        <td>ALAMAT KIOS</td>
                                        <td>REGIONAL</td>
                                        <td>ITEM</td>
                                        <td>#</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_faktur as $l) : ?>
                                        <tr>
                                            <td><?= $l->kd_faktur ?></td>
                                            <td><?= $l->nama_customer ?></td>
                                            <td><?= $l->nama_kios ?></td>
                                            <td><?= $l->alamat_kios ?></td>
                                            <td><?= $l->regional ?></td>
                                            <td><?= $l->total_barang ?></td>
                                            <td><a href="<?= base_url('insert_tmp/') . $l->kd_faktur ?>" class="btn btn-primary btn-block btn-sm"><i class="fas fa-plus"></i></a></td>
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