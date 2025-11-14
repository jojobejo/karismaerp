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
                            <a href="<?= base_url('gudang') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-warehouse"> </i> Gudang</a>
                        </div>
                    </div>

                    <div class="card shadow-sm gudang-card">

                        <div class="card-body">

                            <!-- Tombol Tambah -->
                            <div class="text-right mb-3">
                                <button class="btn btn-primary btn-block" data-toggle="modal" data-target="#modalAddGudang">
                                    <i class="fa fa-plus"></i> Tambah Gudang
                                </button>
                            </div>

                            <div class="row" id="listGudang">

                                <?php foreach ($list_gudang as $g) : ?>
                                    <div class="col-md-4 col-sm-6 mb-3 gudang-item" id="gudang_<?= $g->kode_gudang ?>">
                                        <div class="card shadow-sm">
                                            <div class="card-body">

                                                <div class="d-flex justify-content-between">
                                                    <strong class="nama-gudang"><?= $g->nama_gudang ?></strong>
                                                    <button class="btn btn-outline-warning btn-sm btnEdit" data-id="<?= $g->kode_gudang ?>" data-nama="<?= $g->nama_gudang ?>">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                </div>

                                                <button class="btn btn-detail  w-100 mt-3 py-2 border" onclick="window.location.href='<?= base_url('detail_gudang/' . $g->kode_gudang) ?>'">
                                                    Lihat Detail
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            </div>

                        </div>
                    </div>

                    <!-- Modal Tambah -->
                    <div class="modal fade" id="modalAddGudang">
                        <div class="modal-dialog">
                            <form id="formAddGudang">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Tambah Gudang Baru</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>Nama Gudang</label>
                                            <input type="text" name="nama_gudang" class="form-control" required>
                                            <input type="hidden" name="kd_gdg" class="form-control" value="<?= $gdg_generate ?>" readonly required hidden>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Simpan</button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>


                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEditGudang">
                        <div class="modal-dialog">
                            <form id="formEditGudang">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Gudang</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>

                                    <div class="modal-body">
                                        <input type="hidden" name="id_gudang" id="edit_id">
                                        <div class="form-group">
                                            <label>Nama Gudang</label>
                                            <input type="text" name="nama_gudang" id="edit_nama" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>

                                </div>
                            </form>
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
        $(document).ready(function() {

            // Submit tambah gudang
            $("#formAddGudang").submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: "<?= base_url('logistik/add_gudang_ajax') ?>",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        let data = JSON.parse(res);

                        if (data.status == "ok") {

                            // Tambahkan card baru tanpa reload
                            $("#listGudang").append(`
                        <div class="col-md-4 col-sm-6 mb-3 gudang-item" id="gudang_${data.id}">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <strong class="nama-gudang">${data.nama}</strong>

                                        <button class="btn btn-edit btn-sm btnEdit"
                                            data-id="${data.id}"
                                            data-nama="${data.nama}">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </div>

                                    <button 
                                        class="btn btn-detail w-100 mt-3 py-2 border"
                                        onclick="window.location.href='<?= base_url('logistik/detail_gudang/') ?>${data.id}'">
                                        Lihat Detail
                                    </button>
                                </div>
                            </div>
                        </div>
                    `);

                            $("#modalAddGudang").modal('hide');
                            $("#formAddGudang")[0].reset();
                        }
                    }
                });
            });

            // Buka modal edit
            $(document).on("click", ".btnEdit", function() {
                let id = $(this).data("id");
                let nama = $(this).data("nama");

                $("#edit_id").val(id);
                $("#edit_nama").val(nama);

                $("#modalEditGudang").modal("show");
            });

            $("#formEditGudang").submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: "<?= base_url('logistik/update_gudang_ajax') ?>",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        let data = JSON.parse(res);

                        if (data.status == "ok") {
                            // Update nama card 
                            $("#gudang_" + data.id).find(".nama-gudang").text(data.nama);

                            $("#modalEditGudang").modal('hide');
                        }
                    }
                });
            });

        });
    </script>