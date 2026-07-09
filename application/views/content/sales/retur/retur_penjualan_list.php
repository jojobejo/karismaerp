<!-- views/content/sales/retur/retur_penjualan_list.php -->
<!-- Daftar Retur Penjualan — untuk Admin Stock dan Team Collection -->
<style>
    .rp-badge-status { font-size: 11px; padding: 3px 8px; border-radius: 3px; font-weight: 600; }
    .table-rp-list th { background:#f8f9fa; font-size:12px; border:1px solid #dee2e6; }
    .table-rp-list td { font-size:12px; border:1px solid #dee2e6; vertical-align:middle; }
    .filter-bar { background:#f8f9fa; border:1px solid #dee2e6; border-radius:4px; padding:10px 14px; margin-bottom:12px; }
</style>

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
                        <h1 class="m-0">
                            <i class="fas fa-undo-alt mr-2 text-success"></i>
                            <?= htmlspecialchars($page_title_short ?? 'Retur Penjualan') ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($role_label ?? 'Daftar') ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- FLASH -->
                <?php foreach (['success'=>'success','error'=>'danger'] as $k=>$c): ?>
                    <?php if ($msg = $this->session->flashdata($k)): ?>
                        <div class="alert alert-<?= $c ?> alert-dismissible">
                            <i class="fas fa-<?= $k==='success'?'check-circle':'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- FILTER -->
                <form method="get" class="filter-bar">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small mb-1">Dari Tanggal</label>
                            <input type="date" name="date1" class="form-control form-control-sm" value="<?= htmlspecialchars($filter['date1'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="small mb-1">Sampai Tanggal</label>
                            <input type="date" name="date2" class="form-control form-control-sm" value="<?= htmlspecialchars($filter['date2'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="small mb-1">Status</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">-- Semua Status --</option>
                                <option value="menunggu_verifikasi" <?= ($filter['status'] ?? '') === 'menunggu_verifikasi' ? 'selected' : '' ?>>Menunggu Verifikasi Admin Stock</option>
                                <option value="terverifikasi" <?= ($filter['status'] ?? '') === 'terverifikasi' ? 'selected' : '' ?>>Terverifikasi Admin Stock</option>
                                <option value="menunggu_collection" <?= ($filter['status'] ?? '') === 'menunggu_collection' ? 'selected' : '' ?>>Menunggu Team Collection</option>
                                <option value="selesai" <?= ($filter['status'] ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="ditolak" <?= ($filter['status'] ?? '') === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-sm btn-primary mr-1"><i class="fas fa-search"></i> Filter</button>
                            <a href="<?= current_url() ?>" class="btn btn-sm btn-light"><i class="fas fa-sync"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="card shadow">
                    <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
                        <h3 class="card-title"><i class="fas fa-list mr-1"></i> Daftar Retur Penjualan — <?= htmlspecialchars($role_label ?? '') ?></h3>
                        <span class="badge badge-light text-success"><?= count($retur_list) ?> data</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-rp-list mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:40px;">No.</th>
                                        <th>No. Retur</th>
                                        <th>No. SPR</th>
                                        <th>Tgl Retur</th>
                                        <th>Customer</th>
                                        <th>Dibuat Logistik</th>
                                        <th class="text-right">Total Nilai</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width:100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($retur_list)): ?>
                                        <tr><td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Tidak ada data retur penjualan
                                        </td></tr>
                                    <?php else: ?>
                                        <?php
                                        $status_map = [
                                            'menunggu_verifikasi'  => ['warning',  'Menunggu Admin Stock'],
                                            'terverifikasi'        => ['info',     'Terverifikasi'],
                                            'menunggu_collection'  => ['primary',  'Menunggu Collection'],
                                            'selesai'              => ['success',  'Selesai'],
                                            'ditolak'              => ['danger',   'Ditolak'],
                                        ];
                                        ?>
                                        <?php foreach ($retur_list as $i => $r): ?>
                                        <?php $sm = $status_map[$r['status_retur']] ?? ['secondary', $r['status_retur']]; ?>
                                        <tr>
                                            <td class="text-center"><?= $i + 1 ?></td>
                                            <td class="font-weight-bold text-success"><?= htmlspecialchars($r['no_retur']) ?></td>
                                            <td><a href="<?= base_url('retur_penjualan/detail/' . $r['id_spr']) ?>" class="text-danger"><?= htmlspecialchars($r['no_spr']) ?></a></td>
                                            <td><?= $r['tanggal_retur'] ? date('d/m/Y', strtotime($r['tanggal_retur'])) : '-' ?></td>
                                            <td><?= htmlspecialchars($r['nama_customer'] ?: ($r['nama_customer_master'] ?? '-')) ?></td>
                                            <td><?= htmlspecialchars($r['create_by_retur'] ?? '-') ?></td>
                                            <td class="text-right font-weight-bold">Rp <?= number_format((float)($r['total_nilai'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $sm[0] ?> rp-badge-status"><?= $sm[1] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= base_url('retur_penjualan/retur/detail/' . $r['id_retur']) ?>"
                                                   class="btn btn-xs btn-info" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
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
