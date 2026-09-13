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
                                            <input type="number" step="any" min="1" id="qty_request" name="qty_request" class="form-control form-control-lg font-weight-bold text-primary" placeholder="0" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-muted">Satuan</label>
                                            <input type="text" id="satuan_paket" name="satuan" class="form-control form-control-lg" value="Box">
                                        </div>
                                    </div>
                                </div>

                                <!-- Konfigurasi & Pratinjau Kemasan Innerbox Paket -->
                                <input type="hidden" name="is_innerbox" id="req_is_innerbox" value="0">
                                <input type="hidden" name="jumlah_innerbox" id="req_jumlah_innerbox" value="0">
                                <input type="hidden" name="satuan_innerbox" id="req_satuan_innerbox" value="Innerbox">

                                <div id="boxInfoKemasanReq" class="alert alert-warning border-0 p-3 small text-dark mb-3" style="display: none; border-radius: 8px;">
                                    <div class="font-weight-bold mb-1">
                                        <i class="fas fa-boxes text-warning mr-1"></i> Kemasan Paket: <strong><span id="labelJmlInboxReq">20</span> <span id="labelSatInboxReq">Innerbox</span></strong> / Paket
                                    </div>
                                    <div class="text-primary font-weight-bold" id="labelTotalInboxReq">
                                        Total Kebutuhan Kemasan: <strong>0 Box</strong>
                                    </div>
                                </div>

                                <!-- Panel Estimasi Biaya Kemasan & Printilan Dinamis (Repeater) -->
                                <div class="card border border-primary-subtle mb-3" style="border-radius: 8px; background: #faf5ff; border-left: 4px solid #8b5cf6 !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="small font-weight-bold text-dark m-0" style="color: #6d28d9 !important;">
                                                <i class="fas fa-pallet mr-1"></i> Biaya Kemasan & Printilan (Opsional)
                                            </label>
                                            <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold" id="btnAddReqKemasanRow">
                                                <i class="fas fa-plus mr-1"></i> Tambah Biaya / Printilan
                                            </button>
                                        </div>

                                        <div class="table-responsive mb-2">
                                            <table class="table table-bordered table-sm mb-0 bg-white" id="tblReqKemasan">
                                                <thead class="bg-light text-muted small text-uppercase">
                                                    <tr>
                                                        <th style="width: 58%;">Nama Kemasan / Printilan</th>
                                                        <th style="width: 32%;" class="text-right">Biaya / Paket (Rp)</th>
                                                        <th style="width: 10%; text-align: center;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="reqKemasanBody">
                                                    <!-- Baris dinamis via JS -->
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="p-2 rounded bg-white border mt-2 small">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Kemasan / 1 Paket:</span>
                                                <strong style="color: #6d28d9;" id="lbl_req_total_kemasan_1paket">Rp 0,00</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <span class="text-muted">Total Seluruh Request:</span>
                                                <strong class="text-primary" id="lbl_req_total_kemasan_all">Rp 0,00</strong>
                                            </div>
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
                                                <th style="width: 32%;">Komponen Barang</th>
                                                <th style="width: 17%;" class="text-center">Kemasan Innerbox?</th>
                                                <th style="width: 25%;" class="text-center">Isi per 1 Paket</th>
                                                <th style="width: 9%;" class="text-center">Satuan</th>
                                                <th style="width: 12%;" class="text-center bg-primary text-white">Total Kebutuhan</th>
                                                <th style="width: 5%;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="komponenList">
                                            <tr id="emptyRowKomponen">
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="fas fa-layer-group text-secondary fa-2x mb-2 d-block"></i>
                                                    Belum ada komponen barang yang ditambahkan.<br>
                                                    <small>Pilih dari <strong>Master Formula</strong> di sebelah kiri atau klik tombol <strong><i class="fas fa-plus"></i> Tambah Komponen</strong>.</small>
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
<!-- /.content-wrapper -->
</div>
<!-- /.wrapper -->

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
                                <th style="width: 20%;">Kode Barang</th>
                                <th style="width: 40%;">Nama Barang</th>
                                <th style="width: 15%;" class="text-right">Stok Batch</th>
                                <th style="width: 10%;" class="text-center">Satuan</th>
                                <th style="width: 15%;" class="text-center">Aksi</th>
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
let rowIndex = 0;
let activeRowTarget = null;

function checkEmptyRow() {
    if ($('#komponenList tr.item-row').length === 0) {
        if ($('#emptyRowKomponen').length === 0) {
            let emptyHtml = `
                <tr id="emptyRowKomponen">
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-layer-group text-secondary fa-2x mb-2 d-block"></i>
                        Belum ada komponen barang yang ditambahkan.<br>
                        <small>Pilih dari <strong>Master Formula</strong> di sebelah kiri atau klik tombol <strong><i class="fas fa-plus"></i> Tambah Komponen</strong>.</small>
                    </td>
                </tr>
            `;
            $('#komponenList').html(emptyHtml);
        }
    } else {
        $('#emptyRowKomponen').remove();
    }
}

$(document).ready(function() {
    checkEmptyRow();
    renderReqKemasanItems([{nama: '', nominal: 0}]);
    recalculateTotals();

    // Event perubahan Qty Request
    $('#qty_request').on('input change', function() {
        recalculateTotals();
    });

    // Event Tambah Baris Kemasan Dinamis
    $('#btnAddReqKemasanRow').click(function() {
        addReqKemasanRow('', 0);
    });

    // Event Hapus Baris Kemasan Dinamis
    $(document).on('click', '.btn-del-req-kemasan', function() {
        let totalRows = $('#reqKemasanBody .req-kemasan-row').length;
        let $row = $(this).closest('.req-kemasan-row');
        if (totalRows > 1) {
            $row.remove();
        } else {
            $row.find('.inp-req-nama-kemasan').val('');
            $row.find('.inp-req-nominal-kemasan').val(0);
        }
        recalculateTotals();
    });

    $(document).on('input change', '.inp-req-nominal-kemasan', function() {
        recalculateTotals();
    });

    // Event Hapus Baris Komponen
    $(document).on('click', '.btn-del', function() {
        $(this).closest('tr').remove();
        checkEmptyRow();
        recalculateTotals();
    });

    // Tambah Baris Komponen Baru
    $('#btnAddRow').click(function() {
        $('#emptyRowKomponen').remove();
        let html = renderRequestItemRow(rowIndex, '', '', 'Pcs', 0, 1, 12, 1);
        $('#komponenList').append(html);
        rowIndex++;
        recalculateTotals();
    });

    // Toggle checkbox innerbox
    $(document).on('change', '.chk-innerbox-req', function() {
        let row = $(this).closest('tr');
        let isChecked = $(this).is(':checked');
        let label = row.find('label[for="' + $(this).attr('id') + '"]');

        if (isChecked) {
            label.removeClass('text-muted').addClass('text-primary').html('<i class="fas fa-box text-warning mr-1"></i> Pakai Innerbox');
            row.find('.wrap-innerbox-inputs').show();
            row.find('.wrap-regular-qty').hide();

            let qBox = parseFloat(row.find('.row-qty-inbox').val()) || 1;
            let iBox = parseFloat(row.find('.row-isi-inbox').val()) || 12;
            row.find('.row-qty-inbox').val(qBox);
            row.find('.row-isi-inbox').val(iBox);
        } else {
            label.removeClass('text-primary').addClass('text-muted').text('Tanpa Innerbox');
            row.find('.wrap-innerbox-inputs').hide();
            row.find('.wrap-regular-qty').show();
        }
        recalculateTotals();
    });

    // Event input kuantitas & satuan
    $(document).on('input change', '.row-qty-inbox, .row-isi-inbox, .row-qty-regular, .row-satuan', function() {
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
        if (!idFormula) {
            $('#nama_paket').val('');
            $('#kode_paket').val('');
            $('#req_is_innerbox').val(0);
            $('#req_jumlah_innerbox').val(0);
            renderReqKemasanItems([{nama: '', nominal: 0}]);
            $('#boxInfoKemasanReq').hide();
            $('#komponenList').empty();
            checkEmptyRow();
            recalculateTotals();
            return;
        }

        let opt = $(this).find(':selected');
        $('#nama_paket').val(opt.data('nama'));
        $('#kode_paket').val(opt.data('kode'));
        if (opt.data('satuan')) $('#satuan_paket').val(opt.data('satuan'));

        $.ajax({
            url: '<?= site_url("purchasing/bundling/formula/detail_ajax") ?>',
            data: { id_formula: idFormula },
            dataType: 'json',
            success: function(res) {
                if (res.status && res.data) {
                    let f = res.data;
                    let isInbox = (parseInt(f.is_innerbox) === 1 || f.is_innerbox === true);
                    let jmlInbox = parseFloat(f.jumlah_innerbox) || 20;
                    let satInbox = f.satuan_innerbox || 'Innerbox';

                    $('#req_is_innerbox').val(isInbox ? 1 : 0);
                    $('#req_jumlah_innerbox').val(isInbox ? jmlInbox : 0);
                    $('#req_satuan_innerbox').val(satInbox);

                    renderReqKemasanItems(f.kemasan_items || []);

                    if (isInbox && jmlInbox > 0) {
                        $('#labelJmlInboxReq').text(jmlInbox.toLocaleString('id-ID'));
                        $('#labelSatInboxReq').text(satInbox);
                        $('#boxInfoKemasanReq').show();
                    } else {
                        $('#boxInfoKemasanReq').hide();
                    }

                    $('#komponenList').empty();
                    rowIndex = 0;
                    if (f.details && f.details.length > 0) {
                        f.details.forEach(function(d) {
                            let isiInbox = parseFloat(d.isi_per_innerbox) || 0;
                            let qtyKomponen = parseFloat(d.qty_komponen) || 1;
                            if (isInbox && isiInbox <= 0 && jmlInbox > 0) {
                                isiInbox = qtyKomponen / jmlInbox;
                            } else if (!isInbox) {
                                isiInbox = qtyKomponen;
                            }

                            let html = renderRequestItemRow(
                                rowIndex,
                                d.kode_barang_komponen,
                                d.nama_barang_komponen || d.kode_barang_komponen,
                                d.satuan || 'Pcs',
                                isInbox,
                                jmlInbox,
                                isiInbox,
                                qtyKomponen
                            );
                            $('#komponenList').append(html);
                            rowIndex++;
                        });
                    }
                    checkEmptyRow();
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

        let validRows = 0;
        $('#komponenList .item-row').each(function() {
            let kd = $(this).find('.row-kd').val();
            let qty = parseFloat($(this).find('.row-qty-total').val()) || 0;
            if (kd && kd.trim() !== '' && qty > 0) {
                validRows++;
            }
        });

        if (validRows === 0) {
            Swal.fire('Peringatan', 'Minimal 1 barang komponen isi paket harus dipilih dengan jumlah > 0', 'warning');
            return;
        }

        let formElem = this;
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
                    url: $(formElem).attr('action'),
                    type: 'POST',
                    data: $(formElem).serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire('Berhasil!', res.msg, 'success').then(() => {
                                window.location.href = '<?= site_url("purchasing/bundling/request/detail/") ?>' + res.id_request;
                            });
                        } else {
                            Swal.fire('Gagal!', res.msg || 'Gagal menyimpan request.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error, xhr.responseText);
                        let errMsg = 'Terjadi kesalahan sistem saat menyimpan request.';
                        if (xhr.status === 401 || (xhr.responseJSON && xhr.responseJSON.auth_timeout)) {
                            Swal.fire({
                                title: 'Sesi Berakhir',
                                text: 'Sesi login Anda telah berakhir. Silakan login kembali.',
                                icon: 'warning',
                                confirmButtonText: 'Login Kembali'
                            }).then(() => {
                                window.location.href = '<?= site_url("auth") ?>';
                            });
                            return;
                        }

                        if (xhr.responseJSON && xhr.responseJSON.msg) {
                            errMsg = xhr.responseJSON.msg;
                        } else if (xhr.responseText) {
                            try {
                                let parsed = JSON.parse(xhr.responseText);
                                if (parsed.msg) errMsg = parsed.msg;
                            } catch(e) {
                                // Jika ada error string singkat
                                if (xhr.responseText.length < 200 && !xhr.responseText.includes('<html')) {
                                    errMsg = xhr.responseText;
                                }
                            }
                        }
                        Swal.fire('Gagal Menyimpan', errMsg, 'error');
                    }
                });
            }
        });
    });
});

function renderRequestItemRow(rIndex, kd, nama, sat, isInbox, qtyInbox, isiInbox, qtyTotal) {
    isInbox = (parseInt(isInbox) === 1 || isInbox === true);
    qtyInbox = (qtyInbox !== undefined && qtyInbox !== null && parseFloat(qtyInbox) > 0) ? parseFloat(qtyInbox) : 1;
    isiInbox = (isiInbox !== undefined && isiInbox !== null && parseFloat(isiInbox) > 0) ? parseFloat(isiInbox) : 12;
    qtyTotal = (qtyTotal !== undefined && qtyTotal !== null && parseFloat(qtyTotal) > 0) ? parseFloat(qtyTotal) : 1;
    if (isInbox && qtyInbox > 0 && isiInbox > 0) {
        qtyTotal = qtyInbox * isiInbox;
    }
    sat = sat || 'Pcs';

    return `
        <tr class="item-row">
            <td>
                <input type="hidden" name="komponen[${rIndex}][kode_barang_komponen]" class="row-kd" value="${kd}">
                <div class="input-group input-group-sm mb-1">
                    <input type="text" name="komponen[${rIndex}][nama_barang_komponen]" class="form-control form-control-sm row-nama font-weight-bold" value="${nama}" placeholder="Pilih/ketik barang..." required>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary btn-lookup"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <small class="text-muted row-kd-text">Kode: ${kd || '-'}</small>
            </td>
            <td class="text-center align-middle bg-light">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input chk-innerbox-req" 
                           id="chk_req_inbox_${rIndex}" 
                           name="komponen[${rIndex}][is_innerbox]" 
                           value="1" ${isInbox ? 'checked' : ''}>
                    <label class="custom-control-label font-weight-bold ${isInbox ? 'text-primary' : 'text-muted'}" for="chk_req_inbox_${rIndex}">
                        ${isInbox ? '<i class="fas fa-box text-warning mr-1"></i> Pakai Innerbox' : 'Tanpa Innerbox'}
                    </label>
                </div>
                <input type="hidden" name="komponen[${rIndex}][satuan_innerbox]" value="Innerbox">
            </td>
            <td class="align-middle">
                <!-- Wrapper jika pakai innerbox -->
                <div class="wrap-innerbox-inputs" style="${isInbox ? '' : 'display: none;'}">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="input-group input-group-sm mr-1" style="max-width: 105px;">
                            <input type="number" step="any" min="0.001" 
                                   name="komponen[${rIndex}][qty_innerbox]" 
                                   class="form-control text-center font-weight-bold row-qty-inbox" 
                                   value="${qtyInbox}" placeholder="Jml Box">
                            <div class="input-group-append"><span class="input-group-text px-1 small">Box</span></div>
                        </div>
                        <span class="font-weight-bold text-muted mx-1">&times;</span>
                        <div class="input-group input-group-sm ml-1" style="max-width: 125px;">
                            <input type="number" step="any" min="0.001" 
                                   name="komponen[${rIndex}][isi_per_innerbox]" 
                                   class="form-control text-center font-weight-bold text-primary row-isi-inbox" 
                                   value="${isiInbox}" placeholder="Isi/Box">
                            <div class="input-group-append"><span class="input-group-text px-1 small row-satuan-label">${sat}</span></div>
                        </div>
                    </div>
                    <div class="text-center mt-1">
                        <span class="badge badge-warning text-dark px-2 py-1 row-inbox-summary" style="font-size: 0.78rem;">
                            <i class="fas fa-box mr-1"></i> ${qtyInbox} Box @ ${isiInbox} ${sat} = <strong>${qtyTotal} ${sat}</strong>
                        </span>
                    </div>
                </div>

                <!-- Wrapper jika reguler -->
                <div class="wrap-regular-qty" style="${isInbox ? 'display: none;' : ''}">
                    <div class="input-group input-group-sm mx-auto" style="max-width: 130px;">
                        <input type="number" step="any" min="0.001" 
                               class="form-control text-center font-weight-bold row-qty-regular" 
                               value="${qtyTotal}" placeholder="Qty">
                        <div class="input-group-append"><span class="input-group-text px-1 small row-satuan-label">${sat}</span></div>
                    </div>
                </div>

                <input type="hidden" name="komponen[${rIndex}][qty_per_paket]" class="row-qty-total" value="${qtyTotal}">
            </td>
            <td class="align-middle">
                <input type="text" name="komponen[${rIndex}][satuan]" 
                       class="form-control form-control-sm text-center row-satuan font-weight-bold" 
                       value="${sat}">
            </td>
            <td class="text-center align-middle font-weight-bold text-primary row-total-display" style="font-size: 0.95rem;">
                0
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-link text-danger btn-del" title="Hapus"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `;
}

// Helper Kemasan Dinamis Request
let reqKemasanIndex = 0;
function createReqKemasanRowHtml(index, nama, nominal) {
    nama = nama || '';
    nominal = (nominal !== undefined && nominal !== null && nominal !== '') ? nominal : 0;
    return `
        <tr class="req-kemasan-row" data-index="${index}">
            <td>
                <input type="text" name="kemasan_items[${index}][nama]" class="form-control form-control-sm inp-req-nama-kemasan font-weight-bold" placeholder="Contoh: Kardus Innerbox / Outer Box / Hologram" value="${escapeHtml(nama)}">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                    <input type="number" step="any" min="0" name="kemasan_items[${index}][nominal]" class="form-control form-control-sm text-right font-weight-bold inp-req-nominal-kemasan" placeholder="0" value="${nominal}">
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-xs btn-outline-danger btn-del-req-kemasan" title="Hapus baris kemasan">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    `;
}

function addReqKemasanRow(nama, nominal) {
    let html = createReqKemasanRowHtml(reqKemasanIndex, nama, nominal);
    $('#reqKemasanBody').append(html);
    reqKemasanIndex++;
    recalculateTotals();
}

function renderReqKemasanItems(items) {
    $('#reqKemasanBody').empty();
    reqKemasanIndex = 0;
    let list = Array.isArray(items) && items.length > 0 ? items : [{nama: '', nominal: 0}];
    list.forEach(function(it) {
        addReqKemasanRow(it.nama, it.nominal);
    });
    recalculateTotals();
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

function recalculateTotals() {
    let qtyRequest = parseFloat($('#qty_request').val()) || 0;
    let isReqInbox = parseInt($('#req_is_innerbox').val()) === 1;
    let jmlReqInbox = parseFloat($('#req_jumlah_innerbox').val()) || 0;
    let satReqInbox = $('#req_satuan_innerbox').val() || 'Innerbox';

    if (isReqInbox && jmlReqInbox > 0) {
        let totalInboxNeeded = qtyRequest * jmlReqInbox;
        $('#labelTotalInboxReq').html(
            `Total Kebutuhan Kemasan: <strong>${totalInboxNeeded.toLocaleString('id-ID')} ${satReqInbox}</strong> (${qtyRequest.toLocaleString('id-ID')} Paket × ${jmlReqInbox.toLocaleString('id-ID')} ${satReqInbox})`
        );
        $('#boxInfoKemasanReq').show();
    } else {
        $('#boxInfoKemasanReq').hide();
    }

    // Kalkulasi Biaya Kemasan Live Dinamis dari Repeater
    let totalKemasan1Paket = 0;
    $('#reqKemasanBody .req-kemasan-row').each(function() {
        let nom = parseFloat($(this).find('.inp-req-nominal-kemasan').val()) || 0;
        totalKemasan1Paket += nom;
    });
    let totalKemasanAll = totalKemasan1Paket * qtyRequest;

    $('#lbl_req_total_kemasan_1paket').text('Rp ' + Number(totalKemasan1Paket).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $('#lbl_req_total_kemasan_all').text('Rp ' + Number(totalKemasanAll).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

    $('#komponenList .item-row').each(function() {
        let isInbox = $(this).find('.chk-innerbox-req').is(':checked');
        let sat = $(this).find('.row-satuan').val() || 'Pcs';
        $(this).find('.row-satuan-label').text(sat);

        let qtyPerPaket = 0;
        if (isInbox) {
            let qBox = parseFloat($(this).find('.row-qty-inbox').val()) || 0;
            let iBox = parseFloat($(this).find('.row-isi-inbox').val()) || 0;
            qtyPerPaket = qBox * iBox;
            $(this).find('.row-qty-total').val(qtyPerPaket);
            $(this).find('.row-qty-regular').val(qtyPerPaket);
            $(this).find('.row-inbox-summary').html(
                `<i class="fas fa-box mr-1"></i> ${qBox} Box @ ${iBox} ${sat} = <strong>${qtyPerPaket.toLocaleString('id-ID')} ${sat}</strong>`
            );

            let totalPcs = qtyRequest * qtyPerPaket;
            let totalBox = qtyRequest * qBox;
            $(this).find('.row-total-display').html(
                `<strong>${totalPcs.toLocaleString('id-ID')}</strong> ${sat}<br>` +
                `<span class="badge badge-warning text-dark font-weight-bold mt-1" style="font-size: 0.78rem;"><i class="fas fa-box mr-1"></i> ${totalBox.toLocaleString('id-ID')} Box</span>`
            );
        } else {
            qtyPerPaket = parseFloat($(this).find('.row-qty-regular').val()) || 0;
            $(this).find('.row-qty-total').val(qtyPerPaket);
            let totalPcs = qtyRequest * qtyPerPaket;
            $(this).find('.row-total-display').html(`<strong>${totalPcs.toLocaleString('id-ID')}</strong> ${sat}`);
        }
    });
}

function loadBarangSearch(query) {
    $('#tableSearchResult tbody').html('<tr><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i> Mencari barang di tberp_stock_batch...</td></tr>');
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
                                <button type="button" class="btn btn-sm btn-primary btn-select-barang px-3 font-weight-bold" 
                                        data-kd="${b.kode_barang}" 
                                        data-nama="${b.nama_barang}" 
                                        data-satuan="${b.satuan || 'Pcs'}">
                                    <i class="fas fa-check mr-1"></i> Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#tableSearchResult tbody').html(html);
        },
        error: function() {
            $('#tableSearchResult tbody').html('<tr><td colspan="5" class="text-center text-danger py-3">Gagal mengambil data stok batch</td></tr>');
        }
    });
}
</script>
