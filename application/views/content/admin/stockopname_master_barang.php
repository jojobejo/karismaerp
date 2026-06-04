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
                    @media(max-width:768px){.mb-filter{grid-template-columns:1fr}.mb-panel-header{align-items:flex-start;flex-direction:column}.content-header h1{font-size:22px}.mb-stat-value{font-size:24px}}
                </style>

                <div class="row">
                    <div class="col-12 col-md-4 col-xl-3 mb-3">
                        <div class="mb-stat">
                            <div class="mb-stat-label">Master Opname</div>
                            <div class="mb-stat-value" id="mbTotalItem">0</div>
                            <div class="mb-stat-meta">Grup nama barang, expired date, no lot</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-xl-3 mb-3">
                        <button type="button" class="mb-stat filter-card" data-qrcode-status="generated">
                            <div class="mb-stat-label">Barang QRCode Terbuat</div>
                            <div class="mb-stat-value" id="mbQrGeneratedItem">0</div>
                            <div class="mb-stat-meta">Klik untuk filter sudah digenerate</div>
                        </button>
                    </div>
                    <div class="col-12 col-md-4 col-xl-3 mb-3">
                        <button type="button" class="mb-stat filter-card" data-qrcode-status="pending">
                            <div class="mb-stat-label">Barang Belum QRCode</div>
                            <div class="mb-stat-value" id="mbQrPendingItem">0</div>
                            <div class="mb-stat-meta">Klik untuk filter belum digenerate</div>
                        </button>
                    </div>
                    <div class="col-12 col-md-4 col-xl-3 mb-3">
                        <button type="button" class="mb-bulk-action" id="btnGenerateAllQr">
                            <i class="fas fa-qrcode"></i>
                            Generate Semua QRCode
                        </button>
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
        if (!row.qrcode) {
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
        $.getJSON('<?= base_url('admin/stockopname/master_opname/widgets') ?>', function (res) {
            if (!res.status) {
                toast('error', res.message || 'Gagal memuat summary');
                return;
            }
            $('#mbTotalItem').text(formatNumber((res.data.summary || {}).total_item || 0));
            $('#mbQrGeneratedItem').text(formatNumber((res.data.summary || {}).qrcode_generated_item || 0));
            $('#mbQrPendingItem').text(formatNumber((res.data.summary || {}).qrcode_pending_item || 0));
            if (showToast) {
                toast('success', 'Master opname diperbarui');
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
    });

    $('#btnGenerateAllQr').on('click', function () {
        var $button = $(this);
        var original = $button.html();

        function runGenerate() {
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses QRCode');
            $.ajax({
                url: '<?= base_url('admin/stockopname/master_opname/generate-qrcode-all') ?>',
                type: 'POST',
                dataType: 'json',
                success: function (res) {
                    if (!res.status) {
                        toast('error', res.message || 'Generate QRCode semua barang gagal');
                        return;
                    }

                    table.ajax.reload(null, false);
                    if (selectedId) {
                        loadPreview(selectedId);
                    }
                    loadSummary(false);

                    var summary = res.data || {};
                    toast('success', 'QRCode selesai: ' + formatNumber(summary.generated || 0) + ' data diproses');
                },
                error: function (xhr) {
                    toast('error', ajaxMessage(xhr, 'Server gagal generate semua QRCode'));
                },
                complete: function () {
                    $button.prop('disabled', false).html(original);
                }
            });
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Generate semua QRCode?',
                text: 'Semua QRCode di stockopname_master akan dibuat ulang dan path database akan diupdate.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Generate',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    runGenerate();
                }
            });
            return;
        }

        if (confirm('Generate ulang semua QRCode stockopname_master?')) {
            runGenerate();
        }
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
});
</script>
