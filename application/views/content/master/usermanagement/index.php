<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>
    <div class="content-wrapper">
        <section class="content p-3 p-md-4">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
            <style>
                .erp-page{color:#172033}
                .erp-hero{background:linear-gradient(135deg,#0f766e,#2563eb);border-radius:18px;color:#fff;padding:22px 24px;box-shadow:0 18px 40px rgba(37,99,235,.22)}
                .erp-panel{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 12px 30px rgba(15,23,42,.08);overflow:hidden}
                .erp-panel-header{padding:16px 18px;border-bottom:1px solid #edf2f7}
                .btn-erp{background:linear-gradient(135deg,#2563eb,#0f766e);border:0;color:#fff}.btn-erp:hover{color:#fff;filter:brightness(.95)}
                .filter-grid{display:grid;gap:8px;grid-template-columns:repeat(5,minmax(0,1fr))}
                .erp-page .form-control,.select2-container--default .select2-selection--single{border-radius:10px;border-color:#d7dee8}.select2-container .select2-selection--single{height:38px}.select2-selection__rendered{line-height:36px!important}.select2-selection__arrow{height:36px!important}
                table.dataTable thead th{position:sticky;top:0;background:#f8fafc!important;z-index:2}.badge-soft-success{background:#dcfce7;color:#166534}.badge-soft-danger{background:#fee2e2;color:#991b1b}
                .avatar{width:38px;height:38px;border-radius:50%;object-fit:cover;background:#e2e8f0}.action-group{display:inline-flex;gap:6px;flex-wrap:nowrap;white-space:nowrap}
                #tableUsers td,#tableUsers th{vertical-align:middle;padding:.55rem .75rem}
                .skeleton{min-height:72px;border-radius:12px;background:linear-gradient(90deg,#f1f5f9,#e2e8f0,#f1f5f9);background-size:200% 100%;animation:shimmer 1.1s infinite}@keyframes shimmer{to{background-position:-200% 0}}

                /* Navigasi Tab Terpadu User Management */
                .um-tabs{display:flex;flex-wrap:wrap;gap:8px;background:#fff;padding:8px;border-radius:14px;border:1px solid #e5e7eb;box-shadow:0 6px 18px rgba(15,23,42,.05)}
                .um-tabs .nav-link{border-radius:10px;color:#475569;font-weight:600;padding:10px 16px;border:0;display:inline-flex;align-items:center;gap:8px;transition:all .15s ease;cursor:pointer}
                .um-tabs .nav-link:hover{background:#f1f5f9;color:#0f172a}
                .um-tabs .nav-link.active{background:linear-gradient(135deg,#0f766e,#2563eb);color:#fff;box-shadow:0 6px 16px rgba(37,99,235,.22)}

                /* Panel Scrollable untuk Akses Dashboard & Fasilitas Per User */
                .da-user-list,.fac-user-list{max-height:55vh;overflow-y:auto}
                .da-matrix-container,.fac-matrix-container{max-height:62vh;overflow-y:auto}
                @media(min-width:992px){
                    .um-split-card{height:calc(100vh - 285px);min-height:460px;display:flex;flex-direction:column}
                    .um-split-card>:not(.da-user-list):not(.da-matrix-container):not(.fac-user-list):not(.fac-matrix-container){flex-shrink:0}
                    .da-user-list,.da-matrix-container,.fac-user-list,.fac-matrix-container{flex:1 1 auto;min-height:0;max-height:none;overflow-y:auto}
                }

                .da-user-item,.fac-user-item{cursor:pointer;border-bottom:1px solid #f1f5f9;padding:12px 15px;transition:background .15s ease,border-left .15s ease;border-left:4px solid transparent}
                .da-user-item:hover,.fac-user-item:hover{background:#f8fafc}
                .da-user-item.active,.fac-user-item.active{background:#eff6ff;border-left-color:#127fad}

                /* Styling Akses Menu Dashboard */
                .da-summary-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;font-size:13px;font-weight:700}
                .da-section-tabs{display:flex;flex-wrap:wrap;gap:6px}
                .da-section-tab{border:1px solid #cbd5e1;background:#fff;color:#334155;border-radius:8px;padding:6px 12px;font-size:13px;font-weight:600;cursor:pointer;transition:all .15s ease}
                .da-section-tab:hover,.da-section-tab.active{background:#127fad;border-color:#127fad;color:#fff}
                .da-section-block{border:1px solid #e2e8f0;border-radius:10px;margin-bottom:16px;overflow:hidden}
                .da-section-header{background:#f8fafc;padding:12px 16px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
                .da-menu-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 16px;border-bottom:1px solid #f1f5f9;transition:background .15s ease}
                .da-menu-row:last-child{border-bottom:none}
                .da-menu-row.is-locked{background:#fff5f5}
                .da-menu-icon{width:42px;height:42px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:18px;flex-shrink:0}
                .da-menu-row.is-locked .da-menu-icon{filter:grayscale(40%);opacity:.85}
                .da-tone-blue{background:linear-gradient(135deg,#2296d1,#41c1df)}
                .da-tone-orange{background:linear-gradient(135deg,#f05a24,#fb8b42)}
                .da-tone-slate{background:linear-gradient(135deg,#436b95,#6d8fb1)}
                .da-tone-green{background:linear-gradient(135deg,#3c9f08,#78bf2a)}
                .da-tone-red{background:linear-gradient(135deg,#b90f34,#e1184e)}
                .da-tone-lime{background:linear-gradient(135deg,#a6b700,#d2d900)}
                .da-tone-purple{background:linear-gradient(135deg,#9d1bbb,#cf25c9)}
                .da-tone-teal{background:linear-gradient(135deg,#2d929b,#50b8c0)}
                .da-tone-dark{background:linear-gradient(135deg,#343a40,#59626e)}
                .da-tone-cyan{background:linear-gradient(135deg,#1287a8,#26bad1)}
                .da-tone-brown{background:linear-gradient(135deg,#7c4a2d,#a46b48)}
                .custom-switch-lg .custom-control-label{padding-left:10px;cursor:pointer}
                .custom-switch-lg .custom-control-label::before{height:1.5rem;width:2.75rem;border-radius:3rem;left:-2.75rem}
                .custom-switch-lg .custom-control-label::after{width:calc(1.5rem - 4px);height:calc(1.5rem - 4px);border-radius:50%;left:calc(-2.75rem + 2px)}
                .custom-switch-lg .custom-control-input:checked~.custom-control-label::after{transform:translateX(1.25rem)}

                /* Styling Fasilitas Per User */
                .facility-toggle{width:1.15rem;height:1.15rem;cursor:pointer}
                .facility-group-title td{background:#f8fafc!important;color:#334155;font-weight:700;padding:10px 14px}
                .fac-matrix-container thead th{position:sticky;top:0;background:#fff!important;z-index:2;border-bottom:2px solid #e2e8f0}

                @media(max-width:768px){.erp-hero{padding:18px}.filter-grid{grid-template-columns:1fr!important}.action-group .btn{flex:1}}
            </style>

            <div class="erp-page">
                <div class="erp-hero mb-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center" style="gap:12px;">
                        <div>
                            <h3 class="mb-1 font-weight-bold"><i class="fas fa-users-cog mr-2"></i>User Management</h3>
                            <div style="opacity:.85;">Pusat pengelolaan akun user, hak akses menu dashboard, dan fasilitas per user dalam satu tempat.</div>
                        </div>
                        <div class="d-flex flex-wrap" style="gap:8px;">
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-light font-weight-bold"><i class="fas fa-arrow-left mr-1"></i> Dashboard</a>
                        </div>
                    </div>
                </div>

                <!-- Tab Navigasi Terpadu -->
                <ul class="nav nav-pills um-tabs mb-3" id="umMainTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="tabBtnUsers" data-toggle="pill" href="#paneUsers" data-tab-key="users" role="tab">
                            <i class="fas fa-users"></i> Data User
                        </a>
                    </li>
                    <?php if (!empty($can_dashboard_access)) : ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tabBtnDashboardAccess" data-toggle="pill" href="#paneDashboardAccess" data-tab-key="akses-dashboard" role="tab">
                            <i class="fas fa-user-lock"></i> Akses Menu Dashboard
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($can_user_facility)) : ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="tabBtnUserFacility" data-toggle="pill" href="#paneUserFacility" data-tab-key="fasilitas" role="tab">
                            <i class="fas fa-sliders-h"></i> Fasilitas Per User
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="tab-content" id="umMainTabContent">
                    <!-- TAB 1: DATA USER -->
                    <div class="tab-pane fade show active" id="paneUsers" role="tabpanel">
                        <div class="erp-panel mb-3">
                            <div class="erp-panel-header d-flex flex-column flex-xl-row justify-content-between align-items-xl-center" style="gap:10px;">
                                <div class="filter-grid flex-grow-1">
                                    <select id="filter_departemen" class="form-control"><option value="">Departemen</option><?php foreach($departemen as $d): ?><option value="<?= html_escape($d['id']) ?>"><?= html_escape($d['text']) ?></option><?php endforeach ?></select>
                                    <select id="filter_jobdesk" class="form-control"><option value="">Bagian / Jobdesk</option><?php foreach($jobdesk as $j): ?><option value="<?= html_escape($j['nama_jobdesk']) ?>"><?= html_escape($j['nama_jobdesk']) ?></option><?php endforeach ?></select>
                                    <select id="filter_akses" class="form-control"><option value="">Level Akses</option><?php foreach($akses_level as $a): ?><option value="<?= html_escape($a['id_akses_level']) ?>"><?= html_escape($a['nama_akses_level']) ?></option><?php endforeach ?></select>
                                    <select id="filter_status" class="form-control"><option value="">Status</option><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
                                    <button class="btn btn-outline-secondary" id="btnResetFilter"><i class="fas fa-undo mr-1"></i> Reset</button>
                                </div>
                                <div class="flex-shrink-0">
                                    <button class="btn btn-erp font-weight-bold w-100" id="btnAdd"><i class="fas fa-user-plus mr-1"></i> Tambah User</button>
                                </div>
                            </div>
                            <div class="p-3">
                                <div id="tableSkeleton" class="skeleton mb-2"></div>
                                <table class="table table-hover w-100 compact" id="tableUsers">
                                    <thead><tr><th>User</th><th>NIK</th><th>Departemen</th><th>Jobdesk</th><th>Akses</th><th>Status</th><th>Aksi</th></tr></thead>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($can_dashboard_access)) : ?>
                    <!-- TAB 2: AKSES MENU DASHBOARD -->
                    <div class="tab-pane fade" id="paneDashboardAccess" role="tabpanel">
                        <div class="row">
                            <!-- KOLOM KIRI: DAFTAR USER -->
                            <div class="col-lg-4 mb-3 mb-lg-0">
                                <div class="erp-panel um-split-card">
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
                                <div class="erp-panel um-split-card">
                                    <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
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

                                    <div class="p-3 da-matrix-container" id="daMatrixContainer">
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-hand-pointer fa-2x mb-2 text-secondary"></i>
                                            <p class="mb-0">Pilih salah satu user di daftar kiri untuk mengatur akses menu dashboard-nya.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($can_user_facility)) : ?>
                    <!-- TAB 3: FASILITAS PER USER -->
                    <div class="tab-pane fade" id="paneUserFacility" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-4 mb-3 mb-lg-0">
                                <div class="erp-panel um-split-card">
                                    <div class="p-3 border-bottom bg-light">
                                        <label class="font-weight-bold mb-1 d-block">Cari User</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                            </div>
                                            <input type="text" class="form-control" id="facSearchUser" placeholder="Nama / username / jobdesk">
                                        </div>
                                    </div>
                                    <div id="facUserList" class="fac-user-list">
                                        <div class="p-4 text-center text-muted">Memuat user...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="erp-panel um-split-card">
                                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
                                        <div>
                                            <h5 class="mb-0 font-weight-bold" id="facSelectedUserTitle">Pilih user</h5>
                                            <div class="small text-muted mt-1" id="facSelectedUserMeta">Matrix fasilitas akan tampil setelah user dipilih.</div>
                                        </div>
                                    </div>
                                    <div class="p-3 table-responsive fac-matrix-container">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Fasilitas</th>
                                                    <th>Module</th>
                                                    <th class="text-center">Default / Override</th>
                                                    <th class="text-center">Diizinkan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="facBody">
                                                <tr><td colspan="4" class="text-center text-muted py-5">Belum ada user dipilih.</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal Tambah / Edit User -->
<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="formUser" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Form User</h5>
                <button type="button" class="close js-close-user-modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="id" name="id">
                <div class="row">
                    <div class="col-md-6 form-group"><label>NIK</label><input class="form-control" name="nik" id="nik" required></div>
                    <div class="col-md-6 form-group"><label>Nama</label><input class="form-control" name="nm_karyawan" id="nm_karyawan" required></div>
                    <div class="col-md-6 form-group"><label>Username</label><input class="form-control" name="username" id="username" required></div>
                    <div class="col-md-6 form-group"><label>Password</label><input type="password" class="form-control" name="password" id="password" placeholder="Isi saat tambah / reset manual"></div>
                    <div class="col-md-4 form-group"><label>Departemen</label><input class="form-control" name="departemen" id="departemen" list="listDepartemen"></div>
                    <div class="col-md-4 form-group"><label>Jobdesk</label><select class="form-control select2" name="jobdesk_id" id="jobdesk_id"><option value="">Pilih</option><?php foreach($jobdesk as $j): ?><option value="<?= html_escape($j['id_jobdesk'] ?? '') ?>" data-text="<?= html_escape($j['nama_jobdesk']) ?>"><?= html_escape($j['nama_jobdesk']) ?></option><?php endforeach ?></select><input type="hidden" name="jobdesk" id="jobdesk"></div>
                    <div class="col-md-4 form-group"><label>Akses Level</label><select class="form-control select2" name="akses_lv_id" id="akses_lv_id"><option value="">Pilih</option><?php foreach($akses_level as $a): ?><option value="<?= html_escape($a['id_akses_level'] ?? '') ?>" data-text="<?= html_escape($a['nama_akses_level']) ?>"><?= html_escape($a['nama_akses_level']) ?></option><?php endforeach ?></select><input type="hidden" name="akses_lv" id="akses_lv"></div>
                    <div class="col-md-4 form-group"><label>Tim</label><input type="number" class="form-control" name="tim" id="tim" value="0"></div>
                    <div class="col-md-4 form-group"><label>Wilayah</label><input type="number" class="form-control" name="wilayah" id="wilayah" value="0"></div>
                    <div class="col-md-4 form-group"><label>Status</label><select class="form-control" name="status" id="status"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                    <div class="col-md-12 form-group mb-0"><label>Foto Profile</label><input type="file" class="form-control" name="foto" accept="image/*"></div>
                </div>
                <datalist id="listDepartemen"><?php foreach($departemen as $d): ?><option value="<?= html_escape($d['id']) ?>"></option><?php endforeach ?></datalist>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary js-close-user-modal" data-dismiss="modal">Batal</button>
                <button class="btn btn-erp" type="submit"><i class="fas fa-save mr-1"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function(){
    const canDashboardAccess = <?= !empty($can_dashboard_access) ? 'true' : 'false' ?>;
    const canUserFacility = <?= !empty($can_user_facility) ? 'true' : 'false' ?>;

    function esc(val) {
        return $('<div>').text(val == null ? '' : val).html();
    }

    // =========================================================
    // 1. TAB DATA USER
    // =========================================================
    $('.select2').select2({theme:'bootstrap4', width:'100%', tags:true, dropdownParent: $('#modalUser')});
    $('.js-close-user-modal').on('click', function(e){
        e.preventDefault();
        $('#modalUser').modal('hide');
    });
    function selectedText(id){ return $('#' + id + ' option:selected').data('text') || $('#' + id + ' option:selected').text(); }
    const levelLabels = {1:'1 - Karyawan', 2:'2 - Koordinator', 3:'3 - Manager', 4:'4 - Kadep', 5:'5 - Direktur', 99:'99 - Super Admin'};

    const table = $('#tableUsers').DataTable({
        processing:true, serverSide:true, responsive:true, searchDelay:350,
        ajax:{url:'<?= base_url('master/user-management/list') ?>', type:'POST', data:function(d){d.departemen=$('#filter_departemen').val();d.jobdesk=$('#filter_jobdesk').val();d.akses_lv=$('#filter_akses').val();d.status=$('#filter_status').val();}},
        columns:[
            {data:null, render:r=>`<div class="d-flex align-items-center" style="gap:8px;"><img class="avatar" src="${r.foto ? '<?= base_url() ?>'+r.foto : '<?= base_url('assets/images/Karisma.png') ?>'}"><div><div class="font-weight-bold">${esc(r.nm_karyawan||'-')}</div><small class="text-muted">${esc(r.username||'-')}</small></div></div>`},
            {data:'nik'}, {data:'departemen'}, {data:'jobdesk'},
            {data:'akses_lv', render:v=>`<span class="badge badge-light border">${levelLabels[parseInt(v)] || ('Lv ' + (v||1))}</span>`},
            {data:null, render:r=> r.status === undefined ? '<span class="badge badge-secondary">Legacy</span>' : (parseInt(r.status)===1 ? '<span class="badge badge-soft-success">Aktif</span>' : '<span class="badge badge-soft-danger">Nonaktif</span>')},
            {data:null, orderable:false, searchable:false, render:r=>`<div class="action-group"><button class="btn btn-sm btn-outline-primary btn-edit" data-id="${r.id}" title="Edit User"><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-outline-warning btn-reset" data-id="${r.id}" title="Reset Password"><i class="fas fa-key"></i></button><button class="btn btn-sm btn-outline-secondary btn-toggle" data-id="${r.id}" title="Aktif/Nonaktifkan"><i class="fas fa-power-off"></i></button><button class="btn btn-sm btn-outline-danger btn-delete" data-id="${r.id}" title="Hapus User"><i class="fas fa-trash"></i></button></div>`}
        ],
        initComplete:function(){ $('#tableSkeleton').hide(); }
    });

    $('#filter_departemen,#filter_jobdesk,#filter_akses,#filter_status').on('change',()=>table.ajax.reload());
    $('#btnResetFilter').on('click',function(){ $('.filter-grid select').val(''); table.ajax.reload(); });
    $('#btnAdd').on('click',function(){ $('#formUser')[0].reset(); $('#id').val(''); $('.select2').val('').trigger('change'); $('#modalUser').modal('show'); });
    $('#jobdesk_id').on('change',()=>$('#jobdesk').val(selectedText('jobdesk_id')));
    $('#akses_lv_id').on('change',()=>$('#akses_lv').val($('#akses_lv_id').val() || selectedText('akses_lv_id')));

    $('#tableUsers').on('click','.btn-edit',function(){
        $.getJSON('<?= base_url('master/user-management/detail/') ?>'+$(this).data('id'),function(res){
            if(!res.status){toastr.error(res.message);return;}
            $.each(res.data,function(k,v){$('#'+k).val(v);});
            const jdVal = res.data.jobdesk || res.data.jobdesk_id || '';
            if(jdVal && $('#jobdesk_id option[value="'+jdVal+'"]').length===0){ $('#jobdesk_id').append(new Option(jdVal, jdVal, true, true)); }
            $('#jobdesk_id').val(jdVal).trigger('change');
            $('#akses_lv_id').val(String(res.data.akses_lv || res.data.akses_lv_id || '1')).trigger('change');
            $('#password').val('');
            $('#modalUser').modal('show');
        });
    });

    $('#formUser').on('submit',function(e){
        e.preventDefault();
        $('#jobdesk').val(selectedText('jobdesk_id'));
        $('#akses_lv').val($('#akses_lv_id').val() || selectedText('akses_lv_id'));
        const id=$('#id').val();
        const url=id?'<?= base_url('master/user-management/update/') ?>'+id:'<?= base_url('master/user-management/save') ?>';
        $.ajax({
            url:url,type:'POST',data:new FormData(this),processData:false,contentType:false,dataType:'json',
            success:function(res){
                res.status?toastr.success(res.message):toastr.error(res.message);
                if(res.status){
                    $('#modalUser').modal('hide');
                    table.ajax.reload(null,false);
                    daUsersLoaded = false;
                    facUsersLoaded = false;
                }
            },
            error:function(){toastr.error('Server tidak merespons.');}
        });
    });

    $('#tableUsers').on('click','.btn-delete',function(){
        const id=$(this).data('id');
        Swal.fire({title:'Hapus user?',icon:'warning',showCancelButton:true,confirmButtonText:'Hapus'}).then(r=>{
            if(r.isConfirmed) $.post('<?= base_url('master/user-management/delete/') ?>'+id,function(res){
                res.status?toastr.success(res.message):toastr.error(res.message);
                table.ajax.reload(null,false);
                daUsersLoaded = false;
                facUsersLoaded = false;
            },'json');
        });
    });
    $('#tableUsers').on('click','.btn-toggle',function(){
        $.post('<?= base_url('master/user-management/toggle-status/') ?>'+$(this).data('id'),function(res){
            res.status?toastr.success(res.message):toastr.warning(res.message);
            table.ajax.reload(null,false);
        },'json');
    });
    $('#tableUsers').on('click','.btn-reset',function(){
        const id=$(this).data('id');
        Swal.fire({title:'Reset Password',input:'password',inputPlaceholder:'Password baru minimal 6 karakter',showCancelButton:true,confirmButtonText:'Reset'}).then(r=>{
            if(r.isConfirmed) $.post('<?= base_url('master/user-management/reset-password/') ?>'+id,{password:r.value},function(res){
                res.status?toastr.success(res.message):toastr.error(res.message);
            },'json');
        });
    });

    // =========================================================
    // 2. TAB AKSES MENU DASHBOARD
    // =========================================================
    let daSelectedUserId = 0;
    let daActiveSectionFilter = 'all';
    let daCurrentMatrix = null;
    let daUserSearchTimer = null;
    let daUsersLoaded = false;

    function daLoadUsers(callback) {
        if (!canDashboardAccess) return;
        const query = $('#daSearchUser').val() || '';
        const filterMode = $('#daFilterUserStatus').val();
        $.getJSON('<?= base_url('master/akses-dashboard/users') ?>', { q: query }, function(res) {
            if (!res.status) {
                $('#daUserList').html('<div class="p-3 text-danger">' + esc(res.message) + '</div>');
                return;
            }
            daUsersLoaded = true;
            let rows = res.data || [];
            if (filterMode === 'locked_only') {
                rows = rows.filter(function(u) { return parseInt(u.locked_count, 10) > 0; });
            }

            let html = '';
            rows.forEach(function(u) {
                const isAdmin = parseInt(u.is_admin, 10) === 1;
                const lockedCount = parseInt(u.locked_count, 10) || 0;
                const isActive = parseInt(u.id, 10) === daSelectedUserId;
                let badge = '<span class="badge badge-success"><i class="fas fa-lock-open mr-1"></i>Semua Terbuka</span>';
                if (isAdmin) {
                    badge = '<span class="badge badge-primary"><i class="fas fa-user-shield mr-1"></i>Admin</span>';
                } else if (lockedCount > 0) {
                    badge = '<span class="badge badge-danger"><i class="fas fa-lock mr-1"></i>' + lockedCount + ' Terkunci</span>';
                }

                html += '<div class="da-user-item ' + (isActive ? 'active' : '') + '" data-id="' + esc(u.id) + '">' +
                    '<div class="d-flex justify-content-between align-items-start" style="gap:8px;">' +
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
            if (typeof callback === 'function') callback();
        });
    }

    function daLoadMatrix(userId) {
        if (!canDashboardAccess) return;
        daSelectedUserId = parseInt(userId, 10) || 0;
        if (!daSelectedUserId) return;

        $('#daUserList .da-user-item').removeClass('active');
        $('#daUserList .da-user-item[data-id="' + daSelectedUserId + '"]').addClass('active');

        $('#daMatrixContainer').html('<div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p class="mb-0">Memuat daftar menu...</p></div>');

        $.getJSON('<?= base_url('master/akses-dashboard/matrix/') ?>' + daSelectedUserId, function(res) {
            if (!res.status) {
                toastr.error(res.message);
                return;
            }
            daCurrentMatrix = res.data;
            daRenderMatrix();
        });
    }

    function daRenderMatrix() {
        if (!daCurrentMatrix) return;

        const user = daCurrentMatrix.user || {};
        const sections = daCurrentMatrix.sections || {};
        const summary = daCurrentMatrix.summary || {};
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

        let tabHtml = '<button type="button" class="da-section-tab ' + (daActiveSectionFilter === 'all' ? 'active' : '') + '" data-section="all">Semua Bagian</button>';
        Object.keys(sections).forEach(function(secKey) {
            const sec = sections[secKey];
            tabHtml += '<button type="button" class="da-section-tab ' + (daActiveSectionFilter === secKey ? 'active' : '') + '" data-section="' + esc(secKey) + '">' +
                '<i class="' + esc(sec.icon) + ' mr-1"></i>' + esc(sec.label) +
            '</button>';
        });
        $('#daSectionTabs').html(tabHtml);

        const keyword = ($('#daSearchMenu').val() || '').trim().toLowerCase();
        let html = '';

        Object.keys(sections).forEach(function(secKey) {
            if (daActiveSectionFilter !== 'all' && daActiveSectionFilter !== secKey) {
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

    if (canDashboardAccess) {
        $('#daSearchUser').on('input', function() {
            clearTimeout(daUserSearchTimer);
            daUserSearchTimer = setTimeout(daLoadUsers, 250);
        });
        $('#daFilterUserStatus').on('change', function() { daLoadUsers(); });
        $('#daUserList').on('click', '.da-user-item', function() {
            daLoadMatrix($(this).data('id'));
        });
        $('#daSectionTabs').on('click', '.da-section-tab', function() {
            daActiveSectionFilter = $(this).data('section');
            daRenderMatrix();
        });
        $('#daSearchMenu').on('input', daRenderMatrix);

        $('#daMatrixContainer').on('change', '.da-menu-toggle', function() {
            if (!daSelectedUserId) return;
            const menuKey = $(this).data('key');
            const isAllowed = this.checked ? 1 : 0;

            $.post('<?= base_url('master/akses-dashboard/update') ?>', {
                user_id: daSelectedUserId,
                menu_key: menuKey,
                is_allowed: isAllowed
            }, function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    if (res.data && res.data.matrix) {
                        daCurrentMatrix = res.data.matrix;
                        daRenderMatrix();
                    } else {
                        daLoadMatrix(daSelectedUserId);
                    }
                    daLoadUsers();
                } else {
                    toastr.error(res.message);
                    daLoadMatrix(daSelectedUserId);
                }
            }, 'json').fail(function(xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan server.';
                toastr.error(msg);
                daLoadMatrix(daSelectedUserId);
            });
        });

        function daRunBulk(mode, sectionKey, isAllowed) {
            if (!daSelectedUserId) return;
            $.post('<?= base_url('master/akses-dashboard/bulk') ?>', {
                user_id: daSelectedUserId,
                mode: mode,
                section_key: sectionKey || '',
                is_allowed: isAllowed
            }, function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    if (res.data && res.data.matrix) {
                        daCurrentMatrix = res.data.matrix;
                        daRenderMatrix();
                    }
                    daLoadUsers();
                } else {
                    toastr.error(res.message);
                }
            }, 'json');
        }

        $('#daBtnAllowAll').on('click', function() { daRunBulk('all', '', 1); });
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
                if (result.isConfirmed) daRunBulk('all', '', 0);
            });
        });
        $('#daMatrixContainer').on('click', '.da-btn-sec-allow', function() { daRunBulk('section', $(this).data('section'), 1); });
        $('#daMatrixContainer').on('click', '.da-btn-sec-lock', function() { daRunBulk('section', $(this).data('section'), 0); });
        $('#daBtnResetUser').on('click', function() {
            if (!daSelectedUserId) return;
            $.post('<?= base_url('master/akses-dashboard/reset/') ?>' + daSelectedUserId, {}, function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    if (res.data && res.data.matrix) {
                        daCurrentMatrix = res.data.matrix;
                        daRenderMatrix();
                    }
                    daLoadUsers();
                } else {
                    toastr.error(res.message);
                }
            }, 'json');
        });
    }

    // =========================================================
    // 3. TAB FASILITAS PER USER
    // =========================================================
    let facSelectedUserId = 0;
    let facTimer = null;
    let facUsersLoaded = false;

    function facLoadUsers(callback) {
        if (!canUserFacility) return;
        $('#facUserList').html('<div class="p-4 text-center text-muted">Memuat user...</div>');
        $.getJSON('<?= base_url('master/user-facility/users') ?>', { q: $('#facSearchUser').val() || '' }, function(res) {
            if (!res.status) {
                $('#facUserList').html('<div class="p-3 text-danger">' + esc(res.message) + '</div>');
                return;
            }
            facUsersLoaded = true;
            let html = '';
            (res.data || []).forEach(function(user) {
                const isActive = parseInt(user.id, 10) === facSelectedUserId;
                html += '<div class="fac-user-item ' + (isActive ? 'active' : '') + '" data-id="' + esc(user.id) + '">' +
                    '<div class="font-weight-bold text-dark">' + esc(user.nm_karyawan || user.username || '-') + '</div>' +
                    '<div class="small text-muted mt-1"><span class="font-weight-bold text-info">@' + esc(user.username || '-') + '</span> · ' + esc(user.departemen || '-') + ' · ' + esc(user.jobdesk || '-') + '</div>' +
                '</div>';
            });
            $('#facUserList').html(html || '<div class="p-4 text-center text-muted">User tidak ditemukan.</div>');
            if (typeof callback === 'function') callback();
        });
    }

    function facLoadMatrix(userId) {
        if (!canUserFacility) return;
        facSelectedUserId = parseInt(userId, 10) || 0;
        if (!facSelectedUserId) return;

        $('#facUserList .fac-user-item').removeClass('active');
        $('#facUserList .fac-user-item[data-id="' + facSelectedUserId + '"]').addClass('active');

        $('#facBody').html('<tr><td colspan="4" class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat fasilitas...</td></tr>');
        $.getJSON('<?= base_url('master/user-facility/matrix/') ?>' + facSelectedUserId, function(res) {
            if (!res.status) { toastr.error(res.message); return; }
            const user = res.data.user || {};
            $('#facSelectedUserTitle').html(esc(user.nm_karyawan || user.username || '-') + ' <small class="text-muted font-weight-normal">(@' + esc(user.username || '-') + ')</small>');
            $('#facSelectedUserMeta').text((user.departemen || '-') + ' · ' + (user.jobdesk || '-'));
            let html = '';
            let lastGroup = '';
            (res.data.facilities || []).forEach(function(row) {
                if (row.group !== lastGroup) {
                    lastGroup = row.group;
                    html += '<tr class="facility-group-title"><td colspan="4">' + esc(lastGroup) + '</td></tr>';
                }
                html += '<tr>' +
                    '<td><div class="font-weight-bold">' + esc(row.label) + '</div><div class="small text-muted">' + esc(row.key) + '</div></td>' +
                    '<td>' + esc(row.module) + '</td>' +
                    '<td class="text-center">' + (parseInt(row.has_override, 10) === 1 ? '<span class="badge badge-primary">Override</span>' : '<span class="badge badge-secondary">Default</span>') + '</td>' +
                    '<td class="text-center"><input type="checkbox" class="facility-toggle" data-key="' + esc(row.key) + '" ' + (parseInt(row.is_allowed, 10) === 1 ? 'checked' : '') + '></td>' +
                '</tr>';
            });
            $('#facBody').html(html || '<tr><td colspan="4" class="text-center text-muted py-4">Fasilitas belum tersedia.</td></tr>');
        });
    }

    if (canUserFacility) {
        $('#facSearchUser').on('input', function() {
            clearTimeout(facTimer);
            facTimer = setTimeout(facLoadUsers, 300);
        });
        $('#facUserList').on('click', '.fac-user-item', function() {
            facLoadMatrix($(this).data('id'));
        });
        $('#facBody').on('change', '.facility-toggle', function() {
            if (!facSelectedUserId) return;
            $.post('<?= base_url('master/user-facility/update') ?>', {
                user_id: facSelectedUserId,
                facility_key: $(this).data('key'),
                is_allowed: this.checked ? 1 : 0
            }, function(res) {
                res.status ? toastr.success(res.message) : toastr.error(res.message);
                facLoadMatrix(facSelectedUserId);
            }, 'json');
        });
    }

    // =========================================================
    // 4. NAVIGASI TAB, SHORTCUT DARI TABEL USER, & URL QUERY
    // =========================================================
    function activateTab(tabKey, targetUserId) {
        const $btn = $('#umMainTabs a[data-tab-key="' + tabKey + '"]');
        if ($btn.length === 0) return;

        $btn.tab('show');

        if (tabKey === 'akses-dashboard' && canDashboardAccess) {
            if (!daUsersLoaded) {
                daLoadUsers(function() {
                    if (targetUserId) daLoadMatrix(targetUserId);
                });
            } else if (targetUserId) {
                daLoadMatrix(targetUserId);
            }
        } else if (tabKey === 'fasilitas' && canUserFacility) {
            if (!facUsersLoaded) {
                facLoadUsers(function() {
                    if (targetUserId) facLoadMatrix(targetUserId);
                });
            } else if (targetUserId) {
                facLoadMatrix(targetUserId);
            }
        }
    }

    $('#umMainTabs a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        const tabKey = $(e.target).data('tab-key');
        if (tabKey === 'akses-dashboard' && canDashboardAccess && !daUsersLoaded) {
            daLoadUsers();
        } else if (tabKey === 'fasilitas' && canUserFacility && !facUsersLoaded) {
            facLoadUsers();
        }

        // Perbarui query string di URL tanpa reload halaman
        if (window.history && window.history.replaceState) {
            const url = new URL(window.location.href);
            if (tabKey === 'users') {
                url.searchParams.delete('tab');
            } else {
                url.searchParams.set('tab', tabKey);
            }
            window.history.replaceState({}, '', url.toString());
        }
    });

    // Tombol shortcut di tabel Data User
    $('#tableUsers').on('click', '.btn-jump-dashboard-access', function() {
        activateTab('akses-dashboard', $(this).data('id'));
    });
    $('#tableUsers').on('click', '.btn-jump-facility', function() {
        activateTab('fasilitas', $(this).data('id'));
    });

    // Buka tab otomatis jika ada parameter ?tab= di URL (mis. hasil redirect)
    const initParams = new URLSearchParams(window.location.search);
    const initTab = initParams.get('tab');
    const initUserId = parseInt(initParams.get('user_id') || '0', 10);
    if (initTab === 'akses-dashboard' || initTab === 'fasilitas') {
        activateTab(initTab, initUserId || 0);
    }
});
</script>
</body>
