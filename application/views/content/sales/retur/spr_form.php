<link rel="stylesheet" href="<?= base_url('assets/dist/css/retur-custom.css') ?>"><!-- views/content/sales/retur/spr_form.php -->
<style>
    .spr-card-header {
        background: linear-gradient(135deg, #c0392b, #e74c3c);
        color: #fff;
        padding: 16px 20px;
        border-radius: 8px 8px 0 0;
    }
    .spr-logo-area {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .spr-title {
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: .5px;
    }
    .spr-subtitle {
        font-size: 0.75rem;
        opacity: .85;
    }
    .table-retur th {
        background: #f8f9fa;
        font-size: 14px;
        font-weight: 600;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        text-align: center;
        padding: 8px !important;
    }
    .table-retur td {
        font-size: 14px;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        padding: 6px 8px !important;
    }
    .table-retur .form-control {
        font-size: 14px;
        height: 34px;
    }
    .keterangan-cell {
        min-width: 260px;
    }
    .keterangan-label {
        font-size: 11px;
        line-height: 1.4;
        display: flex;
        align-items: flex-start;
        gap: 5px;
        margin-bottom: 4px;
    }
    .keterangan-label input[type="checkbox"] {
        margin-top: 2px;
        flex-shrink: 0;
    }
    .sub-opt {
        display: flex;
        gap: 10px;
        margin-left: 18px;
        margin-top: 2px;
    }
    .sub-opt label {
        font-size: 11px;
        cursor: pointer;
    }
    .btn-add-row, .btn-del-row {
        font-size: 11px;
        padding: 2px 8px;
    }
    .spr-note {
        background: #fff8f8;
        border-left: 4px solid #e74c3c;
        padding: 10px 14px;
        font-size: 12px;
        color: #c0392b;
        border-radius: 0 4px 4px 0;
    }
    .form-label-sm {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 2px;
    }
    #rowTemplate { display: none; }
    
    /* Styling Modal Lookup Retur */
    .modal-lookup-search {
        position: relative;
        margin-bottom: 12px;
    }
    .modal-lookup-search input {
        width: 100%;
        padding: 8px 36px 8px 12px;
        font-size: 13px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .modal-lookup-search i {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    .modal-lookup-scroll {
        max-height: 380px;
        overflow-y: auto;
        border: 1px solid #e9ecef;
        border-radius: 4px;
    }
    .modal-lookup-table {
        width: 100%;
        margin-bottom: 0;
        font-size: 13px;
    }
    .modal-lookup-table thead th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 2;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        padding: 8px 10px;
    }
    .modal-lookup-table tbody td {
        padding: 8px 10px;
        vertical-align: middle;
    }
    .modal-lookup-table tbody tr {
        cursor: pointer;
        transition: background-color 0.15s;
    }
    .modal-lookup-table tbody tr:hover {
        background-color: #fff5f5;
    }
    .modal-lookup-table tbody tr.selected {
        background-color: #ffe3e3 !important;
    }
    .input-clickable {
        background-color: #fff !important;
        cursor: pointer;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-file-medical-alt mr-2 text-danger"></i> Buat SPR Baru</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Buat SPR</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- FLASH -->
                <?php if ($msg = $this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible"><i class="fas fa-exclamation-circle mr-1"></i><?= $msg ?><button class="close" data-dismiss="alert"><span>&times;</span></button></div>
                <?php endif; ?>

                <?php $is_edit = isset($spr); ?>
                <form action="<?= $is_edit ? base_url('retur_penjualan/update/' . $spr['id_spr']) : base_url('retur_penjualan/store') ?>" method="post" id="formSPR">
                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <?php endif; ?>

                    <!-- SURAT HEADER CARD (mirip form fisik) -->
                    <div class="card shadow-sm" style="border: 2px solid #e74c3c;">
                        <!-- Header Surat -->
                        <div class="spr-card-header d-flex justify-content-between align-items-center">
                            <div class="spr-logo-area">
                                <img src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo Karisma" height="42" style="filter:brightness(10);">
                                <div>
                                    <div class="spr-subtitle">PT. Karisma Indoagro Universal</div>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="spr-title">SURAT PENGAJUAN RETUR BARANG</div>
                                <div class="spr-subtitle">No. SPR: <strong><?= htmlspecialchars($no_spr) ?></strong></div>
                            </div>
                            <div class="text-right" style="font-size:12px;">
                                <i class="fas fa-file-alt fa-2x mb-1 d-block"></i>
                                SPR Penjualan
                            </div>
                        </div>

                        <div class="card-body">
                             <!-- INFO HEADER -->
                             <div class="row mb-3">
                                 <div class="col-md-3">
                                     <label class="form-label-sm">Tanggal <span class="text-danger">*</span></label>
                                     <input type="date" class="form-control form-control-sm" name="tanggal"
                                            id="tanggal" value="<?= $is_edit ? $spr['tanggal'] : date('Y-m-d') ?>" required>
                                 </div>
                                 <div class="col-md-3">
                                     <label class="form-label-sm">Tipe Retur <span class="text-danger">*</span></label>
                                     <select class="form-control form-control-sm" name="tipe_retur" id="tipe_retur" required>
                                         <option value="biasa" <?= ($is_edit && ($spr['tipe_retur'] ?? 'biasa') === 'biasa') ? 'selected' : '' ?>>Retur (Refund/Potong Faktur)</option>
                                         <option value="replace" <?= ($is_edit && ($spr['tipe_retur'] ?? 'biasa') === 'replace') ? 'selected' : '' ?>>Replace (Ganti Barang)</option>
                                         <option value="service" <?= ($is_edit && ($spr['tipe_retur'] ?? 'biasa') === 'service') ? 'selected' : '' ?>>Service (Servis Barang)</option>
                                     </select>
                                 </div>
                                 <div class="col-md-2 d-flex align-items-center" style="margin-top: 24px;">
                                     <div class="custom-control custom-checkbox">
                                         <input type="checkbox" class="custom-control-input" name="is_jagung" id="is_jagung" value="1" <?= ($is_edit && !empty($spr['is_jagung'])) ? 'checked' : '' ?>>
                                         <label class="custom-control-label font-weight-bold text-success" for="is_jagung">KUB</label>
                                     </div>
                                 </div>
                                  <div class="col-md-4">
                                      <label class="form-label-sm">Nama Customer <span class="text-danger">*</span></label>
                                      <div class="input-group input-group-sm">
                                          <input type="text" class="form-control form-control-sm input-clickable" id="customer_display"
                                                 placeholder="-- Klik untuk Pilih Customer --"
                                                 value="<?= $is_edit ? htmlspecialchars(($spr['kd_customer'] ?? '') . ' - ' . ($spr['nama_customer'] ?? '')) : '' ?>"
                                                 readonly>
                                          <div class="input-group-append">
                                              <button class="btn btn-danger btn-sm" type="button" id="btnOpenModalCustomer" title="Pilih Customer">
                                                  <i class="fas fa-search"></i>
                                              </button>
                                          </div>
                                      </div>
                                      <input type="hidden" name="kd_customer" id="kd_customer" value="<?= $is_edit ? htmlspecialchars($spr['kd_customer']) : '' ?>" required>
                                      <input type="hidden" name="nama_customer" id="nama_customer" value="<?= $is_edit ? htmlspecialchars($spr['nama_customer']) : '' ?>">
                                  </div>
                             </div>
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label-sm">Alamat</label>
                                    <input type="text" class="form-control form-control-sm" name="alamat" id="alamat" value="<?= $is_edit ? htmlspecialchars($spr['alamat']) : '' ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-sm">Sales</label>
                                    <input type="text" class="form-control form-control-sm" name="nama_sales" id="nama_sales"
                                           value="<?= $is_edit ? htmlspecialchars($spr['nama_sales']) : htmlspecialchars($user['nama'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- KALIMAT PEMBUKA -->
                            <p class="small mb-2" style="font-style: italic;">
                                Berikut ini adalah barang-barang yang kami ajukan untuk diretur, dengan rincian sbb:
                            </p>

                            <!-- TABEL BARANG RETUR -->
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-sm table-retur" id="tabelRetur">
                                    <thead>
                                        <tr>
                                            <th style="width:36px;">No.</th>
                                            <th style="min-width:200px;">Nama Barang</th>
                                            <th style="min-width:110px;">No. Faktur</th>
                                            <th style="min-width:110px;">No. Batch / No. Lot</th>
                                            <th style="min-width:120px;">Expired Date</th>
                                            <th style="min-width:110px;">Harga (Rp)</th>
                                            <th style="min-width:70px;">Qty</th>
                                            <th style="width:40px;">Hapus</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rowContainer">
                                        <!-- baris awal -->
                                    </tbody>
                                </table>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-danger btn-add-row mb-4" id="btnAddRow">
                                <i class="fas fa-plus"></i> Tambah Baris Barang
                            </button>

                            <!-- KETERANGAN / ALASAN DI LUAR TABEL (GLOBAL UNTUK 1 SPR) -->
                            <div class="card card-outline card-danger p-3 mb-3">
                                <?php
                                $d0 = ($is_edit && !empty($spr_detail)) ? $spr_detail[0] : [];
                                $chk = function($f) use ($d0) { return !empty($d0[$f]) ? 'checked' : ''; };
                                $val = function($f) use ($d0) { return $d0[$f] ?? ''; };
                                $opt = function($f, $v) use ($d0) { return (isset($d0[$f]) && $d0[$f] === $v) ? 'checked' : ''; };
                                ?>
                                <h6 class="font-weight-bold text-danger border-bottom pb-2 mb-3">Keterangan Retur (Centang salah satu atau yang sesuai)</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- 1. Barang bermasalah -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_brg_bermasalah" value="1" class="chk-bermasalah" <?= $chk('alasan_brg_bermasalah') ?>>
                                                <strong>Barang bermasalah retur ke pabrik</strong><br>
                                                <span class="text-muted small" style="margin-left: 18px;">(fail/daya tmbh/berkutu/benih pecah/kemasan rusak)</span>
                                            </label>
                                            <div class="sub-opt alasan-bermasalah-opt mb-2" style="<?= $chk('alasan_brg_bermasalah') ? '' : 'display:none;' ?> margin-left: 18px;">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="brg_opt_replace" name="alasan_brg_bermasalah_opt" value="replace" class="custom-control-input" <?= $opt('alasan_brg_bermasalah_opt', 'replace') ?>>
                                                    <label class="custom-control-label small" for="brg_opt_replace">Replace</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="brg_opt_not" name="alasan_brg_bermasalah_opt" value="not_replace" class="custom-control-input" <?= $opt('alasan_brg_bermasalah_opt', 'not_replace') ?>>
                                                    <label class="custom-control-label small" for="brg_opt_not">Not Replace</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 2. Expired -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_expired" value="1" class="chk-expired" <?= $chk('alasan_expired') ?>>
                                                <strong>Expired</strong><br>
                                                <span class="text-muted small" style="margin-left: 18px;">(2 bln sebelum tgl Exp utk benih &amp; 3 bln sebelum tgl exp utk pestisida)</span>
                                            </label>
                                            <div class="sub-opt alasan-expired-opt mb-2" style="<?= $chk('alasan_expired') ? '' : 'display:none;' ?> margin-left: 18px;">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="exp_opt_replace" name="alasan_expired_opt" value="replace" class="custom-control-input" <?= $opt('alasan_expired_opt', 'replace') ?>>
                                                    <label class="custom-control-label small" for="exp_opt_replace">Replace</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="exp_opt_not" name="alasan_expired_opt" value="not_replace" class="custom-control-input" <?= $opt('alasan_expired_opt', 'not_replace') ?>>
                                                    <label class="custom-control-label small" for="exp_opt_not">Not Replace</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 3. Tidak laku -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_tidak_laku" value="1" <?= $chk('alasan_tidak_laku') ?>>
                                                <strong>Barang tidak laku &amp; masuk OD</strong>
                                            </label>
                                        </div>

                                        <!-- 4. Tes Market -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_tes_market" value="1" <?= $chk('alasan_tes_market') ?>>
                                                <strong>Faktur T/Brg Tes Market</strong>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <!-- 5. Bad Debt -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_bad_debt" value="1" <?= $chk('alasan_bad_debt') ?>>
                                                <strong>Potensi Bad Debt</strong>
                                            </label>
                                        </div>

                                        <!-- 6. Harga tidak sesuai -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_harga_tidak_sesuai" value="1" <?= $chk('alasan_harga_tidak_sesuai') ?>>
                                                <strong>Barang/Harga tdk sesuai Pesanan</strong>
                                            </label>
                                        </div>

                                        <!-- 7. SPR Intern -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_spr_intern" value="1" <?= $chk('alasan_spr_intern') ?>>
                                                <strong>SPR Intern (brg Oper)</strong>
                                            </label>
                                        </div>

                                        <!-- 8. Lain-lain -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <strong>Lain-lain:</strong>
                                            </label>
                                            <input type="text" class="form-control form-control-sm" name="alasan_lainlain" placeholder="Keterangan lain..." value="<?= htmlspecialchars($val('alasan_lainlain')) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CATATAN -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label-sm">Catatan Tambahan</label>
                                    <textarea class="form-control form-control-sm" name="catatan" rows="2"
                                              placeholder="Catatan (opsional)"><?= $is_edit ? htmlspecialchars($spr['catatan']) : '' ?></textarea>
                                </div>
                            </div>

                            <!-- PERNYATAAN -->
                            <div class="spr-note">
                                <strong>Catatan:</strong><br>
                                Barang yang kami retur sesuai dengan data di atas. Bilamana tidak sesuai, maka kami (toko) akan bertanggung jawab
                                menerima konsekuensinya (retur ditolak) sesuai kebijakan PT Karisma Indoagro Universal.
                            </div>

                            <!-- TOMBOL AKSI -->
                            <div class="row mt-4">
                                <?php if ($is_edit): ?>
                                    <div class="col-12 d-flex gap-2">
                                        <?php if (in_array($spr['status'], ['draft', 'ditolak'], true)): ?>
                                            <button type="submit" name="as_draft" value="1" class="btn btn-secondary mr-2">
                                                <i class="fas fa-save"></i> Simpan sebagai Draft
                                            </button>
                                            <button type="submit" name="as_draft" value="0" class="btn btn-danger mr-2" id="btnAjukan">
                                                <i class="fas fa-paper-plane"></i> Simpan & Ajukan Kembali
                                            </button>
                                            <a href="<?= base_url('retur_penjualan/detail/' . $spr['id_spr']) ?>" class="btn btn-light">
                                                <i class="fas fa-arrow-left"></i> Batal
                                            </a>
                                        <?php else: ?>
                                            <button type="submit" name="as_draft" value="0" class="btn btn-info mr-2">
                                                <i class="fas fa-save"></i> Simpan Perubahan (Tetap Diverifikasi)
                                            </button>
                                            <a href="<?= base_url('retur_penjualan/admretur_cek/' . $spr['id_spr']) ?>" class="btn btn-light">
                                                <i class="fas fa-arrow-left"></i> Kembali ke Form Cek
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" name="as_draft" value="1" class="btn btn-secondary mr-2">
                                            <i class="fas fa-save"></i> Simpan sebagai Draft
                                        </button>
                                        <button type="submit" name="as_draft" value="0" class="btn btn-danger mr-2" id="btnAjukan">
                                            <i class="fas fa-paper-plane"></i> Simpan & Ajukan ke Manager SC
                                        </button>
                                        <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-light">
                                            <i class="fas fa-arrow-left"></i> Batal
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div><!-- end card -->

                </form>

                <!-- TEMPLATE ROW (hidden) -->
                <table id="rowTemplate" style="display:none;">
                    <tbody>
                        <tr class="item-row">
                            <td class="text-center row-no font-weight-bold">1</td>
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm input-clickable input-nama-barang" name="nama_barang[]" placeholder="-- Klik Pilih Barang --" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-danger btn-sm btn-lookup-barang" type="button" title="Pilih Barang">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="kd_barang[]" class="field-kd-barang">
                            </td>
                            <td><input type="text" class="form-control form-control-sm" name="no_faktur[]" placeholder="No. Faktur"></td>
                            <td><input type="text" class="form-control form-control-sm" name="no_batch[]" placeholder="No. Batch / Lot"></td>
                            <td><input type="date" class="form-control form-control-sm" name="expired_date[]"></td>
                            <td><input type="text" class="form-control form-control-sm text-right field-harga" name="harga[]" placeholder="0"></td>
                            <td><input type="number" class="form-control form-control-sm text-right" name="qty[]" min="0" step="0.001" placeholder="0"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-del-row" title="Hapus baris">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </section>
    </div>

    <!-- MODAL LOOKUP CUSTOMER -->
    <div class="modal fade" id="modalCustomer" tabindex="-1" role="dialog" aria-labelledby="modalCustomerLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white py-2">
                    <h5 class="modal-title font-weight-bold" id="modalCustomerLabel">
                        <i class="fas fa-users mr-1"></i> Pilih Customer
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div class="modal-lookup-search">
                        <input type="text" id="searchCustomerInput" placeholder="Ketik untuk mencari nama customer, kode, atau alamat/kios..." autocomplete="off">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="modal-lookup-scroll">
                        <table class="table table-bordered table-sm table-hover modal-lookup-table">
                            <thead>
                                <tr>
                                    <th style="width: 45px;" class="text-center">No</th>
                                    <th style="width: 120px;">Kode</th>
                                    <th>Nama Customer</th>
                                    <th>Nama Kios</th>
                                    <th>Alamat Kios</th>
                                    <th style="width: 80px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="listCustomerBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data customer...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL LOOKUP BARANG -->
    <div class="modal fade" id="modalBarang" tabindex="-1" role="dialog" aria-labelledby="modalBarangLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white py-2">
                    <h5 class="modal-title font-weight-bold" id="modalBarangLabel">
                        <i class="fas fa-boxes mr-1"></i> Pilih Barang Retur
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div class="modal-lookup-search">
                        <input type="text" id="searchBarangInput" placeholder="Ketik untuk mencari nama barang atau kode barang..." autocomplete="off">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="modal-lookup-scroll">
                        <table class="table table-bordered table-sm table-hover modal-lookup-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="width: 150px;">Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th style="width: 110px;" class="text-center">Satuan</th>
                                    <th style="width: 80px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="listBarangBody">
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data barang...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<?php
$cust_initial = [];
if (!empty($customers)) {
    $slice = array_slice($customers, 0, 500);
    foreach ($slice as $c) {
        $cust_initial[] = [
            'kd_customer'   => (string) ($c['kd_customer'] ?? ''),
            'nama_customer' => is_string($c['nama_customer'] ?? null) ? mb_convert_encoding($c['nama_customer'], 'UTF-8', 'UTF-8') : '',
            'nama_kios'     => is_string($c['nama_kios'] ?? null) ? mb_convert_encoding($c['nama_kios'], 'UTF-8', 'UTF-8') : '',
            'alamat_kios'   => is_string($c['alamat_kios'] ?? null) ? mb_convert_encoding($c['alamat_kios'], 'UTF-8', 'UTF-8') : '',
            'nama_sales'    => is_string($c['nama_sales'] ?? null) ? mb_convert_encoding($c['nama_sales'], 'UTF-8', 'UTF-8') : '',
        ];
    }
}
?>

<script>
var INITIAL_CUSTOMERS = <?= json_encode($cust_initial, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

$(document).ready(function () {

    var AJAX_BARANG_URL   = '<?= base_url("retur_penjualan/ajax/search_barang") ?>';
    var AJAX_CUSTOMER_URL = '<?= base_url("retur_penjualan/ajax/search_customer") ?>';

    var $activeBarangRow = null;
    var customerSearchTimer = null;
    var barangSearchTimer   = null;

    function formatRupiahInput(val) {
        if (val === null || val === undefined) return '';
        var clean = val.toString().replace(/[^0-9,]/g, '');
        var parts = clean.split(',');
        var integerPart = parts[0].replace(/^0+(?=\d)/, '');
        if (integerPart === '') integerPart = parts[0] === '0' ? '0' : '';
        var decimalPart = parts.length > 1 ? ',' + parts[1].substring(0, 2) : '';
        var formattedInt = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        if (formattedInt === '' && decimalPart !== '') formattedInt = '0';
        return formattedInt + decimalPart;
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        return $('<div>').text(text).html();
    }

    // ================================================================
    // MODAL LOOKUP CUSTOMER
    // ================================================================

    function renderCustomerRows(items) {
        if (!items || !items.length) {
            $('#listCustomerBody').html('<tr><td colspan="6" class="text-center py-4 text-muted">Customer tidak ditemukan.</td></tr>');
            return;
        }

        var html = '';
        $.each(items, function(idx, c) {
            var jsonStr = encodeURIComponent(JSON.stringify(c));
            html += '<tr class="customer-row-select" data-info="' + jsonStr + '">';
            html += '<td class="text-center">' + (idx + 1) + '</td>';
            html += '<td><strong class="text-danger">' + escapeHtml(c.kd_customer || '-') + '</strong></td>';
            html += '<td class="font-weight-bold">' + escapeHtml(c.nama_customer || '-') + '</td>';
            html += '<td>' + escapeHtml(c.nama_kios || '-') + '</td>';
            html += '<td>' + escapeHtml(c.alamat_kios || '-') + '</td>';
            html += '<td class="text-center">';
            html += '<button type="button" class="btn btn-xs btn-outline-danger btn-select-cust" data-info="' + jsonStr + '"><i class="fas fa-check mr-1"></i>Pilih</button>';
            html += '</td>';
            html += '</tr>';
        });

        $('#listCustomerBody').html(html);
    }

    function filterLocalCustomers(q) {
        if (!INITIAL_CUSTOMERS || !INITIAL_CUSTOMERS.length) return [];
        if (!q) return INITIAL_CUSTOMERS.slice(0, 50);
        var qLower = q.toLowerCase();
        return INITIAL_CUSTOMERS.filter(function(c) {
            return (String(c.nama_customer || '').toLowerCase().indexOf(qLower) >= 0) ||
                   (String(c.kd_customer || '').toLowerCase().indexOf(qLower) >= 0) ||
                   (String(c.nama_kios || '').toLowerCase().indexOf(qLower) >= 0) ||
                   (String(c.alamat_kios || '').toLowerCase().indexOf(qLower) >= 0);
        }).slice(0, 50);
    }

    function openCustomerModal() {
        $('#searchCustomerInput').val('');
        $('#modalCustomer').modal('show');
        if (INITIAL_CUSTOMERS && INITIAL_CUSTOMERS.length > 0) {
            renderCustomerRows(INITIAL_CUSTOMERS.slice(0, 50));
        } else {
            loadCustomerList('');
        }
    }

    $('#customer_display, #btnOpenModalCustomer').on('click', function(e) {
        e.preventDefault();
        openCustomerModal();
    });

    $('#modalCustomer').on('shown.bs.modal', function () {
        $('#searchCustomerInput').trigger('focus');
    });

    function loadCustomerList(q) {
        var localMatch = filterLocalCustomers(q);
        if (localMatch.length > 0) {
            renderCustomerRows(localMatch);
        } else {
            $('#listCustomerBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data customer...</td></tr>');
        }

        $.ajax({
            url: AJAX_CUSTOMER_URL,
            type: 'GET',
            dataType: 'json',
            data: { q: q },
            success: function(res) {
                var items = res.results || [];
                if (items.length > 0) {
                    renderCustomerRows(items);
                } else if (!localMatch.length) {
                    $('#listCustomerBody').html('<tr><td colspan="6" class="text-center py-4 text-muted">Customer tidak ditemukan.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.warn("AJAX Customer notice:", status, error);
                var fallback = filterLocalCustomers(q);
                if (fallback.length > 0) {
                    renderCustomerRows(fallback);
                } else {
                    $('#listCustomerBody').html('<tr><td colspan="6" class="text-center py-4 text-muted">Customer tidak ditemukan.</td></tr>');
                }
            }
        });
    }

    $('#searchCustomerInput').on('input', function() {
        var query = $(this).val();
        clearTimeout(customerSearchTimer);
        customerSearchTimer = setTimeout(function() {
            loadCustomerList(query);
        }, 250);
    });

    function applyCustomer(c) {
        if (!c) return;
        $('#kd_customer').val(c.kd_customer || '');
        $('#nama_customer').val(c.nama_customer || '');
        $('#customer_display').val((c.kd_customer ? c.kd_customer + ' - ' : '') + (c.nama_customer || ''));
        $('#alamat').val(c.alamat_kios || '');
        if (c.nama_sales) {
            $('#nama_sales').val(c.nama_sales);
        }
        $('#modalCustomer').modal('hide');
    }

    $(document).on('click', '.btn-select-cust', function(e) {
        e.stopPropagation();
        var info = JSON.parse(decodeURIComponent($(this).data('info')));
        applyCustomer(info);
    });

    $(document).on('click', '.customer-row-select', function() {
        var info = JSON.parse(decodeURIComponent($(this).data('info')));
        applyCustomer(info);
    });

    // ================================================================
    // MODAL LOOKUP BARANG
    // ================================================================

    function openBarangModal($row) {
        $activeBarangRow = $row;
        $('#searchBarangInput').val('');
        $('#modalBarang').modal('show');
        loadBarangList('');
    }

    $('#modalBarang').on('shown.bs.modal', function () {
        $('#searchBarangInput').trigger('focus');
    });

    function loadBarangList(q) {
        $('#listBarangBody').html('<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data barang...</td></tr>');

        $.ajax({
            url: AJAX_BARANG_URL,
            type: 'GET',
            dataType: 'json',
            data: { q: q },
            success: function(res) {
                var items = res.results || [];
                if (!items.length) {
                    $('#listBarangBody').html('<tr><td colspan="5" class="text-center py-4 text-muted">Barang tidak ditemukan.</td></tr>');
                    return;
                }

                var html = '';
                $.each(items, function(idx, b) {
                    var jsonStr = encodeURIComponent(JSON.stringify(b));
                    html += '<tr class="barang-row-select" data-info="' + jsonStr + '">';
                    html += '<td class="text-center">' + (idx + 1) + '</td>';
                    html += '<td><strong class="text-danger">' + escapeHtml(b.kd_barang || '-') + '</strong></td>';
                    html += '<td class="font-weight-bold">' + escapeHtml(b.nama_barang || '-') + '</td>';
                    html += '<td class="text-center">' + escapeHtml(b.satuan || '-') + '</td>';
                    html += '<td class="text-center">';
                    html += '<button type="button" class="btn btn-xs btn-outline-danger btn-select-brg" data-info="' + jsonStr + '"><i class="fas fa-check mr-1"></i>Pilih</button>';
                    html += '</td>';
                    html += '</tr>';
                });

                $('#listBarangBody').html(html);
            },
            error: function() {
                $('#listBarangBody').html('<tr><td colspan="5" class="text-center py-4 text-danger">Gagal mengambil data barang dari server.</td></tr>');
            }
        });
    }

    $('#searchBarangInput').on('input', function() {
        var query = $(this).val();
        clearTimeout(barangSearchTimer);
        barangSearchTimer = setTimeout(function() {
            loadBarangList(query);
        }, 250);
    });

    function applyBarang(b) {
        if (!b || !$activeBarangRow) return;
        $activeBarangRow.find('.input-nama-barang').val(b.nama_barang || '');
        $activeBarangRow.find('.field-kd-barang').val(b.kd_barang || '');
        $('#modalBarang').modal('hide');
        // Arahkan fokus ke No Faktur atau Qty baris bersangkutan
        $activeBarangRow.find('input[name="no_faktur[]"]').focus();
    }

    $(document).on('click', '.btn-select-brg', function(e) {
        e.stopPropagation();
        var info = JSON.parse(decodeURIComponent($(this).data('info')));
        applyBarang(info);
    });

    $(document).on('click', '.barang-row-select', function() {
        var info = JSON.parse(decodeURIComponent($(this).data('info')));
        applyBarang(info);
    });

    // ================================================================
    // CHECKLIST TOGGLE & FORM TABLE HANDLERS
    // ================================================================

    $('.chk-bermasalah').on('change', function() {
        $('.alasan-bermasalah-opt').toggle(this.checked);
        if (!this.checked) $('.alasan-bermasalah-opt input').prop('checked', false);
    });
    $('.chk-expired').on('change', function() {
        $('.alasan-expired-opt').toggle(this.checked);
        if (!this.checked) $('.alasan-expired-opt input').prop('checked', false);
    });

    function renumberRows() {
        $('#rowContainer .item-row').each(function(i) {
            $(this).find('.row-no').text(i + 1);
        });
    }

    function addRow() {
        var tmpl = $('#rowTemplate tbody tr.item-row').clone();
        tmpl.find('.input-nama-barang').val('');
        tmpl.find('.field-kd-barang').val('');
        $('#rowContainer').append(tmpl);
        renumberRows();
        bindRowEvents(tmpl);
    }

    function addRowWithData(item) {
        var tmpl = $('#rowTemplate tbody tr.item-row').clone();
        tmpl.find('.input-nama-barang').val(item.nama_barang || '');
        tmpl.find('.field-kd-barang').val(item.kd_barang || '');
        tmpl.find('input[name="no_faktur[]"]').val(item.no_faktur || '');
        tmpl.find('input[name="no_batch[]"]').val(item.no_batch || '');
        tmpl.find('input[name="expired_date[]"]').val(item.expired_date || '');
        var hrgVal = item.harga ? parseFloat(item.harga) : 0;
        tmpl.find('input[name="harga[]"]').val(hrgVal > 0 ? formatRupiahInput(hrgVal) : '');
        tmpl.find('input[name="qty[]"]').val(item.qty || '');
        $('#rowContainer').append(tmpl);
        renumberRows();
        bindRowEvents(tmpl);
    }

    function bindRowEvents($row) {
        $row.find('.input-nama-barang, .btn-lookup-barang').on('click', function(e) {
            e.preventDefault();
            openBarangModal($row);
        });

        $row.find('.field-harga').on('input', function() {
            var cursorPosition = this.selectionStart;
            var originalLength = this.value.length;
            var formatted = formatRupiahInput(this.value);
            this.value = formatted;
            var newLength = formatted.length;
            var newPos = Math.max(0, cursorPosition + (newLength - originalLength));
            this.setSelectionRange(newPos, newPos);
        });

        $row.find('.btn-del-row').on('click', function() {
            if ($('#rowContainer .item-row').length > 1) {
                $row.remove();
                renumberRows();
            } else {
                alert('Minimal harus ada 1 baris barang.');
            }
        });
    }

    $('#btnAddRow').on('click', addRow);

    // Inisialisasi baris (Edit / Baru)
    var isEdit = <?= isset($spr_detail) ? 'true' : 'false' ?>;
    if (isEdit) {
        var initDetail = <?= isset($spr_detail) ? json_encode($spr_detail) : '[]' ?>;
        if (initDetail.length > 0) {
            initDetail.forEach(function(item) {
                addRowWithData(item);
            });
        } else {
            addRow();
        }
    } else {
        // Init dengan 1 baris kosong
        addRow();
    }

    // Validasi Form Submission
    $('#btnAjukan').on('click', function(e) {
        var customerId = $('#kd_customer').val();
        if (!customerId) {
            e.preventDefault();
            alert('Pilih Customer terlebih dahulu!');
            openCustomerModal();
            return;
        }
        var items = 0;
        $('#rowContainer .item-row .input-nama-barang').each(function(){
            if ($(this).val() && $(this).val().trim()) items++;
        });
        if (items === 0) {
            e.preventDefault();
            alert('Minimal isi 1 baris nama barang!');
            return;
        }
        if (!confirm('Ajukan SPR ke Manager SC? Setelah diajukan tidak dapat diedit.')) {
            e.preventDefault();
        }
    });
});
</script>
