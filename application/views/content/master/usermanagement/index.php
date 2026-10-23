<body class="hold-transition no-sidebar">
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content p-3 p-md-4">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
            <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
            <style>
                .no-sidebar .content-wrapper{margin-left:0!important}.erp-page{color:#172033}.erp-hero{background:linear-gradient(135deg,#0f766e,#2563eb);border-radius:18px;color:#fff;padding:22px 24px;box-shadow:0 18px 40px rgba(37,99,235,.22)}
                .erp-panel{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 12px 30px rgba(15,23,42,.08)}.erp-panel-header{padding:16px 18px;border-bottom:1px solid #edf2f7}.icon-chip{width:40px;height:40px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:#eef2ff;color:#1d4ed8}
                .btn-erp{background:linear-gradient(135deg,#2563eb,#0f766e);border:0;color:#fff}.btn-erp:hover{color:#fff;filter:brightness(.95)}
                .form-control,.form-select,.select2-container--default .select2-selection--single{border-radius:10px;border-color:#d7dee8}.select2-container .select2-selection--single{height:38px}.select2-selection__rendered{line-height:36px!important}.select2-selection__arrow{height:36px!important}
                table.dataTable thead th{position:sticky;top:0;background:#f8fafc!important;z-index:2}.badge-soft-success{background:#dcfce7;color:#166534}.badge-soft-danger{background:#fee2e2;color:#991b1b}
                .avatar{width:38px;height:38px;border-radius:50%;object-fit:cover;background:#e2e8f0}.action-group{display:flex;gap:6px;flex-wrap:wrap}.skeleton{min-height:72px;border-radius:12px;background:linear-gradient(90deg,#f1f5f9,#e2e8f0,#f1f5f9);background-size:200% 100%;animation:shimmer 1.1s infinite}@keyframes shimmer{to{background-position:-200% 0}}
                @media(max-width:768px){.erp-hero{padding:18px}.filter-grid{grid-template-columns:1fr!important}.action-group .btn{flex:1}}
            </style>

            <div class="erp-page">
                <div class="erp-hero mb-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center">
                        <div><h3 class="mb-1 fw-bold">User Management</h3><div class="opacity-75">Hybrid legacy + normalized role permission untuk KARISMA ERP.</div></div>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-outline-light fw-semibold" href="<?= base_url('portal') ?>"><i class="fas fa-arrow-left me-1"></i> Kembali ke Portal</a>
                            <a class="btn btn-outline-light fw-semibold" href="<?= base_url('master/organisasi') ?>"><i class="fas fa-sitemap me-1"></i> Master Organisasi</a>
                            <button class="btn btn-light fw-semibold" id="btnAdd"><i class="fas fa-user-plus me-1"></i> Tambah User</button>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 mb-2"><div class="erp-panel p-3 d-flex justify-content-between align-items-center"><div><small class="text-muted text-uppercase font-weight-bold">Total akun</small><div class="h3 font-weight-bold mb-0" id="userTotal">—</div></div><span class="icon-chip"><i class="fas fa-users"></i></span></div></div>
                    <div class="col-md-4 mb-2"><div class="erp-panel p-3 d-flex justify-content-between align-items-center"><div><small class="text-muted text-uppercase font-weight-bold">Hasil filter</small><div class="h3 font-weight-bold mb-0" id="userFiltered">—</div></div><span class="icon-chip" style="color:#0f766e;background:#e6f5f3"><i class="fas fa-filter"></i></span></div></div>
                    <div class="col-md-4 mb-2"><div class="erp-panel p-3 d-flex justify-content-between align-items-center"><div><small class="text-muted text-uppercase font-weight-bold">Aktif pada halaman</small><div class="h3 font-weight-bold mb-0 text-success" id="userActivePage">—</div></div><span class="icon-chip" style="color:#16834b;background:#e8f7ee"><i class="fas fa-user-check"></i></span></div></div>
                </div>

                <div class="erp-panel mb-3">
                    <div class="erp-panel-header">
                        <div class="d-grid filter-grid gap-2" style="grid-template-columns:repeat(5,minmax(0,1fr));">
                            <select id="filter_departemen" class="form-select"><option value="">Departemen</option><?php foreach($departemen as $d): ?><option value="<?= html_escape($d['id']) ?>"><?= html_escape($d['text']) ?></option><?php endforeach ?></select>
                            <select id="filter_jobdesk" class="form-select"><option value="">Jobdesk</option><?php foreach($jobdesk as $j): ?><option value="<?= html_escape($j['nama_jobdesk']) ?>"><?= html_escape($j['nama_jobdesk']) ?></option><?php endforeach ?></select>
                            <select id="filter_akses" class="form-select"><option value="">Akses</option><?php foreach($akses_level as $a): ?><option value="<?= html_escape($a['nama_akses_level']) ?>"><?= html_escape($a['nama_akses_level']) ?></option><?php endforeach ?></select>
                            <select id="filter_status" class="form-select"><option value="">Status</option><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
                            <button class="btn btn-outline-secondary" id="btnResetFilter"><i class="fas fa-undo me-1"></i> Reset</button>
                        </div>
                    </div>
                    <div class="p-3">
                        <div id="tableSkeleton" class="skeleton mb-2"></div>
                        <table class="table table-hover align-middle w-100 compact" id="tableUsers">
                            <thead><tr><th>User</th><th>NIK</th><th>Departemen</th><th>Jobdesk</th><th>Jabatan</th><th>Akses</th><th>Status</th><th>Aksi</th></tr></thead>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="modalUserCreate" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="formUserCreate" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php $this->load->view('content/master/usermanagement/user_fields', ['idPrefix' => 'create', 'requirePassword' => true]); ?>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-erp" type="submit"><i class="fas fa-save me-1"></i> Simpan User</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalUserEdit" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="formUserEdit" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Ubah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php $this->load->view('content/master/usermanagement/user_fields', ['idPrefix' => 'edit', 'requirePassword' => false]); ?>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-erp" type="submit"><i class="fas fa-save me-1"></i> Simpan Perubahan</button></div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function(){
    const escapeHtml=value=>String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const createModal = new bootstrap.Modal(document.getElementById('modalUserCreate'));
    const editModal = new bootstrap.Modal(document.getElementById('modalUserEdit'));
    $('#modalUserCreate .select2, #modalUserEdit .select2').each(function(){ $(this).select2({width:'100%', dropdownParent: $(this).closest('.modal')}); });
    function syncLegacyFields($form){
        const jobdesk = $form.find('[name="jobdesk_id"] option:selected');
        const akses = $form.find('[name="akses_lv_id"] option:selected');
        if(jobdesk.val()) $form.find('[name="jobdesk"]').val(jobdesk.data('text') || jobdesk.text());
        if(akses.val()) $form.find('[name="akses_lv"]').val(akses.data('text') || akses.text());
    }
    function setSelectValue($select,value,label){
        if(value===null || value===undefined || value==='') return;
        const optionExists=$select.find('option').filter(function(){return String(this.value)===String(value);}).length>0;
        if(!optionExists) $select.append(new Option(label || value,value,true,true));
        $select.val(String(value)).trigger('change');
    }
    const table = $('#tableUsers').DataTable({
        processing:true, serverSide:true, responsive:true, searchDelay:350,
        ajax:{url:'<?= base_url('master/user-management/list') ?>', type:'POST', data:function(d){d.departemen=$('#filter_departemen').val();d.jobdesk=$('#filter_jobdesk').val();d.akses_lv=$('#filter_akses').val();d.status=$('#filter_status').val();},dataSrc:function(json){$('#userTotal').text(json.recordsTotal??0);$('#userFiltered').text(json.recordsFiltered??0);$('#userActivePage').text((json.data||[]).filter(user=>parseInt(user.status)===1).length);return json.data||[];}},
        columns:[
            {data:null, render:r=>`<div class="d-flex align-items-center gap-2"><img class="avatar" src="${r.foto ? '<?= base_url() ?>'+encodeURI(r.foto) : '<?= base_url('assets/images/Karisma.png') ?>'}"><div><div class="fw-semibold">${escapeHtml(r.nm_karyawan||'-')}</div><small class="text-muted">${escapeHtml(r.username||'-')}</small></div></div>`},
            {data:'nik',render:v=>escapeHtml(v||'-')}, {data:'departemen',render:v=>escapeHtml(v||'-')}, {data:'jobdesk',render:v=>escapeHtml(v||'-')}, {data:'jabatan',defaultContent:'-',render:v=>escapeHtml(v||'-')}, {data:'akses_lv',render:v=>escapeHtml(v||'-')},
            {data:null, render:r=> r.status === undefined ? '<span class="badge bg-secondary">Legacy</span>' : (parseInt(r.status)===1 ? '<span class="badge badge-soft-success">Aktif</span>' : '<span class="badge badge-soft-danger">Nonaktif</span>')},
            {data:null, orderable:false, searchable:false, render:r=>`<div class="action-group"><button class="btn btn-sm btn-outline-primary btn-edit" data-id="${r.id}"><i class="fas fa-edit"></i></button><button class="btn btn-sm btn-outline-warning btn-reset" data-id="${r.id}"><i class="fas fa-key"></i></button><button class="btn btn-sm btn-outline-secondary btn-toggle" data-id="${r.id}"><i class="fas fa-power-off"></i></button><button class="btn btn-sm btn-outline-danger btn-delete" data-id="${r.id}"><i class="fas fa-trash"></i></button></div>`}
        ],
        initComplete:function(){ $('#tableSkeleton').hide(); }
    });
    $('#filter_departemen,#filter_jobdesk,#filter_akses,#filter_status').on('change',()=>table.ajax.reload());
    $('#btnResetFilter').on('click',function(){ $('.filter-grid select').val(''); table.ajax.reload(); });
    $('#btnAdd').on('click',function(){ const $form=$('#formUserCreate'); $form[0].reset(); $form.find('.select2').val('').trigger('change'); createModal.show(); });
    $('#tableUsers').on('click','.btn-edit',function(){
        const id=$(this).data('id');
        $.getJSON('<?= base_url('master/user-management/detail/') ?>'+id,function(res){
            if(!res.status){toastr.error(res.message);return;}
            const $form=$('#formUserEdit');
            $form[0].reset();
            $form.find('.select2').val('').trigger('change');
            $form.data('user-id',res.data.id);
            $.each(res.data,function(k,v){ $form.find('[name="'+k+'"]').val(v); });
            setSelectValue($form.find('[name="departemen"]'),res.data.departemen,res.data.departemen);
            setSelectValue($form.find('[name="jobdesk_id"]'),res.data.jobdesk_id,res.data.jobdesk);
            setSelectValue($form.find('[name="akses_lv_id"]'),res.data.akses_lv_id,res.data.akses_lv);
            setSelectValue($form.find('[name="jabatan"]'),res.data.jabatan,res.data.jabatan);
            editModal.show();
        }).fail(function(){toastr.error('Data user gagal dimuat.');});
    });
    function submitUserForm($form, url, formModal){
        syncLegacyFields($form);
        $.ajax({url:url,type:'POST',data:new FormData($form[0]),processData:false,contentType:false,dataType:'json',success:function(res){
            res.status?toastr.success(res.message):toastr.error(res.message);
            if(res.status){formModal.hide();table.ajax.reload(null,false);}
        },error:function(){toastr.error('Server tidak merespons saat menyimpan user.');}});
    }
    $('#formUserCreate').on('submit',function(e){e.preventDefault();submitUserForm($(this),'<?= base_url('master/user-management/save') ?>',createModal);});
    $('#formUserEdit').on('submit',function(e){e.preventDefault();const id=$(this).data('user-id');if(!id){toastr.error('ID user tidak valid.');return;}submitUserForm($(this),'<?= base_url('master/user-management/update/') ?>'+id,editModal);});
    $('#tableUsers').on('click','.btn-delete',function(){ const id=$(this).data('id'); Swal.fire({title:'Hapus user?',icon:'warning',showCancelButton:true,confirmButtonText:'Hapus'}).then(r=>{ if(r.isConfirmed) $.post('<?= base_url('master/user-management/delete/') ?>'+id,function(res){res.status?toastr.success(res.message):toastr.error(res.message); table.ajax.reload(null,false);},'json'); }); });
    $('#tableUsers').on('click','.btn-toggle',function(){ $.post('<?= base_url('master/user-management/toggle-status/') ?>'+$(this).data('id'),function(res){res.status?toastr.success(res.message):toastr.warning(res.message); table.ajax.reload(null,false);},'json'); });
    $('#tableUsers').on('click','.btn-reset',function(){ const id=$(this).data('id'); Swal.fire({title:'Reset Password',input:'password',inputPlaceholder:'Password baru minimal 6 karakter',showCancelButton:true,confirmButtonText:'Reset'}).then(r=>{ if(r.isConfirmed) $.post('<?= base_url('master/user-management/reset-password/') ?>'+id,{password:r.value},function(res){res.status?toastr.success(res.message):toastr.error(res.message);},'json'); }); });
});
</script>
</body>
