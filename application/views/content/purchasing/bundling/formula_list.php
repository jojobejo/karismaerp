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
                                    <th class="py-3 text-center">Satuan</th>
                                    <th class="py-3 text-center">Total Komponen</th>
                                    <th class="py-3">Keterangan</th>
                                    <th class="py-3 text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($formulas)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-flask fa-3x mb-3 text-black-50 d-block"></i>
                                            Belum ada master formula paket bundling tersimpan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($formulas as $f): ?>
                                        <tr>
                                            <td class="px-3 font-weight-bold text-primary"><?= htmlspecialchars($f['kode_paket']) ?></td>
                                            <td class="font-weight-bold text-dark"><?= htmlspecialchars($f['nama_paket']) ?></td>
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title font-weight-bold" id="modalFormulaTitle"><i class="fas fa-flask mr-2"></i>Formula Paket Bundling</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formFormulaModal">
                <input type="hidden" name="id_formula" id="formula_id" value="">
                <div class="modal-body p-4">
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
                                <label class="small font-weight-bold text-muted">Satuan</label>
                                <input type="text" name="satuan_paket" id="f_satuan_paket" class="form-control" value="Box">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                        <label class="small font-weight-bold text-dark m-0">Komposisi Isi untuk 1 Paket:</label>
                        <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold" id="btnAddFormulaItem">
                            <i class="fas fa-plus mr-1"></i> Tambah Item
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="tableFormulaItems">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 50%;">Barang Komponen</th>
                                    <th style="width: 20%;" class="text-center">Isi per Paket</th>
                                    <th style="width: 20%;" class="text-center">Satuan</th>
                                    <th style="width: 10%;" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="formulaItemsBody">
                                <!-- Baris Komponen Diisi JS -->
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mt-3 mb-0">
                        <label class="small font-weight-bold text-muted">Keterangan Formula</label>
                        <textarea name="keterangan" id="f_keterangan" class="form-control" rows="2" placeholder="Catatan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3 font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Formula</button>
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
    $('#btnTambahFormula').click(function() {
        $('#formula_id').val('');
        $('#f_kode_paket').val('');
        $('#f_nama_paket').val('');
        $('#f_satuan_paket').val('Box');
        $('#f_keterangan').val('');
        $('#formulaItemsBody').empty();
        $('#modalFormulaTitle').html('<i class="fas fa-plus-circle mr-2"></i> Tambah Formula Paket Baru');
        addFormulaRow('', '', 1, 'Pcs');
        $('#modalFormula').modal('show');
    });

    $('#btnAddFormulaItem').click(function() {
        addFormulaRow('', '', 1, 'Pcs');
    });

    $(document).on('click', '.btn-del-frow', function() {
        $(this).closest('tr').remove();
    });

    // Buka modal pencarian barang saat klik tombol Cari atau klik input Kode
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

    // Ketika modal pencarian barang ditutup, pastikan modal formula tetap aktif & scrollable
    $('#modalSearchBarangFormula').on('hidden.bs.modal', function() {
        if ($('#modalFormula').hasClass('show')) {
            $('body').addClass('modal-open');
        }
    });

    // Aksi tombol Pilih Barang dari hasil search
    $(document).on('click', '.btn-pilih-barang-formula', function() {
        let kd = $(this).data('kd');
        let nama = $(this).data('nama');
        let sat = $(this).data('sat') || 'Pcs';

        if (activeFormulaRow) {
            activeFormulaRow.find('.row-kd').val(kd);
            activeFormulaRow.find('.row-nama').val(nama);
            activeFormulaRow.find('.row-satuan').val(sat);
            activeFormulaRow.find('.row-qty').focus().select();
        }

        $('#modalSearchBarangFormula').modal('hide');
    });

    // Edit Formula
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
                    $('#f_keterangan').val(f.keterangan || '');
                    $('#formulaItemsBody').empty();

                    if (f.details && f.details.length > 0) {
                        f.details.forEach(function(d) {
                            addFormulaRow(d.kode_barang_komponen, d.nama_barang_komponen, parseFloat(d.qty_komponen), d.satuan);
                        });
                    } else {
                        addFormulaRow('', '', 1, 'Pcs');
                    }

                    $('#modalFormulaTitle').html('<i class="fas fa-edit mr-2"></i> Edit Formula Paket');
                    $('#modalFormula').modal('show');
                }
            }
        });
    });

    $('#formFormulaModal').submit(function(e) {
        e.preventDefault();

        let validRows = 0;
        $('#formulaItemsBody tr').each(function() {
            let kd = $(this).find('.row-kd').val();
            let qty = parseFloat($(this).find('.row-qty').val()) || 0;
            if (kd && kd.trim() !== '' && qty > 0) {
                validRows++;
            }
        });

        if (validRows === 0) {
            Swal.fire('Peringatan', 'Minimal 1 barang komponen isi paket harus dipilih dengan jumlah > 0', 'warning');
            return;
        }

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
                } else if (xhr.responseText && xhr.responseText.length < 200 && !xhr.responseText.includes('<html')) {
                    errMsg = xhr.responseText;
                }
                Swal.fire('Gagal Menyimpan', errMsg, 'error');
            }
        });
    });
});

function addFormulaRow(kd, nama, qty, sat) {
    let html = `
        <tr>
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
            <td class="align-middle">
                <input type="number" step="any" min="0.001" 
                       name="komponen[${fItemIndex}][qty_komponen]" 
                       class="form-control form-control-sm text-center font-weight-bold row-qty" 
                       value="${qty}" required>
            </td>
            <td class="align-middle">
                <input type="text" name="komponen[${fItemIndex}][satuan]" 
                       class="form-control form-control-sm text-center row-satuan" 
                       value="${sat || 'Pcs'}">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-link text-danger btn-del-frow" title="Hapus Item">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#formulaItemsBody').append(html);
    fItemIndex++;
}

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
