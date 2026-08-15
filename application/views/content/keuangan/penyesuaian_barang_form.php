<!-- application/views/content/keuangan/penyesuaian_barang_form.php -->
<!-- Form Penyesuaian Persediaan (Zahir Style) -->
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
        padding: 14px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f5;
    }

    .form-row-zahir {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .form-row-zahir:last-child {
        margin-bottom: 0;
    }

    .form-row-zahir label {
        width: 110px;
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

    .gudang-group {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .gudang-select {
        width: 220px;
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
    .form-grid-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow-y: auto;
        min-height: 380px;
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

    .form-bottom-left label {
        margin: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-bottom-right {
        margin-left: auto;
        display: flex;
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
    .btn-zahir-danger { background: #d9534f; color: #fff; }
    .btn-zahir-danger:hover:not(:disabled) { background: #c9302c; color: #fff; }
    .btn-zahir-secondary { background: #6c757d; color: #fff; }
    .btn-zahir-secondary:hover:not(:disabled) { background: #5a6268; color: #fff; }
    .btn-zahir-teal { background: #17a2b8; color: #fff; }
    .btn-zahir-teal:hover:not(:disabled) { background: #138496; color: #fff; }

    /* Zahir Style Lookup Modal (Sesuai Desain Asli) */
    .zahir-lookup-modal .modal-dialog {
        width: 820px;
        max-width: calc(100vw - 40px);
        margin: 60px auto 30px auto;
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
        font-size: 20px;
        font-weight: 500;
        margin: 0;
        letter-spacing: -0.2px;
    }

    .zahir-modal-search {
        position: relative;
        width: 240px;
    }

    .zahir-modal-search input {
        width: 100%;
        height: 30px;
        padding: 4px 28px 4px 10px;
        font-size: 13px;
        border: 1px solid #d4d8db;
        border-radius: 2px;
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
        top: 8px;
        font-size: 12px;
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
        justify-content: space-between;
        align-items: center;
        background: #fff;
    }

    .btn-zahir-action {
        background-color: #0f769f;
        color: #fff;
        border: none;
        border-radius: 2px;
        padding: 5px 18px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        min-width: 65px;
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

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="pb-container">
            <div class="zahir-card">
                <!-- Header Title -->
                <div class="form-header-title">
                    <h2><i class="fas fa-sliders-h"></i> Penyesuaian Persediaan</h2>
                </div>

                <!-- Form Header Fields -->
                <div class="form-header-section">
                    <div class="form-row-zahir">
                        <label>Ref. :</label>
                        <input type="text" id="refNo" class="form-control-zahir ref-input" style="width:160px"
                               value="<?= htmlspecialchars($next_ref) ?>" readonly />
                    </div>
                    <div class="form-row-zahir">
                        <label>Tanggal :</label>
                        <input type="date" id="tanggal" class="form-control-zahir date-input"
                               value="<?= $header ? $header['tanggal'] : date('Y-m-d') ?>" />
                    </div>
                    <div class="form-row-zahir">
                        <label>Keterangan :</label>
                        <input type="text" id="keterangan" class="form-control-zahir memo-input" style="width:400px"
                               value="<?= $header ? htmlspecialchars($header['keterangan']) : 'Penyesuaian Persediaan' ?>" />
                    </div>
                    <?php $daftar_gudang = !empty($gudang_list) ? $gudang_list : (!empty($gudangs) ? $gudangs : []); ?>
                    <div class="form-row-zahir" style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label style="width: 110px; margin: 0;">Dari Gudang <span style="color:#d9534f; font-weight:bold;">*</span> :</label>
                            <select id="gudangDari" class="form-control-zahir select-gudang" style="width:200px" required>
                                <option value="">-- Pilih Gudang --</option>
                                <?php foreach ($daftar_gudang as $gd): ?>
                                    <option value="<?= $gd['id_gudang'] ?>"
                                        <?= ($header && $header['id_gudang_dari'] == $gd['id_gudang']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($gd['nama_gudang']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <label style="width: auto; margin: 0;">Ke Gudang :</label>
                            <select id="gudangKe" class="form-control-zahir select-gudang" style="width:200px">
                                <option value="">-- Pilih (Opsional) --</option>
                                <?php foreach ($daftar_gudang as $gd): ?>
                                    <option value="<?= $gd['id_gudang'] ?>"
                                        <?= ($header && $header['id_gudang_ke'] == $gd['id_gudang']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($gd['nama_gudang']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Grid Detail Transaksi -->
                <div class="table-container">
                    <table class="grid-table" id="gridTable">
                        <thead>
                            <tr>
                                <th style="width: 160px;">Kode</th>
                                <th style="width: auto;">Nama Barang</th>
                                <th style="width: 120px; text-align:right">Jumlah</th>
                                <th style="width: 100px;">Satuan</th>
                                <th style="width: 240px;">Akun</th>
                                <th style="width: 45px; text-align:center">#</th>
                            </tr>
                        </thead>
                        <tbody id="gridBody">
                            <!-- Diisi secara dinamis via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Petunjuk Zahir -->
                <div style="padding: 8px 20px; font-size: 12px; color: #475569;">
                    Perhatian : Jumlah Barang harus <strong>NEGATIF</strong> jika dikeluarkan !!
                </div>

                <!-- Form Bottom Actions -->
                <div class="form-bottom-bar">
                    <div class="form-bottom-left">
                        <button type="button" class="btn-zahir btn-zahir-primary" onclick="delActiveOrLastRow()" style="min-width: 100px;">Hapus Baris</button>
                    </div>
                    <div class="form-bottom-right" style="display: flex; align-items: center; gap: 12px;">
                        <label style="margin: 0; display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" id="chkCetak" />
                            <span>Cetak</span>
                        </label>
                        <label style="margin: 0; display: flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" id="chkPosting" checked />
                            <span>Posting</span>
                        </label>
                        <button type="button" class="btn-zahir btn-zahir-teal" onclick="addNewRow()"><i class="fas fa-plus"></i> Tambah Baris</button>
                        <button type="button" class="btn-zahir btn-zahir-secondary" onclick="doBatal()">Batal</button>
                        <button type="button" class="btn-zahir btn-zahir-secondary" onclick="doRekamDraft()">Rekam Draft</button>
                        <button type="button" class="btn-zahir btn-zahir-primary" onclick="doRekam()">Rekam</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lookup Barang (Data Persediaan) Sesuai Desain Zahir -->
<div class="modal fade zahir-lookup-modal" id="modalBarang" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" style="max-width: 820px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Persediaan</h5>
                <div class="zahir-modal-search">
                    <input type="text" id="searchBarang" placeholder="" />
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="zahir-lookup-scroll">
                <table class="zahir-lookup-table" id="tableLookupBarang">
                    <colgroup>
                        <col style="width: 25%;">
                        <col style="width: 60%;">
                        <col style="width: 15%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Deskripsi</th>
                            <th class="text-right">Tersedia</th>
                        </tr>
                    </thead>
                    <tbody id="bodyLookupBarang">
                        <tr><td colspan="3" style="height: 320px; text-align: center; vertical-align: middle; color: #64748b;"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="zahir-lookup-footer">
                <div>
                    <button type="button" class="btn-zahir-action" onclick="alert('Pilih baris terlebih dahulu')">Hapus</button>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn-zahir-action" onclick="window.open(BASE + 'master_barang', '_blank')">Baru</button>
                    <button type="button" class="btn-zahir-action" onclick="alert('Fitur edit master barang')">Edit</button>
                    <button type="button" class="btn-zahir-action" onclick="loadBarangLookup()">Update</button>
                    <button type="button" class="btn-zahir-action" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn-zahir-action" id="btnPilihBarang" disabled onclick="pilihBarang()">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lookup Akun (Daftar Akun (Perkiraan)) Sesuai Desain Zahir -->
<div class="modal fade zahir-lookup-modal" id="modalAkun" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" style="max-width: 820px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daftar Akun (Perkiraan)</h5>
                <div class="zahir-modal-search">
                    <input type="text" id="searchAkun" placeholder="" />
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="zahir-lookup-scroll">
                <table class="zahir-lookup-table" id="tableLookupAkun">
                    <colgroup>
                        <col style="width: 30%;">
                        <col style="width: 70%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Akun</th>
                        </tr>
                    </thead>
                    <tbody id="bodyLookupAkun">
                        <tr><td colspan="2" style="height: 320px; text-align: center; vertical-align: middle; color: #64748b;"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="zahir-lookup-footer">
                <div>
                    <button type="button" class="btn-zahir-action" onclick="alert('Pilih baris terlebih dahulu')">Hapus</button>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn-zahir-action" onclick="alert('Fitur tambah akun')">Baru</button>
                    <button type="button" class="btn-zahir-action" onclick="alert('Fitur edit akun')">Edit</button>
                    <button type="button" class="btn-zahir-action" onclick="loadAkunLookup()">Update</button>
                    <button type="button" class="btn-zahir-action" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn-zahir-action" id="btnPilihAkun" disabled onclick="pilihAkun()">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Input Hidden ID Transaksi untuk Edit -->
<input type="hidden" id="editId" value="<?= $id ? (int)$id : '' ?>" />

<script>
var BASE = '<?= base_url(); ?>';
var activeRowIdx = null;
var selectedLookupBarang = null;
var selectedLookupAkun = null;
var initialDetails = <?= !empty($header['details']) ? json_encode($header['details']) : '[]' ?>;

$(document).ready(function() {
    // Render baris form awal
    if (initialDetails.length > 0) {
        $.each(initialDetails, function(i, d) {
            addNewRow(d);
        });
    } else {
        // Buat 5 baris kosong secara default seperti di Zahir
        for (var i = 0; i < 5; i++) {
            addNewRow();
        }
    }

    // Debounce Live Search Modal Barang
    var debounceTimerBarang = null;
    $('#searchBarang').on('input', function() {
        clearTimeout(debounceTimerBarang);
        debounceTimerBarang = setTimeout(function() {
            loadBarangLookup();
        }, 250);
    });

    $('#searchBarang').on('keypress', function(e) {
        if (e.which === 13) {
            clearTimeout(debounceTimerBarang);
            loadBarangLookup();
        }
    });

    // Debounce Live Search Modal Akun
    var debounceTimerAkun = null;
    $('#searchAkun').on('input', function() {
        clearTimeout(debounceTimerAkun);
        debounceTimerAkun = setTimeout(function() {
            loadAkunLookup();
        }, 250);
    });

    $('#searchAkun').on('keypress', function(e) {
        if (e.which === 13) {
            clearTimeout(debounceTimerAkun);
            loadAkunLookup();
        }
    });
});

// Tambah baris baru di grid
function addNewRow(data) {
    data = data || {};
    var idx = $('#gridBody tr').length;

    var kd_barang = data.kd_barang || '';
    var nm_barang = data.nm_barang || '';
    var jumlah = data.jumlah !== undefined ? data.jumlah : '';
    var satuan = data.satuan || '';
    var id_akun = data.id_akun || '';
    var kode_akun = data.kode_akun || '';
    var nama_akun = data.nama_akun || '';
    var label_akun = id_akun ? (kode_akun + ' - ' + nama_akun) : '';

    var html = '<tr data-idx="' + idx + '">';
    // Kode Barang
    html += '<td><div class="cell-lookup-group">';
    html += '<input type="text" class="cell-input input-kd-barang" value="' + escAttr(kd_barang) + '" placeholder="Pilih..." readonly onclick="openLookupBarang(' + idx + ')" />';
    html += '<button type="button" class="btn-cell-lookup" onclick="openLookupBarang(' + idx + ')"><i class="fas fa-ellipsis-h"></i></button>';
    html += '</div></td>';

    // Nama Barang
    html += '<td><input type="text" class="cell-input input-nm-barang" value="' + escAttr(nm_barang) + '" placeholder="Deskripsi barang" readonly /></td>';

    // Jumlah (bisa negatif untuk pengeluaran barang, positif untuk penerimaan)
    html += '<td><input type="number" step="any" class="cell-input input-jumlah" style="text-align:right" value="' + escAttr(jumlah) + '" placeholder="0" /></td>';

    // Satuan
    html += '<td><input type="text" class="cell-input input-satuan" value="' + escAttr(satuan) + '" placeholder="Satuan" /></td>';

    // Akun Penyesuaian
    html += '<td><div class="cell-lookup-group">';
    html += '<input type="text" class="cell-input input-label-akun" value="' + escAttr(label_akun) + '" placeholder="Pilih akun..." readonly onclick="openLookupAkun(' + idx + ')" />';
    html += '<input type="hidden" class="input-id-akun" value="' + escAttr(id_akun) + '" />';
    html += '<button type="button" class="btn-cell-lookup" onclick="openLookupAkun(' + idx + ')"><i class="fas fa-ellipsis-h"></i></button>';
    html += '</div></td>';

    // Hapus Baris
    html += '<td style="text-align:center"><button type="button" class="btn-del-row" onclick="delRow(this)" title="Hapus baris"><i class="fas fa-times"></i></button></td>';

    html += '</tr>';

    $('#gridBody').append(html);
}

// Hapus baris
function delRow(btn) {
    if ($('#gridBody tr').length > 1) {
        $(btn).closest('tr').remove();
    } else {
        // Kosongkan baris jika hanya 1
        var tr = $(btn).closest('tr');
        tr.find('input').val('');
    }
}

// Buka Modal Lookup Barang
function openLookupBarang(idx) {
    if (!$('#gudangDari').val()) {
        alert('Silakan pilih Dari Gudang terlebih dahulu sebelum memilih barang.');
        $('#gudangDari').focus();
        return;
    }
    activeRowIdx = idx;
    selectedLookupBarang = null;
    $('#btnPilihBarang').prop('disabled', true);
    $('#searchBarang').val('');
    $('#modalBarang').modal('show');
    loadBarangLookup();
}

// Muat data barang (Data Persediaan: Kode, Deskripsi, Tersedia)
function loadBarangLookup() {
    var search = $('#searchBarang').val();
    var gudang_id = $('#gudangDari').val();

    $('#bodyLookupBarang').html('<tr><td colspan="3" style="height: 320px; text-align: center; vertical-align: middle; color: #64748b;"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data...</td></tr>');

    $.get(BASE + 'persediaan/penyesuaian_barang/barang_lookup', {
        search: search,
        gudang_id: gudang_id
    }, function(res) {
        if (!res.success || !res.data.length) {
            $('#bodyLookupBarang').html('<tr><td colspan="3" style="height: 320px; text-align: center; vertical-align: middle; color: #64748b;">Tidak ada data barang.</td></tr>');
            return;
        }

        var html = '';
        $.each(res.data, function(i, b) {
            var kd = b.kd_barang || b.kode || '';
            var nm = b.nama_barang || b.deskripsi || '';
            var tersedia = formatStockNumber(b.tersedia);

            html += '<tr data-json="' + escAttr(JSON.stringify(b)) + '" onclick="selectLookupBarang(this)" ondblclick="pilihBarang()">';
            html += '<td>' + escHtml(kd) + '</td>';
            html += '<td>' + escHtml(nm) + '</td>';
            html += '<td style="text-align:right">' + escHtml(tersedia) + '</td>';
            html += '</tr>';
        });

        $('#bodyLookupBarang').html(html);
    }, 'json').fail(function() {
        $('#bodyLookupBarang').html('<tr><td colspan="3" style="height: 320px; text-align: center; vertical-align: middle; color: #64748b;">Gagal memuat barang.</td></tr>');
    });
}

function selectLookupBarang(tr) {
    $('#bodyLookupBarang tr').removeClass('selected');
    $(tr).addClass('selected');
    selectedLookupBarang = JSON.parse($(tr).attr('data-json'));
    $('#btnPilihBarang').prop('disabled', false);
}

function pilihBarang() {
    if (!selectedLookupBarang || activeRowIdx === null) return;

    var kd = selectedLookupBarang.kd_barang || selectedLookupBarang.kode || '';
    var nm = selectedLookupBarang.nama_barang || selectedLookupBarang.deskripsi || '';
    var sat = selectedLookupBarang.satuan || '';

    var tr = $('#gridBody tr[data-idx="' + activeRowIdx + '"]');
    tr.find('.input-kd-barang').val(kd);
    tr.find('.input-nm-barang').val(nm);
    tr.find('.input-satuan').val(sat);

    // Auto-fill Akun Penyesuaian jika barang punya mapping akun default
    if (selectedLookupBarang.id_akun && !tr.find('.input-id-akun').val()) {
        tr.find('.input-id-akun').val(selectedLookupBarang.id_akun);
        tr.find('.input-label-akun').val(selectedLookupBarang.kode_akun + ' - ' + selectedLookupBarang.nama_akun);
    }

    $('#modalBarang').modal('hide');
    tr.find('.input-jumlah').focus();
}

// Buka Modal Lookup Akun
function openLookupAkun(idx) {
    activeRowIdx = idx;
    selectedLookupAkun = null;
    $('#btnPilihAkun').prop('disabled', true);
    $('#searchAkun').val('');
    $('#modalAkun').modal('show');
    loadAkunLookup();
}

// Muat data akun
function loadAkunLookup() {
    var search = $('#searchAkun').val();

    $('#bodyLookupAkun').html('<tr><td colspan="2" style="height: 320px; text-align: center; vertical-align: middle; color: #64748b;"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data...</td></tr>');

    $.get(BASE + 'persediaan/penyesuaian_barang/accounts_lookup', {
        search: search
    }, function(res) {
        if (!res.success || !res.data.length) {
            $('#bodyLookupAkun').html('<tr><td colspan="2" style="height: 320px; text-align: center; vertical-align: middle; color: #64748b;">Tidak ada data akun.</td></tr>');
            return;
        }

        var html = '';
        $.each(res.data, function(i, a) {
            html += '<tr data-json="' + escAttr(JSON.stringify(a)) + '" onclick="selectLookupAkun(this)" ondblclick="pilihAkun()">';
            html += '<td>' + escHtml(a.kode_akun) + '</td>';
            html += '<td>' + escHtml(a.nama_akun) + '</td>';
            html += '</tr>';
        });

        $('#bodyLookupAkun').html(html);
    }, 'json').fail(function() {
        $('#bodyLookupAkun').html('<tr><td colspan="2" style="height: 320px; text-align: center; vertical-align: middle; color: #64748b;">Gagal memuat akun.</td></tr>');
    });
}

function selectLookupAkun(tr) {
    $('#bodyLookupAkun tr').removeClass('selected');
    $(tr).addClass('selected');
    selectedLookupAkun = JSON.parse($(tr).attr('data-json'));
    $('#btnPilihAkun').prop('disabled', false);
}

function pilihAkun() {
    if (!selectedLookupAkun || activeRowIdx === null) return;

    var tr = $('#gridBody tr[data-idx="' + activeRowIdx + '"]');
    tr.find('.input-id-akun').val(selectedLookupAkun.id_akun);
    tr.find('.input-label-akun').val(selectedLookupAkun.kode_akun + ' - ' + selectedLookupAkun.nama_akun);

    $('#modalAkun').modal('hide');
}

// Rekam (Simpan & Posting Transaksi)
function doRekam() {
    var refNo = $('#refNo').val();
    var tanggal = $('#tanggal').val();
    var keterangan = $('#keterangan').val();
    var gudangDari = $('#gudangDari').val();
    var postNow = $('#chkPosting').is(':checked');

    if (!tanggal) {
        alert('Tanggal harus diisi.');
        return;
    }

    if (!gudangDari) {
        alert('Gudang Asal (Dari Gudang) wajib dipilih.');
        $('#gudangDari').focus();
        return;
    }

    var details = [];
    $('#gridBody tr').each(function() {
        var kd = $(this).find('.input-kd-barang').val();
        var nm = $(this).find('.input-nm-barang').val();
        var jml = parseFloat($(this).find('.input-jumlah').val()) || 0;
        var sat = $(this).find('.input-satuan').val();
        var idAkun = $(this).find('.input-id-akun').val();

        if (kd && jml !== 0) {
            details.push({
                kd_barang: kd,
                nm_barang: nm,
                jumlah: jml,
                satuan: sat,
                id_akun: idAkun
            });
        }
    });

    if (details.length === 0) {
        alert('Minimal harus ada 1 baris barang dengan jumlah tidak nol.');
        return;
    }

    var payload = {
        id_penyesuaian: $('#editId').val() || '',
        no_referensi: refNo,
        tanggal: tanggal,
        keterangan: keterangan,
        id_gudang_dari: $('#gudangDari').val(),
        id_gudang_ke: $('#gudangKe').val(),
        post_now: postNow ? 1 : 0,
        details: details
    };

    // Disable tombol
    $('.btn-zahir').prop('disabled', true);

    $.post(BASE + 'persediaan/penyesuaian_barang/save', payload, function(res) {
        $('.btn-zahir').prop('disabled', false);
        alert(res.message);

        if (res.success) {
            if ($('#chkCetak').is(':checked') && payload.id_penyesuaian) {
                window.open(BASE + 'persediaan/penyesuaian_barang/print_receipt/' + payload.id_penyesuaian, '_blank');
            }
            // Kembali ke list
            window.location.href = BASE + 'persediaan/penyesuaian_barang';
        }
    }, 'json').fail(function() {
        $('.btn-zahir').prop('disabled', false);
        alert('Gagal menyimpan. Periksa koneksi.');
    });
}

function doRekamDraft() {
    $('#chkPosting').prop('checked', false);
    doRekam();
}

function delActiveOrLastRow() {
    var $rows = $('#gridBody tr');
    if ($rows.length > 1) {
        $rows.last().remove();
    } else {
        $rows.first().find('input').val('');
    }
}

function doBatal() {
    if (confirm('Batal dan kembali ke daftar?')) {
        window.location.href = BASE + 'persediaan/penyesuaian_barang';
    }
}

// Utility
function escHtml(str) {
    if (!str) return '';
    return $('<span>').text(str).html();
}

function escAttr(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function formatNumber(val) {
    var num = parseFloat(val) || 0;
    return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatStockNumber(val) {
    var num = parseFloat(val) || 0;
    if (Math.floor(num) === num) {
        return num.toLocaleString('id-ID');
    }
    return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}
</script>
</body>
