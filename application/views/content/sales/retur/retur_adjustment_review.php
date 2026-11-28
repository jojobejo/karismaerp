<!-- views/content/sales/retur/retur_adjustment_review.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-7">
                        <h1 class="m-0"><i class="fas fa-sliders-h mr-2 text-warning"></i>Approval Adjustment Retur Penjualan</h1>
                    </div>
                    <div class="col-sm-5 text-right">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php foreach (['success' => 'success', 'error' => 'danger'] as $k => $c): ?>
                    <?php if ($msg = $this->session->flashdata($k)): ?>
                        <div class="alert alert-<?= $c ?> alert-dismissible">
                            <?= $msg ?>
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="card card-outline card-warning shadow-sm mb-3">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            Pengajuan: <?= htmlspecialchars($req['no_request']) ?> · SPR: <?= htmlspecialchars($req['no_spr']) ?>
                        </h3>
                        <div class="card-tools">
                            <?php
                            $st = $req['status'] ?? 'PENDING';
                            $badge = $st === 'APPROVED' ? 'success' : ($st === 'REJECTED' ? 'danger' : ($st === 'USED' ? 'info' : 'warning'));
                            ?>
                            <span class="badge badge-<?= $badge ?> px-2 py-1"><?= htmlspecialchars($st) ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Customer</small>
                                <strong><?= htmlspecialchars($spr['nama_customer'] ?: ($spr['nama_customer_master'] ?? '-')) ?></strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Diajukan Oleh</small>
                                <strong><?= htmlspecialchars($req['requested_by']) ?></strong>
                                <small class="text-muted">(<?= date('d/m/Y H:i', strtotime($req['requested_at'])) ?>)</small>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Alasan Adjustment</small>
                                <strong><?= nl2br(htmlspecialchars($req['alasan'])) ?></strong>
                            </div>
                        </div>
                        <?php if (!empty($req['approved_by'])): ?>
                        <hr>
                        <div class="small">
                            Diproses oleh: <b><?= htmlspecialchars($req['approved_by']) ?></b>
                            (<?= !empty($req['approved_at']) ? date('d/m/Y H:i', strtotime($req['approved_at'])) : '-' ?>)
                            <?php if (!empty($req['approval_note'])): ?>
                                — Catatan: <i><?= htmlspecialchars($req['approval_note']) ?></i>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white py-2">
                        <h3 class="card-title"><i class="fas fa-exchange-alt mr-1"></i> Perbandingan Data SPR vs Adjustment Barang Datang</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Kode &amp; Nama Barang</th>
                                        <th>Satuan</th>
                                        <th class="text-right">Qty SPR</th>
                                        <th class="text-right bg-warning-light">Qty Adjustment</th>
                                        <th>Batch SPR</th>
                                        <th class="bg-warning-light">Batch Adjustment</th>
                                        <th>Exp SPR</th>
                                        <th class="bg-warning-light">Exp Adjustment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $it):
                                        $qtyDiff = abs((float)$it['qty_spr'] - (float)$it['qty_adj']) > 0.0001;
                                        $batchDiff = trim((string)$it['no_batch_spr']) !== trim((string)$it['no_batch_adj']);
                                        $expDiff = trim((string)$it['expired_date_spr']) !== trim((string)$it['expired_date_adj']);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($it['nama_barang']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($it['kd_barang']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($it['satuan']) ?></td>
                                        <td class="text-right"><?= number_format((float)$it['qty_spr'], 2, ',', '.') ?></td>
                                        <td class="text-right font-weight-bold <?= $qtyDiff ? 'text-danger' : '' ?>">
                                            <?= number_format((float)$it['qty_adj'], 2, ',', '.') ?>
                                        </td>
                                        <td><?= htmlspecialchars($it['no_batch_spr'] ?: '-') ?></td>
                                        <td class="font-weight-bold <?= $batchDiff ? 'text-danger' : '' ?>">
                                            <?= htmlspecialchars($it['no_batch_adj'] ?: '-') ?>
                                        </td>
                                        <td><?= htmlspecialchars($it['expired_date_spr'] ?: '-') ?></td>
                                        <td class="font-weight-bold <?= $expDiff ? 'text-danger' : '' ?>">
                                            <?= htmlspecialchars($it['expired_date_adj'] ?: '-') ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if (($req['status'] ?? '') === 'PENDING' && !empty($can_approve)): ?>
                    <div class="card-footer bg-white">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <form method="post" action="<?= base_url('retur_penjualan/retur/adjustment_approve/' . $req['id_request']) ?>">
                                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                    <?php endif; ?>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="approval_note" class="form-control" placeholder="Catatan persetujuan (opsional)...">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-success font-weight-bold" onclick="return confirm('Setujui adjustment retur ini?')">
                                                <i class="fas fa-check mr-1"></i> Setujui Adjustment
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6 mb-2">
                                <form method="post" action="<?= base_url('retur_penjualan/retur/adjustment_reject/' . $req['id_request']) ?>">
                                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                    <?php endif; ?>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="approval_note" class="form-control" placeholder="Alasan penolakan (wajib jika tolak)..." required>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-danger font-weight-bold" onclick="return confirm('Tolak pengajuan adjustment ini?')">
                                                <i class="fas fa-times mr-1"></i> Tolak
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>
