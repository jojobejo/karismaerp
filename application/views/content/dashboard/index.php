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

    .dashboard-topbar {
        min-height: 56px;
        color: #fff;
        background: #127fad;
        border: 0;
        box-shadow: 0 8px 18px rgba(18, 127, 173, .18);
    }

    .dashboard-topbar .navbar-brand,
    .dashboard-topbar .nav-link {
        color: #fff !important;
    }

    .dashboard-topbar .navbar-brand {
        font-weight: 700;
        letter-spacing: .2px;
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
    .dashboard-tone-brown { --tile-a: #8b5737; --tile-b: #ad7552; }
    .dashboard-tone-cyan { --tile-a: #1287a8; --tile-b: #26bad1; }

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

        <nav class="main-header navbar navbar-expand dashboard-topbar">
            <a href="<?= base_url('dashboard') ?>" class="navbar-brand">
                <i class="fas fa-th-large mr-2"></i> Dashboard
            </a>
            <ul class="navbar-nav ml-auto align-items-center">
                <li class="nav-item d-none d-sm-block">
                    <span class="nav-link"><?= html_escape($nama) ?></span>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-light">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="content-wrapper">
            <section class="content">
                <div class="dashboard-shell">
                    <div class="dashboard-hero">
                        <div>
                            <h1>KARISMAERP</h1>
                        </div>
                        <div class="dashboard-badge">
                            <i class="fas fa-user-shield mr-2"></i> <?= html_escape($jobdesk ?: 'USER') ?> · LV <?= html_escape((string)$lv) ?>
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
