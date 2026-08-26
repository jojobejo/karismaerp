<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title><?= html_escape($page_title ?? 'Faktur Penjualan Loby') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/adminlte.min.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            background: #f8f9fa;
            padding: 20px;
        }
        .invoice-card {
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 24px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .invoice-header {
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .company-title {
            font-size: 20px;
            font-weight: 800;
            color: #1788b8;
            margin-bottom: 2px;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: 800;
            text-align: right;
            text-transform: uppercase;
        }
        .table-invoice {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .table-invoice th, .table-invoice td {
            border: 1px solid #333;
            padding: 6px 8px;
            font-size: 11px;
        }
        .table-invoice th {
            background: #f2f2f2;
            text-align: center;
            font-weight: 700;
        }
        .signature-box {
            text-align: center;
            height: 70px;
        }
        .no-print-bar {
            max-width: 800px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
        }
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .invoice-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar no-print">
        <button onclick="window.close()" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Tutup</button>
        <button onclick="window.print()" class="btn btn-sm btn-primary font-weight-bold"><i class="fas fa-print mr-1"></i> Cetak Faktur (Print)</button>
    </div>

    <div class="invoice-card">
        <!-- HEADER -->
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-6">
                    <div class="company-title">KARISMA ERP</div>
                    <div class="text-muted small">Penjualan Langsung Loby (Direct Cash)</div>
                </div>
                <div class="col-6 text-right">
                    <div class="invoice-title text-primary">FAKTUR PENJUALAN</div>
                    <div class="font-weight-bold">No. Faktur: <?= html_escape($faktur['no_faktur']) ?></div>
                    <div class="small text-muted">Tanggal: <?= date('d/m/Y', strtotime($faktur['tanggal_faktur'])) ?></div>
                </div>
            </div>
        </div>

        <!-- INFO CUSTOMER & SO -->
        <div class="row mb-3">
            <div class="col-6">
                <table class="table-sm table-borderless p-0 m-0" style="font-size: 11.5px;">
                    <tr>
                        <td class="text-muted p-0" style="width: 100px;">Customer</td>
                        <td class="font-weight-bold p-0">: <?= html_escape($faktur['customer_name'] ?: $faktur['nama_customer']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted p-0">Kode Customer</td>
                        <td class="p-0">: <?= html_escape($faktur['kd_customer']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted p-0">Alamat / Kios</td>
                        <td class="p-0">: <?= html_escape($faktur['nama_kios'] ?? '-') ?> (<?= html_escape($faktur['alamat_kios'] ?? '-') ?>)</td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table-sm table-borderless p-0 m-0" style="font-size: 11.5px;">
                    <tr>
                        <td class="text-muted p-0" style="width: 110px;">No. Referensi SO</td>
                        <td class="font-weight-bold p-0">: <?= html_escape($faktur['no_so']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted p-0">Metode Bayar</td>
                        <td class="font-weight-bold text-success p-0">: CASH (Lunas / Tunai)</td>
                    </tr>
                    <tr>
                        <td class="text-muted p-0">Petugas Loby</td>
                        <td class="p-0">: <?= html_escape($faktur['create_by']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ITEMS TABLE -->
        <table class="table-invoice">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th style="width: 80px;">Lot / Exp</th>
                    <th style="width: 60px;">Qty</th>
                    <th style="width: 50px;">Satuan</th>
                    <th style="width: 90px;">Harga Satuan</th>
                    <th style="width: 50px;">Disc</th>
                    <th style="width: 100px;">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grandTotal = 0;
                $totalQty = 0;
                $no = 1;
                foreach ($details as $d): 
                    $grandTotal += (float)$d['total_harga'];
                    $totalQty += (float)$d['qty'];
                ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><strong><?= html_escape($d['kd_barang']) ?></strong></td>
                        <td><?= html_escape($d['nama_barang'] ?: ($d['master_nama_barang'] ?? '')) ?></td>
                        <td class="text-center">
                            <small><?= html_escape($d['no_lot'] ?: '-') ?><br><?= html_escape($d['expired_date'] ?: '') ?></small>
                        </td>
                        <td class="text-right font-weight-bold"><?= number_format((float)$d['qty'], 2, ',', '.') ?></td>
                        <td class="text-center"><?= html_escape($d['satuan']) ?></td>
                        <td class="text-right"><?= number_format((float)$d['hrg_satuan'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= (float)$d['disc'] > 0 ? (float)$d['disc'] . '%' : '-' ?></td>
                        <td class="text-right font-weight-bold"><?= number_format((float)$d['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">TOTAL QTY:</th>
                    <th class="text-right"><?= number_format($totalQty, 2, ',', '.') ?></th>
                    <th colspan="3" class="text-right font-weight-bold">TOTAL PEMBAYARAN (CASH):</th>
                    <th class="text-right font-weight-bold" style="font-size: 13px;">Rp <?= number_format($grandTotal, 0, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>

        <!-- CATATAN -->
        <div class="mb-4">
            <small class="text-muted"><strong>Catatan:</strong> <?= html_escape($faktur['catatan'] ?: 'Barang telah diterima langsung oleh customer dalam kondisi baik dan lengkap.') ?></small>
        </div>

        <!-- TANDA TANGAN -->
        <div class="row text-center mt-4">
            <div class="col-4">
                <div>Diterima Oleh,</div>
                <div class="signature-box"></div>
                <div class="font-weight-bold">( <?= html_escape($faktur['customer_name'] ?: $faktur['nama_customer']) ?> )</div>
                <div class="small text-muted">Customer / Pembeli</div>
            </div>
            <div class="col-4">
                <div>Diserahkan Oleh,</div>
                <div class="signature-box"></div>
                <div class="font-weight-bold">( <?= html_escape($faktur['create_by']) ?> )</div>
                <div class="small text-muted">Petugas Loby</div>
            </div>
            <div class="col-4">
                <div>Kasir / Keuangan,</div>
                <div class="signature-box"></div>
                <div class="font-weight-bold">( ......................... )</div>
                <div class="small text-muted">Penerima Kas</div>
            </div>
        </div>
    </div>

</body>
</html>
