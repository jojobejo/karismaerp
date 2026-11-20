<style>
.efaktur-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.efaktur-paper {
    background: #fff;
    border: 1px solid #d9dee3;
    border-radius: 6px;
    padding: 24px 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    color: #1f2937;
    font-family: Arial, Helvetica, sans-serif;
}

.efaktur-meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #374151;
    margin-bottom: 4px;
}

.efaktur-header-center {
    text-align: center;
    margin-bottom: 18px;
}

.efaktur-company {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}

.efaktur-title {
    font-size: 20px;
    font-weight: 700;
    color: #0284c7;
    margin-bottom: 2px;
}

.efaktur-period {
    font-size: 12px;
    font-weight: 600;
    color: #b91c1c;
}

.efaktur-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
}

.efaktur-table thead th {
    background: #e5e7eb;
    border-top: 1.5px solid #4b5563;
    border-bottom: 1.5px solid #4b5563;
    padding: 7px 8px;
    font-weight: 700;
    color: #111827;
    text-align: left;
    white-space: nowrap;
}

.efaktur-table thead th.num,
.efaktur-table tbody td.num,
.efaktur-table tfoot td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}

.efaktur-table tbody td {
    padding: 5px 8px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
    color: #1f2937;
}

.efaktur-table tbody tr:hover {
    background: #f8fafc;
}

.efaktur-table tfoot td {
    font-weight: 700;
    border-top: 2px solid #111827;
    border-bottom: 2px double #111827;
    background: #f3f4f6;
    padding: 7px 8px;
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

    .efaktur-paper {
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
                        <h1>Penjualan - eFaktur</h1>
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
                <div class="efaktur-toolbar no-print">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" id="btnOpenFilter">
                            <i class="fas fa-filter mr-1"></i> Filter Data
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Cetak Laporan
                        </button>
                    </div>
                    <div class="text-muted small" id="efakturStatusInfo">
                        Pilih filter untuk menampilkan laporan eFaktur.
                    </div>
                </div>

                <div class="efaktur-paper">
                    <div class="efaktur-meta">
                        <span><?= date('g:i A, F j, Y') ?></span>
                        <span>Penjualan - eFaktur</span>
                    </div>
                    <div class="efaktur-header-center">
                        <div class="efaktur-company">KARISMA <?= date('Y') ?></div>
                        <div class="efaktur-title">Penjualan - eFaktur</div>
                        <div class="efaktur-period" id="efakturPeriodLabel">-</div>
                    </div>

                    <div id="efakturTableContainer">
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
                <h6 class="modal-title font-weight-bold mb-0">Filter Data - Penjualan eFaktur</h6>
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
                        <label class="small font-weight-bold">No. Ref / No. Faktur :</label>
                        <input type="text" class="form-control form-control-sm" id="filterNoRef" placeholder="Contoh: D2500001 atau KINV...">
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Pelanggan :</label>
                        <input type="text" class="form-control form-control-sm" id="filterPelanggan" placeholder="Nama kios atau pelanggan...">
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">NPWP :</label>
                        <input type="text" class="form-control form-control-sm" id="filterNpwp" placeholder="Cari nomor NPWP...">
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
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
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

    function renderTable(rows) {
        if (!rows || !rows.length) {
            return '<div class="text-center text-muted py-4">Tidak ada data penjualan eFaktur pada periode/filter ini.</div>';
        }

        var html = '<table class="efaktur-table"><thead><tr>';
        html += '<th style="width:90px;">Tanggal</th>';
        html += '<th style="width:125px;">No. Ref.</th>';
        html += '<th>Pelanggan</th>';
        html += '<th style="width:140px;">Nomor Seri Faktur</th>';
        html += '<th style="width:145px;">NPWP</th>';
        html += '<th class="num" style="width:135px;">Jumlah DPP</th>';
        html += '<th class="num" style="width:125px;">Jumlah PPN</th>';
        html += '<th style="width:70px; text-align:center;">Sudah</th>';
        html += '</tr></thead><tbody>';

        var totalDpp = 0;
        var totalPpn = 0;

        rows.forEach(function(r) {
            var dpp = parseFloat(r.jumlah_dpp || 0);
            var ppn = parseFloat(r.jumlah_ppn || 0);
            totalDpp += dpp;
            totalPpn += ppn;

            html += '<tr>';
            html += '<td>' + fmtDateDMY(r.tanggal_faktur) + '</td>';
            html += '<td>' + esc(r.no_faktur) + '</td>';
            html += '<td>' + esc(r.pelanggan) + '</td>';
            html += '<td>' + esc(r.nomor_seri_faktur) + '</td>';
            html += '<td>' + esc(r.npwp) + '</td>';
            html += '<td class="num">' + fmtNum(dpp) + '</td>';
            html += '<td class="num">' + fmtNum(ppn) + '</td>';
            html += '<td style="text-align:center;">' + esc(r.sudah_efaktur) + '</td>';
            html += '</tr>';
        });

        html += '</tbody><tfoot><tr>';
        html += '<td colspan="5" style="text-align:right;">Total (' + rows.length + ' Faktur)</td>';
        html += '<td class="num">' + fmtNum(totalDpp) + '</td>';
        html += '<td class="num">' + fmtNum(totalPpn) + '</td>';
        html += '<td></td>';
        html += '</tr></tfoot></table>';

        return html;
    }

    function loadReport() {
        var params = {
            start_date: $('#filterStartDate').val() || '',
            end_date: $('#filterEndDate').val() || '',
            no_ref: $('#filterNoRef').val() || '',
            pelanggan: $('#filterPelanggan').val() || '',
            npwp: $('#filterNpwp').val() || ''
        };

        $('#efakturTableContainer').html('<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat laporan...</div>');

        $.getJSON(endpointUrl, params)
            .done(function(res) {
                if (!res || !res.success) {
                    $('#efakturTableContainer').html('<div class="alert alert-danger">Gagal memuat data laporan.</div>');
                    return;
                }
                $('#efakturPeriodLabel').text(fmtDateLong(res.start_date) + ' - ' + fmtDateLong(res.end_date));
                $('#efakturTableContainer').html(renderTable(res.rows));
                $('#efakturStatusInfo').text('Menampilkan ' + (res.rows ? res.rows.length : 0) + ' data faktur.');
            })
            .fail(function() {
                $('#efakturTableContainer').html('<div class="alert alert-danger">Terjadi kesalahan saat mengambil data laporan.</div>');
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
