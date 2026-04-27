<!-- icspo.php -->
<style>
    .po-progress-wrap {
        min-width: 150px;
        max-width: 170px;
        margin: 0 auto;
    }

    .po-progress-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .po-progress-track {
        width: 100%;
        height: 9px;
        border-radius: 999px;
        overflow: hidden;
        background: #e9ecef;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.08);
    }

    .po-progress-fill {
        height: 100%;
        border-radius: 999px;
        transition: width .3s ease;
    }

    .po-progress-fill.is-danger {
        background: linear-gradient(90deg, #dc3545 0%, #ff6b6b 100%);
    }

    .po-progress-fill.is-warning {
        background: linear-gradient(90deg, #f39c12 0%, #f6c23e 100%);
    }

    .po-progress-fill.is-success {
        background: linear-gradient(90deg, #28a745 0%, #5ad67d 100%);
    }

    .po-progress-note {
        margin-top: 4px;
        font-size: 10px;
        color: #6c757d;
    }
</style>

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
                                <a href="<?= base_url('ics/ics_diffrent') ?>" class="btn btn-md btn-primary w-100 mb-3">
                                    <i class="fas fa-arrow-left"></i>
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('ics/icsdo') ?>" class="btn btn-md btn-primary w-100 mb-3">
                                    <i class="fas fa-minus-circle"></i> Data DO
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('ics/icspo') ?>" class="btn btn-md btn-secondary w-100 mb-3">
                                    <i class="fas fa-plus-circle"></i> Data LPB
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('ics/retur') ?>" class="btn btn-md btn-primary w-100 mb-3">
                                    <i class="fas fa-plus-circle"></i> Data Retur
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title">
                                <i class="fas fa-plus-circle mr-2"></i> Data LPB (Laporan Penerimaan Barang)
                            </h3>
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
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
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
                                <table class="table table-bordered table-hover" id="idtb_ics_po">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th>No PO</th>
                                            <th>Tgl Transaksi</th>
                                            <th>Kode Supplier</th>
                                            <th>Nama Supplier</th>
                                            <th class="text-center">Total Barang Order</th>
                                            <th class="text-center">Total Barang Diterima</th>
                                            <th class="text-center">Progress</th>
                                            <th class="text-center">Input Terakhir</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center" style="width:90px;">#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($lpb)) : ?>
                                            <?php foreach ($lpb as $row) :
                                                $jumlah_barang_order    = (int)($row['total_barang_order'] ?? 0);
                                                $jumlah_barang_diterima = (int)($row['total_barang_diterima'] ?? 0);
                                                $jumlah_qty_order       = (float)($row['total_qty_order'] ?? 0);
                                                $jumlah_qty_diterima    = (float)($row['total_qty_diterima'] ?? 0);
                                                $status                 = strtolower(trim((string)($row['status'] ?? '')));
                                                $progress_raw           = (float)($row['progress_persen'] ?? 0);
                                                $progress               = max(0, min(100, $progress_raw));
                                                $progress_text          = rtrim(rtrim(number_format($progress, 2, '.', ''), '0'), '.');

                                                if (floor($jumlah_qty_order) == $jumlah_qty_order) {
                                                    $jumlah_qty_order = (int) $jumlah_qty_order;
                                                }

                                                if (floor($jumlah_qty_diterima) == $jumlah_qty_diterima) {
                                                    $jumlah_qty_diterima = (int) $jumlah_qty_diterima;
                                                }

                                                // Warna baris & badge status
                                                if ($status === 'done') {
                                                    $row_class = 'table-success';
                                                    $progress_class = 'is-success';
                                                    $badge     = '<span class="badge badge-success px-2 py-1">
                                                                    <i class="fas fa-check mr-1"></i> Done
                                                                  </span>';
                                                } elseif ($status === 'partial') {
                                                    $row_class = 'table-warning';
                                                    $progress_class = 'is-warning';
                                                    $badge     = '<span class="badge badge-warning px-2 py-1">
                                                                    <i class="fas fa-clock mr-1"></i> Partial
                                                                  </span>';
                                                } else {
                                                    $row_class = '';
                                                    $progress_class = 'is-danger';
                                                    $badge     = '<span class="badge badge-danger px-2 py-1">
                                                                    <i class="fas fa-times mr-1"></i> Belum
                                                                  </span>';
                                                } ?>
                                                <tr class="<?= $row_class ?>">
                                                    <td><?= htmlspecialchars($row['no_po']         ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['tgl_transaksi'] ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['kdsupp']        ?? '') ?></td>
                                                    <td><?= htmlspecialchars($row['nm_suplier']    ?? '-') ?></td>
                                                    <td class="text-center font-weight-bold">
                                                        <?= $jumlah_barang_order ?>
                                                    </td>
                                                    <td class="text-center font-weight-bold <?= $jumlah_barang_diterima > 0 ? 'text-success' : 'text-danger' ?>">
                                                        <?= $jumlah_barang_diterima ?>
                                                    </td>
                                                    <td>
                                                        <div class="po-progress-wrap">
                                                            <div class="po-progress-label">
                                                                <span><?= $progress_text ?>%</span>
                                                                <span><?= $jumlah_qty_diterima ?> / <?= $jumlah_qty_order ?></span>
                                                            </div>
                                                            <div class="po-progress-track">
                                                                <div class="po-progress-fill <?= $progress_class ?>" style="width: <?= $progress ?>%;"></div>
                                                            </div>
                                                            <div class="po-progress-note">
                                                                Berdasarkan qty diterima
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center"><?= htmlspecialchars($row['input_terakhir'] ?? '-') ?></td>
                                                    <td class="text-center"><?= $badge ?></td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('ics/detail_po?no_po=' . urlencode($row['no_po']) . '&kd_suplier=' . urlencode($row['kdsupp'] ?? '')) ?>" class="btn btn-info btn-sm" target="_blank">
                                                            <i class="fas fa-eye"></i> Detail
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="10" class="text-center text-muted">
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
        $(document).ready(function() {
            $('#idtb_ics_po').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: -1
                }],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    zeroRecords: "Tidak ada data ditemukan",
                    emptyTable: "Belum ada data LPB",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
        });
    </script>
</body>
