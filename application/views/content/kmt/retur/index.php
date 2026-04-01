<!-- views/content/kmt/retur/index.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="KarismaLogo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">

        <!-- Header -->
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
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dashboard') ?>">KMT</a></li>
                            <li class="breadcrumb-item active">Retur</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php $this->load->view('partial/main/alert') ?>

                <!-- Tombol Action -->
                <div class="mb-3 d-flex justify-content-between">
                    <div>
                        <?php if ($lv == 1): ?>
                        <a href="<?= base_url('kmt/retur/tambah') ?>" class="btn btn-danger btn-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Retur
                        </a>
                        <button type="button" class="btn btn-primary btn-sm"
                                data-toggle="modal" data-target="#modalImport">
                            <i class="fas fa-file-import mr-1"></i> Import Excel
                        </button>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="<?= base_url('kmt/retur/export')
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
                    'filter_url' => base_url('kmt/retur'),
                    'show_bulan' => true,
                ]); ?>

                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="info-box bg-danger shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-undo"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Jumlah Retur (Filter)</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($total_retur, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-info shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-list-ol"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Jumlah Transaksi</span>
                                <span class="info-box-number"><?= count($list) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary per wilayah -->
                <?php if (!empty($summary)): ?>
                <div class="row mb-3">
                    <?php foreach ($summary as $s): ?>
                    <div class="col-md-4">
                        <div class="info-box bg-danger shadow-sm" style="opacity:.85;">
                            <span class="info-box-icon" style="font-size:1rem;">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">
                                    <?= htmlspecialchars($s['nama_wilayah']) ?>
                                    <small>(<?= $s['jumlah'] ?> transaksi)</small>
                                </span>
                                <span class="info-box-number">
                                    Rp <?= number_format($s['total_retur'], 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Tabel -->
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-1"></i> Daftar Retur — <?= $tahun ?>
                            <?php if ($bulan): ?>
                                <span class="badge badge-danger"><?= $nama_bulan[(int)$bulan] ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblRetur"
                                   class="table table-bordered table-striped table-hover table-sm mb-0">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th width="35">#</th>
                                        <th>Bln</th>
                                        <th>Tanggal</th>
                                        <th>No Retur</th>
                                        <th>SC</th>
                                        <th>Wilayah</th>
                                        <th>Nama Toko</th>
                                        <th>Kota</th>
                                        <th>Nama Barang</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Jumlah Retur</th>
                                        <th>Ket</th>
                                        <th width="70" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($list)): ?>
                                    <tr>
                                        <td colspan="13" class="text-center text-muted py-3">
                                            <i class="fas fa-inbox mr-1"></i> Tidak ada data retur
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php
                                    $nama_bln_short = ['','Jan','Feb','Mar','Apr','Mei','Jun',
                                                       'Jul','Agu','Sep','Okt','Nov','Des'];
                                    foreach ($list as $i => $row):
                                    ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= $nama_bln_short[(int)$row['bulan']] ?? '-' ?></td>
                                        <td style="white-space:nowrap;">
                                            <?= date('d/m/Y', strtotime($row['tanggal_retur'])) ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['no_retur'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['sc'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= htmlspecialchars($row['nama_wilayah'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['nama_toko']) ?></td>
                                        <td><?= htmlspecialchars($row['kota'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['produk']) ?></td>
                                        <td class="text-center">
                                            <?= number_format($row['quantity'], 2, ',', '.') ?>
                                        </td>
                                        <td class="text-right font-weight-bold text-danger">
                                            <?= number_format($row['nilai_retur'], 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <?php
                                            $ket = $row['keterangan'] ?? '-';
                                            $ket_cls = $ket === 'Replacement' ? 'badge-warning' : 'badge-danger';
                                            ?>
                                            <span class="badge <?= $ket_cls ?>">
                                                <?= htmlspecialchars($ket) ?>
                                            </span>
                                        </td>
                                        <td class="text-center" style="white-space:nowrap;">
                                            <?php if ($lv == 1): ?>
                                            <a href="<?= base_url('kmt/retur/edit/' . $row['id']) ?>"
                                               class="btn btn-xs btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('kmt/retur/hapus/' . $row['id']) ?>"
                                               class="btn btn-xs btn-danger btn-hapus" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot style="background:#f4f4f4;font-weight:bold;">
                                    <tr>
                                        <td colspan="10" class="text-right">TOTAL:</td>
                                        <td class="text-right text-danger">
                                            <?= number_format($total_retur, 0, ',', '.') ?>
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

<!-- Modal Import — pola sama dengan modal omset -->
<?php if ($lv == 1): ?>
<?php $this->load->view('content/kmt/modal/modal_import', [
    'import_url'   => base_url('kmt/retur/import'),
    'template_url' => base_url('kmt/retur/template_retur'),
    'import_title' => 'Import Data Retur dari Excel',
    'import_note'  => 'File harus memiliki sheet <strong>RETUR</strong>. Baris 1 = judul, baris 2 = header, data mulai baris 3.',
]); ?>
<?php endif; ?>

<script>
$(function () {
    $('#tblRetur').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[2, 'desc']],
        columnDefs: [
            { targets: [9],  className: 'dt-center' },
            { targets: [10], className: 'dt-right'  },
            { targets: [12], orderable: false        },
        ],
        language: {
            url: '<?= base_url('assets/plugins/datatables/id.json') ?>'
        }
    });

    // Konfirmasi hapus
    $(document).on('click', '.btn-hapus', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({
            title: 'Hapus data retur ini?',
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