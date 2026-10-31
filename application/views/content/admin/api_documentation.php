<style>
.api-doc{--ad-ink:#14233b;--ad-muted:#6c7a90;--ad-line:#e3e9f1;--ad-teal:#176b78;color:var(--ad-ink)}.api-hero{position:relative;overflow:hidden;border-radius:20px;padding:26px;background:linear-gradient(120deg,#10283d,#145d69 65%,#1c8d83);color:#fff;box-shadow:0 16px 34px rgba(16,40,61,.18)}.api-hero:after{content:"";position:absolute;width:230px;height:230px;right:-60px;top:-95px;border:1px solid rgba(255,255,255,.16);border-radius:50%;box-shadow:0 0 0 28px rgba(255,255,255,.04),0 0 0 58px rgba(255,255,255,.03)}.api-card,.api-section{background:#fff;border:1px solid var(--ad-line);border-radius:16px;box-shadow:0 8px 22px rgba(19,35,59,.045)}.api-card{padding:17px;height:100%}.api-section{padding:22px;margin-bottom:16px;scroll-margin-top:18px}.api-kicker{font-size:11px;text-transform:uppercase;letter-spacing:.1em;color:var(--ad-muted);font-weight:700}.api-metric{font-size:22px;font-weight:750}.api-mark{height:42px;width:42px;border-radius:13px;display:grid;place-items:center;background:#e8f4f4;color:var(--ad-teal);font-size:17px}.api-nav{position:sticky;top:14px}.api-nav a{display:block;padding:9px 11px;border-radius:9px;color:#45556c;text-decoration:none;font-size:13px}.api-nav a:hover,.api-nav a.active{background:#eaf4f4;color:#135d66;font-weight:700}.api-code{position:relative;background:#101c2d;color:#d8e5f7;border-radius:12px;padding:16px;overflow:auto;font:12px/1.65 Consolas,Monaco,monospace;white-space:pre}.api-code code{color:inherit}.api-copy{position:absolute;top:9px;right:9px}.api-badge{display:inline-flex;padding:4px 8px;border-radius:99px;font-size:11px;font-weight:700}.api-badge-green{background:#e7f6ec;color:#187443}.api-badge-amber{background:#fff5db;color:#8a6410}.api-step{display:flex;gap:12px;padding:14px;border:1px solid var(--ad-line);border-radius:12px;height:100%}.api-step-num{flex:0 0 30px;height:30px;border-radius:10px;background:#e9f3f4;color:var(--ad-teal);display:grid;place-items:center;font-weight:800}.api-checklist label{display:flex;gap:10px;padding:11px;border-bottom:1px solid #edf1f5;cursor:pointer}.api-checklist label:last-child{border-bottom:0}.api-endpoint{font-family:Consolas,monospace;font-size:12px;overflow-wrap:anywhere}.api-table th{white-space:nowrap;background:#f5f8fb}.api-callout{border-left:4px solid #d59b23;background:#fff9e8;padding:14px 16px;border-radius:0 12px 12px 0}.api-callout-danger{border-color:#cf5147;background:#fff3f2}.api-result{min-height:16px;color:var(--ad-muted);font-size:12px}
@media(max-width:767.98px){.api-hero{padding:20px}.api-section{padding:16px}.api-nav{position:static;display:flex;overflow:auto;white-space:nowrap}.api-nav a{display:inline-block}}
</style>
<section class="content p-3 p-md-4 api-doc">
    <div class="api-hero mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div><div class="small text-uppercase font-weight-bold mb-1" style="letter-spacing:.12em;opacity:.72">Developer guide · KARISMA ERP</div><h2 class="font-weight-bold mb-1">Dokumentasi API Integrasi</h2><div style="opacity:.8">Panduan klien untuk berintegrasi dengan autentikasi dan hak akses SSO Portal.</div></div>
        <div class="d-flex flex-wrap mt-3 mt-md-0"><a class="btn btn-outline-light font-weight-bold mr-2 mb-1" href="<?= base_url('portal') ?>"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Portal</a><a class="btn btn-light font-weight-bold mb-1" href="<?= base_url('admin/portal-sso-clients') ?>"><i class="fas fa-plug mr-1"></i> Integrasi SSO</a></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4 mb-2"><div class="api-card d-flex justify-content-between align-items-center"><div><div class="api-kicker">Endpoint SSO klien</div><div class="api-metric">4 endpoint</div></div><span class="api-mark"><i class="fas fa-exchange-alt"></i></span></div></div>
        <div class="col-md-4 mb-2"><div class="api-card d-flex justify-content-between align-items-center"><div><div class="api-kicker">Metode autentikasi</div><div class="api-metric">Code + PKCE</div></div><span class="api-mark"><i class="fas fa-key"></i></span></div></div>
        <div class="col-md-4 mb-2"><div class="api-card d-flex justify-content-between align-items-center"><div><div class="api-kicker">Masa berlaku code</div><div class="api-metric">120 detik · sekali pakai</div></div><span class="api-mark"><i class="fas fa-hourglass-half"></i></span></div></div>
    </div>
    <div class="api-card mb-3">
        <label class="api-kicker mb-2" for="api-search">Cari dalam panduan</label>
        <input class="form-control" id="api-search" type="search" placeholder="Contoh: callback, client_secret, hak akses, PKCE">
        <div class="api-result mt-2" id="api-search-result">Panduan integrasi dan referensi endpoint portal.</div>
    </div>
    <div class="row">
        <div class="col-lg-3 mb-3"><nav class="api-card api-nav" aria-label="Daftar isi"><div class="api-kicker px-2 mb-2">Daftar isi</div><a href="#overview">Prinsip integrasi</a><a href="#admin-setup">Persiapan admin</a><a href="#flow">Alur SSO</a><a href="#endpoints">Endpoint Portal</a><a href="#modules">Modul sisi klien</a><a href="#client-routes">Route sisi klien</a><a href="#claims">Respons identitas</a><a href="#access">Hak akses</a><a href="#usage">Tata cara pengguna</a><a href="#endpoint-test">Uji endpoint</a><a href="#client-check">API cek klien dan audit</a><a href="#onboarding">Data yang diperlukan</a><a href="#database">Kebutuhan database</a><a href="#errors">Error dan batas</a></nav></div>
        <div class="col-lg-9">
            <article class="api-section" id="overview" data-api-section>
                <a class="btn btn-primary mb-3" href="<?= base_url('admin/portal-sso-check') ?>">Buka Cek API Klien SSO</a><div class="api-kicker">01 · Model keamanan</div><h4 class="font-weight-bold mt-1">Portal memverifikasi identitas; klien membuat session sendiri</h4>
                <p class="text-muted">Portal tidak meneruskan cookie session atau password kepada aplikasi klien. Backend klien menukar authorization code dengan portal melalui HTTPS, menerima klaim identitas, kemudian membuat session lokalnya.</p>
                <div class="row">
                    <div class="col-md-6 mb-2"><div class="api-callout h-100"><strong><i class="fas fa-check-circle text-success mr-1"></i>Yang tersedia</strong><div class="small mt-1">SSO berbasis authorization code, PKCE S256, client secret, serta izin masuk user per aplikasi.</div></div></div>
                    <div class="col-md-6 mb-2"><div class="api-callout api-callout-danger h-100"><strong><i class="fas fa-exclamation-circle text-danger mr-1"></i>Batas sekarang</strong><div class="small mt-1">Belum OpenID Connect penuh. Portal belum mengirim role, izin modul, token API, atau logout lintas aplikasi.</div></div></div>
                </div>
                <div class="small text-muted mt-2">Base URL portal di lingkungan Anda: <code><?= html_escape(base_url()) ?></code></div>
            </article>
            <article class="api-section" id="admin-setup" data-api-section>
                <div class="api-kicker">02 · Pengaturan administrator</div><h4 class="font-weight-bold mt-1">Aktifkan klien dan izin pengguna</h4>
                <div class="row">
                    <div class="col-md-6 mb-2"><div class="api-step"><span class="api-step-num">1</span><div><strong>Daftarkan klien</strong><div class="small text-muted">Pilih app_code, isi URL peluncuran menuju route awal SSO dan callback HTTPS. URL peluncuran memulai SSO klien; callback menerima code dan harus cocok persis.</div></div></div></div>
                    <div class="col-md-6 mb-2"><div class="api-step"><span class="api-step-num">2</span><div><strong>Simpan secret</strong><div class="small text-muted">Simpan pada secret manager server klien. Admin portal dapat membukanya melalui ikon key dengan PIN keamanan.</div></div></div></div>
                    <div class="col-md-6 mb-2"><div class="api-step"><span class="api-step-num">3</span><div><strong>Berikan izin aplikasi</strong><div class="small text-muted">Pada Akses Aplikasi, pilih user lalu simpan izin eksplisit aplikasi. Impor katalog akun klien dan tetapkan satu akun kerja pada identitas user tersebut sebelum login SSO.</div></div></div></div>
                    <div class="col-md-6 mb-2"><div class="api-step"><span class="api-step-num">4</span><div><strong>Siapkan klien dan uji</strong><div class="small text-muted">Aktifkan HTTPS/cURL, simpan secret di backend, siapkan akun serta role lokal, lalu uji user diizinkan dan ditolak.</div></div></div></div>
                </div>
                <div class="api-callout mt-2"><strong>Contoh pola URL:</strong> peluncuran <code>https://&lt;host&gt;/&lt;app&gt;/</code>; callback <code>https://&lt;host&gt;/&lt;app&gt;/auth/sso/callback</code>.</div>
                <div class="table-responsive mt-3"><table class="table table-bordered table-sm api-table mb-0"><thead><tr><th>Konfigurasi backend klien</th><th>Nilai</th></tr></thead><tbody>
                    <tr><td><code>PORTAL_BASE_URL</code></td><td>Base URL HTTPS Portal, misalnya <code>https://portal.example/</code>.</td></tr>
                    <tr><td><code>PORTAL_CLIENT_ID</code></td><td><code>app_code</code> yang didaftarkan Portal.</td></tr>
                    <tr><td><code>PORTAL_CLIENT_SECRET</code></td><td>Secret klien; simpan pada environment/secret manager server.</td></tr>
                    <tr><td><code>PORTAL_CALLBACK_URL</code></td><td>Callback HTTPS yang sama persis dengan registrasi Portal.</td></tr>
                </tbody></table></div>
                <div class="mt-3"><a class="btn btn-sm btn-outline-primary mr-2" href="<?= base_url('admin/portal-sso-clients') ?>"><i class="fas fa-plug mr-1"></i> Buka Integrasi SSO</a><a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/portal-app-access') ?>"><i class="fas fa-user-shield mr-1"></i> Buka Akses Aplikasi</a></div>
            </article>
            <article class="api-section" id="flow" data-api-section>
                <div class="api-kicker">03 · SSO client flow</div><h4 class="font-weight-bold mt-1">Authorization code dengan PKCE S256</h4>
                <ol class="pl-3">
                    <li>Klien membuka flow dari route awal aplikasi. Jika kebijakan klien mengizinkan pergantian user pada browser yang sama, lakukan re-auth SSO dan buang identitas lokal lama sebelum menerbitkan session baru.</li>
                    <li>Klien menghitung <code>code_challenge = BASE64URL(SHA256(code_verifier))</code> tanpa padding dan mengarahkan browser ke portal.</li>
                    <li>Portal memvalidasi klien, callback persis, PKCE, dan izin user. Jika user belum login, portal login dahulu lalu melanjutkan permintaan.</li>
                    <li>Portal mengarahkan callback klien dengan <code>code</code> dan <code>state</code>. Klien wajib cocokkan state dengan session miliknya.</li>
                    <li>Backend klien menukar code ke endpoint token dengan client secret dan code verifier.</li>
                    <li>Klien memvalidasi app_code, state, sub, dan client_account_username; menemukan tepat satu akun lokal yang ditetapkan, memeriksa status dan level akses, merotasi session, lalu membuka dashboard tanpa password lokal.</li>
                </ol>
                <div class="api-callout"><strong>Jangan kirim client_secret atau code_verifier ke browser.</strong> Code hanya dapat digunakan sekali dan berlaku 120 detik.</div>
                <div class="api-callout mt-2"><strong>Ganti akun:</strong> pastikan Portal sedang login sebagai user tujuan. Klien harus memastikan session lokal lama tidak dipakai untuk identitas Portal yang berbeda. Logout dari aplikasi klien saja tidak mengakhiri session Portal.</div>
                <h6 class="font-weight-bold mt-4">Contoh pembentukan PKCE di PHP</h6>
                <div class="position-relative"><button type="button" class="btn btn-sm btn-light api-copy" data-copy="pkce-code"><i class="fas fa-copy mr-1"></i>Salin</button><pre class="api-code" id="pkce-code"><code>$base64Url = function ($value) {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
};
$state = $base64Url(random_bytes(32));
$codeVerifier = $base64Url(random_bytes(32));
$codeChallenge = $base64Url(hash('sha256', $codeVerifier, true));

// Simpan state dan verifier pada session backend klien sebelum redirect.
$_SESSION['portal_sso'] = [
    'state' =&gt; $state,
    'code_verifier' =&gt; $codeVerifier,
];</code></pre></div>
                <h6 class="font-weight-bold mt-3">Permintaan authorize browser</h6>
                <div class="api-code"><code><?= html_escape(base_url('sso/authorize')) ?>?client_id={APP_CODE}&amp;redirect_uri={CALLBACK_URL}&amp;state={STATE}&amp;code_challenge={CHALLENGE}&amp;code_challenge_method=S256</code></div>
            </article>
            <article class="api-section" id="endpoints" data-api-section>
                <div class="api-kicker">04 · Referensi endpoint</div><h4 class="font-weight-bold mt-1">API yang dapat digunakan klien</h4>
                <div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>Metode dan route</th><th>Fungsi</th><th>Dipanggil dari</th></tr></thead><tbody>
                    <tr><td class="api-endpoint"><span class="api-badge api-badge-green">GET</span><br><?= html_escape(base_url('sso/authorize')) ?></td><td>Validasi klien, callback, state, PKCE, dan izin user; mengembalikan browser ke callback.</td><td>Browser user</td></tr>
                    <tr><td class="api-endpoint"><span class="api-badge api-badge-amber">POST</span><br><?= html_escape(base_url('api/sso/token')) ?></td><td>Tukar authorization code untuk klaim identitas.</td><td>Backend klien</td></tr>
                    <tr><td class="api-endpoint"><span class="api-badge api-badge-amber">POST</span><br><?= html_escape(base_url('api/sso/check')) ?></td><td>Periksa autentikasi klien dan prasyarat SSO Portal tanpa membuat session.</td><td>Backend klien</td></tr>
                    <tr><td class="api-endpoint"><span class="api-badge api-badge-amber">POST</span><br><?= html_escape(base_url('api/sso/login-event')) ?></td><td>Catat konfirmasi akun yang berhasil divalidasi klien.</td><td>Backend klien</td></tr>
                </tbody></table></div>
                <h6 class="font-weight-bold">Parameter authorize</h6><div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>Nama</th><th>Ketentuan</th></tr></thead><tbody>
                    <tr><td><code>client_id</code></td><td><code>app_code</code> yang telah didaftarkan.</td></tr><tr><td><code>redirect_uri</code></td><td>Callback HTTPS yang terdaftar; harus cocok persis.</td></tr><tr><td><code>state</code></td><td>Nilai acak minimal 16, maksimal 512 karakter; diverifikasi oleh klien.</td></tr><tr><td><code>code_challenge</code></td><td>SHA-256 dari verifier dalam Base64URL tanpa padding, 43 karakter.</td></tr><tr><td><code>code_challenge_method</code></td><td>Wajib bernilai <code>S256</code>.</td></tr>
                </tbody></table></div>
                <h6 class="font-weight-bold mt-3">Body POST token</h6><div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>Nama</th><th>Isi</th></tr></thead><tbody>
                    <tr><td><code>client_id</code></td><td>Kode aplikasi.</td></tr><tr><td><code>client_secret</code></td><td>Rahasia server klien, jangan kirim dari browser.</td></tr><tr><td><code>code</code></td><td>Code yang diterima callback.</td></tr><tr><td><code>redirect_uri</code></td><td>Callback HTTPS terdaftar yang sama persis.</td></tr><tr><td><code>code_verifier</code></td><td>Verifier yang disimpan sebelum authorize.</td></tr>
                </tbody></table></div>
                <div class="api-code mt-3"><code>Content-Type: application/x-www-form-urlencoded
POST <?= html_escape(base_url('api/sso/token')) ?></code></div>
            </article>
            <article class="api-section" id="modules" data-api-section>
                <div class="api-kicker">05 · Komponen klien</div><h4 class="font-weight-bold mt-1">Modul yang perlu disiapkan aplikasi klien</h4>
                <p class="text-muted">Portal hanya menyediakan autentikasi dan izin masuk aplikasi. Integrasi berikut dibuat pada backend masing-masing klien; desain menu dan route menyesuaikan arsitektur klien.</p>
                <div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>Modul/fungsi</th><th>Tanggung jawab klien</th></tr></thead><tbody>
                    <tr><td>Konfigurasi SSO</td><td>Muat app_code, secret server, URL Portal, callback terdaftar, dan CA HTTPS secara aman.</td></tr>
                    <tr><td>Adapter authorization-code + PKCE</td><td>Buat state/verifier, arahkan user ke authorize, validasi callback, lalu tukar code dari backend.</td></tr>
                    <tr><td>Pemetaan identitas</td><td>Temukan tepat satu akun lokal sesuai <code>client_account_username</code> yang ditetapkan admin untuk <code>sub</code>. Tolak akun hilang, ganda, nonaktif, atau level akses tidak sah; jangan membuat akun otomatis atau memakai fallback username Portal.</td></tr>
                    <tr><td>Session dan logout lokal</td><td>Rotasi session ID saat login, bersihkan identitas lama, batasi masa session, dan sediakan logout lokal.</td></tr>
                    <tr><td>Role dan otorisasi</td><td>Gunakan role/permission klien sendiri; periksa akses server-side pada setiap route/API dengan deny-by-default.</td></tr>
                    <tr><td>Audit dan error</td><td>Catat hasil autentikasi tanpa secret, code, verifier, atau payload sensitif; tampilkan error yang aman kepada user.</td></tr>
                </tbody></table></div>
                <div class="api-callout mt-3"><strong>Portal tidak menyediakan provisioning user, API role/menu, atau API bisnis milik klien.</strong> Jangan membuat endpoint klien untuk menerima password/secret Portal. Pertukaran token adalah request backend klien ke Portal.</div>
            </article>
            <article class="api-section" id="client-routes" data-api-section>
                <div class="api-kicker">06 · Route yang dibuat klien</div><h4 class="font-weight-bold mt-1">API klien tetap milik aplikasi klien</h4>
                <p class="text-muted">Nama route berikut contoh. Portal tidak memanggil API internal klien; browser mengunjungi URL peluncuran (route start SSO) dan callback terdaftar.</p>
                <div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>Route contoh</th><th>Fungsi dan aturan</th></tr></thead><tbody>
                    <tr><td><code>GET /auth/sso/start</code></td><td>Buat state/verifier di session server, lalu redirect browser ke <code>GET {PORTAL}/sso/authorize</code>.</td></tr>
                    <tr><td><code>GET /auth/sso/callback</code></td><td>Callback HTTPS yang didaftarkan persis; validasi state, lalu tukar code dari backend klien.</td></tr>
                    <tr><td><code>GET /api/session</code> (opsional)</td><td>Berikan status dan profil session lokal tanpa mengungkap secret/token Portal.</td></tr>
                    <tr><td><code>POST /auth/logout</code></td><td>Hapus session lokal dan rotasi session ID; logout Portal terpisah.</td></tr>
                    <tr><td><code>/api/&lt;modul-klien&gt;</code></td><td>API bisnis klien sendiri. Setiap route wajib memeriksa session, role, tindakan, dan cakupan datanya di server.</td></tr>
                </tbody></table></div>
                <div class="api-callout api-callout-danger mt-3"><strong>Jangan publikasikan route pertukaran token.</strong> POST <code>{PORTAL}/api/sso/token</code> dipanggil backend klien, bukan browser atau frontend.</div>
            </article>
            <article class="api-section" id="claims" data-api-section>
                <div class="api-kicker">07 · Identitas pengguna</div><h4 class="font-weight-bold mt-1">Klaim yang dikembalikan</h4>
                <div class="api-code"><code>{
  "success": true,
  "data": {
    "user": {
      "sub": "tb_karyawan:123",
      "user_id": 123,
      "auth_source": "tb_karyawan",
      "username": "contoh.user",
      "name": "Nama Pengguna"
    },
    "app_code": "{APP_CODE}",
    "state": "state-dari-klien",
    "client_account_username": "akun.kerja.terpilih"
  }
}</code></div>
                <p class="small text-muted mt-3 mb-0"><code>sub</code> menggabungkan sumber akun dan ID agar ID sama pada <code>tb_karyawan</code> dan <code>tb_users</code> tidak tertukar. Portal tidak mengirim email, password, departemen, role klien, atau izin modul.</p>
            </article>
            <article class="api-section" id="access" data-api-section>
                <div class="api-kicker">08 · Otorisasi</div><h4 class="font-weight-bold mt-1">Portal mengatur akses aplikasi; klien mengatur isi aplikasinya</h4>
                <div class="row">
                    <div class="col-md-6"><h6 class="font-weight-bold text-success">Dikelola Portal</h6><ul class="small text-muted"><li>Akun boleh/tidak boleh masuk ke app_code tertentu.</li><li>Status klien dan callback terdaftar.</li><li>Penghentian login baru setelah akses dicabut.</li></ul></div>
                    <div class="col-md-6"><h6 class="font-weight-bold text-primary">Dikelola Aplikasi Klien</h6><ul class="small text-muted"><li>Role user, menu/modul, aksi CRUD/approval.</li><li>Cakupan data, departemen, lokasi, dan kepemilikan.</li><li>Session lokal, logout, dan pencabutan session aktif.</li></ul></div>
                </div>
                <div class="api-callout api-callout-danger"><strong>User tanpa penetapan izin SSO ditolak.</strong> Visibilitas tile portal saja tidak mengamankan aplikasi. Klien harus menegakkan otorisasi pada setiap request server-side dengan prinsip deny-by-default.</div>
                <p class="small text-muted mt-3 mb-0">Token memeriksa ulang status user, izin aplikasi, serta satu penetapan akun yang berlaku saat pertukaran. Code kedaluwarsa setelah 120 detik dan hanya boleh ditukar sekali. Session klien yang sudah berjalan tidak otomatis dicabut.</p>
            </article>
            <article class="api-section" id="usage" data-api-section>
                <div class="api-kicker">09 · Tata cara pengguna</div><h4 class="font-weight-bold mt-1">Login ke aplikasi klien melalui Portal</h4>
                <ol class="pl-3 mb-3">
                    <li>User masuk menggunakan username dan password Portal yang aktif.</li>
                    <li>Portal menampilkan kartu hanya untuk integrasi aktif yang memiliki izin eksplisit bagi user.</li>
                    <li>Administrator menetapkan satu akun kerja untuk identitas Portal user. Pilih <strong>Mulai Bekerja</strong> untuk membuka route awal SSO klien.</li>
                    <li>Jika session Portal masih aktif, Portal memeriksa izin aplikasi tanpa meminta login Portal ulang.</li>
                    <li>Klien menerima callback, memeriksa state dan authorization code, lalu menemukan tepat satu akun lokal sesuai client_account_username untuk subject tersebut.</li>
                    <li>Klien membuat session sesuai role lokal. Akses Portal atau akun/role lokal yang tidak valid harus ditolak.</li>
                </ol>
                <div class="api-callout"><strong>Berganti user:</strong> login Portal sebagai user tujuan sebelum memilih kartu aplikasi. Jika Portal masih login sebagai user sebelumnya, logout Portal terlebih dahulu. Logout aplikasi klien tidak mengakhiri session Portal.</div>
            </article>
            <article class="api-section" id="endpoint-test" data-api-section>
                <div class="api-kicker">10 · Pengetesan endpoint</div><h4 class="font-weight-bold mt-1">Uji alur dari browser sampai token</h4>
                <ol class="pl-3">
                    <li>Di browser UAT, buka route start klien, misalnya <code>GET {CLIENT_BASE_URL}/auth/sso/start</code>. Jangan menguji authorize dengan cURL anonim karena endpoint ini memakai session login Portal dan alur redirect browser.</li>
                    <li>Pastikan browser menuju <code>GET {PORTAL_BASE_URL}/sso/authorize</code> dengan <code>client_id</code>, callback HTTPS, state, challenge, dan metode <code>S256</code>.</li>
                    <li>Uji user yang diberi izin. Portal mengembalikan browser ke callback dengan code dan state; klien memvalidasi state sebelum melanjutkan.</li>
                    <li>Pastikan log server klien menunjukkan request backend <code>POST {PORTAL_BASE_URL}/api/sso/token</code> memakai form URL-encoded, secret, code, callback yang sama, dan verifier. Code berlaku 120 detik dan hanya sekali pakai.</li>
                    <li>Respons sukses harus HTTP 200 dan memuat <code>success</code>, <code>data.app_code</code>, <code>data.state</code>, serta klaim user. Pastikan klien lalu membentuk session dan route terlindungi memeriksa role lokal.</li>
                </ol>
                <div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>Skenario</th><th>Hasil yang diharapkan</th></tr></thead><tbody>
                    <tr><td>User Portal diizinkan + akun lokal valid</td><td>Authorize diteruskan, token sukses, session klien terbentuk.</td></tr>
                    <tr><td>User Portal tanpa izin</td><td><code>403 access_denied</code>; tidak ada session klien.</td></tr>
                    <tr><td>Callback, state, atau PKCE salah</td><td><code>400 invalid_request</code> atau callback ditolak; tidak ada session.</td></tr>
                    <tr><td>Secret, verifier, callback salah; code expired/replay; klien nonaktif</td><td><code>400 invalid_grant</code>; login ditolak.</td></tr>
                    <tr><td>Akun lokal tidak ada/nonaktif atau role tidak valid</td><td>Portal dapat sukses, tetapi klien tetap menolak session lokal.</td></tr>
                </tbody></table></div>
                <div class="api-callout api-callout-danger mt-3"><strong>Gunakan environment UAT dan user uji.</strong> Jangan menaruh secret, code, verifier, atau body token di browser, screenshot, tiket, atau log. Untuk uji token manual, jalankan hanya dari backend/terminal aman dengan code baru yang belum pernah ditukar.</div>
            </article>
            <article class="api-section" id="onboarding" data-api-section>
                <div class="api-kicker">11 · Persiapan integrasi</div><h4 class="font-weight-bold mt-1">Data yang harus disiapkan</h4>
                <div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>Data</th><th>Yang perlu disiapkan</th></tr></thead><tbody>
                    <tr><td>Identitas aplikasi</td><td>Nama, pemilik/PIC, dan satu app_code yang didaftarkan Portal.</td></tr><tr><td>URL</td><td>URL peluncuran aplikasi dan callback HTTPS terpisah; callback persis sama pada klien dan Portal.</td></tr><tr><td>Kredensial</td><td>client_id dan client_secret; simpan secret hanya di environment atau secret manager backend.</td></tr><tr><td>Server</td><td>HTTPS dengan sertifikat tepercaya, PHP cURL/HTTP client TLS, session server, dan waktu server tersinkron.</td></tr><tr><td>Akun dan role</td><td>Kebijakan akun lokal, kunci sub, role lokal, izin modul/aksi, aturan user nonaktif.</td></tr><tr><td>Operasional</td><td>Owner/PIC, secret rotation, masa session, logout, log aman, akun uji allow/deny.</td></tr>
                </tbody></table></div>
                <div class="api-checklist mt-3" id="api-checklist"><div class="font-weight-bold mb-1">Checklist kesiapan klien <span class="text-muted font-weight-normal" id="check-progress">0/6</span></div>
                    <label><input type="checkbox" data-check="callback"> Callback HTTPS sudah terdaftar dan cocok persis</label>
                    <label><input type="checkbox" data-check="secret"> Secret tersimpan di backend, tidak ada di Git/browser</label>
                    <label><input type="checkbox" data-check="state"> State dan PKCE diverifikasi server-side</label>
                    <label><input type="checkbox" data-check="identity"> Satu akun lokal ditetapkan admin untuk sub; pencarian memakai client_account_username</label>
                    <label><input type="checkbox" data-check="permission"> Role/modul/data menerapkan deny-by-default</label>
                    <label><input type="checkbox" data-check="uat"> Skenario negatif dan UAT sudah disepakati</label>
                    <div class="progress mt-2" style="height:7px"><div class="progress-bar bg-success" id="check-progress-bar" style="width:0%"></div></div>
                </div>
            </article>
            <article class="api-section" id="client-check" data-api-section>
                <h4 class="font-weight-bold">Cek API klien SSO dan konfirmasi login</h4>
                <p><a href="<?= base_url('admin/portal-sso-check') ?>">Admin → Cek API Klien SSO</a> memeriksa registry, callback, tabel, status user, izin aplikasi, dan penetapan akun. Pilihan user hanya untuk diagnosis; tidak mengganti session admin.</p>
                <h6 class="font-weight-bold">POST <?= html_escape(base_url('api/sso/check')) ?></h6>
                <p>Backend klien mengirim form <code>application/x-www-form-urlencoded</code>: <code>client_id</code>, <code>client_secret</code>, <code>sub</code> (contoh <code>tb_karyawan:123</code>), dan <code>redirect_uri</code> dari konfigurasi efektif klien. Jangan mengirim secret dari browser. Kredensial hanya berlaku untuk aplikasi pemilik secret.</p>
                <pre class="api-code"><code>{
  "success": true,
  "data": {
    "app_code": "kiu_po",
    "sub": "tb_karyawan:123",
    "portal_ready": true,
    "client_account_username": "akun.kerja.terpilih",
    "checks": [{"key": "account_assignment", "status": "pass", "message": "..."}],
    "client_session_verified": false,
    "next_step": "Uji browser dan otorisasi lokal klien."
  }
}</code></pre>
                <p>Contoh <code>checks</code> disingkat. HTTP 200 / success berarti diagnosis berhasil dijalankan, bukan semua pemeriksaan lulus. Baca <code>portal_ready</code> dan seluruh checks. Secret/klien salah menghasilkan 401 invalid_client; sub atau parameter kosong menghasilkan 400 invalid_request; metode selain POST menghasilkan 405.</p>
                <div class="api-callout">Endpoint check hanya membaca konfigurasi dan assignment Portal. Tidak memanggil URL masukan, membuat code, memeriksa database klien, atau membentuk session. Koneksi HTTPS dan kecocokan secret baru teruji jika request benar-benar dikirim dari backend klien. Status modul, akun lokal, role, dan dashboard tetap membutuhkan UAT.</div>
                <h6 class="font-weight-bold mt-3">POST <?= html_escape(base_url('api/sso/login-event')) ?></h6>
                <p>Setelah akun lokal dan role tervalidasi, backend klien mengirim form: <code>client_id</code>, <code>client_secret</code>, <code>sub</code>, <code>account_username</code> sesuai klaim, <code>event_id</code> acak 64 karakter heksadesimal (32 byte), serta opsional <code>source_ip</code> dan <code>user_agent</code>. Respons 200 <code>{"success":true}</code>; 401 invalid_client, 400 invalid_request, atau 409 login_event_rejected bila ditolak. Pengiriman ulang event yang sama untuk identitas/akun yang sama bersifat idempoten.</p>
                <p>Histori adalah konfirmasi dari backend klien yang dipercaya, bukan token untuk login. Jangan menjadikan API check atau login-event sebagai pengganti pertukaran authorization code.</p>
                <h6 class="font-weight-bold mt-3">Kontrak callback klien</h6>
                <ol>
                    <li>Buat state acak dan verifier di server klien; simpan dengan waktu kedaluwarsa pada session dan arahkan browser ke authorize.</li>
                    <li>Callback harus menemukan transaksi SSO yang belum dipakai, belum kedaluwarsa, dan state yang cocok secara konstan. Konsumsi transaksi sebelum menukar code.</li>
                    <li>POST token melalui HTTPS dengan verifikasi sertifikat dan timeout. Periksa HTTP 200, success, app_code yang diharapkan, state yang sama, sub valid, dan client_account_username tidak kosong.</li>
                    <li>Cari username akun yang ditetapkan secara persis. Wajib tepat satu akun lokal aktif dengan level akses yang didukung. Jangan fallback ke username Portal atau membuat akun otomatis. Jika mapping sub lama menunjuk akun berbeda, selaraskan secara terkontrol dengan assignment terbaru.</li>
                    <li>Bersihkan identitas session lama, rotasi ID session, tetapkan user/role lokal yang telah diperiksa, catat histori, dan buka dashboard. Jika gagal, jangan sisakan session login baru.</li>
                    <li>Periksa otorisasi setiap route/modul dan cakupan data. Logout klien hanya mengakhiri session klien.</li>
                </ol>
            </article>
            <article class="api-section" id="module-catalog" data-api-section>
                <p class="api-callout">Katalog modul adalah daftar informasi aplikasi. Keberhasilan SSO atau API check tidak membuktikan otorisasi setiap modul; role, aksi, dan cakupan data wajib diuji pada backend klien.</p>
                <div class="api-kicker">Katalog Module Klien</div><h4 class="font-weight-bold mt-1">Klien dapat mengelola daftar module aplikasinya</h4>
                <p>Admin mengelola katalog dari tombol <strong>Detail Module</strong> pada kartu aplikasi di halaman Akses Aplikasi. Klien dapat mengelola katalog aplikasi miliknya melalui endpoint berikut menggunakan HTTPS.</p>
                <div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>Method</th><th>Route</th><th>Fungsi</th></tr></thead><tbody>
                    <tr><td>GET</td><td><code>/api/client/modules</code></td><td>Daftar module aplikasi klien, termasuk status nonaktif.</td></tr>
                    <tr><td>GET</td><td><code>/api/client/modules/{module_code}</code></td><td>Detail module milik klien.</td></tr>
                    <tr><td>POST</td><td><code>/api/client/modules</code></td><td>Tambah module.</td></tr>
                    <tr><td>PUT/PATCH</td><td><code>/api/client/modules/{module_code}</code></td><td>Ubah module.</td></tr>
                    <tr><td>DELETE</td><td><code>/api/client/modules/{module_code}</code></td><td>Hapus module secara permanen.</td></tr>
                </tbody></table></div>
                <div class="api-callout api-callout-danger"><strong>Autentikasi:</strong> HTTP Basic, username berupa <code>app_code</code> dan password berupa client secret. Kirim melalui header <code>Authorization</code> hanya lewat HTTPS. Server mengikat semua operasi ke aplikasi dari kredensial tersebut; klien tidak dapat menentukan atau mengubah <code>app_code</code> tujuan.</div>
                <p class="small text-muted mb-0">Body JSON untuk POST/PUT/PATCH menerima <code>module_code</code>, <code>module_name</code>, <code>description</code>, <code>module_url</code>, <code>sort_order</code>, dan <code>is_active</code>. Dokumentasi lengkap dan contoh body tersedia di <code>docs/development/portal-app-module-catalog-20261024.md</code>.</p>
            </article>
            <article class="api-section" id="database" data-api-section>
                <div class="api-kicker">12 · Prasyarat database</div><h4 class="font-weight-bold mt-1">Pisahkan data Portal dan aplikasi klien</h4>
                <ul class="mb-2">
                    <li><strong>Database Portal:</strong> jika belum ada, terapkan <code>docs/database/portal_sso_20261022.sql</code> (tabel klien/kode), <code>portal_sso_secret_reveal_20261022.sql</code> (kolom secret terenkripsi), <code>portal_sso_launch_uri_20261024.sql</code> (URL peluncuran), <code>portal_sso_client_account_20261029.sql</code>, <code>portal_sso_client_account_assignment_20261030.sql</code>, dan <code>portal_sso_login_history_20261030.sql</code>. Hak user memakai tabel akses Portal yang sudah ada; tidak ada migration akses baru pada panduan ini.</li>
                    <li><strong>Database klien:</strong> siapkan tabel user lokal, role/permission aplikasi, dan tabel mapping identitas yang menyimpan <code>auth_source</code>, <code>external_sub</code>, <code>local_user_id</code>, serta waktu pembuatan/perubahan.</li>
                </ul>
                <div class="api-callout"><strong>Constraint minimal klien:</strong> <code>external_sub</code> unik per provider, foreign key <code>local_user_id</code> ke user lokal, dan aturan unik tambahan jika satu user lokal hanya boleh terhubung ke satu identitas Portal. Nama tabel/kolom dan DDL klien mengikuti skema aplikasi masing-masing. Jangan menyimpan client_secret atau authorization code di tabel user.</div>
                <div class="small text-muted mt-3">Backup database sebelum migration. Pastikan semua tabel/kolom Portal di atas tersedia di environment target. Perubahan alur session klien tidak menambah skema Portal; kebutuhan mapping klien adalah bagian implementasi klien. Rincian field dan aturan mapping ada di <code>docs/database/portal-sso-prasyarat-database-klien-20261024.md</code>.</div>
            </article>
            <article class="api-section mb-0" id="errors" data-api-section>
                <div class="api-kicker">13 · Error dan batasan</div><h4 class="font-weight-bold mt-1">Respons yang perlu ditangani klien</h4>
                <div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>HTTP</th><th>Error</th><th>Tindakan</th></tr></thead><tbody>
                    <tr><td>400</td><td><code>invalid_request</code></td><td>Periksa parameter wajib dan format PKCE.</td></tr><tr><td>400</td><td><code>invalid_grant</code></td><td>Code, secret, callback, verifier, masa berlaku, atau status klien tidak cocok.</td></tr><tr><td>401</td><td><code>invalid_client</code></td><td>Secret salah atau klien nonaktif pada check/login-event.</td></tr><tr><td>403</td><td><code>account_not_assigned</code></td><td>Tetapkan tepat satu akun kerja; katalog kosong juga ditolak.</td></tr><tr><td>409</td><td><code>login_event_rejected</code></td><td>Histori belum siap atau akun event tidak cocok dengan assignment.</td></tr><tr><td>403</td><td><code>access_denied</code></td><td>Admin portal perlu meninjau izin user untuk aplikasi.</td></tr><tr><td>405</td><td><code>method_not_allowed</code></td><td>Gunakan GET untuk authorize dan POST untuk token.</td></tr><tr><td>503</td><td><code>temporarily_unavailable</code></td><td>Periksa layanan portal/database dan coba kembali.</td></tr>
                </tbody></table></div>
                <div class="api-callout api-callout-danger mt-3"><strong>Belum tersedia:</strong> API provisioning/deprovisioning, sinkronisasi role/menu, token akses/refresh, introspection/revoke, OIDC discovery/JWKS, userinfo, logout lintas aplikasi, dan penghentian session klien yang sudah aktif.</div>
            </article>
        </div>
    </div>
</section>
<script>
$(function(){
    const checklistKey='karisma-api-onboarding-v1';
    const boxes=$('#api-checklist [data-check]');
    let saved={};
    try{saved=JSON.parse(localStorage.getItem(checklistKey)||'{}')||{};}catch(error){saved={};}
    boxes.each(function(){this.checked=!!saved[$(this).data('check')];});
    function updateChecklist(){let done=0;boxes.each(function(){if(this.checked){done++;saved[$(this).data('check')]=true;}else{delete saved[$(this).data('check')];}});$('#check-progress').text(done+'/'+boxes.length);$('#check-progress-bar').css('width',(boxes.length?done/boxes.length*100:0)+'%');try{localStorage.setItem(checklistKey,JSON.stringify(saved));}catch(error){}}
    boxes.on('change',updateChecklist);updateChecklist();
    $('#api-search').on('input',function(){const query=$(this).val().trim().toLowerCase();let shown=0;$('.api-section').each(function(){const match=!query||$(this).text().toLowerCase().includes(query);$(this).toggle(match);if(match)shown++;});$('#api-search-result').text(query?(shown+' bagian panduan cocok dengan pencarian.'):'Panduan integrasi dan referensi endpoint portal.');});
    $(document).on('click','.api-copy',async function(){const code=document.getElementById($(this).data('copy'));if(!code)return;const text=code.innerText;try{await navigator.clipboard.writeText(text);$(this).html('<i class="fas fa-check mr-1"></i>Tersalin');setTimeout(()=>$(this).html('<i class="fas fa-copy mr-1"></i>Salin'),1400);}catch(error){const area=document.createElement('textarea');area.value=text;document.body.appendChild(area);area.select();document.execCommand('copy');area.remove();$(this).text('Tersalin');setTimeout(()=>$(this).html('<i class="fas fa-copy mr-1"></i>Salin'),1400);}});
    $('.api-nav a').on('click',function(){$('.api-nav a').removeClass('active');$(this).addClass('active');});
});
</script>

