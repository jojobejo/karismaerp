<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <!-- Navbar -->
    <?php $this->load->view('partial/main/navbar') ?>
    <!-- Main Sidebar Container -->
    <?php $this->load->view('partial/main/sidebar') ?>

<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-book text-primary mr-2"></i> Master Formula Paket Bundling
                    </h1>
                    <p class="text-muted mb-0 small">Master resep komposisi isi paket bundling untuk mempermudah Purchasing membuat request</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= site_url('purchasing/bundling/request') ?>" class="btn btn-outline-secondary font-weight-bold shadow-sm mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Ke Daftar Request
                    </a>
                    <button type="button" class="btn btn-primary font-weight-bold shadow-sm px-3" id="btnTambahFormula">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Formula Baru
                    </button>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                            <thead style="background: #f1f5f9; color: #334155;">
                                <tr>
                                    <th class="py-3 px-3">Kode Paket</th>
                                    <th class="py-3">Nama Paket Bundling</th>
                                    <th class="py-3 text-center">Struktur Kemasan</th>
                                    <th class="py-3 text-center">Satuan</th>
                                    <th class="py-3 text-center">Total Komponen</th>
                                    <th class="py-3">Keterangan</th>
                                    <th class="py-3 text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($formulas)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-flask fa-3x mb-3 text-black-50 d-block"></i>
                                            Belum ada master formula paket bundling tersimpan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($formulas as $f): ?>
                                        <tr>
                                            <td class="px-3 font-weight-bold text-primary"><?= htmlspecialchars($f['kode_paket']) ?></td>
                                            <td class="font-weight-bold text-dark"><?= htmlspecialchars($f['nama_paket']) ?></td>
                                            <td class="text-center">
                                                <?php if (!empty($f['is_innerbox']) && (float)$f['jumlah_innerbox'] > 0): ?>
                                                    <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold" style="font-size: 0.82rem;">
                                                        <i class="fas fa-boxes mr-1"></i> <?= number_format($f['jumlah_innerbox'], 0) ?> <?= htmlspecialchars($f['satuan_innerbox'] ?: 'Innerbox') ?> / Paket
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary px-2 py-1 font-weight-bold" style="font-size: 0.82rem;">
                                                        <i class="fas fa-box mr-1"></i> Kemasan Standar (Langsung)
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= htmlspecialchars($f['satuan_paket']) ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-info px-2 py-1"><?= $f['total_komponen'] ?> Komponen</span>
                                            </td>
                                            <td class="text-muted small"><?= htmlspecialchars($f['keterangan'] ?: '-') ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-formula" data-id="<?= $f['id_formula'] ?>">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- /.content-wrapper -->
</div>
<!-- /.wrapper -->

<!-- Modal Form Formula -->
<div class="modal fade" id="modalFormula" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title font-weight-bold" id="modalFormulaTitle"><i class="fas fa-flask mr-2"></i>Formula Paket Bundling</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formFormulaModal">
                <input type="hidden" name="id_formula" id="formula_id" value="">
                <div class="modal-body p-4">
                    <!-- Baris Informasi Dasar Paket -->
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted">Kode Paket <span class="text-danger">*</span></label>
                                <input type="text" name="kode_paket" id="f_kode_paket" class="form-control" placeholder="Contoh: PKT-JITU-01" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted">Nama Paket Bundling <span class="text-danger">*</span></label>
                                <input type="text" name="nama_paket" id="f_nama_paket" class="form-control font-weight-bold" placeholder="Contoh: Paket Jitu" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted">Satuan Paket</label>
                                <input type="text" name="satuan_paket" id="f_satuan_paket" class="form-control" value="Box">
                            </div>
                        </div>
                    </div>

                    <!-- Panel Konfigurasi Kemasan Innerbox (Header Paket) -->
                    <div class="card border border-primary-subtle bg-light mb-3" style="border-radius: 8px; border-left: 4px solid #2563eb !important;">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="f_is_innerbox" name="is_innerbox" value="1" checked>
                                        <label class="custom-control-label font-weight-bold text-dark" for="f_is_innerbox" style="cursor: pointer;">
                                            <i class="fas fa-boxes text-warning mr-1"></i> Paket Menggunakan Kemasan Innerbox
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1" id="f_innerbox_desc">
                                        1 Paket (Master Box) berisi beberapa Innerbox dengan isi beragam barang penyusun.
                                    </small>
                                </div>
                                <div class="col-md-7 wrap-innerbox-header-config">
                                    <div class="row align-items-center">
                                        <div class="col-sm-6">
                                            <label class="small font-weight-bold text-dark mb-1">
                                                Jumlah Innerbox per 1 Paket: <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="any" min="1" name="jumlah_innerbox" id="f_jumlah_innerbox" class="form-control font-weight-bold text-center text-primary" value="20" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text font-weight-bold" id="f_label_satuan_innerbox">Innerbox</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="small font-weight-bold text-muted mb-1">Sebutan Kemasan Box:</label>
                                            <input type="text" name="satuan_innerbox" id="f_satuan_innerbox" class="form-control form-control-sm" value="Innerbox" placeholder="Innerbox / Box Kecil">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel Biaya Kemasan & Modal Printilan Dinamis (Repeater) -->
                    <div class="card border mb-3" style="border-radius: 8px; border-left: 4px solid #8b5cf6 !important; background: #faf5ff;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small font-weight-bold text-dark" style="color: #6d28d9 !important;">
                                    <i class="fas fa-pallet mr-1"></i> Estimasi Biaya Kemasan & Printilan (Opsional)
                                </span>
                                <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold" id="btnAddFormulaKemasanRow">
                                    <i class="fas fa-plus mr-1"></i> Tambah Biaya / Printilan
                                </button>
                            </div>
                            <div class="table-responsive mb-2">
                                <table class="table table-bordered table-sm mb-0 bg-white" id="tblFormulaKemasan">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th style="width: 58%;">Nama Biaya Kemasan / Printilan</th>
                                            <th style="width: 32%;" class="text-right">Biaya per 1 Paket (Rp)</th>
                                            <th style="width: 10%; text-align: center;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="formulaKemasanBody">
                                        <!-- Baris dinamis via JS -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light font-weight-bold">
                                            <td class="text-right small text-muted text-uppercase align-middle">Total Kemasan / 1 Paket:</td>
                                            <td class="text-right" style="color: #6d28d9; font-size: 0.95rem;" id="f_lbl_total_kemasan">
                                                Rp 0,00
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <small class="text-muted d-block">
                                <i class="fas fa-info-circle mr-1"></i> Masukkan biaya kardus innerbox, master outerbox, stiker hologram, lakban segel, dll per 1 paket. Sisakan 1 kolom atau tambah sesuai kebutuhan.
                            </small>
                        </div>
                    </div>

                    <!-- Judul Komposisi & Tombol Tambah Barang -->
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                        <div>
                            <label class="small font-weight-bold text-dark m-0" id="labelKomposisiTitle">
                                <i class="fas fa-layer-group text-primary mr-1"></i> Isi Barang dalam 1 Innerbox:
                            </label>
                            <small class="text-muted d-block" id="labelKomposisiSubtitle">
                                Masukkan rincian barang penyusun dalam 1 Innerbox. Sistem otomatis menghitung total isi 1 Paket.
                            </small>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary font-weight-bold shadow-sm px-3" id="btnAddFormulaItem">
                            <i class="fas fa-plus mr-1"></i> Tambah Barang
                        </button>
                    </div>

                    <!-- Tabel Komponen / Barang dalam 1 Innerbox -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tableFormulaItems" style="font-size: 0.92rem;">
                            <thead style="background: #f1f5f9; color: #334155;">
                                <tr>
                                    <th style="width: 40%;">Barang Komponen</th>
                                    <th style="width: 20%;" class="text-center" id="thQtyKomponen">Isi dalam 1 Innerbox</th>
                                    <th style="width: 12%;" class="text-center">Satuan Stok</th>
                                    <th style="width: 20%;" class="text-center bg-primary text-white" id="thTotalPaket">Total Isi / 1 Paket</th>
                                    <th style="width: 8%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="formulaItemsBody">
                                <!-- Baris Komponen Diisi JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Card Live Pratinjau Formula (Visual Hierarchy Preview) -->
                    <div class="alert alert-light border shadow-sm mt-3 mb-0" id="cardFormulaSummary" style="border-radius: 8px;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle text-primary fa-2x mr-3"></i>
                            <div class="w-100">
                                <strong class="text-dark d-block" id="summaryFormulaHeader">Struktur & Total Isi 1 Paket:</strong>
                                <div class="small mt-1 text-muted" id="summaryFormulaList">
                                    Tambahkan barang komponen untuk melihat kalkulasi total isi paket.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3 mb-0">
                        <label class="small font-weight-bold text-muted">Keterangan Formula</label>
                        <textarea name="keterangan" id="f_keterangan" class="form-control" rows="2" placeholder="Catatan mengenai formula paket ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Formula</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pencarian Barang Stok Batch -->
<div class="modal fade" id="modalSearchBarangFormula" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-boxes mr-2"></i> Pilih Komponen dari Stok Batch (tberp_stock_batch)
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group mb-3">
                    <input type="text" id="searchBarangFormulaInput" class="form-control" placeholder="Cari nama barang atau kode barang...">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="btnDoSearchFormula">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 380px;">
                    <table class="table table-hover table-sm" id="tableSearchResultFormula">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 20%;">Kode Barang</th>
                                <th style="width: 40%;">Nama Barang</th>
                                <th style="width: 15%;" class="text-right">Stok Batch</th>
                                <th style="width: 10%;" class="text-center">Satuan</th>
                                <th style="width: 15%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="text-center text-muted py-4">Memuat data barang dari stok batch...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-1 bg-light d-flex justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-info-circle mr-1 text-primary"></i> Data terintegrasi langsung dari <code>tberp_stock_batch</code>.
                </small>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let fItemIndex = 0;
let activeFormulaRow = null;

$(document).ready(function() {
    // Tombol Tambah Formula Baru
    // Tombol Tambah Formula Baru
    $('#btnTambahFormula').click(function() {
        $('#formula_id').val('');
        $('#f_kode_paket').val('');
        $('#f_nama_paket').val('');
        $('#f_satuan_paket').val('Box');
        $('#f_is_innerbox').prop('checked', true);
        $('#f_jumlah_innerbox').val(20);
        $('#f_satuan_innerbox').val('Innerbox');
        $('#f_keterangan').val('');
        $('#formulaItemsBody').empty();
        $('#modalFormulaTitle').html('<i class="fas fa-plus-circle mr-2"></i> Tambah Formula Paket Baru');
        updateInnerboxUIMode();
        renderFormulaKemasanItems([{nama: '', nominal: 0}]);
        addFormulaRow('', '', 1, 'Pcs');
        $('#modalFormula').modal('show');
    });

    // Tambah Baris Barang
    $('#btnAddFormulaItem').click(function() {
        addFormulaRow('', '', 1, 'Pcs');
    });

    // Hapus Baris Barang
    $(document).on('click', '.btn-del-frow', function() {
        $(this).closest('tr').remove();
        recalcAllFormulaRows();
    });

    // Toggle Checkbox Innerbox Header
    $('#f_is_innerbox').on('change', function() {
        updateInnerboxUIMode();
        recalcAllFormulaRows();
        recalcFormulaPackagingCost();
    });

    // Event input jumlah innerbox
    $('#f_jumlah_innerbox').on('input change', function() {
        let val = parseFloat($(this).val()) || 0;
        if (val < 1) $(this).val(1);
        recalcAllFormulaRows();
    });

    // Event nama satuan innerbox
    $('#f_satuan_innerbox').on('input change', function() {
        let sat = $(this).val().trim() || 'Innerbox';
        $('#f_label_satuan_innerbox').text(sat);
        recalcAllFormulaRows();
    });

    // Event Biaya Kemasan Dinamis
    $('#btnAddFormulaKemasanRow').click(function() {
        addFormulaKemasanRow('', 0);
    });

    $(document).on('click', '.btn-del-formula-kemasan', function() {
        let totalRows = $('#formulaKemasanBody .formula-kemasan-row').length;
        let $row = $(this).closest('.formula-kemasan-row');
        if (totalRows > 1) {
            $row.remove();
        } else {
            $row.find('.inp-f-nama-kemasan').val('');
            $row.find('.inp-f-nominal-kemasan').val(0);
        }
        recalcFormulaPackagingCost();
    });

    $(document).on('input change', '.inp-f-nominal-kemasan', function() {
        recalcFormulaPackagingCost();
    });

    // Event input isi dalam innerbox per baris
    $(document).on('input change', '.row-isi-inbox', function() {
        let row = $(this).closest('tr');
        recalcFormulaRow(row);
        updateSummaryPreview();
    });

    // Event input satuan barang
    $(document).on('input change', '.row-satuan', function() {
        let row = $(this).closest('tr');
        row.find('.row-satuan-label').text($(this).val() || 'Pcs');
        updateSummaryPreview();
    });

    // Event nama paket
    $('#f_nama_paket').on('input change', function() {
        updateSummaryPreview();
    });

    // Modal Pencarian Barang
    $(document).on('click', '.btn-search-formula-item, .row-kd', function() {
        activeFormulaRow = $(this).closest('tr');
        $('#searchBarangFormulaInput').val('');
        loadBarangSearchFormula('');
        $('#modalSearchBarangFormula').modal('show');
    });

    $('#btnDoSearchFormula').click(function() {
        loadBarangSearchFormula($('#searchBarangFormulaInput').val());
    });

    $('#searchBarangFormulaInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            loadBarangSearchFormula($(this).val());
        }
    });

    $('#modalSearchBarangFormula').on('hidden.bs.modal', function() {
        if ($('#modalFormula').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    // Pilih Barang dari hasil search modal
    $(document).on('click', '.btn-pilih-barang-formula', function() {
        let kd = $(this).data('kd');
        let nama = $(this).data('nama');
        let sat = $(this).data('sat') || 'Pcs';

        if (activeFormulaRow) {
            activeFormulaRow.find('.row-kd').val(kd);
            activeFormulaRow.find('.row-nama').val(nama);
            activeFormulaRow.find('.row-satuan').val(sat);
            activeFormulaRow.find('.row-satuan-label').text(sat);
            activeFormulaRow.find('.row-isi-inbox').focus().select();
            recalcFormulaRow(activeFormulaRow);
            updateSummaryPreview();
        }

        $('#modalSearchBarangFormula').modal('hide');
    });

    // Edit Formula (Buka Data Tersimpan)
    $('.btn-edit-formula').click(function() {
        let id = $(this).data('id');
        $.ajax({
            url: '<?= site_url("purchasing/bundling/formula/detail_ajax") ?>',
            data: { id_formula: id },
            dataType: 'json',
            success: function(res) {
                if (res.status && res.data) {
                    let f = res.data;
                    $('#formula_id').val(f.id_formula);
                    $('#f_kode_paket').val(f.kode_paket);
                    $('#f_nama_paket').val(f.nama_paket);
                    $('#f_satuan_paket').val(f.satuan_paket || 'Box');
                    $('#f_biaya_innerbox').val(f.biaya_innerbox || 0);
                    $('#f_biaya_outerbox').val(f.biaya_outerbox || 0);
                    $('#f_biaya_kemasan_lain').val(f.biaya_kemasan_lain || 0);
                    $('#f_keterangan_biaya_kemasan').val(f.keterangan_biaya_kemasan || '');
                    $('#f_keterangan').val(f.keterangan || '');

                    let isInbox = (parseInt(f.is_innerbox) === 1 || f.is_innerbox === true);
                    let jmlInbox = (f.jumlah_innerbox && parseFloat(f.jumlah_innerbox) > 0) ? parseFloat(f.jumlah_innerbox) : 20;
                    let satInbox = f.satuan_innerbox || 'Innerbox';

                    $('#f_is_innerbox').prop('checked', isInbox);
                    $('#f_jumlah_innerbox').val(jmlInbox);
                    $('#f_satuan_innerbox').val(satInbox);
                    $('#f_label_satuan_innerbox').text(satInbox);

                    updateInnerboxUIMode();
                    renderFormulaKemasanItems(f.kemasan_items || []);
                    $('#formulaItemsBody').empty();

                    if (f.details && f.details.length > 0) {
                        f.details.forEach(function(d) {
                            let isiInbox = parseFloat(d.isi_per_innerbox) || 0;
                            let qtyKomponen = parseFloat(d.qty_komponen) || 1;
                            if (isInbox && isiInbox <= 0 && jmlInbox > 0) {
                                isiInbox = qtyKomponen / jmlInbox;
                            } else if (!isInbox) {
                                isiInbox = qtyKomponen;
                            }
                            addFormulaRow(
                                d.kode_barang_komponen, 
                                d.nama_barang_komponen, 
                                isiInbox, 
                                d.satuan
                            );
                        });
                    } else {
                        addFormulaRow('', '', 1, 'Pcs');
                    }

                    $('#modalFormulaTitle').html('<i class="fas fa-edit mr-2"></i> Edit Formula Paket');
                    recalcAllFormulaRows();
                    $('#modalFormula').modal('show');
                }
            }
        });
    });

    // Simpan Form Formula (Submit AJAX)
    $('#formFormulaModal').submit(function(e) {
        e.preventDefault();

        let validRows = 0;
        $('#formulaItemsBody tr').each(function() {
            let kd = $(this).find('.row-kd').val();
            let qty = parseFloat($(this).find('.row-isi-inbox').val()) || 0;
            if (kd && kd.trim() !== '' && qty > 0) {
                validRows++;
            }
        });

        if (validRows === 0) {
            Swal.fire('Peringatan', 'Minimal 1 barang komponen isi paket harus dipilih dengan kuantitas > 0', 'warning');
            return;
        }

        Swal.showLoading();
        $.ajax({
            url: '<?= site_url("purchasing/bundling/formula/save") ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.msg, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.msg || 'Gagal menyimpan formula', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error, xhr.responseText);
                let errMsg = 'Terjadi kesalahan sistem saat menyimpan formula.';
                if (xhr.status === 401 || (xhr.responseJSON && xhr.responseJSON.auth_timeout)) {
                    Swal.fire('Sesi Berakhir', 'Sesi login Anda telah berakhir. Silakan login kembali.', 'warning')
                        .then(() => location.href = '<?= site_url("auth") ?>');
                    return;
                }
                if (xhr.responseJSON && xhr.responseJSON.msg) {
                    errMsg = xhr.responseJSON.msg;
                }
                Swal.fire('Gagal Menyimpan', errMsg, 'error');
            }
        });
    });
});

// Mengatur tampilan UI form tergantung opsi innerbox aktif/tidak
function updateInnerboxUIMode() {
    let isInbox = $('#f_is_innerbox').is(':checked');
    if (isInbox) {
        $('.wrap-innerbox-header-config').show();
        $('.wrap-innerbox-cost').show();
        $('#f_innerbox_desc').text('1 Paket (Master Box) berisi beberapa Innerbox dengan isi seragam.');
        $('#labelKomposisiTitle').html('<i class="fas fa-layer-group text-primary mr-1"></i> Isi Barang dalam 1 Innerbox:');
        $('#labelKomposisiSubtitle').text('Tentukan komposisi barang penyusun dalam 1 Innerbox. Total isi 1 Paket akan dikalikan otomatis dengan jumlah Innerbox.');
        $('#thQtyKomponen').text('Isi dalam 1 Innerbox');
        $('#thTotalPaket').show();
        $('.col-total-paket').show();
    } else {
        $('.wrap-innerbox-header-config').hide();
        $('.wrap-innerbox-cost').hide();
        $('#f_innerbox_desc').text('Kemasan Standar: Barang komponen langsung dimasukkan ke dalam 1 Paket.');
        $('#labelKomposisiTitle').html('<i class="fas fa-layer-group text-primary mr-1"></i> Isi Barang per 1 Paket:');
        $('#labelKomposisiSubtitle').text('Tentukan jumlah masing-masing barang per 1 Paket langsung.');
        $('#thQtyKomponen').text('Isi per 1 Paket');
        $('#thTotalPaket').hide();
        $('.col-total-paket').hide();
    }
}

// Helper baris biaya kemasan dinamis pada formula
let fKemasanIndex = 0;
function createFormulaKemasanRowHtml(index, nama, nominal) {
    nama = nama || '';
    nominal = (nominal !== undefined && nominal !== null && nominal !== '') ? nominal : 0;
    return `
        <tr class="formula-kemasan-row" data-index="${index}">
            <td>
                <input type="text" name="kemasan_items[${index}][nama]" class="form-control form-control-sm inp-f-nama-kemasan font-weight-bold" placeholder="Contoh: Kardus Innerbox / Outer Box / Hologram" value="${escapeHtml(nama)}">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                    <input type="number" step="any" min="0" name="kemasan_items[${index}][nominal]" class="form-control form-control-sm text-right font-weight-bold inp-f-nominal-kemasan" placeholder="0" value="${nominal}">
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-xs btn-outline-danger btn-del-formula-kemasan" title="Hapus baris kemasan">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    `;
}

function addFormulaKemasanRow(nama, nominal) {
    let html = createFormulaKemasanRowHtml(fKemasanIndex, nama, nominal);
    $('#formulaKemasanBody').append(html);
    fKemasanIndex++;
    recalcFormulaPackagingCost();
}

function renderFormulaKemasanItems(items) {
    $('#formulaKemasanBody').empty();
    fKemasanIndex = 0;
    let list = Array.isArray(items) && items.length > 0 ? items : [{nama: '', nominal: 0}];
    list.forEach(function(it) {
        addFormulaKemasanRow(it.nama, it.nominal);
    });
    recalcFormulaPackagingCost();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Menghitung live total biaya kemasan pada modal formula
function recalcFormulaPackagingCost() {
    let totalKemasan = 0;
    $('#formulaKemasanBody .formula-kemasan-row').each(function() {
        let nom = parseFloat($(this).find('.inp-f-nominal-kemasan').val()) || 0;
        totalKemasan += nom;
    });
    $('#f_lbl_total_kemasan').text('Rp ' + Number(totalKemasan).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
}

// Menambahkan baris barang ke tabel formula
function addFormulaRow(kd, nama, isiInbox, sat) {
    isiInbox = (isiInbox !== undefined && isiInbox !== null && parseFloat(isiInbox) > 0) ? parseFloat(isiInbox) : 1;
    sat = sat || 'Pcs';

    let html = `
        <tr class="fitem-row">
            <td>
                <div class="input-group input-group-sm mb-1">
                    <input type="text" name="komponen[${fItemIndex}][kode_barang_komponen]" 
                           class="form-control font-weight-bold row-kd" 
                           value="${kd}" 
                           placeholder="Klik untuk Cari Barang..." 
                           required readonly style="background-color: #f8fafc; cursor: pointer;" title="Klik untuk mencari barang dari stok batch">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary btn-search-formula-item" title="Cari dari Stok Batch">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                    </div>
                </div>
                <input type="text" name="komponen[${fItemIndex}][nama_barang_komponen]" 
                       class="form-control form-control-sm row-nama" 
                       value="${nama}" 
                       placeholder="Nama Barang Komponen" 
                       readonly style="background-color: #f1f5f9; color: #1e293b; font-weight: 500;">
            </td>
            <td class="text-center align-middle">
                <div class="input-group input-group-sm mx-auto" style="max-width: 140px;">
                    <input type="number" step="any" min="0.001" 
                           name="komponen[${fItemIndex}][isi_per_innerbox]" 
                           class="form-control text-center font-weight-bold text-primary row-isi-inbox" 
                           value="${isiInbox}" required>
                    <div class="input-group-append">
                        <span class="input-group-text px-2 small row-satuan-label">${sat}</span>
                    </div>
                </div>
            </td>
            <td class="align-middle">
                <input type="text" name="komponen[${fItemIndex}][satuan]" 
                       class="form-control form-control-sm text-center row-satuan font-weight-bold" 
                       value="${sat}">
            </td>
            <td class="text-center align-middle bg-light col-total-paket">
                <div class="font-weight-bold text-success row-total-display" style="font-size: 1.05rem;">
                    ${isiInbox} ${sat}
                </div>
                <small class="text-muted row-perhitungan-hint"></small>
                <input type="hidden" name="komponen[${fItemIndex}][qty_komponen]" class="row-qty-total" value="${isiInbox}">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-link text-danger btn-del-frow" title="Hapus Item">
                    <i class="fas fa-trash fa-lg"></i>
                </button>
            </td>
        </tr>
    `;
    $('#formulaItemsBody').append(html);
    fItemIndex++;
    let newRow = $('#formulaItemsBody tr:last');
    recalcFormulaRow(newRow);
    updateSummaryPreview();
}

// Menghitung kuantitas total per baris
function recalcFormulaRow(row) {
    let isInbox = $('#f_is_innerbox').is(':checked');
    let jmlInbox = isInbox ? (parseFloat($('#f_jumlah_innerbox').val()) || 1) : 1;
    let isi = parseFloat(row.find('.row-isi-inbox').val()) || 0;
    let sat = row.find('.row-satuan').val() || 'Pcs';
    let satInbox = $('#f_satuan_innerbox').val().trim() || 'Innerbox';

    row.find('.row-satuan-label').text(sat);

    if (isInbox) {
        let total = jmlInbox * isi;
        row.find('.row-qty-total').val(total);
        row.find('.row-total-display').html(`<strong>${total.toLocaleString('id-ID')} ${sat}</strong>`);
        row.find('.row-perhitungan-hint').text(`${jmlInbox} ${satInbox} × ${isi} ${sat}`);
        row.find('.col-total-paket').show();
    } else {
        row.find('.row-qty-total').val(isi);
        row.find('.row-total-display').html(`<strong>${isi.toLocaleString('id-ID')} ${sat}</strong>`);
        row.find('.row-perhitungan-hint').text(`Langsung per paket`);
        row.find('.col-total-paket').hide();
    }
}

// Menghitung ulang semua baris saat jumlah innerbox berubah
function recalcAllFormulaRows() {
    $('#formulaItemsBody tr').each(function() {
        recalcFormulaRow($(this));
    });
    updateSummaryPreview();
}

// Memperbarui live preview ringkasan struktur formula
function updateSummaryPreview() {
    let namaPaket = $('#f_nama_paket').val().trim() || 'Nama Paket';
    let isInbox = $('#f_is_innerbox').is(':checked');
    let jmlInbox = isInbox ? (parseFloat($('#f_jumlah_innerbox').val()) || 1) : 1;
    let satInbox = $('#f_satuan_innerbox').val().trim() || 'Innerbox';

    let items = [];
    let totals = [];

    $('#formulaItemsBody tr').each(function() {
        let nama = $(this).find('.row-nama').val().trim() || $(this).find('.row-kd').val().trim();
        let isi = parseFloat($(this).find('.row-isi-inbox').val()) || 0;
        let sat = $(this).find('.row-satuan').val() || 'Pcs';
        let total = parseFloat($(this).find('.row-qty-total').val()) || 0;

        if (nama !== '') {
            items.push(`<strong>${nama}</strong> (${isi} ${sat})`);
            totals.push(`<span class="badge badge-success px-2 py-1 mr-1">${nama}: <strong>${total.toLocaleString('id-ID')} ${sat}</strong></span>`);
        }
    });

    if (items.length === 0) {
        $('#summaryFormulaHeader').html(`<i class="fas fa-box text-primary mr-1"></i> Struktur Formula: <strong>${namaPaket}</strong>`);
        $('#summaryFormulaList').html('Belum ada barang komponen yang dipilih.');
        return;
    }

    if (isInbox) {
        $('#summaryFormulaHeader').html(
            `<i class="fas fa-boxes text-warning mr-1"></i> Struktur Formula: <strong>1 Paket [${namaPaket}]</strong> = <strong>${jmlInbox} ${satInbox}</strong> (@ ${items.join(' + ')})`
        );
        $('#summaryFormulaList').html(`
            <div class="d-flex align-items-center flex-wrap mt-2">
                <span class="mr-2 font-weight-bold text-dark"><i class="fas fa-equals mr-1"></i> Total Isi per 1 Paket:</span>
                ${totals.join(' ')}
            </div>
        `);
    } else {
        $('#summaryFormulaHeader').html(
            `<i class="fas fa-box text-primary mr-1"></i> Struktur Formula: <strong>1 Paket [${namaPaket}]</strong> (Kemasan Langsung)`
        );
        $('#summaryFormulaList').html(`
            <div class="d-flex align-items-center flex-wrap mt-2">
                <span class="mr-2 font-weight-bold text-dark"><i class="fas fa-equals mr-1"></i> Isi Komponen per 1 Paket:</span>
                ${totals.join(' ')}
            </div>
        `);
    }
}

// Pencarian Barang di Stok Batch AJAX
function loadBarangSearchFormula(query) {
    $('#tableSearchResultFormula tbody').html('<tr><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i> Mencari barang di tberp_stock_batch...</td></tr>');
    $.ajax({
        url: '<?= site_url("purchasing/bundling/ajax_search_barang") ?>',
        data: { q: query },
        dataType: 'json',
        success: function(items) {
            let html = '';
            if (!items || items.length === 0) {
                html = '<tr><td colspan="5" class="text-center text-muted py-3">Barang tidak ditemukan di tberp_stock_batch</td></tr>';
            } else {
                items.forEach(function(b) {
                    let totalStok = parseFloat(b.total_stok) || 0;
                    let badgeStok = totalStok > 0 
                        ? `<span class="badge badge-success font-weight-bold px-2 py-1">${totalStok.toLocaleString('id-ID')}</span>`
                        : `<span class="badge badge-secondary px-2 py-1">0</span>`;

                    html += `
                        <tr>
                            <td class="font-weight-bold text-primary align-middle">${b.kode_barang}</td>
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">${b.nama_barang}</div>
                                <small class="text-muted">${b.jml_lot ? b.jml_lot + ' Lot terdaftar' : '-'}</small>
                            </td>
                            <td class="text-right align-middle">${badgeStok}</td>
                            <td class="text-center align-middle">${b.satuan || 'Pcs'}</td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-sm btn-success px-3 font-weight-bold btn-pilih-barang-formula"
                                        data-kd="${b.kode_barang}"
                                        data-nama="${b.nama_barang}"
                                        data-sat="${b.satuan || 'Pcs'}">
                                    <i class="fas fa-check mr-1"></i> Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#tableSearchResultFormula tbody').html(html);
        },
        error: function() {
            $('#tableSearchResultFormula tbody').html('<tr><td colspan="5" class="text-center text-danger py-3">Gagal mengambil data stok batch</td></tr>');
        }
    });
}
</script>
