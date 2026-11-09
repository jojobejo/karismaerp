<?php
/**
 * _modal_filter_zahir.php
 * Dialog Filter Data bergaya Zahir Accounting.
 * Mendukung filter 1 Periode (Standar) & filter 2 Periode (Perbandingan).
 */
$defaultStart = isset($start_date) && !empty($start_date) ? $start_date : date('Y-m-01');
$defaultEnd   = isset($end_date) && !empty($end_date) ? $end_date : date('Y-m-t');

$defaultP1Start = isset($p1_start) && !empty($p1_start) ? $p1_start : date('Y-m-01');
$defaultP1End   = isset($p1_end) && !empty($p1_end) ? $p1_end : date('Y-m-t');
$defaultP2Start = isset($p2_start) && !empty($p2_start) ? $p2_start : date('Y-m-01', strtotime('-1 month', strtotime($defaultP1Start)));
$defaultP2End   = isset($p2_end) && !empty($p2_end) ? $p2_end : date('Y-m-t', strtotime('-1 month', strtotime($defaultP1Start)));

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
        padding: 28px 36px 30px;
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
        min-width: 90px;
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
    .zahir-field-group {
        display: flex;
        flex-direction: column;
        gap: 20px;
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
                    <span class="zahir-tab-btn" id="zahirTabLabel">Standar</span>
                </div>
                <div class="zahir-tab-body">
                    <!-- MODE 1 PERIODE (STANDAR / DEFAULT) -->
                    <div id="zahirFilterSinglePeriod" class="zahir-filter-row">
                        <div class="zahir-filter-label">Tanggal :</div>
                        <div class="zahir-date-col">
                            <label for="zahir_start_date" class="zahir-sub-label">Dari :</label>
                            <input type="date" class="zahir-date-input" name="start_date" id="zahir_start_date" value="<?= $defaultStart ?>">
                        </div>
                        <div class="zahir-arrow-sep">&gt;</div>
                        <div class="zahir-date-col">
                            <label for="zahir_end_date" class="zahir-sub-label">Hingga :</label>
                            <input type="date" class="zahir-date-input" name="end_date" id="zahir_end_date" value="<?= $defaultEnd ?>">
                        </div>
                    </div>

                    <!-- MODE 2 PERIODE (PERBANDINGAN) -->
                    <div id="zahirFilterComparePeriod" class="zahir-field-group" style="display: none;">
                        <div class="zahir-filter-row">
                            <div class="zahir-filter-label">Periode 1 :</div>
                            <div class="zahir-date-col">
                                <label for="zahir_p1_start" class="zahir-sub-label">Dari :</label>
                                <input type="date" class="zahir-date-input" name="p1_start" id="zahir_p1_start" value="<?= $defaultP1Start ?>">
                            </div>
                            <div class="zahir-arrow-sep">&gt;</div>
                            <div class="zahir-date-col">
                                <label for="zahir_p1_end" class="zahir-sub-label">Hingga :</label>
                                <input type="date" class="zahir-date-input" name="p1_end" id="zahir_p1_end" value="<?= $defaultP1End ?>">
                            </div>
                        </div>

                        <div class="zahir-filter-row">
                            <div class="zahir-filter-label">Periode 2 :</div>
                            <div class="zahir-date-col">
                                <label for="zahir_p2_start" class="zahir-sub-label">Dari :</label>
                                <input type="date" class="zahir-date-input" name="p2_start" id="zahir_p2_start" value="<?= $defaultP2Start ?>">
                            </div>
                            <div class="zahir-arrow-sep">&gt;</div>
                            <div class="zahir-date-col">
                                <label for="zahir_p2_end" class="zahir-sub-label">Hingga :</label>
                                <input type="date" class="zahir-date-input" name="p2_end" id="zahir_p2_end" value="<?= $defaultP2End ?>">
                            </div>
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
    var singleBox = document.getElementById('zahirFilterSinglePeriod');
    var compareBox = document.getElementById('zahirFilterComparePeriod');
    var tabLabel = document.getElementById('zahirTabLabel');
    var isCompare = false;

    // Mapping target route berdasarkan type
    var targetUrl = '';
    if (customActionUrl) {
        targetUrl = customActionUrl;
    } else if (type === 'rincian') {
        targetUrl = '<?= base_url('laporan/keuangan/arus-kas-rincian') ?>';
    } else if (type === 'rangkuman') {
        targetUrl = '<?= base_url('laporan/keuangan/arus-kas-rangkuman') ?>';
    } else if (type === 'laba_rugi_standar' || type === 'laba_rugi') {
        targetUrl = '<?= base_url('laporan/keuangan/laba-rugi') ?>';
    } else if (type === 'laba_rugi_ytd') {
        targetUrl = '<?= base_url('laporan/keuangan/laba-rugi-ytd') ?>';
    } else if (type === 'laba_rugi_anggaran') {
        targetUrl = '<?= base_url('laporan/keuangan/laba-rugi-anggaran') ?>';
    } else if (type === 'laba_rugi_perbandingan_2') {
        targetUrl = '<?= base_url('laporan/keuangan/laba-rugi-perbandingan-2') ?>';
        isCompare = true;
    } else if (type === 'laba_rugi_perbandingan_4') {
        targetUrl = '<?= base_url('laporan/keuangan/laba-rugi-perbandingan-4') ?>';
        isCompare = true;
    } else {
        targetUrl = '<?= base_url('laporan/keuangan/arus-kas-rangkuman') ?>';
    }

    form.action = targetUrl;

    if (isCompare) {
        singleBox.style.display = 'none';
        compareBox.style.display = 'flex';
        tabLabel.textContent = 'Perbandingan';
        // Nonaktifkan input single agar tidak terkirim di query string
        document.getElementById('zahir_start_date').disabled = true;
        document.getElementById('zahir_end_date').disabled = true;
        document.getElementById('zahir_p1_start').disabled = false;
        document.getElementById('zahir_p1_end').disabled = false;
        document.getElementById('zahir_p2_start').disabled = false;
        document.getElementById('zahir_p2_end').disabled = false;
    } else {
        singleBox.style.display = 'flex';
        compareBox.style.display = 'none';
        tabLabel.textContent = 'Standar';
        document.getElementById('zahir_start_date').disabled = false;
        document.getElementById('zahir_end_date').disabled = false;
        document.getElementById('zahir_p1_start').disabled = true;
        document.getElementById('zahir_p1_end').disabled = true;
        document.getElementById('zahir_p2_start').disabled = true;
        document.getElementById('zahir_p2_end').disabled = true;
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
