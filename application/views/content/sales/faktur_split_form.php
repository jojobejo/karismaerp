<!-- views/content/sales/faktur_split_form.php -->
<?php
$jobdesk = strtoupper((string)$this->session->userdata('jobdesk'));
$is_admin_sc_context = in_array($jobdesk, ['ADMINSC', 'SALESCOUNTER'], true);
$back_url = !empty($back_url) ? $back_url : base_url('sales_order/pecah_faktur');
?>
<style>
    /* Reset gaya tabel agar solid standar (TIDAK SEPERTI CARD / SALES ORDER) */
    table.table, 
    .table-responsive table.table {
        border-collapse: collapse !important;
        border-spacing: 0 !important;
    }
    table.table tbody tr, 
    table.table tfoot tr {
        background: transparent !important;
        box-shadow: none !important;
    }
    table.table tbody td, 
    table.table thead th,
    table.table tfoot td {
        border-radius: 0 !important;
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.04) !important;
        box-shadow: none !important;
    }
</style>
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">

    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?= base_url('assets/images/Karisma.png') ?>"
             alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-cut mr-2 text-warning"></i>
                            Pecah Faktur Z: <strong><?= htmlspecialchars($faktur['no_faktur']) ?></strong>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order/pecah_faktur') ?>">Pecah Faktur</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($faktur['no_faktur']) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Flash error/info -->
                <?php if ($msg = $this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $msg ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Kolom Kiri: Informasi Faktur Induk & Stok Awal -->
                    <div class="col-md-4">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Faktur Induk (Z)</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-borderless table-striped mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted" width="40%">No. Faktur Induk</td>
                                            <td><strong><?= htmlspecialchars($faktur['no_faktur']) ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Customer Induk</td>
                                            <td><?= htmlspecialchars($faktur['customer_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">No. SO</td>
                                            <td><?= htmlspecialchars($faktur['no_so']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Rute / Regional</td>
                                            <td><?= htmlspecialchars($faktur['customer_kd_rute'] ?? $so['customer_kd_rute'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Gudang</td>
                                            <td><?= htmlspecialchars($faktur['gudang_id']) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Panel Stok/Barang Induk untuk Referensi Alokasi -->
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-box-open mr-1"></i> Stok Barang Induk</h3>
                            </div>
                            <div class="card-body p-2">
                                <ul class="list-group list-group-unbordered">
                                    <?php foreach ($details as $d): ?>
                                        <li class="list-group-item py-2" data-parent-item-id="<?= $d['id'] ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="text-dark"><?= htmlspecialchars($d['nama_barang']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($d['kd_barang']) ?></small>
                                                </div>
                                                <div class="text-right">
                                                    <span class="badge badge-info" style="font-size: 0.9rem;">
                                                        Total: <span class="parent-total-qty" data-val="<?= (float)$d['qty'] ?>"><?= (float)$d['qty'] ?></span> pcs
                                                    </span>
                                                    <div class="mt-1">
                                                        <span class="badge badge-secondary parent-sisa-qty" data-val="<?= (float)$d['qty'] ?>">
                                                            Sisa: <?= (float)$d['qty'] ?> pcs
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Pembagian Faktur Turunan -->
                    <div class="col-md-8">
                        <form action="<?= base_url('sales_order/simpan_split_faktur/' . $faktur['id_faktur']) ?>" method="post" id="formSplit">
                            <div class="card card-outline card-warning">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <h3 class="card-title text-dark">
                                        <i class="fas fa-list-ol mr-1"></i> Alokasi Faktur Turunan
                                    </h3>
                                    <div class="card-tools ml-auto">
                                        <button type="button" class="btn btn-warning btn-sm" id="btnAddSplit">
                                            <i class="fas fa-plus"></i> Tambah Customer Turunan
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" id="splitsContainer">
                                    <!-- Dynamic rows will be inserted here -->
                                </div>
                                <div class="card-footer text-right">
                                    <a href="<?= $back_url ?>" class="btn btn-secondary mr-2">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-success" id="btnSubmitSplit" disabled>
                                        <i class="fas fa-save"></i> Simpan & Pecah Faktur
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Template Faktur Turunan (Hidden) -->
    <div id="splitTemplate" style="display: none;">
        <div class="card card-outline card-secondary split-card mb-4 border-left-warning">
            <div class="card-header d-flex justify-content-between align-items-center py-2 bg-light">
                <h6 class="card-title m-0 text-primary font-weight-bold">
                    <i class="fas fa-user-tag mr-1 text-muted"></i> Customer Penerima #<span class="split-index">1</span>
                </h6>
                <div class="card-tools ml-auto">
                    <button type="button" class="btn btn-danger btn-xs btn-remove-split">
                        <i class="fas fa-trash-alt"></i> Hapus
                    </button>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="form-group mb-3">
                    <label class="text-dark font-weight-bold d-flex justify-content-between align-items-center">
                        <span>Pilih Customer Penerima <span class="text-danger">*</span></span>
                        <?php if (!empty($is_customer_acak)): ?>
                            <span class="badge badge-success px-2 py-1" style="font-size: 11px;">
                                <i class="fas fa-lock mr-1"></i> Khusus Kios: <?= htmlspecialchars($kios_induk_nama) ?>
                            </span>
                        <?php endif; ?>
                    </label>
                    <select class="form-control select-customer" required>
                        <option value="">-- Pilih Customer Penerima (<?= count($customers) ?> Kontak Tersedia) --</option>
                        <?php foreach ($customers as $c_idx => $c): ?>
                            <?php 
                            $nominal_riwayat  = (float)($c['total_nominal_pecah'] ?? 0);
                            $faktur_riwayat   = (int)($c['total_faktur_pecah'] ?? 0);
                            $is_limit_reached = !empty($c['is_limit_reached']) || ($nominal_riwayat >= 250000000);
                            $sisa_limit       = max(0.0, 250000000 - $nominal_riwayat);

                            if ($is_limit_reached) {
                                $badge_info = 'LIMIT PENUH: Rp ' . number_format($nominal_riwayat, 0, ',', '.') . ' (Maksimal 250 Jt - Tidak Dapat Digunakan)';
                            } elseif ($nominal_riwayat <= 0 && $faktur_riwayat <= 0) {
                                $badge_info = 'Sisa Kuota: Rp 250.000.000 (Prioritas ' . ($c_idx + 1) . ')';
                            } else {
                                $badge_info = 'Riwayat: Rp ' . number_format($nominal_riwayat, 0, ',', '.') . ' | Sisa: Rp ' . number_format($sisa_limit, 0, ',', '.');
                            }
                            ?>
                            <?php if (!empty($is_customer_acak)): ?>
                                <option value="<?= htmlspecialchars($c['kd_customer']) ?>" <?= $is_limit_reached ? 'disabled class="text-danger font-weight-bold"' : '' ?>>
                                    [<?= htmlspecialchars($c['kd_customer']) ?>] <?= htmlspecialchars($c['kontak_person']) ?> (<?= htmlspecialchars($c['nama_toko']) ?><?= !empty($c['kota']) ? ' - '.$c['kota'] : '' ?>) - [<?= $badge_info ?>]
                                </option>
                            <?php else: ?>
                                <option value="<?= htmlspecialchars($c['kd_customer']) ?>">
                                    <?= htmlspecialchars($c['nama_customer']) ?> <?= !empty($c['nama_kios']) ? '('.htmlspecialchars($c['nama_kios']).')' : '' ?> [<?= htmlspecialchars($c['kd_customer']) ?>]
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Barang</th>
                                <th class="text-center" width="18%">Qty Induk</th>
                                <th class="text-right" width="28%">Harga (Disc 20%)</th>
                                <th class="text-center" width="24%">Qty Alokasi (pcs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $d): ?>
                                <?php 
                                $hrg_asli = (float)($d['hrg_satuan'] ?? 0);
                                $hrg_disc = round($hrg_asli * 0.8, 2);
                                ?>
                                <tr data-item-id="<?= $d['id'] ?>">
                                    <td class="align-middle">
                                        <strong><?= htmlspecialchars($d['nama_barang']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($d['kd_barang']) ?></small>
                                    </td>
                                    <td class="text-center align-middle font-weight-bold text-muted">
                                        <?= (float)$d['qty'] ?> pcs
                                    </td>
                                    <td class="text-right align-middle">
                                        <span class="text-success font-weight-bold">Rp <?= number_format($hrg_disc, 0, ',', '.') ?></span>
                                        <br><small class="text-muted" style="text-decoration: line-through; font-size: 10px;">Rp <?= number_format($hrg_asli, 0, ',', '.') ?></small>
                                        <span class="badge badge-danger px-1 py-0" style="font-size: 8.5px;">-20%</span>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               class="form-control form-control-sm text-center input-qty-alokasi" 
                                               data-item-id="<?= $d['id'] ?>" 
                                               min="0" 
                                               max="<?= (float)$d['qty'] ?>" 
                                               value="0" 
                                               required>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<script>
$(document).ready(function () {
    let splitCount = 0;

    function salesToast(type, message) {
        if (window.Swal) {
            Swal.fire({ toast:true, position:'top-end', icon:type || 'info', title:message || '', timer:2600, showConfirmButton:false });
        } else {
            alert(message || '');
        }
    }

    var availableCustomers = <?= json_encode($customers) ?>;

    function getNextAvailableCustomerSingle() {
        var selectedKodes = {};
        $('#splitsContainer .select-customer').each(function() {
            var val = $(this).val();
            if (val) {
                selectedKodes[val] = true;
            }
        });

        for (var i = 0; i < availableCustomers.length; i++) {
            var c = availableCustomers[i];
            var isLimit = (c.is_limit_reached == 1 || c.is_limit_reached === true || (parseFloat(c.total_nominal_pecah) || 0) >= 250000000);
            if (!selectedKodes[c.kd_customer] && !isLimit) {
                return c;
            }
        }
        // Jika semua yang belum dipilih ternyata limit, cari yang belum limit
        for (var j = 0; j < availableCustomers.length; j++) {
            var c2 = availableCustomers[j];
            var isLimit2 = (c2.is_limit_reached == 1 || c2.is_limit_reached === true || (parseFloat(c2.total_nominal_pecah) || 0) >= 250000000);
            if (!isLimit2) {
                return c2;
            }
        }
        return availableCustomers.length > 0 ? availableCustomers[0] : null;
    }

    // Tambah baris split
    $('#btnAddSplit').on('click', function() {
        splitCount++;
        const clone = $('#splitTemplate').children().clone();
        
        // Atur name/id input agar ter-submit dengan benar sebagai array
        clone.find('.select-customer').attr('name', 'splits[' + splitCount + '][kd_customer]');
        clone.find('.input-qty-alokasi').each(function() {
            const itemId = $(this).data('item-id');
            $(this).attr('name', 'splits[' + splitCount + '][items][' + itemId + ']');
        });

        // Otomatis pilih customer berikutnya yang belum terpakai
        var nextCust = getNextAvailableCustomerSingle();
        if (nextCust) {
            clone.find('.select-customer').val(nextCust.kd_customer);
        }

        clone.find('.split-index').text(splitCount);
        $('#splitsContainer').append(clone);

        // Inisialisasi Select2 Searchable pada baris pecahan baru
        if ($.fn.select2) {
            clone.find('.select-customer').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: '-- Cari & Pilih Customer Penerima --',
                language: {
                    noResults: function() {
                        return "Tidak ada customer ditemukan";
                    }
                }
            });
        }
        
        recalculateAllocations();
        $('#btnSubmitSplit').prop('disabled', false);
        salesToast('success', 'Customer turunan #' + splitCount + ' ditambahkan.');
    });

    // Hapus baris split
    $(document).on('click', '.btn-remove-split', function() {
        $(this).closest('.split-card').remove();
        
        // Re-indexing
        splitCount = 0;
        $('#splitsContainer .split-card').each(function() {
            splitCount++;
            $(this).find('.split-index').text(splitCount);
            $(this).find('.select-customer').attr('name', 'splits[' + splitCount + '][kd_customer]');
            $(this).find('.input-qty-alokasi').each(function() {
                const itemId = $(this).data('item-id');
                $(this).attr('name', 'splits[' + splitCount + '][items][' + itemId + ']');
            });
        });

        recalculateAllocations();
        if (splitCount === 0) {
            $('#btnSubmitSplit').prop('disabled', true);
        }
        salesToast('info', 'Customer turunan dihapus.');
    });

    // Ketika qty alokasi diubah
    $(document).on('input change', '.input-qty-alokasi', function() {
        const itemId = $(this).data('item-id');
        const val = parseFloat($(this).val()) || 0;
        
        // Find the parent item total qty (which is already the remaining qty from controller)
        const parentTotal = parseFloat($('li[data-parent-item-id="' + itemId + '"]').find('.parent-total-qty').data('val')) || 0;
        
        // Calculate allocated qty from other inputs of the same item ID
        let otherAllocated = 0;
        const currentInput = this;
        $('#splitsContainer .input-qty-alokasi[data-item-id="' + itemId + '"]').each(function() {
            if (this !== currentInput) {
                otherAllocated += parseFloat($(this).val()) || 0;
            }
        });
        
        const maxAllowed = parentTotal - otherAllocated;
        if (val < 0) {
            $(this).val(0);
        } else if (val > maxAllowed) {
            const adjustedVal = maxAllowed >= 0 ? maxAllowed : 0;
            $(this).val(adjustedVal);
            salesToast('warning', 'Jumlah alokasi melebihi sisa kuantitas induk (' + adjustedVal + ' pcs).');
        }
        recalculateAllocations();
    });

    // Fungsi menghitung sisa alokasi
    function recalculateAllocations() {
        const parentItems = {};
        
        // Inisialisasi total & sisa kuantitas induk
        $('.parent-total-qty').each(function() {
            const id = $(this).closest('li').data('parent-item-id');
            const total = parseFloat($(this).data('val')) || 0;
            parentItems[id] = {
                total: total,
                allocated: 0
            };
        });

        // Jumlahkan semua alokasi dari form anak-anak
        $('#splitsContainer .input-qty-alokasi').each(function() {
            const id = $(this).data('item-id');
            const val = parseFloat($(this).val()) || 0;
            if (parentItems[id]) {
                parentItems[id].allocated += val;
            }
        });

        // Update tampilan sisa qty di panel kiri & validasi batas
        let isValid = true;
        let totalAllocatedAny = 0;
        
        $.each(parentItems, function(id, data) {
            const sisa = data.total - data.allocated;
            const $sisaBadge = $('li[data-parent-item-id="' + id + '"]').find('.parent-sisa-qty');
            
            $sisaBadge.text('Sisa: ' + sisa + ' pcs').data('val', sisa);
            totalAllocatedAny += data.allocated;

            if (sisa < 0) {
                $sisaBadge.removeClass('badge-secondary badge-success').addClass('badge-danger');
                isValid = false; // Melebihi kuantitas induk
            } else if (sisa === 0) {
                $sisaBadge.removeClass('badge-secondary badge-danger').addClass('badge-success');
            } else {
                $sisaBadge.removeClass('badge-danger badge-success').addClass('badge-secondary');
            }
        });

        // Tombol simpan hanya aktif jika:
        // 1. Ada minimal 1 customer turunan.
        // 2. Tidak ada item yang alokasinya melebihi stok induk.
        // 3. Ada setidaknya beberapa barang yang dialokasikan (total > 0).
        if (splitCount > 0 && isValid && totalAllocatedAny > 0) {
            $('#btnSubmitSplit').prop('disabled', false);
        } else {
            $('#btnSubmitSplit').prop('disabled', true);
        }
    }

    // Validasi submit form
    $('#formSplit').on('submit', function(e) {
        let hasEmptyCustomer = false;
        $('#splitsContainer .select-customer').each(function() {
            if (!$(this).val()) {
                hasEmptyCustomer = true;
            }
        });

        if (hasEmptyCustomer) {
            e.preventDefault();
            salesToast('error', 'Semua customer penerima harus dipilih.');
            return false;
        }

        // Pastikan alokasi total tidak 0
        let totalQty = 0;
        $('#splitsContainer .input-qty-alokasi').each(function() {
            totalQty += parseFloat($(this).val()) || 0;
        });

        if (totalQty <= 0) {
            e.preventDefault();
            salesToast('error', 'Harap isi jumlah alokasi kuantitas barang.');
            return false;
        }
    });
});
</script>
