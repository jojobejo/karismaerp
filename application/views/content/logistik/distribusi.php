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
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-auto">
                            <a href="<?= base_url('histori_driver') ?>" class="btn btn-primary mb-2">Histori Driver</a>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="m-3">
                    <table id="tbtotal_tonase" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Rute</th>
                                <th>Tonase Terkirim</th>
                                <th>Tonase Pending</th>
                                <th>Total Tonase</th>
                                <th>Faktur Terkirim</th>
                                <th>Faktur Pending</th>
                                <th>Total Faktur</th>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($total_tonase as $tot) : ?>
                                <tr>
                                    <td><?= $tot->rute ?></td>
                                    <td><?= $tot->tonase_terkirim ?></td>
                                    <td><?= $tot->tonase_belum_terkirim ?></td>
                                    <td><?= $tot->total_tonase ?></td>
                                    <td><?= $tot->total_faktur_terkirim ?></td>
                                    <td><?= $tot->total_faktur_pending ?></td>
                                    <td><?= $tot->total_faktur ?></td>
                                    <td><a href="<?= base_url('detail_tonase/') . $tot->rute ?>" class="btn btn-info btn-block"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md">
                                <label>Rute</label>
                                <select name="s_rute" id="s_rute" class="form-control">
                                    <option value="">-- Semua Rute --</option>
                                    <?php foreach ($all_rute as $rute) : ?>
                                        <option value="<?= $rute->kd_rute ?>"><?= $rute->kd_rute ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md">
                                <label>Rentang Tanggal</label>
                                <input type="text" id="filter_tanggal" class="form-control" placeholder="YYYY-MM-DD - YYYY-MM-DD">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <table class="table table-bordered table-striped mt-3">
                        <thead>
                            <tr>
                                <th>Rute</th>
                                <th>Tanggal Pengiriman</th>
                            </tr>
                        </thead>
                        <tbody id="result_data">
                            <tr>
                                <td colspan="2" class="text-center">Silakan pilih filter</td>
                            </tr>
                        </tbody>
                    </table>
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

    <script>
        $('#filter_tanggal').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
    </script>