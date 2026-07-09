<!-- views/content/sales/retur/retur_penjualan_detail.php -->
<!-- Detail Retur Penjualan — untuk Admin Stock verifikasi & Team Collection -->
<style>
    .rp-card-header-green { background: linear-gradient(135deg,#1a6b3c,#27ae60); color:#fff; }
    .rp-card-header-blue  { background: linear-gradient(135deg,#1a3c6b,#2770ae); color:#fff; }
    .table-rp-det th { background:#f8f9fa; font-size:12px; border:1px solid #dee2e6; }
    .table-rp-det td { font-size:12px; border:1px solid #dee2e6; vertical-align:middle; }
    .approval-card-rp { border-left:4px solid; padding:10px 14px; border-radius:4px; background:#fafafa; margin-bottom:8px; }
    .approval-card-rp.done    { border-color:#28a745; background:#f0fff4; }
    .approval-card-rp.pending { border-color:#dee2e6; }
    .approval-card-rp.active  { border-color:#ffc107; background:#fffef0; }
    .approval-card-rp.rejected { border-color:#dc3545; background:#fff5f5; }
    .nilai-box { background:#f0fff4; border:1px solid #28a745; border-radius:4px; padding:8px 14px; }
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
                        <h1 class="m-0">
                            <i class="fas fa-undo-alt mr-2 text-success"></i>
                            Detail Retur: <?= htmlspecialchars($retur['no_retur']) ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Detail Retur</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- FLASH -->
                <?php foreach (['success'=>'success','error'=>'danger'] as $k=>$c): ?>
                    <?php if ($msg = $this->session->flashdata($k)): ?>
                        <div class="alert alert-<?= $c ?> alert-dismissible">
                            <i class="fas fa-<?= $k==='success'?'check-circle':'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="row">
                    <!-- MAIN CONTENT -->
                    <div class="col-lg-8">

                        <!-- CARD RETUR -->
                        <div class="card shadow" style="border:2px solid #27ae60;">
                            <div class="card-header rp-card-header-green py-2 d-flex justify-content-between align-items-center">
                                <div style="font-size:11px; opacity:.85;">PT. Karisma Indoagro Universal</div>
                                <div class="text-center">
                                    <div style="font-size:1rem; font-weight:700;">RETUR PENJUALAN</div>
                                    <div style="font-size:11px;">No. <?= htmlspecialchars($retur['no_retur']) ?></div>
                                </div>
                                <div>
                                    <?php
                                    $bm = [
                                        'menunggu_verifikasi' => ['warning','Menunggu Admin Stock'],
                                        'terverifikasi'       => ['info','Terverifikasi'],
                                        'menunggu_collection' => ['primary','Menunggu Collection'],
                                        'selesai'             => ['success','Selesai'],
                                        'ditolak'             => ['danger','Ditolak'],
                                    ][$retur['status_retur']] ?? ['secondary',$retur['status_retur']];
                                    ?>
                                    <span class="badge badge-<?= $bm[0] ?> px-3 py-2" style="font-size:12px;"><?= $bm[1] ?></span>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- INFO HEADER -->
                                <table class="table table-sm table-borderless mb-3" style="font-size:13px;">
                                    <tr>
                                        <td style="width:130px;" class="font-weight-bold">No. Retur</td>
                                        <td>: <strong class="text-success"><?= htmlspecialchars($retur['no_retur']) ?></strong></td>
                                        <td style="width:130px;" class="font-weight-bold">No. SPR Ref.</td>
                                        <td>: <a href="<?= base_url('retur_penjualan/detail/' . $retur['id_spr']) ?>" class="text-danger"><?= htmlspecialchars($retur['no_spr']) ?></a></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Tgl. Retur</td>
                                        <td>: <?= $retur['tanggal_retur'] ? date('d/m/Y', strtotime($retur['tanggal_retur'])) : '-' ?></td>
                                        <td class="font-weight-bold">Customer</td>
                                        <td>: <strong><?= htmlspecialchars($retur['nama_customer'] ?: ($retur['nama_customer_master'] ?? '-')) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Sales</td>
                                        <td>: <?= htmlspecialchars($retur['nama_sales'] ?? '-') ?></td>
                                        <td class="font-weight-bold">Dibuat Logistik</td>
                                        <td>: <?= htmlspecialchars($retur['create_by_retur']) ?> (<?= $retur['create_at_retur'] ? date('d/m/Y H:i', strtotime($retur['create_at_retur'])) : '-' ?>)</td>
                                    </tr>
                                    <?php if ($retur['catatan_logistik']): ?>
                                    <tr>
                                        <td class="font-weight-bold">Catatan Logistik</td>
                                        <td colspan="3">: <?= nl2br(htmlspecialchars($retur['catatan_logistik'])) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>

                                <!-- TABEL DETAIL BARANG -->
                                <h6 class="font-weight-bold text-success mb-2"><i class="fas fa-boxes mr-1"></i> Detail Barang Retur</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-rp-det">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:36px;">No.</th>
                                                <th>Nama Barang</th>
                                                <th>No. Faktur</th>
                                                <th>No. Batch/Lot</th>
                                                <th class="text-right">Qty Retur</th>
                                                <th class="text-right">Harga Satuan</th>
                                                <th class="text-right">Total Nilai</th>
                                                <?php if ($retur['status_retur'] === 'menunggu_verifikasi' && $can_verify): ?>
                                                <th class="text-center" style="width:120px;">Koreksi Admin Stock</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $grand_total = 0;
                                            foreach ($retur_detail as $i => $d):
                                                $subtotal = (float)$d['qty_retur'] * (float)$d['harga_satuan'];
                                                $grand_total += $subtotal;
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars($d['nama_barang'] ?? '-') ?></td>
                                                <td class="small"><?= htmlspecialchars($d['no_faktur'] ?? '-') ?></td>
                                                <td class="small"><?= htmlspecialchars($d['no_batch'] ?? '-') ?></td>
                                                <td class="text-right"><?= number_format((float)$d['qty_retur'], 3) ?></td>
                                                <td class="text-right">Rp <?= number_format((float)$d['harga_satuan'], 0, ',', '.') ?></td>
                                                <td class="text-right font-weight-bold text-success">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                                <?php if ($retur['status_retur'] === 'menunggu_verifikasi' && $can_verify): ?>
                                                <td class="text-center">
                                                    <span class="badge badge-secondary" style="font-size:10px;">Edit saat verifikasi</span>
                                                </td>
                                                <?php endif; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr style="background:#f0fff4;">
                                                <td colspan="<?= ($retur['status_retur'] === 'menunggu_verifikasi' && $can_verify) ? 6 : 6 ?>" class="text-right font-weight-bold">Total Nilai Retur:</td>
                                                <td class="text-right font-weight-bold text-success">Rp <?= number_format($grand_total, 0, ',', '.') ?></td>
                                                <?php if ($retur['status_retur'] === 'menunggu_verifikasi' && $can_verify): ?>
                                                <td></td>
                                                <?php endif; ?>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- CATATAN ADMIN STOCK -->
                                <?php if ($retur['catatan_admin_stock']): ?>
                                <div class="mt-2 p-2 border border-info rounded small" style="background:#f0f8ff;">
                                    <strong><i class="fas fa-boxes mr-1 text-info"></i>Catatan Admin Stock:</strong>
                                    <?= nl2br(htmlspecialchars($retur['catatan_admin_stock'])) ?>
                                </div>
                                <?php endif; ?>

                                <!-- CATATAN COLLECTION -->
                                <?php if ($retur['catatan_collection']): ?>
                                <div class="mt-2 p-2 border border-primary rounded small" style="background:#f0f4ff;">
                                    <strong><i class="fas fa-hand-holding-usd mr-1 text-primary"></i>Catatan Collection:</strong>
                                    <?= nl2br(htmlspecialchars($retur['catatan_collection'])) ?>
                                    <?php if ($retur['no_faktur_potong']): ?>
                                    <br><strong>Dipotongkan pada Faktur:</strong> <?= htmlspecialchars($retur['no_faktur_potong']) ?>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <!-- TOMBOL AKSI -->
                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    <?php
                                    $jobdesk = strtoupper((string)($user['jobdesk'] ?? $this->session->userdata('jobdesk') ?? ''));
                                    $is_admin_stock = in_array($jobdesk, ['ADMSTOCK','ADMINSTOCK','LOGISTIK','ADMIN']);
                                    $is_collection  = in_array($jobdesk, ['COLLECTION','KOLEKTOR','ADMIN']);
                                    $is_logistik    = in_array($jobdesk, ['LOGISTIK','ADMIN']);
                                    ?>

                                    <?php if ($retur['status_retur'] === 'menunggu_verifikasi' && $is_admin_stock): ?>
                                        <a href="<?= base_url('retur_penjualan/retur/verifikasi/' . $retur['id_retur']) ?>"
                                           class="btn btn-info mr-2">
                                            <i class="fas fa-boxes"></i> Verifikasi Admin Stock
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($retur['status_retur'] === 'menunggu_collection' && $is_collection): ?>
                                        <a href="<?= base_url('retur_penjualan/retur/collection/' . $retur['id_retur']) ?>"
                                           class="btn btn-primary mr-2">
                                            <i class="fas fa-hand-holding-usd"></i> Proses Collection
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-light">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div><!-- col-lg-8 -->

                    <!-- SIDEBAR: TIMELINE STATUS -->
                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header bg-dark text-white py-2">
                                <h3 class="card-title"><i class="fas fa-tasks mr-1"></i> Status Retur</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php
                                $steps_retur = [
                                    ['label'=>'Dibuat Logistik',       'icon'=>'truck-loading', 'by'=>'create_by_retur',      'at'=>'create_at_retur',      'note'=>null,                  'done_on'=>['menunggu_verifikasi','terverifikasi','menunggu_collection','selesai','ditolak']],
                                    ['label'=>'Verifikasi Admin Stock', 'icon'=>'boxes',         'by'=>'admin_stock_by_retur', 'at'=>'admin_stock_at_retur', 'note'=>'catatan_admin_stock', 'done_on'=>['menunggu_collection','selesai']],
                                    ['label'=>'Proses Team Collection', 'icon'=>'hand-holding-usd','by'=>'collection_by',     'at'=>'collection_at',        'note'=>'catatan_collection',  'done_on'=>['selesai']],
                                ];
                                $cur_st = $retur['status_retur'];
                                $active_map = [
                                    'menunggu_verifikasi'  => 0,
                                    'terverifikasi'        => 1,
                                    'menunggu_collection'  => 1,
                                    'selesai'              => 2,
                                    'ditolak'              => -1,
                                ];
                                $active_idx = $active_map[$cur_st] ?? -1;
                                foreach ($steps_retur as $idx => $step):
                                    $is_done   = in_array($cur_st, $step['done_on']);
                                    $is_active = ($idx === $active_idx) && $cur_st !== 'selesai';
                                    $card_cls  = $is_done ? 'done' : ($is_active ? 'active' : 'pending');
                                    $ico_color = $is_done ? '#28a745' : ($is_active ? '#ffc107' : '#adb5bd');
                                ?>
                                <div class="approval-card-rp <?= $card_cls ?>">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-<?= $step['icon'] ?> fa-lg mr-2" style="color:<?= $ico_color ?>; width:20px;"></i>
                                        <div class="flex-grow-1">
                                            <div style="font-size:12px; font-weight:600;"><?= $step['label'] ?></div>
                                            <?php if (($is_done || $is_active) && !empty($retur[$step['by']])): ?>
                                                <div style="font-size:11px; color:#555;">
                                                    oleh: <strong><?= htmlspecialchars($retur[$step['by']]) ?></strong>
                                                    <?php if (!empty($retur[$step['at']])): ?>
                                                        &mdash; <?= date('d/m/Y H:i', strtotime($retur[$step['at']])) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($step['note'] && !empty($retur[$step['note']])): ?>
                                                    <div style="font-size:11px; color:#666; margin-top:2px;"><em>"<?= htmlspecialchars($retur[$step['note']]) ?>"</em></div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div style="font-size:11px; color:#aaa;">Menunggu...</div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($is_done): ?>
                                            <i class="fas fa-check-circle text-success ml-2"></i>
                                        <?php elseif ($is_active): ?>
                                            <i class="fas fa-clock text-warning ml-2"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <?php if ($retur['status_retur'] === 'ditolak'): ?>
                                <div class="alert alert-danger mt-2 py-2 small">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    <strong>Retur Ditolak.</strong>
                                    <?php if (!empty($retur['catatan_admin_stock'])): ?>
                                        <br><?= htmlspecialchars($retur['catatan_admin_stock']) ?>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <?php if ($retur['status_retur'] === 'selesai' && !empty($retur['no_faktur_potong'])): ?>
                                <div class="alert alert-success mt-2 py-2 small">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <strong>Dipotongkan pada Faktur:</strong><br>
                                    <?= htmlspecialchars($retur['no_faktur_potong']) ?>
                                </div>
                                <?php endif; ?>

                                <hr class="my-2">
                                <div class="small">
                                    <div class="font-weight-bold mb-1">Ringkasan Nilai:</div>
                                    <?php
                                    $total_retur = 0;
                                    foreach ($retur_detail as $d) $total_retur += (float)$d['qty_retur'] * (float)$d['harga_satuan'];
                                    ?>
                                    <div class="nilai-box mt-1">
                                        <div class="text-muted small">Total Nilai Retur</div>
                                        <div class="font-weight-bold text-success" style="font-size:16px;">Rp <?= number_format($total_retur, 0, ',', '.') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- col-lg-4 -->
                </div><!-- row -->

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
