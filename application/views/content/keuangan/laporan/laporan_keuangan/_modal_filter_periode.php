<?php
/**
 * _modal_filter_periode.php
 * Dialog "Pilih Periode Akuntansi" bergaya Zahir Accounting.
 * Digunakan sebelum membuka Laporan Neraca (Standar, Neraca Saldo, 12 Bulan, Perbandingan 2 Kolom, Anggaran vs Realisasi, Perbandingan 4 Kolom).
 */
$currentMonth = (int)date('n');
$currentYear  = (int)date('Y');

$months = [
    1  => 'January',
    2  => 'February',
    3  => 'March',
    4  => 'April',
    5  => 'May',
    6  => 'June',
    7  => 'July',
    8  => 'August',
    9  => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December',
];

$selP1Month = isset($p1_month) ? (int)$p1_month : (isset($month) ? (int)$month : 1);
$selP1Year  = isset($p1_year)  ? (int)$p1_year  : (isset($year)  ? (int)$year  : 2025);
$selP2Month = isset($p2_month) ? (int)$p2_month : 1;
$selP2Year  = isset($p2_year)  ? (int)$p2_year  : 2025;
?>
<style>
    /* Styling Dialog Zahir "Pilih Periode Akuntansi" */
    .zahir-periode-modal .modal-content {
        border-radius: 2px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        border: 1px solid #7f9db9;
        font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
        background: #f0f0f0;
        color: #000;
    }
    .zahir-periode-modal .modal-header {
        border-bottom: none;
        padding: 24px 32px 12px;
        background: #f0f0f0;
    }
    .zahir-periode-modal .modal-title {
        font-size: 26px;
        font-weight: 500;
        color: #1e293b;
        letter-spacing: -0.3px;
    }
    .zahir-periode-body {
        padding: 16px 32px 30px;
        background: #f0f0f0;
    }
    .zahir-periode-grid {
        display: grid;
        grid-template-columns: 140px 170px 110px;
        align-items: center;
        row-gap: 14px;
        column-gap: 12px;
    }
    .zahir-col-header {
        font-size: 13.5px;
        color: #334155;
        font-weight: 500;
        margin-bottom: 2px;
    }
    .zahir-row-label {
        font-size: 14px;
        color: #1e293b;
        font-weight: 500;
        white-space: nowrap;
    }
    .zahir-select {
        height: 28px;
        font-size: 13.5px;
        color: #000;
        background-color: #fff;
        border: 1px solid #7f9db9;
        border-radius: 0;
        padding: 2px 6px;
        outline: none;
        width: 100%;
        box-sizing: border-box;
    }
    .zahir-select:focus {
        border-color: #008cba;
        box-shadow: 0 0 2px rgba(0, 140, 186, 0.8);
    }
    .zahir-select:disabled {
        background-color: #e2e8f0;
        color: #94a3b8;
        border-color: #cbd5e1;
    }
    .zahir-periode-modal .modal-footer {
        border-top: none;
        padding: 16px 32px 24px;
        background: #f0f0f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .zahir-btn-action {
        background-color: #00779e;
        color: #ffffff !important;
        border: 1px solid #005a78;
        border-radius: 0;
        padding: 5px 28px;
        font-size: 13.5px;
        font-weight: 500;
        min-width: 85px;
        text-align: center;
        cursor: pointer;
        outline: none;
        transition: background-color 0.1s ease;
    }
    .zahir-btn-action:hover {
        background-color: #006080;
    }
    .zahir-btn-action u {
        text-decoration: underline;
    }
</style>

<!-- MODAL PILIH PERIODE AKUNTANSI -->
<div class="modal fade zahir-periode-modal" id="modalPeriodeFilter" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Periode Akuntansi</h5>
            </div>
            <form id="formPeriodeFilter" method="get" action="<?= base_url('laporan/keuangan/neraca') ?>">
                <div class="zahir-periode-body">
                    <div class="zahir-periode-grid">
                        <!-- Header Kolom -->
                        <div></div>
                        <div class="zahir-col-header">Bulan :</div>
                        <div class="zahir-col-header">Tahun :</div>

                        <!-- Baris Periode 1 -->
                        <div class="zahir-row-label">Periode 1 :</div>
                        <div>
                            <select name="p1_month" id="filter_p1_month" class="zahir-select">
                                <?php foreach ($months as $mNum => $mName): ?>
                                    <option value="<?= $mNum ?>" <?= ($mNum === $selP1Month) ? 'selected' : '' ?>><?= $mName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <select name="p1_year" id="filter_p1_year" class="zahir-select">
                                <?php for ($y = 2020; $y <= 2035; $y++): ?>
                                    <option value="<?= $y ?>" <?= ($y === $selP1Year) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Baris Periode 2 -->
                        <div class="zahir-row-label" id="label_periode_2">Periode 2 :</div>
                        <div>
                            <select name="p2_month" id="filter_p2_month" class="zahir-select">
                                <?php foreach ($months as $mNum => $mName): ?>
                                    <option value="<?= $mNum ?>" <?= ($mNum === $selP2Month) ? 'selected' : '' ?>><?= $mName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <select name="p2_year" id="filter_p2_year" class="zahir-select">
                                <?php for ($y = 2020; $y <= 2035; $y++): ?>
                                    <option value="<?= $y ?>" <?= ($y === $selP2Year) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="zahir-btn-action" data-dismiss="modal" id="btnPeriodeBatal"><u>B</u>atal</button>
                    <button type="submit" class="zahir-btn-action" id="btnPeriodeOk"><u>O</u>K</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPeriodeFilter(reportType, customActionUrl) {
    var form = document.getElementById('formPeriodeFilter');
    var p2Month = document.getElementById('filter_p2_month');
    var p2Year  = document.getElementById('filter_p2_year');
    var p2Label = document.getElementById('label_periode_2');

    var targetUrl = '';
    var isCompare = false;

    if (customActionUrl) {
        targetUrl = customActionUrl;
        if (reportType === 'perbandingan_2' || reportType === 'perbandingan_4' || reportType.indexOf('perbandingan') !== -1) {
            isCompare = true;
        }
    } else if (reportType === 'neraca_standar' || reportType === 'standar') {
        targetUrl = '<?= base_url('laporan/keuangan/neraca') ?>';
    } else if (reportType === 'neraca_saldo' || reportType === 'saldo') {
        targetUrl = '<?= base_url('laporan/keuangan/neraca-saldo') ?>';
    } else if (reportType === 'neraca_12_bulan' || reportType === '12_bulan') {
        targetUrl = '<?= base_url('laporan/keuangan/neraca-12-bulan') ?>';
    } else if (reportType === 'neraca_perbandingan_2' || reportType === 'perbandingan_2') {
        targetUrl = '<?= base_url('laporan/keuangan/neraca-perbandingan-2') ?>';
        isCompare = true;
    } else if (reportType === 'neraca_anggaran' || reportType === 'anggaran') {
        targetUrl = '<?= base_url('laporan/keuangan/neraca-anggaran') ?>';
    } else if (reportType === 'neraca_perbandingan_4' || reportType === 'perbandingan_4') {
        targetUrl = '<?= base_url('laporan/keuangan/neraca-perbandingan-4') ?>';
        isCompare = true;
    } else {
        targetUrl = '<?= base_url('laporan/keuangan/neraca') ?>';
    }

    form.action = targetUrl;

    if (isCompare) {
        p2Month.disabled = false;
        p2Year.disabled  = false;
        p2Label.style.opacity = '1';
    } else {
        // Pada laporan 1 periode, baris periode 2 tetap terlihat seperti di dialog Zahir asli
        // namun disabled agar tidak terkirim atau membingungkan user
        p2Month.disabled = true;
        p2Year.disabled  = true;
        p2Label.style.opacity = '0.5';
    }

    $('#modalPeriodeFilter').modal('show');
}

// Shortcut keyboard Alt+B (Batal) dan Alt+O (OK)
document.addEventListener('keydown', function(e) {
    if ($('#modalPeriodeFilter').hasClass('show')) {
        if (e.altKey && (e.key === 'b' || e.key === 'B')) {
            e.preventDefault();
            $('#modalPeriodeFilter').modal('hide');
        } else if (e.altKey && (e.key === 'o' || e.key === 'O')) {
            e.preventDefault();
            document.getElementById('formPeriodeFilter').submit();
        }
    }
});
</script>

