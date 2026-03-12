<!-- operasional/index.php -->
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
                            <i class="fas fa-car text-warning"></i> Biaya Operasional
                            <small class="text-muted">KMT CORN</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dashboard') ?>">KMT</a></li>
                            <li class="breadcrumb-item active">Operasional</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php $this->load->view('partial/main/alert')?>

                <!-- Tombol Tambah — KADEP & ABM -->
                <?php if ($lv != 2): ?>
                <div class="mb-3">
                    <a href="<?= base_url('kmt/operasional/tambah') ?>" class="btn btn-warning btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Biaya
                    </a>
                </div>
                <?php endif; ?>

                <!-- Filter -->
                <?php $this->load->view('partial/main/filter', [
                    'filter_url' => base_url('kmt/operasional'),
                    'show_bulan' => true,
                ]); ?>

                <!-- Summary -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="info-box bg-warning shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Biaya Operasional</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($total_biaya, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-1"></i> Daftar Biaya Operasional — <?= $tahun ?>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblOperasional" class="table table-bordered table-striped table-hover table-sm mb-0">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th>Wilayah</th>
                                        <th>Nama</th>
                                        <th class="text-right">Hotel</th>
                                        <th class="text-right">Per Diem</th>
                                        <th class="text-right">Entertain</th>
                                        <th class="text-right">Gasoline</th>
                                        <th class="text-right">Transportasi</th>
                                        <th class="text-right">Lain-lain</th>
                                        <th class="text-right">Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list as $i => $row): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                        <td><span class="badge badge-secondary"><?= $row['nama_wilayah'] ?? '-' ?></span></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td class="text-right"><?= $row['hotel'] > 0 ? number_format($row['hotel'],0,',','.') : '-' ?></td>
                                        <td class="text-right"><?= $row['per_diem'] > 0 ? number_format($row['per_diem'],0,',','.') : '-' ?></td>
                                        <td class="text-right"><?= $row['entertainment'] > 0 ? number_format($row['entertainment'],0,',','.') : '-' ?></td>
                                        <td class="text-right"><?= $row['gasoline'] > 0 ? number_format($row['gasoline'],0,',','.') : '-' ?></td>
                                        <td class="text-right"><?= $row['transportasi'] > 0 ? number_format($row['transportasi'],0,',','.') : '-' ?></td>
                                        <td class="text-right"><?= $row['lain_lain'] > 0 ? number_format($row['lain_lain'],0,',','.') : '-' ?></td>
                                        <td class="text-right font-weight-bold">
                                            <?= number_format($row['total_biaya'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($lv != 2): ?>
                                            <a href="<?= base_url('kmt/operasional/edit/' . $row['id']) ?>"
                                               class="btn btn-xs btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('kmt/operasional/hapus/' . $row['id']) ?>"
                                               class="btn btn-xs btn-danger btn-hapus" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php else: ?>
                                            <a href="<?= base_url('kmt/operasional/edit/' . $row['id']) ?>"
                                               class="btn btn-xs btn-info" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot style="background:#f4f4f4;font-weight:bold;">
                                    <tr>
                                        <td colspan="10" class="text-right">TOTAL:</td>
                                        <td class="text-right"><?= number_format($total_biaya, 0, ',', '.') ?></td>
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
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(function () {
    $('#tblOperasional').DataTable({
        responsive: true, pageLength: 25,
        columnDefs: [{ targets: [4,5,6,7,8,9,10], className: 'dt-right' }, { targets: [11], orderable: false }],
        language: { url: '<?= base_url('assets/plugins/datatables/id.json') ?>' }
    });
    $(document).on('click', '.btn-hapus', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({ title:'Hapus data ini?', icon:'warning', showCancelButton:true,
            confirmButtonColor:'#d33', confirmButtonText:'Ya, Hapus!', cancelButtonText:'Batal'
        }).then(r => { if (r.isConfirmed) window.location.href = url; });
    });
});
</script>
