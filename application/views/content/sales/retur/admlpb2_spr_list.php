<?php $this->load->view('content/sales/retur_custom_css'); ?>
<?php /* views/content/sales/retur/admlpb2_spr_list.php */ ?>
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
                        <h1 class="m-0"><i class="fas fa-file-invoice mr-2 text-success"></i> SPR Siap Retur (Admin LPB2)</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">SPR Siap Retur</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- FLASH -->
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- LINK Lihat Retur -->
                <div class="row mb-3">
                    <div class="col-auto">
                        <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-primary">
                            <i class="fas fa-list"></i> Lihat Retur Penjualan Dibuat
                        </a>
                    </div>
                </div>

                <!-- FILTER -->
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <form method="get" action="<?= base_url('retur_penjualan/admlpb2') ?>">
                            <div class="row">
                                <div class="col-md-2">
                                    <label class="small mb-0">Dari Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="date1" value="<?= htmlspecialchars($filter['date1'] ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-0">Sampai Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" name="date2" value="<?= htmlspecialchars($filter['date2'] ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-0">Status SPR</label>
                                    <select name="status" class="form-control form-control-sm">
                                        <option value="disetujui_kadep" <?= ($filter['status'] ?? '') === 'disetujui_kadep' ? 'selected' : '' ?>>Disetujui Kadep (Belum Retur)</option>
                                        <option value="selesai" <?= ($filter['status'] ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai (Sudah Retur)</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-success btn-sm mr-1"><i class="fas fa-search"></i> Tampil</button>
                                    <a href="<?= base_url('retur_penjualan/admlpb2') ?>" class="btn btn-secondary btn-sm">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL SPR -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i> Daftar SPR — Siap Dibuat Retur Penjualan</h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($spr_list) ?> SPR</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0" id="tabelSPRLPB2">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No. SPR</th>
                                        <th>Tipe Retur</th>
                                        <th>Tanggal</th>
                                        <th>Customer</th>
                                        <th>Sales</th>
                                        <th class="text-center">Item</th>
                                        <th class="text-center">Status SPR</th>
                                        <th class="text-center">Status Retur</th>
                                        <th class="text-center no-sort">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($spr_list)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                Tidak ada SPR dengan status Disetujui Kadep
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($spr_list as $row): ?>
                                        <?php
                                            $retur_ada = $this->M_ReturPenjualan->get_retur_by_spr($row['id_spr']);
                                            $st = $row['status'];
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('retur_penjualan/detail/' . $row['id_spr']) ?>" class="font-weight-bold text-danger">
                                                    <?= htmlspecialchars($row['no_spr']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if (($row['tipe_retur'] ?? 'biasa') === 'replace'): ?>
                                                    <span class="badge badge-success px-2 py-1">REPLACE</span>
                                                <?php elseif (($row['tipe_retur'] ?? 'biasa') === 'service'): ?>
                                                    <span class="badge badge-warning px-2 py-1">SERVICE</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary px-2 py-1">RETUR</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-nowrap"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                            <td><?= htmlspecialchars($row['nama_customer'] ?: $row['nama_customer_master'] ?: '-') ?></td>
                                            <td><?= htmlspecialchars($row['nama_sales'] ?? '-') ?></td>
                                            <td class="text-center"><?= (int)$row['jumlah_item'] ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $st === 'disetujui_kadep' ? 'indigo' : 'success' ?>">
                                                    <?= $st === 'disetujui_kadep' ? 'Acc Kadep' : 'Selesai' ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($retur_ada): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check mr-1"></i><?= htmlspecialchars($retur_ada['no_retur']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Belum dibuat</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center text-nowrap">
                                                <!-- Lihat Detail SPR -->
                                                <a href="<?= base_url('retur_penjualan/detail/' . $row['id_spr']) ?>"
                                                   class="btn btn-sm btn-info" title="Lihat Detail SPR">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                                <!-- Buat Retur (hanya jika belum ada dan status disetujui_kadep) -->
                                                <?php if (!$retur_ada && $st === 'disetujui_kadep'): ?>
                                                    <a href="<?= base_url('retur_penjualan/retur/buat/' . $row['id_spr']) ?>"
                                                       class="btn btn-sm btn-success btn-buat-retur"
                                                       data-nospr="<?= htmlspecialchars($row['no_spr']) ?>"
                                                       title="Buat Retur Penjualan">
                                                        <i class="fas fa-undo-alt"></i> Buat Retur
                                                    </a>
                                                <?php elseif ($retur_ada): ?>
                                                    <a href="<?= base_url('retur_penjualan/retur/detail/' . $retur_ada['id_retur']) ?>"
                                                       class="btn btn-sm btn-primary" title="Lihat Retur">
                                                        <i class="fas fa-file-alt"></i> Lihat Retur
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
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
$(document).ready(function () {
    $('#tabelSPRLPB2').DataTable({
        responsive: true,
        autoWidth:  false,
        pageLength: 25,
        order:      [[1, 'desc']],
        columnDefs: [{ orderable: false, targets: [7] }],
        language: {
            search:      "Cari:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable:  "Tidak ada data SPR",
            paginate:    { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });

    $(document).on('click', '.btn-buat-retur', function(e) {
        e.preventDefault();
        var url   = $(this).attr('href');
        var noSpr = $(this).data('nospr');
        if (confirm('Buat Retur Penjualan dari SPR ' + noSpr + '?\n\nData barang dari SPR akan otomatis dipindahkan ke Retur Penjualan.')) {
            window.location.href = url;
        }
    });
});
</script>
