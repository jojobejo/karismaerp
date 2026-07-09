<!-- views/content/sales/retur/retur_penjualan_form.php -->
<!-- Digunakan oleh Logistik untuk membuat Retur Penjualan dari SPR yang sudah selesai -->
<style>
    .rp-card-header { background: linear-gradient(135deg,#1a6b3c,#27ae60); color:#fff; }
    .table-rp th { background:#f8f9fa; font-size:12px; border:1px solid #dee2e6; }
    .table-rp td { font-size:12px; border:1px solid #dee2e6; vertical-align:middle; }
    .spr-ref-box { background:#fffef0; border:1px solid #f0d060; border-radius:4px; padding:10px 14px; font-size:12px; }
    .qty-input { width:90px; }
    .harga-input { width:130px; }
    .price-info { font-size:11px; color:#888; }
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
                        <h1 class="m-0">
                            <i class="fas fa-undo-alt mr-2 text-success"></i>
                            Buat Retur Penjualan
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Buat Retur</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- FLASH -->
                <?php foreach (['success'=>'success','error'=>'danger'] as $k=>$c): ?>
                    <?php if ($msg = $this->session->flashdata($k)): ?>
                        <div class="alert alert-<?= $c ?> alert-dismissible">
                            <i class="fas fa-<?= $k==='success'?'check-circle':'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- INFO REFERENSI SPR -->
                <div class="spr-ref-box mb-3">
                    <i class="fas fa-link mr-1 text-warning"></i>
                    <strong>Berdasarkan SPR:</strong>
                    <strong class="text-danger ml-1"><?= htmlspecialchars($spr['no_spr']) ?></strong>
                    &mdash; <?= date('d/m/Y', strtotime($spr['tanggal'])) ?>
                    &mdash; Customer: <strong><?= htmlspecialchars($spr['nama_customer'] ?: ($spr['nama_customer_master'] ?? '-')) ?></strong>
                    &mdash; Sales: <?= htmlspecialchars($spr['nama_sales'] ?? '-') ?>
                </div>

                <form action="<?= base_url('retur_penjualan/retur/simpan/' . $spr['id_spr']) ?>" method="post" id="formReturPenjualan">
                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-8">
                            <!-- CARD HEADER RETUR -->
                            <div class="card shadow">
                                <div class="card-header rp-card-header py-2">
                                    <h3 class="card-title"><i class="fas fa-undo-alt mr-1"></i> Data Retur Penjualan</h3>
                                </div>
                                <div class="card-body">

                                    <!-- INFO OTOMATIS -->
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold mb-1">No. Retur Penjualan</label>
                                                <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($no_retur) ?>" readonly style="background:#f5f5f5;">
                                                <input type="hidden" name="no_retur" value="<?= htmlspecialchars($no_retur) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold mb-1">Tanggal Retur <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control form-control-sm" name="tanggal_retur" value="<?= date('Y-m-d') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold mb-1">No. SPR Referensi</label>
                                                <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['no_spr']) ?>" readonly style="background:#f5f5f5;">
                                                <input type="hidden" name="id_spr" value="<?= $spr['id_spr'] ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold mb-1">Customer</label>
                                                <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['nama_customer'] ?: ($spr['nama_customer_master'] ?? '-')) ?>" readonly style="background:#f5f5f5;">
                                                <input type="hidden" name="kd_customer" value="<?= htmlspecialchars($spr['kd_customer'] ?? '') ?>">
                                                <input type="hidden" name="nama_customer" value="<?= htmlspecialchars($spr['nama_customer'] ?: ($spr['nama_customer_master'] ?? '')) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold mb-1">Alamat</label>
                                                <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['alamat'] ?: ($spr['alamat_master'] ?? '-')) ?>" readonly style="background:#f5f5f5;">
                                                <input type="hidden" name="alamat" value="<?= htmlspecialchars($spr['alamat'] ?: ($spr['alamat_master'] ?? '')) ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold mb-1">Sales</label>
                                                <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['nama_sales'] ?? '-') ?>" readonly style="background:#f5f5f5;">
                                                <input type="hidden" name="nama_sales" value="<?= htmlspecialchars($spr['nama_sales'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold mb-1">Dibuat oleh (Logistik)</label>
                                                <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($user['nama']) ?>" readonly style="background:#f5f5f5;">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold mb-1">Catatan Logistik</label>
                                        <textarea class="form-control form-control-sm" name="catatan_logistik" rows="2"
                                                  placeholder="Catatan kondisi barang saat diterima dari driver..."></textarea>
                                    </div>

                                    <!-- TABEL DETAIL BARANG -->
                                    <h6 class="font-weight-bold text-success mb-2">
                                        <i class="fas fa-boxes mr-1"></i> Detail Barang Retur
                                        <small class="text-muted font-weight-normal">(sesuaikan qty & isi harga jual)</small>
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm table-rp" id="tabelRetur">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" style="width:36px;">No.</th>
                                                    <th>Nama Barang</th>
                                                    <th style="width:110px;">No. Faktur</th>
                                                    <th style="width:100px;">No. Batch/Lot</th>
                                                    <th style="width:90px;">Qty Retur <span class="text-danger">*</span></th>
                                                    <th style="width:140px;">Harga Satuan <span class="text-danger">*</span></th>
                                                    <th style="width:130px;">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($spr_detail as $i => $d): ?>
                                                <tr class="retur-row">
                                                    <td class="text-center"><?= $i + 1 ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($d['nama_barang'] ?? '-') ?>
                                                        <input type="hidden" name="nama_barang[]" value="<?= htmlspecialchars($d['nama_barang'] ?? '') ?>">
                                                        <input type="hidden" name="no_faktur[]" value="<?= htmlspecialchars($d['no_faktur'] ?? '') ?>">
                                                        <input type="hidden" name="no_batch[]" value="<?= htmlspecialchars($d['no_batch'] ?? '') ?>">
                                                        <input type="hidden" name="id_spr_detail[]" value="<?= $d['id_spr_detail'] ?>">
                                                    </td>
                                                    <td class="small"><?= htmlspecialchars($d['no_faktur'] ?? '-') ?></td>
                                                    <td class="small"><?= htmlspecialchars($d['no_batch'] ?? '-') ?></td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm qty-input qty-retur"
                                                               name="qty_retur[]"
                                                               value="<?= number_format((float)$d['qty'], 3, '.', '') ?>"
                                                               min="0.001" max="<?= number_format((float)$d['qty'], 3, '.', '') ?>"
                                                               step="0.001" required>
                                                        <div class="price-info">SPR: <?= number_format((float)$d['qty'], 3) ?></div>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm harga-input harga-satuan"
                                                               name="harga_satuan[]"
                                                               value=""
                                                               min="0" step="1"
                                                               placeholder="0"
                                                               required>
                                                    </td>
                                                    <td class="text-right font-weight-bold total-cell" style="color:#27ae60;">
                                                        <span class="total-display">0</span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr style="background:#f0fff4;">
                                                    <td colspan="6" class="text-right font-weight-bold">Total Nilai Retur:</td>
                                                    <td class="text-right font-weight-bold text-success" id="grandTotal">Rp 0</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                </div>
                                <div class="card-footer d-flex justify-content-between">
                                    <a href="<?= base_url('retur_penjualan/detail/' . $spr['id_spr']) ?>" class="btn btn-light">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-success" id="btnSimpanRetur">
                                        <i class="fas fa-save"></i> Simpan Retur Penjualan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- SIDEBAR INFO -->
                        <div class="col-lg-4">
                            <div class="card shadow border-success" style="border-width:2px !important;">
                                <div class="card-header bg-success text-white py-2">
                                    <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi</h3>
                                </div>
                                <div class="card-body p-3">
                                    <p class="small mb-2">
                                        <i class="fas fa-lightbulb text-warning mr-1"></i>
                                        <strong>Panduan Pengisian:</strong>
                                    </p>
                                    <ul class="small pl-3 mb-3">
                                        <li>Qty Retur tidak boleh melebihi qty pada SPR</li>
                                        <li>Isi <strong>Harga Satuan</strong> sesuai harga jual di faktur customer</li>
                                        <li>Total nilai retur akan dihitung otomatis</li>
                                    </ul>

                                    <hr class="my-2">
                                    <p class="small font-weight-bold mb-1">Alur setelah disimpan:</p>
                                    <div class="small">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge badge-success mr-2">1</span>
                                            <span>Logistik buat Retur Penjualan <i class="fas fa-check text-success ml-1"></i></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge badge-secondary mr-2">2</span>
                                            <span>Admin Stock verifikasi barang & harga</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge badge-secondary mr-2">3</span>
                                            <span>Team Collection tentukan potongan faktur</span>
                                        </div>
                                    </div>

                                    <hr class="my-2">
                                    <div class="small">
                                        <div class="font-weight-bold mb-1">Ringkasan SPR:</div>
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr><td class="py-0 pl-0 text-muted">No. SPR</td><td class="py-0 font-weight-bold"><?= htmlspecialchars($spr['no_spr']) ?></td></tr>
                                            <tr><td class="py-0 pl-0 text-muted">Jumlah Item</td><td class="py-0"><?= count($spr_detail) ?> barang</td></tr>
                                            <tr><td class="py-0 pl-0 text-muted">Disetujui Kadep</td><td class="py-0"><?= $spr['kadep_sc_by'] ? htmlspecialchars($spr['kadep_sc_by']) : '-' ?></td></tr>
                                            <tr><td class="py-0 pl-0 text-muted">Diproses Logistik</td><td class="py-0"><?= $spr['logistik_by'] ? htmlspecialchars($spr['logistik_by']) : '-' ?></td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(document).ready(function() {
    function formatRupiah(n) {
        return 'Rp ' + Math.round(n).toLocaleString('id-ID');
    }

    function hitungTotal() {
        var grand = 0;
        $('.retur-row').each(function() {
            var qty   = parseFloat($(this).find('.qty-retur').val()) || 0;
            var harga = parseFloat($(this).find('.harga-satuan').val()) || 0;
            var total = qty * harga;
            $(this).find('.total-display').text(formatRupiah(total));
            grand += total;
        });
        $('#grandTotal').text(formatRupiah(grand));
    }

    $(document).on('input', '.qty-retur, .harga-satuan', function() {
        hitungTotal();
    });

    hitungTotal();

    $('#formReturPenjualan').on('submit', function(e) {
        var valid = true;
        $('.harga-satuan').each(function() {
            if (!$(this).val() || parseFloat($(this).val()) <= 0) {
                valid = false;
            }
        });
        if (!valid) {
            e.preventDefault();
            alert('Harga satuan wajib diisi untuk semua barang retur.');
            return;
        }
        if (!confirm('Simpan Retur Penjualan ini? Setelah disimpan akan masuk ke antrian verifikasi Admin Stock.')) {
            e.preventDefault();
        }
    });
});
</script>
