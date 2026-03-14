<!-- view/content/logistik/ics/po_selesai.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <section class="content">

                <div class="row mb-2">
                    <div class="col-auto">
                        <a href="<?= base_url('data_lpb_zahir') ?>" class="btn btn-primary mb-3">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('riwayat_barang_masuk') ?>" class="btn btn-info mb-3">
                            <i class="fas fa-history"></i> Riwayat Barang Masuk
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-check-double mr-2"></i> PO Selesai - Barang Sudah Terpenuhi</h3>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">

                            <form action="<?= base_url('po_selesai') ?>" method="post">
                                <div class="row mb-3">
                                    <div class="col-2">
                                        <input type="date" class="form-control" name="date1" value="<?= $date1 ?? '' ?>">
                                    </div>
                                    <div class="col-2">
                                        <input type="date" class="form-control" name="date2" value="<?= $date2 ?? '' ?>">
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-success btn-block">
                                            <i class="fas fa-search"></i> Tampil
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <table class="table table-bordered" id="tabelPoSelesai">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No PO</th>
                                        <th>Tanggal</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th class="text-right">Qty Order</th>
                                        <th class="text-right">Qty Masuk</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $ada = false;
                                    if (!empty($lpb)) :
                                        foreach ($lpb as $row) :
                                            $qty_order = (int)($row['qty']       ?? 0);
                                            $qty_masuk = (int)($row['qty_masuk'] ?? 0);
                                            $sisa      = $qty_order - $qty_masuk;
                                            // Hanya tampilkan yang sudah selesai
                                            if ($sisa > 0) continue;
                                            $ada = true;
                                    ?>
                                        <tr class="table-success">
                                            <td><?= htmlspecialchars($row['no_po']         ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['tgl_transaksi'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['kd_barang']     ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['nama_barang']   ?? '-') ?></td>
                                            <td class="text-right"><?= number_format($qty_order) ?></td>
                                            <td class="text-right"><?= number_format($qty_masuk) ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Selesai
                                                </span>
                                            </td>
                                        </tr>
                                    <?php 
                                        endforeach;
                                    endif;
                                    if (!$ada) :
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fas fa-inbox mr-1"></i> Belum ada PO yang selesai
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

            </section>
        </div>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(document).ready(function () {
    $('#tabelPoSelesai').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ],
        language: {
            search:      "Cari:",
            lengthMenu:  "Tampilkan _MENU_ data",
            info:        "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable:  "Belum ada PO yang selesai",
            paginate: {
                first:    "Pertama",
                last:     "Terakhir",
                next:     "Berikutnya",
                previous: "Sebelumnya"
            }
        }
    });
});
</script>
</body>