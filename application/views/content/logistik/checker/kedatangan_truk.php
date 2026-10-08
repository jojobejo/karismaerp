<!-- view/content/logistik/checker/kedatangan_truk.php -->

<style>
.badge-plat {
    background: #111827;
    color: #f9fafb;
    font-family: 'Courier New', Courier, monospace;
    font-weight: 700;
    letter-spacing: 1.5px;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid #374151;
    display: inline-block;
}
.badge-pintu-lg {
    font-size: 13px;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 6px;
}
.card-pintu {
    border-radius: 8px;
    transition: transform .15s ease, box-shadow .15s ease;
    border: 1px solid #e5e7eb;
}
.card-pintu:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0,0,0,0.08);
}
.card-pintu.pintu-kosong {
    background: #f0fdf4;
    border-color: #86efac;
}
.card-pintu.pintu-menunggu {
    background: #fffbeb;
    border-color: #fde68a;
}
.card-pintu.pintu-proses {
    background: #eff6ff;
    border-color: #93c5fd;
}
.pulse-indicator {
    display: inline-block;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #ef4444;
    box-shadow: 0 0 0 rgba(239, 68, 68, 0.4);
    animation: pulse 1.8s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
.table-hover tbody tr:hover {
    background-color: rgba(243, 244, 246, 0.7) !important;
}
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <!-- Header Title & Action Buttons -->
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold">
                            <i class="fas fa-truck-moving text-primary mr-2"></i>
                            Antrean Truk & Alokasi Pintu Bongkar
                        </h1>
                        <p class="text-muted small mb-0 mt-1">
                            Alur: Pos Security konfirmasi kedatangan truk &rarr; Manager Checker tentukan pintu &rarr; Checker langsung start bongkaran.
                        </p>
                    </div>
                    <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('checker') ?>" class="btn btn-outline-secondary mr-2">
                            <i class="fas fa-arrow-left mr-1"></i> Aktivitas Warehouse
                        </a>
                        <button class="btn btn-success mr-2" data-toggle="modal" data-target="#modalTambahJadwal">
                            <i class="fas fa-plus mr-1"></i> Input / Jadwal Truk
                        </button>
                    </div>
                </div>

                <!-- KPI Summary Cards -->
                <?php
                $cnt_sudah_datang = count($truk_sudah_datang);
                $cnt_menunggu_bongkar = count(array_filter($truk_semua, fn($r) => $r['status'] === 'MENUNGGU_BONGKAR'));
                $cnt_proses_bongkar   = count(array_filter($truk_semua, fn($r) => $r['status'] === 'PROSES_BONGKAR'));
                $cnt_pintu_tersedia   = count(array_filter($status_pintu, fn($p) => $p['status'] === 'KOSONG'));
                ?>
                <div class="row">
                    <!-- Card 1: Truk Tiba di Pos (Menunggu Pintu) -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning shadow-sm">
                            <div class="inner">
                                <h3 class="font-weight-bold mb-1">
                                    <?= $cnt_sudah_datang ?>
                                    <?php if ($cnt_sudah_datang > 0) : ?>
                                        <span class="pulse-indicator ml-2 align-middle"></span>
                                    <?php endif; ?>
                                </h3>
                                <p class="font-weight-bold">Truk Tiba di Pos (Butuh Pintu)</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <a href="#tabelSudahDatang" class="small-box-footer">
                                Prioritas Manager CK <i class="fas fa-arrow-circle-down"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Menunggu Checker Start -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info shadow-sm">
                            <div class="inner">
                                <h3 class="font-weight-bold mb-1"><?= $cnt_menunggu_bongkar ?></h3>
                                <p>Pintu Ditentukan (Siap Bongkar)</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <a href="<?= base_url('checker') ?>" class="small-box-footer">
                                Lihat di Aktivitas Checker <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 3: Sedang Proses Bongkar -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary shadow-sm">
                            <div class="inner">
                                <h3 class="font-weight-bold mb-1"><?= $cnt_proses_bongkar ?></h3>
                                <p>Sedang Proses Bongkaran</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-dolly"></i>
                            </div>
                            <a href="<?= base_url('checker') ?>" class="small-box-footer">
                                Monitor Live Bongkar <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 4: Ketersediaan Pintu -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success shadow-sm">
                            <div class="inner">
                                <h3 class="font-weight-bold mb-1"><?= $cnt_pintu_tersedia ?> <span style="font-size:20px;">/ 10</span></h3>
                                <p>Pintu Gudang Tersedia / Free</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-warehouse"></i>
                            </div>
                            <a href="#statusPintuGrid" class="small-box-footer">
                                Cek Ketersediaan Pintu <i class="fas fa-arrow-circle-down"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ============================================================
                    PANEL STATUS KETERISIAN 10 PINTU GUDANG (LIVE)
                ============================================================ -->
                <div class="card mb-4 shadow-sm" id="statusPintuGrid">
                    <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between">
                        <h3 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-th-large mr-2 text-warning"></i>
                            Status Ketersediaan 10 Pintu Gudang Saat Ini
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-success mr-2"><i class="fas fa-circle mr-1"></i> Kosong (<?= $cnt_pintu_tersedia ?>)</span>
                            <span class="badge badge-warning mr-2"><i class="fas fa-clock mr-1"></i> Menunggu Checker (<?= 10 - $cnt_pintu_tersedia - $cnt_proses_bongkar ?>)</span>
                            <span class="badge badge-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Sedang Bongkar/Muat (<?= $cnt_proses_bongkar ?>)</span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <?php foreach ($status_pintu as $no_p => $p) : 
                                $class_card = 'pintu-kosong';
                                $badge_color = 'badge-success';
                                if ($p['status'] === 'PROSES') {
                                    $class_card = 'pintu-proses';
                                    $badge_color = 'badge-primary';
                                } elseif ($p['status'] === 'MENUNGGU') {
                                    $class_card = 'pintu-menunggu';
                                    $badge_color = 'badge-warning';
                                }
                            ?>
                            <div class="col-md-2 col-sm-4 col-6 mb-2">
                                <div class="card-pintu p-2 h-100 <?= $class_card ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-weight-bold" style="font-size:15px; color:#1f2937;">
                                            <i class="fas fa-door-open mr-1"></i> Pintu <?= $p['label'] ?>
                                        </span>
                                        <span class="badge <?= $badge_color ?>" style="font-size:10px;">
                                            <?= $p['status'] ?>
                                        </span>
                                    </div>
                                    <div style="font-size:11px; color:#4b5563; min-height:36px;" class="text-truncate" title="<?= htmlspecialchars($p['info']) ?>">
                                        <?= htmlspecialchars($p['info']) ?>
                                    </div>
                                    <?php if ($p['status'] !== 'KOSONG') : ?>
                                        <div class="mt-1" style="font-size:10px; color:#6b7280;">
                                            <i class="fas fa-user mr-1"></i> <?= htmlspecialchars($p['checker']) ?>
                                        </div>
                                    <?php else : ?>
                                        <div class="mt-1 text-success font-weight-bold" style="font-size:11px;">
                                            <i class="fas fa-check-circle mr-1"></i> Siap Pakai
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- ============================================================
                    BAGIAN UTAMA 1: DAFTAR TRUK SUDAH DATANG (MENUNGGU MANAGER CK)
                ============================================================ -->
                <div class="card card-warning card-outline mb-4 shadow-sm" id="tabelSudahDatang">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <div>
                            <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size:17px;">
                                <i class="fas fa-truck-loading text-warning mr-2"></i>
                                Truk yang Sudah Tiba di Pos Security — Menunggu Alokasi Pintu
                            </h3>
                            <span class="badge badge-warning ml-2"><?= $cnt_sudah_datang ?> Truk Siap Dialokasikan</span>
                        </div>
                        <div class="card-tools">
                            <small class="text-muted mr-2">
                                <i class="fas fa-info-circle mr-1"></i> Manager Checker menentukan pintu agar Checker bisa langsung start tanpa pilih pintu
                            </small>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover table-striped mb-0" style="font-size:13px;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:40px;">No</th>
                                    <th>Waktu Kedatangan di Pos</th>
                                    <th>No. Polisi Truk</th>
                                    <th>Sopir & HP</th>
                                    <th>Supplier / Ekspedisi</th>
                                    <th>Jenis & Qty Muatan</th>
                                    <th>No. PO / SJ</th>
                                    <th>Petugas Security & Catatan</th>
                                    <th class="text-center" style="width:180px;">Aksi Manager CK</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($truk_sudah_datang)) : ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                        <em>Tidak ada truk yang menunggu alokasi pintu saat ini. Semua truk yang datang sudah dialokasikan ke pintu bongkaran.</em>
                                    </td>
                                </tr>
                                <?php else : ?>
                                <?php $no = 1; foreach ($truk_sudah_datang as $t) : ?>
                                <tr>
                                    <td class="font-weight-bold align-middle"><?= $no++ ?></td>
                                    <td class="align-middle">
                                        <span class="font-weight-bold text-dark">
                                            <?= $t['waktu_kedatangan'] ? date('H:i', strtotime($t['waktu_kedatangan'])) : '-' ?> WIB
                                        </span><br>
                                        <small class="text-muted">
                                            <?= $t['waktu_kedatangan'] ? date('d/m/Y', strtotime($t['waktu_kedatangan'])) : '-' ?>
                                        </small>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge-plat"><?= htmlspecialchars($t['nopol']) ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <strong><?= htmlspecialchars($t['nama_sopir'] ?: '-') ?></strong><br>
                                        <small class="text-muted"><i class="fas fa-phone mr-1"></i><?= htmlspecialchars($t['no_telp_sopir'] ?: '-') ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <strong><?= htmlspecialchars($t['supplier'] ?: '-') ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($t['ekspedisi'] ?: '-') ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-light border font-weight-normal mb-1">
                                            <?= htmlspecialchars($t['jenis_muatan'] ?: '-') ?>
                                        </span><br>
                                        <small class="text-muted font-weight-bold"><?= htmlspecialchars($t['jumlah_muatan'] ?: '-') ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <small><strong>PO:</strong> <?= htmlspecialchars($t['no_po'] ?: '-') ?></small><br>
                                        <small><strong>SJ:</strong> <?= htmlspecialchars($t['no_sj'] ?: '-') ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <small class="text-primary font-weight-bold">
                                            <i class="fas fa-user-shield mr-1"></i><?= htmlspecialchars($t['petugas_security'] ?: 'Pos Security') ?>
                                        </small><br>
                                        <small class="text-muted font-italic"><?= htmlspecialchars($t['catatan_security'] ?: 'Kondisi baik') ?></small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($is_mck || $role === 'MANAGERWH') : ?>
                                            <button class="btn btn-primary btn-sm btn-block font-weight-bold btn-buka-assign"
                                                    data-id="<?= $t['id'] ?>"
                                                    data-nopol="<?= htmlspecialchars($t['nopol']) ?>"
                                                    data-sopir="<?= htmlspecialchars($t['nama_sopir']) ?>"
                                                    data-supplier="<?= htmlspecialchars($t['supplier']) ?>"
                                                    data-muatan="<?= htmlspecialchars($t['jenis_muatan']) ?>"
                                                    data-pintu="<?= $t['pintu'] ?? '' ?>">
                                                <i class="fas fa-door-open mr-1"></i> Tentukan Pintu
                                            </button>
                                        <?php else : ?>
                                            <span class="badge badge-secondary py-2 px-2 d-block">
                                                <i class="fas fa-lock mr-1"></i> Hak Akses Manager CK
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============================================================
                    BAGIAN 2: SELURUH DAFTAR ANTREAN & JADWAL KEDATANGAN TRUK
                ============================================================ -->
                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                        <h3 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-list-ul mr-2 text-primary"></i>
                            Seluruh Riwayat Antrean & Jadwal Kedatangan Truk
                        </h3>
                        <div class="card-tools d-flex align-items-center">
                            <!-- Filter Status -->
                            <form method="get" action="<?= base_url('checker/kedatangan_truk') ?>" class="form-inline mr-2">
                                <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                    <option value="">-- Semua Status --</option>
                                    <option value="TERJADWAL" <?= $status_filter==='TERJADWAL'?'selected':'' ?>>Terjadwal (Belum Tiba)</option>
                                    <option value="SUDAH_DATANG" <?= $status_filter==='SUDAH_DATANG'?'selected':'' ?>>Sudah Datang di Pos</option>
                                    <option value="MENUNGGU_BONGKAR" <?= $status_filter==='MENUNGGU_BONGKAR'?'selected':'' ?>>Menunggu Bongkar (Pintu Terpilih)</option>
                                    <option value="PROSES_BONGKAR" <?= $status_filter==='PROSES_BONGKAR'?'selected':'' ?>>Proses Bongkar</option>
                                    <option value="DONE" <?= $status_filter==='DONE'?'selected':'' ?>>Selesai (Done)</option>
                                </select>
                            </form>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover table-striped mb-0" id="tabelSemuaTruk" style="font-size:13px;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:40px;">No</th>
                                    <th>No. Antrean</th>
                                    <th>No. Polisi</th>
                                    <th>Supplier & Ekspedisi</th>
                                    <th>Jenis & Qty Muatan</th>
                                    <th>Jadwal / Wkt Tiba</th>
                                    <th>Pintu Bongkar</th>
                                    <th>Status Antrean</th>
                                    <th>Checker</th>
                                    <th class="text-center" style="width:140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($truk_semua)) : ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">Belum ada data truk</td>
                                </tr>
                                <?php else : ?>
                                <?php $no = 1; foreach ($truk_semua as $row) : 
                                    $st = $row['status'];
                                    $st_badge = 'badge-secondary';
                                    $st_label = $st;
                                    if ($st === 'TERJADWAL') {
                                        $st_badge = 'badge-info'; $st_label = 'Terjadwal (Belum Tiba)';
                                    } elseif ($st === 'SUDAH_DATANG') {
                                        $st_badge = 'badge-warning'; $st_label = 'Tiba di Pos (Pintu Belum)';
                                    } elseif ($st === 'MENUNGGU_BONGKAR') {
                                        $st_badge = 'badge-secondary'; $st_label = 'Menunggu Checker';
                                    } elseif ($st === 'PROSES_BONGKAR') {
                                        $st_badge = 'badge-primary'; $st_label = 'Proses Bongkar';
                                    } elseif ($st === 'DONE') {
                                        $st_badge = 'badge-success'; $st_label = 'Selesai';
                                    }
                                    $lbl_pintu = !empty($row['pintu']) ? ($nama_pintu_map[$row['pintu'] - 1] ?? 'P'.$row['pintu']) : '-';
                                ?>
                                <tr>
                                    <td class="align-middle"><?= $no++ ?></td>
                                    <td class="align-middle">
                                        <small class="font-weight-bold text-dark"><?= htmlspecialchars($row['no_antrean']) ?></small><br>
                                        <small class="text-muted">PO: <?= htmlspecialchars($row['no_po'] ?: '-') ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge-plat"><?= htmlspecialchars($row['nopol']) ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <strong><?= htmlspecialchars($row['supplier'] ?: '-') ?></strong><br>
                                        <small class="text-muted">Sopir: <?= htmlspecialchars($row['nama_sopir'] ?: '-') ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <?= htmlspecialchars($row['jenis_muatan'] ?: '-') ?><br>
                                        <small class="text-muted font-weight-bold"><?= htmlspecialchars($row['jumlah_muatan'] ?: '-') ?></small>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (!empty($row['waktu_kedatangan'])) : ?>
                                            <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Tiba:</span> <?= date('H:i d/m', strtotime($row['waktu_kedatangan'])) ?>
                                        <?php else : ?>
                                            <span class="text-muted">Jadwal:</span> <?= $row['tgl_jadwal'] ? date('H:i d/m', strtotime($row['tgl_jadwal'])) : '-' ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if (!empty($row['pintu'])) : ?>
                                            <span class="badge badge-dark badge-pintu-lg">
                                                <i class="fas fa-door-open mr-1"></i>Pintu <?= $lbl_pintu ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="text-muted small">- Belum di-assign -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge <?= $st_badge ?> py-1 px-2"><?= $st_label ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <small><?= htmlspecialchars($row['nm_checker'] ?: '-') ?></small>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if ($row['status'] === 'TERJADWAL') : ?>
                                            <!-- Tombol konfirmasi Pos Security untuk truk yang baru tiba -->
                                            <button class="btn btn-outline-success btn-xs btn-block btn-konfirm-tiba"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-nopol="<?= htmlspecialchars($row['nopol']) ?>"
                                                    data-sopir="<?= htmlspecialchars($row['nama_sopir']) ?>"
                                                    title="Pos Security: Klik jika truk fisik telah tiba di gerbang">
                                                <i class="fas fa-check mr-1"></i> Truk Tiba di Pos
                                            </button>
                                        <?php elseif ($row['status'] === 'SUDAH_DATANG' && ($is_mck || $role === 'MANAGERWH')) : ?>
                                            <button class="btn btn-primary btn-xs btn-block btn-buka-assign"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-nopol="<?= htmlspecialchars($row['nopol']) ?>"
                                                    data-sopir="<?= htmlspecialchars($row['nama_sopir']) ?>"
                                                    data-supplier="<?= htmlspecialchars($row['supplier']) ?>"
                                                    data-muatan="<?= htmlspecialchars($row['jenis_muatan']) ?>"
                                                    data-pintu="<?= $row['pintu'] ?? '' ?>">
                                                <i class="fas fa-door-open mr-1"></i> Tentukan Pintu
                                            </button>
                                        <?php elseif ($row['status'] === 'MENUNGGU_BONGKAR' && ($is_mck || $role === 'MANAGERWH')) : ?>
                                            <button class="btn btn-outline-warning btn-xs btn-block btn-buka-assign"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-nopol="<?= htmlspecialchars($row['nopol']) ?>"
                                                    data-sopir="<?= htmlspecialchars($row['nama_sopir']) ?>"
                                                    data-supplier="<?= htmlspecialchars($row['supplier']) ?>"
                                                    data-muatan="<?= htmlspecialchars($row['jenis_muatan']) ?>"
                                                    data-pintu="<?= $row['pintu'] ?? '' ?>">
                                                <i class="fas fa-edit mr-1"></i> Ganti Pintu
                                            </button>
                                        <?php else : ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
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
    </div>

    <!-- ============================================================
        MODAL 1: TENTUKAN PINTU BONGKARAN (MANAGER CHECKER)
    ============================================================ -->
    <div class="modal fade" id="modalAssignPintu" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-door-open mr-2"></i> Tentukan Pintu Masuk Truk
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <!-- Ringkasan Info Truk -->
                    <div class="p-3 mb-3 rounded" style="background:#f8fafc; border:1px solid #e2e8f0;">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block">Nomor Polisi</small>
                                <span class="badge-plat" id="assign_modal_nopol">-</span>
                            </div>
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block">Nama Sopir</small>
                                <strong id="assign_modal_sopir">-</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Supplier / Vendor</small>
                                <span id="assign_modal_supplier">-</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Muatan Barang</small>
                                <span id="assign_modal_muatan" class="font-weight-bold text-dark">-</span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="assign_id_truk">

                    <div class="form-group">
                        <label class="font-weight-bold text-dark">
                            Pilih Pintu Masuk Bongkaran <span class="text-danger">*</span>
                        </label>
                        <select id="assign_pintu_select" class="form-control form-control-lg font-weight-bold" style="font-size:15px;">
                            <option value="">-- Pilih Salah Satu Pintu --</option>
                            <?php foreach ($status_pintu as $no_p => $p) : 
                                $status_ket = ($p['status'] === 'KOSONG') ? '🟢 KOSONG (Siap Digunakan)' : ('🔴 ' . $p['status'] . ' - ' . $p['info']);
                            ?>
                            <option value="<?= $p['pintu'] ?>">
                                Pintu <?= $p['label'] ?> &mdash; <?= $status_ket ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            Pilihlah pintu yang berstatus <strong>KOSONG</strong> agar alur bongkaran tidak terhambat.
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-dark">Catatan / Instruksi Khusus (Opsional)</label>
                        <textarea id="assign_catatan" class="form-control" rows="2" placeholder="Contoh: Utamakan bongkar pallet dulu, penataan di rak blok B..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary font-weight-bold" id="btnSimpanAssignPintu">
                        <i class="fas fa-check mr-1"></i> Simpan & Alokasikan Pintu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
        MODAL 2: KONFIRMASI KEDATANGAN TRUK OLEH POS SECURITY
    ============================================================ -->
    <div class="modal fade" id="modalKonfirmasiSecurity" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-user-shield mr-2"></i> Pos Security &mdash; Konfirmasi Truk Tiba
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2" style="font-size:13px;">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pastikan truk fisik dan surat jalan telah diperiksa di gerbang sebelum mengonfirmasi kedatangan.
                    </div>

                    <input type="hidden" id="sec_id_truk">

                    <div class="form-group">
                        <label class="font-weight-bold">Nomor Polisi Truk</label>
                        <input type="text" id="sec_nopol" class="form-control font-weight-bold" readonly>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Petugas Security</label>
                        <input type="text" id="sec_petugas" class="form-control" value="<?= htmlspecialchars($this->session->userdata('nama') ?: 'Security Pos') ?>" placeholder="Nama petugas yang bertugas">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Catatan Pemeriksaan Pos Security</label>
                        <textarea id="sec_catatan" class="form-control" rows="2" placeholder="Contoh: Surat jalan lengkap, segel utuh, tiba tepat waktu..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success font-weight-bold" id="btnSimpanConfirmDatang">
                        <i class="fas fa-check-circle mr-1"></i> Konfirmasi Truk Sudah Datang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
        MODAL 3: INPUT / TAMBAH JADWAL TRUK (PURCHASING / SIMULASI)
    ============================================================ -->
    <div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-plus-circle mr-2 text-warning"></i> Input Jadwal Kedatangan Truk Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Nomor Polisi Truk <span class="text-danger">*</span></label>
                            <input type="text" id="tbh_nopol" class="form-control text-uppercase" placeholder="Contoh: B 1234 CD">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Supplier / Vendor</label>
                            <input type="text" id="tbh_supplier" class="form-control" placeholder="Nama Supplier">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Nama Sopir</label>
                            <input type="text" id="tbh_sopir" class="form-control" placeholder="Nama Supir">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">No. Telp / HP Sopir</label>
                            <input type="text" id="tbh_telp" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Nama Ekspedisi / Armada</label>
                            <input type="text" id="tbh_ekspedisi" class="form-control" placeholder="Contoh: Karisma Express">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Komoditas / Jenis Muatan <span class="text-danger">*</span></label>
                            <input type="text" id="tbh_muatan" class="form-control" placeholder="Contoh: Jagung Pipil / Beras">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Jumlah / Tonase Muatan</label>
                            <input type="text" id="tbh_jumlah" class="form-control" placeholder="Contoh: 20 Ton / 400 Sak">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">No. PO (Purchasing)</label>
                            <input type="text" id="tbh_po" class="form-control" placeholder="Contoh: PO-PUR/2026/10/...">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">No. Surat Jalan</label>
                            <input type="text" id="tbh_sj" class="form-control" placeholder="No Surat Jalan">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">Status Awal</label>
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="tbh_is_datang" checked>
                                <label class="custom-control-label font-weight-normal" for="tbh_is_datang">
                                    Truk sudah berada di lokasi gerbang (Langsung Tandai Telah Datang)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success font-weight-bold" id="btnSimpanJadwalTruk">
                        <i class="fas fa-save mr-1"></i> Simpan Data Truk
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- JAVASCRIPT LOGIC -->
<script>
$(document).ready(function() {
    function ajaxPost(url, data, callback) {
        $.ajax({
            url: '<?= base_url() ?>' + url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: callback,
            error: function(xhr, status, error) {
                alert('Terjadi kesalahan jaringan atau server: ' + error);
            }
        });
    }

    // Buka Modal Alokasi Pintu (Manager CK)
    $(document).on('click', '.btn-buka-assign', function() {
        var id       = $(this).data('id');
        var nopol    = $(this).data('nopol');
        var sopir    = $(this).data('sopir');
        var supplier = $(this).data('supplier');
        var muatan   = $(this).data('muatan');
        var pintu    = $(this).data('pintu');

        $('#assign_id_truk').val(id);
        $('#assign_modal_nopol').text(nopol);
        $('#assign_modal_sopir').text(sopir || '-');
        $('#assign_modal_supplier').text(supplier || '-');
        $('#assign_modal_muatan').text(muatan || '-');
        $('#assign_pintu_select').val(pintu || '');
        $('#assign_catatan').val('');

        $('#modalAssignPintu').modal('show');
    });

    // Simpan Alokasi Pintu oleh Manager CK
    $('#btnSimpanAssignPintu').on('click', function() {
        var id_truk = $('#assign_id_truk').val();
        var pintu   = $('#assign_pintu_select').val();
        var catatan = $('#assign_catatan').val();

        if (!pintu) {
            alert('Silakan pilih salah satu pintu bongkaran!');
            return;
        }

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        ajaxPost('checker/assign_pintu_truk', {
            id_truk: id_truk,
            pintu: pintu,
            catatan_managerck: catatan
        }, function(res) {
            alert(res.msg);
            if (res.status) {
                location.reload();
            } else {
                $('#btnSimpanAssignPintu').prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Simpan & Alokasikan Pintu');
            }
        });
    });

    // Buka Modal Konfirmasi Pos Security
    $(document).on('click', '.btn-konfirm-tiba', function() {
        var id    = $(this).data('id');
        var nopol = $(this).data('nopol');

        $('#sec_id_truk').val(id);
        $('#sec_nopol').val(nopol);
        $('#sec_catatan').val('');
        $('#modalKonfirmasiSecurity').modal('show');
    });

    // Simpan Konfirmasi Kedatangan oleh Pos Security
    $('#btnSimpanConfirmDatang').on('click', function() {
        var id_truk = $('#sec_id_truk').val();
        var petugas = $('#sec_petugas').val();
        var catatan = $('#sec_catatan').val();

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        ajaxPost('checker/confirm_truk_datang', {
            id_truk: id_truk,
            petugas_security: petugas,
            catatan_security: catatan
        }, function(res) {
            alert(res.msg);
            if (res.status) {
                location.reload();
            } else {
                $('#btnSimpanConfirmDatang').prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Konfirmasi Truk Sudah Datang');
            }
        });
    });

    // Simpan Tambah Jadwal Truk Baru
    $('#btnSimpanJadwalTruk').on('click', function() {
        var nopol = $('#tbh_nopol').val();
        var muatan = $('#tbh_muatan').val();

        if (!nopol) {
            alert('Nomor polisi truk wajib diisi!');
            return;
        }
        if (!muatan) {
            alert('Jenis muatan barang wajib diisi!');
            return;
        }

        var postData = {
            nopol: nopol,
            supplier: $('#tbh_supplier').val(),
            nama_sopir: $('#tbh_sopir').val(),
            no_telp_sopir: $('#tbh_telp').val(),
            ekspedisi: $('#tbh_ekspedisi').val(),
            jenis_muatan: muatan,
            jumlah_muatan: $('#tbh_jumlah').val(),
            no_po: $('#tbh_po').val(),
            no_sj: $('#tbh_sj').val(),
            is_langsung_datang: $('#tbh_is_datang').is(':checked') ? 1 : 0
        };

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        ajaxPost('checker/store_truk_jadwal', postData, function(res) {
            alert(res.msg);
            if (res.status) {
                location.reload();
            } else {
                $('#btnSimpanJadwalTruk').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Data Truk');
            }
        });
    });
});
</script>
