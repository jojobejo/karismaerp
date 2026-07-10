<?php foreach ($item as $i) : ?>
    <?php $this->load->view('content/stock/nonkomersil/modal/modalstock.php') ?>
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 ml-2">
                    <div class="col-sm-0">
                        <h1 class="m-0">Detail Stock Item : <b style="text-transform:uppercase"><?= $i->nama_barang ?> (<?= $i->satuan ?>) (<?= $i->kode_barang ?>)</b></h1>
                    </div><!-- /.col -->
                    <div class="col-sm-0 ml-2">
                        <a href="<?= base_url('stocknonkomersil') ?>" class="btn btn-sm btn-block btn-success mt-1"><i class="fas fa-undo-alt"></i></a>
                    </div>
                </div><!-- /.row -->

                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center mb-2">
                            <div class="col-auto">
                                <h1 class="m-0">Stock Tersedia : <b style="text-transform:uppercase"><?= $i->qty_ready ?></b></h1>
                            </div>
                            <div class="col-auto">
                                <a class="btn btn-sm btn-success mt-1" data-toggle="modal" data-target="#adjustmentqty<?= $i->kode_sistem ?>">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>

                            <div class="col-auto">
                                <form method="POST" action="<?= base_url('stock/filterqtybytgl'); ?>" class="form-inline">
                                    <label for="start_date" class="mr-2">Tanggal Mulai:</label>
                                    <input type="date" class="form-control mr-3" name="start_date" id="start_date" value="">
                                    <label for="end_date" class="mr-2">Tanggal Akhir:</label>
                                    <input type="date" name="end_date" class="form-control mr-3" id="end_date" value="">
                                    <input type="text" name="kdbarang" class="form-control mr-3" id="kdbarang" value="<?= $i->kode_sistem ?>" hidden>
                                    <button type="submit" class="btn btn-primary">Cari</button>
                                </form>
                            </div>
                        </div>

                        <!-- Tempat menampilkan hasil -->
                        <div id="result"></div>
                        <table class="table table-bordered mb-5">
                            <thead style="background-color: #212529; color:white;">
                                <tr>
                                    <td style="text-align: center;">Kode Transaksi</td>
                                    <td style="text-align: center; width: 5%;">Tanggal Transaksi</td>
                                    <td style="text-align: center;">Kode Akun</td>
                                    <td style="text-align: center;">Keterangan</td>
                                    <td style="text-align: center;">Departemen</td>
                                    <td style="text-align: center;">PIC</td>
                                    <td style="text-align: center;">Qty</td>
                                    <?php if ($this->session->userdata('kode') == 'KEU09') : ?>
                                        <td style="text-align: center;">#</td>
                                    <?php elseif ($this->session->userdata('kode') == 'KEU02') : ?>
                                        <td style="text-align: center;">#</td>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stock as $s) :
                                    $qs     = "";
                                    $btn    = "";
                                    $txt    = "";

                                    // KODE AKUN
                                    if ($s->kd_akun == '11512') {
                                        $qs     = "-";
                                        $btn    = "btn btn-block bg-cstm1 btn-sm color-palette";
                                        $txt    = 'Pengurangan Barang';
                                    } elseif ($s->kd_akun == '11511') {
                                        $qs     = '';
                                        $btn    = "btn btn-block btn-sm bg-cstm2 color-palette";
                                        $txt    = 'Penambahan Barang';
                                    } elseif ($s->kd_akun == '11513') {
                                        $qs     = '';
                                        $btn    = "btn btn-block btn-sm bg-cstm3 color-palette";
                                        $txt    = 'adjustmen stock(+)';
                                    } elseif ($s->kd_akun == '11514') {
                                        $qs     = '-';
                                        $btn    = "btn btn-block btn-sm bg-cstm4 color-palette";
                                        $txt    = 'adjustmen stock(-)';
                                    }
                                ?>
                                    <tr>
                                        <td style="text-align: center; width: 5%;"><?= $s->kd_transaksi ?></td>
                                        <td style="text-align: center;"><?= $s->tgl_transaksi ?></td>
                                        <td>
                                            <a href="#" class="<?= $btn ?>"><b style="text-transform:uppercase; color: #212529;"><?= $txt ?></b></a>
                                        </td>
                                        <td style="text-align: center;"><?= $s->ket ?></td>
                                        <?php if ($s->lvusr == "") : ?>
                                            <td style="text-align: center;">ADMIN</td>
                                            <td style="text-align: center;"><?= $s->inpt ?></td>
                                        <?php else : ?>
                                            <td style="text-align: center;"><?= $s->dep ?></td>
                                            <td style="text-align: center;"><?= $s->nmreq ?></td>
                                        <?php endif; ?>
                                        <td style="text-align: center;"><?= $qs . $s->qty . " " . "(" . $s->nm_satuan . ")" ?></td>
                                        <?php if ($this->session->userdata('kode') == 'KEU09') : ?>
                                            <td style="text-align: center;">
                                                <a href="<?= base_url('tr_trash/1/') . $s->id ?>" class="btn btn-danger btn-sm"><i class="fa fa-trash-alt"></i></a>
                                            </td>
                                        <?php elseif ($this->session->userdata('kode') == 'KEU02') : ?>
                                            <td style="text-align: center;">
                                                <a href="<?= base_url('tr_trash/1/') . $s->id ?>" class="btn btn-danger btn-sm"><i class="fa fa-trash-alt"></i></a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <table class="table table-bordered mt-2">
                            <thead style="background-color: #212529; color:white;">
                                <tr>
                                    <td style="text-align: center;">Kode Transaksi</td>
                                    <td style="text-align: center; width: 5%;">Tanggal Transaksi</td>
                                    <td style="text-align: center;">Kode Akun</td>
                                    <td style="text-align: center;">Keterangan</td>
                                    <td style="text-align: center;">Departemen</td>
                                    <td style="text-align: center;">PIC</td>
                                    <td style="text-align: center;">Qty</td>
                                    <td style="text-align: center;">#</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trash as $trh) : ?>
                                    <tr>
                                        <td><?= $trh->kd_po_nk ?></td>
                                        <td><?= $trh->create_at ?></td>
                                        <td><?= $trh->kd_akun ?></td>
                                        <td><?= $trh->keterangan ?></td>
                                        <td><?= $trh->departemen ?></td>
                                        <td><?= $trh->nm_user ?></td>
                                        <td><?= $trh->tr_qty ?></td>
                                        <td style="text-align: center;"><a href="<?= base_url('tr_trash/2/') . $trh->id_trashbin ?>" class="btn btn-warning btn-sm"><i class="fa fa-redo-alt"></i></a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row mr-2">
                    <div class="col-md-8">
                        <h1><b style="text-transform:uppercase">Stock Tracking</b></h1>
                        <div class="noteDirektur">
                            <table class="table table-bordered table-stripeds">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td class="tdnote">ISI NOTE</td>
                                        <td class="tduser">USER</td>
                                        <td style="text-align: center;">TANGGAL</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($note as $n) : ?>
                                        <tr>
                                            <td><?= $n->isi_note ?></td>
                                            <td><?= $n->nama_user ?></td>
                                            <td><?= $n->create_at ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
    </div>
<?php endforeach; ?>