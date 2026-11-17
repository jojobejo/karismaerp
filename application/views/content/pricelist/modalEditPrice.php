<?php foreach ($harga as $d) : ?>
  <div class="modal fade" id="edit<?= $d->id ?>" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title mx-auto" id="editModalLabel"></h5>
        </div>
        <div class="modal-body">
          <div>
            <form action="<?php echo base_url("pricelist/updatePrice") ?>" method="post">
              <div class="form-group">
                <div class="row">
                  <label class="col-sm-3 mt-2 control-label text-right" name="kdbarangisi" id="kdbarangisi">ID BARANG</label><strong class="mt-1">:</strong>
                  <div class="col-sm-8"><input class="form-control" type="text" name="id_price_isi" id="id_price_isi" value="<?php echo $d->id ?>"></div>
                </div>
              </div>
              <div class="form-group">
                <div class="row">
                  <label class="col-sm-3 mt-2 control-label text-right" name="kdbarang_isi" id="kdbarang_isi">Kode Barang</label><strong class="mt-1">:</strong>
                  <div class="col-sm-8"><input class="form-control" type="text" name="kdbarang_isi" id="kdbarang_isi" value="<?php echo $d->kode_barang ?>" readonly></div>
                </div>
              </div>
              <div class="form-group">
                <div class="row">
                  <label class="col-sm-3 mt-2 control-label text-right" name="qtyisi" id="qtyisi">Qty </label><strong class="mt-1">:</strong>
                  <div class="col-sm-8"><input class="form-control" name="qtyisi" id="qtyisi" oninput="check();" type="text" value="<?= $d->ket1 ?>" placeholder="contoh : 1-3 BOX"></div>
                  <p id="pesan_edit"></p>
                </div>
              </div>
              <div class="form-group">
                <div class="row">
                  <label class="col-sm-3 mt-2 control-label text-right" name="hrgisi" id="hrgisi">Harga</label><strong class="mt-1">:</strong>
                  <div class="col-sm-8"><input class="form-control" name="hrgisi" id="hrgisi" type="Number" value="<?= $d->qty_1 ?>"></div>
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

<script>
  function check() {
    var qty = $("#qtyisi").val();


    $.ajax({
      url: "<?php echo base_url("owner/pricelist/checkdata") ?>",
      type: "POST",
      dataType: "JSON",
      data: {
        qty: qty,
        kode_barang: kode_barang,
      },
      cache: false,
      success: function(msg) {
        if (msg.message == "True") {
          document.getElementById("pesan_edit").innerHTML = "Kuantitas telah ada";
          document.getElementById("pesan_edit").style.color = "#ff6666";
          document.getElementById("submit_edit").disabled = "true"
        } else {
          document.getElementById("pesan_edit").innerHTML = "Kuantitas bisa ditambahkan";
          document.getElementById("pesan_edit").style.color = "#66cc66";
          document.getElementById("submit_edit").disabled = ""

        }
      }
    });

  };
</script>