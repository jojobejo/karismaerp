<link rel="stylesheet" href="<?= base_url('assets/dist/css/retur-custom.css') ?>"><?php /* views/content/sales/retur/retur_collection.php */ ?>
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
                        <h1 class="m-0"><i class="fas fa-handshake mr-2 text-info"></i> Team Collection: Proses Retur</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan/retur') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active">Proses Collection</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php 
                    $grand_total_saldo = 0;
                    foreach ($all_returns as $ar) {
                        $grand_total_saldo += (float)$ar['sisa_saldo_retur'];
                    }
                ?>
                <!-- INFO CUSTOMER & TOTAL SALDO -->
                <div class="card shadow mb-3">
                    <div class="card-header bg-info text-white py-2">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-user-tie mr-1"></i> Informasi Customer</h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-6 border-right">
                                <h5 class="mb-1 text-primary font-weight-bold"><?= htmlspecialchars($retur['nama_customer'] ?: $retur['nama_customer_master'] ?: '-') ?></h5>
                                <div class="text-muted small"><i class="fas fa-map-marker-alt mr-1"></i> <?= htmlspecialchars($retur['alamat'] ?: $retur['alamat_master'] ?: '-') ?></div>
                            </div>
                            <div class="col-md-6 text-center text-md-right mt-3 mt-md-0">
                                <div class="text-muted small mb-1">Total Saldo Retur Aktif:</div>
                                <h3 class="text-success font-weight-bold mb-0">Rp <?= number_format($grand_total_saldo, 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORM COLLECTION -->
                <form method="post" action="<?= base_url('retur_penjualan/retur/collection_simpan/' . $retur['id_retur']) ?>">
                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <?php endif; ?>
                    <div class="card shadow">
                        <div class="card-header bg-info text-white py-2">
                            <h3 class="card-title"><i class="fas fa-file-signature mr-1"></i> Penyerahan Surat Retur & Alokasi Faktur Potong</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info py-2 mb-3">
                                <label class="mb-1 font-weight-bold"><i class="fas fa-list mr-1"></i> Daftar Saldo Retur Tersedia</label>
                                <p class="small mb-2">Pilih retur mana saja yang akan digunakan untuk memotong tagihan faktur:</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered bg-white mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="text-center" style="width:50px;">Pilih</th>
                                                <th>No Retur</th>
                                                <th>Tgl Retur</th>
                                                <th class="text-right">Sisa Saldo</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($all_returns as $ar): 
                                                $is_current = ($ar['id_retur'] == $retur['id_retur']);
                                            ?>
                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <input type="checkbox" name="selected_returs[]" value="<?= $ar['id_retur'] ?>" class="chk-retur" data-saldo="<?= $ar['sisa_saldo_retur'] ?>" style="transform: scale(1.5);">
                                                    </td>
                                                    <td class="align-middle">
                                                        <?= htmlspecialchars($ar['no_retur']) ?>
                                                    </td>
                                                    <td class="align-middle"><?= date('d/m/Y', strtotime($ar['tanggal_retur'])) ?></td>
                                                    <td class="text-right align-middle text-success font-weight-bold">Rp <?= number_format($ar['sisa_saldo_retur'], 0, ',', '.') ?></td>
                                                    <td class="text-center align-middle">
                                                        <a href="<?= base_url('retur_penjualan/retur/detail/' . $ar['id_retur']) ?>" target="_blank" class="btn btn-xs btn-info" title="Lihat Detail Retur"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Pilih Faktur yang Akan Dipotong <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="hidden" name="id_faktur_potong" id="id_faktur_potong" required>
                                    <input type="text" id="display_faktur_potong" class="form-control bg-white" placeholder="-- Klik untuk memilih Faktur Belum Lunas --" readonly required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalFaktur">
                                            <i class="fas fa-search"></i> Pilih Faktur
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Nominal Potongan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                    <?php $max_potong = $retur['status_retur'] === 'selesai' ? $retur['sisa_saldo_retur'] : $total_retur; ?>
                                    <input type="number" step="0.01" name="nominal_potongan" id="nominal_potongan" class="form-control" max="<?= $max_potong ?>" required placeholder="Masukkan nominal (parsial/full)">
                                </div>
                                <small class="text-muted" id="nominal_hint">Maksimal pemotongan saldo retur: Rp <?= number_format($max_potong, 0, ',', '.') ?></small>
                                <input type="hidden" id="max_saldo_retur" value="<?= $max_potong ?>">
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Catatan (Collection)</label>
                                <textarea name="catatan_collection" class="form-control" rows="3" placeholder="Catatan/keterangan pemotongan..."></textarea>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-secondary mr-2">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-success"
                                    onclick="return confirm('Proses pemotongan retur ini?')">
                                <i class="fas fa-paper-plane mr-1"></i> Proses Potongan
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
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<!-- Modal Pilih Faktur -->
<div class="modal fade" id="modalFaktur" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title"><i class="fas fa-search mr-2"></i>Pilih Faktur Belum Lunas</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table_faktur" class="table table-bordered table-striped table-sm w-100">
                        <thead>
                            <tr>
                                <th>No Faktur</th>
                                <th>No SO</th>
                                <th>Tanggal</th>
                                <th class="text-right">Sisa Tagihan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($faktur_belum_lunas as $f): ?>
                                <tr>
                                    <td><?= htmlspecialchars($f['no_faktur']) ?></td>
                                    <td><?= htmlspecialchars($f['no_so']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($f['tanggal_faktur'])) ?></td>
                                    <td class="text-right">Rp <?= number_format($f['sisa_tagihan'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-success btn-pilih-faktur"
                                                data-id="<?= $f['id_faktur'] ?>"
                                                data-no="<?= htmlspecialchars($f['no_faktur']) ?>"
                                                data-sisa="<?= $f['sisa_tagihan'] ?>">
                                            <i class="fas fa-check"></i> Pilih
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable) {
        $('#table_faktur').DataTable({
            "pageLength": 10,
            "language": {
                "search": "Cari Faktur:"
            }
        });
    }

    $('.btn-pilih-faktur').on('click', function() {
        var idFaktur = $(this).data('id');
        var noFaktur = $(this).data('no');
        var sisaTagihan = parseFloat($(this).data('sisa'));
        var maxSaldoRetur = parseFloat($('#max_saldo_retur').val());
        
        // Update input hidden & display
        $('#id_faktur_potong').val(idFaktur);
        $('#display_faktur_potong').val('Faktur: ' + noFaktur + ' (Sisa: Rp ' + sisaTagihan.toLocaleString('id-ID') + ')');
        
        // Update max input & hint
        var maxInput = Math.min(sisaTagihan, maxSaldoRetur);
        $('#nominal_potongan').attr('max', maxInput);
        $('#nominal_hint').html('Maksimal pemotongan: Rp ' + maxInput.toLocaleString('id-ID') + ' (Terkecil antara Saldo Retur & Sisa Tagihan)');
        
        $('#modalFaktur').modal('hide');
    });

    // Validasi real-time agar input nominal tidak melebihi maksimal (sisa saldo retur / sisa tagihan)
    $('#nominal_potongan').on('input', function() {
        var max = parseFloat($(this).attr('max'));
        if (isNaN(max)) {
            max = parseFloat($('#max_saldo_retur').val());
        }
        var val = parseFloat($(this).val());
        if (val > max) {
            $(this).val(max);
        }
    });

    // Update max saldo retur jika ada retur yang dicentang
    function recalculateMaxSaldo() {
        var totalSelected = 0;
        $('.chk-retur:checked').each(function() {
            totalSelected += parseFloat($(this).data('saldo')) || 0;
        });
        
        var newMax = totalSelected;
        $('#max_saldo_retur').val(newMax);
        
        // Update input max jika faktur sudah dipilih
        var selectedSisa = 0;
        var displayStr = $('#display_faktur_potong').val() || '';
        if (displayStr.indexOf('Sisa:') !== -1) {
            // Faktur sudah terpilih
            var match = displayStr.match(/Rp\s*([\d\.]+)/);
            if (match) {
                selectedSisa = parseFloat(match[1].replace(/\./g, ''));
            }
        }
        
        if (selectedSisa > 0) {
            var currentMaxInput = Math.min(selectedSisa, newMax);
            $('#nominal_potongan').attr('max', currentMaxInput);
            $('#nominal_hint').html('Maksimal pemotongan: Rp ' + currentMaxInput.toLocaleString('id-ID') + ' (Terkecil antara Saldo Terpilih & Sisa Tagihan)');
            
            // Validasi ulang nilai input jika melebihi batas baru
            var currentVal = parseFloat($('#nominal_potongan').val()) || 0;
            if (currentVal > currentMaxInput) {
                $('#nominal_potongan').val(currentMaxInput);
            }
        } else {
            $('#nominal_potongan').attr('max', newMax);
            $('#nominal_hint').html('Maksimal pemotongan saldo retur gabungan: Rp ' + newMax.toLocaleString('id-ID'));
        }
    }

    $('.chk-retur').on('change', recalculateMaxSaldo);
    
    // Inisialisasi saat load
    recalculateMaxSaldo();
});
</script>
