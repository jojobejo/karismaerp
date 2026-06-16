<!-- views/content/sales/admin_sc_faktur_print_all.php -->
<?php
$total_qty = 0;
$total_pajak = 0;
$grand_total = 0;
?>
<style>
    .route-print-wrap {
        background: #fff;
        color: #000;
        padding: 16px;
    }
    .route-print-actions {
        margin-bottom: 12px;
    }
    .route-print-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid #000;
        padding-bottom: 8px;
        margin-bottom: 12px;
    }
    .route-print-title {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0;
    }
    .route-print-meta {
        text-align: right;
        font-size: 12px;
    }
    .route-print-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #000;
        font-size: 10.5px;
    }
    .route-print-table th,
    .route-print-table td {
        border: 0;
        border-right: 1px solid #000;
        border-bottom: 1px solid #000;
        padding: 4px 5px;
        vertical-align: top;
    }
    .route-print-table th:last-child,
    .route-print-table td:last-child {
        border-right: 0;
    }
    .route-print-table tfoot tr:last-child th {
        border-bottom: 0;
    }
    .route-print-table th {
        background: #e9ecef;
    }
    @media print {
        @page {
            size: A4;
            margin: 10mm;
        }
        body {
            background: #fff !important;
            color: #000 !important;
            font-size: 11px;
        }
        .main-header,
        .main-sidebar,
        .main-footer,
        .control-sidebar,
        .preloader,
        .no-print {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
            min-height: 0 !important;
            background: #fff !important;
        }
        .route-print-wrap {
            padding: 0;
        }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <section class="content">
            <div class="route-print-wrap">
                <div class="route-print-actions no-print">
                    <a href="<?= base_url('sales_order/admin_sc/faktur?rute=' . rawurlencode($selected_rute)) ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-info btn-sm" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Cetak
                    </button>
                </div>

                <div class="route-print-header">
                    <div>
                        <div class="route-print-title">DAFTAR FAKTUR PENJUALAN</div>
                        <div><strong>PT. Karisma Indoargo Universal</strong></div>
                        <div>Rute: <strong><?= htmlspecialchars($selected_rute) ?></strong></div>
                    </div>
                    <div class="route-print-meta">
                        <div>Tanggal Cetak: <?= date('d/m/Y H:i') ?></div>
                        <div>Total Faktur: <strong><?= count($print_items) ?></strong></div>
                    </div>
                </div>

                <table class="route-print-table">
                    <thead>
                        <tr>
                            <th style="width:34px;">No</th>
                            <th>No Faktur</th>
                            <th>No SO</th>
                            <th>Tgl Faktur</th>
                            <th>Customer</th>
                            <th>Pembayaran</th>
                            <th>Tempo</th>
                            <th style="text-align:right;">Qty</th>
                            <th style="text-align:right;">Pajak</th>
                            <th style="text-align:right;">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($print_items as $idx => $item):
                            $faktur = $item['faktur'];
                            $qty = (float)($faktur['total_qty'] ?? 0);
                            $pajak = (float)($faktur['total_pajak'] ?? 0);
                            $total = (float)($faktur['grand_total'] ?? 0);
                            $total_qty += $qty;
                            $total_pajak += $pajak;
                            $grand_total += $total;
                            $payment = strtolower(trim((string)($faktur['cara_pembayaran'] ?? '')));
                            $payment_label = [
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                                'tempo' => 'Tempo',
                                'bg' => 'BG',
                                'bonus' => 'Bonus',
                            ][$payment] ?? ($payment !== '' ? strtoupper($payment) : '-');
                            $tempo = $faktur['jtempo'] ?? $faktur['tempo'] ?? null;
                        ?>
                            <tr>
                                <td style="text-align:center;"><?= $idx + 1 ?></td>
                                <td><strong><?= htmlspecialchars($faktur['no_faktur'] ?? '-') ?></strong></td>
                                <td><?= htmlspecialchars($faktur['no_so'] ?? '-') ?></td>
                                <td><?= !empty($faktur['tanggal_faktur']) ? date('d/m/Y', strtotime($faktur['tanggal_faktur'])) : '-' ?></td>
                                <td><?= htmlspecialchars($faktur['customer_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($payment_label) ?></td>
                                <td>
                                    <?= ($tempo !== null && $tempo !== '') ? (int)$tempo . ' Hari' : '-' ?>
                                    <?php if (!empty($faktur['tanggal_jatuh_tempo'])): ?>
                                        <br><small><?= date('d/m/Y', strtotime($faktur['tanggal_jatuh_tempo'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;"><?= number_format($qty, 2) ?></td>
                                <td style="text-align:right;">Rp <?= number_format($pajak, 0, ',', '.') ?></td>
                                <td style="text-align:right;"><strong>Rp <?= number_format($total, 0, ',', '.') ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" style="text-align:right;">TOTAL</th>
                            <th style="text-align:right;"><?= number_format($total_qty, 2) ?></th>
                            <th style="text-align:right;">Rp <?= number_format($total_pajak, 0, ',', '.') ?></th>
                            <th style="text-align:right;">Rp <?= number_format($grand_total, 0, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    setTimeout(function(){ window.print(); }, 500);
});
</script>
