<style>
.pa-page{--pa-ink:#14233b;--pa-muted:#6b7890;--pa-line:#e5eaf2;--pa-primary:#176b78;color:var(--pa-ink)}
.pa-hero{position:relative;overflow:hidden;border-radius:20px;padding:26px;background:linear-gradient(120deg,#10283d,#145d69 65%,#1c8d83);color:#fff;box-shadow:0 16px 34px rgba(16,40,61,.18)}
.pa-hero:after{content:"";position:absolute;width:230px;height:230px;right:-55px;top:-95px;border:1px solid rgba(255,255,255,.16);border-radius:50%;box-shadow:0 0 0 28px rgba(255,255,255,.04),0 0 0 58px rgba(255,255,255,.03)}
.pa-widget,.pa-panel,.pa-app{background:#fff;border:1px solid var(--pa-line);border-radius:16px;box-shadow:0 8px 22px rgba(19,35,59,.045)}
.pa-widget{padding:16px 18px;height:100%}.pa-widget-label{font-size:12px;color:var(--pa-muted);text-transform:uppercase;letter-spacing:.07em}.pa-widget-value{font-size:25px;font-weight:750;margin-top:2px}.pa-widget-icon{width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#e8f4f4;color:var(--pa-primary);font-size:17px}
.pa-panel{padding:18px}.pa-app{padding:16px;height:100%;transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease}.pa-app:hover{transform:translateY(-2px);border-color:#b9d6da;box-shadow:0 12px 24px rgba(19,35,59,.08)}.pa-app-icon{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;background:#edf5f6;color:var(--pa-accent);font-size:18px}.pa-app-url{font-size:11px;color:var(--pa-muted);overflow-wrap:anywhere}.pa-switch{transform:scale(1.12)}.pa-note{color:var(--pa-muted);font-size:13px}.pa-toolbar{border-bottom:1px solid var(--pa-line);padding-bottom:16px;margin-bottom:16px}
@media(max-width:767.98px){.pa-hero{padding:20px}.pa-panel{padding:14px}}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<section class="content p-3 p-md-4 pa-page">
    <div class="pa-hero mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div><div class="small text-uppercase font-weight-bold mb-1" style="letter-spacing:.12em;opacity:.72">Portal access control</div><h2 class="font-weight-bold mb-1">Akses Aplikasi</h2><div style="opacity:.78">Atur aplikasi yang dapat dibuka setiap pengguna dari portal.</div></div>
        <div class="mt-3 mt-md-0 d-flex flex-wrap">
            <a class="btn btn-outline-primary mr-2 mb-1" href="<?= base_url('admin/portal-sso-check') ?>">Cek API Klien SSO</a><a href="<?= base_url('admin/api-documentation') ?>" class="btn btn-outline-light font-weight-bold mr-2 mb-1"><i class="fas fa-book-open mr-1"></i> Dokumentasi API</a>
            <a href="<?= base_url('master/user-management') ?>" class="btn btn-outline-light font-weight-bold mr-2 mb-1"><i class="fas fa-arrow-left mr-1"></i> Kembali ke User Management</a>
            <a href="<?= base_url('admin/portal-sso-clients') ?>" class="btn btn-light font-weight-bold mb-1"><i class="fas fa-link mr-1"></i> Integrasi SSO</a>
        </div>
    </div>
    <?php if ($save_success): ?><div class="alert alert-success"><?= html_escape($save_success) ?></div><?php endif ?>
    <?php if ($save_error): ?><div class="alert alert-danger"><?= html_escape($save_error) ?></div><?php endif ?>
    <?php if (!$this->db->table_exists('tb_portal_app_access')): ?><div class="alert alert-warning">Tabel akses belum tersedia. Terapkan <code>docs/database/portal_app_access_20261021.sql</code>.</div><?php endif ?>
    <div class="row mb-3">
        <div class="col-md-4 mb-2"><div class="pa-widget d-flex justify-content-between align-items-center"><div><div class="pa-widget-label">Aplikasi tersedia</div><div class="pa-widget-value" id="pa-total">0</div></div><div class="pa-widget-icon"><i class="fas fa-th-large"></i></div></div></div>
        <div class="col-md-4 mb-2"><div class="pa-widget d-flex justify-content-between align-items-center"><div><div class="pa-widget-label">Akses diberikan</div><div class="pa-widget-value text-success" id="pa-allowed">0</div></div><div class="pa-widget-icon" style="background:#e9f7ef;color:#21864a"><i class="fas fa-check"></i></div></div></div>
        <div class="col-md-4 mb-2"><div class="pa-widget d-flex justify-content-between align-items-center"><div><div class="pa-widget-label">Akses dibatasi</div><div class="pa-widget-value text-danger" id="pa-denied">0</div></div><div class="pa-widget-icon" style="background:#fff0ef;color:#c64c43"><i class="fas fa-lock"></i></div></div></div>
    </div>
    <div class="pa-panel">
        <?php if (empty($users)): ?>
            <div class="text-center py-5"><div class="pa-widget-icon mx-auto mb-3"><i class="fas fa-user-slash"></i></div><h5>Belum ada akun</h5><div class="pa-note">Akun portal yang dapat dikelola belum ditemukan.</div></div>
        <?php else: ?>
            <div class="pa-toolbar row align-items-end">
                <div class="col-md-8 mb-2 d-flex flex-wrap justify-content-md-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-2 mb-1" id="pa-select-all"><i class="fas fa-check-double mr-1"></i> Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-1" id="pa-select-none"><i class="fas fa-ban mr-1"></i> Tidak ada</button>
                </div>
                <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center"><div><strong class="d-block" id="pa-user-name"><?= html_escape($selected_user['display_name'] ?? '') ?></strong><div class="mt-1"><span class="mr-3"><strong>Departemen:</strong> <span id="pa-user-department"><?= html_escape($selected_user['departemen'] ?? '-') ?></span></span><span><strong>Jobdesk:</strong> <span id="pa-user-jobdesk"><?= html_escape($selected_user['jobdesk'] ?? '-') ?></span></span></div></div><span class="badge badge-light mt-2 mt-md-0" id="pa-config-state">Memuat pengaturan…</span></div>
            </div>
            <form id="pa-form">
                <input type="hidden" name="auth_source" id="pa-source" value="<?= html_escape($selected_user['auth_source'] ?? '') ?>">
                <input type="hidden" name="user_id" id="pa-user-id" value="<?= (int)($selected_user['id'] ?? 0) ?>">
                <input type="hidden" id="pa-form-token" value="<?= html_escape($account_form_token) ?>">
                <div class="row" id="pa-app-grid"><div class="col-12 text-center py-5 pa-note"><i class="fas fa-circle-notch fa-spin mr-2"></i>Memuat daftar aplikasi…</div></div>
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mt-3 pt-3 border-top">
                    <div class="pa-note mb-2 mb-sm-0"><i class="fas fa-info-circle mr-1"></i>Akses SSO memerlukan pengaturan yang disimpan secara eksplisit.</div>
                    <button type="submit" class="btn btn-primary" id="pa-save"><i class="fas fa-save mr-1"></i> Simpan akses</button>
                </div>
            </form>
        <?php endif ?>
    </div>
</section>
<div class="modal fade" id="pa-account-modal" tabindex="-1" role="dialog" aria-labelledby="pa-account-modal-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><div><h5 class="modal-title font-weight-bold" id="pa-account-modal-title">Akun aplikasi</h5><div class="small text-muted" id="pa-account-app-name"></div></div><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button></div><div class="modal-body"><div id="pa-account-message" class="alert alert-danger d-none"></div><div class="form-group mb-1"><label for="pa-account-select" class="font-weight-bold">Akun terpasang</label><select id="pa-account-select" class="form-control" disabled><option>Memuat akun…</option></select><small class="form-text text-muted">Akun yang sedang digunakan user lain tidak tersedia.</small></div></div><div class="modal-footer justify-content-between"><button type="button" class="btn btn-outline-danger" id="pa-account-unassign" style="display:none"><i class="fas fa-unlink mr-1"></i> Cabut penetapan</button><div class="ml-auto"><button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button> <button type="button" class="btn btn-success" id="pa-account-save" disabled><i class="fas fa-save mr-1"></i> Simpan akun</button></div></div></div></div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function(){
    const detailUrl='<?= base_url('admin/portal-app-access/detail') ?>';
    const saveUrl='<?= base_url('admin/portal-app-access/save') ?>';
    const escapeHtml=value=>String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    function updateCounts(){const total=$('.pa-access-toggle').length,allowed=$('.pa-access-toggle:checked').length;$('#pa-total').text(total);$('#pa-allowed').text(allowed);$('#pa-denied').text(total-allowed);}
    function loadAccount(){
        const source=$('#pa-source').val(),id=$('#pa-user-id').val();
        $('#pa-app-grid').html('<div class="col-12 text-center py-5 pa-note"><i class="fas fa-circle-notch fa-spin mr-2"></i>Memuat pengaturan akses…</div>');
        $('#pa-save').prop('disabled',true);
        $.getJSON(detailUrl,{source:source,user_id:id}).done(function(res){
            if(!res.status){$('#pa-app-grid').html('<div class="col-12"><div class="alert alert-danger mb-0">'+escapeHtml(res.message)+'</div></div>');return;}
            const data=res.data,user=data.user,denied=data.denied_apps||[];
            $('#pa-source').val(user.auth_source);$('#pa-user-id').val(user.id);$('#pa-user-name').text(user.display_name);$('#pa-user-department').text(user.departemen||'-');$('#pa-user-jobdesk').text(user.jobdesk||'-');
            $('#pa-config-state').attr('class','badge '+(data.configured?'badge-success':'badge-warning')).text(data.configured?'Pengaturan tersimpan':'Belum ada penetapan');
            let html='';Object.keys(data.apps).forEach(function(code){const app=data.apps[code],allowed=denied.indexOf(code)===-1;
                const callback=app.callback_url||'';
                const moduleUrl='<?= base_url('admin/portal-app-modules') ?>?app_code='+encodeURIComponent(code)+'&source='+encodeURIComponent($('#pa-source').val())+'&user_id='+encodeURIComponent($('#pa-user-id').val());
                const assignment=app.assigned_client_account||null;
                const accountStatus=app.supports_account_assignment?(assignment?'<div class="small mt-2 text-success pa-assigned-account"><i class="fas fa-user-check mr-1"></i>Akun: <strong>'+escapeHtml(assignment.username)+'</strong></div>':'<div class="small mt-2 text-muted pa-assigned-account"><i class="fas fa-user-clock mr-1"></i>Belum ada akun terpasang</div>'):'';
                const accountButton=app.supports_account_assignment?'<button type="button" class="btn btn-sm btn-outline-success mt-3 ml-1 pa-open-account" data-app-code="'+escapeHtml(code)+'" data-app-name="'+escapeHtml(app.name)+'" '+(allowed?'':'disabled')+'><i class="fas fa-key mr-1"></i> Akun</button>':'';
                html+='<div class="col-sm-6 col-xl-4 mb-3"><article class="pa-app" data-app-code="'+escapeHtml(code)+'"><div class="d-flex align-items-start justify-content-between"><div class="pa-app-icon" style="--pa-accent:'+escapeHtml(app.accent)+'"><i class="fas '+escapeHtml(app.icon)+'"></i></div><div class="custom-control custom-switch pa-switch"><input class="custom-control-input pa-access-toggle" type="checkbox" id="pa-'+escapeHtml(code)+'" name="allowed_apps[]" value="'+escapeHtml(code)+'" '+(allowed?'checked':'')+'><label class="custom-control-label" for="pa-'+escapeHtml(code)+'"></label></div></div><h6 class="font-weight-bold mt-3 mb-1">'+escapeHtml(app.name)+'</h6><div class="pa-note mb-2">'+escapeHtml(app.description)+'</div>'+accountStatus+'<div class="pa-app-url mt-2"><strong>Callback terdaftar:</strong><br>'+(callback?escapeHtml(callback):'Belum terdaftar atau integrasi belum aktif.')+'</div><a class="btn btn-sm btn-outline-primary mt-3" href="'+moduleUrl+'"><i class="fas fa-list-alt mr-1"></i> Detail Module</a>'+accountButton+'</article></div>';
            });
            $('#pa-app-grid').html(html||'<div class="col-12 pa-note">Tidak ada aplikasi pada katalog.</div>');$('#pa-save').prop('disabled',false);updateCounts();
        }).fail(function(){$('#pa-app-grid').html('<div class="col-12"><div class="alert alert-danger mb-0">Server tidak merespons saat memuat akses.</div></div>');}).always(function(){$('#pa-save').prop('disabled',false);});
    }
    if($('#pa-user-id').val()){$('#pa-app-grid').on('change','.pa-access-toggle',updateCounts);$('#pa-select-all').on('click',function(){$('.pa-access-toggle').prop('checked',true);updateCounts();});$('#pa-select-none').on('click',function(){$('.pa-access-toggle').prop('checked',false);updateCounts();});
        $('#pa-form').on('submit',function(event){event.preventDefault();const $button=$('#pa-save');$button.prop('disabled',true).html('<i class="fas fa-circle-notch fa-spin mr-1"></i>Menyimpan…');$.ajax({url:saveUrl,type:'POST',data:$(this).serialize(),dataType:'json'}).done(function(res){if(res.status){toastr.success(res.message);$('#pa-config-state').attr('class','badge badge-success').text('Pengaturan tersimpan');loadAccount();}else{toastr.error(res.message);}}).fail(function(){toastr.error('Server tidak merespons saat menyimpan akses.');}).always(function(){$button.prop('disabled',false).html('<i class="fas fa-save mr-1"></i> Simpan akses');});});
        const accountOptionsUrl='<?= base_url('admin/portal-app-access/account-options') ?>',accountSaveUrl='<?= base_url('admin/portal-app-access/account-save') ?>';let activeAccountApp='';
        $('#pa-app-grid').on('click','.pa-open-account',function(){activeAccountApp=String($(this).data('app-code'));$('#pa-account-app-name').text($(this).data('app-name'));$('#pa-account-message').addClass('d-none').text('');$('#pa-account-select').prop('disabled',true).html('<option>Memuat akun…</option>');$('#pa-account-save').prop('disabled',true);$('#pa-account-unassign').hide();$('#pa-account-modal').modal('show');
            $.getJSON(accountOptionsUrl,{app_code:activeAccountApp,source:$('#pa-source').val(),user_id:$('#pa-user-id').val()}).done(function(res){if(!res.status){$('#pa-account-select').html('<option value="0">Tidak tersedia</option>');$('#pa-account-message').removeClass('d-none').text(res.message);return;}const accounts=res.data.accounts||[],assignment=res.data.assignment||null;let options='<option value="0">Tidak ada akun terpasang</option>';accounts.forEach(function(account){options+='<option value="'+Number(account.id)+'">'+escapeHtml(account.username)+'</option>';});$('#pa-account-select').html(options).val(assignment?String(assignment.account_id):'0').prop('disabled',false);$('#pa-account-unassign').toggle(!!assignment);$('#pa-account-save').prop('disabled',false);}).fail(function(){$('#pa-account-message').removeClass('d-none').text('Daftar akun gagal dimuat.');});
        });
        function saveAccountAssignment(accountId){const $button=$('#pa-account-save');$button.prop('disabled',true);$.ajax({url:accountSaveUrl,type:'POST',data:{form_token:$('#pa-form-token').val(),app_code:activeAccountApp,source:$('#pa-source').val(),user_id:$('#pa-user-id').val(),account_id:accountId},dataType:'json'}).done(function(res){if(res.status){toastr.success(res.message);$('#pa-account-modal').modal('hide');loadAccount();}else{$('#pa-account-message').removeClass('d-none').text(res.message);}}).fail(function(){$('#pa-account-message').removeClass('d-none').text('Penetapan akun gagal disimpan.');}).always(function(){$button.prop('disabled',false);});}
        $('#pa-account-save').on('click',function(){saveAccountAssignment($('#pa-account-select').val()||0);});$('#pa-account-unassign').on('click',function(){saveAccountAssignment(0);});
        loadAccount();
    }
});
</script>
