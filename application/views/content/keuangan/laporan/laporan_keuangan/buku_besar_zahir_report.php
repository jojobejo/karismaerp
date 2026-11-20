<?php
$meta       = $report_meta ?? [];
$slug       = $meta['slug'] ?? 'buku-besar-standar';
$title      = $meta['title'] ?? 'Buku Besar - Standar';
$subtitle   = $meta['subtitle'] ?? '';
$category   = $meta['category'] ?? 'ledger';
$variant    = $meta['variant'] ?? '';
$mode       = $meta['mode'] ?? '';
$coaOptions = $coa_options ?? [];
?>

<style>
.zahir-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.zahir-paper {
    background: #fff;
    border: 1px solid #d9dee3;
    border-radius: 6px;
    padding: 24px 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    color: #1f2937;
    font-family: Arial, Helvetica, sans-serif;
}

.zahir-header-meta {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    font-size: 11px;
    color: #374151;
    margin-bottom: 4px;
}

.zahir-header-center {
    text-align: center;
    margin-bottom: 18px;
}

.zahir-company {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}

.zahir-title {
    font-size: 20px;
    font-weight: 700;
    color: #0284c7;
    margin-bottom: 2px;
}

.zahir-subtitle {
    font-size: 12px;
    font-weight: 700;
    color: #b91c1c;
    margin-bottom: 2px;
}

.zahir-period {
    font-size: 12px;
    font-weight: 600;
    color: #b91c1c;
}

.zahir-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
}

.zahir-table thead th {
    background: #e5e7eb;
    border-top: 1.5px solid #4b5563;
    border-bottom: 1.5px solid #4b5563;
    padding: 6px 8px;
    font-weight: 700;
    color: #111827;
    text-align: left;
    white-space: nowrap;
}

.zahir-table thead th.num,
.zahir-table tbody td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.zahir-table tbody td {
    padding: 4px 8px;
    vertical-align: top;
    color: #1f2937;
}

.zahir-acc-header {
    background: #f8fafc;
    border-top: 1px solid #9ca3af;
    border-bottom: 1px solid #cbd5e1;
    font-weight: 700;
    color: #0f172a;
}

.zahir-acc-opening td {
    font-style: italic;
    color: #374151;
    border-bottom: 1px dashed #e5e7eb;
}

.zahir-acc-total td {
    font-weight: 700;
    border-top: 1px solid #6b7280;
    border-bottom: 1.5px solid #374151;
    background: #f9fafb;
    padding-top: 5px;
    padding-bottom: 6px;
}

.zahir-journal-hdr td {
    font-weight: 700;
    border-top: 1px solid #e5e7eb;
    background: #fafafa;
    padding-top: 6px;
}

.zahir-journal-dtl td {
    padding-top: 2px;
    padding-bottom: 2px;
}

.zahir-audit-note td {
    font-size: 10.5px;
    color: #4b5563;
    font-style: italic;
    padding-top: 1px;
    padding-bottom: 5px;
    border-bottom: 1px solid #f1f5f9;
}

.zahir-grand-total td {
    font-weight: 700;
    border-top: 2px solid #111827;
    border-bottom: 2px double #111827;
    background: #f3f4f6;
    padding: 7px 8px;
}

/* Modal Filter Data bergaya Zahir */
.zahir-filter-modal .modal-content {
    border-radius: 4px;
    border: 1px solid #cbd5e1;
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

    .zahir-paper {
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
                    <a href="<?= base_url('laporan/keuangan') ?>" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Laporan Keuangan
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="zahir-toolbar no-print">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm" id="btnOpenFilter">
                        <i class="fas fa-filter mr-1"></i> Filter Data
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Cetak Laporan
                    </button>
                </div>
                <div class="text-muted small" id="reportStatusInfo">
                    Pilih filter untuk menampilkan laporan.
                </div>
            </div>

            <div class="zahir-paper" id="zahirReportPaper">
                <div class="zahir-header-meta">
                    <span id="zahirPrintTime"><?= date('g:i A, F j, Y') ?></span>
                    <span><?= htmlspecialchars($title) ?></span>
                </div>
                <div class="zahir-header-center">
                    <div class="zahir-company">KARISMA <?= date('Y') ?></div>
                    <div class="zahir-title"><?= htmlspecialchars($title) ?></div>
                    <?php if ($subtitle !== ''): ?>
                        <div class="zahir-subtitle"><?= htmlspecialchars($subtitle) ?></div>
                    <?php endif; ?>
                    <?php if ($category !== 'coa'): ?>
                        <div class="zahir-period" id="zahirPeriodLabel">-</div>
                    <?php endif; ?>
                </div>

                <div id="zahirTableContainer">
                    <div class="text-center text-muted py-4">
                        Silakan tentukan filter pada jendela Filter Data terlebih dahulu.
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div>

<!-- Modal Filter Data (Standar & Pencarian) -->
<div class="modal fade zahir-filter-modal" id="modalFilterData" tabindex="-1" role="dialog" aria-hidden="true">
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
                    <?php if ($category !== 'coa'): ?>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold">Dari Tanggal :</label>
                                <input type="date" class="form-control form-control-sm" id="filterStartDate" value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold">Hingga Tanggal :</label>
                                <input type="date" class="form-control form-control-sm" id="filterEndDate" value="<?= date('Y-m-t') ?>">
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($category === 'ledger'): ?>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Akun / Rekening :</label>
                            <select class="form-control form-control-sm" id="filterIdAkun">
                                <option value="">-- Semua Rekening --</option>
                                <?php foreach ($coaOptions as $opt): ?>
                                    <option value="<?= (int) $opt['id_akun'] ?>">
                                        <?= htmlspecialchars($opt['kode_akun'] . ' - ' . $opt['nama_akun']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php elseif ($category === 'coa'): ?>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Filter Klasifikasi / Rekening :</label>
                            <input type="text" class="form-control form-control-sm" id="filterSearchStandar" placeholder="Kosongkan untuk semua rekening...">
                        </div>
                    <?php endif; ?>
                </div>

                <div id="filterTabPencarian" style="display: none;">
                    <?php if ($category === 'ledger' || $category === 'coa'): ?>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Kode / Nama Rekening :</label>
                            <input type="text" class="form-control form-control-sm" id="filterSearchKeyword" placeholder="Contoh: 11011 atau Kas...">
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label class="small font-weight-bold">Keterangan Jurnal :</label>
                            <input type="text" class="form-control form-control-sm" id="filterKeterangan" placeholder="Cari berdasarkan keterangan...">
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Nomor Jurnal / Referensi :</label>
                            <input type="text" class="form-control form-control-sm" id="filterNomor" placeholder="Contoh: GJ-202611 atau INV...">
                        </div>
                    <?php endif; ?>
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
        return num < 0 ? '(' + absVal + ')' : absVal;
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

    function renderLedgerTable(rows, variant) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data buku besar pada periode/filter ini.</div>';
        }

        var isMutasi = (variant === 'mutasi' || variant === 'mutasi_mata_uang');
        var isActualCurrency = (variant === 'mata_uang' || variant === 'mutasi_mata_uang');
        var openLabel = (variant === 'mata_uang') ? 'Beginning Balance :' : 'Saldo Awal :';
        var closeLabel = (variant === 'mata_uang') ? 'Ending Balance :' : 'Saldo Akhir :';

        var html = '<table class="zahir-table"><thead><tr>';
        if (isMutasi) {
            html += '<th style="width:90px">Tanggal</th>';
            html += '<th style="width:45px">Tp</th>';
            html += '<th style="width:140px">No. Ref.</th>';
            html += '<th>Keterangan</th>';
            html += '<th class="num" style="width:125px">Debet</th>';
            html += '<th class="num" style="width:125px">Kredit</th>';
            html += '<th class="num" style="width:130px">Saldo</th>';
        } else {
            html += '<th style="width:90px">Tanggal</th>';
            html += '<th style="width:45px">Tp</th>';
            html += '<th style="width:140px">No. Ref.</th>';
            html += '<th>Keterangan</th>';
            html += '<th style="width:75px">No. Dept.</th>';
            html += '<th class="num" style="width:125px">Debet</th>';
            html += '<th class="num" style="width:125px">Kredit</th>';
            html += '<th style="width:85px">No. Proyek</th>';
        }
        html += '</tr></thead><tbody>';

        var colCount = isMutasi ? 7 : 8;

        rows.forEach(function(acc) {
            html += '<tr class="zahir-acc-header">';
            if (isActualCurrency) {
                html += '<td colspan="' + (colCount - 1) + '">' + esc(acc.kode_akun) + ' &nbsp;&nbsp; ' + esc(acc.nama_akun) + '</td>';
                html += '<td class="num">IDR</td>';
            } else {
                html += '<td colspan="' + colCount + '">' + esc(acc.kode_akun) + ' &nbsp;&nbsp; ' + esc(acc.nama_akun) + '</td>';
            }
            html += '</tr>';

            html += '<tr class="zahir-acc-opening">';
            html += '<td colspan="' + (colCount - 1) + '" style="text-align:right; font-weight:600;">' + openLabel + '</td>';
            html += '<td class="num" style="font-weight:600;">' + fmtNum(acc.opening_balance) + '</td>';
            html += '</tr>';

            (acc.rows || []).forEach(function(tx) {
                html += '<tr>';
                html += '<td>' + fmtDateDMY(tx.tanggal_transaksi) + '</td>';
                html += '<td>' + esc(tx.tp) + '</td>';
                html += '<td>' + esc(tx.no_ref) + '</td>';
                html += '<td>' + esc(tx.keterangan) + '</td>';
                if (isMutasi) {
                    html += '<td class="num">' + fmtNumBlankZero(tx.debit) + '</td>';
                    html += '<td class="num">' + fmtNumBlankZero(tx.kredit) + '</td>';
                    html += '<td class="num">' + fmtNum(tx.saldo) + '</td>';
                } else {
                    html += '<td>' + esc(tx.no_dept) + '</td>';
                    html += '<td class="num">' + fmtNumBlankZero(tx.debit) + '</td>';
                    html += '<td class="num">' + fmtNumBlankZero(tx.kredit) + '</td>';
                    html += '<td>' + esc(tx.no_proyek) + '</td>';
                }
                html += '</tr>';
            });

            html += '<tr class="zahir-acc-total">';
            if (isMutasi) {
                html += '<td colspan="4" style="text-align:right;">Total &nbsp;|&nbsp; ' + closeLabel + '</td>';
                html += '<td class="num">' + fmtNum(acc.total_debit) + '</td>';
                html += '<td class="num">' + fmtNum(acc.total_kredit) + '</td>';
                html += '<td class="num">' + fmtNum(acc.ending_balance) + '</td>';
            } else {
                html += '<td colspan="5" style="text-align:right;">Total &nbsp;|&nbsp; ' + closeLabel + ' ' + fmtNum(acc.ending_balance) + '</td>';
                html += '<td class="num">' + fmtNum(acc.total_debit) + '</td>';
                html += '<td class="num">' + fmtNum(acc.total_kredit) + '</td>';
                html += '<td></td>';
            }
            html += '</tr>';
        });

        html += '</tbody></table>';
        return html;
    }

    function renderJournalTable(rows, mode) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada transaksi jurnal pada periode/filter ini.</div>';
        }

        var showRefCol = (mode === 'all');
        var html = '<table class="zahir-table"><thead><tr>';
        if (showRefCol) {
            html += '<th style="width:130px">Ref.</th>';
        }
        html += '<th style="width:90px">Tanggal</th>';
        html += '<th>Keterangan</th>';
        html += '<th style="width:75px">No. Dept.</th>';
        html += '<th class="num" style="width:125px">Debet</th>';
        html += '<th class="num" style="width:125px">Kredit</th>';
        html += '<th style="width:85px">No. Proyek</th>';
        html += '</tr></thead><tbody>';

        var grandDebit = 0;
        var grandKredit = 0;

        rows.forEach(function(j) {
            html += '<tr class="zahir-journal-hdr">';
            if (showRefCol) {
                html += '<td>' + esc(j.no_ref) + '</td>';
            }
            html += '<td>' + fmtDateDMY(j.tanggal_transaksi) + '</td>';
            html += '<td colspan="' + (showRefCol ? 5 : 5) + '">' + esc(j.keterangan || j.nomor_jurnal) + '</td>';
            html += '</tr>';

            (j.details || []).forEach(function(d) {
                var deb = parseFloat(d.debit || 0);
                var kre = parseFloat(d.kredit || 0);
                grandDebit += deb;
                grandKredit += kre;

                html += '<tr class="zahir-journal-dtl">';
                if (showRefCol) {
                    html += '<td></td>';
                }
                html += '<td></td>';
                html += '<td><span style="display:inline-block; min-width:80px;">' + esc(d.kode_akun) + '</span> ' + esc(d.nama_akun) + (d.keterangan ? ' — ' + esc(d.keterangan) : '') + '</td>';
                html += '<td>' + esc(d.no_dept) + '</td>';
                html += '<td class="num">' + fmtNumBlankZero(deb) + '</td>';
                html += '<td class="num">' + fmtNumBlankZero(kre) + '</td>';
                html += '<td>' + esc(d.no_proyek) + '</td>';
                html += '</tr>';
            });
        });

        html += '<tr class="zahir-grand-total">';
        html += '<td colspan="' + (showRefCol ? 4 : 3) + '" style="text-align:right;">Total</td>';
        html += '<td class="num">' + fmtNum(grandDebit) + '</td>';
        html += '<td class="num">' + fmtNum(grandKredit) + '</td>';
        html += '<td></td>';
        html += '</tr>';

        html += '</tbody></table>';
        return html;
    }

    function renderCoaTable(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada daftar rekening yang cocok.</div>';
        }

        var html = '<table class="zahir-table"><thead><tr>';
        html += '<th style="width:220px">Klasifikasi</th>';
        html += '<th style="width:130px">Kode Rekening</th>';
        html += '<th>Nama Rekening</th>';
        html += '<th>Nama Alias Rekening</th>';
        html += '</tr></thead><tbody>';

        var lastGroup = '';
        rows.forEach(function(r) {
            var grp = r.nama_klasifikasi || '';
            var showGrp = (grp !== lastGroup) ? grp : '';
            lastGroup = grp;

            html += '<tr style="border-bottom:1px solid #f1f5f9;">';
            html += '<td style="font-weight:' + (showGrp ? '700' : '400') + ';">' + esc(showGrp) + '</td>';
            html += '<td>' + esc(r.kode_akun) + '</td>';
            html += '<td>' + esc(r.nama_akun) + '</td>';
            html += '<td>' + esc(r.nama_alias) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        return html;
    }

    function buildAuditNoteText(j, prefixInput) {
        var parts = [];
        parts.push((prefixInput || 'Diinput Oleh') + ' : ' + esc(j.created_by_name) + ' (' + esc(j.created_at) + ')');
        if (j.updated_at && j.updated_at !== j.created_at && j.updated_by_name) {
            parts.push('Edit Transaksi : ' + esc(j.updated_by_name) + ' (' + esc(j.updated_at) + ')');
        }
        if (j.reversed_at) {
            parts.push('Reversal : ' + esc(j.reversed_by_name || '-') + ' (' + esc(j.reversed_at) + ')');
        }
        return parts.join(' &nbsp;|&nbsp; ');
    }

    function renderAuditTable(rows, mode) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada catatan jejak audit pada periode/filter ini.</div>';
        }

        if (mode === 'audit_summary') {
            var html = '<table class="zahir-table"><thead><tr>';
            html += '<th style="width:125px">Ref.</th>';
            html += '<th style="width:90px">Tanggal</th>';
            html += '<th style="width:125px">No. Ref.</th>';
            html += '<th>Keterangan</th>';
            html += '<th style="width:120px">Catatan</th>';
            html += '<th class="num" style="width:125px">Jumlah</th>';
            html += '<th style="width:120px">Nama</th>';
            html += '</tr></thead><tbody>';

            var grandTotal = 0;
            rows.forEach(function(j) {
                var amt = parseFloat(j.total_debit || 0);
                grandTotal += amt;
                html += '<tr class="zahir-journal-hdr">';
                html += '<td>' + esc(j.nomor_jurnal) + '</td>';
                html += '<td>' + fmtDateDMY(j.tanggal_transaksi) + '</td>';
                html += '<td>' + esc(j.no_ref) + '</td>';
                html += '<td>' + esc(j.keterangan) + '</td>';
                html += '<td>' + esc(j.status) + '</td>';
                html += '<td class="num">' + fmtNum(amt) + '</td>';
                html += '<td>' + esc(j.created_by_name) + '</td>';
                html += '</tr>';
                html += '<tr class="zahir-audit-note"><td colspan="7">' + buildAuditNoteText(j, 'Diinput Oleh') + '</td></tr>';
            });

            html += '<tr class="zahir-grand-total">';
            html += '<td colspan="5" style="text-align:right;">Total</td>';
            html += '<td class="num">' + fmtNum(grandTotal) + '</td>';
            html += '<td></td>';
            html += '</tr></tbody></table>';
            return html;
        }

        var showRef = (mode === 'audit_all');
        var html = '<table class="zahir-table"><thead><tr>';
        if (showRef) {
            html += '<th style="width:125px">Ref.</th>';
        }
        html += '<th style="width:90px">Tanggal</th>';
        html += '<th>Keterangan</th>';
        html += '<th style="width:110px">Catatan</th>';
        html += '<th class="num" style="width:125px">Debet</th>';
        html += '<th class="num" style="width:125px">Kredit</th>';
        html += '<th style="width:120px">Nama</th>';
        html += '</tr></thead><tbody>';

        var gDeb = 0;
        var gKre = 0;
        var colSpanAll = showRef ? 7 : 6;

        rows.forEach(function(j) {
            html += '<tr class="zahir-journal-hdr">';
            if (showRef) {
                html += '<td>' + esc(j.no_ref) + '</td>';
            }
            html += '<td>' + fmtDateDMY(j.tanggal_transaksi) + '</td>';
            html += '<td>' + esc(j.keterangan || j.nomor_jurnal) + '</td>';
            html += '<td>' + esc(j.status) + '</td>';
            html += '<td colspan="2"></td>';
            html += '<td>' + esc(j.created_by_name) + '</td>';
            html += '</tr>';

            (j.details || []).forEach(function(d) {
                var deb = parseFloat(d.debit || 0);
                var kre = parseFloat(d.kredit || 0);
                gDeb += deb;
                gKre += kre;

                html += '<tr class="zahir-journal-dtl">';
                if (showRef) {
                    html += '<td></td>';
                }
                html += '<td></td>';
                html += '<td><span style="display:inline-block; min-width:80px;">' + esc(d.kode_akun) + '</span> ' + esc(d.nama_akun) + '</td>';
                html += '<td>' + esc(d.keterangan) + '</td>';
                html += '<td class="num">' + fmtNumBlankZero(deb) + '</td>';
                html += '<td class="num">' + fmtNumBlankZero(kre) + '</td>';
                html += '<td></td>';
                html += '</tr>';
            });

            html += '<tr class="zahir-audit-note"><td colspan="' + colSpanAll + '">' + buildAuditNoteText(j, mode === 'audit_detail' ? 'Input Transaksi' : 'Diinput Oleh') + '</td></tr>';
        });

        html += '<tr class="zahir-grand-total">';
        html += '<td colspan="' + (showRef ? 4 : 3) + '" style="text-align:right;">Total</td>';
        html += '<td class="num">' + fmtNum(gDeb) + '</td>';
        html += '<td class="num">' + fmtNum(gKre) + '</td>';
        html += '<td></td>';
        html += '</tr></tbody></table>';
        return html;
    }

    function loadReport() {
        var params = {
            jenis: reportMeta.slug,
            start_date: $('#filterStartDate').val() || '',
            end_date: $('#filterEndDate').val() || '',
            id_akun: $('#filterIdAkun').val() || '',
            keterangan: $('#filterKeterangan').val() || '',
            nomor: $('#filterNomor').val() || '',
            search: $('#filterSearchKeyword').val() || $('#filterSearchStandar').val() || ''
        };

        $('#zahirTableContainer').html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat laporan...</div>');

        $.getJSON(endpointUrl, params)
            .done(function(res) {
                if (!res || !res.success) {
                    $('#zahirTableContainer').html('<div class="alert alert-danger">Gagal memuat data laporan.</div>');
                    return;
                }

                if (reportMeta.category !== 'coa') {
                    $('#zahirPeriodLabel').text(fmtDateLong(res.start_date) + ' - ' + fmtDateLong(res.end_date));
                }

                var html = '';
                if (reportMeta.category === 'ledger') {
                    html = renderLedgerTable(res.rows, reportMeta.variant);
                } else if (reportMeta.category === 'coa') {
                    html = renderCoaTable(res.rows);
                } else if (reportMeta.category === 'audit') {
                    html = renderAuditTable(res.rows, reportMeta.mode);
                } else {
                    html = renderJournalTable(res.rows, reportMeta.mode);
                }

                $('#zahirTableContainer').html(html);
                $('#reportStatusInfo').text('Data dimuat (' + (res.rows ? res.rows.length : 0) + ' kelompok/entri).');
            })
            .fail(function() {
                $('#zahirTableContainer').html('<div class="alert alert-danger">Terjadi kesalahan saat mengambil data laporan.</div>');
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

        // Buka modal Filter Data secara otomatis saat halaman pertama dibuka
        $('#modalFilterData').modal('show');
    });
})();
</script>
