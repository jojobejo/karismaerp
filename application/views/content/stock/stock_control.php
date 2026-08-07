<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <style>
        .stock-page .info-box{min-height:104px;border:1px solid #e5e7eb;background:#fff;box-shadow:none}
        .stock-page .info-box.is-active{border-color:#28a745;box-shadow:0 0 0 2px rgba(40,167,69,.12)}
        .stock-page .info-box-icon{width:58px;font-size:20px}
        .stock-page .info-box-text{font-weight:700;color:#1f2937;white-space:normal}
        .stock-page .stock-card-meta{display:block;color:#6b7280;font-size:12px;line-height:1.35}
        .stock-page .stock-toolbar{display:flex;gap:10px;align-items:flex-end;justify-content:space-between;flex-wrap:wrap}
        .stock-page .stock-search{max-width:420px;width:100%}
        .stock-page .stock-table th{white-space:nowrap}
        .stock-page .stock-table td{vertical-align:middle}
        .stock-page .stock-code{font-weight:700;color:#111827}
        .stock-page .pagination-info{font-size:13px;color:#6b7280}
        .stock-page .btn-icon{width:34px;height:31px;display:inline-flex;align-items:center;justify-content:center}
    </style>

    <div class="content-wrapper stock-page">
        <section class="content pt-3">
            <div class="container-fluid">
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary mr-1" title="Home">
                            <i class="fas fa-home"></i>
                        </a>
                        <a href="<?= base_url('stock/buffer') ?>" class="btn btn-warning font-weight-bold" title="Buffer Stock & Restock Warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Buffer Stock Control
                        </a>
                    </div>
                </div>


                <input type="hidden" id="filter-gudang" value="">

                <div class="row" id="gudang-cards">
                    <?php foreach (($gudang_summary ?? []) as $g): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                            <button type="button" class="info-box btn btn-block text-left p-0 btn-gudang-card"
                                    data-gudang="<?= htmlspecialchars((string)$g['gudang_id'], ENT_QUOTES, 'UTF-8') ?>">
                                <span class="info-box-icon bg-primary"><i class="fas fa-warehouse"></i></span>
                                <span class="info-box-content">
                                    <span class="info-box-text"><?= htmlspecialchars($g['nama_gudang'] ?: ('Gudang ' . $g['gudang_id'])) ?></span>
                                    <span class="info-box-number"><?= number_format((float)$g['qty_on_hand'], 0, ',', '.') ?> pcs</span>
                                    <span class="stock-card-meta">
                                        <?= htmlspecialchars($g['tipe_gudang'] ?: '-') ?> |
                                        SKU <?= number_format((int)$g['total_sku'], 0, ',', '.') ?> |
                                        Lot <?= number_format((int)$g['total_batch'], 0, ',', '.') ?> |
                                        Expired <?= number_format((int)$g['expired_batch'], 0, ',', '.') ?>
                                    </span>
                                </span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="stock-toolbar">
                            <div>
                                <h5 class="mb-1 font-weight-bold">List Stock Barang</h5>
                                <div class="text-muted small" id="selected-gudang-label">Semua data gudang</div>
                            </div>
                            <div class="stock-search">
                                <label class="mb-1" for="filter-search">Pencarian Barang</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="filter-search" placeholder="Nama barang atau kode barang">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="btn-reset-search" title="Reset pencarian">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped stock-table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Qty Box</th>
                                        <th class="text-right">Qty Pcs</th>
                                        <th class="text-center">#</th>
                                    </tr>
                                </thead>
                                <tbody id="items-body"></tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap mt-2">
                            <div class="pagination-info" id="pagination-info">Memuat data...</div>
                            <div class="btn-group" role="group" aria-label="Pagination stock">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-prev-page">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm disabled" id="page-label">1</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-next-page">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
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
    var gudangRows = <?= json_encode(array_values($gudang_summary ?? []), JSON_UNESCAPED_UNICODE) ?>;
    var searchTimer = null;
    var requestToken = 0;
    var page = 1;
    var perPage = 15;

    function esc(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function num(v) {
        var n = parseFloat(v || 0);
        return n.toLocaleString('id-ID', {maximumFractionDigits: 0});
    }

    function selectedGudang() {
        return document.getElementById('filter-gudang').value || '';
    }

    function selectedGudangName() {
        var id = selectedGudang();
        if (!id) return 'Semua data gudang';
        var found = gudangRows.find(function(g) { return String(g.gudang_id || '') === String(id); });
        return found ? found.nama_gudang : 'Gudang ' + id;
    }

    function itemUrl(kdBarang) {
        var url = STOCK_URL + '/detail?kd_barang=' + encodeURIComponent(kdBarang || '');
        var gdg = selectedGudang();
        return gdg ? url + '&gudang_id=' + encodeURIComponent(gdg) : url;
    }

    function filters(extra) {
        var q = [];
        var gdg = selectedGudang();
        var search = document.getElementById('filter-search').value;
        if (gdg) q.push('gudang_id=' + encodeURIComponent(gdg));
        if (search) q.push('search=' + encodeURIComponent(search));
        if (extra) {
            Object.keys(extra).forEach(function(k) {
                if (extra[k] !== null && extra[k] !== undefined && extra[k] !== '') {
                    q.push(encodeURIComponent(k) + '=' + encodeURIComponent(extra[k]));
                }
            });
        }
        return q.length ? '?' + q.join('&') : '';
    }

    function fetchJson(path, extra) {
        return fetch(STOCK_URL + path + filters(extra))
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(res) {
                if (res.status !== 'ok') throw new Error(res.message || 'Server error');
                return res.data;
            });
    }

    function totals(rows) {
        return rows.reduce(function(acc, r) {
            acc.total_sku += parseInt(r.total_sku || 0, 10);
            acc.total_batch += parseInt(r.total_batch || 0, 10);
            acc.qty_on_hand += parseFloat(r.qty_on_hand || 0);
            acc.expired_batch += parseInt(r.expired_batch || 0, 10);
            return acc;
        }, {total_sku: 0, total_batch: 0, qty_on_hand: 0, expired_batch: 0});
    }

    function gudangCard(g) {
        var selected = String(g.gudang_id || '') === String(selectedGudang());
        var icon = g.gudang_id === '' ? 'fa-boxes' : (String(g.tipe_gudang || '').toUpperCase() === 'EXPIRED' ? 'fa-calendar-times' : 'fa-warehouse');
        var color = selected ? 'bg-success' : (String(g.tipe_gudang || '').toUpperCase() === 'EXPIRED' ? 'bg-danger' : 'bg-primary');
        return '<div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">'
            + '<button type="button" class="info-box btn btn-block text-left p-0 btn-gudang-card' + (selected ? ' is-active' : '') + '" data-gudang="' + esc(g.gudang_id || '') + '">'
            + '<span class="info-box-icon ' + color + '"><i class="fas ' + icon + '"></i></span>'
            + '<span class="info-box-content">'
            + '<span class="info-box-text">' + esc(g.nama_gudang || 'Semua Data Gudang') + '</span>'
            + '<span class="info-box-number">' + num(g.qty_on_hand) + ' pcs</span>'
            + '<span class="stock-card-meta">' + esc(g.tipe_gudang || 'ALL') + ' | SKU ' + num(g.total_sku) + ' | Lot ' + num(g.total_batch) + ' | Expired ' + num(g.expired_batch) + '</span>'
            + '</span></button></div>';
    }

    function renderGudangs(rows) {
        gudangRows = rows || [];
        var total = totals(gudangRows);
        total.gudang_id = '';
        total.nama_gudang = 'Semua Data Gudang';
        total.tipe_gudang = 'ALL';

        document.getElementById('gudang-cards').innerHTML = [gudangCard(total)]
            .concat(gudangRows.map(gudangCard))
            .join('');
        document.getElementById('selected-gudang-label').textContent = selectedGudangName();
    }

    function setLoading() {
        document.getElementById('items-body').innerHTML = '<tr><td colspan="6" class="text-center py-3"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat...</td></tr>';
    }

    function renderError(err) {
        document.getElementById('items-body').innerHTML = '<tr><td colspan="6" class="text-center text-danger py-3"><i class="fas fa-exclamation-triangle mr-1"></i>' + esc(err.message || err) + '</td></tr>';
        document.getElementById('pagination-info').textContent = 'Gagal memuat data.';
    }

    function renderItems(payload) {
        var rows = payload.rows || [];
        var pg = payload.pagination || {page: 1, total_pages: 1, total_rows: 0, per_page: perPage};
        if (!rows.length) {
            document.getElementById('items-body').innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td></tr>';
        } else {
            document.getElementById('items-body').innerHTML = rows.map(function(r) {
                return '<tr>'
                    + '<td><span class="stock-code">' + esc(r.kd_barang) + '</span></td>'
                    + '<td>' + esc(r.nama_barang || '-') + '</td>'
                    + '<td class="text-right font-weight-bold">' + num(r.qty) + '</td>'
                    + '<td class="text-right">' + num(r.qty_box) + '</td>'
                    + '<td class="text-right">' + num(r.qty_pcs) + '</td>'
                    + '<td class="text-center"><a href="' + esc(itemUrl(r.kd_barang)) + '" class="btn btn-info btn-sm btn-icon" title="Detail barang"><i class="fas fa-eye"></i></a></td>'
                    + '</tr>';
            }).join('');
        }

        page = parseInt(pg.page || 1, 10);
        document.getElementById('page-label').textContent = page + ' / ' + Math.max(1, parseInt(pg.total_pages || 1, 10));
        document.getElementById('pagination-info').textContent = num(pg.total_rows) + ' data, menampilkan ' + num(rows.length) + ' data per halaman.';
        document.getElementById('btn-prev-page').disabled = page <= 1;
        document.getElementById('btn-next-page').disabled = page >= parseInt(pg.total_pages || 1, 10);
    }

    function loadItems() {
        var token = ++requestToken;
        setLoading();
        fetchJson('/items', {page: page, per_page: perPage})
            .then(function(payload) {
                if (token === requestToken) renderItems(payload);
            })
            .catch(renderError);
    }

    function refreshGudangs() {
        fetchJson('/gudangs')
            .then(renderGudangs)
            .catch(function() { renderGudangs(gudangRows); });
    }

    function reloadFirstPage() {
        page = 1;
        refreshGudangs();
        loadItems();
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderGudangs(gudangRows);

        document.getElementById('gudang-cards').addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-gudang-card');
            if (!btn) return;
            document.getElementById('filter-gudang').value = btn.dataset.gudang || '';
            reloadFirstPage();
        });

        document.getElementById('filter-search').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(reloadFirstPage, 350);
        });

        document.getElementById('btn-reset-search').addEventListener('click', function() {
            document.getElementById('filter-search').value = '';
            reloadFirstPage();
        });

        document.getElementById('btn-prev-page').addEventListener('click', function() {
            if (page <= 1) return;
            page -= 1;
            loadItems();
        });

        document.getElementById('btn-next-page').addEventListener('click', function() {
            page += 1;
            loadItems();
        });

        loadItems();
    });
})();
</script>
