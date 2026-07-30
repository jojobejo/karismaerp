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
                        <h1 class="m-0"><i class="fas fa-history mr-2"></i>Histori Pembayaran Supplier</h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('keuangan/pembayaran-supplier') ?>">Pembayaran Supplier</a></li>
                            <li class="breadcrumb-item active">Histori</li>
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

                <div class="mb-3">
                    <a href="<?= base_url('keuangan/pembayaran-supplier') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>

                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-search mr-1"></i>Cari Pembayaran</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" action="<?= base_url('keuangan/pembayaran-supplier/history') ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="q" class="form-control" placeholder="No pembayaran / supplier / jurnal" value="<?= htmlspecialchars($keyword ?? '') ?>">
                                </div>
                                <div class="col-md-4 mt-2 mt-md-0">
                                    <button class="btn btn-primary"><i class="fas fa-search mr-1"></i>Tampilkan</button>
                                    <a href="<?= base_url('keuangan/pembayaran-supplier/history') ?>" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Payment Supplier</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover table-sm" id="tabelHistorySupplierPayment">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No Pembayaran</th>
                                    <th>Tanggal</th>
                                    <th>Supplier</th>
                                    <th>No Jurnal</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Allocated</th>
                                    <th class="text-right">Unapplied</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($payments)): ?>
                                    <tr><td colspan="9" class="text-center text-muted py-4">Belum ada pembayaran supplier.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($payment['nomor_pembayaran']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($payment['keterangan'] ?? '-') ?></small>
                                            </td>
                                            <td class="text-center"><?= !empty($payment['tanggal_pembayaran']) ? date('d/m/Y', strtotime($payment['tanggal_pembayaran'])) : '-' ?></td>
                                            <td><?= htmlspecialchars($payment['nama_supplier'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($payment['nomor_jurnal'] ?? '-') ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$payment['amount'], 0, ',', '.') ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$payment['allocated_amount'], 0, ',', '.') ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$payment['unapplied_amount'], 0, ',', '.') ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-<?= $payment['status'] === 'POSTED' ? 'success' : ($payment['status'] === 'VOID' ? 'secondary' : 'warning') ?>">
                                                    <?= htmlspecialchars($payment['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($payment['status'] === 'POSTED'): ?>
                                                    <form method="post" action="<?= base_url('keuangan/pembayaran-supplier/void/' . (int)$payment['id_pembayaran']) ?>" class="d-inline void-payment-form">
                                                        <input type="hidden" name="reason" value="">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-undo mr-1"></i>Void
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    -
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
        $('#tabelHistorySupplierPayment').DataTable({ pageLength: 25, order: [[1, 'desc']] });
    }
    $('.void-payment-form').on('submit', function (e) {
        var reason = prompt('Alasan void pembayaran supplier:');
        if (!reason) {
            e.preventDefault();
            return false;
        }
        $(this).find('input[name="reason"]').val(reason);
    });
});
</script>
