<script>
    $(function() {
        $('#filter_tanggal').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            }
        });

        $('#filter_tanggal').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(
                picker.startDate.format('YYYY-MM-DD') +
                ' - ' +
                picker.endDate.format('YYYY-MM-DD')
            ).trigger('change');
        });

        $('#filter_tanggal').on('cancel.daterangepicker', function() {
            $(this).val('').trigger('change');
        });
    });
</script>

<script>
    $(function() {

        function loadDistribusi() {
            $.ajax({
                url: "<?= base_url('logistik/distibusi/get_ploting_rute') ?>",
                type: "POST",
                dataType: "json",
                data: {
                    rute: $('#s_rute').val(),
                    tanggal: $('#filter_tanggal').val()
                },
                success: function(res) {
                    let html = '';

                    if (res.length === 0) {
                        html = `<tr>
                                <td colspan="2" class="text-center">
                                    Data tidak ditemukan
                                </td>
                            </tr>`;
                    } else {
                        res.forEach(row => {
                            html += `
                            <tr>
                                <td>${row.nama}</td>
                                <td>${row.tanggal_pengiriman}</td>
                            </tr>`;
                        });
                    }
                    $('#result_data').html(html);
                }
            });
        }
        $('#s_rute, #filter_tanggal').on('change', loadDistribusi);
    });
</script>