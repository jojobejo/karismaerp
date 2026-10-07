<!-- views/content/logistik/checker/so_loading.php -->
<style>
    .route-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }
    .route-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-left: 4px solid #17a2b8;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        padding: 16px;
        transition: all 0.2s ease;
        text-decoration: none;
        color: #333;
        display: block;
    }
    .route-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-left-color: #117a8b;
        text-decoration: none;
        color: #333;
    }
    .route-code {
        font-size: 20px;
        font-weight: 700;
        color: #17a2b8;
        margin-bottom: 4px;
    }
    .route-name {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .route-badge {
        display: inline-block;
        background: #e2f0d9;
        color: #385723;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark" style="font-size:1.3rem;">
                            <i class="fas fa-tasks mr-2 text-info"></i>Checker Loading SO
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('logistik') ?>">Logistik</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('checker') ?>">Warehouse</a></li>
                            <li class="breadcrumb-item active">Checker Loading SO</li>
                        </ol>
                    </div>
                </div>

                <div class="mb-2">
                    <a href="<?= base_url('checker') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Activity Warehouse
                    </a>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php if (!empty($active_trips)): ?>
                <div class="card card-outline card-warning">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-truck mr-1"></i>Trip Pengiriman Aktif</h3></div>
                    <div class="card-body p-2">
                        <div class="row">
                        <?php foreach ($active_trips as $trip): ?>
                            <div class="col-md-6 col-lg-4 mb-2">
                                <div class="border rounded p-2 h-100">
                                    <?php
                                    if ($trip['status'] === 'PROSES_FAKTUR') {
                                        $trip_status_label = 'Proses Faktur';
                                        $trip_status_class = 'badge-info';
                                    } elseif ($trip['status'] === 'SIAP_BERANGKAT') {
                                        $trip_status_label = 'Siap Berangkat';
                                        $trip_status_class = 'badge-success';
                                    } else {
                                        $trip_status_label = str_replace('_', ' ', $trip['status']);
                                        $trip_status_class = 'badge-warning';
                                    }
                                    ?>
                                    <div class="d-flex justify-content-between"><b><?= htmlspecialchars($trip['kode_trip']) ?></b><span class="badge <?= $trip_status_class ?>"><?= htmlspecialchars($trip_status_label) ?></span></div>
                                    <small>Rute <?= htmlspecialchars($trip['kd_rute']) ?> · <?= date('d/m/Y', strtotime($trip['tgl_pengiriman'])) ?></small>
                                    <div class="mt-2">Sisa <b><?= number_format($trip['remaining_tonase'], 3, ',', '.') ?> ton</b> / <b><?= number_format($trip['remaining_kubikasi'], 4, ',', '.') ?> m³</b></div>
                                    <div class="mt-2">
                                        <i class="fas fa-truck text-primary mr-1"></i>
                                        No. Lambung: <b><?= htmlspecialchars($trip['nomor_lambung'] !== '' ? $trip['nomor_lambung'] : '-') ?></b>
                                        <?php if (!empty($trip['nomor_polisi'])): ?>
                                            <small class="text-muted ml-1">(<?= htmlspecialchars($trip['nomor_polisi']) ?>)</small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($trip['status'] === 'MENUNGGU_TAMBAHAN'): ?><small class="text-warning">Menunggu SO tambahan dari Sales</small><?php endif; ?>
                                    <?php if ((!empty($trip['has_do']) || $trip['status'] === 'SIAP_BERANGKAT' || (float)$trip['loaded_tonase'] == 0) && in_array($role, ['CHECKER','MANAGERCK','ADMLOG'], true)): ?>
                                    <div class="mt-2 text-right">
                                        <?php if ($trip['status'] !== 'MENUNGGU_TAMBAHAN' && (!empty($trip['has_do']) || $trip['status'] === 'SIAP_BERANGKAT')): ?>
                                        <button class="btn btn-warning btn-xs btn-trip-action" data-action="open" data-trip="<?= (int)$trip['id_trip'] ?>">Buka Tambahan</button>
                                        <?php endif; ?>
                                        <button class="btn btn-danger btn-xs btn-trip-action" data-action="close" data-trip="<?= (int)$trip['id_trip'] ?>">Tutup Trip</button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-route mr-1"></i>Pilih Rute Loading
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($routes)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 text-gray"></i>
                                <p class="mb-0">Tidak ada rute SO yang siap dimuat (loading) saat ini.</p>
                            </div>
                        <?php else: ?>
                            <div class="route-grid">
                                <?php foreach ($routes as $route):
                                    $activity = $route['activity'] ?? null;
                                    $is_started = $activity
                                        && in_array(($activity['status'] ?? ''), ['PROSES_LOADING', 'PENYIAPAN_BARANG'], true)
                                        && !empty($activity['waktu_mulai']);
                                    $is_loading_done = $activity && ($activity['status'] ?? '') === 'DONE';
                                    $detail_url = base_url('checker/so_loading/detail/' . rawurlencode($route['kd_rute']) . '?date=' . $route['tgl_transaksi']);
                                    $can_start = in_array($role, ['CHECKER', 'MANAGERCK', 'ADMLOG'], true);
                                ?>
                                    <div class="route-card">
                                        <div class="route-code"><?= htmlspecialchars($route['kd_rute']) ?></div>
                                        <div class="route-name" title="<?= htmlspecialchars($route['nama_rute']) ?>">
                                            <?= htmlspecialchars($route['nama_rute']) ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="route-badge">
                                                <i class="fas fa-file-invoice mr-1"></i><?= (int)$route['total_so'] ?> SO
                                            </span>
                                            <?php if ($is_loading_done): ?>
                                                <a href="<?= $detail_url ?>" class="badge badge-warning px-3 py-2" title="Lihat detail loading; menunggu proses faktur Admin SC.">
                                                    <i class="fas fa-file-invoice-dollar mr-1"></i>Proses Faktur
                                                </a>
                                            <?php elseif ($is_started): ?>
                                                <a href="<?= $detail_url ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-play-circle mr-1"></i>Lanjut Loading
                                                </a>
                                            <?php elseif ($can_start): ?>
                                                <button type="button"
                                                        class="btn btn-success btn-sm btn-start-route"
                                                        data-rute="<?= htmlspecialchars($route['kd_rute'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-url="<?= htmlspecialchars($detail_url, ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="fas fa-play mr-1"></i>Start
                                                </button>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Menunggu Checker Start</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
<script>
$(document).on('click', '.btn-trip-action', function() {
    var button = $(this), action = button.data('action');
    var question = action === 'open' ? 'Buka permintaan tambahan muatan untuk trip ini?' : 'Tutup trip ini dan tandai siap berangkat?';
    if (!confirm(question)) return;
    button.prop('disabled', true);
    $.ajax({
        url: action === 'open' ? '<?= base_url("checker/so_loading/open_additional") ?>' : '<?= base_url("checker/so_loading/close_trip") ?>',
        type: 'POST', dataType: 'json', data: {id_trip: button.data('trip')},
        success: function(response) {
            alert(response.message || 'Selesai.');
            if (response.status) location.reload(); else button.prop('disabled', false);
        },
        error: function() { alert('Terjadi kesalahan koneksi.'); button.prop('disabled', false); }
    });
});

$(document).on('click', '.btn-start-route', function() {
    var button = $(this);
    var route = button.data('rute');
    var detailUrl = button.data('url');

    if (!confirm('Mulai loading rute ' + route + '? Waktu mulai akan dicatat.')) return;

    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Memulai...');
    $.ajax({
        url: '<?= base_url("checker/so_loading/start") ?>',
        type: 'POST',
        dataType: 'json',
        data: { kd_rute: route },
        success: function(response) {
            if (response.status) {
                window.location.href = detailUrl;
                return;
            }
            alert(response.message || 'Gagal memulai loading.');
            button.prop('disabled', false).html('<i class="fas fa-play mr-1"></i>Start');
        },
        error: function() {
            alert('Terjadi kesalahan koneksi saat memulai loading.');
            button.prop('disabled', false).html('<i class="fas fa-play mr-1"></i>Start');
        }
    });
});
</script>
