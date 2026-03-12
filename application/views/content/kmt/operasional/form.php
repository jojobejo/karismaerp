<!-- fomm.php -->
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
                        <h1 class="m-0"><i class="fas fa-car text-warning"></i> <?= $title ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/operasional') ?>">Operasional</a></li>
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
                    ? base_url('kmt/operasional/update/' . $row['id'])
                    : base_url('kmt/operasional/simpan');

                // Daftar field biaya beserta label
                $fields_biaya = [
                    'hotel'                  => 'Hotel',
                    'per_diem'               => 'Per Diem',
                    'entertainment'          => 'Entertainment',
                    'communication'          => 'Communication',
                    'atk'                    => 'ATK',
                    'gasoline'               => 'Gasoline',
                    'sparepart_service'      => 'Sparepart / Service Kendaraan',
                    'retribusi_toll_parkir'  => 'Retribusi / Toll / Parkir',
                    'transportasi'           => 'Transportasi',
                    'pos_paket'              => 'Pos / Paket',
                    'tambah_angin'           => 'Tambah Angin',
                    'tambal_ban'             => 'Tambal Ban',
                    'indekost'               => 'Indekost',
                    'lain_lain'              => 'Lain-lain',
                ];
                ?>

                <form action="<?= $action ?>" method="POST" id="formOperasional">
                    <?= form_open($action) ?>

                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi Umum</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal" class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['tanggal'] : date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Wilayah <span class="text-danger">*</span></label>
                                        <?php $lv = (int)$lv; ?>
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
                                        <!-- Hidden untuk ABM agar nilai terkirim -->
                                        <?php if ($lv === 3): ?>
                                        <input type="hidden" name="id_wilayah"
                                               value="<?= $is_edit ? $row['id_wilayah'] : $id_wilayah_user ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nama ABM <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['nama'])
                                                           : $this->session->userdata('nama') ?>" required>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Rincian Biaya -->
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list-ul mr-1"></i> Rincian Biaya</h3>
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
                                            <input type="text" name="<?= $key ?>" id="<?= $key ?>"
                                                   class="form-control form-control-sm angka-biaya"
                                                   value="<?= $is_edit && $row[$key] > 0
                                                               ? number_format($row[$key], 0, ',', '.') : '' ?>"
                                                   placeholder="0">
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Total Otomatis -->
                            <div class="row mt-2">
                                <div class="col-md-4 offset-md-8">
                                    <div class="alert alert-warning mb-0 py-2">
                                        <strong>Total Biaya:</strong>
                                        <span id="totalBiaya" class="float-right font-weight-bold">Rp 0</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-1"></i>
                            <?= $is_edit ? 'Perbarui Data' : 'Simpan Data' ?>
                        </button>
                        <a href="<?= base_url('kmt/operasional') ?>" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>

                </form>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(function () {
    // Format ribuan & hitung total otomatis
    function hitungTotal() {
        var total = 0;
        $('.angka-biaya').each(function () {
            var val = $(this).val().replace(/\./g, '').replace(/,/g, '');
            total += parseInt(val) || 0;
        });
        $('#totalBiaya').text('Rp ' + total.toLocaleString('id-ID'));
        $('#badgeTotal').text('Total: Rp ' + total.toLocaleString('id-ID'));
    }

    $('.angka-biaya').on('input', function () {
        var val = $(this).val().replace(/\D/g, '');
        $(this).val(val ? parseInt(val).toLocaleString('id-ID') : '');
        hitungTotal();
    });

    hitungTotal(); // Hitung awal saat edit
});
</script>
