<div class="card p-2" style="border-radius: 1%">
    <table class="table" id="dataTable" style="font-weight:bold" cellspacing="0">
        <thead>
            <tr style="background-color:#8db3f0">
                <th>List Kemasan</th>
                <th>Harga R1</th>
                <th>Harga R2</th>
                <th>Harga Program</th>
                <th>Harga Online</th>
                <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                    <th width="15%" class="text-center">Aksi</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($harga as $h) : ?>
                <tr>
                    <td><?= $h->nama_barang ?></td>
                    <td>Rp. <?= number_format($h->harga_r1) ?></td>
                    <td>Rp. <?= number_format($h->harga_r2) ?></td>
                    <td>Rp. <?= number_format($h->harga_program) ?></td>
                    <td>Rp. <?= number_format($h->harga_online) ?></td>
                    <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                        <td class="text-center">
                            <a href="#" class="btn btn-success btn-sm " data-toggle="modal" data-target="#editmodalSpecial<?= $h->id ?>">
                                <i class="fa fa-solid fa-pencil-alt"></i>
                            </a>
                            <a href="#" class="btn btn-danger btn-sm " data-toggle="modal" data-target="#hapus<?= $h->id ?>">
                                <i class="fa fa-solid fa-trash-alt"></i>
                            </a>
                            <a href="#" class="btn btn-warning btn-sm " data-toggle="modal" data-target="#convertDataQty<?= $h->id ?>">
                                <i class="fa fa-solid fa-sync-alt"></i>
                            </a>
                        </td>
                    <?php } ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>