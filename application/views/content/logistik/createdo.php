<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <a href="<?= base_url('logistik') ?>" class="btn btn-primary mb-2"><i class="fas fa-home"></i></a>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Kode Do" value="<?= $generate_do ?>" name="do_isi" id="do_isi" readonly>
                                    </div>
                                </div>

                                <div class="col-md" hidden>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Tanggal Kirim" value="-" name="tgl_isi" id="tgl_isi">
                                    </div>
                                </div>

                                <div class="col-md" hidden>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Plat Nomor" value="-" name="plat_isi" id="plat_isi">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Rute" value="" name="regional_isi" id="regional_isi">
                                    </div>
                                </div>
                                <div class="col-md" hidden>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-truck"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Driver" value="-" name="driver_isi" id="driver_isi">
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <?php if (!empty($qcount_tonase_kubikasi)) : ?>
                                    <?php foreach ($qcount_tonase_kubikasi as $q) : ?>
                                        <?php
                                        $tonase_ton = $q->total_tonase_kg / 1000000;
                                        $kubikasi_m3 = round($q->total_kubikasi_m3, 3);
                                        ?>
                                        <h3>Tonase: <?= number_format($tonase_ton, 3) ?> ton</h3>
                                        <h3>Kubikasi: <?= $kubikasi_m3 ?> m³</h3>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <h3>Tonase: 0 ton</h3>
                                    <h3>Kubikasi: 0 m³</h3>
                                <?php endif; ?>
                            </div>

                            <table id="detbarang" class="table table-striped">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td>Faktur</td>
                                        <td>Nama</td>
                                        <td>Alamat</td>
                                        <td>Rute</td>
                                        <td>Kota</td>
                                        <td>No.Telpon</td>
                                        <td>Jam Buka/Tutup</td>
                                        <td>Karakteristik</td>
                                        <td style="text-align: center;">#</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tmp_faktur as $tmp) :

                                        $telp1      = $tmp->telp1;
                                        $telp2      = $tmp->telp2;
                                        $kiosc      = $tmp->toko;
                                        $jmkiosbt   = $tmp->jam_buka_tutup;

                                        if (empty($jmkiosbt)) {
                                            $jmkiosbt   = '-';
                                        } else {
                                            $jmkiosbt;
                                        }
                                        if (empty($kiosc)) {
                                            $kiosc   = '-';
                                        } else {
                                            $kiosc;
                                        }

                                        if ($telp2 == '0') {
                                            $telp   = $tmp->telp1;
                                        } else {
                                            $telp   = $tmp->telp1 . ' ' . '/' . ' ' . $tmp->telp2;
                                        } ?>

                                        <tr data-id="<?= $tmp->id ?>">
                                            <!-- <td><?= $tmp->norut_do ?></td> -->
                                            <td><?= $tmp->kd_faktur ?></td>
                                            <td><?= $tmp->nama_customer ?></td>
                                            <td><?= $tmp->alamat_kios ?></td>
                                            <td><?= $tmp->kdrute ?></td>
                                            <td><?= $tmp->regional ?></td>
                                            <td><?= $telp ?></td>
                                            <td style="text-align: center;"><?= $jmkiosbt ?></td>
                                            <td style="text-align: center;"><?= $kiosc ?></td>
                                            <td style="width: 15%;">
                                                <div class="row">
                                                    <div class="col p-0">
                                                        <a href="<?= base_url('detail_fk/') . $tmp->kd_faktur ?>" class="btn btn-primary btn-block"><i class="fas fa-eye"></i></a>
                                                    </div>
                                                    <div class="col p-">
                                                        <a href="<?= base_url('revert_do/') . $tmp->kd_faktur . '/' . 'formlist' ?>" class="btn btn-block btn-warning"><i class="fas fa-undo"></i></a>
                                                    </div>
                                                    <!-- <div class="col p-0">
                                                        <button class="btn btn-info btn-block btn-nurut" data-id="<?= $tmp->id ?>">
                                                            <i class=" fas fa-sort-amount-down-alt"></i>
                                                        </button>
                                                    </div> -->
                                                </div>
                                            </td>
                                        </tr>
                                        <tr id="editRow" style="display: none;">
                                            <td colspan="7">
                                                <form id="editForm">
                                                    <div class="row">
                                                        <input type="hidden" id="id" name="id" readonly>
                                                        <div class="col-md">
                                                            <input type="number" id="nourut" name="nourut" class="form-control">
                                                        </div>
                                                        <div class="col-md">
                                                            <button type="submit" class="btn btn-success">Simpan</button>
                                                            <button type="button" class="btn btn-danger" id="cancelEdit">Batal</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success btn-block mt-2" id="rekamdo">
                                <i class="fas fa-print"></i> Rekam Draft Order
                            </button>
                        </div>
                    </div>
                    <!-- DATA PREPARATION - SALES - LOGISTIK -->
                    <button class="btn btn-primary mb-2 btn-block" onclick="toggleDataPreDO()" id="btnhide"><i class="fas fa-eye"></i> Faktur Penjualan <i class="fas fa-eye"></i> </button>
                    <div class="card" id="pre_do" style="display: none;">
                        <div class="card-body">
                            <table id="dailyod" class="table table-bordered table-striped">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td>TANGGAL TRANSAKSI</td>
                                        <td>FAKTUR</td>
                                        <td>NAMA CUSTOMER</td>
                                        <td>KIOS</td>
                                        <td>ALAMAT KIOS</td>
                                        <td>RUTE</td>
                                        <td>REGIONAL</td>
                                        <td>ITEM</td>
                                        <td>Status</td>
                                        <td>#</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_faktur as $l) :
                                        $status = $l->data_sts;
                                    ?>
                                        <tr>
                                            <td><?= $l->tgl_inputer ?></td>
                                            <td><?= $l->kd_faktur ?></td>
                                            <td><?= $l->nama_customer ?></td>
                                            <td><?= $l->nama_kios ?></td>
                                            <td><?= $l->alamat_kios ?></td>
                                            <td><?= $l->kd_rute ?></td>
                                            <td><?= $l->regional ?></td>
                                            <td><?= $l->total_barang ?></td>
                                            <?php if ($status == 1) : ?>
                                                <td><span class="badge badge-secondary">NOT IN DRAFT</span></td>
                                                <td>
                                                    <div class="row">
                                                        <a href="<?= base_url('detail_fk/') . $l->kd_faktur ?>" class="btn btn-info btn-block btn-sm"><i class="fas fa-eye"></i></a>
                                                        <a href="<?= base_url('insert_tmp/') . $l->kd_faktur . '/' . 'formlist' ?>" class="btn btn-success btn-block btn-sm"><i class="fas fa-plus"></i></a>
                                                    </div>
                                                </td>
                                            <?php elseif ($status == 2) : ?>
                                                <td><span class="badge badge-success">ON DRAFT</span></td>
                                                <td>
                                                    <div class="row">
                                                        <a href="<?= base_url('detail_fk/') . $l->kd_faktur ?>" class="btn btn-info btn-block btn-sm"><i class="fas fa-eye"></i></a>
                                                    </div>
                                                </td>
                                            <?php endif; ?>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <script>
        function toggleDataPreDO() {
            var tableDiv = document.getElementById("pre_do");

            if (tableDiv.style.display === "none") {
                tableDiv.style.display = "block";
            } else {
                tableDiv.style.display = "none";
            }
        }

        $(document).ready(function() {
            function loadTmpDo() {
                $.ajax({
                    url: 'get_tmp_do',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let rows = '';
                        if (response.length > 0) {
                            $.each(response, function(index, data) {
                                rows += `<tr>
                            <td>${data.kd_faktur}</td>
                            <td>${data.nama_customer}</td>
                            <td>${data.alamat_kios}</td>
                            <td>${data.regional}</td>
                            <td>${data.telp1}</td>
                            <td>${data.telp2}</td>
                        </tr>`;
                            });
                        } else {
                            rows = '<tr><td colspan="6" class="text-center">Data tidak tersedia</td></tr>';
                        }
                        $('#tmp_do_data').html(rows);
                    },
                    error: function() {
                        alert('Gagal mengambil data');
                    }
                });
            }

            loadTmpDo();

            $('#doForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: 'save_do',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            $('#doForm')[0].reset();
                            loadTmpDo();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat menyimpan data');
                    }
                });
            });

            $("#cancelEdit").on("click", function() {
                $("#editRow").hide();
            });

            $(".btn-nurut").on("click", function(e) {
                e.preventDefault();

                var row = $(this).closest("tr");
                var id = row.data("id");

                $.ajax({
                    url: "<?= base_url('get_tmpdonorut') ?>",
                    type: "POST",
                    data: {
                        id: id,
                    },
                    dataType: "json",
                    success: function(data) {
                        $("#id").val(data.id);
                        $("#nourut").val(data.norut_do);

                        $("#editRow").insertAfter(row).show();
                    },
                });
            });

            $("#editForm").on("submit", function(e) {
                e.preventDefault();

                $.ajax({
                    url: "<?= base_url('update_norut') ?>",
                    type: "POST",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            alert("Nomor urut pengiriman telah berhasil diperbarui!");
                            location.reload();
                        } else {
                            alert("Terjadi kesalahan, silakan coba lagi.");
                        }
                    },
                    error: function() {
                        alert("Gagal memperbarui data.");
                    }
                });
            });

            $("#rekamdo").on('click', function() {
                var kd_do = $("#do_isi").val().trim();
                var tgl_krim = $("#tgl_isi").val();
                var platno = $("#plat_isi").val();
                var kota = $("#regional_isi").val();
                var driver = $("#driver_isi").val();

                $("input").css("border", "");

                if (!kd_do && !tgl_krim && !platno && !kota && !driver) {
                    alert('Semua field masih kosong.');
                    $("input").css("border", "2px solid red");
                    return;
                }

                var isValid = true;

                if (!kd_do) {
                    alert('Kode DO harus diisi.');
                    $("#do_isi").css("border", "2px solid red");
                    isValid = false;
                }
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
                if (!kota) {
                    alert('Rute harus diisi');
                    $("#regional_isi").css("border", "2px solid red");
                    isValid = false;
                }
                if (!driver) {
                    alert('Nama Driver harus diisi.');
                    $("#driver_isi").css("border", "2px solid red");
                    isValid = false;
                }

                if (!isValid) return;

                var isNorutValid = true;
                $("#detbarang tbody tr").each(function() {
                    var norut = $(this).find("td:first").text().trim();
                    if (norut === "" || norut === "0") {
                        isNorutValid = false;
                    }
                });

                if (!isNorutValid) {
                    alert("Nomor urut (No) belum terisi semua.");
                    return;
                }

                $.ajax({
                    url: "<?= base_url('rekam_do') ?>",
                    type: "POST",
                    data: {
                        kd_do: kd_do,
                        tgl_krim: tgl_krim,
                        platno: platno,
                        kota: kota,
                        driver: driver
                    },
                    dataType: "JSON",
                    cache: false,
                    success: function(data) {
                        console.log(data);
                        if (data.msg == "success") {
                            alert('Data berhasil direkam');
                            window.location.href = "<?= base_url('create_do') ?>";
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
    </script>