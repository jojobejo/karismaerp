<!-- dashboard.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="KarismaLogo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">

        <!-- Page Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-chart-bar text-primary"></i>
                            Dashboard KMT CORN
                            <small class="text-muted fs-6">Cost / Hasil per Tahun</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item active">KMT Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <section class="content">
            <div class="container-fluid">

                <!-- ============================
                     FILTER CARD
                ============================= -->
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter"></i> Filter Data
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="<?= base_url('kmt/dashboard') ?>" id="formFilter">
                            <div class="row align-items-end">
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label><i class="fas fa-calendar"></i> Tahun</label>
                                        <select name="tahun" class="form-control form-control-sm select2" style="width:100%">
                                            <?php for ($y = date('Y'); $y >= 2022; $y--): ?>
                                                <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>>
                                                    <?= $y ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>

                                <?php
                                // ABM tidak bisa ganti wilayah
                                $is_abm = ((int)$this->session->userdata('akses_lv') === 3);
                                ?>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label><i class="fas fa-map-marker-alt"></i> Wilayah</label>

                                        <?php if ($lv === 3): ?>
                                            <?php
                                                // Cari nama wilayah ABM
                                                $nama_wil = '-';
                                                foreach ($wilayah_list as $w) {
                                                    if ($w['id'] == $id_wilayah) { $nama_wil = $w['nama_wilayah']; break; }
                                                }
                                            ?>
                                            <!-- Nilai tetap dikirim saat form submit -->
                                            <input type="hidden" name="id_wilayah" value="<?= $id_wilayah ?>">
                                            <input type="text" class="form-control form-control-sm"
                                                value="<?= htmlspecialchars($nama_wil) ?>" disabled>
                                        <?php else: ?>
                                            <select name="id_wilayah" class="form-control form-control-sm select2" style="width:100%">
                                                <option value="">-- Semua Wilayah --</option>
                                                <?php foreach ($wilayah_list as $w): ?>
                                                    <option value="<?= $w['id'] ?>"
                                                        <?= ($id_wilayah == $w['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($w['nama_wilayah']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-2 col-sm-12 mt-2 mt-md-0">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                                        <i class="fas fa-search"></i> Tampilkan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.card filter -->

                <!-- ============================
                     SUMMARY INFO BOXES
                ============================= -->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-info elevation-1">
                                <i class="fas fa-money-bill-wave"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Omset</span>
                                <span class="info-box-number">
                                    <?= 'Rp ' . number_format($summary['total_omset'], 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-danger elevation-1">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Biaya</span>
                                <span class="info-box-number">
                                    <?= 'Rp ' . number_format($summary['total_biaya'], 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-warning elevation-1">
                                <i class="fas fa-users"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Gaji</span>
                                <span class="info-box-number">
                                    <?= 'Rp ' . number_format($summary['total_gaji'], 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon <?= $summary['cost_per_hasil'] > 30 ? 'bg-danger' : 'bg-success' ?> elevation-1">
                                <i class="fas fa-percent"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cost / Hasil (YTD)</span>
                                <span class="info-box-number">
                                    <?= number_format($summary['cost_per_hasil'], 2) ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.row info boxes -->

                <!-- ============================
                     TABEL YTD BULANAN
                ============================= -->
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table"></i>
                            Rekap YTD Bulanan — Tahun <?= $tahun ?>
                            <?php if ($id_wilayah): ?>
                                <?php foreach ($wilayah_list as $w): ?>
                                    <?php if ($w['id'] == $id_wilayah): ?>
                                        <span class="badge badge-info ml-1"><?= $w['nama_wilayah'] ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="badge badge-secondary ml-1">Semua Wilayah</span>
                            <?php endif; ?>
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblYTD" class="table table-bordered table-striped table-hover table-sm mb-0 dataTable">
                                <thead>
                                    <tr class="text-center" style="background:#1f3864;color:#fff;">
                                        <th style="min-width:60px">BULAN</th>
                                        <th style="min-width:140px">OMSET</th>
                                        <th style="min-width:130px">OPERASIONAL</th>
                                        <th style="min-width:120px">DCA</th>
                                        <th style="min-width:120px">PERALATAN</th>
                                        <th style="min-width:120px">OTHERS</th>
                                        <th style="min-width:130px">GAJI</th>
                                        <th style="min-width:140px">TOTAL BIAYA</th>
                                        <th style="min-width:110px">COST/HASIL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $grand = array_fill_keys(
                                        ['omset','operasional','dca','peralatan','others','gaji','total_biaya'], 0
                                    );
                                    foreach ($ytd as $row):
                                        foreach (array_keys($grand) as $k) {
                                            $grand[$k] += $row[$k];
                                        }
                                        $ada_data = $row['total_biaya'] > 0 || $row['omset'] > 0;
                                    ?>
                                    <tr class="<?= !$ada_data ? 'text-muted' : '' ?>">
                                        <td class="text-center font-weight-bold"><?= $row['bulan'] ?></td>

                                        <td class="text-right">
                                            <?= $row['omset'] > 0
                                                ? number_format($row['omset'], 0, ',', '.')
                                                : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-right">
                                            <?= $row['operasional'] > 0
                                                ? number_format($row['operasional'], 0, ',', '.')
                                                : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-right">
                                            <?= $row['dca'] > 0
                                                ? number_format($row['dca'], 0, ',', '.')
                                                : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-right">
                                            <?= $row['peralatan'] > 0
                                                ? number_format($row['peralatan'], 0, ',', '.')
                                                : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-right">
                                            <?= $row['others'] > 0
                                                ? number_format($row['others'], 0, ',', '.')
                                                : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-right">
                                            <?= $row['gaji'] > 0
                                                ? number_format($row['gaji'], 0, ',', '.')
                                                : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            <?= $row['total_biaya'] > 0
                                                ? number_format($row['total_biaya'], 0, ',', '.')
                                                : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['cost_per_hasil'] > 0): ?>
                                                <?php
                                                $badge = $row['cost_per_hasil'] >= 30 ? 'danger'
                                                       : ($row['cost_per_hasil'] >= 20 ? 'warning' : 'success');
                                                ?>
                                                <span class="badge badge-<?= $badge ?>">
                                                    <?= number_format($row['cost_per_hasil'], 1) ?>%
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background:#1f3864;color:#fff;font-weight:bold;">
                                        <td class="text-center">TOTAL</td>
                                        <td class="text-right"><?= number_format($grand['omset'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($grand['operasional'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($grand['dca'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($grand['peralatan'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($grand['others'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($grand['gaji'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($grand['total_biaya'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <?php
                                            $cph_grand = $grand['omset'] > 0
                                                ? round($grand['total_biaya'] / $grand['omset'] * 100, 1)
                                                : 0;
                                            $badge_grand = $cph_grand >= 30 ? 'danger'
                                                         : ($cph_grand >= 20 ? 'warning' : 'success');
                                            ?>
                                            <span class="badge badge-<?= $badge_grand ?>">
                                                <?= number_format($cph_grand, 1) ?>%
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card YTD -->

                <!-- ============================
                     TABEL COST PER HASIL PER WILAYAH
                ============================= -->
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-map-marked-alt"></i>
                            Cost / Hasil per Wilayah — <?= $tahun ?>
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblWilayah" class="table table-bordered table-striped table-hover table-sm mb-0 dataTable">
                                <thead>
                                    <tr class="text-center" style="background:#1f3864;color:#fff;">
                                        <th>WILAYAH</th>
                                        <th>Q1 (Jan–Mar)</th>
                                        <th>Q2 (Apr–Jun)</th>
                                        <th>Q3 (Jul–Sep)</th>
                                        <th>Q4 (Okt–Des)</th>
                                        <th>TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cost_per_wilayah as $cw): ?>
                                    <tr>
                                        <td class="font-weight-bold"><?= htmlspecialchars($cw['wilayah']) ?></td>
                                        <?php foreach (['q1','q2','q3','q4','total'] as $q): ?>
                                            <td class="text-center">
                                                <?php if ($cw[$q] > 0): ?>
                                                    <?php
                                                    $b = $cw[$q] >= 30 ? 'danger'
                                                       : ($cw[$q] >= 20 ? 'warning' : 'success');
                                                    ?>
                                                    <span class="badge badge-<?= $b ?>">
                                                        <?= number_format($cw[$q], 1) ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card wilayah -->

            </div>
            <!-- /.container-fluid -->
        </section>
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>Copyright &copy; 2022
            <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.
        </strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>

    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
<!-- ./wrapper -->

<!-- DataTables init — pastikan sudah include JS DataTables di sidebar/footer partial -->
<script>
$(function () {
    // Tabel YTD — tanpa sorting/pagination karena sudah urut bulan
    $('#tblYTD').DataTable({
        paging:   false,
        searching: false,
        ordering: false,
        info:     false,
        columnDefs: [
            { targets: [1,2,3,4,5,6,7], className: 'dt-right' },
            { targets: [0,8],            className: 'dt-center' }
        ]
    });

    // Tabel Per Wilayah
    $('#tblWilayah').DataTable({
        paging:   false,
        searching: false,
        ordering: true,
        info:     false,
    });

    // Select2 untuk filter
    $('.select2').select2({ width: '100%' });
});
</script>