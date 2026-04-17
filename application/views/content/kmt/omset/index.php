<!-- content/kmt/omset/index.php -->
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
                            <i class="fas fa-shopping-cart text-success"></i> Data Omset
                            <small class="text-muted">KMT CORN</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dashboard') ?>">KMT</a></li>
                            <li class="breadcrumb-item active">Omset</li>
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
                        <a href="<?= base_url('kmt/omset/tambah') ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Omset
                        </a>
                
                        <a href="<?= base_url('kmt/retur') ?>" class="btn btn-danger btn-sm">
                            <i class="fas fa-undo mr-1"></i> Retur
                        </a>
                
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalImport">
                            <i class="fas fa-file-import mr-1"></i> Import Excel
                        </button>
                        <?php endif; ?>
                    </div>
                
                    <div>
                        <a href="<?= base_url('kmt/omset/export')
                                . '?tahun=' . $tahun
                                . '&bulan=' . $bulan
                                . '&id_wilayah=' . $id_wilayah ?>"
                            class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    </div>

                </div>

                <!-- Filter -->
                <?php $this->load->view('partial/main/filter', [
                    'filter_url' => base_url('kmt/omset'),
                    'show_bulan' => true,
                ]); ?>

                <!-- Filter tambahan: hanya tampilkan yang punya no_retur -->
                <div class="mb-3">
                    <a href="<?= base_url('kmt/omset')
                                . '?tahun='      . $tahun
                                . '&bulan='      . $bulan
                                . '&id_wilayah=' . $id_wilayah
                                . ($has_retur ? '' : '&has_retur=1') ?>"
                    class="btn btn-sm <?= $has_retur ? 'btn-danger' : 'btn-outline-danger' ?>">
                        <i class="fas fa-undo mr-1"></i>
                        <?= $has_retur ? 'Menampilkan Retur Saja — Klik untuk Reset' : 'Tampilkan Retur Saja' ?>
                    </a>
                    <?php if ($has_retur): ?>
                    <span class="badge badge-danger ml-1">
                        <?= count($list) ?> transaksi memiliki nomor retur
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Summary Card -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="info-box bg-success shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Omset (Filter)</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($total_omset, 0, ',', '.') ?>
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

                <!-- Tabel -->
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-1"></i> Daftar Omset — <?= $tahun ?>
                            <?php if ($bulan): ?>
                                <span class="badge badge-info"><?= $nama_bulan[$bulan] ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblOmset" class="table table-bordered table-striped table-hover table-sm mb-0">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th width="35">#</th>
                                        <th>Tanggal</th>
                                        <th>Wilayah</th>
                                        <th>Nomor Fax</th>
                                        <th>Nama Toko</th>
                                        <th>Kota</th>
                                        <th>Produk</th>
                                        <th>Sales SO</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Penj. DPP Neto</th>
                                        <th width="70" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list as $i => $row): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= htmlspecialchars($row['nama_wilayah'] ?? '-') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($row['nomor'] ?? '-') ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['nama_toko']) ?></td>
                                        <td><?= htmlspecialchars($row['kota'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['produk']) ?></td>
                                        <td><?= htmlspecialchars($row['sales_so'] ?? '-') ?></td>
                                        <td class="text-right"><?= number_format($row['quantity'], 2, ',', '.') ?></td>
                                        <td class="text-right font-weight-bold">
                                            <?= number_format($row['penj_dpp_neto'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($lv == 1): ?>
                                            <a href="<?= base_url('kmt/omset/edit/' . $row['id']) ?>"
                                            class="btn btn-xs btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('kmt/omset/hapus/' . $row['id']) ?>"
                                            class="btn btn-xs btn-danger btn-hapus" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php else: ?>
                                            <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot style="background:#f4f4f4;font-weight:bold;">
                                    <tr>
                                        <td colspan="9" class="text-right">TOTAL:</td>
                                        <td class="text-right">
                                            <?= number_format($total_omset, 0, ',', '.') ?>
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

<?php if ($lv == 1): ?>
<?php $this->load->view('content/kmt/modal/modal_import', [
    'import_url'   => base_url('kmt/omset/import'),
    'template_url' => base_url('kmt/omset/template_omset'),
    'import_title' => 'Import Data Omset dari Excel',
]); ?>
<?php endif; ?>

<script>
$(function () {
    $('#tblOmset').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        columnDefs: [
            { targets: [8, 9], className: 'dt-right' },
            { targets: [10],   orderable: false },
        ],
        language: {
            url: '<?= base_url('assets/plugins/datatables/id.json') ?>'
        }
    });

    // Konfirmasi hapus
    $(document).on('click', '.btn-hapus', function(e) {
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
        }).then(function(result) {
            if (result.isConfirmed) window.location.href = url;
        });
    });
});
</script>
