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
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-2">
                                <a href="<?= base_url('logistik'); ?>" class="btn btn-primary w-10"><i class="fas fa-home"></i></a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card mt-4 mb-2">
                        <div class="card-body">
                            <h4>Data Stock Opname</h4>

                            <table class="table table-bordered" id="list_tb_opname">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th style="width: 15%;text-align: center;">Expired Date</th>
                                        <th style="width: 10%;text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data_ics as $itm) : ?>
                                        <tr>
                                            <td><?= $itm->nama_barang ?></td>
                                            <td><?= $itm->exp_date ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-success btn-sm btn-input" data-nama="<?= $itm->nama_barang ?>" data-exp="<?= $itm->exp_date ?>" data-dimensi="<?= $itm->dimensi ?>">
                                                    <i class="fas fa-pen"></i> Input
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <!-- Modal Input Opname -->
                            <div class="modal fade" id="modalInputOpname" tabindex="-1" role="dialog" aria-labelledby="opnameModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <form id="formOpname">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Input Stock Opname</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="nmbarang" id="modal_nama_barang">
                                                <input type="hidden" name="expdate" id="modal_exp_date">
                                                <input type="hidden" name="dimensi" id="modal_dimensi">

                                                <div class="form-group">
                                                    <label>Nama Barang</label>
                                                    <input type="text" class="form-control" id="view_nama_barang" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label>Expired Date</label>
                                                    <input type="text" class="form-control" id="view_exp_date" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label>Qty Box</label>
                                                    <input type="number" name="qtybox" class="form-control" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Qty Pcs</label>
                                                    <input type="number" name="qtypcs" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

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
        $(function() {
            $("#list_tb_opname").DataTable({
                "responsive": true,
                "lengthChange": false,
                "aaSorting": [],
                "autoWidth": false,
            });

            // Event tombol input
            $('.btn-input').on('click', function() {
                let nama_barang = $(this).data('nama');
                let exp_date = $(this).data('exp');
                let dimensi = $(this).data('dimensi');

                $('#modal_nama_barang').val(nama_barang);
                $('#modal_exp_date').val(exp_date);
                $('#modal_dimensi').val(dimensi);

                $('#view_nama_barang').val(nama_barang);
                $('#view_exp_date').val(exp_date);

                $('#modalInputOpname').modal('show');
            });

            // Submit form via AJAX
            $('#formOpname').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "<?= base_url('logistik/insertopname') ?>",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        $('#modalInputOpname').modal('hide');
                        Swal.fire('Berhasil', 'Data opname berhasil disimpan!', 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data.', 'error');
                    }
                });
            });
        });
    </script>