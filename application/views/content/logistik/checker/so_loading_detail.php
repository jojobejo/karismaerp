<!-- views/content/logistik/checker/so_loading_detail.php -->
<style>
    .verify-table th {
        vertical-align: middle !important;
    }
    .verify-table td {
        vertical-align: middle !important;
    }
    .checkbox-xl {
        width: 22px;
        height: 22px;
        cursor: pointer;
    }
    .loaded-row {
        background-color: #f1fcf4 !important;
        transition: background-color 0.3s ease;
    }
    .unloaded-row {
        transition: background-color 0.3s ease;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark" style="font-size:1.3rem;">
                            <i class="fas fa-truck-loading mr-2 text-info"></i>Detail Loading Rute: <?= htmlspecialchars($kd_rute) ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('logistik') ?>">Logistik</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('checker') ?>">Warehouse</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('checker/so_loading') ?>">SO Loading</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </div>
                </div>

                <div class="mb-2">
                    <a href="<?= base_url('checker/so_loading') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Pilih Rute
                    </a>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-info">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-1"></i>Daftar Barang SO untuk Dimuat
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($items)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-check-double fa-3x mb-3 text-success"></i>
                                <p class="mb-0 font-weight-bold">Semua barang SO rute ini telah selesai dimuat atau diproses.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm verify-table" id="tabelSoLoadingDetail">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>No SO</th>
                                            <th>Customer / Kios</th>
                                            <th>Nama Barang</th>
                                            <th>No Lot</th>
                                            <th>Expired Date</th>
                                            <th style="width: 140px;" class="text-right">Qty Siap</th>
                                            <th style="width: 80px;">Satuan</th>
                                            <th style="width: 100px;" class="text-center">Muat Loading</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($items as $item): 
                                            $is_loaded = (int)$item['checker_loaded'] === 1;
                                            $row_class = $is_loaded ? 'loaded-row' : 'unloaded-row';
                                        ?>
                                            <tr class="<?= $row_class ?>" id="row-<?= $item['id'] ?>">
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td class="font-weight-bold text-nowrap"><?= htmlspecialchars($item['no_so']) ?></td>
                                                <td>
                                                    <?= htmlspecialchars($item['customer_name']) ?>
                                                    <?php if (!empty($item['nama_kios'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($item['nama_kios']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="font-weight-bold text-primary"><?= htmlspecialchars($item['nama_barang']) ?></td>
                                                <td class="text-center small">
                                                    <?= htmlspecialchars($item['no_lot'] ?: '-') ?>
                                                </td>
                                                <td class="text-center small">
                                                    <?= !empty($item['expired_date']) && $item['expired_date'] !== '0000-00-00' ? date('d/m/Y', strtotime($item['expired_date'])) : '-' ?>
                                                </td>
                                                <td class="text-right font-weight-bold" style="font-size: 1.05rem;">
                                                    <?php
                                                    $qty = (float)($item['qty_siap_faktur'] !== null ? $item['qty_siap_faktur'] : $item['qty']);
                                                    $isi = max(1, (int)($item['isi_per_box'] ?? 1));
                                                    if ($isi > 1) {
                                                        $qty_box = floor($qty / $isi);
                                                        $qty_pcs = fmod($qty, $isi);
                                                        
                                                        $parts = [];
                                                        if ($qty_box > 0) {
                                                            $parts[] = (int)$qty_box . ' box';
                                                        }
                                                        if ($qty_pcs > 0) {
                                                            $parts[] = (int)$qty_pcs . ' ' . $item['satuan'];
                                                        }
                                                        
                                                        if (empty($parts)) {
                                                            echo '0 ' . htmlspecialchars($item['satuan']);
                                                        } else {
                                                            echo implode(' + ', $parts);
                                                        }
                                                    } else {
                                                        echo number_format($qty, 2);
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-center"><?= htmlspecialchars($item['satuan']) ?></td>
                                                <td class="text-center">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" 
                                                               class="checkbox-xl cb-load-item" 
                                                               id="cb-<?= $item['id'] ?>" 
                                                               data-id="<?= $item['id'] ?>" 
                                                               <?= $is_loaded ? 'checked' : '' ?>>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
$(document).ready(function() {
    $('.cb-load-item').on('change', function() {
        var checkbox = $(this);
        var idDetail = checkbox.data('id');
        var isChecked = checkbox.is(':checked') ? 1 : 0;
        var row = $('#row-' + idDetail);

        checkbox.prop('disabled', true);

        $.ajax({
            url: '<?= base_url("checker/toggle_so_item_loaded") ?>',
            type: 'POST',
            data: {
                id_detail: idDetail,
                loaded: isChecked
            },
            dataType: 'JSON',
            success: function(response) {
                if (response.status) {
                    if (isChecked) {
                        row.removeClass('unloaded-row').addClass('loaded-row');
                    } else {
                        row.removeClass('loaded-row').addClass('unloaded-row');
                    }

                    if (response.created_do) {
                        alert("🎉 SUKSES!\n\nDelivery Order " + response.created_do + " berhasil dibuat otomatis karena semua SO sudah terfaktur dan barang loading sudah termuat semua.");
                        window.location.href = '<?= base_url("checker/so_loading") ?>';
                    }
                } else {
                    checkbox.prop('checked', !isChecked);
                    alert(response.message || 'Gagal merubah status muat.');
                }
            },
            error: function(xhr, status, error) {
                checkbox.prop('checked', !isChecked);
                alert('Terjadi kesalahan koneksi server: ' + error);
            },
            complete: function() {
                checkbox.prop('disabled', false);
            }
        });
    });
});
</script>
