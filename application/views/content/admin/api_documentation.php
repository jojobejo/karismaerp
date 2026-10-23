<style>
.api-doc{--ad-ink:#14233b;--ad-muted:#6c7a90;--ad-line:#e3e9f1;--ad-teal:#176b78;color:var(--ad-ink)}.api-hero{position:relative;overflow:hidden;border-radius:20px;padding:26px;background:linear-gradient(120deg,#10283d,#145d69 65%,#1c8d83);color:#fff;box-shadow:0 16px 34px rgba(16,40,61,.18)}.api-hero:after{content:"";position:absolute;width:230px;height:230px;right:-60px;top:-95px;border:1px solid rgba(255,255,255,.16);border-radius:50%;box-shadow:0 0 0 28px rgba(255,255,255,.04),0 0 0 58px rgba(255,255,255,.03)}.api-card,.api-section{background:#fff;border:1px solid var(--ad-line);border-radius:16px;box-shadow:0 8px 22px rgba(19,35,59,.045)}.api-card{padding:17px;height:100%}.api-section{padding:22px;margin-bottom:16px;scroll-margin-top:18px}.api-kicker{font-size:11px;text-transform:uppercase;letter-spacing:.1em;color:var(--ad-muted);font-weight:700}.api-metric{font-size:22px;font-weight:750}.api-mark{height:42px;width:42px;border-radius:13px;display:grid;place-items:center;background:#e8f4f4;color:var(--ad-teal);font-size:17px}.api-nav{position:sticky;top:14px}.api-nav a{display:block;padding:9px 11px;border-radius:9px;color:#45556c;text-decoration:none;font-size:13px}.api-nav a:hover,.api-nav a.active{background:#eaf4f4;color:#135d66;font-weight:700}.api-code{position:relative;background:#101c2d;color:#d8e5f7;border-radius:12px;padding:16px;overflow:auto;font:12px/1.65 Consolas,Monaco,monospace;white-space:pre}.api-code code{color:inherit}.api-copy{position:absolute;top:9px;right:9px}.api-badge{display:inline-flex;padding:4px 8px;border-radius:99px;font-size:11px;font-weight:700}.api-badge-green{background:#e7f6ec;color:#187443}.api-badge-amber{background:#fff5db;color:#8a6410}.api-step{display:flex;gap:12px;padding:14px;border:1px solid var(--ad-line);border-radius:12px;height:100%}.api-step-num{flex:0 0 30px;height:30px;border-radius:10px;background:#e9f3f4;color:var(--ad-teal);display:grid;place-items:center;font-weight:800}.api-checklist label{display:flex;gap:10px;padding:11px;border-bottom:1px solid #edf1f5;cursor:pointer}.api-checklist label:last-child{border-bottom:0}.api-endpoint{font-family:Consolas,monospace;font-size:12px;overflow-wrap:anywhere}.api-table th{white-space:nowrap;background:#f5f8fb}.api-callout{border-left:4px solid #d59b23;background:#fff9e8;padding:14px 16px;border-radius:0 12px 12px 0}.api-callout-danger{border-color:#cf5147;background:#fff3f2}.api-result{min-height:16px;color:var(--ad-muted);font-size:12px}
@media(max-width:767.98px){.api-hero{padding:20px}.api-section{padding:16px}.api-nav{position:static;display:flex;overflow:auto;white-space:nowrap}.api-nav a{display:inline-block}}
</style>
<section class="content p-3 p-md-4 api-doc">
    <div class="api-hero mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div><div class="small text-uppercase font-weight-bold mb-1" style="letter-spacing:.12em;opacity:.72">Developer guide · KARISMA ERP</div><h2 class="font-weight-bold mb-1">Dokumentasi API Integrasi</h2><div style="opacity:.8">Referensi API SSO Portal, master barang, data Pre-PO, dan stok logistik yang tersedia di project.</div></div>
        <div class="d-flex flex-wrap mt-3 mt-md-0"><a class="btn btn-outline-light font-weight-bold mr-2 mb-1" href="<?= base_url('portal') ?>"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Portal</a><a class="btn btn-light font-weight-bold mb-1" href="<?= base_url('admin/portal-sso-clients') ?>"><i class="fas fa-plug mr-1"></i> Integrasi SSO</a></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4 mb-2"><div class="api-card d-flex justify-content-between align-items-center"><div><div class="api-kicker">Endpoint SSO klien</div><div class="api-metric">2 endpoint</div></div><span class="api-mark"><i class="fas fa-exchange-alt"></i></span></div></div>
        <div class="col-md-4 mb-2"><div class="api-card d-flex justify-content-between align-items-center"><div><div class="api-kicker">Metode autentikasi</div><div class="api-metric">Code + PKCE</div></div><span class="api-mark"><i class="fas fa-key"></i></span></div></div>
        <div class="col-md-4 mb-2"><div class="api-card d-flex justify-content-between align-items-center"><div><div class="api-kicker">Masa berlaku code</div><div class="api-metric">120 detik · sekali pakai</div></div><span class="api-mark"><i class="fas fa-hourglass-half"></i></span></div></div>
    </div>
    <div class="api-card mb-3">
        <label class="api-kicker mb-2" for="api-search">Cari dalam panduan</label>
        <input class="form-control" id="api-search" type="search" placeholder="Contoh: callback, client_secret, hak akses, PKCE">
        <div class="api-result mt-2" id="api-search-result">Panduan integrasi dan referensi endpoint portal.</div>
    </div>
    <div class="row">
        <div class="col-lg-3 mb-3"><nav class="api-card api-nav" aria-label="Daftar isi"><div class="api-kicker px-2 mb-2">Daftar isi</div><a href="#overview">Prinsip integrasi</a><a href="#admin-setup">Pengaturan admin</a><a href="#flow">Alur SSO</a><a href="#endpoints">Endpoint API</a><a href="#modules">API per modul ERP</a><a href="#claims">Respons identitas</a><a href="#access">Hak akses</a><a href="#onboarding">Data onboarding</a><a href="#errors">Error dan batas</a></nav></div>
        <div class="col-lg-9">
            <article class="api-section" id="overview" data-api-section>
                <div class="api-kicker">01 · Model keamanan</div><h4 class="font-weight-bold mt-1">Portal memverifikasi identitas; klien membuat session sendiri</h4>
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
                    <div class="col-md-6 mb-2"><div class="api-step"><span class="api-step-num">1</span><div><strong>Daftarkan callback</strong><div class="small text-muted">Buka Integrasi SSO, pilih app_code, masukkan callback HTTPS klien.</div></div></div></div>
                    <div class="col-md-6 mb-2"><div class="api-step"><span class="api-step-num">2</span><div><strong>Simpan secret</strong><div class="small text-muted">Simpan pada secret manager server klien. Admin portal dapat membukanya melalui ikon key dengan PIN keamanan.</div></div></div></div>
                    <div class="col-md-6 mb-2"><div class="api-step"><span class="api-step-num">3</span><div><strong>Berikan izin aplikasi</strong><div class="small text-muted">Pada Akses Aplikasi, pilih user lalu simpan aplikasi yang boleh digunakan.</div></div></div></div>
                    <div class="col-md-6 mb-2"><div class="api-step"><span class="api-step-num">4</span><div><strong>Uji sebelum rilis</strong><div class="small text-muted">Uji user diizinkan/ditolak, state, PKCE, code replay, callback, dan user nonaktif.</div></div></div></div>
                </div>
                <div class="mt-3"><a class="btn btn-sm btn-outline-primary mr-2" href="<?= base_url('admin/portal-sso-clients') ?>"><i class="fas fa-plug mr-1"></i> Buka Integrasi SSO</a><a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/portal-app-access') ?>"><i class="fas fa-user-shield mr-1"></i> Buka Akses Aplikasi</a></div>
            </article>
            <article class="api-section" id="flow" data-api-section>
                <div class="api-kicker">03 · SSO client flow</div><h4 class="font-weight-bold mt-1">Authorization code dengan PKCE S256</h4>
                <ol class="pl-3">
                    <li>Backend klien membuat <code>state</code> dan <code>code_verifier</code> acak, lalu menyimpan sementara di session server klien.</li>
                    <li>Klien menghitung <code>code_challenge = BASE64URL(SHA256(code_verifier))</code> tanpa padding dan mengarahkan browser ke portal.</li>
                    <li>Portal memvalidasi klien, callback persis, PKCE, dan izin user. Jika user belum login, portal login dahulu lalu melanjutkan permintaan.</li>
                    <li>Portal mengarahkan callback klien dengan <code>code</code> dan <code>state</code>. Klien wajib cocokkan state dengan session miliknya.</li>
                    <li>Backend klien menukar code ke endpoint token dengan client secret dan code verifier.</li>
                    <li>Klien menghubungkan akun berdasarkan <code>sub</code>, menetapkan role lokal, dan membuat session lokal.</li>
                </ol>
                <div class="api-callout"><strong>Jangan kirim client_secret atau code_verifier ke browser.</strong> Code hanya dapat digunakan sekali dan berlaku 120 detik.</div>
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
                <div class="api-kicker">05 · API per modul ERP</div><h4 class="font-weight-bold mt-1">Endpoint operasional yang tersedia</h4>
                <p class="text-muted">Project juga menyediakan API baca untuk katalog master barang, data Pre-PO, dan stok per gudang. Endpoint tersebut terpisah dari SSO dan belum menggunakan autentikasi client SSO.</p>
                <div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>Modul</th><th>Metode dan route</th><th>Parameter</th><th>Format respons</th></tr></thead><tbody>
                    <tr><td>Master Barang / Purchasing</td><td><span class="api-badge api-badge-green">GET</span><br><code><?= html_escape(base_url('api/v1/barang')) ?></code></td><td><code>search</code>, <code>kode_barang</code>, <code>kd_suplier</code>, <code>limit</code> (maks. 500), <code>offset</code></td><td>JSON <code>status</code>, <code>total_data</code>, <code>total_filter</code>, paging, dan daftar barang.</td></tr>
                    <tr><td>Purchase Order / Pre-PO</td><td><span class="api-badge api-badge-green">GET</span><br><code><?= html_escape(base_url('api/v1/pre-po')) ?></code></td><td>Tidak ada. Mengambil baris dengan tanggal transaksi terisi.</td><td>JSON <code>status</code>, <code>total_data</code>, dan detail PO beserta histori diskon.</td></tr>
                    <tr><td>Logistik / Stok per Gudang</td><td><span class="api-badge api-badge-green">GET</span><br><code><?= html_escape(base_url('ics/api/stock_per_gudang')) ?></code></td><td><code>gudang</code> (opsional)</td><td>JSON <code>status</code> dan <code>data</code> stok dari laporan stok per gudang.</td></tr>
                </tbody></table></div>
                <div class="api-callout api-callout-danger mt-3"><strong>Batas keamanan API operasional:</strong> route master barang, Pre-PO, dan stok ini saat ini tidak memeriksa client_id/client_secret atau session user. Endpoint barang dan Pre-PO juga mengizinkan CORS <code>*</code>. Jangan menganggap login SSO melindungi endpoint ini; batasi akses jaringan atau tambahkan autentikasi dan otorisasi sebelum mengekspos data ke pihak luar.</div>
                <h6 class="font-weight-bold mt-3">Modul ERP lain</h6><p class="small text-muted mb-0">User Management, Master Organisasi, Jobdesk, Akses Level, Master Menu, Akses Aplikasi, dan Integrasi SSO saat ini dikelola melalui halaman ERP yang memakai session dan hak admin. Endpoint AJAX internalnya bukan kontrak API untuk aplikasi klien. Modul Sales, Keuangan, Stock Opname, HRD, Inventaris, Purchasing, dan Logistik juga tidak otomatis menjadi API publik hanya karena memiliki route/controller.</p>
            </article>
            <article class="api-section" id="claims" data-api-section>
                <div class="api-kicker">06 · Identitas pengguna</div><h4 class="font-weight-bold mt-1">Klaim yang dikembalikan</h4>
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
    "app_code": "kiu_po",
    "state": "state-dari-klien"
  }
}</code></div>
                <p class="small text-muted mt-3 mb-0"><code>sub</code> menggabungkan sumber akun dan ID agar ID sama pada <code>tb_karyawan</code> dan <code>tb_users</code> tidak tertukar. Portal tidak mengirim email, password, departemen, role klien, atau izin modul.</p>
            </article>
            <article class="api-section" id="access" data-api-section>
                <div class="api-kicker">07 · Otorisasi</div><h4 class="font-weight-bold mt-1">Portal mengatur akses aplikasi; klien mengatur isi aplikasinya</h4>
                <div class="row">
                    <div class="col-md-6"><h6 class="font-weight-bold text-success">Dikelola Portal</h6><ul class="small text-muted"><li>Akun boleh/tidak boleh masuk ke app_code tertentu.</li><li>Status klien dan callback terdaftar.</li><li>Penghentian login baru setelah akses dicabut.</li></ul></div>
                    <div class="col-md-6"><h6 class="font-weight-bold text-primary">Dikelola Aplikasi Klien</h6><ul class="small text-muted"><li>Role user, menu/modul, aksi CRUD/approval.</li><li>Cakupan data, departemen, lokasi, dan kepemilikan.</li><li>Session lokal, logout, dan pencabutan session aktif.</li></ul></div>
                </div>
                <div class="api-callout api-callout-danger"><strong>User tanpa penetapan izin SSO ditolak.</strong> Visibilitas tile portal saja tidak mengamankan aplikasi. Klien harus menegakkan otorisasi pada setiap request server-side dengan prinsip deny-by-default.</div>
                <p class="small text-muted mt-3 mb-0">Authorization code yang sudah terbit masih dapat ditukar sampai kedaluwarsa (maksimal 120 detik). Session klien yang sudah berjalan tidak otomatis dicabut.</p>
            </article>
            <article class="api-section" id="onboarding" data-api-section>
                <div class="api-kicker">08 · Persiapan integrasi</div><h4 class="font-weight-bold mt-1">Data onboarding yang harus disepakati</h4>
                <div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>Data</th><th>Yang perlu disiapkan</th></tr></thead><tbody>
                    <tr><td>Identitas aplikasi</td><td>Nama, pemilik bisnis/PIC, dan app_code dari katalog portal.</td></tr><tr><td>Environment</td><td>Domain development/UAT/production dengan HTTPS.</td></tr><tr><td>Callback</td><td>Route backend yang stabil; daftarkan URL secara persis di portal.</td></tr><tr><td>Kredensial</td><td>client_id dan client_secret; secret disimpan dalam secret manager backend.</td></tr><tr><td>Pemetaan akun</td><td>Pakai sub sebagai external key; putuskan auto-provision atau akun lokal harus dibuat lebih dulu.</td></tr><tr><td>Role &amp; izin</td><td>Matriks role → modul → aksi → cakupan data; konfigurasi dan enforcement pada klien.</td></tr><tr><td>Siklus hidup</td><td>Aturan user nonaktif, pencabutan akses, session expiry, logout, dan rotasi secret.</td></tr><tr><td>UAT</td><td>User allow/deny/nonaktif, state mismatch, callback/verifier salah, code expired/replay, klien nonaktif.</td></tr>
                </tbody></table></div>
                <div class="api-checklist mt-3" id="api-checklist"><div class="font-weight-bold mb-1">Checklist kesiapan klien <span class="text-muted font-weight-normal" id="check-progress">0/6</span></div>
                    <label><input type="checkbox" data-check="callback"> Callback HTTPS sudah terdaftar dan cocok persis</label>
                    <label><input type="checkbox" data-check="secret"> Secret tersimpan di backend, tidak ada di Git/browser</label>
                    <label><input type="checkbox" data-check="state"> State dan PKCE diverifikasi server-side</label>
                    <label><input type="checkbox" data-check="identity"> Akun lokal dipetakan dengan sub</label>
                    <label><input type="checkbox" data-check="permission"> Role/modul/data menerapkan deny-by-default</label>
                    <label><input type="checkbox" data-check="uat"> Skenario negatif dan UAT sudah disepakati</label>
                    <div class="progress mt-2" style="height:7px"><div class="progress-bar bg-success" id="check-progress-bar" style="width:0%"></div></div>
                </div>
            </article>
            <article class="api-section mb-0" id="errors" data-api-section>
                <div class="api-kicker">09 · Error dan batasan</div><h4 class="font-weight-bold mt-1">Respons yang perlu ditangani klien</h4>
                <div class="table-responsive"><table class="table table-bordered table-sm api-table"><thead><tr><th>HTTP</th><th>Error</th><th>Tindakan</th></tr></thead><tbody>
                    <tr><td>400</td><td><code>invalid_request</code></td><td>Periksa parameter wajib dan format PKCE.</td></tr><tr><td>400</td><td><code>invalid_grant</code></td><td>Code, secret, callback, verifier, masa berlaku, atau status klien tidak cocok.</td></tr><tr><td>403</td><td><code>access_denied</code></td><td>Admin portal perlu meninjau izin user untuk aplikasi.</td></tr><tr><td>405</td><td><code>method_not_allowed</code></td><td>Gunakan GET untuk authorize dan POST untuk token.</td></tr><tr><td>503</td><td><code>temporarily_unavailable</code></td><td>Periksa layanan portal/database dan coba kembali.</td></tr>
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

