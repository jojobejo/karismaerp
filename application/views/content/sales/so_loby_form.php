<!-- views/content/sales/so_loby_form.php -->
<?php
    $is_edit   = !empty($so);
    $id_so_val = $is_edit ? ($so['no_so'] ?? '') : ($no_so ?? '');
    $action    = $is_edit
        ? base_url('sales_order_loby/update/' . $so['id_so'])
        : base_url('sales_order_loby/store');

    $gid_aktif = $is_edit ? ($so['gudang_id'] ?? '') : '';

    function escAttr($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<style>
    .table-items th {
        background-color: #f1f5f9;
        font-size: 12px;
        text-transform: uppercase;
        font-weight: 700;
        vertical-align: middle;
    }
    .table-items td {
        vertical-align: middle;
    }
    .card-loby-header {
        background: linear-gradient(135deg, #1788b8 0%, #0d5f83 100%);
        color: #fff;
    }
</style>
<body class="hold-transition sidebar-mini sidebar-collapse sales-modern-page">
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
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <i class="fas fa-<?= $is_edit ? 'edit' : 'plus-circle' ?> mr-2 text-primary"></i>
                            <?= $is_edit ? 'Edit Sales Order Loby' : 'Buat Sales Order Loby Baru' ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('sales_order_loby') ?>">Sales Order Loby</a></li>
                            <li class="breadcrumb-item active"><?= $is_edit ? 'Edit' : 'Buat Baru' ?></li>
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
                            <i class="fas fa-<?= $key === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-1"></i>
                            <?= $msg ?>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <form action="<?= $action ?>" method="post" id="form-so-loby">
                    <div class="row">
                        <!-- HEADER PANEL -->
                        <div class="col-md-6">
                            <div class="card card-outline card-primary shadow-sm h-100">
                                <div class="card-header py-2">
                                    <h3 class="card-title font-weight-bold"><i class="fas fa-file-invoice mr-1 text-primary"></i> Data Utama SO Loby</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label font-weight-bold">No. SO Loby</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control form-control-sm font-weight-bold" name="no_so" value="<?= escAttr($id_so_val) ?>" readonly style="background:#e9ecef">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label font-weight-bold">Tanggal <span class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control form-control-sm" name="tanggal" required value="<?= $is_edit ? escAttr($so['tanggal_transaksi']) : date('Y-m-d') ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label font-weight-bold">Customer <span class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <div class="input-group input-group-sm">
                                                <input type="text" id="customer_display" class="form-control font-weight-bold" placeholder="-- Klik untuk Pilih Customer --" value="<?= $is_edit ? escAttr($so['customer_name'] ?: $so['nama_customer']) : '' ?>" readonly style="background:#fff; cursor:pointer;" onclick="bukaModalCustomer()">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-primary" type="button" onclick="bukaModalCustomer()"><i class="fas fa-search"></i> Cari</button>
                                                </div>
                                            </div>
                                            <input type="hidden" name="customer_id" id="customer_id" value="<?= $is_edit ? escAttr($so['kd_customer']) : '' ?>" required>
                                            <input type="hidden" name="customer_name" id="customer_name" value="<?= $is_edit ? escAttr($so['customer_name'] ?: $so['nama_customer']) : '' ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label font-weight-bold">Gudang Stok <span class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <select name="gudang_id" id="gudang_id" class="form-control form-control-sm" required <?= $is_edit ? 'disabled' : '' ?>>
                                                <option value="">-- Pilih Lokasi Gudang --</option>
                                                <?php foreach ($gudang_list as $g): ?>
                                                    <option value="<?= escAttr($g['id_gudang']) ?>" <?= ((string)$gid_aktif === (string)$g['id_gudang']) ? 'selected' : '' ?>>
                                                        <?= escAttr($g['nama_gudang']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if ($is_edit): ?>
                                                <input type="hidden" name="gudang_id" value="<?= escAttr($gid_aktif) ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KETENTUAN LOBY -->
                        <div class="col-md-6">
                            <div class="card card-outline card-success shadow-sm h-100">
                                <div class="card-header py-2">
                                    <h3 class="card-title font-weight-bold text-success"><i class="fas fa-cash-register mr-1"></i> Ketentuan Transaksi Loby</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label font-weight-bold">Metode Bayar</label>
                                        <div class="col-sm-8">
                                            <span class="badge badge-success px-3 py-2" style="font-size: 14px;">
                                                <i class="fas fa-money-bill-wave mr-1"></i> CASH (Terkunci Otomatis)
                                            </span>
                                            <input type="hidden" name="cara_pembayaran" value="cash">
                                            <div class="small text-muted mt-1">Penjualan Loby hanya melayani metode pembayaran Cash langsung di tempat.</div>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label font-weight-bold">Pengambilan</label>
                                        <div class="col-sm-8">
                                            <div class="text-dark font-weight-bold"><i class="fas fa-hand-holding-box text-info mr-1"></i> Diambil Langsung di Loby</div>
                                            <div class="small text-muted">Barang langsung diserahkan tanpa melalui rute / DO Logistik.</div>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label font-weight-bold">Catatan SO</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control form-control-sm" name="catatan" rows="2" placeholder="Catatan transaksi Loby (opsional)"><?= $is_edit ? escAttr($so['catatan']) : 'Penjualan Loby (Direct Cash)' ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ITEMS TABLE CARD -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                            <h3 class="card-title font-weight-bold text-dark m-0">
                                <i class="fas fa-boxes mr-1 text-primary"></i> Rincian Barang Penjualan Loby
                            </h3>
                            <div class="card-tools m-0">
                                <button type="button" class="btn btn-sm btn-primary font-weight-bold" onclick="bukaModalBarang()">
                                    <i class="fas fa-plus mr-1"></i> Tambah Barang dari Stok
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-items m-0" id="table-so-items">
                                    <thead>
                                        <tr class="text-center">
                                            <th style="width: 40px;">No</th>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>Lot / Exp Date</th>
                                            <th style="width: 120px;">Qty Ambil</th>
                                            <th style="width: 110px;">Satuan</th>
                                            <th style="width: 140px;">Harga Satuan</th>
                                            <th style="width: 90px;">Disc (%)</th>
                                            <th style="width: 150px;">Subtotal</th>
                                            <th style="width: 50px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-items">
                                        <?php if ($is_edit && !empty($details)): ?>
                                            <?php $i = 0; foreach ($details as $d): ?>
                                                <tr id="row-<?= $i ?>" data-kd="<?= escAttr($d['kd_barang']) ?>">
                                                    <td class="text-center row-number"><?= $i + 1 ?></td>
                                                    <td>
                                                        <span class="font-weight-bold text-primary"><?= escAttr($d['kd_barang']) ?></span>
                                                        <input type="hidden" name="items[<?= $i ?>][kd_barang]" value="<?= escAttr($d['kd_barang']) ?>">
                                                        <input type="hidden" name="items[<?= $i ?>][nama_barang]" value="<?= escAttr($d['nama_barang']) ?>">
                                                        <input type="hidden" name="items[<?= $i ?>][hrg_pokok]" value="<?= (float)$d['hrg_pokok'] ?>">
                                                        <input type="hidden" name="items[<?= $i ?>][berat_gram]" value="<?= (float)$d['berat_gram'] ?>">
                                                        <input type="hidden" name="items[<?= $i ?>][kubikasi_m3]" value="<?= (float)$d['kubikasi_m3'] ?>">
                                                        <input type="hidden" name="items[<?= $i ?>][isi_per_box]" value="<?= (int)$d['isi_per_box'] ?>">
                                                    </td>
                                                    <td><?= escAttr($d['nama_barang']) ?></td>
                                                    <td>
                                                        <div><small class="badge badge-light border">Lot: <?= escAttr($d['no_lot'] ?: '-') ?></small></div>
                                                        <div><small class="text-muted">Exp: <?= escAttr($d['expired_date'] ?: '-') ?></small></div>
                                                        <input type="hidden" name="items[<?= $i ?>][no_lot]" value="<?= escAttr($d['no_lot']) ?>">
                                                        <input type="hidden" name="items[<?= $i ?>][expired_date]" value="<?= escAttr($d['expired_date']) ?>">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" min="0.001" class="form-control form-control-sm text-right item-qty" name="items[<?= $i ?>][qty]" value="<?= (float)$d['qty'] ?>" onchange="hitungSubtotal(<?= $i ?>)" required>
                                                    </td>
                                                    <td class="text-center">
                                                        <span><?= escAttr($d['satuan']) ?></span>
                                                        <input type="hidden" name="items[<?= $i ?>][satuan]" value="<?= escAttr($d['satuan']) ?>">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" min="0" class="form-control form-control-sm text-right item-harga" name="items[<?= $i ?>][hrg_satuan]" value="<?= (float)$d['hrg_satuan'] ?>" onchange="hitungSubtotal(<?= $i ?>)" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" min="0" max="100" class="form-control form-control-sm text-right item-disc" name="items[<?= $i ?>][disc]" value="<?= (float)$d['disc'] ?>" onchange="hitungSubtotal(<?= $i ?>)">
                                                        <input type="hidden" name="items[<?= $i ?>][pajak]" value="0">
                                                    </td>
                                                    <td class="text-right font-weight-bold item-subtotal">
                                                        Rp <?= number_format((float)$d['total_harga'], 0, ',', '.') ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-xs btn-danger" onclick="hapusBaris(<?= $i ?>)"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            <?php $i++; endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="8" class="text-right font-weight-bold" style="font-size: 15px;">TOTAL TRANSAKSI (CASH):</th>
                                            <th class="text-right font-weight-bold text-primary" style="font-size: 16px;" id="display-grand-total">Rp 0</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between py-3">
                            <a href="<?= base_url('sales_order_loby') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                            <button type="submit" class="btn btn-primary font-weight-bold px-4"><i class="fas fa-save mr-1"></i> Simpan Sales Order Loby</button>
                        </div>
                    </div>
                </form>

            </div>
        </section>
    </div>

    <?php $this->load->view('partial/main/footer') ?>
</div>

<!-- MODAL PILIH CUSTOMER -->
<div class="modal fade" id="modalCustomer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header card-loby-header py-2">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-users mr-2"></i> Pilih Customer</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered" id="tabelLookupCustomer" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Customer</th>
                                <th>Kios / Toko</th>
                                <th>Regional</th>
                                <th style="width:70px;">Pilih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $c): ?>
                                <tr>
                                    <td><span class="badge badge-secondary"><?= escAttr($c['kd_customer']) ?></span></td>
                                    <td class="font-weight-bold"><?= escAttr($c['nama_customer']) ?></td>
                                    <td><?= escAttr($c['nama_kios'] ?? '-') ?></td>
                                    <td><?= escAttr($c['regional'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-primary font-weight-bold" onclick="pilihCustomer('<?= escAttr($c['kd_customer']) ?>', '<?= escAttr($c['nama_customer']) ?>')">
                                            <i class="fas fa-check"></i> Pilih
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PILIH BARANG & STOK -->
<div class="modal fade" id="modalBarang" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header card-loby-header py-2">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-boxes mr-2"></i> Pilih Barang dari Stok Gudang</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-2">
                <div class="alert alert-info py-1 px-3 small mb-2">
                    <i class="fas fa-info-circle mr-1"></i> Menampilkan stok tersedia di gudang yang dipilih. Klik tombol <strong>+ Tambah</strong> untuk memasukkan barang ke daftar transaksi Loby.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered" id="tabelLookupStock" style="width:100%">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>No. Lot</th>
                                <th>Exp. Date</th>
                                <th>Stok Tersedia</th>
                                <th>Satuan</th>
                                <th>Estimasi HPP</th>
                                <th style="width:80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-stock-lookup">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let rowIndex = <?= $is_edit ? count($details) : 0 ?>;

function bukaModalCustomer() {
    $('#modalCustomer').modal('show');
}

function pilihCustomer(kd, nama) {
    $('#customer_id').val(kd);
    $('#customer_name').val(nama);
    $('#customer_display').val(nama + ' (' + kd + ')');
    $('#modalCustomer').modal('hide');
}

function bukaModalBarang() {
    let gudangId = $('#gudang_id').val();
    if (!gudangId) {
        alert('Pilih Lokasi Gudang Stok terlebih dahulu!');
        $('#gudang_id').focus();
        return;
    }

    $('#tbody-stock-lookup').html('<tr><td colspan="8" class="text-center py-3"><i class="fas fa-spinner fa-spin mr-1"></i> Memuat data stok gudang...</td></tr>');
    $('#modalBarang').modal('show');

    $.ajax({
        url: '<?= base_url("sales_order_loby/get_stock") ?>',
        type: 'GET',
        data: { gudang_id: gudangId },
        dataType: 'json',
        success: function(res) {
            let html = '';
            if (res.status === 'success' && res.data && res.data.length > 0) {
                res.data.forEach(function(item) {
                    let avStock = parseFloat(item.available_stock || 0);
                    let hppVal  = parseFloat(item.hpp || 0);
                    let rowData = JSON.stringify(item).replace(/"/g, '&quot;');
                    html += `
                        <tr>
                            <td><span class="badge badge-secondary">${item.kd_barang}</span></td>
                            <td class="font-weight-bold">${item.nama_barang || '-'}</td>
                            <td class="text-center">${item.no_lot || '-'}</td>
                            <td class="text-center">${item.exp_date || '-'}</td>
                            <td class="text-right font-weight-bold text-success">${avStock.toLocaleString('id-ID')}</td>
                            <td class="text-center">${item.satuan || 'PCS'}</td>
                            <td class="text-right">Rp ${hppVal.toLocaleString('id-ID')}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-xs btn-primary font-weight-bold" onclick="tambahBarangKeTabel(${rowData})">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada stok barang tersedia di gudang ini.</td></tr>';
            }
            $('#tbody-stock-lookup').html(html);
        },
        error: function() {
            $('#tbody-stock-lookup').html('<tr><td colspan="8" class="text-center text-danger py-3">Gagal memuat stok barang.</td></tr>');
        }
    });
}

function tambahBarangKeTabel(item) {
    let kd_barang   = item.kd_barang;
    let nama_barang = item.nama_barang || '';
    let no_lot      = item.no_lot || '';
    let exp_date    = item.exp_date || '';
    let satuan      = item.satuan || 'PCS';
    let hrg_pokok   = parseFloat(item.hpp || 0);
    let berat_gram  = parseFloat(item.berat_gram || 0);
    let kubikasi_m3 = parseFloat(item.kubikasi_m3 || 0);
    let isi_per_box = parseInt(item.isi_per_box || 1);
    let avStock     = parseFloat(item.available_stock || 1);

    // Default harga jual = hpp * 1.1 atau hpp jika 0
    let defaultHarga = hrg_pokok > 0 ? Math.round(hrg_pokok * 1.1) : 0;

    let tr = `
        <tr id="row-${rowIndex}" data-kd="${kd_barang}">
            <td class="text-center row-number"></td>
            <td>
                <span class="font-weight-bold text-primary">${kd_barang}</span>
                <input type="hidden" name="items[${rowIndex}][kd_barang]" value="${kd_barang}">
                <input type="hidden" name="items[${rowIndex}][nama_barang]" value="${nama_barang}">
                <input type="hidden" name="items[${rowIndex}][hrg_pokok]" value="${hrg_pokok}">
                <input type="hidden" name="items[${rowIndex}][berat_gram]" value="${berat_gram}">
                <input type="hidden" name="items[${rowIndex}][kubikasi_m3]" value="${kubikasi_m3}">
                <input type="hidden" name="items[${rowIndex}][isi_per_box]" value="${isi_per_box}">
            </td>
            <td>${nama_barang}</td>
            <td>
                <div><small class="badge badge-light border">Lot: ${no_lot || '-'}</small></div>
                <div><small class="text-muted">Exp: ${exp_date || '-'}</small></div>
                <input type="hidden" name="items[${rowIndex}][no_lot]" value="${no_lot}">
                <input type="hidden" name="items[${rowIndex}][expired_date]" value="${exp_date}">
            </td>
            <td>
                <input type="number" step="any" min="0.001" max="${avStock}" class="form-control form-control-sm text-right item-qty" name="items[${rowIndex}][qty]" value="1" onchange="hitungSubtotal(${rowIndex})" required>
                <small class="text-muted d-block text-right">Maks: ${avStock}</small>
            </td>
            <td class="text-center">
                <span>${satuan}</span>
                <input type="hidden" name="items[${rowIndex}][satuan]" value="${satuan}">
            </td>
            <td>
                <input type="number" step="any" min="0" class="form-control form-control-sm text-right item-harga" name="items[${rowIndex}][hrg_satuan]" value="${defaultHarga}" onchange="hitungSubtotal(${rowIndex})" required>
            </td>
            <td>
                <input type="number" step="any" min="0" max="100" class="form-control form-control-sm text-right item-disc" name="items[${rowIndex}][disc]" value="0" onchange="hitungSubtotal(${rowIndex})">
                <input type="hidden" name="items[${rowIndex}][pajak]" value="0">
            </td>
            <td class="text-right font-weight-bold item-subtotal">Rp 0</td>
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-danger" onclick="hapusBaris(${rowIndex})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `;

    $('#tbody-items').append(tr);
    hitungSubtotal(rowIndex);
    rowIndex++;
    updateRowNumbers();
    $('#modalBarang').modal('hide');
}

function hapusBaris(idx) {
    $('#row-' + idx).remove();
    updateRowNumbers();
    hitungGrandTotal();
}

function updateRowNumbers() {
    $('#tbody-items tr').each(function(i) {
        $(this).find('.row-number').text(i + 1);
    });
}

function hitungSubtotal(idx) {
    let row = $('#row-' + idx);
    let qty   = parseFloat(row.find('.item-qty').val() || 0);
    let harga = parseFloat(row.find('.item-harga').val() || 0);
    let disc  = parseFloat(row.find('.item-disc').val() || 0);

    let subtotalBefore = qty * harga;
    let subtotalAfter  = subtotalBefore * (1 - (disc / 100));

    row.find('.item-subtotal').text('Rp ' + Math.round(subtotalAfter).toLocaleString('id-ID'));
    hitungGrandTotal();
}

function hitungGrandTotal() {
    let grandTotal = 0;
    $('#tbody-items tr').each(function() {
        let qty   = parseFloat($(this).find('.item-qty').val() || 0);
        let harga = parseFloat($(this).find('.item-harga').val() || 0);
        let disc  = parseFloat($(this).find('.item-disc').val() || 0);
        let subtotalAfter = (qty * harga) * (1 - (disc / 100));
        grandTotal += subtotalAfter;
    });

    $('#display-grand-total').text('Rp ' + Math.round(grandTotal).toLocaleString('id-ID'));
}

$(document).ready(function() {
    $('#tabelLookupCustomer').DataTable({
        "pageLength": 10
    });
    hitungGrandTotal();
});
</script>
</body>
