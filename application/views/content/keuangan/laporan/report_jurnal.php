<?php
/**
 * View Report Jurnal Transaksi Akuntansi
 * Menampilkan kolom:
 * No. | Ref. | Date | No. Bukti | No. Bukti | No. Perk | Descriptions AKUN | Descriptions | Debit | Credit
 * Dilengkapi filter tanggal & kategori, pencarian, ringkasan balance, dan ekspor Excel/CSV.
 */
?>
<style>
    .report-table thead th {
        background-color: #2c3e50;
        color: #ffffff;
        text-align: center;
        vertical-align: middle;
        font-size: 12px;
        white-space: nowrap;
        padding: 8px 6px;
        border-color: #34495e;
    }
    .report-table tbody td {
        font-size: 12px;
        vertical-align: middle;
        padding: 6px 8px;
    }
    .report-table tfoot td {
        background-color: #f8f9fa;
        font-size: 13px;
        font-weight: bold;
        padding: 8px;
    }
    .card-filter {
        border-top: 3px solid #17a2b8;
    }
    .badge-category {
        font-size: 10px;
        padding: 3px 6px;
        font-weight: 600;
    }
    .table-row-jurnal-z {
        background-color: #f4f9fd !important;
        border-left: 4px solid #007bff !important;
    }
    .table-row-jurnal-q {
        background-color: #fffdf5 !important;
        border-left: 4px solid #ffc107 !important;
    }
    .table-row-jurnal-a {
        background-color: #fff8f8 !important;
        border-left: 4px solid #dc3545 !important;
    }
    .legend-box {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 4px;
        margin-right: 6px;
    }
    .legend-z {
        background-color: #e8f4f8;
        color: #1b4f72;
        border: 1px solid #b8daff;
    }
    .legend-q {
        background-color: #fffde7;
        color: #7d6608;
        border: 1px solid #ffeeba;
    }
    .legend-a {
        background-color: #ffdeeb;
        color: #78281f;
        border: 1px solid #f5c6cb;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <!-- Navbar & Sidebar -->
    <?php $this->load->view('partial/main/navbar'); ?>
    <?php $this->load->view('partial/main/sidebar'); ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold">
                            <i class="fas fa-file-invoice-dollar text-info mr-2"></i> Report Jurnal Transaksi
                        </h1>
                        <small class="text-muted">Laporan Buku Jurnal Transaksi Akuntansi & Rincian Pembayaran</small>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right bg-transparent p-0 mb-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('keuangan/pembayaran') ?>">Keuangan</a></li>
                            <li class="breadcrumb-item active">Report Jurnal</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">

                <!-- Filter Card -->
                <div class="card card-outline card-filter shadow-sm mb-3">
                    <div class="card-header bg-white py-2">
                        <h3 class="card-title font-weight-bold text-dark pt-1">
                            <i class="fas fa-filter text-info mr-1"></i> Filter & Parameter Laporan
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body py-3">
                        <form id="form-filter" method="GET" action="<?= base_url('keuangan/report_jurnal') ?>">
                            <div class="row align-items-end">
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="far fa-calendar-alt mr-1"></i> Tanggal Mulai:
                                    </label>
                                    <input type="date" name="start_date" id="filter-start-date" class="form-control form-control-sm" value="<?= htmlspecialchars($start_date) ?>" required>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="far fa-calendar-alt mr-1"></i> Tanggal Akhir:
                                    </label>
                                    <input type="date" name="end_date" id="filter-end-date" class="form-control form-control-sm" value="<?= htmlspecialchars($end_date) ?>" required>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-money-check-alt mr-1"></i> Status Pelunasan Faktur:
                                    </label>
                                    <select name="status_lunas" id="filter-status-lunas" class="form-control form-control-sm font-weight-bold">
                                        <option value="all_paid" <?= ($status_lunas === 'all_paid') ? 'selected' : '' ?>>Semua Pembayaran (Sudah Dibayar)</option>
                                        <option value="lunas" <?= ($status_lunas === 'lunas') ? 'selected' : '' ?>>Hanya Faktur Lunas (100%)</option>
                                        <option value="sebagian" <?= ($status_lunas === 'sebagian') ? 'selected' : '' ?>>Faktur Belum Lunas (Sebagian)</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-tag mr-1"></i> Jenis Jurnal:
                                    </label>
                                    <select name="jenis" id="filter-jenis" class="form-control form-control-sm">
                                        <option value="">Semua Jenis</option>
                                        <?php if (!empty($jenis_jurnals)): ?>
                                            <?php foreach ($jenis_jurnals as $jj): ?>
                                                <option value="<?= htmlspecialchars($jj['kode_jenis_jurnal']) ?>" <?= ($jenis === $jj['kode_jenis_jurnal']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($jj['kode_jenis_jurnal']) ?> - <?= htmlspecialchars($jj['nama_jenis_jurnal']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="MR" <?= ($jenis === 'MR') ? 'selected' : '' ?>>MR - Masuk Rekening</option>
                                            <option value="KM" <?= ($jenis === 'KM') ? 'selected' : '' ?>>KM - Kas Masuk</option>
                                            <option value="JU" <?= ($jenis === 'JU') ? 'selected' : '' ?>>JU - Jurnal Umum</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">
                                        <i class="fas fa-sitemap mr-1"></i> Kategori Faktur:
                                    </label>
                                    <select name="kategori" id="filter-kategori" class="form-control form-control-sm">
                                        <option value="">Semua Kategori</option>
                                        <option value="z_asli" <?= ($kategori === 'z_asli') ? 'selected' : '' ?>>Jurnal Asli (Z)</option>
                                        <option value="q_acak" <?= ($kategori === 'q_acak') ? 'selected' : '' ?>>Jurnal Acak (Q Pecahan)</option>
                                        <option value="a_pot"  <?= ($kategori === 'a_pot') ? 'selected' : '' ?>>Jurnal Potongan (A Kas)</option>
                                    </select>
                                </div>
                                <div class="col-lg-1 col-md-4 col-sm-12 mb-2 d-flex">
                                    <button type="button" id="btn-apply-filter" class="btn btn-info btn-sm font-weight-bold flex-fill mr-1" title="Terapkan Filter">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button type="button" id="btn-reset-filter" class="btn btn-default btn-sm" title="Reset Filter">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Panduan Kategori Jurnal (Legend) -->
                <div class="row mb-2">
                    <div class="col-12">
                        <div class="bg-white p-2 rounded border d-flex align-items-center flex-wrap" style="gap: 8px;">
                            <span class="small font-weight-bold text-muted mr-1"><i class="fas fa-palette mr-1"></i>Panduan Kategori Jurnal:</span>
                            <span class="legend-box legend-z">
                                <i class="fas fa-circle mr-1 text-primary"></i> Jurnal Asli (Z - Faktur Induk)
                            </span>
                            <span class="legend-box legend-q">
                                <i class="fas fa-circle mr-1 text-warning"></i> Jurnal Acak (Q - Faktur Pecahan H)
                            </span>
                            <span class="legend-box legend-a">
                                <i class="fas fa-circle mr-1 text-danger"></i> Jurnal (A) Kas Masuk (Potongan KIU / Tax)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action & Summary Bar -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="btn-group" role="group">
                            <button type="button" id="btn-export-excel" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel (.xlsx)
                            </button>
                            <button type="button" id="btn-export-csv" class="btn btn-outline-secondary btn-sm font-weight-bold shadow-sm">
                                <i class="fas fa-file-csv mr-1"></i> Export CSV
                            </button>
                            <button type="button" id="btn-print-table" class="btn btn-outline-info btn-sm font-weight-bold shadow-sm">
                                <i class="fas fa-print mr-1"></i> Cetak / Print
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <div class="d-inline-flex align-items-center flex-wrap" style="gap: 10px;">
                            <span class="badge badge-light border p-2 text-dark font-weight-bold" style="font-size: 12px;">
                                Total Record: <span id="summary-total-records" class="text-primary font-weight-bold">0</span>
                            </span>
                            <span class="badge badge-light border p-2 text-dark font-weight-bold" style="font-size: 12px;">
                                Total Debit: <span id="summary-total-debit" class="text-success font-weight-bold">Rp 0</span>
                            </span>
                            <span class="badge badge-light border p-2 text-dark font-weight-bold" style="font-size: 12px;">
                                Total Credit: <span id="summary-total-credit" class="text-danger font-weight-bold">Rp 0</span>
                            </span>
                            <span id="badge-balance-status" class="badge badge-success p-2 font-weight-bold" style="font-size: 12px;">
                                <i class="fas fa-check-circle mr-1"></i> BALANCE
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tbl-report-jurnal" class="table table-bordered table-striped table-hover report-table mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 4%;">No.</th>
                                        <th style="width: 5%;">Ref.</th>
                                        <th style="width: 8%;">Date</th>
                                        <th style="width: 7%;">No. Bukti</th>
                                        <th style="width: 13%;">No. Bukti</th>
                                        <th style="width: 9%;">No. Perk</th>
                                        <th style="width: 17%;">Descriptions AKUN</th>
                                        <th style="width: 19%;">Descriptions</th>
                                        <th style="width: 9%;" class="text-right">Debit</th>
                                        <th style="width: 9%;" class="text-right">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Populated via AJAX -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8" class="text-right">TOTAL KESELURUHAN:</td>
                                        <td class="text-right text-success" id="tfoot-total-debit">Rp 0</td>
                                        <td class="text-right text-danger" id="tfoot-total-credit">Rp 0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>

<!-- Page Scripts -->
<script>
$(document).ready(function() {
    // Handler toggle sidebar cadangan
    $(document).on('click', '[data-widget="pushmenu"], .karisma-topbar-toggle', function(e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-collapse');
    });
    let dtTable = null;

    function initOrReloadTable() {
        if (dtTable) {
            dtTable.ajax.reload();
            return;
        }

        dtTable = $('#tbl-report-jurnal').DataTable({
            processing: true,
            serverSide: true,
            searchDelay: 500,
            responsive: false,
            pageLength: 50,
            lengthMenu: [[25, 50, 100, 250, 500], [25, 50, 100, 250, 500]],
            order: [[2, 'asc']],
            createdRow: function(row, data, dataIndex) {
                if (data.kategori_code === 'z_asli') {
                    $(row).addClass('table-row-jurnal-z');
                } else if (data.kategori_code === 'q_acak') {
                    $(row).addClass('table-row-jurnal-q');
                } else if (data.kategori_code === 'a_pot') {
                    $(row).addClass('table-row-jurnal-a');
                }
            },
            language: {
                search: "Cari Data:",
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ baris",
                infoEmpty: "Menampilkan 0 baris",
                emptyTable: "Tidak ada data jurnal transaksi pada rentang tanggal ini.",
                loadingRecords: "Memuat data...",
                processing: '<i class="fas fa-spinner fa-spin mr-2"></i> Memuat data jurnal...',
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Kembali"
                }
            },
            ajax: {
                url: '<?= base_url("keuangan/report_jurnal/data") ?>',
                type: 'POST',
                data: function(d) {
                    d.start_date   = $('#filter-start-date').val();
                    d.end_date     = $('#filter-end-date').val();
                    d.status_lunas = $('#filter-status-lunas').val();
                    d.jenis        = $('#filter-jenis').val();
                    d.kategori     = $('#filter-kategori').val();
                },
                dataSrc: function(json) {
                    if (json && json.status) {
                        $('#summary-total-records').text(json.total_rows || 0);
                        $('#summary-total-debit').text('Rp ' + (json.total_debit_formatted || '0'));
                        $('#summary-total-credit').text('Rp ' + (json.total_kredit_formatted || '0'));
                        $('#tfoot-total-debit').text('Rp ' + (json.total_debit_formatted || '0'));
                        $('#tfoot-total-credit').text('Rp ' + (json.total_kredit_formatted || '0'));

                        let diff = Math.abs((json.total_debit || 0) - (json.total_kredit || 0));
                        if (diff < 0.01) {
                            $('#badge-balance-status')
                                .removeClass('badge-danger')
                                .addClass('badge-success')
                                .html('<i class="fas fa-check-circle mr-1"></i> BALANCE');
                        } else {
                            $('#badge-balance-status')
                                .removeClass('badge-success')
                                .addClass('badge-danger')
                                .html('<i class="fas fa-exclamation-triangle mr-1"></i> SELISIH Rp ' + new Intl.NumberFormat('id-ID').format(diff));
                        }

                        return json.data || [];
                    }
                    return [];
                }
            },
            columns: [
                { 
                    data: 'no', 
                    className: 'text-center font-weight-bold',
                    orderable: false
                },
                { 
                    data: 'ref', 
                    className: 'text-center font-weight-bold text-muted' 
                },
                { 
                    data: 'date', 
                    className: 'text-center' 
                },
                { 
                    data: 'no_bukti_prefix', 
                    className: 'text-center font-weight-bold' 
                },
                { 
                    data: 'no_bukti', 
                    className: 'font-weight-bold text-nowrap',
                    render: function(data, type, row) {
                        let srcTag = row.source_no && row.source_no !== '-' ? 
                            `<span class="badge badge-light border d-block text-muted mt-1 font-weight-normal text-left" style="font-size:10px;" title="Ref Faktur Asal"><i class="fas fa-file-invoice mr-1 text-info"></i>${row.source_no}</span>` : '';
                        return `<span class="text-primary font-weight-bold">${data}</span>${srcTag}`;
                    }
                },
                { 
                    data: 'no_perk', 
                    className: 'text-center font-weight-bold text-dark' 
                },
                { 
                    data: 'desc_akun', 
                    className: 'font-weight-bold' 
                },
                { 
                    data: 'description',
                    render: function(data) {
                        return data || '-';
                    }
                },
                { 
                    data: 'debit', 
                    className: 'text-right font-weight-bold text-success text-nowrap',
                    render: function(data) {
                        let val = parseFloat(data) || 0;
                        return val > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(val) : '-';
                    }
                },
                { 
                    data: 'credit', 
                    className: 'text-right font-weight-bold text-danger text-nowrap',
                    render: function(data) {
                        let val = parseFloat(data) || 0;
                        return val > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(val) : '-';
                    }
                }
            ]
        });
    }

    // Inisialisasi awal
    initOrReloadTable();

    // Event Filter
    $('#btn-apply-filter').on('click', function(e) {
        e.preventDefault();
        initOrReloadTable();
    });

    $('#btn-reset-filter').on('click', function(e) {
        e.preventDefault();
        $('#filter-start-date').val('<?= date("Y-m-01") ?>');
        $('#filter-end-date').val('<?= date("Y-m-d") ?>');
        $('#filter-status-lunas').val('all_paid');
        $('#filter-jenis').val('');
        $('#filter-kategori').val('');
        initOrReloadTable();
    });

    // Event Export Excel
    $('#btn-export-excel').on('click', function(e) {
        e.preventDefault();
        let params = $.param({
            start_date:   $('#filter-start-date').val(),
            end_date:     $('#filter-end-date').val(),
            status_lunas: $('#filter-status-lunas').val(),
            jenis:        $('#filter-jenis').val(),
            kategori:     $('#filter-kategori').val()
        });
        window.location.href = '<?= base_url("keuangan/report_jurnal/export_excel") ?>?' + params;
    });

    // Event Export CSV
    $('#btn-export-csv').on('click', function(e) {
        e.preventDefault();
        let params = $.param({
            start_date:   $('#filter-start-date').val(),
            end_date:     $('#filter-end-date').val(),
            status_lunas: $('#filter-status-lunas').val(),
            jenis:        $('#filter-jenis').val(),
            kategori:     $('#filter-kategori').val()
        });
        window.location.href = '<?= base_url("keuangan/report_jurnal/export_csv") ?>?' + params;
    });

    // Event Print
    $('#btn-print-table').on('click', function(e) {
        e.preventDefault();
        window.print();
    });
});
</script>

