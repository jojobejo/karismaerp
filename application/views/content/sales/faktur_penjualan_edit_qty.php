<?php /* views/content/sales/faktur_penjualan_edit_qty.php */ ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-edit mr-2 text-warning"></i>Edit Qty Faktur Penjualan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('faktur_penjualan') ?>">Faktur Penjualan</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($faktur['no_faktur']) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow border-top border-warning">
                        <div class="card-header bg-light">
                            <h3 class="card-title font-weight-bold">Informasi Faktur: <?= htmlspecialchars($faktur['no_faktur']) ?></h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless w-50 mb-4">
                                <tr>
                                    <td width="30%"><strong>Tanggal Faktur</strong></td>
                                    <td>: <?= date('d/m/Y', strtotime($faktur['tanggal_faktur'])) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Customer</strong></td>
                                    <td>: <?= htmlspecialchars($faktur['customer_name']) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Salesman</strong></td>
                                    <td>: <?= htmlspecialchars($faktur['salesman']) ?></td>
                                </tr>
                            </table>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> <strong>Catatan:</strong> Halaman ini digunakan khusus untuk mengubah kuantitas (Qty) barang akibat <strong>Retur Revisi</strong>. Perubahan Qty akan secara otomatis mengkalkulasi ulang subtotal dan total harga setiap baris tanpa mengubah harga satuan maupun diskon.
                            </div>

                            <form method="post" action="<?= base_url('faktur_penjualan/update_qty/' . $faktur['id_faktur']) ?>">
                                <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="bg-secondary text-white">
                                            <tr>
                                                <th class="text-center" width="50">No</th>
                                                <th>Nama Barang</th>
                                                <th>No Lot / Batch</th>
                                                <th class="text-center">Satuan</th>
                                                <th class="text-right">Harga Satuan</th>
                                                <th class="text-right">Diskon (%)</th>
                                                <th class="text-right">Pajak (%)</th>
                                                <th class="text-center" width="150">Kuantitas (Qty)</th>
                                                <th class="text-right">Total Harga</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($details as $i => $d): ?>
                                            <tr>
                                                <td class="text-center text-middle"><?= $i + 1 ?></td>
                                                <td class="text-middle">
                                                    <?= htmlspecialchars($d['nama_barang']) ?>
                                                    <input type="hidden" name="id_detail[]" value="<?= $d['id'] ?>">
                                                </td>
                                                <td class="text-middle"><?= htmlspecialchars($d['no_lot']) ?></td>
                                                <td class="text-center text-middle"><?= htmlspecialchars($d['satuan']) ?></td>
                                                <td class="text-right text-middle harga-satuan" data-val="<?= (float)$d['hrg_satuan'] ?>">
                                                    Rp <?= number_format((float)$d['hrg_satuan'], 2, ',', '.') ?>
                                                </td>
                                                <td class="text-right text-middle disc-val" data-val="<?= (float)$d['disc'] ?>">
                                                    <?= (float)$d['disc'] ?>%
                                                </td>
                                                <td class="text-right text-middle pajak-val" data-val="<?= (float)$d['pajak'] ?>">
                                                    <?= (float)$d['pajak'] ?>%
                                                </td>
                                                <td>
                                                    <input type="number" step="0.001" min="0" name="qty[]" class="form-control form-control-sm text-right qty-input" value="<?= (float)$d['qty'] ?>">
                                                </td>
                                                <td class="text-right text-middle total-harga font-weight-bold">
                                                    Rp <?= number_format((float)$d['total_harga'], 2, ',', '.') ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Catatan Revisi <span class="text-danger">*</span></label>
                                    <textarea name="catatan_revisi" class="form-control" rows="3" required placeholder="Tulis alasan edit qty (misal: Retur Revisi No. RET/001)..."></textarea>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Apakah Anda yakin akan mengubah Qty faktur ini?');">
                                        <i class="fas fa-save"></i> Simpan Perubahan Qty
                                    </button>
                                    <a href="<?= base_url('faktur_penjualan') ?>" class="btn btn-light ml-2">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    function calculateRow(row) {
        var qty = parseFloat($(row).find('.qty-input').val()) || 0;
        var harga = parseFloat($(row).find('.harga-satuan').data('val')) || 0;
        var disc = parseFloat($(row).find('.disc-val').data('val')) || 0;
        var pajak = parseFloat($(row).find('.pajak-val').data('val')) || 0;

        var sub_before = qty * harga;
        var disc_val = sub_before * (disc / 100);
        var sub_after = sub_before - disc_val;
        var pajak_val = sub_after * (pajak / 100);
        var total = sub_after + pajak_val;

        $(row).find('.total-harga').text('Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    $('.qty-input').on('input', function() {
        calculateRow($(this).closest('tr'));
    });
});
</script>
