<!-- view/content/logistik/checker/analisa_loading.php -->

<style>
#tabelAvgChecker thead.thead-dark th {
    background:#1b5e20 !important;
    color:#fff !important;
    border-color:#145214 !important;
}
.metric-band {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:14px;
}
.metric-box {
    flex:1 1 180px;
    border:1px solid #e0e0e0;
    border-left:4px solid #1565c0;
    padding:10px 12px;
    background:#ffffff;
    border-radius:4px;
}
.metric-box:nth-child(2) { border-left-color:#1b5e20; }
.metric-box:nth-child(3) { border-left-color:#f9a825; }
.metric-box:nth-child(4) { border-left-color:#7b1fa2; }
.metric-box:nth-child(5) { border-left-color:#00897b; }
.metric-box .label {
    font-size:11px;
    color:#666;
    text-transform:uppercase;
    letter-spacing:.04em;
    font-weight:700;
}
.metric-box .value {
    font-size:20px;
    font-weight:800;
    color:#24313a;
    line-height:1.2;
}
.filter-panel {
    background:#f5f9ff;
    border:1px solid #d7e6fb;
    padding:12px;
    border-radius:4px;
    margin-bottom:14px;
}
.filter-panel label {
    font-size:12px;
    font-weight:700;
    margin-bottom:4px;
}
.analysis-card {
    border:none;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
}
.analysis-card .card-header {
    color:#fff;
    border-radius:4px 4px 0 0;
}
.analysis-card.card-checker .card-header { background:#1b5e20; }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <section class="content">
                <?php
                $fmt_durasi = function($detik) {
                    $detik = (int)$detik;
                    if ($detik <= 0) return '-';
                    $j = floor($detik / 3600);
                    $m = floor(($detik % 3600) / 60);
                    if ($j > 0) return $j . ' jam ' . $m . ' menit';
                    return $m . ' menit';
                };
                $jenis_badge = function($jenis) {
                    return $jenis === 'KK' ? 'badge-success' : 'badge-primary';
                };
                $selected_checker_name = 'Semua checker';
                foreach ($list_checker as $ck) {
                    if (($ck['nik'] ?? '') === $nik_checker) {
                        $selected_checker_name = $ck['nm_karyawan'] . ' (' . $ck['nik'] . ')';
                        break;
                    }
                }
                $selected_rute_name = $rute !== '' ? $rute : 'Semua rute';
                $rows_tampil = ($mode === 'checker') ? $avg_loading_rute_checker : $avg_loading_rute;
                $total_rows = count($rows_tampil);
                $rute_keys = [];
                foreach ($rows_tampil as $r) {
                    $rute_keys[($r['jenis'] ?? '') . '|' . ($r['rute'] ?? '')] = true;
                }
                $total_rute = count($rute_keys);
                $total_selesai = array_sum(array_map(fn($r) => (int)$r['total_selesai'], $rows_tampil));
                $avg_global = 0;
                if ($total_selesai > 0) {
                    $weighted_total = 0;
                    foreach ($rows_tampil as $r) {
                        $weighted_total += ((int)$r['avg_detik'] * (int)$r['total_selesai']);
                    }
                    $avg_global = round($weighted_total / $total_selesai);
                }
                ?>

                <div class="d-flex justify-content-between align-items-center mb-3" style="gap:10px; flex-wrap:wrap;">
                    <h4 class="mb-0 font-weight-bold">
                        <i class="fas fa-stopwatch mr-2"></i> Analisa Rata-rata Kecepatan Loading
                    </h4>
                    <a href="<?= base_url('checker') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>

                <form method="get" action="<?= base_url('checker/analisa_loading') ?>" class="filter-panel">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label for="tanggal_awal">Tanggal Awal</label>
                            <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="tanggal_akhir">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="nik_checker">Checker</label>
                            <select class="form-control" id="nik_checker" name="nik_checker">
                                <option value="">Semua Checker</option>
                                <?php foreach ($list_checker as $ck) : ?>
                                    <option value="<?= htmlspecialchars($ck['nik']) ?>" <?= $nik_checker === $ck['nik'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ck['nm_karyawan']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="rute">Rute</label>
                            <select class="form-control" id="rute" name="rute">
                                <option value="">Semua Rute</option>
                                <?php foreach ($list_rute as $rt) : ?>
                                    <option value="<?= htmlspecialchars($rt['rute']) ?>" <?= $rute === $rt['rute'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($rt['rute']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="mode">Tampilan</label>
                            <select class="form-control" id="mode" name="mode">
                                <option value="rute" <?= $mode === 'rute' ? 'selected' : '' ?>>Gabungan per Rute</option>
                                <option value="checker" <?= $mode === 'checker' ? 'selected' : '' ?>>Detail per Checker</option>
                            </select>
                        </div>
                        <div class="col-md-auto mb-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Analisa
                            </button>
                            <a href="<?= base_url('checker/analisa_loading') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="metric-band">
                    <div class="metric-box">
                        <div class="label">Periode</div>
                        <div class="value"><?= date('d/m/Y', strtotime($tanggal_awal)) ?> - <?= date('d/m/Y', strtotime($tanggal_akhir)) ?></div>
                    </div>
                    <div class="metric-box">
                        <div class="label">Loading selesai</div>
                        <div class="value"><?= $total_selesai ?>x</div>
                    </div>
                    <div class="metric-box">
                        <div class="label">Rute dianalisa</div>
                        <div class="value"><?= $total_rute ?></div>
                    </div>
                    <div class="metric-box">
                        <div class="label">Rute dipilih</div>
                        <div class="value"><?= htmlspecialchars($selected_rute_name) ?></div>
                    </div>
                    <div class="metric-box">
                        <div class="label">Checker</div>
                        <div class="value"><?= htmlspecialchars($selected_checker_name) ?></div>
                    </div>
                    <div class="metric-box">
                        <div class="label">Rata-rata semua rute</div>
                        <div class="value"><?= $fmt_durasi($avg_global) ?></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="card analysis-card card-checker">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas <?= $mode === 'checker' ? 'fa-user-check' : 'fa-route' ?> mr-2"></i>
                                    <?= $mode === 'checker' ? 'Rata-rata Loading per Rute dan Checker' : 'Rata-rata Loading Gabungan per Rute' ?>
                                </h3>
                                <span class="badge badge-light ml-2"><?= $total_rows ?> baris</span>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-bordered table-sm" id="tabelAvgChecker">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th>#</th>
                                            <th>Rute</th>
                                            <th>Jenis</th>
                                            <?php if ($mode === 'checker') : ?>
                                            <th>Checker</th>
                                            <?php endif; ?>
                                            <th>Selesai</th>
                                            <th>Rata-rata</th>
                                            <th>Tercepat</th>
                                            <th>Terlama</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($rows_tampil)) : ?>
                                            <tr><td colspan="<?= $mode === 'checker' ? 8 : 7 ?>" class="text-center text-muted">Tidak ada data loading selesai pada periode ini</td></tr>
                                        <?php else : ?>
                                            <?php $no = 1; foreach ($rows_tampil as $row) : ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td class="font-weight-bold"><?= htmlspecialchars($row['rute']) ?></td>
                                                <td class="text-center"><span class="badge <?= $jenis_badge($row['jenis']) ?>"><?= htmlspecialchars($row['jenis']) ?></span></td>
                                                <?php if ($mode === 'checker') : ?>
                                                <td>
                                                    <?= htmlspecialchars($row['nm_checker']) ?>
                                                </td>
                                                <?php endif; ?>
                                                <td class="text-center"><?= (int)$row['total_selesai'] ?>x</td>
                                                <td class="text-center font-weight-bold"><?= $fmt_durasi($row['avg_detik']) ?></td>
                                                <td class="text-center"><?= $fmt_durasi($row['min_detik']) ?></td>
                                                <td class="text-center"><?= $fmt_durasi($row['max_detik']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
$(function () {
    if ($.fn.DataTable) {
        $('#tabelAvgChecker').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                zeroRecords: 'Data tidak ditemukan',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });
    }
});
</script>
</body>
