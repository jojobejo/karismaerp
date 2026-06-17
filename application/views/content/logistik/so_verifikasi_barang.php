<!-- views/content/logistik/so_verifikasi_barang.php -->
<style>
    .verify-summary .info-box {
        min-height: 76px;
    }
    .verify-table th,
    .verify-table td {
        vertical-align: middle;
    }
    .verify-table .qty-input {
        max-width: 120px;
        margin-left: auto;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <?php
    $total_order = 0;
    $total_outstanding = 0;
    $total_siap = 0;
    $total_tidak = 0;
    foreach ($details as $detail) {
        $total_order += (float)$detail->qty;
        $total_outstanding += (float)$detail->qty_outstanding;
        $total_siap += (float)$detail->qty_siap_faktur;
        $total_tidak += (float)$detail->qty_tidak_terkirim;
    }
    ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-7">
                        <h1 class="m-0 text-dark" style="font-size:1.3rem;">
                            <i class="fas fa-clipboard-list mr-2"></i>Verifikasi Barang SO
                        </h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('logistik') ?>">Logistik</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('logistik/so_siap_loading?rute=' . rawurlencode($so->kd_rute)) ?>">SO Siap Loading</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($so->no_so) ?></li>
                        </ol>
                    </div>
                </div>

                <a href="<?= base_url('logistik/so_siap_loading?rute=' . rawurlencode($so->kd_rute)) ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <?php if ($this->session->flashdata('msg')): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <?= $this->session->flashdata('msg') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <div class="card card-outline card-primary">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-file-invoice mr-2"></i><?= htmlspecialchars($so->no_so) ?>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-light"><?= htmlspecialchars($so->nama_rute) ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="small text-muted">Customer</div>
                                <div class="font-weight-bold"><?= htmlspecialchars($so->customer_name ?: ($so->nama_customer ?? '-')) ?></div>
                                <?php if (!empty($so->nama_kios)): ?>
                                    <small class="text-muted"><?= htmlspecialchars($so->nama_kios) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted">Tanggal SO</div>
                                <div class="font-weight-bold"><?= !empty($so->tanggal_transaksi) ? date('d/m/Y', strtotime($so->tanggal_transaksi)) : '-' ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-muted">Regional</div>
                                <div class="font-weight-bold"><?= !empty($so->regional) ? htmlspecialchars($so->regional) : '-' ?></div>
                            </div>
                        </div>

                        <!-- <div class="row verify-summary">
                            <div class="col-md-3 col-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-info"><i class="fas fa-boxes"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Order</span>
                                        <span class="info-box-number"><?= number_format($total_order, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Outstanding</span>
                                        <span class="info-box-number"><?= number_format($total_outstanding, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Siap Faktur</span>
                                        <span class="info-box-number" id="summarySiap"><?= number_format($total_siap, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="info-box shadow-sm">
                                    <span class="info-box-icon bg-danger"><i class="fas fa-ban"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tidak Terkirim</span>
                                        <span class="info-box-number" id="summaryTidak"><?= number_format($total_tidak, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <form method="post"
                              action="<?= base_url('logistik/so_siap_loading/verifikasi/' . $so->id_so . '/simpan') ?>"
                              id="formVerifikasiBarang">
                            <input type="hidden" name="current_rute" value="<?= htmlspecialchars($so->kd_rute) ?>">

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm verify-table">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width:40px">No</th>
                                            <th>Barang</th>
                                            <th>Lot / Exp</th>
                                            <th class="text-right">Order</th>
                                            <th class="text-right">Sudah Faktur</th>
                                            <th class="text-right">Outstanding</th>
                                            <th class="text-right">Qty Siap Faktur</th>
                                            <th class="text-right">Tidak Terkirim</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($details as $idx => $detail):
                                            $isi = max(1, (int)($detail->isi_per_box ?? 1));
                                            $outstanding = (float)$detail->qty_outstanding;
                                            $qty_siap = min((float)$detail->qty_siap_faktur, $outstanding);
                                            $qty_tidak = max(0, $outstanding - $qty_siap);
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $idx + 1 ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($detail->nama_barang) ?></strong>
                                                    <br><small class="text-muted"><?= htmlspecialchars($detail->kd_barang) ?></small>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php if (!empty($detail->no_lot)): ?>
                                                            Lot: <code><?= htmlspecialchars($detail->no_lot) ?></code><br>
                                                        <?php endif; ?>
                                                        Exp: <?= !empty($detail->expired_date) ? date('d/m/Y', strtotime($detail->expired_date)) : '-' ?>
                                                    </small>
                                                </td>
                                                <td class="text-right"><?= number_format((float)$detail->qty, 2) ?></td>
                                                <td class="text-right"><?= number_format((float)$detail->qty_faktur, 2) ?></td>
                                                <td class="text-right font-weight-bold text-warning">
                                                    <?= number_format($outstanding, 2) ?>
                                                </td>
                                                <td class="text-right">
                                                    <input type="hidden" name="id_so_detail[]" value="<?= (int)$detail->id_so_detail ?>">
                                                    <input type="number"
                                                           name="qty_siap_faktur[]"
                                                           class="form-control form-control-sm text-right qty-input qty-siap"
                                                           value="<?= $qty_siap ?>"
                                                           min="0"
                                                           max="<?= $outstanding ?>"
                                                           step="0.001"
                                                           data-outstanding="<?= $outstanding ?>">
                                                    <small class="text-muted">Maks <?= number_format($outstanding, 2) ?> pcs</small>
                                                </td>
                                                <td class="text-right">
                                                    <span class="font-weight-bold text-danger qty-tidak"><?= number_format($qty_tidak, 2) ?></span>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="verifikasi_loading_note[]"
                                                           class="form-control form-control-sm"
                                                           value="<?= htmlspecialchars($detail->verifikasi_loading_note ?? '') ?>"
                                                           placeholder="Opsional">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-right mt-3">
                                <a href="<?= base_url('logistik/so_siap_loading?rute=' . rawurlencode($so->kd_rute)) ?>" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save mr-1"></i>Simpan Verifikasi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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
    function formatNumber(value) {
        return (Number(value) || 0).toLocaleString('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function recalc() {
        var totalSiap = 0;
        var totalTidak = 0;

        $('.qty-siap').each(function () {
            var $input = $(this);
            var outstanding = parseFloat($input.data('outstanding')) || 0;
            var qtySiap = parseFloat($input.val()) || 0;

            if (qtySiap < 0) qtySiap = 0;
            if (qtySiap > outstanding) qtySiap = outstanding;
            $input.val(qtySiap);

            var qtyTidak = Math.max(0, outstanding - qtySiap);
            totalSiap += qtySiap;
            totalTidak += qtyTidak;
            $input.closest('tr').find('.qty-tidak').text(formatNumber(qtyTidak));
        });

        $('#summarySiap').text(formatNumber(totalSiap));
        $('#summaryTidak').text(formatNumber(totalTidak));
    }

    $('.qty-siap').on('input change', recalc);
    $('#formVerifikasiBarang').on('submit', function () {
        recalc();
    });
    recalc();
});
</script>
