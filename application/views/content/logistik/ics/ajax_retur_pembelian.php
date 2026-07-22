<script>
    $(function() {
        const urls = {
            lpbSelect: "<?= base_url('ics/retur/pembelian/lpb_select2') ?>",
            lpbDetail: "<?= base_url('ics/retur/pembelian/lpb_detail') ?>",
            createDraft: "<?= base_url('ics/retur/pembelian/create_draft') ?>",
            submit: "<?= base_url('ics/retur/pembelian/submit') ?>",
            verifyPurchasing: "<?= base_url('ics/retur/pembelian/verify_purchasing') ?>",
            verifyAccounting: "<?= base_url('ics/retur/pembelian/verify_accounting') ?>",
            post: "<?= base_url('ics/retur/pembelian/post') ?>",
            voidDoc: "<?= base_url('ics/retur/pembelian/void') ?>"
        };

        const $lpb = $('#select_lpb_retur');
        const $detailBody = $('#lpb_return_detail_table tbody');

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

        function money(value) {
            const num = parseFloat(value || 0);
            return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function notify(message) {
            alert(message);
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
                $detailBody.append('<tr><td colspan="10" class="text-center text-muted">Detail LPB tidak ditemukan.</td></tr>');
                return;
            }

            rows.forEach(function(row) {
                const qtyDiterima = parseFloat(row.qty_diterima || 0);
                const qtyReturSebelumnya = parseFloat(row.qty_retur_sebelumnya || 0);
                const qtyOnHand = parseFloat(row.qty_on_hand || 0);
                const maxQty = Math.max(0, Math.min(qtyDiterima - qtyReturSebelumnya, qtyOnHand));
                const disabled = maxQty <= 0 ? 'disabled' : '';

                $detailBody.append(`
                    <tr data-id-detail-lpb="${esc(row.id_detail_lpb)}">
                        <td>${esc(row.kd_barang)}</td>
                        <td>${esc(row.nama_barang)}</td>
                        <td>${esc(row.no_lot || '-')}</td>
                        <td>${esc(row.expired_date || '-')}</td>
                        <td class="text-right">${money(qtyDiterima)}</td>
                        <td class="text-right">${money(qtyReturSebelumnya)}</td>
                        <td class="text-right">${money(qtyOnHand)}</td>
                        <td class="text-right">${money(row.harga_satuan)}</td>
                        <td>
                            <input type="number" min="0" step="0.01" max="${maxQty}" class="form-control form-control-sm js-qty-retur" ${disabled}>
                            <small class="text-muted">Maks ${money(maxQty)}</small>
                        </td>
                        <td><input type="text" class="form-control form-control-sm js-alasan-item" ${disabled}></td>
                    </tr>
                `);
            });
        }

        $lpb.on('change', function() {
            const idLpb = $lpb.val();
            $detailBody.html('<tr><td colspan="10" class="text-center text-muted">Memuat detail LPB...</td></tr>');
            if (!idLpb) {
                $detailBody.html('<tr><td colspan="10" class="text-center text-muted">Pilih LPB final terlebih dahulu.</td></tr>');
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

        $('#btn_create_draft_retur').on('click', function() {
            const details = [];
            $('#lpb_return_detail_table tbody tr[data-id-detail-lpb]').each(function() {
                const $tr = $(this);
                const qty = parseFloat($tr.find('.js-qty-retur').val() || 0);
                if (qty > 0) {
                    details.push({
                        id_detail_lpb: $tr.data('id-detail-lpb'),
                        qty_retur: qty,
                        alasan_retur: $tr.find('.js-alasan-item').val()
                    });
                }
            });

            if (!$lpb.val() || !details.length) {
                notify('Pilih LPB dan isi minimal satu qty retur.');
                return;
            }
            if (!confirm('Buat draft retur pembelian dari LPB ini?')) {
                return;
            }

            $.ajax({
                url: urls.createDraft,
                type: 'POST',
                dataType: 'json',
                data: {
                    id_lpb: $lpb.val(),
                    tanggal_retur: $('#tanggal_retur').val(),
                    jenis_penyelesaian: $('#jenis_penyelesaian').val(),
                    alasan_retur: $('#alasan_retur').val(),
                    details: JSON.stringify(details)
                },
                success: function(res) {
                    notify(res && res.message ? res.message : 'Draft diproses.');
                    if (res && res.status) {
                        location.reload();
                    }
                },
                error: function() {
                    notify('Server error saat membuat draft retur pembelian.');
                }
            });
        });

        function runAction(url, payload, confirmText) {
            if (confirmText && !confirm(confirmText)) {
                return;
            }
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: payload,
                success: function(res) {
                    notify(res && res.message ? res.message : 'Aksi selesai.');
                    if (res && res.status) {
                        location.reload();
                    }
                },
                error: function() {
                    notify('Server error saat memproses aksi retur.');
                }
            });
        }

        $('#retur_pembelian_table').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                { targets: [5, 9, 10], className: 'text-center text-nowrap' },
                { targets: [6, 7, 8], className: 'text-right text-nowrap' }
            ]
        });

        $('#retur_pembelian_table').on('click', '.js-retur-action', function() {
            const id = $(this).data('id');
            const action = $(this).data('action');
            if (!id) {
                notify('ID retur tidak valid.');
                return;
            }

            if (action === 'submit') {
                runAction(urls.submit, { id_retur_pembelian: id }, 'Submit draft retur pembelian?');
            } else if (action === 'verify_purchasing') {
                const catatanPurchasing = prompt('Catatan Purchasing:', '');
                runAction(urls.verifyPurchasing, { id_retur_pembelian: id, catatan: catatanPurchasing || '' }, 'Verifikasi Purchasing untuk retur ini?');
            } else if (action === 'verify_accounting') {
                const catatanAccounting = prompt('Catatan Accounting:', '');
                runAction(urls.verifyAccounting, { id_retur_pembelian: id, catatan: catatanAccounting || '' }, 'Verifikasi Accounting untuk retur ini?');
            } else if (action === 'post') {
                runAction(urls.post, { id_retur_pembelian: id }, 'Posting retur pembelian ke stock dan jurnal?');
            } else if (action === 'void') {
                const alasan = prompt('Alasan void/reversal:', '');
                if (!alasan) {
                    notify('Alasan void wajib diisi.');
                    return;
                }
                runAction(urls.voidDoc, { id_retur_pembelian: id, alasan: alasan }, 'Void retur posted dan buat reversal?');
            }
        });
    });
</script>
