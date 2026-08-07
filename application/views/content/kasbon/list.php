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
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                        <tbody>
                            <?php $no = 1; foreach ($kasbon as $row) : ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><strong><?= $row['no_kasbon'] ?></strong></td>
                                <td><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                                <td><?= $row['nama_pemohon'] ?></td>
                                <td><?= $row['keterangan'] ?></td>
                                <td class="text-right">Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <?php if (!empty($row['lampiran'])): ?>
                                        <a href="<?= base_url('assets/uploads/kasbon/' . htmlspecialchars($row['lampiran'])) ?>" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-paperclip"></i> Lihat</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        if ($row['status'] == 'pending') echo '<span class="badge badge-warning">Pending</span>';
                                        elseif ($row['status'] == 'approved') echo '<span class="badge badge-success">Disetujui</span>';
                                        elseif ($row['status'] == 'rejected') echo '<span class="badge badge-danger">Ditolak</span>';
                                        else echo '<span class="badge badge-secondary">'.$row['status'].'</span>';
                                    ?>
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
            $('#table-kasbon').DataTable();
        }
    });
</script>
