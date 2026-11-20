<?php
$meta  = $report_meta ?? ['slug' => 'kartu-piutang-salesman', 'title' => 'Kartu Piutang Per Salesman'];
$slug  = $meta['slug'];
$title = $meta['title'];
?>

<style>
.pu-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.pu-paper {
    background: #fff;
    border: 1px solid #d9dee3;
    border-radius: 6px;
    padding: 24px 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    color: #1f2937;
    font-family: Arial, Helvetica, sans-serif;
}

.pu-meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #374151;
    margin-bottom: 4px;
}

.pu-header-center {
    text-align: center;
    margin-bottom: 18px;
}

.pu-company {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}

.pu-title {
    font-size: 20px;
    font-weight: 700;
    color: #0284c7;
    margin-bottom: 2px;
}

.pu-period {
    font-size: 12px;
    font-weight: 600;
    color: #b91c1c;
}

.pu-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
}

.pu-table thead th {
    background: #e5e7eb;
    border-top: 1.5px solid #4b5563;
    border-bottom: 1.5px solid #4b5563;
    padding: 6px 8px;
    font-weight: 700;
    color: #111827;
    text-align: left;
    white-space: nowrap;
}

.pu-table thead th.num,
.pu-table tbody td.num,
.pu-table tfoot td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.pu-table tbody td {
    padding: 4px 8px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}

.pu-table tfoot td,
.pu-subtotal td {
    font-weight: 700;
    background: #f3f4f6;
    border-top: 1px solid #6b7280;
    border-bottom: 1.5px solid #374151;
    padding: 5px 8px;
}

.pu-inv-total td {
    font-weight: 600;
    border-top: 1px dashed #cbd5e1;
    border-bottom: 1px solid #e2e8f0;
    background: #fafafa;
}

.pu-sales-heading {
    text-align: center;
    font-size: 14px;
    font-weight: 700;
    color: #0284c7;
    margin: 14px 0 6px;
}

.pu-cust-heading {
    font-size: 12.5px;
    font-weight: 700;
    color: #111827;
    margin: 10px 0 4px;
}

.pu-cust-heading.blue {
    text-align: center;
    color: #0284c7;
    font-size: 13px;
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

    .pu-paper {
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
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Laporan Penjualan & Piutang
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="pu-toolbar no-print">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" id="btnOpenFilter">
                            <i class="fas fa-filter mr-1"></i> Filter Data
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak Laporan
                        </button>
                    </div>
                    <div class="text-muted small" id="puStatusInfo">
                        Pilih filter untuk menampilkan laporan.
                    </div>
                </div>

                <div class="pu-paper">
                    <?php if ($slug !== 'surat-tagihan-pelanggan'): ?>
                        <div class="pu-meta">
                            <span><?= date('g:i A, F j, Y') ?></span>
                            <span><?= htmlspecialchars($title) ?></span>
                        </div>
                        <div class="pu-header-center">
                            <div class="pu-company">KARISMA <?= date('Y') ?></div>
                            <div class="pu-title"><?= htmlspecialchars($title) ?></div>
                            <div class="pu-period" id="puPeriodLabel">-</div>
                        </div>
                    <?php endif; ?>

                    <div id="puContainer">
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
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Pelanggan / Salesman / No. Faktur :</label>
                        <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Ketik nama pelanggan, salesman, atau no. faktur...">
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

    function fmtNumBlankZero(val) {
        var num = parseFloat(val || 0);
        if (!num || Math.abs(num) < 0.0001) return '';
        return fmtNum(num);
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

    function renderKartuCustomerBlock(custName, kdCust, invoices, blueHeader) {
        var html = '<div class="pu-cust-heading ' + (blueHeader ? 'blue' : '') + '">' + esc(custName) + ' | ' + esc(kdCust) + ' (IDR)</div>';
        html += '<table class="pu-table"><thead><tr>';
        html += '<th style="width:90px;">Tanggal</th>';
        html += '<th style="width:45px;">Tp</th>';
        html += '<th style="width:130px;">No.Ref</th>';
        html += '<th style="width:95px;">Jatuh Tempo</th>';
        html += '<th style="width:75px;">Mata Uang</th>';
        html += '<th class="num" style="width:135px;">Debet</th>';
        html += '<th class="num" style="width:135px;">Kredit</th>';
        html += '<th class="num" style="width:130px;">Saldo</th>';
        html += '</tr></thead><tbody>';

        var custDeb = 0, custKre = 0, custBal = 0;
        invoices.forEach(function(inv) {
            var deb = parseFloat(inv.debet || 0);
            var kre = parseFloat(inv.kredit || 0);
            var bal = parseFloat(inv.saldo || 0);
            custDeb += deb; custKre += kre; custBal += bal;

            html += '<tr>';
            html += '<td>' + fmtDateDMY(inv.tanggal_faktur) + '</td>';
            html += '<td>SJ</td>';
            html += '<td>' + esc(inv.no_faktur) + '</td>';
            html += '<td>' + fmtDateDMY(inv.jatuh_tempo) + '</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNum(deb) + '</td>';
            html += '<td></td><td></td>';
            html += '</tr>';

            (inv.pembayaran || []).forEach(function(p) {
                html += '<tr>';
                html += '<td>' + fmtDateDMY(p.tanggal) + '</td>';
                html += '<td>' + esc(p.tp) + '</td>';
                html += '<td>' + esc(p.no_ref) + '</td>';
                html += '<td></td>';
                html += '<td>IDR</td>';
                html += '<td></td>';
                html += '<td class="num">' + fmtNum(p.kredit) + '</td>';
                html += '<td></td>';
                html += '</tr>';
            });

            html += '<tr class="pu-inv-total">';
            html += '<td colspan="4" style="text-align:right;">Saldo ' + esc(inv.no_faktur) + ' :</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNum(deb) + '</td>';
            html += '<td class="num">' + fmtNum(kre) + '</td>';
            html += '<td class="num">' + fmtNum(bal) + '</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td colspan="4" style="text-align:right;">Saldo ' + esc(custName) + ' :</td>';
        html += '<td>IDR</td>';
        html += '<td class="num">' + fmtNum(custDeb) + '</td>';
        html += '<td class="num">' + fmtNum(custKre) + '</td>';
        html += '<td class="num">' + fmtNum(custBal) + '</td>';
        html += '</tr></tfoot></table>';

        return { html: html, deb: custDeb, kre: custKre, bal: custBal };
    }

    function renderKartuPiutangSalesman(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data piutang pada periode/filter ini.</div>';
        }

        var salesMap = {};
        rows.forEach(function(r) {
            var s = r.salesman || 'Umum';
            var ck = (r.nama_pelanggan || '-') + '||' + (r.kd_customer || '');
            if (!salesMap[s]) salesMap[s] = {};
            if (!salesMap[s][ck]) salesMap[s][ck] = [];
            salesMap[s][ck].push(r);
        });

        var html = '';
        Object.keys(salesMap).forEach(function(salesman) {
            html += '<div class="pu-sales-heading">' + esc(salesman) + '</div>';
            var sDeb = 0, sKre = 0, sBal = 0;
            var custMap = salesMap[salesman];
            Object.keys(custMap).forEach(function(ck) {
                var parts = ck.split('||');
                var res = renderKartuCustomerBlock(parts[0], parts[1], custMap[ck], false);
                html += res.html;
                sDeb += res.deb; sKre += res.kre; sBal += res.bal;
            });

            html += '<table class="pu-table" style="margin-top:4px; margin-bottom:16px;"><tfoot><tr>';
            html += '<td style="text-align:right;">Saldo ' + esc(salesman) + ' :</td>';
            html += '<td style="width:75px;">IDR</td>';
            html += '<td class="num" style="width:135px;">' + fmtNum(sDeb) + '</td>';
            html += '<td class="num" style="width:135px;">' + fmtNum(sKre) + '</td>';
            html += '<td class="num" style="width:130px;">' + fmtNum(sBal) + '</td>';
            html += '</tr></tfoot></table>';
        });

        return html;
    }

    function renderKartuPiutangMataUangDasar(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data piutang pada periode/filter ini.</div>';
        }

        var custMap = {};
        rows.forEach(function(r) {
            var ck = (r.nama_pelanggan || '-') + '||' + (r.kd_customer || '');
            if (!custMap[ck]) custMap[ck] = [];
            custMap[ck].push(r);
        });

        var html = '';
        Object.keys(custMap).forEach(function(ck) {
            var parts = ck.split('||');
            html += renderKartuCustomerBlock(parts[0], parts[1], custMap[ck], true).html;
        });
        return html;
    }

    function renderKartuSalesmanRangkuman(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data piutang pada periode/filter ini.</div>';
        }

        var salesMap = {};
        rows.forEach(function(r) {
            var s = r.salesman || 'Umum';
            var c = r.nama_pelanggan || '-';
            if (!salesMap[s]) salesMap[s] = {};
            if (!salesMap[s][c]) salesMap[s][c] = { deb: 0, kre: 0, bal: 0 };
            salesMap[s][c].deb += parseFloat(r.debet || 0);
            salesMap[s][c].kre += parseFloat(r.kredit || 0);
            salesMap[s][c].bal += parseFloat(r.saldo || 0);
        });

        var html = '';
        Object.keys(salesMap).forEach(function(salesman) {
            html += '<div class="pu-sales-heading">' + esc(salesman) + '</div>';
            html += '<table class="pu-table" style="margin-bottom:14px;"><thead><tr>';
            html += '<th>Nama Pelanggan</th>';
            html += '<th style="width:85px;">Mata Uang</th>';
            html += '<th class="num" style="width:150px;">Nilai Penjualan</th>';
            html += '<th class="num" style="width:150px;">Pembayaran</th>';
            html += '<th class="num" style="width:140px;">Saldo</th>';
            html += '</tr></thead><tbody>';

            var sDeb = 0, sKre = 0, sBal = 0;
            var custMap = salesMap[salesman];
            Object.keys(custMap).forEach(function(c) {
                var item = custMap[c];
                sDeb += item.deb; sKre += item.kre; sBal += item.bal;
                html += '<tr>';
                html += '<td>' + esc(c) + '</td>';
                html += '<td>IDR</td>';
                html += '<td class="num">' + fmtNum(item.deb) + '</td>';
                html += '<td class="num">' + fmtNum(item.kre) + '</td>';
                html += '<td class="num">' + fmtNum(item.bal) + '</td>';
                html += '</tr>';
            });

            html += '</tbody><tfoot><tr>';
            html += '<td>Saldo ' + esc(salesman) + ' :</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNum(sDeb) + '</td>';
            html += '<td class="num">' + fmtNum(sKre) + '</td>';
            html += '<td class="num">' + fmtNum(sBal) + '</td>';
            html += '</tr></tfoot></table>';
        });

        return html;
    }

    function renderMutasiPiutang(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada mutasi piutang pada periode/filter ini.</div>';
        }

        var groups = {};
        rows.forEach(function(r) {
            var grp = r.kelompok || 'Umum';
            if (!groups[grp]) groups[grp] = [];
            groups[grp].push(r);
        });

        var html = '';
        Object.keys(groups).forEach(function(grp) {
            var items = groups[grp];
            var sAwal = 0, sDeb = 0, sKre = 0, sAkhir = 0;

            html += '<table class="pu-table" style="margin-bottom:14px;"><thead><tr>';
            html += '<th>' + esc(grp) + ' -</th>';
            html += '<th style="width:65px;"></th>';
            html += '<th class="num" style="width:140px;">Saldo Awal</th>';
            html += '<th class="num" style="width:140px;">Debet</th>';
            html += '<th class="num" style="width:140px;">Kredit</th>';
            html += '<th class="num" style="width:140px;">Saldo Akhir</th>';
            html += '</tr></thead><tbody>';

            items.forEach(function(r) {
                sAwal += parseFloat(r.saldo_awal || 0);
                sDeb += parseFloat(r.debet || 0);
                sKre += parseFloat(r.kredit || 0);
                sAkhir += parseFloat(r.saldo_akhir || 0);

                html += '<tr>';
                html += '<td>' + esc(r.nama_pelanggan) + '</td>';
                html += '<td>IDR</td>';
                html += '<td class="num">' + fmtNum(r.saldo_awal) + '</td>';
                html += '<td class="num">' + fmtNumBlankZero(r.debet) + '</td>';
                html += '<td class="num">' + fmtNumBlankZero(r.kredit) + '</td>';
                html += '<td class="num">' + fmtNum(r.saldo_akhir) + '</td>';
                html += '</tr>';
            });

            html += '</tbody><tfoot><tr>';
            html += '<td>Total ' + esc(grp) + '</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNum(sAwal) + '</td>';
            html += '<td class="num">' + fmtNum(sDeb) + '</td>';
            html += '<td class="num">' + fmtNum(sKre) + '</td>';
            html += '<td class="num">' + fmtNum(sAkhir) + '</td>';
            html += '</tr></tfoot></table>';
        });

        return html;
    }

    function renderPembayaranPelanggan(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada pembayaran pelanggan pada periode/filter ini.</div>';
        }

        var html = '<table class="pu-table"><thead><tr>';
        html += '<th style="width:95px;">Tanggal</th>';
        html += '<th style="width:120px;">No.Ref</th>';
        html += '<th>Nama Pelanggan / No. Faktur &amp; Tanggal Faktur</th>';
        html += '<th style="width:75px;">Mata Uang</th>';
        html += '<th class="num" style="width:110px;">Diskon</th>';
        html += '<th class="num" style="width:140px;">Pembayaran</th>';
        html += '</tr></thead><tbody>';

        var grandTotal = 0;
        rows.forEach(function(r) {
            var bayar = parseFloat(r.pembayaran || 0);
            var disk = parseFloat(r.diskon || 0);
            grandTotal += bayar;

            html += '<tr style="background:#fafafa; font-weight:600;">';
            html += '<td>' + fmtDateDMY(r.tanggal_pembayaran) + '</td>';
            html += '<td>' + esc(r.no_ref) + '</td>';
            html += '<td colspan="4">' + esc(r.nama_pelanggan) + '</td>';
            html += '</tr>';

            html += '<tr>';
            html += '<td></td><td></td>';
            html += '<td style="padding-left:18px;">' + esc(r.no_faktur) + ' &nbsp;&nbsp;&nbsp; ' + fmtDateDMY(r.tanggal_faktur) + '</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNumBlankZero(disk) + '</td>';
            html += '<td class="num">' + fmtNum(bayar) + '</td>';
            html += '</tr>';

            html += '<tr class="pu-subtotal">';
            html += '<td colspan="3" style="text-align:right;">Total Pembayaran :</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNumBlankZero(disk) + '</td>';
            html += '<td class="num">' + fmtNum(bayar) + '</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td colspan="3" style="text-align:right;">Grand Total Pembayaran :</td>';
        html += '<td>IDR</td>';
        html += '<td></td>';
        html += '<td class="num">' + fmtNum(grandTotal) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderPiutangPerMataUang(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada saldo piutang pada periode/filter ini.</div>';
        }

        var html = '<table class="pu-table"><thead><tr>';
        html += '<th>Nama Pelanggan</th>';
        html += '<th style="width:75px;">Mata Uang</th>';
        html += '<th class="num" style="width:125px;">0 - 30 Hari</th>';
        html += '<th class="num" style="width:125px;">30 - 60 Hari</th>';
        html += '<th class="num" style="width:125px;">60 - 90 Hari</th>';
        html += '<th class="num" style="width:125px;">&gt; 90 Hari</th>';
        html += '</tr></thead><tbody>';

        var s1 = 0, s2 = 0, s3 = 0, s4 = 0;
        rows.forEach(function(r) {
            var b1 = parseFloat(r.bucket_0_30 || 0);
            var b2 = parseFloat(r.bucket_30_60 || 0);
            var b3 = parseFloat(r.bucket_60_90 || 0);
            var b4 = parseFloat(r.bucket_gt_90 || 0);
            s1 += b1; s2 += b2; s3 += b3; s4 += b4;

            html += '<tr>';
            html += '<td>' + esc(r.nama_pelanggan) + ' | ' + esc(r.kd_customer) + '</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNumBlankZero(b1) + '</td>';
            html += '<td class="num">' + fmtNumBlankZero(b2) + '</td>';
            html += '<td class="num">' + fmtNumBlankZero(b3) + '</td>';
            html += '<td class="num">' + fmtNum(b4) + '</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td>Total :</td>';
        html += '<td>IDR</td>';
        html += '<td class="num">' + fmtNum(s1) + '</td>';
        html += '<td class="num">' + fmtNum(s2) + '</td>';
        html += '<td class="num">' + fmtNum(s3) + '</td>';
        html += '<td class="num">' + fmtNum(s4) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderAgingRincianTable(items, refColTitle) {
        var html = '<table class="pu-table" style="margin-bottom:10px;"><thead><tr>';
        html += '<th style="width:90px;">Tanggal</th>';
        html += '<th style="width:95px;">Jatuh Tempo</th>';
        html += '<th style="width:40px;">Tp</th>';
        html += '<th style="width:120px;">' + refColTitle + '</th>';
        html += '<th style="width:70px;">Mata Uang</th>';
        html += '<th class="num" style="width:115px;">0 - 30 Hari</th>';
        html += '<th class="num" style="width:115px;">30 - 60 Hari</th>';
        html += '<th class="num" style="width:115px;">60 - 90 Hari</th>';
        html += '<th class="num" style="width:115px;">&gt; 90 Hari</th>';
        html += '</tr></thead><tbody>';

        var s1 = 0, s2 = 0, s3 = 0, s4 = 0;
        items.forEach(function(r) {
            var b1 = parseFloat(r.bucket_0_30 || 0);
            var b2 = parseFloat(r.bucket_30_60 || 0);
            var b3 = parseFloat(r.bucket_60_90 || 0);
            var b4 = parseFloat(r.bucket_gt_90 || 0);
            s1 += b1; s2 += b2; s3 += b3; s4 += b4;

            html += '<tr>';
            html += '<td>' + fmtDateDMY(r.tanggal_faktur) + '</td>';
            html += '<td>' + fmtDateDMY(r.jatuh_tempo) + '</td>';
            html += '<td>SJ</td>';
            html += '<td>' + esc(r.no_faktur) + '</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNumBlankZero(b1) + '</td>';
            html += '<td class="num">' + fmtNumBlankZero(b2) + '</td>';
            html += '<td class="num">' + fmtNumBlankZero(b3) + '</td>';
            html += '<td class="num">' + fmtNumBlankZero(b4) + '</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td colspan="4" style="text-align:right;">Saldo :</td>';
        html += '<td>IDR</td>';
        html += '<td class="num">' + fmtNumBlankZero(s1) + '</td>';
        html += '<td class="num">' + fmtNumBlankZero(s2) + '</td>';
        html += '<td class="num">' + fmtNumBlankZero(s3) + '</td>';
        html += '<td class="num">' + fmtNum(s4) + '</td>';
        html += '</tr></tfoot></table>';
        return html;
    }

    function renderAgingRincianByCustomer(rows, withCurrencySubheader) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada rincian piutang pada periode/filter ini.</div>';
        }

        var custMap = {};
        rows.forEach(function(r) {
            var ck = (r.nama_pelanggan || '-') + '||' + (r.kd_customer || '');
            if (!custMap[ck]) custMap[ck] = [];
            custMap[ck].push(r);
        });

        var html = '';
        Object.keys(custMap).forEach(function(ck) {
            var parts = ck.split('||');
            html += '<div class="pu-cust-heading blue">' + esc(parts[0]) + ' | ' + esc(parts[1]) + ' (IDR)</div>';
            if (withCurrencySubheader) {
                html += '<div style="text-align:center; font-weight:700; font-size:11.5px; margin-bottom:4px;">IDR</div>';
            }
            html += renderAgingRincianTable(custMap[ck], 'No.Ref.');
        });
        return html;
    }

    function renderAgingPerSalesmanRangkuman(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data piutang per salesman pada periode/filter ini.</div>';
        }

        var salesMap = {};
        rows.forEach(function(r) {
            var s = r.salesman || 'Umum';
            var ck = (r.nama_pelanggan || '-') + ' | ' + (r.kd_customer || '');
            if (!salesMap[s]) salesMap[s] = {};
            if (!salesMap[s][ck]) salesMap[s][ck] = { b1: 0, b2: 0, b3: 0, b4: 0 };
            salesMap[s][ck].b1 += parseFloat(r.bucket_0_30 || 0);
            salesMap[s][ck].b2 += parseFloat(r.bucket_30_60 || 0);
            salesMap[s][ck].b3 += parseFloat(r.bucket_60_90 || 0);
            salesMap[s][ck].b4 += parseFloat(r.bucket_gt_90 || 0);
        });

        var html = '';
        Object.keys(salesMap).forEach(function(salesman) {
            html += '<div class="pu-sales-heading">' + esc(salesman) + '</div>';
            html += '<table class="pu-table" style="margin-bottom:14px;"><thead><tr>';
            html += '<th>Nama Pelanggan</th>';
            html += '<th style="width:75px;">Mata Uang</th>';
            html += '<th class="num" style="width:120px;">0 - 30 Hari</th>';
            html += '<th class="num" style="width:120px;">30 - 60 Hari</th>';
            html += '<th class="num" style="width:120px;">60 - 90 Hari</th>';
            html += '<th class="num" style="width:120px;">&gt; 90 Hari</th>';
            html += '</tr></thead><tbody>';

            var s1 = 0, s2 = 0, s3 = 0, s4 = 0;
            var custMap = salesMap[salesman];
            Object.keys(custMap).forEach(function(ck) {
                var item = custMap[ck];
                s1 += item.b1; s2 += item.b2; s3 += item.b3; s4 += item.b4;
                html += '<tr>';
                html += '<td>' + esc(ck) + '</td>';
                html += '<td>IDR</td>';
                html += '<td class="num">' + fmtNumBlankZero(item.b1) + '</td>';
                html += '<td class="num">' + fmtNumBlankZero(item.b2) + '</td>';
                html += '<td class="num">' + fmtNumBlankZero(item.b3) + '</td>';
                html += '<td class="num">' + fmtNum(item.b4) + '</td>';
                html += '</tr>';
            });

            html += '</tbody><tfoot><tr>';
            html += '<td style="text-align:right;">Total :</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNumBlankZero(s1) + '</td>';
            html += '<td class="num">' + fmtNumBlankZero(s2) + '</td>';
            html += '<td class="num">' + fmtNumBlankZero(s3) + '</td>';
            html += '<td class="num">' + fmtNum(s4) + '</td>';
            html += '</tr></tfoot></table>';
        });
        return html;
    }

    function renderAgingPerSalesmanRincian(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada rincian piutang per salesman pada periode/filter ini.</div>';
        }

        var salesMap = {};
        rows.forEach(function(r) {
            var s = r.salesman || 'Umum';
            var ck = (r.nama_pelanggan || '-') + '||' + (r.kd_customer || '');
            if (!salesMap[s]) salesMap[s] = {};
            if (!salesMap[s][ck]) salesMap[s][ck] = [];
            salesMap[s][ck].push(r);
        });

        var html = '';
        Object.keys(salesMap).forEach(function(salesman) {
            html += '<div class="pu-sales-heading">' + esc(salesman) + '</div>';
            var custMap = salesMap[salesman];
            Object.keys(custMap).forEach(function(ck) {
                var parts = ck.split('||');
                html += '<div class="pu-cust-heading">' + esc(parts[0]) + ' | ' + esc(parts[1]) + ' (IDR)</div>';
                html += renderAgingRincianTable(custMap[ck], 'Ref. No.');
            });
        });
        return html;
    }

    function terbilang(angka) {
        var n = Math.floor(Math.abs(parseFloat(angka || 0)));
        if (n === 0) return 'Nol';
        var satuan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        function konversi(x) {
            if (x < 12) return satuan[x];
            if (x < 20) return konversi(x - 10) + ' Belas';
            if (x < 100) return konversi(Math.floor(x / 10)) + ' Puluh ' + konversi(x % 10);
            if (x < 200) return 'Seratus ' + konversi(x - 100);
            if (x < 1000) return konversi(Math.floor(x / 100)) + ' Ratus ' + konversi(x % 100);
            if (x < 2000) return 'Seribu ' + konversi(x - 1000);
            if (x < 1000000) return konversi(Math.floor(x / 1000)) + ' Ribu ' + konversi(x % 1000);
            if (x < 1000000000) return konversi(Math.floor(x / 1000000)) + ' Juta ' + konversi(x % 1000000);
            return konversi(Math.floor(x / 1000000000)) + ' Miliar ' + konversi(x % 1000000000);
        }
        return konversi(n).replace(/\s+/g, ' ').trim() + ' Rupiah';
    }

    function renderSuratTagihan(rows, endDate) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada piutang jatuh tempo/belum lunas untuk dicetak surat tagihan.</div>';
        }

        var custMap = {};
        rows.forEach(function(r) {
            var ck = (r.nama_pelanggan || '-') + '||' + (r.kd_customer || '');
            if (!custMap[ck]) custMap[ck] = [];
            custMap[ck].push(r);
        });

        var asOfDMY = fmtDateDMY(endDate);
        var html = '';
        Object.keys(custMap).forEach(function(ck) {
            var parts = ck.split('||');
            var items = custMap[ck];
            var total = 0;

            html += '<div style="padding: 16px 8px 28px; border-bottom: 2px dashed #cbd5e1; margin-bottom: 24px; page-break-after: always;">';
            html += '<div style="text-align:right; font-size:12px; margin-bottom:14px;">Jember, ' + asOfDMY + '</div>';
            html += '<div style="font-size:13.5px; font-weight:700; margin-bottom:12px;">Kepada Yth : ' + esc(parts[0]) + '</div>';
            html += '<p style="font-size:12px; margin-bottom:8px;">Dengan hormat,</p>';
            html += '<p style="font-size:12px; margin-bottom:12px;">Berikut ini kami sampaikan daftar piutang yang belum Anda selesaikan sampai dengan tanggal ' + asOfDMY + ' :</p>';

            html += '<table class="pu-table"><thead><tr>';
            html += '<th style="width:100px;">Tanggal</th>';
            html += '<th style="width:105px;">Jatuh Tempo</th>';
            html += '<th style="width:140px;">No. Faktur</th>';
            html += '<th style="width:90px;">Mata Uang</th>';
            html += '<th class="num" style="width:150px;">Nilai</th>';
            html += '</tr></thead><tbody>';

            items.forEach(function(it) {
                var val = parseFloat(it.sisa || 0);
                total += val;
                html += '<tr>';
                html += '<td>' + fmtDateDMY(it.tanggal_faktur) + '</td>';
                html += '<td>' + fmtDateDMY(it.jatuh_tempo) + '</td>';
                html += '<td>' + esc(it.no_faktur) + '</td>';
                html += '<td>IDR</td>';
                html += '<td class="num">' + fmtNum(val) + '</td>';
                html += '</tr>';
            });

            html += '</tbody><tfoot><tr>';
            html += '<td colspan="3" style="text-align:right;">Total :</td>';
            html += '<td>IDR</td>';
            html += '<td class="num">' + fmtNum(total) + '</td>';
            html += '</tr></tfoot></table>';

            html += '<div style="background:#f3f4f6; padding:6px 10px; font-size:11.5px; font-style:italic; margin-top:4px; margin-bottom:16px;">';
            html += '<strong>Terbilang :</strong> ' + esc(terbilang(total));
            html += '</div>';

            html += '<p style="font-size:12px; margin-bottom:6px;">Jika terdapat kesalahan pencatatan dari data yang kami berikan, mohon untuk segera dikonfirmasikan.</p>';
            html += '<p style="font-size:12px; margin-bottom:24px;">Atas perhatian dan kerjasamanya, kami mengucapkan terima kasih.</p>';
            html += '<div style="font-size:12px; font-weight:600; margin-bottom:36px;">KARISMA <?= date('Y') ?>,</div>';
            html += '<div style="border-top:1.5px dashed #111827; padding-top:12px; font-size:11.5px;">We do not agree about our account payable balance, because:</div>';
            html += '</div>';
        });

        return html;
    }

    function loadReport() {
        var params = {
            jenis: reportMeta.slug,
            start_date: $('#filterStartDate').val() || '',
            end_date: $('#filterEndDate').val() || '',
            search: $('#filterSearch').val() || ''
        };

        $('#puContainer').html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat laporan...</div>');

        $.getJSON(endpointUrl, params)
            .done(function(res) {
                if (!res || !res.success) {
                    $('#puContainer').html('<div class="alert alert-danger">Gagal memuat data laporan.</div>');
                    return;
                }
                $('#puPeriodLabel').text(fmtDateLong(res.start_date) + ' - ' + fmtDateLong(res.end_date));

                var html = '';
                if (reportMeta.slug === 'kartu-piutang-salesman') {
                    html = renderKartuPiutangSalesman(res.rows);
                } else if (reportMeta.slug === 'kartu-piutang-mata-uang-dasar') {
                    html = renderKartuPiutangMataUangDasar(res.rows);
                } else if (reportMeta.slug === 'kartu-piutang-salesman-rangkuman') {
                    html = renderKartuSalesmanRangkuman(res.rows);
                } else if (reportMeta.slug === 'mutasi-piutang') {
                    html = renderMutasiPiutang(res.rows);
                } else if (reportMeta.slug === 'pembayaran-pelanggan') {
                    html = renderPembayaranPelanggan(res.rows);
                } else if (reportMeta.slug === 'piutang-per-pelanggan-rincian') {
                    html = renderAgingRincianByCustomer(res.rows, false);
                } else if (reportMeta.slug === 'piutang-per-mata-uang-rincian') {
                    html = renderAgingRincianByCustomer(res.rows, true);
                } else if (reportMeta.slug === 'piutang-per-salesman-rangkuman') {
                    html = renderAgingPerSalesmanRangkuman(res.rows);
                } else if (reportMeta.slug === 'piutang-per-salesman-rincian') {
                    html = renderAgingPerSalesmanRincian(res.rows);
                } else if (reportMeta.slug === 'surat-tagihan-pelanggan') {
                    html = renderSuratTagihan(res.rows, res.end_date);
                } else {
                    html = renderPiutangPerMataUang(res.rows);
                }

                $('#puContainer').html(html);
                $('#puStatusInfo').text('Menampilkan ' + (res.rows ? res.rows.length : 0) + ' entri.');
            })
            .fail(function() {
                $('#puContainer').html('<div class="alert alert-danger">Terjadi kesalahan saat mengambil data laporan.</div>');
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
