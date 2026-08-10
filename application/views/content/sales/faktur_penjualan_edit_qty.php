<?php /* views/content/sales/faktur_penjualan_edit_qty.php */ ?>
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <?php if ($mode === 'qty'): ?>
                        <h1 class="m-0"><i class="fas fa-cubes mr-2 text-warning"></i>Edit Qty Faktur Penjualan</h1>
                    <?php elseif ($mode === 'harga'): ?>
                        <h1 class="m-0"><i class="fas fa-tag mr-2 text-info"></i>Edit Total Harga Faktur Penjualan</h1>
                    <?php else: ?>
                        <h1 class="m-0"><i class="fas fa-edit mr-2 text-warning"></i>Edit Qty & Total Harga Faktur Penjualan</h1>
                    <?php endif; ?>
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
                    <div class="card shadow border-top <?= $mode === 'harga' ? 'border-info' : 'border-warning' ?>">
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

                            <div class="alert alert-<?= $mode === 'harga' ? 'info' : 'warning' ?>">
                                <i class="fas fa-info-circle"></i> <strong>Catatan:</strong> 
                                <?php if ($mode === 'qty'): ?>
                                    Halaman ini digunakan khusus untuk mengubah <strong>Kuantitas (Qty)</strong> barang akibat Retur Revisi. Mengubah Qty akan secara otomatis mengkalkulasi ulang Total Harga.
                                <?php elseif ($mode === 'harga'): ?>
                                    Halaman ini digunakan khusus untuk mengubah <strong>Total Harga</strong> faktur penjualan (misal akibat Penyesuaian Nilai Faktur). Perubahan hanya mengupdate Total Harga tanpa mengubah Qty.
                                <?php else: ?>
                                    Halaman ini digunakan khusus untuk mengubah <strong>Kuantitas (Qty)</strong> dan/atau <strong>Total Harga</strong> faktur penjualan.
                                <?php endif; ?>
                            </div>

                            <form method="post" action="<?= base_url('faktur_penjualan/update_qty/' . $faktur['id_faktur']) ?>">
                                <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">
                                <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="<?= $mode === 'harga' ? 'bg-info' : 'bg-secondary' ?> text-white">
                                            <tr>
                                                <th class="text-center" width="50">No</th>
                                                <th>Nama Barang</th>
                                                <th>No Lot / Batch</th>
                                                <th class="text-center">Satuan</th>
                                                <th class="text-right">Harga Satuan</th>
                                                <th class="text-right">Diskon (%)</th>
                                                <th class="text-right">Pajak (%)</th>
                                                <th class="text-center" width="140">Kuantitas (Qty)</th>
                                                <th class="text-right" width="180">Total Harga (Rp)</th>
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
                                                    <input type="number" step="0.001" min="0" name="qty[]" <?= $mode === 'harga' ? 'readonly class="form-control form-control-sm text-right qty-input bg-light"' : 'class="form-control form-control-sm text-right qty-input"' ?> value="<?= (float)$d['qty'] ?>">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" name="total_harga[]" <?= $mode === 'qty' ? 'readonly class="form-control form-control-sm text-right font-weight-bold text-primary total-harga-input bg-light"' : 'class="form-control form-control-sm text-right font-weight-bold text-primary total-harga-input"' ?> value="<?= (float)$d['total_harga'] ?>">
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="7" class="text-right text-middle">Grand Total Faktur:</td>
                                                <td class="text-right text-middle" id="grand-total-qty">0</td>
                                                <td class="text-right text-middle text-primary" id="grand-total-amount" style="font-size: 15px;">Rp 0,00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="form-group mt-3">
                                    <label>Catatan Revisi <span class="text-danger">*</span></label>
                                    <textarea name="catatan_revisi" class="form-control" rows="3" required placeholder="<?= $mode === 'qty' ? 'Tulis alasan edit Qty (misal: Retur Revisi No. RET/001)...' : ($mode === 'harga' ? 'Tulis alasan edit Total Harga (misal: Penyesuaian Nilai Faktur)...' : 'Tulis alasan revisi...') ?>"></textarea>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn <?= $mode === 'harga' ? 'btn-info' : 'btn-warning' ?>" onclick="return confirm('Apakah Anda yakin akan menyimpan perubahan faktur ini?');">
                                        <i class="fas fa-save"></i> <?= $mode === 'qty' ? 'Simpan Perubahan Qty' : ($mode === 'harga' ? 'Simpan Perubahan Harga' : 'Simpan Perubahan') ?>
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
</div> <!-- /.wrapper -->

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

        $(row).find('.total-harga-input').val(total.toFixed(2));
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        var totalQty = 0;
        var totalAmount = 0;

        $('.qty-input').each(function() {
            totalQty += parseFloat($(this).val()) || 0;
        });

        $('.total-harga-input').each(function() {
            totalAmount += parseFloat($(this).val()) || 0;
        });

        $('#grand-total-qty').text(totalQty.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 3 }));
        $('#grand-total-amount').text('Rp ' + totalAmount.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    $('.qty-input').on('input', function() {
        calculateRow($(this).closest('tr'));
    });

    $('.total-harga-input').on('input', function() {
        calculateGrandTotal();
    });

    // Kalkulasi awal
    calculateGrandTotal();
});
</script>
