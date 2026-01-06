<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">


                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <div class="row">
                                    <label for="id_bar">No Ref : </label>
                                    <input class="form-control col-4 ml-4" type="text" id="driver_i" name="driver_i" value="" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label for="id_bar">Tanggal : </label>
                                    <input class="form-control col-4 ml-4" type="date" id="driver_i" name="driver_i" value="" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label for="id_bar">Keterangan : </label>
                                    <input class="form-control col-4 ml-4" type="text" id="driver_i" name="driver_i" value="" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="row">
                                            <label for="id_bar">Dari Gudang : </label>
                                            <input class="form-control col-4 ml-4" type="text" id="driver_i" name="driver_i" value="" />
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="row">
                                            <label for="id_bar">Ke Gudang : </label>
                                            <input class="form-control col-4 ml-4" type="text" id="driver_i" name="driver_i" value="" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-auto mt-4">
                                <table id="input_tmp_mutasi" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
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