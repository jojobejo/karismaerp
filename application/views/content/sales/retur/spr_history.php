<!-- views/content/sales/retur/spr_history.php -->
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
    .history-badge {
        display: inline-flex;
        justify-content: center;
        min-width: 110px;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
    }
    .table-history th { background: #f8f9fa; font-size: 12px; border: 1px solid #dee2e6; }
    .table-history td { font-size: 12px; border: 1px solid #dee2e6; vertical-align: middle; }
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
                        <h1 class="m-0"><i class="fas fa-history mr-2 text-secondary"></i> Riwayat Persetujuan SPR (<?= htmlspecialchars($role_label) ?>)</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan') ?>">Retur Penjualan</a></li>
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
                    $is_admin_stock = in_array($jobdesk, ['ADMSTOCK','ADMINSTOCK','ADMIN']);
                    $is_collection  = in_array($jobdesk, ['COLLECTION','KOLEKTOR','ADMIN']);
                    $is_kasir       = in_array($jobdesk, ['KASIR','ADMIN']);
                    $is_admpnj      = in_array($jobdesk, ['ADMPNJ','ADMIN']);
                    $is_kadep       = in_array($jobdesk, ['KADEPSC','KADEP','ADMIN','MANAGER','KADEPUB']);
                    $is_sc          = in_array($jobdesk, ['SC','SALESCOUNTER','ADMIN']);
                    $is_koor        = in_array($jobdesk, ['KOORSC','ADMINSC','ADMIN']);
                    $is_logistik    = in_array($jobdesk, ['LOGISTIK','LOGISTIC','LOGISTICS','ADMIN']);
                    $is_approval = !$is_collection && !$is_kasir;
                    ?>
                    <div class="col-auto">
                        <?php if ($is_approval || $is_admpnj || $is_kadep || $is_sc || $is_koor || $is_logistik): ?>
                        <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-outline-danger mr-1">
                            <i class="fas fa-file-invoice"></i> Daftar SPR (Approval)
                        </a>
                        <?php endif; ?>
                        <?php if ($is_admin_stock || $is_collection || $is_kasir): ?>
                        <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-outline-primary mr-1">
                            <i class="fas fa-undo-alt"></i> Daftar Retur Penjualan
                        </a>
                        <?php endif; ?>
                        <a href="<?= base_url('retur_penjualan/history') ?>" class="btn btn-secondary active">
                            <i class="fas fa-history"></i> Riwayat Persetujuan
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
                        <form method="get" action="<?= base_url('retur_penjualan/history') ?>">
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
                                            'menunggu_kadepub'    => 'Menunggu Kadep UB',
                                            'diverifikasi_koor'   => 'Verifikasi Koor SC',
                                            'dicek_admin_stock'   => 'Dicek Admin Penjualan',
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
                                    <a href="<?= base_url('retur_penjualan/history') ?>" class="btn btn-secondary btn-sm">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL HISTORY -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i> Daftar Riwayat SPR yang Anda Tindak</h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($spr_list) ?> SPR</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0 table-history" id="tabelHistory">
                                <thead class="thead-dark">
                                    <tr>
                                        <th><?= ($role === 'collection' || $role === 'kasir') ? 'No. Retur' : 'No. SPR' ?></th>
                                        <th><?= ($role === 'collection' || $role === 'kasir') ? 'Tanggal Retur' : 'Tanggal SPR' ?></th>
                                        <th>Customer</th>
                                        <th>Sales</th>
                                        <th class="text-center">Item</th>
                                        <th>Catatan Anda</th>
                                        <th class="text-center">Status Akhir</th>
                                        <th class="text-center no-sort">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($spr_list)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-history fa-2x mb-2 d-block text-secondary"></i>
                                                Belum ada riwayat persetujuan/penolakan
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php
                                        $badge_map = [
                                            'draft'               => ['secondary', 'Draft'],
                                            'diajukan'            => ['warning',   'Diajukan'],
                                            'menunggu_kadepub'    => ['warning',   'Wait Kadep UB'],
                                            'diverifikasi_koor'   => ['info',      'Verif. Koor'],
                                            'dicek_admin_stock'   => ['primary',   'Cek Penjualan'],
                                            'disetujui_kadep'     => ['indigo',    'Acc Kadep'],
                                            'selesai'             => ['success',   'Selesai'],
                                            'ditolak'             => ['danger',    'Ditolak'],
                                            'menunggu_verifikasi' => ['warning',   'Wait Admin Stock'],
                                            'menunggu_collection' => ['info',      'Wait Collection'],
                                            'menunggu_kasir'      => ['primary',   'Wait Kasir'],
                                        ];

                                        $jobdesk = strtoupper((string)($this->session->userdata('jobdesk') ?? ''));
                                        $is_logistik = in_array($jobdesk, ['LOGISTIK','ADMIN']);

                                        foreach ($spr_list as $row):
                                            $st    = $row['status'];
                                            $badge = $badge_map[$st][0] ?? 'secondary';
                                            $label = $badge_map[$st][1] ?? $st;

                                            // Ambil catatan user yang logged-in berdasarkan field role-nya
                                            $my_catatan = '';
                                            if ($role === 'koor_sc') {
                                                $my_catatan = $row['koor_sc_catatan'];
                                            } elseif ($role === 'kadepub') {
                                                $my_catatan = $row['kadepub_catatan'];
                                            } elseif ($role === 'admin_stock') {
                                                $my_catatan = $row['admin_stock_catatan'];
                                            } elseif ($role === 'kadep_sc') {
                                                $my_catatan = $row['kadep_sc_catatan'];
                                            } elseif ($role === 'logistik') {
                                                $my_catatan = $row['logistik_catatan'];
                                            } elseif ($role === 'collection') {
                                                $my_catatan = $row['catatan_collection'];
                                            } elseif ($role === 'kasir') {
                                                $my_catatan = $row['catatan_kasir'];
                                            } else {
                                                // Admin atau general: show any matching non-empty
                                                $cats = [];
                                                if ($row['koor_sc_catatan']) $cats[] = 'Koor: '.$row['koor_sc_catatan'];
                                                if ($row['kadepub_catatan']) $cats[] = 'Kadep UB: '.$row['kadepub_catatan'];
                                                if ($row['admin_stock_catatan']) $cats[] = 'Penjualan: '.$row['admin_stock_catatan'];
                                                if ($row['kadep_sc_catatan']) $cats[] = 'Kadep: '.$row['kadep_sc_catatan'];
                                                if ($row['logistik_catatan']) $cats[] = 'Logistik: '.$row['logistik_catatan'];
                                                if ($row['catatan_collection']) $cats[] = 'Collection: '.$row['catatan_collection'];
                                                if ($row['catatan_kasir']) $cats[] = 'Kasir: '.$row['catatan_kasir'];
                                                $my_catatan = implode(' | ', $cats);
                                            }
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url(($role === 'collection' || $role === 'kasir') ? 'retur_penjualan/retur/detail/' . $row['id_spr'] : 'retur_penjualan/detail/' . $row['id_spr']) ?>" class="font-weight-bold text-danger">
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
                                                <a href="<?= base_url(($role === 'collection' || $role === 'kasir') ? 'retur_penjualan/retur/detail/' . $row['id_spr'] : 'retur_penjualan/detail/' . $row['id_spr']) ?>"
                                                   class="btn btn-sm btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <?php if ($is_logistik): ?>
                                                    <a href="<?= base_url('retur_penjualan/print/' . $row['id_spr']) ?>"
                                                       class="btn btn-sm btn-secondary" title="Print" target="_blank">
                                                        <i class="fas fa-print"></i> Print
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
