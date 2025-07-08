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
                                <a href="<?= base_url('admstocktracking') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i>Update Data</a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="container-fluid">
                                    <table class="table table-bordered ">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="bg-success text-white text-center">NAMA</th>
                                                <th colspan="2" class="bg-danger text-white text-center">Out Today</th>
                                                <th colspan="2" class="bg-success text-white text-center">Saldo Awal</th>
                                                <th colspan="2" class="bg-success text-white text-center">7/7/2025</th>
                                                <th colspan="2" class="bg-danger text-white text-center">Saldo Akhir</th>
                                                <th rowspan="2" class="align-middle bg-danger text-white text-center">klop</th>
                                            </tr>
                                            <tr>
                                                <th class="bg-success text-white">Nama Barang</th>
                                                <th class="bg-success text-white">Date</th>
                                                <th class="bg-danger text-white">Box</th>
                                                <th class="bg-danger text-white">Pcs</th>
                                                <th class="bg-success text-white">Box</th>
                                                <th class="bg-success text-white">Pcs</th>
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
                                                    <td><?= $br->out_box ?></td>
                                                    <td><?= $br->out_pcs ?></td>
                                                    <td><?= $br->saldo_awal_box ?></td>
                                                    <td><?= $br->saldo_awal_pcs ?></td>
                                                    <td contenteditable="true" class="editable" data-id="<?= $br->nama_barang ?>|<?= $br->exp_date ?>" data-field="opname_box"><?= $br->opname_box ?></td>
                                                    <td contenteditable="true" class="editable" data-id="<?= $br->nama_barang ?>|<?= $br->exp_date ?>" data-field="opname_pcs"><?= $br->opname_pcs ?></td>
                                                    <td><?= $br->saldo_akhir_box ?></td>
                                                    <td><?= $br->saldo_akhir_pcs ?></td>
                                                    <td><?= $br->klop ?></td>

                                                </tr>
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
                    url: "<?= base_url('admstocktracking/update_inline') ?>",
                    method: "POST",
                    data: {
                        nama_barang: nama_barang,
                        exp_date: exp_date,
                        field: field,
                        value: value
                    },
                    success: function(response) {
                        console.log(response);
                        // bisa tambahkan notifikasi sukses jika mau
                    },
                    error: function() {
                        alert('Gagal menyimpan data');
                    }
                });
            });
        });
    </script>