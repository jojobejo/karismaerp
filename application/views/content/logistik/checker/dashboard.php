<?php
// view/content/logistik/checker/dashboard.php
?>
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

                <!-- ============================================================
                     CSS TAMBAHAN: badge custom & style proses
                ============================================================ -->
                <style>
                    /* Badge status custom */
                    .badge-purple { background: #ede9fe !important; color: #5b21b6 !important; border: 1px solid #c4b5fd; }
                    .badge-pink   { background: #fce7f3 !important; color: #9d174d !important; border: 1px solid #f9a8d4; }
                    .badge-teal   { background: #ccfbf1 !important; color: #0f5132 !important; border: 1px solid #6ee7b7; }
                    .badge-status {
                        display: inline-block;
                        padding: 3px 8px;
                        border-radius: 20px;
                        font-size: 11px;
                        font-weight: 500;
                        white-space: nowrap;
                        line-height: 1.5;
                    }
                    /* Durasi badge di proses */
                    .badge-durasi {
                        display: inline-block;
                        background: #f1f5f9;
                        border: 1px solid #e2e8f0;
                        border-radius: 4px;
                        padding: 1px 6px;
                        font-size: 10px;
                        color: #64748b;
                        margin-left: 4px;
                    }
                    /* Tab Semua section header */
                    .semua-section-header {
                        font-size: 13px;
                        font-weight: 600;
                        color: #495057;
                        padding: 8px 12px;
                        background: #f8f9fa;
                        border-left: 4px solid #6c757d;
                        border-radius: 4px;
                        margin-bottom: 10px;
                        margin-top: 16px;
                    }
                    .semua-section-header.bongkar { border-left-color: #ffc107; }
                    .semua-section-header.lk      { border-left-color: #17a2b8; }
                    .semua-section-header.kk       { border-left-color: #28a745; }
                    /* Checker info di proses */
                    .checker-info {
                        font-size: 12px;
                        color: #6c757d;
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 4px;
                        margin-top: 2px;
                    }
                    .checker-info .checker-name {
                        font-weight: 600;
                        color: #343a40;
                    }
                    .checker-info .sep { color: #ced4da; }
                    .checker-info .mulai-time { color: #6c757d; }
                </style>

                <!-- ============================================================
                     HELPER PHP
                ============================================================ -->
                <?php
                // ---- Helper status ----
                function isProses($s)  { return in_array($s, ['PROSES', 'PROSES_LOADING']); }
                function isDone($s)    { return $s === 'DONE'; }
                function isWait($s)    { return !isProses($s) && !isDone($s); }

                function statusLabel($s) {
                    $map = [
                        'MENUNGGU'        => 'Menunggu',
                        'PROSES'          => 'Proses bongkar',
                        'PENYIAPAN_BARANG'=> 'Penyiapan',
                        'DONE'            => 'Done',
                        'CETAK_DO'        => 'Cetak DO',
                        'DO_SELESAI'      => 'DO Selesai',
                        'PROSES_LOADING'  => 'Proses loading',
                    ];
                    return $map[$s] ?? str_replace('_', ' ', $s);
                }

                /**
                 * Mengembalikan badge HTML berwarna sesuai status
                 */
                function statusBadge($s) {
                    $map = [
                        'MENUNGGU'        => ['label' => 'Menunggu',       'class' => 'badge-secondary'],
                        'PROSES'          => ['label' => 'Proses bongkar', 'class' => 'badge-warning'],
                        'PENYIAPAN_BARANG'=> ['label' => 'Penyiapan',      'class' => 'badge-purple'],
                        'DONE'            => ['label' => 'Done',            'class' => 'badge-success'],
                        'CETAK_DO'        => ['label' => 'Cetak DO',        'class' => 'badge-pink'],
                        'DO_SELESAI'      => ['label' => 'DO Selesai',      'class' => 'badge-teal'],
                        'PROSES_LOADING'  => ['label' => 'Proses loading',  'class' => 'badge-info'],
                    ];
                    $b = $map[$s] ?? ['label' => str_replace('_', ' ', $s), 'class' => 'badge-secondary'];
                    return '<span class="badge badge-status ' . $b['class'] . '">' . $b['label'] . '</span>';
                }

                /**
                 * Hitung durasi dari waktu_mulai sampai sekarang (real-time)
                 */
                function durasiMulai($waktu_mulai) {
                    if (empty($waktu_mulai)) return '';
                    $diff = time() - strtotime($waktu_mulai);
                    if ($diff < 60) return $diff . 'd';
                    $j = floor($diff / 3600);
                    $m = floor(($diff % 3600) / 60);
                    return $j > 0 ? "{$j}j {$m}m" : "{$m}m";
                }

                /**
                 * Hitung durasi antara mulai & selesai
                 */
                function durasiSelesai($waktu_mulai, $waktu_selesai) {
                    if (empty($waktu_mulai) || empty($waktu_selesai)) return '';
                    $sel = strtotime($waktu_selesai) - strtotime($waktu_mulai);
                    $j = floor($sel / 3600);
                    $m = floor(($sel % 3600) / 60);
                    return $j > 0 ? "{$j}j {$m}m" : "{$m}m";
                }

                // ---- Hitung summary ----
                $total_pintu  = 4;
                $pintu_aktif  = 0;
                $total_proses = 0;
                $total_done   = 0;

                foreach ($bongkaran as $b) {
                    if (isProses($b['status'])) { $pintu_aktif++; $total_proses++; }
                    if (isDone($b['status']))    $total_done++;
                }
                foreach ($list_lk as $lk) {
                    if (isProses($lk['status'])) $total_proses++;
                    if (isDone($lk['status']))   $total_done++;
                }
                foreach ($list_kk as $kk) {
                    if (isProses($kk['status'])) $total_proses++;
                    if (isDone($kk['status']))   $total_done++;
                }
                ?>

                <!-- ============================================================
                     HEADER DASHBOARD
                ============================================================ -->
                <div class="row align-items-center mb-3">
                    <div class="col">
                        <h4 class="mb-0">
                            <i class="fas fa-warehouse mr-2 text-primary"></i> Dashboard Warehouse
                        </h4>
                        <small class="text-muted"><?= date('l, d F Y — H:i') ?> WIB</small>
                    </div>

                    <div class="col-auto d-flex align-items-center">
                        <a href="<?= base_url('checker/arsip') ?>" class="btn btn-sm btn-secondary mr-2">
                            <i class="fas fa-archive mr-1"></i> Arsip
                        </a>

                        <a href="<?= base_url('checker') ?>" class="btn btn-sm btn-outline-secondary mr-2">
                            <i class="fas fa-list mr-1"></i> Halaman Kerja
                        </a>

                        <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- ============================================================
                     METRIC CARDS
                ============================================================ -->
                <div class="row mb-3">
                    <div class="col-md-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $pintu_aktif ?> / <?= $total_pintu ?></h3>
                                <p>Pintu Aktif</p>
                            </div>
                            <div class="icon"><i class="fas fa-door-open"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $total_proses ?></h3>
                                <p>Proses Berjalan</p>
                            </div>
                            <div class="icon"><i class="fas fa-spinner"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $total_done ?></h3>
                                <p>Selesai Hari Ini</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-double"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= count($bongkaran) + count($list_lk) + count($list_kk) ?></h3>
                                <p>Total Aktivitas</p>
                            </div>
                            <div class="icon"><i class="fas fa-tasks"></i></div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================
                     VISUALISASI PINTU GUDANG
                ============================================================ -->
                <!-- <div class="card mb-3">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title">
                            <i class="fas fa-door-open mr-2"></i> Kondisi Pintu Gudang (<?= $total_pintu ?> Pintu)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row" id="pintu-row">
                            <?php for ($i = 0; $i < $total_pintu; $i++) :
                                $pb = $bongkaran[$i] ?? null;
                                if (!$pb) {
                                    $door_class = 'border-secondary';
                                    $door_bg    = '';
                                    $door_badge = '<span class="badge badge-status badge-secondary">Kosong</span>';
                                    $door_info  = '<small class="text-muted">—</small>';
                                } elseif (isDone($pb['status'])) {
                                    $door_class = 'border-success';
                                    $door_bg    = 'bg-light';
                                    $door_badge = '<span class="badge badge-status badge-success">Selesai</span>';
                                    $door_info  = '<small class="text-success">' . htmlspecialchars($pb['keterangan'] ?? $pb['kode_bongkar']) . '</small>';
                                } elseif (isProses($pb['status'])) {
                                    $door_class = 'border-warning';
                                    $door_bg    = 'bg-warning bg-opacity-10';
                                    $door_badge = '<span class="badge badge-status badge-warning">Aktif</span>';
                                    $door_info  = '<small class="text-warning font-weight-bold">' . htmlspecialchars($pb['keterangan'] ?? $pb['kode_bongkar']) . '</small>';
                                } else {
                                    $door_class = 'border-info';
                                    $door_bg    = '';
                                    $door_badge = '<span class="badge badge-status badge-info">Menunggu</span>';
                                    $door_info  = '<small class="text-info">' . htmlspecialchars($pb['keterangan'] ?? $pb['kode_bongkar']) . '</small>';
                                }
                                $fc = isDone($pb['status'] ?? '') ? '#28a745'
                                    : (isProses($pb['status'] ?? '') ? '#ffc107'
                                    : ($pb ? '#17a2b8' : '#6c757d'));
                            ?>
                            <div class="col-md-3 col-6 mb-3">
                                <div class="card <?= $door_class ?> text-center h-100" style="border-width:2px">
                                    <div class="card-body py-3 <?= $door_bg ?>">
                                        <div style="margin-bottom:8px">
                                            <svg width="60" height="70" viewBox="0 0 60 70" xmlns="http://www.w3.org/2000/svg">
                                                <rect x="2" y="2" width="56" height="66" rx="4" fill="<?= $fc ?>22" stroke="<?= $fc ?>" stroke-width="3"/>
                                                <rect x="7" y="7" width="46" height="57" rx="3" fill="<?= $fc ?>44"/>
                                                <rect x="10" y="10" width="40" height="18" rx="2" fill="<?= $fc ?>66"/>
                                                <rect x="10" y="32" width="40" height="28" rx="2" fill="<?= $fc ?>66"/>
                                                <?php if (isDone($pb['status'] ?? '')) : ?>
                                                    <path d="M22 46 L28 53 L40 40" stroke="<?= $fc ?>" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                                <?php elseif (isProses($pb['status'] ?? '')) : ?>
                                                    <circle cx="44" cy="46" r="5" fill="<?= $fc ?>"/>
                                                    <path d="M15 52 Q22 48 30 52 Q38 56 45 52" stroke="<?= $fc ?>" stroke-width="2" fill="none" stroke-dasharray="3 2"/>
                                                <?php elseif ($pb) : ?>
                                                    <circle cx="30" cy="46" r="8" stroke="<?= $fc ?>" stroke-width="2" fill="none"/>
                                                    <path d="M30 40 L30 46 L35 46" stroke="<?= $fc ?>" stroke-width="2" stroke-linecap="round"/>
                                                <?php else : ?>
                                                    <rect x="23" y="44" width="14" height="11" rx="2" fill="<?= $fc ?>"/>
                                                    <path d="M25 44 L25 40 Q30 36 35 40 L35 44" stroke="<?= $fc ?>" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                                                <?php endif; ?>
                                                <text x="30" y="22" text-anchor="middle" font-size="11" font-weight="bold" fill="<?= $fc ?>"><?= $i + 1 ?></text>
                                            </svg>
                                        </div>
                                        <div class="font-weight-bold mb-1">Pintu <?= $i + 1 ?></div>
                                        <?= $door_badge ?>
                                        <div class="mt-1"><?= $door_info ?></div>
                                        <?php if ($pb && isProses($pb['status'])) : ?>
                                            <div class="progress mt-2" style="height:6px">
                                                <div class="progress-bar bg-warning" style="width:<?= $bongkaran[$i]['progres'] ?? 0 ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $bongkaran[$i]['progres'] ?? 0 ?>% — <?= htmlspecialchars($bongkaran[$i]['nm_checker'] ?? '') ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div> -->

                <!-- ============================================================
                     FILTER TABS (Semua | Bongkaran | LK | KK)
                ============================================================ -->
                <ul class="nav nav-tabs mb-3" id="dashTab">
                    <li class="nav-item">
                        <a class="nav-link active" href="#tab-semua" data-toggle="tab">
                            <i class="fas fa-th-list mr-1"></i> Semua
                            <span class="badge badge-dark ml-1"><?= count($bongkaran) + count($list_lk) + count($list_kk) ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tab-bongkaran" data-toggle="tab">
                            <i class="fas fa-dolly mr-1"></i> Bongkaran
                            <span class="badge badge-warning ml-1"><?= count($bongkaran) ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tab-lk" data-toggle="tab">
                            <i class="fas fa-truck mr-1"></i> Loading LK
                            <span class="badge badge-info ml-1"><?= count($list_lk) ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tab-kk" data-toggle="tab">
                            <i class="fas fa-truck-loading mr-1"></i> Loading KK
                            <span class="badge badge-success ml-1"><?= count($list_kk) ?></span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- ============================================================
                         TAB SEMUA — tampilkan bongkaran + LK + KK sekaligus
                    ============================================================ -->
                    <div class="tab-pane fade show active" id="tab-semua">

                        <!-- Section: Bongkaran -->
                        <div class="semua-section-header bongkar">
                            <i class="fas fa-dolly mr-2"></i> Bongkaran
                            <span class="badge badge-warning ml-2"><?= count($bongkaran) ?></span>
                        </div>
                        <?php
                        $b_wait_s   = array_filter($bongkaran, fn($r) => isWait($r['status']));
                        $b_proses_s = array_filter($bongkaran, fn($r) => isProses($r['status']));
                        $b_done_s   = array_filter($bongkaran, fn($r) => isDone($r['status']));
                        ?>
                        <?= dashboardColumns($b_wait_s, $b_proses_s, $b_done_s, 'bongkar') ?>

                        <!-- Section: Loading LK -->
                        <div class="semua-section-header lk">
                            <i class="fas fa-truck mr-2"></i> Loading LK
                            <span class="badge badge-info ml-2"><?= count($list_lk) ?></span>
                        </div>
                        <?php
                        $lk_wait_s   = array_filter($list_lk, fn($r) => isWait($r['status']));
                        $lk_proses_s = array_filter($list_lk, fn($r) => isProses($r['status']));
                        $lk_done_s   = array_filter($list_lk, fn($r) => isDone($r['status']));
                        ?>
                        <?= dashboardColumns($lk_wait_s, $lk_proses_s, $lk_done_s, 'lk') ?>

                        <!-- Section: Loading KK -->
                        <div class="semua-section-header kk">
                            <i class="fas fa-truck-loading mr-2"></i> Loading KK
                            <span class="badge badge-success ml-2"><?= count($list_kk) ?></span>
                        </div>
                        <?php
                        $kk_wait_s   = array_filter($list_kk, fn($r) => isWait($r['status']));
                        $kk_proses_s = array_filter($list_kk, fn($r) => isProses($r['status']));
                        $kk_done_s   = array_filter($list_kk, fn($r) => isDone($r['status']));
                        ?>
                        <?= dashboardColumns($kk_wait_s, $kk_proses_s, $kk_done_s, 'kk') ?>

                    </div><!-- end tab-semua -->

                    <!-- ---- TAB BONGKARAN ---- -->
                    <div class="tab-pane fade" id="tab-bongkaran">
                        <?php
                        $b_wait   = array_filter($bongkaran, fn($r) => isWait($r['status']));
                        $b_proses = array_filter($bongkaran, fn($r) => isProses($r['status']));
                        $b_done   = array_filter($bongkaran, fn($r) => isDone($r['status']));
                        ?>
                        <div class="row">
                            <!-- Belum mulai -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-secondary text-white py-2">
                                        <h6 class="mb-0">
                                            <i class="fas fa-circle mr-1"></i> Belum Bongkar
                                            <span class="badge badge-light text-dark float-right"><?= count($b_wait) ?></span>
                                        </h6>
                                    </div>
                                    <div class="card-body p-2" style="min-height:120px">
                                        <?php if (empty($b_wait)) : ?>
                                            <p class="text-muted text-center mt-3 mb-0"><small>Tidak ada</small></p>
                                        <?php else : foreach ($b_wait as $b) : ?>
                                        <div class="card card-body p-2 mb-2 border-left-secondary">
                                            <div class="d-flex align-items-center">
                                                <svg width="28" height="20" viewBox="0 0 56 40" xmlns="http://www.w3.org/2000/svg" class="mr-2 flex-shrink-0">
                                                    <rect x="2" y="6" width="36" height="26" rx="3" fill="#dee2e6" stroke="#6c757d" stroke-width="2"/>
                                                    <rect x="38" y="12" width="16" height="20" rx="2" fill="#ced4da" stroke="#6c757d" stroke-width="2"/>
                                                    <path d="M38 12 L48 12 L54 20 L54 32 L38 32 Z" fill="#ced4da" stroke="#6c757d" stroke-width="2"/>
                                                    <circle cx="12" cy="34" r="4" fill="#495057"/>
                                                    <circle cx="44" cy="34" r="4" fill="#495057"/>
                                                    <circle cx="12" cy="34" r="2" fill="#dee2e6"/>
                                                    <circle cx="44" cy="34" r="2" fill="#dee2e6"/>
                                                </svg>
                                                <div style="min-width:0">
                                                    <div class="font-weight-bold text-truncate" style="font-size:13px"><?= htmlspecialchars($b['keterangan'] ?? $b['kode_bongkar']) ?></div>
                                                    <?php
                                                    $badge_map_b = [
                                                        'MENUNGGU'         => 'badge-secondary',
                                                        'PENYIAPAN_BARANG' => 'badge-primary',
                                                        'CETAK_DO'         => 'badge-info',
                                                    ];
                                                    $badge_cls_b = $badge_map_b[$b['status']] ?? 'badge-secondary';
                                                    ?>
                                                    <span class="badge <?= $badge_cls_b ?>"><?= statusLabel($b['status']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Proses bongkar -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-warning py-2">
                                        <h6 class="mb-0" style="color:#212529">
                                            <i class="fas fa-play-circle mr-1"></i> Proses Bongkar
                                            <span class="badge badge-dark float-right"><?= count($b_proses) ?></span>
                                        </h6>
                                    </div>
                                    <div class="card-body p-2" style="min-height:120px">
                                        <?php if (empty($b_proses)) : ?>
                                            <p class="text-muted text-center mt-3 mb-0"><small>Tidak ada</small></p>
                                        <?php else : foreach ($b_proses as $b) :
                                            $progres = (int)($b['progres'] ?? 0); ?>
                                        <div class="card card-body p-2 mb-2 border-left-warning">
                                            <div class="d-flex align-items-center mb-1">
                                                <svg width="28" height="20" viewBox="0 0 56 40" xmlns="http://www.w3.org/2000/svg" class="mr-2 flex-shrink-0">
                                                    <rect x="2" y="6" width="36" height="26" rx="3" fill="#fff3cd" stroke="#ffc107" stroke-width="2"/>
                                                    <rect x="38" y="12" width="16" height="20" rx="2" fill="#ffe69c" stroke="#ffc107" stroke-width="2"/>
                                                    <path d="M38 12 L48 12 L54 20 L54 32 L38 32 Z" fill="#ffe69c" stroke="#ffc107" stroke-width="2"/>
                                                    <circle cx="12" cy="34" r="4" fill="#664d03"/>
                                                    <circle cx="44" cy="34" r="4" fill="#664d03"/>
                                                    <circle cx="12" cy="34" r="2" fill="#fff3cd"/>
                                                    <circle cx="44" cy="34" r="2" fill="#fff3cd"/>
                                                </svg>
                                                <div style="min-width:0;flex:1">
                                                    <div class="font-weight-bold text-truncate" style="font-size:13px"><?= htmlspecialchars($b['keterangan'] ?? $b['kode_bongkar']) ?></div>
                                                    <?php
                                                    $dur_live = '';
                                                    if (!empty($b['waktu_mulai'])) {
                                                        $sel = time() - strtotime($b['waktu_mulai']);
                                                        if ($sel > 0) {
                                                            $j = floor($sel/3600); $m = floor(($sel%3600)/60);
                                                            $dur_live = $j > 0 ? "{$j}j {$m}m" : "{$m}m";
                                                        }
                                                    }
                                                    ?>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($b['nm_checker'] ?? '-') ?>
                                                        <?= !empty($b['waktu_mulai']) ? ' | mulai ' . date('H:i', strtotime($b['waktu_mulai'])) : '' ?>
                                                        <?= $dur_live ? ' <span class="badge badge-warning" style="font-size:12px">' . $dur_live . '</span>' : '' ?>
                                                    </small>
                                                </div>
                                                <span class="badge badge-warning ml-1" style="font-size:12px"><?= $progres ?>%</span>
                                            </div>
                                            <div class="progress" style="height:5px">
                                                <div class="progress-bar bg-warning" style="width:<?= $progres ?>%"></div>
                                            </div>
                                        </div>
                                        <?php endforeach; endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Done bongkar -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-success text-white py-2">
                                        <h6 class="mb-0">
                                            <i class="fas fa-check-circle mr-1"></i> Done Bongkar
                                            <span class="badge badge-light text-dark float-right"><?= count($b_done) ?></span>
                                        </h6>
                                    </div>
                                    <div class="card-body p-2" style="min-height:120px">
                                        <?php if (empty($b_done)) : ?>
                                            <p class="text-muted text-center mt-3 mb-0"><small>Tidak ada</small></p>
                                        <?php else : foreach ($b_done as $b) :
                                            $durasi = '';
                                            if (!empty($b['waktu_mulai']) && !empty($b['waktu_selesai'])) {
                                                $sel = strtotime($b['waktu_selesai']) - strtotime($b['waktu_mulai']);
                                                $j = floor($sel/3600); $m = floor(($sel%3600)/60);
                                                $durasi = $j > 0 ? "{$j}j {$m}m" : "{$m}m";
                                            }
                                        ?>
                                        <div class="card card-body p-2 mb-2 border-left-success">
                                            <div class="d-flex align-items-center">
                                                <svg width="28" height="20" viewBox="0 0 56 40" xmlns="http://www.w3.org/2000/svg" class="mr-2 flex-shrink-0">
                                                    <rect x="2" y="6" width="36" height="26" rx="3" fill="#d1e7dd" stroke="#198754" stroke-width="2"/>
                                                    <rect x="38" y="12" width="16" height="20" rx="2" fill="#a3cfbb" stroke="#198754" stroke-width="2"/>
                                                    <path d="M38 12 L48 12 L54 20 L54 32 L38 32 Z" fill="#a3cfbb" stroke="#198754" stroke-width="2"/>
                                                    <circle cx="12" cy="34" r="4" fill="#0a3622"/>
                                                    <circle cx="44" cy="34" r="4" fill="#0a3622"/>
                                                    <circle cx="12" cy="34" r="2" fill="#d1e7dd"/>
                                                    <circle cx="44" cy="34" r="2" fill="#d1e7dd"/>
                                                    <path d="M14 18 L18 22 L26 14" stroke="#198754" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                                                </svg>
                                                <div style="min-width:0">
                                                    <div class="font-weight-bold text-truncate" style="font-size:13px"><?= htmlspecialchars($b['keterangan'] ?? $b['kode_bongkar']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($b['nm_checker'] ?? '-') ?><?= $durasi ? ' · ' . $durasi : '' ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ---- TAB LOADING LK ---- -->
                    <div class="tab-pane fade" id="tab-lk">
                        <?php
                        $lk_wait   = array_filter($list_lk, fn($r) => isWait($r['status']));
                        $lk_proses = array_filter($list_lk, fn($r) => isProses($r['status']));
                        $lk_done   = array_filter($list_lk, fn($r) => isDone($r['status']));
                        ?>
                        <?= dashboardColumns($lk_wait, $lk_proses, $lk_done, 'lk') ?>
                    </div>

                    <!-- ---- TAB LOADING KK ---- -->
                    <div class="tab-pane fade" id="tab-kk">
                        <?php
                        $kk_wait   = array_filter($list_kk, fn($r) => isWait($r['status']));
                        $kk_proses = array_filter($list_kk, fn($r) => isProses($r['status']));
                        $kk_done   = array_filter($list_kk, fn($r) => isDone($r['status']));
                        ?>
                        <?= dashboardColumns($kk_wait, $kk_proses, $kk_done, 'kk') ?>
                    </div>

                </div><!-- end tab-content -->

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

<?php
// ---- Helper function kolom 3 ----
function dashboardColumns($wait, $proses, $done, $type)
{
    $color_wait  = ['bg' => '#dee2e6', 'stroke' => '#6c757d', 'dot' => '#495057', 'dotIn' => '#dee2e6', 'lbl' => '#6c757d'];
    $color_proses = [
        'bg' => '#fff3cd',
        'stroke' => '#856404',
        'dot' => '#856404',
        'dotIn' => '#fff3cd'
    ];
    $color_done  = ['bg' => '#d1e7dd', 'stroke' => '#198754', 'dot' => '#0a3622', 'dotIn' => '#d1e7dd'];
    $head_proses = 'bg-warning';
    $txt_proses  = $type === 'lk' ? 'text-white' : '';

    if ($type === 'bongkar') {
        $lbl_wait = 'Belum Bongkar';
        $lbl_p    = 'Proses Bongkar';
        $lbl_done = 'Done Bongkar';
    } elseif ($type === 'lk') {
        $lbl_wait = 'Belum Loading LK';
        $lbl_p    = 'Proses Loading LK';
        $lbl_done = 'Done Loading LK';
    } else { // kk
        $lbl_wait = 'Belum Loading KK';
        $lbl_p    = 'Proses Loading KK';
        $lbl_done = 'Done Loading KK';
    }

    ob_start();
    ?>
    <div class="row">
        <!-- Belum -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-secondary text-white py-2">
                    <h6 class="mb-0"><i class="fas fa-circle mr-1"></i> <?= $lbl_wait ?> <span class="badge badge-light text-dark float-right"><?= count($wait) ?></span></h6>
                </div>
                <div class="card-body p-2" style="min-height:120px">
                    <?php if (empty($wait)) : ?>
                        <p class="text-muted text-center mt-3 mb-0"><small>Tidak ada</small></p>
                    <?php else : foreach ($wait as $item) : ?>
                    <div class="card card-body p-2 mb-2 border-left-secondary">
                        <div class="d-flex align-items-center">
                            <?= trukIconSVG($type, $color_wait) ?>
                            <div style="min-width:0;margin-left:8px">
                                <div class="font-weight-bold text-truncate" style="font-size:13px"><?= htmlspecialchars($item['keterangan']) ?></div>
                                <?php
                                $badge_map = [
                                    'MENUNGGU'        => 'badge-secondary',
                                    'CETAK_DO'        => 'badge-info',
                                    'DO_SELESAI'      => 'badge-warning',
                                    'PENYIAPAN_BARANG'=> 'badge-primary',
                                ];
                                $badge_cls = $badge_map[$item['status']] ?? 'badge-secondary';
                                ?>
                                <span class="badge <?= $badge_cls ?>"><?= statusLabel($item['status']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <!-- Proses -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header <?= $head_proses ?> <?= $txt_proses ?> py-2" <?= !$txt_proses ? 'style="color:#212529"' : '' ?>>
                    <h6 class="mb-0"><i class="fas fa-play-circle mr-1"></i> <?= $lbl_p ?> <span class="badge badge-dark float-right"><?= count($proses) ?></span></h6>
                </div>
                <div class="card-body p-2" style="min-height:120px">
                    <?php if (empty($proses)) : ?>
                        <p class="text-muted text-center mt-3 mb-0"><small>Tidak ada</small></p>
                    <?php else : foreach ($proses as $item) :
                        $progres = (int)($item['progres'] ?? 0); ?>
                    <div class="card card-body p-2 mb-2 border-left-warning">
                        <div class="d-flex align-items-center mb-1">
                            <?= trukIconSVG($type, $color_proses) ?>
                            <div style="min-width:0;flex:1;margin-left:8px">
                                <div class="font-weight-bold text-truncate" style="font-size:13px"><?= htmlspecialchars($item['keterangan']) ?></div>
                                <?php
                                $durasi_live = '';
                                if (!empty($item['waktu_mulai'])) {
                                    $sel = time() - strtotime($item['waktu_mulai']);
                                    if ($sel > 0) {
                                        $j = floor($sel/3600); $m = floor(($sel%3600)/60);
                                        $durasi_live = $j > 0 ? "{$j}j {$m}m" : "{$m}m";
                                    }
                                }
                                ?>
                                <div style="font-size:14px; color:#212529; font-weight:500;">
                                    <?= htmlspecialchars($item['nm_checker'] ?? '-') ?>
                                    <?= !empty($item['waktu_mulai']) ? ' | mulai ' . date('H:i', strtotime($item['waktu_mulai'])) : '' ?>
                                    <?= $durasi_live ? ' <span class="badge badge-warning" style="font-size:12px">' . $durasi_live . '</span>' : '' ?>
                                </div>
                            </div>
                            <span class="badge badge-warning ml-1" style="font-size:12px"><?= $progres ?>%</span>
                        </div>
                        <div class="progress" style="height:5px">
                            <div class="progress-bar bg-warning" style="width:<?= $progres ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <!-- Done -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white py-2">
                    <h6 class="mb-0"><i class="fas fa-check-circle mr-1"></i> <?= $lbl_done ?> <span class="badge badge-light text-dark float-right"><?= count($done) ?></span></h6>
                </div>
                <div class="card-body p-2" style="min-height:120px">
                    <?php if (empty($done)) : ?>
                        <p class="text-muted text-center mt-3 mb-0"><small>Tidak ada</small></p>
                    <?php else : foreach ($done as $item) :
                        $durasi = '';
                        if (!empty($item['waktu_mulai']) && !empty($item['waktu_selesai'])) {
                            $sel = strtotime($item['waktu_selesai']) - strtotime($item['waktu_mulai']);
                            $j = floor($sel/3600); $m = floor(($sel%3600)/60);
                            $durasi = $j > 0 ? "{$j}j {$m}m" : "{$m}m";
                        }
                    ?>
                    <div class="card card-body p-2 mb-2 border-left-success">
                        <div class="d-flex align-items-center">
                            <?= trukIconSVG($type, $color_done) ?>
                            <div style="min-width:0;margin-left:8px">
                                <div class="font-weight-bold text-truncate" style="font-size:13px"><?= htmlspecialchars($item['keterangan']) ?></div>
                                <div style="margin-top:2px; line-height:1.4;">
                                    <!-- Nama checker + durasi -->
                                    <div style="font-size:14px; font-weight:400; color:#212529;">
                                        <?= htmlspecialchars($item['nm_checker'] ?? '-') ?>
                                        <?php if ($durasi): ?>
                                            <span class="badge badge-success ml-1" style="font-size:12px;">
                                                <?= $durasi ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Jam mulai & selesai -->
                                    <div style="font-size:13px; color:#212529;">
                                        <?= !empty($item['waktu_mulai']) ? 'Mulai: ' . date('H:i', strtotime($item['waktu_mulai'])) : '-' ?>
                                        <?= !empty($item['waktu_selesai']) ? ' | Selesai: ' . date('H:i', strtotime($item['waktu_selesai'])) : '' ?>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ---- Helper SVG truk ----
function trukIconSVG($type, $c)
{
    $lbl = strtoupper($type);
    return '<svg width="28" height="20" viewBox="0 0 56 40" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
        <rect x="2" y="6" width="36" height="26" rx="3" fill="' . $c['bg'] . '" stroke="' . $c['stroke'] . '" stroke-width="2"/>
        <rect x="38" y="12" width="16" height="20" rx="2" fill="' . $c['bg'] . '" stroke="' . $c['stroke'] . '" stroke-width="2"/>
        <path d="M38 12 L48 12 L54 20 L54 32 L38 32 Z" fill="' . $c['bg'] . '" stroke="' . $c['stroke'] . '" stroke-width="2"/>
        <circle cx="12" cy="34" r="4" fill="' . $c['dot'] . '"/>
        <circle cx="44" cy="34" r="4" fill="' . $c['dot'] . '"/>
        <circle cx="12" cy="34" r="2" fill="' . $c['dotIn'] . '"/>
        <circle cx="44" cy="34" r="2" fill="' . $c['dotIn'] . '"/>
        <text x="20" y="22" font-size="9" fill="' . $c['stroke'] . '" font-weight="bold" text-anchor="middle">' . $lbl . '</text>
    </svg>';
}
?>
</body>