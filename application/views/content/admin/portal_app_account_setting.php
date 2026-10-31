<style>
.pas-wrap{max-width:900px;margin:auto;color:#172b45}.pas-hero{padding:22px;border-radius:18px;color:#fff;background:linear-gradient(120deg,#10283d,#145d69 68%,#1c8d83)}.pas-card{border:1px solid #e4eaf1;border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(19,35,59,.06)}.pas-icon{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;background:#e8f4f4;color:#176b78;font-size:20px}
</style>
<section class="content p-3 p-md-4 pas-wrap">
    <div class="pas-hero mb-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div><div class="small text-uppercase font-weight-bold" style="opacity:.72;letter-spacing:.1em">Pengaturan akun aplikasi</div><h3 class="font-weight-bold mb-1"><?= html_escape($app['name']) ?></h3><div style="opacity:.85"><?= html_escape($user['display_name']) ?> · <?= html_escape($user['username']) ?></div></div>
        <a class="btn btn-outline-light mt-3 mt-md-0" href="<?= base_url($back_url) ?>"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    </div>
    <div class="pas-card p-3 p-md-4">
        <div class="d-flex align-items-start mb-3"><div class="pas-icon mr-3"><i class="fas fa-key"></i></div><div><h5 class="font-weight-bold mb-1">Pilih akun yang digunakan</h5><div class="text-muted">Setiap akun klien hanya dapat digunakan oleh satu user untuk aplikasi ini. Akun yang sudah dipakai user lain tidak ditampilkan.</div></div></div>
        <div id="pas-message" class="alert d-none" role="status"></div>
        <div class="form-group"><label for="pas-account" class="font-weight-bold">Akun klien tersedia</label><select id="pas-account" class="form-control" disabled><option>Memuat daftar akun…</option></select><small class="form-text text-muted" id="pas-count"></small></div>
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between border-top pt-3"><span class="small text-muted mb-2 mb-sm-0"><i class="fas fa-shield-alt mr-1"></i>Password tidak ditampilkan pada pengaturan ini.</span><button id="pas-save" class="btn btn-primary" type="button" disabled><i class="fas fa-save mr-1"></i> Simpan pilihan</button></div>
    </div>
</section>
<script>
$(function(){
    const appCode=<?= json_encode($app_code) ?>,source=<?= json_encode($user['auth_source']) ?>,userId=<?= (int)$user['id'] ?>;
    const optionsUrl=<?= json_encode(base_url($is_admin_setting ? 'admin/portal-app-access/account-options' : 'portal/account-options')) ?>;
    const saveUrl=<?= json_encode(base_url($is_admin_setting ? 'admin/portal-app-access/account-save' : 'portal/account-save')) ?>;
    let formToken=<?= json_encode($form_token) ?>;
    const $select=$('#pas-account'),$save=$('#pas-save'),$message=$('#pas-message');
    function showMessage(text,ok){$message.removeClass('d-none alert-success alert-danger').addClass(ok?'alert-success':'alert-danger').text(text);}
    function loadAccounts(){
        $select.prop('disabled',true).html('<option>Memuat daftar akun…</option>');$save.prop('disabled',true);
        $.getJSON(optionsUrl,{app_code:appCode,source:source,user_id:userId}).done(function(res){
            if(!res.status){$select.html('<option value="">Daftar akun tidak tersedia</option>');showMessage(res.message,false);return;}
            const accounts=res.data.accounts||[],assignment=res.data.assignment||null;
            let html='<option value="0">Belum memilih akun</option>';
            accounts.forEach(function(account){html+='<option value="'+Number(account.id)+'">'+ $('<div>').text(account.username).html()+(account.assigned_to_user?' · sedang digunakan':'')+'</option>';});
            $select.html(html).val(assignment?String(assignment.account_id):'0').prop('disabled',false);
            $('#pas-count').text(accounts.length+' akun tersedia'+(assignment?' · saat ini: '+assignment.username:''));
            $save.prop('disabled',false);$message.addClass('d-none');
        }).fail(function(){$select.html('<option value="">Gagal memuat akun</option>');showMessage('Server tidak merespons saat memuat daftar akun.',false);});
    }
    $save.on('click',function(){
        $save.prop('disabled',true).html('<i class="fas fa-circle-notch fa-spin mr-1"></i>Menyimpan…');
        $.ajax({url:saveUrl,type:'POST',data:{form_token:formToken,app_code:appCode,source:source,user_id:userId,account_id:$select.val()},dataType:'json'})
            .done(function(res){if(res.data&&res.data.form_token)formToken=res.data.form_token;if(res.status){showMessage(res.message,true);loadAccounts();}else showMessage(res.message,false);})
            .fail(function(){showMessage('Pilihan gagal disimpan. Muat ulang daftar akun lalu coba lagi.',false);})
            .always(function(){$save.prop('disabled',false).html('<i class="fas fa-save mr-1"></i> Simpan pilihan');});
    });
    loadAccounts();
});
</script>
