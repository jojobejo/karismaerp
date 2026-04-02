<!-- ================================================================
     GANTI views/content/kmt/promo/form.php
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
                            <i class="fas fa-tag text-warning"></i> <?= $page_title ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('kmt/promo') ?>">Promo</a></li>
                            <li class="breadcrumb-item active"><?= isset($row) && $row ? 'Edit' : 'Tambah' ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php $this->load->view('partial/main/alert') ?>

                <?php
                $is_edit = isset($row) && $row;
                $action  = $is_edit
                    ? base_url('kmt/promo/update/' . $row['id'])
                    : base_url('kmt/promo/simpan');
                $lv = (int)$lv;
                // Helper: ambil nilai field dengan aman
                $v = function($key, $default = '') use ($row, $is_edit) {
                    return $is_edit && isset($row[$key]) && $row[$key] !== null
                        ? htmlspecialchars($row[$key]) : $default;
                };
                ?>

                <form action="<?= $action ?>" method="POST">

                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-edit mr-1"></i> Formulir Promo Material / Peralatan
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <!-- Tanggal -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Tgl TF (Transfer) <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal"
                                               class="form-control form-control-sm" required
                                               value="<?= $v('tanggal', date('Y-m-d')) ?>">
                                    </div>
                                </div>

                                <!-- Wilayah -->
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

                                <!-- Supplier -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Supplier / Vendor</label>
                                        <input type="text" name="supplier"
                                               class="form-control form-control-sm"
                                               value="<?= $v('supplier') ?>"
                                               placeholder="Nama toko / vendor / supplier">
                                    </div>
                                </div>

                                <!-- Nama Barang -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Nama Barang / Item <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_item"
                                               class="form-control form-control-sm" required
                                               value="<?= $v('nama_item') ?>"
                                               placeholder="Contoh: Banner Pohon CLING, Timbangan Digital, dll">
                                    </div>
                                </div>

                            </div>

                            <hr class="mt-0 mb-3">
                            <p class="text-muted mb-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Isi salah satu atau keduanya. Total akan dihitung otomatis.
                            </p>

                            <div class="row">

                                <!-- Promo Material -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-ad text-info mr-1"></i>
                                            Promo Material (Rp)
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-info text-white">Rp</span>
                                            </div>
                                            <input type="text" name="promo_material" id="promo_material"
                                                   class="form-control angka"
                                                   value="<?= $is_edit ? number_format($row['promo_material'] ?? 0, 0, ',', '.') : '' ?>"
                                                   placeholder="0">
                                        </div>
                                        <small class="text-muted">Banner, spanduk, cetak, pakaian promosi, dll</small>
                                    </div>
                                </div>

                                <!-- Peralatan -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-tools text-secondary mr-1"></i>
                                            Peralatan (Rp)
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-secondary text-white">Rp</span>
                                            </div>
                                            <input type="text" name="peralatan" id="peralatan"
                                                   class="form-control angka"
                                                   value="<?= $is_edit ? number_format($row['peralatan'] ?? 0, 0, ',', '.') : '' ?>"
                                                   placeholder="0">
                                        </div>
                                        <small class="text-muted">Tenda, speaker, timbangan, peralatan event, dll</small>
                                    </div>
                                </div>

                                <!-- Total (otomatis) -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>
                                            <i class="fas fa-calculator text-warning mr-1"></i>
                                            Total Biaya (Rp)
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-warning">Rp</span>
                                            </div>
                                            <input type="text" name="total_biaya" id="total_biaya"
                                                   class="form-control font-weight-bold angka"
                                                   style="background:#fffbea;"
                                                   value="<?= $is_edit ? number_format($row['total_biaya'] ?? 0, 0, ',', '.') : '' ?>"
                                                   placeholder="0 (otomatis)">
                                        </div>
                                        <small class="text-muted">Otomatis = Promo + Peralatan. Bisa diisi manual jika beda.</small>
                                    </div>
                                </div>

                                <!-- Keterangan -->
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Keterangan</label>
                                        <textarea name="keterangan" class="form-control form-control-sm"
                                                  rows="2"
                                                  placeholder="Keterangan tambahan..."><?= $v('keterangan') ?></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fas fa-save mr-1"></i>
                            <?= $is_edit ? 'Perbarui Data' : 'Simpan Data' ?>
                        </button>
                        <a href="<?= base_url('kmt/promo') ?>" class="btn btn-secondary btn-sm ml-2">
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
    // Format angka dengan pemisah ribuan
    $('.angka').on('input', function () {
        var v = $(this).val().replace(/\D/g, '');
        $(this).val(v ? parseInt(v).toLocaleString('id-ID') : '');
        hitungTotal();
    });

    // Auto-hitung total = promo_material + peralatan
    function hitungTotal() {
        var promo = parseInt($('#promo_material').val().replace(/\./g, '').replace(',', '') || 0);
        var alat  = parseInt($('#peralatan').val().replace(/\./g, '').replace(',', '') || 0);
        var total = promo + alat;
        if (total > 0) {
            $('#total_biaya').val(total.toLocaleString('id-ID'));
        }
    }

    // Inisialisasi awal
    hitungTotal();
});
</script>