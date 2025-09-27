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
                    <div class="row">
                        <div class="col-auto">
                            <a href="<?= base_url('usrstockopname') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-home"></i> Dashboard</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('op_usropname_input') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-tasks"></i> Histori Inputer</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="container-fluid">
                                <h4>Histori Input User</h4>
                                <table class="table table-bordered table-sm" id="tb_dash_fefo">
                                    <thead>
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Qty</th>
                                            <th>Qty Pcs</th>
                                            <th>Input At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inputopname as $row) : ?>
                                            <tr>
                                                <td><?= $row->nama_barang ?></td>
                                                <td><?= $row->qty ?></td>
                                                <td><?= $row->qty_pcs ?></td>
                                                <td><?= $row->input_at ?></td>
                                            </tr>
                                        <?php endforeach ?>
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