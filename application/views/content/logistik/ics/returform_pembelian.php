<style>
    .select2-container { width: 100% !important; }
    .return-table th, .return-table td { vertical-align: middle !important; }
    .return-table input[type="number"] { min-width: 90px; }
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
                            <a href="<?= base_url('ics/retur/pembelian/monitoring') ?>" class="btn btn-info"><i class="fas fa-tasks"></i> Progres/Monitor Retur</a>
                        </div>
                        <div class="col-auto">
                            <a href="<?= base_url('ics/retur/pembelian/adjustment') ?>" class="btn btn-warning"><i class="fas fa-balance-scale"></i> Adjustment Harga LPB</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <strong>Retur Pembelian dari LPB Final</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Supplier / Nomor PO / Nomor LPB</label>
                                        <select id="select_lpb_retur" class="form-control"></select>
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal Retur</label>
                                        <input type="date" id="tanggal_retur" class="form-control" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Jenis Penyelesaian</label>
                                        <select id="jenis_penyelesaian" class="form-control">
                                            <option value="POTONG_HUTANG">Potong Hutang Usaha</option>
                                            <option value="KLAIM_SUPPLIER">Klaim Supplier / Menunggu Refund</option>
                                            <option value="REFUND_KAS_BANK">Refund Kas / Bank</option>
                                            <option value="REPLACEMENT">Barang Pengganti</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Alasan Retur</label>
                                        <textarea id="alasan_retur" class="form-control" rows="4"></textarea>
                                    </div>
                                    <button type="button" id="btn_create_draft_retur" class="btn btn-success btn-block">
                                        <i class="fas fa-save"></i> Buat Draft Retur
                                    </button>
                                </div>

                                <div class="col-lg-8">
                                    <div class="table-responsive">
                                        <table id="lpb_return_detail_table" class="table table-bordered table-sm return-table">
                                            <thead class="thead-light text-center">
                                                <tr>
                                                    <th>Kode Barang</th>
                                                    <th>Nama Barang</th>
                                                    <th>No Lot</th>
                                                    <th>Expired</th>
                                                    <th>Qty LPB</th>
                                                    <th>Retur Sebelumnya</th>
                                                    <th>Stok Fisik</th>
                                                    <th>Harga</th>
                                                    <th>Qty Retur</th>
                                                    <th>Alasan Item</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="10" class="text-center text-muted">Pilih LPB final terlebih dahulu.</td>
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
                            <strong>Daftar Retur Pembelian</strong>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="retur_pembelian_table" class="table table-bordered table-striped table-sm">
                                    <thead class="bg-primary text-white text-center">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>No Retur</th>
                                            <th>Nomor LPB</th>
                                            <th>PO</th>
                                            <th>Supplier</th>
                                            <th>Item</th>
                                            <th>DPP</th>
                                            <th>PPN</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (($retur_pembelian_rows ?? []) as $row) : ?>
                                            <tr>
                                                <td><?= html_escape($row['tanggal_retur']) ?></td>
                                                <td><?= html_escape($row['no_retur_pembelian']) ?></td>
                                                <td><?= html_escape($row['nomor_lpb'] ?: '-') ?></td>
                                                <td><?= html_escape($row['no_po'] ?: $row['kd_po']) ?></td>
                                                <td><?= html_escape($row['nama_suplier'] ?: $row['kd_supplier']) ?></td>
                                                <td class="text-center"><?= (int)$row['total_item'] ?></td>
                                                <td class="text-right"><?= number_format((float)$row['total_dpp'], 2, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float)$row['total_ppn'], 2, ',', '.') ?></td>
                                                <td class="text-right"><?= number_format((float)$row['grand_total'], 2, ',', '.') ?></td>
                                                <td class="text-center">
                                                    <?php
                                                    $badge = 'secondary';
                                                    if ($row['status'] === 'POSTED') $badge = 'success';
                                                    if (in_array($row['status'], ['DRAFT', 'SUBMITTED'], true)) $badge = 'warning';
                                                    if (in_array($row['status'], ['PURCHASING_VERIFIED', 'ACCOUNTING_VERIFIED'], true)) $badge = 'info';
                                                    if (in_array($row['status'], ['VOID', 'POSTING_EXCEPTION'], true)) $badge = 'danger';
                                                    ?>
                                                    <span class="badge badge-<?= $badge ?> status-pill"><?= html_escape($row['status']) ?></span>
                                                </td>
                                                <td class="text-nowrap text-center">
                                                    <?php if ($row['status'] === 'DRAFT') : ?>
                                                        <button type="button" class="btn btn-sm btn-primary js-retur-action" data-action="submit" data-id="<?= (int)$row['id_retur_pembelian'] ?>">Submit</button>
                                                    <?php endif; ?>
                                                    <?php if (in_array($row['status'], ['DRAFT', 'SUBMITTED'], true)) : ?>
                                                        <button type="button" class="btn btn-sm btn-info js-retur-action" data-action="verify_purchasing" data-id="<?= (int)$row['id_retur_pembelian'] ?>">Purchasing</button>
                                                    <?php endif; ?>
                                                    <?php if ($row['status'] === 'PURCHASING_VERIFIED') : ?>
                                                        <button type="button" class="btn btn-sm btn-info js-retur-action" data-action="verify_accounting" data-id="<?= (int)$row['id_retur_pembelian'] ?>">Accounting</button>
                                                    <?php endif; ?>
                                                    <?php if ($row['status'] === 'ACCOUNTING_VERIFIED') : ?>
                                                        <button type="button" class="btn btn-sm btn-success js-retur-action" data-action="post" data-id="<?= (int)$row['id_retur_pembelian'] ?>">Post</button>
                                                    <?php endif; ?>
                                                    <?php if ($row['status'] === 'POSTED') : ?>
                                                        <button type="button" class="btn btn-sm btn-danger js-retur-action" data-action="void" data-id="<?= (int)$row['id_retur_pembelian'] ?>">Void</button>
                                                    <?php endif; ?>
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
