<!-- views/content/sales/so_form.php -->
<?php
    $is_edit   = !empty($so);
    $id_so_val = $is_edit ? ($so['no_so'] ?? '') : ($no_so ?? '');
    $action    = $is_edit
        ? base_url('sales_order/update/' . $so['id_so'])
        : base_url('sales_order/store');

    $batas_ton = isset($batas_tonase)   ? $batas_tonase   : 6;
    $batas_kub = isset($batas_kubikasi) ? $batas_kubikasi : 9;
    $gid_aktif = $is_edit ? ($so['gudang_id'] ?? '') : ($gudang_id ?? '');

    function escAttr($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
    function escJs($v)   {
        return str_replace(['\\','\'',"\r","\n","\t"], ['\\\\',"\\'", '','',''], (string)$v);
    }
?>
<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-<?= $is_edit ? 'pencil-alt' : 'plus-circle' ?> mr-2"></i>
                            <?= $is_edit ? 'Edit Sales Order' : 'Buat Sales Order Baru' ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                            <li class="breadcrumb-item active"><?= $is_edit ? 'Edit' : 'Buat' ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <?= $this->session->flashdata('warning') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <form action="<?= $action ?>" method="post" id="form-so">

                    <div class="row so-modern-grid">
                        <!-- INFORMASI SO -->
                        <div class="col-md-6">
                            <div class="card so-panel so-info-panel">
                                <div class="card-header so-panel-header">
                                    <span class="so-panel-icon so-panel-icon-primary"><i class="fas fa-file-invoice"></i></span>
                                    <div>
                                        <h3 class="so-panel-title">Informasi SO</h3>
                                        <div class="so-panel-subtitle">Data dasar sales order dan lokasi stok.</div>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="form-group so-form-row">
                                        <label class="so-form-label">No SO <span class="text-danger">*</span></label>
                                        <div class="so-form-control">
                                            <input type="text" class="form-control" name="no_so"
                                                value="<?= escAttr($id_so_val) ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group so-form-row">
                                        <label class="so-form-label">Tanggal <span class="text-danger">*</span></label>
                                        <div class="so-form-control">
                                            <input type="date" class="form-control" name="tanggal" required
                                                value="<?= $is_edit ? escAttr($so['tanggal_transaksi']) : date('Y-m-d') ?>">
                                        </div>
                                    </div>

                                    <div class="form-group so-form-row">
                                        <label class="so-form-label">Customer <span class="text-danger">*</span></label>
                                        <div class="so-form-control">
                                            <div class="input-group">
                                                <!-- BARU — hapus button, fokus ke input langsung buka modal -->
                                                <input type="text" id="customer_display" class="form-control"
                                                    placeholder="-- Pilih Customer --"
                                                    value="<?= $is_edit ? escAttr($so['customer_name']) : '' ?>"
                                                    readonly style="background:#fff;cursor:pointer"
                                                    tabindex="0">
                                            </div>
                                            <input type="hidden" name="customer_id"   id="customer_id"
                                                value="<?= $is_edit ? escAttr($so['kd_customer'] ?? '') : '' ?>">
                                            <input type="hidden" name="customer_name" id="customer_name"
                                                value="<?= $is_edit ? escAttr($so['customer_name']) : '' ?>">
                                            <div id="customer_validation" class="text-danger small mt-1" style="display:none">
                                                <i class="fas fa-exclamation-circle"></i> Pilih customer terlebih dahulu.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group so-form-row">
                                        <label class="so-form-label">Gudang <span class="text-danger">*</span></label>
                                        <div class="so-form-control">
                                            <select name="gudang_id" id="gudang_id_input" class="form-control" required>
                                                <option value="">-- Pilih Gudang --</option>
                                                <?php foreach ($gudang_list as $g): ?>
                                                    <option value="<?= escAttr($g['id_gudang']) ?>"
                                                        <?= ((string)$gid_aktif === (string)$g['id_gudang']) ? 'selected' : '' ?>>
                                                        <?= escAttr($g['nama_gudang']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small id="gudang-hint" class="text-warning d-none">
                                                <i class="fas fa-exclamation-triangle"></i> Pilih gudang sebelum menambah barang.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="form-group so-form-row mb-0">
                                        <label class="so-form-label">Catatan</label>
                                        <div class="so-form-control">
                                            <textarea name="catatan" class="form-control" rows="2"><?= $is_edit ? htmlspecialchars($so['catatan'] ?? '') : '' ?></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group so-form-row mb-0 mt-2">
                                        <label class="so-form-label">Faktur</label>
                                        <div class="so-form-control">
                                            <input type="hidden" name="is_faktur_z" value="0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox"
                                                       class="custom-control-input"
                                                       id="is_faktur_z"
                                                       name="is_faktur_z"
                                                       value="1"
                                                       <?= !empty($so['is_faktur_z']) ? 'checked' : '' ?>>
                                                <label class="custom-control-label" for="is_faktur_z">
                                                    Faktur Z
                                                </label>
                                            </div>
                                            <small class="text-muted">
                                                Jika dicentang, nomor faktur dari SO ini otomatis memakai awalan Z.
                                            </small>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- TONASE & KUBIKASI -->
                        <div class="col-md-6">
                            <div class="card so-panel so-metric-panel">
                                <div class="card-header so-panel-header">
                                    <span class="so-panel-icon so-panel-icon-info"><i class="fas fa-chart-line"></i></span>
                                    <div>
                                        <h3 class="so-panel-title">Tonase &amp; Kubikasi</h3>
                                        <div class="so-panel-subtitle">Pantau batas muatan sebelum SO disimpan.</div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="so-progress-block">
                                        <div class="so-progress-head">
                                            <span><i class="fas fa-weight mr-1"></i>Tonase</span>
                                            <span class="so-progress-value">
                                                <strong><span id="lbl-tonase">0,000</span></strong> / <?= $batas_ton ?> ton
                                                <span id="lbl-tonase-warn" class="badge badge-danger ml-1 d-none">PENUH</span>
                                            </span>
                                        </div>
                                        <div class="progress so-progress">
                                            <div class="progress-bar bg-success progress-bar-striped" id="tonase-bar"
                                                role="progressbar" style="width:0%;">0%</div>
                                        </div>
                                        <div class="so-progress-foot">
                                            <span>Terpakai: <span id="lbl-tonase-used">0,000</span> ton</span>
                                            <span>Sisa: <strong id="lbl-tonase-sisa" class="text-success"><?= $batas_ton ?> ton</strong></span>
                                        </div>
                                    </div>
                                    <div class="so-progress-block mb-2">
                                        <div class="so-progress-head">
                                            <span><i class="fas fa-cube mr-1"></i>Kubikasi</span>
                                            <span class="so-progress-value">
                                                <strong><span id="lbl-kubikasi">0,00000</span></strong> / <?= $batas_kub ?> m3
                                                <span id="lbl-kubikasi-warn" class="badge badge-danger ml-1 d-none">PENUH</span>
                                            </span>
                                        </div>
                                        <div class="progress so-progress">
                                            <div class="progress-bar bg-info progress-bar-striped" id="kubikasi-bar"
                                                role="progressbar" style="width:0%;">0%</div>
                                        </div>
                                        <div class="so-progress-foot">
                                            <span>Terpakai: <span id="lbl-kubikasi-used">0,00000</span> m3</span>
                                            <span>Sisa: <strong id="lbl-kubikasi-sisa" class="text-success"><?= $batas_kub ?> m3</strong></span>
                                        </div>
                                    </div>
                                    <div class="so-total-strip">
                                        <span>Total pcs <b id="lbl-total-kecil">0</b></span>
                                        <span>Box <b id="lbl-total-box">0</b></span>
                                        <span>Eceran <b id="lbl-total-ecer">0</b> pcs</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABEL ITEM BARANG -->
                    <div class="card so-panel so-items-panel">
                        <div class="card-header so-panel-header">
                            <span class="so-panel-icon so-panel-icon-success"><i class="fas fa-boxes"></i></span>
                            <div>
                                <h3 class="so-panel-title">Item Barang</h3>
                                <div class="so-panel-subtitle">Daftar barang, batch, qty, harga, dan subtotal SO.</div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive so-item-table-shell">
                                <table class="table table-sm mb-0 so-item-table" id="tbl-item">
                                    <thead>
                                        <tr>
                                            <th style="min-width:220px">Barang</th>
                                            <th style="min-width:155px">Expired / Lot</th>
                                            <th style="width:85px" class="text-center">Qty Box</th>
                                            <th style="width:85px" class="text-center">+Eceran</th>
                                            <th style="width:80px" class="text-center">Pcs</th>
                                            <th style="width:60px">Satuan</th>
                                            <th style="width:160px">Harga/Pcs</th>
                                            <th style="width:65px" class="text-center">Disc%</th>
                                            <th style="width:110px" class="text-right">Stl&nbsp;Disc</th>
                                            <th style="width:115px" class="text-right">Subtotal</th>
                                            <th style="width:36px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="item-body"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="9" class="text-right">GRAND TOTAL</td>
                                            <td class="text-right" id="total-grand">0</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- TOMBOL AKSI di pojok kanan bawah & kiri bawah -->
                    <div class="row mt-3 so-action-row">
                        <div class="col-6">
                            <!-- Tombol Tambah Baris di pojok kiri bawah -->
                            <button type="button" class="btn btn-success so-action-btn" id="btn-add-row">
                                <i class="fas fa-plus"></i> Tambah Baris
                            </button>
                        </div>
                        <div class="col-6 text-right">
                            <!-- Tombol Batal & Simpan di pojok kanan bawah -->
                             <button type="submit" class="btn btn-primary so-action-btn" id="btn-submit">
                                <i class="fas fa-save"></i> Simpan SO
                            </button>
                            <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary so-action-btn mr-2">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </div>
                </form>
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

<!-- MODAL: Pilih Barang -->
<div class="modal fade" id="modal-stock" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header stock-modal-header">
                <div class="d-flex align-items-center">
                    <span class="stock-modal-icon"><i class="fas fa-box-open"></i></span>
                    <div>
                        <h5 class="stock-modal-title">Pilih Barang</h5>
                        <div class="stock-modal-subtitle">Stok tersedia per batch, expired date, dan lot.</div>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="stock-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="stock-search" class="form-control" placeholder="Cari kode atau nama barang...">
                </div>
                <div class="stock-table-shell">
                    <table class="table table-sm mb-0" id="tbl-stock-pick">
                        <thead>
                            <tr>
                                <th class="col-stock-name">Nama Barang</th><th class="col-stock-exp">Exp Date</th><th class="col-stock-lot">No Lot</th>
                                <th class="text-right">Avail Box</th><th class="text-right">Sisa Ecer</th>
                                <th class="text-right">Total Pcs</th><th class="text-center">Isi/Box</th>
                                <th>Satuan</th>
                                <th class="col-stock-hidden">Berat/pcs</th>
                                <th class="col-stock-hidden">Kubik/pcs</th>
                                <th class="col-stock-hidden">Gudang</th>
                                <th class="text-center">Pilih</th>
                            </tr>
                        </thead>
                        <tbody id="stock-body">
                            <tr><td colspan="12" class="text-center text-muted py-4">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Memuat...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Pilih Customer -->
<div class="modal fade" id="modal-customer" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header customer-modal-header">
                <div class="d-flex align-items-center">
                    <span class="customer-modal-icon"><i class="fas fa-store"></i></span>
                    <div>
                        <h5 class="customer-modal-title">Pilih Customer</h5>
                        <div class="customer-modal-subtitle">Pilih customer berdasarkan nama, kios, atau rute.</div>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="customer-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="customer-search" class="form-control" placeholder="Cari nama customer, kios, atau kode rute...">
                </div>
                <div class="customer-table-shell">
                    <table class="table table-sm mb-0" id="tbl-customer-pick">
                        <thead>
                            <tr>
                                <th style="width:50%">Customer</th><th style="width:32%">Nama Kios</th><th style="width:18%">KD Rute</th>
                            </tr>
                        </thead>
                        <tbody id="customer-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var BASE_URL       = '<?= base_url() ?>';
var BATAS_TONASE   = <?= (float)$batas_ton ?>;
var BATAS_KUBIKASI = <?= (float)$batas_kub ?>;

<?php
$edit_details_safe = [];
if ($is_edit && !empty($details)) {
    foreach ($details as $d) {
        $safe = [];
        foreach ($d as $k => $v) {
            $safe[$k] = is_string($v) ? mb_convert_encoding($v,'UTF-8','UTF-8') : $v;
        }
        $edit_details_safe[] = $safe;
    }
}
?>
var EDIT_DETAILS = <?= json_encode(array_values($edit_details_safe), JSON_HEX_QUOT|JSON_HEX_APOS|JSON_UNESCAPED_UNICODE) ?>;

function salesToast(type, message) {
    if (window.Swal) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type || 'info',
            title: String(message || '').indexOf('<') >= 0 ? undefined : (message || ''),
            html: String(message || '').indexOf('<') >= 0 ? message : undefined,
            timer: 2600,
            showConfirmButton: false,
            timerProgressBar: true
        });
    } else {
        alert(String(message || '').replace(/<br\s*\/?>/gi, '\n'));
    }
}

function salesConfirm(options) {
    options = options || {};
    if (window.Swal) {
        return Swal.fire({
            title: options.title || 'Konfirmasi',
            text: options.text || 'Lanjutkan proses ini?',
            icon: options.icon || 'question',
            showCancelButton: true,
            confirmButtonText: options.confirmText || 'Ya',
            cancelButtonText: 'Batal',
            confirmButtonColor: options.confirmColor || '#007bff',
            cancelButtonColor: '#6c757d'
        }).then(function(result){ return result.isConfirmed; });
    }
    return Promise.resolve(confirm((options.title ? options.title + '\n' : '') + (options.text || 'Lanjutkan proses ini?')));
}

function salesLoading(show, text) {
    // sengaja no-op: proyek sudah memakai Bootstrap/AdminLTE tanpa overlay tambahan
}

<?php
$customers_safe = [];
foreach ($customers as $c) {
    $cs = [];
    foreach ($c as $k => $v) { $cs[$k] = is_string($v) ? mb_convert_encoding($v,'UTF-8','UTF-8') : $v; }
    $customers_safe[] = $cs;
}
?>
var CUSTOMERS = <?= json_encode(array_values($customers_safe), JSON_HEX_QUOT|JSON_HEX_APOS|JSON_UNESCAPED_UNICODE) ?>;

<?php
$approver_safe = [];
foreach (($approver_list ?? []) as $a) {
    $approver_safe[] = [
        'nm'      => mb_convert_encoding($a['nm_karyawan'] ?? '', 'UTF-8', 'UTF-8'),
        'jobdesk' => mb_convert_encoding($a['jobdesk']     ?? '', 'UTF-8', 'UTF-8'),
    ];
}
?>
var APPROVER_LIST = <?= json_encode($approver_safe, JSON_HEX_QUOT|JSON_HEX_APOS|JSON_UNESCAPED_UNICODE) ?>;

<?php
$gudang_safe = [];
foreach(($gudang_list ?? []) as $g) {
    $gudang_safe[] = [
        'id_gudang'   => (string)$g['id_gudang'],
        'nama_gudang' => mb_convert_encoding($g['nama_gudang'] ?? '', 'UTF-8', 'UTF-8'),
    ];
}
?>
var GUDANG_LIST = <?= json_encode($gudang_safe, JSON_HEX_QUOT|JSON_HEX_APOS|JSON_UNESCAPED_UNICODE) ?>;
var GUDANG_AWAL = '<?= escJs((string)$gid_aktif) ?>';

/* ── State ── */
var currentRowIdx = null;   // baris yang sedang aktif untuk modal
var stockCache    = [];
var stockLoaded   = false;  // flag: stok sudah dimuat untuk gudang saat ini
var rowIdx        = 0;

/* ================================================================
   UTILS
================================================================ */
function fmtNum(n, dec) {
    if (dec === undefined) dec = 2;
    return (parseFloat(n)||0).toLocaleString('id-ID', {minimumFractionDigits:dec, maximumFractionDigits:dec});
}
function formatTgl(ymd) {
    if (!ymd) return '-';
    var p = String(ymd).split('-');
    return p.length === 3 ? p[2]+'/'+p[1]+'/'+p[0] : ymd;
}
function isExpiringSoon(ymd) {
    if (!ymd) return false;
    return (new Date(ymd) - new Date()) / 86400000 <= 30;
}
function esc(v) {
    return String(v||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;')
        .replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function getIsi(idx) {
    var el = document.getElementById('isi_'+idx);
    var v  = el ? parseInt(el.value) : 1;
    return (v > 0) ? v : 1;
}

/* ================================================================
   BUILD ROW HTML
================================================================ */
function buatBaris(idx, d) {
    d = d || {};
    var kd     = d.kd_barang    || '';
    var nm     = d.nama_barang  || '';
    var exp    = d.expired_date || '';
    var lot    = d.no_lot       || '';
    var sat    = d.satuan       || '';
    var hrg    = parseFloat(d.hrg_satuan  || 0);
    var pk     = parseFloat(d.hrg_pokok   || 0);
    var disc   = parseFloat(d.disc        || 0);
    var akun   = d.kode_akun    || '';
    var beratG = parseFloat(d.berat_gram  || 0);
    var kubikM = parseFloat(d.kubikasi_m3 || 0);
    var isi    = parseInt(d.isi_per_box   || 1); if (isi < 1) isi = 1;
    var gudang = d.gudang_id || '';

    var qtyKecilDB = parseFloat(d.qty        || 0);
    var qtyBox     = parseFloat(d.qty_box    || 0);
    var qtySat     = parseFloat(d.qty_satuan || 0);
    if (!d.qty_box && qtyKecilDB > 0) {
        qtyBox = Math.floor(qtyKecilDB / isi);
        qtySat = qtyKecilDB % isi;
    }

    var av     = parseFloat(d.available_stock || 0);
    var avBox  = Math.floor(av / isi);
    var avEcer = Math.floor(av % isi);
    var qtyKecil  = (qtyBox * isi) + qtySat;
    var subBefore = hrg * qtyKecil;
    var subDisc   = subBefore * (1 - disc / 100);
    var sub       = subDisc;
    var expSoon   = exp && isExpiringSoon(exp);
    var hargaClass = (hrg > 0 && pk > 0 && Math.abs(hrg - pk) > 0.001) ? 'text-danger' : '';
    var approveBy  = d.approve_by || '';

    var expOption = exp
        ? '<option value="'+esc(exp)+'" data-lot="'+esc(lot)+'" data-av="'+av
          +'" data-ton="'+beratG+'" data-kub="'+kubikM+'" data-isi="'+isi
          +'" data-gudang="'+esc(String(gudang))+'" selected>'
          + esc(formatTgl(exp)) + (lot ? ' | Lot: '+esc(lot) : '') + (expSoon ? ' ⚠' : '')
          + '</option>'
        : '';

    var h = '<tr id="row-'+idx+'" data-idx="'+idx+'">';

    /* 0 Barang */
    h += '<td>'
       + '<input type="hidden" name="produk_id[]"   value="'+esc(kd)+'">'
       + '<input type="hidden" name="kd_barang[]"   id="kd_'+idx+'" value="'+esc(kd)+'">'
       + '<input type="hidden" name="nama_barang[]" id="nm_'+idx+'" value="'+esc(nm)+'">'
       + '<input type="hidden" name="satuan[]"      id="sat_'+idx+'" value="'+esc(sat)+'">'
       + '<input type="hidden" name="hrg_pokok[]"   id="pk_'+idx+'" value="'+pk+'">'
       + '<input type="hidden" name="kode_akun[]"   value="'+esc(akun)+'">'
       + '<input type="hidden" name="berat_gram[]"  id="bg_'+idx+'" value="'+beratG+'">'
       + '<input type="hidden" name="kubikasi_m3[]" id="km_'+idx+'" value="'+kubikM+'">'
       + '<input type="hidden" name="isi_per_box[]" id="isi_'+idx+'" value="'+isi+'">'
       + '<input type="hidden" name="pajak[]"       value="0">'
       + '<div class="d-flex align-items-center">'
       +   '<div class="flex-grow-1">'
       +     '<small class="text-muted" id="kdlbl_'+idx+'">'+(kd||'&mdash;')+'</small><br>'
       +     '<span id="nmlbl_'+idx+'">'+(nm ? esc(nm) : '<span class="text-muted small">← Klik Pilih</span>')+'</span>'
       +   '</div>'
       +   '<button type="button" class="btn btn-xs btn-outline-primary ml-1 btn-pick" data-idx="'+idx+'">'
       +   '<i class="fas fa-search"></i> Pilih</button>'
       + '</div>'
       + '<div class="mt-1 small">'
       +   '<span class="text-success font-weight-bold">Stock: '
       +     '<span id="avail-box_'+idx+'" data-avail-box="'+avBox+'">'+fmtNum(avBox,0)+'</span> box'
       +   '</span>'
       +   ' + <span class="text-info"><span id="avail-ecer_'+idx+'" data-avail-ecer="'+avEcer
       +     '" data-avail-total="'+av+'">'+fmtNum(avEcer,0)+'</span> pcs ecer</span>'
       + '</div>'
       + '</td>';

    /* 1 Expired/Lot */
    h += '<td>'
       + '<select name="expired_date[]" id="exp_'+idx+'" class="form-control form-control-sm mb-1'
       + (expSoon?' border-danger text-danger':'')+'" required>'
       + '<option value="">-- Pilih barang --</option>'
       + expOption + '</select>'
       + '<input type="hidden" name="no_lot[]" id="lot_'+idx+'" value="'+esc(lot)+'">'
       + '<div class="input-group input-group-sm mt-1">'
       +   '<div class="input-group-prepend"><span class="input-group-text py-0 px-1" style="font-size:10px">Lot</span></div>'
       +   '<input type="text" id="lotlbl_'+idx+'" class="form-control form-control-sm py-0" value="'+esc(lot)+'" readonly placeholder="-" style="font-size:11px">'
       + '</div>'
       + '<div id="expwarn_'+idx+'" class="mt-1">'
       + (expSoon ? '<small class="text-danger"><i class="fas fa-exclamation-triangle"></i> &lt;30 hr</small>' : '')
       + '</div></td>';

    /* 2 Qty Box */
    h += '<td><input type="number" step="1" min="0" name="qty_box[]" id="qtybox_'+idx+'"'
       + ' class="form-control form-control-sm text-center" value="'+qtyBox+'">'
       + '<small class="text-muted d-block text-center">maks <span id="maxbox_'+idx+'">'+fmtNum(avBox,0)+'</span></small></td>';

    /* 3 Eceran */
    h += '<td><input type="number" step="1" min="0" name="qty_satuan[]" id="qtyecer_'+idx+'"'
       + ' class="form-control form-control-sm text-center" value="'+qtySat+'">'
       + '<small class="text-muted d-block text-center">maks <span id="maxecer_'+idx+'">'+fmtNum(avEcer,0)+'</span></small></td>';

    /* 4 Total Pcs */
    h += '<td class="text-center align-middle"><b id="qtylbl_'+idx+'">'+fmtNum(qtyKecil,0)+'</b><br><small class="text-muted">pcs</small></td>';

    /* 5 Satuan */
    h += '<td><input type="text" id="satlbl_'+idx+'" class="form-control form-control-sm" value="'+esc(sat)+'" readonly></td>';

    /* 6 Harga */
    h += '<td>'
       + '<input type="number" step="0.01" min="0" name="hrg_satuan[]" id="hrg_'+idx+'"'
       + ' class="form-control form-control-sm '+hargaClass+'" value="'+(hrg||'')+'" required>'
       + '<div id="hrgwarn_'+idx+'" class="mt-1"></div>'
       + '<div id="approver-wrap_'+idx+'" style="display:none" class="mt-1">'
       + '<select name="approve_by[]" id="approve_by_'+idx+'" class="form-control form-control-sm">'
       + '<option value="">-- Pilih Approver --</option>';
    APPROVER_LIST.forEach(function(a) {
        h += '<option value="'+esc(a.nm)+'"'+(a.nm===approveBy?' selected':'')+'>'+esc(a.nm+(a.jobdesk?' — '+a.jobdesk:''))+'</option>';
    });
    h += '</select></div></td>';

    /* 7 Disc */
    h += '<td><input type="number" step="0.01" min="0" max="100" name="disc[]" id="disc_'+idx+'"'
       + ' class="form-control form-control-sm text-center" value="'+disc+'"></td>';

    /* 8 Stl Disc */
    h += '<td class="text-right align-middle"><small class="text-muted d-block" style="font-size:10px">stl disc</small><b id="subdisc_'+idx+'">'+fmtNum(subDisc)+'</b></td>';

    /* 9 Subtotal */
    h += '<td class="text-right align-middle"><b id="sub_'+idx+'">'+fmtNum(sub)+'</b></td>';

    /* 10 Hapus */
    h += '<td class="text-center align-middle"><button type="button" class="btn btn-xs btn-danger btn-remove" data-idx="'+idx+'"><i class="fas fa-trash"></i></button></td>';

    h += '</tr>';
    return h;
}

/* ================================================================
   TAMBAH BARIS — mengembalikan idx baris yang baru dibuat
================================================================ */
function tambahBaris(d) {
    document.getElementById('item-body').insertAdjacentHTML('beforeend', buatBaris(rowIdx, d || {}));
    bindBaris(rowIdx);
    return rowIdx++;   // ← return idx, LALU increment
}

function bindBaris(idx) {
    ['hrg_','qtybox_','qtyecer_','disc_'].forEach(function(f) {
        var el = document.getElementById(f+idx);
        if (!el) return;
        el.addEventListener('input',  function(){ hitungBaris(idx); });
        el.addEventListener('change', function(){ hitungBaris(idx); });
    });

    var elHarga = document.getElementById('hrg_'+idx);
    if (elHarga) {
        elHarga.addEventListener('blur', function() {
            if (String(this.value).trim() !== '') return;
            var pk = parseFloat((document.getElementById('pk_'+idx) || {value: 0}).value) || 0;
            if (pk > 0) {
                this.value = pk;
                hitungBaris(idx);
            }
        });
    }

    /* Validasi qty box */
    var elBox = document.getElementById('qtybox_'+idx);
    if (elBox) elBox.addEventListener('change', function() {
        var elE   = document.getElementById('avail-ecer_'+idx);
        var avTot = elE ? parseFloat(elE.dataset.availTotal||0) : 0;
        var maxB  = Math.floor(avTot / getIsi(idx));
        var v     = parseInt(this.value)||0;
        if (v > maxB) { this.value = maxB; hitungBaris(idx); salesToast('warning', 'Qty box melebihi stok. Maks ' + maxB + ' box.'); }
    });

    /* Validasi qty eceran */
    var elEcer = document.getElementById('qtyecer_'+idx);
    if (elEcer) elEcer.addEventListener('change', function() {
        var elE   = document.getElementById('avail-ecer_'+idx);
        var avTot = elE ? parseFloat(elE.dataset.availTotal||0) : 0;
        var qBox  = parseInt((document.getElementById('qtybox_'+idx)||{value:0}).value)||0;
        var sisa  = avTot - (qBox * getIsi(idx));
        var v     = parseInt(this.value)||0;
        if (v > sisa) {
            this.value = Math.max(0, Math.floor(sisa));
            hitungBaris(idx);
            salesToast('warning', sisa<=0 ? 'Stok habis.' : 'Maks ' + Math.floor(sisa) + ' pcs eceran.');
        }
    });

    /* Ganti expired */
    var sel = document.getElementById('exp_'+idx);
    if (sel) sel.addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) return;
        var av  = parseFloat(opt.dataset.av  || 0);
        var lot = opt.dataset.lot || '';
        var bg  = parseFloat(opt.dataset.ton || 0);
        var km  = parseFloat(opt.dataset.kub || 0);
        var isi = parseInt(opt.dataset.isi   || 1); if (isi < 1) isi = 1;

        document.getElementById('bg_' +idx).value = bg;
        document.getElementById('km_' +idx).value = km;
        document.getElementById('isi_'+idx).value = isi;
        document.getElementById('lot_'+idx).value = lot;
        var elLL = document.getElementById('lotlbl_'+idx); if (elLL) elLL.value = lot;

        var avBox  = Math.floor(av / isi);
        var avEcer = Math.floor(av % isi);
        var elB = document.getElementById('avail-box_'+idx);
        var elE = document.getElementById('avail-ecer_'+idx);
        if (elB) { elB.textContent = fmtNum(avBox,0); elB.dataset.availBox = avBox; }
        if (elE) { elE.textContent = fmtNum(avEcer,0); elE.dataset.availEcer = avEcer; elE.dataset.availTotal = av; }
        var mxB = document.getElementById('maxbox_'+idx);
        var mxE = document.getElementById('maxecer_'+idx);
        if (mxB) mxB.textContent = fmtNum(avBox,0);
        if (mxE) mxE.textContent = fmtNum(avEcer,0)+' pcs';

        var soon = opt.value && isExpiringSoon(opt.value);
        var ew   = document.getElementById('expwarn_'+idx);
        if (ew)  ew.innerHTML = soon ? '<small class="text-danger"><i class="fas fa-exclamation-triangle"></i> &lt;30 hr</small>' : '';
        soon ? sel.classList.add('border-danger','text-danger') : sel.classList.remove('border-danger','text-danger');
        hitungBaris(idx);
    });
}

/* ================================================================
   KALKULASI
================================================================ */
function hitungBaris(idx) {
    function v(id) { var e=document.getElementById(id); return e?parseFloat(e.value)||0:0; }
    var hrg=v('hrg_'+idx), qBox=v('qtybox_'+idx), qSat=v('qtyecer_'+idx);
    var disc=v('disc_'+idx), pk=v('pk_'+idx);
    var isi=getIsi(idx);
    var isNego = hrg>0 && pk>0 && Math.abs(hrg-pk)>0.001;
    var elH = document.getElementById('hrg_'+idx);
    if (elH) isNego ? elH.classList.add('text-danger') : elH.classList.remove('text-danger');

    var qK   = (qBox*isi)+qSat;
    var sD   = hrg*qK*(1-disc/100);
    var tot  = sD;

    var elQ  = document.getElementById('qtylbl_' +idx);
    var elSD = document.getElementById('subdisc_'+idx);
    var elS  = document.getElementById('sub_'    +idx);
    if (elQ)  elQ.textContent  = fmtNum(qK,0);
    if (elSD) elSD.textContent = fmtNum(sD);
    if (elS)  elS.textContent  = fmtNum(tot);

    var wEl = document.getElementById('hrgwarn_'+idx);
    var aW  = document.getElementById('approver-wrap_'+idx);
    var aS  = document.getElementById('approve_by_'+idx);
    if (wEl) wEl.innerHTML = isNego ? '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Perlu Persetujuan</span>' : '';
    if (aW)  { aW.style.display = isNego?'':'none'; if(aS){ aS.required=isNego; if(!isNego) aS.value=''; } }

    hitungGrand(); hitungTK();
}

function hitungGrand() {
    var g=0;
    document.querySelectorAll('#item-body tr').forEach(function(tr) {
        var i=tr.dataset.idx;
        var hrg=parseFloat((document.getElementById('hrg_'+i)||{value:0}).value)||0;
        var qB =parseFloat((document.getElementById('qtybox_'+i)||{value:0}).value)||0;
        var qE =parseFloat((document.getElementById('qtyecer_'+i)||{value:0}).value)||0;
        var d  =parseFloat((document.getElementById('disc_'+i)||{value:0}).value)||0;
        var qK =(qB*getIsi(i))+qE;
        g += hrg*qK*(1-d/100);
    });
    document.getElementById('total-grand').textContent = fmtNum(g);
}

function hitungTK() {
    var tT=0,tK=0,tP=0,tB=0,tE=0;
    document.querySelectorAll('#item-body tr').forEach(function(tr) {
        var i=tr.dataset.idx;
        var qB=parseFloat((document.getElementById('qtybox_'+i)||{value:0}).value)||0;
        var qE=parseFloat((document.getElementById('qtyecer_'+i)||{value:0}).value)||0;
        var bg=parseFloat((document.getElementById('bg_'+i)||{value:0}).value)||0;
        var km=parseFloat((document.getElementById('km_'+i)||{value:0}).value)||0;
        var qK=(qB*getIsi(i))+qE;
        tT+=qK*bg/1000000; tK+=qK*km; tP+=qK; tB+=qB; tE+=qE;
    });
    document.getElementById('lbl-tonase').textContent      = fmtNum(tT,3);
    document.getElementById('lbl-kubikasi').textContent    = fmtNum(tK,5);
    var tonUsed = document.getElementById('lbl-tonase-used');
    var kubUsed = document.getElementById('lbl-kubikasi-used');
    if (tonUsed) tonUsed.textContent = fmtNum(tT,3);
    if (kubUsed) kubUsed.textContent = fmtNum(tK,5);
    var sisaT = Math.max(0, BATAS_TONASE - tT);
    var sisaK = Math.max(0, BATAS_KUBIKASI - tK);
    var sisaTEl = document.getElementById('lbl-tonase-sisa');
    var sisaKEl = document.getElementById('lbl-kubikasi-sisa');
    if (sisaTEl) { sisaTEl.textContent = fmtNum(sisaT,3) + ' ton'; sisaTEl.className = tT > BATAS_TONASE ? 'text-danger' : 'text-success'; }
    if (sisaKEl) { sisaKEl.textContent = fmtNum(sisaK,4) + ' m3'; sisaKEl.className = tK > BATAS_KUBIKASI ? 'text-danger' : 'text-success'; }
    document.getElementById('lbl-total-kecil').textContent = fmtNum(tP,0);
    document.getElementById('lbl-total-box').textContent   = fmtNum(tB,0);
    document.getElementById('lbl-total-ecer').textContent  = fmtNum(tE,0);

    var bT=document.getElementById('tonase-bar');
    var bK=document.getElementById('kubikasi-bar');
    var pctT = BATAS_TONASE>0 ? Math.min(tT/BATAS_TONASE*100,100) : 0;
    var pctK = BATAS_KUBIKASI>0 ? Math.min(tK/BATAS_KUBIKASI*100,100) : 0;
    if(bT){
        bT.style.width=pctT.toFixed(2)+'%';
        bT.className='progress-bar progress-bar-striped '+(tT>BATAS_TONASE?'bg-danger':(pctT>=80?'bg-warning':'bg-success'));
        bT.textContent=pctT.toFixed(1)+'%';
    }
    if(bK){
        bK.style.width=pctK.toFixed(2)+'%';
        bK.className='progress-bar progress-bar-striped '+(tK>BATAS_KUBIKASI?'bg-danger':(pctK>=80?'bg-warning':'bg-info'));
        bK.textContent=pctK.toFixed(1)+'%';
    }
    var wT=document.getElementById('lbl-tonase-warn');   if(wT) wT.classList.toggle('d-none',tT<=BATAS_TONASE);
    var wK=document.getElementById('lbl-kubikasi-warn'); if(wK) wK.classList.toggle('d-none',tK<=BATAS_KUBIKASI);
}

/* ================================================================
   LOAD STOK — pakai cache, tidak load ulang jika sudah ada
================================================================ */
function loadStock(callback) {
    var gid = document.getElementById('gudang_id_input').value;
    if (!gid) {
        document.getElementById('gudang-hint').classList.remove('d-none');
        salesToast('warning', 'Pilih gudang terlebih dahulu.');
        return;
    }
    document.getElementById('gudang-hint').classList.add('d-none');

    /* ── CACHE HIT: langsung render, tidak fetch ulang ── */
    if (stockLoaded && stockCache.length) {
        renderStock(stockCache);
        if (callback) callback();
        return;
    }

    document.getElementById('stock-body').innerHTML =
        '<tr><td colspan="12" class="text-center py-3"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat stok...</td></tr>';

    salesLoading(true, 'Memuat stok barang...');
    fetch(BASE_URL + 'sales_order/get_stock?gudang_id=' + encodeURIComponent(gid))
        .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
        .then(function(res){
            if (res.status !== 'ok') throw new Error(res.message||'Error server');
            stockCache  = res.data || [];
            stockLoaded = true;
            renderStock(stockCache);
            if (callback) callback();
        })
        .catch(function(err){
            document.getElementById('stock-body').innerHTML =
                '<tr><td colspan="12" class="text-center text-danger py-3">'+esc(err.message)+'</td></tr>';
        })
        .finally(function(){
            salesLoading(false);
        });
}

function renderStock(data) {
    var q = (document.getElementById('stock-search').value||'').toLowerCase();
    var list = q ? data.filter(function(d){
        return String(d.kd_barang||'').toLowerCase().indexOf(q)>=0 ||
               String(d.nama_barang||'').toLowerCase().indexOf(q)>=0;
    }) : data;

    if (!list.length) {
        document.getElementById('stock-body').innerHTML =
            '<tr><td colspan="12" class="text-center text-muted py-3">Tidak ada stok.</td></tr>';
        return;
    }
    var html='', lastKd=null;
    list.forEach(function(d){
        var kd=d.kd_barang||'', isNew=(kd!==lastKd); lastKd=kd;
        var isi=parseInt(d.isi_per_box||1);
        var avT=parseFloat(d.available_stock||0);
        var avB=parseInt(d.available_box||0);
        var avE=parseInt(d.available_ecer||0);
        var exp=d.exp_date||d.expired_date||'';
        var stockKey=d.stock_key||[String(d.stock_batch_id||d.id||''), kd, String(d.gudang_id||''), d.no_lot||'', exp].join('|');
        var gdgId=String(d.gudang_id||'');
        var gdgObj=GUDANG_LIST.filter(function(g){return String(g.id_gudang)===gdgId;})[0];
        var gdgNm=gdgObj?gdgObj.nama_gudang:(gdgId||'-');

        html+='<tr class="tr-pick-stock-row" tabindex="0" data-stock-key="'+esc(stockKey)+'">';
        html+='<td><span class="stock-code-badge">'+esc(kd)+'</span>'
            +'<span class="stock-name-main">'+esc(d.nama_barang||'')+'</span>'
            +(!isNew?'<span class="stock-name-sub">Lot lain untuk barang yang sama</span>':'')
            +'</td>';
        html+='<td>'+(exp?'<span class="stock-exp-badge '+(isExpiringSoon(exp)?'warn':'ok')+'">'+esc(formatTgl(exp))+'</span>':'<span class="text-muted">-</span>')+'</td>';
        html+='<td class="stock-lot-cell"><span class="stock-lot-pill">'+esc(d.no_lot||'-')+'</span></td>';
        html+='<td class="text-right"><span class="stock-metric text-success">'+fmtNum(avB,0)+'</span><span class="stock-metric-label">box</span></td>';
        html+='<td class="text-right"><span class="stock-metric text-info">'+fmtNum(avE,0)+'</span><span class="stock-metric-label">pcs</span></td>';
        html+='<td class="text-right"><span class="stock-metric">'+fmtNum(avT,0)+'</span><span class="stock-metric-label">pcs total</span></td>';
        html+='<td class="text-center"><span class="stock-isi-pill">'+isi+'</span></td>';
        html+='<td><span class="font-weight-bold text-secondary">'+esc(d.satuan||'-')+'</span></td>';
        html+='<td class="text-right"><small>'+(parseFloat(d.berat_gram||0)/1000).toFixed(3)+' kg</small></td>';
        html+='<td class="text-right"><small>'+parseFloat(d.kubikasi_m3||0).toFixed(6)+' m³</small></td>';
        html+='<td><small class="text-muted">'+esc(gdgNm)+'</small></td>';
        html+='<td class="text-center"><button type="button" class="btn btn-sm btn-primary btn-pick-stock"'
            +' data-kd="'+esc(kd)+'" data-nm="'+esc(d.nama_barang||'')+'" data-exp="'+esc(exp)+'"'
            +' data-lot="'+esc(d.no_lot||'')+'" data-sat="'+esc(d.satuan||'')+'" data-av="'+avT+'"'
            +' data-ton="'+parseFloat(d.berat_gram||0)+'" data-kub="'+parseFloat(d.kubikasi_m3||0)+'"'
            +' data-isi="'+isi+'" data-gudang="'+esc(gdgId)+'" data-pk="'+parseFloat(d.hpp||0)+'"'
            +' data-stock-key="'+esc(stockKey)+'">'
            +'<i class="fas fa-check mr-1"></i>Pilih</button></td>';
        html+='</tr>';
    });
    document.getElementById('stock-body').innerHTML = html;
}

/* ================================================================
   APPLY BARANG KE BARIS — dipanggil setelah user pilih dari modal
================================================================ */
function applyBarangKeBaris(i, btn) {
    var kd = btn.dataset.kd;
    var beratBarang = parseFloat(btn.dataset.ton || 0);
    var kubikBarang = parseFloat(btn.dataset.kub || 0);
    document.getElementById('kd_'    +i).value       = kd;
    document.getElementById('nm_'    +i).value       = btn.dataset.nm;
    document.getElementById('kdlbl_' +i).textContent = kd;
    document.getElementById('nmlbl_' +i).innerHTML   = esc(btn.dataset.nm);
    document.getElementById('pk_'    +i).value       = btn.dataset.pk;
    document.getElementById('sat_'   +i).value       = btn.dataset.sat;
    document.getElementById('satlbl_'+i).value       = btn.dataset.sat;
    document.getElementById('bg_'    +i).value       = beratBarang;
    document.getElementById('km_'    +i).value       = kubikBarang;

    var isiDef = parseInt(btn.dataset.isi||1); if(isiDef<1) isiDef=1;
    document.getElementById('isi_'+i).value = isiDef;

    /* Auto-fill HPP ke harga jika harga belum diisi */
    var hpp = parseFloat(btn.dataset.pk||0);
    var elH = document.getElementById('hrg_'+i);
    if (elH && hpp > 0 && !parseFloat(elH.value)) elH.value = hpp;

    /* Rebuild dropdown expired */
    var rows = stockCache.filter(function(s){ return (s.kd_barang||'')===kd; });
    var sel  = document.getElementById('exp_'+i);
    sel.innerHTML = '<option value="">-- Pilih Expired Date --</option>';
    rows.forEach(function(s){
        var opt=document.createElement('option');
        var ed=s.exp_date||s.expired_date||'';
        var stockKey=s.stock_key||[String(s.stock_batch_id||s.id||''), kd, String(s.gudang_id||''), s.no_lot||'', ed].join('|');
        opt.value=ed;
        var isiS=parseInt(s.isi_per_box||1);
        var avB=Math.floor(parseFloat(s.available_stock||0)/isiS);
        var avE=Math.floor(parseFloat(s.available_stock||0)%isiS);
        opt.textContent=formatTgl(ed)+(s.no_lot?' | Lot: '+s.no_lot:'')+' ['+fmtNum(avB,0)+' box + '+fmtNum(avE,0)+' pcs]';
        opt.dataset.ton=parseFloat(s.berat_gram||0);
        opt.dataset.kub=parseFloat(s.kubikasi_m3||0);
        opt.dataset.av =parseFloat(s.available_stock||0);
        opt.dataset.lot=s.no_lot||'';
        opt.dataset.isi=parseInt(s.isi_per_box||1);
        opt.dataset.gudang=s.gudang_id||'';
        opt.dataset.stockKey=stockKey;
        if (stockKey===(btn.dataset.stockKey||'')) opt.selected=true;
        sel.appendChild(opt);
    });
    if (rows.length===1) sel.selectedIndex=1;
    sel.dispatchEvent(new Event('change'));

    /* Reset qty hanya baris ini */
    document.getElementById('qtybox_' +i).value = 0;
    document.getElementById('qtyecer_'+i).value = 0;
    hitungBaris(i);
}

/* ================================================================
   RENDER CUSTOMER
================================================================ */
function renderCustomers(q) {
    q = (q||'').toLowerCase();
    var list = q ? CUSTOMERS.filter(function(c){
        return String(c.nama_customer||'').toLowerCase().indexOf(q) >= 0 ||
               String(c.nama_kios||'').toLowerCase().indexOf(q) >= 0 ||
               String(c.kd_rute||'').toLowerCase().indexOf(q) >= 0;
    }) : CUSTOMERS;
    if (!list.length) {
        document.getElementById('customer-body').innerHTML =
            '<tr><td colspan="3" class="text-center text-muted py-4">Tidak ada customer.</td></tr>';
        return;
    }
    var html = '';
    list.forEach(function(c){
        var nama = c.nama_customer || '';
        var initials = String(nama || '?').trim().split(/\s+/).slice(0, 2).map(function(part){
            return part.charAt(0);
        }).join('').toUpperCase() || '?';
        html += '<tr class="tr-pick-customer" tabindex="0" data-kd="'+esc(c.kd_customer)+'" data-nama="'+esc(c.nama_customer)+'"'
              + ' title="Klik untuk memilih">'
              + '<td><div class="d-flex align-items-center">'
              + '<span class="customer-avatar">'+esc(initials)+'</span>'
              + '<div><span class="customer-name-main">'+esc(nama)+'</span>'
              + '<span class="customer-code-sub">'+esc(c.kd_customer||'-')+'</span></div>'
              + '</div></td>'
              + '<td><span class="customer-kios-pill">'+esc(c.nama_kios||'-')+'</span></td>'
              + '<td><span class="customer-route-pill">'+esc(c.kd_rute||'-')+'</span></td>'
              + '</tr>';
    });
    document.getElementById('customer-body').innerHTML = html;
}

function focusTableRow(selector, current, step) {
    var rows = Array.prototype.slice.call(document.querySelectorAll(selector));
    if (!rows.length) return;
    var idx = current ? rows.indexOf(current) : -1;
    var next = idx < 0 ? 0 : Math.max(0, Math.min(rows.length - 1, idx + step));
    rows[next].focus();
    rows[next].scrollIntoView({ block: 'nearest' });
}

function chooseCustomerRow(tr) {
    if (!tr) return;
    document.getElementById('customer_id').value      = tr.dataset.kd;
    document.getElementById('customer_name').value    = tr.dataset.nama;
    document.getElementById('customer_display').value = tr.dataset.nama;
    document.getElementById('customer_validation').style.display = 'none';
    $('#modal-customer').modal('hide');
}

function chooseStockRow(tr) {
    if (!tr || currentRowIdx === null) return;
    var btn = tr.querySelector('.btn-pick-stock');
    if (!btn) return;
    applyBarangKeBaris(currentRowIdx, btn);
    $('#modal-stock').modal('hide');
}

/* ================================================================
   EVENTS
================================================================ */

/* Hapus baris / buka modal pilih barang (baris lama) */
document.getElementById('item-body').addEventListener('click', function(e) {
    var r = e.target.closest('.btn-remove');
    if (r) { r.closest('tr').remove(); hitungGrand(); hitungTK(); return; }

    var p = e.target.closest('.btn-pick');
    if (p) {
        if (!document.getElementById('gudang_id_input').value) {
            document.getElementById('gudang-hint').classList.remove('d-none');
            salesToast('warning', 'Pilih gudang terlebih dahulu.'); return;
        }
        currentRowIdx = parseInt(p.dataset.idx);   // ← baris yang menekan Pilih
        document.getElementById('stock-search').value = '';
        $('#modal-stock').modal('show');
        loadStock();
    }
});

/* Pilih barang dari modal → tulis ke currentRowIdx */
document.getElementById('stock-body').addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-pick-stock');
    if (!btn || currentRowIdx === null) return;
    applyBarangKeBaris(currentRowIdx, btn);
    $('#modal-stock').modal('hide');
});

document.getElementById('stock-search').addEventListener('keydown', function(e) {
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        focusTableRow('#stock-body .tr-pick-stock-row', null, 1);
    }
});

document.getElementById('stock-body').addEventListener('keydown', function(e) {
    var tr = e.target.closest('.tr-pick-stock-row');
    if (!tr) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); focusTableRow('#stock-body .tr-pick-stock-row', tr, 1); }
    if (e.key === 'ArrowUp') { e.preventDefault(); focusTableRow('#stock-body .tr-pick-stock-row', tr, -1); }
    if (e.key === 'Enter') { e.preventDefault(); chooseStockRow(tr); }
});

/* ── TOMBOL TAMBAH BARIS ──────────────────────────────────────────
   FIX BUG: tambahBaris() dulu → ambil newIdx → set currentRowIdx
   BARU buka modal. Dengan ini pilih barang masuk ke baris BARU,
   bukan baris lama.
─────────────────────────────────────────────────────────────────── */
document.getElementById('btn-add-row').addEventListener('click', function() {
    if (!document.getElementById('gudang_id_input').value) {
        document.getElementById('gudang-hint').classList.remove('d-none');
        document.getElementById('gudang_id_input').focus();
        salesToast('warning', 'Pilih gudang terlebih dahulu.'); return;
    }
    var newIdx    = tambahBaris({});   // buat baris → dapat idx-nya
    currentRowIdx = newIdx;            // arahkan modal ke baris baru
    document.getElementById('stock-search').value = '';
    $('#modal-stock').modal('show');
    loadStock();
});

/* Customer */
var elCustDisp = document.getElementById('customer_display');
function bukaMdlCustomer() {
    document.getElementById('customer-search').value = '';
    renderCustomers('');
    $('#modal-customer').modal('show');
}
elCustDisp.addEventListener('focus', bukaMdlCustomer);
elCustDisp.addEventListener('click', bukaMdlCustomer);

function focusCustomerSearch() {
    [0, 80, 180, 350].forEach(function(delay) {
        setTimeout(function(){
            if (!$('#modal-customer').hasClass('show')) return;
            var el = document.getElementById('customer-search');
            el.focus();
            el.select();
        }, delay);
    });
}

$('#modal-customer').on('shown.bs.modal', function(){
    focusCustomerSearch();
});
$('#modal-customer').on('click', function(e){
    if (e.target === this) focusCustomerSearch();
});
$('#modal-stock').on('shown.bs.modal', function(){
    setTimeout(function(){
        var el = document.getElementById('stock-search');
        el.focus();
        el.select();
    }, 50);
});

document.getElementById('customer-search').addEventListener('input', function(){ renderCustomers(this.value); });
document.getElementById('customer-body').addEventListener('click', function(e){
    var tr = e.target.closest('.tr-pick-customer');
    chooseCustomerRow(tr);
});
document.getElementById('customer-search').addEventListener('keydown', function(e) {
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        focusTableRow('#customer-body .tr-pick-customer', null, 1);
    }
});
document.getElementById('customer-body').addEventListener('keydown', function(e){
    var tr = e.target.closest('.tr-pick-customer');
    if (!tr) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); focusTableRow('#customer-body .tr-pick-customer', tr, 1); }
    if (e.key === 'ArrowUp') { e.preventDefault(); focusTableRow('#customer-body .tr-pick-customer', tr, -1); }
    if (e.key === 'Enter') { e.preventDefault(); chooseCustomerRow(tr); }
});

/* Cari barang */
document.getElementById('stock-search').addEventListener('input', function(){ renderStock(stockCache); });

document.getElementById('form-so').addEventListener('keydown', function(e) {
    if (e.key !== 'Enter' || e.target.tagName === 'TEXTAREA') return;
    if (e.target.closest('.modal')) return;
    var tag = e.target.tagName;
    if (!['INPUT','SELECT'].includes(tag)) return;
    e.preventDefault();
    var focusables = Array.prototype.slice.call(this.querySelectorAll('input:not([type="hidden"]), select, textarea, button, a.btn'))
        .filter(function(el) {
            return el.offsetParent !== null && !el.disabled && el.tabIndex !== -1;
        });
    var idx = focusables.indexOf(e.target);
    if (idx >= 0 && idx < focusables.length - 1) focusables[idx + 1].focus();
});

/* Ganti gudang */
document.getElementById('gudang_id_input').addEventListener('change', function(){
    var el = this;
    var gid = this.value;
    if (!gid) { stockCache=[]; stockLoaded=false; document.getElementById('gudang-hint').classList.remove('d-none'); return; }
    document.getElementById('gudang-hint').classList.add('d-none');

    var adaBarang=false;
    document.querySelectorAll('#item-body tr').forEach(function(tr){
        var i=tr.dataset.idx; if(!i) return;
        if((document.getElementById('kd_'+i)||{value:''}).value) adaBarang=true;
    });

    if (adaBarang) {
        salesConfirm({
            title: 'Ganti gudang?',
            text: 'Mengganti gudang akan menghapus semua item yang sudah dipilih.',
            icon: 'warning',
            confirmText: 'Ya, ganti gudang',
            confirmColor: '#dc2626'
        }).then(function(ok) {
            if (!ok) {
                el.value = el.dataset.prevVal || '';
                return;
            }
            document.getElementById('item-body').innerHTML='';
            rowIdx=0;
            tambahBaris({});
            hitungGrand(); hitungTK();
            el.dataset.prevVal=gid;
            stockCache=[]; stockLoaded=false;
        });
        return;
    }
    this.dataset.prevVal=gid;
    stockCache=[]; stockLoaded=false;
});

/* Submit */
var soSubmitConfirmed = false;
document.getElementById('form-so').addEventListener('submit', function(e){
    if (!document.getElementById('customer_id').value) {
        e.preventDefault();
        document.getElementById('customer_validation').style.display='block';
        document.getElementById('customer_display').focus();
        salesToast('warning', 'Pilih customer terlebih dahulu.');
        return;
    }
    var rows=document.querySelectorAll('#item-body tr');
    if (!rows.length) { e.preventDefault(); salesToast('warning', 'Minimal 1 item barang harus ditambahkan.'); return; }
    var errs=[];
    rows.forEach(function(tr){
        var i=tr.dataset.idx;
        var qB=parseFloat((document.getElementById('qtybox_'+i)||{value:0}).value)||0;
        var qE=parseFloat((document.getElementById('qtyecer_'+i)||{value:0}).value)||0;
        var nm=(document.getElementById('nm_'+i)||{value:'(barang)'}).value||'(barang)';
        var elE=document.getElementById('avail-ecer_'+i);
        var avT=elE?parseFloat(elE.dataset.availTotal||0):0;
        var qK=(qB*getIsi(i))+qE;
        if(qB<=0&&qE<=0) errs.push(nm+': qty 0');
        else if(qB>Math.floor(avT/getIsi(i))) errs.push(nm+': qty box melebihi stok');
        else if(qK>avT) errs.push(nm+': total '+qK+' pcs melebihi stok '+avT+' pcs');
    });
    if(errs.length){ e.preventDefault(); salesToast('error', errs.join('<br>')); return; }

    if (!soSubmitConfirmed) {
        e.preventDefault();
        var form = this;
        salesConfirm({
            title: 'Simpan Sales Order?',
            text: 'Pastikan customer, gudang, dan item barang sudah benar.',
            icon: 'question',
            confirmText: 'Ya, simpan SO',
            confirmColor: '#2563eb'
        }).then(function(ok) {
            if (!ok) return;
            soSubmitConfirmed = true;
            salesLoading(true, 'Menyimpan Sales Order...');
            form.submit();
        });
    }
});

/* ================================================================
   INIT
================================================================ */
(function(){
    var elG = document.getElementById('gudang_id_input');

    if (EDIT_DETAILS.length) {
        if (elG) elG.dataset.prevVal = GUDANG_AWAL;
        EDIT_DETAILS.forEach(function(d){ tambahBaris(d); });
        hitungGrand(); hitungTK();

        if (GUDANG_AWAL) {
            salesLoading(true, 'Memuat stok awal...');
            fetch(BASE_URL+'sales_order/get_stock?gudang_id='+encodeURIComponent(GUDANG_AWAL))
                .then(function(r){ return r.json(); })
                .then(function(res){
                    if(res.status!=='ok') return;
                    stockCache=res.data||[]; stockLoaded=true;

                    document.querySelectorAll('#item-body tr').forEach(function(tr){
                        var i=tr.dataset.idx;
                        var kd=(document.getElementById('kd_'+i)||{value:''}).value;
                        var ev=(document.getElementById('exp_'+i)||{value:''}).value||'';
                        if(!kd) return;

                        /* Rebuild dropdown */
                        var rows=stockCache.filter(function(s){return(s.kd_barang||'')===kd;});
                        var sel=document.getElementById('exp_'+i);
                        if(sel&&rows.length){
                            sel.innerHTML='<option value="">-- Pilih Expired Date --</option>';
                            rows.forEach(function(s){
                                var opt=document.createElement('option');
                                var ed=s.exp_date||s.expired_date||'';
                                opt.value=ed;
                                var iS=parseInt(s.isi_per_box||1);
                                var aB=Math.floor(parseFloat(s.available_stock||0)/iS);
                                var aE=Math.floor(parseFloat(s.available_stock||0)%iS);
                                opt.textContent=formatTgl(ed)+(s.no_lot?' | Lot: '+s.no_lot:'')+' ['+fmtNum(aB,0)+' box + '+fmtNum(aE,0)+' pcs]';
                                opt.dataset.ton=parseFloat(s.berat_gram||0);
                                opt.dataset.kub=parseFloat(s.kubikasi_m3||0);
                                opt.dataset.av =parseFloat(s.available_stock||0);
                                opt.dataset.lot=s.no_lot||'';
                                opt.dataset.isi=parseInt(s.isi_per_box||1);
                                opt.dataset.gudang=s.gudang_id||'';
                                if(ed===ev) opt.selected=true;
                                sel.appendChild(opt);
                            });
                        }
                        /* Update avail display */
                        var cocok=stockCache.filter(function(s){return(s.kd_barang||'')===kd&&(s.exp_date||s.expired_date||'')===ev;});
                        if(cocok.length){
                            var st=cocok[0],iS=parseInt(st.isi_per_box||1),aS=parseFloat(st.available_stock||0);
                            var aBS=Math.floor(aS/iS),aES=Math.floor(aS%iS);
                            var elB=document.getElementById('avail-box_'+i),elE=document.getElementById('avail-ecer_'+i);
                            if(elB){elB.textContent=fmtNum(aBS,0);elB.dataset.availBox=aBS;}
                            if(elE){elE.textContent=fmtNum(aES,0);elE.dataset.availEcer=aES;elE.dataset.availTotal=aS;}
                            var mB=document.getElementById('maxbox_'+i),mE=document.getElementById('maxecer_'+i);
                            if(mB) mB.textContent=fmtNum(aBS,0);
                            if(mE) mE.textContent=fmtNum(aES,0)+' pcs';
                            document.getElementById('isi_'+i).value=iS;
                            document.getElementById('bg_' +i).value=st.berat_gram||0;
                            document.getElementById('km_' +i).value=st.kubikasi_m3||0;
                        }
                        hitungBaris(i);
                    });
                })
                .catch(function(e){ console.warn('Gagal load stok edit:',e); })
                .finally(function(){ salesLoading(false); });
        }
    } else {
        if (elG) elG.dataset.prevVal='';
        tambahBaris({});
        hitungGrand(); hitungTK();
    }
})();
</script>
</body>
