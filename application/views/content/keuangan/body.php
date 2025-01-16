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
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->
            <section class="content">
                <div class="container-fluid">
                    <?php if ($this->session->userdata('lv') == 1) : ?>
                        <a href="<?= base_url('insertmodule') ?>" class="btn btn-primary mb-2">Update Data Stock</a>
                        <a href="<?= base_url('gudang/1') ?>" class="btn btn-success mb-2">Gudang Global</a>
                        <a href="<?= base_url('gudang/2') ?>" class="btn btn-success mb-2">Gudang Induk</a>
                        <a href="<?= base_url('gudang/3') ?>" class="btn btn-success mb-2">Gudang Rusak</a>
                    <?php else : ?>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <table id="tbgudang" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <td>Total Data</td>
                                        <td>Gdg.Global</td>
                                        <td>Gdg.Induk</td>
                                        <td>Gdg.Rusak</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($count_gudang as $c) : ?>
                                        <tr>
                                            <td><?= $c->total_data ?></td>
                                            <td><?= $c->global ?></td>
                                            <td><?= $c->induk ?></td>
                                            <td><?= $c->rusak ?></td>
                                        </tr>
                                    <?php endforeach; ?>
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