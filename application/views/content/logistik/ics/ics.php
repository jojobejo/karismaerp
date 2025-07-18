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
                <?php if ($this->session->userdata('jobdesk') == 'ADMINICS') : ?>
                    <section class="content">
                        <div class="row">
                            <div class="col-auto">
                                <a href="<?= base_url('ics') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-home"></i> Dashboard</a>
                            </div>
                            <div class="col-auto">
                                <button id="btnSimpanOpname" class="btn btn-md btn-primary w-100 mb-3"> <i class="fas fa-tasks"></i> Simpan Opname</button>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('ics/icsdo') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-minus-circle"></i> Data DO</a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('ics/icspo') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-plus-circle"></i> Data PO</a>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="container-fluid">
                                    <table class="table table-bordered" id="tbics_erp">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="bg-primary text-white text-center">NAMA</th>
                                                <th colspan="2" class="bg-success text-white text-center">In Today</th>
                                                <th colspan="2" class="bg-danger text-white text-center">Out Today</th>
                                                <th colspan="2" class="bg-info text-white text-center">Saldo Awal</th>
                                                <th colspan="2" class="bg-success text-white text-center"><?= $tanggal_now ?></th>
                                                <th colspan="2" class="bg-danger text-white text-center">Saldo Akhir</th>
                                                <th rowspan="2" class="align-middle bg-success text-white text-center">Status</th>
                                            </tr>
                                            <tr>
                                                <th class="bg-primary text-white">Nama Barang</th>
                                                <th class="bg-primary text-white">Date</th>
                                                <th class="bg-success text-white">Box</th>
                                                <th class="bg-success text-white">Pcs</th>
                                                <th class="bg-danger text-white">Box</th>
                                                <th class="bg-danger text-white">Pcs</th>
                                                <th class="bg-info text-white">Box</th>
                                                <th class="bg-info text-white">Pcs</th>
                                                <th class="bg-success text-white">Box</th>
                                                <th class="bg-success text-white">Pcs</th>
                                                <th class="bg-danger text-white">Box</th>
                                                <th class="bg-danger text-white">Pcs</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($barang_ics as $br) : ?>
                                                <tr>
                                                    <td><?= $br->nama_barang ?></td>
                                                    <td><?= $br->exp_date ?></td>
                                                    <td><?= $br->in_box ?></td>
                                                    <td><?= $br->in_box ?></td>
                                                    <td><?= $br->out_box ?></td>
                                                    <td><?= $br->out_pcs ?></td>
                                                    <td><?= $br->saldo_awal_box ?></td>
                                                    <td><?= $br->saldo_awal_pcs ?></td>
                                                    <td><?= $br->opname_box ?></td>
                                                    <td><?= $br->opname_pcs ?></td>
                                                    <td><?= $br->saldo_akhir_box ?></td>
                                                    <td><?= $br->saldo_akhir_pcs ?></td>
                                                    <?php if ($br->klop == 'KLOP') : ?>
                                                        <td style="text-align: center;">
                                                            <a href="#" class="btn btn-sm btn-success">MATCH</a>
                                                            <a href="<?= base_url('ics/ics_stock_controller/' . $br->id)  ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                                        </td>
                                                    <?php else : ?>
                                                        <td style="text-align: center;">
                                                            <a href="#" class="btn btn-sm btn-danger">NOT MATCH</a>
                                                            <a href="<?= base_url('ics/ics_stock_controller/' . $br->id)  ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                                <!-- <tr>
                                                    <td><?= $br->nama_barang ?></td>
                                                    <td><?= $br->exp_date ?></td>
                                                    <td><?= $br->in_box ?></td>
                                                    <td><?= $br->in_box ?></td>
                                                    <td><?= $br->out_box ?></td>
                                                    <td><?= $br->out_pcs ?></td>
                                                    <td><?= $br->saldo_awal_box ?></td>
                                                    <td><?= $br->saldo_awal_pcs ?></td>
                                                    <td contenteditable="true" class="editable" data-id="<?= $br->nama_barang ?>|<?= $br->exp_date ?>" data-field="opname_box"><?= $br->opname_box ?></td>
                                                    <td contenteditable="true" class="editable" data-id="<?= $br->nama_barang ?>|<?= $br->exp_date ?>" data-field="opname_pcs"><?= $br->opname_pcs ?></td>
                                                    <td><?= $br->saldo_akhir_box ?></td>
                                                    <td><?= $br->saldo_akhir_pcs ?></td>
                                                    <td><?= $br->klop ?></td>
                                                    <td><?= $br->klop ?></td>
                                                </tr> -->
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
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
        $(document).ready(function() {
            $('.editable').on('blur', function() {
                var data_id = $(this).data('id').split('|');
                var nama_barang = data_id[0];
                var exp_date = data_id[1];
                var field = $(this).data('field');
                var value = $(this).text();

                $.ajax({
                    url: "<?= base_url('ics/updateinline') ?>",
                    method: "POST",
                    data: {
                        nama_barang: nama_barang,
                        exp_date: exp_date,
                        field: field,
                        value: value
                    },
                    success: function(response) {
                        console.log(response);
                    },
                    error: function() {
                        alert('Gagal menyimpan data');
                    }
                });
            });
        });
    </script>

    <script>
        $('#btnSimpanOpname').on('click', function() {
            if (confirm("Apakah Anda yakin ingin menyimpan semua data ke Opname hari ini? Data akan disalin dari tb_ics.")) {
                $.ajax({
                    url: "<?= base_url('ics/simpan_opname') ?>",
                    type: "POST",
                    dataType: "json",
                    success: function(response) {
                        if (response.status === 'success') {
                            alert("Berhasil menyimpan data opname.");
                            location.reload();
                        } else {
                            alert("Gagal menyimpan data opname: " + response.message);
                        }
                    },
                    error: function() {
                        alert("Terjadi kesalahan saat menghubungi server.");
                    }
                });
            }
        });
    </script>