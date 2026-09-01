<!-- application/views/content/sales/so_loby_form.php -->
<!-- Form Sales Order Loby (Zahir ERP Style) -->
<?php
    $is_edit   = !empty($so);
    $id_so_val = $is_edit ? ($so['no_so'] ?? '') : ($no_so ?? '');
    $action    = $is_edit
        ? base_url('sales_order_loby/update/' . $so['id_so'])
        : base_url('sales_order_loby/store');

    $gid_aktif = $is_edit ? ($so['gudang_id'] ?? '') : ($gudang_id ?? '');

    function escAttr($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
    function escJs($v)   {
        return str_replace(['\\','\'',"\r","\n","\t"], ['\\\\',"\\'", '','',''], (string)$v);
    }
?>

<style>
    :root {
        --zahir-blue: #127fad;
        --zahir-dark-blue: #0f6c94;
        --zahir-light-bg: #f0f4f7;
        --zahir-card-border: #d1dbe3;
        --zahir-text: #1e293b;
    }

    body.hold-transition {
        background-color: var(--zahir-light-bg);
    }

    .pb-container {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--zahir-text);
        padding: 20px;
    }

    .zahir-card {
        background: #fff;
        border: 1px solid var(--zahir-card-border);
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 120px);
    }

    /* Header Title */
    .form-header-title {
        background: linear-gradient(135deg, var(--zahir-blue) 0%, #3197c5 100%);
        color: #fff;
        padding: 16px 24px;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 4px 15px rgba(18, 127, 173, 0.15);
    }

    .form-header-title h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Kolom Header Form Input */
    .form-header-section {
        padding: 16px 22px;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f5;
    }

    .form-row-zahir {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .form-row-zahir:last-child {
        margin-bottom: 0;
    }

    .form-row-zahir label {
        width: 120px;
        font-size: 13px;
        font-weight: 500;
        color: #334155;
        margin: 0;
    }

    .form-control-zahir {
        font-size: 13px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 5px 10px;
        height: 32px;
        outline: none;
        background: #fff;
    }

    .form-control-zahir:focus {
        border-color: var(--zahir-blue);
        box-shadow: 0 0 0 2px rgba(18, 127, 173, 0.15);
    }

    .ref-input {
        background-color: #f1f5f9 !important;
        font-weight: 600;
        color: var(--zahir-blue);
    }

    .date-input {
        width: 160px;
    }

    .btn-lookup-small {
        background: #e2e8f0;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        height: 32px;
        width: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #475569;
        transition: background 0.15s;
    }

    .btn-lookup-small:hover {
        background: #cbd5e1;
        color: var(--zahir-blue);
    }

    /* Area Grid / Tabel Transaksi */
    .table-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow-x: auto;
        overflow-y: auto;
        min-height: 360px;
        background: #fff;
    }

    .grid-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 0;
    }

    .grid-table thead th {
        background-color: var(--zahir-blue) !important;
        color: #fff !important;
        font-weight: 500;
        padding: 8px 12px;
        font-size: 13px;
        letter-spacing: 0.3px;
        border-right: 1px solid rgba(255,255,255,0.2);
        border-bottom: none;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .grid-table thead th:last-child {
        border-right: none;
    }

    .grid-table tbody td {
        padding: 2px 4px;
        font-size: 13px;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        vertical-align: middle;
        background: #fff;
    }

    .grid-table tbody td:last-child {
        border-right: none;
    }

    .grid-table tbody tr:hover td {
        background: #f8fafc;
    }

    .grid-table tbody tr.active-row td {
        background: #eff6ff;
    }

    /* Input di dalam sel tabel */
    .cell-input {
        width: 100%;
        border: 1px solid transparent;
        padding: 5px 8px;
        font-size: 13px;
        background: transparent;
        outline: none;
        box-sizing: border-box;
        height: 30px;
    }

    .cell-input:focus {
        border-color: var(--zahir-blue);
        background: #fff;
        border-radius: 3px;
        box-shadow: 0 0 0 1px var(--zahir-blue);
    }

    .cell-lookup-group {
        display: flex;
        align-items: center;
        width: 100%;
    }

    .cell-lookup-group .cell-input {
        flex: 1;
    }

    .btn-cell-lookup {
        background: none;
        border: none;
        color: #64748b;
        padding: 4px 6px;
        cursor: pointer;
        font-size: 12px;
    }

    .btn-cell-lookup:hover {
        color: var(--zahir-blue);
    }

    .btn-del-row {
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        padding: 4px;
        font-size: 13px;
        opacity: 0.7;
    }

    .btn-del-row:hover {
        opacity: 1;
        color: #b91c1c;
    }

    /* Bottom Bar Form */
    .form-bottom-bar {
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f8fafc;
        border-top: 1px solid #eef2f5;
        margin-top: auto;
        position: sticky;
        bottom: 0;
        z-index: 10;
    }

    .form-bottom-left {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 13px;
        color: #475569;
    }

    .form-bottom-right {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Tombol Zahir */
    .btn-zahir {
        font-size: 13px;
        font-weight: 500;
        padding: 7px 18px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 80px;
    }

    .btn-zahir:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    .btn-zahir-primary { background: var(--zahir-blue); color: #fff; }
    .btn-zahir-primary:hover:not(:disabled) { background: var(--zahir-dark-blue); color: #fff; }
    .btn-zahir-secondary { background: #6c757d; color: #fff; }
    .btn-zahir-secondary:hover:not(:disabled) { background: #5a6268; color: #fff; }
    .btn-zahir-teal { background: #17a2b8; color: #fff; }
    .btn-zahir-teal:hover:not(:disabled) { background: #138496; color: #fff; }

    /* Zahir Style Lookup Modal */
    .zahir-lookup-modal .modal-dialog {
        width: 860px;
        max-width: calc(100vw - 40px);
        margin: 50px auto 30px auto;
    }

    .zahir-lookup-modal .modal-content {
        border-radius: 3px;
        border: 1px solid #c8d1d9;
        box-shadow: 0 8px 30px rgba(0,0,0,0.35);
        background: #fff;
        overflow: hidden;
    }

    .zahir-lookup-modal .modal-header {
        background: #fff;
        border-bottom: none;
        padding: 16px 20px 10px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .zahir-lookup-modal .modal-title {
        color: #222;
        font-size: 19px;
        font-weight: 600;
        margin: 0;
        letter-spacing: -0.2px;
    }

    .zahir-modal-search {
        position: relative;
        width: 260px;
    }

    .zahir-modal-search input {
        width: 100%;
        height: 32px;
        padding: 4px 28px 4px 10px;
        font-size: 13px;
        border: 1px solid #d4d8db;
        border-radius: 3px;
        background: #fdfdfd;
        outline: none;
        transition: border 0.2s;
    }

    .zahir-modal-search input:focus {
        border-color: #0f769f;
        background: #fff;
    }

    .zahir-modal-search i {
        position: absolute;
        right: 8px;
        top: 9px;
        font-size: 13px;
        color: #a0aab2;
        pointer-events: none;
    }

    .zahir-lookup-scroll {
        height: 380px;
        min-height: 380px;
        max-height: 380px;
        overflow-y: scroll;
        border-top: 1px solid #d8dee4;
        border-bottom: 1px solid #d8dee4;
        margin: 0 20px;
        background: #fff;
    }

    .zahir-lookup-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin: 0;
    }

    .zahir-lookup-table thead th {
        background-color: #0f769f !important;
        color: #fff !important;
        font-weight: 500;
        font-size: 13px;
        padding: 8px 12px;
        border: none;
        position: sticky;
        top: 0;
        z-index: 2;
        text-align: left;
    }

    .zahir-lookup-table thead th.text-right {
        text-align: right;
    }

    .zahir-lookup-table thead th.text-center {
        text-align: center;
    }

    .zahir-lookup-table tbody tr {
        cursor: pointer;
        user-select: none;
        transition: background 0.1s;
    }

    .zahir-lookup-table tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }

    .zahir-lookup-table tbody tr:nth-child(even) {
        background-color: #f0f3f6;
    }

    .zahir-lookup-table tbody tr:hover {
        background-color: #e2f2fc;
    }

    .zahir-lookup-table tbody tr.selected {
        background-color: #62bbed !important;
        color: #fff !important;
    }

    .zahir-lookup-table tbody tr.selected td {
        color: #fff !important;
        font-weight: 500;
    }

    .zahir-lookup-table tbody td {
        padding: 8px 12px;
        font-size: 13px;
        color: #333;
        border: none;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .zahir-lookup-footer {
        padding: 14px 20px 16px 20px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        background: #fff;
    }

    .btn-zahir-action {
        background-color: #0f769f;
        color: #fff;
        border: none;
        border-radius: 3px;
        padding: 6px 18px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        min-width: 75px;
        transition: background 0.2s;
    }

    .btn-zahir-action:hover {
        background-color: #0b5a7a;
        color: #fff;
    }

    .btn-zahir-action:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <!-- Navbar & Sidebar -->
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="pb-container">

            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-3">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('warning')): ?>
                <div class="alert alert-warning alert-dismissible fade show mb-3">
                    <?= $this->session->flashdata('warning') ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <form action="<?= $action ?>" method="post" id="form-so" class="zahir-card">
                <!-- Header Title -->
                <div class="form-header-title">
                    <h2><i class="fas fa-store"></i> <?= $is_edit ? 'Edit Sales Order Loby' : 'Sales Order Loby' ?></h2>
                </div>

                <!-- Form Header Fields -->
                <div class="form-header-section">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-md-6">
                            <div class="form-row-zahir">
                                <label>No. SO :</label>
                                <input type="text" name="no_so" id="no_so" class="form-control-zahir ref-input" style="width:200px"
                                       value="<?= escAttr($id_so_val) ?>" readonly required />
                            </div>
                            <div class="form-row-zahir">
                                <label>Tanggal :</label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control-zahir date-input"
                                       value="<?= $is_edit ? escAttr($so['tanggal_transaksi']) : date('Y-m-d') ?>" required />
                            </div>
                            <div class="form-row-zahir">
                                <label>Customer <span style="color:#d9534f;font-weight:bold;">*</span> :</label>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <input type="text" id="customer_display" class="form-control-zahir" style="width:280px;cursor:pointer;background:#fff;font-weight:600;"
                                           placeholder="-- Pilih Customer --"
                                           value="<?= $is_edit ? escAttr($so['customer_name'] ?: ($so['nama_customer'] ?? '')) : '' ?>"
                                           readonly onclick="openLookupCustomer()" required />
                                    <button type="button" class="btn-lookup-small" onclick="openLookupCustomer()" title="Pilih Customer">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="customer_id" id="customer_id"
                                       value="<?= $is_edit ? escAttr($so['kd_customer'] ?? '') : '' ?>" required />
                                <input type="hidden" name="customer_name" id="customer_name"
                                       value="<?= $is_edit ? escAttr($so['customer_name'] ?: ($so['nama_customer'] ?? '')) : '' ?>" />
                                <input type="hidden" name="customer_plafon" id="customer_plafon" value="" />
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-md-6">
                            <div class="form-row-zahir">
                                <label>Gudang <span style="color:#d9534f;font-weight:bold;">*</span> :</label>
                                <select name="gudang_id" id="gudang_id_input" class="form-control-zahir" style="width:260px;" required <?= $is_edit ? 'disabled' : '' ?>>
                                    <option value="">-- Pilih Gudang --</option>
                                    <?php foreach ($gudang_list as $g): ?>
                                        <option value="<?= escAttr($g['id_gudang']) ?>"
                                            <?= ((string)$gid_aktif === (string)$g['id_gudang']) ? 'selected' : '' ?>>
                                            <?= escAttr($g['nama_gudang']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($is_edit): ?>
                                    <input type="hidden" name="gudang_id" value="<?= escAttr($gid_aktif) ?>" />
                                <?php endif; ?>
                            </div>
                            <div class="form-row-zahir">
                                <label>Cara Bayar :</label>
                                <input type="text" class="form-control-zahir font-weight-bold" style="width:160px;background:#e9ecef;color:#1e293b;"
                                       value="Cash" readonly />
                                <input type="hidden" name="cara_pembayaran" id="cara_pembayaran" value="cash" />
                            </div>
                            <div class="form-row-zahir">
                                <label>Keterangan :</label>
                                <input type="text" name="catatan" id="catatan" class="form-control-zahir" style="width:340px;"
                                       placeholder="Catatan transaksi Loby (opsional)..."
                                       value="<?= $is_edit ? htmlspecialchars($so['catatan'] ?? '') : '' ?>" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid Detail Transaksi -->
                <div class="table-container">
                    <table class="grid-table" id="gridTable">
                        <thead>
                            <tr>
                                <th style="width: 140px;">Kode</th>
                                <th style="width: auto;">Nama Barang</th>
                                <th style="width: 170px;">Expired / Lot</th>
                                <th style="width: 80px; text-align:right">Qty Box</th>
                                <th style="width: 80px; text-align:right">+Ecer</th>
                                <th style="width: 75px; text-align:right">Total</th>
                                <th style="width: 65px; text-align:center">Satuan</th>
                                <th style="width: 130px; text-align:right">Harga/Pcs</th>
                                <th style="width: 65px; text-align:center">Disc%</th>
                                <th style="width: 135px; text-align:right">Subtotal</th>
                                <th style="width: 40px; text-align:center">#</th>
                            </tr>
                        </thead>
                        <tbody id="gridBody">
                            <!-- Diisi baris secara dinamis via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Form Bottom Actions -->
                <div class="form-bottom-bar">
                    <div class="form-bottom-left">
                        <button type="button" class="btn-zahir btn-zahir-secondary" onclick="delActiveOrLastRow()" style="min-width: 95px;">
                            <i class="fas fa-trash-alt mr-1"></i> Hapus Baris
                        </button>
                        <div style="font-size: 14px; font-weight: 500; color: #334155;">
                            Grand Total: <strong id="grandTotalText" style="color: var(--zahir-blue); font-size: 17px; font-weight: 700; margin-left: 6px;">Rp 0</strong>
                        </div>
                    </div>
                    <div class="form-bottom-right">
                        <button type="button" class="btn-zahir btn-zahir-teal" onclick="addNewRow()">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris
                        </button>
                        <a href="<?= base_url('sales_order_loby') ?>" class="btn-zahir btn-zahir-secondary" style="text-decoration:none;">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
                        <button type="submit" class="btn-zahir btn-zahir-primary" id="btn-submit">
                            <i class="fas fa-save mr-1"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Rekam' ?>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <?php $this->load->view('partial/main/footer') ?>
</div>

<!-- Modal Lookup Barang (Data Persediaan) Sesuai Desain Zahir -->
<div class="modal fade zahir-lookup-modal" id="modalBarang" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-boxes mr-1 text-primary"></i> Data Persediaan</h5>
                <div class="zahir-modal-search">
                    <input type="text" id="searchBarang" placeholder="Cari kode atau nama barang..." />
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="zahir-lookup-scroll">
                <table class="zahir-lookup-table" id="tableLookupBarang">
                    <colgroup>
                        <col style="width: 18%;">
                        <col style="width: 32%;">
                        <col style="width: 15%;">
                        <col style="width: 12%;">
                        <col style="width: 11%;">
                        <col style="width: 12%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Deskripsi</th>
                            <th>Exp Date</th>
                            <th>Lot</th>
                            <th class="text-right">Stok Box</th>
                            <th class="text-right">Total Pcs</th>
                        </tr>
                    </thead>
                    <tbody id="bodyLookupBarang">
                        <tr><td colspan="6" style="height: 320px; text-align: center; vertical-align: middle; color: #64748b;"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data stok...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="zahir-lookup-footer">
                <button type="button" class="btn-zahir-action" style="background:#6c757d;" data-dismiss="modal">Batal</button>
                <button type="button" class="btn-zahir-action" id="btnPilihBarang" disabled onclick="pilihBarang()">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lookup Customer (Data Customer) Sesuai Desain Zahir -->
<div class="modal fade zahir-lookup-modal" id="modalCustomer" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users mr-1 text-primary"></i> Data Customer</h5>
                <div class="zahir-modal-search">
                    <input type="text" id="searchCustomer" placeholder="Cari nama, kios, atau kode..." />
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="zahir-lookup-scroll">
                <table class="zahir-lookup-table" id="tableLookupCustomer">
                    <colgroup>
                        <col style="width: 20%;">
                        <col style="width: 40%;">
                        <col style="width: 22%;">
                        <col style="width: 18%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Customer</th>
                            <th>Kios</th>
                            <th class="text-right">Plafon</th>
                        </tr>
                    </thead>
                    <tbody id="bodyLookupCustomer">
                        <!-- Diisi via JS -->
                    </tbody>
                </table>
            </div>
            <div class="zahir-lookup-footer">
                <button type="button" class="btn-zahir-action" style="background:#6c757d;" data-dismiss="modal">Batal</button>
                <button type="button" class="btn-zahir-action" id="btnPilihCustomer" disabled onclick="pilihCustomer()">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
var BASE_URL = '<?= base_url() ?>';
var SO_ID    = <?= $is_edit ? (int)$so['id_so'] : 0 ?>;

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

var stockCache           = [];
var stockLoaded          = false;
var activeRowIdx         = null;
var $activeFormTr        = null;
var selectedLookupBarang = null;
var selectedLookupCust   = null;

function escHtml(str) {
    return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function escAttr(str) {
    return String(str || '').replace(/"/g, '&quot;');
}

function fmtNum(n, dec) {
    if (dec === undefined) dec = 0;
    return (parseFloat(n)||0).toLocaleString('id-ID', {minimumFractionDigits:dec, maximumFractionDigits:dec});
}

function parseHargaInput(value) {
    var cleaned = String(value || '').replace(/[^\d]/g, '');
    return cleaned === '' ? 0 : parseFloat(cleaned);
}

function formatHargaInput(value) {
    var number = parseHargaInput(value);
    return number > 0 ? number.toLocaleString('id-ID', {maximumFractionDigits:0}) : '';
}

function formatTgl(ymd) {
    if (!ymd) return '-';
    var p = String(ymd).split('-');
    return p.length === 3 ? p[2]+'/'+p[1]+'/'+p[0] : ymd;
}

/* =========================================================================
   GRID ROWS & CALCULATION
   ========================================================================= */

function getRowElem(elemOrIdx) {
    if (typeof elemOrIdx === 'number') {
        return $('#gridBody tr').eq(elemOrIdx);
    }
    return $(elemOrIdx).closest('tr');
}

function setActiveRow(tr) {
    $('#gridBody tr').removeClass('active-row');
    $(tr).addClass('active-row');
    activeRowIdx = $(tr).index();
    $activeFormTr = $(tr);
}

function addNewRow(d) {
    d = d || {};
    var idx = $('#gridBody tr').length;
    var kd      = d.kd_barang    || '';
    var nm      = d.nama_barang  || '';
    var exp     = d.expired_date || '';
    var lot     = d.no_lot       || '';
    var sat     = d.satuan       || 'PCS';
    var hrg     = parseFloat(d.hrg_satuan  || 0);
    var pk      = parseFloat(d.hrg_pokok   || 0);
    var disc    = parseFloat(d.disc        || 0);
    var beratG  = parseFloat(d.berat_gram  || 0);
    var kubikM  = parseFloat(d.kubikasi_m3 || 0);
    var isi     = parseInt(d.isi_per_box   || 1); if (isi < 1) isi = 1;

    var qtyKecilDB = parseFloat(d.qty        || 0);
    var qtyBox     = parseFloat(d.qty_box    || 0);
    var qtySat     = parseFloat(d.qty_satuan || 0);
    if (!d.qty_box && qtyKecilDB > 0) {
        qtyBox = Math.floor(qtyKecilDB / isi);
        qtySat = qtyKecilDB % isi;
    }

    var av      = parseFloat(d.available_stock || 0);
    var qTotal  = (qtyBox * isi) + qtySat;
    var subB    = hrg * qTotal;
    var sub     = subB * (1 - disc / 100);

    var expOption = exp
        ? '<option value="'+escAttr(exp)+'" data-lot="'+escAttr(lot)+'" data-av="'+av
          +'" data-ton="'+beratG+'" data-kub="'+kubikM+'" data-isi="'+isi+'" selected>'
          + escHtml(formatTgl(exp)) + (lot ? ' | Lot: '+escHtml(lot) : '')
          + '</option>'
        : '<option value="">-- Pilih --</option>';

    var html = '<tr data-row-idx="' + idx + '" onclick="setActiveRow(this)">';

    // Kode
    html += '<td><div class="cell-lookup-group">';
    html += '<input type="text" name="kd_barang[]" class="cell-input input-kd-barang font-weight-bold text-dark" value="' + escAttr(kd) + '" placeholder="Pilih..." readonly onclick="openLookupBarang(this)" />';
    html += '<input type="hidden" name="produk_id[]" value="' + escAttr(kd) + '" />';
    html += '<input type="hidden" name="hrg_pokok[]" class="input-hrg-pokok" value="' + pk + '" />';
    html += '<input type="hidden" name="berat_gram[]" class="input-berat" value="' + beratG + '" />';
    html += '<input type="hidden" name="kubikasi_m3[]" class="input-kubikasi" value="' + kubikM + '" />';
    html += '<input type="hidden" name="isi_per_box[]" class="input-isi-box" value="' + isi + '" />';
    html += '<input type="hidden" name="pajak[]" value="0" />';
    html += '<button type="button" class="btn-cell-lookup" onclick="openLookupBarang(this)" title="Pilih Barang"><i class="fas fa-ellipsis-h"></i></button>';
    html += '</div></td>';

    // Nama Barang
    html += '<td><input type="text" name="nama_barang[]" class="cell-input input-nm-barang" value="' + escAttr(nm) + '" placeholder="Deskripsi barang" readonly onclick="openLookupBarang(this)" /></td>';

    // Expired / Lot Dropdown
    html += '<td>';
    html += '<select name="expired_date[]" class="cell-input input-exp-select" style="font-size:12px;" onchange="onExpiredChanged(this)">' + expOption + '</select>';
    html += '<input type="hidden" name="no_lot[]" class="input-no-lot" value="' + escAttr(lot) + '" />';
    html += '</td>';

    // Qty Box
    var qtyBoxVal = qtyBox > 0 ? qtyBox : '';
    html += '<td><input type="number" step="1" min="0" name="qty_box[]" class="cell-input input-qty-box" style="text-align:right;" value="' + qtyBoxVal + '" placeholder="0" onfocus="this.select()" oninput="calcRow(this)" onchange="calcRow(this)" /></td>';

    // Eceran
    var qtySatVal = qtySat > 0 ? qtySat : '';
    html += '<td><input type="number" step="1" min="0" name="qty_satuan[]" class="cell-input input-qty-ecer" style="text-align:right;" value="' + qtySatVal + '" placeholder="0" onfocus="this.select()" oninput="calcRow(this)" onchange="calcRow(this)" /></td>';

    // Total Pcs
    html += '<td style="text-align:right;padding-right:8px;font-weight:600;" class="cell-total-pcs">' + fmtNum(qTotal,0) + '</td>';

    // Satuan
    html += '<td style="text-align:center;"><input type="text" name="satuan[]" class="cell-input input-satuan" style="text-align:center;" value="' + escAttr(sat) + '" readonly /></td>';

    // Harga Satuan
    html += '<td><input type="text" inputmode="numeric" autocomplete="off" name="hrg_satuan[]" class="cell-input input-hrg-satuan" style="text-align:right;" value="' + (hrg > 0 ? formatHargaInput(hrg) : '') + '" placeholder="0" onfocus="this.select()" oninput="onHargaInput(this)" /></td>';

    // Disc %
    var discVal = disc > 0 ? disc : '';
    html += '<td><input type="number" step="0.01" min="0" max="100" name="disc[]" class="cell-input input-disc" style="text-align:center;" value="' + discVal + '" placeholder="0" onfocus="this.select()" oninput="calcRow(this)" /></td>';

    // Subtotal
    html += '<td style="text-align:right;padding-right:8px;font-weight:600;" class="cell-subtotal">' + fmtNum(sub, 0) + '</td>';

    // Tombol Hapus
    html += '<td style="text-align:center;"><button type="button" class="btn-del-row" onclick="delRow(this)" title="Hapus"><i class="fas fa-trash-alt"></i></button></td>';

    html += '</tr>';

    $('#gridBody').append(html);
    var $tr = $('#gridBody tr').last();
    setActiveRow($tr);
    calcGrandTotal();
}

function delRow(btn) {
    $(btn).closest('tr').remove();
    if ($('#gridBody tr').length === 0) {
        addNewRow();
    }
    calcGrandTotal();
}

function delActiveOrLastRow() {
    if ($('#gridBody tr.active-row').length > 0) {
        $('#gridBody tr.active-row').remove();
    } else if ($('#gridBody tr').length > 1) {
        $('#gridBody tr').last().remove();
    }
    if ($('#gridBody tr').length === 0) {
        addNewRow();
    }
    calcGrandTotal();
}

function onHargaInput(elem) {
    elem.value = formatHargaInput(elem.value);
    calcRow(elem);
}

function onExpiredChanged(selectElem) {
    var $tr = getRowElem(selectElem);
    var opt = selectElem.options[selectElem.selectedIndex];
    if (!opt || selectElem.selectedIndex === 0) return;

    var lot = opt.dataset.lot || '';
    var isi = parseInt(opt.dataset.isi || 1); if (isi < 1) isi = 1;
    var ton = parseFloat(opt.dataset.ton || 0);
    var kub = parseFloat(opt.dataset.kub || 0);

    $tr.find('.input-no-lot').val(lot);
    $tr.find('.input-isi-box').val(isi);
    $tr.find('.input-berat').val(ton);
    $tr.find('.input-kubikasi').val(kub);

    calcRow(selectElem);
}

function calcRow(elem) {
    var $tr = getRowElem(elem);
    var qBox = parseFloat($tr.find('.input-qty-box').val()) || 0;
    var qSat = parseFloat($tr.find('.input-qty-ecer').val()) || 0;
    var isi  = parseInt($tr.find('.input-isi-box').val()) || 1; if (isi < 1) isi = 1;
    var hrg  = parseHargaInput($tr.find('.input-hrg-satuan').val());
    var disc = parseFloat($tr.find('.input-disc').val()) || 0;

    var qTotal = (qBox * isi) + qSat;
    var subB   = hrg * qTotal;
    var sub    = subB * (1 - disc / 100);

    $tr.find('.cell-total-pcs').text(fmtNum(qTotal, 0));
    $tr.find('.cell-subtotal').text(fmtNum(sub, 0));

    calcGrandTotal();
}

function calcGrandTotal() {
    var grandTotal = 0;
    $('#gridBody tr').each(function() {
        var $tr = $(this);
        var qBox = parseFloat($tr.find('.input-qty-box').val()) || 0;
        var qSat = parseFloat($tr.find('.input-qty-ecer').val()) || 0;
        var isi  = parseInt($tr.find('.input-isi-box').val()) || 1; if (isi < 1) isi = 1;
        var hrg  = parseHargaInput($tr.find('.input-hrg-satuan').val());
        var disc = parseFloat($tr.find('.input-disc').val()) || 0;

        var qTotal = (qBox * isi) + qSat;
        grandTotal += (hrg * qTotal) * (1 - disc / 100);
    });

    $('#grandTotalText').text('Rp ' + fmtNum(grandTotal, 0));
}

/* =========================================================================
   LOOKUP CUSTOMER MODAL
   ========================================================================= */

function openLookupCustomer() {
    selectedLookupCust = null;
    $('#btnPilihCustomer').prop('disabled', true);
    $('#searchCustomer').val('');
    renderCustomerLookup('');
    $('#modalCustomer').modal('show');
}

function renderCustomerLookup(q) {
    q = (q || '').toLowerCase();
    var list = q ? CUSTOMERS.filter(function(c) {
        return String(c.nama_customer || '').toLowerCase().indexOf(q) >= 0 ||
               String(c.nama_kios || '').toLowerCase().indexOf(q) >= 0 ||
               String(c.kd_customer || '').toLowerCase().indexOf(q) >= 0;
    }) : CUSTOMERS;

    if (!list.length) {
        $('#bodyLookupCustomer').html('<tr><td colspan="4" style="height:320px;text-align:center;vertical-align:middle;color:#64748b;">Tidak ada data customer.</td></tr>');
        return;
    }

    var html = '';
    $.each(list, function(i, c) {
        var plafonNum = Number(String(c.plafon_aktif || 0).replace(/,/g, ''));
        var plafonStr = isFinite(plafonNum) ? 'Rp ' + fmtNum(plafonNum, 0) : '-';

        html += '<tr data-json="' + escAttr(JSON.stringify(c)) + '" onclick="selectLookupCustomer(this)" ondblclick="pilihCustomer()">';
        html += '<td><strong style="color:var(--zahir-blue);">' + escHtml(c.kd_customer || '-') + '</strong></td>';
        html += '<td>' + escHtml(c.nama_customer || '') + '</td>';
        html += '<td>' + escHtml(c.nama_kios || '-') + '</td>';
        html += '<td style="text-align:right;">' + escHtml(plafonStr) + '</td>';
        html += '</tr>';
    });

    $('#bodyLookupCustomer').html(html);
}

$('#searchCustomer').on('input', function() {
    renderCustomerLookup(this.value);
});

function selectLookupCustomer(tr) {
    $('#bodyLookupCustomer tr').removeClass('selected');
    $(tr).addClass('selected');
    selectedLookupCust = JSON.parse($(tr).attr('data-json'));
    $('#btnPilihCustomer').prop('disabled', false);
}

function pilihCustomer() {
    if (!selectedLookupCust) return;

    $('#customer_id').val(selectedLookupCust.kd_customer || '');
    $('#customer_name').val(selectedLookupCust.nama_customer || '');
    $('#customer_display').val(selectedLookupCust.nama_customer || '');
    $('#customer_plafon').val(selectedLookupCust.plafon_aktif || '');

    $('#modalCustomer').modal('hide');
}

/* =========================================================================
   LOOKUP BARANG MODAL
   ========================================================================= */

function stockUrl(gid) {
    var url = BASE_URL + 'sales_order_loby/get_stock?gudang_id=' + encodeURIComponent(gid || '');
    if (SO_ID) url += '&exclude_id_so=' + encodeURIComponent(SO_ID);
    return url;
}

function openLookupBarang(elemOrIdx) {
    var gid = $('#gudang_id_input').val();
    if (!gid) {
        alert('Silakan pilih Gudang terlebih dahulu sebelum memilih barang.');
        $('#gudang_id_input').focus();
        return;
    }

    $activeFormTr = getRowElem(elemOrIdx);
    activeRowIdx = $activeFormTr.index();
    selectedLookupBarang = null;
    $('#btnPilihBarang').prop('disabled', true);
    $('#searchBarang').val('');
    $('#modalBarang').modal('show');
    loadStockLookup();
}

function loadStockLookup() {
    var gid = $('#gudang_id_input').val();
    if (stockLoaded && stockCache.length) {
        renderStockLookup();
        return;
    }

    $('#bodyLookupBarang').html('<tr><td colspan="6" style="height:320px;text-align:center;vertical-align:middle;color:#64748b;"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data stok...</td></tr>');

    $.getJSON(stockUrl(gid), function(res) {
        if (res.status === 'success' || res.status === 'ok') {
            stockCache  = res.data || [];
            stockLoaded = true;
            renderStockLookup();
        } else {
            $('#bodyLookupBarang').html('<tr><td colspan="6" style="height:320px;text-align:center;vertical-align:middle;color:#d9534f;">' + escHtml(res.message || 'Gagal memuat data') + '</td></tr>');
        }
    }).fail(function() {
        $('#bodyLookupBarang').html('<tr><td colspan="6" style="height:320px;text-align:center;vertical-align:middle;color:#d9534f;">Gagal terhubung ke server.</td></tr>');
    });
}

function renderStockLookup() {
    var q = ($('#searchBarang').val() || '').toLowerCase();
    var list = q ? stockCache.filter(function(d) {
        return String(d.kd_barang || '').toLowerCase().indexOf(q) >= 0 ||
               String(d.nama_barang || '').toLowerCase().indexOf(q) >= 0;
    }) : stockCache;

    if (!list.length) {
        $('#bodyLookupBarang').html('<tr><td colspan="6" style="height:320px;text-align:center;vertical-align:middle;color:#64748b;">Tidak ada stok tersedia.</td></tr>');
        return;
    }

    var html = '';
    $.each(list, function(i, d) {
        var kd  = d.kd_barang || '';
        var nm  = d.nama_barang || '';
        var exp = d.exp_date || d.expired_date || '';
        var lot = d.no_lot || '-';
        var isi = parseInt(d.isi_per_box || 1);
        var avT = parseFloat(d.available_stock || 0);
        var avB = Math.floor(avT / isi);

        html += '<tr data-json="' + escAttr(JSON.stringify(d)) + '" onclick="selectLookupBarang(this)" ondblclick="pilihBarang()">';
        html += '<td><strong style="color:var(--zahir-blue);">' + escHtml(kd) + '</strong></td>';
        html += '<td>' + escHtml(nm) + '</td>';
        html += '<td>' + escHtml(formatTgl(exp)) + '</td>';
        html += '<td>' + escHtml(lot) + '</td>';
        html += '<td style="text-align:right;"><strong>' + fmtNum(avB, 0) + '</strong> box</td>';
        html += '<td style="text-align:right;"><strong>' + fmtNum(avT, 0) + '</strong> pcs</td>';
        html += '</tr>';
    });

    $('#bodyLookupBarang').html(html);
}

$('#searchBarang').on('input', function() {
    renderStockLookup();
});

function selectLookupBarang(tr) {
    $('#bodyLookupBarang tr').removeClass('selected');
    $(tr).addClass('selected');
    selectedLookupBarang = JSON.parse($(tr).attr('data-json'));
    $('#btnPilihBarang').prop('disabled', false);
}

function pilihBarang() {
    if (!selectedLookupBarang) return;

    var kd     = selectedLookupBarang.kd_barang || '';
    var nm     = selectedLookupBarang.nama_barang || '';
    var sat    = selectedLookupBarang.satuan || 'PCS';
    var isi    = parseInt(selectedLookupBarang.isi_per_box || 1); if (isi < 1) isi = 1;
    var beratG = parseFloat(selectedLookupBarang.berat_gram || 0);
    var kubikM = parseFloat(selectedLookupBarang.kubikasi_m3 || 0);
    var pk     = parseFloat(selectedLookupBarang.hpp || 0);

    var $tr = $activeFormTr && $activeFormTr.length ? $activeFormTr : getRowElem(activeRowIdx);

    $tr.find('.input-kd-barang').val(kd);
    $tr.find('.input-nm-barang').val(nm);
    $tr.find('.input-satuan').val(sat);
    $tr.find('.input-isi-box').val(isi);
    $tr.find('.input-berat').val(beratG);
    $tr.find('.input-kubikasi').val(kubikM);
    $tr.find('.input-hrg-pokok').val(pk);

    // Filter semua batch stok untuk barang ini
    var sameItems = stockCache.filter(function(s) { return (s.kd_barang || '') === kd; });
    var $selExp = $tr.find('.input-exp-select');
    $selExp.empty();
    $selExp.append('<option value="">-- Pilih Exp/Lot --</option>');

    sameItems.forEach(function(s) {
        var ed = s.exp_date || s.expired_date || '';
        var lot = s.no_lot || '';
        var isiS = parseInt(s.isi_per_box || 1);
        var avT = parseFloat(s.available_stock || 0);
        var avB = Math.floor(avT / isiS);
        var label = formatTgl(ed) + (lot ? ' | Lot: ' + lot : '') + ' [' + fmtNum(avB,0) + ' box]';

        var opt = $('<option></option>')
            .val(ed)
            .text(label)
            .attr('data-lot', lot)
            .attr('data-av', avT)
            .attr('data-isi', isiS)
            .attr('data-ton', parseFloat(s.berat_gram || 0))
            .attr('data-kub', parseFloat(s.kubikasi_m3 || 0));

        if ((s.stock_batch_id || s.id) === (selectedLookupBarang.stock_batch_id || selectedLookupBarang.id)) {
            opt.prop('selected', true);
        }
        $selExp.append(opt);
    });

    if (sameItems.length === 1) {
        $selExp.find('option').eq(1).prop('selected', true);
    }
    $selExp.trigger('change');

    $tr.find('.input-qty-box').val('');
    $tr.find('.input-qty-ecer').val('');
    calcRow($tr);

    $('#modalBarang').modal('hide');
}

// Auto select teks saat input Qty, Disc, & Harga difokuskan
$(document).on('focus', '.input-qty-box, .input-qty-ecer, .input-disc, .input-hrg-satuan', function() {
    this.select();
});

// Reset stok cache saat ganti gudang
$('#gudang_id_input').on('change', function() {
    stockCache  = [];
    stockLoaded = false;
});

/* =========================================================================
   FORM SUBMIT VALIDATION
   ========================================================================= */

$('#form-so').on('submit', function(e) {
    if (!$('#customer_id').val()) {
        e.preventDefault();
        alert('Pilih Customer terlebih dahulu.');
        openLookupCustomer();
        return;
    }

    if (!$('#gudang_id_input').val()) {
        e.preventDefault();
        alert('Pilih Gudang terlebih dahulu.');
        $('#gudang_id_input').focus();
        return;
    }

    var validRows = 0;
    var hasEmptyQty = false;

    $('#gridBody tr').each(function() {
        var kd = $(this).find('.input-kd-barang').val();
        var qB = parseFloat($(this).find('.input-qty-box').val()) || 0;
        var qE = parseFloat($(this).find('.input-qty-ecer').val()) || 0;

        if (kd) {
            validRows++;
            if (qB <= 0 && qE <= 0) {
                hasEmptyQty = true;
            }
        }
    });

    if (validRows === 0) {
        e.preventDefault();
        alert('Minimal harus ada 1 item barang yang dipilih.');
        return;
    }

    if (hasEmptyQty) {
        e.preventDefault();
        alert('Pastikan Qty Box atau Eceran pada barang yang dipilih lebih dari 0.');
        return;
    }
});

/* =========================================================================
   INIT
   ========================================================================= */

$(document).ready(function() {
    if (EDIT_DETAILS && EDIT_DETAILS.length) {
        EDIT_DETAILS.forEach(function(d) {
            addNewRow(d);
        });
    } else {
        addNewRow();
    }
});
</script>
</body>
