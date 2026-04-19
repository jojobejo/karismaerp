<!-- views/content/sales/so_form.php -->
<?php
    $is_edit   = !empty($so);
    $id_so_val = $is_edit ? $so['id_so'] : $no_so;
    $action    = $is_edit
        ? base_url('sales_order/update/' . implode('/', array_map('rawurlencode', explode('/', $so['id_so']))))
        : base_url('sales_order/store');

    $batas_ton = isset($batas_tonase)   ? $batas_tonase   : 6;
    $batas_kub = isset($batas_kubikasi) ? $batas_kubikasi : 9;

    function escAttr($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
    function escJs($v) {
        return str_replace(['\\', "'", "\r", "\n", "\t"], ['\\\\', "\\'", '', '', ''], (string)$v);
    }
?>
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
                        <!-- INFORMASI SO -->
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
                                                <input type="text" id="customer_display" class="form-control"
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

                        <!-- TONASE & KUBIKASI -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white py-2">
                                    <h3 class="card-title"><i class="fas fa-weight mr-1"></i> Tonase &amp; Kubikasi</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="info-box mb-0">
                                                <span class="info-box-icon bg-success" style="min-height:50px"><i class="fas fa-truck"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Batas Tonase</span>
                                                    <span class="info-box-number"><?= $batas_ton ?> ton</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-box mb-0">
                                                <span class="info-box-icon bg-info" style="min-height:50px"><i class="fas fa-cube"></i></span>
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
                                            <div class="progress-bar bg-success" id="tonase-bar" role="progressbar" style="width:0%"></div>
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
                                            <div class="progress-bar bg-info" id="kubikasi-bar" role="progressbar" style="width:0%"></div>
                                        </div>
                                        <small id="lbl-kubikasi-warn" class="text-danger d-none font-weight-bold">
                                            <i class="fas fa-exclamation-triangle"></i> Melebihi batas kubikasi!
                                        </small>
                                    </div>
                                    <div class="mt-3 p-2 bg-light rounded border small">
                                        Total pcs: <b id="lbl-total-kecil">0</b>
                                        &nbsp;|&nbsp; Box: <b id="lbl-total-box">0</b>
                                        &nbsp;+&nbsp; Eceran: <b id="lbl-total-ecer">0</b> pcs
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABEL ITEM -->
                    <div class="card">
                        <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><i class="fas fa-boxes mr-1"></i> Item Barang</h3>
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
                                            <th style="width:90px" class="text-center">Qty Box</th>
                                            <th style="width:90px" class="text-center">+Eceran (pcs)</th>
                                            <th style="width:85px" class="text-center">=Total Pcs</th>
                                            <th style="width:65px">Satuan</th>
                                            <th style="width:130px">
                                                Harga/Pcs
                                                <small class="d-block text-warning" style="font-size:10px">subtotal = harga × total pcs</small>
                                            </th>
                                            <th style="width:65px">Pajak%</th>
                                            <th style="width:130px">Subtotal</th>
                                            <th style="width:90px">Tonase</th>
                                            <th style="width:90px">Kubikasi</th>
                                            <th style="width:38px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="item-body"></tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="8" class="text-right font-weight-bold">GRAND TOTAL</td>
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

<!-- MODAL: Pilih Barang -->
<div class="modal fade" id="modal-stock" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title"><i class="fas fa-search mr-1"></i> Pilih Barang</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="text" id="stock-search" class="form-control mb-2" placeholder="Cari kode atau nama barang...">
                <div style="max-height:420px;overflow-y:auto">
                    <table class="table table-bordered table-sm table-hover mb-0">
                        <thead class="thead-dark sticky-top">
                            <tr>
                                <th>Nama Barang</th>
                                <th>Exp Date</th>
                                <th>No Lot</th>
                                <th class="text-right">Avail Box</th>
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

<!-- MODAL: Pilih Customer -->
<div class="modal fade" id="modal-customer" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title"><i class="fas fa-users mr-1"></i> Pilih Customer</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="text" id="customer-search" class="form-control mb-2" placeholder="Cari nama customer...">
                <div style="max-height:450px;overflow-y:auto">
                    <table class="table table-bordered table-sm table-hover mb-0">
                        <thead class="thead-dark sticky-top">
                            <tr>
                                <th>Nama Customer</th><th>Telepon</th><th>Alamat</th>
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
   HARGA: user input Harga/Pcs
   Subtotal = harga_per_pcs × total_pcs × (1 + pajak/100)
   total_pcs = (qty_box × isi_per_box) + qty_satuan
   isi_per_box: dari server (p×l×t di PHP)
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
var EDIT_DETAILS = <?= json_encode(array_values($edit_details_safe), JSON_HEX_QUOT|JSON_HEX_APOS|JSON_UNESCAPED_UNICODE) ?>;

<?php
$customers_safe = [];
foreach ($customers as $c) {
    $cs = [];
    foreach ($c as $k => $v) { $cs[$k] = is_string($v) ? mb_convert_encoding($v,'UTF-8','UTF-8') : $v; }
    $customers_safe[] = $cs;
}
?>
var CUSTOMERS = <?= json_encode(array_values($customers_safe), JSON_HEX_QUOT|JSON_HEX_APOS|JSON_UNESCAPED_UNICODE) ?>;

var currentRowIdx = null;
var stockCache    = [];
var rowIdx        = 0;

function fmtNum(n, dec) {
    if (dec === undefined) dec = 2;
    return (parseFloat(n)||0).toLocaleString('id-ID', {minimumFractionDigits:dec, maximumFractionDigits:dec});
}
function formatTgl(ymd) {
    if (!ymd) return '-';
    var p = String(ymd).split('-');
    return p.length === 3 ? p[2]+'/'+p[1]+'/'+p[0] : ymd;
}
function isExpiringSoon(ymd) { return (new Date(ymd) - new Date()) / 86400000 <= 30; }
function esc(v) {
    return String(v||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function getIsi(idx) {
    var el = document.getElementById('isi_'+idx);
    var v  = el ? parseInt(el.value) : 1;
    return v > 0 ? v : 1;
}

/* ------------------------------------------------------------------
   BUAT BARIS
   ------------------------------------------------------------------ */
function buatBaris(idx, d) {
    d = d || {};
    var kd      = d.kd_barang    || '';
    var nm      = d.nama_barang  || '';
    var exp     = d.expired_date || '';
    var lot     = d.no_lot       || '';
    var sat     = d.satuan       || '';
    var hrg     = parseFloat(d.hrg_satuan  || 0);  // harga per pcs
    var pk      = parseFloat(d.hrg_pokok   || 0);
    var pajak   = parseFloat(d.pajak       || 0);
    var akun    = d.kode_akun    || '';
    var beratG  = parseFloat(d.berat_gram  || 0);
    var kubikM  = parseFloat(d.kubikasi_m3 || 0);
    var isi     = parseInt(d.isi_per_box   || 1); if (isi < 1) isi = 1;

    var qtyKecilSaved = parseFloat(d.qty        || 0);
    var qtyBox        = parseFloat(d.qty_box    || 0);
    var qtySatuan     = parseFloat(d.qty_satuan || 0);
    if (!d.qty_box && qtyKecilSaved > 0) {
        qtyBox    = Math.floor(qtyKecilSaved / isi);
        qtySatuan = qtyKecilSaved % isi;
    }

    var av      = parseFloat(d.available_stock || 0);
    var avBox   = Math.floor(av / isi);
    var avEcer  = Math.floor(av % isi);
    var qtyKecil = (qtyBox * isi) + qtySatuan;

    // Subtotal = harga_per_pcs × total_pcs
    var sub = hrg * qtyKecil * (1 + pajak / 100);
    var ton = qtyKecil * beratG / 1000000;
    var kub = qtyKecil * kubikM;

    var expOption = exp
        ? '<option value="'+esc(exp)+'" data-lot="'+esc(lot)+'" data-av="'+av+'" data-ton="'+beratG+'" data-kub="'+kubikM+'" data-isi="'+isi+'" selected>'
          +esc(formatTgl(exp))+(lot?' | Lot: '+esc(lot):'')+'</option>'
        : '<option value="">-- Pilih barang dulu --</option>';

    var html = '<tr id="row-'+idx+'" data-idx="'+idx+'">';

    // Kolom Barang
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
    html +=   '<div class="flex-grow-1"><small class="text-muted" id="kdlbl_'+idx+'">'+(kd||'&mdash;')+'</small><br>';
    html +=   '<span id="nmlbl_'+idx+'">'+(nm?esc(nm):'&mdash;')+'</span></div>';
    html +=   '<button type="button" class="btn btn-xs btn-outline-primary ml-1 btn-pick" data-idx="'+idx+'"><i class="fas fa-search"></i> Pilih</button>';
    html += '</div>';
    html += '<div class="mt-1 small">';
    html += '<span class="text-success font-weight-bold">Avail: <span id="avail-box_'+idx+'" data-avail-box="'+avBox+'">'+fmtNum(avBox,0)+'</span> box</span>';
    html += ' + <span class="text-info"><span id="avail-ecer_'+idx+'" data-avail-ecer="'+avEcer+'" data-avail-total="'+av+'">'+fmtNum(avEcer,0)+'</span> pcs ecer</span>';
    html += '</div></td>';

    // Expired / Lot
    html += '<td>';
    html += '<select name="expired_date[]" id="exp_'+idx+'" class="form-control form-control-sm mb-1" required>';
    html += '<option value="">-- Pilih barang --</option>'+expOption;
    html += '</select>';
    html += '<input type="hidden" name="no_lot[]" id="lot_'+idx+'" value="'+esc(lot)+'">';
    html += '<small class="text-muted" id="lotlbl_'+idx+'">'+(lot?'Lot: '+esc(lot):'')+'</small>';
    html += '</td>';

    // Qty Box
    html += '<td><input type="number" step="1" min="0" name="qty_box[]" id="qtybox_'+idx+'" class="form-control form-control-sm text-center" value="'+qtyBox+'">';
    html += '<small class="text-muted d-block text-center">maks <span id="maxbox_'+idx+'">'+fmtNum(avBox,0)+'</span></small></td>';

    // +Eceran
    html += '<td><input type="number" step="1" min="0" name="qty_satuan[]" id="qtyecer_'+idx+'" class="form-control form-control-sm text-center" value="'+qtySatuan+'">';
    html += '<small class="text-muted d-block text-center">maks <span id="maxecer_'+idx+'">'+fmtNum(avEcer,0)+'</span> pcs</small></td>';

    // =Total Pcs
    html += '<td class="text-center align-middle"><b id="qtylbl_'+idx+'">'+fmtNum(qtyKecil,0)+'</b><br><small class="text-muted">pcs</small></td>';

    // Satuan
    html += '<td><input type="text" id="satlbl_'+idx+'" class="form-control form-control-sm" value="'+esc(sat)+'" readonly></td>';

    // Harga/Pcs
    html += '<td>';
    html += '<input type="number" step="0.01" name="hrg_satuan[]" id="hrg_'+idx+'" class="form-control form-control-sm" value="'+(hrg||'')+'" required>';
    html += '<div id="hrgwarn_'+idx+'" class="mt-1"></div>';
    html += '</td>';

    // Pajak
    html += '<td><input type="number" step="0.01" name="pajak[]" id="pjk_'+idx+'" class="form-control form-control-sm" value="'+pajak+'" min="0" max="100"></td>';

    // Subtotal
    html += '<td class="text-right align-middle"><b id="sub_'+idx+'">'+fmtNum(sub)+'</b></td>';

    // Tonase, Kubikasi, Hapus
    html += '<td class="text-right align-middle small"><span id="ton_'+idx+'">'+fmtNum(ton,4)+'</span> ton</td>';
    html += '<td class="text-right align-middle small"><span id="kub_'+idx+'">'+fmtNum(kub,5)+'</span> m³</td>';
    html += '<td class="text-center align-middle"><button type="button" class="btn btn-xs btn-danger btn-remove" data-idx="'+idx+'"><i class="fas fa-trash"></i></button></td>';
    html += '</tr>';
    return html;
}

function tambahBaris(d) {
    document.getElementById('item-body').insertAdjacentHTML('beforeend', buatBaris(rowIdx, d||{}));
    bindBaris(rowIdx);
    rowIdx++;
}

function bindBaris(idx) {
    ['hrg_','qtybox_','qtyecer_','pjk_'].forEach(function(f) {
        var el = document.getElementById(f+idx);
        if (el) el.addEventListener('input', function() { hitungBaris(idx); });
    });

    var elBox = document.getElementById('qtybox_'+idx);
    if (elBox) elBox.addEventListener('change', function() {
        var avTotal = parseFloat(document.getElementById('avail-ecer_'+idx).dataset.availTotal||0);
        var isi = getIsi(idx);
        var maxBox = Math.floor(avTotal/isi);
        var v = parseInt(this.value)||0;
        if (v > maxBox) { this.value = maxBox; hitungBaris(idx); alert('Qty box melebihi stok! Maks '+maxBox+' box.'); }
    });

    var elEcer = document.getElementById('qtyecer_'+idx);
    if (elEcer) elEcer.addEventListener('change', function() {
        var avTotal = parseFloat(document.getElementById('avail-ecer_'+idx).dataset.availTotal||0);
        var isi = getIsi(idx);
        var qtyBox = parseInt(document.getElementById('qtybox_'+idx).value)||0;
        var sisaStok = avTotal - (qtyBox * isi);
        var v = parseInt(this.value)||0;
        if (v > sisaStok) {
            this.value = Math.max(0, Math.floor(sisaStok));
            hitungBaris(idx);
            alert(sisaStok<=0 ? 'Stok habis untuk qty box ini.' : 'Maks '+Math.floor(sisaStok)+' pcs eceran.');
        }
    });

    var sel = document.getElementById('exp_'+idx);
    if (sel) sel.addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) return;
        var av  = parseFloat(opt.dataset.av  || 0);
        var lot = opt.dataset.lot || '';
        var bg  = parseFloat(opt.dataset.ton || 0);
        var km  = parseFloat(opt.dataset.kub || 0);
        var isi = parseInt(opt.dataset.isi   || 1); if (isi<1) isi=1;

        document.getElementById('bg_' +idx).value = bg;
        document.getElementById('km_' +idx).value = km;
        document.getElementById('isi_'+idx).value = isi;
        document.getElementById('lot_'+idx).value = lot;
        document.getElementById('lotlbl_'+idx).textContent = lot ? 'Lot: '+lot : '';

        var avBox  = Math.floor(av/isi);
        var avEcer = Math.floor(av%isi);
        var elB = document.getElementById('avail-box_'+idx);
        var elE = document.getElementById('avail-ecer_'+idx);
        if (elB) { elB.textContent = fmtNum(avBox,0); elB.dataset.availBox = avBox; }
        if (elE) { elE.textContent = fmtNum(avEcer,0); elE.dataset.availEcer = avEcer; elE.dataset.availTotal = av; }
        document.getElementById('maxbox_'+idx).textContent  = fmtNum(avBox,0);
        document.getElementById('maxecer_'+idx).textContent = fmtNum(avEcer,0)+' pcs';
        hitungBaris(idx);
    });
}

/* ------------------------------------------------------------------
   HITUNG BARIS
   Subtotal = hrg_per_pcs × total_pcs × (1+pajak/100)
   hrg_satuan yang diinput user = harga per PCS
   ------------------------------------------------------------------ */
function hitungBaris(idx) {
    function val(id) { var e=document.getElementById(id); return e?parseFloat(e.value)||0:0; }

    var hrg    = val('hrg_'+idx);    // harga per PCS
    var qtyBox = val('qtybox_'+idx);
    var qtySat = val('qtyecer_'+idx);
    var pjk    = val('pjk_'+idx);
    var pk     = val('pk_'+idx);
    var bg     = val('bg_'+idx);
    var km     = val('km_'+idx);
    var isi    = getIsi(idx);

    var qtyKecil = (qtyBox * isi) + qtySat;   // total pcs

    // Update tampilan total pcs
    var elQ = document.getElementById('qtylbl_'+idx);
    if (elQ) elQ.textContent = fmtNum(qtyKecil, 0);

    // Subtotal = harga/pcs × total_pcs + pajak
    var subBefore = hrg * qtyKecil;
    var tot       = subBefore * (1 + pjk/100);
    var elSub = document.getElementById('sub_'+idx);
    if (elSub) elSub.textContent = fmtNum(tot);

    // Peringatan harga < HPP
    var wEl = document.getElementById('hrgwarn_'+idx);
    if (wEl) wEl.innerHTML = (hrg>0 && hrg<pk)
        ? '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Di bawah HPP</span>' : '';

    // Tonase & kubikasi
    var ton = qtyKecil * bg / 1000000;
    var kub = qtyKecil * km;
    var elT = document.getElementById('ton_'+idx);
    var elK = document.getElementById('kub_'+idx);
    if (elT) elT.textContent = fmtNum(ton,4);
    if (elK) elK.textContent = fmtNum(kub,5);

    hitungGrand();
    hitungTK();
}

function hitungGrand() {
    var grand = 0;
    document.querySelectorAll('#item-body tr').forEach(function(tr) {
        var i      = tr.dataset.idx;
        var hrg    = parseFloat((document.getElementById('hrg_'+i)||{value:0}).value)||0;
        var qBox   = parseFloat((document.getElementById('qtybox_'+i)||{value:0}).value)||0;
        var qEcer  = parseFloat((document.getElementById('qtyecer_'+i)||{value:0}).value)||0;
        var pjk    = parseFloat((document.getElementById('pjk_'+i)||{value:0}).value)||0;
        var isi    = getIsi(i);
        var total  = (qBox*isi)+qEcer;
        grand += hrg * total * (1+pjk/100);
    });
    document.getElementById('total-grand').textContent = fmtNum(grand);
}

function hitungTK() {
    var totTon=0,totKub=0,totKecil=0,totBox=0,totEcer=0;
    document.querySelectorAll('#item-body tr').forEach(function(tr) {
        var i     = tr.dataset.idx;
        var qBox  = parseFloat((document.getElementById('qtybox_'+i)||{value:0}).value)||0;
        var qEcer = parseFloat((document.getElementById('qtyecer_'+i)||{value:0}).value)||0;
        var isi   = getIsi(i);
        var bg    = parseFloat((document.getElementById('bg_'+i)||{value:0}).value)||0;
        var km    = parseFloat((document.getElementById('km_'+i)||{value:0}).value)||0;
        var qK    = (qBox*isi)+qEcer;
        totTon   += qK*bg/1000000;
        totKub   += qK*km;
        totKecil += qK;
        totBox   += qBox;
        totEcer  += qEcer;
    });
    document.getElementById('lbl-tonase').textContent         = fmtNum(totTon,3);
    document.getElementById('lbl-kubikasi').textContent       = fmtNum(totKub,5);
    document.getElementById('lbl-total-kecil').textContent    = fmtNum(totKecil,0);
    document.getElementById('lbl-total-box').textContent      = fmtNum(totBox,0);
    document.getElementById('lbl-total-ecer').textContent     = fmtNum(totEcer,0);
    document.getElementById('total-tonase-tbl').textContent   = fmtNum(totTon,3)+' ton';
    document.getElementById('total-kubikasi-tbl').textContent = fmtNum(totKub,5)+' m³';

    var pctT = BATAS_TONASE>0 ? Math.min(totTon/BATAS_TONASE*100,100) : 0;
    var bT   = document.getElementById('tonase-bar');
    if (bT) { bT.style.width=pctT.toFixed(2)+'%'; bT.className='progress-bar '+(totTon>BATAS_TONASE?'bg-danger':'bg-success'); }
    var wT = document.getElementById('lbl-tonase-warn');
    if (wT) wT.classList.toggle('d-none', totTon<=BATAS_TONASE);

    var pctK = BATAS_KUBIKASI>0 ? Math.min(totKub/BATAS_KUBIKASI*100,100) : 0;
    var bK   = document.getElementById('kubikasi-bar');
    if (bK) { bK.style.width=pctK.toFixed(2)+'%'; bK.className='progress-bar '+(totKub>BATAS_KUBIKASI?'bg-danger':'bg-info'); }
    var wK = document.getElementById('lbl-kubikasi-warn');
    if (wK) wK.classList.toggle('d-none', totKub<=BATAS_KUBIKASI);
}

/* LOAD STOK */
function loadStock() {
    document.getElementById('stock-body').innerHTML =
        '<tr><td colspan="11" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';
    fetch(BASE_URL+'sales_order/get_stock?gudang_id='+encodeURIComponent(GUDANG_ID))
        .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
        .then(function(res){
            if(res.status!=='ok') throw new Error(res.message||'Error');
            stockCache = res.data||[];
            renderStock(stockCache);
        })
        .catch(function(err){
            document.getElementById('stock-body').innerHTML =
                '<tr><td colspan="11" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> '+err.message+'</td></tr>';
        });
}

function renderStock(data) {
    var q = (document.getElementById('stock-search').value||'').toLowerCase();
    var filtered = q ? data.filter(function(d){
        return String(d.kode_barang||'').toLowerCase().indexOf(q)>=0
            || String(d.nama_barang||'').toLowerCase().indexOf(q)>=0;
    }) : data;

    if (!filtered.length) {
        document.getElementById('stock-body').innerHTML =
            '<tr><td colspan="11" class="text-center text-muted"><i class="fas fa-inbox"></i> Tidak ada stok</td></tr>';
        return;
    }

    var html='', lastKd=null;
    filtered.forEach(function(d){
        var isNew   = d.kode_barang !== lastKd; lastKd = d.kode_barang;
        var isi     = parseInt(d.isi_per_box||1);
        var avTotal = parseFloat(d.available_stock||0);
        var avBox   = parseInt(d.available_box||0);
        var avEcer  = parseInt(d.available_ecer||0);
        var beratKg = (parseFloat(d.berat_gram||0)/1000).toFixed(3);
        var kubStr  = parseFloat(d.kubikasi_m3||0).toFixed(6);

        html += '<tr class="'+(isNew?'table-light':'')+'">';
        html += '<td><small class="text-muted d-block">'+esc(d.kode_barang)+'</small>';
        html += isNew?'<b>'+esc(d.nama_barang)+'</b>':'<span class="text-muted">&#x21B3;</span> '+esc(d.nama_barang);
        html += '</td>';
        html += '<td>'+(d.exp_date?'<span class="badge '+(isExpiringSoon(d.exp_date)?'badge-warning':'badge-success')+'">'+formatTgl(d.exp_date)+'</span>':'-')+'</td>';
        html += '<td>'+(d.no_lot||'-')+'</td>';
        html += '<td class="text-right"><b class="text-success">'+fmtNum(avBox,0)+' box</b></td>';
        html += '<td class="text-right"><span class="text-info">'+fmtNum(avEcer,0)+' pcs</span></td>';
        html += '<td class="text-right text-muted"><small>'+fmtNum(avTotal,0)+' pcs</small></td>';
        html += '<td class="text-center"><span class="badge badge-secondary">'+isi+'</span></td>';
        html += '<td>'+esc(d.satuan||'')+'</td>';
        html += '<td class="text-right"><small>'+beratKg+' kg</small></td>';
        html += '<td class="text-right"><small>'+kubStr+' m³</small></td>';
        html += '<td class="text-center">';
        html += '<button type="button" class="btn btn-xs btn-primary btn-pick-stock"';
        html += ' data-kd="'+esc(d.kode_barang||'')+'" data-nm="'+esc(d.nama_barang||'')+'"';
        html += ' data-exp="'+esc(d.exp_date||'')+'" data-lot="'+esc(d.no_lot||'')+'"';
        html += ' data-sat="'+esc(d.satuan||'')+'" data-av="'+avTotal+'"';
        html += ' data-ton="'+(parseFloat(d.berat_gram)||0)+'" data-kub="'+(parseFloat(d.kubikasi_m3)||0)+'"';
        html += ' data-isi="'+isi+'" data-pk="'+(parseFloat(d.hpp)||0)+'">';
        html += '<i class="fas fa-check"></i> Pilih</button></td></tr>';
    });
    document.getElementById('stock-body').innerHTML = html;
}

function renderCustomers(q) {
    q = (q||'').toLowerCase();
    var filtered = q ? CUSTOMERS.filter(function(c){ return String(c.nama_customer||'').toLowerCase().indexOf(q)>=0; }) : CUSTOMERS;
    if (!filtered.length) {
        document.getElementById('customer-body').innerHTML='<tr><td colspan="4" class="text-center text-muted">Tidak ada customer.</td></tr>';
        return;
    }
    var html='';
    filtered.forEach(function(c){
        html+='<tr><td><b>'+esc(c.nama_customer||'')+'</b></td>';
        html+='<td><small>'+esc(c.telepon||c.no_telp||'-')+'</small></td>';
        html+='<td><small>'+esc(c.alamat||'-')+'</small></td>';
        html+='<td class="text-center"><button type="button" class="btn btn-xs btn-primary btn-pick-customer" data-id="'+esc(c.id)+'" data-nama="'+esc(c.nama_customer)+'"><i class="fas fa-check"></i></button></td></tr>';
    });
    document.getElementById('customer-body').innerHTML = html;
}

/* EVENTS */
document.getElementById('item-body').addEventListener('click', function(e) {
    var r = e.target.closest('.btn-remove');
    if (r) { r.closest('tr').remove(); hitungGrand(); hitungTK(); return; }
    var p = e.target.closest('.btn-pick');
    if (p) { currentRowIdx=parseInt(p.dataset.idx); loadStock(); $('#modal-stock').modal('show'); }
});

document.getElementById('stock-body').addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-pick-stock');
    if (!btn) return;
    var i = currentRowIdx;
    var kd = btn.dataset.kd;

    document.getElementById('kd_'+i).value       = kd;
    document.getElementById('nm_'+i).value       = btn.dataset.nm;
    document.getElementById('kdlbl_'+i).textContent = kd;
    document.getElementById('nmlbl_'+i).textContent = btn.dataset.nm;
    document.getElementById('pk_'+i).value       = btn.dataset.pk;
    document.getElementById('sat_'+i).value      = btn.dataset.sat;
    document.getElementById('satlbl_'+i).value   = btn.dataset.sat;

    var isiDef = parseInt(btn.dataset.isi||1); if(isiDef<1) isiDef=1;
    document.getElementById('isi_'+i).value = isiDef;

    var rows = stockCache.filter(function(s){ return s.kode_barang===kd; });
    var sel  = document.getElementById('exp_'+i);
    sel.innerHTML = '<option value="">-- Pilih Expired Date --</option>';
    rows.forEach(function(s){
        var opt = document.createElement('option');
        opt.value = s.exp_date||'';
        var isi   = parseInt(s.isi_per_box||1);
        var avBox = Math.floor(parseFloat(s.available_stock||0)/isi);
        var avEcer= Math.floor(parseFloat(s.available_stock||0)%isi);
        opt.textContent = formatTgl(s.exp_date)+(s.no_lot?' | Lot: '+s.no_lot:'')+' ['+fmtNum(avBox,0)+' box + '+fmtNum(avEcer,0)+' pcs]';
        opt.dataset.ton = parseFloat(s.berat_gram||0);
        opt.dataset.kub = parseFloat(s.kubikasi_m3||0);
        opt.dataset.av  = parseFloat(s.available_stock||0);
        opt.dataset.lot = s.no_lot||'';
        opt.dataset.isi = parseInt(s.isi_per_box||1);
        if (s.exp_date===btn.dataset.exp) opt.selected=true;
        sel.appendChild(opt);
    });
    if (rows.length===1) sel.selectedIndex=1;
    sel.dispatchEvent(new Event('change'));

    document.getElementById('qtybox_'+i).value  = 0;
    document.getElementById('qtyecer_'+i).value = 0;
    hitungBaris(i);
    $('#modal-stock').modal('hide');
});

document.getElementById('btn-pilih-customer').addEventListener('click', function(){
    document.getElementById('customer-search').value='';
    renderCustomers('');
    $('#modal-customer').modal('show');
    setTimeout(function(){ document.getElementById('customer-search').focus(); }, 400);
});
document.getElementById('customer-search').addEventListener('input', function(){ renderCustomers(this.value); });
document.getElementById('customer-body').addEventListener('click', function(e){
    var btn=e.target.closest('.btn-pick-customer'); if(!btn) return;
    document.getElementById('customer_id').value      = btn.dataset.id;
    document.getElementById('customer_name').value    = btn.dataset.nama;
    document.getElementById('customer_display').value = btn.dataset.nama;
    document.getElementById('customer_validation').style.display='none';
    $('#modal-customer').modal('hide');
});
document.getElementById('stock-search').addEventListener('input', function(){ renderStock(stockCache); });
document.getElementById('btn-add-row').addEventListener('click', function(){ tambahBaris({}); });

document.getElementById('form-so').addEventListener('submit', function(e){
    if (!document.getElementById('customer_id').value) {
        e.preventDefault();
        document.getElementById('customer_validation').style.display='block';
        document.getElementById('btn-pilih-customer').focus();
        return;
    }
    var rows=document.querySelectorAll('#item-body tr');
    if (!rows.length) { e.preventDefault(); alert('Minimal 1 item barang!'); return; }
    var errs=[];
    rows.forEach(function(tr){
        var i=tr.dataset.idx;
        var qBox  = parseFloat((document.getElementById('qtybox_'+i)||{value:0}).value)||0;
        var qEcer = parseFloat((document.getElementById('qtyecer_'+i)||{value:0}).value)||0;
        var isi   = getIsi(i);
        var nm    = (document.getElementById('nm_'+i)||{value:'(barang)'}).value||'(barang)';
        var avTotal = parseFloat(((document.getElementById('avail-ecer_'+i)||{dataset:{}}).dataset||{}).availTotal||0);
        var qKecil  = (qBox*isi)+qEcer;
        if (qBox<=0 && qEcer<=0) errs.push(nm+': qty box dan eceran keduanya 0');
        else if (qBox>Math.floor(avTotal/isi)) errs.push(nm+': qty box melebihi stok '+Math.floor(avTotal/isi)+' box');
        else if (qKecil>avTotal) errs.push(nm+': total '+qKecil+' pcs melebihi stok '+avTotal+' pcs');
    });
    if (errs.length) { e.preventDefault(); alert('Peringatan:\n\u2022 '+errs.join('\n\u2022 ')); }
});

/* INIT */
if (EDIT_DETAILS.length) { EDIT_DETAILS.forEach(function(d){ tambahBaris(d); }); }
else { tambahBaris({}); }
hitungGrand(); hitungTK();
</script>
</body>