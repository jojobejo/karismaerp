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
                            <i class="fas fa-ellipsis-h text-secondary"></i> Data Others
                            <small class="text-muted">KMT CORN</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dashboard') ?>">KMT</a></li>
                            <li class="breadcrumb-item active">Others</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php $this->load->view('partial/main/alert') ?>

                <!-- Tombol Aksi -->
                <div class="mb-3">
                    <a href="<?= base_url('kmt/others/tambah') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Others
                    </a>
                    <a href="<?= base_url('kmt/others/export')
                              . '?tahun=' . $tahun
                              . '&bulan=' . $bulan
                              . '&id_wilayah=' . $id_wilayah ?>"
                       class="btn btn-success btn-sm ml-1">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </a>
                </div>

                <!-- Filter -->
                <?php $this->load->view('partial/main/filter', [
                    'filter_url'   => base_url('kmt/others'),
                    'show_bulan'   => true,
                    'tahun'        => $tahun,
                    'bulan'        => $bulan,
                    'id_wilayah'   => $id_wilayah,
                    'wilayah_list' => $wilayah_list,
                    'lv'     => $lv,
                ]); ?>

                <!-- Summary -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="info-box bg-secondary shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Biaya Others</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($total_biaya, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-info shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Jumlah Transaksi</span>
                                <span class="info-box-number"><?= count($list) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-1"></i> Daftar Others — <?= $tahun ?>
                            <?php if ($bulan): ?>
                                <span class="badge badge-info"><?= $nama_bulan[$bulan] ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblOthers" class="table table-bordered table-striped table-hover table-sm mb-0">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th width="40">#</th>
                                        <th>Tanggal</th>
                                        <th>Wilayah</th>
                                        <th>Uraian</th>
                                        <th class="text-right">Total Biaya</th>
                                        <th width="80">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list as $i => $row): ?>
                                    <tr>
                                        <td class="text-center"><?= $i + 1 ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= htmlspecialchars($row['nama_wilayah'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['uraian']) ?></td>
                                        <td class="text-right font-weight-bold">
                                            <?= number_format($row['total_biaya'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('kmt/others/edit/' . $row['id']) ?>"
                                               class="btn btn-xs btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('kmt/others/hapus/' . $row['id']) ?>"
                                               class="btn btn-xs btn-danger btn-hapus" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot style="background:#f4f4f4;font-weight:bold;">
                                    <tr>
                                        <td colspan="4" class="text-right">TOTAL:</td>
                                        <td class="text-right">
                                            <?= number_format($total_biaya, 0, ',', '.') ?>
                                        </td>
                                        <td></td>
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
    $('#tblOthers').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'desc']],
        columnDefs: [
            { targets: [4], className: 'dt-right' },
            { targets: [5], orderable: false }
        ],
        language: { url: '<?= base_url('assets/plugins/datatables/id.json') ?>' }
    });

    $(document).on('click', '.btn-hapus', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({
            title: 'Hapus data ini?',
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