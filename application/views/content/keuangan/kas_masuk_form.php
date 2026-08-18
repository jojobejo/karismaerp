<!-- application/views/content/keuangan/kas_masuk_form.php -->
<style>
    :root {
        --zahir-blue: #127fad;
        --zahir-dark-blue: #0f6c94;
        --zahir-light-bg: #f5f8fa;
        --zahir-card-border: #cbd5e1;
        --zahir-text: #1e293b;
    }

    body.hold-transition {
        background-color: var(--zahir-light-bg);
    }

    .form-container {
        font-family: 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--zahir-text);
        padding: 20px;
    }

    .zahir-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .card-header-zahir {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 15px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-header-zahir h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--zahir-blue);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group-zahir {
        margin-bottom: 12px;
    }

    .form-group-zahir label {
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 4px;
        color: #475569;
    }

    .form-control-zahir {
        font-size: 13px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        height: 34px;
        transition: border-color 0.15s ease-in-out;
    }

    .form-control-zahir:focus {
        border-color: var(--zahir-blue);
        box-shadow: 0 0 0 2px rgba(18, 127, 173, 0.15);
        outline: none;
    }

    .terbilang-text {
        font-size: 12px;
        font-style: italic;
        color: var(--zahir-blue);
        font-weight: 600;
        margin-top: 5px;
        background: #f0f9ff;
        padding: 6px 12px;
        border-radius: 4px;
        border-left: 3px solid var(--zahir-blue);
    }

    /* Table grid */
    .table-grid {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .table-grid th {
        background-color: var(--zahir-blue);
        color: #fff;
        font-weight: 500;
        font-size: 13px;
        padding: 8px 12px;
        text-align: left;
    }

    .table-grid td {
        border: 1px solid #e2e8f0;
        padding: 4px 8px;
        background: #fff;
    }

    .table-grid input {
        width: 100%;
        border: 1px solid transparent;
        background: transparent;
        padding: 4px 6px;
        font-size: 13px;
    }

    .table-grid input:focus {
        border-color: var(--zahir-blue);
        background: #fff;
        outline: none;
        border-radius: 2px;
    }

    .table-grid tr:hover td {
        background: #f8fafc;
    }

    .footer-actions {
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 15px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-zahir {
        font-weight: 600;
        font-size: 13px;
        padding: 6px 20px;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .btn-zahir-primary {
        background-color: var(--zahir-blue);
        border: 1px solid var(--zahir-dark-blue);
        color: #fff;
    }

    .btn-zahir-primary:hover {
        background-color: var(--zahir-dark-blue);
        color: #fff;
    }

    .btn-zahir-secondary {
        background-color: #fff;
        border: 1px solid #cbd5e1;
        color: #475569;
    }

    .btn-zahir-secondary:hover {
        background-color: #f8fafc;
        color: #1e293b;
    }

    .btn-zahir-danger {
        background-color: #ef4444;
        border: 1px solid #dc2626;
        color: #fff;
    }

    .btn-zahir-danger:hover {
        background-color: #dc2626;
        color: #fff;
    }

    .input-lookup-wrap {
        display: flex;
        align-items: center;
    }

    .input-lookup-wrap input {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .btn-lookup-trigger {
        border: 1px solid #cbd5e1;
        border-left: none;
        background-color: #f1f5f9;
        height: 34px;
        padding: 0 12px;
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
        color: #475569;
        cursor: pointer;
    }

    .btn-lookup-trigger:hover {
        background-color: #e2e8f0;
    }

    .account-table-select {
        max-height: 350px;
        overflow-y: auto;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="form-container">
            <form id="form-kas-masuk">
                <input type="hidden" name="id_kas_masuk" id="id_kas_masuk" value="<?= $header ? $header['id_kas_masuk'] : '' ?>">
                
                <div class="zahir-card">
                    <div class="card-header-zahir">
                        <h3><i class="fas fa-money-bill-wave"></i> Kas Masuk</h3>
                        <div class="d-flex" style="gap: 15px;">
                            <div class="custom-control custom-checkbox mt-1">
                                <input class="custom-control-input" type="checkbox" name="is_inclusive_tax" id="inclusive-tax" value="1" <?= ($header && $header['is_inclusive_tax']) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="inclusive-tax" style="font-size: 13px;">Inclusive Tax</label>
                            </div>
                            <div class="custom-control custom-checkbox mt-1">
                                <input class="custom-control-input" type="checkbox" name="is_giro" id="giro" value="1" <?= ($header && $header['is_giro']) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="giro" style="font-size: 13px;">Giro</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group row form-group-zahir">
                                    <label class="col-sm-3 col-form-label">Akun Kas : <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <select class="form-control form-control-zahir" name="id_akun_kas" id="id_akun_kas" required>
                                            <option value="">-- Pilih Akun Kas/Bank --</option>
                                            <?php foreach ($cash_accounts as $acc): ?>
                                                <option value="<?= $acc['id_akun'] ?>" <?= ($header && $header['id_akun_kas'] == $acc['id_akun']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($acc['kode_akun'] . ' - ' . $acc['nama_akun']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row form-group-zahir">
                                    <label class="col-sm-3 col-form-label">Disetor Oleh : <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <div class="input-lookup-wrap">
                                            <input type="text" class="form-control form-control-zahir w-100" name="diterima_dari" id="diterima_dari" value="<?= $header ? htmlspecialchars($header['diterima_dari']) : '' ?>" placeholder="Ketik atau cari penyetor..." required>
                                            <button type="button" class="btn-lookup-trigger" id="btn-lookup-pengirim"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row form-group-zahir">
                                    <label class="col-sm-3 col-form-label">Sebesar :</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control form-control-zahir" id="total_amount_display" value="Rp 0,00" readonly style="font-weight: bold; font-size: 15px; color: var(--zahir-blue); background-color: #f8fafc;">
                                        <div class="terbilang-text" id="terbilang-box">Nol Rupiah</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group row form-group-zahir">
                                    <label class="col-sm-3 col-form-label">No. Referensi: <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control form-control-zahir w-100" name="no_referensi" id="no_referensi" value="<?= htmlspecialchars($next_ref) ?>" required>
                                    </div>
                                </div>

                                <div class="form-group row form-group-zahir">
                                    <label class="col-sm-3 col-form-label">Tanggal : <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control form-control-zahir w-100" name="tanggal" id="tanggal" value="<?= $header ? $header['tanggal'] : date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="form-group row form-group-zahir">
                                    <label class="col-sm-3 col-form-label">Memo :</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control form-control-zahir w-100" name="memo" id="memo" value="<?= $header ? htmlspecialchars($header['memo']) : '' ?>" placeholder="Catatan transaksi...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grid Alokasi Dana -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 style="font-size: 14px; font-weight: 700; color: #475569; border-bottom: 2px solid #cbd5e1; padding-bottom: 6px;">Alokasi Dana</h5>
                                <div class="table-responsive">
                                    <table class="table-grid" id="table-alokasi-dana">
                                        <thead>
                                            <tr>
                                                <th style="width: 25%">Kode</th>
                                                <th style="width: 50%">Nama Akun</th>
                                                <th style="width: 25%; text-align: right;">Nilai (Cr)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="alokasi-dana-body">
                                            <!-- Dynamic Rows -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-xs btn-outline-primary" id="btn-tambah-baris">
                                        <i class="fas fa-plus mr-1"></i> Tambah Baris
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="footer-actions">
                        <!-- Left Buttons -->
                        <div style="gap: 10px; display: flex;">
                            <button type="button" class="btn btn-zahir btn-zahir-danger" id="btn-hapus-baris">
                                Hapus Baris
                            </button>
                            <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-rekam-ulang">
                                Rekam Ulang
                            </button>
                            <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-buka-ulang">
                                Buka Ulang
                            </button>
                        </div>
                        
                        <!-- Right Buttons -->
                        <div style="gap: 10px; display: flex; align-items: center;">
                            <div class="custom-control custom-checkbox mr-3">
                                <input class="custom-control-input" type="checkbox" id="print-direct" checked>
                                <label class="custom-control-label" for="print-direct" style="font-size: 13px;">Cetak</label>
                            </div>
                            <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-batal">
                                Batal
                            </button>
                            <button type="button" class="btn btn-zahir btn-zahir-secondary" id="btn-rekam-draft">
                                Rekam Draft
                            </button>
                            <button type="submit" class="btn btn-zahir btn-zahir-primary" id="btn-rekam">
                                Rekam
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL LOOKUP AKUN -->
    <div class="modal fade zahir-modal" id="modalLookupAkun" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daftar Akun (Perkiraan)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-sm mb-3">
                        <input type="text" class="form-control" id="search-lookup-akun" placeholder="Cari Kode atau Nama Akun...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    
                    <div class="account-table-select">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Akun</th>
                                </tr>
                            </thead>
                            <tbody id="lookup-akun-body">
                                <!-- Loaded via Ajax -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-sm btn-danger" id="btn-lookup-new" disabled>Baru</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="btn-lookup-edit" disabled>Edit</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="btn-lookup-update" disabled>Update</button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-sm btn-primary" id="btn-confirm-akun">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let rowIndex = 0;
    let activeRow = null;

    // Load initial empty row or edit details
    <?php if ($header && !empty($header['details'])): ?>
        <?php foreach ($header['details'] as $detail): ?>
            addRow(<?= $detail['id_akun'] ?>, "<?= $detail['kode_akun'] ?>", "<?= $detail['nama_akun'] ?>", <?= (float)$detail['nilai'] ?>);
        <?php endforeach; ?>
    <?php else: ?>
        addRow();
    <?php endif; ?>

    // Check if posted
    <?php if ($header && $header['status'] === 'POSTED'): ?>
        disableForm();
    <?php endif; ?>

    // Spelled / Terbilang calculation initially
    calculateTotal();

    // Add Row Click
    $('#btn-tambah-baris').click(function() {
        addRow();
    });

    // Delete Row Click
    $('#btn-hapus-baris').click(function() {
        if (activeRow) {
            activeRow.remove();
            activeRow = null;
            calculateTotal();
        } else {
            alert('Pilih baris alokasi dana terlebih dahulu.');
        }
    });

    // Batal / Cancel button
    $('#btn-batal').click(function() {
        window.location.href = "<?= base_url('keuangan/kas_masuk') ?>";
    });

    // Focus row selection in table-grid
    $(document).on('click', '#alokasi-dana-body tr', function() {
        $('#alokasi-dana-body tr').css('background-color', '#fff');
        $(this).css('background-color', '#bbdefb');
        activeRow = $(this);
    });

    // Dynamic keyup for calculation
    $(document).on('keyup change', '.detail-nilai', function() {
        calculateTotal();
    });

    // Input lookup triggers on row Kode cell click
    $(document).on('click', '.detail-kode, .detail-nama', function() {
        if ($('#form-kas-masuk').data('disabled') === true) return;
        activeRow = $(this).closest('tr');
        $('#search-lookup-akun').val('');
        loadAccountsLookup('');
        $('#modalLookupAkun').modal('show');
    });

    // Lookup accounts list Ajax
    $('#search-lookup-akun').on('keyup input', function() {
        loadAccountsLookup($(this).val());
    });

    $(document).on('click', '#lookup-akun-body tr', function() {
        if ($(this).data('id')) {
            confirmAccountSelection($(this));
        }
    });

    $('#btn-confirm-akun').click(function() {
        let selectedRow = $('#lookup-akun-body tr.selected');
        if (selectedRow.length > 0) {
            confirmAccountSelection(selectedRow);
        } else {
            $('#modalLookupAkun').modal('hide');
        }
    });

    function confirmAccountSelection(rowElement) {
        let id = rowElement.data('id');
        let code = rowElement.data('code');
        let name = rowElement.data('name');

        if (activeRow && id) {
            activeRow.find('.detail-id-akun').val(id);
            activeRow.find('.detail-kode').val(code);
            activeRow.find('.detail-nama').val(name);
        }
        $('#modalLookupAkun').modal('hide');
    }

    function loadAccountsLookup(searchQuery) {
        $.ajax({
            url: "<?= base_url('keuangan/kas_masuk/accounts_lookup') ?>",
            type: "GET",
            data: { search: searchQuery },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    let html = '';
                    if (response.data.length > 0) {
                        response.data.forEach(function(row) {
                            html += `<tr data-id="${row.id_akun}" data-code="${row.kode_akun}" data-name="${row.nama_akun}">
                                <td style="width: 30%; font-weight: bold; color: var(--zahir-blue);">${row.kode_akun}</td>
                                <td>${row.nama_akun}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="2" class="text-center text-muted">Akun tidak ditemukan.</td></tr>';
                    }
                    $('#lookup-akun-body').html(html);
                }
            }
        });
    }

    // Add Row helper
    function addRow(id_akun = '', kode = '', nama = '', nilai = '') {
        rowIndex++;
        let rowHtml = `<tr id="row-${rowIndex}">
            <td>
                <input type="hidden" name="details[${rowIndex}][id_akun]" class="detail-id-akun" value="${id_akun}">
                <input type="text" class="detail-kode" value="${kode}" readonly placeholder="Klik untuk pilih akun..." style="font-weight: bold; color: var(--zahir-blue); cursor: pointer;">
            </td>
            <td>
                <input type="text" class="detail-nama" value="${nama}" readonly style="cursor: pointer;">
            </td>
            <td>
                <input type="number" step="0.01" name="details[${rowIndex}][nilai]" class="detail-nilai text-right" value="${nilai}" placeholder="0.00" style="text-align: right; font-weight: bold;">
            </td>
        </tr>`;
        $('#alokasi-dana-body').append(rowHtml);
    }

    function calculateTotal() {
        let total = 0.0;
        $('.detail-nilai').each(function() {
            let val = parseFloat($(this).val());
            if (!isNaN(val)) {
                total += val;
            }
        });

        // Display Rupiah Format
        $('#total_amount_display').val('Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        // Spelled Out Text via AJAX
        $.ajax({
            url: "<?= base_url('keuangan/kas_masuk/terbilang_ajax') ?>",
            type: "POST",
            data: { amount: total },
            dataType: "json",
            success: function(res) {
                if (res.success) {
                    $('#terbilang-box').text(res.spelled);
                }
            }
        });
    }

    function disableForm() {
        $('#form-kas-masuk').data('disabled', true);
        $('#form-kas-masuk input, #form-kas-masuk select, #form-kas-masuk textarea').prop('disabled', true);
        $('#btn-tambah-baris, #btn-hapus-baris, #btn-rekam-draft, #btn-rekam').hide();
        $('#btn-buka-ulang, #btn-rekam-ulang').show();
    }

    function enableForm() {
        $('#form-kas-masuk').data('disabled', false);
        $('#form-kas-masuk input, #form-kas-masuk select, #form-kas-masuk textarea').prop('disabled', false);
        $('#total_amount_display').prop('disabled', true);
        $('#btn-tambah-baris, #btn-hapus-baris, #btn-rekam-draft, #btn-rekam').show();
    }

    // Button Buka Ulang Handler (Unpost/Reopen for editing)
    $('#btn-buka-ulang').click(function() {
        enableForm();
        alert('Form telah dibuka kembali. Anda sekarang dapat mengedit transaksi ini dan mengklik Rekam atau Rekam Draft.');
    });

    // Button Rekam Ulang Handler (Duplicate/Create New from Template)
    $('#btn-rekam-ulang').click(function() {
        $('#id_kas_masuk').val('');
        enableForm();
        
        $.ajax({
            url: "<?= base_url('keuangan/kas_masuk/ref_no_ajax') ?>",
            type: "GET",
            dataType: "json",
            success: function(res) {
                if (res.success && res.ref_no) {
                    $('#no_referensi').val(res.ref_no);
                }
            }
        });
        
        alert('Form telah disiapkan sebagai transaksi baru. Silakan sesuaikan data lalu klik Rekam atau Rekam Draft.');
    });

    // Submit handlers
    $('#btn-rekam').click(function(e) {
        e.preventDefault();
        if ($('input[name="post_now"]').length === 0) {
            $('#form-kas-masuk').append('<input type="hidden" name="post_now" value="1">');
        }
        $('#form-kas-masuk').submit();
    });

    $('#btn-rekam-draft').click(function(e) {
        e.preventDefault();
        $('input[name="post_now"]').remove();
        $('#form-kas-masuk').submit();
    });

    $('#form-kas-masuk').submit(function(e) {
        e.preventDefault();

        let idAkunKas = $('#id_akun_kas').val();
        let diterimaDari = $.trim($('#diterima_dari').val());
        let noRef = $.trim($('#no_referensi').val());

        if (!idAkunKas || !diterimaDari || !noRef) {
            alert('Mohon lengkapi semua kolom bertanda bintang (*).');
            return false;
        }

        let hasValidDetail = false;
        $('.detail-id-akun').each(function() {
            let idAkun = $(this).val();
            let nilaiRow = parseFloat($(this).closest('tr').find('.detail-nilai').val()) || 0;
            if (idAkun && nilaiRow > 0) {
                hasValidDetail = true;
            }
        });

        if (!hasValidDetail) {
            alert('Minimal harus ada 1 alokasi dana dengan akun valid dan nilai lebih dari nol.');
            return false;
        }
        
        let btnRekam = $('#btn-rekam, #btn-rekam-draft');
        btnRekam.prop('disabled', true);
        
        let formData = $(this).serialize();

        $.ajax({
            url: "<?= base_url('keuangan/kas_masuk/save') ?>",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(res) {
                btnRekam.prop('disabled', false);
                if (res.success) {
                    alert(res.message);
                    window.location.href = "<?= base_url('keuangan/kas_masuk') ?>";
                } else {
                    alert(res.message || 'Gagal menyimpan transaksi.');
                }
            },
            error: function(xhr, status, err) {
                btnRekam.prop('disabled', false);
                alert('Terjadi kesalahan koneksi/server saat menyimpan transaksi.');
            }
        });
    });

    // Lookup pengirim trigger
    $('#btn-lookup-pengirim').click(function() {
        alert('Ketik nama pengirim langsung pada kolom input Diterima Dari.');
    });
});
</script>
</body>
