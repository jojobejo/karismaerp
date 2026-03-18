<!-- view/content/logistik/bongkaran/arsip.php -->

<style>
/* ── Warna thead Arsip LK (biru) ── */
#tabelArsipLK thead.thead-dark th {
    background: #1565c0 !important;
    color: #fff !important;
    border-color: #0d47a1 !important;
}
/* ── Warna thead Arsip KK (hijau) ── */
#tabelArsipKK thead.thead-dark th {
    background: #1b5e20 !important;
    color: #fff !important;
    border-color: #145214 !important;
}

/* ── Baris label pemisah kelompok ── */
tr.separator-label td {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 4px 12px !important;
    border-top: 2px dashed #aaa !important;
    border-bottom: 2px dashed #aaa !important;
}
tr.separator-label.sep-today  td { background:#e8f5e9; border-color:#43a047 !important; color:#1b5e20; }
tr.separator-label.sep-older  td { background:#f3f3f3; border-color:#bbb    !important; color:#555;    }
</style>

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

                <!-- Tombol navigasi atas -->
                <div class="row mb-3 align-items-center">
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
                <?php
                $today = date('Y-m-d');
                $ab_today = !empty($arsip_bongkar) ? array_filter($arsip_bongkar, fn($r) => date('Y-m-d', strtotime($r['archived_at'] ?? 'now')) === $today) : [];
                $ab_older = !empty($arsip_bongkar) ? array_filter($arsip_bongkar, fn($r) => date('Y-m-d', strtotime($r['archived_at'] ?? 'now')) !== $today) : [];
                ?>
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
                            $no = 1;

                            function durasiArsip($mulai, $selesai) {
                                if (empty($mulai) || empty($selesai)) return '-';
                                $sel = strtotime($selesai) - strtotime($mulai);
                                if ($sel <= 0) return '-';
                                $j = floor($sel/3600); $m = floor(($sel%3600)/60);
                                return $j > 0 ? "{$j} jam {$m} menit" : "{$m} menit";
                            }

                            function barisBongkarArsip($row, &$no, $row_class) { ?>
                            <tr class="<?= $row_class ?>">
                                <td><?= $no++ ?></td>
                                <td><small><?= !empty($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '-' ?></small></td>
                                <td><small><?= htmlspecialchars($row['kode_bongkar']) ?></small></td>
                                <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['nm_checker'] ?? '-') ?></td>
                                <td><small><?= !empty($row['waktu_mulai'])   ? date('d/m/Y H:i', strtotime($row['waktu_mulai']))   : '-' ?></small></td>
                                <td><small><?= !empty($row['waktu_selesai']) ? date('d/m/Y H:i', strtotime($row['waktu_selesai'])) : '-' ?></small></td>
                                <td><small><?= durasiArsip($row['waktu_mulai'] ?? '', $row['waktu_selesai'] ?? '') ?></small></td>
                                <td><?= htmlspecialchars($row['archived_by'] ?? '-') ?></td>
                                <td><small><?= !empty($row['archived_at']) ? date('d/m/Y H:i', strtotime($row['archived_at'])) : '-' ?></small></td>
                            </tr>
                            <?php }

                            if (empty($arsip_bongkar)) : ?>
                                <tr><td colspan="10" class="text-center text-muted"><i class="fas fa-inbox mr-1"></i> Belum ada arsip bongkaran</td></tr>
                            <?php else : ?>

                                <?php if (!empty($ab_today)) : ?>
                                <tr class="separator-label sep-today">
                                    <td colspan="10"><i class="fas fa-check-circle mr-1"></i> Diarsipkan Hari Ini</td>
                                </tr>
                                <?php foreach ($ab_today as $row) : barisBongkarArsip($row, $no, 'table-success'); endforeach; ?>
                                <?php endif; ?>

                                <?php if (!empty($ab_older)) : ?>
                                <tr class="separator-label sep-older">
                                    <td colspan="10"><i class="fas fa-history mr-1"></i> Arsip Sebelumnya</td>
                                </tr>
                                <?php foreach ($ab_older as $row) : barisBongkarArsip($row, $no, ''); endforeach; ?>
                                <?php endif; ?>

                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============================================================
                     ARSIP LOADING LK  (thead biru)
                ============================================================ -->
                <?php
                $al_today = !empty($arsip_lk) ? array_filter($arsip_lk, fn($r) => date('Y-m-d', strtotime($r['archived_at'] ?? 'now')) === $today) : [];
                $al_older = !empty($arsip_lk) ? array_filter($arsip_lk, fn($r) => date('Y-m-d', strtotime($r['archived_at'] ?? 'now')) !== $today) : [];
                ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-truck mr-2"></i> Arsip Loading LK</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-sm" id="tabelArsipLK">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Kode</th>
                                    <th>Keterangan</th>
                                    <th>Tgl</th>
                                    <th>Checker</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                    <th>Diarsipkan Oleh</th>
                                    <th>Tgl Arsip</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $no = 1;

                            function barisLoadingArsip($row, &$no, $row_class) {
                                $durasi = '-';
                                if (!empty($row['waktu_mulai']) && !empty($row['waktu_selesai'])) {
                                    $sel = strtotime($row['waktu_selesai']) - strtotime($row['waktu_mulai']);
                                    if ($sel > 0) {
                                        $j = floor($sel/3600); $m = floor(($sel%3600)/60);
                                        $durasi = $j > 0 ? "{$j} jam {$m} menit" : "{$m} menit";
                                    }
                                }
                            ?>
                            <tr class="<?= $row_class ?>">
                                <td><?= $no++ ?></td>
                                <td><small><?= htmlspecialchars($row['kode'] ?? '-') ?></small></td>
                                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                <td><small><?= $row['tgl'] ?></small></td>
                                <td><?= htmlspecialchars($row['nm_checker'] ?? '-') ?></td>
                                <td><small><?= !empty($row['waktu_mulai'])   ? date('d/m H:i', strtotime($row['waktu_mulai']))   : '-' ?></small></td>
                                <td><small><?= !empty($row['waktu_selesai']) ? date('d/m H:i', strtotime($row['waktu_selesai'])) : '-' ?></small></td>
                                <td><small><?= $durasi ?></small></td>
                                <td><span class="badge badge-success"><?= str_replace('_',' ',$row['status']) ?></span></td>
                                <td><?= htmlspecialchars($row['archived_by'] ?? '-') ?></td>
                                <td><small><?= !empty($row['archived_at']) ? date('d/m/Y H:i', strtotime($row['archived_at'])) : '-' ?></small></td>
                            </tr>
                            <?php }

                            if (empty($arsip_lk)) : ?>
                                <tr><td colspan="6" class="text-center text-muted"><i class="fas fa-inbox mr-1"></i> Belum ada arsip Loading LK</td></tr>
                            <?php else : ?>

                                <?php if (!empty($al_today)) : ?>
                                <tr class="separator-label sep-today">
                                    <td colspan="11"><i class="fas fa-check-circle mr-1"></i> Diarsipkan Hari Ini</td>
                                </tr>
                                <?php foreach ($al_today as $row) : barisLoadingArsip($row, $no, 'table-success'); endforeach; ?>
                                <?php endif; ?>

                                <?php if (!empty($al_older)) : ?>
                                <tr class="separator-label sep-older">
                                    <td colspan="11"><i class="fas fa-history mr-1"></i> Arsip Sebelumnya</td>
                                </tr>
                                <?php foreach ($al_older as $row) : barisLoadingArsip($row, $no, ''); endforeach; ?>
                                <?php endif; ?>

                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============================================================
                     ARSIP LOADING KK  (thead hijau)
                ============================================================ -->
                <?php
                $ak_today = !empty($arsip_kk) ? array_filter($arsip_kk, fn($r) => date('Y-m-d', strtotime($r['archived_at'] ?? 'now')) === $today) : [];
                $ak_older = !empty($arsip_kk) ? array_filter($arsip_kk, fn($r) => date('Y-m-d', strtotime($r['archived_at'] ?? 'now')) !== $today) : [];
                ?>
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-truck-loading mr-2"></i> Arsip Loading KK</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-sm" id="tabelArsipKK">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Kode</th>
                                    <th>Keterangan</th>
                                    <th>Tgl</th>
                                    <th>Checker</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                    <th>Diarsipkan Oleh</th>
                                    <th>Tgl Arsip</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $no = 1;

                            if (empty($arsip_kk)) : ?>
                                <tr><td colspan="6" class="text-center text-muted"><i class="fas fa-inbox mr-1"></i> Belum ada arsip Loading KK</td></tr>
                            <?php else : ?>

                                <?php if (!empty($ak_today)) : ?>
                                <tr class="separator-label sep-today">
                                    <td colspan="11"><i class="fas fa-check-circle mr-1"></i> Diarsipkan Hari Ini</td>
                                </tr>
                                <?php foreach ($ak_today as $row) : barisLoadingArsip($row, $no, 'table-success'); endforeach; ?>
                                <?php endif; ?>

                                <?php if (!empty($ak_older)) : ?>
                                <tr class="separator-label sep-older">
                                    <td colspan="11"><i class="fas fa-history mr-1"></i> Arsip Sebelumnya</td>
                                </tr>
                                <?php foreach ($ak_older as $row) : barisLoadingArsip($row, $no, ''); endforeach; ?>
                                <?php endif; ?>

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