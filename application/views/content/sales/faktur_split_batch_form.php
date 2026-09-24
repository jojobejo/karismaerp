<!-- views/content/sales/faktur_split_batch_form.php -->
<?php
$back_url = !empty($back_url) ? $back_url : base_url('sales_order/pecah_faktur');
?>
<style>
    /* Reset gaya tabel agar solid standar (TIDAK SEPERTI CARD / SALES ORDER) */
    table.table, 
    .table-responsive table.table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
    }
    table.table tbody tr, 
    table.table tfoot tr {
        background: transparent !important;
        box-shadow: none !important;
    }
    table.table tbody td, 
    table.table thead th,
    table.table tfoot td {
        border-radius: 0 !important;
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.04) !important;
        box-shadow: none !important;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02) !important;
    }

    /* Styling Tata Letak Batch Split */
    .split-top-card {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    .stock-monitor-compact-table th {
        font-size: 11.5px;
        background: #f1f5f9;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #475569;
        padding: 6px 10px;
    }
    .stock-monitor-compact-table td {
        font-size: 12px;
        padding: 6px 10px;
        vertical-align: middle !important;
    }
    .split-slot-card {
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        transition: all 0.2s ease;
        border: 1px solid #cbd5e1;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .split-slot-card:hover {
        border-color: #f59e0b;
        box-shadow: 0 6px 16px rgba(245, 158, 11, 0.12);
    }
    .split-slot-header {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        padding: 8px 12px;
        border-radius: 8px 8px 0 0;
        cursor: pointer;
    }
    .badge-faktur-h {
        font-size: 11.5px;
        padding: 3px 7px;
        font-weight: 700;
    }
    .table-split-items-compact th {
        font-size: 11px;
        background: #f8fafc;
        padding: 5px 6px;
        color: #475569;
    }
    .table-split-items-compact td {
        font-size: 11.5px;
        padding: 4px 6px;
        vertical-align: middle !important;
    }
    .input-qty-split {
        font-size: 12px;
        height: 28px;
        padding: 2px 6px;
        text-align: right;
        font-weight: 700;
        min-width: 65px;
    }
    .input-hrg-split {
        font-size: 12px;
        height: 28px;
        padding: 2px 6px;
        text-align: right;
        min-width: 85px;
    }
    .cell-subtotal {
        font-size: 11.5px;
        white-space: nowrap;
    }

    /* Select2 Compact Style untuk Customer Penerima di Slot Pecahan */
    .slot-column .select2-container {
        width: 100% !important;
    }
    .slot-column .select2-container .select2-selection--single {
        height: 32px !important;
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
        padding: 2px 4px !important;
        background-color: #fff !important;
    }
    .slot-column .select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        padding-left: 4px !important;
        padding-right: 20px !important;
    }
    .slot-column .select2-container .select2-selection--single .select2-selection__arrow {
        height: 30px !important;
        right: 4px !important;
    }
    .select2-results__option {
        font-size: 11.5px !important;
        padding: 5px 8px !important;
    }
    .select2-search--dropdown .select2-search__field {
        font-size: 12px !important;
        padding: 4px 8px !important;
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
                        <h1 class="m-0 text-dark font-weight-bold">
                            <i class="fas fa-layer-group text-warning mr-2"></i>
                            Pecah Faktur Z Sekaligus (Batch Split)
                        </h1>
                        <p class="text-muted small mb-0 mt-1">
                            Memecah <strong><?= count($parent_fakturs) ?> Faktur Z Induk</strong> menjadi 
                            <strong><span id="labelTotalPecahan"><?= (int)$jumlah_pecah ?></span> Faktur Pecahan (Kode H)</strong>.
                        </p>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order/pecah_faktur') ?>">Pecah Faktur</a></li>
                            <li class="breadcrumb-item active">Batch Split</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Flash Messages -->
                <?php foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $cls): ?>
                    <?php if ($msg = $this->session->flashdata($key)): ?>
                        <div class="alert alert-<?= $cls ?> alert-dismissible fade show shadow-sm">
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-circle' : 'exclamation-triangle') ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (!empty($is_auto_prefilled)): ?>
                    <div class="alert alert-info border-info shadow-sm mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clipboard-check fa-2x text-info mr-3"></i>
                            <div>
                                <h6 class="font-weight-bold mb-1 text-dark">
                                    <i class="fas fa-magic text-warning mr-1"></i> Rancangan Pemecahan Otomatis Siap Diperiksa
                                </h6>
                                <div class="small text-dark">
                                    Sistem telah menyusun rancangan pembagian menjadi <strong><?= (int)$jumlah_pecah ?> Faktur Pecahan (Kode H)</strong> dengan batas maksimal <strong>Rp <?= number_format($max_plafon ?? 25000000, 0, ',', '.') ?></strong> per faktur. Barang antar Faktur Z <strong>terisolasi (tidak bercampur)</strong>.
                                    <br>
                                    <span class="text-primary font-weight-bold">
                                        <i class="fas fa-search mr-1"></i> Silakan periksa kembali kuantitas barang, customer penerima, dan nominal di bawah ini. Anda dapat mengeditnya sebelum menekan tombol simpan di bawah.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('sales_order/simpan_split_faktur_batch') ?>" method="post" id="formBatchSplit">
                    <!-- Hidden Parent IDs -->
                    <?php foreach ($parent_fakturs as $pid => $pf): ?>
                        <input type="hidden" name="parent_ids[]" value="<?= (int)$pid ?>">
                    <?php endforeach; ?>

                    <!-- ======================================================= -->
                    <!-- 1. BAGIAN ATAS: FAKTUR Z INDUK DIPILIH (FULL WIDTH)     -->
                    <!-- ======================================================= -->
                    <div class="card split-top-card card-outline card-primary shadow-sm mb-3">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold text-dark" style="font-size: 14px;">
                                <i class="fas fa-file-invoice mr-1 text-primary"></i> Faktur Z Induk Dipilih (<?= count($parent_fakturs) ?>)
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-primary mr-2"><?= count($parent_fakturs) ?> Faktur Terpilih</span>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-striped mb-0" style="font-size: 12.5px;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="40" class="text-center">No</th>
                                            <th>No. Faktur Z</th>
                                            <th>Tanggal</th>
                                            <th>No. SO</th>
                                            <th>Customer Asal</th>
                                            <th class="text-center">Total Item / Qty</th>
                                            <th class="text-right">Grand Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $p_no = 1; foreach ($parent_fakturs as $pf): ?>
                                            <tr>
                                                <td class="text-center align-middle text-muted font-weight-bold"><?= $p_no++ ?></td>
                                                <td class="align-middle">
                                                    <strong class="text-primary"><?= htmlspecialchars($pf['no_faktur']) ?></strong>
                                                </td>
                                                <td class="align-middle">
                                                    <?= !empty($pf['tanggal_faktur']) ? date('d/m/Y', strtotime($pf['tanggal_faktur'])) : '-' ?>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="font-weight-bold text-dark"><?= htmlspecialchars($pf['no_so']) ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="font-weight-bold"><?= htmlspecialchars($pf['customer_name']) ?></span>
                                                    <small class="text-muted">(<?= htmlspecialchars($pf['kd_customer'] ?? '-') ?>)</small>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="badge badge-light border">
                                                        <?= (int)($pf['total_barang'] ?? 0) ?> Item (<?= number_format((float)($pf['total_qty'] ?? 0)) ?> pcs)
                                                    </span>
                                                </td>
                                                <td class="text-right align-middle font-weight-bold text-dark">
                                                    Rp <?= number_format((float)($pf['grand_total'] ?? 0), 0, ',', '.') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ======================================================= -->
                    <!-- 2. BAGIAN MONITOR ALOKASI STOK BARANG (FULL WIDTH)       -->
                    <!-- ======================================================= -->
                    <div class="card split-top-card card-outline card-info shadow-sm mb-4">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold text-dark" style="font-size: 14px;">
                                <i class="fas fa-cubes mr-1 text-info"></i> Monitor Alokasi Stok Barang (<?= count($pool_items) ?> Item)
                            </h3>
                            <div class="card-tools d-flex align-items-center">
                                <span class="text-muted small mr-2 d-none d-md-inline">
                                    <i class="fas fa-info-circle text-info mr-1"></i> Pantau sisa stok agar tidak ada barang yang teralokasi berlebih (over)
                                </span>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered stock-monitor-compact-table mb-0">
                                    <thead>
                                        <tr>
                                            <th width="35" class="text-center">No</th>
                                            <th>Nama Barang</th>
                                            <th width="120">Kode Barang</th>
                                            <th width="130">No. Lot / Batch</th>
                                            <th width="160">Dari Faktur Induk</th>
                                            <th width="120" class="text-right">Stok Induk</th>
                                            <th width="130" class="text-right">Total Dialokasi</th>
                                            <th width="130" class="text-right">Sisa Belum Dibagi</th>
                                            <th width="140" class="text-center">Status Alokasi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stockMonitorList">
                                        <?php $m_no = 1; foreach ($pool_items as $d_id => $it): ?>
                                            <tr class="pool-stock-item" id="monitorItem_<?= $d_id ?>" data-item-id="<?= $d_id ?>" data-max-qty="<?= (float)$it['remaining_qty'] ?>">
                                                <td class="text-center text-muted font-weight-bold"><?= $m_no++ ?></td>
                                                <td>
                                                    <strong class="text-dark"><?= htmlspecialchars($it['nama_barang']) ?></strong>
                                                </td>
                                                <td><code><?= htmlspecialchars($it['kd_barang']) ?></code></td>
                                                <td><span class="badge badge-light border"><?= htmlspecialchars($it['no_lot'] ?: '-') ?></span></td>
                                                <td><span class="text-primary font-weight-bold"><?= htmlspecialchars($it['parent_no_faktur']) ?></span></td>
                                                <td class="text-right font-weight-bold text-secondary">
                                                    <?= number_format((float)$it['remaining_qty']) ?> pcs
                                                </td>
                                                <td class="text-right font-weight-bold text-dark val-allocated">
                                                    0 pcs
                                                </td>
                                                <td class="text-right font-weight-bold">
                                                    <span class="val-sisa text-success" style="font-size: 13px;">
                                                        <?= number_format((float)$it['remaining_qty']) ?>
                                                    </span> pcs
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-success px-2 py-1 monitor-sisa-badge" style="font-size: 11px;">
                                                        Tersedia
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

<?php
$total_induk_nilai = 0;
foreach ($parent_fakturs as $pf) {
    $total_induk_nilai += (float)($pf['grand_total'] ?? 0);
}
$total_pool_qty = isset($total_pool_qty) ? (float)$total_pool_qty : 0;
if ($total_pool_qty <= 0 && !empty($pool_items)) {
    foreach ($pool_items as $pi) {
        $total_pool_qty += (float)($pi['remaining_qty'] ?? 0);
    }
}
?>
                    <!-- ======================================================= -->
                    <!-- 3. BAGIAN SLOT FAKTUR PECAHAN (GRID 3 KOLOM PER BARIS)   -->
                    <!-- ======================================================= -->
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                        <div>
                            <h4 class="font-weight-bold text-dark mb-0" style="font-size: 17px;">
                                <i class="fas fa-cut text-warning mr-1"></i> Slot Faktur Pecahan (Kode Awalan H)
                            </h4>
                            <span class="text-muted small">
                                Tampilan 3 faktur pecahan per baris. Alokasikan kuantitas (QTY) barang ke masing-masing faktur pecahan (Kode Awalan H).
                            </span>
                        </div>
                        <div class="mt-2 mt-sm-0 d-flex align-items-center">
                            <button type="button" class="btn btn-outline-secondary btn-sm mr-1" id="btnExpandAll" title="Buka Rincian Semua Slot">
                                <i class="fas fa-expand-alt mr-1"></i> Buka Semua
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm mr-2" id="btnCollapseAll" title="Tutup Rincian Semua Slot">
                                <i class="fas fa-compress-alt mr-1"></i> Tutup Semua
                            </button>
                            <button type="button" class="btn btn-warning btn-sm font-weight-bold shadow-sm mr-1" id="btnAddSlot">
                                <i class="fas fa-plus mr-1"></i> Tambah Slot
                            </button>
                            <a href="<?= $back_url ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Batal
                            </a>
                        </div>
                    </div>

                    <!-- Panel Info Total Nilai & Fitur Bagi Rata Otomatis -->
                    <div class="card bg-light border shadow-sm mb-3">
                        <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center flex-wrap">
                            <div class="my-1">
                                <span class="text-muted small">Total Nilai Faktur Induk:</span>
                                <strong class="text-primary ml-1" style="font-size: 15px;">Rp <?= number_format($total_induk_nilai, 0, ',', '.') ?></strong>
                                <span class="mx-2 text-muted">&bull;</span>
                                <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 11.5px;" title="Harga barang pada Faktur H terdiskon 20%">
                                    <i class="fas fa-tag mr-1 text-danger"></i> Potongan Faktur H: 20% (Netto Rp <?= number_format($total_induk_nilai * 0.8, 0, ',', '.') ?>)
                                </span>
                                <span class="mx-2 text-muted">&bull;</span>
                                <span class="badge badge-info font-weight-bold" style="font-size: 11.5px;">
                                    <i class="fas fa-boxes mr-1"></i> Total Kuantitas: <?= number_format($total_pool_qty) ?> pcs
                                </span>
                            </div>
                            <div class="my-1 d-flex align-items-center">
                                <button type="button" class="btn btn-outline-info btn-sm font-weight-bold shadow-sm mr-2" id="btnToggleAllZeroQty" title="Sembunyikan atau tampilkan baris barang dengan kuantitas 0">
                                    <i class="fas fa-eye mr-1"></i> <span id="lblToggleAllZero">Tampilkan Semua Qty 0</span>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm mr-2" id="btnAutoDistribute" title="Bagi rata kuantitas seluruh barang secara seimbang ke seluruh slot pecahan">
                                    <i class="fas fa-magic mr-1"></i> Bagi Rata Qty Otomatis
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetQty" title="Kosongkan semua input kuantitas">
                                    <i class="fas fa-undo mr-1"></i> Kosongkan Qty
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Container Grid Slot Pecahan (3 Kolom: col-lg-4 col-md-6 col-12) -->
                    <div class="row" id="slotsContainer">
                        <?php for ($slot_idx = 0; $slot_idx < $jumlah_pecah; $slot_idx++): ?>
                            <?php 
                            $slot_no = $slot_idx + 1; 
                            // Tentukan customer penerima dari pre_allocated_splits atau urutan prioritas
                            $pre_slot = $pre_allocated_splits[$slot_idx] ?? null;
                            $auto_cust_kd = $pre_slot['kd_customer'] ?? ($customers[$slot_idx]['kd_customer'] ?? '');
                            $slot_parent_no = $pre_slot['parent_no_faktur'] ?? '';

                            // Cari objek customer
                            $auto_c = null;
                            foreach ($customers as $c_item) {
                                if ($c_item['kd_customer'] === $auto_cust_kd) {
                                    $auto_c = $c_item;
                                    break;
                                }
                            }
                            if (!$auto_c && !empty($customers[$slot_idx])) {
                                $auto_c = $customers[$slot_idx];
                                $auto_cust_kd = $auto_c['kd_customer'];
                            }

                            $auto_display_name = '';
                            if ($auto_c) {
                                if (!empty($is_customer_acak)) {
                                    $auto_display_name = $auto_c['kontak_person'] . ' (' . $auto_c['kd_customer'] . ')';
                                } else {
                                    $auto_display_name = $auto_c['nama_customer'] . ' (' . $auto_c['kd_customer'] . ')';
                                }
                            }
                            ?>
                            <div class="col-lg-4 col-md-6 col-12 mb-3 slot-column" id="slotCard_<?= $slot_idx ?>" data-slot-idx="<?= $slot_idx ?>">
                                <div class="card split-slot-card shadow-sm">
                                    <!-- Header Slot -->
                                    <div class="split-slot-header d-flex justify-content-between align-items-center" data-toggle="collapse" data-target="#slotCollapse_<?= $slot_idx ?>">
                                        <div class="d-flex align-items-center text-truncate mr-2">
                                            <span class="badge badge-warning badge-faktur-h mr-1">
                                                #<?= $slot_no ?> (H)
                                            </span>
                                            <?php if (!empty($slot_parent_no)): ?>
                                                <span class="badge badge-info mr-1" style="font-size: 10px;" title="Faktur Z Induk: <?= htmlspecialchars($slot_parent_no) ?>">
                                                    <i class="fas fa-file-invoice mr-1"></i><?= htmlspecialchars($slot_parent_no) ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="font-weight-bold text-dark small text-truncate slot-customer-display" id="slotCustDisplay_<?= $slot_idx ?>">
                                                <?php if (!empty($auto_display_name)): ?>
                                                    <i class="fas fa-store text-success mr-1"></i> <?= htmlspecialchars($auto_display_name) ?>
                                                <?php else: ?>
                                                    (Pilih Customer)
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center flex-shrink-0">
                                            <span class="badge badge-light border mr-1 font-weight-bold slot-total-nominal" id="slotTotalNominal_<?= $slot_idx ?>" style="font-size: 11px;">
                                                Rp 0
                                            </span>
                                            <button type="button" class="btn btn-tool text-danger btn-delete-slot p-1" data-slot-idx="<?= $slot_idx ?>" title="Hapus slot ini" <?= $jumlah_pecah <= 1 ? 'style="display:none;"' : '' ?>>
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <button type="button" class="btn btn-tool p-1">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Body Slot -->
                                    <div class="collapse show" id="slotCollapse_<?= $slot_idx ?>">
                                        <div class="card-body p-2">
                                            <!-- Form Customer & Tanggal (Kompak) -->
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold mb-1 d-flex justify-content-between align-items-center" style="font-size: 11px;">
                                                    <span>Customer Penerima <span class="text-danger">*</span></span>
                                                    <?php if (!empty($is_customer_acak)): ?>
                                                        <span class="badge badge-success px-1 py-0" style="font-size: 9.5px;" title="Terkunci pada kios induk Faktur Z & tersortir dari beban terendah">
                                                            <i class="fas fa-balance-scale mr-1"></i> Prioritas Kios: <?= htmlspecialchars($kios_induk_names) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </label>
                                                <select name="splits[<?= $slot_idx ?>][kd_customer]" class="form-control form-control-sm select-customer" data-slot-idx="<?= $slot_idx ?>" required style="font-size: 12px; height: 30px;">
                                                    <option value="">-- Pilih Customer Penerima (<?= count($customers) ?> Kontak) --</option>
                                                    <?php foreach ($customers as $c_idx => $c): ?>
                                                        <?php 
                                                        $is_selected      = ($c['kd_customer'] === $auto_cust_kd);
                                                        $nominal_riwayat  = (float)($c['total_nominal_pecah'] ?? 0);
                                                        $faktur_riwayat   = (int)($c['total_faktur_pecah'] ?? 0);
                                                        $is_limit_reached = !empty($c['is_limit_reached']) || ($nominal_riwayat >= 250000000);
                                                        $sisa_limit       = max(0.0, 250000000 - $nominal_riwayat);

                                                        if ($is_limit_reached) {
                                                            $badge_info = 'LIMIT PENUH: Rp ' . number_format($nominal_riwayat, 0, ',', '.') . ' (Maksimal 250 Jt - Tidak Dapat Digunakan)';
                                                        } elseif ($nominal_riwayat <= 0 && $faktur_riwayat <= 0) {
                                                            $badge_info = 'Sisa Kuota: Rp 250.000.000 (Prioritas ' . ($c_idx + 1) . ')';
                                                        } else {
                                                            $badge_info = 'Riwayat: Rp ' . number_format($nominal_riwayat, 0, ',', '.') . ' | Sisa: Rp ' . number_format($sisa_limit, 0, ',', '.');
                                                        }
                                                        ?>
                                                        <?php if (!empty($is_customer_acak)): ?>
                                                            <option value="<?= htmlspecialchars($c['kd_customer']) ?>" <?= $is_selected ? 'selected' : '' ?> <?= $is_limit_reached ? 'disabled class="text-danger font-weight-bold"' : '' ?>>
                                                                [<?= htmlspecialchars($c['kd_customer']) ?>] <?= htmlspecialchars($c['kontak_person']) ?> (<?= htmlspecialchars($c['nama_toko']) ?><?= !empty($c['kota']) ? ' - '.$c['kota'] : '' ?>) - [<?= $badge_info ?>]
                                                            </option>
                                                        <?php else: ?>
                                                            <option value="<?= htmlspecialchars($c['kd_customer']) ?>" <?= $is_selected ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($c['nama_customer']) ?> (<?= htmlspecialchars($c['kd_customer']) ?>)
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label class="small font-weight-bold mb-0" style="font-size: 10.5px;">Tgl Faktur</label>
                                                    <input type="date" name="splits[<?= $slot_idx ?>][tanggal_faktur]" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" style="font-size: 11px; height: 28px;">
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="small font-weight-bold mb-0" style="font-size: 10.5px;">Jatuh Tempo</label>
                                                    <input type="date" name="splits[<?= $slot_idx ?>][tanggal_jatuh_tempo]" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" style="font-size: 11px; height: 28px;">
                                                </div>
                                            </div>

                                            <div class="form-group mb-2">
                                                <input type="text" name="splits[<?= $slot_idx ?>][catatan]" class="form-control form-control-sm" placeholder="Catatan opsional..." style="font-size: 11px; height: 28px;">
                                            </div>

                                            <!-- Tabel Input Alokasi Barang (Harga Satuan Terdiskon 20%) -->
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm table-split-items-compact mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Barang & Sumber</th>
                                                            <th width="80" class="text-right">Qty (Pcs)</th>
                                                            <th width="115" class="text-right">Harga (Disc 20%)</th>
                                                            <th width="90" class="text-right">Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $has_pos_qty = false;
                                                        $zero_qty_count = 0;
                                                        foreach ($pool_items as $check_id => $check_it) {
                                                            $q = isset($pre_allocated_splits[$slot_idx]['items'][$check_id]['qty']) ? (float)$pre_allocated_splits[$slot_idx]['items'][$check_id]['qty'] : 0;
                                                            if ($q > 0.0001) {
                                                                $has_pos_qty = true;
                                                            } else {
                                                                $zero_qty_count++;
                                                            }
                                                        }
                                                        ?>
                                                        <?php foreach ($pool_items as $d_id => $it): ?>
                                                            <?php 
                                                            $hrg_asli = (float)$it['hrg_satuan'];
                                                            $default_hrg = round($hrg_asli * 0.8, 2);
                                                            $default_disc = (float)($it['disc'] ?? 0);
                                                            $default_pajak = (float)($it['pajak'] ?? 0);
                                                            $val_qty = isset($pre_allocated_splits[$slot_idx]['items'][$d_id]['qty']) ? (float)$pre_allocated_splits[$slot_idx]['items'][$d_id]['qty'] : 0;
                                                            $is_zero = ($val_qty <= 0.0001);
                                                            $hide_row = $has_pos_qty && $is_zero;
                                                            ?>
                                                            <tr class="row-item-split <?= $is_zero ? 'row-zero-qty' : '' ?>" data-detail-id="<?= $d_id ?>" data-hrg="<?= $default_hrg ?>" <?= $hide_row ? 'style="display: none;"' : '' ?>>
                                                                <td>
                                                                    <strong class="text-dark d-block text-truncate" style="max-width: 140px;" title="<?= htmlspecialchars($it['nama_barang']) ?>">
                                                                        <?= htmlspecialchars($it['nama_barang']) ?>
                                                                    </strong>
                                                                    <small class="text-muted d-block" style="font-size: 10px;">
                                                                        Lot: <?= htmlspecialchars($it['no_lot'] ?: '-') ?> | Dari: <?= htmlspecialchars($it['parent_no_faktur']) ?>
                                                                    </small>
                                                                </td>
                                                                <td class="text-right align-middle p-1">
                                                                    <?php 
                                                                    $val_qty = isset($pre_allocated_splits[$slot_idx]['items'][$d_id]['qty']) ? (float)$pre_allocated_splits[$slot_idx]['items'][$d_id]['qty'] : 0;
                                                                    ?>
                                                                    <input type="number" 
                                                                           name="splits[<?= $slot_idx ?>][items][<?= $d_id ?>][qty]" 
                                                                           class="form-control form-control-sm input-qty-split input-calc" 
                                                                           min="0" 
                                                                           max="<?= (float)$it['remaining_qty'] ?>" 
                                                                           step="any"
                                                                           value="<?= $val_qty > 0 ? (float)$val_qty : 0 ?>" 
                                                                           data-detail-id="<?= $d_id ?>"
                                                                           data-slot-idx="<?= $slot_idx ?>">
                                                                </td>
                                                                <td class="text-right align-middle p-1 text-muted font-weight-bold" style="font-size: 11px;">
                                                                    <span class="text-success font-weight-bold">Rp <?= number_format($default_hrg, 0, ',', '.') ?></span>
                                                                    <br>
                                                                    <small class="text-muted" style="text-decoration: line-through; font-size: 9.5px;">Rp <?= number_format($hrg_asli, 0, ',', '.') ?></small>
                                                                    <span class="badge badge-danger px-1 py-0" style="font-size: 8.5px;">-20%</span>
                                                                    <input type="hidden" 
                                                                           name="splits[<?= $slot_idx ?>][items][<?= $d_id ?>][hrg_satuan]" 
                                                                           class="input-hrg-split input-calc" 
                                                                           value="<?= $default_hrg ?>" 
                                                                           data-detail-id="<?= $d_id ?>"
                                                                           data-slot-idx="<?= $slot_idx ?>">
                                                                    <input type="hidden" name="splits[<?= $slot_idx ?>][items][<?= $d_id ?>][disc]" value="<?= $default_disc ?>">
                                                                    <input type="hidden" name="splits[<?= $slot_idx ?>][items][<?= $d_id ?>][pajak]" value="<?= $default_pajak ?>">
                                                                </td>
                                                                <td class="text-right align-middle font-weight-bold text-success cell-subtotal p-1" id="subtotal_<?= $slot_idx ?>_<?= $d_id ?>">
                                                                    Rp 0
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <div class="px-2 py-1 bg-light border-top text-center slot-zero-toggle-box" style="<?= ($has_pos_qty && $zero_qty_count > 0) ? '' : 'display: none;' ?>">
                                                    <a href="javascript:void(0)" class="btn-toggle-slot-zero small text-muted font-weight-bold" style="font-size: 10.5px; text-decoration: none;">
                                                        <span class="lbl-zero-text"><i class="fas fa-plus-circle text-primary mr-1"></i> + Tampilkan <?= $zero_qty_count ?> barang lainnya (Qty 0)</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Tombol Aksi Simpan Semua -->
                    <div class="card card-outline card-success shadow-sm mb-4 mt-2">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap py-3">
                            <div>
                                <span class="font-weight-bold text-dark d-block" style="font-size: 15px;">
                                    <i class="fas fa-check-double text-success mr-1"></i> Siap Menerbitkan Seluruh Faktur Pecahan H?
                                </span>
                                <span class="text-muted small">
                                    Faktur pecahan tersimpan di tabel khusus tanpa mempengaruhi kartu stok dan tanpa penjurnalan ulang.
                                </span>
                            </div>
                            <div class="mt-2 mt-sm-0">
                                <a href="<?= $back_url ?>" class="btn btn-secondary mr-1">
                                    <i class="fas fa-times mr-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-success font-weight-bold shadow-sm px-4" id="btnSubmitBatch">
                                    <i class="fas fa-save mr-1"></i> Simpan & Terbitkan Semua Faktur H
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>

<!-- Template HTML untuk penambahan slot baru via JavaScript (Grid 3 Kolom) -->
<template id="slotTemplate">
    <div class="col-lg-4 col-md-6 col-12 mb-3 slot-column" id="slotCard_{{IDX}}" data-slot-idx="{{IDX}}">
        <div class="card split-slot-card shadow-sm">
            <div class="split-slot-header d-flex justify-content-between align-items-center" data-toggle="collapse" data-target="#slotCollapse_{{IDX}}">
                <div class="d-flex align-items-center text-truncate mr-2">
                    <span class="badge badge-warning badge-faktur-h mr-1">
                        #{{SLOT_NO}} (H)
                    </span>
                    <span class="font-weight-bold text-dark small text-truncate slot-customer-display" id="slotCustDisplay_{{IDX}}">
                        (Pilih Customer)
                    </span>
                </div>
                <div class="d-flex align-items-center flex-shrink-0">
                    <span class="badge badge-light border mr-1 font-weight-bold slot-total-nominal" id="slotTotalNominal_{{IDX}}" style="font-size: 11px;">
                        Rp 0
                    </span>
                    <button type="button" class="btn btn-tool text-danger btn-delete-slot p-1" data-slot-idx="{{IDX}}" title="Hapus slot ini">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button type="button" class="btn btn-tool p-1">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>

            <div class="collapse show" id="slotCollapse_{{IDX}}">
                <div class="card-body p-2">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold mb-1 d-flex justify-content-between align-items-center" style="font-size: 11px;">
                            <span>Customer Penerima <span class="text-danger">*</span></span>
                            <?php if (!empty($is_customer_acak)): ?>
                                <span class="badge badge-success px-1 py-0" style="font-size: 9.5px;" title="Terkunci pada kios induk Faktur Z">
                                    <i class="fas fa-lock mr-1"></i> Kios: <?= htmlspecialchars($kios_induk_names) ?>
                                </span>
                            <?php endif; ?>
                        </label>
                        <select name="splits[{{IDX}}][kd_customer]" class="form-control form-control-sm select-customer" data-slot-idx="{{IDX}}" required style="font-size: 12px; height: 30px;">
                            <option value="">-- Pilih Customer Penerima (<?= count($customers) ?> Kontak) --</option>
                            <?php foreach ($customers as $c_idx => $c): ?>
                                <?php 
                                $nominal_riwayat  = (float)($c['total_nominal_pecah'] ?? 0);
                                $faktur_riwayat   = (int)($c['total_faktur_pecah'] ?? 0);
                                $is_limit_reached = !empty($c['is_limit_reached']) || ($nominal_riwayat >= 250000000);
                                $sisa_limit       = max(0.0, 250000000 - $nominal_riwayat);

                                if ($is_limit_reached) {
                                    $badge_info = 'LIMIT PENUH: Rp ' . number_format($nominal_riwayat, 0, ',', '.') . ' (Maksimal 250 Jt - Tidak Dapat Digunakan)';
                                } elseif ($nominal_riwayat <= 0 && $faktur_riwayat <= 0) {
                                    $badge_info = 'Sisa Kuota: Rp 250.000.000 (Prioritas ' . ($c_idx + 1) . ')';
                                } else {
                                    $badge_info = 'Riwayat: Rp ' . number_format($nominal_riwayat, 0, ',', '.') . ' | Sisa: Rp ' . number_format($sisa_limit, 0, ',', '.');
                                }
                                ?>
                                <?php if (!empty($is_customer_acak)): ?>
                                    <option value="<?= htmlspecialchars($c['kd_customer']) ?>" <?= $is_limit_reached ? 'disabled class="text-danger font-weight-bold"' : '' ?>>
                                        [<?= htmlspecialchars($c['kd_customer']) ?>] <?= htmlspecialchars($c['kontak_person']) ?> (<?= htmlspecialchars($c['nama_toko']) ?><?= !empty($c['kota']) ? ' - '.$c['kota'] : '' ?>) - [<?= $badge_info ?>]
                                    </option>
                                <?php else: ?>
                                    <option value="<?= htmlspecialchars($c['kd_customer']) ?>">
                                        <?= htmlspecialchars($c['nama_customer']) ?> (<?= htmlspecialchars($c['kd_customer']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="small font-weight-bold mb-0" style="font-size: 10.5px;">Tgl Faktur</label>
                            <input type="date" name="splits[{{IDX}}][tanggal_faktur]" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" style="font-size: 11px; height: 28px;">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="small font-weight-bold mb-0" style="font-size: 10.5px;">Jatuh Tempo</label>
                            <input type="date" name="splits[{{IDX}}][tanggal_jatuh_tempo]" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" style="font-size: 11px; height: 28px;">
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <input type="text" name="splits[{{IDX}}][catatan]" class="form-control form-control-sm" placeholder="Catatan opsional..." style="font-size: 11px; height: 28px;">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-split-items-compact mb-0">
                            <thead>
                                <tr>
                                    <th>Barang & Sumber</th>
                                    <th width="80" class="text-right">Qty (Pcs)</th>
                                    <th width="115" class="text-right">Harga (Disc 20%)</th>
                                    <th width="90" class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pool_items as $d_id => $it): ?>
                                    <?php 
                                    $hrg_asli = (float)$it['hrg_satuan'];
                                    $default_hrg = round($hrg_asli * 0.8, 2);
                                    $default_disc = (float)($it['disc'] ?? 0);
                                    $default_pajak = (float)($it['pajak'] ?? 0);
                                    ?>
                                    <tr class="row-item-split" data-detail-id="<?= $d_id ?>" data-hrg="<?= $default_hrg ?>">
                                        <td>
                                            <strong class="text-dark d-block text-truncate" style="max-width: 140px;" title="<?= htmlspecialchars($it['nama_barang']) ?>">
                                                <?= htmlspecialchars($it['nama_barang']) ?>
                                            </strong>
                                            <small class="text-muted d-block" style="font-size: 10px;">
                                                Lot: <?= htmlspecialchars($it['no_lot'] ?: '-') ?> | Dari: <?= htmlspecialchars($it['parent_no_faktur']) ?>
                                            </small>
                                        </td>
                                        <td class="text-right align-middle p-1">
                                            <input type="number" 
                                                   name="splits[{{IDX}}][items][<?= $d_id ?>][qty]" 
                                                   class="form-control form-control-sm input-qty-split input-calc" 
                                                   min="0" 
                                                   max="<?= (float)$it['remaining_qty'] ?>" 
                                                   step="any"
                                                   value="0" 
                                                   data-detail-id="<?= $d_id ?>"
                                                   data-slot-idx="{{IDX}}">
                                        </td>
                                        <td class="text-right align-middle p-1 text-muted font-weight-bold" style="font-size: 11px;">
                                            <span class="text-success font-weight-bold">Rp <?= number_format($default_hrg, 0, ',', '.') ?></span>
                                            <br>
                                            <small class="text-muted" style="text-decoration: line-through; font-size: 9.5px;">Rp <?= number_format($hrg_asli, 0, ',', '.') ?></small>
                                            <span class="badge badge-danger px-1 py-0" style="font-size: 8.5px;">-20%</span>
                                            <input type="hidden" 
                                                   name="splits[{{IDX}}][items][<?= $d_id ?>][hrg_satuan]" 
                                                   class="input-hrg-split input-calc" 
                                                   value="<?= $default_hrg ?>" 
                                                   data-detail-id="<?= $d_id ?>"
                                                   data-slot-idx="{{IDX}}">
                                            <input type="hidden" name="splits[{{IDX}}][items][<?= $d_id ?>][disc]" value="<?= $default_disc ?>">
                                            <input type="hidden" name="splits[{{IDX}}][items][<?= $d_id ?>][pajak]" value="<?= $default_pajak ?>">
                                        </td>
                                        <td class="text-right align-middle font-weight-bold text-success cell-subtotal p-1" id="subtotal_{{IDX}}_<?= $d_id ?>">
                                            Rp 0
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="px-2 py-1 bg-light border-top text-center slot-zero-toggle-box" style="display: none;">
                            <a href="javascript:void(0)" class="btn-toggle-slot-zero small text-muted font-weight-bold" style="font-size: 10.5px; text-decoration: none;">
                                <span class="lbl-zero-text"><i class="fas fa-plus-circle text-primary mr-1"></i> + Tampilkan barang lainnya (Qty 0)</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
$(document).ready(function() {
    var currentSlotCount = <?= (int)$jumlah_pecah ?>;
    var availableCustomers = <?= json_encode($customers) ?>;
    var isCustomerAcak = <?= !empty($is_customer_acak) ? 'true' : 'false' ?>;

    // Inisialisasi Select2 Searchable untuk Customer Penerima
    function initCustomerSelect2($elements) {
        if (!$.fn.select2) return;
        $elements.each(function() {
            var $el = $(this);
            if ($el.hasClass('select2-hidden-accessible')) {
                return;
            }
            $el.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: '-- Cari & Pilih Customer --',
                language: {
                    noResults: function() {
                        return "Tidak ada customer ditemukan";
                    }
                }
            });
        });
    }

    // Inisialisasi Select2 untuk semua slot yang ada saat halaman dimuat
    initCustomerSelect2($('.select-customer'));

    var hideZeroQtyActive = true;

    // Helper untuk update visibilitas baris dengan kuantitas 0 pada slot card
    function updateSlotZeroRows($card) {
        var $rows = $card.find('.row-item-split');
        var positiveCount = 0;
        var zeroCount = 0;

        $rows.each(function() {
            var qty = parseFloat($(this).find('.input-qty-split').val()) || 0;
            if (qty > 0.0001) {
                positiveCount++;
                $(this).removeClass('row-zero-qty');
            } else {
                zeroCount++;
                $(this).addClass('row-zero-qty');
            }
        });

        var isSlotExpanded = $card.data('show-zero') === true;
        var $toggleBox = $card.find('.slot-zero-toggle-box');

        if (hideZeroQtyActive && !isSlotExpanded) {
            if (positiveCount > 0) {
                $rows.each(function() {
                    var qty = parseFloat($(this).find('.input-qty-split').val()) || 0;
                    var isFocused = $(this).find('.input-qty-split').is(':focus');
                    if (qty <= 0.0001 && !isFocused) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
                if (zeroCount > 0) {
                    $toggleBox.show();
                    $toggleBox.find('.lbl-zero-text').html('<i class="fas fa-plus-circle text-primary mr-1"></i> + Tampilkan ' + zeroCount + ' barang lainnya (Qty 0)');
                } else {
                    $toggleBox.hide();
                }
            } else {
                // Jika semua barang masih 0 (slot kosong), tampilkan semua agar user bisa input
                $rows.show();
                $toggleBox.hide();
            }
        } else {
            // Tampilkan semua baris
            $rows.show();
            if (zeroCount > 0 && positiveCount > 0) {
                $toggleBox.show();
                $toggleBox.find('.lbl-zero-text').html('<i class="fas fa-minus-circle text-warning mr-1"></i> - Sembunyikan barang Qty 0 (' + zeroCount + ')');
            } else {
                $toggleBox.hide();
            }
        }
    }

    // Toggle visibilitas baris Qty 0 per slot
    $(document).on('click', '.btn-toggle-slot-zero', function(e) {
        e.preventDefault();
        var $card = $(this).closest('.split-slot-card');
        var current = $card.data('show-zero') === true;
        $card.data('show-zero', !current);
        updateSlotZeroRows($card);
    });

    // Toggle global di toolbar
    $('#btnToggleAllZeroQty').on('click', function() {
        hideZeroQtyActive = !hideZeroQtyActive;
        if (hideZeroQtyActive) {
            $(this).removeClass('btn-info text-white').addClass('btn-outline-info');
            $('#lblToggleAllZero').text('Tampilkan Semua Qty 0');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
            $('.split-slot-card').data('show-zero', false);
        } else {
            $(this).removeClass('btn-outline-info').addClass('btn-info text-white');
            $('#lblToggleAllZero').text('Sembunyikan Qty 0');
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
            $('.split-slot-card').data('show-zero', true);
        }
        $('.split-slot-card').each(function() {
            updateSlotZeroRows($(this));
        });
    });

    // Saat input kuantitas kehilangan fokus, sembunyikan kembali jika kuantitasnya 0 dan filter aktif
    $(document).on('blur', '.input-qty-split', function() {
        var $card = $(this).closest('.split-slot-card');
        updateSlotZeroRows($card);
    });

    // Helper untuk mencari customer prioritas berikutnya yang belum terpilih di slot mana pun
    function getNextAvailableCustomer() {
        var selectedKodes = {};
        $('.select-customer').each(function() {
            var val = $(this).val();
            if (val) {
                selectedKodes[val] = true;
            }
        });

        for (var i = 0; i < availableCustomers.length; i++) {
            var c = availableCustomers[i];
            var isLimit = (c.is_limit_reached == 1 || c.is_limit_reached === true || (parseFloat(c.total_nominal_pecah) || 0) >= 250000000);
            if (!selectedKodes[c.kd_customer] && !isLimit) {
                return c;
            }
        }
        for (var j = 0; j < availableCustomers.length; j++) {
            var c2 = availableCustomers[j];
            var isLimit2 = (c2.is_limit_reached == 1 || c2.is_limit_reached === true || (parseFloat(c2.total_nominal_pecah) || 0) >= 250000000);
            if (!isLimit2) {
                return c2;
            }
        }
        return availableCustomers.length > 0 ? availableCustomers[0] : null;
    }

    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Fungsi bagi rata kuantitas seluruh barang ke semua slot aktif secara seimbang
    function autoDistributeAll() {
        var $slots = $('.slot-column');
        var numSlots = $slots.length;
        if (numSlots === 0) return;

        $('.pool-stock-item').each(function() {
            var dId = $(this).data('item-id');
            var maxQty = parseFloat($(this).data('max-qty')) || 0;
            if (maxQty <= 0) return;

            var isInteger = (Math.floor(maxQty) === maxQty);

            if (isInteger) {
                var baseQty = Math.floor(maxQty / numSlots);
                var remainder = maxQty % numSlots;

                $slots.each(function(idx) {
                    var qtyInput = $(this).find('.row-item-split[data-detail-id="' + dId + '"] .input-qty-split');
                    // Distribusikan sisa kuantitas secara seimbang ke slot awal
                    var qtyToSet = baseQty + (idx < remainder ? 1 : 0);
                    qtyInput.val(qtyToSet > 0 ? qtyToSet : 0);
                });
            } else {
                var baseQty = parseFloat((maxQty / numSlots).toFixed(2));
                var remainder = parseFloat((maxQty - (baseQty * numSlots)).toFixed(2));

                $slots.each(function(idx) {
                    var qtyInput = $(this).find('.row-item-split[data-detail-id="' + dId + '"] .input-qty-split');
                    var qtyToSet = (idx === numSlots - 1) ? parseFloat((baseQty + remainder).toFixed(2)) : baseQty;
                    qtyInput.val(qtyToSet > 0 ? qtyToSet : 0);
                });
            }
        });

        recalculateAll();
    }

    // Fungsi reset kuantitas semua slot menjadi 0
    function resetAllQty() {
        $('.input-qty-split').val(0);
        recalculateAll();
    }

    // Tombol aksi Bagi Rata & Kosongkan Qty
    $('#btnAutoDistribute').on('click', function() {
        autoDistributeAll();
    });

    $('#btnResetQty').on('click', function() {
        if (confirm('Kosongkan semua input kuantitas barang di seluruh faktur pecahan?')) {
            resetAllQty();
        }
    });

    // Hitung subtotal dan kalkulasi alokasi stok pool secara realtime
    function recalculateAll() {
        var poolAllocations = {};
        
        // Inisialisasi total per item
        $('.pool-stock-item').each(function() {
            var dId = $(this).data('item-id');
            poolAllocations[dId] = 0;
        });

        // Loop setiap slot card
        $('.split-slot-card').each(function() {
            var $slotCol = $(this).closest('.slot-column');
            var slotIdx = $slotCol.data('slot-idx');
            var slotTotal = 0;

            $(this).find('.row-item-split').each(function() {
                var dId = $(this).data('detail-id');
                var qtyInput = $(this).find('.input-qty-split');
                var hrgInput = $(this).find('.input-hrg-split');

                var qty = parseFloat(qtyInput.val()) || 0;
                var hrg = parseFloat(hrgInput.val()) || 0;

                var subtotal = qty * hrg;
                slotTotal += subtotal;

                // Update teks subtotal cell
                $('#subtotal_' + slotIdx + '_' + dId).text(formatRupiah(subtotal));

                if (!poolAllocations[dId]) poolAllocations[dId] = 0;
                poolAllocations[dId] += qty;
            });

            // Update baris zero-qty pada slot ini
            updateSlotZeroRows($(this));

            // Update badge total per slot
            var $totalBadge = $('#slotTotalNominal_' + slotIdx);
            $totalBadge.removeClass('badge-light badge-success badge-danger text-danger text-white border-danger');

            if (slotTotal > 0) {
                $totalBadge.addClass('badge-success text-white').text(formatRupiah(slotTotal));
            } else {
                $totalBadge.addClass('badge-light border').text(formatRupiah(0));
            }
        });

        // Update indikator stok pada tabel monitor horizontal
        var hasOverAllocation = false;
        $('.pool-stock-item').each(function() {
            var dId = $(this).data('item-id');
            var maxQty = parseFloat($(this).data('max-qty')) || 0;
            var allocated = poolAllocations[dId] || 0;
            var sisa = maxQty - allocated;

            var $itemEl = $(this);
            var $allocEl = $itemEl.find('.val-allocated');
            var $sisaEl = $itemEl.find('.val-sisa');
            var $badge = $itemEl.find('.monitor-sisa-badge');

            $allocEl.text(allocated.toLocaleString('id-ID') + ' pcs');
            $sisaEl.text(sisa.toLocaleString('id-ID'));

            $itemEl.removeClass('table-success table-warning table-danger');
            $badge.removeClass('badge-success badge-primary badge-danger');

            if (sisa < -0.001) {
                // Over allocation (alokasi melebihi stok yang ada)
                hasOverAllocation = true;
                $itemEl.addClass('table-danger');
                $sisaEl.removeClass('text-success text-primary').addClass('text-danger');
                $badge.addClass('badge-danger').html('<i class="fas fa-exclamation-triangle mr-1"></i> Lebih ' + Math.abs(sisa).toLocaleString('id-ID') + ' pcs');
            } else if (Math.abs(sisa) <= 0.001) {
                // Pas habis teralokasi
                $itemEl.addClass('table-success');
                $sisaEl.removeClass('text-danger').addClass('text-primary');
                $badge.addClass('badge-primary').html('<i class="fas fa-check-circle mr-1"></i> Habis Pas');
            } else {
                // Masih ada sisa
                $sisaEl.removeClass('text-danger text-primary').addClass('text-success');
                $badge.addClass('badge-success').html('<i class="fas fa-info-circle mr-1"></i> Sisa ' + sisa.toLocaleString('id-ID') + ' pcs');
            }
        });

        if (hasOverAllocation) {
            $('#btnSubmitBatch').prop('disabled', true).addClass('btn-danger').removeClass('btn-success');
        } else {
            $('#btnSubmitBatch').prop('disabled', false).addClass('btn-success').removeClass('btn-danger');
        }
    }

    // Event listener input angka kuantitas
    $(document).on('input', '.input-calc', function() {
        recalculateAll();
    });

    // Update customer display header
    $(document).on('change', '.select-customer', function() {
        var slotIdx = $(this).data('slot-idx');
        var val = $(this).val();
        if (val) {
            var selectedText = $(this).find('option:selected').text();
            var parts = selectedText.split(' - [');
            var mainTitle = parts[0] ? parts[0].trim() : selectedText;
            $('#slotCustDisplay_' + slotIdx).html('<i class="fas fa-store text-success mr-1"></i> ' + mainTitle);
        } else {
            $('#slotCustDisplay_' + slotIdx).text('(Pilih Customer)');
        }
    });

    // Tambah slot pecahan baru (Grid 3 Kolom)
    $('#btnAddSlot').on('click', function() {
        var template = $('#slotTemplate').html();
        var nextIdx = currentSlotCount++;
        var slotNo = $('.slot-column').length + 1;

        var html = template.replace(/\{\{IDX\}\}/g, nextIdx).replace(/\{\{SLOT_NO\}\}/g, slotNo);
        var $newSlot = $(html);
        $('#slotsContainer').append($newSlot);

        // Inisialisasi Select2 Searchable pada slot baru
        var $select = $newSlot.find('.select-customer');
        initCustomerSelect2($select);

        // Otomatis pilihkan customer prioritas berikutnya yang belum terpilih
        var nextCust = getNextAvailableCustomer();
        if (nextCust) {
            $select.val(nextCust.kd_customer).trigger('change');
            var dispText = isCustomerAcak 
                ? (nextCust.kontak_person + ' (' + nextCust.kd_customer + ')') 
                : (nextCust.nama_customer + ' (' + nextCust.kd_customer + ')');
            $newSlot.find('#slotCustDisplay_' + nextIdx).html('<i class="fas fa-store text-success mr-1"></i> ' + dispText);
        }

        // Update tombol delete di semua slot
        if ($('.slot-column').length > 1) {
            $('.btn-delete-slot').show();
        }

        recalculateAll();

        // Scroll halus ke slot baru
        $('html, body').animate({
            scrollTop: $('#slotCard_' + nextIdx).offset().top - 80
        }, 300);
    });

    // Hapus slot pecahan
    $(document).on('click', '.btn-delete-slot', function(e) {
        e.stopPropagation();
        if ($('.slot-column').length <= 1) {
            alert('Minimal harus ada 1 slot pecahan.');
            return;
        }

        if (confirm('Yakin ingin menghapus slot pecahan ini?')) {
            var slotIdx = $(this).data('slot-idx');
            $('#slotCard_' + slotIdx).fadeOut(200, function() {
                $(this).remove();
                if ($('.slot-column').length <= 1) {
                    $('.btn-delete-slot').hide();
                }
                recalculateAll();
            });
        }
    });

    // Buka / Tutup semua slot
    $('#btnExpandAll').on('click', function() {
        $('.split-slot-card .collapse').collapse('show');
    });
    $('#btnCollapseAll').on('click', function() {
        $('.split-slot-card .collapse').collapse('hide');
    });

    // Validasi form sebelum submit
    $('#formBatchSplit').on('submit', function(e) {
        var valid = true;
        var hasAtLeastOneCust = false;
        var hasAtLeastOneItem = false;

        $('.slot-column').each(function() {
            var custVal = $(this).find('.select-customer').val();
            var hasSlotItem = false;

            $(this).find('.row-item-split').each(function() {
                var q = parseFloat($(this).find('.input-qty-split').val()) || 0;
                if (q > 0) {
                    hasSlotItem = true;
                    hasAtLeastOneItem = true;
                }
            });

            if (hasSlotItem) {
                if (!custVal) {
                    alert('Salah satu slot pecahan memiliki alokasi barang namun belum memilih Customer!');
                    valid = false;
                    return false;
                }
                hasAtLeastOneCust = true;
            }
        });

        if (!valid) {
            e.preventDefault();
            return false;
        }

        if (!hasAtLeastOneCust || !hasAtLeastOneItem) {
            e.preventDefault();
            alert('Harap alokasikan minimal 1 barang dan tentukan customer untuk diproses!');
            return false;
        }

        return confirm('Konfirmasi: Terbitkan seluruh Faktur Pecahan (Kode H) sekarang?');
    });

    // Inisialisasi awal: jika mode auto-split terisi, langsung hitung subtotal & pantau alokasi
    var isAutoPrefilled = <?= !empty($is_auto_prefilled) ? 'true' : 'false' ?>;
    var totalExistingQty = 0;
    $('.input-qty-split').each(function() {
        totalExistingQty += parseFloat($(this).val()) || 0;
    });

    if (isAutoPrefilled) {
        recalculateAll();
    } else if (totalExistingQty === 0 && $('.slot-column').length > 0) {
        autoDistributeAll();
    } else {
        recalculateAll();
    }
});
</script>
