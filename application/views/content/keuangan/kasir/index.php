<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-cash-register mr-2 text-success"></i> Kasir</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">Kasir</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <!-- ============ BARIS ATAS: SALDO + RINGKASAN (KOMPAK & PROPORSIOANL) ============ -->
                <div class="row mb-3">
                    <?php
                    // Kasir TIDAK bisa mengatur saldo. Hanya admin keuangan / kiukeu / superadmin yang bisa.
                    $jobdeskUser = strtoupper((string)$this->session->userdata('jobdesk'));
                    $isKeuangan  = in_array($jobdeskUser, ['ADMINKEU','ADMINKEUTC','KIUKEU','ADMIN'], true) 
                                   || $this->session->userdata('username') === 'admin';
                    ?>

                    <!-- Saldo Kasir -->
                    <div class="col-lg-4 col-md-4 col-12 mb-2">
                        <div class="info-box shadow-sm mb-0 py-2">
                            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-coins"></i></span>
                            <div class="info-box-content py-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="info-box-text font-weight-bold text-success">Saldo Kasir</span>
                                    <?php if ($isKeuangan): ?>
                                        <button class="btn btn-xs btn-outline-secondary" title="Atur Akun Saldo Kasir" onclick="$('#modalSetSaldo').modal('show')">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php if ($saldo_kasir): ?>
                                    <span class="info-box-number text-dark h4 font-weight-bold mb-0" id="saldoAngka">
                                        Rp <?= number_format($saldo_aktual, 0, ',', '.') ?>
                                    </span>
                                    <small class="text-muted truncate d-block" title="<?= htmlspecialchars($saldo_kasir->kode_akun . ' - ' . $saldo_kasir->nama_akun) ?>">
                                        <i class="fas fa-university mr-1"></i><?= htmlspecialchars($saldo_kasir->kode_akun . ' - ' . $saldo_kasir->nama_akun) ?>
                                    </small>
                                <?php else: ?>
                                    <span class="info-box-number text-danger h6 mb-0">Belum Diatur</span>
                                    <?php if ($isKeuangan): ?>
                                        <small><a href="javascript:void(0)" onclick="$('#modalSetSaldo').modal('show')">Atur Akun Saldo</a></small>
                                    <?php else: ?>
                                        <small class="text-muted">Hubungi Bagian Keuangan</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Total Kas Masuk Bulan Ini -->
                    <div class="col-lg-4 col-md-4 col-12 mb-2">
                        <div class="info-box shadow-sm mb-0 py-2">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-arrow-down"></i></span>
                            <div class="info-box-content py-0">
                                <span class="info-box-text font-weight-bold text-info">Kas Masuk Bulan Ini</span>
                                <span class="info-box-number text-dark h4 font-weight-bold mb-0">
                                    Rp <?= number_format($total_masuk, 0, ',', '.') ?>
                                </span>
                                <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i>Periode: <?= $bulan ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Total Kas Keluar Bulan Ini -->
                    <div class="col-lg-4 col-md-4 col-12 mb-2">
                        <div class="info-box shadow-sm mb-0 py-2">
                            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-arrow-up"></i></span>
                            <div class="info-box-content py-0">
                                <span class="info-box-text font-weight-bold text-danger">Kas Keluar Bulan Ini</span>
                                <span class="info-box-number text-dark h4 font-weight-bold mb-0">
                                    Rp <?= number_format($total_keluar, 0, ',', '.') ?>
                                </span>
                                <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i>Periode: <?= $bulan ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ BARIS AKSI + FILTER ============ -->
                <div class="card">
                    <div class="card-header py-2">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h6 class="m-0 font-weight-bold">
                                    <i class="fas fa-list mr-1"></i> Daftar Transaksi
                                </h6>
                            </div>
                            <div class="col-md-8 text-right">
                                <!-- Filter Bulan -->
                                <div class="d-inline-flex align-items-center mr-2">
                                    <label class="mr-1 mb-0 small">Bulan:</label>
                                    <input type="month" id="filterBulan" class="form-control form-control-sm" value="<?= $bulan ?>" style="width:150px;">
                                </div>
                                <!-- Filter Jenis -->
                                <select id="filterJenis" class="form-control form-control-sm d-inline-block mr-2" style="width:150px;">
                                    <option value="">Semua</option>
                                    <option value="kas_masuk" <?= ($filter_jenis ?? '') === 'kas_masuk' ? 'selected' : '' ?>>Kas Masuk</option>
                                    <option value="kas_keluar" <?= ($filter_jenis ?? '') === 'kas_keluar' ? 'selected' : '' ?>>Kas Keluar</option>
                                    <option value="penyelesaian_um" <?= ($filter_jenis ?? '') === 'penyelesaian_um' ? 'selected' : '' ?>>Penyelesaian UM</option>
                                </select>
                                <button class="btn btn-sm btn-info mr-1" onclick="loadTransaksi()">
                                    <i class="fas fa-search"></i>
                                </button>
                                <!-- Tombol Input Baru -->
                                <button class="btn btn-sm btn-success mr-1" onclick="openModal('kas_masuk')">
                                    <i class="fas fa-plus mr-1"></i> Kas Masuk
                                </button>
                                <button class="btn btn-sm btn-danger mr-1" onclick="openModal('kas_keluar')">
                                    <i class="fas fa-minus mr-1"></i> Kas Keluar
                                </button>
                                <!-- Tombol Laporan Mutasi -->
                                <a href="<?= site_url('keuangan/kasir/report_mutasi?tanggal_awal=' . date('Y-m-01') . '&tanggal_akhir=' . date('Y-m-d')) ?>"
                                   class="btn btn-sm btn-warning" title="Laporan Mutasi Kas Harian">
                                    <i class="fas fa-book-open mr-1"></i> Laporan Mutasi
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0" id="tabelTransaksi" style="font-size:13px;">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center" style="width:40px;">No</th>
                                        <th>No Transaksi</th>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Pilihan</th>
                                        <th class="text-right">Nominal</th>
                                        <th>Keterangan</th>
                                        <th>Input Oleh</th>
                                        <th class="text-center" style="width:130px; white-space:nowrap;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTransaksi">
                                    <?php $no = 1; foreach ($transaksi as $t): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><small><?= htmlspecialchars($t['no_transaksi']) ?></small></td>
                                        <td><?= $t['tanggal_fmt'] ?? date('d/m/Y', strtotime($t['tanggal'])) ?></td>
                                        <td class="text-center">
                                            <?php if ($t['jenis_transaksi'] === 'kas_masuk'): ?>
                                                <span class="badge badge-success"><i class="fas fa-arrow-down mr-1"></i>Kas Masuk</span>
                                            <?php elseif ($t['jenis_transaksi'] === 'kas_keluar'): ?>
                                                <span class="badge badge-danger"><i class="fas fa-arrow-up mr-1"></i>Kas Keluar</span>
                                                <?php if ($t['is_settled'] == 1): ?>
                                                    <br><span class="badge badge-secondary mt-1" style="font-size:9px;"><i class="fas fa-check mr-1"></i>Sudah Diselesaikan</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><i class="fas fa-sync mr-1"></i>Peny. UM</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($t['pilihan']) ?></td>
                                        <td class="text-right font-weight-bold">
                                            <?php if ($t['jenis_transaksi'] === 'penyelesaian_um'): ?>
                                                <span class="text-danger d-block">Keluar: Rp <?= number_format($t['nominal'], 0, ',', '.') ?></span>
                                                <span class="text-success d-block">Masuk: Rp <?= number_format($t['nominal_kembali'], 0, ',', '.') ?></span>
                                            <?php else: ?>
                                                <span class="<?= $t['jenis_transaksi'] === 'kas_masuk' ? 'text-success' : 'text-danger' ?>">
                                                    Rp <?= number_format($t['nominal'], 0, ',', '.') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($t['keterangan'] ?? '-') ?></td>
                                        <td><small><?= htmlspecialchars($t['nama_user'] ?? '-') ?></small></td>
                                        <td class="text-center align-middle" style="white-space:nowrap;">
                                            <div class="d-inline-flex justify-content-center align-items-center">
                                                <?php if ($t['jenis_transaksi'] === 'kas_keluar' && $t['is_settled'] == 0): ?>
                                                    <button class="btn btn-xs btn-success mr-1" 
                                                            onclick="openModalKasMasukRef(<?= $t['id'] ?>, '<?= htmlspecialchars(addslashes($t['no_transaksi'])) ?>', '<?= htmlspecialchars(addslashes($t['pilihan'])) ?>', '<?= htmlspecialchars(addslashes($t['keterangan'] ?? '')) ?>', <?= $t['nominal'] ?>)" 
                                                            title="Input Kas Masuk dari Kas Keluar ini">
                                                        <i class="fas fa-plus-circle"></i> Kas Masuk
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-xs btn-danger" onclick="hapusTransaksi(<?= $t['id'] ?>)" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($transaksi)): ?>
                                    <tr><td colspan="9" class="text-center text-muted py-3">Belum ada transaksi untuk bulan ini.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>

<!-- ============ MODAL INPUT TRANSAKSI (HORIZONTAL & WIDE) ============ -->
<div class="modal fade" id="modalTransaksi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2" id="modalHeader">
                <h5 class="modal-title font-weight-bold" id="modalTitle">Input Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body py-3">
                <input type="hidden" id="inputJenis">
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-secondary">Tanggal Transaksi *</label>
                            <input type="date" id="inputTanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-secondary" id="lblNominal">Nominal Transaksi (Rp) *</label>
                            <input type="text" id="inputNominal" class="form-control form-control-sm text-right font-weight-bold" style="font-size:1.1rem;" placeholder="0" oninput="formatNominal(this)">
                        </div>
                    </div>
                    <div class="col-md-4" id="colNominalKembali" style="display:none;">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-secondary">Sisa / Kembalian (Rp) *</label>
                            <input type="text" id="inputNominalKembali" class="form-control form-control-sm text-right font-weight-bold" style="font-size:1.1rem; color:green;" placeholder="0" oninput="formatNominal(this)">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-secondary">Pilihan Kategori *</label>
                            <div class="input-group input-group-sm">
                                <select id="inputPilihan" class="form-control form-control-sm select2-pilihan">
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($pilihan_list as $p): ?>
                                        <option value="<?= htmlspecialchars($p['nama_pilihan']) ?>"><?= htmlspecialchars($p['nama_pilihan']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" title="Tambah pilihan kategori baru" onclick="$('#modalPilihan').modal('show')">
                                        <i class="fas fa-plus mr-1"></i> Kategori Baru
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-secondary">Keterangan / Catatan Transaksi</label>
                            <textarea id="inputKeterangan" class="form-control form-control-sm" rows="3" placeholder="Masukkan rincian keterangan transaksi kas (opsional)..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm px-4 font-weight-bold" onclick="simpanTransaksi()" id="btnSimpan">
                    <i class="fas fa-save mr-1"></i> Simpan Transaksi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============ MODAL TAMBAH PILIHAN ============ -->
<div class="modal fade" id="modalPilihan" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Tambah Pilihan Baru</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-0">
                    <label class="small font-weight-bold">Nama Pilihan *</label>
                    <input type="text" id="namaPilihanBaru" class="form-control form-control-sm" placeholder="Contoh: Penj Toko (A)">
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm" onclick="tambahPilihan()">
                    <i class="fas fa-plus mr-1"></i> Tambah
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============ MODAL KAS MASUK DARI KAS KELUAR ============ -->
<div class="modal fade" id="modalKasMasukRef" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header py-2 bg-success text-white">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-plus-circle mr-1"></i> Input Kas Masuk (Pengembalian Kas Keluar)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body py-3">
                <input type="hidden" id="kmRefId">
                <input type="hidden" id="kmRefNominalTotal">
                <!-- Info transaksi asal -->
                <div class="alert alert-info py-2 mb-3" style="font-size:12px;">
                    <strong><i class="fas fa-info-circle mr-1"></i> Transaksi Kas Keluar Asal:</strong><br>
                    <span class="text-monospace font-weight-bold" id="kmRefInfoNoTrx"></span><br>
                    <span class="text-dark" id="kmRefInfoUraian"></span><br>
                    <span class="text-muted small" id="kmRefInfoKet"></span>
                    <hr class="my-1">
                    <strong>Total Kas Keluar Awal: <span class="text-danger" id="kmRefInfoNominal"></span></strong>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-secondary">Tanggal Kas Masuk *</label>
                            <input type="date" id="kmRefTanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-secondary">Nominal Kas Masuk (Rp) *</label>
                            <input type="text" id="kmRefNominal" class="form-control form-control-sm text-right font-weight-bold"
                                   style="font-size:1.1rem; color:green;" placeholder="0"
                                   oninput="formatNominal(this)">
                            <small class="text-muted">Nominal uang kas yang masuk/diterima kembali</small>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-secondary">Keterangan / Catatan (Opsional)</label>
                    <input type="text" id="kmRefKeterangan" class="form-control form-control-sm"
                           placeholder="Contoh: Terima sisa UM / Pengembalian BBM...">
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm px-4 font-weight-bold" onclick="simpanKasMasukRef()" id="btnSimpanKMRef">
                    <i class="fas fa-save mr-1"></i> Simpan Kas Masuk
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============ MODAL SET SALDO KASIR (hanya keuangan) ============ -->
<?php if ($isKeuangan): ?>
<div class="modal fade" id="modalSetSaldo" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-cog mr-1"></i> Atur Saldo Kasir</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Pilih akun jurnal KAS yang menjadi acuan saldo kasir.</p>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold">Akun Kas *</label>
                    <select id="selectAkunSaldo" class="form-control form-control-sm">
                        <option value="">-- Pilih Akun --</option>
                        <?php foreach ($akun_kas as $ak): ?>
                            <option value="<?= $ak->id_akun ?>"
                                <?= ($saldo_kasir && $saldo_kasir->id_akun == $ak->id_akun) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ak->kode_akun . ' - ' . $ak->nama_akun) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm" onclick="setSaldo()">
                    <i class="fas fa-check mr-1"></i> Terapkan
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// ============================================================
// Buka modal input transaksi
// ============================================================
function openModal(jenis) {
    $('#inputJenis').val(jenis);
    $('#colNominalKembali').hide();
    $('#lblNominal').text('Nominal Transaksi (Rp) *');
    $('#inputNominalKembali').val('');

    if (jenis === 'kas_masuk') {
        $('#modalHeader').removeClass('bg-danger bg-warning').addClass('bg-info');
        $('#modalTitle').html('<i class="fas fa-arrow-down mr-1"></i> Input Kas Masuk');
        $('#btnSimpan').removeClass('btn-danger btn-warning').addClass('btn-info');
    } else if (jenis === 'kas_keluar') {
        $('#modalHeader').removeClass('bg-info bg-warning').addClass('bg-danger');
        $('#modalTitle').html('<i class="fas fa-arrow-up mr-1"></i> Input Kas Keluar');
        $('#btnSimpan').removeClass('btn-info btn-warning').addClass('btn-danger');
    }
    $('#inputNominal').val('');
    $('#inputKeterangan').val('');
    $('#modalTransaksi').modal('show');
}

// ============================================================
// Format angka rupiah di input
// ============================================================
function formatNominal(el) {
    var raw = el.value.replace(/\D/g, '');
    el.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
}

// ============================================================
// Simpan Transaksi
// ============================================================
function simpanTransaksi() {
    var jenis     = $('#inputJenis').val();
    var pilihan   = $('#inputPilihan').val();
    var nominalRaw = $('#inputNominal').val().replace(/\./g, '').replace(',', '.');
    var nominal   = parseFloat(nominalRaw);
    var nominalKembaliRaw = $('#inputNominalKembali').val().replace(/\./g, '').replace(',', '.');
    var keterangan = $('#inputKeterangan').val();
    var tanggal   = $('#inputTanggal').val();

    if (!pilihan) { Swal.fire('Perhatian', 'Pilih kategori transaksi terlebih dahulu!', 'warning'); return; }
    if (!nominal || nominal <= 0) { Swal.fire('Perhatian', 'Nominal harus lebih dari 0!', 'warning'); return; }
    if (jenis === 'penyelesaian_um' && (!nominalKembaliRaw || parseFloat(nominalKembaliRaw) < 0)) {
        Swal.fire('Perhatian', 'Sisa kembalian tidak valid!', 'warning'); return;
    }

    $('#btnSimpan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

    $.post("<?= base_url('keuangan/kasir/simpan_transaksi') ?>", {
        jenis_transaksi: jenis,
        pilihan: pilihan,
        nominal: nominalRaw,
        nominal_kembali: nominalKembaliRaw,
        keterangan: keterangan,
        tanggal: tanggal
    }, function(res) {
        $('#btnSimpan').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
        if (res.status === 'success') {
            $('#modalTransaksi').modal('hide');
            Swal.fire({icon:'success', title:'Berhasil!', text: res.message, timer:1500, showConfirmButton:false})
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }, 'json').fail(function() {
        $('#btnSimpan').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan');
        Swal.fire('Error', 'Koneksi ke server gagal.', 'error');
    });
}

// ============================================================
// Hapus Transaksi
// ============================================================
function hapusTransaksi(id) {
    Swal.fire({
        title: 'Hapus Transaksi?',
        text: 'Data transaksi akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post("<?= base_url('keuangan/kasir/hapus_transaksi') ?>", {id: id}, function(res) {
                if (res.status === 'success') {
                    Swal.fire({icon:'success', title:'Terhapus!', timer:1200, showConfirmButton:false})
                        .then(() => location.reload());
                } else {
                    Swal.fire('Gagal', 'Gagal menghapus transaksi.', 'error');
                }
            }, 'json');
        }
    });
}

// ============================================================
// Tambah Pilihan Baru
// ============================================================
function tambahPilihan() {
    var nama = $('#namaPilihanBaru').val().trim();
    if (!nama) { Swal.fire('Perhatian', 'Nama pilihan tidak boleh kosong!', 'warning'); return; }

    $.post("<?= base_url('keuangan/kasir/tambah_pilihan') ?>", {nama_pilihan: nama}, function(res) {
        if (res.status === 'success') {
            // Tambahkan option baru ke select
            $('#inputPilihan').append('<option value="'+res.nama_pilihan+'" selected>'+res.nama_pilihan+'</option>');
            $('#modalPilihan').modal('hide');
            $('#namaPilihanBaru').val('');
            Swal.fire({icon:'success', title:'Pilihan ditambahkan!', timer:1200, showConfirmButton:false});
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }, 'json');
}

// ============================================================
// Set Saldo Kasir
// ============================================================
function setSaldo() {
    var id_akun = $('#selectAkunSaldo').val();
    if (!id_akun) { Swal.fire('Perhatian', 'Pilih akun kas terlebih dahulu!', 'warning'); return; }

    $.post("<?= base_url('keuangan/kasir/set_saldo') ?>", {id_akun: id_akun}, function(res) {
        if (res.status === 'success') {
            $('#modalSetSaldo').modal('hide');
            Swal.fire({icon:'success', title:'Berhasil!', text: 'Akun saldo kasir diatur ke: ' + res.nama_akun, timer:1500, showConfirmButton:false})
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }, 'json');
}

// ============================================================
// Load transaksi dengan filter
// ============================================================
function loadTransaksi() {
    var bulan = $('#filterBulan').val();
    var jenis = $('#filterJenis').val();
    if (!bulan) return;

    window.location.href = "<?= base_url('keuangan/kasir') ?>?bulan=" + bulan + (jenis ? '&jenis=' + jenis : '');
}

// ============================================================
// Modal Kas Masuk (Pengembalian dari Kas Keluar)
// ============================================================
function openModalKasMasukRef(id, noTrx, uraian, keterangan, nominal) {
    $('#kmRefId').val(id);
    $('#kmRefNominalTotal').val(nominal);
    $('#kmRefInfoNoTrx').text(noTrx);
    $('#kmRefInfoUraian').text(uraian);
    $('#kmRefInfoKet').text(keterangan || '');
    $('#kmRefInfoNominal').text('Rp ' + parseInt(nominal).toLocaleString('id-ID'));
    $('#kmRefNominal').val('');
    $('#kmRefKeterangan').val('');
    $('#kmRefTanggal').val('<?= date('Y-m-d') ?>');
    $('#modalKasMasukRef').modal('show');
}

function simpanKasMasukRef() {
    var idRef       = $('#kmRefId').val();
    var nominalRaw  = $('#kmRefNominal').val().replace(/\./g, '').replace(',', '.') || '0';
    var nominal     = parseFloat(nominalRaw) || 0;
    var tanggal     = $('#kmRefTanggal').val();
    var keterangan  = $('#kmRefKeterangan').val();

    if (!tanggal) { Swal.fire('Perhatian', 'Tanggal Kas Masuk harus diisi!', 'warning'); return; }
    if (nominal <= 0) {
        Swal.fire('Perhatian', 'Nominal Kas Masuk harus lebih besar dari 0!', 'warning');
        return;
    }

    $('#btnSimpanKMRef').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

    $.post("<?= base_url('keuangan/kasir/selesaikan_um') ?>", {
        id_ref:     idRef,
        nominal:    nominalRaw,
        tanggal:    tanggal,
        keterangan: keterangan
    }, function(res) {
        $('#btnSimpanKMRef').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Kas Masuk');
        if (res.status === 'success') {
            $('#modalKasMasukRef').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Kas Masuk Berhasil Disimpan!',
                html: 'No Transaksi: <b>' + res.no_transaksi + '</b>',
                timer: 2000,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }, 'json');
}

$(document).ready(function() {
    $('#filterBulan').on('change', function() { loadTransaksi(); });
    $('#filterJenis').on('change', function() { loadTransaksi(); });
});
</script>
