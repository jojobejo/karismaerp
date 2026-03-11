<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="KarismaLogo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-shopping-cart text-success"></i>
                            <?= $title ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/omset') ?>">Omset</a></li>
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
                $is_edit  = isset($row);
                $action   = $is_edit
                    ? base_url('kmt/omset/update/' . $row['id'])
                    : base_url('kmt/omset/simpan');
                ?>

                <form action="<?= $action ?>" method="POST" id="formOmset">
                    <?= form_open($action) // CSRF token ?>

                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-1"></i> Informasi Transaksi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <!-- Tanggal -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal" class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['tanggal'] : date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <!-- Wilayah -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Wilayah <span class="text-danger">*</span></label>
                                        <select name="id_wilayah" class="form-control form-control-sm" required>
                                            <option value="">-- Pilih --</option>
                                            <?php foreach ($wilayah_list as $w): ?>
                                            <option value="<?= $w['id'] ?>"
                                                <?= ($is_edit && $row['id_wilayah'] == $w['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($w['nama_wilayah']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- No Urut -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>No Urut</label>
                                        <input type="text" name="no_urut" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['no_urut'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <!-- Nomor -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Nomor</label>
                                        <input type="text" name="nomor" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['nomor'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <!-- Kode -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Kode</label>
                                        <input type="text" name="kode" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['kode'] ?? '') : '' ?>">
                                    </div>
                                </div>

                            </div><!-- /.row -->
                        </div>
                    </div>

                    <!-- Informasi Sales & Toko -->
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-store mr-1"></i> Informasi Toko & Sales</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nama Toko <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_toko" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['nama_toko']) : '' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Kota</label>
                                        <input type="text" name="kota" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['kota'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Sales SO</label>
                                        <input type="text" name="sales_so" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['sales_so'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>SC</label>
                                        <input type="text" name="sc" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['sc'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>SE</label>
                                        <input type="text" name="se" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['se'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Wilayah SE</label>
                                        <input type="text" name="wilayah_se" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['wilayah_se'] ?? '') : '' ?>">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Produk & Harga -->
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-box mr-1"></i> Produk & Harga</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Produk <span class="text-danger">*</span></label>
                                        <input type="text" name="produk" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['produk']) : '' ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Merk</label>
                                        <input type="text" name="merk" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['merk'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Jenis</label>
                                        <input type="text" name="jenis" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['jenis'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" step="0.01" name="quantity"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['quantity'] : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <input type="text" name="unit" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['unit'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Box</label>
                                        <input type="number" step="0.01" name="box"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['box'] : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Ltr/Kg</label>
                                        <input type="number" step="0.01" name="ltr_kg"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['ltr_kg'] : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Harga Inc PPN</label>
                                        <input type="text" name="harga_inc_ppn" id="harga_inc_ppn"
                                               class="form-control form-control-sm angka"
                                               value="<?= $is_edit ? number_format($row['harga_inc_ppn'], 0, ',', '.') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Penj DPP Neto</label>
                                        <input type="text" name="penj_dpp_neto" id="penj_dpp_neto"
                                               class="form-control form-control-sm angka"
                                               value="<?= $is_edit ? number_format($row['penj_dpp_neto'], 0, ',', '.') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Penj Inc PPN Neto <span class="text-danger">*</span></label>
                                        <input type="text" name="penj_inc_ppn_neto" id="penj_inc_ppn_neto"
                                               class="form-control form-control-sm angka"
                                               value="<?= $is_edit ? number_format($row['penj_inc_ppn_neto'], 0, ',', '.') : '' ?>">
                                        <small class="text-muted">= TOTAL OMSET</small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Retur & Keterangan -->
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-undo mr-1"></i> Retur & Keterangan</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>No Retur</label>
                                        <input type="text" name="no_retur" class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['no_retur'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tgl Retur</label>
                                        <input type="date" name="tgl_retur" class="form-control form-control-sm"
                                               value="<?= $is_edit ? ($row['tgl_retur'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tgl Kirim</label>
                                        <input type="date" name="tgl_kirim" class="form-control form-control-sm"
                                               value="<?= $is_edit ? ($row['tgl_kirim'] ?? '') : '' ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Keterangan</label>
                                        <textarea name="keterangan" class="form-control form-control-sm" rows="2"
                                        ><?= $is_edit ? htmlspecialchars($row['keterangan'] ?? '') : '' ?></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="mb-4">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i>
                            <?= $is_edit ? 'Perbarui Data' : 'Simpan Data' ?>
                        </button>
                        <a href="<?= base_url('kmt/omset') ?>" class="btn btn-secondary ml-2">
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
// Format angka ribuan otomatis
$(function () {
    function formatRibuan(el) {
        $(el).on('input', function () {
            var val = $(this).val().replace(/\D/g, '');
            $(this).val(val ? parseInt(val).toLocaleString('id-ID') : '');
        });
    }
    formatRibuan('.angka');
});
</script>
