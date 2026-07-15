<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header d-none">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                    </div>
                    <div class="col-sm-6">
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="mb-2">
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary" title="Home">
                        <i class="fas fa-home"></i>
                    </a>
                </div>

                <div class="row" id="gudang-cards">
                    <?php foreach (($gudang_summary ?? []) as $g): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 gudang-card-wrap">
                            <button type="button" class="info-box btn btn-block text-left p-0 btn-gudang-card"
                                    data-gudang="<?= htmlspecialchars((string)$g['gudang_id'], ENT_QUOTES, 'UTF-8') ?>">
                                <span class="info-box-icon bg-primary"><i class="fas fa-warehouse"></i></span>
                                <span class="info-box-content">
                                    <span class="info-box-text"><?= htmlspecialchars($g['nama_gudang'] ?: ('Gudang ' . $g['gudang_id'])) ?></span>
                                    <span class="info-box-number"><?= number_format((float)$g['qty_available'], 0, ',', '.') ?> pcs</span>
                                    <span class="text-muted small">
                                        SKU <?= number_format((int)$g['total_sku'], 0, ',', '.') ?> |
                                        Batch <?= number_format((int)$g['total_batch'], 0, ',', '.') ?> |
                                        Reserved <?= number_format((float)$g['qty_reserved'], 0, ',', '.') ?>
                                    </span>
                                </span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        <input type="hidden" id="filter-gudang" value="">
                        <button type="button" class="d-none" id="btn-clear-gudang"></button>
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-2">
                                <label class="mb-1">Cari Barang / Lot / Ref</label>
                                <input type="text" class="form-control" id="filter-search" placeholder="Kode, nama, lot, ref">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Kode Barang</label>
                                <input type="text" class="form-control" id="filter-kd" placeholder="Kode">
                            </div>
                            <div class="col-md-5 mb-2">
                                <button type="button" class="btn btn-primary" id="btn-load">
                                    <i class="fas fa-sync-alt"></i> Muat
                                </button>
                                <button type="button" class="btn btn-outline-secondary d-none" id="btn-preview-sync">
                                    <i class="fas fa-search"></i> Preview Sync
                                </button>
                            </div>
                            <div class="col-md-2 mb-2 text-md-right d-none">
                                <button type="button" class="btn btn-danger" id="btn-apply-sync">
                                    <i class="fas fa-database"></i> Apply Sync
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="stock-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="pill" href="#tab-items" role="tab">Summary Barang</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="#tab-batches" role="tab">Batch</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="#tab-ledger" role="tab">Ledger</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="pill" href="#tab-recon" role="tab">Rekonsiliasi</a>
                            </li>
                        </ul>

                        <div class="tab-content pt-3">
                            <div class="tab-pane fade show active" id="tab-items" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Kode</th>
                                                <th>Nama Barang</th>
                                                <th class="text-right">On Hand</th>
                                                <th class="text-right">Reserved</th>
                                                <th class="text-right">Available</th>
                                                <th class="text-right">Box</th>
                                                <th class="text-right">Pcs</th>
                                                <th>Exp Terdekat</th>
                                                <th>Sync</th>
                                            </tr>
                                        </thead>
                                        <tbody id="items-body"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-batches" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Kode</th>
                                                <th>Nama Barang</th>
                                                <th>Gudang</th>
                                                <th>Lot</th>
                                                <th>Expired</th>
                                                <th class="text-right">On Hand</th>
                                                <th class="text-right">Reserved</th>
                                                <th class="text-right">Available</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="batches-body"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-ledger" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Waktu</th>
                                                <th>Tipe</th>
                                                <th>Kode</th>
                                                <th>Nama Barang</th>
                                                <th>Gudang</th>
                                                <th>Lot</th>
                                                <th>Expired</th>
                                                <th class="text-right">Qty</th>
                                                <th>Ref</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ledger-body"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-recon" role="tabpanel">
                                <div class="alert alert-info py-2">
                                    Rekonsiliasi membandingkan saldo batch dengan akumulasi ledger. Tipe `MUTASI` belum dihitung sebagai fisik karena schema belum punya arah masuk/keluar.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Kode</th>
                                                <th>Nama Barang</th>
                                                <th>Gudang</th>
                                                <th>Lot</th>
                                                <th>Expired</th>
                                                <th class="text-right">Batch OH</th>
                                                <th class="text-right">Ledger OH</th>
                                                <th class="text-right">Diff OH</th>
                                                <th class="text-right">Diff Reserved</th>
                                                <th>Ledger Terakhir</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recon-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modal-sync" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Hasil Sinkronisasi</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div id="sync-summary" class="mb-2"></div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Aksi</th>
                                                <th>Kode</th>
                                                <th>Gudang</th>
                                                <th>Lot</th>
                                                <th>Expired</th>
                                                <th class="text-right">On Hand</th>
                                                <th class="text-right">Reserved</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sync-body"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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
(function() {
    var STOCK_URL = '<?= base_url('stock') ?>';
    var activeTab = 'items';
    var searchTimer = null;
    var requestToken = 0;
    var gudangRows = <?= json_encode(array_values($gudang_summary ?? []), JSON_UNESCAPED_UNICODE) ?>;

    function esc(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function num(v, d) {
        var n = parseFloat(v || 0);
        return n.toLocaleString('id-ID', {maximumFractionDigits: d || 0});
    }

    function selectedGudang() {
        return document.getElementById('filter-gudang').value || '';
    }

    function filters(extra) {
        var q = [];
        var search = document.getElementById('filter-search').value;
        var gdg = document.getElementById('filter-gudang').value;
        var kd = document.getElementById('filter-kd').value;
        if (extra) {
            Object.keys(extra).forEach(function(k) {
                if (extra[k] !== null && extra[k] !== undefined && extra[k] !== '') {
                    q.push(encodeURIComponent(k) + '=' + encodeURIComponent(extra[k]));
                }
            });
        }
        if (search) q.push('search=' + encodeURIComponent(search));
        if (gdg) q.push('gudang_id=' + encodeURIComponent(gdg));
        if (kd) q.push('kd_barang=' + encodeURIComponent(kd));
        return q.length ? '?' + q.join('&') : '';
    }

    function fetchJson(path, extra, options) {
        return fetch(STOCK_URL + path + filters(extra), options || {})
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(res) {
                if (res.status !== 'ok') throw new Error(res.message || 'Server error');
                return res.data;
            });
    }

    function loading(id, cols) {
        document.getElementById(id).innerHTML = '<tr><td colspan="' + cols + '" class="text-center py-3"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat...</td></tr>';
    }

    function empty(id, cols) {
        document.getElementById(id).innerHTML = '<tr><td colspan="' + cols + '" class="text-center text-muted py-3">Tidak ada data.</td></tr>';
    }

    function renderError(id, cols, err) {
        document.getElementById(id).innerHTML = '<tr><td colspan="' + cols + '" class="text-center text-danger py-3">'
            + '<i class="fas fa-exclamation-triangle mr-1"></i>' + esc(err.message || err) + '</td></tr>';
    }

    function totals(rows) {
        return rows.reduce(function(acc, r) {
            acc.total_sku += parseInt(r.total_sku || 0);
            acc.total_batch += parseInt(r.total_batch || 0);
            acc.qty_available += parseFloat(r.qty_available || 0);
            acc.qty_reserved += parseFloat(r.qty_reserved || 0);
            return acc;
        }, {total_sku: 0, total_batch: 0, qty_available: 0, qty_reserved: 0});
    }

    function gudangCard(g) {
        var selected = String(g.gudang_id || '') === String(selectedGudang());
        var iconBg = selected ? 'bg-success' : 'bg-primary';
        var activeClass = selected ? ' border border-success' : '';
        return '<div class="col-lg-3 col-md-4 col-sm-6 gudang-card-wrap">'
            + '<button type="button" class="info-box btn btn-block text-left p-0 btn-gudang-card' + activeClass + '" data-gudang="' + esc(g.gudang_id || '') + '">'
            + '<span class="info-box-icon ' + iconBg + '"><i class="fas fa-warehouse"></i></span>'
            + '<span class="info-box-content">'
            + '<span class="info-box-text">' + esc(g.nama_gudang || (g.gudang_id ? ('Gudang ' + g.gudang_id) : 'Semua Gudang')) + '</span>'
            + '<span class="info-box-number">' + num(g.qty_available) + ' pcs</span>'
            + '<span class="text-muted small">SKU ' + num(g.total_sku) + ' | Batch ' + num(g.total_batch) + ' | Reserved ' + num(g.qty_reserved) + '</span>'
            + '</span></button></div>';
    }

    function renderGudangs(rows) {
        gudangRows = rows || [];
        if (!gudangRows.length) {
            document.getElementById('gudang-cards').innerHTML = '<div class="col-12"><div class="alert alert-light border">Gudang tidak ditemukan.</div></div>';
            return;
        }

        var total = totals(gudangRows);
        total.gudang_id = '';
        total.nama_gudang = 'Semua Gudang';

        document.getElementById('gudang-cards').innerHTML = [gudangCard(total)]
            .concat(gudangRows.map(gudangCard))
            .join('');
    }

    function renderItems(rows) {
        if (!rows.length) return empty('items-body', 9);
        document.getElementById('items-body').innerHTML = rows.map(function(r) {
            return '<tr>'
                + '<td><code>' + esc(r.kd_barang) + '</code></td>'
                + '<td>' + esc(r.nama_barang || '-') + '</td>'
                + '<td class="text-right">' + num(r.qty_on_hand) + '</td>'
                + '<td class="text-right">' + num(r.qty_reserved) + '</td>'
                + '<td class="text-right font-weight-bold text-success">' + num(r.available_stock) + '</td>'
                + '<td class="text-right">' + num(r.available_box) + '</td>'
                + '<td class="text-right">' + num(r.available_ecer) + '</td>'
                + '<td>' + esc(r.nearest_expired_date || '-') + '</td>'
                + '<td><small>' + esc(r.last_sync_at || '-') + '</small></td>'
                + '</tr>';
        }).join('');
    }

    function renderBatches(rows) {
        if (!rows.length) return empty('batches-body', 9);
        document.getElementById('batches-body').innerHTML = rows.map(function(r) {
            var badge = r.status_expired === 'EXPIRED' ? 'danger' : (r.status_expired === 'NEAR_EXPIRED' ? 'warning' : 'success');
            return '<tr>'
                + '<td><code>' + esc(r.kd_barang) + '</code></td>'
                + '<td>' + esc(r.nama_barang || '-') + '</td>'
                + '<td>' + esc(r.gudang_id || '-') + '</td>'
                + '<td>' + esc(r.no_lot || '-') + '</td>'
                + '<td>' + esc(r.expired_date || '-') + '</td>'
                + '<td class="text-right">' + num(r.qty_on_hand) + '</td>'
                + '<td class="text-right">' + num(r.qty_reserved) + '</td>'
                + '<td class="text-right font-weight-bold text-success">' + num(r.available_stock) + '</td>'
                + '<td><span class="badge badge-' + badge + '">' + esc(r.status_expired) + '</span></td>'
                + '</tr>';
        }).join('');
    }

    function renderLedger(rows) {
        if (!rows.length) return empty('ledger-body', 9);
        document.getElementById('ledger-body').innerHTML = rows.map(function(r) {
            return '<tr>'
                + '<td><small>' + esc(r.created_at || '-') + '</small></td>'
                + '<td><span class="badge badge-secondary">' + esc(r.tipe || '-') + '</span></td>'
                + '<td><code>' + esc(r.kd_barang) + '</code></td>'
                + '<td>' + esc(r.nama_barang || '-') + '</td>'
                + '<td>' + esc(r.gudang_id || '-') + '</td>'
                + '<td>' + esc(r.no_lot || '-') + '</td>'
                + '<td>' + esc(r.expired_date || '-') + '</td>'
                + '<td class="text-right">' + num(r.qty, 3) + '</td>'
                + '<td><small>' + esc((r.ref_type || '-') + ' / ' + (r.ref_no || '-')) + '</small></td>'
                + '</tr>';
        }).join('');
    }

    function renderRecon(rows) {
        if (!rows.length) return empty('recon-body', 10);
        document.getElementById('recon-body').innerHTML = rows.map(function(r) {
            return '<tr>'
                + '<td><code>' + esc(r.kd_barang) + '</code></td>'
                + '<td>' + esc(r.nama_barang || '-') + '</td>'
                + '<td>' + esc(r.gudang_id || '-') + '</td>'
                + '<td>' + esc(r.no_lot || '-') + '</td>'
                + '<td>' + esc(r.expired_date || '-') + '</td>'
                + '<td class="text-right">' + num(r.batch_qty_on_hand, 3) + '</td>'
                + '<td class="text-right">' + num(r.ledger_qty_on_hand, 3) + '</td>'
                + '<td class="text-right text-danger font-weight-bold">' + num(r.diff_on_hand, 3) + '</td>'
                + '<td class="text-right">' + num(r.diff_reserved, 3) + '</td>'
                + '<td><small>' + esc(r.last_ledger_at || '-') + '</small></td>'
                + '</tr>';
        }).join('');
    }

    function loadAll() {
        loadActiveTab();
        refreshGudangs();
    }

    function loadActiveTab() {
        var token = ++requestToken;
        if (activeTab === 'items') {
            loading('items-body', 9);
            fetchJson('/items').then(function(rows) {
                if (token === requestToken) renderItems(rows);
            }).catch(function(err) { renderError('items-body', 9, err); });
            return;
        }

        if (activeTab === 'batches') {
            loading('batches-body', 9);
            fetchJson('/batches', {include_zero: 1, limit: 300}).then(function(rows) {
                if (token === requestToken) renderBatches(rows);
            }).catch(function(err) { renderError('batches-body', 9, err); });
            return;
        }

        if (activeTab === 'ledger') {
            loading('ledger-body', 9);
            fetchJson('/ledger', {limit: 300}).then(function(rows) {
                if (token === requestToken) renderLedger(rows);
            }).catch(function(err) { renderError('ledger-body', 9, err); });
            return;
        }

        loading('recon-body', 10);
        fetchJson('/reconciliation', {limit: 300}).then(function(rows) {
            if (token === requestToken) renderRecon(rows);
        }).catch(function(err) { renderError('recon-body', 10, err); });
    }

    function refreshGudangs() {
        fetchJson('/gudangs').then(renderGudangs).catch(function() {
            renderGudangs(gudangRows);
        });
    }

    function scheduleSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            refreshGudangs();
            loadActiveTab();
        }, 350);
    }

    function renderSync(data) {
        document.getElementById('sync-summary').innerHTML =
            '<div class="alert alert-' + (data.dry_run ? 'info' : 'success') + ' py-2">'
            + esc(data.message) + '<br>'
            + 'Ledger group: <b>' + num(data.ledger_groups) + '</b>, insert: <b>' + num(data.to_insert)
            + '</b>, update: <b>' + num(data.to_update) + '</b>, unchanged: <b>' + num(data.unchanged) + '</b>'
            + '</div>';

        document.getElementById('sync-body').innerHTML = (data.rows || []).map(function(r) {
            return '<tr>'
                + '<td>' + esc(r.action) + '</td>'
                + '<td><code>' + esc(r.kd_barang) + '</code></td>'
                + '<td>' + esc(r.gudang_id || '-') + '</td>'
                + '<td>' + esc(r.no_lot || '-') + '</td>'
                + '<td>' + esc(r.expired_date || '-') + '</td>'
                + '<td class="text-right">' + num(r.qty_on_hand, 3) + '</td>'
                + '<td class="text-right">' + num(r.qty_reserved, 3) + '</td>'
                + '</tr>';
        }).join('') || '<tr><td colspan="7" class="text-center text-muted">Tidak ada perubahan.</td></tr>';

        $('#modal-sync').modal('show');
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderGudangs(gudangRows);

        document.getElementById('gudang-cards').addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-gudang-card');
            if (!btn) return;
            document.getElementById('filter-gudang').value = btn.dataset.gudang || '';
            renderGudangs(gudangRows);
            loadActiveTab();
        });

        document.getElementById('btn-clear-gudang').addEventListener('click', function() {
            document.getElementById('filter-gudang').value = '';
            renderGudangs(gudangRows);
            loadActiveTab();
        });

        document.getElementById('btn-load').addEventListener('click', loadAll);
        document.getElementById('filter-search').addEventListener('input', scheduleSearch);
        document.getElementById('filter-kd').addEventListener('input', scheduleSearch);

        Array.prototype.forEach.call(document.querySelectorAll('#stock-tabs a[data-toggle="pill"]'), function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                activeTab = (e.target.getAttribute('href') || '#tab-items').replace('#tab-', '');
                loadActiveTab();
            });
            tab.addEventListener('click', function(e) {
                activeTab = (e.currentTarget.getAttribute('href') || '#tab-items').replace('#tab-', '');
                setTimeout(loadActiveTab, 0);
            });
        });

        document.getElementById('btn-preview-sync').addEventListener('click', function() {
            fetchJson('/sync').then(renderSync);
        });
        document.getElementById('btn-apply-sync').addEventListener('click', function() {
            if (!confirm('Apply sinkronisasi akan mengubah tberp_stock_batch dari ledger. Lanjutkan?')) return;
            fetchJson('/sync', null, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'apply=1'
            }).then(function(data) {
                renderSync(data);
                refreshGudangs();
                loadActiveTab();
            });
        });
        loadActiveTab();
    });
})();
</script>
