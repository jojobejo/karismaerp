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
                            <a href="<?= base_url('compare_opname') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-arrow-left"></i></a>
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
                                <?php foreach ($detailqtyt1 as $det1) :
                                    $qtyzahir   = $det1->qty_zahir;
                                    $finalqty   = $det1->qty_zahir + $det1->qty_pending;
                                    $qtyfisik   = $det1->qty_fisik;
                                    $status     = $det1->status;
                                    if ($status = '1') {
                                        $statuss = '<span class="badge badge-success w-90 mt-2">MATCH</span>';
                                    } else {
                                        $statuss = '<span class="badge badge-danger w-90 mt-2">NOT MATCH</span>';
                                    }
                                ?>
                                    <div class="row">
                                        <div class="col-auto">
                                            <h4>Qty Zahir : <?= $qtyzahir ?></h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>||</h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>Qty Pending : <?= $det1->qty_pending ?></h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>||</h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>Qty Zahir + Pending : <?= $finalqty ?></h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>||</h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>Qty Fisik : <?= $qtyfisik ?></h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>||</h4>
                                        </div>
                                        <div class="col-auto">
                                            <?= $statuss ?>
                                        </div>
                                        <div class="col-auto">
                                            <h4>||</h4>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <table class="table table-bordered table-sm" id="tb_dash_allbarang">
                                    <thead>
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Expired Date</th>
                                            <th>status</th>
                                            <th>Qty Zahir</th>
                                            <th>Qty Pending</th>
                                            <th>Qty(zahir+pending)</th>
                                            <th>Qty Input</th>
                                            <th>Qty Box</th>
                                            <th>Qty Pcs</th>
                                            <th>Inputer</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($list1 as $row) :
                                            if ($row->status == '1') {
                                                $status = '<span class="badge badge-success w-70 mt-2">MATCH</span>';
                                            } else {
                                                $status = '<span class="badge badge-danger w-70 mt-2">NOT MATCH</span>';
                                            }
                                        ?>
                                            <tr>
                                                <td><?= $row->nama_barang ?></td>
                                                <td><?= $row->exp_date ?></td>
                                                <td><?= $status ?></td>
                                                <td><?= $row->qty_zahir ?></td>
                                                <td><?= $row->qty_pending ?></td>
                                                <td><?= $row->qty_with_pending ?></td>
                                                <td><?= $row->qty ?></td>
                                                <td><?= $row->qty_box ?></td>
                                                <td><?= $row->qty_pcs ?></td>
                                                <td><?= $row->inputer ?></td>
                                                <td>
                                                    <a href="javascript:void(0);" class="btn btn-success btn-sm btn-edit-opname" data-id="<?= $row->id ?>" data-kdbarang="<?= $row->kd_system ?>" data-nama="<?= $row->nama_barang ?>" data-qty="<?= $row->qty ?>" data-qtybox="<?= $row->qty_box ?>" data-qtypcs="<?= $row->qty_pcs ?>" data-expired="<?= $row->exp_date ?>" data-dimensi="<?= $row->dimensi ?>">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane" id="tim2">
                                <?php foreach ($detailqtyt2 as $det2) :
                                    $qtyzahir   = $det2->qty_zahir;
                                    $finalqty   = $det2->qty_zahir + $det2->qty_pending;
                                    $qtyfisik   = $det2->qty_fisik;
                                    $status2     = $det2->status;
                                    if ($status2 == '1') {
                                        $statuss2 = '<span class="badge badge-success w-90 mt-2">MATCH</span>';
                                    } else {
                                        $statuss2 = '<span class="badge badge-danger w-90 mt-2">NOT MATCH</span>';
                                    }
                                ?>
                                    <div class="row">
                                        <div class="col-auto">
                                            <h4>Qty Zahir : <?= $qtyzahir ?></h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>||</h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>Qty Pending : <?= $det2->qty_pending ?></h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>||</h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>Qty Zahir + Pending : <?= $finalqty ?></h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>||</h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>Qty Fisik : <?= $qtyfisik ?></h4>
                                        </div>
                                        <div class="col-auto">
                                            <h4>||</h4>
                                        </div>
                                        <div class="col-auto">
                                            <?= $statuss2 ?>
                                        </div>
                                        <div class="col-auto">
                                            <h4>||</h4>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <table class="table table-bordered table-sm" id="tb_dash_allbarang1">
                                    <thead>
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Expired Date</th>
                                            <th>status</th>
                                            <th>Qty Zahir</th>
                                            <th>Qty Pending</th>
                                            <th>Qty(zahir+pending)</th>
                                            <th>Qty Input</th>
                                            <th>Qty Box</th>
                                            <th>Qty Pcs</th>
                                            <th>Inputer</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($list2 as $row2) :
                                            if ($row2->status == '1') {
                                                $status = '<span class="badge badge-success w-70 mt-2">MATCH</span>';
                                            } else {
                                                $status = '<span class="badge badge-danger w-70 mt-2">NOT MATCH</span>';
                                            }
                                        ?>
                                            <td><?= $row2->nama_barang ?></td>
                                            <td><?= $row2->exp_date ?></td>
                                            <td><?= $status ?></td>
                                            <td><?= $row2->qty_zahir ?></td>
                                            <td><?= $row2->qty_pending ?></td>
                                            <td><?= $row2->qty_with_pending ?></td>
                                            <td><?= $row2->qty ?></td>
                                            <td><?= $row2->qty_box ?></td>
                                            <td><?= $row2->qty_pcs ?></td>
                                            <td><?= $row2->inputer ?></td>
                                            <td>
                                                <a href="javascript:void(0);" class="btn btn-success btn-sm btn-edit-opname" data-id="<?= $row2->id ?>" data-kdbarang="<?= $row2->kd_system ?>" data-nama="<?= $row2->nama_barang ?>" data-qty="<?= $row2->qty ?>" data-qtybox="<?= $row2->qty_box ?>" data-qtypcs="<?= $row2->qty_pcs ?>" data-expired="<?= $row2->exp_date ?>" data-dimensi="<?= $row2->dimensi ?>">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            </td>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h3>Opname To Do</h3>

                            <div>
                                <h3>TIM 1</h3>
                                <?php foreach ($result_1 as $r1) : ?>
                                    <div class="row">
                                        <div class="col-auto">
                                            <h5>Total Data : <?= $r1->total_data ?></h5>
                                        </div>
                                        <div class="col-auto">
                                            <h5>Total Match : <?= $r1->total_match ?></h5>
                                        </div>
                                        <div class="col-auto">
                                            <h5>Total Not Match :<?= $r1->total_not_match ?></h5>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div>
                                <h3>TIM 2</h3>
                                <?php foreach ($result_2 as $r2) : ?>
                                    <div class="row">
                                        <div class="col-auto">
                                            <h5>Total Data : <?= $r2->total_data ?></h5>
                                        </div>
                                        <div class="col-auto">
                                            <h5>Total Match : <?= $r2->total_match ?></h5>
                                        </div>
                                        <div class="col-auto">
                                            <h5>Total Not Match :<?= $r2->total_not_match ?></h5>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <table class="table table-bordered table-sm" id="tbopnametodo">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Expired Date</th>
                                        <th>Qty Zahir</th>
                                        <th>Qty Pending</th>
                                        <th>Qty TIM 1</th>
                                        <th>Qty TIM 2</th>
                                        <th>Status TIM 1</th>
                                        <th>Status TIM 2</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($opnametodo as $op) : ?>
                                        <tr>
                                            <td><?= $op->nama_barang ?></td>
                                            <td><?= $op->exp_date ?></td>
                                            <td><?= $op->qty_zahir ?></td>
                                            <td><?= $op->qty_pending ?></td>
                                            <td><?= $op->qtyinput_1 ?></td>
                                            <td><?= $op->qtyinput_2 ?></td>
                                            <td style="text-align: center;">
                                                <?= ($op->status_tim1 == 'Match') ? '<span class="badge badge-success w-70">MATCH</span>' : '<span class="badge badge-danger w-70">NOTMATCH</span>'; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?= ($op->status_tim2 == 'Match') ? '<span class="badge badge-success w-70">MATCH</span>' : '<span class="badge badge-danger w-70">NOTMATCH</span>'; ?>
                                            </td>
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
                                <label>Tgl Expired</label>
                                <input type="text" class="form-control" id="edit_expdate" name="exp_date" readonly>
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
                                <label>Dimensi</label>
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
                $('#edit_expdate').val(data.expired);
                $('#edit_qty_box').val(data.qtybox);
                $('#edit_qty_pcs').val(data.qtypcs);
                $('#edit_dimensi').val(`${data.dimensi}`);

                $('#modalEditOpname').modal('show');
            });
        });
    </script>