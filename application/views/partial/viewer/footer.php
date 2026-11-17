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

        var filterFokus  = '';
        var filterOnline = '';

        var table = $('#table').DataTable({ 
            "processing": true,
            "serverSide": true,
            "order": [],
            "responsive": true,
            "ajax": {
                "url": "<?= base_url('Viewer/getBarang') ?>",
                "type": "POST",
                "data": function(d) {
                    d.filter_fokus  = filterFokus;
                    d.filter_online = filterOnline;
                }
            },
            "columnDefs": [
                { "visible": false, "targets": [0] },
                { "orderable": false, "targets": [6,7,8] },
                { "className": "text-center", "targets": "_all" }
            ],
            "drawCallback": function() {

                $('.btn-online').off('click').on('click', function() {
                    $('#online_id_barang').val($(this).data('id'));
                    $('#online_kode_barang').val($(this).data('kode'));
                    $('#online_nama_barang').text($(this).data('nama'));
                    $('#switch_shopee').prop('checked', $(this).data('shopee') == 1);
                    $('#switch_tokopedia').prop('checked', $(this).data('tokopedia') == 1);
                    $('#switch_kiushop').prop('checked', $(this).data('kiushop') == 1);
                    $('#modalOnlineShop').modal('show');
                });
            }
        });

        // FILTER PRODUK FOKUS
        $('.btn-filter-fokus').click(function() {
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

    // Set the date we're counting down to
    var countDownDate = new Date("Mar 1, 2023 00:00:00").getTime();

    // Update the count down every 1 second
    var x = setInterval(function() {

        // Get today's date and time
        var now = new Date().getTime();

        // Find the distance between now and the count down date
        var distance = countDownDate - now;

        // Time calculations for days, hours, minutes and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Display the result in the element with id="demo"
        document.getElementById("demo").innerHTML = days + " " + "Hari" + " " + "-" + " " + hours + " " + "Jam" + " " +
            "-" + " " + minutes + " " + "Menit" + " " + "-" + " " + seconds + "s";

        // If the count down is finished, write some text
        if (distance < 0) {
            clearInterval(x);
            document.getElementById("demo").innerHTML = "EXPIRED";
        }
    }, 1000);
</script>

</body>

</html>