<?php $this->load->view('content/sales/retur_custom_css'); ?>
<?php /* views/content/sales/retur/activity_log.php */ ?>
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
                        <h1 class="m-0"><i class="fas fa-history mr-2 text-primary"></i> Activity Log Modul Retur</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Activity Log</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php
                $jobdesk = strtoupper((string)($this->session->userdata('jobdesk') ?? ''));
                $is_sc = in_array($jobdesk, ['SC','SALESCOUNTER','ADMIN']);
                $is_koor = in_array($jobdesk, ['MANAGERSC','ADMINSC','ADMIN']);
                $is_admretur = in_array($jobdesk, ['ADMRETUR','ADMINSTOCK','ADMIN']);
                $is_kadep = in_array($jobdesk, ['KADEPSC','KADEP','ADMIN','MANAGER','KADEPUB']);
                $is_logistik = in_array($jobdesk, ['LOGISTIK','LOGISTIC','LOGISTICS','ADMIN']);
                $is_collection  = in_array($jobdesk, ['COLLECTION','KOLEKTOR','ADMIN']);
                $is_kasir       = in_array($jobdesk, ['KASIR','ADMIN']);
                $is_admpnj      = in_array($jobdesk, ['ADMPNJ','ADMIN']);
                ?>
                <div class="row mb-3">
                    <div class="col-auto">
                        <?php if ($is_admpnj || $is_logistik || $is_sc || $is_koor || $is_kadep): ?>
                            <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-outline-danger mr-2">
                                <i class="fas fa-file-invoice"></i> Daftar SPR
                            </a>
                        <?php endif; ?>

                        <?php if ($is_admretur || $is_collection || $is_kasir): ?>
                            <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-outline-primary mr-2">
                                <i class="fas fa-undo-alt"></i> Daftar Retur Penjualan
                            </a>
                        <?php endif; ?>

                        <a href="<?= base_url('retur_penjualan/activity_log') ?>" class="btn btn-success active">
                            <i class="fas fa-history"></i> Log Aktivitas
                        </a>
                    </div>
                </div>

                <!-- TABLE CARD -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list-alt mr-1"></i> Log Aktivitas Modul SPR & Retur Penjualan</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover small" id="table-log">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center" style="width: 5%">No</th>
                                        <th style="width: 15%">Waktu Aktivitas</th>
                                        <th style="width: 15%">Operator</th>
                                        <th style="width: 15%">No. Referensi</th>
                                        <th style="width: 15%">Aksi</th>
                                        <th style="width: 15%">Perubahan Status</th>
                                        <th style="width: 20%">Catatan / Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    $aksi_map = [
                                        'create_draft'        => ['primary', 'Draft SPR Dibuat'],
                                        'create_submit'       => ['success', 'SPR Diajukan'],
                                        'edit_draft'          => ['info',    'Edit Draft SPR'],
                                        'edit_submit'         => ['success', 'SPR Diajukan Kembali'],
                                        'submit'              => ['success', 'SPR Diajukan'],
                                        'edit_stock'          => ['warning', 'Edit SPR Admin Retur'],
                                        'koor_verify'         => ['primary', 'Verifikasi Manager SC'],
                                        'koor_reject'         => ['danger',  'Ditolak Manager SC'],
                                        'admretur_check'   => ['primary', 'Dicek Admin Retur'],
                                        'admretur_reject'  => ['danger',  'Ditolak Admin Retur'],
                                        'kadep_approve'       => ['success', 'Disetujui Kadep SC'],
                                        'kadep_reject'        => ['danger',  'Ditolak Kadep SC'],
                                        'logistik_process'    => ['success', 'Selesai di Logistik'],
                                        'retur_create'        => ['success', 'Retur Dibuat (Logistik)'],
                                        'retur_edit'          => ['info',    'Edit Retur Penjualan'],
                                        'retur_edit_submit'   => ['success', 'Retur Diajukan Kembali'],
                                        'retur_submit'        => ['success', 'Retur Diajukan Kembali'],
                                        'retur_verify'        => ['success', 'Retur Diverifikasi'],
                                        'retur_reject'        => ['danger',  'Retur Ditolak'],
                                        'retur_collection'    => ['primary', 'Proses Collection'],
                                        'retur_kasir'         => ['success', 'Selesai di Kasir']
                                    ];

                                    foreach ($logs as $log): 
                                        $tipe_ref   = $log['tipe_referensi'];
                                        $badge_tipe = $tipe_ref === 'spr' ? 'badge-info' : 'badge-primary';
                                        
                                        $action_key = $log['aksi'];
                                        $act_badge  = $aksi_map[$action_key][0] ?? 'secondary';
                                        $act_label  = $aksi_map[$action_key][1] ?? $action_key;

                                        $status_awal  = $log['status_awal'] ? str_replace('_', ' ', $log['status_awal']) : '-';
                                        $status_akhir = $log['status_akhir'] ? str_replace('_', ' ', $log['status_akhir']) : '-';
                                    ?>
                                        <tr>
                                            <td class="text-center font-weight-bold"><?= $no++ ?></td>
                                            <td>
                                                <i class="far fa-clock mr-1 text-muted"></i>
                                                <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-user-circle mr-1 text-muted"></i>
                                                <strong><?= htmlspecialchars($log['user_by']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge <?= $badge_tipe ?> px-2 py-1 mr-1"><?= strtoupper($tipe_ref) ?></span>
                                                <span class="font-weight-bold text-dark"><?= htmlspecialchars($log['no_referensi']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $act_badge ?> px-2 py-1"><?= $act_label ?></span>
                                            </td>
                                            <td class="text-nowrap text-muted">
                                                <span class="small bg-light border px-1 rounded"><?= htmlspecialchars($status_awal) ?></span>
                                                <i class="fas fa-arrow-right mx-1 text-secondary small"></i>
                                                <span class="small bg-light border px-1 rounded font-weight-bold text-dark"><?= htmlspecialchars($status_akhir) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($log['catatan']): ?>
                                                    <span class="font-italic text-muted">"<?= htmlspecialchars($log['catatan']) ?>"</span>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<!-- DataTables & Plugins -->
<script>
$(document).ready(function () {
    $('#table-log').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[1, "desc"]], // Urutkan berdasarkan kolom waktu aktivitas desc
        "language": {
            "search": "Cari Log:",
            "lengthMenu": "Tampilkan _MENU_ entri",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
            "infoFiltered": "(difilter dari _MAX_ total entri)",
            "zeroRecords": "Tidak ada data log ditemukan",
            "paginate": {
                "next": "Berikutnya",
                "previous": "Sebelumnya"
            }
        }
    });
});
</script>
