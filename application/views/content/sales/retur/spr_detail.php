<!-- views/content/sales/retur/spr_detail.php -->
<style>
    .spr-badge-timeline {
        display: flex;
        align-items: center;
        gap: 0;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding: 8px 0;
    }
    .spr-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        min-width: 110px;
    }
    .spr-step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 700;
        color: #fff;
        z-index: 1;
    }
    .spr-step-label {
        font-size: 11px;
        margin-top: 5px;
        text-align: center;
        font-weight: 600;
    }
    .spr-step-by {
        font-size: 10px;
        color: #888;
        text-align: center;
        margin-top: 2px;
    }
    .spr-step-line {
        height: 3px;
        flex: 1;
        background: #dee2e6;
        align-self: center;
        margin-bottom: 24px;
    }
    .spr-step-line.done { background: #28a745; }

    .step-done    .spr-step-icon { background: #28a745; }
    .step-active  .spr-step-icon { background: #ffc107; color: #333; }
    .step-pending .spr-step-icon { background: #dee2e6; color: #888; }
    .step-rejected .spr-step-icon { background: #dc3545; }

    .table-detail-spr th { background: #f8f9fa; font-size: 12px; border: 1px solid #dee2e6; }
    .table-detail-spr td { font-size: 12px; border: 1px solid #dee2e6; vertical-align: middle; }

    .alasan-list { list-style: none; padding: 0; margin: 0; }
    .alasan-list li { font-size: 11px; line-height: 1.5; }
    .alasan-list li::before { content: "✓ "; color: #28a745; font-weight: 700; }

    .spr-note-bottom {
        background: #fff8f8;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        padding: 10px 14px;
        font-size: 12px;
        color: #721c24;
    }

    .approval-card {
        border-left: 4px solid;
        padding: 12px 16px;
        border-radius: 4px;
        background: #fafafa;
        margin-bottom: 8px;
    }
    .approval-card.done    { border-color: #28a745; background: #f0fff4; }
    .approval-card.pending { border-color: #dee2e6; }
    .approval-card.active  { border-color: #ffc107; background: #fffef0; }
    .approval-card.rejected { border-color: #dc3545; background: #fff5f5; }
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
                            <i class="fas fa-file-alt mr-2 text-danger"></i>
                            Detail SPR: <?= htmlspecialchars($spr['no_spr']) ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Detail SPR</li>
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

                        <!-- CARD SURAT -->
                        <div class="card shadow" style="border: 2px solid #e74c3c;">
                            <div class="card-header d-flex justify-content-between align-items-center py-2"
                                 style="background: linear-gradient(135deg,#c0392b,#e74c3c); color:#fff;">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= base_url('assets/images/Karisma.png') ?>" height="36" style="filter:brightness(10);" alt="Logo">
                                    <span style="font-size:11px; opacity:.85;">PT. Karisma Indoagro Universal</span>
                                </div>
                                <div class="text-center">
                                    <div style="font-size:1.1rem; font-weight:700;">SURAT PENGAJUAN RETUR BARANG</div>
                                    <div style="font-size:11px;">No. <?= htmlspecialchars($spr['no_spr']) ?></div>
                                </div>
                                <div>
                                    <?php
                                    $badge_map = [
                                        'draft'               => ['secondary','Draft'],
                                        'diajukan'            => ['warning','Diajukan'],
                                        'diverifikasi_koor'   => ['info','Verif. Koor SC'],
                                        'dicek_admin_stock'   => ['primary','Cek Admin Stock'],
                                        'disetujui_kadep'     => ['success','Disetujui Kadep'],
                                        'selesai'             => ['success','Selesai'],
                                        'ditolak'             => ['danger','Ditolak'],
                                    ];
                                    $bm = $badge_map[$spr['status']] ?? ['secondary',$spr['status']];
                                    ?>
                                    <span class="badge badge-<?= $bm[0] ?> px-3 py-2" style="font-size:12px;"><?= $bm[1] ?></span>
                                </div>
                            </div>

                            <div class="card-body">
                                <!-- INFO HEADER -->
                                <table class="table table-sm table-borderless mb-3" style="font-size:13px;">
                                    <tr>
                                        <td style="width:120px;" class="font-weight-bold">Tanggal</td>
                                        <td>: <?= date('d/m/Y', strtotime($spr['tanggal'])) ?></td>
                                        <td style="width:120px;" class="font-weight-bold">Nama Customer</td>
                                        <td>: <strong><?= htmlspecialchars($spr['nama_customer'] ?: ($spr['nama_customer_master'] ?? '-')) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Alamat</td>
                                        <td>: <?= htmlspecialchars($spr['alamat'] ?: ($spr['alamat_master'] ?? '-')) ?></td>
                                        <td class="font-weight-bold">Sales</td>
                                        <td>: <?= htmlspecialchars($spr['nama_sales'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Dibuat Oleh</td>
                                        <td>: <?= htmlspecialchars($spr['create_by']) ?></td>
                                        <td class="font-weight-bold">Tanggal Buat</td>
                                        <td>: <?= $spr['create_at'] ? date('d/m/Y H:i', strtotime($spr['create_at'])) : '-' ?></td>
                                    </tr>
                                </table>

                                <p class="small text-muted mb-2" style="font-style:italic;">
                                    Berikut ini adalah barang-barang yang kami ajukan untuk diretur, dengan rincian sbb:
                                </p>

                                <!-- TABEL DETAIL -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-detail-spr">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:36px;">No.</th>
                                                <th>Nama Barang</th>
                                                <th>No. Faktur</th>
                                                <th>No. Batch/Lot</th>
                                                <th class="text-center">Qty</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($spr_detail)): ?>
                                                <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data barang</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($spr_detail as $i => $d): ?>
                                                    <tr>
                                                        <td class="text-center"><?= $i + 1 ?></td>
                                                        <td><?= htmlspecialchars($d['nama_barang'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($d['no_faktur'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($d['no_batch'] ?? '-') ?></td>
                                                        <td class="text-right"><?= number_format((float)$d['qty'], 3) ?></td>
                                                        <td>
                                                            <?php
                                                            $alasan_list = [];
                                                            if ($d['alasan_brg_bermasalah']) {
                                                                $opt = $d['alasan_brg_bermasalah_opt'] ? ' ('.strtoupper($d['alasan_brg_bermasalah_opt']).')' : '';
                                                                $alasan_list[] = 'Brg bermasalah retur ke pabrik' . $opt;
                                                            }
                                                            if ($d['alasan_expired']) {
                                                                $opt = $d['alasan_expired_opt'] ? ' ('.strtoupper($d['alasan_expired_opt']).')' : '';
                                                                $alasan_list[] = 'Expired' . $opt;
                                                            }
                                                            if ($d['alasan_tidak_laku'])         $alasan_list[] = 'Brg tidak laku & masuk OD';
                                                            if ($d['alasan_tes_market'])          $alasan_list[] = 'Faktur T/Brg Tes Market';
                                                            if ($d['alasan_bad_debt'])            $alasan_list[] = 'Potensi Bad Debt';
                                                            if ($d['alasan_harga_tidak_sesuai'])  $alasan_list[] = 'Brg/Harga tdk sesuai Pesanan';
                                                            if ($d['alasan_spr_intern'])          $alasan_list[] = 'SPR Intern (brg Oper)';
                                                            if (!empty($d['alasan_lainlain']))    $alasan_list[] = 'Lain-lain: ' . htmlspecialchars($d['alasan_lainlain']);
                                                            ?>
                                                            <?php if ($alasan_list): ?>
                                                                <ul class="alasan-list">
                                                                    <?php foreach ($alasan_list as $al): ?>
                                                                        <li><?= $al ?></li>
                                                                    <?php endforeach; ?>
                                                                </ul>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if ($spr['catatan']): ?>
                                    <div class="mt-2 small text-muted"><strong>Catatan:</strong> <?= nl2br(htmlspecialchars($spr['catatan'])) ?></div>
                                <?php endif; ?>

                                <div class="spr-note-bottom mt-3">
                                    <strong>Catatan:</strong> Barang yang kami retur sesuai dengan data di atas. Bilamana tidak sesuai, maka kami (toko) akan
                                    bertanggung jawab menerima konsekuensinya (retur ditolak) sesuai kebijakan PT Karisma Indoagro Universal.
                                </div>

                                <!-- TOMBOL AKSI -->
                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    <?php if ($spr['status'] === 'draft' && $spr['create_by'] === ($user['nama'] ?? '')): ?>
                                        <a href="<?= base_url('retur_penjualan/submit/' . $spr['id_spr']) ?>"
                                           class="btn btn-warning mr-2"
                                           onclick="return confirm('Ajukan SPR ini ke Koor SC?')">
                                            <i class="fas fa-paper-plane"></i> Ajukan ke Koor SC
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('retur_penjualan/print/' . $spr['id_spr']) ?>"
                                       class="btn btn-secondary mr-2" target="_blank">
                                        <i class="fas fa-print"></i> Print SPR
                                    </a>
                                    <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-light">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div><!-- end card -->

                    </div><!-- col-lg-8 -->

                    <!-- SIDEBAR: TIMELINE APPROVAL -->
                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header bg-dark text-white py-2">
                                <h3 class="card-title"><i class="fas fa-tasks mr-1"></i> Status Approval</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php
                                $steps = [
                                    ['key'=>'draft',             'label'=>'Dibuat SC',        'icon'=>'user-edit',   'by_field'=>'create_by',     'at_field'=>'create_at',     'note_field'=>null,              'done_statuses'=>['diajukan','diverifikasi_koor','dicek_admin_stock','disetujui_kadep','selesai','ditolak']],
                                    ['key'=>'diajukan',          'label'=>'Diajukan SC',      'icon'=>'paper-plane', 'by_field'=>'create_by',     'at_field'=>'create_at',     'note_field'=>null,              'done_statuses'=>['diverifikasi_koor','dicek_admin_stock','disetujui_kadep','selesai','ditolak']],
                                    ['key'=>'diverifikasi_koor', 'label'=>'Verifikasi Koor SC','icon'=>'clipboard-check','by_field'=>'koor_sc_by','at_field'=>'koor_sc_at',   'note_field'=>'koor_sc_catatan', 'done_statuses'=>['dicek_admin_stock','disetujui_kadep','selesai']],
                                    ['key'=>'dicek_admin_stock', 'label'=>'Cek Admin Stock',  'icon'=>'boxes',       'by_field'=>'admin_stock_by','at_field'=>'admin_stock_at','note_field'=>'admin_stock_catatan','done_statuses'=>['disetujui_kadep','selesai']],
                                    ['key'=>'disetujui_kadep',   'label'=>'Acc Kadep SC',     'icon'=>'user-tie',    'by_field'=>'kadep_sc_by',   'at_field'=>'kadep_sc_at',   'note_field'=>'kadep_sc_catatan','done_statuses'=>['selesai']],
                                    ['key'=>'selesai',           'label'=>'Selesai (Logistik)','icon'=>'truck-loading','by_field'=>'logistik_by', 'at_field'=>'logistik_at',  'note_field'=>'logistik_catatan','done_statuses'=>['selesai']],
                                ];
                                $cur = $spr['status'];
                                foreach ($steps as $step):
                                    $is_done    = in_array($cur, $step['done_statuses']);
                                    $is_active  = ($cur === $step['key']) && $cur !== 'selesai';
                                    $is_rejected = ($cur === 'ditolak');
                                    $card_class = $is_done ? 'done' : ($is_active ? 'active' : ($is_rejected && $step['key'] !== 'draft' ? 'rejected' : 'pending'));
                                    $icon_color = $is_done ? '#28a745' : ($is_active ? '#ffc107' : '#adb5bd');
                                ?>
                                <div class="approval-card <?= $card_class ?>">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-<?= $step['icon'] ?> fa-lg mr-2" style="color:<?= $icon_color ?>; width:20px;"></i>
                                        <div class="flex-grow-1">
                                            <div style="font-size:12px; font-weight:600;"><?= $step['label'] ?></div>
                                            <?php if ($is_done || ($is_active && $spr[$step['by_field']])): ?>
                                                <div style="font-size:11px; color:#555;">
                                                    oleh: <strong><?= htmlspecialchars($spr[$step['by_field']] ?? '-') ?></strong>
                                                    <?php if ($spr[$step['at_field']]): ?>
                                                        &mdash; <?= date('d/m/Y H:i', strtotime($spr[$step['at_field']])) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($step['note_field'] && !empty($spr[$step['note_field']])): ?>
                                                    <div style="font-size:11px; color:#666; margin-top:2px;"><em>"<?= htmlspecialchars($spr[$step['note_field']]) ?>"</em></div>
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

                                <?php if ($spr['status'] === 'ditolak'): ?>
                                    <div class="alert alert-danger mt-2 py-2 small">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        <strong>SPR Ditolak.</strong>
                                        <?php
                                        // Tampilkan catatan penolak terakhir
                                        foreach (['logistik_catatan','kadep_sc_catatan','admin_stock_catatan','koor_sc_catatan'] as $f) {
                                            if (!empty($spr[$f])) { echo '<br>' . htmlspecialchars($spr[$f]); break; }
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
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
