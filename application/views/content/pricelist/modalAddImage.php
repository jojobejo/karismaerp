<?php $kodeID = $this->input->get('id'); ?>

<div class="modal fade" id="edit1<?php echo $kodeID ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Data Barang</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('pricelist/addPromoImg1'); ?>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">id barang<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="id_bar" name="id_bar" value="<?php echo $title['id_barang'] ?>" /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Kode Barang <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="kode_barang_isi" name="kode_barang_isi" value="<?php echo $title['kode_barang'] ?>" /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label for="nama_barang" class="col-sm-3 control-label text-right">Nama Barang <span class="requieed">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="nama_barang_isi" name="nama_barang_isi" value="<?php echo $title['nama_barang'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label for="nama_barang" class="col-sm-3 control-label text-right">Produk Fokus<span class="requieed">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="produk_fks_isi" name="produk_fks_isi" value="<?php echo $title['produk_fokus'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Nama Suplier <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="nama_suplier_isi" name="nama_suplier_isi" value="<?php echo $title['nama_suplier'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Katagori Produk <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="katagori_isi" name="katagori_isi" value="<?php echo $title['bahan_aktif'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">gbr produk<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="gbr_produk1_isi" name="gbr_produk1_isi" value="<?php echo $title['gbr_produk'] ?> " /></div>
                    </div>
                </div>
                <label for="customFile">Upload Gambar Produk</label>
                <div class="form-group">
                    <div class="form-group">
                        <input type="file" class="form-control" id="gambar_1" name="gambar_1" accept="img/*">
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">gbr promo 2<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="gbr_produk2_isi" name="gbr_produk2_isi" value="<?php echo $title['gbr_promo2'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">gbr promo 3<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="gbr_produk3_isi" name="gbr_produk3_isi" value="<?php echo $title['gbr_promo3'] ?> " /></div>
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

<div class="modal fade" id="edit2<?php echo $kodeID ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Data Barang</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('pricelist/addPromoImg2'); ?>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">id barang<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="id_bar" name="id_bar" value="<?php echo $title['id_barang'] ?>" /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Kode Barang <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="kode_barang_isi" name="kode_barang_isi" value="<?php echo $title['kode_barang'] ?>" /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label for="nama_barang" class="col-sm-3 control-label text-right">Nama Barang <span class="requieed">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="nama_barang_isi" name="nama_barang_isi" value="<?php echo $title['nama_barang'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label for="nama_barang" class="col-sm-3 control-label text-right">Produk Fokus<span class="requieed">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="produk_fks_isi" name="produk_fks_isi" value="<?php echo $title['produk_fokus'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Nama Suplier <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="nama_suplier_isi" name="nama_suplier_isi" value="<?php echo $title['nama_suplier'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Katagori Produk <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="katagori_isi" name="katagori_isi" value="<?php echo $title['bahan_aktif'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">gbr produk<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="gbr_produk1_isi" name="gbr_produk1_isi" value="<?php echo $title['gbr_produk'] ?> " /></div>
                    </div>
                </div>
                <label for="customFile">Upload Gambar Produk</label>
                <div class="form-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="gambar_1" name="gambar_1" accept="img/*">
                        <label class="custom-file-label" for="customFile">Pilih Gambar Produk</label>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">gbr promo 1<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="gbr_produk2_isi" name="gbr_produk2_isi" value="<?php echo $title['gbr_promo1'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">gbr promo 3<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="gbr_produk3_isi" name="gbr_produk3_isi" value="<?php echo $title['gbr_promo3'] ?> " /></div>
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

<div class="modal fade" id="edit3<?php echo $kodeID ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Data Barang</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('pricelist/addPromoImg3'); ?>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">id barang<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="id_bar" name="id_bar" value="<?php echo $title['id_barang'] ?>" /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Kode Barang <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="kode_barang_isi" name="kode_barang_isi" value="<?php echo $title['kode_barang'] ?>" /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label for="nama_barang" class="col-sm-3 control-label text-right">Nama Barang <span class="requieed">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="nama_barang_isi" name="nama_barang_isi" value="<?php echo $title['nama_barang'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label for="nama_barang" class="col-sm-3 control-label text-right">Produk Fokus<span class="requieed">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="produk_fks_isi" name="produk_fks_isi" value="<?php echo $title['produk_fokus'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Nama Suplier <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="nama_suplier_isi" name="nama_suplier_isi" value="<?php echo $title['nama_suplier'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Katagori Produk <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="katagori_isi" name="katagori_isi" value="<?php echo $title['bahan_aktif'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">gbr produk<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="gbr_produk1_isi" name="gbr_produk1_isi" value="<?php echo $title['gbr_produk'] ?> " /></div>
                    </div>
                </div>
                <label for="customFile">Upload Gambar Produk</label>
                <div class="form-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="gambar_1" name="gambar_1" accept="img/*">
                        <label class="custom-file-label" for="customFile">Pilih Gambar Produk</label>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">gbr promo 1<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="gbr_produk2_isi" name="gbr_produk2_isi" value="<?php echo $title['gbr_promo1'] ?> " /></div>
                    </div>
                </div>
                <div class="form-group" hidden>
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">gbr promo 2<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="gbr_produk3_isi" name="gbr_produk3_isi" value="<?php echo $title['gbr_promo2'] ?> " /></div>
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