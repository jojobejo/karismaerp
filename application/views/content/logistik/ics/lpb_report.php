<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">
                    <div class="card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-chart-bar mr-2"></i> Laporan LPB
                            </h3>
                            <div>
                                <a href="<?= base_url('ics/lpb_manual') ?>" class="btn btn-light btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Input LPB Manual
                                </a>
                                <a href="<?= base_url('ics/icspo') ?>" class="btn btn-light btn-sm">
                                    <i class="fas fa-arrow-left mr-1"></i> Data LPB
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="get" action="<?= base_url('ics/lpb_report') ?>" class="mb-3">
                                <div class="row">
                                    <div class="col-md-3 col-sm-6 mb-2">
                                        <select class="form-control" name="source">
                                            <?php $source = $filters['source'] ?? 'all'; ?>
                                            <option value="all" <?= $source === 'all' ? 'selected' : '' ?>>Semua Sumber</option>
                                            <option value="manual" <?= $source === 'manual' ? 'selected' : '' ?>>LPB Manual Purchasing</option>
                                            <option value="logistik" <?= $source === 'logistik' ? 'selected' : '' ?>>LPB Logistik dari PO</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <input type="date" class="form-control" name="date1" value="<?= htmlspecialchars($filters['date1'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <input type="date" class="form-control" name="date2" value="<?= htmlspecialchars($filters['date2'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-2">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-filter mr-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="lpbReportTable">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th>Tgl LPB</th>
                                            <th>Sumber</th>
                                            <th>No LPB</th>
                                            <th>Ref</th>
                                            <th>No PO</th>
                                            <th>Gudang</th>
                                            <th class="text-center">Item</th>
                                            <th class="text-right">Qty</th>
                                            <th class="text-right">Nilai</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (($rows ?? []) as $row) : ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['tgl_lpb'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['source_label'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['nomor_lpb'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['manual_ref_no'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['no_po'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['nama_gudang'] ?? '-') ?></td>
                                                <td class="text-center"><?= (int) ($row['total_item'] ?? 0) ?></td>
                                                <td class="text-right"><?= number_format((float) ($row['total_qty'] ?? 0), 2, ',', '.') ?></td>
                                                <td class="text-right"><?= 'Rp ' . number_format((float) ($row['total_harga'] ?? 0), 0, ',', '.') ?></td>
                                                <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
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
        $(function() {
            $('#lpbReportTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [[0, 'desc']],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    zeroRecords: 'Tidak ada data ditemukan',
                    emptyTable: 'Belum ada data LPB',
                    paginate: { first: 'Pertama', last: 'Terakhir', next: 'Berikutnya', previous: 'Sebelumnya' }
                }
            });
        });
    </script>
</body>
