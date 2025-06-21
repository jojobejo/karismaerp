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
                <?php if ($this->session->userdata('jobdesk') == 'ADMINICS') : ?>
                    <section class="content">
                        <div class="row">
                            <div class="col-auto">
                                <a href="<?= base_url('admstocktracking') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Tracking Stock</a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="container-fluid">
                                    <h4>Dashboard Stock Opname</h4>

                                    <h5 class="mt-4">Opname FEFO</h5>
                                    <table class="table table-bordered table-sm" id="tb_opname_fefo_adm">
                                        <thead>
                                            <tr>
                                                <th>Nama Barang</th>
                                                <th>Expired Date</th>
                                                <th>Qty Fisik</th>
                                                <th>Qty Buku</th>
                                                <th>Qty Pending</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($fefo as $row) : ?>
                                                <tr>
                                                    <td><?= $row->nama_barang ?></td>
                                                    <td><?= $row->exp_date ?></td>
                                                    <td><?= $row->qty_fisik ?></td>
                                                    <td><?= $row->qty_buku ?></td>
                                                    <td><?= $row->qty_pending ?></td>
                                                    <td><span class="badge <?= $row->status == 'MATCH' ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= $row->status ?>
                                                        </span></td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>

                                    <h5 class="mt-5">Opname All Barang</h5>
                                    <table class="table table-bordered table-sm" id="tb_opname_allbarang_adm">
                                        <thead>
                                            <tr>
                                                <th>Nama Barang</th>
                                                <th>Qty Fisik</th>
                                                <th>Qty Buku</th>
                                                <th>Qty Pending</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allbarang as $row) : ?>
                                                <tr>
                                                    <td><?= $row->nama_barang ?></td>
                                                    <td><?= $row->qty_fisik ?></td>
                                                    <td><?= $row->qty_buku ?></td>
                                                    <td><?= $row->qty_pending ?></td>
                                                    <td>
                                                        <span class="badge <?= $row->status == 'MATCH' ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= $row->status ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- <section class="content">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-auto ml-2">
                                    <h3>Stock ICS</h3>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-4">
                                    <a href="" class="btn btn-primary w-100">Data Update</a>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <a href="" class="btn btn-primary w-100">Data Purchase Order</a>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <a href="" class="btn btn-primary w-100">Data Delivery Order</a>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="content">
                        <div class="container-fluid">
                            <div class="card mt-4 mb-2">
                                <div class="card-body">
                                    <div style="overflow-x:auto;">
                                        <table id="tbics" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="background-color: #a0d18c;">NAMA <br> BARANG</th>
                                                    <th rowspan="2" style="background-color: #f4a29b;">Expired <br> Date</th>
                                                    <th colspan="2" style="background-color: #f8805e; color:white;">Out Today</th>
                                                    <th colspan="2" style="background-color: #90d089;">Saldo Awal</th>
                                                    <th colspan="2" style="background-color: #7fd9d1;">16/6/2025</th>
                                                    <th colspan="2" style="background-color: #fca469;">Saldo Akhir</th>
                                                    <th rowspan="2" style="background-color: #f66;">klop</th>
                                                </tr>
                                                <tr>
                                                    <th style="background-color: #f8805e;">Box</th>
                                                    <th style="background-color: #f8805e;">Pcs</th>
                                                    <th style="background-color: #90d089;">Box</th>
                                                    <th style="background-color: #90d089;">Pcs</th>
                                                    <th style="background-color: #7fd9d1;">Box</th>
                                                    <th style="background-color: #7fd9d1;">Pcs</th>
                                                    <th style="background-color: #fca469;">Box</th>
                                                    <th style="background-color: #fca469;">Pcs</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($listics as $list) : ?>
                                                    <tr>
                                                        <td><?= $list->nama_barang ?></td>
                                                        <td><?= $list->exp_date ?></td>
                                                        <td>0</td>
                                                        <td>0</td>
                                                        <td>0</td>
                                                        <td>0</td>
                                                        <td>0</td>
                                                        <td>0</td>
                                                        <td>0</td>
                                                        <td>0</td>
                                                        <td>klop</td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section> -->
                <?php elseif ($this->session->userdata('jobdesk') == 'STOCKOPNAME') : ?>

                    <section class="content">
                        <div class="row">
                            <div class="col-auto">
                                <a href="<?= base_url('stockopname') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-plus"></i> Stock Opname</a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('stkopname_tracking') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Tracking Stock</a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="container-fluid">
                                    <h4>Dashboard Stock Opname</h4>

                                    <h5 class="mt-4">Opname FEFO</h5>
                                    <table class="table table-bordered table-sm" id="tb_opname_fefo_user">
                                        <thead>
                                            <tr>
                                                <th>Nama Barang</th>
                                                <th>Expired Date</th>
                                                <th>Qty Fisik</th>
                                                <th>Qty Buku</th>
                                                <th>Qty Pending</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($fefo as $row) : ?>
                                                <tr>
                                                    <td><?= $row->nama_barang ?></td>
                                                    <td><?= $row->exp_date ?></td>
                                                    <td><?= $row->qty_fisik ?></td>
                                                    <td><?= $row->qty_buku ?></td>
                                                    <td><?= $row->qty_pending ?></td>
                                                    <td><span class="badge <?= $row->status == 'MATCH' ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= $row->status ?>
                                                        </span></td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>



                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="card">
                            <div class="card-body">
                                <div class="container-fluid">
                                    <h5 class="mt-5">Opname All Barang</h5>
                                    <table class="table table-bordered table-sm" id="tb_opname_allbarang_user">
                                        <thead>
                                            <tr>
                                                <th>Nama Barang</th>
                                                <th>Qty Fisik</th>
                                                <th>Qty Buku</th>
                                                <th>Qty Pending</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allbarang as $row) : ?>
                                                <tr>
                                                    <td><?= $row->nama_barang ?></td>
                                                    <td><?= $row->qty_fisik ?></td>
                                                    <td><?= $row->qty_buku ?></td>
                                                    <td><?= $row->qty_pending ?></td>
                                                    <td>
                                                        <span class="badge <?= $row->status == 'MATCH' ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= $row->status ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
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
        // FEFO Pie Chart
        var fefoPieCtx = document.getElementById('fefoPieChart').getContext('2d');
        new Chart(fefoPieCtx, {
            type: 'pie',
            data: {
                labels: ['MATCH', 'NOT MATCH'],
                datasets: [{
                    label: 'Statistik FEFO',
                    data: [<?= $stat_fefo['match'] ?>, <?= $stat_fefo['not_match'] ?>],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // All Barang Pie Chart
        var allBarangPieCtx = document.getElementById('allBarangPieChart').getContext('2d');
        new Chart(allBarangPieCtx, {
            type: 'pie',
            data: {
                labels: [
                    <?php foreach ($allbarang as $row) : ?> "<?= $row->nama_barang ?>",
                    <?php endforeach ?>
                ],
                datasets: [{
                    label: 'Qty Fisik',
                    data: [
                        <?php foreach ($allbarang as $row) : ?>
                            <?= $row->qty_fisik ?>,
                        <?php endforeach ?>
                    ],
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#dc3545', '#6610f2',
                        '#20c997', '#e83e8c', '#fd7e14', '#6c757d', '#17a2b8'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>