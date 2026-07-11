<?php /* views/content/sales/retur/retur_form.php */ ?>
<style>
    .table-retur th { background: #343a40; color: #fff; font-size: 12px; }
    .table-retur td { font-size: 12px; vertical-align: middle; }
    .field-retur input[readonly] { background: #f5f5f5; color: #555; }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-undo-alt mr-2 text-success"></i> Buat Retur Penjualan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan/admlpb2') ?>">SPR Siap Retur</a></li>
                            <li class="breadcrumb-item active">Buat Retur</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php foreach (['success' => 'success', 'error' => 'danger'] as $k => $c): ?>
                    <?php if ($msg = $this->session->flashdata($k)): ?>
                        <div class="alert alert-<?= $c ?> alert-dismissible">
                            <?= $msg ?>
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <form method="post" action="<?= base_url('retur_penjualan/retur/simpan/' . $spr['id_spr']) ?>">
                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <?php endif; ?>

                    <!-- HEADER INFO -->
                    <div class="card shadow mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi Retur Penjualan</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">No. Retur</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($no_retur) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">Dari SPR</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['no_spr']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">Tanggal Retur</label>
                                        <div class="col-sm-8">
                                            <input type="date" name="tanggal_retur" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">Customer</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['nama_customer'] ?: ($spr['nama_customer_master'] ?? '-')) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">Alamat</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['alamat'] ?: ($spr['alamat_master'] ?? '-')) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">Sales</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['nama_sales'] ?? '-') ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold">Catatan (Opsional)</label>
                                        <textarea name="catatan_admlpb2" class="form-control form-control-sm" rows="2" placeholder="Catatan tambahan..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABEL DETAIL BARANG (dari SPR) -->
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white py-2">
                            <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Detail Barang Retur (dari SPR <?= htmlspecialchars($spr['no_spr']) ?>)</h3>
                            <div class="card-tools">
                                <span class="badge badge-light"><?= count($spr_detail) ?> item</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-retur mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:40px;">No.</th>
                                            <th>Nama Barang</th>
                                            <th>Satuan</th>
                                            <th>No. Faktur</th>
                                            <th>No. Batch/Lot</th>
                                            <th>Expired Date</th>
                                            <th class="text-center" style="width:100px;">Qty Retur</th>
                                            <th class="text-right" style="width:130px;">Harga Satuan</th>
                                            <th class="text-right" style="width:140px;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($spr_detail)): ?>
                                            <tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data barang dalam SPR ini</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($spr_detail as $i => $d): ?>
                                            <tr class="item-row">
                                                <td class="text-center"><?= $i + 1 ?></td>
                                                <td>
                                                    <input type="hidden" name="id_spr_detail[]" value="<?= $d['id_spr_detail'] ?>">
                                                    <input type="text" name="nama_barang[]" class="form-control form-control-sm field-retur"
                                                           value="<?= htmlspecialchars($d['nama_barang'] ?? '') ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="satuan[]" class="form-control form-control-sm field-retur"
                                                           value="<?= htmlspecialchars($d['satuan'] ?? '') ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="no_faktur[]" class="form-control form-control-sm field-retur"
                                                           value="<?= htmlspecialchars($d['no_faktur'] ?? '') ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="no_batch[]" class="form-control form-control-sm field-retur"
                                                           value="<?= htmlspecialchars($d['no_batch'] ?? '') ?>" readonly>
                                                </td>
                                                <td>
                                                    <?php $exp = $d['expired_date'] ?? null; ?>
                                                    <input type="date" name="expired_date[]" class="form-control form-control-sm"
                                                           value="<?= $exp ? date('Y-m-d', strtotime($exp)) : '' ?>">
                                                </td>
                                                <td>
                                                    <input type="number" name="qty_retur[]" class="form-control form-control-sm text-right qty-input"
                                                           value="<?= (float)($d['qty'] ?? 0) ?>" min="0.001" step="0.001" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="harga_satuan[]" class="form-control form-control-sm text-right harga-input"
                                                           value="<?= (float)($d['harga'] ?? 0) ?>" min="0" step="0.01" required>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm text-right subtotal-input"
                                                           value="Rp 0" readonly>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <tr class="table-dark">
                                            <td colspan="8" class="text-right font-weight-bold">TOTAL NILAI RETUR:</td>
                                            <td class="text-right font-weight-bold" id="grand-total">Rp 0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="<?= base_url('retur_penjualan/admlpb2') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success" onclick="return confirm('Simpan Retur Penjualan ini? SPR akan otomatis berubah status menjadi Selesai.')">
                                <i class="fas fa-save"></i> Simpan Retur Penjualan
                            </button>
                        </div>
                    </div>
                </form>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    function calculateSubtotal(row) {
                        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                        const harga = parseFloat(row.querySelector('.harga-input').value) || 0;
                        const subtotal = qty * harga;
                        
                        row.querySelector('.subtotal-input').value = 'Rp ' + subtotal.toLocaleString('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2
                        });
                        return subtotal;
                    }

                    function calculateGrandTotal() {
                        let grandTotal = 0;
                        const rows = document.querySelectorAll('.item-row');
                        rows.forEach(row => {
                            grandTotal += calculateSubtotal(row);
                        });
                        document.getElementById('grand-total').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2
                        });
                    }

                    const rows = document.querySelectorAll('.item-row');
                    rows.forEach(row => {
                        // Recalculate on inputs change
                        row.querySelector('.qty-input').addEventListener('input', calculateGrandTotal);
                        row.querySelector('.harga-input').addEventListener('input', calculateGrandTotal);
                    });

                    // Initial calculation
                    calculateGrandTotal();
                });
                </script>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
