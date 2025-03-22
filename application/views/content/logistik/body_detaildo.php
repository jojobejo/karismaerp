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
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row">
                        <a href="<?= base_url('logistik') ?>" class="btn btn-primary mb-2 ml-2"><i class="fas fa-arrow-circle-left"></i></a>
                        <h3>HALAMAN DETAIL DO</h3>
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
                            <h2 class="mb-4">Data Kios</h2>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th rowspan="2">No</th>
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
                                            $norut_counter = 1; // Reset nomor urut untuk faktur baru
                                        }

                                    ?>
                                        <tr>
                                            <?php if ($show_faktur_info) : ?>
                                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->norut ?></td>
                                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->nama_kios ?></td>
                                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->regional ?></td>
                                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>">JBR1</td>
                                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->kd_faktur ?></td>
                                                <td rowspan="<?= $rowspan_count[$row->kd_faktur] ?>"><?= $row->tgl_inputer ?></td>
                                            <?php endif; ?>
                                            <td><?= $norut_counter++ ?></td>
                                            <td><?= $row->nm_barang ?></td>
                                            <td><?= $row->no_lot ?> - <?= $row->tgl_exp ?></td>
                                            <td>0</td>
                                            <td><?= $row->qty ?> Btl</td>
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