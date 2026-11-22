<!-- views/content/sales/so_loading_riwayat.php -->
<style>
    .summary-card {
        border-radius: 8px;
        border: 1px solid #e9ecef;
        background: #fff;
        padding: 14px 16px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .summary-card .summary-label {
        font-size: 12px;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .summary-card .summary-value {
        font-size: 24px;
        font-weight: 700;
        line-height: 1.2;
        margin-top: 2px;
    }
    .summary-card .summary-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    #tabelRiwayatSoLoading th,
    #tabelRiwayatSoLoading td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .badge-rute {
        background: #e3f2fd;
        color: #0d47a1;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
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
                        <h1 class="m-0">
                            <i class="fas fa-truck-loading mr-2 text-info"></i>Riwayat SO Loading
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order') ?>">Sales Order</a></li>
                            <li class="breadcrumb-item active">Riwayat SO Loading</li>
                        </ol>
                    </div>
                </div>

                <div class="mb-2 d-flex flex-wrap align-items-center" style="gap: 8px;">
                    <a href="<?= base_url('sales_order') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Sales Order
                    </a>
                    <a href="<?= base_url('sales_order/so_rute') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-map-marked-alt mr-1"></i>SO per Rute
                    </a>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Kartu Ringkasan -->
                <div class="row mb-3">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="summary-card">
                            <div>
                                <div class="summary-label">Total SO Siap / Loading</div>
                                <div class="summary-value text-primary"><?= (int)($summary['total_so'] ?? 0) ?></div>
                            </div>
                            <div class="summary-icon bg-primary text-white">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="summary-card">
                            <div>
                                <div class="summary-label">Siap Loading / Verifikasi</div>
                                <div class="summary-value text-warning"><?= (int)($summary['siap_loading'] ?? 0) ?></div>
                            </div>
                            <div class="summary-icon bg-warning text-white">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="summary-card">
                            <div>
                                <div class="summary-label">Sudah Masuk DO</div>
                                <div class="summary-value text-success"><?= (int)($summary['sudah_do'] ?? 0) ?></div>
                            </div>
                            <div class="summary-icon bg-success text-white">
                                <i class="fas fa-truck"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="summary-card">
                            <div>
                                <div class="summary-label">Dimuat / Ada Ditolak</div>
                                <div class="summary-value text-info">
                                    <?= (int)($summary['menunggu_do'] ?? 0) ?>
                                    <small class="text-danger" style="font-size:14px;">/ <?= (int)($summary['ada_ditolak'] ?? 0) ?></small>
                                </div>
                            </div>
                            <div class="summary-icon bg-info text-white">
                                <i class="fas fa-boxes"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Pencarian -->
                <div class="card card-outline card-info">
                    <div class="card-header py-2">
                        <h3 class="card-title" style="font-size:15px;">
                            <i class="fas fa-filter mr-1"></i>Filter Riwayat SO Loading
                        </h3>
                    </div>
                    <div class="card-body py-2">
                        <form method="get" action="<?= base_url('sales_order/riwayat_loading') ?>" class="row align-items-end">
                            <input type="hidden" name="filter" value="1">
                            <div class="col-md-2 col-sm-6 form-group mb-2">
                                <label class="small font-weight-bold mb-1">Tanggal Dari</label>
                                <input type="date" name="tanggal_dari" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($filter['tanggal_dari'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-2 col-sm-6 form-group mb-2">
                                <label class="small font-weight-bold mb-1">Tanggal Sampai</label>
                                <input type="date" name="tanggal_sampai" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($filter['tanggal_sampai'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-2 col-sm-6 form-group mb-2">
                                <label class="small font-weight-bold mb-1">Rute</label>
                                <select name="kd_rute" class="form-control form-control-sm">
                                    <option value="">-- Semua Rute --</option>
                                    <?php foreach (($route_options ?? []) as $opt): ?>
                                        <option value="<?= htmlspecialchars($opt['kd_rute'], ENT_QUOTES, 'UTF-8') ?>"
                                            <?= (($filter['kd_rute'] ?? '') === $opt['kd_rute']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($opt['kd_rute'] . ' - ' . $opt['nama_rute'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 form-group mb-2">
                                <label class="small font-weight-bold mb-1">Status Loading</label>
                                <select name="status_loading" class="form-control form-control-sm">
                                    <option value="">-- Semua Status --</option>
                                    <option value="siap_loading" <?= (($filter['status_loading'] ?? '') === 'siap_loading') ? 'selected' : '' ?>>Siap Loading / Verifikasi</option>
                                    <option value="menunggu_do" <?= (($filter['status_loading'] ?? '') === 'menunggu_do') ? 'selected' : '' ?>>Dimuat (Menunggu DO)</option>
                                    <option value="sudah_do" <?= (($filter['status_loading'] ?? '') === 'sudah_do') ? 'selected' : '' ?>>Sudah Masuk DO</option>
                                    <option value="ada_ditolak" <?= (($filter['status_loading'] ?? '') === 'ada_ditolak') ? 'selected' : '' ?>>Ada Item Tidak Dimuat</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-8 form-group mb-2">
                                <label class="small font-weight-bold mb-1">Kata Kunci</label>
                                <input type="text" name="q" class="form-control form-control-sm"
                                    placeholder="No SO / Customer / Sales / DO..."
                                    value="<?= htmlspecialchars($filter['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-2 col-sm-4 form-group mb-2 d-flex" style="gap: 6px;">
                                <button type="submit" class="btn btn-info btn-sm flex-fill">
                                    <i class="fas fa-search mr-1"></i>Cari
                                </button>
                                <a href="<?= base_url('sales_order/riwayat_loading?filter=1') ?>" class="btn btn-outline-secondary btn-sm" title="Tampilkan semua data tanpa batas tanggal">
                                    Semua
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabel Riwayat -->
                <div class="card card-outline card-primary">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h3 class="card-title" style="font-size:15px;">
                            <i class="fas fa-list mr-1"></i>Daftar Sales Order Sudah Di-Loading
                            <span class="badge badge-primary ml-1"><?= count($rows ?? []) ?> SO</span>
                        </h3>
                    </div>
                    <div class="card-body p-2">
                        <?php if (empty($rows)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 text-gray"></i>
                                <p class="mb-1">Belum ada riwayat SO yang sudah di-loading sesuai filter.</p>
                                <small>Coba ubah rentang tanggal atau klik tombol <b>Semua</b>.</small>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="tabelRiwayatSoLoading" class="table table-bordered table-hover table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center" style="width:45px;">No</th>
                                            <th style="width:100px;">Tanggal SO</th>
                                            <th>No SO</th>
                                            <th>Customer / Kios</th>
                                            <th>Salesman</th>
                                            <th class="text-center">Rute</th>
                                            <th>Trip</th>
                                            <th class="text-center">Item Dimuat</th>
                                            <th>No Faktur / DO</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center" style="width:130px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($rows as $row):
                                            $total_item        = (int)$row['total_item'];
                                            $item_dimuat       = (int)$row['item_dimuat'];
                                            $item_tidak_dimuat = (int)$row['item_tidak_dimuat'];
                                            $has_do            = !empty($row['kd_do_list']);

                                            if ($item_tidak_dimuat > 0) {
                                                $status_badge = '<span class="badge badge-danger">Ada Item Tidak Dimuat</span>';
                                            } elseif ($has_do) {
                                                $status_badge = '<span class="badge badge-success">Sudah Masuk DO</span>';
                                            } elseif ($item_dimuat > 0) {
                                                $status_badge = '<span class="badge badge-info">Dimuat (Menunggu DO)</span>';
                                            } elseif (($row['so_status'] ?? '') === 'sedang_verifikasi') {
                                                $status_badge = '<span class="badge badge-warning">Verifikasi / Siap Loading</span>';
                                            } else {
                                                $status_badge = '<span class="badge badge-primary">Siap Loading (Proses Checker)</span>';
                                            }
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= !empty($row['tanggal_transaksi']) ? date('d/m/Y', strtotime($row['tanggal_transaksi'])) : '-' ?></td>
                                                <td class="font-weight-bold">
                                                    <a href="<?= base_url('sales_order/detail/' . (int)$row['id_so']) ?>" class="text-primary" title="Buka Detail SO">
                                                        <?= htmlspecialchars($row['no_so'], ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold"><?= htmlspecialchars($row['customer_name'] ?: '-', ENT_QUOTES, 'UTF-8') ?></div>
                                                    <?php if (!empty($row['nama_kios'])): ?>
                                                        <small class="text-muted"><?= htmlspecialchars($row['nama_kios'], ENT_QUOTES, 'UTF-8') ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['create_by'] ?: '-', ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="text-center">
                                                    <span class="badge-rute" title="<?= htmlspecialchars($row['nama_rute'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= htmlspecialchars($row['kd_rute'], ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['kode_trip'])): ?>
                                                        <span class="font-weight-bold"><?= htmlspecialchars($row['kode_trip'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-success" title="Item dimuat">
                                                        <i class="fas fa-check mr-1"></i><?= $item_dimuat ?> / <?= $total_item ?>
                                                    </span>
                                                    <?php if ($item_tidak_dimuat > 0): ?>
                                                        <span class="badge badge-danger ml-1" title="Item tidak dimuat">
                                                            <i class="fas fa-times mr-1"></i><?= $item_tidak_dimuat ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['no_faktur_list'])): ?>
                                                        <div><small class="text-muted">Faktur:</small> <b><?= htmlspecialchars($row['no_faktur_list'], ENT_QUOTES, 'UTF-8') ?></b></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($row['kd_do_list'])): ?>
                                                        <div><small class="text-muted">DO:</small> <span class="text-success font-weight-bold"><?= htmlspecialchars($row['kd_do_list'], ENT_QUOTES, 'UTF-8') ?></span></div>
                                                    <?php endif; ?>
                                                    <?php if (empty($row['no_faktur_list']) && empty($row['kd_do_list'])): ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?= $status_badge ?></td>
                                                <td class="text-center">
                                                    <button type="button"
                                                            class="btn btn-info btn-xs btn-detail-riwayat-so"
                                                            data-id="<?= (int)$row['id_so'] ?>"
                                                            data-noso="<?= htmlspecialchars($row['no_so'], ENT_QUOTES, 'UTF-8') ?>"
                                                            title="Lihat rincian item yang dimuat">
                                                        <i class="fas fa-boxes mr-1"></i>Item
                                                    </button>
                                                    <a href="<?= base_url('sales_order/detail/' . (int)$row['id_so']) ?>"
                                                       class="btn btn-outline-primary btn-xs"
                                                       title="Buka halaman detail SO">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
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

<!-- Modal Detail Item Loading SO -->
<div class="modal fade" id="modalDetailRiwayatSo" tabindex="-1" role="dialog" aria-labelledby="modalDetailRiwayatSoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white py-2">
                <h5 class="modal-title" id="modalDetailRiwayatSoLabel" style="font-size:16px;">
                    <i class="fas fa-box-open mr-1"></i>Detail Riwayat Loading SO
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailRiwayatSoBody">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                    <div>Memuat detail item...</div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <a href="#" id="btnLinkDetailSoModal" class="btn btn-primary btn-sm" style="display:none;">
                    <i class="fas fa-external-link-alt mr-1"></i>Buka Detail SO
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
// Escape HTML untuk mencegah XSS saat merender konten dinamis pada modal
function escapeHtmlSoRiwayat(str) {
    return String(str === null || str === undefined ? '' : str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatQtySoRiwayat(val) {
    var num = parseFloat(val || 0);
    if (isNaN(num)) return '0';
    return Number.isInteger(num) ? num.toLocaleString('id-ID') : num.toLocaleString('id-ID', { maximumFractionDigits: 2 });
}

$(function() {
    // Inisialisasi DataTables jika plugin tersedia
    if ($.fn.DataTable && $('#tabelRiwayatSoLoading').length) {
        $('#tabelRiwayatSoLoading').DataTable({
            pageLength: 25,
            order: [],
            language: {
                search: 'Cari cepat:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ SO',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                zeroRecords: 'Tidak ada data yang cocok'
            }
        });
    }

    // Tampilkan modal rincian item loading SO via AJAX
    $(document).on('click', '.btn-detail-riwayat-so', function() {
        var idSo = $(this).data('id');
        var noSo = $(this).data('noso');
        $('#modalDetailRiwayatSoLabel').html('<i class="fas fa-box-open mr-1"></i>Detail Riwayat Loading SO: ' + escapeHtmlSoRiwayat(noSo));
        $('#btnLinkDetailSoModal').attr('href', '<?= base_url("sales_order/detail/") ?>' + encodeURIComponent(idSo)).show();
        $('#modalDetailRiwayatSoBody').html(
            '<div class="text-center py-4 text-muted">' +
                '<i class="fas fa-spinner fa-spin fa-2x mb-2"></i>' +
                '<div>Memuat detail item...</div>' +
            '</div>'
        );
        $('#modalDetailRiwayatSo').modal('show');

        $.ajax({
            url: '<?= base_url("sales_order/riwayat_loading_detail/") ?>' + encodeURIComponent(idSo),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (!res || !res.status) {
                    $('#modalDetailRiwayatSoBody').html(
                        '<div class="alert alert-danger mb-0">' + escapeHtmlSoRiwayat((res && res.message) || 'Gagal memuat detail SO.') + '</div>'
                    );
                    return;
                }

                var h = res.header || {};
                var items = res.items || [];
                var html = '' +
                    '<div class="row mb-3 small">' +
                        '<div class="col-md-4"><b>No SO:</b> ' + escapeHtmlSoRiwayat(h.no_so || '-') + '<br><b>Tanggal:</b> ' + escapeHtmlSoRiwayat(h.tanggal_transaksi || '-') + '<br><b>Salesman:</b> ' + escapeHtmlSoRiwayat(h.create_by || '-') + '</div>' +
                        '<div class="col-md-4"><b>Customer:</b> ' + escapeHtmlSoRiwayat(h.customer_name || '-') + '<br><b>Kios:</b> ' + escapeHtmlSoRiwayat(h.nama_kios || '-') + '</div>' +
                        '<div class="col-md-4"><b>Rute:</b> ' + escapeHtmlSoRiwayat((h.kd_rute || '-') + (h.nama_rute ? ' (' + h.nama_rute + ')' : '')) + '<br><b>Trip:</b> ' + escapeHtmlSoRiwayat(h.kode_trip || '-') + '</div>' +
                    '</div>' +
                    '<div class="table-responsive">' +
                        '<table class="table table-bordered table-sm mb-0">' +
                            '<thead class="thead-light">' +
                                '<tr>' +
                                    '<th class="text-center" style="width:40px;">No</th>' +
                                    '<th>Kode Barang</th>' +
                                    '<th>Nama Barang</th>' +
                                    '<th class="text-right">Qty Siap</th>' +
                                    '<th class="text-right">Qty Dimuat</th>' +
                                    '<th class="text-right">Tidak Terkirim</th>' +
                                    '<th>Faktur / DO</th>' +
                                    '<th class="text-center">Status Muat</th>' +
                                '</tr>' +
                            '</thead>' +
                            '<tbody>';

                if (!items.length) {
                    html += '<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada item pada SO ini.</td></tr>';
                } else {
                    items.forEach(function(it, idx) {
                        var loaded = parseInt(it.checker_loaded || 0, 10);
                        var hasDo = !!(it.kd_do_list && String(it.kd_do_list).trim() !== '');
                        var qtyDimuat = it.qty_checker_loaded !== null && it.qty_checker_loaded !== ''
                            ? it.qty_checker_loaded
                            : (loaded === 1 || hasDo ? it.qty_siap : 0);

                        var badge = '<span class="badge badge-secondary">Belum Dipilih</span>';
                        var rowClass = '';
                        if (loaded === 2) {
                            badge = '<span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Tidak Dimuat</span>';
                            rowClass = 'table-danger';
                        } else if (hasDo) {
                            badge = '<span class="badge badge-success"><i class="fas fa-truck mr-1"></i>Sudah DO</span>';
                            rowClass = 'table-success';
                        } else if (loaded === 1) {
                            badge = '<span class="badge badge-info"><i class="fas fa-check mr-1"></i>Dimuat</span>';
                            rowClass = 'table-info';
                        }

                        var docInfo = '';
                        if (it.no_faktur_list) docInfo += '<div><small class="text-muted">Faktur:</small> <b>' + escapeHtmlSoRiwayat(it.no_faktur_list) + '</b></div>';
                        if (it.kd_do_list) docInfo += '<div><small class="text-muted">DO:</small> <span class="text-success font-weight-bold">' + escapeHtmlSoRiwayat(it.kd_do_list) + '</span></div>';
                        if (!docInfo) docInfo = '<span class="text-muted">-</span>';

                        html += '<tr class="' + rowClass + '">' +
                            '<td class="text-center">' + (idx + 1) + '</td>' +
                            '<td>' + escapeHtmlSoRiwayat(it.kd_barang || '-') + '</td>' +
                            '<td>' + escapeHtmlSoRiwayat(it.nama_barang || '-') + ' <small class="text-muted">(' + escapeHtmlSoRiwayat(it.satuan || 'PCS') + ')</small></td>' +
                            '<td class="text-right">' + formatQtySoRiwayat(it.qty_siap) + '</td>' +
                            '<td class="text-right font-weight-bold">' + formatQtySoRiwayat(qtyDimuat) + '</td>' +
                            '<td class="text-right">' + formatQtySoRiwayat(it.qty_tidak_terkirim) + '</td>' +
                            '<td>' + docInfo + '</td>' +
                            '<td class="text-center">' + badge + '</td>' +
                        '</tr>';
                    });
                }

                html += '</tbody></table></div>';
                $('#modalDetailRiwayatSoBody').html(html);
            },
            error: function() {
                $('#modalDetailRiwayatSoBody').html(
                    '<div class="alert alert-danger mb-0">Terjadi kesalahan koneksi saat memuat detail item SO.</div>'
                );
            }
        });
    });
});
</script>

