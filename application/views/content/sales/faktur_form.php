<!-- views/content/sales/faktur_form.php -->
<?php
$jobdesk = strtoupper((string)$this->session->userdata('jobdesk'));
$is_admin_sc_context = in_array($jobdesk, ['ADMINSC', 'SALESCOUNTER'], true);
$faktur_back_url = $is_admin_sc_context
    ? base_url('sales_order/admin_sc/pilih_barang/' . $so['id_so'])
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
                Jenis faktur: <strong><?= (($tax_rate ?? 0) > 0) ? 'Pajak 11% (kode barang Q)' : 'Non Pajak (kode barang bukan Q)' ?></strong>.
                Rute / Regional: <strong><?= !empty($so['customer_kd_rute']) ? htmlspecialchars($so['customer_kd_rute']) : '<span class="text-muted">-</span>' ?></strong>
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
                                    <input type="text" class="form-control" name="salesman"
                                           value="<?= htmlspecialchars($this->session->userdata('nama') ?? $this->session->userdata('username') ?? '') ?>"
                                           placeholder="Nama salesman">
                                </div>
                                <div class="form-group">
                                    <label>Cara Pembayaran <span class="text-danger">*</span></label>
                                    <select class="form-control" name="cara_pembayaran" required>
                                        <option value="cash">Cash</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="bg">BG</option>
                                        <option value="bonus">Bonus</option>
                                    </select>
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
                                            <th class="text-right">Siap Faktur</th>
                                            <th class="text-right">
                                                Qty Faktur
                                                <br><small class="text-muted">(maks = siap faktur)</small>
                                            </th>
                                            <th class="text-right">Harga Satuan</th>
                                            <th class="text-right">Subtotal</th>
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
                                            <input type="hidden" name="hrg_satuan[]"     value="<?= $d['hrg_satuan'] ?>">
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
                                                        <span class="text-muted"><?= $outstanding ?></span>
                                                    <?php endif; ?>
                                                </strong>
                                                <br>
                                                <small class="text-muted">= <?= number_format($outstanding) ?> pcs</small>
                                                <!-- Simpan nilai max siap faktur sebagai data attr -->
                                                <input type="hidden" class="max-outstanding" value="<?= $outstanding ?>">
                                            </td>
                                            <td class="text-right" style="min-width:180px;">
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <div class="input-group input-group-sm mr-1" style="max-width:86px;">
                                                        <input type="number"
                                                               class="form-control text-right qty-box-input"
                                                               name="qty_box_input[]"
                                                               value="<?= $out_box ?>"
                                                               min="0"
                                                               step="1"
                                                               data-row="<?= $i ?>">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">box</span>
                                                        </div>
                                                    </div>
                                                    <div class="input-group input-group-sm" style="max-width:86px;">
                                                        <input type="number"
                                                               class="form-control text-right qty-pcs-input"
                                                               name="qty_pcs_input[]"
                                                               value="<?= (int)$out_pcs ?>"
                                                               min="0"
                                                               step="1"
                                                               data-row="<?= $i ?>">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">pcs</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <small class="text-muted qty-helper" data-row="<?= $i ?>">
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
                                            <th class="text-right" id="totalNilaiFaktur">Rp 0</th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-right">Tax: <?= number_format((float)($tax_rate ?? 0), 0) ?>(%)</th>
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

    function syncQty(row) {
        const $hidden = $('.qty-faktur[data-row="' + row + '"]');
        const $boxInput = $('.qty-box-input[data-row="' + row + '"]');
        const $pcsInput = $('.qty-pcs-input[data-row="' + row + '"]');
        const $helper = $('.qty-helper[data-row="' + row + '"]');
        const isi = parseFloat($hidden.data('isi')) || 1;
        const outstanding = parseFloat($hidden.data('outstanding')) || 0;
        let qtyBox = parseFloat($boxInput.val()) || 0;
        let qtyPcsSisa = parseFloat($pcsInput.val()) || 0;
        let qtyPcs = (qtyBox * isi) + qtyPcsSisa;

        if (qtyPcs > outstanding) {
            qtyPcs = outstanding;
            qtyBox = Math.floor(outstanding / isi);
            qtyPcsSisa = outstanding - (qtyBox * isi);
            $boxInput.val(qtyBox);
            $pcsInput.val(qtyPcsSisa);
        }

        $hidden.val(qtyPcs);

        const isPartial = qtyPcs > 0 && qtyPcs < outstanding;
        $boxInput.add($pcsInput).toggleClass('text-danger font-weight-bold', isPartial);
        $helper
            .toggleClass('text-danger font-weight-bold', isPartial)
            .text('total ' + qtyPcs.toLocaleString('id-ID') + ' pcs, maks ' + outstanding.toLocaleString('id-ID') + ' pcs');
    }

    function syncAllQty() {
        $('.qty-faktur').each(function() {
            syncQty($(this).data('row'));
        });
    }

    function hitungSubtotal() {
        let totalNilaiFaktur = 0;
        let totalTax = 0;
        let grandTotalHarga = 0;

        $('.qty-faktur').each(function () {
            const row    = $(this).data('row');
            const qty    = parseFloat($(this).val()) || 0;
            const harga  = parseFloat($(this).data('harga')) || 0;
            const disc   = parseFloat($(this).data('disc'))  || 0;
            const pajak  = parseFloat($(this).data('pajak')) || 0;

            const sub    = qty * harga;
            const afterD = sub  * (1 - disc / 100);
            const tax    = afterD * (pajak / 100);
            const total  = afterD + tax;

            totalNilaiFaktur += afterD;
            totalTax += tax;
            grandTotalHarga += total;

            $('[data-row="' + row + '"].subtotal').text(
                'Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 0 })
            );
        });

        $('#totalNilaiFaktur').text('Rp ' + totalNilaiFaktur.toLocaleString('id-ID', { minimumFractionDigits: 0 }));
        $('#totalTax').text('Rp ' + totalTax.toLocaleString('id-ID', { minimumFractionDigits: 0 }));
        $('#grandTotalHarga').text('Rp ' + grandTotalHarga.toLocaleString('id-ID', { minimumFractionDigits: 0 }));
    }

    // Hitung saat pertama kali dan saat nilai berubah
    updateTanggalJatuhTempo();
    $('#tanggalFaktur, #jtempo').on('change', updateTanggalJatuhTempo);

    syncAllQty();
    hitungSubtotal();
    $(document).on('input change', '.qty-box-input, .qty-pcs-input', function() {
        syncQty($(this).data('row'));
        hitungSubtotal();
    });
    $(document).on('focus', '.qty-box-input, .qty-pcs-input', function() {
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
        syncAllQty();
        hitungSubtotal();
        let adaQty = false;
        let adaMelewati = false;

        $('.qty-faktur').each(function () {
            const qty    = parseFloat($(this).val()) || 0;
            const maxQty = parseFloat($(this).data('outstanding')) || 0;
            if (qty > 0)      adaQty = true;
            if (qty > maxQty) adaMelewati = true;
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

        if (!fakturSubmitConfirmed) {
            e.preventDefault();
            salesConfirm({
                title: 'Simpan Faktur?',
                text: 'Pastikan qty dan data faktur sudah benar.',
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
