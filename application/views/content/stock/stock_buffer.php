<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <style>
        .buffer-page .kpi-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .buffer-page .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .buffer-page .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .buffer-page .kpi-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }
        .buffer-page .kpi-value {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }
        .buffer-page .badge-status {
            display: inline-block;
            white-space: nowrap;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 14px;
            text-transform: none;
            line-height: 1.4;
        }
        .buffer-page .badge-critical { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .buffer-page .badge-under { background: #ffedd5; color: #c2410c; border: 1px solid #fdba74; }
        .buffer-page .badge-warning { background: #fef9c3; color: #a16207; border: 1px solid #fde047; }
        .buffer-page .badge-safe { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .buffer-page .reorder-box {
            display: inline-block;
            font-weight: 800;
            color: #0284c7;
            background: #e0f2fe;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
        }
        .buffer-page .buffer-table th {
            vertical-align: middle;
            font-size: 12px;
            background-color: #1e293b;
            color: #f8fafc;
        }
        .buffer-page .buffer-table td {
            vertical-align: middle;
            font-size: 13px;
        }
        .buffer-page .edit-min-btn {
            cursor: pointer;
            color: #2563eb;
            margin-left: 4px;
        }
        .buffer-page .edit-min-btn:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        .buffer-page .text-under-buffer {
            color: #c2410c;
            font-weight: 700;
        }
    </style>

    <div class="content-wrapper buffer-page">
        <section class="content pt-3">
            <div class="container-fluid">
                <!-- Action Bar & Title -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary mr-2" title="Dashboard Home">
                            <i class="fas fa-home"></i>
                        </a>
                        <a href="<?= base_url('stock') ?>" class="btn btn-outline-dark mr-2" title="Stock Control">
                            <i class="fas fa-boxes"></i> Stock Control
                        </a>
                        <div>
                            <h4 class="m-0 font-weight-bold text-dark">Buffer Stock Control & Restock Warning</h4>
                            <small class="text-muted">Monitoring ambang batas minimum persediaan real-time berbasis ERP Ledger</small>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-info mr-1" id="btn-refresh" title="Refresh Data">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <a href="#" class="btn btn-success" id="btn-export-csv" title="Export Excel / CSV">
                            <i class="fas fa-file-excel"></i> Export CSV
                        </a>
                    </div>
                </div>

                <!-- KPI Summary Cards -->
                <div class="row mb-3" id="kpi-cards">
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-2">
                        <div class="kpi-card p-3 d-flex align-items-center" id="card-critical" title="Klik untuk filter Critical">
                            <div class="kpi-icon bg-danger text-white mr-3">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <div class="kpi-title">Critical / Kosong</div>
                                <div class="kpi-value" id="kpi-critical">0</div>
                                <small class="text-muted">Stok Available = 0</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-2">
                        <div class="kpi-card p-3 d-flex align-items-center" id="card-under" title="Klik untuk filter Under Buffer">
                            <div class="kpi-icon bg-warning text-white mr-3">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div>
                                <div class="kpi-title">Under Buffer</div>
                                <div class="kpi-value" id="kpi-under">0</div>
                                <small class="text-muted">Di bawah batas minimum</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-2">
                        <div class="kpi-card p-3 d-flex align-items-center" id="card-warning" title="Klik untuk filter Warning">
                            <div class="kpi-icon bg-info text-white mr-3">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div>
                                <div class="kpi-title">Warning (Near Limit)</div>
                                <div class="kpi-value" id="kpi-warning">0</div>
                                <small class="text-muted">Stok &le; 120% Target</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 mb-2">
                        <div class="kpi-card p-3 d-flex align-items-center" id="card-reorder" title="Klik untuk lihat rekomendasi reorder">
                            <div class="kpi-icon bg-success text-white mr-3">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div>
                                <div class="kpi-title">Rekomendasi Reorder</div>
                                <div class="kpi-value"><span id="kpi-reorder-box">0</span> <small style="font-size: 14px;">Box</small></div>
                                <small class="text-muted">Total defisit <span id="kpi-defisit-pcs">0</span> pcs</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Filter & Table Card (Live Nested Filter without Submit Button) -->
                <div class="card card-outline card-primary">
                    <div class="card-header bg-light">
                        <div class="row align-items-end">
                            <div class="col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1" for="filter-gudang">Filter Gudang</label>
                                <select class="form-control form-control-sm" id="filter-gudang">
                                    <option value="">-- Semua Gudang --</option>
                                    <?php foreach (($gudang_summary ?? []) as $g): ?>
                                        <option value="<?= htmlspecialchars((string)$g['gudang_id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($g['nama_gudang'] ?: ('Gudang ' . $g['gudang_id'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-2">
                                <label class="small font-weight-bold mb-1" for="filter-status">Status Alert Buffer</label>
                                <select class="form-control form-control-sm" id="filter-status">
                                    <option value="all">-- Semua Status --</option>
                                    <option value="critical">🔴 Critical (Kosong)</option>
                                    <option value="under_buffer" selected>🟠 Under Buffer (Perlu Restock)</option>
                                    <option value="warning">🟡 Warning (Near Limit)</option>
                                    <option value="safe">🟢 Safe (Aman)</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-sm-12 mb-2">
                                <label class="small font-weight-bold mb-1" for="filter-search">Pencarian Barang / Supplier</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="filter-search" placeholder="Cari kode barang, nama barang, atau supplier (otomatis)...">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="btn-reset-filter" title="Reset Filter">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered m-0 buffer-table">
                                <thead>
                                    <tr>
                                        <th style="width: 120px;">Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Supplier</th>
                                        <th class="text-right" style="width: 130px;">Min Buffer</th>
                                        <th class="text-right" style="width: 100px;">Stok Fisik</th>
                                        <th class="text-right" style="width: 90px;">Reserved</th>
                                        <th class="text-right" style="width: 110px;">Available</th>
                                        <th class="text-center" style="width: 110px;">Defisit</th>
                                        <th class="text-center" style="width: 170px;">Rekomendasi Reorder</th>
                                        <th class="text-center" style="width: 140px;">Status</th>
                                        <th class="text-center" style="width: 60px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="buffer-table-body">
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-muted">Memuat data buffer stock...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap">
                        <div class="text-muted small" id="pagination-info">Memuat metadata...</div>
                        <div class="btn-group" role="group" aria-label="Pagination">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-prev-page">
                                <i class="fas fa-chevron-left"></i> Prev
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm disabled" id="page-label">1 / 1</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-next-page">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <!-- Modal Edit Minimum Buffer Stock -->
    <div class="modal fade" id="modal-edit-min" tabindex="-1" role="dialog" aria-labelledby="modalEditMinLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalEditMinLabel"><i class="fas fa-edit mr-2"></i>Edit Target Minimum Buffer</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-edit-min">
                    <div class="modal-body">
                        <input type="hidden" id="edit-kd-barang">
                        <div class="form-group">
                            <label class="font-weight-bold mb-1">Kode Barang:</label>
                            <input type="text" class="form-control" id="edit-display-kd" readonly>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold mb-1">Nama Barang:</label>
                            <input type="text" class="form-control" id="edit-display-nama" readonly>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold mb-1" for="edit-input-min">Target Minimum Buffer Stock (Pcs):</label>
                            <input type="number" min="0" step="1" class="form-control" id="edit-input-min" required>
                            <small class="text-muted">Nilai batas stok minimal yang memicu alert warning & reorder recommendation.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-save-min">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let currentPage = 1;
            let totalPages = 1;
            let searchTimeout = null;

            const filterGudang = document.getElementById('filter-gudang');
            const filterStatus = document.getElementById('filter-status');
            const filterSearch = document.getElementById('filter-search');
            const tableBody = document.getElementById('buffer-table-body');
            const btnExport = document.getElementById('btn-export-csv');

            function numberFormat(val, decimals = 0) {
                const num = parseFloat(val) || 0;
                return num.toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
            }

            function buildExportUrl() {
                const params = new URLSearchParams({
                    gudang_id: filterGudang.value,
                    status_alert: filterStatus.value,
                    search: filterSearch.value.trim()
                });
                return '<?= base_url('stock/buffer/export') ?>?' + params.toString();
            }

            function fetchSummary() {
                const params = new URLSearchParams({
                    gudang_id: filterGudang.value,
                    search: filterSearch.value.trim()
                });

                fetch('<?= base_url('stock/buffer/summary') ?>?' + params.toString())
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'ok') {
                            const d = res.data;
                            document.getElementById('kpi-critical').textContent = numberFormat(d.critical_count);
                            document.getElementById('kpi-under').textContent = numberFormat(d.under_buffer_count);
                            document.getElementById('kpi-warning').textContent = numberFormat(d.warning_count);
                            document.getElementById('kpi-reorder-box').textContent = numberFormat(d.total_reorder_box);
                            document.getElementById('kpi-defisit-pcs').textContent = numberFormat(d.total_defisit_pcs);
                        }
                    })
                    .catch(err => console.error('Gagal mengambil summary buffer:', err));
            }

            function fetchTableData(page = 1) {
                currentPage = page;
                tableBody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat data buffer stock...</td></tr>`;

                const params = new URLSearchParams({
                    gudang_id: filterGudang.value,
                    status_alert: filterStatus.value,
                    search: filterSearch.value.trim(),
                    page: page,
                    per_page: 15
                });

                btnExport.href = buildExportUrl();

                fetch('<?= base_url('stock/buffer/items') ?>?' + params.toString())
                    .then(res => res.json())
                    .then(res => {
                        if (res.status !== 'ok') {
                            throw new Error(res.message || 'Gagal memuat data');
                        }

                        const d = res.data;
                        totalPages = d.total_pages || 1;
                        renderTable(d.rows || []);
                        renderPagination(d);
                    })
                    .catch(err => {
                        tableBody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle mr-1"></i> ${err.message}</td></tr>`;
                    });
            }

            function triggerNestedReload(resetPage = true) {
                fetchSummary();
                fetchTableData(resetPage ? 1 : currentPage);
            }

            function renderTable(rows) {
                if (rows.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-muted">Tidak ditemukan data barang untuk filter ini.</td></tr>`;
                    return;
                }

                let html = '';
                rows.forEach(r => {
                    let badgeClass = 'badge-safe';
                    let statusLabel = '🟢 Safe';
                    let availColorClass = 'text-success font-weight-bold';

                    if (r.status_alert === 'CRITICAL') {
                        badgeClass = 'badge-critical';
                        statusLabel = '🔴 Critical';
                        availColorClass = 'text-danger font-weight-bold';
                    } else if (r.status_alert === 'UNDER_BUFFER') {
                        badgeClass = 'badge-under';
                        statusLabel = '🟠 Under Buffer';
                        availColorClass = 'text-under-buffer';
                    } else if (r.status_alert === 'WARNING') {
                        badgeClass = 'badge-warning';
                        statusLabel = '🟡 Warning';
                        availColorClass = 'text-info font-weight-bold';
                    }

                    const minStr = `${numberFormat(r.stock_minimum)} Pcs <small class="text-muted">(${numberFormat(r.stock_minimum_box)} Box)</small>`;
                    const onHandStr = `${numberFormat(r.qty_on_hand)} Pcs`;
                    const reservedStr = `${numberFormat(r.qty_reserved)} Pcs`;
                    const availableStr = `${numberFormat(r.qty_available)} Pcs`;
                    
                    let defisitStr = '-';
                    if (r.defisit > 0) {
                        defisitStr = `<span class="badge badge-danger" style="font-size: 11px; padding: 4px 8px;">+${numberFormat(r.defisit)} Pcs</span>`;
                    }

                    let reorderBadge = '-';
                    if (r.reorder_box > 0) {
                        reorderBadge = `<span class="reorder-box" title="Rekomendasi Reorder"><i class="fas fa-shopping-cart mr-1"></i>${numberFormat(r.reorder_box)} Box <small>(${numberFormat(r.reorder_total_pcs)} Pcs)</small></span>`;
                    }

                    html += `
                        <tr>
                            <td class="font-weight-bold text-dark">${r.kd_barang}</td>
                            <td>${r.nama_barang}</td>
                            <td><small class="text-muted">${r.nama_suplier}</small></td>
                            <td class="text-right">
                                ${minStr}
                                <i class="fas fa-pencil-alt edit-min-btn" title="Ubah target buffer minimum"
                                   data-kd="${r.kd_barang}" data-nama="${r.nama_barang}" data-min="${r.stock_minimum}"></i>
                            </td>
                            <td class="text-right">${onHandStr}</td>
                            <td class="text-right text-muted">${reservedStr}</td>
                            <td class="text-right ${availColorClass}">${availableStr}</td>
                            <td class="text-center">${defisitStr}</td>
                            <td class="text-center">${reorderBadge}</td>
                            <td class="text-center"><span class="badge-status ${badgeClass}">${statusLabel}</span></td>
                            <td class="text-center">
                                <a href="<?= base_url('stock/detail/') ?>${encodeURIComponent(r.kd_barang)}" class="btn btn-xs btn-outline-primary" title="Detail Kartu Stok">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });

                tableBody.innerHTML = html;

                // Event listener untuk tombol edit minimum
                document.querySelectorAll('.edit-min-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        document.getElementById('edit-kd-barang').value = this.dataset.kd;
                        document.getElementById('edit-display-kd').value = this.dataset.kd;
                        document.getElementById('edit-display-nama').value = this.dataset.nama;
                        document.getElementById('edit-input-min').value = this.dataset.min;
                        $('#modal-edit-min').modal('show');
                    });
                });
            }

            function renderPagination(meta) {
                const info = document.getElementById('pagination-info');
                const pageLabel = document.getElementById('page-label');
                const btnPrev = document.getElementById('btn-prev-page');
                const btnNext = document.getElementById('btn-next-page');

                info.textContent = `Menampilkan halaman ${meta.page} dari ${meta.total_pages} (Total: ${meta.total} barang)`;
                pageLabel.textContent = `${meta.page} / ${meta.total_pages}`;

                btnPrev.disabled = meta.page <= 1;
                btnNext.disabled = meta.page >= meta.total_pages;
            }

            // Live Auto-Reload Event Listeners (Nested Auto Filter)
            filterGudang.addEventListener('change', function () {
                triggerNestedReload(true);
            });

            filterStatus.addEventListener('change', function () {
                triggerNestedReload(true);
            });

            filterSearch.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    triggerNestedReload(true);
                }, 350);
            });

            document.getElementById('btn-reset-filter').addEventListener('click', function () {
                filterGudang.value = '';
                filterStatus.value = 'all';
                filterSearch.value = '';
                triggerNestedReload(true);
            });

            document.getElementById('btn-refresh').addEventListener('click', function () {
                triggerNestedReload(false);
            });

            // Interactive KPI Card Clicks (Nested Filter Shortcuts)
            document.getElementById('card-critical').addEventListener('click', function () {
                filterStatus.value = 'critical';
                triggerNestedReload(true);
            });

            document.getElementById('card-under').addEventListener('click', function () {
                filterStatus.value = 'under_buffer';
                triggerNestedReload(true);
            });

            document.getElementById('card-warning').addEventListener('click', function () {
                filterStatus.value = 'warning';
                triggerNestedReload(true);
            });

            document.getElementById('card-reorder').addEventListener('click', function () {
                filterStatus.value = 'under_buffer';
                triggerNestedReload(true);
            });

            document.getElementById('btn-prev-page').addEventListener('click', function () {
                if (currentPage > 1) fetchTableData(currentPage - 1);
            });

            document.getElementById('btn-next-page').addEventListener('click', function () {
                if (currentPage < totalPages) fetchTableData(currentPage + 1);
            });

            // Submit Form Edit Minimum
            document.getElementById('form-edit-min').addEventListener('submit', function (e) {
                e.preventDefault();
                const kdBarang = document.getElementById('edit-kd-barang').value;
                const stockMin = document.getElementById('edit-input-min').value;
                const btnSave = document.getElementById('btn-save-min');

                btnSave.disabled = true;
                btnSave.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...`;

                const formData = new FormData();
                formData.append('kd_barang', kdBarang);
                formData.append('stock_minimum', stockMin);

                fetch('<?= base_url('stock/buffer/update-minimum') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    btnSave.disabled = false;
                    btnSave.textContent = 'Simpan Perubahan';

                    if (res.status === 'ok') {
                        $('#modal-edit-min').modal('hide');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                        } else {
                            alert(res.message);
                        }
                        triggerNestedReload(false);
                    } else {
                        alert('Gagal: ' + res.message);
                    }
                })
                .catch(err => {
                    btnSave.disabled = false;
                    btnSave.textContent = 'Simpan Perubahan';
                    alert('Terjadi kesalahan jaringan.');
                });
            });

            // Initial load
            fetchSummary();
            fetchTableData(1);
        });
    </script>
