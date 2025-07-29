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

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <?php foreach ($get_barang as $nm) : ?>
                                <h5 class="card-title">Detail Inputer Barang - <b><?= $nm->nm_barang ?></b></h5>

                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="<?= base_url('ics/ics_diffrent') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-arrow-left"></i></a>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-md btn-success" data-toggle="modal" data-target="#modalAddOpname">
                                        <i class="fas fa-plus"></i> Add Expired Baru
                                    </button>
                                </div>
                            </div>

                            <div class="col-auto">
                                <div class="form-group" style="position: relative;background: #fff;">
                                    <h5 class="card-title mt-2 mb-2"><b>Compare By Expired Date</b></h5>
                                    <table id="tb_exp_form" class="table table-bordered table-striped" style="border: 1px solid #000000; border-collapse: collapse; width: 100%; text-align: center;">
                                        <thead>
                                            <tr>
                                                <th style="border: 1px solid #000000;">Expired</th>
                                                <th style="border: 1px solid #000000;">Qty</th>
                                                <th style="border: 1px solid #000000;">DO</th>
                                                <th style="border: 1px solid #000000;">PO</th>
                                                <th style="border: 1px solid #000000;">Qty All</th>
                                                <th style="border: 1px solid #000000;">ICS</th>
                                                <th style="border: 1px solid #000000;">Selisih</th>
                                                <th style="border: 1px solid #000000;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($list_stock_by_exp as $br) : ?>
                                                <tr>
                                                    <td><?= $br->expired ?></td>
                                                    <td><?= $br->qty ?></td>
                                                    <td><?= $br->do ?></td>
                                                    <td><?= $br->po ?></td>
                                                    <td><?= $br->ics ?></td>
                                                    <td><?= $br->qty_all ?></td>
                                                    <td><?= $br->selisih ?></td>
                                                    <?php if ($br->status == '1') : ?>
                                                        <td>
                                                            <a href="#" class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                                                            <button class="btn btn-sm btn-info view-detail" data-exp="<?= $br->expired ?>" data-nama="<?= $nm->nm_barang ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-md btn-success btn-add-opname" data-toggle="modal" data-target="#modalAddOpname" data-dimensi="<?= $br->dimensi ?>" data-kdbarang="<?= $br->kd_system ?>" data-exp="<?= $br->expired ?>" data-nama="<?= $nm->nm_barang ?>" data-id="<?= $br->id ?>">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </td>
                                                    <?php else : ?>
                                                        <td>
                                                            <a href="#" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></a>
                                                            <button class="btn btn-sm btn-info view-detail" data-exp="<?= $br->expired ?>" data-nama="<?= $nm->nm_barang ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-md btn-success btn-add-opname" data-toggle="modal" data-target="#modalAddOpname" data-dimensi="<?= $br->dimensi ?>" data-kdbarang="<?= $br->kd_system ?>" data-exp="<?= $br->expired ?>" data-nama="<?= $nm->nm_barang ?>" data-id="<?= $br->id ?>">
                                                                <i class="fas fa-plus"></i>
                                                            </button>

                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3" id="card_do" style="display: none;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title">Data DO</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm" id="ics_do_byexp">
                                <thead class="bg-warning text-white text-center">
                                    <tr>
                                        <th>Kode Faktur</th>
                                        <th>Tgl Transaksi</th>
                                        <th>Nama Barang</th>
                                        <th>Expired Date</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card mt-3" id="card_detail" style="display: none;">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title">Detail Transaksi Berdasarkan Expired Date</h5>
                        </div>
                        <div class="card-body">
                            <h6><b>Data DO</b></h6>
                            <table class="table table-bordered table-sm" id="table_do">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Faktur</th>
                                        <th>Tgl Transaksi</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>

                            <h6><b>Data PO</b></h6>
                            <table class="table table-bordered table-sm" id="table_po">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Faktur</th>
                                        <th>Tgl Transaksi</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>

                            <h6><b>Data Tracking Inputer</b></h6>
                            <table class="table table-bordered table-sm" id="table_log">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Qty</th>
                                        <th>User Input</th>
                                        <th>Waktu Input</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

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
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="nama_barang">Nama Barang</label>
                                            <input type="text" name="nama_barang" id="modal_nama_barang" class="form-control" readonly required>
                                            <input type="text" name="id" id="modal_id_barang" readonly>
                                            <input type="text" name="kdbarang" id="modal_kdbarang" readonly>
                                            <input type="text" name="dimensi" id="modal_dimensi" readonly>
                                            <input type="hidden" name="action" value="formbyexp">
                                        </div>
                                        <div class="form-group">
                                            <label for="exp_date">Expired Date</label>
                                            <input type="text" name="exp_date" id="modal_exp_date" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="keterangan_isi">Keterangan</label>
                                            <textarea class="form-control" name="keterangan_isi" id="modal_keterangan" required placeholder="Tambahkan keterangan inputer"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="qty_box">Qty Box</label>
                                            <input type="number" name="qty_box" class="form-control" placeholder="0">
                                        </div>
                                        <div class="form-group">
                                            <label for="qty_pcs">Qty Pcs</label>
                                            <input type="number" name="qty_pcs" class="form-control" placeholder="0">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </section>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- /.content-wrapper -->
    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>

    <!-- Modal Add Opname -->



    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    </div>

    <script>
        $(document).ready(function() {
            $('.view-detail').click(function() {
                const nama_barang = $(this).data('nama');
                const exp_date = $(this).data('exp');


                $.ajax({
                    url: "<?= base_url('ics/get_detail_by_exp') ?>",
                    type: "POST",
                    data: {
                        nama_barang: nama_barang,
                        exp_date: exp_date
                    },
                    dataType: "json",
                    success: function(res) {
                        $('#card_detail').show();

                        // Tabel DO
                        let do_html = '';
                        $.each(res.data_do, function(i, v) {
                            do_html += `<tr>
                        <td>${i+1}</td>
                        <td>${v.kd_faktur}</td>
                        <td>${v.tgl_transaksi}</td>
                        <td>${v.qty}</td>
                    </tr>`;
                        });
                        $('#table_do tbody').html(do_html);

                        // Tabel PO
                        let po_html = '';
                        $.each(res.data_po, function(i, v) {
                            po_html += `<tr>
                        <td>${i+1}</td>
                        <td>${v.kd_faktur}</td>
                        <td>${v.tgl_transaksi}</td>
                        <td>${v.qty}</td>
                    </tr>`;
                        });
                        $('#table_po tbody').html(po_html);

                        // Tabel Log Input
                        let log_html = '';
                        $.each(res.data_log, function(i, v) {
                            log_html += `<tr>
                        <td>${i+1}</td>
                        <td>${v.qty}</td>
                        <td>${v.inputer}</td>
                        <td>${v.tgl_input}</td>
                    </tr>`;
                        });
                        $('#table_log tbody').html(log_html);
                    }
                });
            });
        });

        $('.btn-add-opname').click(function() {
            const exp_date = $(this).data('exp');
            const nama_barang = $(this).data('nama');
            const id_barang = $(this).data('id');
            const kdbarang = $(this).data('kdbarang');
            const dimensi = $(this).data('dimensi');

            $('#modal_exp_date').val(exp_date);
            $('#modal_nama_barang').val(nama_barang);
            $('#modal_id_barang').val(id_barang);
            $('#modal_kdbarang').val(kdbarang);
            $('#modal_dimensi').val(dimensi);
        });
    </script>