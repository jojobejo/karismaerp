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
                    <a href="<?= base_url('create_do') ?>" class="btn btn-success mb-2">Add Delivery Order</a>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <table id="tbDashboardLogistik" class="table table-bordered table-striped">
                                <h3>Info Data Update</h3>
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <th>Data Olah</th>
                                        <th>Keterangan</th>
                                        <th>Last Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($updated as $u) :
                                        $infoupdt   = $u->gudangid;
                                        if ($infoupdt == '6') {
                                            $info = "Delivery Order";
                                            if ($u->statusdata == 1) {
                                                $statusdata = 'DO Update PAGI';
                                            } else {
                                                $statusdata = 'DO Update SORE';
                                            }
                                        } else {
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $info ?></td>
                                            <td><?= $statusdata ?></td>
                                            <td><?= format_indo($u->last_update) ?></td>
                                            <td><a href="<?= base_url('truncatelog/') . $u->kd_update . '/' . $u->statusdata ?>" class="btn btn-block btn-sm btn-danger"><i class="fas fa-trash"></i></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <!-- <h3>List Faktur Penjualan</h3>
                            <h3>Faktur ON PROGRESS</h3>
                            <h3>Faktur DONE</h3> -->
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <!-- <div class="container mt-4">
                                <h4 class="text-center">Rencana Pengiriman Barang</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tanggal:</strong> 11/3/2025</p>
                                        <p><strong>No. Kendaraan:</strong> P 9904 UG-2</p>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <p><strong>Cetak:</strong> 15:52</p>
                                        <p><strong>Loading:</strong> <span class="badge badge-success">FIX</span></p>
                                    </div>
                                </div>

                                <h5 class="text-center font-weight-bold">BALI</h5>

                                <table class="table table-bordered">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th rowspan="2">No. Loading</th>
                                            <th rowspan="2">No. SPP</th>
                                            <th rowspan="2">Nama</th>
                                            <th rowspan="2">Kota</th>
                                            <th rowspan="2">Rute</th>
                                            <th colspan="2">TTB</th>
                                            <th rowspan="2">Nama Barang</th>
                                            <th rowspan="2">No. LOT</th>
                                            <th colspan="2">Quantity Invoice</th>
                                        </tr>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Besar</th>
                                            <th>Kecil</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>SPP001</td>
                                            <td>Artha Mandiri</td>
                                            <td>Denpasar</td>
                                            <td>R001</td>
                                            <td>TTB123</td>
                                            <td>11/03/2025</td>
                                            <td>Produk A</td>
                                            <td>LOT56789</td>
                                            <td>10</td>
                                            <td>5</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Checker:</strong> </p>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <p><strong>Driver:</strong> Jefri</p>
                                    </div>
                                </div>
                            </div> -->
                            <table id="tbDashboardLogistik" class="table table-bordered table-striped">
                                <h3>Info Data Update</h3>
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td>Kode DO</td>
                                        <td>Tgl. Buat</td>
                                        <td>Tgl. Kirim</td>
                                        <td>No Kendaraan</td>
                                        <td>Rute</td>
                                        <td>Total Faktur</td>
                                        <td>Total Barang</td>
                                        <td>Status</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                    </tr>
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