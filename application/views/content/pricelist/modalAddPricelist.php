<!-- /.modal -->
<?php ?>
<?php $kodeID = $this->input->get('id'); ?>
<div class="modal fade" id="modalAddQty">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Tambahkan Data List Price</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php echo form_open_multipart('pricelist/insertQtyPrice'); ?>
        <div class="form-group">
          <div class="row">
            <label class="col-sm-3 control-label text-right" for="id_bar">Nama Barang Kemasan<span class="required">*</span></label>
            <div class="col-sm-8"><input class="form-control" type="text" id="nm_barang" name="nm_barang" value="" placeholder="Input Nama Barang Kemasan" />
              <input class="form-control" type="text" id="kdbarang" name="kdbarang" value="<?php echo $kodeID ?>" hidden />
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="row">
            <label class="col-sm-3 control-label text-right" for="id_bar">Harga Qty 1<span class="required">*</span></label>
            <div class="row">
              <div class="col-sm ml-2">
                <input class="form-control" type="text" id="ket_1" name="ket_1" value="" placeholder="Nama Qty 1" />
              </div>
              <div class="col-sm">
                <input class="form-control" type="text" id="hr_1" name="hr_1" value="" placeholder="Rp." />
              </div>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="row">
            <label class="col-sm-3 control-label text-right" for="id_bar">Harga Qty 2<span class="required">*</span></label>
            <div class="row">
              <div class="col-sm ml-2">
                <input class="form-control" type="text" id="ket_2" name="ket_2" value="" placeholder="Nama Qty 2" />
              </div>
              <div class="col-sm">
                <input class="form-control" type="text" id="hr_2" name="hr_2" value="" placeholder="Rp." />
              </div>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="row">
            <label class="col-sm-3 control-label text-right" for="id_bar">Harga Qty 3<span class="required">*</span></label>
            <div class="row">
              <div class="col-sm ml-2">
                <input class="form-control" type="text" id="ket_3" name="ket_3" value="" placeholder="Nama Qty 3" />
              </div>
              <div class="col-sm">
                <input class="form-control" type="text" id="hr_3" name="hr_3" value="" placeholder="Rp." />
              </div>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="row">
            <label class="col-sm-3 control-label text-right" for="id_bar">Harga Qty 4<span class="required">*</span></label>
            <div class="row">
              <div class="col-sm ml-2">
                <input class="form-control" type="text" id="ket_4" name="ket_4" value="" placeholder="Nama Qty 4" />
              </div>
              <div class="col-sm">
                <input class="form-control" type="text" id="hr_4" name="hr_4" value="" placeholder="Rp." />
              </div>
            </div>
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

<!-- /.modal -->
<?php ?>
<?php $kodeID = $this->input->get('id'); ?>
<?php foreach ($price as $p) : ?>
  <div class="modal fade" id="edit<?= $p->id_pricelist ?>">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Harga</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <?php echo form_open_multipart('pricelist/editHargaPrice'); ?>

          <div class="form-group">
            <div class="row">
              <label class="col-sm-3 control-label text-right" for="id_bar">Nama Barang Kemasan<span class="required">*</span></label>
              <div class="col-sm-8"><input class="form-control" type="text" id="nm_barang" name="nm_barang" value="<?= $p->nama_barang ?>" placeholder="Input Nama Barang Kemasan" />
                <input class="form-control" type="text" id="kdbarang" name="kdbarang" value="<?php echo $kodeID ?>" hidden />
                <input class="form-control" type="text" id="idharga" name="idharga" value="<?= $p->id_pricelist ?>" hidden />
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-sm-3 control-label text-right" for="id_bar">Harga Qty 1<span class="required">*</span></label>
              <div class="row">
                <div class="col-sm ml-2">
                  <input class="form-control" type="text" id="ket_1" name="ket_1" value="<?= $p->ket1 ?>" placeholder="Nama Qty 1" />
                </div>
                <div class="col-sm">
                  <input class="form-control" type="text" id="hr_1" name="hr_1" value="<?= $p->qty_1 ?>" placeholder="Rp." />
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-sm-3 control-label text-right" for="id_bar">Harga Qty 2<span class="required">*</span></label>
              <div class="row">
                <div class="col-sm ml-2">
                  <input class="form-control" type="text" id="ket_2" name="ket_2" value="<?= $p->ket2 ?>" placeholder="Nama Qty 2" />
                </div>
                <div class="col-sm">
                  <input class="form-control" type="text" id="hr_2" name="hr_2" value="<?= $p->qty_2 ?>" placeholder="Rp." />
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-sm-3 control-label text-right" for="id_bar">Harga Qty 3<span class="required">*</span></label>
              <div class="row">
                <div class="col-sm ml-2">
                  <input class="form-control" type="text" id="ket_3" name="ket_3" value="<?= $p->ket3 ?>" placeholder="Nama Qty 3" />
                </div>
                <div class="col-sm">
                  <input class="form-control" type="text" id="hr_3" name="hr_3" value="<?= $p->qty_3 ?>" placeholder="Rp." />
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-sm-3 control-label text-right" for="id_bar">Harga Qty 4<span class="required">*</span></label>
              <div class="row">
                <div class="col-sm ml-2">
                  <input class="form-control" type="text" id="ket_4" name="ket_4" value="<?= $p->ket4 ?>" placeholder="Nama Qty 4" />
                </div>
                <div class="col-sm">
                  <input class="form-control" type="text" id="hr_4" name="hr_4" value="<?= $p->qty_4 ?>" placeholder="Rp." />
                </div>
              </div>
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

<!-- /.modal -->
<?php foreach ($price as $p) : ?>
  <div class="modal fade" id="hapusQty<?php echo $p->id_pricelist ?>">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-body">
          <h3>!!!Anda akan menghapus data !!! </h3>
          <br>
          <h3><?php echo $p->nama_barang ?></h3>
          <br>
          <h3>Data yang sudah terhapus tidak dapat di kembalikan lagi</h3>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
          <a class="btn btn-danger" href="<?php echo base_url("pricelist/hapusQty/$p->id_pricelist/$p->kode_barang") ?>">Hapus</a>
        </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
<?php endforeach; ?>
<!-- /.modal -->

<?php foreach ($price as $p) : ?>
  <div class="modal fade" id="convertToNet<?= $p->id_pricelist ?>">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Convert Data Qty To Harga NETT</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <?php echo form_open_multipart('pricelist/convertToNett'); ?>
          <div class="form-group">
            <div class="row">
              <label class="col-sm-3 control-label text-right" for="id_bar">Nama Barang Kemasan<span class="required">*</span></label>
              <div class="col-sm-8"><input class="form-control" type="text" id="nm_barang" name="nm_barang" value="<?= $p->nama_barang ?>" readonly />
                <input class="form-control" type="text" id="kdbarang" name="kdbarang" value="<?php echo $kodeID ?>" hidden />
                <input class="form-control" type="text" id="idbarang" name="idbarang" value="<?= $p->id_pricelist ?>" hidden />
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-sm-3 control-label text-right" for="id_bar">Harga Terendah/QTY/R1/Partai<span class="required">*</span></label>
              <div class="col-sm-8"><input class="form-control" type="text" id="harga_isi1" name="harga_isi1" value="<?= $p->qty_1 ?>" />
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-sm-3 control-label text-right" for="id_bar">Harga Ecer/Kios/R2<span class="required">*</span></label>
              <div class="col-sm-8"><input class="form-control" type="text" id="harga_isi2" name="harga_isi2" value="<?= $p->qty_2 ?>" />
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-sm-3 control-label text-right" for="id_bar">Harga Program <span class="required">*</span></label>
              <div class="col-sm-8"><input class="form-control" type="text" id="harga_isi3" name="harga_isi3" value="<?= $p->qty_3 ?>" />
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <label class="col-sm-3 control-label text-right" for="id_bar">Harga Online <span class="required">*</span></label>
              <div class="col-sm-8"><input class="form-control" type="text" id="harga_isi4" name="harga_isi4" value="<?= $p->qty_4 ?>" />
              </div>
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