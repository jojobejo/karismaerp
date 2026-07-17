<!-- views/content/sales/faktur_form.php -->
<?php
$jobdesk = strtoupper((string)$this->session->userdata('jobdesk'));
$is_admin_sc_context = in_array($jobdesk, ['ADMINSC', 'SALESCOUNTER'], true);
$so_rute = trim((string)($so['kd_rute'] ?? ($so['customer_kd_rute'] ?? '')));
$faktur_back_url = $is_admin_sc_context
    ? base_url('sales_order/admin_sc' . ($so_rute !== '' ? '?rute=' . rawurlencode($so_rute) : ''))
    : base_url('sales_order/detail/' . $so['id_so']);
?>
<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
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
                        <?php if ($is_admin_sc_context): ?>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order/admin_sc') ?>">Admin SC</a></li>
                            <?php if ($so_rute !== ''): ?>
                                <li class="breadcrumb-item">
                                    <a href="<?= base_url('sales_order/admin_sc?rute=' . rawurlencode($so_rute)) ?>">
                                        Rute <?= htmlspecialchars($so_rute) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="breadcrumb-item">
                                <a href="<?= base_url('sales_order/admin_sc/pilih_barang/' . $so['id_so']) ?>">
                                    Pilih Barang
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                            <li class="breadcrumb-item">
                                <a href="<?= base_url('sales_order/detail/' . $so['id_so']) ?>">
                                    <?= htmlspecialchars($so['no_so']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php if ($is_admin_sc_context): ?>
                            <li class="breadcrumb-item"><?= htmlspecialchars($so['no_so']) ?></li>
                        <?php endif; ?>
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
                &mdash; Customer: <strong><?= htmlspecialchars($so['customer_name']) ?></strong>
                Jenis faktur:
                <strong>
                    <?= (($tax_rate ?? 0) > 0) ? 'Pajak 11% (kode barang Q)' : 'Non Pajak (kode barang bukan Q)' ?>
                    <?php if (!empty($so['is_faktur_z'])): ?>
                        (Faktur Z)
                    <?php endif; ?>
                </strong>.
                Rute / Regional: <strong><?= !empty($so['customer_kd_rute']) ? htmlspecialchars($so['customer_kd_rute']) : '<span class="text-muted">-</span>' ?></strong>
            </div>

            <form action="<?= base_url('sales_order/simpan_faktur/' . $so['id_so']) ?>" method="post"
                  id="formFaktur">
                <input type="hidden" name="tax_mode" value="<?= htmlspecialchars($tax_mode ?? 'non_pajak') ?>">

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
                                    <?php if (!empty($so['is_faktur_z'])): ?>
                                        <small class="text-info">
                                            <i class="fas fa-check-circle mr-1"></i>Mode Faktur Z dari Sales Order.
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Faktur <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal_faktur"
                                           id="tanggalFaktur"
                                           value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Tempo <span class="text-danger">*</span></label>
                                    <select class="form-control" name="jtempo" id="jtempo" required>
                                        <option value="0">0 Hari</option>
                                        <option value="30">30 Hari</option>
                                        <option value="60">60 Hari</option>
                                        <option value="90">90 Hari</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Tgl Jatuh Tempo <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="tanggal_jatuh_tempo"
                                           id="tanggalJatuhTempo"
                                           value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Salesman</label>
                                    <?php
                                    $salesman_value = trim((string)($so['create_by'] ?? ''));
                                    ?>
                                    <input type="text" class="form-control" name="salesman"
                                           value="<?= htmlspecialchars($salesman_value, ENT_QUOTES, 'UTF-8') ?>"
                                           placeholder="Nama salesman" readonly>
                                    <small class="text-muted">Mengikuti pembuat Sales Order.</small>
                                </div>

                                <div class="form-group">
                                    <label>Cara Pembayaran</label>
                                    <?php
                                    $cp_labels = ['cash' => 'Cash', 'transfer' => 'Transfer', 'bg' => 'BG', 'tempo' => 'Tempo'];
                                    $cp_key    = strtolower(trim((string)($so['cara_pembayaran'] ?? 'cash')));
                                    $cp_label  = $cp_labels[$cp_key] ?? ucfirst($cp_key);
                                    ?>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($cp_label) ?>" readonly>
                                    <input type="hidden" name="cara_pembayaran" value="<?= htmlspecialchars($cp_key, ENT_QUOTES, 'UTF-8') ?>">
                                    <small class="text-muted">Mengikuti cara pembayaran Sales Order.</small>
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
                                    Item - Qty Faktur Otomatis
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-sm mb-0" id="tblItem">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="35%">Barang</th>
                                            <th>Lot / Exp</th>
                                            <th class="text-right">Qty Faktur</th>
                                            <th class="text-right">Harga Satuan</th>
                                            <th class="text-right">Subtotal</th>
                                            <th class="text-center" width="8%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($details as $i => $d):
                                            $outstanding = (float)($d['qty_available_faktur'] ?? ((float)$d['qty'] - (float)$d['qty_faktur']));
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
                                            <input type="hidden" name="hrg_pokok[]"      value="<?= $d['hrg_pokok'] ?>">
                                            <input type="hidden" name="disc[]"           value="<?= $d['disc'] ?>">
                                            <input type="hidden" name="pajak[]"          value="<?= (float)($d['pajak'] ?? 0) ?>">
                                            <input type="hidden" name="berat_gram[]"     value="<?= $d['berat_gram'] ?>">
                                            <input type="hidden" name="kubikasi_m3[]"    value="<?= $d['kubikasi_m3'] ?>">
                                            <input type="hidden" name="qty_faktur[]"     class="qty-faktur"
                                                   value="<?= $outstanding ?>"
                                                   data-harga="<?= $d['hrg_satuan'] ?>"
                                                   data-disc="<?= $d['disc'] ?>"
                                                   data-pajak="<?= $d['pajak'] ?? 0 ?>"
                                                   data-row="<?= $i ?>"
                                                   data-isi="<?= $isi ?>"
                                                   data-outstanding="<?= $outstanding ?>">

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
                                                        <span class="text-muted"><?= number_format($outstanding) ?> pcs</span>
                                                    <?php endif; ?>
                                                </strong>
                                                <br>
                                                <small class="text-muted"><?= number_format($outstanding) ?> pcs</small>
                                                <input type="hidden" class="max-outstanding" value="<?= $outstanding ?>">
                                            </td>
                                            <td class="text-right" style="min-width:150px;">
                                                <input type="hidden"
                                                       class="harga-satuan-input"
                                                       name="hrg_satuan[]"
                                                       value="<?= (float)$d['hrg_satuan'] ?>"
                                                       data-row="<?= $i ?>">
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <span class="harga-satuan-text mr-2" data-row="<?= $i ?>">
                                                        Rp <?= number_format($d['hrg_satuan'], 0, ',', '.') ?>
                                                    </span>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary btn-edit-harga"
                                                            data-row="<?= $i ?>"
                                                            data-detail="<?= (int)$d['id_so_detail'] ?>"
                                                            data-barang="<?= htmlspecialchars($d['nama_barang'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                                <?php if ($d['disc'] > 0): ?>
                                                    <br><small class="text-danger">disc <?= $d['disc'] ?>%</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right subtotal" data-row="<?= $i ?>">
                                                Rp 0
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger btn-remove-item" title="Hapus Item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr>
                                            <th colspan="5" class="text-right">Total Nilai Faktur:</th>
                                            <th class="text-right" id="totalNilaiFaktur">Rp 0</th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-right">
                                                Tax: <?= number_format((float)($tax_rate ?? 0), 0) ?>(%)
                                                <?php if (!empty($so['is_faktur_z'])): ?>
                                                    <br><small class="text-muted">Faktur Z</small>
                                                <?php endif; ?>
                                            </th>
                                            <th class="text-right" id="totalTax">Rp 0</th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-right">Grand Total Harga:</th>
                                            <th class="text-right" id="grandTotalHarga">Rp 0</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Card Jurnal -->
                        <div class="card card-outline card-info mt-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calculator mr-1"></i>
                                    Perhitungan Jurnal
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-sm mb-0" style="background-color: #fdfdfd;">
                                    <tbody>
                                        <tr>
                                            <td class="pl-2 py-2" style="font-weight: 500;">piutang dagang</td>
                                            <td class="text-right pr-2 py-2" id="jurnalPiutangDagang" style="font-weight: bold;">Rp 0</td>
                                            <input type="hidden" name="jurnal_piutang_dagang" id="inputJurnalPiutangDagang" value="0">
                                        </tr>
                                        <tr>
                                            <td class="pl-2 py-2" style="font-weight: 500;">penjualan</td>
                                            <td class="text-right pr-2 py-2" id="jurnalPenjualan" style="font-weight: bold;">Rp 0</td>
                                            <input type="hidden" name="jurnal_penjualan" id="inputJurnalPenjualan" value="0">
                                        </tr>
                                        <tr>
                                            <td class="pl-2 py-2" style="font-weight: 500;">ppn keluar</td>
                                            <td class="text-right pr-2 py-2" id="jurnalPpnKeluar" style="font-weight: bold;">Rp 0</td>
                                            <input type="hidden" name="jurnal_ppn_keluar" id="inputJurnalPpnKeluar" value="0">
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol -->
                <div class="row mt-2">
                    <div class="col-12 text-right">
                        <a href="<?= $faktur_back_url ?>"
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

    <div class="modal fade" id="modalEditHarga" tabindex="-1" role="dialog" aria-labelledby="modalEditHargaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditHargaLabel">Edit Harga Satuan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editHargaRow">
                    <input type="hidden" id="editHargaDetail">
                    <div class="form-group">
                        <label>Barang</label>
                        <input type="text" class="form-control" id="editHargaBarang" readonly>
                    </div>
                    <div class="form-group mb-0">
                        <label>Harga Satuan</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" class="form-control text-right" id="editHargaValue" min="0" step="0.01">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanHarga">Simpan Harga</button>
                </div>
            </div>
        </div>
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
    function salesToast(type, message) {
        if (window.Swal) {
            Swal.fire({ toast:true, position:'top-end', icon:type || 'info', title:message || '', timer:2600, showConfirmButton:false });
        } else {
            alert(message || '');
        }
    }

    function salesConfirm(options) {
        options = options || {};
        if (window.Swal) {
            return Swal.fire({
                title: options.title || 'Konfirmasi',
                text: options.text || 'Lanjutkan proses ini?',
                icon: options.icon || 'question',
                showCancelButton: true,
                confirmButtonText: options.confirmText || 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonColor: options.confirmColor || '#28a745'
            }).then(function(result){ return result.isConfirmed; });
        }
        return Promise.resolve(confirm((options.title ? options.title + '\n' : '') + (options.text || 'Lanjutkan proses ini?')));
    }

    function setButtonLoading(button, loading, text) {
        if (!button) return;
        var $btn = $(button);
        if (loading) {
            if (!$btn.data('original-html')) $btn.data('original-html', $btn.html());
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>' + (text || 'Memproses'));
        } else {
            $btn.prop('disabled', false).html($btn.data('original-html'));
        }
    }

    function updateTanggalJatuhTempo() {
        const tanggalFaktur = $('#tanggalFaktur').val();
        const tempo = parseInt($('#jtempo').val(), 10) || 0;
        if (!tanggalFaktur) return;

        const date = new Date(tanggalFaktur + 'T00:00:00');
        date.setDate(date.getDate() + tempo);
        $('#tanggalJatuhTempo').val(date.toISOString().slice(0, 10));
    }

    function hitungSubtotal() {
        let totalNilaiFaktur = 0;
        let totalTax = 0;
        let grandTotalHarga = 0;

        $('.qty-faktur').each(function () {
            const row    = $(this).data('row');
            const qty    = parseFloat($(this).val()) || 0;
            const harga  = parseFloat($('.harga-satuan-input[data-row="' + row + '"]').val()) || 0;
            const disc   = parseFloat($(this).data('disc'))  || 0;
            const pajak  = parseFloat($(this).data('pajak')) || 0;

            const sub    = qty * harga;
            const afterD = sub  * (1 - disc / 100);

            grandTotalHarga += afterD;

            $('[data-row="' + row + '"].subtotal').text(
                'Rp ' + afterD.toLocaleString('id-ID', { minimumFractionDigits: 0 })
            );
        });

        const taxRate = parseFloat('<?= $tax_rate ?? 0 ?>') || 0;
        const divFactor = 1 + (taxRate / 100);

        totalNilaiFaktur = grandTotalHarga / divFactor;
        totalTax = grandTotalHarga - totalNilaiFaktur;

        $('#totalNilaiFaktur').text('Rp ' + Math.round(totalNilaiFaktur).toLocaleString('id-ID', { minimumFractionDigits: 0 }));
        $('#totalTax').text('Rp ' + Math.round(totalTax).toLocaleString('id-ID', { minimumFractionDigits: 0 }));
        $('#grandTotalHarga').text('Rp ' + Math.round(grandTotalHarga).toLocaleString('id-ID', { minimumFractionDigits: 0 }));

        // Hitung Jurnal
        const jurnalPiutang = Math.round(grandTotalHarga);
        const jurnalPenjualan = Math.round(totalNilaiFaktur);
        const jurnalPpnKeluar = jurnalPiutang - jurnalPenjualan;

        $('#jurnalPiutangDagang').text('Rp ' + jurnalPiutang.toLocaleString('id-ID', { minimumFractionDigits: 0 }));
        $('#jurnalPenjualan').text('Rp ' + jurnalPenjualan.toLocaleString('id-ID', { minimumFractionDigits: 0 }));
        $('#jurnalPpnKeluar').text('Rp ' + jurnalPpnKeluar.toLocaleString('id-ID', { minimumFractionDigits: 0 }));

        $('#inputJurnalPiutangDagang').val(jurnalPiutang);
        $('#inputJurnalPenjualan').val(jurnalPenjualan);
        $('#inputJurnalPpnKeluar').val(jurnalPpnKeluar);
    }

    function formatRupiah(value) {
        return 'Rp ' + (parseFloat(value) || 0).toLocaleString('id-ID', { minimumFractionDigits: 0 });
    }

    function setHargaButtonLoading(loading) {
        var $btn = $('#btnSimpanHarga');
        if (loading) {
            if (!$btn.data('original-html')) $btn.data('original-html', $btn.html());
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan');
        } else {
            $btn.prop('disabled', false).html($btn.data('original-html') || 'Simpan Harga');
        }
    }

    // Hitung saat pertama kali dan saat nilai berubah
    updateTanggalJatuhTempo();
    $('#tanggalFaktur, #jtempo').on('change', updateTanggalJatuhTempo);
 
    hitungSubtotal();

    $(document).on('click', '.btn-remove-item', function() {
        if ($('.btn-remove-item').length <= 1) {
            salesToast('warning', 'Faktur harus berisi minimal 1 item.');
            return;
        }
        $(this).closest('tr').remove();
        hitungSubtotal();
    });

    $(document).on('click', '.btn-edit-harga', function() {
        const row = $(this).data('row');
        const harga = $('.harga-satuan-input[data-row="' + row + '"]').val() || 0;
        $('#editHargaRow').val(row);
        $('#editHargaDetail').val($(this).data('detail') || '');
        $('#editHargaBarang').val($(this).data('barang') || '');
        $('#editHargaValue').val(harga);
        $('#modalEditHarga').modal('show');
    });
    $('#modalEditHarga').on('shown.bs.modal', function() {
        $('#editHargaValue').trigger('focus').trigger('select');
    });
    $('#btnSimpanHarga').on('click', function() {
        const row = $('#editHargaRow').val();
        const idSoDetail = $('#editHargaDetail').val();
        const harga = parseFloat($('#editHargaValue').val()) || 0;
        if (harga <= 0) {
            salesToast('error', 'Harga satuan harus lebih dari 0.');
            return;
        }
        if (!idSoDetail) {
            salesToast('error', 'Data item SO tidak valid.');
            return;
        }

        setHargaButtonLoading(true);
        $.ajax({
            url: "<?= base_url('sales_order/admin_sc/update_harga_faktur/' . $so['id_so']) ?>",
            type: "POST",
            dataType: "JSON",
            data: {
                id_so_detail: idSoDetail,
                harga: harga
            },
            success: function(response) {
                if (!response || response.msg !== 'success') {
                    salesToast('error', (response && response.message) || 'Gagal menyimpan harga.');
                    return;
                }

                const savedHarga = parseFloat(response.harga) || harga;
                $('.harga-satuan-input[data-row="' + row + '"]').val(savedHarga);
                $('.harga-satuan-text[data-row="' + row + '"]').text(formatRupiah(savedHarga));
                hitungSubtotal();
                $('#modalEditHarga').modal('hide');
                salesToast('success', response.message || 'Harga SO berhasil diperbarui.');
            },
            error: function(xhr, status, error) {
                salesToast('error', 'Terjadi kesalahan: ' + error);
            },
            complete: function() {
                setHargaButtonLoading(false);
            }
        });
    });
    $(document).on('focus', '#editHargaValue', function() {
        this.select();
    });
    $('#formFaktur').on('keydown', 'input, select, textarea', function(e) {
        if (e.key !== 'Enter' || $(this).is('textarea')) return;
        e.preventDefault();
        const focusables = $('#formFaktur')
            .find('input:not([type="hidden"]), select, textarea, button')
            .filter(':visible:not(:disabled)');
        const idx = focusables.index(this);
        if (idx >= 0 && idx < focusables.length - 1) {
            focusables.eq(idx + 1).focus();
        } else {
            $('#btnSimpanFaktur').focus();
        }
    });
    let fakturSubmitConfirmed = false;

    // Validasi sebelum submit
    $('#formFaktur').on('submit', function (e) {
        hitungSubtotal();
        let adaQty = false;
        let adaMelewati = false;
        let hargaTidakValid = false;

        $('.qty-faktur').each(function () {
            const qty    = parseFloat($(this).val()) || 0;
            const maxQty = parseFloat($(this).data('outstanding')) || 0;
            if (qty > 0)      adaQty = true;
            if (qty > maxQty) adaMelewati = true;
        });
        $('.harga-satuan-input').each(function () {
            if ((parseFloat($(this).val()) || 0) <= 0) {
                hargaTidakValid = true;
            }
        });

        if (!adaQty) {
            e.preventDefault();
            salesToast('warning', 'Minimal 1 item harus memiliki qty lebih dari 0.');
            return;
        }
        if (adaMelewati) {
            e.preventDefault();
            salesToast('error', 'Qty faktur tidak boleh melebihi qty outstanding.');
            return;
        }
        if (hargaTidakValid) {
            e.preventDefault();
            salesToast('error', 'Harga satuan harus lebih dari 0.');
            return;
        }

        if (!fakturSubmitConfirmed) {
            e.preventDefault();
            salesConfirm({
                title: 'Simpan Faktur?',
                text: 'Pastikan data faktur sudah benar.',
                icon: 'question',
                confirmText: 'Ya, simpan faktur',
                confirmColor: '#16a34a'
            }).then(function(ok) {
                if (!ok) return;
                fakturSubmitConfirmed = true;
                $('#formFaktur').trigger('submit');
            });
            return;
        }

        setButtonLoading(document.getElementById('btnSimpanFaktur'), true, 'Menyimpan');
    });
});
</script>
