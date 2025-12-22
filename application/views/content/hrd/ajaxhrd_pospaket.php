<script>
    $('#tb_penerimaan_pos').DataTable({
        processing: true,
        serverSide: true,
        order: [],
        ajax: {
            url: "<?= base_url('lap_penerimaan_pos_serverside') ?>",
            type: "POST"
        }
    });

    $('#formInputPaket').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "<?= base_url('tambah_penerimaan_paket') ?>",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(res) {
                if (res.status) {
                    $('#modalInputPaket').modal('hide');
                    $('#tb_penerimaan_pos').DataTable().ajax.reload(null, false);
                } else {
                    alert(res.message);
                }
            }
        });
    });

    $('#tb_penerimaan_pos').on('click', '.btn-edit', function() {
        let id = $(this).data('id');

        $.get("<?= base_url('get_paket_by_id/') ?>" + id, function(res) {
            let d = JSON.parse(res);

            $('#edit_id').val(d.id);
            $('#edit_tanggal').val(d.tanggal);
            $('#edit_kd_penerima').val(d.kd_penerima);
            $('#edit_keterangan_1').val(d.keterangan_1);
            $('#edit_tanggal_terima_1').val(d.tanggal_terima_1);
            $('#edit_jam_terima_1').val(d.jam_terima_1);

            $('#modalEditPaket').modal('show');
        });
    });

    $('#formEditPaket').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "<?= base_url('edit_penerimaan_paket') ?>",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function() {
                $('#modalEditPaket').modal('hide');
                $('#tb_penerimaan_pos').DataTable().ajax.reload(null, false);
            }
        });
    });



    $('#tb_penerimaan_pos').on('click', '.btn-hapus', function() {
        let id = $(this).data('id');
        $('#hapus_id').val(id);
        $('#modalHapus').modal('show');
    });

    $('#tb_penerimaan_pos').on('click', '.btn-hapus', function() {
        let id = $(this).data('id');

        console.log('ID HAPUS:', id); // debugging sanity check

        $('#hapus_id').val(id);
        $('#modalHapus').modal('show');
    });

    $('#btnHapus').click(function() {
        let id = $('#hapus_id').val();

        if (!id) {
            alert('ID tidak ditemukan');
            return;
        }

        $.ajax({
            url: "<?= base_url('hapus_penerimaan_paket') ?>",
            type: "POST",
            data: {
                id: id
            },
            dataType: "json",
            success: function(res) {
                console.log(res);

                if (res.status === true) {
                    $('#modalHapus').modal('hide');
                    $('#tb_penerimaan_pos').DataTable().ajax.reload(null, false);
                } else {
                    alert('Gagal menghapus data');
                }
            }
        });
    });


    $('#modalKonfirmasi').on('show.bs.modal', function(e) {
        $('#konfirmasi_id').val($(e.relatedTarget).data('id'));
    });
</script>