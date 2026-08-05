<?php /* views/content/sales/faktur_penjualan_list.php */ ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-file-invoice-dollar mr-2"></i>Daftar Faktur Penjualan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Faktur Penjualan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- FLASH -->
            <?php foreach (['success'=>'success','error'=>'danger'] as $k=>$c): ?>
                <?php if ($msg = $this->session->flashdata($k)): ?>
                    <div class="alert alert-<?= $c ?> alert-dismissible shadow-sm">
                        <i class="fas fa-<?= $k==='success'?'check-circle':'exclamation-circle' ?> mr-1"></i>
                        <?= $msg ?>
                        <button class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title m-0">Semua Faktur Penjualan</h3>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('faktur_penjualan') ?>" class="form-inline mb-4">
                        <label class="mr-2">Periode:</label>
                        <input type="date" name="date1" class="form-control form-control-sm mr-2" value="<?= htmlspecialchars($filter['date1']) ?>">
                        <label class="mr-2">s/d</label>
                        <input type="date" name="date2" class="form-control form-control-sm mr-2" value="<?= htmlspecialchars($filter['date2']) ?>">
                        
                        <label class="mr-2 ml-3">Status:</label>
                        <select name="status" class="form-control form-control-sm mr-2">
                            <option value="all" <?= $filter['status'] === 'all' ? 'selected' : '' ?>>Semua Status</option>
                            <option value="confirmed" <?= $filter['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="selesai_do" <?= $filter['status'] === 'selesai_do' ? 'selected' : '' ?>>Selesai DO</option>
                            <option value="cancelled" <?= $filter['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>

                        <button type="submit" class="btn btn-sm btn-info"><i class="fas fa-search"></i> Tampilkan</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped table-hover" id="table-faktur">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>No. Faktur</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Sales</th>
                                    <th>Status</th>
                                    <th class="text-center"><i class="fas fa-cog"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fakturs as $i => $f): ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($f['no_faktur']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($f['tanggal_faktur'])) ?></td>
                                    <td><?= htmlspecialchars($f['master_customer_name'] ?: $f['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($f['so_salesman'] ?: $f['salesman']) ?></td>
                                    <td>
                                        <?php if ($f['status'] === 'confirmed'): ?>
                                            <span class="badge badge-info">Confirmed</span>
                                        <?php elseif ($f['status'] === 'selesai_do'): ?>
                                            <span class="badge badge-success">Selesai DO</span>
                                        <?php elseif ($f['status'] === 'cancelled'): ?>
                                            <span class="badge badge-danger">Cancelled</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?= $f['status'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('faktur_penjualan/edit_qty/' . $f['id_faktur']) ?>" class="btn btn-xs btn-warning" title="Edit Qty (Retur Revisi)">
                                            <i class="fas fa-edit"></i> Edit Qty
                                        </a>
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
<script>
$(document).ready(function() {
    $('#table-faktur').DataTable({
        "ordering": false,
        "pageLength": 50,
        "language": {
            "search": "Cari Faktur:"
        }
    });
});
</script>
