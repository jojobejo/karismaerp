<!-- views/content/sales/admin_sc_faktur_list.php -->
<style>
    #tabelAdminScFaktur_wrapper {
        padding: 0 6px 6px;
    }
    #tabelAdminScFaktur_wrapper .row:first-child,
    #tabelAdminScFaktur_wrapper .row:last-child {
        margin-left: 0;
        margin-right: 0;
        padding: 4px 8px;
    }
    #tabelAdminScFaktur_wrapper .dataTables_filter input {
        height: 30px;
        max-width: 180px;
        padding: 3px 8px;
    }
    .admin-sc-summary .info-box {
        min-height: 78px;
    }
    .admin-sc-summary .info-box-icon {
        flex: 0 0 58px;
        width: 58px;
        height: 58px;
        min-height: 58px;
        max-height: 58px;
        border-radius: 8px;
        font-size: 24px;
    }
    .admin-sc-summary .info-box-icon > i {
        line-height: 58px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-file-invoice mr-2"></i> Admin SC - Faktur Selesai
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order/admin_sc') ?>">Admin SC</a></li>
                            <li class="breadcrumb-item active">Faktur Selesai</li>
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
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-circle' : 'exclamation-triangle') ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="mb-3">
                    <a href="<?= base_url('sales_order/admin_sc') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Admin SC
                    </a>
                </div>

                <div class="card card-outline card-secondary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Faktur</h3>
                    </div>
                    <div class="card-body py-2">
                        <form action="<?= base_url('sales_order/admin_sc/faktur') ?>" method="post">
                            <div class="row">
                                <div class="col-md-2">
                                    <label class="small mb-0">Dari Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="date1"
                                           value="<?= htmlspecialchars($filter['date1'] ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-0">Sampai Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="date2"
                                           value="<?= htmlspecialchars($filter['date2'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="small mb-0">Customer</label>
                                    <select name="customer_id" class="form-control form-control-sm">
                                        <option value="">-- Semua Customer --</option>
                                        <?php foreach ($customers as $c): ?>
                                            <option value="<?= $c['id'] ?>"
                                                <?= ($filter['customer_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['nama_customer']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-success btn-sm mr-1">
                                        <i class="fas fa-search mr-1"></i> Tampil
                                    </button>
                                    <a href="<?= base_url('sales_order/admin_sc/faktur') ?>" class="btn btn-secondary btn-sm">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row admin-sc-summary">
                    <div class="col-6 col-md-3">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-info"><i class="fas fa-file-invoice"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Faktur</span>
                                <span class="info-box-number"><?= number_format(count($fakturs)) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-success"><i class="fas fa-boxes"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Qty</span>
                                <span class="info-box-number"><?= number_format((float)$total_qty, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Pajak</span>
                                <span class="info-box-number">Rp <?= number_format((float)$total_pajak, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-primary"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Grand Total</span>
                                <span class="info-box-number">Rp <?= number_format((float)$grand_total, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i> Daftar Faktur yang Sudah Dibuat
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($fakturs) ?> faktur</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover table-sm" id="tabelAdminScFaktur">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No Faktur</th>
                                    <th>No SO</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Rute</th>
                                    <th>Pembayaran</th>
                                    <th class="text-center">Tempo</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-center no-sort" style="min-width:95px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($fakturs)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Belum ada faktur selesai sesuai filter.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($fakturs as $f):
                                        $status = strtolower((string)($f['status'] ?? ''));
                                        $status_badge = [
                                            'confirmed'   => 'success',
                                            'draft'       => 'warning',
                                            'in_progress' => 'primary',
                                            'proses_do'   => 'info',
                                            'selesai'     => 'success',
                                            'completed'   => 'success',
                                        ];
                                        $status_label = [
                                            'confirmed'   => 'Confirmed',
                                            'draft'       => 'Draft',
                                            'in_progress' => 'In Progress',
                                            'proses_do'   => 'Proses DO',
                                            'selesai'     => 'Selesai',
                                            'completed'   => 'Completed',
                                        ];
                                        $payment = strtolower(trim((string)($f['cara_pembayaran'] ?? '')));
                                        $payment_label = [
                                            'cash'     => 'Cash',
                                            'transfer' => 'Transfer',
                                            'tempo'    => 'Tempo',
                                            'bg'       => 'BG',
                                            'bonus'    => 'Bonus',
                                        ][$payment] ?? ($payment !== '' ? strtoupper($payment) : '-');
                                        $tempo = $f['jtempo'] ?? $f['tempo'] ?? null;
                                        $rute = $f['so_kd_rute'] ?: ($f['customer_kd_rute'] ?? '');
                                    ?>
                                        <tr>
                                            <td class="font-weight-bold"><?= htmlspecialchars($f['no_faktur']) ?></td>
                                            <td>
                                                <?= htmlspecialchars($f['no_so'] ?? '-') ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?= !empty($f['tanggal_faktur']) ? date('d/m/Y', strtotime($f['tanggal_faktur'])) : '-' ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($f['customer_name'] ?: ($f['nama_customer'] ?? '-')) ?>
                                                <?php if (!empty($f['nama_kios'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($f['nama_kios']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $rute !== '' ? htmlspecialchars($rute) : '<span class="text-muted">-</span>' ?></td>
                                            <td><span class="badge badge-info"><?= htmlspecialchars($payment_label) ?></span></td>
                                            <td class="text-center">
                                                <?= ($tempo !== null && $tempo !== '') ? (int)$tempo . ' Hari' : '-' ?>
                                                <?php if (!empty($f['tanggal_jatuh_tempo'])): ?>
                                                    <br><small class="text-muted"><?= date('d/m/Y', strtotime($f['tanggal_jatuh_tempo'])) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $status_badge[$status] ?? 'secondary' ?>">
                                                    <?= htmlspecialchars($status_label[$status] ?? ($f['status'] ?? '-')) ?>
                                                </span>
                                            </td>
                                            <td class="text-right font-weight-bold">
                                                Rp <?= number_format((float)($f['grand_total'] ?? 0), 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <a href="<?= base_url('sales_order/detail_faktur/' . $f['id_faktur']) ?>"
                                                   class="btn btn-sm btn-info" title="Detail Faktur">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('sales_order/detail_faktur/' . $f['id_faktur'] . '?print=1') ?>"
                                                   class="btn btn-sm btn-secondary" target="_blank" title="Cetak Faktur">
                                                    <i class="fas fa-print"></i>
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
    $('#tabelAdminScFaktur').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[2, 'desc']],
        columnDefs: [
            { orderable: false, targets: [9] }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable: "Tidak ada faktur selesai",
            paginate: { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });
});
</script>
