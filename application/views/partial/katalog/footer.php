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
    var table;
    $(document).ready(function() {
        var filterFokus = '';
        var filterOnline = '';

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

            var online = button.data('online');
            if (online == 'shopee') {
                button.css({ 'background': '#ee4d2d', 'color': 'white' });
            } else if (online == 'tokopedia') {
                button.css({ 'background': '#42b549', 'color': 'white' });
            } else if (online == 'kiushop') {
                button.css({ 'background': '#6096B4', 'color': 'white' });
            }
        }

        function applyMobileCardFilter() {
            var visibleCount = 0;

            $('#mobileCardList .katalog-mobile-card').each(function() {
                var card = $(this);
                var fokus = String(card.data('fokus') || '').trim();
                var online = String(card.data('online') || '').trim();

                var fokusMatch = filterFokus === '' || fokus === filterFokus;
                var onlineMatch = false;

                if (filterOnline === '') {
                    onlineMatch = true;
                } else if (filterOnline === 'kosong') {
                    onlineMatch = online === 'kosong';
                } else {
                    onlineMatch = online.indexOf(filterOnline) !== -1;
                }

                if (fokusMatch && onlineMatch) {
                    visibleCount++;
                    card.show();
                } else {
                    card.hide();
                }
            });

            if ($('#mobileEmptyState').length) {
                $('#mobileEmptyState').toggle(visibleCount === 0);
            }
        }

        table = $('#example1, #tableSalesOnline').DataTable({
            "processing": true,
            "serverSide": true,
            "order": [],
            "responsive": false,
            "scrollX": true,
            "autoWidth": false,
            "ajax": {
                "url": "<?= base_url('katalog/getBarang') ?>",
                "type": "POST",
                "data": function(d) {
                    d.filter_fokus = filterFokus;
                    d.filter_online = filterOnline;
                }
            },
            "columnDefs": [
                { "visible": false, "targets": [0] },
                { "orderable": false, "targets": [6, 7, 8] },
                { "className": "dt-body-center", "targets": "_all" }
            ]
        });

        applyMobileCardFilter();

        $(window).on('resize', function() {
            if (table) {
                table.columns.adjust();
            }
        });

        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            e.stopPropagation();

            $('#edit_id_bar').val($(this).data('id'));
            $('#edit_kode_barang').val($(this).data('kode'));
            $('#edit_nama_barang').val($(this).data('nama'));
            $('#edit_produk_fokus').val($(this).data('fokus'));
            $('#edit_nama_suplier').val($(this).data('suplier'));
            $('#edit_katagori').val($(this).data('katagori'));
            $('#modalEdit').modal('show');
        });

        $(document).on('click', '.btn-hapus', function(e) {
            e.preventDefault();
            e.stopPropagation();

            $('#hapus_nama_barang').text($(this).data('nama'));
            $('#hapus_link').attr('href', '<?= base_url('katalog/deleteDat/') ?>' + $(this).data('id'));
            $('#modalHapus').modal('show');
        });

        $(document).on('click', '.btn-online', function(e) {
            e.preventDefault();
            e.stopPropagation();

            $('#online_id_barang').val($(this).data('id'));
            $('#online_kode_barang').val($(this).data('kode'));
            $('#online_nama_barang').text($(this).data('nama'));
            $('#switch_shopee').prop('checked', $(this).data('shopee') == 1);
            $('#switch_tokopedia').prop('checked', $(this).data('tokopedia') == 1);
            $('#switch_kiushop').prop('checked', $(this).data('kiushop') == 1);
            $('#modalOnlineShop').modal('show');
        });

        $(document).on('click', '.btn-filter-fokus', function() {
            $('.btn-filter-fokus').removeClass('active');
            $(this).addClass('active');
            filterFokus = $(this).data('fokus');
            table.ajax.reload();
            applyMobileCardFilter();
        });

        $(document).on('click', '.btn-filter-online', function() {
            var button = $(this);
            applyOnlineFilterButtonState(button);
            filterOnline = button.data('online');
            table.ajax.reload();
            applyMobileCardFilter();
        });
    });
</script>

</body>

</html>
