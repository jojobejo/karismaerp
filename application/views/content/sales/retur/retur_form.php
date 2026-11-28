<link rel="stylesheet" href="<?= base_url('assets/dist/css/retur-custom.css') ?>"><?php /* views/content/sales/retur/retur_form.php */ ?>
<style>
    .table-retur th { background: #343a40; color: #fff; font-size: 12px; }
    .table-retur td { font-size: 12px; vertical-align: middle; }
    .field-retur input[readonly] { background: #f5f5f5; color: #555; }
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
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-undo-alt mr-2 text-success"></i> Buat Retur Penjualan</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan/admlpb') ?>">SPR Siap Retur</a></li>
                            <li class="breadcrumb-item active">Buat Retur</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php foreach (['success' => 'success', 'error' => 'danger'] as $k => $c): ?>
                    <?php if ($msg = $this->session->flashdata($k)): ?>
                        <div class="alert alert-<?= $c ?> alert-dismissible">
                            <?= $msg ?>
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php
                $adjStatus = $adjustment_req['status'] ?? '';
                $adjItemsMap = [];
                if (!empty($adjustment_req['items_json'])) {
                    $decodedAdj = json_decode($adjustment_req['items_json'], true) ?: [];
                    foreach ($decodedAdj as $ai) {
                        $adjItemsMap[(int)($ai['id_spr_detail'] ?? 0)] = $ai;
                    }
                }
                ?>

                <?php if (!isset($retur['id_retur']) && !empty($spr['id_spr'])): ?>
                    <?php if ($adjStatus === 'PENDING'): ?>
                        <div class="alert alert-warning shadow-sm d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <i class="fas fa-hourglass-half mr-1"></i>
                                Pengajuan Adjustment <strong><?= htmlspecialchars($adjustment_req['no_request']) ?></strong> sedang menunggu persetujuan <strong>Ahmad (MT Manager Accounting &amp; Tax)</strong>. Adjustment belum dapat diproses sebelum disetujui.
                            </div>
                        </div>
                    <?php elseif ($adjStatus === 'APPROVED'): ?>
                        <div class="alert alert-success shadow-sm">
                            <i class="fas fa-check-circle mr-1"></i>
                            Adjustment <strong><?= htmlspecialchars($adjustment_req['no_request']) ?></strong> telah disetujui oleh <strong><?= htmlspecialchars($adjustment_req['approved_by']) ?></strong>. Data barang di bawah telah disesuaikan dengan adjustment yang disetujui.
                            <?php if (!empty($adjustment_req['approval_note'])): ?>
                                <br><small>Catatan Ahmad: <i><?= htmlspecialchars($adjustment_req['approval_note']) ?></i></small>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($adjStatus === 'REJECTED'): ?>
                        <div class="alert alert-danger shadow-sm d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <i class="fas fa-times-circle mr-1"></i>
                                Pengajuan Adjustment <strong><?= htmlspecialchars($adjustment_req['no_request']) ?></strong> ditolak oleh <strong><?= htmlspecialchars($adjustment_req['approved_by']) ?></strong>:
                                <i><?= htmlspecialchars($adjustment_req['approval_note'] ?? '-') ?></i>
                            </div>
                            <button type="button" class="btn btn-warning btn-sm font-weight-bold mt-2 mt-md-0" data-toggle="modal" data-target="#modalAdjustmentRetur">
                                <i class="fas fa-sliders-h mr-1"></i> Ajukan Adjustment Ulang
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info shadow-sm d-flex justify-content-between align-items-center flex-wrap py-2">
                            <div>
                                <i class="fas fa-info-circle mr-1"></i>
                                Barang datang tidak sesuai SPR? Ajukan adjustment terlebih dahulu untuk persetujuan <strong>Ahmad (MT Manager Accounting &amp; Tax)</strong>.
                            </div>
                            <button type="button" class="btn btn-warning btn-sm font-weight-bold mt-2 mt-md-0" data-toggle="modal" data-target="#modalAdjustmentRetur">
                                <i class="fas fa-sliders-h mr-1"></i> Ajukan Adjustment
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php
                $action_url = isset($retur['id_retur']) 
                    ? base_url('retur_penjualan/retur/update/' . $retur['id_retur']) 
                    : base_url('retur_penjualan/retur/simpan/' . $spr['id_spr']);
                ?>
                <form method="post" action="<?= $action_url ?>">
                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <?php endif; ?>

                    <!-- HEADER INFO -->
                    <div class="card shadow mb-3">
                        <div class="card-header bg-success text-white py-2">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Informasi Retur Penjualan</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">No. Retur</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($no_retur) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">Dari SPR</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['no_spr']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">Tanggal Retur</label>
                                        <div class="col-sm-8">
                                            <input type="date" name="tanggal_retur" class="form-control form-control-sm" value="<?= isset($retur['tanggal_retur']) ? htmlspecialchars($retur['tanggal_retur']) : date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold text-danger">Masukkan Ke Gudang *</label>
                                        <div class="col-sm-8">
                                            <?php if (!empty($is_consignment_return)): ?>
                                                <input type="hidden" name="gudang_id" value="<?= (int)$consignment_warehouse['id_gudang'] ?>">
                                                <input type="text" class="form-control form-control-sm bg-light font-weight-bold"
                                                       value="<?= htmlspecialchars($consignment_warehouse['nama_gudang']) ?>" readonly>
                                                <small class="text-muted">Retur konsinyasi wajib kembali ke gudang konsinyasi.</small>
                                            <?php else: ?>
                                                <select name="gudang_id" class="form-control form-control-sm" required>
                                                    <option value="">-- Pilih Gudang --</option>
                                                    <?php foreach ($gudang_list as $g): ?>
                                                        <option value="<?= $g['id_gudang'] ?>" <?= (isset($retur['gudang_id']) && $retur['gudang_id'] == $g['id_gudang']) ? 'selected' : '' ?>><?= htmlspecialchars($g['nama_gudang']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">Customer</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['nama_customer'] ?: ($spr['nama_customer_master'] ?? '-')) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">Alamat</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['alamat'] ?: ($spr['alamat_master'] ?? '-')) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm font-weight-bold">Sales</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($spr['nama_sales'] ?? '-') ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label class="small font-weight-bold">Catatan (Opsional)</label>
                                        <textarea name="catatan_admlpb" class="form-control form-control-sm" rows="2" placeholder="Catatan tambahan..."><?= isset($retur['catatan_logistik']) ? htmlspecialchars($retur['catatan_logistik']) : '' ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABEL DETAIL BARANG (dari SPR) -->
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white py-2">
                            <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Detail Barang Retur (dari SPR <?= htmlspecialchars($spr['no_spr']) ?>)</h3>
                            <div class="card-tools">
                                <span class="badge badge-light"><?= count($spr_detail) ?> item</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-retur mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:40px;">No.</th>
                                            <th>Nama Barang</th>
                                            <th>Satuan</th>
                                            <th>No. Faktur</th>
                                            <th>No. Batch/Lot</th>
                                            <th>Expired Date</th>
                                            <th class="text-center" style="width:100px;">Qty Retur</th>
                                            <?php $hide_harga = isset($user['jobdesk']) && in_array($user['jobdesk'], ['ADMLPB', 'ADMINLOGLPB'], true); ?>
                                            <th class="text-right <?= $hide_harga ? 'd-none' : '' ?>" style="width:130px;">Harga Satuan</th>
                                            <th class="text-right <?= $hide_harga ? 'd-none' : '' ?>" style="width:140px;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($spr_detail)): ?>
                                            <tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data barang dalam SPR ini</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($spr_detail as $i => $d):
                                                $sid = (int)($d['id_spr_detail'] ?? 0);
                                                $adjItem = ($adjStatus === 'APPROVED' && isset($adjItemsMap[$sid])) ? $adjItemsMap[$sid] : null;
                                                $valBatch = $adjItem ? ($adjItem['no_batch_adj'] ?? '') : ($d['no_batch'] ?? '');
                                                $valExp   = $adjItem ? ($adjItem['expired_date_adj'] ?? '') : ($d['expired_date'] ?? '');
                                                $valQty   = $adjItem ? (float)($adjItem['qty_adj'] ?? 0) : (float)($d['qty'] ?? 0);
                                            ?>
                                            <tr class="item-row">
                                                <td class="text-center"><?= $i + 1 ?></td>
                                                <td>
                                                    <input type="hidden" name="id_spr_detail[]" value="<?= $d['id_spr_detail'] ?>">
                                                    <input type="hidden" name="kd_barang[]" value="<?= htmlspecialchars($d['kd_barang'] ?? '') ?>">
                                                    <input type="text" name="nama_barang[]" class="form-control form-control-sm field-retur"
                                                           value="<?= htmlspecialchars($d['nama_barang'] ?? '') ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="satuan[]" class="form-control form-control-sm field-retur"
                                                           value="<?= htmlspecialchars($d['satuan'] ?? '') ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="no_faktur[]" class="form-control form-control-sm field-retur"
                                                           value="<?= htmlspecialchars($d['no_faktur'] ?? '') ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="no_batch[]" class="form-control form-control-sm field-retur"
                                                           value="<?= htmlspecialchars($valBatch) ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="date" name="expired_date[]" class="form-control form-control-sm field-retur"
                                                           value="<?= $valExp ? date('Y-m-d', strtotime($valExp)) : '' ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" name="qty_retur[]" class="form-control form-control-sm text-right qty-input field-retur"
                                                           value="<?= $valQty ?>" min="0" step="0.001" readonly required>
                                                </td>
                                                <td class="<?= $hide_harga ? 'd-none' : '' ?>">
                                                    <input type="number" name="harga_satuan[]" class="form-control form-control-sm text-right harga-input field-retur"
                                                           value="<?= (float)($d['harga'] ?? 0) ?>" min="0" step="0.01" readonly required>
                                                </td>
                                                <td class="<?= $hide_harga ? 'd-none' : '' ?>">
                                                    <input type="text" class="form-control form-control-sm text-right subtotal-input"
                                                           value="Rp 0" readonly>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <tr class="table-dark <?= $hide_harga ? 'd-none' : '' ?>">
                                            <td colspan="8" class="text-right font-weight-bold">TOTAL NILAI RETUR:</td>
                                            <td class="text-right font-weight-bold" id="grand-total">Rp 0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-secondary mr-2">
                                <i class="fas fa-arrow-left"></i> Batal
                            </a>
                            <?php if (isset($retur['id_retur'])): ?>
                                <button type="submit" name="submit_action" value="update" class="btn btn-primary mr-2" onclick="return confirm('Simpan perubahan Retur Penjualan ini?')">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                                <button type="submit" name="submit_action" value="resubmit" class="btn btn-success" onclick="return confirm('Simpan perubahan dan ajukan kembali Retur Penjualan ini ke Admin Retur?')">
                                    <i class="fas fa-paper-plane"></i> Simpan & Ajukan Kembali
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-success" onclick="return confirm('Simpan Retur Penjualan ini? SPR akan otomatis berubah status menjadi Selesai.')">
                                    <i class="fas fa-save"></i> Simpan Retur Penjualan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    function calculateSubtotal(row) {
                        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                        const harga = parseFloat(row.querySelector('.harga-input').value) || 0;
                        const subtotal = qty * harga;
                        
                        row.querySelector('.subtotal-input').value = 'Rp ' + subtotal.toLocaleString('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2
                        });
                        return subtotal;
                    }

                    function calculateGrandTotal() {
                        let grandTotal = 0;
                        const rows = document.querySelectorAll('.item-row');
                        rows.forEach(row => {
                            grandTotal += calculateSubtotal(row);
                        });
                        document.getElementById('grand-total').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2
                        });
                    }

                    const rows = document.querySelectorAll('.item-row');
                    rows.forEach(row => {
                        // Recalculate on inputs change
                        row.querySelector('.qty-input').addEventListener('input', calculateGrandTotal);
                        row.querySelector('.harga-input').addEventListener('input', calculateGrandTotal);
                    });

                    // Initial calculation
                    calculateGrandTotal();
                });
                </script>

            </div>
        </section>
    </div>

    <?php if (!isset($retur['id_retur']) && !empty($spr['id_spr']) && !in_array($adjStatus, ['PENDING', 'APPROVED'], true)): ?>
    <!-- Modal Pengajuan Adjustment Retur SPR (Persetujuan Ahmad / MT Manager Accounting & Tax) -->
    <div class="modal fade" id="modalAdjustmentRetur" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="<?= base_url('retur_penjualan/retur/request_adjustment/' . $spr['id_spr']) ?>">
                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <?php endif; ?>
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-sliders-h mr-2"></i>Ajukan Adjustment Retur (Approval Ahmad)</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Sesuaikan Qty, Batch/Lot, atau Expired Date sesuai barang fisik yang datang. Pengajuan ini harus disetujui <b>Ahmad (MT Manager Accounting &amp; Tax)</b> sebelum retur dapat disimpan.
                        </div>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Barang</th>
                                        <th class="text-right" style="width:90px;">Qty SPR</th>
                                        <th style="width:110px;">Qty Datang</th>
                                        <th style="width:130px;">Batch/Lot</th>
                                        <th style="width:140px;">Exp Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($spr_detail as $sd):
                                        $sid = (int)$sd['id_spr_detail'];
                                        $sdExp = !empty($sd['expired_date']) ? date('Y-m-d', strtotime($sd['expired_date'])) : '';
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($sd['nama_barang'] ?? '') ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($sd['satuan'] ?? '') ?></small>
                                        </td>
                                        <td class="text-right align-middle"><?= (float)($sd['qty'] ?? 0) ?></td>
                                        <td>
                                            <input type="number" name="adj_qty[<?= $sid ?>]" class="form-control form-control-sm text-right"
                                                   value="<?= (float)($sd['qty'] ?? 0) ?>" min="0" step="0.001" required>
                                        </td>
                                        <td>
                                            <input type="text" name="adj_no_batch[<?= $sid ?>]" class="form-control form-control-sm"
                                                   value="<?= htmlspecialchars($sd['no_batch'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <input type="date" name="adj_expired_date[<?= $sid ?>]" class="form-control form-control-sm"
                                                   value="<?= $sdExp ?>">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold small">Alasan Ketidaksesuaian / Adjustment <span class="text-danger">*</span></label>
                            <textarea name="alasan_adjustment" class="form-control form-control-sm" rows="2" required placeholder="Contoh: Barang yang datang fisik hanya 5 pcs, beda batch..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Pengajuan ke Ahmad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>
