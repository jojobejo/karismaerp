<?php
// Helper format rupiah
function fmt_num($angka) {
    return $angka != 0 ? number_format((float)$angka, 0, ',', '.') : '-';
}
$tgl_fmt = date('d-M-y', strtotime($tanggal));
?>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="container-fluid py-3">

            <!-- Baris Navigasi & Filter -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= site_url('keuangan/kasir') ?>" class="btn btn-outline-secondary btn-sm mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Kasir
                    </a>
                    <h5 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-book-open text-primary mr-1"></i> Laporan Mutasi Kas Harian
                    </h5>
                </div>
                <div class="d-flex align-items-center">
                    <form method="get" action="<?= site_url('keuangan/kasir/report_mutasi') ?>" class="form-inline mr-2">
                        <label class="mr-2 small font-weight-bold">Pilih Tanggal:</label>
                        <input type="date" name="tanggal" class="form-control form-control-sm mr-2" value="<?= htmlspecialchars($tanggal) ?>">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search mr-1"></i> Tampilkan
                        </button>
                    </form>
                    <a href="<?= site_url('keuangan/kasir/print_mutasi?tanggal=' . $tanggal) ?>" target="_blank" class="btn btn-success btn-sm font-weight-bold">
                        <i class="fas fa-print mr-1"></i> Cetak Mutasi
                    </a>
                </div>
            </div>

            <!-- Tampilan Kertas Laporan Buku Harian Kas (Excel Style) -->
            <div class="card shadow-sm border-secondary">
                <div class="card-body p-4 bg-white" style="font-family: 'Courier New', Courier, monospace; font-size: 13px;">
                    
                    <!-- Header Dokumen Buku Harian -->
                    <table class="table table-borderless table-sm mb-2" style="border-bottom: 2px solid #000;">
                        <tr>
                            <td style="width:25%;"><strong>Jenis Buku Harian:</strong><br>Kas/Gab</td>
                            <td style="width:25%;"><strong>No Perk:</strong><br>102</td>
                            <td style="width:25%;"><strong>Periode:</strong><br><?= $tgl_fmt ?></td>
                            <td style="width:25%; text-align:right;"><strong>Halaman:</strong><br>01/</td>
                        </tr>
                    </table>

                    <!-- Tabel Utama Mutasi -->
                    <table class="table table-bordered table-sm text-dark" style="border: 1px solid #000;">
                        <thead class="text-center bg-light" style="border-bottom: 2px solid #000;">
                            <tr>
                                <th style="width: 35px;">No</th>
                                <th style="width: 120px;">No Bkt</th>
                                <th>Uraian</th>
                                <th style="width: 130px;" class="text-right">Debet</th>
                                <th style="width: 130px;" class="text-right">Kredit</th>
                                <th style="width: 140px;" class="text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $saldo = (float)$saldo_awal;
                            $total_debet = 0;
                            $total_kredit = 0;
                            ?>

                            <!-- Saldo Awal -->
                            <tr class="font-weight-bold bg-light">
                                <td class="text-center">-</td>
                                <td>-</td>
                                <td><strong>Mutasi Saldo (Saldo Awal)</strong></td>
                                <td class="text-right">-</td>
                                <td class="text-right">-</td>
                                <td class="text-right"><strong><?= number_format($saldo, 0, ',', '.') ?></strong></td>
                            </tr>

                            <!-- Sub-header 1: Kas Keluar / UM Keluar Outstanding -->
                            <tr class="bg-light">
                                <td colspan="6" class="font-weight-bold text-danger py-1" style="border-top: 2px solid #000; border-bottom: 1px solid #000;">
                                    --- DAFTAR KAS KELUAR / UM KELUAR (OUTSTANDING) ---
                                </td>
                            </tr>

                            <?php if (!empty($kas_keluar_outstanding)): ?>
                                <?php foreach ($kas_keluar_outstanding as $kk): 
                                    $nominal_kredit = (float)$kk['nominal'];
                                    $saldo -= $nominal_kredit;
                                    $total_kredit += $nominal_kredit;
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><small class="font-weight-bold"><?= htmlspecialchars($kk['no_transaksi']) ?></small></td>
                                    <td><?= htmlspecialchars($kk['pilihan']) ?></td>
                                    <td class="text-right">-</td>
                                    <td class="text-right text-danger"><?= number_format($nominal_kredit, 0, ',', '.') ?></td>
                                    <td class="text-right"><?= number_format($saldo, 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted font-italic py-1">Tidak ada Kas Keluar outstanding</td>
                                </tr>
                            <?php endif; ?>

                            <!-- Sub-header 2: Kas Masuk Harian -->
                            <tr class="bg-light">
                                <td colspan="6" class="font-weight-bold text-success py-1" style="border-top: 2px solid #000; border-bottom: 1px solid #000;">
                                    --- DAFTAR KAS MASUK HARIAN ---
                                </td>
                            </tr>

                            <?php if (!empty($kas_masuk_harian)): ?>
                                <?php foreach ($kas_masuk_harian as $km): 
                                    $nominal_debet  = (float)$km['nominal'];
                                    $nominal_kredit = (float)($km['nominal_kredit_induk'] ?? 0);
                                    $saldo += ($nominal_debet - $nominal_kredit);
                                    $total_debet  += $nominal_debet;
                                    $total_kredit += $nominal_kredit;
                                    $no_bkt = !empty($km['no_bukti']) ? $km['no_bukti'] : $km['no_transaksi'];
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><small class="font-weight-bold"><?= htmlspecialchars($no_bkt) ?></small></td>
                                    <td><?= htmlspecialchars($km['pilihan']) ?></td>
                                    <td class="text-right text-success"><?= number_format($nominal_debet, 0, ',', '.') ?></td>
                                    <td class="text-right text-danger"><?= $nominal_kredit > 0 ? number_format($nominal_kredit, 0, ',', '.') : '-' ?></td>
                                    <td class="text-right"><?= number_format($saldo, 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted font-italic py-1">Tidak ada Kas Masuk pada tanggal ini</td>
                                </tr>
                            <?php endif; ?>

                            <!-- Baris Total Footer -->
                            <tr class="font-weight-bold bg-light" style="border-top: 2px solid #000;">
                                <td colspan="3" class="text-right">TOTAL MUTASI:</td>
                                <td class="text-right text-success"><?= number_format($total_debet, 0, ',', '.') ?></td>
                                <td class="text-right text-danger"><?= number_format($total_kredit, 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($saldo, 0, ',', '.') ?></td>
                            </tr>
                            <tr class="font-weight-bold" style="border-top: 2px solid #000; background-color: #e9ecef;">
                                <td colspan="3" class="text-right">SALDO AKHIR KAS BUKU:</td>
                                <td colspan="3" class="text-right text-primary h6 m-0 font-weight-bold">
                                    Rp <?= number_format($saldo, 0, ',', '.') ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>
