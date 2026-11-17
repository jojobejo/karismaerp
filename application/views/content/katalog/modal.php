<div class="modal fade" id="tambahbarang">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Data Barang</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('keuangan/add'); ?>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Kode Barang <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="kode_barang_isi" name="kode_barang_isi" placeholder="Isikan kode barang ..." /></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label for="nama_barang" class="col-sm-3 control-label text-right">Nama Barang <span class="requieed">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="nama_barang_isi" name="nama_barang_isi" placeholder="Isikan nama barang ..." /></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Produk Fokus <span class="required">*</span></label>
                        <div class="col-sm-8"><select class="form-control" name="produk_fokus_isi" id="produk_fokus_isi">
                                <option value="">--Pilih Katagori Produk Fokus--</option>
                                <option value="-">-</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Nama Suplier <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="nama_suplier_isi" name="nama_suplier_isi" placeholder="Isikan nama suplier" /></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Katagori Produk <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="katagori_isi" name="katagori_isi" placeholder="Isikan katagori produk" /></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_barang">Bahan Aktif <span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="bhn_aktif_isi" name="bhn_aktif_isi" placeholder="Isikan katagori produk" /></div>
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