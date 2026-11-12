<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>

    <div class="content-wrapper">
        <section class="content p-3 p-md-4">
            <style>
                .da-hero {
                    background: linear-gradient(135deg, #1e293b 0%, #0f4c81 55%, #127fad 100%);
                    border-radius: 14px;
                    color: #fff;
                    padding: 22px 26px;
                    box-shadow: 0 14px 30px rgba(15, 76, 129, .18);
                }
                .da-card {
                    background: #fff;
                    border: 1px solid #e2e8f0;
                    border-radius: 12px;
                    box-shadow: 0 8px 24px rgba(15, 23, 42, .05);
                    overflow: hidden;
                }
                .da-user-list {
                    max-height: 68vh;
                    overflow-y: auto;
                }
                .da-user-item {
                    cursor: pointer;
                    border-bottom: 1px solid #f1f5f9;
                    padding: 12px 15px;
                    transition: background .15s ease, border-left .15s ease;
                    border-left: 4px solid transparent;
                }
                .da-user-item:hover {
                    background: #f8fafc;
                }
                .da-user-item.active {
                    background: #eff6ff;
                    border-left-color: #127fad;
                }
                .da-summary-pill {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    padding: 6px 12px;
                    border-radius: 999px;
                    font-size: 13px;
                    font-weight: 700;
                }
                .da-section-tabs {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 6px;
                }
                .da-section-tab {
                    border: 1px solid #cbd5e1;
                    background: #fff;
                    color: #334155;
                    border-radius: 8px;
                    padding: 6px 12px;
                    font-size: 13px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all .15s ease;
                }
                .da-section-tab:hover,
                .da-section-tab.active {
                    background: #127fad;
                    border-color: #127fad;
                    color: #fff;
                }
                .da-section-block {
                    border: 1px solid #e2e8f0;
                    border-radius: 10px;
                    margin-bottom: 16px;
                    overflow: hidden;
                }
                .da-section-header {
                    background: #f8fafc;
                    padding: 12px 16px;
                    border-bottom: 1px solid #e2e8f0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 10px;
                }
                .da-menu-row {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 14px;
                    padding: 12px 16px;
                    border-bottom: 1px solid #f1f5f9;
                    transition: background .15s ease;
                }
                .da-menu-row:last-child {
                    border-bottom: none;
                }
                .da-menu-row.is-locked {
                    background: #fff5f5;
                }
                .da-menu-icon {
                    width: 42px;
                    height: 42px;
                    border-radius: 9px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    font-size: 18px;
                    flex-shrink: 0;
                    position: relative;
                }
                .da-menu-row.is-locked .da-menu-icon {
                    filter: grayscale(40%);
                    opacity: .85;
                }
                .da-tone-blue { background: linear-gradient(135deg, #2296d1, #41c1df); }
                .da-tone-orange { background: linear-gradient(135deg, #f05a24, #fb8b42); }
                .da-tone-slate { background: linear-gradient(135deg, #436b95, #6d8fb1); }
                .da-tone-green { background: linear-gradient(135deg, #3c9f08, #78bf2a); }
                .da-tone-red { background: linear-gradient(135deg, #b90f34, #e1184e); }
                .da-tone-lime { background: linear-gradient(135deg, #a6b700, #d2d900); }
                .da-tone-purple { background: linear-gradient(135deg, #9d1bbb, #cf25c9); }
                .da-tone-teal { background: linear-gradient(135deg, #2d929b, #50b8c0); }
                .da-tone-dark { background: linear-gradient(135deg, #343a40, #59626e); }
                .da-tone-cyan { background: linear-gradient(135deg, #1287a8, #26bad1); }
                .da-tone-brown { background: linear-gradient(135deg, #7c4a2d, #a46b48); }

                .custom-switch-lg .custom-control-label {
                    padding-left: 10px;
                    cursor: pointer;
                }
                .custom-switch-lg .custom-control-label::before {
                    height: 1.5rem;
                    width: 2.75rem;
                    border-radius: 3rem;
                    left: -2.75rem;
                }
                .custom-switch-lg .custom-control-label::after {
                    width: calc(1.5rem - 4px);
                    height: calc(1.5rem - 4px);
                    border-radius: 50%;
                    left: calc(-2.75rem + 2px);
                }
                .custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
                    transform: translateX(1.25rem);
                }
            </style>

            <div class="da-hero mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div>
                    <h3 class="mb-1 font-weight-bold">
                        <i class="fas fa-user-lock mr-2"></i>Pengaturan Hak Akses Menu Dashboard
                    </h3>
                    <div class="text-white-50">
                        Khusus Administrator: tentukan menu dashboard apa saja yang diizinkan atau dikunci (ikon gembok) untuk masing-masing user.
                    </div>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-light font-weight-bold shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- KOLOM KIRI: DAFTAR USER -->
                <div class="col-lg-4 mb-3">
                    <div class="da-card">
                        <div class="p-3 border-bottom bg-light">
                            <label class="font-weight-bold mb-1 d-block">Pilih User</label>
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" class="form-control" id="daSearchUser" placeholder="Cari nama, username, jobdesk...">
                            </div>
                            <select class="form-control form-control-sm" id="daFilterUserStatus">
                                <option value="all">Tampilkan Semua User</option>
                                <option value="locked_only">User dengan Menu Terkunci</option>
                            </select>
                        </div>
                        <div id="daUserList" class="da-user-list">
                            <div class="p-4 text-center text-muted">Memuat daftar user...</div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: MATRIX MENU DASHBOARD -->
                <div class="col-lg-8">
                    <div class="da-card">
                        <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
                            <div>
                                <h5 class="mb-0 font-weight-bold" id="daSelectedUserName">Pilih user di sebelah kiri</h5>
                                <div class="small text-muted mt-1" id="daSelectedUserMeta">
                                    Daftar menu dashboard akan tampil setelah user dipilih.
                                </div>
                            </div>
                            <div id="daSummaryPills" class="d-flex flex-wrap" style="gap: 8px; display: none !important;"></div>
                        </div>

                        <div id="daAdminNotice" class="alert alert-info m-3 mb-0" style="display: none;">
                            <i class="fas fa-user-shield mr-2"></i>
                            <strong>Akun Administrator:</strong> User ini memiliki role Admin sehingga selalu memiliki akses penuh ke semua modul dan tidak dapat dikunci.
                        </div>

                        <div id="daToolbar" class="p-3 border-bottom bg-light" style="display: none;">
                            <div class="row align-items-center mb-2">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white"><i class="fas fa-filter text-muted"></i></span>
                                        </div>
                                        <input type="text" class="form-control" id="daSearchMenu" placeholder="Cari nama menu atau URL (mis. Retur Pembelian)...">
                                    </div>
                                </div>
                                <div class="col-md-6 text-md-right" id="daBulkActions">
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold mr-1" id="daBtnAllowAll">
                                        <i class="fas fa-lock-open mr-1"></i> Izinkan Semua
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm font-weight-bold mr-1" id="daBtnLockAll">
                                        <i class="fas fa-lock mr-1"></i> Kunci Semua
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold" id="daBtnResetUser" title="Hapus pengaturan kustom dan kembalikan ke default">
                                        <i class="fas fa-undo mr-1"></i> Reset Default
                                    </button>
                                </div>
                            </div>
                            <div class="da-section-tabs" id="daSectionTabs"></div>
                        </div>

                        <div class="p-3" id="daMatrixContainer">
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-hand-pointer fa-2x mb-2 text-secondary"></i>
                                <p class="mb-0">Pilih salah satu user di daftar kiri untuk mengatur akses menu dashboard-nya.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
$(function() {
    let selectedUserId = 0;
    let activeSectionFilter = 'all';
    let currentMatrix = null;
    let userSearchTimer = null;

    function esc(val) {
        return $('<div>').text(val == null ? '' : val).html();
    }

    function notify(type, title, text) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: title,
                text: text || '',
                showConfirmButton: false,
                timer: 2600,
                timerProgressBar: true
            });
        } else {
            alert(title + (text ? ': ' + text : ''));
        }
    }

    function loadUsers() {
        const query = $('#daSearchUser').val() || '';
        const filterMode = $('#daFilterUserStatus').val();
        $.getJSON('<?= base_url('master/akses-dashboard/users') ?>', { q: query }, function(res) {
            if (!res.status) {
                $('#daUserList').html('<div class="p-3 text-danger">' + esc(res.message) + '</div>');
                return;
            }
            let rows = res.data || [];
            if (filterMode === 'locked_only') {
                rows = rows.filter(function(u) { return parseInt(u.locked_count, 10) > 0; });
            }

            let html = '';
            rows.forEach(function(u) {
                const isAdmin = parseInt(u.is_admin, 10) === 1;
                const lockedCount = parseInt(u.locked_count, 10) || 0;
                const isActive = parseInt(u.id, 10) === selectedUserId;
                let badge = '<span class="badge badge-success"><i class="fas fa-lock-open mr-1"></i>Semua Terbuka</span>';
                if (isAdmin) {
                    badge = '<span class="badge badge-primary"><i class="fas fa-user-shield mr-1"></i>Admin</span>';
                } else if (lockedCount > 0) {
                    badge = '<span class="badge badge-danger"><i class="fas fa-lock mr-1"></i>' + lockedCount + ' Terkunci</span>';
                }

                html += '<div class="da-user-item ' + (isActive ? 'active' : '') + '" data-id="' + esc(u.id) + '">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                        '<div class="font-weight-bold text-dark">' + esc(u.nm_karyawan || u.username || '-') + '</div>' +
                        badge +
                    '</div>' +
                    '<div class="small text-muted mt-1">' +
                        '<span class="font-weight-bold text-info">@' + esc(u.username || '-') + '</span> · ' +
                        esc(u.jobdesk || '-') + ' · LV ' + esc(u.akses_lv || '-') +
                    '</div>' +
                '</div>';
            });

            $('#daUserList').html(html || '<div class="p-4 text-center text-muted">User tidak ditemukan.</div>');
        });
    }

    function loadMatrix(userId) {
        selectedUserId = parseInt(userId, 10) || 0;
        if (!selectedUserId) return;

        $('#daMatrixContainer').html('<div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p class="mb-0">Memuat daftar menu...</p></div>');

        $.getJSON('<?= base_url('master/akses-dashboard/matrix/') ?>' + selectedUserId, function(res) {
            if (!res.status) {
                notify('error', 'Gagal', res.message);
                return;
            }
            currentMatrix = res.data;
            renderMatrix();
        });
    }

    function renderMatrix() {
        if (!currentMatrix) return;

        const user = currentMatrix.user || {};
        const sections = currentMatrix.sections || {};
        const summary = currentMatrix.summary || {};
        const isAdmin = parseInt(user.is_admin, 10) === 1;

        $('#daSelectedUserName').html(esc(user.nm_karyawan || user.username || '-') + ' <small class="text-muted font-weight-normal">(@' + esc(user.username || '-') + ')</small>');
        $('#daSelectedUserMeta').text((user.departemen || '-') + ' · ' + (user.jobdesk || '-') + ' · Level ' + (user.akses_lv || '-'));

        $('#daSummaryPills').attr('style', 'display: flex !important; gap: 8px;').html(
            '<span class="da-summary-pill bg-light text-dark border"><i class="fas fa-th-large text-primary"></i> ' + (summary.total_unique || 0) + ' Modul</span>' +
            '<span class="da-summary-pill bg-success text-white"><i class="fas fa-lock-open"></i> ' + (summary.allowed_unique || 0) + ' Terbuka</span>' +
            '<span class="da-summary-pill bg-danger text-white"><i class="fas fa-lock"></i> ' + (summary.locked_unique || 0) + ' Terkunci</span>'
        );

        $('#daToolbar').show();
        if (isAdmin) {
            $('#daAdminNotice').show();
            $('#daBulkActions').hide();
        } else {
            $('#daAdminNotice').hide();
            $('#daBulkActions').show();
        }

        // Render tab filter section
        let tabHtml = '<button type="button" class="da-section-tab ' + (activeSectionFilter === 'all' ? 'active' : '') + '" data-section="all">Semua Bagian</button>';
        Object.keys(sections).forEach(function(secKey) {
            const sec = sections[secKey];
            tabHtml += '<button type="button" class="da-section-tab ' + (activeSectionFilter === secKey ? 'active' : '') + '" data-section="' + esc(secKey) + '">' +
                '<i class="' + esc(sec.icon) + ' mr-1"></i>' + esc(sec.label) +
            '</button>';
        });
        $('#daSectionTabs').html(tabHtml);

        const keyword = ($('#daSearchMenu').val() || '').trim().toLowerCase();
        let html = '';

        Object.keys(sections).forEach(function(secKey) {
            if (activeSectionFilter !== 'all' && activeSectionFilter !== secKey) {
                return;
            }
            const sec = sections[secKey];
            const filteredMenus = (sec.menus || []).filter(function(m) {
                if (!keyword) return true;
                const hay = (m.title + ' ' + m.route + ' ' + m.description + ' ' + m.menu_key).toLowerCase();
                return hay.indexOf(keyword) !== -1;
            });

            if (filteredMenus.length === 0) return;

            html += '<div class="da-section-block">' +
                '<div class="da-section-header">' +
                    '<div>' +
                        '<div class="font-weight-bold text-dark"><i class="' + esc(sec.icon) + ' mr-2 text-primary"></i>' + esc(sec.label) + '</div>' +
                        '<div class="small text-muted">' + esc(sec.description || '') + '</div>' +
                    '</div>' +
                    (!isAdmin ? (
                        '<div class="btn-group btn-group-sm">' +
                            '<button type="button" class="btn btn-outline-success da-btn-sec-allow" data-section="' + esc(secKey) + '"><i class="fas fa-lock-open mr-1"></i>Izinkan Semua</button>' +
                            '<button type="button" class="btn btn-outline-danger da-btn-sec-lock" data-section="' + esc(secKey) + '"><i class="fas fa-lock mr-1"></i>Kunci Semua</button>' +
                        '</div>'
                    ) : '') +
                '</div>';

            filteredMenus.forEach(function(menu) {
                const allowed = parseInt(menu.is_allowed, 10) === 1;
                const switchId = 'sw_' + secKey + '_' + menu.menu_key;
                const secLabels = (menu.section_labels || []).join(', ');
                const multiNote = (menu.section_labels && menu.section_labels.length > 1)
                    ? '<span class="badge badge-light border text-muted ml-1" title="Perubahan di sini berlaku di semua tab tempat menu ini tampil">Tersinkron di: ' + esc(secLabels) + '</span>'
                    : '';

                html += '<div class="da-menu-row ' + (!allowed ? 'is-locked' : '') + '">' +
                    '<div class="d-flex align-items-center" style="gap: 12px; min-width: 0; flex: 1;">' +
                        '<div class="da-menu-icon da-tone-' + esc(menu.tone || 'blue') + '">' +
                            '<i class="' + esc(menu.icon || 'fas fa-th-large') + '"></i>' +
                        '</div>' +
                        '<div style="min-width: 0;">' +
                            '<div class="font-weight-bold text-dark">' +
                                esc(menu.title) +
                                ' <code class="small text-muted ml-1">/' + esc(menu.route) + '</code>' +
                                multiNote +
                            '</div>' +
                            '<div class="small text-muted text-truncate">' + esc(menu.description || '') + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="d-flex align-items-center flex-shrink-0" style="gap: 14px;">' +
                        (allowed
                            ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-lock-open mr-1"></i>Terbuka</span>'
                            : '<span class="badge badge-danger px-2 py-1"><i class="fas fa-lock mr-1"></i>Terkunci</span>'
                        ) +
                        '<div class="custom-control custom-switch custom-switch-lg">' +
                            '<input type="checkbox" class="custom-control-input da-menu-toggle" id="' + esc(switchId) + '" data-key="' + esc(menu.menu_key) + '" ' + (allowed ? 'checked' : '') + ' ' + (isAdmin ? 'disabled' : '') + '>' +
                            '<label class="custom-control-label" for="' + esc(switchId) + '"></label>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            });

            html += '</div>';
        });

        $('#daMatrixContainer').html(html || '<div class="text-center text-muted py-4">Tidak ada menu yang cocok dengan pencarian Anda.</div>');
    }

    // Event: cari user & filter user
    $('#daSearchUser').on('input', function() {
        clearTimeout(userSearchTimer);
        userSearchTimer = setTimeout(loadUsers, 250);
    });
    $('#daFilterUserStatus').on('change', loadUsers);

    // Event: pilih user
    $('#daUserList').on('click', '.da-user-item', function() {
        $('.da-user-item').removeClass('active');
        $(this).addClass('active');
        loadMatrix($(this).data('id'));
    });

    // Event: filter section & cari menu
    $('#daSectionTabs').on('click', '.da-section-tab', function() {
        activeSectionFilter = $(this).data('section');
        renderMatrix();
    });
    $('#daSearchMenu').on('input', function() {
        renderMatrix();
    });

    // Event: toggle satu menu
    $('#daMatrixContainer').on('change', '.da-menu-toggle', function() {
        if (!selectedUserId) return;
        const menuKey = $(this).data('key');
        const isAllowed = this.checked ? 1 : 0;

        $.post('<?= base_url('master/akses-dashboard/update') ?>', {
            user_id: selectedUserId,
            menu_key: menuKey,
            is_allowed: isAllowed
        }, function(res) {
            if (res.status) {
                notify('success', 'Tersimpan', res.message);
                if (res.data && res.data.matrix) {
                    currentMatrix = res.data.matrix;
                    renderMatrix();
                } else {
                    loadMatrix(selectedUserId);
                }
                loadUsers();
            } else {
                notify('error', 'Gagal', res.message);
                loadMatrix(selectedUserId);
            }
        }, 'json').fail(function(xhr) {
            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan server.';
            notify('error', 'Error', msg);
            loadMatrix(selectedUserId);
        });
    });

    // Helper bulk update
    function runBulk(mode, sectionKey, isAllowed) {
        if (!selectedUserId) return;
        $.post('<?= base_url('master/akses-dashboard/bulk') ?>', {
            user_id: selectedUserId,
            mode: mode,
            section_key: sectionKey || '',
            is_allowed: isAllowed
        }, function(res) {
            if (res.status) {
                notify('success', 'Berhasil', res.message);
                if (res.data && res.data.matrix) {
                    currentMatrix = res.data.matrix;
                    renderMatrix();
                }
                loadUsers();
            } else {
                notify('error', 'Gagal', res.message);
            }
        }, 'json');
    }

    $('#daBtnAllowAll').on('click', function() {
        runBulk('all', '', 1);
    });

    $('#daBtnLockAll').on('click', function() {
        Swal.fire({
            title: 'Kunci Semua Menu?',
            text: 'Seluruh menu dashboard untuk user ini akan diberi ikon gembok dan tidak bisa dibuka.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Ya, Kunci Semua',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                runBulk('all', '', 0);
            }
        });
    });

    $('#daMatrixContainer').on('click', '.da-btn-sec-allow', function() {
        runBulk('section', $(this).data('section'), 1);
    });

    $('#daMatrixContainer').on('click', '.da-btn-sec-lock', function() {
        runBulk('section', $(this).data('section'), 0);
    });

    $('#daBtnResetUser').on('click', function() {
        if (!selectedUserId) return;
        $.post('<?= base_url('master/akses-dashboard/reset/') ?>' + selectedUserId, {}, function(res) {
            if (res.status) {
                notify('success', 'Direset', res.message);
                if (res.data && res.data.matrix) {
                    currentMatrix = res.data.matrix;
                    renderMatrix();
                }
                loadUsers();
            } else {
                notify('error', 'Gagal', res.message);
            }
        }, 'json');
    });

    loadUsers();
});
</script>
</body>

