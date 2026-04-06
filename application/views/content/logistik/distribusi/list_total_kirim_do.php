<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-auto">
                            <a href="<?= base_url('logistik/distibusi') ?>" class="btn btn-secondary mb-2">
                                <i class="fas fa-arrow-left"></i> Kembali ke Distribusi
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Rentang Tanggal Pengiriman</label>
                                <input type="text" id="filter_tanggal_kirim_do" class="form-control" placeholder="YYYY-MM-DD - YYYY-MM-DD">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" id="btn_filter_kirim_do" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" id="btn_reload_kirim_do" class="btn btn-info btn-block">
                                    <i class="fas fa-sync-alt"></i> Muat Ulang
                                </button>
                            </div>
                        </div>
                        <hr>

                        <table class="table table-bordered table-striped" id="tbl_kirim_do">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Rute</th>
                                    <th>Total Faktur</th>
                                    <th>Faktur Terkirim</th>
                                    <th>Faktur Pending</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>
</body>
