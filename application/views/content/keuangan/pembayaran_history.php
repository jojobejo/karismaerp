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
                        <h1 class="m-0"><i class="fas fa-history mr-2"></i>Histori Pembayaran Faktur</h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('keuangan/pembayaran') ?>">Pembayaran</a></li>
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
                    <a href="<?= base_url('keuangan/pembayaran') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>

                <div class="card card-outline card-success">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Histori Pembayaran Terbaru (250 Data Terakhir)</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-sm" id="tabelHistoriPembayaran">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Tanggal Input</th>
                                        <th>No. Faktur</th>
                                        <th>Customer</th>
                                        <th>Metode</th>
                                        <th>Status Cair</th>
                                        <th class="text-right">Jumlah Pembayaran</th>
                                        <th class="text-right">Diskon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_payments)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Belum ada histori pembayaran.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_payments as $row):
                                            $status_bg_val = $row['status_bg'] ?? 'not_bg';
                                        ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($row['create_at'] ?? $row['tanggal_pembayaran'])) ?></td>
                                                <td>
                                                    <a href="<?= base_url('keuangan/pembayaran/bayar/' . $row['id_faktur']) ?>" class="font-weight-bold">
                                                        <?= htmlspecialchars($row['no_faktur']) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="<?= base_url('keuangan/pembayaran/customer/' . rawurlencode($row['kd_customer'])) ?>">
                                                        <?= htmlspecialchars($row['nama_customer']) ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($row['metode_pembayaran'] ?: '-') ?></td>
                                                <td>
                                                    <?php if ($status_bg_val === 'pending'): ?>
                                                        <span class="badge badge-warning">Belum Cair (Pending)</span>
                                                    <?php elseif ($status_bg_val === 'cair'): ?>
                                                        <span class="badge badge-success">Sudah Cair</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Langsung Masuk</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-right font-weight-bold">Rp <?= number_format((float)$row['jumlah_pembayaran'], 0, ',', '.') ?></td>
                                                <td class="text-right text-success"><?= (float)$row['jumlah_diskon'] > 0 ? 'Rp ' . number_format((float)$row['jumlah_diskon'], 0, ',', '.') : '-' ?></td>
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
    </footer>
</div>

<script>
$(function () {
    if ($.fn.DataTable) {
        $('#tabelHistoriPembayaran').DataTable({
            "order": [[0, "desc"]]
        });
    }
});
</script>
