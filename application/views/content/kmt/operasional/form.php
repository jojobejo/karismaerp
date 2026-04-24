<!-- ================================================================
     views/content/kmt/operasional/form.php — VERSI + VERIFIKASI
     
     Aturan akses:
       lv 1 (super)  : form selalu aktif, bisa verifikasi/batal
       lv 2 (admkeu) : form selalu aktif, bisa verifikasi/batal
       lv 3 (ABM)    : form aktif hanya jika BELUM terverifikasi
                       jika sudah → READ-ONLY (view only)
================================================================ -->
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
                            <i class="fas fa-car text-warning"></i> <?= $page_title ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <a href="<?= base_url('kmt/operasional') ?>">Operasional</a>
                            </li>
                            <li class="breadcrumb-item active">
                                <?= isset($row) ? 'Edit' : 'Tambah' ?>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php $this->load->view('partial/main/alert') ?>

                <?php
                $is_edit     = isset($row);
                $lv          = (int)$lv;
                $is_verified = $is_edit && (int)($row['status_verifikasi'] ?? 0) === 1;

                // Hanya ABM (lv 3) + data terverifikasi yang READ-ONLY
                // lv 1 & 2 selalu bisa edit
                $form_readonly = ($lv === 3 && $is_verified);

                $action = $is_edit
                    ? base_url('kmt/operasional/update/' . $row['id'])
                    : base_url('kmt/operasional/simpan');

                $fields_biaya = [
                    'hotel'                 => 'Hotel',
                    'per_diem'              => 'Per Diem',
                    'entertainment'         => 'Entertainment',
                    'communication'         => 'Communication',
                    'atk'                   => 'ATK',
                    'gasoline'              => 'Gasoline',
                    'sparepart_service'     => 'Sparepart / Service Kendaraan',
                    'retribusi_toll_parkir' => 'Retribusi / Toll / Parkir',
                    'transportasi'          => 'Transportasi',
                    'pos_paket'             => 'Pos / Paket',
                    'tambah_angin'          => 'Tambah Angin',
                    'tambal_ban'            => 'Tambal Ban',
                    'indekost'              => 'Indekost',
                    'sewa_kendaraan'        => 'Sewa Kendaraan',
                    'lain_lain'             => 'Lain-lain',
                ];
                ?>

                <!-- ════════════════════════════════════════════
                     BANNER STATUS VERIFIKASI (hanya saat edit)
                ════════════════════════════════════════════ -->
                <?php if ($is_edit): ?>

                    <?php if ($is_verified): ?>
                    <!-- Data SUDAH terverifikasi -->
                    <div class="alert alert-success shadow-sm mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-check-circle fa-2x mr-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 font-weight-bold">Data Telah Diverifikasi</h6>
                                <div class="row" style="font-size:13px;">
                                    <div class="col-md-4">
                                        <i class="fas fa-user-check mr-1"></i>
                                        Verifikator:
                                        <strong>
                                            <?= htmlspecialchars($row['nama_verifikator'] ?? '-') ?>
                                        </strong>
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
                                        Catatan:
                                        <em><?= htmlspecialchars($row['verified_notes']) ?></em>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($lv === 3): ?>
                                <!-- ABM: hanya lihat -->
                                <div class="mt-2 pt-2 border-top border-success">
                                    <i class="fas fa-lock mr-1"></i>
                                    <strong>Data dikunci.</strong>
                                    Hubungi Adm Keuangan jika ada perubahan yang diperlukan.
                                </div>
                                <?php else: ?>
                                <!-- lv 1 & 2: bisa batalkan verifikasi -->
                                <div class="mt-2 pt-2 border-top border-success">
                                    <small class="text-muted d-block mb-1">
                                        Anda dapat membatalkan verifikasi jika ditemukan kesalahan.
                                        Setelah dibatalkan, ABM dapat mengedit kembali.
                                    </small>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            id="btnBatalVerifForm">
                                        <i class="fas fa-undo mr-1"></i> Batalkan Verifikasi
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php elseif ($lv <= 2): ?>
                    <!-- Data BELUM terverifikasi → banner untuk admkeu/super -->
                    <div class="alert alert-warning shadow-sm mb-3">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-hourglass-half fa-2x mr-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 font-weight-bold">Menunggu Verifikasi</h6>
                                <p class="mb-2 small">
                                    Periksa isian data di bawah. Jika sudah benar, klik
                                    <strong>Verifikasi</strong>.
                                    Anda tetap bisa mengedit data sebelum memverifikasi.
                                </p>
                                <button type="button" class="btn btn-sm btn-success"
                                        id="btnVerifForm">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Verifikasi Data Ini
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Riwayat Verifikasi (collapsed) -->
                    <?php if (!empty($log_verifikasi)): ?>
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header p-2">
                            <h3 class="card-title" style="font-size:13px;">
                                <i class="fas fa-history mr-1"></i> Riwayat Verifikasi
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
                                <?php foreach ($log_verifikasi as $log):
                                    $badge_map = [
                                        'verifikasi'       => ['success', 'Verifikasi'],
                                        'batal_verifikasi' => ['danger',  'Batal Verifikasi'],
                                        'reset_oleh_edit'  => ['secondary','Reset (Edit)'],
                                    ];
                                    [$bc, $bl] = $badge_map[$log['aksi']] ?? ['secondary', $log['aksi']];
                                ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td>
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

                <!-- ════════════════════════════════════════════
                     FORM
                ════════════════════════════════════════════ -->
                <?php if ($form_readonly): ?>
                <div class="callout callout-info mb-3">
                    <i class="fas fa-eye mr-1"></i>
                    Mode <strong>Lihat saja</strong> —
                    data sudah dikunci oleh Adm Keuangan.
                </div>
                <?php endif; ?>

                <form action="<?= $action ?>" method="POST" id="formOperasional"
                      <?= $form_readonly ? 'style="pointer-events:none;opacity:0.85;"' : '' ?>>
                    <?= form_open($action) ?>

                    <!-- Card 1: Informasi Umum -->
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-1"></i> Informasi Umum
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <!-- Tanggal -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['tanggal'] : date('Y-m-d') ?>"
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
                                                <?= (($is_edit && $row['id_wilayah'] == $w['id'])
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
                                        <label>MDO</label>
                                        <input type="text" name="nama_mdo"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit
                                                   ? htmlspecialchars($row['nama_mdo'] ?? '')
                                                   : '' ?>"
                                               placeholder="Nama MDO..."
                                               <?= $form_readonly ? 'readonly' : '' ?>>
                                    </div>
                                </div>

                                <!-- Nama ABM -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nama ABM <span class="text-danger">*</span></label>
                                        <input type="text" name="nama"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit
                                                   ? htmlspecialchars($row['nama'])
                                                   : $this->session->userdata('nama') ?>"
                                               <?= $form_readonly ? 'readonly' : 'required' ?>>
                                    </div>
                                </div>

                                <!-- UM -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Uang Muka (UM)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" name="um" id="umOperasional"
                                                   class="form-control form-control-sm angka-um"
                                                   value="<?= $is_edit && ($row['um'] ?? 0) > 0
                                                       ? number_format($row['um'], 0, ',', '.')
                                                       : '' ?>"
                                                   placeholder="0"
                                                   <?= $form_readonly ? 'readonly' : '' ?>>
                                        </div>
                                    </div>
                                </div>

                                <!-- Refund -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>
                                            Refund
                                            <small class="text-muted">(UM &minus; Total Biaya)</small>
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" id="refundOperasional"
                                                   class="form-control form-control-sm"
                                                   value="<?= $is_edit && ($row['refund'] ?? 0) > 0
                                                       ? number_format($row['refund'], 0, ',', '.')
                                                       : '0' ?>"
                                                   readonly
                                                   style="background:#fff3cd;font-weight:600;">
                                        </div>
                                        <input type="hidden" name="refund" id="refundHidden"
                                               value="<?= $is_edit ? ($row['refund'] ?? 0) : 0 ?>">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Rincian Biaya -->
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list-ul mr-1"></i> Rincian Biaya
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-warning" id="badgeTotal">Total: Rp 0</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($fields_biaya as $key => $label): ?>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label class="small"><?= $label ?></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text"
                                                   name="<?= $key ?>"
                                                   id="<?= $key ?>"
                                                   class="form-control form-control-sm angka-biaya"
                                                   value="<?= $is_edit && ($row[$key] ?? 0) > 0
                                                       ? number_format($row[$key], 0, ',', '.')
                                                       : '' ?>"
                                                   placeholder="0"
                                                   <?= $form_readonly ? 'readonly' : '' ?>>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Ringkasan Total & Refund -->
                            <div class="row mt-2">
                                <div class="col-md-4 offset-md-8">
                                    <div class="alert alert-warning mb-1 py-2">
                                        <strong>Total Biaya:</strong>
                                        <span id="totalBiaya" class="float-right font-weight-bold">
                                            Rp 0
                                        </span>
                                    </div>
                                    <div class="alert alert-info mb-0 py-2">
                                        <strong>Refund:</strong>
                                        <span id="refundDisplay" class="float-right font-weight-bold">
                                            Rp 0
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Tombol aksi -->
                    <div class="mb-4">
                        <?php if (!$form_readonly): ?>
                        <button type="submit" class="btn btn-warning" id="btnSimpan">
                            <i class="fas fa-save mr-1"></i>
                            <?= $is_edit ? 'Perbarui Data' : 'Simpan Data' ?>
                        </button>
                        <?php endif; ?>
                        <a href="<?= base_url('kmt/operasional') ?>"
                           class="btn btn-secondary ml-2">
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

<script>
$(function () {

    // ── Hitung total & refund ─────────────────────────────────────
    function hitungTotal() {
        var total = 0;
        $('.angka-biaya').each(function () {
            total += parseInt($(this).val().replace(/\./g, '') || 0);
        });
        var um     = parseInt($('#umOperasional').val().replace(/\./g, '') || 0);
        var refund = Math.max(0, um - total);

        $('#totalBiaya').text('Rp ' + total.toLocaleString('id-ID'));
        $('#badgeTotal').text('Total: Rp ' + total.toLocaleString('id-ID'));
        $('#refundOperasional').val(refund > 0 ? refund.toLocaleString('id-ID') : '0');
        $('#refundDisplay').text('Rp ' + refund.toLocaleString('id-ID'));
        $('#refundHidden').val(refund);
    }

    // Format angka saat input
    $('.angka-biaya').on('input', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
        hitungTotal();
    });

    $('.angka-um').on('input', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
        hitungTotal();
    });

    // Hitung awal
    hitungTotal();

    // ════════════════════════════════════════════════════
    // Tombol Verifikasi dari form (hanya lv 1 & 2)
    // ════════════════════════════════════════════════════
    <?php if ($is_edit && $lv <= 2): ?>
    var OP_ID = <?= (int)$row['id'] ?>;

    // Verifikasi
    $('#btnVerifForm').on('click', function () {
        Swal.fire({
            title           : 'Verifikasi Data Operasional?',
            html            : 'Data milik <strong><?= htmlspecialchars(addslashes($row['nama'])) ?></strong>'
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

    // Batal Verifikasi
    $('#btnBatalVerifForm').on('click', function () {
        Swal.fire({
            title           : 'Batalkan Verifikasi?',
            html            : 'Data akan kembali terbuka untuk diedit oleh ABM.',
            icon            : 'warning',
            input           : 'textarea',
            inputPlaceholder: 'Alasan pembatalan (wajib diisi)...',
            inputValidator  : function(v) {
                if (!v.trim()) return 'Alasan wajib diisi!';
            },
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
            url   : '<?= base_url('kmt/operasional/ajax_verifikasi') ?>',
            method: 'POST',
            data  : {
                id     : OP_ID,
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
                            icon : 'success', title: 'Berhasil', text: r.msg,
                            timer: 1800, showConfirmButton: false
                        }).then(function () {
                            window.location.href = '<?= base_url('kmt/operasional') ?>';
                        });
                    } else {
                        Swal.fire('Gagal', r.msg, 'error');
                    }
                } catch(e) {
                    Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Koneksi gagal.', 'error');
            }
        });
    }
    <?php endif; ?>

});
</script>