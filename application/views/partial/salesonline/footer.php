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

    var filterFokus  = '';
    var filterOnline = '';

    var table = $('#example1, #tableSalesOnline').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "responsive": true,
        "ajax": {
            "url": "<?= base_url('salesonline/getBarang') ?>",
            "type": "POST",
            "data": function(d) {
                d.filter_fokus  = filterFokus;
                d.filter_online = filterOnline; // ← kirim filter online
            }
        },
        "drawCallback": function() {

            $('.btn-edit').off('click').on('click', function() {
                $('#edit_id_bar').val($(this).data('id'));
                $('#edit_kode_barang').val($(this).data('kode'));
                $('#edit_nama_barang').val($(this).data('nama'));
                $('#edit_produk_fokus').val($(this).data('fokus'));
                $('#edit_nama_suplier').val($(this).data('suplier'));
                $('#edit_katagori').val($(this).data('katagori'));
                $('#modalEdit').modal('show');
            });

            $('.btn-hapus').off('click').on('click', function() {
                $('#hapus_nama_barang').text($(this).data('nama'));
                $('#hapus_link').attr('href', '<?= base_url('salesonline/deleteDat/') ?>' + $(this).data('id'));
                $('#modalHapus').modal('show');
            });

            $('.btn-online').off('click').on('click', function() {
                $('#online_id_barang').val($(this).data('id'));
                $('#online_kode_barang').val($(this).data('kode'));
                $('#online_nama_barang').text($(this).data('nama'));
                $('#switch_shopee').prop('checked',    $(this).data('shopee')    == 1);
                $('#switch_tokopedia').prop('checked', $(this).data('tokopedia') == 1);
                $('#switch_kiushop').prop('checked',   $(this).data('kiushop')   == 1);
                $('#modalOnlineShop').modal('show');
            });
        }
    });

    // Filter Produk Fokus
    $(document).on('click', '.btn-filter-fokus', function() {
        $('.btn-filter-fokus').removeClass('active');
        $(this).addClass('active');
        filterFokus = $(this).data('fokus');
        table.ajax.reload();
    });

    // Filter Online Shop ← tambahan baru
    $(document).on('click', '.btn-filter-online', function() {
        $('.btn-filter-online').each(function() {
            $(this).removeClass('active');
            // Reset style ke default
            var online = $(this).data('online');
            if (online == 'shopee') {
                $(this).css({'background':'white', 'color':'#ee4d2d'});
            } else if (online == 'tokopedia') {
                $(this).css({'background':'white', 'color':'#42b549'});
            } else if (online == 'kiushop') {
                $(this).css({'background':'white', 'color':'#6096B4'});
            }
        });

        $(this).addClass('active');

        // Highlight tombol aktif
        var online = $(this).data('online');
        if (online == 'shopee') {
            $(this).css({'background':'#ee4d2d', 'color':'white'});
        } else if (online == 'tokopedia') {
            $(this).css({'background':'#42b549', 'color':'white'});
        } else if (online == 'kiushop') {
            $(this).css({'background':'#6096B4', 'color':'white'});
        }

        filterOnline = online;
        table.ajax.reload();
    });

});
</script>

</body>

</html>