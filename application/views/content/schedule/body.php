<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>
        <?php $this->load->view('content/schedule/modalschedule.php') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h1></h1>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->
            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col ">
                                    <h3 class="">Schedule Direktur</h3>
                                </div>
                                <div>
                                    <a class="btn btn-primary ml-4 text-center" data-toggle="modal" data-target="#addschedule"><i class="fas fa-plus"></i><b style="text-transform: uppercase;"> Add Schedule</b></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tbtamu" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <td>No</td>
                                        <td>Tanggal</td>
                                        <td>Jam</td>
                                        <td>Instansi</td>
                                        <td>PIC</td>
                                        <td>Estimasi Waktu</td>
                                        <td>Tujuan</td>
                                        <td>Status</td>
                                        <td>Keterangan</td>
                                        <td>#</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    foreach ($getschedule as $get) :
                                        $status = "";
                                        if ($get->status == '0') {
                                            $status = 'NO STATUS';
                                        } else if ($get->status == '1') {
                                            $status = '1';
                                        } else if ($get->status == '2') {
                                            $status = '2';
                                        } else if ($get->status == '3') {
                                            $status = '3';
                                        }
                                    ?>

                                        <tr>
                                            <th><?= $no++ ?></th>
                                            <th><?= format_indo($get->tanggal)  ?></th>
                                            <th><?= $get->jam ?></th>
                                            <th><?= $get->suplier ?></th>
                                            <th><?= $get->pic ?></th>
                                            <th><?= $get->estimasi_end ?></th>
                                            <th><?= $get->tujuan ?></th>
                                            <th><?= $status ?></th>
                                            <th><?= $get->keterangan ?></th>
                                            <th>
                                                <div class="row">
                                                    <div class="col">
                                                        <a href="" class="btn btn-sm btn-warning btn-block" data-toggle="modal" data-target="#editedschedule<?= $get->id ?>"><i class="fas fa-pencil-alt"></i></a>
                                                    </div>
                                                    <div class="col">
                                                        <a href="" class="btn btn-sm btn-danger btn-block" data-toggle="modal" data-target="#deleteschedule<?= $get->id ?>"><i class="fas fa-trash-alt"></i></a>
                                                    </div>
                                                    <div class="col">
                                                        <a href="" class="btn btn-sm btn-info btn-block" data-toggle="modal" data-target="#reschedule<?= $get->id ?>"><i class="fas fa-sync-alt"></i></a>
                                                    </div>
                                                </div>
                                            </th>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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