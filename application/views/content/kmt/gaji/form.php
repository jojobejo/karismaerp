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
                        <h1 class="m-0"><i class="fas fa-users text-primary"></i> <?= $page_title ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/gaji') ?>">Gaji</a></li>
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
                $action  = $is_edit ? base_url('kmt/gaji/update/'.$row['id']) : base_url('kmt/gaji/simpan');
                $bulan_label = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                ?>

                <form action="<?= $action ?>" method="POST">
                    <?= form_open($action) ?>

                    <!-- Info Karyawan -->
                    <div class="card card-outline card-primary">
                        <div class="card-header"><h3 class="card-title">Data Karyawan</h3></div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Tahun <span class="text-danger">*</span></label>
                                        <select name="tahun" class="form-control form-control-sm" required>
                                            <?php for ($y=date('Y'); $y>=2022; $y--): ?>
                                            <option value="<?= $y ?>" <?= ($is_edit&&$row['tahun']==$y)||(!$is_edit&&date('Y')==$y)?'selected':'' ?>><?= $y ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Wilayah <span class="text-danger">*</span></label>
                                        <select name="id_wilayah" class="form-control form-control-sm" required>
                                            <option value="">-- Pilih --</option>
                                            <?php foreach ($wilayah_list as $w): ?>
                                            <option value="<?= $w['id'] ?>" <?= ($is_edit&&$row['id_wilayah']==$w['id'])?'selected':'' ?>>
                                                <?= htmlspecialchars($w['nama_wilayah']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nama Karyawan <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['nama']):'' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Posisi</label>
                                        <input type="text" name="posisi" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['posisi']??''):'' ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control form-control-sm">
                                            <option value="Tetap" <?= ($is_edit&&$row['status']=='Tetap')?'selected':'' ?>>Tetap</option>
                                            <option value="Kontrak" <?= ($is_edit&&$row['status']=='Kontrak')?'selected':'' ?>>Kontrak</option>
                                            <option value="Freelance" <?= ($is_edit&&$row['status']=='Freelance')?'selected':'' ?>>Freelance</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal Mulai</label>
                                        <input type="date" name="tgl_mulai" class="form-control form-control-sm"
                                               value="<?= $is_edit ? ($row['tgl_mulai']??''):'' ?>">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal Resign</label>
                                        <input type="date" name="tgl_resign" class="form-control form-control-sm"
                                               value="<?= $is_edit ? ($row['tgl_resign']??''):'' ?>">
                                        <small class="text-muted">Kosongkan jika masih aktif</small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Gaji Per Bulan -->
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Gaji Per Bulan</h3>
                            <div class="card-tools">
                                <span class="badge badge-primary" id="badgeTotalGaji">Total: Rp 0</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($bulan_cols as $i => $col): ?>
                                <div class="col-md-2 col-sm-4">
                                    <div class="form-group">
                                        <label class="small font-weight-bold"><?= $bulan_label[$i] ?></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" name="<?= $col ?>" class="form-control angka-gaji"
                                                   value="<?= $is_edit && $row[$col]>0 ? number_format($row[$col],0,',','.'):'' ?>"
                                                   placeholder="0">
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Total -->
                            <div class="row mt-2">
                                <div class="col-md-4 offset-md-8">
                                    <div class="alert alert-primary mb-0 py-2">
                                        <strong>Total Gaji Setahun:</strong>
                                        <span id="totalGajiTahunan" class="float-right font-weight-bold">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> <?= $is_edit?'Perbarui':'Simpan' ?>
                        </button>
                        <a href="<?= base_url('kmt/gaji') ?>" class="btn btn-secondary ml-2">
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
        var total=0;
        $('.angka-gaji').each(function(){
            total+=parseInt($(this).val().replace(/\./g,'')||0);
        });
        var fmt=total.toLocaleString('id-ID');
        $('#totalGajiTahunan').text('Rp '+fmt);
        $('#badgeTotalGaji').text('Total: Rp '+fmt);
    }
    $('.angka-gaji').on('input',function(){
        var v=$(this).val().replace(/\D/g,'');
        $(this).val(v?parseInt(v).toLocaleString('id-ID'):'');
        hitungTotal();
    });
    hitungTotal();
});
</script>
