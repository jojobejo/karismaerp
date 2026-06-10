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
                        <a href="<?= base_url('admin/stockopname/input') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-mobile-alt"></i> Input Opname
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
                    .so-match-badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 8px;font-size:11px;font-weight:850;background:#fee2e2;color:#991b1b;white-space:nowrap}.so-match-badge.match{background:#dcfce7;color:#166534}.so-diff-plus{color:#166534}.so-diff-minus{color:#991b1b}
                    .table td,.table th{vertical-align:middle}.btn i{margin-right:5px}.so-empty{color:#64748b;text-align:center;padding:28px 12px}.so-action-btn{width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center}.so-action-btn i{margin-right:0}
                    .so-tabs{border-bottom:1px solid #e1e7ef}.so-tabs .nav-link{font-weight:800;color:#475569;border-radius:0;border:0;border-bottom:3px solid transparent}.so-tabs .nav-link.active{color:#2563eb;border-bottom-color:#2563eb;background:#f8fafc}
                    .so-log-list{display:grid;gap:10px}.so-log-item{border:1px solid #e1e7ef;border-radius:8px;background:#f8fafc;padding:10px}.so-log-title{font-size:13px;font-weight:800;color:#1f2937}.so-log-meta{font-size:12px;color:#64748b;margin-top:3px}
                    .so-edit-modal{border:0;border-radius:8px;overflow:hidden;box-shadow:0 24px 70px rgba(15,23,42,.22)}.so-edit-modal .modal-header{background:#1f2937;color:#fff;border:0;padding:16px 18px}.so-edit-modal .modal-title{font-weight:850}.so-edit-modal .close{color:#fff;opacity:.85;text-shadow:none}
                    .so-edit-modal .modal-body{background:#f8fafc;padding:18px}.so-modal-context{background:#fff;border:1px solid #e1e7ef;border-radius:8px;padding:14px;margin-bottom:14px}.so-modal-context-title{font-size:13px;font-weight:850;color:#111827;line-height:1.4}.so-modal-context-code{margin-top:5px;font-family:monospace;font-size:12px;color:#475569}
                    .so-field-card{background:#fff;border:1px solid #e1e7ef;border-radius:8px;padding:12px;height:100%}.so-field-card label{font-size:11px;text-transform:uppercase;font-weight:850;color:#64748b;margin-bottom:6px}.so-field-card .form-control[readonly]{background:#f1f5f9;color:#475569;border-color:#dbe5ef}.so-field-card.is-editable{border-color:#93c5fd;background:#eff6ff}.so-field-card.is-editable .form-control{font-size:22px;font-weight:850;color:#111827;border-color:#93c5fd;background:#fff}.so-modal-help{font-size:12px;color:#64748b;margin-top:10px}
                    @media(max-width:992px){.so-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:576px){.so-summary{grid-template-columns:1fr}.content-header h1{font-size:22px}.so-panel-header{align-items:flex-start;flex-direction:column}}
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

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="so-panel">
                            <div class="so-panel-header">
                                <h2 class="so-title">Stock Buku Per Lot</h2>
                                <span class="so-muted"><?= count($master_items) ?> data</span>
                            </div>
                            <div class="table-responsive p-3">
                                <?php if (empty($master_items)) : ?>
                                    <div class="so-empty">Data master barang tidak ditemukan.</div>
                                <?php else : ?>
                                    <table class="table table-sm table-bordered table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Exp Date</th>
                                                <th>Lot</th>
                                                <th class="text-right">Qty</th>
                                                <th class="text-right">Qty Tim 1</th>
                                                <th class="text-right">Qty Tim 2</th>
                                                <th class="text-right">Selisih T1</th>
                                                <th class="text-right">Selisih T2</th>
                                                <th>Status Tim 1</th>
                                                <th>Status Tim 2</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($master_items as $row) : ?>
                                                <?php
                                                $qty_buku_lot = (int)($row['qty_buku'] ?? $row['qty'] ?? 0);
                                                $qty_tim_1_lot = (int)($row['qty_tim_1'] ?? 0);
                                                $qty_tim_2_lot = (int)($row['qty_tim_2'] ?? 0);
                                                $selisih_tim_1 = (int)($row['selisih_tim_1'] ?? ($qty_tim_1_lot - $qty_buku_lot));
                                                $selisih_tim_2 = (int)($row['selisih_tim_2'] ?? ($qty_tim_2_lot - $qty_buku_lot));
                                                $tim_1_match = (int)($row['tim_1_match'] ?? ($qty_tim_1_lot === $qty_buku_lot ? 1 : 0));
                                                $tim_2_match = (int)($row['tim_2_match'] ?? ($qty_tim_2_lot === $qty_buku_lot ? 1 : 0));
                                                $diff_class_1 = $selisih_tim_1 > 0 ? 'so-diff-plus' : ($selisih_tim_1 < 0 ? 'so-diff-minus' : '');
                                                $diff_class_2 = $selisih_tim_2 > 0 ? 'so-diff-plus' : ($selisih_tim_2 < 0 ? 'so-diff-minus' : '');
                                                ?>
                                                <tr>
                                                    <td><?= $so_e($row['expired_date'] ?? '-') ?></td>
                                                    <td><?= $so_e($row['no_lot'] ?? '-') ?></td>
                                                    <td class="text-right font-weight-bold"><?= number_format($qty_buku_lot, 0, ',', '.') ?></td>
                                                    <td class="text-right"><?= number_format($qty_tim_1_lot, 0, ',', '.') ?></td>
                                                    <td class="text-right"><?= number_format($qty_tim_2_lot, 0, ',', '.') ?></td>
                                                    <td class="text-right font-weight-bold <?= $diff_class_1 ?>"><?= number_format($selisih_tim_1, 0, ',', '.') ?></td>
                                                    <td class="text-right font-weight-bold <?= $diff_class_2 ?>"><?= number_format($selisih_tim_2, 0, ',', '.') ?></td>
                                                    <td><span class="so-match-badge <?= $tim_1_match ? 'match' : '' ?>"><?= $tim_1_match ? 'Match' : 'Tidak Match' ?></span></td>
                                                    <td><span class="so-match-badge <?= $tim_2_match ? 'match' : '' ?>"><?= $tim_2_match ? 'Match' : 'Tidak Match' ?></span></td>
                                                </tr>
                                            <?php endforeach ?>
                                        </tbody>
                                    </table>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="so-panel">
                            <div class="so-panel-header">
                                <h2 class="so-title">Data Hasil Input Opname</h2>
                                <span class="so-muted"><?= $total_input ?> input</span>
                            </div>
                            <ul class="nav nav-tabs so-tabs px-3 pt-2" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-tim-1" data-toggle="pill" href="#pane-tim-1" role="tab">
                                        Tim 1 <span class="badge badge-primary ml-1"><?= count($input_by_team[1]) ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-tim-2" data-toggle="pill" href="#pane-tim-2" role="tab">
                                        Tim 2 <span class="badge badge-warning ml-1"><?= count($input_by_team[2]) ?></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <?php foreach ([1, 2] as $team) : ?>
                                    <div class="tab-pane fade <?= $team === 1 ? 'show active' : '' ?>" id="pane-tim-<?= $team ?>" role="tabpanel">
                                        <div class="table-responsive p-3">
                                            <?php if (empty($input_by_team[$team])) : ?>
                                                <div class="so-empty">Belum ada hasil input opname untuk Tim <?= $team ?>.</div>
                                            <?php else : ?>
                                                <table class="table table-sm table-bordered table-hover mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Waktu</th>
                                                            <th>User</th>
                                                            <th>Exp Date</th>
                                                            <th>Lot</th>
                                                            <th class="text-right">Box</th>
                                                            <th class="text-right">Pcs</th>
                                                            <th class="text-right">Qty</th>
                                                            <th>Wilayah</th>
                                                            <th class="text-center">#</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($input_by_team[$team] as $row) : ?>
                                                            <?php $row_json = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>
                                                            <tr>
                                                                <td><?= $so_e($row['created_at'] ?? $row['input_at'] ?? '-') ?></td>
                                                                <td><?= $so_e($row['input_by'] ?? '-') ?></td>
                                                                <td><?= $so_e($row['expired_date'] ?? '-') ?></td>
                                                                <td><?= $so_e($row['no_lot'] ?? '-') ?></td>
                                                                <td class="text-right"><?= number_format((int)($row['qty_box'] ?? 0), 0, ',', '.') ?></td>
                                                                <td class="text-right"><?= number_format((int)($row['qty_pcs'] ?? 0), 0, ',', '.') ?></td>
                                                                <td class="text-right font-weight-bold"><?= number_format((int)($row['qty'] ?? 0), 0, ',', '.') ?></td>
                                                                <td><?= $so_e($row['wilayah'] ?? '-') ?></td>
                                                                <td class="text-center">
                                                                    <button type="button" class="btn btn-outline-primary btn-sm so-action-btn js-edit-opname" data-row="<?= $row_json ?>" title="Edit input opname">
                                                                        <i class="fas fa-edit"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach ?>
                                                    </tbody>
                                                </table>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                <?php endforeach ?>
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

    if (window.location.hash && $(window.location.hash).length) {
        $('.so-tabs a[href="' + window.location.hash + '"]').tab('show');
    }

    $('.so-tabs a[data-toggle="pill"]').on('shown.bs.tab', function (event) {
        window.location.hash = $(event.target).attr('href');
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
        var activePane = $('.so-tabs .nav-link.active').attr('href') || '';
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

                if (activePane) {
                    window.location.hash = activePane;
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
