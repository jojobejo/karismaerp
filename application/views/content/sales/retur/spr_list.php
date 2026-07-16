<!-- views/content/sales/retur/spr_list.php -->
<style>
    .spr-status-badge {
        display: inline-flex;
        justify-content: center;
        min-width: 110px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .3px;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .timeline-step {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
    }
    .timeline-step .dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .dot-done    { background: #28a745; }
    .dot-active  { background: #ffc107; }
    .dot-pending { background: #dee2e6; }
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
                        <h1 class="m-0"><i class="fas fa-undo-alt mr-2 text-danger"></i> Retur Penjualan (SPR)</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                            <li class="breadcrumb-item active">Retur Penjualan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- FLASH MESSAGE -->
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- SHORTCUT ROLE LINKS -->
                <div class="row mb-3">
                    <?php
                    $jobdesk = strtoupper((string)($this->session->userdata('jobdesk') ?? ''));
                    $is_sc = in_array($jobdesk, ['SC','SALESCOUNTER','ADMIN']);
                    $is_koor = in_array($jobdesk, ['MANAGERSC','ADMINSC','ADMIN']);
                    $is_admretur = in_array($jobdesk, ['ADMRETUR','ADMINSTOCK','ADMIN']);
                    $is_kadep = in_array($jobdesk, ['KADEPSC','KADEP','ADMIN','MANAGER','KADEPUB']);
                    $is_logistik = in_array($jobdesk, ['LOGISTIK','LOGISTIC','LOGISTICS','ADMIN']);
                    $is_collection  = in_array($jobdesk, ['COLLECTION','KOLEKTOR','ADMIN']);
                    $is_kasir       = in_array($jobdesk, ['KASIR','ADMIN']);
                    $is_admlpb2     = in_array($jobdesk, ['ADMLPB2','LOGISTIK','ADMIN']);
                    $is_admpnj      = in_array($jobdesk, ['ADMPNJ','ADMIN']);
                    $is_kadepub     = in_array($jobdesk, ['KADEPUB','ADMIN']);
                    $is_approval    = $is_koor || $is_admpnj || $is_kadep || $is_kadepub;
                    ?>
                    <div class="col-auto">
                        <?php if ($is_sc || $is_koor): ?>
                            <a href="<?= base_url('retur_penjualan/create') ?>" class="btn btn-danger mr-1">
                                <i class="fas fa-plus"></i> Buat SPR Baru
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($is_admpnj || $is_logistik || $is_sc || $is_koor || $is_kadep): ?>
                            <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-danger active mr-1">
                                <i class="fas fa-file-invoice"></i> Daftar SPR
                            </a>
                        <?php endif; ?>

                        <?php if ($is_admretur || $is_collection || $is_kasir || $is_admlpb2 || $is_koor || $is_kadep || $is_kadepub || in_array($jobdesk, ['MANAGERACC','MANAGERSE','DIREKTUROP','DIREKTURUTAMA'])): ?>
                            <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-outline-primary mr-1">
                                <i class="fas fa-undo-alt"></i> Daftar Retur Penjualan
                            </a>
                        <?php endif; ?>

                        <a href="<?= base_url('retur_penjualan/activity_log') ?>" class="btn btn-outline-success">
                            <i class="fas fa-history"></i> Log Aktivitas
                        </a>
                    </div>
                </div>

                <!-- FILTER -->
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <form method="get" action="<?= base_url('retur_penjualan') ?>">
                            <div class="row">
                                <div class="col-md-2">
                                    <label class="small mb-0">Dari Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="date1" value="<?= htmlspecialchars($filter['date1'] ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-0">Sampai Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="date2" value="<?= htmlspecialchars($filter['date2'] ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-0">Status</label>
                                    <select name="status" class="form-control form-control-sm">
                                        <option value="">-- Semua Status --</option>
                                        <?php
                                        $status_opts = [
                                            'draft'               => 'Draft',
                                            'diajukan'            => 'Diajukan',
                                            'diverifikasi_koor'   => 'Verifikasi Manager SC',
                                            'dicek_admretur'   => 'Dicek Admin Retur',
                                            'disetujui_kadep'     => 'Disetujui Kadep',
                                            'selesai'             => 'Selesai',
                                            'ditolak'             => 'Ditolak',
                                        ];
                                        foreach ($status_opts as $val => $lbl):
                                        ?>
                                            <option value="<?= $val ?>" <?= ($filter['status'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-success btn-sm mr-1"><i class="fas fa-search"></i> Tampil</button>
                                    <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-secondary btn-sm">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL SPR -->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i> Daftar Surat Perintah Retur (SPR)</h3>
                        <div class="card-tools">
                            <?php if ($is_approval): ?>
                                <a href="<?= base_url('retur_penjualan/history') ?>" class="btn btn-xs btn-outline-light mr-2">
                                    <i class="fas fa-history"></i> Riwayat Persetujuan
                                </a>
                            <?php endif; ?>
                            <span class="badge badge-light"><?= count($spr_list) ?> SPR</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0" id="tabelSPR">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No. SPR</th>
                                        <th>Tipe Retur</th>
                                        <th>Tanggal</th>
                                        <th>Customer</th>
                                        <th>Sales</th>
                                        <th class="text-center">Item</th>
                                        <th class="text-center">Progres Approval</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center no-sort">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($spr_list)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                Tidak ada data SPR
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php
                                        $badge_map = [
                                            'draft'               => ['secondary', 'Draft'],
                                            'diajukan'            => ['warning',   'Diajukan'],
                                            'menunggu_kadepub'    => ['warning',   'Wait Kadep UB'],
                                            'diverifikasi_koor'   => ['info',      'Verif. Koor'],
                                            'dicek_admretur'   => ['primary',   'Cek Penjualan'],
                                            'disetujui_kadep'     => ['indigo',    'Acc Kadep'],
                                            'selesai'             => ['success',   'Selesai'],
                                            'ditolak'             => ['danger',    'Ditolak'],
                                        ];

                                        // Bobot tiap status untuk progress bar
                                        $progress_map = [
                                            'draft'               => 0,
                                            'diajukan'            => 20,
                                            'menunggu_kadepub'    => 30,
                                            'diverifikasi_koor'   => 40,
                                            'dicek_admretur'   => 60,
                                            'disetujui_kadep'     => 80,
                                            'selesai'             => 100,
                                            'ditolak'             => 0,
                                        ];

                                        foreach ($spr_list as $row):
                                            $st    = $row['status'];
                                            $badge = $badge_map[$st][0] ?? 'secondary';
                                            $label = $badge_map[$st][1] ?? $st;
                                            $pct   = $progress_map[$st] ?? 0;
                                            $bar_c = $st === 'ditolak' ? 'danger' : ($st === 'selesai' ? 'success' : 'warning');
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('retur_penjualan/detail/' . $row['id_spr']) ?>" class="font-weight-bold text-danger">
                                                    <?= htmlspecialchars($row['no_spr']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if (($row['tipe_retur'] ?? 'biasa') === 'replace'): ?>
                                                    <span class="badge badge-success px-2 py-1">REPLACE</span>
                                                <?php elseif (($row['tipe_retur'] ?? 'biasa') === 'service'): ?>
                                                    <span class="badge badge-warning px-2 py-1">SERVICE</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary px-2 py-1">RETUR</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-nowrap"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                            <td><?= htmlspecialchars($row['nama_customer'] ?: $row['nama_customer_master'] ?: '-') ?></td>
                                            <td><?= htmlspecialchars($row['nama_sales'] ?? '-') ?></td>
                                            <td class="text-center"><?= (int)$row['jumlah_item'] ?></td>
                                            <td>
                                                <?php if ($st === 'ditolak'): ?>
                                                    <span class="text-danger small"><i class="fas fa-times-circle"></i> Ditolak</span>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 mr-1" style="height:14px; border-radius:3px;">
                                                            <div class="progress-bar bg-<?= $bar_c ?>"
                                                                 style="width:<?= $pct ?>%; font-size:10px; line-height:14px;">
                                                                <?= $pct > 20 ? $pct.'%' : '' ?>
                                                            </div>
                                                        </div>
                                                        <small class="text-nowrap text-<?= $bar_c === 'warning' ? 'dark' : $bar_c ?>" style="min-width:32px;"><?= $pct ?>%</small>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $badge ?> spr-status-badge"><?= $label ?></span>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="<?= base_url('retur_penjualan/detail/' . $row['id_spr']) ?>"
                                                   class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php
                                                $tindak_url = '';
                                                $tindak_label = '';
                                                $tindak_icon = '';
                                                $tindak_class = '';
                                                if ($st === 'diajukan' && $is_koor) {
                                                    $tindak_url = base_url('retur_penjualan/mngsc/verifikasi/' . $row['id_spr']);
                                                    $tindak_label = 'Verifikasi';
                                                    $tindak_icon = 'clipboard-check';
                                                    $tindak_class = 'warning';
                                                } elseif ($st === 'menunggu_kadepub' && $is_kadepub) {
                                                    $tindak_url = base_url('retur_penjualan/kadepub/verifikasi/' . $row['id_spr']);
                                                    $tindak_label = 'Verifikasi Jagung';
                                                    $tindak_icon = 'clipboard-check';
                                                    $tindak_class = 'success';
                                                } elseif ($st === 'diverifikasi_koor' && $is_admpnj) {
                                                    $tindak_url = base_url('retur_penjualan/admretur/cek/' . $row['id_spr']);
                                                    $tindak_label = 'Cek SPR';
                                                    $tindak_icon = 'boxes';
                                                    $tindak_class = 'info';
                                                } elseif ($st === 'dicek_admretur' && $is_kadep) {
                                                    $tindak_url = base_url('retur_penjualan/kadep_sc/approve/' . $row['id_spr']);
                                                    $tindak_label = 'Approve';
                                                    $tindak_icon = 'user-tie';
                                                    $tindak_class = 'primary';
                                                }
                                                // Logistik TIDAK punya tombol aksi Proses di sini
                                                ?>
                                                <?php if (!empty($tindak_url)): ?>
                                                    <a href="<?= $tindak_url ?>"
                                                       class="btn btn-sm btn-<?= $tindak_class ?>" title="<?= $tindak_label ?>">
                                                        <i class="fas fa-<?= $tindak_icon ?>"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($is_logistik && $st === 'disetujui_kadep'): ?>
                                                    <?php /* Logistik: hanya cetak SPR yg sudah disetujui kadep */ ?>
                                                    <a href="<?= base_url('retur_penjualan/print/' . $row['id_spr']) ?>"
                                                       class="btn btn-sm btn-success" title="Cetak SPR" target="_blank">
                                                        <i class="fas fa-print"></i> Cetak
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (in_array($st, ['draft', 'ditolak'], true) && ($row['create_by'] === ($user['nama'] ?? '') || $is_sc || $jobdesk === 'ADMIN')): ?>
                                                     <a href="<?= base_url('retur_penjualan/edit/' . $row['id_spr']) ?>"
                                                        class="btn btn-sm btn-primary" title="Edit SPR">
                                                         <i class="fas fa-edit"></i>
                                                     </a>
                                                 <?php endif; ?>
                                                 <?php if (in_array($st, ['draft', 'ditolak'], true) && $row['create_by'] === ($user['nama'] ?? '')): ?>
                                                     <a href="<?= base_url('retur_penjualan/submit/' . $row['id_spr']) ?>"
                                                        class="btn btn-sm btn-warning btn-submit-spr" title="Ajukan ke Manager SC"
                                                        data-nospr="<?= htmlspecialchars($row['no_spr']) ?>">
                                                         <i class="fas fa-paper-plane"></i>
                                                     </a>
                                                 <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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

<script>
$(document).ready(function () {
    $('#tabelSPR').DataTable({
        responsive: true,
        autoWidth:  false,
        pageLength: 25,
        order:      [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: [5, 7] }],
        language: {
            search:      "Cari:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable:  "Tidak ada data SPR",
            paginate:    { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });

    // Konfirmasi submit
    $(document).on('click', '.btn-submit-spr', function(e) {
        e.preventDefault();
        var url    = $(this).attr('href');
        var noSpr  = $(this).data('nospr');
        if (confirm('Ajukan SPR ' + noSpr + ' ke Manager SC? Setelah diajukan tidak dapat diubah.')) {
            window.location.href = url;
        }
    });
});
</script>
