<!-- view/content/logistik/bongkaran/index.php -->

<!-- STYLE TAMBAHAN -->
<style>
/* Warna thead tabel LK (biru) sesuai card-header bg-primary */
#tabelLK thead.thead-dark th {
    background: #1565c0 !important; color: #fff !important; border-color: #0d47a1 !important;
}
/* Warna thead tabel KK (hijau) sesuai card-header bg-success */
#tabelKK thead.thead-dark th {
    background: #1b5e20 !important; color: #fff !important; border-color: #145214 !important;
}
tr.separator-label td {
    font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
    padding:4px 12px !important;
    border-top:2px dashed #aaa !important; border-bottom:2px dashed #aaa !important;
}
tr.separator-label.sep-active  td { background:#fffde7; border-color:#f9a825 !important; color:#e65100; }
tr.separator-label.sep-pending td { background:#f3f3f3; border-color:#bbb    !important; color:#555;    }
tr.separator-label.sep-done    td { background:#e8f5e9; border-color:#43a047 !important; color:#1b5e20; }
tr.row-proses  { background:#fffde7 !important; }
tr.row-pending { background:#fafafa !important; }
.aksi-checker, .aksi-managerck {
    display:flex; flex-wrap:wrap; gap:6px; align-items:center; justify-content:center;
}
.aksi-checker .form-control, .aksi-managerck .form-control { width:70px !important; flex-shrink:0; }
.aksi-checker .btn, .aksi-managerck .btn { min-width:82px; white-space:nowrap; }
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
            <section class="content">

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
                    <?php if (in_array($role, ['SALES','DIREKTUR'])) : ?>
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
                    <div class="col-auto">
                        <button class="btn btn-danger" id="btnArchiveAll">
                            <i class="fas fa-box-archive mr-1"></i> Archive Aktivitas Hari Ini
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('checker/arsip') ?>" class="btn btn-secondary">
                            <i class="fas fa-archive"></i> Lihat Arsip
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- ============================================================
                    PANEL AKTIVITAS WAREHOUSE (ringkasan)
                ============================================================ -->
                <div class="card mb-3" style="border:none; box-shadow: 0 2px 12px rgba(0,0,0,0.10);">
                    <div class="card-header text-white" style="background: linear-gradient(135deg,#1a237e 0%,#1565c0 100%); border-radius:8px 8px 0 0;">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-broadcast-tower mr-2"></i>
                            Aktivitas Warehouse — <?= date('j/n/Y') ?>
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-3" style="background:#fff;">
                        <?php
                        $aktif   = array_filter($bongkaran, fn($r) => $r['status'] === 'PROSES');
                        $selesai = array_filter($bongkaran, fn($r) => $r['status'] === 'DONE');
                        ?>
                        <div class="row">

                            <!-- ── Kolom Bongkaran ── -->
                            <div class="col-md-4 mb-2">
                                <div class="p-2 rounded h-100" style="background:#fff8e1; border-left:4px solid #f9a825;">
                                    <p class="mb-2" style="color:#e65100; font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:.04em;">
                                        <i class="fas fa-dolly mr-1"></i> Bongkaran
                                    </p>
                                    <?php if (empty($bongkaran)) : ?>
                                        <p class="mb-0" style="color:#aaa; font-size:12px; font-style:italic;">Tidak ada data</p>
                                    <?php endif; ?>
                                    <?php $no = 1; foreach ($bongkaran as $b) :
                                        if ($b['status'] === 'DONE') {
                                            $dot = '#22c55e'; $label = 'done ✅';
                                        } elseif ($b['status'] === 'PROSES') {
                                            $dot = '#f59e0b'; $label = 'proses ▶️';
                                        } elseif ($b['status'] === 'MENUNGGU') {
                                            $dot = '#94a3b8'; $label = 'menunggu ⏳';
                                        } else {
                                            $dot = '#60a5fa'; $label = str_replace('_',' ',$b['status']);
                                        }
                                        $is_last_b = ($no === count($bongkaran));
                                    ?>
                                    <div class="d-flex align-items-center py-1"
                                        style="font-size:12px; <?= !$is_last_b ? 'border-bottom:1px solid #fde68a;' : '' ?>">
                                        <span style="width:8px;height:8px;border-radius:50%;background:<?= $dot ?>;flex-shrink:0;" class="mr-2"></span>
                                        <span class="font-weight-bold mr-1" style="color:#333; flex-shrink:0;"><?= $no++ ?>.</span>
                                        <span style="color:#444; flex:1; min-width:0;" class="text-truncate">
                                            <?= htmlspecialchars($b['keterangan'] ?? $b['kode_bongkar']) ?>
                                        </span>
                                        <?php if (!empty($b['nm_checker'])) : ?>
                                            <span style="font-size:11px; color:#888; flex-shrink:0; margin:0 4px;">
                                                <i class="fas fa-user" style="font-size:10px;"></i> <?= htmlspecialchars($b['nm_checker']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="font-weight-bold" style="color:<?= $dot ?>; flex-shrink:0;"><?= $label ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- ── Kolom Loading LK ── -->
                            <div class="col-md-4 mb-2">
                                <div class="p-2 rounded h-100" style="background:#e3f2fd; border-left:4px solid #1565c0;">
                                    <p class="mb-2" style="color:#1565c0; font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:.04em;">
                                        <i class="fas fa-truck mr-1"></i> Loading LK
                                    </p>
                                    <?php if (empty($list_lk)) : ?>
                                        <p class="mb-0" style="color:#aaa; font-size:12px; font-style:italic;">Tidak ada data</p>
                                    <?php endif; ?>
                                    <?php $no = 1; foreach ($list_lk as $lk) :
                                        $lbl = [
                                            'MENUNGGU'       => ['dot'=>'#94a3b8','teks'=>'menunggu ⏳'],
                                            'PROSES_LOADING' => ['dot'=>'#f59e0b','teks'=>'proses loading ▶️'],
                                            'CETAK_DO'       => ['dot'=>'#a78bfa','teks'=>'cetak DO 🖨️'],
                                            'DO_SELESAI'     => ['dot'=>'#fb923c','teks'=>'DO selesai 📄'],
                                            'DONE'           => ['dot'=>'#22c55e','teks'=>'done ✅'],
                                        ][$lk['status']] ?? ['dot'=>'#aaa','teks'=>$lk['status']];
                                        $is_last_lk = ($no === count($list_lk));
                                    ?>
                                    <div class="d-flex align-items-center py-1"
                                        style="font-size:12px; <?= !$is_last_lk ? 'border-bottom:1px solid #bfdbfe;' : '' ?>">
                                        <span style="width:8px;height:8px;border-radius:50%;background:<?= $lbl['dot'] ?>;flex-shrink:0;" class="mr-2"></span>
                                        <span class="font-weight-bold mr-1" style="color:#333; flex-shrink:0;"><?= $no++ ?>.</span>
                                        <span style="color:#444; flex:1; min-width:0;" class="text-truncate">
                                            <?= htmlspecialchars($lk['keterangan']) ?>
                                        </span>
                                        <?php if (!empty($lk['nm_checker'])) : ?>
                                            <span style="font-size:11px; color:#888; flex-shrink:0; margin:0 4px;">
                                                <i class="fas fa-user" style="font-size:10px;"></i> <?= htmlspecialchars($lk['nm_checker']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="font-weight-bold" style="color:<?= $lbl['dot'] ?>; flex-shrink:0;"><?= $lbl['teks'] ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- ── Kolom Loading KK ── -->
                            <div class="col-md-4 mb-2">
                                <div class="p-2 rounded h-100" style="background:#e8f5e9; border-left:4px solid #1b5e20;">
                                    <p class="mb-2" style="color:#1b5e20; font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:.04em;">
                                        <i class="fas fa-truck-loading mr-1"></i> Loading KK
                                    </p>
                                    <?php if (empty($list_kk)) : ?>
                                        <p class="mb-0" style="color:#aaa; font-size:12px; font-style:italic;">Tidak ada data</p>
                                    <?php endif; ?>
                                    <?php $no = 1; foreach ($list_kk as $kk) :
                                        $lbl = [
                                            'MENUNGGU'       => ['dot'=>'#94a3b8','teks'=>'menunggu ⏳'],
                                            'PROSES_LOADING' => ['dot'=>'#f59e0b','teks'=>'proses loading ▶️'],
                                            'CETAK_DO'       => ['dot'=>'#a78bfa','teks'=>'cetak DO 🖨️'],
                                            'DO_SELESAI'     => ['dot'=>'#fb923c','teks'=>'DO selesai 📄'],
                                            'DONE'           => ['dot'=>'#22c55e','teks'=>'done ✅'],
                                        ][$kk['status']] ?? ['dot'=>'#aaa','teks'=>$kk['status']];
                                        $is_last_kk = ($no === count($list_kk));
                                    ?>
                                    <div class="d-flex align-items-center py-1"
                                        style="font-size:12px; <?= !$is_last_kk ? 'border-bottom:1px solid #bbf7d0;' : '' ?>">
                                        <span style="width:8px;height:8px;border-radius:50%;background:<?= $lbl['dot'] ?>;flex-shrink:0;" class="mr-2"></span>
                                        <span class="font-weight-bold mr-1" style="color:#333; flex-shrink:0;"><?= $no++ ?>.</span>
                                        <span style="color:#444; flex:1; min-width:0;" class="text-truncate">
                                            <?= htmlspecialchars($kk['keterangan']) ?>
                                        </span>
                                        <?php if (!empty($kk['nm_checker'])) : ?>
                                            <span style="font-size:11px; color:#888; flex-shrink:0; margin:0 4px;">
                                                <i class="fas fa-user" style="font-size:10px;"></i> <?= htmlspecialchars($kk['nm_checker']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="font-weight-bold" style="color:<?= $lbl['dot'] ?>; flex-shrink:0;"><?= $lbl['teks'] ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>

                        <!-- Footer ringkasan -->
                        <hr style="border-color:#e0e0e0; margin:10px 0 8px;">
                        <div class="d-flex justify-content-center align-items-center" style="gap:16px; flex-wrap:wrap;">
                            <span style="font-size:13px; font-weight:700; color:#1565c0;">
                                <i class="fas fa-warehouse mr-1"></i>
                                Aktif: <span style="font-size:16px; color:#e65100;"><?= count($aktif) ?></span> pintu berjalan
                            </span>
                            <span style="font-size:13px; font-weight:700; color:#1b5e20;">
                                <i class="fas fa-check-circle mr-1"></i>
                                Selesai: <span style="font-size:16px; color:#22c55e;"><?= count($selesai) ?></span> bongkaran
                            </span>
                        </div>
                    </div>
                </div>


                <!-- TABEL BONGKARAN -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title"><i class="fas fa-dolly mr-2"></i> Data Bongkaran</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <?php
                        $has_aksi_b = in_array($role, ['CHECKER','MANAGERCK','MANAGERWH','ADMLOG']);
                        $colspan_b  = $has_aksi_b ? 11 : 10;
                        $b_aktif   = array_filter($bongkaran, fn($r) => in_array($r['status'], ['PROSES','PENYIAPAN_BARANG','CETAK_DO']));
                        $b_pending = array_filter($bongkaran, fn($r) => $r['status'] === 'MENUNGGU');
                        $b_done    = array_filter($bongkaran, fn($r) => $r['status'] === 'DONE');

                        $renderBongkaran = function($b) use (&$no, $role, $nik, $my_active_id, $has_aksi_b) {
                            $is_done         = ($b['status'] === 'DONE');
                            $is_taken        = !empty($b['nik_checker']);
                            $is_my_job       = ($b['nik_checker'] === $nik);
                            $progres         = (int)($b['progres'] ?? 0);
                            $is_done_checker = ($b['status_checker'] === 'DONE');
                            $badge = ['MENUNGGU'=>'badge-secondary','PROSES'=>'badge-warning',
                                      'PENYIAPAN_BARANG'=>'badge-info','CETAK_DO'=>'badge-primary',
                                      'DONE'=>'badge-success'][$b['status']] ?? 'badge-secondary';
                            if ($is_done) $rc = 'table-success';
                            elseif ($b['status'] === 'MENUNGGU') $rc = 'row-pending';
                            else $rc = 'row-proses';
                        ?>
                        <tr class="<?= $rc ?>">
                            <td><?= $no++ ?></td>
                            <td><small><?= date('d/m/Y', strtotime($b['created_at'])) ?></small></td>
                            <td><small><?= htmlspecialchars($b['kode_bongkar']) ?></small></td>
                            <td><?= htmlspecialchars($b['keterangan'] ?? '-') ?></td>
                            <td><?= $is_taken ? htmlspecialchars($b['nm_checker']) : '<span class="text-muted">-</span>' ?></td>
                            <td><small><?= $is_taken ? ($b['waktu_mulai'] ?? '-') : '-' ?></small></td>
                            <td><small><?= $is_done_checker ? ($b['waktu_selesai'] ?? '-') : '-' ?></small></td>
                            <td style="min-width:110px;">
                                <div class="progress" style="height:16px;">
                                    <div class="progress-bar <?= $progres==100?'bg-success':'bg-warning' ?>" style="width:<?= $progres ?>%"><?= $progres ?>%</div>
                                </div>
                            </td>
                            <td class="text-center" style="min-width:100px;">
                                <?php
                                if ($is_taken && !empty($b['waktu_mulai'])) {
                                    $mulai=$b['waktu_mulai']; $selisih=((!empty($b['waktu_selesai'])&&$is_done_checker)?strtotime($b['waktu_selesai']):time())-strtotime($mulai);
                                    if($selisih>0){$j=floor($selisih/3600);$m=floor(($selisih%3600)/60);echo '<small>'.($j>0?$j.' jam ':'').$m.' menit</small>';if(!$is_done_checker)echo ' <span class="badge badge-warning" style="font-size:9px;">live</span>';}
                                    else echo '<small class="text-muted">-</small>';
                                } else echo '<small class="text-muted">-</small>';
                                ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $badge ?>"><?= str_replace('_',' ',$b['status']) ?></span>
                            </td>
                            <?php if ($has_aksi_b) : ?>
                            <td class="text-center" style="min-width:230px;">
                                <?php if ($role === 'CHECKER') : ?>
                                    <?php if (!$is_taken && $my_active_id === null) : ?>
                                        <button class="btn btn-sm btn-success btn-start" data-id="<?= $b['id'] ?>"><i class="fas fa-play"></i> Start</button>
                                    <?php elseif (!$is_taken && $my_active_id !== null) : ?>
                                        <span class="badge badge-secondary">Selesaikan job Anda dulu</span>
                                    <?php elseif ($is_my_job && $b['status_checker'] === 'PROSES') : ?>
                                        <div class="aksi-checker">
                                            <select class="form-control form-control-sm select-progres" data-id="<?= $b['id'] ?>">
                                                <?php foreach ([0,10,20,30,40,50,60,70,80,90] as $p) : ?><option value="<?= $p ?>" <?= $progres==$p?'selected':'' ?>><?= $p ?>%</option><?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-sm btn-warning btn-update-progres" data-id="<?= $b['id'] ?>"><i class="fas fa-sync"></i> Update</button>
                                            <button class="btn btn-sm btn-primary btn-done" data-id="<?= $b['id'] ?>"><i class="fas fa-check"></i> Done</button>
                                        </div>
                                    <?php else : ?>
                                        <span class="text-muted small"><i class="fas fa-lock mr-1"></i><?= htmlspecialchars($b['nm_checker'] ?? 'checker lain') ?></span>
                                    <?php endif; ?>
                                <?php elseif ($role === 'MANAGERCK') : ?>
                                    <?php if (!$is_taken) : ?>
                                        <button class="btn btn-sm btn-success btn-start-mck" data-id="<?= $b['id'] ?>" data-type="bongkaran"><i class="fas fa-play"></i> Start</button>
                                    <?php elseif ($b['status'] === 'PROSES') : ?>
                                        <div class="aksi-managerck">
                                            <select class="form-control form-control-sm select-progres" data-id="<?= $b['id'] ?>">
                                                <?php foreach ([0,10,20,30,40,50,60,70,80,90] as $p) : ?><option value="<?= $p ?>" <?= $progres==$p?'selected':'' ?>><?= $p ?>%</option><?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-sm btn-warning btn-update-progres" data-id="<?= $b['id'] ?>"><i class="fas fa-sync"></i> Update</button>
                                            <button class="btn btn-sm btn-primary btn-done" data-id="<?= $b['id'] ?>"><i class="fas fa-check"></i> Done</button>
                                        </div>
                                    <?php else : ?><span class="text-muted small">—</span><?php endif; ?>
                                <?php else : ?><span class="text-muted small">—</span><?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php }; ?>

                        <table class="table table-bordered table-sm" id="tabelBongkaran">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th><th>Tgl Dibuat</th><th>Kode</th><th>Keterangan</th><th>Checker</th>
                                    <th>Mulai</th><th>Selesai</th><th>Progres</th><th>Durasi</th><th>Status</th>
                                    <?php if ($has_aksi_b) : ?><th class="text-center">Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no=1; ?>
                            <?php if (!empty($b_aktif)) : ?>
                            <tr class="separator-label sep-active"><td colspan="<?= $colspan_b ?>"><i class="fas fa-circle-notch fa-spin mr-1"></i> Sedang Proses</td></tr>
                            <?php foreach ($b_aktif as $b) : $renderBongkaran($b); endforeach; ?>
                            <?php endif; ?>
                            <?php if (!empty($b_pending)) : ?>
                            <tr class="separator-label sep-pending"><td colspan="<?= $colspan_b ?>"><i class="fas fa-hourglass-half mr-1"></i> Menunggu / Belum Dikerjakan</td></tr>
                            <?php foreach ($b_pending as $b) : $renderBongkaran($b); endforeach; ?>
                            <?php endif; ?>
                            <?php if (!empty($b_done)) : ?>
                            <tr class="separator-label sep-done"><td colspan="<?= $colspan_b ?>"><i class="fas fa-check-circle mr-1"></i> Sudah Selesai / Done</td></tr>
                            <?php foreach ($b_done as $b) : $renderBongkaran($b); endforeach; ?>
                            <?php endif; ?>
                            <?php if (empty($bongkaran)) : ?>
                            <tr><td colspan="<?= $colspan_b ?>" class="text-center text-muted">Tidak ada data bongkaran</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABEL LOADING LK (header biru) -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-truck mr-2"></i> Loading LK</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <?php
                        $has_aksi_lk = in_array($role, ['ADMLOG','CHECKER','MANAGERCK']);
                        $colspan_lk  = $has_aksi_lk ? 11 : 10;
                        $lk_aktif   = array_filter($list_lk, fn($r) => $r['status'] === 'PROSES_LOADING');
                        $lk_pending = array_filter($list_lk, fn($r) => in_array($r['status'], ['MENUNGGU','CETAK_DO','DO_SELESAI']));
                        $lk_done    = array_filter($list_lk, fn($r) => $r['status'] === 'DONE');

                        $renderLK = function($lk) use (&$no, $role, $nik, $has_aksi_lk) {
                            $lk_done=$lk['status']==='DONE'; $lk_progres=(int)($lk['progres']??0);
                            $lk_started=$lk['status']==='PROSES_LOADING';
                            $badge_lk=['CETAK_DO'=>'badge-info','DO_SELESAI'=>'badge-warning','PROSES_LOADING'=>'badge-primary','DONE'=>'badge-success'][$lk['status']]??'badge-secondary';
                            $lk_durasi='-';
                            if(!empty($lk['waktu_mulai'])){$akhir=(!empty($lk['waktu_selesai'])&&$lk_done)?strtotime($lk['waktu_selesai']):time();$sel=$akhir-strtotime($lk['waktu_mulai']);if($sel>0){$j=floor($sel/3600);$m=floor(($sel%3600)/60);$lk_durasi=$j>0?"{$j} jam {$m} menit":"{$m} menit";}}
                            $rc=$lk_done?'table-success':($lk_started?'row-proses':'row-pending');
                        ?>
                        <tr class="<?= $rc ?>">
                            <td><?= $no++ ?></td>
                            <td><small><?= htmlspecialchars($lk['kode']??'-') ?></small></td>
                            <td><?= htmlspecialchars($lk['keterangan']) ?></td>
                            <td><small><?= $lk['tgl'] ?></small></td>
                            <td><?= !empty($lk['nm_checker'])?htmlspecialchars($lk['nm_checker']):'<span class="text-muted">-</span>' ?></td>
                            <td><small><?= !empty($lk['waktu_mulai'])?date('d/m H:i',strtotime($lk['waktu_mulai'])):'-' ?></small></td>
                            <td><small><?= !empty($lk['waktu_selesai'])?date('d/m H:i',strtotime($lk['waktu_selesai'])):'-' ?></small></td>
                            <td style="min-width:100px;"><div class="progress" style="height:14px;"><div class="progress-bar <?= $lk_progres==100?'bg-success':'bg-primary' ?>" style="width:<?= $lk_progres ?>%"><?= $lk_progres ?>%</div></div></td>
                            <td><small><?= $lk_durasi ?><?php if($lk_started):?> <span class="badge badge-warning" style="font-size:9px;">live</span><?php endif;?></small></td>
                            <td class="text-center">
                                <?php if($role==='ADMLOG'&&in_array($lk['status'],['CETAK_DO','DO_SELESAI'])): ?>
                                    <select class="form-control form-control-sm select-status-lk" data-id="<?= $lk['id'] ?>" style="min-width:120px;">
                                        <?php foreach(['CETAK_DO','DO_SELESAI'] as $s):?><option value="<?= $s ?>" <?= $lk['status']===$s?'selected':''?>><?= str_replace('_',' ',$s) ?></option><?php endforeach;?>
                                    </select>
                                <?php else:?><span class="badge <?= $badge_lk ?>"><?= str_replace('_',' ',$lk['status']) ?></span><?php endif;?>
                            </td>
                            <?php if($has_aksi_lk): ?>
                            <td class="text-center" style="min-width:230px;">
                                <?php if($role==='ADMLOG'): ?>
                                    <?php if(in_array($lk['status'],['CETAK_DO','DO_SELESAI'])): ?>
                                        <button class="btn btn-sm btn-info btn-simpan-lk mr-1" data-id="<?= $lk['id'] ?>"><i class="fas fa-save"></i> Simpan</button>
                                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                                <?php elseif($role==='CHECKER'): ?>
                                    <?php if($lk['status']==='DO_SELESAI'): ?>
                                        <button class="btn btn-sm btn-success btn-start-lk" data-id="<?= $lk['id'] ?>"><i class="fas fa-play"></i> Start</button>
                                    <?php elseif($lk_started&&$lk['nik_checker']===$nik): ?>
                                        <div class="aksi-checker">
                                            <select class="form-control form-control-sm select-progres-lk"><?php foreach([0,10,20,30,40,50,60,70,80,90] as $p):?><option value="<?= $p ?>" <?= $lk_progres==$p?'selected':''?>><?= $p ?>%</option><?php endforeach;?></select>
                                            <button class="btn btn-sm btn-warning btn-update-progres-lk" data-id="<?= $lk['id'] ?>"><i class="fas fa-sync"></i> Update</button>
                                            <button class="btn btn-sm btn-primary btn-done-lk" data-id="<?= $lk['id'] ?>"><i class="fas fa-check"></i> Done</button>
                                        </div>
                                    <?php elseif($lk_started): ?><span class="text-muted small"><i class="fas fa-lock mr-1"></i><?= htmlspecialchars($lk['nm_checker']??'') ?></span>
                                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                                <?php elseif($role==='MANAGERCK'): ?>
                                    <?php if($lk['status']==='DO_SELESAI'): ?>
                                        <button class="btn btn-sm btn-success btn-start-mck" data-id="<?= $lk['id'] ?>" data-type="lk"><i class="fas fa-play"></i> Start</button>
                                    <?php elseif($lk_started): ?>
                                        <div class="aksi-managerck">
                                            <select class="form-control form-control-sm select-progres-lk"><?php foreach([0,10,20,30,40,50,60,70,80,90] as $p):?><option value="<?= $p ?>" <?= $lk_progres==$p?'selected':''?>><?= $p ?>%</option><?php endforeach;?></select>
                                            <button class="btn btn-sm btn-warning btn-update-progres-lk" data-id="<?= $lk['id'] ?>"><i class="fas fa-sync"></i> Update</button>
                                            <button class="btn btn-sm btn-primary btn-done-lk" data-id="<?= $lk['id'] ?>"><i class="fas fa-check"></i> Done</button>
                                        </div>
                                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php }; ?>

                        <table class="table table-bordered table-sm" id="tabelLK">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th><th>Kode</th><th>Keterangan</th><th>Tgl</th><th>Checker</th>
                                    <th>Mulai</th><th>Selesai</th><th>Progres</th><th>Durasi</th><th>Status</th>
                                    <?php if($has_aksi_lk):?><th class="text-center">Aksi</th><?php endif;?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no=1; ?>
                            <?php if(!empty($lk_aktif)):?>
                            <tr class="separator-label sep-active"><td colspan="<?= $colspan_lk ?>"><i class="fas fa-circle-notch fa-spin mr-1"></i> Sedang Proses</td></tr>
                            <?php foreach($lk_aktif as $lk):$renderLK($lk);endforeach;endif;?>
                            <?php if(!empty($lk_pending)):?>
                            <tr class="separator-label sep-pending"><td colspan="<?= $colspan_lk ?>"><i class="fas fa-hourglass-half mr-1"></i> Menunggu / Belum Dikerjakan</td></tr>
                            <?php foreach($lk_pending as $lk):$renderLK($lk);endforeach;endif;?>
                            <?php if(!empty($lk_done)):?>
                            <tr class="separator-label sep-done"><td colspan="<?= $colspan_lk ?>"><i class="fas fa-check-circle mr-1"></i> Sudah Selesai / Done</td></tr>
                            <?php foreach($lk_done as $lk):$renderLK($lk);endforeach;endif;?>
                            <?php if(empty($list_lk)):?><tr><td colspan="<?= $colspan_lk ?>" class="text-center text-muted">Tidak ada data Loading LK</td></tr><?php endif;?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABEL LOADING KK (header hijau) -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title"><i class="fas fa-truck-loading mr-2"></i> Loading KK</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <?php
                        $has_aksi_kk = in_array($role, ['ADMLOG','CHECKER','MANAGERCK']);
                        $colspan_kk  = $has_aksi_kk ? 11 : 10;
                        $kk_aktif   = array_filter($list_kk, fn($r) => $r['status'] === 'PROSES_LOADING');
                        $kk_pending = array_filter($list_kk, fn($r) => in_array($r['status'], ['MENUNGGU','CETAK_DO','DO_SELESAI']));
                        $kk_done    = array_filter($list_kk, fn($r) => $r['status'] === 'DONE');

                        $renderKK = function($kk) use (&$no, $role, $nik, $has_aksi_kk) {
                            $kk_done=$kk['status']==='DONE'; $kk_progres=(int)($kk['progres']??0);
                            $kk_started=$kk['status']==='PROSES_LOADING';
                            $badge_kk=['CETAK_DO'=>'badge-info','DO_SELESAI'=>'badge-warning','PROSES_LOADING'=>'badge-primary','DONE'=>'badge-success'][$kk['status']]??'badge-secondary';
                            $kk_durasi='-';
                            if(!empty($kk['waktu_mulai'])){$akhir=(!empty($kk['waktu_selesai'])&&$kk_done)?strtotime($kk['waktu_selesai']):time();$sel=$akhir-strtotime($kk['waktu_mulai']);if($sel>0){$j=floor($sel/3600);$m=floor(($sel%3600)/60);$kk_durasi=$j>0?"{$j} jam {$m} menit":"{$m} menit";}}
                            $rc=$kk_done?'table-success':($kk_started?'row-proses':'row-pending');
                        ?>
                        <tr class="<?= $rc ?>">
                            <td><?= $no++ ?></td>
                            <td><small><?= htmlspecialchars($kk['kode']??'-') ?></small></td>
                            <td><?= htmlspecialchars($kk['keterangan']) ?></td>
                            <td><small><?= $kk['tgl'] ?></small></td>
                            <td><?= !empty($kk['nm_checker'])?htmlspecialchars($kk['nm_checker']):'<span class="text-muted">-</span>' ?></td>
                            <td><small><?= !empty($kk['waktu_mulai'])?date('d/m H:i',strtotime($kk['waktu_mulai'])):'-' ?></small></td>
                            <td><small><?= !empty($kk['waktu_selesai'])?date('d/m H:i',strtotime($kk['waktu_selesai'])):'-' ?></small></td>
                            <td style="min-width:100px;"><div class="progress" style="height:14px;"><div class="progress-bar bg-success" style="width:<?= $kk_progres ?>%"><?= $kk_progres ?>%</div></div></td>
                            <td><small><?= $kk_durasi ?><?php if($kk_started):?> <span class="badge badge-warning" style="font-size:9px;">live</span><?php endif;?></small></td>
                            <td class="text-center">
                                <?php if($role==='ADMLOG'&&in_array($kk['status'],['CETAK_DO','DO_SELESAI'])): ?>
                                    <select class="form-control form-control-sm select-status-kk" data-id="<?= $kk['id'] ?>" style="min-width:120px;">
                                        <?php foreach(['CETAK_DO','DO_SELESAI'] as $s):?><option value="<?= $s ?>" <?= $kk['status']===$s?'selected':''?>><?= str_replace('_',' ',$s) ?></option><?php endforeach;?>
                                    </select>
                                <?php else:?><span class="badge <?= $badge_kk ?>"><?= str_replace('_',' ',$kk['status']) ?></span><?php endif;?>
                            </td>
                            <?php if($has_aksi_kk): ?>
                            <td class="text-center" style="min-width:230px;">
                                <?php if($role==='ADMLOG'): ?>
                                    <?php if(in_array($kk['status'],['CETAK_DO','DO_SELESAI'])): ?>
                                        <button class="btn btn-sm btn-info btn-simpan-kk mr-1" data-id="<?= $kk['id'] ?>"><i class="fas fa-save"></i> Simpan</button>
                                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                                <?php elseif($role==='CHECKER'): ?>
                                    <?php if($kk['status']==='DO_SELESAI'): ?>
                                        <button class="btn btn-sm btn-success btn-start-kk" data-id="<?= $kk['id'] ?>"><i class="fas fa-play"></i> Start</button>
                                    <?php elseif($kk_started&&$kk['nik_checker']===$nik): ?>
                                        <div class="aksi-checker">
                                            <select class="form-control form-control-sm select-progres-kk"><?php foreach([0,10,20,30,40,50,60,70,80,90] as $p):?><option value="<?= $p ?>" <?= $kk_progres==$p?'selected':''?>><?= $p ?>%</option><?php endforeach;?></select>
                                            <button class="btn btn-sm btn-warning btn-update-progres-kk" data-id="<?= $kk['id'] ?>"><i class="fas fa-sync"></i> Update</button>
                                            <button class="btn btn-sm btn-primary btn-done-kk" data-id="<?= $kk['id'] ?>"><i class="fas fa-check"></i> Done</button>
                                        </div>
                                    <?php elseif($kk_started): ?><span class="text-muted small"><i class="fas fa-lock mr-1"></i><?= htmlspecialchars($kk['nm_checker']??'') ?></span>
                                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                                <?php elseif($role==='MANAGERCK'): ?>
                                    <?php if($kk['status']==='DO_SELESAI'): ?>
                                        <button class="btn btn-sm btn-success btn-start-mck" data-id="<?= $kk['id'] ?>" data-type="kk"><i class="fas fa-play"></i> Start</button>
                                    <?php elseif($kk_started): ?>
                                        <div class="aksi-managerck">
                                            <select class="form-control form-control-sm select-progres-kk"><?php foreach([0,10,20,30,40,50,60,70,80,90] as $p):?><option value="<?= $p ?>" <?= $kk_progres==$p?'selected':''?>><?= $p ?>%</option><?php endforeach;?></select>
                                            <button class="btn btn-sm btn-warning btn-update-progres-kk" data-id="<?= $kk['id'] ?>"><i class="fas fa-sync"></i> Update</button>
                                            <button class="btn btn-sm btn-primary btn-done-kk" data-id="<?= $kk['id'] ?>"><i class="fas fa-check"></i> Done</button>
                                        </div>
                                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php }; ?>

                        <table class="table table-bordered table-sm" id="tabelKK">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>#</th><th>Kode</th><th>Keterangan</th><th>Tgl</th><th>Checker</th>
                                    <th>Mulai</th><th>Selesai</th><th>Progres</th><th>Durasi</th><th>Status</th>
                                    <?php if($has_aksi_kk):?><th class="text-center">Aksi</th><?php endif;?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no=1; ?>
                            <?php if(!empty($kk_aktif)):?>
                            <tr class="separator-label sep-active"><td colspan="<?= $colspan_kk ?>"><i class="fas fa-circle-notch fa-spin mr-1"></i> Sedang Proses</td></tr>
                            <?php foreach($kk_aktif as $kk):$renderKK($kk);endforeach;endif;?>
                            <?php if(!empty($kk_pending)):?>
                            <tr class="separator-label sep-pending"><td colspan="<?= $colspan_kk ?>"><i class="fas fa-hourglass-half mr-1"></i> Menunggu / Belum Dikerjakan</td></tr>
                            <?php foreach($kk_pending as $kk):$renderKK($kk);endforeach;endif;?>
                            <?php if(!empty($kk_done)):?>
                            <tr class="separator-label sep-done"><td colspan="<?= $colspan_kk ?>"><i class="fas fa-check-circle mr-1"></i> Sudah Selesai / Done</td></tr>
                            <?php foreach($kk_done as $kk):$renderKK($kk);endforeach;endif;?>
                            <?php if(empty($list_kk)):?><tr><td colspan="<?= $colspan_kk ?>" class="text-center text-muted">Tidak ada data Loading KK</td></tr><?php endif;?>
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

<!-- MODALS -->

<?php if ($role === 'MANAGERWH') : ?>
<div class="modal fade" id="modalBuatBongkaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="fas fa-plus mr-2"></i> Buat Bongkaran Baru</h5>
            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="form-group"><label class="font-weight-bold">Kode</label><input type="text" class="form-control" value="<?= $kode_baru ?>" readonly></div>
            <div class="form-group"><label class="font-weight-bold">Keterangan <span class="text-danger">*</span></label><input type="text" id="inputKeterangan" class="form-control" placeholder="Contoh: NK 212 BTGT"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-primary" id="btnSimpanBongkaran"><i class="fas fa-save mr-1"></i> Simpan</button>
        </div>
    </div></div>
</div>
<?php endif; ?>

<?php if ($role === 'ADMLOG') : ?>
<div class="modal fade" id="modalTambahKK" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-plus mr-2"></i> Tambah Loading KK</h5>
            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body"><div class="form-group"><label class="font-weight-bold">Keterangan <span class="text-danger">*</span></label><input type="text" id="inputKeteranganKK" class="form-control" placeholder="Contoh: JBR"></div></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-success" id="btnSimpanKK"><i class="fas fa-save mr-1"></i> Simpan</button>
        </div>
    </div></div>
</div>
<div class="modal fade" id="modalTambahLK" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-warning">
            <h5 class="modal-title"><i class="fas fa-plus mr-2"></i> Tambah Loading LK</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body"><div class="form-group"><label class="font-weight-bold">Keterangan <span class="text-danger">*</span></label><input type="text" id="inputKeteranganLK" class="form-control" placeholder="Contoh: P-2"></div></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-warning" id="btnSimpanLK"><i class="fas fa-save mr-1"></i> Simpan</button>
        </div>
    </div></div>
</div>
<?php endif; ?>

<?php if ($role === 'MANAGERCK') : ?>
<div class="modal fade" id="modalStartMCK" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-play mr-2"></i> Start — Pilih Checker</h5>
            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="mck_id"><input type="hidden" id="mck_type">
            <div class="form-group">
                <label class="font-weight-bold">Pilih Checker <span class="text-danger">*</span></label>
                <select id="mck_checker" class="form-control">
                    <option value="">-- Pilih Checker --</option>
                    <?php foreach ($list_checker as $ck) : ?>
                        <option value="<?= $ck['nik'] ?>" data-nama="<?= htmlspecialchars($ck['nm_karyawan']) ?>"><?= htmlspecialchars($ck['nm_karyawan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-success" id="btnKonfirmasiStartMCK"><i class="fas fa-play mr-1"></i> Konfirmasi Start</button>
        </div>
    </div></div>
</div>
<?php endif; ?>

<script>
var BASE = '<?= base_url() ?>';
function ajaxPost(url, data, cb) {
    $.post(BASE + url, data, cb, 'json').fail(function(xhr){ alert('Error: ' + xhr.responseText.substring(0,200)); });
}
$(document).ready(function () {
    setTimeout(function(){ location.reload(); }, 30000);

    $('#btnSimpanBongkaran').on('click', function () {
        var ket=$('#inputKeterangan').val().trim();
        if(!ket){alert('Keterangan wajib diisi');return;}
        ajaxPost('checker/store',{keterangan:ket},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-start-mck',function(){
        $('#mck_id').val($(this).data('id'));$('#mck_type').val($(this).data('type'));$('#mck_checker').val('');$('#modalStartMCK').modal('show');
    });
    $('#btnKonfirmasiStartMCK').on('click',function(){
        var id=$('#mck_id').val(),type=$('#mck_type').val(),nik=$('#mck_checker').val(),nama=$('#mck_checker option:selected').data('nama');
        if(!nik){alert('Pilih checker terlebih dahulu');return;}
        var urlMap={bongkaran:'checker/start',kk:'checker/start_kk',lk:'checker/start_lk'};
        ajaxPost(urlMap[type],{id:id,nik_checker:nik,nm_checker:nama},function(res){$('#modalStartMCK').modal('hide');alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-start',function(){
        if(!confirm('Ambil pekerjaan ini?'))return;
        ajaxPost('checker/start',{id:$(this).data('id')},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-update-progres',function(){
        var id=$(this).data('id'),progres=$(this).closest('.aksi-checker,.aksi-managerck').find('.select-progres').val();
        ajaxPost('checker/update_progres',{id:id,progres:progres},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-done',function(){
        if(!confirm('Tandai sebagai SELESAI?'))return;
        ajaxPost('checker/done',{id:$(this).data('id')},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-simpan-lk',function(){
        var id=$(this).data('id'),status=$(this).closest('tr').find('.select-status-lk').val();
        ajaxPost('checker/update_lk',{id:id,status:status},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-simpan-kk',function(){
        var id=$(this).data('id'),status=$(this).closest('tr').find('.select-status-kk').val();
        ajaxPost('checker/update_kk',{id:id,status:status},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $('#btnSimpanKK').on('click',function(){
        var ket=$('#inputKeteranganKK').val().trim();if(!ket){alert('Keterangan wajib diisi');return;}
        ajaxPost('checker/store_kk',{keterangan:ket},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $('#btnSimpanLK').on('click',function(){
        var ket=$('#inputKeteranganLK').val().trim();if(!ket){alert('Keterangan wajib diisi');return;}
        ajaxPost('checker/store_lk',{keterangan:ket},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-start-lk',function(){
        if(!confirm('Mulai Loading LK ini?'))return;
        ajaxPost('checker/start_lk',{id:$(this).data('id')},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-update-progres-lk',function(){
        var id=$(this).data('id'),progres=$(this).closest('.aksi-checker,.aksi-managerck').find('.select-progres-lk').val();
        ajaxPost('checker/update_progres_lk',{id:id,progres:progres},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-done-lk',function(){
        if(!confirm('Tandai Loading LK sebagai SELESAI?'))return;
        ajaxPost('checker/done_lk',{id:$(this).data('id')},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-start-kk',function(){
        if(!confirm('Mulai Loading KK ini?'))return;
        ajaxPost('checker/start_kk',{id:$(this).data('id')},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-update-progres-kk',function(){
        var id=$(this).data('id'),progres=$(this).closest('.aksi-checker,.aksi-managerck').find('.select-progres-kk').val();
        ajaxPost('checker/update_progres_kk',{id:id,progres:progres},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $(document).on('click','.btn-done-kk',function(){
        if(!confirm('Tandai Loading KK sebagai SELESAI?'))return;
        ajaxPost('checker/done_kk',{id:$(this).data('id')},function(res){alert(res.msg);if(res.status)location.reload();});
    });
    $('#btnArchiveAll').on('click',function(){
        Swal.fire({
            title:'Archive Aktivitas Hari Ini?',
            html:'Semua aktivitas yang sudah <b>DONE</b> akan diarsipkan,<br>tidak terbatas hari ini saja.<br><small class="text-muted">Data yang belum DONE tidak akan terpengaruh.</small>',
            icon:'warning',showCancelButton:true,confirmButtonColor:'#dc3545',cancelButtonColor:'#6c757d',
            confirmButtonText:'<i class="fas fa-box-archive mr-1"></i> Ya, Archive Sekarang',cancelButtonText:'Batal'
        }).then(function(result){
            if(!result.isConfirmed)return;
            ajaxPost('checker/archive_all_today',{},function(res){
                Swal.fire({icon:res.status?'success':'error',title:res.status?'Berhasil!':'Gagal',text:res.msg,timer:res.status?2500:0,showConfirmButton:!res.status})
                .then(function(){if(res.status)location.reload();});
            });
        });
    });
});
</script>
</body>