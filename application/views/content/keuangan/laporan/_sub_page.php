<?php
/**
 * _sub_page.php — Shared partial untuk halaman laporan sub-kategori.
 *
 * Requires:
 *   $menus       array of [ title, icon, url, desc ]
 *   $back_url    string
 *   $page_title  string
 *   $page_icon   string  (FontAwesome class)
 *   $page_color  string  (hex color)
 */
?>
<style>
    .lpsub-page { font-family: 'Segoe UI', sans-serif; }

    .lpsub-topbar {
        display: flex;
        align-items: center;
        gap: 14px;
        background: linear-gradient(135deg, <?= $page_color ?>, <?= $page_color ?>cc);
        padding: 20px 28px;
        color: #fff;
        margin-bottom: 26px;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,.14);
    }

    .lpsub-back-btn {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: rgba(255,255,255,.2);
        color: #fff;
        font-size: 16px;
        text-decoration: none !important;
        flex-shrink: 0;
        transition: background .18s ease;
    }

    .lpsub-back-btn:hover { background: rgba(255,255,255,.35); color: #fff !important; }

    .lpsub-topbar h1 {
        font-size: 24px;
        font-weight: 800;
        margin: 0;
    }

    .lpsub-menu-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        padding: 0 24px 36px;
    }

    .lpsub-card {
        background: #fff;
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        padding: 12px 16px;
        text-decoration: none !important;
        color: #1f2d3d !important;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
    }

    .lpsub-card:hover {
        border-color: <?= $page_color ?>;
        box-shadow: 0 6px 16px rgba(0,0,0,.08);
        transform: translateY(-2px);
        color: #1f2d3d !important;
    }

    .lpsub-card-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: <?= $page_color ?>18;
        color: <?= $page_color ?>;
        font-size: 16px;
        flex-shrink: 0;
        transition: background .18s ease, transform .18s ease;
    }

    .lpsub-card:hover .lpsub-card-icon {
        background: <?= $page_color ?>;
        color: #fff;
        transform: scale(1.05);
    }

    .lpsub-card-body { 
        flex: 1; 
        overflow: hidden; 
    }

    .lpsub-card-title {
        font-size: 14px;
        font-weight: 700;
        margin: 0;
        line-height: 1.3;
        color: #17202a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .lpsub-card-desc {
        display: none;
    }

    .lpsub-card-action {
        display: none;
    }

    @media (max-width: 991.98px) {
        .lpsub-menu-grid { grid-template-columns: repeat(2, minmax(0,1fr)); padding: 0 16px 28px; }
    }

    @media (max-width: 575.98px) {
        .lpsub-menu-grid { grid-template-columns: 1fr; }
        .lpsub-topbar { padding: 16px 18px; }
        .lpsub-topbar h1 { font-size: 20px; }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse lpsub-page">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header"><div class="container-fluid"></div></div>

            <section class="content">

                <div class="lpsub-topbar">
                    <a href="<?= $back_url ?>" class="lpsub-back-btn" title="Kembali ke Laporan">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1><i class="<?= $page_icon ?> mr-2"></i> <?= html_escape($page_title) ?></h1>
                </div>

                <div class="lpsub-menu-grid">
                    <?php foreach ($menus as $m) : ?>
                        <a href="<?= $m['url'] ?>" class="lpsub-card">
                            <div class="lpsub-card-icon"><i class="<?= $m['icon'] ?>"></i></div>
                            <div class="lpsub-card-body">
                                <div class="lpsub-card-title"><?= html_escape($m['title']) ?></div>
                                <div class="lpsub-card-desc"><?= html_escape($m['desc']) ?></div>
                                <div class="lpsub-card-action">Buka <i class="fas fa-arrow-right"></i></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
        </footer>
    </div>
</body>
