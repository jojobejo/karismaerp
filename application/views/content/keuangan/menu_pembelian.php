<style>
    .menu-page .content-header { padding: 6px .5rem 0; }
    .menu-page .page-title-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
    .menu-page .page-title-left { display: flex; align-items: center; gap: 10px; }
    .menu-page .page-home-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; background: #1788b8; color: #fff; }
    .menu-page .page-title { font-size: 30px; font-weight: 700; color: #34495e; margin: 0; }
    .menu-page .report-card-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
    .menu-page .support-card-btn { width: 100%; min-height: 116px; text-align: left; border: 1px solid #d9e2ec; border-radius: 6px; background: #fff; padding: 14px; color: #1f2d3d; transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
    .menu-page .report-card-link { display: block; text-decoration: none; }
    .menu-page .report-card-link .support-card-btn { display: block; }
    .menu-page .support-card-btn:hover, .menu-page .support-card-btn:focus { border-color: #1788b8; box-shadow: 0 10px 22px rgba(23, 136, 184, .12); transform: translateY(-2px); outline: none; }
    .menu-page .support-card-icon { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: #fff; background: #1788b8; margin-bottom: 10px; }
    .menu-page .support-card-title { display: block; font-weight: 800; font-size: 17px; }
    .menu-page .support-card-desc { display: block; color: #68778a; font-size: 13px; line-height: 1.35; margin-top: 4px; }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper menu-page">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header"><div class="container-fluid"></div></div>

            <section class="content">
                <div class="container-fluid">
                    <div class="page-title-row">
                        <div class="page-title-left">
                            <a href="<?= base_url('dashboard') ?>" class="page-home-btn" title="Dashboard"><i class="fas fa-home"></i></a>
                            <h1 class="page-title">Jurnal Pembelian (Keuangan)</h1>
                        </div>
                    </div>

                    <div class="report-card-grid">
                        <a href="<?= base_url('jurnal/pembelian') ?>" class="report-card-link">
                            <span class="support-card-btn">
                                <span class="support-card-icon"><i class="fas fa-shopping-cart"></i></span>
                                <span class="support-card-title">Daftar Jurnal Pembelian</span>
                                <span class="support-card-desc">Daftar pencatatan jurnal transaksi pembelian (LPB dan PO).</span>
                            </span>
                        </a>
                        <a href="<?= base_url('jurnal/retur-pembelian') ?>" class="report-card-link">
                            <span class="support-card-btn">
                                <span class="support-card-icon"><i class="fas fa-undo-alt"></i></span>
                                <span class="support-card-title">Daftar Jurnal Retur</span>
                                <span class="support-card-desc">Daftar jurnal retur pembelian dari route ICS retur pembelian.</span>
                            </span>
                        </a>
                        <a href="<?= base_url('jurnal/pelunasan-utang') ?>" class="report-card-link">
                            <span class="support-card-btn">
                                <span class="support-card-icon"><i class="fas fa-money-check-alt"></i></span>
                                <span class="support-card-title">Daftar Jurnal Pelunasan Utang Perusahaan</span>
                                <span class="support-card-desc">Daftar pembayaran supplier dan potong hutang retur pembelian.</span>
                            </span>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>

