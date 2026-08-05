<link rel="stylesheet" href="<?= base_url('assets/dist/css/retur-custom.css') ?>"><?php /* views/content/sales/retur/retur_list.php */ ?>
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
                        <h1 class="m-0"><i class="fas fa-undo-alt mr-2 text-primary"></i> Retur Penjualan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">Retur Penjualan</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

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
                $is_mngacc      = in_array($jobdesk, ['MANAGERACC','ADMIN']);
                $is_mngse       = in_array($jobdesk, ['MANAGERSE','ADMIN']);
                $is_dirop       = in_array($jobdesk, ['DIREKTUROP','ADMIN']);
                $is_dirut       = in_array($jobdesk, ['DIREKTURUTAMA','ADMIN']);
                $is_kadepub     = in_array($jobdesk, ['KADEPUB','ADMIN']);
                ?>
                <div class="row mb-3">
                    <div class="col-auto">
                        <?php if ($is_admpnj || $is_logistik || $is_sc || $is_koor || $is_kadep): ?>
                            <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-outline-danger mr-1">
                                <i class="fas fa-file-invoice"></i> Daftar SPR
                            </a>
                        <?php endif; ?>

                        <?php if ($is_admretur || $is_collection || $is_kasir || $is_admlpb2 || $is_mngacc || $is_mngse || $is_dirop || $is_dirut || $is_kadepub || $is_koor): ?>
                            <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-primary active mr-1">
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
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <form method="get" action="<?= base_url('retur_penjualan/retur') ?>">
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
                                    <label class="small mb-0">Status Retur</label>
                                    <select name="status" class="form-control form-control-sm">
                                        <option value="">-- Semua --</option>
                                        <?php
                                        $status_opts = [
                                            'menunggu_verifikasi'    => 'Menunggu Admin Retur',
                                            'retur_menunggu_kadepub' => 'Menunggu Kadep UB',
                                            'retur_menunggu_mngacc'  => 'Menunggu Manager Account',
                                            'retur_menunggu_mngsc'  => 'Menunggu Manager SC',
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
                                    <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-secondary btn-sm">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL RETUR PENJUALAN -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i> Daftar Retur Penjualan</h3>
                        <div class="card-tools">
                            <?php 
                            $is_approver = in_array($jobdesk, ['MANAGERSC', 'ADMINSC', 'ADMRETUR', 'ADMINSTOCK', 'KADEPSC', 'KADEP', 'MANAGER', 'COLLECTION', 'KOLEKTOR', 'KASIR', 'ADMIN', 'ADMPNJ', 'KADEPUB', 'MANAGERACC', 'MANAGERSE', 'DIREKTUROP', 'DIREKTURUTAMA']);
                            if ($is_approver): 
                            ?>
                                <a href="<?= base_url('retur_penjualan/retur/history') ?>" class="btn btn-xs btn-outline-light mr-2">
                                    <i class="fas fa-history"></i> Riwayat Persetujuan
                                </a>
                            <?php endif; ?>
                            <span class="badge badge-light"><?= count($retur_list) ?> Retur</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0" id="tabelReturCollection">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Customer</th>
                                        <th class="text-center">Jumlah Retur Aktif</th>
                                        <th class="text-right">Total Saldo Retur Aktif</th>
                                        <th class="text-center no-sort">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($retur_list)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                Tidak ada data Retur Penjualan
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php
                                        // Group by Customer
                                        $grouped = [];
                                        foreach ($retur_list as $r) {
                                            $cust_id = $r['kd_customer'];
                                            
                                            $nama_cust = !empty($r['nama_customer']) ? $r['nama_customer'] : (!empty($r['nama_customer_master']) ? $r['nama_customer_master'] : 'Tanpa Customer');
                                            
                                            if (!isset($grouped[$cust_id])) {
                                                $grouped[$cust_id] = [
                                                    'kd_customer' => $cust_id,
                                                    'nama_customer' => $nama_cust,
                                                    'total_saldo' => 0,
                                                    'returns' => []
                                                ];
                                            }
                                            $grouped[$cust_id]['returns'][] = $r;
                                            
                                            $sisa = isset($r['sisa_saldo_retur']) ? $r['sisa_saldo_retur'] : (isset($r['total_nilai']) ? $r['total_nilai'] : 0);
                                            $grouped[$cust_id]['total_saldo'] += (float)$sisa;
                                        }

                                        foreach ($grouped as $g):
                                            $first_retur_id = !empty($g['returns']) ? $g['returns'][0]['id_retur'] : 0;
                                        ?>
                                        <tr>
                                            <td class="font-weight-bold text-lg align-middle"><?= htmlspecialchars($g['nama_customer']) ?></td>
                                            <td class="text-center align-middle h5"><span class="badge badge-secondary"><?= count($g['returns']) ?> Retur</span></td>
                                            <td class="text-right align-middle text-success font-weight-bold h5">Rp <?= number_format($g['total_saldo'], 0, ',', '.') ?></td>
                                            <td class="text-center align-middle">
                                                <?php if ($first_retur_id): ?>
                                                <a href="<?= base_url('retur_penjualan/retur/collection/' . $first_retur_id) ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-file-invoice"></i> Proses Collection
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
    $('#tabelReturCollection').DataTable({
        responsive: true,
        autoWidth:  false,
        pageLength: 25,
        order:      [[0, 'asc']], // Sort by Customer Name
        columnDefs: [{ orderable: false, targets: [3] }], // Aksi is unorderable
        language: {
            search:      "Cari Customer:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable:  "Tidak ada data Retur Penjualan",
            paginate:    { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });
});
</script>
