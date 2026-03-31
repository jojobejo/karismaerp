<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" height="150" width="300">
    </div>
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-undo text-danger"></i> Data Retur
                            <small class="text-muted">KMT CORN</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/omset') ?>">Omset</a></li>
                            <li class="breadcrumb-item active">Retur</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php $this->load->view('partial/main/alert') ?>

                <!-- Info Transaksi Omset -->
                <div class="card card-outline card-info mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-receipt mr-1"></i> Informasi Transaksi
                        </h3>
                        <div class="card-tools">
                            <a href="<?= base_url('kmt/omset') ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Omset
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <small class="text-muted">Tanggal</small>
                                <p class="font-weight-bold mb-1">
                                    <?= date('d/m/Y', strtotime($omset['tanggal'])) ?>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Nama Toko</small>
                                <p class="font-weight-bold mb-1"><?= htmlspecialchars($omset['nama_toko']) ?></p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Produk</small>
                                <p class="font-weight-bold mb-1"><?= htmlspecialchars($omset['produk']) ?></p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Sales SO</small>
                                <p class="font-weight-bold mb-1"><?= htmlspecialchars($omset['sales_so'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Wilayah</small>
                                <p class="mb-1">
                                    <span class="badge badge-secondary">
                                        <?= htmlspecialchars($omset['nama_wilayah'] ?? '-') ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Qty</small>
                                <p class="font-weight-bold mb-1">
                                    <?= number_format($omset['quantity'], 2, ',', '.') ?>
                                    <?= $omset['unit'] ?>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Nilai Penjualan (Inc PPN)</small>
                                <p class="font-weight-bold mb-1 text-success">
                                    Rp <?= number_format($omset['penj_inc_ppn_neto'], 0, ',', '.') ?>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Total Sudah Diretur</small>
                                <p class="font-weight-bold mb-1 text-danger">
                                    Rp <?= number_format($summary['total_retur'] ?? 0, 0, ',', '.') ?>
                                    <small class="text-muted">(<?= $summary['jumlah'] ?? 0 ?> transaksi)</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Retur -->
                <?php if (($summary['total_retur'] ?? 0) > 0): ?>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="info-box bg-danger shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-minus-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Retur Kurangi Target ABM</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($summary['retur_kurangi'] ?? 0, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-warning shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Retur Tidak Kurangi Target</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($summary['retur_tidak'] ?? 0, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-secondary shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-undo"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Semua Retur</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($summary['total_retur'] ?? 0, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">

                    <!-- Form Tambah Retur -->
                    <div class="col-md-4">
                        <div class="card card-outline card-danger">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plus mr-1"></i> Tambah Retur
                                </h3>
                            </div>
                            <div class="card-body">
                                <form action="<?= base_url('kmt/omset/simpan_retur') ?>" method="POST">
                                    <?= form_open(base_url('kmt/omset/simpan_retur')) ?>
                                    <input type="hidden" name="id_omset" value="<?= $omset['id'] ?>">

                                    <div class="form-group">
                                        <label>Tanggal Retur <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_retur"
                                               class="form-control form-control-sm"
                                               value="<?= date('Y-m-d') ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label>No Retur</label>
                                        <input type="text" name="no_retur"
                                               class="form-control form-control-sm"
                                               placeholder="Nomor dokumen retur">
                                    </div>

                                    <div class="form-group">
                                        <label>Quantity Retur</label>
                                        <input type="number" step="0.01" name="quantity"
                                               class="form-control form-control-sm"
                                               placeholder="0">
                                    </div>

                                    <div class="form-group">
                                        <label>Nilai Retur (Rp) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-danger text-white">Rp</span>
                                            </div>
                                            <input type="text" name="nilai_retur"
                                                   class="form-control angka"
                                                   placeholder="0" required>
                                        </div>
                                    </div>

                                    <!-- Kurangi Target ABM -->
                                    <div class="form-group">
                                        <label>Pengaruh ke Target ABM <span class="text-danger">*</span></label>
                                        <div class="card border mb-0">
                                            <div class="card-body py-2">
                                                <div class="custom-control custom-radio mb-2">
                                                    <input type="radio" id="kurangi_ya" name="kurangi_target"
                                                           value="1" class="custom-control-input" required>
                                                    <label class="custom-control-label text-danger font-weight-bold"
                                                           for="kurangi_ya">
                                                        <i class="fas fa-minus-circle mr-1"></i>
                                                        Kurangi Target ABM
                                                        <br>
                                                        <small class="text-muted font-weight-normal">
                                                            Retur ini akan mengurangi omset target ABM
                                                        </small>
                                                    </label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="kurangi_tidak" name="kurangi_target"
                                                           value="0" class="custom-control-input">
                                                    <label class="custom-control-label text-warning font-weight-bold"
                                                           for="kurangi_tidak">
                                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                                        Tidak Kurangi Target ABM
                                                        <br>
                                                        <small class="text-muted font-weight-normal">
                                                            Hanya sebagai catatan, tidak mempengaruhi target
                                                        </small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Unit</label>
                                        <input type="text" name="unit"
                                            class="form-control form-control-sm"
                                            placeholder="Pack / Sak / Kg">
                                    </div>

                                    <div class="form-group">
                                        <label>Keterangan <span class="text-danger">*</span></label>
                                        <select name="keterangan" class="form-control form-control-sm" required
                                                id="selKeterangan">
                                            <option value="">-- Pilih --</option>
                                            <option value="Retur">Retur</option>
                                            <option value="Replacement">Replacement</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Keterangan Detail</label>
                                        <input type="text" name="keterangan_detail"
                                            class="form-control form-control-sm"
                                            placeholder="Contoh: Barang Expired, Barang Bermasalah...">
                                    </div>

                                    <div class="form-group">
                                        <label>Kategori</label>
                                        <select name="kategori" class="form-control form-control-sm">
                                            <option value="">-- Pilih --</option>
                                            <option value="Barang bermasalah">Barang bermasalah</option>
                                            <option value="Replacement">Replacement</option>
                                            <option value="Expired">Expired</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-danger btn-block btn-sm">
                                        <i class="fas fa-save mr-1"></i> Simpan Retur
                                    </button>

                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Retur -->
                    <div class="col-md-8">
                        <div class="card card-outline card-danger">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-list mr-1"></i>
                                    Daftar Retur — <?= htmlspecialchars($omset['nama_toko']) ?>
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="tblRetur" class="table table-bordered table-striped table-hover table-sm mb-0">
                                        <thead style="background:#1f3864;color:#fff;">
                                            <tr>
                                                <th>#</th>
                                                <th>Tgl Retur</th>
                                                <th>No Retur</th>
                                                <th>SC</th>          <!-- ← BARU -->
                                                <th>Kota</th>         <!-- ← BARU -->
                                                <th class="text-right">Qty</th>
                                                <th class="text-right">Harga DPP</th>  <!-- ← BARU -->
                                                <th class="text-right">Nilai Retur</th>
                                                <th class="text-center">Target ABM</th>
                                                <th>Kategori</th>     <!-- ← BARU -->
                                                <th>Unit</th>
                                                <th>Keterangan</th>
                                                <th>Keterangan Detail</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($list_retur)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-3">
                                                    <i class="fas fa-inbox mr-1"></i>
                                                    Belum ada data retur untuk transaksi ini
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($list_retur as $i => $r): ?>
                                            <tr>
                                                <td class="text-center"><?= $i + 1 ?></td>
                                                <td><?= date('d/m/Y', strtotime($r['tanggal_retur'])) ?></td>
                                                <td><?= htmlspecialchars($r['no_retur'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($r['sc'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($r['kota'] ?? '-') ?></td>
                                                <td class="text-right">
                                                    <?= number_format($r['quantity'], 2, ',', '.') ?>
                                                </td>
                                                <td class="text-right">
                                                    <?= number_format($r['harga_dpp'] ?? 0, 0, ',', '.') ?>
                                                </td>
                                                <td class="text-right font-weight-bold text-danger">
                                                    <?= number_format($r['nilai_retur'], 0, ',', '.') ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($r['kurangi_target'] == 1): ?>
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-minus-circle mr-1"></i>
                                                            Kurangi Target
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                                            Tidak Kurangi
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($r['kategori'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($r['unit'] ?? '-') ?></td>
                                                <td class="small">
                                                    <?php
                                                    $ket = $r['keterangan'] ?? '-';
                                                    $badge = $ket === 'Replacement' ? 'badge-warning' : 'badge-danger';
                                                    ?>
                                                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($ket) ?></span>
                                                </td>
                                                <td class="small"><?= htmlspecialchars($r['keterangan_detail'] ?? '-') ?></td>
                                                <td class="text-center">
                                                    <a href="<?= base_url('kmt/omset/hapus_retur/' . $r['id']) ?>"
                                                       class="btn btn-xs btn-danger btn-hapus"
                                                       title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                        <?php if (!empty($list_retur)): ?>
                                        <tfoot style="background:#f4f4f4;font-weight:bold;">
                                            <tr>
                                                <td colspan="7" class="text-right">TOTAL:</td>
                                                <td class="text-right text-danger">
                                                    Rp <?= number_format($summary['total_retur'] ?? 0, 0, ',', '.') ?>
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /.row -->

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022
            <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.
        </strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(function () {
    $('#tblRetur').DataTable({
        responsive: true,
        pageLength: 25,
        ordering: false,
        language: { url: '<?= base_url('assets/plugins/datatables/id.json') ?>' }
    });

    // Format angka
    $('.angka').on('input', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
    });

    $('#selKeterangan').on('change', function () {
        // Kosongkan keterangan detail saat ganti pilihan
        $('input[name="keterangan_detail"]').val('').focus();
    });

    // Konfirmasi hapus
    $(document).on('click', '.btn-hapus', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({
            title: 'Hapus retur ini?',
            text: 'Data yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) window.location.href = url;
        });
    });
});
</script>