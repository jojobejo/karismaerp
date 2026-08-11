<?php
// Helper format rupiah
function fmt_rp($angka) {
    return $angka != 0 ? number_format((float)$angka, 0, ',', '.') : '-';
}
?>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0 fw-bold">
                    <i class="fas fa-book-open me-2 text-primary"></i>Report Mutasi Kasir
                </h4>
                <small class="text-muted">
                    <?php if ($saldo_kasir): ?>
                        Akun: <strong><?= htmlspecialchars($saldo_kasir->kode_akun . ' - ' . $saldo_kasir->nama_akun) ?></strong>
                    <?php else: ?>
                        <span class="text-warning">Akun kasir belum dikonfigurasi</span>
                    <?php endif; ?>
                </small>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= site_url('keuangan/kasir') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
                <a href="<?= site_url('keuangan/kasir/print_mutasi?tanggal_awal=' . $tanggal_awal . '&tanggal_akhir=' . $tanggal_akhir) ?>"
                   target="_blank" id="btn-print"
                   class="btn btn-success btn-sm">
                    <i class="fas fa-print me-1"></i>Print
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body py-3">
                <form method="get" action="<?= site_url('keuangan/kasir/report_mutasi') ?>" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label small mb-1">Tanggal Awal</label>
                        <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($tanggal_awal) ?>" required>
                    </div>
                    <div class="col-auto">
                        <label class="form-label small mb-1">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($tanggal_akhir) ?>" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter me-1"></i>Tampilkan
                        </button>
                    </div>
                    <!-- Tombol shortcut periode -->
                    <div class="col-auto ms-2">
                        <a href="<?= site_url('keuangan/kasir/report_mutasi?tanggal_awal=' . date('Y-m-d') . '&tanggal_akhir=' . date('Y-m-d')) ?>"
                           class="btn btn-outline-info btn-sm">Hari Ini</a>
                        <a href="<?= site_url('keuangan/kasir/report_mutasi?tanggal_awal=' . date('Y-m-01') . '&tanggal_akhir=' . date('Y-m-d')) ?>"
                           class="btn btn-outline-secondary btn-sm">Bulan Ini</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="text-muted small mb-1">Saldo Awal Periode</div>
                    <div class="fw-bold fs-6 text-dark">Rp <?= number_format($saldo_awal, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="text-muted small mb-1">Total Kas Masuk (Debit)</div>
                    <div class="fw-bold fs-6 text-success">Rp <?= number_format($total_debit, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3">
                    <div class="text-muted small mb-1">Total Kas Keluar (Kredit)</div>
                    <div class="fw-bold fs-6 text-danger">Rp <?= number_format($total_kredit, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center py-3" style="border-left: 4px solid #0d6efd !important;">
                    <div class="text-muted small mb-1">Saldo Akhir</div>
                    <div class="fw-bold fs-5 text-primary">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- Tabel Mutasi -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="fas fa-list me-2 text-primary"></i>
                    Daftar Mutasi Kas
                    <small class="text-muted">
                        <?= date('d/m/Y', strtotime($tanggal_awal)) ?> s/d <?= date('d/m/Y', strtotime($tanggal_akhir)) ?>
                    </small>
                </span>
                <span class="badge bg-primary"><?= count($transaksi) ?> transaksi</span>    
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm mb-0" id="tbl-mutasi">
                        <thead class="text-center" style="font-size:12px;">
                            <tr>
                                <th class="py-2" style="width:50px;">No</th>
                                <th class="py-2" style="width:100px;">Tanggal</th>
                                <th class="py-2" style="width:120px;">No. Transaksi</th>
                                <th class="py-2 text-start">Uraian / Keterangan</th>
                                <th class="py-2" style="width:140px;">Debit (Masuk)</th>
                                <th class="py-2" style="width:140px;">Kredit (Keluar)</th>
                                <th class="py-2" style="width:150px;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody style="font-size:12px;">
                            <!-- Baris Saldo Awal -->
                            <tr class="fw-bold">
                                <td colspan="3" class="text-center">-</td>
                                <td>Saldo Awal Periode</td>
                                <td class="text-end"><?= fmt_rp($saldo_awal) ?></td>
                                <td class="text-center">-</td>
                                <td class="text-end"><?= number_format($saldo_awal, 0, ',', '.') ?></td>
                            </tr>

                            <?php if (empty($transaksi)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Tidak ada transaksi pada periode ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $no = 1;
                                $last_date = '';
                                foreach ($transaksi as $row):
                                    // Tampilkan pemisah tanggal jika berbeda
                                    if ($row['tanggal'] !== $last_date):
                                        $last_date = $row['tanggal'];
                                ?>
                                    <tr>
                                        <td colspan="7" class="fw-bold ps-3 py-1" style="font-size:11px;">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            <?= date('l, d F Y', strtotime($row['tanggal'])) ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['tanggal_fmt']) ?></td>
                                    <td class="text-center">
                                        <span class="fw-normal" style="font-size:11px;">
                                            <?= htmlspecialchars($row['no_transaksi']) ?>
                                        </span>
                                        <?php if ($row['jenis_transaksi'] === 'kas_keluar' && !empty($row['is_settled'])): ?>
                                            <br><span class="fw-normal mt-1" style="font-size:10px;" title="Sudah diinput Kas Masuk">
                                                (Selesai)
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($row['pilihan'] ?? '-') ?></div>
                                        <?php if (!empty($row['id_ref'])): ?>
                                            <small class="fw-bold"><i class="fas fa-link me-1"></i>Kas Masuk (Ref: <?= htmlspecialchars($row['ref_no_transaksi'] ?? '') ?>)</small>
                                        <?php endif; ?>
                                        <?php if (!empty($row['keterangan'])): ?>
                                            <small class="d-block"><?= htmlspecialchars($row['keterangan']) ?></small>
                                        <?php endif; ?>
                                        <small class="d-block"><?= htmlspecialchars($row['nama_user'] ?? '') ?></small>
                                    </td>
                                    <td class="text-end <?= $row['debit'] > 0 ? 'fw-semibold' : '' ?>">
                                        <?= $row['debit'] > 0 ? fmt_rp($row['debit']) : '-' ?>
                                    </td>
                                    <td class="text-end <?= $row['kredit'] > 0 ? 'fw-semibold' : '' ?>">
                                        <?= $row['kredit'] > 0 ? fmt_rp($row['kredit']) : '-' ?>
                                    </td>
                                    <td class="text-end fw-bold">
                                        <?= number_format($row['saldo_berjalan'], 0, ',', '.') ?>
                                    </td>
                                </tr>

                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Baris Total -->
                            <tr class="fw-bold">
                                <td colspan="4" class="text-end">SALDO KAS BUKU</td>
                                <td class="text-end"><?= number_format($total_debit, 0, ',', '.') ?></td>
                                <td class="text-end"><?= number_format($total_kredit, 0, ',', '.') ?></td>
                                <td class="text-end fs-6"><?= number_format($saldo_akhir, 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

