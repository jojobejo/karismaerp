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

            <?php $this->load->view('content/logistik/ics/modalopname') ?>

            <div class="content-header">
                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-2">
                                <a href="<?= base_url('logistik'); ?>" class="btn btn-primary w-10"><i class="fas fa-home"></i></a>
                            </div>
                            <!-- MODULE ICS -->
                            <?php if ($this->session->userdata('jobdesk') == 'ADMINICS') : ?>
                            <?php elseif ($this->session->userdata('jobdesk') == 'STOCKOPNAME') : ?>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <a href="" class="btn btn-primary w-100">Histori Opname</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card mt-4 mb-2">
                        <div class="card-body">
                            <h4>Data Stock Opname</h4>

                            <table class="table table-bordered" id="list_tb_opname">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th style="width: 10%;text-align: center;">Expired Date</th>
                                        <th style="width: 5%;text-align: center;">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data_ics as $item) : ?>
                                        <tr>
                                            <td><?= $item->nama_barang ?></td>
                                            <td><?= $item->exp_date ?></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary" style="" data-toggle="modal" data-target="#muploadlog<?= $item->id ?>"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
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
        $(function() {
            $("#list_tb_opname").DataTable({
                "responsive": true,
                "lengthChange": false,
                "aaSorting": [],
                "autoWidth": false,
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>