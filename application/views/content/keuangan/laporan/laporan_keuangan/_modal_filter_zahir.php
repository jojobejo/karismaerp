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
        border: 1px solid #94a3b8;
        font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
        background: #fff;
    }
    .zahir-modal .modal-header {
        border-bottom: none;
        padding: 20px 28px 10px;
    }
    .zahir-modal .modal-title {
        font-size: 22px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }
    .zahir-tab-header {
        padding: 0 28px;
        border-bottom: 2px solid #008cba;
        margin-top: 6px;
    }
    .zahir-tab-btn {
        display: inline-block;
        background: #008cba;
        color: #fff !important;
        padding: 6px 22px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 4px 4px 0 0;
        text-decoration: none !important;
        letter-spacing: 0.2px;
    }
    .zahir-tab-body {
        padding: 32px 36px 36px;
        background: #fff;
    }
    .zahir-filter-row {
        display: flex;
        align-items: flex-end;
        gap: 16px;
    }
    .zahir-filter-label {
        font-size: 14px;
        font-weight: 500;
        color: #222;
        min-width: 85px;
        margin-bottom: 6px;
        white-space: nowrap;
    }
    .zahir-date-col {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .zahir-sub-label {
        font-size: 13px;
        color: #475569;
        font-weight: 500;
        margin-bottom: 0;
        white-space: nowrap;
    }
    .zahir-date-input {
        width: 175px;
        height: 32px;
        border: 1px solid #94a3b8;
        border-radius: 2px;
        padding: 2px 8px;
        font-size: 13px;
        color: #1e293b;
        background: #fff;
        outline: none;
        box-sizing: border-box;
    }
    .zahir-date-input:focus {
        border-color: #008cba;
        box-shadow: 0 0 3px rgba(0, 140, 186, 0.5);
    }
    .zahir-arrow-sep {
        font-size: 16px;
        font-weight: 700;
        color: #333;
        margin-bottom: 6px;
        padding: 0 4px;
        user-select: none;
    }
    .zahir-modal .modal-footer {
        border-top: none;
        padding: 10px 32px 26px;
        display: flex;
        justify-content: flex-end;
        gap: 14px;
    }
    .zahir-btn-action {
        background: #008cba;
        color: #fff;
        border: none;
        padding: 6px 26px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 3px;
        transition: background 0.15s ease;
        min-width: 80px;
        text-align: center;
        cursor: pointer;
    }
    .zahir-btn-action:hover {
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
                        <div class="zahir-date-col">
                            <label for="zahir_start_date" class="zahir-sub-label">Dari :</label>
                            <input type="date" class="zahir-date-input" name="start_date" id="zahir_start_date" value="<?= $defaultStart ?>" required>
                        </div>
                        <div class="zahir-arrow-sep">&gt;</div>
                        <div class="zahir-date-col">
                            <label for="zahir_end_date" class="zahir-sub-label">Hingga :</label>
                            <input type="date" class="zahir-date-input" name="end_date" id="zahir_end_date" value="<?= $defaultEnd ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="zahir-btn-action" data-dismiss="modal" id="btnZahirCancel"><u>B</u>atal</button>
                    <button type="submit" class="zahir-btn-action" id="btnZahirOk"><u>O</u>K</button>
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
