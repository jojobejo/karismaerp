<!-- jQuery -->
<script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
<!-- Bootstrap 4 -->
<script src="<?php echo base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- DataTables -->
<script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/jszip/jszip.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/pdfmake/pdfmake.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/pdfmake/vfs_fonts.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.html5.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.print.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') ?>"></script>
<!-- AdminLTE App -->
<script src="<?php echo base_url('assets/dist/js/adminlte.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        bsCustomFileInput.init();

        var state = {
            page: 1,
            perPage: parseInt($('#catalogPerPageSelect').val(), 10) || 12,
            search: '',
            filterFokus: '',
            filterOnline: '',
            sort: $('#catalogSortSelect').val() || 'fokus'
        };
        var activeRequest = null;
        var searchDebounce = null;
        var defaultPreviewImage = '<?= base_url('images/Karisma.png') ?>';
        var loadCatalogUrl = '<?= base_url('Sales/getBarangAjax') ?>';
        var saveBahanUrl = '<?= base_url('Sales/updateBahanAktif') ?>';
        var saveGambarUrl = '<?= base_url('Sales/updateGambarProduk') ?>';
        var catalogAnchor = $('.catalog-shell');

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        }

        function renderStatusBadge(isActive, label) {
            if (isActive) {
                return '<span class="status-badge is-on"><i class="fa fa-check-circle"></i>' + label + '</span>';
            }

            return '<span class="status-badge is-off"><i class="fa fa-minus-circle"></i>' + label + '</span>';
        }

        function applyOnlineFilterButtonState(button) {
            $('.btn-filter-online').each(function() {
                $(this).removeClass('active');

                var online = $(this).data('online');
                if (online == 'shopee') {
                    $(this).css({ 'background': 'white', 'color': '#ee4d2d' });
                } else if (online == 'tokopedia') {
                    $(this).css({ 'background': 'white', 'color': '#42b549' });
                } else if (online == 'kiushop') {
                    $(this).css({ 'background': 'white', 'color': '#6096B4' });
                }
            });

            button.addClass('active');

            var selected = button.data('online');
            if (selected == 'shopee') {
                button.css({ 'background': '#ee4d2d', 'color': 'white' });
            } else if (selected == 'tokopedia') {
                button.css({ 'background': '#42b549', 'color': 'white' });
            } else if (selected == 'kiushop') {
                button.css({ 'background': '#6096B4', 'color': 'white' });
            }
        }

        function setLoading(isLoading) {
            $('#catalogLoadingState, #catalogLoadingGrid').toggleClass('is-visible', isLoading);
            $('#catalogLoadingGrid').toggle(isLoading);
            if (isLoading) {
                $('#catalogGrid').empty();
                $('#catalogEmptyState, #catalogErrorState').removeClass('is-visible');
            }
        }

        function setSearchClearVisibility() {
            $('#catalogSearchClear').toggleClass('is-visible', state.search !== '');
        }

        function scrollCatalogToTop() {
            if (!catalogAnchor.length) {
                return;
            }

            $('html, body').animate({
                scrollTop: Math.max(catalogAnchor.offset().top - 18, 0)
            }, 260);
        }

        function renderCatalog(items, meta) {
            var html = '';

            $.each(items, function(_, item) {
                var focusText = item.produk_fokus ? item.produk_fokus : '-';

                html += ''
                    + '<article class="catalog-card">'
                    + '  <div class="catalog-card-head">'
                    + '    <img src="' + escapeHtml(item.image_url) + '" alt="' + escapeHtml(item.nama_barang) + '" class="catalog-image btn-preview-produk"'
                    + '      data-image-url="' + escapeHtml(item.image_url) + '"'
                    + '      data-nama="' + escapeHtml(item.nama_barang) + '"'
                    + '      data-kode="' + escapeHtml(item.kode_barang) + '">'
                    + '    <div class="catalog-body">'
                    + '      <span class="focus-pill">Fokus ' + escapeHtml(focusText) + '</span>'
                    + '      <h4 class="catalog-name">' + escapeHtml(item.nama_barang) + '</h4>'
                    + '      <p class="catalog-code">Kode: ' + escapeHtml(item.kode_barang) + '</p>'
                    + '      <p class="catalog-supplier">Supplier: ' + escapeHtml(item.nama_suplier || '-') + '</p>'
                    + '    </div>'
                    + '  </div>'
                    + '  <div class="catalog-meta">'
                    + '    <div class="meta-box">'
                    + '      <span class="meta-label">Kelompok Bahan</span>'
                    + '      <div class="meta-value">' + escapeHtml(item.kelompok || '-') + '</div>'
                    + '    </div>'
                    + '    <div class="meta-box">'
                    + '      <span class="meta-label">Bahan Aktif</span>'
                    + '      <div class="meta-value">' + escapeHtml(item.bahan_aktif || '-') + '</div>'
                    + '    </div>'
                    + '  </div>'
                    + '  <div class="status-row">'
                    +       renderStatusBadge(item.shopee == 1, 'Shopee')
                    +       renderStatusBadge(item.tokopedia == 1, 'Tokopedia')
                    +       renderStatusBadge(item.kiushop == 1, 'KiuShop')
                    + '  </div>'
                    + '  <div class="catalog-actions">'
                    + '    <a href="' + escapeHtml(item.pricelist_url) + '" class="btn btn-primary btn-sm" target="_blank" title="Lihat Pricelist"><i class="fa fa-eye mr-1"></i>Lihat</a>'
                    + '    <button type="button" class="btn btn-warning btn-sm btn-edit-bahan"'
                    + '      data-id="' + escapeHtml(item.id_barang) + '"'
                    + '      data-kode="' + escapeHtml(item.kode_barang) + '"'
                    + '      data-nama="' + escapeHtml(item.nama_barang) + '"'
                    + '      data-kelompok="' + escapeHtml(item.kelompok || '') + '"'
                    + '      data-bahan="' + escapeHtml(item.bahan_aktif || '') + '"'
                    + '      title="Edit Bahan Aktif"><i class="fa fa-pencil-alt mr-1"></i>Bahan</button>'
                    + '    <button type="button" class="btn btn-info btn-sm btn-edit-gambar"'
                    + '      data-id="' + escapeHtml(item.id_barang) + '"'
                    + '      data-kode="' + escapeHtml(item.kode_barang) + '"'
                    + '      data-nama="' + escapeHtml(item.nama_barang) + '"'
                    + '      data-image="' + escapeHtml(item.gbr_produk || '') + '"'
                    + '      title="Edit Gambar Produk"><i class="fa fa-image mr-1"></i>Gambar</button>'
                    + '  </div>'
                    + '</article>';
            });

            $('#catalogGrid').html(html);
            $('#catalogLoadingState, #catalogLoadingGrid, #catalogErrorState').removeClass('is-visible').hide();
            $('#catalogEmptyState').toggleClass('is-visible', items.length === 0);
            $('#catalogResultCount').text(meta.total_items || 0);

            var caption = meta.total_items > 0
                ? 'Menampilkan katalog yang relevan dengan filter aktif.'
                : 'Belum ada produk yang cocok dengan kriteria saat ini.';
            $('#catalogResultCaption').text(caption);

            if (meta.total_items > 0) {
                $('#catalogPagingMeta').text('Menampilkan ' + meta.from + ' - ' + meta.to + ' dari ' + meta.total_items + ' produk.');
                $('#catalogPaginationInfo').text('Halaman ' + meta.page + ' dari ' + meta.total_pages);
            } else {
                $('#catalogPagingMeta').text('Tidak ada data untuk ditampilkan.');
                $('#catalogPaginationInfo').text('Halaman 0 dari 0');
            }

            $('#catalogPageIndicator').text(meta.page);
            $('#catalogPrevButton').prop('disabled', meta.page <= 1);
            $('#catalogNextButton').prop('disabled', meta.page >= meta.total_pages || meta.total_items === 0);
        }

        function showCatalogError(message) {
            $('#catalogLoadingState, #catalogLoadingGrid').removeClass('is-visible').hide();
            $('#catalogGrid').empty();
            $('#catalogEmptyState').removeClass('is-visible');
            $('#catalogErrorState').addClass('is-visible');
            $('#catalogResultCaption').text(message || 'Terjadi kendala saat memuat katalog.');
            $('#catalogPagingMeta').text('Katalog belum berhasil dimuat.');
        }

        function loadCatalog(options) {
            options = options || {};
            if (options.resetPage) {
                state.page = 1;
            }
            var shouldScrollToTop = options.scrollToTop === true;

            if (activeRequest && activeRequest.readyState !== 4) {
                activeRequest.abort();
            }

            setLoading(true);
            setSearchClearVisibility();

            activeRequest = $.ajax({
                url: loadCatalogUrl,
                type: 'GET',
                dataType: 'json',
                data: {
                    page: state.page,
                    per_page: state.perPage,
                    search: state.search,
                    filter_fokus: state.filterFokus,
                    filter_online: state.filterOnline,
                    sort: state.sort
                },
                success: function(response) {
                    if (!response || response.status !== true) {
                        showCatalogError('Server belum mengembalikan data katalog dengan benar.');
                        return;
                    }

                    renderCatalog(response.items || [], response.meta || {
                        page: 1,
                        total_pages: 1,
                        total_items: 0,
                        from: 0,
                        to: 0
                    });

                    if (shouldScrollToTop) {
                        scrollCatalogToTop();
                    }
                },
                error: function(xhr, status) {
                    if (status === 'abort') {
                        return;
                    }

                    showCatalogError('Koneksi ke data katalog terputus. Silakan coba lagi.');
                }
            });
        }

        function showFeedback(selector, message, isSuccess) {
            $(selector)
                .removeClass('is-success is-error')
                .addClass(isSuccess ? 'is-success' : 'is-error')
                .show()
                .text(message);
        }

        function resetFeedback(selector) {
            $(selector)
                .removeClass('is-success is-error')
                .hide()
                .text('');
        }

        $(document).on('click', '.btn-edit-bahan', function() {
            resetFeedback('#feedbackBahanAktif');
            $('#edit_bahan_id_barang').val($(this).data('id'));
            $('#edit_bahan_kode_barang').text($(this).data('kode'));
            $('#edit_bahan_nama_barang').text($(this).data('nama'));
            $('#edit_kelompok').val($(this).data('kelompok'));
            $('#edit_bahan_aktif').val($(this).data('bahan'));
            $('#modalEditBahanAktif').modal('show');
        });

        $(document).on('click', '.btn-edit-gambar', function() {
            var imageName = $(this).data('image');
            var previewSrc = imageName && imageName !== '-'
                ? '<?= base_url('images/produk/') ?>' + imageName
                : defaultPreviewImage;

            resetFeedback('#feedbackGambarProduk');
            $('#formEditGambarProduk')[0].reset();
            $('.custom-file-label[for="gambar_produk"]').text('Pilih file gambar');
            $('#edit_gambar_id_barang').val($(this).data('id'));
            $('#edit_gambar_kode_barang').text($(this).data('kode'));
            $('#edit_gambar_nama_barang').text($(this).data('nama'));
            $('#previewGambarProduk').attr('src', previewSrc);
            $('#modalEditGambarProduk').modal('show');
        });

        $(document).on('click', '.btn-preview-produk', function() {
            $('#productPreviewImage').attr('src', $(this).data('image-url') || defaultPreviewImage);
            $('#productPreviewName').text($(this).data('nama') || 'Produk');
            $('#productPreviewCode').text($(this).data('kode') || '-');
            $('#modalPreviewProduk').modal('show');
        });

        $('.btn-filter-fokus').on('click', function() {
            $('.btn-filter-fokus').removeClass('active');
            $(this).addClass('active');
            state.filterFokus = $(this).data('fokus') || '';
            loadCatalog({ resetPage: true });
        });

        $(document).on('click', '.btn-filter-online', function() {
            var button = $(this);
            applyOnlineFilterButtonState(button);
            state.filterOnline = button.data('online') || '';
            loadCatalog({ resetPage: true });
        });

        $('#catalogSortSelect').on('change', function() {
            state.sort = $(this).val() || 'fokus';
            loadCatalog({ resetPage: true });
        });

        $('#catalogPerPageSelect').on('change', function() {
            state.perPage = parseInt($(this).val(), 10) || 12;
            loadCatalog({ resetPage: true });
        });

        $('#catalogSearchInput').on('input', function() {
            state.search = $.trim($(this).val());
            setSearchClearVisibility();
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(function() {
                loadCatalog({ resetPage: true });
            }, 320);
        });

        $('#catalogSearchClear').on('click', function() {
            $('#catalogSearchInput').val('');
            state.search = '';
            setSearchClearVisibility();
            loadCatalog({ resetPage: true });
        });

        $('#catalogPrevButton').on('click', function() {
            if (state.page > 1) {
                state.page -= 1;
                loadCatalog({ scrollToTop: true });
            }
        });

        $('#catalogNextButton').on('click', function() {
            state.page += 1;
            loadCatalog({ scrollToTop: true });
        });

        $('#catalogRetryButton').on('click', function() {
            loadCatalog();
        });

        $('#gambar_produk').on('change', function() {
            var file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) {
                $('#previewGambarProduk').attr('src', defaultPreviewImage);
                return;
            }

            var reader = new FileReader();
            reader.onload = function(e) {
                $('#previewGambarProduk').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        });

        $('#formEditBahanAktif').on('submit', function(e) {
            e.preventDefault();
            resetFeedback('#feedbackBahanAktif');

            var $button = $('#btnSimpanBahanAktif');
            var originalHtml = $button.html();
            $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Menyimpan...');

            $.ajax({
                url: saveBahanUrl,
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status) {
                        showFeedback('#feedbackBahanAktif', response.message, true);
                        setTimeout(function() {
                            $('#modalEditBahanAktif').modal('hide');
                            loadCatalog();
                        }, 700);
                    } else {
                        showFeedback('#feedbackBahanAktif', response.message || 'Perubahan gagal disimpan.', false);
                    }
                },
                error: function() {
                    showFeedback('#feedbackBahanAktif', 'Terjadi kendala saat mengirim perubahan bahan aktif.', false);
                },
                complete: function() {
                    $button.prop('disabled', false).html(originalHtml);
                }
            });
        });

        $('#formEditGambarProduk').on('submit', function(e) {
            e.preventDefault();
            resetFeedback('#feedbackGambarProduk');

            var formData = new FormData(this);
            var $button = $('#btnSimpanGambarProduk');
            var originalHtml = $button.html();
            $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Mengunggah...');

            $.ajax({
                url: saveGambarUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        showFeedback('#feedbackGambarProduk', response.message, true);
                        if (response.image_url) {
                            $('#previewGambarProduk').attr('src', response.image_url);
                        }
                        setTimeout(function() {
                            $('#modalEditGambarProduk').modal('hide');
                            loadCatalog();
                        }, 700);
                    } else {
                        showFeedback('#feedbackGambarProduk', response.message || 'Gambar gagal diperbarui.', false);
                    }
                },
                error: function() {
                    showFeedback('#feedbackGambarProduk', 'Upload gambar gagal diproses. Silakan coba lagi.', false);
                },
                complete: function() {
                    $button.prop('disabled', false).html(originalHtml);
                }
            });
        });

        $('#modalEditBahanAktif, #modalEditGambarProduk').on('hidden.bs.modal', function() {
            resetFeedback('#feedbackBahanAktif');
            resetFeedback('#feedbackGambarProduk');
        });

        loadCatalog();
    });
</script>

</body>

</html>
