<style>
    .select2-container { width: 100% !important; }
    .adjustment-table th, .adjustment-table td { vertical-align: middle !important; }
    .adjustment-table input[type="number"] { min-width: 120px; }
    .status-pill { font-size: 11px; letter-spacing: 0; }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">
                    <div class="row mb-2">
                        <div class="col-auto">
                            <a href="<?= base_url('ics/retur') ?>" class="btn btn-primary"><i class="fas fa-home"></i></a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/retur/pembelian') ?>" class="btn btn-info"><i class="fas fa-truck-loading"></i> Retur Pembelian</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <strong>Adjustment Harga LPB</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>LPB Salah</label>
                                        <select id="select_lpb_adjustment" class="form-control"></select>
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal Adjustment</label>
                                        <input type="date" id="tanggal_adjustment" class="form-control" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Nomor Lot</label>
                                        <input type="text" class="form-control" value="Adj. Harga Beli" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Expired Date</label>
                                        <input type="text" class="form-control" value="01/01/1000" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Alasan Adjustment</label>
                                        <textarea id="alasan_adjustment" class="form-control" rows="4"></textarea>
                                    </div>
                                    <button type="button" id="btn_post_adjustment_lpb" class="btn btn-success btn-block">
                                        <i class="fas fa-check-circle"></i> Posting Adjustment
                                    </button>
                                </div>

                                <div class="col-lg-8">
                                    <div class="table-responsive">
                                        <table id="lpb_adjustment_detail_table" class="table table-bordered table-sm adjustment-table">
                                            <thead class="thead-light text-center">
                                                <tr>
                                                    <th>Kode Barang</th>
                                                    <th>Nama Barang</th>
                                                    <th>Qty</th>
                                                    <th>Harga LPB Salah</th>
                                                    <th>Harga Invoice Benar</th>
                                                    <th>Selisih / Satuan</th>
                                                    <th>Kelompok</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted">Pilih LPB salah terlebih dahulu.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <strong>Daftar Adjustment Harga LPB</strong>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="adjustment_lpb_table" class="table table-bordered table-striped table-sm">
                                    <thead class="bg-primary text-white text-center">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>No Adjustment</th>
                                            <th>LPB Salah</th>
                                            <th>LPB Adjustment</th>
                                            <th>PRPP</th>
                                            <th>Supplier</th>
                                            <th>Selisih DPP</th>
                                            <th>Selisih PPN</th>
                                            <th>Selisih Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (($adjustment_rows ?? []) as $row) : ?>
                                            <tr>
                                                <td><?= html_escape($row['tanggal_adjustment']) ?></td>
                                                <td><?= html_escape($row['no_adjustment']) ?></td>
                                                <td><?= html_escape($row['nomor_lpb_salah']) ?></td>
                                                <td><?= html_escape($row['nomor_lpb_adjustment'] ?: '-') ?></td>
                                                <td><?= html_escape($row['no_retur_pembelian'] ?: '-') ?></td>
                                                <td><?= html_escape($row['nama_suplier'] ?: $row['kd_supplier']) ?></td>
                                                <td class="text-right"><?= number_format((float)$row['selisih_dpp'], 2, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float)$row['selisih_ppn'], 2, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float)$row['selisih_total'], 2, ',', '.') ?></td>
                                                <td class="text-center">
                                                    <span class="badge badge-<?= $row['status'] === 'POSTED' ? 'success' : 'warning' ?> status-pill"><?= html_escape($row['status']) ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>
