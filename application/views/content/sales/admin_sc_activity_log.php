<!-- views/content/sales/admin_sc_activity_log.php -->
<?php
function admin_sc_aksi_badge($aksi) {
    $map = [
        'BUAT_FAKTUR'       => ['success',   'fa-file-invoice-dollar', 'Buat Faktur'],
        'UPDATE_HARGA'      => ['warning',   'fa-tags',                'Update Harga'],
        'SPLIT_FAKTUR'      => ['info',      'fa-cut',                 'Split Faktur'],
        'KEMBALIKAN_SO'     => ['secondary', 'fa-reply',               'Kembalikan SO'],
        'REPOST_ITEM'       => ['danger',    'fa-undo',                'Repost Item'],
        'PRINT_FAKTUR_RUTE' => ['primary',   'fa-print',               'Cetak Rute'],
    ];
    $a   = strtoupper((string)$aksi);
    $cfg = $map[$a] ?? ['dark', 'fa-history', $a];
    return '<span class="badge badge-'.$cfg[0].' px-2 py-1">'
         . '<i class="fas '.$cfg[1].' mr-1"></i>'
         . htmlspecialchars($cfg[2])
         . '</span>';
}
?>
<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-history mr-2"></i> Activity Log — Admin SC
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order/admin_sc') ?>">Admin SC</a></li>
                            <li class="breadcrumb-item active">Activity Log</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <div class="mb-3">
                    <a href="<?= base_url('sales_order/admin_sc') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Admin SC
                    </a>
                </div>

                <!-- FILTER -->
                <div class="card card-outline card-info mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Activity Log</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <form method="get" action="<?= base_url('sales_order/admin_sc/activity_log') ?>">
                            <div class="row">
                                <div class="col-md-2 mb-2">
                                    <label class="small mb-1">No SO</label>
                                    <input type="text" name="no_so" class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($filter['no_so'] ?? '') ?>"
                                        placeholder="Cari No SO...">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="small mb-1">No Faktur</label>
                                    <input type="text" name="no_faktur" class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($filter['no_faktur'] ?? '') ?>"
                                        placeholder="Cari No Faktur...">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="small mb-1">Aksi</label>
                                    <select name="aksi" class="form-control form-control-sm">
                                        <option value="">-- Semua Aksi --</option>
                                        <?php
                                        $options = [
                                            'BUAT_FAKTUR'       => 'Buat Faktur',
                                            'UPDATE_HARGA'      => 'Update Harga',
                                            'SPLIT_FAKTUR'      => 'Split Faktur',
                                            'KEMBALIKAN_SO'     => 'Kembalikan SO',
                                            'REPOST_ITEM'       => 'Repost Item',
                                            'PRINT_FAKTUR_RUTE' => 'Cetak Rute',
                                        ];
                                        foreach ($options as $val => $lbl):
                                        ?>
                                            <option value="<?= $val ?>" <?= ($filter['aksi'] ?? '') === $val ? 'selected' : '' ?>>
                                                <?= $lbl ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label class="small mb-1">Tanggal</label>
                                    <input type="date" name="tanggal" class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($filter['tanggal'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small mb-1">Kata Kunci</label>
                                    <input type="text" name="keyword" class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($filter['keyword'] ?? '') ?>"
                                        placeholder="Keterangan / User...">
                                </div>
                                <div class="col-md-1 mb-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                                        <i class="fas fa-search mr-1"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABLE LOG -->
                <div class="card card-outline card-primary">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i> Total Log: <strong><?= number_format($total) ?></strong>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th style="width: 150px;">Waktu</th>
                                        <th style="width: 130px;">Aksi</th>
                                        <th style="width: 140px;">No SO / Faktur</th>
                                        <th>Keterangan</th>
                                        <th style="width: 130px;">Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-info-circle mr-1"></i> Tidak ada data activity log.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $i => $log): ?>
                                            <tr>
                                                <td class="text-center"><?= (($page - 1) * $per_page) + $i + 1 ?></td>
                                                <td>
                                                    <small class="font-weight-bold">
                                                        <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                                    </small>
                                                </td>
                                                <td><?= admin_sc_aksi_badge($log['aksi']) ?></td>
                                                <td>
                                                    <?php if (!empty($log['no_so'])): ?>
                                                        <small class="d-block text-primary">SO: <strong><?= htmlspecialchars($log['no_so']) ?></strong></small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($log['no_faktur'])): ?>
                                                        <small class="d-block text-success">FAK: <strong><?= htmlspecialchars($log['no_faktur']) ?></strong></small>
                                                    <?php endif; ?>
                                                    <?php if (empty($log['no_so']) && empty($log['no_faktur'])): ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= nl2br(htmlspecialchars($log['keterangan'] ?? '')) ?>
                                                    <?php if (!empty($log['detail_produk'])): ?>
                                                        <details class="mt-1">
                                                            <summary class="small text-info cursor-pointer">Lihat Rincian Item</summary>
                                                            <pre class="small bg-light p-2 mt-1 rounded border mb-0" style="white-space: pre-wrap; font-size: 11px;"><?= htmlspecialchars($log['detail_produk']) ?></pre>
                                                        </details>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-dark font-weight-bold">
                                                        <i class="fas fa-user-circle text-muted mr-1"></i>
                                                        <?= htmlspecialchars($log['dilakukan_oleh'] ?? 'system') ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($total_pages > 1): ?>
                        <div class="card-footer py-2">
                            <ul class="pagination pagination-sm m-0 float-right">
                                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= base_url('sales_order/admin_sc/activity_log?' . http_build_query(array_merge($filter, ['page' => $p]))) ?>">
                                            <?= $p ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOAGRO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>
</body>
