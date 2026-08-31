<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>
    <div class="content-wrapper">
        <section class="content p-3 p-md-4">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
            <style>
                .facility-hero{background:linear-gradient(135deg,#0f766e,#1d4ed8);border-radius:14px;color:#fff;padding:20px 22px;box-shadow:0 14px 30px rgba(15,118,110,.16)}
                .facility-panel{background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 10px 24px rgba(15,23,42,.07)}
                .facility-user{cursor:pointer;border-bottom:1px solid #eef2f7;padding:11px 12px}
                .facility-user:hover,.facility-user.active{background:#eef7ff}
                .facility-toggle{width:1.15rem;height:1.15rem}
                .facility-group-title{background:#f8fafc;color:#334155;font-weight:700}
            </style>

            <div class="facility-hero mb-3 d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
                <div>
                    <h3 class="mb-1 fw-bold">Fasilitas Per User</h3>
                    <div class="opacity-75">Kelola akses menu, tombol aksi, data sensitif, dan scope operasional per user.</div>
                </div>
                <a href="<?= base_url('dashboard') ?>" class="btn btn-light fw-semibold"><i class="fas fa-home me-1"></i> Dashboard</a>
            </div>

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="facility-panel">
                        <div class="p-3 border-bottom">
                            <label class="form-label fw-semibold">Cari User</label>
                            <input type="text" class="form-control" id="searchUser" placeholder="Nama / username / jobdesk">
                        </div>
                        <div id="userList" style="max-height:68vh;overflow:auto;">
                            <div class="p-3 text-muted">Memuat user...</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="facility-panel">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <div class="fw-bold" id="selectedUserTitle">Pilih user</div>
                                <div class="small text-muted" id="selectedUserMeta">Matrix fasilitas akan tampil setelah user dipilih.</div>
                            </div>
                        </div>
                        <div class="p-3 table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Fasilitas</th>
                                        <th>Module</th>
                                        <th class="text-center">Default / Override</th>
                                        <th class="text-center">Diizinkan</th>
                                    </tr>
                                </thead>
                                <tbody id="facilityBody">
                                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada user dipilih.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function(){
    let selectedUserId = 0;
    let timer = null;

    function esc(value){ return $('<div>').text(value == null ? '' : value).html(); }

    function loadUsers(){
        $('#userList').html('<div class="p-3 text-muted">Memuat user...</div>');
        $.getJSON('<?= base_url('master/user-facility/users') ?>', {q: $('#searchUser').val() || ''}, function(res){
            if(!res.status){ $('#userList').html('<div class="p-3 text-danger">'+esc(res.message)+'</div>'); return; }
            let html = '';
            (res.data || []).forEach(function(user){
                html += '<div class="facility-user" data-id="'+esc(user.id)+'">' +
                    '<div class="fw-semibold">'+esc(user.nm_karyawan || user.username || '-')+'</div>' +
                    '<div class="small text-muted">'+esc(user.username || '-')+' | '+esc(user.departemen || '-')+' | '+esc(user.jobdesk || '-')+'</div>' +
                    '</div>';
            });
            $('#userList').html(html || '<div class="p-3 text-muted">User tidak ditemukan.</div>');
        });
    }

    function loadMatrix(userId){
        selectedUserId = parseInt(userId, 10) || 0;
        $('#facilityBody').html('<tr><td colspan="4" class="text-center text-muted py-4">Memuat fasilitas...</td></tr>');
        $.getJSON('<?= base_url('master/user-facility/matrix/') ?>' + selectedUserId, function(res){
            if(!res.status){ toastr.error(res.message); return; }
            const user = res.data.user || {};
            $('#selectedUserTitle').text(user.nm_karyawan || user.username || '-');
            $('#selectedUserMeta').text((user.username || '-') + ' | ' + (user.departemen || '-') + ' | ' + (user.jobdesk || '-'));
            let html = '';
            let lastGroup = '';
            (res.data.facilities || []).forEach(function(row){
                if(row.group !== lastGroup){
                    lastGroup = row.group;
                    html += '<tr class="facility-group-title"><td colspan="4">'+esc(lastGroup)+'</td></tr>';
                }
                html += '<tr>' +
                    '<td><div class="fw-semibold">'+esc(row.label)+'</div><div class="small text-muted">'+esc(row.key)+'</div></td>' +
                    '<td>'+esc(row.module)+'</td>' +
                    '<td class="text-center">'+(parseInt(row.has_override,10)===1 ? '<span class="badge bg-primary">Override</span>' : '<span class="badge bg-secondary">Default</span>')+'</td>' +
                    '<td class="text-center"><input type="checkbox" class="form-check-input facility-toggle" data-key="'+esc(row.key)+'" '+(parseInt(row.is_allowed,10)===1?'checked':'')+'></td>' +
                    '</tr>';
            });
            $('#facilityBody').html(html || '<tr><td colspan="4" class="text-center text-muted py-4">Fasilitas belum tersedia.</td></tr>');
        });
    }

    $('#searchUser').on('input', function(){
        clearTimeout(timer);
        timer = setTimeout(loadUsers, 300);
    });
    $('#userList').on('click', '.facility-user', function(){
        $('.facility-user').removeClass('active');
        $(this).addClass('active');
        loadMatrix($(this).data('id'));
    });
    $('#facilityBody').on('change', '.facility-toggle', function(){
        if(!selectedUserId){ return; }
        $.post('<?= base_url('master/user-facility/update') ?>', {
            user_id: selectedUserId,
            facility_key: $(this).data('key'),
            is_allowed: this.checked ? 1 : 0
        }, function(res){
            res.status ? toastr.success(res.message) : toastr.error(res.message);
            loadMatrix(selectedUserId);
        }, 'json');
    });

    loadUsers();
});
</script>
</body>
