<style>
.psai-page{--psai-ink:#14233b;--psai-muted:#6b7890;--psai-line:#e5eaf2;color:var(--psai-ink)}.psai-hero{border-radius:20px;padding:26px;background:linear-gradient(120deg,#10283d,#145d69 65%,#1c8d83);color:#fff;box-shadow:0 16px 34px rgba(16,40,61,.18)}.psai-panel{background:#fff;border:1px solid var(--psai-line);border-radius:16px;padding:18px;box-shadow:0 8px 22px rgba(19,35,59,.045)}.psai-muted{color:var(--psai-muted)}
@media(max-width:767.98px){.psai-hero{padding:20px}.psai-panel{padding:14px}}
</style>
<section class="content p-3 p-md-4 psai-page">
    <div class="psai-hero mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center"><div><div class="small text-uppercase font-weight-bold mb-1" style="letter-spacing:.12em;opacity:.72">Portal SSO</div><h2 class="font-weight-bold mb-1">Akun List</h2><div style="opacity:.82">Pilih klien untuk melihat dan mengelola akun serta PIC.</div></div><a href="<?= base_url('admin/portal-sso-clients') ?>" class="btn btn-outline-light font-weight-bold mt-3 mt-md-0"><i class="fas fa-arrow-left mr-1"></i> Integrasi SSO</a></div>
    <?php if (!$account_table_ready): ?><div class="alert alert-warning">Tabel akun klien belum tersedia. Terapkan <code>docs/database/portal_sso_client_account_20261029.sql</code>.</div><?php endif ?>
    <div class="psai-panel">
        <?php if (empty($clients)): ?><div class="text-center py-5"><div class="psai-muted mb-2"><i class="fas fa-plug fa-2x"></i></div><h6 class="font-weight-bold">Belum ada klien SSO</h6><div class="psai-muted">Daftarkan integrasi terlebih dahulu pada halaman Integrasi SSO.</div></div>
        <?php else: ?><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Klien aplikasi</th><th>Kode</th><th>Status</th><th>Jumlah akun</th><th class="text-right">Aksi</th></tr></thead><tbody>
            <?php foreach ($clients as $client): ?><tr><td class="font-weight-bold"><?= html_escape($client['display_name']) ?></td><td><code><?= html_escape($client['app_code']) ?></code></td><td><span class="badge badge-<?= (int)$client['is_enabled'] === 1 ? 'success' : 'secondary' ?>"><?= (int)$client['is_enabled'] === 1 ? 'Aktif' : 'Nonaktif' ?></span></td><td><?= (int)$client['account_count'] ?></td><td class="text-right"><a href="<?= base_url('admin/portal-sso-clients/accounts/' . rawurlencode($client['app_code'])) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-users mr-1"></i> Buka Akun List</a></td></tr><?php endforeach ?>
        </tbody></table></div><?php endif ?>
    </div>
</section>
