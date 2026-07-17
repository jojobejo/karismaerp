<!-- views/content/sales/retur/spr_approval.php -->
<!-- Digunakan oleh: Manager SC, Admin Retur, Kadep SC, Logistik -->
<style>
    .table-detail-spr th { background: #f8f9fa; font-size: 14px; border: 1px solid #dee2e6; padding: 8px !important; }
    .table-detail-spr td { font-size: 14px; border: 1px solid #dee2e6; vertical-align: middle; padding: 8px !important; }
    .alasan-list { list-style: none; padding: 0; margin: 0; }
    .alasan-list li { font-size: 13px; line-height: 1.5; }
    .alasan-list li::before { content: "✓ "; color: #28a745; font-weight: 700; }
    .spr-note-bottom {
        background: #fff8f8; border: 1px solid #f5c6cb; border-radius: 4px;
        padding: 10px 14px; font-size: 12px; color: #721c24;
    }
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
                        <?php
                        $role_labels = [
                            'mngsc'     => ['Manager SC', 'clipboard-check', 'warning'],
                            'admretur' => ['Admin Penjualan', 'boxes', 'info'],
                            'kadepub'     => ['Kadep Unit Bisnis', 'user-tie', 'success'],
                            'kadep_sc'    => ['Kadep SC', 'user-tie', 'primary'],
                            'logistik'    => ['Logistik', 'truck-loading', 'success'],
                        ];
                        $rl = $role_labels[$role] ?? ['Verifikasi', 'check', 'secondary'];
                        ?>
                        <h1 class="m-0">
                            <i class="fas fa-<?= $rl[1] ?> mr-2 text-<?= $rl[2] ?>"></i>
                            <?= $rl[0] ?>: Tindak <?= htmlspecialchars($spr['no_spr']) ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= $back_url ?>">Antrian <?= $rl[0] ?></a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($spr['no_spr']) ?></li>
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

                        <!-- KARTU SPR (hanya tampil) -->
                        <div class="card shadow" style="border:2px solid #e74c3c;">
                            <div class="card-header d-flex justify-content-between align-items-center py-2"
                                 style="background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;">
                                <div class="d-flex align-items-center">
                                    <span style="font-size:11px;opacity:.85;">PT. Karisma Indoagro Universal</span>
                                </div>
                                <div class="text-center">
                                    <div style="font-size:1rem;font-weight:700;">SURAT PENGAJUAN RETUR BARANG</div>
                                    <div style="font-size:11px;">No. <?= htmlspecialchars($spr['no_spr']) ?></div>
                                </div>
                                <div style="font-size:12px; text-align:right;">
                                    Tgl: <?= date('d/m/Y', strtotime($spr['tanggal'])) ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-2" style="font-size:13px;">
                                    <tr>
                                        <td style="width:130px;" class="font-weight-bold">Customer</td>
                                        <td>: <strong><?= htmlspecialchars($spr['nama_customer'] ?: ($spr['nama_customer_master'] ?? '-')) ?></strong></td>
                                        <td style="width:80px;" class="font-weight-bold">Sales</td>
                                        <td>: <?= htmlspecialchars($spr['nama_sales'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Alamat</td>
                                        <td colspan="3">: <?= htmlspecialchars($spr['alamat'] ?: ($spr['alamat_master'] ?? '-')) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Dibuat oleh</td>
                                        <td>: <?= htmlspecialchars($spr['create_by']) ?> (<?= $spr['create_at'] ? date('d/m/Y H:i', strtotime($spr['create_at'])) : '' ?>)</td>
                                        <td></td><td></td>
                                    </tr>
                                </table>

                                <div class="table-responsive mt-2">
                                    <table class="table table-bordered table-sm table-detail-spr">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:36px;">No.</th>
                                                <th>Nama Barang</th>
                                                <th>No. Faktur</th>
                                                <th>No. Batch/Lot</th>
                                                <th class="text-center">Qty</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($spr_detail)): ?>
                                                <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data barang</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($spr_detail as $i => $d): ?>
                                                    <tr>
                                                        <td class="text-center"><?= $i + 1 ?></td>
                                                        <td><?= htmlspecialchars($d['nama_barang'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($d['no_faktur'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($d['no_batch'] ?? '-') ?></td>
                                                        <td class="text-right"><?= number_format((float)$d['qty'], 3) ?></td>
                                                        <td>
                                                            <?php
                                                            $al = [];
                                                            if ($d['alasan_brg_bermasalah'])    $al[] = 'Brg bermasalah retur ke pabrik' . ($d['alasan_brg_bermasalah_opt'] ? ' ('.strtoupper($d['alasan_brg_bermasalah_opt']).')' : '');
                                                            if ($d['alasan_expired'])           $al[] = 'Expired' . ($d['alasan_expired_opt'] ? ' ('.strtoupper($d['alasan_expired_opt']).')' : '');
                                                            if ($d['alasan_tidak_laku'])        $al[] = 'Brg tidak laku & masuk OD';
                                                            if ($d['alasan_tes_market'])        $al[] = 'Faktur T/Brg Tes Market';
                                                            if ($d['alasan_bad_debt'])          $al[] = 'Potensi Bad Debt';
                                                            if ($d['alasan_harga_tidak_sesuai'])$al[] = 'Brg/Harga tdk sesuai Pesanan';
                                                            if ($d['alasan_spr_intern'])        $al[] = 'SPR Intern (brg Oper)';
                                                            if (!empty($d['alasan_lainlain']))  $al[] = 'Lain-lain: '.htmlspecialchars($d['alasan_lainlain']);
                                                            ?>
                                                            <?php if ($al): ?>
                                                                <ul class="alasan-list"><?php foreach($al as $a): ?><li><?= $a ?></li><?php endforeach; ?></ul>
                                                            <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if ($spr['catatan']): ?>
                                    <div class="mt-2 small"><strong>Catatan SC:</strong> <?= nl2br(htmlspecialchars($spr['catatan'])) ?></div>
                                <?php endif; ?>

                                <div class="spr-note-bottom mt-3">
                                    Barang yang kami retur sesuai dengan data di atas. Bilamana tidak sesuai, maka kami (toko) akan bertanggung jawab
                                    menerima konsekuensinya (retur ditolak) sesuai kebijakan PT Karisma Indoagro Universal.
                                </div>
                            </div>
                        </div>

                    </div><!-- col-lg-8 -->

                    <!-- SIDEBAR: FORM KEPUTUSAN -->
                    <div class="col-lg-4">
                        <div class="card shadow border-<?= $rl[2] ?>" style="border-width:2px !important;">
                            <div class="card-header bg-<?= $rl[2] ?> text-white py-2 d-flex justify-content-between align-items-center">
                                <h3 class="card-title m-0">
                                    <i class="fas fa-<?= $rl[1] ?> mr-1"></i> Keputusan <?= $rl[0] ?>
                                </h3>
                                <?php if ($role === 'admretur'): ?>
                                    <a href="<?= base_url('retur_penjualan/edit/'.$spr['id_spr']) ?>" class="btn btn-sm btn-warning text-dark"><i class="fas fa-edit"></i> Edit Data</a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <form action="<?= $action_url ?>" method="post" id="formApproval">
                                    <?php if ($this->config->item('csrf_protection') === TRUE): ?>
                                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                    <?php endif; ?>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-1">Tindakan <span class="text-danger">*</span></label>
                                        <div>
                                            <?php if ($role === 'logistik'): ?>
                                                <div class="custom-control custom-radio mb-2">
                                                    <input type="radio" id="aksi_selesai" name="aksi" value="selesai" class="custom-control-input" required>
                                                    <label class="custom-control-label text-success" for="aksi_selesai">
                                                        <i class="fas fa-check-circle"></i> <strong>Selesai — Proses Retur</strong>
                                                    </label>
                                                </div>
                                            <?php else: ?>
                                                <div class="custom-control custom-radio mb-2">
                                                    <input type="radio" id="aksi_setuju" name="aksi" value="setuju" class="custom-control-input" required>
                                                    <label class="custom-control-label text-success" for="aksi_setuju">
                                                        <i class="fas fa-check-circle"></i> <strong>Setuju — Lanjutkan</strong>
                                                    </label>
                                                </div>
                                            <?php endif; ?>
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

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-<?= $rl[2] ?> btn-block mr-2" id="btnSubmitApproval">
                                            <i class="fas fa-save"></i> Simpan Keputusan
                                        </button>
                                    </div>
                                </form>
                                <div class="mt-2">
                                    <a href="<?= $back_url ?>" class="btn btn-light btn-block">
                                        <i class="fas fa-arrow-left"></i> Kembali ke Antrian
                                    </a>
                                    <?php
                                    $jobdesk = strtoupper((string)($this->session->userdata('jobdesk') ?? ''));
                                    $is_logistik = in_array($jobdesk, ['LOGISTIK','LOGISTIC','LOGISTICS','ADMIN']);
                                    ?>
                                    <?php if ($is_logistik): ?>
                                    <a href="<?= base_url('retur_penjualan/print/' . $spr['id_spr']) ?>"
                                       class="btn btn-outline-secondary btn-block mt-1" target="_blank">
                                        <i class="fas fa-print"></i> Print SPR
                                    </a>
                                    <?php endif; ?>
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
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
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
            alert('Catatan/alasan wajib diisi jika SPR ditolak.');
            $('#catatanInput').focus();
            return;
        }

        var konfirm = aksi === 'tolak'
            ? 'Yakin menolak SPR ini? Tindakan ini tidak dapat dibatalkan.'
            : 'Yakin menyetujui SPR ini dan meneruskan ke tahap berikutnya?';

        if (!confirm(konfirm)) {
            e.preventDefault();
        }
    });
});
</script>
