<style>
    .timeline-od { border-left: 3px solid #dee2e6; padding-left: 16px; margin-left: 8px; }
    .timeline-od .tl-step { margin-bottom: 16px; position: relative; }
    .timeline-od .tl-step::before {
        content: '';
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #dee2e6;
        position: absolute;
        left: -23px;
        top: 3px;
    }
    .timeline-od .tl-step.done::before   { background: #28a745; }
    .timeline-od .tl-step.active::before { background: #ffc107; }
    .timeline-od .tl-step.reject::before { background: #dc3545; }
    .table-det th { background: #f8f9fa; font-size: 14px; padding: 8px !important; }
    .table-det td { font-size: 14px; padding: 8px !important; }
</style>

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
                            <i class="fas fa-file-alt mr-2 text-info"></i>
                            Detail Pengajuan OD-<?= $pengajuan['id'] ?>
                        </h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <ol class="breadcrumb float-sm-right mb-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales/C_PengajuanOD') ?>">Pengajuan OD</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php foreach (['success'=>'success','error'=>'danger'] as $k=>$c): ?>
                    <?php if ($msg = $this->session->flashdata($k)): ?>
                        <div class="alert alert-<?= $c ?> alert-dismissible">
                            <?= $msg ?>
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="row">
                    <!-- MAIN CARD LEFT -->
                    <div class="col-lg-8">
                        <div class="card shadow" style="border:2px solid #17a2b8;">
                            <div class="card-header d-flex justify-content-between align-items-center py-2"
                                 style="background:linear-gradient(135deg,#138496,#17a2b8);color:#fff;">
                                <div>
                                    <div style="font-size:1.1rem;font-weight:700;">PENGAJUAN OVERDUE</div>
                                    <div style="font-size:11px;opacity:.85;">PT. Karisma Indoagro Universal</div>
                                </div>
                                <?php
                                $badge_map = [
                                    'pending_mngsc'   => ['warning', 'Menunggu Manager SC'],
                                    'pending_mngtc'   => ['info',    'Menunggu Manager TC'],
                                    'pending_kadepsc' => ['info',    'Menunggu Kadep SC'],
                                    'approved'        => ['success', 'Disetujui / Selesai'],
                                    'rejected'        => ['danger',  'Ditolak'],
                                ];
                                $bm = $badge_map[$pengajuan['status']] ?? ['secondary', $pengajuan['status']];
                                ?>
                                <span class="badge badge-<?= $bm[0] ?> px-3 py-2" style="font-size:12px;"><?= $bm[1] ?></span>
                            </div>
                            
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-3" style="font-size:13px;">
                                    <tr>
                                        <td style="width:140px;" class="font-weight-bold">Dibuat oleh</td>
                                        <td>: <?= htmlspecialchars((string)$pengajuan['create_by']) ?></td>
                                        <td style="width:140px;" class="font-weight-bold">Customer</td>
                                        <td>: <strong><?= htmlspecialchars((string)$pengajuan['customer_name']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Tgl Pengajuan</td>
                                        <td>: <?= date('d/m/Y', strtotime($pengajuan['tanggal_pengajuan'])) ?></td>
                                        <td class="font-weight-bold">Tgl Jatuh Tempo Baru</td>
                                        <td>: <strong><?= date('d/m/Y', strtotime($pengajuan['target_tanggal_jatuh_tempo'])) ?></strong></td>
                                    </tr>
                                </table>

                                <p class="font-weight-bold mb-1">Pesanan barang-barang sebagai berikut :</p>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" style="font-size: 13px;">
                                        <thead class="text-center bg-light">
                                            <tr>
                                                <th>No.</th>
                                                <th>BARANG</th>
                                                <th>Jumlah</th>
                                                <th>Tgl Faktur</th>
                                                <th>No Faktur</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1; 
                                            $total = 0;
                                            
                                            // Calculate rowspans
                                            $faktur_counts = [];
                                            foreach ($details as $d) {
                                                if (!isset($faktur_counts[$d['no_faktur']])) {
                                                    $faktur_counts[$d['no_faktur']] = 0;
                                                }
                                                $faktur_counts[$d['no_faktur']]++;
                                            }
                                            
                                            $printed_faktur = [];
                                            
                                            foreach ($details as $i => $d) : 
                                                $subtotal = (float)$d['total_harga'];
                                                $total += $subtotal;
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= htmlspecialchars((string)$d['nama_barang']) ?></td>
                                                <td class="text-right"><?= number_format($subtotal, 2, '.', ',') ?></td>
                                                
                                                <?php if (!isset($printed_faktur[$d['no_faktur']])) : ?>
                                                    <td rowspan="<?= $faktur_counts[$d['no_faktur']] ?>" class="text-center" style="vertical-align: middle;">
                                                        <?= date('d-M-y', strtotime($d['tanggal_faktur'])) ?>
                                                    </td>
                                                    <td rowspan="<?= $faktur_counts[$d['no_faktur']] ?>" style="vertical-align: middle;">
                                                        <?= $d['no_faktur'] ?>
                                                    </td>
                                                    <?php $printed_faktur[$d['no_faktur']] = true; ?>
                                                <?php endif; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <tr class="font-weight-bold bg-light">
                                                <td colspan="2" class="text-center">Total</td>
                                                <td class="text-right"><?= number_format($total, 2, '.', ',') ?></td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3" style="font-size: 14px;">
                                    <strong>CATATAN :</strong><br>
                                    <div>
                                        <?= nl2br(htmlspecialchars((string)$pengajuan['catatan'])) ?>
                                    </div>
                                </div>

                                <?php 
                                $lampiran = $pengajuan['lampiran_sc'] ?: $pengajuan['lampiran_mngtc'];
                                if ($lampiran) : 
                                ?>
                                <div class="mt-3">
                                    <strong>Lampiran:</strong><br>
                                    <button type="button" class="btn btn-sm btn-info mb-1" data-toggle="modal" data-target="#modal-lampiran-preview">
                                        <i class="fas fa-image"></i> Lihat Lampiran
                                    </button>
                                </div>

                                <!-- Modal Preview Lampiran -->
                                <div class="modal fade" id="modal-lampiran-preview" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fas fa-paperclip mr-1"></i> Lampiran Pengajuan OD</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-center p-3">
                                                <?php 
                                                $ext = strtolower(pathinfo($lampiran, PATHINFO_EXTENSION));
                                                if (in_array($ext, ['pdf'])) :
                                                ?>
                                                    <iframe src="<?= base_url($lampiran) ?>" style="width: 100%; height: 500px;" frameborder="0"></iframe>
                                                <?php else : ?>
                                                    <img src="<?= base_url($lampiran) ?>" class="img-fluid rounded shadow" style="max-height: 70vh;">
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="<?= base_url($lampiran) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-external-link-alt mr-1"></i> Buka di Tab Baru</a>
                                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="mt-4">
                                    <?php
                                    $show_approve = false;
                                    if ($pengajuan['status'] == 'pending_mngsc' && in_array($user['jobdesk'], ['MANAGERSC', 'ADMIN'])) $show_approve = true;
                                    if ($pengajuan['status'] == 'pending_mngtc' && in_array($user['jobdesk'], ['MANAGERTC', 'ADMIN'])) $show_approve = true;
                                    if ($pengajuan['status'] == 'pending_kadepsc' && in_array($user['jobdesk'], ['KADEPSC', 'ADMIN'])) $show_approve = true;
                                    ?>

                                    <?php if ($show_approve) : ?>
                                        <a href="<?= base_url('sales/C_PengajuanOD/approval/'.$pengajuan['id']) ?>" class="btn btn-success mr-2">
                                            <i class="fas fa-check-circle"></i> Proses Persetujuan
                                        </a>
                                    <?php endif; ?>

                                    <?php if (in_array($pengajuan['status'], ['pending_mngsc', 'rejected']) && in_array($user['jobdesk'], ['SC', 'ADMINSC', 'ADMIN'])) : ?>
                                        <a href="<?= base_url('sales/C_PengajuanOD/edit/'.$pengajuan['id']) ?>" class="btn btn-warning mr-2">
                                            <i class="fas fa-edit"></i> Edit Pengajuan
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?= base_url('sales/C_PengajuanOD') ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- RIGHT TIMELINE SIDEBAR -->
                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header bg-dark text-white py-2">
                                <h3 class="card-title"><i class="fas fa-tasks mr-1"></i> Status Proses Pengajuan OD</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php
                                $st = $pengajuan['status'];
                                $max_tempo = isset($pengajuan['max_tempo_baru']) ? $pengajuan['max_tempo_baru'] : 0;
                                if (!empty($pengajuan['fakturs'])) {
                                    foreach ($pengajuan['fakturs'] as $fk) {
                                        if ($fk['tempo_baru'] > $max_tempo) {
                                            $max_tempo = $fk['tempo_baru'];
                                        }
                                    }
                                }

                                $steps = [
                                    [
                                        'label' => 'Dibuat Sales / SC',
                                        'icon'  => 'file-alt',
                                        'by'    => $pengajuan['create_by'],
                                        'at'    => $pengajuan['create_at'],
                                        'note'  => $pengajuan['catatan'],
                                        'is_done' => true,
                                        'is_reject' => false,
                                        'is_active' => false
                                    ],
                                    [
                                        'label' => 'Persetujuan Manager SC',
                                        'icon'  => 'user-check',
                                        'by'    => $pengajuan['approval_mngsc_by'],
                                        'at'    => $pengajuan['approval_mngsc_at'],
                                        'note'  => $pengajuan['catatan_mngsc'],
                                        'is_done' => !empty($pengajuan['approval_mngsc_by']) && !($st == 'rejected' && empty($pengajuan['approval_mngtc_by']) && empty($pengajuan['approval_kadepsc_by'])),
                                        'is_reject' => ($st == 'rejected' && !empty($pengajuan['approval_mngsc_by']) && empty($pengajuan['approval_mngtc_by']) && empty($pengajuan['approval_kadepsc_by'])),
                                        'is_active' => ($st == 'pending_mngsc')
                                    ],
                                    [
                                        'label' => 'Persetujuan Manager TC',
                                        'icon'  => 'user-cog',
                                        'by'    => $pengajuan['approval_mngtc_by'],
                                        'at'    => $pengajuan['approval_mngtc_at'],
                                        'note'  => $pengajuan['catatan_mngtc'],
                                        'is_done' => !empty($pengajuan['approval_mngtc_by']) && !($st == 'rejected' && empty($pengajuan['approval_kadepsc_by'])),
                                        'is_reject' => ($st == 'rejected' && !empty($pengajuan['approval_mngtc_by']) && empty($pengajuan['approval_kadepsc_by'])),
                                        'is_active' => ($st == 'pending_mngtc')
                                    ]
                                ];

                                if ($max_tempo > 90 || !empty($pengajuan['approval_kadepsc_by']) || $st == 'pending_kadepsc') {
                                    $steps[] = [
                                        'label' => 'Persetujuan Kadep SC (>90 Hari)',
                                        'icon'  => 'user-shield',
                                        'by'    => $pengajuan['approval_kadepsc_by'],
                                        'at'    => $pengajuan['approval_kadepsc_at'],
                                        'note'  => $pengajuan['catatan_kadepsc'],
                                        'is_done' => ($st == 'approved'),
                                        'is_reject' => ($st == 'rejected' && !empty($pengajuan['approval_kadepsc_by'])),
                                        'is_active' => ($st == 'pending_kadepsc')
                                    ];
                                }
                                ?>

                                <div class="timeline-od">
                                    <?php foreach ($steps as $step): 
                                        if (isset($step['is_reject']) && $step['is_reject']) {
                                            $step_class = 'reject';
                                            $icon_color = '#dc3545';
                                        } elseif (isset($step['is_done']) && $step['is_done']) {
                                            $step_class = 'done';
                                            $icon_color = '#28a745';
                                        } elseif (isset($step['is_active']) && $step['is_active']) {
                                            $step_class = 'active';
                                            $icon_color = '#ffc107';
                                        } else {
                                            $step_class = '';
                                            $icon_color = '#6c757d'; // gray
                                        }
                                    ?>
                                    <div class="tl-step <?= $step_class ?>">
                                        <div style="font-size:12px; font-weight:600;">
                                            <i class="fas fa-<?= $step['icon'] ?> mr-1" style="color:<?= $icon_color ?>;"></i>
                                            <?= $step['label'] ?>
                                        </div>
                                        <?php if ($step['by']): ?>
                                            <div class="small text-muted mt-1">
                                                Oleh: <strong><?= htmlspecialchars($step['by']) ?></strong><br>
                                                Tgl: <?= date('d/m/Y H:i', strtotime($step['at'])) ?>
                                            </div>
                                            <?php if ($step['note']): ?>
                                                <div class="small bg-light p-1 mt-1 border rounded">
                                                    <em>Catatan: <?= htmlspecialchars($step['note']) ?></em>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="small text-muted mt-1 font-italic">
                                                <?= (isset($step['is_reject']) && $step['is_reject']) ? 'Dibatalkan' : 'Menunggu proses' ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>
