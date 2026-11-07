<script>
    $(function() {
        const hasSwal = typeof Swal !== 'undefined';
        const endpointBase = "<?= base_url($this->uri->uri_string() === 'purchase/listBarang' ? 'purchase/listBarang' : 'master_barang') ?>";
        const canFullEdit = <?= !empty($master_barang_access['can_full_edit']) ? 'true' : 'false' ?>;
        const canInfoLainEdit = <?= !empty($master_barang_access['can_info_lain_edit']) ? 'true' : 'false' ?>;
        const defaultImage = "<?= base_url('assets/images/Karisma.png') ?>";

        if ($.fn.select2) {
            $('#kd_suplier').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Pilih Supplier',
                allowClear: true
            });
        }

        let currentId = 0;
        let currentSearch = '';
        let currentKelompokBarang = '';
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

        function escapeHtml(value) {
            return $('<div>').text(value === null || typeof value === 'undefined' ? '' : value).html();
        }

        function formatText(value) {
            return value ? escapeHtml(value) : '-';
        }

        function setKelompokDagangValue(value, label) {
            const normalizedValue = value === null || typeof value === 'undefined' ? '' : String(value);
            const $select = $('#kelompok_dagang');

            if (normalizedValue !== '') {
                const optionExists = $select.find('option').filter(function() {
                    return $(this).val() === normalizedValue;
                }).length > 0;

                if (!optionExists) {
                    $select.append($('<option>', {
                        value: normalizedValue,
                        text: label || normalizedValue
                    }));
                }
            }

            $select.val(normalizedValue);
        }

        function updateKodeAkunDesc($select) {
            const $option = $select.find('option:selected');
            const target = $select.data('desc-target');
            const kode = $select.val() || '';
            const nama = $option.data('nama') || '';
            $(target).text(kode ? (nama || '-') : '-');
        }

        function setKodeAkunValue(selector, value) {
            const normalizedValue = value === null || typeof value === 'undefined' ? '' : String(value);
            const $select = $(selector);

            if (normalizedValue !== '') {
                const optionExists = $select.find('option').filter(function() {
                    return $(this).val() === normalizedValue;
                }).length > 0;

                if (!optionExists) {
                    $select.append($('<option>', {
                        value: normalizedValue,
                        text: normalizedValue
                    }));
                }
            }

            $select.val(normalizedValue);
            updateKodeAkunDesc($select);
        }

        function setSifatCheckbox(selector, value) {
            $(selector).prop('checked', (value || 'T') === 'F');
        }

        function setHppMethod(row) {
            const methods = {
                '#hpp_average': (row.hpp_average || 'T') === 'T',
                '#hpp_fifo': (row.hpp_fifo || 'F') === 'T',
                '#hpp_lifo': (row.hpp_lifo || 'F') === 'T'
            };
            const selected = Object.keys(methods).filter(function(selector) {
                return methods[selector];
            });

            $('.hpp-method-check').prop('checked', false);
            if (selected.length === 1) {
                $(selected[0]).prop('checked', true);
                return;
            }

            $('#hpp_average').prop('checked', true);
        }

        function resetKodeAkunDefaults() {
            $('.kode-akun-select').each(function() {
                setKodeAkunValue(this, $(this).data('default') || '');
            });
        }

        function setFormReadOnlyState() {
            const fullEditFields = [
                '#kode_barang',
                '#nama_barang',
                '#satuan',
                '#is_lot',
                '#status_active',
                '#is_active',
                '#kelompok_dagang',
                '#is_inventori',
                '#is_beli',
                '#is_jual',
                '#hpp_average',
                '#hpp_fifo',
                '#hpp_lifo',
                '.kode-akun-select',
                '#kelompok_barang',
                '#kategori_barang',
                '#bahan_aktif',
                '#merk_barang',
                '#produsen',
                '#spesifikasi_merk',
                '#golongan',
                '#kelompok',
                '#komposisi',
                '#grup',
                '#kd_suplier',
                '#stock_minimum',
                '#produk_fokus'
            ];

            $(fullEditFields.join(',')).prop('disabled', !canFullEdit);
            if ($.fn.select2) {
                $('#kd_suplier').prop('disabled', !canFullEdit).trigger('change.select2');
            }
            $('#panjang, #lebar, #tinggi, #berat, #isi, #kemasan').prop('disabled', !canInfoLainEdit);
        }

        function resetForm() {
            currentId = 0;
            $('#formMasterBarangModern')[0].reset();
            $('#master_id').val('');
            if ($.fn.select2) {
                $('#kd_suplier').val('').trigger('change');
            } else {
                $('#kd_suplier').val('');
            }
            $('.master-list-item').removeClass('active');
            $('#tab-informasi-link').tab('show');
            $('#tab-gambar img').attr('src', defaultImage);
            setStatusBarang(true);
            setSifatCheckbox('#is_inventori', 'T');
            setSifatCheckbox('#is_beli', 'T');
            setSifatCheckbox('#is_jual', 'T');
            setHppMethod({
                hpp_average: 'T',
                hpp_fifo: 'F',
                hpp_lifo: 'F'
            });
            resetKodeAkunDefaults();
            setFormReadOnlyState();
        }

        function populateForm(row) {
            currentId = parseInt(row.id_barang || row.id || 0, 10);
            $('#master_id').val(currentId);
            $('#kode_barang').val(row.kode_barang || '');
            $('#nama_barang').val(row.nama_barang || '');
            $('#satuan').val(row.satuan || '');
            setKelompokDagangValue(row.kelompok_dagang || '', row.kelompok_dagang_label || '');
            $('#kelompok_barang').val(row.kelompok_barang || '');
            $('#kategori_barang').val(row.kategori_barang || '');
            $('#bahan_aktif').val(row.bahan_aktif || '');
            $('#merk_barang').val(row.merk_barang || '');
            $('#produsen').val(row.produsen || '');
            $('#spesifikasi_merk').val(row.spesifikasi_merk || '');
            $('#golongan').val(row.golongan || '');
            $('#kelompok').val(row.kelompok || '');
            $('#komposisi').val(row.komposisi || '');
            $('#grup').val(row.grup || '');
            if ($.fn.select2) {
                $('#kd_suplier').val(row.kd_suplier || '').trigger('change');
            } else {
                $('#kd_suplier').val(row.kd_suplier || '');
            }
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
            setSifatCheckbox('#is_inventori', row.is_inventori || 'T');
            setSifatCheckbox('#is_beli', row.is_beli || 'T');
            setSifatCheckbox('#is_jual', row.is_jual || 'T');
            setHppMethod(row);
            setKodeAkunValue('#kode_akun_harga_pokok', row.kode_akun_harga_pokok || '51030');
            setKodeAkunValue('#kode_akun_penjualan', row.kode_akun_penjualan || '41032');
            setKodeAkunValue('#kode_akun_persediaan', row.kode_akun_persediaan || '14030');
            setKodeAkunValue('#kode_akun_pengiriman_beli', row.kode_akun_pengiriman_beli || '51032');
            setKodeAkunValue('#kode_akun_pengiriman_jual', row.kode_akun_pengiriman_jual || '64030');
            setKodeAkunValue('#kode_akun_retur_penjualan', row.kode_akun_retur_penjualan || '41034');
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
                const kelompokLabel = item.kelompok_dagang_label ? ' &bull; ' + formatText(item.kelompok_dagang_label) : '';
                html += '' +
                    '<div class="master-list-item' + activeClass + '" data-id="' + item.id + '">' +
                    '  <div class="master-list-thumb"><img src="' + defaultImage + '" alt="Barang"></div>' +
                    '  <div class="master-list-meta">' +
                    '    <div class="master-list-code">' + formatText(item.kode_barang) + inactiveBadge + '</div>' +
                    '    <div class="master-list-name">' + formatText(item.nama_barang) + '</div>' +
                    '    <div class="master-list-supplier">' + formatText(item.nama_suplier) + kelompokLabel + '</div>' +
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

        function loadList(searchValue, kelompokBarangValue) {
            $.ajax({
                url: endpointBase + "/list",
                type: "POST",
                dataType: "json",
                data: {
                    search: searchValue || '',
                    kelompok_barang: kelompokBarangValue || '',
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
            loadList(currentSearch, currentKelompokBarang);
        }, 300));

        $('#masterBarangKelompokFilter').on('change', function() {
            currentKelompokBarang = $(this).val() || '';
            loadList(currentSearch, currentKelompokBarang);
        });

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

        $('.hpp-method-check').on('change', function() {
            if ($(this).is(':checked')) {
                $('.hpp-method-check').not(this).prop('checked', false);
                return;
            }

            if ($('.hpp-method-check:checked').length === 0) {
                $(this).prop('checked', true);
            }
        });

        $('.kode-akun-select').on('change', function() {
            updateKodeAkunDesc($(this));
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
                    kelompok_dagang: $('#kelompok_dagang').val(),
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
                    is_active: $('#status_active').is(':checked') ? 'T' : 'F',
                    is_inventori: $('#is_inventori').is(':checked') ? 'F' : 'T',
                    is_beli: $('#is_beli').is(':checked') ? 'F' : 'T',
                    is_jual: $('#is_jual').is(':checked') ? 'F' : 'T',
                    hpp_average: $('#hpp_average').is(':checked') ? 'T' : 'F',
                    hpp_fifo: $('#hpp_fifo').is(':checked') ? 'T' : 'F',
                    hpp_lifo: $('#hpp_lifo').is(':checked') ? 'T' : 'F',
                    kode_akun_harga_pokok: $('#kode_akun_harga_pokok').val(),
                    kode_akun_penjualan: $('#kode_akun_penjualan').val(),
                    kode_akun_persediaan: $('#kode_akun_persediaan').val(),
                    kode_akun_pengiriman_beli: $('#kode_akun_pengiriman_beli').val(),
                    kode_akun_pengiriman_jual: $('#kode_akun_pengiriman_jual').val(),
                    kode_akun_retur_penjualan: $('#kode_akun_retur_penjualan').val()
                },
                success: function(resp) {
                    if (!resp.status) {
                        notify('error', 'Gagal', resp.message || 'Proses gagal.');
                        return;
                    }

                    notify('success', 'Berhasil', resp.message || 'Data master barang berhasil disimpan.');
                    loadList(currentSearch, currentKelompokBarang);
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
        resetKodeAkunDefaults();
        loadList('', '');
    });
</script>
