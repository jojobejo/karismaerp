<style>
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
        background-color: #343a40;
        color: #fff;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <?php foreach ($dostatus as $d) : ?>

            <div class="content-wrapper">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row">
                            <?php if ($this->session->userdata('jobdesk') == 'ADMINKEUTC') : ?>
                                <a href="<?= base_url('keuangan') ?>" class="btn btn-primary mb-2 ml-2"><i class="fas fa-arrow-circle-left"></i></a>
                            <?php elseif ($this->session->userdata('jobdesk') == 'LOGISTIK') : ?>
                                <a href="<?= base_url('logistik') ?>" class="btn btn-primary mb-2 ml-2"><i class="fas fa-arrow-circle-left"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <section class="content">
                    <div class="container-fluid">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title">Rencana Pengiriman Barang</h3>
                            </div>
                            <div class="card-body">
                                <?php if ($this->session->userdata('jobdesk') == 'LOGISTIK') : ?>
                                    <div class="row mb-4">
                                        <div class="col-auto">
                                            <h2>Detail Orders</h2>
                                        </div>
                                        <div class="col-auto">
                                            <?php if ($d->status == '1') : ?>
                                                <div class="row">
                                                    <div class="col-auto">
                                                        <a href="#" class="btn btn-warning">DRAFT</a>
                                                    </div>
                                                    <div class="col-auto">
                                                        <?php foreach ($kdo as $k) : ?>
                                                            <a href="<?= base_url('list_faktur/') . $k->kd_do . "/" . $k->regional ?>" class="btn btn-info"><i class="fas fa-plus"></i> Tambah Faktur</a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php elseif ($d->status == '2') : ?>
                                                <a href="#" class="btn btn-info">DONE</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php elseif ($this->session->userdata('jobdesk') == 'ADMINKEUTC') : ?>
                                <?php endif; ?>

                                <?php foreach ($kdo as $k) : ?>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Kode Faktur</div>
                                        <div>: <?= $k->kd_do ?></div>
                                    </div>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Regional Pengiriman</div>
                                        <div>: <?= $k->regional ?></div>
                                    </div>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Customer</div>
                                        <div>: <?= $k->totalfaktur ?></div>
                                    </div>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Barang</div>
                                        <div>: <?= $k->total_barang ?></div>
                                    </div>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Tonase</div>
                                        <div>: <?= $k->total_tonase_faktur . ' (TON)' ?></div>
                                    </div>
                                    <div class="mb-2 d-flex">
                                        <div class="me-3 fw-semibold" style="width: 180px;">Total Kubikasi</div>
                                        <div>: <?= $k->total_kubikasi . ' (m³)' ?></div>
                                    </div>

                                    <!-- FORM START -->
                                    <?php if ($this->session->userdata('jobdesk') == 'LOGISTIK') : ?>
                                        <?php if ($d->status == '1') : ?>
                                            <div class="row mb-2">
                                                <div class="col-md" hidden>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control" value="<?= $k->kd_do ?>" name="do_isi" id="do_isi" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-md">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                        </div>
                                                        <input type="date" class="form-control" placeholder="Tanggal Kirim" value="" name="tgl_isi" id="tgl_isi">
                                                    </div>
                                                </div>

                                                <div class="col-md">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-truck"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control" placeholder="Driver" value="" name="driver_isi" id="driver_isi">
                                                    </div>
                                                </div>

                                                <div class="col-md">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control" placeholder="Plat Nomor" value="" name="plat_isi" id="plat_isi">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else : ?>
                                        <?php endif; ?>
                                    <?php elseif ($this->session->userdata('jobdesk') == 'ADMINKEUTC') : ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <!-- END FORM -->

                                <?php if ($this->session->userdata('jobdesk') == 'LOGISTIK') : ?>
                                    <table class="table table-bordered" id="tb_checker_do">
                                        <thead>
                                            <tr>
                                                <?php if ($d->status == '1') : ?>
                                                    <th rowspan="2">#</th>
                                                <?php elseif ($d->status == '2') : ?>
                                                <?php endif; ?>
                                                <th colspan="2">Data Kios</th>
                                                <th rowspan="2">Rute</th>
                                                <th colspan="2">TTB</th>
                                                <th rowspan="2">No</th>
                                                <th rowspan="2">Nama Barang</th>
                                                <th rowspan="2">No Lot</th>
                                                <th colspan="2">Qty</th>

                                            </tr>
                                            <tr>
                                                <th>Nama Kios</th>
                                                <th>Regional</th>
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
                                                    $norut_counter = 1;
                                                }
                                            ?>
                                                <tr>
                                                    <?php if ($show_faktur_info) : ?>
                                                        <?php if ($d->status == '1') : ?>
                                                            <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>">
                                                                <a href="<?= base_url('cancel_fk/' . $row->kd_faktur . '/' . $row->kd_do) ?>" class="btn btn-sm btn-block btn-danger"><i class="fas fa-times-circle"></i></a>
                                                            </td>
                                                        <?php elseif ($d->status == '2') : ?>
                                                        <?php endif; ?>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->nama_kios ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->regional ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_rute ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_faktur ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->tgl_transaksi ?></td>
                                                    <?php endif; ?>
                                                    <td><?= $norut_counter++ ?></td>
                                                    <td><?= $row->nm_barang ?></td>
                                                    <td><?= $row->no_lot ?> - <?= $row->tgl_exp ?></td>
                                                    <td><?= $row->qty_box ?></td>
                                                    <td><?= $row->qty_pcs ?></td>
                                                    <!-- <?php if ($d->status == '1') : ?>
                                                    <?php if ($row->status == '2') : ?>
                                                        <td><a href="#" class="btn btn-sm btn-block btn-success"></a></td>
                                                    <?php elseif ($row->status == '3') : ?>
                                                        <td><a href="#" class="btn btn-sm btn-block btn-danger"></a></td>
                                                    <?php elseif ($row->status == '1') : ?>
                                                        <td><a href="#" class="btn btn-sm btn-block btn-warning"></a></td>
                                                    <?php endif; ?>
                                                <?php elseif ($d->status == '2') : ?>
                                                <?php endif; ?>

                                                <?php if ($d->status == '1') : ?>
                                                    <?php if ($row->status == '2') : ?>
                                                        <td>
                                                            <a href="#" class="btn btn-info"><i class="fas fa-thumbs-up"></i></a>
                                                            <a href="<?= base_url('acc_check/' . $row->id . '/' . "3" . '/' . $row->kd_do) ?>" class="btn btn-warning"><i class="fas fa-undo"></i></a>
                                                        </td>
                                                    <?php elseif ($row->status == "3") : ?>
                                                        <td>
                                                            <a href="#" class="btn btn-danger"><i class="fas fa-times-circle"></i></a>
                                                            <a href="<?= base_url('acc_check/' . $row->id . '/' . "3" . '/' . $row->kd_do) ?>" class="btn btn-warning"><i class="fas fa-undo"></i></a>
                                                        </td>
                                                    <?php elseif ($row->status == "1") : ?>
                                                        <td>
                                                            <a href="<?= base_url('acc_check/' . $row->id . '/' . "1" . '/' . $row->kd_do) ?>" class="btn btn-success"><i class="fas fa-check-circle"></i></a>
                                                            <a href="<?= base_url('acc_check/' . $row->id . '/' . "2" . '/' . $row->kd_do) ?>" class="btn btn-danger"><i class="fas fa-times-circle"></i></a>
                                                        </td>
                                                    <?php endif; ?>
                                                <?php elseif ($d->status == '2') : ?>
                                                <?php endif; ?> -->
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php foreach ($kdo as $k) : ?>
                                        <div class="row">
                                            <div class="col">
                                                <button type="button" class="btn btn-success w-100 mt-3" id="draftpost">
                                                    <i class="fas fa-check-double"></i> Rekam Draft Order
                                                </button>
                                            </div>
                                            <div class="col">
                                                <button type="button" class="btn btn-info btn-block mt-3" id="btnPrintOrder" data-kd="<?= $k->kd_do ?>">
                                                    <i class="fas fa-print"></i> Print Order
                                                </button>
                                            </div>
                                            <div class="col">
                                                <button type="button" class="btn btn-primary btn-block mt-3" id="btnPrintRegis" data-kd="<?= $k->kd_do ?>">
                                                    <i class="fas fa-print"></i> Print Register
                                                </button>
                                            </div>
                                            <div class="col">
                                                <button type="button" class="btn btn-warning btn-block mt-3" id="btnPrintChecker" data-kd="<?= $k->kd_do ?>">
                                                    <i class="fas fa-print"></i> Print Checker
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <!-- LOGISTIK END -->

                                    <!-- TC START -->
                                <?php elseif ($this->session->userdata('jobdesk') == 'ADMINKEUTC') : ?>
                                    <table class="table table-bordered" id="tb_checker_do">
                                        <thead>
                                            <tr>
                                                <th colspan="2">Data Kios</th>
                                                <th rowspan="2">Rute</th>
                                                <th colspan="3">TTB</th>
                                                <th rowspan="2">Cash / Tempo</th>
                                            </tr>
                                            <tr>
                                                <th>Nama Kios</th>
                                                <th>Regional</th>
                                                <th>Kode Faktur</th>
                                                <th>Tgl Input</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $prev_norut = null;
                                            $rowspan_count = [];
                                            $norut_counter = 1;

                                            foreach ($datatc as $row) {
                                                if (!isset($rowspan_count[$row->kd_faktur])) {
                                                    $rowspan_count[$row->kd_faktur] = 0;
                                                }
                                                $rowspan_count[$row->kd_faktur]++;
                                            }

                                            $printed_faktur = [];
                                            foreach ($datatc as $row) :
                                                $show_faktur_info = !in_array($row->kd_faktur, $printed_faktur);
                                                if ($show_faktur_info) {
                                                    $printed_faktur[] = $row->kd_faktur;
                                                }
                                                if ($row->jtempo == '0') {
                                                    $tempo = '<span class="badge badge-primary">Cash</span>';
                                                } else if ($row->jtempo == '30') {
                                                    $tempo = '<span class="badge badge-warning">' . htmlspecialchars($row->jtempo) . '</span>';
                                                } else {
                                                    $tempo = '<span class="badge badge-success">' . htmlspecialchars($row->jtempo) . '</span>';
                                                }
                                            ?>
                                                <tr>
                                                    <?php if ($show_faktur_info) : ?>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->nama_kios ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->regional ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_rute ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_faktur ?></td>
                                                        <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->tgl_transaksi ?></td>
                                                    <?php endif; ?>
                                                    <td><?= format_rupiah($row->nominal_p) ?></td>
                                                    <td><?= $tempo ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </section>
            </div>
        <?php endforeach; ?>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>
        <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>

    <script>
        $(document).ready(function() {
            $("#draftpost").on('click', function() {
                var kd_do = $("#do_isi").val().trim();
                var tgl_krim = $("#tgl_isi").val();
                var platno = $("#plat_isi").val();
                var driver = $("#driver_isi").val();

                $("input").css("border", "");

                if (!tgl_krim && !platno && !driver) {
                    alert('Semua field masih kosong.');
                    $("input").css("border", "2px solid red");
                    return;
                }

                var isValid = true;
                if (!tgl_krim) {
                    alert('Tanggal Kirim harus diisi.');
                    $("#tgl_isi").css("border", "2px solid red");
                    isValid = false;
                }
                if (!platno) {
                    alert('Plat Nomor harus diisi.');
                    $("#plat_isi").css("border", "2px solid red");
                    isValid = false;
                }
                if (!driver) {
                    alert('Nama Driver harus diisi.');
                    $("#driver_isi").css("border", "2px solid red");
                    isValid = false;
                }

                if (!isValid) return;

                $.ajax({
                    url: "<?= base_url('rekam_order_check') ?>",
                    type: "POST",
                    data: {
                        kd_do: kd_do,
                        tgl_krim: tgl_krim,
                        platno: platno,
                        driver: driver
                    },
                    dataType: "JSON",
                    success: function(data) {
                        console.log(data);
                        if (data.msg === "success") {
                            alert('Data berhasil direkam');
                            window.location.href = "<?= base_url('detail_do/') ?>" + kd_do;
                        } else {
                            alert(data.message || 'Ada kesalahan data');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Terjadi kesalahan: ' + error);
                    }
                });
            });
        });

        $("#btnPrintOrder").on('click', function() {
            var kd_do = $(this).data('kd');
            var tgl_krim = $("#tgl_isi").val().trim();
            var platno = $("#plat_isi").val().trim();
            var driver = $("#driver_isi").val().trim();

            if (!tgl_krim || !platno || !driver) {
                alert("Lengkapi semua field terlebih dahulu sebelum print.");
                if (!tgl_krim) $("#tgl_isi").css("border", "2px solid red");
                if (!platno) $("#plat_isi").css("border", "2px solid red");
                if (!driver) $("#driver_isi").css("border", "2px solid red");
                return;
            }
            var printUrl = "<?= base_url('print_do/') ?>" + kd_do +
                "?tgl_kirim=" + encodeURIComponent(tgl_krim) +
                "&driver=" + encodeURIComponent(driver) +
                "&plat=" + encodeURIComponent(platno);

            window.open(printUrl, "_blank");
        });

        $("#btnPrintRegis").on('click', function() {
            var kd_do = $(this).data('kd');
            var tgl_krim = $("#tgl_isi").val().trim();
            var platno = $("#plat_isi").val().trim();
            var driver = $("#driver_isi").val().trim();

            if (!tgl_krim || !platno || !driver) {
                alert("Lengkapi semua field terlebih dahulu sebelum print.");
                if (!tgl_krim) $("#tgl_isi").css("border", "2px solid red");
                if (!platno) $("#plat_isi").css("border", "2px solid red");
                if (!driver) $("#driver_isi").css("border", "2px solid red");
                return;
            }
            var printUrl = "<?= base_url('print_regis/') ?>" + kd_do +
                "?tgl_kirim=" + encodeURIComponent(tgl_krim) +
                "&driver=" + encodeURIComponent(driver) +
                "&plat=" + encodeURIComponent(platno);
            window.open(printUrl, "_blank");
        });

        $("#btnPrintChecker").on('click', function() {
            var kd_do = $(this).data('kd');
            var tgl_krim = $("#tgl_isi").val().trim();
            var platno = $("#plat_isi").val().trim();
            var driver = $("#driver_isi").val().trim();

            if (!tgl_krim || !platno || !driver) {
                alert("Lengkapi semua field terlebih dahulu sebelum print.");
                if (!tgl_krim) $("#tgl_isi").css("border", "2px solid red");
                if (!platno) $("#plat_isi").css("border", "2px solid red");
                if (!driver) $("#driver_isi").css("border", "2px solid red");
                return;
            }
            var printUrl = "<?= base_url('print_checker/') ?>" + kd_do +
                "?tgl_kirim=" + encodeURIComponent(tgl_krim) +
                "&driver=" + encodeURIComponent(driver) +
                "&plat=" + encodeURIComponent(platno);
            window.open(printUrl, "_blank");
        });
    </script>