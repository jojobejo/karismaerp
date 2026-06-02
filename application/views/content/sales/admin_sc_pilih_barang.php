<!-- views/content/sales/admin_sc_pilih_barang.php -->
<style>
    .tax-choice-group {
        display: inline-flex;
        gap: 4px;
    }
    .tax-choice-btn {
        border: 1px solid #ced4da;
        background: #fff;
        color: #495057;
        border-radius: 4px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
        cursor: pointer;
    }
    .tax-choice-btn.active {
        border-color: #007bff;
        background: #007bff;
        color: #fff;
    }
    .tax-choice-btn:disabled {
        opacity: .55;
        cursor: not-allowed;
    }
    .tax-badge {
        display: inline-block;
        margin-top: 3px;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10.5px;
        font-weight: 700;
    }
    .tax-badge-pajak {
        background: #e0f2fe;
        color: #0369a1;
    }
    .tax-badge-non {
        background: #f1f5f9;
        color: #475569;
    }
    tr.tax-hidden {
        display: none;
    }
</style>
<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-7">
                        <h1 class="m-0">
                            <i class="fas fa-boxes mr-2"></i> Pilih Barang Faktur
                        </h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order/admin_sc') ?>">Admin SC</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($so['no_so']) ?></li>
                        </ol>
                    </div>
                </div>
                <a href="<?= base_url('sales_order/admin_sc') ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
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

                <div class="card card-outline card-primary mb-3">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice mr-2"></i><?= htmlspecialchars($so['no_so']) ?>
                        </h3>
                        <div class="card-tools">
                            <?php if (($so['status'] ?? '') === 'partial'): ?>
                                <span class="badge badge-warning">Partial</span>
                            <?php else: ?>
                                <span class="badge badge-light">Siap Faktur</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="small text-muted">Customer</div>
                                <div class="font-weight-bold"><?= htmlspecialchars($so['customer_name'] ?: ($so['nama_customer'] ?? '-')) ?></div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Tanggal SO</div>
                                <div class="font-weight-bold"><?= !empty($so['tanggal_transaksi']) ? date('d/m/Y', strtotime($so['tanggal_transaksi'])) : '-' ?></div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Sales</div>
                                <div class="font-weight-bold"><?= htmlspecialchars($so['create_by'] ?? '-') ?></div>
                            </div>
                            <div class="col-md-2">
                                <div class="small text-muted">Rute</div>
                                <div class="font-weight-bold"><?= htmlspecialchars($so['kd_rute'] ?: ($so['customer_kd_rute'] ?? '-')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="<?= base_url('sales_order/admin_sc/form_faktur/' . $so['id_so']) ?>" method="get" id="formPilihFaktur">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list-ul mr-1"></i> Barang Terverifikasi
                            </h3>
                            <div class="card-tools">
                                <div class="form-inline">
                                    <label class="mr-2 mb-0 small font-weight-bold">Jenis Faktur</label>
                                    <input type="hidden" name="tax_mode" id="taxMode" value="non_pajak">
                                    <div class="tax-choice-group" role="group" aria-label="Jenis Faktur">
                                        <button type="button" class="tax-choice-btn active" data-tax-mode="non_pajak">
                                            Non Pajak
                                        </button>
                                        <button type="button" class="tax-choice-btn" data-tax-mode="pajak">
                                            Pajak
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-hover table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Barang</th>
                                        <th>Lot / Exp</th>
                                        <th class="text-right">Qty Siap Faktur</th>
                                        <th class="text-right">Tidak Terkirim</th>
                                        <th>Catatan Verifikasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($details as $detail):
                                        $available = (float)($detail['qty_available_faktur'] ?? 0);
                                        $tidak = (float)($detail['qty_tidak_terkirim'] ?? 0);
                                        $kd_barang = trim((string)($detail['kd_barang'] ?? ''));
                                        $is_pajak_item = strtoupper(substr($kd_barang, 0, 1)) === 'Q';
                                        $item_tax_mode = $is_pajak_item ? 'pajak' : 'non_pajak';
                                    ?>
                                        <tr data-tax-mode="<?= $item_tax_mode ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($detail['nama_barang']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($kd_barang) ?></small>
                                                <br><span class="tax-badge <?= $is_pajak_item ? 'tax-badge-pajak' : 'tax-badge-non' ?>">
                                                    <?= $is_pajak_item ? 'Pajak 11%' : 'Non Pajak' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php if (!empty($detail['no_lot'])): ?>
                                                        Lot: <code><?= htmlspecialchars($detail['no_lot']) ?></code><br>
                                                    <?php endif; ?>
                                                    Exp: <?= !empty($detail['expired_date']) ? date('d/m/Y', strtotime($detail['expired_date'])) : '-' ?>
                                                </small>
                                            </td>
                                            <td class="text-right font-weight-bold text-success">
                                                <?= number_format($available, 2) ?>
                                            </td>
                                            <td class="text-right <?= $tidak > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                                                <?= number_format($tidak, 2) ?>
                                            </td>
                                            <td>
                                                <?= !empty($detail['verifikasi_loading_note'])
                                                    ? htmlspecialchars($detail['verifikasi_loading_note'])
                                                    : '<span class="text-muted">-</span>' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="text-right mt-2">
                        <button type="submit" class="btn btn-success" id="btnLanjutFaktur" disabled>
                            <i class="fas fa-file-invoice-dollar mr-1"></i> Fakturkan Barang Sesuai Jenis
                        </button>
                    </div>
                </form>
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
$(document).ready(function () {
    function activeTaxMode() {
        return $('#taxMode').val() || 'non_pajak';
    }

    function applyTaxMode(mode) {
        mode = mode === 'pajak' ? 'pajak' : 'non_pajak';
        $('#taxMode').val(mode);
        $('.tax-choice-btn')
            .removeClass('active')
            .filter('[data-tax-mode="' + mode + '"]')
            .addClass('active');

        $('tbody tr[data-tax-mode]').each(function () {
            var match = $(this).data('tax-mode') === mode;
            $(this).toggleClass('tax-hidden', !match);
        });

        updateButton();
    }

    function updateButton() {
        var mode = activeTaxMode();
        var total = $('tbody tr[data-tax-mode="' + mode + '"]').length;
        $('#btnLanjutFaktur').prop('disabled', total < 1);
    }

    $('.tax-choice-btn').on('click', function () {
        applyTaxMode($(this).data('tax-mode'));
    });

    $('#formPilihFaktur').on('submit', function (e) {
        var mode = activeTaxMode();
        if ($('tbody tr[data-tax-mode="' + mode + '"]').length < 1) {
            e.preventDefault();
            alert('Tidak ada barang untuk jenis faktur yang dipilih.');
        }
    });

    applyTaxMode($('#taxMode').val());
});
</script>
