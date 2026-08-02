<link rel="stylesheet" href="<?= base_url('assets/dist/css/retur-custom.css') ?>"><?php /* views/content/sales/retur/retur_detail.php */
if (!function_exists('hitung_durasi')) {
    function hitung_durasi($from, $to) {
        if (empty($from) || empty($to)) return null;
        $t1 = new DateTime($from);
        $t2 = new DateTime($to);
        $diff = $t1->diff($t2);
        
        $parts = [];
        if ($diff->d > 0) $parts[] = $diff->d . ' hari';
        if ($diff->h > 0) $parts[] = $diff->h . ' jam';
        if ($diff->i > 0) $parts[] = $diff->i . ' menit';
        if ($diff->s > 0 && empty($parts)) $parts[] = $diff->s . ' detik';
        
        return empty($parts) ? '0 menit' : implode(' ', $parts);
    }
}
?>
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
    .table-det th { background: #f8f9fa; font-size: 14px; padding: 8px !important; }
    .table-det td { font-size: 14px; padding: 8px !important; }
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
                    <div class="col-sm-6 text-right">
                        <a href="<?= base_url('retur_penjualan/retur/print/' . $retur['id_retur']) ?>" target="_blank" class="btn btn-primary btn-sm mr-2">
                            <i class="fas fa-print mr-1"></i> Cetak Retur
                        </a>
                        <ol class="breadcrumb float-sm-right d-inline-flex mb-0">
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
                <?php
                $jobdesk = strtoupper((string)($this->session->userdata('jobdesk') ?? ''));
                $is_admretur = in_array($jobdesk, ['ADMRETUR','ADMINSTOCK','ADMIN']);
                $is_collection  = in_array($jobdesk, ['COLLECTION','KOLEKTOR','ADMIN']);
                $is_kasir       = in_array($jobdesk, ['KASIR','ADMIN']);
                $is_admlpb2     = in_array($jobdesk, ['ADMLPB2','LOGISTIK','ADMIN']);
                $st = $retur['status_retur'];

                $show_approval_card = false;
                $approval_role_label = '';

                $is_koor = in_array($jobdesk, ['MANAGERSC','ADMINSC','ADMIN']);
                $is_kadep = in_array($jobdesk, ['KADEPSC','KADEP','ADMIN','MANAGER','KADEPUB']);
                $is_mngacc      = in_array($jobdesk, ['MANAGERACC','ADMIN']);
                $is_mngse       = in_array($jobdesk, ['MANAGERSE','ADMIN']);
                $is_dirop       = in_array($jobdesk, ['DIREKTUROP','ADMIN']);
                $is_dirut       = in_array($jobdesk, ['DIREKTURUTAMA','ADMIN']);
                $is_kadepub     = in_array($jobdesk, ['KADEPUB','ADMIN']);
                ?>

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
                                    'retur_menunggu_kadepub' => ['info',    'Menunggu Kadep UB'],
                                    'retur_menunggu_mngacc'  => ['info',    'Menunggu Manager Account'],
                                    'retur_menunggu_mngsc'  => ['info',    'Menunggu Manager SC'],
                                    'retur_menunggu_mngse'   => ['info',    'Menunggu Manager SE'],
                                    'retur_menunggu_kadepsc' => ['info',    'Menunggu Kadep SC'],
                                    'retur_menunggu_dirop'   => ['info',    'Menunggu Dirop'],
                                    'retur_menunggu_dirut'   => ['info',    'Menunggu Dirut'],
                                    'menunggu_verifikasi' => ['warning', 'Menunggu Admin Retur'],
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
                                    <tr>
                                        <td class="font-weight-bold">Tipe Retur</td>
                                        <td colspan="3">: 
                                            <?php if (($retur['tipe_retur'] ?? 'biasa') === 'replace'): ?>
                                                <span class="badge badge-success px-2 py-1">REPLACE (Ganti Barang)</span>
                                            <?php elseif (($retur['tipe_retur'] ?? 'biasa') === 'service'): ?>
                                                <span class="badge badge-warning px-2 py-1">SERVICE (Servis Barang)</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary px-2 py-1">RETUR (Refund/Potong Faktur)</span>
                                            <?php endif; ?>
                                        </td>
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
                                                <?php $hide_harga = ($jobdesk === 'ADMLPB2'); ?>
                                                <th class="text-right <?= $hide_harga ? 'd-none' : '' ?>">Harga</th>
                                                <th class="text-right <?= $hide_harga ? 'd-none' : '' ?>">Subtotal</th>
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
                                                    <td class="text-right <?= $hide_harga ? 'd-none' : '' ?>">Rp <?= number_format((float)$d['harga_satuan'], 0, ',', '.') ?></td>
                                                    <td class="text-right <?= $hide_harga ? 'd-none' : '' ?>">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="table-dark <?= $hide_harga ? 'd-none' : '' ?>">
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

                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    <?php if ($st === 'menunggu_verifikasi' && $is_admretur): ?>
                                        <a href="<?= base_url('retur_penjualan/retur/verifikasi/' . $retur['id_retur']) ?>" class="btn btn-warning mr-2">
                                            <i class="fas fa-clipboard-check"></i> Verifikasi (Admin Retur)
                                        </a>
                                    <?php elseif ($st === 'menunggu_collection' && $is_collection): ?>
                                        <a href="<?= base_url('retur_penjualan/retur/collection/' . $retur['id_retur']) ?>" class="btn btn-info mr-2">
                                            <i class="fas fa-handshake"></i> Proses Collection
                                        </a>
                                    <?php elseif ($st === 'menunggu_kasir' && $is_kasir): ?>
                                        <a href="<?= base_url('retur_penjualan/retur/kasir/' . $retur['id_retur']) ?>" class="btn btn-success mr-2">
                                            <i class="fas fa-cash-register"></i> Proses Kasir
                                        </a>
                                    <?php elseif ($st === 'ditolak' && $is_admlpb2): ?>
                                        <a href="<?= base_url('retur_penjualan/retur/edit/' . $retur['id_retur']) ?>" class="btn btn-primary mr-2">
                                            <i class="fas fa-edit"></i> Edit Retur
                                        </a>
                                        <a href="<?= base_url('retur_penjualan/retur/submit/' . $retur['id_retur']) ?>" class="btn btn-success mr-2"
                                           onclick="return confirm('Ajukan kembali Retur Penjualan ini ke Admin Retur?')">
                                            <i class="fas fa-paper-plane"></i> Ajukan Kembali
                                        </a>
                                    <?php elseif (
                                        ($st === 'retur_menunggu_mngacc' && $is_mngacc) ||
                                        ($st === 'retur_menunggu_mngsc' && $is_koor) ||
                                        ($st === 'retur_menunggu_kadepub' && $is_kadepub) ||
                                        ($st === 'retur_menunggu_mngse' && $is_mngse) ||
                                        ($st === 'retur_menunggu_kadepsc' && $is_kadep) ||
                                        ($st === 'retur_menunggu_dirop' && $is_dirop) ||
                                        ($st === 'retur_menunggu_dirut' && $is_dirut)
                                    ): ?>
                                        <a href="<?= base_url('retur_penjualan/retur/approve/' . $retur['id_retur']) ?>" class="btn btn-warning mr-2">
                                            <i class="fas fa-check-circle"></i> Persetujuan Retur
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
                        <?php if ($show_approval_card): ?>
                            <div class="card shadow border-warning mb-3">
                                <div class="card-header bg-warning text-dark py-2">
                                    <h3 class="card-title font-weight-bold" style="font-size:1.05rem;"><i class="fas fa-check-circle mr-1"></i> Persetujuan Retur</h3>
                                </div>
                                <div class="card-body p-3">
                                    <div class="alert alert-warning py-1 px-2 small mb-2">
                                        Menunggu persetujuan Anda sebagai <strong><?= $approval_role_label ?></strong>.
                                    </div>
                                    <form action="<?= base_url('retur_penjualan/retur/approve_simpan/' . $retur['id_retur']) ?>" method="post">
                                        <div class="form-group mb-2">
                                            <label class="small font-weight-bold mb-1">Catatan / Rekomendasi</label>
                                            <textarea class="form-control form-control-sm" name="catatan" rows="3" placeholder="Masukkan catatan..."></textarea>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <button type="submit" name="aksi" value="tolak" class="btn btn-danger btn-sm px-3" onclick="return confirm('Tolak Retur Penjualan ini?')">
                                                <i class="fas fa-times mr-1"></i> Tolak
                                            </button>
                                            <button type="submit" name="aksi" value="setuju" class="btn btn-success btn-sm px-3" onclick="return confirm('Setujui Retur Penjualan ini?')">
                                                <i class="fas fa-check mr-1"></i> Setuju
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card shadow">
                            <div class="card-header bg-dark text-white py-2">
                                <h3 class="card-title"><i class="fas fa-tasks mr-1"></i> Status Proses Retur</h3>
                            </div>
                            <div class="card-body p-3">
                                <?php
                                $total_duration = '';
                                $rejected_at_field = 'admretur_at_retur';
                                if ($st === 'ditolak') {
                                    foreach (['kadepub', 'mngacc', 'mngsc', 'mngse', 'kadepsc', 'dirop', 'dirut'] as $pfx) {
                                        if (!empty($retur[$pfx . '_at_retur'])) {
                                            $rejected_at_field = $pfx . '_at_retur';
                                        }
                                    }
                                }

                                if ($st === 'selesai' && !empty($retur['kasir_at'])) {
                                    $total_duration = 'Total Waktu (Selesai): <strong>' . hitung_durasi($retur['create_at_retur'], $retur['kasir_at']) . '</strong>';
                                } elseif ($st === 'ditolak' && !empty($retur[$rejected_at_field])) {
                                    $total_duration = 'Total Waktu (Sampai Ditolak): <strong>' . hitung_durasi($retur['create_at_retur'], $retur[$rejected_at_field]) . '</strong>';
                                } else {
                                    $total_duration = 'Total Waktu (Berjalan): <strong>' . hitung_durasi($retur['create_at_retur'], date('Y-m-d H:i:s')) . '</strong>';
                                }
                                ?>
                                <?php if ($total_duration !== ''): ?>
                                    <div class="alert alert-info py-2 px-3 mb-3 small">
                                        <i class="fas fa-clock mr-1"></i> <?= $total_duration ?>
                                    </div>
                                <?php endif; ?>
                                <?php
                                $steps_retur = [
                                    [
                                        'label' => 'Dibuat ADMLPB2',
                                        'icon' => 'file-alt',
                                        'done_statuses' => ['menunggu_verifikasi', 'retur_menunggu_mngacc', 'retur_menunggu_mngsc', 'retur_menunggu_kadepub', 'retur_menunggu_mngse', 'retur_menunggu_kadepsc', 'retur_menunggu_dirop', 'retur_menunggu_dirut', 'menunggu_collection', 'menunggu_kasir', 'selesai', 'ditolak'],
                                        'by' => $retur['create_by_retur'],
                                        'at' => $retur['create_at_retur'],
                                        'note' => $retur['catatan_logistik'] ?? null,
                                        'prev_at' => null
                                    ],
                                    [
                                        'label' => 'Verifikasi Admin Retur',
                                        'icon' => 'clipboard-check',
                                        'done_statuses' => ['retur_menunggu_mngacc', 'retur_menunggu_mngsc', 'retur_menunggu_kadepub', 'retur_menunggu_mngse', 'retur_menunggu_kadepsc', 'retur_menunggu_dirop', 'retur_menunggu_dirut', 'menunggu_collection', 'menunggu_kasir', 'selesai'],
                                        'by' => $retur['admretur_by_retur'],
                                        'at' => $retur['admretur_at_retur'],
                                        'note' => $retur['catatan_admretur'] ?? null,
                                        'prev_at' => 'create_at_retur'
                                    ],
                                    [
                                        'label' => 'Persetujuan Manager Account',
                                        'icon' => 'user-check',
                                        'done_statuses' => ['retur_menunggu_mngsc', 'retur_menunggu_kadepub', 'retur_menunggu_mngse', 'retur_menunggu_kadepsc', 'retur_menunggu_dirop', 'retur_menunggu_dirut', 'menunggu_collection', 'menunggu_kasir', 'selesai'],
                                        'by' => $retur['mngacc_by_retur'] ?? null,
                                        'at' => $retur['mngacc_at_retur'] ?? null,
                                        'note' => $retur['catatan_mngacc_retur'] ?? null,
                                        'prev_at' => 'admretur_at_retur'
                                    ],
                                    [
                                        'label' => 'Persetujuan Manager SC',
                                        'icon' => 'user-friends',
                                        'done_statuses' => ['retur_menunggu_kadepub', 'retur_menunggu_mngse', 'retur_menunggu_kadepsc', 'retur_menunggu_dirop', 'retur_menunggu_dirut', 'menunggu_collection', 'menunggu_kasir', 'selesai'],
                                        'by' => $retur['mngsc_by_retur'] ?? null,
                                        'at' => $retur['mngsc_at_retur'] ?? null,
                                        'note' => $retur['catatan_mngsc_retur'] ?? null,
                                        'prev_at' => 'mngacc_at_retur'
                                    ],
                                ];

                                if (!empty($retur['is_jagung'])) {
                                    $steps_retur[] = [
                                        'label' => 'Persetujuan Kadep UB',
                                        'icon' => 'user-shield',
                                        'done_statuses' => ['retur_menunggu_mngse', 'retur_menunggu_kadepsc', 'retur_menunggu_dirop', 'retur_menunggu_dirut', 'menunggu_collection', 'menunggu_kasir', 'selesai'],
                                        'by' => $retur['kadepub_by_retur'] ?? null,
                                        'at' => $retur['kadepub_at_retur'] ?? null,
                                        'note' => $retur['catatan_kadepub_retur'] ?? null,
                                        'prev_at' => 'mngsc_at_retur'
                                    ];
                                }

                                $prev_at_mngse = !empty($retur['is_jagung']) ? 'kadepub_at_retur' : 'mngsc_at_retur';

                                $steps_retur[] = [
                                    'label' => 'Persetujuan Manager SE',
                                    'icon' => 'user-tag',
                                    'done_statuses' => ['retur_menunggu_kadepsc', 'retur_menunggu_dirop', 'retur_menunggu_dirut', 'menunggu_collection', 'menunggu_kasir', 'selesai'],
                                    'by' => $retur['mngse_by_retur'] ?? null,
                                    'at' => $retur['mngse_at_retur'] ?? null,
                                    'note' => $retur['catatan_mngse_retur'] ?? null,
                                    'prev_at' => $prev_at_mngse
                                ];

                                $steps_retur[] = [
                                    'label' => 'Persetujuan Kadep SC',
                                    'icon' => 'user-tie',
                                    'done_statuses' => ['retur_menunggu_dirop', 'retur_menunggu_dirut', 'menunggu_collection', 'menunggu_kasir', 'selesai'],
                                    'by' => $retur['kadepsc_by_retur'] ?? null,
                                    'at' => $retur['kadepsc_at_retur'] ?? null,
                                    'note' => $retur['catatan_kadepsc_retur'] ?? null,
                                    'prev_at' => 'mngse_at_retur'
                                ];

                                $steps_retur[] = [
                                    'label' => 'Persetujuan Direktur Operasional',
                                    'icon' => 'user-cog',
                                    'done_statuses' => ['retur_menunggu_dirut', 'menunggu_collection', 'menunggu_kasir', 'selesai'],
                                    'by' => $retur['dirop_by_retur'] ?? null,
                                    'at' => $retur['dirop_at_retur'] ?? null,
                                    'note' => $retur['catatan_dirop_retur'] ?? null,
                                    'prev_at' => 'kadepsc_at_retur'
                                ];

                                $steps_retur[] = [
                                    'label' => 'Persetujuan Direktur Utama',
                                    'icon' => 'user-check',
                                    'done_statuses' => ['menunggu_collection', 'menunggu_kasir', 'selesai'],
                                    'by' => $retur['dirut_by_retur'] ?? null,
                                    'at' => $retur['dirut_at_retur'] ?? null,
                                    'note' => $retur['catatan_dirut_retur'] ?? null,
                                    'prev_at' => 'dirop_at_retur'
                                ];

                                $rejected_by = '';
                                $rejected_at = '';
                                $rejected_note = '';
                                if ($st === 'ditolak') {
                                    if (!empty($retur['admretur_by_retur']) && strpos($retur['update_by_retur'], $retur['admretur_by_retur']) !== false) {
                                        $rejected_by = $retur['admretur_by_retur'];
                                        $rejected_at = $retur['admretur_at_retur'];
                                        $rejected_note = $retur['catatan_admretur'];
                                    }
                                    foreach (['kadepub', 'mngacc', 'mngsc', 'mngse', 'kadepsc', 'dirop', 'dirut'] as $pfx) {
                                        if (!empty($retur[$pfx . '_by_retur']) && !empty($retur['catatan_' . $pfx . '_retur'])) {
                                            $rejected_by = $retur[$pfx . '_by_retur'];
                                            $rejected_at = $retur[$pfx . '_at_retur'];
                                            $rejected_note = $retur['catatan_' . $pfx . '_retur'];
                                        }
                                    }
                                }
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
                                            <?php
                                            $step_duration = '';
                                            if ($step['prev_at'] && !empty($step['at']) && !empty($retur[$step['prev_at']])) {
                                                $dur = hitung_durasi($retur[$step['prev_at']], $step['at']);
                                                if ($dur) {
                                                    $step_duration = '<span class="badge badge-light border ml-1 px-2 py-1 text-dark font-weight-bold" style="font-size:10px;"><i class="fas fa-stopwatch text-secondary mr-1"></i>' . $dur . '</span>';
                                                }
                                            }
                                            ?>
                                            <div style="font-size:11px; color:#555;">
                                                oleh: <strong><?= htmlspecialchars($step['by']) ?></strong>
                                                <?php if ($step['at']): ?> &mdash; <?= date('d/m/Y H:i', strtotime($step['at'])) ?><?php endif; ?>
                                                <?= $step_duration ?>
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
                                        <?php if (!empty($rejected_by)): ?>
                                            <br>Ditolak oleh: <strong><?= htmlspecialchars($rejected_by) ?></strong>
                                            <?php if (!empty($rejected_at)): ?>
                                                &mdash; <?= date('d/m/Y H:i', strtotime($rejected_at)) ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($rejected_note): ?>
                                            <br>Catatan: <em>"<?= htmlspecialchars($rejected_note) ?>"</em>
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
