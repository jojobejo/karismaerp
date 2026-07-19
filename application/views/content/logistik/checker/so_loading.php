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
                                <?php foreach ($routes as $route): ?>
                                    <a href="<?= base_url('checker/so_loading/detail/' . rawurlencode($route['kd_rute']) . '?date=' . $route['tgl_transaksi']) ?>" class="route-card">
                                        <div class="route-code"><?= htmlspecialchars($route['kd_rute']) ?></div>
                                        <div class="route-name" title="<?= htmlspecialchars($route['nama_rute']) ?>">
                                            <?= htmlspecialchars($route['nama_rute']) ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="route-badge">
                                                <i class="fas fa-file-invoice mr-1"></i><?= (int)$route['total_so'] ?> SO
                                            </span>
                                            <span class="text-info font-weight-bold" style="font-size: 13px;">
                                                Mulai Loading <i class="fas fa-chevron-right ml-1"></i>
                                            </span>
                                        </div>
                                    </a>
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
