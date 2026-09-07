<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-hammer text-primary mr-2"></i> Pembuatan Paket Bundling (Assembly)
                    </h1>
                    <p class="text-muted mb-0 small">Transformasi komponen barang di Gudang Bundling menjadi produk Paket Bundling fisik</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= site_url('logistik/bundling/detail/' . $request['id_request']) ?>" class="btn btn-outline-secondary font-weight-bold shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form id="formAssembly" method="post" action="<?= site_url('logistik/bundling/save_assembly') ?>">
                <input type="hidden" name="id_request" value="<?= $request['id_request'] ?>">
                <input type="hidden" name="kode_paket" value="<?= htmlspecialchars($request['kode_paket']) ?>">
                <input type="hidden" name="nama_paket" value="<?= htmlspecialchars($request['nama_paket']) ?>">
                <input type="hidden" name="id_gudang" value="<?= $request['id_gudang_tujuan'] ?>">
                <input type="hidden" name="satuan" value="<?= htmlspecialchars($request['satuan']) ?>">

                <div class="row">
                    <!-- Kolom Kiri: Info Paket yang Dibuat -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                                <h6 class="font-weight-bold text-dark m-0"><i class="fas fa-box text-primary mr-2"></i>Target Pembuatan Paket</h6>
                            </div>
                            <div class="card-body pt-0">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">No. Perakitan (Assembly)</label>
                                    <input type="text" class="form-control font-weight-bold bg-light" value="<?= htmlspecialchars($no_assembly) ?>" readonly>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Ref. Request Purchasing</label>
                                    <input type="text" class="form-control font-weight-bold bg-light" value="<?= htmlspecialchars($request['no_request']) ?>" readonly>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Produk Paket</label>
                                    <h5 class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($request['nama_paket']) ?></h5>
                                    <span class="badge badge-light border text-muted"><?= htmlspecialchars($request['kode_paket']) ?></span>
                                </div>

                                <div class="alert alert-light border small text-muted mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Request:</span>
                                        <strong><?= number_format((float)$request['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Sudah Dibuat:</span>
                                        <strong class="text-success"><?= number_format((float)$request['qty_realisasi'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Sisa Belum Dibuat:</span>
                                        <strong class="text-danger"><?= number_format($sisa_request, 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?></strong>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Jumlah Paket yang Dibuat Saat Ini <span class="text-danger">*</span></label>
                                    <input type="number" step="any" min="1" max="<?= $sisa_request ?>" 
                                           id="qty_assembly" name="qty_assembly" 
                                           class="form-control form-control-lg font-weight-bold text-success" 
                                           value="<?= $sisa_request ?>" required>
                                    <small class="text-muted">Mendukung pembuatan sebagian (parsial). Masukkan kuantitas yang siap dirakit sekarang.</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Tanggal Perakitan</label>
                                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Nomor Lot Paket Baru</label>
                                    <input type="text" name="no_lot_paket" class="form-control font-weight-bold" value="LOT-PKT-<?= date('ymd') ?>-<?= rand(100, 999) ?>">
                                    <small class="text-muted">Nomor identitas batch lot untuk kardus paket bundling.</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Expired Date Paket</label>
                                    <input type="date" name="expired_date_paket" class="form-control" value="">
                                    <small class="text-muted">Opsional. Jika dikosongkan, sistem akan otomatis mengambil tanggal kadaluarsa terdekat dari komponen.</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Catatan Pelaksanaan</label>
                                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan perakitan..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Pengurangan Stok Komponen di Gudang Bundling -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                                <h6 class="font-weight-bold text-dark m-0">
                                    <i class="fas fa-layer-group text-primary mr-2"></i> Penggunaan Komponen di Gudang Bundling
                                </h6>
                                <small class="text-muted">Komponen berikut akan berkurang otomatis dari stok fisik dan dicatat OUT pada kartu stok.</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="tableAssemblyItems" style="font-size: 0.92rem;">
                                        <thead style="background: #f1f5f9; color: #334155;">
                                            <tr>
                                                <th style="width: 30%;">Komponen Barang</th>
                                                <th style="width: 14%;" class="text-center">Isi / Paket</th>
                                                <th style="width: 28%;">Pilih Batch Lot Gudang Bundling</th>
                                                <th style="width: 14%;" class="text-center">Stok Ada</th>
                                                <th style="width: 14%;" class="text-center bg-danger text-white">Qty Terpakai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $cIdx = 0; ?>
                                            <?php foreach ($components as $comp): ?>
                                                <tr class="comp-row" data-isi="<?= (float)$comp['qty_per_paket'] ?>">
                                                    <td>
                                                        <input type="hidden" name="komponen[<?= $cIdx ?>][kode_barang]" value="<?= htmlspecialchars($comp['kode_barang_komponen']) ?>">
                                                        <input type="hidden" name="komponen[<?= $cIdx ?>][nama_barang]" value="<?= htmlspecialchars($comp['nama_barang_komponen']) ?>">
                                                        <input type="hidden" name="komponen[<?= $cIdx ?>][satuan]" value="<?= htmlspecialchars($comp['satuan']) ?>">
                                                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($comp['nama_barang_komponen']) ?></div>
                                                        <small class="text-muted">Kode: <?= htmlspecialchars($comp['kode_barang_komponen']) ?></small>
                                                    </td>
                                                    <td class="text-center font-weight-bold text-muted">
                                                        <?= number_format((float)$comp['qty_per_paket'], 2, ',', '.') ?> <?= htmlspecialchars($comp['satuan']) ?>
                                                    </td>
                                                    <td>
                                                        <?php if (empty($comp['batches'])): ?>
                                                            <span class="badge badge-danger">Stok di Gudang Bundling 0! Mutasikan dulu.</span>
                                                            <input type="hidden" name="komponen[<?= $cIdx ?>][no_lot]" value="-">
                                                            <input type="hidden" name="komponen[<?= $cIdx ?>][expired_date]" value="">
                                                        <?php else: ?>
                                                            <select class="form-control form-control-sm select-comp-lot">
                                                                <?php foreach ($comp['batches'] as $b): ?>
                                                                    <?php $avail = (float)$b['qty_on_hand'] - (float)$b['qty_reserved']; ?>
                                                                    <option value="<?= htmlspecialchars($b['no_lot']) ?>" 
                                                                            data-lot="<?= htmlspecialchars($b['no_lot']) ?>" 
                                                                            data-exp="<?= htmlspecialchars($b['expired_date'] ?: '') ?>" 
                                                                            data-avail="<?= $avail ?>">
                                                                        Lot: <?= htmlspecialchars($b['no_lot']) ?> (Exp: <?= $b['expired_date'] ?: '-' ?>) - Ada: <?= number_format($avail, 2, ',', '.') ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <input type="hidden" name="komponen[<?= $cIdx ?>][no_lot]" class="input-comp-lot" value="<?= htmlspecialchars($comp['batches'][0]['no_lot']) ?>">
                                                            <input type="hidden" name="komponen[<?= $cIdx ?>][expired_date]" class="input-comp-exp" value="<?= htmlspecialchars($comp['batches'][0]['expired_date'] ?: '') ?>">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center font-weight-bold text-primary">
                                                        <?= number_format($comp['stok_bundling'], 2, ',', '.') ?> <?= htmlspecialchars($comp['satuan']) ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="any" min="0" 
                                                               name="komponen[<?= $cIdx ?>][qty_digunakan]" 
                                                               class="form-control form-control-sm text-center font-weight-bold text-danger input-qty-pakai" 
                                                               value="0" readonly>
                                                    </td>
                                                </tr>
                                                <?php $cIdx++; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="p-4 bg-light border-top">
                                    <div class="row align-items-center">
                                        <div class="col-md-7 text-muted small">
                                            <i class="fas fa-info-circle text-primary mr-1"></i>
                                            <strong>Dampak Transaksi:</strong>
                                            Komponen akan dipotong dari Gudang Bundling, dan Paket Bundling akan bertambah di Gudang Bundling lengkap dengan akumulasi HPP.
                                        </div>
                                        <div class="col-md-5 text-right">
                                            <button type="submit" class="btn btn-success btn-lg px-4 font-weight-bold shadow">
                                                <i class="fas fa-check mr-1"></i> Konfirmasi & Selesaikan Assembly
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
$(document).ready(function() {
    recalcAssemblyQuantities();

    $('#qty_assembly').on('input change', function() {
        recalcAssemblyQuantities();
    });

    $('.select-comp-lot').change(function() {
        let opt = $(this).find(':selected');
        let lot = opt.data('lot');
        let exp = opt.data('exp');
        let row = $(this).closest('td');
        row.find('.input-comp-lot').val(lot);
        row.find('.input-comp-exp').val(exp);
    });

    $('#formAssembly').submit(function(e) {
        e.preventDefault();

        let qtyAsm = parseFloat($('#qty_assembly').val()) || 0;
        if (qtyAsm <= 0) {
            Swal.fire('Peringatan', 'Jumlah paket yang dibuat harus lebih dari 0', 'warning');
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Pembuatan Paket?',
            text: 'Sebanyak ' + qtyAsm + ' Box <?= htmlspecialchars($request["nama_paket"]) ?> akan dirakit. Stok komponen di Gudang Bundling akan berkurang.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Proses Sekarang!',
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
                                window.location.href = '<?= site_url("logistik/bundling/detail/" . $request["id_request"]) ?>';
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

function recalcAssemblyQuantities() {
    let qtyAsm = parseFloat($('#qty_assembly').val()) || 0;
    $('.comp-row').each(function() {
        let isi = parseFloat($(this).data('isi')) || 1;
        let butuh = qtyAsm * isi;
        $(this).find('.input-qty-pakai').val(butuh);
    });
}
</script>
