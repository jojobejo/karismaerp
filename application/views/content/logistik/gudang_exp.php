<style>
    /* Card gudang */
    .gudang-card {
        background: #f8fbff;
        border: 1px solid #dce7f5;
        border-radius: 10px;
        transition: 0.2s;
    }

    .gudang-card:hover {
        background: #eef5ff;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
    }

    /* Nama gudang */
    .nama-gudang {
        color: #2a3f5f;
        font-weight: 600;
    }

    /* Button detail */
    .btn-detail {
        background: #2beebdff;
        border: 1px solid #cfd8e6;
        color: #2a3f5f;
        transition: 0.2s;
    }

    .btn-detail:hover {
        background: #e6eef9;
        border-color: #b8c7dd;
    }

    /* Tombol edit */
    .btn-edit {
        border-color: #b8c7dd;
        color: #4b5c75;
    }

    .btn-edit:hover {
        background: #e6efff;
        color: #1f3d7a;
    }

    /* Tombol utama (Tambah Gudang) */
    .btn-primary {
        background: #4a7bd1 !important;
        border-color: #4a7bd1 !important;
    }

    .btn-primary:hover {
        background: #3e6ebb !important;
        border-color: #3e6ebb !important;
    }

    /* Modal */
    .modal-content {
        border-radius: 12px;
        border: none;
        background: #fafcff;
    }

    .modal-header {
        background: #e9f0fb;
    }

    .modal-title {
        color: #2a3f5f;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">
                    <div class="row">
                        <div class="col-auto">
                            <a href="<?= base_url('ics/by_allbarang') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-box"></i> Data All Barang</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/by_expdate') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-calendar"></i> Data By Expired Date</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/icsdo') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-minus-circle"></i> Data DO</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/icspo') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-plus-circle"></i> Data PO</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/export_opname') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-file-export"></i> Export Result </a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/ics_diffrent') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-eye"></i> Show Diffrent</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('gudang') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-warehouse"> </i> Gudang</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('gudang_expired') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-calendar-times"></i> Gudang Expired</a>
                        </div>
                    </div>

                    <div class="card shadow-sm gudang-card">
                        <div class="card-body">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Expired Date</th>
                                        <th>Qty</th>
                                        <th>Box</th>
                                        <th>Pcs</th>
                                        <th>#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dt_expired as $d) : ?>
                                        <tr>
                                            <td><?= $d->nama_barang ?></td>
                                            <td><?= $d->exp_date ?></td>
                                            <td><?= $d->qty ?></td>
                                            <td><?= $d->qty_box ?></td>
                                            <td><?= $d->qty_pcs ?></td>
                                            <td><?= $d->id ?></td>
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