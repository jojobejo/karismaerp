<!-- views/content/sales/so_form.php -->
<?php
    $is_edit   = !empty($so);
    $id_so_val = $is_edit ? $so['id_so'] : $no_so;
    $action    = $is_edit
        ? base_url('sales_order/update/' . $so['id_so'])
        : base_url('sales_order/store');

    $batas_ton = isset($batas_tonase)   ? $batas_tonase   : 6;
    $batas_kub = isset($batas_kubikasi) ? $batas_kubikasi : 9;

    function escAttr($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
    function escJs($v) {
        return str_replace(
            ['\\', "'", "\r", "\n", "\t"],
            ['\\\\', "\\'", '', '', ''],
            (string)$v
        );
    }
?>
<body class="hold-transition sidebar-mini sidebar-collapse">
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

                <div class="mb-3">
                    <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <form action="<?= $action ?>" method="post" id="form-so">

                    <div class="row">
                        <!-- ============================================================
                             INFORMASI SO
                        ============================================================ -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white py-2">
                                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi SO</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label">No SO</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="id_so"
                                                value="<?= htmlspecialchars($id_so_val) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label">Tanggal <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="date" class="form-control" name="tanggal" required
                                                value="<?= $is_edit ? $so['tanggal_transaksi'] : date('Y-m-d') ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label">Customer <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <input type="text" id="customer_display"
                                                    class="form-control"
                                                    placeholder="-- Pilih Customer --"
                                                    value="<?= $is_edit ? escAttr($so['customer_name']) : '' ?>"
                                                    readonly style="background:#fff;cursor:pointer">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-primary" id="btn-pilih-customer">
                                                        <i class="fas fa-search"></i> Pilih
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="hidden" name="customer_id"   id="customer_id"
                                                value="<?= $is_edit ? escAttr($so['customer_id']) : '' ?>">
                                            <input type="hidden" name="customer_name" id="customer_name"
                                                value="<?= $is_edit ? escAttr($so['customer_name']) : '' ?>">
                                            <div id="customer_validation" class="text-danger small mt-1" style="display:none">
                                                <i class="fas fa-exclamation-circle"></i> Pilih customer terlebih dahulu.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label">Gudang</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="gudang_id" readonly
                                                value="<?= escAttr($gudang_id) ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label">Catatan</label>
                                        <div class="col-sm-8">
                                            <textarea name="catatan" class="form-control" rows="2"><?= $is_edit ? htmlspecialchars($so['catatan'] ?? '') : '' ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================================
                             TONASE & KUBIKASI
                        ============================================================ -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white py-2">
                                    <h3 class="card-title">
                                        <i class="fas fa-weight mr-1"></i> Tonase &amp; Kubikasi
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="info-box mb-0">
                                                <span class="info-box-icon bg-success" style="min-height:50px">
                                                    <i class="fas fa-truck"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Batas Tonase</span>
                                                    <span class="info-box-number"><?= $batas_ton ?> ton</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box mb-0">
                                                <span class="info-box-icon bg-info" style="min-height:50px">
                                                    <i class="fas fa-cube"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Batas Kubikasi</span>
                                                    <span class="info-box-number"><?= $batas_kub ?> m³</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small><b>Tonase:</b> <span id="lbl-tonase">0,000</span> ton</small>
                                            <small class="text-muted">Maks <?= $batas_ton ?> ton</small>
                                        </div>
                                        <div class="progress" style="height:12px">
                                            <div class="progress-bar bg-success" id="tonase-bar"
                                                 role="progressbar" style="width:0%"></div>
                                        </div>
                                        <small id="lbl-tonase-warn" class="text-danger d-none font-weight-bold">
                                            <i class="fas fa-exclamation-triangle"></i> Melebihi batas tonase!
                                        </small>
                                    </div>

                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small><b>Kubikasi:</b> <span id="lbl-kubikasi">0,00000</span> m³</small>
                                            <small class="text-muted">Maks <?= $batas_kub ?> m³</small>
                                        </div>
                                        <div class="progress" style="height:12px">
                                            <div class="progress-bar bg-info" id="kubikasi-bar"
                                                 role="progressbar" style="width:0%"></div>
                                        </div>
                                        <small id="lbl-kubikasi-warn" class="text-danger d-none font-weight-bold">
                                            <i class="fas fa-exclamation-triangle"></i> Melebihi batas kubikasi!
                                        </small>
                                    </div>

                                    <div class="mt-3 p-2 bg-light rounded border small">
                                        <i class="fas fa-boxes text-secondary mr-1"></i>
                                        Total pcs: <b id="lbl-total-kecil">0</b>
                                        &nbsp;|&nbsp;
                                        <i class="fas fa-box text-secondary mr-1"></i>
                                        Total box: <b id="lbl-total-box">0</b>
                                        &nbsp;+&nbsp;
                                        <i class="fas fa-cubes text-secondary mr-1"></i>
                                        Eceran: <b id="lbl-total-ecer">0</b> pcs
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================
                         TABEL ITEM BARANG
                         Kolom qty: [Qty Box] [Isi/Box] [+Eceran(pcs)] [=Total pcs]
                    ============================================================ -->
                    <div class="card">
                        <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-boxes mr-1"></i> Item Barang
                            </h3>
                            <button type="button" class="btn btn-light btn-sm" id="btn-add-row">
                                <i class="fas fa-plus"></i> Tambah Barang
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="tbl-item">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="min-width:220px">Barang</th>
                                            <th style="min-width:160px">Expired / Lot</th>
                                            <th style="width:85px" class="text-center">Qty Box</th>
                                            <th style="width:60px" class="text-center">Isi/Box</th>
                                            <th style="width:85px" class="text-center">
                                                +Eceran
                                                <i class="fas fa-info-circle text-info"
                                                   title="Jumlah satuan kecil (pcs) di luar box penuh"
                                                   data-toggle="tooltip"></i>
                                            </th>
                                            <th style="width:80px" class="text-center">=Total Pcs</th>
                                            <th style="width:60px">Satuan</th>
                                            <th style="width:125px">Harga/Box</th>
                                            <th style="width:60px">Pajak%</th>
                                            <th style="width:125px">Subtotal</th>
                                            <th style="width:85px">Tonase</th>
                                            <th style="width:85px">Kubikasi</th>
                                            <th style="width:38px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="item-body"></tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="9" class="text-right font-weight-bold">GRAND TOTAL</td>
                                            <td class="text-right font-weight-bold" id="total-grand">0</td>
                                            <td class="text-right font-weight-bold small" id="total-tonase-tbl">0 ton</td>
                                            <td class="text-right font-weight-bold small" id="total-kubikasi-tbl">0 m³</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-4">
                        <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary mr-2">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary" id="btn-submit">
                            <i class="fas fa-save"></i> Simpan SO
                        </button>
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

<!-- ==================================================================
     MODAL: Pilih Barang
================================================================== -->
<div class="modal fade" id="modal-stock" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title"><i class="fas fa-search mr-1"></i> Pilih Barang</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="text" id="stock-search" class="form-control mb-2"
                    placeholder="Cari kode atau nama barang...">
                <div style="max-height:420px;overflow-y:auto">
                    <table class="table table-bordered table-sm table-hover mb-0">
                        <thead class="thead-dark sticky-top">
                            <tr>
                                <th>Nama Barang</th>
                                <th>Exp Date</th>
                                <th>No Lot</th>
                                <th class="text-right">Stok Box</th>
                                <th class="text-right">Sisa Ecer</th>
                                <th class="text-right">Total Pcs</th>
                                <th class="text-center">Isi/Box</th>
                                <th>Satuan</th>
                                <th class="text-right">Berat/pcs</th>
                                <th class="text-right">Kubik/pcs</th>
                                <th class="text-center">Pilih</th>
                            </tr>
                        </thead>
                        <tbody id="stock-body">
                            <tr><td colspan="11" class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Memuat...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================================================================
     MODAL: Pilih Customer
================================================================== -->
<div class="modal fade" id="modal-customer" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title"><i class="fas fa-users mr-1"></i> Pilih Customer</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="text" id="customer-search" class="form-control mb-2"
                    placeholder="Cari nama customer...">
                <div style="max-height:450px;overflow-y:auto">
                    <table class="table table-bordered table-sm table-hover mb-0">
                        <thead class="thead-dark sticky-top">
                            <tr>
                                <th>Nama Customer</th>
                                <th>Telepon</th>
                                <th>Alamat</th>
                                <th style="width:60px" class="text-center">Pilih</th>
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
/* ====================================================================
   SO FORM — Karisma ERP
   Logika qty BARU:
     qty_box     = input box (user isi)
     qty_satuan  = input eceran/pcs (user isi, boleh 0)
     isi_per_box = dari master barang (p×l×t atau kolom isi)
     qty_kecil   = (qty_box × isi_per_box) + qty_satuan

   Validasi stok:
     max_box     = available_box  = floor(available_stock / isi_per_box)
     max_ecer    = available_ecer = available_stock % isi_per_box
     TAPI: user boleh input eceran lebih dari sisa box jika ada stok.
     Yang penting: total qty_kecil <= available_stock

   Harga:
     hrg_satuan   = harga per BOX
     hrg_per_pcs  = hrg_satuan / isi_per_box
     subtotal     = (hrg_satuan × qty_box) + (hrg_per_pcs × qty_satuan)
   ==================================================================== */

var BASE_URL       = '<?= base_url() ?>';
var GUDANG_ID      = '<?= escJs($gudang_id) ?>';
var BATAS_TONASE   = <?= (float)$batas_ton ?>;
var BATAS_KUBIKASI = <?= (float)$batas_kub ?>;

<?php
$edit_details_safe = [];
if ($is_edit && !empty($details)) {
    foreach ($details as $d) {
        $safe = [];
        foreach ($d as $k => $v) {
            if (is_string($v)) $v = mb_convert_encoding($v, 'UTF-8', 'UTF-8');
            $safe[$k] = $v;
        }
        $edit_details_safe[] = $safe;
    }
}
?>
var EDIT_DETAILS = <?= json_encode(array_values($edit_details_safe), JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>;

<?php
$customers_safe = [];
foreach ($customers as $c) {
    $cs = [];
    foreach ($c as $k => $v) {
        $cs[$k] = is_string($v) ? mb_convert_encoding($v, 'UTF-8', 'UTF-8') : $v;
    }
    $customers_safe[] = $cs;
}
?>
var CUSTOMERS = <?= json_encode(array_values($customers_safe), JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>;

var currentRowIdx = null;
var stockCache    = [];
var rowIdx        = 0;

/* ------------------------------------------------------------------
   UTILS
   ------------------------------------------------------------------ */
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
    return String(v||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

/* ------------------------------------------------------------------
   BUAT BARIS TABEL ITEM
   ------------------------------------------------------------------ */
function buatBaris(idx, d) {
    d = d || {};
    var kd      = d.kd_barang    || '';
    var nm      = d.nama_barang  || '';
    var exp     = d.expired_date || '';
    var lot     = d.no_lot       || '';
    var sat     = d.satuan       || '';
    var hrg     = parseFloat(d.hrg_satuan  || 0);
    var pk      = parseFloat(d.hrg_pokok   || 0);
    var pajak   = parseFloat(d.pajak       || 0);
    var akun    = d.kode_akun    || '';
    var beratG  = parseFloat(d.berat_gram  || 0);
    var kubikM  = parseFloat(d.kubikasi_m3 || 0);
    var isi     = parseInt(d.isi_per_box   || 1); if (isi < 1) isi = 1;

    /* qty: hitung mundur untuk mode edit */
    var qtyKecilSaved = parseFloat(d.qty        || 0);
    var qtyBox        = parseFloat(d.qty_box    || 0);
    var qtySatuan     = parseFloat(d.qty_satuan || 0);

    /* Jika dari DB lama (tidak punya qty_satuan), turunkan dari qty */
    if (!d.qty_box && qtyKecilSaved > 0) {
        qtyBox    = Math.floor(qtyKecilSaved / isi);
        qtySatuan = qtyKecilSaved % isi;
    }

    var av       = parseFloat(d.available_stock || 0);
    var avBox    = isi > 0 ? Math.floor(av / isi)  : 0;
    var avEcer   = isi > 0 ? Math.floor(av % isi)  : 0;

    /* Total pcs yang dipesan */
    var qtyKecil = (qtyBox * isi) + qtySatuan;

    /* Harga per pcs (untuk subtotal eceran) */
    var hrgPcs   = isi > 0 ? hrg / isi : 0;
    var subBefore = (hrg * qtyBox) + (hrgPcs * qtySatuan);
    var sub       = subBefore * (1 + pajak / 100);

    var ton = qtyKecil * beratG / 1000000;
    var kub = qtyKecil * kubikM;

    var expOption = exp
        ? '<option value="'+esc(exp)+'" '
          +'data-lot="'+esc(lot)+'" '
          +'data-av="'+av+'" '
          +'data-ton="'+beratG+'" '
          +'data-kub="'+kubikM+'" '
          +'data-isi="'+isi+'" selected>'
          +esc(formatTgl(exp))+(lot ? ' | Lot: '+esc(lot) : '')
          +'</option>'
        : '<option value="">-- Pilih barang dulu --</option>';

    var html = '';
    html += '<tr id="row-'+idx+'" data-idx="'+idx+'">';

    /* --- Barang --- */
    html += '<td>';
    html += '<input type="hidden" name="produk_id[]"   value="'+esc(kd)+'">';
    html += '<input type="hidden" name="kd_barang[]"   id="kd_'+idx+'"  value="'+esc(kd)+'">';
    html += '<input type="hidden" name="nama_barang[]" id="nm_'+idx+'"  value="'+esc(nm)+'">';
    html += '<input type="hidden" name="satuan[]"      id="sat_'+idx+'" value="'+esc(sat)+'">';
    html += '<input type="hidden" name="hrg_pokok[]"   id="pk_'+idx+'"  value="'+pk+'">';
    html += '<input type="hidden" name="kode_akun[]"   value="'+esc(akun)+'">';
    html += '<input type="hidden" name="berat_gram[]"  id="bg_'+idx+'"  value="'+beratG+'">';
    html += '<input type="hidden" name="kubikasi_m3[]" id="km_'+idx+'"  value="'+kubikM+'">';
    html += '<input type="hidden" name="isi_per_box[]" id="isi_'+idx+'" value="'+isi+'">';
    html += '<div class="d-flex align-items-center">';
    html +=   '<div class="flex-grow-1">';
    html +=     '<small class="text-muted" id="kdlbl_'+idx+'">'+(kd||'&mdash;')+'</small><br>';
    html +=     '<span id="nmlbl_'+idx+'">'+(nm ? esc(nm) : '&mdash;')+'</span>';
    html +=   '</div>';
    html +=   '<button type="button" class="btn btn-xs btn-outline-primary ml-1 btn-pick" data-idx="'+idx+'">';
    html +=     '<i class="fas fa-search"></i> Pilih';
    html +=   '</button>';
    html += '</div>';
    /* Tampilan stok tersedia */
    html += '<div class="mt-1">';
    html +=   '<small class="text-success"><i class="fas fa-box mr-1"></i>Box: ';
    html +=     '<b id="avail-box_'+idx+'" data-avail-box="'+avBox+'">'+fmtNum(avBox,0)+'</b></small>';
    html +=   '&nbsp;+&nbsp;';
    html +=   '<small class="text-info"><i class="fas fa-cubes mr-1"></i>Ecer: ';
    html +=     '<b id="avail-ecer_'+idx+'" data-avail-ecer="'+avEcer+'" data-avail-total="'+av+'">'+fmtNum(avEcer,0)+'</b> pcs</small>';
    html += '</div>';
    html += '</td>';

    /* --- Expired / Lot --- */
    html += '<td>';
    html += '<select name="expired_date[]" id="exp_'+idx+'" class="form-control form-control-sm mb-1" required>';
    html += '<option value="">-- Pilih barang --</option>';
    html += expOption;
    html += '</select>';
    html += '<input type="hidden" name="no_lot[]" id="lot_'+idx+'" value="'+esc(lot)+'">';
    html += '<small class="text-muted" id="lotlbl_'+idx+'">'+(lot ? 'Lot: '+esc(lot) : '')+'</small>';
    html += '</td>';

    /* --- Qty Box --- */
    html += '<td>';
    html += '<input type="number" step="1" min="0" name="qty_box[]" id="qtybox_'+idx+'"';
    html += ' class="form-control form-control-sm text-center" value="'+qtyBox+'">';
    html += '<small class="text-muted d-block text-center">maks <b id="maxbox_'+idx+'">'+fmtNum(avBox,0)+'</b></small>';
    html += '</td>';

    /* --- Isi/Box --- */
    html += '<td class="text-center align-middle">';
    html += '<span id="isilbl_'+idx+'" class="badge badge-secondary">'+isi+'</span>';
    html += '<br><small class="text-muted">pcs/box</small>';
    html += '</td>';

    /* --- Qty Eceran (satuan kecil) --- */
    html += '<td>';
    html += '<input type="number" step="1" min="0" name="qty_satuan[]" id="qtyecer_'+idx+'"';
    html += ' class="form-control form-control-sm text-center" value="'+qtySatuan+'">';
    html += '<small class="text-muted d-block text-center">maks <b id="maxecer_'+idx+'">'+fmtNum(avEcer,0)+'</b> pcs</small>';
    html += '</td>';

    /* --- Total Pcs (read only) --- */
    html += '<td class="text-center align-middle">';
    html += '<b id="qtylbl_'+idx+'" class="text-dark">'+fmtNum(qtyKecil,0)+'</b>';
    html += '<br><small class="text-muted">pcs</small>';
    html += '</td>';

    /* --- Satuan --- */
    html += '<td><input type="text" id="satlbl_'+idx+'" class="form-control form-control-sm" value="'+esc(sat)+'" readonly></td>';

    /* --- Harga/Box --- */
    html += '<td>';
    html += '<input type="number" step="0.01" name="hrg_satuan[]" id="hrg_'+idx+'"';
    html += ' class="form-control form-control-sm" value="'+(hrg||'')+'" required>';
    html += '<div id="hrgwarn_'+idx+'" class="mt-1"></div>';
    html += '</td>';

    /* --- Pajak --- */
    html += '<td><input type="number" step="0.01" name="pajak[]" id="pjk_'+idx+'"';
    html += ' class="form-control form-control-sm" value="'+pajak+'" min="0" max="100"></td>';

    /* --- Subtotal --- */
    html += '<td class="text-right align-middle"><b id="sub_'+idx+'">'+fmtNum(sub)+'</b></td>';

    /* --- Tonase baris --- */
    html += '<td class="text-right align-middle small"><span id="ton_'+idx+'">'+fmtNum(ton,4)+'</span> ton</td>';

    /* --- Kubikasi baris --- */
    html += '<td class="text-right align-middle small"><span id="kub_'+idx+'">'+fmtNum(kub,5)+'</span> m³</td>';

    /* --- Hapus --- */
    html += '<td class="text-center align-middle">';
    html += '<button type="button" class="btn btn-xs btn-danger btn-remove" data-idx="'+idx+'">';
    html += '<i class="fas fa-trash"></i></button></td>';

    html += '</tr>';
    return html;
}

/* ------------------------------------------------------------------
   TAMBAH & BIND BARIS
   ------------------------------------------------------------------ */
function tambahBaris(d) {
    document.getElementById('item-body').insertAdjacentHTML('beforeend', buatBaris(rowIdx, d || {}));
    bindBaris(rowIdx);
    rowIdx++;
}

function bindBaris(idx) {
    /* Input yang mempengaruhi kalkulasi */
    ['hrg_','qtybox_','qtyecer_','pjk_'].forEach(function(f) {
        var el = document.getElementById(f+idx);
        if (el) el.addEventListener('input', function() { hitungBaris(idx); });
    });

    /* Validasi langsung saat user mengetik qty box */
    var elBox = document.getElementById('qtybox_'+idx);
    if (elBox) {
        elBox.addEventListener('change', function() {
            var avTotal = parseFloat((document.getElementById('avail-ecer_'+idx)||{}).dataset.availTotal || 0);
            var isi     = parseInt(document.getElementById('isi_'+idx).value) || 1;
            var maxBox  = Math.floor(avTotal / isi);
            var v       = parseInt(this.value) || 0;
            if (v > maxBox) {
                this.value = maxBox;
                hitungBaris(idx);
                alert('Qty box melebihi stok! Maksimal ' + maxBox + ' box (' + (maxBox*isi) + ' pcs)');
            }
        });
    }

    /* Validasi langsung saat user mengetik qty eceran */
    var elEcer = document.getElementById('qtyecer_'+idx);
    if (elEcer) {
        elEcer.addEventListener('change', function() {
            var avTotal = parseFloat((document.getElementById('avail-ecer_'+idx)||{}).dataset.availTotal || 0);
            var isi     = parseInt(document.getElementById('isi_'+idx).value) || 1;
            var qtyBox  = parseInt(document.getElementById('qtybox_'+idx).value) || 0;
            var sisaAvail = avTotal - (qtyBox * isi);  /* sisa stok setelah box */
            var v = parseInt(this.value) || 0;
            if (v > sisaAvail) {
                this.value = Math.max(0, Math.floor(sisaAvail));
                hitungBaris(idx);
                if (sisaAvail <= 0) {
                    alert('Stok habis! Semua stok sudah terpakai untuk qty box.');
                } else {
                    alert('Qty eceran melebihi sisa stok. Maksimal ' + Math.floor(sisaAvail) + ' pcs eceran.');
                }
            }
        });
    }

    /* Ganti expired date */
    var sel = document.getElementById('exp_'+idx);
    if (sel) {
        sel.addEventListener('change', function() {
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

            var isiLbl = document.getElementById('isilbl_'+idx);
            if (isiLbl) isiLbl.textContent = isi;

            document.getElementById('lot_'   +idx).value       = lot;
            document.getElementById('lotlbl_'+idx).textContent = lot ? 'Lot: '+lot : '';

            /* Update info stok tersedia */
            var avBox  = Math.floor(av / isi);
            var avEcer = Math.floor(av % isi);

            var elAvBox = document.getElementById('avail-box_'+idx);
            if (elAvBox) { elAvBox.textContent = fmtNum(avBox,0); elAvBox.dataset.availBox = avBox; }

            var elAvEcer = document.getElementById('avail-ecer_'+idx);
            if (elAvEcer) { elAvEcer.textContent = fmtNum(avEcer,0); elAvEcer.dataset.availEcer = avEcer; elAvEcer.dataset.availTotal = av; }

            var elMaxBox = document.getElementById('maxbox_'+idx);
            if (elMaxBox) elMaxBox.textContent = fmtNum(avBox,0);

            var elMaxEcer = document.getElementById('maxecer_'+idx);
            if (elMaxEcer) elMaxEcer.textContent = fmtNum(avEcer,0);

            hitungBaris(idx);
        });
    }
}

/* ------------------------------------------------------------------
   HITUNG SATU BARIS
   ------------------------------------------------------------------ */
function hitungBaris(idx) {
    function val(id)  { var el = document.getElementById(id); return el ? parseFloat(el.value)||0 : 0; }
    function ival(id) { var el = document.getElementById(id); return el ? parseInt(el.value)||0  : 0; }

    var hrg      = val('hrg_'+idx);
    var qtyBox   = val('qtybox_'+idx);
    var qtySat   = val('qtyecer_'+idx);
    var pjk      = val('pjk_'+idx);
    var pk       = val('pk_'+idx);
    var bg       = val('bg_'+idx);
    var km       = val('km_'+idx);
    var isi      = ival('isi_'+idx); if (isi < 1) isi = 1;

    var qtyKecil = (qtyBox * isi) + qtySat;

    /* Label total pcs */
    var elQtyLbl = document.getElementById('qtylbl_'+idx);
    if (elQtyLbl) elQtyLbl.textContent = fmtNum(qtyKecil, 0);

    /* Harga per pcs (untuk eceran) */
    var hrgPcs     = isi > 0 ? hrg / isi : 0;
    var subBefore  = (hrg * qtyBox) + (hrgPcs * qtySat);
    var tot        = subBefore * (1 + pjk / 100);

    var elSub = document.getElementById('sub_'+idx);
    if (elSub) elSub.textContent = fmtNum(tot);

    /* Peringatan harga < HPP */
    var wEl = document.getElementById('hrgwarn_'+idx);
    if (wEl) {
        wEl.innerHTML = (hrg > 0 && hrg < pk)
            ? '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Di bawah HPP</span>'
            : '';
    }

    /* Tonase & kubikasi per baris (pakai total pcs) */
    var ton = qtyKecil * bg / 1000000;
    var kub = qtyKecil * km;

    var elTon = document.getElementById('ton_'+idx);
    var elKub = document.getElementById('kub_'+idx);
    if (elTon) elTon.textContent = fmtNum(ton, 4);
    if (elKub) elKub.textContent = fmtNum(kub, 5);

    hitungGrand();
    hitungTK();
}

/* ------------------------------------------------------------------
   GRAND TOTAL
   ------------------------------------------------------------------ */
function hitungGrand() {
    var grand = 0;
    document.querySelectorAll('#item-body tr').forEach(function(tr) {
        var i      = tr.dataset.idx;
        var hrg    = parseFloat((document.getElementById('hrg_'+i)||{value:0}).value)||0;
        var box    = parseFloat((document.getElementById('qtybox_'+i)||{value:0}).value)||0;
        var ecer   = parseFloat((document.getElementById('qtyecer_'+i)||{value:0}).value)||0;
        var pjk    = parseFloat((document.getElementById('pjk_'+i)||{value:0}).value)||0;
        var isi    = parseInt((document.getElementById('isi_'+i)||{value:1}).value)||1;
        var hrgPcs = isi > 0 ? hrg / isi : 0;
        var sub    = (hrg * box) + (hrgPcs * ecer);
        grand += sub * (1 + pjk / 100);
    });
    document.getElementById('total-grand').textContent = fmtNum(grand);
}

/* ------------------------------------------------------------------
   TOTAL TONASE & KUBIKASI + PROGRESS BAR
   ------------------------------------------------------------------ */
function hitungTK() {
    var totTon   = 0, totKub = 0, totKecil = 0, totBox = 0, totEcer = 0;

    document.querySelectorAll('#item-body tr').forEach(function(tr) {
        var i      = tr.dataset.idx;
        var qBox   = parseFloat((document.getElementById('qtybox_'+i)||{value:0}).value)||0;
        var qEcer  = parseFloat((document.getElementById('qtyecer_'+i)||{value:0}).value)||0;
        var isi    = parseInt((document.getElementById('isi_'+i)||{value:1}).value)||1; if(isi<1)isi=1;
        var bg     = parseFloat((document.getElementById('bg_'+i)||{value:0}).value)||0;
        var km     = parseFloat((document.getElementById('km_'+i)||{value:0}).value)||0;
        var qKecil = (qBox * isi) + qEcer;
        totTon   += qKecil * bg / 1000000;
        totKub   += qKecil * km;
        totKecil += qKecil;
        totBox   += qBox;
        totEcer  += qEcer;
    });

    document.getElementById('lbl-tonase').textContent       = fmtNum(totTon,   3);
    document.getElementById('lbl-kubikasi').textContent     = fmtNum(totKub,   5);
    document.getElementById('lbl-total-kecil').textContent  = fmtNum(totKecil, 0);
    document.getElementById('lbl-total-box').textContent    = fmtNum(totBox,   0);
    document.getElementById('lbl-total-ecer').textContent   = fmtNum(totEcer,  0);
    document.getElementById('total-tonase-tbl').textContent   = fmtNum(totTon,  3) + ' ton';
    document.getElementById('total-kubikasi-tbl').textContent = fmtNum(totKub,  5) + ' m³';

    var pctTon = BATAS_TONASE   > 0 ? Math.min((totTon / BATAS_TONASE)   * 100, 100) : 0;
    var barTon = document.getElementById('tonase-bar');
    if (barTon) {
        barTon.style.width = pctTon.toFixed(2) + '%';
        barTon.className   = 'progress-bar ' + (totTon > BATAS_TONASE ? 'bg-danger' : 'bg-success');
    }
    var wTon = document.getElementById('lbl-tonase-warn');
    if (wTon) wTon.classList.toggle('d-none', totTon <= BATAS_TONASE);

    var pctKub = BATAS_KUBIKASI > 0 ? Math.min((totKub / BATAS_KUBIKASI) * 100, 100) : 0;
    var barKub = document.getElementById('kubikasi-bar');
    if (barKub) {
        barKub.style.width = pctKub.toFixed(2) + '%';
        barKub.className   = 'progress-bar ' + (totKub > BATAS_KUBIKASI ? 'bg-danger' : 'bg-info');
    }
    var wKub = document.getElementById('lbl-kubikasi-warn');
    if (wKub) wKub.classList.toggle('d-none', totKub <= BATAS_KUBIKASI);
}

/* ------------------------------------------------------------------
   LOAD STOK (AJAX)
   ------------------------------------------------------------------ */
function loadStock() {
    document.getElementById('stock-body').innerHTML =
        '<tr><td colspan="11" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';

    fetch(BASE_URL + 'sales_order/get_stock?gudang_id=' + encodeURIComponent(GUDANG_ID))
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(res) {
            if (res.status !== 'ok') throw new Error(res.message || 'Error dari server');
            stockCache = res.data || [];
            renderStock(stockCache);
        })
        .catch(function(err) {
            console.error('get_stock error:', err);
            document.getElementById('stock-body').innerHTML =
                '<tr><td colspan="11" class="text-center text-danger">'
                + '<i class="fas fa-exclamation-triangle mr-1"></i> Gagal memuat stok: ' + err.message
                + '</td></tr>';
        });
}

function renderStock(data) {
    var q = (document.getElementById('stock-search').value || '').toLowerCase();
    var filtered = q
        ? data.filter(function(d) {
            return String(d.kode_barang||'').toLowerCase().indexOf(q) >= 0
                || String(d.nama_barang||'').toLowerCase().indexOf(q) >= 0;
          })
        : data;

    if (!filtered.length) {
        document.getElementById('stock-body').innerHTML =
            '<tr><td colspan="11" class="text-center text-muted"><i class="fas fa-inbox mr-1"></i> Tidak ada stok</td></tr>';
        return;
    }

    var html   = '';
    var lastKd = null;
    filtered.forEach(function(d) {
        var isNew     = d.kode_barang !== lastKd;
        lastKd        = d.kode_barang;
        var isi       = parseInt(d.isi_per_box || 1);
        var avTotal   = parseFloat(d.available_stock || 0);
        var avBox     = parseInt(d.available_box  || 0);
        var avEcer    = parseInt(d.available_ecer || 0);
        var beratKg   = (parseFloat(d.berat_gram||0)/1000).toFixed(3);
        var kubikStr  = parseFloat(d.kubikasi_m3||0).toFixed(6);

        html += '<tr class="'+(isNew?'table-light':'')+'">';
        html += '<td>';
        html += '<small class="text-muted d-block">'+esc(d.kode_barang)+'</small>';
        html += isNew ? '<b>'+esc(d.nama_barang)+'</b>' : '<span class="text-muted">&#x21B3;</span> '+esc(d.nama_barang);
        html += '</td>';
        html += '<td>'+(d.exp_date
            ? '<span class="badge '+(isExpiringSoon(d.exp_date)?'badge-warning':'badge-success')+'">'+formatTgl(d.exp_date)+'</span>'
            : '-')+'</td>';
        html += '<td>'+(d.no_lot||'-')+'</td>';

        /* Stok box */
        html += '<td class="text-right">';
        html += '<b class="text-success">'+fmtNum(avBox,0)+' box</b>';
        html += '</td>';

        /* Sisa eceran */
        html += '<td class="text-right">';
        html += '<span class="text-info">'+fmtNum(avEcer,0)+' pcs</span>';
        html += '</td>';

        /* Total pcs */
        html += '<td class="text-right">';
        html += '<small class="text-muted">'+fmtNum(avTotal,0)+' pcs</small>';
        html += '</td>';

        html += '<td class="text-center"><span class="badge badge-info">'+isi+'</span><br><small class="text-muted">pcs/box</small></td>';
        html += '<td>'+esc(d.satuan||'')+'</td>';
        html += '<td class="text-right"><small>'+beratKg+' kg</small></td>';
        html += '<td class="text-right"><small>'+kubikStr+' m³</small></td>';
        html += '<td class="text-center">';
        html += '<button type="button" class="btn btn-xs btn-primary btn-pick-stock"';
        html += ' data-kd="'+esc(d.kode_barang)+'"';
        html += ' data-nm="'+esc(d.nama_barang)+'"';
        html += ' data-exp="'+esc(d.exp_date||'')+'"';
        html += ' data-lot="'+esc(d.no_lot||'')+'"';
        html += ' data-sat="'+esc(d.satuan||'')+'"';
        html += ' data-av="'+avTotal+'"';
        html += ' data-ton="'+(parseFloat(d.berat_gram)||0)+'"';
        html += ' data-kub="'+(parseFloat(d.kubikasi_m3)||0)+'"';
        html += ' data-isi="'+isi+'"';
        html += ' data-pk="'+(parseFloat(d.hpp)||0)+'">';
        html += '<i class="fas fa-check"></i> Pilih</button>';
        html += '</td></tr>';
    });
    document.getElementById('stock-body').innerHTML = html;
}

/* ------------------------------------------------------------------
   RENDER CUSTOMER MODAL
   ------------------------------------------------------------------ */
function renderCustomers(q) {
    q = (q||'').toLowerCase();
    var filtered = q
        ? CUSTOMERS.filter(function(c) {
            return String(c.nama_customer||'').toLowerCase().indexOf(q) >= 0
                || String(c.id||'').toLowerCase().indexOf(q) >= 0;
          })
        : CUSTOMERS;

    if (!filtered.length) {
        document.getElementById('customer-body').innerHTML =
            '<tr><td colspan="4" class="text-center text-muted">Tidak ada customer ditemukan.</td></tr>';
        return;
    }
    var html = '';
    filtered.forEach(function(c) {
        html += '<tr>';
        html += '<td><b>'+esc(c.nama_customer||'')+'</b></td>';
        html += '<td><small>'+esc(c.telepon||c.no_telp||'-')+'</small></td>';
        html += '<td><small>'+esc(c.alamat||'-')+'</small></td>';
        html += '<td class="text-center">';
        html += '<button type="button" class="btn btn-xs btn-primary btn-pick-customer"';
        html += ' data-id="'+esc(c.id)+'"';
        html += ' data-nama="'+esc(c.nama_customer)+'">';
        html += '<i class="fas fa-check"></i></button>';
        html += '</td></tr>';
    });
    document.getElementById('customer-body').innerHTML = html;
}

/* ------------------------------------------------------------------
   EVENTS
   ------------------------------------------------------------------ */
/* Tabel item: remove & pick */
document.getElementById('item-body').addEventListener('click', function(e) {
    var r = e.target.closest('.btn-remove');
    if (r) { r.closest('tr').remove(); hitungGrand(); hitungTK(); return; }

    var p = e.target.closest('.btn-pick');
    if (p) {
        currentRowIdx = parseInt(p.dataset.idx);
        loadStock();
        $('#modal-stock').modal('show');
    }
});

/* Modal barang: pilih baris */
document.getElementById('stock-body').addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-pick-stock');
    if (!btn) return;
    var i  = currentRowIdx;
    var kd = btn.dataset.kd;

    document.getElementById('kd_'   +i).value       = kd;
    document.getElementById('nm_'   +i).value       = btn.dataset.nm;
    document.getElementById('kdlbl_'+i).textContent = kd;
    document.getElementById('nmlbl_'+i).textContent = btn.dataset.nm;
    document.getElementById('pk_'   +i).value       = btn.dataset.pk;

    /* Isi/box dari barang terpilih */
    var isiDef = parseInt(btn.dataset.isi || 1); if (isiDef < 1) isiDef = 1;
    document.getElementById('isi_'+i).value = isiDef;
    var isiLbl = document.getElementById('isilbl_'+i);
    if (isiLbl) isiLbl.textContent = isiDef;

    /* Satuan */
    document.getElementById('sat_'+i).value = btn.dataset.sat;
    var satLbl = document.getElementById('satlbl_'+i);
    if (satLbl) satLbl.value = btn.dataset.sat;

    /* Kumpulkan semua exp date untuk kode barang ini dari cache */
    var rows = stockCache.filter(function(s) { return s.kode_barang === kd; });
    var sel  = document.getElementById('exp_'+i);
    sel.innerHTML = '<option value="">-- Pilih Expired Date --</option>';

    rows.forEach(function(s) {
        var opt        = document.createElement('option');
        opt.value      = s.exp_date || '';
        var isi        = parseInt(s.isi_per_box || 1);
        var avBox      = Math.floor(parseFloat(s.available_stock||0) / isi);
        var avEcer     = Math.floor(parseFloat(s.available_stock||0) % isi);
        var tgl        = formatTgl(s.exp_date);
        var lotStr     = s.no_lot ? ' | Lot: '+s.no_lot : '';
        var avStr      = ' ['+fmtNum(avBox,0)+' box + '+fmtNum(avEcer,0)+' pcs]';
        opt.textContent = tgl + lotStr + avStr;
        opt.dataset.ton = parseFloat(s.berat_gram  ||0);
        opt.dataset.kub = parseFloat(s.kubikasi_m3 ||0);
        opt.dataset.av  = parseFloat(s.available_stock||0);
        opt.dataset.lot = s.no_lot || '';
        opt.dataset.isi = parseInt(s.isi_per_box||1);
        if (s.exp_date === btn.dataset.exp) opt.selected = true;
        sel.appendChild(opt);
    });

    if (rows.length === 1) sel.selectedIndex = 1;
    sel.dispatchEvent(new Event('change'));

    /* Reset qty saat ganti barang */
    var elBox  = document.getElementById('qtybox_'+i);
    var elEcer = document.getElementById('qtyecer_'+i);
    if (elBox)  elBox.value  = 0;
    if (elEcer) elEcer.value = 0;

    hitungBaris(i);
    $('#modal-stock').modal('hide');
});

/* Buka modal customer */
document.getElementById('btn-pilih-customer').addEventListener('click', function() {
    document.getElementById('customer-search').value = '';
    renderCustomers('');
    $('#modal-customer').modal('show');
    setTimeout(function(){ document.getElementById('customer-search').focus(); }, 400);
});

/* Search customer */
document.getElementById('customer-search').addEventListener('input', function() {
    renderCustomers(this.value);
});

/* Pilih customer dari modal */
document.getElementById('customer-body').addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-pick-customer');
    if (!btn) return;
    document.getElementById('customer_id').value      = btn.dataset.id;
    document.getElementById('customer_name').value    = btn.dataset.nama;
    document.getElementById('customer_display').value = btn.dataset.nama;
    document.getElementById('customer_validation').style.display = 'none';
    $('#modal-customer').modal('hide');
});

/* Search barang */
document.getElementById('stock-search').addEventListener('input', function() { renderStock(stockCache); });

/* Tambah baris */
document.getElementById('btn-add-row').addEventListener('click', function() { tambahBaris({}); });

/* Submit validation */
document.getElementById('form-so').addEventListener('submit', function(e) {
    if (!document.getElementById('customer_id').value) {
        e.preventDefault();
        document.getElementById('customer_validation').style.display = 'block';
        document.getElementById('btn-pilih-customer').focus();
        return;
    }
    var rows = document.querySelectorAll('#item-body tr');
    if (!rows.length) { e.preventDefault(); alert('Minimal 1 item barang harus ditambahkan!'); return; }

    var errs = [];
    rows.forEach(function(tr) {
        var i        = tr.dataset.idx;
        var qtyBox   = parseFloat((document.getElementById('qtybox_'+i)||{value:0}).value)||0;
        var qtySat   = parseFloat((document.getElementById('qtyecer_'+i)||{value:0}).value)||0;
        var isi      = parseInt((document.getElementById('isi_'+i)||{value:1}).value)||1; if(isi<1)isi=1;
        var nm       = (document.getElementById('nm_'+i)||{value:'(barang)'}).value||'(barang)';
        var avTotal  = parseFloat(((document.getElementById('avail-ecer_'+i)||{}).dataset||{}).availTotal||0);
        var avBox    = Math.floor(avTotal / isi);

        var qtyKecil = (qtyBox * isi) + qtySat;

        if (qtyBox <= 0 && qtySat <= 0) {
            errs.push(nm + ': qty box dan eceran keduanya 0');
        } else if (qtyBox > avBox) {
            errs.push(nm + ': qty box ' + qtyBox + ' melebihi stok ' + avBox + ' box');
        } else if (qtyKecil > avTotal) {
            errs.push(nm + ': total ' + qtyKecil + ' pcs melebihi stok ' + avTotal + ' pcs');
        }
    });
    if (errs.length) { e.preventDefault(); alert('Peringatan:\n\u2022 ' + errs.join('\n\u2022 ')); }
});

/* Aktifkan tooltip Bootstrap */
$(function() { $('[data-toggle="tooltip"]').tooltip(); });

/* ------------------------------------------------------------------
   INIT
   ------------------------------------------------------------------ */
if (EDIT_DETAILS.length) {
    EDIT_DETAILS.forEach(function(d) { tambahBaris(d); });
} else {
    tambahBaris({});
}
hitungGrand();
hitungTK();
</script>
</body>