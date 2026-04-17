<!-- views/content/sales/so_form.php -->
<?php
    $is_edit   = !empty($so);
    $id_so_val = $is_edit ? $so['id_so'] : $no_so;
    $action    = $is_edit
        ? base_url('sales_order/update/' . $so['id_so'])
        : base_url('sales_order/store');

    // Batas default dari controller (dikirim dari konstanta model)
    $batas_ton = $batas_tonase   ?? 6;
    $batas_kub = $batas_kubikasi ?? 9;
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
                        <!-- Informasi SO -->
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
                                            <select name="customer_id" id="customer_id" class="form-control" required>
                                                <option value="">-- Pilih Customer --</option>
                                                <?php foreach ($customers as $c): ?>
                                                    <option value="<?= $c['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($c['nama_customer']) ?>"
                                                        <?= ($is_edit && $so['customer_id'] == $c['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($c['nama_customer']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="customer_name" id="customer_name"
                                                value="<?= $is_edit ? htmlspecialchars($so['customer_name']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label">Gudang</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="gudang_id" readonly
                                                value="<?= htmlspecialchars($gudang_id) ?>">
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

                        <!-- Tonase & Kubikasi (otomatis, batas fixed) -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white py-2">
                                    <h3 class="card-title">
                                        <i class="fas fa-weight mr-1"></i> Tonase & Kubikasi
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <!-- Info batas (read-only) -->
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

                                    <!-- Progress Tonase -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>
                                                <b>Tonase:</b>
                                                <span id="lbl-tonase">0,000</span> ton
                                            </small>
                                            <small class="text-muted">Maks <?= $batas_ton ?> ton</small>
                                        </div>
                                        <div class="progress" style="height:12px">
                                            <div class="progress-bar bg-success" id="tonase-bar"
                                                 role="progressbar" style="width:0%">
                                            </div>
                                        </div>
                                        <small id="lbl-tonase-warn" class="text-danger d-none font-weight-bold">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Melebihi batas tonase!
                                        </small>
                                    </div>

                                    <!-- Progress Kubikasi -->
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>
                                                <b>Kubikasi:</b>
                                                <span id="lbl-kubikasi">0,00000</span> m³
                                            </small>
                                            <small class="text-muted">Maks <?= $batas_kub ?> m³</small>
                                        </div>
                                        <div class="progress" style="height:12px">
                                            <div class="progress-bar bg-info" id="kubikasi-bar"
                                                 role="progressbar" style="width:0%">
                                            </div>
                                        </div>
                                        <small id="lbl-kubikasi-warn" class="text-danger d-none font-weight-bold">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Melebihi batas kubikasi!
                                        </small>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABEL ITEM BARANG -->
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
                                            <th style="min-width:230px">Barang</th>
                                            <th style="min-width:160px">Expired / No Lot</th>
                                            <th style="width:90px">Qty</th>
                                            <th style="width:70px">Satuan</th>
                                            <th style="width:100px">Stok Avail</th>
                                            <th style="width:130px">Harga Satuan</th>
                                            <th style="width:70px">Pajak %</th>
                                            <th style="width:130px">Subtotal</th>
                                            <th style="width:100px">Tonase</th>
                                            <th style="width:100px">Kubikasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="item-body"></tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="7" class="text-right font-weight-bold">GRAND TOTAL</td>
                                            <td class="text-right font-weight-bold" id="total-grand">0</td>
                                            <td class="text-right font-weight-bold small" id="total-tonase-tbl">0 ton</td>
                                            <td class="text-right font-weight-bold small" id="total-kubikasi-tbl">0 m³</td>
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
                <input type="text" id="stock-search" class="form-control mb-2"
                    placeholder="Cari kode atau nama barang...">
                <div style="max-height:420px;overflow-y:auto">
                    <table class="table table-bordered table-sm table-hover mb-0">
                        <thead class="thead-dark sticky-top">
                            <tr>
                                <th>Nama Barang</th>
                                <th>Exp Date</th>
                                <th>No Lot</th>
                                <th class="text-right">Stok Avail</th>
                                <th>Satuan</th>
                                <th class="text-right">Berat/sat</th>
                                <th class="text-right">Kubik/sat</th>
                                <th class="text-center">Pilih</th>
                            </tr>
                        </thead>
                        <tbody id="stock-body">
                            <tr><td colspan="8" class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Memuat...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* ===================================================================
   SO FORM — Karisma ERP
   Tonase otomatis : qty × berat_gram / 1.000.000   (gram → ton)
   Kubikasi otomatis: qty × kubikasi_m3              (sudah m³)
   Batas default   : <?= $batas_ton ?> ton, <?= $batas_kub ?> m³
   =================================================================== */

const BASE_URL      = '<?= base_url() ?>';
const GUDANG_ID     = '<?= htmlspecialchars($gudang_id) ?>';
const BATAS_TONASE  = <?= (float)$batas_ton ?>;    // ton
const BATAS_KUBIKASI= <?= (float)$batas_kub ?>;    // m³

<?php if ($is_edit && !empty($details)): ?>
const EDIT_DETAILS = <?= json_encode(array_values($details)) ?>;
<?php else: ?>
const EDIT_DETAILS = [];
<?php endif; ?>

let currentRowIdx = null;
let stockCache    = [];
let rowIdx        = 0;

// -------------------------------------------------------------------
// UTILS
// -------------------------------------------------------------------
function fmtNum(n, dec = 2) {
    return parseFloat(n || 0).toLocaleString('id-ID', {
        minimumFractionDigits: dec, maximumFractionDigits: dec
    });
}
function formatTgl(ymd) {
    if (!ymd) return '-';
    const p = ymd.split('-');
    return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : ymd;
}
function isExpiringSoon(ymd) {
    if (!ymd) return false;
    return (new Date(ymd) - new Date()) / 86400000 <= 30;
}

// -------------------------------------------------------------------
// BUAT BARIS
// -------------------------------------------------------------------
function buatBaris(idx, d) {
    d = d || {};
    const kd       = d.kd_barang     || '';
    const nm       = d.nama_barang   || '';
    const exp      = d.expired_date  || '';
    const lot      = d.no_lot        || '';
    const qty      = d.qty           || '';
    const sat      = d.satuan        || '';
    const hrg      = d.hrg_satuan    || '';
    const pk       = d.hrg_pokok     || 0;
    const pajak    = d.pajak         || 0;
    const akun     = d.kode_akun     || '';
    const avail    = d.available_stock|| 0;
    // berat_gram & kubikasi_m3 diambil dari data edit atau diisi saat pilih barang
    const beratG   = d.berat_gram    || 0;
    const kubikM   = d.kubikasi_m3   || 0;
    const subtotal = (parseFloat(hrg) * parseFloat(qty) || 0) * (1 + parseFloat(pajak)/100);

    // Hitung tonase & kubikasi baris ini
    const ton = (parseFloat(qty)||0) * (parseFloat(beratG)||0) / 1000000;
    const kub = (parseFloat(qty)||0) * (parseFloat(kubikM)||0);

    // Opsi expired date untuk mode edit (akan diisi ulang saat pilih modal)
    const expOption = exp
        ? `<option value="${exp}" data-lot="${lot}" data-av="${avail}" data-ton="${beratG}" data-kub="${kubikM}" selected>
               ${formatTgl(exp)}${lot ? ' | Lot: '+lot : ''}
           </option>`
        : '<option value="">-- Pilih barang dulu --</option>';

    return `
    <tr id="row-${idx}" data-idx="${idx}">
        <td>
            <input type="hidden" name="produk_id[]"    value="${kd}">
            <input type="hidden" name="kd_barang[]"    id="kd_${idx}"    value="${kd}">
            <input type="hidden" name="nama_barang[]"  id="nm_${idx}"    value="${nm}">
            <input type="hidden" name="satuan[]"       id="sat_${idx}"   value="${sat}">
            <input type="hidden" name="hrg_pokok[]"    id="pk_${idx}"    value="${pk}">
            <input type="hidden" name="kode_akun[]"    value="${akun}">
            <!-- berat_gram & kubikasi_m3: dikirim ke server untuk hitung tonase/kubikasi -->
            <input type="hidden" name="berat_gram[]"   id="bg_${idx}"    value="${beratG}">
            <input type="hidden" name="kubikasi_m3[]"  id="km_${idx}"    value="${kubikM}">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <small class="text-muted" id="kdlbl_${idx}">${kd || '—'}</small><br>
                    <span id="nmlbl_${idx}">${nm || '—'}</span>
                </div>
                <button type="button" class="btn btn-xs btn-outline-primary ml-1 btn-pick"
                    data-idx="${idx}">
                    <i class="fas fa-search"></i> Pilih
                </button>
            </div>
            <small class="text-muted">
                Tersedia: <b id="avail_${idx}" data-avail="${avail}">${fmtNum(avail)}</b>
            </small>
        </td>
        <td>
            <select name="expired_date[]" id="exp_${idx}"
                    class="form-control form-control-sm mb-1" required>
                <option value="">-- Pilih barang --</option>
                ${expOption}
            </select>
            <input type="hidden" name="no_lot[]" id="lot_${idx}" value="${lot}">
            <small class="text-muted" id="lotlbl_${idx}">${lot ? 'Lot: '+lot : ''}</small>
        </td>
        <td>
            <input type="number" step="0.001" name="qty[]" id="qty_${idx}"
                class="form-control form-control-sm" value="${qty}" min="0.001" required>
        </td>
        <td>
            <input type="text" name="satuan_lbl[]" id="satlbl_${idx}"
                class="form-control form-control-sm" value="${sat}" readonly>
        </td>
        <td class="text-center align-middle">
            <span id="availnum_${idx}" data-avail="${avail}">${fmtNum(avail)}</span>
        </td>
        <td>
            <input type="number" step="0.01" name="hrg_satuan[]" id="hrg_${idx}"
                class="form-control form-control-sm" value="${hrg}" required>
            <div id="hrgwarn_${idx}" class="mt-1"></div>
        </td>
        <td>
            <input type="number" step="0.01" name="pajak[]" id="pjk_${idx}"
                class="form-control form-control-sm" value="${pajak}" min="0" max="100">
        </td>
        <td class="text-right align-middle">
            <b id="sub_${idx}">${fmtNum(subtotal)}</b>
        </td>
        <td class="text-right align-middle small">
            <!-- tonase baris ini (tampilan saja, dihitung otomatis) -->
            <span id="ton_${idx}">${fmtNum(ton,4)}</span> ton
        </td>
        <td class="text-right align-middle small">
            <span id="kub_${idx}">${fmtNum(kub,5)}</span> m³
        </td>
    </tr>`;
}

// -------------------------------------------------------------------
// TAMBAH & BIND BARIS
// -------------------------------------------------------------------
function tambahBaris(d) {
    document.getElementById('item-body').insertAdjacentHTML('beforeend', buatBaris(rowIdx, d||{}));
    bindBaris(rowIdx);
    rowIdx++;
}

function bindBaris(idx) {
    ['hrg_','qty_','pjk_'].forEach(f => {
        const el = document.getElementById(f + idx);
        if (el) el.addEventListener('input', () => hitungBaris(idx));
    });

    // Saat pilih expired date dari dropdown → update lot, avail, berat, kubikasi
    const sel = document.getElementById('exp_' + idx);
    if (sel) {
        sel.addEventListener('change', function () {
            const opt  = this.options[this.selectedIndex];
            if (!opt || !opt.value) return;
            const av   = parseFloat(opt.dataset.av  || 0);
            const lot  = opt.dataset.lot || '';
            const bg   = parseFloat(opt.dataset.ton || 0);  // data-ton = berat_gram
            const km   = parseFloat(opt.dataset.kub || 0);  // data-kub = kubikasi_m3

            // Update lot
            document.getElementById('lot_'    + idx).value       = lot;
            document.getElementById('lotlbl_' + idx).textContent = lot ? 'Lot: '+lot : '';

            // Update berat & kubikasi hidden (untuk dikirim ke server)
            document.getElementById('bg_' + idx).value = bg;
            document.getElementById('km_' + idx).value = km;

            // Update avail
            const elAv = document.getElementById('avail_' + idx);
            if (elAv) { elAv.textContent = fmtNum(av); elAv.dataset.avail = av; }
            const elAn = document.getElementById('availnum_' + idx);
            if (elAn) { elAn.textContent = fmtNum(av); elAn.dataset.avail = av; }

            hitungBaris(idx);
        });
    }
}

// -------------------------------------------------------------------
// HITUNG SUBTOTAL + TONASE/KUBIKASI BARIS
// -------------------------------------------------------------------
function hitungBaris(idx) {
    const hrg = parseFloat(document.getElementById('hrg_' + idx)?.value || 0);
    const qty = parseFloat(document.getElementById('qty_' + idx)?.value || 0);
    const pjk = parseFloat(document.getElementById('pjk_' + idx)?.value || 0);
    const pk  = parseFloat(document.getElementById('pk_'  + idx)?.value || 0);
    const bg  = parseFloat(document.getElementById('bg_'  + idx)?.value || 0); // gram
    const km  = parseFloat(document.getElementById('km_'  + idx)?.value || 0); // m³

    // Subtotal
    const sub = hrg * qty;
    const tot = sub + (sub * pjk / 100);
    const elSub = document.getElementById('sub_' + idx);
    if (elSub) elSub.textContent = fmtNum(tot);

    // Peringatan harga < HPP
    const warnEl = document.getElementById('hrgwarn_' + idx);
    if (warnEl) {
        warnEl.innerHTML = (hrg > 0 && hrg < pk)
            ? '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Di bawah HPP</span>'
            : '';
    }

    // Tonase & kubikasi per baris (tampilan)
    const tonase   = qty * bg / 1000000;  // gram → ton
    const kubikasi = qty * km;            // m³
    const elTon = document.getElementById('ton_' + idx);
    const elKub = document.getElementById('kub_' + idx);
    if (elTon) elTon.textContent = fmtNum(tonase, 4);
    if (elKub) elKub.textContent = fmtNum(kubikasi, 5);

    hitungGrand();
    hitungTK();
}

function hitungGrand() {
    let grand = 0;
    document.querySelectorAll('#item-body tr').forEach(tr => {
        const i   = tr.dataset.idx;
        const hrg = parseFloat(document.getElementById('hrg_' + i)?.value || 0);
        const qty = parseFloat(document.getElementById('qty_' + i)?.value || 0);
        const pjk = parseFloat(document.getElementById('pjk_' + i)?.value || 0);
        const sub = hrg * qty;
        grand += sub + (sub * pjk / 100);
    });
    document.getElementById('total-grand').textContent = fmtNum(grand);
}

// -------------------------------------------------------------------
// HITUNG TOTAL TONASE & KUBIKASI + PROGRESS BAR
// -------------------------------------------------------------------
function hitungTK() {
    let totTon = 0, totKub = 0;
    document.querySelectorAll('#item-body tr').forEach(tr => {
        const i   = tr.dataset.idx;
        const qty = parseFloat(document.getElementById('qty_' + i)?.value || 0);
        const bg  = parseFloat(document.getElementById('bg_'  + i)?.value || 0);
        const km  = parseFloat(document.getElementById('km_'  + i)?.value || 0);
        totTon += qty * bg / 1000000;
        totKub += qty * km;
    });

    // Label
    document.getElementById('lbl-tonase').textContent   = fmtNum(totTon, 3);
    document.getElementById('lbl-kubikasi').textContent = fmtNum(totKub, 5);

    // Footer tabel
    document.getElementById('total-tonase-tbl').textContent   = fmtNum(totTon, 3)  + ' ton';
    document.getElementById('total-kubikasi-tbl').textContent = fmtNum(totKub, 5)  + ' m³';

    // Progress bar tonase
    const pctTon = Math.min((totTon / BATAS_TONASE) * 100, 100);
    const barTon = document.getElementById('tonase-bar');
    barTon.style.width = pctTon + '%';
    barTon.className   = 'progress-bar ' + (totTon > BATAS_TONASE ? 'bg-danger' : 'bg-success');
    const wTon = document.getElementById('lbl-tonase-warn');
    wTon.classList.toggle('d-none', totTon <= BATAS_TONASE);

    // Progress bar kubikasi
    const pctKub = Math.min((totKub / BATAS_KUBIKASI) * 100, 100);
    const barKub = document.getElementById('kubikasi-bar');
    barKub.style.width = pctKub + '%';
    barKub.className   = 'progress-bar ' + (totKub > BATAS_KUBIKASI ? 'bg-danger' : 'bg-info');
    const wKub = document.getElementById('lbl-kubikasi-warn');
    wKub.classList.toggle('d-none', totKub <= BATAS_KUBIKASI);
}

// -------------------------------------------------------------------
// LOAD & RENDER STOK (AJAX)
// data-ton di tombol = berat_gram, data-kub = kubikasi_m3
// -------------------------------------------------------------------
function loadStock() {
    document.getElementById('stock-body').innerHTML =
        '<tr><td colspan="8" class="text-center">' +
        '<i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>';

    fetch(`${BASE_URL}sales_order/get_stock?gudang_id=${GUDANG_ID}`)
        .then(r => r.json())
        .then(res => { stockCache = res.data || []; renderStock(stockCache); })
        .catch(() => {
            document.getElementById('stock-body').innerHTML =
                '<tr><td colspan="8" class="text-center text-danger">Gagal memuat stok.</td></tr>';
        });
}

function renderStock(data) {
    const q = (document.getElementById('stock-search').value || '').toLowerCase();
    const filtered = q
        ? data.filter(d =>
            (d.kode_barang||'').toLowerCase().includes(q) ||
            (d.nama_barang||'').toLowerCase().includes(q))
        : data;

    if (!filtered.length) {
        document.getElementById('stock-body').innerHTML =
            '<tr><td colspan="8" class="text-center text-muted">' +
            '<i class="fas fa-inbox mr-1"></i> Tidak ada stok</td></tr>';
        return;
    }

    let html = '';
    let lastKd = null;
    filtered.forEach(d => {
        const isNew = d.kode_barang !== lastKd;
        lastKd = d.kode_barang;
        // berat per satuan: gram → kg untuk tampilan lebih mudah dibaca
        const beratKg  = (parseFloat(d.berat_gram||0) / 1000).toFixed(3);
        const kubikStr = parseFloat(d.kubikasi_m3||0).toFixed(6);

        html += `
        <tr class="${isNew ? 'table-light' : ''}">
            <td>
                <small class="text-muted d-block">${d.kode_barang}</small>
                ${isNew ? `<b>${d.nama_barang}</b>` : `<span class="text-muted">↳</span> ${d.nama_barang}`}
            </td>
            <td>
                ${d.exp_date
                    ? `<span class="badge ${isExpiringSoon(d.exp_date)?'badge-warning':'badge-success'}">
                           ${formatTgl(d.exp_date)}</span>`
                    : '-'}
            </td>
            <td>${d.no_lot||'-'}</td>
            <td class="text-right"><b>${fmtNum(d.available_stock)}</b></td>
            <td>${d.satuan||''}</td>
            <td class="text-right"><small>${beratKg} kg</small></td>
            <td class="text-right"><small>${kubikStr} m³</small></td>
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-primary btn-pick-stock"
                    data-kd="${d.kode_barang}"
                    data-nm="${d.nama_barang}"
                    data-exp="${d.exp_date||''}"
                    data-lot="${d.no_lot||''}"
                    data-sat="${d.satuan||''}"
                    data-av="${d.available_stock}"
                    data-ton="${d.berat_gram||0}"
                    data-kub="${d.kubikasi_m3||0}"
                    data-pk="${d.hpp||0}"
                    data-akun="">
                    <i class="fas fa-check"></i> Pilih
                </button>
            </td>
        </tr>`;
    });
    document.getElementById('stock-body').innerHTML = html;
}

// -------------------------------------------------------------------
// EVENT: Buka modal pilih barang
// -------------------------------------------------------------------
document.getElementById('item-body').addEventListener('click', e => {
    if (e.target.closest('.btn-remove')) {
        e.target.closest('tr').remove();
        hitungGrand(); hitungTK();
    }
    if (e.target.closest('.btn-pick')) {
        currentRowIdx = parseInt(e.target.closest('.btn-pick').dataset.idx);
        loadStock();
        $('#modal-stock').modal('show');
    }
});

// -------------------------------------------------------------------
// EVENT: Pilih baris di modal
// Mengisi dropdown expired date dengan SEMUA baris expired barang ini
// -------------------------------------------------------------------
document.getElementById('stock-body').addEventListener('click', e => {
    const btn = e.target.closest('.btn-pick-stock');
    if (!btn) return;
    const i  = currentRowIdx;
    const kd = btn.dataset.kd;

    // Isi field utama
    document.getElementById('kd_'     + i).value       = kd;
    document.getElementById('nm_'     + i).value       = btn.dataset.nm;
    document.getElementById('kdlbl_'  + i).textContent = kd;
    document.getElementById('nmlbl_'  + i).textContent = btn.dataset.nm;
    document.getElementById('sat_'    + i).value       = btn.dataset.sat;
    document.getElementById('satlbl_' + i).value       = btn.dataset.sat;
    document.getElementById('pk_'     + i).value       = btn.dataset.pk;

    // Kumpulkan semua expired date untuk kode barang ini dari stockCache
    const rows = stockCache.filter(s => s.kode_barang === kd);
    const sel  = document.getElementById('exp_' + i);
    sel.innerHTML = '<option value="">-- Pilih Expired Date --</option>';

    rows.forEach(s => {
        const opt    = document.createElement('option');
        opt.value    = s.exp_date || '';
        const tgl    = formatTgl(s.exp_date);
        const lotStr = s.no_lot ? ' | Lot: ' + s.no_lot : '';
        const avStr  = ' (stok: ' + fmtNum(s.available_stock) + ')';
        opt.textContent  = tgl + lotStr + avStr;
        // data-ton = berat_gram, data-kub = kubikasi_m3 (sesuai kolom yg dikembalikan API)
        opt.dataset.ton  = s.berat_gram    || 0;
        opt.dataset.kub  = s.kubikasi_m3   || 0;
        opt.dataset.av   = s.available_stock || 0;
        opt.dataset.lot  = s.no_lot || '';
        if (s.exp_date === btn.dataset.exp) opt.selected = true;
        sel.appendChild(opt);
    });

    // Jika hanya 1 pilihan → pilih otomatis
    if (rows.length === 1) sel.selectedIndex = 1;

    // Trigger change agar berat, kubikasi, avail, lot langsung terisi
    sel.dispatchEvent(new Event('change'));

    hitungBaris(i);
    $('#modal-stock').modal('hide');
});

// Customer name
document.getElementById('customer_id').addEventListener('change', function () {
    document.getElementById('customer_name').value =
        this.options[this.selectedIndex].dataset.nama || '';
});

// Search modal
document.getElementById('stock-search').addEventListener('input', () => renderStock(stockCache));

// -------------------------------------------------------------------
// VALIDASI SUBMIT
// -------------------------------------------------------------------
document.getElementById('form-so').addEventListener('submit', e => {
    const rows = document.querySelectorAll('#item-body tr');
    if (!rows.length) {
        e.preventDefault();
        alert('Minimal 1 item barang harus ditambahkan!');
        return;
    }
    let errs = [];
    rows.forEach(tr => {
        const i     = tr.dataset.idx;
        const qty   = parseFloat(document.getElementById('qty_'   + i)?.value || 0);
        const nm    = document.getElementById('nm_' + i)?.value || '(barang)';
        const avail = parseFloat(document.getElementById('avail_' + i)?.dataset.avail || 0);
        if (qty <= 0)         errs.push(`${nm}: qty harus > 0`);
        else if (qty > avail) errs.push(`${nm}: diminta ${qty}, tersedia ${avail}`);
    });
    if (errs.length) {
        e.preventDefault();
        alert('Peringatan:\n• ' + errs.join('\n• '));
    }
});

document.getElementById('btn-add-row').addEventListener('click', () => tambahBaris({}));

// -------------------------------------------------------------------
// INIT
// -------------------------------------------------------------------
if (EDIT_DETAILS.length) {
    EDIT_DETAILS.forEach(d => tambahBaris(d));
} else {
    tambahBaris({});
}
hitungGrand();
hitungTK();
</script>
</body>