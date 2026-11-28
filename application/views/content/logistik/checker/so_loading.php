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
        border: 1px solid #e2e8f0;
        border-top: 4px solid #17a2b8;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
        padding: 16px;
        transition: all 0.2s ease;
        color: #1e293b;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .route-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.1);
        border-top-color: #117a8b;
    }
    .route-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
    }
    .route-code {
        font-size: 20px;
        font-weight: 800;
        color: #0f766e;
        line-height: 1.2;
    }
    .route-name {
        font-size: 13px;
        color: #64748b;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .route-badge {
        display: inline-flex;
        align-items: center;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .route-checker-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 12px;
    }
    .route-checker-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        margin-bottom: 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .route-checker-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
    }
    .route-checker-pill.assigned {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .route-checker-pill.unassigned {
        background: #fef3c7;
        color: #b45309;
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

                <div class="mb-2 d-flex flex-wrap align-items-center" style="gap: 8px;">
                    <a href="<?= base_url('checker') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Activity Warehouse
                    </a>
                    <a href="<?= base_url('checker/so_loading/riwayat') ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-history mr-1"></i>Riwayat Loading SO
                    </a>
                    <?php if (!empty($is_checker_coordinator)): ?>
                    <button type="button" class="btn btn-warning btn-sm font-weight-bold" data-toggle="modal" data-target="#modalKelolaChecker">
                        <i class="fas fa-users-cog mr-1"></i>Kelola Daftar Checker
                    </button>
                    <?php endif; ?>
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
                                    <?php if ((!empty($trip['has_do']) || $trip['status'] === 'SIAP_BERANGKAT' || (float)$trip['loaded_tonase'] == 0) && in_array($role, ['CHECKER','MANAGERCK','MANAGERWH','ADMLOG'], true)): ?>
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
                                    $assigned_nik  = trim((string)($activity['nik_checker'] ?? ''));
                                    $assigned_nama = trim((string)($activity['nm_checker'] ?? ''));
                                    $is_coord      = !empty($is_checker_coordinator);
                                    $can_start     = ($assigned_nik !== '' && ($assigned_nik === ($nik ?? '') || $is_coord));
                                ?>
                                    <div class="route-card">
                                        <div>
                                            <div class="route-card-header">
                                                <div style="min-width: 0;">
                                                    <div class="route-code"><?= htmlspecialchars($route['kd_rute']) ?></div>
                                                    <div class="route-name" title="<?= htmlspecialchars($route['nama_rute']) ?>">
                                                        <?= htmlspecialchars($route['nama_rute']) ?>
                                                    </div>
                                                </div>
                                                <span class="route-badge">
                                                    <i class="fas fa-file-invoice mr-1"></i><?= (int)$route['total_so'] ?> SO
                                                </span>
                                            </div>

                                            <div class="route-checker-box">
                                                <div class="route-checker-label">
                                                    <span>Checker Loading</span>
                                                    <?php if ($assigned_nik !== ''): ?>
                                                        <span class="route-checker-pill assigned">
                                                            <i class="fas fa-user-check"></i> <?= htmlspecialchars($assigned_nama ?: $assigned_nik) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="route-checker-pill unassigned">
                                                            <i class="fas fa-clock"></i> Belum Ditentukan
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if ($is_coord && !$is_loading_done): ?>
                                                    <div class="input-group input-group-sm mt-2">
                                                        <select class="form-control select-route-checker">
                                                            <option value="">-- Pilih Checker --</option>
                                                            <?php foreach (($list_checker ?? []) as $ck): ?>
                                                                <option value="<?= htmlspecialchars($ck['nik']) ?>" <?= $assigned_nik === $ck['nik'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($ck['nm_karyawan']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <div class="input-group-append">
                                                            <button type="button"
                                                                    class="btn <?= $assigned_nik !== '' ? 'btn-outline-primary' : 'btn-primary' ?> font-weight-bold btn-assign-route-checker"
                                                                    data-rute="<?= htmlspecialchars($route['kd_rute'], ENT_QUOTES, 'UTF-8') ?>"
                                                                    data-trip="<?= (int)($route['id_trip'] ?? 0) ?>">
                                                                <?= $assigned_nik !== '' ? 'Ubah' : 'Tetapkan' ?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-end" style="gap: 6px;">
                                            <?php if ($is_loading_done): ?>
                                                <a href="<?= $detail_url ?>" class="btn btn-warning btn-sm btn-block font-weight-bold" title="Lihat detail loading; menunggu proses faktur Admin SC.">
                                                    <i class="fas fa-file-invoice-dollar mr-1"></i>Proses Faktur
                                                </a>
                                            <?php elseif ($is_started): ?>
                                                <a href="<?= $detail_url ?>" class="btn btn-info btn-sm flex-fill font-weight-bold">
                                                    <i class="fas fa-play-circle mr-1"></i>Lanjut Loading
                                                </a>
                                                <?php if ($can_start): ?>
                                                <button type="button" class="btn btn-outline-danger btn-sm btn-cancel-start-route" data-rute="<?= htmlspecialchars($route['kd_rute'], ENT_QUOTES, 'UTF-8') ?>" title="Batalkan start loading">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                                <?php endif; ?>
                                            <?php elseif ($can_start): ?>
                                                <button type="button"
                                                        class="btn btn-success btn-sm btn-block font-weight-bold btn-start-route"
                                                        data-rute="<?= htmlspecialchars($route['kd_rute'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-trip="<?= (int)($route['id_trip'] ?? 0) ?>"
                                                        data-url="<?= htmlspecialchars($detail_url, ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="fas fa-play mr-1"></i>Start Loading
                                                </button>
                                            <?php elseif ($assigned_nik === ''): ?>
                                                <span class="text-muted small font-italic w-100 text-center">
                                                    <i class="fas fa-info-circle mr-1"></i>Tetapkan checker untuk mulai loading
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small w-100 text-center">
                                                    <i class="fas fa-lock mr-1"></i>Khusus <?= htmlspecialchars($assigned_nama) ?>
                                                </span>
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

<?php if (!empty($is_checker_coordinator)): ?>
<div class="modal fade" id="modalKelolaChecker" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header bg-warning">
            <h5 class="modal-title font-weight-bold"><i class="fas fa-users-cog mr-2"></i> Kelola Daftar Checker (Departemen LOGISTIC)</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info py-2 mb-3" style="font-size:13px;">
                <i class="fas fa-info-circle mr-1"></i>
                Hanya user aktif dari departemen <b>LOGISTIC</b> yang dapat ditambahkan ke daftar checker.
            </div>
            <div class="form-row align-items-end mb-3">
                <div class="col-md-9">
                    <label class="font-weight-bold small mb-1">Tambah Checker dari Departemen LOGISTIC:</label>
                    <select id="kc_new_nik" class="form-control form-control-sm">
                        <option value="">-- Pilih Karyawan LOGISTIC --</option>
                        <?php foreach (($logistic_candidates ?? []) as $cand): ?>
                            <option value="<?= htmlspecialchars($cand['nik']) ?>">
                                <?= htmlspecialchars($cand['nama_lngkp']) ?> (<?= htmlspecialchars($cand['username']) ?> - <?= htmlspecialchars($cand['bagian']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-success btn-sm btn-block font-weight-bold" id="btnAddCheckerMaster">
                        <i class="fas fa-plus mr-1"></i> Tambah Checker
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>NIK</th>
                            <th>Departemen / Bagian</th>
                            <th class="text-center" style="width:90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($list_checker)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada checker aktif.</td></tr>
                        <?php else: ?>
                            <?php foreach ($list_checker as $ck): ?>
                                <tr>
                                    <td class="font-weight-bold"><?= htmlspecialchars($ck['nm_karyawan']) ?></td>
                                    <td><code><?= htmlspecialchars($ck['username'] ?? '-') ?></code></td>
                                    <td><?= htmlspecialchars($ck['nik']) ?></td>
                                    <td><?= htmlspecialchars(($ck['departement'] ?? 'LOGISTIC') . ' — ' . ($ck['bagian'] ?? '-')) ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-danger btn-remove-checker-master"
                                                data-nik="<?= htmlspecialchars($ck['nik']) ?>"
                                                data-nama="<?= htmlspecialchars($ck['nm_karyawan']) ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
    </div></div>
</div>
<?php endif; ?>

<script>
$(document).on('click', '.btn-assign-route-checker', function() {
    var btn = $(this);
    var nik = btn.closest('.route-card').find('.select-route-checker').val();
    if (!nik) { alert('Pilih checker terlebih dahulu.'); return; }
    btn.prop('disabled', true);
    $.ajax({
        url: '<?= base_url("checker/so_loading/assign_checker") ?>',
        type: 'POST', dataType: 'json',
        data: { kd_rute: btn.data('rute'), id_trip: btn.data('trip') || 0, nik_checker: nik },
        success: function(res) {
            alert(res.message || 'Selesai.');
            if (res.status) location.reload(); else btn.prop('disabled', false);
        },
        error: function() { alert('Terjadi kesalahan koneksi.'); btn.prop('disabled', false); }
    });
});

$('#btnAddCheckerMaster').on('click', function() {
    var nik = $('#kc_new_nik').val();
    if (!nik) { alert('Pilih karyawan departemen LOGISTIC terlebih dahulu.'); return; }
    $.post('<?= base_url("checker/add_checker_master") ?>', { nik: nik }, function(res) {
        alert(res.msg || 'Selesai.');
        if (res.status) location.reload();
    }, 'json');
});

$(document).on('click', '.btn-remove-checker-master', function() {
    var nik = $(this).data('nik'), nama = $(this).data('nama');
    if (!confirm('Hapus ' + nama + ' dari daftar checker aktif?')) return;
    $.post('<?= base_url("checker/remove_checker_master") ?>', { nik: nik }, function(res) {
        alert(res.msg || 'Selesai.');
        if (res.status) location.reload();
    }, 'json');
});
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
        data: { kd_rute: route, id_trip: button.data('trip') || 0 },
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

$(document).on('click', '.btn-cancel-start-route', function() {
    var button = $(this);
    var route = button.data('rute');

    if (!confirm('Batalkan start loading rute ' + route + '?\nStatus akan dikembalikan ke Siap Loading.')) return;

    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    $.ajax({
        url: '<?= base_url("checker/so_loading/cancel") ?>',
        type: 'POST',
        dataType: 'json',
        data: { kd_rute: route },
        success: function(response) {
            if (response.status) {
                alert(response.message || 'Start loading berhasil dibatalkan.');
                window.location.reload();
                return;
            }
            alert(response.message || 'Gagal membatalkan loading.');
            button.prop('disabled', false).html('<i class="fas fa-undo"></i>');
        },
        error: function() {
            alert('Terjadi kesalahan koneksi saat membatalkan loading.');
            button.prop('disabled', false).html('<i class="fas fa-undo"></i>');
        }
    });
});
</script>
