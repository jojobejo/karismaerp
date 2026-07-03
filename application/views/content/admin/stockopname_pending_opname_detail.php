<?php
defined('BASEPATH') or exit('No direct script access allowed');
$summary = $summary ?? [];
$pending_rows = $pending_rows ?? [];
$keyword = $keyword ?? '';
$so_e = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
?>
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-7">
                        <h1 class="m-0">Detail Pending Opname</h1>
                    </div>
                    <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('admin/stockopname/monitoring') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Monitoring
                        </a>
                        <a href="<?= base_url('admin/stockopname/barang-pending') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Kelola Pending
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <style>
                    .pending-detail{background:#fff;border:1px solid #e1e7ef;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06)}
                    .pending-detail-header{padding:14px 16px;border-bottom:1px solid #e8edf3;display:flex;align-items:center;justify-content:space-between;gap:10px}
                    .pending-title{font-size:16px;font-weight:800;color:#1f2937;margin:0}
                    .pending-stat{background:#fff;border:1px solid #e1e7ef;border-radius:8px;padding:14px;min-height:96px}
                    .pending-stat span{display:block;color:#64748b;font-size:12px;text-transform:uppercase;font-weight:800}
                    .pending-stat strong{display:block;font-size:24px;color:#111827;line-height:1.1;margin-top:7px}
                    .pending-table-wrap{overflow:auto}.pending-code{font-family:monospace;font-weight:700}
                    .table td,.table th{vertical-align:middle}.btn i{margin-right:5px}
                    @media(max-width:768px){.pending-detail-header{align-items:flex-start;flex-direction:column}.content-header h1{font-size:22px}}
                </style>
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="pending-stat"><span>Total Item</span><strong><?= number_format((int)($summary['total_item'] ?? 0), 0, ',', '.') ?></strong></div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="pending-stat"><span>Total Qty</span><strong><?= number_format((int)($summary['total_qty'] ?? 0), 0, ',', '.') ?></strong></div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="pending-stat"><span>Total PCS</span><strong><?= number_format((int)($summary['total_qty_pcs'] ?? 0), 0, ',', '.') ?></strong></div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="pending-stat"><span>Total Box</span><strong><?= number_format((int)($summary['total_qty_box'] ?? 0), 0, ',', '.') ?></strong></div>
                    </div>
                </div>
                <div class="pending-detail">
                    <div class="pending-detail-header">
                        <div>
                            <h2 class="pending-title">Data Barang Pending Opname</h2>
                            <div class="text-muted small">Hitungan masuk master berdasarkan nama barang dan expired date yang sama.</div>
                        </div>
                        <form method="get" action="<?= base_url('admin/stockopname/monitoring/pending-opname') ?>" class="input-group input-group-sm" style="max-width:360px">
                            <input type="search" name="keyword" value="<?= $so_e($keyword) ?>" class="form-control" placeholder="Cari kode, nama, expired, lot">
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
                                <a href="<?= base_url('admin/stockopname/monitoring/pending-opname') ?>" class="btn btn-outline-secondary"><i class="fas fa-sync-alt"></i></a>
                            </div>
                        </form>
                    </div>
                    <div class="pending-table-wrap">
                        <table class="table table-sm table-hover table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Expired</th>
                                    <th>Lot</th>
                                    <th>Nama Barang</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">PCS</th>
                                    <th class="text-right">Box</th>
                                    <th>Status Master</th>
                                    <th>Update</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pending_rows)) : ?>
                                    <tr><td colspan="10" class="text-center text-muted p-4">Belum ada data pending opname.</td></tr>
                                <?php endif ?>
                                <?php foreach ($pending_rows as $row) : ?>
                                    <tr data-row-id="<?= (int)($row['id'] ?? 0) ?>">
                                        <td class="pending-code"><?= $so_e($row['kode_barang'] ?? '-') ?></td>
                                        <td><?= $so_e($row['expired_date'] ?? '-') ?></td>
                                        <td><?= $so_e(($row['no_lot'] ?? '') !== '' ? $row['no_lot'] : '-') ?></td>
                                        <td><?= $so_e($row['nama_barang'] ?? '-') ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format((int)($row['qty'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format((int)($row['qty_pcs'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format((int)($row['qty_box'] ?? 0), 0, ',', '.') ?></td>
                                        <td>
                                            <?php if ((int)($row['master_id'] ?? 0) > 0) : ?>
                                                <span class="badge badge-success">Masuk master #<?= (int)$row['master_id'] ?></span>
                                            <?php else : ?>
                                                <span class="badge badge-warning">Belum ada master</span>
                                            <?php endif ?>
                                        </td>
                                        <td><?= $so_e($row['updated_at'] ?? $row['created_at'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <a href="<?= base_url('admin/stockopname/barang-pending?edit_id=' . (int)($row['id'] ?? 0)) ?>" class="btn btn-outline-primary btn-sm" title="Update barang pending">
                                                <i class="fas fa-edit"></i> Update
                                            </a>
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-pending" data-id="<?= (int)($row['id'] ?? 0) ?>" title="Delete barang pending">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <footer class="main-footer"><strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong></footer>
</div>
<script>
window.addEventListener('load', function () {
    function notify(icon, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({toast:true,position:'top-end',icon:icon,title:message,showConfirmButton:false,timer:2600});
        } else {
            alert(message);
        }
    }
    function deletePending(id) {
        $.ajax({
            url: '<?= base_url('admin/stockopname/barang-pending/delete') ?>',
            type: 'POST',
            dataType: 'json',
            data: {id: id}
        }).done(function (res) {
            notify(res.status ? 'success' : 'error', res.message || 'Data diproses');
            if (res.status) {
                $('tr[data-row-id="' + id + '"]').remove();
                if ($('tr[data-row-id]').length === 0) {
                    $('.pending-table-wrap tbody').html('<tr><td colspan="10" class="text-center text-muted p-4">Belum ada data pending opname.</td></tr>');
                }
            }
        }).fail(function () {
            notify('error', 'Server gagal menghapus data pending');
        });
    }
    $('.btn-delete-pending').on('click', function () {
        var id = $(this).data('id');
        if (!id) { notify('error', 'ID barang pending tidak valid.'); return; }
        if (typeof Swal !== 'undefined') {
            Swal.fire({icon:'warning',title:'Hapus barang pending?',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal'}).then(function (result) {
                if (result.isConfirmed) { deletePending(id); }
            });
            return;
        }
        if (confirm('Hapus barang pending?')) { deletePending(id); }
    });
});
</script>
