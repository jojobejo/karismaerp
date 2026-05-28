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
                        <h1 class="m-0">All Barang</h1>
                    </div>
                    <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('admin/stockopname') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Stockopname
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
                    .mb-filter{display:grid;grid-template-columns:minmax(240px,1fr) 96px;gap:10px}.mb-table-wrap{padding:16px}.mb-badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 9px;font-size:12px;font-weight:700;white-space:nowrap}.mb-badge.ready{background:#dcfce7;color:#166534}.mb-badge.pending{background:#ffedd5;color:#9a3412}
                    .mb-preview-box{border:1px dashed #cbd5e1;border-radius:8px;min-height:176px;display:flex;align-items:center;justify-content:center;background:#f8fafc;overflow:hidden}.mb-preview-box img{max-width:100%;max-height:220px;object-fit:contain}.mb-preview-placeholder{color:#64748b;font-size:13px}.mb-asset-path{font-size:11px;color:#64748b;word-break:break-all}.table td,.table th{vertical-align:middle}.btn i{margin-right:5px}.mb-actions{display:flex;flex-wrap:wrap;gap:6px}.mb-actions .btn{white-space:nowrap}.mb-icon-btn{width:31px;height:31px;display:inline-flex;align-items:center;justify-content:center;padding:0}.mb-icon-btn i{margin-right:0}
                    @media(max-width:768px){.mb-filter{grid-template-columns:1fr}.mb-panel-header{align-items:flex-start;flex-direction:column}.content-header h1{font-size:22px}.mb-stat-value{font-size:24px}}
                </style>

                <div class="row">
                    <div class="col-12 col-md-4 col-xl-3 mb-3">
                        <div class="mb-stat">
                            <div class="mb-stat-label">All Barang</div>
                            <div class="mb-stat-value" id="mbTotalItem">0</div>
                            <div class="mb-stat-meta">tb_master_barang_all</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-8 mb-3">
                        <div class="mb-panel h-100">
                            <div class="mb-panel-header">
                                <h2 class="mb-panel-title">Master Barang</h2>
                                <div class="mb-filter">
                                    <input type="search" class="form-control form-control-sm" id="mbSearch" placeholder="Cari kode, nama, satuan, asset">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="mbReset"><i class="fas fa-undo"></i>Reset</button>
                                </div>
                            </div>
                            <div class="mb-table-wrap">
                                <table class="table table-sm table-hover table-bordered w-100" id="tableMasterBarang">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Dimensi</th>
                                            <th>Satuan</th>
                                            <th>Berat</th>
                                            <th>Kubikasi</th>
                                            <th>QRCode</th>
                                            <th>Barcode</th>
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

                                <div class="font-weight-bold mb-2">Barcode</div>
                                <div class="mb-preview-box mb-2" id="previewBarcodeBox">
                                    <span class="mb-preview-placeholder">Belum tergenerate</span>
                                </div>
                                <div class="mb-asset-path" id="previewBarcodePath">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="modalMasterBarang" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content" id="formMasterBarang">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Master Barang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="master_id" name="id">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="kd_barang">Kode</label>
                            <input type="text" class="form-control" id="kd_barang" name="kd_barang" required>
                        </div>
                        <div class="form-group col-md-8">
                            <label for="nama_barang">Nama Barang</label>
                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="satuan">Satuan</label>
                            <input type="text" class="form-control" id="satuan" name="satuan">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="p">P</label>
                            <input type="number" step="any" class="form-control" id="p" name="p" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="l">L</label>
                            <input type="number" step="any" class="form-control" id="l" name="l" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="t">T</label>
                            <input type="number" step="any" class="form-control" id="t" name="t" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="berat">Berat</label>
                            <input type="number" step="any" class="form-control" id="berat" name="berat" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="kubikasi">Kubikasi</label>
                            <input type="number" step="any" class="form-control" id="kubikasi" name="kubikasi">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveMasterBarang"><i class="fas fa-save"></i>Simpan</button>
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
window.addEventListener('load', function () {
    var selectedId = null;
    $('[data-toggle="tooltip"]').tooltip();
    var table = $('#tableMasterBarang').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searchDelay: 350,
        order: [[1, 'asc']],
        ajax: {
            url: '<?= base_url('admin/stockopname/master-barang/ajax-list') ?>',
            type: 'POST',
            data: function (d) {
                d.search = {value: $('#mbSearch').val()};
            }
        },
        columns: [
            {data: 'kd_barang'},
            {data: 'nama_barang'},
            {data: 'dimensi', render: function (value) { return formatNumber(value); }},
            {data: 'satuan'},
            {data: 'berat', render: function (value) { return formatNumber(value); }},
            {data: 'kubikasi'},
            {data: 'qrcode', orderable: false, render: function (value, type, row) { return renderAssetCell(value, row.id, row.qrcode_ready); }},
            {data: 'barcode', orderable: false, render: function (value, type, row) { return renderAssetCell(value, row.id, row.barcode_ready); }},
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
        html += '<button type="button" class="btn btn-outline-primary btn-xs mb-icon-btn btn-edit-master" data-toggle="tooltip" title="Edit barang" data-id="' + row.id + '"><i class="fas fa-edit"></i></button>';
        if (!row.qrcode) {
            html += '<button type="button" class="btn btn-outline-success btn-xs mb-icon-btn btn-generate-asset" data-toggle="tooltip" title="Generate QRCode" data-type="qrcode" data-regenerate="0" data-id="' + row.id + '"><i class="fas fa-qrcode"></i></button>';
        } else if (parseInt(row.qrcode_ready || 0, 10) !== 1) {
            html += '<button type="button" class="btn btn-outline-warning btn-xs mb-icon-btn btn-generate-asset" data-toggle="tooltip" title="Generate ulang QRCode" data-type="qrcode" data-regenerate="1" data-id="' + row.id + '"><i class="fas fa-qrcode"></i></button>';
        } else {
            html += '<button type="button" class="btn btn-outline-secondary btn-xs mb-icon-btn btn-preview-asset" data-toggle="tooltip" title="QRCode sudah ada" data-id="' + row.id + '"><i class="fas fa-check"></i></button>';
        }
        if (!row.barcode) {
            html += '<button type="button" class="btn btn-outline-dark btn-xs mb-icon-btn btn-generate-asset" data-toggle="tooltip" title="Generate Barcode" data-type="barcode" data-regenerate="0" data-id="' + row.id + '"><i class="fas fa-barcode"></i></button>';
        } else if (parseInt(row.barcode_ready || 0, 10) !== 1) {
            html += '<button type="button" class="btn btn-outline-warning btn-xs mb-icon-btn btn-generate-asset" data-toggle="tooltip" title="Generate ulang Barcode" data-type="barcode" data-regenerate="1" data-id="' + row.id + '"><i class="fas fa-barcode"></i></button>';
        } else {
            html += '<button type="button" class="btn btn-outline-secondary btn-xs mb-icon-btn btn-preview-asset" data-toggle="tooltip" title="Barcode sudah ada" data-id="' + row.id + '"><i class="fas fa-check"></i></button>';
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
        $.getJSON('<?= base_url('admin/stockopname/master-barang/widgets') ?>', function (res) {
            if (!res.status) {
                toast('error', res.message || 'Gagal memuat summary');
                return;
            }
            $('#mbTotalItem').text(formatNumber((res.data.summary || {}).total_item || 0));
            if (showToast) {
                toast('success', 'Master barang diperbarui');
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
            url: '<?= base_url('admin/stockopname/master-barang/preview-asset') ?>',
            type: 'POST',
            dataType: 'json',
            data: {id: id},
            success: function (res) {
                if (!res.status) {
                    toast('error', res.message || 'Gagal memuat preview');
                    return;
                }
                selectedId = res.data.id;
                $('#previewItemLabel').text(res.data.kd_barang + ' - ' + res.data.nama_barang);
                renderPreview('#previewQrBox', '#previewQrPath', res.data.qrcode, 'QRCode ' + res.data.kd_barang);
                renderPreview('#previewBarcodeBox', '#previewBarcodePath', res.data.barcode, 'Barcode ' + res.data.kd_barang);
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

    $('#tableMasterBarang').on('click', '.btn-edit-master', function () {
        var id = $(this).data('id');
        $.ajax({
            url: '<?= base_url('admin/stockopname/master-barang/detail') ?>',
            type: 'POST',
            dataType: 'json',
            data: {id: id},
            success: function (res) {
                if (!res.status) {
                    toast('error', res.message || 'Data tidak ditemukan');
                    return;
                }
                $('#master_id').val(res.data.id);
                $('#kd_barang').val(res.data.kd_barang);
                $('#nama_barang').val(res.data.nama_barang);
                $('#satuan').val(res.data.satuan);
                $('#p').val(res.data.p);
                $('#l').val(res.data.l);
                $('#t').val(res.data.t);
                $('#berat').val(res.data.berat);
                $('#kubikasi').val(res.data.kubikasi);
                $('#modalMasterBarang').modal('show');
            },
            error: function (xhr) {
                toast('error', ajaxMessage(xhr, 'Gagal mengambil detail barang'));
            }
        });
    });

    $('#formMasterBarang').on('submit', function (e) {
        e.preventDefault();
        var $button = $('#btnSaveMasterBarang');
        var original = $button.html();
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>Menyimpan');

        $.ajax({
            url: '<?= base_url('admin/stockopname/master-barang/update') ?>',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function (res) {
                if (!res.status) {
                    toast('error', res.message || 'Gagal update master barang');
                    return;
                }
                $('#modalMasterBarang').modal('hide');
                selectedId = $('#master_id').val();
                table.ajax.reload(null, false);
                loadPreview(selectedId);
                toast('success', res.message || 'Master barang berhasil diperbarui');
            },
            error: function (xhr) {
                toast('error', ajaxMessage(xhr, 'Server gagal memproses update'));
            },
            complete: function () {
                $button.prop('disabled', false).html(original);
            }
        });
    });

    $('#tableMasterBarang').on('click', '.btn-generate-asset', function () {
        var $button = $(this);
        var id = $button.data('id');
        var type = $button.data('type');
        var regenerate = $button.data('regenerate') || 0;
        var original = $button.html();
        var url = type === 'barcode'
            ? '<?= base_url('admin/stockopname/master-barang/generate-barcode') ?>'
            : '<?= base_url('admin/stockopname/master-barang/generate-qrcode') ?>';

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
