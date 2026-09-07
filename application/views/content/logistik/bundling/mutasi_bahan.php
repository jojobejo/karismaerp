<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <!-- Navbar -->
    <?php $this->load->view('partial/main/navbar') ?>
    <!-- Main Sidebar Container -->
    <?php $this->load->view('partial/main/sidebar') ?>

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
                <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
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

                <!-- Panel Mutasi Bertahap / Kalkulator Kebutuhan Harian -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%); border-left: 5px solid #0284c7 !important;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-lg-6">
                                <h6 class="font-weight-bold text-dark mb-1">
                                    <i class="fas fa-calculator text-primary mr-2"></i> Mutasi Bahan Bertahap (Harian)
                                </h6>
                                <p class="text-muted small mb-0">
                                    Pindahkan komponen secukupnya sesuai kapasitas perakitan harian (misal 10 atau 20 paket) agar stok di Gudang Induk tidak tersedot seluruhnya dan tetap dapat dijual eceran oleh Sales.
                                </p>
                            </div>
                            <div class="col-lg-6 mt-3 mt-lg-0">
                                <div class="d-flex flex-wrap align-items-center justify-content-lg-end">
                                    <label class="small font-weight-bold text-dark mr-2 mb-0 text-nowrap">Target Rakit:</label>
                                    <div class="input-group input-group-sm mr-2 mb-1" style="max-width: 140px;">
                                        <input type="number" id="inputTargetPaket" class="form-control font-weight-bold text-center text-primary" min="1" max="<?= $sisa_request ?>" value="10" placeholder="10">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><?= htmlspecialchars($request['satuan'] ?: 'Box') ?></span>
                                        </div>
                                    </div>
                                    <div class="btn-group btn-group-sm mb-1" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-preset-paket font-weight-bold" data-val="10">10</button>
                                        <button type="button" class="btn btn-outline-primary btn-preset-paket font-weight-bold" data-val="25">25</button>
                                        <button type="button" class="btn btn-outline-primary btn-preset-paket font-weight-bold" data-val="50">50</button>
                                        <button type="button" class="btn btn-outline-secondary btn-preset-paket font-weight-bold" data-val="<?= $sisa_request ?>">Semua (<?= $sisa_request ?>)</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Mutasi Bahan -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="font-weight-bold text-dark m-0">
                                <i class="fas fa-boxes text-primary mr-2"></i> Rincian Jumlah Komponen yang Akan Dimutasi
                            </h6>
                            <small class="text-muted">Kuantitas dihitung otomatis sesuai target rakit di atas, dan dapat disesuaikan manual per baris jika diperlukan.</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold" id="btnResetHitung">
                            <i class="fas fa-sync-alt mr-1"></i> Hitung Ulang
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" style="font-size: 0.92rem;">
                                <thead style="background: #f1f5f9; color: #334155;">
                                    <tr>
                                        <th style="width: 25%;">Komponen Barang</th>
                                        <th style="width: 10%;" class="text-center">Isi / 1 Pkt</th>
                                        <th style="width: 12%;" class="text-center">Sisa Target</th>
                                        <th style="width: 14%;" class="text-center">Stok Bundling Saat Ini</th>
                                        <th style="width: 23%;">Pilih Batch Lot Gudang Induk</th>
                                        <th style="width: 16%;" class="text-center bg-primary text-white font-weight-bold">Jumlah Dimutasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $idx = 0; ?>
                                    <?php foreach ($stock_status as $stk): ?>
                                        <?php 
                                            $maxAvail = !empty($stk['batches']) ? ((float)$stk['batches'][0]['qty_on_hand'] - (float)$stk['batches'][0]['qty_reserved']) : 0;
                                            $isiPerPaket = (float)$stk['qty_per_paket'] ?: 1;
                                            $stokDiBundling = (float)$stk['stok_gudang_bundling'];
                                            $sisaKebutuhanTotal = (float)$stk['sisa_kebutuhan'];

                                            // Default kalkulasi awal untuk target 10 paket (atau sisa jika kurang dari 10)
                                            $targetAwal = min(10, (float)$sisa_request);
                                            $kebutuhanTarget = $targetAwal * $isiPerPaket;
                                            $defQty = max(0, $kebutuhanTarget - $stokDiBundling);
                                            $defQty = min($defQty, $sisaKebutuhanTotal);
                                            $defQty = min($defQty, $maxAvail);
                                        ?>
                                        <tr class="item-mutasi-row" 
                                            data-isi="<?= $isiPerPaket ?>" 
                                            data-stok-bundling="<?= $stokDiBundling ?>" 
                                            data-sisa-req="<?= $sisaKebutuhanTotal ?>" 
                                            data-max-avail="<?= $maxAvail ?>">
                                            <td>
                                                <input type="hidden" name="items[<?= $idx ?>][kode_barang]" value="<?= htmlspecialchars($stk['kode_barang']) ?>">
                                                <input type="hidden" name="items[<?= $idx ?>][nama_barang]" value="<?= htmlspecialchars($stk['nama_barang']) ?>">
                                                <div class="font-weight-bold text-dark"><?= htmlspecialchars($stk['nama_barang']) ?></div>
                                                <small class="text-muted">Kode: <?= htmlspecialchars($stk['kode_barang']) ?></small>
                                            </td>
                                            <td class="text-center font-weight-bold text-muted">
                                                <?= number_format($isiPerPaket, 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                            </td>
                                            <td class="text-center font-weight-bold text-danger">
                                                <?= number_format($sisaKebutuhanTotal, 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
                                            </td>
                                            <td class="text-center font-weight-bold text-muted">
                                                <?= number_format($stokDiBundling, 2, ',', '.') ?> <?= htmlspecialchars($stk['satuan']) ?>
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
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="any" min="0" max="<?= $maxAvail ?>" 
                                                           name="items[<?= $idx ?>][qty_mutasi]" 
                                                           id="max_<?= $idx ?>"
                                                           class="form-control font-weight-bold text-center input-qty text-primary" 
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
                                Mutasi hanya akan memindahkan kuantitas yang tertera di kolom <strong>Jumlah Dimutasi</strong>. Sisa barang tetap berada di Gudang Induk untuk penjualan Sales.
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
<!-- /.content-wrapper -->
</div>
<!-- /.wrapper -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Fungsi menghitung kuantitas mutasi sesuai target paket
    function applyTargetPaket(targetBox) {
        targetBox = parseFloat(targetBox) || 0;
        $('.item-mutasi-row').each(function() {
            let isi = parseFloat($(this).data('isi')) || 1;
            let stokBundling = parseFloat($(this).data('stok-bundling')) || 0;
            let maxAvail = parseFloat($(this).data('max-avail')) || 0;
            let sisaReq = parseFloat($(this).data('sisa-req')) || 0;

            let butuhUntukTarget = targetBox * isi;
            // Kebutuhan tambahan yang belum ada di gudang bundling
            let perluMutasi = Math.max(0, butuhUntukTarget - stokBundling);
            // Batasi agar tidak melampaui sisa kebutuhan total
            perluMutasi = Math.min(perluMutasi, sisaReq);
            // Batasi agar tidak melampaui ketersediaan di gudang induk
            perluMutasi = Math.min(perluMutasi, maxAvail);

            $(this).find('.input-qty').val(perluMutasi);
        });
    }

    // Listener input target paket
    $('#inputTargetPaket').on('input change', function() {
        applyTargetPaket($(this).val());
    });

    // Preset tombol cepat (10, 25, 50, Semua)
    $('.btn-preset-paket').click(function() {
        let val = $(this).data('val');
        $('#inputTargetPaket').val(val);
        applyTargetPaket(val);
    });

    $('#btnResetHitung').click(function() {
        applyTargetPaket($('#inputTargetPaket').val());
    });

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
        $(this).closest('tr').data('max-avail', avail);
    });

    $('#formMutasiBahan').submit(function(e) {
        e.preventDefault();

        // Hitung total item yang dimutasi
        let totalMutasi = 0;
        $('.input-qty').each(function() {
            totalMutasi += (parseFloat($(this).val()) || 0);
        });

        if (totalMutasi <= 0) {
            Swal.fire('Peringatan', 'Jumlah barang yang dimutasi masih 0. Masukkan jumlah mutasi bahan terlebih dahulu atau naikkan target paket.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Mutasi Bahan?',
            text: 'Barang komponen akan dipindahkan dari Gudang Induk ke Gudang Bundling sesuai kuantitas yang ditentukan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Mutasikan Sekarang!',
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
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error, xhr.responseText);
                        let errMsg = 'Terjadi kesalahan sistem saat memproses mutasi.';
                        if (xhr.status === 401 || (xhr.responseJSON && xhr.responseJSON.auth_timeout)) {
                            Swal.fire('Sesi Berakhir', 'Sesi login Anda telah berakhir. Silakan login kembali.', 'warning')
                                .then(() => location.href = '<?= site_url("auth") ?>');
                            return;
                        }
                        if (xhr.responseJSON && xhr.responseJSON.msg) {
                            errMsg = xhr.responseJSON.msg;
                        }
                        Swal.fire('Gagal Memproses Mutasi', errMsg, 'error');
                    }
                });
            }
        });
    });
});
</script>
