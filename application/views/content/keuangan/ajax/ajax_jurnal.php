<script>
    $(function() {
        const schemaReady = <?= !empty($schema_ready) ? 'true' : 'false' ?>;
        const endpointBase = "<?= base_url($this->uri->uri_string() === 'keuangan/jurnal' ? 'keuangan/jurnal' : 'jurnal') ?>";
        const hasSwal = typeof Swal !== 'undefined';
        let currentId = 0;
        let accountRows = [];
        let currentSearch = '';
        let currentKlasifikasi = '';
        let currentMaster = '';
        let currentMasterRows = [];

        const masterConfig = {
            'klasifikasi': {
                title: 'Klasifikasi',
                idField: 'id_klasifikasi',
                rowTitle: function(row) { return row.kode_klasifikasi + ' - ' + row.nama_klasifikasi; },
                rowMeta: function(row) { return row.jenis_laporan + ' | ' + row.saldo_normal + ' | Urutan ' + row.urutan; },
                fields: [
                    { name: 'id_klasifikasi', label: 'ID Klasifikasi', type: 'number', required: true },
                    { name: 'kode_klasifikasi', label: 'Kode', type: 'text', required: true },
                    { name: 'nama_klasifikasi', label: 'Nama', type: 'text', required: true, full: true },
                    { name: 'alias_klasifikasi', label: 'Alias', type: 'text', full: true },
                    { name: 'jenis_laporan', label: 'Jenis Laporan', type: 'select', options: [{ value: 'NERACA', label: 'NERACA' }, { value: 'LABA_RUGI', label: 'LABA_RUGI' }] },
                    { name: 'saldo_normal', label: 'Saldo Normal', type: 'saldo_select' },
                    { name: 'urutan', label: 'Urutan', type: 'number' },
                    { name: 'is_active', label: 'Aktif', type: 'checkbox' }
                ]
            },
            'saldo-normal': {
                title: 'Saldo Normal',
                idField: 'kode_saldo',
                rowTitle: function(row) { return row.kode_saldo + ' - ' + row.nama_saldo; },
                rowMeta: function(row) { return (row.keterangan || '-') + ' | Urutan ' + row.urutan; },
                fields: [
                    { name: 'kode_saldo', label: 'Kode Saldo', type: 'text', required: true },
                    { name: 'nama_saldo', label: 'Nama Saldo', type: 'text', required: true },
                    { name: 'keterangan', label: 'Keterangan', type: 'textarea', full: true },
                    { name: 'urutan', label: 'Urutan', type: 'number' },
                    { name: 'is_active', label: 'Aktif', type: 'checkbox' }
                ]
            },
            'tipe-kontrol': {
                title: 'Tipe Kontrol',
                idField: 'kode_tipe_kontrol',
                rowTitle: function(row) { return row.kode_tipe_kontrol + ' - ' + row.nama_tipe_kontrol; },
                rowMeta: function(row) { return (row.keterangan || '-') + ' | Urutan ' + row.urutan; },
                fields: [
                    { name: 'kode_tipe_kontrol', label: 'Kode Tipe Kontrol', type: 'text', required: true },
                    { name: 'nama_tipe_kontrol', label: 'Nama Tipe Kontrol', type: 'text', required: true },
                    { name: 'keterangan', label: 'Keterangan', type: 'textarea', full: true },
                    { name: 'urutan', label: 'Urutan', type: 'number' },
                    { name: 'is_active', label: 'Aktif', type: 'checkbox' }
                ]
            },
            'parent-subclass': {
                title: 'Parent / Subclass',
                idField: 'id_akun',
                rowTitle: function(row) { return row.kode_akun + ' - ' + row.nama_akun; },
                rowMeta: function(row) { return (row.nama_klasifikasi || '-') + ' | Level ' + row.level_akun; },
                fields: [
                    { name: 'kode_akun', label: 'Kode Akun', type: 'text', required: true },
                    { name: 'nama_akun', label: 'Nama Akun', type: 'text', required: true, full: true },
                    { name: 'id_klasifikasi', label: 'Klasifikasi', type: 'klasifikasi_select' },
                    { name: 'parent_id', label: 'Parent', type: 'parent_select' },
                    { name: 'saldo_normal', label: 'Saldo Normal', type: 'saldo_select' },
                    { name: 'tipe_kontrol', label: 'Tipe Kontrol', type: 'kontrol_select' },
                    { name: 'urutan_placeholder', label: 'Info', type: 'readonly', value: 'Data ini disimpan sebagai akun HEADER.' },
                    { name: 'is_active', label: 'Aktif', type: 'checkbox' }
                ]
            }
        };

        function notify(icon, title, text) {
            if (hasSwal) {
                Swal.fire({ icon: icon, title: title, text: text });
                return;
            }
            alert((title ? title + ': ' : '') + text);
        }

        function debounce(fn, delay) {
            let timeoutId = null;
            return function() {
                const ctx = this;
                const args = arguments;
                clearTimeout(timeoutId);
                timeoutId = setTimeout(function() { fn.apply(ctx, args); }, delay);
            };
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function(chr) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[chr];
            });
        }

        function formatMoney(value) {
            const number = parseFloat(value || 0);
            return 'Rp ' + number.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function formatDate(value) {
            if (!value) {
                return '-';
            }
            const datePart = String(value).split(' ')[0];
            const pieces = datePart.split('-');
            if (pieces.length === 3) {
                return pieces[2] + '/' + pieces[1] + '/' + pieces[0];
            }
            return value;
        }

        function updateSummary(summary) {
            summary = summary || {};
            $('#sumTotal').text(summary.total || 0);
            $('#sumHeader').text(summary.header || 0);
            $('#sumPosting').text(summary.posting || 0);
            $('#sumActive').text(summary.active || 0);
            $('#sumInactive').text(summary.inactive || 0);
        }

        function firstSelectValue(selector) {
            return $(selector + ' option:first').val() || '';
        }

        function renderKlasifikasiOptions(options, selectedValue) {
            let html = '<option value="">Pilih Klasifikasi</option>';
            (options || []).forEach(function(item) {
                const selected = parseInt(item.id_klasifikasi, 10) === parseInt(selectedValue || 0, 10) ? ' selected' : '';
                html += '<option value="' + parseInt(item.id_klasifikasi, 10) + '" data-saldo="' + escapeHtml(item.saldo_normal) + '"' + selected + '>' +
                    escapeHtml(item.kode_klasifikasi + ' - ' + item.nama_klasifikasi) +
                    '</option>';
            });
            $('#id_klasifikasi').html(html);
        }

        function renderSaldoOptions(options, selectedValue) {
            let html = '';
            (options || []).forEach(function(item) {
                const selected = item.kode_saldo === selectedValue ? ' selected' : '';
                html += '<option value="' + escapeHtml(item.kode_saldo) + '"' + selected + '>' +
                    escapeHtml(item.nama_saldo + ' (' + item.kode_saldo + ')') +
                    '</option>';
            });
            $('#saldo_normal').html(html);
        }

        function renderKontrolOptions(options, selectedValue) {
            let html = '';
            (options || []).forEach(function(item) {
                const selected = item.kode_tipe_kontrol === selectedValue ? ' selected' : '';
                html += '<option value="' + escapeHtml(item.kode_tipe_kontrol) + '"' + selected + '>' +
                    escapeHtml(item.nama_tipe_kontrol + ' (' + item.kode_tipe_kontrol + ')') +
                    '</option>';
            });
            $('#tipe_kontrol').html(html);
        }

        function renderParentOptions(selectedId) {
            let html = '<option value="">Tanpa Parent</option>';
            accountRows.forEach(function(row) {
                if (row.tipe_akun !== 'HEADER' || parseInt(row.id_akun, 10) === currentId) {
                    return;
                }
                const selected = parseInt(row.id_akun, 10) === parseInt(selectedId || 0, 10) ? ' selected' : '';
                html += '<option value="' + parseInt(row.id_akun, 10) + '"' + selected + '>' +
                    escapeHtml(row.kode_akun + ' - ' + row.nama_akun) +
                    '</option>';
            });
            $('#parent_id').html(html);
        }

        function renderList(rows) {
            const $list = $('#accountList');
            $('#accountCountLabel').text(rows.length + ' data');

            if (!rows.length) {
                $list.html('<div class="empty-state">Data akun belum tersedia.</div>');
                return;
            }

            let html = '';
            rows.forEach(function(row) {
                const activeClass = parseInt(row.id_akun, 10) === currentId ? ' active' : '';
                const status = parseInt(row.is_active, 10) === 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>';
                html += '<div class="account-item' + activeClass + '" data-id="' + parseInt(row.id_akun, 10) + '">' +
                    '<div class="account-item-top">' +
                    '<div><div class="account-code">' + escapeHtml(row.kode_akun) + ' ' + status + '</div>' +
                    '<div class="account-name">' + escapeHtml(row.nama_akun) + '</div></div>' +
                    '<div class="account-actions">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary account-icon-btn btn-account-detail" data-id="' + parseInt(row.id_akun, 10) + '" title="Detail akun"><i class="fas fa-edit"></i></button>' +
                    '</div></div>' +
                    '<div class="account-meta">' + escapeHtml(row.nama_klasifikasi || '-') + ' | ' + escapeHtml(row.tipe_akun) + ' | ' + escapeHtml(row.saldo_normal) + '</div>' +
                    '</div>';
            });

            $list.html(html);
        }

        function resetForm() {
            currentId = 0;
            $('#formJurnalAccount')[0].reset();
            $('#id_akun').val('');
            $('#tipe_kontrol').val(firstSelectValue('#tipe_kontrol'));
            $('#saldo_normal').val(firstSelectValue('#saldo_normal'));
            $('#tipe_akun').val('HEADER');
            $('#is_active').prop('checked', true);
            $('#allow_manual_journal').prop('checked', false).prop('disabled', false);
            $('.account-item').removeClass('active');
            $('#btnDeleteAccount, #btnDeactivateAccount').prop('disabled', true);
            renderParentOptions('');
        }

        function openAccountModal(row) {
            if (row) {
                populateForm(row);
                $('#modalJurnalAccountTitle').text('Detail Akun Jurnal');
            } else {
                resetForm();
                $('#modalJurnalAccountTitle').text('Tambah Akun Jurnal');
            }
            $('#modalJurnalAccount').modal('show');
        }

        function populateForm(row) {
            currentId = parseInt(row.id_akun, 10);
            $('#id_akun').val(currentId);
            $('#kode_akun').val(row.kode_akun || '');
            $('#nama_akun').val(row.nama_akun || '');
            $('#id_klasifikasi').val(row.id_klasifikasi || '');
            $('#saldo_normal').val(row.saldo_normal || firstSelectValue('#saldo_normal'));
            $('#tipe_akun').val(row.tipe_akun || 'HEADER');
            $('#tipe_kontrol').val(row.tipe_kontrol || firstSelectValue('#tipe_kontrol'));
            $('#is_active').prop('checked', parseInt(row.is_active, 10) === 1);
            $('#allow_manual_journal').prop('checked', parseInt(row.allow_manual_journal, 10) === 1);
            renderParentOptions(row.parent_id || '');
            $('#btnDeleteAccount').prop('disabled', parseInt(row.transaction_count || 0, 10) > 0 || parseInt(row.child_count || 0, 10) > 0);
            $('#btnDeactivateAccount').prop('disabled', parseInt(row.is_active, 10) !== 1);
            $('#btnEditSelectedAccount').prop('disabled', false);
            toggleManualJournal();
        }

        function renderJournalRows(rows, schemaReady) {
            $('#journalCountLabel').text((rows || []).length + ' data');

            if (!schemaReady) {
                $('#journalRows').html('<tr><td colspan="5" class="text-center text-muted">Tabel jurnal belum tersedia. Data akan tampil setelah schema General Ledger dimigrasikan.</td></tr>');
                return;
            }

            if (!rows || !rows.length) {
                $('#journalRows').html('<tr><td colspan="5" class="text-center text-muted">Belum ada jurnal untuk akun ini.</td></tr>');
                return;
            }

            let html = '';
            rows.forEach(function(row) {
                html += '<tr>' +
                    '<td>' + escapeHtml(formatDate(row.tanggal_jurnal)) + '</td>' +
                    '<td>' + escapeHtml(row.no_referensi || '-') + '</td>' +
                    '<td>' + escapeHtml(row.catatan || '-') + '</td>' +
                    '<td class="money-cell">' + escapeHtml(formatMoney(row.debit)) + '</td>' +
                    '<td class="money-cell">' + escapeHtml(formatMoney(row.kredit)) + '</td>' +
                    '</tr>';
            });
            $('#journalRows').html(html);
        }

        function updateSelectedAccount(row) {
            if (!row) {
                $('#selectedAccountTitle').text('Pilih akun');
                $('#selectedAccountMeta').text('Data jurnal akan menyesuaikan akun pada daftar.');
                $('#btnEditSelectedAccount').prop('disabled', true);
                renderJournalRows([], true);
                return;
            }

            $('#selectedAccountTitle').text((row.kode_akun || '-') + ' - ' + (row.nama_akun || '-'));
            $('#selectedAccountMeta').text((row.nama_klasifikasi || '-') + ' | ' + (row.tipe_akun || '-') + ' | Saldo normal ' + (row.saldo_normal || '-'));
        }

        function loadAccountJournal(id) {
            $.ajax({
                url: endpointBase + '/account-journal',
                type: 'POST',
                dataType: 'json',
                data: { id_akun: id },
                success: function(resp) {
                    if (!resp.success || !resp.data) {
                        renderJournalRows([], false);
                        return;
                    }
                    updateSelectedAccount(resp.data.account || null);
                    renderJournalRows(resp.data.rows || [], !!resp.data.journal_schema_ready);
                },
                error: function(xhr) {
                    $('#journalRows').html('<tr><td colspan="5" class="text-center text-danger">' + escapeHtml((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memuat jurnal akun.') + '</td></tr>');
                }
            });
        }

        function loadList(searchValue) {
            if (!schemaReady) {
                $('#accountList').html('<div class="empty-state">Schema accounting belum tersedia.</div>');
                return;
            }

            $.ajax({
                url: endpointBase + '/list',
                type: 'POST',
                dataType: 'json',
                data: {
                    search: searchValue || '',
                    id_klasifikasi: currentKlasifikasi || ''
                },
                success: function(resp) {
                    if (!resp.success) {
                        notify('warning', 'Perhatian', resp.message || 'Gagal memuat akun.');
                        return;
                    }

                    accountRows = (resp.data && resp.data.rows) ? resp.data.rows : [];
                    updateSummary(resp.data ? resp.data.summary : {});
                    if (resp.data) {
                        renderKlasifikasiOptions(resp.data.klasifikasi_options || [], $('#id_klasifikasi').val());
                        renderSaldoOptions(resp.data.saldo_normal_options || [], $('#saldo_normal').val());
                        renderKontrolOptions(resp.data.tipe_kontrol_options || [], $('#tipe_kontrol').val());
                    }
                    renderList(accountRows);
                    renderParentOptions($('#parent_id').val());

                    if (!currentId && accountRows.length) {
                        loadDetail(accountRows[0].id_akun);
                    } else if (currentId > 0) {
                        const selectedRow = accountRows.find(function(item) {
                            return parseInt(item.id_akun, 10) === parseInt(currentId, 10);
                        });
                        if (selectedRow) {
                            updateSelectedAccount(selectedRow);
                            loadAccountJournal(currentId);
                        }
                    } else if (!accountRows.length) {
                        updateSelectedAccount(null);
                    }
                },
                error: function(xhr) {
                    notify('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memuat data akun.');
                }
            });
        }

        function loadDetail(id) {
            $.ajax({
                url: endpointBase + '/detail',
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(resp) {
                    if (!resp.success || !resp.data || !resp.data.row) {
                        notify('warning', 'Perhatian', resp.message || 'Data akun tidak ditemukan.');
                        return;
                    }
                    populateForm(resp.data.row);
                    updateSelectedAccount(resp.data.row);
                    loadAccountJournal(id);
                    $('.account-item').removeClass('active');
                    $('.account-item[data-id="' + parseInt(id, 10) + '"]').addClass('active');
                },
                error: function(xhr) {
                    notify('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengambil detail akun.');
                }
            });
        }

        function toggleManualJournal() {
            const isHeader = $('#tipe_akun').val() === 'HEADER';
            $('#allow_manual_journal').prop('disabled', isHeader);
            if (isHeader) {
                $('#allow_manual_journal').prop('checked', false);
            }
        }

        $('#accountSearch').on('input', debounce(function() {
            currentSearch = $(this).val().trim();
            currentId = 0;
            loadList(currentSearch);
        }, 300));

        $('#accountKlasifikasiFilter').on('change', function() {
            currentKlasifikasi = $(this).val();
            currentId = 0;
            loadList(currentSearch);
        });

        $('#accountList').on('click', '.account-item', function() {
            loadDetail($(this).data('id'));
        });

        $('#accountList').on('click', '.btn-account-detail', function(e) {
            e.stopPropagation();
            loadDetail($(this).data('id'));
            const row = accountRows.find(function(item) {
                return parseInt(item.id_akun, 10) === parseInt($(e.currentTarget).data('id'), 10);
            });
            openAccountModal(row || null);
        });

        $('#btnHeaderNewAccount, #btnListNewAccount').on('click', function() {
            openAccountModal(null);
        });

        $('#btnNewAccount').on('click', function() {
            openAccountModal(null);
        });

        $('#btnEditSelectedAccount').on('click', function() {
            const row = accountRows.find(function(item) {
                return parseInt(item.id_akun, 10) === parseInt(currentId, 10);
            });
            if (row) {
                openAccountModal(row);
            }
        });

        $('#id_klasifikasi').on('change', function() {
            const saldo = $(this).find(':selected').data('saldo');
            if (saldo) {
                $('#saldo_normal').val(saldo);
            }
        });

        $('#tipe_akun').on('change', toggleManualJournal);

        $('#formJurnalAccount').on('submit', function(e) {
            e.preventDefault();
            if (!schemaReady) {
                notify('warning', 'Perhatian', 'Schema accounting belum tersedia.');
                return;
            }

            const endpoint = currentId > 0 ? endpointBase + '/update' : endpointBase + '/store';
            $.ajax({
                url: endpoint,
                type: 'POST',
                dataType: 'json',
                data: {
                    id_akun: currentId,
                    kode_akun: $('#kode_akun').val(),
                    nama_akun: $('#nama_akun').val(),
                    id_klasifikasi: $('#id_klasifikasi').val(),
                    parent_id: $('#parent_id').val(),
                    saldo_normal: $('#saldo_normal').val(),
                    tipe_akun: $('#tipe_akun').val(),
                    tipe_kontrol: $('#tipe_kontrol').val(),
                    allow_manual_journal: $('#allow_manual_journal').is(':checked') ? 1 : 0,
                    is_active: $('#is_active').is(':checked') ? 1 : 0
                },
                success: function(resp) {
                    if (!resp.success) {
                        notify('error', 'Gagal', resp.message || 'Proses gagal.');
                        return;
                    }
                    notify('success', 'Berhasil', resp.message || 'Akun berhasil disimpan.');
                    if (resp.data && resp.data.id_akun) {
                        currentId = parseInt(resp.data.id_akun, 10);
                    }
                    $('#modalJurnalAccount').modal('hide');
                    loadList(currentSearch);
                },
                error: function(xhr) {
                    notify('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan server.');
                }
            });
        });

        $('#btnDeactivateAccount').on('click', function() {
            if (currentId <= 0) {
                return;
            }
            $.ajax({
                url: endpointBase + '/deactivate',
                type: 'POST',
                dataType: 'json',
                data: { id_akun: currentId },
                success: function(resp) {
                    notify(resp.success ? 'success' : 'error', resp.success ? 'Berhasil' : 'Gagal', resp.message || '');
                    loadList(currentSearch);
                },
                error: function(xhr) {
                    notify('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menonaktifkan akun.');
                }
            });
        });

        $('#btnDeleteAccount').on('click', function() {
            if (currentId <= 0 || !confirm('Hapus akun jurnal ini?')) {
                return;
            }
            $.ajax({
                url: endpointBase + '/delete',
                type: 'POST',
                dataType: 'json',
                data: { id_akun: currentId },
                success: function(resp) {
                    notify(resp.success ? 'success' : 'error', resp.success ? 'Berhasil' : 'Gagal', resp.message || '');
                    resetForm();
                    loadList(currentSearch);
                },
                error: function(xhr) {
                    notify('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghapus akun.');
                }
            });
        });

        function masterEndpoint(action) {
            return endpointBase + '/master/' + currentMaster + '/' + action;
        }

        function fieldHtml(field, value) {
            const wrapClass = field.full ? 'form-group full' : 'form-group';
            const required = field.required ? ' required' : '';
            value = value === null || typeof value === 'undefined' ? '' : value;

            if (field.type === 'textarea') {
                return '<div class="' + wrapClass + '"><label>' + escapeHtml(field.label) + '</label><textarea class="form-control master-input" name="' + field.name + '"' + required + '>' + escapeHtml(value) + '</textarea></div>';
            }

            if (field.type === 'checkbox') {
                const checked = parseInt(value === '' ? 1 : value, 10) === 1 ? ' checked' : '';
                return '<div class="' + wrapClass + '"><label>' + escapeHtml(field.label) + '</label><div class="custom-control custom-checkbox mt-2"><input type="checkbox" class="custom-control-input master-input" id="master_' + field.name + '" name="' + field.name + '" value="1"' + checked + '><label class="custom-control-label" for="master_' + field.name + '">Aktif</label></div></div>';
            }

            if (field.type === 'select') {
                let html = '<div class="' + wrapClass + '"><label>' + escapeHtml(field.label) + '</label><select class="form-control master-input" name="' + field.name + '">';
                (field.options || []).forEach(function(option) {
                    html += '<option value="' + escapeHtml(option.value) + '"' + (option.value === value ? ' selected' : '') + '>' + escapeHtml(option.label) + '</option>';
                });
                html += '</select></div>';
                return html;
            }

            if (field.type === 'saldo_select') {
                let html = '<div class="' + wrapClass + '"><label>' + escapeHtml(field.label) + '</label><select class="form-control master-input" name="' + field.name + '">';
                $('#saldo_normal option').each(function() {
                    html += '<option value="' + escapeHtml($(this).val()) + '"' + ($(this).val() === value ? ' selected' : '') + '>' + escapeHtml($(this).text()) + '</option>';
                });
                html += '</select></div>';
                return html;
            }

            if (field.type === 'kontrol_select') {
                let html = '<div class="' + wrapClass + '"><label>' + escapeHtml(field.label) + '</label><select class="form-control master-input" name="' + field.name + '">';
                $('#tipe_kontrol option').each(function() {
                    html += '<option value="' + escapeHtml($(this).val()) + '"' + ($(this).val() === value ? ' selected' : '') + '>' + escapeHtml($(this).text()) + '</option>';
                });
                html += '</select></div>';
                return html;
            }

            if (field.type === 'klasifikasi_select') {
                let html = '<div class="' + wrapClass + '"><label>' + escapeHtml(field.label) + '</label><select class="form-control master-input" name="' + field.name + '">';
                $('#id_klasifikasi option').each(function() {
                    html += '<option value="' + escapeHtml($(this).val()) + '"' + ($(this).val() === String(value || '') ? ' selected' : '') + '>' + escapeHtml($(this).text()) + '</option>';
                });
                html += '</select></div>';
                return html;
            }

            if (field.type === 'parent_select') {
                let html = '<div class="' + wrapClass + '"><label>' + escapeHtml(field.label) + '</label><select class="form-control master-input" name="' + field.name + '"><option value="">Tanpa Parent</option>';
                accountRows.forEach(function(row) {
                    if (row.tipe_akun === 'HEADER') {
                        html += '<option value="' + parseInt(row.id_akun, 10) + '"' + (parseInt(row.id_akun, 10) === parseInt(value || 0, 10) ? ' selected' : '') + '>' + escapeHtml(row.kode_akun + ' - ' + row.nama_akun) + '</option>';
                    }
                });
                html += '</select></div>';
                return html;
            }

            if (field.type === 'readonly') {
                return '<div class="' + wrapClass + '"><label>' + escapeHtml(field.label) + '</label><input type="text" class="form-control" value="' + escapeHtml(field.value || value) + '" readonly></div>';
            }

            return '<div class="' + wrapClass + '"><label>' + escapeHtml(field.label) + '</label><input type="' + field.type + '" class="form-control master-input" name="' + field.name + '" value="' + escapeHtml(value) + '"' + required + '></div>';
        }

        function renderMasterForm(row) {
            const cfg = masterConfig[currentMaster];
            row = row || {};
            let html = '';
            cfg.fields.forEach(function(field) {
                html += fieldHtml(field, row[field.name]);
            });
            $('#masterFormFields').html(html);
            $('#master_id').val(row[cfg.idField] || '');
            $('#btnMasterDelete').prop('disabled', !row[cfg.idField]);
        }

        function renderMasterRows(rows) {
            const cfg = masterConfig[currentMaster];
            if (!rows.length) {
                $('#masterRows').html('<div class="empty-state">Data master belum tersedia.</div>');
                return;
            }

            let html = '';
            rows.forEach(function(row) {
                const id = row[cfg.idField];
                const active = parseInt(row.is_active, 10) === 1 ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>';
                html += '<div class="master-row" data-id="' + escapeHtml(id) + '">' +
                    '<div class="master-row-title">' + escapeHtml(cfg.rowTitle(row)) + ' ' + active + '</div>' +
                    '<div class="master-row-meta">' + escapeHtml(cfg.rowMeta(row)) + '</div>' +
                    '</div>';
            });
            $('#masterRows').html(html);
        }

        function loadMasterRows(selectFirst) {
            $.ajax({
                url: masterEndpoint('list'),
                type: 'POST',
                dataType: 'json',
                success: function(resp) {
                    if (!resp.success) {
                        notify('error', 'Gagal', resp.message || 'Gagal memuat master.');
                        return;
                    }

                    currentMasterRows = resp.data && resp.data.rows ? resp.data.rows : [];
                    renderMasterRows(currentMasterRows);
                    if (selectFirst && currentMasterRows.length) {
                        populateMaster(currentMasterRows[0]);
                        $('#masterRows .master-row').first().addClass('active');
                    } else {
                        renderMasterForm({});
                    }
                },
                error: function(xhr) {
                    notify('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memuat master.');
                }
            });
        }

        function populateMaster(row) {
            renderMasterForm(row);
        }

        $('.btn-open-master').on('click', function() {
            currentMaster = $(this).data('master');
            if (!masterConfig[currentMaster]) {
                notify('warning', 'Perhatian', 'Master tidak valid.');
                return;
            }

            $('#master_key').val(currentMaster);
            $('#modalJurnalMasterTitle').text(masterConfig[currentMaster].title);
            $('#modalJurnalMaster').modal('show');
            loadMasterRows(true);
        });

        $('#masterRows').on('click', '.master-row', function() {
            const id = String($(this).data('id'));
            const cfg = masterConfig[currentMaster];
            const row = currentMasterRows.find(function(item) {
                return String(item[cfg.idField]) === id;
            });
            if (row) {
                $('#masterRows .master-row').removeClass('active');
                $(this).addClass('active');
                populateMaster(row);
            }
        });

        $('#btnMasterNew').on('click', function() {
            $('#masterRows .master-row').removeClass('active');
            renderMasterForm({});
        });

        $('#formJurnalMaster').on('submit', function(e) {
            e.preventDefault();
            const id = $('#master_id').val();
            const data = { id: id };

            $('#masterFormFields .master-input').each(function() {
                const name = $(this).attr('name');
                if (!name) {
                    return;
                }
                if ($(this).attr('type') === 'checkbox') {
                    data[name] = $(this).is(':checked') ? 1 : 0;
                } else {
                    data[name] = $(this).val();
                }
            });

            $.ajax({
                url: masterEndpoint(id ? 'update' : 'store'),
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function(resp) {
                    if (!resp.success) {
                        notify('error', 'Gagal', resp.message || 'Proses master gagal.');
                        return;
                    }
                    notify('success', 'Berhasil', resp.message || 'Master berhasil disimpan.');
                    loadMasterRows(true);
                    loadList(currentSearch);
                },
                error: function(xhr) {
                    notify('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan server.');
                }
            });
        });

        $('#btnMasterDelete').on('click', function() {
            const id = $('#master_id').val();
            if (!id || !confirm('Hapus data master ini?')) {
                return;
            }

            $.ajax({
                url: masterEndpoint('delete'),
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function(resp) {
                    notify(resp.success ? 'success' : 'error', resp.success ? 'Berhasil' : 'Gagal', resp.message || '');
                    loadMasterRows(true);
                    loadList(currentSearch);
                },
                error: function(xhr) {
                    notify('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghapus master.');
                }
            });
        });

        let purchaseJournalRows = [];
        let salesJournalRows = [];

        function renderPurchaseJournalRows(rows) {
            $('#purchaseJournalCount').text((rows || []).length + ' data');
            if (!schemaReady) {
                $('#purchaseJournalRows').html('<tr><td colspan="6" class="text-center text-muted">Schema jurnal belum tersedia.</td></tr>');
                return;
            }
            if (!rows || !rows.length) {
                $('#purchaseJournalRows').html('<tr><td colspan="6" class="text-center text-muted">Data jurnal pembelian tidak ditemukan.</td></tr>');
                return;
            }

            let html = '';
            rows.forEach(function(row) {
                html += '<tr data-id="' + parseInt(row.id_jurnal, 10) + '">' +
                    '<td>' + escapeHtml(row.referensi || row.nomor_lpb || row.nomor_jurnal || '-') + '</td>' +
                    '<td>' + escapeHtml(formatDate(row.tanggal_transaksi)) + '</td>' +
                    '<td>' + escapeHtml(row.no_po || '-') + '</td>' +
                    '<td>' + escapeHtml(row.supplier || '-') + '</td>' +
                    '<td>IDR</td>' +
                    '<td class="money-cell">' + escapeHtml(formatMoney(row.nilai)) + '</td>' +
                    '</tr>';
            });
            $('#purchaseJournalRows').html(html);
        }

        function loadPurchaseJournalList(searchValue) {
            if (!schemaReady) {
                return;
            }
            $.ajax({
                url: endpointBase + '/purchase-list',
                type: 'POST',
                dataType: 'json',
                data: { search: searchValue || '' },
                success: function(resp) {
                    if (!resp.success) {
                        return;
                    }
                    purchaseJournalRows = (resp.data && resp.data.rows) ? resp.data.rows : [];
                    renderPurchaseJournalRows(purchaseJournalRows);
                }
            });
        }

        function renderSalesJournalRows(rows) {
            $('#salesJournalCount').text((rows || []).length + ' data');
            if (!schemaReady) {
                $('#salesJournalRows').html('<tr><td colspan="6" class="text-center text-muted">Schema jurnal belum tersedia.</td></tr>');
                return;
            }
            if (!rows || !rows.length) {
                $('#salesJournalRows').html('<tr><td colspan="6" class="text-center text-muted">Data jurnal penjualan tidak ditemukan.</td></tr>');
                return;
            }

            let html = '';
            rows.forEach(function(row) {
                html += '<tr data-id="' + parseInt(row.id_jurnal, 10) + '">' +
                    '<td>' + escapeHtml(row.nomor_jurnal || '-') + '</td>' +
                    '<td>' + escapeHtml(formatDate(row.tanggal_transaksi)) + '</td>' +
                    '<td>' + escapeHtml(row.no_so || '-') + '</td>' +
                    '<td>' + escapeHtml(row.pelanggan || '-') + '</td>' +
                    '<td>IDR</td>' +
                    '<td class="money-cell">' + escapeHtml(formatMoney(row.nilai)) + '</td>' +
                    '</tr>';
            });
            $('#salesJournalRows').html(html);
        }

        function loadSalesJournalList(searchValue) {
            if (!schemaReady) {
                return;
            }
            $.ajax({
                url: endpointBase + '/sales-list',
                type: 'POST',
                dataType: 'json',
                data: { search: searchValue || '' },
                success: function(resp) {
                    if (!resp.success) {
                        return;
                    }
                    salesJournalRows = (resp.data && resp.data.rows) ? resp.data.rows : [];
                    renderSalesJournalRows(salesJournalRows);
                }
            });
        }

        $('#salesJournalSearch').on('input', debounce(function() {
            loadSalesJournalList($(this).val().trim());
        }, 300));

        $('#purchaseJournalSearch').on('input', debounce(function() {
            loadPurchaseJournalList($(this).val().trim());
        }, 300));

        $('#jurnalPeriodForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: endpointBase + '/period-store',
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(resp) {
                    notify(resp.success ? 'success' : 'error', resp.success ? 'Berhasil' : 'Gagal', resp.message || '');
                    if (resp.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    notify('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan periode fiskal.');
                }
            });
        });

        $('#jurnalPeriodTable').on('click', '.btn-jurnal-period-action', function() {
            const action = $(this).data('action');
            const reason = prompt('Alasan approval ' + action + ':');
            if (!reason) {
                return;
            }
            $.ajax({
                url: endpointBase + '/period-action',
                type: 'POST',
                dataType: 'json',
                data: {
                    id_periode: $(this).data('id'),
                    action: action,
                    reason: reason
                },
                success: function(resp) {
                    notify(resp.success ? 'success' : 'error', resp.success ? 'Berhasil' : 'Gagal', resp.message || '');
                    if (resp.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    notify('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengubah status periode fiskal.');
                }
            });
        });

        $('#salesJournalRows').on('click', 'tr', function() {
            const id = $(this).data('id');
            if (!id) return;
            loadSalesJournalDetail(id);
        });

        $('#purchaseJournalRows').on('click', 'tr', function() {
            const id = $(this).data('id');
            if (!id) return;
            loadPurchaseJournalDetail(id);
        });

        function loadPurchaseJournalDetail(id) {
            $.ajax({
                url: endpointBase + '/purchase-detail',
                type: 'POST',
                dataType: 'json',
                data: { id_jurnal: id },
                success: function(resp) {
                    if (!resp.success || !resp.data) {
                        notify('warning', 'Perhatian', resp.message || 'Data jurnal tidak ditemukan.');
                        return;
                    }
                    const header = resp.data.journal || {};
                    const rows = resp.data.details || [];

                    $('#salesJournalRef').text(header.kode_jenis_jurnal || 'PJ');
                    $('#salesJournalDate').text(formatDate(header.tanggal_transaksi) || '-');
                    $('#salesJournalTitle').text(header.keterangan || ('Pembelian, ' + (header.supplier || '-')));
                    $('#salesJournalUser').text(header.created_by_name || '-');
                    $('#salesJournalDebit').text(formatMoney(header.total_debit || 0));
                    $('#salesJournalKredit').text(formatMoney(header.total_kredit || 0));

                    let html = '';
                    if (!rows.length) {
                        html = '<tr><td colspan="5" class="text-center text-muted">Detail jurnal tidak ditemukan.</td></tr>';
                    } else {
                        rows.forEach(function(r) {
                            const isDebit = parseFloat(r.debit || 0) > 0;
                            html += '<tr>' +
                                '<td>' + escapeHtml(r.nomor_dokumen || header.nomor_lpb || header.source_no || '-') + '</td>' +
                                '<td>' + escapeHtml(r.kode_rekening_display || r.kode_akun || '-') + '</td>' +
                                '<td>' + escapeHtml(r.nama_akun || r.keterangan || '-') + '</td>' +
                                '<td class="text-right">' + (isDebit ? escapeHtml(formatMoney(r.debit)) : '') + '</td>' +
                                '<td class="text-right">' + (!isDebit ? escapeHtml(formatMoney(r.kredit)) : '') + '</td>' +
                                '</tr>';
                        });
                    }
                    $('#salesJournalDetailRows').html(html);
                    $('#modalSalesJournal').modal('show');
                },
                error: function(xhr) {
                    notify('error', 'Gagal', 'Terjadi kesalahan saat memuat detail jurnal.');
                }
            });
        }

        function loadSalesJournalDetail(id) {
            $.ajax({
                url: endpointBase + '/sales-detail',
                type: 'POST',
                dataType: 'json',
                data: { id_jurnal: id },
                success: function(resp) {
                    if (!resp.success || !resp.data) {
                        notify('warning', 'Perhatian', resp.message || 'Data jurnal tidak ditemukan.');
                        return;
                    }
                    const header = resp.data.journal || {};
                    const rows = resp.data.details || [];
                    
                    $('#salesJournalRef').text(header.nomor_jurnal || '-');
                    $('#salesJournalDate').text(formatDate(header.tanggal_transaksi) || '-');
                    $('#salesJournalTitle').text(header.keterangan || 'Penjualan');
                    $('#salesJournalUser').text(header.created_by_name || '-');
                    $('#salesJournalDebit').text(formatMoney(header.total_debit || 0));
                    $('#salesJournalKredit').text(formatMoney(header.total_kredit || 0));
                    
                    let html = '';
                    if (!rows.length) {
                        html = '<tr><td colspan="5" class="text-center text-muted">Detail jurnal tidak ditemukan.</td></tr>';
                    } else {
                        rows.forEach(function(r) {
                            const isDebit = parseFloat(r.debit || 0) > 0;
                            let desc = r.keterangan || '';
                            if (desc.indexOf('Faktur penjualan') !== -1) {
                                desc = '-';
                            } else if (!desc) {
                                desc = header.keterangan || '-';
                                if (desc.indexOf('Faktur penjualan') !== -1) {
                                    desc = '-';
                                }
                            }
                            html += '<tr>' +
                                '<td>' + escapeHtml(r.kode_rekening_display || r.kode_akun || '-') + '</td>' +
                                '<td>' + escapeHtml(r.nama_akun || '-') + '</td>' +
                                '<td class="text-right">' + (isDebit ? escapeHtml(formatMoney(r.debit)) : '') + '</td>' +
                                '<td class="text-right">' + (!isDebit ? escapeHtml(formatMoney(r.kredit)) : '') + '</td>' +
                                '<td>' + escapeHtml(desc) + '</td>' +
                                '</tr>';
                        });
                    }
                    $('#salesJournalDetailRows').html(html);
                    $('#modalSalesJournal').modal('show');
                },
                error: function(xhr) {
                    notify('error', 'Gagal', 'Terjadi kesalahan saat memuat detail jurnal.');
                }
            });
        }

        toggleManualJournal();
        renderParentOptions('');
        loadList('');
        loadPurchaseJournalList('');
        loadSalesJournalList('');
    });
</script>
