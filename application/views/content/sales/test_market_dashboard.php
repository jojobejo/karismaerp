<?php
$summary = $summary ?? [];
$positions = $positions ?? [];
$items = $items ?? [];
$realizations = $realizations ?? [];
$filters = $filters ?? [];
$item_pagination = $item_pagination ?? [];
$realization_pagination = $realization_pagination ?? [];
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
                    <h1><i class="fas fa-flask text-info mr-2"></i>Test Market</h1>
                    <p class="text-muted mb-0">Posisi barang titipan Test Market per kios dan realisasi pembeliannya.</p>
                </div>
                <div class="col-sm-4 text-sm-right mt-2 mt-sm-0">
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <?php
                $cards = [
                    ['Jumlah di Kios', number_format((float)($summary['qty_titip'] ?? 0), 3, ',', '.'), 'fas fa-boxes', 'primary'],
                    ['Dibeli Kios', number_format((float)($summary['qty_terjual'] ?? 0), 3, ',', '.'), 'fas fa-shopping-cart', 'success'],
                    ['Sisa di Kios', number_format((float)($summary['qty_sisa'] ?? 0), 3, ',', '.'), 'fas fa-warehouse', 'warning'],
                ];
                foreach ($cards as $card):
                ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="small-box bg-<?= $card[3] ?>">
                            <div class="inner"><h3 style="font-size:1.55rem"><?= $card[1] ?></h3><p><?= html_escape($card[0]) ?></p></div>
                            <div class="icon"><i class="<?= $card[2] ?>"></i></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-1"></i>Filter Monitoring</h3></div>
                <div class="card-body">
                    <form method="get" action="<?= base_url('test-market') ?>">
                        <input type="hidden" name="tab" value="<?= html_escape($filters['tab'] ?? 'kios') ?>">
                        <div class="form-row">
                            <div class="col-md-5 mb-2">
                                <input type="text" name="q" class="form-control" value="<?= html_escape($filters['keyword'] ?? '') ?>" placeholder="Cari faktur, SO, kios, kode atau nama barang">
                            </div>
                            <div class="col-md-3 mb-2">
                                <select name="kd_customer" class="form-control">
                                    <option value="">Semua Kios</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= html_escape($customer['kd_customer']) ?>" <?= ($filters['kd_customer'] ?? '') === $customer['kd_customer'] ? 'selected' : '' ?>>
                                            <?= html_escape($customer['customer_name'] ?: $customer['kd_customer']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="tersedia" <?= ($filters['status'] ?? '') === 'tersedia' ? 'selected' : '' ?>>Masih di Kios</option>
                                    <option value="habis" <?= ($filters['status'] ?? '') === 'habis' ? 'selected' : '' ?>>Sudah Habis</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2 d-flex">
                                <button class="btn btn-info flex-fill"><i class="fas fa-search mr-1"></i>Tampilkan</button>
                                <a href="<?= base_url('test-market') . '?tab=' . html_escape($filters['tab'] ?? 'kios') ?>" class="btn btn-light ml-1" title="Reset"><i class="fas fa-redo"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            $baseTabParams = [
                'q'           => $filters['keyword'] ?? '',
                'kd_customer' => $filters['kd_customer'] ?? '',
                'status'      => $filters['status'] ?? '',
            ];
            $activeTab = $filters['tab'] ?? 'kios';
            ?>
            <div class="card border-0 shadow-sm mb-3" style="border-radius:12px; overflow:hidden;">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap border-bottom bg-white">
                        <a href="<?= base_url('test-market') . '?' . http_build_query(array_merge($baseTabParams, ['tab' => 'kios'])) ?>"
                           class="px-4 py-3 font-weight-bold text-decoration-none <?= $activeTab === 'kios' ? 'text-primary' : 'text-muted' ?>"
                           style="border-bottom:3px solid <?= $activeTab === 'kios' ? '#2563eb' : 'transparent' ?>; background:<?= $activeTab === 'kios' ? '#eff6ff' : '#fff' ?>;">
                            <i class="fas fa-store mr-2"></i>Barang Test Market di Kios
                            <span class="badge badge-primary ml-1"><?= (int)($item_pagination['total_rows'] ?? count($items)) ?> barang</span>
                        </a>
                        <a href="<?= base_url('test-market') . '?' . http_build_query(array_merge($baseTabParams, ['tab' => 'dibeli'])) ?>"
                           class="px-4 py-3 font-weight-bold text-decoration-none <?= $activeTab === 'dibeli' ? 'text-success' : 'text-muted' ?>"
                           style="border-bottom:3px solid <?= $activeTab === 'dibeli' ? '#16a34a' : 'transparent' ?>; background:<?= $activeTab === 'dibeli' ? '#f0fdf4' : '#fff' ?>;">
                            <i class="fas fa-shopping-bag mr-2"></i>Barang yang Sudah Dibeli Kios
                            <span class="badge badge-success ml-1"><?= (int)($realization_pagination['total_rows'] ?? count($realizations)) ?> transaksi</span>
                        </a>
                    </div>
                </div>
            </div>

            <?php if ($activeTab === 'kios'): ?>
            <div class="card">
                <div class="card-header"><h3 class="card-title">Rekap Barang Test Market di Kios</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Barang</th><th>Posisi</th>
                                <th class="text-right">Dititipkan</th><th class="text-right">Dibeli</th><th class="text-right">Sisa</th><th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data Test Market.</td></tr>
                            <?php else: foreach ($items as $item):
                                $itemStatus = (float)$item['qty_sisa'] <= 0.0001
                                    ? 'HABIS'
                                    : ((float)$item['qty_terjual'] > 0 ? 'SEBAGIAN' : 'DI_KIOS');
                                $detailQuery = http_build_query([
                                    'kd_customer' => $filters['kd_customer'] ?? '',
                                    'status'      => $filters['status'] ?? '',
                                ]);
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?= base_url('test-market/barang/' . rawurlencode($item['kd_barang'])) . '?' . $detailQuery ?>">
                                            <i class="fas fa-external-link-alt mr-2"></i><strong><?= html_escape($item['nama_barang']) ?></strong>
                                        </a>
                                        <br><small class="text-muted ml-4"><?= html_escape($item['kd_barang']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border"><i class="fas fa-store mr-1"></i><?= (int)$item['total_kios'] ?> kios</span>
                                        <br><small class="text-muted"><?= (int)$item['total_faktur'] ?> faktur</small>
                                    </td>
                                    <td class="text-right"><?= number_format((float)$item['qty_titip'], 3, ',', '.') ?> <?= html_escape($item['satuan']) ?></td>
                                    <td class="text-right text-success font-weight-bold"><?= number_format((float)$item['qty_terjual'], 3, ',', '.') ?></td>
                                    <td class="text-right text-warning font-weight-bold"><?= number_format((float)$item['qty_sisa'], 3, ',', '.') ?></td>
                                    <td><span class="badge badge-<?= $itemStatus === 'HABIS' ? 'success' : ($itemStatus === 'SEBAGIAN' ? 'warning' : 'info') ?>"><?= html_escape(str_replace('_', ' ', $itemStatus)) ?></span></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php $this->load->view('content/sales/test_market_pagination', [
                    'pagination' => $item_pagination,
                    'page_param' => 'page_kios',
                    'query_params' => array_merge($baseTabParams, ['tab' => 'kios']),
                ]); ?>
            </div>
            <?php else: ?>
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title">Barang yang Sudah Dibeli Kios</h3></div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Tanggal</th><th>Faktur</th><th>Kios</th><th>Barang</th><th class="text-right">Qty</th><th class="text-right">Nilai</th><th>Metode</th></tr></thead>
                        <tbody>
                            <?php if (empty($realizations)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada realisasi pembelian kios.</td></tr>
                            <?php else: foreach ($realizations as $row): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_realisasi'])) ?></td>
                                    <td><?= html_escape($row['no_faktur']) ?></td>
                                    <td><?= html_escape($row['customer_name'] ?: $row['kd_customer']) ?></td>
                                    <td><?= html_escape($row['nama_barang'] ?: $row['kd_barang']) ?></td>
                                    <td class="text-right"><?= number_format((float)$row['qty'], 3, ',', '.') ?> <?= html_escape($row['satuan']) ?></td>
                                    <td class="text-right">Rp <?= number_format((float)$row['subtotal'], 0, ',', '.') ?></td>
                                    <td><?= html_escape($row['metode_pembayaran'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php $this->load->view('content/sales/test_market_pagination', [
                    'pagination' => $realization_pagination,
                    'page_param' => 'page_dibeli',
                    'query_params' => array_merge($baseTabParams, ['tab' => 'dibeli']),
                ]); ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>
</div>
