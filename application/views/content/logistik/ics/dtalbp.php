<!-- view/content/logistik/ics/datalpb.php -->
<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Logo" height="150" width="300">
    </div>

    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <div class="content-header">
            <section class="content">

                <?php if ($this->session->flashdata('success')) : ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-1"></i> <?= $this->session->flashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-1"></i> <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                <?php endif; ?>

                <div class="row mb-2">
                    <div class="col-auto">
                        <a href="<?= base_url('ics/ics_diffrent') ?>" class="btn btn-md btn-primary mb-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('po_selesai') ?>" class="btn btn-md btn-success mb-3">
                            <i class="fas fa-check-double"></i> PO Selesai
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('riwayat_barang_masuk') ?>" class="btn btn-md btn-info mb-3">
                            <i class="fas fa-history"></i> Riwayat Barang Masuk
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-truck-loading mr-2"></i> Data PO - Barang Belum Terpenuhi</h3>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">

                            <div class="row mb-2">
                                <div class="col-2">
                                    <button class="btn btn-success mb-3 btn-block" data-toggle="modal" data-target="#modalImportCSV">
                                        <i class="fas fa-file-csv"></i> Import CSV
                                    </button>
                                </div>
                                <div class="col-2">
                                    <a class="btn btn-secondary mb-3 btn-block" href="<?= base_url('data_lpb_zahir') ?>">
                                        <i class="fas fa-file-csv"></i> Data LPB
                                    </a>
                                </div>
                            </div>

                            <form action="<?= base_url('data_lpb_zahir') ?>" method="post">
                                <div class="row mb-3">
                                    <div class="col-2">
                                        <input type="date" class="form-control" name="date1" id="name1"
                                               value="<?= htmlspecialchars($this->input->post('date1') ?? '') ?>">
                                    </div>
                                    <div class="col-2">
                                        <input type="date" class="form-control" name="date2" id="name2"
                                               value="<?= htmlspecialchars($this->input->post('date2') ?? '') ?>">
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-success btn-block">
                                            <i class="fas fa-search"></i> Tampil
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <table class="table table-bordered" id="tabelPO">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No PO</th>
                                        <th>Tanggal</th>
                                        <th>Kode Supplier</th>
                                        <th>Nama Supplier</th>
                                        <th class="text-center">Jumlah Barang</th>
                                        <th class="text-center">Barang Masuk</th>
                                        <th class="text-center" style="width:80px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $ada_data = false;
                                    if (!empty($lpb)) :
                                        foreach ($lpb as $row) :
                                            $jumlah_barang       = (int)($row['jumlah_barang']       ?? 0);
                                            $jumlah_barang_masuk = (int)($row['jumlah_barang_masuk'] ?? 0);
                                            $sisa                = $jumlah_barang - $jumlah_barang_masuk;

                                            // Tampilkan hanya PO yang belum semua item terpenuhi
                                            if ($sisa <= 0) continue;
                                            $ada_data = true;
                                    ?>
                                        <tr class="<?= $jumlah_barang_masuk > 0 ? 'table-warning' : '' ?>">
                                            <td><?= htmlspecialchars($row['no_po']         ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['tgl_transaksi'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['kd_suplier']    ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['nama_suplier']  ?? '-') ?></td>
                                            <td class="text-center font-weight-bold"><?= $jumlah_barang ?></td>
                                            <td class="text-center text-success font-weight-bold"><?= $jumlah_barang_masuk ?></td>
                                            <td class="text-center">
                                                <button
                                                    class="btn btn-sm btn-success btn-input-qty"
                                                    title="Input Penerimaan"
                                                    data-no-po="<?= htmlspecialchars($row['no_po']        ?? '') ?>"
                                                    data-kd-po="<?= htmlspecialchars($row['kd_po']        ?? '') ?>"
                                                    data-kd-suplier="<?= htmlspecialchars($row['kd_suplier']   ?? '') ?>"  {{-- TAMBAH INI --}}
                                                    data-nama-suplier="<?= htmlspecialchars($row['nama_suplier'] ?? '') ?>"
                                                    data-toggle="modal"
                                                    data-target="#modalInputQty">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php 
                                        endforeach;
                                    endif;
                                    if (!$ada_data) :
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-success">
                                                <i class="fas fa-check-circle mr-1"></i> Semua barang PO sudah terpenuhi
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

            </section>
        </div>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0</div>
    </footer>
    <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<!-- ================================================================
     MODAL INPUT PENERIMAAN BARANG (PER NO_PO, LIST BARANG DINAMIS)
================================================================ -->
<div class="modal fade" id="modalInputQty" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle mr-2"></i> Input Penerimaan Barang
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">

                <!-- Info PO -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="font-weight-bold">No PO</label>
                        <input type="text" class="form-control-plaintext font-weight-bold text-primary" id="modal_no_po" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="font-weight-bold">Supplier</label>
                        <input type="text" class="form-control-plaintext text-muted" id="modal_nama_suplier" readonly>
                    </div>
                </div>
                <hr class="my-2">

                <!-- Loading indicator -->
                <div id="loadingBarang" class="text-center py-3" style="display:none;">
                    <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
                    <p class="mt-2 text-muted">Memuat data barang...</p>
                </div>

                <!-- Form input per barang -->
                <form id="formInputQty" action="<?= base_url('save_qty_diterima') ?>" method="post">
                    <input type="hidden" name="no_po" id="form_no_po">
                    <input type="hidden" name="kd_po" id="form_kd_po">

                    <div id="wrapperBarang">
                        <!-- Diisi dinamis via AJAX -->
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
                <button type="submit" form="formInputQty" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Simpan Semua
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    // ── DataTables ────────────────────────────────────────────────
    $('#tabelPO').DataTable({
        responsive  : true,
        autoWidth   : false,
        pageLength  : 25,
        order       : [[0, 'desc']],
        columnDefs  : [{ orderable: false, targets: -1 }],
        language: {
            search      : "Cari:",
            lengthMenu  : "Tampilkan _MENU_ data",
            info        : "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            zeroRecords : "Tidak ada data ditemukan",
            emptyTable  : "Tidak ada data tersedia",
            paginate    : { first: "Pertama", last: "Terakhir", next: "Berikutnya", previous: "Sebelumnya" }
        }
    });

    // ── Buka modal: load daftar barang via AJAX ───────────────────
    $('.btn-input-qty').on('click', function () {
        var noPo        = $(this).data('no-po');
        var kdPo        = $(this).data('kd-po');
        var kdSuplier   = $(this).data('kd-suplier');   // TAMBAH INI
        var namaSuplier = $(this).data('nama-suplier');

        $('#modal_no_po').val(noPo);
        $('#modal_nama_suplier').val(namaSuplier);
        $('#form_no_po').val(noPo);
        $('#form_kd_po').val(kdPo);

        $('#wrapperBarang').html('');
        $('#loadingBarang').show();

        // Kirim no_po + kd_suplier ke endpoint
        $.getJSON('<?= base_url('get_barang_by_po') ?>', { no_po: noPo, kd_suplier: kdSuplier }, function (data) {
            $('#loadingBarang').hide();

            if (!data || data.length === 0) {
                $('#wrapperBarang').html(
                    '<div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> Semua barang dalam PO ini sudah terpenuhi.</div>'
                );
                return;
            }

            var html = buildFormBarang(data);
            $('#wrapperBarang').html(html);

            // Re-init tambah/hapus baris per card barang
            initCardEvents();
        }).fail(function () {
            $('#loadingBarang').hide();
            $('#wrapperBarang').html(
                '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i> Gagal memuat data barang. Silakan coba lagi.</div>'
            );
        });
    });

    // ── Build HTML form per barang ────────────────────────────────
    function buildFormBarang(items) {
        var satuanOptions = '';
        <?php foreach ($list_satuan as $s) : ?>
            satuanOptions += '<option value="<?= $s['nm_satuan'] ?>"><?= $s['nm_satuan'] ?></option>';
        <?php endforeach; ?>

        var html = '';
        $.each(items, function (idx, item) {
            html += `
            <div class="card card-outline card-success mb-3" data-barang-idx="${idx}">
                <div class="card-header py-2">
                    <strong class="text-success">${item.kd_barang}</strong>
                    &mdash; ${item.nama_barang}
                    <span class="badge badge-warning ml-2">Sisa: ${item.sisa} ${item.satuan}</span>
                    <input type="hidden" name="rows[${idx}][kd_barang]" value="${item.kd_barang}">
                </div>
                <div class="card-body py-2">
                    <table class="table table-bordered table-sm mb-1 tabel-sub-baris" data-idx="${idx}">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:150px;">Qty Diterima <span class="text-danger">*</span></th>
                                <th style="width:130px;">Satuan <span class="text-danger">*</span></th>
                                <th style="width:160px;">No Lot</th>
                                <th style="width:160px;">Exp Date</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody class="body-sub-baris">
                            <tr>
                                <td><input type="number" class="form-control form-control-sm input-qty"
                                           name="rows[${idx}][sub][0][qty_diterima]" min="1"
                                           max="${item.sisa}" required></td>
                                <td>
                                    <select class="form-control form-control-sm" name="rows[${idx}][sub][0][satuan]" required>
                                        <option value="">-- Pilih --</option>${satuanOptions}
                                    </select>
                                </td>
                                <td><input type="text" class="form-control form-control-sm" name="rows[${idx}][sub][0][no_lot]" placeholder="No Lot"></td>
                                <td><input type="date" class="form-control form-control-sm" name="rows[${idx}][sub][0][exp_date]"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger btn-hapus-sub" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-xs btn-info btn-tambah-sub"
                            data-idx="${idx}" data-sisa="${item.sisa}" data-satuan-opts="${escHtml(satuanOptions)}">
                        <i class="fas fa-plus"></i> Tambah Baris
                    </button>
                </div>
            </div>`;
        });
        return html;
    }

    function escHtml(str) { return $('<div>').text(str).html(); }

    // ── Events dalam card barang (delegasi ke #wrapperBarang) ─────
    function initCardEvents() {
        // sudah pakai delegasi di bawah, tidak perlu bind ulang
    }

    $('#wrapperBarang')
        .on('click', '.btn-tambah-sub', function () {
            var idx      = $(this).data('idx');
            var sisa     = $(this).data('sisa');
            var tbody    = $(this).closest('.card-body').find('.body-sub-baris');
            var subIdx   = tbody.find('tr').length;
            var satuanOpts = $(this).data('satuan-opts');

            var tr = `
                <tr>
                    <td><input type="number" class="form-control form-control-sm input-qty"
                               name="rows[${idx}][sub][${subIdx}][qty_diterima]" min="1" max="${sisa}" required></td>
                    <td>
                        <select class="form-control form-control-sm" name="rows[${idx}][sub][${subIdx}][satuan]" required>
                            <option value="">-- Pilih --</option>${satuanOpts}
                        </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm" name="rows[${idx}][sub][${subIdx}][no_lot]" placeholder="No Lot"></td>
                    <td><input type="date" class="form-control form-control-sm" name="rows[${idx}][sub][${subIdx}][exp_date]"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-hapus-sub">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            tbody.append(tr);
            updateHapusSub(tbody);
        })

        .on('click', '.btn-hapus-sub', function () {
            var tbody = $(this).closest('tbody');
            $(this).closest('tr').remove();
            updateHapusSub(tbody);
            reindexSubBaris(tbody);
        });

    function updateHapusSub(tbody) {
        var btns = tbody.find('.btn-hapus-sub');
        btns.prop('disabled', btns.length === 1);
    }

    function reindexSubBaris(tbody) {
        tbody.find('tr').each(function (si) {
            $(this).find('input, select').each(function () {
                var name = $(this).attr('name');
                if (name) {
                    // ganti sub index: rows[X][sub][Y] → rows[X][sub][si]
                    $(this).attr('name', name.replace(/\[sub\]\[\d+\]/, '[sub][' + si + ']'));
                }
            });
        });
    }

    // ── Validasi submit: total per barang tidak boleh > sisa ──────
    $('#formInputQty').on('submit', function (e) {
        var valid = true;

        $('.card[data-barang-idx]').each(function () {
            var card   = $(this);
            var sisa   = parseInt(card.find('.badge-warning').text().replace(/[^0-9]/g, '')) || 0;
            var total  = 0;

            card.find('.input-qty').each(function () {
                total += parseInt($(this).val()) || 0;
            });

            if (total > sisa) {
                valid = false;
                var namaBarang = card.find('.card-header strong').text();
                alert('Total qty untuk barang "' + namaBarang + '" (' + total + ') melebihi sisa (' + sisa + '). Silakan periksa kembali.');
                return false; // break each
            }
        });

        if (!valid) { e.preventDefault(); return false; }
    });

    // ── Auto-hide alert ───────────────────────────────────────────
    setTimeout(function () { $('.alert').fadeOut('slow'); }, 4000);
});
</script>
</body>