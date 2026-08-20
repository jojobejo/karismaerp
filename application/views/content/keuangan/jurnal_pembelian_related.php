<?php
$schemaReady = !empty($schema_ready);
$journalTitle = $journal_title ?? 'Jurnal Pembelian';
$listTitle = $journal_list_title ?? 'Daftar Jurnal';
$infoText = $journal_info ?? 'Klik baris untuk melihat detail jurnal.';
$searchPlaceholder = $journal_search_placeholder ?? 'Cari data jurnal...';
$listEndpoint = $journal_list_endpoint ?? 'purchase-list';
$columns = $journal_columns ?? ['Referensi', 'Tanggal', 'Dokumen', 'Supplier', 'Kurs', 'Nilai'];
$rowType = $journal_row_type ?? 'purchase';
?>
<style>
    .jurnal-list-page .content-header { padding: 6px .5rem 0; }
    .jurnal-list-page .page-title-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
    .jurnal-list-page .page-title-left { display: flex; align-items: center; gap: 10px; }
    .jurnal-list-page .page-home-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; background: #1788b8; color: #fff; }
    .jurnal-list-page .page-title { font-size: 30px; font-weight: 700; color: #34495e; margin: 0; }
    .jurnal-list-page .panel-heading { background: #1788b8; color: #fff; padding: 13px 16px; font-weight: 700; display: flex; align-items: center; justify-content: space-between; }
    .jurnal-list-page .list-panel { background: #fff; border: 1px solid #d9e2ec; border-radius: 4px; overflow: hidden; }
    .jurnal-list-page .list-toolbar { padding: 12px 14px; border-bottom: 1px solid #eef2f7; display: flex; justify-content: space-between; gap: 8px; align-items: center; }
    .jurnal-list-page .journal-search { width: min(360px, 100%); }
    .jurnal-list-page .zahir-table { width: 100%; margin: 0; }
    .jurnal-list-page .zahir-table th { background: #1788b8; color: #fff; border-color: #1788b8; white-space: nowrap; }
    .jurnal-list-page .zahir-table td { vertical-align: middle; }
    .jurnal-list-page .zahir-table tr { cursor: pointer; }
    .jurnal-list-page .money-cell { text-align: right; white-space: nowrap; font-weight: 600; }
    .jurnal-list-page .zahir-detail-head { display: flex; gap: 22px; align-items: center; font-size: 18px; font-weight: 800; color: #020617; border-bottom: 1px solid #111827; padding-bottom: 8px; margin-bottom: 8px; }
    .jurnal-list-page .zahir-detail-table td { border: 0; padding: 7px 8px; background: #f1f3f5; }
    .jurnal-list-page .zahir-total-row { border-top: 1px solid #111827; margin-top: 8px; padding-top: 8px; font-weight: 800; }
    .jurnal-list-page .btn-jurnal-primary { background: #1788b8; border-color: #1788b8; color: #fff; font-weight: 700; }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper jurnal-list-page">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header"><div class="container-fluid"></div></div>

            <section class="content">
                <div class="container-fluid">
                    <div class="page-title-row">
                        <div class="page-title-left">
                            <a href="<?= base_url('keuangan/pembelian') ?>" class="page-home-btn" title="Kembali ke Pembelian"><i class="fas fa-arrow-left"></i></a>
                            <h1 class="page-title"><?= htmlspecialchars($journalTitle) ?></h1>
                        </div>
                    </div>

                    <div class="list-panel">
                        <div class="panel-heading">
                            <span><?= htmlspecialchars($listTitle) ?></span>
                            <small id="journalCount">0 data</small>
                        </div>
                        <div class="list-toolbar">
                            <div class="text-muted"><i class="fas fa-info-circle mr-1"></i><?= htmlspecialchars($infoText) ?></div>
                            <input type="text" class="form-control journal-search" id="journalSearch" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped zahir-table">
                                <thead>
                                    <tr>
                                        <?php foreach ($columns as $column): ?>
                                            <th class="<?= $column === 'Nilai' ? 'text-right' : '' ?>"><?= htmlspecialchars($column) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody id="journalRows">
                                    <tr><td colspan="<?= count($columns) ?>" class="text-center text-muted py-4">Memuat data jurnal...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="modal fade" id="modalJournalDetail" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="zahir-detail-head">
                            <span id="journalRef">JU</span>
                            <span id="journalDate">-</span>
                            <span id="journalTitle">Jurnal</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm zahir-detail-table">
                                <tbody id="journalDetailRows">
                                    <tr><td class="text-muted">Memuat detail jurnal...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="row zahir-total-row">
                            <div class="col-sm-6">Total Debit = <span id="journalDebit">Rp 0</span></div>
                            <div class="col-sm-6 text-sm-right">Total Kredit = <span id="journalKredit">Rp 0</span></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">Diinput oleh : <span id="journalUser">-</span></div>
                            <div>
                                <button type="button" class="btn btn-jurnal-primary mr-2" onclick="window.print()">Print</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<?php $this->load->view('partial/main/footergdg.php') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var endpointBase = '<?= base_url('jurnal') ?>';
    var schemaReady = <?= $schemaReady ? 'true' : 'false' ?>;
    var listEndpoint = <?= json_encode($listEndpoint) ?>;
    var rowType = <?= json_encode($rowType) ?>;
    var columnCount = <?= (int)count($columns) ?>;

    function formatMoney(num) {
        var p = parseFloat(num);
        if (isNaN(p)) return 'Rp 0';
        return 'Rp ' + p.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function formatDate(str) {
        if (!str) return '-';
        var parts = str.split('-');
        if (parts.length !== 3) return str;
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    function escapeHtml(string) {
        var matchHtmlRegExp = /["'&<>]/;
        var str = '' + string;
        var match = matchHtmlRegExp.exec(str);
        if (!match) return str;
        var escape;
        var html = '';
        var index = 0;
        var lastIndex = 0;
        for (index = match.index; index < str.length; index++) {
            switch (str.charCodeAt(index)) {
                case 34: escape = '&quot;'; break;
                case 38: escape = '&amp;'; break;
                case 39: escape = '&#39;'; break;
                case 60: escape = '&lt;'; break;
                case 62: escape = '&gt;'; break;
                default: continue;
            }
            if (lastIndex !== index) html += str.substring(lastIndex, index);
            lastIndex = index + 1;
            html += escape;
        }
        return lastIndex !== index ? html + str.substring(lastIndex, index) : html;
    }

    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    function rowCells(row) {
        if (rowType === 'supplier_payment') {
            return [
                row.nomor_pembayaran || row.nomor_jurnal || '-',
                formatDate(row.tanggal_transaksi),
                row.source_type === 'SUPPLIER_RETURN_DEDUCTION' ? 'Potong Retur' : 'Pembayaran',
                row.supplier || '-',
                'IDR',
                formatMoney(row.nilai)
            ];
        }

        return [
            row.referensi || row.no_retur || row.nomor_jurnal || '-',
            formatDate(row.tanggal_transaksi),
            row.nomor_lpb || '-',
            row.supplier || '-',
            'IDR',
            formatMoney(row.nilai)
        ];
    }

    function renderRows(rows) {
        $('#journalCount').text((rows || []).length + ' data');
        if (!rows || !rows.length) {
            $('#journalRows').html('<tr><td colspan="' + columnCount + '" class="text-center text-muted py-4">Data jurnal tidak ditemukan.</td></tr>');
            return;
        }

        var html = '';
        rows.forEach(function(row) {
            var cells = rowCells(row);
            html += '<tr data-id="' + parseInt(row.id_jurnal, 10) + '">';
            cells.forEach(function(cell, index) {
                html += '<td class="' + (index === cells.length - 1 ? 'money-cell' : '') + '">' + escapeHtml(cell) + '</td>';
            });
            html += '</tr>';
        });
        $('#journalRows').html(html);
    }

    function loadList(searchValue) {
        if (!schemaReady) return;
        $.ajax({
            url: endpointBase + '/' + listEndpoint,
            type: 'POST',
            dataType: 'json',
            data: { search: searchValue || '' },
            success: function(resp) {
                if (!resp.success) return;
                var rows = (resp.data && resp.data.rows) ? resp.data.rows : [];
                renderRows(rows);
            }
        });
    }

    $('#journalSearch').on('input', debounce(function() {
        loadList($(this).val().trim());
    }, 300));

    $('#journalRows').on('click', 'tr', function() {
        var id = $(this).data('id');
        if (!id) return;
        loadDetail(id);
    });

    function loadDetail(id) {
        $.ajax({
            url: endpointBase + '/general-detail',
            type: 'POST',
            dataType: 'json',
            data: { id_jurnal: id },
            success: function(resp) {
                if (!resp.success || !resp.data) {
                    alert(resp.message || 'Data jurnal tidak ditemukan.');
                    return;
                }
                var header = resp.data.journal || {};
                var rows = resp.data.details || [];

                $('#journalRef').text(header.kode_jenis_jurnal || header.nomor_jurnal || 'JU');
                $('#journalDate').text(formatDate(header.tanggal_transaksi) || '-');
                $('#journalTitle').text(header.keterangan || 'Jurnal');
                $('#journalUser').text(header.created_by_name || '-');
                $('#journalDebit').text(formatMoney(header.total_debit || 0));
                $('#journalKredit').text(formatMoney(header.total_kredit || 0));

                var html = '';
                if (!rows.length) {
                    html = '<tr><td colspan="5" class="text-center text-muted">Detail jurnal tidak ditemukan.</td></tr>';
                } else {
                    rows.forEach(function(r) {
                        var isDebit = parseFloat(r.debit || 0) > 0;
                        html += '<tr>' +
                            '<td>' + escapeHtml(r.nomor_dokumen || header.source_no || '-') + '</td>' +
                            '<td>' + escapeHtml(r.kode_rekening_display || r.kode_akun || '-') + '</td>' +
                            '<td>' + escapeHtml(r.nama_akun || r.keterangan || '-') + '</td>' +
                            '<td class="text-right">' + (isDebit ? escapeHtml(formatMoney(r.debit)) : '') + '</td>' +
                            '<td class="text-right">' + (!isDebit ? escapeHtml(formatMoney(r.kredit)) : '') + '</td>' +
                            '</tr>';
                    });
                }
                $('#journalDetailRows').html(html);
                $('#modalJournalDetail').modal('show');
            }
        });
    }

    loadList('');
});
</script>
