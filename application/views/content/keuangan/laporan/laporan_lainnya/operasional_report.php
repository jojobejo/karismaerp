<?php
/**
 * Halaman Laporan Transaksi Operasional pada menu Laporan Lainnya.
 * Mengikuti desain tampilan TAGPD (toolbar filter, 5 kartu ringkasan, container sheet, dan tabel sticky bergaya spreadsheet).
 */
?>
<style>
    .op-page { font-family: 'Segoe UI', Arial, sans-serif; }
    .op-page .content-header { padding: 8px .5rem 0; }

    .op-toolbar {
        background: #fff;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
        margin-bottom: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        align-items: flex-end;
    }
    .op-toolbar .form-group { margin-bottom: 0; min-width: 165px; }
    .op-toolbar label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 4px;
        display: block;
    }

    .op-summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }
    @media (max-width: 1199.98px) {
        .op-summary-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .op-summary-grid { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    }

    .op-stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #2d6b6b;
        border-radius: 8px;
        padding: 12px 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .04);
    }
    .op-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .op-stat-val {
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
    }
    .op-stat-sub {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
    }

    .op-container {
        background: #fff;
        padding: 18px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .06);
    }

    .op-sheet-meta {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 2px solid #2d6b6b;
    }
    .op-sheet-title {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }
    .op-sheet-sub {
        font-size: 13px;
        color: #475569;
        margin: 2px 0 0;
    }
    .op-period-badge {
        background: #f0fdfa;
        border: 1px solid #99f6e4;
        color: #115e59;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 700;
    }

    .op-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 14px;
    }
    .op-tabs .nav-link {
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 6px 12px;
    }
    .op-tabs .nav-link:hover {
        background: #e2eef6;
        color: #0f172a;
    }
    .op-tabs .nav-link.active {
        background: #2d6b6b;
        border-color: #2d6b6b;
        color: #fff;
    }

    .op-table-scroll {
        width: 100%;
        max-height: 64vh;
        overflow: auto;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        position: relative;
    }
    .op-table {
        width: max-content;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
        color: #0f172a;
    }
    .op-table th,
    .op-table td {
        border-right: 1px solid #cbd5e1;
        border-bottom: 1px solid #e2e8f0;
        padding: 7px 10px;
        vertical-align: middle;
        white-space: nowrap;
        background: #fff;
    }
    .op-table thead th {
        background: #e2eef6;
        color: #0f172a;
        font-weight: 700;
        border-bottom: 1px solid #94a3b8;
        position: sticky;
        top: 0;
        z-index: 3;
    }
    .op-table tbody tr:hover td {
        background: #f8fafc;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse op-page">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper bg-light">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-7">
                        <h1 class="m-0 text-dark font-weight-bold" style="font-size: 1.45rem;">
                            <i class="fas fa-chart-line mr-2" style="color: #2d6b6b;"></i>Transaksi Operasional
                        </h1>
                    </div>
                    <div class="col-sm-5 text-right">
                        <a href="<?= base_url('laporan/lainnya') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Laporan Lainnya
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- TOOLBAR FILTER -->
                <form id="formFilterOperasional" class="op-toolbar">
                    <div class="form-group">
                        <label for="opDateFrom">Tanggal Mulai</label>
                        <input type="date" id="opDateFrom" class="form-control form-control-sm" value="<?= htmlspecialchars($date_from) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="opDateTo">Tanggal Sampai</label>
                        <input type="date" id="opDateTo" class="form-control form-control-sm" value="<?= htmlspecialchars($date_to) ?>" required>
                    </div>
                    <div class="form-group d-flex align-items-end" style="gap: 8px; min-width: auto;">
                        <button type="submit" class="btn btn-sm font-weight-bold text-white px-3" id="btnTampilkanOp" style="background: #2d6b6b;">
                            <i class="fas fa-filter mr-1"></i> Tampilkan
                        </button>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold px-3" id="btnExportOperasional">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel (.xlsx)
                        </button>
                    </div>
                </form>

                <!-- 5 KARTU RINGKASAN -->
                <div class="op-summary-grid">
                    <div class="op-stat-card">
                        <div class="op-stat-label">Penjualan Bersih (DPP)</div>
                        <div class="op-stat-val" id="stat-penjualan-bersih">Rp 0,00</div>
                        <div class="op-stat-sub" id="stat-retur-sub">Retur: Rp 0,00</div>
                    </div>
                    <div class="op-stat-card">
                        <div class="op-stat-label">HPP Bersih (Moving Avg)</div>
                        <div class="op-stat-val" id="stat-hpp-bersih">Rp 0,00</div>
                        <div class="op-stat-sub" id="stat-po-lpb-sub">0 PO / 0 unit LPB</div>
                    </div>
                    <div class="op-stat-card">
                        <div class="op-stat-label">Laba Kotor</div>
                        <div class="op-stat-val text-success" id="stat-laba-kotor">Rp 0,00</div>
                        <div class="op-stat-sub" id="stat-margin-sub">Margin: 0,00%</div>
                    </div>
                    <div class="op-stat-card">
                        <div class="op-stat-label">Pembayaran Customer</div>
                        <div class="op-stat-val" id="stat-bayar-cust">Rp 0,00</div>
                        <div class="op-stat-sub" id="stat-bg-pending-sub">BG Pending: Rp 0,00</div>
                    </div>
                    <div class="op-stat-card">
                        <div class="op-stat-label">Pembayaran Supplier</div>
                        <div class="op-stat-val" id="stat-bayar-supp">Rp 0,00</div>
                        <div class="op-stat-sub" id="stat-so-open-sub">SO Open: Rp 0,00</div>
                    </div>
                </div>

                <!-- CONTAINER UTAMA BERGAYA SHEET TAGPD -->
                <div class="op-container">
                    <div class="op-sheet-meta">
                        <div>
                            <h2 class="op-sheet-title">LAPORAN TRANSAKSI OPERASIONAL</h2>
                            <p class="op-sheet-sub">Rincian PO, LPB Barang Datang, Faktur Penjualan, Retur, Pembayaran, Laba Rugi &amp; Mutasi Akun</p>
                        </div>
                        <div class="op-period-badge" id="badgePeriodeOp">
                            Periode: <?= date('d/m/Y', strtotime($date_from)) ?> s/d <?= date('d/m/Y', strtotime($date_to)) ?>
                        </div>
                    </div>

                    <ul class="nav nav-pills op-tabs" id="opTabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab-ringkasan">Ringkasan</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-po">PO Detail</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-lpb">LPB Barang Datang</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-faktur">Faktur Penjualan</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-rb">Retur Pembelian</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-rp">Retur Penjualan</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-pc">Pembayaran Customer</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-ps">Pembayaran Supplier</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-lr">Laba Rugi</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-akun">Neraca Saldo &amp; Laba Kotor</a></li>
                    </ul>

                    <div class="tab-content" id="opTabContent">
                        <div class="tab-pane fade show active" id="tab-ringkasan"></div>
                        <div class="tab-pane fade" id="tab-po"></div>
                        <div class="tab-pane fade" id="tab-lpb"></div>
                        <div class="tab-pane fade" id="tab-faktur"></div>
                        <div class="tab-pane fade" id="tab-rb"></div>
                        <div class="tab-pane fade" id="tab-rp"></div>
                        <div class="tab-pane fade" id="tab-pc"></div>
                        <div class="tab-pane fade" id="tab-ps"></div>
                        <div class="tab-pane fade" id="tab-lr"></div>
                        <div class="tab-pane fade" id="tab-akun"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
const INITIAL_OP_REPORT = <?= json_encode($initial_report, JSON_UNESCAPED_UNICODE) ?>;

function fmtIdr(val) {
    return 'Rp ' + Number(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtQty(val) {
    return Number(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}
function fmtAccNum(val) {
    if (val === null || val === undefined || val === '') return '';
    const num = Number(val);
    const absStr = Math.abs(num).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return num < 0 ? '(' + absStr + ')' : absStr;
}
function fmtDateId(iso) {
    if (!iso || iso.length < 10) return iso || '-';
    const p = iso.substring(0, 10).split('-');
    return p[2] + '/' + p[1] + '/' + p[0];
}
function escHtml(str) {
    return String(str == null ? '' : str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function buildTable(headers, rows, emptyMsg) {
    if (!rows.length) {
        return `<div class="alert alert-light border text-center text-muted mb-0">${emptyMsg || 'Tidak ada data pada periode ini.'}</div>`;
    }
    const ths = headers.map(h => `<th class="${h.cls || ''}">${h.label}</th>`).join('');
    const trs = rows.map(cols => '<tr>' + cols.map((c, idx) => `<td class="${headers[idx].cls || ''}">${c}</td>`).join('') + '</tr>').join('');
    return `<div class="op-table-scroll"><table class="op-table"><thead><tr>${ths}</tr></thead><tbody>${trs}</tbody></table></div>`;
}

function renderSummaryCards(data) {
    const lr = (data.laba_rugi && data.laba_rugi.summary) ? data.laba_rugi.summary : {};
    const poCount = new Set((data.po_detail || []).map(r => r.no_po)).size;
    const lpbQty = (data.lpb_detail || []).reduce((acc, r) => acc + Number(r.qty_datang || 0), 0);

    let bayarCust = 0, bgPending = 0;
    (data.pembayaran_customer || []).forEach(r => {
        const v = Number(r.nilai || 0);
        bayarCust += v;
        if (String(r.status_bayar || '').toUpperCase() === 'PENDING') bgPending += v;
    });
    const bayarSupp = (data.pembayaran_supplier || []).reduce((acc, r) => acc + Number(r.nilai || 0), 0);

    const soOpenRow = (data.ringkasan || []).find(x => String(x.indikator || '').toLowerCase().indexOf('so open') >= 0);

    $('#stat-penjualan-bersih').text(fmtIdr(lr.penjualan_bersih || 0));
    $('#stat-retur-sub').text('Retur: ' + fmtIdr(lr.retur_dpp || 0));
    $('#stat-hpp-bersih').text(fmtIdr(lr.hpp_bersih || 0));
    $('#stat-po-lpb-sub').text(poCount + ' PO / ' + fmtQty(lpbQty) + ' unit LPB');
    $('#stat-laba-kotor').text(fmtIdr(lr.laba_kotor || 0));
    $('#stat-margin-sub').text('Margin: ' + Number(lr.margin || 0).toFixed(2) + '%');
    $('#stat-bayar-cust').text(fmtIdr(bayarCust));
    $('#stat-bg-pending-sub').text('BG Pending: ' + fmtIdr(bgPending));
    $('#stat-bayar-supp').text(fmtIdr(bayarSupp));
    $('#stat-so-open-sub').text('SO Open: ' + (soOpenRow ? soOpenRow.nilai : 'Rp 0,00'));
    $('#badgePeriodeOp').text('Periode: ' + fmtDateId(data.date_from) + ' s/d ' + fmtDateId(data.date_to));
}

function renderOperasionalReport(data) {
    renderSummaryCards(data);

    $('#tab-ringkasan').html(buildTable(
        [{ label: 'Indikator' }, { label: 'Nilai', cls: 'font-weight-bold text-right' }],
        (data.ringkasan || []).map(r => [escHtml(r.indikator), escHtml(r.nilai)])
    ));

    $('#tab-po').html(buildTable(
        [
            { label: 'No. PO' }, { label: 'Tanggal PO' }, { label: 'Supplier' },
            { label: 'Kode Barang' }, { label: 'Nama Barang' },
            { label: 'Qty', cls: 'text-right' }, { label: 'Satuan' },
            { label: 'Harga PO/Unit', cls: 'text-right' }, { label: 'Skema PPN' },
            { label: 'Nilai Barang', cls: 'text-right font-weight-bold' }
        ],
        (data.po_detail || []).map(r => [
            escHtml(r.no_po), escHtml(r.tgl_transaksi), escHtml(r.nama_suplier),
            escHtml(r.kd_barang), escHtml(r.nama_barang),
            fmtQty(r.qty), escHtml(r.satuan),
            fmtIdr(r.harga_po), escHtml(r.skema_ppn), fmtIdr(r.nilai_barang)
        ])
    ));

    $('#tab-lpb').html(buildTable(
        [
            { label: 'Tgl Realisasi' }, { label: 'No. PO' }, { label: 'No. LPB' },
            { label: 'Supplier' }, { label: 'Kode' }, { label: 'Detail Barang' },
            { label: 'Kat' }, { label: 'Qty PO', cls: 'text-right' },
            { label: 'Qty Datang', cls: 'text-right' }, { label: 'Qty Sisa', cls: 'text-right' },
            { label: 'Satuan' }, { label: 'Harga Inc PPN', cls: 'text-right' },
            { label: 'Harga DPP', cls: 'text-right' }, { label: 'Nilai Datang (DPP)', cls: 'text-right font-weight-bold' },
            { label: 'Nilai Datang Inc PPN', cls: 'text-right font-weight-bold' },
            { label: 'Status Realisasi' }
        ],
        (data.lpb_detail || []).map(r => [
            escHtml(r.tanggal_realisasi), escHtml(r.no_po), escHtml(r.nomor_lpb),
            escHtml(r.nama_suplier), escHtml(r.kd_barang), escHtml(r.nama_barang),
            escHtml(r.kategori_pajak), fmtQty(r.qty_po), fmtQty(r.qty_datang),
            fmtQty(r.qty_sisa), escHtml(r.satuan), fmtIdr(r.harga_inc_ppn),
            fmtIdr(r.harga_dpp), fmtIdr(r.nilai_datang), fmtIdr(r.nilai_datang_inc), escHtml(r.status_realisasi)
        ])
    ));

    $('#tab-faktur').html(buildTable(
        [
            { label: 'Tgl Faktur' }, { label: 'No. Faktur' }, { label: 'Customer' },
            { label: 'Cara Bayar / Tempo' }, { label: 'Kode' }, { label: 'Barang' },
            { label: 'Qty', cls: 'text-right' }, { label: 'Satuan' },
            { label: 'Nilai Faktur', cls: 'text-right' }, { label: 'DPP', cls: 'text-right' },
            { label: 'HPP Satuan', cls: 'text-right' },
            { label: 'HPP Total', cls: 'text-right' }, { label: 'Laba Kotor', cls: 'text-right font-weight-bold' }
        ],
        (data.faktur_penjualan || []).map(r => [
            escHtml(r.tanggal_faktur), escHtml(r.no_faktur), escHtml(r.customer_name),
            escHtml(r.cara_bayar_tempo), escHtml(r.kd_barang), escHtml(r.nama_barang),
            fmtQty(r.qty), escHtml(r.satuan), fmtIdr(r.nilai_faktur), fmtIdr(r.dpp),
            fmtIdr(r.hpp_unit), fmtIdr(r.hpp_total), fmtIdr(r.laba_kotor)
        ])
    ));

    $('#tab-rb').html(buildTable(
        [
            { label: 'Tgl Retur' }, { label: 'No. Jurnal' }, { label: 'No. Retur' },
            { label: 'Supplier' }, { label: 'Referensi PO / LPB' }, { label: 'Barang & Lot' },
            { label: 'Qty', cls: 'text-right' }, { label: 'Satuan' },
            { label: 'DPP', cls: 'text-right' }, { label: 'PPN', cls: 'text-right' },
            { label: 'Total Retur', cls: 'text-right font-weight-bold' }
        ],
        (data.retur_pembelian || []).map(r => [
            escHtml(r.tanggal_retur), escHtml(r.nomor_jurnal), escHtml(r.no_retur_pembelian),
            escHtml(r.nama_suplier), escHtml(r.referensi), escHtml(r.barang_lot),
            fmtQty(r.qty_retur), escHtml(r.satuan), fmtIdr(r.dpp), fmtIdr(r.ppn), fmtIdr(r.total_retur)
        ])
    ));

    $('#tab-rp').html(buildTable(
        [
            { label: 'Tgl Retur' }, { label: 'No. Jurnal' }, { label: 'No. Retur' },
            { label: 'Customer' }, { label: 'Faktur Asal' }, { label: 'Barang & Lot' },
            { label: 'Alasan' }, { label: 'Qty', cls: 'text-right' }, { label: 'Satuan' },
            { label: 'Nilai Retur', cls: 'text-right' }, { label: 'Saldo Retur', cls: 'text-right' },
            { label: 'HPP Reversal', cls: 'text-right font-weight-bold' }
        ],
        (data.retur_penjualan || []).map(r => [
            escHtml(r.tanggal_retur), escHtml(r.nomor_jurnal), escHtml(r.no_retur),
            escHtml(r.nama_customer), escHtml(r.faktur_asal), escHtml(r.barang_lot),
            escHtml(r.alasan), fmtQty(r.qty_retur), escHtml(r.satuan),
            fmtIdr(r.nilai_retur), fmtIdr(r.saldo_retur_aktif), fmtIdr(r.hpp_reversal)
        ])
    ));

    $('#tab-pc').html(buildTable(
        [
            { label: 'Tanggal' }, { label: 'No. Transaksi / Jurnal' }, { label: 'Customer / Kios' },
            { label: 'Faktur' }, { label: 'Metode / Rekening' }, { label: 'Rincian' },
            { label: 'Nilai', cls: 'text-right font-weight-bold' }, { label: 'Status' }
        ],
        (data.pembayaran_customer || []).map(r => [
            escHtml(r.tanggal_pembayaran), escHtml(r.nomor_transaksi), escHtml(r.customer_kios),
            escHtml(r.no_faktur), escHtml(r.metode_rekening), escHtml(r.rincian),
            fmtIdr(r.nilai), escHtml(r.status_bayar)
        ])
    ));

    $('#tab-ps').html(buildTable(
        [
            { label: 'Tanggal' }, { label: 'No. Jurnal' }, { label: 'Supplier' },
            { label: 'No. LPB' }, { label: 'Rekening' },
            { label: 'Nilai', cls: 'text-right font-weight-bold' }, { label: 'Status' },
            { label: 'No. Bukti Bayar' }
        ],
        (data.pembayaran_supplier || []).map(r => [
            escHtml(r.tanggal_pembayaran), escHtml(r.nomor_jurnal), escHtml(r.nama_suplier),
            escHtml(r.no_lpb), escHtml(r.rekening), fmtIdr(r.nilai),
            escHtml(r.status_bayar), escHtml(r.no_bukti_bayar)
        ])
    ));

    const lr = data.laba_rugi || { summary: {}, per_customer: [] };
    const s = lr.summary || {};
    let lrHtml = '<h6 class="font-weight-bold mb-2">Ringkasan Laba Rugi Penjualan</h6>';
    lrHtml += buildTable(
        [{ label: 'Uraian' }, { label: 'Nilai', cls: 'text-right font-weight-bold' }],
        [
            ['Penjualan terfaktur sebelum retur (DPP)', fmtIdr(s.penjualan_dpp)],
            ['Retur penjualan (DPP)', fmtIdr(s.retur_dpp)],
            ['Penjualan bersih', fmtIdr(s.penjualan_bersih)],
            ['HPP penjualan sebelum retur (Moving Average)', fmtIdr(s.hpp_faktur)],
            ['Pengurang HPP atas barang yang diretur', fmtIdr(s.hpp_retur)],
            ['HPP bersih', fmtIdr(s.hpp_bersih)],
            ['Laba kotor setelah retur', fmtIdr(s.laba_kotor)],
            ['Margin laba kotor', Number(s.margin || 0).toFixed(2) + '%']
        ]
    );
    $('#tab-lr').html(lrHtml);

    // 10. Neraca Saldo & Laba Kotor (sesuai format akun jurnal terposting)
    const ma = data.mutasi_akun || { neraca_saldo: [], penjualan_accounts: [], hpp_accounts: [] };
    const nsRows = ma.neraca_saldo || [];
    if (!nsRows.length) {
        $('#tab-akun').html('<div class="alert alert-light border text-center text-muted mb-0">Tidak ada data jurnal terposting pada periode ini.</div>');
    } else {
        let html = '<div class="op-table-scroll"><table class="op-table">';
        html += '<thead><tr><th style="width:90px">Kode Akun</th><th>Nama Akun</th><th class="text-right" style="width:160px">Debit</th><th class="text-right" style="width:160px">Kredit</th></tr></thead><tbody>';
        nsRows.forEach(r => {
            html += `<tr>
                <td class="font-weight-bold">${escHtml(r.kode_akun)}</td>
                <td class="font-weight-bold">${escHtml(r.nama_akun)}</td>
                <td class="text-right font-weight-bold">${fmtAccNum(r.debit_saldo)}</td>
                <td class="text-right font-weight-bold">${fmtAccNum(r.kredit_saldo)}</td>
            </tr>`;
        });
        html += `<tr style="background:#f1f5f9;">
            <td></td><td></td>
            <td class="text-right font-weight-bold" style="border-top:2px solid #0f172a;background:#f1f5f9;">${fmtAccNum(ma.total_debit || 0)}</td>
            <td class="text-right font-weight-bold" style="border-top:2px solid #0f172a;background:#f1f5f9;">${fmtAccNum(ma.total_kredit || 0)}</td>
        </tr>`;
        html += '<tr><td colspan="4" style="height:14px;background:#fff;border-bottom:2px solid #0f172a;"></td></tr>';

        (ma.penjualan_accounts || []).forEach(p => {
            html += `<tr><td></td><td class="font-weight-bold">${escHtml(p.nama_akun)}</td><td class="text-right font-weight-bold">${fmtAccNum(p.nilai)}</td><td></td></tr>`;
        });
        html += `<tr><td></td><td></td><td></td><td class="text-right font-weight-bold" style="border-top:1px solid #0f172a;">${fmtAccNum(ma.total_penjualan || 0)}</td></tr>`;

        (ma.hpp_accounts || []).forEach(h => {
            html += `<tr><td></td><td class="font-weight-bold">${escHtml(h.nama_akun)}</td><td class="text-right font-weight-bold">${fmtAccNum(h.nilai)}</td><td></td></tr>`;
        });
        html += `<tr><td></td><td></td><td></td><td class="text-right font-weight-bold" style="border-top:1px solid #0f172a;">${fmtAccNum(ma.total_hpp || 0)}</td></tr>`;

        html += `<tr>
            <td style="background:#dc2626;"></td>
            <td class="font-weight-bold text-white" style="background:#dc2626;">LABA KOTOR</td>
            <td style="background:#dc2626;"></td>
            <td class="text-right font-weight-bold text-white" style="background:#dc2626;">${fmtAccNum(ma.laba_kotor || 0)}</td>
        </tr>`;
        html += '</tbody></table></div>';
        $('#tab-akun').html(html);
    }
}

$(document).ready(function() {
    renderOperasionalReport(INITIAL_OP_REPORT);

    $('#formFilterOperasional').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btnTampilkanOp');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...');
        $.getJSON('<?= base_url("laporan/lainnya/operasional-data") ?>', {
            date_from: $('#opDateFrom').val(),
            date_to: $('#opDateTo').val()
        }, function(res) {
            if (res.success && res.data) {
                renderOperasionalReport(res.data);
            }
        }).always(function() {
            btn.prop('disabled', false).html('<i class="fas fa-filter mr-1"></i> Tampilkan');
        });
    });

    $('#btnExportOperasional').on('click', function() {
        const q = $.param({
            date_from: $('#opDateFrom').val(),
            date_to: $('#opDateTo').val()
        });
        window.location.href = '<?= base_url("laporan/lainnya/operasional-export") ?>?' + q;
    });
});
</script>
