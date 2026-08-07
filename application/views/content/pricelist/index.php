<style>
    .pricelist-card-stat {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        color: #f8fafc;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .pricelist-card-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
    }
    .stat-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .bg-gradient-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: #ffffff;
    }
    .bg-gradient-emerald {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: #ffffff;
    }
    .bg-gradient-amber {
        background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
        color: #ffffff;
    }
    .bg-gradient-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        color: #ffffff;
    }
    .badge-tier {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 11px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .badge-tier-regular {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .badge-tier-grosir {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .badge-tier-distributor {
        background-color: #f3e8ff;
        color: #6b21a8;
        border: 1px solid #e9d5ff;
    }
    .price-tag {
        font-weight: 700;
        font-size: 14px;
        color: #0f172a;
    }
    .price-dpp {
        font-size: 11px;
        color: #64748b;
    }
    .table-pricelist th {
        background-color: #f8fafc;
        color: #334155;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        vertical-align: middle !important;
        border-bottom: 2px solid #e2e8f0;
    }
    .table-pricelist td {
        vertical-align: middle !important;
        font-size: 13px;
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-tags text-primary mr-2"></i>Pricelist Barang
                    </h1>
                    <p class="text-muted mb-0 small">Kalkulasi Moving Average Pembelian dari LPB & Pengelolaan Harga Jual Consumable</p>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-outline-primary btn-rounded shadow-sm mr-2" id="btnRecalculateAll">
                        <i class="fas fa-sync-alt mr-1"></i> Hitung Ulang HPP LPB
                    </button>
                    <a href="<?= base_url('pricelist_online') ?>" class="btn btn-secondary btn-rounded shadow-sm">
                        <i class="fas fa-globe mr-1"></i> Pricelist Legacy
                    </a>
                </div>
            </div>

            <!-- Stats Cards Bar -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 col-12 mb-2">
                    <div class="card pricelist-card-stat p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon-wrapper bg-gradient-blue mr-3">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase font-weight-bold">Integrasi LPB</div>
                                <div class="h5 mb-0 font-weight-bold text-white">Verified Moving Avg</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-2">
                    <div class="card pricelist-card-stat p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon-wrapper bg-gradient-emerald mr-3">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase font-weight-bold">Margin Governance</div>
                                <div class="h5 mb-0 font-weight-bold text-white">Floor Price Controlled</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-2">
                    <div class="card pricelist-card-stat p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon-wrapper bg-gradient-amber mr-3">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase font-weight-bold">Akurasi & Validitas</div>
                                <div class="h5 mb-0 font-weight-bold text-white">100% Tax & DPP Ready</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-2">
                    <div class="card pricelist-card-stat p-3">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon-wrapper bg-gradient-purple mr-3">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase font-weight-bold">Performance</div>
                                <div class="h5 mb-0 font-weight-bold text-white">Indexed Fast Lookup</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Filter & Table Card -->
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="small text-muted font-weight-bold mb-1"><i class="fas fa-users mr-1"></i>Tier Customer</label>
                            <select class="form-control form-control-sm border-secondary-subtle" id="filterTier">
                                <option value="REGULAR" selected>REGULAR (Default Jual)</option>
                                <option value="GROSIR">GROSIR (Bulk Sales)</option>
                                <option value="DISTRIBUTOR">DISTRIBUTOR (Special Tier)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="small text-muted font-weight-bold mb-1"><i class="fas fa-layer-group mr-1"></i>Kelompok Dagang</label>
                            <select class="form-control form-control-sm border-secondary-subtle" id="filterKelompok">
                                <option value="">-- Semua Kelompok --</option>
                                <?php foreach ($kelompok_dagang as $kd): ?>
                                    <option value="<?= htmlspecialchars($kd['kelompok_dagang']) ?>">Kelompok <?= htmlspecialchars($kd['kelompok_dagang']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small text-muted font-weight-bold mb-1"><i class="fas fa-search mr-1"></i>Pencarian Item</label>
                            <input type="text" class="form-control form-control-sm" id="searchKeyword" placeholder="Cari Kode Barang / Nama Barang...">
                        </div>
                        <div class="col-md-2 text-right">
                            <label class="small text-muted font-weight-bold mb-1 d-block">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-primary btn-block" id="btnFilterApply">
                                <i class="fas fa-filter mr-1"></i> Terapkan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-pricelist mb-0" id="tbPricelistMain">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="15%">Kode / Nama Barang</th>
                                    <th width="10%">Satuan</th>
                                    <th width="14%" class="text-right">HPP Avg LPB (DPP)</th>
                                    <th width="10%" class="text-center">Target Margin</th>
                                    <th width="15%" class="text-right">Harga Jual Inc PPN</th>
                                    <th width="13%" class="text-right">Harga Min Jual</th>
                                    <th width="8%" class="text-center">Status</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX Datatables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Margin & Harga Jual -->
<div class="modal fade" id="modalEditMargin" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-gradient-blue text-white border-0 py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Penyesuaian Margin & Harga Jual</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form id="formUpdateMargin">
                    <input type="hidden" id="editIdPricelist" name="id_pricelist">
                    <div class="form-group mb-3">
                        <label class="text-muted small font-weight-bold">Barang</label>
                        <input type="text" class="form-control form-control-plaintext font-weight-bold text-dark h6" id="editNamaBarang" readonly>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="text-muted small font-weight-bold">HPP Average LPB (DPP)</label>
                            <input type="text" class="form-control bg-light font-weight-bold" id="editHppAvg" readonly>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small font-weight-bold">Harga Minimum Jual</label>
                            <input type="text" class="form-control bg-light text-danger font-weight-bold" id="editHargaMin" readonly>
                        </div>
                    </div>

                    <div class="card bg-light border-0 p-3 mb-3">
                        <div class="form-group mb-2">
                            <label class="font-weight-bold text-primary small">Margin Keuntungan (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control font-weight-bold text-primary" id="editMarginPersen" name="margin_persen" required>
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-primary small">Tambahan Margin Nominal (Rp)</label>
                            <input type="number" step="100" class="form-control font-weight-bold" id="editMarginNominal" name="margin_nominal" value="0">
                        </div>
                    </div>

                    <!-- Live Preview Box -->
                    <div class="p-3 border rounded bg-white shadow-sm mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Estimasi Harga Jual (DPP):</span>
                            <span class="font-weight-bold text-dark" id="prevHargaDpp">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">PPN (11%):</span>
                            <span class="font-weight-bold text-muted" id="prevPpn">Rp 0</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold text-dark">Estimasi Harga Jual Inc. PPN:</span>
                            <span class="h5 font-weight-bold text-success mb-0" id="prevHargaIncPpn">Rp 0</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> Simpan Harga</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Histori Perubahan Harga -->
<div class="modal fade" id="modalHistory" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-history mr-2"></i>Histori Perubahan HPP & Pricelist</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center small" id="tbHistory">
                        <thead class="thead-dark">
                            <tr>
                                <th>Waktu Update</th>
                                <th>HPP Avg Lama</th>
                                <th>HPP Avg Baru</th>
                                <th>Harga Inc PPN Lama</th>
                                <th>Harga Inc PPN Baru</th>
                                <th>Keterangan / Alasan</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tbMain = $('#tbPricelistMain').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url("pricelist/data") ?>',
                type: 'POST',
                data: function(d) {
                    d.tier = $('#filterTier').val();
                    d.kelompok_dagang = $('#filterKelompok').val();
                    d.search.value = $('#searchKeyword').val();
                }
            },
            columns: [
                { data: null, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, className: 'text-center' },
                { 
                    data: null, 
                    render: function(d) {
                        return '<div><span class="font-weight-bold text-primary">' + d.kd_barang + '</span><br>' +
                               '<span class="text-dark font-weight-semibold">' + d.nama_barang + '</span></div>';
                    }
                },
                { data: 'satuan', className: 'text-center' },
                { 
                    data: null, 
                    className: 'text-right',
                    render: function(d) {
                        var val = parseFloat(d.hpp_avg_base || 0);
                        return '<span class="font-weight-bold text-dark">Rp ' + val.toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</span>';
                    }
                },
                { 
                    data: null, 
                    className: 'text-center',
                    render: function(d) {
                        return '<span class="badge badge-info font-weight-bold">' + parseFloat(d.margin_persen || 0) + '%</span>';
                    }
                },
                { 
                    data: null, 
                    className: 'text-right',
                    render: function(d) {
                        var val = parseFloat(d.harga_jual_inc_ppn || 0);
                        var dpp = parseFloat(d.harga_jual_dpp || 0);
                        return '<div><span class="price-tag text-success">Rp ' + val.toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</span><br>' +
                               '<span class="price-dpp">Excl: Rp ' + dpp.toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</span></div>';
                    }
                },
                { 
                    data: null, 
                    className: 'text-right',
                    render: function(d) {
                        var val = parseFloat(d.harga_minimum_jual || 0);
                        return '<span class="font-weight-bold text-danger">Rp ' + val.toLocaleString('id-ID', {minimumFractionDigits: 2}) + '</span>';
                    }
                },
                { 
                    data: 'status', 
                    className: 'text-center',
                    render: function(s) {
                        return '<span class="badge badge-success px-2 py-1">ACTIVE</span>';
                    }
                },
                { 
                    data: null, 
                    className: 'text-center',
                    render: function(d) {
                        return '<button class="btn btn-xs btn-outline-primary btn-edit-margin mr-1" data-row=\'' + JSON.stringify(d) + '\' title="Edit Margin"><i class="fas fa-edit"></i></button>' +
                               '<button class="btn btn-xs btn-outline-secondary btn-view-history" data-kd="' + d.kd_barang + '" title="Histori Perubahan"><i class="fas fa-history"></i></button>';
                    }
                }
            ],
            dom: 'rtip',
            pageLength: 25
        });

        $('#btnFilterApply').on('click', function() {
            tbMain.ajax.reload();
        });

        $('#searchKeyword').on('keyup', function(e) {
            if (e.key === 'Enter') {
                tbMain.ajax.reload();
            }
        });

        $('#filterTier, #filterKelompok').on('change', function() {
            tbMain.ajax.reload();
        });

        // Trigger Recalculate All
        $('#btnRecalculateAll').on('click', function() {
            if (!confirm('Apakah Anda yakin ingin menghitung ulang seluruh HPP Average dari LPB?')) return;
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
            
            $.post('<?= base_url("pricelist/recalculate") ?>', function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Hitung Ulang HPP LPB');
                alert('Proses rekalkulasi selesai. Total barang diproses: ' + (res.processed_count || 0));
                tbMain.ajax.reload();
            }, 'json').fail(function() {
                $btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Hitung Ulang HPP LPB');
                alert('Gagal merekalkulasi HPP.');
            });
        });

        // Open Modal Edit Margin
        var currentHppAvg = 0;
        $(document).on('click', '.btn-edit-margin', function() {
            var row = $(this).data('row');
            $('#editIdPricelist').val(row.id_pricelist);
            $('#editNamaBarang').val(row.kd_barang + ' - ' + row.nama_barang);
            
            currentHppAvg = parseFloat(row.hpp_avg_base || 0);
            var minPrice = parseFloat(row.harga_minimum_jual || 0);

            $('#editHppAvg').val('Rp ' + currentHppAvg.toLocaleString('id-ID', {minimumFractionDigits: 2}));
            $('#editHargaMin').val('Rp ' + minPrice.toLocaleString('id-ID', {minimumFractionDigits: 2}));
            $('#editMarginPersen').val(parseFloat(row.margin_persen || 0));
            $('#editMarginNominal').val(parseFloat(row.margin_nominal || 0));

            calcPreviewPrice();
            $('#modalEditMargin').modal('show');
        });

        function calcPreviewPrice() {
            var pct = parseFloat($('#editMarginPersen').val() || 0);
            var nom = parseFloat($('#editMarginNominal').val() || 0);

            var dpp = currentHppAvg * (1 + (pct / 100)) + nom;
            var ppn = dpp * 0.11;
            var total = dpp + ppn;

            $('#prevHargaDpp').text('Rp ' + dpp.toLocaleString('id-ID', {minimumFractionDigits: 2}));
            $('#prevPpn').text('Rp ' + ppn.toLocaleString('id-ID', {minimumFractionDigits: 2}));
            $('#prevHargaIncPpn').text('Rp ' + total.toLocaleString('id-ID', {minimumFractionDigits: 2}));
        }

        $('#editMarginPersen, #editMarginNominal').on('input change', calcPreviewPrice);

        // Form Submit Edit Margin
        $('#formUpdateMargin').on('submit', function(e) {
            e.preventDefault();
            $.post('<?= base_url("pricelist/update_margin") ?>', $(this).serialize(), function(res) {
                if (res.success) {
                    $('#modalEditMargin').modal('hide');
                    tbMain.ajax.reload(null, false);
                } else {
                    alert(res.message || 'Gagal menyimpan harga.');
                }
            }, 'json');
        });

        // View History
        $(document).on('click', '.btn-view-history', function() {
            var kdBg = $(this).data('kd');
            var $tbody = $('#tbHistory tbody');
            $tbody.html('<tr><td colspan="7" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>');
            $('#modalHistory').modal('show');

            $.get('<?= base_url("pricelist/history") ?>', { kd_barang: kdBg }, function(res) {
                if (res.success && res.data.length > 0) {
                    var html = '';
                    res.data.forEach(function(h) {
                        html += '<tr>' +
                            '<td>' + h.changed_at + '</td>' +
                            '<td class="text-right">Rp ' + parseFloat(h.hpp_avg_lama).toLocaleString('id-ID') + '</td>' +
                            '<td class="text-right font-weight-bold">Rp ' + parseFloat(h.hpp_avg_baru).toLocaleString('id-ID') + '</td>' +
                            '<td class="text-right">Rp ' + parseFloat(h.harga_jual_lama).toLocaleString('id-ID') + '</td>' +
                            '<td class="text-right font-weight-bold text-success">Rp ' + parseFloat(h.harga_jual_baru).toLocaleString('id-ID') + '</td>' +
                            '<td>' + (h.alasan_perubahan || '-') + '</td>' +
                            '<td>' + (h.changed_by || 'SYSTEM') + '</td>' +
                        '</tr>';
                    });
                    $tbody.html(html);
                } else {
                    $tbody.html('<tr><td colspan="7" class="text-center text-muted py-3">Belum ada histori perubahan.</td></tr>');
                }
            }, 'json');
        });
    });
</script>
