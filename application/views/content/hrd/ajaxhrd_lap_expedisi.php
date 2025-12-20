<script>
    $(document).ready(function() {
        $('#tb_lap_expedisi').DataTable({
            processing: true,
            serverSide: true,
            order: [],
            ajax: {
                url: "<?= base_url('lap_expedisi_serverside') ?>",
                type: "POST"
            },
            columnDefs: [{
                targets: -1,
                orderable: false
            }]
        });
    });

    // EDIT CLICK
    $('#tb_lap_expedisi').on('click', '.btn-edit', function() {
        let id = $(this).data('id');

        $.get("<?= base_url('get_expedisi_by_id/') ?>" + id, function(res) {
            let d = JSON.parse(res);

            $('#edit_id').val(d.id);
            $('#edit_tanggal').val(d.tanggal);
            $('#edit_jammasuk').val(d.jammasuk);
            $('#edit_jamkeluar').val(d.jamkeluar);
            $('#edit_nopol').val(d.nopol);
            $('#edit_namadriver').val(d.namadriver);
            $('#edit_notlpndriver').val(d.notlpndriver);
            $('#edit_perusahaanpengirim').val(d.perusahaanpengirim);
            $('#edit_namabarang').val(d.namabarang);
            $('#edit_jumlahbarang').val(d.jumlahbarang);
            $('#edit_keterangan').val(d.keterangan);

            $('#modalEditExpedisi').modal('show');
        });
    });

    // SUBMIT EDIT
    $('#formEditExpedisi').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "<?= base_url('edit_lap_expedisi') ?>",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(res) {
                if (res.status) {
                    $('#modalEditExpedisi').modal('hide');
                    $('#tb_lap_expedisi').DataTable().ajax.reload(null, false);
                }
            }
        });
    });


    // HAPUS
    $('#tb_lap_expedisi').on('click', '.btn-hapus', function() {
        $('#hapus_id').val($(this).data('id'));
        $('#modalHapusExpedisi').modal('show');
    });

    $('#btnHapusExpedisi').click(function() {
        $.post("<?= base_url('hapus_lap_expedisi') ?>", {
            id: $('#hapus_id').val()
        }, function() {
            $('#modalHapusExpedisi').modal('hide');
            $('#tb_lap_expedisi').DataTable().ajax.reload(null, false);
        });
    });
</script>