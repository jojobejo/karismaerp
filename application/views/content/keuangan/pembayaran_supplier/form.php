<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <?php
        $defaultAmount = 0;
        foreach ($documents as $doc) {
            $defaultAmount += (float)$doc['outstanding'];
        }
    ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-7">
                        <h1 class="m-0"><i class="fas fa-money-check-alt mr-2"></i>Form Pembayaran Supplier</h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('keuangan/pembayaran-supplier') ?>">Pembayaran Supplier</a></li>
                            <li class="breadcrumb-item active">Form</li>
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
                    <div class="alert alert-warning">Tidak ada dokumen hutang terbuka untuk dibayar.</div>
                <?php elseif (empty($cash_bank_accounts)): ?>
                    <div class="alert alert-danger">Akun kas/bank posting belum tersedia.</div>
                <?php else: ?>
                    <form method="post" action="<?= base_url('keuangan/pembayaran-supplier/post') ?>" id="formBayarSupplier">
                        <input type="hidden" name="id_supplier" value="<?= (int)$supplier['id_suplier'] ?>">

                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-receipt mr-1"></i>Header Pembayaran</h3>
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
                                            <label>No Pembayaran</label>
                                            <input type="text" name="nomor_pembayaran" class="form-control" placeholder="Auto jika kosong">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Akun Kas/Bank</label>
                                            <select name="id_akun_kas_bank" class="form-control select2" required>
                                                <option value="">Pilih akun</option>
                                                <?php foreach ($cash_bank_accounts as $account): ?>
                                                    <option value="<?= (int)$account['id_akun'] ?>">
                                                        <?= htmlspecialchars($account['kode_akun'] . ' - ' . ($account['nama_akun'] ?: $account['tipe_kontrol'])) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Nominal Pembayaran</label>
                                            <input type="number" name="amount" id="paymentAmount" class="form-control text-right" min="0" step="0.01" value="<?= number_format($defaultAmount, 2, '.', '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <input type="text" name="keterangan" class="form-control" value="Pembayaran supplier <?= htmlspecialchars($supplier['nama_suplier'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Alokasi Dokumen</h3>
                                <div class="card-tools">
                                    <span class="badge badge-light">Total Alokasi: <span id="totalAllocationLabel">Rp 0</span></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Dokumen</th>
                                            <th>PO / Invoice</th>
                                            <th class="text-right">Sisa Hutang</th>
                                            <th class="text-right">Alokasi</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documents as $doc):
                                            $meta = $doc['lpb_meta'] ?? [];
                                            $sourceId = $meta['id_lpb_list'] ?? '';
                                        ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="invoice_no[]" value="<?= htmlspecialchars($doc['nomor_dokumen']) ?>">
                                                    <input type="hidden" name="invoice_source_id[]" value="<?= htmlspecialchars($sourceId) ?>">
                                                    <strong><?= htmlspecialchars($doc['nomor_dokumen']) ?></strong>
                                                    <br><small class="text-muted">LPB ID: <?= htmlspecialchars($sourceId ?: '-') ?></small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($meta['no_po_list'] ?? '-') ?>
                                                    <br><small class="text-muted">Invoice: <?= htmlspecialchars($meta['no_invoice_list'] ?? '-') ?></small>
                                                </td>
                                                <td class="text-right">
                                                    Rp <?= number_format((float)$doc['outstanding'], 0, ',', '.') ?>
                                                </td>
                                                <td>
                                                    <input type="number" name="amount_allocated[]" class="form-control form-control-sm text-right allocation-input" min="0" max="<?= htmlspecialchars($doc['outstanding']) ?>" step="0.01" value="<?= number_format((float)$doc['outstanding'], 2, '.', '') ?>">
                                                </td>
                                                <td>
                                                    <input type="text" name="allocation_note[]" class="form-control form-control-sm" placeholder="Catatan alokasi">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check mr-1"></i>Posting Pembayaran
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
    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    }

    function money(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    function refreshTotal() {
        var total = 0;
        $('.allocation-input').each(function () {
            total += parseFloat($(this).val() || 0);
        });
        $('#totalAllocationLabel').text(money(total));
    }

    $('.allocation-input').on('input', refreshTotal);
    refreshTotal();

    $('#formBayarSupplier').on('submit', function (e) {
        var amount = parseFloat($('#paymentAmount').val() || 0);
        var total = 0;
        $('.allocation-input').each(function () {
            total += parseFloat($(this).val() || 0);
        });
        if (Math.round(amount * 100) !== Math.round(total * 100)) {
            e.preventDefault();
            alert('Nominal pembayaran harus sama dengan total alokasi.');
        }
    });
});
</script>
