<!-- views/content/sales/so_detail.php -->
<?php
$jobdesk = strtoupper((string)$this->session->userdata('jobdesk'));
$from_page = strtolower((string)$this->input->get('from', true));
$is_admin_sc_detail = $jobdesk === 'ADMINSC' || $from_page === 'admin_sc';
$hide_activity_log = $is_admin_sc_detail || $jobdesk === 'SC';
$back_url = $is_admin_sc_detail ? base_url('sales_order/admin_sc') : base_url('sales_order');
$back_label = $is_admin_sc_detail ? 'Kembali ke Admin SC' : 'Kembali';
?>
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
                        <li class="breadcrumb-item">
                            <a href="<?= $back_url ?>"><?= $is_admin_sc_detail ? 'Admin SC' : 'Sales Order' ?></a>
                        </li>
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
            <div class="sales-action-bar">
                <a href="<?= $back_url ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> <?= $back_label ?>
                </a>
                <?php if (!$is_admin_sc_detail && $so['status'] === 'draft'): ?>
                    <a href="<?= base_url('sales_order/edit/' . $so['id_so']) ?>"
                       class="btn btn-warning btn-sm">
                        <i class="fas fa-pencil-alt"></i> Edit SO
                    </a>
                    <a href="<?= base_url('sales_order/rekam/' . $so['id_so']) ?>"
                       class="btn btn-primary btn-sm"
                       data-confirm-title="Rekam SO?"
                       data-confirm-text="Status akan berubah menjadi Open dan SO tidak dapat diedit lagi.">
                        <i class="fas fa-check"></i> Rekam SO
                    </a>
                    <a href="<?= base_url('sales_order/cancel/' . $so['id_so']) ?>"
                       class="btn btn-danger btn-sm"
                       data-confirm-title="Batalkan SO?"
                       data-confirm-text="Sales Order ini akan dibatalkan.">
                        <i class="fas fa-times"></i> Batalkan SO
                    </a>
                <?php endif; ?>
            </div>

            <?php
            $badge_map = [
                'draft'              => 'secondary',
                'open'               => 'primary',
                'sedang_verifikasi'  => 'warning',
                'siap_faktur'        => 'info',
                'partial'            => 'warning',
                'completed'          => 'success',
                'cancelled'          => 'danger',
            ];
            $label_map = [
                'draft'              => 'Draft',
                'open'               => 'Open',
                'sedang_verifikasi'  => 'Sedang Verifikasi',
                'siap_faktur'        => 'Siap Faktur',
                'partial'            => 'Partial',
                'completed'          => 'Completed',
                'cancelled'          => 'Cancelled',
            ];
            $badge = $badge_map[$so['status']] ?? 'secondary';
            $label = $label_map[$so['status']] ?? $so['status'];
            $pct   = $total_order > 0 ? round(($total_faktur / $total_order) * 100, 1) : 0;
            ?>

            <?php if (!$is_admin_sc_detail): ?>
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
            <?php endif; ?>

            <!-- PROGRESS BAR -->
            <?php if (!$is_admin_sc_detail && $total_order > 0): ?>
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

            <!-- INFORMASI & ITEM SO -->
            <div class="card card-outline card-primary mb-3 collapsed-card">
                <div class="card-header py-2">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-1"></i> Informasi & Item Sales Order
                        <span class="badge badge-info ml-1"><?= count($details) ?> item</span>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-plus"></i>
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
                                    <td>
                                        <?php
                                        $so_route = $so['kd_rute'] ?? '';
                                        $customer_route = $so['customer_kd_rute'] ?? '';
                                        $effective_route = $so_route !== '' ? $so_route : $customer_route;
                                        ?>
                                        <?= $effective_route !== '' ? htmlspecialchars($effective_route) : '<span class="text-muted">-</span>' ?>
                                        <?php if ($so_route !== '' && $customer_route !== '' && $so_route !== $customer_route): ?>
                                            <br><small class="text-muted">Master: <?= htmlspecialchars($customer_route) ?></small>
                                        <?php endif; ?>
                                    </td>
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
                    <div class="border-top">
                        <table class="table table-sm table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
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
                                    <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada item</td></tr>
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
                    </div>
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
                                    <th class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fakturs as $n => $f):
                                    $collapse_id = 'detailFaktur' . (int)$f['id_faktur'];
                                    $items_faktur = $faktur_details[(int)$f['id_faktur']] ?? [];
                                ?>
                                <tr>
                                    <td><?= $n + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($f['no_faktur']) ?></strong>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($f['tanggal_faktur'])) ?></td>
                                    <td class="text-right"><?= number_format($f['total_tonase'], 3) ?> ton</td>
                                    <td class="text-center">
                                        <?php
                                        $fs_badge = [
                                            'confirmed'   => 'success',
                                            'draft'       => 'warning',
                                            'cancelled'   => 'danger',
                                            'in_progress' => 'primary',
                                            'proses_do'   => 'info',
                                            'selesai'     => 'success',
                                            'completed'   => 'success',
                                        ];
                                        $fs_label = [
                                            'confirmed'   => 'Confirmed',
                                            'draft'       => 'Draft',
                                            'cancelled'   => 'Cancelled',
                                            'in_progress' => 'In Progress',
                                            'proses_do'   => 'Proses DO',
                                            'selesai'     => 'Selesai',
                                            'completed'   => 'Completed',
                                        ];
                                        ?>
                                        <span class="badge badge-<?= $fs_badge[$f['status']] ?? 'secondary' ?>">
                                            <?= $fs_label[$f['status']] ?? $f['status'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-xs btn-info"
                                                data-toggle="collapse"
                                                data-target="#<?= $collapse_id ?>"
                                                aria-expanded="false"
                                                aria-controls="<?= $collapse_id ?>"
                                                title="Tampilkan barang faktur">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse bg-light" id="<?= $collapse_id ?>">
                                    <td colspan="6" class="p-2">
                                        <?php if (empty($items_faktur)): ?>
                                            <div class="text-center text-muted py-2">Detail barang faktur tidak tersedia.</div>
                                        <?php else: ?>
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width:40px">No</th>
                                                        <th>Barang</th>
                                                        <th>Lot / Exp</th>
                                                        <th class="text-right">Qty</th>
                                                        <th class="text-right">Harga</th>
                                                        <th class="text-right">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($items_faktur as $idx => $item): ?>
                                                        <tr>
                                                            <td class="text-center"><?= $idx + 1 ?></td>
                                                            <td>
                                                                <strong><?= htmlspecialchars($item['nama_barang'] ?? '-') ?></strong>
                                                                <br><small class="text-muted"><?= htmlspecialchars($item['kd_barang'] ?? '-') ?></small>
                                                            </td>
                                                            <td>
                                                                <small>
                                                                    <?php if (!empty($item['no_lot'])): ?>
                                                                        Lot: <code><?= htmlspecialchars($item['no_lot']) ?></code><br>
                                                                    <?php endif; ?>
                                                                    Exp: <?= !empty($item['expired_date']) ? date('d/m/Y', strtotime($item['expired_date'])) : '-' ?>
                                                                </small>
                                                            </td>
                                                            <td class="text-right"><?= number_format((float)($item['qty'] ?? 0), 2) ?></td>
                                                            <td class="text-right">Rp <?= number_format((float)($item['hrg_satuan'] ?? 0), 0, ',', '.') ?></td>
                                                            <td class="text-right font-weight-bold">Rp <?= number_format((float)($item['total_harga'] ?? 0), 0, ',', '.') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$hide_activity_log): ?>
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
            <?php endif; ?>

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

    function salesToast(type, message) {
        if (window.Swal) {
            Swal.fire({ toast:true, position:'top-end', icon:type || 'info', title:message || '', timer:2600, showConfirmButton:false });
        } else {
            alert(message || '');
        }
    }

    function salesConfirm(options) {
        options = options || {};
        if (window.Swal) {
            return Swal.fire({
                title: options.title || 'Konfirmasi',
                text: options.text || 'Lanjutkan proses ini?',
                icon: options.icon || 'question',
                showCancelButton: true,
                confirmButtonText: options.confirmText || 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonColor: options.confirmColor || '#007bff'
            }).then(function(result){ return result.isConfirmed; });
        }
        return Promise.resolve(confirm((options.title ? options.title + '\n' : '') + (options.text || 'Lanjutkan proses ini?')));
    }

    function salesLoading(show, text) {}

    $(document).on('click', 'a[data-confirm-title]', function(e) {
        e.preventDefault();
        const href = this.href;
        const isDanger = $(this).hasClass('btn-danger');
        salesConfirm({
            title: $(this).data('confirm-title'),
            text: $(this).data('confirm-text'),
            icon: isDanger ? 'warning' : 'question',
            confirmText: isDanger ? 'Ya, batalkan' : 'Ya, rekam',
            confirmColor: isDanger ? '#dc2626' : '#2563eb'
        }).then(function(ok) {
            if (!ok) return;
            salesLoading(true, 'Memproses SO...');
            window.location.href = href;
        });
    });

    <?php if (!$hide_activity_log): ?>
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
    <?php endif; ?>
});
</script>
