<style>
    .small-chart {
        width: 50% !important;
        height: auto;
        margin: auto;
        display: block;
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
                <section class="content">

                    <div class="row">
                        <div class="col-auto">
                            <a href="<?= base_url('dashboard_opname') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-home"></i>Dashboard</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('master_barang') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Master Barang</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('compare_opname_all') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-book-open"></i> Stock Compare All</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('compare_opname_exp') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-book-open"></i> Stock Compare Exp</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('opname_datapending') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-tasks"></i> Data Pending</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('request_opname_admin') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-notes-medical"></i> Request Input</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body text-center">

                            <h1 style="text-align: center; margin-bottom: 25px;">Dashboard Opname</h1>
                            <!-- chart wrapper supaya di tengah -->
                            <div style="max-width: 1000px; margin: 0 auto;">
                                <canvas id="cartsummary"></canvas>
                            </div>

                            <!-- summary text -->
                            <div class="mt-3">
                                <span class="mx-2">All Barang : <b><?= $summary['total_barang'] ?></b></span>
                                <span class="mx-2">Total Match : <b><?= $summary['total_match'] ?></b></span>
                                <span class="mx-2">Total Not Match : <b><?= $summary['total_notmatch'] ?></b></span>
                            </div>

                        </div>
                    </div>

                </section>
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
        const ctx = document.getElementById('cartsummary').getContext('2d');
        const chartSummary = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [
                    'Match (<?= $summary['persen_match'] ?>%)',
                    'Not Match (<?= $summary['persen_notmatch'] ?>%)'
                ],
                datasets: [{
                    data: [<?= $summary['total_match'] ?>, <?= $summary['total_notmatch'] ?>],
                    backgroundColor: ['#28a745', '#dc3545'], // hijau & merah
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                return label + ': ' + value + ' barang';
                            }
                        }
                    }
                }
            }
        });
    </script>