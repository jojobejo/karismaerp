<?php
$candidateRows = isset($candidate_rows) && is_array($candidate_rows) ? $candidate_rows : [];
$requestRows = isset($request_rows) && is_array($request_rows) ? $request_rows : [];
$canCreate = !empty($can_create_revision_request);
$canAccounting = !empty($can_accounting_unpost_revision);
$canPurchasing = !empty($can_purchasing_unpost_lpb);

$formatDate = function ($dateStr) {
    $dateStr = trim((string) $dateStr);
    if ($dateStr === '' || $dateStr === '0000-00-00' || $dateStr === '0000-00-00 00:00:00') {
        return '-';
    }
    $timestamp = strtotime($dateStr);
    return $timestamp ? date('d/m/Y', $timestamp) : $dateStr;
};

$formatNumber = function ($value) {
    return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
};

$statusBadge = function ($status) {
    $status = strtoupper(trim((string) $status));
    $map = [
        'REQUESTED' => ['warning', 'Menunggu Accounting'],
        'ACCOUNTING_PROCESS' => ['info', 'Proses Accounting'],
        'READY_LPB_UNPOST' => ['success', 'Siap Unpost LPB'],
        'LPB_UNPOSTED' => ['primary', 'LPB Unpost'],
        'REVISION_DONE' => ['secondary', 'Revisi Selesai'],
        'CANCELLED' => ['dark', 'Dibatalkan'],
    ];
    $item = $map[$status] ?? ['light', $status ?: '-'];
    return '<span class="badge badge-' . $item[0] . ' px-2 py-1">' . htmlspecialchars($item[1], ENT_QUOTES, 'UTF-8') . '</span>';
};
?>
<style>
    .revision-kpi {
        border-radius: 8px;
        padding: 14px 16px;
        color: #fff;
        min-height: 86px;
    }

    .revision-kpi h4 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
    }

    .revision-kpi p {
        margin: 4px 0 0;
        font-size: 13px;
        opacity: .92;
    }

    .revision-kpi.is-red {
        background: linear-gradient(135deg, #c92a2a, #e8590c);
    }

    .revision-kpi.is-teal {
        background: linear-gradient(135deg, #087f5b, #0ca678);
    }

    .revision-kpi.is-indigo {
        background: linear-gradient(135deg, #364fc7, #1c7ed6);
    }

    .revision-action-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        justify-content: center;
    }

    .revision-action-row .btn {
        min-width: 34px;
    }

    .table-revision td,
    .table-revision th {
        vertical-align: middle;
        white-space: nowrap;
    }

    .detail-group-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 12px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0;
    }

    .detail-group-title + .table-responsive {
        border-left: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        border-radius: 0 0 8px 8px;
        margin-bottom: 14px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 align-items-center">
                        <div class="col-sm-8">
                            <h1 class="m-0">List Revisi Harga LPB</h1>
                        </div>
                        <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                            <a href="<?= base_url('ics/icspo') ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Data LPB
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <div class="revision-kpi is-red">
                                <h4><?= count($candidateRows) ?></h4>
                                <p>LPB kandidat revisi karena barang sudah terjual</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="revision-kpi is-indigo">
                                <h4><?= count($requestRows) ?></h4>
                                <p>Total request revisi tercatat</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="revision-kpi is-teal">
                                <h4><?= (int) array_reduce($requestRows, function ($carry, $row) {
                                    return $carry + (strtoupper((string) ($row['status'] ?? '')) === 'READY_LPB_UNPOST' ? 1 : 0);
                                }, 0) ?></h4>
                                <p>Notifikasi Purchasing: siap unpost LPB</p>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-search-dollar mr-2"></i>LPB Kandidat Revisi</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-revision" id="tableCandidateRevision">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th>Tgl LPB</th>
                                            <th>No LPB</th>
                                            <th>No PO</th>
                                            <th>Supplier</th>
                                            <th>Invoice LPB</th>
                                            <th>Faktur Terjual</th>
                                            <th>Item Terjual</th>
                                            <th>Qty Terjual</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($candidateRows)) : ?>
                                            <?php foreach ($candidateRows as $row) : ?>
                                                <tr>
                                                    <td data-order="<?= htmlspecialchars($row['input_at'] ?? '') ?>"><?= htmlspecialchars($formatDate($row['input_at'] ?? '')) ?></td>
                                                    <td class="font-weight-bold"><?= htmlspecialchars($row['nomor_lpb'] ?? ('LPB-' . (int) ($row['id_lpb'] ?? 0))) ?></td>
                                                    <td><?= htmlspecialchars($row['no_po'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($row['nama_supplier'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($row['no_invoice'] ?? '-') ?></td>
                                                    <td>
                                                        <span class="badge badge-danger px-2 py-1"><?= (int) ($row['total_faktur_terjual'] ?? 0) ?></span>
                                                        <small class="text-muted ml-1"><?= htmlspecialchars($row['sample_faktur'] ?? '') ?></small>
                                                    </td>
                                                    <td class="text-center"><?= (int) ($row['total_item_terjual'] ?? 0) ?></td>
                                                    <td class="text-right"><?= htmlspecialchars($formatNumber($row['total_qty_terjual'] ?? 0)) ?></td>
                                                    <td class="text-center">
                                                        <?php if ($canCreate) : ?>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger btn-create-request"
                                                                data-id-lpb="<?= (int) ($row['id_lpb'] ?? 0) ?>"
                                                                data-nomor-lpb="<?= htmlspecialchars($row['nomor_lpb'] ?? ('LPB-' . (int) ($row['id_lpb'] ?? 0)), ENT_QUOTES, 'UTF-8') ?>"
                                                                title="Buat request revisi">
                                                                <i class="fas fa-paper-plane"></i>
                                                            </button>
                                                        <?php else : ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">
                                                    <i class="fas fa-inbox mr-1"></i> Belum ada LPB kandidat revisi.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-clipboard-list mr-2"></i>Request Revisi Berjalan</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-revision" id="tableRequestRevision">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th>Tgl Request</th>
                                            <th>No Request</th>
                                            <th>No LPB</th>
                                            <th>No PO</th>
                                            <th>Supplier</th>
                                            <th>Faktur</th>
                                            <th>Progress Unpost</th>
                                            <th>Status</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($requestRows)) : ?>
                                            <?php foreach ($requestRows as $row) :
                                                $totalItem = (int) ($row['total_detail_record'] ?? $row['total_item'] ?? 0);
                                                $totalDone = (int) ($row['total_item_unposted'] ?? 0);
                                            ?>
                                                <tr>
                                                    <td data-order="<?= htmlspecialchars($row['requested_at'] ?? '') ?>"><?= htmlspecialchars($formatDate($row['requested_at'] ?? '')) ?></td>
                                                    <td class="font-weight-bold"><?= htmlspecialchars($row['no_request'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($row['nomor_lpb'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($row['no_po'] ?? '-') ?></td>
                                                    <td><?= htmlspecialchars($row['nama_supplier'] ?? '-') ?></td>
                                                    <td>
                                                        <span class="badge badge-info px-2 py-1"><?= (int) ($row['total_faktur'] ?? 0) ?></span>
                                                        <small class="text-muted ml-1"><?= htmlspecialchars($row['sample_faktur'] ?? '') ?></small>
                                                    </td>
                                                    <td class="text-center"><?= $totalDone ?> / <?= $totalItem ?></td>
                                                    <td class="text-center"><?= $statusBadge($row['status'] ?? '') ?></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-primary btn-detail-request" data-id-request="<?= (int) ($row['id_request'] ?? 0) ?>" title="Lihat detail">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">
                                                    <i class="fas fa-inbox mr-1"></i> Belum ada request revisi harga LPB.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="modal fade" id="modalCreateRevision" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form id="formCreateRevision">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Buat Request Revisi Harga LPB</h5>
                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_lpb" id="createIdLpb">
                            <div class="form-group">
                                <label>No LPB</label>
                                <input type="text" class="form-control" id="createNomorLpb" readonly>
                            </div>
                            <div class="form-group">
                                <label>Alasan Revisi</label>
                                <textarea class="form-control" name="alasan_revisi" rows="4" required placeholder="Contoh: harga invoice supplier berbeda dengan harga LPB yang sudah diposting."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger" id="btnSubmitCreateRevision">
                                <i class="fas fa-paper-plane mr-1"></i> Simpan Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalDetailRevision" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="detailRevisionTitle">Detail Request</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div id="detailRevisionSummary" class="mb-3"></div>
                        <div id="detailRevisionActions" class="mb-3"></div>
                        <div id="detailRevisionItems"></div>
                        <hr>
                        <h6 class="font-weight-bold">Log Aktivitas</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Aksi</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody id="detailRevisionLogs"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
        </footer>
        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <script>
        $(function() {
            var canAccounting = <?= $canAccounting ? 'true' : 'false' ?>;
            var canPurchasing = <?= $canPurchasing ? 'true' : 'false' ?>;

            function escapeHtml(value) {
                return $('<div/>').text(value === null || value === undefined ? '' : value).html();
            }

            function formatNumber(value) {
                var num = parseFloat(value || 0);
                return num.toLocaleString('id-ID', { maximumFractionDigits: 2 });
            }

            function statusBadge(status) {
                status = String(status || '').toUpperCase();
                var map = {
                    REQUESTED: ['warning', 'Menunggu Accounting'],
                    ACCOUNTING_PROCESS: ['info', 'Proses Accounting'],
                    READY_LPB_UNPOST: ['success', 'Siap Unpost LPB'],
                    LPB_UNPOSTED: ['primary', 'LPB Unpost'],
                    REVISION_DONE: ['secondary', 'Revisi Selesai'],
                    CANCELLED: ['dark', 'Dibatalkan'],
                    UNPOSTED: ['success', 'Unposted']
                };
                var item = map[status] || ['light', status || '-'];
                return '<span class="badge badge-' + item[0] + ' px-2 py-1">' + escapeHtml(item[1]) + '</span>';
            }

            function initDataTable(selector, emptyText) {
                if ($(selector + ' tbody tr').length === 1 && $(selector + ' tbody td[colspan]').length === 1) {
                    return;
                }

                $(selector).DataTable({
                    responsive: true,
                    autoWidth: false,
                    pageLength: 25,
                    order: [[0, 'desc']],
                    columnDefs: [{ orderable: false, targets: -1 }],
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                        zeroRecords: 'Tidak ada data ditemukan',
                        emptyTable: emptyText,
                        paginate: {
                            first: 'Pertama',
                            last: 'Terakhir',
                            next: 'Berikutnya',
                            previous: 'Sebelumnya'
                        }
                    }
                });
            }

            initDataTable('#tableCandidateRevision', 'Belum ada LPB kandidat revisi.');
            initDataTable('#tableRequestRevision', 'Belum ada request revisi.');

            $('.btn-create-request').on('click', function() {
                $('#createIdLpb').val($(this).data('id-lpb'));
                $('#createNomorLpb').val($(this).data('nomor-lpb'));
                $('#formCreateRevision textarea[name="alasan_revisi"]').val('');
                $('#modalCreateRevision').modal('show');
            });

            $('#formCreateRevision').on('submit', function(e) {
                e.preventDefault();
                var $btn = $('#btnSubmitCreateRevision');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan');

                $.post('<?= base_url('ics/lpb_revision/create') ?>', $(this).serialize(), function(res) {
                    alert(res.message || 'Request diproses.');
                    if (res.status) {
                        window.location.reload();
                    }
                }, 'json').fail(function() {
                    alert('Request gagal diproses.');
                }).always(function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Simpan Request');
                });
            });

            $('.btn-detail-request').on('click', function() {
                loadDetail($(this).data('id-request'));
            });

            function loadDetail(idRequest) {
                $('#detailRevisionTitle').text('Detail Request');
                $('#detailRevisionSummary').html('<div class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat detail...</div>');
                $('#detailRevisionActions, #detailRevisionItems, #detailRevisionLogs').empty();
                $('#modalDetailRevision').modal('show');

                $.get('<?= base_url('ics/lpb_revision/detail') ?>', { id_request: idRequest }, function(res) {
                    if (!res.status) {
                        $('#detailRevisionSummary').html('<div class="alert alert-danger">' + escapeHtml(res.message || 'Detail gagal dimuat.') + '</div>');
                        return;
                    }

                    renderDetail(res.data);
                }, 'json').fail(function() {
                    $('#detailRevisionSummary').html('<div class="alert alert-danger">Detail gagal dimuat.</div>');
                });
            }

            function renderDetail(data) {
                var request = data.request || {};
                var details = data.details || [];
                var logs = data.logs || [];
                $('#detailRevisionTitle').text((request.no_request || 'Detail Request') + ' - ' + (request.nomor_lpb || '-'));
                $('#detailRevisionSummary').html(
                    '<div class="row">' +
                        '<div class="col-md-3"><strong>Status</strong><br>' + statusBadge(request.status) + '</div>' +
                        '<div class="col-md-3"><strong>No LPB</strong><br>' + escapeHtml(request.nomor_lpb || '-') + '</div>' +
                        '<div class="col-md-3"><strong>No PO</strong><br>' + escapeHtml(request.no_po || '-') + '</div>' +
                        '<div class="col-md-3"><strong>Supplier</strong><br>' + escapeHtml(request.nama_supplier || '-') + '</div>' +
                    '</div>' +
                    '<div class="mt-2"><strong>Alasan</strong><br>' + escapeHtml(request.alasan_revisi || '-') + '</div>'
                );

                renderHeaderActions(request);
                renderDetailItems(request, details);
                renderLogs(logs);
            }

            function renderHeaderActions(request) {
                var html = '<div class="revision-action-row justify-content-start">';
                if (canPurchasing && request.status === 'READY_LPB_UNPOST') {
                    html += '<button type="button" class="btn btn-warning btn-sm btn-unpost-lpb" data-id-request="' + escapeHtml(request.id_request) + '">' +
                        '<i class="fas fa-unlink mr-1"></i> Unpost LPB</button>';
                }
                if (canPurchasing && (request.status === 'LPB_UNPOSTED' || request.status === 'READY_LPB_UNPOST')) {
                    html += '<button type="button" class="btn btn-success btn-sm btn-finish-request" data-id-request="' + escapeHtml(request.id_request) + '">' +
                        '<i class="fas fa-check mr-1"></i> Tandai Revisi Selesai</button>';
                }
                html += '<a class="btn btn-info btn-sm" target="_blank" href="<?= base_url('ics/detail_record_lpb?id_lpb=') ?>' + encodeURIComponent(request.id_lpb || '') + '&kd_po=' + encodeURIComponent(request.kd_po || '') + '&no_po=' + encodeURIComponent(request.no_po || '') + '&kd_suplier=' + encodeURIComponent(request.kd_supplier || '') + '">' +
                    '<i class="fas fa-external-link-alt mr-1"></i> Buka Detail LPB</a>';
                html += '</div>';
                $('#detailRevisionActions').html(html);
            }

            function renderDetailItems(request, details) {
                var groups = {};
                details.forEach(function(row) {
                    var noFaktur = row.no_faktur || '-';
                    if (!groups[noFaktur]) {
                        groups[noFaktur] = [];
                    }
                    groups[noFaktur].push(row);
                });

                var html = '';
                Object.keys(groups).forEach(function(noFaktur) {
                    var rows = groups[noFaktur];
                    var pending = rows.some(function(row) { return row.status === 'REQUESTED'; });
                    html += '<div class="detail-group-title">';
                    html += '<div><strong>Faktur: ' + escapeHtml(noFaktur) + '</strong> <span class="text-muted">(' + rows.length + ' item)</span></div>';
                    if (canAccounting && pending && (request.status === 'REQUESTED' || request.status === 'ACCOUNTING_PROCESS')) {
                        html += '<button type="button" class="btn btn-danger btn-sm btn-unpost-faktur" data-id-request="' + escapeHtml(request.id_request) + '" data-no-faktur="' + escapeHtml(noFaktur) + '">' +
                            '<i class="fas fa-undo mr-1"></i> Unpost Faktur</button>';
                    } else {
                        html += statusBadge(pending ? 'REQUESTED' : 'UNPOSTED');
                    }
                    html += '</div>';
                    html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
                    html += '<thead><tr><th>Kd Barang</th><th>Nama Barang</th><th>Lot</th><th>Expired</th><th class="text-right">Qty LPB</th><th class="text-right">Qty Terjual</th><th>Source</th><th>Status</th></tr></thead><tbody>';
                    rows.forEach(function(row) {
                        html += '<tr>' +
                            '<td>' + escapeHtml(row.kd_barang) + '</td>' +
                            '<td>' + escapeHtml(row.nama_barang) + '</td>' +
                            '<td>' + escapeHtml(row.no_lot || '-') + '</td>' +
                            '<td>' + escapeHtml(row.expired_date || '-') + '</td>' +
                            '<td class="text-right">' + formatNumber(row.qty_lpb) + '</td>' +
                            '<td class="text-right">' + formatNumber(row.qty_terjual) + '</td>' +
                            '<td>' + escapeHtml(row.source_table) + '</td>' +
                            '<td>' + statusBadge(row.status) + '</td>' +
                        '</tr>';
                    });
                    html += '</tbody></table></div>';
                });

                $('#detailRevisionItems').html(html || '<div class="alert alert-light border">Belum ada detail request.</div>');
            }

            function renderLogs(logs) {
                var html = '';
                logs.forEach(function(row) {
                    html += '<tr>' +
                        '<td>' + escapeHtml(row.dilakukan_pada || '-') + '</td>' +
                        '<td>' + escapeHtml(row.action_type || '-') + '</td>' +
                        '<td>' + escapeHtml((row.status_before || '-') + ' -> ' + (row.status_after || '-')) + '</td>' +
                        '<td>' + escapeHtml(row.keterangan || '-') + '</td>' +
                        '<td>' + escapeHtml(row.dilakukan_oleh || '-') + '</td>' +
                    '</tr>';
                });
                $('#detailRevisionLogs').html(html || '<tr><td colspan="5" class="text-center text-muted">Belum ada log.</td></tr>');
            }

            $(document).on('click', '.btn-unpost-faktur', function() {
                var idRequest = $(this).data('id-request');
                var noFaktur = $(this).data('no-faktur');
                if (!confirm('Unpost faktur ' + noFaktur + ' sesuai request revisi LPB?')) {
                    return;
                }

                $.post('<?= base_url('ics/lpb_revision/unpost_faktur') ?>', {
                    id_request: idRequest,
                    no_faktur: noFaktur
                }, function(res) {
                    alert(res.message || 'Unpost faktur diproses.');
                    if (res.status) {
                        loadDetail(idRequest);
                    }
                }, 'json').fail(function() {
                    alert('Unpost faktur gagal diproses.');
                });
            });

            $(document).on('click', '.btn-unpost-lpb', function() {
                var idRequest = $(this).data('id-request');
                var note = prompt('Keterangan UNPOST LPB:', 'UNPOST LPB karena revisi harga setelah faktur penjualan request selesai di-unpost.');
                if (note === null) {
                    return;
                }

                $.post('<?= base_url('ics/lpb_revision/unpost_lpb') ?>', {
                    id_request: idRequest,
                    keterangan: note
                }, function(res) {
                    alert(res.message || 'UNPOST LPB diproses.');
                    if (res.status) {
                        loadDetail(idRequest);
                    }
                }, 'json').fail(function() {
                    alert('UNPOST LPB gagal diproses.');
                });
            });

            $(document).on('click', '.btn-finish-request', function() {
                var idRequest = $(this).data('id-request');
                if (!confirm('Tandai request revisi harga LPB ini sudah selesai?')) {
                    return;
                }

                $.post('<?= base_url('ics/lpb_revision/finish') ?>', {
                    id_request: idRequest
                }, function(res) {
                    alert(res.message || 'Request diproses.');
                    if (res.status) {
                        window.location.reload();
                    }
                }, 'json').fail(function() {
                    alert('Request gagal diproses.');
                });
            });
        });
    </script>
</body>
