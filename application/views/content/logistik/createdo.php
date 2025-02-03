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
                    <a href="<?= base_url('faktur_list') ?>" class="btn btn-success mb-2">List Faktur</a>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h3>Draft DO</h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Tanggal Kirim" value="" name="po_isi" id="po_isi">
                                    </div>
                                </div>
                                <div class="col-md" hidden>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Kode Do" value="" name="do_isi" id="do_isi">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Plat Nomor" value="" name="do_isi" id="do_isi">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Kota Pengiriman" value="" name="do_isi" id="do_isi">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-truck"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Driver" value="" name="do_isi" id="do_isi">
                                    </div>
                                </div>
                            </div>
                            <table id="" class="table table-striped">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td>Faktur</td>
                                        <td>Kios</td>
                                        <td>Alamat</td>
                                        <td>Regional</td>
                                        <td>#</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
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