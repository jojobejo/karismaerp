<script>
    $(document).ready(function() {
        const $tableBody = $('#tbl_kirim_do tbody');

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : value).html();
        }

        function renderRows(rows) {
            if (!rows || rows.length === 0) {
                return `
                    <tr>
                        <td colspan="5" class="text-center text-muted">Data tidak ditemukan</td>
                    </tr>
                `;
            }

            let html = '';
            rows.forEach((row, index) => {
                const rute = escapeHtml(row.rute ? row.rute : '-');
                const total = escapeHtml(row.total_faktur ? row.total_faktur : '0');
                const terkirim = escapeHtml(row.total_faktur_terkirim ? row.total_faktur_terkirim : '0');
                const pending = escapeHtml(row.total_faktur_pending ? row.total_faktur_pending : '0');
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${rute}</td>
                        <td>${total}</td>
                        <td>${terkirim}</td>
                        <td>${pending}</td>
                    </tr>
                `;
            });
            return html;
        }

        function resetDataTable() {
            if ($.fn.DataTable.isDataTable('#tbl_kirim_do')) {
                $('#tbl_kirim_do').DataTable().destroy();
            }
        }

        function initDataTable() {
            $('#tbl_kirim_do').DataTable({
                paging: true,
                lengthChange: false,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true
            });
        }

        function loadKirimDo() {
            const tanggal = $('#filter_tanggal_kirim_do').val();

            resetDataTable();
            $tableBody.html(`
                <tr>
                    <td colspan="5" class="text-center text-muted">Memuat data...</td>
                </tr>
            `);

            $.ajax({
                url: "<?= base_url('logistik/distibusi/ajax_total_kirim_do') ?>",
                type: "POST",
                dataType: "json",
                data: {
                    tanggal: tanggal
                },
                success: function(res) {
                    const rows = res && res.data ? res.data : [];
                    $tableBody.html(renderRows(rows));
                    initDataTable();
                },
                error: function() {
                    $tableBody.html(`
                        <tr>
                            <td colspan="5" class="text-center text-danger">Gagal memuat data dari server</td>
                        </tr>
                    `);
                }
            });
        }

        $('#filter_tanggal_kirim_do').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            }
        });

        $('#filter_tanggal_kirim_do').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(
                picker.startDate.format('YYYY-MM-DD') +
                ' - ' +
                picker.endDate.format('YYYY-MM-DD')
            );
        });

        $('#filter_tanggal_kirim_do').on('cancel.daterangepicker', function() {
            $(this).val('');
        });

        $('#btn_filter_kirim_do').on('click', loadKirimDo);
        $('#btn_reload_kirim_do').on('click', function() {
            $('#filter_tanggal_kirim_do').val('');
            loadKirimDo();
        });

        loadKirimDo();
    });
</script>
