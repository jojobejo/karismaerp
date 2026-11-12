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

    .btn-approval-bell {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 10px;
        background: #fff;
        color: #64748b;
        border: 1px solid rgba(18, 127, 173, .16);
        box-shadow: 0 10px 24px rgba(23, 32, 42, .06);
        cursor: pointer;
        transition: all .2s ease;
    }

    .btn-approval-bell:hover {
        background: #f0f9ff;
        color: #127fad;
        transform: translateY(-1px);
    }

    .btn-approval-bell.has-notif {
        color: #d97706;
        border-color: rgba(217, 119, 6, .35);
        background: #fffbeb;
    }

    .btn-approval-bell.has-notif i {
        animation: bellRing 1.6s ease-in-out infinite;
        transform-origin: top center;
    }

    .bell-count-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: #dc2626;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(220, 38, 38, .4);
    }

    @keyframes bellRing {
        0%, 100% { transform: rotate(0deg); }
        10% { transform: rotate(16deg); }
        20% { transform: rotate(-14deg); }
        30% { transform: rotate(12deg); }
        40% { transform: rotate(-10deg); }
        50% { transform: rotate(6deg); }
        60% { transform: rotate(0deg); }
    }

    .dashboard-nav-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .dashboard-tabs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 4px;
        margin-bottom: 0;
        flex: 1 1 auto;
        min-width: 0;
        scrollbar-width: thin;
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

        .dashboard-nav-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .dashboard-search-wrapper {
            flex: 1 1 100%;
            width: 100%;
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

        .dashboard-search-input {
            font-size: 14px;
        }

        .dashboard-tile-badge {
            max-width: 120px;
        }
    }

    /* Menu Search Styling */
    .dashboard-search-wrapper {
        position: relative;
        flex: 0 0 350px;
        max-width: 100%;
        margin-bottom: 0;
    }

    .dashboard-search-box {
        position: relative;
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1px solid rgba(18, 127, 173, .18);
        border-radius: 8px;
        min-height: 50px;
        padding: 0 16px;
        box-shadow: 0 8px 18px rgba(23, 32, 42, .05);
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .dashboard-search-box:focus-within {
        border-color: #127fad;
        box-shadow: 0 10px 22px rgba(18, 127, 173, .18);
        transform: translateY(-2px);
    }

    .dashboard-search-icon {
        font-size: 16px;
        color: #127fad;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .dashboard-search-input {
        flex: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: 15px;
        font-weight: 600;
        color: var(--dash-ink);
        padding: 8px 0;
        min-width: 0;
    }

    .dashboard-search-input::placeholder {
        color: #8c9ba5;
        font-weight: 400;
        font-size: 14px;
    }

    .dashboard-search-clear {
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 18px;
        cursor: pointer;
        padding: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color .2s ease, transform .2s ease;
    }

    .dashboard-search-clear:hover {
        color: #dc2626;
        transform: scale(1.1);
    }

    .dashboard-search-shortcut {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-left: 10px;
        user-select: none;
    }

    .dashboard-search-shortcut kbd {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 3px 7px;
        font-size: 12px;
        font-weight: 700;
        font-family: inherit;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .06);
    }

    .dashboard-tile-badge {
        display: inline-block;
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        background: rgba(255, 255, 255, .24);
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        padding: 4px 9px;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, .3);
        box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
    }

    .dashboard-search-panel {
        display: none;
    }

    .dashboard-search-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 13px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 999px;
    }

    /* Styling Menu Terkunci (Ikon Gembok) */
    .dashboard-menu-tile.is-locked {
        cursor: not-allowed;
        filter: saturate(0.42) brightness(0.86);
    }

    .dashboard-menu-tile.is-locked:hover,
    .dashboard-menu-tile.is-locked:focus {
        transform: translateY(-1px);
        box-shadow: 0 18px 34px rgba(22, 31, 44, .14);
    }

    .dashboard-menu-tile.is-locked::before {
        background: linear-gradient(135deg, rgba(15, 23, 42, .45), rgba(15, 23, 42, .58)),
                    linear-gradient(135deg, var(--tile-a), var(--tile-b));
    }

    .dashboard-lock-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(15, 23, 42, .72);
        color: #fef2f2;
        border: 1px solid rgba(255, 255, 255, .35);
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .3px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .25);
        flex-shrink: 0;
    }

    .dashboard-lock-badge i {
        color: #fbbf24;
    }

    .dashboard-tile-icon-wrap {
        position: relative;
        display: inline-flex;
    }

    .dashboard-tile-lock-corner {
        position: absolute;
        right: -8px;
        bottom: -8px;
        width: 30px;
        height: 30px;
        border-radius: 999px;
        background: #dc2626;
        color: #fff;
        border: 2px solid #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .3);
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
                            <?php $approvalCount = (int)($approval_notification_count ?? 0); ?>
                            <button type="button"
                                class="btn-approval-bell <?= $approvalCount > 0 ? 'has-notif' : '' ?>"
                                data-toggle="modal"
                                data-target="#modalNotifikasiApproval"
                                title="<?= $approvalCount > 0 ? $approvalCount . ' pengajuan menunggu approval Anda' : 'Tidak ada notifikasi approval' ?>"
                                aria-label="Notifikasi Approval">
                                <i class="fas fa-bell fa-lg"></i>
                                <?php if ($approvalCount > 0): ?>
                                    <span class="bell-count-badge"><?= $approvalCount > 99 ? '99+' : $approvalCount ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dashboard-badge">
                                <i class="fas fa-user-shield mr-2"></i> <?= html_escape($jobdesk ?: 'USER') ?> · LV <?= html_escape((string)$lv) ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Kumpulkan daftar modul unik untuk pencarian cepat lintas departemen
                    $uniqueSearchMenus = array();
                    foreach ($sections as $sKey => $sVal) {
                        if (empty($sVal['menus']) || !is_array($sVal['menus'])) {
                            continue;
                        }
                        foreach ($sVal['menus'] as $menu) {
                            $routeKey = trim((string)$menu['route']);
                            if (!isset($uniqueSearchMenus[$routeKey])) {
                                $uniqueSearchMenus[$routeKey] = $menu;
                                $uniqueSearchMenus[$routeKey]['section_labels'] = array($sVal['label']);
                            } else {
                                if (!in_array($sVal['label'], $uniqueSearchMenus[$routeKey]['section_labels'], true)) {
                                    $uniqueSearchMenus[$routeKey]['section_labels'][] = $sVal['label'];
                                }
                            }
                        }
                    }
                    ?>

                    <!-- Baris Navigasi: Tabs Modul (Kiri) & Menu Search (Kanan) -->
                    <div class="dashboard-nav-bar">
                        <div class="dashboard-tabs" role="tablist">
                            <?php foreach ($sections as $key => $section) : ?>
                                <button type="button" class="dashboard-tab <?= $key === $activeKey ? 'active' : '' ?>" data-target="<?= html_escape($key) ?>" role="tab">
                                    <i class="<?= html_escape($section['icon']) ?>"></i><?= html_escape($section['label']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Menu Search Cepat Modul Dashboard Sejajar Sisi Kanan -->
                        <div class="dashboard-search-wrapper">
                            <div class="dashboard-search-box">
                                <i class="fas fa-search dashboard-search-icon"></i>
                                <input type="text" id="dashboardModuleSearch" class="dashboard-search-input" placeholder="Cari modul..." autocomplete="off">
                                <button type="button" id="dashboardSearchClear" class="dashboard-search-clear" title="Hapus pencarian (Esc)" style="display: none;">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Hasil Pencarian Modul -->
                    <div id="dashboardSearchPanel" class="dashboard-search-panel">
                        <div class="dashboard-panel-head">
                            <div>
                                <h2>Hasil Pencarian Modul</h2>
                                <div class="dashboard-search-meta">
                                    <span class="dashboard-search-tag" id="dashboardSearchBadgeCount">
                                        <i class="fas fa-layer-group mr-1"></i> <span id="dashboardSearchCountText">0 modul</span>
                                    </span>
                                    <p id="dashboardSearchResultDesc" class="mb-0 text-muted small"></p>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetSearch">
                                    <i class="fas fa-times mr-1"></i> Tutup Pencarian
                                </button>
                            </div>
                        </div>

                        <div class="dashboard-menu-grid" id="dashboardSearchGrid">
                            <?php foreach ($uniqueSearchMenus as $menu) :
                                $secLabelStr = implode(' · ', $menu['section_labels']);
                                $searchKeywords = strtolower($menu['title'] . ' ' . $menu['description'] . ' ' . $menu['route'] . ' ' . implode(' ', $menu['section_labels']));
                                $isLocked = !empty($menu['is_locked']);
                            ?>
                                <a href="<?= $isLocked ? '#' : base_url($menu['route']) ?>"
                                   class="dashboard-menu-tile dashboard-tone-<?= html_escape($menu['tone']) ?> dashboard-search-item <?= $isLocked ? 'is-locked' : '' ?>"
                                   data-keywords="<?= html_escape($searchKeywords) ?>"
                                   data-title="<?= html_escape(strtolower($menu['title'])) ?>"
                                   <?= $isLocked ? 'data-locked="1" data-lock-title="' . html_escape($menu['title']) . '" aria-disabled="true"' : '' ?>
                                   style="display: none;">
                                    <span class="dashboard-menu-content">
                                        <span class="d-flex justify-content-between align-items-start" style="gap: 8px;">
                                            <span class="dashboard-tile-icon-wrap">
                                                <span class="dashboard-tile-icon"><i class="<?= html_escape($menu['icon']) ?>"></i></span>
                                                <?php if ($isLocked) : ?>
                                                    <span class="dashboard-tile-lock-corner" title="Menu Terkunci"><i class="fas fa-lock"></i></span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="d-flex flex-column align-items-end" style="gap: 6px;">
                                                <span class="dashboard-tile-badge" title="<?= html_escape($secLabelStr) ?>"><?= html_escape($secLabelStr) ?></span>
                                                <?php if ($isLocked) : ?>
                                                    <span class="dashboard-lock-badge"><i class="fas fa-lock"></i> Terkunci</span>
                                                <?php endif; ?>
                                            </span>
                                        </span>
                                        <span>
                                            <h3 class="dashboard-menu-title"><?= html_escape($menu['title']) ?></h3>
                                            <p class="dashboard-menu-desc"><?= html_escape($menu['description']) ?></p>
                                        </span>
                                        <span class="dashboard-menu-action">
                                            <?php if ($isLocked) : ?>
                                                <i class="fas fa-lock"></i> Tidak ada akses
                                            <?php else : ?>
                                                Buka modul <i class="fas fa-arrow-right"></i>
                                            <?php endif; ?>
                                        </span>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Tampilan Jika Modul Tidak Ditemukan -->
                        <div id="dashboardSearchEmpty" class="text-center py-5" style="display: none; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1; margin-top: 10px;">
                            <div class="mb-3">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 72px; height: 72px; background: rgba(18, 127, 173, .1); color: #127fad; font-size: 32px;">
                                    <i class="fas fa-search-minus"></i>
                                </span>
                            </div>
                            <h4 class="font-weight-bold text-dark">Modul Tidak Ditemukan</h4>
                            <p class="text-muted mb-3" id="dashboardSearchEmptyMsg">Tidak ada modul yang cocok dengan kata kunci pencarian Anda.</p>
                            <button type="button" class="btn btn-primary btn-sm px-3" id="btnSearchClearEmpty">
                                <i class="fas fa-undo mr-1"></i> Bersihkan Pencarian
                            </button>
                        </div>
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
                                <?php foreach ($section['menus'] as $menu) :
                                    $isLocked = !empty($menu['is_locked']);
                                ?>
                                    <a href="<?= $isLocked ? '#' : base_url($menu['route']) ?>"
                                       class="dashboard-menu-tile dashboard-tone-<?= html_escape($menu['tone']) ?> <?= $isLocked ? 'is-locked' : '' ?>"
                                       <?= $isLocked ? 'data-locked="1" data-lock-title="' . html_escape($menu['title']) . '" aria-disabled="true"' : '' ?>>
                                        <span class="dashboard-menu-content">
                                            <span class="d-flex justify-content-between align-items-start" style="gap: 8px;">
                                                <span class="dashboard-tile-icon-wrap">
                                                    <span class="dashboard-tile-icon"><i class="<?= html_escape($menu['icon']) ?>"></i></span>
                                                    <?php if ($isLocked) : ?>
                                                        <span class="dashboard-tile-lock-corner" title="Menu Terkunci"><i class="fas fa-lock"></i></span>
                                                    <?php endif; ?>
                                                </span>
                                                <?php if ($isLocked) : ?>
                                                    <span class="dashboard-lock-badge" title="Anda tidak memiliki akses untuk membuka modul ini"><i class="fas fa-lock"></i> Terkunci</span>
                                                <?php endif; ?>
                                            </span>
                                            <span>
                                                <h3 class="dashboard-menu-title"><?= html_escape($menu['title']) ?></h3>
                                                <p class="dashboard-menu-desc"><?= html_escape($menu['description']) ?></p>
                                            </span>
                                            <span class="dashboard-menu-action">
                                                <?php if ($isLocked) : ?>
                                                    <i class="fas fa-lock"></i> Tidak ada akses
                                                <?php else : ?>
                                                    Buka modul <i class="fas fa-arrow-right"></i>
                                                <?php endif; ?>
                                            </span>
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
            // Navigasi Tab Dashboard Normal
            $('.dashboard-tab').on('click', function() {
                const target = $(this).data('target');
                resetSearch();
                $('.dashboard-tab').removeClass('active');
                $(this).addClass('active');
                $('.dashboard-panel').removeClass('active');
                $('.dashboard-panel[data-panel="' + target + '"]').addClass('active');
            });

            // Efek hover tile menu
            $(document).on('mouseenter focus', '.dashboard-menu-tile', function() {
                $(this).addClass('is-active');
            }).on('mouseleave blur', '.dashboard-menu-tile', function() {
                $(this).removeClass('is-active');
            });

            // ELEMEN PENCARIAN MODUL
            const $searchInput = $('#dashboardModuleSearch');
            const $searchClear = $('#dashboardSearchClear');
            const $searchPanel = $('#dashboardSearchPanel');
            const $searchGrid = $('#dashboardSearchGrid');
            const $searchEmpty = $('#dashboardSearchEmpty');
            const $searchEmptyMsg = $('#dashboardSearchEmptyMsg');
            const $searchCountText = $('#dashboardSearchCountText');
            const $searchResultDesc = $('#dashboardSearchResultDesc');
            const $searchItems = $('.dashboard-search-item');
            const $dashboardPanels = $('.dashboard-panel');

            // Fungsi eksekusi pencarian real-time
            function executeSearch(query) {
                query = (query || '').trim().toLowerCase();

                if (query.length === 0) {
                    resetSearch();
                    return;
                }

                $searchClear.show();
                $dashboardPanels.hide();
                $searchPanel.show();

                let visibleCount = 0;
                $searchItems.each(function() {
                    const keywords = ($(this).data('keywords') || '').toString().toLowerCase();
                    if (keywords.indexOf(query) !== -1) {
                        $(this).css('display', 'flex');
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                $searchCountText.text(visibleCount + ' modul');
                $searchResultDesc.text('Menampilkan hasil pencarian untuk "' + query + '"');

                if (visibleCount === 0) {
                    $searchGrid.hide();
                    $searchEmpty.show();
                    $searchEmptyMsg.text('Tidak ada modul yang cocok dengan kata kunci "' + query + '". Coba kata kunci lainnya.');
                } else {
                    $searchEmpty.hide();
                    $searchGrid.show();
                }
            }

            // Fungsi reset pencarian dan kembali ke tampilan dashboard normal
            function resetSearch() {
                $searchInput.val('');
                $searchClear.hide();
                $searchPanel.hide();
                $searchEmpty.hide();
                $searchItems.hide();
                $('.dashboard-panel.active').show();
            }

            // Event input realtime
            $searchInput.on('input', function() {
                executeSearch($(this).val());
            });

            // Tombol Bersihkan / Reset Pencarian
            $searchClear.on('click', function() {
                resetSearch();
                $searchInput.focus();
            });

            $('#btnResetSearch, #btnSearchClearEmpty').on('click', function() {
                resetSearch();
            });

            function showLockedAlert(menuTitle) {
                const msg = 'Anda tidak memiliki akses untuk membuka menu "' + (menuTitle || 'ini') + '". Hubungi Administrator untuk membuka akses.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Akses Menu Terkunci',
                        text: msg,
                        confirmButtonColor: '#127fad',
                        confirmButtonText: 'Mengerti'
                    });
                } else {
                    alert(msg);
                }
            }

            // Blokir klik pada tile menu yang terkunci (ikon gembok)
            $(document).on('click', '.dashboard-menu-tile.is-locked', function(e) {
                e.preventDefault();
                showLockedAlert($(this).data('lock-title') || $(this).find('.dashboard-menu-title').text());
            });

            // Shortcut Keyboard:
            // 1. Enter pada input pencarian: langsung buka modul hasil pencarian pertama
            $searchInput.on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const $firstMatch = $searchItems.filter(':visible').first();
                    if ($firstMatch.length > 0) {
                        if ($firstMatch.hasClass('is-locked')) {
                            showLockedAlert($firstMatch.data('lock-title') || $firstMatch.find('.dashboard-menu-title').text());
                            return;
                        }
                        const targetUrl = $firstMatch.attr('href');
                        if (targetUrl && targetUrl !== '#') {
                            window.location.href = targetUrl;
                        }
                    }
                } else if (e.key === 'Escape') {
                    resetSearch();
                    $searchInput.blur();
                }
            });

            // 2. Global Shortcut: Ctrl+K / Cmd+K atau tombol '/' untuk fokus ke input search
            $(document).on('keydown', function(e) {
                const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
                const isFormInput = activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select';

                if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    $searchInput.focus().select();
                } else if (e.key === '/' && !isFormInput) {
                    e.preventDefault();
                    $searchInput.focus().select();
                } else if (e.key === 'Escape' && isFormInput && document.activeElement === $searchInput[0]) {
                    resetSearch();
                    $searchInput.blur();
                }
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

<!-- MODAL NOTIFIKASI -->
<div class="modal fade" id="modalNotifikasiApproval" tabindex="-1" role="dialog" aria-labelledby="modalNotifApprovalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #1e293b 0%, #0f4c81 60%, #127fad 100%);">
                <h5 class="modal-title font-weight-bold" id="modalNotifApprovalTitle">
                    <i class="fas fa-bell mr-2"></i> Notifikasi
                    <?php if (!empty($approval_notification_count)): ?>
                        <span class="badge badge-danger ml-2"><?= (int)$approval_notification_count ?></span>
                    <?php endif; ?>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 p-md-4" style="background-color: #f8fafc; max-height: 70vh; overflow-y: auto;">
                <?php if (empty($approval_notifications)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="font-weight-bold text-dark">Semua Sudah Diproses</h5>
                        <p class="small mb-0">Tidak ada pengajuan atau dokumen yang memerlukan approval Anda saat ini.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group shadow-sm">
                        <?php foreach ($approval_notifications as $item): ?>
                            <a href="<?= html_escape($item['url']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px; padding: 14px 16px;">
                                <div class="d-flex align-items-start" style="gap: 12px; min-width: 0; flex: 1;">
                                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                        <i class="<?= html_escape($item['icon']) ?>"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                            <span class="badge <?= html_escape($item['badge_class']) ?>"><?= html_escape($item['category']) ?></span>
                                            <strong class="text-dark"><?= html_escape($item['title']) ?></strong>
                                        </div>
                                        <div class="small text-dark mt-1 font-weight-bold"><?= html_escape($item['subtitle']) ?></div>
                                        <div class="small text-muted"><?= html_escape($item['detail']) ?></div>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="small text-muted mb-1"><?= html_escape($item['time_label']) ?></div>
                                    <span class="btn btn-sm btn-outline-primary font-weight-bold">
                                        Buka <i class="fas fa-arrow-right ml-1"></i>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
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
