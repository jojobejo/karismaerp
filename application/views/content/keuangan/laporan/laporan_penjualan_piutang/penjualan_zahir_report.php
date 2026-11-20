<?php
$meta  = $report_meta ?? ['slug' => 'rincian', 'title' => 'Penjualan - Rincian'];
$slug  = $meta['slug'];
$title = $meta['title'];
?>

<style>
.pz-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.pz-paper {
    background: #fff;
    border: 1px solid #d9dee3;
    border-radius: 6px;
    padding: 24px 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    color: #1f2937;
    font-family: Arial, Helvetica, sans-serif;
}

.pz-meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #374151;
    margin-bottom: 4px;
}

.pz-header-center {
    text-align: center;
    margin-bottom: 18px;
}

.pz-company {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}

.pz-title {
    font-size: 20px;
    font-weight: 700;
    color: #0284c7;
    margin-bottom: 2px;
}

.pz-period {
    font-size: 12px;
    font-weight: 600;
    color: #b91c1c;
}

.pz-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
}

.pz-table thead th {
    background: #e5e7eb;
    border-top: 1.5px solid #4b5563;
    border-bottom: 1.5px solid #4b5563;
    padding: 6px 8px;
    font-weight: 700;
    color: #111827;
    text-align: left;
    white-space: nowrap;
}

.pz-table thead th.num,
.pz-table tbody td.num,
.pz-table tfoot td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.pz-table tbody td {
    padding: 5px 8px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}

.pz-table tfoot td {
    font-weight: 700;
    background: #e5e7eb;
    border-top: 1.5px solid #4b5563;
    border-bottom: 1.5px solid #4b5563;
    padding: 6px 8px;
}

.pz-rincian-card {
    border-top: 1.5px solid #1d4ed8;
    padding-top: 8px;
    margin-bottom: 16px;
    page-break-inside: avoid;
}

.pz-rincian-hdr {
    width: 100%;
    font-size: 11.5px;
    margin-bottom: 6px;
}

.pz-rincian-hdr td {
    padding: 1px 6px 1px 0;
    vertical-align: top;
}

.pz-rincian-lbl {
    color: #1d4ed8;
    font-weight: 700;
    white-space: nowrap;
    width: 105px;
}

.pz-rincian-ftr {
    width: 100%;
    font-size: 11.5px;
    margin-top: 4px;
}

.pz-rincian-ftr td {
    padding: 2px 6px 2px 0;
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

    .pz-paper {
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
                        <a href="<?= base_url('laporan/penjualan') ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Laporan Penjualan
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="pz-toolbar no-print">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" id="btnOpenFilter">
                            <i class="fas fa-filter mr-1"></i> Filter Data
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak Laporan
                        </button>
                    </div>
                    <div class="text-muted small" id="pzStatusInfo">
                        Pilih filter untuk menampilkan laporan.
                    </div>
                </div>

                <div class="pz-paper">
                    <div class="pz-meta">
                        <span><?= date('g:i A, F j, Y') ?></span>
                        <span><?= htmlspecialchars($title) ?></span>
                    </div>
                    <div class="pz-header-center">
                        <div class="pz-company">KARISMA <?= date('Y') ?></div>
                        <div class="pz-title"><?= htmlspecialchars($title) ?></div>
                        <div class="pz-period" id="pzPeriodLabel">-</div>
                    </div>

                    <div id="pzContainer">
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

                <div id="filterTabStandar">
                    <div class="form-row">
                        <div class="form-group col-md-6 mb-0">
                            <label class="small font-weight-bold">Dari Tanggal :</label>
                            <input type="date" class="form-control form-control-sm" id="filterStartDate" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label class="small font-weight-bold">Hingga Tanggal :</label>
                            <input type="date" class="form-control form-control-sm" id="filterEndDate" value="<?= date('Y-m-t') ?>">
                        </div>
                    </div>
                </div>

                <div id="filterTabPencarian" style="display: none;">
                    <div class="form-group">
                        <label class="small font-weight-bold">No. Reference / No. Faktur :</label>
                        <input type="text" class="form-control form-control-sm" id="filterNoRef" placeholder="Cari nomor faktur/retur...">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Pelanggan :</label>
                        <input type="text" class="form-control form-control-sm" id="filterPelanggan" placeholder="Nama pelanggan/kios...">
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-primary" id="btnApplyFilter">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var reportMeta = <?= json_encode($meta) ?>;
    var endpointUrl = <?= json_encode($report_endpoint) ?>;

    function esc(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fmtNum(val) {
        var num = parseFloat(val || 0);
        if (isNaN(num)) num = 0;
        var absVal = Math.abs(num).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        return num < 0 ? '-' + absVal : absVal;
    }

    function fmtDateDMY(iso) {
        if (!iso) return '';
        var parts = String(iso).split(' ')[0].split('-');
        if (parts.length !== 3) return iso;
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    function fmtDateLong(iso) {
        if (!iso) return '';
        var months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        var parts = String(iso).split('-');
        if (parts.length !== 3) return iso;
        var mIdx = parseInt(parts[1], 10) - 1;
        return parts[2] + ' ' + (months[mIdx] || parts[1]) + ' ' + parts[0];
    }

    function renderRincian(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data penjualan pada periode/filter ini.</div>';
        }

        var html = '';
        rows.forEach(function(h) {
            html += '<div class="pz-rincian-card">';
            html += '<table class="pz-rincian-hdr">';
            html += '<tr>';
            html += '<td class="pz-rincian-lbl">Tanggal :</td><td>' + fmtDateDMY(h.tanggal_faktur) + '</td>';
            html += '<td class="pz-rincian-lbl">Pelanggan :</td><td><strong>' + esc(h.pelanggan) + ' (' + esc(h.kd_customer) + ')</strong></td>';
            html += '<td class="pz-rincian-lbl">Mata Uang :</td><td>IDR &nbsp;&nbsp; 1.00</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td class="pz-rincian-lbl">No. Reference :</td><td>' + esc(h.no_faktur) + '</td>';
            html += '<td class="pz-rincian-lbl">Gudang :</td><td>' + esc(h.nama_gudang) + '</td>';
            html += '<td class="pz-rincian-lbl">Status :</td><td>' + esc(h.status) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td class="pz-rincian-lbl">Sales Order :</td><td>' + esc(h.no_so || '-') + '</td>';
            html += '<td class="pz-rincian-lbl">Catatan :</td><td colspan="3">' + esc(h.catatan) + '</td>';
            html += '</tr>';
            html += '</table>';

            html += '<table class="pz-table"><thead><tr>';
            html += '<th style="width:110px;">Kode</th>';
            html += '<th>Keterangan</th>';
            html += '<th class="num" style="width:70px;">Diterima</th>';
            html += '<th class="num" style="width:70px;">Dipesan</th>';
            html += '<th style="width:65px;">Satuan</th>';
            html += '<th class="num" style="width:110px;">Harga Satuan</th>';
            html += '<th class="num" style="width:65px;">Diskon</th>';
            html += '<th class="num" style="width:115px;">Sub Total</th>';
            html += '<th class="num" style="width:55px;">Pajak</th>';
            html += '</tr></thead><tbody>';

            (h.items || []).forEach(function(it) {
                html += '<tr>';
                html += '<td>' + esc(it.kd_barang) + '</td>';
                html += '<td>' + esc(it.nama_barang) + '</td>';
                html += '<td class="num">' + fmtNum(it.diterima) + '</td>';
                html += '<td class="num">' + fmtNum(it.dipesan) + '</td>';
                html += '<td>' + esc(it.satuan) + '</td>';
                html += '<td class="num">' + fmtNum(it.harga_satuan) + '</td>';
                html += '<td class="num">' + parseFloat(it.diskon || 0) + '%</td>';
                html += '<td class="num">' + fmtNum(it.sub_total) + '</td>';
                html += '<td class="num">' + parseFloat(it.pajak_pct || 0) + '%</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';

            html += '<table class="pz-rincian-ftr">';
            html += '<tr>';
            html += '<td style="width:110px;">Salesman :</td><td style="width:240px;">' + esc(h.salesman) + '</td>';
            html += '<td style="width:120px;">Biaya Pengiriman :</td><td class="num" style="width:90px;">0.00</td>';
            html += '<td style="text-align:right; font-weight:700; width:120px;">Total :</td><td class="num" style="font-weight:700; width:120px;">' + fmtNum(h.total) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Diinput Oleh :</td><td>' + esc(h.create_by) + '</td>';
            html += '<td>Diskon Akhir :</td><td class="num">0.00</td>';
            html += '<td style="text-align:right; font-weight:700;">Pembayaran :</td><td class="num" style="font-weight:700;">' + fmtNum(h.pembayaran) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td colspan="4"></td>';
            html += '<td style="text-align:right; font-weight:700;">Saldo :</td><td class="num" style="font-weight:700;">' + fmtNum(h.saldo) + '</td>';
            html += '</tr>';
            html += '</table>';
            html += '</div>';
        });

        return html;
    }

    function renderNegatifRangkuman(rows) {
        var html = '<table class="pz-table"><thead><tr>';
        html += '<th style="width:90px;">Tanggal</th>';
        html += '<th style="width:125px;">Reference</th>';
        html += '<th style="width:125px;">No. Pesanan</th>';
        html += '<th>Pelanggan</th>';
        html += '<th style="width:75px;">Mata Uang</th>';
        html += '<th class="num" style="width:110px;">Sub Total</th>';
        html += '<th class="num" style="width:85px;">Diskon</th>';
        html += '<th class="num" style="width:95px;">Pajak</th>';
        html += '<th class="num" style="width:120px;">Total Penjualan</th>';
        html += '<th class="num" style="width:110px;">Pembayaran</th>';
        html += '<th class="num" style="width:105px;">Saldo</th>';
        html += '</tr></thead><tbody>';

        var sSub = 0, sDisc = 0, sTax = 0, sTot = 0, sPay = 0, sBal = 0;
        (rows || []).forEach(function(r) {
            sSub += parseFloat(r.sub_total || 0);
            sDisc += parseFloat(r.diskon || 0);
            sTax += parseFloat(r.pajak || 0);
            sTot += parseFloat(r.total_penjualan || 0);
            sPay += parseFloat(r.pembayaran || 0);
            sBal += parseFloat(r.saldo || 0);

            html += '<tr>';
            html += '<td>' + fmtDateDMY(r.tanggal) + '</td>';
            html += '<td>' + esc(r.reference) + '</td>';
            html += '<td>' + esc(r.no_pesanan) + '</td>';
            html += '<td>' + esc(r.pelanggan) + '</td>';
            html += '<td>' + esc(r.mata_uang) + '</td>';
            html += '<td class="num">' + fmtNum(r.sub_total) + '</td>';
            html += '<td class="num">' + fmtNum(r.diskon) + '</td>';
            html += '<td class="num">' + fmtNum(r.pajak) + '</td>';
            html += '<td class="num">' + fmtNum(r.total_penjualan) + '</td>';
            html += '<td class="num">' + fmtNum(r.pembayaran) + '</td>';
            html += '<td class="num">' + fmtNum(r.saldo) + '</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td colspan="4" style="text-align:right;">Total :</td>';
        html += '<td>IDR</td>';
        html += '<td class="num">' + fmtNum(sSub) + '</td>';
        html += '<td class="num">' + fmtNum(sDisc) + '</td>';
        html += '<td class="num">' + fmtNum(sTax) + '</td>';
        html += '<td class="num">' + fmtNum(sTot) + '</td>';
        html += '<td class="num">' + fmtNum(sPay) + '</td>';
        html += '<td class="num">' + fmtNum(sBal) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderNegatifSederhana(rows) {
        var html = '<table class="pz-table"><tbody>';
        var total = 0;

        (rows || []).forEach(function(r) {
            var amt = parseFloat(r.total_penjualan || 0);
            total += amt;
            html += '<tr>';
            html += '<td style="width:100px;">' + fmtDateDMY(r.tanggal) + '</td>';
            html += '<td style="width:140px;">' + esc(r.reference) + '</td>';
            html += '<td>' + esc(r.pelanggan) + '</td>';
            html += '<td style="width:70px;">IDR</td>';
            html += '<td class="num" style="width:150px;">' + fmtNum(amt) + '</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td colspan="3" style="text-align:center;">Total :</td>';
        html += '<td>IDR</td>';
        html += '<td class="num">' + fmtNum(total) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderMataUangRangkuman(rows) {
        var html = '<table class="pz-table"><thead><tr>';
        html += '<th>Tanggal</th>';
        html += '<th class="num" style="width:200px;">Jumlah</th>';
        html += '<th class="num" style="width:200px;">Jumlah IDR</th>';
        html += '</tr></thead><tbody>';

        var totalIdr = 0;
        (rows || []).forEach(function(r) {
            var jml = parseFloat(r.jumlah || 0);
            var jmlIdr = parseFloat(r.jumlah_idr || 0);
            totalIdr += jmlIdr;
            html += '<tr>';
            html += '<td>' + esc(r.mata_uang || 'IDR') + '</td>';
            html += '<td class="num">' + fmtNum(jml) + '</td>';
            html += '<td class="num">' + fmtNum(jmlIdr) + '</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td style="text-align:center;">Total :</td>';
        html += '<td class="num">IDR</td>';
        html += '<td class="num">' + fmtNum(totalIdr) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderSederhana(rows, withCurrencyHeading) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data penjualan pada periode/filter ini.</div>';
        }

        var html = '';
        if (withCurrencyHeading) {
            html += '<div style="text-align:center; font-weight:700; color:#b91c1c; font-size:14px; margin-bottom:8px;">IDR</div>';
        }
        html += '<table class="pz-table"><thead><tr>';
        html += '<th style="width:90px;">Tanggal</th>';
        html += '<th style="width:125px;">No. Ref.</th>';
        html += '<th style="width:120px;">Order No.</th>';
        html += '<th>Nama Pelanggan</th>';
        html += '<th style="width:75px;">Mata Uang</th>';
        html += '<th class="num" style="width:130px;">Jumlah</th>';
        html += '<th class="num" style="width:140px;">Jumlah IDR</th>';
        html += '</tr></thead><tbody>';

        var total = 0;
        rows.forEach(function(r) {
            var jml = parseFloat(r.jumlah || 0);
            var jmlIdr = parseFloat(r.jumlah_idr || 0);
            total += jmlIdr;
            var custLabel = esc(r.pelanggan_nama) + (r.kd_customer ? ' (' + esc(r.kd_customer) + ')' : '');

            html += '<tr>';
            html += '<td>' + fmtDateDMY(r.tanggal_faktur) + '</td>';
            html += '<td>' + esc(r.no_faktur) + '</td>';
            html += '<td>' + esc(r.order_no) + '</td>';
            html += '<td>' + custLabel + '</td>';
            html += '<td>' + esc(r.mata_uang || 'IDR') + '</td>';
            html += '<td class="num">' + fmtNum(jml) + '</td>';
            html += '<td class="num">' + fmtNum(jmlIdr) + ' &#10003;</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td colspan="4" style="text-align:right;">Total :</td>';
        html += '<td>IDR</td>';
        html += '<td class="num">' + fmtNum(total) + '</td>';
        html += '<td class="num">' + fmtNum(total) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderPerSalesmanFaktur(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data penjualan pada periode/filter ini.</div>';
        }

        var groups = {};
        rows.forEach(function(r) {
            var key = r.salesman || 'Umum';
            if (!groups[key]) groups[key] = [];
            groups[key].push(r);
        });

        var html = '';
        var grandTotal = 0;
        Object.keys(groups).forEach(function(salesman) {
            var items = groups[salesman];
            var subTotal = 0;

            html += '<div style="margin-bottom:18px;">';
            html += '<div style="font-size:14px; font-weight:700; color:#0284c7; margin-bottom:2px;">' + esc(salesman) + '</div>';
            html += '<div style="font-size:12px; font-weight:700; color:#111827; margin-bottom:6px; padding-left:14px;">Salesman : ' + esc(salesman) + '</div>';
            html += '<table class="pz-table"><thead><tr>';
            html += '<th style="width:90px;">Tanggal</th>';
            html += '<th style="width:125px;">No. Ref.</th>';
            html += '<th style="width:120px;">Order No.</th>';
            html += '<th>Nama Pelanggan</th>';
            html += '<th style="width:75px;">Mata Uang</th>';
            html += '<th class="num" style="width:130px;">Jumlah</th>';
            html += '<th class="num" style="width:140px;">Jumlah IDR</th>';
            html += '</tr></thead><tbody>';

            items.forEach(function(r) {
                var jml = parseFloat(r.jumlah || 0);
                var jmlIdr = parseFloat(r.jumlah_idr || 0);
                subTotal += jmlIdr;
                grandTotal += jmlIdr;
                var custLabel = esc(r.pelanggan_nama) + (r.kd_customer ? ' (' + esc(r.kd_customer) + ')' : '');

                html += '<tr>';
                html += '<td>' + fmtDateDMY(r.tanggal_faktur) + '</td>';
                html += '<td>' + esc(r.no_faktur) + '</td>';
                html += '<td>' + esc(r.order_no) + '</td>';
                html += '<td>' + custLabel + '</td>';
                html += '<td>' + esc(r.mata_uang || 'IDR') + '</td>';
                html += '<td class="num">' + fmtNum(jml) + '</td>';
                html += '<td class="num">' + fmtNum(jmlIdr) + ' &#10003;</td>';
                html += '</tr>';
            });

            html += '</tbody><tfoot><tr>';
            html += '<td colspan="4" style="text-align:right;">Total ' + esc(salesman) + '</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNum(subTotal) + '</td>';
            html += '<td class="num">' + fmtNum(subTotal) + '</td>';
            html += '</tr></tfoot></table></div>';
        });

        html += '<table class="pz-table"><tfoot><tr>';
        html += '<td style="text-align:right;">Grand Total :</td>';
        html += '<td style="width:75px;">IDR</td>';
        html += '<td class="num" style="width:140px;">' + fmtNum(grandTotal) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderPerKelompokPelangganFaktur(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data penjualan pada periode/filter ini.</div>';
        }

        var groups = {};
        rows.forEach(function(r) {
            var grp = r.kelompok_pelanggan || 'Umum';
            var cust = r.pelanggan_nama || '-';
            if (!groups[grp]) groups[grp] = {};
            if (!groups[grp][cust]) groups[grp][cust] = [];
            groups[grp][cust].push(r);
        });

        var html = '';
        var grandTotal = 0;

        Object.keys(groups).forEach(function(grp) {
            html += '<div style="font-size:14px; font-weight:700; color:#0284c7; margin:10px 0 6px;">' + esc(grp) + '</div>';
            var custMap = groups[grp];

            Object.keys(custMap).forEach(function(custName) {
                var items = custMap[custName];
                var subTotal = 0;

                html += '<div style="font-size:12px; font-weight:700; color:#111827; margin:8px 0 4px;">Nama Pelanggan : ' + esc(custName) + '</div>';
                html += '<table class="pz-table" style="margin-bottom:10px;"><thead><tr>';
                html += '<th style="width:90px;">Tanggal</th>';
                html += '<th style="width:125px;">No. Ref</th>';
                html += '<th style="width:120px;">Order No.</th>';
                html += '<th>Salesman</th>';
                html += '<th style="width:75px;">Mata Uang</th>';
                html += '<th class="num" style="width:130px;">Jumlah</th>';
                html += '<th class="num" style="width:140px;">Jumlah IDR</th>';
                html += '</tr></thead><tbody>';

                items.forEach(function(r) {
                    var jml = parseFloat(r.jumlah || 0);
                    var jmlIdr = parseFloat(r.jumlah_idr || 0);
                    subTotal += jmlIdr;
                    grandTotal += jmlIdr;

                    html += '<tr>';
                    html += '<td>' + fmtDateDMY(r.tanggal_faktur) + '</td>';
                    html += '<td>' + esc(r.no_faktur) + '</td>';
                    html += '<td>' + esc(r.order_no) + '</td>';
                    html += '<td>' + esc(r.salesman) + '</td>';
                    html += '<td>' + esc(r.mata_uang || 'IDR') + '</td>';
                    html += '<td class="num">' + fmtNum(jml) + '</td>';
                    html += '<td class="num">' + fmtNum(jmlIdr) + ' &#10003;</td>';
                    html += '</tr>';
                });

                html += '</tbody><tfoot><tr>';
                html += '<td colspan="4" style="text-align:right;">Total ' + esc(custName) + '</td>';
                html += '<td>IDR</td>';
                html += '<td class="num">' + fmtNum(subTotal) + '</td>';
                html += '<td class="num">' + fmtNum(subTotal) + '</td>';
                html += '</tr></tfoot></table>';
            });
        });

        html += '<table class="pz-table"><tfoot><tr>';
        html += '<td style="text-align:right;">Grand Total :</td>';
        html += '<td style="width:75px;">IDR</td>';
        html += '<td class="num" style="width:140px;">' + fmtNum(grandTotal) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderPerKelompokSalesman(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data penjualan pada periode/filter ini.</div>';
        }

        var groups = {};
        rows.forEach(function(r) {
            var grp = r.kelompok_salesman || 'general';
            if (!groups[grp]) groups[grp] = [];
            groups[grp].push(r);
        });

        var html = '';
        var grandNilai = 0, grandBayar = 0, grandSaldo = 0;

        Object.keys(groups).forEach(function(grp) {
            var items = groups[grp];
            var subNilai = 0, subBayar = 0, subSaldo = 0;

            html += '<div style="margin-bottom:16px;">';
            html += '<div style="font-size:14px; font-weight:700; color:#0284c7; margin-bottom:4px;">' + esc(grp) + '</div>';
            html += '<table class="pz-table"><thead><tr>';
            html += '<th>Nama Salesman</th>';
            html += '<th style="width:85px;">Mata Uang</th>';
            html += '<th class="num" style="width:160px;">Nilai</th>';
            html += '<th class="num" style="width:160px;">Pembayaran</th>';
            html += '<th class="num" style="width:150px;">Saldo</th>';
            html += '</tr></thead><tbody>';

            items.forEach(function(r) {
                var n = parseFloat(r.nilai || 0);
                var p = parseFloat(r.pembayaran || 0);
                var s = parseFloat(r.saldo || 0);
                subNilai += n; subBayar += p; subSaldo += s;
                grandNilai += n; grandBayar += p; grandSaldo += s;

                html += '<tr>';
                html += '<td>' + esc(r.nama_salesman) + '</td>';
                html += '<td>' + esc(r.mata_uang || 'IDR') + '</td>';
                html += '<td class="num">' + fmtNum(n) + '</td>';
                html += '<td class="num">' + fmtNum(p) + '</td>';
                html += '<td class="num">' + fmtNum(s) + '</td>';
                html += '</tr>';
            });

            html += '</tbody><tfoot><tr>';
            html += '<td>Total ' + esc(grp) + '</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNum(subNilai) + '</td>';
            html += '<td class="num">' + fmtNum(subBayar) + '</td>';
            html += '<td class="num">' + fmtNum(subSaldo) + '</td>';
            html += '</tr></tfoot></table></div>';
        });

        html += '<table class="pz-table"><tfoot><tr>';
        html += '<td>Total :</td>';
        html += '<td style="width:85px;">IDR</td>';
        html += '<td class="num" style="width:160px;">' + fmtNum(grandNilai) + '</td>';
        html += '<td class="num" style="width:160px;">' + fmtNum(grandBayar) + '</td>';
        html += '<td class="num" style="width:150px;">' + fmtNum(grandSaldo) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderReturRangkuman(rows) {
        var html = '<table class="pz-table"><thead><tr>';
        html += '<th style="width:90px;">Tanggal</th>';
        html += '<th style="width:125px;">Reference</th>';
        html += '<th style="width:125px;">No. Pesanan</th>';
        html += '<th>Pelanggan</th>';
        html += '<th style="width:75px;">Mata Uang</th>';
        html += '<th class="num" style="width:110px;">Sub Total</th>';
        html += '<th class="num" style="width:85px;">Diskon</th>';
        html += '<th class="num" style="width:95px;">Pajak</th>';
        html += '<th class="num" style="width:120px;">Total Retur</th>';
        html += '<th class="num" style="width:110px;">Pembayaran</th>';
        html += '<th class="num" style="width:105px;">Saldo</th>';
        html += '</tr></thead><tbody>';

        var sSub = 0, sDisc = 0, sTax = 0, sTot = 0, sPay = 0, sBal = 0;
        (rows || []).forEach(function(r) {
            sSub += parseFloat(r.sub_total || 0);
            sDisc += parseFloat(r.diskon || 0);
            sTax += parseFloat(r.pajak || 0);
            sTot += parseFloat(r.total_retur || 0);
            sPay += parseFloat(r.pembayaran || 0);
            sBal += parseFloat(r.saldo || 0);

            html += '<tr>';
            html += '<td>' + fmtDateDMY(r.tanggal) + '</td>';
            html += '<td>' + esc(r.reference) + '</td>';
            html += '<td>' + esc(r.no_pesanan) + '</td>';
            html += '<td>' + esc(r.pelanggan) + '</td>';
            html += '<td>' + esc(r.mata_uang || 'IDR') + '</td>';
            html += '<td class="num">' + fmtNum(r.sub_total) + '</td>';
            html += '<td class="num">' + fmtNum(r.diskon) + '</td>';
            html += '<td class="num">' + fmtNum(r.pajak) + '</td>';
            html += '<td class="num">' + fmtNum(r.total_retur) + '</td>';
            html += '<td class="num">' + fmtNum(r.pembayaran) + '</td>';
            html += '<td class="num">' + fmtNum(r.saldo) + '</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td colspan="4" style="text-align:right;">Total :</td>';
        html += '<td>IDR</td>';
        html += '<td class="num">' + fmtNum(sSub) + '</td>';
        html += '<td class="num">' + fmtNum(sDisc) + '</td>';
        html += '<td class="num">' + fmtNum(sTax) + '</td>';
        html += '<td class="num">' + fmtNum(sTot) + '</td>';
        html += '<td class="num">' + fmtNum(sPay) + '</td>';
        html += '<td class="num">' + fmtNum(sBal) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderReturSederhana(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data retur penjualan pada periode/filter ini.</div>';
        }

        var html = '<table class="pz-table"><thead><tr>';
        html += '<th style="width:90px;">Tanggal</th>';
        html += '<th style="width:125px;">No.Ref.</th>';
        html += '<th style="width:120px;">No. Pesanan</th>';
        html += '<th>Nama Pelanggan</th>';
        html += '<th style="width:75px;">Mata Uang</th>';
        html += '<th class="num" style="width:130px;">Jumlah</th>';
        html += '<th class="num" style="width:140px;">Jumlah IDR</th>';
        html += '</tr></thead><tbody>';

        var total = 0;
        rows.forEach(function(r) {
            var amt = parseFloat(r.total_retur || 0);
            total += amt;
            var custLabel = esc(r.pelanggan) + (r.kd_customer ? ' (' + esc(r.kd_customer) + ')' : '');

            html += '<tr>';
            html += '<td>' + fmtDateDMY(r.tanggal) + '</td>';
            html += '<td>' + esc(r.reference) + '</td>';
            html += '<td>' + esc(r.no_pesanan) + '</td>';
            html += '<td>' + custLabel + '</td>';
            html += '<td>' + esc(r.mata_uang || 'IDR') + '</td>';
            html += '<td class="num">' + fmtNum(amt) + '</td>';
            html += '<td class="num">' + fmtNum(amt) + ' &#10003;</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td colspan="4" style="text-align:center;">Total :</td>';
        html += '<td>IDR</td>';
        html += '<td class="num">' + fmtNum(total) + '</td>';
        html += '<td class="num">' + fmtNum(total) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderReturRincian(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data retur penjualan pada periode/filter ini.</div>';
        }

        var html = '';
        rows.forEach(function(h) {
            html += '<div class="pz-rincian-card">';
            html += '<table class="pz-rincian-hdr">';
            html += '<tr>';
            html += '<td class="pz-rincian-lbl">Tanggal :</td><td>' + fmtDateDMY(h.tanggal_retur) + '</td>';
            html += '<td class="pz-rincian-lbl">Pelanggan :</td><td><strong>' + esc(h.pelanggan) + ' (' + esc(h.kd_customer) + ')</strong></td>';
            html += '<td class="pz-rincian-lbl">Mata Uang :</td><td>IDR &nbsp;&nbsp; 1.00</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td class="pz-rincian-lbl">No. Reference :</td><td>' + esc(h.no_retur) + '</td>';
            html += '<td class="pz-rincian-lbl">Gudang :</td><td>' + esc(h.nama_gudang) + '</td>';
            html += '<td class="pz-rincian-lbl">Status :</td><td>' + esc(h.status_retur) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td class="pz-rincian-lbl">No. SPR :</td><td>' + esc(h.no_spr || '-') + '</td>';
            html += '<td class="pz-rincian-lbl">Catatan :</td><td colspan="3">' + esc(h.catatan) + '</td>';
            html += '</tr>';
            html += '</table>';

            html += '<table class="pz-table"><thead><tr>';
            html += '<th style="width:110px;">Kode</th>';
            html += '<th>Keterangan</th>';
            html += '<th class="num" style="width:75px;">Kembali</th>';
            html += '<th class="num" style="width:85px;">Telah Dipesan</th>';
            html += '<th style="width:65px;">Satuan</th>';
            html += '<th class="num" style="width:110px;">Harga Satuan</th>';
            html += '<th class="num" style="width:65px;">Diskon</th>';
            html += '<th class="num" style="width:115px;">Sub Total</th>';
            html += '<th class="num" style="width:55px;">Pajak</th>';
            html += '</tr></thead><tbody>';

            (h.items || []).forEach(function(it) {
                html += '<tr>';
                html += '<td>' + esc(it.kd_barang) + '</td>';
                html += '<td>' + esc(it.nama_barang) + '</td>';
                html += '<td class="num">' + fmtNum(it.kembali) + '</td>';
                html += '<td class="num">' + fmtNum(it.telah_dipesan) + '</td>';
                html += '<td>' + esc(it.satuan) + '</td>';
                html += '<td class="num">' + fmtNum(it.harga_satuan) + '</td>';
                html += '<td class="num">' + parseFloat(it.diskon || 0) + '%</td>';
                html += '<td class="num">' + fmtNum(it.sub_total) + '</td>';
                html += '<td class="num">' + parseFloat(it.pajak_pct || 0) + '%</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';

            html += '<table class="pz-rincian-ftr">';
            html += '<tr>';
            html += '<td style="width:110px;">Salesman :</td><td style="width:240px;">' + esc(h.salesman) + '</td>';
            html += '<td style="width:120px;">Biaya Pengiriman :</td><td class="num" style="width:90px;">0.00</td>';
            html += '<td style="text-align:right; font-weight:700; width:120px;">Total :</td><td class="num" style="font-weight:700; width:120px;">' + fmtNum(h.total) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td>Diinput Oleh :</td><td>' + esc(h.create_by) + '</td>';
            html += '<td>Diskon Akhir :</td><td class="num">0.00</td>';
            html += '<td style="text-align:right; font-weight:700;">Pembayaran :</td><td class="num" style="font-weight:700;">' + fmtNum(h.pembayaran) + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td colspan="4"></td>';
            html += '<td style="text-align:right; font-weight:700;">Saldo :</td><td class="num" style="font-weight:700;">' + fmtNum(h.saldo) + '</td>';
            html += '</tr>';
            html += '</table>';
            html += '</div>';
        });

        return html;
    }

    function loadReport() {
        var params = {
            jenis: reportMeta.slug,
            start_date: $('#filterStartDate').val() || '',
            end_date: $('#filterEndDate').val() || '',
            no_ref: $('#filterNoRef').val() || '',
            pelanggan: $('#filterPelanggan').val() || ''
        };

        $('#pzContainer').html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat laporan...</div>');

        $.getJSON(endpointUrl, params)
            .done(function(res) {
                if (!res || !res.success) {
                    $('#pzContainer').html('<div class="alert alert-danger">Gagal memuat data laporan.</div>');
                    return;
                }
                $('#pzPeriodLabel').text(fmtDateLong(res.start_date) + ' - ' + fmtDateLong(res.end_date));

                var html = '';
                if (reportMeta.slug === 'rincian') {
                    html = renderRincian(res.rows);
                } else if (reportMeta.slug === 'negatif-rangkuman') {
                    html = renderNegatifRangkuman(res.rows);
                } else if (reportMeta.slug === 'negatif-sederhana') {
                    html = renderNegatifSederhana(res.rows);
                } else if (reportMeta.slug === 'per-salesman-faktur') {
                    html = renderPerSalesmanFaktur(res.rows);
                } else if (reportMeta.slug === 'per-kelompok-pelanggan-faktur') {
                    html = renderPerKelompokPelangganFaktur(res.rows);
                } else if (reportMeta.slug === 'sederhana') {
                    html = renderSederhana(res.rows, false);
                } else if (reportMeta.slug === 'per-mata-uang-sederhana') {
                    html = renderSederhana(res.rows, true);
                } else if (reportMeta.slug === 'per-kelompok-salesman') {
                    html = renderPerKelompokSalesman(res.rows);
                } else if (reportMeta.slug === 'retur-rangkuman') {
                    html = renderReturRangkuman(res.rows);
                } else if (reportMeta.slug === 'retur-sederhana') {
                    html = renderReturSederhana(res.rows);
                } else if (reportMeta.slug === 'retur-rincian') {
                    html = renderReturRincian(res.rows);
                } else {
                    html = renderMataUangRangkuman(res.rows);
                }

                $('#pzContainer').html(html);
                $('#pzStatusInfo').text('Menampilkan ' + (res.rows ? res.rows.length : 0) + ' entri.');
            })
            .fail(function() {
                $('#pzContainer').html('<div class="alert alert-danger">Terjadi kesalahan saat mengambil data laporan.</div>');
            });
    }

    $(function() {
        $('.zahir-filter-tab').on('click', function() {
            $('.zahir-filter-tab').removeClass('active');
            $(this).addClass('active');
            var tab = $(this).data('tab');
            if (tab === 'pencarian') {
                $('#filterTabStandar').hide();
                $('#filterTabPencarian').show();
            } else {
                $('#filterTabPencarian').hide();
                $('#filterTabStandar').show();
            }
        });

        $('#btnOpenFilter').on('click', function() {
            $('#modalFilterData').modal('show');
        });

        $('#btnApplyFilter').on('click', function() {
            $('#modalFilterData').modal('hide');
            loadReport();
        });

        $('#modalFilterData').modal('show');
    });
})();
</script>
