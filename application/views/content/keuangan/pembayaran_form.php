<body class="hold-transition sidebar-mini sidebar-collapse">
<?php
$pending_bg = $pending_bg ?? null;
$is_bg_cair_mode = !empty($pending_bg);
$metode_options = [
    'cash' => 'Cash',
    'transfer' => 'Transfer',
    'bg' => 'BG',
    'retur' => 'Saldo Retur',
];
$default_metode = strtolower((string)($faktur['cara_pembayaran'] ?? ''));
if (!isset($metode_options[$default_metode])) {
    $default_metode = 'cash';
}
?>
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
                        <h1 class="m-0"><i class="fas fa-money-check-alt mr-2"></i>Input Pembayaran</h1>
                    </div>
                    <div class="col-sm-5">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('keuangan/pembayaran') ?>">Pembayaran</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($faktur['no_faktur']) ?></li>
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
                    <a href="<?= base_url('keuangan/pembayaran/customer/' . rawurlencode($faktur['kd_customer'])) ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Detail Customer
                    </a>
                </div>

                <?php if (!empty($linked_returs)): ?>
                    <div class="alert alert-info shadow-sm border-info mb-4" style="background-color: #e8f4fd; color: #0c5460; border-left: 5px solid #17a2b8;">
                        <h5 class="font-weight-bold mb-2"><i class="fas fa-info-circle mr-2"></i>Informasi Pemotongan Retur dari Collection</h5>
                        <p class="mb-2">Collection telah menentukan bahwa Faktur ini dapat dipotong menggunakan Retur Penjualan berikut:</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-2 bg-white text-dark small" style="border-radius: 4px; overflow: hidden;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No. Retur</th>
                                        <th>Tipe Retur</th>
                                        <th>Tanggal Retur</th>
                                        <th>Status Retur</th>
                                        <th class="text-right">Nominal Retur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($linked_returs as $ret): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('retur_penjualan/retur/detail/' . $ret['no_retur']) ?>" class="font-weight-bold" target="_blank">
                                                    <?= htmlspecialchars($ret['no_retur']) ?> <i class="fas fa-external-link-alt ml-1" style="font-size: 0.8em;"></i>
                                                </a>
                                            </td>
                                            <td><span class="badge badge-secondary"><?= htmlspecialchars(ucfirst($ret['tipe_retur'])) ?></span></td>
                                            <td><?= date('d/m/Y', strtotime($ret['tanggal_retur'])) ?></td>
                                            <td>
                                                <?php
                                                $lbl = $ret['status_retur'];
                                                if ($lbl === 'menunggu_collection') $lbl = 'Menunggu Collection';
                                                elseif ($lbl === 'menunggu_kasir') $lbl = 'Menunggu Kasir (Serah Terima)';
                                                elseif ($lbl === 'selesai') $lbl = 'Selesai';
                                                ?>
                                                <span class="badge badge-info"><?= htmlspecialchars($lbl) ?></span>
                                            </td>
                                            <td class="text-right font-weight-bold text-success">Rp <?= number_format((float)$ret['total_retur'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <span class="small text-muted font-italic">
                            <i class="fas fa-lightbulb mr-1"></i> Tips: Silakan pilih metode pembayaran <strong>Saldo Retur</strong> di form sebelah kanan untuk memotong tagihan menggunakan retur di atas.
                        </span>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-5">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-file-invoice mr-1"></i>Ringkasan Faktur</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="42%">No Faktur</td>
                                        <td><strong><?= htmlspecialchars($faktur['no_faktur']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tanggal Faktur</td>
                                        <td><?= !empty($faktur['tanggal_faktur']) ? date('d/m/Y', strtotime($faktur['tanggal_faktur'])) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Customer</td>
                                        <td><?= htmlspecialchars($faktur['nama_customer']) ?></td>
                                    </tr>
                                    <?php if (!empty($faktur['nama_barang']) && $faktur['nama_barang'] !== '-'): ?>
                                    <tr>
                                        <td class="text-muted">Barang</td>
                                        <td><small class="text-muted d-block text-wrap" style="max-width: 250px; line-height: 1.2;"><?= htmlspecialchars($faktur['nama_barang']) ?></small></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="text-muted">Cara Pembayaran</td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars(ucfirst($faktur['cara_pembayaran'] ?: '-')) ?></span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Status Overdue</td>
                                        <td><span class="badge badge-<?= $faktur['status_overdue'] === 'Belum overdue' ? 'secondary' : ($faktur['status_overdue'] === 'Overdue 30' ? 'warning' : 'danger') ?>"><?= htmlspecialchars($faktur['status_overdue']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Total Tagihan</td>
                                        <td>Rp <?= number_format((float)$faktur['total_tagihan'], 0, ',', '.') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Total Pembayaran</td>
                                        <td>Rp <?= number_format((float)$faktur['total_pembayaran'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php if (!empty($faktur['total_bg_pending'])): ?>
                                    <tr>
                                        <td class="text-muted">BG Belum Cair</td>
                                        <td><strong class="text-warning">Rp <?= number_format((float)$faktur['total_bg_pending'], 0, ',', '.') ?></strong></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="text-muted">Sisa Tagihan</td>
                                        <td><strong class="text-danger">Rp <?= number_format((float)$faktur['sisa_tagihan'], 0, ',', '.') ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-history mr-1"></i>Histori Pembayaran</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Metode</th>
                                            <th>Status</th>
                                            <th>BG Cair</th>
                                            <th class="text-right">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($history)): ?>
                                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada pembayaran.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($history as $row): ?>
                                                <?php
                                                $is_bg_row = strtolower((string)($row['metode_pembayaran'] ?? '')) === 'bg';
                                                $is_bg_cair = ($row['status_bg'] ?? '') === 'cair';
                                                ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($row['tanggal_pembayaran'])) ?></td>
                                                    <td><?= htmlspecialchars($row['metode_pembayaran'] ?: '-') ?></td>
                                                    <td>
                                                        <?php if ($is_bg_row): ?>
                                                            <span class="badge badge-<?= $is_bg_cair ? 'success' : 'warning' ?>">
                                                                <?= $is_bg_cair ? 'Sudah Cair' : 'Belum Cair' ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success">Masuk Tagihan</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?= !empty($row['tanggal_bg_cair']) ? date('d/m/Y', strtotime($row['tanggal_bg_cair'])) : '-' ?>
                                                        <?php if ($is_bg_cair && !empty($row['bg_cair_at'])): ?>
                                                            <br><small class="text-muted">Dikonfirmasi <?= date('d/m/Y H:i', strtotime($row['bg_cair_at'])) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-right">Rp <?= number_format((float)$row['jumlah_pembayaran'], 0, ',', '.') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas <?= $is_bg_cair_mode ? 'fa-check-circle' : 'fa-plus-circle' ?> mr-1"></i>
                                    <?= $is_bg_cair_mode ? 'Konfirmasi BG Sudah Cair' : 'Form Pembayaran' ?>
                                </h3>
                            </div>
                            <form action="<?= $is_bg_cair_mode ? base_url('keuangan/pembayaran/cair/' . $pending_bg['id_pembayaran']) : base_url('keuangan/pembayaran/simpan/' . $faktur['id_faktur']) ?>"
                                  method="post"
                                  <?= $is_bg_cair_mode ? "onsubmit=\"return confirm('Tandai BG ini sudah cair dan kurangi sisa tagihan?');\"" : '' ?>>
                                <div class="card-body">
                                    <?php if ($is_bg_cair_mode): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Pembayaran BG ini sudah dicatat tetapi belum mengurangi tagihan. Klik tombol <strong>BG Sudah Cair</strong> untuk memasukkannya ke total pembayaran.
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-group">
                                        <label>Metode Pembayaran <span class="text-danger">*</span></label>
                                        <?php if ($is_bg_cair_mode): ?>
                                            <input type="text" class="form-control" value="BG" readonly>
                                        <?php else: ?>
                                             <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                                                 <?php foreach ($metode_options as $value => $label): ?>
                                                     <?php
                                                     $disabled_attr = '';
                                                     if ($value === 'retur' && (float)$saldo_retur <= 0) {
                                                         $disabled_attr = 'disabled';
                                                         $label .= ' (Tidak ada saldo)';
                                                     }
                                                     ?>
                                                     <option value="<?= htmlspecialchars($value) ?>" <?= $default_metode === $value ? 'selected' : '' ?> <?= $disabled_attr ?>>
                                                         <?= htmlspecialchars($label) ?>
                                                     </option>
                                                 <?php endforeach; ?>
                                             </select>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group">
                                        <label>Tanggal Pembayaran <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_pembayaran" class="form-control"
                                               value="<?= $is_bg_cair_mode ? htmlspecialchars($pending_bg['tanggal_pembayaran']) : date('Y-m-d') ?>"
                                               <?= $is_bg_cair_mode ? 'readonly' : 'required' ?>>
                                    </div>
                                    <div class="form-group">
                                        <label>Jumlah Pembayaran <span class="text-danger">*</span></label>
                                        <input type="number" name="jumlah_pembayaran" class="form-control" min="1"
                                               max="<?= (float)$faktur['sisa_tagihan'] ?>" step="0.01"
                                               value="<?= $is_bg_cair_mode ? (float)$pending_bg['jumlah_pembayaran'] : (float)$faktur['sisa_tagihan'] ?>"
                                               <?= $is_bg_cair_mode ? 'readonly' : 'required' ?>>
                                        <?php if (!$is_bg_cair_mode): ?>
                                            <small class="text-muted">Maksimal Rp <?= number_format((float)$faktur['sisa_tagihan'], 0, ',', '.') ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group" id="tanggal_bg_cair_group" style="<?= (!$is_bg_cair_mode && $default_metode !== 'bg') ? 'display:none' : '' ?>">
                                        <label>Tanggal BG Cair <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_bg_cair" class="form-control"
                                               value="<?= $is_bg_cair_mode ? htmlspecialchars($pending_bg['tanggal_bg_cair']) : date('Y-m-d') ?>"
                                               <?= $is_bg_cair_mode || $default_metode === 'bg' ? ($is_bg_cair_mode ? 'readonly' : 'required') : '' ?>>
                                        <small class="text-muted">Pembayaran BG belum mengurangi tagihan sampai tombol BG Sudah Cair diklik.</small>
                                    </div>
                                    <div class="form-group" id="saldo_retur_group" style="display:none;">
                                        <label>Saldo Retur Customer</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">Rp</span>
                                            </div>
                                            <input type="text" class="form-control" id="input_saldo_retur" value="<?= number_format($saldo_retur, 0, ',', '.') ?>" readonly>
                                        </div>
                                        <small class="text-muted">Saldo retur customer yang tersedia untuk pemotongan faktur ini.</small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Keterangan</label>
                                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Nomor referensi / catatan pembayaran" <?= $is_bg_cair_mode ? 'readonly' : '' ?>><?= $is_bg_cair_mode ? htmlspecialchars($pending_bg['keterangan'] ?? '') : '' ?></textarea>
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas <?= $is_bg_cair_mode ? 'fa-check' : 'fa-save' ?> mr-1"></i>
                                        <?= $is_bg_cair_mode ? 'BG Sudah Cair' : 'Simpan Pembayaran' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var metode = document.getElementById('metode_pembayaran');
    var bgGroup = document.getElementById('tanggal_bg_cair_group');
    var returGroup = document.getElementById('saldo_retur_group');
    var jumlahInput = document.querySelector('input[name="jumlah_pembayaran"]');
    var sisaTagihan = <?= (float)$faktur['sisa_tagihan'] ?>;
    var saldoRetur = <?= (float)$saldo_retur ?>;

    if (!metode) return;

    var bgDate = bgGroup ? bgGroup.querySelector('input[name="tanggal_bg_cair"]') : null;

    function handleMetodeChange() {
        var val = metode.value;
        
        // Toggle BG Group
        if (bgGroup) {
            var isBg = val === 'bg';
            bgGroup.style.display = isBg ? '' : 'none';
            if (bgDate) bgDate.required = isBg;
        }

        // Toggle Retur Group
        if (returGroup) {
            var isRetur = val === 'retur';
            returGroup.style.display = isRetur ? '' : 'none';
            
            if (isRetur && jumlahInput) {
                var maxLimit = Math.min(sisaTagihan, saldoRetur);
                jumlahInput.max = maxLimit;
                if (parseFloat(jumlahInput.value) > maxLimit || jumlahInput.value == sisaTagihan) {
                    jumlahInput.value = maxLimit;
                }
            } else if (jumlahInput) {
                jumlahInput.max = sisaTagihan;
            }
        }
    }

    metode.addEventListener('change', handleMetodeChange);
    handleMetodeChange();
});
</script>
