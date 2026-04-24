<!-- ================================================================
     views/content/kmt/dca/index.php  — VERSI + VERIFIKASI
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
                            <i class="fas fa-handshake text-info"></i>
                            Data DCA <small class="text-muted">KMT CORN</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dashboard') ?>">KMT</a></li>
                            <li class="breadcrumb-item active">DCA</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php $this->load->view('partial/main/alert') ?>

                <!-- ── Tombol aksi ── -->
                <div class="mb-3 d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <a href="<?= base_url('kmt/dca/tambah') ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah DCA
                        </a>
                        <a href="<?= base_url('kmt/dca/rekap')
                                . '?tahun='      . $tahun
                                . '&bulan='      . $bulan
                                . '&id_wilayah=' . $id_wilayah ?>"
                           class="btn btn-primary btn-sm ml-1">
                            <i class="fas fa-file-invoice mr-1"></i> Rekapitulasi
                        </a>
                    </div>
                    <div>
                        <a href="<?= base_url('kmt/dca/export')
                                . '?tahun='      . $tahun
                                . '&bulan='      . $bulan
                                . '&id_wilayah=' . $id_wilayah ?>"
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </a>
                    </div>
                </div>

                <!-- ── Filter ── -->
                <?php $this->load->view('partial/main/filter', [
                    'filter_url' => base_url('kmt/dca'),
                    'show_bulan' => true,
                ]); ?>

                <!-- ── Filter status verifikasi (khusus level 1 & 2) ── -->
                <?php if ($lv <= 2): ?>
                <div class="mb-3">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="<?= base_url('kmt/dca') . '?tahun=' . $tahun . '&bulan=' . $bulan . '&id_wilayah=' . $id_wilayah ?>"
                           class="btn <?= $status_verifikasi === '' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                            Semua
                            <span class="badge badge-light"><?= $jml_belum + $jml_sudah ?></span>
                        </a>
                        <a href="<?= base_url('kmt/dca') . '?tahun=' . $tahun . '&bulan=' . $bulan . '&id_wilayah=' . $id_wilayah . '&status_verifikasi=0' ?>"
                           class="btn <?= $status_verifikasi === '0' ? 'btn-warning' : 'btn-outline-warning' ?>">
                            <i class="fas fa-clock mr-1"></i> Belum Verifikasi
                            <span class="badge badge-light"><?= $jml_belum ?></span>
                        </a>
                        <a href="<?= base_url('kmt/dca') . '?tahun=' . $tahun . '&bulan=' . $bulan . '&id_wilayah=' . $id_wilayah . '&status_verifikasi=1' ?>"
                           class="btn <?= $status_verifikasi === '1' ? 'btn-success' : 'btn-outline-success' ?>">
                            <i class="fas fa-check-circle mr-1"></i> Sudah Verifikasi
                            <span class="badge badge-light"><?= $jml_sudah ?></span>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ── Summary cards ── -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="info-box bg-info shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Biaya DCA</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($total_biaya, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php if ($lv <= 2): ?>
                    <div class="col-md-4">
                        <div class="info-box bg-warning shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Menunggu Verifikasi</span>
                                <span class="info-box-number"><?= $jml_belum ?> data</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-success shadow-sm">
                            <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Sudah Diverifikasi</span>
                                <span class="info-box-number"><?= $jml_sudah ?> data</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- ── Tabel DCA ── -->
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-1"></i> Daftar DCA — <?= $tahun ?>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblDca"
                                   class="table table-bordered table-striped table-hover table-sm mb-0"
                                   style="font-size:13px;">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th>Wilayah</th>
                                        <th>MDO</th>
                                        <th>ABM</th>
                                        <th>Uraian</th>
                                        <th class="text-right">UM</th>
                                        <th class="text-right">Refund</th>
                                        <th class="text-right">Real Biaya</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($list as $i => $row):
                                    $verified  = (int)$row['status_verifikasi'] === 1;
                                    $can_edit  = !($lv === 3 && $verified);
                                    $can_hapus = !($verified && $lv > 1);
                                ?>
                                <tr class="<?= $verified ? '' : 'table-warning-light' ?>">
                                    <td class="align-middle"><?= $i + 1 ?></td>
                                    <td class="align-middle text-nowrap">
                                        <?= date('d/m/Y', strtotime($row['tanggal_dca'])) ?>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-secondary">
                                            <?= htmlspecialchars($row['nama_wilayah'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="align-middle"><?= htmlspecialchars($row['nama_mdo'] ?? '-') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($row['abm'] ?? '-') ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($row['uraian']) ?></td>
                                    <td class="text-right align-middle">
                                        <?= number_format($row['um'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-right align-middle text-danger">
                                        <?= $row['refund'] > 0 ? number_format($row['refund'], 0, ',', '.') : '-' ?>
                                    </td>
                                    <td class="text-right align-middle">
                                        <?= number_format($row['real_biaya'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-right align-middle font-weight-bold">
                                        <?= number_format($row['total_biaya'], 0, ',', '.') ?>
                                    </td>

                                    <!-- Kolom Status Verifikasi -->
                                    <td class="text-center align-middle">
                                        <?php if ($verified): ?>
                                            <span class="badge badge-success"
                                                  data-toggle="tooltip"
                                                  title="Oleh: <?= htmlspecialchars($row['nama_verifikator'] ?? '-') ?>&#10;<?= $row['verified_at'] ? date('d/m/Y H:i', strtotime($row['verified_at'])) : '' ?>&#10;<?= htmlspecialchars($row['verified_notes'] ?? '') ?>">
                                                <i class="fas fa-check-circle mr-1"></i> Terverifikasi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-warning text-dark">
                                                <i class="fas fa-clock mr-1"></i> Menunggu
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Kolom Aksi -->
                                    <td class="text-center align-middle text-nowrap">

                                        <!-- Edit: ABM tidak boleh edit data terverifikasi -->
                                        <?php if ($can_edit): ?>
                                        <a href="<?= base_url('kmt/dca/edit/' . $row['id']) ?>"
                                           class="btn btn-xs btn-warning"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-xs btn-secondary"
                                                title="Tidak dapat diedit — sudah diverifikasi"
                                                disabled>
                                            <i class="fas fa-lock"></i>
                                        </button>
                                        <?php endif; ?>

                                        <!-- Tombol Verifikasi (khusus level 1 & 2) -->
                                        <?php if ($lv <= 2): ?>
                                            <?php if (!$verified): ?>
                                            <button type="button"
                                                    class="btn btn-xs btn-success btn-verifikasi"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-uraian="<?= htmlspecialchars($row['uraian']) ?>"
                                                    title="Verifikasi data ini">
                                                <i class="fas fa-check"></i> Verifikasi
                                            </button>
                                            <?php else: ?>
                                            <button type="button"
                                                    class="btn btn-xs btn-outline-danger btn-batal-verifikasi"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-uraian="<?= htmlspecialchars($row['uraian']) ?>"
                                                    title="Batalkan verifikasi">
                                                <i class="fas fa-undo"></i> Batal
                                            </button>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- Hapus: tidak boleh hapus data terverifikasi (kecuali super) -->
                                        <?php if ($can_hapus): ?>
                                        <a href="<?= base_url('kmt/dca/hapus/' . $row['id']) ?>"
                                           class="btn btn-xs btn-danger btn-hapus"
                                           title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>

                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot style="background:#f4f4f4;font-weight:bold;">
                                    <tr>
                                        <td colspan="9" class="text-right">TOTAL:</td>
                                        <td class="text-right">
                                            <?= number_format($total_biaya, 0, ',', '.') ?>
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- /.container-fluid -->
        </section>
    </div><!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>Copyright &copy; 2022
            <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.
        </strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div><!-- /.wrapper -->


<!-- ================================================================
     Modal Verifikasi
================================================================ -->
<div class="modal fade" id="modalVerifikasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle mr-2"></i> Konfirmasi Verifikasi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    Anda akan memverifikasi data DCA:
                    <strong id="verif-uraian" class="text-info"></strong>
                </p>
                <p class="text-muted small mb-3">
                    Setelah diverifikasi, ABM tidak dapat mengedit data ini lagi.
                </p>
                <div class="form-group mb-0">
                    <label class="small">Catatan (opsional)</label>
                    <textarea id="verif-catatan" class="form-control form-control-sm"
                              rows="2" placeholder="Misal: Bukti lengkap, nominal sesuai..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
                <button type="button" class="btn btn-success btn-sm" id="btnKonfirmasiVerif">
                    <i class="fas fa-check mr-1"></i> Ya, Verifikasi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Batal Verifikasi -->
<div class="modal fade" id="modalBatalVerif" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-undo mr-2"></i> Batalkan Verifikasi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    Batalkan verifikasi DCA:
                    <strong id="batal-uraian" class="text-danger"></strong>
                </p>
                <p class="text-muted small mb-3">
                    Data akan kembali ke status <em>Belum Diverifikasi</em> dan ABM dapat mengedit lagi.
                </p>
                <div class="form-group mb-0">
                    <label class="small">Alasan pembatalan <span class="text-danger">*</span></label>
                    <textarea id="batal-catatan" class="form-control form-control-sm"
                              rows="2" placeholder="Tulis alasan pembatalan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btnKonfirmasiBatal">
                    <i class="fas fa-undo mr-1"></i> Batalkan Verifikasi
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ================================================================
     JavaScript
================================================================ -->
<script>
$(function () {

    // Inisialisasi DataTable
    var table = $('#tblDca').DataTable({
        responsive : true,
        pageLength : 25,
        order      : [[1, 'desc']],
        columnDefs : [
            { targets: [6,7,8,9], className: 'dt-right' },
            { targets: [10,11],   orderable: false }
        ],
        language: { url: '<?= base_url('assets/plugins/datatables/id.json') ?>' }
    });

    // Tooltip Bootstrap
    $('[data-toggle="tooltip"]').tooltip({ html: true });

    // ── Konfirmasi hapus ──────────────────────────────────────────
    $(document).on('click', '.btn-hapus', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({
            title: 'Hapus data ini?',
            icon : 'warning',
            showCancelButton    : true,
            confirmButtonColor  : '#d33',
            confirmButtonText   : 'Ya, Hapus!',
            cancelButtonText    : 'Batal'
        }).then(r => { if (r.isConfirmed) window.location.href = url; });
    });

    // ── Verifikasi ────────────────────────────────────────────────
    var pendingId   = null;
    var pendingAksi = null;

    $(document).on('click', '.btn-verifikasi', function () {
        pendingId   = $(this).data('id');
        pendingAksi = 'verifikasi';
        $('#verif-uraian').text($(this).data('uraian'));
        $('#verif-catatan').val('');
        $('#modalVerifikasi').modal('show');
    });

    $('#btnKonfirmasiVerif').on('click', function () {
        if (!pendingId) return;
        kirimVerifikasi(pendingId, 'verifikasi', $('#verif-catatan').val());
        $('#modalVerifikasi').modal('hide');
    });

    // ── Batal Verifikasi ──────────────────────────────────────────
    $(document).on('click', '.btn-batal-verifikasi', function () {
        pendingId = $(this).data('id');
        $('#batal-uraian').text($(this).data('uraian'));
        $('#batal-catatan').val('');
        $('#modalBatalVerif').modal('show');
    });

    $('#btnKonfirmasiBatal').on('click', function () {
        var catatan = $('#batal-catatan').val().trim();
        if (!catatan) {
            $('#batal-catatan').addClass('is-invalid').focus();
            return;
        }
        $('#batal-catatan').removeClass('is-invalid');
        kirimVerifikasi(pendingId, 'batal', catatan);
        $('#modalBatalVerif').modal('hide');
    });

    // ── AJAX verifikasi / batal ───────────────────────────────────
    function kirimVerifikasi(id, aksi, catatan) {
        $.ajax({
            url    : '<?= base_url('kmt/dca/ajax_verifikasi') ?>',
            method : 'POST',
            data   : {
                id     : id,
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
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', r.msg, 'error');
                    }
                } catch(e) {
                    Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Koneksi gagal. Periksa jaringan Anda.', 'error');
            }
        });
    }

});
</script>