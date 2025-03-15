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
                        <a href="<?= base_url('logistik') ?>" class="btn btn-primary mb-2 ml-2"><i class="fas fa-arrow-circle-left"></i></a>
                        <h3>HALAMAN DETAIL DO</h3>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title">Rencana Pengiriman Barang</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No. Loading</th>
                                        <th>No. SPPB</th>
                                        <th>Nama</th>
                                        <th>Kota</th>
                                        <th>Rute</th>
                                        <th>TTB No</th>
                                        <th>TTB Tanggal</th>
                                        <th>No. Urut</th>
                                        <th>Nama Barang</th>
                                        <th>No. LOT</th>
                                        <th colspan="2">Quantity</th>
                                    </tr>
                                    <tr>
                                        <th colspan="10"></th>
                                        <th>Invoice</th>
                                        <th>Besar</th>
                                        <th>Kecil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>3</td>
                                        <td>786</td>
                                        <td>Artha Mandiri - No 087 859 981 870</td>
                                        <td>Badung</td>
                                        <td>BLI-1</td>
                                        <td>B25003206</td>
                                        <td>11/03/2025</td>
                                        <td>1</td>
                                        <td>Round Up 486 SL 12 X 1 ltr</td>
                                        <td>BP502005-12/2030</td>
                                        <td>50</td>
                                        <td>Box</td>
                                    </tr>
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