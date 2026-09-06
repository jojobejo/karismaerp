<?php
$context = isset($dashboard_context) ? $dashboard_context : array();
$sections = isset($dashboard_sections) && is_array($dashboard_sections) ? $dashboard_sections : array();
$activeKey = isset($dashboard_active_key) ? $dashboard_active_key : key($sections);
$nama = isset($context['nama']) ? $context['nama'] : $this->session->userdata('username');
$jobdesk = isset($context['jobdesk']) ? $context['jobdesk'] : $this->session->userdata('jobdesk');
$lv = isset($context['lv']) ? (int)$context['lv'] : (int)$this->session->userdata('lv');
?>
<style>
    :root {
        --dash-ink: #17202a;
        --dash-muted: #607184;
        --dash-bg: #eef2f7;
    }

    body.app-dashboard-body {
        background: var(--dash-bg);
    }

    .app-dashboard-body .main-header,
    .app-dashboard-body .content-wrapper,
    .app-dashboard-body .main-footer {
        margin-left: 0 !important;
    }

    .app-dashboard-body .content-wrapper {
        min-height: calc(100vh - 104px);
        background:
            radial-gradient(circle at 18% 16%, rgba(18, 127, 173, .12), transparent 27%),
            linear-gradient(135deg, #f8fafc 0%, #edf2f7 50%, #e7edf4 100%);
    }

    .dashboard-shell {
        width: min(1880px, calc(100% - 36px));
        margin: 0 auto;
        padding: 34px 0 46px;
    }

    .dashboard-hero {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 22px;
    }

    .dashboard-hero h1 {
        color: var(--dash-ink);
        font-size: 36px;
        line-height: 1.18;
        font-weight: 800;
        letter-spacing: 0;
        margin: 0 0 8px;
    }

    .dashboard-hero p {
        max-width: 920px;
        color: var(--dash-muted);
        font-size: 17px;
        margin: 0;
    }

    .dashboard-badge {
        color: #127fad;
        background: #fff;
        border: 1px solid rgba(18, 127, 173, .16);
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 15px;
        box-shadow: 0 10px 24px rgba(23, 32, 42, .06);
        white-space: nowrap;
    }

    .dashboard-tabs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 10px;
        margin-bottom: 18px;
    }

    .dashboard-tab {
        flex: 0 0 auto;
        border: 1px solid rgba(18, 127, 173, .18);
        background: #fff;
        color: #314455;
        border-radius: 8px;
        min-height: 50px;
        padding: 14px 20px;
        font-size: 15px;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(23, 32, 42, .05);
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }

    .dashboard-tab:hover,
    .dashboard-tab:focus,
    .dashboard-tab.active {
        color: #fff;
        background: #127fad;
        transform: translateY(-2px);
    }

    .dashboard-tab i {
        margin-right: 8px;
    }

    .dashboard-panel {
        display: none;
    }

    .dashboard-panel.active {
        display: block;
    }

    .dashboard-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .dashboard-panel-head h2 {
        color: var(--dash-ink);
        font-size: 26px;
        font-weight: 800;
        letter-spacing: 0;
        margin: 0 0 4px;
    }

    .dashboard-panel-head p {
        color: var(--dash-muted);
        font-size: 16px;
        margin: 0;
    }

    .dashboard-menu-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        grid-auto-rows: 220px;
        gap: 18px;
    }

    .dashboard-menu-tile {
        position: relative;
        display: flex;
        height: 220px;
        min-height: 220px;
        padding: 26px;
        color: #fff;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: 0 18px 34px rgba(22, 31, 44, .12);
        text-decoration: none;
        isolation: isolate;
        transform: translateY(0);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .dashboard-menu-tile:hover,
    .dashboard-menu-tile:focus {
        color: #fff;
        text-decoration: none;
        transform: translateY(-5px);
        box-shadow: 0 24px 42px rgba(22, 31, 44, .2);
    }

    .dashboard-menu-tile::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background: linear-gradient(135deg, var(--tile-a), var(--tile-b));
    }

    .dashboard-menu-tile::after {
        content: "";
        position: absolute;
        inset: auto -38px -52px auto;
        width: 160px;
        height: 160px;
        border: 24px solid rgba(255, 255, 255, .12);
        border-radius: 999px;
        z-index: -1;
        transition: transform .25s ease, opacity .25s ease;
    }

    .dashboard-menu-tile:hover::after,
    .dashboard-menu-tile:focus::after {
        transform: scale(1.15);
        opacity: .65;
    }

    .dashboard-menu-content {
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 18px;
    }

    .dashboard-tile-icon {
        width: 74px;
        height: 74px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, .18);
        font-size: 36px;
        transition: transform .2s ease, background .2s ease;
    }

    .dashboard-menu-tile:hover .dashboard-tile-icon,
    .dashboard-menu-tile:focus .dashboard-tile-icon {
        transform: translateY(-3px) scale(1.04);
        background: rgba(255, 255, 255, .24);
    }

    .dashboard-menu-title {
        font-size: 23px;
        line-height: 1.24;
        font-weight: 700;
        letter-spacing: 0;
        margin: 0;
    }

    .dashboard-menu-desc {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(8px);
        margin: 0;
        color: rgba(255, 255, 255, .88);
        font-size: 15px;
        line-height: 1.45;
        transition: max-height .24s ease, opacity .2s ease, transform .2s ease;
    }

    .dashboard-menu-tile:hover .dashboard-menu-desc,
    .dashboard-menu-tile:focus .dashboard-menu-desc,
    .dashboard-menu-tile.is-active .dashboard-menu-desc {
        max-height: 90px;
        opacity: 1;
        transform: translateY(0);
        margin-top: 9px;
    }

    .dashboard-menu-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: rgba(255, 255, 255, .92);
        font-size: 15px;
        font-weight: 700;
    }

    .dashboard-tone-blue { --tile-a: #2296d1; --tile-b: #41c1df; }
    .dashboard-tone-orange { --tile-a: #f05a24; --tile-b: #fb8b42; }
    .dashboard-tone-slate { --tile-a: #436b95; --tile-b: #6d8fb1; }
    .dashboard-tone-green { --tile-a: #3c9f08; --tile-b: #78bf2a; }
    .dashboard-tone-red { --tile-a: #b90f34; --tile-b: #e1184e; }
    .dashboard-tone-lime { --tile-a: #a6b700; --tile-b: #d2d900; }
    .dashboard-tone-purple { --tile-a: #9d1bbb; --tile-b: #cf25c9; }
    .dashboard-tone-teal { --tile-a: #2d929b; --tile-b: #50b8c0; }
    .dashboard-tone-dark { --tile-a: #343a40; --tile-b: #59626e; }
    .dashboard-tone-cyan { --tile-a: #1287a8; --tile-b: #26bad1; }

    @keyframes bellShake {
        0% { transform: rotate(0deg) scale(1); }
        10% { transform: rotate(-16deg) scale(1.08); }
        20% { transform: rotate(16deg) scale(1.08); }
        30% { transform: rotate(-12deg) scale(1.05); }
        40% { transform: rotate(12deg) scale(1.05); }
        50% { transform: rotate(-6deg) scale(1.02); }
        60% { transform: rotate(6deg) scale(1.02); }
        70% { transform: rotate(0deg) scale(1); }
        100% { transform: rotate(0deg) scale(1); }
    }

    .btn-notif-shake {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        color: #fff !important;
        border: none;
        border-radius: 8px;
        padding: 9px 16px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        box-shadow: 0 6px 18px rgba(220, 38, 38, 0.4);
        transition: transform .2s ease, box-shadow .2s ease;
        position: relative;
    }

    .btn-notif-shake:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(220, 38, 38, 0.55);
        text-decoration: none;
    }

    .btn-notif-shake i.fa-bell {
        animation: bellShake 1.4s infinite ease-in-out;
        transform-origin: top center;
        display: inline-block;
        font-size: 17px;
    }

    .btn-notif-shake .notif-badge {
        background: #fff;
        color: #dc2626;
        font-size: 12px;
        font-weight: 800;
        border-radius: 999px;
        padding: 2px 7px;
        line-height: 1.2;
    }

    @media (max-width: 1399.98px) {
        .dashboard-menu-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .dashboard-shell {
            width: min(100% - 28px, 960px);
        }

        .dashboard-menu-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-auto-rows: 204px;
        }

        .dashboard-menu-tile {
            height: 204px;
            min-height: 204px;
        }
    }

    @media (max-width: 575.98px) {
        .dashboard-shell {
            width: min(100% - 24px, 560px);
            padding: 24px 0 34px;
        }

        .dashboard-hero,
        .dashboard-panel-head {
            display: block;
        }

        .dashboard-hero h1 {
            font-size: 28px;
        }

        .dashboard-badge {
            display: inline-block;
            margin-top: 14px;
            white-space: normal;
        }

        .dashboard-menu-grid {
            grid-template-columns: 1fr;
            grid-auto-rows: 190px;
        }

        .dashboard-menu-tile {
            height: 190px;
            min-height: 190px;
        }
    }
</style>

<body class="hold-transition layout-top-nav app-dashboard-body">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php
        $karisma_topbar_has_sidebar = false;
        $this->load->view('partial/main/app_topbar');
        ?>

        <div class="content-wrapper">
            <section class="content">
                <div class="dashboard-shell">
                    <div class="dashboard-hero">
                        <div>
                            <h1>KARISMAERP</h1>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 12px; flex-wrap: wrap;">
                            <?php if (!empty($is_admpnj) && !empty($lpb_notification_count)): ?>
                                <button type="button" class="btn-notif-shake" data-toggle="modal" data-target="#modalNotifikasiRevisiLpb" title="Klik untuk melihat notifikasi permintaan revisi harga LPB">
                                    <i class="fas fa-bell"></i>
                                    <span>Revisi Harga LPB</span>
                                    <span class="notif-badge"><?= (int)$lpb_notification_count ?></span>
                                </button>
                            <?php endif; ?>
                            <div class="dashboard-badge">
                                <i class="fas fa-user-shield mr-2"></i> <?= html_escape($jobdesk ?: 'USER') ?> · LV <?= html_escape((string)$lv) ?>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-tabs" role="tablist">
                        <?php foreach ($sections as $key => $section) : ?>
                            <button type="button" class="dashboard-tab <?= $key === $activeKey ? 'active' : '' ?>" data-target="<?= html_escape($key) ?>" role="tab">
                                <i class="<?= html_escape($section['icon']) ?>"></i><?= html_escape($section['label']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <?php foreach ($sections as $key => $section) : ?>
                        <div class="dashboard-panel <?= $key === $activeKey ? 'active' : '' ?>" data-panel="<?= html_escape($key) ?>">
                            <div class="dashboard-panel-head">
                                <div>
                                    <h2><?= html_escape($section['label']) ?></h2>
                                    <p><?= html_escape($section['description']) ?></p>
                                </div>
                            </div>

                            <div class="dashboard-menu-grid">
                                <?php foreach ($section['menus'] as $menu) : ?>
                                    <a href="<?= base_url($menu['route']) ?>" class="dashboard-menu-tile dashboard-tone-<?= html_escape($menu['tone']) ?>">
                                        <span class="dashboard-menu-content">
                                            <span>
                                                <span class="dashboard-tile-icon"><i class="<?= html_escape($menu['icon']) ?>"></i></span>
                                            </span>
                                            <span>
                                                <h3 class="dashboard-menu-title"><?= html_escape($menu['title']) ?></h3>
                                                <p class="dashboard-menu-desc"><?= html_escape($menu['description']) ?></p>
                                            </span>
                                            <span class="dashboard-menu-action">Buka modul <i class="fas fa-arrow-right"></i></span>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>
    </div>

    <script>
        $(function() {
            $('.dashboard-tab').on('click', function() {
                const target = $(this).data('target');
                $('.dashboard-tab').removeClass('active');
                $(this).addClass('active');
                $('.dashboard-panel').removeClass('active');
                $('.dashboard-panel[data-panel="' + target + '"]').addClass('active');
            });

            $('.dashboard-menu-tile')
                .on('mouseenter focus', function() {
                    $(this).addClass('is-active');
                })
                .on('mouseleave blur', function() {
                    $(this).removeClass('is-active');
                });
        });
    </script>

<?php if (!empty($is_admpnj)): ?>
<!-- MODAL NOTIFIKASI REVISI HARGA LPB UNTUK ADMPNJ -->
<div class="modal fade" id="modalNotifikasiRevisiLpb" tabindex="-1" role="dialog" aria-labelledby="modalNotifLpbTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-danger text-white py-3">
                <h5 class="modal-title font-weight-bold" id="modalNotifLpbTitle">
                    <i class="fas fa-bell mr-2"></i> Notifikasi Permintaan Revisi Harga LPB
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" style="background-color: #f8fafc; max-height: 70vh; overflow-y: auto;">
                <?php if (empty($lpb_notifications)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>Tidak Ada Permintaan Revisi LPB Aktif</h5>
                        <p class="small mb-0">Semua faktur penjualan berjalan normal dan tidak ada request revisi harga LPB yang tertunda.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning border-0 shadow-sm mb-3">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mr-3 mt-1"></i>
                            <div>
                                <strong class="text-dark font-weight-bold">Perhatian Admin Penjualan (ADMPNJ):</strong>
                                <p class="mb-0 text-muted small mt-1">
                                    Terdapat permintaan revisi harga LPB dari Purchasing/Logistik. Anda perlu melakukan <strong>Posting Ulang (Repost)</strong> pada Faktur Penjualan yang terdampak melalui menu <strong>Faktur Penjualan (Modul Transaksi)</strong> agar penyesuaian HPP barang dapat sinkron dengan benar.
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php foreach ($lpb_notifications as $notif): ?>
                        <div class="card card-outline card-danger shadow-sm mb-3">
                            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <span class="badge badge-danger mr-2 px-2 py-1 font-weight-bold"><?= htmlspecialchars($notif['no_request']) ?></span>
                                    <strong class="text-dark">LPB: <?= htmlspecialchars($notif['nomor_lpb'] ?: ('ID #' . $notif['id_lpb'])) ?></strong>
                                </div>
                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                                    <i class="fas fa-clock mr-1"></i><?= htmlspecialchars($notif['status']) ?>
                                </span>
                            </div>
                            <div class="card-body py-3">
                                <div class="row mb-2 small">
                                    <div class="col-sm-6 mb-1">
                                        <span class="text-muted">Supplier:</span> <strong><?= htmlspecialchars($notif['nama_supplier'] ?: '-') ?></strong>
                                    </div>
                                    <div class="col-sm-6 mb-1 text-sm-right">
                                        <span class="text-muted">Diminta oleh:</span> <strong><?= htmlspecialchars($notif['requested_by'] ?: 'Purchasing') ?></strong> (<?= !empty($notif['requested_at']) ? date('d/m/Y H:i', strtotime($notif['requested_at'])) : '-' ?>)
                                    </div>
                                </div>
                                <?php if (!empty($notif['alasan_revisi'])): ?>
                                    <div class="bg-light p-2 rounded mb-2 small text-muted">
                                        <i class="fas fa-info-circle mr-1 text-info"></i>Alasan: "<?= htmlspecialchars($notif['alasan_revisi']) ?>"
                                    </div>
                                <?php endif; ?>

                                <div class="mt-2">
                                    <span class="small font-weight-bold text-danger d-block mb-1">
                                        <i class="fas fa-file-invoice mr-1"></i>Faktur Penjualan yang Terdampak (Butuh Repost):
                                    </span>
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <?php if (!empty($notif['fakturs'])): ?>
                                            <?php foreach ($notif['fakturs'] as $fak): ?>
                                                <span class="badge <?= $fak['is_unposted'] ? 'badge-success' : 'badge-danger' ?> px-2 py-1 mr-1 mb-1 font-weight-bold" style="font-size: 12px;">
                                                    <i class="fas <?= $fak['is_unposted'] ? 'fa-check' : 'fa-exclamation-circle' ?> mr-1"></i>
                                                    <?= htmlspecialchars($fak['no_faktur']) ?>
                                                    <?= $fak['is_unposted'] ? '(Sudah Diproses)' : '(Perlu Repost)' ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">- Tidak ada faktur terdampak -</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light py-2 text-right">
                                <a href="<?= base_url('admin/transaksi?ref=lpb_revision&no_request=' . urlencode($notif['no_request']) . '&faktur=' . urlencode($notif['faktur_list_str'])) ?>" class="btn btn-danger btn-sm font-weight-bold shadow-sm">
                                    <i class="fas fa-external-link-alt mr-1"></i> Buka Faktur Penjualan (Admin Transaksi)
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
