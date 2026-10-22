<section class="content-header"><div class="container-fluid"><h1>Akses Aplikasi Portal</h1></div></section>
<section class="content"><div class="container-fluid">
    <?php if ($save_success): ?><div class="alert alert-success"><?= html_escape($save_success) ?></div><?php endif ?>
    <?php if ($save_error): ?><div class="alert alert-danger"><?= html_escape($save_error) ?></div><?php endif ?>
    <?php if (!$this->db->table_exists('tb_portal_app_access')): ?>
        <div class="alert alert-warning">Tabel pengaturan akses belum tersedia. Terapkan skrip database <code>docs/database/portal_app_access_20261021.sql</code> terlebih dahulu.</div>
    <?php endif ?>
    <div class="card"><div class="card-body">
        <?php if (empty($users)): ?>
            <div class="alert alert-info mb-0">Belum ada akun yang dapat dikelola.</div>
        <?php else: ?>
            <form method="get" action="<?= base_url('admin/portal-app-access') ?>" class="form-row align-items-end mb-4">
                <div class="form-group col-md-8"><label for="portal-user">Pilih akun</label>
                    <select class="form-control" id="portal-user" name="user_id" required>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= (int)$user['id'] ?>" data-source="<?= html_escape($user['auth_source']) ?>" <?= $selected_user && $selected_user['auth_source'] === $user['auth_source'] && (int)$selected_user['id'] === (int)$user['id'] ? 'selected' : '' ?>>
                                <?= html_escape($user['display_name']) ?> — <?= html_escape($user['username']) ?> (<?= html_escape($user['auth_source']) ?><?= (int)$user['status'] === 1 ? '' : ', nonaktif' ?>)
                            </option>
                        <?php endforeach ?>
                    </select>
                    <input type="hidden" id="portal-user-source" name="source" value="<?= html_escape($selected_user['auth_source'] ?? '') ?>">
                </div>
                <div class="form-group col-md-4"><button type="submit" class="btn btn-outline-primary">Tampilkan akun</button></div>
            </form>
            <?php if ($selected_user): ?>
                <div class="mb-3"><strong><?= html_escape($selected_user['display_name']) ?></strong> <span class="text-muted">(<?= html_escape($selected_user['username']) ?> · <?= html_escape($selected_user['auth_source']) ?>)</span></div>
                <form method="post" action="<?= base_url('admin/portal-app-access/save') ?>">
                    <?php if (config_item('csrf_protection')): ?><input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>"><?php endif ?>
                    <input type="hidden" name="auth_source" value="<?= html_escape($selected_user['auth_source']) ?>">
                    <input type="hidden" name="user_id" value="<?= (int)$selected_user['id'] ?>">
                    <div class="table-responsive"><table class="table table-bordered table-hover">
                        <thead><tr><th>Aplikasi</th><th>Alamat</th><th class="text-center" style="width:160px">Akses di Portal</th></tr></thead>
                        <tbody><?php foreach ($apps as $code => $app): ?><tr>
                            <td><i class="fas <?= html_escape($app['icon']) ?> mr-2"></i><?= html_escape($app['name']) ?></td>
                            <td><a href="<?= html_escape($app['url']) ?>" target="_blank" rel="noopener noreferrer"><?= html_escape($app['url']) ?></a></td>
                            <td class="text-center"><div class="custom-control custom-switch d-inline-block">
                                <input class="custom-control-input" type="checkbox" id="app-<?= html_escape($code) ?>" name="allowed_apps[]" value="<?= html_escape($code) ?>" <?= in_array($code, $denied_apps, true) ? '' : 'checked' ?>>
                                <label class="custom-control-label" for="app-<?= html_escape($code) ?>">Diizinkan</label>
                            </div></td>
                        </tr><?php endforeach ?></tbody>
                    </table></div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan akses</button>
                </form>
            <?php endif ?>
        <?php endif ?>
    </div></div>
</div></section>
<script>
document.getElementById('portal-user')?.addEventListener('change', function () {
    document.getElementById('portal-user-source').value = this.options[this.selectedIndex].dataset.source;
});
</script>
