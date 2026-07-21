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
    .btn-load-yes {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 50%;
        font-size: 15px;
    }
    .btn-load-no {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 50%;
        font-size: 15px;
    }
    .loaded-row {
        background-color: #f1fcf4 !important;
        transition: background-color 0.3s ease;
    }
    .rejected-row {
        background-color: #fff5f5 !important;
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
                                            <th style="width: 120px;" class="text-center">Muat / Tidak</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($items as $item): 
                                            $status_muat = (int)($item['checker_loaded'] ?? 0);
                                            $row_class = $status_muat === 1 ? 'loaded-row' : ($status_muat === 2 ? 'rejected-row' : 'unloaded-row');
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
                                                <td class="text-center" style="white-space:nowrap;">
                                                    <?php
                                                    // checker_loaded: 1=dimuat, 2=tidak dimuat, 0/null=belum dipilih
                                                    $status_muat = (int)($item['checker_loaded'] ?? 0);
                                                    ?>
                                                    <button type="button"
                                                            class="btn btn-sm btn-load-yes <?= $status_muat === 1 ? 'btn-success' : 'btn-outline-success' ?>"
                                                            data-id="<?= $item['id'] ?>"
                                                            data-action="1"
                                                            title="Dimuat">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-sm btn-load-no <?= $status_muat === 2 ? 'btn-danger' : 'btn-outline-danger' ?>"
                                                            data-id="<?= $item['id'] ?>"
                                                            data-action="2"
                                                            title="Tidak Dimuat">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <span id="info-progress">
                                            <span id="count-done">0</span> dari <?= count($items) ?> item sudah dipilih
                                        </span>
                                    </small>
                                    <button type="button" id="btn-selesai-loading" class="btn btn-primary btn-sm" disabled>
                                        <i class="fas fa-flag-checkered mr-1"></i>Selesai Loading
                                    </button>
                                </div>
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
    var totalItems = <?= count($items) ?>;
    var kdRute = '<?= addslashes($kd_rute) ?>';

    function countDone() {
        var done = 0;
        $('.btn-load-yes, .btn-load-no').each(function() {
            // cek per baris: apakah salah satu tombol sudah aktif
        });
        // hitung baris yang sudah punya status
        done = $('tr[id^="row-"]').filter(function() {
            return $(this).hasClass('loaded-row') || $(this).hasClass('rejected-row');
        }).length;
        return done;
    }

    function updateProgress() {
        var done = countDone();
        $('#count-done').text(done);
        if (done >= totalItems && totalItems > 0) {
            $('#btn-selesai-loading').prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
        } else {
            $('#btn-selesai-loading').prop('disabled', true);
        }
    }

    // Init progress on load
    updateProgress();

    // Tombol ✅ / ❌ per baris
    $(document).on('click', '.btn-load-yes, .btn-load-no', function() {
        var btn = $(this);
        var idDetail = btn.data('id');
        var action = btn.data('action'); // 1=dimuat, 2=tidak dimuat
        var row = $('#row-' + idDetail);

        btn.prop('disabled', true);
        row.find('.btn-load-yes, .btn-load-no').prop('disabled', true);

        $.ajax({
            url: '<?= base_url("checker/toggle_so_item_loaded") ?>',
            type: 'POST',
            data: { id_detail: idDetail, loaded: action },
            dataType: 'JSON',
            success: function(response) {
                if (response.status) {
                    // Update tampilan tombol
                    row.find('.btn-load-yes')
                        .removeClass('btn-success btn-outline-success')
                        .addClass(action === 1 ? 'btn-success' : 'btn-outline-success');
                    row.find('.btn-load-no')
                        .removeClass('btn-danger btn-outline-danger')
                        .addClass(action === 2 ? 'btn-danger' : 'btn-outline-danger');

                    // Update warna baris
                    row.removeClass('loaded-row rejected-row unloaded-row');
                    if (action === 1) row.addClass('loaded-row');
                    else if (action === 2) row.addClass('rejected-row');

                    updateProgress();
                } else {
                    alert(response.message || 'Gagal merubah status.');
                }
            },
            error: function(xhr, status, error) {
                alert('Terjadi kesalahan koneksi: ' + error);
            },
            complete: function() {
                row.find('.btn-load-yes, .btn-load-no').prop('disabled', false);
            }
        });
    });

    // Tombol Selesai Loading
    $('#btn-selesai-loading').on('click', function() {
        if (!confirm('Konfirmasi: semua barang rute ini sudah selesai diproses (dimuat / tidak dimuat)?\n\nKlik OK untuk menyelesaikan loading.')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...');

        $.ajax({
            url: '<?= base_url("checker/selesai_loading_rute") ?>',
            type: 'POST',
            data: { kd_rute: kdRute, date: '<?= $this->input->get("date", true) ?>' },
            dataType: 'JSON',
            success: function(response) {
                if (response.status) {
                    if (response.created_do) {
                        alert('✅ Loading selesai!\n\n🎉 Delivery Order ' + response.created_do + ' berhasil dibuat otomatis.');
                    } else if (response.ada_ditolak) {
                        alert('✅ Loading selesai dicatat.\n\n⚠️ ' + response.message + '\n\nRute ini akan tetap muncul sampai Admin SC melakukan repost faktur.');
                    } else {
                        alert('✅ Loading selesai.\n\n' + (response.message || 'Menunggu proses selanjutnya.'));
                    }
                    window.location.href = '<?= base_url("checker/so_loading") ?>';
                } else {
                    alert('❌ ' + (response.message || 'Gagal menyelesaikan loading.'));
                    btn.prop('disabled', false).html('<i class="fas fa-flag-checkered mr-1"></i>Selesai Loading');
                }
            },
            error: function(xhr, status, error) {
                alert('Terjadi kesalahan koneksi: ' + error);
                btn.prop('disabled', false).html('<i class="fas fa-flag-checkered mr-1"></i>Selesai Loading');
            }
        });
        });
});
</script>
