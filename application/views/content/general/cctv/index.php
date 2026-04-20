<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

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
                        <h1 class="m-0">Tracking CCTV</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item active">Tracking CCTV</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- Flash Message -->
                <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $this->session->flashdata('success') ?>
                </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $this->session->flashdata('error') ?>
                </div>
                <?php endif; ?>

                <!-- SUMMARY DASHBOARD -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= (int)$summary->total_online ?></h3>
                                <p>Kamera Online</p>
                            </div>
                            <div class="icon"><i class="fas fa-video"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= (int)$summary->total_offline ?></h3>
                                <p>Kamera Offline</p>
                            </div>
                            <div class="icon"><i class="fas fa-video-slash"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= (int)$summary->total_terekam ?></h3>
                                <p>Terekam</p>
                            </div>
                            <div class="icon"><i class="fas fa-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= (int)$summary->total_tidak_terekam ?></h3>
                                <p>Tidak Terekam</p>
                            </div>
                            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                </div>

                <!-- FILTER -->
                <div class="card card-default collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Data</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= site_url('cctv') ?>">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Tgl Awal</label>
                                        <input type="date" name="tgl_awal" class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($filter['tgl_awal'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Tgl Akhir</label>
                                        <input type="date" name="tgl_akhir" class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($filter['tgl_akhir'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Lokasi</label>
                                        <select name="lokasi" class="form-control form-control-sm">
                                            <option value="">-- Semua Lokasi --</option>
                                            <?php foreach ($lokasi_list as $l): ?>
                                                <option value="<?= htmlspecialchars($l->lokasi) ?>"
                                                    <?= ($filter['lokasi'] === $l->lokasi) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($l->lokasi) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control form-control-sm">
                                            <option value="">-- Semua --</option>
                                            <option value="Online"  <?= ($filter['status'] === 'Online')  ? 'selected' : '' ?>>Online</option>
                                            <option value="Offline" <?= ($filter['status'] === 'Offline') ? 'selected' : '' ?>>Offline</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Rekaman</label>
                                        <select name="status_rekaman" class="form-control form-control-sm">
                                            <option value="">-- Semua --</option>
                                            <option value="Terekam" <?= ($filter['status_rekaman'] === 'Terekam') ? 'selected' : '' ?>>Terekam</option>
                                            <option value="Tidak"   <?= ($filter['status_rekaman'] === 'Tidak')   ? 'selected' : '' ?>>Tidak</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL DATA CCTV -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title">
                            <i class="fas fa-video mr-2"></i> Data Tracking CCTV
                        </h3>
                        <div class="card-tools">
                            <a href="<?= site_url('cctv/tambah') ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Tambah Kamera
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-striped table-hover table-sm" id="tblCctv">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Tanggal</th>
                                    <th>Lokasi</th>
                                    <th>Nama Kamera</th>
                                    <th>IP Kamera</th>
                                    <th>Status</th>
                                    <th>Rekaman</th>
                                    <th>Keterangan</th>
                                    <th style="width:80px" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cctv_list)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-inbox mr-1"></i> Tidak ada data
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cctv_list as $i => $row): ?>
                                    <tr id="row-<?= $row->id ?>">
                                        <td class="text-center"><?= $i + 1 ?></td>
                                        <td><?= date('d/m/Y', strtotime($row->tgl)) ?></td>
                                        <td><?= htmlspecialchars($row->lokasi) ?></td>
                                        <td><?= htmlspecialchars($row->nama_kamera) ?></td>
                                        <td><code><?= htmlspecialchars($row->ip_kamera) ?></code></td>
                                        <td class="text-center">
                                            <?php if ($row->status === 'Online'): ?>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-circle" style="font-size:8px;"></i> Online
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-circle" style="font-size:8px;"></i> Offline
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row->status_rekaman === 'Terekam'): ?>
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-check mr-1"></i>Terekam
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-times mr-1"></i>Tidak
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row->keterangan ?? '-') ?></td>
                                        <td class="text-center">
                                            <a href="<?= site_url('cctv/edit/' . $row->id) ?>"
                                               class="btn btn-xs btn-warning" title="Edit">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <a href="<?= site_url('cctv/hapus/' . $row->id) ?>"
                                               class="btn btn-xs btn-danger btn-hapus" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- /.container-fluid -->
        </section>
    </div><!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div><!-- /.wrapper -->

<script>
$(document).ready(function () {
    // Konfirmasi hapus
    $(document).on('click', '.btn-hapus', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (confirm('Yakin ingin menghapus data kamera ini?')) {
            window.location.href = url;
        }
    });

    // Auto-refresh status badge setiap 30 detik
    setInterval(function () {
        $.getJSON('<?= site_url('cctv/refresh_status') ?>', function (data) {
            $.each(data, function (i, item) {
                var row = $('#row-' + item.id);
                if (!row.length) return;

                var badgeStatus  = row.find('td:eq(5) span');
                var badgeRekaman = row.find('td:eq(6) span');

                if (item.status === 'Online') {
                    badgeStatus.attr('class', 'badge badge-success')
                               .html('<i class="fas fa-circle" style="font-size:8px;"></i> Online');
                } else {
                    badgeStatus.attr('class', 'badge badge-danger')
                               .html('<i class="fas fa-circle" style="font-size:8px;"></i> Offline');
                }

                if (item.status_rekaman === 'Terekam') {
                    badgeRekaman.attr('class', 'badge badge-primary')
                                .html('<i class="fas fa-check mr-1"></i>Terekam');
                } else {
                    badgeRekaman.attr('class', 'badge badge-warning')
                                .html('<i class="fas fa-times mr-1"></i>Tidak');
                }
            });
        });
    }, 30000);
});
</script>
</body>