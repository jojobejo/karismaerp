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
                                <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>Laporan Digital Purchasing & LPB
                            </h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?= base_url('ics/icspo') ?>">Logistik</a></li>
                                <li class="breadcrumb-item active">Laporan Digital Purchasing</li>
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
                                <span class="info-box-icon"><i class="fas fa-truck-loading"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total LPB Digital</span>
                                    <span class="info-box-number" id="boxTotalLpb">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gradient-teal elevation-2">
                                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nilai Total Pembelian</span>
                                    <span class="info-box-number" id="boxNilaiTotal">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gradient-warning text-dark elevation-2">
                                <span class="info-box-icon"><i class="fas fa-file-signature"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">FP Belum Diterima</span>
                                    <span class="info-box-number" id="boxPendingFp">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-gradient-indigo elevation-2">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Rata-rata Lead Time PO</span>
                                    <span class="info-box-number" id="boxLeadTime">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Report Card -->
                    <div class="card card-outline card-primary shadow-lg rounded-lg">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <h3 class="card-title text-primary font-weight-bold mb-0">
                                <i class="fas fa-table mr-2"></i> Matriks Data Pembelian Harian & Aging LPB
                            </h3>
                            <div>
                                <a href="<?= base_url('ics/lpb_manual') ?>" class="btn btn-primary btn-sm rounded-pill shadow-sm">
                                    <i class="fas fa-plus-circle mr-1"></i> Input LPB Manual
                                </a>
                                <a href="<?= base_url('ics/icspo') ?>" class="btn btn-outline-secondary btn-sm rounded-pill ml-1">
                                    <i class="fas fa-arrow-left mr-1"></i> Data LPB Logistik
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Filter Section -->
                            <form id="filterForm" class="bg-light p-3 rounded mb-4 border">
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-2 mb-2">
                                        <label class="font-weight-bold text-muted small"><i class="fas fa-calendar-alt mr-1"></i>Tanggal LPB Dari</label>
                                        <input type="date" class="form-control form-control-sm" id="filterDate1" name="date1" value="<?= htmlspecialchars($filters['date1'] ?? '') ?>">
                                    </div>
                                    <div class="form-group col-md-2 mb-2">
                                        <label class="font-weight-bold text-muted small"><i class="fas fa-calendar-alt mr-1"></i>Sampai</label>
                                        <input type="date" class="form-control form-control-sm" id="filterDate2" name="date2" value="<?= htmlspecialchars($filters['date2'] ?? '') ?>">
                                    </div>
                                    <div class="form-group col-md-3 mb-2">
                                        <label class="font-weight-bold text-muted small"><i class="fas fa-layer-group mr-1"></i>Sumber Transaksi</label>
                                        <select class="form-control form-control-sm" id="filterSource" name="source">
                                            <option value="all">Semua Sumber Transaksi</option>
                                            <option value="manual">LPB Manual Purchasing</option>
                                            <option value="logistik">LPB Logistik dari PO</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3 mb-2">
                                        <label class="font-weight-bold text-muted small"><i class="fas fa-hourglass-half mr-1"></i>Aging Faktur Pajak</label>
                                        <select class="form-control form-control-sm" id="filterAgingFp" name="aging_fp">
                                            <option value="all">Semua Status Aging FP</option>
                                            <option value="belum">Belum Diterima</option>
                                            <option value="0 - 15 Hari">0 - 15 Hari</option>
                                            <option value="16 - 30 Hari">16 - 30 Hari</option>
                                            <option value="31 - 45 Hari">31 - 45 Hari</option>
                                            <option value="46 - 60 Hari">46 - 60 Hari</option>
                                            <option value="> 60 Hari">> 60 Hari</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-2 mb-2 d-flex">
                                        <button type="submit" class="btn btn-primary btn-sm btn-block mr-1 font-weight-bold">
                                            <i class="fas fa-search mr-1"></i> Filter
                                        </button>
                                        <button type="button" id="btnResetFilter" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Modern Multi-Level Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover text-nowrap w-100" id="lpbReportDigitalTable">
                                    <thead class="bg-navy text-white text-center">
                                        <tr>
                                            <th rowspan="2" class="align-middle">#</th>
                                            <th colspan="4" class="bg-primary text-white border-bottom-0">Data Purchase Order (PO)</th>
                                            <th colspan="5" class="bg-secondary text-white border-bottom-0">Data LPB & Surat Jalan</th>
                                            <th colspan="4" class="bg-dark text-white border-bottom-0">Invoice & Pembayaran</th>
                                            <th colspan="2" class="bg-info text-white border-bottom-0">Data Supplier</th>
                                            <th colspan="10" class="bg-teal text-white border-bottom-0">Data Barang & Batch</th>
                                            <th colspan="11" class="bg-indigo text-white border-bottom-0">Finansial, Diskon & Pajak (Rp)</th>
                                            <th colspan="5" class="bg-warning text-dark border-bottom-0">Faktur Pajak</th>
                                            <th colspan="4" class="bg-purple text-white border-bottom-0">Lead Time & Aging</th>
                                        </tr>
                                        <tr class="bg-light text-dark text-center small font-weight-bold">
                                            <!-- PO -->
                                            <th>Tgl PO</th>
                                            <th>No PO</th>
                                            <th>Tgl Perubahan</th>
                                            <th>TOP (Hari)</th>
                                            <!-- LPB -->
                                            <th>Tgl LPB</th>
                                            <th>No LPB</th>
                                            <th>Jenis LPB</th>
                                            <th>Sumber</th>
                                            <th>No SJ</th>
                                            <!-- Invoice -->
                                            <th>No Invoice</th>
                                            <th>Tgl Invoice</th>
                                            <th>Tgl Perubahan</th>
                                            <th>Tgl Riil</th>
                                            <!-- Supplier -->
                                            <th>Kode Supp</th>
                                            <th>Nama Supplier</th>
                                            <!-- Barang -->
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Produsen</th>
                                            <th>Spesifikasi</th>
                                            <th>Golongan</th>
                                            <th>Kelompok</th>
                                            <th>Komposisi</th>
                                            <th>Grup</th>
                                            <th>No Batch</th>
                                            <th>Exp Date</th>
                                            <!-- Finansial -->
                                            <th>Qty</th>
                                            <th>Harga Satuan</th>
                                            <th>Total Harga</th>
                                            <th>Sales Disc</th>
                                            <th>CBD</th>
                                            <th>FOC</th>
                                            <th>Insentif CN</th>
                                            <th>DPP</th>
                                            <th>PPN 11%</th>
                                            <th>PPN 12%</th>
                                            <th>DPP Lain</th>
                                            <!-- FP -->
                                            <th>No Seri FP</th>
                                            <th>Tgl FP</th>
                                            <th>Tgl Terima</th>
                                            <th>Tgl Input</th>
                                            <th>SPT Masa</th>
                                            <!-- Lead Time & Aging -->
                                            <th>LT PO-LPB</th>
                                            <th>LT FP-Hari Ini</th>
                                            <th>Aging FP</th>
                                            <th>Aging Invoice</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2026 <a href="https://kiu.co.id">PT. KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block"><b>ERP Digital Purchasing</b> v2.0</div>
        </footer>
    </div>

    <!-- DataTables & Custom Styling -->
    <style>
        .table-responsive {
            max-height: 70vh;
            overflow-y: auto;
        }
        #lpbReportDigitalTable thead th {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .badge-aging {
            font-size: 85%;
            padding: 5px 8px;
            border-radius: 12px;
        }
    </style>

    <script>
        $(document).ready(function() {
            // Helper Formatter
            function formatCurrency(val) {
                var num = parseFloat(val) || 0;
                return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            }

            function formatQty(val) {
                var num = parseFloat(val) || 0;
                return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            }

            function getAgingBadge(category) {
                if (!category || category === 'Belum Diterima') {
                    return '<span class="badge badge-secondary badge-aging"><i class="fas fa-clock mr-1"></i>Belum Diterima</span>';
                } else if (category === '0 - 15 Hari') {
                    return '<span class="badge badge-success badge-aging"><i class="fas fa-check-circle mr-1"></i>' + category + '</span>';
                } else if (category === '16 - 30 Hari') {
                    return '<span class="badge badge-info badge-aging">' + category + '</span>';
                } else if (category === '31 - 45 Hari') {
                    return '<span class="badge badge-warning badge-aging">' + category + '</span>';
                } else if (category === '46 - 60 Hari') {
                    return '<span class="badge badge-danger badge-aging">' + category + '</span>';
                } else {
                    return '<span class="badge badge-dark badge-aging">' + category + '</span>';
                }
            }

            $.fn.dataTable.ext.errMode = 'none';

            // DataTables Initialization
            var table = $('#lpbReportDigitalTable')
                .on('error.dt', function(e, settings, techNote, message) {
                    console.warn('DataTables AJAX info:', message);
                })
                .DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                scrollX: true,
                ajax: {
                    url: '<?= base_url("ics/lpb_report/json") ?>',
                    type: 'POST',
                    data: function(d) {
                        d.date1 = $('#filterDate1').val();
                        d.date2 = $('#filterDate2').val();
                        d.source = $('#filterSource').val();
                        d.aging_fp = $('#filterAgingFp').val();
                    },
                    dataSrc: function(json) {
                        // Summary Stats Calculation
                        if (json && json.data) {
                            var totalRecords = json.recordsTotal || 0;
                            var grandTotal = 0;
                            var pendingFp = 0;
                            var sumLtPo = 0;
                            var countLtPo = 0;

                            json.data.forEach(function(row) {
                                grandTotal += parseFloat(row.total_harga) || 0;
                                if (!row.tgl_terima_fp || row.aging_fp_category === 'Belum Diterima') {
                                    pendingFp++;
                                }
                                if (row.lead_time_po_lpb !== null && row.lead_time_po_lpb !== undefined) {
                                    sumLtPo += parseInt(row.lead_time_po_lpb);
                                    countLtPo++;
                                }
                            });

                            $('#boxTotalLpb').text(totalRecords.toLocaleString('id-ID'));
                            $('#boxNilaiTotal').text(formatCurrency(grandTotal));
                            $('#boxPendingFp').text(pendingFp.toLocaleString('id-ID'));
                            var avgLt = countLtPo > 0 ? (sumLtPo / countLtPo).toFixed(1) + ' Hari' : '-';
                            $('#boxLeadTime').text(avgLt);
                        }
                        return json.data;
                    }
                },
                columns: [
                    { 
                        data: null, 
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }, 
                        className: 'text-center' 
                    },
                    // PO Data
                    { data: 'tgl_po', defaultContent: '-' },
                    { data: 'no_po', defaultContent: '-' },
                    { data: 'tgl_perubahan_po', defaultContent: '-' },
                    { data: 'top_days', render: function(val) { return val ? val + ' Hari' : '-'; }, className: 'text-center' },
                    // LPB Data
                    { data: 'tgl_lpb', defaultContent: '-' },
                    { data: 'nomor_lpb', defaultContent: '-' },
                    { data: 'jenis_lpb', defaultContent: '-' },
                    { 
                        data: 'source_type', 
                        render: function(val) {
                            return val === 'MANUAL' 
                                ? '<span class="badge badge-warning">MANUAL</span>' 
                                : '<span class="badge badge-info">LOGISTIK</span>';
                        }, 
                        className: 'text-center' 
                    },
                    { data: 'nosj', defaultContent: '-' },
                    // Invoice Data
                    { data: 'no_invoice', defaultContent: '-' },
                    { data: 'tanggal_invoice', defaultContent: '-' },
                    { data: 'tgl_perubahan_invoice', defaultContent: '-' },
                    { data: 'tgl_riil_invoice', defaultContent: '-' },
                    // Supplier Data
                    { data: 'kd_supplier', defaultContent: '-' },
                    { data: 'nama_supplier', defaultContent: '-' },
                    // Barang & Batch Data
                    { data: 'kd_barang', defaultContent: '-' },
                    { data: 'nama_barang', defaultContent: '-' },
                    { data: 'produsen', defaultContent: '-' },
                    { data: 'spesifikasi_merk', defaultContent: '-' },
                    { data: 'golongan', defaultContent: '-' },
                    { data: 'kelompok', defaultContent: '-' },
                    { data: 'komposisi', defaultContent: '-' },
                    { data: 'grup', defaultContent: '-' },
                    { data: 'no_batch', defaultContent: '-' },
                    { data: 'exp_date', defaultContent: '-' },
                    // Finansial Data
                    { data: 'qty_diterima', render: formatQty, className: 'text-right' },
                    { data: 'harga_satuan', render: formatCurrency, className: 'text-right' },
                    { data: 'total_harga', render: formatCurrency, className: 'text-right font-weight-bold' },
                    { data: 'sales_disc', render: formatCurrency, className: 'text-right' },
                    { data: 'cbd', render: formatCurrency, className: 'text-right' },
                    { data: 'foc', render: formatCurrency, className: 'text-right' },
                    { data: 'insentif_cn', render: formatCurrency, className: 'text-right' },
                    { data: 'dpp', render: formatCurrency, className: 'text-right' },
                    { data: 'ppn_11', render: formatCurrency, className: 'text-right' },
                    { data: 'ppn_12', render: formatCurrency, className: 'text-right' },
                    { data: 'dpp_nilai_lain', render: formatCurrency, className: 'text-right' },
                    // Faktur Pajak
                    { data: 'no_seri_fp', defaultContent: '-' },
                    { data: 'tgl_fp', defaultContent: '-' },
                    { data: 'tgl_terima_fp', defaultContent: '-' },
                    { data: 'tgl_input_fp', defaultContent: '-' },
                    { data: 'lapor_spt_masa', defaultContent: '-' },
                    // Lead Time & Aging
                    { data: 'lead_time_po_lpb', render: function(val) { return val !== null ? val + ' Hari' : '-'; }, className: 'text-center' },
                    { data: 'lead_time_fp_today', render: function(val) { return val !== null ? val + ' Hari' : '-'; }, className: 'text-center' },
                    { data: 'aging_fp_category', render: getAgingBadge, className: 'text-center' },
                    { data: 'aging_invoice_category', render: getAgingBadge, className: 'text-center' }
                ],
                order: [[1, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari No PO, LPB, Barang, FP...",
                    lengthMenu: "Tampilkan _MENU_ baris",
                    zeroRecords: "Tidak ada data Laporan Digital Purchasing ditemukan",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Memuat data...</span></div>'
                }
            });

            // Filter Form Submit
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            // Reset Filter
            $('#btnResetFilter').on('click', function() {
                $('#filterForm')[0].reset();
                table.ajax.reload();
            });
        });
    </script>
