<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <?php
    $so_e = function ($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    };
    $input_rows = $input_rows ?? [];
    $master_items = $master_items ?? [];
    $compare = $compare ?? [];
    $edit_logs = $edit_logs ?? [];
    $input_by_team = [
        1 => [],
        2 => [],
    ];
    foreach ($input_rows as $row) {
        $team = (int)($row['tim_opname'] ?? 0);
        if (isset($input_by_team[$team])) {
            $input_by_team[$team][] = $row;
        }
    }
    $lot_key = function ($expiredDate, $lot) {
        return trim((string)$expiredDate) . '||' . trim((string)$lot);
    };
    $stock_by_lot = [];
    foreach ($master_items as $row) {
        $stock_by_lot[$lot_key($row['expired_date'] ?? '', $row['no_lot'] ?? '')] = (int)($row['qty_buku'] ?? $row['qty'] ?? 0);
    }
    $nama_barang = $compare['nama_barang'] ?? ($master_items[0]['nama_barang'] ?? ($input_rows[0]['nama_barang'] ?? '-'));
    $qty_buku = (int)($compare['qty_buku'] ?? array_sum(array_column($master_items, 'qty')));
    $qty_tim_1 = (int)($compare['qty_tim_1'] ?? 0);
    $qty_tim_2 = (int)($compare['qty_tim_2'] ?? 0);
    $status = $compare['status_opname'] ?? 're_check';
    $status_label = [
        'all_match' => 'All Match',
        'tim_1' => 'Tim 1 Match',
        'tim_2' => 'Tim 2 Match',
        're_check' => 'Re-Check',
    ][$status] ?? 'Re-Check';
    $total_input = count($input_rows);
    ?>

    <div class="content-wrapper so-detail-page">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-8">
                        <h1 class="m-0">Detail Input Opname</h1>
                        <div class="so-muted mt-1"><?= $so_e($kode_barang ?? '-') ?> - <?= $so_e($nama_barang) ?></div>
                    </div>
                    <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('admin/stockopname/monitoring') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Monitoring
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid pb-4">
                <style>
                    .so-detail-page{background:#f5f7fb}.so-muted{color:#64748b;font-size:12px}.so-panel{background:#fff;border:1px solid #e1e7ef;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06)}
                    .so-panel-header{padding:14px 16px;border-bottom:1px solid #e8edf3;display:flex;align-items:center;justify-content:space-between;gap:10px}.so-title{font-weight:800;color:#1f2937;margin:0;font-size:16px}
                    .so-code{font-family:monospace;font-size:12px;background:#f8fafc;border:1px solid #dbe5ef;border-radius:6px;padding:4px 7px;color:#334155}.so-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px}
                    .so-stat{background:#fff;border:1px solid #e1e7ef;border-radius:8px;padding:14px;box-shadow:0 8px 22px rgba(16,24,40,.05)}.so-stat-label{font-size:11px;text-transform:uppercase;color:#64748b;font-weight:800}.so-stat-value{font-size:24px;font-weight:850;color:#111827;line-height:1.1;margin-top:7px}
                    .so-badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 9px;font-size:12px;font-weight:800;background:#fee2e2;color:#991b1b}.so-badge.all_match{background:#dcfce7;color:#166534}.so-badge.tim_1{background:#dbeafe;color:#1d4ed8}.so-badge.tim_2{background:#ede9fe;color:#6d28d9}
                    .so-match-badge{display:inline-flex;align-items:center;border-radius:999px;padding:3px 7px;font-size:11px;font-weight:850;background:#fee2e2;color:#991b1b;white-space:nowrap}.so-match-badge.match{background:#dcfce7;color:#166534}.so-diff-plus{color:#166534}.so-diff-minus{color:#991b1b}
                    .table td,.table th{vertical-align:middle}.btn i{margin-right:5px}.so-empty{color:#64748b;text-align:center;padding:28px 12px}.so-action-btn{width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center}.so-action-btn i{margin-right:0}
                    .so-panel.is-compact .so-panel-header{padding:10px 12px}.so-panel.is-compact .table-responsive{padding:8px!important}.so-table-row{display:grid;grid-template-columns:minmax(380px,40%) minmax(0,60%);gap:12px}.so-compact-table{font-size:13px;line-height:1.25;table-layout:fixed;width:100%;white-space:nowrap}.so-compact-table td,.so-compact-table th{padding:.34rem .42rem;overflow:hidden;text-overflow:ellipsis}.so-compact-table th{font-size:12px;text-align:center;background:#f8fafc}.so-lot-table{min-width:430px}.so-lot-table td:nth-child(3),.so-lot-table th:nth-child(3),.so-lot-table td:nth-child(4),.so-lot-table th:nth-child(4){text-align:right}.so-lot-table td:nth-child(5),.so-lot-table th:nth-child(5),.so-lot-table td:nth-child(6),.so-lot-table th:nth-child(6){text-align:center}.so-input-table{min-width:760px}.so-input-table td:nth-child(n+3),.so-input-table th:nth-child(n+3){text-align:right}.so-input-table td:nth-child(7),.so-input-table th:nth-child(7){text-align:center}.so-cell-main{font-weight:800;color:#1f2937;line-height:1.2;overflow:hidden;text-overflow:ellipsis}.so-cell-sub{font-size:11px;color:#64748b;line-height:1.25;margin-top:2px;overflow:hidden;text-overflow:ellipsis}.so-qty-stack{line-height:1.25}.so-qty-stack span{display:block}.so-filter-action.is-active{background:#16a34a;color:#fff;border-color:#16a34a}.so-filter-info{font-size:11px;color:#2563eb;font-weight:800}.so-filter-empty{display:none;color:#64748b;text-align:center;padding:16px 8px}.so-header-main{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.so-filter-chips{display:inline-flex;align-items:center;gap:5px;flex-wrap:wrap}.so-filter-chip{display:inline-flex;align-items:center;border:1px solid #dbe5ef;border-radius:999px;background:#f8fafc;color:#334155;font-size:11px;font-weight:850;padding:3px 8px}.so-filter-chip strong{color:#0f172a;margin-right:4px}.so-filter-chip.status-ok{background:#dcfce7;border-color:#bbf7d0;color:#166534}.so-filter-chip.status-cek{background:#fee2e2;border-color:#fecaca;color:#991b1b}
                    .so-tabs{border-bottom:1px solid #e1e7ef}.so-tabs .nav-link{font-weight:800;color:#475569;border-radius:0;border:0;border-bottom:3px solid transparent}.so-tabs .nav-link.active{color:#2563eb;border-bottom-color:#2563eb;background:#f8fafc}
                    .so-log-list{display:grid;gap:10px}.so-log-item{border:1px solid #e1e7ef;border-radius:8px;background:#f8fafc;padding:10px}.so-log-title{font-size:13px;font-weight:800;color:#1f2937}.so-log-meta{font-size:12px;color:#64748b;margin-top:3px}
                    .so-edit-modal{border:0;border-radius:8px;overflow:hidden;box-shadow:0 24px 70px rgba(15,23,42,.22)}.so-edit-modal .modal-header{background:#1f2937;color:#fff;border:0;padding:16px 18px}.so-edit-modal .modal-title{font-weight:850}.so-edit-modal .close{color:#fff;opacity:.85;text-shadow:none}
                    .so-edit-modal .modal-body{background:#f8fafc;padding:18px}.so-modal-context{background:#fff;border:1px solid #e1e7ef;border-radius:8px;padding:14px;margin-bottom:14px}.so-modal-context-title{font-size:13px;font-weight:850;color:#111827;line-height:1.4}.so-modal-context-code{margin-top:5px;font-family:monospace;font-size:12px;color:#475569}
                    .so-field-card{background:#fff;border:1px solid #e1e7ef;border-radius:8px;padding:12px;height:100%}.so-field-card label{font-size:11px;text-transform:uppercase;font-weight:850;color:#64748b;margin-bottom:6px}.so-field-card .form-control[readonly]{background:#f1f5f9;color:#475569;border-color:#dbe5ef}.so-field-card.is-editable{border-color:#93c5fd;background:#eff6ff}.so-field-card.is-editable .form-control{font-size:22px;font-weight:850;color:#111827;border-color:#93c5fd;background:#fff}.so-modal-help{font-size:12px;color:#64748b;margin-top:10px}
                    @media(max-width:992px){.so-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.so-table-row{grid-template-columns:1fr}}@media(max-width:576px){.so-summary{grid-template-columns:1fr}.content-header h1{font-size:22px}.so-panel-header{align-items:flex-start;flex-direction:column}}
                </style>

                <div class="so-summary">
                    <div class="so-stat">
                        <div class="so-stat-label">Kode Barang</div>
                        <div class="so-stat-value"><span class="so-code"><?= $so_e($kode_barang ?? '-') ?></span></div>
                    </div>
                    <div class="so-stat">
                        <div class="so-stat-label">Stock Buku</div>
                        <div class="so-stat-value"><?= number_format($qty_buku, 0, ',', '.') ?></div>
                    </div>
                    <div class="so-stat">
                        <div class="so-stat-label">Qty Tim 1</div>
                        <div class="so-stat-value"><?= number_format($qty_tim_1, 0, ',', '.') ?></div>
                    </div>
                    <div class="so-stat">
                        <div class="so-stat-label">Qty Tim 2</div>
                        <div class="so-stat-value"><?= number_format($qty_tim_2, 0, ',', '.') ?></div>
                    </div>
                    <div class="so-stat">
                        <div class="so-stat-label">Status</div>
                        <div class="so-stat-value"><span class="so-badge <?= $so_e($status) ?>"><?= $so_e($status_label) ?></span></div>
                    </div>
                </div>

                <div class="so-table-row">
                    <div class="mb-3">
                        <div class="so-panel is-compact h-100">
                            <div class="so-panel-header">
                                <h2 class="so-title">Stock Buku Per Lot</h2>
                                <span class="so-muted"><?= count($master_items) ?> data</span>
                            </div>
                            <div class="table-responsive p-3">
                                <?php if (empty($master_items)) : ?>
                                    <div class="so-empty">Data master barang tidak ditemukan.</div>
                                <?php else : ?>
                                    <ul class="nav nav-tabs so-tabs mb-2" role="tablist">
                                        <?php foreach ([1, 2] as $team) : ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?= $team === 1 ? 'active' : '' ?>" data-toggle="pill" href="#stock-pane-tim-<?= $team ?>" role="tab">Tim <?= $team ?></a>
                                            </li>
                                        <?php endforeach ?>
                                    </ul>
                                    <div class="tab-content">
                                        <?php foreach ([1, 2] as $team) : ?>
                                            <div class="tab-pane fade <?= $team === 1 ? 'show active' : '' ?>" id="stock-pane-tim-<?= $team ?>" role="tabpanel">
                                                <table class="table table-sm table-bordered table-hover mb-0 so-compact-table so-lot-table">
                                                    <colgroup>
                                                        <col style="width:24%">
                                                        <col style="width:18%">
                                                        <col style="width:16%">
                                                        <col style="width:16%">
                                                        <col style="width:14%">
                                                        <col style="width:12%">
                                                    </colgroup>
                                                    <thead>
                                                        <tr>
                                                            <th>Expired Date</th>
                                                            <th>LOT</th>
                                                            <th>Qty Buku</th>
                                                            <th>Qty Input</th>
                                                            <th>Status</th>
                                                            <th>#</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($master_items as $row) : ?>
                                                            <?php
                                                            $qty_buku_lot = (int)($row['qty_buku'] ?? $row['qty'] ?? 0);
                                                            $qty_input_lot = (int)($row['qty_tim_' . $team] ?? 0);
                                                            $is_match = (int)($row['tim_' . $team . '_match'] ?? ($qty_input_lot === $qty_buku_lot ? 1 : 0));
                                                            $expired_date = $row['expired_date'] ?? '';
                                                            $no_lot = $row['no_lot'] ?? '';
                                                            ?>
                                                            <tr>
                                                                <td><?= $so_e($expired_date ?: '-') ?></td>
                                                                <td title="<?= $so_e($no_lot ?: '-') ?>"><?= $so_e($no_lot ?: '-') ?></td>
                                                                <td class="text-right"><?= number_format($qty_buku_lot, 0, ',', '.') ?></td>
                                                                <td class="text-right font-weight-bold"><?= number_format($qty_input_lot, 0, ',', '.') ?></td>
                                                                <td><span class="so-match-badge <?= $is_match ? 'match' : '' ?>"><?= $is_match ? 'OK' : 'Cek' ?></span></td>
                                                                <td>
                                                                    <button type="button" class="btn btn-outline-success btn-sm so-action-btn so-filter-action js-filter-input" data-team="<?= $team ?>" data-exp="<?= $so_e($expired_date) ?>" data-lot="<?= $so_e($no_lot) ?>" data-status="<?= $is_match ? 'OK' : 'Cek' ?>" title="Filter input berdasarkan lot ini">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="so-panel is-compact h-100">
                            <div class="so-panel-header">
                                <div class="so-header-main">
                                    <h2 class="so-title">Data Hasil Input Opname</h2>
                                    <div class="so-filter-chips d-none" id="inputFilterChips">
                                        <span class="so-filter-chip"><strong>Tim</strong><span id="filterTeam">-</span></span>
                                        <span class="so-filter-chip"><strong>Exp</strong><span id="filterExp">-</span></span>
                                        <span class="so-filter-chip"><strong>Lot</strong><span id="filterLot">-</span></span>
                                        <span class="so-filter-chip" id="filterStatusChip"><strong>Status</strong><span id="filterStatus">-</span></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="so-muted"><?= $total_input ?> input</div>
                                    <div class="so-filter-info d-none" id="inputFilterInfo"></div>
                                </div>
                            </div>
                            <div class="table-responsive p-3">
                                <?php if (empty($input_rows)) : ?>
                                    <div class="so-empty">Belum ada hasil input opname.</div>
                                <?php else : ?>
                                    <table class="table table-sm table-bordered table-hover mb-0 so-compact-table so-input-table">
                                        <colgroup>
                                            <col style="width:24%">
                                            <col style="width:24%">
                                            <col style="width:13%">
                                            <col style="width:11%">
                                            <col style="width:11%">
                                            <col style="width:11%">
                                            <col style="width:6%">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>Input</th>
                                                <th>Expired / Lot</th>
                                                <th class="text-right">Qty Buku</th>
                                                <th class="text-right">Qty</th>
                                                <th class="text-right">Qty Box</th>
                                                <th class="text-right">Qty Pcs</th>
                                                <th class="text-center">#</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($input_rows as $row) : ?>
                                                <?php
                                                $team = (int)($row['tim_opname'] ?? 0);
                                                $row_json = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                                $input_time = $row['created_at'] ?? $row['input_at'] ?? '-';
                                                $input_user = $row['input_by'] ?? '-';
                                                $wilayah = $row['wilayah'] ?? '-';
                                                $expired_date = $row['expired_date'] ?? '';
                                                $no_lot = $row['no_lot'] ?? '';
                                                $qty_buku_input = $stock_by_lot[$lot_key($expired_date, $no_lot)] ?? 0;
                                                ?>
                                                <tr class="js-input-row" data-team="<?= $team ?>" data-exp="<?= $so_e($expired_date) ?>" data-lot="<?= $so_e($no_lot) ?>">
                                                    <td>
                                                        <div class="so-cell-main"><?= $so_e($input_user) ?></div>
                                                        <div class="so-cell-sub">Tim <?= $so_e($team ?: '-') ?> | <?= $so_e($input_time) ?> | Wil. <?= $so_e($wilayah) ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="so-cell-main"><?= $so_e($expired_date ?: '-') ?></div>
                                                        <div class="so-cell-sub">Lot <?= $so_e($no_lot ?: '-') ?></div>
                                                    </td>
                                                    <td class="text-right"><?= number_format($qty_buku_input, 0, ',', '.') ?></td>
                                                    <td class="text-right font-weight-bold"><?= number_format((int)($row['qty'] ?? 0), 0, ',', '.') ?></td>
                                                    <td class="text-right"><?= number_format((int)($row['qty_box'] ?? 0), 0, ',', '.') ?></td>
                                                    <td class="text-right"><?= number_format((int)($row['qty_pcs'] ?? 0), 0, ',', '.') ?></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-outline-primary btn-sm so-action-btn js-edit-opname" data-row="<?= $row_json ?>" title="Edit input opname">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                    <div class="so-filter-empty js-filter-empty">Tidak ada input opname untuk pilihan lot ini.</div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="so-panel mb-3">
                    <div class="so-panel-header">
                        <h2 class="so-title">Log Perubahan Input Opname</h2>
                        <span class="so-muted"><?= count($edit_logs) ?> aktifitas terakhir</span>
                    </div>
                    <div class="p-3">
                        <?php if (empty($edit_logs)) : ?>
                            <div class="so-empty">Belum ada perubahan data input opname.</div>
                        <?php else : ?>
                            <div class="so-log-list">
                                <?php foreach ($edit_logs as $log) : ?>
                                    <?php
                                    $fields = json_decode((string)($log['changed_fields'] ?? '[]'), true);
                                    $fields = is_array($fields) ? implode(', ', $fields) : '-';
                                    ?>
                                    <div class="so-log-item">
                                        <div class="so-log-title">
                                            #<?= $so_e($log['opname_id'] ?? '-') ?> diperbarui oleh <?= $so_e($log['changed_by'] ?? '-') ?>
                                        </div>
                                        <div class="so-log-meta">
                                            Field: <?= $so_e($fields ?: '-') ?> | <?= $so_e($log['changed_at'] ?? '-') ?>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="modalEditOpname" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content so-edit-modal" id="formEditOpname">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Qty Input Opname</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="editOpnameAlert"></div>
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="kode_barang" id="edit_kode_barang" value="<?= $so_e($kode_barang ?? '') ?>">
                    <div class="so-modal-context">
                        <div class="so-modal-context-title" id="edit_nama_barang_text">-</div>
                        <div class="so-modal-context-code"><?= $so_e($kode_barang ?? '-') ?></div>
                    </div>
                    <input type="hidden" id="edit_nama_barang">
                    <div class="row">
                        <input type="hidden" name="tim_opname" id="edit_tim_opname">
                        <input type="hidden" name="wilayah" id="edit_wilayah">
                        <div class="col-md-4">
                            <div class="so-field-card mb-3">
                                <label>Expired Date</label>
                                <input type="text" class="form-control" name="expired_date" id="edit_expired_date" readonly required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="so-field-card mb-3">
                                <label>Lot</label>
                                <input type="text" class="form-control" name="no_lot" id="edit_no_lot" readonly required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="so-field-card mb-3">
                                <label>Qty Total</label>
                                <input type="number" class="form-control" name="qty" id="edit_qty" min="0" step="1" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="so-field-card is-editable mb-3">
                                <label>Qty Box</label>
                                <input type="number" class="form-control" name="qty_box" id="edit_qty_box" min="0" step="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="so-field-card is-editable mb-3">
                                <label>Qty Pcs</label>
                                <input type="number" class="form-control" name="qty_pcs" id="edit_qty_pcs" min="0" step="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="so-modal-help">Hanya Qty Box dan Qty Pcs yang dapat diubah. Qty Total dihitung otomatis dari dimensi barang dan nilai pcs.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveEditOpname">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>
</div>

<script>
$(function () {
    var updateUrl = '<?= base_url('admin/stockopname/detail_input_opname/update') ?>';
    var pageKodeBarang = <?= json_encode((string)($kode_barang ?? ''), JSON_UNESCAPED_UNICODE) ?>;
    var currentDimensi = 0;

    function sameFilter(button, filter) {
        return filter &&
            String(button.attr('data-team') || '') === String(filter.team) &&
            String(button.attr('data-exp') || '') === String(filter.exp) &&
            String(button.attr('data-lot') || '') === String(filter.lot);
    }

    function applyInputFilter(filter) {
        $('.js-filter-input').removeClass('is-active');

        if (!filter) {
            $('.js-input-row').show();
            $('.js-filter-empty').hide();
            $('#inputFilterInfo').addClass('d-none').text('');
            $('#inputFilterChips').addClass('d-none');
            $('#filterStatusChip').removeClass('status-ok status-cek');
            return;
        }

        $('.js-filter-input').filter(function () {
            return sameFilter($(this), filter);
        }).addClass('is-active');

        $('#inputFilterInfo')
            .removeClass('d-none')
            .text('Filter: Tim ' + filter.team + ' | ' + (filter.exp || '-') + ' | Lot ' + (filter.lot || '-'));
        $('#inputFilterChips').removeClass('d-none');
        $('#filterTeam').text(filter.team || '-');
        $('#filterExp').text(filter.exp || '-');
        $('#filterLot').text(filter.lot || '-');
        $('#filterStatus').text(filter.status || '-');
        $('#filterStatusChip')
            .removeClass('status-ok status-cek')
            .addClass(filter.status === 'OK' ? 'status-ok' : 'status-cek');

        $('.js-input-row').each(function () {
            var row = $(this);
            var show = String(row.attr('data-team') || '') === String(filter.team) &&
                String(row.attr('data-exp') || '') === String(filter.exp) &&
                String(row.attr('data-lot') || '') === String(filter.lot);
            row.toggle(show);
        });

        $('.js-filter-empty').toggle($('.js-input-row:visible').length === 0);
    }

    $('.js-filter-input').on('click', function () {
        var button = $(this);
        var filter = {
            team: String(button.attr('data-team') || ''),
            exp: String(button.attr('data-exp') || ''),
            lot: String(button.attr('data-lot') || ''),
            status: String(button.attr('data-status') || '')
        };

        if (sameFilter(button, window.activeInputFilter || null)) {
            window.activeInputFilter = null;
        } else {
            window.activeInputFilter = filter;
        }
        applyInputFilter(window.activeInputFilter);
    });

    function intValue(selector) {
        return parseInt($(selector).val() || 0, 10) || 0;
    }

    function refreshQtyTotal() {
        var qtyBox = intValue('#edit_qty_box');
        var qtyPcs = intValue('#edit_qty_pcs');
        $('#edit_qty').val((qtyBox * currentDimensi) + qtyPcs);
    }

    $('.js-edit-opname').on('click', function () {
        var row = {};
        try {
            row = JSON.parse($(this).attr('data-row') || '{}');
        } catch (e) {
            row = {};
        }

        var originalQtyBox = parseInt(row.qty_box || 0, 10) || 0;
        var originalQtyPcs = parseInt(row.qty_pcs || 0, 10) || 0;
        var originalQty = parseInt(row.qty || 0, 10) || 0;
        currentDimensi = parseInt(row.dimensi || 0, 10) || 0;
        if (currentDimensi <= 0 && originalQtyBox > 0) {
            currentDimensi = Math.max(0, Math.floor((originalQty - originalQtyPcs) / originalQtyBox));
        }

        $('#editOpnameAlert').addClass('d-none').text('');
        $('#edit_id').val(row.id || '');
        $('#edit_kode_barang').val(row.kode_barang || pageKodeBarang);
        $('#edit_nama_barang').val(row.nama_barang || '-');
        $('#edit_nama_barang_text').text(row.nama_barang || '-');
        $('#edit_tim_opname').val(row.tim_opname || '-');
        $('#edit_expired_date').val(row.expired_date || '');
        $('#edit_no_lot').val(row.no_lot || '-');
        $('#edit_wilayah').val(row.wilayah || 0);
        $('#edit_qty_box').val(originalQtyBox);
        $('#edit_qty_pcs').val(originalQtyPcs);
        $('#edit_qty').val(originalQty);
        $('#modalEditOpname').modal('show');
    });

    $('#edit_qty_box, #edit_qty_pcs').on('input', refreshQtyTotal);

    $('#formEditOpname').on('submit', function (event) {
        event.preventDefault();
        var button = $('#btnSaveEditOpname');
        var alertBox = $('#editOpnameAlert');

        button.prop('disabled', true);
        alertBox.addClass('d-none').text('');

        $.ajax({
            url: updateUrl,
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function (res) {
                if (!res || !res.status) {
                    alertBox.removeClass('d-none').text((res && res.message) || 'Gagal menyimpan perubahan.');
                    return;
                }

                window.location.reload();
            },
            error: function () {
                alertBox.removeClass('d-none').text('Terjadi gangguan saat menyimpan perubahan.');
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });
});
</script>
