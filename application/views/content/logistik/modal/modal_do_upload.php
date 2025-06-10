<div class="modal fade" id="muploadlog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Data Prepare DO</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" enctype="multipart/form-data" action="<?= base_url('csv_import') ?>">
                    <?php date_default_timezone_set("Asia/Jakarta");
                    $now = date('Y-m-d H:i:s'); ?>
                    <div class="form-group">
                        <select name="gdgid" id="gdgid" class="form-control">
                            <option value="6">Delivery Order</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="input-group">

                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="exampleInputFile" name="csv_file" required>
                                <input type="text" class="form-control" name="kdgenerates" id="kdgenerates" value="<?= $kdgenerate ?>" hidden>
                                <input type="text" class="form-control" name="dateupload" id="dateupload" value="<?= $now ?>" hidden>
                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                            </div>

                            <div class="input-group-append">
                                <span class="input-group-text">Upload</span>
                            </div>

                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm btn-block">Upload</button>
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->