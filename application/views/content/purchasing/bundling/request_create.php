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
                <!-- Hidden Values untuk Kemasan & Formula -->
                <input type="hidden" name="is_innerbox" id="req_is_innerbox" value="0">
                <input type="hidden" name="jumlah_innerbox" id="req_jumlah_innerbox" value="0">
                <input type="hidden" name="satuan_innerbox" id="req_satuan_innerbox" value="Innerbox">
                <input type="hidden" name="simpan_sebagai_formula" value="0">

                <!-- 1. KARTU ATAS: INFORMASI REQUEST (FULL WIDTH, KOMPAK & BEBAS SCROLL) -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold text-dark m-0">
                            <i class="fas fa-file-invoice text-primary mr-2"></i>Informasi Request Paket Bundling
                        </h6>
                        <a href="<?= site_url('purchasing/bundling/formula') ?>" target="_blank" class="small font-weight-bold text-primary">
                            <i class="fas fa-external-link-alt mr-1"></i> Kelola Master Formula Paket
                        </a>
                    </div>
                    <div class="card-body pt-1 pb-3">
                        <!-- Baris 1: No Request, Tgl Request, Dropdown Utama Master Formula -->
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Nomor Request</label>
                                    <input type="text" name="no_request" class="form-control form-control-sm font-weight-bold bg-light" value="<?= htmlspecialchars($no_request) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Tanggal Request <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_request" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-primary mb-1">
                                        <i class="fas fa-flask mr-1"></i> Pilih Master Formula Paket <span class="text-danger">*</span>
                                    </label>
                                    <select id="selectFormula" class="form-control form-control-sm select2" required>
                                        <option value="">-- Pilih dari Master Formula Tersimpan --</option>
                                        <?php foreach ($formulas as $f): ?>
                                            <option value="<?= $f['id_formula'] ?>" data-kode="<?= htmlspecialchars($f['kode_paket']) ?>" data-nama="<?= htmlspecialchars($f['nama_paket']) ?>" data-satuan="<?= htmlspecialchars($f['satuan_paket']) ?>">
                                                <?= htmlspecialchars($f['nama_paket']) ?> (<?= htmlspecialchars($f['kode_paket']) ?>) - <?= $f['total_komponen'] ?> Komponen
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 2: Nama Paket, Kode Paket, Qty Diminta, Satuan -->
                        <div class="row align-items-center mt-1">
                            <div class="col-md-4">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Nama Paket Bundling</label>
                                    <input type="text" id="nama_paket" name="nama_paket" class="form-control form-control-sm font-weight-bold bg-light" placeholder="Otomatis dari formula..." readonly required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Kode Paket Bundling</label>
                                    <input type="text" id="kode_paket" name="kode_paket" class="form-control form-control-sm bg-light" placeholder="Otomatis dari formula..." readonly required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Jumlah Paket Diminta <span class="text-danger">*</span></label>
                                    <input type="number" step="any" min="1" id="qty_request" name="qty_request" class="form-control form-control-sm font-weight-bold text-primary" placeholder="Masukkan jumlah request..." value="" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Satuan</label>
                                    <input type="text" id="satuan_paket" name="satuan" class="form-control form-control-sm font-weight-bold text-center bg-light" value="Box" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: Gudang Asal, Gudang Tujuan, Catatan -->
                        <div class="row align-items-center mt-1">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted mb-1">Gudang Asal (Bahan Komponen)</label>
                                    <select name="id_gudang_asal" class="form-control form-control-sm">
                                        <?php foreach ($gudangs as $g): ?>
                                            <option value="<?= $g['id_gudang'] ?>" <?= ($g['id_gudang'] == 2) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($g['nama_gudang']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted mb-1">Gudang Tujuan (Perakitan Paket)</label>
                                    <select name="id_gudang_tujuan" class="form-control form-control-sm">
                                        <?php foreach ($gudangs as $g): ?>
                                            <option value="<?= $g['id_gudang'] ?>" <?= (stripos($g['nama_gudang'], 'Bundling') !== false || $g['id_gudang'] == 12) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($g['nama_gudang']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted mb-1">Catatan / Keterangan Request</label>
                                    <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Catatan instruksi khusus untuk tim Logistik...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. KARTU BAWAH: KOMPOSISI ISI PAKET (READ ONLY DARI MASTER FORMULA) -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-bottom pt-3 pb-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="font-weight-bold text-dark m-0" id="cardTitleKomposisi">
                                    <i class="fas fa-layer-group text-primary mr-2"></i>Komposisi Isi Paket <span class="badge badge-secondary px-2 py-1 ml-1" style="font-size: 0.75rem;">READ ONLY</span>
                                </h6>
                                <small class="text-muted" id="cardSubtitleKomposisi">
                                    Resep komposisi terkunci sesuai Master Formula. Perubahan atau penambahan komposisi dilakukan melalui menu Master Formula.
                                </small>
                            </div>
                            <a href="<?= site_url('purchasing/bundling/formula') ?>" target="_blank" class="btn btn-sm btn-outline-secondary font-weight-bold shadow-sm">
                                <i class="fas fa-cog mr-1"></i> Edit di Master Formula
                            </a>
                        </div>

                        <!-- Panel Pratinjau Struktur Kemasan (Read-Only) -->
                        <div id="panelKemasanPreview" class="p-3 bg-light border" style="border-radius: 10px; display: none;">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <div id="infoKemasanInnerbox" style="display: none;">
                                    <span class="badge badge-warning text-dark px-3 py-2 font-weight-bold mr-2" style="font-size: 0.88rem;">
                                        <i class="fas fa-boxes mr-1"></i> Struktur: <span id="labelJmlInboxReq">20</span> <span id="labelSatInboxReq">Innerbox</span> / 1 Paket
                                    </span>
                                    <span class="text-muted small">1 Paket Master Box dirakit dari <strong id="textJmlInboxDetail">20</strong> kemasan kardus kecil.</span>
                                </div>
                                <div id="infoKemasanStandar" style="display: none;">
                                    <span class="badge badge-secondary px-3 py-2 font-weight-bold" style="font-size: 0.88rem;">
                                        <i class="fas fa-box mr-1"></i> Struktur: Kemasan Standar (Langsung Tanpa Innerbox)
                                    </span>
                                </div>
                                <div class="font-weight-bold text-primary mt-2 mt-md-0" id="labelTotalInboxReq">
                                    Total Kebutuhan Kemasan: <strong>0 Innerbox</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="tableKomponen" style="font-size: 0.92rem;">
                                <thead style="background: #f1f5f9; color: #334155;">
                                    <tr>
                                        <th style="width: 5%;" class="text-center">No</th>
                                        <th style="width: 45%;">Komponen Barang</th>
                                        <th style="width: 20%;" class="text-center" id="thQtyKomponen">Qty</th>
                                        <th style="width: 12%;" class="text-center">Satuan Stok</th>
                                        <th style="width: 18%;" class="text-center" id="thTotalPaket">Total Isi / 1 Paket</th>
                                    </tr>
                                </thead>
                                <tbody id="komponenList">
                                    <tr id="emptyRowKomponen">
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fas fa-flask fa-3x text-secondary mb-2 d-block"></i>
                                            <strong class="d-block text-dark font-weight-bold">Silakan Pilih Master Formula Paket di Atas</strong>
                                            <small class="text-muted d-block mt-1">Rincian komposisi isi paket akan otomatis dimuat sesuai resep master formula.</small>
                                            <a href="<?= site_url('purchasing/bundling/formula') ?>" target="_blank" class="btn btn-xs btn-outline-primary font-weight-bold mt-2">
                                                <i class="fas fa-plus-circle mr-1"></i> Tambah / Kelola Master Formula Baru
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 bg-light border-top">
                            <div class="row align-items-center">
                                <div class="col-md-7 text-muted small">
                                    <i class="fas fa-info-circle text-primary mr-1"></i>
                                    <span id="labelRumusOtomatis">
                                        Komposisi di atas merupakan referensi resmi dari Master Formula Karisma ERP.
                                    </span>
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
            </form>
        </div>
    </section>
</div>
<!-- /.content-wrapper -->
</div>
<!-- /.wrapper -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function checkEmptyRow() {
    if ($('#komponenList tr.item-row').length === 0) {
        if ($('#emptyRowKomponen').length === 0) {
            let emptyHtml = `
                <tr id="emptyRowKomponen">
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="fas fa-flask fa-3x text-secondary mb-2 d-block"></i>
                        <strong class="d-block text-dark font-weight-bold">Silakan Pilih Master Formula Paket di Atas</strong>
                        <small class="text-muted d-block mt-1">Rincian komposisi isi paket akan otomatis dimuat sesuai resep master formula.</small>
                        <a href="<?= site_url('purchasing/bundling/formula') ?>" target="_blank" class="btn btn-xs btn-outline-primary font-weight-bold mt-2">
                            <i class="fas fa-plus-circle mr-1"></i> Tambah / Kelola Master Formula Baru
                        </a>
                    </td>
                </tr>
            `;
            $('#komponenList').html(emptyHtml);
        }
    } else {
        $('#emptyRowKomponen').remove();
    }
}

function recalculateKemasan() {
    let qtyRequest = parseFloat($('#qty_request').val()) || 0;
    let isInbox = parseInt($('#req_is_innerbox').val()) === 1;
    let jmlInbox = parseFloat($('#req_jumlah_innerbox').val()) || 0;
    let satInbox = $('#req_satuan_innerbox').val() || 'Innerbox';

    if (isInbox && jmlInbox > 0) {
        let totalInboxNeeded = qtyRequest * jmlInbox;
        $('#labelTotalInboxReq').html(
            `Total Kebutuhan Kemasan: <strong>${totalInboxNeeded.toLocaleString('id-ID')} ${satInbox}</strong> (${qtyRequest.toLocaleString('id-ID')} Paket × ${jmlInbox.toLocaleString('id-ID')} ${satInbox})`
        );
    } else {
        $('#labelTotalInboxReq').html('');
    }
}

function renderRequestItemRowReadOnly(no, kd, nama, sat, isiInbox, qtyTotal) {
    isiInbox = (isiInbox !== undefined && isiInbox !== null && parseFloat(isiInbox) > 0) ? parseFloat(isiInbox) : 1;
    qtyTotal = (qtyTotal !== undefined && qtyTotal !== null && parseFloat(qtyTotal) > 0) ? parseFloat(qtyTotal) : isiInbox;
    sat = sat || 'Pcs';
    let isInbox = parseInt($('#req_is_innerbox').val()) === 1;
    let jmlInbox = parseFloat($('#req_jumlah_innerbox').val()) || 1;
    let satInbox = $('#req_satuan_innerbox').val() || 'Innerbox';

    let displayPaket = isInbox
        ? `<strong>${qtyTotal.toLocaleString('id-ID')}</strong> ${sat}<br><small class="text-muted font-weight-bold">(${isiInbox.toLocaleString('id-ID')} × ${jmlInbox.toLocaleString('id-ID')} ${satInbox})</small>`
        : `<strong>${qtyTotal.toLocaleString('id-ID')}</strong> ${sat}<br><small class="text-muted">Kemasan Standar</small>`;

    return `
        <tr class="item-row">
            <td class="text-center align-middle text-muted font-weight-bold" style="font-size: 0.9rem;">
                ${no}
            </td>
            <td class="align-middle">
                <input type="hidden" name="komponen[${no - 1}][kode_barang_komponen]" class="row-kd" value="${kd}">
                <input type="hidden" name="komponen[${no - 1}][nama_barang_komponen]" class="row-nama" value="${escapeHtml(nama)}">
                <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">${escapeHtml(nama)}</div>
                <small class="text-muted">Kode: <span class="font-weight-bold text-primary">${kd || '-'}</span></small>
            </td>
            <td class="align-middle text-center font-weight-bold text-primary" style="font-size: 1rem;">
                ${isiInbox.toLocaleString('id-ID')} <span class="text-muted font-weight-normal" style="font-size: 0.85rem;">${sat}</span>
                <input type="hidden" name="komponen[${no - 1}][isi_per_innerbox]" class="row-isi-inbox" value="${isiInbox}">
            </td>
            <td class="align-middle text-center">
                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 0.85rem;">${sat}</span>
                <input type="hidden" name="komponen[${no - 1}][satuan]" class="row-satuan" value="${sat}">
            </td>
            <td class="align-middle text-center row-paket-display" style="font-size: 0.92rem;">
                ${displayPaket}
                <input type="hidden" name="komponen[${no - 1}][qty_per_paket]" class="row-qty-total" value="${qtyTotal}">
            </td>
        </tr>
    `;
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

$(document).ready(function() {
    checkEmptyRow();

    // Event perubahan Qty Request
    $('#qty_request').on('input change', function() {
        recalculateKemasan();
    });

    // Pilihan Formula Siap Pakai (Read Only Loader)
    $('#selectFormula').change(function() {
        let idFormula = $(this).val();
        if (!idFormula) {
            $('#nama_paket').val('');
            $('#kode_paket').val('');
            $('#satuan_paket').val('Box');
            $('#req_is_innerbox').val(0);
            $('#req_jumlah_innerbox').val(0);
            $('#req_satuan_innerbox').val('Innerbox');
            $('#panelKemasanPreview').hide();
            $('#komponenList').empty();
            checkEmptyRow();
            recalculateKemasan();
            return;
        }

        let opt = $(this).find(':selected');
        $('#nama_paket').val(opt.data('nama'));
        $('#kode_paket').val(opt.data('kode'));
        if (opt.data('satuan')) $('#satuan_paket').val(opt.data('satuan'));

        // Muat detail komponen dari Master Formula
        $.ajax({
            url: '<?= site_url("purchasing/bundling/formula/detail_ajax") ?>',
            data: { id_formula: idFormula },
            dataType: 'json',
            success: function(res) {
                if (res.status && res.data) {
                    let f = res.data;
                    let isInbox = (parseInt(f.is_innerbox) === 1 || f.is_innerbox === true || f.is_innerbox === "1");
                    let jmlInbox = parseFloat(f.jumlah_innerbox) || 20;
                    let satInbox = f.satuan_innerbox || 'Innerbox';

                    $('#req_is_innerbox').val(isInbox ? 1 : 0);
                    $('#req_jumlah_innerbox').val(isInbox ? jmlInbox : 0);
                    $('#req_satuan_innerbox').val(satInbox);

                    // Tampilkan panel informasi kemasan
                    $('#panelKemasanPreview').show();
                    if (isInbox && jmlInbox > 0) {
                        $('#infoKemasanInnerbox').show();
                        $('#infoKemasanStandar').hide();
                        $('#labelJmlInboxReq').text(jmlInbox.toLocaleString('id-ID'));
                        $('#textJmlInboxDetail').text(jmlInbox.toLocaleString('id-ID'));
                        $('#labelSatInboxReq').text(satInbox);
                        $('#thQtyKomponen').text('Qty');
                    } else {
                        $('#infoKemasanInnerbox').hide();
                        $('#infoKemasanStandar').show();
                        $('#thQtyKomponen').text('Qty');
                    }

                    // Muat rincian komponen ke tabel (Read-Only)
                    $('#komponenList').empty();
                    if (f.details && f.details.length > 0) {
                        let no = 1;
                        f.details.forEach(function(d) {
                            let isiInbox = parseFloat(d.isi_per_innerbox) || 0;
                            let qtyKomponen = parseFloat(d.qty_komponen) || 1;
                            if (isInbox && isiInbox <= 0 && jmlInbox > 0) {
                                isiInbox = qtyKomponen / jmlInbox;
                            } else if (!isInbox) {
                                isiInbox = qtyKomponen;
                            }

                            let html = renderRequestItemRowReadOnly(
                                no,
                                d.kode_barang_komponen,
                                d.nama_barang_komponen || d.kode_barang_komponen,
                                d.satuan || 'Pcs',
                                isiInbox,
                                qtyKomponen
                            );
                            $('#komponenList').append(html);
                            no++;
                        });
                    }
                    checkEmptyRow();
                    recalculateKemasan();
                }
            }
        });
    });

    // Form Submit AJAX
    $('#formRequestBundling').submit(function(e) {
        e.preventDefault();

        let idFormula = $('#selectFormula').val();
        if (!idFormula) {
            Swal.fire('Peringatan', 'Silakan pilih Master Formula Paket terlebih dahulu', 'warning');
            return;
        }

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
            Swal.fire('Peringatan', 'Komposisi isi paket belum memiliki komponen barang valid. Silakan periksa di Master Formula.', 'warning');
            return;
        }

        let formElem = this;
        Swal.fire({
            title: 'Kirim Request Bundling?',
            text: 'Request ' + qtyReq + ' ' + ($('#satuan_paket').val() || 'Box') + ' ' + $('#nama_paket').val() + ' akan dikirimkan ke Logistik untuk diproses.',
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
</script>
