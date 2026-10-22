<section class="content-header"><div class="container-fluid"><h1>Permintaan Reset Password</h1></div></section>
<section class="content"><div class="container-fluid">
    <?php if ($message = $this->session->flashdata('reset_success')): ?><div class="alert alert-success"><?= html_escape($message) ?></div><?php endif ?>
    <?php if ($message = $this->session->flashdata('reset_error')): ?><div class="alert alert-danger"><?= html_escape($message) ?></div><?php endif ?>
    <div class="card"><div class="card-body table-responsive">
        <table class="table table-bordered table-hover"><thead><tr><th>Username</th><th>Sumber akun</th><th>Waktu permintaan</th><th>Status</th><th>Waktu diproses</th><th>Aksi</th></tr></thead><tbody>
        <?php if (empty($requests)): ?><tr><td colspan="6" class="text-center">Belum ada permintaan reset password.</td></tr><?php endif ?>
        <?php foreach ($requests as $request): ?><tr>
            <td><?= html_escape($request->username) ?></td><td><?= html_escape($request->auth_source) ?></td>
            <td><?= html_escape($request->requested_at) ?></td><td><span class="badge badge-<?= $request->status === 'PENDING' ? 'warning' : 'success' ?>"><?= html_escape($request->status) ?></span></td>
            <td><?= html_escape($request->processed_at ?: '-') ?></td><td>
                <?php if ($request->status === 'PENDING'): ?><form method="post" action="<?= base_url('admin/password-reset-requests/reset/' . (int)$request->id) ?>" onsubmit="return confirm('Reset password akun ini menjadi karisma123?')">
                    <?php if (config_item('csrf_protection')): ?><input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>"><?php endif ?>
                    <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-key"></i> Reset ke karisma123</button></form><?php else: ?>-<?php endif ?>
            </td>
        </tr><?php endforeach ?>
        </tbody></table>
        <p class="text-muted mb-0">Password sementara adalah <code>karisma123</code>. Sampaikan kepada pemilik akun melalui jalur internal yang terverifikasi.</p>
    </div></div>
</div></section>
