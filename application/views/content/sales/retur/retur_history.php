<link rel="stylesheet" href="<?= base_url('assets/dist/css/retur-custom.css') ?>"><!-- views/content/sales/retur/retur_history.php -->
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
                        <h1 class="m-0"><i class="fas fa-history mr-2 text-secondary"></i> Riwayat Persetujuan Retur (<?= htmlspecialchars($role_label) ?>)</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan/retur') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Riwayat Persetujuan</li>
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
                    $is_admretur = in_array($jobdesk, ['ADMRETUR','ADMINSTOCK','ADMIN']);
                    $is_collection  = in_array($jobdesk, ['COLLECTION','KOLEKTOR','ADMIN']);
                    $is_kasir       = in_array($jobdesk, ['KASIR','ADMIN']);
                    $is_admpnj      = in_array($jobdesk, ['ADMPNJ','ADMIN']);
                    $is_kadep       = in_array($jobdesk, ['KADEPSC','KADEP','ADMIN','MANAGER','KADEPUB']);
                    $is_sc          = in_array($jobdesk, ['SC','SALESCOUNTER','ADMIN']);
                    $is_koor        = in_array($jobdesk, ['MANAGERSC','ADMINSC','ADMIN']);
                    $is_logistik    = in_array($jobdesk, ['LOGISTIK','LOGISTIC','LOGISTICS','ADMIN']);
                    $is_approval = !$is_collection && !$is_kasir;
                    ?>
                    <div class="col-auto">
                        <?php if ($is_approval || $is_admpnj || $is_kadep || $is_sc || $is_koor || $is_logistik): ?>
                        <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-outline-danger mr-1">
                            <i class="fas fa-file-invoice"></i> Daftar SPR
                        </a>
                        <?php endif; ?>
                        <?php if ($is_admretur || $is_collection || $is_kasir || $is_koor || $is_kadep): ?>
                        <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-outline-primary mr-1">
                            <i class="fas fa-undo-alt"></i> Daftar Retur Penjualan
                        </a>
                        <?php endif; ?>
                        <a href="<?= base_url('retur_penjualan/retur/history') ?>" class="btn btn-secondary active">
                            <i class="fas fa-history"></i> Riwayat Persetujuan Retur
                        </a>
                    </div>
                </div>

                <!-- FILTER -->
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Riwayat</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <form method="get" action="<?= base_url('retur_penjualan/retur/history') ?>">
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
                                    <label class="small mb-0">Status Akhir</label>
                                    <select name="status" class="form-control form-control-sm">
                                        <option value="">-- Semua Status --</option>
                                        <?php
                                        $status_opts = [
                                            'menunggu_verifikasi'    => 'Menunggu Admin Retur',
                                            'retur_menunggu_kadepub' => 'Menunggu Kadep UB',
                                            'retur_menunggu_mngacc'  => 'Menunggu Manager Account',
                                            'retur_menunggu_mngsc'   => 'Menunggu Manager SC',
                                            'retur_menunggu_mngse'   => 'Menunggu Manager SE',
                                            'retur_menunggu_kadepsc' => 'Menunggu Kadep SC',
                                            'retur_menunggu_dirop'   => 'Menunggu Dirop',
                                            'retur_menunggu_dirut'   => 'Menunggu Dirut',
                                            'menunggu_collection'    => 'Menunggu Collection',
                                            'menunggu_kasir'         => 'Menunggu Kasir',
                                            'selesai'                => 'Selesai',
                                            'ditolak'                => 'Ditolak',
                                        ];
                                        foreach ($status_opts as $val => $lbl):
                                        ?>
                                            <option value="<?= $val ?>" <?= ($filter['status'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-success btn-sm mr-1"><i class="fas fa-search"></i> Tampil</button>
                                    <a href="<?= base_url('retur_penjualan/retur/history') ?>" class="btn btn-secondary btn-sm">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL HISTORY -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i> Daftar Riwayat Retur Penjualan yang Anda Tindak</h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($retur_list) ?> Retur</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0 table-history" id="tabelHistory">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No. Retur</th>
                                        <th>Tanggal Retur</th>
                                        <th>Customer</th>
                                        <th>Sales</th>
                                        <th class="text-center">Item</th>
                                        <th>Catatan Anda</th>
                                        <th class="text-center">Status Akhir</th>
                                        <th class="text-center no-sort">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($retur_list)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-history fa-2x mb-2 d-block text-secondary"></i>
                                                Belum ada riwayat persetujuan/penolakan
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php
                                        $badge_map = [
                                            'menunggu_verifikasi'    => ['warning',  'Wait Admin Retur'],
                                            'retur_menunggu_kadepub' => ['info',     'Wait Kadep UB'],
                                            'retur_menunggu_mngacc'  => ['info',     'Wait Mng Account'],
                                            'retur_menunggu_mngsc'   => ['info',     'Wait Mng SC'],
                                            'retur_menunggu_mngse'   => ['info',     'Wait Mng SE'],
                                            'retur_menunggu_kadepsc' => ['info',     'Wait Kadep SC'],
                                            'retur_menunggu_dirop'   => ['info',     'Wait Dirop'],
                                            'retur_menunggu_dirut'   => ['info',     'Wait Dirut'],
                                            'menunggu_collection'    => ['warning',  'Wait Collection'],
                                            'menunggu_kasir'         => ['primary',  'Wait Kasir'],
                                            'selesai'                => ['success',  'Selesai'],
                                            'ditolak'                => ['danger',   'Ditolak'],
                                        ];

                                        foreach ($retur_list as $row):
                                            $st    = $row['status'];
                                            $badge = $badge_map[$st][0] ?? 'secondary';
                                            $label = $badge_map[$st][1] ?? $st;

                                            $my_catatan = '';
                                            if ($role === 'admretur') {
                                                $my_catatan = $row['catatan_admretur'];
                                            } elseif ($role === 'mngacc') {
                                                $my_catatan = $row['catatan_mngacc_retur'];
                                            } elseif ($role === 'mngsc') {
                                                $my_catatan = $row['catatan_mngsc_retur'];
                                            } elseif ($role === 'kadepub') {
                                                $my_catatan = $row['catatan_kadepub_retur'];
                                            } elseif ($role === 'mngse') {
                                                $my_catatan = $row['catatan_mngse_retur'];
                                            } elseif ($role === 'kadep_sc') {
                                                $my_catatan = $row['catatan_kadepsc_retur'];
                                            } elseif ($role === 'dirop') {
                                                $my_catatan = $row['catatan_dirop_retur'];
                                            } elseif ($role === 'dirut') {
                                                $my_catatan = $row['catatan_dirut_retur'];
                                            } elseif ($role === 'collection') {
                                                $my_catatan = $row['catatan_collection'];
                                            } elseif ($role === 'kasir') {
                                                $my_catatan = $row['catatan_kasir'];
                                            } else {
                                                $cats = [];
                                                if ($row['catatan_admretur']) $cats[] = 'Penjualan: '.$row['catatan_admretur'];
                                                if ($row['catatan_mngacc_retur']) $cats[] = 'Acc: '.$row['catatan_mngacc_retur'];
                                                if ($row['catatan_mngsc_retur']) $cats[] = 'Koor: '.$row['catatan_mngsc_retur'];
                                                if ($row['catatan_kadepub_retur']) $cats[] = 'Kadep UB: '.$row['catatan_kadepub_retur'];
                                                if ($row['catatan_mngse_retur']) $cats[] = 'SE: '.$row['catatan_mngse_retur'];
                                                if ($row['catatan_kadepsc_retur']) $cats[] = 'Kadep SC: '.$row['catatan_kadepsc_retur'];
                                                if ($row['catatan_dirop_retur']) $cats[] = 'Dirop: '.$row['catatan_dirop_retur'];
                                                if ($row['catatan_dirut_retur']) $cats[] = 'Dirut: '.$row['catatan_dirut_retur'];
                                                if ($row['catatan_collection']) $cats[] = 'Collection: '.$row['catatan_collection'];
                                                if ($row['catatan_kasir']) $cats[] = 'Kasir: '.$row['catatan_kasir'];
                                                $my_catatan = implode(' | ', $cats);
                                            }
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('retur_penjualan/retur/detail/' . $row['id_spr']) ?>" class="font-weight-bold text-primary">
                                                    <?= htmlspecialchars($row['no_spr']) ?>
                                                </a>
                                            </td>
                                            <td class="text-nowrap"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                            <td><?= htmlspecialchars($row['nama_customer'] ?: $row['nama_customer_master'] ?: '-') ?></td>
                                            <td><?= htmlspecialchars($row['nama_sales'] ?? '-') ?></td>
                                            <td class="text-center"><?= (int)$row['jumlah_item'] ?></td>
                                            <td><?= htmlspecialchars($my_catatan ?: '-') ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $badge ?> history-badge"><?= $label ?></span>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="<?= base_url('retur_penjualan/retur/detail/' . $row['id_spr']) ?>"
                                                   class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <a href="<?= base_url('retur_penjualan/retur/print/' . $row['id_spr']) ?>"
                                                   class="btn btn-sm btn-secondary" title="Print" target="_blank">
                                                    <i class="fas fa-print"></i> Print
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-light mt-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke List Retur
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
    $('#tabelHistory').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: [7] }],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable: "Tidak ada data riwayat",
            paginate: { first: "Pertama", last: "Terakhir", next: "Berikutnya", previous: "Sebelumnya" }
        }
    });
});
</script>
