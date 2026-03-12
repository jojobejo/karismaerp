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
                        <h1 class="m-0"><i class="fas fa-undo text-danger"></i> <?= $title ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/retur') ?>">Retur</a></li>
                            <li class="breadcrumb-item active"><?= isset($row)?'Edit':'Tambah' ?></li>
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
                $action  = $is_edit ? base_url('kmt/retur/update/'.$row['id']) : base_url('kmt/retur/simpan');
                $lv      = (int)$lv;
                ?>
                <form action="<?= $action ?>" method="POST">
                    <?= form_open($action) ?>

                    <div class="card card-outline card-danger">
                        <div class="card-header"><h3 class="card-title">Formulir Retur</h3></div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal Retur <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_retur" class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['tanggal_retur'] : date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Wilayah <span class="text-danger">*</span></label>
                                        <select name="id_wilayah" class="form-control form-control-sm"
                                                <?= $lv===3?'disabled':'' ?> required>
                                            <option value="">-- Pilih --</option>
                                            <?php foreach ($wilayah_list as $w): ?>
                                            <option value="<?= $w['id'] ?>"
                                                <?= (($is_edit&&$row['id_wilayah']==$w['id'])
                                                    ||(!$is_edit&&$id_wilayah_user==$w['id']))?'selected':'' ?>>
                                                <?= htmlspecialchars($w['nama_wilayah']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($lv===3): ?>
                                        <input type="hidden" name="id_wilayah"
                                               value="<?= $is_edit ? $row['id_wilayah'] : $id_wilayah_user ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>No Retur</label>
                                        <input type="text" name="no_retur" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['no_retur']??''):'' ?>">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nama Toko <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_toko" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['nama_toko']):'' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Produk <span class="text-danger">*</span></label>
                                        <input type="text" name="produk" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['produk']):'' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" step="0.01" name="quantity" class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['quantity'] : '' ?>" placeholder="0">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nilai Retur (Rp)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text bg-danger text-white">Rp</span></div>
                                            <input type="text" name="nilai_retur" class="form-control angka"
                                                   value="<?= $is_edit ? number_format($row['nilai_retur'],0,',','.'):'' ?>" placeholder="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Keterangan</label>
                                        <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                                        ><?= $is_edit ? htmlspecialchars($row['keterangan']??''):'' ?></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-save mr-1"></i> <?= $is_edit?'Perbarui':'Simpan' ?>
                        </button>
                        <a href="<?= base_url('kmt/retur') ?>" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.<div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
<script>
$(function(){
    $('.angka').on('input',function(){
        var v=$(this).val().replace(/\D/g,'');
        $(this).val(v?parseInt(v).toLocaleString('id-ID'):'');
    });
});
</script>
