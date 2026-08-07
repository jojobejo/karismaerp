<?php
/**
 * View: Import Excel Purchasing → LPB
 * Halaman untuk upload, validasi, preview, dan proses import data Excel dari Purchasing
 * ke dalam sistem Laporan Penerimaan Barang (LPB) KARISMA ERP.
 *
 * @author  KARISMA ERP Development Team
 * @since   2026-08-06
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
            <!-- Header Konten -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0 text-dark font-weight-bold">
                                <i class="fas fa-file-import text-primary mr-2"></i>Import Excel Purchasing
                            </h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="<?= base_url('ics/icspo') ?>">Logistik</a></li>
                                <li class="breadcrumb-item"><a href="<?= base_url('ics/lpb_report') ?>">Laporan LPB</a></li>
                                <li class="breadcrumb-item active">Import Excel</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">

                    <!-- Notifikasi Flash -->
                    <?php if ($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show elevation-1" role="alert">
                            <i class="fas fa-check-circle mr-2"></i><?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show elevation-1" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i><?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>

                    <!-- ============================================================ -->
                    <!-- TAHAP 1: Upload File Excel                                   -->
                    <!-- ============================================================ -->
                    <div id="sectionUpload">
                        <div class="card card-outline card-primary shadow-lg rounded-lg">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h3 class="card-title text-primary font-weight-bold mb-0">
                                    <i class="fas fa-cloud-upload-alt mr-2"></i>Upload File Excel Purchasing
                                </h3>
                                <div class="d-flex" style="gap: 5px;">
                                    <a href="<?= base_url('ics/import_lpb/download_template') ?>" class="btn btn-outline-info btn-sm rounded-pill">
                                        <i class="fas fa-download mr-1"></i> Download Template
                                    </a>
                                    <a href="<?= base_url('ics/lpb_report') ?>" class="btn btn-outline-secondary btn-sm rounded-pill">
                                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Panduan Upload -->
                                <div class="callout callout-info bg-light border-left-info mb-4">
                                    <h5 class="font-weight-bold text-info"><i class="fas fa-info-circle mr-1"></i> Panduan Upload</h5>
                                    <ul class="mb-0 small text-muted">
                                        <li>Format file yang didukung: <strong>.xlsx</strong> atau <strong>.xls</strong></li>
                                        <li>Ukuran maksimal: <strong>5 MB</strong></li>
                                        <li>Pastikan kolom sesuai template: <code>no_lpb</code>, <code>no_po</code>, <code>tgl_lpb</code>, <code>id_supplier</code>, <code>nama_supplier</code>, <code>no_sj_supplier</code>, <code>no_invoice</code>, <code>no_faktur_pajak</code>, <code>dpp</code>, <code>ppn</code>, <code>grand_total</code>, <code>status_lpb</code></li>
                                        <li>Data akan divalidasi otomatis sebelum diproses (format, referensi master, kalkulasi keuangan)</li>
                                    </ul>
                                </div>

                                <!-- Area Drag & Drop -->
                                <div id="dropZone" class="border-dashed rounded-lg p-5 text-center position-relative" 
                                     style="border: 3px dashed #c8d6e5; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); cursor: pointer; transition: all 0.3s ease;">
                                    <div id="dropZoneContent">
                                        <i class="fas fa-file-excel fa-4x text-success mb-3" style="opacity: 0.6;"></i>
                                        <h4 class="text-muted font-weight-bold">Drag & Drop File Excel di Sini</h4>
                                        <p class="text-muted mb-3">atau klik untuk memilih file</p>
                                        <button type="button" id="btnSelectFile" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                                            <i class="fas fa-folder-open mr-2"></i>Pilih File Excel
                                        </button>
                                    </div>
                                    <input type="file" id="fileInput" name="file_excel" accept=".xlsx,.xls" style="display:none;">
                                </div>

                                <!-- Info File Terpilih -->
                                <div id="fileInfo" class="mt-3 d-none">
                                    <div class="alert alert-light border d-flex justify-content-between align-items-center shadow-sm">
                                        <div>
                                            <i class="fas fa-file-excel text-success fa-lg mr-2"></i>
                                            <strong id="fileName">-</strong>
                                            <span class="text-muted ml-2" id="fileSize">-</span>
                                        </div>
                                        <div>
                                            <button type="button" id="btnRemoveFile" class="btn btn-outline-danger btn-sm rounded-pill mr-2">
                                                <i class="fas fa-times mr-1"></i>Hapus
                                            </button>
                                            <button type="button" id="btnUpload" class="btn btn-success btn-sm rounded-pill px-4 shadow">
                                                <i class="fas fa-upload mr-1"></i>Upload & Validasi
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress Bar Upload -->
                                <div id="uploadProgress" class="mt-3 d-none">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small class="font-weight-bold text-primary" id="progressLabel">Mengupload file...</small>
                                        <small class="font-weight-bold" id="progressPercent">0%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                             role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAHAP 2: Preview & Validasi Hasil                            -->
                    <!-- ============================================================ -->
                    <div id="sectionPreview" class="d-none">
                        <!-- Summary Cards -->
                        <div class="row mb-3" id="summaryCards">
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box bg-gradient-navy elevation-2">
                                    <span class="info-box-icon"><i class="fas fa-list-ol"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Baris Data</span>
                                        <span class="info-box-number" id="summaryTotal">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box bg-gradient-success elevation-2">
                                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Data Valid</span>
                                        <span class="info-box-number" id="summaryValid">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box bg-gradient-warning text-dark elevation-2">
                                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Data Warning</span>
                                        <span class="info-box-number" id="summaryWarning">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="info-box bg-gradient-danger elevation-2">
                                    <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Data Error</span>
                                        <span class="info-box-number" id="summaryError">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Preview Data -->
                        <div class="card card-outline card-info shadow-lg rounded-lg">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h3 class="card-title text-info font-weight-bold mb-0">
                                    <i class="fas fa-search-plus mr-2"></i>Preview & Validasi Data
                                </h3>
                                <div class="d-flex" style="gap: 5px;">
                                    <!-- Filter Status Validasi -->
                                    <div class="btn-group btn-group-sm" role="group" id="filterValidation">
                                        <button type="button" class="btn btn-outline-dark active rounded-pill-left" data-filter="all">
                                            <i class="fas fa-globe mr-1"></i>Semua
                                        </button>
                                        <button type="button" class="btn btn-outline-success" data-filter="valid">
                                            <i class="fas fa-check mr-1"></i>Valid
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" data-filter="warning">
                                            <i class="fas fa-exclamation mr-1"></i>Warning
                                        </button>
                                        <button type="button" class="btn btn-outline-danger rounded-pill-right" data-filter="error">
                                            <i class="fas fa-times mr-1"></i>Error
                                        </button>
                                    </div>
                                    <button type="button" id="btnBackUpload" class="btn btn-outline-secondary btn-sm rounded-pill">
                                        <i class="fas fa-redo mr-1"></i>Upload Ulang
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="tblPreview" class="table table-hover table-bordered table-sm mb-0" style="font-size: 12px;">
                                        <thead class="bg-gradient-navy text-white">
                                            <tr>
                                                <th class="text-center" style="width:35px;">#</th>
                                                <th class="text-center" style="width:50px;">Status</th>
                                                <th>No. LPB</th>
                                                <th>No. PO</th>
                                                <th class="text-center">Tgl LPB</th>
                                                <th>ID Supplier</th>
                                                <th>Nama Supplier</th>
                                                <th>No. Surat Jalan</th>
                                                <th>No. Invoice</th>
                                                <th>No. Faktur Pajak</th>
                                                <th class="text-right">DPP</th>
                                                <th class="text-right">PPN</th>
                                                <th class="text-right">Grand Total</th>
                                                <th class="text-center">Status LPB</th>
                                                <th>Keterangan Validasi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewBody">
                                            <!-- Diisi via JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Hanya data berstatus <span class="badge badge-success">Valid</span> dan 
                                    <span class="badge badge-warning text-dark">Warning</span> yang akan diproses.
                                    Data <span class="badge badge-danger">Error</span> akan dilewati.
                                </div>
                                <button type="button" id="btnProcessImport" class="btn btn-primary btn-lg rounded-pill px-5 shadow" disabled>
                                    <i class="fas fa-cogs mr-2"></i>Proses Import <span id="processCount" class="badge badge-light ml-1">0</span> data
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- TAHAP 3: Hasil Import                                        -->
                    <!-- ============================================================ -->
                    <div id="sectionResult" class="d-none">
                        <div class="card card-outline card-success shadow-lg rounded-lg">
                            <div class="card-header bg-white py-3">
                                <h3 class="card-title text-success font-weight-bold mb-0">
                                    <i class="fas fa-check-double mr-2"></i>Hasil Proses Import
                                </h3>
                            </div>
                            <div class="card-body text-center py-5">
                                <div id="resultIcon" class="mb-4">
                                    <i class="fas fa-check-circle fa-5x text-success" style="animation: pulse 1.5s infinite;"></i>
                                </div>
                                <h3 class="font-weight-bold text-dark mb-3" id="resultTitle">Import Berhasil!</h3>
                                <p class="text-muted mb-4" id="resultMessage">-</p>

                                <div class="row justify-content-center mb-4">
                                    <div class="col-md-3">
                                        <div class="card bg-gradient-success text-white shadow">
                                            <div class="card-body text-center py-3">
                                                <h2 class="font-weight-bold mb-0" id="resultImported">0</h2>
                                                <small>Data Baru Diimport</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-gradient-info text-white shadow">
                                            <div class="card-body text-center py-3">
                                                <h2 class="font-weight-bold mb-0" id="resultUpdated">0</h2>
                                                <small>Data Diperbarui</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-gradient-secondary text-white shadow">
                                            <div class="card-body text-center py-3">
                                                <h2 class="font-weight-bold mb-0" id="resultSkipped">0</h2>
                                                <small>Data Dilewati</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center" style="gap: 10px;">
                                    <a href="<?= base_url('ics/lpb_report') ?>" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                                        <i class="fas fa-table mr-2"></i>Lihat Laporan LPB
                                    </a>
                                    <button type="button" id="btnImportLagi" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                                        <i class="fas fa-redo mr-2"></i>Import Lagi
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer text-sm">
            <strong>&copy; <?= date('Y') ?> KARISMA ERP.</strong> All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Import Purchasing Module</b> v1.0
            </div>
        </footer>
    </div>

    <!-- ============================================================ -->
    <!-- CUSTOM CSS                                                   -->
    <!-- ============================================================ -->
    <style>
        /* Animasi pulse untuk ikon sukses */
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Border dashed untuk drop zone */
        .border-dashed {
            border-style: dashed !important;
        }

        /* Efek hover pada drop zone */
        #dropZone:hover, #dropZone.drag-over {
            border-color: #007bff !important;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
            transform: scale(1.01);
        }

        #dropZone.drag-over {
            box-shadow: 0 0 20px rgba(0, 123, 255, 0.3);
        }

        /* Status badge pada baris tabel validasi */
        .status-valid { background-color: #d4edda !important; }
        .status-warning { background-color: #fff3cd !important; }
        .status-error { background-color: #f8d7da !important; }

        /* Rounded pill kiri/kanan untuk button group */
        .rounded-pill-left {
            border-radius: 50rem 0 0 50rem !important;
        }
        .rounded-pill-right {
            border-radius: 0 50rem 50rem 0 !important;
        }

        /* Animasi fade-in untuk section */
        .fade-in {
            animation: fadeIn 0.4s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Keterangan validasi pada tabel */
        .validation-msg {
            font-size: 11px;
            line-height: 1.4;
        }
        .validation-msg .badge {
            font-size: 10px;
            font-weight: 500;
        }

        /* Callout kustom border kiri */
        .border-left-info {
            border-left: 4px solid #17a2b8 !important;
        }

        /* Scroll horizontal tabel */
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        .table-responsive thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }
    </style>

    <!-- ============================================================ -->
    <!-- JAVASCRIPT                                                   -->
    <!-- ============================================================ -->
    <script>
    $(document).ready(function () {
        // Variabel global untuk menyimpan data parsing hasil upload
        let parsedData = [];
        let selectedFile = null;

        // ===========================================================
        // Fungsi Utilitas
        // ===========================================================

        /**
         * Format angka ke format Rupiah Indonesia
         * @param {number} amount - Angka yang akan diformat
         * @returns {string} Format Rp. X.XXX.XXX
         */
        function formatCurrency(amount) {
            if (!amount && amount !== 0) return '-';
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        /**
         * Mendapatkan class badge berdasarkan status validasi
         * @param {string} status - Status validasi (valid/warning/error)
         * @returns {string} HTML badge
         */
        function getStatusBadge(status) {
            switch (status) {
                case 'valid':
                    return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Valid</span>';
                case 'warning':
                    return '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i>Warning</span>';
                case 'error':
                    return '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Error</span>';
                default:
                    return '<span class="badge badge-secondary px-2 py-1">Unknown</span>';
            }
        }

        /**
         * Mendapatkan badge status LPB
         * @param {string} status - Status LPB (UNPOST/POSTED/VOID)
         * @returns {string} HTML badge
         */
        function getStatusLpbBadge(status) {
            switch (String(status).toUpperCase()) {
                case 'POSTED':
                    return '<span class="badge badge-success px-2">POSTED</span>';
                case 'UNPOST':
                    return '<span class="badge badge-warning text-dark px-2">UNPOST</span>';
                case 'VOID':
                    return '<span class="badge badge-danger px-2">VOID</span>';
                default:
                    return '<span class="badge badge-secondary px-2">' + status + '</span>';
            }
        }

        /**
         * Format keterangan validasi dari array messages
         * @param {Array} messages - Array pesan validasi
         * @param {string} status - Status validasi baris
         * @returns {string} HTML list keterangan
         */
        function formatValidationMessages(messages, status) {
            if (!messages || messages.length === 0) {
                return '<span class="text-success small"><i class="fas fa-check mr-1"></i>Semua validasi lolos</span>';
            }
            let html = '<div class="validation-msg">';
            messages.forEach(function (msg) {
                let badgeClass = 'badge-secondary';
                let icon = 'info-circle';
                if (msg.type === 'error') { badgeClass = 'badge-danger'; icon = 'times-circle'; }
                else if (msg.type === 'warning') { badgeClass = 'badge-warning text-dark'; icon = 'exclamation-triangle'; }
                else if (msg.type === 'info') { badgeClass = 'badge-info'; icon = 'info-circle'; }

                html += '<div class="mb-1"><span class="badge ' + badgeClass + ' mr-1"><i class="fas fa-' + icon + '"></i></span>' + msg.text + '</div>';
            });
            html += '</div>';
            return html;
        }

        // ===========================================================
        // Event Handler: Drag & Drop dan Pilih File
        // ===========================================================

        // Klik area drop zone untuk memilih file
        $('#dropZone, #btnSelectFile').on('click', function (e) {
            if (e.target.id !== 'btnSelectFile' && !$(e.target).closest('#btnSelectFile').length) {
                // Klik pada area drop zone (bukan tombol)
            }
            $('#fileInput').trigger('click');
        });

        // Mencegah event default drag
        $('#dropZone').on('dragover dragenter', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });

        $('#dropZone').on('dragleave dragend drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
        });

        // Handle file drop
        $('#dropZone').on('drop', function (e) {
            e.preventDefault();
            let files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });

        // Handle file input change
        $('#fileInput').on('change', function () {
            if (this.files.length > 0) {
                handleFileSelect(this.files[0]);
            }
        });

        /**
         * Menangani file yang dipilih user
         * Validasi tipe dan ukuran file sebelum menampilkan info
         * @param {File} file - Objek file dari input/drop
         */
        function handleFileSelect(file) {
            // Validasi tipe file
            let allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
            let allowedExt = ['.xlsx', '.xls'];
            let fileExt = '.' + file.name.split('.').pop().toLowerCase();

            if (!allowedExt.includes(fileExt)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format File Tidak Didukung',
                    text: 'Hanya file .xlsx atau .xls yang diperbolehkan.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            // Validasi ukuran file (maks 5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran File Terlalu Besar',
                    text: 'Maksimal ukuran file adalah 5 MB.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            selectedFile = file;
            let sizeKB = (file.size / 1024).toFixed(1);
            let sizeLabel = sizeKB > 1024 ? (file.size / 1024 / 1024).toFixed(2) + ' MB' : sizeKB + ' KB';

            $('#fileName').text(file.name);
            $('#fileSize').text('(' + sizeLabel + ')');
            $('#fileInfo').removeClass('d-none').addClass('fade-in');
        }

        // Hapus file yang sudah dipilih
        $('#btnRemoveFile').on('click', function () {
            selectedFile = null;
            $('#fileInput').val('');
            $('#fileInfo').addClass('d-none');
        });

        // ===========================================================
        // Event Handler: Upload & Validasi
        // ===========================================================
        $('#btnUpload').on('click', function () {
            if (!selectedFile) {
                Swal.fire({ icon: 'warning', title: 'Belum ada file!', text: 'Silakan pilih file Excel terlebih dahulu.' });
                return;
            }

            let formData = new FormData();
            formData.append('file_excel', selectedFile);

            // Tampilkan progress bar
            $('#uploadProgress').removeClass('d-none').addClass('fade-in');
            $('#btnUpload').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Mengupload...');

            $.ajax({
                url: '<?= base_url("ics/import_lpb/upload") ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    let xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            let percent = Math.round((e.loaded / e.total) * 100);
                            $('#progressBar').css('width', percent + '%');
                            $('#progressPercent').text(percent + '%');
                            if (percent >= 100) {
                                $('#progressLabel').text('Memvalidasi data...');
                            }
                        }
                    });
                    return xhr;
                },
                success: function (response) {
                    $('#uploadProgress').addClass('d-none');
                    $('#btnUpload').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Upload & Validasi');

                    if (response.status === true) {
                        parsedData = response.data;
                        renderPreview(response.data, response.summary);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Gagal',
                            html: response.message || 'Terjadi kesalahan saat memproses file.',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                },
                error: function (xhr) {
                    $('#uploadProgress').addClass('d-none');
                    $('#btnUpload').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Upload & Validasi');
                    let msg = 'Terjadi kesalahan server.';
                    try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                    Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#dc3545' });
                }
            });
        });

        // ===========================================================
        // Fungsi Render Preview Tabel Validasi
        // ===========================================================

        /**
         * Merender tabel preview dan summary cards dari data hasil parsing
         * @param {Array} data - Array baris data yang sudah divalidasi
         * @param {Object} summary - Objek ringkasan {total, valid, warning, error}
         */
        function renderPreview(data, summary) {
            // Sembunyikan section upload, tampilkan preview
            $('#sectionUpload').addClass('d-none');
            $('#sectionPreview').removeClass('d-none').addClass('fade-in');
            $('#sectionResult').addClass('d-none');

            // Update summary cards
            $('#summaryTotal').text(summary.total || 0);
            $('#summaryValid').text(summary.valid || 0);
            $('#summaryWarning').text(summary.warning || 0);
            $('#summaryError').text(summary.error || 0);

            // Render baris tabel
            renderTableRows(data, 'all');

            // Update tombol proses (hanya data valid + warning yang diproses)
            let processable = (summary.valid || 0) + (summary.warning || 0);
            $('#processCount').text(processable);
            $('#btnProcessImport').prop('disabled', processable === 0);
        }

        /**
         * Merender baris tabel berdasarkan filter status
         * @param {Array} data - Array data
         * @param {string} filter - Filter: all/valid/warning/error
         */
        function renderTableRows(data, filter) {
            let tbody = $('#previewBody');
            tbody.empty();

            let filtered = data;
            if (filter !== 'all') {
                filtered = data.filter(function (row) { return row.validation_status === filter; });
            }

            if (filtered.length === 0) {
                tbody.append('<tr><td colspan="15" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Tidak ada data dengan status "' + filter + '"</td></tr>');
                return;
            }

            filtered.forEach(function (row, idx) {
                let rowClass = 'status-' + row.validation_status;
                let html = '<tr class="' + rowClass + '">';
                html += '<td class="text-center font-weight-bold">' + (row.row_number || (idx + 1)) + '</td>';
                html += '<td class="text-center">' + getStatusBadge(row.validation_status) + '</td>';
                html += '<td><code>' + (row.no_lpb || '-') + '</code></td>';
                html += '<td><code>' + (row.no_po || '-') + '</code></td>';
                html += '<td class="text-center text-nowrap">' + (row.tgl_lpb || '-') + '</td>';
                html += '<td>' + (row.id_supplier || '-') + '</td>';
                html += '<td>' + (row.nama_supplier || '-') + '</td>';
                html += '<td>' + (row.no_sj_supplier || '-') + '</td>';
                html += '<td>' + (row.no_invoice || '<span class="text-muted font-italic">-</span>') + '</td>';
                html += '<td style="font-size:11px;">' + (row.no_faktur_pajak || '<span class="text-muted font-italic">-</span>') + '</td>';
                html += '<td class="text-right text-nowrap">' + formatCurrency(row.dpp) + '</td>';
                html += '<td class="text-right text-nowrap">' + formatCurrency(row.ppn) + '</td>';
                html += '<td class="text-right text-nowrap font-weight-bold">' + formatCurrency(row.grand_total) + '</td>';
                html += '<td class="text-center">' + getStatusLpbBadge(row.status_lpb) + '</td>';
                html += '<td>' + formatValidationMessages(row.messages, row.validation_status) + '</td>';
                html += '</tr>';
                tbody.append(html);
            });
        }

        // Filter validasi status di tabel preview
        $('#filterValidation').on('click', 'button', function () {
            $('#filterValidation button').removeClass('active');
            $(this).addClass('active');
            let filter = $(this).data('filter');
            renderTableRows(parsedData, filter);
        });

        // ===========================================================
        // Event Handler: Proses Import
        // ===========================================================
        $('#btnProcessImport').on('click', function () {
            // Hanya kirim data valid dan warning
            let processableData = parsedData.filter(function (row) {
                return row.validation_status === 'valid' || row.validation_status === 'warning';
            });

            if (processableData.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Tidak Ada Data', text: 'Tidak ada data yang dapat diproses.' });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Proses Import',
                html: '<div class="text-left">' +
                    '<p>Anda akan memproses <strong>' + processableData.length + '</strong> baris data LPB.</p>' +
                    '<ul class="small text-muted">' +
                    '<li>Data LPB yang sudah ada di database akan <strong>diperbarui</strong> (update administrasi)</li>' +
                    '<li>Data LPB baru akan <strong>ditambahkan</strong> ke database</li>' +
                    '<li>Semua perubahan dicatat di audit log</li>' +
                    '</ul></div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-cogs mr-1"></i> Ya, Proses Import',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    executeImport(processableData);
                }
            });
        });

        /**
         * Menjalankan proses import ke server
         * @param {Array} data - Array data yang sudah divalidasi (valid + warning)
         */
        function executeImport(data) {
            // Loading state
            $('#btnProcessImport').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses Import...');

            $.ajax({
                url: '<?= base_url("ics/import_lpb/process") ?>',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ rows: data }),
                success: function (response) {
                    if (response.status === true) {
                        showResult(response);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Gagal',
                            text: response.message || 'Terjadi kesalahan saat proses import.',
                            confirmButtonColor: '#dc3545'
                        });
                        $('#btnProcessImport').prop('disabled', false)
                            .html('<i class="fas fa-cogs mr-2"></i>Proses Import <span class="badge badge-light ml-1">' + data.length + '</span> data');
                    }
                },
                error: function (xhr) {
                    let msg = 'Terjadi kesalahan server.';
                    try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
                    Swal.fire({ icon: 'error', title: 'Error Server', text: msg, confirmButtonColor: '#dc3545' });
                    $('#btnProcessImport').prop('disabled', false)
                        .html('<i class="fas fa-cogs mr-2"></i>Proses Import <span class="badge badge-light ml-1">' + data.length + '</span> data');
                }
            });
        }

        /**
         * Menampilkan hasil proses import
         * @param {Object} response - Response dari server {imported, updated, skipped, message}
         */
        function showResult(response) {
            $('#sectionUpload').addClass('d-none');
            $('#sectionPreview').addClass('d-none');
            $('#sectionResult').removeClass('d-none').addClass('fade-in');

            $('#resultImported').text(response.imported || 0);
            $('#resultUpdated').text(response.updated || 0);
            $('#resultSkipped').text(response.skipped || 0);
            $('#resultMessage').text(response.message || 'Data berhasil diproses ke dalam sistem LPB.');

            let total = (response.imported || 0) + (response.updated || 0);
            if (total > 0) {
                $('#resultTitle').text('Import Berhasil!');
                $('#resultIcon').html('<i class="fas fa-check-circle fa-5x text-success" style="animation: pulse 1.5s infinite;"></i>');
            } else {
                $('#resultTitle').text('Tidak Ada Perubahan');
                $('#resultIcon').html('<i class="fas fa-info-circle fa-5x text-info"></i>');
            }
        }

        // ===========================================================
        // Navigasi Antar Section
        // ===========================================================

        // Tombol kembali ke upload
        $('#btnBackUpload, #btnImportLagi').on('click', function () {
            resetAll();
        });

        /**
         * Reset semua state dan tampilan ke kondisi awal (section upload)
         */
        function resetAll() {
            parsedData = [];
            selectedFile = null;
            $('#fileInput').val('');
            $('#fileInfo').addClass('d-none');
            $('#uploadProgress').addClass('d-none');
            $('#progressBar').css('width', '0%');
            $('#progressPercent').text('0%');
            $('#progressLabel').text('Mengupload file...');

            $('#sectionUpload').removeClass('d-none').addClass('fade-in');
            $('#sectionPreview').addClass('d-none');
            $('#sectionResult').addClass('d-none');

            $('#btnUpload').prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Upload & Validasi');
            $('#btnProcessImport').prop('disabled', true)
                .html('<i class="fas fa-cogs mr-2"></i>Proses Import <span id="processCount" class="badge badge-light ml-1">0</span> data');
        }
    });
    </script>
</body>
