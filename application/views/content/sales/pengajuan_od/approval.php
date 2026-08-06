<style>
    .timeline-od { border-left: 3px solid #dee2e6; padding-left: 16px; margin-left: 8px; }
    .timeline-od .tl-step { margin-bottom: 16px; position: relative; }
    .timeline-od .tl-step::before {
        content: '';
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #dee2e6;
        position: absolute;
        left: -23px;
        top: 3px;
    }
    .timeline-od .tl-step.done::before   { background: #28a745; }
    .timeline-od .tl-step.active::before { background: #ffc107; }
    .timeline-od .tl-step.reject::before { background: #dc3545; }
    .table-detail-spr th { background: #f8f9fa; font-size: 14px; border: 1px solid #dee2e6; padding: 8px !important; }
    .table-detail-spr td { font-size: 14px; border: 1px solid #dee2e6; vertical-align: middle; padding: 8px !important; }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-check-circle mr-2 text-info"></i>
                            Persetujuan Pengajuan OD
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales/C_PengajuanOD') ?>">Pengajuan OD</a></li>
                            <li class="breadcrumb-item active">OD-<?= $pengajuan['id'] ?></li>
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

                        <!-- KARTU PENGAJUAN -->
                        <div class="card shadow" style="border:2px solid #17a2b8;">
                            <div class="card-header d-flex justify-content-between align-items-center py-2"
                                 style="background:linear-gradient(135deg,#138496,#17a2b8);color:#fff;">
                                <div>
                                    <div style="font-size:1.1rem;font-weight:700;">PENGAJUAN OVERDUE</div>
                                    <div style="font-size:11px;opacity:.85;">PT. Karisma Indoagro Universal</div>
                                </div>
                                <div style="font-size:12px; text-align:right;">
                                    Tgl: <?= date('d/m/Y', strtotime($pengajuan['tanggal_pengajuan'])) ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-2" style="font-size:13px;">
                                    <tr>
                                        <td style="width:140px;" class="font-weight-bold">Dibuat oleh</td>
                                        <td>: <?= htmlspecialchars((string)$pengajuan['create_by']) ?></td>
                                        <td style="width:140px;" class="font-weight-bold">Customer</td>
                                        <td>: <strong><?= htmlspecialchars((string)$pengajuan['customer_name']) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Tgl Pengajuan</td>
                                        <td>: <?= date('d/m/Y', strtotime($pengajuan['tanggal_pengajuan'])) ?></td>
                                        <td class="font-weight-bold">Tgl Jatuh Tempo Baru</td>
                                        <td>: <strong><?= date('d/m/Y', strtotime($pengajuan['target_tanggal_jatuh_tempo'])) ?></strong></td>
                                    </tr>
                                </table>

                                <p class="font-weight-bold mb-1">Pesanan barang-barang sebagai berikut :</p>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" style="font-size: 13px;">
                                        <thead class="text-center bg-light">
                                            <tr>
                                                <th>No.</th>
                                                <th>BARANG</th>
                                                <th>Jumlah</th>
                                                <th>Tgl Faktur</th>
                                                <th>No Faktur</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $no = 1; 
                                            $total = 0;
                                            
                                            // Calculate rowspans
                                            $faktur_counts = [];
                                            foreach ($details as $d) {
                                                if (!isset($faktur_counts[$d['no_faktur']])) {
                                                    $faktur_counts[$d['no_faktur']] = 0;
                                                }
                                                $faktur_counts[$d['no_faktur']]++;
                                            }
                                            
                                            $printed_faktur = [];
                                            
                                            foreach ($details as $i => $d) : 
                                                $subtotal = (float)$d['total_harga'];
                                                $total += $subtotal;
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= htmlspecialchars((string)$d['nama_barang']) ?></td>
                                                <td class="text-right"><?= number_format($subtotal, 2, '.', ',') ?></td>
                                                
                                                <?php if (!isset($printed_faktur[$d['no_faktur']])) : ?>
                                                    <td rowspan="<?= $faktur_counts[$d['no_faktur']] ?>" class="text-center" style="vertical-align: middle;">
                                                        <?= date('d-M-y', strtotime($d['tanggal_faktur'])) ?>
                                                    </td>
                                                    <td rowspan="<?= $faktur_counts[$d['no_faktur']] ?>" style="vertical-align: middle;">
                                                        <?= $d['no_faktur'] ?>
                                                    </td>
                                                    <?php $printed_faktur[$d['no_faktur']] = true; ?>
                                                <?php endif; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <tr class="font-weight-bold bg-light">
                                                <td colspan="2" class="text-center">Total</td>
                                                <td class="text-right"><?= number_format($total, 2, '.', ',') ?></td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="table-responsive mt-3">
                                    <table class="table table-bordered table-sm table-detail-spr">
                                        <tbody>
                                            <tr>
                                                <th style="width: 25%;">Catatan Pengajuan</th>
                                                <td><?= nl2br(htmlspecialchars((string)$pengajuan['catatan'])) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Lampiran</th>
                                                <td>
                                                    <?php 
                                                    $lampiran = $pengajuan['lampiran_sc'] ?: $pengajuan['lampiran_mngtc'];
                                                    if ($lampiran) : 
                                                    ?>
                                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal-lampiran-preview">
                                                            <i class="fas fa-image"></i> Lihat Lampiran
                                                        </button>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if ($lampiran) : ?>
                                <!-- Modal Preview Lampiran -->
                                <div class="modal fade" id="modal-lampiran-preview" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fas fa-paperclip mr-1"></i> Lampiran Pengajuan OD</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-center p-3">
                                                <?php 
                                                $ext = strtolower(pathinfo($lampiran, PATHINFO_EXTENSION));
                                                if (in_array($ext, ['pdf'])) :
                                                ?>
                                                    <iframe src="<?= base_url($lampiran) ?>" style="width: 100%; height: 500px;" frameborder="0"></iframe>
                                                <?php else : ?>
                                                    <img src="<?= base_url($lampiran) ?>" class="img-fluid rounded shadow" style="max-height: 70vh;">
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="<?= base_url($lampiran) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-external-link-alt mr-1"></i> Buka di Tab Baru</a>
                                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                            </div>
                        </div>

                    </div><!-- col-lg-8 -->

                    <!-- SIDEBAR: FORM KEPUTUSAN & TIMELINE -->
                    <div class="col-lg-4">
                        <div class="card shadow border-info mb-3" style="border-width:2px !important;">
                            <div class="card-header bg-info text-white py-2">
                                <h3 class="card-title m-0">
                                    <i class="fas fa-check-circle mr-1"></i> Persetujuan OD
                                </h3>
                            </div>
                            <div class="card-body">
                                <form action="<?= base_url('sales/C_PengajuanOD/approve/' . $pengajuan['id']) ?>" method="post" id="formApproval" enctype="multipart/form-data">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-1">Tindakan <span class="text-danger">*</span></label>
                                        <div>
                                            <div class="custom-control custom-radio mb-2">
                                                <input type="radio" id="aksi_setuju" name="action" value="approve" class="custom-control-input" required>
                                                <label class="custom-control-label text-success" for="aksi_setuju">
                                                    <i class="fas fa-check-circle"></i> <strong>Setuju</strong>
                                                </label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="aksi_tolak" name="action" value="reject" class="custom-control-input">
                                                <label class="custom-control-label text-danger" for="aksi_tolak">
                                                    <i class="fas fa-times-circle"></i> <strong>Tolak</strong>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($pengajuan['status'] == 'pending_mngtc' && in_array($user['jobdesk'], ['MANAGERTC', 'ADMIN'])) : ?>
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-1">Upload Lampiran (Opsional)</label>
                                        <input type="file" name="lampiran_mngtc" class="form-control-file" accept="image/*,.pdf">
                                        <small class="text-muted">Maksimal 2MB (jpg/png/pdf).</small>
                                    </div>
                                    <?php endif; ?>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-1">
                                            Catatan / Alasan
                                            <small class="text-muted font-weight-normal">(wajib jika ditolak)</small>
                                        </label>
                                        <textarea class="form-control" name="catatan_approval" id="catatanInput" rows="4"
                                                  placeholder="Tulis catatan atau alasan keputusan..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-info btn-block mb-2" id="btnSubmitApproval">
                                        <i class="fas fa-save"></i> Simpan Keputusan
                                    </button>
                                </form>
                                <div class="mt-2">
                                    <a href="<?= base_url('sales/C_PengajuanOD') ?>" class="btn btn-light btn-block">
                                        <i class="fas fa-arrow-left"></i> Kembali Ke List
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#formApproval').on('submit', function(e) {
        var isReject = $('#aksi_tolak').is(':checked');
        var cat = $.trim($('#catatanInput').val());
        if (isReject && cat === '') {
            alert('Catatan / Alasan penolakan wajib diisi!');
            e.preventDefault();
            return false;
        }
    });
});
</script>
