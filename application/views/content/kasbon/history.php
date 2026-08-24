<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <style>
        /* CSS untuk mempercantik dan memberi spacing yang rapi pada DataTables */
        .dataTables_wrapper {
            padding: 5px 0;
        }
        .dataTables_wrapper .row:first-child {
            margin-bottom: 15px;
            align-items: center;
        }
        .dataTables_wrapper .row:last-child {
            margin-top: 15px;
            align-items: center;
        }
        .dataTables_length {
            margin-bottom: 0;
        }
        .dataTables_length label {
            display: inline-flex;
            align-items: center;
            margin-bottom: 0;
            font-weight: 500;
            color: #495057;
            font-size: 14px;
        }
        .dataTables_length select {
            width: auto !important;
            display: inline-block !important;
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            margin: 0 6px;
            cursor: pointer;
        }
        .dataTables_filter {
            text-align: right;
            margin-bottom: 0;
        }
        .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            margin-bottom: 0;
            font-weight: 500;
            color: #495057;
            font-size: 14px;
        }
        .dataTables_filter input {
            width: auto !important;
            display: inline-block !important;
            margin-left: 8px !important;
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 4px 10px;
        }
        .dataTables_info {
            font-weight: 500;
            color: #6c757d;
            font-size: 13px;
        }
        .dataTables_paginate {
            text-align: right;
        }
        .dataTables_paginate .pagination {
            margin-bottom: 0;
            justify-content: flex-end;
        }
    </style>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-history mr-2 text-info"></i> Riwayat Approval Kas Bon</h1>
                        <small class="text-muted">Daftar pengajuan kas bon yang telah disetujui / diproses sebelumnya</small>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('C_Kasbon') ?>">Kas Bon</a></li>
                            <li class="breadcrumb-item active">Riwayat Approval</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                
                <div class="row mb-3">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <a href="<?= base_url('C_Kasbon') ?>" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Kas Bon
                        </a>
                        <a href="<?= base_url('C_Kasbon/create') ?>" class="btn btn-primary shadow-sm">
                            <i class="fas fa-plus mr-1"></i> Buat Pengajuan Kas Bon
                        </a>
                    </div>
                </div>

                <!-- TABEL RIWAYAT APPROVAL KAS BON -->
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header bg-info text-white clearfix">
                        <h3 class="card-title font-weight-bold m-0 float-left"><i class="fas fa-clipboard-check mr-2"></i> Pengajuan Kas Bon Yang Telah Diproses / Disetujui</h3>
                        <div class="card-tools float-right m-0">
                            <span class="badge badge-light px-3 py-1 font-weight-bold" style="font-size:12px;"><?= count($approval_history) ?> Riwayat</span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped align-middle mb-0" id="table-history-kasbon" style="font-size: 13.5px; width: 100%;">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center" style="width: 35px;">No</th>
                                        <th>No Kas Bon</th>
                                        <th>Tgl Pengajuan</th>
                                        <th>Pemohon</th>
                                        <th>Keterangan</th>
                                        <th class="text-right">Nominal</th>
                                        <th class="text-center">Lampiran</th>
                                        <th>Jejak Approval</th>
                                        <th class="text-center">Status Akhir</th>
                                        <th class="text-center" style="width: 80px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($approval_history as $row) : ?>
                                    <tr>
                                        <td class="text-center align-middle"><?= $no++ ?></td>
                                        <td class="align-middle">
                                            <a href="<?= base_url('C_Kasbon/detail/' . $row['id']) ?>" class="font-weight-bold text-primary">
                                                <?= htmlspecialchars($row['no_kasbon']) ?>
                                            </a>
                                        </td>
                                        <td class="align-middle"><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                                        <td class="align-middle font-weight-bold"><?= htmlspecialchars($row['nama_pemohon']) ?></td>
                                        <td class="align-middle"><?= htmlspecialchars($row['keterangan']) ?></td>
                                        <td class="text-right align-middle font-weight-bold text-success" style="font-size:14px;">
                                            Rp <?= number_format($row['nominal'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php if (!empty($row['lampiran'])): ?>
                                                <a href="<?= base_url('assets/uploads/kasbon/' . htmlspecialchars($row['lampiran'])) ?>" target="_blank" class="btn btn-xs btn-info" title="Lihat Lampiran">
                                                    <i class="fas fa-paperclip"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle" style="min-width: 180px;">
                                            <div class="small">
                                                <?php if (!empty($row['approved_atasan_by'])): ?>
                                                    <div class="text-success mb-1">
                                                        <i class="fas fa-check-circle mr-1"></i> <strong>Atasan:</strong> <?= htmlspecialchars($row['approved_atasan_by']) ?>
                                                        <span class="text-muted d-block" style="font-size:11px;"><?= !empty($row['approved_atasan_at']) ? date('d/m/Y H:i', strtotime($row['approved_atasan_at'])) : '' ?></span>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($row['approved_penilai_by'])): ?>
                                                    <div class="text-info mb-1">
                                                        <i class="fas fa-check-double mr-1"></i> <strong>Penilai:</strong> <?= htmlspecialchars($row['approved_penilai_by']) ?>
                                                        <span class="text-muted d-block" style="font-size:11px;"><?= !empty($row['approved_penilai_at']) ? date('d/m/Y H:i', strtotime($row['approved_penilai_at'])) : '' ?></span>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($row['cair_by'])): ?>
                                                    <div class="text-primary mb-1">
                                                        <i class="fas fa-hand-holding-usd mr-1"></i> <strong>Kasir:</strong> <?= htmlspecialchars($row['cair_by']) ?>
                                                        <span class="text-muted d-block" style="font-size:11px;"><?= !empty($row['cair_at']) ? date('d/m/Y H:i', strtotime($row['cair_at'])) : '' ?></span>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($row['rejected_by'])): ?>
                                                    <div class="text-danger mb-1">
                                                        <i class="fas fa-times-circle mr-1"></i> <strong>Ditolak:</strong> <?= htmlspecialchars($row['rejected_by']) ?>
                                                        <span class="text-muted d-block" style="font-size:11px;">Alasan: <?= htmlspecialchars($row['rejected_reason'] ?: '-') ?></span>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (empty($row['approved_atasan_by']) && empty($row['approved_penilai_by']) && empty($row['rejected_by']) && empty($row['cair_by'])): ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php 
                                                if ($row['status'] === 'pending_atasan') {
                                                    $label = $this->M_Kasbon->get_approver_label($row['approver_1'] ?? '');
                                                    echo '<span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i>Menunggu ' . htmlspecialchars($label) . '</span>';
                                                } elseif ($row['status'] === 'pending_penilai') {
                                                    $label = $this->M_Kasbon->get_approver_label($row['approver_2'] ?? '');
                                                    echo '<span class="badge badge-warning px-2 py-1"><i class="fas fa-clock mr-1"></i>Menunggu ' . htmlspecialchars($label) . '</span>';
                                                } elseif ($row['status'] === 'approved') {
                                                    echo '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Disetujui (Siap Cair)</span>';
                                                } elseif ($row['status'] === 'cair') {
                                                    echo '<span class="badge badge-info px-2 py-1"><i class="fas fa-hand-holding-usd mr-1"></i>Sudah Dicairkan</span>';
                                                } elseif ($row['status'] === 'rejected') {
                                                    echo '<span class="badge badge-danger px-2 py-1"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>';
                                                } else {
                                                    echo '<span class="badge badge-secondary px-2 py-1">'.htmlspecialchars($row['status']).'</span>';
                                                }
                                            ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <a href="<?= base_url('C_Kasbon/detail/' . $row['id']) ?>" class="btn btn-xs btn-outline-secondary" title="Lihat Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

</div> <!-- ./wrapper -->

<script>
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-history-kasbon').DataTable({
                "ordering": false,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                "language": {
                    "emptyTable": "Belum ada riwayat approval kas bon.",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "infoFiltered": "(disaring dari _MAX_ total data)",
                    "search": "Cari Riwayat:",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                }
            });
        }
    });
</script>
