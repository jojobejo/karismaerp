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

                    <div class="row">
                        <div class="col-auto ml-2">
                            <h3>Dashboard Delivery Order || </h3>
                        </div>
                        <div class="col-auto">
                            <h3>Last Updated : -</h3>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-auto">
                            <a href="#" class="btn btn-primary mb-2" data-toggle="modal" data-target="#muploadlog">Update Data DO</a>
                        </div>
                        <div class="col-auto">
                            <a href="https://10.10.10.12/zahirdigital/keuangan/export_do_cb.php" class="btn btn-info mb-2">Ambil Data Penjualan(TODAY)</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('create_do') ?>" class="btn btn-success mb-2">Add Delivery Order</a>
                        </div>
                    </div>

                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <table id="tbDashboardLogistik" class="table table-bordered table-striped">
                                <h3>Delivery Order List</h3>
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td>Kode DO</td>
                                        <td>Tgl. Buat</td>
                                        <td>Rute</td>
                                        <td>Total Faktur</td>
                                        <td>Total Barang</td>
                                        <td>Status</td>
                                        <td>#</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listdo as $i) :
                                        $status = $i->status;
                                        if ($status == '1') {
                                            $datasts = '<div class="col"><a href="#" class="btn btn-sm btn-warning btn-block">Draft</a></div>';
                                        } else if ($status == '2') {
                                            $datasts = '<div class="col"><a href="#" class="btn btn-sm btn-info btn-block">On Delivery</a></div>';
                                        } else if ($status == '3') {
                                            $datasts = '<div class="col"><a href="#" class="btn btn-sm btn-success btn-block">On Delivery</a></div>';
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $i->kddo ?></td>
                                            <td><?= $i->createat ?></td>
                                            <td><?= $i->rute ?></td>
                                            <td><?= $i->totalfaktur ?></td>
                                            <td><?= $i->totalbarang ?></td>
                                            <td>
                                                <?= $datasts ?>
                                            </td>
                                            <?php if ($i->status == '1') : ?>
                                                <td>
                                                    <a href="<?= base_url('detail_do/') . $i->kddo ?>" class="btn btn-sm btn-info btn-block"><i class="fas fa-eye"></i></a>
                                                </td>
                                            <?php elseif ($i->status == '2') : ?>
                                                <td>
                                                    <div class="row">
                                                        <div class="col">
                                                            <a href="<?= base_url('detail_do/') . $i->kddo ?>" class="btn btn-sm btn-info btn-block"><i class="fas fa-eye"></i></a>
                                                        </div>
                                                        <div class="col">
                                                            <a href="<?= base_url('printdo/') . $i->kddo ?>" class="btn btn-sm btn-success btn-block"><i class="fas fa-print"></i></a>
                                                        </div>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- /.content-header -->
            <!-- <section class="content">
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
            </section> -->


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