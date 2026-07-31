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
                    <div class="col-sm-7">
                        <h1 class="m-0"><i class="fas fa-file-invoice-dollar mr-2"></i>Daftar Piutang Customer</h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">Piutang Customer</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show">
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="mb-3">
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Dashboard
                    </a>
                    <a href="<?= base_url('keuangan/pembayaran/export-excel') ?>" class="btn btn-success btn-sm ml-2">
                        <i class="fas fa-file-excel mr-1"></i>Export Excel
                    </a>
                </div>

                <div class="card mb-3">
                    <div class="card-body py-2">
                        <div class="form-inline">
                            <label for="filterTanggalSisa" class="font-weight-bold mr-2">Simulasi Tanggal Acuan:</label>
                            <input type="date" class="form-control form-control-sm" id="filterTanggalSisa" value="<?= date('Y-m-d') ?>">
                            <small class="text-muted ml-3">Pilih tanggal untuk melihat sisa hari tempo dihitung dari tanggal tersebut.</small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Semua Faktur Belum Lunas</h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= count($fakturs) ?> faktur</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm" id="tabelPiutangCollection" style="width:100%;">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No Faktur</th>
                                        <th>Tanggal Faktur</th>
                                        <th>Tanggal Tempo</th>
                                        <th>Sisa Hari</th>
                                        <th>Customer</th>
                                        <th class="text-right">Total Piutang</th>
                                        <th class="text-right">Total Pembayaran</th>
                                        <th class="text-right">BG Belum Cair</th>
                                        <th class="text-right">Sisa Piutang</th>
                                        <th class="text-center">Status Bayar</th>
                                        <th class="text-center">Overdue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($fakturs)): ?>
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-4">Tidak ada faktur belum lunas.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($fakturs as $faktur):
                                            $status_bayar = strtolower($faktur['status_pembayaran']);
                                            $status_class = $status_bayar === 'lunas' ? 'success' : ($status_bayar === 'sebagian' ? 'warning' : 'danger');
                                            $status_label = [
                                                'lunas'       => 'Lunas',
                                                'sebagian'    => 'Sebagian',
                                                'belum_lunas' => 'Belum Lunas',
                                            ][$status_bayar] ?? $faktur['status_pembayaran'];
                                            $overdue = $faktur['status_overdue'];
                                            $overdue_class = $overdue === 'Belum overdue' ? 'secondary' : ($overdue === 'Overdue 30' ? 'warning' : 'danger');
                                        ?>
                                            <tr>
                                                <td>
                                                    <a href="javascript:void(0)" class="btn-history-pembayaran font-weight-bold text-primary" 
                                                       data-id-faktur="<?= $faktur['id_faktur'] ?>" 
                                                       data-no-faktur="<?= htmlspecialchars($faktur['no_faktur']) ?>">
                                                        <?= htmlspecialchars($faktur['no_faktur']) ?> <i class="fas fa-search-plus ml-1 small"></i>
                                                    </a>
                                                </td>
                                                <td class="text-nowrap">
                                                    <?= !empty($faktur['tanggal_faktur']) ? date('d/m/Y', strtotime($faktur['tanggal_faktur'])) : '-' ?>
                                                </td>
                                                <td class="text-nowrap">
                                                    <?= !empty($faktur['tanggal_jatuh_tempo']) ? date('d/m/Y', strtotime($faktur['tanggal_jatuh_tempo'])) : '-' ?>
                                                </td>
                                                <td class="text-nowrap font-weight-bold col-sisa-hari" data-tanggal-tempo="<?= htmlspecialchars($faktur['tanggal_jatuh_tempo']) ?>" data-order="<?= (int)$faktur['sisa_hari'] ?>">
                                                    <?php
                                                    $sisa = (int)$faktur['sisa_hari'];
                                                    if ($sisa >= 0) {
                                                        echo "<span class='text-success'>" . $sisa . " hari</span>";
                                                    } else {
                                                        echo "<span class='text-danger'>" . $sisa . " hari</span>";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($faktur['nama_customer']) ?></strong>
                                                    <br><small class="text-muted"><?= htmlspecialchars($faktur['kd_customer']) ?></small>
                                                </td>
                                                <td class="text-right">Rp <?= number_format((float)$faktur['total_tagihan'], 0, ',', '.') ?></td>
                                                <td class="text-right">Rp <?= number_format((float)$faktur['total_pembayaran'], 0, ',', '.') ?></td>
                                                <td class="text-right text-warning">Rp <?= number_format((float)($faktur['total_bg_pending'] ?? 0), 0, ',', '.') ?></td>
                                                <td class="text-right font-weight-bold text-danger">Rp <?= number_format((float)$faktur['sisa_tagihan'], 0, ',', '.') ?></td>
                                                <td class="text-center"><span class="badge badge-<?= $status_class ?>"><?= htmlspecialchars($status_label) ?></span></td>
                                                <td class="text-center"><span class="badge badge-<?= $overdue_class ?>"><?= htmlspecialchars($overdue) ?></span></td>
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

    <!-- Modal Riwayat Pembayaran -->
    <div class="modal fade" id="modalHistoryPembayaran" tabindex="-1" role="dialog" aria-labelledby="modalHistoryPembayaranLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalHistoryPembayaranLabel">
                        <i class="fas fa-history mr-2"></i>Riwayat Pembayaran Faktur: <strong id="historiNoFaktur">-</strong>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Cara Pembayaran</th>
                                    <th>Status BG</th>
                                    <th>Tanggal BG Cair</th>
                                    <th class="text-right">Diskon</th>
                                    <th class="text-right">Jumlah Bayar</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="tableHistoriPembayaranBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>

<script>
$(document).ready(function () {
    function updateSisaHari() {
        var filterVal = $('#filterTanggalSisa').val();
        if (!filterVal) return;
        var selectedDate = new Date(filterVal);
        selectedDate.setHours(0, 0, 0, 0);

        $('.col-sisa-hari').each(function () {
            var tglTempoStr = $(this).data('tanggal-tempo');
            if (!tglTempoStr || tglTempoStr === '0000-00-00') {
                $(this).html('-');
                return;
            }
            var tglTempo = new Date(tglTempoStr);
            tglTempo.setHours(0, 0, 0, 0);

            var diffTime = tglTempo.getTime() - selectedDate.getTime();
            var diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));

            $(this).attr('data-order', diffDays);

            if (diffDays >= 0) {
                $(this).html("<span class='text-success'>" + diffDays + " hari</span>");
            } else {
                $(this).html("<span class='text-danger'>" + diffDays + " hari</span>");
            }
        });
    }

    var table = null;
    if ($.fn.DataTable) {
        table = $('#tabelPiutangCollection').DataTable({
            "order": [[3, "asc"]], // Default sort by Sisa Hari ascending
            "pageLength": 25
        });

        table.on('draw', function () {
            updateSisaHari();
        });
    }

    // Run initially
    updateSisaHari();

    // Bind change event
    $('#filterTanggalSisa').on('change', function () {
        updateSisaHari();
        if (table) {
            table.rows().invalidate().draw(false);
        }
    });

    // Use event delegation so it works even after datatable pagination/filtering
    $(document).on('click', '.btn-history-pembayaran', function () {
        var idFaktur = $(this).data('id-faktur');
        var noFaktur = $(this).data('no-faktur');

        $('#historiNoFaktur').text(noFaktur);
        $('#tableHistoriPembayaranBody').html('<tr><td colspan="7" class="text-center text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat riwayat pembayaran...</td></tr>');
        $('#modalHistoryPembayaran').modal('show');

        $.ajax({
            url: '<?= base_url("keuangan/pembayaran/get_payment_history_json") ?>',
            type: 'GET',
            dataType: 'JSON',
            data: { id_faktur: idFaktur },
            success: function (response) {
                if (response && response.status) {
                    var items = response.data;
                    var html = '';
                    if (items && items.length > 0) {
                        items.forEach(function (item) {
                            var tgl = item.tanggal_pembayaran ? new Date(item.tanggal_pembayaran).toLocaleDateString('id-ID') : '-';
                            var diskon = parseFloat(item.jumlah_diskon || 0);
                            var bayar = parseFloat(item.jumlah_pembayaran || 0);
                            
                            var statusBgLabel = '-';
                            if (item.metode_pembayaran && item.metode_pembayaran.toLowerCase() === 'bg') {
                                if (item.status_bg === 'cair') {
                                    statusBgLabel = '<span class="badge badge-success">Sudah Cair</span>';
                                } else {
                                    statusBgLabel = '<span class="badge badge-warning">Belum Cair</span>';
                                }
                            }
                            var tglBgCair = item.tanggal_bg_cair ? new Date(item.tanggal_bg_cair).toLocaleDateString('id-ID') : '-';

                            var caraBayar = item.cara_pembayaran || item.metode_pembayaran || '-';
                            if (typeof caraBayar === 'string' && ['cash', 'transfer', 'bg', 'tempo'].indexOf(caraBayar.toLowerCase()) !== -1) {
                                caraBayar = caraBayar.charAt(0).toUpperCase() + caraBayar.slice(1).toLowerCase();
                            }

                            html += '<tr>' +
                                '<td>' + tgl + '</td>' +
                                '<td>' + caraBayar + '</td>' +
                                '<td>' + statusBgLabel + '</td>' +
                                '<td>' + tglBgCair + '</td>' +
                                '<td class="text-right text-muted">Rp ' + diskon.toLocaleString('id-ID') + '</td>' +
                                '<td class="text-right font-weight-bold text-success">Rp ' + bayar.toLocaleString('id-ID') + '</td>' +
                                '<td><small>' + (item.keterangan || '-') + '</small></td>' +
                                '</tr>';
                        });
                    } else {
                        html = '<tr><td colspan="7" class="text-center text-muted py-3">Belum ada histori pembayaran untuk faktur ini.</td></tr>';
                    }
                    $('#tableHistoriPembayaranBody').html(html);
                } else {
                    $('#tableHistoriPembayaranBody').html('<tr><td colspan="7" class="text-center text-danger">' + (response.message || 'Gagal memuat data.') + '</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                $('#tableHistoriPembayaranBody').html('<tr><td colspan="7" class="text-center text-danger">Terjadi kesalahan koneksi: ' + error + '</td></tr>');
            }
        });
    });
});
</script>
