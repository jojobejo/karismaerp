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
                            <?php foreach ($get_barang as $nm) :
                                $dimensi = $nm->p * $nm->l * $nm->t;
                            ?>
                                <h5 class="card-title">Detail Inputer Barang - <b><?= $nm->nm_barang ?></b></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="<?= base_url('ics/ics_diffrent') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-arrow-left"></i></a>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-md btn-success" data-toggle="modal" data-target="#modal_insert_new_exp">
                                        <i class="fas fa-plus"></i> Add Expired Baru
                                    </button>
                                </div>
                            </div>

                            <div class="col-auto">
                                <div class="form-group" style="position: relative;background: #fff;">
                                    <h5 class="card-title mt-2 mb-2"><b>Compare By Expired Date</b></h5>
                                    <table id="tb_exp_form" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Expired</th>
                                                <th>Qty</th>
                                                <th>DO</th>
                                                <th>LPB</th>
                                                <th>Qty All</th>
                                                <th>Fisik Qty</th>
                                                <th>Selisih</th>
                                                <th>Fisik BOX</th>
                                                <th>Fisik PCS</th>
                                                <th>Kordinat</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($list_stock_by_exp as $br) : ?>
                                                <tr>
                                                    <td><?= $br->expired ?></td>
                                                    <td><?= $br->qty ?></td>
                                                    <td><?= $br->do ?></td>
                                                    <td><?= $br->po ?></td>
                                                    <td><?= $br->qty_all ?></td>
                                                    <td><?= $br->ics ?></td>
                                                    <td><?= $br->selisih ?></td>
                                                    <td><?= $br->qty_box ?></td>
                                                    <td><?= $br->qty_pcs ?></td>
                                                    <td><?= $br->kordinat ?></td>
                                                    <?php if ($br->status == '1') : ?>
                                                        <td>
                                                            <a href="#" class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                                                            <button class="btn btn-sm btn-info view-detail" data-exp="<?= $br->expired ?>" data-nama="<?= $nm->nm_barang ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-md btn-success btn-add-opname" data-toggle="modal" data-target="#modalAddOpname<?= $br->opname_id ?>" data-exp="<?= $br->expired ?>" data-nama="<?= $nm->nm_barang ?>" data-id="<?= $br->opname_id ?>" data-kdbarang="<?= $br->kd_system ?>" data-dimensi="<?= $br->dimensi ?>"><i class="fas fa-plus"></i></button>
                                                        </td>
                                                    <?php else : ?>
                                                        <td>
                                                            <a href="#" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></a>
                                                            <button class="btn btn-sm btn-info view-detail" data-exp="<?= $br->expired ?>" data-nama="<?= $nm->nm_barang ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-md btn-success btn-add-opname" data-toggle="modal" data-target="#modalAddOpname<?= $br->opname_id ?>" data-exp="<?= $br->expired ?>" data-nama="<?= $nm->nm_barang ?>" data-id="<?= $br->opname_id ?>" data-kdbarang="<?= $br->kd_system ?>" data-dimensi="<?= $br->dimensi ?>"><i class="fas fa-plus"></i></button>
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

                    <div class="card mt-3" id="card_detail" style="display: none;">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title">Detail Data</h5>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <a href="#" class="btn btn-primary" id="btnShowDO">DO</a>
                                </div>
                                <div class="col-auto">
                                    <a href="#" class="btn btn-primary" id="btnShowLPB">LPB</a>
                                </div>
                                <div class="col-auto">
                                    <a href="#" class="btn btn-primary" id="btnShowLogInpt">Log Input</a>
                                </div>
                            </div>

                            <div id="data_log_do" style="display: none;">
                                <table class="table table-bordered table-sm mb-2" id="tbdo_track_barang" style="display: none;">
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
                            </div>

                            <div id="data_log_lpb" style="display: none;">
                                <table class="table table-bordered table-sm" id="tblpb_track_barang">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Faktur</th>
                                            <th>Tgl Transaksi</th>
                                            <th>Qty</th>
                                            <th>Input At</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div id="data_log_inpt" style="display: none;">
                                <table class="table table-bordered table-sm" id="tblog_track_barang">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Qty</th>
                                            <th>Keterangan</th>
                                            <th>User Input</th>
                                            <th>Waktu Input</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                    <?php foreach ($list_stock_by_exp as $br) : ?>
                        <div class="modal fade" id="modalAddOpname<?= $br->opname_id ?>" tabindex="-1" role="dialog" aria-labelledby="modalAddOpnameLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <form action="<?= base_url('ics/sv_opname') ?>" method="post">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success">
                                            <h5 class="modal-title"><i class="fas fa-box"></i> Input Data Opname</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="nama_barang" value="<?= $nm->nm_barang ?>">
                                            <input type="hidden" name="id" value="<?= $br->opname_id ?>">
                                            <input type="hidden" name="kdbarang" value="<?= $br->kd_system ?>">
                                            <input type="hidden" name="dimensi" value="<?= $br->dimensi ?>">
                                            <input type="hidden" name="action" value="formbyexp">
                                            <div class="form-group">
                                                <label for="exp_date">Expired Date</label>
                                                <input type="text" name="exp_date" class="form-control" value="<?= $br->expired ?>" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="keterangan_isi">Keterangan</label>
                                                <textarea class="form-control" name="keterangan_isi" required placeholder="Tambahkan keterangan inputer"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="qty_box">Qty Box</label>
                                                <input type="number" name="qty_box" class="form-control" value="<?= $br->qty_box ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="qty_pcs">Qty Pcs</label>
                                                <input type="number" name="qty_pcs" class="form-control" value="<?= $br->qty_pcs ?>">
                                            </div>

                                            <div class="form-group">
                                                <label for="qty_pcs">Kalkulator</label>
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <input type="text" class="form-control mathInput" placeholder="contoh: 10+5*4+10">
                                                    </div>
                                                    <div class="col-auto">
                                                        <a href="#" class="btn btn-success font-control btnmaths">Calculate</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="result" style="margin-top:15px; font-weight:bold; color:blue;"></div>
                                            <div id="result" style="margin-top:15px; font-weight:bold; color:blue;"></div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Modal Insert New Expired -->
                    <div class="modal fade" id="modal_insert_new_exp" tabindex="-1" role="dialog" aria-labelledby="modalInsertNewExpLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form action="<?= base_url('ics/sv_opname') ?>" method="post">
                                <div class="modal-content">
                                    <div class="modal-header bg-success">
                                        <h5 class="modal-title" id="modalInsertNewExpLabel"><i class="fas fa-plus-circle"></i> Input Saldo Awal</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Nama Barang -->
                                        <div class="form-group" hidden>
                                            <label for="nama_barang">Nama Barang</label>
                                            <input type="text" name="nama_barang" class="form-control" value="<?= $nm->nm_barang ?>" required readonly>
                                            <input type="text" name="dimensi" class="form-control" value="<?= $dimensi ?>" required readonly>
                                            <input type="text" name="action" class="form-control" value="new_expired" required readonly>
                                            <input type="text" name="kdbarang" class="form-control" value="<?= $nm->kd_system ?>" required readonly>
                                            <input type="text" name="keterangan_isi" class="form-control" value="input_expired_baru" required readonly>
                                        </div>
                                        <!-- Expired Date -->
                                        <div class="form-group">
                                            <label for="exp_date">Expired Date</label>
                                            <input type="date" name="exp_date" class="form-control" required>
                                        </div>
                                        <!-- Qty Box -->
                                        <div class="form-group" hidden>
                                            <label for="qty_box">Qty Box</label>
                                            <input type="number" name="qty_box" class="form-control" placeholder="0" value="0" required>
                                        </div>
                                        <!-- Qty Pcs -->
                                        <div class="form-group" hidden>
                                            <label for="qty_pcs">Qty Pcs</label>
                                            <input type="number" name="qty_pcs" class="form-control" placeholder="0" value="0" required>
                                        </div>
                                        <!-- Waktu Input -->
                                        <input type="hidden" name="input_at" value="<?= date('d/m/Y') ?>">
                                        <input type="hidden" name="create_at" value="<?= date('Y-m-d H:i:s') ?>">
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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
    <aside class="control-sidebar control-sidebar-dark">
    </aside>
    </div>

    <script>
        $(document).ready(function() {

            $('.view-detail').on('click', function() {
                $('.view-detail').removeClass('btn-secondary').addClass('btn-info');
                $(this).removeClass('btn-info').addClass('btn-secondary');
            });

            $('#btnShowDO, #btnShowLPB, #btnShowLogInpt').on('click', function() {
                $('#btnShowDO, #btnShowLPB, #btnShowLogInpt').removeClass('btn-secondary').addClass('btn-primary');
                $(this).removeClass('btn-primary').addClass('btn-secondary');
            });

            $('#btnShowDO').click(function() {
                $('#data_log_do').show();
                $('#tbdo_track_barang').show();
                $('#data_log_lpb, #data_log_inpt').hide();
            });

            $('#btnShowLPB').click(function() {
                $('#data_log_lpb').show();
                $('#tblpb_track_barang').show();
                $('#data_log_do, #data_log_inpt').hide();
            });

            $('#btnShowLogInpt').click(function() {
                $('#data_log_inpt').show();
                $('#tblog_track_barang').show();
                $('#data_log_do, #data_log_lpb').hide();
            });
        });
    </script>

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

                        $('#tbdo_track_barang tbody').html('');
                        $('#tblpb_track_barang tbody').html('');
                        $('#tblog_track_barang tbody').html('');

                        let do_html = '';
                        $.each(res.data_do, function(i, item) {
                            do_html += `<tr>
                            <td>${i + 1}</td>
                            <td>${item.kd_faktur}</td>
                            <td>${item.tgl_transaksi}</td>
                            <td>${item.qty}</td>
                        </tr>`;
                        });
                        $('#tbdo_track_barang tbody').html(do_html);

                        let po_html = '';
                        $.each(res.data_po, function(i, item) {
                            po_html += `<tr>
                            <td>${i + 1}</td>
                            <td>${item.kd_faktur_lpb}</td>
                            <td>${item.tgl_transaksi}</td>
                            <td>${item.qty}</td>
                            <td>${item.input_at}</td>
                        </tr>`;
                        });
                        $('#tblpb_track_barang tbody').html(po_html);

                        let log_html = '';
                        $.each(res.data_log, function(i, item) {
                            log_html += `<tr>
                            <td>${i + 1}</td>
                            <td>${item.qty}</td>
                            <td>${item.keterangan}</td>
                            <td>${item.inputer}</td>
                            <td>${item.tgl_input}</td>
                        </tr>`;
                        });
                        $('#tblog_track_barang tbody').html(log_html);
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

    <script>
        $(document).ready(function() {
            $('input[name="qty_box"], input[name="qty_pcs"]').on('input', function() {
                let qty_box = parseInt($('input[name="qty_box"]').val()) || 0;
                let qty_pcs = parseInt($('input[name="qty_pcs"]').val()) || 0;
                let total = qty_box + qty_pcs;
                $('#qty_total_input').val(total);
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".btnmaths").forEach(function(btn) {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    let parent = btn.closest(".modal-body"); // cari parent modal
                    let input = parent.querySelector(".mathInput").value.trim();
                    let resultBox = parent.querySelector(".result");

                    if (input === "") {
                        resultBox.innerHTML = "Masukkan ekspresi matematika!";
                        return;
                    }

                    // validasi hanya boleh angka, operator + - * / ( )
                    let validPattern = /^[0-9+\-*/().\s]+$/;
                    if (!validPattern.test(input)) {
                        resultBox.innerHTML = "Input hanya boleh angka dan operator + - * / ( )";
                        return;
                    }

                    try {
                        let result = Function("return " + input)();
                        resultBox.innerHTML = "= " + result;
                    } catch (err) {
                        resultBox.innerHTML = "Ekspresi tidak valid!";
                    }
                });
            });
        });
    </script>