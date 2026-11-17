<div class="card p-2 m-5" style="border-radius: 1%">
    <table class="table" id="dataTable" style="font-weight:bold" cellspacing="0">
        <thead>
            <tr style="background-color:#8db3f0">
                <th>List Kemasan</th>

                <?php if ($this->session->userdata('hak_akses') == '1' || $this->session->userdata('hak_akses') == '3') { ?>
                    <th width="15%" class="text-center">Aksi</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($harga as $h) : ?>
                <tr>
                    <td><?= $h->nama_barang ?></td>
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