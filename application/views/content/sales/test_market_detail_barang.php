<?php
$positions = $positions ?? [];
$filters = $filters ?? [];
$summary = $summary ?? [];
$kios_list = $kios_list ?? [];
$realizations = $realizations ?? [];
$backQuery = http_build_query([
    'q'           => '',
    'kd_customer' => $filters['kd_customer'] ?? '',
    'status'      => $filters['status'] ?? '',
]);
?>
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-8">
                        <h1><i class="fas fa-box-open text-info mr-2"></i><?= html_escape($item_name) ?></h1>
                        <p class="text-muted mb-0">Detail posisi barang Test Market — <?= html_escape($item_code) ?></p>
                    </div>
                    <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('test-market') . '?' . $backQuery ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Test Market
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row mb-2">
                    <?php
                    $cards = [
                        ['Total Dititipkan', $summary['qty_titip'] ?? 0, 'fas fa-truck-loading', '#2563eb', '#dbeafe'],
                        ['Sudah Laku / Dibeli', $summary['qty_terjual'] ?? 0, 'fas fa-shopping-bag', '#16a34a', '#dcfce7'],
                        ['Sisa di Kios', $summary['qty_sisa'] ?? 0, 'fas fa-store', '#d97706', '#fef3c7'],
                    ];
                    foreach ($cards as $card):
                    ?>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm mb-3" style="border-radius:12px; border-left:5px solid <?= $card[3] ?> !important;">
                                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted font-weight-bold small text-uppercase"><?= html_escape($card[0]) ?></span>
                                        <h4 class="mb-0 font-weight-bold mt-1" style="color:<?= $card[3] ?>"><?= number_format((float)$card[1], 3, ',', '.') ?> <small class="text-muted"><?= html_escape($positions[0]['satuan'] ?? '') ?></small></h4>
                                    </div>
                                    <div class="p-2 rounded-circle" style="color:<?= $card[3] ?>; background:<?= $card[4] ?>"><i class="<?= $card[2] ?> fa-lg"></i></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><div class="alert alert-light border mb-2"><strong><?= (int)($summary['total_kios'] ?? 0) ?></strong> kios menerima barang ini</div></div>
                    <div class="col-md-4"><div class="alert alert-light border mb-2"><strong><?= (int)($summary['total_faktur'] ?? 0) ?></strong> faktur Test Market</div></div>
                    <div class="col-md-4"><div class="alert alert-light border mb-2">Nilai barang laku: <strong class="text-success">Rp <?= number_format((float)($summary['nilai_laku'] ?? 0), 0, ',', '.') ?></strong></div></div>
                </div>

                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
                    <div class="card-header bg-white py-3 border-0">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-store text-warning mr-2"></i>Posisi Barang per Kios</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f1f5f9; color:#334155;"><tr><th>Kios</th><th class="text-center">Faktur</th><th class="text-right">Dititipkan</th><th class="text-right">Sudah Dibeli</th><th class="text-right">Sisa di Kios</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if (empty($kios_list)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada posisi barang di kios.</td></tr>
                                <?php else: foreach ($kios_list as $kios):
                                    $kiosStatus = (float)$kios['qty_sisa'] <= 0.0001 ? 'HABIS' : ((float)$kios['qty_terjual'] > 0 ? 'SEBAGIAN LAKU' : 'DI KIOS');
                                ?>
                                    <tr>
                                        <td><strong><?= html_escape($kios['customer_name']) ?></strong><br><small class="text-muted"><?= html_escape($kios['kd_customer']) ?></small></td>
                                        <td class="text-center"><?= (int)$kios['total_faktur'] ?></td>
                                        <td class="text-right font-weight-bold"><?= number_format((float)$kios['qty_titip'], 3, ',', '.') ?> <?= html_escape($kios['satuan']) ?></td>
                                        <td class="text-right text-success font-weight-bold"><?= number_format((float)$kios['qty_terjual'], 3, ',', '.') ?></td>
                                        <td class="text-right text-warning font-weight-bold"><?= number_format((float)$kios['qty_sisa'], 3, ',', '.') ?></td>
                                        <td><span class="badge badge-<?= $kiosStatus === 'HABIS' ? 'success' : ($kiosStatus === 'SEBAGIAN LAKU' ? 'warning' : 'info') ?>"><?= $kiosStatus ?></span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
                    <div class="card-header bg-white py-3 border-0"><h3 class="card-title font-weight-bold"><i class="fas fa-file-invoice text-primary mr-2"></i>Rincian Faktur, Lot, dan Expired</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tanggal/Faktur</th><th>Kios</th><th>Barang</th><th>Lot/Expired</th>
                                    <th class="text-right">Dititipkan</th><th class="text-right">Dibeli</th><th class="text-right">Sisa</th><th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($positions)): ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">Data barang Test Market tidak ditemukan.</td></tr>
                                <?php else: foreach ($positions as $row): ?>
                                    <tr>
                                        <td>
                                            <?= !empty($row['tanggal_faktur']) ? date('d/m/Y', strtotime($row['tanggal_faktur'])) : '-' ?><br>
                                            <a href="<?= base_url('keuangan/pembayaran/bayar/' . (int)$row['id_faktur']) ?>"><strong><?= html_escape($row['no_faktur']) ?></strong></a>
                                            <br><small class="text-muted"><?= html_escape($row['no_so'] ?? '') ?></small>
                                        </td>
                                        <td><strong><?= html_escape($row['customer_name'] ?: $row['kd_customer']) ?></strong><br><small><?= html_escape($row['kd_customer']) ?></small></td>
                                        <td><strong><?= html_escape($row['nama_barang'] ?: $row['kd_barang']) ?></strong><br><small><?= html_escape($row['kd_barang']) ?></small></td>
                                        <td><?= html_escape($row['no_lot'] ?: '-') ?><br><small><?= !empty($row['expired_date']) ? date('d/m/Y', strtotime($row['expired_date'])) : '-' ?></small></td>
                                        <td class="text-right"><?= number_format((float)$row['qty_titip'], 3, ',', '.') ?> <?= html_escape($row['satuan']) ?></td>
                                        <td class="text-right text-success font-weight-bold"><?= number_format((float)$row['qty_terjual'], 3, ',', '.') ?></td>
                                        <td class="text-right text-warning font-weight-bold"><?= number_format((float)$row['qty_sisa'], 3, ',', '.') ?></td>
                                        <td><span class="badge badge-<?= $row['status'] === 'HABIS' ? 'success' : ($row['status'] === 'SEBAGIAN' ? 'warning' : 'info') ?>"><?= html_escape(str_replace('_', ' ', $row['status'])) ?></span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-shopping-bag text-success mr-2"></i>Riwayat Barang yang Sudah Laku / Dibeli Kios</h3>
                        <span class="badge badge-success"><?= count($realizations) ?> transaksi</span>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f0fdf4; color:#166534;"><tr><th>Tanggal</th><th>Faktur</th><th>Kios</th><th>Barang</th><th class="text-right">Qty Dibeli</th><th class="text-right">Nilai</th><th>Metode</th></tr></thead>
                            <tbody>
                                <?php if (empty($realizations)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-info-circle mr-1"></i>Belum ada barang yang dibeli kios.</td></tr>
                                <?php else: foreach ($realizations as $row): ?>
                                    <tr>
                                        <td><?= !empty($row['tanggal_realisasi']) ? date('d/m/Y', strtotime($row['tanggal_realisasi'])) : '-' ?></td>
                                        <td><strong><?= html_escape($row['no_faktur']) ?></strong></td>
                                        <td><?= html_escape($row['customer_name'] ?: $row['kd_customer']) ?><br><small class="text-muted"><?= html_escape($row['kd_customer']) ?></small></td>
                                        <td><?= html_escape($row['nama_barang'] ?: $row['kd_barang']) ?><br><small class="text-muted"><?= html_escape($row['kd_barang']) ?></small></td>
                                        <td class="text-right text-success font-weight-bold"><?= number_format((float)$row['qty'], 3, ',', '.') ?> <?= html_escape($row['satuan']) ?></td>
                                        <td class="text-right">Rp <?= number_format((float)$row['subtotal'], 0, ',', '.') ?></td>
                                        <td><?= html_escape($row['metode_pembayaran'] ?: '-') ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
