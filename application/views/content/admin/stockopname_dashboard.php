<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper stockopname-admin">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-7">
                        <h1 class="m-0">Dashboard Admin Stockopname</h1>
                    </div>
                    <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Dashboard
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" id="btnRefreshStockopname">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <style>
                    .stockopname-admin{background:#f5f7fb}
                    .so-panel{background:#fff;border:1px solid #e1e7ef;border-radius:8px;box-shadow:0 8px 22px rgba(16,24,40,.06)}
                    .so-panel-header{padding:14px 16px;border-bottom:1px solid #e8edf3;display:flex;align-items:center;justify-content:space-between;gap:10px}
                    .so-panel-title{font-weight:700;color:#1f2937;margin:0;font-size:16px}
                    .so-stat{min-height:108px;padding:16px;border-radius:8px;border:1px solid #e1e7ef;background:#fff}
                    .so-stat-label{font-size:12px;text-transform:uppercase;color:#64748b;font-weight:700}
                    .so-stat-value{font-size:26px;font-weight:800;color:#111827;line-height:1.1;margin-top:8px}
                    .so-stat-meta{font-size:12px;color:#6b7280;margin-top:6px}
                    .so-stat.info{border-left:4px solid #2563eb}.so-stat.success{border-left:4px solid #16a34a}.so-stat.warning{border-left:4px solid #f59e0b}.so-stat.danger{border-left:4px solid #dc2626}.so-stat.master{border-left:4px solid #0f766e}
                    .so-stat-link{display:block;color:inherit}.so-stat-link:hover{color:inherit;text-decoration:none}.so-stat-link:hover .so-stat{border-color:#8bb8ad;box-shadow:0 12px 28px rgba(15,118,110,.12)}
                    .so-filter{display:grid;grid-template-columns:minmax(220px,1fr) 180px 96px;gap:10px}
                    .so-table-wrap{padding:16px}.so-activity{list-style:none;margin:0;padding:0}.so-activity li{display:flex;gap:10px;padding:11px 0;border-bottom:1px solid #edf2f7}.so-activity li:last-child{border-bottom:0}
                    .so-dot{width:10px;height:10px;border-radius:50%;background:#2563eb;margin-top:6px;flex:0 0 10px}.so-badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 9px;font-size:12px;font-weight:700}
                    .so-badge.match{background:#dcfce7;color:#166534}.so-badge.selisih{background:#ffedd5;color:#9a3412}.so-badge.belum{background:#e5e7eb;color:#374151}
                    .so-number-plus{color:#15803d;font-weight:700}.so-number-minus{color:#b91c1c;font-weight:700}.so-number-zero{color:#374151;font-weight:700}
                    .so-demo-result{border:1px dashed #cbd5e1;border-radius:8px;padding:12px;background:#f8fafc;min-height:72px}
                    .progress{height:8px;border-radius:99px}.table td,.table th{vertical-align:middle}.btn i{margin-right:5px}
                    @media(max-width:768px){.so-filter{grid-template-columns:1fr}.so-stat-value{font-size:22px}.so-panel-header{align-items:flex-start;flex-direction:column}.content-header h1{font-size:22px}}
                </style>

                <div class="row">
                    <div class="col-6 col-md-4 col-xl mb-3">
                        <div class="so-stat info">
                            <div class="so-stat-label">Progress Input</div>
                            <div class="so-stat-value"><span id="soProgress">0</span>%</div>
                            <div class="progress mt-2"><div class="progress-bar bg-primary" id="soProgressBar" style="width:0%"></div></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl mb-3">
                        <div class="so-stat success">
                            <div class="so-stat-label">Match</div>
                            <div class="so-stat-value" id="soMatch">0</div>
                            <div class="so-stat-meta">Item sesuai sistem</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl mb-3">
                        <div class="so-stat warning">
                            <div class="so-stat-label">Selisih</div>
                            <div class="so-stat-value" id="soSelisih">0</div>
                            <div class="so-stat-meta">Item perlu validasi</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl mb-3">
                        <div class="so-stat danger">
                            <div class="so-stat-label">Belum Input</div>
                            <div class="so-stat-value" id="soBelum">0</div>
                            <div class="so-stat-meta">Item belum dihitung</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl mb-3">
                        <a class="so-stat-link" href="<?= base_url('admin/stockopname/master-barang') ?>">
                            <div class="so-stat master">
                                <div class="so-stat-label">Master Barang</div>
                                <div class="so-stat-value" id="soMasterBarang">0</div>
                                <div class="so-stat-meta"><span id="soMasterKategori">0</span> kategori dummy</div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mb-3">
                        <div class="so-panel h-100">
                            <div class="so-panel-header">
                                <h2 class="so-panel-title">Rekonsiliasi Stok</h2>
                                <div class="so-filter">
                                    <input type="search" class="form-control form-control-sm" id="soSearch" placeholder="Cari barang, kode, expired, lot">
                                    <select class="form-control form-control-sm" id="soStatus">
                                        <option value="">Semua status</option>
                                        <option value="match">Match</option>
                                        <option value="selisih">Selisih</option>
                                        <option value="belum">Belum input</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="soReset"><i class="fas fa-undo"></i>Reset</button>
                                </div>
                            </div>
                            <div class="so-table-wrap">
                                <table class="table table-sm table-hover table-bordered w-100" id="tableAdminStockopname">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Kode</th>
                                            <th>Barang</th>
                                            <th>Expired</th>
                                            <th>Lot</th>
                                            <th>Sistem</th>
                                            <th>Fisik</th>
                                            <th>Selisih</th>
                                            <th>Status</th>
                                            <th>Update</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-3">
                        <div class="so-panel mb-3">
                            <div class="so-panel-header">
                                <h2 class="so-panel-title">Komposisi Status</h2>
                            </div>
                            <div class="p-3">
                                <canvas id="soStatusChart" height="190"></canvas>
                            </div>
                        </div>

                        <div class="so-panel">
                            <div class="so-panel-header">
                                <h2 class="so-panel-title">Input Terakhir</h2>
                            </div>
                            <div class="p-3">
                                <ul class="so-activity" id="soRecentInputs">
                                    <li><span class="so-dot"></span><div class="text-muted">Memuat data...</div></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7 mb-3">
                        <div class="so-panel h-100">
                            <div class="so-panel-header">
                                <h2 class="so-panel-title">Selisih Terbesar</h2>
                            </div>
                            <div class="table-responsive p-3">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Barang</th>
                                            <th class="text-right">Sistem</th>
                                            <th class="text-right">Fisik</th>
                                            <th class="text-right">Selisih</th>
                                        </tr>
                                    </thead>
                                    <tbody id="soTopVariance">
                                        <tr><td colspan="4" class="text-muted">Memuat data...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 mb-3">
                        <div class="so-panel h-100">
                            <div class="so-panel-header">
                                <h2 class="so-panel-title">Demo Input Stockopname</h2>
                            </div>
                            <form class="p-3" id="formStockopnameDemo">
                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label>Kode</label>
                                        <input class="form-control form-control-sm" name="kode_barang" value="DEMO-001" required>
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label>Nama Barang</label>
                                        <input class="form-control form-control-sm" name="nama_barang" value="Barang Simulasi Stockopname" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Qty Sistem</label>
                                        <input type="number" class="form-control form-control-sm" name="qty_buku" value="100" min="0" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Qty Fisik</label>
                                        <input type="number" class="form-control form-control-sm" name="qty_fisik" value="96" min="0" required>
                                    </div>
                                </div>
                                <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-calculator"></i>Hitung Simulasi</button>
                            </form>
                            <div class="px-3 pb-3">
                                <div class="so-demo-result" id="soDemoResult">
                                    <span class="text-muted">Belum ada hasil simulasi.</span>
                                </div>
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
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
        </div>
    </footer>
</div>

<script>
window.addEventListener('load', function () {
    var statusChart = null;
    var table = $('#tableAdminStockopname').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searchDelay: 350,
        lengthMenu: [[10, 25, 50], [10, 25, 50]],
        ajax: {
            url: '<?= base_url('admin/stockopname/list') ?>',
            type: 'POST',
            data: function (d) {
                d.search = {value: $('#soSearch').val()};
                d.status = $('#soStatus').val();
            }
        },
        columns: [
            {data: 'id', width: '50px'},
            {data: 'kode_barang'},
            {data: 'nama_barang'},
            {data: 'expired_date'},
            {data: 'no_lot'},
            {data: 'qty_buku', className: 'text-right', render: formatNumber},
            {data: 'qty_fisik', className: 'text-right', render: formatNumber},
            {data: 'selisih', className: 'text-right', render: renderSelisih},
            {data: 'status_opname', render: renderStatus},
            {data: 'last_input', render: function (data) { return data || '-'; }}
        ],
        order: [[9, 'desc']]
    });

    function formatNumber(value) {
        value = parseInt(value || 0, 10);
        return value.toLocaleString('id-ID');
    }

    function renderSelisih(value) {
        value = parseInt(value || 0, 10);
        var cls = value > 0 ? 'so-number-plus' : (value < 0 ? 'so-number-minus' : 'so-number-zero');
        return '<span class="' + cls + '">' + value.toLocaleString('id-ID') + '</span>';
    }

    function renderStatus(value) {
        var label = value === 'match' ? 'Match' : (value === 'belum' ? 'Belum Input' : 'Selisih');
        return '<span class="so-badge ' + value + '">' + label + '</span>';
    }

    function toast(icon, title) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: 2200
        });
    }

    function loadWidgets(showToast) {
        $.getJSON('<?= base_url('admin/stockopname/widgets') ?>', function (res) {
            if (!res.status) {
                toast('error', res.message || 'Gagal memuat widget');
                return;
            }
            var data = res.data || {};
            renderSummary(data.summary || {});
            renderMasterBarang(data.master_barang || {});
            renderRecent(data.recent_inputs || []);
            renderVariance(data.top_variance || []);
            renderChart(data.summary || {});
            if (showToast) toast('success', 'Dashboard stockopname diperbarui');
        }).fail(function () {
            toast('error', 'Server tidak merespons');
        });
    }

    function renderSummary(summary) {
        var progress = parseFloat(summary.progress || 0);
        $('#soProgress').text(progress.toLocaleString('id-ID'));
        $('#soProgressBar').css('width', Math.min(progress, 100) + '%');
        $('#soMatch').text(formatNumber(summary.match_item));
        $('#soSelisih').text(formatNumber(summary.selisih_item));
        $('#soBelum').text(formatNumber(summary.belum_item));
    }

    function renderMasterBarang(summary) {
        $('#soMasterBarang').text(formatNumber(summary.total_item));
        $('#soMasterKategori').text(formatNumber(summary.kategori));
    }

    function renderRecent(rows) {
        if (!rows.length) {
            $('#soRecentInputs').html('<li><span class="so-dot"></span><div class="text-muted">Belum ada input opname.</div></li>');
            return;
        }
        var html = rows.map(function (row) {
            return '<li><span class="so-dot"></span><div><div class="font-weight-bold">' + escapeHtml(row.nama_barang || '-') + '</div><small class="text-muted">' + escapeHtml(row.input_by || row.inputers || '-') + ' | Qty ' + formatNumber(row.qty || row.qty_fisik || 0) + ' | ' + escapeHtml(row.create_at || row.last_input || '-') + '</small></div></li>';
        }).join('');
        $('#soRecentInputs').html(html);
    }

    function renderVariance(rows) {
        if (!rows.length) {
            $('#soTopVariance').html('<tr><td colspan="4" class="text-muted">Tidak ada selisih.</td></tr>');
            return;
        }
        var html = rows.map(function (row) {
            return '<tr><td><div class="font-weight-bold">' + escapeHtml(row.nama_barang || '-') + '</div><small class="text-muted">' + escapeHtml(row.kode_barang || '-') + '</small></td><td class="text-right">' + formatNumber(row.qty_buku) + '</td><td class="text-right">' + formatNumber(row.qty_fisik) + '</td><td class="text-right">' + renderSelisih(row.selisih) + '</td></tr>';
        }).join('');
        $('#soTopVariance').html(html);
    }

    function renderChart(summary) {
        var ctx = document.getElementById('soStatusChart');
        var values = [
            parseInt(summary.match_item || 0, 10),
            parseInt(summary.selisih_item || 0, 10),
            parseInt(summary.belum_item || 0, 10)
        ];
        if (statusChart) {
            statusChart.data.datasets[0].data = values;
            statusChart.update();
            return;
        }
        statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Match', 'Selisih', 'Belum Input'],
                datasets: [{
                    data: values,
                    backgroundColor: ['#16a34a', '#f59e0b', '#64748b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {legend: {position: 'bottom'}}
            }
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    $('#soSearch').on('keyup', function () {
        table.ajax.reload();
    });
    $('#soStatus').on('change', function () {
        table.ajax.reload();
    });
    $('#soReset').on('click', function () {
        $('#soSearch').val('');
        $('#soStatus').val('');
        table.ajax.reload();
    });
    $('#btnRefreshStockopname').on('click', function () {
        table.ajax.reload(null, false);
        loadWidgets(true);
    });
    $('#formStockopnameDemo').on('submit', function (event) {
        event.preventDefault();
        $.ajax({
            url: '<?= base_url('admin/stockopname/demo-preview') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (res) {
                if (!res.status) {
                    toast('warning', res.message || 'Validasi gagal');
                    return;
                }
                var row = res.data;
                $('#soDemoResult').html(
                    '<div class="d-flex justify-content-between align-items-center flex-wrap">' +
                    '<div><div class="font-weight-bold">' + escapeHtml(row.nama_barang) + '</div><small class="text-muted">' + escapeHtml(row.kode_barang) + '</small></div>' +
                    '<div class="text-right"><div>' + renderStatus(row.status_opname) + '</div><div class="mt-1">' + renderSelisih(row.selisih) + '</div></div>' +
                    '</div><div class="text-muted mt-2">' + escapeHtml(row.message) + '</div>'
                );
                toast(row.status_opname === 'match' ? 'success' : 'info', res.message);
            },
            error: function () {
                toast('error', 'Server tidak merespons');
            }
        });
    });

    loadWidgets(false);
});
</script>
