<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="fas fa-address-book mr-2 text-primary"></i> Data Customers</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item active">Data Customers</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-primary" style="height: calc(100vh - 180px); display:flex; flex-direction:row; overflow:hidden; margin-bottom:0;">

                    <!-- ===== PANEL KIRI: LIST CUSTOMER ===== -->
                    <div style="width:300px; min-width:280px; border-right:1px solid #dee2e6; display:flex; flex-direction:column; background:#f4f6f9;">

                        <!-- Search Bar -->
                        <div style="padding:10px; background:#e9ecef; border-bottom:1px solid #dee2e6;">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="searchCustomer" class="form-control" placeholder="Cari customer...">
                            </div>
                        </div>

                        <!-- List Item -->
                        <div id="customerList" style="flex:1; overflow-y:auto;">
                            <?php foreach ($customers as $c): ?>
                                <?php
                                    $label = trim((string)($c->nama_kios ?: $c->nama_customer ?: ''));
                                    if ($label === '') $label = '— Tanpa Nama —';
                                    $phone = trim((string)($c->telp1 ?: '-'));
                                ?>
                                <div class="cust-item" data-id="<?= $c->id ?>"
                                     style="display:flex; align-items:center; padding:9px 12px; border-bottom:1px solid #dee2e6; cursor:pointer; background:#fff; transition:background .15s;">
                                    <div style="width:36px; height:36px; border-radius:50%; background:#6c757d; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-right:10px; font-size:14px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div style="overflow:hidden;">
                                        <div style="font-weight:600; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($label) ?></div>
                                        <div style="font-size:11px; color:#007bff;">Customer</div>
                                        <div style="font-size:11px; color:#6c757d;"><?= htmlspecialchars($phone) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div style="padding:8px 12px; background:#e9ecef; border-top:1px solid #dee2e6; display:flex; gap:10px;">
                            <button class="btn btn-sm btn-outline-success" title="Baru" onclick="newCustomer()"><i class="fas fa-plus"></i></button>
                            <button class="btn btn-sm btn-outline-primary" title="Edit" onclick="editCustomer()"><i class="fas fa-pencil-alt"></i></button>
                            <button class="btn btn-sm btn-outline-danger" title="Hapus" onclick="deleteCustomer()"><i class="fas fa-trash-alt"></i></button>
                            <button class="btn btn-sm btn-outline-secondary" title="Refresh" onclick="location.reload()"><i class="fas fa-sync-alt"></i></button>
                        </div>
                    </div>

                    <!-- ===== PANEL KANAN: FORM DETAIL ===== -->
                    <div style="flex:1; padding:20px; overflow-y:auto; display:flex; flex-direction:column;">

                        <h5 id="formTitle" style="font-weight:300; margin-bottom:15px; color:#333;">
                            — Pilih customer di sebelah kiri —
                        </h5>

                        <form id="formCustomer" onsubmit="saveCustomer(event)">
                            <input type="hidden" name="id" id="custId">

                            <!-- Tabs -->
                            <ul class="nav nav-tabs mb-3" id="customerTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#tabUmum">Umum</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#tabAlamat">Alamat & Catatan</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- TAB UMUM -->
                                <div class="tab-pane fade show active" id="tabUmum">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php
                                            $fields_left = [
                                                ['label' => 'ID (Kode Customer)', 'name' => 'kd_customer', 'type' => 'text'],
                                                ['label' => 'Perusahaan',         'name' => 'nama_kios',    'type' => 'text'],
                                                ['label' => 'Tipe',               'name' => 'tipe',         'type' => 'select', 'options' => ['Customer', 'Supplier']],
                                                ['label' => 'Klasifikasi (Sales)','name' => 'nama_sales',   'type' => 'text'],
                                                ['label' => 'Kontak Person',      'name' => 'nama_customer','type' => 'text'],
                                                ['label' => 'Jabatan (Rute)',     'name' => 'kd_rute',      'type' => 'text'],
                                                ['label' => 'Telpon 1',           'name' => 'telp1',        'type' => 'text'],
                                                ['label' => 'Telpon 2',           'name' => 'telp2',        'type' => 'text'],
                                            ];
                                            foreach ($fields_left as $f): ?>
                                                <div class="form-group row mb-2">
                                                    <label class="col-sm-5 col-form-label col-form-label-sm" style="font-size:13px;"><?= $f['label'] ?> :</label>
                                                    <div class="col-sm-7">
                                                        <?php if ($f['type'] === 'select'): ?>
                                                            <select name="<?= $f['name'] ?>" id="<?= $f['name'] ?>" class="form-control form-control-sm field-input" disabled>
                                                                <?php foreach ($f['options'] as $opt): ?>
                                                                    <option value="<?= $opt ?>"><?= $opt ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        <?php else: ?>
                                                            <input type="<?= $f['type'] ?>" name="<?= $f['name'] ?>" id="<?= $f['name'] ?>" class="form-control form-control-sm field-input" readonly>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row mb-2">
                                                <label class="col-sm-5 col-form-label col-form-label-sm" style="font-size:13px;">Batas Kredit : Rp</label>
                                                <div class="col-sm-7">
                                                    <input type="text" name="plafon_aktif" id="plafon_aktif" class="form-control form-control-sm field-input text-right" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row mb-2">
                                                <label class="col-sm-5 col-form-label col-form-label-sm" style="font-size:13px;">Regional :</label>
                                                <div class="col-sm-7">
                                                    <input type="text" name="regional" id="regional" class="form-control form-control-sm field-input" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row mb-2">
                                                <label class="col-sm-5 col-form-label col-form-label-sm" style="font-size:13px;">Jam Buka/Tutup :</label>
                                                <div class="col-sm-7">
                                                    <input type="text" name="jam_buka_tutup" id="jam_buka_tutup" class="form-control form-control-sm field-input" readonly>
                                                </div>
                                            </div>
                                            <div class="form-group row mb-2">
                                                <label class="col-sm-5 col-form-label col-form-label-sm" style="font-size:13px;">Karakteristik Kios :</label>
                                                <div class="col-sm-7">
                                                    <input type="text" name="karakteristik_kios" id="karakteristik_kios" class="form-control form-control-sm field-input" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB ALAMAT -->
                                <div class="tab-pane fade" id="tabAlamat">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group row mb-2">
                                                <label class="col-sm-3 col-form-label col-form-label-sm" style="font-size:13px;">Alamat :</label>
                                                <div class="col-sm-9">
                                                    <textarea name="alamat_kios" id="alamat_kios" class="form-control form-control-sm field-input" rows="4" readonly style="resize:none;"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /tab-content -->

                            <!-- Form Action Buttons -->
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:15px; border-top:1px solid #dee2e6;">
                                <button type="button" class="btn btn-primary btn-sm px-4" onclick="newCustomer()">
                                    <i class="fas fa-plus mr-1"></i> Baru
                                </button>
                                <div>
                                    <button type="button" id="btnBatal" class="btn btn-secondary btn-sm px-4 mr-2" onclick="cancelEdit()" disabled>
                                        <i class="fas fa-times mr-1"></i> Batal
                                    </button>
                                    <button type="submit" id="btnRekam" class="btn btn-success btn-sm px-4" disabled>
                                        <i class="fas fa-save mr-1"></i> Rekam
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- /panel kanan -->

                </div><!-- /card -->
            </div>
        </section>
    </div>

</div><!-- /wrapper -->

<style>
    /* Saat item aktif (terpilih) */
    .cust-item.active-item {
        background: #007bff !important;
        color: #fff;
    }
    .cust-item.active-item .text-primary,
    .cust-item.active-item .text-muted,
    .cust-item.active-item small,
    .cust-item.active-item div {
        color: #fff !important;
    }
    .cust-item:hover {
        background: #e9f5ff !important;
    }
    .cust-item.active-item:hover {
        background: #0069d9 !important;
    }
    /* Field readonly tampak gray, enabled tampak putih */
    .field-input[readonly],
    .field-input[disabled] {
        background-color: #e9ecef !important;
    }
    .field-input:not([readonly]):not([disabled]) {
        background-color: #fff !important;
    }
    /* Tabs styling */
    .nav-tabs .nav-link.active {
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
    }
</style>

<script>
    let currentId = null;
    let isEditing  = false;

    // ============================================================
    // Filter list by search input
    // ============================================================
    $('#searchCustomer').on('input', function() {
        var q = $(this).val().toLowerCase();
        $('.cust-item').each(function() {
            var txt = $(this).text().toLowerCase();
            $(this).toggle(txt.includes(q));
        });
    });

    // ============================================================
    // Pilih customer dari list
    // ============================================================
    $(document).on('click', '.cust-item', function() {
        if (isEditing) {
            if (!confirm('Ada perubahan yang belum disimpan. Lanjut pindah customer?')) return;
            disableForm();
        }
        selectItem($(this));
        var id = $(this).data('id');
        loadDetail(id);
    });

    function selectItem($el) {
        $('.cust-item').removeClass('active-item');
        $el.addClass('active-item');
    }

    function loadDetail(id) {
        currentId = id;
        $.ajax({
            url: "<?= base_url('data_customers/get_detail') ?>",
            type: "GET",
            data: { id: id },
            dataType: "json",
            success: function(data) {
                if (!data) { alert('Data tidak ditemukan.'); return; }
                fillForm(data);
                disableForm();
                var label = data.nama_kios || data.nama_customer || '— Tanpa Nama —';
                $('#formTitle').text(label);
            },
            error: function() { alert('Gagal memuat data.'); }
        });
    }

    function fillForm(data) {
        $('#custId').val(data.id || '');
        $('#kd_customer').val(data.kd_customer || '');
        $('#nama_kios').val(data.nama_kios || '');
        $('#nama_sales').val(data.nama_sales || '');
        $('#nama_customer').val(data.nama_customer || '');
        $('#kd_rute').val(data.kd_rute || '');
        $('#telp1').val(data.telp1 || '');
        $('#telp2').val(data.telp2 || '');
        var plafon = parseFloat(data.plafon_aktif) || 0;
        $('#plafon_aktif').val(plafon > 0 ? plafon.toLocaleString('id-ID') : '');
        $('#regional').val(data.regional || '');
        $('#jam_buka_tutup').val(data.jam_buka_tutup || '');
        $('#karakteristik_kios').val(data.karakteristik_kios || '');
        $('#alamat_kios').val(data.alamat_kios || '');
        // Default tipe: Customer
        $('#tipe').val('Customer');
    }

    function clearForm() {
        $('#formCustomer')[0].reset();
        $('#custId').val('');
        $('#tipe').val('Customer');
        $('#formTitle').text('— Form Customer Baru —');
    }

    // ============================================================
    // Aktifkan / nonaktifkan form
    // ============================================================
    function enableForm() {
        isEditing = true;
        $('.field-input').each(function() {
            if ($(this).is('select')) {
                $(this).prop('disabled', false);
            } else {
                $(this).prop('readonly', false);
            }
        });
        $('#btnRekam').prop('disabled', false);
        $('#btnBatal').prop('disabled', false);
    }

    function disableForm() {
        isEditing = false;
        $('.field-input').each(function() {
            if ($(this).is('select')) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('readonly', true);
            }
        });
        $('#btnRekam').prop('disabled', true);
        $('#btnBatal').prop('disabled', true);
    }

    // ============================================================
    // Aksi tombol
    // ============================================================
    function newCustomer() {
        $('.cust-item').removeClass('active-item');
        currentId = null;
        clearForm();
        enableForm();
        $('#kd_customer').focus();
    }

    function editCustomer() {
        if (!currentId) {
            Swal.fire('Perhatian', 'Pilih data customer terlebih dahulu!', 'warning');
            return;
        }
        enableForm();
        $('#nama_kios').focus();
    }

    function cancelEdit() {
        if (currentId) {
            loadDetail(currentId);
        } else {
            clearForm();
            disableForm();
        }
    }

    function deleteCustomer() {
        if (!currentId) {
            Swal.fire('Perhatian', 'Pilih data customer terlebih dahulu!', 'warning');
            return;
        }
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Yakin ingin menghapus data customer ini? Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.post("<?= base_url('data_customers/delete') ?>", { id: currentId }, function(res) {
                    if (res && res.status === 'success') {
                        Swal.fire('Berhasil!', 'Data customer berhasil dihapus.', 'success')
                            .then(function() { location.reload(); });
                    } else {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus.', 'error');
                    }
                }, 'json');
            }
        });
    }

    function saveCustomer(e) {
        e.preventDefault();

        // Bersihkan format angka sebelum kirim
        var plafon = $('#plafon_aktif').val().replace(/\./g, '').replace(',', '.');
        $('#plafon_aktif').val(plafon);

        $.post("<?= base_url('data_customers/store') ?>", $('#formCustomer').serialize(), function(res) {
            if (res && res.status === 'success') {
                Swal.fire('Berhasil!', 'Data customer berhasil disimpan.', 'success')
                    .then(function() { location.reload(); });
            } else {
                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan.', 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Koneksi ke server gagal.', 'error');
        });
    }
</script>
