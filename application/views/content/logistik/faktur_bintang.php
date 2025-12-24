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
            <div class="card m-2">
                <div class="card-body">
                    <h3>Faktur Bintang Putri Karisma</h3>
                    <div class="row">
                        <div class="col-4">
                            <table id="tbfakturbintang" class="table table-bordered table-striped">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td>Kode DO</td>
                                        <td>Rute</td>
                                        <td>Tanggal Transaksi</td>
                                        <td>#</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fakturbintang as $fb) : ?>
                                        <tr>
                                            <td><?= $fb->kd_faktur ?></td>
                                            <td><?= $fb->kd_rute ?></td>
                                            <td><?= $fb->tgl_inputer ?></td>
                                            <td><?= $fb->id ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-8">
                            <div class="card card-outline card-primary">
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label class="col-sm-12 text-left">Tanggal <span class="required">*</span></label>
                                        <div class="col-sm-12">
                                            <input class="form-control" type="date" id="tanggal" name="tanggal">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
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