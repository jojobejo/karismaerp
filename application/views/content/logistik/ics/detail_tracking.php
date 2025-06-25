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
                            <a href="<?= base_url('compareall_tim') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-arrow-left"></i></a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <?php foreach ($nmbarang as $nm) : ?>
                                <h5 class="card-title">Detail Inputer Barang - <b><?= $nm->nama_barang ?></b></h5>
                            <?php endforeach; ?>
                            <div class="card-tools">
                                <ul class="nav nav-pills ml-auto">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#tim1" data-toggle="tab">Tim 1</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#tim2" data-toggle="tab">Tim 2</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="tab-content p-3">
                            <div class="tab-pane active" id="tim1">
                                <table class="table table-bordered table-sm" id="tb_dash_allbarang">
                                    <thead>
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Expired Date</th>
                                            <th>Qty</th>
                                            <th>Qty Box</th>
                                            <th>Qty Pcs</th>
                                            <th>Inputer</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($list1 as $row) : ?>
                                            <tr>
                                                <td><?= $row->nama_barang ?></td>
                                                <td><?= $row->qty ?></td>
                                                <td><?= $row->qty_box ?></td>
                                                <td><?= $row->qty_pcs ?></td>
                                                <td><?= $row->inputer ?></td>
                                                <td>
                                                    <a href="javascript:void(0);" class="btn btn-success btn-sm btn-edit-opname" data-id="<?= $row->id ?>" data-kdbarang="<?= $row->kd_system ?>" data-nama="<?= $row->nama_barang ?>" data-qty="<?= $row->qty ?>" data-qtybox="<?= $row->qty_box ?>" data-qtypcs="<?= $row->qty_pcs ?>" data-p="<?= $row->dimensi ?>">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane" id="tim2">
                                <table class="table table-bordered table-sm" id="tb_dash_allbarang1">
                                    <thead>
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Qty</th>
                                            <th>Qty Box</th>
                                            <th>Qty Pcs</th>
                                            <th>Inputer</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($list2 as $row1) : ?>
                                            <tr>
                                                <td><?= $row1->nama_barang ?></td>
                                                <td><?= $row1->qty ?></td>
                                                <td><?= $row1->qty_box ?></td>
                                                <td><?= $row1->qty_pcs ?></td>
                                                <td><?= $row1->inputer ?></td>
                                                <td>
                                                    <a href="javascript:void(0);" class="btn btn-success btn-sm btn-edit-opname" data-id="<?= $row1->id ?>" data-kdbarang="<?= $row1->kd_system ?>" data-nama="<?= $row1->nama_barang ?>" data-qty="<?= $row1->qty ?>" data-qtybox="<?= $row1->qty_box ?>" data-qtypcs="<?= $row1->qty_pcs ?>" data-p="<?= $row1->dimensi ?>">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- <div class="card-body">
                            
                        </div> -->
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

        <!-- Modal Edit Opname -->
        <div class="modal fade" id="modalEditOpname" tabindex="-1" role="dialog" aria-labelledby="editOpnameModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <form id="formEditOpname" method="POST" action="<?= base_url('save_edit_opname') ?>">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title text-white" id="editOpnameModalLabel">Edit Data Opname</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="edit_id">
                            <input type="hidden" name="kd_barang" id="edit_kd_barang">

                            <div class="form-group">
                                <label>Nama Barang</label>
                                <input type="text" class="form-control" id="edit_nama_barang" name="nama_barang" readonly>
                            </div>

                            <div class="form-group">
                                <label>Qty Box</label>
                                <input type="number" class="form-control" id="edit_qty_box" name="qty_box" required>
                            </div>

                            <div class="form-group">
                                <label>Qty Pcs</label>
                                <input type="number" class="form-control" id="edit_qty_pcs" name="qty_pcs" required>
                            </div>

                            <div class="form-group" hidden>
                                <label>Dimensi (P x L x T)</label>
                                <input type="text" class="form-control" id="edit_dimensi" name="dimensi" readonly>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <!-- ./wrapper -->

    <script>
        $(document).ready(function() {
            $('.btn-edit-opname').on('click', function() {
                const data = $(this).data();

                $('#edit_id').val(data.id);
                $('#edit_kd_barang').val(data.kdbarang);
                $('#edit_nama_barang').val(data.nama);
                $('#edit_qty_box').val(data.qtybox);
                $('#edit_qty_pcs').val(data.qtypcs);
                $('#edit_dimensi').val(`${data.p}`);

                $('#modalEditOpname').modal('show');
            });
        });
    </script>