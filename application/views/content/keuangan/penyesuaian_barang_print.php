<!-- application/views/content/keuangan/penyesuaian_barang_print.php -->
<!-- Layout cetak bukti transaksi penyesuaian barang -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Penyesuaian Barang - <?= htmlspecialchars($header['no_referensi']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
            background: #fff;
        }
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #127fad;
            padding-bottom: 12px;
        }
        .print-header h2 {
            font-size: 18px;
            color: #127fad;
            margin-bottom: 4px;
        }
        .print-header p {
            font-size: 11px;
            color: #666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 16px;
        }
        .info-table td {
            padding: 3px 8px;
            font-size: 12px;
        }
        .info-table .label {
            font-weight: 600;
            width: 130px;
            color: #475569;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .detail-table th {
            background: #127fad;
            color: #fff;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 500;
            text-align: left;
        }
        .detail-table td {
            padding: 6px 10px;
            font-size: 11px;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-table .number {
            text-align: right;
        }
        .footer-info {
            margin-top: 24px;
            display: flex;
            justify-content: space-between;
        }
        .footer-info .col {
            text-align: center;
            width: 30%;
        }
        .footer-info .col p {
            margin-top: 50px;
            border-top: 1px solid #333;
            padding-top: 4px;
            font-size: 11px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-posted { background: #d4edda; color: #155724; }
        .status-draft { background: #fff3cd; color: #856404; }

        @media print {
            body { padding: 10px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px; text-align:right;">
        <button onclick="window.print()" style="padding:8px 20px; background:#127fad; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:13px;">
            <i class="fas fa-print"></i> Cetak
        </button>
        <button onclick="window.close()" style="padding:8px 20px; background:#95a5a6; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:13px; margin-left:8px;">
            Tutup
        </button>
    </div>

    <div class="print-header">
        <h2>PENYESUAIAN PERSEDIAAN</h2>
        <p>Karisma Indoagro Universal, PT</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">No. Referensi</td>
            <td>: <?= htmlspecialchars($header['no_referensi']) ?></td>
            <td class="label">Status</td>
            <td>: <span class="status-badge <?= $header['status'] === 'POSTED' ? 'status-posted' : 'status-draft' ?>"><?= $header['status'] ?></span></td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td>: <?= date('d/m/Y', strtotime($header['tanggal'])) ?></td>
            <td class="label">Total Nilai</td>
            <td>: Rp <?= number_format($header['total_nilai'], 2, ',', '.') ?></td>
        </tr>
        <tr>
            <td class="label">Keterangan</td>
            <td colspan="3">: <?= htmlspecialchars($header['keterangan']) ?></td>
        </tr>
        <tr>
            <td class="label">Dari Gudang</td>
            <td>: <?= htmlspecialchars($header['gudang_dari'] ?? '-') ?></td>
            <td class="label">Ke Gudang</td>
            <td>: <?= htmlspecialchars($header['gudang_ke'] ?? '-') ?></td>
        </tr>
    </table>

    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th style="width:140px">Kode</th>
                <th>Nama Barang</th>
                <th style="width:100px" class="number">Jumlah</th>
                <th style="width:80px">Satuan</th>
                <th style="width:150px">Akun</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($header['details'])): ?>
                <?php foreach ($header['details'] as $i => $d): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($d['kd_barang']) ?></td>
                    <td><?= htmlspecialchars($d['nm_barang']) ?></td>
                    <td class="number"><?= number_format($d['jumlah'], 3, ',', '.') ?></td>
                    <td><?= htmlspecialchars($d['satuan']) ?></td>
                    <td><?= htmlspecialchars(($d['kode_akun'] ?? '') . ($d['nama_akun'] ? ' - ' . $d['nama_akun'] : '')) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">Tidak ada detail baris</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer-info">
        <div class="col">
            <p>Dibuat oleh</p>
        </div>
        <div class="col">
            <p>Diketahui oleh</p>
        </div>
        <div class="col">
            <p>Disetujui oleh</p>
        </div>
    </div>
</body>
</html>
