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
                            <a href="<?= base_url('ics') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-home"></i> Dashboard</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/icsdo') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-minus-circle"></i> Data DO</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/icspo') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-plus-circle"></i> Data PO</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/ics_diffrent') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-eye"></i> Show Diffrent</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="container-fluid">

                                <!-- Tombol Import -->
                                <button class="btn btn-success mb-3" data-toggle="modal" data-target="#modalImportCSV">
                                    <i class="fas fa-file-csv"></i> Import CSV
                                </button>

                                <!-- Modal Import CSV -->
                                <div class="modal fade" id="modalImportCSV" tabindex="-1" role="dialog" aria-labelledby="modalImportCSVLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <form id="formImportCSV" enctype="multipart/form-data">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info">
                                                    <h5 class="modal-title">Import Data PO dari CSV</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="file_csv">Pilih File CSV</label>
                                                        <input type="file" class="form-control-file" name="file_csv" accept=".csv" required>
                                                    </div>
                                                    <div id="importStatus" class="text-danger"></div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-info">Upload</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>


                                <table class="table table-bordered" id="tb_ics_po">
                                    <thead class="bg-primary text-white text-center">
                                        <tr>
                                            <th>Kode Faktur</th>
                                            <th>Tgl Transaksi</th>
                                            <th>Nama Barang</th>
                                            <th>Expired Date</th>
                                            <th>Qty</th>
                                            <th>Box</th>
                                            <th>Pcs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ics_po as $po) : ?>
                                            <tr>
                                                <td><?= $po->kd_faktur ?></td>
                                                <td><?= $po->tgl_transaksi ?></td>
                                                <td><?= $po->nama_barang ?></td>
                                                <td><?= $po->exp_date ?></td>
                                                <td><?= $po->qty ?></td>
                                                <td><?= $po->qty_box ?></td>
                                                <td><?= $po->qty_pcs ?></td>
                                            </tr>
                                        <?php endforeach; ?>
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

    <script>
        $(document).ready(function() {
            $('#formImportCSV').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: "<?= base_url('ics/import_csv') ?>",
                    method: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        $('#importStatus').removeClass('text-danger').addClass('text-success').text('Import berhasil!');
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: function(err) {
                        $('#importStatus').removeClass('text-success').addClass('text-danger').text('Gagal mengimpor file CSV.');
                    }
                });
            });
        });
    </script>