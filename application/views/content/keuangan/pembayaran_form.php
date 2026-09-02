<?php
$is_draft_mode = isset($is_draft_mode) && $is_draft_mode && !empty($draft_payment);
?>
<body class="hold-transition sidebar-mini sidebar-collapse">
<?php
$pending_bg = $pending_bg ?? null;
$is_bg_cair_mode = !empty($is_bg_cair_mode);

// Ambil akun klasifikasi Harta dinamis dari controller atau query langsung jika belum diset
if (!isset($akun_harta) || !is_array($akun_harta)) {
    if ($this->db->table_exists('tbkeu_akun')) {
        $this->db->select('a.id_akun, a.kode_akun, a.nama_akun, a.tipe_kontrol');
        $this->db->from('tbkeu_akun a');
        if ($this->db->table_exists('tbkeu_klasifikasi_akun')) {
            $this->db->join('tbkeu_klasifikasi_akun k', 'k.id_klasifikasi = a.id_klasifikasi', 'left');
            $this->db->where("(a.id_klasifikasi = 1 OR LOWER(COALESCE(k.nama_klasifikasi, '')) = 'harta' OR k.kode_klasifikasi = '1')", null, false);
        } else {
            $this->db->where('a.id_klasifikasi', 1);
        }
        if ($this->db->field_exists('tipe_kontrol', 'tbkeu_akun')) {
            $this->db->where_in('a.tipe_kontrol', ['KAS', 'BANK', 'kas', 'bank']);
        }
        if ($this->db->field_exists('is_active', 'tbkeu_akun')) {
            $this->db->where('a.is_active', 1);
        }
        if ($this->db->field_exists('tipe_akun', 'tbkeu_akun')) {
            $this->db->where("(a.tipe_akun != 'HEADER' OR a.tipe_akun IS NULL)", null, false);
        }
        $this->db->order_by('a.nama_akun', 'ASC');
        $akun_harta = $this->db->get()->result_array();
    } else {
        $akun_harta = [];
    }
}

$metode_options = [];
if (!empty($akun_harta)) {
    foreach ($akun_harta as $acc) {
        $metode_options[$acc['nama_akun']] = $acc['nama_akun'];
    }
}
$nama_akun_retur = 'Q Hutang Non Dagang (Retur Penjualan yg blm dipot)';
$metode_options[$nama_akun_retur] = $nama_akun_retur;

$default_metode = '';
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
                        <?php
                          $total_instruksi_potongan = 0;
                          foreach ($linked_returs as $ret) {
                              if (preg_match('/Instruksi Potong Faktur .*? senilai Rp ([\d\.]+)/', $ret['catatan_collection'], $m)) {
                                  $total_instruksi_potongan += (float) str_replace('.', '', $m[1]);
                              }
                          }
                          ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-2 bg-white text-dark small" style="border-radius: 4px; overflow: hidden;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No. Retur</th>
                                        <th>Tipe Retur</th>
                                        <th>Tanggal Retur</th>
                                        <th class="text-right">Nominal Pemotongan</th>
                                        <th>Catatan</th>
                                        <th class="text-right">Nominal Retur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($linked_returs as $ret): ?>
                                        <?php 
                                        $n_retur = trim((string)($ret['no_retur'] ?? ''));
                                        $n_spr = trim((string)($ret['no_spr'] ?? ''));
                                        
                                        $no_retur_display = 'RETUR #' . $ret['id_retur'];
                                        if ($n_retur !== '') {
                                            $no_retur_display = $n_retur;
                                        } elseif ($n_spr !== '') {
                                            $no_retur_display = $n_spr;
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="<?= base_url('retur_penjualan/retur/detail/' . $ret['id_retur']) ?>" class="font-weight-bold text-dark" target="_blank">
                                                    <?= htmlspecialchars($no_retur_display, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> 
                                                    <i class="fas fa-external-link-alt ml-1" style="font-size: 0.8em;"></i>
                                                </a>
                                            </td>
                                            <td><span class="badge badge-secondary"><?= htmlspecialchars(ucfirst($ret['tipe_retur'])) ?></span></td>
                                            <td><?= date('d/m/Y', strtotime($ret['tanggal_retur'])) ?></td>
                                            <?php
                                            $cat_raw = $ret['catatan_collection'] ?? '';
                                            $nom_potong = 0;
                                            $cat_bersih = $cat_raw;
                                            if (preg_match('/Instruksi Potong Faktur .*? senilai Rp ([\d\.]+)\.?\s*(.*)$/si', $cat_raw, $m)) {
                                                $nom_potong = (float) str_replace('.', '', $m[1]);
                                                $cat_bersih = trim($m[2]);
                                            } elseif (preg_match('/Instruksi Potong Faktur .*? senilai Rp ([\d\.]+)/i', $cat_raw, $m)) {
                                                $nom_potong = (float) str_replace('.', '', $m[1]);
                                                $cat_bersih = trim(str_replace($m[0], '', $cat_raw));
                                            }
                                            ?>
                                            <td class="text-right font-weight-bold text-primary">Rp <?= number_format($nom_potong, 0, ',', '.') ?></td>
                                            <td><?= nl2br(htmlspecialchars($cat_bersih)) ?></td>
                                            <td class="text-right font-weight-bold text-success">Rp <?= number_format((float)$ret['total_retur'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if ($total_instruksi_potongan > 0): ?>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="3" class="text-right font-weight-bold text-dark">TOTAL INSTRUKSI POTONGAN UNTUK FAKTUR INI:</td>
                                        <td class="text-right font-weight-bold text-danger" style="font-size: 1.1em;">Rp <?= number_format($total_instruksi_potongan, 0, ',', '.') ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
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
                                        <td class="text-muted">Tanggal Tempo</td>
                                        <td><?= !empty($faktur['tanggal_jatuh_tempo']) ? date('d/m/Y', strtotime($faktur['tanggal_jatuh_tempo'])) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Customer</td>
                                        <td><?= htmlspecialchars($faktur['nama_customer']) ?></td>
                                    </tr>
                                     <?php if (!empty($faktur['nama_barang']) && $faktur['nama_barang'] !== '-'): ?>
                                     <tr>
                                         <td class="text-muted">Barang</td>
                                         <td>
                                             <button type="button" class="btn btn-xs btn-outline-info" id="btnLihatDetailFaktur" data-id-faktur="<?= $faktur['id_faktur'] ?>">
                                                 <i class="fas fa-eye mr-1"></i>Lihat Detail
                                             </button>
                                         </td>
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
                                        <td class="text-muted">Total Piutang</td>
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
                                        <td class="text-muted">Sisa Piutang</td>
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
                                                    <td>
                                                        <?= htmlspecialchars($row['metode_pembayaran'] ?: '-') ?>
                                                        <?php if (!empty($row['no_bg']) || !empty($row['nama_bank'])): ?>
                                                            <br><small class="text-muted">
                                                                <?= !empty($row['no_bg']) ? 'No. BG: ' . htmlspecialchars($row['no_bg']) : '' ?>
                                                                <?= (!empty($row['no_bg']) && !empty($row['nama_bank'])) ? ' | ' : '' ?>
                                                                <?= !empty($row['nama_bank']) ? 'Bank: ' . htmlspecialchars($row['nama_bank']) : '' ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($is_bg_row): ?>
                                                            <span class="badge badge-<?= $is_bg_cair ? 'success' : 'warning' ?>">
                                                                <?= $is_bg_cair ? 'Sudah Cair' : 'Belum Cair' ?>
                                                            </span>
                                                            <?php if (!$is_bg_cair): ?>
                                                                <a href="<?= base_url('keuangan/pembayaran/bayar/' . $faktur['id_faktur'] . '?cair_bg=' . $row['id_pembayaran']) ?>"
                                                                   class="btn btn-xs btn-outline-warning ml-1 font-weight-bold"
                                                                   title="Klik untuk konfirmasi pencairan BG ini">
                                                                    <i class="fas fa-check-circle"></i> Cairkan
                                                                </a>
                                                            <?php endif; ?>
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
                                                    <td class="text-right">
                                                        Rp <?= number_format((float)$row['jumlah_pembayaran'], 0, ',', '.') ?>
                                                        <?php if ((float)($row['jumlah_diskon'] ?? 0) > 0): ?>
                                                            <br><small class="text-muted">(Diskon: Rp <?= number_format((float)$row['jumlah_diskon'], 0, ',', '.') ?>)</small>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card card-outline <?= ($is_draft_mode || $is_bg_cair_mode) ? 'card-warning' : 'card-success' ?>">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold">
                                    <i class="fas <?= $is_draft_mode ? 'fa-file-invoice' : ($is_validasi_kasir_mode ? 'fa-cash-register' : ($is_bg_cair_mode ? 'fa-check-circle' : 'fa-plus-circle')) ?> mr-1"></i>
                                    <?= $is_draft_mode ? 'Edit & Posting Draft Pembayaran' : ($is_validasi_kasir_mode ? 'Validasi Pembayaran Kasir' : ($is_bg_cair_mode ? 'Konfirmasi BG Sudah Cair' : 'Form Pembayaran')) ?>
                                </h3>
                                <?php if ($is_bg_cair_mode || $is_draft_mode): ?>
                                    <div class="card-tools">
                                        <a href="<?= base_url('keuangan/pembayaran/bayar/' . $faktur['id_faktur']) ?>" class="btn btn-xs btn-outline-secondary">
                                            <i class="fas fa-arrow-left mr-1"></i> Batal / Form Pembayaran Baru
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <form id="formPembayaran" action="<?= $is_validasi_kasir_mode ? base_url('keuangan/pembayaran/approve_kasir/' . $validasi_kasir['id_pembayaran']) : ($is_bg_cair_mode ? base_url('keuangan/pembayaran/cair/' . $pending_bg['id_pembayaran']) : base_url('keuangan/pembayaran/simpan/' . $faktur['id_faktur'])) ?>"
                                  method="post"
                                  <?= $is_validasi_kasir_mode ? "onsubmit=\"return confirm('Validasi dan proses pembayaran kasir ini?');\"" : ($is_bg_cair_mode ? "onsubmit=\"return confirm('Tandai BG ini sudah cair dan kurangi sisa tagihan?');\"" : '') ?>>
                                <div class="card-body">
                                    <?php if ($is_draft_mode): ?>
                                        <input type="hidden" name="id_pembayaran_draft" value="<?= $draft_payment['id_pembayaran'] ?>">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-edit mr-1"></i>
                                            <strong>Mode Draft Pembayaran:</strong> Menampilkan draft pembayaran <strong>#PAY-<?= sprintf('%05d', $draft_payment['id_pembayaran']) ?></strong> yang belum terposting. Silakan periksa atau sesuaikan data di bawah, lalu klik <strong>Simpan & Posting</strong>.
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($is_lunas) && $is_lunas && !$is_validasi_kasir_mode && !$is_bg_cair_mode && !$is_draft_mode): ?>
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Faktur ini sudah dibayar lunas. Anda dapat melihat riwayat pembayarannya di samping.
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($is_validasi_kasir_mode): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <strong>Validasi Pembayaran Kasir:</strong> Kasir telah menginput pembayaran ini. Silakan periksa nominal dan tanggal. Klik <strong>Simpan Validasi</strong> untuk menyetujui.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="form-group">
                                        <label>Metode Pembayaran <span class="text-danger">*</span></label>
                                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required <?= $is_validasi_kasir_mode ? 'readonly style="pointer-events: none;"' : '' ?>>
                                            <?php
                                             $selected_metode = $is_draft_mode ? ($draft_payment['metode_pembayaran'] ?? $default_metode) : ($is_validasi_kasir_mode ? 'Q Kas' : ($is_bg_cair_mode ? ($pending_bg['metode_pembayaran'] ?? $default_metode) : $default_metode));
                                             foreach ($metode_options as $value => $label):
                                                 $disabled_attr = '';
                                                 if (($value === $nama_akun_retur || $value === 'Q Hutang Non Dagang') && (float)$saldo_retur <= 0) {
                                                     $disabled_attr = 'disabled';
                                                     $label .= ' (Tidak ada saldo)';
                                                 }
                                                 $is_selected = strtolower($selected_metode) === strtolower($value) ? 'selected' : '';
                                             ?>
                                                <option value="<?= htmlspecialchars($value) ?>" <?= $is_selected ?> <?= $disabled_attr ?>>
                                                    <?= htmlspecialchars($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                            <option value="bg" <?= strtolower($selected_metode) === 'bg' ? 'selected' : '' ?>>BG / Cek</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <?php
                                            $is_bg_checked = $is_draft_mode ? (strtolower($draft_payment['metode_pembayaran'] ?? '') === 'bg' || ($draft_payment['status_bg'] ?? '') !== 'not_bg') : ($is_bg_cair_mode ? true : false);
                                            ?>
                                            <input type="checkbox" class="custom-control-input" id="check_is_bg" name="is_bg" value="1" <?= $is_bg_checked ? 'checked' : '' ?> <?= $is_validasi_kasir_mode ? 'disabled' : '' ?>>
                                            <label class="custom-control-label font-weight-bold" for="check_is_bg">
                                                <i class="fas fa-money-check mr-1 text-primary"></i> BG (Bilyet Giro / Cek)
                                            </label>
                                        </div>
                                        <small class="text-muted">Centang jika pembayaran ini menggunakan BG / Cek.</small>
                                    </div>

                                    <div class="row" id="no_bg_nama_bank_group" style="<?= (!$is_bg_checked && $default_metode !== 'bg') ? 'display:none;' : '' ?>">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>No. BG / Cek</label>
                                                <input type="text" name="no_bg" class="form-control" placeholder="Contoh: BG-123456"
                                                       value="<?= $is_draft_mode ? htmlspecialchars($draft_payment['no_bg'] ?? '') : ($is_bg_cair_mode ? htmlspecialchars($pending_bg['no_bg'] ?? '') : '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nama Bank Pembayar / Penerbit</label>
                                                <input type="text" name="nama_bank" class="form-control" placeholder="Contoh: BCA / Mandiri / BRI"
                                                       value="<?= $is_draft_mode ? htmlspecialchars($draft_payment['nama_bank'] ?? '') : ($is_bg_cair_mode ? htmlspecialchars($pending_bg['nama_bank'] ?? '') : '') ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tanggal Pembayaran <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_pembayaran" class="form-control" required
                                               value="<?= $is_draft_mode ? htmlspecialchars($draft_payment['tanggal_pembayaran']) : ($is_validasi_kasir_mode ? htmlspecialchars($validasi_kasir['tanggal_pembayaran']) : ($is_bg_cair_mode ? htmlspecialchars($pending_bg['tanggal_pembayaran']) : date('Y-m-d'))) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Jumlah Pembayaran <span class="text-danger">*</span></label>
                                        <?php
                                        $max_bayar = (float)$faktur['sisa_tagihan'];
                                        if ($is_validasi_kasir_mode) {
                                            $max_bayar += (float)($validasi_kasir['jumlah_pembayaran'] ?? 0);
                                        } elseif ($is_bg_cair_mode) {
                                            $max_bayar += (float)($pending_bg['jumlah_pembayaran'] ?? 0);
                                        }

                                        if ($is_draft_mode) {
                                            $raw_bayar = (float)$draft_payment['jumlah_pembayaran'];
                                        } elseif ($is_validasi_kasir_mode) {
                                            $raw_bayar = (float)$validasi_kasir['jumlah_pembayaran'];
                                        } elseif ($is_bg_cair_mode) {
                                            $raw_bayar = (float)$pending_bg['jumlah_pembayaran'];
                                        } else {
                                            // Jika ada BG belum cair, tawarkan sisa tagihan non-BG terlebih dahulu
                                            $bg_pending_total = (float)($faktur['total_bg_pending'] ?? 0);
                                            $sisa_non_bg = max(0, (float)$faktur['sisa_tagihan'] - $bg_pending_total);
                                            $raw_bayar = ($sisa_non_bg > 0) ? $sisa_non_bg : (float)$faktur['sisa_tagihan'];
                                        }
                                        $display_bayar = number_format($raw_bayar, 0, ',', '.');
                                        ?>
                                        <input type="text" name="jumlah_pembayaran" id="jumlah_pembayaran" class="form-control input-currency" required
                                               value="<?= htmlspecialchars($display_bayar) ?>">
                                        <small class="text-muted">
                                            Maksimal Rp <?= number_format($max_bayar, 0, ',', '.') ?>
                                            <?php if (!$is_bg_cair_mode && !empty($faktur['total_bg_pending'])): ?>
                                                <span class="text-warning ml-1">(Terdapat BG belum cair: Rp <?= number_format((float)$faktur['total_bg_pending'], 0, ',', '.') ?>)</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="form-group" <?= $is_bg_cair_mode ? 'style="display:none;"' : '' ?>>
                                        <label>Jumlah Diskon</label>
                                        <input type="text" name="jumlah_diskon" id="jumlah_diskon" class="form-control input-currency" value="<?= $is_draft_mode ? number_format((float)($draft_payment['jumlah_diskon'] ?? 0), 0, ',', '.') : '0' ?>">
                                    </div>
                                    <div class="form-group" id="tanggal_bg_cair_group" style="<?= (!$is_bg_cair_mode && $default_metode !== 'bg') ? 'display:none' : '' ?>">
                                        <label>Tanggal BG Cair <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_bg_cair" class="form-control"
                                               value="<?= $is_draft_mode ? htmlspecialchars($draft_payment['tanggal_bg_cair'] ?? date('Y-m-d')) : ($is_bg_cair_mode ? htmlspecialchars($pending_bg['tanggal_bg_cair']) : date('Y-m-d')) ?>">
                                        <small class="text-muted">Tanggal estimasi atau tanggal realisasi pencairan Bilyet Giro / BG.</small>
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
                                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Nomor referensi / catatan pembayaran"><?= $is_draft_mode ? htmlspecialchars($draft_payment['keterangan'] ?? '') : ($is_validasi_kasir_mode ? htmlspecialchars($validasi_kasir['keterangan'] ?? '') : ($is_bg_cair_mode ? htmlspecialchars($pending_bg['keterangan'] ?? '') : '')) ?></textarea>
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <?php if ($is_draft_mode): ?>
                                        <a href="<?= base_url('keuangan/pembayaran/bayar/' . $faktur['id_faktur']) ?>" class="btn btn-secondary mr-2">
                                            <i class="fas fa-times mr-1"></i> Batal
                                        </a>
                                        <button type="submit" class="btn btn-warning font-weight-bold">
                                            <i class="fas fa-check-circle mr-1"></i> Simpan & Posting
                                        </button>
                                    <?php elseif ($is_bg_cair_mode): ?>
                                        <a href="<?= base_url('keuangan/pembayaran/bayar/' . $faktur['id_faktur']) ?>" class="btn btn-secondary mr-2">
                                            <i class="fas fa-times mr-1"></i> Batal
                                        </a>
                                        <button type="submit" class="btn btn-warning font-weight-bold">
                                            <i class="fas fa-check mr-1"></i> BG Sudah Cair
                                        </button>
                                    <?php elseif ($is_validasi_kasir_mode): ?>
                                        <button type="submit" class="btn btn-info font-weight-bold">
                                            <i class="fas fa-check mr-1"></i> Simpan Validasi
                                        </button>
                                    <?php elseif (isset($is_lunas) && $is_lunas): ?>
                                        <button type="button" class="btn btn-secondary" disabled>
                                            <i class="fas fa-ban mr-1"></i> Sudah Lunas
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-success font-weight-bold">
                                            <i class="fas fa-save mr-1"></i> Simpan Pembayaran
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <!-- Card Jurnal removed as requested -->
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

<!-- Modal Detail Faktur -->
<div class="modal fade" id="modalDetailFaktur" tabindex="-1" role="dialog" aria-labelledby="modalDetailFakturLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalDetailFakturLabel">
                    <i class="fas fa-file-invoice mr-2"></i>Detail Faktur Penjualan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="35%" class="text-muted">No. Faktur</td>
                                <td><strong id="modalNoFaktur">-</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal</td>
                                <td id="modalTanggalFaktur">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="35%" class="text-muted">Customer</td>
                                <td id="modalCustomerName">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Rute</td>
                                <td id="modalCustomerRute">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama Barang</th>
                                <th>Lot / Exp</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Harga Satuan</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="modalDetailFakturTableBody">
                            <tr>
                                <td colspan="5" class="text-center text-muted">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // AJAX Faktur Detail Modal
    var btnLihat = document.getElementById('btnLihatDetailFaktur');
    if (btnLihat) {
        btnLihat.addEventListener('click', function() {
            var idFaktur = this.getAttribute('data-id-faktur');
            if (!idFaktur) return;

            // Clear table
            $('#modalDetailFakturTableBody').html('<tr><td colspan="5" class="text-center text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Memuat data...</td></tr>');
            
            // Show modal
            $('#modalDetailFaktur').modal('show');

            $.ajax({
                url: '<?= base_url("sales_order/admin_sc/get_faktur_detail_info_json") ?>',
                type: 'GET',
                dataType: 'JSON',
                data: { id_faktur: idFaktur },
                success: function(response) {
                    if (response && response.status) {
                        var faktur = response.faktur;
                        var items = response.items;

                        $('#modalNoFaktur').text(faktur.no_faktur);
                        var tgl = faktur.tanggal_faktur ? new Date(faktur.tanggal_faktur).toLocaleDateString('id-ID') : '-';
                        $('#modalTanggalFaktur').text(tgl);
                        $('#modalCustomerName').text(faktur.customer_name);
                        $('#modalCustomerRute').text(faktur.kd_rute || '-');

                        var html = '';
                        var grandTotal = 0;
                        if (items && items.length > 0) {
                            items.forEach(function(item) {
                                var sub = parseFloat(item.total_harga || item.subtotal_after_disc || 0);
                                grandTotal += sub;
                                
                                var lot = item.no_lot ? 'Lot: ' + item.no_lot : '';
                                var exp = item.expired_date ? 'Exp: ' + item.expired_date : '';
                                var lotExp = [lot, exp].filter(Boolean).join('<br>');

                                html += '<tr>' +
                                    '<td><strong>' + item.nama_barang + '</strong><br><small class="text-muted">' + item.kd_barang + '</small></td>' +
                                    '<td><small>' + (lotExp || '-') + '</small></td>' +
                                    '<td class="text-right">' + parseFloat(item.qty).toLocaleString('id-ID') + ' ' + (item.satuan || 'pcs') + '</td>' +
                                    '<td class="text-right">Rp ' + parseFloat(item.hrg_satuan || 0).toLocaleString('id-ID') + '</td>' +
                                    '<td class="text-right">Rp ' + sub.toLocaleString('id-ID') + '</td>' +
                                    '</tr>';
                            });
                            
                            // Add total row
                            html += '<tr class="font-weight-bold bg-light">' +
                                '<td colspan="4" class="text-right">Total Faktur:</td>' +
                                '<td class="text-right text-success">Rp ' + grandTotal.toLocaleString('id-ID') + '</td>' +
                                '</tr>';
                        } else {
                            html = '<tr><td colspan="5" class="text-center text-muted">Tidak ada detail item.</td></tr>';
                        }
                        $('#modalDetailFakturTableBody').html(html);
                    } else {
                        $('#modalDetailFakturTableBody').html('<tr><td colspan="5" class="text-center text-danger">' + (response.message || 'Gagal memuat data.') + '</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#modalDetailFakturTableBody').html('<tr><td colspan="5" class="text-center text-danger">Terjadi kesalahan koneksi: ' + error + '</td></tr>');
                }
            });
        });
    }

    var checkBg = document.getElementById('check_is_bg');
    var metode = document.getElementById('metode_pembayaran');
    var bgGroup = document.getElementById('tanggal_bg_cair_group');
    var returGroup = document.getElementById('saldo_retur_group');
    var jumlahInput = document.querySelector('input[name="jumlah_pembayaran"]');
    var sisaTagihan = <?= (float)$faktur['sisa_tagihan'] ?>;
    var saldoRetur = <?= (float)$saldo_retur ?>;

    if (!metode) return;

    var bgDate = bgGroup ? bgGroup.querySelector('input[name="tanggal_bg_cair"]') : null;
    var bgExtraGroup = document.getElementById('no_bg_nama_bank_group');

    function formatRupiahInput(val) {
        if (val === null || val === undefined) return '';
        var clean = val.toString().replace(/[^0-9,]/g, '');
        var parts = clean.split(',');
        var integerPart = parts[0].replace(/^0+(?=\d)/, '');
        if (integerPart === '') integerPart = parts[0] === '0' ? '0' : '';
        var decimalPart = parts.length > 1 ? ',' + parts[1].substring(0, 2) : '';
        var formattedInt = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        if (formattedInt === '' && decimalPart !== '') formattedInt = '0';
        return formattedInt + decimalPart;
    }

    function parseRupiahNumber(val) {
        if (!val) return 0;
        var str = val.toString().replace(/\./g, '').replace(',', '.');
        var num = parseFloat(str);
        return isNaN(num) ? 0 : num;
    }

    function bindCurrencyInput(el) {
        if (!el) return;
        el.addEventListener('input', function() {
            var cursorPosition = this.selectionStart;
            var originalLength = this.value.length;
            var formatted = formatRupiahInput(this.value);
            this.value = formatted;
            var newLength = formatted.length;
            var newPos = Math.max(0, cursorPosition + (newLength - originalLength));
            this.setSelectionRange(newPos, newPos);
            hitungJurnal();
        });
    }

    function handleMetodeChange() {
        var val = metode.value;
        var isBgChecked = checkBg ? checkBg.checked : false;
        var isBg = (val === 'bg' || isBgChecked || <?= $is_bg_cair_mode ? 'true' : 'false' ?>);
        
        // Toggle BG Group
        if (bgGroup) {
            bgGroup.style.display = isBg ? '' : 'none';
            if (bgDate) bgDate.required = isBg;
        }

        // Toggle No BG & Nama Bank Extra Group
        if (bgExtraGroup) {
            bgExtraGroup.style.display = isBg ? '' : 'none';
        }

        // Toggle Retur Group
        if (returGroup) {
            var isRetur = (val === 'Q Hutang Non Dagang (Retur Penjualan yg blm dipot)' || val === 'Q Hutang Non Dagang' || val.toLowerCase().indexOf('retur') !== -1);
            returGroup.style.display = isRetur ? '' : 'none';
            
            if (isRetur && jumlahInput) {
                var maxLimit = Math.min(sisaTagihan, saldoRetur);
                var currentVal = parseRupiahNumber(jumlahInput.value);
                if (currentVal > maxLimit || currentVal == sisaTagihan) {
                    jumlahInput.value = formatRupiahInput(maxLimit);
                }
            }
        }
        
        hitungJurnal();
    }

    function hitungJurnal() {
        var debitAkun = '-';
        var refPrefix = 'MR';
        var amount = 0;

        if (<?= $is_validasi_kasir_mode ? 'true' : 'false' ?>) {
            debitAkun = 'Q Kas';
            amount = parseRupiahNumber(jumlahInput ? jumlahInput.value : 0);
            refPrefix = 'KM';
        } else if (<?= $is_bg_cair_mode ? 'true' : 'false' ?>) {
            debitAkun = '<?= htmlspecialchars($pending_bg["metode_pembayaran"] ?? "") ?>';
            amount = <?= (float)($pending_bg["jumlah_pembayaran"] ?? 0) ?>;
            if (debitAkun.toLowerCase() === 'q kas' || debitAkun.toLowerCase() === 'a kas') {
                refPrefix = 'KM';
            }
        } else {
            if (!metode || !jumlahInput) return;
            var val = metode.value;
            amount = parseRupiahNumber(jumlahInput.value);
            
            debitAkun = val;
            if (val.toLowerCase() === 'q kas' || val.toLowerCase() === 'a kas') {
                refPrefix = 'KM';
            } else if (val === 'Q Hutang Non Dagang' || val === 'Q Hutang Non Dagang (Retur Penjualan yg blm dipot)') {
                debitAkun = 'Q Hutang Non Dagang (Retur Penjualan yg blm dipot)';
            } else if (val.toLowerCase() === 'bg') {
                debitAkun = 'BG / Cek';
            }
        }

        var formattedAmount = 'Rp ' + amount.toLocaleString('id-ID', { minimumFractionDigits: 0 });
        
        var refEl = document.getElementById('jurnalRef');
        if (refEl) refEl.textContent = refPrefix + ' (Auto Generate)';
        
        var debitAkunEl = document.getElementById('jurnalDebitAkun');
        if (debitAkunEl) debitAkunEl.textContent = debitAkun;
        
        var debitNilaiEl = document.getElementById('jurnalDebitNilai');
        if (debitNilaiEl) debitNilaiEl.textContent = formattedAmount;
        
        var kreditNilaiEl = document.getElementById('jurnalKreditNilai');
        if (kreditNilaiEl) kreditNilaiEl.textContent = formattedAmount;
    }

    var diskonInput = document.getElementById('jumlah_diskon');

    bindCurrencyInput(jumlahInput);
    bindCurrencyInput(diskonInput);

    metode.addEventListener('change', function() {
        var val = (this.value || '').toLowerCase();
        if (val === 'bg') {
            if (checkBg) checkBg.checked = true;
        } else if (!<?= $is_bg_cair_mode ? 'true' : 'false' ?>) {
            if (checkBg && checkBg.checked) {
                checkBg.checked = false;
            }
        }
        handleMetodeChange();
    });
    if (checkBg) {
        checkBg.addEventListener('change', handleMetodeChange);
    }

    var form = document.getElementById('formPembayaran');
    if (form) {
        form.addEventListener('submit', function(e) {
            var bayar = parseRupiahNumber(jumlahInput ? jumlahInput.value : 0);
            var diskon = diskonInput ? parseRupiahNumber(diskonInput.value) : 0;
            var total = bayar + diskon;
            var maxLimit = sisaTagihan + (<?= $is_validasi_kasir_mode ? (float)($validasi_kasir['jumlah_pembayaran'] ?? 0) : ($is_bg_cair_mode ? (float)($pending_bg['jumlah_pembayaran'] ?? 0) : 0) ?>);

            if (total > maxLimit + 1) { // Allowing tiny floating point buffer similar to server side
                e.preventDefault();
                alert('❌ Jumlah Pembayaran + Diskon (Rp ' + total.toLocaleString('id-ID') + ') tidak boleh melebihi Sisa Tagihan (Rp ' + maxLimit.toLocaleString('id-ID') + ')!');
                return false;
            }
        });
    }
    
    handleMetodeChange();
    hitungJurnal();
});
</script>
