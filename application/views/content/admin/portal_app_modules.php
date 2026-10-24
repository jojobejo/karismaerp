<style>
    .pm-page{color:#172033}.pm-hero{background:linear-gradient(125deg,#10283d,#145d69 68%,#1c8d83);border-radius:18px;color:#fff;padding:24px;box-shadow:0 16px 34px rgba(16,40,61,.16)}.pm-panel{background:#fff;border:1px solid #e5eaf2;border-radius:15px;box-shadow:0 8px 24px rgba(19,35,59,.05)}.pm-muted{color:#718096}.pm-code{font-family:Consolas,monospace}.pm-table th{white-space:nowrap}.pm-actions{white-space:nowrap}
</style>
<section class="content p-3 p-md-4 pm-page">
    <div class="pm-hero mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div><div class="small text-uppercase font-weight-bold mb-1" style="letter-spacing:.12em;opacity:.72">Katalog module klien</div><h2 class="font-weight-bold mb-1">Setting Module</h2><div><?= html_escape($app['name']) ?> <span class="pm-code ml-1">(<?= html_escape($app_code) ?>)</span></div></div>
        <div class="mt-3 mt-md-0"><a href="<?= base_url($back_url) ?>" class="btn btn-outline-light font-weight-bold"><i class="fas fa-arrow-left mr-1"></i> Kembali</a></div>
    </div>
    <?php if (!$database_ready): ?><div class="alert alert-warning">Database katalog module belum diterapkan. Jalankan <code>docs/database/portal_app_module_20261024.sql</code> sebelum menyimpan data.</div><?php endif ?>
    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="pm-panel p-3">
                <h5 class="font-weight-bold mb-1" id="pm-form-title">Tambah Module</h5>
                <p class="small pm-muted mb-3">Daftarkan fungsi yang tersedia pada aplikasi klien ini.</p>
                <form id="pm-form">
                    <input type="hidden" name="app_code" value="<?= html_escape($app_code) ?>">
                    <input type="hidden" name="form_token" id="pm-token" value="<?= html_escape($form_token) ?>">
                    <input type="hidden" name="is_active" value="0">
                    <div class="form-group"><label for="pm-code">Kode module</label><input class="form-control" id="pm-code" name="module_code" maxlength="80" pattern="[A-Za-z0-9][A-Za-z0-9._-]{0,79}" required><small class="form-text pm-muted">Unik pada aplikasi ini; gunakan huruf, angka, titik, garis bawah, atau tanda hubung.</small></div>
                    <div class="form-group"><label for="pm-name">Nama module</label><input class="form-control" id="pm-name" name="module_name" maxlength="150" required></div>
                    <div class="form-group"><label for="pm-description">Deskripsi</label><textarea class="form-control" id="pm-description" name="description" rows="3" maxlength="2000"></textarea></div>
                    <div class="form-group"><label for="pm-url">Route atau URL (opsional)</label><input class="form-control" id="pm-url" name="module_url" maxlength="500" placeholder="Contoh: /purchasing/po"><small class="form-text pm-muted">Gunakan path aplikasi atau URL HTTPS.</small></div>
                    <div class="form-row">
                        <div class="form-group col-6"><label for="pm-order">Urutan</label><input class="form-control" id="pm-order" name="sort_order" type="number" min="0" value="0"></div>
                        <div class="form-group col-6 d-flex align-items-end"><div class="custom-control custom-switch mb-2"><input class="custom-control-input" id="pm-active" type="checkbox" checked><label class="custom-control-label" for="pm-active">Module aktif</label></div></div>
                    </div>
                    <div class="d-flex"><button class="btn btn-primary mr-2" id="pm-submit" type="submit"><i class="fas fa-save mr-1"></i> Simpan Module</button><button class="btn btn-outline-secondary d-none" id="pm-cancel" type="button">Batal ubah</button></div>
                </form>
            </div>
        </div>
        <div class="col-lg-8 mb-3">
            <div class="pm-panel p-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3"><div><h5 class="font-weight-bold mb-1">Daftar Module</h5><div class="small pm-muted">Kelola katalog module khusus untuk <?= html_escape($app['name']) ?>.</div></div><span class="badge badge-light mt-2 mt-md-0" id="pm-count">0 module</span></div>
                <div class="table-responsive"><table class="table table-hover pm-table mb-0"><thead class="thead-light"><tr><th>Kode</th><th>Nama Module</th><th>Route / URL</th><th>Status</th><th>Aksi</th></tr></thead><tbody id="pm-rows"><tr><td colspan="5" class="text-center pm-muted py-4">Memuat data module…</td></tr></tbody></table></div>
            </div>
            <div class="alert alert-info mt-3 mb-0"><strong>Akses klien:</strong> klien dapat mengelola katalognya melalui API menggunakan Basic Authentication dengan <code>app_code</code> dan secret klien. Akses hanya berlaku untuk aplikasi milik kredensial tersebut. Lihat <code>docs/development/portal-app-module-catalog-20261024.md</code>.</div>
        </div>
    </div>
</section>
<script>
$(function(){
    const appCode='<?= html_escape($app_code) ?>';
    const listUrl='<?= base_url('admin/portal-app-modules/list') ?>?app_code='+encodeURIComponent(appCode);
    const saveUrl='<?= base_url('admin/portal-app-modules/save') ?>';
    let rows=[];let editId=null;
    const esc=value=>String(value??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    function resetForm(){editId=null;$('#pm-form')[0].reset();$('#pm-form [name="app_code"]').val(appCode);$('#pm-form [name="sort_order"]').val(0);$('#pm-active').prop('checked',true);$('#pm-form-title').text('Tambah Module');$('#pm-submit').html('<i class="fas fa-save mr-1"></i> Simpan Module');$('#pm-cancel').addClass('d-none');}
    function render(){
        $('#pm-count').text(rows.length+' module');
        if(!rows.length){$('#pm-rows').html('<tr><td colspan="5" class="text-center pm-muted py-4">Belum ada module untuk aplikasi ini.</td></tr>');return;}
        $('#pm-rows').html(rows.map(row=>'<tr><td><code class="pm-code">'+esc(row.module_code)+'</code></td><td><strong>'+esc(row.module_name)+'</strong><div class="small pm-muted">'+esc(row.description||'')+'</div></td><td class="small">'+(row.module_url?'<code>'+esc(row.module_url)+'</code>':'<span class="pm-muted">-</span>')+'</td><td>'+(parseInt(row.is_active)===1?'<span class="badge badge-success">Aktif</span>':'<span class="badge badge-secondary">Nonaktif</span>')+'</td><td class="pm-actions"><button class="btn btn-sm btn-outline-primary pm-edit" data-id="'+parseInt(row.id,10)+'" title="Ubah"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger pm-delete" data-id="'+parseInt(row.id,10)+'" title="Hapus"><i class="fas fa-trash"></i></button></td></tr>').join(''));
    }
    function load(){
        $.getJSON(listUrl).done(res=>{if(!res.status){$('#pm-rows').html('<tr><td colspan="5" class="text-center text-danger py-4">'+esc(res.message)+'</td></tr>');return;}rows=res.data.modules||[];render();})
        .fail(()=>$('#pm-rows').html('<tr><td colspan="5" class="text-center text-danger py-4">Daftar module gagal dimuat. Pastikan migrasi database tersedia.</td></tr>'));
    }
    $('#pm-active').on('change',function(){$('#pm-form [name="is_active"]').val(this.checked?'1':'0');});
    $('#pm-form').on('submit',function(event){
        event.preventDefault();$('#pm-form [name="is_active"]').val($('#pm-active').is(':checked')?'1':'0');
        const url=editId?'<?= base_url('admin/portal-app-modules/update/') ?>'+editId:saveUrl;
        $('#pm-submit').prop('disabled',true);
        $.ajax({url,type:'POST',data:$(this).serialize(),dataType:'json'}).done(res=>{
            if(res.data&&res.data.form_token)$('#pm-token').val(res.data.form_token);
            if(!res.status){toastr.error(res.message);return;}
            toastr.success(res.message);resetForm();load();
        }).fail(xhr=>{toastr.error(xhr.status===403?'Sesi formulir kedaluwarsa. Muat ulang halaman.':'Module gagal disimpan.');})
        .always(()=>$('#pm-submit').prop('disabled',false));
    });
    $('#pm-cancel').on('click',resetForm);
    $('#pm-rows').on('click','.pm-edit',function(){
        const row=rows.find(item=>parseInt(item.id,10)===parseInt($(this).data('id'),10));if(!row)return;
        editId=row.id;$('#pm-code').val(row.module_code);$('#pm-name').val(row.module_name);$('#pm-description').val(row.description||'');$('#pm-url').val(row.module_url||'');$('#pm-order').val(row.sort_order);$('#pm-active').prop('checked',parseInt(row.is_active)===1).trigger('change');$('#pm-form-title').text('Ubah Module');$('#pm-submit').html('<i class="fas fa-save mr-1"></i> Simpan Perubahan');$('#pm-cancel').removeClass('d-none');window.scrollTo({top:0,behavior:'smooth'});
    });
    $('#pm-rows').on('click','.pm-delete',function(){
        const id=$(this).data('id');const row=rows.find(item=>parseInt(item.id,10)===parseInt(id,10));
        Swal.fire({title:'Hapus module?',text:row?row.module_name:'Data module akan dihapus.',icon:'warning',showCancelButton:true,confirmButtonText:'Hapus',cancelButtonText:'Batal'}).then(result=>{
            if(!result.isConfirmed)return;
            $.ajax({url:'<?= base_url('admin/portal-app-modules/delete/') ?>'+id,type:'POST',data:{form_token:$('#pm-token').val()},dataType:'json'}).done(res=>{if(res.data&&res.data.form_token)$('#pm-token').val(res.data.form_token);res.status?toastr.success(res.message):toastr.error(res.message);if(res.status)load();}).fail(()=>toastr.error('Module gagal dihapus.'));
        });
    });
    load();
});
</script>
