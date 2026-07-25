<link rel="stylesheet" href="<?= base_url('assets/dist/css/retur-custom.css') ?>"><?php /* views/content/sales/retur/retur_approval.php */
if (!function_exists('hitung_durasi')) {
    function hitung_durasi($from, $to) {
        if (empty($from) || empty($to)) return null;
        $t1 = new DateTime($from);
        $t2 = new DateTime($to);
        $diff = $t1->diff($t2);
        
        $parts = [];
        if ($diff->d > 0) $parts[] = $diff->d . ' hari';
        if ($diff->h > 0) $parts[] = $diff->h . ' jam';
        if ($diff->i > 0) $parts[] = $diff->i . ' menit';
        if ($diff->s > 0 && empty($parts)) $parts[] = $diff->s . ' detik';
        
        return empty($parts) ? '0 menit' : implode(' ', $parts);
    }
}
?>
<style>
    .table-detail-spr th { background: #f8f9fa; font-size: 14px; border: 1px solid #dee2e6; padding: 8px !important; }
    .table-detail-spr td { font-size: 14px; border: 1px solid #dee2e6; vertical-align: middle; padding: 8px !important; }
    .spr-note-bottom {
        background:#fff8f8; border:1px solid #f5c6cb; border-radius:4px;
        padding:10px 14px; font-size:12px; color:#721c24;
    }
    .timeline-retur { border-left: 3px solid #dee2e6; padding-left: 16px; margin-left: 8px; }
    .timeline-retur .tl-step { margin-bottom: 16px; position: relative; }
    .timeline-retur .tl-step::before {
        content: '';
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #dee2e6;
        position: absolute;
        left: -23px;
        top: 3px;
    }
    .timeline-retur .tl-step.done::before   { background: #28a745; }
    .timeline-retur .tl-step.active::before { background: #ffc107; }
    .timeline-retur .tl-step.reject::before { background: #dc3545; }
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
                        <h1 class="m-0">
                            <i class="fas fa-check-circle mr-2 text-info"></i>
                            Persetujuan Retur: <?= htmlspecialchars($retur['no_retur']) ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('retur_penjualan/retur') ?>">Retur Penjualan</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($retur['no_retur']) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- FLASH -->
                <?php foreach (['success'=>'success','error'=>'danger'] as $k=>$c): ?>
                    <?php if ($msg = $this->session->flashdata($k)): ?>
                        <div class="alert alert-<?= $c ?> alert-dismissible">
                            <i class="fas fa-<?= $k==='success'?'check-circle':'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="row">
                    <div class="col-lg-8">

                        <!-- KARTU RETUR -->
                        <div class="card shadow" style="border:2px solid #17a2b8;">
                            <div class="card-header d-flex justify-content-between align-items-center py-2"
                                 style="background:linear-gradient(135deg,#138496,#17a2b8);color:#fff;">
                                <div class="d-flex align-items-center">
                                    <span style="font-size:11px;opacity:.85;">PT. Karisma Indoagro Universal</span>
                                </div>
                                <div class="text-center">
                                    <div style="font-size:1rem;font-weight:700;">RETUR PENJUALAN</div>
                                    <div style="font-size:11px;">No. <?= htmlspecialchars($retur['no_retur']) ?></div>
                                </div>
                                <div style="font-size:12px; text-align:right;">
                                    Tgl: <?= date('d/m/Y', strtotime($retur['tanggal_retur'])) ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-2" style="font-size:13px;">
                                    <tr>
                                        <td style="width:130px;" class="font-weight-bold">Customer</td>
                                        <td>: <strong><?= htmlspecialchars($retur['nama_customer'] ?: ($retur['nama_customer_master'] ?? '-')) ?></strong></td>
                                        <td style="width:80px;" class="font-weight-bold">Sales</td>
                                        <td>: <?= htmlspecialchars($retur['nama_sales'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Alamat</td>
                                        <td colspan="3">: <?= htmlspecialchars($retur['alamat'] ?: ($retur['alamat_master'] ?? '-')) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Dibuat oleh</td>
                                        <td>: <?= htmlspecialchars($retur['create_by_retur'] ?? '-') ?> (<?= $retur['create_at_retur'] ? date('d/m/Y H:i', strtotime($retur['create_at_retur'])) : '' ?>)</td>
                                        <td class="font-weight-bold">Dari SPR</td>
                                        <td>: <?= htmlspecialchars($retur['no_spr'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Tipe Retur</td>
                                        <td colspan="3">: 
                                            <?php if (($retur['tipe_retur'] ?? 'biasa') === 'replace'): ?>
                                                <span class="badge badge-success px-2 py-1">REPLACE (Ganti Barang)</span>
                                            <?php elseif (($retur['tipe_retur'] ?? 'biasa') === 'service'): ?>
                                                <span class="badge badge-warning px-2 py-1">SERVICE (Servis Barang)</span>
                                            <?php else: ?>
                                                 <span class="badge badge-secondary px-2 py-1">RETUR (Refund/Potong Faktur)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>

                                <div class="table-responsive mt-2">
                                    <table class="table table-bordered table-sm table-detail-spr">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:36px;">No.</th>
                                                <th>Nama Barang</th>
                                                <th style="width:70px;">Satuan</th>
                                                <th style="width:110px;">No. Faktur</th>
                                                <th style="width:110px;">No. Batch/Lot</th>
                                                <th class="text-center" style="width:100px;">Exp. Date</th>
                                                <th class="text-center" style="width:80px;">Qty Retur</th>
                                                <th class="text-right" style="width:100px;">Harga Satuan</th>
                                                <th class="text-right" style="width:110px;">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $total = 0; foreach ($retur_detail as $i => $d):
                                                $subtotal = (float)$d['qty_retur'] * (float)$d['harga_satuan'];
                                                 $total += $subtotal;
                                             ?>
                                             <tr>
                                                 <td class="text-center"><?= $i + 1 ?></td>
                                                 <td><?= htmlspecialchars($d['nama_barang'] ?? '') ?></td>
                                                 <td><?= htmlspecialchars($d['satuan'] ?? '') ?></td>
                                                 <td><?= htmlspecialchars($d['no_faktur'] ?? '') ?></td>
                                                 <td><?= htmlspecialchars($d['no_batch'] ?? '') ?></td>
                                                 <td class="text-center"><?= !empty($d['expired_date']) ? date('d/m/Y', strtotime($d['expired_date'])) : '-' ?></td>
                                                 <td class="text-right"><?= (float)$d['qty_retur'] ?></td>
                                                 <td class="text-right">Rp <?= number_format((float)$d['harga_satuan'], 0, ',', '.') ?></td>
                                                 <td class="text-right">Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                             </tr>
                                             <?php endforeach; ?>
                                             <tr class="table-secondary">
                                                 <td colspan="8" class="text-right font-weight-bold">TOTAL VALUE:</td>
                                                 <td class="text-right font-weight-bold">Rp <?= number_format($total, 0, ',', '.') ?></td>
                                             </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if (!empty($retur['catatan_logistik'])): ?>
                                    <div class="mt-2 small"><strong>Catatan ADMLPB2:</strong> <?= nl2br(htmlspecialchars($retur['catatan_logistik'])) ?></div>
                                <?php endif; ?>

                                <div class="spr-note-bottom mt-3">
                                    Barang yang kami retur sesuai dengan data di atas. Bilamana tidak sesuai, maka kami (toko) akan bertanggung jawab
                                    menerima konsekuensinya (retur ditolak) sesuai kebijakan PT Karisma Indoagro Universal. <br><br>
                                    <strong>Catatan Keuangan:</strong> 
                                    <?php if (($retur['tipe_retur'] ?? 'biasa') === 'biasa'): ?>
                                        Jika retur ini telah diselesaikan, maka saldo dari retur ini akan masuk ke akun <strong>210-17 Q Hutang Non Dagang (Retur Penjualan yg blm dipot)</strong> milik customer dan jurnal otomatis akan dibuat.
                                    <?php else: ?>
                                        Karena tipe retur adalah <strong><?= strtoupper($retur['tipe_retur']) ?></strong>, maka tidak ada jurnal otomatis yang akan dibuat.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div><!-- col-lg-8 -->

                    <!-- SIDEBAR: FORM KEPUTUSAN -->
                    <div class="col-lg-4">
                        <div class="card shadow border-info" style="border-width:2px !important;">
                            <div class="card-header bg-info text-white py-2">
                                <h3 class="card-title m-0">
                                    <i class="fas fa-check-circle mr-1"></i> Persetujuan Retur
                                </h3>
                            </div>
                            <div class="card-body">
                                <form action="<?= base_url('retur_penjualan/retur/approve_simpan/' . $retur['id_retur']) ?>" method="post" id="formApproval">
                                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                    <?php endif; ?>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-1">Tindakan <span class="text-danger">*</span></label>
                                        <div>
                                            <div class="custom-control custom-radio mb-2">
                                                <input type="radio" id="aksi_setuju" name="aksi" value="setuju" class="custom-control-input" required>
                                                <label class="custom-control-label text-success" for="aksi_setuju">
                                                    <i class="fas fa-check-circle"></i> <strong>Setuju — Lanjutkan</strong>
                                                </label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="aksi_tolak" name="aksi" value="tolak" class="custom-control-input">
                                                <label class="custom-control-label text-danger" for="aksi_tolak">
                                                    <i class="fas fa-times-circle"></i> <strong>Tolak — Kembalikan</strong>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-1">
                                            Catatan / Alasan
                                            <small class="text-muted font-weight-normal">(wajib jika ditolak)</small>
                                        </label>
                                        <textarea class="form-control" name="catatan" id="catatanInput" rows="4"
                                                  placeholder="Tulis catatan atau alasan keputusan..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-info btn-block mb-2" id="btnSubmitApproval">
                                        <i class="fas fa-save"></i> Simpan Keputusan
                                    </button>
                                </form>
                                <div class="mt-2">
                                    <a href="<?= base_url('retur_penjualan/retur/print/' . $retur['id_retur']) ?>" target="_blank" class="btn btn-outline-info btn-block mb-2">
                                        <i class="fas fa-print"></i> Cetak Detail Retur
                                    </a>
                                    <a href="<?= base_url('retur_penjualan/retur') ?>" class="btn btn-light btn-block">
                                        <i class="fas fa-arrow-left"></i> Kembali ke Antrian
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!-- col-lg-4 -->
                </div><!-- row -->

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(document).ready(function() {
    $('#formApproval').on('submit', function(e) {
        var aksi    = $('input[name="aksi"]:checked').val();
        var catatan = $('#catatanInput').val().trim();

        if (!aksi) {
            e.preventDefault();
            alert('Pilih tindakan terlebih dahulu (Setuju atau Tolak).');
            return;
        }
        if ((aksi === 'tolak') && !catatan) {
            e.preventDefault();
            alert('Catatan/alasan wajib diisi jika Retur ditolak.');
            $('#catatanInput').focus();
            return;
        }

        var konfirm = aksi === 'tolak'
            ? 'Yakin menolak Retur ini? Tindakan ini tidak dapat dibatalkan.'
            : 'Yakin menyetujui Retur ini dan meneruskan ke tahap berikutnya?';

        if (!confirm(konfirm)) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
