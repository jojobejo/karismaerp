<?php
defined('BASEPATH') or exit('No direct script access allowed');

$schemaReady = !empty($schema_ready);
$metrics = isset($metrics) && is_array($metrics) ? $metrics : ['total_periods' => 0, 'open_periods' => 0, 'closed_periods' => 0, 'pending_reopen' => 0];
$periods = isset($periods) && is_array($periods) ? $periods : [];
$closings = isset($closings) && is_array($closings) ? $closings : [];
$reopens = isset($reopens) && is_array($reopens) ? $reopens : [];
$canApprove = !empty($can_approve);
?>

<style>
    .tutup-buku-page {
        background-color: #f4f6f9;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .tb-header-card {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: #fff;
        border-radius: 8px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .tb-header-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .tb-header-subtitle {
        font-size: 14px;
        color: #e0e7ff;
        max-width: 800px;
        line-height: 1.5;
    }
    .tb-stat-card {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background: #fff;
        overflow: hidden;
    }
    .tb-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    }
    .tb-stat-body {
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .tb-stat-number {
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }
    .tb-stat-label {
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
    }
    .tb-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .tb-nav-tabs .nav-link {
        font-weight: 600;
        font-size: 14px;
        color: #495057;
        padding: 12px 20px;
        border: none;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
    }
    .tb-nav-tabs .nav-link.active {
        color: #1e3c72;
        border-bottom: 3px solid #1e3c72;
        background: transparent;
    }
    .tb-card {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        margin-bottom: 25px;
    }
    .tb-card-header {
        background: #fff;
        border-bottom: 1px solid #edf2f7;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .tb-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }
    .badge-open {
        background-color: #e6fffa;
        color: #047481;
        border: 1px solid #b2f5ea;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 6px;
    }
    .badge-closed {
        background-color: #edf2f7;
        color: #4a5568;
        border: 1px solid #cbd5e0;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 6px;
    }
    .badge-passed {
        background-color: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .badge-failed {
        background-color: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .rule-item {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 14px 18px;
        margin-bottom: 12px;
        background: #fff;
        transition: border-color 0.2s;
    }
    .rule-item.rule-passed {
        border-left: 5px solid #22c55e;
    }
    .rule-item.rule-failed-blocking {
        border-left: 5px solid #ef4444;
        background: #fffafa;
    }
    .rule-item.rule-failed-warning {
        border-left: 5px solid #f59e0b;
        background: #fffdfa;
    }
    .checksum-code {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 11px;
        background: #f1f5f9;
        padding: 3px 6px;
        border-radius: 4px;
        color: #0f172a;
        word-break: break-all;
    }
</style>

<div class="wrapper tutup-buku-page">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper p-3">
        <!-- HEADER HERO -->
        <div class="tb-header-card">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h1 class="tb-header-title">
                        <i class="fas fa-lock mr-2"></i> Modul Tutup Buku &amp; Cut-Off Periode
                    </h1>
                    <p class="tb-header-subtitle mb-0">
                        Mekanisme pembukuan akuntansi terstandar: penguncian periode transaksi, rekonsiliasi GL vs Subledger (AR/AP/Persediaan), snapshot saldo historis yang dapat diaudit, dan otomasi jurnal penutup tahunan.
                    </p>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="<?= base_url('jurnal') ?>" class="btn btn-outline-light btn-sm mr-2">
                        <i class="fas fa-book mr-1"></i> Buka Buku Besar
                    </a>
                    <button type="button" class="btn btn-light btn-sm" onclick="location.reload();">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <?php if (!$schemaReady) : ?>
            <div class="alert alert-danger shadow-sm">
                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Perhatian:</strong> Skema database modul Tutup Buku belum lengkap. Jalankan migrasi <code>db/migrations/20261005_modul_tutup_buku.sql</code> terlebih dahulu.
            </div>
        <?php endif; ?>

        <!-- METRIC WIDGETS -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="tb-stat-card">
                    <div class="tb-stat-body">
                        <div>
                            <div class="tb-stat-number text-primary"><?= number_format($metrics['total_periods']) ?></div>
                            <div class="tb-stat-label">Total Periode Fiskal</div>
                        </div>
                        <div class="tb-stat-icon bg-light text-primary">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="tb-stat-card">
                    <div class="tb-stat-body">
                        <div>
                            <div class="tb-stat-number text-success"><?= number_format($metrics['open_periods']) ?></div>
                            <div class="tb-stat-label">Periode Aktif (OPEN)</div>
                        </div>
                        <div class="tb-stat-icon bg-light text-success">
                            <i class="fas fa-unlock"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="tb-stat-card">
                    <div class="tb-stat-body">
                        <div>
                            <div class="tb-stat-number text-secondary"><?= number_format($metrics['closed_periods']) ?></div>
                            <div class="tb-stat-label">Periode Terkunci (CLOSED)</div>
                        </div>
                        <div class="tb-stat-icon bg-light text-secondary">
                            <i class="fas fa-lock"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="tb-stat-card">
                    <div class="tb-stat-body">
                        <div>
                            <div class="tb-stat-number <?= $metrics['pending_reopen'] > 0 ? 'text-danger' : 'text-muted' ?>">
                                <?= number_format($metrics['pending_reopen']) ?>
                            </div>
                            <div class="tb-stat-label">Permohonan Buka Periode</div>
                        </div>
                        <div class="tb-stat-icon bg-light <?= $metrics['pending_reopen'] > 0 ? 'text-danger' : 'text-muted' ?>">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN TABS CONTAINER -->
        <div class="tb-card">
            <div class="border-bottom">
                <ul class="nav tb-nav-tabs" id="closingTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-periods-link" data-toggle="tab" href="#tab-periods" role="tab">
                            <i class="fas fa-calendar-check mr-2"></i> Daftar Periode &amp; Eksekusi Tutup Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-history-link" data-toggle="tab" href="#tab-history" role="tab">
                            <i class="fas fa-history mr-2"></i> Riwayat Snapshot Closing (Audit Trail)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-reopen-link" data-toggle="tab" href="#tab-reopen" role="tab">
                            <i class="fas fa-door-open mr-2"></i> Permohonan Buka Periode (Reopen)
                            <?php if ($metrics['pending_reopen'] > 0): ?>
                                <span class="badge badge-danger ml-1"><?= $metrics['pending_reopen'] ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="tab-content p-4" id="closingTabsContent">
                <!-- TAB 1: PERIODE & EKSEKUSI -->
                <div class="tab-pane fade show active" id="tab-periods" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-weight-bold text-dark mb-0">Daftar Periode Fiskal Pembukuan</h5>
                        <small class="text-muted">Status CLOSED mengunci seluruh transaksi mutasi, jurnal, dan faktur pada rentang tanggal terkait.</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle" id="tblPeriods">
                            <thead class="thead-light">
                                <tr>
                                    <th>Kode Periode</th>
                                    <th>Nama Periode</th>
                                    <th>Rentang Tanggal</th>
                                    <th>Status Periode</th>
                                    <th>Jurnal Posted</th>
                                    <th>Status Closing</th>
                                    <th>Validasi Pre-Closing</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($periods)): ?>
                                    <?php foreach ($periods as $p): ?>
                                        <tr>
                                            <td class="font-weight-bold text-primary"><?= html_escape($p->kode_periode) ?></td>
                                            <td><?= html_escape($p->nama_periode) ?></td>
                                            <td>
                                                <small class="text-muted d-block">
                                                    <?= date('d M Y', strtotime($p->tanggal_mulai)) ?> s/d <?= date('d M Y', strtotime($p->tanggal_selesai)) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($p->status === 'OPEN'): ?>
                                                    <span class="badge-open"><i class="fas fa-lock-open mr-1"></i> OPEN</span>
                                                <?php else: ?>
                                                    <span class="badge-closed"><i class="fas fa-lock mr-1"></i> CLOSED</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-light border">
                                                    <?= number_format($p->total_jurnal_posted) ?> posted
                                                </span>
                                                <?php if ($p->total_jurnal_draft > 0): ?>
                                                    <span class="badge badge-warning text-dark ml-1">
                                                        <?= number_format($p->total_jurnal_draft) ?> draft
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($p->id_closing)): ?>
                                                    <span class="badge badge-info">
                                                        <?= html_escape($p->last_closing_type) ?> (v<?= $p->closing_version ?>)
                                                    </span>
                                                    <small class="d-block text-muted">
                                                        <?= $p->closing_status ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted font-italic">Belum Closing</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($p->id_validation_run)): ?>
                                                    <?php if ($p->last_val_status === 'PASSED'): ?>
                                                        <span class="badge-passed"><i class="fas fa-check-circle mr-1"></i> Lolos</span>
                                                    <?php else: ?>
                                                        <span class="badge-failed">
                                                            <i class="fas fa-times-circle mr-1"></i> <?= $p->last_val_blocking ?> Blocking
                                                        </span>
                                                    <?php endif; ?>
                                                    <small class="d-block text-muted">
                                                        <?= date('d/m/y H:i', strtotime($p->last_val_run_at)) ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted font-italic">Belum Diperiksa</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-info btn-check-preclosing" 
                                                            data-id="<?= $p->id_periode ?>" 
                                                            data-kode="<?= html_escape($p->kode_periode) ?>" 
                                                            title="Jalankan Validasi Pre-Closing">
                                                        <i class="fas fa-clipboard-check mr-1"></i> Cek Kelayakan
                                                    </button>

                                                    <?php if ($p->status === 'OPEN'): ?>
                                                        <button type="button" class="btn btn-primary btn-open-closing-modal" 
                                                                data-id="<?= $p->id_periode ?>" 
                                                                data-kode="<?= html_escape($p->kode_periode) ?>" 
                                                                data-selesai="<?= $p->tanggal_selesai ?>"
                                                                title="Eksekusi Tutup Buku">
                                                            <i class="fas fa-lock mr-1"></i> Tutup Buku
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-warning btn-open-reopen-modal" 
                                                                data-id="<?= $p->id_periode ?>" 
                                                                data-kode="<?= html_escape($p->kode_periode) ?>" 
                                                                title="Ajukan Buka Periode (Reopen)">
                                                            <i class="fas fa-unlock mr-1"></i> Ajukan Reopen
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            Belum ada data periode fiskal di database. Silakan buat periode di modul Jurnal terlebih dahulu.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: RIWAYAT SNAPSHOT CLOSING -->
                <div class="tab-pane fade" id="tab-history" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-weight-bold text-dark mb-0">Riwayat Snapshot Tutup Buku (Closing Ledger Freeze)</h5>
                        <small class="text-muted">Setiap eksekusi tutup buku menghasilkan snapshot immutable GL, persediaan, AR, dan AP serta Checksum SHA256.</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="tblClosings">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID / Periode</th>
                                    <th>Tipe</th>
                                    <th>Versi</th>
                                    <th>Tgl Closing</th>
                                    <th>Waktu Eksekusi</th>
                                    <th>Total GL Debit/Kredit</th>
                                    <th>Laba Bersih</th>
                                    <th>Status</th>
                                    <th>Checksum Audit</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($closings)): ?>
                                    <?php foreach ($closings as $c): ?>
                                        <tr>
                                            <td>
                                                <strong class="text-primary">#<?= $c->id_closing ?></strong> - <?= html_escape($c->kode_periode) ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $c->tipe_closing === 'TAHUNAN' ? 'badge-dark' : 'badge-secondary' ?>">
                                                    <?= $c->tipe_closing ?>
                                                </span>
                                            </td>
                                            <td><span class="badge badge-info">v<?= $c->closing_version ?></span></td>
                                            <td><?= date('d M Y', strtotime($c->tanggal_closing)) ?></td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($c->waktu_eksekusi)) ?><br>
                                                    Oleh: <?= html_escape($c->eksekutor_name ?: 'System') ?>
                                                </small>
                                            </td>
                                            <td>Rp <?= number_format($c->total_debit_gl, 2, ',', '.') ?></td>
                                            <td class="<?= $c->total_laba_bersih_periode >= 0 ? 'text-success font-weight-bold' : 'text-danger font-weight-bold' ?>">
                                                Rp <?= number_format($c->total_laba_bersih_periode, 2, ',', '.') ?>
                                            </td>
                                            <td>
                                                <?php if ($c->status === 'COMPLETED'): ?>
                                                    <span class="badge badge-success">COMPLETED</span>
                                                <?php elseif ($c->status === 'REOPENED'): ?>
                                                    <span class="badge badge-warning text-dark">REOPENED</span>
                                                <?php elseif ($c->status === 'SUPERSEDED'): ?>
                                                    <span class="badge badge-light border">SUPERSEDED</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary"><?= $c->status ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="checksum-code" title="<?= html_escape($c->checksum) ?>">
                                                    <?= substr($c->checksum, 0, 14) ?>...
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <button type="button" class="btn btn-sm btn-info btn-view-closing-detail" data-id="<?= $c->id_closing ?>">
                                                    <i class="fas fa-eye mr-1"></i> Snapshot
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            Belum ada riwayat snapshot closing yang tercatat.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: WORKFLOW REOPEN -->
                <div class="tab-pane fade" id="tab-reopen" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-weight-bold text-dark mb-0">Workflow Permohonan Buka Periode (Reopen Audit Request)</h5>
                        <small class="text-muted">Pembukaan kembali periode lampau mewajibkan alasan formal audit dan persetujuan bertingkat.</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="tblReopen">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Periode</th>
                                    <th>Diajukan Oleh</th>
                                    <th>Waktu Pengajuan</th>
                                    <th>Alasan Permohonan</th>
                                    <th>Status Approval</th>
                                    <th>Otorisator</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reopens)): ?>
                                    <?php foreach ($reopens as $r): ?>
                                        <tr>
                                            <td><strong>#<?= $r->id_reopen ?></strong></td>
                                            <td class="font-weight-bold text-primary"><?= html_escape($r->kode_periode) ?></td>
                                            <td><?= html_escape($r->pemohon_name ?: 'User ID ' . $r->diajukan_oleh) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($r->diajukan_pada)) ?></td>
                                            <td>
                                                <div style="max-width: 280px; white-space: normal;" class="text-muted">
                                                    <?= nl2br(html_escape($r->alasan_pembukaan)) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($r->status_approval === 'PENDING'): ?>
                                                    <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i> Menunggu Approval</span>
                                                <?php elseif ($r->status_approval === 'APPROVED'): ?>
                                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= html_escape($r->manager_name ?: '-') ?>
                                                    <?php if (!empty($r->disetujui_manager_at)): ?>
                                                        <br><?= date('d/m/y H:i', strtotime($r->disetujui_manager_at)) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td class="text-right">
                                                <?php if ($r->status_approval === 'PENDING' && $canApprove): ?>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-success btn-process-reopen" data-id="<?= $r->id_reopen ?>" data-action="APPROVE" title="Setujui Buka Periode">
                                                            <i class="fas fa-check mr-1"></i> Setujui
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-process-reopen" data-id="<?= $r->id_reopen ?>" data-action="REJECT" title="Tolak Permohonan">
                                                            <i class="fas fa-times mr-1"></i> Tolak
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small font-italic">Selesai</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            Tidak ada riwayat permohonan reopen periode saat ini.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: PRE-CLOSING VALIDATION CHECKLIST -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalPreClosing" tabindex="-1" role="dialog" aria-labelledby="modalPreClosingTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="modalPreClosingTitle">
                    <i class="fas fa-clipboard-check text-primary mr-2"></i> Hasil Validasi Pre-Closing: <span id="valModalPeriodCode" class="text-primary"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="valLoading" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <h6 class="font-weight-bold">Memeriksa Integritas Data &amp; Menjalankan Rekonsiliasi...</h6>
                    <small class="text-muted">Memeriksa keseimbangan GL, jurnal draft, unapplied payments, AR/AP, dan stok.</small>
                </div>

                <div id="valResultContainer" style="display: none;">
                    <div id="valSummaryAlert" class="alert mb-4"></div>

                    <h6 class="font-weight-bold text-dark mb-3">Rincian Item Pemeriksaan:</h6>
                    <div id="valRuleList"></div>
                </div>
            </div>
            <div class="modal-footer bg-light justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnProceedClosingFromModal" style="display: none;">
                    <i class="fas fa-arrow-right mr-1"></i> Lanjutkan ke Tutup Buku
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: EKSEKUSI TUTUP BUKU (CLOSING CONFIRMATION) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalExecuteClosing" tabindex="-1" role="dialog" aria-labelledby="modalExecuteClosingTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formExecuteClosing">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalExecuteClosingTitle">
                        <i class="fas fa-lock mr-2"></i> Eksekusi Tutup Buku Periode
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_periode" id="closingPeriodeId">

                    <div class="alert alert-info">
                        Anda akan mengunci periode <strong id="closingPeriodLabel"></strong>. Seluruh transaksi pada periode ini tidak akan dapat diubah setelah tutup buku berhasil.
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Tipe Tutup Buku <span class="text-danger">*</span></label>
                        <select class="form-control" name="tipe_closing" id="closingTipe">
                            <option value="BULANAN">Tutup Buku Bulanan (Monthly Closing - Freeze Snapshot)</option>
                            <option value="TAHUNAN">Tutup Buku Tahunan (Year-End Closing - Auto Jurnal Penutup)</option>
                        </select>
                        <small class="form-text text-muted" id="closingTipeHint">
                            Tutup buku bulanan mengunci periode dan membekukan saldo. Tutup buku tahunan akan membentuk jurnal penutup akun nominal (Pendapatan &amp; Beban) ke Laba Ditahan.
                        </small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Catatan Tutup Buku / Berita Acara</label>
                        <textarea class="form-control" name="catatan" rows="3" placeholder="Masukkan catatan atau nomor berita acara tutup buku..."></textarea>
                    </div>

                    <div class="form-group form-check mb-0">
                        <input type="checkbox" class="form-check-input" id="cbBypassWarning" name="bypass_warning" value="1">
                        <label class="form-check-label small text-muted" for="cbBypassWarning">
                            Lanjutkan jika hanya terdapat anomali berstatus Peringatan (WARNING non-blocking).
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitClosing">
                        <i class="fas fa-check mr-1"></i> Jalankan Tutup Buku Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: VIEW SNAPSHOT DETAIL -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalClosingDetail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-archive mr-2"></i> Snapshot Tutup Buku: <span id="snapModalTitle"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <span class="mr-3"><strong>Eksekutor:</strong> <span id="snapEksekutor"></span></span>
                        <span class="mr-3"><strong>Waktu:</strong> <span id="snapWaktu"></span></span>
                        <span><strong>Checksum:</strong> <code id="snapChecksum" class="checksum-code"></code></span>
                    </div>
                    <div class="mt-2 mt-md-0 btn-group btn-group-sm">
                        <a href="#" id="btnExportGL" class="btn btn-outline-secondary" target="_blank"><i class="fas fa-download mr-1"></i> CSV GL</a>
                        <a href="#" id="btnExportInv" class="btn btn-outline-secondary" target="_blank"><i class="fas fa-download mr-1"></i> CSV Stok</a>
                        <a href="#" id="btnExportAR" class="btn btn-outline-secondary" target="_blank"><i class="fas fa-download mr-1"></i> CSV AR</a>
                        <a href="#" id="btnExportAP" class="btn btn-outline-secondary" target="_blank"><i class="fas fa-download mr-1"></i> CSV AP</a>
                    </div>
                </div>

                <div class="p-3">
                    <ul class="nav nav-pills mb-3" id="snapSubTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active btn-sm" id="snap-gl-tab" data-toggle="pill" href="#snap-gl" role="tab">Buku Besar GL</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-sm" id="snap-inv-tab" data-toggle="pill" href="#snap-inv" role="tab">Persediaan Stok</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-sm" id="snap-ar-tab" data-toggle="pill" href="#snap-ar" role="tab">Piutang Usaha (AR)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-sm" id="snap-ap-tab" data-toggle="pill" href="#snap-ap" role="tab">Hutang Usaha (AP)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn-sm" id="snap-closing-journal-tab" data-toggle="pill" href="#snap-closing-journal" role="tab">Jurnal Penutup</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="snapSubTabsContent">
                        <div class="tab-pane fade show active" id="snap-gl" role="tabpanel">
                            <div class="table-responsive" style="max-height: 450px;">
                                <table class="table table-sm table-striped" id="tblSnapGL">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Kode Akun</th>
                                            <th>Nama Akun</th>
                                            <th>Laporan</th>
                                            <th>Posisi</th>
                                            <th class="text-right">Saldo Awal</th>
                                            <th class="text-right">Mutasi Debit</th>
                                            <th class="text-right">Mutasi Kredit</th>
                                            <th class="text-right">Saldo Akhir</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="snap-inv" role="tabpanel">
                            <div class="table-responsive" style="max-height: 450px;">
                                <table class="table table-sm table-striped" id="tblSnapInv">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Gudang</th>
                                            <th>No Lot</th>
                                            <th>Expired</th>
                                            <th class="text-right">Qty Akhir</th>
                                            <th class="text-right">HPP Rata2</th>
                                            <th class="text-right">Total Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="snap-ar" role="tabpanel">
                            <div class="table-responsive" style="max-height: 450px;">
                                <table class="table table-sm table-striped" id="tblSnapAR">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Customer</th>
                                            <th>No Faktur</th>
                                            <th>Tgl Faktur</th>
                                            <th>Jatuh Tempo</th>
                                            <th class="text-right">Total Tagihan</th>
                                            <th class="text-right">Total Bayar</th>
                                            <th class="text-right">Sisa Piutang</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="snap-ap" role="tabpanel">
                            <div class="table-responsive" style="max-height: 450px;">
                                <table class="table table-sm table-striped" id="tblSnapAP">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Supplier</th>
                                            <th>No Dokumen</th>
                                            <th>Tgl Faktur</th>
                                            <th>Jatuh Tempo</th>
                                            <th class="text-right">Total Tagihan</th>
                                            <th class="text-right">Total Bayar</th>
                                            <th class="text-right">Sisa Hutang</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="snap-closing-journal" role="tabpanel">
                            <div id="closingJournalContainer" class="p-3">
                                <div class="text-muted text-center py-4">Tutup buku bulanan tidak memiliki jurnal penutup.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: PENGAJUAN REOPEN PERIODE -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalRequestReopen" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formRequestReopen">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-unlock mr-2"></i> Pengajuan Buka Periode (Reopen)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_periode" id="reopenPeriodeId">

                    <div class="alert alert-warning small">
                        <strong>Perhatian:</strong> Pembukaan periode lampau yang sudah tutup buku memerlukan persetujuan Manager Accounting / Direksi. Seluruh aktivitas audit trail akan dicatat secara permanen.
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Periode Terkunci</label>
                        <input type="text" class="form-control" id="reopenPeriodCode" readonly>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Alasan Pembukaan Kembali Periode <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="alasan" rows="4" required placeholder="Jelaskan alasan materiil pembukaan periode untuk keperluan audit..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold" id="btnSubmitReopen">
                        <i class="fas fa-paper-plane mr-1"></i> Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- SCRIPT INTERAKSI JQUERY & AJAX -->
<!-- ========================================================================= -->
<script>
$(document).ready(function() {
    var baseUrl = '<?= base_url() ?>';

    // Helper format rupiah
    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2 }).format(num || 0);
    }

    // 1. Jalankan Validasi Pre-Closing
    $('.btn-check-preclosing').on('click', function() {
        var idPeriode = $(this).data('id');
        var kodePeriode = $(this).data('kode');

        $('#valModalPeriodCode').text(kodePeriode);
        $('#valLoading').show();
        $('#valResultContainer').hide();
        $('#btnProceedClosingFromModal').hide();
        $('#modalPreClosing').modal('show');

        $.ajax({
            url: baseUrl + 'keuangan/tutup-buku/check',
            type: 'POST',
            data: { id_periode: idPeriode, tipe_closing: 'BULANAN' },
            dataType: 'json',
            success: function(res) {
                $('#valLoading').hide();
                $('#valResultContainer').show();

                if (res.success && res.data) {
                    var data = res.data;
                    var isPassed = data.status_overall === 'PASSED';

                    var alertClass = isPassed ? 'alert-success' : 'alert-danger';
                    var alertIcon = isPassed ? 'fa-check-circle' : 'fa-exclamation-triangle';
                    var alertMsg = isPassed 
                        ? '<strong>Status Validasi: LOLOS (PASSED).</strong> Seluruh prasyarat tutup buku terpenuhi.' 
                        : '<strong>Status Validasi: GAGAL (FAILED).</strong> Terdapat ' + data.blocking_count + ' anomali kritis yang menghalangi tutup buku.';

                    $('#valSummaryAlert').removeClass('alert-success alert-danger alert-warning').addClass(alertClass).html(
                        '<i class="fas ' + alertIcon + ' mr-2"></i>' + alertMsg
                    );

                    var htmlLogs = '';
                    $.each(data.logs, function(idx, log) {
                        var itemClass = 'rule-passed';
                        var badgeStatus = '<span class="badge-passed"><i class="fas fa-check mr-1"></i> PASSED</span>';

                        if (log.status === 'FAILED') {
                            if (log.tingkat_keparahan === 'BLOCKING') {
                                itemClass = 'rule-failed-blocking';
                                badgeStatus = '<span class="badge-failed"><i class="fas fa-times mr-1"></i> BLOCKING</span>';
                            } else {
                                itemClass = 'rule-failed-warning';
                                badgeStatus = '<span class="badge badge-warning text-dark"><i class="fas fa-exclamation mr-1"></i> WARNING</span>';
                            }
                        }

                        htmlLogs += '<div class="rule-item ' + itemClass + '">';
                        htmlLogs += '  <div class="d-flex justify-content-between align-items-center mb-1">';
                        htmlLogs += '    <h6 class="font-weight-bold mb-0 text-dark">' + log.rule_title + '</h6>';
                        htmlLogs += '    <div>' + badgeStatus + '</div>';
                        htmlLogs += '  </div>';
                        htmlLogs += '  <p class="text-muted small mb-1">' + log.deskripsi + '</p>';

                        if (log.difference_amount > 0) {
                            htmlLogs += '  <div class="small text-danger font-weight-bold">';
                            htmlLogs += '    Expected: ' + formatRupiah(log.expected_amount) + ' | Actual: ' + formatRupiah(log.actual_amount) + ' | Selisih: ' + formatRupiah(log.difference_amount);
                            htmlLogs += '  </div>';
                        }
                        htmlLogs += '</div>';
                    });

                    $('#valRuleList').html(htmlLogs);

                    if (isPassed) {
                        $('#btnProceedClosingFromModal').show().off('click').on('click', function() {
                            $('#modalPreClosing').modal('hide');
                            openClosingModal(idPeriode, kodePeriode);
                        });
                    }
                } else {
                    $('#valSummaryAlert').addClass('alert-danger').text(res.message || 'Gagal memproses validasi pre-closing.');
                }
            },
            error: function(xhr) {
                $('#valLoading').hide();
                $('#valResultContainer').show();
                var msg = 'Terjadi kesalahan sistem saat menjalankan pre-closing.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#valSummaryAlert').addClass('alert-danger').text(msg);
            }
        });
    });

    // 2. Buka Modal Konfirmasi Eksekusi Tutup Buku
    function openClosingModal(id, kode) {
        $('#closingPeriodeId').val(id);
        $('#closingPeriodLabel').text(kode);
        $('#modalExecuteClosing').modal('show');
    }

    $('.btn-open-closing-modal').on('click', function() {
        var id = $(this).data('id');
        var kode = $(this).data('kode');
        openClosingModal(id, kode);
    });

    $('#closingTipe').on('change', function() {
        if ($(this).val() === 'TAHUNAN') {
            $('#closingTipeHint').html('<strong class="text-warning">Perhatian Tutup Buku Tahunan:</strong> Sistem akan menolkan akun pendapatan dan beban lalu memindahkan selisih laba/rugi bersih ke Laba Ditahan (Retained Earnings).');
        } else {
            $('#closingTipeHint').text('Tutup buku bulanan mengunci periode dan membekukan snapshot saldo GL, persediaan, AR, dan AP.');
        }
    });

    // 3. Submit Eksekusi Tutup Buku
    $('#formExecuteClosing').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitClosing');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Memproses Tutup Buku...');

        $.ajax({
            url: baseUrl + 'keuangan/tutup-buku/execute',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Jalankan Tutup Buku Sekarang');
                if (res.success) {
                    alert('Sukses: ' + res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + res.message);
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Jalankan Tutup Buku Sekarang');
                var msg = 'Terjadi kesalahan saat mengeksekusi tutup buku.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert('Peringatan: ' + msg);
            }
        });
    });

    // 4. Lihat Snapshot Closing Detail
    $('.btn-view-closing-detail').on('click', function() {
        var idClosing = $(this).data('id');
        $.ajax({
            url: baseUrl + 'keuangan/tutup-buku/detail/' + idClosing,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.data) {
                    var d = res.data;
                    var h = d.header;

                    $('#snapModalTitle').text(h.kode_periode + ' (v' + h.closing_version + ' - ' + h.tipe_closing + ')');
                    $('#snapEksekutor').text(h.eksekutor_name || 'System');
                    $('#snapWaktu').text(h.waktu_eksekusi);
                    $('#snapChecksum').text(h.checksum);

                    // Setup export links
                    $('#btnExportGL').attr('href', baseUrl + 'keuangan/tutup-buku/export/' + idClosing + '/accounts');
                    $('#btnExportInv').attr('href', baseUrl + 'keuangan/tutup-buku/export/' + idClosing + '/inventory');
                    $('#btnExportAR').attr('href', baseUrl + 'keuangan/tutup-buku/export/' + idClosing + '/ar');
                    $('#btnExportAP').attr('href', baseUrl + 'keuangan/tutup-buku/export/' + idClosing + '/ap');

                    // 4A. Render GL Table
                    var glRows = '';
                    $.each(d.accounts || [], function(i, acc) {
                        glRows += '<tr>';
                        glRows += '<td><strong>' + acc.kode_akun + '</strong></td>';
                        glRows += '<td>' + acc.nama_akun + '</td>';
                        glRows += '<td><span class="badge badge-light">' + acc.jenis_laporan + '</span></td>';
                        glRows += '<td>' + acc.saldo_normal + '</td>';
                        glRows += '<td class="text-right">' + formatRupiah(acc.saldo_awal) + '</td>';
                        glRows += '<td class="text-right">' + formatRupiah(acc.mutasi_debit) + '</td>';
                        glRows += '<td class="text-right">' + formatRupiah(acc.mutasi_kredit) + '</td>';
                        glRows += '<td class="text-right font-weight-bold">' + formatRupiah(acc.saldo_akhir) + '</td>';
                        glRows += '</tr>';
                    });
                    $('#tblSnapGL tbody').html(glRows || '<tr><td colspan="8" class="text-center">Tidak ada data akun</td></tr>');

                    // 4B. Render Inventory Table
                    var invRows = '';
                    $.each(d.inventory || [], function(i, inv) {
                        invRows += '<tr>';
                        invRows += '<td><strong>' + inv.kd_barang + '</strong></td>';
                        invRows += '<td>' + (inv.nama_barang || '-') + '</td>';
                        invRows += '<td>' + inv.gudang_id + '</td>';
                        invRows += '<td>' + inv.batch_no + '</td>';
                        invRows += '<td>' + (inv.expired_date || '-') + '</td>';
                        invRows += '<td class="text-right">' + Number(inv.qty_akhir).toLocaleString('id-ID') + '</td>';
                        invRows += '<td class="text-right">' + formatRupiah(inv.hpp_rata_rata) + '</td>';
                        invRows += '<td class="text-right font-weight-bold">' + formatRupiah(inv.total_nilai_stok) + '</td>';
                        invRows += '</tr>';
                    });
                    $('#tblSnapInv tbody').html(invRows || '<tr><td colspan="8" class="text-center">Tidak ada data persediaan</td></tr>');

                    // 4C. Render AR Table
                    var arRows = '';
                    $.each(d.ar || [], function(i, ar) {
                        arRows += '<tr>';
                        arRows += '<td>' + (ar.customer_name || ar.kd_customer) + '</td>';
                        arRows += '<td><strong>' + ar.no_faktur + '</strong></td>';
                        arRows += '<td>' + ar.tanggal_faktur + '</td>';
                        arRows += '<td>' + (ar.tanggal_jatuh_tempo || '-') + '</td>';
                        arRows += '<td class="text-right">' + formatRupiah(ar.total_tagihan) + '</td>';
                        arRows += '<td class="text-right">' + formatRupiah(ar.total_bayar) + '</td>';
                        arRows += '<td class="text-right text-danger font-weight-bold">' + formatRupiah(ar.sisa_piutang) + '</td>';
                        arRows += '</tr>';
                    });
                    $('#tblSnapAR tbody').html(arRows || '<tr><td colspan="7" class="text-center">Tidak ada faktur piutang terbuka</td></tr>');

                    // 4D. Render AP Table
                    var apRows = '';
                    $.each(d.ap || [], function(i, ap) {
                        apRows += '<tr>';
                        apRows += '<td>' + (ap.nama_suplier || ap.kd_suplier) + '</td>';
                        apRows += '<td><strong>' + ap.nomor_dokumen + '</strong></td>';
                        apRows += '<td>' + ap.tanggal_faktur + '</td>';
                        apRows += '<td>' + (ap.tanggal_jatuh_tempo || '-') + '</td>';
                        apRows += '<td class="text-right">' + formatRupiah(ap.total_tagihan) + '</td>';
                        apRows += '<td class="text-right">' + formatRupiah(ap.total_bayar) + '</td>';
                        apRows += '<td class="text-right text-danger font-weight-bold">' + formatRupiah(ap.sisa_hutang) + '</td>';
                        apRows += '</tr>';
                    });
                    $('#tblSnapAP tbody').html(apRows || '<tr><td colspan="7" class="text-center">Tidak ada tagihan hutang terbuka</td></tr>');

                    // 4E. Render Jurnal Penutup
                    if (d.closing_journal) {
                        var cj = d.closing_journal;
                        var cjHtml = '<div class="card border mb-3">';
                        cjHtml += '  <div class="card-header bg-light d-flex justify-content-between">';
                        cjHtml += '    <strong>' + cj.nomor_jurnal + '</strong>';
                        cjHtml += '    <span>' + cj.tanggal_transaksi + ' | Status: <span class="badge badge-success">' + cj.status + '</span></span>';
                        cjHtml += '  </div>';
                        cjHtml += '  <div class="card-body p-0">';
                        cjHtml += '    <table class="table table-sm mb-0">';
                        cjHtml += '      <thead><tr><th>Baris</th><th>Akun</th><th>Keterangan</th><th class="text-right">Debit</th><th class="text-right">Kredit</th></tr></thead><tbody>';
                        $.each(cj.details || [], function(idx, line) {
                            cjHtml += '<tr><td>' + line.nomor_baris + '</td><td>' + line.kode_akun + ' - ' + line.nama_akun + '</td><td>' + (line.keterangan || '-') + '</td><td class="text-right">' + formatRupiah(line.debit) + '</td><td class="text-right">' + formatRupiah(line.kredit) + '</td></tr>';
                        });
                        cjHtml += '    </tbody></table>';
                        cjHtml += '  </div>';
                        cjHtml += '</div>';
                        $('#closingJournalContainer').html(cjHtml);
                    } else {
                        $('#closingJournalContainer').html('<div class="text-muted text-center py-4">Tutup buku ini tidak memiliki jurnal penutup tahunan.</div>');
                    }

                    $('#modalClosingDetail').modal('show');
                }
            }
        });
    });

    // 5. Permohonan Buka Periode (Reopen)
    $('.btn-open-reopen-modal').on('click', function() {
        var id = $(this).data('id');
        var kode = $(this).data('kode');
        $('#reopenPeriodeId').val(id);
        $('#reopenPeriodCode').val(kode);
        $('#modalRequestReopen').modal('show');
    });

    $('#formRequestReopen').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnSubmitReopen');
        $btn.prop('disabled', true).text('Mengirim...');

        $.ajax({
            url: baseUrl + 'keuangan/tutup-buku/reopen-request',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).text('Kirim Permohonan');
                if (res.success) {
                    alert('Sukses: ' + res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + res.message);
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Kirim Permohonan');
                alert('Gagal mengirim permohonan buka periode.');
            }
        });
    });

    // 6. Approval / Reject Reopen oleh Manajer
    $('.btn-process-reopen').on('click', function() {
        var idReopen = $(this).data('id');
        var action = $(this).data('action');
        var promptText = action === 'APPROVE' 
            ? 'Konfirmasi: Apakah Anda yakin menyetujui pembukaan kembali periode ini? (Jurnal penutup akan direversal jika ada).' 
            : 'Konfirmasi: Apakah Anda yakin menolak permohonan buka periode ini?';

        if (!confirm(promptText)) {
            return;
        }

        var catatan = prompt('Masukkan catatan approval/penolakan:', '');
        if (catatan === null) return;

        $.ajax({
            url: baseUrl + 'keuangan/tutup-buku/reopen-action',
            type: 'POST',
            data: { id_reopen: idReopen, action: action, catatan: catatan },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alert('Sukses: ' + res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + res.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat memproses permohonan reopen.');
            }
        });
    });
});
</script>
