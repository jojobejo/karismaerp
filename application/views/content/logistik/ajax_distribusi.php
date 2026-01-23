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

        $('#filter_tanggal_driver').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            }
        });

        $('#filter_tanggal_driver').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(
                picker.startDate.format('YYYY-MM-DD') +
                ' - ' +
                picker.endDate.format('YYYY-MM-DD')
            ).trigger('change');
        });

        $('#filter_tanggal_driver').on('cancel.daterangepicker', function() {
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
<script>
    $(function() {

        function loadMatrix() {
            $.ajax({
                url: "<?= base_url('logistik/distibusi/driver_rute_matrix') ?>",
                type: "POST",
                dataType: "json",
                data: {
                    tanggal: $('#filter_tanggal_driver').val()
                },
                success: function(res) {

                    let thead = `<tr><th>Driver</th>`;
                    res.rute.forEach(r => {
                        thead += `<th>${r.kd_rute}</th>`;
                    });
                    thead += `</tr>`;
                    $('#thead_rute').html(thead);

                    let tbody = '';
                    res.data.forEach(d => {
                        tbody += `<tr><td>${d.nama_driver}</td>`;
                        res.rute.forEach(r => {
                            let val = d.rute[r.kd_rute] ?? 0;
                            tbody += `<td class="text-center">${val}</td>`;
                        });
                        tbody += `</tr>`;
                    });

                    $('#tbody_driver').html(tbody);
                }
            });
        }

        $('#filter_tanggal_driver').on('change', loadMatrix);
    });
</script>