<?php
$schemaReady = !empty($schema_ready);
$accounts = isset($accounts) ? $accounts : [];
$events = isset($events) ? $events : [];
$mappings = isset($mappings) ? $mappings : [];
$exceptions = isset($exceptions) ? $exceptions : [];
$journals = isset($journals) ? $journals : [];
$dummySources = isset($dummy_sources) ? $dummy_sources : [];
?>
<style>
    .acct-page .content-header { padding: 8px .5rem 0; }
    .acct-page .page-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
    .acct-page .page-title { font-size: 26px; font-weight: 800; color: #243447; margin: 0; }
    .acct-page .tool-grid { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr); gap: 12px; }
    .acct-page .panel { background: #fff; border: 1px solid #d8e1ea; border-radius: 4px; overflow: hidden; margin-bottom: 12px; }
    .acct-page .panel-head { background: #12799f; color: #fff; padding: 11px 14px; font-weight: 800; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .acct-page .panel-body { padding: 14px; }
    .acct-page .form-row-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    .acct-page .line-table th { background: #eef5f8; color: #23384c; white-space: nowrap; }
    .acct-page .line-table td { vertical-align: middle; }
    .acct-page .money { text-align: right; white-space: nowrap; }
    .acct-page .small-muted { color: #6c7886; font-size: 12px; }
    .acct-page .summary-line { display: flex; justify-content: space-between; gap: 10px; padding: 6px 0; border-bottom: 1px solid #edf1f5; }
    .acct-page .summary-line strong { color: #23384c; }
    .acct-page .scroll-box { max-height: 280px; overflow: auto; }
    .acct-page .table-sm td, .acct-page .table-sm th { padding: .42rem; }
    .acct-page .btn-acct { background: #12799f; border-color: #12799f; color: #fff; font-weight: 700; }
    .acct-page .status-pill { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 12px; font-weight: 700; background: #eef2f5; color: #23384c; }
    @media (max-width: 991.98px) {
        .acct-page .tool-grid, .acct-page .form-row-grid { grid-template-columns: 1fr; }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper acct-page">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header"><div class="container-fluid"></div></div>
            <section class="content">
                <div class="container-fluid">
                    <div class="page-row">
                        <h1 class="page-title">Accounting Runtime Test</h1>
                        <a href="<?= base_url('jurnal') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-sitemap mr-1"></i> Chart of Accounts</a>
                    </div>

                    <?php if (!$schemaReady) : ?>
                        <div class="alert alert-warning">
                            <strong>Runtime schema belum lengkap.</strong> Jalankan SQL `docs/database/accounting_runtime_full_20260713.sql`.
                        </div>
                    <?php endif; ?>

                    <div class="tool-grid">
                        <div>
                            <div class="panel">
                                <div class="panel-head">
                                    <span>Input Jurnal Manual</span>
                                    <span id="manualBalance" class="status-pill">0 / 0</span>
                                </div>
                                <div class="panel-body">
                                    <form id="manualForm">
                                        <div class="form-row-grid mb-2">
                                            <input type="date" class="form-control" name="tanggal_transaksi" value="<?= date('Y-m-d') ?>" <?= !$schemaReady ? 'disabled' : '' ?>>
                                            <input type="text" class="form-control" name="source_no" placeholder="No dokumen" <?= !$schemaReady ? 'disabled' : '' ?>>
                                            <input type="text" class="form-control" name="idempotency_key" placeholder="Idempotency key" <?= !$schemaReady ? 'disabled' : '' ?>>
                                            <input type="text" class="form-control" name="keterangan" placeholder="Keterangan" <?= !$schemaReady ? 'disabled' : '' ?>>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered line-table" id="manualLineTable">
                                                <thead>
                                                    <tr>
                                                        <th style="min-width:230px">Akun</th>
                                                        <th>Keterangan</th>
                                                        <th style="width:150px">Debit</th>
                                                        <th style="width:150px">Kredit</th>
                                                        <th style="width:42px"></th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAddLine" <?= !$schemaReady ? 'disabled' : '' ?>><i class="fas fa-plus mr-1"></i> Baris</button>
                                            <div>
                                                <button type="submit" class="btn btn-acct" <?= !$schemaReady ? 'disabled' : '' ?>><i class="fas fa-save mr-1"></i> Simpan Draft</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-head"><span>Auto Posting Dummy Distributor Agro</span></div>
                                <div class="panel-body">
                                    <form id="autoForm">
                                        <div class="mb-2">
                                            <select class="form-control" id="dummyScenario" <?= !$schemaReady ? 'disabled' : '' ?>>
                                                <option value="">Pilih skenario dummy bisnis distributor agro</option>
                                                <?php foreach ($dummySources as $dummy) : ?>
                                                    <option value="<?= (int)$dummy->id_dummy ?>">
                                                        <?= html_escape($dummy->posting_event . ' | ' . $dummy->source_no . ' | ' . $dummy->product_name) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="small-muted mt-1" id="dummyScenarioInfo">Contoh default: penjualan obat pertanian oleh PT distributor agrobisnis.</div>
                                        </div>
                                        <div class="form-row-grid mb-2">
                                            <select class="form-control" name="posting_event" <?= !$schemaReady ? 'disabled' : '' ?>>
                                                <?php foreach ($events as $key => $label) : ?>
                                                    <option value="<?= html_escape($key) ?>"><?= html_escape($key . ' - ' . $label) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="date" class="form-control" name="tanggal_transaksi" value="<?= date('Y-m-d') ?>" <?= !$schemaReady ? 'disabled' : '' ?>>
                                            <input type="text" class="form-control" name="source_module" value="DISTRIBUTOR_AGRO_TEST" <?= !$schemaReady ? 'disabled' : '' ?>>
                                            <input type="text" class="form-control" name="source_no" value="INV-AGRO-<?= date('Ymd-His') ?>" <?= !$schemaReady ? 'disabled' : '' ?>>
                                        </div>
                                        <div class="form-row-grid mb-2">
                                            <input type="number" min="0" step="0.01" class="form-control" name="amount" value="8880000" placeholder="Amount" <?= !$schemaReady ? 'disabled' : '' ?>>
                                            <input type="number" min="0" step="0.01" class="form-control" name="tax" value="976800" placeholder="Tax" <?= !$schemaReady ? 'disabled' : '' ?>>
                                            <input type="number" min="0" step="0.01" class="form-control" name="cogs" value="6144000" placeholder="COGS" <?= !$schemaReady ? 'disabled' : '' ?>>
                                            <input type="text" class="form-control" name="keterangan" value="Faktur penjualan Herbisida GulmaClean 1L ke Koperasi Tani Subur Jaya" <?= !$schemaReady ? 'disabled' : '' ?>>
                                        </div>
                                        <button type="submit" class="btn btn-acct" <?= !$schemaReady ? 'disabled' : '' ?>><i class="fas fa-bolt mr-1"></i> Posting</button>
                                    </form>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-head">
                                    <span>Jurnal</span>
                                    <button type="button" class="btn btn-light btn-sm" id="btnRefreshJournals"><i class="fas fa-sync"></i></button>
                                </div>
                                <div class="panel-body scroll-box">
                                    <table class="table table-sm table-hover" id="journalTable">
                                        <thead><tr><th>No</th><th>Tanggal</th><th>Status</th><th class="text-right">Debit</th><th></th></tr></thead>
                                        <tbody>
                                            <?php foreach ($journals as $journal) : ?>
                                                <tr data-id="<?= (int)$journal->id_jurnal ?>">
                                                    <td><?= html_escape($journal->nomor_jurnal) ?></td>
                                                    <td><?= html_escape($journal->tanggal_transaksi) ?></td>
                                                    <td><?= html_escape($journal->status) ?></td>
                                                    <td class="money"><?= number_format((float)$journal->total_debit, 2, ',', '.') ?></td>
                                                    <td><button type="button" class="btn btn-xs btn-outline-primary btn-detail" data-id="<?= (int)$journal->id_jurnal ?>">Detail</button></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="panel">
                                <div class="panel-head"><span>Validasi Runtime</span></div>
                                <div class="panel-body">
                                    <div class="summary-line"><span>Schema</span><strong><?= $schemaReady ? 'READY' : 'BELUM' ?></strong></div>
                                    <div class="summary-line"><span>Akun posting eligible</span><strong><?= count($accounts) ?></strong></div>
                                    <div class="summary-line"><span>Mapping aktif</span><strong><?= count($mappings) ?></strong></div>
                                    <div class="summary-line"><span>Exception open</span><strong><?= count($exceptions) ?></strong></div>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-head"><span>Posting Exception</span></div>
                                <div class="panel-body scroll-box">
                                    <table class="table table-sm">
                                        <thead><tr><th>Event</th><th>Source</th><th>Error</th></tr></thead>
                                        <tbody id="exceptionRows">
                                            <?php foreach ($exceptions as $row) : ?>
                                                <tr>
                                                    <td><?= html_escape($row->posting_event) ?></td>
                                                    <td><?= html_escape($row->source_no ?: $row->source_id) ?></td>
                                                    <td><?= html_escape($row->error_code) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-head"><span>Reversal</span></div>
                                <div class="panel-body">
                                    <form id="reverseForm">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="id_jurnal" placeholder="ID jurnal POSTED" <?= !$schemaReady ? 'disabled' : '' ?>>
                                            <input type="text" class="form-control" name="reason" placeholder="Alasan reversal" <?= !$schemaReady ? 'disabled' : '' ?>>
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-warning" <?= !$schemaReady ? 'disabled' : '' ?>><i class="fas fa-undo"></i></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-head"><span>Laporan</span></div>
                                <div class="panel-body">
                                    <form id="reportForm">
                                        <div class="form-row-grid mb-2">
                                            <select class="form-control" name="report">
                                                <option value="buku_besar">Buku Besar</option>
                                                <option value="neraca_saldo">Neraca Saldo</option>
                                                <option value="laba_rugi">Laba Rugi</option>
                                                <option value="neraca">Neraca</option>
                                                <option value="piutang">Piutang</option>
                                                <option value="hutang">Hutang</option>
                                                <option value="kas_bank">Kas/Bank</option>
                                            </select>
                                            <input type="date" class="form-control" name="date_from" value="<?= date('Y-m-01') ?>">
                                            <input type="date" class="form-control" name="date_to" value="<?= date('Y-m-d') ?>">
                                            <button type="submit" class="btn btn-acct"><i class="fas fa-search"></i></button>
                                        </div>
                                    </form>
                                    <div class="scroll-box">
                                        <table class="table table-sm" id="reportTable">
                                            <thead><tr><th>Kolom</th><th class="text-right">Debit</th><th class="text-right">Kredit</th></tr></thead>
                                            <tbody><tr><td colspan="3" class="text-muted text-center">Belum dimuat.</td></tr></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="modal fade" id="journalDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Jurnal</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" id="journalDetailBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-acct" id="btnPostSelected">Posting Draft</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
$(function() {
    const accounts = <?= json_encode($accounts) ?>;
    const dummySources = <?= json_encode($dummySources) ?>;
    const endpoints = {
        manualStore: "<?= base_url('accounting-test/manual-store') ?>",
        manualPost: "<?= base_url('accounting-test/manual-post') ?>",
        autoPost: "<?= base_url('accounting-test/auto-post') ?>",
        reverse: "<?= base_url('accounting-test/reverse') ?>",
        detail: "<?= base_url('accounting-test/journal-detail') ?>",
        journals: "<?= base_url('accounting-test/journals') ?>",
        exceptions: "<?= base_url('accounting-test/exceptions') ?>",
        report: "<?= base_url('accounting-test/report') ?>"
    };
    let selectedJournalId = 0;

    function accountOptions() {
        let html = '<option value="">Pilih akun</option>';
        accounts.forEach(function(row) {
            html += '<option value="' + row.id_akun + '">' + escapeHtml(row.kode_akun + ' - ' + row.nama_akun) + '</option>';
        });
        return html;
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(chr) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[chr];
        });
    }

    function money(value) {
        return (parseFloat(value || 0)).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function notify(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ text: message });
        } else {
            alert(message);
        }
    }

    function addLine(accountId, debit, kredit) {
        $('#manualLineTable tbody').append(
            '<tr>' +
                '<td><select class="form-control form-control-sm line-account">' + accountOptions() + '</select></td>' +
                '<td><input type="text" class="form-control form-control-sm line-note"></td>' +
                '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm line-debit"></td>' +
                '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm line-kredit"></td>' +
                '<td><button type="button" class="btn btn-xs btn-outline-danger btn-remove-line"><i class="fas fa-times"></i></button></td>' +
            '</tr>'
        );
        const $row = $('#manualLineTable tbody tr:last');
        if (accountId) $row.find('.line-account').val(accountId);
        if (debit) $row.find('.line-debit').val(debit);
        if (kredit) $row.find('.line-kredit').val(kredit);
        updateBalance();
    }

    function updateBalance() {
        let debit = 0;
        let kredit = 0;
        $('#manualLineTable tbody tr').each(function() {
            debit += parseFloat($(this).find('.line-debit').val() || 0);
            kredit += parseFloat($(this).find('.line-kredit').val() || 0);
        });
        $('#manualBalance').text(money(debit) + ' / ' + money(kredit));
    }

    function collectLines() {
        const lines = [];
        $('#manualLineTable tbody tr').each(function() {
            lines.push({
                id_akun: $(this).find('.line-account').val(),
                keterangan: $(this).find('.line-note').val(),
                debit: $(this).find('.line-debit').val() || 0,
                kredit: $(this).find('.line-kredit').val() || 0
            });
        });
        return lines;
    }

    function moduleForEvent(eventName) {
        if (eventName === 'SALES_INVOICE' || eventName === 'CUSTOMER_PAYMENT' || eventName === 'SALES_RETURN') {
            return 'SALES_DISTRIBUTOR_AGRO';
        }
        if (eventName === 'PURCHASE_INVOICE' || eventName === 'SUPPLIER_PAYMENT' || eventName === 'PURCHASE_RETURN' || eventName === 'GOODS_RECEIPT') {
            return 'PURCHASE_DISTRIBUTOR_AGRO';
        }
        return 'INVENTORY_DISTRIBUTOR_AGRO';
    }

    function applyDummyScenario(id) {
        const row = dummySources.find(function(item) {
            return parseInt(item.id_dummy, 10) === parseInt(id || 0, 10);
        });
        if (!row) {
            return;
        }

        $('#autoForm [name="posting_event"]').val(row.posting_event);
        $('#autoForm [name="tanggal_transaksi"]').val(row.tanggal_transaksi);
        $('#autoForm [name="source_module"]').val(moduleForEvent(row.posting_event));
        $('#autoForm [name="source_no"]').val(row.source_no);
        $('#autoForm [name="amount"]').val(parseFloat(row.amount || 0).toFixed(2));
        $('#autoForm [name="tax"]').val(parseFloat(row.tax || 0).toFixed(2));
        $('#autoForm [name="cogs"]').val(parseFloat(row.cogs || 0).toFixed(2));
        $('#autoForm [name="keterangan"]').val(row.keterangan || '');
        $('#dummyScenarioInfo').text((row.partner_name || '-') + ' | ' + (row.product_name || '-') + ' | ' + money(row.qty) + ' ' + (row.unit_name || '') + ' @ ' + money(row.unit_price) + ' | ' + (row.warehouse_name || '-'));
    }

    function postForm(url, data, done) {
        $.post(url, data).done(function(resp) {
            notify(resp.message || 'OK');
            if (resp.success && done) done(resp);
        }).fail(function(xhr) {
            const resp = xhr.responseJSON || {};
            notify(resp.message || 'Request gagal.');
        });
    }

    function refreshJournals() {
        postForm(endpoints.journals, { status: '' }, function(resp) {
            const rows = (resp.data && resp.data.rows) || [];
            let html = '';
            rows.forEach(function(row) {
                html += '<tr data-id="' + row.id_jurnal + '">' +
                    '<td>' + escapeHtml(row.nomor_jurnal) + '</td>' +
                    '<td>' + escapeHtml(row.tanggal_transaksi) + '</td>' +
                    '<td>' + escapeHtml(row.status) + '</td>' +
                    '<td class="money">' + money(row.total_debit) + '</td>' +
                    '<td><button type="button" class="btn btn-xs btn-outline-primary btn-detail" data-id="' + row.id_jurnal + '">Detail</button></td>' +
                    '</tr>';
            });
            $('#journalTable tbody').html(html || '<tr><td colspan="5" class="text-center text-muted">Tidak ada data.</td></tr>');
        });
    }

    function renderDetail(resp) {
        const journal = resp.data.journal;
        const details = resp.data.details || [];
        selectedJournalId = parseInt(journal.id_jurnal, 10);
        let html = '<div class="mb-2"><strong>' + escapeHtml(journal.nomor_jurnal) + '</strong> <span class="status-pill">' + escapeHtml(journal.status) + '</span></div>';
        html += '<table class="table table-sm table-bordered"><thead><tr><th>Akun</th><th>Keterangan</th><th class="text-right">Debit</th><th class="text-right">Kredit</th></tr></thead><tbody>';
        details.forEach(function(row) {
            html += '<tr><td>' + escapeHtml(row.kode_akun + ' - ' + row.nama_akun) + '</td><td>' + escapeHtml(row.keterangan) + '</td><td class="money">' + money(row.debit) + '</td><td class="money">' + money(row.kredit) + '</td></tr>';
        });
        html += '</tbody></table>';
        $('#journalDetailBody').html(html);
        $('#btnPostSelected').toggle(journal.status === 'DRAFT');
        $('#journalDetailModal').modal('show');
    }

    function refreshExceptions() {
        postForm(endpoints.exceptions, { status: 'OPEN' }, function(resp) {
            const rows = (resp.data && resp.data.rows) || [];
            let html = '';
            rows.forEach(function(row) {
                html += '<tr><td>' + escapeHtml(row.posting_event) + '</td><td>' + escapeHtml(row.source_no || row.source_id) + '</td><td>' + escapeHtml(row.error_code) + '</td></tr>';
            });
            $('#exceptionRows').html(html || '<tr><td colspan="3" class="text-center text-muted">Tidak ada exception.</td></tr>');
        });
    }

    $('#btnAddLine').on('click', function() { addLine(); });
    $('#manualLineTable').on('input change', 'input, select', updateBalance);
    $('#manualLineTable').on('click', '.btn-remove-line', function() { $(this).closest('tr').remove(); updateBalance(); });

    $('#manualForm').on('submit', function(e) {
        e.preventDefault();
        const data = $(this).serializeArray();
        data.push({ name: 'lines_json', value: JSON.stringify(collectLines()) });
        postForm(endpoints.manualStore, $.param(data), function() { refreshJournals(); });
    });

    $('#autoForm').on('submit', function(e) {
        e.preventDefault();
        postForm(endpoints.autoPost, $(this).serialize(), function() { refreshJournals(); refreshExceptions(); });
    });

    $('#dummyScenario').on('change', function() {
        applyDummyScenario($(this).val());
    });

    $('#reverseForm').on('submit', function(e) {
        e.preventDefault();
        postForm(endpoints.reverse, $(this).serialize(), function() { refreshJournals(); });
    });

    $('#journalTable').on('click', '.btn-detail', function() {
        postForm(endpoints.detail, { id_jurnal: $(this).data('id') }, renderDetail);
    });

    $('#btnPostSelected').on('click', function() {
        postForm(endpoints.manualPost, { id_jurnal: selectedJournalId }, function() {
            $('#journalDetailModal').modal('hide');
            refreshJournals();
        });
    });

    $('#btnRefreshJournals').on('click', refreshJournals);

    $('#reportForm').on('submit', function(e) {
        e.preventDefault();
        postForm(endpoints.report, $(this).serialize(), function(resp) {
            const rows = (resp.data && resp.data.rows) || [];
            let html = '';
            rows.forEach(function(row) {
                const label = row.kode_akun ? (row.kode_akun + ' - ' + row.nama_akun) : (row.nomor_jurnal || row.nama_klasifikasi || row.nomor_dokumen || '-');
                html += '<tr><td>' + escapeHtml(label) + '</td><td class="money">' + money(row.debit) + '</td><td class="money">' + money(row.kredit) + '</td></tr>';
            });
            $('#reportTable tbody').html(html || '<tr><td colspan="3" class="text-center text-muted">Tidak ada data.</td></tr>');
        });
    });

    if (accounts.length >= 2) {
        addLine(accounts[0].id_akun, 100000, '');
        addLine(accounts[1].id_akun, '', 100000);
    } else {
        addLine();
        addLine();
    }

    if (dummySources.length) {
        $('#dummyScenario').val(dummySources[0].id_dummy);
        applyDummyScenario(dummySources[0].id_dummy);
    }
});
</script>
