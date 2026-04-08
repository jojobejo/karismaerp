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
                $is_edit         = isset($row);
                $action          = $is_edit
                    ? base_url('kmt/dca/update/' . $row['id'])
                    : base_url('kmt/dca/simpan');
                $lv              = (int)$lv;
                $existing_detail = isset($detail) ? $detail : [];
                ?>

                <form action="<?= $action ?>" method="POST" id="formDca">
                    <?= form_open($action) ?>

                    <!-- ═══════════════════════════════════════════
                         CARD 1 — Informasi DCA
                    ═══════════════════════════════════════════ -->
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi DCA</h3>
                        </div>
                        <div class="card-body">

                            <!-- Baris 1: Tanggal | Wilayah | MDO | ABM -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal DCA <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_dca"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['tanggal_dca'] : date('Y-m-d') ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Wilayah <span class="text-danger">*</span></label>
                                        <select name="id_wilayah" class="form-control form-control-sm"
                                                <?= $lv === 3 ? 'disabled' : '' ?> required>
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
                                        <?php if ($lv === 3): ?>
                                        <input type="hidden" name="id_wilayah"
                                               value="<?= $is_edit ? $row['id_wilayah'] : $id_wilayah_user ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>MDO <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_mdo"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['nama_mdo'] ?? '') : '' ?>"
                                               placeholder="Nama MDO..." required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>ABM</label>
                                        <input type="text" name="abm"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit
                                                           ? htmlspecialchars($row['abm'] ?? '')
                                                           : $this->session->userdata('nama') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Baris 2: UM | Refund Otomatis | Keterangan Umum -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Uang Muka (UM) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" name="um_header" id="umHeader"
                                                   class="form-control form-control-sm angka-header"
                                                   value="<?= $is_edit ? number_format($row['um'] ?? 0, 0, ',', '.') : '' ?>"
                                                   placeholder="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Refund <small class="text-muted">(otomatis: UM &minus; Real Biaya)</small></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" id="refundHeaderDisplay"
                                                   class="form-control form-control-sm"
                                                   value="<?= $is_edit ? number_format($row['refund'] ?? 0, 0, ',', '.') : '0' ?>"
                                                   readonly style="background:#fff3cd;font-weight:600;">
                                        </div>
                                        <!-- hidden untuk dikirim ke server -->
                                        <input type="hidden" name="refund_header" id="refundHeaderVal"
                                               value="<?= $is_edit ? ($row['refund'] ?? 0) : 0 ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Keterangan Umum</label>
                                        <input type="text" name="uraian"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['uraian'] ?? '') : '' ?>"
                                               placeholder="Opsional...">
                                    </div>
                                </div>
                            </div>

                        </div><!-- /.card-body -->
                    </div><!-- /.card -->

                    <!-- ═══════════════════════════════════════════
                         CARD 2 — Detail Biaya per Kegiatan
                    ═══════════════════════════════════════════ -->
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
                                <table class="table table-sm table-bordered mb-0" id="tblDetail">
                                    <thead style="background:#1f3864;color:#fff;font-size:12px;">
                                        <tr>
                                            <th width="28">#</th>
                                            <th width="170">Kegiatan <span class="text-danger">*</span></th>
                                            <th width="120">Tgl Kegiatan</th>
                                            <th width="120">Tgl Kasbon</th>
                                            <th width="70" class="text-center">Peserta</th>
                                            <th width="110" class="text-center">Qty Bisi</th>
                                            <th width="110" class="text-center">Qty Q235</th>
                                            <th width="140">Real Biaya</th>
                                            <th width="160">Keterangan</th>
                                            <th width="36"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyDetail">

                                        <?php if (!empty($existing_detail)): ?>
                                        <?php foreach ($existing_detail as $i => $det): ?>
                                        <tr class="baris-detail">
                                            <td class="text-center no-baris align-middle"><?= $i + 1 ?></td>

                                            <!-- Kegiatan -->
                                            <td>
                                                <select name="id_kegiatan[]"
                                                        class="form-control form-control-sm sel-kegiatan">
                                                    <option value="">-- Pilih --</option>
                                                    <?php foreach ($kegiatan_list as $kg): ?>
                                                    <option value="<?= $kg['id'] ?>"
                                                        <?= ($det['id_kegiatan'] == $kg['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($kg['nama_kegiatan']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                    <option value="custom"
                                                        <?= empty($det['id_kegiatan']) ? 'selected' : '' ?>>
                                                        + Kegiatan Baru
                                                    </option>
                                                </select>
                                                <input type="hidden" name="nama_kegiatan[]"
                                                       class="inp-nama-kegiatan"
                                                       value="<?= htmlspecialchars($det['nama_kegiatan']) ?>">
                                                <input type="text"
                                                       class="form-control form-control-sm mt-1 inp-custom-kegiatan"
                                                       placeholder="Tulis nama kegiatan baru..."
                                                       style="display:<?= empty($det['id_kegiatan']) ? 'block' : 'none' ?>">
                                            </td>

                                            <!-- Tgl Kegiatan -->
                                            <td>
                                                <input type="date" name="tgl_kegiatan[]"
                                                       class="form-control form-control-sm"
                                                       value="<?= $det['tgl_kegiatan'] ?? '' ?>">
                                            </td>

                                            <!-- Tgl Kasbon -->
                                            <td>
                                                <input type="date" name="tgl_kasbon[]"
                                                       class="form-control form-control-sm"
                                                       value="<?= $det['tgl_kasbon'] ?? '' ?>">
                                            </td>

                                            <!-- Jumlah Peserta -->
                                            <td>
                                                <input type="number" name="jml_peserta[]"
                                                       class="form-control form-control-sm text-center"
                                                       value="<?= (int)($det['jml_peserta'] ?? 0) ?>"
                                                       min="0" placeholder="0">
                                            </td>

                                            <!-- Qty Bisi -->
                                            <td>
                                                <input type="text" name="qty_bisi[]"
                                                       class="form-control form-control-sm angka-detail text-right"
                                                       value="<?= ($det['qty_bisi'] ?? 0) > 0 ? number_format($det['qty_bisi'], 0, ',', '.') : '' ?>"
                                                       placeholder="0">
                                            </td>

                                            <!-- Qty Q235 -->
                                            <td>
                                                <input type="text" name="qty_q235[]"
                                                       class="form-control form-control-sm angka-detail text-right"
                                                       value="<?= ($det['qty_q235'] ?? 0) > 0 ? number_format($det['qty_q235'], 0, ',', '.') : '' ?>"
                                                       placeholder="0">
                                            </td>

                                            <!-- Real Biaya -->
                                            <td>
                                                <input type="text" name="real_detail[]"
                                                       class="form-control form-control-sm angka-detail inp-real text-right"
                                                       value="<?= ($det['real_biaya'] ?? 0) > 0 ? number_format($det['real_biaya'], 0, ',', '.') : '' ?>"
                                                       placeholder="0">
                                            </td>

                                            <!-- Keterangan -->
                                            <td>
                                                <input type="text" name="ket_detail[]"
                                                       class="form-control form-control-sm"
                                                       value="<?= htmlspecialchars($det['keterangan'] ?? '') ?>"
                                                       placeholder="Opsional...">
                                            </td>

                                            <!-- Hapus -->
                                            <td class="text-center align-middle">
                                                <button type="button"
                                                        class="btn btn-xs btn-danger btn-hapus-baris">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>

                                    </tbody>
                                </table>
                            </div>

                            <!-- Tombol tambah baris -->
                            <div class="p-2 border-top">
                                <button type="button" class="btn btn-sm btn-outline-info"
                                        id="btnTambahBaris">
                                    <i class="fas fa-plus mr-1"></i> Tambah Kegiatan
                                </button>
                                <small class="text-muted ml-2">
                                    Pilih "+ Kegiatan Baru" untuk kegiatan yang tidak ada di daftar
                                </small>
                            </div>

                            <!-- Ringkasan total -->
                            <div class="p-3 border-top">
                                <div class="row justify-content-end">
                                    <div class="col-md-5 col-lg-4">
                                        <table class="table table-sm mb-0"
                                               style="font-size:13px;">
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
                        <button type="submit" class="btn btn-info" id="btnSimpan">
                            <i class="fas fa-save mr-1"></i>
                            <?= $is_edit ? 'Perbarui Data' : 'Simpan Data' ?>
                        </button>
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

<!-- ═══════════════════════════════════════════════════════════
     Template baris baru (hidden)
═══════════════════════════════════════════════════════════ -->
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
    <td>
        <input type="date" name="tgl_kegiatan[]" class="form-control form-control-sm">
    </td>
    <td>
        <input type="date" name="tgl_kasbon[]" class="form-control form-control-sm">
    </td>
    <td>
        <input type="number" name="jml_peserta[]"
               class="form-control form-control-sm text-center"
               min="0" placeholder="0">
    </td>
    <td>
        <input type="text" name="qty_bisi[]"
               class="form-control form-control-sm angka-detail text-right"
               placeholder="0">
    </td>
    <td>
        <input type="text" name="qty_q235[]"
               class="form-control form-control-sm angka-detail text-right"
               placeholder="0">
    </td>
    <td>
        <input type="text" name="real_detail[]"
               class="form-control form-control-sm angka-detail inp-real text-right"
               placeholder="0">
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

<!-- ═══════════════════════════════════════════════════════════
     JavaScript
═══════════════════════════════════════════════════════════ -->
<script>
$(function () {

    // ── Format angka: helper ──────────────────────────────────
    function parseAngka(str) {
        return parseInt((str + '').replace(/\./g, '').replace(/,/g, '') || 0) || 0;
    }
    function formatAngka(n) {
        return n > 0 ? n.toLocaleString('id-ID') : '';
    }
    function formatRp(n) {
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    // ── UM Header — format & hitung ulang ────────────────────
    $('#umHeader').on('input', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
        hitungSemua();
    });

    // ── Tambah baris baru ─────────────────────────────────────
    $('#btnTambahBaris').on('click', function () {
        var tmpl = $('#tmplBaris').html();
        $('#bodyDetail').append(tmpl);
        updateNomor();
        hitungSemua();
    });

    // Otomatis tambah 1 baris jika belum ada (tambah baru)
    if ($('#bodyDetail .baris-detail').length === 0) {
        $('#btnTambahBaris').trigger('click');
    }

    // ── Hapus baris ───────────────────────────────────────────
    $(document).on('click', '.btn-hapus-baris', function () {
        if ($('#bodyDetail .baris-detail').length <= 1) {
            Swal.fire('Info', 'Minimal harus ada 1 kegiatan.', 'info');
            return;
        }
        $(this).closest('tr').remove();
        updateNomor();
        hitungSemua();
    });

    // ── Pilih kegiatan dari dropdown ──────────────────────────
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

    // ── Kegiatan custom: simpan ke hidden + AJAX ke DB ────────
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
                    // Tambah option ke semua dropdown jika belum ada
                    $('.sel-kegiatan').each(function () {
                        var $s   = $(this);
                        var ada  = false;
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

    // ── Format input angka detail ─────────────────────────────
    $(document).on('input', '.angka-detail', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
        hitungSemua();
    });

    // ── Hitung semua total ────────────────────────────────────
    function hitungSemua() {
        var sumReal     = 0;
        var sumQtyBisi  = 0;
        var sumQtyQ235  = 0;

        $('#bodyDetail .baris-detail').each(function () {
            var $row = $(this);
            sumReal    += parseAngka($row.find('.inp-real').val());
            sumQtyBisi += parseAngka($row.find('[name="qty_bisi[]"]').val());
            sumQtyQ235 += parseAngka($row.find('[name="qty_q235[]"]').val());
        });

        var um     = parseAngka($('#umHeader').val());
        var refund = Math.max(0, um - sumReal);

        // Update field refund header (display + hidden)
        $('#refundHeaderDisplay').val(formatAngka(refund) || '0');
        $('#refundHeaderVal').val(refund);

        // Update ringkasan
        $('#sumQtyBisi').text(sumQtyBisi.toLocaleString('id-ID'));
        $('#sumQtyQ235').text(sumQtyQ235.toLocaleString('id-ID'));
        $('#sumReal').text(formatRp(sumReal));
        $('#sumRefund').text(formatRp(refund));
        $('#sumTotal').text(formatRp(sumReal));
        $('#badgeTotalDca').text('Total: ' + formatRp(sumReal));
    }

    // ── Nomor urut baris ──────────────────────────────────────
    function updateNomor() {
        $('#bodyDetail .baris-detail').each(function (i) {
            $(this).find('.no-baris').text(i + 1);
        });
    }

    // Hitung awal (saat edit — data existing sudah terisi)
    hitungSemua();
    updateNomor();

    // ── Validasi sebelum submit ───────────────────────────────
    $('#formDca').on('submit', function (e) {

        // Validasi UM wajib diisi
        if (parseAngka($('#umHeader').val()) <= 0) {
            e.preventDefault();
            Swal.fire('Perhatian', 'Uang Muka (UM) harus diisi.', 'warning');
            $('#umHeader').focus();
            return;
        }

        // Validasi kegiatan custom belum diisi nama
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

});
</script>