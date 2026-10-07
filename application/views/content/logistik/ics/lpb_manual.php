<style>
    .lpb-manual-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: space-between;
        align-items: center;
    }

    .lpb-manual-table th,
    .lpb-manual-table td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .lpb-manual-table .select2-container {
        min-width: 280px;
    }

    .manual-barang-group {
        min-width: 260px;
    }

    .manual-display-barang {
        background-color: #ffffff !important;
        cursor: pointer;
    }

    .manual-display-barang:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .modal-table-barang tbody tr {
        cursor: pointer;
        transition: background-color 0.15s ease-in-out;
    }

    .modal-table-barang tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    .lpb-manual-row-action {
        width: 38px;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="Karisma" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <div class="lpb-manual-toolbar">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-clipboard-list mr-2"></i> Input LPB Manual
                                </h3>
                                <div>
                                    <a href="<?= base_url('ics/icspo') ?>" class="btn btn-light btn-sm">
                                        <i class="fas fa-arrow-left mr-1"></i> Data LPB
                                    </a>
                                    <a href="<?= base_url('ics/lpb_report?source=manual') ?>" class="btn btn-light btn-sm">
                                        <i class="fas fa-chart-bar mr-1"></i> Laporan
                                    </a>
                                </div>
                            </div>
                        </div>
                        <form id="lpbManualForm" autocomplete="off">
                            <div class="card-body">
                                <div class="alert d-none" id="lpbManualAlert"></div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Ref Manual</label>
                                            <input type="text" class="form-control" name="manual_ref_no" value="<?= htmlspecialchars($manual_ref ?? '') ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tanggal LPB <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="tgl_lpb" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Jenis LPB <span class="text-danger">*</span></label>
                                            <select class="form-control" name="jenis_lpb" required>
                                                <?php foreach (($lpb_type_options ?? []) as $key => $option) : ?>
                                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($option['label'] ?? $key) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Gudang <span class="text-danger">*</span></label>
                                            <select class="form-control" name="gudang_id" required>
                                                <option value="">Pilih Gudang</option>
                                                <?php foreach (($list_gudang ?? []) as $gudang) : ?>
                                                    <option value="<?= htmlspecialchars($gudang['id_gudang']) ?>"><?= htmlspecialchars($gudang['nama_gudang']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-truck text-primary mr-1"></i> Supplier / Pemasok</label>
                                            <select class="form-control select2" name="kd_suplier" id="kd_suplier_select" style="width: 100%;">
                                                <option value="">-- Pilih Supplier (Opsional) --</option>
                                                <?php foreach (($list_suplier ?? []) as $sup) : ?>
                                                    <option value="<?= htmlspecialchars($sup['kd_suplier']) ?>" data-nama="<?= htmlspecialchars($sup['nama_suplier']) ?>">
                                                        <?= htmlspecialchars($sup['kd_suplier']) ?> - <?= htmlspecialchars($sup['nama_suplier']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="nama_suplier" id="nama_suplier_hidden" value="">
                                            <small class="text-muted">Pilih supplier yang mengirimkan barang fisik ini</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>No SJ</label>
                                            <input type="text" class="form-control" name="nosj" placeholder="-">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>No Invoice</label>
                                            <input type="text" class="form-control" name="no_invoice" placeholder="-">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <input type="text" class="form-control" name="keterangan" placeholder="Catatan input manual">
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($is_admlpb_user)) : ?> 
                                    <div class="alert alert-info py-2 px-3 mb-3 border">
                                        <i class="fas fa-info-circle mr-1"></i> <strong>Mode Admin LPB (Logistik):</strong> Kolom Harga Satuan disembunyikan karena pengisian harga merupakan wewenang Purchasing. LPB yang disimpan akan berstatus <strong>DRAFT</strong> (stok fisik masuk gudang) dan akan difinalisasi/diposting oleh Purchasing.
                                    </div>
                                <?php endif; ?>

                                <div class="lpb-manual-toolbar mb-2">
                                    <strong>Detail Barang</strong>
                                    <button type="button" class="btn btn-success btn-sm" id="btnAddManualRow">
                                        <i class="fas fa-plus mr-1"></i> Tambah Barang
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover lpb-manual-table" id="lpbManualTable">
                                        <thead class="thead-dark text-center">
                                            <tr>
                                                <th>Barang</th>
                                                <th>Satuan</th>
                                                <th>Qty</th>
                                                <th>No Lot</th>
                                                <th>Expired</th>
                                                <?php if (empty($is_admlpb_user)) : ?>
                                                    <th>Harga Satuan</th>
                                                <?php endif; ?>
                                                <th class="lpb-manual-row-action">#</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-end" style="gap:8px;">
                                <a href="<?= base_url('ics/icspo') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times mr-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary" id="btnSaveManualLpb">
                                    <i class="fas fa-save mr-1"></i> <?= !empty($is_admlpb_user) ? 'Simpan Draft LPB Manual' : 'Simpan LPB Manual' ?>
                                </button>
                            </div>
                        </form>
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

    <!-- Modal Pencarian Barang -->
    <div class="modal fade" id="modalCariBarangManual" tabindex="-1" role="dialog" aria-labelledby="modalCariBarangManualLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalCariBarangManualLabel">
                        <i class="fas fa-search mr-2"></i> Cari & Pilih Barang
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">
                            <i class="fas fa-barcode mr-1"></i> Kata Kunci Pencarian (Kode / Nama Barang):
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" class="form-control" id="searchModalBarangManual" placeholder="Ketik kode atau nama barang..." autocomplete="off">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="btnClearSearchModalBarang" title="Bersihkan pencarian">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Ketik kata kunci untuk menyaring daftar barang, lalu klik tombol <strong>Pilih</strong> atau klik baris barang yang diinginkan.
                        </small>
                    </div>

                    <div id="loadingModalBarang" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Memuat...</span>
                        </div>
                        <div class="text-muted mt-2 small">Sedang mencari data barang...</div>
                    </div>

                    <div class="table-responsive" style="max-height: 420px;" id="wrapperTabelModalBarang">
                        <table class="table table-bordered table-hover modal-table-barang mb-0" id="tabelModalBarangManual">
                            <thead class="thead-light sticky-top">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No</th>
                                    <th style="width: 140px;">Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th style="width: 90px;" class="text-center">Satuan</th>
                                    <th style="width: 90px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyModalBarangManual">
                                <!-- Data barang via AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <div id="emptyModalBarang" class="text-center py-5 d-none text-muted">
                        <i class="fas fa-box-open fa-3x mb-2 text-secondary"></i>
                        <p class="mb-0 font-weight-bold">Tidak ada barang yang ditemukan.</p>
                        <small>Coba gunakan kata kunci pencarian yang lain.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <span class="text-muted small mr-auto" id="infoTotalBarangModal"></span>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            var rowIndex = 0;
            var isAdmlpbUser = <?= !empty($is_admlpb_user) ? 'true' : 'false' ?>;
            var saveButtonLabel = isAdmlpbUser
                ? '<i class="fas fa-save mr-1"></i> Simpan Draft LPB Manual'
                : '<i class="fas fa-save mr-1"></i> Simpan LPB Manual';

            // Variabel untuk melacak baris yang sedang aktif memilih barang
            var $activeRowTarget = null;
            var searchTimeout = null;
            var cachedResults = [];

            // Inisialisasi Select2 Supplier
            if ($.fn.select2) {
                $('#kd_suplier_select').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: '-- Pilih Supplier (Opsional) --',
                    allowClear: true
                });
            }

            $('#kd_suplier_select').on('change', function() {
                var selectedNama = $(this).find(':selected').data('nama') || '';
                $('#nama_suplier_hidden').val(selectedNama);
            });

            function showAlert(type, message) {
                $('#lpbManualAlert')
                    .removeClass('d-none alert-success alert-danger alert-warning')
                    .addClass('alert-' + type)
                    .text(message);
            }

            function addRow() {
                rowIndex++;
                var hargaSatuanCell = isAdmlpbUser
                    ? '<input type="hidden" name="harga_satuan[]" value="0">'
                    : '<td><input type="number" class="form-control" name="harga_satuan[]" min="0" step="0.0001" value="0"></td>';

                var row = '' +
                    '<tr>' +
                    '<td>' +
                        '<div class="input-group manual-barang-group">' +
                            '<input type="hidden" class="manual-kd-barang" name="kd_barang[]" required>' +
                            '<input type="text" class="form-control manual-display-barang bg-white" placeholder="Klik untuk cari barang..." readonly style="cursor: pointer;" required title="Klik untuk memilih barang">' +
                            '<div class="input-group-append">' +
                                '<button type="button" class="btn btn-outline-primary btn-browse-barang" title="Cari Barang">' +
                                    '<i class="fas fa-search"></i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                    '<td><input type="text" class="form-control manual-satuan" name="satuan[]" readonly></td>' +
                    '<td><input type="number" class="form-control" name="qty_diterima[]" min="0.001" step="0.001" required></td>' +
                    '<td><input type="text" class="form-control" name="no_lot[]" required></td>' +
                    '<td><input type="date" class="form-control" name="expired_date[]" required></td>' +
                    hargaSatuanCell +
                    '<td class="text-center"><button type="button" class="btn btn-danger btn-sm btnRemoveManualRow" title="Hapus baris"><i class="fas fa-trash"></i></button></td>' +
                    '</tr>';
                var $row = $(row);
                $('#lpbManualTable tbody').append($row);
            }

            // Fungsi pencarian barang di modal
            function fetchBarangModal(term) {
                $('#loadingModalBarang').removeClass('d-none');
                $('#wrapperTabelModalBarang').addClass('d-none');
                $('#emptyModalBarang').addClass('d-none');

                $.ajax({
                    url: '<?= base_url('ics/lpb_manual/barang') ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        q: term || '',
                        limit: 50
                    },
                    success: function(response) {
                        $('#loadingModalBarang').addClass('d-none');
                        var results = response && response.results ? response.results : [];
                        cachedResults = results;
                        renderTabelModalBarang(results);
                    },
                    error: function() {
                        $('#loadingModalBarang').addClass('d-none');
                        $('#wrapperTabelModalBarang').addClass('d-none');
                        $('#emptyModalBarang').removeClass('d-none').find('p').text('Gagal memuat data barang.');
                    }
                });
            }

            // Render daftar barang ke modal
            function renderTabelModalBarang(items) {
                var $tbody = $('#tbodyModalBarangManual');
                $tbody.empty();

                if (!items || items.length === 0) {
                    $('#wrapperTabelModalBarang').addClass('d-none');
                    $('#emptyModalBarang').removeClass('d-none').find('p').text('Tidak ada barang yang ditemukan.');
                    $('#infoTotalBarangModal').text('0 barang ditemukan');
                    return;
                }

                $('#emptyModalBarang').addClass('d-none');
                $('#wrapperTabelModalBarang').removeClass('d-none');
                $('#infoTotalBarangModal').text(items.length + ' barang ditampilkan');

                $.each(items, function(index, item) {
                    var tr = $('<tr></tr>')
                        .attr('data-index', index)
                        .css('cursor', 'pointer')
                        .append('<td class="text-center">' + (index + 1) + '</td>')
                        .append('<td><span class="badge badge-secondary py-1 px-2 font-weight-bold">' + escapeHtml(item.kode_barang) + '</span></td>')
                        .append('<td><strong>' + escapeHtml(item.nama_barang) + '</strong></td>')
                        .append('<td class="text-center"><span class="badge badge-light border">' + escapeHtml(item.satuan || 'PCS') + '</span></td>')
                        .append('<td class="text-center"><button type="button" class="btn btn-primary btn-sm btn-select-barang-modal"><i class="fas fa-check mr-1"></i> Pilih</button></td>');

                    $tbody.append(tr);
                });
            }

            function escapeHtml(text) {
                if (!text) return '';
                return $('<div>').text(text).html();
            }

            // Memilih barang dan memasukkannya ke baris yang sedang aktif
            function selectBarangToRow(item) {
                if (!$activeRowTarget || !item) return;

                $activeRowTarget.find('.manual-kd-barang').val(item.kode_barang);
                $activeRowTarget.find('.manual-display-barang').val(item.kode_barang + ' - ' + item.nama_barang);
                $activeRowTarget.find('.manual-satuan').val(item.satuan || 'PCS');

                // Beri efek highlight sekejap pada baris tabel
                $activeRowTarget.addClass('table-primary');
                setTimeout(function() {
                    $activeRowTarget.removeClass('table-primary');
                }, 600);

                $('#modalCariBarangManual').modal('hide');

                // Fokuskan otomatis ke input Qty
                setTimeout(function() {
                    $activeRowTarget.find('input[name="qty_diterima[]"]').focus().select();
                }, 300);
            }

            // Event saat kolom barang di tabel diklik (baik input text maupun tombol search)
            $('#lpbManualTable').on('click', '.manual-display-barang, .btn-browse-barang', function() {
                $activeRowTarget = $(this).closest('tr');
                $('#modalCariBarangManual').modal('show');
            });

            // Event saat modal terbuka: auto-focus dan load data awal
            $('#modalCariBarangManual').on('shown.bs.modal', function() {
                var $searchInput = $('#searchModalBarangManual');
                $searchInput.focus().select();

                // Jika data belum pernah diambil atau tabel kosong, fetch data
                if (cachedResults.length === 0) {
                    fetchBarangModal('');
                }
            });

            // Event input search dengan debouncing
            $('#searchModalBarangManual').on('input', function() {
                var query = $(this).val();
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    fetchBarangModal(query);
                }, 250);
            });

            // Tombol bersihkan search
            $('#btnClearSearchModalBarang').on('click', function() {
                $('#searchModalBarangManual').val('').focus();
                fetchBarangModal('');
            });

            // Event klik baris pada tabel modal atau tombol pilih
            $('#tbodyModalBarangManual').on('click', 'tr', function(e) {
                var index = $(this).data('index');
                if (typeof index !== 'undefined' && cachedResults[index]) {
                    selectBarangToRow(cachedResults[index]);
                }
            });

            $('#btnAddManualRow').on('click', addRow);
            $('#lpbManualTable').on('click', '.btnRemoveManualRow', function() {
                if ($('#lpbManualTable tbody tr').length <= 1) {
                    showAlert('warning', 'Minimal harus ada 1 baris barang.');
                    return;
                }
                $(this).closest('tr').remove();
            });

            $('#lpbManualForm').on('submit', function(e) {
                e.preventDefault();
                var $button = $('#btnSaveManualLpb');
                $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Menyimpan...');

                $.ajax({
                    url: '<?= base_url('ics/lpb_manual/store') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            showAlert('success', response.message || 'LPB Manual berhasil disimpan.');
                            setTimeout(function() {
                                window.location.href = response.redirect_url || '<?= base_url('ics/lpb_report?source=manual') ?>';
                            }, 800);
                            return;
                        }
                        showAlert('danger', response.message || 'LPB Manual gagal disimpan.');
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'LPB Manual gagal disimpan.';
                        showAlert('danger', message);
                    },
                    complete: function() {
                        $button.prop('disabled', false).html(saveButtonLabel);
                    }
                });
            });

            // Tambahkan baris pertama saat halaman dimuat
            addRow();
        });
    </script>
</body>
