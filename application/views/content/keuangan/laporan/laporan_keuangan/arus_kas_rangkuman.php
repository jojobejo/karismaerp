<?php
/**
 * Laporan Arus Kas - Rangkuman
 * Sesuai format Zahir Accounting Karisma.
 */
$back_url   = base_url('laporan/keuangan');
$page_title = 'Laporan Arus Kas (Rangkuman)';
?>
<style>
    .ak-page {
        font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
        color: #222;
        background: #f4f6f9;
        min-height: 100vh;
        padding-bottom: 50px;
    }
    .ak-toolbar {
        background: #fff;
        border-radius: 8px;
        padding: 12px 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .ak-toolbar .btn {
        font-weight: 600;
        font-size: 13px;
    }
    /* Report Sheet (Kertas Laporan) */
    .ak-sheet {
        background: #fff;
        max-width: 860px;
        margin: 0 auto;
        padding: 40px 55px 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #dbe2e8;
        border-radius: 4px;
        position: relative;
    }
    .ak-timestamp {
        font-size: 11px;
        color: #333;
        margin-bottom: 15px;
    }
    .ak-header-center {
        text-align: center;
        margin-bottom: 35px;
    }
    .ak-company {
        font-size: 18px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #111;
        margin-bottom: 2px;
    }
    .ak-title {
        font-size: 26px;
        font-weight: 800;
        color: #1788b8;
        margin-bottom: 4px;
    }
    .ak-period {
        font-size: 14px;
        font-weight: 700;
        color: #111;
    }
    /* Table layout */
    .ak-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        line-height: 1.6;
    }
    .ak-table td {
        padding: 4px 6px;
        vertical-align: middle;
    }
    .ak-sec-header {
        font-size: 14px;
        font-weight: 700;
        color: #1788b8;
        padding-top: 14px !important;
        padding-bottom: 4px !important;
    }
    .ak-item-title {
        padding-left: 35px !important;
        color: #222;
    }
    .ak-top-title {
        padding-left: 20px !important;
        color: #222;
        font-weight: 500;
    }
    .ak-amount {
        text-align: right;
        font-variant-numeric: tabular-nums;
        color: #222;
        width: 210px;
    }
    .ak-subtotal-row td {
        font-weight: 700;
        color: #1788b8;
        padding-top: 6px;
        padding-bottom: 12px;
    }
    .ak-subtotal-row .ak-amount {
        border-top: 1px solid #777;
    }
    /* Bottom Totals */
    .ak-bottom-box {
        margin-top: 25px;
    }
    .ak-total-label {
        font-weight: 700;
        color: #1788b8;
        font-size: 13.5px;
        padding: 5px 6px;
    }
    .ak-total-curr {
        width: 35px;
        text-align: right;
        font-weight: 700;
        color: #1788b8;
        padding: 5px 6px;
    }
    .ak-total-val {
        width: 180px;
        text-align: right;
        font-weight: 700;
        color: #1788b8;
        font-variant-numeric: tabular-nums;
        padding: 5px 6px;
    }
    .ak-border-top {
        border-top: 1px solid #777;
    }
    .ak-page-footer {
        border-top: 1.5px solid #222;
        margin-top: 40px;
        padding-top: 8px;
        font-size: 12px;
        color: #333;
    }

    /* Print Styles */
    @media print {
        body, html {
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .main-header, .main-sidebar, .main-footer, .ak-toolbar, .content-header, .preloader {
            display: none !important;
        }
        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        .ak-sheet {
            max-width: 100% !important;
            border: none !important;
            box-shadow: none !important;
            padding: 10mm 15mm !important;
            margin: 0 !important;
        }
        .ak-page {
            background: #fff !important;
            padding: 0 !important;
        }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse ak-page">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar'); ?>
    <?php $this->load->view('partial/main/sidebar'); ?>

    <div class="content-wrapper">
        <section class="content-header pt-2 pb-1">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-file-invoice-dollar mr-2 text-primary"></i> <?= $page_title ?></h5>
                    <ol class="breadcrumb m-0 float-sm-right bg-transparent p-0" style="font-size: 13px;">
                        <li class="breadcrumb-item"><a href="<?= base_url('laporan') ?>">Laporan</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('laporan/keuangan') ?>">Keuangan</a></li>
                        <li class="breadcrumb-item active">Arus Kas (Rangkuman)</li>
                    </ol>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Action Toolbar -->
                <div class="ak-toolbar">
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= $back_url ?>" class="btn btn-secondary btn-sm mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <span class="badge badge-info p-2" style="font-size: 13px;">
                            <i class="fas fa-calendar-alt mr-1"></i> <?= date('d M Y', strtotime($start_date)) ?> s/d <?= date('d M Y', strtotime($end_date)) ?>
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-info btn-sm mr-2" onclick="openZahirFilter('rangkuman')">
                            <i class="fas fa-filter mr-1"></i> Filter Data
                        </button>
                        <a href="<?= base_url('laporan/keuangan/arus-kas-export/rangkuman?start_date=' . $start_date . '&end_date=' . $end_date) ?>" class="btn btn-success btn-sm mr-2">
                            <i class="fas fa-file-excel mr-1"></i> Excel
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak / PDF
                        </button>
                    </div>
                </div>

                <!-- Report Canvas Sheet -->
                <div class="ak-sheet">
                    <div class="ak-timestamp"><?= $print_time ?></div>

                    <div class="ak-header-center">
                        <div class="ak-company"><?= htmlspecialchars($company_name) ?></div>
                        <div class="ak-title"><?= htmlspecialchars($report_title) ?></div>
                        <div class="ak-period"><?= date('d F Y', strtotime($start_date)) ?> - <?= date('d F Y', strtotime($end_date)) ?></div>
                    </div>

                    <table class="ak-table">
                        <tbody>
                            <!-- Top Items (Biaya Dibayar Dimuka, Biaya Adm & Umum) -->
                            <?php if (!empty($data['top_items'])): ?>
                                <?php foreach ($data['top_items'] as $cat): ?>
                                    <tr>
                                        <td class="ak-top-title"><?= htmlspecialchars($cat['name']) ?></td>
                                        <td class="ak-amount"><?= number_format($cat['total'], 2, '.', ',') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td class="ak-top-title">Biaya Dibayar Dimuka</td>
                                    <td class="ak-amount">0.00</td>
                                </tr>
                                <tr>
                                    <td class="ak-top-title">Biaya Adm & Umum</td>
                                    <td class="ak-amount">0.00</td>
                                </tr>
                            <?php endif; ?>

                            <!-- Operating Activities -->
                            <tr>
                                <td colspan="2" class="ak-sec-header">Operating Activities</td>
                            </tr>
                            <?php if (!empty($data['operating'])): ?>
                                <?php foreach ($data['operating'] as $cat): ?>
                                    <tr>
                                        <td class="ak-item-title"><?= htmlspecialchars($cat['name']) ?></td>
                                        <td class="ak-amount"><?= number_format($cat['total'], 2, '.', ',') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td class="ak-item-title text-muted" style="font-style: italic;">Tidak ada transaksi aktivitas operasi</td>
                                    <td class="ak-amount">0.00</td>
                                </tr>
                            <?php endif; ?>
                            <tr class="ak-subtotal-row">
                                <td class="ak-item-title" style="padding-left: 0 !important;">Total Operating Activities :</td>
                                <td class="ak-amount"><?= number_format($data['total_operating'], 2, '.', ',') ?></td>
                            </tr>

                            <!-- Investing Activities -->
                            <tr>
                                <td colspan="2" class="ak-sec-header">Investing Activities</td>
                            </tr>
                            <?php if (!empty($data['investing'])): ?>
                                <?php foreach ($data['investing'] as $cat): ?>
                                    <tr>
                                        <td class="ak-item-title"><?= htmlspecialchars($cat['name']) ?></td>
                                        <td class="ak-amount"><?= number_format($cat['total'], 2, '.', ',') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td class="ak-item-title text-muted" style="font-style: italic;">Tidak ada transaksi aktivitas investasi</td>
                                    <td class="ak-amount">0.00</td>
                                </tr>
                            <?php endif; ?>
                            <tr class="ak-subtotal-row">
                                <td class="ak-item-title" style="padding-left: 0 !important;">Total Investing Activities :</td>
                                <td class="ak-amount"><?= number_format($data['total_investing'], 2, '.', ',') ?></td>
                            </tr>

                            <!-- Financing Activities -->
                            <tr>
                                <td colspan="2" class="ak-sec-header">Financing Activities</td>
                            </tr>
                            <?php if (!empty($data['financing'])): ?>
                                <?php foreach ($data['financing'] as $cat): ?>
                                    <tr>
                                        <td class="ak-item-title"><?= htmlspecialchars($cat['name']) ?></td>
                                        <td class="ak-amount"><?= number_format($cat['total'], 2, '.', ',') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td class="ak-item-title text-muted" style="font-style: italic;">Tidak ada transaksi aktivitas pendanaan</td>
                                    <td class="ak-amount">0.00</td>
                                </tr>
                            <?php endif; ?>
                            <tr class="ak-subtotal-row">
                                <td class="ak-item-title" style="padding-left: 0 !important;">Total Financing Activities :</td>
                                <td class="ak-amount"><?= number_format($data['total_financing'], 2, '.', ',') ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Cash Reconciliation / Saldo Kas -->
                    <table class="ak-table ak-bottom-box">
                        <tbody>
                            <tr>
                                <td class="ak-total-label">Total Keluar/Masuk Kas :</td>
                                <td class="ak-total-curr">Rp</td>
                                <td class="ak-total-val"><?= number_format($data['total_keluar_masuk'], 2, '.', ',') ?></td>
                            </tr>
                            <tr>
                                <td class="ak-total-label">Saldo Awal :</td>
                                <td class="ak-total-curr">Rp</td>
                                <td class="ak-total-val"><?= number_format($data['saldo_awal'], 2, '.', ',') ?></td>
                            </tr>
                            <tr>
                                <td class="ak-total-label">Saldo Akhir :</td>
                                <td class="ak-total-curr ak-border-top">Rp</td>
                                <td class="ak-total-val ak-border-top"><?= number_format($data['saldo_akhir'], 2, '.', ',') ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="ak-page-footer">
                        Halaman : 1
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php 
$filter_action_url = base_url('laporan/keuangan/arus-kas-rangkuman');
include APPPATH . 'views/content/keuangan/laporan/laporan_keuangan/_modal_filter_zahir.php'; 
?>
</body>
