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
    $request_logs = $request_logs ?? [];
    $filters = $filters ?? ['tim' => 0, 'wilayah' => '', 'input_by' => ''];
    $request_tim_options = $request_tim_options ?? [1, 2];
    $request_wilayah_options = $request_wilayah_options ?? [];
    $request_inputer_options = $request_inputer_options ?? [];
    ?>

    <div class="content-wrapper so-request-page">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-7">
                        <h1 class="m-0">Request Opname User</h1>
                    </div>
                    <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('admin/stockopname/monitoring') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Monitoring
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <style>
                    .so-request-page{background:#f5f7fb}
                    .sr-panel{background:#fff;border:1px solid #e1e7ef;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06)}
                    .sr-panel-header{padding:14px 16px;border-bottom:1px solid #e8edf3;display:flex;align-items:center;justify-content:space-between;gap:12px}
                    .sr-title{font-weight:800;color:#1f2937;margin:0;font-size:16px}
                    .sr-muted{color:#64748b;font-size:12px}
                    .sr-code{font-family:monospace;font-size:12px;background:#f8fafc;border:1px solid #dbe5ef;border-radius:6px;padding:4px 7px;color:#334155;white-space:nowrap}
                    .sr-badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 9px;font-size:12px;font-weight:800;background:#fffbeb;color:#92400e}
                    .sr-filter-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
                    .sr-filter-card{padding:16px}
                    .sr-label{display:block;font-size:12px;font-weight:700;color:#475569;margin-bottom:6px}
                    .sr-select{height:38px;border:1px solid #dbe5ef;border-radius:8px}
                    .sr-bulk-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:12px 16px;border-top:1px solid #e8edf3;background:#f8fafc}
                    .sr-check{width:16px;height:16px}
                    .table td,.table th{vertical-align:middle}.btn i{margin-right:5px}
                    @media(max-width:992px){.sr-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
                    @media(max-width:768px){.content-header h1{font-size:22px}.sr-panel-header{align-items:flex-start;flex-direction:column}.sr-filter-grid{grid-template-columns:1fr}}
                </style>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="sr-panel">
                            <div class="sr-panel-header">
                                <div>
                                    <h2 class="sr-title">Daftar Request Opname</h2>
                                    <div class="sr-muted"><?= number_format(count($request_logs), 0, ',', '.') ?> data request ditampilkan</div>
                                </div>
                                <span class="sr-badge">Request Master Item</span>
                            </div>
                            <div class="sr-filter-card border-bottom">
                                <form method="get">
                                    <div class="sr-filter-grid">
                                        <div>
                                            <label class="sr-label">Filter Tim</label>
                                            <select name="tim" class="form-control sr-select">
                                                <option value="">Semua Tim</option>
                                                <?php foreach ($request_tim_options as $timOption) : ?>
                                                    <option value="<?= (int)$timOption ?>" <?= (int)($filters['tim'] ?? 0) === (int)$timOption ? 'selected' : '' ?>>Tim <?= (int)$timOption ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="sr-label">Filter Wilayah</label>
                                            <select name="wilayah" class="form-control sr-select">
                                                <option value="">Semua Wilayah</option>
                                                <?php foreach ($request_wilayah_options as $wilayahOption) : ?>
                                                    <option value="<?= $so_e($wilayahOption) ?>" <?= (string)($filters['wilayah'] ?? '') === (string)$wilayahOption ? 'selected' : '' ?>><?= $so_e($wilayahOption) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="sr-label">Filter Inputer</label>
                                            <select name="input_by" class="form-control sr-select">
                                                <option value="">Semua Inputer</option>
                                                <?php foreach ($request_inputer_options as $inputerOption) : ?>
                                                    <option value="<?= $so_e($inputerOption) ?>" <?= (string)($filters['input_by'] ?? '') === (string)$inputerOption ? 'selected' : '' ?>><?= $so_e($inputerOption) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-end">
                                            <div class="w-100 d-flex" style="gap:8px;">
                                                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-filter"></i> Terapkan</button>
                                                <a href="<?= base_url('admin/stockopname/monitoring/request-opname') ?>" class="btn btn-outline-secondary btn-sm flex-fill"><i class="fas fa-undo"></i> Reset</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="table-responsive p-3">
                                <form id="requestAffirmForm">
                                <table class="table table-sm table-bordered table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:40px"><input type="checkbox" id="checkAllRequest" class="sr-check"></th>
                                            <th>Waktu</th>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Exp Date Request</th>
                                            <th class="text-right">Pcs</th>
                                            <th class="text-right">Box</th>
                                            <th class="text-right">Qty</th>
                                            <th class="text-right">Dimensi</th>
                                            <th>Wilayah</th>
                                            <th>Tim</th>
                                            <th>Requested By</th>
                                            <th>Afirmasi</th>
                                            <th>Reviewed By</th>
                                            <th>Reviewed At</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($request_logs)) : ?>
                                            <tr>
                                                <td colspan="16" class="text-center text-muted py-4">Belum ada request opname.</td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php foreach ($request_logs as $row) : ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="request_ids[]" value="<?= (int)($row['id'] ?? 0) ?>" class="sr-check js-request-check">
                                                </td>
                                                <td><?= $so_e($row['requested_at'] ?? $row['created_at'] ?? '-') ?></td>
                                                <td><span class="sr-code"><?= $so_e($row['kode_barang'] ?? '-') ?></span></td>
                                                <td><?= $so_e($row['nama_barang'] ?? '-') ?></td>
                                                <td><?= $so_e($row['expired_date'] ?? '-') ?></td>
                                                <td class="text-right"><?= number_format((int)($row['qty_pcs'] ?? 0), 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((int)($row['qty_box'] ?? 0), 0, ',', '.') ?></td>
                                                <td class="text-right font-weight-bold"><?= number_format((int)($row['qty'] ?? 0), 0, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((int)($row['dimensi'] ?? 0), 0, ',', '.') ?></td>
                                                <td><span class="sr-badge"><?= $so_e($row['wilayah'] ?? '-') ?></span></td>
                                                <td><?= $so_e($row['tim_opname'] ?? '-') ?></td>
                                                <td><?= $so_e($row['requested_by'] ?? '-') ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-success btn-sm js-affirm-single" data-id="<?= (int)($row['id'] ?? 0) ?>">
                                                        <i class="fas fa-check-circle"></i> Afirmasi
                                                    </button>
                                                </td>
                                                <td><?= $so_e($row['reviewed_by'] ?? '-') ?></td>
                                                <td><?= $so_e($row['reviewed_at'] ?? '-') ?></td>
                                                <td><?= $so_e($row['review_note'] ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </form>
                            </div>
                            <div class="sr-bulk-bar">
                                <div class="sr-muted"><span id="requestSelectedCount">0</span> data dipilih untuk afirmasi request opname</div>
                                <button type="button" class="btn btn-success btn-sm" id="btnBulkAffirmRequest"><i class="fas fa-check-circle"></i> Bulk Afirmasi Request</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
    (function ($) {
        function updateSelectedCount() {
            $('#requestSelectedCount').text($('.js-request-check:checked').length);
        }

        function postAffirmRequest(payload, button) {
            $.post('<?= base_url('admin/stockopname/ajax-affirm-request-opname-bulk') ?>', payload, null, 'json')
                .done(function (res) {
                    if (!res || !res.status) {
                        alert((res && res.message) || 'Afirmasi request gagal.');
                        return;
                    }
                    alert(res.message || 'Afirmasi request berhasil.');
                    window.location.reload();
                })
                .fail(function () {
                    alert('Terjadi gangguan saat memproses afirmasi request.');
                })
                .always(function () {
                    if (button) {
                        button.prop('disabled', false);
                    }
                });
        }

        $(document).on('change', '#checkAllRequest', function () {
            $('.js-request-check').prop('checked', $(this).is(':checked'));
            updateSelectedCount();
        });

        $(document).on('change', '.js-request-check', function () {
            var total = $('.js-request-check').length;
            var checked = $('.js-request-check:checked').length;
            $('#checkAllRequest').prop('checked', total > 0 && total === checked);
            updateSelectedCount();
        });

        $(document).on('click', '.js-affirm-single', function () {
            var button = $(this);
            var requestId = button.data('id') || 0;
            if (!requestId) {
                alert('Data request tidak valid.');
                return;
            }
            if (!window.confirm('Afirmasi request ini ke hasil opname?')) {
                return;
            }
            button.prop('disabled', true);
            postAffirmRequest({request_ids: [requestId]}, button);
        });

        $(document).on('click', '#btnBulkAffirmRequest', function () {
            var checked = $('.js-request-check:checked');
            var button = $(this);
            if (!checked.length) {
                alert('Pilih minimal satu data request opname.');
                return;
            }
            if (!window.confirm('Afirmasi semua data request opname yang dipilih ke hasil opname?')) {
                return;
            }
            button.prop('disabled', true);
            postAffirmRequest($('#requestAffirmForm').serialize(), button);
        });
    })(jQuery);
</script>
