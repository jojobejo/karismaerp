<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-file-invoice text-primary mr-2"></i> Buat Request Paket Bundling
                    </h1>
                    <p class="text-muted mb-0 small">Tentukan komposisi isi 1 paket dan jumlah target paket yang akan dirakit oleh Logistik</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= site_url('purchasing/bundling/request') ?>" class="btn btn-outline-secondary font-weight-bold shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form id="formRequestBundling" method="post" action="<?= site_url('purchasing/bundling/request/save') ?>">
                <div class="row">
                    <!-- Kolom Kiri: Header Request -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                                <h6 class="font-weight-bold text-dark m-0"><i class="fas fa-info-circle text-primary mr-2"></i>Informasi Request</h6>
                            </div>
                            <div class="card-body pt-0">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Nomor Request</label>
                                    <input type="text" name="no_request" class="form-control font-weight-bold bg-light" value="<?= htmlspecialchars($no_request) ?>" readonly>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Tanggal Request <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_request" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Pilih dari Master Formula (Opsional)</label>
                                    <select id="selectFormula" class="form-control select2">
                                        <option value="">-- Ketik Bebas / Pilih Formula Tersimpan --</option>
                                        <?php foreach ($formulas as $f): ?>
                                            <option value="<?= $f['id_formula'] ?>" data-kode="<?= htmlspecialchars($f['kode_paket']) ?>" data-nama="<?= htmlspecialchars($f['nama_paket']) ?>" data-satuan="<?= htmlspecialchars($f['satuan_paket']) ?>">
                                                <?= htmlspecialchars($f['nama_paket']) ?> (<?= htmlspecialchars($f['kode_paket']) ?>) - <?= $f['total_komponen'] ?> Komponen
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Memilih formula akan otomatis mengisi rincian komponen di tabel samping.</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Nama Paket Bundling <span class="text-danger">*</span></label>
                                    <input type="text" id="nama_paket" name="nama_paket" class="form-control font-weight-bold" placeholder="Contoh: Paket Jitu" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Kode Paket Bundling <span class="text-danger">*</span></label>
                                    <input type="text" id="kode_paket" name="kode_paket" class="form-control" placeholder="Contoh: PKT-JITU-01" required>
                                </div>

                                <div class="row">
                                    <div class="col-8">
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-muted">Jumlah Paket Diminta <span class="text-danger">*</span></label>
                                            <input type="number" step="any" min="1" id="qty_request" name="qty_request" class="form-control form-control-lg font-weight-bold text-primary" placeholder="0" value="250" required>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-muted">Satuan</label>
                                            <input type="text" id="satuan_paket" name="satuan" class="form-control form-control-lg" value="Box">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Gudang Tujuan Perakitan</label>
                                    <select name="id_gudang_tujuan" class="form-control">
                                        <?php foreach ($gudangs as $g): ?>
                                            <option value="<?= $g['id_gudang'] ?>" <?= (stripos($g['nama_gudang'], 'Bundling') !== false || $g['id_gudang'] == 12) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($g['nama_gudang']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Gudang tempat fisik paket bundling akan dirakit dan disimpan.</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Gudang Asal Komponen (Gudang Induk)</label>
                                    <select name="id_gudang_asal" class="form-control">
                                        <?php foreach ($gudangs as $g): ?>
                                            <option value="<?= $g['id_gudang'] ?>" <?= ($g['id_gudang'] == 2) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($g['nama_gudang']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Gudang sumber tempat bahan komponen akan diambil/dimutasi.</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Catatan / Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan khusus dari Direktur / Purchasing..."></textarea>
                                </div>

                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="simpan_sebagai_formula" name="simpan_sebagai_formula" value="1" checked>
                                    <label class="custom-control-label small text-muted" for="simpan_sebagai_formula">Simpan komposisi ini sebagai Master Formula baru untuk digunakan kembali</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Rincian Komponen Paket (Formula Komposisi) -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark m-0"><i class="fas fa-layer-group text-primary mr-2"></i>Komposisi Isi Paket & Kebutuhan Otomatis</h6>
                                    <small class="text-muted">Tentukan isi 1 paket. Total kebutuhan barang akan dihitung otomatis oleh sistem.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold" id="btnAddRow">
                                    <i class="fas fa-plus mr-1"></i> Tambah Komponen
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="tableKomponen" style="font-size: 0.92rem;">
                                        <thead style="background: #f1f5f9; color: #334155;">
                                            <tr>
                                                <th style="width: 42%;">Komponen Barang</th>
                                                <th style="width: 18%;" class="text-center">Isi per 1 Paket</th>
                                                <th style="width: 14%;" class="text-center">Satuan</th>
                                                <th style="width: 20%;" class="text-center bg-primary text-white">Total Kebutuhan</th>
                                                <th style="width: 6%;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="komponenList">
                                            <!-- Baris default awal (Contoh: Spontas, Round Up, Kaos Jitu) -->
                                            <tr class="item-row">
                                                <td>
                                                    <input type="hidden" name="komponen[0][kode_barang_komponen]" class="row-kd" value="SPON-1LTR">
                                                    <input type="text" name="komponen[0][nama_barang_komponen]" class="form-control form-control-sm row-nama font-weight-bold" value="Spontas 1 Ltr" placeholder="Pilih/ketik barang..." required>
                                                    <small class="text-muted row-kd-text">Kode: SPON-1LTR</small>
                                                </td>
                                                <td>
                                                    <input type="number" step="any" min="0.001" name="komponen[0][qty_per_paket]" class="form-control form-control-sm text-center row-qty font-weight-bold" value="1" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="komponen[0][satuan]" class="form-control form-control-sm text-center row-satuan" value="Ltr">
                                                </td>
                                                <td class="text-center align-middle font-weight-bold text-primary row-total" style="font-size: 1.05rem;">
                                                    250
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-link text-danger btn-del"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr class="item-row">
                                                <td>
                                                    <input type="hidden" name="komponen[1][kode_barang_komponen]" class="row-kd" value="ROUN-1LTR">
                                                    <input type="text" name="komponen[1][nama_barang_komponen]" class="form-control form-control-sm row-nama font-weight-bold" value="Round Up 1 Ltr" placeholder="Pilih/ketik barang..." required>
                                                    <small class="text-muted row-kd-text">Kode: ROUN-1LTR</small>
                                                </td>
                                                <td>
                                                    <input type="number" step="any" min="0.001" name="komponen[1][qty_per_paket]" class="form-control form-control-sm text-center row-qty font-weight-bold" value="1" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="komponen[1][satuan]" class="form-control form-control-sm text-center row-satuan" value="Ltr">
                                                </td>
                                                <td class="text-center align-middle font-weight-bold text-primary row-total" style="font-size: 1.05rem;">
                                                    250
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-link text-danger btn-del"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr class="item-row">
                                                <td>
                                                    <input type="hidden" name="komponen[2][kode_barang_komponen]" class="row-kd" value="KAOS-JITU">
                                                    <input type="text" name="komponen[2][nama_barang_komponen]" class="form-control form-control-sm row-nama font-weight-bold" value="Kaos Jitu" placeholder="Pilih/ketik barang..." required>
                                                    <small class="text-muted row-kd-text">Kode: KAOS-JITU</small>
                                                </td>
                                                <td>
                                                    <input type="number" step="any" min="0.001" name="komponen[2][qty_per_paket]" class="form-control form-control-sm text-center row-qty font-weight-bold" value="1" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="komponen[2][satuan]" class="form-control form-control-sm text-center row-satuan" value="Pcs">
                                                </td>
                                                <td class="text-center align-middle font-weight-bold text-primary row-total" style="font-size: 1.05rem;">
                                                    250
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-link text-danger btn-del"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3 bg-light border-top">
                                    <div class="row align-items-center">
                                        <div class="col-md-7 text-muted small">
                                            <i class="fas fa-calculator text-primary mr-1"></i>
                                            <strong>Rumus Otomatis:</strong> Total Kebutuhan = Qty Paket Request × Qty Komponen per Paket.
                                            Purchasing tidak perlu menghitung manual.
                                        </div>
                                        <div class="col-md-5 text-right">
                                            <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow">
                                                <i class="fas fa-paper-plane mr-1"></i> Kirim Request ke Logistik
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<!-- Modal Pencarian Barang -->
<div class="modal fade" id="modalSearchBarang" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title font-weight-bold"><i class="fas fa-search mr-2"></i>Pilih Komponen dari Master Barang</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group mb-3">
                    <input type="text" id="searchBarangInput" class="form-control" placeholder="Cari nama barang atau kode barang...">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" id="btnDoSearch"><i class="fas fa-search"></i> Cari</button>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 380px;">
                    <table class="table table-hover table-sm" id="tableSearchResult">
                        <thead class="bg-light">
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="4" class="text-center text-muted py-4">Ketik nama barang untuk mencari</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let rowIndex = 3;
let activeRowTarget = null;

$(document).ready(function() {
    recalculateTotals();

    // Event perubahan Qty Request
    $('#qty_request').on('input change', function() {
        recalculateTotals();
    });

    // Event perubahan Qty Komponen
    $(document).on('input change', '.row-qty', function() {
        recalculateTotals();
    });

    // Event Hapus Baris
    $(document).on('click', '.btn-del', function() {
        if ($('#komponenList tr').length <= 1) {
            Swal.fire('Perhatian', 'Minimal harus ada 1 komponen dalam paket bundling', 'warning');
            return;
        }
        $(this).closest('tr').remove();
        recalculateTotals();
    });

    // Tambah Baris Komponen Baru
    $('#btnAddRow').click(function() {
        let html = `
            <tr class="item-row">
                <td>
                    <input type="hidden" name="komponen[${rowIndex}][kode_barang_komponen]" class="row-kd" value="">
                    <div class="input-group input-group-sm">
                        <input type="text" name="komponen[${rowIndex}][nama_barang_komponen]" class="form-control row-nama font-weight-bold" placeholder="Pilih/ketik barang..." required>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary btn-lookup"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    <small class="text-muted row-kd-text">Kode: -</small>
                </td>
                <td>
                    <input type="number" step="any" min="0.001" name="komponen[${rowIndex}][qty_per_paket]" class="form-control form-control-sm text-center row-qty font-weight-bold" value="1" required>
                </td>
                <td>
                    <input type="text" name="komponen[${rowIndex}][satuan]" class="form-control form-control-sm text-center row-satuan" value="Pcs">
                </td>
                <td class="text-center align-middle font-weight-bold text-primary row-total" style="font-size: 1.05rem;">
                    0
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-link text-danger btn-del"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
        $('#komponenList').append(html);
        rowIndex++;
        recalculateTotals();
    });

    // Lookup Barang
    $(document).on('click', '.btn-lookup, .row-nama', function() {
        activeRowTarget = $(this).closest('tr');
        $('#modalSearchBarang').modal('show');
        loadBarangSearch('');
    });

    $('#searchBarangInput').on('keyup', function(e) {
        if (e.keyCode === 13) {
            loadBarangSearch($(this).val());
        }
    });
    $('#btnDoSearch').click(function() {
        loadBarangSearch($('#searchBarangInput').val());
    });

    // Pilih Barang dari Modal
    $(document).on('click', '.btn-select-barang', function() {
        if (!activeRowTarget) return;
        let kd = $(this).data('kd');
        let nama = $(this).data('nama');
        let sat = $(this).data('satuan');

        activeRowTarget.find('.row-kd').val(kd);
        activeRowTarget.find('.row-nama').val(nama);
        activeRowTarget.find('.row-satuan').val(sat || 'Pcs');
        activeRowTarget.find('.row-kd-text').text('Kode: ' + kd);

        $('#modalSearchBarang').modal('hide');
        recalculateTotals();
    });

    // Pilihan Formula Siap Pakai
    $('#selectFormula').change(function() {
        let idFormula = $(this).val();
        if (!idFormula) return;

        let opt = $(this).find(':selected');
        $('#nama_paket').val(opt.data('nama'));
        $('#kode_paket').val(opt.data('kode'));
        if (opt.data('satuan')) $('#satuan_paket').val(opt.data('satuan'));

        $.ajax({
            url: '<?= site_url("purchasing/bundling/formula/detail_ajax") ?>',
            data: { id_formula: idFormula },
            dataType: 'json',
            success: function(res) {
                if (res.status && res.data && res.data.details) {
                    $('#komponenList').empty();
                    rowIndex = 0;
                    res.data.details.forEach(function(d) {
                        let html = `
                            <tr class="item-row">
                                <td>
                                    <input type="hidden" name="komponen[${rowIndex}][kode_barang_komponen]" class="row-kd" value="${d.kode_barang_komponen}">
                                    <input type="text" name="komponen[${rowIndex}][nama_barang_komponen]" class="form-control form-control-sm row-nama font-weight-bold" value="${d.nama_barang_komponen || d.kode_barang_komponen}" required>
                                    <small class="text-muted row-kd-text">Kode: ${d.kode_barang_komponen}</small>
                                </td>
                                <td>
                                    <input type="number" step="any" min="0.001" name="komponen[${rowIndex}][qty_per_paket]" class="form-control form-control-sm text-center row-qty font-weight-bold" value="${parseFloat(d.qty_komponen)}" required>
                                </td>
                                <td>
                                    <input type="text" name="komponen[${rowIndex}][satuan]" class="form-control form-control-sm text-center row-satuan" value="${d.satuan || 'Pcs'}">
                                </td>
                                <td class="text-center align-middle font-weight-bold text-primary row-total" style="font-size: 1.05rem;">
                                    0
                                </td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-link text-danger btn-del"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                        $('#komponenList').append(html);
                        rowIndex++;
                    });
                    recalculateTotals();
                }
            }
        });
    });

    // Form Submit AJAX
    $('#formRequestBundling').submit(function(e) {
        e.preventDefault();

        let qtyReq = parseFloat($('#qty_request').val()) || 0;
        if (qtyReq <= 0) {
            Swal.fire('Peringatan', 'Jumlah request paket harus lebih dari 0', 'warning');
            return;
        }

        Swal.fire({
            title: 'Kirim Request Bundling?',
            text: 'Request ' + qtyReq + ' Box ' + $('#nama_paket').val() + ' akan dikirimkan ke Logistik untuk diproses.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Kirim Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Berhasil!', res.msg, 'success').then(() => {
                                window.location.href = '<?= site_url("purchasing/bundling/request/detail/") ?>' + res.id_request;
                            });
                        } else {
                            Swal.fire('Gagal!', res.msg, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    });
});

function recalculateTotals() {
    let qtyRequest = parseFloat($('#qty_request').val()) || 0;
    $('#komponenList .item-row').each(function() {
        let qtyPerPaket = parseFloat($(this).find('.row-qty').val()) || 0;
        let total = qtyRequest * qtyPerPaket;
        $(this).find('.row-total').text(total.toLocaleString('id-ID'));
    });
}

function loadBarangSearch(query) {
    $.ajax({
        url: '<?= site_url("purchasing/bundling/ajax_search_barang") ?>',
        data: { q: query },
        dataType: 'json',
        success: function(items) {
            let html = '';
            if (items.length === 0) {
                html = '<tr><td colspan="4" class="text-center text-muted py-3">Barang tidak ditemukan</td></tr>';
            } else {
                items.forEach(function(b) {
                    html += `
                        <tr>
                            <td class="font-weight-bold">${b.kode_barang}</td>
                            <td>${b.nama_barang}</td>
                            <td>${b.satuan || 'Pcs'}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary btn-select-barang" data-kd="${b.kode_barang}" data-nama="${b.nama_barang}" data-satuan="${b.satuan || 'Pcs'}">
                                    <i class="fas fa-check"></i> Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#tableSearchResult tbody').html(html);
        }
    });
}
</script>
