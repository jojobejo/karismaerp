<!-- views/content/sales/so_list.php -->
<style>
    #tabelSO_wrapper {
        padding: 0 6px 6px;
    }

    #tabelSO_wrapper .row:first-child,
    #tabelSO_wrapper .row:last-child {
        margin-left: 0;
        margin-right: 0;
        padding: 4px 8px;
    }

    #tabelSO_wrapper .dataTables_length,
    #tabelSO_wrapper .dataTables_filter,
    #tabelSO_wrapper .dataTables_info,
    #tabelSO_wrapper .dataTables_paginate {
        margin: 0;
        padding-top: 0;
    }

    #tabelSO_wrapper .dataTables_length label,
    #tabelSO_wrapper .dataTables_filter label {
        align-items: center;
        display: flex;
        gap: 6px;
        margin-bottom: 0;
    }

    #tabelSO_wrapper .dataTables_filter label {
        justify-content: flex-end;
    }

    #tabelSO_wrapper .dataTables_filter input {
        margin-left: 0;
        height: 30px;
        padding: 3px 8px;
        max-width: 180px;
    }

    #tabelSO_wrapper .dataTables_length select {
        height: 30px;
        padding: 3px 6px;
    }

    #tabelSO_wrapper .row:first-child {
        align-items: center;
        margin-bottom: 4px;
    }

    .so-status-badge {
        display: inline-flex;
        justify-content: center;
        min-width: 92px;
    }

    @keyframes custom-shake {
        0%, 100% { transform: rotate(0deg); }
        5%, 15%, 25% { transform: rotate(8deg); }
        10%, 20%, 30% { transform: rotate(-8deg); }
        35% { transform: rotate(3deg); }
        40% { transform: rotate(-3deg); }
        45%, 95% { transform: rotate(0deg); }
    }

    .btn-shake-notification {
        animation: custom-shake 2.2s ease-in-out infinite;
        transform-origin: 50% 50%;
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
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
                    <h1 class="m-0"><i class="fas fa-file-invoice mr-2"></i> <?= !empty($show_completed) ? 'Sales Order Selesai' : 'Sales Order' ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Sales Order</li>
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
                        <i class="fas fa-<?= $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-circle' : 'exclamation-triangle') ?> mr-1"></i>
                        <?= $msg ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Panel Approval Harga Manager SC (Button-only Mode) -->
            <?php 
            $user_jobdesk = strtoupper((string)$this->session->userdata('jobdesk'));
            $is_manager_sc = in_array($user_jobdesk, ['MNGSC', 'MANAGER SC', 'MANAGERSC', 'ADMIN'], true);
            ?>

            <?php if ($is_manager_sc): ?>
            <?php endif; ?>

            <!-- TOMBOL AKSI -->
            <div class="row mb-2">
                <div class="col-auto">
                    <a href="<?= base_url('sales_order/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat SO Baru
                    </a>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('sales_order/activity_log') ?>" class="btn btn-info">
                        <i class="fas fa-history"></i> Activity Log
                    </a>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('sales_order/faktur_rute') ?>" class="btn btn-success">
                        <i class="fas fa-route"></i> Faktur per Rute
                    </a>
                </div>
                <div class="col-auto">
                    <a href="<?= base_url('sales_order/so_rute') ?>" class="btn btn-primary">
                        <i class="fas fa-map-marked-alt"></i> SO per Rute
                    </a>
                </div>
                <?php if ($is_manager_sc): ?>
                <div class="col-auto" id="colPanelApproval" style="display: none;">
                    <button type="button" class="btn btn-warning text-dark font-weight-bold btn-shake-notification" id="btnBukaPanelApproval">
                        <i class="fas fa-bell mr-1"></i> Tinjau Permintaan (<span id="managerApprovalCount">0</span>)
                    </button>
                </div>
                <div class="col-auto" id="colPanelCancelPartial" style="display: none;">
                    <button type="button" class="btn btn-dark font-weight-bold btn-shake-notification" id="btnBukaPanelCancelPartial">
                        <i class="fas fa-bell mr-1"></i> Pembatalan Sisa (<span id="managerCancelPartialCount">0</span>)
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- FILTER -->
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter</h3>
                </div>
                <div class="card-body py-2">
                    <form action="<?= base_url('sales_order' . (!empty($show_completed) ? '?selesai=1' : '')) ?>" method="post">
                        <?php if (!empty($show_completed)): ?>
                            <input type="hidden" name="selesai" value="1">
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
                            <div class="col-md-3">
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
                            <div class="col-md-2">
                                <label class="small mb-0">Status</label>
                                <select name="status" class="form-control form-control-sm">
                                    <?php if (!empty($show_completed)): ?>
                                        <option value="completed" selected>Completed</option>
                                    <?php else: ?>
                                        <option value="">-- Semua Status --</option>
                                        <option value="draft"     <?= ($filter['status'] ?? '') === 'draft'     ? 'selected' : '' ?>>Draft</option>
                                        <option value="open"      <?= ($filter['status'] ?? '') === 'open'      ? 'selected' : '' ?>>Open</option>
                                        <option value="sedang_verifikasi" <?= ($filter['status'] ?? '') === 'sedang_verifikasi' ? 'selected' : '' ?>>Verifikasi</option>
                                        <option value="siap_faktur" <?= ($filter['status'] ?? '') === 'siap_faktur' ? 'selected' : '' ?>>Siap Faktur</option>
                                        <option value="partial" <?= ($filter['status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                                        <option value="cancelled" <?= ($filter['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-success btn-sm mr-1">
                                    <i class="fas fa-search"></i> Tampil
                                </button>
                                <a href="<?= base_url('sales_order' . (!empty($show_completed) ? '?selesai=1' : '')) ?>" class="btn btn-secondary btn-sm">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABEL SO -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i> <?= !empty($show_completed) ? 'Daftar Sales Order Selesai' : 'Daftar Sales Order Aktif' ?>
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light"><?= count($so_list) ?> SO</span>
                        <?php if (!empty($show_completed)): ?>
                            <a href="<?= base_url('sales_order') ?>" class="btn btn-sm btn-light ml-2">
                                <i class="fas fa-list"></i> SO Aktif
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('sales_order?selesai=1') ?>" class="btn btn-sm btn-success ml-2">
                                <i class="fas fa-check-circle"></i> SO Selesai
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover table-sm" id="tabelSO">
                        <thead class="thead-dark">
                            <tr>
                                <th>No SO</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th>Rute</th>
                                <th class="text-right">Item Diorder</th>
                                <th class="text-right">Item Selesai</th>
                                <th class="text-right">Outstanding</th>
                                <th class="text-center" style="min-width:170px;">Progress</th>
                                <th class="text-center">Status</th>
                                <th class="text-center no-sort" style="min-width:120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($so_list)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Tidak ada data Sales Order
                                    </td>
                                </tr>
                            <?php else: ?>
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
                                    'sedang_verifikasi'  => 'Verifikasi',
                                    'siap_faktur'        => 'Siap Faktur',
                                    'partial'            => 'Partial',
                                    'completed'          => 'Completed',
                                    'cancelled'          => 'Cancelled',
                                ];
                                foreach ($so_list as $row):
                                    $badge      = $badge_map[$row['status']] ?? 'secondary';
                                    $label      = $label_map[$row['status']] ?? $row['status'];
                                    $item_diorder  = (int)($row['jumlah_item']          ?? 0);
                                    $item_diterima = (int)($row['jumlah_item_diterima']  ?? 0);
                                    $outstanding   = $item_diorder - $item_diterima;
                                    $pct           = $item_diorder > 0 ? round(($item_diterima / $item_diorder) * 100, 1) : 0;

                                    if ($row['status'] === 'completed' || $pct >= 100) {
                                        $bar_color = 'success';
                                    } elseif ($row['status'] === 'cancelled') {
                                        $bar_color = 'danger';
                                    } elseif ($pct > 0) {
                                        $bar_color = 'warning';
                                    } else {
                                        $bar_color = 'secondary';
                                    }
                                    $is_pending_cancel = isset($pending_cancels) && in_array($row['id_so'], $pending_cancels);
                                ?>
                                <tr <?= $is_pending_cancel ? 'style="background-color: #ffeeba;" title="Ada permintaan pembatalan parsial menunggu persetujuan"' : '' ?>>
                                    <td>
                                        <a href="<?= base_url('sales_order/detail/' . $row['id_so']) ?>"
                                           class="font-weight-bold">
                                            <?= htmlspecialchars($row['no_so']) ?>
                                        </a>
                                    </td>
                                    <td class="text-nowrap"><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                    <td><?= !empty($row['customer_kd_rute']) ? htmlspecialchars($row['customer_kd_rute']) : '<span class="text-muted">-</span>' ?></td>
                                    <td class="text-center"><?= number_format($item_diorder) ?></td>
                                    <td class="text-center text-success font-weight-bold">
                                        <?= number_format($item_diterima) ?>
                                    </td>
                                    <td class="text-center <?= $outstanding > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                        <?= number_format($outstanding) ?>
                                    </td>

                                    <!-- PROGRESS -->
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-1" style="height:16px;">
                                                <div class="progress-bar bg-<?= $bar_color ?>"
                                                     style="width:<?= $pct ?>%; font-size:10px; line-height:16px;">
                                                    <?= $pct > 15 ? $pct . '%' : '' ?>
                                                </div>
                                            </div>
                                            <small class="text-nowrap font-weight-bold text-<?= $bar_color === 'secondary' ? 'muted' : $bar_color ?>"
                                                   style="min-width:38px;">
                                                <?= $pct ?>%
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge badge-<?= $badge ?> <?= ($row['status'] === 'partial' || $badge === 'warning') ? 'text-white' : '' ?> so-status-badge px-2 py-1"><?= $label ?></span>
                                    </td>

                                    <!-- TOMBOL AKSI — diperbesar -->
                                    <td class="text-center text-nowrap">
                                        <a href="<?= base_url('sales_order/detail/' . $row['id_so']) ?>"
                                           class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($row['status'] === 'partial' && !$is_pending_cancel): ?>
                                            <button type="button" class="btn btn-sm btn-warning btn-cancel-partial" data-id="<?= $row['id_so'] ?>" title="Batalkan Sisa Barang">
                                                <i class="fas fa-times-circle text-white"></i>
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

            <?php if ($is_manager_sc): ?>
            <!-- Modal Panel Approval Harga -->
            <div class="modal fade" id="modalPanelApprovalHarga" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title font-weight-bold"><i class="fas fa-money-bill-wave mr-1"></i> Permintaan Persetujuan Edit Harga (Admin SC)</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No. SO</th>
                                            <th>Customer</th>
                                            <th>Barang</th>
                                            <th class="text-right">Harga Lama</th>
                                            <th class="text-right text-success">Harga Baru</th>
                                            <th>Diajukan Oleh</th>
                                            <th class="text-center" width="20%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="panel-approval-body">
                                        <!-- Loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Modal Cancel Partial -->
            <div class="modal fade" id="modalCancelPartial" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Batalkan Sisa Barang (Partial)</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Pilih barang yang belum terfaktur untuk dibatalkan:</p>
                            <form id="formCancelPartial">
                                <input type="hidden" name="id_so" id="cp_id_so">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 40px; text-align: center;"><input type="checkbox" id="cp_check_all"></th>
                                                <th>Kode</th>
                                                <th>Nama Barang</th>
                                                <th class="text-right">Qty Sisa (Batal)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cp_items_tbody">
                                            <!-- Data injected via JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-danger btn-sm" id="btnSubmitCancelPartial"><i class="fas fa-paper-plane"></i> Ajukan Pembatalan</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($is_manager_sc): ?>
            <!-- Modal Cancel Partial (Manager SC) -->
            <div class="modal fade" id="modalPanelCancelPartial" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="fas fa-times-circle mr-2"></i> Persetujuan Pembatalan Sisa Barang</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center" style="width: 40px;"><input type="checkbox" id="mgr_cp_check_all"></th>
                                            <th>No. SO</th>
                                            <th>Tgl SO</th>
                                            <th>Customer</th>
                                            <th>Barang</th>
                                            <th class="text-right">Hrg Satuan</th>
                                            <th class="text-right">Sisa (Batal)</th>
                                            <th class="text-right">Total Hrg</th>
                                            <th>Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody id="panel-cancel-partial-body">
                                        <!-- Loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <div>
                                <button type="button" class="btn btn-success btn-sm" id="btnMgrApproveCancelPartial"><i class="fas fa-check"></i> Setujui Terpilih</button>
                                <button type="button" class="btn btn-danger btn-sm" id="btnMgrRejectCancelPartial"><i class="fas fa-times"></i> Tolak Terpilih</button>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                        </div>
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
</div>

<script>
$(document).ready(function () {
    $('#tabelSO').DataTable({
        responsive:  true,
        autoWidth:   false,
        pageLength:  25,
        order:       [[1, 'desc']],
        columnDefs:  [
            { orderable: false, targets: [7, 9] }
        ],
        language: {
            search:      "Cari:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable:  "Tidak ada data Sales Order",
            paginate:    { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });

    <?php if ($is_manager_sc): ?>
    function loadPendingApprovals() {
        $.ajax({
            url: '<?= base_url("sales_order/admin_sc/get_pending_approvals") ?>',
            type: 'GET',
            dataType: 'JSON',
            success: function(res) {
                if (res && res.msg === 'success' && res.data.length > 0) {
                    $('#managerApprovalCount').text(res.data.length);
                    $('#colPanelApproval').fadeIn(200);
                    
                    let html = '';
                    res.data.forEach(function(row) {
                        html += `<tr>
                            <td>${row.no_so}</td>
                            <td>${row.nm_customer || '-'}</td>
                            <td>${row.nama_barang || '-'}</td>
                            <td class="text-right">Rp ${parseFloat(row.harga_lama).toLocaleString('id-ID')}</td>
                            <td class="text-right text-success font-weight-bold">Rp ${parseFloat(row.harga_baru).toLocaleString('id-ID')}</td>
                            <td>${row.requested_by}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-xs btn-success btn-approve-harga-mgr" data-id="${row.id}"><i class="fas fa-check"></i> Setuju</button>
                                <button type="button" class="btn btn-xs btn-danger btn-reject-harga-mgr" data-id="${row.id}"><i class="fas fa-times"></i> Tolak</button>
                            </td>
                        </tr>`;
                    });
                    $('#panel-approval-body').html(html);
                } else {
                    $('#colPanelApproval').fadeOut(200);
                }
            }
        });
    }

    loadPendingApprovals();
    
    // Interval check every 15 seconds
    setInterval(loadPendingApprovals, 15000);

    $('#btnBukaPanelApproval').on('click', function() {
        $('#modalPanelApprovalHarga').modal('show');
    });

    $(document).on('click', '.btn-approve-harga-mgr', function() {
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url("sales_order/admin_sc/approve_harga") ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'JSON',
            success: function(res) {
                if (res && res.msg === 'success') {
                    alert('✅ ' + res.message);
                    loadPendingApprovals();
                    setTimeout(function() {
                        if ($('#panel-approval-body tr').length <= 1) {
                            $('#modalPanelApprovalHarga').modal('hide');
                        }
                    }, 500);
                } else {
                    alert('❌ ' + (res.message || 'Gagal menyetujui.'));
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('Terjadi kesalahan koneksi.');
                btn.prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-reject-harga-mgr', function() {
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url("sales_order/admin_sc/reject_harga") ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'JSON',
            success: function(res) {
                if (res && res.msg === 'success') {
                    alert('✅ ' + res.message);
                    loadPendingApprovals();
                    setTimeout(function() {
                        if ($('#panel-approval-body tr').length <= 1) {
                            $('#modalPanelApprovalHarga').modal('hide');
                        }
                    }, 500);
                } else {
                    alert('❌ ' + (res.message || 'Gagal menolak.'));
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('Terjadi kesalahan koneksi.');
                btn.prop('disabled', false);
            }
        });
    });
    <?php endif; ?>

    // Cancel Partial Script
    $('.btn-cancel-partial').click(function(e) {
        e.preventDefault();
        let id_so = $(this).data('id');
        $('#cp_id_so').val(id_so);
        $('#cp_items_tbody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');
        $('#cp_check_all').prop('checked', false);
        $('#modalCancelPartial').modal('show');

        $.get('<?= base_url('sales_order/get_partial_items/') ?>' + id_so, function(res) {
            let data = JSON.parse(res);
            let html = '';
            if (data.msg === 'success' && data.data.length > 0) {
                data.data.forEach(function(item) {
                    let sisa = parseFloat(item.qty) - parseFloat(item.qty_faktur);
                    html += `<tr>
                        <td class="text-center"><input type="checkbox" class="cp-item-check" value="${item.id_so_detail}" data-qty="${sisa}"></td>
                        <td>${item.kd_barang}</td>
                        <td>${item.nama_barang}</td>
                        <td class="text-right text-danger font-weight-bold">${sisa}</td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="4" class="text-center">Tidak ada sisa barang yang bisa dibatalkan.</td></tr>';
            }
            $('#cp_items_tbody').html(html);
        });
    });

    $('#cp_check_all').change(function() {
        $('.cp-item-check').prop('checked', $(this).prop('checked'));
    });

    $('#btnSubmitCancelPartial').click(function() {
        let items = [];
        $('.cp-item-check:checked').each(function() {
            items.push({
                id_so_detail: $(this).val(),
                qty_cancel: $(this).data('qty')
            });
        });

        if (items.length === 0) {
            alert('Pilih minimal 1 barang untuk dibatalkan.');
            return;
        }

        if (confirm('Ajukan pembatalan sisa barang ke Manager SC?')) {
            let btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Proses...');
            $.post('<?= base_url('sales_order/request_cancel_partial') ?>', {
                id_so: $('#cp_id_so').val(),
                items: items
            }, function(res) {
                let data = JSON.parse(res);
                if (data.msg === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Ajukan Pembatalan');
                }
            });
        }
    });

    <?php if ($is_manager_sc): ?>
    // Cancel Partial Logic (Manager SC)
    function loadPendingCancelPartial() {
        $.get('<?= base_url('sales_order/admin_sc_get_pending_cancel_requests') ?>', function(res) {
            let data = JSON.parse(res);
            let count = data.data.length;
            if (count > 0) {
                $('#managerCancelPartialCount').text(count);
                $('#colPanelCancelPartial').show();
                
                let html = '';
                data.data.forEach(function(row) {
                    let hrg = parseFloat(row.hrg_satuan || 0);
                    let qty = parseFloat(row.qty_cancel || 0);
                    let total = hrg * qty;
                    html += `<tr>
                        <td class="text-center"><input type="checkbox" class="mgr-cp-check" value="${row.id}"></td>
                        <td>${row.no_so}</td>
                        <td>${row.tanggal_transaksi}</td>
                        <td>${row.nama_customer || '-'}</td>
                        <td>${row.nama_barang || '-'}</td>
                        <td class="text-right text-nowrap">Rp ${hrg.toLocaleString('id-ID')}</td>
                        <td class="text-right text-danger font-weight-bold">${qty}</td>
                        <td class="text-right text-nowrap">Rp ${total.toLocaleString('id-ID')}</td>
                        <td>${row.request_by}</td>
                    </tr>`;
                });
                $('#panel-cancel-partial-body').html(html);
            } else {
                $('#colPanelCancelPartial').hide();
                $('#panel-cancel-partial-body').html('<tr><td colspan="7" class="text-center">Tidak ada permintaan.</td></tr>');
            }
        });
    }

    loadPendingCancelPartial();
    setInterval(loadPendingCancelPartial, 15000);

    $('#btnBukaPanelCancelPartial').on('click', function() {
        $('#mgr_cp_check_all').prop('checked', false);
        $('#modalPanelCancelPartial').modal('show');
    });

    $('#mgr_cp_check_all').change(function() {
        $('.mgr-cp-check').prop('checked', $(this).prop('checked'));
    });

    $('#btnMgrApproveCancelPartial, #btnMgrRejectCancelPartial').click(function() {
        let isApprove = $(this).attr('id') === 'btnMgrApproveCancelPartial';
        let ids = [];
        $('.mgr-cp-check:checked').each(function() {
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            alert('Pilih minimal 1 data.');
            return;
        }

        if (confirm(isApprove ? 'Setujui pembatalan terpilih?' : 'Tolak pembatalan terpilih?')) {
            let btn = $(this);
            let url = isApprove ? '<?= base_url('sales_order/admin_sc_approve_cancel_partial') ?>' : '<?= base_url('sales_order/admin_sc_reject_cancel_partial') ?>';
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Proses...');
            
            $.post(url, { request_ids: ids }, function(res) {
                let data = JSON.parse(res);
                if (data.msg === 'success') {
                    alert(data.message);
                    loadPendingCancelPartial();
                    $('#modalPanelCancelPartial').modal('hide');
                    location.reload();
                } else {
                    alert(data.message);
                }
                btn.prop('disabled', false).html(isApprove ? '<i class="fas fa-check"></i> Setujui Terpilih' : '<i class="fas fa-times"></i> Tolak Terpilih');
            });
        }
    });
    <?php endif; ?>

});
</script>
