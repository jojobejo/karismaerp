<!-- content/logistik/ics/detail_po.php -->

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">
                    <style>
                        .detail-po-hero {
                            background: linear-gradient(135deg, #243cff 0%, #3558ff 52%, #7f92ff 100%);
                            color: #fff;
                            border-radius: 16px;
                            padding: 22px 24px;
                            box-shadow: 0 18px 38px rgba(36, 60, 255, 0.22);
                            position: relative;
                            overflow: hidden;
                        }

                        .detail-po-hero::after {
                            content: '';
                            position: absolute;
                            inset: auto -60px -60px auto;
                            width: 180px;
                            height: 180px;
                            background: rgba(255, 255, 255, 0.08);
                            border-radius: 50%;
                        }

                        .mini-stat {
                            background: #fff;
                            border-radius: 14px;
                            padding: 14px 16px;
                            border: 1px solid #dbe4ff;
                            box-shadow: 0 10px 24px rgba(36, 60, 255, 0.08);
                            height: 100%;
                        }

                        .mini-stat-link {
                            display: block;
                            text-decoration: none !important;
                            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
                        }

                        .mini-stat-link:hover {
                            transform: translateY(-2px);
                        }

                        .mini-stat-link .mini-stat {
                            cursor: pointer;
                        }

                        .mini-stat-link:hover .mini-stat {
                            border-color: #91a2ff;
                            box-shadow: 0 18px 32px rgba(36, 60, 255, 0.16);
                        }

                        .mini-stat .label {
                            font-size: 12px;
                            font-weight: 700;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            color: #64748b;
                        }

                        .mini-stat .value {
                            font-size: 26px;
                            font-weight: 800;
                            color: #0f172a;
                            line-height: 1.1;
                            margin-top: 6px;
                        }

                        .btn-round-action {
                            width: 38px;
                            height: 38px;
                            border-radius: 999px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            box-shadow: 0 10px 18px rgba(36, 60, 255, 0.22);
                        }

                        .table thead.thead-emerald th {
                            background: #243cff;
                            color: #fff;
                            border: 1px solid #dbe4ff !important;
                        }

                        .draft-shell {
                            border-radius: 16px;
                            overflow: hidden;
                            border: 1px solid #dbe4ff;
                            box-shadow: 0 12px 32px rgba(36, 60, 255, 0.08);
                        }

                        .draft-shell .card-header {
                            background: linear-gradient(135deg, #243cff 0%, #4b63ff 100%);
                            color: #fff;
                        }

                        .modal-rows-table td,
                        .modal-rows-table th {
                            vertical-align: middle;
                        }

                        .sticky-modal-footer {
                            border-top: 1px solid #e2e8f0;
                            background: #f8fafc;
                        }

                        .badge-soft {
                            background: #eef1ff;
                            color: #243cff;
                            border: 1px solid #c6d0ff;
                            font-weight: 700;
                        }

                        .summary-empty {
                            border: 1px dashed #cbd5e1;
                            border-radius: 14px;
                            padding: 28px 16px;
                            text-align: center;
                            color: #64748b;
                            background: #f8fafc;
                        }

                        .draft-header-box {
                            border: 1px solid #dbe4ff;
                            border-radius: 16px;
                            padding: 12px 14px;
                            background: linear-gradient(180deg, #f5f7ff 0%, #ffffff 100%);
                            margin-bottom: 14px;
                        }

                        .draft-header-row {
                            margin-left: -5px;
                            margin-right: -5px;
                        }

                        .draft-header-row > [class*="col-"] {
                            padding-left: 5px;
                            padding-right: 5px;
                        }

                        .draft-header-field {
                            margin-bottom: 8px;
                        }

                        .draft-header-field label {
                            margin-bottom: 4px;
                            white-space: nowrap;
                        }

                        @media (min-width: 992px) {
                            .draft-header-row {
                                flex-wrap: wrap;
                            }

                            .draft-header-col {
                                flex: 1 1 auto;
                                min-width: 110px;
                            }

                            .draft-header-col-lpb {
                                width: 12%;
                            }

                            .draft-header-col-sj {
                                width: 12%;
                            }

                            .draft-header-col-date {
                                width: 12%;
                            }

                            .draft-header-col-type {
                                width: 15%;
                            }

                            .draft-header-col-gudang {
                                width: 15%;
                            }

                            .draft-header-col-checker {
                                width: 16%;
                            }

                            .draft-header-col-note {
                                width: 18%;
                                flex-grow: 1;
                            }
                        }

                        @media (min-width: 1366px) {
                            .draft-header-row {
                                flex-wrap: nowrap;
                            }
                        }

                        .draft-summary-stat {
                            background: #f6f8ff;
                            border: 1px solid #dbe4ff;
                            border-radius: 14px;
                            padding: 14px 16px;
                            height: 100%;
                        }

                        .draft-summary-stat .label {
                            font-size: 12px;
                            font-weight: 700;
                            letter-spacing: 0.06em;
                            text-transform: uppercase;
                            color: #64748b;
                        }

                        .draft-summary-stat .value {
                            font-size: 24px;
                            font-weight: 800;
                            color: #0f172a;
                            margin-top: 4px;
                            line-height: 1.1;
                        }

                        .draft-actions {
                            display: flex;
                            gap: 10px;
                            justify-content: flex-end;
                            flex-wrap: wrap;
                            margin-top: 18px;
                        }

                        .lpb-po-filter {
                            display: flex;
                            gap: 8px;
                            flex-wrap: wrap;
                            align-items: center;
                            margin-bottom: 12px;
                        }

                        .lpb-status-actions {
                            display: inline-flex;
                            gap: 5px;
                            align-items: center;
                            justify-content: center;
                            flex-wrap: nowrap;
                        }
                    </style>

                    <div class="row mb-3">
                        <div class="col-auto">
                            <a href="<?= base_url('ics/icspo') ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="detail-po-hero mb-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h3 class="mb-1 font-weight-bold">
                                    Detail Penerimaan PO
                                </h3>
                                <p class="mb-0">
                                    Kelola draft penerimaan per barang langsung dari baris PO. Klik tombol plus hijau untuk input lot dan expired date
                                </p>
                            </div>
                            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                                <div class="h5 mb-1">No PO</div>
                                <div class="h3 font-weight-bold mb-0"><?= htmlspecialchars($no_po) ?></div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $isPurchasingDetailPo = !empty($is_detail_po_purchasing);
                    $totalOrder = 0;
                    $totalReceived = 0;
                    $totalLpbRecord = 0;
                    $formatQtyPo = static function ($value, $maxDecimals = 0) {
                        $number = (float) $value;
                        $decimals = $maxDecimals > 0 && abs($number - round($number)) > 0.00001 ? $maxDecimals : 0;
                        return number_format($number, $decimals, ',', '.');
                    };
                    $formatDatePo = static function ($value) {
                        $value = trim((string) $value);
                        return $value === '' || $value === '0000-00-00' ? '-' : $value;
                    };
                    foreach ($detail as $row) {
                        $totalOrder += (float) ($row['qty_kecil'] ?? 0);
                        $totalReceived += (float) ($row['qty_kecil_diterima'] ?? 0);
                        $totalLpbRecord += (int) ($row['total_lpb_record'] ?? 0);
                    }
                    ?>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="mini-stat">
                                <div class="label">Qty Order Kecil</div>
                                <div class="value"><?= number_format($totalOrder, 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="mini-stat">
                                <div class="label">Qty Diterima Kecil</div>
                                <div class="value"><?= number_format($totalReceived, 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="<?= base_url('ics/detail_record_lpb?kd_po=' . urlencode($detail[0]['kd_po'] ?? '') . '&no_po=' . urlencode($no_po ?? '') . '&kd_suplier=' . urlencode($kd_suplier ?? '')) ?>" class="mini-stat-link" target="_blank">
                                <div class="mini-stat">
                                    <div class="label">Total Record LPB</div>
                                    <div class="value"><?= number_format($totalLpbRecord, 0, ',', '.') ?></div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="card draft-shell mb-4">
                        <div class="card-header">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-boxes mr-2"></i> Detail Barang PO
                            </h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-hover" id="tabelDetailPo">
                                <thead class="thead-emerald">
                                    <tr>
                                        <th class="text-center align-middle" rowspan="2">No</th>
                                        <th class="text-center align-middle" rowspan="2">Kode Barang</th>
                                        <th class="text-center align-middle" rowspan="2">Nama Barang</th>
                                        <th class="text-center align-middle" rowspan="2">Qty Order</th>
                                        <th class="text-center" colspan="2">Qty Order</th>
                                        <th class="text-center align-middle" rowspan="2">Qty In</th>
                                        <th class="text-center" colspan="3">Qty Diterima</th>
                                        <th class="text-center align-middle" rowspan="2">Qty Sisa</th>
                                        <?php if (!$isPurchasingDetailPo) : ?>
                                            <th class="text-center align-middle" rowspan="2">Status</th>
                                            <th class="text-center align-middle" rowspan="2">Draft Temp</th>
                                            <th class="text-center align-middle" rowspan="2">#</th>
                                        <?php endif; ?>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Box</th>
                                        <th class="text-center">Kg/Ltr</th>
                                        <th class="text-center">Box</th>
                                        <th class="text-center">Kg/Ltr</th>
                                        <th class="text-center">Qty Kecil</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($detail)) : ?>
                                        <?php foreach ($detail as $i => $row) : ?>
                                            <tr id="po-row-<?= htmlspecialchars($row['kd_po'] ?? '') ?>-<?= htmlspecialchars($row['kd_barang'] ?? '') ?>" data-kd-po="<?= htmlspecialchars($row['kd_po'] ?? '') ?>" data-kd-barang="<?= htmlspecialchars($row['kd_barang'] ?? '') ?>">
                                                <td class="text-center"><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars($row['kd_barang'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($row['nama_barang'] ?? '-') ?></td>
                                                <td class="text-center"><?= $formatQtyPo($row['qty_kecil'] ?? 0) ?></td>
                                                <td class="text-center"><?= $formatQtyPo($row['qty_order_box'] ?? 0, 2) ?></td>
                                                <td class="text-center"><?= $formatQtyPo($row['qty_order_kg'] ?? 0, 2) ?></td>
                                                <td class="text-center js-qty-in"><?= $formatQtyPo($row['qty_in'] ?? 0) ?></td>
                                                <td class="text-center js-qty-diterima-box"><?= $formatQtyPo($row['qty_diterima_box'] ?? 0, 2) ?></td>
                                                <td class="text-center js-qty-diterima-kg"><?= $formatQtyPo($row['qty_diterima_kg'] ?? 0, 2) ?></td>
                                                <td class="text-center js-qty-diterima-kecil"><?= $formatQtyPo($row['qty_kecil_diterima'] ?? 0) ?></td>
                                                <td class="text-center js-qty-sisa"><?= $formatQtyPo($row['qty_kecil_sisa'] ?? 0) ?></td>
                                                <?php if (!$isPurchasingDetailPo) : ?>
                                                    <td class="text-center js-status-cell">
                                                        <?php
                                                        $statusBarang = strtoupper((string) ($row['status_barang'] ?? 'BELUM'));
                                                        $badgeClass = 'secondary';
                                                        if ($statusBarang === 'FULL') {
                                                            $badgeClass = 'success';
                                                        } elseif ($statusBarang === 'PARTIAL') {
                                                            $badgeClass = 'warning';
                                                        } elseif ($statusBarang === 'BELUM') {
                                                            $badgeClass = 'danger';
                                                        }
                                                        ?>
                                                        <span class="badge badge-<?= $badgeClass ?> px-3 py-2 js-status-badge"><?= htmlspecialchars($statusBarang) ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-soft js-draft-badge" data-kd-po="<?= htmlspecialchars($row['kd_po'] ?? '') ?>" data-kd-barang="<?= htmlspecialchars($row['kd_barang'] ?? '') ?>">
                                                            0 baris
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-success btn-round-action js-open-modal" title="Tambah draft penerimaan" data-kd-po="<?= htmlspecialchars($row['kd_po'] ?? '') ?>" data-kd-suplier="<?= htmlspecialchars($kd_suplier ?? '') ?>" data-kd-barang="<?= htmlspecialchars($row['kd_barang'] ?? '') ?>" data-nama-barang="<?= htmlspecialchars($row['nama_barang'] ?? '-') ?>" data-no-po="<?= htmlspecialchars($no_po) ?>" data-satuan="<?= htmlspecialchars($row['satuan'] ?? '') ?>" data-sisa="<?= htmlspecialchars((string) ($row['qty_kecil_sisa'] ?? 0)) ?>" data-sisa-besar="<?= htmlspecialchars((string) ($row['qty_sisa'] ?? 0)) ?>" data-sisa-kecil="<?= htmlspecialchars((string) ($row['qty_kecil_sisa'] ?? 0)) ?>" data-dimensi="<?= htmlspecialchars((string) ($row['dimensi_br'] ?? 1)) ?>" data-toggle="modal" data-target="#modalTmpPoReceived">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="<?= $isPurchasingDetailPo ? 11 : 14 ?>" class="text-center text-muted">
                                                <i class="fas fa-inbox mr-1"></i> Belum ada barang diterima untuk PO ini
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if ($isPurchasingDetailPo) : ?>
                    <div class="card draft-shell">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-clipboard-check mr-2"></i> List Data LPB Yang Telah Direkam
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="lpb-po-filter" id="lpbPoStatusDataFilter">
                                <button type="button" class="btn btn-primary btn-sm active" data-filter="all">Semua</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="invoice">
                                    <i class="fas fa-file-invoice mr-1"></i> Belum Invoice
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="pajak">
                                    <i class="fas fa-percent mr-1"></i> Belum Pajak
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="uang">
                                    <i class="fas fa-check-double mr-1"></i> Belum Afirmasi Harga
                                </button>
                            </div>
                            <div class="lpb-po-filter" id="lpbPoStatusBarangFilter">
                                <button type="button" class="btn btn-primary btn-sm active" data-filter="all">Semua Status Barang</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="sales">
                                    <i class="fas fa-cash-register mr-1"></i> Sudah Transaksi
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="journal">
                                    <i class="fas fa-balance-scale mr-1"></i> Sudah Jurnal
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-filter="clear">
                                    <i class="fas fa-check-circle mr-1"></i> Belum Transaksi/Jurnal
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="tabelLpbPoPurchasing">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th>Tgl LPB</th>
                                            <th>NO LPB</th>
                                            <th>Tgl PO</th>
                                            <th>No PO</th>
                                            <th>Tgl SJ</th>
                                            <th>No SJ</th>
                                            <th class="text-center">Invoice</th>
                                            <th>No FP</th>
                                            <th class="text-right">Grand Total</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Status Data</th>
                                            <th class="text-center">Satatus Barang</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($lpb_po_records)) : ?>
                                            <?php foreach ($lpb_po_records as $row) :
                                                $progressStatus = strtolower(trim((string) ($row['progress_status'] ?? 'belum')));
                                                $totalVerified = (int) ($row['total_verified'] ?? 0);
                                                $totalDetail = (int) ($row['total_detail'] ?? 0);
                                                $invoiceValue = trim((string) ($row['no_invoice'] ?? ''));
                                                $invoiceCount = (int) ($row['invoice_count'] ?? 0);
                                                $fakturValue = trim((string) ($row['kode_faktur_pajak'] ?? ''));
                                                $hasInvoice = $invoiceCount > 0;
                                                $hasFaktur = $fakturValue !== '' && $fakturValue !== '-';
                                                $isVerified = $progressStatus === 'done' || ($totalDetail > 0 && $totalVerified >= $totalDetail);
                                                $invoiceBtnClass = $hasInvoice ? 'btn-success' : 'btn-light text-secondary border';
                                                $fakturBtnClass = $hasFaktur ? 'btn-success' : 'btn-light text-secondary border';
                                                $verifiedBtnClass = $isVerified ? 'btn-success' : 'btn-light text-secondary border';
                                                $hasSalesTransaction = (int) ($row['has_sales_transaction'] ?? 0) === 1;
                                                $hasActiveJournal = (int) ($row['has_active_lpb_journal'] ?? 0) === 1;
                                                $statusLpbRaw = $row['status_lpb'] ?? null;
                                                if ($statusLpbRaw === null || $statusLpbRaw === '') {
                                                    $statusBadge = '<span class="badge badge-secondary px-2 py-1">DRAFT</span>';
                                                } elseif ((string) $statusLpbRaw === '0') {
                                                    $statusBadge = '<span class="badge badge-warning px-2 py-1">UNPOST</span>';
                                                } else {
                                                    $statusBadge = '<span class="badge badge-success px-2 py-1">TERPOSTING</span>';
                                                }
                                                $detailUrl = base_url('ics/detail_record_lpb?kd_po=' . urlencode($row['kd_po'] ?? '') . '&no_po=' . urlencode($row['no_po'] ?? '') . '&kd_suplier=' . urlencode($row['kd_suplier'] ?? '') . '&id_lpb=' . urlencode($row['id_lpb'] ?? ''));
                                                $invoiceTitle = $hasInvoice
                                                    ? $invoiceCount . ' invoice untuk nomor LPB ini'
                                                    : 'Belum ada invoice untuk nomor LPB ini';
                                                $salesTitle = $hasSalesTransaction
                                                    ? 'Sudah ada ' . (int) ($row['sales_invoice_count'] ?? 0) . ' faktur penjualan: ' . (string) ($row['sales_invoice_sample'] ?? '-')
                                                    : 'Belum ada transaksi penjualan dari LPB ini';
                                                $journalTitle = $hasActiveJournal
                                                    ? 'Jurnal LPB POSTED: ' . (string) ($row['lpb_journal_sample'] ?? '-')
                                                    : 'Belum ada jurnal LPB POSTED aktif';
                                            ?>
                                            <tr data-has-invoice="<?= $hasInvoice ? '1' : '0' ?>" data-has-faktur="<?= $hasFaktur ? '1' : '0' ?>" data-is-verified="<?= $isVerified ? '1' : '0' ?>" data-has-sales="<?= $hasSalesTransaction ? '1' : '0' ?>" data-has-journal="<?= $hasActiveJournal ? '1' : '0' ?>">
                                                <td><?= htmlspecialchars($row['tgl_lpb'] ?? '-') ?></td>
                                                <td>
                                                    <a href="<?= $detailUrl ?>" class="font-weight-bold" target="_blank">
                                                        <?= htmlspecialchars($row['nomor_lpb'] ?? '-') ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($row['tgl_po'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($row['no_po'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($formatDatePo($row['tgl_sj'] ?? '-')) ?></td>
                                                <td><?= htmlspecialchars($row['nosj'] ?? '-') ?></td>
                                                <td class="text-center">
                                                    <span class="badge <?= $hasInvoice ? 'badge-success' : 'badge-secondary' ?>" title="<?= htmlspecialchars($invoiceTitle, ENT_QUOTES, 'UTF-8') ?>">
                                                        <?= $hasInvoice ? number_format($invoiceCount, 0, ',', '.') . ' invoice' : '0 invoice' ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($hasFaktur ? $fakturValue : '-') ?></td>
                                                <td class="text-right"><?= 'Rp ' . number_format((float) ($row['grand_total_lpb'] ?? 0), 0, ',', '.') ?></td>
                                                <td class="text-center"><?= $statusBadge ?></td>
                                                <td class="text-center">
                                                    <div class="lpb-status-actions">
                                                        <button type="button" class="btn btn-sm <?= $invoiceBtnClass ?>" title="<?= $hasInvoice ? 'Invoice sudah ada' : 'Invoice belum ada' ?>">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm <?= $fakturBtnClass ?>" title="<?= $hasFaktur ? 'Pajak/Faktur sudah ada' : 'Pajak/Faktur belum ada' ?>">
                                                            <i class="fas fa-percent"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm <?= $verifiedBtnClass ?>" title="<?= $isVerified ? 'Afirmasi harga selesai' : 'Afirmasi harga belum selesai' ?>">
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="lpb-status-actions">
                                                        <button type="button" class="btn btn-sm <?= $hasSalesTransaction ? 'btn-warning' : 'btn-light text-secondary border' ?>" title="<?= htmlspecialchars($salesTitle, ENT_QUOTES, 'UTF-8') ?>">
                                                            <i class="fas fa-cash-register"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm <?= $hasActiveJournal ? 'btn-danger' : 'btn-light text-secondary border' ?>" title="<?= htmlspecialchars($journalTitle, ENT_QUOTES, 'UTF-8') ?>">
                                                            <i class="fas fa-balance-scale"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php else : ?>
                    <div class="card draft-shell">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-clipboard-list mr-2"></i> Draft Temporary Penerimaan
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="draft-header-box">
                                <div class="row draft-header-row" id="pre_po_data">
                                    <div class="col-lg draft-header-col draft-header-col-lpb col-md-6 draft-header-field">
                                        <label class="font-weight-bold">Nomor LPB</label>
                                        <input type="text" class="form-control bg-light" id="final_nomor_lpb" placeholder="Auto" readonly>
                                    </div>
                                    <div class="col-lg draft-header-col draft-header-col-sj col-md-6 draft-header-field" id="pre_po_date">
                                        <label class="font-weight-bold">Nomor SJ</label>
                                        <input type="text" class="form-control" id="final_nosj" placeholder="No SJ">
                                    </div>
                                    <div class="col-lg draft-header-col draft-header-col-date col-md-6 draft-header-field">
                                        <label class="font-weight-bold">Tanggal SJ</label>
                                        <input type="date" class="form-control" id="final_tgl_sj">
                                    </div>
                                    <div class="col-lg-3 col-md-6 draft-header-field" hidden>
                                        <label class="font-weight-bold">Nomor PO</label>
                                        <input type="text" class="form-control" id="final_no_po" value="<?= htmlspecialchars($no_po) ?>" readonly>
                                        <input type="hidden" id="final_kd_po" value="<?= htmlspecialchars($kd_po ?? '') ?>">
                                    </div>
                                    <div class="col-lg-3 col-md-6 draft-header-field" hidden>
                                        <label class="font-weight-bold">Invoice <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="final_invoice" value="-" readonly>
                                    </div>
                                    <div class="col-lg draft-header-col draft-header-col-type col-md-6 draft-header-field">
                                        <label class="font-weight-bold">Jenis PO <span class="text-danger">*</span></label>
                                        <select class="form-control" id="final_jenis_lpb">
                                            <?php foreach (($lpb_type_options ?? []) as $typeKey => $typeInfo) : ?>
                                                <option value="<?= htmlspecialchars($typeKey, ENT_QUOTES) ?>"><?= htmlspecialchars($typeInfo['label'] ?? $typeKey) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg draft-header-col draft-header-col-gudang col-md-6 draft-header-field">
                                        <label class="font-weight-bold">Gudang <span class="text-danger">*</span></label>
                                        <select class="form-control" id="final_gudang_id">
                                            <option value="">-- Pilih Gudang --</option>
                                            <?php foreach (($list_gudang ?? []) as $gudang) : ?>
                                                <option value="<?= htmlspecialchars($gudang['id_gudang']) ?>"><?= htmlspecialchars($gudang['nama_gudang']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php if (!empty($show_checker_input) || !empty($is_admlpb_user) || !empty($is_admin_po)) : ?>
                                    <div class="col-lg draft-header-col draft-header-col-checker col-md-6 draft-header-field">
                                        <label class="font-weight-bold">Checker By</label>
                                        <input type="text" class="form-control" id="final_checker_by" placeholder="Nama / Kode Checker">
                                    </div>
                                    <?php else : ?>
                                    <input type="hidden" id="final_checker_by" value="">
                                    <?php endif; ?>
                                    <div class="col-lg draft-header-col draft-header-col-note col-md-6 draft-header-field">
                                        <label class="font-weight-bold">Keterangan</label>
                                        <input type="text" class="form-control" id="final_keterangan" placeholder="Catatan">
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="col-md-3 mb-3">
                                        <div class="draft-summary-stat">
                                            <div class="label">Total Qty Draft</div>
                                            <div class="value" id="summaryTotalQty">0</div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <div class="draft-summary-stat">
                                            <div class="label">Total Qty Kecil</div>
                                            <div class="value" id="summaryTotalQtyKecil">0</div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <div class="draft-summary-stat">
                                            <div class="label">Jumlah Lot</div>
                                            <div class="value" id="summaryTotalLot">0</div>
                                        </div>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <div class="draft-summary-stat">
                                            <div class="label">Baris Draft</div>
                                            <div class="value" id="summaryTotalRows">0</div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div id="tmpSummaryState" class="summary-empty">
                                <i class="fas fa-layer-group fa-2x mb-2"></i>
                                <div>Belum ada draft penerimaan untuk PO ini.</div>
                            </div>

                            <div class="table-responsive" id="tmpSummaryWrapper" style="display:none;">
                                <table class="table table-bordered table-hover mt-1" id="detail_transaksi_po">
                                    <thead class="thead-emerald">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th class="text-center">Qty Diterima</th>
                                            <th class="text-center">Qty Kecil</th>
                                            <th class="text-center">Satuan</th>
                                            <th>No Lot</th>
                                            <th class="text-center">Expired Date</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="draft-actions">
                                <button type="button" class="btn btn-outline-secondary" id="btnResetFinalForm">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                                <button type="button" class="btn btn-success" id="btnSubmitFinalLpb">
                                    <i class="fas fa-save mr-1"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>

        <div class="modal fade" id="modalTmpPoReceived" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle mr-2"></i> Draft Penerimaan Barang
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form id="formTmpPoReceived">

                        <div class="modal-body">
                            <input type="hidden" name="kd_po" id="tmp_kd_po">
                            <input type="hidden" name="kd_suplier" id="tmp_kd_suplier">
                            <input type="hidden" name="kd_barang" id="tmp_kd_barang">

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="font-weight-bold">No PO</label>
                                    <input type="text" class="form-control" id="tmp_no_po" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="font-weight-bold">Kode Barang</label>
                                    <input type="text" class="form-control" id="tmp_display_kd_barang" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="font-weight-bold">Qty Sisa</label>
                                    <div class="row">
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Qty Besar</span>
                                                </div>
                                                <input type="text" class="form-control" id="tmp_qty_sisa_besar" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Qty Kecil</span>
                                                </div>
                                                <input type="text" class="form-control" id="tmp_qty_sisa_kecil" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-light border mb-3">
                                <strong id="tmp_nama_barang">-</strong><br>
                                <small class="text-muted">Input bisa lebih dari satu baris lot untuk barang yang sama. Draft ini tersimpan di temporary table.</small>
                            </div>

                            <div id="tmpModalLoader" class="text-center py-4" style="display:none;">
                                <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
                                <div class="mt-2 text-muted">Memuat draft penerimaan...</div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered modal-rows-table" id="tableModalTmpReceived">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="min-width:150px;">Kd Barang</th>
                                            <th style="min-width:140px;">Qty Diterima</th>
                                            <th style="min-width:140px;">Qty Kecil</th>
                                            <th style="min-width:150px;">Satuan</th>
                                            <th style="min-width:180px;">No Lot</th>
                                            <th style="min-width:180px;">Expired Date</th>
                                            <th style="width:70px;" class="text-center">#</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tmpRowsBody"></tbody>
                                </table>
                            </div>

                            <button type="button" class="btn btn-outline-success" id="btnTambahBarisTmp">
                                <i class="fas fa-plus mr-1"></i> Tambah Baris Baru
                            </button>
                        </div>

                        <div class="modal-footer sticky-modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times mr-1"></i> Tutup
                            </button>
                            <button type="submit" class="btn btn-success" id="btnSimpanTmp">
                                <i class="fas fa-save mr-1"></i> Simpan Draft
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
        </footer>
        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <script>
        $(function() {
            var isPurchasingDetailPo = <?= $isPurchasingDetailPo ? 'true' : 'false' ?>;
            var currentItem = {
                kd_po: '',
                kd_suplier: '',
                kd_barang: '',
                nama_barang: '',
                no_po: '',
                satuan_default: '',
                qty_sisa: 0,
                qty_kecil_sisa: 0,
                dimensi_br: 1
            };
            var isSubmittingFinal = false;
            var lpbNumberRequestSeq = 0;
            var defaultFinalForm = {
                no_po: '<?= htmlspecialchars($no_po, ENT_QUOTES) ?>',
                nomor_lpb: '',
                nosj: '',
                tgl_sj: '',
                no_invoice: '-',
                jenis_lpb: 'LPB CP',
                gudang_id: '',
                keterangan: ''
            };

            var detailPoTable = $('#tabelDetailPo').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                order: [
                    [0, 'asc']
                ],
                columnDefs: isPurchasingDetailPo ? [] : [{
                    orderable: false,
                    targets: [12, 13]
                }],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    zeroRecords: "Tidak ada data ditemukan",
                    emptyTable: "Tidak ada data tersedia",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            if (isPurchasingDetailPo) {
                var lpbPoStatusDataFilter = 'all';
                var lpbPoStatusBarangFilter = 'all';

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (!settings.nTable || settings.nTable.id !== 'tabelLpbPoPurchasing') {
                        return true;
                    }

                    var $row = $(settings.aoData[dataIndex].nTr);

                    if (lpbPoStatusDataFilter === 'invoice' && String($row.data('has-invoice')) !== '0') {
                        return false;
                    }

                    if (lpbPoStatusDataFilter === 'pajak' && String($row.data('has-faktur')) !== '0') {
                        return false;
                    }

                    if (lpbPoStatusDataFilter === 'uang' && String($row.data('is-verified')) !== '0') {
                        return false;
                    }

                    if (lpbPoStatusBarangFilter === 'sales' && String($row.data('has-sales')) !== '1') {
                        return false;
                    }

                    if (lpbPoStatusBarangFilter === 'journal' && String($row.data('has-journal')) !== '1') {
                        return false;
                    }

                    if (lpbPoStatusBarangFilter === 'clear' &&
                        (String($row.data('has-sales')) !== '0' || String($row.data('has-journal')) !== '0')) {
                        return false;
                    }

                    return true;
                });

                var lpbPoTable = $('#tabelLpbPoPurchasing').DataTable({
                    responsive: true,
                    autoWidth: false,
                    pageLength: 25,
                    order: [
                        [0, 'desc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: [10, 11]
                    }],
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                        zeroRecords: "Tidak ada data ditemukan",
                        emptyTable: "Belum ada LPB yang direkam untuk PO ini",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "Berikutnya",
                            previous: "Sebelumnya"
                        }
                    }
                });

                $('#lpbPoStatusDataFilter').on('click', 'button[data-filter]', function() {
                    lpbPoStatusDataFilter = $(this).data('filter') || 'all';
                    $('#lpbPoStatusDataFilter button[data-filter]')
                        .removeClass('btn-primary active')
                        .addClass('btn-outline-secondary');
                    $(this)
                        .removeClass('btn-outline-secondary')
                        .addClass('btn-primary active');
                    lpbPoTable.draw();
                });

                $('#lpbPoStatusBarangFilter').on('click', 'button[data-filter]', function() {
                    lpbPoStatusBarangFilter = $(this).data('filter') || 'all';
                    $('#lpbPoStatusBarangFilter button[data-filter]')
                        .removeClass('btn-primary active')
                        .addClass('btn-outline-secondary');
                    $(this)
                        .removeClass('btn-outline-secondary')
                        .addClass('btn-primary active');
                    lpbPoTable.draw();
                });
            }

            function escHtml(str) {
                return $('<div>').text(str || '').html();
            }

            function getStatusBadgeClass(status) {
                status = (status || 'BELUM').toString().toUpperCase();

                if (status === 'FULL') {
                    return 'success';
                }

                if (status === 'PARTIAL') {
                    return 'warning';
                }

                if (status === 'BELUM') {
                    return 'danger';
                }

                return 'secondary';
            }

            function findDetailPoRow(kdPo, kdBarang) {
                var matchedRow = $();

                $('#tabelDetailPo tbody tr').each(function() {
                    var row = $(this);

                    if ((row.attr('data-kd-po') || '') == (kdPo || '') &&
                        (row.attr('data-kd-barang') || '') == (kdBarang || '')) {
                        matchedRow = row;
                        return false;
                    }
                });

                return matchedRow;
            }

            function applyDetailPoRows(rows) {
                $.each(rows || [], function(_, row) {
                    var detailRow = findDetailPoRow(row.kd_po, row.kd_barang);

                    if (!detailRow.length) {
                        return;
                    }

                    var statusBarang = (row.status_barang || 'BELUM').toString().toUpperCase();
                    var badgeClass = getStatusBadgeClass(statusBarang);

                    detailRow.find('.js-qty-in').text(formatNumber(row.qty_in));
                    detailRow.find('.js-qty-diterima-box').text(formatNumber(row.qty_diterima_box));
                    detailRow.find('.js-qty-diterima-kg').text(formatNumber(row.qty_diterima_kg));
                    detailRow.find('.js-qty-diterima-kecil').text(formatNumber(row.qty_kecil_diterima));
                    detailRow.find('.js-qty-sisa').text(formatNumber(row.qty_kecil_sisa));
                    detailRow.find('.js-status-badge')
                        .attr('class', 'badge badge-' + badgeClass + ' px-3 py-2 js-status-badge')
                        .text(statusBarang);

                    detailRow.find('.js-open-modal')
                        .attr('data-sisa', row.qty_kecil_sisa || 0)
                        .attr('data-sisa-besar', row.qty_sisa || 0)
                        .attr('data-sisa-kecil', row.qty_kecil_sisa || 0)
                        .data('sisa', row.qty_kecil_sisa || 0)
                        .data('sisa-besar', row.qty_sisa || 0)
                        .data('sisa-kecil', row.qty_kecil_sisa || 0);

                    if (currentItem.kd_po == row.kd_po && currentItem.kd_barang == row.kd_barang) {
                        currentItem.qty_sisa = parseFloat(row.qty_sisa) || 0;
                        currentItem.qty_kecil_sisa = parseFloat(row.qty_kecil_sisa) || 0;

                        if ($('#modalTmpPoReceived').hasClass('show')) {
                            fillModalHeader(currentItem);
                        }
                    }
                });

                detailPoTable.rows().invalidate('dom').draw(false);
            }

            function reloadDetailPoRows() {
                $.ajax({
                    url: '<?= base_url('ics/ajax_get_detail_po_rows') ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        no_po: '<?= htmlspecialchars($no_po, ENT_QUOTES) ?>',
                        kd_suplier: '<?= htmlspecialchars($kd_suplier ?? '', ENT_QUOTES) ?>'
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            return;
                        }

                        applyDetailPoRows(res.rows || []);
                    }
                });
            }

            function formatNumber(value) {
                return new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                }).format(parseFloat(value) || 0);
            }

            function formatInputNumber(value) {
                if (value === null || typeof value === 'undefined' || value === '') {
                    return '';
                }

                var number = parseFloat(value) || 0;

                if (Math.abs(number - Math.round(number)) < 0.00001) {
                    return String(Math.round(number));
                }

                return String(parseFloat(number.toFixed(2)));
            }

            function buildRow(rowData) {
                var selectedSatuan = currentItem.satuan_default || '';
                var qtyValue = rowData && typeof rowData.qty_diterima !== 'undefined' && rowData.qty_diterima !== null ? formatInputNumber(rowData.qty_diterima) : '';
                var dimensiBr = parseFloat(currentItem.dimensi_br) || 1;
                if (dimensiBr <= 0) dimensiBr = 1;

                var qtyValNum = rowData && typeof rowData.qty_diterima !== 'undefined' && rowData.qty_diterima !== null ? parseFloat(rowData.qty_diterima) || 0 : 0;
                var qtyKecilVal = (rowData && (typeof rowData.qty_diterima_kecil !== 'undefined' || typeof rowData.qty_kecil_diterima !== 'undefined'))
                    ? formatInputNumber(rowData.qty_diterima_kecil || rowData.qty_kecil_diterima)
                    : (qtyValNum > 0 ? formatInputNumber(qtyValNum * dimensiBr) : '');

                var noLotValue = rowData && rowData.no_lot ? rowData.no_lot : '';
                var expiredValue = rowData && rowData.expired_date ? rowData.expired_date : '';

                return '' +
                    '<tr>' +
                    '<td><input type="text" class="form-control form-control-sm bg-light js-kd-barang-row" value="' + escHtml(currentItem.kd_barang) + '" readonly></td>' +
                    '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm js-qty-row" value="' + escHtml(qtyValue) + '" placeholder="0"></td>' +
                    '<td><input type="text" class="form-control form-control-sm bg-light js-qty-kecil-row" value="' + escHtml(qtyKecilVal) + '" readonly placeholder="0"></td>' +
                    '<td><input type="text" class="form-control form-control-sm bg-light js-satuan-row" value="' + escHtml(selectedSatuan) + '" readonly></td>' +
                    '<td><input type="text" class="form-control form-control-sm js-lot-row" value="' + escHtml(noLotValue) + '" placeholder="Nomor lot"></td>' +
                    '<td><input type="date" class="form-control form-control-sm js-exp-row" value="' + escHtml(expiredValue) + '"></td>' +
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-danger js-hapus-row"><i class="fas fa-trash"></i></button></td>' +
                    '</tr>';
            }

            function ensureAtLeastOneRow() {
                if ($('#tmpRowsBody tr').length === 0) {
                    $('#tmpRowsBody').append(buildRow());
                }
                syncDeleteButtons();
            }

            function syncDeleteButtons() {
                var totalRows = $('#tmpRowsBody tr').length;
                $('#tmpRowsBody .js-hapus-row').prop('disabled', totalRows === 1);
            }

            function getPayloadRows() {
                var rows = [];

                $('#tmpRowsBody tr').each(function() {
                    rows.push({
                        qty_diterima: $(this).find('.js-qty-row').val(),
                        satuan: $(this).find('.js-satuan-row').val(),
                        no_lot: $(this).find('.js-lot-row').val(),
                        expired_date: $(this).find('.js-exp-row').val()
                    });
                });

                return rows;
            }

            function fillModalHeader(item) {
                item = item || {};
                var noPo = item.no_po || '<?= htmlspecialchars($no_po ?? '', ENT_QUOTES) ?>';
                var kdPo = item.kd_po || '<?= htmlspecialchars($kd_po ?? '', ENT_QUOTES) ?>';
                var kdSuplier = item.kd_suplier || '<?= htmlspecialchars($kd_suplier ?? '', ENT_QUOTES) ?>';
                var kdBarang = item.kd_barang || '';
                var namaBarang = item.nama_barang || '-';

                $('#tmp_kd_po').val(kdPo);
                $('#tmp_kd_suplier').val(kdSuplier);
                $('#tmp_kd_barang').val(kdBarang);
                $('#tmp_no_po').val(noPo);
                $('#tmp_display_kd_barang').val(kdBarang);
                $('#tmp_nama_barang').text(namaBarang);
                $('#tmp_qty_sisa_besar').val(formatNumber(item.qty_sisa) + (item.satuan_default ? ' ' + item.satuan_default : ''));
                $('#tmp_qty_sisa_kecil').val(formatNumber(item.qty_kecil_sisa) + ' PCS');
            }

            function renderModalRows(rows) {
                $('#tmpRowsBody').empty();

                if (rows && rows.length) {
                    $.each(rows, function(_, row) {
                        $('#tmpRowsBody').append(buildRow(row));
                    });
                }

                ensureAtLeastOneRow();
            }

            function renderSummaryTable(rows) {
                var tbody = $('#detail_transaksi_po tbody');
                tbody.empty();

                if (!rows || rows.length === 0) {
                    $('#tmpSummaryWrapper').hide();
                    $('#tmpSummaryState').show();
                    $('.js-draft-badge').text('0 baris');
                    updateSummaryStats([]);
                    return;
                }

                $.each(rows, function(index, row) {
                    var idTmpReceived = row.id_tmp_recieved || row.id_tmp_received || row.id || '';
                    var qtyDiterimaKecil = (typeof row.qty_diterima_kecil !== 'undefined' && row.qty_diterima_kecil !== null)
                        ? parseFloat(row.qty_diterima_kecil) || 0
                        : ((parseFloat(row.qty_diterima) || 0) * (parseFloat(row.dimensi_br) || 1));

                    tbody.append(
                        '<tr>' +
                        '<td class="text-center">' + (index + 1) + '</td>' +
                        '<td>' + escHtml(row.kd_barang) + '</td>' +
                        '<td>' + escHtml(row.nama_barang) + '</td>' +
                        '<td class="text-center">' + formatNumber(row.qty_diterima) + '</td>' +
                        '<td class="text-center">' + formatNumber(qtyDiterimaKecil) + '</td>' +
                        '<td class="text-center">' + escHtml(row.satuan) + '</td>' +
                        '<td>' + escHtml(row.no_lot || '-') + '</td>' +
                        '<td class="text-center">' + escHtml(row.expired_date || '-') + '</td>' +
                        '<td class="text-center">' +
                        '<button type="button" class="btn btn-sm btn-outline-danger js-delete-summary-row" data-id="' + escHtml(idTmpReceived) + '" title="Hapus baris draft">' +
                        '<i class="fas fa-trash"></i>' +
                        '</button>' +
                        '</td>' +
                        '</tr>'
                    );
                });

                $('#tmpSummaryState').hide();
                $('#tmpSummaryWrapper').show();
                updateSummaryStats(rows || []);
            }

            function updateDraftBadges(rows) {
                var countMap = {};

                $.each(rows || [], function(_, row) {
                    var key = (row.kd_po || '') + '||' + (row.kd_barang || '');
                    countMap[key] = (countMap[key] || 0) + 1;
                });

                $('.js-draft-badge').each(function() {
                    var key = ($(this).data('kd-po') || '') + '||' + ($(this).data('kd-barang') || '');
                    var total = countMap[key] || 0;
                    $(this).text(total + ' baris');
                });
            }

            function updateSummaryStats(rows) {
                var totalQty = 0;
                var totalQtyKecil = 0;
                var totalRows = rows ? rows.length : 0;
                var totalLot = 0;

                $.each(rows || [], function(_, row) {
                    var qtyBesar = parseFloat(row.qty_diterima) || 0;
                    var qtyKecil = (typeof row.qty_diterima_kecil !== 'undefined' && row.qty_diterima_kecil !== null)
                        ? (parseFloat(row.qty_diterima_kecil) || 0)
                        : (qtyBesar * (parseFloat(row.dimensi_br) || 1));

                    totalQty += qtyBesar;
                    totalQtyKecil += qtyKecil;

                    if ((row.no_lot || '').toString().trim() !== '') {
                        totalLot++;
                    }
                });

                $('#summaryTotalQty').text(formatNumber(totalQty));
                $('#summaryTotalQtyKecil').text(formatNumber(totalQtyKecil));
                $('#summaryTotalLot').text(formatNumber(totalLot));
                $('#summaryTotalRows').text(formatNumber(totalRows));
            }

            function resetFinalForm() {
                $('#final_no_po').val(defaultFinalForm.no_po);
                $('#final_nomor_lpb').val(defaultFinalForm.nomor_lpb);
                $('#final_nosj').val(defaultFinalForm.nosj);
                $('#final_tgl_sj').val(defaultFinalForm.tgl_sj);
                $('#final_invoice').val(defaultFinalForm.no_invoice);
                $('#final_jenis_lpb').val(defaultFinalForm.jenis_lpb);
                if (!$('#final_jenis_lpb').val()) {
                    $('#final_jenis_lpb').prop('selectedIndex', 0);
                }
                $('#final_gudang_id').val(defaultFinalForm.gudang_id);
                $('#final_checker_by').val('');
                $('#final_keterangan').val(defaultFinalForm.keterangan);
                refreshGeneratedLpbNumber();
            }

            function handleAjaxError(xhr, fallbackMessage) {
                var message = fallbackMessage;

                if (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            var json = JSON.parse(xhr.responseText);
                            if (json && json.message) {
                                message = json.message;
                            }
                        } catch (e) {
                            var cleanedText = xhr.responseText.replace(/<[^>]*>?/gm, '').trim();
                            if (cleanedText.length > 0) {
                                message = cleanedText.substring(0, 300);
                            }
                        }
                    }
                }

                Swal.fire('Gagal', message, 'error');
            }

            function refreshGeneratedLpbNumber() {
                var jenisLpb = $('#final_jenis_lpb').val();
                var requestSeq = ++lpbNumberRequestSeq;

                if (!jenisLpb) {
                    $('#final_nomor_lpb').val('');
                    return;
                }

                $('#final_nomor_lpb').val('Memuat...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_generate_lpb_number') ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        jenis_lpb: jenisLpb
                    },
                    success: function(res) {
                        if (requestSeq !== lpbNumberRequestSeq) {
                            return;
                        }

                        if (res.status !== 'success') {
                            $('#final_nomor_lpb').val('');
                            return;
                        }

                        $('#final_nomor_lpb').val(res.nomor_lpb || '');
                    },
                    error: function() {
                        if (requestSeq === lpbNumberRequestSeq) {
                            $('#final_nomor_lpb').val('');
                        }
                    }
                });
            }

            function reloadSummaryTable() {
                $.ajax({
                    url: '<?= base_url('ics/ajax_get_tmp_po_received_summary') ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        no_po: '<?= htmlspecialchars($no_po, ENT_QUOTES) ?>',
                        kd_suplier: '<?= htmlspecialchars($kd_suplier ?? '', ENT_QUOTES) ?>'
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Data temporary tidak dapat dimuat.', 'error');
                            return;
                        }

                        renderSummaryTable(res.rows || []);
                        updateDraftBadges(res.rows || []);
                        reloadDetailPoRows();
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Server tidak merespons saat memuat draft temporary.', 'error');
                    }
                });
            }

            $(document).on('click', '.js-open-modal', function(e) {
                var btn = $(this).closest('.js-open-modal');
                var getAttr = function(attrName, fallbackKey) {
                    var val = btn.attr(attrName);
                    if (typeof val === 'undefined' || val === null || val === '') {
                        var dataKey = attrName.replace('data-', '').replace(/-([a-z])/g, function(g) { return g[1].toUpperCase(); });
                        val = btn.data(dataKey);
                    }
                    if ((typeof val === 'undefined' || val === null || val === '') && fallbackKey) {
                        val = btn.attr(fallbackKey) || btn.data(fallbackKey.replace('data-', ''));
                    }
                    return (typeof val !== 'undefined' && val !== null) ? val : '';
                };

                currentItem = {
                    kd_po: getAttr('data-kd-po') || '<?= htmlspecialchars($kd_po ?? '', ENT_QUOTES) ?>',
                    kd_suplier: getAttr('data-kd-suplier') || '<?= htmlspecialchars($kd_suplier ?? '', ENT_QUOTES) ?>',
                    kd_barang: getAttr('data-kd-barang'),
                    nama_barang: getAttr('data-nama-barang') || '-',
                    no_po: getAttr('data-no-po') || '<?= htmlspecialchars($no_po ?? '', ENT_QUOTES) ?>',
                    satuan_default: getAttr('data-satuan'),
                    qty_sisa: parseFloat(getAttr('data-sisa-besar')) || 0,
                    qty_kecil_sisa: parseFloat(getAttr('data-sisa-kecil', 'data-sisa')) || 0,
                    dimensi_br: parseFloat(getAttr('data-dimensi')) || 1
                };

                fillModalHeader(currentItem);
                $('#tmpRowsBody').empty();
                $('#tmpModalLoader').show();

                $.ajax({
                    url: '<?= base_url('ics/ajax_get_tmp_po_received_item') ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        kd_po: currentItem.kd_po,
                        kd_barang: currentItem.kd_barang
                    },
                    success: function(res) {
                        $('#tmpModalLoader').hide();

                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Draft barang tidak dapat dimuat.', 'error');
                            renderModalRows([]);
                            return;
                        }

                        renderModalRows(res.rows || []);
                    },
                    error: function() {
                        $('#tmpModalLoader').hide();
                        renderModalRows([]);
                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengambil draft barang.', 'error');
                    }
                });
            });

            $('#modalTmpPoReceived').on('show.bs.modal', function() {
                if (currentItem && (currentItem.kd_barang || currentItem.no_po)) {
                    fillModalHeader(currentItem);
                }
            });

            $('#btnTambahBarisTmp').on('click', function() {
                $('#tmpRowsBody').append(buildRow());
                syncDeleteButtons();
            });

            $(document).on('input change', '.js-qty-row', function() {
                var val = parseFloat($(this).val()) || 0;
                var dimensi = parseFloat(currentItem.dimensi_br) || 1;
                if (dimensi <= 0) dimensi = 1;
                var qtyKecil = val > 0 ? formatInputNumber(val * dimensi) : '';
                $(this).closest('tr').find('.js-qty-kecil-row').val(qtyKecil);
            });

            $(document).on('click', '.js-hapus-row', function() {
                $(this).closest('tr').remove();
                ensureAtLeastOneRow();
            });

            $('#btnReloadTmpTable').on('click', function() {
                reloadSummaryTable();
            });

            $('#btnResetFinalForm').on('click', function() {
                resetFinalForm();
                Swal.fire('Reset Form', 'Field header draft berhasil dikosongkan.', 'success');
            });

            $('#final_jenis_lpb').on('change', function() {
                refreshGeneratedLpbNumber();
            });

            $(document).on('click', '.js-delete-summary-row', function() {
                var idTmpReceived = parseInt($(this).attr('data-id'), 10) || 0;

                if (!idTmpReceived) {
                    Swal.fire('Gagal', 'ID draft tidak valid.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Hapus Baris Draft?',
                    text: 'Baris draft temporary ini akan dihapus.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: '<?= base_url('ics/ajax_delete_tmp_po_received_row') ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_tmp_recieved: idTmpReceived,
                            id_tmp_received: idTmpReceived,
                            no_po: $('#final_no_po').val(),
                            kd_suplier: '<?= htmlspecialchars($kd_suplier ?? '', ENT_QUOTES) ?>'
                        },
                        success: function(res) {
                            if (res.status !== 'success') {
                                Swal.fire('Gagal', res.message || 'Baris draft gagal dihapus.', 'error');
                                return;
                            }

                            reloadSummaryTable();

                            Swal.fire('Berhasil', res.message || 'Baris draft berhasil dihapus.', 'success');
                        },
                        error: function(xhr) {
                            handleAjaxError(xhr, 'Terjadi kesalahan saat menghapus baris draft.');
                        }
                    });
                });
            });

            $('#formTmpPoReceived').on('submit', function(e) {
                e.preventDefault();

                var payloadRows = getPayloadRows();
                var totalQty = 0;
                var hasAnyQty = false;
                var valid = true;

                $.each(payloadRows, function(_, row) {
                    var qty = parseFloat(row.qty_diterima) || 0;

                    if (qty > 0) {
                        hasAnyQty = true;
                        totalQty += qty;

                        if (!row.satuan) {
                            Swal.fire('Validasi', 'Satuan wajib dipilih untuk setiap baris yang memiliki qty.', 'warning');
                            valid = false;
                            return false;
                        }
                    }
                });

                if (!valid) {
                    return;
                }

                var totalQtyKecil = totalQty * (currentItem.dimensi_br || 1);

                if (totalQtyKecil > currentItem.qty_kecil_sisa + 0.00001) {
                    Swal.fire(
                        'Qty Melebihi Sisa',
                        'Total qty draft (' + formatNumber(totalQty) + ' || ' + formatNumber(totalQtyKecil) + ') melebihi Qty Sisa barang (' + formatNumber(currentItem.qty_sisa) + ' || ' + formatNumber(currentItem.qty_kecil_sisa) + ').',
                        'warning'
                    );
                    return;
                }

                $('#btnSimpanTmp').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_save_tmp_po_received') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        kd_po: $('#tmp_kd_po').val(),
                        kd_suplier: $('#tmp_kd_suplier').val(),
                        kd_barang: $('#tmp_kd_barang').val(),
                        rows: payloadRows
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Draft gagal disimpan.', 'error');
                            return;
                        }

                        renderModalRows(res.rows || []);
                        reloadSummaryTable();

                        Swal.fire(
                            'Berhasil',
                            hasAnyQty ? 'Draft penerimaan berhasil disimpan.' : 'Draft untuk barang ini berhasil dikosongkan.',
                            'success'
                        ).then(function() {
                            $('#modalTmpPoReceived').modal('hide');
                        });
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Terjadi kesalahan saat menyimpan draft.');
                    },
                    complete: function() {
                        $('#btnSimpanTmp').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Draft');
                    }
                });
            });

            $('#btnSubmitFinalLpb').on('click', function() {
                if (isSubmittingFinal) {
                    return;
                }

                $('#final_invoice').val('-');
                var invoice = '-';
                var nomorSj = $.trim($('#final_nosj').val());
                var tanggalSj = $('#final_tgl_sj').val();
                var jenisLpb = $('#final_jenis_lpb').val();
                var gudangId = $('#final_gudang_id').val();
                var checkerBy = $('#final_checker_by').length ? $.trim($('#final_checker_by').val()) : '';
                var keterangan = $.trim($('#final_keterangan').val());

                if (!invoice) {
                    Swal.fire('Validasi', 'Nomor invoice wajib diisi.', 'warning');
                    $('#final_invoice').focus();
                    return;
                }

                if (!jenisLpb) {
                    Swal.fire('Validasi', 'Silakan pilih jenis PO / LPB.', 'warning');
                    $('#final_jenis_lpb').focus();
                    return;
                }

                if (!gudangId) {
                    Swal.fire('Validasi', 'Silakan pilih gudang tujuan penerimaan.', 'warning');
                    $('#final_gudang_id').focus();
                    return;
                }

                isSubmittingFinal = true;
                $('#btnSubmitFinalLpb')
                    .prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/ajax_finalize_tmp_po_received') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        no_po: $('#final_no_po').val(),
                        kd_po: $('#final_kd_po').val(),
                        kd_suplier: '<?= htmlspecialchars($kd_suplier ?? '', ENT_QUOTES) ?>',
                        nosj: nomorSj,
                        tgl_sj: tanggalSj,
                        no_invoice: invoice,
                        jenis_lpb: jenisLpb,
                        gudang_id: gudangId,
                        checker_by: checkerBy,
                        checker_name: checkerBy,
                        keterangan: keterangan
                    },
                    success: function(res) {
                        if (res.status !== 'success') {
                            Swal.fire('Gagal', res.message || 'Simpan final gagal.', 'error');
                            return;
                        }

                        resetFinalForm();
                        reloadSummaryTable();

                        Swal.fire({
                            title: 'Berhasil',
                            text: res.message || 'Penerimaan berhasil disimpan.',
                            icon: 'success'
                        });
                    },
                    error: function(xhr) {
                        handleAjaxError(xhr, 'Terjadi kesalahan saat menyimpan penerimaan final.');
                    },
                    complete: function() {
                        isSubmittingFinal = false;
                        $('#btnSubmitFinalLpb')
                            .prop('disabled', false)
                            .html('<i class="fas fa-save mr-1"></i> Simpan');
                    }
                });
            });

            $('#modalTmpPoReceived').on('hidden.bs.modal', function() {
                $('#formTmpPoReceived')[0].reset();
                $('#tmpRowsBody').empty();
            });

            if (!isPurchasingDetailPo) {
                resetFinalForm();
                reloadSummaryTable();
            }
        });
    </script>
</body>
