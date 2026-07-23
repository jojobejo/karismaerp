<?php
/**
 * Laporan Index — Halaman pemilihan kategori laporan.
 * Terinspirasi dari tampilan Zahir Accounting.
 */
$categories = [
    [
        'title'       => 'Laporan Keuangan',
        'icon'        => 'fas fa-chart-pie',
        'route'       => 'laporan/keuangan',
        'color_a'     => '#1b6ca8',
        'color_b'     => '#2296d1',
        'description' => 'Neraca, Laba Rugi, Jurnal Umum, dan laporan posisi keuangan lainnya.',
    ],
    [
        'title'       => 'Laporan Penjualan & Piutang',
        'icon'        => 'fas fa-file-invoice-dollar',
        'route'       => 'laporan/penjualan',
        'color_a'     => '#1a8f4c',
        'color_b'     => '#27c46b',
        'description' => 'eFaktur Penjualan, Jurnal Penjualan, Piutang Usaha, Retur, dan Pembayaran.',
    ],
    [
        'title'       => 'Laporan Pembelian & Hutang',
        'icon'        => 'fas fa-shopping-cart',
        'route'       => 'laporan/pembelian',
        'color_a'     => '#b35a00',
        'color_b'     => '#e07a1e',
        'description' => 'Jurnal Pembelian (LPB/PO), Hutang Usaha, dan laporan pengadaan barang.',
    ],
    [
        'title'       => 'Laporan Barang',
        'icon'        => 'fas fa-boxes',
        'route'       => 'laporan/barang',
        'color_a'     => '#5a3ea1',
        'color_b'     => '#8662d9',
        'description' => 'Stok barang, mutasi gudang, expired date, dan kartu stok per periode.',
    ],
    [
        'title'       => 'Laporan Lainnya',
        'icon'        => 'fas fa-folder-open',
        'route'       => 'laporan/lainnya',
        'color_a'     => '#2d6b6b',
        'color_b'     => '#3fa9a9',
        'description' => 'Laporan HRD, KPI, Logistik distribusi, dan laporan khusus lainnya.',
    ],
];
?>
<style>
    .lp-page { font-family: 'Segoe UI', sans-serif; }
    .lp-page .content-header { padding: 6px .5rem 0; }

    .lp-topbar {
        background: linear-gradient(135deg, #1b4f72, #1788b8 70%, #21d3ee);
        padding: 28px 32px 22px;
        color: #fff;
        margin-bottom: 28px;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 6px 28px rgba(18, 127, 173, .22);
    }

    .lp-topbar h1 {
        font-size: 30px;
        font-weight: 800;
        margin: 0 0 4px;
        letter-spacing: -.3px;
    }

    .lp-topbar p {
        margin: 0;
        opacity: .82;
        font-size: 16px;
    }

    .lp-cat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 22px;
        padding: 0 28px 40px;
    }

    .lp-cat-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        text-decoration: none !important;
        color: #fff !important;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        min-height: 220px;
        padding: 0;
        box-shadow: 0 12px 32px rgba(0,0,0,.16);
        transition: transform .2s ease, box-shadow .2s ease;
        isolation: isolate;
    }

    .lp-cat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 22px 44px rgba(0,0,0,.22);
        color: #fff !important;
        text-decoration: none !important;
    }

    .lp-cat-bg {
        position: absolute;
        inset: 0;
        z-index: -2;
        background: linear-gradient(145deg, var(--ca), var(--cb));
    }

    .lp-cat-deco {
        position: absolute;
        right: -30px;
        top: -30px;
        width: 160px;
        height: 160px;
        border-radius: 999px;
        background: rgba(255,255,255,.1);
        z-index: -1;
        transition: transform .25s ease;
    }

    .lp-cat-card:hover .lp-cat-deco {
        transform: scale(1.18);
    }

    .lp-cat-deco2 {
        position: absolute;
        right: 30px;
        bottom: -50px;
        width: 110px;
        height: 110px;
        border-radius: 999px;
        background: rgba(255,255,255,.07);
        z-index: -1;
    }

    .lp-cat-icon-wrap {
        position: absolute;
        top: 22px;
        left: 24px;
        width: 64px;
        height: 64px;
        background: rgba(255,255,255,.18);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        transition: background .2s ease, transform .2s ease;
    }

    .lp-cat-card:hover .lp-cat-icon-wrap {
        background: rgba(255,255,255,.28);
        transform: scale(1.07);
    }

    .lp-cat-body {
        padding: 22px 24px 22px;
        background: linear-gradient(0deg, rgba(0,0,0,.38) 0%, transparent 100%);
    }

    .lp-cat-title {
        font-size: 20px;
        font-weight: 800;
        margin: 0 0 6px;
        line-height: 1.2;
    }

    .lp-cat-desc {
        font-size: 13px;
        line-height: 1.45;
        opacity: .88;
        margin: 0 0 14px;
    }

    .lp-cat-action {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
        opacity: .9;
        letter-spacing: .3px;
        transition: gap .18s ease;
    }

    .lp-cat-card:hover .lp-cat-action {
        gap: 12px;
    }

    @media (max-width: 991.98px) {
        .lp-cat-grid { grid-template-columns: repeat(2, minmax(0,1fr)); padding: 0 16px 30px; }
    }

    @media (max-width: 575.98px) {
        .lp-cat-grid { grid-template-columns: 1fr; }
        .lp-topbar { padding: 20px 18px 16px; }
        .lp-topbar h1 { font-size: 24px; }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse lp-page">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header"><div class="container-fluid"></div></div>

            <section class="content">

                <div class="lp-topbar">
                    <h1><i class="fas fa-chart-bar mr-2"></i> Laporan</h1>
                    <p>Pilih kategori laporan yang ingin Anda buka. Data diambil langsung dari transaksi operasional ERP.</p>
                </div>

                <div class="lp-cat-grid">
                    <?php foreach ($categories as $cat) : ?>
                        <a href="<?= base_url($cat['route']) ?>" class="lp-cat-card" style="--ca:<?= $cat['color_a'] ?>;--cb:<?= $cat['color_b'] ?>">
                            <div class="lp-cat-bg"></div>
                            <div class="lp-cat-deco"></div>
                            <div class="lp-cat-deco2"></div>
                            <div class="lp-cat-icon-wrap">
                                <i class="<?= $cat['icon'] ?>"></i>
                            </div>
                            <div class="lp-cat-body">
                                <div class="lp-cat-title"><?= html_escape($cat['title']) ?></div>
                                <div class="lp-cat-desc"><?= html_escape($cat['description']) ?></div>
                                <div class="lp-cat-action">Buka Laporan <i class="fas fa-arrow-right"></i></div>
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
