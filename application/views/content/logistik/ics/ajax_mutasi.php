<script>
    $('#filter_tanggal').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD'
        }
    });

    $('#btnFilter').on('click', function() {
        $.get('<?= base_url("ics/ajax_filter_mutasi") ?>', {
            gudang: $('#filter_gudang').val(),
            tanggal: $('#filter_tanggal').val()
        }, function(html) {
            $('#mutasi_barang tbody').html(html);
        });
    });

    $('.btn-rollback').click(function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Rollback Mutasi?',
            text: 'Saldo akan dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Rollback'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ics/mutasi/rollback', {
                    id: id
                }, function() {
                    Swal.fire('Sukses', 'Mutasi di rollback', 'success');
                    location.reload();
                });
            }
        })
    })


    $('.btn-unpost').click(function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Unpost Mutasi?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Unpost'
        }).then((res) => {
            if (res.isConfirmed) {
                $.post('ics/mutasi/unpost', {
                    id: id
                }, () => {
                    Swal.fire('OK', 'Status UNPOST', 'success');
                    location.reload();
                });
            }
        })
    })
    
</script>