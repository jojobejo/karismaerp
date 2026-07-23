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
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-terminal mr-2"></i> Log Sistem LPB Manual
                            </h3>
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Dashboard
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="lpbManualLogTable">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                            <th>Ref Manual</th>
                                            <th>ID LPB</th>
                                            <th>Pesan</th>
                                            <th>User</th>
                                            <th>IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (($rows ?? []) as $row) : ?>
                                            <?php $status = strtoupper(trim((string) ($row['status'] ?? 'INFO'))); ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['created_at'] ?? '-') ?></td>
                                                <td class="text-center">
                                                    <span class="badge badge-<?= $status === 'SUCCESS' ? 'success' : ($status === 'FAILED' ? 'danger' : 'secondary') ?>">
                                                        <?= htmlspecialchars($status) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($row['action_type'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['manual_ref_no'] ?? '-') ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row['id_lpb'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['message'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['created_by'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['ip_address'] ?? '-') ?></td>
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
            $('#lpbManualLogTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [[0, 'desc']],
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    zeroRecords: 'Tidak ada data ditemukan',
                    emptyTable: 'Belum ada log LPB Manual',
                    paginate: { first: 'Pertama', last: 'Terakhir', next: 'Berikutnya', previous: 'Sebelumnya' }
                }
            });
        });
    </script>
</body>
