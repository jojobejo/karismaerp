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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let fItemIndex = 0;

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
        $.ajax({
            url: '<?= site_url("purchasing/bundling/formula/save") ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    Swal.fire('Berhasil', res.msg, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.msg, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        });
    });
});

function addFormulaRow(kd, nama, qty, sat) {
    let html = `
        <tr>
            <td>
                <input type="text" name="komponen[${fItemIndex}][kode_barang_komponen]" class="form-control form-control-sm mb-1 font-weight-bold" value="${kd}" placeholder="Kode Barang..." required>
                <input type="text" name="komponen[${fItemIndex}][nama_barang_komponen]" class="form-control form-control-sm" value="${nama}" placeholder="Nama Barang...">
            </td>
            <td>
                <input type="number" step="any" min="0.001" name="komponen[${fItemIndex}][qty_komponen]" class="form-control form-control-sm text-center font-weight-bold" value="${qty}" required>
            </td>
            <td>
                <input type="text" name="komponen[${fItemIndex}][satuan]" class="form-control form-control-sm text-center" value="${sat || 'Pcs'}">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-link text-danger btn-del-frow"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `;
    $('#formulaItemsBody').append(html);
    fItemIndex++;
}
</script>
