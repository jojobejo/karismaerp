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

                    <div class="card">
                        <div class="card-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-auto">
                                        <a class="btn btn-primary mb-3" href="<?= base_url('ics/icspo') ?>">
                                            <i class="fas fa-home"></i>
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <a class="btn btn-secondary mb-3 " href="<?= base_url('ics/retur/penjualan') ?>">
                                            <i class="fas fa-file-csv"></i> Retur
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <a class="btn btn-success mb-3 " href="<?= base_url('ics/retur/penjualan') ?>">
                                            <i class="fas fa-file-csv"></i> Retur Penjualan
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <a class="btn btn-success mb-3 " href="<?= base_url('ics/retur/pembelian') ?>">
                                            <i class="fas fa-file-csv"></i> Retur Pembelian
                                        </a>
                                    </div>
                                </div>

                                <table class="table table-bordered" id="tb_ics_po">
                                    <thead class="bg-primary text-white text-center">
                                        <tr>
                                            <th>Tanggal Retur</th>
                                            <th>Retur</th>
                                            <th>Note Retur</th>
                                            <th>Total Barang</th>
                                            <th>Status</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
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