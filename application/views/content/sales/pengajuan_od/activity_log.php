<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-history mr-2 text-info"></i>
                            Activity Log Pengajuan OD
                        </h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <ol class="breadcrumb float-sm-right mb-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales/C_PengajuanOD') ?>">Pengajuan OD</a></li>
                            <li class="breadcrumb-item active">Activity Log</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0"><i class="fas fa-list mr-1"></i> Log Aktivitas OD</h3>
                        <a href="<?= base_url('sales/C_PengajuanOD') ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0" style="font-size: 14px;">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th style="width: 15%;">Waktu</th>
                                        <th style="width: 10%;">ID Pengajuan</th>
                                        <th style="width: 25%;">Aksi</th>
                                        <th style="width: 20%;">Oleh</th>
                                        <th style="width: 30%;">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td class="text-center align-middle">
                                                    <?= date('d/m/Y H:i:s', strtotime($log['waktu'])) ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <a href="<?= base_url('sales/C_PengajuanOD/detail/' . $log['id']) ?>" class="badge badge-info px-2 py-1" style="font-size: 13px;">
                                                        OD-<?= $log['id'] ?>
                                                    </a>
                                                </td>
                                                <td class="align-middle">
                                                    <?php 
                                                        $badge_class = 'secondary';
                                                        $icon = 'circle';
                                                        if (strpos($log['aksi'], 'Buat') !== false) {
                                                            $badge_class = 'primary';
                                                            $icon = 'plus-circle';
                                                        } elseif (strpos($log['aksi'], 'Persetujuan') !== false) {
                                                            $badge_class = 'success';
                                                            $icon = 'check-circle';
                                                        } elseif (strpos($log['aksi'], 'Ditolak') !== false) {
                                                            $badge_class = 'danger';
                                                            $icon = 'times-circle';
                                                        }
                                                    ?>
                                                    <span class="badge badge-<?= $badge_class ?> px-2 py-1" style="font-size: 12px;">
                                                        <i class="fas fa-<?= $icon ?> mr-1"></i> <?= htmlspecialchars($log['aksi']) ?>
                                                    </span>
                                                </td>
                                                <td class="align-middle font-weight-bold">
                                                    <?= htmlspecialchars($log['actor']) ?>
                                                </td>
                                                <td class="align-middle">
                                                    <em class="text-muted"><?= htmlspecialchars($log['note'] ?: '-') ?></em>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">Belum ada aktivitas tercatat.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
