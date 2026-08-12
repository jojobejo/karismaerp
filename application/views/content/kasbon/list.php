<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-money-bill-wave mr-2 text-success"></i> Kas Bon</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">Kas Bon</li>
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
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="row mb-3">
                    <div class="col-12 text-right">
                        <a href="<?= base_url('C_Kasbon/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i> Buat Pengajuan Kas Bon
                        </a>
                    </div>
                </div>

                <!-- TABEL KAS BON -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title m-0"><i class="fas fa-list mr-2"></i> Daftar Pengajuan Kas Bon</h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($kasbon) ?> Pengajuan</span>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0" id="table-kasbon" style="font-size: 14px;">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center" style="width: 40px;">No</th>
                                    <th>No Kas Bon</th>
                                    <th>Tanggal</th>
                                    <th>Nama Pemohon</th>
                                    <th>Keterangan</th>
                                    <th class="text-right">Nominal</th>
                                    <th class="text-center">Lampiran</th>
                                    <th class="text-center">Status / Approval Flow</th>
                                    <th class="text-center" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                        <tbody>
                            <?php $no = 1; foreach ($kasbon as $row) : ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++ ?></td>
                                <td class="align-middle"><strong><?= $row['no_kasbon'] ?></strong></td>
                                <td class="align-middle"><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($row['nama_pemohon']) ?></td>
                                <td class="align-middle"><?= htmlspecialchars($row['keterangan']) ?></td>
                                <td class="text-right align-middle font-weight-bold text-dark">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                                <td class="text-center align-middle">
                                    <?php if (!empty($row['lampiran'])): ?>
                                        <a href="<?= base_url('assets/uploads/kasbon/' . htmlspecialchars($row['lampiran'])) ?>" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-paperclip"></i> Lihat</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle">
                                    <?php 
                                        if ($row['status'] === 'pending_atasan') {
                                            $label = $this->M_Kasbon->get_approver_label($row['approver_1'] ?? '');
                                            echo '<span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Menunggu ' . htmlspecialchars($label) . '</span>';
                                        } elseif ($row['status'] === 'pending_penilai') {
                                            $label = $this->M_Kasbon->get_approver_label($row['approver_2'] ?? '');
                                            echo '<span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Menunggu ' . htmlspecialchars($label) . '</span>';
                                        } elseif ($row['status'] === 'approved') {
                                            echo '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Disetujui (Siap Cair)</span>';
                                        } elseif ($row['status'] === 'cair') {
                                            echo '<span class="badge badge-info"><i class="fas fa-hand-holding-usd mr-1"></i>Sudah Dicairkan</span>';
                                        } elseif ($row['status'] === 'rejected') {
                                            echo '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>';
                                        } else {
                                            echo '<span class="badge badge-secondary">'.htmlspecialchars($row['status']).'</span>';
                                        }
                                    ?>
                                    
                                    <!-- Flow Log Info -->
                                    <div class="text-left mt-1" style="font-size: 11px; line-height: 1.4;">
                                        <?php if (!empty($row['approved_atasan_by'])): ?>
                                            <div class="text-success"><i class="fas fa-check mr-1"></i><?= htmlspecialchars($this->M_Kasbon->get_approver_label($row['approver_1'] ?? '')) ?>: <?= htmlspecialchars($row['approved_atasan_by']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($row['approved_penilai_by'])): ?>
                                            <div class="text-success"><i class="fas fa-check mr-1"></i><?= htmlspecialchars($this->M_Kasbon->get_approver_label($row['approver_2'] ?? '')) ?>: <?= htmlspecialchars($row['approved_penilai_by']) ?></div>
                                        <?php endif; ?>
                                        <?php if ($row['status'] === 'rejected' && !empty($row['rejected_by'])): ?>
                                            <div class="text-danger"><i class="fas fa-times mr-1"></i>Ditolak: <?= htmlspecialchars($row['rejected_by']) ?></div>
                                            <?php if (!empty($row['rejected_reason'])): ?>
                                                <div class="text-muted font-italic">"<?= htmlspecialchars($row['rejected_reason']) ?>"</div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($row['cair_by'])): ?>
                                            <div class="text-info"><i class="fas fa-check-double mr-1"></i>Kasir: <?= htmlspecialchars($row['cair_by']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex flex-column align-items-center" style="gap:4px;">
                                        <!-- Tombol Detail selalu tampil -->
                                        <a href="<?= base_url('C_Kasbon/detail/' . $row['id']) ?>" class="btn btn-xs btn-secondary" title="Lihat Detail">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        
                                        <?php if ($this->M_Kasbon->can_approve($row, $user)): ?>
                                            <button onclick="approveKasbon(<?= $row['id'] ?>, '<?= htmlspecialchars($row['no_kasbon']) ?>')" class="btn btn-xs btn-success" title="Setujui Pengajuan">
                                                <i class="fas fa-check"></i> Setuju
                                            </button>
                                            <button onclick="rejectKasbon(<?= $row['id'] ?>, '<?= htmlspecialchars($row['no_kasbon']) ?>')" class="btn btn-xs btn-danger" title="Tolak Pengajuan">
                                                <i class="fas fa-times"></i> Tolak
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($this->M_Kasbon->can_cair($row, $user)): ?>
                                            <button onclick="cairkanKasbon(<?= $row['id'] ?>, '<?= htmlspecialchars($row['no_kasbon']) ?>', <?= $row['nominal'] ?>)" class="btn btn-xs btn-primary" title="Cairkan Uang">
                                                <i class="fas fa-money-bill-wave"></i> Cairkan
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </section>
</div>

</div> <!-- ./wrapper -->

<script>
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-kasbon').DataTable({
                "ordering": false,
                "language": {
                    "emptyTable": "Belum ada data pengajuan kas bon."
                }
            });
        }
    });

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
