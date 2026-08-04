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
    /* Baris dengan item ditolak checker */
    .row-ada-ditolak {
        background-color: #fff8e1 !important;
    }
    .row-ada-ditolak:hover {
        background-color: #fff0c0 !important;
    }
    .badge-ditolak {
        background-color: #e65100;
        color: #fff;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 3px;
        white-space: nowrap;
        display: inline-block;
        margin-left: 3px;
        vertical-align: middle;
    }
    .alert-ditolak-banner {
        border-left: 4px solid #e65100;
        background: #fff3e0;
        padding: 8px 14px;
        border-radius: 4px;
        margin-bottom: 12px;
        font-size: 13px;
        color: #bf360c;
    }
    .alert-ditolak-banner i { margin-right: 6px; }
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
                            <i class="fas fa-file-invoice mr-2"></i>
                            <?= !empty($selected_rute)
                                ? 'Admin SC - Faktur Rute ' . htmlspecialchars($selected_rute)
                                : 'Admin SC - Rute Faktur Selesai' ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order/admin_sc') ?>">Admin SC</a></li>
                            <?php if (!empty($selected_rute)): ?>
                                <li class="breadcrumb-item"><a href="<?= base_url('sales_order/admin_sc/faktur') ?>">Faktur Selesai</a></li>
                                <li class="breadcrumb-item active">Rute <?= htmlspecialchars($selected_rute) ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item active">Faktur Selesai</li>
                            <?php endif; ?>
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
                    <a href="<?= base_url('sales_order/admin_sc') ?>" class="btn btn-secondary btn-sm mr-1">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Admin SC
                    </a>
                    <a href="<?= base_url('sales_order/admin_sc/activity_log') ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-history mr-1"></i> Activity Log Admin SC
                    </a>
                </div>

                <?php
                    $active_query = array_filter([
                        'date1'       => $filter['date1'] ?? '',
                        'date2'       => $filter['date2'] ?? '',
                        'customer_id' => $filter['customer_id'] ?? '',
                    ], function($v) { return $v !== '' && $v !== null; });
                ?>

                <div class="card card-outline card-secondary">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Faktur</h3>
                    </div>
                    <div class="card-body py-2">
                        <form action="<?= base_url('sales_order/admin_sc/faktur') ?>" method="post">
                            <?php if (!empty($selected_rute)): ?>
                                <input type="hidden" name="rute" value="<?= htmlspecialchars($selected_rute, ENT_QUOTES, 'UTF-8') ?>">
                            <?php endif; ?>
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

                <?php if (empty($selected_rute)): ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-route mr-2"></i> Rute Faktur Selesai
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($route_summary ?? []) ?> rute</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        // Cek apakah ada rute dengan item ditolak
                        $total_rute_faktur_ditolak = 0;
                        foreach ($route_summary as $rs) {
                            if ((int)($rs['total_item_ditolak'] ?? 0) > 0) $total_rute_faktur_ditolak++;
                        }
                        ?>
                        <?php if ($total_rute_faktur_ditolak > 0): ?>   
                        <?php endif; ?>
                        <table class="table table-bordered table-hover table-sm" id="tabelAdminScFaktur">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Rute</th>
                                    <th>Tanggal Faktur</th>
                                    <th class="text-center">Total Faktur</th>
                                    <th class="text-right">Total Qty</th>
                                    <th class="text-right">Total Pajak</th>
                                    <th class="text-right">Grand Total</th>
                                    <th class="text-center no-sort" style="min-width:86px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($route_summary)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Belum ada faktur selesai sesuai filter.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($route_summary as $row):
                                        $rute_query = array_merge($active_query, [
                                            'rute'  => $row['kd_rute'],
                                            'date1' => $row['tgl_faktur'],
                                            'date2' => $row['tgl_faktur'],
                                        ]);
                                        $ada_ditolak_r = (int)($row['total_item_ditolak'] ?? 0) > 0;
                                    ?>
                                        <tr class="<?= $ada_ditolak_r ? 'row-ada-ditolak' : '' ?>">
                                            <td class="font-weight-bold">
                                                <?= htmlspecialchars($row['kd_rute']) ?>
                                                <?php if ($ada_ditolak_r): ?>
                                                    <span class="badge-ditolak" title="Ada barang tidak termuat — perlu repost faktur">
                                                        <i class="fas fa-exclamation-triangle"></i> barang tidak termuat
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-nowrap"><?= date('d/m/Y', strtotime($row['tgl_faktur'])) ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-primary px-2 py-1"><?= number_format((int)$row['total_faktur']) ?></span>
                                            </td>
                                            <td class="text-right font-weight-bold text-success"><?= number_format((float)$row['total_qty'], 2) ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$row['total_pajak'], 0, ',', '.') ?></td>
                                            <td class="text-right font-weight-bold">Rp <?= number_format((float)$row['grand_total'], 0, ',', '.') ?></td>
                                            <td class="text-center">
                                                <a href="<?= base_url('sales_order/admin_sc/faktur?' . http_build_query($rute_query)) ?>"
                                                class="btn btn-sm btn-info" title="Lihat faktur rute">
                                                    <i class="fas fa-arrow-right mr-1"></i> Lihat
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <div class="mb-2">
                    <a href="<?= base_url('sales_order/admin_sc/faktur?' . http_build_query($active_query)) ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Rute
                    </a>
                    <span class="badge badge-primary ml-1 px-2 py-1">Rute <?= htmlspecialchars($selected_rute) ?></span>
                </div>

                <?php
                $total_faktur_ditolak = 0;
                foreach ($fakturs as $f) {
                    if ((int)($f['jumlah_item_ditolak'] ?? 0) > 0) $total_faktur_ditolak++;
                }
                ?>
                <?php if ($total_faktur_ditolak > 0): ?>
                <div class="alert-ditolak-banner mb-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian!</strong> Terdapat <strong><?= $total_faktur_ditolak ?> faktur</strong>
                    pada rute <strong><?= htmlspecialchars($selected_rute) ?></strong>
                    yang SO-nya memiliki barang tidak termuat saat loading.
                    Barang ini <strong>tidak boleh difakturkan</strong>.
                    Gunakan tombol <strong><i class="fas fa-undo"></i> Repost</strong> pada faktur terkait
                    untuk mengembalikan item tersebut ke SO.
                </div>
                <?php endif; ?>

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
                            <?php if (!empty($fakturs)): ?>
                                <?php $print_query = array_merge($active_query, ['rute' => $selected_rute]); ?>
                                <a href="<?= base_url('sales_order/admin_sc/faktur/print_rute?' . http_build_query($print_query)) ?>"
                                   class="btn btn-light btn-xs mr-1" target="_blank">
                                    <i class="fas fa-print mr-1"></i> Cetak Semua
                                </a>
                            <?php endif; ?>
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
                                            'proses_do'   => 'info',
                                            'selesai_do'  => 'success',
                                        ];
                                        $status_label = [
                                            'confirmed'   => 'Confirmed',
                                            'draft'       => 'Draft',
                                            'proses_do'   => 'Proses DO',
                                            'selesai_do'  => 'Selesai DO',
                                        ];
                                        $payment = strtolower(trim((string)($f['cara_pembayaran'] ?? '')));
                                        $payment_label = [
                                            'cash'     => 'Cash',
                                            'transfer' => 'Transfer',
                                            'tempo'    => 'Tempo',
                                            'bg'       => 'BG',
                                        ][$payment] ?? ($payment !== '' ? strtoupper($payment) : '-');
                                        $tempo = $f['jtempo'] ?? $f['tempo'] ?? null;
                                        $rute = $f['so_kd_rute'] ?: ($f['customer_kd_rute'] ?? '');
                                        $ada_ditolak_f = (int)($f['jumlah_item_ditolak'] ?? 0) > 0;
                                    ?>
                                        <tr class="<?= $ada_ditolak_f ? 'row-ada-ditolak' : '' ?>">
                                            <td class="font-weight-bold">
                                                <?= htmlspecialchars($f['no_faktur']) ?>
                                                <?php if (!empty($f['is_split_parent'])): ?>
                                                    <br><span class="badge badge-warning" style="font-size: 75%; font-weight: normal;"><i class="fas fa-cut mr-1"></i>Split Induk</span>
                                                <?php endif; ?>
                                                <?php if (!empty($f['parent_id_faktur'])): ?>
                                                    <br><span class="badge badge-info" style="font-size: 75%; font-weight: normal;"><i class="fas fa-link mr-1"></i>Turunan</span>
                                                <?php endif; ?>
                                                <?php if ($ada_ditolak_f): ?>
                                                    <br><span class="badge-ditolak" title="SO ini punya <?= (int)$f['jumlah_item_ditolak'] ?> item tidak termuat — repost terlebih dahulu">
                                                        <i class="fas fa-ban"></i> <?= (int)$f['jumlah_item_ditolak'] ?> tdk termuat
                                                    </span>
                                                <?php endif; ?>
                                            </td>
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
                                                <?php if (in_array($status, ['confirmed', 'proses_do', 'selesai_do'])): ?>
                                                    <button class="btn btn-sm btn-warning btn-repost-faktur ml-1"
                                                            data-id="<?= (int)$f['id_faktur'] ?>"
                                                            data-nofaktur="<?= htmlspecialchars($f['no_faktur']) ?>"
                                                            title="Repost item ke SO">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
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

    <!-- Modal Repost Faktur -->
    <div class="modal fade" id="modalRepostFaktur" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-undo mr-1"></i>Repost Item Faktur ke SO
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="repost-loading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Memuat data faktur...</p>
                    </div>
                    <div id="repost-content" style="display:none;">
                        <p class="mb-1"><strong>Faktur:</strong> <span id="repost-no-faktur"></span></p>
                        <table class="table table-bordered table-sm mt-2">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width:40px;" class="text-center">
                                        <input type="checkbox" id="cb-repost-all" title="Pilih semua">
                                    </th>
                                    <th>Nama Barang</th>
                                    <th class="text-center">No Lot</th>
                                    <th class="text-center">Exp Date</th>
                                    <th class="text-right">Qty Faktur</th>
                                    <th class="text-center">Satuan</th>
                                </tr>
                            </thead>
                            <tbody id="repost-tbody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning btn-sm" id="btn-submit-repost" disabled>
                        <i class="fas fa-undo mr-1"></i>Repost Item Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    var isRouteDetail = <?= !empty($selected_rute) ? 'true' : 'false' ?>;
    $('#tabelAdminScFaktur').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: isRouteDetail ? [[2, 'desc']] : [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [isRouteDetail ? 9 : 5] }
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

// ── Repost Faktur ──────────────────────────────────────
var repostIdFaktur = 0;

$(document).on('click', '.btn-repost-faktur', function() {
    repostIdFaktur = $(this).data('id');
    var noFaktur   = $(this).data('nofaktur');

    $('#repost-no-faktur').text(noFaktur);
    $('#repost-loading').show();
    $('#repost-content').hide();
    $('#repost-tbody').html('');
    $('#btn-submit-repost').prop('disabled', true);
    $('#cb-repost-all').prop('checked', false);
    $('#modalRepostFaktur').modal('show');

    $.ajax({
        url: '<?= base_url("sales_order/admin_sc/get_faktur_detail_json") ?>',
        type: 'GET',
        data: { id_faktur: repostIdFaktur },
        dataType: 'JSON',
        success: function(res) {
            if (!res.status) {
                alert(res.message || 'Gagal memuat data.');
                $('#modalRepostFaktur').modal('hide');
                return;
            }
            var tbody = '';
            $.each(res.items, function(i, item) {
                var exp = item.expired_date ? item.expired_date : '-';
                var isUnloaded = (parseInt(item.checker_loaded) === 2);
                var rowStyle = isUnloaded ? ' class="table-danger text-danger" style="background-color: #fff5f5 !important;"' : '';
                tbody += '<tr' + rowStyle + '>'
                    + '<td class="text-center"><input type="checkbox" class="cb-repost-item" value="' + item.id + '"' + (isUnloaded ? ' checked' : '') + '></td>'
                    + '<td>' 
                        + (item.nama_barang || '-') 
                        + (isUnloaded ? ' <span class="badge badge-danger ml-1">Tidak Termuat</span>' : '')
                    + '</td>'
                    + '<td class="text-center">' + (item.no_lot || '-') + '</td>'
                    + '<td class="text-center">' + exp + '</td>'
                    + '<td class="text-right font-weight-bold">' + parseFloat(item.qty).toLocaleString('id-ID', {minimumFractionDigits:2}) + '</td>'
                    + '<td class="text-center">' + (item.satuan || '') + '</td>'
                    + '</tr>';
            });
            $('#repost-tbody').html(tbody);
            $('#repost-loading').hide();
            $('#repost-content').show();
            updateRepostBtn();
        },
        error: function() {
            alert('Gagal memuat data faktur.');
            $('#modalRepostFaktur').modal('hide');
        }
    });
});

$('#cb-repost-all').on('change', function() {
    $('.cb-repost-item').prop('checked', $(this).is(':checked'));
    updateRepostBtn();
});

$(document).on('change', '.cb-repost-item', function() {
    updateRepostBtn();
    $('#cb-repost-all').prop('checked',
        $('.cb-repost-item').length === $('.cb-repost-item:checked').length
    );
});

function updateRepostBtn() {
    $('#btn-submit-repost').prop('disabled', $('.cb-repost-item:checked').length === 0);
}

$('#btn-submit-repost').on('click', function() {
    var ids = [];
    $('.cb-repost-item:checked').each(function() { ids.push($(this).val()); });
    if (ids.length === 0) return;

    if (!confirm('Yakin merepost ' + ids.length + ' item kembali ke SO?\n\nItem ini akan dikeluarkan dari faktur dan bisa difakturkan ulang.')) return;

    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...');

    $.ajax({
        url: '<?= base_url("sales_order/admin_sc/repost_faktur_item") ?>',
        type: 'POST',
        data: { id_faktur: repostIdFaktur, id_fd_list: ids },
        dataType: 'JSON',
        success: function(res) {
            if (res.status) {
                $('#modalRepostFaktur').modal('hide');
                alert(res.faktur_cancelled
                    ? '✅ Semua item direpost. Faktur otomatis dibatalkan karena tidak ada item tersisa.'
                    : '✅ Item berhasil direpost ke SO.'
                );
                location.reload();
            } else {
                alert('❌ ' + (res.message || 'Gagal repost.'));
                btn.prop('disabled', false).html('<i class="fas fa-undo mr-1"></i>Repost Item Terpilih');
            }
        },
        error: function() {
            alert('Terjadi kesalahan koneksi.');
            btn.prop('disabled', false).html('<i class="fas fa-undo mr-1"></i>Repost Item Terpilih');
        }
    });
});
</script>
