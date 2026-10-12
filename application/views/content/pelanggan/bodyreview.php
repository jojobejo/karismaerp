<?php
// Perhitungan Metrik & Agregasi Statistik
$total_responden = count($dataplanggan);
$rating_counts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$monthly_stats = [];
$sum_nilai = 0;

$bulan_nama = [
    '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
    '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
    '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
];

foreach ($dataplanggan as $dp) {
    $score = (int)$dp->nilai_kp;
    if ($score >= 1 && $score <= 5) {
        $rating_counts[$score]++;
        $sum_nilai += $score;
    }

    $ym = substr($dp->tgl_input, 0, 7);
    if (!empty($ym)) {
        if (!isset($monthly_stats[$ym])) {
            $parts = explode('-', $ym);
            $lbl = (isset($bulan_nama[$parts[1]]) ? $bulan_nama[$parts[1]] : $parts[1]) . ' ' . $parts[0];
            $monthly_stats[$ym] = [
                'label' => $lbl,
                'count' => 0,
                'sum' => 0
            ];
        }
        $monthly_stats[$ym]['count']++;
        $monthly_stats[$ym]['sum'] += $score;
    }
}

ksort($monthly_stats);

$avg_score = $total_responden > 0 ? round($sum_nilai / $total_responden, 2) : 0;
$positive_count = $rating_counts[5] + $rating_counts[4];
$neutral_count = $rating_counts[3];
$negative_count = $rating_counts[2] + $rating_counts[1];

$csat_percent = $total_responden > 0 ? round(($positive_count / $total_responden) * 100, 1) : 0;
$neutral_percent = $total_responden > 0 ? round(($neutral_count / $total_responden) * 100, 1) : 0;
$negative_percent = $total_responden > 0 ? round(($negative_count / $total_responden) * 100, 1) : 0;

// Siapkan Data untuk Chart.js
$chart_months = [];
$chart_counts = [];
$chart_averages = [];
foreach ($monthly_stats as $ym => $st) {
    $chart_months[] = $st['label'];
    $chart_counts[] = $st['count'];
    $chart_averages[] = $st['count'] > 0 ? round($st['sum'] / $st['count'], 2) : 0;
}

// Urutkan data pelanggan agar review terbaru muncul di paling atas
$dataplanggan_sorted = array_reverse($dataplanggan);
?>

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
            <!-- Modern Page Header -->
            <div class="content-header pb-2">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-sm-7">
                            <div class="d-flex align-items-center">
                                <div class="header-icon-box mr-3">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.55rem; letter-spacing: -0.5px;">
                                        Indeks Penilaian Kepuasan Pelanggan (IKP)
                                    </h1>
                                    <p class="text-muted mb-0 small">
                                        Analisis real-time feedback kepuasan pelanggan, sentimen, dan tren penilaian
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 text-sm-right mt-3 mt-sm-0">
                            <div class="btn-group">
                                <a href="<?= base_url('kepuasan_pelanggan') ?>" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm mr-2" title="Buka Layanan Kuesioner">
                                    <i class="fas fa-external-link-alt mr-1"></i> Buka Kuesioner
                                </a>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" onclick="location.reload();" title="Refresh Halaman">
                                    <i class="fas fa-sync-alt mr-1"></i> Segarkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content section -->
            <section class="content">
                <div class="container-fluid">

                    <!-- Top 4 KPI / Stat Cards -->
                    <div class="row">
                        <!-- KPI 1: Skor Rata-Rata -->
                        <div class="col-lg-3 col-6">
                            <div class="kpi-card kpi-emerald shadow-sm">
                                <div class="kpi-body">
                                    <div class="kpi-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="kpi-content">
                                        <span class="kpi-title">Rata-Rata Skor</span>
                                        <div class="d-flex align-items-baseline">
                                            <h2 class="kpi-value mb-0"><?= number_format($avg_score, 2) ?></h2>
                                            <span class="text-muted ml-1 font-weight-bold small">/ 5.00</span>
                                        </div>
                                        <div class="kpi-stars mt-1">
                                            <?php
                                            $full_stars = floor($avg_score);
                                            $has_half = ($avg_score - $full_stars) >= 0.3;
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $full_stars) {
                                                    echo '<i class="fas fa-star text-warning"></i>';
                                                } elseif ($i == $full_stars + 1 && $has_half) {
                                                    echo '<i class="fas fa-star-half-alt text-warning"></i>';
                                                } else {
                                                    echo '<i class="far fa-star text-muted"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="kpi-footer text-success">
                                    <i class="fas fa-smile mr-1"></i>
                                    <strong><?= $avg_score >= 4.0 ? 'Sangat Baik (Puas)' : ($avg_score >= 3.0 ? 'Cukup Baik' : 'Perlu Peningkatan') ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 2: Total Responden -->
                        <div class="col-lg-3 col-6">
                            <div class="kpi-card kpi-blue shadow-sm">
                                <div class="kpi-body">
                                    <div class="kpi-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="kpi-content">
                                        <span class="kpi-title">Total Responden</span>
                                        <h2 class="kpi-value mb-0"><?= number_format($total_responden) ?></h2>
                                        <span class="text-muted small">Total feedback masuk</span>
                                    </div>
                                </div>
                                <div class="kpi-footer text-info">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <span>100% Tercatat Otomatis</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 3: CSAT Positive Rate -->
                        <div class="col-lg-3 col-6">
                            <div class="kpi-card kpi-purple shadow-sm">
                                <div class="kpi-body">
                                    <div class="kpi-icon">
                                        <i class="fas fa-thumbs-up"></i>
                                    </div>
                                    <div class="kpi-content">
                                        <span class="kpi-title">Tingkat Kepuasan</span>
                                        <div class="d-flex align-items-baseline">
                                            <h2 class="kpi-value mb-0"><?= $csat_percent ?>%</h2>
                                        </div>
                                        <span class="text-muted small"><?= $positive_count ?> dari <?= $total_responden ?> Puas</span>
                                    </div>
                                </div>
                                <div class="kpi-footer text-purple">
                                    <div class="progress" style="height: 6px; border-radius: 4px; background: #e9ecef;">
                                        <div class="progress-bar bg-purple" role="progressbar" style="width: <?= $csat_percent ?>%" aria-valuenow="<?= $csat_percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 4: Nilai Tertinggi -->
                        <div class="col-lg-3 col-6">
                            <div class="kpi-card kpi-amber shadow-sm">
                                <div class="kpi-body">
                                    <div class="kpi-icon">
                                        <i class="fas fa-award"></i>
                                    </div>
                                    <div class="kpi-content">
                                        <span class="kpi-title">Rating Bintang 5</span>
                                        <div class="d-flex align-items-baseline">
                                            <h2 class="kpi-value mb-0"><?= number_format($rating_counts[5]) ?></h2>
                                            <span class="text-muted ml-1 small">(<?= $total_responden > 0 ? round(($rating_counts[5] / $total_responden) * 100, 1) : 0 ?>%)</span>
                                        </div>
                                        <span class="text-muted small">Suara bintang maksimal</span>
                                    </div>
                                </div>
                                <div class="kpi-footer text-warning">
                                    <i class="fas fa-crown mr-1"></i>
                                    <span>Kategori Terpopuler</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Analytics Row (Charts & Interactive Rating Breakdown) -->
                    <div class="row">
                        <!-- Chart 1: Donut Chart & Rating Progress Bars -->
                        <div class="col-lg-5 col-md-12 mb-4">
                            <div class="card modern-card shadow-sm h-100">
                                <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                                    <h5 class="card-title font-weight-bold text-dark mb-0">
                                        <i class="fas fa-chart-pie mr-2 text-primary"></i>Distribusi Rating
                                    </h5>
                                    <span class="badge badge-light border text-muted px-2 py-1 small">
                                        Total <?= $total_responden ?> Suara
                                    </span>
                                </div>
                                <div class="card-body">
                                    <!-- Donut Chart Container -->
                                    <div style="position: relative; height: 190px;" class="mb-3">
                                        <canvas id="chartRatingDonut"></canvas>
                                    </div>

                                    <!-- Interactive Breakdown Rows (Clickable to filter table!) -->
                                    <div class="rating-breakdown-list">
                                        <?php
                                        $ratings_meta = [
                                            5 => ['label' => 'Sangat Puas', 'color' => '#10b981', 'bar' => 'bg-success', 'emoji' => 'fas fa-laugh-beam'],
                                            4 => ['label' => 'Puas', 'color' => '#3b82f6', 'bar' => 'bg-primary', 'emoji' => 'fas fa-smile'],
                                            3 => ['label' => 'Cukup', 'color' => '#f59e0b', 'bar' => 'bg-warning', 'emoji' => 'fas fa-meh'],
                                            2 => ['label' => 'Kurang Puas', 'color' => '#f97316', 'bar' => 'bg-orange', 'emoji' => 'fas fa-frown'],
                                            1 => ['label' => 'Tidak Puas', 'color' => '#ef4444', 'bar' => 'bg-danger', 'emoji' => 'fas fa-angry'],
                                        ];

                                        foreach ($ratings_meta as $r => $meta) :
                                            $count = $rating_counts[$r];
                                            $pct = $total_responden > 0 ? round(($count / $total_responden) * 100, 1) : 0;
                                        ?>
                                            <div class="breakdown-item mb-2" onclick="filterByRating(<?= $r ?>)" title="Klik untuk filter data rating <?= $r ?>">
                                                <div class="d-flex justify-content-between align-items-center mb-1 small">
                                                    <span class="font-weight-600">
                                                        <i class="<?= $meta['emoji'] ?> mr-1" style="color: <?= $meta['color'] ?>;"></i>
                                                        <strong><?= $r ?> ★</strong> (<?= $meta['label'] ?>)
                                                    </span>
                                                    <span class="text-muted">
                                                        <strong><?= $count ?></strong> <small class="text-secondary">(<?= $pct ?>%)</small>
                                                    </span>
                                                </div>
                                                <div class="progress progress-xs" style="height: 7px; border-radius: 5px; background: #eef2f5;">
                                                    <div class="progress-bar <?= $meta['bar'] ?>" role="progressbar" style="width: <?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="text-center mt-3">
                                        <small class="text-muted font-italic">
                                            <i class="fas fa-mouse-pointer mr-1"></i> Klik baris atau chart untuk memfilter tabel
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chart 2: Monthly Trends & Sentiment Summary -->
                        <div class="col-lg-7 col-md-12 mb-4">
                            <div class="card modern-card shadow-sm h-100">
                                <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                                    <h5 class="card-title font-weight-bold text-dark mb-0">
                                        <i class="fas fa-chart-bar mr-2 text-info"></i>Tren Penilaian & Responden Bulanan
                                    </h5>
                                    <div class="card-tools">
                                        <span class="badge badge-info-soft px-2 py-1">
                                            <i class="far fa-calendar mr-1"></i> Riwayat 2026
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Monthly Chart Container -->
                                    <div style="position: relative; height: 260px;" class="mb-3">
                                        <canvas id="chartMonthlyTrend"></canvas>
                                    </div>

                                    <!-- Sentiment Summary Pills -->
                                    <div class="sentiment-summary-box p-3 rounded-lg mt-2">
                                        <div class="row text-center">
                                            <div class="col-4 border-right">
                                                <div class="text-success font-weight-bold mb-1">
                                                    <i class="fas fa-smile-beam mr-1"></i> Positif
                                                </div>
                                                <h5 class="mb-0 font-weight-bold text-dark"><?= $csat_percent ?>%</h5>
                                                <small class="text-muted"><?= $positive_count ?> Suara (4 & 5 ★)</small>
                                            </div>
                                            <div class="col-4 border-right">
                                                <div class="text-warning font-weight-bold mb-1">
                                                    <i class="fas fa-meh mr-1"></i> Netral
                                                </div>
                                                <h5 class="mb-0 font-weight-bold text-dark"><?= $neutral_percent ?>%</h5>
                                                <small class="text-muted"><?= $neutral_count ?> Suara (3 ★)</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-danger font-weight-bold mb-1">
                                                    <i class="fas fa-frown-open mr-1"></i> Negatif
                                                </div>
                                                <h5 class="mb-0 font-weight-bold text-dark"><?= $negative_percent ?>%</h5>
                                                <small class="text-muted"><?= $negative_count ?> Suara (1 & 2 ★)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Data Table Card with Interactive Filters -->
                    <div class="card modern-card shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom pt-3 pb-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title font-weight-bold text-dark mb-0">
                                        <i class="fas fa-list-alt text-primary mr-2"></i>Daftar Riwayat Penilaian Pelanggan
                                    </h4>
                                    <p class="text-muted mb-0 small mt-1">Rincian log masuk kepuasan pelanggan secara kronologis</p>
                                </div>
                                <div class="mt-2 mt-sm-0" id="tableActionButtons">
                                    <!-- Buttons container will be injected here or styled via DataTables -->
                                </div>
                            </div>

                            <!-- Interactive Filter Toolbar -->
                            <div class="filter-toolbar mt-3 pt-3 border-top">
                                <div class="row align-items-center">
                                    <div class="col-lg-8 col-12 mb-2 mb-lg-0">
                                        <label class="text-muted small font-weight-bold mr-2 mb-1 d-block d-sm-inline">
                                            <i class="fas fa-filter text-primary mr-1"></i> Filter Rating:
                                        </label>
                                        <div class="btn-group btn-group-sm flex-wrap rating-filter-group" role="group">
                                            <button type="button" class="btn btn-outline-primary active filter-pill" data-rating="all" onclick="filterByRating('all')">
                                                Semua <span class="badge badge-light ml-1"><?= $total_responden ?></span>
                                            </button>
                                            <button type="button" class="btn btn-outline-success filter-pill" data-rating="5" onclick="filterByRating(5)">
                                                <i class="fas fa-star text-warning"></i> 5 <span class="badge badge-light ml-1"><?= $rating_counts[5] ?></span>
                                            </button>
                                            <button type="button" class="btn btn-outline-info filter-pill" data-rating="4" onclick="filterByRating(4)">
                                                <i class="fas fa-star text-warning"></i> 4 <span class="badge badge-light ml-1"><?= $rating_counts[4] ?></span>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning filter-pill" data-rating="3" onclick="filterByRating(3)">
                                                <i class="fas fa-star text-warning"></i> 3 <span class="badge badge-light ml-1"><?= $rating_counts[3] ?></span>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary filter-pill" data-rating="2" onclick="filterByRating(2)">
                                                <i class="fas fa-star text-warning"></i> 2 <span class="badge badge-light ml-1"><?= $rating_counts[2] ?></span>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger filter-pill" data-rating="1" onclick="filterByRating(1)">
                                                <i class="fas fa-star text-warning"></i> 1 <span class="badge badge-light ml-1"><?= $rating_counts[1] ?></span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-12 text-lg-right">
                                        <div class="d-inline-flex align-items-center">
                                            <button type="button" id="btnResetFilter" class="btn btn-sm btn-light border text-secondary rounded-pill px-3 shadow-sm" onclick="resetFilters()">
                                                <i class="fas fa-undo-alt mr-1"></i> Reset Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Filter Notice Banner -->
                        <div id="activeFilterNotice" class="alert alert-info py-2 px-3 m-3 mb-0 rounded-lg d-none align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-info-circle mr-2"></i>
                                <span id="filterNoticeText">Filter aktif: Rating 5</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-info p-0 font-weight-bold" onclick="resetFilters()">
                                Tampilkan Semua <i class="fas fa-times ml-1"></i>
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tb_penilaian" class="table table-hover table-striped border align-middle w-100">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">#</th>
                                            <th>Tanggal Penilaian</th>
                                            <th>Waktu (Jam)</th>
                                            <th>Tingkat Kepuasan</th>
                                            <th>Skor Rating</th>
                                            <th class="text-center">Sentimen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($dataplanggan_sorted as $dp) :
                                            $val = (int)$dp->nilai_kp;
                                            if ($val == 5) {
                                                $badge_class = 'badge-success-soft';
                                                $label_text  = 'Sangat Puas';
                                                $emoji_icon  = 'fas fa-laugh-beam text-success';
                                                $sentiment_badge = '<span class="badge badge-pill badge-success"><i class="fas fa-thumbs-up mr-1"></i> Positif</span>';
                                            } elseif ($val == 4) {
                                                $badge_class = 'badge-info-soft';
                                                $label_text  = 'Puas';
                                                $emoji_icon  = 'fas fa-smile text-info';
                                                $sentiment_badge = '<span class="badge badge-pill badge-info"><i class="fas fa-thumbs-up mr-1"></i> Positif</span>';
                                            } elseif ($val == 3) {
                                                $badge_class = 'badge-warning-soft';
                                                $label_text  = 'Cukup';
                                                $emoji_icon  = 'fas fa-meh text-warning';
                                                $sentiment_badge = '<span class="badge badge-pill badge-warning text-white"><i class="fas fa-minus mr-1"></i> Netral</span>';
                                            } elseif ($val == 2) {
                                                $badge_class = 'badge-orange-soft';
                                                $label_text  = 'Kurang Puas';
                                                $emoji_icon  = 'fas fa-frown text-orange';
                                                $sentiment_badge = '<span class="badge badge-pill badge-orange text-white"><i class="fas fa-thumbs-down mr-1"></i> Negatif</span>';
                                            } else {
                                                $badge_class = 'badge-danger-soft';
                                                $label_text  = 'Sangat Tidak Puas';
                                                $emoji_icon  = 'fas fa-angry text-danger';
                                                $sentiment_badge = '<span class="badge badge-pill badge-danger"><i class="fas fa-thumbs-down mr-1"></i> Negatif</span>';
                                            }
                                        ?>
                                            <tr data-rating="<?= $val ?>" data-date="<?= $dp->tgl_input ?>">
                                                <td class="text-center font-weight-bold text-muted"><?= $no++ ?></td>
                                                <td>
                                                    <i class="far fa-calendar-alt text-primary mr-2"></i>
                                                    <span class="font-weight-600 text-dark"><?= format_indo($dp->tgl_input) ?></span>
                                                </td>
                                                <td>
                                                    <span class="time-badge">
                                                        <i class="far fa-clock text-secondary mr-1"></i> <?= !empty($dp->jam_input) ? $dp->jam_input . ' WIB' : '-' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="satisfaction-badge <?= $badge_class ?>">
                                                        <i class="<?= $emoji_icon ?> mr-1"></i> <?= $label_text ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="stars-display mr-2">
                                                            <?php for ($s = 1; $s <= 5; $s++) : ?>
                                                                <?php if ($s <= $val) : ?>
                                                                    <i class="fas fa-star text-warning"></i>
                                                                <?php else : ?>
                                                                    <i class="far fa-star text-muted" style="opacity: 0.3;"></i>
                                                                <?php endif; ?>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <span class="badge badge-light border font-weight-bold text-dark px-2">
                                                            <?= $val ?> / 5
                                                        </span>
                                                        <span class="d-none search-rating"><?= $val ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <?= $sentiment_badge ?>
                                                </td>
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
            <strong>Copyright &copy; <?= date('Y') ?> <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 2.0 (Modernized)
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
        </aside>
    </div>
    <!-- ./wrapper -->

    <!-- Load Chart.js (v2.9.4 bundle yang tersedia di project) -->
    <script src="<?= base_url('assets/plugins/chart.js/Chart.bundle.min.js') ?>"></script>

    <!-- Custom Modern Styling -->
    <style>
        /* Card & Layout Enhancements */
        .modern-card {
            border: none;
            border-radius: 14px;
            background: #ffffff;
            transition: all 0.3s ease;
        }

        .header-icon-box {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: linear-gradient(135deg, #005AA6 0%, #0088ff 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 4px 14px rgba(0, 90, 166, 0.3);
        }

        /* KPI Cards */
        .kpi-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.09) !important;
        }

        .kpi-body {
            display: flex;
            align-items: center;
        }

        .kpi-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .kpi-emerald .kpi-icon {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
        }
        .kpi-emerald::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .kpi-blue .kpi-icon {
            background: rgba(0, 123, 255, 0.12);
            color: #007bff;
        }
        .kpi-blue::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #38bdf8);
        }

        .kpi-purple .kpi-icon {
            background: rgba(139, 92, 246, 0.12);
            color: #8b5cf6;
        }
        .kpi-purple::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #8b5cf6, #c084fc);
        }

        .kpi-amber .kpi-icon {
            background: rgba(245, 158, 11, 0.12);
            color: #f59e0b;
        }
        .kpi-amber::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
        }

        .kpi-title {
            font-size: 0.82rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.6px;
            color: #6c757d;
            display: block;
            margin-bottom: 2px;
        }

        .kpi-value {
            font-size: 1.85rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.1;
        }

        .kpi-footer {
            margin-top: 0.85rem;
            padding-top: 0.75rem;
            border-top: 1px dashed #e9ecef;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
        }

        .bg-purple {
            background-color: #8b5cf6 !important;
        }

        .text-purple {
            color: #8b5cf6 !important;
        }

        .bg-orange {
            background-color: #f97316 !important;
        }

        .text-orange {
            color: #f97316 !important;
        }

        /* Interactive Rating Breakdown List */
        .breakdown-item {
            cursor: pointer;
            padding: 7px 10px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .breakdown-item:hover {
            background-color: #f8fafc;
            transform: translateX(4px);
        }

        /* Sentiment Summary Box */
        .sentiment-summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        /* Filter Pills */
        .filter-pill {
            border-radius: 50px !important;
            margin-right: 6px;
            margin-bottom: 6px;
            padding: 5px 14px;
            font-weight: 600;
            font-size: 0.84rem;
            transition: all 0.2s ease;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
        }

        .filter-pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .filter-pill.active {
            color: #ffffff !important;
            border-color: transparent !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        .filter-pill[data-rating="all"].active { background: #005AA6 !important; }
        .filter-pill[data-rating="5"].active { background: #10b981 !important; }
        .filter-pill[data-rating="4"].active { background: #3b82f6 !important; }
        .filter-pill[data-rating="3"].active { background: #f59e0b !important; }
        .filter-pill[data-rating="2"].active { background: #f97316 !important; }
        .filter-pill[data-rating="1"].active { background: #ef4444 !important; }

        .filter-pill.active .badge {
            background-color: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }

        /* Badges & Tables */
        .satisfaction-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .badge-success-soft {
            background-color: rgba(16, 185, 129, 0.14);
            color: #065f46;
        }

        .badge-info-soft {
            background-color: rgba(59, 130, 246, 0.14);
            color: #1e40af;
        }

        .badge-warning-soft {
            background-color: rgba(245, 158, 11, 0.15);
            color: #92400e;
        }

        .badge-orange-soft {
            background-color: rgba(249, 115, 22, 0.15);
            color: #9a3412;
        }

        .badge-danger-soft {
            background-color: rgba(239, 68, 68, 0.14);
            color: #991b1b;
        }

        .time-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 500;
        }

        #tb_penilaian thead th {
            border-top: none;
            text-transform: uppercase;
            font-size: 0.76rem;
            letter-spacing: 0.5px;
            color: #475569;
            font-weight: 700;
            padding: 12px 14px;
        }

        #tb_penilaian tbody td {
            vertical-align: middle;
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        #tb_penilaian tbody tr:hover {
            background-color: #f8fafc;
        }

        .dt-buttons .btn {
            border-radius: 6px !important;
            margin-right: 4px;
            font-size: 0.82rem;
            padding: 5px 12px;
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #334155 !important;
            font-weight: 500;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .dt-buttons .btn:hover {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
        }
    </style>

    <!-- Interactive Scripts: DataTables & Chart.js -->
    <script>
        var ratingTable = null;
        var currentFilterRating = 'all';

        // Inisialisasi setelah semua script footer (termasuk DataTables) selesai dimuat
        window.addEventListener('load', function() {
            initDataTable();
            initCharts();
        });

        function initDataTable() {
            if (typeof $.fn.DataTable !== 'undefined') {
                // Pasang filter rating ke dataTable
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'tb_penilaian') {
                        return true;
                    }
                    if (currentFilterRating === 'all') {
                        return true;
                    }
                    var rowNode = ratingTable ? ratingTable.row(dataIndex).node() : null;
                    if (!rowNode) return true;
                    var rowRating = $(rowNode).attr('data-rating');
                    return String(rowRating) === String(currentFilterRating);
                });

                ratingTable = $('#tb_penilaian').DataTable({
                    responsive: true,
                    lengthChange: true,
                    autoWidth: false,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                    order: [[0, "asc"]],
                    dom: "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6'<'d-flex justify-content-sm-end align-items-center'Bf>>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row align-items-center mt-3'<'col-sm-5'i><'col-sm-7'p>>",
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fas fa-file-excel mr-1 text-success"></i> Excel',
                            className: 'btn btn-outline-secondary btn-sm shadow-sm',
                            title: 'Data_Indeks_Kepuasan_Pelanggan'
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fas fa-file-pdf mr-1 text-danger"></i> PDF',
                            className: 'btn btn-outline-secondary btn-sm shadow-sm',
                            title: 'Data_Indeks_Kepuasan_Pelanggan'
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print mr-1 text-info"></i> Cetak',
                            className: 'btn btn-outline-secondary btn-sm shadow-sm'
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="fas fa-columns mr-1 text-secondary"></i> Kolom',
                            className: 'btn btn-outline-secondary btn-sm shadow-sm'
                        }
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Cari riwayat penilaian...",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                        infoFiltered: "(disaring dari total _MAX_ entri)",
                        zeroRecords: "Tidak ditemukan data penilaian yang cocok",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            next: '<i class="fas fa-chevron-right"></i>',
                            previous: '<i class="fas fa-chevron-left"></i>'
                        }
                    }
                });

                // Style search input to look modern
                $('.dataTables_filter input').addClass('form-control form-control-sm rounded-pill px-3 shadow-none border');
                $('.dataTables_length select').addClass('form-control form-control-sm rounded shadow-none border');
            }
        }

        // Filter tabel berdasarkan skor rating
        function filterByRating(rating) {
            currentFilterRating = rating;

            // Update status tombol filter
            $('.filter-pill').removeClass('active');
            if (rating === 'all') {
                $('.filter-pill[data-rating="all"]').addClass('active');
                $('#activeFilterNotice').addClass('d-none').removeClass('d-flex');
            } else {
                $('.filter-pill[data-rating="' + rating + '"]').addClass('active');
                var label = rating === 5 ? 'Sangat Puas' :
                            rating === 4 ? 'Puas' :
                            rating === 3 ? 'Cukup' :
                            rating === 2 ? 'Kurang Puas' : 'Tidak Puas';
                $('#filterNoticeText').html('<strong>Filter Aktif:</strong> Menampilkan rating <strong>' + rating + ' ★ (' + label + ')</strong>');
                $('#activeFilterNotice').removeClass('d-none').addClass('d-flex');
            }

            if (ratingTable) {
                ratingTable.draw();

                // Smooth scroll ke tabel jika dari klik chart/breakdown
                $('html, body').animate({
                    scrollTop: $("#tb_penilaian").offset().top - 180
                }, 400);
            }
        }

        // Reset semua filter
        function resetFilters() {
            filterByRating('all');
        }

        // Inisialisasi Chart.js Visualizations
        function initCharts() {
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js library is not available');
                return;
            }

            // 1. Donut Chart - Distribusi Rating
            var ctxDonut = document.getElementById('chartRatingDonut');
            if (ctxDonut) {
                var ratingDonut = new Chart(ctxDonut.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['5 ★ Sangat Puas', '4 ★ Puas', '3 ★ Cukup', '2 ★ Kurang', '1 ★ Tidak Puas'],
                        datasets: [{
                            data: [
                                <?= $rating_counts[5] ?>,
                                <?= $rating_counts[4] ?>,
                                <?= $rating_counts[3] ?>,
                                <?= $rating_counts[2] ?>,
                                <?= $rating_counts[1] ?>
                            ],
                            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#f97316', '#ef4444'],
                            hoverBackgroundColor: ['#059669', '#2563eb', '#d97706', '#ea580c', '#dc2626'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        cutoutPercentage: 68,
                        legend: {
                            display: false
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var idx = tooltipItem.index;
                                    var count = data.datasets[0].data[idx];
                                    var total = <?= $total_responden > 0 ? $total_responden : 1 ?>;
                                    var pct = ((count / total) * 100).toFixed(1);
                                    return ' ' + data.labels[idx] + ': ' + count + ' suara (' + pct + '%)';
                                }
                            }
                        },
                        onClick: function(evt, elements) {
                            if (elements && elements.length > 0) {
                                var clickedIdx = elements[0]._index;
                                var mappedRatings = [5, 4, 3, 2, 1];
                                filterByRating(mappedRatings[clickedIdx]);
                            }
                        }
                    }
                });
            }

            // 2. Bar & Line Combo Chart - Tren Bulanan
            var ctxTrend = document.getElementById('chartMonthlyTrend');
            if (ctxTrend) {
                var trendChart = new Chart(ctxTrend.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($chart_months) ?>,
                        datasets: [
                            {
                                type: 'line',
                                label: 'Rata-rata Skor (1-5)',
                                data: <?= json_encode($chart_averages) ?>,
                                yAxisID: 'y-score',
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 3,
                                fill: false,
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7
                            },
                            {
                                type: 'bar',
                                label: 'Jumlah Responden',
                                data: <?= json_encode($chart_counts) ?>,
                                yAxisID: 'y-count',
                                backgroundColor: 'rgba(59, 130, 246, 0.75)',
                                hoverBackgroundColor: '#2563eb',
                                borderRadius: 6,
                                barPercentage: 0.45
                            }
                        ]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        tooltips: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false
                                }
                            }],
                            yAxes: [
                                {
                                    id: 'y-score',
                                    position: 'right',
                                    ticks: {
                                        min: 1,
                                        max: 5,
                                        stepSize: 1,
                                        callback: function(value) {
                                            return value + ' ★';
                                        }
                                    },
                                    gridLines: {
                                        drawOnChartArea: false
                                    }
                                },
                                {
                                    id: 'y-count',
                                    position: 'left',
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(value) {
                                            if (Number.isInteger(value)) {
                                                return value;
                                            }
                                        }
                                    },
                                    gridLines: {
                                        color: '#f1f5f9'
                                    }
                                }
                            ]
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    }
                });
            }
        }
    </script>