<style>
    .mutasi-toolbar .form-control {
        min-width: 180px;
    }

    .mutasi-table tbody tr[data-id] {
        cursor: pointer;
    }

    .mutasi-table tbody tr[data-id]:hover {
        background: #f7fbff;
    }

    .mutasi-table tbody tr.active {
        background: #e8f2ff;
    }

    .mutasi-add-row td {
        height: 54px;
        vertical-align: middle !important;
        background: #fbfcfe;
    }

    .barang-row.active {
        background: #e8f2ff;
    }

    .qty-plot {
        max-width: 110px;
    }

    .barang-pagination {
        gap: .5rem;
    }

    #cardLotMutasi {
        display: none;
    }
</style>

<body class="hold-transition sidebar-mini sidebar-collapse">
    <div class="wrapper">

        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?php echo base_url('assets/images/Karisma.png') ?>" alt="AdminLTELogo" height="150" width="300">
        </div>

        <?php $this->load->view('partial/main/navbar') ?>
        <?php $this->load->view('partial/main/sidebar') ?>

        <div class="content-wrapper">
            <div class="content-header">
                <section class="content">

                    <div class="row mb-2">
                        <div class="col-auto">
                            <a href="<?= base_url('ics/mutasi_barang') ?>" class="btn btn-md btn-primary">Dashboard Mutasi</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <div class="form-group mb-md-0">
                                        <label>No Ref</label>
                                        <input class="form-control" type="text" id="nofresnsi" name="nofresnsi" value="<?= $ref_mutasi ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-md-0">
                                        <label>Tanggal</label>
                                        <input class="form-control" type="date" id="tgl_transaksi" name="tgl_transaksi" value="<?= $tanggal ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-md-0">
                                        <label>Dari Gudang</label>
                                        <select name="fromgdg" id="fromgdg" class="form-control">
                                            <?php foreach ($gudang as $gdg) : ?>
                                                <option value="<?= $gdg->id_gudang ?>"><?= $gdg->nama_gudang ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-md-0">
                                        <label>Ke Gudang</label>
                                        <select name="tujuangdg" id="tujuangdg" class="form-control">
                                            <?php foreach ($gudang as $gdg) : ?>
                                                <option value="<?= $gdg->id_gudang ?>"><?= $gdg->nama_gudang ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group mb-0">
                                        <label>Keterangan</label>
                                        <input class="form-control" type="text" id="keterangan_mutasi" name="keterangan_mutasi" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h3 class="card-title mb-0">List Barang Mutasi</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <button type="button" class="btn btn-primary" id="btnOpenBarangMutasi">
                                    <i class="fas fa-plus"></i> Tambah Barang
                                </button>
                                <small class="text-muted ml-2" id="fromGdgLockInfo"></small>
                            </div>
                            <div class="table-responsive">
                                <table id="input_tmp_mutasi" class="table table-bordered table-striped mutasi-table">
                                    <thead>
                                        <tr>
                                            <th>Kode Barang</th>
                                            <th>Nama Barang</th>
                                            <th>No Lot</th>
                                            <th>Expired Date</th>
                                            <th>Qty</th>
                                            <th>Satuan</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <a href="#" class="btn btn-success btn-block" id="rekammutasi">Rekam</a>
                        </div>
                    </div>

                    <div class="card" id="cardLotMutasi">
                        <div class="card-header d-flex align-items-center">
                            <h3 class="card-title mb-0">Data Lot Barang</h3>
                            <span class="badge badge-primary ml-2" id="selectedBarangKode"></span>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="font-weight-bold" id="selectedBarangNama">-</div>
                                <small class="text-muted">Pilih tombol plus pada lot yang akan diplot ke barang mutasi.</small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="tableLotMutasi">
                                    <thead>
                                        <tr>
                                            <th>No Lot</th>
                                            <th>Expired Date</th>
                                            <th>Qty</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Pilih barang pada list mutasi barang</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </section>
            </div>
        </div>

        <div class="modal fade" id="modalBarangMutasi" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header align-items-center">
                        <h5 class="modal-title mb-0">List Data Barang</h5>
                        <div class="ml-auto mutasi-toolbar">
                            <input type="search" class="form-control" id="searchBarangMutasi" placeholder="Cari kode atau nama barang">
                        </div>
                        <button type="button" class="close ml-3" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tableBarangMutasi">
                                <thead>
                                    <tr>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th style="width: 160px;">Qty</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 barang-pagination" id="barangMutasiPagination">
                            <button type="button" class="btn btn-sm btn-secondary" id="btnBarangPrev">Sebelumnya</button>
                            <span class="text-muted" id="barangPageInfo">Halaman 1 / 1</span>
                            <button type="button" class="btn btn-sm btn-secondary" id="btnBarangNext">Berikutnya</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="btnTambahBarangTmp">Tambah Barang</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalPlotQtyMutasi" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Input Qty Plot</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="tmp_id">
                        <input type="hidden" id="tmp_satuan_id">
                        <input type="hidden" id="tmp_no_lot">
                        <input type="hidden" id="tmp_exp_date">

                        <div class="form-group">
                            <label>Barang</label>
                            <input type="text" id="tmp_nama_barang" class="form-control" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No Lot</label>
                                    <input type="text" id="tmp_no_lot_label" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Expired Date</label>
                                    <input type="text" id="tmp_exp_date_label" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Qty Tersedia</label>
                            <input type="number" id="tmp_qty_gudang" class="form-control" readonly>
                        </div>

                        <div class="form-group mb-0">
                            <label>Qty Plot</label>
                            <input type="number" id="tmp_qty" class="form-control qty-plot" min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="button" id="btnUpdateTmp" class="btn btn-primary">Rekam</button>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; 2022 <a href="https://kiu.co.id">PT.KARISMA INDOARGO UNIVERSAL</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <script>
        $(function() {
            const defaultGudang = '<?= (int) $gudang_aktif ?>';
            let selectedBarang = null;
            let lotOptions = [];
            let barangSearchTimer = null;
            let barangPage = 1;
            const barangPerPage = 10;
            let draftCount = 0;
            let selectedTmpId = null;
            let selectedTmpRow = null;

            function esc(value) {
                return $('<div>').text(value == null ? '' : value).html();
            }

            function formatQty(value) {
                const number = Number(value || 0);
                return Number.isInteger(number) ? number : number.toFixed(2);
            }

            function notify(icon, title, text) {
                if (window.Swal) {
                    Swal.fire({
                        icon: icon || 'info',
                        title: title || '',
                        text: text || '',
                        timer: icon === 'success' ? 1800 : undefined,
                        showConfirmButton: icon !== 'success'
                    });
                    return;
                }
                alert([title, text].filter(Boolean).join('\n'));
            }

            function toast(icon, title) {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: icon || 'success',
                        title: title || '',
                        timer: 2200,
                        showConfirmButton: false
                    });
                    return;
                }
                alert(title || '');
            }

            function confirmAction(options, callback) {
                if (window.Swal) {
                    Swal.fire({
                        title: options.title || 'Konfirmasi',
                        text: options.text || '',
                        icon: options.icon || 'warning',
                        showCancelButton: true,
                        confirmButtonText: options.confirmText || 'Ya',
                        cancelButtonText: options.cancelText || 'Batal',
                        confirmButtonColor: options.confirmColor || '#007bff'
                    }).then(result => {
                        if (result.isConfirmed) callback();
                    });
                    return;
                }
                if (confirm(options.title || 'Konfirmasi')) callback();
            }

            function syncGudangTujuan() {
                const idGudang = $('#fromgdg').val();
                $('#tujuangdg option').prop('disabled', false).show();
                $('#tujuangdg option[value="' + idGudang + '"]').prop('disabled', true).hide();

                if ($('#tujuangdg').val() === idGudang) {
                    const firstTarget = $('#tujuangdg option:not(:disabled)').first().val();
                    $('#tujuangdg').val(firstTarget || '');
                }
            }

            function syncFromGudangLock() {
                const hasDraft = draftCount > 0;
                $('#fromgdg').prop('disabled', hasDraft);
                $('#fromGdgLockInfo').text(hasDraft ? 'Dari Gudang terkunci selama draft masih berisi barang.' : '');
            }

            function renderTmpRows(data) {
                draftCount = data.length;
                if (draftCount > 0 && data[0].gudang_asal) {
                    $('#fromgdg').val(data[0].gudang_asal);
                    syncGudangTujuan();
                }
                syncFromGudangLock();
                let html = '';

                if (!data.length) {
                    html = `<tr><td colspan="7" class="text-center text-muted">Belum ada data</td></tr>`;
                } else {
                    data.forEach(r => {
                        html += `
                            <tr data-id="${esc(r.id)}"
                                data-kode="${esc(r.kd_barang || '')}"
                                data-kode-system="${esc(r.kode_barang_system || '')}"
                                data-nama="${esc(r.nama_barang || '')}"
                                data-lot="${esc(r.no_lot || '')}"
                                data-exp="${esc(r.exp_date || '')}"
                                data-qty="${esc(r.qty || 0)}"
                                data-satuan="${esc(r.satuan_id || 2)}"
                                data-satuan-nama="${esc(r.satuan_nama || 'Pcs')}"
                                class="${selectedTmpId && Number(selectedTmpId) === Number(r.id) ? 'active' : ''}">
                                <td>${esc(r.kd_barang || r.kode_barang_system || '-')}</td>
                                <td>${esc(r.nama_barang || '-')}</td>
                                <td>${esc(r.no_lot || '-')}</td>
                                <td>${esc(r.exp_date || '-')}</td>
                                <td>${esc(formatQty(r.qty))}</td>
                                <td>${esc(r.satuan_nama || 'Pcs')}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete-row" data-id="${esc(r.id)}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                    });
                }

                $('#input_tmp_mutasi tbody').html(html);
                if (selectedTmpId && !$('#input_tmp_mutasi tbody tr[data-id="' + selectedTmpId + '"]').length) {
                    selectedTmpId = null;
                    selectedTmpRow = null;
                    hideLotCard();
                }
            }

            function loadTmpMutasi(callback) {
                $.getJSON('<?= base_url("ics/ajax_list_tmp_mutasi") ?>', function(data) {
                    renderTmpRows(data);
                    if (typeof callback === 'function') callback(data);
                });
            }

            function renderBarangPagination(pagination) {
                const page = Number(pagination.page || 1);
                const totalPages = Number(pagination.total_pages || 1);
                const totalRows = Number(pagination.total_rows || 0);

                barangPage = page;
                $('#barangPageInfo').text(`Halaman ${page} / ${totalPages} (${totalRows} barang)`);
                $('#btnBarangPrev').prop('disabled', page <= 1);
                $('#btnBarangNext').prop('disabled', page >= totalPages);
            }

            function loadBarangModal(page) {
                barangPage = page || barangPage || 1;
                $('#tableBarangMutasi tbody').html(`<tr><td colspan="3" class="text-center text-muted">Memuat data...</td></tr>`);
                selectedBarang = null;

                $.getJSON('<?= base_url("ics/ajax_list_barang_mutasi_gudang") ?>', {
                    id_gudang: $('#fromgdg').val(),
                    term: $('#searchBarangMutasi').val(),
                    page: barangPage,
                    per_page: barangPerPage
                }, function(res) {
                    const data = Array.isArray(res) ? res : (res.data || []);
                    const pagination = Array.isArray(res) ? {
                        page: 1,
                        total_pages: 1,
                        total_rows: data.length
                    } : (res.pagination || {});
                    let html = '';

                    if (!data.length) {
                        html = `<tr><td colspan="3" class="text-center text-muted">Data tidak ditemukan</td></tr>`;
                    } else {
                        data.forEach(row => {
                            html += `
                                <tr class="barang-row"
                                    data-kode="${esc(row.kd_barang || '')}"
                                    data-kode-system="${esc(row.kode_barang_system || '')}"
                                    data-nama="${esc(row.nama_barang || '')}"
                                    data-satuan="${esc(row.satuan_id || 2)}"
                                    data-satuan-nama="${esc(row.satuan_nama || 'Pcs')}"
                                    data-qty-gudang="${esc(row.qty || 0)}">
                                    <td>${esc(row.kd_barang || row.kode_barang_system || '-')}</td>
                                    <td>${esc(row.nama_barang || '-')}</td>
                                    <td>${esc(formatQty(row.qty))}</td>
                                </tr>`;
                        });
                    }

                    $('#tableBarangMutasi tbody').html(html);
                    renderBarangPagination(pagination);
                });
            }

            function addSelectedBarang() {
                if (!selectedBarang) {
                    notify('warning', 'Pilih barang terlebih dahulu');
                    return;
                }

                const qty = 1;
                const qtyGudang = Number(selectedBarang.$row.data('qty-gudang') || 0);

                if (qtyGudang <= 0) {
                    notify('warning', 'Stock barang kosong');
                    return;
                }

                $.post('<?= base_url("ics/ajax_add_tmp_mutasi") ?>', {
                    kode_barang: selectedBarang.$row.data('kode'),
                    kode_barang_system: selectedBarang.$row.data('kode-system'),
                    nama_barang: selectedBarang.$row.data('nama'),
                    exp_date: '',
                    no_lot: '',
                    qty: qty,
                    satuan_id: selectedBarang.$row.data('satuan') || 2,
                    gudang_asal: $('#fromgdg').val()
                }, function(res) {
                    const r = typeof res === 'object' ? res : JSON.parse(res);
                    if (r.status) {
                        $('#modalBarangMutasi').modal('hide');
                        toast('success', 'Barang ditambahkan ke draft');
                        loadTmpMutasi();
                    } else {
                        notify('error', 'Gagal menambah barang', r.msg || 'Data tidak valid');
                    }
                }, 'json');
            }

            function hideLotCard() {
                $('#cardLotMutasi').hide();
                $('#selectedBarangKode').text('');
                $('#selectedBarangNama').text('-');
                $('#tableLotMutasi tbody').html(`<tr><td colspan="4" class="text-center text-muted">Pilih barang pada list mutasi barang</td></tr>`);
            }

            function renderLotRows(rows) {
                let html = '';

                if (!rows.length) {
                    html = `<tr><td colspan="4" class="text-center text-muted">Data lot tidak ditemukan</td></tr>`;
                } else {
                    rows.forEach(row => {
                        html += `
                            <tr>
                                <td>${esc(row.no_lot || '-')}</td>
                                <td>${esc(row.exp_date || '-')}</td>
                                <td>${esc(formatQty(row.qty_gudang || 0))}</td>
                                <td>
                                    <button type="button"
                                        class="btn btn-sm btn-primary btn-plot-lot"
                                        data-lot="${esc(row.no_lot || '-')}"
                                        data-exp="${esc(row.exp_date || '')}"
                                        data-qty="${esc(row.qty_gudang || 0)}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                            </tr>`;
                    });
                }

                $('#tableLotMutasi tbody').html(html);
            }

            function loadLotTable($row) {
                selectedTmpId = $row.data('id');
                selectedTmpRow = $row;

                $('#input_tmp_mutasi tbody tr[data-id]').removeClass('active');
                $row.addClass('active');
                $('#cardLotMutasi').show();
                $('#selectedBarangKode').text($row.data('kode') || '-');
                $('#selectedBarangNama').text($row.data('nama') || '-');
                $('#tableLotMutasi tbody').html(`<tr><td colspan="4" class="text-center text-muted">Memuat data lot...</td></tr>`);

                $.getJSON('<?= base_url("ics/ajax_lot_tmp_mutasi") ?>', {
                    id: selectedTmpId,
                    id_gudang: $('#fromgdg').val()
                }, function(res) {
                    if (!res.status) {
                        notify('error', 'Gagal memuat lot', res.msg || 'Data tidak valid');
                        hideLotCard();
                        return;
                    }

                    lotOptions = res.data || [];
                    renderLotRows(lotOptions);
                }).fail(function() {
                    notify('error', 'Gagal memuat lot', 'Terjadi kesalahan sistem');
                    hideLotCard();
                });
            }

            function reloadSelectedLotTable() {
                const $row = $('#input_tmp_mutasi tbody tr[data-id="' + selectedTmpId + '"]');
                if ($row.length) {
                    loadLotTable($row);
                }
            }

            function openQtyModal($button) {
                if (!selectedTmpRow || !selectedTmpId) {
                    notify('warning', 'Pilih barang mutasi terlebih dahulu');
                    return;
                }

                const qtyGudang = Number($button.data('qty') || 0);
                if (qtyGudang <= 0) {
                    notify('warning', 'Stock lot kosong');
                    return;
                }

                const currentQty = Number(selectedTmpRow.data('qty') || 1);
                const defaultQty = Math.max(1, Math.min(currentQty, qtyGudang));

                $('#tmp_id').val(selectedTmpId);
                $('#tmp_satuan_id').val(selectedTmpRow.data('satuan') || 2);
                $('#tmp_nama_barang').val(selectedTmpRow.data('nama') || '-');
                $('#tmp_no_lot').val($button.data('lot') || '-');
                $('#tmp_exp_date').val($button.data('exp') || '');
                $('#tmp_no_lot_label').val($button.data('lot') || '-');
                $('#tmp_exp_date_label').val($button.data('exp') || '');
                $('#tmp_qty_gudang').val(qtyGudang);
                $('#tmp_qty').attr('max', qtyGudang).val(defaultQty);
                $('#modalPlotQtyMutasi').modal('show');
            }

            $('#fromgdg').val(defaultGudang);
            syncGudangTujuan();
            loadTmpMutasi();

            $('#fromgdg').on('change', function() {
                syncGudangTujuan();
                barangPage = 1;
                if ($('#modalBarangMutasi').hasClass('show')) {
                    loadBarangModal(1);
                }
            });

            $(document).on('click', '#btnOpenBarangMutasi', function() {
                $('#searchBarangMutasi').val('');
                barangPage = 1;
                $('#modalBarangMutasi').modal('show');
                loadBarangModal(1);
            });

            $('#searchBarangMutasi').on('input', function() {
                clearTimeout(barangSearchTimer);
                barangSearchTimer = setTimeout(function() {
                    loadBarangModal(1);
                }, 250);
            });

            $('#btnBarangPrev').on('click', function() {
                if (barangPage > 1) {
                    loadBarangModal(barangPage - 1);
                }
            });

            $('#btnBarangNext').on('click', function() {
                loadBarangModal(barangPage + 1);
            });

            $(document).on('click', '.barang-row', function() {
                $('.barang-row').removeClass('active');
                $(this).addClass('active');
                selectedBarang = {
                    $row: $(this)
                };
            });

            $(document).on('dblclick', '.barang-row', function() {
                $(this).trigger('click');
                addSelectedBarang();
            });

            $('#btnTambahBarangTmp').on('click', addSelectedBarang);

            $(document).on('click', '#input_tmp_mutasi tbody tr[data-id]', function(e) {
                if ($(e.target).closest('button').length) return;
                loadLotTable($(this));
            });

            $(document).on('click', '.btn-plot-lot', function() {
                openQtyModal($(this));
            });

            $(document).on('click', '.btn-delete-row', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');

                confirmAction({
                    title: 'Hapus data draft?',
                    text: 'Barang ini akan dihapus dari list mutasi barang.',
                    confirmText: 'Hapus',
                    confirmColor: '#dc3545'
                }, function() {
                    $.post('<?= base_url("ics/ajax_delete_tmp_mutasi") ?>', {
                        id: id
                    }, function() {
                        if (Number(selectedTmpId) === Number(id)) {
                            selectedTmpId = null;
                            selectedTmpRow = null;
                            hideLotCard();
                        }
                        toast('success', 'Data draft dihapus');
                        loadTmpMutasi();
                    });
                });
            });

            $('#btnUpdateTmp').on('click', function() {
                const qty = Number($('#tmp_qty').val() || 0);
                const qtyGudang = Number($('#tmp_qty_gudang').val() || 0);

                if (qty <= 0) {
                    notify('warning', 'Qty tidak valid', 'Qty plot wajib lebih dari 0');
                    return;
                }
                if (qty > qtyGudang) {
                    notify('warning', 'Qty melebihi stock', 'Qty plot tidak boleh melebihi qty tersedia');
                    return;
                }

                $.post('<?= base_url("ics/ajax_update_tmp_mutasi") ?>', {
                    id: $('#tmp_id').val(),
                    id_gudang: $('#fromgdg').val(),
                    no_lot: $('#tmp_no_lot').val(),
                    exp_date: $('#tmp_exp_date').val(),
                    qty: $('#tmp_qty').val(),
                    satuan_id: $('#tmp_satuan_id').val()
                }, function(res) {
                    const r = typeof res === 'object' ? res : JSON.parse(res);
                    if (r.status) {
                        $('#modalPlotQtyMutasi').modal('hide');
                        toast('success', 'Lot berhasil diplot');
                        loadTmpMutasi(function() {
                            reloadSelectedLotTable();
                        });
                    } else {
                        notify('error', 'Gagal merekam plot', r.msg || 'Data tidak valid');
                    }
                }, 'json');
            });

            $('#rekammutasi').on('click', function(e) {
                e.preventDefault();

                $.ajax({
                    url: '<?= base_url("ics/ajax_rekam_mutasi") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        nofresnsi: $('#nofresnsi').val(),
                        tgl_transaksi: $('#tgl_transaksi').val(),
                        keterangan_mutasi: $('#keterangan_mutasi').val(),
                        fromgdg: $('#fromgdg').val(),
                        tujuangdg: $('#tujuangdg').val()
                    },
                    success: function(res) {
                        if (res.status) {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'success',
                                    title: res.msg,
                                    text: 'No Ref: ' + res.noreff,
                                    confirmButtonText: 'OK'
                                }).then(() => location.reload());
                            } else {
                                alert(res.msg + '\nNo Ref: ' + res.noreff);
                                location.reload();
                            }
                        } else {
                            notify('warning', 'Mutasi belum bisa direkam', res.msg);
                        }
                    },
                    error: function() {
                        notify('error', 'Terjadi kesalahan sistem');
                    }
                });
            });
        });
    </script>
