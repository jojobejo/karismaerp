<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <?php
        $totalDebt = 0;
        foreach ($documents as $doc) {
            $totalDebt += (float)$doc['outstanding'];
        }
        $totalReturn = 0;
        foreach ($return_credits as $credit) {
            $totalReturn += (float)$credit['available_amount'];
        }
        $defaultTarget = min($totalDebt, $totalReturn);
        $remainingDebtDefault = $defaultTarget;
        $remainingReturnDefault = $defaultTarget;
    ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-7">
                        <h1 class="m-0"><i class="fas fa-cut mr-2"></i>Potong Hutang Retur Pembelian</h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('keuangan/pembayaran-supplier') ?>">Pembayaran Supplier</a></li>
                            <li class="breadcrumb-item active">Potong Retur</li>
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
                    <a href="<?= base_url('keuangan/pembayaran-supplier/supplier/' . (int)$supplier['id_suplier']) ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                </div>

                <?php if (empty($documents)): ?>
                    <div class="alert alert-warning">Tidak ada dokumen hutang terbuka untuk dipotong.</div>
                <?php elseif (empty($return_credits)): ?>
                    <div class="alert alert-warning">Tidak ada dokumen retur pembelian dengan saldo 13013 yang masih bisa dipotong.</div>
                <?php else: ?>
                    <form method="post" action="<?= base_url('keuangan/pembayaran-supplier/post-potong-retur') ?>" id="formPotongRetur">
                        <input type="hidden" name="id_supplier" value="<?= (int)$supplier['id_suplier'] ?>">

                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-receipt mr-1"></i>Header Potong Hutang</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Supplier</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($supplier['nama_suplier'] ?? '-') ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Tanggal</label>
                                            <input type="date" name="tanggal_pembayaran" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>No Potong Hutang</label>
                                            <input type="text" name="nomor_pembayaran" class="form-control" placeholder="Auto jika kosong">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Total Dipotong</label>
                                            <input type="text" id="deductionTotal" class="form-control text-right" value="Rp 0" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label>Keterangan</label>
                                    <input type="text" name="keterangan" class="form-control" value="Potong hutang retur pembelian <?= htmlspecialchars($supplier['nama_suplier'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h3 class="card-title"><i class="fas fa-file-invoice mr-2"></i>Dokumen Hutang Terbuka</h3>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Dokumen</th>
                                                    <th class="text-right">Sisa Hutang</th>
                                                    <th class="text-right">Dipotong</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($documents as $doc):
                                                    $debtDefault = min((float)$doc['outstanding'], $remainingDebtDefault);
                                                    $remainingDebtDefault -= $debtDefault;
                                                    $meta = $doc['lpb_meta'] ?? [];
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" name="invoice_no[]" value="<?= htmlspecialchars($doc['nomor_dokumen']) ?>">
                                                            <input type="hidden" name="invoice_source_id[]" value="<?= htmlspecialchars($meta['id_lpb_list'] ?? '') ?>">
                                                            <strong><?= htmlspecialchars($doc['nomor_dokumen']) ?></strong>
                                                            <br><small class="text-muted"><?= htmlspecialchars($meta['no_po_list'] ?? '-') ?></small>
                                                        </td>
                                                        <td class="text-right">Rp <?= number_format((float)$doc['outstanding'], 0, ',', '.') ?></td>
                                                        <td>
                                                            <input type="number" name="amount_allocated[]" class="form-control form-control-sm text-right debt-input" min="0" max="<?= htmlspecialchars($doc['outstanding']) ?>" step="0.01" value="<?= number_format($debtDefault, 2, '.', '') ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header bg-warning">
                                        <h3 class="card-title"><i class="fas fa-undo-alt mr-2"></i>Dokumen Retur Digunakan</h3>
                                    </div>
                                    <div class="card-body table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>No Retur</th>
                                                    <th class="text-right">Sisa Retur</th>
                                                    <th class="text-right">Dipakai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($return_credits as $credit):
                                                    $returnDefault = min((float)$credit['available_amount'], $remainingReturnDefault);
                                                    $remainingReturnDefault -= $returnDefault;
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" name="return_no[]" value="<?= htmlspecialchars($credit['nomor_dokumen']) ?>">
                                                            <input type="hidden" name="return_source_id[]" value="<?= htmlspecialchars($credit['source_id'] ?? '') ?>">
                                                            <strong><?= htmlspecialchars($credit['nomor_dokumen']) ?></strong>
                                                            <br><small class="text-muted"><?= !empty($credit['tanggal_retur']) ? date('d/m/Y', strtotime($credit['tanggal_retur'])) : '-' ?></small>
                                                        </td>
                                                        <td class="text-right">Rp <?= number_format((float)$credit['available_amount'], 0, ',', '.') ?></td>
                                                        <td>
                                                            <input type="number" name="return_amount_allocated[]" class="form-control form-control-sm text-right return-input" min="0" max="<?= htmlspecialchars($credit['available_amount']) ?>" step="0.01" value="<?= number_format($returnDefault, 2, '.', '') ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-footer text-right">
                                <span class="mr-3">Hutang: <strong id="debtTotalLabel">Rp 0</strong></span>
                                <span class="mr-3">Retur: <strong id="returnTotalLabel">Rp 0</strong></span>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-check mr-1"></i>Posting Potong Hutang
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>

<script>
$(function () {
    function rupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    function sumInputs(selector) {
        var total = 0;
        $(selector).each(function () {
            total += parseFloat($(this).val() || 0);
        });
        return total;
    }

    function refreshTotal() {
        var debtTotal = sumInputs('.debt-input');
        var returnTotal = sumInputs('.return-input');
        $('#debtTotalLabel').text(rupiah(debtTotal));
        $('#returnTotalLabel').text(rupiah(returnTotal));
        $('#deductionTotal').val(rupiah(Math.min(debtTotal, returnTotal)));
    }

    $('.debt-input, .return-input').on('input', refreshTotal);
    refreshTotal();

    $('#formPotongRetur').on('submit', function (e) {
        var debtTotal = sumInputs('.debt-input');
        var returnTotal = sumInputs('.return-input');
        if (debtTotal <= 0 || returnTotal <= 0) {
            e.preventDefault();
            alert('Nominal hutang dan retur harus lebih dari nol.');
            return;
        }
        if (Math.round(debtTotal * 100) !== Math.round(returnTotal * 100)) {
            e.preventDefault();
            alert('Total dokumen hutang yang dipotong harus sama dengan total retur yang dipakai.');
        }
    });
});
</script>
</body>
