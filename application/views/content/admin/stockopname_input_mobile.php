<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper so-input-page">
        <section class="content">
            <div class="container-fluid py-3 pb-4">
                <style>
                    .so-input-page{background:#eef3f8}.so-mobile-shell{max-width:460px;margin:0 auto}.so-panel{background:#fff;border:1px solid #dce5ee;border-radius:8px;box-shadow:0 8px 22px rgba(15,23,42,.07)}
                    .so-panel-header{padding:14px 16px;border-bottom:1px solid #e6edf4;display:flex;align-items:center;justify-content:space-between;gap:10px}.so-panel-title{font-size:16px;font-weight:800;color:#172033;margin:0}.so-scan-status{font-size:12px;color:#64748b}
                    .so-scan-box{background:#0f172a;border-radius:8px;overflow:hidden;min-height:260px;display:none}.so-scan-box video{object-fit:cover}.so-info-grid{display:grid;gap:10px}.so-readonly{background:#f8fafc;border:1px solid #dbe4ef;border-radius:8px;padding:10px 12px}.so-readonly label{display:block;margin:0 0 3px;color:#64748b;font-size:11px;font-weight:800;text-transform:uppercase}.so-readonly div{font-size:14px;color:#111827;font-weight:700;word-break:break-word}
                    .so-qty-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.so-qty-field label{font-size:12px;font-weight:800;color:#334155}.so-qty-field .input-group-text{border-radius:0 8px 8px 0;background:#f8fafc;font-size:12px;font-weight:800;color:#64748b}.so-qty-field .form-control{border-radius:8px 0 0 8px}.so-action-bar{position:sticky;bottom:0;background:linear-gradient(180deg,rgba(238,243,248,0),#eef3f8 28%);padding:16px 0 4px}.so-action-bar .btn{height:46px;font-weight:800}.so-back-btn{width:36px;height:32px;display:inline-flex;align-items:center;justify-content:center}.so-back-btn i,.so-icon-btn i{margin-right:0}.so-icon-btn{height:44px;font-weight:800}.btn i{margin-right:6px}.form-control{border-radius:8px}.so-muted{font-size:12px;color:#64748b}
                    @media(min-width:768px){.so-info-grid{grid-template-columns:1fr 1fr}.so-readonly.full{grid-column:1/-1}.so-scan-box{min-height:320px}}
                    @media(max-width:420px){.content-header h1{font-size:21px}.so-panel-header{padding:12px}.so-panel .p-3{padding:12px!important}.so-qty-grid{gap:8px}}
                </style>

                <div class="so-mobile-shell">
                    <a href="<?= base_url('stockopname/history-input') ?>" class="btn btn-outline-primary btn-block so-icon-btn mb-3">
                        <i class="fas fa-history"></i>Histori Input
                    </a>

                    <div class="so-panel mb-3">
                        <div class="p-3">
                            <button type="button" class="btn btn-primary btn-block so-icon-btn" id="btnScan">
                                <i class="fas fa-qrcode"></i>Scan
                            </button>
                            <div class="so-scan-box mt-3" id="qrReader"></div>
                            <input type="hidden" id="manualScanValue">
                        </div>
                    </div>

                    <form id="formInputOpname">
                        <input type="hidden" name="master_id" id="masterId">
                        <div class="so-panel mb-3">
                            <div class="so-panel-header">
                                <h2 class="so-panel-title">Kartu Stock</h2>
                            </div>
                            <div class="p-3">
                                <div class="so-info-grid">
                                    <div class="so-readonly full">
                                        <label>Nama Barang</label>
                                        <div id="itemName">-</div>
                                    </div>
                                    <div class="so-readonly">
                                        <label>Expired Date</label>
                                        <div id="itemExpired">-</div>
                                    </div>
                                    <div class="so-readonly">
                                        <label>No Lot</label>
                                        <div id="itemLot">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="so-panel">
                            <div class="so-panel-header">
                                <h2 class="so-panel-title">Qty Opname</h2>
                                <span class="so-muted" id="qtyTotalLabel">Total 0</span>
                            </div>
                            <div class="p-3">
                                <div class="so-qty-grid">
                                    <div class="form-group so-qty-field mb-0">
                                        <label>Qty Pcs</label>
                                        <div class="input-group input-group-lg">
                                            <input type="number" class="form-control" name="qty_pcs" id="qtyPcs" min="0" step="1" placeholder="0" inputmode="numeric">
                                            <div class="input-group-append">
                                                <span class="input-group-text">PCS</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group so-qty-field mb-0">
                                        <label>Qty Box</label>
                                        <div class="input-group input-group-lg">
                                            <input type="number" class="form-control" name="qty_box" id="qtyBox" min="0" step="1" placeholder="0" inputmode="numeric">
                                            <div class="input-group-append">
                                                <span class="input-group-text">BOX</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="so-action-bar">
                            <button type="submit" class="btn btn-success btn-block" id="btnSaveOpname" disabled>
                                <i class="fas fa-save"></i>Input Opname
                            </button>
                        </div>
                    </form>
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

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
window.addEventListener('load', function () {
    var scanner = null;
    var scanning = false;
    var selectedDimensi = 0;

    function toast(icon, title) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({toast:true,position:'top-end',icon:icon,title:title,showConfirmButton:false,timer:2400});
            return;
        }
        alert(title);
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function ajaxMessage(xhr, fallback) {
        if (xhr.responseJSON && xhr.responseJSON.message) return xhr.responseJSON.message;
        if (xhr.responseText) {
            try {
                var parsed = JSON.parse(xhr.responseText);
                if (parsed.message) return parsed.message;
            } catch (e) {}
        }
        return fallback;
    }

    function setScannerStatus(text) {
        $('#scanStatus').text(text);
    }

    function stopScanner() {
        if (!scanner || !scanning) return $.Deferred().resolve().promise();
        return scanner.stop().then(function () {
            scanning = false;
            $('#qrReader').hide();
            $('#btnScan').prop('disabled', false).html('<i class="fas fa-qrcode"></i>Scan');
            setScannerStatus('Siap');
        }).catch(function () {
            scanning = false;
            $('#qrReader').hide();
            $('#btnScan').prop('disabled', false).html('<i class="fas fa-qrcode"></i>Scan');
        });
    }

    function fillItem(row) {
        $('#masterId').val(row.id || '');
        $('#itemName').html(escapeHtml(row.nama_barang || '-'));
        $('#itemExpired').text(row.expired_date || '-');
        $('#itemLot').text(row.no_lot || '-');
        selectedDimensi = parseInt(row.dimensi || 0, 10) || 0;
        updateQtyTotal();
        $('#btnSaveOpname').prop('disabled', false);
    }

    function resetAfterSave() {
        $('#formInputOpname')[0].reset();
        $('#masterId').val('');
        $('#itemName,#itemExpired,#itemLot').text('-');
        selectedDimensi = 0;
        $('#qtyTotalLabel').text('Total 0');
        $('#btnSaveOpname').prop('disabled', true);
    }

    function updateQtyTotal() {
        var pcs = parseInt($('#qtyPcs').val() || 0, 10) || 0;
        var box = parseInt($('#qtyBox').val() || 0, 10) || 0;
        var total = (box * selectedDimensi) + pcs;
        $('#qtyTotalLabel').text('Total ' + total.toLocaleString('id-ID'));
    }

    function lookupScan(value) {
        value = $.trim(value || '');
        if (!value) {
            toast('warning', 'QRCode belum terbaca');
            return;
        }
        setScannerStatus('Mencari');
        $.ajax({
            url: '<?= base_url('admin/stockopname/input/lookup') ?>',
            type: 'POST',
            dataType: 'json',
            data: {scan_value: value},
            success: function (res) {
                if (!res.status) {
                    toast('warning', res.message || 'Data tidak ditemukan');
                    setScannerStatus('Siap');
                    return;
                }
                fillItem(res.data || {});
                $('#manualScanValue').val(value);
                toast('success', res.message || 'Data berhasil diisi');
                stopScanner();
            },
            error: function (xhr) {
                toast('error', ajaxMessage(xhr, 'Server tidak merespons'));
                setScannerStatus('Siap');
            }
        });
    }

    $('#btnScan').on('click', function () {
        if (scanning) {
            stopScanner();
            return;
        }
        if (typeof Html5Qrcode === 'undefined') {
            toast('error', 'Library scanner belum termuat');
            return;
        }

        $('#qrReader').show();
        $('#btnScan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>Membuka kamera');
        setScannerStatus('Kamera');
        scanner = scanner || new Html5Qrcode('qrReader');
        scanner.start(
            {facingMode: 'environment'},
            {fps: 10, qrbox: {width: 220, height: 220}},
            function (decodedText) {
                if (!decodedText) return;
                setScannerStatus('Terbaca');
                lookupScan(decodedText);
            }
        ).then(function () {
            scanning = true;
            $('#btnScan').prop('disabled', false).html('<i class="fas fa-stop"></i>Tutup Scan');
        }).catch(function () {
            $('#qrReader').hide();
            $('#btnScan').prop('disabled', false).html('<i class="fas fa-qrcode"></i>Scan');
            setScannerStatus('Siap');
            toast('error', 'Kamera tidak dapat dibuka');
        });
    });

    $('#btnManualLookup').on('click', function () {
        lookupScan($('#manualScanValue').val());
    });

    $('#manualScanValue').on('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            lookupScan($(this).val());
        }
    });

    $('#qtyPcs,#qtyBox').on('input', function () {
        updateQtyTotal();
    });

    $('#formInputOpname').on('submit', function (event) {
        event.preventDefault();
        var $button = $('#btnSaveOpname');
        $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>Menyimpan');
        $.ajax({
            url: '<?= base_url('admin/stockopname/input/save') ?>',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function (res) {
                if (!res.status) {
                    toast('warning', res.message || 'Validasi gagal');
                    $button.prop('disabled', false).html('<i class="fas fa-save"></i>Input Opname');
                    return;
                }
                toast('success', res.message || 'Data opname tersimpan');
                resetAfterSave();
                $button.html('<i class="fas fa-save"></i>Input Opname');
            },
            error: function (xhr) {
                toast('error', ajaxMessage(xhr, 'Server tidak merespons'));
                $button.prop('disabled', false).html('<i class="fas fa-save"></i>Input Opname');
            }
        });
    });
});
</script>
