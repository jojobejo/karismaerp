<!-- views/content/sales/so_detail.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
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
                        <i class="fas fa-file-invoice mr-2"></i>
                        Detail SO: <strong><?= htmlspecialchars($so['no_so']) ?></strong>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($so['no_so']) ?></li>
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
                        <?= $msg ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- TOMBOL AKSI -->
            <div class="mb-3">
                <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <?php if ($so['status'] === 'draft'): ?>
                    <a href="<?= base_url('sales_order/edit/' . $so['id_so']) ?>"
                       class="btn btn-warning btn-sm">
                        <i class="fas fa-pencil-alt"></i> Edit SO
                    </a>
                    <a href="<?= base_url('sales_order/rekam/' . $so['id_so']) ?>"
                       class="btn btn-primary btn-sm"
                       onclick="return confirm('Rekam SO ini? Status akan berubah menjadi Open dan SO tidak dapat diedit lagi.')">
                        <i class="fas fa-check"></i> Rekam SO
                    </a>
                    <a href="<?= base_url('sales_order/cancel/' . $so['id_so']) ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Batalkan SO ini?')">
                        <i class="fas fa-times"></i> Batalkan SO
                    </a>
                <?php endif; ?>
            </div>

            <?php
            $badge_map = [
                'draft'     => 'secondary',
                'open'      => 'primary',
                'completed' => 'success',
                'cancelled' => 'danger',
            ];
            $label_map = [
                'draft'     => 'Draft',
                'open'      => 'Open',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];
            $badge = $badge_map[$so['status']] ?? 'secondary';
            $label = $label_map[$so['status']] ?? $so['status'];
            $pct   = $total_order > 0 ? round(($total_faktur / $total_order) * 100, 1) : 0;
            ?>

            <!-- SUMMARY CARDS -->
            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-boxes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Order</span>
                            <span class="info-box-number"><?= number_format($total_order) ?></span>
                            <span class="info-box-text small">pcs</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-file-invoice"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Sudah Difakturkan</span>
                            <span class="info-box-number"><?= number_format($total_faktur) ?></span>
                            <span class="info-box-text small">pcs</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon <?= $total_outstanding > 0 ? 'bg-danger' : 'bg-secondary' ?> elevation-1">
                            <i class="fas fa-hourglass-half"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Outstanding</span>
                            <span class="info-box-number <?= $total_outstanding > 0 ? 'text-danger' : '' ?>">
                                <?= number_format($total_outstanding) ?>
                            </span>
                            <span class="info-box-text small">pcs</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-weight-hanging"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Tonase</span>
                            <span class="info-box-number"><?= number_format($so['total_tonase'], 3) ?></span>
                            <span class="info-box-text small">ton</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROGRESS BAR -->
            <?php if ($total_order > 0): ?>
            <div class="card card-outline card-<?= $pct >= 100 ? 'success' : 'warning' ?> mb-3">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted font-weight-bold">
                            <i class="fas fa-chart-line mr-1"></i> Progress Pemenuhan Order
                        </small>
                        <span class="badge badge-<?= $pct >= 100 ? 'success' : ($pct > 0 ? 'warning' : 'secondary') ?>">
                            <?= $pct ?>%
                        </span>
                    </div>
                    <div class="progress" style="height:18px; border-radius:4px;">
                        <div class="progress-bar bg-<?= $pct >= 100 ? 'success' : 'warning' ?> progress-bar-striped"
                             style="width:<?= $pct ?>%; font-size:11px; line-height:18px;">
                            <?= $pct > 10 ? $pct . '%' : '' ?>
                        </div>
                    </div>
                    <small class="text-muted">
                        <?= number_format($total_faktur) ?> dari <?= number_format($total_order) ?> pcs sudah difakturkan
                        &mdash; Status: <span class="badge badge-<?= $badge ?>"><?= $label ?></span>
                    </small>
                </div>
            </div>
            <?php endif; ?>

            <!-- INFORMASI SO -->
            <div class="card card-outline card-primary mb-3">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi Sales Order</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row no-gutters">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted pl-3" width="45%">No. SO</td>
                                    <td><strong><?= htmlspecialchars($so['no_so']) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-3">Tanggal</td>
                                    <td><?= date('d/m/Y', strtotime($so['tanggal_transaksi'])) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-3">Customer</td>
                                    <td><strong><?= htmlspecialchars($so['customer_name']) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-3">Rute / Regional</td>
                                    <td><?= !empty($so['regional']) ? htmlspecialchars($so['regional']) : '<span class="text-muted">-</span>' ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-3">Gudang</td>
                                    <td><?= htmlspecialchars($so['gudang_id']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted pl-3" width="45%">Status</td>
                                    <td><span class="badge badge-<?= $badge ?> px-3 py-1"><?= $label ?></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-3">Dibuat oleh</td>
                                    <td><?= htmlspecialchars($so['create_by']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted pl-3">Kubikasi</td>
                                    <td><?= number_format($so['total_kubikasi'], 4) ?> m³</td>
                                </tr>
                                <?php if (!empty($so['catatan'])): ?>
                                <tr>
                                    <td class="text-muted pl-3">Catatan</td>
                                    <td><?= nl2br(htmlspecialchars($so['catatan'])) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ITEM SO -->
            <div class="card card-outline card-info mb-3">
                <div class="card-header py-2">
                    <h3 class="card-title">
                        <i class="fas fa-list-ul mr-1"></i> Item Sales Order
                        <span class="badge badge-info ml-1"><?= count($details) ?> item</span>
                    </h3>
                    <?php if ($so['status'] === 'open' && $total_outstanding > 0): ?>
                        <div class="card-tools">
                            <button type="submit" form="form-pilih-faktur" class="btn btn-success btn-xs btn-buat-faktur-pilih">
                                <i class="fas fa-file-invoice-dollar"></i> Fakturkan Item Dipilih
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <form id="form-pilih-faktur" action="<?= base_url('sales_order/form_faktur/' . $so['id_so']) ?>" method="get">
                        <?php if ($so['status'] === 'open' && $total_outstanding > 0): ?>
                            <div class="px-3 py-2 border-bottom bg-light">
                                <div class="form-inline">
                                    <label class="mr-2 mb-1 mb-sm-0 font-weight-bold">Jenis Faktur</label>
                                    <select name="tax_mode" id="tax-mode-faktur" class="form-control form-control-sm">
                                        <option value="non_pajak" selected>Non Pajak (0%)</option>
                                        <option value="pajak">Pajak (11%)</option>
                                    </select>
                                    <small class="text-muted ml-sm-2 mt-1 mt-sm-0">Berlaku untuk item SO yang dipilih.</small>
                                </div>
                            </div>
                        <?php endif; ?>
                        <table class="table table-sm table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <?php if ($so['status'] === 'open' && $total_outstanding > 0): ?>
                                        <th class="text-center" style="width:42px">
                                            <input type="checkbox" id="check-all-faktur" title="Pilih semua item outstanding">
                                        </th>
                                    <?php endif; ?>
                                    <th>#</th>
                                    <th>Barang</th>
                                    <th>Lot / Exp</th>
                                    <th class="text-right">Qty Order</th>
                                    <th class="text-right">Difakturkan</th>
                                    <th class="text-right">Outstanding</th>
                                    <th class="text-right">Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($details)): ?>
                                    <tr><td colspan="<?= ($so['status'] === 'open' && $total_outstanding > 0) ? 8 : 7 ?>" class="text-center text-muted py-3">Tidak ada item</td></tr>
                                <?php else: ?>
                                    <?php foreach ($details as $i => $d):
                                        $outstanding_item = (float)$d['qty'] - (float)$d['qty_faktur'];
                                        $isi     = max(1, (int)($d['isi_per_box'] ?? 1));
                                        $ord_box = floor($d['qty'] / $isi);
                                        $ord_pcs = fmod($d['qty'], $isi);
                                        $fak_box = floor($d['qty_faktur'] / $isi);
                                        $fak_pcs = fmod($d['qty_faktur'], $isi);
                                        $out_box = floor($outstanding_item / $isi);
                                        $out_pcs = fmod($outstanding_item, $isi);
                                    ?>
                                    <tr class="<?= $outstanding_item <= 0 ? 'table-success' : '' ?>">
                                        <?php if ($so['status'] === 'open' && $total_outstanding > 0): ?>
                                            <td class="text-center align-middle">
                                                <?php if ($outstanding_item > 0): ?>
                                                    <input type="checkbox" class="check-item-faktur" name="item[]" value="<?= (int)$d['id_so_detail'] ?>">
                                                <?php else: ?>
                                                    <i class="fas fa-check text-success"></i>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($d['nama_barang']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($d['kd_barang']) ?></small>
                                        </td>
                                        <td>
                                            <small>
                                                <?php if (!empty($d['no_lot'])): ?>
                                                    Lot: <code><?= htmlspecialchars($d['no_lot']) ?></code><br>
                                                <?php endif; ?>
                                                Exp: <?= !empty($d['expired_date']) ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?>
                                            </small>
                                        </td>
                                        <td class="text-right">
                                            <?= $ord_box > 0 ? $ord_box . ' box' : '' ?>
                                            <?= $ord_pcs > 0 ? ($ord_box > 0 ? ' + ' : '') . (int)$ord_pcs . ' pcs' : '' ?>
                                            <?php if ($ord_box == 0 && $ord_pcs == 0): ?><?= (int)$d['qty'] ?> pcs<?php endif; ?>
                                        </td>
                                        <td class="text-right text-success">
                                            <?php if ($d['qty_faktur'] > 0): ?>
                                                <?= $fak_box > 0 ? $fak_box . ' box' : '' ?>
                                                <?= $fak_pcs > 0 ? ($fak_box > 0 ? ' + ' : '') . (int)$fak_pcs . ' pcs' : '' ?>
                                                <?php if ($fak_box == 0 && $fak_pcs == 0): ?><?= (int)$d['qty_faktur'] ?> pcs<?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right <?= $outstanding_item > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                            <?php if ($outstanding_item > 0): ?>
                                                <?= $out_box > 0 ? $out_box . ' box' : '' ?>
                                                <?= $out_pcs > 0 ? ($out_box > 0 ? ' + ' : '') . (int)$out_pcs . ' pcs' : '' ?>
                                                <?php if ($out_box == 0 && $out_pcs == 0): ?><?= (int)$outstanding_item ?> pcs<?php endif; ?>
                                            <?php else: ?>
                                                <i class="fas fa-check text-success"></i> Lunas
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">Rp <?= number_format($d['hrg_satuan'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>

            <!-- FAKTUR PENJUALAN -->
            <div class="card card-outline card-success mb-3">
                <div class="card-header py-2">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice-dollar mr-1"></i>
                        Faktur Penjualan
                        <span class="badge badge-success ml-1"><?= count($fakturs) ?></span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($fakturs)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                            Belum ada Faktur Penjualan.
                            <?php if ($so['status'] === 'draft'): ?>
                                <br><small>Rekam SO terlebih dahulu untuk dapat membuat Faktur.</small>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>No. Faktur</th>
                                    <th>Tanggal</th>
                                    <th class="text-right">Tonase</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fakturs as $n => $f): ?>
                                <tr>
                                    <td><?= $n + 1 ?></td>
                                    <td>
                                        <a href="<?= base_url('sales_order/detail_faktur/' . $f['id_faktur']) ?>">
                                            <strong><?= htmlspecialchars($f['no_faktur']) ?></strong>
                                        </a>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($f['tanggal_faktur'])) ?></td>
                                    <td class="text-right"><?= number_format($f['total_tonase'], 3) ?> ton</td>
                                    <td class="text-center">
                                        <?php
                                        $fs_badge = ['confirmed' => 'success', 'draft' => 'secondary', 'cancelled' => 'danger'];
                                        $fs_label = ['confirmed' => 'Confirmed', 'draft' => 'Draft', 'cancelled' => 'Cancelled'];
                                        ?>
                                        <span class="badge badge-<?= $fs_badge[$f['status']] ?? 'secondary' ?>">
                                            <?= $fs_label[$f['status']] ?? $f['status'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('sales_order/detail_faktur/' . $f['id_faktur']) ?>"
                                           class="btn btn-xs btn-info" title="Detail Faktur">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ACTIVITY LOG -->
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-history mr-1"></i> Activity Log</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" id="logContainer">
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Memuat activity log...
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
</div><!-- /.wrapper -->

<script>
$(document).ready(function () {
    function escapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    $('#check-all-faktur').on('change', function() {
        $('.check-item-faktur').prop('checked', this.checked);
    });

    $(document).on('change', '.check-item-faktur', function() {
        const total = $('.check-item-faktur').length;
        const checked = $('.check-item-faktur:checked').length;
        $('#check-all-faktur').prop('checked', total > 0 && checked === total);
    });

    $('#form-pilih-faktur').on('submit', function(e) {
        if ($('.check-item-faktur:checked').length < 1) {
            e.preventDefault();
            if (window.Swal) {
                Swal.fire('Peringatan', 'Pilih minimal 1 item SO yang akan difakturkan.', 'warning');
            } else {
                alert('Pilih minimal 1 item SO yang akan difakturkan.');
            }
        }
    });

    $.getJSON('<?= base_url('sales_order/activity_log_so/' . $so['id_so']) ?>', function(resp) {
        if (!resp.data || !resp.data.length) {
            $('#logContainer').html('<div class="text-center text-muted py-3">Belum ada aktivitas.</div>');
            return;
        }
        let html = '<table class="table table-sm table-bordered mb-0">'
            + '<thead class="thead-light"><tr>'
            + '<th>Waktu</th><th>Aksi</th><th>Keterangan</th><th>Oleh</th>'
            + '</tr></thead><tbody>';
        resp.data.forEach(function(log) {
            const oleh = log.dilakukan_oleh || log.created_by || log.create_by || '-';
            html += '<tr>'
                + '<td><small>' + escapeHtml(log.created_at) + '</small></td>'
                + '<td><span class="badge badge-secondary">' + escapeHtml(log.aksi) + '</span></td>'
                + '<td><small>' + escapeHtml(log.keterangan) + '</small></td>'
                + '<td><small>' + escapeHtml(oleh) + '</small></td>'
                + '</tr>';
        });
        html += '</tbody></table>';
        $('#logContainer').html(html);
    }).fail(function() {
        $('#logContainer').html('<div class="text-center text-muted py-2">Gagal memuat log.</div>');
    });
});
</script>
