<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-dolly text-primary mr-2"></i> Mutasi Bahan ke Gudang Bundling
                    </h1>
                    <p class="text-muted mb-0 small">Pemindahan fisik komponen dari Gudang Induk ke Gudang Bundling untuk persiapan perakitan</p>
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
            <form id="formMutasiBahan" method="post" action="<?= site_url('logistik/bundling/execute_mutasi') ?>">
                <input type="hidden" name="id_request" value="<?= $request['id_request'] ?>">

                <!-- Info Header Mutasi -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Referensi Request</span>
                                <strong class="text-primary font-weight-bold"><?= htmlspecialchars($request['no_request']) ?></strong>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Paket Bundling</span>
                                <strong class="text-dark"><?= htmlspecialchars($request['nama_paket']) ?></strong>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Gudang Asal (Pengirim)</span>
                                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                                    <?= htmlspecialchars($request['nama_gudang_asal'] ?: 'Gdg. Induk') ?>
                                </span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small d-block">Gudang Tujuan (Penerima)</span>
                                <span class="badge badge-primary font-weight-bold px-2 py-1">
                                    <?= htmlspecialchars($request['nama_gudang_tujuan'] ?: 'Gdg. Bundling') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Mutasi Bahan -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                        <h6 class="font-weight-bold text-dark m-0">
                            <i class="fas fa-boxes text-primary mr-2"></i> Pilih Komponen & Batch Lot yang Dimutasi
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" style="font-size: 0.92rem;">
                                <thead style="background: #f1f5f9; color: #334155;">
                                    <tr>
                                        <th style="width: 28%;">Komponen Barang</th>
                                        <th style="width: 14%;" class="text-center">Sisa Kebutuhan</th>
                                        <th style="width: 14%;" class="text-center">Stok Bundling Saat Ini</th>
                                        <th style="width: 24%;">Pilih Batch Lot Gudang Induk</th>
                                        <th style="width: 20%;" class="text-center">Jumlah Dimutasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $idx = 0; ?>
                                    <?php foreach ($stock_status as $stk): ?>
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[<?= $idx ?>][kode_barang]" value="<?= htmlspecialchars($stk['kode_barang']) ?>">
                                                <input type="hidden" name="items[<?= $idx ?>][nama_barang]" value="<?= htmlspecialchars($stk['nama_barang']) ?>">
                                                <div class="font-weight-bold text-dark"><?= htmlspecialchars($stk['nama_barang']) ?></div>
                                                <small class="text-muted">Kode: <?= htmlspecialchars($stk['kode_barang']) ?></small>
                                            </td>
                                            <td class="text-center font-weight-bold text-danger">
                                                <?= number_format($stk['sisa_kebutuhan'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                            </td>
                                            <td class="text-center font-weight-bold text-muted">
                                                <?= number_format($stk['stok_gudang_bundling'], 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                            </td>
                                            <td>
                                                <?php if (empty($stk['batches'])): ?>
                                                    <span class="badge badge-danger">Stok di Gudang Induk Kosong!</span>
                                                    <input type="hidden" name="items[<?= $idx ?>][no_lot]" value="-">
                                                    <input type="hidden" name="items[<?= $idx ?>][expired_date]" value="">
                                                <?php else: ?>
                                                    <select class="form-control form-control-sm select-lot" data-target-max="max_<?= $idx ?>">
                                                        <?php foreach ($stk['batches'] as $b): ?>
                                                            <?php $avail = (float)$b['qty_on_hand'] - (float)$b['qty_reserved']; ?>
                                                            <option value="<?= htmlspecialchars($b['no_lot']) ?>" 
                                                                    data-lot="<?= htmlspecialchars($b['no_lot']) ?>" 
                                                                    data-exp="<?= htmlspecialchars($b['expired_date'] ?: '') ?>" 
                                                                    data-avail="<?= $avail ?>">
                                                                Lot: <?= htmlspecialchars($b['no_lot']) ?> (Exp: <?= $b['expired_date'] ?: '-' ?>) - Ada: <?= number_format($avail, 2, ',', '.') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input type="hidden" name="items[<?= $idx ?>][no_lot]" class="input-lot" value="<?= htmlspecialchars($stk['batches'][0]['no_lot']) ?>">
                                                    <input type="hidden" name="items[<?= $idx ?>][expired_date]" class="input-exp" value="<?= htmlspecialchars($stk['batches'][0]['expired_date'] ?: '') ?>">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $defQty = min($stk['stok_gudang_induk'], $stk['kekurangan_di_bundling']);
                                                    if ($defQty <= 0 && $stk['sisa_kebutuhan'] > 0) $defQty = min($stk['stok_gudang_induk'], $stk['sisa_kebutuhan']);
                                                    $maxAvail = !empty($stk['batches']) ? ((float)$stk['batches'][0]['qty_on_hand'] - (float)$stk['batches'][0]['qty_reserved']) : 0;
                                                    $defQty = min($defQty, $maxAvail);
                                                ?>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="any" min="0" max="<?= $maxAvail ?>" 
                                                           name="items[<?= $idx ?>][qty_mutasi]" 
                                                           id="max_<?= $idx ?>"
                                                           class="form-control font-weight-bold text-center input-qty" 
                                                           value="<?= $defQty > 0 ? $defQty : 0 ?>">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><?= htmlspecialchars($stk['satuan']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $idx++; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                <i class="fas fa-info-circle text-primary mr-1"></i>
                                Mutasi akan langsung mengurangi stok di Gudang Induk dan menambah stok di Gudang Bundling.
                            </span>
                            <button type="submit" class="btn btn-primary px-4 font-weight-bold shadow">
                                <i class="fas fa-check-circle mr-1"></i> Konfirmasi & Rekam Mutasi
                            </button>
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
    $('.select-lot').change(function() {
        let opt = $(this).find(':selected');
        let lot = opt.data('lot');
        let exp = opt.data('exp');
        let avail = parseFloat(opt.data('avail')) || 0;
        let targetId = $(this).data('target-max');

        let row = $(this).closest('td');
        row.find('.input-lot').val(lot);
        row.find('.input-exp').val(exp);

        let inputQty = $('#' + targetId);
        inputQty.attr('max', avail);
        if (parseFloat(inputQty.val()) > avail) {
            inputQty.val(avail);
        }
    });

    $('#formMutasiBahan').submit(function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Konfirmasi Mutasi Bahan?',
            text: 'Barang akan dipindahkan dari Gudang Induk ke Gudang Bundling.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Mutasikan!',
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
</script>
