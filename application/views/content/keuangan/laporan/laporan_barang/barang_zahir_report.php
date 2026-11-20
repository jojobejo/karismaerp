<?php
$meta  = $report_meta ?? ['slug' => 'kartu-stok-umum', 'title' => 'Kartu Stok - Umum'];
$slug  = $meta['slug'];
$title = $meta['title'];
?>

<style>
.br-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.br-paper {
    background: #fff;
    border: 1px solid #d9dee3;
    border-radius: 6px;
    padding: 24px 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    color: #1f2937;
    font-family: Arial, Helvetica, sans-serif;
}

.br-meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #374151;
    margin-bottom: 4px;
}

.br-header-center {
    text-align: center;
    margin-bottom: 18px;
}

.br-company {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}

.br-title {
    font-size: 20px;
    font-weight: 700;
    color: #0284c7;
    margin-bottom: 2px;
}

.br-period {
    font-size: 12px;
    font-weight: 600;
    color: #b91c1c;
}

.br-wh-heading {
    text-align: center;
    font-size: 14px;
    font-weight: 700;
    color: #b91c1c;
    margin: 16px 0 8px;
}

.br-item-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12.5px;
    font-weight: 700;
    margin: 12px 0 4px;
}

.br-item-bar .left {
    color: #0284c7;
}

.br-item-bar .right {
    color: #b91c1c;
}

.br-grp-heading {
    text-align: center;
    font-size: 13px;
    font-weight: 700;
    color: #0284c7;
    margin: 12px 0 4px;
}

.br-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
    margin-bottom: 10px;
}

.br-table thead th {
    background: #e5e7eb;
    border-top: 1.5px solid #4b5563;
    border-bottom: 1.5px solid #4b5563;
    padding: 6px 8px;
    font-weight: 700;
    color: #111827;
    text-align: left;
    white-space: nowrap;
}

.br-table thead th.num,
.br-table tbody td.num,
.br-table tfoot td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.br-table tbody td {
    padding: 4px 8px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}

.br-table tfoot td,
.br-subtotal td {
    font-weight: 700;
    background: #f3f4f6;
    border-top: 1px solid #6b7280;
    border-bottom: 1.5px solid #374151;
    padding: 5px 8px;
}

.br-jurnal-head td {
    font-weight: 700;
    background: #f8fafc;
    border-top: 1px solid #cbd5e1;
    color: #111827;
    padding-top: 7px !important;
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

    .br-paper {
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
                        <a href="<?= base_url('laporan/barang') ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Laporan Barang
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="br-toolbar no-print">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" id="btnOpenFilter">
                            <i class="fas fa-filter mr-1"></i> Filter Data
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak Laporan
                        </button>
                    </div>
                    <div class="text-muted small" id="brStatusInfo">
                        Pilih filter untuk menampilkan laporan.
                    </div>
                </div>

                <div class="br-paper">
                    <div class="br-meta">
                        <span><?= date('g:i A, F j, Y') ?></span>
                        <span><?= htmlspecialchars($title) ?></span>
                    </div>
                    <div class="br-header-center">
                        <div class="br-company">KARISMA <?= date('Y') ?></div>
                        <div class="br-title"><?= htmlspecialchars($title) ?></div>
                        <div class="br-period" id="brPeriodLabel">-</div>
                    </div>

                    <div id="brContainer">
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
                        <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Kode Barang, Nama Barang, Gudang, Kelompok...">
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
        if (/^\d{5}$/.test(k)) return k.slice(0, 3) + '-' + k.slice(3);
        return k;
    }

    // 1. Kartu Stok - Umum & Kartu Stok Per Pemasok Per Gudang
    function renderKartuStok(rows, byWarehouse) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data mutasi stok pada periode ini.</div>';
        }
        let html = '';
        let lastWh = '';

        rows.forEach(function (it) {
            if (byWarehouse && it.nama_gudang !== lastWh) {
                lastWh = it.nama_gudang;
                html += '<div class="br-wh-heading">' + esc(lastWh) + '</div>';
            }

            html += '<div class="br-item-bar">'
                + '<div class="left">' + esc(it.kode_barang) + ' &nbsp;|&nbsp; ' + esc(it.nama_barang) + '</div>'
                + '<div class="right">' + esc(it.kelompok) + '</div>'
                + '</div>';

            html += '<table class="br-table"><thead><tr>'
                + '<th>Tanggal</th>'
                + '<th>Nomor Referensi</th>'
                + '<th>Unit</th>'
                + '<th class="num">Masuk</th>'
                + '<th class="num">Harga Pokok</th>'
                + '<th class="num">Nilai</th>'
                + '<th class="num">Keluar</th>'
                + '<th class="num">Harga Pokok</th>'
                + '<th class="num">Nilai</th>'
                + '<th class="num">Saldo</th>'
                + '<th class="num">Harga Pokok</th>'
                + '<th class="num">Nilai</th>'
                + '</tr></thead><tbody>';

            const opQty = parseFloat(it.opening_qty) || 0;
            const hpp   = parseFloat(it.harga_pokok) || 0;
            html += '<tr>'
                + '<td>0</td>'
                + '<td>O/B &nbsp; Opening Balance:</td>'
                + '<td>' + esc(it.unit) + '</td>'
                + '<td colspan="6"></td>'
                + '<td class="num">' + fmtNum(opQty) + '</td>'
                + '<td class="num">' + (opQty ? fmtNum(hpp) : '') + '</td>'
                + '<td class="num">' + fmtNum(opQty * hpp) + '</td>'
                + '</tr>';

            let sumMasukVal = 0, sumKeluarVal = 0;
            (it.lines || []).forEach(function (ln) {
                if (ln.masuk > 0) sumMasukVal += parseFloat(ln.nilai) || 0;
                if (ln.keluar > 0) sumKeluarVal += parseFloat(ln.nilai) || 0;

                html += '<tr>'
                    + '<td>' + esc(fmtDate(ln.tanggal)) + '</td>'
                    + '<td>' + esc(ln.tp) + ' &nbsp; ' + esc(ln.no_referensi) + '</td>'
                    + '<td>' + esc(it.unit) + '</td>'
                    + '<td class="num">' + (ln.masuk > 0 ? fmtNum(ln.masuk, 3) : '') + '</td>'
                    + '<td class="num">' + (ln.masuk > 0 ? fmtNum(ln.harga_pokok) : '') + '</td>'
                    + '<td class="num">' + (ln.masuk > 0 ? fmtNum(ln.nilai) : '') + '</td>'
                    + '<td class="num">' + (ln.keluar > 0 ? fmtNum(ln.keluar) : '') + '</td>'
                    + '<td class="num">' + (ln.keluar > 0 ? fmtNum(ln.harga_pokok) : '') + '</td>'
                    + '<td class="num">' + (ln.keluar > 0 ? fmtNum(ln.nilai) : '') + '</td>'
                    + '<td class="num">' + fmtNum(ln.saldo) + '</td>'
                    + '<td class="num">' + (ln.saldo > 0 ? fmtNum(ln.harga_pokok) : '') + '</td>'
                    + '<td class="num">' + (ln.saldo > 0 ? fmtNum(ln.saldo_nilai) : '') + '</td>'
                    + '</tr>';
            });

            const sal = parseFloat(it.saldo_qty) || 0;
            const labelFoot = byWarehouse ? 'Sub Total :' : ('Total ' + esc(it.nama_barang) + ' :');
            html += '</tbody><tfoot><tr>'
                + '<td colspan="3" class="text-right">' + labelFoot + '</td>'
                + '<td class="num">' + fmtNum(it.total_masuk) + '</td>'
                + '<td class="num">' + fmtNum(hpp) + '</td>'
                + '<td class="num">' + fmtNum(sumMasukVal) + '</td>'
                + '<td class="num">' + fmtNum(it.total_keluar) + '</td>'
                + '<td class="num">' + fmtNum(hpp) + '</td>'
                + '<td class="num">' + fmtNum(sumKeluarVal) + '</td>'
                + '<td class="num">' + fmtNum(sal) + '</td>'
                + '<td class="num">' + fmtNum(sal > 0 ? hpp : 0) + '</td>'
                + '<td class="num">' + fmtNum(sal * hpp) + '</td>'
                + '</tr></tfoot></table>';
        });

        return html;
    }

    // 2. Barang Dibeli & Barang Terjual
    function renderBarangRekap(rows, isPenjualan) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data pada periode ini.</div>';
        }
        const qtyLabel = isPenjualan ? 'Terjual' : 'Jumlah';
        const valLabel = isPenjualan ? 'Nilai Penjualan' : 'Nilai Pembelian';

        const groups = {};
        rows.forEach(function (r) {
            const g = r.kelompok || 'Barang Dagangan';
            if (!groups[g]) groups[g] = [];
            groups[g].push(r);
        });

        let html = '';
        Object.keys(groups).forEach(function (gName) {
            html += '<div class="br-grp-heading">' + esc(gName) + '</div>';
            html += '<table class="br-table"><thead><tr>'
                + '<th>Kode Barang</th>'
                + '<th>Nama Barang</th>'
                + '<th>Unit</th>'
                + '<th class="num">' + qtyLabel + '</th>'
                + '<th>Mata Uang</th>'
                + '<th class="num">' + valLabel + '</th>'
                + '</tr></thead><tbody>';

            let sumVal = 0;
            groups[gName].forEach(function (r) {
                const v = parseFloat(r.nilai) || 0;
                sumVal += v;
                html += '<tr>'
                    + '<td>' + esc(r.kode_barang) + '</td>'
                    + '<td>' + esc(r.nama_barang) + '</td>'
                    + '<td>' + esc(r.unit) + '</td>'
                    + '<td class="num">' + fmtNum(r.jumlah) + '</td>'
                    + '<td>IDR</td>'
                    + '<td class="num">' + fmtNum(v) + '</td>'
                    + '</tr>';
            });

            html += '</tbody><tfoot><tr>'
                + '<td colspan="4" class="text-right">Total ' + esc(gName) + ' :</td>'
                + '<td>IDR</td>'
                + '<td class="num">' + fmtNum(sumVal) + '</td>'
                + '</tr></tfoot></table>';
        });

        return html;
    }

    // 3. Daftar Barang - Umum & Daftar Harga Jual Barang
    function renderDaftarBarangUmum(rows, isHargaJual) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data barang.</div>';
        }
        const groups = {};
        rows.forEach(function (r) {
            const g = r.kelompok || 'Barang Dagangan';
            if (!groups[g]) groups[g] = [];
            groups[g].push(r);
        });

        let html = '';
        Object.keys(groups).forEach(function (gName) {
            html += '<div class="br-grp-heading">' + esc(gName) + '</div>';
            if (isHargaJual) {
                html += '<table class="br-table"><thead><tr>'
                    + '<th>Kode Barang</th>'
                    + '<th>Nama Barang</th>'
                    + '<th>Unit</th>'
                    + '<th class="num">Tersedia</th>'
                    + '<th class="num">Dipesan</th>'
                    + '<th>Mata Uang</th>'
                    + '<th class="num">Harga Jual</th>'
                    + '</tr></thead><tbody>';
                groups[gName].forEach(function (r) {
                    html += '<tr>'
                        + '<td>' + esc(r.kode_barang) + '</td>'
                        + '<td>' + esc(r.nama_barang) + '</td>'
                        + '<td>' + esc(r.unit) + '</td>'
                        + '<td class="num">' + fmtNum(r.tersedia) + '</td>'
                        + '<td class="num">' + fmtNum(r.dipesan) + '</td>'
                        + '<td>IDR</td>'
                        + '<td class="num">' + fmtNum(r.harga_satuan) + '</td>'
                        + '</tr>';
                });
                html += '</tbody></table>';
            } else {
                html += '<table class="br-table"><thead><tr>'
                    + '<th>Kode Barang</th>'
                    + '<th>Nama Barang</th>'
                    + '<th class="num">Tersedia</th>'
                    + '<th>Unit</th>'
                    + '<th>Mata Uang</th>'
                    + '<th class="num">Harga Satuan</th>'
                    + '<th class="num">Total Nilai</th>'
                    + '</tr></thead><tbody>';
                let totVal = 0;
                groups[gName].forEach(function (r) {
                    const tv = parseFloat(r.total_nilai) || 0;
                    totVal += tv;
                    html += '<tr>'
                        + '<td>' + esc(r.kode_barang) + '</td>'
                        + '<td>' + esc(r.nama_barang) + '</td>'
                        + '<td class="num">' + fmtNum(r.tersedia) + '</td>'
                        + '<td>' + esc(r.unit) + '</td>'
                        + '<td>IDR</td>'
                        + '<td class="num">' + fmtNum(r.harga_satuan) + '</td>'
                        + '<td class="num">' + fmtNum(tv) + '</td>'
                        + '</tr>';
                });
                html += '</tbody><tfoot><tr>'
                    + '<td colspan="5" class="text-right">Total ' + esc(gName) + ' :</td>'
                    + '<td></td>'
                    + '<td class="num">' + fmtNum(totVal) + '</td>'
                    + '</tr></tfoot></table>';
            }
        });

        return html;
    }

    // 4. Analisa Per Gudang, Daftar Barang Per Gudang, Formulir Stok Opname
    function renderPerGudang(rows, mode) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data barang per gudang.</div>';
        }
        const byWh = {};
        rows.forEach(function (r) {
            const wh = r.nama_gudang || 'Gudang';
            const grp = r.kelompok || 'Barang Dagangan';
            if (!byWh[wh]) byWh[wh] = {};
            if (!byWh[wh][grp]) byWh[wh][grp] = [];
            byWh[wh][grp].push(r);
        });

        let html = '';
        Object.keys(byWh).forEach(function (whName) {
            html += '<div class="br-wh-heading">' + esc(whName) + '</div>';
            let whTotal = 0;

            Object.keys(byWh[whName]).forEach(function (grpName) {
                html += '<div class="br-grp-heading">' + esc(grpName) + '</div>';

                if (mode === 'analisa') {
                    html += '<table class="br-table"><thead><tr>'
                        + '<th>Kode Barang</th>'
                        + '<th>Nama Barang</th>'
                        + '<th>Unit</th>'
                        + '<th class="num">Tersedia</th>'
                        + '<th class="num">Dipesan ke Pemasok</th>'
                        + '<th class="num">Pesanan Pelanggan</th>'
                        + '</tr></thead><tbody>';
                    byWh[whName][grpName].forEach(function (r) {
                        html += '<tr>'
                            + '<td>' + esc(r.kode_barang) + '</td>'
                            + '<td>' + esc(r.nama_barang) + '</td>'
                            + '<td>' + esc(r.unit) + '</td>'
                            + '<td class="num">' + fmtNum(r.tersedia) + '</td>'
                            + '<td class="num">' + fmtNum(r.dipesan_ke_pemasok) + '</td>'
                            + '<td class="num">' + fmtNum(r.pesanan_pelanggan) + '</td>'
                            + '</tr>';
                    });
                    html += '</tbody></table>';
                } else if (mode === 'opname') {
                    html += '<table class="br-table"><thead><tr>'
                        + '<th>Kode Barang</th>'
                        + '<th>Nama Barang</th>'
                        + '<th>Unit</th>'
                        + '<th class="num" style="width:120px;">Jumlah</th>'
                        + '</tr></thead><tbody>';
                    byWh[whName][grpName].forEach(function (r) {
                        html += '<tr>'
                            + '<td>' + esc(r.kode_barang) + '</td>'
                            + '<td>' + esc(r.nama_barang) + '</td>'
                            + '<td>' + esc(r.unit) + '</td>'
                            + '<td class="num" style="border-bottom:1px dotted #94a3b8;"></td>'
                            + '</tr>';
                    });
                    html += '</tbody></table>';
                } else {
                    html += '<table class="br-table"><thead><tr>'
                        + '<th>Kode Barang</th>'
                        + '<th>Nama Barang</th>'
                        + '<th class="num">Tersedia</th>'
                        + '<th>Unit</th>'
                        + '<th>Mata Uang</th>'
                        + '<th class="num">Harga Satuan</th>'
                        + '<th class="num">Total Nilai</th>'
                        + '</tr></thead><tbody>';
                    let grpTot = 0;
                    byWh[whName][grpName].forEach(function (r) {
                        const tv = parseFloat(r.total_nilai) || 0;
                        grpTot += tv;
                        whTotal += tv;
                        html += '<tr>'
                            + '<td>' + esc(r.kode_barang) + '</td>'
                            + '<td>' + esc(r.nama_barang) + '</td>'
                            + '<td class="num">' + fmtNum(r.tersedia) + '</td>'
                            + '<td>' + esc(r.unit) + '</td>'
                            + '<td>IDR</td>'
                            + '<td class="num">' + fmtNum(r.harga_satuan) + '</td>'
                            + '<td class="num">' + fmtNum(tv) + '</td>'
                            + '</tr>';
                    });
                    html += '</tbody><tfoot><tr>'
                        + '<td colspan="5" class="text-right">Total ' + esc(grpName) + ' :</td>'
                        + '<td></td>'
                        + '<td class="num">' + fmtNum(grpTot) + '</td>'
                        + '</tr></tfoot></table>';
                }
            });

            if (mode === 'daftar') {
                html += '<table class="br-table"><tfoot><tr>'
                    + '<td colspan="6" class="text-right">Total ' + esc(whName) + ' :</td>'
                    + '<td class="num">' + fmtNum(whTotal) + '</td>'
                    + '</tr></tfoot></table>';
            }
        });

        return html;
    }

    // 5. Jurnal Persediaan
    function renderJurnalPersediaan(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data jurnal persediaan pada periode ini.</div>';
        }
        let html = '<table class="br-table"><thead><tr>'
            + '<th style="width:95px;">Tanggal</th>'
            + '<th>Deskripsi</th>'
            + '<th style="width:110px;">Departemen</th>'
            + '<th class="num" style="width:130px;">Debit</th>'
            + '<th class="num" style="width:130px;">Credit</th>'
            + '<th style="width:100px;">Proyek</th>'
            + '</tr></thead><tbody>';

        rows.forEach(function (hdr) {
            html += '<tr class="br-jurnal-head">'
                + '<td>' + esc(fmtDate(hdr.tanggal_transaksi)) + '</td>'
                + '<td colspan="5">' + esc(hdr.keterangan || hdr.nomor_jurnal) + '</td>'
                + '</tr>';
            (hdr.details || []).forEach(function (d) {
                const deb = parseFloat(d.debit) || 0;
                const kre = parseFloat(d.kredit) || 0;
                const ref = d.nomor_dokumen || hdr.source_no || hdr.nomor_jurnal || '';
                html += '<tr>'
                    + '<td></td>'
                    + '<td><span class="text-muted mr-2">' + esc(ref) + '</span><span class="mr-2">' + esc(fmtKodeAkun(d.kode_akun)) + '</span>' + esc(d.nama_akun) + '</td>'
                    + '<td>' + esc(d.departemen || '-') + '</td>'
                    + '<td class="num">' + (deb > 0 ? fmtNum(deb) : '') + '</td>'
                    + '<td class="num">' + (kre > 0 ? fmtNum(kre) : '') + '</td>'
                    + '<td>' + esc(d.proyek || '-') + '</td>'
                    + '</tr>';
            });
        });

        html += '</tbody></table>';
        return html;
    }

    // 6. Transaksi Barang - Semua Transaksi & Transaksi - Pemakaian / Penyesuaian Barang
    function renderTransaksiBarang(rows, showTotal) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data transaksi barang pada periode ini.</div>';
        }
        let html = '';
        rows.forEach(function (hdr) {
            html += '<div style="border-top:1.5px solid #374151;border-bottom:1.5px solid #374151;padding:10px 0;margin-bottom:14px;">'
                + '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 16px;font-size:11.5px;margin-bottom:8px;">'
                + '<div><b>Tanggal :</b> ' + esc(fmtDate(hdr.tanggal)) + '<br><b>Dari Gudang :</b> ' + esc(hdr.dari_gudang) + '<br><b>Catatan :</b> ' + esc(hdr.catatan) + '</div>'
                + '<div><b>No. Referensi :</b> ' + esc(hdr.no_referensi) + '<br><b>Ke Gudang :</b> ' + esc(hdr.ke_gudang) + '</div>'
                + '<div class="text-right"><b>' + esc(hdr.status) + '</b><br><b>Proyek :</b> -<br><b>Departemen :</b> Head Quarter</div>'
                + '</div>';

            html += '<table class="br-table"><thead><tr>'
                + '<th>Deskripsi</th>'
                + '<th class="num">Jumlah</th>'
                + '<th>Unit</th>'
                + '<th class="num">Harga Satuan</th>'
                + '<th class="num">Sub Total</th>'
                + '</tr></thead><tbody>';

            (hdr.items || []).forEach(function (it) {
                html += '<tr>'
                    + '<td><b>' + esc(it.kode_barang) + '</b> &nbsp; ' + esc(it.nama_barang) + '</td>'
                    + '<td class="num">' + fmtNum(it.jumlah) + '</td>'
                    + '<td>' + esc(it.unit) + '</td>'
                    + '<td class="num">' + fmtNum(it.harga_satuan) + '</td>'
                    + '<td class="num">' + fmtNum(it.sub_total) + '</td>'
                    + '</tr>';
            });

            html += '</tbody></table>'
                + '<div style="display:flex;justify-content:space-between;font-size:11.5px;margin-top:4px;">'
                + '<div><b>Input Oleh :</b> ' + esc(hdr.input_oleh) + '</div>'
                + (showTotal ? ('<div><b>Total :</b> ' + fmtNum(hdr.total) + '</div>') : '<div></div>')
                + '</div></div>';
        });
        return html;
    }

    // 7. Mutasi Barang Per Gudang & Mutasi Barang - Umum
    function renderMutasiBarang(rows, byWarehouse) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data mutasi barang pada periode ini.</div>';
        }
        const tree = {};
        rows.forEach(function (r) {
            const wh  = byWarehouse ? (r.nama_gudang || 'Gudang') : 'SEMUA';
            const grp = r.kelompok || 'Barang Dagangan';
            if (!tree[wh]) tree[wh] = {};
            if (!tree[wh][grp]) tree[wh][grp] = [];
            tree[wh][grp].push(r);
        });

        let html = '';
        Object.keys(tree).forEach(function (whName) {
            if (byWarehouse) {
                html += '<div class="br-wh-heading">' + esc(whName) + '</div>';
            }
            let whAwal = 0, whMasuk = 0, whKeluar = 0, whAkhir = 0;

            Object.keys(tree[whName]).forEach(function (grpName) {
                html += '<div class="br-grp-heading">' + esc(grpName) + '</div>';
                html += '<table class="br-table"><thead><tr>'
                    + '<th>Kode Barang</th>'
                    + '<th>Nama Barang</th>'
                    + '<th>Unit</th>'
                    + '<th class="num">Stok Awal</th>'
                    + '<th class="num">Stok Masuk</th>'
                    + '<th class="num">Stok Keluar</th>'
                    + '<th class="num">Stok Akhir</th>'
                    + '<th class="num">Saldo Awal</th>'
                    + '<th class="num">Nilai Masuk</th>'
                    + '<th class="num">Nilai Keluar</th>'
                    + '<th class="num">Saldo Akhir</th>'
                    + '</tr></thead><tbody>';

                let gAwal = 0, gMasuk = 0, gKeluar = 0, gAkhir = 0;
                tree[whName][grpName].forEach(function (r) {
                    const sa = parseFloat(r.saldo_awal) || 0;
                    const nm = parseFloat(r.nilai_masuk) || 0;
                    const nk = parseFloat(r.nilai_keluar) || 0;
                    const sz = parseFloat(r.saldo_akhir) || 0;
                    gAwal += sa; gMasuk += nm; gKeluar += nk; gAkhir += sz;
                    whAwal += sa; whMasuk += nm; whKeluar += nk; whAkhir += sz;

                    html += '<tr>'
                        + '<td>' + esc(r.kode_barang) + '</td>'
                        + '<td>' + esc(r.nama_barang) + '</td>'
                        + '<td>' + esc(r.unit) + '</td>'
                        + '<td class="num">' + fmtNum(r.stok_awal) + '</td>'
                        + '<td class="num">' + (r.stok_masuk ? fmtNum(r.stok_masuk) : '') + '</td>'
                        + '<td class="num">' + (r.stok_keluar ? fmtNum(r.stok_keluar) : '') + '</td>'
                        + '<td class="num">' + fmtNum(r.stok_akhir) + '</td>'
                        + '<td class="num">' + fmtNum(sa) + '</td>'
                        + '<td class="num">' + (nm ? fmtNum(nm) : '') + '</td>'
                        + '<td class="num">' + (nk ? fmtNum(nk) : '') + '</td>'
                        + '<td class="num">' + fmtNum(sz) + '</td>'
                        + '</tr>';
                });

                html += '</tbody><tfoot><tr>'
                    + '<td colspan="7" class="text-right">Total ' + esc(grpName) + ' :</td>'
                    + '<td class="num">' + fmtNum(gAwal) + '</td>'
                    + '<td class="num">' + fmtNum(gMasuk) + '</td>'
                    + '<td class="num">' + fmtNum(gKeluar) + '</td>'
                    + '<td class="num">' + fmtNum(gAkhir) + '</td>'
                    + '</tr></tfoot></table>';
            });

            if (byWarehouse) {
                html += '<table class="br-table"><tfoot><tr>'
                    + '<td colspan="7" class="text-right">Total ' + esc(whName) + ' :</td>'
                    + '<td class="num">' + fmtNum(whAwal) + '</td>'
                    + '<td class="num">' + fmtNum(whMasuk) + '</td>'
                    + '<td class="num">' + fmtNum(whKeluar) + '</td>'
                    + '<td class="num">' + fmtNum(whAkhir) + '</td>'
                    + '</tr></tfoot></table>';
            }
        });
        return html;
    }

    // 8. Nomor Lot Barang - Mutasi
    function renderLotMutasi(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data mutasi nomor lot pada periode ini.</div>';
        }
        const tree = {};
        rows.forEach(function (r) {
            const wh = r.nama_gudang || 'Gudang';
            const grp = r.kelompok || 'Barang Dagangan';
            const item = r.kode_barang + ' - ' + r.nama_barang;
            const lot = r.no_lot || '0';
            if (!tree[wh]) tree[wh] = {};
            if (!tree[wh][grp]) tree[wh][grp] = {};
            if (!tree[wh][grp][item]) tree[wh][grp][item] = {};
            if (!tree[wh][grp][item][lot]) tree[wh][grp][item][lot] = [];
            tree[wh][grp][item][lot].push(r);
        });

        let html = '';
        Object.keys(tree).forEach(function (wh) {
            html += '<div class="br-wh-heading">' + esc(wh) + '</div>';
            Object.keys(tree[wh]).forEach(function (grp) {
                html += '<div class="br-grp-heading">' + esc(grp) + '</div>';
                html += '<table class="br-table"><thead><tr>'
                    + '<th>Tanggal</th>'
                    + '<th>No. Ref</th>'
                    + '<th>Unit</th>'
                    + '<th class="num">Masuk</th>'
                    + '<th class="num">Keluar</th>'
                    + '<th>Tgl. Kadaluarsa</th>'
                    + '<th class="num">Saldo</th>'
                    + '<th>Nomor Lot</th>'
                    + '<th>Catatan</th>'
                    + '</tr></thead><tbody>';

                Object.keys(tree[wh][grp]).forEach(function (item) {
                    html += '<tr class="br-jurnal-head"><td colspan="9" style="color:#991b1b;">' + esc(item) + '</td></tr>';
                    let itemIn = 0, itemOut = 0, itemSal = 0;

                    Object.keys(tree[wh][grp][item]).forEach(function (lot) {
                        html += '<tr><td colspan="9" style="font-weight:700;color:#b91c1c;">Lot: ' + esc(lot) + '</td></tr>';
                        let lotIn = 0, lotOut = 0, lotSal = 0;
                        tree[wh][grp][item][lot].forEach(function (r) {
                            const m = parseFloat(r.masuk) || 0;
                            const k = parseFloat(r.keluar) || 0;
                            lotIn += m; lotOut += k; lotSal = parseFloat(r.saldo) || 0;
                            html += '<tr>'
                                + '<td>' + esc(fmtDate(r.tanggal)) + '</td>'
                                + '<td>' + esc(r.no_ref) + '</td>'
                                + '<td>' + esc(r.unit) + '</td>'
                                + '<td class="num">' + fmtNum(m) + '</td>'
                                + '<td class="num">' + fmtNum(k) + '</td>'
                                + '<td>' + esc(fmtDate(r.expired_date)) + '</td>'
                                + '<td class="num">' + fmtNum(lotSal) + '</td>'
                                + '<td>' + esc(lot) + '</td>'
                                + '<td>' + esc(r.catatan) + '</td>'
                                + '</tr>';
                        });
                        itemIn += lotIn; itemOut += lotOut; itemSal += lotSal;
                        html += '<tr class="br-subtotal">'
                            + '<td colspan="3">Total Lot ' + esc(lot) + ' :</td>'
                            + '<td class="num">' + fmtNum(lotIn) + '</td>'
                            + '<td class="num">' + fmtNum(lotOut) + '</td>'
                            + '<td></td>'
                            + '<td class="num">' + fmtNum(lotSal) + '</td>'
                            + '<td colspan="2"></td>'
                            + '</tr>';
                    });

                    html += '<tr class="br-subtotal">'
                        + '<td colspan="3">Total ' + esc(item) + ' :</td>'
                        + '<td class="num">' + fmtNum(itemIn) + '</td>'
                        + '<td class="num">' + fmtNum(itemOut) + '</td>'
                        + '<td></td>'
                        + '<td class="num">' + fmtNum(itemSal) + '</td>'
                        + '<td colspan="2"></td>'
                        + '</tr>';
                });

                html += '</tbody></table>';
            });
        });
        return html;
    }

    // 9. Nomor Lot Barang - Tersedia
    function renderLotTersedia(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data nomor lot tersedia.</div>';
        }
        const tree = {};
        rows.forEach(function (r) {
            const wh = r.nama_gudang || 'Gudang';
            const grp = r.kelompok || 'Barang Dagangan';
            const item = r.kode_barang + ' - ' + r.nama_barang;
            if (!tree[wh]) tree[wh] = {};
            if (!tree[wh][grp]) tree[wh][grp] = {};
            if (!tree[wh][grp][item]) tree[wh][grp][item] = [];
            tree[wh][grp][item].push(r);
        });

        let html = '';
        Object.keys(tree).forEach(function (wh) {
            html += '<div class="br-wh-heading">' + esc(wh) + '</div>';
            Object.keys(tree[wh]).forEach(function (grp) {
                html += '<div class="br-grp-heading">' + esc(grp) + '</div>';
                html += '<table class="br-table"><thead><tr>'
                    + '<th>Kode Barang / Nomor Lot</th>'
                    + '<th>Unit</th>'
                    + '<th class="num">Jumlah</th>'
                    + '<th>Tanggal Produksi</th>'
                    + '<th>Tanggal Kadaluarsa</th>'
                    + '</tr></thead><tbody>';

                Object.keys(tree[wh][grp]).forEach(function (item) {
                    html += '<tr class="br-jurnal-head"><td colspan="5" style="color:#991b1b;">' + esc(item) + '</td></tr>';
                    tree[wh][grp][item].forEach(function (r) {
                        html += '<tr>'
                            + '<td style="padding-left:22px;">' + esc(r.no_lot) + '</td>'
                            + '<td>' + esc(r.unit) + '</td>'
                            + '<td class="num">' + fmtNum(r.jumlah) + '</td>'
                            + '<td>' + esc(fmtDate(r.tgl_produksi)) + '</td>'
                            + '<td>' + esc(fmtDate(r.tgl_kadaluarsa)) + '</td>'
                            + '</tr>';
                    });
                });

                html += '</tbody></table>';
            });
        });
        return html;
    }

    // 10. Analisa Barang - Umum
    function renderAnalisaUmum(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data analisa barang.</div>';
        }
        const groups = {};
        rows.forEach(function (r) {
            const grp = r.kelompok || 'Barang Dagangan';
            if (!groups[grp]) groups[grp] = [];
            groups[grp].push(r);
        });

        let html = '';
        Object.keys(groups).forEach(function (grp) {
            html += '<div class="br-grp-heading">' + esc(grp) + '</div>';
            html += '<table class="br-table"><thead><tr>'
                + '<th>Kode Barang</th>'
                + '<th>Deskripsi Barang</th>'
                + '<th>Unit</th>'
                + '<th class="num">Tersedia</th>'
                + '<th class="num">Dipesan ke Pemasok</th>'
                + '<th class="num">Pesanan Pelanggan</th>'
                + '</tr></thead><tbody>';

            let sumTersedia = 0;
            let sumPo = 0;
            let sumSo = 0;
            groups[grp].forEach(function (r) {
                const tersedia = parseFloat(r.tersedia || 0);
                const po = parseFloat(r.dipesan_ke_pemasok || 0);
                const so = parseFloat(r.pesanan_pelanggan || 0);
                sumTersedia += tersedia;
                sumPo += po;
                sumSo += so;

                html += '<tr>'
                    + '<td>' + esc(r.kode_barang) + '</td>'
                    + '<td>' + esc(r.nama_barang) + '</td>'
                    + '<td>' + esc(r.unit) + '</td>'
                    + '<td class="num">' + fmtNum(tersedia) + '</td>'
                    + '<td class="num">' + fmtNum(po) + '</td>'
                    + '<td class="num">' + fmtNum(so) + '</td>'
                    + '</tr>';
            });

            html += '<tr class="br-subtotal">'
                + '<td colspan="3">Total ' + esc(grp) + '</td>'
                + '<td class="num">' + fmtNum(sumTersedia) + '</td>'
                + '<td class="num">' + fmtNum(sumPo) + '</td>'
                + '<td class="num">' + fmtNum(sumSo) + '</td>'
                + '</tr>';
            html += '</tbody></table>';
        });
        return html;
    }

    // 11. Barang Paling Laris (Top 10, Per Kelompok, Keuntungan)
    function renderLarisSimple(rows, mode) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data penjualan produk pada periode ini.</div>';
        }
        const useGroup = (mode === 'top10' || mode === 'per-kelompok');
        if (!useGroup) {
            // Barang Paling Laris (berdasarkan keuntungan / jumlah)
            let html = '<table class="br-table"><thead><tr>'
                + '<th>Kode Barang</th>'
                + '<th>Nama Barang</th>'
                + '<th>Unit</th>'
                + '<th class="num">Jumlah Terjual</th>'
                + '<th class="num">Nilai Penjualan</th>'
                + '<th class="num">Harga Pokok</th>'
                + '<th class="num">Laba / Rugi</th>'
                + '<th class="num">Margin (%)</th>'
                + '</tr></thead><tbody>';

            let sumQty = 0, sumSales = 0, sumHpp = 0, sumProfit = 0;
            rows.forEach(function (r) {
                const qty = parseFloat(r.terjual || 0);
                const sales = parseFloat(r.nilai_penjualan || 0);
                const hpp = parseFloat(r.harga_pokok || 0);
                const profit = parseFloat(r.laba_rugi || 0);
                sumQty += qty;
                sumSales += sales;
                sumHpp += hpp;
                sumProfit += profit;

                html += '<tr>'
                    + '<td>' + esc(r.kode_barang) + '</td>'
                    + '<td>' + esc(r.nama_barang) + '</td>'
                    + '<td>' + esc(r.unit) + '</td>'
                    + '<td class="num">' + fmtNum(qty) + '</td>'
                    + '<td class="num">' + fmtNum(sales) + '</td>'
                    + '<td class="num">' + fmtNum(hpp) + '</td>'
                    + '<td class="num">' + fmtNum(profit) + '</td>'
                    + '<td class="num">' + fmtNum(r.persen) + '%</td>'
                    + '</tr>';
            });
            const totalPct = sumSales > 0 ? (sumProfit / sumSales) * 100 : 0;
            html += '<tr class="br-grandtotal">'
                + '<td colspan="3">Grand Total</td>'
                + '<td class="num">' + fmtNum(sumQty) + '</td>'
                + '<td class="num">' + fmtNum(sumSales) + '</td>'
                + '<td class="num">' + fmtNum(sumHpp) + '</td>'
                + '<td class="num">' + fmtNum(sumProfit) + '</td>'
                + '<td class="num">' + fmtNum(totalPct) + '%</td>'
                + '</tr></tbody></table>';
            return html;
        }

        // Dikelompokkan per kelompok barang
        const groups = {};
        rows.forEach(function (r) {
            const grp = r.kelompok || 'Barang Dagangan';
            if (!groups[grp]) groups[grp] = [];
            groups[grp].push(r);
        });

        let html = '';
        let grandQty = 0, grandSales = 0, grandHpp = 0, grandProfit = 0;
        Object.keys(groups).forEach(function (grp) {
            let list = groups[grp].slice().sort(function (a, b) {
                return parseFloat(b.terjual || 0) - parseFloat(a.terjual || 0);
            });
            if (mode === 'top10') {
                list = list.slice(0, 10);
            }

            html += '<div class="br-grp-heading">' + esc(grp) + '</div>';
            html += '<table class="br-table"><thead><tr>'
                + '<th>Kode Barang</th>'
                + '<th>Deskripsi Barang</th>'
                + '<th>Unit</th>'
                + '<th class="num">Terjual</th>'
                + '<th class="num">Nilai Penjualan</th>'
                + '<th class="num">Harga Pokok</th>'
                + '<th class="num">Laba / Rugi</th>'
                + '<th class="num">(%)</th>'
                + '</tr></thead><tbody>';

            let subQty = 0, subSales = 0, subHpp = 0, subProfit = 0;
            list.forEach(function (r) {
                const qty = parseFloat(r.terjual || 0);
                const sales = parseFloat(r.nilai_penjualan || 0);
                const hpp = parseFloat(r.harga_pokok || 0);
                const profit = parseFloat(r.laba_rugi || 0);
                subQty += qty;
                subSales += sales;
                subHpp += hpp;
                subProfit += profit;

                html += '<tr>'
                    + '<td>' + esc(r.kode_barang) + '</td>'
                    + '<td>' + esc(r.nama_barang) + '</td>'
                    + '<td>' + esc(r.unit) + '</td>'
                    + '<td class="num">' + fmtNum(qty) + '</td>'
                    + '<td class="num">' + fmtNum(sales) + '</td>'
                    + '<td class="num">' + fmtNum(hpp) + '</td>'
                    + '<td class="num">' + fmtNum(profit) + '</td>'
                    + '<td class="num">' + fmtNum(r.persen) + '%</td>'
                    + '</tr>';
            });

            grandQty += subQty;
            grandSales += subSales;
            grandHpp += subHpp;
            grandProfit += subProfit;
            const subPct = subSales > 0 ? (subProfit / subSales) * 100 : 0;

            html += '<tr class="br-subtotal">'
                + '<td colspan="3">Total ' + esc(grp) + '</td>'
                + '<td class="num">' + fmtNum(subQty) + '</td>'
                + '<td class="num">' + fmtNum(subSales) + '</td>'
                + '<td class="num">' + fmtNum(subHpp) + '</td>'
                + '<td class="num">' + fmtNum(subProfit) + '</td>'
                + '<td class="num">' + fmtNum(subPct) + '%</td>'
                + '</tr></tbody></table>';
        });

        const grandPct = grandSales > 0 ? (grandProfit / grandSales) * 100 : 0;
        html += '<table class="br-table"><tbody><tr class="br-grandtotal">'
            + '<td colspan="3">Grand Total</td>'
            + '<td class="num">' + fmtNum(grandQty) + '</td>'
            + '<td class="num">' + fmtNum(grandSales) + '</td>'
            + '<td class="num">' + fmtNum(grandHpp) + '</td>'
            + '<td class="num">' + fmtNum(grandProfit) + '</td>'
            + '<td class="num">' + fmtNum(grandPct) + '%</td>'
            + '</tr></tbody></table>';
        return html;
    }

    // 12. Laba / Rugi Barang - Total
    function renderLabaRugiTotal(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data laba / rugi barang pada periode ini.</div>';
        }
        const groups = {};
        rows.forEach(function (r) {
            const grp = r.kelompok || 'Barang Dagangan';
            if (!groups[grp]) groups[grp] = [];
            groups[grp].push(r);
        });

        let html = '';
        let grandQty = 0, grandSales = 0, grandHpp = 0, grandProfit = 0;
        Object.keys(groups).forEach(function (grp) {
            html += '<div class="br-grp-heading">' + esc(grp) + '</div>';
            html += '<table class="br-table"><thead><tr>'
                + '<th>Kode Barang</th>'
                + '<th>Deskripsi Barang</th>'
                + '<th>Unit</th>'
                + '<th class="num">Terjual</th>'
                + '<th class="num">Nilai Penjualan</th>'
                + '<th class="num">HPP / Unit</th>'
                + '<th class="num">Harga Pokok</th>'
                + '<th class="num">Laba / Rugi</th>'
                + '<th class="num">(%)</th>'
                + '</tr></thead><tbody>';

            let subQty = 0, subSales = 0, subHpp = 0, subProfit = 0;
            groups[grp].forEach(function (r) {
                const qty = parseFloat(r.terjual || 0);
                const sales = parseFloat(r.nilai_penjualan || 0);
                const hppUnit = parseFloat(r.hpp_unit || 0);
                const hpp = parseFloat(r.harga_pokok || 0);
                const profit = parseFloat(r.laba_rugi || 0);
                subQty += qty;
                subSales += sales;
                subHpp += hpp;
                subProfit += profit;

                html += '<tr>'
                    + '<td>' + esc(r.kode_barang) + '</td>'
                    + '<td>' + esc(r.nama_barang) + '</td>'
                    + '<td>' + esc(r.unit) + '</td>'
                    + '<td class="num">' + fmtNum(qty) + '</td>'
                    + '<td class="num">' + fmtNum(sales) + '</td>'
                    + '<td class="num">' + fmtNum(hppUnit) + '</td>'
                    + '<td class="num">' + fmtNum(hpp) + '</td>'
                    + '<td class="num">' + fmtNum(profit) + '</td>'
                    + '<td class="num">' + fmtNum(r.persen) + '%</td>'
                    + '</tr>';
            });

            grandQty += subQty;
            grandSales += subSales;
            grandHpp += subHpp;
            grandProfit += subProfit;
            const subPct = subSales > 0 ? (subProfit / subSales) * 100 : 0;

            html += '<tr class="br-subtotal">'
                + '<td colspan="3">Total ' + esc(grp) + '</td>'
                + '<td class="num">' + fmtNum(subQty) + '</td>'
                + '<td class="num">' + fmtNum(subSales) + '</td>'
                + '<td class="num">-</td>'
                + '<td class="num">' + fmtNum(subHpp) + '</td>'
                + '<td class="num">' + fmtNum(subProfit) + '</td>'
                + '<td class="num">' + fmtNum(subPct) + '%</td>'
                + '</tr></tbody></table>';
        });

        const grandPct = grandSales > 0 ? (grandProfit / grandSales) * 100 : 0;
        html += '<table class="br-table"><tbody><tr class="br-grandtotal">'
            + '<td colspan="3">Grand Total</td>'
            + '<td class="num">' + fmtNum(grandQty) + '</td>'
            + '<td class="num">' + fmtNum(grandSales) + '</td>'
            + '<td class="num">-</td>'
            + '<td class="num">' + fmtNum(grandHpp) + '</td>'
            + '<td class="num">' + fmtNum(grandProfit) + '</td>'
            + '<td class="num">' + fmtNum(grandPct) + '%</td>'
            + '</tr></tbody></table>';
        return html;
    }

    // 13. Laba / Rugi Barang - Rincian
    function renderLabaRugiRincian(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data rincian laba / rugi barang pada periode ini.</div>';
        }
        const tree = {};
        rows.forEach(function (r) {
            const grp = r.kelompok || 'Barang Dagangan';
            const item = r.kode_barang + ' - ' + r.nama_barang;
            if (!tree[grp]) tree[grp] = {};
            if (!tree[grp][item]) tree[grp][item] = [];
            tree[grp][item].push(r);
        });

        let html = '';
        let grandQty = 0, grandSales = 0, grandHpp = 0, grandProfit = 0;
        Object.keys(tree).forEach(function (grp) {
            html += '<div class="br-grp-heading">' + esc(grp) + '</div>';
            html += '<table class="br-table"><thead><tr>'
                + '<th>Tanggal</th>'
                + '<th>TP</th>'
                + '<th>No. Referensi</th>'
                + '<th>Unit</th>'
                + '<th class="num">Terjual</th>'
                + '<th class="num">Nilai Penjualan</th>'
                + '<th class="num">HPP / Unit</th>'
                + '<th class="num">Harga Pokok</th>'
                + '<th class="num">Laba / Rugi</th>'
                + '<th class="num">(%)</th>'
                + '</tr></thead><tbody>';

            let grpQty = 0, grpSales = 0, grpHpp = 0, grpProfit = 0;
            Object.keys(tree[grp]).forEach(function (item) {
                html += '<tr class="br-jurnal-head"><td colspan="10" style="color:#991b1b;">' + esc(item) + '</td></tr>';
                let itemQty = 0, itemSales = 0, itemHpp = 0, itemProfit = 0;

                tree[grp][item].forEach(function (r) {
                    const qty = parseFloat(r.terjual || 0);
                    const sales = parseFloat(r.nilai_penjualan || 0);
                    const hppUnit = parseFloat(r.hpp_unit || 0);
                    const hpp = parseFloat(r.harga_pokok || 0);
                    const profit = parseFloat(r.laba_rugi || 0);
                    itemQty += qty;
                    itemSales += sales;
                    itemHpp += hpp;
                    itemProfit += profit;

                    html += '<tr>'
                        + '<td>' + esc(fmtDate(r.tanggal)) + '</td>'
                        + '<td>' + esc(r.tp) + '</td>'
                        + '<td>' + esc(r.no_referensi) + '</td>'
                        + '<td>' + esc(r.unit) + '</td>'
                        + '<td class="num">' + fmtNum(qty) + '</td>'
                        + '<td class="num">' + fmtNum(sales) + '</td>'
                        + '<td class="num">' + fmtNum(hppUnit) + '</td>'
                        + '<td class="num">' + fmtNum(hpp) + '</td>'
                        + '<td class="num">' + fmtNum(profit) + '</td>'
                        + '<td class="num">' + fmtNum(r.persen) + '%</td>'
                        + '</tr>';
                });

                grpQty += itemQty;
                grpSales += itemSales;
                grpHpp += itemHpp;
                grpProfit += itemProfit;
                const itemPct = itemSales > 0 ? (itemProfit / itemSales) * 100 : 0;

                html += '<tr class="br-subtotal">'
                    + '<td colspan="4">Subtotal ' + esc(item) + '</td>'
                    + '<td class="num">' + fmtNum(itemQty) + '</td>'
                    + '<td class="num">' + fmtNum(itemSales) + '</td>'
                    + '<td class="num">-</td>'
                    + '<td class="num">' + fmtNum(itemHpp) + '</td>'
                    + '<td class="num">' + fmtNum(itemProfit) + '</td>'
                    + '<td class="num">' + fmtNum(itemPct) + '%</td>'
                    + '</tr>';
            });

            grandQty += grpQty;
            grandSales += grpSales;
            grandHpp += grpHpp;
            grandProfit += grpProfit;
            const grpPct = grpSales > 0 ? (grpProfit / grpSales) * 100 : 0;

            html += '<tr class="br-subtotal">'
                + '<td colspan="4">Total ' + esc(grp) + '</td>'
                + '<td class="num">' + fmtNum(grpQty) + '</td>'
                + '<td class="num">' + fmtNum(grpSales) + '</td>'
                + '<td class="num">-</td>'
                + '<td class="num">' + fmtNum(grpHpp) + '</td>'
                + '<td class="num">' + fmtNum(grpProfit) + '</td>'
                + '<td class="num">' + fmtNum(grpPct) + '%</td>'
                + '</tr></tbody></table>';
        });

        const grandPct = grandSales > 0 ? (grandProfit / grandSales) * 100 : 0;
        html += '<table class="br-table"><tbody><tr class="br-grandtotal">'
            + '<td colspan="4">Grand Total</td>'
            + '<td class="num">' + fmtNum(grandQty) + '</td>'
            + '<td class="num">' + fmtNum(grandSales) + '</td>'
            + '<td class="num">-</td>'
            + '<td class="num">' + fmtNum(grandHpp) + '</td>'
            + '<td class="num">' + fmtNum(grandProfit) + '</td>'
            + '<td class="num">' + fmtNum(grandPct) + '%</td>'
            + '</tr></tbody></table>';
        return html;
    }

    // 14. Penjualan Per Barang / Pembelian Per Barang & Pemasok (Faktur, Invoice, Total)
    function renderAnalisaTransaksiHierarki(rows, mode) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data transaksi pada periode ini.</div>';
        }

        // Mode total (ringkasan tanpa rincian faktur)
        if (mode === 'pembelian-pemasok-barang-total' || mode === 'pembelian-barang-pemasok-total') {
            const isVendorFirst = (mode === 'pembelian-pemasok-barang-total');
            const tree = {};
            rows.forEach(function (r) {
                const parentKey = isVendorFirst ? (r.pemasok || 'Pemasok Umum') : (r.kode_barang + ' - ' + r.nama_barang);
                const childKey = isVendorFirst ? (r.kode_barang + ' - ' + r.nama_barang) : (r.pemasok || 'Pemasok Umum');
                if (!tree[parentKey]) tree[parentKey] = {};
                if (!tree[parentKey][childKey]) {
                    tree[parentKey][childKey] = {
                        unit: r.unit,
                        jumlah: 0,
                        total: 0
                    };
                }
                tree[parentKey][childKey].jumlah += parseFloat(r.jumlah || 0);
                tree[parentKey][childKey].total += parseFloat(r.total || 0);
            });

            let html = '<table class="br-table"><thead><tr>'
                + '<th>' + (isVendorFirst ? 'Pemasok / Barang' : 'Barang / Pemasok') + '</th>'
                + '<th>Unit</th>'
                + '<th class="num">Jumlah</th>'
                + '<th class="num">Total Nilai</th>'
                + '</tr></thead><tbody>';

            let grandQty = 0, grandTotal = 0;
            Object.keys(tree).forEach(function (parentKey) {
                html += '<tr class="br-jurnal-head"><td colspan="4" style="color:#991b1b;">' + esc(parentKey) + '</td></tr>';
                let subQty = 0, subTotal = 0;
                Object.keys(tree[parentKey]).forEach(function (childKey) {
                    const agg = tree[parentKey][childKey];
                    subQty += agg.jumlah;
                    subTotal += agg.total;
                    html += '<tr>'
                        + '<td style="padding-left:22px;">' + esc(childKey) + '</td>'
                        + '<td>' + esc(agg.unit) + '</td>'
                        + '<td class="num">' + fmtNum(agg.jumlah) + '</td>'
                        + '<td class="num">' + fmtNum(agg.total) + '</td>'
                        + '</tr>';
                });
                grandQty += subQty;
                grandTotal += subTotal;
                html += '<tr class="br-subtotal">'
                    + '<td colspan="2">Total ' + esc(parentKey) + '</td>'
                    + '<td class="num">' + fmtNum(subQty) + '</td>'
                    + '<td class="num">' + fmtNum(subTotal) + '</td>'
                    + '</tr>';
            });

            html += '<tr class="br-grandtotal">'
                + '<td colspan="2">Grand Total</td>'
                + '<td class="num">' + fmtNum(grandQty) + '</td>'
                + '<td class="num">' + fmtNum(grandTotal) + '</td>'
                + '</tr></tbody></table>';
            return html;
        }

        // Mode rincian faktur/invoice per gudang -> barang -> partner -> baris faktur
        const isSales = (mode === 'penjualan-barang-pelanggan-faktur');
        const isVendorFirst = (mode === 'pembelian-pemasok-barang-invoice');
        const tree = {};
        rows.forEach(function (r) {
            const wh = r.nama_gudang || 'Gudang';
            const lvl1 = isVendorFirst ? (r.pemasok || 'Pemasok Umum') : (r.kode_barang + ' - ' + r.nama_barang);
            const lvl2 = isSales
                ? (r.pelanggan || 'Pelanggan Umum')
                : (isVendorFirst ? (r.kode_barang + ' - ' + r.nama_barang) : (r.pemasok || 'Pemasok Umum'));
            if (!tree[wh]) tree[wh] = {};
            if (!tree[wh][lvl1]) tree[wh][lvl1] = {};
            if (!tree[wh][lvl1][lvl2]) tree[wh][lvl1][lvl2] = [];
            tree[wh][lvl1][lvl2].push(r);
        });

        let html = '';
        let grandQty = 0, grandTotal = 0;
        Object.keys(tree).forEach(function (wh) {
            html += '<div class="br-wh-heading">' + esc(wh) + '</div>';
            html += '<table class="br-table"><thead><tr>'
                + '<th>Tanggal</th>'
                + '<th>No. Faktur / Ref</th>'
                + (isSales ? '<th>Salesman</th>' : '')
                + '<th class="num">Jumlah</th>'
                + '<th>Unit</th>'
                + '<th class="num">Harga Satuan</th>'
                + '<th class="num">Disc (%)</th>'
                + '<th class="num">Total</th>'
                + '</tr></thead><tbody>';

            const colSpanTotal = isSales ? 8 : 7;
            const colSpanLabel = isSales ? 3 : 2;
            let whQty = 0, whTotal = 0;

            Object.keys(tree[wh]).forEach(function (lvl1) {
                html += '<tr class="br-jurnal-head"><td colspan="' + colSpanTotal + '" style="color:#991b1b;">' + esc(lvl1) + '</td></tr>';
                let l1Qty = 0, l1Total = 0;

                Object.keys(tree[wh][lvl1]).forEach(function (lvl2) {
                    html += '<tr><td colspan="' + colSpanTotal + '" style="padding-left:18px;font-weight:700;color:#1e3a5f;background:#f8fafc;">' + esc(lvl2) + '</td></tr>';
                    let l2Qty = 0, l2Total = 0;

                    tree[wh][lvl1][lvl2].forEach(function (r) {
                        const qty = parseFloat(r.jumlah || 0);
                        const tot = parseFloat(r.total || 0);
                        l2Qty += qty;
                        l2Total += tot;

                        html += '<tr>'
                            + '<td style="padding-left:28px;">' + esc(fmtDate(r.tanggal)) + '</td>'
                            + '<td>' + esc(r.no_referensi) + '</td>'
                            + (isSales ? '<td>' + esc(r.salesman || '-') + '</td>' : '')
                            + '<td class="num">' + fmtNum(qty) + '</td>'
                            + '<td>' + esc(r.unit) + '</td>'
                            + '<td class="num">' + fmtNum(r.harga_satuan) + '</td>'
                            + '<td class="num">' + fmtNum(r.disc_persen) + '%</td>'
                            + '<td class="num">' + fmtNum(tot) + '</td>'
                            + '</tr>';
                    });

                    l1Qty += l2Qty;
                    l1Total += l2Total;
                    html += '<tr class="br-subtotal">'
                        + '<td colspan="' + colSpanLabel + '" style="padding-left:18px;">Subtotal ' + esc(lvl2) + '</td>'
                        + '<td class="num">' + fmtNum(l2Qty) + '</td>'
                        + '<td colspan="3"></td>'
                        + '<td class="num">' + fmtNum(l2Total) + '</td>'
                        + '</tr>';
                });

                whQty += l1Qty;
                whTotal += l1Total;
                html += '<tr class="br-subtotal">'
                    + '<td colspan="' + colSpanLabel + '">Total ' + esc(lvl1) + '</td>'
                    + '<td class="num">' + fmtNum(l1Qty) + '</td>'
                    + '<td colspan="3"></td>'
                    + '<td class="num">' + fmtNum(l1Total) + '</td>'
                    + '</tr>';
            });

            grandQty += whQty;
            grandTotal += whTotal;
            html += '<tr class="br-subtotal">'
                + '<td colspan="' + colSpanLabel + '">Total ' + esc(wh) + '</td>'
                + '<td class="num">' + fmtNum(whQty) + '</td>'
                + '<td colspan="3"></td>'
                + '<td class="num">' + fmtNum(whTotal) + '</td>'
                + '</tr></tbody></table>';
        });

        const colSpanGrand = isSales ? 3 : 2;
        html += '<table class="br-table"><tbody><tr class="br-grandtotal">'
            + '<td colspan="' + colSpanGrand + '">Grand Total</td>'
            + '<td class="num">' + fmtNum(grandQty) + '</td>'
            + '<td colspan="3"></td>'
            + '<td class="num">' + fmtNum(grandTotal) + '</td>'
            + '</tr></tbody></table>';
        return html;
    }

    // 15. Perputaran Barang Paling Cepat (Per Kelompok & Semua Kelompok)
    function renderPerputaranCepat(rows, perKelompok) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data perputaran barang pada periode ini.</div>';
        }
        if (!perKelompok) {
            let html = '<table class="br-table"><thead><tr>'
                + '<th>Kode Barang</th>'
                + '<th>Nama Barang</th>'
                + '<th>Unit</th>'
                + '<th class="num">Terjual</th>'
                + '<th>Mata Uang</th>'
                + '<th class="num">Nilai Penjualan</th>'
                + '</tr></thead><tbody>';

            let sumQty = 0, sumVal = 0;
            rows.forEach(function (r) {
                const q = parseFloat(r.terjual || 0);
                const v = parseFloat(r.nilai_penjualan || 0);
                sumQty += q;
                sumVal += v;
                html += '<tr>'
                    + '<td>' + esc(r.kode_barang) + '</td>'
                    + '<td>' + esc(r.nama_barang) + '</td>'
                    + '<td>' + esc(r.unit) + '</td>'
                    + '<td class="num">' + fmtNum(q) + '</td>'
                    + '<td>IDR</td>'
                    + '<td class="num">' + fmtNum(v) + '</td>'
                    + '</tr>';
            });
            html += '<tr class="br-grandtotal">'
                + '<td colspan="3">Grand Total</td>'
                + '<td class="num">' + fmtNum(sumQty) + '</td>'
                + '<td>IDR</td>'
                + '<td class="num">' + fmtNum(sumVal) + '</td>'
                + '</tr></tbody></table>';
            return html;
        }

        const groups = {};
        rows.forEach(function (r) {
            const grp = r.kelompok || 'Barang Dagangan';
            if (!groups[grp]) groups[grp] = [];
            groups[grp].push(r);
        });

        let html = '';
        let grandQty = 0, grandVal = 0;
        Object.keys(groups).forEach(function (grp) {
            html += '<div class="br-grp-heading">' + esc(grp) + '</div>';
            html += '<table class="br-table"><thead><tr>'
                + '<th>Kode Barang</th>'
                + '<th>Nama Barang</th>'
                + '<th>Unit</th>'
                + '<th class="num">Terjual</th>'
                + '<th>Mata Uang</th>'
                + '<th class="num">Nilai Penjualan</th>'
                + '</tr></thead><tbody>';

            let subQty = 0, subVal = 0;
            groups[grp].forEach(function (r) {
                const q = parseFloat(r.terjual || 0);
                const v = parseFloat(r.nilai_penjualan || 0);
                subQty += q;
                subVal += v;
                html += '<tr>'
                    + '<td>' + esc(r.kode_barang) + '</td>'
                    + '<td>' + esc(r.nama_barang) + '</td>'
                    + '<td>' + esc(r.unit) + '</td>'
                    + '<td class="num">' + fmtNum(q) + '</td>'
                    + '<td>IDR</td>'
                    + '<td class="num">' + fmtNum(v) + '</td>'
                    + '</tr>';
            });

            grandQty += subQty;
            grandVal += subVal;
            html += '<tr class="br-subtotal">'
                + '<td colspan="3">Total ' + esc(grp) + '</td>'
                + '<td class="num">' + fmtNum(subQty) + '</td>'
                + '<td>IDR</td>'
                + '<td class="num">' + fmtNum(subVal) + '</td>'
                + '</tr></tbody></table>';
        });

        html += '<table class="br-table"><tbody><tr class="br-grandtotal">'
            + '<td colspan="3">Grand Total</td>'
            + '<td class="num">' + fmtNum(grandQty) + '</td>'
            + '<td>IDR</td>'
            + '<td class="num">' + fmtNum(grandVal) + '</td>'
            + '</tr></tbody></table>';
        return html;
    }

    // 16. Laporan Analisa Penjualan Lanjutan (Salesman / Pelanggan / Barang)
    function renderAnalisaPenjualanLanjutan(rows, mode) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data penjualan pada periode ini.</div>';
        }

        // Helper agregasi baris barang
        function aggItemMap(list, keyFn) {
            const map = {};
            list.forEach(function (r) {
                const k = keyFn(r);
                if (!map[k]) {
                    map[k] = {
                        label: k,
                        unit: r.unit,
                        jumlah: 0,
                        total: 0,
                        discSum: 0,
                        cnt: 0
                    };
                }
                map[k].jumlah += parseFloat(r.jumlah || 0);
                map[k].total += parseFloat(r.total || 0);
                map[k].discSum += parseFloat(r.disc_persen || 0);
                map[k].cnt += 1;
            });
            return Object.keys(map).map(function (k) {
                const o = map[k];
                o.harga = o.jumlah > 0 ? (o.total / o.jumlah) : 0;
                o.disc = o.cnt > 0 ? (o.discSum / o.cnt) : 0;
                return o;
            });
        }

        // A. Penjualan Per Salesman Per Pelanggan
        if (mode === 'penjualan-salesman-pelanggan') {
            const tree = {};
            rows.forEach(function (r) {
                const s = r.salesman || 'Others';
                const c = r.pelanggan || '-';
                if (!tree[s]) tree[s] = {};
                if (!tree[s][c]) tree[s][c] = 0;
                tree[s][c] += parseFloat(r.total || 0);
            });

            let html = '', grand = 0;
            Object.keys(tree).forEach(function (s) {
                html += '<div class="br-wh-heading">' + esc(s) + '</div>';
                html += '<table class="br-table"><thead><tr>'
                    + '<th>Nama Pelanggan</th><th>Mata Uang</th><th class="num">Total</th>'
                    + '</tr></thead><tbody>';
                let sub = 0;
                Object.keys(tree[s]).sort().forEach(function (c) {
                    const val = tree[s][c];
                    sub += val;
                    html += '<tr><td>' + esc(c) + '</td><td>IDR</td><td class="num">' + fmtNum(val) + '</td></tr>';
                });
                grand += sub;
                html += '<tr class="br-subtotal"><td>Total ' + esc(s) + '</td><td>IDR</td><td class="num">' + fmtNum(sub) + '</td></tr>';
                html += '</tbody></table>';
            });
            html += '<table class="br-table"><tbody><tr class="br-grandtotal"><td>Grand Total</td><td>IDR</td><td class="num">' + fmtNum(grand) + '</td></tr></tbody></table>';
            return html;
        }

        // B. Penjualan Per Salesman Per Barang Total / Selain Retur
        if (mode === 'penjualan-salesman-barang-total' || mode === 'penjualan-salesman-barang-non-retur') {
            const tree = {};
            rows.forEach(function (r) {
                const s = r.salesman || 'Others';
                const g = r.kelompok || 'Barang Dagangan';
                if (!tree[s]) tree[s] = {};
                if (!tree[s][g]) tree[s][g] = [];
                tree[s][g].push(r);
            });

            let html = '', grandTotal = 0;
            Object.keys(tree).forEach(function (s) {
                html += '<div class="br-wh-heading">' + esc(s) + '</div>';
                let salesTot = 0;
                Object.keys(tree[s]).forEach(function (g) {
                    html += '<div class="br-grp-heading">' + esc(g) + '</div>';
                    html += '<table class="br-table"><thead><tr>'
                        + '<th>Nama Barang</th><th class="num">Jumlah</th><th>Unit</th>'
                        + '<th class="num">Harga Satuan</th><th class="num">Disc. %</th><th>Mata</th><th class="num">Total</th>'
                        + '</tr></thead><tbody>';
                    const items = aggItemMap(tree[s][g], function (r) { return r.nama_barang; });
                    let grpTot = 0;
                    items.forEach(function (it) {
                        grpTot += it.total;
                        html += '<tr>'
                            + '<td>' + esc(it.label) + '</td>'
                            + '<td class="num">' + fmtNum(it.jumlah) + '</td>'
                            + '<td>' + esc(it.unit) + '</td>'
                            + '<td class="num">' + fmtNum(it.harga) + '</td>'
                            + '<td class="num">' + fmtNum(it.disc) + '</td>'
                            + '<td>IDR</td>'
                            + '<td class="num">' + fmtNum(it.total) + '</td>'
                            + '</tr>';
                    });
                    salesTot += grpTot;
                    html += '<tr class="br-subtotal"><td colspan="5">Total ' + esc(g) + '</td><td>IDR</td><td class="num">' + fmtNum(grpTot) + '</td></tr>';
                    html += '</tbody></table>';
                });
                grandTotal += salesTot;
                html += '<table class="br-table"><tbody><tr class="br-subtotal"><td colspan="5">Total ' + esc(s) + '</td><td>IDR</td><td class="num">' + fmtNum(salesTot) + '</td></tr></tbody></table>';
            });
            html += '<table class="br-table"><tbody><tr class="br-grandtotal"><td colspan="5">Grand Total</td><td>IDR</td><td class="num">' + fmtNum(grandTotal) + '</td></tr></tbody></table>';
            return html;
        }

        // C. Penjualan Per Pelanggan Per Barang Total
        if (mode === 'penjualan-pelanggan-barang-total') {
            const tree = {};
            rows.forEach(function (r) {
                const s = r.salesman || 'Others';
                const c = r.pelanggan || '-';
                const g = r.kelompok || 'Barang Dagangan';
                if (!tree[s]) tree[s] = {};
                if (!tree[s][c]) tree[s][c] = {};
                if (!tree[s][c][g]) tree[s][c][g] = [];
                tree[s][c][g].push(r);
            });

            let html = '', grandTotal = 0;
            Object.keys(tree).forEach(function (s) {
                html += '<div class="br-wh-heading">' + esc(s) + '</div>';
                Object.keys(tree[s]).sort().forEach(function (c) {
                    html += '<div style="font-weight:700;color:#111827;margin:8px 0 4px;">' + esc(c) + '</div>';
                    let custTot = 0;
                    Object.keys(tree[s][c]).forEach(function (g) {
                        html += '<div class="br-grp-heading" style="margin-left:12px;">' + esc(g) + '</div>';
                        html += '<table class="br-table"><thead><tr>'
                            + '<th>Nama Barang</th><th class="num">Jumlah</th><th>Unit</th>'
                            + '<th class="num">Harga Satuan</th><th class="num">Disc. %</th><th>Mata</th><th class="num">Total</th>'
                            + '</tr></thead><tbody>';
                        const items = aggItemMap(tree[s][c][g], function (r) { return r.nama_barang; });
                        let grpTot = 0;
                        items.forEach(function (it) {
                            grpTot += it.total;
                            html += '<tr>'
                                + '<td>' + esc(it.label) + '</td>'
                                + '<td class="num">' + fmtNum(it.jumlah) + '</td>'
                                + '<td>' + esc(it.unit) + '</td>'
                                + '<td class="num">' + fmtNum(it.harga) + '</td>'
                                + '<td class="num">' + fmtNum(it.disc) + '</td>'
                                + '<td>IDR</td>'
                                + '<td class="num">' + fmtNum(it.total) + '</td>'
                                + '</tr>';
                        });
                        custTot += grpTot;
                        html += '<tr class="br-subtotal"><td colspan="5">Total ' + esc(g) + '</td><td>IDR</td><td class="num">' + fmtNum(grpTot) + '</td></tr>';
                        html += '</tbody></table>';
                    });
                    grandTotal += custTot;
                    html += '<table class="br-table"><tbody><tr class="br-subtotal"><td colspan="5">Total ' + esc(c) + '</td><td>IDR</td><td class="num">' + fmtNum(custTot) + '</td></tr></tbody></table>';
                });
            });
            html += '<table class="br-table"><tbody><tr class="br-grandtotal"><td colspan="5">Grand Total</td><td>IDR</td><td class="num">' + fmtNum(grandTotal) + '</td></tr></tbody></table>';
            return html;
        }

        // D. Penjualan Per Pelanggan Per Barang Per Faktur & Per Salesman Per Pelanggan Per Barang Per Faktur
        if (mode === 'penjualan-pelanggan-barang-faktur' || mode === 'penjualan-salesman-pelanggan-barang-faktur') {
            const tree = {};
            rows.forEach(function (r) {
                const s = r.salesman || 'Others';
                const c = r.pelanggan || '-';
                const g = r.kelompok || 'Barang Dagangan';
                const b = r.nama_barang;
                if (!tree[s]) tree[s] = {};
                if (!tree[s][c]) tree[s][c] = {};
                if (!tree[s][c][g]) tree[s][c][g] = {};
                if (!tree[s][c][g][b]) tree[s][c][g][b] = [];
                tree[s][c][g][b].push(r);
            });

            let html = '', grandTotal = 0;
            Object.keys(tree).forEach(function (s) {
                html += '<div class="br-wh-heading">' + esc(s) + '</div>';
                Object.keys(tree[s]).sort().forEach(function (c) {
                    html += '<div style="font-weight:700;color:#111827;margin:8px 0 4px;">' + esc(c) + '</div>';
                    let custTot = 0;
                    Object.keys(tree[s][c]).forEach(function (g) {
                        html += '<div class="br-grp-heading" style="margin-left:12px;">' + esc(g) + '</div>';
                        html += '<table class="br-table"><thead><tr>'
                            + '<th>Tanggal</th><th>No. Referensi</th><th class="num">Jumlah</th><th>Unit</th>'
                            + '<th class="num">Harga Satuan</th><th class="num">Disc. %</th><th>Mata</th><th class="num">Total</th>'
                            + '</tr></thead><tbody>';
                        let grpTot = 0;
                        Object.keys(tree[s][c][g]).forEach(function (b) {
                            html += '<tr class="br-jurnal-head"><td colspan="8" style="color:#0369a1;">' + esc(b) + '</td></tr>';
                            let itemQty = 0, itemTot = 0, itemUnit = '';
                            tree[s][c][g][b].forEach(function (r) {
                                const q = parseFloat(r.jumlah || 0);
                                const t = parseFloat(r.total || 0);
                                itemQty += q;
                                itemTot += t;
                                itemUnit = r.unit;
                                html += '<tr>'
                                    + '<td style="padding-left:22px;">' + esc(fmtDate(r.tanggal)) + '</td>'
                                    + '<td>' + esc(r.no_referensi) + '</td>'
                                    + '<td class="num">' + fmtNum(q) + '</td>'
                                    + '<td>' + esc(r.unit) + '</td>'
                                    + '<td class="num">' + fmtNum(r.harga_satuan) + '</td>'
                                    + '<td class="num">' + fmtNum(r.disc_persen) + '</td>'
                                    + '<td>IDR</td>'
                                    + '<td class="num">' + fmtNum(t) + '</td>'
                                    + '</tr>';
                            });
                            grpTot += itemTot;
                            html += '<tr class="br-subtotal">'
                                + '<td colspan="2">Total ' + esc(b) + '</td>'
                                + '<td class="num">' + fmtNum(itemQty) + '</td>'
                                + '<td>' + esc(itemUnit) + '</td>'
                                + '<td colspan="2"></td><td>IDR</td>'
                                + '<td class="num">' + fmtNum(itemTot) + '</td>'
                                + '</tr>';
                        });
                        custTot += grpTot;
                        html += '<tr class="br-subtotal"><td colspan="6">Total ' + esc(g) + '</td><td>IDR</td><td class="num">' + fmtNum(grpTot) + '</td></tr>';
                        html += '</tbody></table>';
                    });
                    grandTotal += custTot;
                    html += '<table class="br-table"><tbody><tr class="br-subtotal"><td colspan="6">Total ' + esc(c) + '</td><td>IDR</td><td class="num">' + fmtNum(custTot) + '</td></tr></tbody></table>';
                });
            });
            html += '<table class="br-table"><tbody><tr class="br-grandtotal"><td colspan="6">Grand Total</td><td>IDR</td><td class="num">' + fmtNum(grandTotal) + '</td></tr></tbody></table>';
            return html;
        }

        // E. Penjualan Per Barang Per Pelanggan Total & Per Barang Per Salesman Total
        const bySalesOnly = (mode === 'penjualan-barang-salesman-total');
        const tree = {};
        rows.forEach(function (r) {
            const wh = r.nama_gudang || '(B) Gdg. Induk';
            const g = r.kelompok || 'Barang Dagangan';
            const b = r.nama_barang;
            const s = r.salesman || 'Others';
            if (!tree[wh]) tree[wh] = {};
            if (!tree[wh][g]) tree[wh][g] = {};
            if (!tree[wh][g][b]) tree[wh][g][b] = {};
            if (!tree[wh][g][b][s]) tree[wh][g][b][s] = [];
            tree[wh][g][b][s].push(r);
        });

        let html = '', grandTotal = 0;
        Object.keys(tree).forEach(function (wh) {
            html += '<div class="br-wh-heading">' + esc(wh) + '</div>';
            Object.keys(tree[wh]).forEach(function (g) {
                html += '<div class="br-grp-heading">' + esc(g) + '</div>';
                let grpTot = 0;
                Object.keys(tree[wh][g]).forEach(function (b) {
                    html += '<div style="font-weight:700;color:#111827;margin:6px 0 3px;">' + esc(b) + '</div>';
                    let itemQty = 0, itemTot = 0, itemUnit = '';
                    html += '<table class="br-table"><thead><tr>'
                        + '<th>' + (bySalesOnly ? 'Salesman' : 'Nama Pelanggan') + '</th>'
                        + '<th class="num">Jumlah</th><th>Unit</th>'
                        + '<th class="num">Nilai Satuan</th><th class="num">Disc. %</th><th>Mata</th><th class="num">Total</th>'
                        + '</tr></thead><tbody>';

                    Object.keys(tree[wh][g][b]).sort().forEach(function (s) {
                        if (!bySalesOnly) {
                            html += '<tr class="br-jurnal-head"><td colspan="7" style="color:#0369a1;">' + esc(s) + '</td></tr>';
                        }
                        const list = aggItemMap(tree[wh][g][b][s], function (r) {
                            return bySalesOnly ? (r.salesman || 'Others') : (r.pelanggan || '-');
                        });
                        let sQty = 0, sTot = 0;
                        list.forEach(function (it) {
                            sQty += it.jumlah;
                            sTot += it.total;
                            itemUnit = it.unit;
                            html += '<tr>'
                                + '<td style="padding-left:' + (bySalesOnly ? '10px' : '22px') + ';">' + esc(it.label) + '</td>'
                                + '<td class="num">' + fmtNum(it.jumlah) + '</td>'
                                + '<td>' + esc(it.unit) + '</td>'
                                + '<td class="num">' + fmtNum(it.harga) + '</td>'
                                + '<td class="num">' + fmtNum(it.disc) + '</td>'
                                + '<td>IDR</td>'
                                + '<td class="num">' + fmtNum(it.total) + '</td>'
                                + '</tr>';
                        });
                        itemQty += sQty;
                        itemTot += sTot;
                        if (!bySalesOnly) {
                            html += '<tr class="br-subtotal"><td>Total ' + esc(s) + '</td><td class="num">' + fmtNum(sQty) + '</td><td>' + esc(itemUnit) + '</td><td colspan="2"></td><td>IDR</td><td class="num">' + fmtNum(sTot) + '</td></tr>';
                        }
                    });
                    grpTot += itemTot;
                    html += '<tr class="br-subtotal"><td>Total ' + esc(b) + '</td><td class="num">' + fmtNum(itemQty) + '</td><td>' + esc(itemUnit) + '</td><td colspan="2"></td><td>IDR</td><td class="num">' + fmtNum(itemTot) + '</td></tr>';
                    html += '</tbody></table>';
                });
                grandTotal += grpTot;
                html += '<table class="br-table"><tbody><tr class="br-subtotal"><td colspan="5">Total ' + esc(g) + '</td><td>IDR</td><td class="num">' + fmtNum(grpTot) + '</td></tr></tbody></table>';
            });
        });
        html += '<table class="br-table"><tbody><tr class="br-grandtotal"><td colspan="5">Grand Total</td><td>IDR</td><td class="num">' + fmtNum(grandTotal) + '</td></tr></tbody></table>';
        return html;
    }

    function loadReport() {
        const startDate = $('#filterStartDate').val();
        const endDate = $('#filterEndDate').val();
        const search = $('#filterSearch').val().trim();

        $('#brPeriodLabel').text(fmtPeriodText(startDate, endDate));
        $('#brStatusInfo').text('Memuat data...');
        $('#brContainer').html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat laporan...</div>');

        $.getJSON(ENDPOINT, {
            jenis: SLUG,
            start_date: startDate,
            end_date: endDate,
            search: search
        }).done(function (res) {
            const rows = (res && res.rows) ? res.rows : [];
            $('#brStatusInfo').text('Menampilkan ' + rows.length + ' data.');

            if (SLUG === 'kartu-stok-umum') {
                $('#brContainer').html(renderKartuStok(rows, false));
            } else if (SLUG === 'kartu-stok-per-pemasok-per-gudang') {
                $('#brContainer').html(renderKartuStok(rows, true));
            } else if (SLUG === 'barang-dibeli') {
                $('#brContainer').html(renderBarangRekap(rows, false));
            } else if (SLUG === 'barang-terjual') {
                $('#brContainer').html(renderBarangRekap(rows, true));
            } else if (SLUG === 'daftar-barang-umum') {
                $('#brContainer').html(renderDaftarBarangUmum(rows, false));
            } else if (SLUG === 'daftar-harga-jual') {
                $('#brContainer').html(renderDaftarBarangUmum(rows, true));
            } else if (SLUG === 'analisa-per-gudang') {
                $('#brContainer').html(renderPerGudang(rows, 'analisa'));
            } else if (SLUG === 'daftar-barang-per-gudang') {
                $('#brContainer').html(renderPerGudang(rows, 'daftar'));
            } else if (SLUG === 'formulir-stok-opname') {
                $('#brContainer').html(renderPerGudang(rows, 'opname'));
            } else if (SLUG === 'jurnal-persediaan') {
                $('#brContainer').html(renderJurnalPersediaan(rows));
            } else if (SLUG === 'transaksi-semua') {
                $('#brContainer').html(renderTransaksiBarang(rows, false));
            } else if (SLUG === 'transaksi-pemakaian-penyesuaian') {
                $('#brContainer').html(renderTransaksiBarang(rows, true));
            } else if (SLUG === 'mutasi-per-gudang') {
                $('#brContainer').html(renderMutasiBarang(rows, true));
            } else if (SLUG === 'mutasi-umum') {
                $('#brContainer').html(renderMutasiBarang(rows, false));
            } else if (SLUG === 'lot-mutasi') {
                $('#brContainer').html(renderLotMutasi(rows));
            } else if (SLUG === 'lot-tersedia') {
                $('#brContainer').html(renderLotTersedia(rows));
            } else if (SLUG === 'analisa-umum') {
                $('#brContainer').html(renderAnalisaUmum(rows));
            } else if (SLUG === 'laris-top10') {
                $('#brContainer').html(renderLarisSimple(rows, 'top10'));
            } else if (SLUG === 'laris-per-kelompok') {
                $('#brContainer').html(renderLarisSimple(rows, 'per-kelompok'));
            } else if (SLUG === 'laris-keuntungan') {
                $('#brContainer').html(renderLarisSimple(rows, 'keuntungan'));
            } else if (SLUG === 'laba-rugi-total') {
                $('#brContainer').html(renderLabaRugiTotal(rows));
            } else if (SLUG === 'laba-rugi-rincian') {
                $('#brContainer').html(renderLabaRugiRincian(rows));
            } else if (
                SLUG === 'penjualan-barang-pelanggan-faktur' ||
                SLUG === 'pembelian-barang-pemasok-faktur' ||
                SLUG === 'pembelian-pemasok-barang-invoice' ||
                SLUG === 'pembelian-pemasok-barang-total' ||
                SLUG === 'pembelian-barang-pemasok-total'
            ) {
                $('#brContainer').html(renderAnalisaTransaksiHierarki(rows, SLUG));
            } else if (SLUG === 'perputaran-cepat-per-kelompok') {
                $('#brContainer').html(renderPerputaranCepat(rows, true));
            } else if (SLUG === 'perputaran-cepat-semua') {
                $('#brContainer').html(renderPerputaranCepat(rows, false));
            } else if (
                SLUG === 'penjualan-pelanggan-barang-total' ||
                SLUG === 'penjualan-pelanggan-barang-faktur' ||
                SLUG === 'penjualan-salesman-barang-total' ||
                SLUG === 'penjualan-barang-pelanggan-total' ||
                SLUG === 'penjualan-barang-salesman-total' ||
                SLUG === 'penjualan-salesman-pelanggan-barang-faktur' ||
                SLUG === 'penjualan-salesman-barang-non-retur' ||
                SLUG === 'penjualan-salesman-pelanggan'
            ) {
                $('#brContainer').html(renderAnalisaPenjualanLanjutan(rows, SLUG));
            }
        }).fail(function () {
            $('#brStatusInfo').text('Gagal memuat data.');
            $('#brContainer').html('<div class="alert alert-danger">Gagal memuat data laporan.</div>');
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
