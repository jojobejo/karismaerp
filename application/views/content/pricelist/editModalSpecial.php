<?php foreach ($harga as $d) : ?>
    <div class="modal fade bd-example-modal-lg" id="editmodalSpecial<?= $d->id ?>" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto" id="editModalLabel">Edit Harga</h5>
                </div>
                <div class="modal-body">
                    <div>
                        <form action="<?php echo base_url("pricelist/updatePrice") ?>" method="post">
                            <div class="form-group" hidden>
                                <div class="row">
                                    <label class="col-sm-3 mt-2 control-label text-right" name="id_isi" id="id_isi">ID BARANG</label><strong class="mt-1">:</strong>
                                    <div class="col-sm-8"><input class="form-control" type="text" name="id_isi" id="id_isi" value="<?php echo $d->id ?>"></div>
                                </div>
                            </div>
                            <div class="form-group" hidden>
                                <div class="row">
                                    <label class="col-sm-3 mt-2 control-label text-right" name="id_isi" id="id_isi">ID BARANG</label><strong class="mt-1">:</strong>
                                    <div class="col-sm-8"><input class="form-control" type="text" name="kd_isi" id="kd_isi" value="<?= $d->kode_barang ?>"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 mt-2 control-label text-right" name="r1_isi" id="r1_isi">Harga R1</label><strong class="mt-1">:</strong>
                                    <div class="col-sm-8"><input class="form-control" type="text" name="r1_isi" id="r1_isi" value="<?= $d->harga_r1 ?>"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 mt-2 control-label text-right" name="r2_isi" id="r2_isi">Harga R2</label><strong class="mt-1">:</strong>
                                    <div class="col-sm-8"><input class="form-control" type="text" name="r2_isi" id="r2_isi" value="<?= $d->harga_r2 ?>"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 mt-2 control-label text-right" name="r3_isi" id="r3_isi">Harga Program</label><strong class="mt-1">:</strong>
                                    <div class="col-sm-8"><input class="form-control" type="text" name="r3_isi" id="r3_isi" value="<?= $d->harga_program ?>"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class="col-sm-3 mt-2 control-label text-right" name="r4_isi" id="r4_isi">Harga Online</label><strong class="mt-1">:</strong>
                                    <div class="col-sm-8"><input class="form-control" type="text" name="r4_isi" id="r4_isi" value="<?= $d->harga_online ?>"></div>
                                </div>
                            </div>
                            <input class="btn btn-success" type="submit" name="btn" value="Save" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach ?>

<!-- /.modal -->
<?php foreach ($harga as $b) : ?>
    <div class="modal fade" id="hapus<?php echo $b->id ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <h3>!!!Anda akan menghapus data !!! </h3>
                    <br>
                    <h3><?php echo $b->nama_barang ?></h3>
                    <br>
                    <h3>Data yang sudah terhapus tidak dapat di kembalikan lagi</h3>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
                    <a class="btn btn-danger" href="<?php echo base_url("pricelist/hapusHargaEcer/$b->id/$b->kode_barang") ?>">Hapus</a>
                </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
<?php endforeach; ?>
<!-- /.modal -->