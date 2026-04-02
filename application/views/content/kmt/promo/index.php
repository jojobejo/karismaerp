<!-- ================================================================
     GANTI views/content/kmt/promo/index.php
     ================================================================ -->
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
                            <i class="fas fa-tag text-warning"></i> Promo Material / Peralatan
                            <small class="text-muted">KMT CORN</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dashboard') ?>">KMT</a></li>
                            <li class="breadcrumb-item active">Promo</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php $this->load->view('partial/main/alert') ?>

                <!-- Tombol Aksi -->
                <div class="mb-3 d-flex justify-content-between">
                    <div>
                        <a href="<?= base_url('kmt/promo/tambah') ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Item
                        </a>
                        <?php if ($lv == 1): ?>
                        <button type="button" class="btn btn-primary btn-sm"
                                data-toggle="modal" data-target="#modalImport">
                            <i class="fas fa-file-import mr-1"></i> Import Excel
                        </button>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="<?= base_url('kmt/promo/export')
                                    . '?tahun='      . $tahun
                                    . '&bulan='      . $bulan
                                    . '&id_wilayah=' . $id_wilayah ?>"
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    </div>
                </div>

                <!-- Filter -->
                <?php $this->load->view('partial/main/filter', [
                    'filter_url' => base_url('kmt/promo'),
                    'show_bulan' => true,
                ]); ?>

                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="info-box bg-warning shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Biaya</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($total_biaya, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-info shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-ad"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Promo Material</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($total_promo, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-secondary shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-tools"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Peralatan</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($total_peralatan, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-1"></i>
                            Daftar Promo Material / Peralatan — <?= $tahun ?>
                            <?php if ($bulan): ?>
                                <span class="badge badge-warning"><?= $nama_bulan[(int)$bulan] ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblPromo"
                                   class="table table-bordered table-striped table-hover table-sm mb-0"
                                   style="white-space:nowrap;">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th width="35">#</th>
                                        <th>Tanggal</th>
                                        <th>Wilayah</th>
                                        <th>Supplier</th>
                                        <th>Nama Barang</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-right">Promo Material</th>
                                        <th class="text-right">Peralatan</th>
                                        <th>Keterangan</th>
                                        <th width="70" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($list)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-3">
                                            <i class="fas fa-inbox mr-1"></i> Tidak ada data
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($list as $i => $row): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= htmlspecialchars($row['nama_wilayah'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['supplier'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['nama_item']) ?></td>
                                        <td class="text-right font-weight-bold">
                                            <?= number_format($row['total_biaya'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-right text-info">
                                            <?= ($row['promo_material'] > 0)
                                                ? number_format($row['promo_material'], 0, ',', '.')
                                                : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-right text-secondary">
                                            <?= ($row['peralatan'] > 0)
                                                ? number_format($row['peralatan'], 0, ',', '.')
                                                : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="small text-muted">
                                            <?= htmlspecialchars($row['keterangan'] ?? '-') ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('kmt/promo/edit/' . $row['id']) ?>"
                                               class="btn btn-xs btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('kmt/promo/hapus/' . $row['id']) ?>"
                                               class="btn btn-xs btn-danger btn-hapus" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot style="background:#f4f4f4; font-weight:bold;">
                                    <tr>
                                        <td colspan="5" class="text-right">TOTAL:</td>
                                        <td class="text-right">
                                            <?= number_format($total_biaya, 0, ',', '.') ?>
                                        </td>
                                        <td class="text-right text-info">
                                            <?= number_format($total_promo, 0, ',', '.') ?>
                                        </td>
                                        <td class="text-right text-secondary">
                                            <?= number_format($total_peralatan, 0, ',', '.') ?>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

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

<?php if ($lv == 1): ?>
<?php $this->load->view('content/kmt/modal/modal_import', [
    'import_url'   => base_url('kmt/promo/import') . '?id_wilayah=' . $id_wilayah,
    'template_url' => null,
    'import_title' => 'Import Data Promo Material dari Excel',
    'import_note'  => 'File harus memiliki sheet <strong>PROMO MATERIAL</strong>. Baris 1 = judul, baris 2 = header, data mulai baris 3.',
]); ?>
<?php endif; ?>

<script>
$(function () {
    $('#tblPromo').DataTable({
        responsive: true,
        pageLength: 25,
        scrollX: true,
        autoWidth: false,
        order: [[1, 'desc']],
        columnDefs: [
            { targets: [5, 6, 7], className: 'dt-right' },
            { targets: [9], orderable: false },
        ],
        language: { url: '<?= base_url('assets/plugins/datatables/id.json') ?>' }
    });

    $(document).on('click', '.btn-hapus', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({
            title: 'Hapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(r => { if (r.isConfirmed) window.location.href = url; });
    });
});
</script>