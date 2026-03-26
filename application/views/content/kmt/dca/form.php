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
                $is_edit = isset($row);
                $action  = $is_edit
                    ? base_url('kmt/dca/update/' . $row['id'])
                    : base_url('kmt/dca/simpan');
                $lv = (int)$lv;

                // Detail existing (saat edit)
                $existing_detail = isset($detail) ? $detail : [];
                ?>

                <form action="<?= $action ?>" method="POST" id="formDca">
                    <?= form_open($action) ?>

                    <!-- ── Info Utama ── -->
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Informasi DCA</h3>
                        </div>
                        <div class="card-body">
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
                                                <?= (($is_edit && $row['id_wilayah'] == $w['id'])
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
                                        <label>ABM</label>
                                        <input type="text" name="abm"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit
                                                           ? htmlspecialchars($row['abm'] ?? '')
                                                           : $this->session->userdata('nama') ?>">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Keterangan Umum</label>
                                        <input type="text" name="uraian"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['uraian'] ?? '') : '' ?>"
                                               placeholder="Opsional...">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- ── Detail Kegiatan ── -->
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
                                    <thead style="background:#1f3864;color:#fff;">
                                        <tr>
                                            <th width="30">#</th>
                                            <th>Kegiatan <span class="text-danger">*</span></th>
                                            <th width="160">UM (Uang Muka)</th>
                                            <th width="160">Refund</th>
                                            <th width="160">Real Biaya</th>
                                            <th width="160">Total</th>
                                            <th width="180">Keterangan</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="bodyDetail">
                                        <?php if (!empty($existing_detail)): ?>
                                            <?php foreach ($existing_detail as $i => $det): ?>
                                            <tr class="baris-detail">
                                                <td class="text-center no-baris"><?= $i + 1 ?></td>
                                                <td>
                                                    <select name="id_kegiatan[]"
                                                            class="form-control form-control-sm sel-kegiatan">
                                                        <option value="">-- Pilih Kegiatan --</option>
                                                        <?php foreach ($kegiatan_list as $kg): ?>
                                                        <option value="<?= $kg['id'] ?>"
                                                            <?= ($det['id_kegiatan'] == $kg['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($kg['nama_kegiatan']) ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                        <option value="custom" <?= empty($det['id_kegiatan']) ? 'selected' : '' ?>>
                                                            + Kegiatan Baru (Custom)
                                                        </option>
                                                    </select>
                                                    <input type="hidden" name="nama_kegiatan[]"
                                                           class="inp-nama-kegiatan"
                                                           value="<?= htmlspecialchars($det['nama_kegiatan']) ?>">
                                                    <input type="text" class="form-control form-control-sm mt-1 inp-custom-kegiatan"
                                                           placeholder="Tulis nama kegiatan baru..."
                                                           style="display:<?= empty($det['id_kegiatan']) ? 'block' : 'none' ?>">
                                                </td>
                                                <td>
                                                    <input type="text" name="um_detail[]"
                                                           class="form-control form-control-sm angka-detail"
                                                           value="<?= $det['um'] > 0 ? number_format($det['um'], 0, ',', '.') : '' ?>"
                                                           placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="text" name="refund_detail[]"
                                                           class="form-control form-control-sm angka-detail inp-refund"
                                                           value="<?= $det['refund'] > 0 ? number_format($det['refund'], 0, ',', '.') : '' ?>"
                                                           placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="text" name="real_detail[]"
                                                           class="form-control form-control-sm angka-detail inp-real"
                                                           value="<?= $det['real_biaya'] > 0 ? number_format($det['real_biaya'], 0, ',', '.') : '' ?>"
                                                           placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm inp-total-baris"
                                                           value="<?= $det['total_biaya'] > 0 ? number_format($det['total_biaya'], 0, ',', '.') : '' ?>"
                                                           readonly style="background:#e8f4fd">
                                                </td>
                                                <td>
                                                    <input type="text" name="ket_detail[]"
                                                           class="form-control form-control-sm"
                                                           value="<?= htmlspecialchars($det['keterangan'] ?? '') ?>"
                                                           placeholder="Opsional...">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs btn-danger btn-hapus-baris">
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
                                <button type="button" class="btn btn-sm btn-outline-info" id="btnTambahBaris">
                                    <i class="fas fa-plus mr-1"></i> Tambah Kegiatan
                                </button>
                                <small class="text-muted ml-2">
                                    Pilih "Kegiatan Baru (Custom)" untuk menambah kegiatan yang tidak ada di daftar
                                </small>
                            </div>

                            <!-- Total keseluruhan -->
                            <div class="p-3 border-top">
                                <div class="row justify-content-end">
                                    <div class="col-md-4">
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <td class="font-weight-bold">Total UM</td>
                                                <td class="text-right" id="sumUm">Rp 0</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold text-danger">Total Refund</td>
                                                <td class="text-right text-danger" id="sumRefund">Rp 0</td>
                                            </tr>
                                            <tr>
                                                <td class="font-weight-bold">Total Real Biaya</td>
                                                <td class="text-right" id="sumReal">Rp 0</td>
                                            </tr>
                                            <tr style="background:#1f3864;color:#fff;">
                                                <td class="font-weight-bold">TOTAL BIAYA DCA</td>
                                                <td class="text-right font-weight-bold" id="sumTotal">Rp 0</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

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

<!-- Template baris (hidden) -->
<script type="text/html" id="tmplBaris">
<tr class="baris-detail">
    <td class="text-center no-baris"></td>
    <td>
        <select name="id_kegiatan[]" class="form-control form-control-sm sel-kegiatan">
            <option value="">-- Pilih Kegiatan --</option>
            <?php foreach ($kegiatan_list as $kg): ?>
            <option value="<?= $kg['id'] ?>"><?= htmlspecialchars($kg['nama_kegiatan']) ?></option>
            <?php endforeach; ?>
            <option value="custom">+ Kegiatan Baru (Custom)</option>
        </select>
        <input type="hidden" name="nama_kegiatan[]" class="inp-nama-kegiatan" value="">
        <input type="text" class="form-control form-control-sm mt-1 inp-custom-kegiatan"
               placeholder="Tulis nama kegiatan baru..." style="display:none">
    </td>
    <td>
        <input type="text" name="um_detail[]"
               class="form-control form-control-sm angka-detail" placeholder="0">
    </td>
    <td>
        <input type="text" name="refund_detail[]"
               class="form-control form-control-sm angka-detail inp-refund" placeholder="0">
    </td>
    <td>
        <input type="text" name="real_detail[]"
               class="form-control form-control-sm angka-detail inp-real" placeholder="0">
    </td>
    <td>
        <input type="text" class="form-control form-control-sm inp-total-baris"
               readonly style="background:#e8f4fd" placeholder="0">
    </td>
    <td>
        <input type="text" name="ket_detail[]"
               class="form-control form-control-sm" placeholder="Opsional...">
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-xs btn-danger btn-hapus-baris">
            <i class="fas fa-times"></i>
        </button>
    </td>
</tr>
</script>

<script>
$(function () {

    // ── Tambah baris baru ──────────────────────────────────────
    $('#btnTambahBaris').on('click', function () {
        var tmpl = $('#tmplBaris').html();
        $('#bodyDetail').append(tmpl);
        updateNomor();
        hitungSemua();
    });

    // Tambah baris otomatis jika belum ada baris
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

    // ── Input kegiatan custom — simpan ke hidden + AJAX save ──
    $(document).on('blur', '.inp-custom-kegiatan', function () {
        var $row    = $(this).closest('tr');
        var $hidden = $row.find('.inp-nama-kegiatan');
        var $sel    = $row.find('.sel-kegiatan');
        var nama    = $(this).val().trim();

        if (!nama) return;
        $hidden.val(nama);

        // Simpan ke DB via AJAX agar muncul di session berikutnya
        $.post('<?= base_url('kmt/dca/tambah_kegiatan') ?>', {
            nama_kegiatan: nama,
            '<?= $this->security->get_csrf_token_name() ?>':
                '<?= $this->security->get_csrf_hash() ?>'
        }, function (res) {
            var r = JSON.parse(res);
            if (r.status === 'ok' || r.status === 'exists') {
                // Tambah option baru ke semua dropdown jika belum ada
                var sudahAda = false;
                $sel.find('option').each(function () {
                    if ($(this).val() == r.id) { sudahAda = true; }
                });
                if (!sudahAda) {
                    $sel.find('option[value="custom"]').before(
                        '<option value="' + r.id + '">' + r.nama + '</option>'
                    );
                    // Tambah juga ke semua baris lain
                    $('.sel-kegiatan').not($sel).each(function () {
                        var adaDuplikat = false;
                        $(this).find('option').each(function () {
                            if ($(this).val() == r.id) adaDuplikat = true;
                        });
                        if (!adaDuplikat) {
                            $(this).find('option[value="custom"]').before(
                                '<option value="' + r.id + '">' + r.nama + '</option>'
                            );
                        }
                    });
                }
                $sel.val(r.id);
                $hidden.val(r.nama);
            }
        });
    });

    // ── Format angka & hitung total per baris ─────────────────
    $(document).on('input', '.angka-detail', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
        hitungBaris($(this).closest('tr'));
        hitungSemua();
    });

    function hitungBaris($row) {
        var real   = parseInt($row.find('.inp-real').val().replace(/\./g, '')   || 0);
        var refund = parseInt($row.find('.inp-refund').val().replace(/\./g, '') || 0);
        var total  = real - refund;
        $row.find('.inp-total-baris').val(total > 0 ? total.toLocaleString('id-ID') : '0');
    }

    function hitungSemua() {
        var sumUm = 0, sumRefund = 0, sumReal = 0, sumTotal = 0;

        $('#bodyDetail .baris-detail').each(function () {
            var $row = $(this);
            sumUm     += parseInt($row.find('[name="um_detail[]"]').val().replace(/\./g, '')     || 0);
            sumRefund += parseInt($row.find('[name="refund_detail[]"]').val().replace(/\./g, '') || 0);
            sumReal   += parseInt($row.find('[name="real_detail[]"]').val().replace(/\./g, '')   || 0);
            var tb = parseInt($row.find('.inp-total-baris').val().replace(/\./g, '') || 0);
            sumTotal += tb;
        });

        $('#sumUm').text('Rp ' + sumUm.toLocaleString('id-ID'));
        $('#sumRefund').text('Rp ' + sumRefund.toLocaleString('id-ID'));
        $('#sumReal').text('Rp ' + sumReal.toLocaleString('id-ID'));
        $('#sumTotal').text('Rp ' + sumTotal.toLocaleString('id-ID'));
        $('#badgeTotalDca').text('Total: Rp ' + sumTotal.toLocaleString('id-ID'));
    }

    function updateNomor() {
        $('#bodyDetail .baris-detail').each(function (i) {
            $(this).find('.no-baris').text(i + 1);
        });
    }

    // Hitung awal (saat edit)
    $('#bodyDetail .baris-detail').each(function () {
        hitungBaris($(this));
    });
    hitungSemua();
    updateNomor();

    // ── Validasi sebelum submit ───────────────────────────────
    $('#formDca').on('submit', function (e) {
        var valid = true;
        $('#bodyDetail .baris-detail').each(function () {
            var $row   = $(this);
            var nama   = $row.find('.inp-nama-kegiatan').val().trim();
            var custom = $row.find('.inp-custom-kegiatan').val().trim();
            var real   = $row.find('.inp-real').val();

            // Jika kegiatan custom tapi belum diisi
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