<!-- views/content/sales/retur/_spr_queue_list_base.php -->
<!-- Di-include oleh koor_sc_list, admin_stock_list, kadep_sc_list, logistik_list -->
<?php
// Variabel yang dibutuhkan dari controller:
//   $page_title, $spr_list, $filter, $user, $role_label
//   $role_config:
//     [
//       'icon'        => string,
//       'color'       => string (bootstrap color),
//       'back_url'    => string,
//       'action_base' => string (url prefix untuk verifikasi/approve/cek/proses),
//       'action_label'=> string,
//       'empty_msg'   => string,
//     ]

$rc = $role_config ?? [];
$icon       = $rc['icon']        ?? 'clipboard-check';
$color      = $rc['color']       ?? 'warning';
$act_base   = $rc['action_base'] ?? '#';
$act_label  = $rc['action_label'] ?? 'Tindak';

$jobdesk    = strtoupper((string)($this->session->userdata('jobdesk') ?? ''));
$is_logistik = in_array($jobdesk, ['LOGISTIK','ADMIN']);

$badge_map = [
    'draft'               => ['secondary','Draft'],
    'diajukan'            => ['warning','Diajukan'],
    'diverifikasi_koor'   => ['info','Verif. Koor SC'],
    'dicek_admin_stock'   => ['primary','Cek Admin Stock'],
    'disetujui_kadep'     => ['success','Acc Kadep'],
    'selesai'             => ['success','Selesai'],
    'ditolak'             => ['danger','Ditolak'],
];
?>
<style>
    .queue-badge { display:inline-flex; justify-content:center; min-width:110px; font-size:11px; font-weight:600; padding:3px 8px; border-radius:4px; }
    .table-queue th { background:#f8f9fa; font-size:12px; border:1px solid #dee2e6; }
    .table-queue td { font-size:12px; border:1px solid #dee2e6; vertical-align:middle; }
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
                            <i class="fas fa-<?= $icon ?> mr-2 text-<?= $color ?>"></i>
                            <?= htmlspecialchars($role_label) ?>: Antrian SPR
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Antrian <?= htmlspecialchars($role_label) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- FLASH -->
                <?php foreach (['success'=>'success','error'=>'danger','warning'=>'warning'] as $k=>$c): ?>
                    <?php if ($msg = $this->session->flashdata($k)): ?>
                        <div class="alert alert-<?= $c ?> alert-dismissible">
                            <i class="fas fa-<?= $k==='success'?'check-circle':'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- FILTER -->
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter</h3>
                        <div class="card-tools"><button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button></div>
                    </div>
                    <div class="card-body py-2">
                        <form method="get">
                            <div class="row">
                                <div class="col-md-2">
                                    <label class="small mb-0">Dari Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="date1" value="<?= htmlspecialchars($filter['date1'] ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-0">Sampai Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="date2" value="<?= htmlspecialchars($filter['date2'] ?? '') ?>">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-success btn-sm mr-1"><i class="fas fa-search"></i> Tampil</button>
                                    <a href="?" class="btn btn-secondary btn-sm">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL ANTRIAN -->
                <div class="card">
                    <div class="card-header bg-<?= $color ?> <?= in_array($color,['warning']) ? 'text-dark' : 'text-white' ?>">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i> Antrian SPR — <?= htmlspecialchars($role_label) ?></h3>
                        <div class="card-tools">
                            <a href="<?= base_url('retur_penjualan/history') ?>" class="btn btn-xs <?= in_array($color,['warning']) ? 'btn-dark' : 'btn-outline-light' ?> mr-2">
                                <i class="fas fa-history"></i> Riwayat Persetujuan
                            </a>
                            <span class="badge badge-light"><?= count($spr_list) ?> SPR</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0 table-queue" id="tabelQueue">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No. SPR</th>
                                        <th>Tanggal</th>
                                        <th>Customer</th>
                                        <th>Sales</th>
                                        <th class="text-center">Item</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center no-sort">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($spr_list)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-check-double fa-2x mb-2 d-block text-success"></i>
                                                Tidak ada SPR dalam antrian ini
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($spr_list as $row):
                                            $bm = $badge_map[$row['status']] ?? ['secondary',$row['status']];
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('retur_penjualan/detail/' . $row['id_spr']) ?>" class="font-weight-bold text-danger">
                                                    <?= htmlspecialchars($row['no_spr']) ?>
                                                </a>
                                            </td>
                                            <td class="text-nowrap"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                            <td><?= htmlspecialchars($row['nama_customer'] ?: $row['nama_customer_master'] ?: '-') ?></td>
                                            <td><?= htmlspecialchars($row['nama_sales'] ?? '-') ?></td>
                                            <td class="text-center"><?= (int)$row['jumlah_item'] ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $bm[0] ?> queue-badge"><?= $bm[1] ?></span>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="<?= base_url($act_base . $row['id_spr']) ?>"
                                                   class="btn btn-sm btn-<?= $color ?>" title="<?= $act_label ?>">
                                                    <i class="fas fa-<?= $icon ?>"></i> <?= $act_label ?>
                                                </a>
                                                <?php if ($is_logistik): ?>
                                                    <a href="<?= base_url('retur_penjualan/print/' . $row['id_spr']) ?>"
                                                       class="btn btn-sm btn-secondary" title="Print" target="_blank">
                                                        <i class="fas fa-print"></i>
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

                <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-light mt-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke List SPR
                </a>

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
$(document).ready(function() {
    $('#tabelQueue').DataTable({
        responsive: true, autoWidth: false, pageLength: 25,
        order: [[1,'asc']],
        columnDefs: [{ orderable: false, targets: [6] }],
        language: {
            search:"Cari:", lengthMenu:"Tampilkan _MENU_ data",
            info:"Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords:"Tidak ada data", emptyTable:"Tidak ada antrian",
            paginate:{first:"Pertama",last:"Terakhir",next:"Berikutnya",previous:"Sebelumnya"}
        }
    });
});
</script>
