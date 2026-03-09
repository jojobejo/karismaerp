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
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                    <div class="col-auto">
                        <span class="badge badge-success p-2" style="font-size:12px;">
                            <i class="fas fa-circle mr-1"></i> Hijau = Diarsipkan hari ini
                        </span>
                    </div>
                </div>

                <!-- ============================================================
                     ARSIP BONGKARAN
                ============================================================ -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title"><i class="fas fa-dolly mr-2"></i> Arsip Bongkaran</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-sm" id="tabelArsip">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Tgl Dibuat</th>
                                    <th>Kode</th>
                                    <th>Keterangan</th>
                                    <th>Checker</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Durasi</th>
                                    <th>Diarsipkan Oleh</th>
                                    <th>Tgl Arsip</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $today = date('Y-m-d');
                            if (!empty($arsip_bongkar)) :
                                $no = 1;
                                foreach ($arsip_bongkar as $row) :
                                    $tgl_arsip  = date('Y-m-d', strtotime($row['archived_at'] ?? 'now'));
                                    $is_today   = ($tgl_arsip === $today);

                                    // Hitung durasi
                                    $durasi_str = '-';
                                    if (!empty($row['waktu_mulai']) && !empty($row['waktu_selesai'])) {
                                        $selisih = strtotime($row['waktu_selesai']) - strtotime($row['waktu_mulai']);
                                        if ($selisih > 0) {
                                            $jam   = floor($selisih / 3600);
                                            $menit = floor(($selisih % 3600) / 60);
                                            $durasi_str = $jam > 0 ? "{$jam} jam {$menit} menit" : "{$menit} menit";
                                        }
                                    }
                            ?>
                            <tr class="<?= $is_today ? 'table-success' : '' ?>">
                                <td><?= $no++ ?></td>
                                <td><small><?= !empty($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '-' ?></small></td>
                                <td><small><?= htmlspecialchars($row['kode_bongkar']) ?></small></td>
                                <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nm_checker'] ?? '-') ?></td>
                                <td><small><?= !empty($row['waktu_mulai'])   ? date('d/m/Y H:i', strtotime($row['waktu_mulai']))   : '-' ?></small></td>
                                <td><small><?= !empty($row['waktu_selesai']) ? date('d/m/Y H:i', strtotime($row['waktu_selesai'])) : '-' ?></small></td>
                                <td><small><?= $durasi_str ?></small></td>
                                <td><?= htmlspecialchars($row['archived_by'] ?? '-') ?></td>
                                <td><small><?= !empty($row['archived_at']) ? date('d/m/Y H:i', strtotime($row['archived_at'])) : '-' ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="10" class="text-center text-muted"><i class="fas fa-inbox mr-1"></i> Belum ada arsip bongkaran</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============================================================
                     ARSIP LOADING LK
                ============================================================ -->
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
                                foreach ($arsip_lk as $row) :
                                    $tgl_arsip = date('Y-m-d', strtotime($row['archived_at'] ?? 'now'));
                                    $is_today  = ($tgl_arsip === $today);
                            ?>
                            <tr class="<?= $is_today ? 'table-success' : '' ?>">
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                <td><?= $row['tgl'] ?></td>
                                <td><span class="badge badge-success"><?= str_replace('_',' ',$row['status']) ?></span></td>
                                <td><?= htmlspecialchars($row['archived_by'] ?? '-') ?></td>
                                <td><small><?= !empty($row['archived_at']) ? date('d/m/Y H:i', strtotime($row['archived_at'])) : '-' ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="6" class="text-center text-muted">Belum ada arsip Loading LK</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============================================================
                     ARSIP LOADING KK
                ============================================================ -->
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
                                foreach ($arsip_kk as $row) :
                                    $tgl_arsip = date('Y-m-d', strtotime($row['archived_at'] ?? 'now'));
                                    $is_today  = ($tgl_arsip === $today);
                            ?>
                            <tr class="<?= $is_today ? 'table-success' : '' ?>">
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                <td><?= $row['tgl'] ?></td>
                                <td><span class="badge badge-success"><?= str_replace('_',' ',$row['status']) ?></span></td>
                                <td><?= htmlspecialchars($row['archived_by'] ?? '-') ?></td>
                                <td><small><?= !empty($row['archived_at']) ? date('d/m/Y H:i', strtotime($row['archived_at'])) : '-' ?></small></td>
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