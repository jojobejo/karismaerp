<?php
/**
 * views/kmt/_partial/filter.php
 * Reusable filter bar — include di setiap halaman index
 *
 * Required vars dari controller:
 *   $tahun, $bulan (optional), $id_wilayah, $wilayah_list, $akses_lv
 *   $filter_url  — base url tujuan GET, contoh: base_url('kmt/omset')
 *   $show_bulan  — bool, tampilkan filter bulan atau tidak (default true)
 */
$show_bulan = isset($show_bulan) ? $show_bulan : true;
$is_abm     = ((int)$akses_lv === 3);
?>
<div class="card card-outline card-primary mb-3">
    <div class="card-header py-2">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Data</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body py-2">
        <form method="GET" action="<?= $filter_url ?>">
            <div class="row align-items-end">

                <!-- Tahun -->
                <div class="col-md-2 col-sm-6">
                    <div class="form-group mb-0">
                        <label class="small mb-1"><i class="fas fa-calendar mr-1"></i>Tahun</label>
                        <select name="tahun" class="form-control form-control-sm">
                            <?php for ($y = date('Y'); $y >= 2022; $y--): ?>
                                <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <!-- Bulan (optional) -->
                <?php if ($show_bulan): ?>
                <div class="col-md-2 col-sm-6">
                    <div class="form-group mb-0">
                        <label class="small mb-1"><i class="fas fa-calendar-alt mr-1"></i>Bulan</label>
                        <select name="bulan" class="form-control form-control-sm">
                            <option value="">-- Semua --</option>
                            <?php
                            $nm = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                            for ($m = 1; $m <= 12; $m++):
                            ?>
                                <option value="<?= $m ?>" <?= (isset($bulan) && $bulan == $m) ? 'selected' : '' ?>>
                                    <?= $nm[$m] ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Wilayah -->
                <div class="col-md-3 col-sm-6">
                    <div class="form-group mb-0">
                        <label class="small mb-1"><i class="fas fa-map-marker-alt mr-1"></i>Wilayah</label>
                        <select name="id_wilayah" class="form-control form-control-sm"
                                <?= $is_abm ? 'disabled' : '' ?>>
                            <?php if (!$is_abm): ?>
                                <option value="">-- Semua Wilayah --</option>
                            <?php endif; ?>
                            <?php foreach ($wilayah_list as $w): ?>
                                <option value="<?= $w['id'] ?>"
                                    <?= ($id_wilayah == $w['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($w['nama_wilayah']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Submit -->
                <div class="col-md-2 col-sm-6 mt-2 mt-md-0">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-search mr-1"></i> Tampilkan
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
