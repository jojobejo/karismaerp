<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>
    <?php $rows = $comparison_rows ?? []; $e = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }; ?>
    <div class="content-wrapper" style="background:#f5f7fb">
        <section class="content-header"><div class="container-fluid d-flex justify-content-between align-items-center"><h1 class="m-0">Tracking Inputer Wilayah <?= $e($wilayah ?: '-') ?></h1><a class="btn btn-outline-secondary btn-sm" href="<?= base_url('supervisi-opname') ?>"><i class="fas fa-arrow-left"></i> Supervisi Opname</a></div></section>
        <section class="content"><div class="container-fluid"><div class="card shadow-sm"><div class="card-header"><strong>Perbandingan Input Tim 1 dan Tim 2</strong><span class="float-right text-muted small"><?= number_format(count($rows), 0, ',', '.') ?> barang/lot</span></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Status</th><th>Waktu Terakhir</th><th>Kode</th><th>Nama Barang</th><th>Lot</th><th>Exp Date</th><th class="text-right">Qty Tim 1</th><th class="text-right">Qty Tim 2</th><th>Inputer Tim 1</th><th>Inputer Tim 2</th></tr></thead><tbody>
            <?php if (!$rows) : ?><tr><td colspan="10" class="text-center text-muted py-4">Belum ada input opname Tim 1 atau Tim 2 untuk wilayah ini.</td></tr><?php endif; ?>
            <?php foreach ($rows as $row) : ?><?php $same = ($row['status_compare'] ?? '') === 'SAMA'; ?><tr><td><span class="badge badge-<?= $same ? 'success' : 'warning' ?>"><?= $same ? 'SAMA' : 'RE-CHECK' ?></span></td><td><?= $e($row['last_input'] ?? '-') ?></td><td><code><?= $e($row['kode_barang'] ?? '-') ?></code></td><td><?= $e($row['nama_barang'] ?? '-') ?></td><td><?= $e($row['no_lot'] ?? '-') ?></td><td><?= $e($row['expired_date'] ?? '-') ?></td><td class="text-right"><?= number_format((int)($row['qty_tim_1'] ?? 0), 0, ',', '.') ?></td><td class="text-right"><?= number_format((int)($row['qty_tim_2'] ?? 0), 0, ',', '.') ?></td><td><?= $e($row['inputer_tim_1'] ?? '-') ?></td><td><?= $e($row['inputer_tim_2'] ?? '-') ?></td></tr><?php endforeach; ?>
        </tbody></table></div></div></div></div></section>
    </div>
</div>
