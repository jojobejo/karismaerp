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
                            <a href="<?= base_url('ics/ics_diffrent') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-arrow-left"></i></a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/icsdo') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-minus-circle"></i> Data DO</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/icspo') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-plus-circle"></i> Data LPB</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="container-fluid">

                                <div class="row mb-2">
                                    <div class="col-2">
                                        <button class="btn btn-success mb-3 btn-block" data-toggle="modal" data-target="#modalImportCSV">
                                            <i class="fas fa-file-csv"></i> Import CSV
                                        </button>
                                    </div>
                                    <div class="col-2">
                                        <a class="btn btn-secondary mb-3 btn-block" href="<?= base_url('data_lpb_zahir') ?>">
                                            <i class="fas fa-file-csv"></i> Data LPB
                                        </a>
                                    </div>
                                </div>

                                <form action="<?= base_url('get_lpb') ?>" method="post">
                                    <div class="row mb-2">
                                        <div class="col-2">
                                            <input type="date" class="form-control" name="date1" id="name1">
                                        </div>
                                        <div class="col-2">
                                            <input type="date" class="form-control" name="date2" id="name2">
                                        </div>
                                        <div class="col-2">
                                            <button class="btn btn-success btn-block">Tampil</button>
                                        </div>
                                    </div>
                                </form>

                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>No Refrensi</th>
                                            <th>Kode Supp</th>
                                            <th>Gudang</th>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Qty</th>
                                            <th>Satuan</th>
                                            <th>Nolot</th>
                                            <th>Exp Date</th>
                                            <th>note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($lpb)) : ?>
                                            <?php foreach ($lpb as $row) : ?>
                                                <tr>
                                                    <td><?= $row['TGL'] ?? '' ?></td>
                                                    <td><?= $row['NO_REFERENSI'] ?? '' ?></td>
                                                    <td><?= $row['KODE_SUPP'] ?? '' ?></td>
                                                    <td><?= $row['NAMAGUDANG'] ?? '' ?></td>
                                                    <td><?= $row['KODE'] ?? '' ?></td>
                                                    <td><?= $row['NAMA_BARANG'] ?? '' ?></td>
                                                    <td><?= number_format($row['QTY'] ?? 0) ?></td>
                                                    <td><?= $row['SATUAN'] ?? '' ?></td>
                                                    <td><?= $row['NO_LOT'] ?? '' ?></td>
                                                    <td><?= $row['TANGGAL_EXP'] ?? '' ?></td>
                                                    <td><?= $row['NOTE'] ?? '' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-danger">Tidak ada data pada periode ini</td>
                                            </tr>
                                        <?php endif; ?>
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