<style>
    #tbl_driver_rute thead th {
        background-color: #1f2937;
        /* slate-800 */
        color: #fff;
        text-align: center;
        font-size: 13px;
        vertical-align: middle;
    }

    #tbl_driver_rute tbody td:first-child {
        background-color: #f3f4f6;
        /* gray-100 */
        font-weight: 600;
        white-space: nowrap;
    }

    #tbl_driver_rute tbody td {
        text-align: center;
        font-size: 13px;
    }

    .cell-zero {
        color: #9ca3af;
        /* gray-400 */
    }

    .cell-active {
        background-color: #dcfce7;
        /* green-100 */
        color: #166534;
        /* green-800 */
        font-weight: 600;
        border-radius: 4px;
    }

    .table-responsive {
        max-height: 65vh;
        overflow-y: auto;
    }

    /* sticky header */
    #tbl_driver_rute thead th {
        position: sticky;
        top: 0;
        z-index: 2;
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
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-auto">
                            <a href="<?= base_url('histori_driver') ?>" class="btn btn-primary mb-2">Histori Driver</a>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="card">
                    <div class="card-body">
                        <table id="tbtotal_tonase" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Rute</th>
                                    <th>Tonase Terkirim</th>
                                    <th>Tonase Pending</th>
                                    <th>Total Tonase</th>
                                    <th>Faktur Terkirim</th>
                                    <th>Faktur Pending</th>
                                    <th>Total Faktur</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($total_tonase as $tot) : ?>
                                    <tr>
                                        <td><?= $tot->rute ?></td>
                                        <td><?= $tot->tonase_terkirim ?></td>
                                        <td><?= $tot->tonase_belum_terkirim ?></td>
                                        <td><?= $tot->total_tonase ?></td>
                                        <td><?= $tot->total_faktur_terkirim ?></td>
                                        <td><?= $tot->total_faktur_pending ?></td>
                                        <td><?= $tot->total_faktur ?></td>
                                        <td><a href="<?= base_url('detail_tonase/') . $tot->rute ?>" class="btn btn-info btn-block"><i class="fas fa-eye"></i></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- <div class="card">
                    <div class="card-body">
                        <div class="mt-4">
                            <div class="row">
                                <div class="col-md">
                                    <label>Rute</label>
                                    <select name="s_rute" id="s_rute" class="form-control">
                                        <option value="">-- Semua Rute --</option>
                                        <?php foreach ($all_rute as $rute) : ?>
                                            <option value="<?= $rute->kd_rute ?>"><?= $rute->kd_rute ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md">
                                    <label>Rentang Tanggal</label>
                                    <input type="text" id="filter_tanggal" class="form-control" placeholder="YYYY-MM-DD - YYYY-MM-DD">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <table class="table table-bordered table-striped mt-3">
                            <thead>
                                <tr>
                                    <th>Rute</th>
                                    <th>Tanggal Pengiriman</th>
                                </tr>
                            </thead>
                            <tbody id="result_data">
                                <tr>
                                    <td colspan="2" class="text-center">Silakan pilih filter</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div> -->

                <div class="card">
                    <div class="card-body">
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label>Rentang Tanggal</label>
                                <input type="text" id="filter_tanggal_driver" class="form-control" placeholder="YYYY-MM-DD - YYYY-MM-DD">
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive">

                            <div class="mt-2 text-muted small">
                                Menampilkan distribusi driver berdasarkan rute <strong>(status DO = selesai)</strong>
                            </div>

                            <table class="table table-bordered table-striped" id="tbl_driver_rute">
                                <thead id="thead_rute"></thead>
                                <tbody id="tbody_driver"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tbl_sales">

                            </table>
                        </div>
                    </div>
                </div> -->

                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-4">
                                <label>Rentang Tanggal</label>
                                <input type="text" id="filter_tanggal" class="form-control" placeholder="YYYY-MM-DD - YYYY-MM-DD">
                            </div>

                            <div class="col-md-4">
                                <label>Pilih Rute</label>
                                <select id="filter_rute" class="form-control">
                                    <option value="">-- Pilih Rute --</option>
                                    <?php foreach ($all_rute as $r) : ?>
                                        <option value="<?= $r->kd_rute ?>"><?= $r->kd_rute ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <h6 class="text-muted">Driver Ready untuk Diploting</h6>

                        <table class="table table-bordered table-striped mt-2">
                            <thead>
                                <tr>
                                    <th>Driver</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_ready">
                                <tr>
                                    <td colspan="2" class="text-center text-muted">
                                        Lengkapi filter terlebih dahulu
                                    </td>
                                </tr>
                            </tbody>
                        </table>

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