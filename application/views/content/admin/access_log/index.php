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
                    <h1 class="m-0 font-weight-bold" style="color: #0f172a; font-size: 1.5rem;">
                        <i class="fas fa-laptop-code text-primary mr-2"></i> Log Perangkat &amp; Akses Pengguna
                    </h1>
                    <p class="text-muted mb-0 small">Monitoring real-time pengunjung, lokasi perangkat (Kota &amp; Negara), OS, browser, dan IP yang mengakses Karisma ERP</p>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold shadow-xs" data-toggle="modal" data-target="#modalPurgeLog">
                        <i class="fas fa-trash-alt mr-1"></i> Bersihkan Log Lama
                    </button>
                    <a href="<?= site_url('admin/access_log') ?>" class="btn btn-primary btn-sm font-weight-bold shadow-sm ml-2">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh Data
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- 4 Summary Cards Monitoring Perangkat -->
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border-left: 5px solid #2563eb !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted font-weight-bold small text-uppercase">Akses Hari Ini</span>
                                    <h3 class="mb-0 font-weight-bold text-primary mt-1"><?= number_format($stats['total_today']) ?></h3>
                                    <small class="text-muted">Total hit request hari ini</small>
                                </div>
                                <div class="p-3 rounded-circle text-primary" style="background: #dbeafe;">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border-left: 5px solid #0284c7 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted font-weight-bold small text-uppercase">Desktop / PC</span>
                                    <h3 class="mb-0 font-weight-bold text-info mt-1"><?= number_format($stats['desktop_today']) ?></h3>
                                    <small class="text-muted">
                                        <?= $stats['total_today'] > 0 ? round(($stats['desktop_today'] / $stats['total_today']) * 100) : 0 ?>% dari total akses
                                    </small>
                                </div>
                                <div class="p-3 rounded-circle text-info" style="background: #e0f2fe;">
                                    <i class="fas fa-desktop fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border-left: 5px solid #10b981 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted font-weight-bold small text-uppercase">Mobile Phone / HP</span>
                                    <h3 class="mb-0 font-weight-bold text-success mt-1"><?= number_format($stats['mobile_today']) ?></h3>
                                    <small class="text-muted">
                                        <?= $stats['total_today'] > 0 ? round(($stats['mobile_today'] / $stats['total_today']) * 100) : 0 ?>% dari total akses
                                    </small>
                                </div>
                                <div class="p-3 rounded-circle text-success" style="background: #d1fae5;">
                                    <i class="fas fa-mobile-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border-left: 5px solid #f59e0b !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted font-weight-bold small text-uppercase">IP Unik Pengunjung</span>
                                    <h3 class="mb-0 font-weight-bold text-warning mt-1"><?= number_format($stats['unique_ip_today']) ?></h3>
                                    <small class="text-muted">Alamat IP berbeda hari ini</small>
                                </div>
                                <div class="p-3 rounded-circle text-warning" style="background: #fef3c7;">
                                    <i class="fas fa-network-wired fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar Card -->
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <form method="get" action="<?= site_url('admin/access_log') ?>" class="row align-items-end">
                        <div class="col-md-3 form-group mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from']) ?>">
                        </div>
                        <div class="col-md-3 form-group mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to']) ?>">
                        </div>
                        <div class="col-md-2 form-group mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Tipe Perangkat</label>
                            <select name="device_type" class="form-control form-control-sm font-weight-bold">
                                <option value="SEMUA" <?= ($filters['device_type'] === 'SEMUA') ? 'selected' : '' ?>>Semua Perangkat</option>
                                <option value="Desktop" <?= ($filters['device_type'] === 'Desktop') ? 'selected' : '' ?>>Desktop / PC</option>
                                <option value="Mobile Phone" <?= ($filters['device_type'] === 'Mobile Phone') ? 'selected' : '' ?>>Mobile Phone (HP)</option>
                                <option value="Tablet" <?= ($filters['device_type'] === 'Tablet') ? 'selected' : '' ?>>Tablet</option>
                                <option value="Bot / Crawler" <?= ($filters['device_type'] === 'Bot / Crawler') ? 'selected' : '' ?>>Bot / Crawler</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Cari IP / User / Kota / Negara / Merk</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Contoh: Surabaya, iPhone, admin..." value="<?= htmlspecialchars($filters['search']) ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1 form-group mb-2 mb-md-0 text-right">
                            <a href="<?= site_url('admin/access_log') ?>" class="btn btn-sm btn-outline-secondary btn-block" title="Reset Filter">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Daftar Log Akses -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-history text-primary mr-1"></i> Riwayat Akses Halaman Terakhir
                        </h6>
                        <span class="badge badge-light border text-muted px-2 py-1">
                            Total: <?= number_format($total_rows) ?> Catatan Log
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                            <thead style="background: #f1f5f9; color: #334155;">
                                <tr>
                                    <th class="py-3 px-3" style="width: 140px;">Waktu Akses</th>
                                    <th class="py-3">Pengguna</th>
                                    <th class="py-3">Perangkat &amp; Model</th>
                                    <th class="py-3">Sistem Operasi</th>
                                    <th class="py-3">Browser</th>
                                    <th class="py-3">Alamat IP</th>
                                    <th class="py-3" style="min-width: 170px; background: #eff6ff; color: #1e40af;">
                                        <i class="fas fa-map-marker-alt text-danger mr-1"></i> Lokasi Perangkat
                                    </th>
                                    <th class="py-3">Halaman yang Dibuka</th>
                                    <th class="py-3 text-center" style="width: 70px;">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-search fa-3x mb-3 text-black-50 d-block"></i>
                                            Belum ada catatan log aktivitas akses pada filter ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $row): ?>
                                        <tr>
                                            <td class="px-3 text-muted">
                                                <strong><?= date('d/m/Y', strtotime($row['created_at'])) ?></strong>
                                                <br><small class="text-primary font-weight-bold"><?= date('H:i:s', strtotime($row['created_at'])) ?> WIB</small>
                                            </td>
                                            <td>
                                                <?php if ($row['username'] !== 'Guest'): ?>
                                                    <strong class="text-dark"><i class="fas fa-user-check text-success mr-1"></i><?= htmlspecialchars($row['username']) ?></strong>
                                                    <?php if (!empty($row['nama_user']) && $row['nama_user'] !== $row['username']): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($row['nama_user']) ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary"><i class="fas fa-user-secret mr-1"></i>Guest</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['device_type'] === 'Mobile Phone'): ?>
                                                    <span class="badge badge-success font-weight-bold px-2 py-1">
                                                        <i class="fas fa-mobile-alt mr-1"></i> HP / Mobile
                                                    </span>
                                                <?php elseif ($row['device_type'] === 'Tablet'): ?>
                                                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                                                        <i class="fas fa-tablet-alt mr-1"></i> Tablet
                                                    </span>
                                                <?php elseif ($row['device_type'] === 'Bot / Crawler'): ?>
                                                    <span class="badge badge-secondary font-weight-bold px-2 py-1">
                                                        <i class="fas fa-robot mr-1"></i> Bot
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-primary font-weight-bold px-2 py-1">
                                                        <i class="fas fa-desktop mr-1"></i> Desktop
                                                    </span>
                                                <?php endif; ?>
                                                <div class="mt-1 font-weight-bold text-dark small">
                                                    <?= htmlspecialchars($row['device_brand'] ?: '-') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-dark"><?= htmlspecialchars($row['os'] ?: '-') ?></span>
                                            </td>
                                            <td>
                                                <span class="text-dark"><?= htmlspecialchars($row['browser'] ?: '-') ?></span>
                                            </td>
                                            <td>
                                                <code class="px-2 py-1 bg-light border rounded text-dark font-weight-bold" style="font-size: 0.85rem;">
                                                    <?= htmlspecialchars($row['ip_address']) ?>
                                                </code>
                                            </td>
                                            <td style="background: #f8faff;">
                                                <?php if ($row['location_source'] === 'Lokal' || $row['city'] === 'Localhost / LAN' || (!empty($row['district']) && strpos($row['district'], 'Sumbersari') !== false && strpos($row['country'], 'Lokal') !== false)): ?>
                                                    <span class="badge badge-success font-weight-bold px-2 py-1" style="font-size: 0.75rem;">
                                                        <i class="fas fa-network-wired mr-1"></i> Jaringan Kantor
                                                    </span>
                                                    <div class="mt-1 font-weight-bold text-dark">
                                                        Indonesia (Lokal), Jember
                                                    </div>
                                                    <small class="text-primary font-weight-bold">
                                                        <i class="fas fa-map-marker-alt text-danger mr-1"></i> Kecamatan Sumbersari
                                                    </small>
                                                    <?php if (!empty($row['isp']) && $row['isp'] !== '-'): ?>
                                                        <br><small class="text-muted"><i class="fas fa-wifi text-secondary mr-1"></i><?= htmlspecialchars($row['isp']) ?></small>
                                                    <?php endif; ?>
                                                <?php elseif ($row['location_source'] === 'GPS'): ?>
                                                    <span class="badge badge-primary font-weight-bold px-2 py-1" style="font-size: 0.75rem;">
                                                        <i class="fas fa-crosshairs mr-1"></i> GPS Presisi
                                                    </span>
                                                    <?php if (!empty($row['district']) && $row['district'] !== '-'): ?>
                                                        <div class="mt-1 font-weight-bold text-dark">
                                                            <i class="fas fa-map-pin text-danger mr-1"></i><?= htmlspecialchars($row['district']) ?>
                                                        </div>
                                                        <small class="text-muted font-weight-bold">
                                                            <?= htmlspecialchars($row['city']) ?>, <?= htmlspecialchars($row['region'] ?: $row['country']) ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <div class="mt-1 font-weight-bold text-dark">
                                                            <i class="fas fa-map-pin text-danger mr-1"></i><?= htmlspecialchars($row['city'] ?: 'Lokasi Terdeteksi') ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($row['latitude']) && !empty($row['longitude'])): ?>
                                                        <br>
                                                        <a href="https://www.google.com/maps?q=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>" target="_blank" rel="noopener noreferrer" class="small text-danger font-weight-bold">
                                                            <i class="fas fa-map-marked-alt mr-1"></i> Buka Titik Peta GPS
                                                        </a>
                                                    <?php endif; ?>
                                                <?php elseif (!empty($row['city']) && $row['city'] !== '-'): ?>
                                                    <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 0.75rem;">
                                                        <i class="fas fa-globe mr-1"></i> Estimasi IP
                                                    </span>
                                                    <div class="mt-1 font-weight-bold text-dark">
                                                        <i class="fas fa-map-marker-alt text-danger mr-1"></i><?= htmlspecialchars($row['city']) ?>
                                                        <?php if (!empty($row['country']) && $row['country'] !== '-'): ?>
                                                            , <small class="text-muted font-weight-bold"><?= htmlspecialchars($row['country']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($row['isp']) && $row['isp'] !== '-'): ?>
                                                        <small class="text-muted"><i class="fas fa-wifi text-secondary mr-1"></i><?= htmlspecialchars($row['isp']) ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted small font-italic"><i class="fas fa-globe text-secondary mr-1"></i>Indonesia</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="max-width: 320px;">
                                                <span class="badge badge-<?= ($row['http_method'] === 'POST') ? 'warning text-dark' : 'light border' ?> mr-1 font-weight-bold">
                                                    <?= htmlspecialchars($row['http_method']) ?>
                                                </span>
                                                <span class="text-truncate d-inline-block align-middle font-weight-bold text-primary" style="max-width: 250px;" title="<?= htmlspecialchars($row['url_accessed']) ?>">
                                                    <?= htmlspecialchars(parse_url($row['url_accessed'], PHP_URL_PATH) ?: $row['url_accessed']) ?>
                                                </span>
                                                <?php if (!empty($row['is_ajax'])): ?>
                                                    <small class="badge badge-light border text-muted ml-1">AJAX</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-info btn-xs py-1 px-2" 
                                                        onclick="showUserAgentModal('<?= htmlspecialchars(addslashes($row['user_agent'])) ?>', '<?= htmlspecialchars($row['device_brand']) ?>', '<?= htmlspecialchars($row['ip_address']) ?>', '<?= htmlspecialchars(($row['district'] ? $row['district'] . ', ' : '') . ($row['city'] ?? '-') . ', ' . ($row['country'] ?? '-')) ?>', '<?= htmlspecialchars($row['isp'] ?? '-') ?>', '<?= !empty($row['latitude']) ? (float)$row['latitude'] : '' ?>', '<?= !empty($row['longitude']) ? (float)$row['longitude'] : '' ?>', '<?= htmlspecialchars($row['location_source'] ?? 'IP') ?>')"
                                                        title="Lihat detail lokasi &amp; User-Agent lengkap">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3 border-top">
                        <small class="text-muted">
                            Menampilkan halaman <strong><?= $current_page ?></strong> dari <strong><?= $total_pages ?></strong> (Total <?= number_format($total_rows) ?> log)
                        </small>
                        <nav aria-label="Navigasi Halaman">
                            <ul class="pagination pagination-sm m-0">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= site_url('admin/access_log') . '?page=' . ($current_page - 1) . '&date_from=' . urlencode($filters['date_from']) . '&date_to=' . urlencode($filters['date_to']) . '&device_type=' . urlencode($filters['device_type']) . '&search=' . urlencode($filters['search']) ?>">&laquo; Prev</a>
                                    </li>
                                <?php endif; ?>

                                <?php 
                                $startP = max(1, $current_page - 2);
                                $endP = min($total_pages, $current_page + 2);
                                for ($p = $startP; $p <= $endP; $p++): 
                                ?>
                                    <li class="page-item <?= ($p == $current_page) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= site_url('admin/access_log') . '?page=' . $p . '&date_from=' . urlencode($filters['date_from']) . '&date_to=' . urlencode($filters['date_to']) . '&device_type=' . urlencode($filters['device_type']) . '&search=' . urlencode($filters['search']) ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= site_url('admin/access_log') . '?page=' . ($current_page + 1) . '&date_from=' . urlencode($filters['date_from']) . '&date_to=' . urlencode($filters['date_to']) . '&device_type=' . urlencode($filters['device_type']) . '&search=' . urlencode($filters['search']) ?>">Next &raquo;</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>
</div>

<!-- Modal Raw User Agent & Lokasi Detail -->
<div class="modal fade" id="modalUserAgent" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title font-weight-bold">
                    <i class="fas fa-map-marker-alt mr-2 text-warning"></i> Detail Lokasi &amp; User-Agent
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2">
                    <small class="text-muted font-weight-bold text-uppercase">Perangkat:</small>
                    <div class="font-weight-bold text-dark" id="modalUaBrand">-</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted font-weight-bold text-uppercase">Tipe &amp; Lokasi Geografis:</small>
                    <div class="font-weight-bold text-danger" id="modalUaLoc">-</div>
                    <div id="modalUaGpsBtn" class="mt-1"></div>
                </div>
                <div class="mb-2">
                    <small class="text-muted font-weight-bold text-uppercase">ISP / Jaringan:</small>
                    <div class="font-weight-bold text-dark" id="modalUaIsp">-</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted font-weight-bold text-uppercase">Alamat IP:</small>
                    <div class="font-weight-bold text-primary" id="modalUaIp">-</div>
                </div>
                <div>
                    <small class="text-muted font-weight-bold text-uppercase">Raw User-Agent Header:</small>
                    <textarea class="form-control form-control-sm mt-1" rows="3" readonly id="modalUaRaw" style="background:#f1f5f9; font-family: monospace; font-size:0.80rem;"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Pembersihan Log Lama -->
<div class="modal fade" id="modalPurgeLog" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <form action="<?= site_url('admin/access_log/purge') ?>" method="post">
                <div class="modal-header bg-danger text-white border-0" style="border-radius: 12px 12px 0 0;">
                    <h6 class="modal-title font-weight-bold">
                        <i class="fas fa-trash-alt mr-2"></i> Pembersihan Log Lama
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Fitur ini membantu menjaga kapasitas database Hostinger Anda agar tetap cepat dan hemat ruang dengan menghapus data log yang sudah lama.
                    </p>
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold text-dark">Hapus log yang lebih lama dari:</label>
                        <select name="days" class="form-control form-control-sm font-weight-bold">
                            <option value="30">30 Hari yang lalu</option>
                            <option value="60" selected>60 Hari yang lalu</option>
                            <option value="90">90 Hari yang lalu</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold px-3">
                        <i class="fas fa-trash mr-1"></i> Bersihkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showUserAgentModal(rawUa, brand, ip, loc, isp, lat, lng, source) {
    $('#modalUaBrand').text(brand || '-');
    $('#modalUaIp').text(ip || '-');

    var sourceBadge = '<span class="badge badge-secondary mr-1">' + (source || 'IP') + '</span> ';
    if (source === 'GPS') {
        sourceBadge = '<span class="badge badge-primary mr-1"><i class="fas fa-crosshairs mr-1"></i>GPS Presisi</span> ';
    } else if (source === 'Lokal') {
        sourceBadge = '<span class="badge badge-success mr-1"><i class="fas fa-network-wired mr-1"></i>Lokal / Kantor</span> ';
    }

    $('#modalUaLoc').html(sourceBadge + (loc || '-'));
    $('#modalUaIsp').text(isp || '-');
    $('#modalUaRaw').val(rawUa || '-');

    if (lat && lng) {
        $('#modalUaGpsBtn').html(
            '<a href="https://www.google.com/maps?q=' + lat + ',' + lng + '" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline-danger font-weight-bold">' +
            '<i class="fas fa-map-marked-alt mr-1"></i> Buka Titik Koordinat GPS di Google Maps (' + lat + ', ' + lng + ')' +
            '</a>'
        );
    } else {
        $('#modalUaGpsBtn').empty();
    }

    $('#modalUserAgent').modal('show');
}
</script>
