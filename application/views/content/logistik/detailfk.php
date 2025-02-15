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
                    <div class="row">
                        <a href="<?= base_url('create_do') ?>" class="btn btn-primary mb-2 ml-2"><i class="fas fa-arrow-circle-left"></i></a>
                        <?php foreach ($customer as $c) :
                            $status_faktur = $c->data_sts;
                            $status_upload = $c->upload_sts;
                        ?>
                            <h3 class="ml-4" style="font-weight: bold; font-size: xx-large;"><?= $c->nama_kios ?> || <?= $c->regional ?></h3>
                            <?php if ($status_faktur == 1) : ?>
                                <h3 class="ml-4"><span class="badge badge-secondary">NOT IN DRAFT</span></h3>
                            <?php elseif ($status_faktur == 2) : ?>
                                <h3 class="ml-4"><span class="badge badge-success">ON DRAFT LIST</span></h3>
                            <?php endif; ?>
                            <?php if ($status_upload == '1') : ?>
                                <h3 class="ml-4"><span class="badge badge-info">PAGI</span></h3>
                            <?php else : ?>
                                <h3 class="ml-4"><span class="badge badge-dark">SORE</span></h3>
                            <?php endif; ?>


                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <table id="" class="table table-striped">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>QTY</th>
                                        <th>Satuan</th>
                                        <th>No-Lot</th>
                                        <th>Exp Date</th>
                                        <th>#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detail_fk as $det) : ?>
                                        <tr>
                                            <td><?= $det->kd_barang ?></td>
                                            <td><?= $det->nm_barang ?></td>
                                            <td><?= $det->qty ?></td>
                                            <td><?= $det->satuan ?></td>
                                            <td><?= $det->no_lot ?></td>
                                            <td><?= $det->tgl_exp ?></td>
                                            <?php if ($status_faktur == '2') : ?>
                                                <?php if ($det->barang_sts == '1') : ?>
                                                    <td>
                                                        <h3><span class="badge badge-success w-100"><i class="fas fa-certificate"></i></span></a></h3>
                                                    </td>
                                                <?php elseif ($det->barang_sts == '3') : ?>
                                                    <td colspan="2">
                                                        <h3><a><span class="badge badge-warning w-100"><i class="fas fa-pause-circle"></i></span></a></h3>
                                                    </td>
                                                <?php elseif ($det->barang_sts == '2') : ?>
                                                    <td colspan="2">
                                                        <h3><span class="badge badge-success w-100"><i class="fas fa-certificate"></i></span></a></h3>
                                                    </td>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <?php if ($det->barang_sts == '1') : ?>
                                                    <td>
                                                        <a href="<?= base_url('pnd_br_detpo/') . $det->id . '/' . $kdfaktur . '/' . 'pending' ?>" class="btn btn-danger w-100"><i class="fas fa-trash"></i></a>
                                                    </td>
                                                <?php elseif ($det->barang_sts == '3') : ?>
                                                    <td colspan="2">
                                                        <h3><a href="<?= base_url('pnd_br_detpo/') . $det->id . '/' . $kdfaktur . '/' . 'revert' ?>"><span class="badge badge-warning w-100"><i class="fas fa-pause-circle"></i></span></a></h3>
                                                    </td>
                                                <?php elseif ($det->barang_sts == '2') : ?>
                                                    <td colspan="2">
                                                        <h3><span class="badge badge-success w-100"><i class="fas fa-certificate"></i></span></a></h3>
                                                    </td>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if ($status_faktur == '1') : ?>
                                <a href="<?= base_url('insert_tmp/') . $kdfaktur . '/' . 'formdetail' ?>" class="btn btn-success btn-block mt-4 mb-2">Input To Draft</a>
                            <?php else : ?>
                                <a href="<?= base_url('revert_do/') . $kdfaktur ?>" class="btn btn-warning btn-block mt-4 mb-2">Revert DO</a>
                            <?php endif; ?>
                        <?php endforeach; ?>
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