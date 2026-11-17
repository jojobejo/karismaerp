<!-- /.modal -->
<?php foreach ($barang as $b) : ?>
    <div class="modal fade" id="upload<?php echo $b->id_barang ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Upload Foto Produk &nbsp; <?= $b->nama_barang ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo form_open_multipart('katalog/uploadProduk'); ?>
                    <div class="form-group" hidden>
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">id barang <span class="required">*</span></label>
                            <div class="col-sm-8"><input class="form-control" type="text" id="id_bar" name="id_bar" value="<?php echo $b->id_barang ?>" readonly /></div>
                        </div>
                    </div>
                    <div class="form-group" hidden>
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="kd_barang">Kode Barang <span class="required">*</span></label>
                            <div class="col-sm-8"><input class="form-control" type="text" id="kode_barang_isi" name="kode_barang_isi" value="<?= $b->kode_barang ?>" /></div>
                        </div>
                    </div>
                    <div class="form-group" hidden>
                        <div class="row">
                            <label for="nama_barang" class="col-sm-3 control-label text-right">Nama Barang <span class="requieed">*</span></label>
                            <div class="col-sm-8"><input class="form-control" type="text" id="nama_barang_isi" name="nama_barang_isi" value="<?= $b->nama_barang ?>" /></div>
                        </div>
                    </div>
                    <div class="form-group" hidden>
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="kd_barang">Produk Fokus <span class="required">*</span></label>
                            <div class="col-sm-8"><select class="form-control" name="produk_fokus_isi" id="produk_fokus_isi">
                                    <option value="<?= $b->produk_fokus ?>" selected><?= $b->produk_fokus ?></option>
                                </select></div>
                        </div>
                    </div>
                    <div class="form-group" hidden>
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="kd_barang">Nama Suplier <span class="required">*</span></label>
                            <div class="col-sm-8"><input class="form-control" type="text" id="nama_suplier_isi" name="nama_suplier_isi" value="<?= $b->nama_suplier ?>" /></div>
                        </div>
                    </div>
                    <div class="form-group" hidden>
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="kd_barang">Katagori Produk <span class="required">*</span></label>
                            <div class="col-sm-8"><input class="form-control" type="text" id="katagori_isi" name="katagori_isi" value="<?= $b->bahan_aktif ?>" /></div>
                        </div>
                    </div>

                    <label for="customFile">Upload Gambar Produk</label>
                    <div class="form-group">
                        <div class="form-group">
                            <input type="file" class="form-control" id="gambar_1" name="gambar_1" accept="img/*">
                        </div>
                    </div>

                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
<?php endforeach; ?>
<!-- /.modal -->