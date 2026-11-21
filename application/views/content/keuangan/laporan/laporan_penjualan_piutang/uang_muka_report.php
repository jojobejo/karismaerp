<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
<?php $this->load->view('partial/main/navbar'); ?>
<?php $this->load->view('partial/main/sidebar'); ?>
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center">
                    <h1 class="h5 mb-0 mr-auto">Uang Muka</h1>
                    <a href="<?= base_url('laporan/penjualan') ?>" class="btn btn-sm btn-secondary mr-2">Kembali</a>
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#advanceFilter">Filter Data</button>
                </div>
                <div class="card-body">
                    <?php if ($date_error): ?>
                        <div class="alert alert-warning mb-0" role="alert">Masukkan periode yang valid. Tanggal akhir harus sama dengan atau setelah tanggal awal.</div>
                    <?php elseif (!$show_report): ?>
                        <p class="text-muted mb-0">Pilih filter untuk menampilkan laporan.</p>
                    <?php elseif (!$rows): ?>
                        <p class="text-muted mb-0">Tidak ada transaksi uang muka yang sesuai dengan filter.</p>
                    <?php else: ?>
                        <p>Periode <?= date('d/m/Y', strtotime($selected['start_date'])) ?> hingga <?= date('d/m/Y', strtotime($selected['end_date'])) ?></p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead><tr><th>Tanggal</th><th>Referensi</th><th>Pelanggan / Supplier</th><th>Jenis</th><th>Status</th><th class="text-right">Jumlah (Rp)</th></tr></thead>
                                <tbody>
                                    <?php foreach ($rows as $row): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                            <td><?= html_escape($row['no_referensi']) ?></td>
                                            <td><?= html_escape($row['customer']) ?></td>
                                            <td><?= html_escape($row['type']) ?></td>
                                            <td><?= html_escape($row['status']) ?></td>
                                            <td class="text-right"><?= number_format((float) $row['amount'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="advanceFilter" tabindex="-1" role="dialog" aria-labelledby="advanceFilterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form class="modal-content" id="advanceFilterForm" action="<?= base_url('laporan/penjualan/uang-muka') ?>" method="get">
            <div class="modal-header">
                <h2 class="h6 modal-title" id="advanceFilterTitle">Filter Data - Uang Muka</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
            </div>
            <div class="modal-body">
                <?php $filter_type = 'advance'; include __DIR__ . '/_filter_fields.php'; ?>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-sm btn-primary">OK</button>
            </div>
        </form>
    </div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('advanceFilterForm');
    var start = form.elements.start_date;
    var end = form.elements.end_date;

    $('#advanceFilter').on('hidden.bs.modal', function() {
        form.reset();
        end.setCustomValidity('');
    });
    $(start).add(end).on('change', function() {
        end.setCustomValidity(start.value && end.value && start.value > end.value ? 'Tanggal akhir harus sama dengan atau setelah tanggal awal.' : '');
    });

    <?php if (!$show_report || $date_error): ?>
    $('#advanceFilter').modal('show');
    <?php endif; ?>
});
</script>
