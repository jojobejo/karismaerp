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
                        <h1 class="m-0"><i class="fas fa-cash-register mr-2"></i>Pembayaran Faktur</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">Pembayaran Faktur</li>
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

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-search mr-1"></i>Cari Customer</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= base_url('keuangan/pembayaran') ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="q" class="form-control" placeholder="Nama customer / kode customer"
                                           value="<?= htmlspecialchars($keyword ?? '') ?>">
                                </div>
                                <div class="col-md-3 mt-2 mt-md-0">
                                    <button class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i>Tampilkan
                                    </button>
                                    <a href="<?= base_url('keuangan/pembayaran') ?>" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Customer dengan Faktur Belum Lunas</h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($customers) ?> customer</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover table-sm" id="tabelPembayaranCustomer">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Customer</th>
                                    <th class="text-center">Total Faktur</th>
                                    <th class="text-right">Total Tagihan</th>
                                    <th class="text-right">Total Pembayaran</th>
                                    <th class="text-right">Sisa Tagihan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($customers)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Tidak ada customer dengan faktur selesai DO yang belum lunas.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('keuangan/pembayaran/customer/' . rawurlencode($customer['kd_customer'])) ?>">
                                                    <strong><?= htmlspecialchars($customer['nama_customer']) ?></strong>
                                                </a>
                                                <br><small class="text-muted"><?= htmlspecialchars($customer['kd_customer']) ?></small>
                                            </td>
                                            <td class="text-center"><?= number_format((float)$customer['total_faktur'], 0, ',', '.') ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$customer['total_tagihan'], 0, ',', '.') ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$customer['total_pembayaran'], 0, ',', '.') ?></td>
                                            <td class="text-right font-weight-bold text-danger">Rp <?= number_format((float)$customer['sisa_tagihan'], 0, ',', '.') ?></td>
                                            <td class="text-center">
                                                <a href="<?= base_url('keuangan/pembayaran/customer/' . rawurlencode($customer['kd_customer'])) ?>"
                                                   class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye mr-1"></i>Detail
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
        $('#tabelPembayaranCustomer').DataTable();
    }
});
</script>
