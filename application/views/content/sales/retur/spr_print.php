<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPR — <?= htmlspecialchars($spr['no_spr']) ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; background: #fff; }
        .page { width: 210mm; min-height: 297mm; padding: 12mm 15mm; margin: 0 auto; }

        /* HEADER */
        .spr-header { display: flex; align-items: center; justify-content: space-between; border: 2px solid #000; padding: 8px 12px; margin-bottom: 0; }
        .spr-header-logo { display: flex; align-items: center; gap: 10px; }
        .spr-header-logo img { height: 44px; }
        .spr-header-logo .company-name { font-size: 9pt; line-height: 1.4; }
        .spr-header-title { text-align: center; flex: 1; }
        .spr-header-title h2 { font-size: 14pt; font-weight: 700; letter-spacing: 0.5px; }
        .spr-header-no { text-align: right; font-size: 9pt; }

        /* INFO TABLE */
        .info-table { width: 100%; border-collapse: collapse; border: 2px solid #000; border-top: none; }
        .info-table td { border: 1px solid #000; padding: 4px 8px; font-size: 10pt; }
        .info-table .lbl { font-weight: normal; width: 80px; text-align: center; background: #f5f5f5; }

        /* KALIMAT */
        .intro { font-size: 10pt; padding: 6px 0 4px 0; font-style: italic; }

        /* DETAIL TABLE */
        .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .detail-table th { border: 1px solid #000; padding: 5px 6px; text-align: center; font-size: 10pt; background: #e8e8e8; }
        .detail-table td { border: 1px solid #000; padding: 4px 6px; font-size: 10pt; vertical-align: top; }
        .detail-table td.center { text-align: center; }
        .detail-table td.right  { text-align: right; }
        .keterangan-cell { font-size: 9.5pt; }
        .keterangan-cell .chk-row { display: flex; align-items: flex-start; gap: 4px; line-height: 1.4; margin-bottom: 2px; }
        .chk-box { width: 12px; height: 12px; border: 1px solid #000; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; font-size: 10pt; }
        .chk-box.checked::after { content: "✓"; }
        .sub-radio { margin-left: 16px; font-size: 9pt; display: flex; gap: 12px; }
        .radio-box { display: inline-flex; align-items: center; gap: 3px; }
        .radio-circle { width: 10px; height: 10px; border-radius: 50%; border: 1px solid #000; display: inline-flex; align-items: center; justify-content: center; font-size: 8pt; }
        .radio-circle.checked::after { content: "●"; font-size: 7pt; }

        /* NOTE */
        .note-box { border: 1px solid #c0392b; padding: 8px 10px; margin-top: 8px; font-size: 10pt; color: #c0392b; }
        .note-box strong { color: #c0392b; }

        /* TANDA TANGAN */
        .ttd-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .ttd-table td { width: 25%; text-align: center; padding: 8px 4px; border: 1px solid #000; font-size: 10pt; vertical-align: top; }
        .ttd-name { font-weight: bold; margin-top: 50px; font-size: 10pt; border-top: 1px solid #000; padding-top: 4px; }

        /* APPROVAL HISTORY */
        .approval-section { margin-top: 12px; }
        .approval-section h4 { font-size: 10pt; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 2px; }
        .approval-row { display: flex; gap: 8px; font-size: 9.5pt; margin-bottom: 2px; }
        .approval-row .arl { width: 120px; font-weight: bold; }

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
        <button onclick="window.print()" style="padding:6px 18px; background:#c0392b; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px;">
            🖨 Print SPR
        </button>
        <button onclick="window.close()" style="padding:6px 18px; background:#6c757d; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:12px; margin-left:8px;">
            ✕ Tutup
        </button>
    </div>

    <!-- HEADER SURAT -->
    <div class="spr-header">
        <div class="spr-header-logo">
            <img src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo Karisma">
            <div class="company-name">
                <strong>PT. KARISMA INDOAGRO UNIVERSAL</strong><br>
                <small>Solusi Pertanian Terpadu</small>
            </div>
        </div>
        <div class="spr-header-title">
            <h2>SURAT PENGAJUAN RETUR BARANG</h2>
        </div>
        <div class="spr-header-no">
            No. SPR: <strong><?= htmlspecialchars($spr['no_spr']) ?></strong>
        </div>
    </div>

    <!-- INFO HEADER -->
    <table class="info-table">
        <tr>
            <td class="lbl">Tanggal</td>
            <td><?= date('d/m/Y', strtotime($spr['tanggal'])) ?></td>
            <td class="lbl">Nama Customer</td>
            <td><strong><?= htmlspecialchars($spr['nama_customer'] ?: ($spr['nama_customer_master'] ?? '-')) ?></strong></td>
            <td class="lbl">Alamat</td>
            <td><?= htmlspecialchars($spr['alamat'] ?: ($spr['alamat_master'] ?? '-')) ?></td>
            <td class="lbl">Sales</td>
            <td><?= htmlspecialchars($spr['nama_sales'] ?? '-') ?></td>
        </tr>
    </table>

    <!-- INTRO -->
    <p class="intro">Berikut ini adalah barang-barang yang kami ajukan untuk diretur, dengan rincian sbb:</p>

    <!-- DETAIL TABLE -->
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width:30px;">No.</th>
                <th>Nama Barang</th>
                <th style="width:100px;">No Faktur</th>
                <th style="width:100px;">No. Batch/No. Lot</th>
                <th style="width:50px;">Qty</th>
                <th style="min-width:200px;">Keterangan <small>(centang salah satu)</small></th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Pastikan setidaknya ada 8 baris (sesuai form fisik)
            $rows = $spr_detail;
            $min_rows = max(8, count($rows));
            for ($i = 0; $i < $min_rows; $i++):
                $d = $rows[$i] ?? null;
            ?>
            <tr>
                <td class="center"><?= $i + 1 ?></td>
                <td><?= $d ? htmlspecialchars($d['nama_barang'] ?? '') : '&nbsp;' ?></td>
                <td><?= $d ? htmlspecialchars($d['no_faktur'] ?? '') : '&nbsp;' ?></td>
                <td><?= $d ? htmlspecialchars($d['no_batch'] ?? '') : '&nbsp;' ?></td>
                <td class="right"><?= $d && $d['qty'] > 0 ? number_format((float)$d['qty'], 3) : '&nbsp;' ?></td>
                <td class="keterangan-cell">
                    <?php
                    $alasan_brg    = $d ? (int)$d['alasan_brg_bermasalah']    : 0;
                    $alasan_exp    = $d ? (int)$d['alasan_expired']            : 0;
                    $alasan_tdk    = $d ? (int)$d['alasan_tidak_laku']         : 0;
                    $alasan_tes    = $d ? (int)$d['alasan_tes_market']         : 0;
                    $alasan_bd     = $d ? (int)$d['alasan_bad_debt']           : 0;
                    $alasan_harga  = $d ? (int)$d['alasan_harga_tidak_sesuai'] : 0;
                    $alasan_spr    = $d ? (int)$d['alasan_spr_intern']         : 0;
                    $alasan_ll     = $d ? htmlspecialchars($d['alasan_lainlain'] ?? '') : '';
                    $brg_opt       = $d ? ($d['alasan_brg_bermasalah_opt'] ?? '') : '';
                    $exp_opt       = $d ? ($d['alasan_expired_opt'] ?? '') : '';
                    ?>
                    <div class="chk-row">
                        <span class="chk-box <?= $alasan_brg ? 'checked' : '' ?>"></span>
                        <span>Barang bermasalah retur ke pabrik (fail/daya tmbh/berkutu/benih pecah/kemasan rusak)</span>
                    </div>
                    <div class="sub-radio">
                        <span class="radio-box"><span class="radio-circle <?= ($brg_opt==='replace') ? 'checked' : '' ?>"></span> Replace</span>
                        <span class="radio-box"><span class="radio-circle <?= ($brg_opt==='not_replace') ? 'checked' : '' ?>"></span> Not Replace</span>
                    </div>
                    <div class="chk-row">
                        <span class="chk-box <?= $alasan_exp ? 'checked' : '' ?>"></span>
                        <span>Expired (2 bln sebelum tgl Exp utk benih &amp; 3 bln sebelum tgl exp utk pestisida)</span>
                    </div>
                    <div class="sub-radio">
                        <span class="radio-box"><span class="radio-circle <?= ($exp_opt==='replace') ? 'checked' : '' ?>"></span> Replace</span>
                        <span class="radio-box"><span class="radio-circle <?= ($exp_opt==='not_replace') ? 'checked' : '' ?>"></span> Not Replace</span>
                    </div>
                    <div class="chk-row"><span class="chk-box <?= $alasan_tdk ? 'checked' : '' ?>"></span><span>Barang tidak laku &amp; masuk OD</span></div>
                    <div class="chk-row"><span class="chk-box <?= $alasan_tes ? 'checked' : '' ?>"></span><span>Faktur T/Brg Tes Market</span></div>
                    <div class="chk-row"><span class="chk-box <?= $alasan_bd  ? 'checked' : '' ?>"></span><span>Potensi Bad Debt</span></div>
                    <div class="chk-row"><span class="chk-box <?= $alasan_harga? 'checked' : '' ?>"></span><span>Barang/Harga tdk sesuai Pesanan</span></div>
                    <div class="chk-row"><span class="chk-box <?= $alasan_spr ? 'checked' : '' ?>"></span><span>SPR Intern (brg Oper)</span></div>
                    <div class="chk-row"><span class="chk-box"></span><span>Lain-lain: <?= $alasan_ll ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
                </td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- CATATAN SC -->
    <?php if ($spr['catatan']): ?>
        <p style="font-size:10pt; margin-top:6px;"><strong>Catatan SC:</strong> <?= nl2br(htmlspecialchars($spr['catatan'])) ?></p>
    <?php endif; ?>

    <!-- NOTE MERAH -->
    <div class="note-box">
        <strong>Catatan:</strong><br>
        Barang yang kami retur sesuai dengan data di atas. Bilamana tidak sesuai, maka kami (toko) akan bertanggung jawab
        menerima konsekuensinya (retur ditolak) sesuai kebijakan PT Karisma Indoagro Universal.
    </div>

    <!-- TANDA TANGAN -->
    <table class="ttd-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Sales</strong>
                <div class="ttd-name"><?= htmlspecialchars($spr['nama_sales'] ?? '') ?></div>
            </td>
            <td>
                Diverifikasi,<br>
                <strong>Koor SC</strong>
                <div class="ttd-name"><?= htmlspecialchars($spr['koor_sc_by'] ?? '') ?></div>
            </td>
            <td>
                Dicek,<br>
                <strong>Admin Stock</strong>
                <div class="ttd-name"><?= htmlspecialchars($spr['admin_stock_by'] ?? '') ?></div>
            </td>
            <td>
                Disetujui,<br>
                <strong>Kadep SC</strong>
                <div class="ttd-name"><?= htmlspecialchars($spr['kadep_sc_by'] ?? '') ?></div>
            </td>
        </tr>
    </table>

    <!-- APPROVAL HISTORY -->
    <?php
    $has_history = $spr['koor_sc_by'] || $spr['admin_stock_by'] || $spr['kadep_sc_by'] || $spr['logistik_by'];
    if ($has_history):
    ?>
    <div class="approval-section">
        <h4>Riwayat Approval</h4>
        <?php if ($spr['koor_sc_by']): ?>
            <div class="approval-row">
                <span class="arl">Koor SC</span>
                <span><?= htmlspecialchars($spr['koor_sc_by']) ?> — <?= $spr['koor_sc_at'] ? date('d/m/Y H:i', strtotime($spr['koor_sc_at'])) : '-' ?>
                <?= $spr['koor_sc_catatan'] ? '| ' . htmlspecialchars($spr['koor_sc_catatan']) : '' ?></span>
            </div>
        <?php endif; ?>
        <?php if ($spr['admin_stock_by']): ?>
            <div class="approval-row">
                <span class="arl">Admin Stock</span>
                <span><?= htmlspecialchars($spr['admin_stock_by']) ?> — <?= $spr['admin_stock_at'] ? date('d/m/Y H:i', strtotime($spr['admin_stock_at'])) : '-' ?>
                <?= $spr['admin_stock_catatan'] ? '| ' . htmlspecialchars($spr['admin_stock_catatan']) : '' ?></span>
            </div>
        <?php endif; ?>
        <?php if ($spr['kadep_sc_by']): ?>
            <div class="approval-row">
                <span class="arl">Kadep SC</span>
                <span><?= htmlspecialchars($spr['kadep_sc_by']) ?> — <?= $spr['kadep_sc_at'] ? date('d/m/Y H:i', strtotime($spr['kadep_sc_at'])) : '-' ?>
                <?= $spr['kadep_sc_catatan'] ? '| ' . htmlspecialchars($spr['kadep_sc_catatan']) : '' ?></span>
            </div>
        <?php endif; ?>
        <?php if ($spr['logistik_by']): ?>
            <div class="approval-row">
                <span class="arl">Logistik</span>
                <span><?= htmlspecialchars($spr['logistik_by']) ?> — <?= $spr['logistik_at'] ? date('d/m/Y H:i', strtotime($spr['logistik_at'])) : '-' ?>
                <?= $spr['logistik_catatan'] ? '| ' . htmlspecialchars($spr['logistik_catatan']) : '' ?></span>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <p style="font-size:8pt; color:#888; margin-top:16px; text-align:right;">
        Dicetak oleh: <?= htmlspecialchars($user['nama'] ?? '-') ?> | <?= date('d/m/Y H:i') ?> | No. SPR: <?= htmlspecialchars($spr['no_spr']) ?>
    </p>
</div>
</body>
</html>
