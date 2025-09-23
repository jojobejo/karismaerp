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
                            <a href="<?= base_url('compare_opname') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-book-open"></i> Stock Opname Compare</a>
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
                            <h5 class="card-title mt-2">Compare - Allbarang</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm" id="compare_allbarang">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">Nama Barang</th>
                                        <th style="text-align: center;">QTY Fisik</th>
                                        <th style="text-align: center;">QTY Zahir</th>
                                        <th style="text-align: center;">QTY Pending</th>
                                        <th style="text-align: center;">QTY(Pending + Zahir)</th>
                                        <th style="text-align: center;">Status</th>
                                        <th style="text-align: center;">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($compare_all as $all) : ?>
                                        <tr>
                                            <td style="text-align: center;"><?= $all->nama_barang ?></td>
                                            <td style="text-align: center;"><?= $all->qty_fisik ?></td>
                                            <td style="text-align: center;"><?= $all->qty_sistem ?></td>
                                            <td style="text-align: center;"><?= $all->qty_pending ?></td>
                                            <td style="text-align: center;"><?= $all->total_sistem_pending ?></td>
                                            <?php if ($all->status == 'match') : ?>
                                                <td style="text-align: center;"><a href="#" class="btn btn-success w-100"></a></td>
                                            <?php else : ?>
                                                <td style="text-align: center;"><a href="#" class="btn btn-danger w-100"></a></td>
                                            <?php endif; ?>
                                            <td style="text-align: center;">
                                                <a href="<?= base_url('detail_opname/') . $all->kode_barang  ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card card-primary">
                        <div class="card-header">
                            <h5 class="card-title">Compare Tim - Expired Date</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm" id="compare_expired_date">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">#</th>
                                        <th>Nama Barang</th>
                                        <th>Expired Date</th>
                                        <th style="text-align: center;">QTY Fisik</th>
                                        <th style="text-align: center;">QTY Zahir</th>
                                        <th style="text-align: center;">QTY Pending</th>
                                        <th style="text-align: center;">QTY (Zahir + Pending)</th>
                                        <th style="text-align: center;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="text-align: center;"></td>
                                        <td style="text-align: center;"></td>
                                        <td style="text-align: center;"></td>
                                        <td style="text-align: center;"></td>
                                        <td style="text-align: center;"></td>
                                        <td style="text-align: center;"></td>
                                        <td style="text-align: center;"></td>
                                        <td style="text-align: center;"></td>
                                    </tr>
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

    <script>
        $('#btn-export-compare-allbarang').on('click', function() {
            window.location.href = "<?= site_url('export_compare_allbarang') ?>";
        });
    </script>