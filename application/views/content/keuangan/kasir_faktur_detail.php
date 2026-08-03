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
                        <h1 class="m-0"><i class="fas fa-file-invoice-dollar mr-2"></i><?= htmlspecialchars($customer_name) ?></h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('keuangan/pembayaran/kasir') ?>">Pembayaran</a></li>
                            <li class="breadcrumb-item active">Detail Customer</li>
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
                    <a href="<?= base_url('keuangan/pembayaran/kasir') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Faktur Selesai DO Belum Lunas</h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($fakturs) ?> faktur</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover table-sm" id="tabelPembayaranFaktur">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No Faktur</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Tanggal Tempo</th>
                                    <th>Customer</th>
                                    <th class="text-right">Total Piutang</th>
                                    <th class="text-right">Total Pembayaran</th>
                                    <th class="text-right">BG Belum Cair</th>
                                    <th class="text-right">Sisa Piutang</th>
                                    <th class="text-center">Status Bayar</th>
                                    <th class="text-center">Overdue</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($fakturs)): ?>
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">Tidak ada faktur belum lunas untuk customer ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($fakturs as $faktur):
                                        $status_bayar = strtolower($faktur['status_pembayaran']);
                                        $status_class = $status_bayar === 'lunas' ? 'success' : ($status_bayar === 'sebagian' ? 'warning' : 'danger');
                                        $status_label = [
                                            'lunas'       => 'Lunas',
                                            'sebagian'    => 'Sebagian',
                                            'belum_lunas' => 'Belum Lunas',
                                        ][$status_bayar] ?? $faktur['status_pembayaran'];
                                        $overdue = $faktur['status_overdue'];
                                        $overdue_class = $overdue === 'Belum overdue' ? 'secondary' : ($overdue === 'Overdue 30' ? 'warning' : 'danger');
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($faktur['no_faktur']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($faktur['no_so'] ?? '-') ?></small>
                                            </td>
                                            <td class="text-nowrap">
                                                <?= !empty($faktur['tanggal_faktur']) ? date('d/m/Y', strtotime($faktur['tanggal_faktur'])) : '-' ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?= !empty($faktur['tanggal_jatuh_tempo']) ? date('d/m/Y', strtotime($faktur['tanggal_jatuh_tempo'])) : '-' ?>
                                            </td>
                                            <td><?= htmlspecialchars($faktur['nama_customer']) ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$faktur['total_tagihan'], 0, ',', '.') ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$faktur['total_pembayaran'], 0, ',', '.') ?></td>
                                            <td class="text-right text-warning">Rp <?= number_format((float)($faktur['total_bg_pending'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-right font-weight-bold text-danger">Rp <?= number_format((float)$faktur['sisa_tagihan'], 0, ',', '.') ?></td>
                                            <td class="text-center"><span class="badge badge-<?= $status_class ?>"><?= htmlspecialchars($status_label) ?></span></td>
                                            <td class="text-center"><span class="badge badge-<?= $overdue_class ?>"><?= htmlspecialchars($overdue) ?></span></td>
                                            <td class="text-center">
                                                <a href="<?= base_url('keuangan/pembayaran/kasir_bayar/' . $faktur['id_faktur']) ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-money-bill-wave mr-1"></i>Bayar
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
        $('#tabelPembayaranFaktur').DataTable();
    }
});
</script>
