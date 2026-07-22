<script>
    $(function() {
        const urls = {
            lpbSelect: "<?= base_url('ics/retur/pembelian/adjustment/lpb_select2') ?>",
            lpbDetail: "<?= base_url('ics/retur/pembelian/adjustment/lpb_detail') ?>",
            postAdjustment: "<?= base_url('ics/retur/pembelian/adjustment/post') ?>"
        };

        const $lpb = $('#select_lpb_adjustment');
        const $detailBody = $('#lpb_adjustment_detail_table tbody');

        function esc(value) {
            return String(value == null ? '' : value).replace(/[&<>"'`=\/]/g, function(s) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                    '/': '&#x2F;',
                    '`': '&#x60;',
                    '=': '&#x3D;'
                })[s];
            });
        }

        function numberValue(value) {
            return parseFloat(value || 0) || 0;
        }

        function money(value) {
            return numberValue(value).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function notify(message) {
            alert(message);
        }

        function updateDiff($tr) {
            const hargaSalah = numberValue($tr.data('harga-salah'));
            const hargaBenar = numberValue($tr.find('.js-harga-benar').val());
            $tr.find('.js-selisih').text(money(hargaBenar - hargaSalah));
        }

        $lpb.select2({
            theme: 'bootstrap4',
            placeholder: 'Cari supplier / PO / nomor LPB',
            allowClear: true,
            ajax: {
                url: urls.lpbSelect,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { term: params.term || '' };
                },
                processResults: function(data) {
                    return { results: data || [] };
                },
                cache: true
            }
        });

        function renderRows(rows) {
            $detailBody.empty();
            if (!rows || !rows.length) {
                $detailBody.append('<tr><td colspan="7" class="text-center text-muted">Detail LPB tidak ditemukan.</td></tr>');
                return;
            }

            rows.forEach(function(row) {
                const hargaSalah = numberValue(row.harga_salah);
                $detailBody.append(`
                    <tr data-id-detail-lpb="${esc(row.id_detail_lpb)}" data-harga-salah="${hargaSalah}">
                        <td>${esc(row.kd_barang)}</td>
                        <td>${esc(row.nama_barang)}</td>
                        <td class="text-right">${money(row.qty_diterima)}</td>
                        <td class="text-right">${money(hargaSalah)}</td>
                        <td>
                            <input type="number" min="0" step="0.0001" class="form-control form-control-sm js-harga-benar" value="${hargaSalah}">
                        </td>
                        <td class="text-right js-selisih">0,00</td>
                        <td class="text-center">${esc(row.kelompok_dagang || '-')}</td>
                    </tr>
                `);
            });
        }

        $lpb.on('change', function() {
            const idLpb = $lpb.val();
            $detailBody.html('<tr><td colspan="7" class="text-center text-muted">Memuat detail LPB...</td></tr>');
            if (!idLpb) {
                $detailBody.html('<tr><td colspan="7" class="text-center text-muted">Pilih LPB salah terlebih dahulu.</td></tr>');
                return;
            }

            $.ajax({
                url: urls.lpbDetail,
                type: 'GET',
                dataType: 'json',
                data: { id_lpb: idLpb },
                success: function(res) {
                    renderRows(res && res.rows ? res.rows : []);
                },
                error: function() {
                    renderRows([]);
                }
            });
        });

        $detailBody.on('input', '.js-harga-benar', function() {
            updateDiff($(this).closest('tr'));
        });

        $('#btn_post_adjustment_lpb').on('click', function() {
            const details = [];
            let hasDiff = false;

            $('#lpb_adjustment_detail_table tbody tr[data-id-detail-lpb]').each(function() {
                const $tr = $(this);
                const hargaSalah = numberValue($tr.data('harga-salah'));
                const hargaBenar = numberValue($tr.find('.js-harga-benar').val());
                if (hargaBenar > 0 && Math.abs(hargaBenar - hargaSalah) > 0.0001) {
                    hasDiff = true;
                }
                details.push({
                    id_detail_lpb_salah: $tr.data('id-detail-lpb'),
                    harga_benar: hargaBenar
                });
            });

            if (!$lpb.val() || !details.length) {
                notify('Pilih LPB salah dan isi harga invoice benar.');
                return;
            }
            if (!hasDiff) {
                notify('Minimal satu harga invoice benar harus berbeda dari harga LPB salah.');
                return;
            }
            if (!confirm('Posting adjustment harga LPB sekarang?')) {
                return;
            }

            $.ajax({
                url: urls.postAdjustment,
                type: 'POST',
                dataType: 'json',
                data: {
                    id_lpb: $lpb.val(),
                    tanggal_adjustment: $('#tanggal_adjustment').val(),
                    alasan_adjustment: $('#alasan_adjustment').val(),
                    details: JSON.stringify(details)
                },
                success: function(res) {
                    notify(res && res.message ? res.message : 'Adjustment diproses.');
                    if (res && res.status) {
                        location.reload();
                    }
                },
                error: function() {
                    notify('Server error saat posting adjustment harga LPB.');
                }
            });
        });

        $('#adjustment_lpb_table').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                { targets: [9], className: 'text-center text-nowrap' },
                { targets: [6, 7, 8], className: 'text-right text-nowrap' }
            ]
        });
    });
</script>
