<?php
// Tampilan Laporan Kartu Stok Per Gudang dengan Modals
?>
<style>
    .report-page { font-family: 'Segoe UI', Arial, sans-serif; }
    .report-page .content-header { padding: 6px .5rem 0; }
    
    /* Toolbar / Filter area - Hidden during print */
    .report-toolbar {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,.05);
        margin-bottom: 22px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: flex-end;
    }
    .report-toolbar .form-group { margin-bottom: 0; min-width: 180px; }
    .report-toolbar .form-group.fg-large { min-width: 250px; }
    .report-toolbar label { font-size: 13px; font-weight: 600; color: #4b5563; margin-bottom: 6px; display: block; }
    .report-toolbar .btn { font-weight: 600; }
    
    /* Report Container */
    .report-container {
        background: #fff;
        padding: 35px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,.06);
        min-height: 600px;
        color: #111827;
    }
    
    /* Report Header */
    .report-header { margin-bottom: 30px; text-align: center; }
    .report-company { font-size: 20px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 2px; }
    .report-title { font-size: 24px; font-weight: 700; color: #1e3a8a; margin-bottom: 4px; }
    .report-subtitle { font-size: 14px; color: #4b5563; margin-bottom: 6px; font-weight: 500; }
    .report-warehouse { font-size: 16px; font-weight: 700; color: #b91c1c; margin-top: 5px; }
    
    /* Product Section Table */
    .product-section { margin-bottom: 40px; }
    .product-header-row {
        background: #f3f4f6;
        padding: 8px 12px;
        border-bottom: 2px solid #1e3a8a;
        display: flex;
        justify-content: space-between;
        font-weight: 700;
        font-size: 14px;
        color: #1e3a8a;
    }
    
    /* Report Table */
    .report-table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 5px; }
    .report-table th {
        background: #e5e7eb;
        padding: 8px 6px;
        font-weight: 700;
        text-align: left;
        border-bottom: 1.5px solid #9ca3af;
        border-top: 1px solid #d1d5db;
        color: #374151;
    }
    .report-table td { padding: 6px 6px; vertical-align: middle; border-bottom: 1px solid #f3f4f6; }
    
    /* Subtotal Row */
    .subtotal-row {
        font-weight: 700;
        background: #f9fafb;
        border-top: 1.5px solid #9ca3af;
        border-bottom: 1.5px solid #9ca3af;
    }
    .subtotal-row td { padding: 8px 6px; }
    
    /* Grand Total Box */
    .grand-total-box {
        margin-top: 30px;
        border-top: 2px solid #1e3a8a;
        padding-top: 15px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 20px;
        font-size: 15px;
        font-weight: 700;
    }
    .grand-total-val { font-size: 18px; color: #1e3a8a; }
    
    .text-right { text-align: right !important; }
    .text-center { text-align: center !important; }
    .text-semibold { font-weight: 600; }
    .text-muted-custom { color: #6b7280; }
    
    /* Nested table layout specifics */
    .col-header-group {
        border-bottom: 1px solid #d1d5db !important;
        text-align: center !important;
    }
    
    /* Modal Custom Styling */
    .modal-erp-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .modal-erp-table th { background: #008cba; color: white; padding: 10px; font-weight: 600; border: 1px solid #007ba4; text-align: left; }
    .modal-erp-table td { padding: 8px 10px; border: 1px solid #e2e8f0; cursor: pointer; }
    .modal-erp-table tr:hover { background-color: #f1f5f9; }
    .modal-erp-table tr.selected-row { background-color: #bae6fd !important; }
    
    .search-box-container { position: relative; margin-bottom: 15px; }
    .search-box-container input { width: 100%; padding: 8px 12px; font-size: 13px; border: 1px solid #cbd5e1; border-radius: 4px; }
    
    .modal-erp-footer { display: flex; justify-content: space-between; padding-top: 15px; border-top: 1px solid #e2e8f0; }
    .modal-erp-footer .left-btns { display: flex; gap: 8px; }
    .modal-erp-footer .right-btns { display: flex; gap: 8px; }
    
    /* Print Styles */
    @media print {
        body { background: #fff !important; }
        .wrapper { background: #fff !important; }
        .main-header, .main-sidebar, .main-footer, .report-toolbar, .content-header { display: none !important; }
        .content-wrapper { margin-left: 0 !important; padding: 0 !important; background: #fff !important; }
        .report-container { box-shadow: none !important; padding: 0 !important; }
        .report-table th { background: #e5e7eb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .product-header-row { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper report-page">
        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= base_url('laporan/barang') ?>" class="btn btn-sm btn-outline-secondary" title="Kembali"><i class="fas fa-arrow-left"></i> Kembali</a>
                            <h4 class="m-0 ml-2 font-weight-bold text-dark">Kartu Stok Per Gudang</h4>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    
                    <!-- Toolbar Filter -->
                    <div class="report-toolbar">
                        <div class="form-group fg-large">
                            <label>Produk</label>
                            <div class="input-group input-group-sm browse-group" style="cursor: pointer;" id="btnBrowseProduct">
                                <input type="text" id="filterProductText" class="form-control" placeholder="-- Semua Produk --" readonly>
                                <input type="hidden" id="filterProduct" value="all">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Kelompok Barang</label>
                            <div class="input-group input-group-sm browse-group" style="cursor: pointer;" id="btnBrowseGroup">
                                <input type="text" id="filterGroupText" class="form-control" placeholder="-- Semua Kelompok --" readonly>
                                <input type="hidden" id="filterGroup" value="all">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Gudang</label>
                            <div class="input-group input-group-sm browse-group" style="cursor: pointer;" id="btnBrowseWarehouse">
                                <input type="text" id="filterWarehouseText" class="form-control" placeholder="-- Semua Gudang --" readonly>
                                <input type="hidden" id="filterWarehouse" value="all">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" id="filterStartDate" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" id="filterEndDate" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-sm btn-primary px-3" id="btnFilter"><i class="fas fa-filter"></i> Terapkan Filter</button>
                            <button type="button" class="btn btn-sm btn-secondary px-3" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                        </div>
                    </div>

                    <!-- Report Content -->
                    <div class="report-container">
                        <div class="report-header">
                            <div class="report-company">KARISMA <?= date('Y') ?></div>
                            <div class="report-title">Kartu Stok Per Gudang</div>
                            <div class="report-subtitle">Periode: <span id="lblPeriode">-</span></div>
                            <div class="report-warehouse" id="lblWarehouse">-- Semua Gudang --</div>
                        </div>

                        <!-- Report Card List -->
                        <div id="reportContentArea">
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-3 text-secondary"></i>
                                <p>Silakan sesuaikan filter di atas dan klik 'Terapkan Filter' untuk memuat Laporan Kartu Stok.</p>
                            </div>
                        </div>

                        <!-- Global Grand Total -->
                        <div class="grand-total-box d-none" id="grandTotalBox">
                            <div>Total Nilai Akhir Saldo (Seluruh Produk):</div>
                            <div class="grand-total-val" id="lblGrandTotal">Rp 0.00</div>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </div>

    <!-- MODAL PRODUK -->
    <div class="modal fade" id="modalProduct" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-dark">Data Persediaan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="search-box-container">
                        <input type="text" id="searchProduct" placeholder="Cari Kode atau Deskripsi Produk...">
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="modal-erp-table" id="tableProduct">
                            <thead>
                                <tr>
                                    <th width="30%">Kode</th>
                                    <th width="70%">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-code="all" data-name="-- Semua Produk --">
                                    <td class="text-semibold text-primary">ALL</td>
                                    <td>-- Semua Produk --</td>
                                </tr>
                                <?php foreach ($products as $p): ?>
                                    <tr data-code="<?= htmlspecialchars($p['kode_barang']) ?>" data-name="<?= htmlspecialchars($p['kode_barang'] . ' | ' . $p['nama_barang']) ?>">
                                        <td><?= htmlspecialchars($p['kode_barang']) ?></td>
                                        <td><?= htmlspecialchars($p['nama_barang']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer modal-erp-footer">
                    <div class="left-btns">
                        <button type="button" class="btn btn-sm btn-danger disabled">Hapus</button>
                        <button type="button" class="btn btn-sm btn-primary disabled">Baru</button>
                        <button type="button" class="btn btn-sm btn-info disabled">Edit</button>
                        <button type="button" class="btn btn-sm btn-warning disabled">Update</button>
                    </div>
                    <div class="right-btns">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-sm btn-primary px-3" id="btnConfirmProduct">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL KELOMPOK BARANG -->
    <div class="modal fade" id="modalGroup" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-dark">Kelompok Barang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="search-box-container">
                        <input type="text" id="searchGroup" placeholder="Cari Kelompok Barang...">
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="modal-erp-table" id="tableGroup">
                            <thead>
                                <tr>
                                    <th width="100%">Kelompok Barang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-code="all" data-name="-- Semua Kelompok --">
                                    <td>-- Semua Kelompok --</td>
                                </tr>
                                <?php foreach ($groups as $g): ?>
                                    <tr data-code="<?= htmlspecialchars($g['id']) ?>" data-name="<?= htmlspecialchars($g['kelompok']) ?>">
                                        <td><?= htmlspecialchars($g['kelompok']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer modal-erp-footer">
                    <div class="left-btns">
                        <button type="button" class="btn btn-sm btn-info disabled">Alias</button>
                        <button type="button" class="btn btn-sm btn-danger disabled">Hapus</button>
                        <button type="button" class="btn btn-sm btn-primary disabled">Baru</button>
                        <button type="button" class="btn btn-sm btn-info disabled">Edit</button>
                        <button type="button" class="btn btn-sm btn-warning disabled">Update</button>
                    </div>
                    <div class="right-btns">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-sm btn-primary px-3" id="btnConfirmGroup">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL LOKASI/GUDANG -->
    <div class="modal fade" id="modalWarehouse" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-dark">Lokasi/Gudang</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="search-box-container">
                        <input type="text" id="searchWarehouse" placeholder="Cari Gudang...">
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <table class="modal-erp-table" id="tableWarehouse">
                            <thead>
                                <tr>
                                    <th width="30%">Kode</th>
                                    <th width="70%">Nama Gudang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-code="all" data-name="-- Semua Gudang --">
                                    <td class="text-semibold text-primary">ALL</td>
                                    <td>-- Semua Gudang --</td>
                                </tr>
                                <?php foreach ($warehouses as $w): ?>
                                    <tr data-code="<?= $w['id_gudang'] ?>" data-name="<?= htmlspecialchars($w['nama_gudang']) ?>">
                                        <td>Gdg. <?= htmlspecialchars(substr($w['nama_gudang'], 0, 5)) ?></td>
                                        <td><?= htmlspecialchars($w['nama_gudang']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer modal-erp-footer">
                    <div class="left-btns">
                        <button type="button" class="btn btn-sm btn-info disabled">Alias</button>
                        <button type="button" class="btn btn-sm btn-danger disabled">Hapus</button>
                        <button type="button" class="btn btn-sm btn-primary disabled">Baru</button>
                        <button type="button" class="btn btn-sm btn-info disabled">Edit</button>
                        <button type="button" class="btn btn-sm btn-warning disabled">Update</button>
                    </div>
                    <div class="right-btns">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-sm btn-primary px-3" id="btnConfirmWarehouse">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<?php $this->load->view('partial/main/footergdg.php') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var endpoint = '<?= base_url('laporan/barang/kartu-stock-gudang-data') ?>';

    function formatNumber(num) {
        var p = parseFloat(num);
        if (isNaN(p)) return '0.00';
        return p.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(str) {
        if (!str) return '-';
        var parts = str.split('-');
        if (parts.length !== 3) return str;
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }
    
    function formatDateFull(str) {
        if (!str) return '-';
        var d = new Date(str);
        if (isNaN(d)) return str;
        var months = ['July','August','September','October','November','December','January','February','March','April','May','June'];
        // Format to Indonesian standard or English depending on design
        var monthsEN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        return d.getDate() + ' ' + monthsEN[d.getMonth()] + ' ' + d.getFullYear();
    }

    // Modal Opening Triggers
    $('#btnBrowseProduct').on('click', function() {
        $('#modalProduct').modal('show');
    });
    $('#btnBrowseGroup').on('click', function() {
        $('#modalGroup').modal('show');
    });
    $('#btnBrowseWarehouse').on('click', function() {
        $('#modalWarehouse').modal('show');
    });

    // Row selection toggle inside modals
    function bindModalRowSelection(tableId, textInputId, hiddenInputId, modalId, confirmBtnId) {
        var selectedData = null;

        $(tableId + ' tbody tr').on('click', function() {
            $(tableId + ' tbody tr').removeClass('selected-row');
            $(this).addClass('selected-row');
            selectedData = {
                code: $(this).data('code'),
                name: $(this).data('name')
            };
        });

        // Double click to automatically confirm
        $(tableId + ' tbody tr').on('dblclick', function() {
            selectedData = {
                code: $(this).data('code'),
                name: $(this).data('name')
            };
            confirmSelection();
        });

        $(confirmBtnId).on('click', function() {
            confirmSelection();
        });

        function confirmSelection() {
            if (selectedData) {
                $(hiddenInputId).val(selectedData.code);
                $(textInputId).val(selectedData.name);
                $(modalId).modal('hide');
            } else {
                alert('Silakan pilih salah satu baris terlebih dahulu!');
            }
        }
    }

    bindModalRowSelection('#tableProduct', '#filterProductText', '#filterProduct', '#modalProduct', '#btnConfirmProduct');
    bindModalRowSelection('#tableGroup', '#filterGroupText', '#filterGroup', '#modalGroup', '#btnConfirmGroup');
    bindModalRowSelection('#tableWarehouse', '#filterWarehouseText', '#filterWarehouse', '#modalWarehouse', '#btnConfirmWarehouse');

    // Search filtering within tables
    function bindSearchFilter(inputId, tableId) {
        $(inputId).on('keyup', function() {
            var val = $(this).val().toLowerCase();
            $(tableId + ' tbody tr').each(function() {
                var text = $(this).text().toLowerCase();
                $(this).toggle(text.indexOf(val) > -1);
            });
        });
    }

    bindSearchFilter('#searchProduct', '#tableProduct');
    bindSearchFilter('#searchGroup', '#tableGroup');
    bindSearchFilter('#searchWarehouse', '#tableWarehouse');

    // Fetch and Render Report Data
    $('#btnFilter').on('click', function() {
        var start = $('#filterStartDate').val();
        var end = $('#filterEndDate').val();
        var prod = $('#filterProduct').val();
        var grp = $('#filterGroup').val();
        var wh = $('#filterWarehouse').val();

        if (!start || !end) {
            alert('Tanggal mulai dan selesai harus diisi!');
            return;
        }

        // Update display labels
        $('#lblPeriode').text(formatDateFull(start) + ' - ' + formatDateFull(end));
        var whName = $('#filterWarehouseText').val();
        $('#lblWarehouse').text(wh === 'all' ? '-- Semua Gudang --' : whName);

        $('#reportContentArea').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i><p>Sedang memuat data...</p></div>');
        $('#grandTotalBox').addClass('d-none');

        $.ajax({
            url: endpoint,
            type: 'POST',
            data: {
                start_date: start,
                end_date: end,
                kd_barang: prod,
                kelompok_barang: grp,
                id_gudang: wh
            },
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data && res.data.length > 0) {
                    var html = '';
                    var grandTotalNilai = 0;

                    res.data.forEach(function(p) {
                        grandTotalNilai += parseFloat(p.final_saldo_nilai);
                        
                        html += '<div class="product-section">';
                        html += '  <div class="product-header-row">';
                        html += '    <span>' + p.product_code + ' &nbsp;|&nbsp; ' + p.product_name + '</span>';
                        html += '    <span class="text-danger">' + (p.kelompok_barang || 'Tanpa Kelompok') + '</span>';
                        html += '  </div>';
                        html += '  <table class="report-table">';
                        html += '    <thead>';
                        html += '      <tr>';
                        html += '        <th rowspan="2" width="10%">Tanggal</th>';
                        html += '        <th rowspan="2" width="12%">Nomor Referensi</th>';
                        html += '        <th rowspan="2" width="6%">Unit</th>';
                        html += '        <th colspan="3" class="col-header-group">Masuk</th>';
                        html += '        <th colspan="3" class="col-header-group">Keluar</th>';
                        html += '        <th colspan="3" class="col-header-group">Saldo</th>';
                        html += '      </tr>';
                        html += '      <tr>';
                        html += '        <th class="text-right" width="8%">Qty</th>';
                        html += '        <th class="text-right" width="10%">Harga Pokok</th>';
                        html += '        <th class="text-right" width="11%">Nilai</th>';
                        html += '        <th class="text-right" width="8%">Qty</th>';
                        html += '        <th class="text-right" width="10%">Harga Pokok</th>';
                        html += '        <th class="text-right" width="11%">Nilai</th>';
                        html += '        <th class="text-right" width="8%">Qty</th>';
                        html += '        <th class="text-right" width="10%">Harga Pokok</th>';
                        html += '        <th class="text-right" width="11%">Nilai</th>';
                        html += '      </tr>';
                        html += '    </thead>';
                        html += '    <tbody>';

                        // Render Opening Balance
                        var ob = p.opening_balance;
                        html += '      <tr class="text-semibold text-muted-custom">';
                        html += '        <td>' + formatDate(start) + '</td>';
                        html += '        <td>' + ob.referensi + '</td>';
                        html += '        <td>' + ob.unit + '</td>';
                        html += '        <td colspan="3"></td>';
                        html += '        <td colspan="3"></td>';
                        html += '        <td class="text-right">' + formatNumber(ob.saldo_qty) + '</td>';
                        html += '        <td class="text-right">' + formatNumber(ob.saldo_harga) + '</td>';
                        html += '        <td class="text-right">' + formatNumber(ob.saldo_nilai) + '</td>';
                        html += '      </tr>';

                        // Render Transaction rows
                        p.rows.forEach(function(r) {
                            html += '      <tr>';
                            html += '        <td>' + formatDate(r.tanggal) + '</td>';
                            html += '        <td>' + r.referensi + '</td>';
                            html += '        <td>' + r.unit + '</td>';
                            
                            html += '        <td class="text-right">' + (r.masuk_qty > 0 ? formatNumber(r.masuk_qty) : '') + '</td>';
                            html += '        <td class="text-right">' + (r.masuk_qty > 0 ? formatNumber(r.masuk_harga) : '') + '</td>';
                            html += '        <td class="text-right">' + (r.masuk_qty > 0 ? formatNumber(r.masuk_nilai) : '') + '</td>';
                            
                            html += '        <td class="text-right">' + (r.keluar_qty > 0 ? formatNumber(r.keluar_qty) : '') + '</td>';
                            html += '        <td class="text-right">' + (r.keluar_qty > 0 ? formatNumber(r.keluar_harga) : '') + '</td>';
                            html += '        <td class="text-right">' + (r.keluar_qty > 0 ? formatNumber(r.keluar_nilai) : '') + '</td>';
                            
                            html += '        <td class="text-right text-semibold">' + formatNumber(r.saldo_qty) + '</td>';
                            html += '        <td class="text-right text-semibold">' + formatNumber(r.saldo_harga) + '</td>';
                            html += '        <td class="text-right text-semibold">' + formatNumber(r.saldo_nilai) + '</td>';
                            html += '      </tr>';
                        });

                        // Render Subtotal row
                        html += '      <tr class="subtotal-row">';
                        var shortName = p.product_name.length > 25 ? p.product_name.substr(0, 22) + '...' : p.product_name;
                        html += '        <td colspan="3" class="text-right">' + shortName + ' :</td>';
                        
                        html += '        <td class="text-right">' + formatNumber(p.sub_masuk_qty) + '</td>';
                        html += '        <td class="text-right">' + formatNumber(p.sub_masuk_harga) + '</td>';
                        html += '        <td class="text-right">' + formatNumber(p.sub_masuk_nilai) + '</td>';
                        
                        html += '        <td class="text-right">' + formatNumber(p.sub_keluar_qty) + '</td>';
                        html += '        <td class="text-right">' + formatNumber(p.sub_keluar_harga) + '</td>';
                        html += '        <td class="text-right">' + formatNumber(p.sub_keluar_nilai) + '</td>';
                        
                        html += '        <td class="text-right">' + formatNumber(p.final_saldo_qty) + '</td>';
                        html += '        <td class="text-right">' + formatNumber(p.final_saldo_harga) + '</td>';
                        html += '        <td class="text-right">' + formatNumber(p.final_saldo_nilai) + '</td>';
                        html += '      </tr>';
                        
                        html += '    </tbody>';
                        html += '  </table>';
                        html += '</div>';
                    });

                    $('#reportContentArea').html(html);
                    $('#lblGrandTotal').text('Rp ' + formatNumber(grandTotalNilai));
                    $('#grandTotalBox').removeClass('d-none');
                } else {
                    $('#reportContentArea').html('<div class="text-center py-5 text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-3 text-warning"></i><p>Tidak ada data kartu stok yang ditemukan untuk periode dan kriteria filter ini.</p></div>');
                }
            },
            error: function(err) {
                $('#reportContentArea').html('<div class="text-center py-5 text-danger"><i class="fas fa-times-circle fa-2x mb-3"></i><p>Gagal memuat data laporan dari server.</p></div>');
            }
        });
    });
});
</script>
