<?php
// Tampilan Laporan Jurnal Penjualan
?>
<style>
    .report-page { font-family: 'Segoe UI', Arial, sans-serif; }
    .report-page .content-header { padding: 6px .5rem 0; }
    
    /* Toolbar / Filter area - Hidden during print */
    .report-toolbar {
        background: #fff;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }
    .report-toolbar .form-group { margin-bottom: 0; }
    .report-toolbar label { font-size: 13px; font-weight: 600; color: #4b5563; margin-bottom: 4px; }
    .report-toolbar .btn { font-weight: 600; }
    
    /* Report Container */
    .report-container {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,.08);
        min-height: 600px;
        color: #111827;
    }
    
    /* Report Header */
    .report-header { margin-bottom: 24px; }
    .report-company { font-size: 18px; font-weight: 700; text-transform: uppercase; text-align: center; color: #111827; margin-bottom: 2px; }
    .report-title { font-size: 20px; font-weight: 700; text-align: center; color: #0284c7; margin-bottom: 12px; }
    .report-subtitle { font-size: 13px; font-weight: 600; text-align: center; color: #991b1b; margin-bottom: 16px; }
    .report-meta { font-size: 12px; color: #374151; display: flex; justify-content: space-between; margin-bottom: 6px; }
    
    /* Report Table */
    .report-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .report-table th {
        background: #e5e7eb;
        padding: 10px 8px;
        font-weight: 700;
        text-align: left;
        border-top: 2px solid #9ca3af;
        border-bottom: 2px solid #9ca3af;
    }
    .report-table td { padding: 6px 8px; vertical-align: top; }
    
    /* Transaction Header Row */
    .trx-header { font-weight: 600; border-bottom: 1px solid #d1d5db; }
    
    /* Transaction Detail Row */
    .trx-detail td { padding-top: 4px; padding-bottom: 4px; }
    .trx-detail .acc-name { padding-left: 20px; }
    
    .text-right { text-align: right !important; }
    .text-center { text-align: center !important; }
    
    /* Print Styles */
    @media print {
        body { background: #fff !important; }
        .wrapper { background: #fff !important; }
        .main-header, .main-sidebar, .main-footer, .report-toolbar, .content-header { display: none !important; }
        .content-wrapper { margin-left: 0 !important; padding: 0 !important; background: #fff !important; }
        .report-container { box-shadow: none !important; padding: 0 !important; }
        .report-table th { background: #f3f4f6 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper report-page">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <a href="<?= base_url('laporan/penjualan') ?>" class="btn btn-sm btn-outline-secondary" title="Kembali"><i class="fas fa-arrow-left"></i> Kembali</a>
                        <h4 class="m-0 ml-2 font-weight-bold text-dark"><?= htmlspecialchars($page_heading ?? 'Jurnal - Penjualan') ?></h4>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    
                    <!-- Toolbar Filter -->
                    <div class="report-toolbar">
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" id="filterStartDate" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" id="filterEndDate" class="form-control form-control-sm" value="<?= date('Y-m-t') ?>">
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnOpenFilterModal"><i class="fas fa-sliders-h"></i> Filter Data</button>
                            <button type="button" class="btn btn-sm btn-primary" id="btnFilter"><i class="fas fa-filter"></i> Terapkan Filter</button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                        </div>
                    </div>

                    <!-- Report Content -->
                    <div class="report-container">
                        <div class="report-header">
                            <div class="report-meta">
                                <div><span id="printTime"></span></div>
                                <div></div>
                            </div>
                            <div class="report-company">KARISMA <?= date('Y') ?></div>
                            <div class="report-title"><?= htmlspecialchars($report_title ?? 'Jurnal Penjualan') ?></div>
                            <div class="report-subtitle"><span id="lblPeriode">-</span></div>
                        </div>

                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th width="12%">Tanggal</th>
                                    <th width="38%">Keterangan</th>
                                    <th width="10%">No. Dept.</th>
                                    <th width="15%" class="text-right">Debet</th>
                                    <th width="15%" class="text-right">Kredit</th>
                                    <th width="10%">No. Proyek</th>
                                </tr>
                            </thead>
                            <tbody id="reportTbody">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Silakan klik 'Terapkan Filter' untuk memuat laporan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </section>
        </div>
    </div>

    <div class="modal fade" id="modalFilterData" tabindex="-1" role="dialog" aria-labelledby="modalFilterDataLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="background:#f3f4f6; border:1px solid #d1d5db;">
                <div class="modal-body p-4">
                    <h5 class="mb-3" id="modalFilterDataLabel" style="font-weight:400; color:#1f2937;">Filter Data</h5>

                    <ul class="nav nav-pills mb-4" id="filterDataTabs" role="tablist" style="gap:4px;">
                        <li class="nav-item">
                            <a class="nav-link active py-1 px-3" id="tab-standar-link" data-toggle="pill" href="#tabFilterStandar" role="tab" style="border-radius:2px; font-size:13px;">Standar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-3" id="tab-pencarian-link" data-toggle="pill" href="#tabFilterPencarian" role="tab" style="border-radius:2px; font-size:13px;">Pencarian</a>
                        </li>
                    </ul>

                    <div class="tab-content" style="min-height:110px;">
                        <div class="tab-pane fade show active" id="tabFilterStandar" role="tabpanel">
                            <div class="form-row align-items-end">
                                <div class="col-auto pt-2 pr-3" style="font-size:13px; color:#374151;">
                                    Tanggal :
                                </div>
                                <div class="col-auto">
                                    <label for="modalStartDate" class="mb-1 d-block" style="font-size:12px; color:#374151; font-weight:400;">Dari</label>
                                    <input type="date" id="modalStartDate" required class="form-control form-control-sm" value="<?= date('Y-m-01') ?>" style="background:#e5e7eb; border:1px solid #9ca3af; max-width:160px;">
                                </div>
                                <div class="col-auto pb-1 px-2 font-weight-bold" style="color:#111827;">&gt;</div>
                                <div class="col-auto">
                                    <label for="modalEndDate" class="mb-1 d-block" style="font-size:12px; color:#374151; font-weight:400;">Hingga</label>
                                    <input type="date" id="modalEndDate" required class="form-control form-control-sm" value="<?= date('Y-m-t') ?>" style="background:#e5e7eb; border:1px solid #9ca3af; max-width:160px;">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tabFilterPencarian" role="tabpanel">
                            <div class="form-group row mb-2 align-items-center">
                                <label for="modalKeteranganJurnal" class="col-sm-3 col-form-label text-right" style="font-size:13px; color:#374151; font-weight:400;">Keterangan Jurnal :</label>
                                <div class="col-sm-6">
                                    <input type="text" id="modalKeteranganJurnal" class="form-control form-control-sm" placeholder="Contoh: Penjualan / nama pelanggan" style="background:#e5e7eb; border:1px solid #9ca3af;">
                                </div>
                            </div>
                            <div class="form-group row mb-0 align-items-center">
                                <label for="modalNomorJurnal" class="col-sm-3 col-form-label text-right" style="font-size:13px; color:#374151; font-weight:400;">Nomor Jurnal :</label>
                                <div class="col-sm-6">
                                    <input type="text" id="modalNomorJurnal" class="form-control form-control-sm" placeholder="Contoh: D25000001 / SD000001" style="background:#e5e7eb; border:1px solid #9ca3af;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4" style="gap:8px;">
                        <button type="button" class="btn btn-sm text-white px-4" id="btnCancelFilterModal" style="background:#0275a8; border-radius:2px;"><u>B</u>atal</button>
                        <button type="button" class="btn btn-sm text-white px-4" id="btnOkFilterModal" style="background:#0275a8; border-radius:2px;"><u>O</u>K</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<?php $this->load->view('partial/main/footergdg.php') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var endpoint = '<?= $report_endpoint ?? base_url('jurnal/sales-report-data') ?>';
    var emptyMessage = '<?= htmlspecialchars($empty_message ?? 'Data jurnal penjualan tidak ditemukan pada periode ini.', ENT_QUOTES) ?>';
    
    function formatMoney(num) {
        var p = parseFloat(num);
        if (isNaN(p)) return '';
        if (p === 0) return '';
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
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
    }
    
    function updatePrintTime() {
        var d = new Date();
        var ampm = d.getHours() >= 12 ? 'PM' : 'AM';
        var h = d.getHours() % 12;
        h = h ? h : 12; 
        var m = d.getMinutes().toString().padStart(2, '0');
        var timeStr = h + ':' + m + ' ' + ampm + ', &nbsp;&nbsp;' + formatDateFull(d);
        $('#printTime').html(timeStr);
    }

    function escapeHtml(string) {
        var matchHtmlRegExp = /["'&<>]/;
        var str = '' + string;
        var match = matchHtmlRegExp.exec(str);
        if (!match) return str;
        var escape;
        var html = '';
        var index = 0;
        var lastIndex = 0;
        for (index = match.index; index < str.length; index++) {
            switch (str.charCodeAt(index)) {
                case 34: escape = '&quot;'; break;
                case 38: escape = '&amp;'; break;
                case 39: escape = '&#39;'; break;
                case 60: escape = '&lt;'; break;
                case 62: escape = '&gt;'; break;
                default: continue;
            }
            if (lastIndex !== index) html += str.substring(lastIndex, index);
            lastIndex = index + 1;
            html += escape;
        }
        return lastIndex !== index ? html + str.substring(lastIndex, index) : html;
    }

    function formatDatePeriod(str) {
        if (!str) return '-';
        var parts = str.split('-');
        if (parts.length !== 3) return str;
        var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var mIdx = parseInt(parts[1], 10) - 1;
        return parts[2] + ' ' + (months[mIdx] || parts[1]) + ' ' + parts[0];
    }

    var hasLoadedReportOnce = false;
    var appliedSearch = { description: '', number: '' };

    function loadReport() {
        var startDate = $('#filterStartDate').val();
        var endDate = $('#filterEndDate').val();
        
        $('#lblPeriode').text(formatDatePeriod(startDate) + ' - ' + formatDatePeriod(endDate));
        $('#reportTbody').html('<tr><td colspan="6" class="text-center py-4 text-muted">Memuat data...</td></tr>');
        updatePrintTime();

        $.ajax({
            url: endpoint,
            type: 'POST',
            dataType: 'json',
            data: {
                start_date: startDate,
                end_date: endDate,
                keterangan_jurnal: appliedSearch.description,
                nomor_jurnal: appliedSearch.number
            },
            success: function(resp) {
                if (!resp.success) {
                    $('#reportTbody').html('<tr><td colspan="6" class="text-center text-danger py-4">' + escapeHtml(resp.message) + '</td></tr>');
                    return;
                }
                
                var data = (resp.data && resp.data.data) ? resp.data.data : [];
                if (!data.length) {
                    $('#reportTbody').html('<tr><td colspan="6" class="text-center text-muted py-4">' + escapeHtml(emptyMessage) + '</td></tr>');
                    return;
                }
                
                var html = '';
                data.forEach(function(h) {
                    // Transaction Header Row
                    var ket = escapeHtml(h.keterangan || 'Penjualan');
                    if (h.pelanggan && ket.toLowerCase().indexOf(h.pelanggan.toLowerCase()) === -1) {
                        ket += ', ' + escapeHtml(h.pelanggan);
                    }
                    var refNo = h.no_faktur || h.referensi || h.nomor_jurnal || '';
                    
                    html += '<tr class="trx-header">';
                    html += '<td>' + escapeHtml(formatDate(h.tanggal_transaksi)) + '</td>';
                    html += '<td colspan="5">' + ket + '</td>';
                    html += '</tr>';
                    
                    // Detail Rows
                    if (h.details && h.details.length) {
                        h.details.forEach(function(d) {
                            var isDebit = parseFloat(d.debit || 0) > 0;
                            var dept = d.cost_center || '';
                            var proyek = d.project_no || '';
                            var accDesc = (refNo ? '<span style="display:inline-block;min-width:110px;padding-right:12px;">' + escapeHtml(refNo) + '</span>' : '') +
                                '<span style="display:inline-block;min-width:65px;padding-right:10px;">' + escapeHtml(d.kode_rekening_display || d.kode_akun || '') + '</span>' +
                                escapeHtml(d.nama_akun || '');
                            
                            html += '<tr class="trx-detail">';
                            html += '<td></td>';
                            html += '<td class="acc-name">' + accDesc + '</td>';
                            html += '<td>' + escapeHtml(dept) + '</td>';
                            html += '<td class="text-right">' + (isDebit ? escapeHtml(formatMoney(d.debit)) : '') + '</td>';
                            html += '<td class="text-right">' + (!isDebit ? escapeHtml(formatMoney(d.kredit)) : '') + '</td>';
                            html += '<td>' + escapeHtml(proyek) + '</td>';
                            html += '</tr>';
                        });
                    }
                    
                    // Empty row separator
                    html += '<tr><td colspan="6" style="border-bottom: 1px solid #e5e7eb; padding: 2px;"></td></tr>';
                });
                
                $('#reportTbody').html(html);
            },
            error: function() {
                $('#reportTbody').html('<tr><td colspan="6" class="text-center text-danger py-4">Terjadi kesalahan koneksi.</td></tr>');
            }
        });
    }

    $('#btnFilter').on('click', function() {
        hasLoadedReportOnce = true;
        $('#modalStartDate').val($('#filterStartDate').val());
        $('#modalEndDate').val($('#filterEndDate').val());
        loadReport();
    });

    $('#btnOpenFilterModal').on('click', function() {
        $('#modalStartDate').val($('#filterStartDate').val());
        $('#modalEndDate').val($('#filterEndDate').val());
        $('#modalEndDate')[0].setCustomValidity('');
        $('#modalKeteranganJurnal').val(appliedSearch.description);
        $('#modalNomorJurnal').val(appliedSearch.number);
        $('#modalFilterData').modal({ backdrop: 'static', keyboard: false, show: true });
    });

    $('#btnOkFilterModal').on('click', function() {
        var sDate = $('#modalStartDate').val();
        var eDate = $('#modalEndDate').val();
        if (!sDate || !eDate || sDate > eDate) {
            $('#tab-standar-link').tab('show');
            $('#modalEndDate')[0].setCustomValidity(sDate && eDate && sDate > eDate ? 'Tanggal akhir harus sama dengan atau setelah tanggal awal.' : '');
            $('#modalStartDate')[0].reportValidity() && $('#modalEndDate')[0].reportValidity();
            return;
        }
        $('#filterStartDate').val(sDate);
        $('#filterEndDate').val(eDate);
        appliedSearch = {
            description: $.trim($('#modalKeteranganJurnal').val() || ''),
            number: $.trim($('#modalNomorJurnal').val() || '')
        };
        hasLoadedReportOnce = true;
        $('#modalFilterData').modal('hide');
        loadReport();
    });

    $('#btnCancelFilterModal').on('click', function() {
        if (!hasLoadedReportOnce) {
            window.location.href = '<?= base_url('laporan/penjualan') ?>';
            return;
        }
        $('#modalFilterData').modal('hide');
    });

    // Tampilkan modal Filter Data terlebih dahulu sebelum laporan dimuat
    $('#modalFilterData').modal({ backdrop: 'static', keyboard: false, show: true });
});
</script>
