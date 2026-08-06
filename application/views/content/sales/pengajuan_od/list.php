<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-file-invoice mr-2 text-primary"></i> Pengajuan OD / Tempo</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">Pengajuan OD</li>
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
                        <?php if (in_array($user['jobdesk'], ['SC', 'ADMINSC', 'ADMIN'])) : ?>
                            <a href="<?= base_url('sales/C_PengajuanOD/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i> Buat Pengajuan OD Baru
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- FILTER -->
                <div class="card card-primary card-outline">
                    <div class="card-header py-2">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas <?= !empty($is_history) ? 'fa-history' : 'fa-list' ?> mr-1"></i>
                            <?= !empty($is_history) ? 'Riwayat Pengajuan OD (Selesai / Ditolak)' : 'Daftar Pengajuan OD (Sedang Berjalan)' ?>
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <form method="get" action="<?= !empty($is_history) ? base_url('sales/C_PengajuanOD/history') : base_url('sales/C_PengajuanOD') ?>">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="small mb-0">Status Pengajuan</label>
                                    <select name="status" class="form-control form-control-sm">
                                        <option value="all" <?= ($this->input->get('status') == 'all' || !$this->input->get('status')) ? 'selected' : '' ?>>-- Semua --</option>
                                        <option value="pending" <?= $this->input->get('status') == 'pending' ? 'selected' : '' ?>>Menunggu Persetujuan</option>
                                        <option value="approved" <?= $this->input->get('status') == 'approved' ? 'selected' : '' ?>>Disetujui</option>
                                        <option value="rejected" <?= $this->input->get('status') == 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-success btn-sm mr-1"><i class="fas fa-search"></i> Tampil</button>
                                    <a href="<?= base_url('sales/C_PengajuanOD') ?>" class="btn btn-secondary btn-sm">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL PENGAJUAN -->
                <div class="card">
                    <div class="card-header <?= !empty($is_history) ? 'bg-secondary text-white' : 'bg-primary text-white' ?>">
                        <h3 class="card-title m-0"><i class="fas <?= !empty($is_history) ? 'fa-history' : 'fa-list' ?> mr-2"></i> <?= !empty($is_history) ? 'Riwayat Pengajuan OD (Selesai / Ditolak)' : 'Daftar Pengajuan OD' ?></h3>
                        <div class="card-tools">
                            <?php if (empty($is_history)) : ?>
                                <a href="<?= base_url('sales/C_PengajuanOD/history') ?>" class="btn btn-warning btn-sm font-weight-bold mr-2 text-dark">
                                    <i class="fas fa-history mr-1"></i> Riwayat Pengajuan
                                </a>
                            <?php else : ?>
                                <a href="<?= base_url('sales/C_PengajuanOD') ?>" class="btn btn-info btn-sm font-weight-bold mr-2">
                                    <i class="fas fa-list mr-1"></i> Pengajuan Aktif
                                </a>
                            <?php endif; ?>
                            <span class="badge badge-light"><?= count($pengajuan) ?> Pengajuan</span>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0" id="table-pengajuan" style="font-size: 14px;">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center" style="width: 40px;">No</th>
                                    <th>Tgl Pengajuan</th>
                                    <th style="width: 15%">Faktur Terpilih</th>
                                    <th style="width: 15%">Customer</th>
                                    <th class="text-center">Jml Faktur</th>
                                    <th class="text-center">Max Tempo Baru</th>
                                    <th>Status</th>
                                    <th>Dibuat Oleh</th>
                                    <th class="text-center" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                        <tbody>
                            <?php $no = 1; foreach ($pengajuan as $row) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                                <td><?= mb_strimwidth((string)$row['faktur_list_str'], 0, 25, '...') ?></td>
                                <td><?= $row['customer_name'] ?></td>
                                <td class="text-center"><?= $row['jumlah_faktur'] ?> Faktur</td>
                                <td class="text-center"><strong><?= $row['max_tempo_baru'] ?> Hari</strong></td>
                                <td>
                                    <?php 
                                        if ($row['status'] == 'pending_mngsc') echo '<span class="badge badge-warning">Menunggu Mng SC</span>';
                                        elseif ($row['status'] == 'pending_mngtc') echo '<span class="badge badge-warning">Menunggu Mng TC</span>';
                                        elseif ($row['status'] == 'pending_kadepsc') echo '<span class="badge badge-warning">Menunggu Kadep SC</span>';
                                        elseif ($row['status'] == 'approved') echo '<span class="badge badge-success">Disetujui / Selesai</span>';
                                        elseif ($row['status'] == 'rejected') echo '<span class="badge badge-danger">Ditolak</span>';
                                    ?>
                                </td>
                                <td><?= $row['create_by'] ?></td>
                                <td class="text-center" style="white-space: nowrap;">
                                    <div class="d-inline-flex" style="gap: 4px;">
                                         <!-- View Details Button -->
                                         <a href="<?= base_url('sales/C_PengajuanOD/detail/'.$row['id']) ?>" class="btn btn-info btn-sm">
                                             <i class="fas fa-eye"></i> Detail
                                         </a>

                                         <!-- Edit Button for SC / Admin -->
                                         <?php if ($row['status'] == 'pending_mngsc' && in_array($user['jobdesk'], ['SC', 'ADMINSC', 'ADMIN'])) : ?>
                                         <a href="<?= base_url('sales/C_PengajuanOD/edit/'.$row['id']) ?>" class="btn btn-warning btn-sm">
                                             <i class="fas fa-edit"></i> Edit
                                         </a>
                                         <?php endif; ?>

                                        <!-- Approval Action Buttons -->
                                        <?php
                                        $show_approve = false;
                                        if ($row['status'] == 'pending_mngsc' && in_array($user['jobdesk'], ['MANAGERSC', 'ADMIN'])) $show_approve = true;
                                        if ($row['status'] == 'pending_mngtc' && in_array($user['jobdesk'], ['MANAGERTC', 'ADMIN'])) $show_approve = true;
                                        if ($row['status'] == 'pending_kadepsc' && in_array($user['jobdesk'], ['KADEPSC', 'ADMIN'])) $show_approve = true;
                                        
                                        if ($show_approve) :
                                        ?>
                                        <a href="<?= base_url('sales/C_PengajuanOD/approval/'.$row['id']) ?>" class="btn btn-success btn-sm btn-approve">
                                            <i class="fas fa-check"></i> Tindakan
                                        </a>
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

<!-- jQuery (Load if not loaded in header) -->
<script>
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#table-pengajuan').DataTable();
        }
    });
</script>
