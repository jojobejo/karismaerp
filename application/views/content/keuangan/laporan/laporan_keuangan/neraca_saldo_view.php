<?php
/**
 * neraca_saldo_view.php
 * Tampilan Laporan Neraca Saldo (Trial Balance) bergaya Zahir Accounting.
 * Menampilkan:
 * 1. Kode Rekening (Kode & Nama Akun)
 * 2. Saldo Awal (Debet & Kredit)
 * 3. Transaksi Bulan Ini (Debet & Kredit)
 * 4. Saldo Akhir (Debet & Kredit)
 * Baris Total: Total Debet vs Kredit
 */
$back_url = base_url('laporan/keuangan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <style>
        .ns-page-container {
            font-family: 'Segoe UI', Tahoma, Helvetica, Arial, sans-serif;
            background: #eef2f6;
            min-height: 100vh;
            padding: 24px 16px 48px;
            color: #1e293b;
        }
        .ns-toolbar {
            max-width: 1100px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .ns-sheet {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            padding: 36px 48px 48px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            border-radius: 4px;
            position: relative;
        }
        .ns-timestamp {
            font-size: 11px;
            color: #64748b;
            position: absolute;
            top: 24px;
            left: 48px;
        }
        .ns-header-center {
            text-align: center;
            margin-bottom: 24px;
        }
        .ns-company {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .ns-title {
            font-size: 24px;
            font-weight: 700;
            color: #008cba;
            margin: 4px 0 2px;
        }
        .ns-period {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-top: 4px;
        }
        .ns-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            line-height: 1.45;
        }
        .ns-table thead th {
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            background-color: #f1f5f9;
            text-align: center;
            white-space: nowrap;
        }
        .ns-table thead th.th-left {
            text-align: left;
        }
        .ns-table tbody td {
            padding: 3px 8px;
            vertical-align: middle;
            border-left: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }
        .ns-table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }
        .ns-table tbody tr:hover {
            background-color: #f1f5f9;
        }
        .ns-code {
            width: 85px;
            color: #334155;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .ns-name {
            color: #1e293b;
        }
        .ns-amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            color: #0f172a;
            min-width: 95px;
        }
        .ns-total-row td {
            border-top: 2px solid #008cba !important;
            border-bottom: 2px solid #008cba !important;
            font-weight: 700;
            background-color: #f8fafc !important;
            padding: 8px 8px !important;
            font-size: 12px;
        }
        .ns-total-label {
            font-weight: 700;
            color: #0f172a;
        }
        .ns-footer {
            margin-top: 32px;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
        }

        /* Print Mode */
        @media print {
            body { background: #fff !important; }
            .ns-page-container { background: #fff !important; padding: 0 !important; }
            .ns-toolbar { display: none !important; }
            .ns-sheet {
                box-shadow: none !important;
                padding: 10mm 15mm !important;
                max-width: 100% !important;
            }
            .sidebar, .main-header, .navbar, .main-footer { display: none !important; }
        }
    </style>
</head>
<body>
<div class="content-wrapper" style="margin-left: 0 !important; background: #eef2f6;">
    <div class="ns-page-container">
        <!-- Action Toolbar -->
        <div class="ns-toolbar">
            <div class="d-flex align-items-center gap-2">
                <a href="<?= $back_url ?>" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <span class="badge badge-info p-2" style="font-size: 13px;">
                    <i class="fas fa-calendar-alt mr-1"></i> <?= htmlspecialchars($period_label) ?>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-info btn-sm mr-2" onclick="openPeriodeFilter('neraca_saldo')">
                    <i class="fas fa-filter mr-1"></i> Filter Data
                </button>
                <a href="<?= $export_url ?>" class="btn btn-success btn-sm mr-2">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Cetak / PDF
                </button>
            </div>
        </div>

        <!-- Sheet Canvas -->
        <div class="ns-sheet">
            <div class="ns-timestamp"><?= $print_time ?></div>

            <div class="ns-header-center">
                <div class="ns-company"><?= htmlspecialchars($company_name) ?></div>
                <div class="ns-title"><?= htmlspecialchars($report_title) ?></div>
                <div class="ns-period"><?= htmlspecialchars($period_label) ?></div>
            </div>

            <table class="ns-table">
                <thead>
                    <tr>
                        <th rowspan="2" colspan="2" class="th-left" style="vertical-align: middle;">Kode Rekening</th>
                        <th colspan="2">Saldo Awal</th>
                        <th colspan="2">Transaksi Bulan Ini</th>
                        <th colspan="2">Saldo Akhir</th>
                    </tr>
                    <tr>
                        <th class="ns-amount" style="text-align: center;">Debet</th>
                        <th class="ns-amount" style="text-align: center;">Kredit</th>
                        <th class="ns-amount" style="text-align: center;">Debet</th>
                        <th class="ns-amount" style="text-align: center;">Kredit</th>
                        <th class="ns-amount" style="text-align: center;">Debet</th>
                        <th class="ns-amount" style="text-align: center;">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $fmt = function($v) {
                        return abs($v) > 0.0001 ? number_format((float)$v, 2, '.', ',') : '';
                    };
                    ?>
                    <?php if (!empty($data['items'])): ?>
                        <?php foreach ($data['items'] as $it): ?>
                            <tr>
                                <td class="ns-code"><?= htmlspecialchars($it['kode_tampil']) ?></td>
                                <td class="ns-name"><?= htmlspecialchars($it['nama_akun']) ?></td>
                                <td class="ns-amount"><?= $fmt($it['awal_debet']) ?></td>
                                <td class="ns-amount"><?= $fmt($it['awal_kredit']) ?></td>
                                <td class="ns-amount"><?= $fmt($it['mutasi_debet']) ?></td>
                                <td class="ns-amount"><?= $fmt($it['mutasi_kredit']) ?></td>
                                <td class="ns-amount"><?= $fmt($it['akhir_debet']) ?></td>
                                <td class="ns-amount"><?= $fmt($it['akhir_kredit']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada data transaksi atau akun pada periode ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="ns-total-row">
                        <td colspan="2" class="ns-total-label">Total:</td>
                        <td class="ns-amount" style="color: #0284c7; font-weight: bold;"><?= number_format($data['grand_total']['awal_debet'], 2, '.', ',') ?></td>
                        <td class="ns-amount" style="color: #0284c7; font-weight: bold;"><?= number_format($data['grand_total']['awal_kredit'], 2, '.', ',') ?></td>
                        <td class="ns-amount" style="color: #0284c7; font-weight: bold;"><?= number_format($data['grand_total']['mutasi_debet'], 2, '.', ',') ?></td>
                        <td class="ns-amount" style="color: #0284c7; font-weight: bold;"><?= number_format($data['grand_total']['mutasi_kredit'], 2, '.', ',') ?></td>
                        <td class="ns-amount" style="color: #0284c7; font-weight: bold;"><?= number_format($data['grand_total']['akhir_debet'], 2, '.', ',') ?></td>
                        <td class="ns-amount" style="color: #0284c7; font-weight: bold;"><?= number_format($data['grand_total']['akhir_kredit'], 2, '.', ',') ?></td>
                    </tr>
                </tfoot>
            </table>

            <div class="ns-footer">
                <span>Halaman : 1</span>
                <span>Dicetak: <?= $print_time ?></span>
            </div>
        </div>
    </div>
</div>

<?php 
// Include modal filter Zahir "Pilih Periode Akuntansi"
include APPPATH . 'views/content/keuangan/laporan/laporan_keuangan/_modal_filter_periode.php'; 
?>
</body>
</html>

