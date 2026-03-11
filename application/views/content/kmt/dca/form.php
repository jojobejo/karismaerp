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
                        <h1 class="m-0"><i class="fas fa-handshake text-info"></i> <?= $title ?></h1>
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
                $action  = $is_edit ? base_url('kmt/dca/update/'.$row['id']) : base_url('kmt/dca/simpan');
                $lv      = (int)$akses_lv;
                ?>

                <form action="<?= $action ?>" method="POST">
                    <?= form_open($action) ?>

                    <div class="card card-outline card-info">
                        <div class="card-header"><h3 class="card-title">Formulir DCA</h3></div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal DCA <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_dca" class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['tanggal_dca'] : date('Y-m-d') ?>" required>
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
                                                <?= (($is_edit && $row['id_wilayah']==$w['id'])
                                                    || (!$is_edit && $id_wilayah_user==$w['id'])) ? 'selected':'' ?>>
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
                                        <input type="text" name="abm" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['abm']??'')
                                                           : $this->session->userdata('nama') ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Uraian <span class="text-danger">*</span></label>
                                        <input type="text" name="uraian" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['uraian']) : '' ?>"
                                               placeholder="Contoh: Channel Meeting, Duta Karisma, Corn Tour..." required>
                                    </div>
                                </div>

                            </div>

                            <!-- Nominal -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>UM (Uang Muka)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                            <input type="text" name="um" id="um" class="form-control angka"
                                                   value="<?= $is_edit && $row['um']>0 ? number_format($row['um'],0,',','.'):'' ?>" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Refund</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                            <input type="text" name="refund" id="refund" class="form-control angka"
                                                   value="<?= $is_edit && $row['refund']>0 ? number_format($row['refund'],0,',','.'):'' ?>" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Real Biaya</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                            <input type="text" name="real_biaya" id="real_biaya" class="form-control angka"
                                                   value="<?= $is_edit ? number_format($row['real_biaya'],0,',','.'):'' ?>" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Total Biaya <small class="text-muted">(Real - Refund)</small></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text bg-info text-white">Rp</span></div>
                                            <input type="text" id="total_biaya_display" class="form-control font-weight-bold"
                                                   readonly style="background:#e8f4fd">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save mr-1"></i> <?= $is_edit ? 'Perbarui':'Simpan' ?>
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
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.<div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
<script>
$(function(){
    function formatRibuan(el){
        $(el).on('input',function(){
            var v=$(this).val().replace(/\D/g,'');
            $(this).val(v?parseInt(v).toLocaleString('id-ID'):'');
            hitungTotal();
        });
    }
    function hitungTotal(){
        var real=parseInt($('#real_biaya').val().replace(/\./g,'')||0);
        var refund=parseInt($('#refund').val().replace(/\./g,'')||0);
        var total=real-refund;
        $('#total_biaya_display').val(total>0?total.toLocaleString('id-ID'):'0');
    }
    formatRibuan('#um'); formatRibuan('#refund'); formatRibuan('#real_biaya');
    hitungTotal();
});
</script>
