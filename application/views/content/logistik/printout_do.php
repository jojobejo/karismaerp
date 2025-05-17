<style>
    @media print {
        .table thead th {
            background-color: rgb(0, 0, 0) !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }

    table {
        font-size: 14px;
        white-space: nowrap;
    }

    th,
    td {
        vertical-align: middle;
        text-align: center;
    }

    .table thead th {
        background-color: rgb(0, 0, 0);
        color: #fff !important;
    }

    .header-title {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
        text-transform: uppercase;
    }

    .info-faktur {
        margin-bottom: 20px;
        font-size: 16px;
    }

    .info-faktur div {
        margin-bottom: 5px;
    }

    .wrap-text {
        white-space: normal !important;
        text-align: left;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper m-5">
        <?php foreach ($dostatus as $d) : ?>
            <div class="header-title">FAKTUR DELIVERY ORDER</div>
            <div class="info-faktur">
                <?php foreach ($doprintsts as $print) :
                    $tonase = ($print->total_tonase_faktur / 1000);
                ?>
                    <div>Nama Driver : <?= $print->driver ?></div>
                    <div>No Polisi : <?= $print->nolambung ?></div>
                    <div>Regional : <?= format_indo($print->tgl_pengiriman) ?></div>
                    <div>Total Customer :<?= $print->totalfaktur ?> </div>
                    <div>Total Barang : <?= $print->total_barang ?></div>
                    <div>Tonase : <?= $print->total_tonase_faktur . ' (Kg) ' . '||' . ' ' . $tonase . ' (Ton)' ?></div>
                <?php endforeach; ?>
            </div>

            <table class="table table-bordered" id="tb_checker_do">
                <thead>
                    <tr>
                        <th colspan="3">Data Kios</th>
                        <th rowspan="2">Rute</th>
                        <th colspan="2">TTB</th>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Nama Barang</th>
                        <th rowspan="2">No Lot</th>
                        <th colspan="2">Qty</th>
                        <?php if ($d->status == '1') : ?>
                            <th rowspan="2">Status</th>
                            <th rowspan="2">#</th>
                        <?php elseif ($d->status == '2') : ?>
                        <?php endif; ?>
                    </tr>
                    <tr>
                        <th>Nama Kios</th>
                        <th>Jam Buka - Tutup</th>
                        <th class="wrap-text">Karakteristik</th>
                        <th>Kode Faktur</th>
                        <th>Tgl Input</th>
                        <th>Besar</th>
                        <th>Kecil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $prev_norut = null;
                    $rowspan_count = [];
                    $norut_counter = 1;

                    foreach ($data_list as $row) {
                        if (!isset($rowspan_count[$row->kd_faktur])) {
                            $rowspan_count[$row->kd_faktur] = 0;
                        }
                        $rowspan_count[$row->kd_faktur]++;
                    }

                    $printed_faktur = [];
                    foreach ($data_list as $row) :
                        $show_faktur_info = !in_array($row->kd_faktur, $printed_faktur);
                        if ($show_faktur_info) {
                            $printed_faktur[] = $row->kd_faktur;
                            $norut_counter = 0;
                        }

                        if ($row->karakteristik_kios == '') {
                            $karakteristik_kios = '-';
                        } else {
                            $karakteristik_kios = $row->karakteristik_kios;
                        }

                        if ($row->jam_buka_tutup == '') {
                            $jambukatutup = '-';
                        } else {
                            $jambukatutup = $row->jam_buka_tutup;
                        }
                    ?>
                        <tr>
                            <?php if ($show_faktur_info) :
                            ?>
                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->nama_kios ?></td>
                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $jambukatutup ?></td>
                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>" class="wrap-text"><?= $karakteristik_kios ?></td>
                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_rute ?></td>
                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_faktur ?></td>
                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->tgl_inputer ?></td>
                            <?php endif; ?>
                            <td><?= $norut_counter++ ?></td>
                            <td><?= $row->nm_barang ?></td>
                            <td><?= $row->no_lot ?> - <?= $row->tgl_exp ?></td>
                            <td><?= $row->qty_box ?></td>
                            <td><?= $row->qty_pcs ?></td>
                            <?php if ($d->status == '1') : ?>
                                <?php if ($row->status == '2') : ?>
                                    <td><a href="#" class="btn btn-sm btn-block btn-success"></a></td>
                                <?php elseif ($row->status == '3') : ?>
                                    <td><a href="#" class="btn btn-sm btn-block btn-danger"></a></td>
                                <?php elseif ($row->status == '1') : ?>
                                    <td><a href="#" class="btn btn-sm btn-block btn-warning"></a></td>
                                <?php endif; ?>
                            <?php elseif ($d->status == '2') : ?>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>
    <!-- ./wrapper -->