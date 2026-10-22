<body class="hold-transition sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php $this->load->view('partial/main/navbar') ?>
    <?php $this->load->view('partial/main/sidebar') ?>
    <div class="content-wrapper"><section class="content p-3 p-md-4">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
        <style>.erp-hero{background:linear-gradient(135deg,#0f766e,#2563eb);border-radius:18px;color:#fff;padding:22px;box-shadow:0 18px 40px rgba(37,99,235,.2)}.erp-panel{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 12px 30px rgba(15,23,42,.08)}.btn-erp{background:linear-gradient(135deg,#2563eb,#0f766e);border:0;color:#fff}table.dataTable thead th{background:#f8fafc!important}</style>
        <div class="erp-hero mb-3 d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center"><div><h3 class="mb-1 fw-bold">Master Organisasi</h3><div class="opacity-75">Kelola departemen, jobdesk, dan jabatan pada satu halaman.</div></div><a href="<?= base_url('master/user-management') ?>" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Kembali ke User Management</a></div>
        <div class="erp-panel p-3">
            <ul class="nav nav-pills gap-2 mb-3" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-entity="departemen" data-bs-toggle="pill" data-bs-target="#tab-departemen" type="button">Departemen</button></li>
                <li class="nav-item"><button class="nav-link" data-entity="jobdesk" data-bs-toggle="pill" data-bs-target="#tab-jobdesk" type="button">Jobdesk</button></li>
                <li class="nav-item"><button class="nav-link" data-entity="jabatan" data-bs-toggle="pill" data-bs-target="#tab-jabatan" type="button">Jabatan</button></li>
            </ul>
            <div class="tab-content">
                <?php foreach (['departemen' => 'Departemen', 'jobdesk' => 'Jobdesk', 'jabatan' => 'Jabatan'] as $entity => $label): ?>
                <div class="tab-pane fade <?= $entity === 'departemen' ? 'show active' : '' ?>" id="tab-<?= $entity ?>">
                    <div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0">Daftar <?= $label ?></h5><button class="btn btn-erp btn-add" data-entity="<?= $entity ?>"><i class="fas fa-plus me-1"></i> Tambah <?= $label ?></button></div>
                    <div class="table-responsive"><table class="table table-hover align-middle w-100 entity-table" data-entity="<?= $entity ?>"><thead><tr><th>Nama <?= $label ?></th><?php if ($entity !== 'departemen'): ?><th>Deskripsi</th><th>Status</th><?php endif ?><th>Aksi</th></tr></thead></table></div>
                </div>
                <?php endforeach ?>
            </div>
        </div>
    </section></div>
</div>
<div class="modal fade" id="modalEntity" tabindex="-1"><div class="modal-dialog"><form class="modal-content" id="formEntity"><div class="modal-header"><h5 class="modal-title">Form Master</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><input type="hidden" id="entity"><input type="hidden" id="entityId"><div class="mb-3"><label class="form-label" id="nameLabel">Nama</label><input class="form-control" id="entityName" required maxlength="100"></div><div class="mb-3" id="descriptionGroup"><label class="form-label">Deskripsi</label><textarea class="form-control" id="entityDescription" rows="3"></textarea></div><div id="statusGroup"><label class="form-label">Status</label><select class="form-select" id="entityStatus"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-erp" type="submit">Simpan</button></div></form></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script><script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function(){
    const labels={departemen:'Departemen',jobdesk:'Jobdesk',jabatan:'Jabatan'};
    const tables={};
    const modal=new bootstrap.Modal(document.getElementById('modalEntity'));
    function escapeHtml(value){return $('<div>').text(value == null ? '' : value).html();}
    function initTable(entity){
        if(tables[entity]) return;
        const columns=[{data:'nama_'+entity,render:v=>escapeHtml(v)}];
        if(entity!=='departemen'){
            columns.push({data:'deskripsi',render:v=>escapeHtml(v)});
            columns.push({data:'status',render:v=>parseInt(v,10)===1?'<span class="badge bg-success">Aktif</span>':'<span class="badge bg-secondary">Nonaktif</span>'});
        }
        columns.push({data:null,orderable:false,searchable:false,render:r=>'<div class="d-flex gap-1"><button class="btn btn-sm btn-outline-primary btn-edit" data-id="'+encodeURIComponent(r.id)+'" aria-label="Ubah"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger btn-delete" data-id="'+encodeURIComponent(r.id)+'" aria-label="Hapus"><i class="fas fa-trash"></i></button></div>'});
        tables[entity]=$('.entity-table[data-entity="'+entity+'"]').DataTable({processing:true,serverSide:true,responsive:true,ajax:{url:'<?= base_url('master/organisasi/list/') ?>'+entity,type:'POST'},columns:columns,language:{emptyTable:'Belum ada data',processing:'Memuat data...'}});
    }
    initTable('departemen');
    $('button[data-entity][data-bs-toggle="pill"]').on('shown.bs.tab',function(){initTable($(this).data('entity'));});
    $('.btn-add').on('click',function(){const entity=$(this).data('entity');$('#formEntity')[0].reset();$('#entity').val(entity);$('#entityId').val('');$('#nameLabel').text('Nama '+labels[entity]);$('#descriptionGroup,#statusGroup').toggle(entity!=='departemen');$('.modal-title').text('Tambah '+labels[entity]);modal.show();});
    $('.entity-table').on('click','.btn-edit',function(){const entity=$(this).closest('.entity-table').data('entity'),id=$(this).data('id');$.getJSON('<?= base_url('master/organisasi/detail/') ?>'+entity+'/'+id,function(res){if(!res.status){toastr.error(res.message);return;}$('#entity').val(entity);$('#entityId').val(res.data.id);$('#entityName').val(res.data['nama_'+entity]);$('#entityDescription').val(res.data.deskripsi||'');$('#entityStatus').val(res.data.status ?? 1);$('#nameLabel').text('Nama '+labels[entity]);$('#descriptionGroup,#statusGroup').toggle(entity!=='departemen');$('.modal-title').text('Ubah '+labels[entity]);modal.show();});});
    $('#formEntity').on('submit',function(e){e.preventDefault();const entity=$('#entity').val(),id=$('#entityId').val(),payload={};payload['nama_'+entity]=$('#entityName').val();payload.deskripsi=$('#entityDescription').val();payload.status=$('#entityStatus').val();$.ajax({url:'<?= base_url('master/organisasi/save/') ?>'+entity+(id?'/'+id:''),type:'POST',data:payload,dataType:'json',success:function(res){if(!res.status){toastr.error(res.message);return;}toastr.success(res.message);modal.hide();tables[entity].ajax.reload(null,false);},error:function(){toastr.error('Server tidak merespons.');}});});
    $('.entity-table').on('click','.btn-delete',function(){const entity=$(this).closest('.entity-table').data('entity'),id=$(this).data('id');Swal.fire({title:'Hapus '+labels[entity].toLowerCase()+'?',icon:'warning',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal'}).then(r=>{if(r.isConfirmed)$.post('<?= base_url('master/organisasi/delete/') ?>'+entity+'/'+id,res=>{res.status?toastr.success(res.message):toastr.error(res.message);tables[entity].ajax.reload(null,false);},'json');});});
});
</script>
</body>
