<style>
    .persediaan-page {
        min-height: calc(100vh - 56px);
        background: #f4f6f8;
        color: #0f1720;
        border-top: 3px solid #177fae;
        padding: 18px 22px 16px;
    }

    .persediaan-title {
        font-size: 25px;
        font-weight: 500;
        margin-bottom: 16px;
    }

    .persediaan-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .persediaan-search {
        position: relative;
        width: 330px;
        max-width: 100%;
    }

    .persediaan-search input,
    .input-line {
        width: 100%;
        height: 32px;
        border: 0;
        border-radius: 0;
        background: #e4e4e4;
        padding: 5px 34px 5px 8px;
    }

    .input-modal-dialog {
        max-width: 720px;
    }

    .input-modal-content {
        border: 0;
        border-radius: 0;
        overflow: hidden;
        box-shadow: 0 16px 34px rgba(15, 23, 32, .28);
    }

    .input-modal-header {
        background: #157fad;
        color: #fff;
        border: 0;
        padding: 16px 20px;
    }

    .input-modal-header .modal-title {
        font-size: 22px;
        font-weight: 600;
    }

    .input-modal-subtitle {
        display: block;
        font-size: 13px;
        opacity: .9;
        margin-top: 2px;
    }

    .item-preview {
        background: #eef5f8;
        border-left: 4px solid #087bb0;
        padding: 12px 14px;
        margin-bottom: 14px;
    }

    .item-preview strong {
        display: block;
        font-size: 15px;
        color: #0f1720;
    }

    .item-preview span {
        color: #52606d;
        font-size: 13px;
    }

    .field-panel {
        background: #f7f9fb;
        border: 1px solid #d8e0e7;
        padding: 14px;
    }

    .qty-stock-panel {
        background: #0f1720;
        color: #fff;
        padding: 11px 14px;
        min-height: 58px;
    }

    .qty-stock-panel label {
        display: block;
        color: #a9d5e8;
        font-size: 12px;
        margin: 0;
    }

    .qty-stock-panel input {
        background: transparent;
        color: #fff;
        border: 0;
        padding: 0;
        height: auto;
        font-size: 24px;
        font-weight: 600;
    }

    .input-modal-footer {
        border: 0;
        background: #f4f6f8;
        padding: 14px 20px 18px;
    }

    .persediaan-search i {
        position: absolute;
        right: 10px;
        top: 9px;
        color: #87919b;
    }

    .persediaan-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
        table-layout: fixed;
    }

    .persediaan-table thead th {
        background: #157fad;
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        height: 40px;
        padding: 8px;
        border: 0;
    }

    .persediaan-table tbody td {
        background: #e5e5e5;
        height: 38px;
        padding: 7px 8px;
        border: 0;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .persediaan-table tbody tr[data-row] {
        cursor: pointer;
    }

    .persediaan-table tbody tr.is-selected td {
        background: #6dbbe0;
        color: #fff;
    }

    .persediaan-btn {
        min-width: 92px;
        height: 30px;
        border: 0;
        border-radius: 0;
        background: #087bb0;
        color: #fff;
        font-size: 14px;
        padding: 5px 14px;
    }

    .persediaan-btn:hover,
    .persediaan-btn:focus {
        color: #fff;
        background: #066c9b;
    }

    .persediaan-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 8px;
    }

    .input-grid {
        display: grid;
        grid-template-columns: 120px 1fr;
        gap: 10px 14px;
        align-items: center;
    }

    .select2-container--bootstrap4 .select2-selection {
        border-radius: 0;
        min-height: 34px;
    }

    @media (max-width: 768px) {
        .persediaan-page {
            padding: 14px 12px;
        }

        .persediaan-toolbar,
        .persediaan-footer,
        .input-grid {
            display: block;
        }

        .persediaan-search,
        .persediaan-btn {
            width: 100%;
            margin-top: 8px;
        }
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
            <section class="content persediaan-page">
                <h1 class="persediaan-title">Data Persediaan</h1>

                <div class="persediaan-toolbar">
                    <div class="persediaan-search">
                        <input type="search" id="searchBarangMutasi" autocomplete="off" placeholder="Cari kode atau nama barang">
                        <i class="fas fa-search"></i>
                    </div>
                    <button type="button" class="persediaan-btn" id="btnKembaliInput">Kembali</button>
                </div>

                <table class="persediaan-table" id="tableBarangMutasi">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Kode</th>
                            <th>Deskripsi</th>
                            <th style="width: 18%; text-align: right;">Tersedia</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="persediaan-footer">
                    <span id="barangPageInfo">Halaman 1 / 1</span>
                    <div>
                        <button type="button" class="persediaan-btn" id="btnBarangPrev">Prev</button>
                        <button type="button" class="persediaan-btn" id="btnBarangNext">Next</button>
                    </div>
                </div>
            </section>
        </div>

        <div class="modal fade" id="modalInputBarangMutasi" tabindex="-1">
            <div class="modal-dialog input-modal-dialog modal-dialog-centered">
                <div class="modal-content input-modal-content">
                    <div class="modal-header input-modal-header">
                        <div>
                            <h5 class="modal-title mb-0">Input Barang Mutasi</h5>
                            <span class="input-modal-subtitle">Pilih lot dan expired date sebelum merekam list mutasi.</span>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="kodeBarang">
                        <input type="hidden" id="kodeBarangSystem">
                        <input type="hidden" id="namaBarang">
                        <input type="hidden" id="satuanId">
                        <div class="item-preview">
                            <strong id="namaBarangLabel">-</strong>
                            <span id="kodeBarangLabel">-</span>
                        </div>
                        <div class="field-panel">
                            <div class="input-grid">
                                <label for="qtyDiminta">Jumlah</label>
                                <input type="number" class="input-line" id="qtyDiminta" min="1" step="1" value="1">

                                <label for="noLotSelect">No Lot</label>
                                <select id="noLotSelect" style="width:100%;"></select>

                                <label for="expiredSelect">Expired Date</label>
                                <select id="expiredSelect" style="width:100%;"></select>
                            </div>
                            <div class="qty-stock-panel mt-3">
                                <label for="qtyStock">Qty Stock Tersedia</label>
                                <input type="text" id="qtyStock" readonly value="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer input-modal-footer">
                        <button type="button" class="persediaan-btn" data-dismiss="modal">Batal</button>
                        <button type="button" class="persediaan-btn" id="btnRekamBarangMutasi">Rekam</button>
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
            const idGudang = '<?= (int) $gudang_asal ?>';
            const inputUrl = '<?= base_url("ics/mutasi_barang/input") ?>?tujuangdg=<?= (int) $gudang_tujuan ?>';
            const urls = {
                listBarang: '<?= base_url("ics/ajax_list_barang_mutasi_gudang") ?>',
                lotSelect: '<?= base_url("ics/ajax_mutasi_lot_select2") ?>',
                expSelect: '<?= base_url("ics/ajax_mutasi_exp_select2") ?>',
                lotQty: '<?= base_url("ics/ajax_mutasi_lot_qty") ?>',
                addTmp: '<?= base_url("ics/ajax_add_tmp_mutasi") ?>'
            };

            let page = 1;
            let totalPages = 1;
            let searchTimer = null;
            let selectedBarang = null;
            let lastQtyAlertAt = 0;

            function esc(value) {
                return $('<div>').text(value == null ? '' : value).html();
            }

            function qtyNumber(value) {
                return Number(value || 0);
            }

            function formatQty(value) {
                const number = qtyNumber(value);
                return Number.isInteger(number) ? number : number.toFixed(2);
            }

            function notify(icon, title, text) {
                if (window.Swal) {
                    Swal.fire({
                        icon: icon || 'info',
                        title: title || '',
                        text: text || '',
                        showConfirmButton: icon !== 'success',
                        timer: icon === 'success' ? 1800 : undefined
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
                        icon: icon || 'info',
                        title: title || '',
                        showConfirmButton: false,
                        timer: 1800
                    });
                    return;
                }
                alert(title || '');
            }

            function confirmRecord(callback) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'question',
                        title: 'Rekam barang mutasi?',
                        text: 'Barang akan ditambahkan ke list mutasi sementara.',
                        showCancelButton: true,
                        confirmButtonText: 'Rekam',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#087bb0'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            callback();
                        }
                    });
                    return;
                }

                if (confirm('Rekam barang mutasi?')) {
                    callback();
                }
            }

            function renderRows(rows) {
                let html = '';
                if (!rows.length) {
                    html = '<tr><td colspan="3" class="text-center">Data tidak ditemukan</td></tr>';
                } else {
                    rows.forEach(function(row) {
                        html += `
                            <tr data-row="barang"
                                data-kode="${esc(row.kd_barang || '')}"
                                data-kode-system="${esc(row.kode_barang_system || '')}"
                                data-nama="${esc(row.nama_barang || '')}"
                                data-satuan="${esc(row.satuan_id || 2)}"
                                data-qty="${esc(row.qty || 0)}">
                                <td>${esc(row.kd_barang || row.kode_barang_system || '')}</td>
                                <td>${esc(row.nama_barang || '')}</td>
                                <td class="text-right">${esc(formatQty(row.qty))}</td>
                            </tr>`;
                    });
                }
                $('#tableBarangMutasi tbody').html(html);
            }

            function loadBarang(nextPage) {
                page = Math.max(1, nextPage || 1);
                $('#tableBarangMutasi tbody').html('<tr><td colspan="3" class="text-center">Memuat data...</td></tr>');
                $.getJSON(urls.listBarang, {
                    id_gudang: idGudang,
                    term: $('#searchBarangMutasi').val(),
                    page: page,
                    per_page: 10
                }, function(res) {
                    const data = res.data || [];
                    const pagination = res.pagination || {};
                    page = Number(pagination.page || page);
                    totalPages = Number(pagination.total_pages || 1);
                    renderRows(data);
                    $('#barangPageInfo').text('Halaman ' + page + ' / ' + totalPages + ' (' + Number(pagination.total_rows || data.length) + ' barang)');
                    $('#btnBarangPrev').prop('disabled', page <= 1);
                    $('#btnBarangNext').prop('disabled', page >= totalPages);
                }).fail(function() {
                    notify('error', 'Gagal memuat data persediaan', 'Server tidak merespons.');
                });
            }

            function currentItemParams() {
                return {
                    id_gudang: idGudang,
                    kode_barang_system: $('#kodeBarangSystem').val(),
                    nama_barang: $('#namaBarang').val()
                };
            }

            function resetLotFields() {
                $('#noLotSelect').val(null).trigger('change');
                $('#expiredSelect').val(null).trigger('change');
                $('#qtyStock').val('');
            }

            function openInputModal(row) {
                selectedBarang = {
                    kode: row.data('kode'),
                    kodeSystem: row.data('kode-system'),
                    nama: row.data('nama'),
                    satuan: row.data('satuan'),
                    qty: row.data('qty')
                };

                $('#kodeBarang').val(selectedBarang.kode || '');
                $('#kodeBarangSystem').val(selectedBarang.kodeSystem || '');
                $('#namaBarang').val(selectedBarang.nama || '');
                $('#satuanId').val(selectedBarang.satuan || 2);
                $('#namaBarangLabel').text(selectedBarang.nama || '-');
                $('#kodeBarangLabel').text(selectedBarang.kode || selectedBarang.kodeSystem || '-');
                $('#qtyDiminta').val(1);
                resetLotFields();
                $('#modalInputBarangMutasi').modal('show');
                toast('info', 'Lengkapi lot untuk barang terpilih');
            }

            $('#noLotSelect').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modalInputBarangMutasi'),
                placeholder: 'Cari no lot',
                allowClear: true,
                ajax: {
                    url: urls.lotSelect,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return Object.assign(currentItemParams(), {
                            term: params.term || ''
                        });
                    }
                }
            });

            $('#expiredSelect').select2({
                theme: 'bootstrap4',
                dropdownParent: $('#modalInputBarangMutasi'),
                placeholder: 'Pilih expired date',
                allowClear: true,
                ajax: {
                    url: urls.expSelect,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return Object.assign(currentItemParams(), {
                            no_lot: $('#noLotSelect').val() || '',
                            term: params.term || ''
                        });
                    }
                }
            });

            function loadQtyStock() {
                const noLot = $('#noLotSelect').val();
                const expDate = $('#expiredSelect').val();
                $('#qtyStock').val('');
                if (!noLot || !expDate) {
                    return;
                }

                $.getJSON(urls.lotQty, Object.assign(currentItemParams(), {
                    no_lot: noLot,
                    exp_date: expDate
                }), function(res) {
                    const stock = qtyNumber(res.qty_gudang || 0);
                    $('#qtyStock').val(formatQty(stock));
                    toast(stock > 0 ? 'success' : 'warning', 'Qty stock: ' + formatQty(stock));
                }).fail(function() {
                    notify('error', 'Gagal memuat qty stock', 'Server tidak merespons.');
                });
            }

            $('#noLotSelect').on('change', function() {
                $('#expiredSelect').val(null).trigger('change');
                $('#qtyStock').val('');
            });

            $('#noLotSelect').on('select2:select', function(e) {
                toast('info', 'No Lot dipilih: ' + (e.params.data.text || e.params.data.id));
            });

            $('#expiredSelect').on('select2:select', function(e) {
                toast('info', 'Expired Date dipilih: ' + (e.params.data.text || e.params.data.id));
            });

            $('#expiredSelect').on('change', loadQtyStock);

            $('#qtyDiminta').on('input', function() {
                const qty = qtyNumber($(this).val());
                const stock = qtyNumber($('#qtyStock').val());
                if (stock > 0 && qty > stock && Date.now() - lastQtyAlertAt > 1200) {
                    lastQtyAlertAt = Date.now();
                    notify('warning', 'Stock tidak cukup', 'Qty yang diminta melebihi qty stock tersedia.');
                }
            });

            $('#btnRekamBarangMutasi').on('click', function() {
                if (!selectedBarang) {
                    notify('warning', 'Pilih barang terlebih dahulu');
                    return;
                }

                const qty = qtyNumber($('#qtyDiminta').val());
                const stock = qtyNumber($('#qtyStock').val());
                if (qty <= 0) {
                    notify('warning', 'Qty tidak valid', 'Jumlah barang harus lebih dari 0.');
                    return;
                }
                if (!$('#noLotSelect').val() || !$('#expiredSelect').val()) {
                    notify('warning', 'Lot belum lengkap', 'No Lot dan Expired Date wajib dipilih.');
                    return;
                }
                if (qty > stock) {
                    notify('warning', 'Stock tidak cukup', 'Qty yang diminta melebihi qty stock tersedia.');
                    return;
                }

                confirmRecord(function() {
                    $.post(urls.addTmp, {
                        kode_barang: $('#kodeBarang').val(),
                        kode_barang_system: $('#kodeBarangSystem').val(),
                        nama_barang: $('#namaBarang').val(),
                        no_lot: $('#noLotSelect').val(),
                        exp_date: $('#expiredSelect').val(),
                        qty: qty,
                        satuan_id: $('#satuanId').val() || 2,
                        gudang_asal: idGudang
                    }, function(res) {
                        if (!res.status) {
                            notify('error', 'Gagal menambahkan barang', res.msg || 'Data tidak valid');
                            return;
                        }

                        notify('success', 'Barang masuk list mutasi');
                        setTimeout(function() {
                            window.location.href = inputUrl;
                        }, 650);
                    }, 'json').fail(function() {
                        notify('error', 'Gagal menambahkan barang', 'Server tidak merespons.');
                    });
                });
            });

            $('#searchBarangMutasi').on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    loadBarang(1);
                }, 250);
            });

            $('#btnBarangPrev').on('click', function() {
                if (page > 1) {
                    loadBarang(page - 1);
                }
            });

            $('#btnBarangNext').on('click', function() {
                if (page < totalPages) {
                    loadBarang(page + 1);
                }
            });

            $('#btnKembaliInput').on('click', function() {
                window.location.href = inputUrl;
            });

            $('#tableBarangMutasi').on('click', 'tr[data-row="barang"]', function() {
                $('#tableBarangMutasi tbody tr').removeClass('is-selected');
                $(this).addClass('is-selected');
            });

            $('#tableBarangMutasi').on('dblclick', 'tr[data-row="barang"]', function() {
                $(this).trigger('click');
                openInputModal($(this));
            });

            loadBarang(1);
        });
    </script>
