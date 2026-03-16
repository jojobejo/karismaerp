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
                        <h1 class="m-0"><i class="fas fa-tag text-warning"></i> <?= $page_title ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/promo') ?>">Promo</a></li>
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
                $action  = $is_edit ? base_url('kmt/promo/update/'.$row['id']) : base_url('kmt/promo/simpan');
                $lv      = (int)$lv;
                ?>
                <form action="<?= $action ?>" method="POST">
                    <?= form_open($action) ?>

                    <div class="card card-outline card-warning">
                        <div class="card-header"><h3 class="card-title">Formulir Promo / Peralatan</h3></div>
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

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nama Item <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_item" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['nama_item']):'' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Kategori</label>
                                        <select name="kategori" class="form-control form-control-sm">
                                            <option value="">-- Pilih --</option>
                                            <?php foreach ($kategori_list as $k): ?>
                                            <option value="<?= $k ?>" <?= ($is_edit&&$row['kategori']==$k)?'selected':'' ?>>
                                                <?= $k ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Qty</label>
                                        <input type="number" name="qty" id="qty" class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['qty'] : 1 ?>" min="1">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Satuan</label>
                                        <input type="text" name="satuan" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['satuan']??''):'' ?>"
                                               placeholder="pcs, lembar, unit...">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Harga Satuan</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                            <input type="text" name="harga_satuan" id="harga_satuan" class="form-control angka"
                                                   value="<?= $is_edit ? number_format($row['harga_satuan'],0,',','.'):'' ?>"
                                                   placeholder="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Total Biaya</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text bg-warning">Rp</span></div>
                                            <input type="text" id="total_display" class="form-control font-weight-bold" readonly style="background:#fffbea">
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
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-1"></i> <?= $is_edit?'Perbarui':'Simpan' ?>
                        </button>
                        <a href="<?= base_url('kmt/promo') ?>" class="btn btn-secondary ml-2">
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
    function hitungTotal(){
        var qty=parseInt($('#qty').val()||1);
        var harga=parseInt($('#harga_satuan').val().replace(/\./g,'')||0);
        $('#total_display').val((qty*harga).toLocaleString('id-ID'));
    }
    $('#harga_satuan').on('input',function(){
        var v=$(this).val().replace(/\D/g,'');
        $(this).val(v?parseInt(v).toLocaleString('id-ID'):'');
        hitungTotal();
    });
    $('#qty').on('input',hitungTotal);
    hitungTotal();
});
</script>
