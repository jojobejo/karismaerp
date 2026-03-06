<!-- view/content/logistik/bongkaran/index.php -->
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

                <!-- Flash -->
                <?php if ($this->session->flashdata('success')) : ?>
                    <div class="alert alert-success alert-dismissible">
                        <i class="fas fa-check-circle mr-1"></i><?= $this->session->flashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <!-- Tombol aksi atas -->
                <div class="row mb-3">
                    <?php if ($jobdesk === 'MANAGERWH') : ?>
                    <div class="col-auto">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#modalBuatBongkaran">
                            <i class="fas fa-plus"></i> Buat Bongkaran
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('bongkaran/arsip') ?>" class="btn btn-secondary">
                            <i class="fas fa-archive"></i> Lihat Arsip
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title"><i class="fas fa-dolly mr-2"></i> Data Bongkaran</h3>
                    </div>
                    <div class="card-body">

                        <table class="table table-bordered table-hover table-sm" id="tabelBongkaran">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th>Kode</th>
                                    <th>Keterangan</th>
                                    <th>Jalur KK</th>
                                    <th>Jalur LK</th>
                                    <th>Checker</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Progres</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list)) : ?>
                                <?php foreach ($list as $row) : ?>
                                <?php
                                    $status     = $row['status'];
                                    $is_done    = ($status === 'DONE');
                                    $is_taken   = !empty($row['nik_checker']);
                                    $is_my_job  = ($row['nik_checker'] === $nik);
                                    $progres    = (int)($row['progres'] ?? 0);

                                    $badge = [
                                        'MENUNGGU'         => 'badge-secondary',
                                        'PROSES'           => 'badge-warning',
                                        'PENYIAPAN_BARANG' => 'badge-info',
                                        'CETAK_DO'         => 'badge-primary',
                                        'DONE'             => 'badge-success',
                                    ][$status] ?? 'badge-secondary';
                                ?>
                                <tr class="<?= $is_done ? 'table-success' : '' ?>">
                                    <td><strong><?= htmlspecialchars($row['kode_bongkar']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>

                                    <!-- Jalur KK: hanya Admlog yang bisa edit -->
                                    <td>
                                        <?php if ($jobdesk === 'LOGISTIK' && !$is_done) : ?>
                                            <input type="text" class="form-control form-control-sm input-jalur"
                                                   data-id="<?= $row['id'] ?>" data-field="jalur_kk"
                                                   value="<?= htmlspecialchars($row['jalur_kk'] ?? '') ?>"
                                                   placeholder="Jalur KK">
                                        <?php else : ?>
                                            <?= htmlspecialchars($row['jalur_kk'] ?? '-') ?>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Jalur LK: hanya Admlog yang bisa edit -->
                                    <td>
                                        <?php if ($jobdesk === 'LOGISTIK' && !$is_done) : ?>
                                            <input type="text" class="form-control form-control-sm input-jalur"
                                                   data-id="<?= $row['id'] ?>" data-field="jalur_lk"
                                                   value="<?= htmlspecialchars($row['jalur_lk'] ?? '') ?>"
                                                   placeholder="Jalur LK">
                                        <?php else : ?>
                                            <?= htmlspecialchars($row['jalur_lk'] ?? '-') ?>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= $is_taken ? htmlspecialchars($row['nm_checker']) : '<span class="text-muted">-</span>' ?></td>
                                    <td><?= $is_taken ? htmlspecialchars($row['waktu_mulai'] ?? '-') : '-' ?></td>
                                    <td><?= ($row['status_checker'] === 'DONE') ? htmlspecialchars($row['waktu_selesai'] ?? '-') : '-' ?></td>

                                    <!-- Progres bar -->
                                    <td style="min-width:120px;">
                                        <div class="progress" style="height:18px;">
                                            <div class="progress-bar <?= $progres == 100 ? 'bg-success' : 'bg-warning' ?>"
                                                 style="width:<?= $progres ?>%">
                                                <?= $progres ?>%
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status: Admlog bisa ubah -->
                                    <td class="text-center">
                                        <?php if ($jobdesk === 'LOGISTIK' && !$is_done) : ?>
                                            <select class="form-control form-control-sm select-status"
                                                    data-id="<?= $row['id'] ?>">
                                                <?php foreach (['MENUNGGU','PROSES','PENYIAPAN_BARANG','CETAK_DO','DONE'] as $s) : ?>
                                                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>>
                                                        <?= str_replace('_', ' ', $s) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else : ?>
                                            <span class="badge <?= $badge ?>">
                                                <?= str_replace('_', ' ', $status) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Kolom Aksi -->
                                    <td class="text-center" style="min-width:160px;">

                                        <?php if ($jobdesk === 'CHECKER') : ?>
                                            <?php if (!$is_taken) : ?>
                                                <!-- Belum ada checker: tampilkan Start -->
                                                <button class="btn btn-sm btn-success btn-start"
                                                        data-id="<?= $row['id'] ?>">
                                                    <i class="fas fa-play"></i> Start
                                                </button>
                                            <?php elseif ($is_my_job && $row['status_checker'] === 'PROSES') : ?>
                                                <!-- Job saya, sedang proses -->
                                                <div class="d-flex align-items-center">
                                                    <select class="form-control form-control-sm mr-1 select-progres"
                                                            data-id="<?= $row['id'] ?>">
                                                        <?php foreach ([0,10,20,30,40,50,60,70,80,90] as $p) : ?>
                                                            <option value="<?= $p ?>" <?= $progres == $p ? 'selected' : '' ?>>
                                                                <?= $p ?>%
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button class="btn btn-sm btn-warning btn-update-progres mr-1"
                                                            data-id="<?= $row['id'] ?>">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary btn-done"
                                                            data-id="<?= $row['id'] ?>">
                                                        <i class="fas fa-check"></i> Done
                                                    </button>
                                                </div>
                                            <?php else : ?>
                                                <span class="text-muted small">Diambil checker lain</span>
                                            <?php endif; ?>

                                        <?php elseif ($jobdesk === 'MANAGERWH') : ?>
                                            <?php if ($is_done && !$row['is_archived']) : ?>
                                                <button class="btn btn-sm btn-secondary btn-archive"
                                                        data-id="<?= $row['id'] ?>">
                                                    <i class="fas fa-archive"></i> Archive
                                                </button>
                                            <?php elseif (!$is_done) : ?>
                                                <span class="text-muted small">Menunggu selesai</span>
                                            <?php endif; ?>

                                        <?php elseif ($jobdesk === 'LOGISTIK') : ?>
                                            <button class="btn btn-sm btn-info btn-simpan-admlog"
                                                    data-id="<?= $row['id'] ?>">
                                                <i class="fas fa-save"></i> Simpan
                                            </button>

                                        <?php else : ?>
                                            <!-- View only: Direktur / Sales -->
                                            <span class="text-muted small"><i class="fas fa-eye"></i> View</span>
                                        <?php endif; ?>

                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted">
                                        <i class="fas fa-inbox mr-1"></i> Tidak ada data bongkaran
                                    </td>
                                </tr>
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

<!-- ================================================================
     MODAL BUAT BONGKARAN (Manager WH only)
================================================================ -->
<?php if ($jobdesk === 'MANAGERWH') : ?>
<div class="modal fade" id="modalBuatBongkaran" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus mr-2"></i> Buat Bongkaran Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">Kode Bongkaran</label>
                    <input type="text" class="form-control" value="<?= $kode_baru ?>" readonly>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Keterangan <span class="text-danger">*</span></label>
                    <textarea id="inputKeterangan" class="form-control" rows="3"
                              placeholder="Contoh: Bongkar truk B 1234 CD muatan pupuk"></textarea>
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

<script>
$(document).ready(function () {

    // DataTables
    $('#tabelBongkaran').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: -1 }],
        language: {
            search: "Cari:", lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data", emptyTable: "Tidak ada data",
            paginate: { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });

    // ---- MANAGER WH: Buat Bongkaran ----
    $('#btnSimpanBongkaran').on('click', function () {
        var keterangan = $('#inputKeterangan').val().trim();
        if (!keterangan) { alert('Keterangan wajib diisi'); return; }

        $.post('<?= base_url('bongkaran/store') ?>', { keterangan: keterangan }, function (res) {
            if (res.status) {
                alert(res.msg);
                location.reload();
            } else {
                alert('Gagal: ' + res.msg);
            }
        }, 'json');
    });

    // ---- CHECKER: Start ----
    $(document).on('click', '.btn-start', function () {
        var id = $(this).data('id');
        if (!confirm('Ambil pekerjaan bongkaran ini?')) return;
        $.post('<?= base_url('bongkaran/start') ?>', { id: id }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        }, 'json');
    });

    // ---- CHECKER: Update Progres ----
    $(document).on('click', '.btn-update-progres', function () {
        var id      = $(this).data('id');
        var progres = $(this).closest('div').find('.select-progres').val();
        $.post('<?= base_url('bongkaran/update_progres') ?>', { id: id, progres: progres }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        }, 'json');
    });

    // ---- CHECKER: Done ----
    $(document).on('click', '.btn-done', function () {
        var id = $(this).data('id');
        if (!confirm('Tandai bongkaran ini sebagai SELESAI?')) return;
        $.post('<?= base_url('bongkaran/done') ?>', { id: id }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        }, 'json');
    });

    // ---- ADMLOG: Simpan jalur & status ----
    $(document).on('click', '.btn-simpan-admlog', function () {
        var id  = $(this).data('id');
        var row = $(this).closest('tr');
        var kk  = row.find('input[data-field="jalur_kk"]').val();
        var lk  = row.find('input[data-field="jalur_lk"]').val();
        var st  = row.find('.select-status').val();

        $.post('<?= base_url('bongkaran/update_admlog') ?>', {
            id: id, jalur_kk: kk, jalur_lk: lk, status: st
        }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        }, 'json');
    });

    // ---- MANAGER WH: Archive ----
    $(document).on('click', '.btn-archive', function () {
        var id = $(this).data('id');
        if (!confirm('Arsipkan data bongkaran ini? Data tidak akan muncul di halaman utama.')) return;
        $.post('<?= base_url('bongkaran/archive') ?>', { id: id }, function (res) {
            alert(res.msg);
            if (res.status) location.reload();
        }, 'json');
    });

});
</script>
</body>