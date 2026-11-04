<section class="content p-3 p-md-4">
    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title">Cek API Klien SSO</h3></div>
        <div class="card-body">
            <p>Periksa kesiapan Portal untuk aplikasi dan identitas user tertentu. Pemeriksaan tidak mengubah akun, menerbitkan code, atau membuat session klien.</p>
            <a class="btn btn-outline-primary mb-3" href="<?= base_url('admin/api-documentation') ?>">Dokumentasi API</a>
            <a class="btn btn-outline-secondary mb-3" href="<?= base_url('admin/portal-sso-clients') ?>">Integrasi SSO</a>
            <a class="btn btn-outline-secondary mb-3" href="<?= base_url('admin/portal-sso-clients/login-history') ?>">Histori login klien</a>
            <form method="post" action="<?= base_url('admin/portal-sso-check') ?>">
                <input type="hidden" name="form_token" value="<?= html_escape($form_token) ?>">
                <div class="form-group"><label for="sso-check-app">Aplikasi klien</label><select class="form-control" name="app_code" id="sso-check-app" required>
                    <option value="">Pilih aplikasi</option>
                    <?php foreach ($clients as $client): ?>
                    <option value="<?= html_escape($client['app_code']) ?>" <?= $app_code === $client['app_code'] ? 'selected' : '' ?>><?= html_escape($client['app_code']) ?> — <?= (int)$client['is_enabled'] === 1 ? 'Aktif' : 'Nonaktif' ?></option>
                    <?php endforeach ?>
                </select></div>
                <div class="form-group"><label for="sso-check-user">Identitas Portal yang diuji</label><select class="form-control" name="subject" id="sso-check-user" required><option value="">Pilih aplikasi klien dahulu</option></select></div>
                <div id="sso-check-warning" class="alert alert-warning" role="alert" style="display:none">Belum ada identitas Portal dengan akses dan akun kerja yang dikaitkan ke aplikasi klien ini.</div>
                <?php if ($warning): ?><div class="alert alert-warning" role="alert"><?= html_escape($warning) ?></div><?php endif ?>
                <div class="form-group"><label for="sso-check-callback">Callback terdaftar pada aplikasi klien</label><input id="sso-check-callback" class="form-control" type="url" value="<?= html_escape($redirect_uri) ?>" readonly><small class="text-muted">Diambil otomatis dari konfigurasi SSO aplikasi yang dipilih.</small></div>
                <button class="btn btn-primary" type="submit">Jalankan pemeriksaan</button>
            </form>
        </div>
    </div>
    <?php if ($report): ?>
    <div class="card"><div class="card-body">
        <h4><?= $report['portal_ready'] ? 'Prasyarat Portal lulus' : 'Prasyarat Portal belum lengkap' ?></h4>
        <p>Akun kerja: <strong><?= html_escape($report['client_account_username'] ?? 'Belum ditetapkan') ?></strong>. Session dan level akses klien: <strong>belum diverifikasi</strong>.</p>
        <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Status</th><th>Pemeriksaan</th></tr></thead><tbody>
        <?php foreach ($report['checks'] as $check): ?>
            <tr><td><span class="badge badge-<?= $check['status'] === 'pass' ? 'success' : 'danger' ?>"><?= $check['status'] === 'pass' ? 'Lulus' : 'Gagal' ?></span></td><td><?= html_escape($check['message']) ?></td></tr>
        <?php endforeach ?>
        </tbody></table></div>
        <div class="alert alert-info"><?= html_escape($report['next_step']) ?></div>
    </div></div>
    <?php endif ?>
    <div class="card"><div class="card-body">
        <h4>Pengujian integrasi dari backend dan browser klien</h4>
        <ol>
            <li>Dari backend klien, panggil <code>POST /api/sso/check</code> dengan client_id, client_secret, sub, dan redirect_uri. HTTP 200 berarti pemeriksaan berjalan; pastikan <code>data.portal_ready = true</code>. Ini juga menguji koneksi HTTPS dan kecocokan secret.</li>
            <li>Login Portal sebagai user yang dipilih pada browser uji. Buka kartu aplikasi lalu pilih <strong>Mulai Bekerja</strong>. Memilih user di formulir ini tidak mengganti identitas session admin.</li>
            <li>Pastikan callback memvalidasi state, backend menukar code dengan PKCE, akun lokal cocok tepat satu dengan klaim <code>client_account_username</code>, dan level akses lokal diizinkan.</li>
            <li>Pastikan dashboard terbuka tanpa password lokal, session lama diganti, menu dan data sesuai role. Cocokkan histori login klien; histori merupakan laporan klien, bukan bukti pemeriksaan semua izin modul.</li>
            <li>Uji penolakan: izin dicabut, assignment kosong, akun lokal hilang/ganda/nonaktif, role tidak sah, state salah, verifier salah, secret salah, callback berbeda, code kedaluwarsa dan code dipakai ulang. Tidak boleh terbentuk session baru.</li>
        </ol>
        <p class="mb-0">Panduan pencatatan hasil: <code>docs/penggunaan/portal/cek-api-klien-sso.md</code>. Portal tidak membaca database atau memeriksa implementasi modul internal klien.</p>
    </div></div>
</section>
<script>
(function () {
    const clients = <?= json_encode(array_column($clients, null, 'app_code'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const linkedUsers = <?= json_encode($linked_users, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const selectedSubject = <?= json_encode($subject, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const app = document.getElementById('sso-check-app');
    const user = document.getElementById('sso-check-user');
    const callback = document.getElementById('sso-check-callback');
    const warning = document.getElementById('sso-check-warning');
    function refresh() {
        const code = app.value;
        const options = linkedUsers[code] || [];
        user.replaceChildren(new Option(options.length ? 'Pilih identitas Portal' : 'Belum ada identitas terkait', ''));
        options.forEach(function (entry) { user.add(new Option(entry.label, entry.subject)); });
        if (options.some(function (entry) { return entry.subject === selectedSubject; })) user.value = selectedSubject;
        callback.value = clients[code] ? clients[code].redirect_uri || '' : '';
        warning.style.display = code && !options.length ? '' : 'none';
        user.disabled = !options.length;
    }
    app.addEventListener('change', refresh);
    refresh();
})();
</script>
