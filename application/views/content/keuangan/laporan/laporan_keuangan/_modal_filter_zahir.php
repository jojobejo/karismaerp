<?php
/**
 * _modal_filter_zahir.php
 * Dialog Filter Data bergaya Zahir Accounting.
 */
$defaultStart = isset($start_date) && !empty($start_date) ? $start_date : date('Y-01-01');
$defaultEnd   = isset($end_date) && !empty($end_date) ? $end_date : date('Y-m-d');
$actionUrl    = isset($filter_action_url) && !empty($filter_action_url) ? $filter_action_url : base_url('laporan/keuangan/arus-kas-rangkuman');
?>
<style>
    /* Styling Zahir Filter Modal */
    .zahir-modal .modal-content {
        border-radius: 4px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        border: 1px solid #c2cdd8;
        font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
    }
    .zahir-modal .modal-header {
        border-bottom: none;
        padding: 16px 24px 8px;
    }
    .zahir-modal .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: #2c3e50;
    }
    .zahir-tab-header {
        padding: 0 24px;
        border-bottom: 2px solid #008cba;
        margin-top: 10px;
    }
    .zahir-tab-btn {
        display: inline-block;
        background: #008cba;
        color: #fff !important;
        padding: 6px 20px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 4px 4px 0 0;
        text-decoration: none !important;
    }
    .zahir-tab-body {
        padding: 24px 28px;
        background: #fff;
        min-height: 120px;
    }
    .zahir-filter-row {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .zahir-filter-label {
        font-size: 14px;
        font-weight: 500;
        color: #333;
        min-width: 75px;
    }
    .zahir-date-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .zahir-date-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .zahir-date-item span {
        font-size: 13px;
        color: #555;
    }
    .zahir-date-input {
        width: 160px;
        height: 34px;
        border: 1px solid #b0bec5;
        border-radius: 3px;
        padding: 4px 10px;
        font-size: 13px;
        color: #2c3e50;
        background: #fff;
    }
    .zahir-date-input:focus {
        border-color: #008cba;
        outline: none;
        box-shadow: 0 0 4px rgba(0, 140, 186, 0.4);
    }
    .zahir-arrow-sep {
        font-size: 16px;
        font-weight: 700;
        color: #333;
    }
    .zahir-modal .modal-footer {
        border-top: none;
        padding: 14px 24px 20px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .zahir-btn-cancel {
        background: #008cba;
        color: #fff;
        border: none;
        padding: 6px 22px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 3px;
        transition: background 0.15s ease;
    }
    .zahir-btn-cancel:hover {
        background: #00779e;
        color: #fff;
    }
    .zahir-btn-ok {
        background: #008cba;
        color: #fff;
        border: none;
        padding: 6px 26px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 3px;
        transition: background 0.15s ease;
    }
    .zahir-btn-ok:hover {
        background: #00779e;
        color: #fff;
    }
</style>

<!-- MODAL FILTER DATA (ZAHIR STYLE) -->
<div class="modal fade zahir-modal" id="modalZahirFilter" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 580px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Data</h5>
            </div>
            <form id="formZahirFilter" method="get" action="<?= $actionUrl ?>">
                <div class="zahir-tab-header">
                    <span class="zahir-tab-btn">Standar</span>
                </div>
                <div class="zahir-tab-body">
                    <div class="zahir-filter-row">
                        <div class="zahir-filter-label">Tanggal :</div>
                        <div class="zahir-date-group">
                            <div class="zahir-date-item">
                                <span>Dari :</span>
                                <input type="date" class="zahir-date-input" name="start_date" id="zahir_start_date" value="<?= $defaultStart ?>" required>
                            </div>
                            <div class="zahir-arrow-sep">&gt;</div>
                            <div class="zahir-date-item">
                                <span>Hingga :</span>
                                <input type="date" class="zahir-date-input" name="end_date" id="zahir_end_date" value="<?= $defaultEnd ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="zahir-btn-cancel" data-dismiss="modal" id="btnZahirCancel"><u>B</u>atal</button>
                    <button type="submit" class="zahir-btn-ok" id="btnZahirOk"><u>O</u>K</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openZahirFilter(type, customActionUrl) {
    var form = document.getElementById('formZahirFilter');
    if (customActionUrl) {
        form.action = customActionUrl;
    } else if (type === 'rincian') {
        form.action = '<?= base_url('laporan/keuangan/arus-kas-rincian') ?>';
    } else {
        form.action = '<?= base_url('laporan/keuangan/arus-kas-rangkuman') ?>';
    }
    $('#modalZahirFilter').modal('show');
}

// Shortcut Alt+B (Batal) dan Alt+O (OK)
document.addEventListener('keydown', function(e) {
    if ($('#modalZahirFilter').hasClass('show')) {
        if (e.altKey && (e.key === 'b' || e.key === 'B')) {
            e.preventDefault();
            $('#modalZahirFilter').modal('hide');
        } else if (e.altKey && (e.key === 'o' || e.key === 'O')) {
            e.preventDefault();
            document.getElementById('formZahirFilter').submit();
        }
    }
});
</script>
