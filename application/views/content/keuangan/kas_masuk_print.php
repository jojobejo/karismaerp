<!-- application/views/content/keuangan/kas_masuk_print.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KAS MASUK — <?= htmlspecialchars($header['no_referensi']) ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            color: #000;
            padding: 20px;
            line-height: 1.4;
        }
        .header-print {
            text-align: center;
            border-bottom: 2px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-print h2 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-table td {
            padding: 4px;
            vertical-align: top;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .detail-table th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .detail-table td {
            padding: 8px;
            border-bottom: 1px dotted #ccc;
        }
        .detail-table tr.total-row td {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }
        .terbilang-box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 30px;
            font-style: italic;
            font-weight: bold;
        }
        .signature-section {
            width: 100%;
            margin-top: 50px;
        }
        .signature-section td {
            text-align: center;
            width: 33%;
        }
        @media print {
            body {
                padding: 0;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print();">
    <div class="btn-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print();" style="padding: 6px 15px; cursor: pointer;">Cetak Sekarang</button>
    </div>

    <div class="header-print">
        <h2>PT. KARISMA INDOARGO UNIVERSAL</h2>
        <div style="font-size: 12px;">BUKTI PENERIMAAN KAS / BANK (KAS MASUK)</div>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 15%;">No. Referensi</td>
            <td style="width: 2%;">:</td>
            <td style="width: 43%; font-weight: bold;"><?= htmlspecialchars($header['no_referensi']) ?></td>
            <td style="width: 15%;">Tanggal</td>
            <td style="width: 2%;">:</td>
            <td style="width: 23%;"><?= date('d/m/Y', strtotime($header['tanggal'])) ?></td>
        </tr>
        <tr>
            <td>Simpan Ke</td>
            <td>:</td>
            <td><?= htmlspecialchars($header['kode_akun_kas'] . ' - ' . $header['nama_akun_kas']) ?></td>
            <td>Giro / Cek</td>
            <td>:</td>
            <td><?= $header['is_giro'] ? 'Ya' : 'Tidak' ?></td>
        </tr>
        <tr>
            <td>Disetor Oleh</td>
            <td>:</td>
            <td colspan="4"><strong><?= htmlspecialchars($header['diterima_dari']) ?></strong></td>
        </tr>
        <tr>
            <td>Memo / Catatan</td>
            <td>:</td>
            <td colspan="4"><?= htmlspecialchars($header['memo']) ?></td>
        </tr>
    </table>

    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 20%;">Kode Akun</th>
                <th style="width: 55%;">Nama Akun / Klasifikasi</th>
                <th style="width: 25%; text-align: right;">Nilai (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($header['details'] as $detail): ?>
                <tr>
                    <td><?= htmlspecialchars($detail['kode_akun']) ?></td>
                    <td><?= htmlspecialchars($detail['nama_akun']) ?></td>
                    <td style="text-align: right;"><?= number_format($detail['nilai'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">Total Penerimaan :</td>
                <td style="text-align: right;">Rp <?= number_format($header['total_amount'], 2, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <div class="terbilang-box">
        Terbilang: # <?= htmlspecialchars($terbilang) ?> #
    </div>

    <table class="signature-section">
        <tr>
            <td>Disetujui Oleh,</td>
            <td>Diterima Oleh,</td>
            <td>Disetor Oleh,</td>
        </tr>
        <tr style="height: 80px;">
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>( ____________________ )</td>
            <td>( ____________________ )</td>
            <td>( ____________________ )</td>
        </tr>
    </table>
</body>
</html>
