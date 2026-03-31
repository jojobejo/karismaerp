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
                        <h1 class="m-0"><i class="fas fa-undo text-danger"></i> <?= $page_title ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/retur') ?>">Retur</a></li>
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
                    ? base_url('kmt/retur/update/' . $row['id'])
                    : base_url('kmt/retur/simpan');
                $lv      = (int)$lv;
                ?>
                <form action="<?= $action ?>" method="POST">
                    <?= form_open($action) ?>

                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-undo mr-1"></i>
                                Formulir <?= $is_edit ? 'Edit' : 'Tambah' ?> Retur
                            </h3>
                        </div>
                        <div class="card-body">

                            <!-- ── BARIS 1: Tanggal, No Retur, Wilayah ── -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tanggal Retur <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_retur"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['tanggal_retur'] : date('Y-m-d') ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>No Retur</label>
                                        <input type="text" name="no_retur"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['no_retur'] ?? '') : '' ?>"
                                               placeholder="Nomor dokumen retur">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Wilayah <span class="text-danger">*</span></label>
                                        <select name="id_wilayah"
                                                class="form-control form-control-sm"
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
                                        <?php if ($lv === 3): ?>
                                        <input type="hidden" name="id_wilayah"
                                               value="<?= $is_edit ? $row['id_wilayah'] : $id_wilayah_user ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>SC</label>
                                        <input type="text" name="sc"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['sc'] ?? '') : '' ?>"
                                               placeholder="Nama SC">
                                    </div>
                                </div>
                            </div>

                            <!-- ── BARIS 2: Nama Toko, Kota, Produk ── -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nama Toko <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_toko"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['nama_toko']) : '' ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Kota</label>
                                        <input type="text" name="kota"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['kota'] ?? '') : '' ?>"
                                               placeholder="Kota toko">
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Nama Barang / Produk <span class="text-danger">*</span></label>
                                        <input type="text" name="produk"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['produk']) : '' ?>"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <!-- ── BARIS 3: Qty, Harga DPP, Nilai Retur ── -->
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" step="0.01" name="quantity"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? $row['quantity'] : '' ?>"
                                               placeholder="0">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Unit</label>
                                        <input type="text" name="unit"
                                               class="form-control form-control-sm"
                                               value="<?= $is_edit ? htmlspecialchars($row['unit'] ?? '') : '' ?>"
                                               placeholder="Pack / Sak">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Harga DPP (Rp)</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" name="harga_dpp"
                                                   class="form-control angka"
                                                   value="<?= $is_edit ? number_format($row['harga_dpp'] ?? 0, 0, ',', '.') : '' ?>"
                                                   placeholder="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nilai Retur (Rp) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-danger text-white">Rp</span>
                                            </div>
                                            <input type="text" name="nilai_retur"
                                                   class="form-control angka"
                                                   value="<?= $is_edit ? number_format($row['nilai_retur'], 0, ',', '.') : '' ?>"
                                                   placeholder="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Kategori</label>
                                        <select name="kategori" class="form-control form-control-sm">
                                            <option value="">-- Pilih --</option>
                                            <?php
                                            $kategori_list = ['Barang bermasalah', 'Replacement', 'Expired', 'Lainnya'];
                                            foreach ($kategori_list as $kat):
                                                $selected = ($is_edit && ($row['kategori'] ?? '') === $kat) ? 'selected' : '';
                                            ?>
                                            <option value="<?= $kat ?>" <?= $selected ?>><?= $kat ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- ── BARIS 4: Pengaruh Target ABM + Keterangan ── -->
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Pengaruh ke Target ABM <span class="text-danger">*</span></label>
                                        <div class="card border mb-0">
                                            <div class="card-body py-2">
                                                <?php
                                                $kurangi_val = $is_edit ? (int)($row['kurangi_target'] ?? 0) : -1;
                                                ?>
                                                <div class="custom-control custom-radio mb-2">
                                                    <input type="radio" id="kurangi_ya" name="kurangi_target"
                                                           value="1" class="custom-control-input"
                                                           <?= $kurangi_val === 1 ? 'checked' : '' ?> required>
                                                    <label class="custom-control-label text-danger font-weight-bold"
                                                           for="kurangi_ya">
                                                        <i class="fas fa-minus-circle mr-1"></i>
                                                        Kurangi Target ABM
                                                        <br>
                                                        <small class="text-muted font-weight-normal">
                                                            Retur ini mengurangi omset target ABM
                                                        </small>
                                                    </label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="kurangi_tidak" name="kurangi_target"
                                                           value="0" class="custom-control-input"
                                                           <?= $kurangi_val === 0 ? 'checked' : '' ?>>
                                                    <label class="custom-control-label text-warning font-weight-bold"
                                                           for="kurangi_tidak">
                                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                                        Tidak Kurangi Target ABM
                                                        <br>
                                                        <small class="text-muted font-weight-normal">
                                                            Hanya catatan, tidak mempengaruhi target
                                                        </small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Keterangan</label>
                                        <textarea name="keterangan"
                                                  class="form-control form-control-sm"
                                                  rows="4"
                                                  placeholder="Alasan / detail retur..."
                                        ><?= $is_edit ? htmlspecialchars($row['keterangan'] ?? '') : '' ?></textarea>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Keterangan Detail</label>
                                        <textarea name="keterangan_detail"
                                                  class="form-control form-control-sm"
                                                  rows="4"
                                                  placeholder="Detail tambahan..."
                                        ><?= $is_edit ? htmlspecialchars($row['keterangan_detail'] ?? '') : '' ?></textarea>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /.card-body -->
                    </div><!-- /.card -->

                    <!-- Alert khusus edit: perubahan kurangi_target -->
                    <?php if ($is_edit): ?>
                    <div class="alert alert-warning alert-sm py-2 px-3 mb-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Perhatian:</strong> Jika Anda mengubah pilihan <em>Pengaruh ke Target ABM</em>,
                        nilai omset akan disesuaikan otomatis.
                    </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-save mr-1"></i>
                            <?= $is_edit ? 'Perbarui Data' : 'Simpan Retur' ?>
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
    // Format angka ribuan
    $('.angka').on('input', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
    });
});
</script>