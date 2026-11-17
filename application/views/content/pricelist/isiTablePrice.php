<div class="card p-2" style="border-radius: 1%">
  <table class="table" id="dataTable" style="font-weight:bold" cellspacing="0">
    <thead>
      <tr style="background-color:#8db3f0">
        <th class="text-center">List Kemasan</th>
        <th colspan="4" style="text-align: center;">Harga Qty</th>
        <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
          <th width="15%" class="text-center">Aksi</th>
        <?php } ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($price as $r) : ?>
        <tr>
          <td><?= $r->nama_barang ?></td>
          <?php
          $ket1 = $r->ket1;
          $hrg1 = $r->qty_1;
          $hrga1 = $r->qty_1a;

          if ($ket1 == '' && $hrg1 == '0') {
            echo '<td></td>';
          } else if ($hrg1 > 0 && $hrga1 > 0) {
            echo '<td class="text-center">
              <div style="background-color: #8db3f0;">' . $ket1 . '
              </div>
              Rp.' . number_format($hrg1) . ' - ' . 'Rp.' . number_format($hrga1) . '
            </td>';
          }  else {
            echo '<td class="text-center">
              <div style="background-color: #8db3f0;">' . $ket1 . '
              </div>
              Rp.' . number_format($hrg1) . '
            </td>';
          }

          ?>
          <?php
          $ket2 = $r->ket2;
          $hrg2 = $r->qty_2;
          $hrga2 = $r->qty_2a;

          if ($ket2 == '' && $hrg2 == '0') {
            echo '<td></td>';
          } else if ($hrg2 > 0 && $hrga2 > 0) {
            echo '<td class="text-center">
              <div style="background-color: #8db3f0;">' . $ket2 . '
              </div>
              Rp.' . number_format($hrg2) . '-' . 'Rp.' . number_format($hrga2) . '
            </td>';
          } else {
            echo '<td class="text-center">
              <div style="background-color: #8db3f0;">' . $ket2 . '
              </div>
              Rp.' . number_format($hrg2) . '
            </td>';
          }

          ?>

          <?php
          $ket3 = $r->ket3;
          $hrg3 = $r->qty_3;
          $hrga3 = $r->qty_3a;

          if ($ket3 == '' && $hrg3 == '0') {
            echo '<td></td>';
          } else if ($hrg3 > 0 && $hrga3 > 0) {
            echo '<td class="text-center">
              <div style="background-color: #8db3f0;">' . $ket3 . '
              </div>
              Rp.' . number_format($hrg3) . ' - ' . 'Rp.' . number_format($hrga3) . '
            </td>';
          } else {
            echo '<td class="text-center">
              <div style="background-color: #8db3f0;">' . $ket3 . '
              </div>
              Rp.' . number_format($hrg3) . '
            </td>';
          }

          ?>

          <?php
          $ket = $r->ket4;
          $hrg = $r->qty_4;
          $hrga4 = $r->qty_4a;

          if ($ket == '' && $hrg == '0') {
            echo '<td></td>';
          } else if ($hrg > 0 && $hrga4 > 0) {
            echo '<td class="text-center">
              <div style="background-color: #8db3f0;">' . $ket . '
              </div>
              Rp.' . number_format($hrg) . '-' . 'Rp.' . number_format($hrga4) . '
            </td>';
          } else {
            echo '<td class="text-center">
              <div style="background-color: #8db3f0;">' . $ket3 . '
              </div>
              Rp.' . number_format($hrg) . '
            </td>';
          }

          ?>

          <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
            <td class="text-center">
              <a href="#" class="btn btn-success btn-sm " data-toggle="modal" data-target="#edit<?= $r->id_pricelist ?>">
                <i class="fa fa-solid fa-pencil-alt"></i>
              </a>
              <a href="#" class="btn btn-danger btn-sm " data-toggle="modal" data-target="#hapusQty<?= $r->id_pricelist ?>">
                <i class="fa fa-solid fa-trash"></i>
              </a>
              <a href="#" class="btn btn-warning btn-sm " data-toggle="modal" data-target="#convertToNet<?= $r->id_pricelist ?>">
                <i class="fa fa-solid fa-sync-alt"></i>
              </a>
            </td>
          <?php } ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>