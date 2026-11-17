<!-- MODAL EDIT  -->
<?php foreach ($harga as $h) : ?>
    <!-- MODAL EDIT  -->
    <div class="card  p-2" style="font-weight:bold">
        <?php if ($this->session->userdata('hak_akses') != '4') { ?>
            <div style="background-color:#e1f0c5;" class="card card-body">
                <table>
                    <tbody>
                        <tr>
                            <td width="80%">Harga Terendah/QTY/R1/Partai</td>
                            <td width="20%" style="font-weight: bold;">Rp. <?php echo number_format($h->harga_r1); ?></td>
                            <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                                <td>
                                    <a href="#" class="btn btn-success btn-sm " data-toggle="modal" data-target="#editmodalR1<?php echo $h->id_barang ?>">
                                        <i class="fa fa-solid fa-pencil-alt"></i>
                                    </a>
                                </td>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
        <?php if ($this->session->userdata('hak_akses') != '4') { ?>
            <div style="background-color:#f0c5c5;" class="card card-body mt-2">
                <table>
                    <tbody>
                        <tr>
                            <td width="80%">Harga Ecer/Kios/R2</td>
                            <td width="20%" style="font-weight: bold;">Rp. <?php echo number_format($h->harga_r2); ?></td>
                            <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                                <td>
                                    <a href="#" class="btn btn-success btn-sm " data-toggle="modal" data-target="#editmodalR2<?php echo $h->id_barang ?>">
                                        <i class="fa fa-solid fa-pencil-alt"></i>
                                    </a>
                                </td>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
        <?php if ($this->session->userdata('hak_akses') != '4') { ?>
            <div style="background-color:#f0dfc5" class="card card-body mt-2">
                <table>
                    <tbody>
                        <tr>
                            <td width="80%">Harga Program</td>
                            <td width="20%" style="font-weight: bold;">Rp. <?php echo number_format($h->harga_program); ?></td>
                            <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                                <td>
                                    <a href="#" class="btn btn-success btn-sm " data-toggle="modal" data-target="#editmodalR3<?php echo $h->id_barang ?>">
                                        <i class="fa fa-solid fa-pencil-alt"></i>
                                    </a>
                                </td>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
        <?php if ($this->session->userdata('hak_akses') != '4') { ?>
            <div style="background-color:#a0dfc5" class="card card-body mt-2">
                <table>
                    <tbody>
                        <tr>
                            <td width="80%">Harga Online</td>
                            <td width="20%" style="font-weight: bold;">Rp. <?php echo number_format($h->harga_online); ?></td>
                            <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                                <td>
                                    <a href="#" class="btn btn-success btn-sm " data-toggle="modal" data-target="#editmodalR4<?php echo $h->id_barang ?>">
                                        <i class="fa fa-solid fa-pencil-alt"></i>
                                    </a>
                                </td>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div> <!-- /end-div-awal -->

    <!-- MODAL EDIT R1 -->
    <div class="modal fade" id="editmodalR1<?php echo $h->id_barang ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Data List Price</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo form_open_multipart('pricelist/editSpecialPrice'); ?>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">Harga Terendah/QTY/R1/Partai<span class="required">*</span></label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" id="id_isi" name="id_isi" value="<?php echo $h->id_barang ?>" hidden />
                                <input class="form-control" type="text" id="kode_barang_isi" name="kode_barang_isi" value="<?php echo $h->kode_barang ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi1" name="harga_isi1" value="<?php echo $h->harga_r1  ?>" />
                                <input class="form-control" type="text" id="harga_isi2" name="harga_isi2" value="<?php echo $h->harga_r2  ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi3" name="harga_isi3" value="<?php echo $h->harga_program  ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi4" name="harga_isi4" value="<?php echo $h->harga_online  ?>" hidden />
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
        <!-- /.modal -->
    </div>
    <!-- MODAL EDIT R2 -->
    <div class="modal fade" id="editmodalR2<?php echo $h->id_barang ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Data List Price</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo form_open_multipart('pricelist/editSpecialPrice'); ?>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">Harga Ecer/Kios/R2<span class="required">*</span></label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" id="id_isi" name="id_isi" value="<?php echo $h->id_barang ?>" hidden />
                                <input class="form-control" type="text" id="kode_barang_isi" name="kode_barang_isi" value="<?php echo $h->kode_barang ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi1" name="harga_isi1" value="<?php echo $h->harga_r1  ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi2" name="harga_isi2" value="<?php echo $h->harga_r2  ?>" />
                                <input class="form-control" type="text" id="harga_isi3" name="harga_isi3" value="<?php echo $h->harga_program  ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi4" name="harga_isi4" value="<?php echo $h->harga_online  ?>" hidden />
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
        <!-- /.modal -->
    </div>
    <!-- MODAL EDIT PROGRAM -->
    <div class="modal fade" id="editmodalR3<?php echo $h->id_barang ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Data List Price</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo form_open_multipart('pricelist/editSpecialPrice'); ?>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">Harga Program<span class="required">*</span></label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" id="id_isi" name="id_isi" value="<?php echo $h->id_barang ?>" hidden />
                                <input class="form-control" type="text" id="kode_barang_isi" name="kode_barang_isi" value="<?php echo $h->kode_barang ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi1" name="harga_isi1" value="<?php echo $h->harga_r1  ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi2" name="harga_isi2" value="<?php echo $h->harga_r2  ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi3" name="harga_isi3" value="<?php echo $h->harga_program  ?>" />
                                <input class="form-control" type="text" id="harga_isi4" name="harga_isi4" value="<?php echo $h->harga_online  ?>" hidden />
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
        <!-- /.modal -->
    </div>
    <!-- MODAL EDIT ONLINE-->
    <div class="modal fade" id="editmodalR4<?php echo $h->id_barang ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Data List Price</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo form_open_multipart('pricelist/editSpecialPrice'); ?>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">Harga Online<span class="required">*</span></label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" id="id_isi" name="id_isi" value="<?php echo $h->id_barang ?>" hidden />
                                <input class="form-control" type="text" id="kode_barang_isi" name="kode_barang_isi" value="<?php echo $h->kode_barang ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi1" name="harga_isi1" value="<?php echo $h->harga_r1  ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi2" name="harga_isi2" value="<?php echo $h->harga_r2  ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi3" name="harga_isi3" value="<?php echo $h->harga_program  ?>" hidden />
                                <input class="form-control" type="text" id="harga_isi4" name="harga_isi4" value="<?php echo $h->harga_online  ?>" />
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
        <!-- /.modal -->
    </div>
<?php endforeach; ?>