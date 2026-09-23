<!-- application/views/content/sales/customer_acak_list.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-dark">
                        <i class="fas fa-users text-primary mr-2"></i> Master Customer Acak (Pecah Faktur)
                    </h1>
                    <small class="text-muted">Master kontak person penerima turunan per kios untuk keperluan pemecahan Faktur Z</small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="<?= base_url('sales_order/pecah_faktur') ?>" class="btn btn-outline-secondary mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Pecah Faktur
                    </a>
                    <button type="button" class="btn btn-primary mr-2 shadow-sm font-weight-bold" data-toggle="modal" data-target="#modalTambahCustomerAcak">
                        <i class="fas fa-plus-circle mr-1"></i> Tambah Customer Acak
                    </button>
                    <a href="<?= base_url('sales_order/sync_customer_acak') ?>" class="btn btn-outline-success font-weight-bold" onclick="return confirm('Apakah Anda yakin ingin menyinkronkan data kontak person dari file cust acak.xlsx?');">
                        <i class="fas fa-sync-alt mr-1"></i> Sinkronisasi Excel
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Card Filter & Pencarian -->
            <div class="card card-outline card-primary shadow-sm mb-3">
                <div class="card-body p-3">
                    <form method="get" action="<?= base_url('sales_order/customer_acak') ?>" class="row align-items-center">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Filter Kios / Toko Induk</label>
                            <select name="nama_toko" class="form-control form-control-sm select2" onchange="this.form.submit()">
                                <option value="">-- Semua Kios / Toko (<?= count($unique_tokos) ?> Toko) --</option>
                                <?php foreach ($unique_tokos as $ut): ?>
                                    <option value="<?= htmlspecialchars($ut['nama_toko']) ?>" <?= $selected_toko === $ut['nama_toko'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ut['nama_toko']) ?> (<?= (int)$ut['total_kontak'] ?> kontak)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1">Pencarian Kontak / Kode / Kota</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="q" class="form-control" placeholder="Cari kode (misal AGRO01AC), kontak, kota..." value="<?= htmlspecialchars($search) ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                    <?php if (!empty($search) || !empty($selected_toko)): ?>
                                        <a href="<?= base_url('sales_order/customer_acak') ?>" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-md-right mt-2 mt-md-4">
                            <span class="badge badge-info p-2 font-weight-bold" style="font-size: 13px;">
                                Total: <?= number_format(count($customers_acak)) ?> Kontak Person
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data Customer Acak -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-striped mb-0 text-nowrap" id="tableCustomerAcak">
                            <thead class="thead-light">
                                <tr>
                                    <th width="40" class="text-center">No</th>
                                    <th width="120">Kode Acak</th>
                                    <th width="200">Kios / Toko Induk</th>
                                    <th>Kontak Person (Penerima)</th>
                                    <th>Alamat Lengkap</th>
                                    <th width="120">Kota</th>
                                    <th width="140">NIK</th>
                                    <th width="130">NPWP</th>
                                    <th width="90" class="text-center no-sort">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($customers_acak)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle mr-1"></i> Tidak ada data kontak person customer acak ditemukan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($customers_acak as $ca): ?>
                                        <tr>
                                            <td class="text-center align-middle text-muted"><?= $no++ ?></td>
                                            <td class="align-middle">
                                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-family: monospace; font-size: 12px;">
                                                    <?= htmlspecialchars($ca['kd_customer']) ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <strong class="text-dark"><?= htmlspecialchars($ca['nama_toko']) ?></strong>
                                                <?php if (!empty($ca['kd_customer_induk'])): ?>
                                                    <br><small class="text-primary font-weight-bold"><i class="fas fa-link mr-1"></i>Induk: <?= htmlspecialchars($ca['kd_customer_induk']) ?></small>
                                                <?php else: ?>
                                                    <br><small class="text-muted"><i class="fas fa-unlink mr-1"></i>Non-terpetakan</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle font-weight-bold text-dark">
                                                <?= htmlspecialchars($ca['kontak_person']) ?>
                                            </td>
                                            <td class="align-middle" style="white-space: normal; max-width: 250px;">
                                                <small class="text-muted"><?= htmlspecialchars($ca['alamat'] ?: '-') ?></small>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-light border"><?= htmlspecialchars($ca['kota'] ?: '-') ?></span>
                                            </td>
                                            <td class="align-middle">
                                                <small class="text-secondary"><?= htmlspecialchars($ca['nik'] ?: '-') ?></small>
                                            </td>
                                            <td class="align-middle">
                                                <small class="text-secondary"><?= htmlspecialchars($ca['npwp'] ?: '-') ?></small>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-default btn-sm btn-edit-ca" 
                                                            data-id="<?= $ca['id'] ?>"
                                                            data-kd="<?= htmlspecialchars($ca['kd_customer']) ?>"
                                                            data-toko="<?= htmlspecialchars($ca['nama_toko']) ?>"
                                                            data-induk="<?= htmlspecialchars($ca['kd_customer_induk'] ?? '') ?>"
                                                            data-kontak="<?= htmlspecialchars($ca['kontak_person']) ?>"
                                                            data-alamat="<?= htmlspecialchars($ca['alamat'] ?? '') ?>"
                                                            data-kota="<?= htmlspecialchars($ca['kota'] ?? '') ?>"
                                                            data-nik="<?= htmlspecialchars($ca['nik'] ?? '') ?>"
                                                            data-npwp="<?= htmlspecialchars($ca['npwp'] ?? '') ?>"
                                                            title="Edit Customer Acak">
                                                        <i class="fas fa-edit text-info"></i>
                                                    </button>
                                                    <a href="<?= base_url('sales_order/hapus_customer_acak/' . $ca['id']) ?>" 
                                                       class="btn btn-default btn-sm" 
                                                       onclick="return confirm('Apakah Anda yakin ingin menghapus customer acak <?= htmlspecialchars(addslashes($ca['kontak_person'])) ?> (<?= htmlspecialchars($ca['kd_customer']) ?>)?');"
                                                       title="Hapus Customer Acak">
                                                        <i class="fas fa-trash text-danger"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div> <!-- /.wrapper -->

<!-- MODAL TAMBAH CUSTOMER ACAK -->
<div class="modal fade" id="modalTambahCustomerAcak" tabindex="-1" role="dialog" aria-labelledby="modalTambahTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow">
            <form action="<?= base_url('sales_order/simpan_customer_acak') ?>" method="post" id="formTambahCustomerAcak">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalTambahTitle">
                        <i class="fas fa-user-plus mr-1"></i> Tambah Kontak Customer Acak Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border small text-muted mb-3">
                        <i class="fas fa-info-circle text-primary mr-1"></i>
                        Data ini digunakan saat pemecahan Faktur Z agar nama toko / kontak penerima pada faktur pecahan (Kode H) dapat terdistribusi secara acak dan tercatat rapi di laporan ekspor pajak.
                    </div>

                    <div class="row">
                        <!-- Toko / Kios Induk -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Kios / Toko Induk <span class="text-danger">*</span>
                            </label>
                            <select name="select_kios" id="tambah_select_kios" class="form-control form-control-sm" style="width: 100%;">
                                <option value="">-- Ketik untuk Cari Kios / Pilih Toko --</option>
                                <?php if (!empty($unique_tokos)): ?>
                                    <optgroup label="Toko Terdaftar di Customer Acak">
                                        <?php foreach ($unique_tokos as $ut): ?>
                                            <option value="<?= htmlspecialchars($ut['nama_toko']) ?>" 
                                                    data-kd="<?= htmlspecialchars($ut['kd_customer_induk'] ?? '') ?>">
                                                <?= htmlspecialchars($ut['nama_toko']) ?> <?= !empty($ut['kd_customer_induk']) ? '(' . htmlspecialchars($ut['kd_customer_induk']) . ')' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                                <option value="__NEW__">+ Ketik Nama Kios / Toko Baru Manual</option>
                            </select>
                        </div>

                        <!-- Nama Toko Final -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Nama Toko / Kios (Tercatat) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_toko" id="tambah_nama_toko" class="form-control form-control-sm" placeholder="Nama Kios / Toko" required>
                        </div>

                        <!-- Kode Induk -->
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Kode Customer Induk
                            </label>
                            <input type="text" name="kd_customer_induk" id="tambah_kd_customer_induk" class="form-control form-control-sm bg-light" placeholder="Misal: AGRO76">
                            <small class="text-muted">Kode induk kios di master customer</small>
                        </div>

                        <!-- Kode Customer Acak -->
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Kode Customer Acak <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="kd_customer" id="tambah_kd_customer" class="form-control font-weight-bold text-uppercase" placeholder="Misal: AGRO04AC" required style="letter-spacing: 1px;">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" id="btnAutoKodeTambah" title="Generate Otomatis Kode Acak">
                                        <i class="fas fa-magic"></i> Auto
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Format: 4 huruf awal + 2 digit urut + AC</small>
                        </div>

                        <!-- Kontak Person -->
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Kontak Person (Penerima) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kontak_person" id="tambah_kontak_person" class="form-control form-control-sm font-weight-bold" placeholder="Nama Orang / Kontak" required>
                        </div>

                        <!-- Kota -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">Kota</label>
                            <input type="text" name="kota" id="tambah_kota" class="form-control form-control-sm" placeholder="Contoh: Jember">
                        </div>

                        <!-- NIK -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">NIK (KTP)</label>
                            <input type="text" name="nik" id="tambah_nik" class="form-control form-control-sm" placeholder="16 digit NIK (jika ada)">
                        </div>

                        <!-- NPWP -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">NPWP</label>
                            <input type="text" name="npwp" id="tambah_npwp" class="form-control form-control-sm" value="000000000000000" placeholder="000000000000000">
                        </div>

                        <!-- Alamat -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" id="tambah_alamat" class="form-control form-control-sm" rows="2" placeholder="Alamat lengkap penerima..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Customer Acak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT CUSTOMER ACAK -->
<div class="modal fade" id="modalEditCustomerAcak" tabindex="-1" role="dialog" aria-labelledby="modalEditTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow">
            <form action="<?= base_url('sales_order/update_customer_acak') ?>" method="post" id="formEditCustomerAcak">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title font-weight-bold" id="modalEditTitle">
                        <i class="fas fa-edit mr-1"></i> Edit Kontak Customer Acak
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Nama Toko -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Nama Kios / Toko Induk <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_toko" id="edit_nama_toko" class="form-control form-control-sm" required>
                        </div>

                        <!-- Kode Induk -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Kode Customer Induk
                            </label>
                            <input type="text" name="kd_customer_induk" id="edit_kd_customer_induk" class="form-control form-control-sm">
                        </div>

                        <!-- Kode Acak -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Kode Customer Acak <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kd_customer" id="edit_kd_customer" class="form-control form-control-sm font-weight-bold text-uppercase" required>
                        </div>

                        <!-- Kontak Person -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">
                                Kontak Person (Penerima) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="kontak_person" id="edit_kontak_person" class="form-control form-control-sm font-weight-bold" required>
                        </div>

                        <!-- Kota -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">Kota</label>
                            <input type="text" name="kota" id="edit_kota" class="form-control form-control-sm">
                        </div>

                        <!-- NIK -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">NIK</label>
                            <input type="text" name="nik" id="edit_nik" class="form-control form-control-sm">
                        </div>

                        <!-- NPWP -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">NPWP</label>
                            <input type="text" name="npwp" id="edit_npwp" class="form-control form-control-sm">
                        </div>

                        <!-- Alamat -->
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-dark mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" id="edit_alamat" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-info btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if ($('.select2').length && typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    if (typeof $.fn.DataTable !== 'undefined' && !$.fn.DataTable.isDataTable('#tableCustomerAcak')) {
        $('#tableCustomerAcak').DataTable({
            "pageLength": 25,
            "language": {
                "search": "Cari di tabel:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Tidak ada data yang cocok",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Lanjut",
                    "previous": "Sebelum"
                }
            },
            "order": [[0, "asc"]]
        });
    }

    // Helper panggil generator kode acak
    function fetchNextKodeAcak(kdInduk, namaToko, targetInput) {
        if (!kdInduk && !namaToko) return;
        $.getJSON('<?= base_url("sales_order/get_next_kode_acak") ?>', {
            kd_induk: kdInduk,
            nama_toko: namaToko
        }, function(res) {
            if (res && res.status && res.suggested_code) {
                targetInput.val(res.suggested_code);
            }
        });
    }

    // Inisialisasi Select2 AJAX untuk pencarian Kios dari master tb_customer
    if (typeof $.fn.select2 !== 'undefined') {
        $('#tambah_select_kios').select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $('#modalTambahCustomerAcak'),
            placeholder: '-- Pilih / Cari Kios Master --',
            allowClear: true,
            ajax: {
                url: '<?= base_url("sales_order/ajax_search_kios") ?>',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                },
                cache: true
            }
        });
    }

    // Event saat Kios dipilih di Modal Tambah
    $('#tambah_select_kios').on('select2:select change', function(e) {
        var val = $(this).val();
        if (val === '__NEW__') {
            $('#tambah_nama_toko').val('').focus();
            $('#tambah_kd_customer_induk').val('');
            $('#tambah_kota').val('');
            $('#tambah_alamat').val('');
            return;
        }

        // Cek jika data berasal dari AJAX select2:select
        if (e.params && e.params.data) {
            var d = e.params.data;
            var namaToko = d.nama_kios || d.id;
            var kdInduk  = d.kd_customer || '';
            var kota     = d.kota || '';
            var alamat   = d.alamat || '';

            $('#tambah_nama_toko').val(namaToko);
            $('#tambah_kd_customer_induk').val(kdInduk);
            if (kota) $('#tambah_kota').val(kota);
            if (alamat) $('#tambah_alamat').val(alamat);

            fetchNextKodeAcak(kdInduk, namaToko, $('#tambah_kd_customer'));
        } else if (val) {
            var selectedOpt = $(this).find('option:selected');
            var kdInduk = selectedOpt.data('kd') || '';
            $('#tambah_nama_toko').val(val);
            $('#tambah_kd_customer_induk').val(kdInduk);
            fetchNextKodeAcak(kdInduk, val, $('#tambah_kd_customer'));
        }
    });

    // Tombol Auto Generate Kode Acak
    $('#btnAutoKodeTambah').on('click', function() {
        var kdInduk = $('#tambah_kd_customer_induk').val();
        var namaToko = $('#tambah_nama_toko').val();
        fetchNextKodeAcak(kdInduk, namaToko, $('#tambah_kd_customer'));
    });

    // Buka Modal Edit
    $(document).on('click', '.btn-edit-ca', function() {
        var btn = $(this);
        $('#edit_id').val(btn.data('id'));
        $('#edit_kd_customer').val(btn.data('kd'));
        $('#edit_nama_toko').val(btn.data('toko'));
        $('#edit_kd_customer_induk').val(btn.data('induk'));
        $('#edit_kontak_person').val(btn.data('kontak'));
        $('#edit_alamat').val(btn.data('alamat'));
        $('#edit_kota').val(btn.data('kota'));
        $('#edit_nik').val(btn.data('nik'));
        $('#edit_npwp').val(btn.data('npwp') || '000000000000000');

        $('#modalEditCustomerAcak').modal('show');
    });
});
</script>
