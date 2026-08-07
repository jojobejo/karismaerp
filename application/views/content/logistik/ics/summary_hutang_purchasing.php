<?php
/**
 * View: Dashboard Summary Hutang Purchasing (Per Faktur / Invoice)
 * Menampilkan ringkasi agregasi hutang dagang per nomor invoice/faktur
 *
 * @author KARISMA ERP Development Team
 * @since  2026-08-06
 */
?>
<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0 text-dark font-weight-bold">
                                <i class="fas fa-calculator text-primary mr-2"></i>Summary Hutang Purchasing (Per Faktur)
                            </h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?= base_url('ics/icspo') ?>">Logistik</a></li>
                                <li class="breadcrumb-item"><a href="<?= base_url('ics/lpb_report') ?>">Laporan LPB</a></li>
                                <li class="breadcrumb-item active">Summary Hutang</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">

                    <!-- Stat Info Box Row -->
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gradient-navy elevation-2">
                                <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Faktur/Invoice</span>
                                    <span class="info-box-number" id="boxTotalFaktur">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gradient-success elevation-2">
                                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Hutang Dagang</span>
                                    <span class="info-box-number" id="boxTotalHutang">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gradient-warning text-dark elevation-2">
                                <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total PPN 12%</span>
                                    <span class="info-box-number" id="boxTotalPpn12">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gradient-teal elevation-2">
                                <span class="info-box-icon"><i class="fas fa-tags"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total DPP Nilai Lain</span>
                                    <span class="info-box-number" id="boxTotalDppLain">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Report Card -->
                    <div class="card card-outline card-primary shadow-lg rounded-lg">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center flex-wrap" style="gap: 5px;">
                                <h3 class="card-title text-primary font-weight-bold mb-0 mr-2">
                                    <i class="fas fa-layer-group mr-2"></i>Agregasi Per Invoice / Faktur
                                </h3>
                                <a href="<?= base_url('ics/lpb_report') ?>" class="btn btn-outline-primary btn-sm rounded-pill shadow-sm">
                                    <i class="fas fa-list-alt mr-1"></i> Lihat Laporan Detail LPB
                                </a>
                                <a href="<?= base_url('ics/import_lpb') ?>" class="btn btn-outline-info btn-sm rounded-pill shadow-sm">
                                    <i class="fas fa-file-import mr-1"></i> Import Excel Purchasing
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Filter Data -->
                            <form id="filterSummaryForm" class="bg-light p-3 rounded mb-4 border" onsubmit="return false;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold text-secondary small">
                                        <i class="fas fa-filter text-primary mr-1"></i>Filter Summary Hutang
                                    </span>
                                    <button type="button" id="btnResetSummaryFilter" class="btn btn-outline-secondary btn-xs rounded-pill px-3">
                                        <i class="fas fa-redo mr-1"></i> Reset Filter
                                    </button>
                                </div>

                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-3 col-sm-6 mb-2">
                                        <label class="font-weight-bold text-muted small mb-1"><i class="fas fa-calendar-alt mr-1"></i>Dari Tanggal</label>
                                        <input type="date" class="form-control form-control-sm" id="filterSumDate1" name="date1" value="<?= htmlspecialchars($filters['date1'] ?? '') ?>">
                                    </div>
                                    <div class="form-group col-md-3 col-sm-6 mb-2">
                                        <label class="font-weight-bold text-muted small mb-1"><i class="fas fa-calendar-alt mr-1"></i>Sampai Tanggal</label>
                                        <input type="date" class="form-control form-control-sm" id="filterSumDate2" name="date2" value="<?= htmlspecialchars($filters['date2'] ?? '') ?>">
                                    </div>
                                </div>
                            </form>

                            <!-- DataTables Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover text-nowrap w-100" id="summaryHutangTable">
                                    <thead class="bg-navy text-white text-center">
                                        <tr>
                                            <th style="width: 35px;">#</th>
                                            <th>No. Invoice / Faktur</th>
                                            <th>Tgl Invoice</th>
                                            <th>Kode Supp</th>
                                            <th>Nama Supplier</th>
                                            <th>Daftar PO</th>
                                            <th>Daftar LPB</th>
                                            <th>Daftar SJ</th>
                                            <th class="text-right">Total DPP Nilai Lain</th>
                                            <th class="text-right">Total PPN 12%</th>
                                            <th class="text-right">Jumlah Per Faktur (Hutang)</th>
                                            <th>No Seri FP</th>
                                            <th class="text-center">Status LPB</th>
                                            <th class="text-center">Aging Invoice</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>

        <footer class="main-footer text-sm">
            <strong>&copy; <?= date('Y') ?> KARISMA ERP.</strong> All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Summary Hutang Module</b> v1.0
            </div>
        </footer>
    </div>

    <!-- DataTables JS Script -->
    <script>
    $(document).ready(function () {
        function formatCurrency(val) {
            if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
            return 'Rp ' + parseFloat(val).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        let summaryTable = $('#summaryHutangTable').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            order: [[10, 'desc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            ajax: {
                url: '<?= base_url("ics/summary_hutang/json") ?>',
                type: 'POST',
                data: function (d) {
                    d.date1 = $('#filterSumDate1').val();
                    d.date2 = $('#filterSumDate2').val();
                },
                dataSrc: function (json) {
                    let totalHutang = 0;
                    let totalPpn12 = 0;
                    let totalDppLain = 0;

                    if (json.data && json.data.length > 0) {
                        json.data.forEach(function (row) {
                            totalHutang += parseFloat(row.total_jumlah_hutang || 0);
                            totalPpn12 += parseFloat(row.total_ppn_12 || 0);
                            totalDppLain += parseFloat(row.total_dpp_nilai_lain || 0);
                        });
                    }

                    $('#boxTotalFaktur').text(json.recordsTotal || 0);
                    $('#boxTotalHutang').text(formatCurrency(totalHutang));
                    $('#boxTotalPpn12').text(formatCurrency(totalPpn12));
                    $('#boxTotalDppLain').text(formatCurrency(totalDppLain));

                    return json.data;
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                    className: 'text-center font-weight-bold'
                },
                {
                    data: 'no_invoice',
                    render: function (data) {
                        return '<code class="font-weight-bold">' + (data || '-') + '</code>';
                    }
                },
                { data: 'tanggal_invoice', className: 'text-center' },
                { data: 'kd_supplier' },
                { data: 'nama_supplier', className: 'font-weight-bold' },
                { data: 'daftar_po' },
                { data: 'daftar_lpb' },
                { data: 'daftar_sj' },
                {
                    data: 'total_dpp_nilai_lain',
                    className: 'text-right',
                    render: function (data) { return formatCurrency(data); }
                },
                {
                    data: 'total_ppn_12',
                    className: 'text-right text-info',
                    render: function (data) { return formatCurrency(data); }
                },
                {
                    data: 'total_jumlah_hutang',
                    className: 'text-right font-weight-bold text-success',
                    render: function (data) { return formatCurrency(data); }
                },
                { data: 'no_seri_fp' },
                {
                    data: 'status_lpb_text',
                    className: 'text-center',
                    render: function (data) {
                        let st = String(data).toUpperCase();
                        if (st === 'POSTED') return '<span class="badge badge-success px-2">POSTED</span>';
                        if (st === 'VOID') return '<span class="badge badge-danger px-2">VOID</span>';
                        return '<span class="badge badge-warning text-dark px-2">UNPOST</span>';
                    }
                },
                {
                    data: 'aging_invoice_category',
                    className: 'text-center',
                    render: function (data) {
                        let bg = 'badge-secondary';
                        if (data === '0 - 15 Hari') bg = 'badge-success';
                        else if (data === '16 - 30 Hari') bg = 'badge-info';
                        else if (data === '31 - 45 Hari') bg = 'badge-warning text-dark';
                        else if (data === '46 - 60 Hari' || data === '> 60 Hari') bg = 'badge-danger';
                        return '<span class="badge ' + bg + ' px-2">' + (data || '-') + '</span>';
                    }
                }
            ]
        });

        $('#filterSumDate1, #filterSumDate2').on('change', function () {
            summaryTable.ajax.reload();
        });

        $('#btnResetSummaryFilter').on('click', function () {
            $('#filterSumDate1').val('');
            $('#filterSumDate2').val('');
            summaryTable.ajax.reload();
        });
    });
    </script>
</body>
