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
            <?php $this->load->view('content/logistik/modal/modal_do_upload') ?>

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
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <input type="date" class="form-control" placeholder="Tanggal Kirim" value="" name="po_isi" id="po_isi">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Kode Do" value="" name="do_isi" id="do_isi">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Plat Nomor" value="" name="plat_no" id="plat_no">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Kota Pengiriman" value="" name="kota_isi" id="kota_isi">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-truck"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Driver" value="" name="nm_driver" id="nm_driver">
                                    </div>
                                </div>
                            </div>
                            <table id="" class="table table-striped">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td>Faktur</td>
                                        <td>Nama</td>
                                        <td>Alamat</td>
                                        <td>Kota</td>
                                        <td>No.Telpon</td>
                                        <td>#</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tmp_faktur as $tmp) :
                                        $telp1 = $tmp->telp1;
                                        $telp2 = $tmp->telp2;

                                        if ($telp2 == '0') {
                                            $telp   = $tmp->telp1;
                                        } else {
                                            $telp   = $tmp->telp1 . ' ' . '/' . ' ' . $tmp->telp2;
                                        } ?>
                                        <tr>
                                            <td><?= $tmp->kd_faktur ?></td>
                                            <td><?= $tmp->nama_customer ?></td>
                                            <td><?= $tmp->alamat_kios ?></td>
                                            <td><?= $tmp->regional ?></td>
                                            <td><?= $telp ?></td>
                                            <td>
                                                <a href="<?= base_url('detail_fk/') . $tmp->kd_faktur ?>" class="btn btn-info btn-block btn-sm"><i class="fas fa-eye"></i></a>
                                                <a href="<?= base_url('revert_do/') . $tmp->kd_faktur . '/' . 'formlist' ?>" class="btn btn-block btn-warning"><i class="fas fa-undo"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="card-footer">
                                <div class="media">
                                    <div class="media-body">
                                        <h5 class="mt-0 mb-1"></h5>
                                    </div>
                                    <a href="" class="btn btn-success"><i class="fas fa-print"></i> Rekam Order</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary mb-2 btn-block" onclick="toggleDataPreDO()" id="btnhide"><i class="fas fa-eye"></i> Faktur Penjualan <i class="fas fa-eye"></i> </button>
                    <div class="card" id="pre_do" style="display: none;">
                        <div class="card-body">
                            <table id="dailyod" class="table table-bordered table-striped">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td>FAKTUR</td>
                                        <td>NAMA CUSTOMER</td>
                                        <td>KIOS</td>
                                        <td>ALAMAT KIOS</td>
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
                                            <td><?= $l->kd_faktur ?></td>
                                            <td><?= $l->nama_customer ?></td>
                                            <td><?= $l->nama_kios ?></td>
                                            <td><?= $l->alamat_kios ?></td>
                                            <td><?= $l->regional ?></td>
                                            <td><?= $l->total_barang ?></td>
                                            <?php if ($status == 1) : ?>
                                                <td><span class="badge badge-secondary">NOT IN DRAFT</span></td>
                                            <?php elseif ($status == 2) : ?>
                                                <td><span class="badge badge-success">ON DRAFT</span></td>
                                            <?php endif; ?>
                                            <td>
                                                <div class="row">
                                                    <a href="<?= base_url('detail_fk/') . $l->kd_faktur ?>" class="btn btn-info btn-block btn-sm"><i class="fas fa-eye"></i></a>
                                                    <a href="<?= base_url('insert_tmp/') . $l->kd_faktur . '/' . 'formlist' ?>" class="btn btn-success btn-block btn-sm"><i class="fas fa-plus"></i></a>
                                                </div>
                                            </td>
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
            // Load data dari get_tmp_do
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

            loadTmpDo(); // Panggil saat halaman dimuat

            // Simpan data ke tb_do
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
                            loadTmpDo(); // Refresh data
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat menyimpan data');
                    }
                });
            });
        });
    </script>




    <!-- <div class="row mb-2">
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <input type="date" class="form-control" placeholder="Tanggal Kirim" value="" name="po_isi" id="po_isi">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Kode Do" value="" name="do_isi" id="do_isi">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Plat Nomor" value="" name="plat_no" id="plat_no">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-clipboard"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Kota Pengiriman" value="" name="kota_isi" id="kota_isi">
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-truck"></i></span>
                                        </div>
                                        <input type="text" class="form-control" placeholder="Driver" value="" name="nm_driver" id="nm_driver">
                                    </div>
                                </div>
                            </div>
                            <table id="" class="table table-striped">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td>Faktur</td>
                                        <td>Nama</td>
                                        <td>Alamat</td>
                                        <td>Kota</td>
                                        <td>No.Telpon</td>
                                        <td>#</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tmp_faktur as $tmp) :
                                        $telp1 = $tmp->telp1;
                                        $telp2 = $tmp->telp2;

                                        if ($telp2 == '0') {
                                            $telp   = $tmp->telp1;
                                        } else {
                                            $telp   = $tmp->telp1 . ' ' . '/' . ' ' . $tmp->telp2;
                                        } ?>
                                        <tr>
                                            <td><?= $tmp->kd_faktur ?></td>
                                            <td><?= $tmp->nama_customer ?></td>
                                            <td><?= $tmp->alamat_kios ?></td>
                                            <td><?= $tmp->regional ?></td>
                                            <td><?= $telp ?></td>
                                            <td>
                                                <a href="<?= base_url('revert_do/') . $tmp->kd_faktur ?>" class="btn btn-block btn-warning"><i class="fas fa-undo"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="card-footer">
                                <div class="media">
                                    <div class="media-body">
                                        <h5 class="mt-0 mb-1"></h5>
                                    </div>
                                    <a href="" class="btn btn-success"><i class="fas fa-print"></i> Rekam Order</a>
                                </div>
                            </div> -->