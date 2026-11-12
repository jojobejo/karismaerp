<?php
/**
 * Halaman Laporan TAGPD (Daftar Tagihan Piutang) pada menu Laporan Lainnya.
 */
$rows = $initial_report['rows'] ?? [];
$totals = $initial_report['totals'] ?? [];
$totalPelunasanDisplay = (float)($totals['pelunasan_retur_pot'] ?? 0)
    + (float)($totals['pelunasan_bg_cair'] ?? 0)
    + (float)($totals['pelunasan_ku_cash'] ?? 0);
?>
<style>
    .tagpd-page { font-family: 'Segoe UI', Arial, sans-serif; }
    .tagpd-page .content-header { padding: 8px .5rem 0; }

    .tagpd-toolbar {
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

    .tagpd-toolbar .form-group { margin-bottom: 0; min-width: 155px; }
    .tagpd-toolbar .form-group.fg-search { min-width: 240px; flex: 1 1 240px; }
    .tagpd-toolbar label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 4px;
        display: block;
    }

    .tagpd-summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .tagpd-stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #2d6b6b;
        border-radius: 8px;
        padding: 12px 14px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .04);
    }

    .tagpd-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }

    .tagpd-stat-val {
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
    }

    .tagpd-container {
        background: #fff;
        padding: 18px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, .06);
    }

    .tagpd-sheet-meta {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 2px solid #2d6b6b;
    }

    .tagpd-sheet-title {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    .tagpd-sheet-sub {
        font-size: 13px;
        color: #475569;
        margin: 2px 0 0;
    }

    .tagpd-today-badge {
        background: #f0fdfa;
        border: 1px solid #99f6e4;
        color: #115e59;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 700;
    }

    .tagpd-table-scroll {
        width: 100%;
        max-height: 68vh;
        overflow: auto;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        position: relative;
    }

    .tagpd-table {
        width: max-content;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
        color: #0f172a;
    }

    .tagpd-table th,
    .tagpd-table td {
        border-right: 1px solid #cbd5e1;
        border-bottom: 1px solid #e2e8f0;
        padding: 6px 8px;
        vertical-align: middle;
        white-space: nowrap;
        background: #fff;
    }

    .tagpd-table thead th {
        background: #e2eef6;
        color: #0f172a;
        font-weight: 700;
        text-align: center;
        border-bottom: 1px solid #94a3b8;
        position: sticky;
        z-index: 3;
    }

    .tagpd-table thead tr.tagpd-head-row1 th {
        top: 0;
        height: 32px;
    }

    .tagpd-table thead tr.tagpd-head-row2 th {
        top: 32px;
        height: 30px;
        background: #edf5fa;
        font-size: 11px;
    }

    .tagpd-table thead tr.tagpd-head-letters th {
        top: 62px;
        height: 24px;
        background: #f8fafc;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
    }

    .tagpd-table .sticky-col {
        position: sticky;
        z-index: 2;
    }

    .tagpd-table thead .sticky-col {
        z-index: 5;
    }

    .tagpd-table .col-no {
        left: 0;
        min-width: 46px;
        max-width: 46px;
    }

    .tagpd-table .col-faktur {
        left: 46px;
        min-width: 130px;
        box-shadow: 2px 0 4px rgba(15, 23, 42, .08);
    }

    .tagpd-table tbody tr:nth-child(even) td {
        background: #f8fafc;
    }

    .tagpd-table tbody tr:hover td {
        background: #eff6ff;
    }

    .tagpd-table td.col-address {
        white-space: normal;
        min-width: 220px;
        max-width: 300px;
    }

    .tagpd-table tfoot tr.tagpd-total-row td {
        background: #e2e8f0 !important;
        color: #0f172a;
        font-weight: 800;
        border-top: 2px solid #64748b;
        position: sticky;
        bottom: 0;
        z-index: 3;
    }

    @media (max-width: 991.98px) {
        .tagpd-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 575.98px) {
        .tagpd-summary-grid { grid-template-columns: 1fr; }
    }

    @media print {
        .main-header, .main-sidebar, .main-footer, .tagpd-toolbar, .content-header { display: none !important; }
        .content-wrapper { margin-left: 0 !important; background: #fff !important; }
        .tagpd-table-scroll { max-height: none !important; overflow: visible !important; }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse tagpd-page">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                        <div class="d-flex align-items-center">
                            <a href="<?= base_url('laporan/lainnya') ?>" class="btn btn-sm btn-outline-secondary mr-2" title="Kembali ke Laporan Lainnya">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <h4 class="m-0 font-weight-bold text-dark">TAGPD — Daftar Tagihan Piutang</h4>
                        </div>
                        <small class="text-muted">Laporan Lainnya / TAGPD</small>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <!-- Filter Toolbar -->
                    <div class="tagpd-toolbar">
                        <div class="form-group">
                            <label for="filterTodayDate">Tanggal Acuan (Today)</label>
                            <input type="date" id="filterTodayDate" class="form-control form-control-sm" value="<?= html_escape($today_date) ?>">
                        </div>
                        <div class="form-group">
                            <label for="filterSales">Sales</label>
                            <select id="filterSales" class="form-control form-control-sm">
                                <option value="">Semua Sales</option>
                                <?php foreach ($sales_options as $salesOpt) : ?>
                                    <option value="<?= html_escape($salesOpt) ?>"><?= html_escape($salesOpt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="filterStatusBayar">Status Tagihan</label>
                            <select id="filterStatusBayar" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="belum_lunas">Belum Lunas (Saldo PD &gt; 0)</option>
                                <option value="lunas">Sudah Lunas (Saldo PD = 0)</option>
                            </select>
                        </div>
                        <div class="form-group fg-search">
                            <label for="filterKeyword">Cari No. Faktur / Kode / Customer</label>
                            <input type="text" id="filterKeyword" class="form-control form-control-sm" placeholder="Ketik no faktur, kode, nama toko, atau sales...">
                        </div>
                        <div class="form-group d-flex flex-wrap" style="gap: 8px; min-width: auto;">
                            <button type="button" id="btnFilterTagpd" class="btn btn-sm btn-primary font-weight-bold">
                                <i class="fas fa-filter mr-1"></i> Tampilkan
                            </button>
                            <button type="button" id="btnExportXls" class="btn btn-sm btn-success font-weight-bold">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </button>
                            <button type="button" id="btnExportCsv" class="btn btn-sm btn-info font-weight-bold">
                                <i class="fas fa-file-csv mr-1"></i> Export CSV
                            </button>
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-secondary font-weight-bold">
                                <i class="fas fa-print mr-1"></i> Print
                            </button>
                        </div>
                    </div>

                    <!-- Ringkasan Nominal -->
                    <div class="tagpd-summary-grid">
                        <div class="tagpd-stat-card">
                            <div class="tagpd-stat-label">Jumlah Faktur</div>
                            <div class="tagpd-stat-val" id="statRowCount"><?= count($rows) ?> Faktur</div>
                        </div>
                        <div class="tagpd-stat-card" style="border-left-color: #2563eb;">
                            <div class="tagpd-stat-label">Saldo Awal (Rp)</div>
                            <div class="tagpd-stat-val" id="statSaldoAwal">Rp <?= number_format((float)($totals['saldo_awal'] ?? 0), 0, ',', '.') ?></div>
                        </div>
                        <div class="tagpd-stat-card" style="border-left-color: #d97706;">
                            <div class="tagpd-stat-label">Jumlah/Faktur Bulan Ini</div>
                            <div class="tagpd-stat-val" id="statJumlahFaktur">Rp <?= number_format((float)($totals['jumlah_faktur'] ?? 0), 0, ',', '.') ?></div>
                        </div>
                        <div class="tagpd-stat-card" style="border-left-color: #16a34a;">
                            <div class="tagpd-stat-label">Total Pelunasan Efektif</div>
                            <div class="tagpd-stat-val" id="statPelunasan">Rp <?= number_format($totalPelunasanDisplay, 0, ',', '.') ?></div>
                        </div>
                        <div class="tagpd-stat-card" style="border-left-color: #dc2626;">
                            <div class="tagpd-stat-label">Saldo PD Akhir (Rp)</div>
                            <div class="tagpd-stat-val" id="statSaldoPd">Rp <?= number_format((float)($totals['saldo_pd'] ?? 0), 0, ',', '.') ?></div>
                        </div>
                    </div>

                    <!-- Kontainer Tabel TAGPD -->
                    <div class="tagpd-container">
                        <div class="tagpd-sheet-meta">
                            <div>
                                <div class="text-muted small font-weight-bold">Jangka Waktu JT</div>
                                <h1 class="tagpd-sheet-title">Daftar Tagihan Piutang</h1>
                                <p class="tagpd-sheet-sub" id="lblPeriodeInfo">
                                    Periode Mutasi: <?= html_escape($month_start) ?> s/d <?= html_escape($today_date) ?>
                                    <span class="text-muted ml-2">(Kolom yang belum tersedia di sistem dikosongkan sesuai format)</span>
                                </p>
                            </div>
                            <div class="tagpd-today-badge">
                                Today: <span id="lblTodayDate"><?= html_escape($today_date) ?></span>
                            </div>
                        </div>

                        <div class="tagpd-table-scroll" id="tagpdTableWrap">
                            <?php
                            $is_excel = false;
                            include APPPATH . 'views/content/keuangan/laporan/laporan_lainnya/_tagpd_table.php';
                            ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
        </footer>
    </div>

    <script>
        $(function() {
            function formatRupiah(num) {
                var val = Math.round(parseFloat(num || 0));
                return 'Rp ' + val.toLocaleString('id-ID');
            }

            function getFilters() {
                return {
                    today_date: $('#filterTodayDate').val(),
                    sales: $('#filterSales').val(),
                    status_bayar: $('#filterStatusBayar').val(),
                    keyword: $('#filterKeyword').val()
                };
            }

            function loadTagpdData() {
                var params = getFilters();
                var $btn = $('#btnFilterTagpd');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...');

                $.ajax({
                    url: '<?= base_url('laporan/lainnya/tagpd-data') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: params,
                    success: function(res) {
                        if (!res || !res.success) {
                            alert('Gagal memuat data laporan TAGPD.');
                            return;
                        }

                        var report = res.data || {};
                        var totals = report.totals || {};
                        var rows = report.rows || [];

                        $('#tagpdTableWrap').html(res.table_html || '');
                        $('#lblTodayDate').text(report.today_date || params.today_date);
                        $('#lblPeriodeInfo').html(
                            'Periode Mutasi: ' + (report.month_start || '-') + ' s/d ' + (report.today_date || '-') +
                            ' <span class="text-muted ml-2">(Kolom yang belum tersedia di sistem dikosongkan sesuai format)</span>'
                        );

                        var pelunasan = parseFloat(totals.pelunasan_retur_pot || 0)
                            + parseFloat(totals.pelunasan_bg_cair || 0)
                            + parseFloat(totals.pelunasan_ku_cash || 0);

                        $('#statRowCount').text(rows.length + ' Faktur');
                        $('#statSaldoAwal').text(formatRupiah(totals.saldo_awal));
                        $('#statJumlahFaktur').text(formatRupiah(totals.jumlah_faktur));
                        $('#statPelunasan').text(formatRupiah(pelunasan));
                        $('#statSaldoPd').text(formatRupiah(totals.saldo_pd));
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat mengambil data laporan TAGPD.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-filter mr-1"></i> Tampilkan');
                    }
                });
            }

            function exportTagpd(format) {
                var params = getFilters();
                params.format = format;
                window.location.href = '<?= base_url('laporan/lainnya/tagpd-export') ?>?' + $.param(params);
            }

            $('#btnFilterTagpd').on('click', loadTagpdData);
            $('#filterSales, #filterStatusBayar, #filterTodayDate').on('change', loadTagpdData);
            $('#filterKeyword').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    loadTagpdData();
                }
            });

            $('#btnExportXls').on('click', function() {
                exportTagpd('xls');
            });
            $('#btnExportCsv').on('click', function() {
                exportTagpd('csv');
            });
        });
    </script>
</body>

