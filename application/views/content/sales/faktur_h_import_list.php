<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark font-weight-bold">
                        <i class="fas fa-file-invoice text-purple mr-2" style="color: #6f42c1;"></i> Faktur H Import (Cetak Nota & Pelunasan)
                    </h1>
                    <small class="text-muted">Modul import data pecahan faktur (Kode H) dari Excel untuk cetak nota fisik Epson LX-310 sekaligus import pelunasan otomatis beserta Jurnal Pembayaran Acak (Q) & Jurnal Kas Masuk (A).</small>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('sales_order/pecah_faktur') ?>">Pecah Faktur</a></li>
                        <li class="breadcrumb-item active">Faktur H Import</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Flashdata Notifikasi -->
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('info')): ?>
                <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-info-circle mr-1"></i> <?= $this->session->flashdata('info') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('warning')): ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle mr-1"></i> <?= $this->session->flashdata('warning') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>

            <!-- Toolbar Aksi Atas -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div class="d-flex align-items-center flex-wrap mb-2 mb-md-0">
                    <button type="button" class="btn btn-purple text-white btn-sm shadow-sm font-weight-bold mr-2 mb-1" style="background-color: #6f42c1; border-color: #6f42c1;" data-toggle="modal" data-target="#modalImportFakturH">
                        <i class="fas fa-file-excel mr-1"></i> Import Excel Faktur H
                    </button>
                    <button type="button" class="btn btn-success btn-sm shadow-sm font-weight-bold mr-2 mb-1" data-toggle="modal" data-target="#modalImportPelunasanFakturH" title="Import file pelunasan berdasarkan Nomor Faktur Z Induk dari Excel / CSV (otomatis mengalokasikan ke Faktur H & membuat Jurnal Q/A)">
                        <i class="fas fa-check-double mr-1"></i> Import Pelunasan Faktur Z (Jurnal Q & A)
                    </button>
                    <a href="<?= base_url('sales_order/export_faktur_h_lunas?' . http_build_query($filter ?? [])) ?>" class="btn btn-outline-success btn-sm shadow-sm font-weight-bold mr-2 mb-1" title="Unduh file Excel seluruh Faktur H yang berstatus lunas beserta data Jurnal Q & A">
                        <i class="fas fa-file-excel mr-1"></i> Export Faktur Lunas & Jurnal (.xlsx)
                    </a>
                </div>
                <div>
                    <a href="<?= base_url('sales_order/pecah_faktur') ?>" class="btn btn-outline-info btn-sm shadow-sm font-weight-bold">
                        <i class="fas fa-book mr-1"></i> Lihat Jurnal Pembayaran
                    </a>
                </div>
            </div>

            <!-- Ringkasan Statistik Widget 6 Kartu -->
            <div class="row mb-2">
                <div class="col-6 col-md-2">
                    <div class="info-box bg-white shadow-sm border mb-2">
                        <span class="info-box-icon text-white elevation-1" style="background-color: #6f42c1; width: 50px; font-size: 20px;">
                            <i class="fas fa-file-invoice"></i>
                        </span>
                        <div class="info-box-content p-1 pl-2">
                            <span class="info-box-text text-muted small">Total Faktur H</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 17px; color: #6f42c1;">
                                <?= number_format($stat['total_faktur']) ?> Faktur
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="info-box bg-white shadow-sm border mb-2">
                        <span class="info-box-icon bg-info text-white elevation-1" style="width: 50px; font-size: 20px;">
                            <i class="fas fa-boxes"></i>
                        </span>
                        <div class="info-box-content p-1 pl-2">
                            <span class="info-box-text text-muted small">Total Kuantitas</span>
                            <span class="info-box-number text-info font-weight-bold" style="font-size: 17px;">
                                <?= number_format($stat['total_qty']) ?> pcs
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="info-box bg-white shadow-sm border mb-2">
                        <span class="info-box-icon bg-primary text-white elevation-1" style="width: 50px; font-size: 20px;">
                            <i class="fas fa-money-bill-wave"></i>
                        </span>
                        <div class="info-box-content p-1 pl-2">
                            <span class="info-box-text text-muted small">Total Tagihan (Netto)</span>
                            <span class="info-box-number text-primary font-weight-bold" style="font-size: 15px;">
                                Rp <?= number_format($stat['grand_total'], 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="info-box bg-white shadow-sm border mb-2">
                        <span class="info-box-icon bg-success text-white elevation-1" style="width: 50px; font-size: 20px;">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content p-1 pl-2">
                            <span class="info-box-text text-muted small">Sudah Lunas (<?= number_format($stat['total_lunas'] ?? 0) ?>)</span>
                            <span class="info-box-number text-success font-weight-bold" style="font-size: 15px;">
                                Rp <?= number_format($stat['nominal_lunas'] ?? 0, 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="info-box bg-white shadow-sm border mb-2">
                        <span class="info-box-icon bg-danger text-white elevation-1" style="width: 50px; font-size: 20px;">
                            <i class="fas fa-hourglass-half"></i>
                        </span>
                        <div class="info-box-content p-1 pl-2">
                            <span class="info-box-text text-muted small">Belum Lunas (<?= number_format($stat['total_belum'] ?? 0) ?>)</span>
                            <span class="info-box-number text-danger font-weight-bold" style="font-size: 15px;">
                                Rp <?= number_format($stat['nominal_sisa'] ?? 0, 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="info-box bg-white shadow-sm border mb-2">
                        <span class="info-box-icon bg-warning text-white elevation-1" style="width: 50px; font-size: 20px;">
                            <i class="fas fa-tags"></i>
                        </span>
                        <div class="info-box-content p-1 pl-2">
                            <span class="info-box-text text-muted small">Total Jenis Item</span>
                            <span class="info-box-number text-warning font-weight-bold" style="font-size: 17px;">
                                <?= number_format($stat['total_item']) ?> item
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Filter Tanggal, Status Pelunasan & Pencarian -->
            <div class="card card-outline card-secondary shadow-sm mb-3">
                <div class="card-header py-2">
                    <h3 class="card-title font-weight-bold text-muted small text-uppercase">
                        <i class="fas fa-filter mr-1"></i> Filter Periode & Status Pelunasan Faktur H Import
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body py-2">
                    <form action="<?= base_url('sales_order/faktur_h_import') ?>" method="get" id="formFilterFakturHImport">
                        <div class="row align-items-end">
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Dari Tanggal Faktur</label>
                                <input type="date" name="date1" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($filter['date1'] ?? '') ?>">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Sampai Tanggal Faktur</label>
                                <input type="date" name="date2" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($filter['date2'] ?? '') ?>">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Status Pelunasan</label>
                                <?php $curStBayar = strtolower(trim((string)($filter['status_bayar'] ?? ''))); ?>
                                <select name="status_bayar" class="form-control form-control-sm">
                                    <option value="" <?= $curStBayar === '' ? 'selected' : '' ?>>-- Semua Status --</option>
                                    <option value="lunas" <?= $curStBayar === 'lunas' ? 'selected' : '' ?>>Sudah Lunas (Ada Jurnal Q & A)</option>
                                    <option value="belum_lunas" <?= $curStBayar === 'belum_lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Kata Kunci / No. Faktur / Customer / No. Jurnal</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari No. Faktur H, Customer, Toko, No. Jurnal Q/A..."
                                       value="<?= htmlspecialchars($filter['search'] ?? '') ?>">
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <button type="submit" class="btn btn-primary btn-sm mr-1">
                                    <i class="fas fa-search mr-1"></i> Terapkan
                                </button>
                                <a href="<?= base_url('sales_order/faktur_h_import') ?>" class="btn btn-default btn-sm">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card Tabel Faktur H Import -->
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center flex-wrap mr-auto mb-2 mb-md-0">
                        <button type="button" 
                                id="btnToggleSelectAllFakturHImport" 
                                class="btn btn-outline-secondary btn-sm font-weight-bold mr-2 mb-1 shadow-sm" 
                                title="Pilih / Batalkan pilihan semua faktur H import di halaman ini">
                            <i class="far fa-check-square mr-1"></i> Pilih Semua
                        </button>
                        <button type="button" 
                                id="btnLunasiBatchFakturHImport" 
                                class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2 mb-1" 
                                style="display:none;" 
                                title="Lunasi seluruh faktur H import yang sedang dicentang sekaligus buat Jurnal Acak (Q) & Jurnal (A)">
                            <i class="fas fa-check-double mr-1"></i> Lunasi Terpilih (<span class="countSelectedFakturHImport">0</span>)
                        </button>
                        <button type="button" 
                                id="btnUnpostBatchPelunasanFakturHImport" 
                                class="btn btn-warning btn-sm font-weight-bold shadow-sm mr-2 mb-1" 
                                style="display:none;" 
                                title="Batalkan (Unpost) pelunasan & hapus Jurnal Q/A untuk faktur yang dicentang">
                            <i class="fas fa-undo mr-1"></i> Unpost Pelunasan Terpilih (<span class="countSelectedFakturHImport">0</span>)
                        </button>
                        <button type="button" 
                                id="btnHapusBatchFakturHImport" 
                                class="btn btn-danger btn-sm font-weight-bold shadow-sm mr-2 mb-1" 
                                style="display:none;" 
                                title="Hapus seluruh faktur H import yang sedang dicentang">
                            <i class="fas fa-trash-alt mr-1"></i> Hapus Terpilih (<span id="countSelectedFakturHImport">0</span>)
                        </button>
                        <button type="button" 
                                id="btnExportBatchLunasFakturHImport" 
                                class="btn btn-outline-success btn-sm font-weight-bold shadow-sm mr-2 mb-1" 
                                style="display:none;" 
                                title="Export data Faktur H lunas beserta rincian Jurnal Q & A untuk faktur yang dicentang">
                            <i class="fas fa-file-excel mr-1"></i> Export Lunas Terpilih (<span class="countSelectedFakturHImport">0</span>)
                        </button>
                        <button type="button" 
                                class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2 mb-1" 
                                data-toggle="modal" 
                                data-target="#modalImportPelunasanFakturH">
                            <i class="fas fa-file-upload mr-1"></i> Import Pelunasan (Excel)
                        </button>
                    </div>
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="badge badge-warning p-2 mr-2" style="font-size: 12.5px;">
                            Total: <strong><?= number_format((int)($stat['total_faktur'] ?? 0)) ?></strong> Faktur H Import
                        </span>
                        <a href="<?= base_url('sales_order/export_faktur_h_lunas?' . http_build_query($filter ?? [])) ?>" 
                           class="btn btn-success btn-sm font-weight-bold shadow-sm mr-2 mb-1"
                           title="Export seluruh Faktur H yang berstatus lunas beserta data Jurnal Q & A ke Excel (.xlsx)">
                            <i class="fas fa-file-excel mr-1"></i> Export Lunas & Jurnal
                        </a>
                        <button type="button" 
                                class="btn btn-primary btn-sm font-weight-bold shadow-sm mb-1"
                                id="btnCetakSemuaFakturHImport"
                                title="Cetak Faktur H Import Sekaligus ke Printer Dot-Matrix Epson LX-310 (21 cm x 14 cm)">
                            <i class="fas fa-print mr-1"></i> Cetak Faktur (LX-310)
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-nowrap" id="tableFakturHImportPage">
                            <thead class="thead-light">
                                <tr>
                                    <th width="35" class="text-center no-sort">
                                        <input type="checkbox" id="checkAllFakturHImport" title="Centang / Hilangkan centang semua pada halaman ini" style="cursor:pointer; width:16px; height:16px;">
                                    </th>
                                    <th width="40" class="text-center">No</th>
                                    <th>No. Faktur H</th>
                                    <th>Faktur Z Induk</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Customer / Kontak Person</th>
                                    <th class="text-center">Total Item</th>
                                    <th class="text-right">Nominal Faktur</th>
                                    <th class="text-center">Status Pelunasan</th>
                                    <th>Jurnal Acak (Q) & Jurnal (A)</th>
                                    <th>Diimport Oleh</th>
                                    <th width="160" class="text-center no-sort">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
    </div> <!-- /.content-wrapper -->
</div> <!-- /.wrapper -->

<!-- Modal Import Faktur H Khusus Kebutuhan Cetak Nota LX-310 -->
<div class="modal fade" id="modalImportFakturH" tabindex="-1" role="dialog" aria-labelledby="modalImportFakturHLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= base_url('sales_order/import_faktur_h') ?>" method="post" enctype="multipart/form-data" id="formImportFakturH">
                <input type="hidden" name="redirect_to" value="sales_order/faktur_h_import">
                <div class="modal-header text-white" style="background-color: #6f42c1;">
                    <h5 class="modal-title font-weight-bold" id="modalImportFakturHLabel">
                        <i class="fas fa-file-import mr-1"></i> Import Faktur H (Cetak Nota LX-310)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label for="fileImportFakturH" class="font-weight-bold text-dark">Pilih File Excel Faktur H (.xlsx / .xls)</label>
                        <div class="custom-file">
                            <input type="file" name="file_faktur_h" id="fileImportFakturH" class="custom-file-input" accept=".xlsx,.xls" required>
                            <label class="custom-file-label" for="fileImportFakturH" id="labelFileImportFakturH">Pilih file Excel Faktur H...</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <a href="<?= base_url('sales_order/template_import_faktur_h') ?>" class="btn btn-outline-primary btn-sm mr-auto font-weight-bold shadow-sm">
                        <i class="fas fa-download mr-1"></i> Unduh Template Excel (.xlsx)
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white btn-sm font-weight-bold shadow-sm" style="background-color: #6f42c1; border-color: #6f42c1;" id="btnSubmitImportFakturH">
                        <i class="fas fa-upload mr-1"></i> Proses Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Pelunasan Berdasarkan Faktur Z (Otomatis Alokasi ke Faktur H & Jurnal Q/A) -->
<div class="modal fade" id="modalImportPelunasanFakturH" tabindex="-1" role="dialog" aria-labelledby="modalImportPelunasanFakturHLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= base_url('sales_order/import_pelunasan_faktur_h') ?>" method="post" enctype="multipart/form-data" id="formImportPelunasanFakturH">
                <input type="hidden" name="redirect_to" value="sales_order/faktur_h_import">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold" id="modalImportPelunasanFakturHLabel">
                        <i class="fas fa-check-double mr-1"></i> Import Pelunasan Berdasarkan Faktur Z (Otomatis Alokasi ke Faktur H & Jurnal Q/A)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <strong>Cara Kerja:</strong> Pelunasan diinput per <strong>Nomor Faktur Z (Induk)</strong>. Nominal yang dibayarkan akan dialokasikan ke Faktur H di bawah Faktur Z tersebut secara berurutan.<br>
                        <span class="text-muted">Mendukung <strong>2 metode pembayaran sekaligus</strong> dalam 1 baris dengan mengisi kolom <code>metode_pembayaran_2</code> &amp; <code>jumlah_pembayaran_2</code> (opsional, misal bayar sebagian via Q Mandiri dan sisanya via Q BRI).</span>
                    </div>
                    <div class="form-group mb-0">
                        <label for="fileImportPelunasanH" class="font-weight-bold text-dark">Pilih File Pelunasan Faktur Z (.xlsx / .xls / .csv)</label>
                        <div class="custom-file">
                            <input type="file" name="file_pelunasan_h" id="fileImportPelunasanH" class="custom-file-input" accept=".xlsx,.xls,.csv" required>
                            <label class="custom-file-label" for="fileImportPelunasanH" id="labelFileImportPelunasanH">Pilih file Excel / CSV Pelunasan Faktur Z...</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <div>
                        <a href="<?= base_url('sales_order/template_import_pelunasan_faktur_h?mode=unpaid') ?>" class="btn btn-outline-success btn-sm font-weight-bold shadow-sm">
                            <i class="fas fa-download mr-1"></i> Unduh Template Faktur Z Belum Lunas (.xlsx)
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm" id="btnSubmitImportPelunasanH">
                            <i class="fas fa-check-double mr-1"></i> Proses Import Pelunasan & Buat Jurnal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pelunasan 1 Faktur H Import (Single) -->
<div class="modal fade" id="modalLunasiSingleFakturH" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= base_url('sales_order/lunasi_faktur_h_import') ?>" method="post" id="formLunasiSingleFakturH">
                <input type="hidden" name="id_import_h" id="singleLunasiIdImportH">
                <input type="hidden" name="redirect_to" value="sales_order/faktur_h_import">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-check-circle mr-1"></i> Lunasi Faktur H: <span id="singleLunasiNoFakturLabel"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2 small text-muted">
                        Customer: <strong class="text-dark" id="singleLunasiCustomerLabel">-</strong>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Tanggal Pelunasan</label>
                        <input type="date" name="tanggal_pembayaran" id="singleLunasiTanggal" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Nominal Pelunasan (Netto Faktur H)</label>
                        <input type="number" step="0.01" name="jumlah_bayar" id="singleLunasiNominal" class="form-control form-control-sm font-weight-bold text-success" required>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Metode Pembayaran 1 (Akun Kas/Bank Jurnal Q)</label>
                        <select name="metode_pembayaran" id="singleLunasiMetode" class="form-control form-control-sm">
                            <option value="Q Mandiri">Q Mandiri (12014)</option>
                            <option value="Q Kas Ayat Silang">Q Kas Ayat Silang</option>
                            <option value="Q Kas">Q Kas (11010)</option>
                            <option value="Q BCA">Q BCA</option>
                            <option value="Q BRI">Q BRI</option>
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="toggleSplitMetodeSingleImport">
                            <label class="custom-control-label small font-weight-bold text-primary" for="toggleSplitMetodeSingleImport" style="cursor:pointer;">
                                Gunakan 2 Metode Pembayaran Sekaligus (Split Bank/Kas)
                            </label>
                        </div>
                    </div>
                    <div id="wrapSplitMetodeSingleImport" class="p-2 mb-3 rounded border bg-light" style="display:none;">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold">Metode Pembayaran 2</label>
                            <select name="metode_pembayaran_2" id="singleLunasiMetode2" class="form-control form-control-sm">
                                <option value="Q BRI" selected>Q BRI</option>
                                <option value="Q Mandiri">Q Mandiri (12014)</option>
                                <option value="Q Kas Ayat Silang">Q Kas Ayat Silang</option>
                                <option value="Q Kas">Q Kas (11010)</option>
                                <option value="Q BCA">Q BCA</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold">Nominal Pembayaran 2 (Netto)</label>
                            <input type="number" step="0.01" name="jumlah_bayar_2" id="singleLunasiNominal2" class="form-control form-control-sm font-weight-bold text-primary" placeholder="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Skema Potongan (Untuk Jurnal A)</label>
                        <select name="skema_potongan" id="singleLunasiSkema" class="form-control form-control-sm">
                            <option value="20" selected>Potongan 20% (Tax) — Netto 80%</option>
                            <option value="40">Potongan 40% (20% Tax + 20% Fee Petugas) — Netto 60%</option>
                            <option value="0">Tanpa Potongan (0%) — Hanya Jurnal Q</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">No. Faktur Z Induk (Opsional - Referensi Jurnal A)</label>
                        <input type="text" name="parent_no_faktur" id="singleLunasiParentNo" class="form-control form-control-sm" placeholder="Contoh: ZINV2601001">
                    </div>
                    <div class="alert alert-light border py-2 mb-0 small" id="singleLunasiPreviewBox">
                        <!-- Preview perhitungan Bruto & Potongan -->
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-check mr-1"></i> Simpan Pelunasan & Buat Jurnal Q + A
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pelunasan Batch (Terpilih via Checkbox) -->
<div class="modal fade" id="modalLunasiBatchFakturH" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow-lg border-0">
            <form action="<?= base_url('sales_order/lunasi_faktur_h_import_batch') ?>" method="post" id="formLunasiBatchFakturH">
                <input type="hidden" name="ids" id="batchLunasiIdsInput">
                <input type="hidden" name="redirect_to" value="sales_order/faktur_h_import">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-check-double mr-1"></i> Lunasi Batch (<span id="batchLunasiCountLabel">0</span> Faktur H Terpilih)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        Seluruh Faktur H yang dicentang akan dilunasi penuh (100% sesuai nominal tagihan masing-masing faktur) dan otomatis dibuatkan <strong>Jurnal Pembayaran Acak (Q)</strong> serta <strong>Jurnal Kas Masuk (A)</strong>.
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Tanggal Pelunasan</label>
                        <input type="date" name="tanggal_pembayaran" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                        <small class="text-muted">Jika dikosongkan, otomatis mengikuti tanggal faktur masing-masing.</small>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">Metode Pembayaran (Akun Kas/Bank Jurnal Q)</label>
                        <select name="metode_pembayaran" class="form-control form-control-sm">
                            <option value="Q Mandiri" selected>Q Mandiri (12014)</option>
                            <option value="Q Kas Ayat Silang">Q Kas Ayat Silang</option>
                            <option value="Q Kas">Q Kas (11010)</option>
                            <option value="Q BCA">Q BCA</option>
                            <option value="Q BRI">Q BRI</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Skema Potongan (Untuk Jurnal A)</label>
                        <select name="skema_potongan" class="form-control form-control-sm">
                            <option value="20" selected>Potongan 20% (Tax) — Netto 80%</option>
                            <option value="40">Potongan 40% (20% Tax + 20% Fee Petugas) — Netto 60%</option>
                            <option value="0">Tanpa Potongan (0%) — Hanya Jurnal Q</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-check-double mr-1"></i> Proses Lunasi Semua Terpilih
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Lihat Rincian Jurnal Pembayaran Acak (Q) & Jurnal Kas Masuk (A) -->
<div class="modal fade" id="modalJurnalPelunasanFakturH" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-book mr-1"></i> Jurnal Pelunasan Faktur H: <span id="jurnalModalNoFaktur"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-3" id="jurnalModalBodyContent">
                <!-- Di-inject via AJAX -->
            </div>
            <div class="modal-footer bg-light">
                <a href="<?= base_url('jurnal/pembayaran') ?>" target="_blank" class="btn btn-outline-primary btn-sm mr-auto font-weight-bold">
                    <i class="fas fa-external-link-alt mr-1"></i> Buka Menu Jurnal Pembayaran
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Rincian Barang Faktur H Import -->
<div class="modal fade" id="modalDetailFakturHImport" tabindex="-1" role="dialog" aria-labelledby="modalDetailFakturHImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title font-weight-bold text-dark" id="modalDetailFakturHImportLabel">
                    <i class="fas fa-tag mr-1" style="color: #6f42c1;"></i> Rincian Faktur H Import: <span id="detailNoFakturH" class="font-weight-bold" style="color: #6f42c1;"></span>
                </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-3">
                <div class="row mb-3" id="detailHeaderFakturH">
                    <!-- Rincian header di-inject via JS -->
                </div>
                <h6 class="font-weight-bold text-dark mb-2">
                    <i class="fas fa-boxes mr-1 text-secondary"></i> Daftar Rincian Barang
                </h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm table-striped mb-0 text-nowrap" id="tableDetailItemsFakturH">
                        <thead class="thead-light">
                            <tr>
                                <th width="30" class="text-center">No</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th class="text-center">No. Lot</th>
                                <th class="text-center">Expired</th>
                                <th class="text-right">Qty</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-center">Disc (%)</th>
                                <th class="text-right">Total Harga</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDetailItemsFakturH">
                            <!-- Items di-inject via JS -->
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="9" class="text-right">Grand Total:</td>
                                <td class="text-right text-success" id="detailGrandTotalFakturH">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Bagian Rincian Jurnal Pelunasan di dalam Modal Detail -->
                <div id="detailJurnalSectionFakturH"></div>
            </div>
            <div class="modal-footer bg-light">
                <a href="#" target="_blank" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnCetakModalDetailFakturH">
                    <i class="fas fa-print mr-1"></i> Cetak Faktur Ini (LX-310)
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery) {
        var $ = window.jQuery;

        // Custom upload input label
        $('#fileImportFakturH').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $('#labelFileImportFakturH').text(fileName || 'Pilih file Excel Faktur H...');
        });

        $('#fileImportPelunasanH').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $('#labelFileImportPelunasanH').text(fileName || 'Pilih file Excel / CSV Pelunasan Faktur Z...');
        });

        $('#formImportFakturH').on('submit', function() {
            $('#btnSubmitImportFakturH').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Import...');
        });

        $('#formImportPelunasanFakturH').on('submit', function() {
            $('#btnSubmitImportPelunasanH').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Pelunasan & Jurnal...');
        });

        // Preview perhitungan Bruto & Potongan di Modal Single Pelunasan
        function updateSingleLunasiPreview() {
            var netto1 = parseFloat($('#singleLunasiNominal').val() || 0);
            var netto2 = $('#toggleSplitMetodeSingleImport').is(':checked') ? parseFloat($('#singleLunasiNominal2').val() || 0) : 0;
            var netto = Math.max(0, netto1) + Math.max(0, netto2);
            var skema = parseInt($('#singleLunasiSkema').val() || 20, 10);
            var pembagi = (skema === 40) ? 0.6 : ((skema === 20) ? 0.8 : 1.0);
            var bruto = (pembagi > 0 && pembagi < 1.0) ? Math.round(netto / pembagi) : netto;
            var potongan = Math.max(0, bruto - netto);

            var html = '<strong>Simulasi Jurnal yang Akan Dibuat (Total Netto: Rp ' + netto.toLocaleString('id-ID') + '):</strong><br>' +
                '&bull; <strong>Jurnal Acak (Q):</strong> Debit Kas/Bank = <strong>Rp ' + bruto.toLocaleString('id-ID') + '</strong> | Kredit Piutang (13099) = <strong>Rp ' + netto.toLocaleString('id-ID') + '</strong>';
            if (potongan > 0) {
                html += ' | Kredit Q Kas (11010) = <strong>Rp ' + potongan.toLocaleString('id-ID') + '</strong><br>' +
                    '&bull; <strong>Jurnal Kas Masuk (A):</strong> Debit A Kas (11030) = <strong>Rp ' + potongan.toLocaleString('id-ID') + '</strong> | Kredit PND Pot Penj KIU = <strong>Rp ' + potongan.toLocaleString('id-ID') + '</strong>';
            } else {
                html += '<br>&bull; <strong>Jurnal Kas Masuk (A):</strong> <em>Tidak dibuat (Skema Tanpa Potongan 0%)</em>';
            }
            $('#singleLunasiPreviewBox').html(html);
        }

        $('#toggleSplitMetodeSingleImport').on('change', function() {
            if ($(this).is(':checked')) {
                $('#wrapSplitMetodeSingleImport').slideDown(150);
            } else {
                $('#wrapSplitMetodeSingleImport').slideUp(150);
                $('#singleLunasiNominal2').val('');
            }
            updateSingleLunasiPreview();
        });

        $('#singleLunasiNominal, #singleLunasiNominal2, #singleLunasiSkema').on('input change', updateSingleLunasiPreview);

        $(document).on('click', '.btn-open-lunasi-single', function() {
            var btn = $(this);
            $('#singleLunasiIdImportH').val(btn.data('id'));
            $('#singleLunasiNoFakturLabel').text(btn.data('no-faktur'));
            $('#singleLunasiCustomerLabel').text(btn.data('customer'));
            $('#singleLunasiTanggal').val(btn.data('tanggal') || '<?= date("Y-m-d") ?>');
            $('#singleLunasiNominal').val(btn.data('nominal'));
            $('#toggleSplitMetodeSingleImport').prop('checked', false);
            $('#wrapSplitMetodeSingleImport').hide();
            $('#singleLunasiNominal2').val('');
            $('#singleLunasiParentNo').val(btn.data('parent-no') || '');
            updateSingleLunasiPreview();
            $('#modalLunasiSingleFakturH').modal('show');
        });

        // Handler toggle pushmenu (buka/tutup sidebar)
        $(document).on('click', '[data-widget="pushmenu"], .karisma-topbar-toggle', function(e) {
            e.preventDefault();
            var $body = $('body');
            var hadCollapse = $body.hasClass('sidebar-collapse');
            setTimeout(function() {
                if ($body.hasClass('sidebar-collapse') === hadCollapse) {
                    if ($(window).width() >= 992) {
                        $body.toggleClass('sidebar-collapse');
                    } else {
                        $body.toggleClass('sidebar-open');
                    }
                }
            }, 60);
        });

        // Helper format HTML, angka Rupiah, dan tanggal tampilan tabel
        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatRupiah(val) {
            var num = parseFloat(val || 0);
            if (isNaN(num)) num = 0;
            return 'Rp ' + Math.round(num).toLocaleString('id-ID');
        }

        function formatNumberId(val) {
            var num = parseFloat(val || 0);
            if (isNaN(num)) num = 0;
            return num.toLocaleString('id-ID');
        }

        function formatDateIndo(dateStr) {
            if (!dateStr) return '-';
            var clean = String(dateStr).trim().substring(0, 10);
            var parts = clean.split('-');
            if (parts.length !== 3) return escapeHtml(dateStr);
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }

        function formatDateTimeShort(dtStr) {
            if (!dtStr) return '';
            var parts = String(dtStr).trim().split(' ');
            var d = parts[0] ? parts[0].split('-') : [];
            if (d.length !== 3) return escapeHtml(dtStr);
            var t = parts[1] ? parts[1].substring(0, 5) : '';
            return d[2] + '/' + d[1] + '/' + d[0].substring(2) + (t ? ' ' + t : '');
        }

        // Penyimpan status pilihan checkbox lintas halaman pada DataTables server-side
        var selectedFakturHMap = {};

        // Setup DataTables Server-Side untuk ribuan data Faktur H Import
        var dtFakturH = null;
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#tableFakturHImportPage')) {
            dtFakturH = $('#tableFakturHImportPage').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                searchDelay: 400,
                responsive: false,
                scrollX: true,
                autoWidth: false,
                ordering: true,
                order: [[4, 'desc']], // Urutkan tanggal faktur terbaru
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500]],
                ajax: {
                    url: '<?= base_url("sales_order/faktur_h_import_datatable") ?>',
                    type: 'POST',
                    data: function(d) {
                        d.date1 = $('input[name="date1"]').val() || '';
                        d.date2 = $('input[name="date2"]').val() || '';
                        d.status_bayar = $('select[name="status_bayar"]').val() || '';
                        d.filter_search = $('input[name="search"]').val() || '';
                    }
                },
                createdRow: function(row, data) {
                    $(row).attr('data-id-import-h', data.id_import_h);
                },
                columns: [
                    {
                        data: 'id_import_h',
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle',
                        render: function(data, type, row) {
                            var idStr = String(row.id_import_h);
                            var checkedAttr = selectedFakturHMap[idStr] ? ' checked' : '';
                            var gt = parseFloat(row.grand_total || 0);
                            return '<input type="checkbox" class="check-item-faktur-h-import" value="' + parseInt(row.id_import_h, 10) + '" data-no-faktur="' + escapeHtml(row.no_faktur) + '" data-grand-total="' + gt + '"' + checkedAttr + ' style="cursor:pointer; width:16px; height:16px;">';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle font-weight-bold text-muted',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_faktur',
                        className: 'align-middle',
                        render: function(data, type, row) {
                            var id = parseInt(row.id_import_h, 10);
                            return '<a href="javascript:void(0)" onclick="lihatDetailFakturHImport(' + id + ')" ' +
                                'class="badge badge-purple font-weight-bold text-white shadow-sm" style="background-color: #6f42c1; font-size: 12px; padding: 5px 9px;" ' +
                                'title="Klik untuk melihat rincian barang & jurnal faktur ini">' +
                                '<i class="fas fa-tag mr-1"></i> ' + escapeHtml(row.no_faktur) + '</a>';
                        }
                    },
                    {
                        data: 'parent_no_faktur',
                        className: 'align-middle',
                        render: function(data, type, row) {
                            var parentNo = row.parent_no_faktur ? escapeHtml(row.parent_no_faktur) : '-';
                            return '<span class="badge badge-secondary font-weight-bold" style="font-size: 11px;">' + parentNo + '</span>';
                        }
                    },
                    {
                        data: 'tanggal_faktur',
                        className: 'align-middle font-weight-bold text-dark',
                        render: function(data, type, row) {
                            return formatDateIndo(row.tanggal_faktur);
                        }
                    },
                    {
                        data: 'customer_name',
                        className: 'align-middle',
                        render: function(data, type, row) {
                            var custName = row.customer_name || '-';
                            var html = '<span class="font-weight-bold text-dark d-block">' + escapeHtml(custName) + '</span>';
                            if (row.kontak_person && row.kontak_person !== custName) {
                                html += '<span class="text-muted small d-block">Kontak: ' + escapeHtml(row.kontak_person) + '</span>';
                            }
                            html += '<span class="badge badge-light border small text-muted">' + escapeHtml(row.kota || '-') + ' (' + escapeHtml(row.kd_rute || 'SBY') + ')</span>';
                            return html;
                        }
                    },
                    {
                        data: 'total_item',
                        className: 'text-center align-middle',
                        render: function(data, type, row) {
                            return '<span class="badge badge-light border font-weight-bold">' +
                                parseInt(row.total_item || 0, 10) + ' item (' + formatNumberId(row.total_qty) + ' pcs)</span>';
                        }
                    },
                    {
                        data: 'grand_total',
                        className: 'text-right align-middle',
                        render: function(data, type, row) {
                            var gt = parseFloat(row.grand_total || 0);
                            var stBayar = String(row.status_bayar || 'belum_lunas').trim().toLowerCase();
                            var isLunas = (stBayar === 'lunas');
                            var bruto = parseFloat(row.nominal_bruto || 0);
                            var html = '<span class="font-weight-bold text-dark d-block" style="font-size: 13px;">' + formatRupiah(gt) + '</span>';
                            if (isLunas && bruto > gt) {
                                html += '<span class="small text-muted" style="font-size: 10.5px;">Bruto: ' + formatRupiah(bruto) + ' (Pot ' + parseInt(row.skema_potongan || 20, 10) + '%)</span>';
                            }
                            return html;
                        }
                    },
                    {
                        data: 'status_bayar',
                        className: 'text-center align-middle',
                        render: function(data, type, row) {
                            var gt = parseFloat(row.grand_total || 0);
                            var bayar = parseFloat(row.total_bayar || 0);
                            var stBayar = String(row.status_bayar || 'belum_lunas').trim().toLowerCase();
                            if (stBayar === 'lunas') {
                                var html = '<span class="badge badge-success px-2 py-1 shadow-sm" style="font-size: 11px;"><i class="fas fa-check-circle mr-1"></i> LUNAS</span>';
                                html += '<div class="small text-success font-weight-bold mt-1" style="font-size: 11px;">' + formatRupiah(bayar > 0 ? bayar : gt) + '</div>';
                                if (row.tanggal_bayar) {
                                    html += '<div class="small text-muted" style="font-size: 10px;">' + formatDateIndo(row.tanggal_bayar) + ' &bull; ' + escapeHtml(row.metode_pembayaran || '') + '</div>';
                                }
                                return html;
                            }
                            if (stBayar === 'sebagian') {
                                var sisaTagihan = Math.max(0, gt - bayar);
                                return '<span class="badge badge-warning px-2 py-1" style="font-size: 11px;"><i class="fas fa-adjust mr-1"></i> SEBAGIAN</span>' +
                                    '<div class="small text-dark font-weight-bold mt-1" style="font-size: 11px;">Dibayar: ' + formatRupiah(bayar) + '</div>' +
                                    '<div class="small text-danger font-weight-bold" style="font-size: 10.5px;">Sisa: ' + formatRupiah(sisaTagihan) + '</div>';
                            }
                            return '<span class="badge badge-secondary px-2 py-1" style="font-size: 11px;"><i class="far fa-clock mr-1"></i> BELUM LUNAS</span>';
                        }
                    },
                    {
                        data: 'nomor_jurnal_q',
                        className: 'align-middle',
                        render: function(data, type, row) {
                            var id = parseInt(row.id_import_h, 10);
                            if (!row.nomor_jurnal_q && !row.nomor_jurnal_a) {
                                return '<span class="text-muted small"><i class="fas fa-minus-circle mr-1"></i> Belum ada jurnal</span>';
                            }
                            var html = '';
                            if (row.nomor_jurnal_q) {
                                html += '<div class="mb-1"><a href="javascript:void(0)" onclick="lihatJurnalPelunasanFakturH(' + id + ')" ' +
                                    'class="badge badge-primary font-weight-bold shadow-sm" style="font-size: 11px; padding: 4px 7px;" ' +
                                    'title="Klik untuk melihat rincian Jurnal Pembayaran Acak (Q)">' +
                                    '<i class="fas fa-book mr-1"></i> Q: ' + escapeHtml(row.nomor_jurnal_q) + '</a></div>';
                            }
                            if (row.nomor_jurnal_a) {
                                html += '<div><a href="javascript:void(0)" onclick="lihatJurnalPelunasanFakturH(' + id + ')" ' +
                                    'class="badge badge-info font-weight-bold shadow-sm" style="font-size: 11px; padding: 4px 7px;" ' +
                                    'title="Klik untuk melihat rincian Jurnal Kas Masuk (A)">' +
                                    '<i class="fas fa-file-invoice-dollar mr-1"></i> A: ' + escapeHtml(row.nomor_jurnal_a) + '</a></div>';
                            }
                            return html;
                        }
                    },
                    {
                        data: 'create_by',
                        className: 'align-middle small text-muted',
                        render: function(data, type, row) {
                            return escapeHtml(row.create_by || '-') + '<br><span style="font-size: 10px;">' + formatDateTimeShort(row.create_at) + '</span>';
                        }
                    },
                    {
                        data: 'id_import_h',
                        orderable: false,
                        searchable: false,
                        className: 'text-center align-middle',
                        render: function(data, type, row) {
                            var id = parseInt(row.id_import_h, 10);
                            var gt = parseFloat(row.grand_total || 0);
                            var bayar = parseFloat(row.total_bayar || 0);
                            var sisa = Math.max(0, gt - bayar);
                            var stBayar = String(row.status_bayar || 'belum_lunas').trim().toLowerCase();
                            var noFakturEsc = escapeHtml(row.no_faktur || '');
                            var html = '<div class="btn-group btn-group-sm">';

                            html += '<button type="button" class="btn btn-default btn-sm border" onclick="lihatDetailFakturHImport(' + id + ')" title="Lihat Rincian Barang & Jurnal"><i class="fas fa-eye text-primary"></i></button>';

                            if (stBayar === 'lunas') {
                                html += '<button type="button" class="btn btn-outline-primary btn-sm" onclick="lihatJurnalPelunasanFakturH(' + id + ')" title="Lihat Jurnal Pembayaran Acak (Q) & Jurnal (A)"><i class="fas fa-book"></i></button>';
                                html += '<a href="<?= base_url("sales_order/unpost_pelunasan_faktur_h_import/") ?>' + id + '?from=page" ' +
                                    'class="btn btn-outline-warning btn-sm btn-unpost-single-faktur-h" data-no-faktur="' + noFakturEsc + '" ' +
                                    'title="Batalkan (Unpost) Pelunasan & Hapus Jurnal Q/A"><i class="fas fa-undo"></i></a>';
                            } else {
                                if (stBayar === 'sebagian' && (row.nomor_jurnal_q || row.nomor_jurnal_a)) {
                                    html += '<button type="button" class="btn btn-outline-primary btn-sm" onclick="lihatJurnalPelunasanFakturH(' + id + ')" title="Lihat Jurnal Pembayaran Acak (Q) & Jurnal (A)"><i class="fas fa-book"></i></button>';
                                    html += '<a href="<?= base_url("sales_order/unpost_pelunasan_faktur_h_import/") ?>' + id + '?from=page" ' +
                                        'class="btn btn-outline-warning btn-sm btn-unpost-single-faktur-h" data-no-faktur="' + noFakturEsc + '" ' +
                                        'title="Batalkan (Unpost) Pelunasan & Hapus Jurnal Q/A"><i class="fas fa-undo"></i></a>';
                                }
                                html += '<button type="button" class="btn btn-success btn-sm font-weight-bold btn-open-lunasi-single" ' +
                                    'data-id="' + id + '" ' +
                                    'data-no-faktur="' + noFakturEsc + '" ' +
                                    'data-parent-no="' + escapeHtml(row.parent_no_faktur || '') + '" ' +
                                    'data-customer="' + escapeHtml(row.customer_name || '') + '" ' +
                                    'data-tanggal="' + escapeHtml(row.tanggal_faktur || '<?= date("Y-m-d") ?>') + '" ' +
                                    'data-nominal="' + (sisa > 0 ? sisa : gt) + '" ' +
                                    'title="Lunasi Faktur H ini & Buat Jurnal Q + A">' +
                                    '<i class="fas fa-check mr-1"></i> Lunasi</button>';
                            }

                            html += '<a href="<?= base_url("sales_order/cetak_faktur_h_import/") ?>' + id + '" target="_blank" class="btn btn-outline-info btn-sm" title="Cetak Faktur (Printer Dot-Matrix LX-310)"><i class="fas fa-print"></i></a>';
                            html += '<a href="<?= base_url("sales_order/hapus_faktur_h_import/") ?>' + id + '?from=page" ' +
                                'class="btn btn-outline-danger btn-sm btn-hapus-single-faktur-h" data-no-faktur="' + noFakturEsc + '" ' +
                                'title="Hapus Faktur H Import ini"><i class="fas fa-trash-alt"></i></a>';

                            html += '</div>';
                            return html;
                        }
                    }
                ],
                language: {
                    processing: '<i class="fas fa-spinner fa-spin mr-1 text-primary"></i> Memuat data...',
                    search: "Cari Faktur H Import:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ faktur",
                    infoEmpty: "Tidak ada data faktur H import",
                    infoFiltered: "(disaring dari _MAX_ total faktur)",
                    zeroRecords: "Tidak ada faktur H import yang cocok dengan pencarian",
                    emptyTable: "Belum ada data Faktur H Hasil Import.",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                },
                columnDefs: [
                    { orderable: false, targets: [0, 1, 11] }
                ]
            });
        }

        // Konfirmasi delegated untuk tombol unpost & hapus satuan agar aman dari karakter khusus no_faktur
        $(document).on('click', '.btn-unpost-single-faktur-h', function(e) {
            var noFaktur = $(this).data('no-faktur') || '';
            if (!confirm('Batalkan (Unpost) pelunasan Faktur ' + noFaktur + ' beserta Jurnal Acak (Q) dan Jurnal (A)?')) {
                e.preventDefault();
            }
        });

        $(document).on('click', '.btn-hapus-single-faktur-h', function(e) {
            var noFaktur = $(this).data('no-faktur') || '';
            if (!confirm('Apakah Anda yakin ingin menghapus Faktur H Import ' + noFaktur + '?\n\nData import beserta jurnal pelunasannya akan dihapus secara permanen.')) {
                e.preventDefault();
            }
        });

        // Handler sinkronisasi status checkbox terpilih lintas halaman
        function updateSelectedState() {
            var dt = dtFakturH || ($.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturHImportPage') ? $('#tableFakturHImportPage').DataTable() : null);
            var currentNodes = dt ? dt.rows({ page: 'current' }).nodes() : $('#tableFakturHImportPage tbody tr');
            var currentCheckboxes = $(currentNodes).find('.check-item-faktur-h-import');

            // Sinkronkan status checked di halaman saat ini sesuai map
            currentCheckboxes.each(function() {
                var idStr = String($(this).val());
                $(this).prop('checked', !!selectedFakturHMap[idStr]);
            });

            var totalCurrent = currentCheckboxes.length;
            var checkedCurrent = currentCheckboxes.filter(':checked').length;

            $('#checkAllFakturHImport').prop('checked', totalCurrent > 0 && totalCurrent === checkedCurrent);

            var totalCheckedAll = Object.keys(selectedFakturHMap).length;

            $('#countSelectedFakturHImport').text(totalCheckedAll);
            $('.countSelectedFakturHImport').text(totalCheckedAll);
            if (totalCheckedAll > 0) {
                $('#btnHapusBatchFakturHImport').show();
                $('#btnLunasiBatchFakturHImport').show();
                $('#btnUnpostBatchPelunasanFakturHImport').show();
                $('#btnExportBatchLunasFakturHImport').show();
                $('#btnCetakSemuaFakturHImport').html('<i class="fas fa-print mr-1"></i> Cetak Terpilih (' + totalCheckedAll + ' Faktur)');
            } else {
                $('#btnHapusBatchFakturHImport').hide();
                $('#btnLunasiBatchFakturHImport').hide();
                $('#btnUnpostBatchPelunasanFakturHImport').hide();
                $('#btnExportBatchLunasFakturHImport').hide();
                $('#btnCetakSemuaFakturHImport').html('<i class="fas fa-print mr-1"></i> Cetak Semua (LX-310)');
            }
        }

        $(document).on('change', '#checkAllFakturHImport', function() {
            var isChecked = $(this).is(':checked');
            var dt = dtFakturH || ($.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturHImportPage') ? $('#tableFakturHImportPage').DataTable() : null);
            var currentCheckboxes = dt ? $(dt.rows({ page: 'current' }).nodes()).find('.check-item-faktur-h-import') : $('.check-item-faktur-h-import:visible');
            currentCheckboxes.each(function() {
                var idStr = String($(this).val());
                $(this).prop('checked', isChecked);
                if (isChecked) {
                    selectedFakturHMap[idStr] = true;
                } else {
                    delete selectedFakturHMap[idStr];
                }
            });
            updateSelectedState();
        });

        $(document).on('click', '#btnToggleSelectAllFakturHImport', function(e) {
            e.preventDefault();
            var dt = dtFakturH || ($.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturHImportPage') ? $('#tableFakturHImportPage').DataTable() : null);
            var currentCheckboxes = dt ? $(dt.rows({ page: 'current' }).nodes()).find('.check-item-faktur-h-import') : $('.check-item-faktur-h-import:visible');
            var isAllChecked = currentCheckboxes.length > 0 && currentCheckboxes.length === currentCheckboxes.filter(':checked').length;
            var targetChecked = !isAllChecked;
            currentCheckboxes.each(function() {
                var idStr = String($(this).val());
                $(this).prop('checked', targetChecked);
                if (targetChecked) {
                    selectedFakturHMap[idStr] = true;
                } else {
                    delete selectedFakturHMap[idStr];
                }
            });
            updateSelectedState();
        });

        $(document).on('change', '.check-item-faktur-h-import', function() {
            var idStr = String($(this).val());
            if ($(this).is(':checked')) {
                selectedFakturHMap[idStr] = true;
            } else {
                delete selectedFakturHMap[idStr];
            }
            updateSelectedState();
        });

        if (dtFakturH) {
            dtFakturH.on('draw', function() {
                updateSelectedState();
            });
        }

        function getSelectedIds() {
            return Object.keys(selectedFakturHMap);
        }

        // Tombol Lunasi Batch
        $(document).on('click', '#btnLunasiBatchFakturHImport', function(e) {
            e.preventDefault();
            var ids = getSelectedIds();
            if (ids.length === 0) return;
            $('#batchLunasiIdsInput').val(ids.join(','));
            $('#batchLunasiCountLabel').text(ids.length);
            $('#modalLunasiBatchFakturH').modal('show');
        });

        // Tombol Unpost Pelunasan Batch
        $(document).on('click', '#btnUnpostBatchPelunasanFakturHImport', function(e) {
            e.preventDefault();
            var ids = getSelectedIds();
            if (ids.length === 0) return;
            if (confirm('Apakah Anda yakin ingin membatalkan (Unpost) pelunasan beserta Jurnal Acak (Q) & Jurnal (A) pada ' + ids.length + ' Faktur H terpilih?')) {
                var form = $('<form>', {
                    'action': '<?= base_url("sales_order/unpost_pelunasan_faktur_h_import_batch") ?>',
                    'method': 'POST',
                    'style': 'display:none;'
                });
                form.append($('<input>', { 'type': 'hidden', 'name': 'ids', 'value': ids.join(',') }));
                form.append($('<input>', { 'type': 'hidden', 'name': 'redirect_to', 'value': 'sales_order/faktur_h_import' }));
                $('body').append(form);
                form.submit();
            }
        });

        // Tombol Export Batch Lunas & Jurnal Terpilih
        $(document).on('click', '#btnExportBatchLunasFakturHImport', function(e) {
            e.preventDefault();
            var selectedIds = getSelectedIds();
            if (selectedIds.length === 0) return;

            var form = $('<form>', {
                'action': '<?= base_url("sales_order/export_faktur_h_lunas") ?>',
                'method': 'POST',
                'style': 'display:none;'
            });
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'ids',
                'value': selectedIds.join(',')
            }));
            $('body').append(form);
            form.submit();
            setTimeout(function() { form.remove(); }, 2000);
        });

        // Tombol Cetak Semua / Terpilih
        $(document).on('click', '#btnCetakSemuaFakturHImport', function(e) {
            e.preventDefault();
            var dt = $.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturHImportPage') ? $('#tableFakturHImportPage').DataTable() : null;
            var selectedIds = getSelectedIds();

            var form = $('<form>', {
                'action': '<?= base_url("sales_order/cetak_semua_faktur_h_import") ?>',
                'method': 'POST',
                'target': '_blank',
                'style': 'display:none;'
            });

            if (selectedIds.length > 0) {
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'id_import_h',
                    'value': selectedIds.join(',')
                }));
            } else {
                var visibleIds = [];
                if (dt) {
                    $(dt.rows({ page: 'current' }).nodes()).find('.check-item-faktur-h-import').each(function() {
                        visibleIds.push($(this).val());
                    });
                }
                if (visibleIds.length > 0) {
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': 'id_import_h',
                        'value': visibleIds.join(',')
                    }));
                }
            }

            $('body').append(form);
            form.submit();
            setTimeout(function() { form.remove(); }, 2000);
        });

        // Tombol Hapus Batch
        $(document).on('click', '#btnHapusBatchFakturHImport', function(e) {
            e.preventDefault();
            var selectedIds = getSelectedIds();
            if (selectedIds.length === 0) return;

            if (confirm('Apakah Anda yakin ingin menghapus ' + selectedIds.length + ' Faktur H Import terpilih?\n\nData import beserta jurnal pelunasannya akan dihapus secara permanen.')) {
                var form = $('<form>', {
                    'action': '<?= base_url("sales_order/hapus_faktur_h_import_batch") ?>',
                    'method': 'POST',
                    'style': 'display:none;'
                });
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'ids',
                    'value': selectedIds.join(',')
                }));
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'redirect_to',
                    'value': 'sales_order/faktur_h_import'
                }));
                $('body').append(form);
                form.submit();
            }
        });

        // Helper untuk me-render tabel Jurnal Q dan Jurnal A
        function renderJournalCardHtml(title, badgeCls, jData) {
            if (!jData || !jData.journal) {
                return '<div class="alert alert-secondary py-2 small mb-2"><strong>' + title + ':</strong> Tidak ada / belum terbentuk jurnal.</div>';
            }
            var j = jData.journal;
            var rowsHtml = '';
            var totD = 0, totK = 0;
            if (jData.details && jData.details.length > 0) {
                $.each(jData.details, function(i, d) {
                    var deb = parseFloat(d.debit || 0);
                    var kre = parseFloat(d.kredit || 0);
                    totD += deb;
                    totK += kre;
                    rowsHtml += '<tr>' +
                        '<td class="text-center">' + (i + 1) + '</td>' +
                        '<td class="font-weight-bold text-primary">' + (d.kode_akun || '-') + '</td>' +
                        '<td class="font-weight-bold">' + (d.nama_akun || '-') + '</td>' +
                        '<td>' + (d.keterangan || '-') + '</td>' +
                        '<td class="text-right font-weight-bold text-success">' + (deb > 0 ? 'Rp ' + deb.toLocaleString('id-ID') : '-') + '</td>' +
                        '<td class="text-right font-weight-bold text-info">' + (kre > 0 ? 'Rp ' + kre.toLocaleString('id-ID') : '-') + '</td>' +
                    '</tr>';
                });
            }
            return '<div class="card card-outline card-' + badgeCls + ' mb-3 shadow-sm">' +
                '<div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">' +
                    '<div><strong>' + title + '</strong> &mdash; <span class="badge badge-' + badgeCls + '" style="font-size:12px;">' + j.nomor_jurnal + '</span></div>' +
                    '<div class="small text-muted">Tanggal: <strong>' + j.tanggal_transaksi + '</strong> | Ref: <strong>' + (j.source_no || '-') + '</strong> | Status: <span class="badge badge-success">' + j.status + '</span></div>' +
                '</div>' +
                '<div class="card-body p-0">' +
                    '<div class="table-responsive">' +
                        '<table class="table table-sm table-bordered table-striped mb-0">' +
                            '<thead class="thead-light"><tr><th width="35" class="text-center">#</th><th width="100">Kode Akun</th><th>Nama Akun</th><th>Keterangan</th><th width="150" class="text-right">Debit</th><th width="150" class="text-right">Kredit</th></tr></thead>' +
                            '<tbody>' + rowsHtml + '</tbody>' +
                            '<tfoot class="bg-light font-weight-bold"><tr><td colspan="4" class="text-right">Total Balance:</td><td class="text-right text-success">Rp ' + totD.toLocaleString('id-ID') + '</td><td class="text-right text-info">Rp ' + totK.toLocaleString('id-ID') + '</td></tr></tfoot>' +
                        '</table>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        // Modal khusus melihat Jurnal Q & A
        window.lihatJurnalPelunasanFakturH = function(id) {
            $('#jurnalModalNoFaktur').text('Memuat...');
            $('#jurnalModalBodyContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
            $('#modalJurnalPelunasanFakturH').modal('show');

            $.getJSON('<?= base_url("sales_order/jurnal_pelunasan_faktur_h_import_json/") ?>' + id, function(res) {
                if (res.status === 'success') {
                    var f = res.faktur;
                    $('#jurnalModalNoFaktur').text(f.no_faktur + ' (' + (f.customer_name || '-') + ')');
                    var html = '<div class="row mb-2">' +
                        '<div class="col-md-12">' +
                            '<div class="alert alert-light border py-2 mb-3 small">' +
                                '<strong>Ringkasan Pelunasan:</strong> ' +
                                'No. Faktur H: <strong class="text-primary">' + f.no_faktur + '</strong> &bull; ' +
                                'Nominal Netto (Faktur H): <strong class="text-success">Rp ' + parseFloat(f.total_bayar || f.grand_total || 0).toLocaleString('id-ID') + '</strong> &bull; ' +
                                'Nominal Bruto: <strong>Rp ' + parseFloat(f.nominal_bruto || 0).toLocaleString('id-ID') + '</strong> &bull; ' +
                                'Nominal Potongan (' + (f.skema_potongan || 20) + '%): <strong class="text-danger">Rp ' + parseFloat(f.nominal_potongan || 0).toLocaleString('id-ID') + '</strong> &bull; ' +
                                'Metode: <strong>' + (f.metode_pembayaran || '-') + '</strong>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                    html += renderJournalCardHtml('1. Jurnal Pembayaran Acak (Q)', 'primary', res.jurnal_q);
                    html += renderJournalCardHtml('2. Jurnal Kas Masuk (A) — Potongan Penjualan', 'info', res.jurnal_a);
                    $('#jurnalModalBodyContent').html(html);
                } else {
                    $('#jurnalModalBodyContent').html('<div class="alert alert-danger">' + (res.message || 'Gagal memuat jurnal.') + '</div>');
                }
            }).fail(function(xhr, status, error) {
                var errDetail = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : (xhr.responseText ? xhr.responseText.substring(0, 200) : error);
                $('#jurnalModalBodyContent').html('<div class="alert alert-danger"><strong>Gagal memuat jurnal:</strong> ' + (errDetail || 'Gagal menghubungi server.') + '</div>');
            });
        };

        // Modal Detail AJAX
        window.lihatDetailFakturHImport = function(id) {
            $('#detailNoFakturH').text('Memuat...');
            $('#detailHeaderFakturH').html('<div class="col-12 text-center py-3"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
            $('#tbodyDetailItemsFakturH').empty();
            $('#detailJurnalSectionFakturH').empty();
            $('#modalDetailFakturHImport').modal('show');

            $.getJSON('<?= base_url("sales_order/detail_faktur_h_import_json/") ?>' + id, function(res) {
                if (res.status === 'success') {
                    var f = res.faktur;
                    $('#detailNoFakturH').text(f.no_faktur);
                    $('#btnCetakModalDetailFakturH').attr('href', '<?= base_url("sales_order/cetak_faktur_h_import/") ?>' + f.id_import_h);

                    var stBayarBadge = (f.status_bayar === 'lunas')
                        ? '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> LUNAS (Rp ' + parseFloat(f.total_bayar || f.grand_total || 0).toLocaleString('id-ID') + ')</span>'
                        : '<span class="badge badge-secondary">BELUM LUNAS</span>';

                    var headerHtml = '' +
                        '<div class="col-md-4 mb-2">' +
                            '<table class="table table-sm table-borderless mb-0">' +
                                '<tr><td class="text-muted" width="120">No. Faktur:</td><td class="font-weight-bold text-primary">' + f.no_faktur + '</td></tr>' +
                                '<tr><td class="text-muted">Faktur Z Induk:</td><td>' + (f.parent_no_faktur || '-') + '</td></tr>' +
                                '<tr><td class="text-muted">Tanggal:</td><td>' + f.tanggal_faktur + '</td></tr>' +
                                '<tr><td class="text-muted">Jatuh Tempo:</td><td>' + (f.tanggal_jatuh_tempo || '-') + ' (' + f.tempo + ' hari)</td></tr>' +
                            '</table>' +
                        '</div>' +
                        '<div class="col-md-4 mb-2">' +
                            '<table class="table table-sm table-borderless mb-0">' +
                                '<tr><td class="text-muted" width="120">Customer:</td><td class="font-weight-bold">' + f.customer_name + '</td></tr>' +
                                '<tr><td class="text-muted">Kontak Person:</td><td>' + (f.kontak_person || '-') + '</td></tr>' +
                                '<tr><td class="text-muted">Alamat:</td><td>' + (f.alamat || '-') + '</td></tr>' +
                                '<tr><td class="text-muted">Kota / Rute:</td><td>' + (f.kota || '-') + ' (' + (f.kd_rute || 'SBY') + ')</td></tr>' +
                            '</table>' +
                        '</div>' +
                        '<div class="col-md-4 mb-2">' +
                            '<table class="table table-sm table-borderless mb-0">' +
                                '<tr><td class="text-muted" width="120">Status Bayar:</td><td>' + stBayarBadge + '</td></tr>' +
                                '<tr><td class="text-muted">Jurnal Q / A:</td><td>' + (f.nomor_jurnal_q || '-') + ' / ' + (f.nomor_jurnal_a || '-') + '</td></tr>' +
                                '<tr><td class="text-muted">Salesman:</td><td>' + (f.salesman || '-') + '</td></tr>' +
                                '<tr><td class="text-muted">Diimport Oleh:</td><td>' + f.create_by + ' (' + f.create_at + ')</td></tr>' +
                            '</table>' +
                        '</div>';
                    $('#detailHeaderFakturH').html(headerHtml);

                    var itemsHtml = '';
                    var totalGrand = 0;
                    if (res.details && res.details.length > 0) {
                        $.each(res.details, function(idx, item) {
                            var subtotal = parseFloat(item.total_harga || 0);
                            totalGrand += subtotal;
                            itemsHtml += '' +
                                '<tr>' +
                                    '<td class="text-center">' + (idx + 1) + '</td>' +
                                    '<td>' + (item.kd_barang || '-') + '</td>' +
                                    '<td class="font-weight-bold">' + item.nama_barang + '</td>' +
                                    '<td class="text-center">' + (item.no_lot || '-') + '</td>' +
                                    '<td class="text-center">' + (item.expired_date || '-') + '</td>' +
                                    '<td class="text-right font-weight-bold">' + parseFloat(item.qty).toLocaleString('id-ID') + '</td>' +
                                    '<td class="text-center">' + item.satuan + '</td>' +
                                    '<td class="text-right">Rp ' + parseFloat(item.hrg_satuan).toLocaleString('id-ID') + '</td>' +
                                    '<td class="text-center">' + parseFloat(item.disc) + '%</td>' +
                                    '<td class="text-right font-weight-bold text-success">Rp ' + subtotal.toLocaleString('id-ID') + '</td>' +
                                '</tr>';
                        });
                    } else {
                        itemsHtml = '<tr><td colspan="10" class="text-center text-muted py-3">Tidak ada rincian barang.</td></tr>';
                    }
                    $('#tbodyDetailItemsFakturH').html(itemsHtml);
                    $('#detailGrandTotalFakturH').text('Rp ' + totalGrand.toLocaleString('id-ID'));

                    if (res.jurnal_q || res.jurnal_a) {
                        var jHtml = '<h6 class="font-weight-bold text-dark mb-2 mt-3"><i class="fas fa-book mr-1 text-primary"></i> Rincian Jurnal Pelunasan (Jurnal Acak Q & Jurnal A)</h6>';
                        jHtml += renderJournalCardHtml('1. Jurnal Pembayaran Acak (Q)', 'primary', res.jurnal_q);
                        jHtml += renderJournalCardHtml('2. Jurnal Kas Masuk (A)', 'info', res.jurnal_a);
                        $('#detailJurnalSectionFakturH').html(jHtml);
                    }
                } else {
                    $('#detailHeaderFakturH').html('<div class="col-12 text-danger p-3"><i class="fas fa-exclamation-triangle mr-1"></i> ' + res.message + '</div>');
                }
            }).fail(function() {
                $('#detailHeaderFakturH').html('<div class="col-12 text-danger p-3"><i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat data rincian faktur.</div>');
            });
        };
    }
});
</script>
