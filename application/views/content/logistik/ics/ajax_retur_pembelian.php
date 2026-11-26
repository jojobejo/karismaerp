<script>
    $(function() {
        const urls = {
            supplierSelect: "<?= base_url('ics/retur/pembelian/supplier_select2') ?>",
            barangSelect: "<?= base_url('ics/retur/pembelian/gudang_barang_select2') ?>",
            lotSelect: "<?= base_url('ics/retur/pembelian/gudang_lot_select2') ?>",
            expSelect: "<?= base_url('ics/retur/pembelian/gudang_exp_select2') ?>",
            draftList: "<?= base_url('ics/retur/pembelian/draft_list') ?>",
            draftAdd: "<?= base_url('ics/retur/pembelian/draft_add') ?>",
            draftDelete: "<?= base_url('ics/retur/pembelian/draft_delete') ?>",
            draftClear: "<?= base_url('ics/retur/pembelian/draft_clear') ?>",
            postDraft: "<?= base_url('ics/retur/pembelian/post_draft') ?>",
            detailItems: "<?= base_url('ics/retur/pembelian/monitoring/detail') ?>",
            submit: "<?= base_url('ics/retur/pembelian/submit') ?>",
            verifyPurchasing: "<?= base_url('ics/retur/pembelian/verify_purchasing') ?>",
            verifyAccounting: "<?= base_url('ics/retur/pembelian/verify_accounting') ?>",
            post: "<?= base_url('ics/retur/pembelian/post') ?>",
            voidDoc: "<?= base_url('ics/retur/pembelian/void') ?>"
        };

        const gudangReturId = $('#gudang_retur_id').val();
        const $supplier = $('#select_supplier_retur');
        const $barang = $('#select_barang_retur');
        const $kodeBarang = $('#kode_barang_retur');
        const $lot = $('#select_lot_retur');
        const $exp = $('#select_exp_retur');
        const $qty = $('#qty_retur_input');
        const $labelSatuan = $('#label_satuan_kecil');
        const $hintStok = $('#hint_stok_batch');
        const $btnAddDraft = $('#btn_add_draft_item');
        const $btnClearDraft = $('#btn_clear_draft');
        const $draftTbody = $('#draft_retur_detail_table tbody');
        const $panelPenyelesaian = $('#panel_penyelesaian_draft');
        const $badgeDraftCount = $('#badge_draft_count');
        const $badgeStatusDraftHint = $('#badge_status_draft_hint');
        const $draftSupplierLabel = $('#draft_active_supplier_label');

        let currentMaxBatchQty = 0;

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

        function formatQty(value) {
            const num = parseFloat(value || 0);
            return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        function notify(message) {
            alert(message);
        }

        function setSelectDisabled($el, disabled) {
            $el.prop('disabled', disabled);
            if (disabled) {
                $el.val(null).trigger('change');
            }
        }

        function resetBatchAndQty() {
            currentMaxBatchQty = 0;
            $qty.val('').prop('disabled', true).removeAttr('max');
            $hintStok.text('Stok tersedia batch: -');
            $btnAddDraft.prop('disabled', true);
        }

        // 1. Select2 Pilih Supplier
        $supplier.select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih Supplier terlebih dahulu...',
            allowClear: true,
            ajax: {
                url: urls.supplierSelect,
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

        // 2. Select2 Cari Nama Barang berdasarkan Gudang Retur Pembelian
        $barang.select2({
            theme: 'bootstrap4',
            placeholder: 'Cari nama barang di Gudang Retur Pembelian...',
            allowClear: true,
            ajax: {
                url: urls.barangSelect,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '',
                        gudang_id: gudangReturId,
                        kd_supplier: $supplier.val() || ''
                    };
                },
                processResults: function(data) {
                    return { results: data || [] };
                },
                cache: true
            }
        });

        // 3. Select2 Nested Dinamis: No Lot berdasarkan Nama Barang & Gudang Retur Pembelian
        $lot.select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih No Lot...',
            allowClear: true,
            ajax: {
                url: urls.lotSelect,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '',
                        gudang_id: gudangReturId,
                        kd_barang: $barang.val() || ''
                    };
                },
                processResults: function(data) {
                    return { results: data || [] };
                },
                cache: true
            }
        });

        // 4. Select2 Nested Dinamis: Expired Date menyesuaikan dengan No Lot yang dipilih
        $exp.select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih Expired Date...',
            allowClear: true,
            ajax: {
                url: urls.expSelect,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '',
                        gudang_id: gudangReturId,
                        kd_barang: $barang.val() || '',
                        no_lot: $lot.val() || ''
                    };
                },
                processResults: function(data) {
                    return { results: data || [] };
                },
                cache: true
            }
        });

        // Event berantai (Nested Dynamic Flow)
        $supplier.on('change', function() {
            const hasSupplier = !!$supplier.val();
            $kodeBarang.val('');
            setSelectDisabled($barang, !hasSupplier);
            setSelectDisabled($lot, true);
            setSelectDisabled($exp, true);
            resetBatchAndQty();
        });

        $barang.on('select2:select', function(e) {
            const data = e.params.data || {};
            $kodeBarang.val(data.kd_barang || data.id || '');
            $labelSatuan.text(data.satuan_kecil || 'PCS');
        });

        $barang.on('change', function() {
            const hasBarang = !!$barang.val();
            if (!hasBarang) {
                $kodeBarang.val('');
                $labelSatuan.text('PCS');
            }
            setSelectDisabled($lot, !hasBarang);
            setSelectDisabled($exp, true);
            resetBatchAndQty();
        });

        $lot.on('change', function() {
            const hasLot = !!$lot.val();
            setSelectDisabled($exp, !hasLot);
            resetBatchAndQty();
        });

        $exp.on('select2:select', function(e) {
            const data = e.params.data || {};
            currentMaxBatchQty = parseFloat(data.qty_tersedia || 0);
            const satuan = data.satuan_kecil || $labelSatuan.text() || 'PCS';
            $labelSatuan.text(satuan);
            $hintStok.text('Stok tersedia batch: ' + formatQty(currentMaxBatchQty) + ' ' + satuan);
            if (currentMaxBatchQty > 0) {
                $qty.prop('disabled', false).attr('max', currentMaxBatchQty).focus();
                $btnAddDraft.prop('disabled', false);
            } else {
                resetBatchAndQty();
            }
        });

        $exp.on('change', function() {
            if (!$exp.val()) {
                resetBatchAndQty();
            }
        });

        // Render tabel draft di kolom sebelah kanan sesuai dengan kolom yang diinput
        function renderDraftRows(rows) {
            $draftTbody.empty();
            const count = rows && rows.length ? rows.length : 0;
            $badgeDraftCount.text(count + ' Item');

            if (!count) {
                $draftTbody.append('<tr><td colspan="7" class="text-center text-muted py-3">Belum ada barang di dalam draft. Silakan pilih supplier &amp; tambahkan barang di sebelah kiri.</td></tr>');
                $panelPenyelesaian.addClass('disabled-panel');
                $badgeStatusDraftHint.removeClass('badge-success').addClass('badge-warning').text('Buat draft barang terlebih dahulu');
                $btnClearDraft.addClass('d-none');
                $draftSupplierLabel.text('- Belum ada draft -');
                $supplier.prop('disabled', false);
                $('#supplier_lock_hint').text('Pilih supplier tujuan retur pembelian terlebih dahulu.');
                return;
            }

            // Kunci pilihan supplier sesuai draft aktif agar 1 dokumen retur konsisten 1 supplier
            const first = rows[0];
            const supCode = first.kd_supplier || '';
            const supName = first.supplier_label || first.nama_supplier || supCode;
            $draftSupplierLabel.text(supName + ' (' + supCode + ')');

            if ($supplier.val() !== supCode && supCode !== '') {
                const opt = new Option(supName + ' (' + supCode + ')', supCode, true, true);
                $supplier.append(opt).trigger('change');
            }
            $supplier.prop('disabled', true);
            $('#supplier_lock_hint').text('Supplier dikunci mengikuti draft aktif. Kosongkan atau posting draft untuk ganti supplier.');

            $panelPenyelesaian.removeClass('disabled-panel');
            $badgeStatusDraftHint.removeClass('badge-warning').addClass('badge-success').text('Draft siap dilengkapi & diposting');
            $btnClearDraft.removeClass('d-none');

            rows.forEach(function(row, idx) {
                const satuan = row.satuan_kecil || row.satuan || 'PCS';
                $draftTbody.append(`
                    <tr data-id="${esc(row.id)}">
                        <td class="text-center">${idx + 1}</td>
                        <td class="text-center font-weight-bold">${esc(row.kd_barang)}</td>
                        <td>${esc(row.barang_label || row.nama_barang)}</td>
                        <td class="text-center">${esc(row.no_lot || '-')}</td>
                        <td class="text-center">${esc(row.expired_date || '-')}</td>
                        <td class="text-right font-weight-bold">${formatQty(row.qty_retur)} <span class="text-muted small">${esc(satuan)}</span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-xs js-delete-draft-item" data-id="${esc(row.id)}" title="Hapus item">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                `);
            });
        }

        function loadDraftList() {
            $.ajax({
                url: urls.draftList,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    renderDraftRows(res && res.rows ? res.rows : []);
                },
                error: function() {
                    renderDraftRows([]);
                }
            });
        }

        // Tambah item ke Draft
        $btnAddDraft.on('click', function() {
            const kdSupplier = $supplier.val();
            const kdBarang = $barang.val();
            const noLot = $lot.val();
            const expiredDate = $exp.val();
            const qtyVal = parseFloat($qty.val() || 0);

            if (!kdSupplier || !kdBarang || !noLot || !expiredDate || qtyVal <= 0) {
                notify('Lengkapi Supplier, Nama Barang, No Lot, Expired Date, dan Qty satuan kecil terlebih dahulu.');
                return;
            }

            if (currentMaxBatchQty > 0 && qtyVal > currentMaxBatchQty + 0.0001) {
                notify('Qty retur melebihi stok batch yang tersedia (' + formatQty(currentMaxBatchQty) + ').');
                return;
            }

            $btnAddDraft.prop('disabled', true);
            $.ajax({
                url: urls.draftAdd,
                type: 'POST',
                dataType: 'json',
                data: {
                    kd_supplier: kdSupplier,
                    gudang_id: gudangReturId,
                    kd_barang: kdBarang,
                    no_lot: noLot,
                    expired_date: expiredDate,
                    qty_retur: qtyVal
                },
                success: function(res) {
                    if (res && res.status) {
                        renderDraftRows(res.data && res.data.rows ? res.data.rows : []);
                        // Reset pilihan barang, lot, exp, dan qty untuk input item berikutnya
                        $barang.val(null).trigger('change');
                    } else {
                        notify(res && res.message ? res.message : 'Gagal menambahkan item ke draft.');
                        $btnAddDraft.prop('disabled', false);
                    }
                },
                error: function() {
                    notify('Server error saat menyimpan item draft.');
                    $btnAddDraft.prop('disabled', false);
                }
            });
        });

        // Hapus 1 baris item dari Draft
        $draftTbody.on('click', '.js-delete-draft-item', function() {
            const id = $(this).data('id');
            if (!id) return;
            if (!confirm('Hapus barang ini dari draft retur pembelian?')) return;

            $.ajax({
                url: urls.draftDelete,
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(res) {
                    if (res && res.status) {
                        renderDraftRows(res.data && res.data.rows ? res.data.rows : []);
                    } else {
                        notify(res && res.message ? res.message : 'Gagal menghapus item draft.');
                    }
                },
                error: function() {
                    notify('Server error saat menghapus item draft.');
                }
            });
        });

        // Kosongkan seluruh Draft
        $btnClearDraft.on('click', function() {
            if (!confirm('Kosongkan seluruh item di dalam draft retur pembelian?')) return;

            $.ajax({
                url: urls.draftClear,
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    if (res && res.status) {
                        renderDraftRows([]);
                    } else {
                        notify(res && res.message ? res.message : 'Gagal mengosongkan draft.');
                    }
                },
                error: function() {
                    notify('Server error saat mengosongkan draft.');
                }
            });
        });

        // Tombol Posting Data Retur Pembelian (setelah Jenis Penyelesaian & Alasan Retur diisi)
        $('#btn_post_draft_retur').on('click', function() {
            const tanggalRetur = $('#tanggal_retur').val();
            const jenisPenyelesaian = $('#jenis_penyelesaian').val();
            const alasanRetur = $.trim($('#alasan_retur').val() || '');
            const kdSupplier = $supplier.val();

            if (!tanggalRetur) {
                notify('Tanggal retur wajib diisi.');
                return;
            }
            if (!jenisPenyelesaian) {
                notify('Jenis penyelesaian wajib dipilih.');
                return;
            }
            if (!alasanRetur) {
                notify('Alasan retur wajib diisi sebelum memposting data retur.');
                $('#alasan_retur').focus();
                return;
            }

            if (!confirm('Posting data retur pembelian ini sekarang? Stok fisik di Gudang Retur Pembelian akan langsung dikurangi.')) {
                return;
            }

            const $btnPost = $(this);
            $btnPost.prop('disabled', true);

            $.ajax({
                url: urls.postDraft,
                type: 'POST',
                dataType: 'json',
                data: {
                    kd_supplier: kdSupplier,
                    tanggal_retur: tanggalRetur,
                    jenis_penyelesaian: jenisPenyelesaian,
                    alasan_retur: alasanRetur
                },
                success: function(res) {
                    notify(res && res.message ? res.message : 'Proses posting selesai.');
                    if (res && res.status) {
                        location.reload();
                    } else {
                        $btnPost.prop('disabled', false);
                    }
                },
                error: function() {
                    notify('Server error saat memposting data retur pembelian.');
                    $btnPost.prop('disabled', false);
                }
            });
        });

        // Lihat detail item pada tabel Daftar Retur Pembelian
        $('#retur_pembelian_table').on('click', '.js-btn-view-detail', function() {
            const id = $(this).data('id');
            const noRetur = $(this).data('no');
            const supplier = $(this).data('supplier');

            $('#modal_detail_no_retur').text(noRetur || '-');
            $('#modal_detail_supplier').text(supplier || '-');
            const $tbody = $('#modal_table_detail_items tbody');
            $tbody.html('<tr><td colspan="6" class="text-center text-muted">Memuat detail barang...</td></tr>');
            $('#modalDetailReturPembelian').modal('show');

            $.ajax({
                url: urls.detailItems + '/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    $tbody.empty();
                    const items = (res && res.items) ? res.items : [];
                    if (!items.length) {
                        $tbody.html('<tr><td colspan="6" class="text-center text-muted">Tidak ada detail barang.</td></tr>');
                        return;
                    }
                    items.forEach(function(item, i) {
                        $tbody.append(`
                            <tr>
                                <td class="text-center">${i + 1}</td>
                                <td class="text-center font-weight-bold">${esc(item.kd_barang)}</td>
                                <td>${esc(item.nama_barang)}</td>
                                <td class="text-center">${esc(item.no_lot || '-')}</td>
                                <td class="text-center">${esc(item.expired_date || '-')}</td>
                                <td class="text-right font-weight-bold">${formatQty(item.qty_retur)} ${esc(item.satuan || 'PCS')}</td>
                            </tr>
                        `);
                    });
                },
                error: function() {
                    $tbody.html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat detail barang.</td></tr>');
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
                { targets: [0, 3, 4, 6, 8, 9], className: 'text-center text-nowrap' },
                { targets: [7], className: 'text-right text-nowrap' }
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
                runAction(urls.voidDoc, { id_retur_pembelian: id, alasan: alasan }, 'Void retur posted dan kembalikan stok?');
            }
        });

        // Muat draft awal yang tersimpan di server
        const initialDraftRows = <?= json_encode($draft_rows ?? []) ?>;
        if (Array.isArray(initialDraftRows)) {
            renderDraftRows(initialDraftRows);
        } else {
            loadDraftList();
        }
    });
</script>
