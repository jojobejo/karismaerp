<div class="modal fade" id="modalAddUser">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah User Baru</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo form_open_multipart('adduser'); ?>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="kd_user">Kode User<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="kode_isi" name="kode_isi" value="" /></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="id_bar">Nama User<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="nama_isi" name="nama_isi" value="" placeholder="input Nama Pengguna" /></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="id_bar">Departemen<span class="required">*</span></label>
                        <div class="col-sm-8">
                            <select class="form-control" name="departemen_isi" id="departemen_isi">
                                <option value="-" selected>-- Pilih Departemen --</option>
                                <option value="KEUANGAN">KEUANGAN</option>
                                <option value="DIREKTUR">DIREKTUR</option>
                                <option value="IT">IT</option>
                                <option value="SALES">SALES</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="id_bar">Username<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="text" id="username_isi" name="username_isi" value="" placeholder="Input Username" /></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="id_bar">Password<span class="required">*</span></label>
                        <div class="col-sm-8"><input class="form-control" type="password" id="password_isi" name="password_isi" value="" placeholder="Input Password" /></div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <label class="col-sm-3 control-label text-right" for="id_bar">Level User<span class="required">*</span></label>
                        <div class="col-sm-8"><select class="form-control" name="level_isi" id="level_isi">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </Select></div>
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