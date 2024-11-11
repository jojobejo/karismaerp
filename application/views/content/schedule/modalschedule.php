    <div class="modal fade" id="addschedule">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Schedule</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo form_open_multipart('addschedule'); ?>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">Tanggal<span class="required">*</span></label>
                            <div class="col-sm-8">
                                <input class="form-control" type="date" id="tgl" name="tgl" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">Jam<span class="required">*</span></label>
                            <div class="col-sm-8">
                                <input class="form-control" type="time" id="jam" name="jam" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">Instansi<span class="required">*</span></label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" id="instansi" name="instansi" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">PIC<span class="required">*</span></label>
                            <div class="col-sm-8">
                                <input class="form-control" type="texts" id="pic" name="pic" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">Estimasi<span class="required">*</span></label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" id="estimasi" name="estimasi" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" for="id_bar">Tujuan<span class="required">*</span></label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" id="tujuan" name="tujuan" />
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
    </div>

    <?php foreach ($getschedule as $g) : ?>
        <!-- MODAL EDIT -->
        <div class="modal fade" id="editedschedule<?= $g->id ?>">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Schedule</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php echo form_open_multipart('editchedule'); ?>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">Tanggal<span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="date" id="tgl" name="tgl" value="<?= $g->tanggal ?>" />
                                    <input class="form-control" type="text" id="id" name="id" value="<?= $g->id ?>" readonly hidden />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">Jam<span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="time" id="jam" name="jam" value="<?= $g->jam ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">Instansi<span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="text" id="instansi" name="instansi" value="<?= $g->suplier ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">PIC<span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="texts" id="pic" name="pic" value="<?= $g->pic ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">Estimasi<span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="text" id="estimasi" name="estimasi" value="<?= $g->estimasi_end ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">Tujuan<span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="text" id="tujuan" name="tujuan" value="<?= $g->tujuan ?>" />
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
        </div>
    <?php endforeach; ?>

    <!-- MODAL schedule -->
    <?php foreach ($getschedule as $g) : ?>
        <div class="modal fade" id="reschedule<?= $g->id ?>">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Schedule</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php echo form_open_multipart('reschedule'); ?>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">Tanggal<span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="date" id="tgl" name="tgl" value="<?= $g->tanggal ?>" />
                                    <input class="form-control" type="text" id="id" name="id" value="<?= $g->id ?>" readonly hidden />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">Jam<span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="time" id="jam" name="jam" value="<?= $g->jam ?>" />
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
        </div>
    <?php endforeach; ?>
    <?php foreach ($getschedule as $g) : ?>
        <div class="modal fade" id="deleteschedule<?= $g->id ?>">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Delete Schedule</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <?php echo form_open_multipart('deleteschedule'); ?>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label text-right" for="id_bar">Schedule akan terhapus </label>
                                <div class="col-sm-8" hidden>
                                    <input class="form-control" type="date" id="tgl" name="tgl" value="<?= $g->tanggal ?>" />
                                    <input class="form-control" type="text" id="id" name="id" value="<?= $g->id ?>" readonly hidden />
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
        </div>
    <?php endforeach; ?>