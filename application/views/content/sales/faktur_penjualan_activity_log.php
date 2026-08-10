<?php /* views/content/sales/faktur_penjualan_activity_log.php */ ?>
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-history mr-2 text-info"></i>Activity Log Edit Faktur Penjualan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('faktur_penjualan') ?>">Faktur Penjualan</a></li>
                        <li class="breadcrumb-item active">Activity Log</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title m-0"><i class="fas fa-list-alt mr-2"></i>Riwayat Perubahan Qty & Total Harga</h3>
                    <div class="card-tools">
                        <a href="<?= base_url('faktur_penjualan') ?>" class="btn btn-sm btn-secondary font-weight-bold">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke List Faktur
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('faktur_penjualan/activity_log') ?>" class="form-inline mb-4">
                        <label class="mr-2">Periode Edit:</label>
                        <input type="date" name="date1" class="form-control form-control-sm mr-2" value="<?= htmlspecialchars($filter['date1']) ?>">
                        <label class="mr-2">s/d</label>
                        <input type="date" name="date2" class="form-control form-control-sm mr-2" value="<?= htmlspecialchars($filter['date2']) ?>">

                        <label class="mr-2 ml-3">Pencarian:</label>
                        <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="No Faktur / User / Catatan..." value="<?= htmlspecialchars($filter['search']) ?>">

                        <button type="submit" class="btn btn-sm btn-info mr-2"><i class="fas fa-search"></i> Filter</button>
                        <a href="<?= base_url('faktur_penjualan/activity_log') ?>" class="btn btn-sm btn-light">Reset</a>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-hover" id="table-log">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" width="50">No</th>
                                    <th width="150">Waktu Log</th>
                                    <th width="160">No. Faktur</th>
                                    <th>Customer</th>
                                    <th width="160">Diubah Oleh</th>
                                    <th width="140">Jenis Revisi</th>
                                    <th>Keterangan / Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($logs)): ?>
                                    <?php foreach ($logs as $i => $l): ?>
                                    <tr>
                                        <td class="text-center"><?= $i + 1 ?></td>
                                        <td><?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?></td>
                                        <td>
                                            <?php if (!empty($l['id_faktur'])): ?>
                                                <a href="<?= base_url('faktur_penjualan/edit_qty/' . $l['id_faktur']) ?>" class="font-weight-bold text-primary">
                                                    <?= htmlspecialchars($l['no_faktur']) ?>
                                                </a>
                                            <?php else: ?>
                                                <strong><?= htmlspecialchars($l['no_faktur']) ?></strong>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($l['master_customer_name'] ?: ($l['fp_customer_name'] ?: '-')) ?></td>
                                        <td><i class="fas fa-user mr-1 text-secondary"></i> <strong><?= htmlspecialchars($l['dilakukan_oleh']) ?></strong></td>
                                        <td>
                                            <?php 
                                                $aksi = $l['aksi'] ?? '';
                                                if ($aksi === 'EDIT_HARGA') {
                                                    echo '<span class="badge badge-info"><i class="fas fa-tag mr-1"></i> Edit Harga</span>';
                                                } elseif ($aksi === 'EDIT_QTY') {
                                                    echo '<span class="badge badge-warning"><i class="fas fa-cubes mr-1"></i> Edit Qty</span>';
                                                } else {
                                                    echo '<span class="badge badge-primary"><i class="fas fa-edit mr-1"></i> Edit Qty & Harga</span>';
                                                }
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($l['keterangan']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat aktivitas log edit faktur.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div> <!-- /.wrapper -->

<script>
$(document).ready(function() {
    $('#table-log').DataTable({
        "ordering": false,
        "pageLength": 50,
        "language": {
            "search": "Cari di Tabel Log:"
        }
    });
});
</script>
