<script>
    $(function() {
        const hasSwal = typeof Swal !== 'undefined';
        const endpointBase = "<?= base_url($this->uri->uri_string() === 'purchase/listBarang' ? 'purchase/listBarang' : 'master_barang') ?>";
        const canFullEdit = <?= !empty($master_barang_access['can_full_edit']) ? 'true' : 'false' ?>;
        const canInfoLainEdit = <?= !empty($master_barang_access['can_info_lain_edit']) ? 'true' : 'false' ?>;
        const defaultImage = "<?= base_url('assets/images/Karisma.png') ?>";

        let currentId = 0;
        let currentSearch = '';
        let currentRows = [];

        function notify(icon, title, text) {
            if (hasSwal) {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text
                });
            } else {
                alert((title ? title + ': ' : '') + text);
            }
        }

        function debounce(fn, delay) {
            let timeoutId = null;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeoutId);
                timeoutId = setTimeout(function() {
                    fn.apply(context, args);
                }, delay);
            };
        }

        function formatText(value) {
            return value ? value : '-';
        }

        function setFormReadOnlyState() {
            const fullEditFields = [
                '#kode_barang',
                '#nama_barang',
                '#satuan',
                '#is_lot',
                '#status_active',
                '#is_active',
                '#kelompok_barang',
                '#kategori_barang',
                '#bahan_aktif',
                '#merk_barang',
                '#kd_suplier',
                '#stock_minimum',
                '#produk_fokus'
            ];

            $(fullEditFields.join(',')).prop('disabled', !canFullEdit);
            $('#panjang, #lebar, #tinggi, #berat, #isi, #kemasan').prop('disabled', !canInfoLainEdit);
        }

        function resetForm() {
            currentId = 0;
            $('#formMasterBarangModern')[0].reset();
            $('#master_id').val('');
            $('.master-list-item').removeClass('active');
            $('#tab-informasi-link').tab('show');
            $('#tab-gambar img').attr('src', defaultImage);
            setStatusBarang(true);
            setFormReadOnlyState();
        }

        function populateForm(row) {
            currentId = parseInt(row.id_barang || row.id || 0, 10);
            $('#master_id').val(currentId);
            $('#kode_barang').val(row.kode_barang || '');
            $('#nama_barang').val(row.nama_barang || '');
            $('#satuan').val(row.satuan || '');
            $('#kelompok_barang').val(row.kelompok_barang || '');
            $('#kategori_barang').val(row.kategori_barang || '');
            $('#bahan_aktif').val(row.bahan_aktif || '');
            $('#merk_barang').val(row.merk_barang || '');
            $('#kd_suplier').val(row.kd_suplier || '');
            $('#stock_minimum').val(row.stock_minimum || 0);
            $('#produk_fokus').val(row.produk_fokus || '');
            $('#panjang').val(row.panjang || 0);
            $('#lebar').val(row.lebar || 0);
            $('#tinggi').val(row.tinggi || 0);
            $('#berat').val(row.berat || 0);
            $('#isi').val(row.isi || 0);
            $('#kemasan').val(row.kemasan || 0);
            $('#is_lot').prop('checked', (row.is_lot || 'F') === 'T');
            setStatusBarang((row.is_active || 'T') !== 'F');
            setFormReadOnlyState();
        }

        function renderList(items) {
            const $list = $('#masterBarangList');

            if (!items.length) {
                $list.html('<div class="empty-state">Data barang tidak ditemukan.</div>');
                return;
            }

            let html = '';
            items.forEach(function(item) {
                const activeClass = parseInt(item.id, 10) === currentId ? ' active' : '';
                const inactiveBadge = item.is_active === 'F' ? ' <span class="badge badge-secondary">Nonaktif</span>' : '';
                html += '' +
                    '<div class="master-list-item' + activeClass + '" data-id="' + item.id + '">' +
                    '  <div class="master-list-thumb"><img src="' + defaultImage + '" alt="Barang"></div>' +
                    '  <div class="master-list-meta">' +
                    '    <div class="master-list-code">' + formatText(item.kode_barang) + inactiveBadge + '</div>' +
                    '    <div class="master-list-name">' + formatText(item.nama_barang) + '</div>' +
                    '    <div class="master-list-supplier">' + formatText(item.nama_suplier) + '</div>' +
                    '  </div>' +
                    '</div>';
            });

            $list.html(html);
        }

        function loadDetail(id) {
            $.ajax({
                url: endpointBase + "/detail",
                type: "POST",
                dataType: "json",
                data: {
                    id: id
                },
                success: function(resp) {
                    if (!resp.status || !resp.data) {
                        notify('warning', 'Perhatian', resp.message || 'Data tidak ditemukan.');
                        return;
                    }

                    populateForm(resp.data);
                    $('.master-list-item').removeClass('active');
                    $('.master-list-item[data-id="' + id + '"]').addClass('active');
                },
                error: function(xhr) {
                    let message = 'Gagal mengambil detail data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    notify('error', 'Gagal', message);
                }
            });
        }

        function loadList(searchValue) {
            $.ajax({
                url: endpointBase + "/list",
                type: "POST",
                dataType: "json",
                data: {
                    search: searchValue || '',
                    limit: 150
                },
                success: function(resp) {
                    if (!resp.status) {
                        notify('warning', 'Perhatian', resp.message || 'Gagal memuat daftar barang.');
                        return;
                    }

                    currentRows = resp.data || [];
                    $('#masterBarangCountLabel').text((resp.filtered || 0) + ' data');
                    renderList(currentRows);

                    if (!currentRows.length) {
                        resetForm();
                        return;
                    }

                    const selectedExists = currentRows.some(function(item) {
                        return parseInt(item.id, 10) === currentId;
                    });

                    if (!selectedExists) {
                        loadDetail(currentRows[0].id);
                    }
                },
                error: function() {
                    notify('error', 'Gagal', 'Gagal memuat daftar master barang.');
                }
            });
        }

        $('#masterBarangSearch').on('input', debounce(function() {
            currentSearch = $(this).val().trim();
            loadList(currentSearch);
        }, 300));

        $('#masterBarangList').on('click', '.master-list-item', function() {
            const id = parseInt($(this).data('id'), 10);
            if (id > 0) {
                loadDetail(id);
            }
        });

        $('#btnBaruMasterBarang').on('click', function() {
            resetForm();
        });

        $('#btnBatalMasterBarang').on('click', function() {
            if (currentId > 0) {
                loadDetail(currentId);
                return;
            }
            resetForm();
        });

        function setStatusBarang(isActive) {
            $('#status_active').prop('checked', !!isActive);
            $('#is_active').prop('checked', !isActive);
        }

        $('#status_active').on('change', function() {
            setStatusBarang($(this).is(':checked'));
        });

        $('#is_active').on('change', function() {
            setStatusBarang(!$(this).is(':checked'));
        });

        $('#formMasterBarangModern').on('submit', function(e) {
            e.preventDefault();

            if (!canInfoLainEdit) {
                notify('warning', 'Perhatian', 'Akses anda hanya dapat melihat data master barang.');
                return;
            }

            if (!canFullEdit && currentId <= 0) {
                notify('warning', 'Perhatian', 'Akses logistik hanya dapat mengubah data barang yang sudah ada.');
                return;
            }

            const endpoint = currentId > 0 ? endpointBase + "/update" : endpointBase + "/store";

            $.ajax({
                url: endpoint,
                type: "POST",
                dataType: "json",
                data: {
                    id: currentId,
                    kode_barang: $('#kode_barang').val(),
                    kd_suplier: $('#kd_suplier').val(),
                    nama_barang: $('#nama_barang').val(),
                    satuan: $('#satuan').val(),
                    kelompok_barang: $('#kelompok_barang').val(),
                    kategori_barang: $('#kategori_barang').val(),
                    bahan_aktif: $('#bahan_aktif').val(),
                    merk_barang: $('#merk_barang').val(),
                    stock_minimum: $('#stock_minimum').val(),
                    produk_fokus: $('#produk_fokus').val(),
                    panjang: $('#panjang').val(),
                    lebar: $('#lebar').val(),
                    tinggi: $('#tinggi').val(),
                    berat: $('#berat').val(),
                    isi: $('#isi').val(),
                    kemasan: $('#kemasan').val(),
                    is_lot: $('#is_lot').is(':checked') ? 'T' : 'F',
                    is_active: $('#status_active').is(':checked') ? 'T' : 'F'
                },
                success: function(resp) {
                    if (!resp.status) {
                        notify('error', 'Gagal', resp.message || 'Proses gagal.');
                        return;
                    }

                    notify('success', 'Berhasil', resp.message || 'Data master barang berhasil disimpan.');
                    loadList(currentSearch);
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan pada server.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    notify('error', 'Gagal', message);
                }
            });
        });

        setFormReadOnlyState();
        loadList('');
    });
</script>
