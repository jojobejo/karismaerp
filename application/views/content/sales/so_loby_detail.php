<!-- views/content/sales/so_loby_detail.php -->
<!-- Detail Sales Order Loby (Zahir ERP Style) -->
<style>
    :root {
        --zahir-blue: #127fad;
        --zahir-dark-blue: #0f6c94;
        --zahir-light-bg: #f0f4f7;
        --zahir-card-border: #d1dbe3;
        --zahir-text: #1e293b;
    }

    body.hold-transition {
        background-color: var(--zahir-light-bg);
    }

    .pb-container {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--zahir-text);
        padding: 20px;
        min-height: calc(100vh - 75px);
        display: flex;
        flex-direction: column;
    }

    .zahir-card {
        background: #fff;
        border: 1px solid var(--zahir-card-border);
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        margin-bottom: 24px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: calc(100vh - 115px);
    }

    /* Header Title */
    .form-header-title {
        background: linear-gradient(135deg, var(--zahir-blue) 0%, #3197c5 100%);
        color: #fff;
        padding: 16px 24px;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 4px 15px rgba(18, 127, 173, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-header-title h2 {
        margin: 0;
        font-size: 19px;
        font-weight: 700;
        color: #fff;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Kolom Header Form Info */
    .form-header-section {
        padding: 20px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #eef2f5;
    }

    .info-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
    }

    .info-group-box {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 14px 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .info-group-title {
        font-size: 11px;
        font-weight: 700;
        color: var(--zahir-blue);
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 1px dashed #e2e8f0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-row {
        display: flex;
        font-size: 13px;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .info-row:last-child {
        margin-bottom: 0;
    }

    .info-label {
        width: 105px;
        color: #64748b;
        font-weight: 500;
        flex-shrink: 0;
    }

    .info-val {
        color: #1e293b;
        font-weight: 600;
        word-break: break-word;
    }

    /* Area Grid / Tabel Transaksi */
    .table-container {
        flex: 1;
        overflow-x: auto;
        background: #fff;
    }

    .grid-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .grid-table thead th {
        background-color: var(--zahir-blue) !important;
        color: #fff !important;
        font-weight: 600;
        padding: 10px 12px;
        font-size: 13px;
        letter-spacing: 0.3px;
        border-right: 1px solid rgba(255,255,255,0.2);
        border-bottom: none;
        vertical-align: middle;
    }

    .grid-table thead th:last-child {
        border-right: none;
    }

    .grid-table tbody td {
        padding: 10px 12px;
        font-size: 13px;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        vertical-align: middle;
        background: #fff;
        color: #334155;
    }

    .grid-table tbody td:last-child {
        border-right: none;
    }

    .grid-table tbody tr:nth-child(even) td {
        background: #f8fafc;
    }

    .grid-table tbody tr:hover td {
        background: #eff6ff;
    }

    .grid-table tfoot th,
    .grid-table tfoot td {
        padding: 12px 14px;
        background: #f1f5f9;
        border-top: 2px solid #cbd5e1;
        font-size: 13px;
        color: #1e293b;
    }

    /* Bottom Bar */
    .form-bottom-bar {
        padding: 14px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        border-top: 1px solid #eef2f5;
        margin-top: auto;
    }

    /* Tombol Zahir */
    .btn-zahir {
        font-size: 13px;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 90px;
        text-decoration: none !important;
    }

    .btn-zahir-primary { background: var(--zahir-blue); color: #fff; }
    .btn-zahir-primary:hover { background: var(--zahir-dark-blue); color: #fff; }
    .btn-zahir-secondary { background: #64748b; color: #fff; }
    .btn-zahir-secondary:hover { background: #475569; color: #fff; }
    .btn-zahir-teal { background: #0d9488; color: #fff; }
    .btn-zahir-teal:hover { background: #0f766e; color: #fff; }
    .btn-zahir-danger { background: #ef4444; color: #fff; }
    .btn-zahir-danger:hover { background: #dc2626; color: #fff; }
    .btn-zahir-warning { background: #f59e0b; color: #fff; }
    .btn-zahir-warning:hover { background: #d97706; color: #fff; }

    @media (max-width: 991.98px) {
        .info-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <!-- Navbar & Sidebar -->
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="pb-container">

            <!-- FLASH MESSAGE -->
            <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $cls): ?>
                <?php if ($msg = $this->session->flashdata($key)): ?>
                    <div class="alert alert-<?= $cls ?> alert-dismissible fade show mb-3">
                        <i class="fas fa-<?= $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-circle' : 'info-circle') ?> mr-1"></i>
                        <?= $msg ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="zahir-card">
                <!-- Header Banner -->
                <div class="form-header-title">
                    <h2>
                        <i class="fas fa-store"></i> 
                        Detail Sales Order Loby: <strong><?= html_escape($so['no_so']) ?></strong>
                    </h2>
                    <div>
                        <?php if ($so['status'] === 'completed' || !empty($fakturs)): ?>
                            <span class="badge badge-success px-3 py-2" style="font-size: 13px; letter-spacing: 0.5px;">
                                <i class="fas fa-check-double mr-1"></i> INVOICED / SELESAI
                            </span>
                        <?php elseif ($so['status'] === 'cancelled'): ?>
                            <span class="badge badge-danger px-3 py-2" style="font-size: 13px; letter-spacing: 0.5px;">
                                <i class="fas fa-times-circle mr-1"></i> DIBATALKAN
                            </span>
                        <?php else: ?>
                            <span class="badge badge-warning px-3 py-2 text-dark font-weight-bold" style="font-size: 13px; letter-spacing: 0.5px; background:#fef08a;">
                                <i class="fas fa-clock mr-1 text-warning"></i> OPEN (SIAP FAKTUR)
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Form Header Info Section (3 Kolom Elegan) -->
                <div class="form-header-section">
                    <div class="info-summary-grid">
                        <!-- Box 1: Informasi Dokumen SO -->
                        <div class="info-group-box">
                            <div class="info-group-title">
                                <i class="fas fa-file-invoice"></i> Informasi Dokumen SO
                            </div>
                            <div class="info-row">
                                <span class="info-label">No. SO Loby</span>
                                <span class="info-val" style="color: var(--zahir-blue);"><?= html_escape($so['no_so']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Tanggal Transaksi</span>
                                <span class="info-val"><?= date('d F Y', strtotime($so['tanggal_transaksi'])) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Cara Bayar</span>
                                <span class="info-val">
                                    <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                        <i class="fas fa-money-bill-wave mr-1"></i> CASH
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- Box 2: Customer & Kios -->
                        <div class="info-group-box">
                            <div class="info-group-title">
                                <i class="fas fa-user-tag"></i> Data Customer
                            </div>
                            <div class="info-row">
                                <span class="info-label">Customer</span>
                                <span class="info-val"><?= html_escape($so['customer_name'] ?: ($so['nama_customer'] ?? '-')) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Kode Customer</span>
                                <span class="info-val text-muted"><?= html_escape($so['kd_customer']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Kios / Wilayah</span>
                                <span class="info-val"><?= html_escape($so['nama_kios'] ?: '-') ?> (<?= html_escape($so['regional'] ?: '-') ?>)</span>
                            </div>
                        </div>

                        <!-- Box 3: Lokasi & User -->
                        <div class="info-group-box">
                            <div class="info-group-title">
                                <i class="fas fa-warehouse"></i> Gudang &amp; Inputer
                            </div>
                            <div class="info-row">
                                <span class="info-label">Gudang Stok</span>
                                <span class="info-val" style="color: var(--zahir-dark-blue); font-weight:700;">
                                    <?= html_escape($so['nama_gudang'] ?: 'Gudang ID: ' . $so['gudang_id']) ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Dibuat Oleh</span>
                                <span class="info-val"><?= html_escape($so['create_by']) ?> <small class="text-muted">(<?= date('d/m/y H:i', strtotime($so['create_at'])) ?>)</small></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Catatan</span>
                                <span class="info-val font-italic text-secondary"><?= html_escape($so['catatan'] ?: '-') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid Rincian Barang -->
                <div class="table-container">
                    <table class="grid-table">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">No</th>
                                <th style="width: 140px;">Kode Barang</th>
                                <th style="width: auto;">Nama Barang</th>
                                <th style="width: 120px; text-align: center;">No. Lot</th>
                                <th style="width: 110px; text-align: center;">Expired Date</th>
                                <th style="width: 85px; text-align: right;">Qty Box</th>
                                <th style="width: 85px; text-align: right;">+Ecer</th>
                                <th style="width: 95px; text-align: right;">Total Qty</th>
                                <th style="width: 70px; text-align: center;">Satuan</th>
                                <th style="width: 130px; text-align: right;">Harga/Pcs</th>
                                <th style="width: 65px; text-align: center;">Disc%</th>
                                <th style="width: 145px; text-align: right;">Total Harga</th>
                                <th style="width: 125px; text-align: center;">Status Faktur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grandTotal = 0;
                            $totalQty = 0;
                            $totalBox = 0;
                            $totalEcer = 0;
                            $no = 1;
                            if (!empty($details)): 
                                foreach ($details as $d): 
                                    $grandTotal += (float)$d['total_harga'];
                                    $totalQty   += (float)$d['qty'];
                                    $totalBox   += (float)($d['qty_box'] ?? 0);
                                    $totalEcer  += (float)($d['qty_satuan'] ?? 0);
                            ?>
                                <tr>
                                    <td style="text-align: center; color: #64748b;"><?= $no++ ?></td>
                                    <td><strong style="color: var(--zahir-blue);"><?= html_escape($d['kd_barang']) ?></strong></td>
                                    <td><strong style="color: #1e293b;"><?= html_escape($d['nama_barang']) ?></strong></td>
                                    <td style="text-align: center;"><span class="badge badge-light border px-2 py-1"><?= html_escape($d['no_lot'] ?: '-') ?></span></td>
                                    <td style="text-align: center; color: #475569;"><?= $d['expired_date'] ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?></td>
                                    <td style="text-align: right;"><?= number_format((float)($d['qty_box'] ?? 0), 0, ',', '.') ?></td>
                                    <td style="text-align: right;"><?= number_format((float)($d['qty_satuan'] ?? 0), 0, ',', '.') ?></td>
                                    <td style="text-align: right; font-weight: 700; color: var(--zahir-dark-blue);"><?= number_format((float)$d['qty'], 0, ',', '.') ?></td>
                                    <td style="text-align: center;"><span class="badge badge-secondary" style="font-size:11px;"><?= html_escape($d['satuan']) ?></span></td>
                                    <td style="text-align: right;">Rp <?= number_format((float)$d['hrg_satuan'], 0, ',', '.') ?></td>
                                    <td style="text-align: center;"><?= (float)$d['disc'] > 0 ? (float)$d['disc'] . '%' : '-' ?></td>
                                    <td style="text-align: right; font-weight: 700; color: #166534;">Rp <?= number_format((float)$d['total_harga'], 0, ',', '.') ?></td>
                                    <td style="text-align: center;">
                                        <?php if ((float)$d['qty_faktur'] >= (float)$d['qty'] && (float)$d['qty'] > 0): ?>
                                            <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Lengkap</span>
                                        <?php elseif ((float)$d['qty_faktur'] > 0): ?>
                                            <span class="badge badge-warning px-2 py-1"><?= number_format((float)$d['qty_faktur'], 0, ',', '.') ?> pcs</span>
                                        <?php else: ?>
                                            <span class="badge badge-light border text-muted px-2 py-1">Belum Faktur</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="13" style="text-align: center; padding: 24px; color: #94a3b8;">
                                        Tidak ada item rincian barang.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right; font-weight: 700;">TOTAL QUANTITY:</td>
                                <td style="text-align: right; font-weight: 700;"><?= number_format($totalBox, 0, ',', '.') ?> box</td>
                                <td style="text-align: right; font-weight: 700;"><?= number_format($totalEcer, 0, ',', '.') ?> ecer</td>
                                <td style="text-align: right; font-weight: 700; color: var(--zahir-blue);"><?= number_format($totalQty, 0, ',', '.') ?> pcs</td>
                                <td colspan="3" style="text-align: right; font-weight: 700; font-size: 14px;">GRAND TOTAL (CASH):</td>
                                <td style="text-align: right; font-weight: 800; font-size: 16px; color: #15803d;">Rp <?= number_format($grandTotal, 0, ',', '.') ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Informasi Faktur Penjualan Terkait (Jika Ada) -->
                <?php if (!empty($fakturs)): ?>
                    <div style="padding: 16px 24px; background: #f0fdf4; border-top: 1px solid #bbf7d0;">
                        <h4 style="font-size: 14px; font-weight: 700; color: #166534; margin: 0 0 10px 0;">
                            <i class="fas fa-file-invoice-dollar mr-1"></i> Faktur Penjualan Loby Terbit
                        </h4>
                        <div class="table-responsive bg-white rounded border">
                            <table class="table table-sm table-hover m-0">
                                <thead class="bg-light text-center" style="font-size: 12px;">
                                    <tr>
                                        <th>No. Faktur</th>
                                        <th>Tanggal Faktur</th>
                                        <th>Status Faktur</th>
                                        <th>Status Keuangan</th>
                                        <th>Dibuat Oleh</th>
                                        <th style="width: 180px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size: 13px;">
                                    <?php foreach ($fakturs as $f): ?>
                                        <tr class="text-center align-middle">
                                            <td class="font-weight-bold" style="color: var(--zahir-blue);"><?= html_escape($f['no_faktur']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($f['tanggal_faktur'])) ?></td>
                                            <td><span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Selesai DO</span></td>
                                            <td>
                                                <span class="badge badge-info px-2 py-1">
                                                    <i class="fas fa-file-invoice mr-1"></i> Siap Bayar
                                                </span>
                                            </td>
                                            <td><?= html_escape($f['create_by']) ?></td>
                                            <td>
                                                <a href="<?= base_url('sales_order_loby/detail_faktur/' . $f['id_faktur']) ?>" class="btn btn-xs btn-outline-info font-weight-bold mr-1">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <a href="<?= base_url('sales_order_loby/print_faktur/' . $f['id_faktur']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary font-weight-bold">
                                                    <i class="fas fa-print"></i> Cetak
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Bottom Action Bar -->
                <div class="form-bottom-bar">
                    <div>
                        <a href="<?= base_url('sales_order_loby') ?>" class="btn-zahir btn-zahir-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <?php if (empty($fakturs) && $so['status'] !== 'cancelled'): ?>
                            <a href="<?= base_url('sales_order_loby/edit/' . $so['id_so']) ?>" class="btn-zahir btn-zahir-warning">
                                <i class="fas fa-edit"></i> Edit SO
                            </a>
                            <a href="<?= base_url('sales_order_loby/cancel/' . $so['id_so']) ?>" class="btn-zahir btn-zahir-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan SO Loby ini? Reservasi stok barang akan dilepas.')">
                                <i class="fas fa-times"></i> Batalkan SO
                            </a>
                            <a href="<?= base_url('sales_order_loby/form_faktur/' . $so['id_so']) ?>" class="btn-zahir btn-zahir-teal" style="background:#059669; font-size:14px; padding:8px 24px;">
                                <i class="fas fa-file-invoice-dollar"></i> Proses Faktur Sekarang
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php $this->load->view('partial/main/footer') ?>
</div>
</body>
