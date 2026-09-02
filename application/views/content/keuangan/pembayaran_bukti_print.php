<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'BUKTI PEMBAYARAN FAKTUR') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background-color: #f8fafc;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border-radius: 6px;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .company-info h2 {
            margin: 0 0 4px 0;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .company-info p {
            margin: 0;
            font-size: 11px;
            color: #475569;
        }
        .doc-title-box {
            text-align: right;
        }
        .doc-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #0284c7;
            text-transform: uppercase;
        }
        .doc-no {
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-top: 3px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 4px;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .status-posted {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .status-unpost {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .meta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
        }
        .meta-box h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 4px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 0;
            font-size: 12px;
            vertical-align: top;
        }
        .meta-table td.label {
            width: 38%;
            color: #475569;
        }
        .meta-table td.sep {
            width: 4%;
            text-align: center;
        }
        .meta-table td.val {
            font-weight: 600;
            color: #0f172a;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .payment-table th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            padding: 9px 12px;
            text-align: left;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-size: 12px;
        }
        .payment-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .payment-table tr.total-row td {
            font-weight: 700;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            background: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        .terbilang-card {
            background: #f1f5f9;
            border-left: 4px solid #0284c7;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 12px;
            font-style: italic;
            color: #334155;
        }
        .terbilang-card strong {
            font-style: normal;
            color: #0f172a;
        }

        .summary-box {
            margin-bottom: 30px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
        }
        .summary-item .s-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
        }
        .summary-item .s-val {
            font-size: 15px;
            font-weight: 700;
            margin-top: 2px;
        }
        .val-primary { color: #0284c7; }
        .val-success { color: #16a34a; }
        .val-danger { color: #dc2626; }

        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 12px;
        }
        .sign-space {
            height: 70px;
        }
        .sign-name {
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
        }
        .sign-role {
            font-size: 11px;
            color: #64748b;
        }

        .btn-print-box {
            margin-bottom: 20px;
            text-align: right;
        }
        .btn-print {
            background-color: #0284c7;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-print:hover {
            background-color: #0369a1;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-container {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .btn-print-box {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="btn-print-box">
            <button class="btn-print" onclick="window.print();">
                &#128438; Cetak Sekarang
            </button>
        </div>

        <!-- HEADER PERUSAHAAN & DOKUMEN -->
        <div class="header-section">
            <div class="company-info">
                <h2>PT. KARISMA INDOARGO UNIVERSAL</h2>
                <p>Jl. Raya Karisma No. 88, Sidoarjo - Jawa Timur</p>
                <p>Telp: (031) 8988888 | Email: finance@kiu.co.id</p>
            </div>
            <div class="doc-title-box">
                <div class="doc-title">Bukti Pembayaran Faktur</div>
                <div class="doc-no">No. Kwitansi: <strong>#PAY-<?= sprintf('%05d', (int)$payment['id_pembayaran']) ?></strong></div>
                <?php
                $status_posting = strtoupper((string)($payment['status'] ?? 'POSTED'));
                $badge_class = ($status_posting === 'UNPOST') ? 'status-unpost' : 'status-posted';
                ?>
                <div>
                    <span class="status-badge <?= $badge_class ?>">
                        Status: <?= htmlspecialchars($status_posting) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- METADATA GRID -->
        <div class="meta-grid">
            <!-- Data Customer -->
            <div class="meta-box">
                <h4>Informasi Customer</h4>
                <table class="meta-table">
                    <tr>
                        <td class="label">Nama Customer</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($payment['nama_customer'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Kode Customer</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($payment['kd_customer'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Alamat</td>
                        <td class="sep">:</td>
                        <td class="val"><small><?= htmlspecialchars($payment['alamat_customer'] ?? '-') ?></small></td>
                    </tr>
                    <tr>
                        <td class="label">No. Telepon</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($payment['no_telp'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>

            <!-- Data Faktur & Transaksi -->
            <div class="meta-box">
                <h4>Informasi Transaksi</h4>
                <table class="meta-table">
                    <tr>
                        <td class="label">No. Faktur</td>
                        <td class="sep">:</td>
                        <td class="val" style="color: #0284c7;"><?= htmlspecialchars($payment['no_faktur'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">No. SO</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($payment['no_so'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Bayar</td>
                        <td class="sep">:</td>
                        <td class="val"><?= !empty($payment['tanggal_pembayaran']) ? date('d/m/Y', strtotime($payment['tanggal_pembayaran'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="label">Metode Bayar</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($payment['metode_pembayaran'] ?? '-') ?></td>
                    </tr>
                    <?php if (!empty($payment['nomor_jurnal'])): ?>
                    <tr>
                        <td class="label">No. Jurnal Ref</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($payment['nomor_jurnal']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($payment['no_bg'])): ?>
                    <tr>
                        <td class="label">No. BG / Cek</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($payment['no_bg']) ?> (Bank: <?= htmlspecialchars($payment['nama_bank'] ?? '-') ?>)</td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- RINCIAN PEMBAYARAN TABLE -->
        <table class="payment-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 55%;">Keterangan / Deskripsi Pembayaran</th>
                    <th style="width: 20%;" class="text-right">Potongan / Diskon</th>
                    <th style="width: 20%;" class="text-right">Jumlah Bayar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $jumlah_bayar = (float)($payment['jumlah_pembayaran'] ?? 0);
                $jumlah_diskon = (float)($payment['jumlah_diskon'] ?? 0);
                $total_settled = $jumlah_bayar + $jumlah_diskon;
                $ket_bayar = !empty($payment['keterangan']) 
                    ? $payment['keterangan'] 
                    : 'Pembayaran Faktur ' . ($payment['no_faktur'] ?? '') . ' via ' . ($payment['metode_pembayaran'] ?? '-');
                ?>
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <strong><?= htmlspecialchars($ket_bayar) ?></strong>
                        <?php if (!empty($payment['nama_bank'])): ?>
                            <br><small class="text-muted">Bank: <?= htmlspecialchars($payment['nama_bank']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">
                        <?= $jumlah_diskon > 0 ? 'Rp ' . number_format($jumlah_diskon, 2, ',', '.') : '-' ?>
                    </td>
                    <td class="text-right font-weight-bold">
                        Rp <?= number_format($jumlah_bayar, 2, ',', '.') ?>
                    </td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-right">TOTAL PENERIMAAN / PELUNASAN :</td>
                    <td class="text-right" style="color: #0284c7; font-size: 14px;">
                        Rp <?= number_format($total_settled, 2, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- TERBILANG -->
        <div class="terbilang-card">
            <strong>Terbilang:</strong> <?= htmlspecialchars($terbilang ?? '-') ?>
        </div>

        <!-- RINGKASAN STATUS FAKTUR -->
        <?php if (isset($payment['total_tagihan'])): ?>
        <div class="summary-box">
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="s-label">Total Nilai Faktur</div>
                    <div class="s-val val-primary">Rp <?= number_format((float)$payment['total_tagihan'], 0, ',', '.') ?></div>
                </div>
                <div class="summary-item">
                    <div class="s-label">Total Telah Dibayar (Kumulatif)</div>
                    <div class="s-val val-success">Rp <?= number_format((float)$payment['total_pembayaran_kumulatif'], 0, ',', '.') ?></div>
                </div>
                <div class="summary-item">
                    <div class="s-label">Sisa Piutang Faktur</div>
                    <div class="s-val val-danger">Rp <?= number_format((float)$payment['sisa_tagihan'], 0, ',', '.') ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- TANDA TANGAN -->
        <table class="signature-table">
            <tr>
                <td>
                    Dibuat Oleh,
                    <div class="sign-space"></div>
                    <div class="sign-name"><?= htmlspecialchars($payment['create_by'] ?? 'Kasir / Keuangan') ?></div>
                    <div class="sign-role">Kasir / Bagian Keuangan</div>
                </td>
                <td>
                    Mengetahui,
                    <div class="sign-space"></div>
                    <div class="sign-name">____________________</div>
                    <div class="sign-role">Kepala Bagian Keuangan</div>
                </td>
                <td>
                    Diterima Dari,
                    <div class="sign-space"></div>
                    <div class="sign-name"><?= htmlspecialchars($payment['nama_customer'] ?? 'Penyetor / Customer') ?></div>
                    <div class="sign-role">Customer / Kolektor</div>
                </td>
            </tr>
        </table>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
