<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? ('Faktur - ' . ($faktur['no_faktur'] ?? ''))) ?></title>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f1f5f9;
            color: #000;
            margin: 0;
            padding: 20px;
            font-size: 11.5px;
            line-height: 1.35;
        }

        /* ACTION BAR (NO PRINT) */
        .no-print-bar {
            max-width: 920px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 10px 18px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 15px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-print {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #0369a1;
        }

        .btn-back {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-back:hover {
            background-color: #e2e8f0;
        }

        /* INVOICE CANVAS (ZAHIR STYLE) */
        .zahir-invoice-container {
            max-width: 920px;
            margin: 0 auto;
            background: #ffffff;
            padding: 22px 28px 30px 28px;
            border: 1px solid #64748b;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            min-height: 580px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* HEADER */
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 3px;
        }

        .doc-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #000000;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
        }

        .company-name {
            font-size: 14.5px;
            font-weight: 700;
            font-style: italic;
            color: #000000;
            text-transform: uppercase;
            text-align: right;
            margin: 0;
            padding: 0;
        }

        .divider-thick-top {
            border-top: 2.5px solid #000000;
            margin: 4px 0 10px 0;
        }

        /* METADATA GRID */
        .meta-grid {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 6px;
        }

        .meta-col {
            width: 49%;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 11px;
            color: #000;
        }

        .meta-table td.lbl {
            width: 85px;
            color: #000;
            font-weight: normal;
        }

        .meta-table td.sep {
            width: 12px;
            text-align: center;
        }

        .meta-table td.val {
            font-weight: 500;
            color: #000;
        }

        .divider-thin-meta {
            border-top: 1.5px solid #000000;
            margin: 6px 0 0 0;
        }

        /* ITEMS TABLE */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .table-items thead th {
            font-size: 11px;
            font-weight: 600;
            padding: 5px 6px;
            border-bottom: 1.5px solid #000000;
            color: #000;
            vertical-align: middle;
        }

        .table-items tbody td {
            font-size: 11px;
            padding: 4px 6px;
            vertical-align: middle;
            color: #000;
            border: none;
        }

        /* Zebra striping Zahir style */
        .table-items tbody tr.bg-soft {
            background-color: #eaf1f8 !important;
        }
        .table-items tbody tr.bg-white {
            background-color: #ffffff !important;
        }

        .col-item-name {
            display: flex;
            align-items: center;
        }
        .item-code {
            display: inline-block;
            width: 95px;
            font-weight: normal;
            flex-shrink: 0;
        }
        .item-name {
            font-weight: normal;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        /* MIN HEIGHT CONTAINER FOR TABLE */
        .table-scroll-container {
            min-height: 140px;
        }

        /* FOOTER SUMMARY & SPELL-OUT */
        .footer-summary-wrap {
            border-top: 2px solid #000000;
            padding-top: 6px;
            margin-top: 10px;
        }

        .summary-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .spelled-box {
            width: 58%;
            font-size: 11.5px;
            font-weight: 500;
            font-style: normal;
            padding-top: 2px;
            line-height: 1.35;
            color: #000;
        }

        .calc-box {
            width: 40%;
        }

        .calc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .calc-table td {
            padding: 2px 0;
            font-size: 11.5px;
            color: #000;
        }

        .calc-table td.lbl {
            text-align: right;
            padding-right: 14px;
            width: 55%;
        }

        .calc-table td.curr {
            width: 25px;
            text-align: left;
            font-style: italic;
            font-weight: bold;
        }

        .calc-table td.val {
            text-align: right;
            width: 40%;
        }

        .calc-table tr.row-total td {
            padding-top: 4px;
            font-size: 13.5px;
            font-weight: bold;
        }

        /* SIGNATURE SECTION */
        .signature-grid {
            display: flex;
            justify-content: space-between;
            margin-top: 38px;
            padding: 0 35px 5px 35px;
        }

        .sig-col {
            width: 260px;
            text-align: center;
        }

        .sig-title {
            font-size: 11.5px;
            font-weight: bold;
            color: #000;
        }
        .sig-title-corp {
            font-size: 11.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
        }

        .sig-space {
            height: 55px;
        }

        .sig-line {
            border-bottom: 1px solid #000000;
            width: 220px;
            margin: 0 auto;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .zahir-invoice-container {
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 12mm 15mm !important;
                margin: 0 !important;
            }

            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- ACTION BAR -->
    <div class="no-print-bar no-print">
        <div style="font-size: 13px; font-weight: bold; color: #1e293b;">
            <i class="fas fa-file-invoice"></i> Format Cetak Faktur Standar Zahir
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="<?= base_url('sales_order/detail_faktur/' . $faktur['id_faktur']) ?>" class="btn-action btn-back">
                &larr; Kembali ke Detail
            </a>
            <button onclick="window.print()" class="btn-action btn-print">
                &#128424; Cetak Dokumen
            </button>
        </div>
    </div>

    <?php
    $noFakturCetak = $faktur['no_faktur'] ?? '-';
    $noSOCetak     = $so['no_so'] ?? ($faktur['no_so'] ?? '-');
    $namaCustomer  = $faktur['customer_name'] ?? ($so['customer_name'] ?? 'Pelanggan New');
    $alamatCustomer= $faktur['alamat_kios'] ?? ($faktur['alamat'] ?? ($so['alamat_kios'] ?? ($so['alamat'] ?? 'Jawa Timur, Indonesia')));
    if (!empty($faktur['regional'])) {
        $alamatCustomer .= ' (' . $faktur['regional'] . ')';
    }

    $tglFaktur = !empty($faktur['tanggal_faktur']) ? $faktur['tanggal_faktur'] : date('Y-m-d');
    $tglFormatted = date('l, F d, Y', strtotime($tglFaktur));

    $keteranganFaktur = !empty($faktur['keterangan']) 
        ? $faktur['keterangan'] 
        : ('Penjualan, ' . $namaCustomer);

    $termBayar = !empty($faktur['cara_pembayaran']) 
        ? ucfirst(strtolower($faktur['cara_pembayaran'])) 
        : (!empty($so['cara_pembayaran']) ? ucfirst(strtolower($so['cara_pembayaran'])) : 'Cash/Tunai');

    $salesman = !empty($so['salesman']) 
        ? $so['salesman'] 
        : (!empty($faktur['create_by']) ? $faktur['create_by'] : (!empty($so['create_by']) ? $so['create_by'] : 'Eko'));

    $discFinal  = (float)($faktur['diskon_nominal'] ?? 0);
    $pajak      = (float)($faktur['ppn_nominal'] ?? 0);
    $grandTotal = (float)($faktur['total_tagihan'] ?? ($faktur['total_setelah_pajak'] ?? 0));
    $terbilangTeks = !empty($terbilang) ? $terbilang : 'Nol Rupiah';
    ?>

    <!-- INVOICE SHEET -->
    <div class="zahir-invoice-container">
        <div>
            <!-- HEADER -->
            <div class="header-top">
                <h1 class="doc-title">FAKTUR</h1>
                <div class="company-name">PT. KARISMA INDOARGO UNIVERSAL</div>
            </div>
            <div class="divider-thick-top"></div>

            <!-- METADATA 2 KOLOM -->
            <div class="meta-grid">
                <!-- Kolom Kiri -->
                <div class="meta-col">
                    <table class="meta-table">
                        <tr>
                            <td class="lbl">Nomor</td>
                            <td class="sep">:</td>
                            <td class="val"><strong><?= htmlspecialchars($noFakturCetak) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="lbl">Nomor SO</td>
                            <td class="sep">:</td>
                            <td class="val"><?= htmlspecialchars($noSOCetak) ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Kepada</td>
                            <td class="sep">:</td>
                            <td class="val"><?= htmlspecialchars($namaCustomer) ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Alamat</td>
                            <td class="sep">:</td>
                            <td class="val"><?= htmlspecialchars($alamatCustomer) ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Kolom Kanan -->
                <div class="meta-col">
                    <table class="meta-table">
                        <tr>
                            <td class="lbl">Tanggal</td>
                            <td class="sep">:</td>
                            <td class="val"><?= htmlspecialchars($tglFormatted) ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Up.</td>
                            <td class="sep">:</td>
                            <td class="val">-</td>
                        </tr>
                        <tr>
                            <td class="lbl">Keterangan</td>
                            <td class="sep">:</td>
                            <td class="val"><?= htmlspecialchars($keteranganFaktur) ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Term</td>
                            <td class="sep">:</td>
                            <td class="val"><?= htmlspecialchars($termBayar) ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Salesman</td>
                            <td class="sep">:</td>
                            <td class="val"><?= htmlspecialchars($salesman) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="divider-thin-meta"></div>

            <!-- TABEL ITEMS PESANAN -->
            <div class="table-scroll-container">
                <table class="table-items">
                    <thead>
                        <tr>
                            <th class="text-left" style="width: 48%;">Nama Barang / Pesanan</th>
                            <th class="text-center" style="width: 9%;">Jumlah</th>
                            <th class="text-center" style="width: 9%;">Satuan</th>
                            <th class="text-right" style="width: 13%;">Harga Satuan</th>
                            <th class="text-right" style="width: 8%;">Disc.</th>
                            <th class="text-right" style="width: 13%;">Sub Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($details)): ?>
                            <tr class="bg-soft">
                                <td colspan="6" class="text-center" style="padding: 18px 0; color: #64748b;">
                                    Tidak ada item barang dalam faktur ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $rowIdx = 0; foreach ($details as $d): ?>
                                <?php 
                                $isSoftBg = ($rowIdx % 2 === 0); 
                                $rowIdx++;
                                $itemQty = (float)($d['qty'] ?? 1);
                                $itemHrg = (float)($d['hrg_satuan'] ?? 0);
                                $itemDisc = (float)($d['disc1'] ?? 0);
                                $itemSubtotal = (float)($d['subtotal_after_disc'] ?? ($d['subtotal'] ?? ($itemQty * $itemHrg)));
                                ?>
                                <tr class="<?= $isSoftBg ? 'bg-soft' : 'bg-white' ?>">
                                    <td class="text-left">
                                        <div class="col-item-name">
                                            <span class="item-code"><?= htmlspecialchars($d['kd_barang'] ?? '') ?></span>
                                            <span class="item-name"><?= htmlspecialchars($d['nama_barang'] ?? '') ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= number_format($itemQty, 0, ',', '.') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($d['satuan'] ?? 'Pcs') ?></td>
                                    <td class="text-right"><?= number_format($itemHrg, 2, '.', ',') ?></td>
                                    <td class="text-right"><?= number_format($itemDisc, 2, '.', ',') ?></td>
                                    <td class="text-right"><?= number_format($itemSubtotal, 2, '.', ',') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <!-- FOOTER SUMMARY -->
            <div class="footer-summary-wrap">
                <div class="summary-flex">
                    <!-- Terbilang Kalimat Rupiah -->
                    <div class="spelled-box">
                        <?= htmlspecialchars($terbilangTeks) ?>
                    </div>

                    <!-- Ringkasan Angka Final -->
                    <div class="calc-box">
                        <table class="calc-table">
                            <tr>
                                <td class="lbl">Discount Final :</td>
                                <td class="curr"></td>
                                <td class="val"><?= number_format($discFinal, 2, '.', ',') ?></td>
                            </tr>
                            <tr>
                                <td class="lbl">Pajak :</td>
                                <td class="curr"></td>
                                <td class="val"><?= number_format($pajak, 2, '.', ',') ?></td>
                            </tr>
                            <tr class="row-total">
                                <td class="lbl">Total :</td>
                                <td class="curr">Rp</td>
                                <td class="val"><?= number_format($grandTotal, 2, '.', ',') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TANDA TANGAN (ZAHIR STYLE) -->
            <div class="signature-grid">
                <div class="sig-col">
                    <div class="sig-title"><?= htmlspecialchars($namaCustomer) ?></div>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                </div>
                <div class="sig-col">
                    <div class="sig-title-corp">PT. KARISMA INDOARGO UNIVERSAL</div>
                    <div class="sig-space"></div>
                    <div class="sig-line"></div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
