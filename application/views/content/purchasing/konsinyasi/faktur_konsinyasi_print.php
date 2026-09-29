<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'FAKTUR REALISASI PENJUALAN KONSINYASI') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background-color: #f1f5f9;
        }
        .print-container {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px 35px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border-radius: 8px;
        }
        .no-print-toolbar {
            max-width: 820px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-print {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #0369a1;
        }
        .btn-back {
            background-color: #e2e8f0;
            color: #334155;
        }
        .btn-back:hover {
            background-color: #cbd5e1;
        }

        /* HEADER */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .company-info h2 {
            margin: 0 0 3px 0;
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .company-info p {
            margin: 0;
            font-size: 11px;
            color: #475569;
            line-height: 1.4;
        }
        .doc-title-box {
            text-align: right;
        }
        .doc-title {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: #0284c7;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
        }
        .doc-no {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 4px;
        }

        /* METADATA GRID */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 18px;
        }
        .meta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
        }
        .meta-box h4 {
            margin: 0 0 8px 0;
            font-size: 11px;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            font-weight: 700;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2.5px 0;
            font-size: 11.5px;
            vertical-align: top;
        }
        .meta-table td.label {
            width: 38%;
            color: #64748b;
        }
        .meta-table td.sep {
            width: 4%;
            text-align: center;
            color: #64748b;
        }
        .meta-table td.val {
            font-weight: 600;
            color: #0f172a;
        }

        /* TABEL BARANG */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .items-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            padding: 8px 10px;
            font-size: 11.5px;
            border: 1px solid #0f172a;
        }
        .items-table td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            font-size: 11.5px;
            vertical-align: middle;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: 700;
        }

        /* REKAP & PERHITUNGAN TAGIHAN */
        .calculation-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .notice-card {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #2563eb;
            border-radius: 6px;
            padding: 12px 14px;
            font-size: 11.5px;
            color: #1e3a8a;
            line-height: 1.5;
        }
        .notice-card strong {
            color: #1e40af;
            display: block;
            margin-bottom: 4px;
            font-size: 12px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 4px 6px;
            font-size: 11.5px;
        }
        .totals-table tr.grand-total td {
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            font-weight: 800;
            font-size: 13px;
            color: #0f172a;
            padding: 6px;
            background: #f8fafc;
        }

        /* SIGNATURE */
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 11.5px;
        }
        .sign-space {
            height: 65px;
        }
        .sign-name {
            font-weight: 700;
            border-top: 1px solid #0f172a;
            display: inline-block;
            min-width: 160px;
            padding-top: 4px;
        }
        .sign-role {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .print-container {
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

    <div class="no-print-toolbar no-print">
        <a href="<?= site_url('purchasing/konsinyasi?tab=penyelesaian') ?>" class="btn-action btn-back">
            &larr; Kembali ke Modul Konsinyasi
        </a>
        <button class="btn-action btn-print" onclick="window.print();">
            &#128438; Cetak Faktur / Simpan PDF
        </button>
    </div>

    <div class="print-container">

        <!-- HEADER -->
        <div class="header-section">
            <div class="company-info">
                <h2>PT. KARISMA INDOARGO UNIVERSAL</h2>
                <p>Jl. Raya Karisma No. 88, Sidoarjo - Jawa Timur</p>
                <p>Telp: (031) 8988888 | Email: purchasing@kiu.co.id</p>
            </div>
            <div class="doc-title-box">
                <div class="doc-title">Laporan Realisasi Penjualan Konsinyasi</div>
                <div class="doc-subtitle">Bukti Barang Konsinyasi Laku Terjual (Dasar Faktur Pembelian)</div>
                <div class="doc-no">No: <?= htmlspecialchars($settlement['no_settlement'] ?? '-') ?></div>
            </div>
        </div>

        <!-- METADATA GRID -->
        <div class="meta-grid">
            <!-- Data Supplier Konsinyor -->
            <div class="meta-box">
                <h4>Ditujukan Kepada (Supplier Konsinyor):</h4>
                <table class="meta-table">
                    <tr>
                        <td class="label">Nama Supplier</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($settlement['nama_suplier'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Kode Supplier</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($settlement['kd_suplier'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Gudang Penyimpanan</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($settlement['nama_gudang'] ?? 'Gudang Konsinyasi') ?></td>
                    </tr>
                    <?php if (!empty($settlement['nomor_lpb_asal'])): ?>
                    <tr>
                        <td class="label">Ref. LPB Masuk</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($settlement['nomor_lpb_asal']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Data Realisasi Penjualan di Kios -->
            <div class="meta-box">
                <h4>Informasi Penjualan &amp; Pelunasan:</h4>
                <table class="meta-table">
                    <tr>
                        <td class="label">Tanggal Laporan</td>
                        <td class="sep">:</td>
                        <td class="val"><?= date('d F Y', strtotime($settlement['tanggal_settlement'])) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Kios / Customer</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($settlement['customer_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">No. Faktur Titipan</td>
                        <td class="sep">:</td>
                        <td class="val" style="color: #0284c7;"><?= htmlspecialchars($settlement['no_faktur'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">No. Sales Order</td>
                        <td class="sep">:</td>
                        <td class="val"><?= htmlspecialchars($settlement['no_so'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Status Barang</td>
                        <td class="sep">:</td>
                        <td class="val" style="color: #16a34a;">RESMI TERJUAL / DIBELI KIOS</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- TABEL RINCIAN BARANG -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 14%;">Kode Barang</th>
                    <th style="width: 32%;">Nama Barang Titipan</th>
                    <th style="width: 14%;">No. Lot / ED</th>
                    <th style="width: 10%;" class="text-right">Qty Laku</th>
                    <th style="width: 12%;" class="text-right">Harga Jual</th>
                    <th style="width: 14%;" class="text-right">Total Realisasi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $qty_laku = (float)($settlement['qty_net'] ?? $settlement['qty_terjual']);
                $hrg_jual = (float)($settlement['hrg_jual'] ?? 0);
                $total_jual = (float)($settlement['subtotal_jual'] ?? ($qty_laku * $hrg_jual));
                ?>
                <tr>
                    <td class="text-center">1</td>
                    <td class="font-bold"><?= htmlspecialchars($settlement['kd_barang']) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($settlement['nama_barang']) ?></strong>
                    </td>
                    <td class="text-center">
                        <small>Lot: <?= htmlspecialchars($settlement['no_lot'] ?: '-') ?><br>ED: <?= !empty($settlement['expired_date']) ? date('d/m/Y', strtotime($settlement['expired_date'])) : '-' ?></small>
                    </td>
                    <td class="text-right font-bold" style="font-size: 12.5px;">
                        <?= number_format($qty_laku, 2) ?> <?= htmlspecialchars($settlement['satuan'] ?? 'Btl') ?>
                    </td>
                    <td class="text-right">
                        Rp <?= number_format($hrg_jual, 2, ',', '.') ?>
                    </td>
                    <td class="text-right font-bold" style="color: #0f172a;">
                        Rp <?= number_format($total_jual, 2, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- PERHITUNGAN TAGIHAN BELI & CATATAN -->
        <div class="calculation-grid">
            <div class="notice-card">
                <strong>Catatan Resmi untuk Supplier (<?= htmlspecialchars($settlement['nama_suplier']) ?>):</strong>
                Dokumen ini merupakan laporan resmi atas realisasi barang konsinyasi yang telah laku terjual dan dilunasi oleh pihak kios.
                <br><br>
                Mohon pihak supplier dapat menerbitkan <strong>Faktur Penjualan / Invoice Tagihan Pembelian</strong> beserta <strong>Faktur Pajak</strong> sejumlah Qty yang terjual di atas (<strong><?= number_format($qty_laku, 2) ?> <?= htmlspecialchars($settlement['satuan']) ?></strong>) kepada <strong>PT. KARISMA INDOARGO UNIVERSAL</strong>.
            </div>

            <div>
                <?php
                $hrg_pokok_satuan = (float)($settlement['hrg_beli_satuan'] > 0 ? $settlement['hrg_beli_satuan'] : ($faktur_detail['hrg_pokok'] ?? 71000));
                $dpp_pokok = (float)($settlement['subtotal_beli'] > 0 ? $settlement['subtotal_beli'] : ($qty_laku * $hrg_pokok_satuan));
                $ppn_persen = (float)($settlement['ppn_persen'] > 0 ? $settlement['ppn_persen'] : 11.0);
                $ppn_nominal = (float)($settlement['nilai_ppn'] > 0 ? $settlement['nilai_ppn'] : round(($dpp_pokok * $ppn_persen) / 100, 2));
                $total_beli = (float)($settlement['total_tagihan_beli'] > 0 ? $settlement['total_tagihan_beli'] : ($dpp_pokok + $ppn_nominal));
                ?>
                <table class="totals-table">
                    <tr>
                        <td class="text-muted">Total Penjualan ke Kios</td>
                        <td class="text-right font-bold">Rp <?= number_format($total_jual, 2, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Estimasi Harga Beli Pokok / Satuan</td>
                        <td class="text-right">Rp <?= number_format($hrg_pokok_satuan, 2, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">DPP Tagihan Beli Supplier</td>
                        <td class="text-right font-bold">Rp <?= number_format($dpp_pokok, 2, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">PPN Masukan (<?= $ppn_persen ?>%)</td>
                        <td class="text-right">Rp <?= number_format($ppn_nominal, 2, ',', '.') ?></td>
                    </tr>
                    <tr class="grand-total">
                        <td>Estimasi Tagihan Supplier:</td>
                        <td class="text-right" style="color: #0284c7;">Rp <?= number_format($total_beli, 2, ',', '.') ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- TANDA TANGAN -->
        <table class="signature-table">
            <tr>
                <td>
                    Dibuat Oleh,
                    <div class="sign-space"></div>
                    <div class="sign-name"><?= htmlspecialchars($this->session->userdata('nama') ?: 'Staff Purchasing') ?></div>
                    <div class="sign-role">Purchasing / Keuangan</div>
                </td>
                <td>
                    Diketahui Oleh,
                    <div class="sign-space"></div>
                    <div class="sign-name">Accounting / Spv</div>
                    <div class="sign-role">PT. Karisma Indoargo Universal</div>
                </td>
                <td>
                    Diterima Oleh,
                    <div class="sign-space"></div>
                    <div class="sign-name"><?= htmlspecialchars($settlement['nama_suplier']) ?></div>
                    <div class="sign-role">Supplier Konsinyor</div>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
