<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Karisma Logo" height="150" width="300">
    </div>
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <?php
    $e = function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
    $input_rows = $input_rows ?? [];
    $master_items = $master_items ?? [];
    $recycle_rows = $recycle_rows ?? [];
    $request_rows = $request_rows ?? [];
    $edit_logs = $edit_logs ?? [];
    $compare = $compare ?? [];
    $nama_barang = $compare['nama_barang'] ?? ($master_items[0]['nama_barang'] ?? ($input_rows[0]['nama_barang'] ?? '-'));
    $qty_buku = (int)($compare['qty_buku'] ?? array_sum(array_column($master_items, 'qty')));
    $qty_tim_1 = (int)($compare['qty_tim_1'] ?? 0);
    $qty_tim_2 = (int)($compare['qty_tim_2'] ?? 0);
    $status = $compare['status_opname'] ?? 're_check';
    $status_label = ['all_match' => 'All Match', 'tim_1' => 'Tim 1 Match', 'tim_2' => 'Tim 2 Match', 're_check' => 'Re-Check'][$status] ?? 'Re-Check';
    $lot_key = function ($expired, $lot) { return trim((string)$expired) . '||' . trim((string)$lot); };
    $stock_by_lot = [];
    foreach ($master_items as $row) {
        $stock_by_lot[$lot_key($row['expired_date'] ?? '', $row['no_lot'] ?? '')] = (int)($row['qty_buku'] ?? $row['qty'] ?? 0);
    }
    $source_label = function ($value) {
        $map = ['manual' => 'Manual Request', 'request' => 'Manual Request', 'adjustment' => 'Adjustment', 'repost' => 'Repost', 'system' => 'System'];
        $parts = array_filter(array_map('trim', explode(',', strtolower((string)$value))));
        return implode(', ', array_map(function ($part) use ($map) { return $map[$part] ?? ucwords(str_replace('_', ' ', $part)); }, $parts)) ?: '-';
    };
    ?>

    <div class="content-wrapper so-detail-page">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-8">
                        <h1 class="m-0">Detail Input Opname</h1>
                        <div class="so-muted mt-1"><?= $e($kode_barang ?? '-') ?> - <?= $e($nama_barang) ?></div>
                    </div>
                    <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('admin/stockopname/monitoring') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Monitoring</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid pb-4">
                <style>
                    .so-detail-page{background:#f5f7fb}.so-muted{color:#64748b;font-size:12px}.so-panel{background:#fff;border:1px solid #e1e7ef;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06);overflow:hidden}.so-panel-header{padding:14px 16px;border-bottom:1px solid #e8edf3;display:flex;align-items:center;justify-content:space-between;gap:10px}.so-title{font-weight:800;color:#1f2937;margin:0;font-size:16px}.so-header-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.so-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px}.so-stat{background:#fff;border:1px solid #e1e7ef;border-radius:8px;padding:14px;box-shadow:0 8px 22px rgba(16,24,40,.05)}.so-stat-label{font-size:11px;text-transform:uppercase;color:#64748b;font-weight:800}.so-stat-value{font-size:24px;font-weight:850;color:#111827;line-height:1.1;margin-top:7px}.so-code{font-family:monospace;font-size:12px;background:#f8fafc;border:1px solid #dbe5ef;border-radius:6px;padding:4px 7px}.so-badge{display:inline-flex;border-radius:999px;padding:4px 9px;font-size:12px;font-weight:800;background:#fee2e2;color:#991b1b}.so-badge.all_match{background:#dcfce7;color:#166534}.so-badge.tim_1{background:#dbeafe;color:#1d4ed8}.so-badge.tim_2{background:#ede9fe;color:#6d28d9}.so-layout{display:grid;grid-template-columns:minmax(360px,40%) minmax(0,60%);gap:14px}.so-stack{display:grid;gap:14px;align-content:start}.so-bottom{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px}.so-empty{color:#64748b;text-align:center;padding:28px 12px}.so-table{font-size:12px;white-space:nowrap;margin:0}.so-table th{background:#f8fafc;text-align:center}.so-table td,.so-table th{vertical-align:middle;padding:.42rem}.so-cell-main{font-weight:800;color:#1f2937}.so-cell-sub{font-size:11px;color:#64748b;margin-top:2px}.so-action-btn{width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center}.so-action-btn i{margin:0}.so-input-row{cursor:pointer}.so-input-row.is-selected{background:#eff6ff;box-shadow:inset 3px 0 #2563eb}.so-log-list{display:grid;gap:8px;max-height:360px;overflow:auto}.so-log-item{border:1px solid #e1e7ef;border-radius:8px;background:#f8fafc;padding:10px}.so-log-title{font-size:13px;font-weight:800}.so-log-meta{font-size:11px;color:#64748b;margin-top:3px}.so-edit-modal{border:0;border-radius:8px;overflow:hidden}.so-edit-modal .modal-header{background:#1f2937;color:#fff}.so-edit-modal .close{color:#fff;text-shadow:none}.so-field-card{background:#fff;border:1px solid #e1e7ef;border-radius:8px;padding:12px;height:100%}.so-field-card label{font-size:11px;text-transform:uppercase;font-weight:850;color:#64748b}.so-field-card.is-editable{border-color:#93c5fd;background:#eff6ff}.so-field-card.is-editable .form-control{font-size:22px;font-weight:850}.so-modal-context{background:#f8fafc;border:1px solid #e1e7ef;border-radius:8px;padding:12px;margin-bottom:14px}.so-loading{opacity:.55;pointer-events:none}.so-toast{position:fixed;right:20px;top:70px;z-index:9999;min-width:280px;max-width:420px;padding:12px 16px;border-radius:8px;color:#fff;box-shadow:0 10px 30px rgba(0,0,0,.2);display:none}.so-toast.success{background:#15803d}.so-toast.error{background:#b91c1c}.so-lot-status{display:inline-flex;padding:3px 7px;border-radius:999px;font-size:10px;font-weight:850;background:#e2e8f0;color:#475569}.so-lot-status.done{background:#dcfce7;color:#166534}.so-lot-status.partial{background:#dbeafe;color:#1d4ed8}.so-lot-status.diff{background:#fee2e2;color:#991b1b}.so-team-tabs{display:flex;border-bottom:1px solid #e1e7ef;background:#f8fafc;padding:0 14px}.so-team-tab{border:0;background:transparent;padding:11px 18px;font-weight:850;color:#64748b;border-bottom:3px solid transparent}.so-team-tab.is-active{color:#2563eb;border-bottom-color:#2563eb;background:#fff}.so-filter-empty{display:none}
                    @media(max-width:992px){.so-summary{grid-template-columns:repeat(2,1fr)}.so-layout,.so-bottom{grid-template-columns:1fr}}@media(max-width:576px){.so-summary{grid-template-columns:1fr}.so-panel-header{align-items:flex-start;flex-direction:column}}
                </style>
                <div id="soToast" class="so-toast"></div>

                <div id="soDynamicContent">
                    <div class="so-summary">
                        <div class="so-stat"><div class="so-stat-label">Kode Barang</div><div class="so-stat-value"><span class="so-code"><?= $e($kode_barang) ?></span></div></div>
                        <div class="so-stat"><div class="so-stat-label">Stock Buku</div><div class="so-stat-value"><?= number_format($qty_buku, 0, ',', '.') ?></div></div>
                        <div class="so-stat"><div class="so-stat-label">Qty Tim 1</div><div class="so-stat-value"><?= number_format($qty_tim_1, 0, ',', '.') ?></div></div>
                        <div class="so-stat"><div class="so-stat-label">Qty Tim 2</div><div class="so-stat-value"><?= number_format($qty_tim_2, 0, ',', '.') ?></div></div>
                        <div class="so-stat"><div class="so-stat-label">Status</div><div class="so-stat-value"><span class="so-badge <?= $e($status) ?>"><?= $e($status_label) ?></span></div></div>
                    </div>

                    <div class="so-layout">
                        <div class="so-stack">
                            <div class="so-panel">
                                <div class="so-panel-header"><h2 class="so-title">Stock Buku Per Lot</h2><span class="so-muted"><?= count($master_items) ?> data</span></div>
                                <div class="table-responsive">
                                    <?php if (!$master_items) : ?><div class="so-empty">Data stock buku tidak ditemukan.</div><?php else : ?>
                                    <table class="table table-bordered table-hover so-table">
                                        <thead><tr><th>Expired Date</th><th>No Lot</th><th>Qty Buku</th><th>Tim 1</th><th>Tim 2</th><th>Status</th><th>#</th></tr></thead>
                                        <tbody><?php foreach ($master_items as $row) :
                                            $book_qty = (int)($row['qty_buku'] ?? $row['qty'] ?? 0);
                                            $team_1_qty = (int)($row['qty_tim_1'] ?? 0);
                                            $team_2_qty = (int)($row['qty_tim_2'] ?? 0);
                                            $team_1_ok = $team_1_qty === $book_qty;
                                            $team_2_ok = $team_2_qty === $book_qty;
                                            if ($team_1_ok && $team_2_ok) { $lot_status = 'Selesai'; $lot_status_class = 'done'; }
                                            elseif ($team_1_ok || $team_2_ok) { $lot_status = 'Sebagian'; $lot_status_class = 'partial'; }
                                            elseif ($team_1_qty > 0 || $team_2_qty > 0) { $lot_status = 'Selisih'; $lot_status_class = 'diff'; }
                                            else { $lot_status = 'Belum Input'; $lot_status_class = ''; }
                                            $filter_key = $lot_key($row['expired_date'] ?? '', $row['no_lot'] ?? '');
                                        ?>
                                            <tr><td><?= $e($row['expired_date'] ?? '-') ?></td><td><?= $e($row['no_lot'] ?? '-') ?></td><td class="text-right"><?= number_format($book_qty, 0, ',', '.') ?></td><td class="text-right"><?= number_format($team_1_qty, 0, ',', '.') ?></td><td class="text-right"><?= number_format($team_2_qty, 0, ',', '.') ?></td><td class="text-center"><span class="so-lot-status <?= $lot_status_class ?>"><?= $lot_status ?></span></td><td class="text-center"><input type="checkbox" class="js-lot-filter" data-key="<?= $e($filter_key) ?>" title="Tampilkan hasil input lot ini"></td></tr>
                                        <?php endforeach ?></tbody>
                                    </table><?php endif ?>
                                </div>
                            </div>

                            <div class="so-panel">
                                <div class="so-panel-header"><h2 class="so-title">Request Item</h2><span class="so-muted"><?= count($request_rows) ?> group</span></div>
                                <div class="table-responsive">
                                    <?php if (!$request_rows) : ?><div class="so-empty">Belum ada request item untuk barang ini.</div><?php else : ?>
                                    <table class="table table-bordered table-hover so-table js-request-table">
                                        <thead><tr><th>Expired Date</th><th>No Lot</th><th>Qty</th><th>Qty PCS</th><th>Qty Box</th><th>Input By</th><th>Input Source</th><th>#</th></tr></thead>
                                        <tbody><?php foreach ($request_rows as $index => $row) : ?>
                                            <?php $request_json = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>
                                            <tr class="js-request-row" data-index="<?= $index ?>"><td><?= $e($row['expired_date']) ?></td><td><?= $e($row['no_lot']) ?></td><td class="text-right"><?= number_format((int)$row['qty'], 0, ',', '.') ?></td><td class="text-right"><?= number_format((int)$row['qty_pcs'], 0, ',', '.') ?></td><td class="text-right"><?= number_format((int)$row['qty_box'], 0, ',', '.') ?></td><td><?= $e($row['input_by']) ?></td><td><?= $e($source_label($row['input_source'] ?? 'manual')) ?></td><td class="text-center"><button type="button" class="btn btn-outline-success btn-sm so-action-btn js-add-request" data-row="<?= $request_json ?>" title="Tambah ke hasil opname"><i class="fas fa-plus"></i></button></td></tr>
                                        <?php endforeach ?></tbody>
                                    </table>
                                    <div class="p-2 text-center js-request-pagination"></div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>

                        <div class="so-panel">
                            <div class="so-panel-header">
                                <div><h2 class="so-title">Data Hasil Input Opname</h2><div class="so-muted mt-1">Klik baris untuk memilih data adjustment.</div></div>
                                <div class="so-header-actions"><span class="so-muted"><?= count($input_rows) ?> input</span><button type="button" class="btn btn-warning btn-sm" id="btnAdjustment"><i class="fas fa-sliders-h"></i> Adjustment Opname</button></div>
                            </div>
                            <div class="so-team-tabs"><button type="button" class="so-team-tab is-active js-team-tab" data-team="1">Tim 1</button><button type="button" class="so-team-tab js-team-tab" data-team="2">Tim 2</button></div>
                            <div class="table-responsive">
                                <?php if (!$input_rows) : ?><div class="so-empty">Belum ada hasil input opname.</div><?php else : ?>
                                <table class="table table-bordered table-hover so-table">
                                    <thead><tr><th>Input</th><th>Expired / Lot</th><th>Qty Buku</th><th>Qty</th><th>Qty Box</th><th>Qty PCS</th><th>#</th></tr></thead>
                                    <tbody><?php foreach ($input_rows as $row) :
                                        $row_json = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                        $book = $stock_by_lot[$lot_key($row['expired_date'] ?? '', $row['no_lot'] ?? '')] ?? 0;
                                    ?>
                                        <tr class="so-input-row js-select-opname" data-row="<?= $row_json ?>" data-team="<?= (int)($row['tim_opname'] ?? 0) ?>" data-key="<?= $e($lot_key($row['expired_date'] ?? '', $row['no_lot'] ?? '')) ?>">
                                            <td><div class="so-cell-main"><?= $e($row['input_by'] ?? '-') ?></div><div class="so-cell-sub">Tim <?= $e($row['tim_opname'] ?? '-') ?> | <?= $e($row['created_at'] ?? $row['input_at'] ?? '-') ?></div></td>
                                            <td><div class="so-cell-main"><?= $e($row['expired_date'] ?? '-') ?></div><div class="so-cell-sub">Lot <?= $e($row['no_lot'] ?? '-') ?></div></td>
                                            <td class="text-right"><?= number_format($book, 0, ',', '.') ?></td><td class="text-right font-weight-bold"><?= number_format((int)$row['qty'], 0, ',', '.') ?></td><td class="text-right"><?= number_format((int)$row['qty_box'], 0, ',', '.') ?></td><td class="text-right"><?= number_format((int)$row['qty_pcs'], 0, ',', '.') ?></td>
                                            <td class="text-center"><div class="d-flex justify-content-center" style="gap:5px"><button type="button" class="btn btn-outline-primary btn-sm so-action-btn js-edit-opname" title="Edit Qty"><i class="fas fa-edit"></i></button><button type="button" class="btn btn-outline-danger btn-sm so-action-btn js-delete-opname" title="Delete"><i class="fas fa-trash"></i></button></div></td>
                                        </tr>
                                    <?php endforeach ?></tbody>
                                </table><div class="so-empty so-filter-empty" id="inputFilterEmpty">Checklist lot pada Stock Buku Per Lot untuk menampilkan data Tim 1.</div><?php endif ?>
                            </div>
                        </div>
                    </div>

                    <div class="so-bottom">
                        <div class="so-panel">
                            <div class="so-panel-header"><h2 class="so-title">Recycle Bin Input Opname</h2><span class="so-muted"><?= count($recycle_rows) ?> data</span></div>
                            <div class="table-responsive">
                                <?php if (!$recycle_rows) : ?><div class="so-empty">Recycle bin kosong.</div><?php else : ?>
                                <table class="table table-bordered table-hover so-table">
                                    <thead><tr><th>Input</th><th>Expired/Lot</th><th>Qty</th><th>Qty Box</th><th>Qty PCS</th><th>#</th></tr></thead>
                                    <tbody><?php foreach ($recycle_rows as $row) : ?>
                                        <tr><td><div class="so-cell-main"><?= $e($row['input_by']) ?></div><div class="so-cell-sub">Dihapus <?= $e($row['deleted_by']) ?> | <?= $e($row['deleted_at']) ?></div></td><td><div class="so-cell-main"><?= $e($row['expired_date']) ?></div><div class="so-cell-sub">Lot <?= $e($row['no_lot']) ?></div></td><td class="text-right"><?= number_format((int)$row['qty'], 0, ',', '.') ?></td><td class="text-right"><?= number_format((int)$row['qty_box'], 0, ',', '.') ?></td><td class="text-right"><?= number_format((int)$row['qty_pcs'], 0, ',', '.') ?></td><td class="text-center"><button type="button" class="btn btn-outline-success btn-sm so-action-btn js-repost-opname" data-id="<?= (int)$row['id'] ?>" title="Repost"><i class="fas fa-undo"></i></button></td></tr>
                                    <?php endforeach ?></tbody>
                                </table><?php endif ?>
                            </div>
                        </div>

                        <div class="so-panel">
                            <div class="so-panel-header"><h2 class="so-title">Log Perubahan Input Opname</h2><span class="so-muted"><?= count($edit_logs) ?> aktivitas terakhir</span></div>
                            <div class="p-3">
                                <?php if (!$edit_logs) : ?><div class="so-empty">Belum ada perubahan data input opname.</div><?php else : ?>
                                <div class="so-log-list"><?php foreach ($edit_logs as $log) :
                                    $action = $log['action_type'] ?? $log['action'] ?? 'EDIT_QTY';
                                    $fields = json_decode((string)($log['changed_fields'] ?? '[]'), true);
                                    $actor = $log['created_by'] ?? $log['changed_by'] ?? '-';
                                    $time = $log['created_at'] ?? $log['changed_at'] ?? '-';
                                ?>
                                    <div class="so-log-item"><div class="so-log-title"><?= $e($action) ?> #<?= $e($log['opname_id'] ?? '-') ?> oleh <?= $e($actor) ?></div><div class="so-log-meta"><?= $e($log['description'] ?? ('Field: ' . (is_array($fields) ? implode(', ', $fields) : '-'))) ?> | <?= $e($time) ?> | IP <?= $e($log['ip_address'] ?? '-') ?></div></div>
                                <?php endforeach ?></div><?php endif ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="modalEditOpname" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content so-edit-modal" id="formEditOpname">
                <div class="modal-header"><h5 class="modal-title" id="editModalTitle">Edit Qty Input Opname</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="editOpnameAlert"></div>
                    <input type="hidden" name="id" id="edit_id"><input type="hidden" name="kode_barang" value="<?= $e($kode_barang) ?>"><input type="hidden" name="action_type" id="edit_action_type" value="EDIT_QTY">
                    <div class="so-modal-context"><strong id="edit_nama_barang">-</strong><div class="so-muted"><span id="edit_expired_text">-</span> | Lot <span id="edit_lot_text">-</span> | Dimensi <span id="edit_dimensi_text">0</span></div></div>
                    <div class="row">
                        <div class="col-md-4"><div class="so-field-card mb-3"><label>Qty Total</label><input type="number" class="form-control" id="edit_qty" readonly></div></div>
                        <div class="col-md-4"><div class="so-field-card is-editable mb-3"><label>Qty Box</label><input type="number" class="form-control" name="qty_box" id="edit_qty_box" min="0" step="1" required></div></div>
                        <div class="col-md-4"><div class="so-field-card is-editable mb-3"><label>Qty PCS</label><input type="number" class="form-control" name="qty_pcs" id="edit_qty_pcs" min="0" step="1" required></div></div>
                    </div>
                    <div class="so-muted">Qty Total dihitung otomatis: Qty Box x Dimensi + Qty PCS.</div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="btnSaveEditOpname"><i class="fas fa-save"></i> Simpan</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalAddRequest" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content so-edit-modal" id="formAddRequest">
                <div class="modal-header"><h5 class="modal-title">Input Request Item</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="addRequestAlert"></div>
                    <input type="hidden" name="kode_barang" id="request_kode_barang">
                    <input type="hidden" name="expired_date" id="request_expired_date">
                    <input type="hidden" name="no_lot" id="request_no_lot">
                    <div class="so-modal-context"><strong id="request_nama_barang">-</strong><div class="so-muted"><span id="request_expired_text">-</span> | Lot <span id="request_lot_text">-</span> | Dimensi <span id="request_dimensi_text">0</span></div></div>
                    <div class="row">
                        <div class="col-md-3"><div class="so-field-card mb-3"><label>Tim Opname</label><select class="form-control" name="tim_opname" id="request_tim_opname" required><option value="1">Tim 1</option><option value="2">Tim 2</option></select></div></div>
                        <div class="col-md-3"><div class="so-field-card mb-3"><label>Qty Total</label><input type="number" class="form-control" id="request_qty" readonly></div></div>
                        <div class="col-md-3"><div class="so-field-card is-editable mb-3"><label>Qty Box</label><input type="number" class="form-control" name="qty_box" id="request_qty_box" min="0" step="1" required></div></div>
                        <div class="col-md-3"><div class="so-field-card is-editable mb-3"><label>Qty PCS</label><input type="number" class="form-control" name="qty_pcs" id="request_qty_pcs" min="0" step="1" required></div></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-success" id="btnSaveRequest"><i class="fas fa-plus"></i> Tambah ke Opname</button></div>
            </form>
        </div>
    </div>

    <footer class="main-footer"><strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong> All rights reserved.</footer>
</div>

<script>
$(function () {
    var urls = {
        update: '<?= base_url('admin/stockopname/detail_input_opname/update') ?>',
        remove: '<?= base_url('admin/stockopname/detail_input_opname/delete') ?>',
        repost: '<?= base_url('admin/stockopname/detail_input_opname/repost') ?>'
        ,addRequest: '<?= base_url('admin/stockopname/detail_input_opname/add_request') ?>'
    };
    var pageKodeBarang = <?= json_encode((string)$kode_barang, JSON_UNESCAPED_UNICODE) ?>;
    var selectedRow = null;
    var currentDimensi = 0;
    var requestDimensi = 0;
    var activeTeam = 1;
    var selectedLotKeys = {};

    function toast(message, success) {
        $('#soToast').stop(true, true).removeClass('success error').addClass(success ? 'success' : 'error').text(message).fadeIn(150).delay(2600).fadeOut(250);
    }
    function rowData(element) {
        try { return JSON.parse($(element).closest('.js-select-opname').attr('data-row') || '{}'); } catch (e) { return {}; }
    }
    function refreshWidgets(message) {
        $('#soDynamicContent').addClass('so-loading');
        $.get(window.location.href, { ajax_refresh: 1 }).done(function (html) {
            var fresh = $('<div>').append($.parseHTML(html)).find('#soDynamicContent').html();
            if (!fresh) { toast('Data tersimpan, tetapi widget gagal dimuat ulang.', false); return; }
            $('#soDynamicContent').html(fresh);
            selectedRow = null;
            initRequestPagination();
            restoreOpnameFilters();
            toast(message, true);
        }).fail(function () { toast('Data tersimpan, tetapi refresh widget gagal.', false); }).always(function () { $('#soDynamicContent').removeClass('so-loading'); });
    }
    function applyOpnameFilters() {
        var checkedKeys = Object.keys(selectedLotKeys).filter(function (key) { return selectedLotKeys[key]; });
        var visible = 0;
        $('.js-select-opname').each(function () {
            var show = String($(this).attr('data-team')) === String(activeTeam) && checkedKeys.indexOf(String($(this).attr('data-key'))) !== -1;
            $(this).toggle(show);
            if (show) visible++;
        });
        $('#inputFilterEmpty').toggle(visible === 0).text(
            checkedKeys.length ? 'Belum ada data opname Tim ' + activeTeam + ' untuk lot yang dipilih.' : 'Checklist lot pada Stock Buku Per Lot untuk menampilkan data Tim ' + activeTeam + '.'
        );
    }
    function restoreOpnameFilters() {
        $('.js-lot-filter').each(function () {
            $(this).prop('checked', !!selectedLotKeys[String($(this).attr('data-key'))]);
        });
        $('.js-team-tab').removeClass('is-active').filter('[data-team="' + activeTeam + '"]').addClass('is-active');
        applyOpnameFilters();
    }
    function openEditor(row, actionType) {
        var box = parseInt(row.qty_box || 0, 10) || 0, pcs = parseInt(row.qty_pcs || 0, 10) || 0, qty = parseInt(row.qty || 0, 10) || 0;
        currentDimensi = parseInt(row.dimensi || 0, 10) || 0;
        if (currentDimensi <= 0 && box > 0) currentDimensi = Math.max(0, Math.floor((qty - pcs) / box));
        $('#edit_id').val(row.id || '');
        $('#edit_action_type').val(actionType);
        $('#editModalTitle').text(actionType === 'ADJUSTMENT' ? 'Adjustment Opname' : 'Edit Qty Input Opname');
        $('#edit_nama_barang').text(row.nama_barang || '-');
        $('#edit_expired_text').text(row.expired_date || '-'); $('#edit_lot_text').text(row.no_lot || '-'); $('#edit_dimensi_text').text(currentDimensi);
        $('#edit_qty_box').val(box); $('#edit_qty_pcs').val(pcs); $('#edit_qty').val(qty);
        $('#editOpnameAlert').addClass('d-none').text('');
        $('#modalEditOpname').modal('show');
    }
    function initRequestPagination() {
        var rows = $('.js-request-row'), pageSize = 10, pages = Math.ceil(rows.length / pageSize);
        function showPage(page) {
            rows.hide().slice((page - 1) * pageSize, page * pageSize).show();
            $('.js-request-page').removeClass('btn-primary').addClass('btn-outline-secondary').filter('[data-page="' + page + '"]').removeClass('btn-outline-secondary').addClass('btn-primary');
        }
        var holder = $('.js-request-pagination').empty();
        if (pages > 1) for (var i = 1; i <= pages; i++) holder.append('<button type="button" class="btn btn-sm btn-outline-secondary js-request-page mr-1" data-page="' + i + '">' + i + '</button>');
        showPage(1);
    }
    initRequestPagination();
    applyOpnameFilters();

    $(document).on('change', '.js-lot-filter', function () {
        selectedLotKeys[String($(this).attr('data-key'))] = $(this).is(':checked');
        applyOpnameFilters();
    });
    $(document).on('click', '.js-team-tab', function () {
        activeTeam = parseInt($(this).attr('data-team'), 10) || 1;
        $('.js-team-tab').removeClass('is-active'); $(this).addClass('is-active');
        selectedRow = null; $('.js-select-opname').removeClass('is-selected');
        applyOpnameFilters();
    });

    $(document).on('click', '.js-select-opname', function (event) {
        if ($(event.target).closest('button').length) return;
        $('.js-select-opname').removeClass('is-selected'); $(this).addClass('is-selected'); selectedRow = rowData(this);
    });
    $(document).on('click', '.js-edit-opname', function () { openEditor(rowData(this), 'EDIT_QTY'); });
    $(document).on('click', '#btnAdjustment', function () {
        if (!selectedRow) { toast('Pilih satu baris input opname terlebih dahulu.', false); return; }
        openEditor(selectedRow, 'ADJUSTMENT');
    });
    $(document).on('input', '#edit_qty_box, #edit_qty_pcs', function () {
        var box = parseInt($('#edit_qty_box').val() || 0, 10) || 0, pcs = parseInt($('#edit_qty_pcs').val() || 0, 10) || 0;
        $('#edit_qty').val((box * currentDimensi) + pcs);
    });
    $(document).on('submit', '#formEditOpname', function (event) {
        event.preventDefault();
        var button = $('#btnSaveEditOpname').prop('disabled', true), alertBox = $('#editOpnameAlert').addClass('d-none');
        $.post(urls.update, $(this).serialize(), null, 'json').done(function (res) {
            if (!res || !res.status) { alertBox.removeClass('d-none').text((res && res.message) || 'Gagal menyimpan perubahan.'); return; }
            $('#modalEditOpname').modal('hide'); refreshWidgets(res.message || 'Perubahan berhasil disimpan.');
        }).fail(function () { alertBox.removeClass('d-none').text('Terjadi gangguan saat menyimpan perubahan.'); }).always(function () { button.prop('disabled', false); });
    });
    $(document).on('click', '.js-delete-opname', function () {
        var row = rowData(this);
        if (!window.confirm('Pindahkan input opname ini ke recycle bin?')) return;
        var reason = window.prompt('Alasan penghapusan (opsional):', '') || '';
        $.post(urls.remove, {id: row.id, kode_barang: pageKodeBarang, delete_reason: reason}, null, 'json').done(function (res) {
            if (!res || !res.status) { toast((res && res.message) || 'Delete gagal.', false); return; }
            refreshWidgets(res.message);
        }).fail(function () { toast('Terjadi gangguan saat delete input opname.', false); });
    });
    $(document).on('click', '.js-repost-opname', function () {
        var id = $(this).attr('data-id');
        if (!window.confirm('Kembalikan data ini ke hasil input opname?')) return;
        $.post(urls.repost, {id: id, kode_barang: pageKodeBarang}, null, 'json').done(function (res) {
            if (!res || !res.status) { toast((res && res.message) || 'Repost gagal.', false); return; }
            refreshWidgets(res.message);
        }).fail(function () { toast('Terjadi gangguan saat repost input opname.', false); });
    });
    $(document).on('click', '.js-add-request', function () {
        var row = {};
        try { row = JSON.parse($(this).attr('data-row') || '{}'); } catch (e) { row = {}; }
        var box = parseInt(row.qty_box || 0, 10) || 0, pcs = parseInt(row.qty_pcs || 0, 10) || 0, qty = parseInt(row.qty || 0, 10) || 0;
        requestDimensi = box > 0 ? Math.max(0, Math.floor((qty - pcs) / box)) : 0;
        $('#request_kode_barang').val(row.kode_barang || pageKodeBarang);
        $('#request_expired_date').val(row.expired_date || '');
        $('#request_no_lot').val(row.no_lot || '-');
        $('#request_nama_barang').text(row.nama_barang || pageKodeBarang);
        $('#request_expired_text').text(row.expired_date || '-'); $('#request_lot_text').text(row.no_lot || '-'); $('#request_dimensi_text').text(requestDimensi);
        $('#request_tim_opname').val(String(activeTeam)); $('#request_qty_box').val(box); $('#request_qty_pcs').val(pcs); $('#request_qty').val(qty);
        $('#addRequestAlert').addClass('d-none').text('');
        $('#modalAddRequest').modal('show');
    });
    $(document).on('input', '#request_qty_box, #request_qty_pcs', function () {
        var box = parseInt($('#request_qty_box').val() || 0, 10) || 0, pcs = parseInt($('#request_qty_pcs').val() || 0, 10) || 0;
        $('#request_qty').val((box * requestDimensi) + pcs);
    });
    $(document).on('submit', '#formAddRequest', function (event) {
        event.preventDefault();
        var button = $('#btnSaveRequest').prop('disabled', true), alertBox = $('#addRequestAlert').addClass('d-none');
        var filterKey = $('#request_expired_date').val() + '||' + $('#request_no_lot').val();
        activeTeam = parseInt($('#request_tim_opname').val(), 10) || 1;
        $.post(urls.addRequest, $(this).serialize(), null, 'json').done(function (res) {
            if (!res || !res.status) { alertBox.removeClass('d-none').text((res && res.message) || 'Gagal menambahkan request item.'); return; }
            selectedLotKeys[filterKey] = true;
            $('#modalAddRequest').modal('hide'); refreshWidgets(res.message);
        }).fail(function () { alertBox.removeClass('d-none').text('Terjadi gangguan saat menyimpan request item.'); }).always(function () { button.prop('disabled', false); });
    });
    $(document).on('click', '.js-request-page', function () {
        var page = parseInt($(this).attr('data-page'), 10) || 1, rows = $('.js-request-row'), size = 10;
        rows.hide().slice((page - 1) * size, page * size).show();
        $('.js-request-page').removeClass('btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
    });
});
</script>
