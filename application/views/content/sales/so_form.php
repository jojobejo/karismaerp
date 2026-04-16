<!-- views/content/sales/so_form.php -->
<?php
    $is_edit   = !empty($so);
    $id_so_val = $is_edit ? $so['id_so']    : $no_so;
    $action    = $is_edit
        ? base_url('sales_order/update/' . $so['id_so'])
        : base_url('sales_order/store');
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

                <!-- TOMBOL KEMBALI -->
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
                        <!-- KOLOM KIRI: Informasi SO -->
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

                        <!-- KOLOM KANAN: Tonase & Kubikasi -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white py-2">
                                    <h3 class="card-title"><i class="fas fa-weight mr-1"></i> Batas Tonase & Kubikasi</h3>
                                </div>
                                <div class="card-body">

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-5 col-form-label">Batas Tonase (kg)</label>
                                        <div class="col-sm-7">
                                            <input type="number" step="0.001" name="batas_tonase" id="batas_tonase"
                                                class="form-control"
                                                value="<?= $is_edit ? $so['batas_tonase'] : '' ?>"
                                                placeholder="Opsional">
                                        </div>
                                    </div>

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-5 col-form-label">Batas Kubikasi (m³)</label>
                                        <div class="col-sm-7">
                                            <input type="number" step="0.00001" name="batas_kubikasi" id="batas_kubikasi"
                                                class="form-control"
                                                value="<?= $is_edit ? $so['batas_kubikasi'] : '' ?>"
                                                placeholder="Opsional">
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    <!-- Progress Bars -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small><b>Tonase:</b> <span id="lbl-tonase">0</span> kg</small>
                                            <small class="text-muted" id="lbl-tonase-limit"></small>
                                        </div>
                                        <div class="progress" style="height:10px">
                                            <div class="progress-bar bg-success" id="tonase-bar"
                                                 role="progressbar" style="width:0%"></div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small><b>Kubikasi:</b> <span id="lbl-kubikasi">0</span> m³</small>
                                            <small class="text-muted" id="lbl-kubikasi-limit"></small>
                                        </div>
                                        <div class="progress" style="height:10px">
                                            <div class="progress-bar bg-info" id="kubikasi-bar"
                                                 role="progressbar" style="width:0%"></div>
                                        </div>
                                    </div>

                                    <div id="tk-warning" class="mt-2"></div>

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
                                            <th style="min-width:140px">Expired / No Lot</th>
                                            <th style="width:90px">Qty</th>
                                            <th style="width:70px">Satuan</th>
                                            <th style="width:90px">Stok Avail</th>
                                            <th style="width:130px">Harga Satuan</th>
                                            <th style="width:70px">Pajak %</th>
                                            <th style="width:130px">Subtotal</th>
                                            <th style="width:90px">Tonase/sat</th>
                                            <th style="width:90px">Kubik/sat</th>
                                            <th style="width:40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="item-body">
                                        <!-- diisi JS -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="7" class="text-right font-weight-bold">GRAND TOTAL</td>
                                            <td class="text-right font-weight-bold" id="total-grand">0</td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL SIMPAN -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div id="harga-warning" class="text-danger small"></div>
                        <div>
                            <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary mr-2">
                                <i class="fas fa-times"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="btn-submit">
                                <i class="fas fa-save"></i> Simpan SO
                            </button>
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

<!-- MODAL: Pilih Barang dari Stok -->
<div class="modal fade" id="modal-stock" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title"><i class="fas fa-search mr-1"></i> Pilih Barang</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <input type="text" id="stock-search" class="form-control"
                        placeholder="Cari kode atau nama barang...">
                </div>
                <div style="max-height:420px; overflow-y:auto">
                    <table class="table table-bordered table-sm table-hover mb-0" id="tbl-stock">
                        <thead class="thead-dark sticky-top">
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Exp Date</th>
                                <th>No Lot</th>
                                <th class="text-right">Stok Tersedia</th>
                                <th>Satuan</th>
                                <th class="text-center">Pilih</th>
                            </tr>
                        </thead>
                        <tbody id="stock-body">
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Memuat data stok...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* =====================================================================
   SO FORM SCRIPT — sesuai struktur proyek Karisma ERP
   ===================================================================== */

const BASE_URL  = '<?= base_url() ?>';
const GUDANG_ID = '<?= htmlspecialchars($gudang_id) ?>';

// Data dari server saat edit
<?php if ($is_edit && !empty($details)): ?>
const EDIT_DETAILS = <?= json_encode(array_values($details)) ?>;
<?php else: ?>
const EDIT_DETAILS = [];
<?php endif; ?>

let currentRowIdx = null;
let stockCache    = [];
let rowIdx        = 0;

// =====================================================================
// UTILITIES
// =====================================================================
function fmtNum(n, dec = 2) {
    return parseFloat(n || 0).toLocaleString('id-ID', {
        minimumFractionDigits: dec,
        maximumFractionDigits: dec
    });
}

// =====================================================================
// GENERATE BARIS ITEM
// =====================================================================
function buatBaris(idx, d) {
    d = d || {};
    const kd       = d.kd_barang        || '';
    const nm       = d.nama_barang      || '';
    const exp      = d.expired_date     || '';
    const lot      = d.no_lot           || '';
    const qty      = d.qty              || '';
    const sat      = d.satuan           || '';
    const hrg      = d.hrg_satuan       || '';
    const pk       = d.hrg_pokok        || 0;
    const pajak    = d.pajak            || 0;
    const ton      = d.tonase_satuan    || 0;
    const kub      = d.kubikasi_satuan  || 0;
    const akun     = d.kode_akun        || '';
    const avail    = d.available_stock  || 0;
    const subtotal = (parseFloat(hrg) * parseFloat(qty) || 0) * (1 + parseFloat(pajak) / 100);

    return `
    <tr id="row-${idx}" data-idx="${idx}">
        <td>
            <input type="hidden" name="produk_id[]"       value="${kd}">
            <input type="hidden" name="kd_barang[]"        id="kd_${idx}"  value="${kd}">
            <input type="hidden" name="nama_barang[]"      id="nm_${idx}"  value="${nm}">
            <input type="hidden" name="satuan[]"           id="sat_${idx}" value="${sat}">
            <input type="hidden" name="hrg_pokok[]"        id="pk_${idx}"  value="${pk}">
            <input type="hidden" name="tonase_satuan[]"    id="ton_${idx}" value="${ton}">
            <input type="hidden" name="kubikasi_satuan[]"  id="kub_${idx}" value="${kub}">
            <input type="hidden" name="kode_akun[]"        value="${akun}">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <small class="text-muted" id="kdlbl_${idx}">${kd || '—'}</small><br>
                    <span id="nmlbl_${idx}">${nm || '—'}</span>
                </div>
                <button type="button" class="btn btn-xs btn-outline-primary ml-1 btn-pick"
                    data-idx="${idx}" title="Pilih Barang">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <small class="text-muted">Tersedia: <b id="avail_${idx}">${fmtNum(avail)}</b></small>
        </td>
        <td>
            <input type="date" name="expired_date[]" id="exp_${idx}"
                class="form-control form-control-sm mb-1" value="${exp}" required>
            <input type="text" name="no_lot[]" id="lot_${idx}"
                class="form-control form-control-sm" placeholder="No Lot" value="${lot}">
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
            <span id="availnum_${idx}">${fmtNum(avail)}</span>
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
        <td>
            <input type="number" step="0.0001" name="tonase_satuan[]" id="toninp_${idx}"
                class="form-control form-control-sm" value="${ton}">
        </td>
        <td>
            <input type="number" step="0.000001" name="kubikasi_satuan[]" id="kubinp_${idx}"
                class="form-control form-control-sm" value="${kub}">
        </td>
        <td class="text-center align-middle">
            <button type="button" class="btn btn-xs btn-danger btn-remove" data-idx="${idx}">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;
}

// =====================================================================
// TAMBAH BARIS
// =====================================================================
function tambahBaris(d) {
    document.getElementById('item-body').insertAdjacentHTML('beforeend', buatBaris(rowIdx, d || {}));
    bindBaris(rowIdx);
    rowIdx++;
}

function bindBaris(idx) {
    const fields = ['hrg_', 'qty_', 'pjk_', 'toninp_', 'kubinp_'];
    fields.forEach(f => {
        const el = document.getElementById(f + idx);
        if (el) el.addEventListener('input', () => hitungBaris(idx));
    });
}

// =====================================================================
// HITUNG SUBTOTAL
// =====================================================================
function hitungBaris(idx) {
    const hrg  = parseFloat(document.getElementById('hrg_'    + idx)?.value || 0);
    const qty  = parseFloat(document.getElementById('qty_'    + idx)?.value || 0);
    const pjk  = parseFloat(document.getElementById('pjk_'    + idx)?.value || 0);
    const pk   = parseFloat(document.getElementById('pk_'     + idx)?.value || 0);
    const sub  = hrg * qty;
    const tot  = sub + (sub * pjk / 100);

    const elSub = document.getElementById('sub_' + idx);
    if (elSub) elSub.textContent = fmtNum(tot);

    // Peringatan harga di bawah HPP
    const warnEl = document.getElementById('hrgwarn_' + idx);
    if (warnEl) {
        warnEl.innerHTML = (hrg > 0 && hrg < pk)
            ? '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Di bawah HPP</span>'
            : '';
    }

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

// =====================================================================
// HITUNG TONASE & KUBIKASI (progress bar)
// =====================================================================
function hitungTK() {
    let totTon = 0, totKub = 0;
    document.querySelectorAll('#item-body tr').forEach(tr => {
        const i = tr.dataset.idx;
        const q = parseFloat(document.getElementById('qty_'    + i)?.value || 0);
        const t = parseFloat(document.getElementById('toninp_' + i)?.value || 0);
        const k = parseFloat(document.getElementById('kubinp_' + i)?.value || 0);
        totTon += t * q;
        totKub += k * q;
    });

    const bTon = parseFloat(document.getElementById('batas_tonase')?.value   || 0);
    const bKub = parseFloat(document.getElementById('batas_kubikasi')?.value || 0);

    document.getElementById('lbl-tonase').textContent   = totTon.toFixed(3);
    document.getElementById('lbl-kubikasi').textContent = totKub.toFixed(5);

    // Progress bar tonase
    if (bTon > 0) {
        const pct   = Math.min((totTon / bTon) * 100, 100);
        const bar   = document.getElementById('tonase-bar');
        bar.style.width = pct + '%';
        bar.className   = 'progress-bar ' + (totTon > bTon ? 'bg-danger' : 'bg-success');
        document.getElementById('lbl-tonase-limit').textContent = 'Batas: ' + fmtNum(bTon, 3) + ' kg';
    }

    // Progress bar kubikasi
    if (bKub > 0) {
        const pct   = Math.min((totKub / bKub) * 100, 100);
        const bar   = document.getElementById('kubikasi-bar');
        bar.style.width = pct + '%';
        bar.className   = 'progress-bar ' + (totKub > bKub ? 'bg-danger' : 'bg-info');
        document.getElementById('lbl-kubikasi-limit').textContent = 'Batas: ' + totKub.toFixed(5) + ' m³';
    }

    // Pesan warning
    let msgs = [];
    if (bTon > 0 && bKub > 0) {
        const oT = totTon > bTon, oK = totKub > bKub;
        if  (oT && !oK) msgs.push('<i class="fas fa-exclamation-triangle text-warning"></i> Tonase melebihi batas, kubikasi masih aman.');
        if  (oK && !oT) msgs.push('<i class="fas fa-exclamation-triangle text-warning"></i> Kubikasi melebihi batas, tonase masih aman.');
        if  (oT && oK)  msgs.push('<i class="fas fa-times-circle text-danger"></i> Tonase DAN kubikasi melebihi batas!');
    }
    document.getElementById('tk-warning').innerHTML = msgs.map(m =>
        `<div class="callout callout-warning py-1 mb-1"><small>${m}</small></div>`
    ).join('');
}

// =====================================================================
// LOAD DATA STOK (AJAX)
// =====================================================================
function loadStock() {
    document.getElementById('stock-body').innerHTML =
        '<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat...</td></tr>';

    fetch(`${BASE_URL}sales_order/get_stock?gudang_id=${GUDANG_ID}`)
        .then(r => r.json())
        .then(res => {
            stockCache = res.data || [];
            renderStock(stockCache);
        })
        .catch(() => {
            document.getElementById('stock-body').innerHTML =
                '<tr><td colspan="7" class="text-center text-danger">Gagal memuat data stok.</td></tr>';
        });
}

function renderStock(data) {
    const q = (document.getElementById('stock-search').value || '').toLowerCase();
    const filtered = q
        ? data.filter(d =>
            (d.kode_barang || '').toLowerCase().includes(q) ||
            (d.nama_barang || '').toLowerCase().includes(q))
        : data;

    if (!filtered.length) {
        document.getElementById('stock-body').innerHTML =
            '<tr><td colspan="7" class="text-center text-muted"><i class="fas fa-inbox mr-1"></i> Stok kosong / tidak ditemukan</td></tr>';
        return;
    }

    document.getElementById('stock-body').innerHTML = filtered.map(d => `
        <tr>
            <td><small>${d.kode_barang || ''}</small></td>
            <td>${d.nama_barang || ''}</td>
            <td>${d.exp_date || '-'}</td>
            <td>${d.no_lot   || '-'}</td>
            <td class="text-right"><b>${fmtNum(d.available_stock)}</b></td>
            <td>${d.satuan   || ''}</td>
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-primary btn-pick-stock"
                    data-kd="${d.kode_barang}"
                    data-nm="${d.nama_barang}"
                    data-exp="${d.exp_date  || ''}"
                    data-lot="${d.no_lot    || ''}"
                    data-sat="${d.satuan    || ''}"
                    data-av="${d.available_stock}"
                    data-ton="${d.tonase_satuan    || 0}"
                    data-kub="${d.kubikasi_satuan  || 0}"
                    data-pk="${d.hrg_pokok || 0}"
                    data-akun="${d.kode_akun || ''}">
                    <i class="fas fa-check"></i> Pilih
                </button>
            </td>
        </tr>`).join('');
}

// =====================================================================
// EVENT DELEGATION
// =====================================================================
document.getElementById('item-body').addEventListener('click', e => {
    // Tombol hapus baris
    if (e.target.closest('.btn-remove')) {
        e.target.closest('tr').remove();
        hitungGrand();
        hitungTK();
    }
    // Tombol buka modal pilih barang
    if (e.target.closest('.btn-pick')) {
        currentRowIdx = parseInt(e.target.closest('.btn-pick').dataset.idx);
        loadStock();
        $('#modal-stock').modal('show');
    }
});

// Pilih barang dari modal → isi baris
document.getElementById('stock-body').addEventListener('click', e => {
    const btn = e.target.closest('.btn-pick-stock');
    if (!btn) return;
    const i = currentRowIdx;

    // Isi hidden + display fields
    document.getElementById('kd_'      + i).value       = btn.dataset.kd;
    document.getElementById('nm_'      + i).value       = btn.dataset.nm;
    document.getElementById('kdlbl_'   + i).textContent = btn.dataset.kd;
    document.getElementById('nmlbl_'   + i).textContent = btn.dataset.nm;
    document.getElementById('exp_'     + i).value       = btn.dataset.exp;
    document.getElementById('lot_'     + i).value       = btn.dataset.lot;
    document.getElementById('sat_'     + i).value       = btn.dataset.sat;
    document.getElementById('satlbl_'  + i).value       = btn.dataset.sat;
    document.getElementById('pk_'      + i).value       = btn.dataset.pk;
    document.getElementById('toninp_'  + i).value       = btn.dataset.ton;
    document.getElementById('kubinp_'  + i).value       = btn.dataset.kub;
    document.getElementById('avail_'   + i).textContent = fmtNum(btn.dataset.av);
    document.getElementById('availnum_'+ i).textContent = fmtNum(btn.dataset.av);
    document.getElementById('ton_'     + i).value       = btn.dataset.ton;
    document.getElementById('kub_'     + i).value       = btn.dataset.kub;

    hitungBaris(i);
    $('#modal-stock').modal('hide');
});

// Customer change → simpan nama ke hidden input
document.getElementById('customer_id').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    document.getElementById('customer_name').value = opt.dataset.nama || '';
});

// Batas tonase/kubikasi berubah → update progress bar
['batas_tonase','batas_kubikasi'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', hitungTK);
});

// Search di modal
document.getElementById('stock-search').addEventListener('input', function () {
    renderStock(stockCache);
});

// =====================================================================
// VALIDASI SEBELUM SUBMIT
// =====================================================================
document.getElementById('form-so').addEventListener('submit', e => {
    const rows = document.querySelectorAll('#item-body tr');
    if (!rows.length) {
        e.preventDefault();
        toastr.error('Minimal 1 item barang harus ditambahkan!');
        return;
    }

    let stokErr = [];
    rows.forEach(tr => {
        const i     = tr.dataset.idx;
        const qty   = parseFloat(document.getElementById('qty_'   + i)?.value || 0);
        const avail = parseFloat(document.getElementById('avail_' + i)?.textContent?.replace(/\./g,'').replace(',','.') || 0);
        const nm    = document.getElementById('nm_' + i)?.value || '';
        if (qty > avail) stokErr.push(`${nm}: diminta ${qty}, tersedia ${avail}`);
    });

    if (stokErr.length) {
        e.preventDefault();
        toastr.error('Stok tidak mencukupi:\n' + stokErr.join('\n'));
    }
});

// Tombol tambah baris
document.getElementById('btn-add-row').addEventListener('click', () => tambahBaris({}));

// =====================================================================
// INIT
// =====================================================================
if (EDIT_DETAILS.length) {
    EDIT_DETAILS.forEach(d => tambahBaris(d));
} else {
    tambahBaris({});   // default 1 baris kosong
}
hitungGrand();
hitungTK();
</script>
</body>