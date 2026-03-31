<!-- views/kmt/retur/index.php -->
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

                <!-- Tombol Aksi -->
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <a href="<?= base_url('kmt/retur/tambah') ?>" class="btn btn-danger btn-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Retur
                        </a>
                        <a href="<?= base_url('kmt/retur/export')
                                    . '?tahun='      . $tahun
                                    . '&bulan='      . $bulan
                                    . '&id_wilayah=' . $id_wilayah ?>"
                           class="btn btn-success btn-sm ml-1">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    </div>
                </div>

                <?php $this->load->view('partial/main/filter', [
                    'filter_url' => base_url('kmt/retur'),
                    'show_bulan' => true,
                ]); ?>

                <!-- Summary per wilayah -->
                <?php if (!empty($summary)): ?>
                <div class="row mb-3">
                    <?php foreach ($summary as $s): ?>
                    <div class="col-md-4">
                        <div class="info-box bg-danger shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-undo"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">
                                    <?= htmlspecialchars($s['nama_wilayah']) ?>
                                    (<?= $s['jumlah'] ?> transaksi)
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

                <!-- Tabel Data Retur -->
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-1"></i>
                            Daftar Retur — <?= $tahun ?>
                            <?php if ($bulan): ?>
                                / <?= $nama_bulan[(int)$bulan] ?>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblRetur"
                                   class="table table-bordered table-striped table-hover table-sm mb-0">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Tgl Retur</th>
                                        <th>Wilayah</th>
                                        <th>No Retur</th>
                                        <th>SC</th>
                                        <th>Nama Toko</th>
                                        <th>Kota</th>
                                        <th>Nama Barang</th>
                                        <th class="text-center">Qty</th>
                                        <th>Unit</th>
                                        <th class="text-right">Harga DPP</th>
                                        <th class="text-right">Nilai Retur</th>
                                        <th class="text-center">Target ABM</th>
                                        <th>Kategori</th>
                                        <th>Keterangan</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($list)): ?>
                                    <tr>
                                        <td colspan="16" class="text-center text-muted py-3">
                                            <i class="fas fa-inbox mr-1"></i> Tidak ada data retur
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($list as $i => $row): ?>
                                    <tr>
                                        <td class="text-center"><?= $i + 1 ?></td>

                                        <td><?= date('d/m/Y', strtotime($row['tanggal_retur'])) ?></td>

                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= htmlspecialchars($row['nama_wilayah'] ?? '-') ?>
                                            </span>
                                        </td>

                                        <td><?= htmlspecialchars($row['no_retur'] ?? '-') ?></td>

                                        <td><?= htmlspecialchars($row['sc'] ?? '-') ?></td>

                                        <td><?= htmlspecialchars($row['nama_toko']) ?></td>

                                        <td><?= htmlspecialchars($row['kota'] ?? '-') ?></td>

                                        <td><?= htmlspecialchars($row['produk']) ?></td>

                                        <td class="text-center">
                                            <?= number_format($row['quantity'], 2, ',', '.') ?>
                                        </td>

                                        <td><?= htmlspecialchars($row['unit'] ?? '-') ?></td>

                                        <td class="text-right">
                                            <?= number_format($row['harga_dpp'] ?? 0, 0, ',', '.') ?>
                                        </td>

                                        <td class="text-right font-weight-bold text-danger">
                                            <?= number_format($row['nilai_retur'], 0, ',', '.') ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ((int)($row['kurangi_target'] ?? 0) === 1): ?>
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

                                        <td>
                                            <?php
                                            $kat = $row['kategori'] ?? '-';

                                            switch ($kat) {
                                                case 'Replacement':
                                                    $kat_class = 'badge-warning';
                                                    break;
                                                case 'Barang bermasalah':
                                                    $kat_class = 'badge-danger';
                                                    break;
                                                case 'Expired':
                                                    $kat_class = 'badge-secondary';
                                                    break;
                                                default:
                                                    $kat_class = 'badge-light';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $kat_class ?>">
                                                <?= htmlspecialchars($kat) ?>
                                            </span>
                                        </td>
                                        <td class="small">
                                            <?= htmlspecialchars($row['keterangan'] ?? '-') ?>
                                        </td>

                                        <td class="text-center" style="white-space:nowrap;">
                                            <a href="<?= base_url('kmt/retur/edit/' . $row['id']) ?>"
                                               class="btn btn-xs btn-warning"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('kmt/retur/hapus/' . $row['id']) ?>"
                                               class="btn btn-xs btn-danger btn-hapus"
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot style="background:#f4f4f4;font-weight:bold;">
                                    <tr>
                                        <td colspan="11" class="text-right">TOTAL NILAI RETUR:</td>
                                        <td class="text-right text-danger">
                                            Rp <?= number_format($total_retur, 0, ',', '.') ?>
                                        </td>
                                        <td colspan="4"></td>
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

<script>
$(function () {
    $('#tblRetur').DataTable({
        responsive: true,
        pageLength: 25,
        columnDefs: [
            { targets: [0, 8, 12], className: 'dt-center' },
            { targets: [10, 11],   className: 'dt-right'  },
            { targets: [15],       orderable: false        },
        ],
        language: { url: '<?= base_url('assets/plugins/datatables/id.json') ?>' }
    });

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