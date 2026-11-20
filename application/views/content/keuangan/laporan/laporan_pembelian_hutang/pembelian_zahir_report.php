<?php
$meta  = $report_meta ?? ['slug' => 'jurnal-pembelian', 'title' => 'Jurnal - Pembelian'];
$slug  = $meta['slug'];
$title = $meta['title'];
?>

<style>
.pb-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.pb-paper {
    background: #fff;
    border: 1px solid #d9dee3;
    border-radius: 6px;
    padding: 24px 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    color: #1f2937;
    font-family: Arial, Helvetica, sans-serif;
}

.pb-meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #374151;
    margin-bottom: 4px;
}

.pb-header-center {
    text-align: center;
    margin-bottom: 18px;
}

.pb-company {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}

.pb-title {
    font-size: 20px;
    font-weight: 700;
    font-style: italic;
    color: #0284c7;
    margin-bottom: 2px;
}

.pb-period {
    font-size: 12px;
    font-weight: 600;
    color: #b91c1c;
}

.pb-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
}

.pb-table thead th {
    background: #e5e7eb;
    border-top: 1.5px solid #4b5563;
    border-bottom: 1.5px solid #4b5563;
    padding: 6px 8px;
    font-weight: 700;
    color: #111827;
    text-align: left;
    white-space: nowrap;
}

.pb-table thead th.num,
.pb-table tbody td.num,
.pb-table tfoot td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.pb-table tbody td {
    padding: 4px 8px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}

.pb-table tfoot td {
    font-weight: 700;
    background: #f3f4f6;
    border-top: 1.5px solid #4b5563;
    border-bottom: 1.5px solid #374151;
    padding: 6px 8px;
}

.pb-jurnal-head td {
    font-weight: 700;
    background: #f8fafc;
    border-top: 1px solid #cbd5e1;
    color: #111827;
    padding-top: 7px !important;
}

.pb-lpb-total td {
    font-weight: 700;
    border-top: 1px dashed #94a3b8;
    border-bottom: 1.5px solid #475569;
    background: #f8fafc;
    color: #0f172a;
}

.pb-adj-row td {
    background: #fff7ed;
    color: #9a3412;
}

.pb-rincian-card {
    border-top: 1.5px solid #374151;
    border-bottom: 1.5px solid #374151;
    padding: 12px 0;
    margin-bottom: 18px;
}

.pb-rincian-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 24px;
    font-size: 11.5px;
    margin-bottom: 10px;
}

.pb-rincian-kv {
    display: flex;
    gap: 6px;
    margin-bottom: 2px;
}

.pb-rincian-kv .k {
    width: 110px;
    font-weight: 700;
    color: #374151;
    flex-shrink: 0;
}

.pb-rincian-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-top: 10px;
    font-size: 11.5px;
}

.pb-rincian-summary {
    min-width: 260px;
}

.pb-rincian-summary .row-sum {
    display: flex;
    justify-content: space-between;
    padding: 2px 0;
}

.pb-rincian-summary .row-sum.bold {
    font-weight: 700;
    border-top: 1px solid #9ca3af;
    margin-top: 3px;
    padding-top: 4px;
}

.zahir-filter-tabs {
    display: flex;
    gap: 6px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 8px;
    margin-bottom: 14px;
}

.zahir-filter-tab {
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #334155;
    padding: 5px 14px;
    font-size: 12.5px;
    font-weight: 600;
    border-radius: 3px;
    cursor: pointer;
}

.zahir-filter-tab.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #fff;
}

@media print {
    .no-print,
    .main-header,
    .main-sidebar,
    .content-header,
    .main-footer {
        display: none !important;
    }

    .content-wrapper {
        margin-left: 0 !important;
        background: #fff !important;
        padding: 0 !important;
    }

    .pb-paper {
        border: none;
        box-shadow: none;
        padding: 0;
    }
}
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <section class="content-header no-print">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1><?= htmlspecialchars($title) ?></h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="<?= base_url('laporan/pembelian') ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Laporan Pembelian & Hutang
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="pb-toolbar no-print">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" id="btnOpenFilter">
                            <i class="fas fa-filter mr-1"></i> Filter Data
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak Laporan
                        </button>
                    </div>
                    <div class="text-muted small" id="pbStatusInfo">
                        Pilih filter untuk menampilkan laporan.
                    </div>
                </div>

                <div class="pb-paper">
                    <div class="pb-meta">
                        <span><?= date('g:i A, F j, Y') ?></span>
                        <span><?= htmlspecialchars($title) ?></span>
                    </div>
                    <div class="pb-header-center">
                        <div class="pb-company">KARISMA <?= date('Y') ?></div>
                        <div class="pb-title"><?= htmlspecialchars($title) ?></div>
                        <div class="pb-period" id="pbPeriodLabel">-</div>
                    </div>

                    <div id="pbContainer">
                        <div class="text-center text-muted py-4">
                            Silakan tentukan filter pada jendela Filter Data terlebih dahulu.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal Filter Data -->
<div class="modal fade" id="modalFilterData" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title font-weight-bold mb-0">Filter Data - <?= htmlspecialchars($title) ?></h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="zahir-filter-tabs">
                    <button type="button" class="zahir-filter-tab active" data-tab="standar">Standar</button>
                    <button type="button" class="zahir-filter-tab" data-tab="pencarian">Pencarian</button>
                </div>

                <div id="tabStandar">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Dari Tanggal</label>
                            <input type="date" id="filterStartDate" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="small font-weight-bold">Sampai Tanggal</label>
                            <input type="date" id="filterEndDate" class="form-control form-control-sm" value="<?= date('Y-m-t') ?>">
                        </div>
                    </div>
                </div>

                <div id="tabPencarian" style="display:none;">
                    <div class="form-group">
                        <label class="small font-weight-bold">Kata Kunci Pencarian</label>
                        <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="No. LPB, No. PO, Supplier, Barang, No. Faktur...">
                        <small class="text-muted">Pencarian akan memfilter dokumen, pemasok, atau barang yang cocok.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnApplyFilter">
                    <i class="fas fa-check mr-1"></i> Tampilkan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const SLUG = <?= json_encode($slug) ?>;
    const ENDPOINT = <?= json_encode($report_endpoint) ?>;

    function esc(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fmtNum(val, dec = 2) {
        const n = parseFloat(val) || 0;
        return n.toLocaleString('id-ID', {
            minimumFractionDigits: dec,
            maximumFractionDigits: dec
        });
    }

    function fmtDate(d) {
        if (!d || d === '0000-00-00') return '-';
        const parts = String(d).slice(0, 10).split('-');
        if (parts.length !== 3) return d;
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    function fmtPeriodText(s, e) {
        const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        function f(dt) {
            const p = String(dt).split('-');
            if (p.length !== 3) return dt;
            return parseInt(p[2], 10) + ' ' + (bulan[parseInt(p[1], 10) - 1] || p[1]) + ' ' + p[0];
        }
        return 'Periode : ' + f(s) + ' s/d ' + f(e);
    }

    function fmtKodeAkun(kode) {
        const k = String(kode || '');
        if (/^\d{5}$/.test(k)) {
            return k.slice(0, 3) + '-' + k.slice(3);
        }
        return k;
    }

    // 1. Render Jurnal - Pembelian & Jurnal - Diskon Pembelian
    function renderJurnalPembelian(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data jurnal pada periode ini.</div>';
        }
        let html = '<table class="pb-table"><thead><tr>'
            + '<th style="width:95px;">Tanggal</th>'
            + '<th>Deskripsi</th>'
            + '<th style="width:110px;">Departemen</th>'
            + '<th class="num" style="width:130px;">Debit</th>'
            + '<th class="num" style="width:130px;">Credit</th>'
            + '<th style="width:100px;">Proyek</th>'
            + '</tr></thead><tbody>';

        let grandDebit = 0;
        let grandKredit = 0;

        rows.forEach(function (hdr) {
            html += '<tr class="pb-jurnal-head">'
                + '<td>' + esc(fmtDate(hdr.tanggal_transaksi)) + '</td>'
                + '<td colspan="5">' + esc(hdr.keterangan || hdr.nomor_jurnal) + '</td>'
                + '</tr>';

            (hdr.details || []).forEach(function (d) {
                const debit = parseFloat(d.debit) || 0;
                const kredit = parseFloat(d.kredit) || 0;
                grandDebit += debit;
                grandKredit += kredit;
                const ref = d.nomor_dokumen || hdr.source_no || hdr.nomor_jurnal || '';
                const desc = (ref ? '<span class="text-muted mr-2">' + esc(ref) + '</span>' : '')
                    + '<span class="mr-2">' + esc(fmtKodeAkun(d.kode_akun)) + '</span>'
                    + esc(d.nama_akun);
                html += '<tr>'
                    + '<td></td>'
                    + '<td>' + desc + '</td>'
                    + '<td>' + esc(d.departemen || '-') + '</td>'
                    + '<td class="num">' + (debit > 0 ? fmtNum(debit) : '0,00') + '</td>'
                    + '<td class="num">' + (kredit > 0 ? fmtNum(kredit) : '0,00') + '</td>'
                    + '<td>' + esc(d.proyek || '-') + '</td>'
                    + '</tr>';
            });
        });

        html += '</tbody><tfoot><tr>'
            + '<td colspan="3" class="text-right">Total</td>'
            + '<td class="num">' + fmtNum(grandDebit) + '</td>'
            + '<td class="num">' + fmtNum(grandKredit) + '</td>'
            + '<td></td>'
            + '</tr></tfoot></table>';
        return html;
    }

    // 2. Render Penerimaan Barang (Harian) - Format Laporan Pak Supri
    function renderPenerimaanBarangHarian(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data penerimaan barang pada periode ini.</div>';
        }
        let html = '<table class="pb-table"><thead><tr>'
            + '<th>Tanggal</th>'
            + '<th>Nomor LPB</th>'
            + '<th>Tgl PO</th>'
            + '<th>PO</th>'
            + '<th>Nomor Surat Jalan</th>'
            + '<th>Supplier</th>'
            + '<th>Nama Barang</th>'
            + '<th>LOT - EXP</th>'
            + '<th class="num">Qty</th>'
            + '<th class="num">@ Rp.</th>'
            + '</tr></thead><tbody>';

        let grandQty = 0;

        rows.forEach(function (lpb) {
            const items = lpb.items || [];
            let lpbQty = 0;

            items.forEach(function (it, idx) {
                const q = parseFloat(it.qty) || 0;
                lpbQty += q;
                grandQty += q;

                const supplierInfo = idx === 0
                    ? '<div><b>' + esc(lpb.kd_suplier) + '</b></div>'
                        + '<div>' + esc(lpb.nama_suplier) + '</div>'
                        + '<div class="text-muted small">' + esc(lpb.nama_gudang) + '</div>'
                    : '';
                const barangInfo = '<div><b>' + esc(it.kd_barang) + '</b></div>'
                    + '<div>' + esc(it.nama_barang) + '</div>';
                const lotExp = esc(it.no_lot || '-') + '<br><span class="text-muted small">' + esc(fmtDate(it.expired_date)) + '</span>';

                html += '<tr>'
                    + '<td>' + (idx === 0 ? esc(fmtDate(lpb.tanggal_lpb)) : '') + '</td>'
                    + '<td>' + (idx === 0 ? esc(lpb.nomor_lpb) : '') + '</td>'
                    + '<td>' + (idx === 0 ? esc(fmtDate(lpb.tanggal_po)) : '') + '</td>'
                    + '<td>' + (idx === 0 ? esc(lpb.no_po) : '') + '</td>'
                    + '<td>' + (idx === 0 ? esc(lpb.nosj) : '') + '</td>'
                    + '<td>' + supplierInfo + '</td>'
                    + '<td>' + barangInfo + '</td>'
                    + '<td>' + lotExp + '</td>'
                    + '<td class="num">' + fmtNum(q, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.harga_satuan, 2) + '</td>'
                    + '</tr>';
            });

            (lpb.adjustments || []).forEach(function (adj) {
                const lotExp = esc(adj.no_lot_adjustment || '-') + '<br><span class="small">' + esc(fmtDate(adj.expired_date_adjustment)) + '</span>';
                html += '<tr class="pb-adj-row">'
                    + '<td>' + esc(fmtDate(adj.tanggal_adjustment)) + '</td>'
                    + '<td colspan="4"><b>Adj. Harga</b> (' + esc(adj.no_adjustment) + ')</td>'
                    + '<td></td>'
                    + '<td><b>' + esc(adj.kd_barang) + '</b><br>' + esc(adj.nama_barang) + '</td>'
                    + '<td>' + lotExp + '</td>'
                    + '<td class="num">' + fmtNum(adj.qty, 2) + '</td>'
                    + '<td class="num">' + fmtNum(adj.harga_benar, 2) + '</td>'
                    + '</tr>';
            });

            html += '<tr class="pb-lpb-total">'
                + '<td colspan="8" class="text-right">Total --&gt;</td>'
                + '<td class="num">' + fmtNum(lpbQty, 2) + '</td>'
                + '<td></td>'
                + '</tr>';
        });

        html += '</tbody><tfoot><tr>'
            + '<td colspan="8" class="text-right">Grand Total Qty</td>'
            + '<td class="num">' + fmtNum(grandQty, 2) + '</td>'
            + '<td></td>'
            + '</tr></tfoot></table>';
        return html;
    }

    // 3. Render Pembelian - Rincian
    function renderPembelianRincian(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data pembelian pada periode ini.</div>';
        }
        let html = '';

        rows.forEach(function (hdr) {
            html += '<div class="pb-rincian-card">'
                + '<div class="pb-rincian-grid">'
                + '<div>'
                + '<div class="pb-rincian-kv"><span class="k">Tanggal</span><span>: ' + esc(fmtDate(hdr.tanggal)) + '</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Pemasok</span><span>: ' + esc(hdr.nama_suplier) + ' (' + esc(hdr.kd_suplier) + ')</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Mata Uang</span><span>: IDR 1.00</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">No. Referensi</span><span>: ' + esc(hdr.no_referensi) + '</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Gudang</span><span>: ' + esc(hdr.nama_gudang) + '</span></div>'
                + '</div>'
                + '<div>'
                + '<div class="pb-rincian-kv"><span class="k">Status</span><span>: ' + esc(hdr.status_dokumen) + '</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Catatan</span><span>: ' + esc(hdr.catatan || '-') + '</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Proyek</span><span>: -</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Departemen</span><span>: -</span></div>'
                + '</div>'
                + '</div>';

            html += '<table class="pb-table"><thead><tr>'
                + '<th>Deskripsi</th>'
                + '<th class="num">Diterima</th>'
                + '<th class="num">Dipesan</th>'
                + '<th>Satuan</th>'
                + '<th class="num">Harga Satuan</th>'
                + '<th class="num">Disc.</th>'
                + '<th class="num">Sub Total</th>'
                + '<th class="num">Pajak</th>'
                + '</tr></thead><tbody>';

            (hdr.items || []).forEach(function (it) {
                html += '<tr>'
                    + '<td><b>' + esc(it.kd_barang) + '</b> - ' + esc(it.nama_barang) + '</td>'
                    + '<td class="num">' + fmtNum(it.diterima, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.dipesan, 2) + '</td>'
                    + '<td>' + esc(it.satuan) + '</td>'
                    + '<td class="num">' + fmtNum(it.harga_satuan, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.diskon, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.subtotal, 2) + '</td>'
                    + '<td class="num">' + (it.pajak_persen > 0 ? fmtNum(it.pajak_persen, 1) + '%' : '-') + '</td>'
                    + '</tr>';
            });

            html += '</tbody></table>';

            html += '<div class="pb-rincian-footer">'
                + '<div>'
                + '<div><b>Bag. Pembelian :</b> ' + esc(hdr.bag_pembelian || '-') + '</div>'
                + '<div><b>Input oleh :</b> ' + esc(hdr.input_oleh || '-') + '</div>'
                + '</div>'
                + '<div class="pb-rincian-summary">'
                + '<div class="row-sum"><span>Biaya Kirim</span><span>' + fmtNum(hdr.biaya_kirim, 2) + '</span></div>'
                + '<div class="row-sum"><span>Diskon Akhir</span><span>' + fmtNum(hdr.diskon_akhir, 2) + '</span></div>'
                + '<div class="row-sum bold"><span>Total</span><span>' + fmtNum(hdr.total, 2) + '</span></div>'
                + '<div class="row-sum"><span>Pembayaran</span><span>' + fmtNum(hdr.pembayaran, 2) + '</span></div>'
                + '<div class="row-sum bold"><span>Saldo</span><span>' + fmtNum(hdr.saldo, 2) + '</span></div>'
                + '</div>'
                + '</div>'
                + '</div>';
        });

        return html;
    }

    // 4. Render Pembelian - eFaktur
    function renderEfakturPembelian(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data eFaktur pembelian pada periode ini.</div>';
        }
        let html = '<table class="pb-table"><thead><tr>'
            + '<th>Tanggal</th>'
            + '<th>No. Ref.</th>'
            + '<th>Suplier</th>'
            + '<th>Nomor Seri Faktur</th>'
            + '<th>NPWP</th>'
            + '<th class="num">Jumlah DPP</th>'
            + '<th class="num">Jumlah PPN</th>'
            + '<th style="width:70px;text-align:center;">Sudah</th>'
            + '</tr></thead><tbody>';

        let totalDpp = 0;
        let totalPpn = 0;

        rows.forEach(function (r) {
            const dpp = parseFloat(r.jumlah_dpp) || 0;
            const ppn = parseFloat(r.jumlah_ppn) || 0;
            totalDpp += dpp;
            totalPpn += ppn;

            html += '<tr>'
                + '<td>' + esc(fmtDate(r.tanggal)) + '</td>'
                + '<td>' + esc(r.no_ref) + '</td>'
                + '<td>' + esc(r.suplier) + '</td>'
                + '<td>' + esc(r.nomor_seri_faktur) + '</td>'
                + '<td>' + esc(r.npwp || '-') + '</td>'
                + '<td class="num">' + fmtNum(dpp, 2) + '</td>'
                + '<td class="num">' + fmtNum(ppn, 2) + '</td>'
                + '<td style="text-align:center;">' + esc(r.sudah || '-') + '</td>'
                + '</tr>';
        });

        html += '</tbody><tfoot><tr>'
            + '<td colspan="5" class="text-right">Total :</td>'
            + '<td class="num">' + fmtNum(totalDpp, 2) + '</td>'
            + '<td class="num">' + fmtNum(totalPpn, 2) + '</td>'
            + '<td></td>'
            + '</tr></tfoot></table>';
        return html;
    }

    // 5. Render Pesanan Pembelian - Rincian
    function renderPesananPembelianRincian(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data pesanan pembelian pada periode ini.</div>';
        }
        let html = '';

        rows.forEach(function (hdr) {
            html += '<div class="pb-rincian-card">'
                + '<div class="pb-rincian-grid">'
                + '<div>'
                + '<div class="pb-rincian-kv"><span class="k">Tanggal</span><span>: ' + esc(fmtDate(hdr.tanggal)) + '</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">No. Referensi</span><span>: ' + esc(hdr.no_referensi) + '</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">No. Pesanan</span><span>: ' + esc(hdr.kd_po || '-') + '</span></div>'
                + '</div>'
                + '<div>'
                + '<div class="pb-rincian-kv"><span class="k">Pemasok</span><span>: ' + esc(hdr.nama_suplier) + ' (' + esc(hdr.kd_suplier) + ')</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Gudang</span><span>: ' + esc(hdr.nama_gudang) + ' <span class="badge badge-light ml-2">' + esc(hdr.status_dokumen) + '</span></span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Catatan</span><span>: ' + esc(hdr.catatan || '-') + '</span></div>'
                + '</div>'
                + '<div>'
                + '<div class="pb-rincian-kv"><span class="k">Mata Uang</span><span>: IDR 1.00</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Proyek</span><span>: -</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Departemen</span><span>: Head Quarter</span></div>'
                + '</div>'
                + '</div>';

            html += '<table class="pb-table"><thead><tr>'
                + '<th>Deskripsi</th>'
                + '<th class="num">Dipesan</th>'
                + '<th class="num">Diterima</th>'
                + '<th>Satuan</th>'
                + '<th class="num">Harga Satuan</th>'
                + '<th class="num">Disc.</th>'
                + '<th class="num">Sub Total</th>'
                + '<th class="num">Pajak</th>'
                + '</tr></thead><tbody>';

            (hdr.items || []).forEach(function (it) {
                html += '<tr>'
                    + '<td><b>' + esc(it.kd_barang) + '</b> &nbsp; ' + esc(it.nama_barang) + '</td>'
                    + '<td class="num">' + fmtNum(it.dipesan, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.diterima, 2) + '</td>'
                    + '<td>' + esc(it.satuan) + '</td>'
                    + '<td class="num">' + fmtNum(it.harga_satuan, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.diskon_persen, 0) + '%</td>'
                    + '<td class="num">' + fmtNum(it.subtotal, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.pajak_persen, 0) + '%</td>'
                    + '</tr>';
            });

            html += '</tbody></table>';

            html += '<div class="pb-rincian-footer">'
                + '<div>'
                + '<div><b>Bag. Pembelian :</b> ' + esc(hdr.bag_pembelian || '-') + '</div>'
                + '<div><b>Input Oleh :</b> ' + esc(hdr.input_oleh || '-') + '</div>'
                + '</div>'
                + '<div class="pb-rincian-summary">'
                + '<div class="row-sum"><span>Biaya Kirim :</span><span>' + fmtNum(hdr.biaya_kirim, 2) + '</span></div>'
                + '<div class="row-sum"><span>Diskon Akhir :</span><span>' + fmtNum(hdr.diskon_akhir, 2) + '</span></div>'
                + '<div class="row-sum bold"><span>Total :</span><span>' + fmtNum(hdr.total, 2) + '</span></div>'
                + '</div>'
                + '</div>'
                + '</div>';
        });

        return html;
    }

    // 6. Render Pembelian - Sederhana
    function renderPembelianSederhana(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data pembelian pada periode ini.</div>';
        }
        let html = '<table class="pb-table"><thead><tr>'
            + '<th>Tanggal</th>'
            + '<th>No. Ref.</th>'
            + '<th>No. Pesanan</th>'
            + '<th>Nama Pemasok</th>'
            + '<th>Mata Uang</th>'
            + '<th class="num">Jumlah</th>'
            + '<th class="num">Jumlah (IDR)</th>'
            + '</tr></thead><tbody>';

        let total = 0;
        rows.forEach(function (r) {
            const jml = parseFloat(r.jumlah) || 0;
            total += jml;
            html += '<tr>'
                + '<td>' + esc(fmtDate(r.tanggal)) + '</td>'
                + '<td>' + esc(r.no_ref) + '</td>'
                + '<td>' + esc(r.no_pesanan || '') + '</td>'
                + '<td>' + esc(r.nama_pemasok) + '</td>'
                + '<td>' + esc(r.mata_uang || 'IDR') + '</td>'
                + '<td class="num">' + fmtNum(jml, 2) + '</td>'
                + '<td class="num">' + fmtNum(jml, 2) + ' &#10003;</td>'
                + '</tr>';
        });

        html += '</tbody><tfoot><tr>'
            + '<td colspan="5" class="text-right">Total :</td>'
            + '<td class="num">' + fmtNum(total, 2) + '</td>'
            + '<td class="num">' + fmtNum(total, 2) + '</td>'
            + '</tr></tfoot></table>';
        return html;
    }

    // 7. Render Pembelian Bernilai Negatif - Rangkuman & Pengiriman Pembelian - Rangkuman
    function renderPembelianRangkuman(rows, isPengiriman) {
        const colRef = isPengiriman ? 'Reference' : 'No. Referensi';
        const colPo  = isPengiriman ? 'No. Pemesanan' : 'No. Pesanan';
        const colSup = isPengiriman ? 'Vendor' : 'Pemasok';

        let html = '<table class="pb-table"><thead><tr>'
            + '<th>Tanggal</th>'
            + '<th>' + colRef + '</th>'
            + '<th>' + colPo + '</th>'
            + '<th>' + colSup + '</th>'
            + '<th>Mata Uang</th>'
            + '<th class="num">Sub Total</th>'
            + '<th class="num">Diskon</th>'
            + '<th class="num">Pajak</th>'
            + '<th class="num">Total Pembelian</th>'
            + '<th class="num">Pembayaran</th>'
            + '<th class="num">Saldo</th>'
            + '</tr></thead><tbody>';

        let sumSub = 0, sumDisc = 0, sumTax = 0, sumTot = 0, sumPay = 0, sumSaldo = 0;

        rows.forEach(function (r) {
            const sub  = parseFloat(r.sub_total) || 0;
            const disc = parseFloat(r.diskon) || 0;
            const tax  = parseFloat(r.pajak) || 0;
            const tot  = parseFloat(r.total_pembelian) || 0;
            const pay  = parseFloat(r.pembayaran) || 0;
            const sal  = parseFloat(r.saldo) || 0;

            sumSub += sub; sumDisc += disc; sumTax += tax;
            sumTot += tot; sumPay += pay; sumSaldo += sal;

            html += '<tr>'
                + '<td>' + esc(fmtDate(r.tanggal)) + '</td>'
                + '<td>' + esc(r.no_referensi) + '</td>'
                + '<td>' + esc(r.no_pesanan || '') + '</td>'
                + '<td>' + esc(r.pemasok) + '</td>'
                + '<td>' + esc(r.mata_uang || 'IDR') + '</td>'
                + '<td class="num">' + fmtNum(sub, 2) + '</td>'
                + '<td class="num">' + fmtNum(disc, 2) + '</td>'
                + '<td class="num">' + fmtNum(tax, 2) + '</td>'
                + '<td class="num">' + fmtNum(tot, 2) + '</td>'
                + '<td class="num">' + fmtNum(pay, 2) + '</td>'
                + '<td class="num">' + fmtNum(sal, 2) + '</td>'
                + '</tr>';
        });

        html += '</tbody><tfoot><tr>'
            + '<td colspan="4" class="text-right">Total :</td>'
            + '<td>IDR</td>'
            + '<td class="num">' + fmtNum(sumSub, 2) + '</td>'
            + '<td class="num">' + fmtNum(sumDisc, 2) + '</td>'
            + '<td class="num">' + fmtNum(sumTax, 2) + '</td>'
            + '<td class="num">' + fmtNum(sumTot, 2) + '</td>'
            + '<td class="num">' + fmtNum(sumPay, 2) + '</td>'
            + '<td class="num">' + fmtNum(sumSaldo, 2) + '</td>'
            + '</tr></tfoot></table>';
        return html;
    }

    // 8. Render Purchase Return
    function renderPurchaseReturn(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data retur pembelian pada periode ini.</div>';
        }
        let html = '<table class="pb-table"><thead><tr>'
            + '<th>Tanggal</th>'
            + '<th>PO</th>'
            + '<th>Faktur</th>'
            + '<th>Kode Supplier</th>'
            + '<th>Nama Supplier</th>'
            + '<th>Kode Barang</th>'
            + '<th>Nama Barang</th>'
            + '<th class="num">Qty</th>'
            + '<th class="num">On Order</th>'
            + '<th>Unit</th>'
            + '<th class="num">Harga</th>'
            + '<th class="num">Total</th>'
            + '<th class="num">VAT</th>'
            + '</tr></thead><tbody>';

        rows.forEach(function (hdr) {
            let subTot = 0;
            (hdr.items || []).forEach(function (it) {
                const dpp = parseFloat(it.dpp) || 0;
                const tot = parseFloat(it.total) || 0;
                subTot += tot;
                html += '<tr>'
                    + '<td>' + esc(fmtDate(hdr.tanggal)) + '</td>'
                    + '<td>' + esc(hdr.no_po) + '</td>'
                    + '<td>' + esc(hdr.no_faktur) + '</td>'
                    + '<td>' + esc(hdr.kd_suplier) + '</td>'
                    + '<td>' + esc(hdr.nama_suplier) + '</td>'
                    + '<td>' + esc(it.kd_barang) + '</td>'
                    + '<td>' + esc(it.nama_barang) + '</td>'
                    + '<td class="num">' + fmtNum(it.qty, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.on_order, 2) + '</td>'
                    + '<td>' + esc(it.unit) + '</td>'
                    + '<td class="num">' + fmtNum(it.harga, 2) + '</td>'
                    + '<td class="num">' + fmtNum(dpp > 0 ? dpp : tot, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.vat_persen, 0) + '%</td>'
                    + '</tr>';
            });
            html += '<tr class="pb-lpb-total">'
                + '<td colspan="11" class="text-right">Total:</td>'
                + '<td class="num">' + fmtNum(hdr.total > 0 ? hdr.total : subTot, 2) + '</td>'
                + '<td></td>'
                + '</tr>';
        });

        html += '</tbody></table>';
        return html;
    }

    // 9. Render Retur Pembelian - Rincian
    function renderReturPembelianRincian(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data retur pembelian pada periode ini.</div>';
        }
        let html = '';

        rows.forEach(function (hdr) {
            html += '<div class="pb-rincian-card">'
                + '<div class="pb-rincian-grid">'
                + '<div>'
                + '<div class="pb-rincian-kv"><span class="k">Tanggal</span><span>: ' + esc(fmtDate(hdr.tanggal)) + '</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">No. Referensi</span><span>: ' + esc(hdr.no_faktur) + '</span></div>'
                + '</div>'
                + '<div>'
                + '<div class="pb-rincian-kv"><span class="k">Pemasok</span><span>: ' + esc(hdr.nama_suplier) + ' (' + esc(hdr.kd_suplier) + ')</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Gudang</span><span>: ' + esc(hdr.nama_gudang) + ' <span class="badge badge-light ml-2">' + esc(hdr.status_dokumen) + '</span></span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Catatan</span><span>: ' + esc(hdr.catatan || '-') + '</span></div>'
                + '</div>'
                + '<div>'
                + '<div class="pb-rincian-kv"><span class="k">Mata Uang</span><span>: IDR 1.00</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Proyek</span><span>: -</span></div>'
                + '<div class="pb-rincian-kv"><span class="k">Departemen</span><span>: Head Quarter</span></div>'
                + '</div>'
                + '</div>';

            html += '<table class="pb-table"><thead><tr>'
                + '<th>Deskripsi</th>'
                + '<th class="num">Retur</th>'
                + '<th class="num">Dipesan</th>'
                + '<th>Satuan</th>'
                + '<th class="num">Harga Satuan</th>'
                + '<th class="num">Disc.</th>'
                + '<th class="num">Sub Total</th>'
                + '<th class="num">Pajak</th>'
                + '</tr></thead><tbody>';

            (hdr.items || []).forEach(function (it) {
                const sub = parseFloat(it.dpp) > 0 ? parseFloat(it.dpp) : parseFloat(it.total);
                html += '<tr>'
                    + '<td><b>' + esc(it.kd_barang) + '</b> &nbsp; ' + esc(it.nama_barang) + '</td>'
                    + '<td class="num">' + fmtNum(it.qty, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.on_order, 2) + '</td>'
                    + '<td>' + esc(it.unit) + '</td>'
                    + '<td class="num">' + fmtNum(it.harga, 2) + '</td>'
                    + '<td class="num">0%</td>'
                    + '<td class="num">' + fmtNum(sub, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.vat_persen, 0) + '%</td>'
                    + '</tr>';
            });

            html += '</tbody></table>';

            html += '<div class="pb-rincian-footer">'
                + '<div>'
                + '<div><b>Bag. Pembelian :</b> ' + esc(hdr.bag_pembelian || '-') + '</div>'
                + '<div><b>Input oleh :</b> ' + esc(hdr.input_oleh || '-') + '</div>'
                + '</div>'
                + '<div class="pb-rincian-summary">'
                + '<div class="row-sum"><span>Biaya Kirim :</span><span>' + fmtNum(hdr.biaya_kirim, 2) + '</span></div>'
                + '<div class="row-sum"><span>Diskon Akhir :</span><span>' + fmtNum(hdr.diskon_akhir, 2) + '</span></div>'
                + '<div class="row-sum bold"><span>Total :</span><span>' + fmtNum(hdr.total, 2) + '</span></div>'
                + '<div class="row-sum"><span>Pembayaran :</span><span>' + fmtNum(hdr.pembayaran, 2) + '</span></div>'
                + '<div class="row-sum bold"><span>Saldo :</span><span>' + fmtNum(hdr.saldo, 2) + '</span></div>'
                + '</div>'
                + '</div>'
                + '</div>';
        });

        return html;
    }

    // 10. Render Retur Pembelian - Sederhana & Pesanan Pembelian - Sederhana
    function renderSederhanaDua(rows, isPesanan) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data pada periode ini.</div>';
        }
        let html = '<table class="pb-table"><thead><tr>'
            + '<th>Tanggal</th>'
            + '<th>No. Pesanan</th>'
            + '<th>No. Permintaan</th>'
            + '<th>Nama Pemasok</th>'
            + '<th>Mata Uang</th>'
            + '<th class="num">Jumlah</th>'
            + '<th class="num">Jumlah (IDR)</th>'
            + '</tr></thead><tbody>';

        let total = 0;
        rows.forEach(function (r) {
            const jml = parseFloat(r.jumlah) || 0;
            total += jml;
            const check = isPesanan
                ? ((String(r.status || '').toUpperCase().indexOf('CLOSE') >= 0 || jml > 0) ? ' &#10003;' : '')
                : ' &#10003;';
            html += '<tr>'
                + '<td>' + esc(fmtDate(r.tanggal)) + '</td>'
                + '<td>' + esc(r.no_pesanan) + '</td>'
                + '<td>' + esc(r.no_permintaan || '') + '</td>'
                + '<td>' + esc(r.nama_pemasok) + '</td>'
                + '<td>' + esc(r.mata_uang || 'IDR') + '</td>'
                + '<td class="num">' + fmtNum(jml, 2) + '</td>'
                + '<td class="num">' + fmtNum(jml, 2) + check + '</td>'
                + '</tr>';
        });

        html += '</tbody><tfoot><tr>'
            + '<td colspan="4" class="text-right">Total :</td>'
            + '<td>IDR</td>'
            + '<td class="num">' + fmtNum(total, 2) + '</td>'
            + '<td class="num">' + fmtNum(total, 2) + '</td>'
            + '</tr></tfoot></table>';
        return html;
    }

    // 11. Render Pesanan Pembelian - Status Pengiriman
    function renderPesananStatusPengiriman(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data pesanan pembelian pada periode ini.</div>';
        }
        let html = '<table class="pb-table"><thead><tr>'
            + '<th>Tanggal</th>'
            + '<th>No. Pesanan / Deskripsi</th>'
            + '<th>Pemasok</th>'
            + '<th>Bag. Pembelian / Unit</th>'
            + '<th class="num">Pesanan</th>'
            + '<th class="num">Telah Tiba</th>'
            + '<th class="num">Sisa Pesanan / Total</th>'
            + '<th style="width:60px;text-align:center;">Status</th>'
            + '</tr></thead><tbody>';

        rows.forEach(function (hdr) {
            html += '<tr class="pb-jurnal-head">'
                + '<td>' + esc(fmtDate(hdr.tanggal)) + '</td>'
                + '<td>' + esc(hdr.no_pesanan) + '</td>'
                + '<td>' + esc(hdr.pemasok) + '</td>'
                + '<td>' + esc(hdr.bag_pembelian) + '</td>'
                + '<td></td>'
                + '<td class="text-right">IDR</td>'
                + '<td class="num">' + fmtNum(hdr.total, 2) + '</td>'
                + '<td style="text-align:center;">' + esc(hdr.status_code || '') + '</td>'
                + '</tr>';

            (hdr.items || []).forEach(function (it) {
                html += '<tr>'
                    + '<td></td>'
                    + '<td colspan="2"><b>' + esc(it.kd_barang) + '</b> &nbsp; ' + esc(it.nama_barang) + '</td>'
                    + '<td>' + esc(it.unit) + '</td>'
                    + '<td class="num">' + fmtNum(it.pesanan, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.telah_tiba, 2) + '</td>'
                    + '<td class="num">' + fmtNum(it.sisa_pesanan, 2) + '</td>'
                    + '<td></td>'
                    + '</tr>';
            });
        });

        html += '</tbody></table>';
        return html;
    }

    function loadReport() {
        const startDate = $('#filterStartDate').val();
        const endDate = $('#filterEndDate').val();
        const search = $('#filterSearch').val().trim();

        $('#pbPeriodLabel').text(fmtPeriodText(startDate, endDate));
        $('#pbStatusInfo').text('Memuat data...');
        $('#pbContainer').html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat laporan...</div>');

        $.getJSON(ENDPOINT, {
            jenis: SLUG,
            start_date: startDate,
            end_date: endDate,
            search: search
        }).done(function (res) {
            const rows = (res && res.rows) ? res.rows : [];
            $('#pbStatusInfo').text('Menampilkan ' + rows.length + ' data.');

            if (SLUG === 'jurnal-pembelian' || SLUG === 'jurnal-diskon') {
                $('#pbContainer').html(renderJurnalPembelian(rows));
            } else if (SLUG === 'penerimaan-barang-harian') {
                $('#pbContainer').html(renderPenerimaanBarangHarian(rows));
            } else if (SLUG === 'rincian') {
                $('#pbContainer').html(renderPembelianRincian(rows));
            } else if (SLUG === 'efaktur') {
                $('#pbContainer').html(renderEfakturPembelian(rows));
            } else if (SLUG === 'pesanan-rincian') {
                $('#pbContainer').html(renderPesananPembelianRincian(rows));
            } else if (SLUG === 'sederhana') {
                $('#pbContainer').html(renderPembelianSederhana(rows));
            } else if (SLUG === 'negatif-rangkuman') {
                $('#pbContainer').html(renderPembelianRangkuman(rows, false));
            } else if (SLUG === 'pengiriman-rangkuman') {
                $('#pbContainer').html(renderPembelianRangkuman(rows, true));
            } else if (SLUG === 'purchase-return') {
                $('#pbContainer').html(renderPurchaseReturn(rows));
            } else if (SLUG === 'retur-rincian') {
                $('#pbContainer').html(renderReturPembelianRincian(rows));
            } else if (SLUG === 'retur-sederhana') {
                $('#pbContainer').html(renderSederhanaDua(rows, false));
            } else if (SLUG === 'pesanan-sederhana') {
                $('#pbContainer').html(renderSederhanaDua(rows, true));
            } else if (SLUG === 'pesanan-status-pengiriman') {
                $('#pbContainer').html(renderPesananStatusPengiriman(rows));
            }
        }).fail(function () {
            $('#pbStatusInfo').text('Gagal memuat data.');
            $('#pbContainer').html('<div class="alert alert-danger">Gagal memuat data laporan.</div>');
        });
    }

    $(document).ready(function () {
        $('.zahir-filter-tab').on('click', function () {
            $('.zahir-filter-tab').removeClass('active');
            $(this).addClass('active');
            const tab = $(this).data('tab');
            if (tab === 'standar') {
                $('#tabStandar').show();
                $('#tabPencarian').hide();
            } else {
                $('#tabStandar').hide();
                $('#tabPencarian').show();
            }
        });

        $('#btnOpenFilter').on('click', function () {
            $('#modalFilterData').modal('show');
        });

        $('#btnApplyFilter').on('click', function () {
            $('#modalFilterData').modal('hide');
            loadReport();
        });

        $('#modalFilterData').modal('show');
    });
})();
</script>
</body>
