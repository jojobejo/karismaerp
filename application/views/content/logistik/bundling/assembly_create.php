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
                        <i class="fas fa-hammer text-primary mr-2"></i> Pembuatan Paket Bundling (Assembly)
                    </h1>
                    <p class="text-muted mb-0 small">Perakitan fisik paket bundling mengambil bahan dari Gudang Induk. Draft penyesuaian persediaan akan otomatis dibuat untuk Bagian Accounting.</p>
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
            <!-- Banner Penjelasan Hierarki Kemasan & Petunjuk Fisik Perakitan Gudang -->
            <?php if (!empty($request['is_innerbox'])): ?>
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #f0f7ff 0%, #f4fbf7 100%); border-left: 5px solid #2563eb !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 44px; height: 44px; font-size: 1.25rem;">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <div>
                                    <h5 class="font-weight-bold text-dark mb-0">Petunjuk Pengepakan Fisik: Paket Innerbox Multi-Item</h5>
                                    <small class="text-muted">Panduan cara pembungkusan untuk Petugas Perakitan Logistik Gudang</small>
                                </div>
                            </div>
                            <span class="badge badge-primary px-3 py-2 font-weight-bold mt-2 mt-sm-0" style="font-size: 0.88rem; border-radius: 8px;">
                                <i class="fas fa-layer-group mr-1"></i> 1 <?= htmlspecialchars($request['satuan']) ?> Master Box = <?= number_format((float)$request['jumlah_innerbox'], 0) ?> <?= htmlspecialchars($request['satuan_innerbox'] ?: 'Innerbox') ?>
                            </span>
                        </div>

                        <!-- 3 Kotak Alur Fisik -->
                        <div class="row align-items-stretch">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="p-3 bg-white rounded shadow-sm h-100 border">
                                    <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold mb-2">
                                        <i class="fas fa-box-open mr-1"></i> 1. Isi Kardus Kecil (1 Innerbox)
                                    </span>
                                    <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                        Setiap 1 Innerbox Diisi Campuran:
                                    </div>
                                    <ul class="mb-0 pl-3 mt-2 small font-weight-bold text-secondary">
                                        <?php foreach ($components as $itemInbox): ?>
                                            <li class="mb-1">
                                                <?= htmlspecialchars($itemInbox['nama_barang_komponen']) ?>: 
                                                <span class="text-primary"><?= number_format($itemInbox['isi_per_innerbox'] ?: 1, 0) ?> <?= htmlspecialchars($itemInbox['satuan']) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="p-3 bg-white rounded shadow-sm h-100 border">
                                    <span class="badge badge-info px-2 py-1 font-weight-bold mb-2">
                                        <i class="fas fa-box mr-1"></i> 2. Kardus Luar (1 Master Box)
                                    </span>
                                    <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                        Kardus Luar Memuat:
                                    </div>
                                    <div class="mt-2">
                                        <h4 class="text-info font-weight-bold mb-0">
                                            <?= number_format((float)$request['jumlah_innerbox'], 0) ?> <small style="font-size: 0.9rem;">Kardus Kecil (Innerbox)</small>
                                        </h4>
                                        <small class="text-muted d-block mt-1">
                                            Masukkan 20 kardus kecil yang sudah dibungkus ke dalam 1 kardus luar.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded shadow-sm h-100 border">
                                    <span class="badge badge-success px-2 py-1 font-weight-bold mb-2">
                                        <i class="fas fa-bullseye mr-1"></i> 3. Status Perakitan Request
                                    </span>
                                    <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                        Sisa Belum Dirakit:
                                    </div>
                                    <div class="mt-2">
                                        <h4 class="text-danger font-weight-bold mb-0">
                                            <?= number_format($sisa_request, 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?> <small class="text-muted" style="font-size: 0.85rem;">Master Box</small>
                                        </h4>
                                        <div class="small text-muted mt-1 font-weight-bold">
                                            Setara: <span class="text-dark"><?= number_format((float)$sisa_request * (float)$request['jumlah_innerbox'], 0, ',', '.') ?> Kardus Kecil</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form id="formAssembly" method="post" action="<?= site_url('logistik/bundling/save_assembly') ?>">
                <input type="hidden" name="id_request" value="<?= $request['id_request'] ?>">
                <input type="hidden" name="kode_paket" value="<?= htmlspecialchars($request['kode_paket']) ?>">
                <input type="hidden" name="nama_paket" value="<?= htmlspecialchars($request['nama_paket']) ?>">
                <input type="hidden" name="id_gudang_asal" value="<?= $request['id_gudang_asal'] ?>">
                <input type="hidden" name="id_gudang" value="<?= $request['id_gudang_tujuan'] ?>">
                <input type="hidden" name="satuan" value="<?= htmlspecialchars($request['satuan']) ?>">

                <div class="row">
                    <!-- Kolom Kiri: Info Paket yang Dibuat -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                                <h6 class="font-weight-bold text-dark m-0"><i class="fas fa-box text-primary mr-2"></i>Target Pembuatan Paket</h6>
                            </div>
                            <div class="card-body pt-0">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">No. Perakitan (Assembly)</label>
                                    <input type="text" class="form-control font-weight-bold bg-light" value="<?= htmlspecialchars($no_assembly) ?>" readonly>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Ref. Request Purchasing</label>
                                    <input type="text" class="form-control font-weight-bold bg-light" value="<?= htmlspecialchars($request['no_request']) ?>" readonly>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Produk Paket</label>
                                    <h5 class="font-weight-bold text-dark mb-0"><?= htmlspecialchars($request['nama_paket']) ?></h5>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge badge-light border text-muted mr-2"><?= htmlspecialchars($request['kode_paket']) ?></span>
                                        <?php if (!empty($request['is_innerbox'])): ?>
                                            <span class="badge badge-primary px-2 py-1 font-weight-bold">
                                                <i class="fas fa-boxes mr-1"></i> Innerbox: <?= number_format((float)$request['jumlah_innerbox'], 0) ?> Box / <?= htmlspecialchars($request['satuan']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6 pr-1">
                                        <label class="small font-weight-bold text-muted">Sumber Bahan</label>
                                        <input type="text" class="form-control form-control-sm font-weight-bold bg-light text-primary" value="<?= htmlspecialchars($request['nama_gudang_asal'] ?: 'Gudang Induk') ?>" readonly>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <label class="small font-weight-bold text-muted">Gudang Paket</label>
                                        <input type="text" class="form-control form-control-sm font-weight-bold bg-light text-success" value="<?= htmlspecialchars($request['nama_gudang_tujuan'] ?: 'Gudang Bundling') ?>" readonly>
                                    </div>
                                </div>

                                <div class="alert alert-light border small text-muted mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Request:</span>
                                        <strong><?= number_format((float)$request['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Sudah Dibuat:</span>
                                        <strong class="text-success"><?= number_format((float)$request['qty_realisasi'], 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Sisa Belum Dibuat:</span>
                                        <strong class="text-danger"><?= number_format($sisa_request, 0, ',', '.') ?> <?= htmlspecialchars($request['satuan']) ?></strong>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Jumlah Paket yang Dibuat Saat Ini <span class="text-danger">*</span></label>
                                    <input type="number" step="any" min="1" max="<?= $sisa_request ?>" 
                                           id="qty_assembly" name="qty_assembly" 
                                           class="form-control form-control-lg font-weight-bold text-success" 
                                           value="<?= $sisa_request ?>" required>
                                    <small class="text-muted">Mendukung pembuatan sebagian (parsial). Masukkan kuantitas master box yang siap dirakit sekarang.</small>
                                    <?php if (!empty($request['is_innerbox'])): ?>
                                        <div id="assembly-innerbox-indicator" class="alert alert-info py-2 px-3 small font-weight-bold mt-2 mb-0" style="border-radius: 8px;">
                                            <i class="fas fa-boxes mr-1"></i> Setara dengan perakitan <span id="text-innerbox-count" class="text-dark font-weight-bold"><?= number_format((float)$sisa_request * (float)$request['jumlah_innerbox'], 0, ',', '.') ?></span> kardus kecil (innerbox).
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Tanggal Perakitan</label>
                                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Nomor Lot Paket Baru</label>
                                    <input type="text" name="no_lot_paket" class="form-control font-weight-bold" value="LOT-PKT-<?= date('ymd') ?>-<?= rand(100, 999) ?>">
                                    <small class="text-muted">Nomor identitas batch lot untuk kardus paket bundling.</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Expired Date Paket</label>
                                    <input type="date" name="expired_date_paket" class="form-control" value="">
                                    <small class="text-muted">Opsional. Jika dikosongkan, sistem akan otomatis mengambil tanggal kadaluarsa terdekat dari komponen.</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Catatan Pelaksanaan</label>
                                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan perakitan..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Pengurangan Stok Komponen di Gudang Induk -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                                <h6 class="font-weight-bold text-dark m-0">
                                    <i class="fas fa-layer-group text-primary mr-2"></i> Penggunaan Komponen dari <?= htmlspecialchars($request['nama_gudang_asal'] ?: 'Gudang Induk') ?>
                                </h6>
                                <small class="text-muted">Komponen berikut diambil langsung dari Gudang Induk dan otomatis dibuatkan dokumen Draft Penyesuaian Barang untuk Bagian Accounting.</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="tableAssemblyItems" style="font-size: 0.92rem;">
                                        <thead style="background: #f1f5f9; color: #334155;">
                                            <tr>
                                                <th style="width: 28%;">Komponen Barang</th>
                                                <?php if (!empty($request['is_innerbox'])): ?>
                                                    <th style="width: 18%;" class="text-center">Komposisi / Paket</th>
                                                <?php else: ?>
                                                    <th style="width: 14%;" class="text-center">Isi / Paket</th>
                                                <?php endif; ?>
                                                <th style="width: 26%;">Pilih Batch Lot Gudang Induk</th>
                                                <th style="width: 14%;" class="text-center">Stok Gdg. Induk</th>
                                                <th style="width: 14%;" class="text-center bg-danger text-white">Qty Terpakai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $cIdx = 0; ?>
                                            <?php foreach ($components as $comp): ?>
                                                <tr class="comp-row" 
                                                    data-isi="<?= (float)$comp['qty_per_paket'] ?>"
                                                    data-is-inbox="<?= !empty($comp['is_innerbox']) ? 1 : 0 ?>"
                                                    data-qty-inbox="<?= (float)($comp['qty_innerbox'] ?? 0) ?>"
                                                    data-isi-inbox="<?= (float)($comp['isi_per_innerbox'] ?? 0) ?>">
                                                    <td>
                                                        <input type="hidden" name="komponen[<?= $cIdx ?>][kode_barang]" value="<?= htmlspecialchars($comp['kode_barang_komponen']) ?>">
                                                        <input type="hidden" name="komponen[<?= $cIdx ?>][nama_barang]" value="<?= htmlspecialchars($comp['nama_barang_komponen']) ?>">
                                                        <input type="hidden" name="komponen[<?= $cIdx ?>][satuan]" value="<?= htmlspecialchars($comp['satuan']) ?>">
                                                        <div class="font-weight-bold text-dark"><?= htmlspecialchars($comp['nama_barang_komponen']) ?></div>
                                                        <small class="text-muted">Kode: <?= htmlspecialchars($comp['kode_barang_komponen']) ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (!empty($comp['is_innerbox'])): ?>
                                                            <div class="font-weight-bold text-primary" style="font-size: 0.95rem;">
                                                                <?= number_format((float)($comp['isi_per_innerbox'] ?: 1), 0) ?> <?= htmlspecialchars($comp['satuan']) ?> <small class="text-muted">/ innerbox</small>
                                                            </div>
                                                            <small class="text-muted font-weight-bold d-block mt-1">
                                                                Total: <?= number_format((float)$comp['qty_per_paket'], 0) ?> <?= htmlspecialchars($comp['satuan']) ?> / Box
                                                            </small>
                                                        <?php else: ?>
                                                            <div class="font-weight-bold text-dark">
                                                                <?= number_format((float)$comp['qty_per_paket'], 2, ',', '.') ?> <?= htmlspecialchars($comp['satuan']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (empty($comp['batches'])): ?>
                                                            <span class="badge badge-danger">Stok di Gudang Induk 0!</span>
                                                            <input type="hidden" name="komponen[<?= $cIdx ?>][no_lot]" value="-">
                                                            <input type="hidden" name="komponen[<?= $cIdx ?>][expired_date]" value="">
                                                        <?php else: ?>
                                                            <select class="form-control form-control-sm select-comp-lot">
                                                                <?php foreach ($comp['batches'] as $b): ?>
                                                                    <?php $avail = (float)$b['qty_on_hand'] - (float)$b['qty_reserved']; ?>
                                                                    <option value="<?= htmlspecialchars($b['no_lot']) ?>" 
                                                                            data-lot="<?= htmlspecialchars($b['no_lot']) ?>" 
                                                                            data-exp="<?= htmlspecialchars($b['expired_date'] ?: '') ?>" 
                                                                            data-avail="<?= $avail ?>">
                                                                        Lot: <?= htmlspecialchars($b['no_lot']) ?> (Exp: <?= $b['expired_date'] ?: '-' ?>) - Ada: <?= number_format($avail, 2, ',', '.') ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <input type="hidden" name="komponen[<?= $cIdx ?>][no_lot]" class="input-comp-lot" value="<?= htmlspecialchars($comp['batches'][0]['no_lot']) ?>">
                                                            <input type="hidden" name="komponen[<?= $cIdx ?>][expired_date]" class="input-comp-exp" value="<?= htmlspecialchars($comp['batches'][0]['expired_date'] ?: '') ?>">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center font-weight-bold text-primary">
                                                        <?= number_format($comp['stok_gudang_asal'], 2, ',', '.') ?> <?= htmlspecialchars($comp['satuan']) ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" step="any" min="0" 
                                                               name="komponen[<?= $cIdx ?>][qty_digunakan]" 
                                                               class="form-control form-control-sm text-center font-weight-bold text-danger input-qty-pakai" 
                                                               value="0" readonly>
                                                        <div class="box-pakai-badge mt-1" style="display: none;"></div>
                                                    </td>
                                                </tr>
                                                <?php $cIdx++; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3 bg-light border-top small text-muted">
                                    <i class="fas fa-info-circle text-info mr-1"></i>
                                    <strong>Alur Akuntansi Otomatis:</strong> Setelah Logistik klik <em>Simpan & Rekam Perakitan</em>, sistem otomatis menerbitkan dokumen <strong>Draft Penyesuaian Persediaan</strong> ke modul <code>Penyesuaian Barang</code> untuk diposting oleh Bagian Accounting.
                                </div>

                                <div class="p-4 bg-light border-top">
                                    <div class="row align-items-center">
                                        <div class="col-md-7 text-muted small">
                                            <i class="fas fa-info-circle text-primary mr-1"></i>
                                            <strong>Dampak Transaksi:</strong>
                                            Komponen akan dipotong dari Gudang Bundling, dan Paket Bundling akan bertambah di Gudang Bundling lengkap dengan akumulasi HPP.
                                        </div>
                                        <div class="col-md-5 text-right">
                                            <button type="submit" class="btn btn-success btn-lg px-4 font-weight-bold shadow">
                                                <i class="fas fa-check mr-1"></i> Konfirmasi & Selesaikan Assembly
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    recalcAssemblyQuantities();

    $('#qty_assembly').on('input change', function() {
        recalcAssemblyQuantities();
    });

    $('.select-comp-lot').change(function() {
        let opt = $(this).find(':selected');
        let lot = opt.data('lot');
        let exp = opt.data('exp');
        let row = $(this).closest('td');
        row.find('.input-comp-lot').val(lot);
        row.find('.input-comp-exp').val(exp);
    });

    $('#formAssembly').submit(function(e) {
        e.preventDefault();

        let qtyAsm = parseFloat($('#qty_assembly').val()) || 0;
        if (qtyAsm <= 0) {
            Swal.fire('Peringatan', 'Jumlah paket yang dibuat harus lebih dari 0', 'warning');
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Pembuatan Paket?',
            text: 'Sebanyak ' + qtyAsm + ' Box <?= htmlspecialchars($request["nama_paket"]) ?> akan dirakit. Stok komponen di Gudang Bundling akan berkurang.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Proses Sekarang!',
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

function recalcAssemblyQuantities() {
    let qtyAsm = parseFloat($('#qty_assembly').val()) || 0;
    let jmlInboxPerPaket = <?= (float)($request['jumlah_innerbox'] ?? 0) ?>;
    if (jmlInboxPerPaket > 0) {
        let totalInboxAsm = qtyAsm * jmlInboxPerPaket;
        $('#text-innerbox-count').text(totalInboxAsm.toLocaleString('id-ID'));
    }

    $('.comp-row').each(function() {
        let isi = parseFloat($(this).data('isi')) || 1;
        let butuh = qtyAsm * isi;
        $(this).find('.input-qty-pakai').val(butuh);

        let isInbox = parseInt($(this).data('is-inbox')) === 1;
        let qInbox = parseFloat($(this).data('qty-inbox')) || 0;
        let badge = $(this).find('.box-pakai-badge');
        if (isInbox && qInbox > 0 && qtyAsm > 0) {
            let totalBox = qtyAsm * qInbox;
            badge.html(`<span class="badge badge-warning text-dark font-weight-bold" style="font-size: 0.75rem;"><i class="fas fa-boxes mr-1"></i> ${totalBox.toLocaleString('id-ID')} Kardus Kecil</span>`).show();
        } else {
            badge.hide();
        }
    });
}
</script>
