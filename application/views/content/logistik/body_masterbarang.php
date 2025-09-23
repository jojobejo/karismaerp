<style>
    .small-chart {
        width: 50% !important;
        height: auto;
        margin: auto;
        display: block;
    }
</style>

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
                            <a href="<?= base_url('master_barang') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Master Barang</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('compare_opname') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-book-open"></i> Stock Opname Compare</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('opname_datapending') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Data Pending</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('request_opname_admin') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-notes-medical"></i> Request Input</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h1 style="text-align: center;">Master Barang</h1>
                            <div class="dataTables_wrapper dt-bootstrap4">
                                <table id="masterbarang" class="table table-bordered table-striped dataTable dtr-inline" aria-describedby="example1_info">
                                    <thead>
                                        <tr>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Satuan</th>
                                            <th>Dimensi</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($master as $m) :
                                            $dimensi = $m->p * $m->l * $m->t;
                                        ?>
                                            <tr>
                                                <td><?= $m->kode_barang ?></td>
                                                <td><?= $m->nm_barang ?></td>
                                                <td><?= $m->satuan ?></td>
                                                <td><?= $dimensi ?></td>
                                                <td><a href="" class="btn btn-info" data-toggle="modal" data-target="#masterdet<?= $m->id ?>"><i class=" fa fa-eye"></i></a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- MODAL -->
        <!-- <div class="modal fade" id="masterdet<?= $m->id ?>">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Penambahan Nomor Plat Baru</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php echo form_open_multipart('addplat'); ?>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">Nomor Plat<span class="required">*</span></label>
                                <div class="col-sm-8"><input class="form-control" type="text" id="plat_isi" name="plat_isi" value="" /></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">Kode Plat<span class="required">*</span></label>
                                <div class="col-sm-8"><input class="form-control" type="text" id="kd_plat_isi" name="kd_plat_isi" value="" /></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                    </form>
                </div>
            </div>
        </div> -->

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