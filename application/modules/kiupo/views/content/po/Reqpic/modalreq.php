<?php foreach ($lstock as $l) : ?>
    <div class="modal fade" id="addreq<?= $l->kode_sys ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Item Request - <span style="text-transform:uppercase"><b><?= $l->nama_barang ?></b></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo form_open_multipart('addtmpreqbarang'); ?>
                    <div class="form-group">
                        <div class="row" hidden>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" id="kdbys" name="kdbys" value="<?= $l->kode_sys ?>" readonly />
                                <input class="form-control" type="text" id="kdbr" name="kdbr" value="<?= $l->kode_adm ?>" readonly />
                                <input class="form-control" type="text" id="idsat" name="idsat" value="<?= $l->id_satuan ?>" readonly />
                                <input class="form-control" type="text" id="katbr" name="katbr" value="<?= $l->kat_barang ?>" readonly />
                                <input class="form-control" type="text" id="nm_barang" name="nm_barang" value="<?= $l->nama_barang ?>" readonly />
                                <input class="form-control" type="text" id="descnk_isi" name="descnk_isi" value="<?= $l->descnk ?>" readonly />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="kd_user">Keterangan<span class="required">*</span></label>
                            <div class="col-sm-8"><input class="form-control" type="text" id="ket_isi" name="ket_isi" value="" placeholder="Inputkan keterangan kebutuhan" /></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="kd_user">QTY<span class="required">*</span></label>
                            <div class="col-sm-8"><input class="form-control" type="number" id="qty_isi" name="qty_isi" value="" placeholder="Inputkan jumlah kebutuhan" /></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
<?php endforeach; ?>

<div class="modal fade" id="reqmasterbarang">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Request Master Barang</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('add_mbarang_tmp'); ?>

                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_user">Nama Barang<span class="required">*</span></label>
                        <div class="col-sm-8">
                            <input class="form-control" type="text" id="nmbarang" name="nmbarang" value="" placeholder="Inputkan Nama Barang" />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_user">Deskripsi<span class="required">*</span></label>
                        <div class="col-sm-8"><textarea name="descisi" id="descisi" class="form-control" placeholder="Inputkan deskripsi barang"></textarea></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_user">Satuan<span class="required">*</span></label>
                        <div class="col-sm-8">
                            <select name="stuanbr" id="stuanbr" class="form-control">
                                <option value="0" disabled selected>Pilih Satuan Barang</option>
                                <?php foreach ($satuan as $s) : ?>
                                    <option id="stuanbrpil" name="stuanbrpil" value="<?= $s->id ?>"><?= $s->nm_satuan ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>