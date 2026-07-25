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
    /* Select2 di dalam tabel */
    .select2-container { width: 100% !important; }
    .select2-container .select2-selection--single {
        height: 34px;
        font-size: 14px;
        border: 1px solid #ced4da;
        border-radius: 3px;
    }
    .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 32px;
        padding-left: 8px;
        font-size: 14px;
    }
    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 32px;
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
                                         <label class="custom-control-label font-weight-bold text-success" for="is_jagung">Barang Jagung</label>
                                     </div>
                                 </div>
                                 <div class="col-md-4">
                                     <label class="form-label-sm">Nama Customer <span class="text-danger">*</span></label>
                                     <select class="form-control form-control-sm" name="kd_customer" id="kd_customer" required>
                                         <option value="">-- Pilih Customer --</option>
                                         <?php foreach ($customers as $c): ?>
                                             <option value="<?= htmlspecialchars($c['kd_customer']) ?>"
                                                     <?= ($is_edit && $spr['kd_customer'] == $c['kd_customer']) ? 'selected' : '' ?>
                                                     data-nama="<?= htmlspecialchars($c['nama_customer']) ?>"
                                                     data-alamat="<?= htmlspecialchars($c['alamat_kios'] ?? '') ?>"
                                                     data-sales="<?= htmlspecialchars($c['nama_sales'] ?? '') ?>">
                                                 <?= htmlspecialchars($c['kd_customer'] . ' - ' . $c['nama_customer']) ?>
                                             </option>
                                         <?php endforeach; ?>
                                     </select>
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
                                <!-- Select2 untuk nama barang -->
                                <select class="form-control form-control-sm select2-barang" name="nama_barang[]">
                                    <option value=""></option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control form-control-sm" name="no_faktur[]" placeholder="No. Faktur"></td>
                            <td><input type="text" class="form-control form-control-sm" name="no_batch[]" placeholder="No. Batch / Lot"></td>
                            <td><input type="date" class="form-control form-control-sm" name="expired_date[]"></td>
                            <td><input type="number" class="form-control form-control-sm text-right field-harga" name="harga[]" min="0" step="1" placeholder="0"></td>
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

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(document).ready(function () {

    var AJAX_BARANG_URL = '<?= base_url("retur_penjualan/ajax/search_barang") ?>';

    // ---- Searchable customer ----
    $('#kd_customer').select2({
        theme: 'default',
        placeholder: '-- Pilih Customer --',
        allowClear: true
    });

    // ---- Auto-fill customer ----
    $('#kd_customer').on('change', function() {
        var opt = $(this).find(':selected');
        $('#nama_customer').val(opt.data('nama') || '');
        $('#alamat').val(opt.data('alamat') || '');
        // Jangan override nama_sales jika sudah ada
        if (!$('#nama_sales').val()) {
            $('#nama_sales').val(opt.data('sales') || '');
        }
    });

    // ---- Global Checklist Toggle ----
    $('.chk-bermasalah').on('change', function() {
        $('.alasan-bermasalah-opt').toggle(this.checked);
        if (!this.checked) $('.alasan-bermasalah-opt input').prop('checked', false);
    });
    $('.chk-expired').on('change', function() {
        $('.alasan-expired-opt').toggle(this.checked);
        if (!this.checked) $('.alasan-expired-opt input').prop('checked', false);
    });

    // ---- Inisialisasi Select2 pada sebuah row ----
    function initSelect2Barang($row) {
        $row.find('.select2-barang').select2({
            theme: 'default',
            placeholder: 'Ketik nama barang...',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: AJAX_BARANG_URL,
                dataType: 'json',
                delay: 300,
                data: function(params) { return { q: params.term }; },
                processResults: function(data) { return { results: data.results }; },
                cache: true
            },
            templateResult: function(item) {
                if (!item.id) return item.text;
                return $('<span>' + item.text + '</span>');
            },
            dropdownParent: $('body')
        });
    }

    // ---- Add row ----
    function renumberRows() {
        $('#rowContainer .item-row').each(function(i) {
            $(this).find('.row-no').text(i + 1);
        });
    }

    function addRow() {
        var tmpl = $('#rowTemplate tbody tr.item-row').clone();
        // Reset nilai select2 template agar tidak duplikat id
        tmpl.find('.select2-barang').val('');
        $('#rowContainer').append(tmpl);
        renumberRows();
        bindRowEvents(tmpl);
        initSelect2Barang(tmpl);
    }

    function addRowWithData(item) {
        var tmpl = $('#rowTemplate tbody tr.item-row').clone();
        if (item.nama_barang) {
            tmpl.find('.select2-barang').append(new Option(item.nama_barang, item.nama_barang, true, true));
        }
        tmpl.find('input[name="no_faktur[]"]').val(item.no_faktur || '');
        tmpl.find('input[name="no_batch[]"]').val(item.no_batch || '');
        tmpl.find('input[name="expired_date[]"]').val(item.expired_date || '');
        tmpl.find('input[name="harga[]"]').val(item.harga || '');
        tmpl.find('input[name="qty[]"]').val(item.qty || '');
        $('#rowContainer').append(tmpl);
        renumberRows();
        bindRowEvents(tmpl);
        initSelect2Barang(tmpl);
    }

    function bindRowEvents($row) {
        $row.find('.btn-del-row').on('click', function() {
            if ($('#rowContainer .item-row').length > 1) {
                // Destroy select2 sebelum hapus
                $row.find('.select2-barang').select2('destroy');
                $row.remove();
                renumberRows();
            } else {
                alert('Minimal harus ada 1 baris barang.');
            }
        });
    }

    $('#btnAddRow').on('click', addRow);

    // Init rows
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

    // ---- Konfirmasi ajukan ----
    $('#btnAjukan').on('click', function(e) {
        var nama = $('#kd_customer').val();
        if (!nama) {
            e.preventDefault();
            alert('Pilih Customer terlebih dahulu!');
            return;
        }
        var items = 0;
        $('#rowContainer .item-row .select2-barang').each(function(){
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
