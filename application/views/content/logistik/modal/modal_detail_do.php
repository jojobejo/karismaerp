<?php foreach ($kdo as $k) : ?>
    <div class="modal fade" id="edited_rute">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Edit Rute</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form action="<?= base_url('edited_rute_do') ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Regional DO</label>
                            <input type="text" name="kddo" id="kddo" class="form-control" value="<?= $k->kd_do ?>" readonly required hidden>
                            <input type="text" name="id" id="id" class="form-control" value="<?= $k->id ?>" readonly required hidden>
                            <select name="regional" id="regional" class="form-control" required>
                                <option value="">Pilih Rute Pengiriman</option>
                                <?php foreach (['LK' => 'Luar Kota', 'KK' => 'Karisidenan'] as $jenis => $label) : ?>
                                    <optgroup label="<?= $jenis ?> - <?= $label ?>">
                                        <?php foreach (($rute_options ?? []) as $rute) : ?>
                                            <?php if ($rute->jenis_rute !== $jenis) continue; ?>
                                            <option value="<?= htmlspecialchars($rute->kd_rute, ENT_QUOTES, 'UTF-8') ?>"
                                                <?= (string)$k->regional === (string)$rute->kd_rute ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($rute->kd_rute, ENT_QUOTES, 'UTF-8') ?>
                                                - <?= htmlspecialchars($rute->keterangan, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Pilihan rute diambil dari master rute LK/KK.</small>
                        </div>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
<?php endforeach; ?>
