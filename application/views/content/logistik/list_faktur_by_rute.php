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
                        <a href="<?= base_url('detail_do/') . $kdfaktur ?>" class="btn btn-primary mb-2 ml-2"><i class="fas fa-arrow-circle-left"></i></a>
                        <h3></h3>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <?php
                    $batas_ton = 6;
                    $batas_kub = 9;
                    $tonase_awal = (float)($do_summary->total_tonase_faktur ?? 0);
                    $kubikasi_awal = (float)($do_summary->total_kubikasi ?? 0);
                    ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card card-outline card-success mb-0">
                                <div class="card-header py-2">
                                    <h6 class="card-title mb-0"><i class="fas fa-weight mr-1"></i> Estimasi Tonase</h6>
                                </div>
                                <div class="card-body py-2">
                                    <div class="progress mb-2" style="height:14px;border-radius:7px;">
                                        <div class="progress-bar bg-success progress-bar-striped" id="barTonase" style="width:0%"></div>
                                    </div>
                                    <div class="row text-center small">
                                        <div class="col-4">
                                            <div class="text-muted">Saat Ini</div>
                                            <div class="font-weight-bold"><?= number_format($tonase_awal, 3) ?> ton</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted">Dipilih</div>
                                            <div class="font-weight-bold text-primary"><span id="selectedTonase">0.000</span> ton</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted">Total Estimasi</div>
                                            <div class="font-weight-bold" id="totalTonase">0.000 ton</div>
                                        </div>
                                    </div>
                                    <small class="text-muted">Batas tonase: <?= number_format($batas_ton, 1) ?> ton</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-outline card-info mb-0">
                                <div class="card-header py-2">
                                    <h6 class="card-title mb-0"><i class="fas fa-cube mr-1"></i> Estimasi Kubikasi</h6>
                                </div>
                                <div class="card-body py-2">
                                    <div class="progress mb-2" style="height:14px;border-radius:7px;">
                                        <div class="progress-bar bg-info progress-bar-striped" id="barKubikasi" style="width:0%"></div>
                                    </div>
                                    <div class="row text-center small">
                                        <div class="col-4">
                                            <div class="text-muted">Saat Ini</div>
                                            <div class="font-weight-bold"><?= number_format($kubikasi_awal, 4) ?> m&sup3;</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted">Dipilih</div>
                                            <div class="font-weight-bold text-primary"><span id="selectedKubikasi">0.0000</span> m&sup3;</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted">Total Estimasi</div>
                                            <div class="font-weight-bold" id="totalKubikasi">0.0000 m³</div>
                                        </div>
                                    </div>
                                    <small class="text-muted">Batas kubikasi: <?= number_format($batas_kub, 1) ?> m&sup3;</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title">List Faktur Tambahan</h3>
                        </div>
                        <div class="card-body">
                            <table id="lsfakturbyrute" class="table table-bordered table-striped">
                                <thead style="background-color: #212529; color:white;">
                                    <tr>
                                        <td class="text-center" style="width:38px">
                                            <input type="checkbox" id="chkAllFaktur" title="Pilih semua faktur">
                                        </td>
                                        <td>TANGGAL TRANSAKSI</td>
                                        <td>FAKTUR</td>
                                        <td>KIOS</td>
                                        <td>Alamat Kios</td>
                                        <td>RUTE</td>
                                        <td>REGIONAL</td>
                                        <td>ITEM</td>
                                        <td>TONASE</td>
                                        <td>KUBIKASI</td>
                                        <td>#</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_faktur as $l) :
                                        $status = $l->data_sts;
                                        $tonase = (float)($l->total_tonase_faktur ?? 0);
                                        $kubikasi = (float)($l->total_kubikasi ?? 0);
                                    ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="chk-faktur-estimasi"
                                                    data-tonase="<?= $tonase ?>"
                                                    data-kubikasi="<?= $kubikasi ?>">
                                            </td>
                                            <td><?= $l->tgl_inputer ?></td>
                                            <td><?= $l->kd_faktur ?></td>
                                            <td><?= $l->nama_kios ?></td>
                                            <td><?= $l->alamat_kios  ?></td>
                                            <td><?= $l->kd_rute ?></td>
                                            <td><?= $l->regional ?></td>
                                            <td><?= $l->total_barang ?></td>
                                            <td class="text-right"><?= number_format($tonase, 3) ?> ton</td>
                                            <td class="text-right"><?= number_format($kubikasi, 4) ?> m&sup3;</td>
                                            <td>
                                                <div class="row">
                                                    <a href="<?= base_url('insertfromdraft?kddo=' . rawurlencode($kdfaktur) . '&kd_faktur=' . rawurlencode($l->kd_faktur)) ?>" class="btn btn-success btn-block btn-sm"><i class="fas fa-plus"></i></a>
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
        (function() {
            var tonaseAwal = <?= json_encode($tonase_awal) ?>;
            var kubikasiAwal = <?= json_encode($kubikasi_awal) ?>;
            var batasTonase = <?= json_encode($batas_ton) ?>;
            var batasKubikasi = <?= json_encode($batas_kub) ?>;

            function fmt(value, digits) {
                return Number(value || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: digits,
                    maximumFractionDigits: digits
                });
            }

            function updateEstimasi() {
                var addTonase = 0;
                var addKubikasi = 0;

                document.querySelectorAll('.chk-faktur-estimasi:checked').forEach(function(chk) {
                    addTonase += parseFloat(chk.dataset.tonase || 0);
                    addKubikasi += parseFloat(chk.dataset.kubikasi || 0);
                });

                var totalTonase = tonaseAwal + addTonase;
                var totalKubikasi = kubikasiAwal + addKubikasi;
                var pctTonase = batasTonase > 0 ? Math.min((totalTonase / batasTonase) * 100, 100) : 0;
                var pctKubikasi = batasKubikasi > 0 ? Math.min((totalKubikasi / batasKubikasi) * 100, 100) : 0;

                document.getElementById('selectedTonase').textContent = fmt(addTonase, 3);
                document.getElementById('selectedKubikasi').textContent = fmt(addKubikasi, 4);

                var elTotalTonase = document.getElementById('totalTonase');
                var elTotalKubikasi = document.getElementById('totalKubikasi');
                elTotalTonase.textContent = fmt(totalTonase, 3) + ' ton';
                elTotalKubikasi.textContent = fmt(totalKubikasi, 4) + ' m³';
                elTotalTonase.className = 'font-weight-bold ' + (totalTonase > batasTonase ? 'text-danger' : 'text-success');
                elTotalKubikasi.className = 'font-weight-bold ' + (totalKubikasi > batasKubikasi ? 'text-danger' : 'text-info');

                var barTonase = document.getElementById('barTonase');
                var barKubikasi = document.getElementById('barKubikasi');
                barTonase.style.width = pctTonase.toFixed(2) + '%';
                barKubikasi.style.width = pctKubikasi.toFixed(2) + '%';
                barTonase.className = 'progress-bar progress-bar-striped ' + (totalTonase > batasTonase ? 'bg-danger' : 'bg-success');
                barKubikasi.className = 'progress-bar progress-bar-striped ' + (totalKubikasi > batasKubikasi ? 'bg-danger' : 'bg-info');
            }

            document.addEventListener('change', function(e) {
                if (e.target.id === 'chkAllFaktur') {
                    document.querySelectorAll('.chk-faktur-estimasi').forEach(function(chk) {
                        chk.checked = e.target.checked;
                    });
                    updateEstimasi();
                }

                if (e.target.classList.contains('chk-faktur-estimasi')) {
                    var total = document.querySelectorAll('.chk-faktur-estimasi').length;
                    var checked = document.querySelectorAll('.chk-faktur-estimasi:checked').length;
                    var chkAll = document.getElementById('chkAllFaktur');
                    if (chkAll) chkAll.checked = total > 0 && total === checked;
                    updateEstimasi();
                }
            });

            updateEstimasi();
        })();
    </script>
