<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-box-open text-warning mr-2"></i> Pembongkaran Paket Bundling (Unbundling)
                    </h1>
                    <p class="text-muted mb-0 small">Bongkar paket bundling menjadi komponen barang eceran bebas untuk memenuhi penjualan eceran pelanggan</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= site_url('logistik/bundling') ?>" class="btn btn-outline-secondary font-weight-bold shadow-sm mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <a href="<?= site_url('logistik/bundling/history') ?>" class="btn btn-outline-primary font-weight-bold shadow-sm">
                        <i class="fas fa-history mr-1"></i> Riwayat Pembongkaran
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Alert Prinsip Stok Bebas vs Paket -->
            <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 12px; background: #e0f2fe; color: #0369a1;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shield-alt fa-2x mr-3 text-info"></i>
                    <div>
                        <strong style="font-size: 1rem;">Mekanisme Anti-Double Counting KarismaERP:</strong>
                        <div class="small mt-1" style="color: #0c4a6e;">
                            Barang yang sudah berada dalam paket tidak boleh dianggap sebagai stok bebas. 
                            Melalui proses pembongkaran ini, <strong>stok paket akan berkurang</strong> dan <strong>stok komponen kembali bertambah</strong> menjadi stok bebas eceran secara presisi beserta pengembalian nilai HPP persediaan.
                        </div>
                    </div>
                </div>
            </div>

            <form id="formDisassembly" method="post" action="<?= site_url('logistik/bundling/save_disassembly') ?>">
                <div class="row">
                    <!-- Kolom Kiri: Input Paket yang Akan Dibongkar -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                                <h6 class="font-weight-bold text-dark m-0"><i class="fas fa-box text-warning mr-2"></i>Paket yang Dibongkar</h6>
                            </div>
                            <div class="card-body pt-0">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">No. Pembongkaran (Disassembly)</label>
                                    <input type="text" class="form-control font-weight-bold bg-light" value="<?= htmlspecialchars($no_disassembly) ?>" readonly>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Pilih Paket Bundling yang Akan Dibongkar <span class="text-danger">*</span></label>
                                    <select id="selectFormulaDsb" class="form-control select2" required>
                                        <option value="">-- Pilih Paket Bundling --</option>
                                        <?php foreach ($formulas as $f): ?>
                                            <option value="<?= $f['id_formula'] ?>" data-kode="<?= htmlspecialchars($f['kode_paket']) ?>" data-nama="<?= htmlspecialchars($f['nama_paket']) ?>" data-satuan="<?= htmlspecialchars($f['satuan_paket']) ?>">
                                                <?= htmlspecialchars($f['nama_paket']) ?> (<?= htmlspecialchars($f['kode_paket']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="kode_paket" id="dsb_kode_paket" value="">
                                    <input type="hidden" name="nama_paket" id="dsb_nama_paket" value="">
                                    <input type="hidden" name="satuan" id="dsb_satuan" value="Box">
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Gudang Penyimpanan Paket</label>
                                    <select name="id_gudang" id="dsb_id_gudang" class="form-control">
                                        <?php foreach ($gudangs as $g): ?>
                                            <option value="<?= $g['id_gudang'] ?>" <?= ($g['id_gudang'] == 12) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($g['nama_gudang']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Box Cek Stok Paket Tersedia -->
                                <div class="alert alert-light border small mb-3" id="boxStokPaket">
                                    <div class="d-flex justify-content-between">
                                        <span>Stok Paket Tersedia:</span>
                                        <strong id="labelStokPaket" class="text-primary font-weight-bold">Pilih paket dulu</strong>
                                    </div>
                                    <div class="mt-2" id="divBatchPaket" style="display:none;">
                                        <label class="small font-weight-bold text-muted mb-1">Pilih Batch Lot Paket:</label>
                                        <select class="form-control form-control-sm" id="selectLotPaket">
                                            <!-- Diisi AJAX -->
                                        </select>
                                        <input type="hidden" name="no_lot_paket" id="dsb_no_lot_paket" value="-">
                                        <input type="hidden" name="expired_date_paket" id="dsb_exp_paket" value="">
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Jumlah Paket yang Dibongkar <span class="text-danger">*</span></label>
                                    <input type="number" step="any" min="1" id="qty_disassembly" name="qty_disassembly" class="form-control form-control-lg font-weight-bold text-danger" placeholder="0" value="10" required>
                                    <small class="text-muted">Masukkan berapa box paket yang akan dibuka/dibongkar.</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Tanggal Pembongkaran</label>
                                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Alasan Pembongkaran <span class="text-danger">*</span></label>
                                    <textarea name="alasan" class="form-control font-weight-bold" rows="3" placeholder="Contoh: Customer membeli 10 Spontas 1 Ltr secara ecer, stok ecer lepas habis..." required>Kebutuhan penjualan eceran pelanggan</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Komponen yang Akan Kembali Menjadi Stok Bebas -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                                <h6 class="font-weight-bold text-dark m-0">
                                    <i class="fas fa-undo-alt text-success mr-2"></i> Komponen yang Dikembalikan ke Stok Eceran Bebas
                                </h6>
                                <small class="text-muted">Barang berikut akan kembali bertambah pada stok bebas dan siap dijual eceran melalui Sales Order / Kasir.</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="tableReturnItems" style="font-size: 0.92rem;">
                                        <thead style="background: #f1f5f9; color: #334155;">
                                            <tr>
                                                <th style="width: 45%;">Barang Komponen</th>
                                                <th style="width: 20%;" class="text-center">Isi per Paket</th>
                                                <th style="width: 15%;" class="text-center">Satuan</th>
                                                <th style="width: 20%;" class="text-center bg-success text-white">Qty Kembali ke Ecer</th>
                                            </tr>
                                        </thead>
                                        <tbody id="returnItemsList">
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    Silakan pilih paket bundling di panel kiri terlebih dahulu.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="p-4 bg-light border-top">
                                    <div class="row align-items-center">
                                        <div class="col-md-7 text-muted small">
                                            <i class="fas fa-check-double text-success mr-1"></i>
                                            <strong>Hasil Pembongkaran:</strong>
                                            Paket berkurang dari stok, komponen bertambah ke stok bebas. Penjualan ecer dapat langsung dilakukan tanpa risiko selisih fisik.
                                        </div>
                                        <div class="col-md-5 text-right">
                                            <button type="submit" class="btn btn-warning btn-lg px-4 font-weight-bold shadow text-dark">
                                                <i class="fas fa-box-open mr-1"></i> Konfirmasi Pembongkaran
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let returnIndex = 0;

$(document).ready(function() {
    $('#selectFormulaDsb').change(function() {
        let idFormula = $(this).val();
        if (!idFormula) {
            $('#returnItemsList').html('<tr><td colspan="4" class="text-center py-5 text-muted">Silakan pilih paket bundling terlebih dahulu.</td></tr>');
            $('#dsb_kode_paket').val('');
            $('#dsb_nama_paket').val('');
            return;
        }

        let opt = $(this).find(':selected');
        let kd = opt.data('kode');
        let nm = opt.data('nama');
        let sat = opt.data('satuan');

        $('#dsb_kode_paket').val(kd);
        $('#dsb_nama_paket').val(nm);
        $('#dsb_satuan').val(sat || 'Box');

        // Cek stok paket di gudang
        loadStokPaket(kd, $('#dsb_id_gudang').val());

        // Ambil komponen formula
        $.ajax({
            url: '<?= site_url("purchasing/bundling/formula/detail_ajax") ?>',
            data: { id_formula: idFormula },
            dataType: 'json',
            success: function(res) {
                if (res.status && res.data && res.data.details) {
                    $('#returnItemsList').empty();
                    returnIndex = 0;
                    res.data.details.forEach(function(d) {
                        let html = `
                            <tr class="ret-row" data-isi="${parseFloat(d.qty_komponen)}">
                                <td>
                                    <input type="hidden" name="komponen[${returnIndex}][kode_barang]" value="${d.kode_barang_komponen}">
                                    <input type="hidden" name="komponen[${returnIndex}][nama_barang]" value="${d.nama_barang_komponen}">
                                    <input type="hidden" name="komponen[${returnIndex}][satuan]" value="${d.satuan || 'Pcs'}">
                                    <input type="hidden" name="komponen[${returnIndex}][no_lot]" value="-">
                                    <input type="hidden" name="komponen[${returnIndex}][expired_date]" value="">
                                    <div class="font-weight-bold text-dark">${d.nama_barang_komponen || d.kode_barang_komponen}</div>
                                    <small class="text-muted">Kode: ${d.kode_barang_komponen}</small>
                                </td>
                                <td class="text-center font-weight-bold text-muted">
                                    ${parseFloat(d.qty_komponen)} ${d.satuan || 'Pcs'}
                                </td>
                                <td class="text-center">
                                    ${d.satuan || 'Pcs'}
                                </td>
                                <td class="text-center">
                                    <input type="number" step="any" min="0" 
                                           name="komponen[${returnIndex}][qty_kembali]" 
                                           class="form-control form-control-sm text-center font-weight-bold text-success ret-qty-out" 
                                           value="0" readonly>
                                </td>
                            </tr>
                        `;
                        $('#returnItemsList').append(html);
                        returnIndex++;
                    });
                    recalcDisassemblyQuantities();
                }
            }
        });
    });

    $('#qty_disassembly').on('input change', function() {
        recalcDisassemblyQuantities();
    });

    $('#dsb_id_gudang').change(function() {
        let kd = $('#dsb_kode_paket').val();
        if (kd) {
            loadStokPaket(kd, $(this).val());
        }
    });

    $(document).on('change', '#selectLotPaket', function() {
        let opt = $(this).find(':selected');
        $('#dsb_no_lot_paket').val(opt.data('lot') || '-');
        $('#dsb_exp_paket').val(opt.data('exp') || '');
    });

    $('#formDisassembly').submit(function(e) {
        e.preventDefault();

        let qtyDsb = parseFloat($('#qty_disassembly').val()) || 0;
        if (qtyDsb <= 0) {
            Swal.fire('Peringatan', 'Jumlah paket yang dibongkar harus lebih dari 0', 'warning');
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Pembongkaran Paket?',
            text: 'Sebanyak ' + qtyDsb + ' Box ' + $('#dsb_nama_paket').val() + ' akan dibongkar. Komponen akan kembali ke stok bebas ecer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Bongkar Sekarang!',
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
                                window.location.href = '<?= site_url("logistik/bundling/history") ?>';
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

function recalcDisassemblyQuantities() {
    let qtyDsb = parseFloat($('#qty_disassembly').val()) || 0;
    $('.ret-row').each(function() {
        let isi = parseFloat($(this).data('isi')) || 1;
        let totalReturn = qtyDsb * isi;
        $(this).find('.ret-qty-out').val(totalReturn);
    });
}

function loadStokPaket(kdPaket, idGudang) {
    $.ajax({
        url: '<?= site_url("logistik/bundling/ajax_get_paket_stock_batch") ?>',
        data: { kode_paket: kdPaket, id_gudang: idGudang },
        dataType: 'json',
        success: function(res) {
            if (res.status) {
                $('#labelStokPaket').text(parseFloat(res.available).toLocaleString('id-ID') + ' Box');
                $('#qty_disassembly').attr('max', res.available);

                if (res.batches && res.batches.length > 0) {
                    $('#divBatchPaket').show();
                    let optHtml = '';
                    res.batches.forEach(function(b) {
                        let av = parseFloat(b.qty_on_hand) - parseFloat(b.qty_reserved);
                        optHtml += `<option value="${b.no_lot}" data-lot="${b.no_lot}" data-exp="${b.expired_date || ''}">${b.no_lot} (Exp: ${b.expired_date || '-'}) - Ada: ${av}</option>`;
                    });
                    $('#selectLotPaket').html(optHtml);
                    let first = res.batches[0];
                    $('#dsb_no_lot_paket').val(first.no_lot || '-');
                    $('#dsb_exp_paket').val(first.expired_date || '');
                } else {
                    $('#divBatchPaket').hide();
                    $('#dsb_no_lot_paket').val('-');
                    $('#dsb_exp_paket').val('');
                }
            }
        }
    });
}
</script>
