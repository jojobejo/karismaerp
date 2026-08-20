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
                        <h1 class="m-0"><i class="fas fa-truck mr-2"></i><?= htmlspecialchars($supplier['nama_suplier'] ?? 'Supplier') ?></h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('keuangan/pembayaran-supplier') ?>">Pembayaran Supplier</a></li>
                            <li class="breadcrumb-item active">Detail Supplier</li>
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
                    <a href="<?= base_url('keuangan/pembayaran-supplier/form/' . (int)$supplier['id_suplier']) ?>" class="btn btn-success btn-sm">
                        <i class="fas fa-money-check-alt mr-1"></i>Bayar Semua Terbuka
                    </a>
                    <?php if (!empty($return_credits)): ?>
                        <a href="<?= base_url('keuangan/pembayaran-supplier/potong-retur/' . (int)$supplier['id_suplier']) ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-cut mr-1"></i>Potong Hutang Retur
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($return_credits)): ?>
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-undo-alt mr-2"></i>Dokumen Retur Siap Potong</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No Retur</th>
                                            <th>Tanggal</th>
                                            <th>Keterangan</th>
                                            <th class="text-right">Nilai Retur</th>
                                            <th class="text-right">Sudah Dipotong</th>
                                            <th class="text-right">Sisa Retur</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($return_credits as $credit): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($credit['nomor_dokumen']) ?></strong></td>
                                                <td><?= !empty($credit['tanggal_retur']) ? date('d/m/Y', strtotime($credit['tanggal_retur'])) : '-' ?></td>
                                                <td><?= htmlspecialchars($credit['keterangan'] ?? '-') ?></td>
                                                <td class="text-right">Rp <?= number_format((float)$credit['total_retur'], 0, ',', '.') ?></td>
                                                <td class="text-right">Rp <?= number_format((float)$credit['total_dipotong'], 0, ',', '.') ?></td>
                                                <td class="text-right font-weight-bold text-warning">Rp <?= number_format((float)$credit['available_amount'], 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="get" action="<?= base_url('keuangan/pembayaran-supplier/form/' . (int)$supplier['id_suplier']) ?>" id="formPilihDokumen">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title"><i class="fas fa-file-invoice mr-2"></i>Dokumen Hutang Terbuka</h3>
                            <div class="card-tools">
                                <button type="submit" class="btn btn-light btn-sm text-success">
                                    <i class="fas fa-check mr-1"></i>Bayar Pilihan
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-hover table-sm" id="tabelDokumenSupplier">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center" style="width: 42px;"><input type="checkbox" id="checkAllDocs"></th>
                                        <th>Dokumen</th>
                                        <th>PO / Invoice</th>
                                        <th class="text-right">Total Hutang</th>
                                        <th class="text-right">Retur/Payment</th>
                                        <th class="text-right">Sisa Hutang</th>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($documents)): ?>
                                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada dokumen hutang terbuka untuk supplier ini.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($documents as $doc):
                                            $meta = $doc['lpb_meta'] ?? [];
                                        ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="dokumen[]" value="<?= htmlspecialchars($doc['nomor_dokumen']) ?>" class="doc-check">
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($doc['nomor_dokumen']) ?></strong>
                                                    <br><small class="text-muted">LPB ID: <?= htmlspecialchars($meta['id_lpb_list'] ?? '-') ?></small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($meta['no_po_list'] ?? '-') ?>
                                                    <br><small class="text-muted">Invoice: <?= htmlspecialchars($meta['no_invoice_list'] ?? '-') ?></small>
                                                </td>
                                                <td class="text-right">Rp <?= number_format((float)$doc['total_hutang'], 0, ',', '.') ?></td>
                                                <td class="text-right">Rp <?= number_format((float)$doc['total_pengurang'], 0, ',', '.') ?></td>
                                                <td class="text-right font-weight-bold text-danger">Rp <?= number_format((float)$doc['outstanding'], 0, ',', '.') ?></td>
                                                <td class="text-center"><?= !empty($doc['tanggal_tertua']) ? date('d/m/Y', strtotime($doc['tanggal_tertua'])) : '-' ?></td>
                                                <td class="text-center"><span class="badge badge-info"><?= htmlspecialchars($doc['status_label']) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
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
        $('#tabelDokumenSupplier').DataTable({ pageLength: 25, order: [[6, 'asc']] });
    }
    $('#checkAllDocs').on('change', function () {
        $('.doc-check').prop('checked', this.checked);
    });
    $('#formPilihDokumen').on('submit', function (e) {
        if ($('.doc-check:checked').length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu dokumen.');
        }
    });
});
</script>
