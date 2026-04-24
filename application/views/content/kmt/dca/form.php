<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" height="150" width="300">
    </div>
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-handshake text-info"></i> <?= $page_title ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dca') ?>">DCA</a></li>
                            <li class="breadcrumb-item active"><?= isset($row) ? 'Edit' : 'Tambah' ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php $this->load->view('partial/main/alert') ?>

                <?php
                $is_edit          = isset($row);
                $lv               = (int)$lv;
                $is_verified      = $is_edit && (int)($row['status_verifikasi'] ?? 0) === 1;
                $existing_detail  = isset($detail) ? $detail : [];

                // Apakah form boleh diedit?
                // - lv 1 & 2 : SELALU boleh edit
                // - lv 3     : hanya boleh edit jika BELUM terverifikasi
                $form_readonly    = ($lv === 3 && $is_verified);

                $action = $is_edit
                    ? base_url('kmt/dca/update/' . $row['id'])
                    : base_url('kmt/dca/simpan');
                ?>

                <!-- ════════════════════════════════════════════════════
                     BANNER STATUS VERIFIKASI
                     Hanya muncul saat edit ($is_edit = true)
                ════════════════════════════════════════════════════ -->
                <?php if ($is_edit): ?>

                    <?php if ($is_verified): ?>
                    <!-- ── Data SUDAH diverifikasi ── -->
                    <div class="alert alert-success shadow-sm mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-check-circle fa-2x mr-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 font-weight-bold">Data Telah Diverifikasi</h6>
                                <div class="row" style="font-size:13px;">
                                    <div class="col-md-4">
                                        <i class="fas fa-user-check mr-1"></i>
                                        Verifikator:
                                        <strong><?= htmlspecialchars($row['nama_verifikator'] ?? '-') ?></strong>
                                    </div>
                                    <div class="col-md-4">
                                        <i class="fas fa-calendar-check mr-1"></i>
                                        Waktu:
                                        <strong>
                                            <?= $row['verified_at']
                                                ? date('d/m/Y H:i', strtotime($row['verified_at']))
                                                : '-' ?>
                                        </strong>
                                    </div>
                                    <?php if (!empty($row['verified_notes'])): ?>
                                    <div class="col-md-4">
                                        <i class="fas fa-comment mr-1"></i>
                                        Catatan: <em><?= htmlspecialchars($row['verified_notes']) ?></em>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($lv === 3): ?>
                                <!-- ABM: pesan kunci -->
                                <div class="mt-2 pt-2 border-top border-success">
                                    <i class="fas fa-lock mr-1"></i>
                                    <strong>Data dikunci.</strong>
                                    Hubungi Adm Keuangan jika ada perubahan yang diperlukan.
                                </div>

                                <?php else: ?>
                                <!-- lv 1 & 2: tombol batalkan verifikasi -->
                                <div class="mt-2 pt-2 border-top border-success">
                                    <small class="text-muted d-block mb-1">
                                        Anda dapat membatalkan verifikasi jika ditemukan kesalahan data.
                                        Setelah dibatalkan, ABM dapat mengedit kembali.
                                    </small>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            id="btnBatalVerifForm">
                                        <i class="fas fa-undo mr-1"></i> Batalkan Verifikasi
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php elseif ($lv <= 2): ?>
                    <!-- ── Data BELUM diverifikasi — banner untuk lv 1 & 2 ── -->
                    <div class="alert alert-warning shadow-sm mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-hourglass-half fa-2x mr-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 font-weight-bold">Menunggu Verifikasi</h6>
                                <p class="mb-2 small">
                                    Periksa isian data di bawah. Jika sudah benar,
                                    klik <strong>Verifikasi</strong>.
                                    Anda tetap bisa mengedit data sebelum atau sesudah verifikasi.
                                </p>
                                <button type="button" class="btn btn-sm btn-success"
                                        id="btnVerifForm">
                                    <i class="fas fa-check-circle mr-1"></i> Verifikasi Data Ini
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ── Riwayat Verifikasi (collapsed by default) ── -->
                    <?php if (!empty($log_verifikasi)): ?>
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header p-2">
                            <h3 class="card-title" style="font-size:13px;">
                                <i class="fas fa-history mr-1"></i>
                                Riwayat Verifikasi
                                <span class="badge badge-secondary ml-1">
                                    <?= count($log_verifikasi) ?>
                                </span>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool"
                                        data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- collapsed by default -->
                        <div class="card-body p-0" style="display:none;">
                            <table class="table table-sm table-bordered mb-0"
                                   style="font-size:12px;">
                                <thead style="background:#f4f4f4;">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Aksi</th>
                                        <th>Oleh</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($log_verifikasi as $log): ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_map = [
                                            'verifikasi'       => ['success', 'Verifikasi'],
                                            'batal_verifikasi' => ['danger',  'Batal Verifikasi'],
                                            'reset_oleh_edit'  => ['secondary','Reset (Edit)'],
                                        ];
                                        [$bc, $bl] = $badge_map[$log['aksi']] ?? ['secondary', $log['aksi']];
                                        ?>
                                        <span class="badge badge-<?= $bc ?>"><?= $bl ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($log['nama_user'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($log['catatan'] ?? '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php endif; // end $is_edit ?>

                <!-- ════════════════════════════════════════════════════
                     FORM DATA DCA
                     $form_readonly = true  → hanya tampil, lv3 + verified
                     $form_readonly = false → bisa diedit (semua level lainnya)
                ════════════════════════════════════════════════════ -->

                <?php if ($form_readonly): ?>
                <!-- ── Mode READ-ONLY untuk ABM yang datanya sudah terverifikasi ── -->
                <div class="callout callout-info mb-3">
                    <i class="fas fa-eye mr-1"></i>
                    Mode <strong>Lihat saja</strong> — data sudah dikunci oleh Adm Keuangan.
                </div>
                <?php endif; ?>

                <form action="<?= $action ?>" method="POST" id="formDca"
                      <?= $form_readonly ? 'style="pointer-events:none;opacity:0.85;"' : '' ?>>
                    <?= form_open($action) ?>

                    <!-- ═══════════════════════════════════════
                         CARD 1 — Informasi DCA
                    ═══════════════════════════════════════ -->
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-1"></i> Informasi DCA
                            </h3>
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <!-- Tanggal -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal DCA <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_dca"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['tanggal_dca'] : date('Y-m-d') ?>"
                                               <?= $form_readonly ? 'readonly' : 'required' ?>>
                                    </div>
                                </div>

                                <!-- Wilayah -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Wilayah <span class="text-danger">*</span></label>
                                        <select name="id_wilayah"
                                                class="form-control form-control-sm"
                                                <?= ($lv === 3 || $form_readonly) ? 'disabled' : '' ?>
                                                <?= $form_readonly ? '' : 'required' ?>>
                                            <option value="">-- Pilih --</option>
                                            <?php foreach ($wilayah_list as $w): ?>
                                            <option value="<?= $w['id'] ?>"
                                                <?= (($is_edit  && $row['id_wilayah'] == $w['id'])
                                                    || (!$is_edit && $id_wilayah_user == $w['id']))
                                                    ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($w['nama_wilayah']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($lv === 3 || $form_readonly): ?>
                                        <input type="hidden" name="id_wilayah"
                                               value="<?= $is_edit
                                                   ? $row['id_wilayah']
                                                   : $id_wilayah_user ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- MDO -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>MDO <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_mdo"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit
                                                   ? htmlspecialchars($row['nama_mdo'] ?? '')
                                                   : '' ?>"
                                               placeholder="Nama MDO..."
                                               <?= $form_readonly ? 'readonly' : 'required' ?>>
                                    </div>
                                </div>

                                <!-- ABM -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>ABM</label>
                                        <input type="text" name="abm"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit
                                                   ? htmlspecialchars($row['abm'] ?? '')
                                                   : $this->session->userdata('nama') ?>"
                                               <?= $form_readonly ? 'readonly' : '' ?>>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- UM -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Uang Muka (UM) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" name="um_header" id="umHeader"
                                                   class="form-control form-control-sm angka-header"
                                                   value="<?= $is_edit
                                                       ? number_format($row['um'] ?? 0, 0, ',', '.')
                                                       : '' ?>"
                                                   placeholder="0"
                                                   <?= $form_readonly ? 'readonly' : 'required' ?>>
                                        </div>
                                    </div>
                                </div>

                                <!-- Refund (otomatis) -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>
                                            Refund
                                            <small class="text-muted">(UM &minus; Real Biaya)</small>
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" id="refundHeaderDisplay"
                                                   class="form-control form-control-sm"
                                                   value="<?= $is_edit
                                                       ? number_format($row['refund'] ?? 0, 0, ',', '.')
                                                       : '0' ?>"
                                                   readonly
                                                   style="background:#fff3cd;font-weight:600;">
                                        </div>
                                        <input type="hidden" name="refund_header"
                                               id="refundHeaderVal"
                                               value="<?= $is_edit ? ($row['refund'] ?? 0) : 0 ?>">
                                    </div>
                                </div>

                                <!-- Keterangan Umum -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Keterangan Umum</label>
                                        <input type="text" name="uraian"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit
                                                   ? htmlspecialchars($row['uraian'] ?? '')
                                                   : '' ?>"
                                               placeholder="Opsional..."
                                               <?= $form_readonly ? 'readonly' : '' ?>>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /.card-body -->
                    </div><!-- /.card -->

                    <!-- ═══════════════════════════════════════
                         CARD 2 — Detail Biaya per Kegiatan
                    ═══════════════════════════════════════ -->
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list-ul mr-1"></i> Detail Biaya per Kegiatan
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-info" id="badgeTotalDca">Total: Rp 0</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0"
                                       id="tblDetail">
                                    <thead style="background:#1f3864;color:#fff;font-size:12px;">
                                        <tr>
                                            <th width="28">#</th>
                                            <th width="170">Kegiatan <span class="text-danger">*</span></th>
                                            <th width="120">Tgl Kegiatan</th>
                                            <th width="120">Tgl Kasbon</th>
                                            <th width="70"  class="text-center">Peserta</th>
                                            <th width="110" class="text-center">Qty Bisi</th>
                                            <th width="110" class="text-center">Qty Q235</th>
                                            <th width="140">Real Biaya</th>
                                            <th width="160">Keterangan</th>
                                            <?php if (!$form_readonly): ?>
                                            <th width="36"></th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyDetail">

                                        <?php if (!empty($existing_detail)): ?>
                                        <?php foreach ($existing_detail as $i => $det): ?>
                                        <tr class="baris-detail">
                                            <td class="text-center no-baris align-middle">
                                                <?= $i + 1 ?>
                                            </td>

                                            <!-- Kegiatan -->
                                            <td>
                                                <?php if ($form_readonly): ?>
                                                    <span class="form-control-plaintext form-control-sm">
                                                        <?= htmlspecialchars($det['nama_kegiatan']) ?>
                                                    </span>
                                                <?php else: ?>
                                                <select name="id_kegiatan[]"
                                                        class="form-control form-control-sm sel-kegiatan">
                                                    <option value="">-- Pilih --</option>
                                                    <?php foreach ($kegiatan_list as $kg): ?>
                                                    <option value="<?= $kg['id'] ?>"
                                                        <?= ($det['id_kegiatan'] == $kg['id'])
                                                            ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($kg['nama_kegiatan']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                    <option value="custom"
                                                        <?= empty($det['id_kegiatan'])
                                                            ? 'selected' : '' ?>>
                                                        + Kegiatan Baru
                                                    </option>
                                                </select>
                                                <input type="hidden" name="nama_kegiatan[]"
                                                       class="inp-nama-kegiatan"
                                                       value="<?= htmlspecialchars($det['nama_kegiatan']) ?>">
                                                <input type="text"
                                                       class="form-control form-control-sm mt-1
                                                              inp-custom-kegiatan"
                                                       placeholder="Tulis nama kegiatan baru..."
                                                       style="display:<?= empty($det['id_kegiatan'])
                                                           ? 'block' : 'none' ?>">
                                                <?php endif; ?>
                                            </td>

                                            <!-- Tgl Kegiatan -->
                                            <td>
                                                <input type="date" name="tgl_kegiatan[]"
                                                       class="form-control form-control-sm"
                                                       value="<?= $det['tgl_kegiatan'] ?? '' ?>"
                                                       <?= $form_readonly ? 'readonly' : '' ?>>
                                            </td>

                                            <!-- Tgl Kasbon -->
                                            <td>
                                                <input type="date" name="tgl_kasbon[]"
                                                       class="form-control form-control-sm"
                                                       value="<?= $det['tgl_kasbon'] ?? '' ?>"
                                                       <?= $form_readonly ? 'readonly' : '' ?>>
                                            </td>

                                            <!-- Jumlah Peserta -->
                                            <td>
                                                <input type="number" name="jml_peserta[]"
                                                       class="form-control form-control-sm text-center"
                                                       value="<?= (int)($det['jml_peserta'] ?? 0) ?>"
                                                       min="0" placeholder="0"
                                                       <?= $form_readonly ? 'readonly' : '' ?>>
                                            </td>

                                            <!-- Qty Bisi -->
                                            <td>
                                                <input type="text" name="qty_bisi[]"
                                                       class="form-control form-control-sm
                                                              angka-detail text-right"
                                                       value="<?= ($det['qty_bisi'] ?? 0) > 0
                                                           ? number_format($det['qty_bisi'], 0, ',', '.')
                                                           : '' ?>"
                                                       placeholder="0"
                                                       <?= $form_readonly ? 'readonly' : '' ?>>
                                            </td>

                                            <!-- Qty Q235 -->
                                            <td>
                                                <input type="text" name="qty_q235[]"
                                                       class="form-control form-control-sm
                                                              angka-detail text-right"
                                                       value="<?= ($det['qty_q235'] ?? 0) > 0
                                                           ? number_format($det['qty_q235'], 0, ',', '.')
                                                           : '' ?>"
                                                       placeholder="0"
                                                       <?= $form_readonly ? 'readonly' : '' ?>>
                                            </td>

                                            <!-- Real Biaya -->
                                            <td>
                                                <input type="text" name="real_detail[]"
                                                       class="form-control form-control-sm
                                                              angka-detail inp-real text-right"
                                                       value="<?= ($det['real_biaya'] ?? 0) > 0
                                                           ? number_format($det['real_biaya'], 0, ',', '.')
                                                           : '' ?>"
                                                       placeholder="0"
                                                       <?= $form_readonly ? 'readonly' : '' ?>>
                                            </td>

                                            <!-- Keterangan -->
                                            <td>
                                                <input type="text" name="ket_detail[]"
                                                       class="form-control form-control-sm"
                                                       value="<?= htmlspecialchars($det['keterangan'] ?? '') ?>"
                                                       placeholder="Opsional..."
                                                       <?= $form_readonly ? 'readonly' : '' ?>>
                                            </td>

                                            <!-- Hapus baris (tersembunyi jika readonly) -->
                                            <?php if (!$form_readonly): ?>
                                            <td class="text-center align-middle">
                                                <button type="button"
                                                        class="btn btn-xs btn-danger btn-hapus-baris">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>

                                    </tbody>
                                </table>
                            </div>

                            <!-- Tombol tambah baris (disembunyikan jika readonly) -->
                            <?php if (!$form_readonly): ?>
                            <div class="p-2 border-top">
                                <button type="button" class="btn btn-sm btn-outline-info"
                                        id="btnTambahBaris">
                                    <i class="fas fa-plus mr-1"></i> Tambah Kegiatan
                                </button>
                                <small class="text-muted ml-2">
                                    Pilih "+ Kegiatan Baru" untuk kegiatan yang tidak ada di daftar
                                </small>
                            </div>
                            <?php endif; ?>

                            <!-- Ringkasan total -->
                            <div class="p-3 border-top">
                                <div class="row justify-content-end">
                                    <div class="col-md-5 col-lg-4">
                                        <table class="table table-sm mb-0" style="font-size:13px;">
                                            <tr>
                                                <td>Total Qty Bisi</td>
                                                <td class="text-right font-weight-bold"
                                                    id="sumQtyBisi">0</td>
                                            </tr>
                                            <tr>
                                                <td>Total Qty Q235</td>
                                                <td class="text-right font-weight-bold"
                                                    id="sumQtyQ235">0</td>
                                            </tr>
                                            <tr>
                                                <td>Total Real Biaya</td>
                                                <td class="text-right" id="sumReal">Rp 0</td>
                                            </tr>
                                            <tr class="text-danger">
                                                <td class="font-weight-bold">
                                                    Refund (UM &minus; Real)
                                                </td>
                                                <td class="text-right font-weight-bold"
                                                    id="sumRefund">Rp 0</td>
                                            </tr>
                                            <tr style="background:#1f3864;color:#fff;">
                                                <td class="font-weight-bold">TOTAL BIAYA DCA</td>
                                                <td class="text-right font-weight-bold"
                                                    id="sumTotal">Rp 0</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /.card-body -->
                    </div><!-- /.card -->

                    <!-- Tombol aksi -->
                    <div class="mb-4">
                        <?php if (!$form_readonly): ?>
                        <button type="submit" class="btn btn-info" id="btnSimpan">
                            <i class="fas fa-save mr-1"></i>
                            <?= $is_edit ? 'Perbarui Data' : 'Simpan Data' ?>
                        </button>
                        <?php endif; ?>
                        <a href="<?= base_url('kmt/dca') ?>" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>

                </form>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022
            <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.
        </strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<!-- ════════════════════════════════════════════════════
     Template baris baru (hidden)
════════════════════════════════════════════════════ -->
<?php if (!$form_readonly): ?>
<script type="text/html" id="tmplBaris">
<tr class="baris-detail">
    <td class="text-center no-baris align-middle"></td>
    <td>
        <select name="id_kegiatan[]" class="form-control form-control-sm sel-kegiatan">
            <option value="">-- Pilih --</option>
            <?php foreach ($kegiatan_list as $kg): ?>
            <option value="<?= $kg['id'] ?>"><?= htmlspecialchars($kg['nama_kegiatan']) ?></option>
            <?php endforeach; ?>
            <option value="custom">+ Kegiatan Baru</option>
        </select>
        <input type="hidden" name="nama_kegiatan[]" class="inp-nama-kegiatan" value="">
        <input type="text" class="form-control form-control-sm mt-1 inp-custom-kegiatan"
               placeholder="Tulis nama kegiatan baru..." style="display:none">
    </td>
    <td><input type="date" name="tgl_kegiatan[]" class="form-control form-control-sm"></td>
    <td><input type="date" name="tgl_kasbon[]"   class="form-control form-control-sm"></td>
    <td>
        <input type="number" name="jml_peserta[]"
               class="form-control form-control-sm text-center" min="0" placeholder="0">
    </td>
    <td>
        <input type="text" name="qty_bisi[]"
               class="form-control form-control-sm angka-detail text-right" placeholder="0">
    </td>
    <td>
        <input type="text" name="qty_q235[]"
               class="form-control form-control-sm angka-detail text-right" placeholder="0">
    </td>
    <td>
        <input type="text" name="real_detail[]"
               class="form-control form-control-sm angka-detail inp-real text-right" placeholder="0">
    </td>
    <td>
        <input type="text" name="ket_detail[]"
               class="form-control form-control-sm" placeholder="Opsional...">
    </td>
    <td class="text-center align-middle">
        <button type="button" class="btn btn-xs btn-danger btn-hapus-baris">
            <i class="fas fa-times"></i>
        </button>
    </td>
</tr>
</script>
<?php endif; ?>


<!-- ════════════════════════════════════════════════════
     JavaScript
════════════════════════════════════════════════════ -->
<script>
$(function () {

    // ── Helpers format angka ──────────────────────────────────────
    function parseAngka(str) {
        return parseInt((str + '').replace(/\./g, '').replace(/,/g, '') || 0) || 0;
    }
    function formatAngka(n) {
        return n > 0 ? n.toLocaleString('id-ID') : '';
    }
    function formatRp(n) {
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    // ── Jika form readonly: hanya hitung ringkasan, skip semua interaksi edit ──
    var IS_READONLY = <?= $form_readonly ? 'true' : 'false' ?>;

    // Hitung ringkasan (selalu dijalankan, termasuk mode readonly)
    function hitungSemua() {
        var sumReal = 0, sumQtyBisi = 0, sumQtyQ235 = 0;
        $('#bodyDetail .baris-detail').each(function () {
            var $row = $(this);
            sumReal    += parseAngka($row.find('.inp-real').val());
            sumQtyBisi += parseAngka($row.find('[name="qty_bisi[]"]').val());
            sumQtyQ235 += parseAngka($row.find('[name="qty_q235[]"]').val());
        });
        var um     = parseAngka($('#umHeader').val());
        var refund = Math.max(0, um - sumReal);

        $('#refundHeaderDisplay').val(formatAngka(refund) || '0');
        $('#refundHeaderVal').val(refund);
        $('#sumQtyBisi').text(sumQtyBisi.toLocaleString('id-ID'));
        $('#sumQtyQ235').text(sumQtyQ235.toLocaleString('id-ID'));
        $('#sumReal').text(formatRp(sumReal));
        $('#sumRefund').text(formatRp(refund));
        $('#sumTotal').text(formatRp(sumReal));
        $('#badgeTotalDca').text('Total: ' + formatRp(sumReal));
    }

    function updateNomor() {
        $('#bodyDetail .baris-detail').each(function (i) {
            $(this).find('.no-baris').text(i + 1);
        });
    }

    // Hitung awal
    hitungSemua();
    updateNomor();

    // Stop di sini jika readonly
    if (IS_READONLY) return;

    // ── UM Header ────────────────────────────────────────────────
    $('#umHeader').on('input', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
        hitungSemua();
    });

    // ── Tambah baris ─────────────────────────────────────────────
    $('#btnTambahBaris').on('click', function () {
        var tmpl = $('#tmplBaris').html();
        $('#bodyDetail').append(tmpl);
        updateNomor();
        hitungSemua();
    });

    // Otomatis tambah 1 baris jika halaman tambah baru & kosong
    if ($('#bodyDetail .baris-detail').length === 0) {
        $('#btnTambahBaris').trigger('click');
    }

    // ── Hapus baris ───────────────────────────────────────────────
    $(document).on('click', '.btn-hapus-baris', function () {
        if ($('#bodyDetail .baris-detail').length <= 1) {
            Swal.fire('Info', 'Minimal harus ada 1 kegiatan.', 'info');
            return;
        }
        $(this).closest('tr').remove();
        updateNomor();
        hitungSemua();
    });

    // ── Pilih kegiatan dari dropdown ──────────────────────────────
    $(document).on('change', '.sel-kegiatan', function () {
        var $row     = $(this).closest('tr');
        var val      = $(this).val();
        var namaText = $(this).find('option:selected').text();
        var $custom  = $row.find('.inp-custom-kegiatan');
        var $hidden  = $row.find('.inp-nama-kegiatan');

        if (val === 'custom') {
            $custom.show().focus();
            $hidden.val('');
        } else {
            $custom.hide().val('');
            $hidden.val(val ? namaText : '');
        }
    });

    // ── Kegiatan custom → AJAX simpan ke DB ───────────────────────
    $(document).on('blur', '.inp-custom-kegiatan', function () {
        var $row    = $(this).closest('tr');
        var $hidden = $row.find('.inp-nama-kegiatan');
        var $sel    = $row.find('.sel-kegiatan');
        var nama    = $(this).val().trim();
        if (!nama) return;
        $hidden.val(nama);

        $.post('<?= base_url('kmt/dca/tambah_kegiatan') ?>', {
            nama_kegiatan: nama,
            '<?= $this->security->get_csrf_token_name() ?>':
                '<?= $this->security->get_csrf_hash() ?>'
        }, function (res) {
            try {
                var r = JSON.parse(res);
                if (r.status === 'ok' || r.status === 'exists') {
                    $('.sel-kegiatan').each(function () {
                        var $s  = $(this);
                        var ada = false;
                        $s.find('option').each(function () {
                            if ($(this).val() == r.id) ada = true;
                        });
                        if (!ada) {
                            $s.find('option[value="custom"]').before(
                                '<option value="' + r.id + '">' + r.nama + '</option>'
                            );
                        }
                    });
                    $sel.val(r.id);
                    $hidden.val(r.nama);
                }
            } catch(e) {}
        });
    });

    // ── Format input angka detail ─────────────────────────────────
    $(document).on('input', '.angka-detail', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
        hitungSemua();
    });

    // ── Validasi sebelum submit ───────────────────────────────────
    $('#formDca').on('submit', function (e) {
        if (parseAngka($('#umHeader').val()) <= 0) {
            e.preventDefault();
            Swal.fire('Perhatian', 'Uang Muka (UM) harus diisi.', 'warning');
            $('#umHeader').focus();
            return;
        }
        var valid = true;
        $('#bodyDetail .baris-detail').each(function () {
            var $row   = $(this);
            var nama   = $row.find('.inp-nama-kegiatan').val().trim();
            var custom = $row.find('.inp-custom-kegiatan').val().trim();
            if ($row.find('.sel-kegiatan').val() === 'custom' && !custom && !nama) {
                $row.find('.inp-custom-kegiatan').addClass('is-invalid').focus();
                valid = false;
            }
        });
        if (!valid) {
            e.preventDefault();
            Swal.fire('Perhatian', 'Lengkapi nama kegiatan custom yang belum diisi.', 'warning');
        }
    });

    // ════════════════════════════════════════════════════
    // Verifikasi dari halaman form (hanya lv 1 & 2)
    // ════════════════════════════════════════════════════
    <?php if ($is_edit && $lv <= 2): ?>
    var DCA_ID = <?= (int)$row['id'] ?>;

    // Tombol verifikasi (data belum verified)
    $('#btnVerifForm').on('click', function () {
        Swal.fire({
            title           : 'Verifikasi Data DCA?',
            html            : 'Data DCA <strong><?= htmlspecialchars(addslashes($row['uraian'])) ?></strong>'
                            + ' akan diverifikasi.<br>'
                            + '<small class="text-muted">Setelah diverifikasi, ABM tidak dapat mengedit.</small>',
            icon            : 'question',
            input           : 'textarea',
            inputPlaceholder: 'Catatan verifikasi (opsional)...',
            inputAttributes : { style: 'font-size:13px;' },
            showCancelButton   : true,
            confirmButtonColor : '#28a745',
            confirmButtonText  : '<i class="fas fa-check mr-1"></i> Ya, Verifikasi',
            cancelButtonText   : 'Batal',
        }).then(function (result) {
            if (result.isConfirmed) {
                kirimVerifForm('verifikasi', result.value || '');
            }
        });
    });

    // Tombol batalkan verifikasi (data sudah verified)
    $('#btnBatalVerifForm').on('click', function () {
        Swal.fire({
            title           : 'Batalkan Verifikasi?',
            html            : 'Data akan kembali terbuka untuk diedit oleh ABM.',
            icon            : 'warning',
            input           : 'textarea',
            inputPlaceholder: 'Alasan pembatalan (wajib diisi)...',
            inputValidator  : function(v) { if (!v.trim()) return 'Alasan wajib diisi!'; },
            showCancelButton   : true,
            confirmButtonColor : '#dc3545',
            confirmButtonText  : '<i class="fas fa-undo mr-1"></i> Batalkan Verifikasi',
            cancelButtonText   : 'Tutup',
        }).then(function (result) {
            if (result.isConfirmed) {
                kirimVerifForm('batal', result.value);
            }
        });
    });

    function kirimVerifForm(aksi, catatan) {
        $.ajax({
            url   : '<?= base_url('kmt/dca/ajax_verifikasi') ?>',
            method: 'POST',
            data  : {
                id     : DCA_ID,
                aksi   : aksi,
                catatan: catatan,
                '<?= $this->security->get_csrf_token_name() ?>':
                    '<?= $this->security->get_csrf_hash() ?>'
            },
            success: function (res) {
                try {
                    var r = JSON.parse(res);
                    if (r.status === 'ok') {
                        Swal.fire({
                            icon : 'success',
                            title: 'Berhasil',
                            text : r.msg,
                            timer: 1800,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.href = '<?= base_url('kmt/dca') ?>';
                        });
                    } else {
                        Swal.fire('Gagal', r.msg, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Koneksi gagal. Periksa jaringan.', 'error');
            }
        });
    }
    <?php endif; ?>

});
</script>