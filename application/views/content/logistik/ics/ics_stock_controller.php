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
                            <a href="<?= base_url('ics') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-arrow-left"></i></a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <?php foreach ($nmbarang as $nm) : ?>
                                <h5 class="card-title">Detail Inputer Barang - <b><?= $nm->nama_barang . ' ' . '(' . $nm->exp_date . ')' ?></b></h5>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <div class="form-group" style="position: relative;background: #fff;">
                                        <h5 class="card-title mt-2 mb-2"><b>Compare By Expired Date</b></h5>
                                        <table style="border: 1px solid #000000; border-collapse: collapse; width: 100%; text-align: center;">
                                            <thead>
                                                <tr>
                                                    <th style="border: 1px solid #000000; padding: 5px;">Expired</th>
                                                    <th style="border: 1px solid #000000; padding: 5px;">Qty</th>
                                                    <th style="border: 1px solid #000000; padding: 5px;">DO</th>
                                                    <th style="border: 1px solid #000000; padding: 5px;">PO</th>
                                                    <th style="border: 1px solid #000000; padding: 5px;">Qty All</th>
                                                    <th style="border: 1px solid #000000; padding: 5px;">Ics</th>
                                                    <th style="border: 1px solid #000000; padding: 5px;">Selisih</th>
                                                    <th style="border: 1px solid #000000; padding: 5px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($detail_stok as $dstock) : ?>
                                                    <tr>
                                                        <td style="border: 1px solid #000000; padding: 5px;"><?= $dstock->exp_date ?></td>
                                                        <td style="border: 1px solid #000000; padding: 5px;"><?= $dstock->qty_awal ?></td>
                                                        <td style="border: 1px solid #000000; padding: 5px;"><?= $dstock->DO ?></td>
                                                        <td style="border: 1px solid #000000; padding: 5px;"><?= $dstock->PO ?></td>
                                                        <td style="border: 1px solid #000000; padding: 5px;"><?= $dstock->qty_all ?></td>
                                                        <td style="border: 1px solid #000000; padding: 5px;"><?= $dstock->ICS ?></td>
                                                        <td style="border: 1px solid #000000; padding: 5px;"><?= $dstock->selisih ?></td>
                                                        <td style="border: 1px solid #000000; padding: 5px;"><?= $dstock->status ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <button class="btn btn-sm btn-success w-100 mt-2" data-toggle="modal" data-target="#modalAddOpname">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <h3 class="card-title mt-2 mb-3"><strong>Tracking Inputer</strong></h3>
                            <table class="table table-bordered table-sm" id="x">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Expired Date</th>
                                        <th>QTY</th>
                                        <th>Box</th>
                                        <th>Pcs</th>
                                        <th>Input At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($input_log as $log) : ?>
                                        <tr>
                                            <td><?= $log->nama_barang ?></td>
                                            <td><?= $log->exp_date ?></td>
                                            <td><?= $log->qty ?></td>
                                            <td><?= $log->qty_box ?></td>
                                            <td><?= $log->qty_pcs ?></td>
                                            <td><?= $log->tgl_input ?></td>
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

        <!-- Modal Add Opname -->
        <div class="modal fade" id="modalAddOpname" tabindex="-1" role="dialog" aria-labelledby="modalAddOpnameLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="<?= base_url('ics/sv_opname') ?>" method="post">
                    <div class="modal-content">
                        <div class="modal-header bg-success">
                            <h5 class="modal-title" id="modalAddOpnameLabel"><i class="fas fa-box"></i> Input Data Opname</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php foreach ($detail_stok as $dstock) : ?>
                            <div class="modal-body">
                                <!-- Nama Barang -->
                                <div class="form-group">
                                    <label for="nama_barang">Nama Barang</label>
                                    <input type="text" name="nama_barang" class="form-control" value="<?= $dstock->nama_barang ?>" readonly required>
                                    <input type="text" name="dimensi" class="form-control" value="<?= $dstock->dimensi ?>" hidden readonly>
                                    <input type="text" name="id" class="form-control" value="<?= $dstock->id ?>" readonly hidden>
                                </div>
                                <!-- Expired Date -->
                                <div class="form-group">
                                    <label for="exp_date">Expired Date</label>
                                    <input type="text" name="exp_date" class="form-control" value="<?= $dstock->exp_date ?>" readonly>
                                </div>
                                <!-- Qty Box -->
                                <div class="form-group">
                                    <label for="qty_box">Qty Box</label>
                                    <input type="number" name="qty_box" class="form-control" placeholder="0">
                                </div>
                                <!-- Qty Pcs -->
                                <div class="form-group">
                                    <label for="qty_pcs">Qty Pcs</label>
                                    <input type="number" name="qty_pcs" class="form-control" placeholder="0">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
        </div>


        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->