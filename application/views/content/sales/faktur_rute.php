<!-- views/content/sales/faktur_rute.php -->
<style>
    .route-card {
        display: block;
        color: #1f2937;
        border: 1px solid #dee2e6;
        border-left: 3px solid #ced4da;
        border-radius: 4px;
        padding: 6px 8px;
        margin-bottom: 4px;
        background: #fff;
        text-decoration: none;
    }
    .route-card:hover {
        color: #1f2937;
        border-left-color: #007bff;
        background: #f8fbff;
        text-decoration: none;
    }
    .route-card.active {
        border-color: #007bff;
        border-left-color: #007bff;
        background: #eaf4ff;
    }
    .route-card .route-code {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.15;
    }
    .route-card .route-meta {
        font-size: 10.5px;
        color: #6c757d;
        line-height: 1.2;
    }
    .route-card .route-tonase {
        color: #111827;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.15;
    }
    .route-card .badge {
        font-size: 10px;
        padding: 2px 5px;
        line-height: 1.1;
    }
    .quota-box .progress {
        height: 18px;
        border-radius: 4px;
    }
    .quota-box .progress-bar {
        font-size: 11px;
        line-height: 18px;
    }
    #tabelFakturRute_wrapper {
        padding: 12px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-7">
                        <h1 class="m-0">
                            <i class="fas fa-route mr-2"></i> Faktur per Rute
                        </h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                            <li class="breadcrumb-item active">Faktur per Rute</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $selected_route_name = '-';
        foreach ($routes as $r) {
            if (($r['kd_rute'] ?? '') === $selected_rute) {
                $selected_route_name = $r['nama_rute'] ?: $r['kd_rute'];
                break;
            }
        }
        $total_faktur = count($fakturs);
        $pct_ton = $batas_tonase > 0 ? min(100, round(($total_tonase / $batas_tonase) * 100, 1)) : 0;
        $pct_kub = $batas_kubikasi > 0 ? min(100, round(($total_kubikasi / $batas_kubikasi) * 100, 1)) : 0;
        $ton_bar = $total_tonase > $batas_tonase ? 'danger' : ($pct_ton >= 80 ? 'warning' : 'success');
        $kub_bar = $total_kubikasi > $batas_kubikasi ? 'danger' : ($pct_kub >= 80 ? 'warning' : 'info');
        ?>

        <section class="content">
            <div class="container-fluid">
                <div class="mb-3">
                    <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke SO
                    </a>
                    <?php if (!empty($selected_rute) && strtoupper($selected_rute) !== 'TANPA_RUTE' && !empty($fakturs)): ?>
                    <button type="button"
                            class="btn btn-success btn-sm ml-1"
                            id="btnConfirmRuteLoading"
                            data-rute="<?= htmlspecialchars($selected_rute, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-check-circle mr-1"></i> Konfirmasi Siap Loading
                    </button>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-lg-3">
                        <div class="card card-outline card-primary">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-map-marked-alt mr-1"></i> Pilih Rute
                                </h3>
                            </div>
                            <div class="card-body p-1" style="max-height:620px; overflow:auto;">
                                <?php if (empty($routes)): ?>
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Tidak ada master rute
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($routes as $r):
                                        $active = ($r['kd_rute'] === $selected_rute);
                                        $route_url = base_url('sales_order/faktur_rute?rute=' . rawurlencode($r['kd_rute']));
                                    ?>
                                        <a href="<?= $route_url ?>" class="route-card <?= $active ? 'active' : '' ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="route-code"><?= htmlspecialchars($r['kd_rute']) ?></div>
                                                    <div class="route-meta text-truncate" title="<?= htmlspecialchars($r['nama_rute']) ?>">
                                                        <?= htmlspecialchars($r['nama_rute']) ?>
                                                    </div>
                                                </div>
                                                <span class="badge badge-primary"><?= (int)$r['total_faktur'] ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <span class="route-tonase">
                                                    <?= number_format((float)$r['total_tonase'], 3) ?> ton
                                                </span>
                                                <span class="route-meta">
                                                    <?= number_format((float)$r['total_kubikasi'], 4) ?> m3
                                                </span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-9">
                        <div class="row quota-box">
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-<?= $ton_bar ?>"><i class="fas fa-weight-hanging"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tonase</span>
                                        <span class="info-box-number">
                                            <?= number_format($total_tonase, 3) ?> / <?= number_format($batas_tonase, 0) ?> ton
                                        </span>
                                        <div class="progress mt-1">
                                            <div class="progress-bar bg-<?= $ton_bar ?>" style="width:<?= $pct_ton ?>%">
                                                <?= $pct_ton ?>%
                                            </div>
                                        </div>
                                        <small class="<?= $sisa_tonase < 0 ? 'text-danger' : 'text-muted' ?>">
                                            Sisa: <?= number_format($sisa_tonase, 3) ?> ton
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-<?= $kub_bar ?>"><i class="fas fa-cubes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Kubikasi</span>
                                        <span class="info-box-number">
                                            <?= number_format($total_kubikasi, 4) ?> / <?= number_format($batas_kubikasi, 0) ?> m3
                                        </span>
                                        <div class="progress mt-1">
                                            <div class="progress-bar bg-<?= $kub_bar ?>" style="width:<?= $pct_kub ?>%">
                                                <?= $pct_kub ?>%
                                            </div>
                                        </div>
                                        <small class="<?= $sisa_kubikasi < 0 ? 'text-danger' : 'text-muted' ?>">
                                            Sisa: <?= number_format($sisa_kubikasi, 4) ?> m3
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title">
                                    <i class="fas fa-list mr-2"></i>
                                    Faktur Confirmed Belum DO - <?= htmlspecialchars($selected_route_name) ?>
                                </h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-hover table-sm" id="tabelFakturRute">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>No Faktur</th>
                                            <th>No SO</th>
                                            <th>Tanggal</th>
                                            <th>Customer</th>
                                            <th>Regional</th>
                                            <th class="text-center">Barang</th>
                                            <th class="text-right">Qty</th>
                                            <th class="text-right">Tonase</th>
                                            <th class="text-right">Kubikasi</th>
                                            <th class="text-center no-sort">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($fakturs)): ?>
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">
                                                    Tidak ada faktur untuk rute ini
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($fakturs as $f): ?>
                                                <tr>
                                                    <td class="font-weight-bold">
                                                        <?= htmlspecialchars($f['no_faktur']) ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($f['id_so'])): ?>
                                                            <a href="<?= base_url('sales_order/detail/' . $f['id_so']) ?>">
                                                                <?= htmlspecialchars($f['no_so']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($f['no_so']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <?= !empty($f['tanggal_faktur']) ? date('d/m/Y', strtotime($f['tanggal_faktur'])) : '-' ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars($f['customer_name'] ?: '-') ?>
                                                        <?php if (!empty($f['nama_kios'])): ?>
                                                            <br><small class="text-muted"><?= htmlspecialchars($f['nama_kios']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= !empty($f['regional']) ? htmlspecialchars($f['regional']) : '<span class="text-muted">-</span>' ?>
                                                    </td>
                                                    <td class="text-center"><?= number_format((int)$f['total_barang']) ?></td>
                                                    <td class="text-right"><?= number_format((float)$f['total_qty'], 2) ?></td>
                                                    <td class="text-right"><?= number_format((float)$f['total_tonase'], 3) ?> ton</td>
                                                    <td class="text-right"><?= number_format((float)$f['total_kubikasi'], 4) ?> m3</td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('sales_order/detail_faktur/' . $f['id_faktur']) ?>"
                                                           class="btn btn-sm btn-info" title="Detail Faktur">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(document).ready(function () {
    $('#tabelFakturRute').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[2, 'desc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords: "Tidak ada data ditemukan",
            emptyTable: "Tidak ada faktur",
            paginate: { first:"Pertama", last:"Terakhir", next:"Berikutnya", previous:"Sebelumnya" }
        }
    });

    $('#btnConfirmRuteLoading').on('click', function () {
        var rute = $(this).data('rute');
        if (!rute) return;
        if (!confirm('Konfirmasi rute ' + rute + ' sebagai SIAP LOADING?')) return;

        $.ajax({
            url: '<?= base_url("sales_order/confirm_rute_loading") ?>',
            type: 'POST',
            dataType: 'json',
            data: { kd_rute: rute },
            success: function (res) {
                alert(res.message || 'Selesai');
                if (res.msg === 'success') {
                    window.location.reload();
                }
            },
            error: function () {
                alert('Terjadi kesalahan koneksi.');
            }
        });
    });
});
</script>
