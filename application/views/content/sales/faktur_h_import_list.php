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
                        <i class="fas fa-file-invoice text-purple mr-2" style="color: #6f42c1;"></i> Faktur H Import (Cetak Nota)
                    </h1>
                    <small class="text-muted">Modul khusus import data pecahan faktur (Kode H) dari file Excel untuk kebutuhan cetak nota fisik ke printer dot-matrix Epson LX-310.</small>
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
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            <?php endif; ?>

            <!-- Toolbar Aksi Atas -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div class="d-flex align-items-center flex-wrap mb-2 mb-md-0">
                    <a href="<?= base_url('sales_order/pecah_faktur') ?>" class="btn btn-default btn-sm shadow-sm mr-2 font-weight-bold">
                        <i class="fas fa-cut mr-1 text-warning"></i> Modul Pecah Faktur Z
                    </a>
                    <button type="button" class="btn btn-purple text-white btn-sm shadow-sm font-weight-bold mr-2" style="background-color: #6f42c1; border-color: #6f42c1;" data-toggle="modal" data-target="#modalImportFakturH">
                        <i class="fas fa-file-excel mr-1"></i> Import Excel Faktur H
                    </button>
                    <a href="<?= base_url('sales_order/template_import_faktur_h') ?>" class="btn btn-outline-primary btn-sm shadow-sm font-weight-bold">
                        <i class="fas fa-download mr-1"></i> Download Template Excel (.xlsx)
                    </a>
                </div>
                <div>
                    <span class="badge badge-light border p-2 text-muted">
                        <i class="fas fa-shield-alt text-success mr-1"></i> Data Terisolasi &bull; Tidak Merubah Stok & Jurnal
                    </span>
                </div>
            </div>

            <!-- Ringkasan Statistik Widget 4 Kartu -->
            <div class="row mb-2">
                <div class="col-6 col-md-3">
                    <div class="info-box bg-white shadow-sm border">
                        <span class="info-box-icon text-white elevation-1" style="background-color: #6f42c1;">
                            <i class="fas fa-file-invoice"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Total Faktur H Import</span>
                            <span class="info-box-number font-weight-bold" style="font-size: 20px; color: #6f42c1;">
                                <?= number_format($stat['total_faktur']) ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box bg-white shadow-sm border">
                        <span class="info-box-icon bg-info text-white elevation-1">
                            <i class="fas fa-boxes"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Total Kuantitas Barang</span>
                            <span class="info-box-number text-info font-weight-bold" style="font-size: 20px;">
                                <?= number_format($stat['total_qty']) ?> pcs
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box bg-white shadow-sm border">
                        <span class="info-box-icon bg-warning text-white elevation-1">
                            <i class="fas fa-tags"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Total Jenis Item</span>
                            <span class="info-box-number text-warning font-weight-bold" style="font-size: 20px;">
                                <?= number_format($stat['total_item']) ?> item
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box bg-white shadow-sm border">
                        <span class="info-box-icon bg-success text-white elevation-1">
                            <i class="fas fa-money-bill-wave"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Total Nilai Penjualan</span>
                            <span class="info-box-number text-success font-weight-bold" style="font-size: 19px;">
                                Rp <?= number_format($stat['grand_total'], 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Filter Tanggal & Pencarian -->
            <div class="card card-outline card-secondary shadow-sm mb-3">
                <div class="card-header py-2">
                    <h3 class="card-title font-weight-bold text-muted small text-uppercase">
                        <i class="fas fa-filter mr-1"></i> Filter Periode Faktur H Import
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
                            <div class="col-md-3 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Dari Tanggal Faktur</label>
                                <input type="date" name="date1" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($filter['date1'] ?? '') ?>">
                            </div>
                            <div class="col-md-3 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Sampai Tanggal Faktur</label>
                                <input type="date" name="date2" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($filter['date2'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1">Kata Kunci / No. Faktur / Customer</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari No. Faktur H, Customer, Toko, Sales..."
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
                                class="btn btn-outline-secondary btn-sm font-weight-bold mr-2 shadow-sm" 
                                title="Pilih / Batalkan pilihan semua faktur H import di halaman ini">
                            <i class="far fa-check-square mr-1"></i> Pilih Semua
                        </button>
                        <button type="button" 
                                id="btnHapusBatchFakturHImport" 
                                class="btn btn-danger btn-sm font-weight-bold shadow-sm mr-2" 
                                style="display:none;" 
                                title="Hapus seluruh faktur H import yang sedang dicentang">
                            <i class="fas fa-trash-alt mr-1"></i> Hapus Terpilih (<span id="countSelectedFakturHImport">0</span>)
                        </button>
                        <button type="button" 
                                class="btn btn-purple text-white btn-sm font-weight-bold shadow-sm mr-2" 
                                style="background-color: #6f42c1; border-color: #6f42c1;"
                                data-toggle="modal" 
                                data-target="#modalImportFakturH">
                            <i class="fas fa-file-excel mr-1"></i> Import Excel Faktur H
                        </button>
                        <a href="<?= base_url('sales_order/template_import_faktur_h') ?>" 
                           class="btn btn-outline-primary btn-sm font-weight-bold shadow-sm">
                            <i class="fas fa-download mr-1"></i> Template Excel (.xlsx)
                        </a>
                    </div>
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="badge badge-warning p-2 mr-2" style="font-size: 12.5px;">
                            Total: <strong><?= count($fakturs_h_import ?? []) ?></strong> Faktur H Import
                        </span>
                        <button type="button" 
                                class="btn btn-primary btn-sm font-weight-bold shadow-sm"
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
                                    <th>Alamat & Kota (Rute)</th>
                                    <th class="text-center">Total Item</th>
                                    <th class="text-right">Total Nilai</th>
                                    <th>Diimport Oleh</th>
                                    <th width="120" class="text-center no-sort">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($fakturs_h_import)): ?>
                                    <tr>
                                        <td colspan="11" class="text-center py-5 text-muted">
                                            <i class="fas fa-file-invoice fa-3x mb-3 text-purple" style="color: #6f42c1;"></i><br>
                                            <span class="font-weight-bold" style="font-size: 15px;">Belum Ada Data Faktur H Hasil Import</span><br>
                                            <span class="small text-muted">Silakan unduh template Excel resmi dan unggah data faktur H Anda melalui tombol <strong>Import Excel Faktur H</strong> di atas.</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($fakturs_h_import as $fhi): ?>
                                        <tr data-id-import-h="<?= (int)$fhi['id_import_h'] ?>">
                                            <td class="text-center align-middle">
                                                <input type="checkbox" class="check-item-faktur-h-import" value="<?= (int)$fhi['id_import_h'] ?>" data-no-faktur="<?= htmlspecialchars($fhi['no_faktur']) ?>" style="cursor:pointer; width:16px; height:16px;">
                                            </td>
                                            <td class="text-center align-middle font-weight-bold text-muted"><?= $no++ ?></td>
                                            <td class="align-middle">
                                                <a href="javascript:void(0)" onclick="lihatDetailFakturHImport(<?= (int)$fhi['id_import_h'] ?>)"
                                                   class="badge badge-purple font-weight-bold text-white shadow-sm" style="background-color: #6f42c1; font-size: 12px; padding: 5px 9px;"
                                                   title="Klik untuk melihat rincian barang faktur ini">
                                                    <i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($fhi['no_faktur']) ?>
                                                </a>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-secondary font-weight-bold" style="font-size: 11px;">
                                                    <?= !empty($fhi['parent_no_faktur']) ? htmlspecialchars($fhi['parent_no_faktur']) : '-' ?>
                                                </span>
                                            </td>
                                            <td class="align-middle font-weight-bold text-dark">
                                                <?= !empty($fhi['tanggal_faktur']) ? date('d/m/Y', strtotime($fhi['tanggal_faktur'])) : '-' ?>
                                            </td>
                                            <td class="align-middle">
                                                <span class="font-weight-bold text-dark d-block">
                                                    <?= htmlspecialchars($fhi['customer_name'] ?? '-') ?>
                                                </span>
                                                <?php if (!empty($fhi['kontak_person']) && $fhi['kontak_person'] !== $fhi['customer_name']): ?>
                                                    <span class="text-muted small">
                                                        Kontak: <?= htmlspecialchars($fhi['kontak_person']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle">
                                                <span class="d-block small text-dark"><?= htmlspecialchars($fhi['alamat'] ?? '-') ?></span>
                                                <span class="badge badge-light border small text-muted"><?= htmlspecialchars($fhi['kota'] ?? '-') ?> (<?= htmlspecialchars($fhi['kd_rute'] ?? 'SBY') ?>)</span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-light border font-weight-bold">
                                                    <?= (int)$fhi['total_item'] ?> item (<?= number_format((float)$fhi['total_qty']) ?> pcs)
                                                </span>
                                            </td>
                                            <td class="text-right align-middle font-weight-bold text-success" style="font-size: 13px;">
                                                Rp <?= number_format((float)$fhi['grand_total'], 0, ',', '.') ?>
                                            </td>
                                            <td class="align-middle small text-muted">
                                                <?= htmlspecialchars($fhi['create_by'] ?? '-') ?><br>
                                                <span style="font-size: 10px;"><?= !empty($fhi['create_at']) ? date('d/m/y H:i', strtotime($fhi['create_at'])) : '' ?></span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-default btn-sm border" onclick="lihatDetailFakturHImport(<?= (int)$fhi['id_import_h'] ?>)" title="Lihat Rincian Barang">
                                                        <i class="fas fa-eye text-primary"></i>
                                                    </button>
                                                    <a href="<?= base_url('sales_order/cetak_faktur_h_import/' . $fhi['id_import_h']) ?>"
                                                       target="_blank"
                                                       class="btn btn-outline-info btn-sm"
                                                       title="Cetak Faktur (Printer Dot-Matrix LX-310)">
                                                        <i class="fas fa-print mr-1"></i> Cetak
                                                    </a>
                                                    <a href="<?= base_url('sales_order/hapus_faktur_h_import/' . $fhi['id_import_h'] . '?from=page') ?>"
                                                       class="btn btn-outline-danger btn-sm"
                                                       title="Hapus Faktur H Import ini"
                                                       onclick="return confirm('Apakah Anda yakin ingin menghapus Faktur H Import <?= htmlspecialchars($fhi['no_faktur']) ?>?\n\nData import ini akan dihapus secara permanen.')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                        <i class="fas fa-file-import mr-1"></i> Import Faktur H (Khusus Cetak Nota LX-310)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2" style="font-size: 13px;">
                        <i class="fas fa-shield-alt mr-1"></i> <strong>Aman & Terisolasi:</strong>
                        Data faktur H yang diimpor melalui modul ini disimpan pada tabel terpisah khusus kebutuhan cetak nota.
                        Proses ini <strong>sama sekali TIDAK mempengaruhi</strong> master customer acak, stok gudang, piutang, maupun jurnal akuntansi.
                    </div>
                    <div class="alert alert-light border py-2" style="font-size: 12.5px;">
                        <strong>Petunjuk:</strong>
                        <ol class="mb-0 pl-3">
                            <li>Unduh template Excel resmi melalui tombol <strong>Unduh Template Excel</strong> di bawah.</li>
                            <li>Buka template dan salin data faktur H Anda (bulan Januari - September atau periode lainnya).</li>
                            <li>Satu nomor faktur H dapat memuat beberapa baris barang (multi-item).</li>
                            <li>Simpan file dalam format <code>.xlsx</code> atau <code>.xls</code>, lalu pilih file pada kolom di bawah dan klik <strong>Proses Import</strong>.</li>
                        </ol>
                    </div>
                    <div class="form-group">
                        <label for="fileImportFakturH" class="font-weight-bold text-dark">Pilih File Excel Faktur H (.xlsx / .xls)</label>
                        <div class="custom-file">
                            <input type="file" name="file_faktur_h" id="fileImportFakturH" class="custom-file-input" accept=".xlsx,.xls" required>
                            <label class="custom-file-label" for="fileImportFakturH" id="labelFileImportFakturH">Pilih file Excel Faktur H...</label>
                        </div>
                        <small class="form-text text-muted">Format file yang didukung: .xlsx atau .xls (Ukuran maksimal: 10 MB).</small>
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
                <div class="table-responsive">
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

        $('#formImportFakturH').on('submit', function() {
            $('#btnSubmitImportFakturH').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses Import...');
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

        // Setup DataTables
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#tableFakturHImportPage')) {
            $('#tableFakturHImportPage').DataTable({
                responsive: false,
                scrollX: true,
                autoWidth: false,
                ordering: true,
                order: [[4, 'desc']], // Urutkan tanggal faktur desc
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, "Semua"]],
                language: {
                    search: "Cari Faktur H Import:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ faktur",
                    infoEmpty: "Tidak ada data faktur H import",
                    infoFiltered: "(disaring dari _MAX_ total faktur)",
                    zeroRecords: "Tidak ada faktur H import yang cocok dengan pencarian",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                },
                columnDefs: [
                    { orderable: false, targets: 'no-sort' }
                ]
            });
        }

        // Checkbox status handler
        function updateSelectedState() {
            var dt = $.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturHImportPage') ? $('#tableFakturHImportPage').DataTable() : null;
            var currentNodes = dt ? dt.rows({ page: 'current' }).nodes() : $('#tableFakturHImportPage tbody tr');
            var currentCheckboxes = $(currentNodes).find('.check-item-faktur-h-import');
            var totalCurrent = currentCheckboxes.length;
            var checkedCurrent = currentCheckboxes.filter(':checked').length;

            $('#checkAllFakturHImport').prop('checked', totalCurrent > 0 && totalCurrent === checkedCurrent);

            var allChecked = dt ? $(dt.rows().nodes()).find('.check-item-faktur-h-import:checked') : $('.check-item-faktur-h-import:checked');
            var totalCheckedAll = allChecked.length;

            $('#countSelectedFakturHImport').text(totalCheckedAll);
            if (totalCheckedAll > 0) {
                $('#btnHapusBatchFakturHImport').show();
                $('#btnCetakSemuaFakturHImport').html('<i class="fas fa-print mr-1"></i> Cetak Terpilih (' + totalCheckedAll + ' Faktur)');
            } else {
                $('#btnHapusBatchFakturHImport').hide();
                $('#btnCetakSemuaFakturHImport').html('<i class="fas fa-print mr-1"></i> Cetak Semua (LX-310)');
            }
        }

        $(document).on('change', '#checkAllFakturHImport', function() {
            var isChecked = $(this).is(':checked');
            var dt = $.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturHImportPage') ? $('#tableFakturHImportPage').DataTable() : null;
            if (dt) {
                var currentNodes = dt.rows({ page: 'current' }).nodes();
                $(currentNodes).find('.check-item-faktur-h-import').prop('checked', isChecked);
            } else {
                $('.check-item-faktur-h-import:visible').prop('checked', isChecked);
            }
            updateSelectedState();
        });

        $(document).on('click', '#btnToggleSelectAllFakturHImport', function(e) {
            e.preventDefault();
            var dt = $.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturHImportPage') ? $('#tableFakturHImportPage').DataTable() : null;
            var currentCheckboxes = dt ? $(dt.rows({ page: 'current' }).nodes()).find('.check-item-faktur-h-import') : $('.check-item-faktur-h-import:visible');
            var isAllChecked = currentCheckboxes.length > 0 && currentCheckboxes.length === currentCheckboxes.filter(':checked').length;
            currentCheckboxes.prop('checked', !isAllChecked);
            updateSelectedState();
        });

        $(document).on('change', '.check-item-faktur-h-import', function() {
            updateSelectedState();
        });

        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturHImportPage')) {
            $('#tableFakturHImportPage').DataTable().on('draw', function() {
                updateSelectedState();
            });
        }

        // Tombol Cetak Semua / Terpilih
        $(document).on('click', '#btnCetakSemuaFakturHImport', function(e) {
            e.preventDefault();
            var dt = $.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturHImportPage') ? $('#tableFakturHImportPage').DataTable() : null;
            var selectedIds = [];

            if (dt) {
                $(dt.rows().nodes()).find('.check-item-faktur-h-import:checked').each(function() {
                    selectedIds.push($(this).val());
                });
            } else {
                $('.check-item-faktur-h-import:checked').each(function() {
                    selectedIds.push($(this).val());
                });
            }

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
            var dt = $.fn.DataTable && $.fn.DataTable.isDataTable('#tableFakturHImportPage') ? $('#tableFakturHImportPage').DataTable() : null;
            var selectedIds = [];

            if (dt) {
                $(dt.rows().nodes()).find('.check-item-faktur-h-import:checked').each(function() {
                    selectedIds.push($(this).val());
                });
            } else {
                $('.check-item-faktur-h-import:checked').each(function() {
                    selectedIds.push($(this).val());
                });
            }

            if (selectedIds.length === 0) return;

            if (confirm('Apakah Anda yakin ingin menghapus ' + selectedIds.length + ' Faktur H Import terpilih?\n\nData import ini akan dihapus secara permanen.')) {
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

        // Modal Detail AJAX
        window.lihatDetailFakturHImport = function(id) {
            $('#detailNoFakturH').text('Memuat...');
            $('#detailHeaderFakturH').html('<div class="col-12 text-center py-3"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
            $('#tbodyDetailItemsFakturH').empty();
            $('#modalDetailFakturHImport').modal('show');

            $.getJSON('<?= base_url("sales_order/detail_faktur_h_import_json/") ?>' + id, function(res) {
                if (res.status === 'success') {
                    var f = res.faktur;
                    $('#detailNoFakturH').text(f.no_faktur);
                    $('#btnCetakModalDetailFakturH').attr('href', '<?= base_url("sales_order/cetak_faktur_h_import/") ?>' + f.id_import_h);

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
                                '<tr><td class="text-muted" width="120">Salesman:</td><td>' + (f.salesman || '-') + '</td></tr>' +
                                '<tr><td class="text-muted">Diimport Oleh:</td><td>' + f.create_by + ' (' + f.create_at + ')</td></tr>' +
                                '<tr><td class="text-muted">Catatan:</td><td>' + (f.catatan || '-') + '</td></tr>' +
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

