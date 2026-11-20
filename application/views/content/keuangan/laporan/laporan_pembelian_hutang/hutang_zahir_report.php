<?php
$meta  = $report_meta ?? ['slug' => 'rincian-mata-uang-dasar', 'title' => 'Rincian Hutang - Mata Uang Dasar'];
$slug  = $meta['slug'];
$title = $meta['title'];
?>

<style>
.hu-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.hu-paper {
    background: #fff;
    border: 1px solid #d9dee3;
    border-radius: 6px;
    padding: 24px 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    color: #1f2937;
    font-family: Arial, Helvetica, sans-serif;
}

.hu-meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #374151;
    margin-bottom: 4px;
}

.hu-header-center {
    text-align: center;
    margin-bottom: 18px;
}

.hu-company {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}

.hu-title {
    font-size: 20px;
    font-weight: 700;
    color: #0284c7;
    margin-bottom: 2px;
}

.hu-period {
    font-size: 12px;
    font-weight: 600;
    color: #b91c1c;
}

.hu-sup-heading {
    text-align: center;
    font-size: 13px;
    font-weight: 700;
    color: #0284c7;
    margin: 14px 0 6px;
}

.hu-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
    margin-bottom: 10px;
}

.hu-table thead th {
    background: #e5e7eb;
    border-top: 1.5px solid #4b5563;
    border-bottom: 1.5px solid #4b5563;
    padding: 6px 8px;
    font-weight: 700;
    color: #111827;
    text-align: left;
    white-space: nowrap;
}

.hu-table thead th.num,
.hu-table tbody td.num,
.hu-table tfoot td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.hu-table tbody td {
    padding: 4px 8px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}

.hu-table tfoot td,
.hu-subtotal td {
    font-weight: 700;
    background: #f3f4f6;
    border-top: 1px solid #6b7280;
    border-bottom: 1.5px solid #374151;
    padding: 5px 8px;
}

.hu-inv-total td {
    font-weight: 600;
    border-top: 1px dashed #cbd5e1;
    border-bottom: 1px solid #e2e8f0;
    background: #fafafa;
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

    .hu-paper {
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
                <div class="hu-toolbar no-print">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" id="btnOpenFilter">
                            <i class="fas fa-filter mr-1"></i> Filter Data
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak Laporan
                        </button>
                    </div>
                    <div class="text-muted small" id="huStatusInfo">
                        Pilih filter untuk menampilkan laporan.
                    </div>
                </div>

                <div class="hu-paper">
                    <div class="hu-meta">
                        <span><?= date('g:i A, F j, Y') ?></span>
                        <span><?= htmlspecialchars($title) ?></span>
                    </div>
                    <div class="hu-header-center">
                        <div class="hu-company">KARISMA <?= date('Y') ?></div>
                        <div class="hu-title"><?= htmlspecialchars($title) ?></div>
                        <div class="hu-period" id="huPeriodLabel">-</div>
                    </div>

                    <div id="huContainer">
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
                        <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Nama Pemasok, Kode Pemasok, No. Faktur...">
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
        if (SLUG === 'rincian-mata-uang-dasar' || SLUG === 'rangkuman-hutang-mata-uang-dasar') {
            return f(e);
        }
        return 'Periode : ' + f(s) + ' s/d ' + f(e);
    }

    // 1. Rincian Hutang - Mata Uang Dasar
    function renderRincianHutang(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada saldo hutang usaha.</div>';
        }
        let html = '';
        let g0 = 0, g30 = 0, g60 = 0, g90 = 0;

        rows.forEach(function (sup) {
            g0 += parseFloat(sup.b_0_30) || 0;
            g30 += parseFloat(sup.b_30_60) || 0;
            g60 += parseFloat(sup.b_60_90) || 0;
            g90 += parseFloat(sup.b_gt_90) || 0;

            html += '<div class="hu-sup-heading">' + esc(sup.nama_suplier) + ' | ' + esc(sup.kd_suplier) + ' (IDR)</div>';
            html += '<table class="hu-table"><thead><tr>'
                + '<th>Tanggal</th>'
                + '<th>Jatuh Tempo</th>'
                + '<th>Tp</th>'
                + '<th>No. Referensi</th>'
                + '<th>Mata Uang</th>'
                + '<th class="num">0 - 30 Hari</th>'
                + '<th class="num">30 - 60 Hari</th>'
                + '<th class="num">60 - 90 Hari</th>'
                + '<th class="num">&gt; 90 Hari</th>'
                + '</tr></thead><tbody>';

            (sup.invoices || []).forEach(function (inv) {
                html += '<tr>'
                    + '<td>' + esc(fmtDate(inv.tanggal)) + '</td>'
                    + '<td>' + esc(fmtDate(inv.jatuh_tempo)) + '</td>'
                    + '<td>' + esc(inv.tp) + '</td>'
                    + '<td>' + esc(inv.no_referensi) + '</td>'
                    + '<td>IDR</td>'
                    + '<td class="num">' + (inv.b_0_30 ? fmtNum(inv.b_0_30) : '') + '</td>'
                    + '<td class="num">' + (inv.b_30_60 ? fmtNum(inv.b_30_60) : '') + '</td>'
                    + '<td class="num">' + (inv.b_60_90 ? fmtNum(inv.b_60_90) : '') + '</td>'
                    + '<td class="num">' + (inv.b_gt_90 ? fmtNum(inv.b_gt_90) : '') + '</td>'
                    + '</tr>';
            });

            html += '</tbody><tfoot><tr>'
                + '<td colspan="4">Saldo ' + esc(sup.nama_suplier) + ' :</td>'
                + '<td>IDR</td>'
                + '<td class="num">' + (sup.b_0_30 ? fmtNum(sup.b_0_30) : '') + '</td>'
                + '<td class="num">' + (sup.b_30_60 ? fmtNum(sup.b_30_60) : '') + '</td>'
                + '<td class="num">' + (sup.b_60_90 ? fmtNum(sup.b_60_90) : '') + '</td>'
                + '<td class="num">' + (sup.b_gt_90 ? fmtNum(sup.b_gt_90) : '') + '</td>'
                + '</tr></tfoot></table>';
        });

        html += '<table class="hu-table"><tfoot><tr>'
            + '<td colspan="4">Total Seluruh Pemasok :</td>'
            + '<td>IDR</td>'
            + '<td class="num">' + fmtNum(g0) + '</td>'
            + '<td class="num">' + fmtNum(g30) + '</td>'
            + '<td class="num">' + fmtNum(g60) + '</td>'
            + '<td class="num">' + fmtNum(g90) + '</td>'
            + '</tr></tfoot></table>';
        return html;
    }

    // 2. Kartu Hutang - Mata Uang Dasar
    function renderKartuHutang(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada transaksi kartu hutang pada periode ini.</div>';
        }
        let html = '';
        let gDeb = 0, gKre = 0, gSal = 0;

        rows.forEach(function (sup) {
            gDeb += parseFloat(sup.total_debit) || 0;
            gKre += parseFloat(sup.total_kredit) || 0;
            gSal += parseFloat(sup.total_saldo) || 0;

            html += '<div class="hu-sup-heading">' + esc(sup.nama_suplier) + ' | ' + esc(sup.kd_suplier) + ' (IDR)</div>';
            html += '<table class="hu-table"><thead><tr>'
                + '<th>Tanggal</th>'
                + '<th>Tp</th>'
                + '<th>No. Referensi</th>'
                + '<th>Jatuh Tempo</th>'
                + '<th>Mata Uang</th>'
                + '<th class="num">Debit</th>'
                + '<th class="num">Credit</th>'
                + '<th class="num">Saldo</th>'
                + '</tr></thead><tbody>';

            (sup.invoices || []).forEach(function (inv) {
                (inv.lines || []).forEach(function (ln) {
                    html += '<tr>'
                        + '<td>' + esc(fmtDate(ln.tanggal)) + '</td>'
                        + '<td>' + esc(ln.tp) + '</td>'
                        + '<td>' + esc(ln.no_referensi) + '</td>'
                        + '<td>' + esc(fmtDate(ln.jatuh_tempo)) + '</td>'
                        + '<td>IDR</td>'
                        + '<td class="num">' + (ln.debit > 0 ? fmtNum(ln.debit) : '') + '</td>'
                        + '<td class="num">' + (ln.kredit > 0 ? fmtNum(ln.kredit) : '') + '</td>'
                        + '<td class="num"></td>'
                        + '</tr>';
                });
                html += '<tr class="hu-inv-total">'
                    + '<td colspan="4" class="text-right">Saldo Faktur ' + esc(inv.no_faktur) + ' :</td>'
                    + '<td>IDR</td>'
                    + '<td class="num">' + fmtNum(inv.total_debit) + '</td>'
                    + '<td class="num">' + fmtNum(inv.total_kredit) + '</td>'
                    + '<td class="num">' + fmtNum(inv.saldo) + '</td>'
                    + '</tr>';
            });

            html += '</tbody><tfoot><tr>'
                + '<td colspan="4">Saldo Pemasok ' + esc(sup.nama_suplier) + ' :</td>'
                + '<td>IDR</td>'
                + '<td class="num">' + fmtNum(sup.total_debit) + '</td>'
                + '<td class="num">' + fmtNum(sup.total_kredit) + '</td>'
                + '<td class="num">' + fmtNum(sup.total_saldo) + '</td>'
                + '</tr></tfoot></table>';
        });

        html += '<table class="hu-table"><tfoot><tr>'
            + '<td colspan="4">Total Seluruh Pemasok :</td>'
            + '<td>IDR</td>'
            + '<td class="num">' + fmtNum(gDeb) + '</td>'
            + '<td class="num">' + fmtNum(gKre) + '</td>'
            + '<td class="num">' + fmtNum(gSal) + '</td>'
            + '</tr></tfoot></table>';
        return html;
    }

    // 3. Mutasi Hutang - Mata Uang Dasar
    function renderMutasiHutang(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data mutasi hutang pada periode ini.</div>';
        }
        let html = '<table class="hu-table"><thead><tr>'
            + '<th>Nama Pemasok</th>'
            + '<th>Mata Uang</th>'
            + '<th class="num">Saldo Awal</th>'
            + '<th class="num">Debit</th>'
            + '<th class="num">Credit</th>'
            + '<th class="num">Saldo Akhir</th>'
            + '</tr></thead><tbody>';

        let sAwal = 0, sDeb = 0, sKre = 0, sAkhir = 0;
        rows.forEach(function (r) {
            const a = parseFloat(r.saldo_awal) || 0;
            const d = parseFloat(r.debit) || 0;
            const k = parseFloat(r.kredit) || 0;
            const z = parseFloat(r.saldo_akhir) || 0;
            sAwal += a; sDeb += d; sKre += k; sAkhir += z;
            html += '<tr>'
                + '<td>' + esc(r.nama_suplier) + ' (' + esc(r.kd_suplier) + ')</td>'
                + '<td>IDR</td>'
                + '<td class="num">' + fmtNum(a) + '</td>'
                + '<td class="num">' + (d > 0 ? fmtNum(d) : '') + '</td>'
                + '<td class="num">' + (k > 0 ? fmtNum(k) : '') + '</td>'
                + '<td class="num">' + fmtNum(z) + '</td>'
                + '</tr>';
        });

        html += '</tbody><tfoot><tr>'
            + '<td>Total :</td>'
            + '<td>IDR</td>'
            + '<td class="num">' + fmtNum(sAwal) + '</td>'
            + '<td class="num">' + fmtNum(sDeb) + '</td>'
            + '<td class="num">' + fmtNum(sKre) + '</td>'
            + '<td class="num">' + fmtNum(sAkhir) + '</td>'
            + '</tr></tfoot></table>';
        return html;
    }

    // 4. Pembayaran Hutang - Mata Uang Dasar
    function renderPembayaranHutang(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data pembayaran hutang pada periode ini.</div>';
        }
        let html = '<table class="hu-table"><thead><tr>'
            + '<th>Tanggal</th>'
            + '<th>No. Referensi</th>'
            + '<th>Nama Pemasok / No. Faktur</th>'
            + '<th>Tanggal Faktur</th>'
            + '<th>Mata Uang</th>'
            + '<th class="num">Diskon</th>'
            + '<th class="num">Pembayaran</th>'
            + '</tr></thead><tbody>';

        rows.forEach(function (hdr) {
            html += '<tr>'
                + '<td>' + esc(fmtDate(hdr.tanggal)) + '</td>'
                + '<td>' + esc(hdr.no_referensi) + '</td>'
                + '<td colspan="5"><b>' + esc(hdr.nama_pemasok) + '</b></td>'
                + '</tr>';

            (hdr.items || []).forEach(function (it) {
                html += '<tr>'
                    + '<td colspan="2"></td>'
                    + '<td>' + esc(it.no_faktur) + '</td>'
                    + '<td>' + esc(fmtDate(it.tanggal_faktur)) + '</td>'
                    + '<td>IDR</td>'
                    + '<td class="num">' + (it.diskon > 0 ? fmtNum(it.diskon) : '') + '</td>'
                    + '<td class="num">' + fmtNum(it.pembayaran) + '</td>'
                    + '</tr>';
            });

            html += '<tr class="hu-inv-total">'
                + '<td colspan="4" class="text-right">Total Pembayaran :</td>'
                + '<td>IDR</td>'
                + '<td></td>'
                + '<td class="num">' + fmtNum(hdr.total_pembayaran) + '</td>'
                + '</tr>';
        });

        html += '</tbody></table>';
        return html;
    }

    // 5. Rangkuman Hutang - Mata Uang Dasar
    function renderRangkumanHutang(rows) {
        if (!rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada saldo hutang usaha.</div>';
        }
        let html = '<table class="hu-table"><thead><tr>'
            + '<th>Nama Pemasok</th>'
            + '<th>Mata Uang</th>'
            + '<th class="num">0 - 30 Hari</th>'
            + '<th class="num">30 - 60 Hari</th>'
            + '<th class="num">60 - 90 Hari</th>'
            + '<th class="num">&gt; 90 Hari</th>'
            + '</tr></thead><tbody>';

        let g0 = 0, g30 = 0, g60 = 0, g90 = 0;
        rows.forEach(function (sup) {
            g0 += parseFloat(sup.b_0_30) || 0;
            g30 += parseFloat(sup.b_30_60) || 0;
            g60 += parseFloat(sup.b_60_90) || 0;
            g90 += parseFloat(sup.b_gt_90) || 0;
            html += '<tr>'
                + '<td>' + esc(sup.nama_suplier) + ' | ' + esc(sup.kd_suplier) + '</td>'
                + '<td>IDR</td>'
                + '<td class="num">' + (sup.b_0_30 ? fmtNum(sup.b_0_30) : '') + '</td>'
                + '<td class="num">' + (sup.b_30_60 ? fmtNum(sup.b_30_60) : '') + '</td>'
                + '<td class="num">' + (sup.b_60_90 ? fmtNum(sup.b_60_90) : '') + '</td>'
                + '<td class="num">' + (sup.b_gt_90 ? fmtNum(sup.b_gt_90) : '') + '</td>'
                + '</tr>';
        });

        html += '</tbody><tfoot><tr>'
            + '<td>Total Seluruh Pemasok :</td>'
            + '<td>IDR</td>'
            + '<td class="num">' + fmtNum(g0) + '</td>'
            + '<td class="num">' + fmtNum(g30) + '</td>'
            + '<td class="num">' + fmtNum(g60) + '</td>'
            + '<td class="num">' + fmtNum(g90) + '</td>'
            + '</tr></tfoot></table>';
        return html;
    }

    function loadReport() {
        const startDate = $('#filterStartDate').val();
        const endDate = $('#filterEndDate').val();
        const search = $('#filterSearch').val().trim();

        $('#huPeriodLabel').text(fmtPeriodText(startDate, endDate));
        $('#huStatusInfo').text('Memuat data...');
        $('#huContainer').html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat laporan...</div>');

        $.getJSON(ENDPOINT, {
            jenis: SLUG,
            start_date: startDate,
            end_date: endDate,
            search: search
        }).done(function (res) {
            const rows = (res && res.rows) ? res.rows : [];
            $('#huStatusInfo').text('Menampilkan ' + rows.length + ' data.');

            if (SLUG === 'rincian-mata-uang-dasar') {
                $('#huContainer').html(renderRincianHutang(rows));
            } else if (SLUG === 'kartu-hutang-mata-uang-dasar') {
                $('#huContainer').html(renderKartuHutang(rows));
            } else if (SLUG === 'mutasi-hutang-mata-uang-dasar') {
                $('#huContainer').html(renderMutasiHutang(rows));
            } else if (SLUG === 'pembayaran-hutang-mata-uang-dasar') {
                $('#huContainer').html(renderPembayaranHutang(rows));
            } else if (SLUG === 'rangkuman-hutang-mata-uang-dasar') {
                $('#huContainer').html(renderRangkumanHutang(rows));
            }
        }).fail(function () {
            $('#huStatusInfo').text('Gagal memuat data.');
            $('#huContainer').html('<div class="alert alert-danger">Gagal memuat data laporan.</div>');
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
