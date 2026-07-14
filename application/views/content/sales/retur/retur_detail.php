<?php /* views/content/sales/retur/retur_detail.php */ ?>
<style>
    .timeline-retur { border-left: 3px solid #dee2e6; padding-left: 16px; margin-left: 8px; }
    .timeline-retur .tl-step { margin-bottom: 16px; position: relative; }
    .timeline-retur .tl-step::before {
        content: '';
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #dee2e6;
        position: absolute;
        left: -23px;
        top: 3px;
    }
    .timeline-retur .tl-step.done::before   { background: #28a745; }
    .timeline-retur .tl-step.active::before { background: #ffc107; }
    .timeline-retur .tl-step.reject::before { background: #dc3545; }
    .table-det th { background: #f8f9fa; font-size: 12px; }
    .table-det td { font-size: 12px; }
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
                        <h1 class="m-0"><i class="fas fa-file-invoice mr-2 text-primary"></i> Detail Retur: <?= htmlspecialchars($retur['no_retur']) ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan/retur') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
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

                <div class="row">
                    <!-- MAIN -->
                    <div class="col-lg-8">
                        <div class="card shadow" style="border: 2px solid #007bff;">
                            <div class="card-header d-flex justify-content-between align-items-center py-2"
                                 style="background: linear-gradient(135deg,#0056b3,#007bff); color:#fff;">
                                <div>
                                    <div style="font-size:1.1rem; font-weight:700;">RETUR PENJUALAN</div>
                                    <div style="font-size:11px; opacity:.85;">No. <?= htmlspecialchars($retur['no_retur']) ?></div>
                                </div>
                                <?php
                                $badge_map = [
                                    'menunggu_verifikasi' => ['warning', 'Menunggu Admin Stock'],
                                    'menunggu_collection' => ['info',    'Menunggu Collection'],
                                    'menunggu_kasir'      => ['primary', 'Menunggu Kasir'],
                                    'selesai'             => ['success', 'Selesai'],
                                    'ditolak'             => ['danger',  'Ditolak'],
                                ];
                                $bm = $badge_map[$retur['status_retur']] ?? ['secondary', $retur['status_retur']];
                                ?>
                                <span class="badge badge-<?= $bm[0] ?> px-3 py-2" style="font-size:12px;"><?= $bm[1] ?></span>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-3" style="font-size:13px;">
                                    <tr>
                                        <td style="width:130px;" class="font-weight-bold">No. Retur</td>
                                        <td>: <?= htmlspecialchars($retur['no_retur']) ?></td>
                                        <td style="width:130px;" class="font-weight-bold">Dari SPR</td>
                                        <td>: <?= htmlspecialchars($retur['no_spr'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Tanggal</td>
                                        <td>: <?= date('d/m/Y', strtotime($retur['tanggal_retur'])) ?></td>
                                        <td class="font-weight-bold">Customer</td>
                                        <td>: <strong><?= htmlspecialchars($retur['nama_customer'] ?: $retur['nama_customer_master'] ?: '-') ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Alamat</td>
                                        <td>: <?= htmlspecialchars($retur['alamat'] ?: $retur['alamat_master'] ?: '-') ?></td>
                                        <td class="font-weight-bold">Sales</td>
                                        <td>: <?= htmlspecialchars($retur['nama_sales'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Dibuat Oleh</td>
                                        <td>: <?= htmlspecialchars($retur['create_by_retur'] ?? '-') ?></td>
                                        <td class="font-weight-bold">Tgl. Buat</td>
                                        <td>: <?= $retur['create_at_retur'] ? date('d/m/Y H:i', strtotime($retur['create_at_retur'])) : '-' ?></td>
                                    </tr>
                                </table>

                                <!-- TABEL DETAIL BARANG -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-det">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:40px;">No.</th>
                                                <th>Nama Barang</th>
                                                <th>Satuan</th>
                                                <th>No. Faktur</th>
                                                <th>No. Batch</th>
                                                <th class="text-center">Exp. Date</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-right">Harga</th>
                                                <th class="text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($retur_detail)): ?>
                                                <tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data barang</td></tr>
                                            <?php else: ?>
                                                <?php $total = 0; foreach ($retur_detail as $i => $d):
                                                    $subtotal = (float)$d['qty_retur'] * (float)$d['harga_satuan'];
                                                    $total += $subtotal;
                                                ?>
                                                <tr>
                                                    <td class="text-center"><?= $i + 1 ?></td>
                                                    <td><?= htmlspecialchars($d['nama_barang'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($d['satuan'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($d['no_faktur'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($d['no_batch'] ?? '-') ?></td>
                                                    <td class="text-center"><?= !empty($d['expired_date']) ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?></td>
                                                    <td class="text-center"><?= number_format((float)$d['qty_retur'], 3) ?></td>
                                                    <td class="text-right">Rp <?= number_format((float)$d['harga_satuan'], 0, ',', '.') ?></td>
                                                    <td class="text-right">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="table-dark">
                                                    <td colspan="8" class="text-right font-weight-bold">TOTAL NILAI RETUR:</td>
                                                    <td class="text-right font-weight-bold">Rp <?= number_format($total, 0, ',', '.') ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if ($retur['catatan_logistik']): ?>
                                    <div class="mt-2 small text-muted"><strong>Catatan ADMLPB2:</strong> <?= nl2br(htmlspecialchars($retur['catatan_logistik'])) ?></div>
                                <?php endif; ?>
                                <?php if ($retur['no_faktur_potong']): ?>
                                    <div class="mt-2 small text-info"><strong>No. Faktur Potong:</strong> <?= htmlspecialchars($retur['no_faktur_potong']) ?></div>
                                <?php endif; ?>

                                <!-- TOMBOL AKSI -->
                                <?php
                                $jobdesk = strtoupper((string)($this->session->userdata('jobdesk') ?? ''));
                                $is_admin_stock = in_array($jobdesk, ['ADMSTOCK','ADMINSTOCK','ADMIN']);
                                $is_collection  = in_array($jobdesk, ['COLLECTION','KOLEKTOR','ADMIN']);
                                $is_kasir       = in_array($jobdesk, ['KASIR','ADMIN']);
                                $st = $retur['status_retur'];
                                ?>
                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    <?php if ($st === 'menunggu_verifikasi' && $is_admin_stock): ?>
                                        <a href="<?= base_url('retur_penjualan/retur/verifikasi/' . $retur['id_retur']) ?>" class="btn btn-warning mr-2">
                                            <i class="fas fa-clipboard-check"></i> Verifikasi (Admin Stock)
                                        </a>
                                    <?php elseif ($st === 'menunggu_collection' && $is_collection): ?>
                                        <a href="<?= base_url('retur_penjualan/retur/collection/' . $retur['id_retur']) ?>" class="btn btn-info mr-2">
                                            <i class="fas fa-handshake"></i> Proses Collection
                                        </a>
                                    <?php elseif ($st === 'menunggu_kasir' && $is_kasir): ?>
                                        <a href="<?= base_url('retur_penjualan/retur/kasir/' . $retur['id_retur']) ?>" class="btn btn-success mr-2">
                                            <i class="fas fa-cash-register"></i> Proses Kasir
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('retur_penjualan/retur/print/' . $retur['id_retur']) ?>" target="_blank" class="btn btn-secondary mr-2">
                                        <i class="fas fa-print"></i> Cetak Retur Penjualan
                                    </a>
                                    <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-light">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TIMELINE SIDEBAR -->
                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header bg-dark text-white py-2">
                                <h3 class="card-title"><i class="fas fa-tasks mr-1"></i> Status Proses Retur</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php
                                $st = $retur['status_retur'];
                                $steps_retur = [
                                    ['label' => 'Dibuat ADMLPB2',   'icon' => 'file-alt',       'done_statuses' => ['menunggu_verifikasi','menunggu_collection','menunggu_kasir','selesai','ditolak'], 'by' => $retur['create_by_retur'],     'at' => $retur['create_at_retur'],     'note' => $retur['catatan_logistik'] ?? null],
                                    ['label' => 'Verifikasi Admin Stock','icon' => 'clipboard-check','done_statuses' => ['menunggu_collection','menunggu_kasir','selesai'],                             'by' => $retur['admin_stock_by_retur'],'at' => $retur['admin_stock_at_retur'],'note' => $retur['catatan_admin_stock'] ?? null],
                                    ['label' => 'Proses Collection', 'icon' => 'handshake',      'done_statuses' => ['menunggu_kasir','selesai'],                                                       'by' => $retur['collection_by'] ?? null,'at' => $retur['collection_at'] ?? null,'note' => $retur['catatan_collection'] ?? null],
                                    ['label' => 'Kasir Selesai',    'icon' => 'cash-register',  'done_statuses' => ['selesai'],                                                                        'by' => $retur['kasir_by'] ?? null,    'at' => $retur['kasir_at'] ?? null,    'note' => $retur['catatan_kasir'] ?? null],
                                ];
                                ?>
                                <div class="timeline-retur">
                                    <?php foreach ($steps_retur as $step):
                                        $is_done   = in_array($st, $step['done_statuses']);
                                        $step_class = $is_done ? 'done' : ($st === 'ditolak' ? 'reject' : 'active');
                                    ?>
                                    <div class="tl-step <?= $step_class ?>">
                                        <div style="font-size:12px; font-weight:600;">
                                            <i class="fas fa-<?= $step['icon'] ?> mr-1" style="color:<?= $is_done ? '#28a745' : ($step_class === 'reject' ? '#dc3545' : '#ffc107') ?>;"></i>
                                            <?= $step['label'] ?>
                                        </div>
                                        <?php if ($step['by']): ?>
                                            <div style="font-size:11px; color:#555;">
                                                oleh: <strong><?= htmlspecialchars($step['by']) ?></strong>
                                                <?php if ($step['at']): ?> &mdash; <?= date('d/m/Y H:i', strtotime($step['at'])) ?><?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div style="font-size:11px; color:#aaa;">Menunggu...</div>
                                        <?php endif; ?>
                                        <?php if ($step['note']): ?>
                                            <div style="font-size:11px; color:#666; font-style:italic;">"<?= htmlspecialchars($step['note']) ?>"</div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($st === 'ditolak'): ?>
                                    <div class="alert alert-danger py-2 small mt-2">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        <strong>Retur Ditolak.</strong>
                                        <?php if ($retur['catatan_admin_stock']): ?>
                                            <br><?= htmlspecialchars($retur['catatan_admin_stock']) ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
