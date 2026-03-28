<!-- icspo.php -->

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">

                    <?php if ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') != 'ADMINLOGLPB') : ?>
                        <div class="row">
                            <div class="col-auto">
                                <a href="<?= base_url('ics/ics_diffrent') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-arrow-left"></i></a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('ics/icsdo') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-minus-circle"></i> Data DO</a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('ics/icspo') ?>" class="btn btn-md btn-secondary w-100 mb-3"><i class="fas fa-plus-circle"></i> Data LPB</a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('ics/retur') ?>" class="btn btn-md btn-primary w-100 mb-3"><i class="fas fa-plus-circle"></i> Data Retur</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i> Data LPB (Laporan Penerimaan Barang)</h3>
                        </div>
                        <div class="card-body">
                            <div class="container-fluid">

                                <!-- Filter Tanggal -->
                                <form action="<?= base_url('ics/icspo') ?>" method="post">
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <input type="date" class="form-control" name="date1" value="<?= $date1 ?? '' ?>">
                                        </div>
                                        <div class="col-2">
                                            <input type="date" class="form-control" name="date2" value="<?= $date2 ?? '' ?>">
                                        </div>
                                        <div class="col-2">
                                            <button class="btn btn-primary btn-block">
                                                <i class="fas fa-search"></i> Tampil
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Tombol Aksi Atas (sesuai role) -->
                                <?php if ($this->session->userdata('lv') == '1' && $this->session->userdata('jobdesk') != 'ADMINICS') : ?>
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <a class="btn btn-success btn-block" href="<?= base_url('data_lpb_zahir') ?>">
                                                <i class="fas fa-file-csv"></i> Data LPB
                                            </a>
                                        </div>
                                        <div class="col-2">
                                            <a class="btn btn-success btn-block" href="<?= base_url('ics/retur') ?>">
                                                <i class="fas fa-undo"></i> Data Retur
                                            </a>
                                        </div>
                                    </div>
                                <?php elseif ($this->session->userdata('lv') == '2') : ?>
                                    <div class="row mb-3">
                                        <div class="col-2">
                                            <button class="btn btn-success btn-block" data-toggle="modal" data-target="#modalImportCSV">
                                                <i class="fas fa-file-csv"></i> Import CSV
                                            </button>
                                        </div>
                                        <div class="col-2">
                                            <a class="btn btn-success btn-block" href="<?= base_url('ics/retur') ?>">
                                                <i class="fas fa-undo"></i> Retur
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Modal Import CSV -->
                                    <div class="modal fade" id="modalImportCSV" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <form action="<?= base_url('ics/import_csv') ?>" method="post" enctype="multipart/form-data">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success">
                                                        <h5 class="modal-title">Import Data PO dari CSV</h5>
                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Pilih File CSV</label>
                                                            <input type="file" name="file_csv" class="form-control" required accept=".csv">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success">Import</button>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Tabel Data LPB -->
                                <table class="table table-bordered table-hover" id="tb_ics_po">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th>No PO</th>
                                            <th>Tgl Transaksi</th>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th class="text-right">Qty Order</th>
                                            <th class="text-right">Qty Masuk</th>
                                            <th class="text-right">Sisa</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($lpb)) : ?>
                                            <?php foreach ($lpb as $row) :
                                                $qty_order = (int)($row['qty']       ?? 0);
                                                $qty_masuk = (int)($row['qty_masuk'] ?? 0);
                                                $sisa      = $qty_order - $qty_masuk;

                                                // Tentukan warna baris & badge status
                                                if ($sisa <= 0) {
                                                    $row_class  = 'table-success';
                                                    $badge      = '<span class="badge badge-success"><i class="fas fa-check"></i> Selesai</span>';
                                                } elseif ($qty_masuk > 0) {
                                                    $row_class  = 'table-warning';
                                                    $badge      = '<span class="badge badge-warning"><i class="fas fa-clock"></i> Sebagian</span>';
                                                } else {
                                                    $row_class  = '';
                                                    $badge      = '<span class="badge badge-danger"><i class="fas fa-times"></i> Belum Datang</span>';
                                                }
                                            ?>
                                                <tr class="<?= $row_class ?>">
                                                    <td><?= htmlspecialchars($row['no_po']         ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['tgl_transaksi'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['kd_barang']     ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['nama_barang']   ?? '-') ?></td>
                                                    <td class="text-right"><?= number_format($qty_order) ?></td>
                                                    <td class="text-right"><?= number_format($qty_masuk) ?></td>
                                                    <td class="text-right"><?= number_format($sisa) ?></td>
                                                    <td class="text-center"><?= $badge ?></td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('ics/detail_po?no_po=' . urlencode($row['no_po'])) ?>" 
                                                            class="btn btn-info btn-sm">
                                                            <i class="fas fa-eye"></i> Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">
                                                    <i class="fas fa-inbox mr-1"></i> Tidak ada data
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
            $('#tb_ics_po').DataTable({
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
                    emptyTable:  "Belum ada data LPB",
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