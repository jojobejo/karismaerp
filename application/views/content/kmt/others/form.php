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
                            <i class="fas fa-ellipsis-h text-secondary"></i> <?= $page_title ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/others') ?>">Others</a></li>
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
                    ? base_url('kmt/others/update/' . $row['id'])
                    : base_url('kmt/others/simpan');
                $lv = (int)$lv;
                ?>

                <form action="<?= $action ?>" method="POST">
                    <?= form_open($action) ?>

                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-edit mr-1"></i> Formulir Others
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
                                               required>
                                    </div>
                                </div>

                                <!-- Wilayah -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Wilayah <span class="text-danger">*</span></label>
                                        <select name="id_wilayah"
                                                class="form-control form-control-sm"
                                                <?= $lv === 3 ? 'disabled' : '' ?>
                                                required>
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

                                <!-- Uraian -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Uraian <span class="text-danger">*</span></label>
                                        <input type="text" name="uraian"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['uraian']) : '' ?>"
                                               placeholder="Keterangan biaya others..."
                                               required>
                                    </div>
                                </div>

                                <!-- Total Biaya -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Total Biaya <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" name="total_biaya" id="total_biaya"
                                                   class="form-control angka"
                                                   value="<?= $is_edit
                                                               ? number_format($row['total_biaya'], 0, ',', '.')
                                                               : '' ?>"
                                                   placeholder="0" required>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-save mr-1"></i>
                            <?= $is_edit ? 'Perbarui Data' : 'Simpan Data' ?>
                        </button>
                        <a href="<?= base_url('kmt/others') ?>" class="btn btn-outline-secondary ml-2">
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
    $('.angka').on('input', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
    });
});
</script>