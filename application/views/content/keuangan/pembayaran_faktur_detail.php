<style>
    .invoice-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .invoice-row:hover td {
        background-color: #f1f7fc !important;
    }
    .invoice-row.selected td {
        background-color: #d1ecf1 !important;
        font-weight: 600;
    }
    .invoice-row.selected td a.btn {
        font-weight: normal;
    }

    .history-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .history-row:hover td {
        background-color: #f8fafc !important;
    }
    .history-row.selected td {
        background-color: #e2e8f0 !important;
    }

    /* Context Menu Styles */
    .context-menu {
        position: absolute;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border-radius: 6px;
        z-index: 1050;
        min-width: 170px;
        overflow: hidden;
    }
    .context-menu ul {
        list-style: none;
        padding: 4px 0;
        margin: 0;
    }
    .context-menu li {
        padding: 9px 16px;
        cursor: pointer;
        font-size: 13px;
        color: #334155;
        display: flex;
        align-items: center;
        transition: all 0.15s ease;
    }
    .context-menu li:hover {
        background: #f1f5f9;
        color: #0284c7;
    }

    /* Modal Styling */
    .zahir-modal .modal-content {
        border-radius: 8px;
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }
    .zahir-modal .modal-header {
        background: linear-gradient(135deg, #127fad 0%, #3197c5 100%);
        color: #fff;
        border-radius: 8px 8px 0 0;
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
                    <div class="col-sm-7">
                        <h1 class="m-0"><i class="fas fa-file-invoice-dollar mr-2"></i><?= htmlspecialchars($customer_name) ?></h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('keuangan/pembayaran') ?>">Pembayaran</a></li>
                            <li class="breadcrumb-item active">Detail Customer</li>
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
                    <a href="<?= base_url('keuangan/pembayaran') ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>

                <!-- TABEL ATAS: Faktur Selesai DO Belum Lunas -->
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-list mr-2"></i>Faktur Selesai DO Belum Lunas
                        </h3>
                        <div class="card-tools ml-auto">
                            <small class="text-white-50 mr-2"><i class="fas fa-hand-pointer mr-1"></i>Klik baris faktur untuk melihat riwayat pembayaran</small>
                            <span class="badge badge-light"><?= count($fakturs) ?> faktur</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover table-sm mb-0" id="tabelPembayaranFaktur">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No Faktur</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Tanggal Tempo</th>
                                    <th>Customer</th>
                                    <th class="text-right">Total Piutang</th>
                                    <th class="text-right">Total Pembayaran</th>
                                    <th class="text-right">BG Belum Cair</th>
                                    <th class="text-right">Sisa Piutang</th>
                                    <th class="text-center">Status Bayar</th>
                                    <th class="text-center">Overdue</th>
                                    <th class="text-center" style="min-width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($fakturs)): ?>
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">Tidak ada faktur belum lunas untuk customer ini.</td>
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
                                        <tr class="invoice-row" 
                                            data-id-faktur="<?= (int)$faktur['id_faktur'] ?>" 
                                            data-no-faktur="<?= htmlspecialchars($faktur['no_faktur']) ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($faktur['no_faktur']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($faktur['no_so'] ?? '-') ?></small>
                                            </td>
                                            <td class="text-nowrap">
                                                <?= !empty($faktur['tanggal_faktur']) ? date('d/m/Y', strtotime($faktur['tanggal_faktur'])) : '-' ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?= !empty($faktur['tanggal_jatuh_tempo']) ? date('d/m/Y', strtotime($faktur['tanggal_jatuh_tempo'])) : '-' ?>
                                            </td>
                                            <td><?= htmlspecialchars($faktur['nama_customer']) ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$faktur['total_tagihan'], 0, ',', '.') ?></td>
                                            <td class="text-right">Rp <?= number_format((float)$faktur['total_pembayaran'], 0, ',', '.') ?></td>
                                            <td class="text-right text-warning">Rp <?= number_format((float)($faktur['total_bg_pending'] ?? 0), 0, ',', '.') ?></td>
                                            <td class="text-right font-weight-bold text-danger">Rp <?= number_format((float)$faktur['sisa_tagihan'], 0, ',', '.') ?></td>
                                            <td class="text-center"><span class="badge badge-<?= $status_class ?>"><?= htmlspecialchars($status_label) ?></span></td>
                                            <td class="text-center"><span class="badge badge-<?= $overdue_class ?>"><?= htmlspecialchars($overdue) ?></span></td>
                                            <td class="text-center text-nowrap">
                                                <div class="d-inline-flex align-items-center" style="gap: 5px;">
                                                    <?php if ((float)($faktur['total_bg_pending'] ?? 0) > 0): ?>
                                                        <?php
                                                        $bg_id = !empty($faktur['id_pembayaran_bg_pending']) ? $faktur['id_pembayaran_bg_pending'] : 1;
                                                        ?>
                                                        <a href="<?= base_url('keuangan/pembayaran/bayar/' . $faktur['id_faktur'] . '?cair_bg=' . $bg_id) ?>" 
                                                           class="btn btn-warning btn-sm btn-bayar-action font-weight-bold" 
                                                           title="Konfirmasi Pencairan BG">
                                                            <i class="fas fa-check-circle mr-1"></i>Cairkan BG
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="<?= base_url('keuangan/pembayaran/bayar/' . $faktur['id_faktur']) ?>" 
                                                       class="btn btn-success btn-sm btn-bayar-action" 
                                                       title="Input Pembayaran Baru (Cash, Transfer, Retur, dll.)">
                                                        <i class="fas fa-money-bill-wave mr-1"></i>Bayar
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABEL BAWAH: Riwayat Pembayaran Faktur Terpilih -->
                <div class="card card-outline card-info shadow-sm" id="cardRiwayatPembayaran">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-history mr-2"></i>Riwayat Pembayaran - <span id="labelSelectedFaktur" class="badge badge-light text-info font-weight-bold ml-1">Pilih Faktur</span>
                        </h3>
                        <div class="card-tools ml-auto d-flex align-items-center">
                            <small class="text-white-50 mr-3 d-none d-md-inline">
                                <i class="fas fa-mouse-pointer mr-1"></i>Klik kanan baris pembayaran untuk <b>Detail Jurnal</b>
                            </small>
                            <span class="badge badge-light" id="badgeJumlahHistory">0 pembayaran</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="loadingHistory" class="text-center py-4" style="display: none;">
                            <i class="fas fa-spinner fa-spin fa-2x text-info"></i>
                            <p class="mt-2 text-muted mb-0">Memuat riwayat pembayaran...</p>
                        </div>
                        <div class="table-responsive" id="containerTabelHistori">
                            <table class="table table-bordered table-hover table-striped table-sm mb-0" id="tabelHistoriPembayaran">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 15%;">Tanggal Pembayaran</th>
                                        <th style="width: 15%;">Metode Pembayaran</th>
                                        <th style="width: 18%;">No. Referensi / Bank</th>
                                        <th style="width: 14%;">Status BG / Kasir</th>
                                        <th style="width: 15%;" class="text-right">Jumlah Bayar</th>
                                        <th style="width: 12%;" class="text-right">Diskon / Potongan</th>
                                        <th style="width: 11%;">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyHistoriPembayaran">
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle mr-1"></i>Silakan klik salah satu faktur di atas untuk melihat riwayat pembayaran.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>

<!-- Context Menu for Right Click -->
<div id="context-menu-pembayaran" class="context-menu" style="display:none;">
    <ul>
        <li id="menu-detail-jurnal-pembayaran">
            <i class="fas fa-book-open text-primary mr-2"></i>Detail Jurnal
        </li>
    </ul>
</div>

<!-- MODAL: PERINCIAN JURNAL -->
<div class="modal fade zahir-modal" id="modal-perincian-jurnal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-book-open mr-2"></i> Perincian Transaksi Jurnal
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light d-flex justify-content-between border-bottom flex-wrap">
                    <div class="mb-2 mb-md-0">
                        <strong>No. Ref:</strong> <span id="detail-ref" class="text-primary font-weight-bold">-</span><br>
                        <strong>Tanggal:</strong> <span id="detail-tanggal">-</span>
                    </div>
                    <div class="text-right">
                        <strong>Status:</strong> <span id="detail-status">-</span><br>
                        <strong>Keterangan:</strong> <span id="detail-keterangan">-</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 20%;">Kode Akun</th>
                                <th style="width: 40%;">Nama Akun</th>
                                <th style="width: 20%;" class="text-right">Debit</th>
                                <th style="width: 20%;" class="text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody id="detail-lines-body">
                            <!-- Loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    // Inisialisasi DataTable untuk tabel faktur
    if ($.fn.DataTable) {
        $('#tabelPembayaranFaktur').DataTable({
            "language": {
                "emptyTable": "Tidak ada data faktur",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ faktur",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 faktur",
                "lengthMenu": "Tampilkan _MENU_ faktur",
                "search": "Cari Faktur:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    }

    let activeFakturId = null;
    let contextMenuPaymentId = null;
    let contextMenuJurnalId = null;

    // Helper: format angka rupiah
    function formatRupiah(num) {
        let val = parseFloat(num) || 0;
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
    }

    // Helper: format tanggal YYYY-MM-DD ke DD/MM/YYYY
    function formatTanggal(tglStr) {
        if (!tglStr) return '-';
        let parts = tglStr.split(' ')[0].split('-');
        if (parts.length === 3) {
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }
        return tglStr;
    }

    // Load Riwayat Pembayaran via AJAX
    function loadPaymentHistory(idFaktur, noFaktur) {
        if (!idFaktur) return;
        activeFakturId = idFaktur;

        $('#labelSelectedFaktur').text(noFaktur || ('ID #' + idFaktur));
        $('#loadingHistory').show();
        $('#containerTabelHistori').hide();

        $.ajax({
            url: '<?= base_url("keuangan/pembayaran/get_payment_history_json") ?>',
            type: 'GET',
            data: { id_faktur: idFaktur },
            dataType: 'json',
            success: function(res) {
                $('#loadingHistory').hide();
                $('#containerTabelHistori').show();

                if (res && res.status && Array.isArray(res.data)) {
                    let history = res.data;
                    $('#badgeJumlahHistory').text(history.length + ' pembayaran');

                    if (history.length === 0) {
                        $('#bodyHistoriPembayaran').html(`
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle mr-1"></i>Belum ada riwayat pembayaran untuk faktur <strong>${noFaktur}</strong>.
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    let rowsHtml = '';
                    history.forEach(function(item) {
                        let isBg = (item.metode_pembayaran || '').toLowerCase() === 'bg';
                        let isBgCair = (item.status_bg || '') === 'cair';
                        let statusKasir = item.status_kasir || '';

                        // Status BG / Kasir badge
                        let statusBadge = '';
                        if (isBg) {
                            statusBadge = isBgCair 
                                ? '<span class="badge badge-success">Sudah Cair</span>' 
                                : '<span class="badge badge-warning">Belum Cair</span> <a href="<?= base_url('keuangan/pembayaran/bayar/') ?>' + (item.id_faktur || activeFakturId) + '?cair_bg=' + item.id_pembayaran + '" class="btn btn-xs btn-outline-warning ml-1 font-weight-bold" title="Konfirmasi pencairan BG ini"><i class="fas fa-check-circle mr-1"></i>Cairkan</a>';
                        } else if (statusKasir === 'pending_kasir') {
                            statusBadge = '<span class="badge badge-info">Pending Kasir</span>';
                        } else {
                            statusBadge = '<span class="badge badge-secondary">Langsung Masuk</span>';
                        }

                        // Info bank / BG
                        let refBankInfo = '-';
                        if (item.no_bg || item.nama_bank) {
                            let parts = [];
                            if (item.no_bg) parts.push('No. BG: ' + item.no_bg);
                            if (item.nama_bank) parts.push('Bank: ' + item.nama_bank);
                            refBankInfo = parts.join(' | ');
                        } else if (item.nomor_jurnal) {
                            refBankInfo = '<span class="text-primary font-weight-bold"><i class="fas fa-book mr-1"></i>' + item.nomor_jurnal + '</span>';
                        }

                        let diskonText = parseFloat(item.jumlah_diskon) > 0 ? formatRupiah(item.jumlah_diskon) : '-';

                        rowsHtml += `
                            <tr class="history-row" 
                                data-id-pembayaran="${item.id_pembayaran}" 
                                data-id-jurnal="${item.id_jurnal || ''}"
                                title="Klik kanan untuk melihat Detail Jurnal">
                                <td>${formatTanggal(item.tanggal_pembayaran)}</td>
                                <td>
                                    <strong>${item.metode_pembayaran || '-'}</strong>
                                    ${item.nomor_jurnal ? '<br><small class="text-muted"><i class="fas fa-bookmark mr-1"></i>' + item.nomor_jurnal + '</small>' : ''}
                                </td>
                                <td><small>${refBankInfo}</small></td>
                                <td>${statusBadge}</td>
                                <td class="text-right font-weight-bold text-success">${formatRupiah(item.jumlah_pembayaran)}</td>
                                <td class="text-right text-info">${diskonText}</td>
                                <td><small class="text-muted">${item.keterangan || '-'}</small></td>
                            </tr>
                        `;
                    });

                    $('#bodyHistoriPembayaran').html(rowsHtml);
                } else {
                    $('#bodyHistoriPembayaran').html(`
                        <tr>
                            <td colspan="7" class="text-center text-danger py-4">Gagal memuat riwayat pembayaran.</td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#loadingHistory').hide();
                $('#containerTabelHistori').show();
                $('#bodyHistoriPembayaran').html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4">Terjadi kesalahan sistem saat memuat data.</td>
                    </tr>
                `);
            }
        });
    }

    // Klik Baris Faktur -> Load riwayat pembayaran
    $(document).on('click', '#tabelPembayaranFaktur tbody tr.invoice-row', function(e) {
        // Jangan trigger jika klik tombol bayar
        if ($(e.target).closest('.btn-bayar-action, a, button').length) {
            return;
        }

        $('#tabelPembayaranFaktur tbody tr.invoice-row').removeClass('selected table-active');
        $(this).addClass('selected table-active');

        let idFaktur = $(this).data('id-faktur');
        let noFaktur = $(this).data('no-faktur');
        loadPaymentHistory(idFaktur, noFaktur);
    });

    // Otomatis pilih faktur pertama jika ada data faktur
    let $firstInvoiceRow = $('#tabelPembayaranFaktur tbody tr.invoice-row').first();
    if ($firstInvoiceRow.length > 0) {
        $firstInvoiceRow.addClass('selected table-active');
        let idFaktur = $firstInvoiceRow.data('id-faktur');
        let noFaktur = $firstInvoiceRow.data('no-faktur');
        loadPaymentHistory(idFaktur, noFaktur);
    }

    // Sembunyikan context menu saat klik di mana saja
    $(document).on('click', function() {
        $('#context-menu-pembayaran').hide();
    });

    // Klik kanan pada baris riwayat pembayaran -> tampilkan context menu
    $(document).on('contextmenu', '#bodyHistoriPembayaran tr.history-row', function(e) {
        let idPembayaran = $(this).data('id-pembayaran');
        if (!idPembayaran) return;

        e.preventDefault();
        contextMenuPaymentId = idPembayaran;
        contextMenuJurnalId = $(this).data('id-jurnal') || null;

        $('#bodyHistoriPembayaran tr.history-row').removeClass('selected');
        $(this).addClass('selected');

        let posX = e.pageX;
        let posY = e.pageY;

        // Cegah menu keluar dari viewport
        let menuWidth = 180;
        if (posX + menuWidth > $(window).width()) {
            posX = $(window).width() - menuWidth - 10;
        }

        $('#context-menu-pembayaran').css({
            display: "block",
            left: posX,
            top: posY
        });
    });

    // Klik "Detail Jurnal" pada context menu
    $('#menu-detail-jurnal-pembayaran').on('click', function() {
        $('#context-menu-pembayaran').hide();
        if (!contextMenuPaymentId && !contextMenuJurnalId) return;
        showDetailJurnalModal(contextMenuJurnalId, contextMenuPaymentId);
    });

    // Fungsi menampilkan modal detail jurnal
    function showDetailJurnalModal(idJurnal, idPembayaran) {
        $.ajax({
            url: '<?= base_url("buku_besar/jurnal_umum_detail") ?>',
            type: 'POST',
            data: { 
                id_jurnal: idJurnal || 0,
                id_pembayaran: idPembayaran || 0
            },
            dataType: 'json',
            success: function(res) {
                if (res && res.success && res.data) {
                    let journal = res.data.journal || {};
                    let details = res.data.details || [];

                    $('#detail-ref').text(journal.nomor_jurnal || journal.source_no || '-');
                    $('#detail-tanggal').text(formatTanggal(journal.tanggal_transaksi));
                    $('#detail-keterangan').text(journal.keterangan || '-');

                    let statusBadge = (journal.status === 'POSTED')
                        ? '<span class="badge badge-success font-weight-bold">POSTED</span>'
                        : '<span class="badge badge-warning font-weight-bold">' + (journal.status || 'DRAFT') + '</span>';
                    $('#detail-status').html(statusBadge);

                    let linesHtml = '';
                    let totalDebit = 0;
                    let totalKredit = 0;

                    if (details.length === 0) {
                        linesHtml = '<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada baris akun pada jurnal ini.</td></tr>';
                    } else {
                        details.forEach(function(row) {
                            let deb = parseFloat(row.debit) || 0;
                            let kre = parseFloat(row.kredit) || 0;
                            totalDebit += deb;
                            totalKredit += kre;

                            let debitStr = deb > 0 ? new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(deb) : '-';
                            let kreditStr = kre > 0 ? new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(kre) : '-';

                            linesHtml += `
                                <tr>
                                    <td>${row.kode_akun || '-'}</td>
                                    <td>${row.nama_akun || '-'}</td>
                                    <td class="text-right">${debitStr}</td>
                                    <td class="text-right">${kreditStr}</td>
                                </tr>
                            `;
                        });

                        linesHtml += `
                            <tr class="font-weight-bold bg-light">
                                <td colspan="2" class="text-right">Total :</td>
                                <td class="text-right">${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(totalDebit)}</td>
                                <td class="text-right">${new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2 }).format(totalKredit)}</td>
                            </tr>
                        `;
                    }

                    $('#detail-lines-body').html(linesHtml);
                    $('#modal-perincian-jurnal').modal('show');
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Detail Jurnal',
                            text: res.message || 'Transaksi pembayaran ini belum tercatat pada jurnal umum.'
                        });
                    } else {
                        alert(res.message || 'Transaksi pembayaran ini belum tercatat pada jurnal umum.');
                    }
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan sistem saat mengambil data jurnal.'
                    });
                } else {
                    alert('Terjadi kesalahan sistem saat mengambil data jurnal.');
                }
            }
        });
    }
});
</script>
