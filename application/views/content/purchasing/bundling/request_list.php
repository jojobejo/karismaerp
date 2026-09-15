<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <!-- Navbar -->
    <?php $this->load->view('partial/main/navbar') ?>
    <!-- Main Sidebar Container -->
    <?php $this->load->view('partial/main/sidebar') ?>

<div class="content-wrapper" style="min-height: 850px; background: #f8fafc;">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.6rem;">
                        <i class="fas fa-boxes text-primary mr-2"></i> Request Paket Bundling
                    </h1>
                    <p class="text-muted mb-0 small">Pengelolaan dan monitoring permintaan perakitan paket bundling dari Purchasing</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= site_url('purchasing/bundling/formula') ?>" class="btn btn-outline-secondary mr-2 shadow-sm font-weight-bold">
                        <i class="fas fa-book mr-1"></i> Master Formula Paket
                    </a>
                    <a href="<?= site_url('purchasing/bundling/request/create') ?>" class="btn btn-primary shadow-sm font-weight-bold px-3">
                        <i class="fas fa-plus-circle mr-1"></i> Buat Request Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Bar -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form method="get" action="<?= site_url('purchasing/bundling/request') ?>" class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-muted mb-1">Status Request</label>
                            <select name="status" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()">
                                <option value="SEMUA" <?= ($filters['status'] == 'SEMUA') ? 'selected' : '' ?>>Semua Status</option>
                                <option value="MENUNGGU_PROSES" <?= ($filters['status'] == 'MENUNGGU_PROSES') ? 'selected' : '' ?>>Menunggu Proses</option>
                                <option value="PROSES_SEBAGIAN" <?= ($filters['status'] == 'PROSES_SEBAGIAN') ? 'selected' : '' ?>>Proses Sebagian</option>
                                <option value="SELESAI" <?= ($filters['status'] == 'SELESAI') ? 'selected' : '' ?>>Selesai</option>
                                <option value="BATAL" <?= ($filters['status'] == 'BATAL') ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-muted mb-1">Cari No Request / Nama Paket</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Ketik nomor request / nama paket..." value="<?= htmlspecialchars($filters['search']) ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 text-right pt-2">
                            <?php if (!empty($filters['search']) || $filters['status'] !== 'SEMUA'): ?>
                                <a href="<?= site_url('purchasing/bundling/request') ?>" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-times-circle mr-1"></i> Reset Filter
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Daftar Request -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                            <thead style="background: #f1f5f9; color: #334155;">
                                <tr>
                                    <th class="py-3 px-3">No. Request</th>
                                    <th class="py-3">Tanggal</th>
                                    <th class="py-3">Nama Paket Bundling</th>
                                    <th class="py-3">Gudang Tujuan</th>
                                    <th class="py-3 text-center">Qty Request</th>
                                    <th class="py-3 text-center">Terealisasi</th>
                                    <th class="py-3 text-center">Sisa</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-center" style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 text-black-50 d-block"></i>
                                            Belum ada data request paket bundling.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $r): ?>
                                        <?php 
                                            $badgeClass = 'badge-secondary';
                                            if ($r['status'] == 'MENUNGGU_PROSES') $badgeClass = 'badge-warning text-dark';
                                            elseif ($r['status'] == 'PROSES_SEBAGIAN') $badgeClass = 'badge-info';
                                            elseif ($r['status'] == 'SELESAI') $badgeClass = 'badge-success';
                                            elseif ($r['status'] == 'BATAL') $badgeClass = 'badge-danger';
                                            
                                            $sisa = max(0, (float)$r['qty_request'] - (float)$r['qty_realisasi']);
                                            $percent = ((float)$r['qty_request'] > 0) ? min(100, round(((float)$r['qty_realisasi'] / (float)$r['qty_request']) * 100)) : 0;
                                        ?>
                                        <tr>
                                            <td class="px-3 font-weight-bold text-primary">
                                                <a href="<?= site_url('purchasing/bundling/request/detail/' . $r['id_request']) ?>">
                                                    <?= htmlspecialchars($r['no_request']) ?>
                                                </a>
                                                <div class="small text-muted font-weight-normal">Oleh: <?= htmlspecialchars($r['user_request']) ?></div>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($r['tanggal_request'])) ?></td>
                                            <td>
                                                <div class="font-weight-bold text-dark"><?= htmlspecialchars($r['nama_paket']) ?></div>
                                                <span class="badge badge-light border text-muted mr-1"><?= htmlspecialchars($r['kode_paket']) ?></span>
                                                <?php if (!empty($r['estimasi_hpp_per_paket']) && (float)$r['estimasi_hpp_per_paket'] > 0): ?>
                                                    <span class="badge badge-success px-2 py-1" style="font-size: 0.78rem;" title="Estimasi Modal HPP per 1 Paket">
                                                        <i class="fas fa-tag mr-1"></i> Modal: Rp <?= number_format((float)$r['estimasi_hpp_per_paket'], 0, ',', '.') ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($r['nama_gudang_tujuan'] ?: 'Gdg. Bundling') ?></td>
                                            <td class="text-center font-weight-bold text-dark">
                                                <?= number_format((float)$r['qty_request'], 0, ',', '.') ?> <?= htmlspecialchars($r['satuan']) ?>
                                            </td>
                                            <td class="text-center font-weight-bold text-success">
                                                <?= number_format((float)$r['qty_realisasi'], 0, ',', '.') ?> <?= htmlspecialchars($r['satuan']) ?>
                                                <div class="progress mt-1" style="height: 5px;">
                                                    <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                                                </div>
                                            </td>
                                            <td class="text-center font-weight-bold text-danger">
                                                <?= number_format($sisa, 0, ',', '.') ?> <?= htmlspecialchars($r['satuan']) ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $badgeClass ?> px-2 py-1 font-weight-bold" style="font-size: 0.8rem; border-radius: 6px;">
                                                    <?= str_replace('_', ' ', $r['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm shadow-sm">
                                                    <a href="<?= site_url('purchasing/bundling/request/detail/' . $r['id_request']) ?>" class="btn btn-outline-primary" title="Lihat Detail & Kebutuhan">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($r['status'] == 'MENUNGGU_PROSES'): ?>
                                                        <button type="button" class="btn btn-outline-danger" onclick="cancelRequest(<?= $r['id_request'] ?>, '<?= htmlspecialchars($r['no_request']) ?>')" title="Batalkan Request">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($r['status'] == 'BATAL'): ?>
                                                        <button type="button" class="btn btn-outline-danger" onclick="deleteRequest(<?= $r['id_request'] ?>, '<?= htmlspecialchars($r['no_request']) ?>')" title="Hapus Permanen Request">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- /.content-wrapper -->
</div>
<!-- /.wrapper -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function cancelRequest(id, noReq) {
    Swal.fire({
        title: 'Batalkan Request?',
        text: 'Apakah Anda yakin ingin membatalkan Request Bundling #' + noReq + '?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Batalkan!',
        cancelButtonText: 'Tutup'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= site_url("purchasing/bundling/request/cancel/") ?>' + id,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        Swal.fire('Berhasil', res.msg, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.msg, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                }
            });
        }
    });
}

function deleteRequest(id, noReq) {
    Swal.fire({
        title: 'Hapus Permanen Request?',
        text: 'Apakah Anda yakin ingin menghapus data Request Bundling #' + noReq + ' yang telah dibatalkan ini? Data yang dihapus tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Permanen!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= site_url("purchasing/bundling/request/delete/") ?>' + id,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        Swal.fire('Berhasil', res.msg, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.msg, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem saat menghapus data', 'error');
                }
            });
        }
    });
}
</script>
