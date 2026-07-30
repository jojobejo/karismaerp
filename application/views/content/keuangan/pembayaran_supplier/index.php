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
                    <div class="col-sm-7">
                        <h1 class="m-0"><i class="fas fa-file-invoice-dollar mr-2"></i>Pembayaran Supplier</h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">Pembayaran Supplier</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (empty($schema_ready)): ?>
                    <div class="alert alert-danger">
                        Schema accounting pembayaran supplier belum lengkap.
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-3">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= number_format((float)($summary['total_supplier'] ?? 0), 0, ',', '.') ?></h3>
                                <p>Supplier Terhutang</p>
                            </div>
                            <div class="icon"><i class="fas fa-truck"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3><?= number_format((float)($summary['total_dokumen'] ?? 0), 0, ',', '.') ?></h3>
                                <p>Dokumen Hutang</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-invoice"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3 style="font-size: 1.35rem;">Rp <?= number_format((float)($summary['total_outstanding'] ?? 0), 0, ',', '.') ?></h3>
                                <p>Sisa Hutang</p>
                            </div>
                            <div class="icon"><i class="fas fa-balance-scale"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3 style="font-size: 1.35rem;">Rp <?= number_format((float)($summary['total_payment_posted'] ?? 0), 0, ',', '.') ?></h3>
                                <p>Payment Posted</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-search mr-1"></i>Cari Supplier</h3>
                        <div class="card-tools">
                            <a href="<?= base_url('keuangan/pembayaran-supplier/history') ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-history mr-1"></i>Histori
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= base_url('keuangan/pembayaran-supplier') ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="q" class="form-control" placeholder="Nama supplier / ID supplier" value="<?= htmlspecialchars($keyword ?? '') ?>">
                                </div>
                                <div class="col-md-4 mt-2 mt-md-0">
                                    <button class="btn btn-primary"><i class="fas fa-search mr-1"></i>Tampilkan</button>
                                    <a href="<?= base_url('keuangan/pembayaran-supplier') ?>" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Supplier dengan Hutang Belum Lunas</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover table-sm" id="tabelSupplierOutstanding">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Supplier</th>
                                    <th class="text-center">Dokumen</th>
                                    <th class="text-right">Total Hutang</th>
                                    <th class="text-right">Retur/Payment</th>
                                    <th class="text-right">Sisa Hutang</th>
                                    <th class="text-center">Tanggal Tertua</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($suppliers)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada hutang supplier terbuka.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($supplier['nama_supplier']) ?></strong>
                                                <br><small class="text-muted">ID Supplier: <?= htmlspecialchars($supplier['id_supplier']) ?></small>
                                            </td>
                                            <td class="text-center"><?= number_format((float)$supplier['total_dokumen'], 0, ',', '.') ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$supplier['total_hutang'], 0, ',', '.') ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$supplier['total_pengurang'], 0, ',', '.') ?></td>
                                            <td class="text-right font-weight-bold text-danger">Rp <?= number_format((float)$supplier['outstanding'], 0, ',', '.') ?></td>
                                            <td class="text-center"><?= !empty($supplier['tanggal_tertua']) ? date('d/m/Y', strtotime($supplier['tanggal_tertua'])) : '-' ?></td>
                                            <td class="text-center">
                                                <a href="<?= base_url('keuangan/pembayaran-supplier/supplier/' . (int)$supplier['id_supplier']) ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye mr-1"></i>Detail
                                                </a>
                                                <a href="<?= base_url('keuangan/pembayaran-supplier/form/' . (int)$supplier['id_supplier']) ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-money-check-alt mr-1"></i>Bayar
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
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>

<script>
$(function () {
    if ($.fn.DataTable) {
        $('#tabelSupplierOutstanding').DataTable({
            pageLength: 25,
            order: [[4, 'desc']]
        });
    }
});
</script>
