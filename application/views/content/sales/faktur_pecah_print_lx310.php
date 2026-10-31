<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Cetak Faktur Pecahan H (LX-310)') ?></title>
    <style>
        /* =========================================================================
         * FORMAT CETAK PERSIS FASTREPORT: TTB_2022_Q_LOT_PAJAK_1.11.fr3
         * Ukuran Kertas : Custom 210 mm x 140 mm (21 cm x 14 cm)
         * Margin        : Top 7 mm (0.7 cm), Right 1 mm (0.1 cm), Bottom 0, Left 0
         * Area Cetak    : 209 mm x 133 mm (789.92 px x 502.68 px @ 96 DPI)
         * Konversi      : 1 mm = 3.779527559 px
         * ========================================================================= */
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: "Arial Unicode MS", Arial, sans-serif;
            background-color: #e2e8f0;
            color: #000000;
            margin: 0;
            padding: 20px 0;
            font-size: 11.25pt; /* Font.Height="-15" */
            line-height: 1.2;
        }

        /* Bilah Aksi di Layar (Tidak Ikut Tercetak) */
        .no-print-bar {
            width: 210mm;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 10px 18px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 15px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            border: none;
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

        .print-container {
            width: 210mm;
            margin: 0 auto;
        }

        /* Lembar Kertas 210mm x 140mm di Layar */
        .print-sheet {
            width: 210mm;
            height: 140mm;
            margin: 0 auto 20px auto;
            background: #ffffff;
            padding: 7mm 1mm 0mm 0mm; /* TopMargin=7mm, RightMargin=1mm, BottomMargin=0, LeftMargin=0 */
            border: 1px dashed #94a3b8;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            position: relative;
            overflow: hidden;
            page-break-after: always;
            break-after: page;
        }

        .print-sheet:last-child {
            margin-bottom: 0;
            page-break-after: avoid;
            break-after: avoid;
        }

        /* Area Printable Dalam Margin (209mm x 133mm) */
        .printable-canvas {
            position: relative;
            width: 209mm;
            height: 133mm;
            overflow: hidden;
        }

        /* -------------------------------------------------------------------------
         * 1. PageHeader1 (Height="52" px = 13.76mm)
         * ------------------------------------------------------------------------- */
        .band-page-header {
            position: relative;
            width: 209mm;
            height: 13.76mm;
        }

        /* Memo32: Nomor Faktur [frxDBFakturPenjualan."NOJURNAL"]
         * Left="611" (161.66mm), Top="21" (5.56mm), Width="165.35" (43.75mm), Height="27" (7.14mm)
         * Font: Trebuchet MS, -19 (14.25pt), Style=1 (Bold), HAlign=haCenter
         */
        .frx-memo32-nofaktur {
            position: absolute;
            left: 161.66mm;
            top: 5.56mm;
            width: 43.75mm;
            height: 7.14mm;
            font-family: "Trebuchet MS", Arial, sans-serif;
            font-size: 14.25pt;
            font-weight: bold;
            text-align: center;
            line-height: 7.14mm;
            white-space: nowrap;
        }

        /* -------------------------------------------------------------------------
         * 2. ColumnHeader1 (Height="80" px = 21.17mm)
         * ------------------------------------------------------------------------- */
        .band-column-header {
            position: relative;
            width: 209mm;
            height: 21.17mm;
            font-family: "Arial Unicode MS", Arial, sans-serif;
            font-size: 11.25pt;
            font-weight: normal;
        }

        /* Memo6: [Tanggal Transaksi]
         * Left="8.12" (2.15mm), Top="20.12" (5.32mm), Width="96.12" (25.43mm), Height="52.68" (13.94mm)
         */
        .frx-memo6-tgl {
            position: absolute;
            left: 2.15mm;
            top: 5.32mm;
            width: 25.43mm;
            height: 13.94mm;
            text-align: center;
            white-space: nowrap;
        }

        /* Memo50: [Term Pembayaran]
         * Left="109.02" (28.84mm), Top="20.12" (5.32mm), Width="98.68" (26.11mm), Height="52.56" (13.91mm)
         */
        .frx-memo50-term {
            position: absolute;
            left: 28.84mm;
            top: 5.32mm;
            width: 26.11mm;
            height: 13.91mm;
            text-align: center;
        }

        /* Memo18: [<Tanggal Transaksi>+<DUEDAYS>]
         * Left="217.69" (57.60mm), Top="20.12" (5.32mm), Width="91.12" (24.11mm), Height="52.68" (13.94mm)
         */
        .frx-memo18-jtempo {
            position: absolute;
            left: 57.60mm;
            top: 5.32mm;
            width: 24.11mm;
            height: 13.94mm;
            text-align: center;
        }

        /* Memo8: [Kontak Person]
         * Left="309.81" (81.97mm), Top="20.12" (5.32mm), Width="175.65" (46.47mm), Height="53.34" (14.11mm)
         */
        .frx-memo8-kontak {
            position: absolute;
            left: 81.97mm;
            top: 5.32mm;
            width: 46.47mm;
            height: 14.11mm;
            text-align: center;
            overflow: hidden;
        }

        /* Memo38: [frxDBFakturPenjualan."ALAMATANTAR"]
         * Left="476.78" (126.15mm), Top="8.34" (2.21mm), Width="308.69" (81.67mm), Height="65.35" (17.29mm)
         */
        .frx-memo38-alamat {
            position: absolute;
            left: 126.15mm;
            top: 2.21mm;
            width: 81.67mm;
            height: 17.29mm;
            text-align: center;
            overflow: hidden;
            line-height: 1.18;
        }

        /* -------------------------------------------------------------------------
         * 3. MasterData: Band3 (Height="37.7953" px = 10.00mm per item)
         * ------------------------------------------------------------------------- */
        .band-master-container {
            position: relative;
            width: 209mm;
            max-height: 56.29mm; /* Area antara ColumnHeader1 (34.93mm) dan PageFooter1 (91.22mm) */
            overflow: hidden;
        }

        .band-master-row {
            position: relative;
            width: 209mm;
            height: 10.00mm;
            font-family: "Arial Unicode MS", Arial, sans-serif;
            font-size: 11.25pt;
            font-weight: normal;
        }

        /* Memo52: [LINE#].
         * Left="8.12" (2.15mm), Top="0.78" (0.21mm), Width="23" (6.09mm), Height="20.22" (5.35mm), HAlign=haRight, VAlign=vaCenter
         */
        .frx-memo52-lineno {
            position: absolute;
            left: 2.15mm;
            top: 0.21mm;
            width: 6.09mm;
            height: 5.35mm;
            text-align: right;
            line-height: 5.35mm;
            white-space: nowrap;
        }

        /* Memo45: [Nama Barang]
         * Left="44.68" (11.82mm), Top="0.78" (0.21mm), Width="432.62" (114.46mm), Height="20" (5.29mm)
         */
        .frx-memo45-namabarang {
            position: absolute;
            left: 11.82mm;
            top: 0.21mm;
            width: 100.00mm;
            height: 5.29mm;
            text-align: left;
            line-height: 5.29mm;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Memo37 & Memo21: JUMLAHF1 & SATUANF1 (Fraksi 1 bila berbeda satuan)
         * Memo37: Left="332.28" (87.91mm), Top="0.78" (0.21mm), Width="61.12" (16.17mm), Height="20.22" (5.35mm)
         * Memo21: Left="398.75" (105.50mm), Top="0.56" (0.15mm), Width="38.44" (10.17mm), Height="19.78" (5.23mm)
         */
        .frx-memo37-jumlahf1 {
            position: absolute;
            left: 87.91mm;
            top: 0.21mm;
            width: 16.17mm;
            height: 5.35mm;
            text-align: right;
            line-height: 5.35mm;
            white-space: nowrap;
        }

        .frx-memo21-satuanf1 {
            position: absolute;
            left: 105.50mm;
            top: 0.15mm;
            width: 10.17mm;
            height: 5.23mm;
            text-align: right;
            line-height: 5.23mm;
            white-space: nowrap;
        }

        /* frxDBFakturPenjualanJUMLAH: [frxDBFakturPenjualan."JUMLAH"]
         * Left="425.10" (112.48mm), Top="0.56" (0.15mm), Width="79.37" (21.00mm), Height="19.90" (5.26mm), HAlign=haRight, VAlign=vaCenter
         */
        .frx-jumlah {
            position: absolute;
            left: 112.48mm;
            top: 0.15mm;
            width: 21.00mm;
            height: 5.26mm;
            text-align: right;
            line-height: 5.26mm;
            white-space: nowrap;
        }

        /* frxDBFakturPenjualanIdUnitReport: Satuan (Pcs, Btl, Pack, dll)
         * Left="508.25" (134.48mm), Top="0.56" (0.15mm), Width="45.35" (12.00mm), Height="20.12" (5.32mm), VAlign=vaCenter
         */
        .frx-idunit {
            position: absolute;
            left: 134.48mm;
            top: 0.15mm;
            width: 12.00mm;
            height: 5.32mm;
            text-align: left;
            line-height: 5.32mm;
            white-space: nowrap;
        }

        /* Memo61: [[frxDBFakturPenjualan."HARGA"]*1.11]
         * Left="570.90" (151.05mm), Top="0.78" (0.21mm), Width="71" (18.79mm), Height="20.22" (5.35mm), HAlign=haRight, VAlign=vaCenter
         */
        .frx-memo61-harga {
            position: absolute;
            left: 151.05mm;
            top: 0.21mm;
            width: 18.79mm;
            height: 5.35mm;
            text-align: right;
            line-height: 5.35mm;
            white-space: nowrap;
        }

        /* Memo54: [[frxDBFakturPenjualan."SUBTOTAL"]*1.11]
         * Left="652.68" (172.69mm), Top="0.78" (0.21mm), Width="122.02" (32.28mm), Height="20.22" (5.35mm), HAlign=haRight, VAlign=vaCenter
         */
        .frx-memo54-subtotal {
            position: absolute;
            left: 172.69mm;
            top: 0.21mm;
            width: 32.28mm;
            height: 5.35mm;
            text-align: right;
            line-height: 5.35mm;
            white-space: nowrap;
        }

        /* frxDBLotJualLOT_NO: Lot & Expired Date
         * Left="45.35" (12.00mm), Top="18.90" (5.00mm), Width="400.63" (106.00mm), Height="18.90" (5.00mm)
         * Font: Arial, -13 (9.75pt)
         */
        .frx-lot-no {
            position: absolute;
            left: 12.00mm;
            top: 5.00mm;
            width: 106.00mm;
            height: 5.00mm;
            font-family: Arial, sans-serif;
            font-size: 9.75pt;
            text-align: left;
            line-height: 5.00mm;
            white-space: nowrap;
        }

        /* -------------------------------------------------------------------------
         * 4. PageFooter1 (Height="157.91342" px = 41.78mm, di bagian bawah halaman)
         * ------------------------------------------------------------------------- */
        .band-page-footer {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 209mm;
            height: 41.78mm;
            font-family: "Arial Unicode MS", Arial, sans-serif;
            font-size: 11.25pt;
            font-weight: normal;
        }

        /* Memo2: [frxDBFakturPenjualan."NOTE"]
         * Left="45" (11.91mm), Top="0.41" (0.11mm), Width="447.65" (118.44mm), Height="24.56" (6.50mm)
         */
        .frx-memo2-note {
            position: absolute;
            left: 11.91mm;
            top: 0.11mm;
            width: 118.44mm;
            height: 6.50mm;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Memo60: [frxDBFakturPenjualan."SALESMAN"]  /  [frxDBFakturPenjualan."KODEPELANGGAN"]
         * Left="44.44" (11.76mm), Top="21" (5.56mm), Width="343.09" (90.78mm), Height="22.78" (6.03mm)
         * Font: Trebuchet MS, -15 (11.25pt), VAlign=vaCenter
         */
        .frx-memo60-salesman {
            position: absolute;
            left: 11.76mm;
            top: 5.56mm;
            width: 90.78mm;
            height: 6.03mm;
            font-family: "Trebuchet MS", Arial, sans-serif;
            font-size: 11.25pt;
            line-height: 6.03mm;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Memo44: [frxDBFakturPenjualan."A_USER"], [frxDBgjurnal."JAMBUAT"]
         * Left="345.27" (91.35mm), Top="21.22" (5.61mm), Width="308.40" (81.60mm), Height="24.56" (6.50mm)
         */
        .frx-memo44-user {
            position: absolute;
            left: 91.35mm;
            top: 5.61mm;
            width: 81.60mm;
            height: 6.50mm;
            text-align: left;
            white-space: nowrap;
        }

        /* Memo25: [Total Penjualan]
         * Left="622.90" (164.81mm), Top="21" (5.56mm), Width="154.02" (40.75mm), Height="24" (6.35mm)
         * Font: Arial Unicode MS, -15 (11.25pt), Style=1 (Bold), HAlign=haRight
         */
        .frx-memo25-total {
            position: absolute;
            left: 164.81mm;
            top: 5.56mm;
            width: 40.75mm;
            height: 6.35mm;
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }

        /* frxDBFakturPenjualanTGLDIANTAR: [frxDBFakturPenjualan."TGLDIANTAR"]
         * Digeser ke kanan dari 3.00mm menjadi 4.50mm agar pas di dalam batas kotak kertas cetak fisik dan tidak terpotong tepi kiri.
         */
        .frx-tgldiantar-box {
            position: absolute;
            left: 4.50mm;
            top: 13.82mm;
            width: 20.00mm;
            height: 6.00mm;
            border: 1px solid #000000;
            font-family: Arial, sans-serif;
            font-size: 9.75pt;
            text-align: center;
            line-height: 5.50mm;
            white-space: nowrap;
        }

        /* frxDBFakturPenjualanPELANGGAN: [frxDBFakturPenjualan."JABATAN"] (Kode Rute / Rayon)
         * Digeser ke kanan dari 52.00mm menjadi 54.00mm agar pas presisi di dalam kotak form kertas.
         */
        .frx-jabatan-box {
            position: absolute;
            left: 54.00mm;
            top: 13.82mm;
            width: 20.00mm;
            height: 6.00mm;
            border: 1px solid #000000;
            font-family: Arial, sans-serif;
            font-size: 9.75pt;
            text-align: center;
            line-height: 5.50mm;
            white-space: nowrap;
        }

        /* Memo1 & frxDBFakturPenjualanDISCOUNT_FINAL_AMOUNT:
         * Memo1: Left="662" (175.15mm), Top="73" (19.31mm), Width="49" (12.96mm), Height="22.32" (5.91mm), Style=1 (Bold)
         * Amount: Left="668.01" (176.74mm), Top="73.33" (19.40mm), Width="109.61" (29.00mm), Height="19.90" (5.26mm), HAlign=haRight
         */
        .frx-memo1-disclabel {
            position: absolute;
            left: 175.15mm;
            top: 19.31mm;
            width: 12.96mm;
            height: 5.91mm;
            font-weight: bold;
            text-align: left;
            white-space: nowrap;
        }

        .frx-disc-amount {
            position: absolute;
            left: 176.74mm;
            top: 19.40mm;
            width: 29.00mm;
            height: 5.26mm;
            text-align: right;
            white-space: nowrap;
        }

        /* Memo34: [SUM(<frxDBFakturPenjualan."Berat">*<frxDBFakturPenjualan."jUMLAH">)/1000]
         * Left="286.69" (75.85mm), Top="82.78" (21.90mm), Width="85" (22.49mm), Height="30.12" (7.97mm)
         * Font: Arial Unicode MS, -16 (12pt), HAlign=haRight, VAlign=vaCenter
         */
        .frx-memo34-berat {
            position: absolute;
            left: 75.85mm;
            top: 21.90mm;
            width: 22.49mm;
            height: 7.97mm;
            font-size: 12pt;
            text-align: right;
            line-height: 7.97mm;
            white-space: nowrap;
        }

        /* PENGATURAN CETAK KHUSUS EPSON LX-310 (CONTINUOUS FORM 21 cm x 14 cm / 8.5 x 5.5 inch)
         * Menggunakan orientasi landscape eksplisit agar driver printer dot-matrix
         * tidak memutar teks 90 derajat secara vertikal (portrait).
         */
        @page {
            size: 210mm 140mm landscape;
            margin: 7mm 1mm 0mm 0mm;
        }

        @media print {
            @page {
                size: 210mm 140mm landscape;
                margin: 7mm 1mm 0mm 0mm;
            }

            html, body {
                width: 209mm;
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .print-container {
                width: 209mm !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .print-sheet {
                border: none !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 209mm !important;
                height: 133mm !important;
                max-height: 133mm !important;
                page-break-after: always !important;
                break-after: page !important;
            }

            .print-sheet:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
        }
    </style>
</head>
<body>

    <?php
    // Normalisasi struktur data: mendukung mode cetak 1 faktur maupun banyak faktur sekaligus
    if (empty($list_faktur) && !empty($faktur)) {
        $list_faktur = [
            [
                'faktur'        => $faktur,
                'details'       => $details ?? [],
                'parent_faktur' => $parent_faktur ?? [],
                'grand_total'   => $grand_total ?? 0
            ]
        ];
    }
    $total_sheets = count($list_faktur);

    // Helper pemformatan nama satuan agar seragam dengan rumus IIF IDUNIT di FastReport
    if (!function_exists('formatSatuanFr3')) {
        function formatSatuanFr3($satuan)
        {
            $s = strtoupper(trim((string)$satuan));
            $map = [
                'PCS'    => 'Pcs',
                'BTL'    => 'Btl',
                'BOTOL'  => 'Btl',
                'DRUM'   => 'Drum',
                'GAL'    => 'Gal',
                'GALON'  => 'Gal',
                'KG'     => 'Kg',
                'PACK'   => 'Pack',
                'PAIL'   => 'Pail',
                'SACH'   => 'Sach',
                'SACHET' => 'Sach',
                'TAB'    => 'Tab',
                'BALL'   => 'Ball',
                'ZAK'    => 'Zak',
                'KLG'    => 'Klg',
                'KALENG' => 'Klg',
                'BOX'    => 'Box',
                'DUS'    => 'Box',
                'LTR'    => 'Ltr',
                'LITER'  => 'Ltr',
            ];
            return $map[$s] ?? ucfirst(strtolower(trim((string)$satuan ?: 'Pcs')));
        }
    }
    ?>

    <!-- Bilah Tombol di Layar -->
    <div class="no-print-bar">
        <div>
            <strong>Format Cetak Epson LX-310 (TTB_2022_Q_LOT_PAJAK_1.11.fr3)</strong>
            <span style="color: #64748b; margin-left: 8px;">
                Ukuran: 21 cm x 14 cm (Landscape) &bull; Total: <strong><?= $total_sheets ?></strong> Faktur
                <?php if ($total_sheets > 1 && isset($total_all_grand)): ?>
                    (Total Nilai: <strong>Rp <?= number_format($total_all_grand, 0, ',', '.') ?></strong>)
                <?php endif; ?>
            </span>
            <div style="font-size: 11px; color: #0284c7; margin-top: 3px;">
                <i class="fas fa-info-circle"></i> Tips Cetak: Di dialog Print browser, pastikan <strong>Layout: Landscape</strong> dan Paper Size: <strong>Custom (21x14 cm / 8.5x5.5")</strong>.
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="window.print()" class="btn-action btn-print">
                &#128424; <?= $total_sheets > 1 ? 'Cetak Semua (' . $total_sheets . ' Faktur)' : 'Cetak Faktur (LX-310)' ?>
            </button>
            <?php if ($total_sheets === 1 && !empty($list_faktur[0]['faktur']['id_pecah'])): ?>
                <a href="<?= base_url('sales_order/detail_faktur_pecah/' . $list_faktur[0]['faktur']['id_pecah']) ?>" class="btn-action btn-back">
                    &larr; Detail
                </a>
            <?php else: ?>
                <a href="<?= base_url('sales_order/pecah_faktur#content-faktur-h') ?>" class="btn-action btn-back">
                    &larr; Daftar Faktur H
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Container Cetak -->
    <div class="print-container">
        <?php foreach ($list_faktur as $idx_sheet => $sheet_item): ?>
            <?php
            $faktur        = $sheet_item['faktur'];
            $details       = $sheet_item['details'] ?? [];
            $parent_faktur = $sheet_item['parent_faktur'] ?? [];
            $grand_total   = (float)($sheet_item['grand_total'] ?? 0);

            // 1. Term Pembayaran (Memo50) & DUEDAYS (Memo18)
            $tempo_hari = (int)($faktur['tempo'] ?? ($faktur['jtempo'] ?? 0));
            $tgl_faktur_ts = !empty($faktur['tanggal_faktur']) ? strtotime($faktur['tanggal_faktur']) : time();
            $tgl_jtempo_ts = !empty($faktur['tanggal_jatuh_tempo']) ? strtotime($faktur['tanggal_jatuh_tempo']) : 0;

            // Jika tempo belum tersimpan di DB, hitung dari selisih tanggal jatuh tempo dan tanggal faktur
            if ($tempo_hari <= 0 && $tgl_jtempo_ts > $tgl_faktur_ts) {
                $tempo_hari = (int)round(($tgl_jtempo_ts - $tgl_faktur_ts) / 86400);
            }

            // Jika masih belum ada, cek parent Faktur Z
            if ($tempo_hari <= 0 && !empty($parent_faktur)) {
                $tempo_hari = (int)($parent_faktur['tempo'] ?? ($parent_faktur['jtempo'] ?? 0));
                if ($tempo_hari <= 0 && !empty($parent_faktur['tanggal_faktur']) && !empty($parent_faktur['tanggal_jatuh_tempo'])) {
                    $ptF = strtotime($parent_faktur['tanggal_faktur']);
                    $ptJ = strtotime($parent_faktur['tanggal_jatuh_tempo']);
                    if ($ptJ > $ptF) {
                        $tempo_hari = (int)round(($ptJ - $ptF) / 86400);
                    }
                }
            }

            // Jika faktur pecahan tidak memiliki tempo eksplisit atau bernilai 0,
            // default standar distribusi agrokimia Karisma ERP adalah 30 Hari
            if ($tempo_hari <= 0) {
                $tempo_hari = 30;
            }

            // Format Term Pembayaran sesuai permintaan: '30 Hari', '60 Hari', dst.
            $cara_bayar_text = "{$tempo_hari} Hari";

            // 2. Tanggal Transaksi (Memo6) & Jatuh Tempo (Memo18: [<Tanggal Transaksi> + DUEDAYS])
            $tgl_faktur = date('d/m/Y', $tgl_faktur_ts);
            if ($tgl_jtempo_ts > $tgl_faktur_ts) {
                $tgl_jtempo = date('d/m/Y', $tgl_jtempo_ts);
            } else {
                $tgl_jtempo = date('d/m/Y', strtotime("+{$tempo_hari} days", $tgl_faktur_ts));
            }

            // 3. Kontak Person (Memo8)
            $kontak_nama = !empty($faktur['full_customer_name'])
                ? $faktur['full_customer_name']
                : (!empty($faktur['customer_name']) ? preg_replace('/\s*\([^)]*\)$/', '', $faktur['customer_name']) : '-');

            // 4. Alamat Pengantaran (Memo38)
            $alamat_teks = !empty($faktur['ca_alamat'])
                ? trim($faktur['ca_alamat'])
                : (!empty($faktur['alamat_kios']) ? trim($faktur['alamat_kios']) : '');

            $kota_teks = !empty($faktur['ca_kota'])
                ? trim($faktur['ca_kota'])
                : (!empty($faktur['regional']) ? trim($faktur['regional']) : '');

            // 5. Note / Catatan (Memo2) - Hapus teks sistem "Pecahan Massal dari Faktur Z: ..."
            $catatan_raw = $faktur['catatan'] ?? '';
            $catatan_clean = trim(preg_replace('/Pecahan\s*(Massal|Otomatis)?\s*dari\s*Faktur\s*Z[\s:]*[A-Z0-9_\-\/,\s]*/i', '', $catatan_raw));

            // 6. Salesman & Kode Pelanggan (Memo60)
            $salesman_str = !empty($faktur['salesman']) ? strtoupper(trim($faktur['salesman'])) : 'SALES';
            $kd_cust_str  = !empty($faktur['kd_customer']) ? strtoupper(trim($faktur['kd_customer'])) : '-';

            // 7. User & Waktu Buat (Memo44) - Format: mm.dd.yyyy - hh:mm
            $creator_name = !empty($faktur['create_by']) ? $faktur['create_by'] : 'Admin';
            $create_time  = !empty($faktur['create_at']) ? date('m.d.Y - H:i', strtotime($faktur['create_at'])) : date('m.d.Y - H:i');

            // 8. Jabatan / Rute (frxDBFakturPenjualanPELANGGAN)
            $rute_str = !empty($faktur['customer_kd_rute']) ? strtoupper(trim($faktur['customer_kd_rute'])) : 'SBY';

            // 9. Perhitungan Total Berat (Memo34: SUM(Berat * JUMLAH) / 1000) & Total Discount
            $sum_berat_gram = 0;
            $total_disc_amount = 0;
            foreach ($details as $d_calc) {
                $q_c = (float)($d_calc['qty'] ?? 0);
                $bg_c = (float)($d_calc['berat_gram'] ?? 0);
                $sum_berat_gram += ($bg_c * $q_c);

                $sub_before = (float)($d_calc['subtotal_before_disc'] ?? ($q_c * (float)($d_calc['hrg_satuan'] ?? 0)));
                $sub_after  = (float)($d_calc['total_harga'] ?? $sub_before);
                if ($sub_before > $sub_after) {
                    $total_disc_amount += ($sub_before - $sub_after);
                }
            }
            $berat_val = ($sum_berat_gram > 0)
                ? ($sum_berat_gram / 1000)
                : ((float)($faktur['total_tonase'] ?? 0) * 1000);
            ?>

            <!-- LEMBAR FAKTUR #<?= $idx_sheet + 1 ?> -->
            <div class="print-sheet">
                <div class="printable-canvas">

                    <!-- 1. PageHeader1 -->
                    <div class="band-page-header">
                        <div class="frx-memo32-nofaktur">
                            <?= htmlspecialchars($faktur['no_faktur']) ?>
                        </div>
                    </div>

                    <!-- 2. ColumnHeader1 -->
                    <div class="band-column-header">
                        <div class="frx-memo6-tgl">
                            <?= $tgl_faktur ?>
                        </div>
                        <div class="frx-memo50-term">
                            <?= htmlspecialchars($cara_bayar_text) ?>
                        </div>
                        <div class="frx-memo18-jtempo">
                            <?= $tgl_jtempo ?>
                        </div>
                        <div class="frx-memo8-kontak">
                            <?= htmlspecialchars($kontak_nama) ?>
                        </div>
                        <div class="frx-memo38-alamat">
                            <?php if (!empty($alamat_teks)): ?>
                                <?= nl2br(htmlspecialchars($alamat_teks)) ?>
                                <?php if (!empty($kota_teks)): ?><br><?php endif; ?>
                            <?php endif; ?>
                            <?php if (!empty($kota_teks)): ?>
                                <?= htmlspecialchars($kota_teks) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 3. MasterData: Band3 -->
                    <div class="band-master-container">
                        <?php $line_no = 1; foreach ($details as $d): ?>
                            <?php
                            $qty_item      = (float)($d['qty'] ?? 0);
                            $satuan_item   = formatSatuanFr3($d['satuan'] ?? 'Pcs');
                            $hrg_item      = (float)($d['hrg_satuan'] ?? 0);
                            $subtotal_item = (float)($d['total_harga'] ?? ($qty_item * $hrg_item));

                            // Logika LOT_NO & DATE2 sesuai rumus FastReport:
                            // [iif(<frxDBLotJual."LOT_NO">='0','',<frxDBLotJual."LOT_NO">)]  [iif(<frxDBLotJual."LOT_NO">='0','',' - ')] [IIF(<frxDBLotJual."DATE2">='1/1/1000','',<frxDBLotJual."DATE2">)]
                            $raw_lot = trim((string)($d['no_lot'] ?? ''));
                            $valid_lot = ($raw_lot !== '' && $raw_lot !== '0' && $raw_lot !== '-') ? $raw_lot : '';

                            $raw_exp = trim((string)($d['expired_date'] ?? ''));
                            $valid_exp = '';
                            if (
                                $raw_exp !== '' &&
                                $raw_exp !== '0000-00-00' &&
                                $raw_exp !== '1000-01-01' &&
                                $raw_exp !== '1970-01-01' &&
                                $raw_exp !== '1/1/1000'
                            ) {
                                $ts_exp = strtotime($raw_exp);
                                if ($ts_exp && (int)date('Y', $ts_exp) > 1970) {
                                    $valid_exp = date('d/m/Y', $ts_exp);
                                }
                            }
                            ?>
                            <div class="band-master-row">
                                <div class="frx-memo52-lineno">
                                    <?= $line_no++ ?>.
                                </div>
                                <div class="frx-memo45-namabarang">
                                    <?= htmlspecialchars($d['nama_barang']) ?>
                                </div>
                                <div class="frx-jumlah">
                                    <?= (floor($qty_item) == $qty_item) ? number_format($qty_item, 0, '.', ',') : number_format($qty_item, 2, '.', ',') ?>
                                </div>
                                <div class="frx-idunit">
                                    <?= htmlspecialchars($satuan_item) ?>
                                </div>
                                <div class="frx-memo61-harga">
                                    <?= number_format($hrg_item, 0, '.', ',') ?>
                                </div>
                                <div class="frx-memo54-subtotal">
                                    <?= number_format($subtotal_item, 0, '.', ',') ?>
                                </div>
                                <?php if ($valid_lot !== ''): ?>
                                    <div class="frx-lot-no">
                                        <?= htmlspecialchars($valid_lot) ?><?= $valid_exp !== '' ? '  - ' . htmlspecialchars($valid_exp) : '' ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- 4. PageFooter1 -->
                    <div class="band-page-footer">
                        <!-- Memo2: NOTE -->
                        <div class="frx-memo2-note">
                            <?= htmlspecialchars($catatan_clean) ?>
                        </div>

                        <!-- Memo60: SALESMAN  /  KODEPELANGGAN -->
                        <div class="frx-memo60-salesman">
                            <?= htmlspecialchars($salesman_str) ?>&nbsp;&nbsp;/&nbsp;&nbsp;<?= htmlspecialchars($kd_cust_str) ?>
                        </div>

                        <!-- Memo44: A_USER, JAMBUAT -->
                        <div class="frx-memo44-user">
                            <?= htmlspecialchars($creator_name) ?>, <?= $create_time ?>
                        </div>

                        <!-- Memo25: Total Penjualan -->
                        <div class="frx-memo25-total">
                            <?= number_format($grand_total, 0, '.', ',') ?>
                        </div>

                        <!-- frxDBFakturPenjualanTGLDIANTAR -->
                        <div class="frx-tgldiantar-box">
                            <?= $tgl_faktur ?>
                        </div>

                        <!-- frxDBFakturPenjualanPELANGGAN (JABATAN / Rute) -->
                        <div class="frx-jabatan-box">
                            <?= htmlspecialchars($rute_str) ?>
                        </div>

                        <!-- Memo1 & DISCOUNT_FINAL_AMOUNT -->
                        <?php if ($total_disc_amount > 0): ?>
                            <div class="frx-memo1-disclabel">Disc :</div>
                            <div class="frx-disc-amount"><?= number_format($total_disc_amount, 0, '.', ',') ?></div>
                        <?php endif; ?>

                        <!-- Memo34: SUM(Berat * JUMLAH) / 1000 -->
                        <?php if ($berat_val > 0): ?>
                            <div class="frx-memo34-berat">
                                <?= number_format($berat_val, 2, '.', ',') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
