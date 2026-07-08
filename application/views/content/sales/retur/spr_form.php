<!-- views/content/sales/retur/spr_form.php -->
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
        font-size: 12px;
        font-weight: 600;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        text-align: center;
    }
    .table-retur td {
        font-size: 12px;
        vertical-align: middle;
        border: 1px solid #dee2e6;
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

                <form action="<?= base_url('retur_penjualan/store') ?>" method="post" id="formSPR">
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
                                           id="tanggal" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label-sm">Nama Customer <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm" name="kd_customer" id="kd_customer" required>
                                        <option value="">-- Pilih Customer --</option>
                                        <?php foreach ($customers as $c): ?>
                                            <option value="<?= htmlspecialchars($c['kd_customer']) ?>"
                                                    data-nama="<?= htmlspecialchars($c['nama_customer']) ?>"
                                                    data-alamat="<?= htmlspecialchars($c['alamat_kios'] ?? '') ?>"
                                                    data-sales="<?= htmlspecialchars($c['nama_sales'] ?? '') ?>">
                                                <?= htmlspecialchars($c['nama_customer']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="nama_customer" id="nama_customer">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-sm">Alamat</label>
                                    <input type="text" class="form-control form-control-sm" name="alamat" id="alamat">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label-sm">Sales</label>
                                    <input type="text" class="form-control form-control-sm" name="nama_sales" id="nama_sales"
                                           value="<?= htmlspecialchars($user['nama'] ?? '') ?>">
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
                                            <th style="min-width:180px;">Nama Barang</th>
                                            <th style="min-width:120px;">No. Faktur</th>
                                            <th style="min-width:120px;">No. Batch / No. Lot</th>
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
                                <h6 class="font-weight-bold text-danger border-bottom pb-2 mb-3">Keterangan Retur (Centang salah satu atau yang sesuai)</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- 1. Barang bermasalah -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_brg_bermasalah" value="1" class="chk-bermasalah">
                                                <strong>Barang bermasalah retur ke pabrik</strong><br>
                                                <span class="text-muted small" style="margin-left: 18px;">(fail/daya tmbh/berkutu/benih pecah/kemasan rusak)</span>
                                            </label>
                                            <div class="sub-opt alasan-bermasalah-opt mb-2" style="display:none; margin-left: 18px;">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="brg_opt_replace" name="alasan_brg_bermasalah_opt" value="replace" class="custom-control-input">
                                                    <label class="custom-control-label small" for="brg_opt_replace">Replace</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="brg_opt_not" name="alasan_brg_bermasalah_opt" value="not_replace" class="custom-control-input">
                                                    <label class="custom-control-label small" for="brg_opt_not">Not Replace</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 2. Expired -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_expired" value="1" class="chk-expired">
                                                <strong>Expired</strong><br>
                                                <span class="text-muted small" style="margin-left: 18px;">(2 bln sebelum tgl Exp utk benih &amp; 3 bln sebelum tgl exp utk pestisida)</span>
                                            </label>
                                            <div class="sub-opt alasan-expired-opt mb-2" style="display:none; margin-left: 18px;">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="exp_opt_replace" name="alasan_expired_opt" value="replace" class="custom-control-input">
                                                    <label class="custom-control-label small" for="exp_opt_replace">Replace</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="exp_opt_not" name="alasan_expired_opt" value="not_replace" class="custom-control-input">
                                                    <label class="custom-control-label small" for="exp_opt_not">Not Replace</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 3. Tidak laku -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_tidak_laku" value="1">
                                                <strong>Barang tidak laku &amp; masuk OD</strong>
                                            </label>
                                        </div>

                                        <!-- 4. Tes Market -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_tes_market" value="1">
                                                <strong>Faktur T/Brg Tes Market</strong>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <!-- 5. Bad Debt -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_bad_debt" value="1">
                                                <strong>Potensi Bad Debt</strong>
                                            </label>
                                        </div>

                                        <!-- 6. Harga tidak sesuai -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_harga_tidak_sesuai" value="1">
                                                <strong>Barang/Harga tdk sesuai Pesanan</strong>
                                            </label>
                                        </div>

                                        <!-- 7. SPR Intern -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <input type="checkbox" name="alasan_spr_intern" value="1">
                                                <strong>SPR Intern (brg Oper)</strong>
                                            </label>
                                        </div>

                                        <!-- 8. Lain-lain -->
                                        <div class="form-group mb-2">
                                            <label class="keterangan-label font-weight-normal mb-1">
                                                <strong>Lain-lain:</strong>
                                            </label>
                                            <input type="text" class="form-control form-control-sm" name="alasan_lainlain" placeholder="Keterangan lain...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- CATATAN -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label-sm">Catatan Tambahan</label>
                                    <textarea class="form-control form-control-sm" name="catatan" rows="2"
                                              placeholder="Catatan (opsional)"></textarea>
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
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" name="as_draft" value="1" class="btn btn-secondary mr-2">
                                        <i class="fas fa-save"></i> Simpan sebagai Draft
                                    </button>
                                    <button type="submit" name="as_draft" value="0" class="btn btn-danger mr-2" id="btnAjukan">
                                        <i class="fas fa-paper-plane"></i> Simpan & Ajukan ke Koor SC
                                    </button>
                                    <a href="<?= base_url('retur_penjualan') ?>" class="btn btn-light">
                                        <i class="fas fa-arrow-left"></i> Batal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!-- end card -->

                </form>

                <!-- TEMPLATE ROW (hidden) -->
                <table id="rowTemplate" style="display:none;">
                    <tbody>
                        <tr class="item-row">
                            <td class="text-center row-no font-weight-bold">1</td>
                            <td><input type="text" class="form-control form-control-sm" name="nama_barang[]" placeholder="Nama barang"></td>
                            <td><input type="text" class="form-control form-control-sm" name="no_faktur[]" placeholder="No. Faktur"></td>
                            <td><input type="text" class="form-control form-control-sm" name="no_batch[]" placeholder="No. Batch / Lot"></td>
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

    // ---- Add row ----
    function renumberRows() {
        $('#rowContainer .item-row').each(function(i) {
            $(this).find('.row-no').text(i + 1);
        });
    }

    function addRow() {
        var tmpl = $('#rowTemplate tbody tr.item-row').clone();
        $('#rowContainer').append(tmpl);
        renumberRows();
        bindRowEvents(tmpl);
    }

    function bindRowEvents($row) {
        // Delete row
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

    // Init dengan 1 baris
    addRow();

    // ---- Konfirmasi ajukan ----
    $('#btnAjukan').on('click', function(e) {
        var nama = $('#kd_customer').val();
        if (!nama) {
            e.preventDefault();
            alert('Pilih Customer terlebih dahulu!');
            return;
        }
        var items = 0;
        $('#rowContainer .item-row input[name="nama_barang[]"]').each(function(){
            if ($(this).val().trim()) items++;
        });
        if (items === 0) {
            e.preventDefault();
            alert('Minimal isi 1 baris nama barang!');
            return;
        }
        if (!confirm('Ajukan SPR ke Koor SC? Setelah diajukan tidak dapat diedit.')) {
            e.preventDefault();
        }
    });
});
</script>
