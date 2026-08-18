<?php
// Tentukan warna dan label status
$status_map = [
    'pending_atasan'          => ['label' => 'Menunggu Approval Atasan',          'color' => 'warning', 'icon' => 'clock'],
    'pending_penilai'         => ['label' => 'Menunggu Approval Penilai',          'color' => 'warning', 'icon' => 'clock'],
    'pending_penilai_tambahan'=> ['label' => 'Menunggu Approval Penilai Tambahan', 'color' => 'warning', 'icon' => 'clock'],
    'approved'                => ['label' => 'Disetujui – Siap Dicairkan',          'color' => 'success', 'icon' => 'check-circle'],
    'cair'                    => ['label' => 'Sudah Dicairkan',                     'color' => 'info',    'icon' => 'hand-holding-usd'],
    'rejected'                => ['label' => 'Ditolak',                             'color' => 'danger',  'icon' => 'times-circle'],
];
$st = $status_map[$kasbon['status']] ?? ['label' => ucfirst($kasbon['status']), 'color' => 'secondary', 'icon' => 'question-circle'];
?>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <style>
        .timeline > div > .timeline-item {
            border-radius: 6px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
            border: 1px solid #e9ecef !important;
        }
        .timeline > div > .timeline-item > .timeline-header {
            font-size: 14px;
            font-weight: 600;
            padding: 10px 12px;
        }
        .timeline > div > .timeline-item > .timeline-body {
            padding: 10px 12px;
            font-size: 13px;
        }
    </style>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-file-invoice-dollar mr-2 text-success"></i> Detail Kas Bon</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('C_Kasbon') ?>">Kas Bon</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show shadow-sm">
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="row">
                    <!-- Kolom Kiri: Info Kasbon -->
                    <div class="col-md-7">
                        <div class="card card-outline card-success shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h3 class="card-title font-weight-bold text-dark m-0">
                                    <i class="fas fa-file-alt mr-2 text-success"></i>
                                    Informasi Pengajuan
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-<?= $st['color'] ?> px-3 py-2" style="font-size:13px; border-radius: 20px;">
                                        <i class="fas fa-<?= $st['icon'] ?> mr-1"></i>
                                        <?= $st['label'] ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-hover mb-0" style="font-size:14px;">
                                    <tbody>
                                        <tr class="border-bottom">
                                            <th style="width:35%;background:#f8f9fa;padding:12px 16px;" class="text-muted align-middle">No Kas Bon</th>
                                            <td style="padding:12px 16px;" class="align-middle"><strong class="text-primary" style="font-size:15px;"><?= htmlspecialchars($kasbon['no_kasbon']) ?></strong></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <th style="background:#f8f9fa;padding:12px 16px;" class="text-muted align-middle">Nama Pemohon</th>
                                            <td style="padding:12px 16px;" class="align-middle"><i class="fas fa-user mr-2 text-secondary"></i> <strong><?= htmlspecialchars($kasbon['nama_pemohon']) ?></strong></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <th style="background:#f8f9fa;padding:12px 16px;" class="text-muted align-middle">Tanggal Pengajuan</th>
                                            <td style="padding:12px 16px;" class="align-middle"><i class="fas fa-calendar-alt mr-2 text-secondary"></i> <?= date('d F Y', strtotime($kasbon['tanggal_pengajuan'])) ?></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <th style="background:#f8f9fa;padding:12px 16px;" class="text-muted align-middle">Nominal Kas Bon</th>
                                            <td style="padding:12px 16px;" class="align-middle"><strong class="text-success" style="font-size:18px;">Rp <?= number_format($kasbon['nominal'], 0, ',', '.') ?></strong></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <th style="background:#f8f9fa;padding:12px 16px;" class="text-muted align-middle">Keterangan / Keperluan</th>
                                            <td style="padding:12px 16px;" class="align-middle"><?= nl2br(htmlspecialchars($kasbon['keterangan'])) ?></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <th style="background:#f8f9fa;padding:12px 16px;" class="text-muted align-middle">Tipe Approval Workflow</th>
                                            <td style="padding:12px 16px;" class="align-middle">
                                                <?php
                                                    $wt = $kasbon['workflow_type'] ?? '-';
                                                    $wt_label = ['IT' => 'IT (Atasan → Kasir)', 'KEUANGAN_SALES' => 'Keuangan/Sales (Penilai 1 → Penilai 2 → Kasir)', 'DEFAULT' => 'Default (Atasan → Kasir)'];
                                                    echo '<span class="badge badge-light border px-2 py-1">' . ($wt_label[$wt] ?? $wt) . '</span>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="background:#f8f9fa;padding:12px 16px;" class="text-muted align-middle">Lampiran Dokumen</th>
                                            <td style="padding:12px 16px;" class="align-middle">
                                                <?php if (!empty($kasbon['lampiran'])): ?>
                                                    <a href="<?= base_url('assets/uploads/kasbon/' . htmlspecialchars($kasbon['lampiran'])) ?>" target="_blank" class="btn btn-sm btn-info shadow-sm">
                                                        <i class="fas fa-paperclip mr-1"></i> Lihat Lampiran
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="fas fa-minus mr-1"></i> Tidak ada lampiran</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex align-items-center mb-4 flex-wrap" style="gap:10px;">
                            <a href="<?= base_url('C_Kasbon') ?>" class="btn btn-secondary shadow-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                            </a>

                            <?php if ($can_approve): ?>
                                <button onclick="approveKasbon(<?= $kasbon['id'] ?>, '<?= htmlspecialchars($kasbon['no_kasbon']) ?>')" class="btn btn-success shadow-sm">
                                    <i class="fas fa-check mr-1"></i> Setujui Pengajuan Ini
                                </button>
                                <button onclick="rejectKasbon(<?= $kasbon['id'] ?>, '<?= htmlspecialchars($kasbon['no_kasbon']) ?>')" class="btn btn-danger shadow-sm">
                                    <i class="fas fa-times mr-1"></i> Tolak Pengajuan Ini
                                </button>
                            <?php endif; ?>

                            <?php if ($can_cair): ?>
                                <button onclick="cairkanKasbon(<?= $kasbon['id'] ?>, '<?= htmlspecialchars($kasbon['no_kasbon']) ?>', <?= $kasbon['nominal'] ?>)" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-money-bill-wave mr-1"></i> Cairkan Uang
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Status Approval -->
                    <div class="col-md-5">
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-header bg-primary text-white py-3">
                                <h3 class="card-title font-weight-bold m-0"><i class="fas fa-tasks mr-2"></i> Status Approval</h3>
                            </div>
                            <div class="card-body bg-light">
                                <div class="timeline timeline-inverse">

                                    <!-- Pengajuan dibuat -->
                                    <div class="time-label">
                                        <span class="bg-success px-3 py-1"><?= date('d M Y', strtotime($kasbon['tanggal_pengajuan'])) ?></span>
                                    </div>
                                    <div>
                                        <i class="fas fa-file-alt bg-success"></i>
                                        <div class="timeline-item">
                                            <span class="time"><i class="fas fa-clock"></i> <?= date('H:i', strtotime($kasbon['created_at'])) ?></span>
                                            <h3 class="timeline-header"><strong><?= htmlspecialchars($kasbon['nama_pemohon']) ?></strong> membuat pengajuan</h3>
                                            <div class="timeline-body">
                                                Nominal: <strong>Rp <?= number_format($kasbon['nominal'], 0, ',', '.') ?></strong><br>
                                                No: <strong><?= htmlspecialchars($kasbon['no_kasbon']) ?></strong>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Approval Stage 1 -->
                                    <?php if (!empty($kasbon['approver_1'])): ?>
                                        <?php $label_app1 = $this->M_Kasbon->get_approver_label($kasbon['approver_1']); ?>
                                        <?php if (!empty($kasbon['approved_atasan_by'])): ?>
                                            <div>
                                                <i class="fas fa-check bg-success"></i>
                                                <div class="timeline-item">
                                                    <span class="time"><i class="fas fa-clock"></i> <?= date('d M Y H:i', strtotime($kasbon['approved_atasan_at'])) ?></span>
                                                    <h3 class="timeline-header text-success">Disetujui oleh <?= htmlspecialchars($label_app1) ?></h3>
                                                    <div class="timeline-body">
                                                        <strong><?= htmlspecialchars($kasbon['approved_atasan_by']) ?></strong> menyetujui pengajuan ini.
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div>
                                                <i class="fas fa-hourglass-half bg-warning"></i>
                                                <div class="timeline-item">
                                                    <h3 class="timeline-header text-warning">Menunggu <?= htmlspecialchars($label_app1) ?></h3>
                                                    <div class="timeline-body">
                                                        Pengajuan ini sedang menunggu persetujuan tahap pertama.
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <!-- Approval Stage 2 -->
                                    <?php if (!empty($kasbon['approver_2'])): ?>
                                        <?php $label_app2 = $this->M_Kasbon->get_approver_label($kasbon['approver_2']); ?>
                                        <?php if (!empty($kasbon['approved_penilai_by'])): ?>
                                            <div>
                                                <i class="fas fa-check bg-success"></i>
                                                <div class="timeline-item">
                                                    <span class="time"><i class="fas fa-clock"></i> <?= date('d M Y H:i', strtotime($kasbon['approved_penilai_at'])) ?></span>
                                                    <h3 class="timeline-header text-success">Disetujui oleh <?= htmlspecialchars($label_app2) ?></h3>
                                                    <div class="timeline-body">
                                                        <strong><?= htmlspecialchars($kasbon['approved_penilai_by']) ?></strong> menyetujui pengajuan ini.
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif (!empty($kasbon['approved_atasan_by'])): ?>
                                            <!-- Menunggu Stage 2 (hanya tampil jika Stage 1 sudah selesai) -->
                                            <div>
                                                <i class="fas fa-hourglass-half bg-warning"></i>
                                                <div class="timeline-item">
                                                    <h3 class="timeline-header text-warning">Menunggu <?= htmlspecialchars($label_app2) ?></h3>
                                                    <div class="timeline-body">
                                                        Pengajuan ini sedang menunggu persetujuan akhir.
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <!-- Ditolak -->
                                    <?php if ($kasbon['status'] === 'rejected'): ?>
                                        <div>
                                            <i class="fas fa-times bg-danger"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="fas fa-clock"></i> <?= !empty($kasbon['rejected_at']) ? date('d M Y H:i', strtotime($kasbon['rejected_at'])) : '-' ?></span>
                                                <h3 class="timeline-header text-danger">Pengajuan Ditolak</h3>
                                                <div class="timeline-body">
                                                    Ditolak oleh: <strong><?= htmlspecialchars($kasbon['rejected_by'] ?? '-') ?></strong>
                                                    <?php if (!empty($kasbon['rejected_reason'])): ?>
                                                        <br><em class="text-muted">Alasan: "<?= htmlspecialchars($kasbon['rejected_reason']) ?>"</em>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Status: Approved (Siap Cair) -->
                                    <?php if (in_array($kasbon['status'], ['approved', 'cair'])): ?>
                                        <div>
                                            <i class="fas fa-check-double bg-success"></i>
                                            <div class="timeline-item">
                                                <h3 class="timeline-header text-success">Semua Persetujuan Terpenuhi</h3>
                                                <div class="timeline-body">
                                                    Pengajuan telah mendapat semua persetujuan dan siap dicairkan oleh Kasir.
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Dicairkan oleh Kasir -->
                                    <?php if ($kasbon['status'] === 'cair' && !empty($kasbon['cair_by'])): ?>
                                        <div>
                                            <i class="fas fa-hand-holding-usd bg-info"></i>
                                            <div class="timeline-item">
                                                <span class="time"><i class="fas fa-clock"></i> <?= date('d M Y H:i', strtotime($kasbon['cair_at'])) ?></span>
                                                <h3 class="timeline-header text-info">Uang Dicairkan oleh Kasir</h3>
                                                <div class="timeline-body">
                                                    Dicairkan oleh: <strong><?= htmlspecialchars($kasbon['cair_by']) ?></strong><br>
                                                    Transaksi Kas Keluar otomatis telah dicatat di buku kasir.
                                                </div>
                                            </div>
                                        </div>
                                    <?php elseif ($kasbon['status'] === 'approved'): ?>
                                        <div>
                                            <i class="fas fa-clock bg-primary"></i>
                                            <div class="timeline-item">
                                                <h3 class="timeline-header text-primary">Menunggu Pencairan Kasir</h3>
                                                <div class="timeline-body">
                                                    Kasir akan mencairkan uang setelah konfirmasi.
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div><i class="fas fa-circle bg-secondary"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>

<script>
    function approveKasbon(id, noKasbon) {
        Swal.fire({
            title: 'Setujui Pengajuan?',
            text: 'Apakah Anda yakin ingin menyetujui pengajuan Kas Bon ' + noKasbon + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Setujui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('C_Kasbon/approve/') ?>' + id;
            }
        });
    }

    function rejectKasbon(id, noKasbon) {
        Swal.fire({
            title: 'Tolak Pengajuan Kas Bon?',
            text: 'Masukkan alasan penolakan untuk nomor ' + noKasbon + ':',
            input: 'textarea',
            inputPlaceholder: 'Tuliskan alasan penolakan di sini...',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Tolak!',
            cancelButtonText: 'Batal',
            preConfirm: (reason) => {
                if (!reason) {
                    Swal.showValidationMessage('Alasan penolakan tidak boleh kosong!');
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                var form = $('<form action="<?= base_url('C_Kasbon/reject/') ?>' + id + '" method="POST">' +
                             '<input type="hidden" name="rejected_reason" value="' + encodeURIComponent(result.value) + '">' +
                             '</form>');
                $('body').append(form);
                form.submit();
            }
        });
    }

    function cairkanKasbon(id, noKasbon, nominal) {
        Swal.fire({
            title: 'Cairkan Kas Bon?',
            html: 'Anda akan mencairkan Kas Bon <b>' + noKasbon + '</b> sebesar <b>Rp ' + parseInt(nominal).toLocaleString('id-ID') + '</b>.<br>Pencairan ini akan otomatis dicatat sebagai Kas Keluar di kasir.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Cairkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('C_Kasbon/cairkan/') ?>' + id;
            }
        });
    }
</script>
