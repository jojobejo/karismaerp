<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <h1 class="m-0">List Request PIC</h1>
                        </div>
                        <div class="col-sm mb-2">
                            <a href="<?= base_url('reqpic') ?>" class="btn btn-md btn-primary btn-block"><i class="fas fa-home"></i></a>
                        </div>
                        <div class="col-sm mb-2">
                            <a href="<?= base_url('reqpicaccreq') ?>" class="btn btn-md btn-warning btn-block"><b>REQUEST ACC</b></a>
                        </div>
                        <div class="col-sm mb-2">
                            <a href="<?= base_url('index_brsedia') ?>" class="btn btn-md btn-info btn-block"><b>BARANG TERSEDIA</b></a>
                        </div>
                        <div class="col-sm mb-2">
                            <a href="<?= base_url('index_done') ?>" class="btn btn-md btn-success btn-block"><b>DONE</b></a>
                        </div>
                    </div> <!-- END ROW -->
                    <table class="table table-bordered" id="list_reqpic">
                        <thead class="table-dark">
                            <tr>
                                <td>Nama Pengaju</td>
                                <td>Departemen</td>
                                <td>Tanggal Transaksi</td>
                                <td style="width: min-content;">Tujuan Pembelian</td>
                                <td>Status</td>
                                <td>Status PO</td>
                                <td>#</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($getlistpic as $g) : ?>
                                <tr>
                                    <td><?= $g->nm_user ?></td>
                                    <td><?= $g->departemen ?></td>
                                    <td><?= format_tgl_lahir($g->tgl_transaksi) ?></td>
                                    <td style="width: min-content;"><?= $g->tj_pembelian ?></td>
                                    <td>
                                        <a class="btn btn-block btn-warning btn-sm m-1"><b><?= $g->status ?></b></a>
                                    </td>
                                    <td>
                                        <?php if ($g->status_po == 'ON PROGRESS - KADEP') : ?>
                                            <a class="btn btn-block btn-warning btn-sm"><b><?= $g->status_po ?></b></a>
                                        <?php elseif ($g->status_po == 'ACC-KADEP') : ?>
                                            <a class="btn btn-block btn-primary btn-sm"><b><?= $g->status_po ?></b></a>
                                        <?php elseif ($g->status_po == 'SEDANG DIAJUKAN') : ?>
                                            <a class="btn btn-block btn-warning btn-sm"><b><?= $g->status_po ?></b></a>
                                        <?php elseif ($g->status_po == 'ACC DIREKTUR') : ?>
                                            <a class="btn btn-block btn-primary btn-sm"><b><?= $g->status_po ?></b></a>
                                        <?php elseif ($g->status_po == 'PROSES PEMBELIAN') : ?>
                                            <a class="btn btn-block btn-primary btn-sm"><b><?= $g->status_po ?></b></a>
                                        <?php elseif ($g->status_po == 'DONE') : ?>
                                            <a class="btn btn-block btn-success btn-sm"><b><?= $g->status_po ?></b></a>
                                        <?php elseif ($g->status_po == 'REJECT') : ?>
                                            <a class="btn btn-block btn-danger btn-sm"><b><?= $g->status_po ?></b></a>
                                        <?php elseif ($g->status_po == '0') : ?>
                                            <a class="btn btn-block btn-secondary btn-sm"><b>BARANG READY</b></a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col">
                                                <a class="btn btn-block btn-primary btn-sm" href="<?= base_url('reqpic/detreqbarangpic/' . $g->kd_po_nk) ?>"><i class="fas fa-eye"></i></a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div> <!-- END CARD BODY -->
            </div> <!-- END CARD -->
        </div>
    </div>
</div>