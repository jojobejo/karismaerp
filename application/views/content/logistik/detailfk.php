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
                            <table id="detbarang" class="table table-striped">
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
                                        <tr data-id="<?= $det->id ?>">
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
                                                    <td style="width: 10%;">
                                                        <div class="row">
                                                            <div class="col">
                                                                <button class="btn btn-primary w-100 btn-edit" data-id="<?= $det->id ?>">
                                                                    <i class="fas fa-pencil-alt"></i>
                                                                </button>
                                                            </div>
                                                            <div class="col">
                                                                <a href="<?= base_url('pnd_br_detpo/') . $det->id . '/' . $kdfaktur . '/' . 'pending' ?>" class="btn btn-danger w-100"><i class="fas fa-hand-paper"></i></a>
                                                            </div>
                                                        </div>
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

                                    <!-- BARIS EDIT TABEL -->

                                    <tr id="editRow" style="display: none;">
                                        <td colspan="7">
                                            <form id="editForm">
                                                <div class="row">
                                                    <input type="hidden" id="id" name="id" readonly>
                                                    <div class="col-md-2">
                                                        <input type="text" id="edit_nama" name="nm_barang" class="form-control" readonly>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="number" id="edit_qty" name="qty" class="form-control">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="text" id="edit_satuan" name="satuan" class="form-control">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="text" id="edit_no_lot" name="no_lot" class="form-control">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="text" id="edit_exp" name="tgl_exp" class="form-control">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="submit" class="btn btn-success">Simpan</button>
                                                        <button type="button" class="btn btn-danger" id="cancelEdit">Batal</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php if ($status_faktur == '1') : ?>
                                <a href="<?= base_url('insert_tmp/') . $kdfaktur . '/' . 'formdetail' ?>" class="btn btn-success btn-block mt-4 mb-2">Input To Draft</a>
                            <?php else : ?>
                                <a href="<?= base_url('revert_do/') . $kdfaktur . '/' . 'revertdetail' ?>" class="btn btn-warning btn-block mt-4 mb-2">Revert DO</a>
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

    <script>
        $(document).ready(function() {
            $(".btn-edit").on("click", function(e) {
                e.preventDefault();

                var row = $(this).closest("tr");
                var id = row.data("id");

                $.ajax({
                    url: "<?= base_url('get_barang') ?>",
                    type: "POST",
                    data: {
                        id: id
                    },
                    dataType: "json",
                    success: function(data) {
                        $("#id").val(data.id);
                        $("#edit_nama").val(data.nm_barang);
                        $("#edit_qty").val(data.qty);
                        $("#edit_satuan").val(data.satuan);
                        $("#edit_no_lot").val(data.no_lot);
                        $("#edit_exp").val(data.tgl_exp);

                        $("#editRow").insertAfter(row).show();
                    }
                });
            });

            $("#cancelEdit").on("click", function() {
                $("#editRow").hide();
            });


            $("#editForm").on("submit", function(e) {
                e.preventDefault();

                $.ajax({
                    url: "<?= base_url('update_barang') ?>",
                    type: "POST",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            alert("Data berhasil diperbarui!");
                            location.reload();
                        } else {
                            alert("Terjadi kesalahan, silakan coba lagi.");
                        }
                    },
                    error: function() {
                        alert("Gagal memperbarui data.");
                    }
                });
            });

        });
    </script>