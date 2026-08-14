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

    /* Modal Lookup */
    .lookup-modal .modal-header {
        background: var(--zahir-blue);
        color: #fff;
        padding: 10px 16px;
    }

    .lookup-modal .modal-title {
        font-size: 16px;
        font-weight: 600;
    }

    .lookup-search {
        padding: 10px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid #e2e8f0;
    }

    .lookup-search input {
        flex: 1;
        font-size: 13px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 6px 10px;
        height: 34px;
    }

    .lookup-table {
        width: 100%;
        border-collapse: collapse;
    }

    .lookup-table thead th {
        background: #f1f5f9;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        border-bottom: 2px solid #cbd5e1;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .lookup-table tbody td {
        font-size: 12px;
        padding: 8px 12px;
        border-bottom: 1px solid #eef2f5;
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .lookup-table tbody tr:hover td {
        background: #e3f2fd;
    }

    .lookup-table tbody tr.selected td {
        background: #90caf9;
        font-weight: 600;
    }

    .lookup-scroll {
        max-height: 350px;
        overflow-y: auto;
    }

    .lookup-bottom {
        padding: 10px 16px;
        display: flex;
        gap: 8px;
        justify-content: center;
        border-top: 1px solid #e2e8f0;
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
                        <input type="text" id="keterangan" class="form-control-zahir" style="width:380px"
                               value="<?= htmlspecialchars($header ? $header['keterangan'] : 'Penyesuaian Persediaan') ?>" />
                    </div>
                    <div class="form-row-zahir">
                        <label>Dari Gudang :</label>
                        <div class="gudang-group">
                            <select id="gudangDari" class="form-control-zahir gudang-select">
                                <option value="">-- Pilih --</option>
                                <?php foreach ($gudangs as $g): ?>
                                    <option value="<?= $g['id_gudang'] ?>"
                                        <?= ($header && $header['id_gudang_dari'] == $g['id_gudang']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['nama_gudang']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn-lookup-small" title="Pilih gudang"><i class="fas fa-th"></i></button>
                        </div>
                    </div>
                    <div class="form-row-zahir">
                        <label>Ke Gudang :</label>
                        <div class="gudang-group">
                            <select id="gudangKe" class="form-control-zahir gudang-select">
                                <option value="">-- Pilih --</option>
                                <?php foreach ($gudangs as $g): ?>
                                    <option value="<?= $g['id_gudang'] ?>"
                                        <?= ($header && $header['id_gudang_ke'] == $g['id_gudang']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($g['nama_gudang']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn-lookup-small" title="Pilih gudang"><i class="fas fa-th"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Area Grid Tabel Detail Transaksi -->
                <div class="form-grid-section">
                    <table class="grid-table" id="detailGrid">
                        <colgroup>
                            <col style="width: 170px;">
                            <col style="width: auto;">
                            <col style="width: 130px;">
                            <col style="width: 100px;">
                            <col style="width: 220px;">
                            <col style="width: 45px;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th style="text-align:right">Jumlah</th>
                                <th>Satuan</th>
                                <th>Akun Penyesuaian</th>
                                <th style="text-align:center">#</th>
                            </tr>
                        </thead>
                        <tbody id="gridBody">
                            <!-- Baris input dinamis di-render via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Bottom Bar Sticky di Bawah -->
                <div class="form-bottom-bar">
                    <div class="form-bottom-left">
                        <label><input type="checkbox" id="chkCetak" /> Cetak</label>
                        <label><input type="checkbox" id="chkPosting" checked /> Posting</label>
                        <label><input type="checkbox" id="chkKembali" /> Kembali ke Awal</label>
                    </div>
                    <div class="form-bottom-right">
                        <button type="button" class="btn-zahir btn-zahir-teal" onclick="addNewRow()"><i class="fas fa-plus"></i> Tambah Baris</button>
                        <button type="button" class="btn-zahir btn-zahir-primary" onclick="doRekam()"><i class="fas fa-save"></i> Rekam</button>
                        <button type="button" class="btn-zahir btn-zahir-secondary" onclick="doBatal()"><i class="fas fa-times"></i> Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lookup Barang -->
<div class="modal fade lookup-modal" id="modalBarang" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-boxes mr-2"></i> Data Barang Persediaan</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="lookup-search">
                <input type="text" id="searchBarang" placeholder="Ketik kode / nama barang..." />
                <button class="btn-zahir btn-zahir-primary" onclick="loadBarangLookup()"><i class="fas fa-search"></i> Cari</button>
            </div>
            <div class="lookup-scroll">
                <table class="lookup-table" id="tableLookupBarang">
                    <colgroup>
                        <col style="width: 140px;">
                        <col style="width: auto;">
                        <col style="width: 90px;">
                        <col style="width: 110px;">
                        <col style="width: 110px;">
                        <col style="width: 110px;">
                        <col style="width: 140px;">
                        <col style="width: 180px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No. Barang</th>
                            <th>Deskripsi</th>
                            <th>Sat.</th>
                            <th style="text-align:right">Tersedia</th>
                            <th style="text-align:right">Dipesan</th>
                            <th style="text-align:right">Total</th>
                            <th>Kelompok</th>
                            <th>Gudang</th>
                        </tr>
                    </thead>
                    <tbody id="bodyLookupBarang">
                        <tr><td colspan="8" class="text-center text-muted" style="padding:20px;">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="lookup-bottom">
                <button class="btn-zahir btn-zahir-primary" id="btnPilihBarang" disabled onclick="pilihBarang()"><i class="fas fa-check"></i> Pilih</button>
                <button class="btn-zahir btn-zahir-secondary" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lookup Akun -->
<div class="modal fade lookup-modal" id="modalAkun" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-list-alt mr-2"></i> Daftar Akun Perkiraan</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="lookup-search">
                <input type="text" id="searchAkun" placeholder="Ketik kode / nama akun..." />
                <button class="btn-zahir btn-zahir-primary" onclick="loadAkunLookup()"><i class="fas fa-search"></i> Cari</button>
            </div>
            <div class="lookup-scroll">
                <table class="lookup-table" id="tableLookupAkun">
                    <colgroup>
                        <col style="width: 150px;">
                        <col style="width: auto;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                        </tr>
                    </thead>
                    <tbody id="bodyLookupAkun">
                        <tr><td colspan="2" class="text-center text-muted" style="padding:20px;">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="lookup-bottom">
                <button class="btn-zahir btn-zahir-primary" id="btnPilihAkun" disabled onclick="pilihAkun()"><i class="fas fa-check"></i> Pilih</button>
                <button class="btn-zahir btn-zahir-secondary" data-dismiss="modal">Batal</button>
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
    activeRowIdx = idx;
    selectedLookupBarang = null;
    $('#btnPilihBarang').prop('disabled', true);
    $('#searchBarang').val('');
    $('#modalBarang').modal('show');
    loadBarangLookup();
}

// Muat data barang
function loadBarangLookup() {
    var search = $('#searchBarang').val();
    var gudang_id = $('#gudangDari').val();

    $('#bodyLookupBarang').html('<tr><td colspan="8" class="text-center text-muted" style="padding:20px;"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data barang...</td></tr>');

    $.get(BASE + 'persediaan/penyesuaian_barang/barang_lookup', {
        search: search,
        gudang_id: gudang_id
    }, function(res) {
        if (!res.success || !res.data.length) {
            $('#bodyLookupBarang').html('<tr><td colspan="8" class="text-center text-muted" style="padding:20px;">Tidak ada data barang.</td></tr>');
            return;
        }

        var html = '';
        $.each(res.data, function(i, b) {
            html += '<tr data-json="' + escAttr(JSON.stringify(b)) + '" onclick="selectLookupBarang(this)" ondblclick="pilihBarang()">';
            html += '<td>' + escHtml(b.kd_barang) + '</td>';
            html += '<td>' + escHtml(b.nama_barang) + '</td>';
            html += '<td>' + escHtml(b.satuan || '') + '</td>';
            html += '<td style="text-align:right">' + formatNumber(b.tersedia) + '</td>';
            html += '<td style="text-align:right">' + formatNumber(b.dipesan) + '</td>';
            html += '<td style="text-align:right">' + formatNumber(b.total) + '</td>';
            html += '<td>' + escHtml(b.kelompok || '-') + '</td>';
            html += '<td>' + escHtml(b.nama_gudang || '-') + '</td>';
            html += '</tr>';
        });

        $('#bodyLookupBarang').html(html);
    }, 'json').fail(function() {
        $('#bodyLookupBarang').html('<tr><td colspan="8" class="text-center text-muted" style="padding:20px;">Gagal memuat barang.</td></tr>');
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

    var tr = $('#gridBody tr[data-idx="' + activeRowIdx + '"]');
    tr.find('.input-kd-barang').val(selectedLookupBarang.kd_barang);
    tr.find('.input-nm-barang').val(selectedLookupBarang.nama_barang);
    tr.find('.input-satuan').val(selectedLookupBarang.satuan || '');

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

    $('#bodyLookupAkun').html('<tr><td colspan="2" class="text-center text-muted" style="padding:20px;"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat akun...</td></tr>');

    $.get(BASE + 'persediaan/penyesuaian_barang/accounts_lookup', {
        search: search
    }, function(res) {
        if (!res.success || !res.data.length) {
            $('#bodyLookupAkun').html('<tr><td colspan="2" class="text-center text-muted" style="padding:20px;">Tidak ada akun.</td></tr>');
            return;
        }

        var html = '';
        $.each(res.data, function(i, a) {
            html += '<tr data-json="' + escAttr(JSON.stringify(a)) + '" onclick="selectLookupAkun(this)" ondblclick="pilihAkun()">';
            html += '<td><strong>' + escHtml(a.kode_akun) + '</strong></td>';
            html += '<td>' + escHtml(a.nama_akun) + '</td>';
            html += '</tr>';
        });

        $('#bodyLookupAkun').html(html);
    }, 'json').fail(function() {
        $('#bodyLookupAkun').html('<tr><td colspan="2" class="text-center text-muted" style="padding:20px;">Gagal memuat akun.</td></tr>');
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
    var postNow = $('#chkPosting').is(':checked');

    if (!tanggal) {
        alert('Tanggal harus diisi.');
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
</script>
</body>
