<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper master-barang-page">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-7">
                        <h1 class="m-0">Master Opname</h1>
                    </div>
                    <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('admin/stockopname') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Stockopname
                        </a>
                        <a href="<?= base_url('admin/stockopname/input') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-mobile-alt"></i> Input Opname
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" id="btnRefreshMasterBarang">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <style>
                    .master-barang-page{background:#f5f7fb}.mb-panel{background:#fff;border:1px solid #e1e7ef;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06)}
                    .mb-panel-header{padding:14px 16px;border-bottom:1px solid #e8edf3;display:flex;align-items:center;justify-content:space-between;gap:10px}.mb-panel-title{font-weight:700;color:#1f2937;margin:0;font-size:16px}
                    .mb-stat{min-height:96px;padding:16px;border-radius:8px;border:1px solid #e1e7ef;background:#fff;border-left:4px solid #0f766e}.mb-stat-label{font-size:12px;text-transform:uppercase;color:#64748b;font-weight:700}.mb-stat-value{font-size:28px;font-weight:800;color:#111827;line-height:1.1;margin-top:8px}.mb-stat-meta{font-size:12px;color:#6b7280;margin-top:6px}
                    .mb-stat.filter-card{width:100%;text-align:left;cursor:pointer;transition:border-color .15s ease,box-shadow .15s ease,transform .15s ease}.mb-stat.filter-card:hover{border-color:#94a3b8;box-shadow:0 10px 24px rgba(16,24,40,.1);transform:translateY(-1px)}.mb-stat.filter-card.active{border-color:#0f766e;box-shadow:0 0 0 3px rgba(15,118,110,.14),0 8px 22px rgba(16,24,40,.08)}
                    .mb-bulk-action{min-height:96px;width:100%;border-radius:8px;border:1px solid #0f766e;background:#0f766e;color:#fff;display:flex;align-items:center;justify-content:center;gap:10px;font-weight:800;transition:background .15s ease,box-shadow .15s ease,transform .15s ease}.mb-bulk-action:hover{background:#115e59;color:#fff;box-shadow:0 10px 24px rgba(15,118,110,.22);transform:translateY(-1px)}.mb-bulk-action:disabled{cursor:not-allowed;opacity:.7;transform:none}
                    .mb-filter{display:grid;grid-template-columns:minmax(240px,1fr) 96px;gap:10px}.mb-table-wrap{padding:16px}.mb-badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 9px;font-size:12px;font-weight:700;white-space:nowrap}.mb-badge.ready{background:#dcfce7;color:#166534}.mb-badge.pending{background:#ffedd5;color:#9a3412}
                    .mb-preview-box{border:1px dashed #cbd5e1;border-radius:8px;min-height:176px;display:flex;align-items:center;justify-content:center;background:#f8fafc;overflow:hidden}.mb-preview-box img{max-width:100%;max-height:220px;object-fit:contain}.mb-preview-placeholder{color:#64748b;font-size:13px}.mb-asset-path{font-size:11px;color:#64748b;word-break:break-all}.mb-print-qrcode{display:flex;align-items:center;justify-content:center;width:100%;min-height:42px;font-weight:800}.table td,.table th{vertical-align:middle}.btn i{margin-right:5px}.mb-actions{display:flex;flex-wrap:wrap;gap:6px}.mb-actions .btn{white-space:nowrap}.mb-icon-btn{width:31px;height:31px;display:inline-flex;align-items:center;justify-content:center;padding:0}.mb-icon-btn i{margin-right:0}
                    .qr-progress-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}.qr-progress-meta span{border:1px solid #e1e7ef;border-radius:6px;padding:6px 10px;background:#f8fafc;font-size:12px;color:#334155;font-weight:700}.qr-progress-meta span.is-running{border-color:#99f6e4;background:#ecfdf5;color:#0f766e}.qr-action-row{display:flex;flex-wrap:wrap;gap:8px}.qr-failed-list{max-height:220px;overflow:auto;border:1px solid #e8edf3;border-radius:8px}.qr-failed-list table{margin-bottom:0}.progress{height:18px;border-radius:8px;background:#e5e7eb;overflow:hidden;position:relative}.progress-bar{font-size:11px;font-weight:800;transition:width .35s ease;background-size:18px 18px}.qr-progress-track.is-running:after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,.55),transparent);transform:translateX(-100%);animation:qrTrackSweep 1.05s linear infinite}.qr-progress-bar.is-running{background-image:linear-gradient(45deg,rgba(255,255,255,.25) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.25) 50%,rgba(255,255,255,.25) 75%,transparent 75%,transparent);animation:qrStripeMove .75s linear infinite}.qr-pulse{display:inline-flex;align-items:center;gap:6px}.qr-pulse:before{content:"";width:7px;height:7px;border-radius:999px;background:#10b981;box-shadow:0 0 0 0 rgba(16,185,129,.45);animation:qrPulse 1.1s ease-out infinite}@keyframes qrStripeMove{from{background-position:0 0}to{background-position:18px 0}}@keyframes qrTrackSweep{to{transform:translateX(100%)}}@keyframes qrPulse{70%{box-shadow:0 0 0 8px rgba(16,185,129,0)}100%{box-shadow:0 0 0 0 rgba(16,185,129,0)}}
                    @media(max-width:768px){.mb-filter{grid-template-columns:1fr}.mb-panel-header{align-items:flex-start;flex-direction:column}.content-header h1{font-size:22px}.mb-stat-value{font-size:24px}}
                </style>

                <div class="row">
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <div class="mb-stat">
                            <div class="mb-stat-label">Total Data</div>
                            <div class="mb-stat-value" id="qrTotalItem">0</div>
                            <div class="mb-stat-meta">Sumber: stockopname_master_item</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <button type="button" class="mb-stat filter-card" data-qrcode-status="generated">
                            <div class="mb-stat-label">QR Code Selesai</div>
                            <div class="mb-stat-value" id="qrDoneItem">0</div>
                            <div class="mb-stat-meta">Klik untuk filter DONE</div>
                        </button>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <button type="button" class="mb-stat filter-card" data-qrcode-status="pending">
                            <div class="mb-stat-label">QR Code Pending</div>
                            <div class="mb-stat-value" id="qrPendingItem">0</div>
                            <div class="mb-stat-meta">Klik untuk filter PENDING</div>
                        </button>
                    </div>
                    <div class="col-12 col-md-6 col-xl-3 mb-3">
                        <button type="button" class="mb-stat filter-card" data-qrcode-status="failed">
                            <div class="mb-stat-label">QR Code Gagal</div>
                            <div class="mb-stat-value" id="qrFailedItem">0</div>
                            <div class="mb-stat-meta">Klik untuk filter FAILED</div>
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="mb-panel">
                            <div class="mb-panel-header">
                                <h2 class="mb-panel-title">Generate QR Code Opname</h2>
                                <div class="qr-action-row">
                                    <button type="button" class="btn btn-success btn-sm" id="btnGenerateQrBatch">
                                        <i class="fas fa-qrcode"></i> Generate QR Code
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" id="btnRetryFailedQr">
                                        <i class="fas fa-redo"></i> Retry Data Gagal
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRefreshQrStatus">
                                        <i class="fas fa-sync-alt"></i> Refresh Status
                                    </button>
                                </div>
                            </div>
                            <div class="p-3">
                                <div class="progress qr-progress-track" id="qrProgressTrack">
                                    <div class="progress-bar bg-success qr-progress-bar" id="qrProgressBar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="width:0%">0%</div>
                                </div>
                                <div class="qr-progress-meta">
                                    <span id="qrProgressProcessed">Processed: 0 / 0</span>
                                    <span id="qrProgressSuccess">Sukses: 0</span>
                                    <span id="qrProgressFailed">Gagal: 0</span>
                                    <span id="qrProgressStatus">Status: idle</span>
                                    <span>Batch size: 100</span>
                                </div>
                                <div class="qr-failed-list mt-3" id="qrFailedListWrap" style="display:none">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Kode Barang</th>
                                                <th>Error</th>
                                                <th>Attempt</th>
                                                <th>Update</th>
                                            </tr>
                                        </thead>
                                        <tbody id="qrFailedListBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-8 mb-3">
                        <div class="mb-panel h-100">
                            <div class="mb-panel-header">
                                <h2 class="mb-panel-title">Data Master Opname</h2>
                                <div class="mb-filter">
                                    <input type="search" class="form-control form-control-sm" id="mbSearch" placeholder="Cari nama, expired date, no lot">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="mbReset"><i class="fas fa-undo"></i>Reset</button>
                                </div>
                            </div>
                            <div class="mb-table-wrap">
                                <table class="table table-sm table-hover table-bordered w-100" id="tableMasterBarang">
                                    <thead>
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Expired Date</th>
                                            <th>No Lot</th>
                                            <th>Qty</th>
                                            <th>Qty Pcs</th>
                                            <th>Qty Box</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 mb-3">
                        <div class="mb-panel h-100">
                            <div class="mb-panel-header">
                                <h2 class="mb-panel-title">Preview Asset</h2>
                                <span class="text-muted small" id="previewItemLabel">Pilih barang</span>
                            </div>
                            <div class="p-3">
                                <div class="font-weight-bold mb-2">QRCode</div>
                                <div class="mb-preview-box mb-2" id="previewQrBox">
                                    <span class="mb-preview-placeholder">Belum tergenerate</span>
                                </div>
                                <div class="mb-asset-path mb-3" id="previewQrPath">-</div>
                                <a href="#" target="_blank" rel="noopener" class="btn btn-success mb-print-qrcode disabled" id="previewPrintQr">
                                    <i class="fas fa-print"></i> Print QRCode
                                </a>
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
window.addEventListener('load', function () {
    var selectedId = null;
    var qrcodeStatus = '';
    var isQrRunning = false;
    var qrRun = {processed: 0, success: 0, failed: 0, total: 0, mode: 'normal'};
    var qrButtonHtml = {
        normal: $('#btnGenerateQrBatch').html(),
        retry: $('#btnRetryFailedQr').html()
    };
    $('[data-toggle="tooltip"]').tooltip();
    var table = $('#tableMasterBarang').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searchDelay: 350,
        order: [[0, 'asc']],
        ajax: {
            url: '<?= base_url('admin/stockopname/master_opname/ajax-list') ?>',
            type: 'POST',
            data: function (d) {
                d.search = {value: $('#mbSearch').val()};
                d.qrcode_status = qrcodeStatus;
            }
        },
        columns: [
            {data: 'nama_barang'},
            {data: 'expired_date'},
            {data: 'no_lot'},
            {data: 'qty', render: function (value) { return formatNumber(value); }},
            {data: 'qty_pcs', render: function (value) { return formatNumber(value); }},
            {data: 'qty_box', render: function (value) { return formatNumber(value); }},
            {data: null, orderable: false, searchable: false, render: renderActions}
        ],
        drawCallback: function () {
            var rows = this.api().rows({page: 'current'}).data();
            if (!selectedId && rows.length > 0) {
                selectedId = rows[0].id;
                loadPreview(selectedId);
            }
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    function formatNumber(value) {
        var parsed = Number(value || 0);
        return parsed.toLocaleString('id-ID');
    }

    function parseDisplayNumber(value) {
        return parseInt(String(value || '0').replace(/[^\d]/g, ''), 10) || 0;
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function toast(icon, title) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 2400
            });
            return;
        }
        alert(title);
    }

    function ajaxMessage(xhr, fallback) {
        if (xhr.responseJSON && xhr.responseJSON.message) {
            return xhr.responseJSON.message;
        }
        if (xhr.responseText) {
            try {
                var parsed = JSON.parse(xhr.responseText);
                if (parsed.message) {
                    return parsed.message;
                }
            } catch (e) {}
        }
        return fallback;
    }

    function renderAssetCell(path, id, ready) {
        if (!path) {
            return '<span class="mb-badge pending">Belum Ada</span>';
        }
        if (parseInt(ready || 0, 10) !== 1) {
            return '<span class="mb-badge pending">File Hilang</span><div class="mb-asset-path mt-1">' + escapeHtml(path) + '</div>';
        }
        return '<button type="button" class="btn btn-outline-success btn-xs mb-icon-btn btn-preview-asset" data-toggle="tooltip" title="Preview asset" data-id="' + id + '"><i class="fas fa-eye"></i></button><div class="mb-asset-path mt-1">' + escapeHtml(path) + '</div>';
    }

    function renderActions(row) {
        var html = '<div class="mb-actions">';
        var status = String(row.qrcode_status || '').toUpperCase();
        if (status !== 'DONE') {
            html += '<button type="button" class="btn btn-outline-success btn-xs mb-icon-btn btn-generate-asset" data-toggle="tooltip" title="Generate QRCode" data-type="qrcode" data-regenerate="0" data-id="' + row.id + '"><i class="fas fa-qrcode"></i></button>';
        } else if (parseInt(row.qrcode_ready || 0, 10) !== 1) {
            html += '<button type="button" class="btn btn-outline-warning btn-xs mb-icon-btn btn-generate-asset" data-toggle="tooltip" title="Generate ulang QRCode" data-type="qrcode" data-regenerate="1" data-id="' + row.id + '"><i class="fas fa-qrcode"></i></button>';
        } else {
            html += '<button type="button" class="btn btn-outline-secondary btn-xs mb-icon-btn btn-preview-asset" data-toggle="tooltip" title="QRCode sudah ada" data-id="' + row.id + '"><i class="fas fa-check"></i></button>';
        }
        html += '</div>';
        return html;
    }

    function renderPreview(boxSelector, pathSelector, asset, label) {
        var path = asset && asset.path ? asset.path : '';
        var url = asset && asset.url ? asset.url + '?t=' + new Date().getTime() : '';
        if (asset && asset.exists && url) {
            $(boxSelector).html('<img src="' + escapeHtml(url) + '" alt="' + escapeHtml(label) + '">');
            $(pathSelector).text(path);
            return;
        }
        $(boxSelector).html('<span class="mb-preview-placeholder">Belum tergenerate</span>');
        $(pathSelector).text(path || '-');
    }

    function loadSummary(showToast) {
        $.getJSON('<?= base_url('admin/stockopname/qrcode/summary') ?>', function (res) {
            if (!res.success) {
                toast('error', res.message || 'Gagal memuat summary');
                return;
            }
            $('#qrTotalItem').text(formatNumber(res.total || 0));
            $('#qrDoneItem').text(formatNumber(res.done || 0));
            $('#qrPendingItem').text(formatNumber(res.pending || 0));
            $('#qrFailedItem').text(formatNumber(res.failed || 0));
            $('#btnRetryFailedQr').prop('disabled', isQrRunning || parseInt(res.failed || 0, 10) <= 0);
            if (showToast) {
                toast('success', 'Status QR Code diperbarui');
            }
        }).fail(function () {
            toast('error', 'Server tidak merespons');
        });
    }

    function loadPreview(id) {
        if (!id) {
            return;
        }
        $.ajax({
            url: '<?= base_url('admin/stockopname/master_opname/preview-asset') ?>',
            type: 'POST',
            dataType: 'json',
            data: {id: id},
            success: function (res) {
                if (!res.status) {
                    toast('error', res.message || 'Gagal memuat preview');
                    return;
                }
                selectedId = res.data.id;
                $('#previewItemLabel').text('#' + res.data.id + ' - ' + res.data.nama_barang);
                renderPreview('#previewQrBox', '#previewQrPath', res.data.qrcode, 'QRCode ' + res.data.id);
                $('#previewPrintQr')
                    .attr('href', '<?= base_url('admin/stockopname/master_opname/print-qrcode') ?>/' + res.data.id)
                    .removeClass('disabled');
            },
            error: function (xhr) {
                toast('error', ajaxMessage(xhr, 'Gagal memuat preview asset'));
            }
        });
    }

    function setQrButtons(disabled) {
        $('#btnGenerateQrBatch,#btnRetryFailedQr,#btnRefreshQrStatus,#btnRefreshMasterBarang').prop('disabled', disabled);
        if (!disabled) {
            $('#btnGenerateQrBatch').html(qrButtonHtml.normal);
            $('#btnRetryFailedQr').html(qrButtonHtml.retry);
            loadSummary(false);
        }
    }

    function setQrRunningState(running, mode) {
        $('#qrProgressTrack,#qrProgressBar').toggleClass('is-running', running);
        $('#qrProgressStatus').toggleClass('is-running', running);
        if (running) {
            var $button = mode === 'retry' ? $('#btnRetryFailedQr') : $('#btnGenerateQrBatch');
            var label = mode === 'retry' ? 'Retry berjalan' : 'Generate berjalan';
            $button.html('<i class="fas fa-spinner fa-spin"></i> ' + label);
            return;
        }

        $('#btnGenerateQrBatch').html(qrButtonHtml.normal);
        $('#btnRetryFailedQr').html(qrButtonHtml.retry);
    }

    function setQrProgress(percent, processedText, statusText) {
        percent = Math.max(0, Math.min(100, parseInt(percent || 0, 10)));
        var displayPercent = isQrRunning && percent === 0 ? 4 : percent;
        $('#qrProgressBar')
            .css('width', displayPercent + '%')
            .attr('aria-valuenow', percent)
            .text(percent + '%');
        $('#qrProgressProcessed').text(processedText);
        $('#qrProgressSuccess').text('Sukses: ' + formatNumber(qrRun.success));
        $('#qrProgressFailed').text('Gagal: ' + formatNumber(qrRun.failed));
        $('#qrProgressStatus').html(isQrRunning ? '<span class="qr-pulse">Status: ' + escapeHtml(statusText) + '</span>' : 'Status: ' + escapeHtml(statusText));
    }

    function loadFailedList() {
        $.getJSON('<?= base_url('admin/stockopname/qrcode/failed_list') ?>', function (res) {
            if (!res.success || !res.data || !res.data.length) {
                $('#qrFailedListWrap').hide();
                $('#qrFailedListBody').empty();
                return;
            }

            var html = '';
            $.each(res.data, function (_, row) {
                html += '<tr>' +
                    '<td>' + escapeHtml(row.id) + '</td>' +
                    '<td>' + escapeHtml(row.kode_barang) + '</td>' +
                    '<td>' + escapeHtml(row.qrcode_error_message || '-') + '</td>' +
                    '<td>' + escapeHtml(row.qrcode_attempt_count || 0) + '</td>' +
                    '<td>' + escapeHtml(row.qrcode_updated_at || '-') + '</td>' +
                    '</tr>';
            });
            $('#qrFailedListBody').html(html);
            $('#qrFailedListWrap').show();
        });
    }

    function startQrRun(mode) {
        if (isQrRunning) {
            return;
        }

        mode = mode === 'retry' ? 'retry' : 'normal';
        var title = mode === 'retry' ? 'Retry data QR Code gagal?' : 'Generate QR Code opname?';
        var text = mode === 'retry'
            ? 'Sistem hanya memproses data FAILED dengan retry flag aktif.'
            : 'Sistem memproses data PENDING bertahap per batch 100 tanpa reload halaman.';

        function begin() {
            isQrRunning = true;
            qrRun = {
                processed: 0,
                success: 0,
                failed: 0,
                total: mode === 'retry' ? parseDisplayNumber($('#qrFailedItem').text()) : parseDisplayNumber($('#qrTotalItem').text()),
                mode: mode
            };
            setQrButtons(true);
            setQrRunningState(true, mode);
            var totalLabel = qrRun.total > 0 ? formatNumber(qrRun.total) : 'menghitung';
            setQrProgress(0, 'Processed: 0 / ' + totalLabel, mode === 'retry' ? 'mulai retry' : 'mulai generate');
            runQrBatch(mode);
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: mode === 'retry' ? 'Retry' : 'Generate',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    begin();
                }
            });
            return;
        }

        if (confirm(title)) {
            begin();
        }
    }

    function runQrBatch(mode) {
        var url = mode === 'retry'
            ? '<?= base_url('admin/stockopname/qrcode/retry_failed') ?>'
            : '<?= base_url('admin/stockopname/qrcode/generate_batch') ?>';

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            timeout: 28000,
            data: {batch_size: 100},
            success: function (res) {
                if (!res.success) {
                    toast('error', res.message || 'Generate QR Code gagal');
                    isQrRunning = false;
                    setQrRunningState(false, mode);
                    setQrButtons(false);
                    return;
                }

                qrRun.processed += parseInt(res.processed || 0, 10);
                qrRun.success += parseInt(res.success_count || 0, 10);
                qrRun.failed += parseInt(res.failed_count || 0, 10);
                qrRun.total = mode === 'retry'
                    ? parseInt(res.total_failed || (qrRun.processed + (res.remaining_failed || 0)), 10)
                    : parseInt(res.total || 0, 10);

                var remaining = mode === 'retry' ? parseInt(res.remaining_failed || 0, 10) : parseInt(res.remaining || 0, 10);
                var processedLabel = 'Processed: ' + formatNumber(qrRun.processed) + ' / ' + formatNumber(qrRun.total || qrRun.processed);
                var statusLabel = res.is_completed ? 'selesai' : 'batch berjalan, sisa ' + formatNumber(remaining);
                setQrProgress(res.percent || 0, processedLabel, statusLabel);
                loadSummary(false);

                if (!res.is_completed && (parseInt(res.processed || 0, 10) > 0 || res.time_limited)) {
                    window.setTimeout(function () {
                        runQrBatch(mode);
                    }, 250);
                    return;
                }

                isQrRunning = false;
                setQrRunningState(false, mode);
                setQrButtons(false);
                setQrProgress(res.percent || 100, processedLabel, 'selesai');
                table.ajax.reload(null, false);
                if (selectedId) {
                    loadPreview(selectedId);
                }
                loadFailedList();
                toast('success', mode === 'retry' ? 'Retry QR Code selesai' : 'Generate QR Code selesai');
            },
            error: function (xhr) {
                isQrRunning = false;
                setQrRunningState(false, mode);
                setQrButtons(false);
                setQrProgress(0, 'Processed: ' + formatNumber(qrRun.processed) + ' / ' + formatNumber(qrRun.total), 'request gagal');
                loadSummary(false);
                loadFailedList();
                toast('error', ajaxMessage(xhr, 'Server gagal memproses batch QR Code'));
            }
        });
    }

    $('#mbSearch').on('keyup', function () {
        selectedId = null;
        table.ajax.reload();
    });

    $('#mbReset').on('click', function () {
        $('#mbSearch').val('');
        qrcodeStatus = '';
        $('.filter-card').removeClass('active');
        selectedId = null;
        table.ajax.reload();
    });

    $('.filter-card').on('click', function () {
        var status = $(this).data('qrcode-status') || '';
        qrcodeStatus = qrcodeStatus === status ? '' : status;
        $('.filter-card').removeClass('active');
        if (qrcodeStatus) {
            $(this).addClass('active');
        }
        selectedId = null;
        table.ajax.reload();
    });

    $('#btnRefreshMasterBarang').on('click', function () {
        table.ajax.reload(null, false);
        if (selectedId) {
            loadPreview(selectedId);
        }
        loadSummary(true);
        loadFailedList();
    });

    $('#btnGenerateQrBatch').on('click', function () {
        startQrRun('normal');
    });

    $('#btnRetryFailedQr').on('click', function () {
        startQrRun('retry');
    });

    $('#btnRefreshQrStatus').on('click', function () {
        loadSummary(true);
        loadFailedList();
    });

    $('#tableMasterBarang tbody').on('click', 'tr', function (e) {
        if ($(e.target).closest('button,a,input').length) {
            return;
        }
        var row = table.row(this).data();
        if (row && row.id) {
            loadPreview(row.id);
        }
    });

    $('#tableMasterBarang').on('click', '.btn-preview-asset', function () {
        loadPreview($(this).data('id'));
    });

    $('#tableMasterBarang').on('click', '.btn-generate-asset', function () {
        var $button = $(this);
        var id = $button.data('id');
        var regenerate = $button.data('regenerate') || 0;
        var original = $button.html();
        var url = '<?= base_url('admin/stockopname/master_opname/generate-qrcode') ?>';

        $('[data-toggle="tooltip"]').tooltip('hide');
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {id: id, regenerate: regenerate},
            success: function (res) {
                if (!res.status) {
                    toast('error', res.message || 'Generate gagal');
                    return;
                }
                selectedId = id;
                table.ajax.reload(null, false);
                loadPreview(id);
                loadSummary(false);
                toast('success', res.message || 'Asset berhasil dibuat');
            },
            error: function (xhr) {
                toast('error', ajaxMessage(xhr, 'Server gagal generate asset'));
            },
            complete: function () {
                $button.prop('disabled', false).html(original);
            }
        });
    });

    loadSummary(false);
    loadFailedList();
});
</script>
