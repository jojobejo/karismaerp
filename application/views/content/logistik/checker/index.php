<!-- view/content/logistik/checker/index.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <section class="content">

                <!-- Tombol navigasi atas -->
                <div class="row mb-3">
                    <?php if ($role === 'MANAGERWH') : ?>
                    <div class="col-auto">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#modalBuatBongkaran">
                            <i class="fas fa-plus"></i> Buat Bongkaran
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('checker/arsip') ?>" class="btn btn-secondary">
                            <i class="fas fa-archive"></i> Lihat Arsip
                        </a>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-danger" id="btnArchiveAll">
                            <i class="fas fa-box-archive mr-1"></i> Archive Aktivitas Hari Ini
                        </button>
                    </div>
                    <?php endif; ?>
                    <?php if (in_array($role, ['SALESCK','DIREKTURCK'])) : ?>
                    <div class="col-auto">
                        <a href="<?= base_url('checker/arsip') ?>" class="btn btn-secondary">
                            <i class="fas fa-archive mr-1"></i> Lihat Arsip
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ($role === 'ADMLOG') : ?>
                    <div class="col-auto">
                        <button class="btn btn-info" data-toggle="modal" data-target="#modalTambahKK">
                            <i class="fas fa-plus"></i> Tambah Loading KK
                        </button>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-warning" data-toggle="modal" data-target="#modalTambahLK">
                            <i class="fas fa-plus"></i> Tambah Loading LK
                        </button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- ============================================================
                     PANEL AKTIVITAS WAREHOUSE (ringkasan)
                ============================================================ -->
                <div class="card card-dark mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-broadcast-tower mr-2"></i>
                            Aktivitas Warehouse — <?= date('j/n/Y') ?>
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="background:#1a1a2e; color:#eee;">
                        <?php
                        // "Jalan X pintu" = bongkaran yang status PROSES (sudah di-start checker)
                        $aktif   = array_filter($bongkaran, fn($r) => $r['status'] === 'PROSES');
                        $selesai = array_filter($bongkaran, fn($r) => $r['status'] === 'DONE');
                        ?>
                        <div class="row">
                            <!-- Kolom Bongkaran -->
                            <div class="col-md-4">
                                <p class="mb-1" style="color:#f0c040; font-weight:bold;">
                                    <i class="fas fa-dolly mr-1"></i> Bongkaran
                                </p>
                                <?php $no = 1; foreach ($bongkaran as $b) : ?>
                                <p class="mb-1 ml-2" style="font-size:13px;">
                                    <?= $no++ ?>. <?= htmlspecialchars($b['keterangan'] ?? $b['kode_bongkar']) ?> :
                                    <?php if ($b['status'] === 'DONE') : ?>
                                        <span style="color:#4ade80;">done ✅</span>
                                    <?php elseif ($b['status'] === 'PROSES') : ?>
                                        <span style="color:#fbbf24;">proses bongkar ▶️</span>
                                    <?php elseif ($b['status'] === 'MENUNGGU') : ?>
                                        <span style="color:#94a3b8;">menunggu ⏳</span>
                                    <?php else : ?>
                                        <span style="color:#60a5fa;"><?= str_replace('_',' ',$b['status']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($b['nm_checker'])) : ?>
                                        <small style="color:#94a3b8;"> — <?= htmlspecialchars($b['nm_checker']) ?></small>
                                    <?php endif; ?>
                                </p>
                                <?php endforeach; ?>
                                <?php if (empty($bongkaran)) : ?>
                                    <p class="ml-2" style="color:#94a3b8; font-size:13px;">Tidak ada data</p>
                                <?php endif; ?>
                            </div>

                            <!-- Kolom Loading LK -->
                            <div class="col-md-4">
                                <p class="mb-1" style="color:#60a5fa; font-weight:bold;">
                                    <i class="fas fa-truck mr-1"></i> Loading LK
                                </p>
                                <?php $no = 1; foreach ($list_lk as $lk) : ?>
                                <p class="mb-1 ml-2" style="font-size:13px;">
                                    <?= $no++ ?>. <?= htmlspecialchars($lk['keterangan']) ?> :
                                    <?php
                                    $lbl = [
                                        'MENUNGGU'       => ['color'=>'#94a3b8','teks'=>'menunggu ⏳'],
                                        'PROSES_LOADING' => ['color'=>'#fbbf24','teks'=>'proses loading ▶️'],
                                        'CETAK_DO'       => ['color'=>'#a78bfa','teks'=>'sudah cetak DO 🖨️'],
                                        'DONE'           => ['color'=>'#4ade80','teks'=>'done ✅'],
                                    ][$lk['status']] ?? ['color'=>'#eee','teks'=>$lk['status']];
                                    ?>
                                    <span style="color:<?= $lbl['color'] ?>;"><?= $lbl['teks'] ?></span>
                                </p>
                                <?php endforeach; ?>
                                <?php if (empty($list_lk)) : ?>
                                    <p class="ml-2" style="color:#94a3b8; font-size:13px;">Tidak ada data</p>
                                <?php endif; ?>
                            </div>

                            <!-- Kolom Loading KK -->
                            <div class="col-md-4">
                                <p class="mb-1" style="color:#34d399; font-weight:bold;">
                                    <i class="fas fa-truck-loading mr-1"></i> Loading KK
                                </p>
                                <?php $no = 1; foreach ($list_kk as $kk) : ?>
                                <p class="mb-1 ml-2" style="font-size:13px;">
                                    <?= $no++ ?>. <?= htmlspecialchars($kk['keterangan']) ?> :
                                    <?php
                                    $lbl = [
                                        'MENUNGGU'       => ['color'=>'#94a3b8','teks'=>'menunggu ⏳'],
                                        'PROSES_LOADING' => ['color'=>'#fbbf24','teks'=>'proses loading ▶️'],
                                        'CETAK_DO'       => ['color'=>'#a78bfa','teks'=>'sudah cetak DO 🖨️'],
                                        'DONE'           => ['color'=>'#4ade80','teks'=>'done ✅'],
                                    ][$kk['status']] ?? ['color'=>'#eee','teks'=>$kk['status']];
                                    ?>
                                    <span style="color:<?= $lbl['color'] ?>;"><?= $lbl['teks'] ?></span>
                                </p>
                                <?php endforeach; ?>
                                <?php if (empty($list_kk)) : ?>
                                    <p class="ml-2" style="color:#94a3b8; font-size:13px;">Tidak ada data</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Footer ringkasan -->
                        <hr style="border-color:#333;">
                        <p class="mb-0 text-center" style="color:#f0c040; font-size:13px; font-weight:bold;">
                            🏭 Aktivitas WH jalan <?= count($aktif) ?> pintu
                        </p>
                    </div>
                </div>

                <!-- ============================================================
                     TABEL BONGKARAN
                ============================================================ -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title"><i class="fas fa-dolly mr-2"></i> Data Bongkaran</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-sm" id="tabelBongkaran">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Tgl Dibuat</th>
                                    <th>Kode</th>
                                    <th>Keterangan</th>
                                    <th>Checker</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Progres</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                    <?php if (in_array($role, ['CHECKER','MANAGERWH','ADMLOG'])) : ?>
                                    <th class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no = 1; foreach ($bongkaran as $b) :
                                $is_done   = ($b['status'] === 'DONE');
                                $is_taken  = !empty($b['nik_checker']);
                                $is_my_job = ($b['nik_checker'] === $nik);
                                $progres   = (int)($b['progres'] ?? 0);
                                $badge = ['MENUNGGU'=>'badge-secondary','PROSES'=>'badge-warning',
                                          'PENYIAPAN_BARANG'=>'badge-info','CETAK_DO'=>'badge-primary',
                                          'DONE'=>'badge-success'][$b['status']] ?? 'badge-secondary';
                            ?>
                            <tr class="<?= $is_done ? 'table-success' : '' ?>">
                                <td><?= $no++ ?></td>
                                <td><small><?= date('d/m/Y', strtotime($b['created_at'])) ?></small></td>
                                <td><small><?= htmlspecialchars($b['kode_bongkar']) ?></small></td>
                                <td><?= htmlspecialchars($b['keterangan'] ?? '-') ?></td>
                                <td><?= $is_taken ? htmlspecialchars($b['nm_checker']) : '<span class="text-muted">-</span>' ?></td>
                                <td><small><?= $is_taken ? ($b['waktu_mulai'] ?? '-') : '-' ?></small></td>
                                <td><small><?= ($b['status_checker'] === 'DONE') ? ($b['waktu_selesai'] ?? '-') : '-' ?></small></td>
                                <td style="min-width:110px;">
                                    <div class="progress" style="height:16px;">
                                        <div class="progress-bar <?= $progres==100?'bg-success':'bg-warning' ?>"
                                             style="width:<?= $progres ?>%"><?= $progres ?>%</div>
                                    </div>
                                </td>
                                <td class="text-center" style="min-width:100px;">
                                    <?php
                                    if ($is_taken && !empty($b['waktu_mulai'])) {
                                        $mulai = strtotime($b['waktu_mulai']);
                                        $akhir = (!empty($b['waktu_selesai']) && $b['status_checker'] === 'DONE')
                                                 ? strtotime($b['waktu_selesai'])
                                                 : time();
                                        $selisih = $akhir - $mulai;
                                        if ($selisih > 0) {
                                            $jam   = floor($selisih / 3600);
                                            $menit = floor(($selisih % 3600) / 60);
                                            if ($jam > 0) {
                                                echo '<small>' . $jam . ' jam ' . $menit . ' menit</small>';
                                            } else {
                                                echo '<small>' . $menit . ' menit</small>';
                                            }
                                            if ($b['status_checker'] !== 'DONE') {
                                                echo ' <span class="badge badge-warning" style="font-size:9px;">live</span>';
                                            }
                                        } else {
                                            echo '<small class="text-muted">-</small>';
                                        }
                                    } else {
                                        echo '<small class="text-muted">-</small>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($role === 'ADMLOG' && !$is_done) : ?>
                                        <select class="form-control form-control-sm select-status-bongkaran"
                                                data-id="<?= $b['id'] ?>" style="min-width:130px;">
                                            <?php foreach (['MENUNGGU','PROSES','PENYIAPAN_BARANG','CETAK_DO','DONE'] as $s) : ?>
                                                <option value="<?= $s ?>" <?= $b['status']===$s?'selected':'' ?>>
                                                    <?= str_replace('_',' ',$s) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else : ?>
                                        <span class="badge <?= $badge ?>"><?= str_replace('_',' ',$b['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if (in_array($role, ['CHECKER','MANAGERWH','ADMLOG'])) : ?>
                                <td class="text-center" style="min-width:180px;">
                                    <?php if ($role === 'CHECKER') : ?>
                                        <?php if (!$is_taken && $my_active_id === null) : ?>
                                            <!-- Belum ada yang ambil + checker ini belum punya job aktif -->
                                            <button class="btn btn-sm btn-success btn-start" data-id="<?= $b['id'] ?>">
                                                <i class="fas fa-play"></i> Start
                                            </button>
                                        <?php elseif (!$is_taken && $my_active_id !== null) : ?>
                                            <!-- Belum ada yang ambil, tapi checker ini sudah punya job lain -->
                                            <span class="badge badge-secondary">Selesaikan job Anda dulu</span>
                                        <?php elseif ($is_my_job && $b['status_checker'] === 'PROSES') : ?>
                                            <!-- Job milik checker ini sendiri -->
                                            <div class="d-flex align-items-center">
                                                <select class="form-control form-control-sm mr-1 select-progres" data-id="<?= $b['id'] ?>" style="width:70px;">
                                                    <?php foreach ([0,10,20,30,40,50,60,70,80,90] as $p) : ?>
                                                        <option value="<?= $p ?>" <?= $progres==$p?'selected':'' ?>><?= $p ?>%</option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="btn btn-sm btn-warning btn-update-progres mr-1" data-id="<?= $b['id'] ?>">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary btn-done" data-id="<?= $b['id'] ?>">
                                                    <i class="fas fa-check"></i> Done
                                                </button>
                                            </div>
                                        <?php else : ?>
                                            <!-- Sudah diambil checker lain -->
                                            <span class="text-muted small">
                                                <i class="fas fa-lock mr-1"></i>
                                                <?= htmlspecialchars($b['nm_checker'] ?? 'checker lain') ?>
                                            </span>
                                        <?php endif; ?>

                                    <?php elseif ($role === 'ADMLOG') : ?>
                                        <button class="btn btn-sm btn-info btn-simpan-status-bongkaran" data-id="<?= $b['id'] ?>">
                                            <i class="fas fa-save"></i> Simpan
                                        </button>

                                    <?php elseif ($role === 'MANAGERWH') : ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($bongkaran)) : ?>
                                <tr><td colspan="9" class="text-center text-muted">Tidak ada data bongkaran</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============================================================
                     TABEL LOADING LK
                ============================================================ -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-truck mr-2"></i> Loading LK</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-sm" id="tabelLK">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Keterangan</th>
                                    <th>Tgl Input</th>
                                    <th>Status</th>
                                    <?php if (in_array($role, ['ADMLOG','MANAGERWH'])) : ?>
                                    <th class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no = 1; foreach ($list_lk as $lk) :
                                $badge_lk = ['MENUNGGU'=>'badge-secondary','PROSES_LOADING'=>'badge-warning',
                                             'CETAK_DO'=>'badge-primary','DONE'=>'badge-success'][$lk['status']] ?? 'badge-secondary';
                            ?>
                            <tr class="<?= $lk['status']==='DONE'?'table-success':'' ?>">
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($lk['keterangan']) ?></td>
                                <td><small><?= $lk['tgl'] ?></small></td>
                                <td class="text-center">
                                    <?php if ($role === 'ADMLOG' && $lk['status'] !== 'DONE') : ?>
                                        <select class="form-control form-control-sm select-status-lk" data-id="<?= $lk['id'] ?>" style="min-width:130px;">
                                            <?php foreach (['MENUNGGU','PROSES_LOADING','CETAK_DO','DONE'] as $s) : ?>
                                                <option value="<?= $s ?>" <?= $lk['status']===$s?'selected':'' ?>><?= str_replace('_',' ',$s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else : ?>
                                        <span class="badge <?= $badge_lk ?>"><?= str_replace('_',' ',$lk['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if (in_array($role, ['ADMLOG','MANAGERWH'])) : ?>
                                <td class="text-center">
                                    <?php if (in_array($role, ['SALES','DIREKTUR'])) : ?>
                    <div class="col-auto">
                        <a href="<?= base_url('checker/arsip') ?>" class="btn btn-secondary">
                            <i class="fas fa-archive mr-1"></i> Lihat Arsip
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ($role === 'ADMLOG') : ?>
                                        <button class="btn btn-sm btn-info btn-simpan-lk" data-id="<?= $lk['id'] ?>">
                                            <i class="fas fa-save"></i> Simpan
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($list_lk)) : ?>
                                <tr><td colspan="5" class="text-center text-muted">Tidak ada data Loading LK</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ============================================================
                     TABEL LOADING KK
                ============================================================ -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-truck-loading mr-2"></i> Loading KK</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-sm" id="tabelKK">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Keterangan</th>
                                    <th>Tgl Input</th>
                                    <th>Status</th>
                                    <?php if (in_array($role, ['ADMLOG','MANAGERWH'])) : ?>
                                    <th class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no = 1; foreach ($list_kk as $kk) :
                                $badge_kk = ['MENUNGGU'=>'badge-secondary','PROSES_LOADING'=>'badge-warning',
                                             'CETAK_DO'=>'badge-primary','DONE'=>'badge-success'][$kk['status']] ?? 'badge-secondary';
                            ?>
                            <tr class="<?= $kk['status']==='DONE'?'table-success':'' ?>">
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($kk['keterangan']) ?></td>
                                <td><small><?= $kk['tgl'] ?></small></td>
                                <td class="text-center">
                                    <?php if ($role === 'ADMLOG' && $kk['status'] !== 'DONE') : ?>
                                        <select class="form-control form-control-sm select-status-kk" data-id="<?= $kk['id'] ?>" style="min-width:130px;">
                                            <?php foreach (['MENUNGGU','PROSES_LOADING','CETAK_DO','DONE'] as $s) : ?>
                                                <option value="<?= $s ?>" <?= $kk['status']===$s?'selected':'' ?>><?= str_replace('_',' ',$s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php else : ?>
                                        <span class="badge <?= $badge_kk ?>"><?= str_replace('_',' ',$kk['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <?php if (in_array($role, ['ADMLOG','MANAGERWH'])) : ?>
                                <td class="text-center">
                                    <?php if (in_array($role, ['SALES','DIREKTUR'])) : ?>
                    <div class="col-auto">
                        <a href="<?= base_url('checker/arsip') ?>" class="btn btn-secondary">
                            <i class="fas fa-archive mr-1"></i> Lihat Arsip
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ($role === 'ADMLOG') : ?>
                                        <button class="btn btn-sm btn-info btn-simpan-kk" data-id="<?= $kk['id'] ?>">
                                            <i class="fas fa-save"></i> Simpan
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($list_kk)) : ?>
                                <tr><td colspan="5" class="text-center text-muted">Tidak ada data Loading KK</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
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

<!-- ================================================================ MODALS ================================================================ -->

<?php if ($role === 'MANAGERWH') : ?>
<!-- Modal Buat Bongkaran -->
<div class="modal fade" id="modalBuatBongkaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus mr-2"></i> Buat Bongkaran Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">Kode</label>
                    <input type="text" class="form-control" value="<?= $kode_baru ?>" readonly>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Keterangan <span class="text-danger">*</span></label>
                    <input type="text" id="inputKeterangan" class="form-control" placeholder="Contoh: NK 212 BTGT">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpanBongkaran">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($role === 'ADMLOG') : ?>
<!-- Modal Tambah KK -->
<div class="modal fade" id="modalTambahKK" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus mr-2"></i> Tambah Loading KK</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">Keterangan <span class="text-danger">*</span></label>
                    <input type="text" id="inputKeteranganKK" class="form-control" placeholder="Contoh: JBR">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnSimpanKK">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Tambah LK -->
<div class="modal fade" id="modalTambahLK" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-plus mr-2"></i> Tambah Loading LK</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">Keterangan <span class="text-danger">*</span></label>
                    <input type="text" id="inputKeteranganLK" class="form-control" placeholder="Contoh: P-2">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="btnSimpanLK">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ================================================================ SCRIPT ================================================================ -->
<script>
var BASE = '<?= base_url() ?>';

function ajaxPost(url, data, cb) {
    $.post(BASE + url, data, cb, 'json')
     .fail(function(xhr){ alert('Error: ' + xhr.responseText.substring(0,200)); });
}

$(document).ready(function () {

    // Auto-refresh panel aktivitas setiap 30 detik
    setTimeout(function(){ location.reload(); }, 30000);

    // ---- MANAGER WH: Buat Bongkaran ----
    $('#btnSimpanBongkaran').on('click', function () {
        var ket = $('#inputKeterangan').val().trim();
        if (!ket) { alert('Keterangan wajib diisi'); return; }
        ajaxPost('checker/store', { keterangan: ket }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        });
    });

    // ---- CHECKER: Start ----
    $(document).on('click', '.btn-start', function () {
        if (!confirm('Ambil pekerjaan ini?')) return;
        ajaxPost('checker/start', { id: $(this).data('id') }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        });
    });

    // ---- CHECKER: Update Progres ----
    $(document).on('click', '.btn-update-progres', function () {
        var id      = $(this).data('id');
        var progres = $(this).closest('div').find('.select-progres').val();
        ajaxPost('checker/update_progres', { id: id, progres: progres }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        });
    });

    // ---- CHECKER: Done ----
    $(document).on('click', '.btn-done', function () {
        if (!confirm('Tandai sebagai SELESAI?')) return;
        ajaxPost('checker/done', { id: $(this).data('id') }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        });
    });

    // ---- ADMLOG: Simpan status bongkaran ----
    $(document).on('click', '.btn-simpan-status-bongkaran', function () {
        var id     = $(this).data('id');
        var status = $(this).closest('tr').find('.select-status-bongkaran').val();
        ajaxPost('checker/update_status_bongkaran', { id: id, status: status }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        });
    });

    // ---- ADMLOG: Simpan status LK ----
    $(document).on('click', '.btn-simpan-lk', function () {
        var id     = $(this).data('id');
        var status = $(this).closest('tr').find('.select-status-lk').val();
        ajaxPost('checker/update_lk', { id: id, status: status }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        });
    });

    // ---- ADMLOG: Simpan status KK ----
    $(document).on('click', '.btn-simpan-kk', function () {
        var id     = $(this).data('id');
        var status = $(this).closest('tr').find('.select-status-kk').val();
        ajaxPost('checker/update_kk', { id: id, status: status }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        });
    });

    // ---- ADMLOG: Tambah KK ----
    $('#btnSimpanKK').on('click', function () {
        var ket = $('#inputKeteranganKK').val().trim();
        if (!ket) { alert('Keterangan wajib diisi'); return; }
        ajaxPost('checker/store_kk', { keterangan: ket }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        });
    });

    // ---- ADMLOG: Tambah LK ----
    $('#btnSimpanLK').on('click', function () {
        var ket = $('#inputKeteranganLK').val().trim();
        if (!ket) { alert('Keterangan wajib diisi'); return; }
        ajaxPost('checker/store_lk', { keterangan: ket }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        });
    });

    // ---- MANAGER WH: Archive SEMUA aktivitas hari ini ----
    $('#btnArchiveAll').on('click', function () {
        Swal.fire({
            title: 'Archive Aktivitas Hari Ini?',
            html: 'Semua aktivitas yang sudah <b>DONE</b> akan diarsipkan,<br>tidak terbatas hari ini saja.<br>' +
                  '<small class="text-muted">Data yang belum DONE tidak akan terpengaruh.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-box-archive mr-1"></i> Ya, Archive Sekarang',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            ajaxPost('checker/archive_all_today', {}, function (res) {
                Swal.fire({
                    icon: res.status ? 'success' : 'error',
                    title: res.status ? 'Berhasil!' : 'Gagal',
                    text: res.msg,
                    timer: res.status ? 2500 : 0,
                    showConfirmButton: !res.status
                }).then(function () {
                    if (res.status) location.reload();
                });
            });
        });
    });

});
</script>
</body>