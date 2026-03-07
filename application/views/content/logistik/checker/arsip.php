<!-- view/content/logistik/bongkaran/arsip.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <section class="content">

                <div class="row mb-3">
                    <div class="col-auto">
                        <a href="<?= base_url('checker') ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <!-- Arsip Bongkaran -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title"><i class="fas fa-dolly mr-2"></i> Arsip Bongkaran</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-sm" id="tabelArsip">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Kode</th>
                                    <th>Keterangan</th>
                                    <th>Checker</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Diarsipkan Oleh</th>
                                    <th>Tgl Arsip</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($arsip_bongkar)) : $no = 1;
                                foreach ($arsip_bongkar as $row) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><small><?= htmlspecialchars($row['kode_bongkar']) ?></small></td>
                                    <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['nm_checker'] ?? '-') ?></td>
                                    <td><small><?= $row['waktu_mulai']   ?? '-' ?></small></td>
                                    <td><small><?= $row['waktu_selesai'] ?? '-' ?></small></td>
                                    <td><?= htmlspecialchars($row['archived_by'] ?? '-') ?></td>
                                    <td><small><?= $row['archived_at'] ?? '-' ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="8" class="text-center text-muted">Belum ada arsip bongkaran</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Arsip Loading LK -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-truck mr-2"></i> Arsip Loading LK</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-sm" id="tabelArsipLK">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Keterangan</th>
                                    <th>Tgl</th>
                                    <th>Status</th>
                                    <th>Diarsipkan Oleh</th>
                                    <th>Tgl Arsip</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($arsip_lk)) : $no = 1;
                                foreach ($arsip_lk as $row) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                    <td><?= $row['tgl'] ?></td>
                                    <td><span class="badge badge-success"><?= str_replace('_',' ',$row['status']) ?></span></td>
                                    <td><?= htmlspecialchars($row['archived_by'] ?? '-') ?></td>
                                    <td><small><?= $row['archived_at'] ?? '-' ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="6" class="text-center text-muted">Belum ada arsip Loading LK</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Arsip Loading KK -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-truck-loading mr-2"></i> Arsip Loading KK</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-sm" id="tabelArsipKK">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Keterangan</th>
                                    <th>Tgl</th>
                                    <th>Status</th>
                                    <th>Diarsipkan Oleh</th>
                                    <th>Tgl Arsip</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($arsip_kk)) : $no = 1;
                                foreach ($arsip_kk as $row) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                    <td><?= $row['tgl'] ?></td>
                                    <td><span class="badge badge-success"><?= str_replace('_',' ',$row['status']) ?></span></td>
                                    <td><?= htmlspecialchars($row['archived_by'] ?? '-') ?></td>
                                    <td><small><?= $row['archived_at'] ?? '-' ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="6" class="text-center text-muted">Belum ada arsip Loading KK</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>
        </div>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
</body>