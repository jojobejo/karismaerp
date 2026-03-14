<!-- content/logistik/ics/detail_po.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <section class="content">

                <div class="row mb-2">
                    <div class="col-auto">
                        <a href="<?= base_url('ics/icspo') ?>" class="btn btn-primary mb-3">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">
                            <i class="fas fa-eye mr-2"></i> Detail Penerimaan — No PO: <?= htmlspecialchars($no_po) ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="tabelDetailPo">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th class="text-right">Qty Diterima</th>
                                    <th>No Lot</th>
                                    <th>Expired Date</th>
                                    <th>Tgl Input</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($detail)) : ?>
                                    <?php foreach ($detail as $i => $row) : ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= htmlspecialchars($row['kd_barang']    ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['nama_barang']  ?? '-') ?></td>
                                            <td class="text-right"><?= number_format((int)($row['qty_diterima'] ?? 0)) ?></td>
                                            <td><?= htmlspecialchars($row['no_lot']       ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['exp_date']     ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['create_at']    ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-inbox mr-1"></i> Belum ada barang diterima untuk PO ini
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>
        </div>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(document).ready(function () {
    $('#tabelDetailPo').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        language: {
            search:      "Cari:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data",
            emptyTable:  "Belum ada data penerimaan",
            paginate: {
                first: "Pertama", last: "Terakhir",
                next: "Berikutnya", previous: "Sebelumnya"
            }
        }
    });
});
</script>
</body>