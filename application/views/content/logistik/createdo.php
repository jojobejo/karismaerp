<!-- views/content/logistik/createdo.php -->
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
                                            <span class="input-group-text"><i class="fas fa-route"></i></span>
                                        </div>
                                        <select class="form-control" name="regional_isi" id="regional_isi">
                                            <option value="">Pilih Rute Pengiriman</option>
                                            <?php foreach (['LK' => 'Luar Kota', 'KK' => 'Karisidenan'] as $jenis => $label) : ?>
                                                <optgroup label="<?= $jenis ?> - <?= $label ?>">
                                                    <?php foreach (($rute_options ?? []) as $rute) : ?>
                                                        <?php if ($rute->jenis_rute !== $jenis) continue; ?>
                                                        <option value="<?= htmlspecialchars($rute->kd_rute, ENT_QUOTES, 'UTF-8') ?>">
                                                            <?= htmlspecialchars($rute->kd_rute, ENT_QUOTES, 'UTF-8') ?>
                                                            - <?= htmlspecialchars($rute->keterangan, ENT_QUOTES, 'UTF-8') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <small class="text-muted">Rute LK/KK diambil dari master rute.</small>
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

                                        <h3>Tonase: <?= number_format($tonase_ton, 6) ?> ton</h3>
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
                                                        <a href="<?= base_url('detail_fk?kd_faktur=' . rawurlencode($tmp->kd_faktur)) ?>" class="btn btn-primary btn-block"><i class="fas fa-eye"></i></a>
                                                    </div>
                                                    <div class="col p-">
                                                        <a href="<?= base_url('revert_do?kd_faktur=' . rawurlencode($tmp->kd_faktur) . '&action=formlist') ?>" class="btn btn-block btn-warning"><i class="fas fa-undo"></i></a>
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
                            <div class="row mb-2" id="bulk_action_bar" style="display:none;">
                                <div class="col-auto">
                                    <button class="btn btn-success btn-sm" id="btnTambahDipilih">
                                        <i class="fas fa-plus"></i> Tambah yang Dipilih (<span id="jumlah_dipilih">0</span>)
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-secondary btn-sm" id="btnBatalPilih">
                                        <i class="fas fa-times"></i> Batal Pilih
                                    </button>
                                </div>
                            </div>
                            <table id="dailyod" class="table table-bordered table-striped table-hover table-sm">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <th style="width:40px; text-align:center;">
                                            <input type="checkbox" id="chkAll" title="Pilih Semua">
                                        </th>
                                        <th>TANGGAL TRANSAKSI</th>
                                        <th>FAKTUR</th>
                                        <th>NAMA CUSTOMER</th>
                                        <th>KIOS</th>
                                        <th>ALAMAT KIOS</th>
                                        <th>RUTE</th>
                                        <th>REGIONAL</th>
                                        <th style="text-align:center;">ITEM</th>
                                        <th style="text-align:center;">STATUS</th>
                                        <th style="text-align:center; width:100px;">#</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <div class="modal fade" id="modalAddOpname" tabindex="-1" role="dialog" aria-labelledby="modalAddOpnameLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form action="<?= base_url('') ?>" method="post">
                                <div class="modal-content">
                                    <div class="modal-header bg-success">
                                        <h5 class="modal-title" id="modalAddOpnameLabel"><i class="fas fa-box"></i> Input Data Master Barang</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="modal_nama_barang">Kode Faktur</label>
                                            <input type="text" name="modal_nama_barang" id="modal_nama_barang" class="form-control" readonly required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    </div>
                                </div>
                            </form>
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
    // =============================================
    // TOGGLE FAKTUR PENJUALAN
    // =============================================
    function toggleDataPreDO() {
        var tableDiv = document.getElementById("pre_do");
        if (tableDiv.style.display === "none") {
            tableDiv.style.display = "block";
            loadFakturPenjualan();
        } else {
            tableDiv.style.display = "none";
        }
    }

    // =============================================
    // INIT DATATABLE
    // =============================================
    function initDailyodTable() {
        $('#dailyod').DataTable({
            paging:       true,
            lengthChange: true,
            lengthMenu:   [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
            searching:    true,
            ordering:     true,
            info:         true,
            autoWidth:    false,
            responsive:   true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            order: [[1, 'asc']], // urutkan berdasarkan tanggal
            columnDefs: [
                { orderable: false, targets: [0, 10] }, // checkbox & aksi tidak bisa diurutkan
                { className: 'text-center', targets: [0, 8, 9, 10] }
            ]
        });
    }

    // =============================================
    // LOAD FAKTUR PENJUALAN VIA AJAX
    // =============================================
    function loadFakturPenjualan() {
        if ($.fn.DataTable.isDataTable('#dailyod')) {
            $('#dailyod').DataTable().destroy();
        }

        // Reset checkbox
        $('#chkAll').prop('checked', false);
        $('#bulk_action_bar').hide();
        $('#jumlah_dipilih').text(0);

        var tbody = $('#dailyod tbody');
        tbody.html('<tr><td colspan="11" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat data...</td></tr>');

        $.ajax({
            url: '<?= base_url("get_list_faktur_ajax") ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                tbody.empty();

                if (!response || !response.status) {
                    tbody.html('<tr><td colspan="11" class="text-center text-danger">Gagal memuat data: ' + (response.message || '') + '</td></tr>');
                    initDailyodTable();
                    return;
                }

                if (response.data.length === 0) {
                    tbody.html('<tr><td colspan="11" class="text-center">Tidak ada data faktur.</td></tr>');
                    initDailyodTable();
                    return;
                }

                $.each(response.data, function(i, l) {
                    var kdFakturUrl = encodeURIComponent(l.kd_faktur || '');
                    var statusBadge = '';
                    var canAdd      = false;

                    if (l.data_sts === 'list_do' || l.data_sts === 'approved') {
                        statusBadge = '<span class="badge badge-secondary">NOT IN DRAFT</span>';
                        canAdd      = true;
                    } else if (l.data_sts === 'draft') {
                        statusBadge = '<span class="badge badge-warning">DRAFT</span>';
                        canAdd      = true;
                    } else if (l.data_sts === 'proses_do') {
                        statusBadge = '<span class="badge badge-info">PROSES DO</span>';
                        canAdd      = false;
                    } else if (l.data_sts === 'selesai' || l.data_sts === 'selesai_do') {
                        statusBadge = '<span class="badge badge-success">SELESAI</span>';
                        canAdd      = false;
                    } else if (l.data_sts === 'in_delivery') {
                        statusBadge = '<span class="badge badge-success">ON DRAFT</span>';
                        canAdd      = false;
                    } else {
                        statusBadge = '<span class="badge badge-light">' + l.data_sts + '</span>';
                        canAdd      = false;
                    }

                    var checkboxCell = '';
                    var actionBtn    = '';

                    if (canAdd) {
                        checkboxCell = `<td class="text-center">
                            <input type="checkbox" class="chk-faktur" value="${l.kd_faktur}">
                        </td>`;
                        actionBtn = `
                            <div class="row">
                                <a href="<?= base_url('detail_fk') ?>?kd_faktur=${kdFakturUrl}" class="btn btn-info btn-block btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="<?= base_url('insert_tmp') ?>?kd_faktur=${kdFakturUrl}&action=formlist" class="btn btn-success btn-block btn-sm"><i class="fas fa-plus"></i></a>
                            </div>`;
                    } else {
                        checkboxCell = '<td class="text-center">-</td>';
                        actionBtn = `
                            <div class="row">
                                <a href="<?= base_url('detail_fk') ?>?kd_faktur=${kdFakturUrl}" class="btn btn-info btn-block btn-sm"><i class="fas fa-eye"></i></a>
                            </div>`;
                    }

                    tbody.append(`
                        <tr>
                            ${checkboxCell}
                            <td>${l.tgl_inputer}</td>
                            <td>${l.kd_faktur}</td>
                            <td>${l.nama_customer}</td>
                            <td>${l.nama_kios}</td>
                            <td>${l.alamat_kios}</td>
                            <td>${l.kd_rute}</td>
                            <td>${l.regional}</td>
                            <td>${l.total_barang}</td>
                            <td>${statusBadge}</td>
                            <td>${actionBtn}</td>
                        </tr>
                    `);
                });

                initDailyodTable();
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                tbody.html('<tr><td colspan="11" class="text-center text-danger">Error: ' + error + '</td></tr>');
                initDailyodTable();
            }
        });
    }

    // =============================================
    // DOCUMENT READY
    // =============================================
    $(document).ready(function() {

        // ----- CHECKBOX BULK ACTION -----
        $(document).on('change', '#chkAll', function() {
            var checked = $(this).prop('checked');
            $('.chk-faktur').prop('checked', checked);
            updateBulkBar();
        });

        $(document).on('change', '.chk-faktur', function() {
            updateBulkBar();
            var total   = $('.chk-faktur').length;
            var checked = $('.chk-faktur:checked').length;
            $('#chkAll').prop('checked', total === checked && total > 0);
        });

        function updateBulkBar() {
            var jumlah = $('.chk-faktur:checked').length;
            if (jumlah > 0) {
                $('#bulk_action_bar').show();
                $('#jumlah_dipilih').text(jumlah);
            } else {
                $('#bulk_action_bar').hide();
                $('#jumlah_dipilih').text(0);
            }
        }

        $('#btnBatalPilih').on('click', function() {
            $('.chk-faktur, #chkAll').prop('checked', false);
            $('#bulk_action_bar').hide();
            $('#jumlah_dipilih').text(0);
        });

        $('#btnTambahDipilih').on('click', function() {
            var dipilih = [];
            $('.chk-faktur:checked').each(function() {
                dipilih.push($(this).val());
            });

            if (dipilih.length === 0) {
                alert('Pilih minimal satu faktur terlebih dahulu.');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses ' + dipilih.length + ' faktur...');

            prosesInsertBerurutan(dipilih, 0, function() {
                alert(dipilih.length + ' faktur berhasil ditambahkan.');
                location.reload();
            });
        });

        function prosesInsertBerurutan(list, index, callback) {
            if (index >= list.length) {
                callback();
                return;
            }

            var kd_faktur = list[index];

            $.ajax({
                url: '<?= base_url("insert_tmp") ?>',
                data: {
                    kd_faktur: kd_faktur,
                    action: 'formlist'
                },
                type: 'GET',
                success: function() {
                    prosesInsertBerurutan(list, index + 1, callback);
                },
                error: function() {
                    console.warn('Gagal insert faktur: ' + kd_faktur);
                    prosesInsertBerurutan(list, index + 1, callback);
                }
            });
        }

        // ----- EDIT NORUT -----
        $("#cancelEdit").on("click", function() {
            $("#editRow").hide();
        });

        $(".btn-nurut").on("click", function(e) {
            e.preventDefault();
            var row = $(this).closest("tr");
            var id  = row.data("id");

            $.ajax({
                url: "<?= base_url('get_tmpdonorut') ?>",
                type: "POST",
                data: { id: id },
                dataType: "json",
                success: function(data) {
                    $("#id").val(data.id);
                    $("#nourut").val(data.norut_do);
                    $("#editRow").insertAfter(row).show();
                }
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
                        alert("Nomor urut berhasil diperbarui!");
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

        // ----- REKAM DO -----
        $("#rekamdo").on('click', function() {
            var kd_do  = $("#do_isi").val().trim();
            var kota   = $("#regional_isi").val().trim();

            $("input").css("border", "");

            if (!kd_do) {
                $("#do_isi").css("border", "2px solid red");
                return;
            }
            if (!kota) {
                alert('Rute harus diisi');
                $("#regional_isi").css("border", "2px solid red");
                return;
            }

            $.ajax({
                url: "<?= base_url('rekam_do') ?>",
                type: "POST",
                data: {
                    kd_do:    kd_do,
                    tgl_krim: $("#tgl_isi").val(),
                    platno:   $("#plat_isi").val(),
                    kota:     kota,
                    driver:   $("#driver_isi").val()
                },
                dataType: "json",
                cache: false,
                success: function(data) {
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

    }); // end document.ready
</script>
