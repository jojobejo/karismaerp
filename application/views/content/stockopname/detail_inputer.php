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
                            <a href="<?= base_url('dashboard_opname') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-home"></i>Dashboard</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('master_barang') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Master Barang</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('compare_opname_all') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-book-open"></i> Stock Compare All</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('compare_opname_exp') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-book-open"></i> Stock Compare Exp</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('opname_datapending') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Data Pending</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('request_opname_admin') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-notes-medical"></i> Request Input</a>
                        </div>
                    </div>

                    <div class="card card-primary mt-2 mb-5">
                        <div class="card-header">
                            <?php foreach ($item_info as $i) : ?>
                                <h5 class="card-title mt-2"><?= $i->nama_barang ?></h5>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm" id="detail_inputer">
                                <thead>
                                    <tr>
                                        <th>Inputer</th>
                                        <th>Qty</th>
                                        <th>Qty BOX</th>
                                        <th>Qty PCS</th>
                                        <th style="text-align: center;">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($item_detail as $d) : ?>
                                        <tr>
                                            <td><?= $d->input_by ?></td>
                                            <td><?= $d->qty ?></td>
                                            <td><?= $d->qty_box ?></td>
                                            <td><?= $d->qty_pcs ?></td>

                                            <td style="width: 10%;">
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <a href="<?= base_url('edit_opname/') . $d->id ?>" class="btn btn-sm btn-success"><i class="fas fa-plus"></i></a>
                                                    </div>
                                                    <div class="col-auto">
                                                        <a href="<?= base_url('edit_opname/') . $d->id ?>" class="btn btn-sm btn-danger"><i class="fas fa-minus"></i></a>
                                                    </div>
                                                </div>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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