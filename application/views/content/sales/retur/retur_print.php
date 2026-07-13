<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retur Penjualan — <?= htmlspecialchars($retur['no_retur']) ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; background: #fff; }
        .page { width: 210mm; min-height: 297mm; padding: 12mm 15mm; margin: 0 auto; }

        /* HEADER */
        .header-box { display: flex; align-items: center; justify-content: space-between; border: 2px solid #000; padding: 8px 12px; margin-bottom: 0; }
        .header-logo { display: flex; align-items: center; gap: 10px; }
        .header-logo img { height: 44px; }
        .header-logo .company-name { font-size: 9pt; line-height: 1.4; }
        .header-title { text-align: center; flex: 1; }
        .header-title h2 { font-size: 11pt; font-weight: 700; letter-spacing: 0.5px; white-space: nowrap; margin: 0; }
        .header-no { text-align: right; font-size: 9pt; }

        /* INFO TABLE */
        .info-table { width: 100%; border-collapse: collapse; border: 2px solid #000; border-top: none; }
        .info-table td { border: 1px solid #000; padding: 4px 8px; font-size: 10pt; }
        .info-table .lbl { font-weight: normal; width: 80px; text-align: center; background: #f5f5f5; }

        /* INTRO */
        .intro { font-size: 10pt; padding: 6px 0 4px 0; font-style: italic; }

        /* DETAIL TABLE */
        .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .detail-table th { border: 1px solid #000; padding: 5px 6px; text-align: center; font-size: 10pt; background: #e8e8e8; }
        .detail-table td { border: 1px solid #000; padding: 4px 6px; font-size: 10pt; vertical-align: top; }
        .detail-table td.center { text-align: center; }
        .detail-table td.right  { text-align: right; }

        /* TANDA TANGAN */
        .ttd-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .ttd-table td { width: 25%; text-align: center; padding: 8px 4px; border: 1px solid #000; font-size: 10pt; vertical-align: top; }
        .ttd-name { font-weight: bold; margin-top: 50px; font-size: 10pt; border-top: 1px solid #000; padding-top: 4px; }

        /* APPROVAL HISTORY */
        .approval-section { margin-top: 12px; }
        .approval-section h4 { font-size: 10pt; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 2px; }
        .approval-row { display: flex; gap: 8px; font-size: 9.5pt; margin-bottom: 2px; }
        .approval-row .arl { width: 150px; font-weight: bold; }

        @media print {
            body { margin: 0; }
            .page { padding: 8mm 10mm; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="page">

    <!-- TOMBOL PRINT -->
    <div class="no-print" style="text-align:right; margin-bottom:10px;">
        <button onclick="window.print()" style="padding:6px 18px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
            🖨 Print Retur Penjualan
        </button>
        <button onclick="window.close()" style="padding:6px 18px; background:#6c757d; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px; margin-left:8px;">
            ✕ Tutup
        </button>
    </div>

    <!-- HEADER SURAT -->
    <div class="header-box">
        <div class="header-logo">
            <img src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo Karisma">
            <div class="company-name">
                <strong>PT. KARISMA INDOAGRO UNIVERSAL</strong><br>
                <small>Solusi Pertanian Terpadu</small>
            </div>
        </div>
        <div class="header-title">
            <h2>RETUR PENJUALAN</h2>
        </div>
        <div class="header-no">
            No. Retur: <strong><?= htmlspecialchars($retur['no_retur']) ?></strong>
        </div>
    </div>

    <!-- INFO HEADER -->
    <table class="info-table">
        <tr>
            <td class="lbl">Tanggal</td>
            <td><?= date('d/m/Y', strtotime($retur['tanggal_retur'])) ?></td>
            <td class="lbl">Dari SPR</td>
            <td><?= htmlspecialchars($retur['no_spr'] ?? '-') ?></td>
            <td class="lbl">Nama Customer</td>
            <td><strong><?= htmlspecialchars($retur['nama_customer'] ?: ($retur['nama_customer_master'] ?? '-')) ?></strong></td>
        </tr>
        <tr>
            <td class="lbl">Sales</td>
            <td><?= htmlspecialchars($retur['nama_sales'] ?? '-') ?></td>
            <td class="lbl">Alamat</td>
            <td colspan="3"><?= htmlspecialchars($retur['alamat'] ?: ($retur['alamat_master'] ?? '-')) ?></td>
        </tr>
    </table>

    <!-- INTRO -->
    <p class="intro">Rincian produk yang diproses retur penjualan:</p>

    <!-- DETAIL TABLE -->
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:30px;">No.</th>
                <th>Nama Barang</th>
                <th style="width:120px;">No Faktur</th>
                <th style="width:120px;">No. Batch/Lot</th>
                <th style="width:100px;">Exp. Date</th>
                <th style="width:60px;">Qty</th>
                <th style="width:100px;">Harga Satuan</th>
                <th style="width:120px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total = 0;
            foreach ($retur_detail as $i => $d): 
                $subtotal = (float)$d['qty_retur'] * (float)$d['harga_satuan'];
                $total += $subtotal;
            ?>
            <tr>
                <td class="center"><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($d['nama_barang'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['no_faktur'] ?? '') ?></td>
                <td><?= htmlspecialchars($d['no_batch'] ?? '') ?></td>
                <td class="center"><?= !empty($d['expired_date']) ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?></td>
                <td class="center"><?= number_format((float)$d['qty_retur'], 3) ?></td>
                <td class="right">Rp <?= number_format((float)$d['harga_satuan'], 0, ',', '.') ?></td>
                <td class="right">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="background:#f5f5f5; font-weight:bold;">
                <td colspan="7" class="right">TOTAL NILAI RETUR:</td>
                <td class="right">Rp <?= number_format($total, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <!-- CATATAN -->
    <?php if ($retur['catatan_logistik']): ?>
        <p style="font-size:10pt; margin-bottom:6px;"><strong>Catatan ADMLPB2:</strong> <?= nl2br(htmlspecialchars($retur['catatan_logistik'])) ?></p>
    <?php endif; ?>
    <?php if ($retur['no_faktur_potong']): ?>
        <p style="font-size:10pt; margin-bottom:6px; color:#0056b3;"><strong>No. Faktur Potong:</strong> <?= htmlspecialchars($retur['no_faktur_potong']) ?></p>
    <?php endif; ?>

    <!-- TANDA TANGAN -->
    <table class="ttd-table">
        <tr>
            <td>
                Dibuat Oleh,<br>
                <strong>ADMLPB2</strong>
                <div class="ttd-name"><?= htmlspecialchars($retur['create_by_retur'] ?? '') ?></div>
            </td>
            <td>
                Diverifikasi,<br>
                <strong>Admin Stock</strong>
                <div class="ttd-name"><?= htmlspecialchars($retur['admin_stock_by_retur'] ?? '') ?></div>
            </td>
            <td>
                Proses Piutang,<br>
                <strong>Collection</strong>
                <div class="ttd-name"><?= htmlspecialchars($retur['collection_by'] ?? '') ?></div>
            </td>
            <td>
                Selesai,<br>
                <strong>Kasir</strong>
                <div class="ttd-name"><?= htmlspecialchars($retur['kasir_by'] ?? '') ?></div>
            </td>
        </tr>
    </table>

    <!-- APPROVAL HISTORY -->
    <?php
    $has_history = $retur['admin_stock_by_retur'] || $retur['collection_by'] || $retur['kasir_by'];
    if ($has_history):
    ?>
    <div class="approval-section">
        <h4>Riwayat Approval Proses Retur</h4>
        <?php if ($retur['admin_stock_by_retur']): ?>
            <div class="approval-row">
                <span class="arl">Admin Stock</span>
                <span><?= htmlspecialchars($retur['admin_stock_by_retur']) ?> — <?= $retur['admin_stock_at_retur'] ? date('d/m/Y H:i', strtotime($retur['admin_stock_at_retur'])) : '-' ?>
                <?= $retur['catatan_admin_stock'] ? '| ' . htmlspecialchars($retur['catatan_admin_stock']) : '' ?></span>
            </div>
        <?php endif; ?>
        <?php if ($retur['collection_by']): ?>
            <div class="approval-row">
                <span class="arl">Collection (Faktur Potong)</span>
                <span><?= htmlspecialchars($retur['collection_by']) ?> — <?= $retur['collection_at'] ? date('d/m/Y H:i', strtotime($retur['collection_at'])) : '-' ?>
                <?= $retur['catatan_collection'] ? '| ' . htmlspecialchars($retur['catatan_collection']) : '' ?></span>
            </div>
        <?php endif; ?>
        <?php if ($retur['kasir_by']): ?>
            <div class="approval-row">
                <span class="arl">Kasir</span>
                <span><?= htmlspecialchars($retur['kasir_by']) ?> — <?= $retur['kasir_at'] ? date('d/m/Y H:i', strtotime($retur['kasir_at'])) : '-' ?>
                <?= $retur['catatan_kasir'] ? '| ' . htmlspecialchars($retur['catatan_kasir']) : '' ?></span>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <p style="font-size:8pt; color:#888; margin-top:16px; text-align:right;">
        Dicetak oleh: <?= htmlspecialchars($user['nama'] ?? '-') ?> | <?= date('d/m/Y H:i') ?> | No. Retur: <?= htmlspecialchars($retur['no_retur']) ?>
    </p>
</div>
</body>
</html>
