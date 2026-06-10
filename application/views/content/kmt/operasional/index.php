<!-- ================================================================
     views/content/kmt/operasional/index.php — VERSI + VERIFIKASI
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
                            <i class="fas fa-car text-warning"></i>
                            Biaya Operasional <small class="text-muted">KMT CORN</small>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/dashboard') ?>">KMT</a></li>
                            <li class="breadcrumb-item active">Operasional</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php $this->load->view('partial/main/alert') ?>

                <!-- ── Tombol aksi ── -->
                <div class="mb-3 d-flex justify-content-between flex-wrap">
                    <div>
                        <?php if ($lv !== 2): ?>
                        <a href="<?= base_url('kmt/operasional/tambah') ?>"
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Biaya
                        </a>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="<?= base_url('kmt/operasional/export')
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
                    'filter_url' => base_url('kmt/operasional'),
                    'show_bulan' => true,
                ]); ?>

                <!-- ── Filter status verifikasi (lv 1 & 2) ── -->
                <?php if ($lv <= 2): ?>
                <div class="mb-3">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="<?= base_url('kmt/operasional') . '?tahun=' . $tahun . '&bulan=' . $bulan . '&id_wilayah=' . $id_wilayah ?>"
                           class="btn <?= $status_verifikasi === '' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                            Semua
                            <span class="badge badge-light"><?= $jml_belum + $jml_sudah ?></span>
                        </a>
                        <a href="<?= base_url('kmt/operasional') . '?tahun=' . $tahun . '&bulan=' . $bulan . '&id_wilayah=' . $id_wilayah . '&status_verifikasi=0' ?>"
                           class="btn <?= $status_verifikasi === '0' ? 'btn-warning' : 'btn-outline-warning' ?>">
                            <i class="fas fa-clock mr-1"></i> Belum Verifikasi
                            <span class="badge badge-light"><?= $jml_belum ?></span>
                        </a>
                        <a href="<?= base_url('kmt/operasional') . '?tahun=' . $tahun . '&bulan=' . $bulan . '&id_wilayah=' . $id_wilayah . '&status_verifikasi=1' ?>"
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
                        <div class="info-box bg-warning shadow-sm">
                            <span class="info-box-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Biaya Operasional</span>
                                <span class="info-box-number">
                                    Rp <?= number_format($total_biaya, 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php if ($lv <= 2): ?>
                    <div class="col-md-4">
                        <div class="info-box bg-danger shadow-sm">
                            <span class="info-box-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Menunggu Verifikasi</span>
                                <span class="info-box-number"><?= $jml_belum ?> data</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-success shadow-sm">
                            <span class="info-box-icon">
                                <i class="fas fa-check-double"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Sudah Diverifikasi</span>
                                <span class="info-box-number"><?= $jml_sudah ?> data</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- ── Tabel ── -->
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-table mr-1"></i>
                            Daftar Biaya Operasional — <?= $tahun ?>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tblOperasional"
                                   class="table table-bordered table-striped table-hover table-sm mb-0"
                                   style="font-size:13px;">
                                <thead style="background:#1f3864;color:#fff;">
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th>Waktu Input</th>
                                        <th>Wilayah</th>
                                        <th>Nama ABM</th>
                                        <th>MDO</th>
                                        <th class="text-right">Hotel</th>
                                        <th class="text-right">Per Diem</th>
                                        <th class="text-right">Entertain</th>
                                        <th class="text-right">Gasoline</th>
                                        <th class="text-right">Transportasi</th>
                                        <th class="text-right">Lain-lain</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($list as $i => $row):
                                    $verified  = (int)$row['status_verifikasi'] === 1;
                                    // ABM tidak bisa edit jika sudah terverifikasi
                                    $can_edit  = !($lv === 3 && $verified);
                                    $can_hapus = !($verified && $lv > 1);
                                ?>
                                <tr>
                                    <td class="align-middle"><?= $i + 1 ?></td>
                                    <td class="align-middle text-nowrap">
                                        <?= date('d/m/Y', strtotime($row['tanggal'])) ?>
                                    </td>
                                    <td class="align-middle text-nowrap">
                                        <?= !empty($row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-' ?>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-secondary">
                                            <?= htmlspecialchars($row['nama_wilayah'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <?= htmlspecialchars($row['nama']) ?>
                                    </td>
                                    <td class="align-middle">
                                        <?= htmlspecialchars($row['nama_mdo'] ?? '-') ?>
                                    </td>
                                    <td class="text-right align-middle">
                                        <?= $row['hotel'] > 0 ? number_format($row['hotel'],0,',','.') : '-' ?>
                                    </td>
                                    <td class="text-right align-middle">
                                        <?= $row['per_diem'] > 0 ? number_format($row['per_diem'],0,',','.') : '-' ?>
                                    </td>
                                    <td class="text-right align-middle">
                                        <?= $row['entertainment'] > 0 ? number_format($row['entertainment'],0,',','.') : '-' ?>
                                    </td>
                                    <td class="text-right align-middle">
                                        <?= $row['gasoline'] > 0 ? number_format($row['gasoline'],0,',','.') : '-' ?>
                                    </td>
                                    <td class="text-right align-middle">
                                        <?= $row['transportasi'] > 0 ? number_format($row['transportasi'],0,',','.') : '-' ?>
                                    </td>
                                    <td class="text-right align-middle">
                                        <?= $row['lain_lain'] > 0 ? number_format($row['lain_lain'],0,',','.') : '-' ?>
                                    </td>
                                    <td class="text-right align-middle font-weight-bold">
                                        <?= number_format($row['total_biaya'],0,',','.') ?>
                                    </td>

                                    <!-- Status Verifikasi -->
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

                                    <!-- Aksi -->
                                    <td class="text-center align-middle text-nowrap">

                                        <?php if ($lv === 3): ?>
                                            <?php if ($can_edit): ?>
                                            <!-- ABM: belum terverifikasi → edit -->
                                            <a href="<?= base_url('kmt/operasional/edit/' . $row['id']) ?>"
                                               class="btn btn-xs btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= base_url('kmt/operasional/hapus/' . $row['id']) ?>"
                                               class="btn btn-xs btn-danger btn-hapus" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php else: ?>
                                            <!-- ABM: sudah terverifikasi → hanya lihat -->
                                            <a href="<?= base_url('kmt/operasional/edit/' . $row['id']) ?>"
                                               class="btn btn-xs btn-info" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <!-- lv 1 & 2: selalu bisa edit -->
                                            <a href="<?= base_url('kmt/operasional/edit/' . $row['id']) ?>"
                                               class="btn btn-xs btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Tombol Verifikasi / Batal (lv 1 & 2) -->
                                            <?php if (!$verified): ?>
                                            <button type="button"
                                                    class="btn btn-xs btn-success btn-verifikasi"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                                    title="Verifikasi">
                                                <i class="fas fa-check"></i> Verifikasi
                                            </button>
                                            <?php else: ?>
                                            <button type="button"
                                                    class="btn btn-xs btn-outline-danger btn-batal-verifikasi"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                                    title="Batalkan Verifikasi">
                                                <i class="fas fa-undo"></i> Batal
                                            </button>
                                            <?php endif; ?>

                                            <!-- Hapus hanya jika belum terverifikasi atau super -->
                                            <?php if ($can_hapus): ?>
                                            <a href="<?= base_url('kmt/operasional/hapus/' . $row['id']) ?>"
                                               class="btn btn-xs btn-danger btn-hapus" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot style="background:#f4f4f4;font-weight:bold;">
                                    <tr>
                                        <td colspan="12" class="text-right">TOTAL:</td>
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

<!-- ── Modal Verifikasi ── -->
<div class="modal fade" id="modalVerifikasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle mr-2"></i> Konfirmasi Verifikasi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Verifikasi data operasional milik:</p>
                <p class="font-weight-bold text-info" id="verif-nama"></p>
                <p class="text-muted small mb-3">
                    Setelah diverifikasi, ABM tidak dapat mengedit data ini lagi.
                </p>
                <div class="form-group mb-0">
                    <label class="small">Catatan <span class="text-muted">(opsional)</span></label>
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

<!-- ── Modal Batal Verifikasi ── -->
<div class="modal fade" id="modalBatalVerif" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-undo mr-2"></i> Batalkan Verifikasi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Batalkan verifikasi data operasional milik:</p>
                <p class="font-weight-bold text-danger" id="batal-nama"></p>
                <p class="text-muted small mb-3">
                    Data akan kembali ke status <em>Belum Diverifikasi</em>
                    dan ABM dapat mengedit kembali.
                </p>
                <div class="form-group mb-0">
                    <label class="small">
                        Alasan pembatalan <span class="text-danger">*</span>
                    </label>
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

<script>
$(function () {

    // DataTable
    $('#tblOperasional').DataTable({
        responsive  : true,
        pageLength  : 25,
        order       : [[1, 'desc']],
        columnDefs  : [
            { targets: [6,7,8,9,10,11,12], className: 'dt-right' },
            { targets: [13,14], orderable: false }
        ],
        language: { url: '<?= base_url('assets/plugins/datatables/id.json') ?>' }
    });

    // Tooltip
    $('[data-toggle="tooltip"]').tooltip({ html: true });

    // ── Konfirmasi hapus ──────────────────────────────────────────
    $(document).on('click', '.btn-hapus', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        Swal.fire({
            title: 'Hapus data ini?', icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(r => { if (r.isConfirmed) window.location.href = url; });
    });

    // ── Buka modal verifikasi ─────────────────────────────────────
    var pendingId = null;

    $(document).on('click', '.btn-verifikasi', function () {
        pendingId = $(this).data('id');
        $('#verif-nama').text($(this).data('nama'));
        $('#verif-catatan').val('');
        $('#modalVerifikasi').modal('show');
    });

    $('#btnKonfirmasiVerif').on('click', function () {
        if (!pendingId) return;
        kirimVerifikasi(pendingId, 'verifikasi', $('#verif-catatan').val());
        $('#modalVerifikasi').modal('hide');
    });

    // ── Buka modal batal verifikasi ───────────────────────────────
    $(document).on('click', '.btn-batal-verifikasi', function () {
        pendingId = $(this).data('id');
        $('#batal-nama').text($(this).data('nama'));
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

    // ── AJAX verifikasi ───────────────────────────────────────────
    function kirimVerifikasi(id, aksi, catatan) {
        $.ajax({
            url   : '<?= base_url('kmt/operasional/ajax_verifikasi') ?>',
            method: 'POST',
            data  : {
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
                            icon : 'success', title: 'Berhasil', text: r.msg,
                            timer: 1800, showConfirmButton: false
                        }).then(() => location.reload());
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

});
</script>
