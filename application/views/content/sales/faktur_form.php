<!-- views/content/sales/faktur_form.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>
                        Buat Faktur Penjualan
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                        <li class="breadcrumb-item">
                            <a href="<?= base_url('sales_order/detail/' . $so['id_so']) ?>">
                                <?= htmlspecialchars($so['no_so']) ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Buat Faktur</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- Flash -->
            <?php if ($msg = $this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $msg ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>

            <!-- Info SO -->
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle mr-1"></i>
                Membuat Faktur Penjualan dari SO <strong><?= htmlspecialchars($so['no_so']) ?></strong>
                &mdash; Customer: <strong><?= htmlspecialchars($so['customer_name']) ?></strong>.
                Anda dapat mengisi qty sebagian (parsial) sesuai stok yang tersedia.
                Qty tidak boleh melebihi kolom <em>Outstanding</em>.
            </div>

            <form action="<?= base_url('sales_order/simpan_faktur/' . $so['id_so']) ?>" method="post"
                  id="formFaktur">

                <div class="row">
                    <!-- Kolom kiri: header faktur -->
                    <div class="col-md-4">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Header Faktur</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>No. Faktur <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="no_faktur"
                                           value="<?= htmlspecialchars($no_faktur) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Faktur <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal_faktur"
                                           value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <textarea class="form-control" name="catatan" rows="3"
                                              placeholder="Catatan pengiriman (opsional)..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom kanan: item -->
                    <div class="col-md-8">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-boxes mr-1"></i>
                                    Item — Isi Qty yang Akan Difakturkan
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-sm mb-0" id="tblItem">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="35%">Barang</th>
                                            <th>Lot / Exp</th>
                                            <th class="text-right">Outstanding</th>
                                            <th class="text-right">
                                                Qty Faktur
                                                <br><small class="text-muted">(maks = outstanding)</small>
                                            </th>
                                            <th class="text-right">Harga Satuan</th>
                                            <th class="text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($details as $i => $d):
                                            $outstanding = (float)$d['qty'] - (float)$d['qty_faktur'];
                                            $isi         = max(1, (int)($d['isi_per_box'] ?? 1));
                                            $out_box     = floor($outstanding / $isi);
                                            $out_pcs     = fmod($outstanding, $isi);
                                        ?>
                                        <tr>
                                            <!-- Hidden fields -->
                                            <input type="hidden" name="id_so_detail[]"  value="<?= $d['id_so_detail'] ?>">
                                            <input type="hidden" name="kd_barang[]"      value="<?= htmlspecialchars($d['kd_barang']) ?>">
                                            <input type="hidden" name="nama_barang[]"    value="<?= htmlspecialchars($d['nama_barang']) ?>">
                                            <input type="hidden" name="no_lot[]"         value="<?= htmlspecialchars($d['no_lot'] ?? '') ?>">
                                            <input type="hidden" name="expired_date[]"   value="<?= htmlspecialchars($d['expired_date'] ?? '') ?>">
                                            <input type="hidden" name="isi_per_box[]"    value="<?= $isi ?>">
                                            <input type="hidden" name="satuan[]"         value="<?= htmlspecialchars($d['satuan'] ?? '') ?>">
                                            <input type="hidden" name="hrg_satuan[]"     value="<?= $d['hrg_satuan'] ?>">
                                            <input type="hidden" name="hrg_pokok[]"      value="<?= $d['hrg_pokok'] ?>">
                                            <input type="hidden" name="disc[]"           value="<?= $d['disc'] ?>">
                                            <input type="hidden" name="pajak[]"          value="<?= $d['pajak'] ?? 0 ?>">
                                            <input type="hidden" name="berat_gram[]"     value="<?= $d['berat_gram'] ?>">
                                            <input type="hidden" name="kubikasi_m3[]"    value="<?= $d['kubikasi_m3'] ?>">

                                            <td>
                                                <strong><?= htmlspecialchars($d['nama_barang']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($d['kd_barang']) ?></small>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php if (!empty($d['no_lot'])): ?>
                                                        Lot: <code><?= htmlspecialchars($d['no_lot']) ?></code><br>
                                                    <?php endif; ?>
                                                    Exp: <?= !empty($d['expired_date']) ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?>
                                                </small>
                                            </td>
                                            <td class="text-right">
                                                <strong class="text-danger">
                                                    <?= $out_box > 0 ? $out_box . ' box' : '' ?>
                                                    <?= $out_pcs > 0 ? ($out_box > 0 ? '+ ' : '') . (int)$out_pcs . ' pcs' : '' ?>
                                                    <?php if ($out_box == 0 && $out_pcs == 0): ?>
                                                        <span class="text-muted"><?= $outstanding ?></span>
                                                    <?php endif; ?>
                                                </strong>
                                                <br>
                                                <small class="text-muted">= <?= number_format($outstanding) ?> pcs</small>
                                                <!-- Simpan nilai max outstanding sebagai data attr -->
                                                <input type="hidden" class="max-outstanding" value="<?= $outstanding ?>">
                                            </td>
                                            <td class="text-right" style="min-width:130px;">
                                                <div class="input-group input-group-sm">
                                                    <input type="number"
                                                           class="form-control text-right qty-faktur"
                                                           name="qty_faktur[]"
                                                           value="<?= $outstanding ?>"
                                                           min="0"
                                                           max="<?= $outstanding ?>"
                                                           step="1"
                                                           data-harga="<?= $d['hrg_satuan'] ?>"
                                                           data-disc="<?= $d['disc'] ?>"
                                                           data-pajak="<?= $d['pajak'] ?? 0 ?>"
                                                           data-row="<?= $i ?>">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">pcs</span>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    maks <?= number_format($outstanding) ?> pcs
                                                </small>
                                            </td>
                                            <td class="text-right">
                                                Rp <?= number_format($d['hrg_satuan'], 0, ',', '.') ?>
                                                <?php if ($d['disc'] > 0): ?>
                                                    <br><small class="text-danger">disc <?= $d['disc'] ?>%</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right subtotal" data-row="<?= $i ?>">
                                                Rp 0
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr>
                                            <th colspan="5" class="text-right">Total Nilai Faktur:</th>
                                            <th class="text-right" id="grandTotal">Rp 0</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol -->
                <div class="row mt-2">
                    <div class="col-12 text-right">
                        <a href="<?= base_url('sales_order/detail/' . $so['id_so']) ?>"
                           class="btn btn-secondary mr-2">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-success" id="btnSimpanFaktur">
                            <i class="fas fa-save"></i> Simpan Faktur Penjualan
                        </button>
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
$(document).ready(function () {

    function hitungSubtotal() {
        let grandTotal = 0;

        $('.qty-faktur').each(function () {
            const row    = $(this).data('row');
            const qty    = parseFloat($(this).val()) || 0;
            const harga  = parseFloat($(this).data('harga')) || 0;
            const disc   = parseFloat($(this).data('disc'))  || 0;
            const pajak  = parseFloat($(this).data('pajak')) || 0;
            const maxQty = parseFloat($(this).attr('max'))   || 0;

            // Paksa tidak melebihi max outstanding
            if (qty > maxQty) {
                $(this).val(maxQty);
                return;
            }

            const sub    = qty * harga;
            const afterD = sub  * (1 - disc / 100);
            const total  = afterD * (1 + pajak / 100);

            grandTotal += total;

            $('[data-row="' + row + '"].subtotal').text(
                'Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 0 })
            );
        });

        $('#grandTotal').text('Rp ' + grandTotal.toLocaleString('id-ID', { minimumFractionDigits: 0 }));
    }

    // Hitung saat pertama kali dan saat nilai berubah
    hitungSubtotal();
    $(document).on('input change', '.qty-faktur', hitungSubtotal);

    // Validasi sebelum submit
    $('#formFaktur').on('submit', function (e) {
        let adaQty = false;
        let adaMelewati = false;

        $('.qty-faktur').each(function () {
            const qty    = parseFloat($(this).val()) || 0;
            const maxQty = parseFloat($(this).attr('max')) || 0;
            if (qty > 0)      adaQty = true;
            if (qty > maxQty) adaMelewati = true;
        });

        if (!adaQty) {
            e.preventDefault();
            Swal.fire('Peringatan', 'Minimal 1 item harus memiliki qty lebih dari 0.', 'warning');
            return;
        }
        if (adaMelewati) {
            e.preventDefault();
            Swal.fire('Error', 'Qty faktur tidak boleh melebihi qty outstanding.', 'error');
            return;
        }

        $('#btnSimpanFaktur').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    });
});
</script>