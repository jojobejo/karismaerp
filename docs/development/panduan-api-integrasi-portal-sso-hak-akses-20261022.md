# Panduan API Integrasi Portal: SSO dan Hak Akses

## 1. Tujuan dan cakupan

Panduan ini menjelaskan kontrak integrasi yang saat ini tersedia di portal KARISMA ERP untuk login aplikasi klien dan pengaturan izin masuk pengguna per aplikasi.

Portal tidak membagikan cookie/session portal atau password pengguna. Backend aplikasi klien menukar authorization code dengan portal, memverifikasi identitas, lalu membuat session lokal. Protokol ini adalah integrasi internal berbasis authorization code + PKCE S256; ini belum implementasi OpenID Connect penuh.

Hak akses yang dikelola portal saat ini adalah izin masuk ke aplikasi klien. Role, menu/modul, aksi, dan cakupan data di dalam aplikasi tetap harus dikelola dan ditegakkan oleh aplikasi klien. Portal belum mengirim role/permission klien dan belum menyediakan API provisioning user atau role ke aplikasi klien.

## 2. Endpoint Portal yang dipakai klien

| Fungsi | Route | Pemakai |
|---|---|---|
| Memulai autentikasi dan meminta authorization code | GET /sso/authorize | Browser pengguna, atas nama aplikasi klien |
| Menukar code menjadi klaim identitas | POST /api/sso/token | Backend aplikasi klien saja |
| Memulai autentikasi | GET /sso/authorize | Browser pengguna melalui route start klien |
| Menukar code menjadi klaim identitas | POST /api/sso/token | Backend aplikasi klien saja |

Gunakan base URL portal yang diberikan tim operasional. Portal dan aplikasi klien wajib memakai HTTPS dengan sertifikat yang dipercaya. Route admin Portal hanya untuk administrator; route tersebut bukan kontrak integrasi klien. Portal tidak memanggil API bisnis internal klien.

## 3. Data aplikasi dan URL

Administrator memilih satu `app_code` resmi untuk klien. `client_id` sama dengan `app_code`. Satu klien memiliki URL peluncuran aplikasi (`launch_uri`) dan satu callback HTTPS (`redirect_uri`); keduanya berbeda dan callback harus cocok persis pada authorize serta token.

## 4. Tata cara penggunaan oleh administrator portal

1. Pastikan schema SSO sudah diterapkan sesuai `docs/database/portal_sso_20261022.md`.
2. Pastikan user portal sudah tersedia dan aktif pada sumber akun yang benar (`tb_karyawan` atau `tb_users`).
3. Buka **Integrasi SSO**, pilih `app_code`, isi URL peluncuran dan callback HTTPS, lalu simpan. URL peluncuran membuka aplikasi klien; callback menerima `code` dan `state`.
4. Salin secret ke secret manager backend klien. Admin dapat menampilkan ulang secret melalui ikon key setelah PIN diverifikasi; secret tetap tidak boleh disimpan pada browser klien, file konfigurasi publik, atau Git.
5. Buka **Akses Aplikasi**, pilih user portal, atur aplikasi yang diizinkan, lalu tekan **Simpan akses**. Tanpa konfigurasi akses yang disimpan, request SSO user ditolak.
6. Tim aplikasi klien menyiapkan konfigurasi backend, route start/callback, pemetaan subject lokal, session, role, dan otorisasi internal.
7. Jalankan UAT bersama user yang diizinkan dan ditolak sebelum membuka akses production.

Klien menyimpan konfigurasi seperti `PORTAL_BASE_URL`, `PORTAL_CLIENT_ID`, `PORTAL_CLIENT_SECRET`, dan `PORTAL_CALLBACK_URL` pada environment server atau secret manager. Nama environment di atas contoh; jangan commit secret. Pastikan PHP cURL/HTTP client memvalidasi CA dan nama host HTTPS.

Untuk membuka tampilan ulang secret, terapkan migration `docs/database/portal_sso_secret_reveal_20261022.sql` dan atur `PORTAL_SSO_ENCRYPTION_KEY` yang persisten pada environment server. PIN awal `898989` dapat diganti dengan `PORTAL_SSO_KEY_PIN`. Klien lama sebelum migration tambahan perlu dirotasi karena hanya memiliki hash dan tidak bisa dipulihkan. Jika secret bocor, lakukan rotasi dan perbarui server klien. Menonaktifkan klien menghentikan login baru dan pertukaran code, tetapi tidak mematikan session lokal yang sudah dimiliki pengguna.

## 5. Alur login aplikasi klien

### 5.1 Siapkan request di backend klien

Saat user memulai SSO, backend klien membuat:
- state acak dengan panjang 16–512 karakter;
- code_verifier acak 43–128 karakter dari A-Z, a-z, 0-9, titik, underscore, tilde, atau tanda minus;
- code_challenge = BASE64URL(SHA256(code_verifier)), tanpa padding. Hasilnya 43 karakter;
- simpan state dan code_verifier sementara di session server klien.

Setiap kartu/URL peluncuran menuju route awal klien, bukan callback. Untuk browser yang dapat berganti akun Portal, klien harus memulai SSO ulang atau memastikan session lokal tidak mempertahankan identitas berbeda. Hapus/rotasi session ID lama sebelum menetapkan identitas klien yang baru.

Contoh generator PHP:

```php
$base64Url = function ($value) {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
};
$state = $base64Url(random_bytes(32));
$codeVerifier = $base64Url(random_bytes(32));
$codeChallenge = $base64Url(hash('sha256', $codeVerifier, true));
$_SESSION['portal_sso'] = [
    'state' => $state,
    'code_verifier' => $codeVerifier,
];
```

Jangan menyimpan code_verifier di URL atau mengirim client_secret ke browser.

Arahkan browser ke portal dengan parameter berikut:

```text
GET {PORTAL_BASE_URL}/sso/authorize
    ?client_id={APP_CODE}
    &redirect_uri={URL_CALLBACK_TERDAFTAR}
    &state={STATE}
    &code_challenge={CODE_CHALLENGE}
    &code_challenge_method=S256
```

Semua parameter wajib. Portal memeriksa klien aktif, callback terdaftar secara persis, format PKCE, dan izin pengguna ke aplikasi. Jika user belum login portal, portal meminta login lalu melanjutkan request authorize tervalidasi.

Jika diizinkan, portal mengarahkan browser ke callback:

```text
{URL_CALLBACK_TERDAFTAR}?code={AUTHORIZATION_CODE}&state={STATE}
```

### 5.2 Validasi callback

Callback aplikasi klien wajib:
1. Membandingkan state yang kembali dengan nilai di session server klien menggunakan perbandingan aman seperti hash_equals().
2. Menolak callback jika state tidak tersedia atau nilainya berbeda.
3. Menukar code dari backend klien ke portal. Jangan menukar code melalui JavaScript/browser.

### 5.3 Tukar authorization code

Backend klien mengirim HTTPS POST ke {PORTAL_BASE_URL}/api/sso/token dengan Content-Type application/x-www-form-urlencoded.

| Field | Isi |
|---|---|
| client_id | app_code yang didaftarkan |
| client_secret | Secret server klien, simpan di secret manager/environment server |
| code | Code dari callback portal |
| redirect_uri | Callback HTTPS yang didaftarkan; harus sama persis |
| code_verifier | Verifier yang disimpan sebelum redirect |

Contoh cURL dari backend:

```bash
curl --fail-with-body --silent --show-error \
  -X POST "${PORTAL_BASE_URL}/api/sso/token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-urlencode "client_id=${PORTAL_CLIENT_ID}" \
  --data-urlencode "client_secret=${PORTAL_CLIENT_SECRET}" \
  --data-urlencode "code=${AUTHORIZATION_CODE}" \
  --data-urlencode "redirect_uri=${PORTAL_CALLBACK_URL}" \
  --data-urlencode "code_verifier=${CODE_VERIFIER}"
```

Respons sukses HTTP 200:

```json
{
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
    "state": "state-yang-dikirim-klien"
  }
}
```

sub menggabungkan sumber akun dan ID karena tb_karyawan dan tb_users dapat memiliki ID yang sama. Kaitkan akun lokal ke sub, bukan hanya username. name adalah nama tampilan. Portal tidak mengirim email, password, departemen, role klien, atau daftar izin modul.

Code berlaku 120 detik dan hanya dapat ditukar sekali. Code salah/terpakai/kedaluwarsa, secret salah, callback berbeda, verifier PKCE salah, atau klien nonaktif menghasilkan HTTP 400 invalid_grant.

## 6. Izin aplikasi pengguna

Administrator portal membuka Akses Aplikasi, memilih akun, lalu menyimpan aplikasi yang boleh dimasuki. Sumber akun (tb_karyawan atau tb_users) dibedakan agar identitas tidak tertukar. Endpoint authorize memeriksa izin sebelum membuat code.

- User tanpa penetapan akses ditolak oleh SSO sampai admin menyimpan konfigurasi.
- Penyimpanan baru menulis pilihan allow/deny untuk setiap aplikasi pada katalog.
- Konfigurasi lama yang hanya memiliki baris penolakan tetap berlaku: aplikasi tanpa baris penolakan dianggap diizinkan jika user mempunyai konfigurasi lama.
- Perubahan izin menghentikan penerbitan code pada request authorize berikutnya. Code yang sudah diterbitkan masih bisa ditukar hingga kedaluwarsa, maksimal 120 detik.
- Tile yang terlihat pada portal bukan pengganti pemeriksaan izin server. Aplikasi klien harus mengaktifkan integrasi SSO dan melindungi route/data lokalnya.

### Tanggung jawab aplikasi klien

Setelah menerima identitas yang sah, aplikasi klien membuat atau memperbarui user lokal, menetapkan role lokal sesuai kebijakan bisnis, membuat session lokal, dan memeriksa izin server-side pada setiap request. Terapkan deny-by-default untuk user lokal tanpa role/permission. Jangan memberikan role administrator berdasarkan username, nama, atau fakta bahwa login berasal dari portal.

Jika role/menu/modul aplikasi klien harus dikelola dari portal, dibutuhkan kontrak fase berikutnya: katalog modul/action setiap klien, API sinkronisasi/admin dengan autentikasi service-to-service, skema mapping role/permission per app, aturan perubahan/pencabutan, audit, serta propagasi ke session aktif. Kemampuan ini belum tersedia pada API portal sekarang.

## 7. Data onboarding aplikasi klien

| Data/keputusan | Penanggung jawab | Ketentuan |
|---|---|---|
| Nama dan pemilik bisnis aplikasi | Pemilik aplikasi | Nama modul, unit pemilik, PIC operasional |
| app_code | Admin portal | Pilih kode yang tersedia dan daftarkan pada menu Integrasi SSO |
| Domain environment | Tim klien/infra | URL development/UAT dan production; HTTPS |
| URL peluncuran | Tim klien | Halaman awal aplikasi; HTTPS |
| Callback URL | Tim klien | Route callback backend stabil; didaftarkan persis |
| client_id | Portal | Sama dengan app_code |
| client_secret | Portal dan tim klien | Ditampilkan saat registrasi/rotasi atau melalui ikon key dengan PIN; simpan di secret manager, jangan commit |
| Kunci akun lokal | Tim klien | Simpan `sub` sebagai external identity key; tentukan auto-provision atau akun lokal pradaftar |
| Pemetaan profil | Pemilik user dan tim klien | Tentukan penggunaan username dan name; atribut lain tidak tersedia |
| Matriks role dan modul | Pemilik bisnis aplikasi | Role → modul → aksi → cakupan data; dikelola di aplikasi klien |
| Kebijakan user nonaktif/keluar | Pemilik bisnis dan tim klien | Dampak pencabutan akses portal pada akun/session lokal |
| Session lokal | Tim klien | Umur idle/absolut, logout, rotasi session ID, re-auth aksi sensitif |
| PIC keamanan/support | Kedua tim | Respons insiden, rotasi secret, dan prosedur downtime |
| Akun UAT dan skenario | QA/pemilik bisnis | User diizinkan/ditolak/nonaktif, role berbeda, uji state/PKCE/callback |

### Checklist sebelum production
- Callback production terdaftar dan cocok persis.
- Akses user pilot disimpan eksplisit pada menu portal.
- Klien menguji state dan PKCE S256; secret hanya ada di backend.
- Uji code expired/replay, user tanpa izin, klien nonaktif, secret/callback/verifier salah.
- Role lokal minimal dan pemeriksaan izin server-side diuji untuk endpoint/data penting.
- Tim menyepakati logout, pencabutan akses, user nonaktif, dan rotasi secret.
- Password portal, cookie session, code, verifier, dan secret tidak masuk log.

## 8. Modul dan route yang harus disiapkan klien

| Modul klien | Tanggung jawab |
|---|---|
| Konfigurasi dan koneksi | HTTPS, sertifikat tepercaya, HTTP client/cURL, app_code, secret server, base URL Portal, callback. |
| Adapter SSO | Route start membuat state/verifier; callback memvalidasi state dan menukar code dari backend. |
| Identity mapping | Cocokkan `sub` ke satu user lokal; tolak subject duplikat/tidak dikenal, akun nonaktif, atau role invalid. |
| Session/authentication | Rotasi ID session, hapus identitas lama saat pergantian user, session expiry, dan logout lokal. |
| Authorization | Periksa role, menu, aksi, serta cakupan data di server pada setiap route/API; gunakan deny-by-default. |
| Audit/error | Catat status login tanpa secret, code, verifier, atau payload sensitif. |

Contoh route klien (nama bebas, kecuali callback yang didaftarkan): `GET /auth/sso/start`, `GET /auth/sso/callback`, `POST /auth/logout`, serta route bisnis `/api/{modul}` yang dilindungi role lokal. Route start/callback bukan endpoint Portal; Portal hanya menyediakan `GET /sso/authorize` dan `POST /api/sso/token`. Jangan membuka endpoint token klien ke browser.

## 9. Pengetesan hit endpoint Portal

1. Di browser UAT, buka route start klien. Pastikan browser diarahkan ke `GET {PORTAL_BASE_URL}/sso/authorize` dengan `client_id`, `redirect_uri`, `state`, `code_challenge`, dan `code_challenge_method=S256`.
2. Login ke Portal jika diminta. User yang memiliki izin aplikasi akan kembali ke callback dengan `code` dan `state`; user tanpa izin mendapat `403 access_denied`.
3. Pastikan callback mencocokkan state sebelum backend klien mengirim `POST {PORTAL_BASE_URL}/api/sso/token` dengan `application/x-www-form-urlencoded`: `client_id`, `client_secret`, `code`, `redirect_uri`, `code_verifier`.
4. Respons sukses adalah HTTP 200 dengan `success=true`, `data.app_code`, `data.state`, dan `data.user` (`sub`, `user_id`, `auth_source`, `username`, `name`). Setelah itu klien memetakan user dan membuat session lokal.
5. Ulangi pengujian dengan callback/state/challenge salah, secret/verifier salah, code expired/replay, klien nonaktif, user tanpa izin, dan user yang tidak memiliki mapping/role lokal. Semua kasus harus ditolak tanpa membuat session.

Authorize sebaiknya diuji lewat browser dan route start klien karena memerlukan autentikasi/session Portal; token diuji dari backend/terminal UAT dengan code baru yang belum digunakan. Code berlaku 120 detik dan hanya sekali pakai. Jangan salin secret, code, verifier, atau body token ke browser, screenshot, tiket, atau log.

## 10. Kebutuhan database

### Database Portal

Terapkan hanya jika belum ada pada environment Portal:
- `docs/database/portal_sso_20261022.sql`: tabel registry klien dan authorization code sementara.
- `docs/database/portal_sso_secret_reveal_20261022.sql`: kolom secret terenkripsi untuk tampilan ulang admin.
- `docs/database/portal_sso_launch_uri_20261024.sql`: kolom URL peluncuran, terpisah dari callback.
- Tabel hak aplikasi user Portal sudah menjadi data/sumber yang diperiksa oleh authorize; panduan ini tidak menambah tabel akses baru.

### Database aplikasi klien

Klien perlu tabel akun lokal dan tabel penghubung identity yang memuat sumber/issuer, `external_sub`, `local_user_id`, dan timestamp. Terapkan unique key pada `(provider, external_sub)`, foreign key ke tabel user lokal, serta unique `(provider, local_user_id)` bila pemetaan satu-ke-satu. Role dan permission tetap disimpan/dikelola pada schema masing-masing klien. Nama tabel/kolom dan migration dibuat di repository klien; Portal tidak membuat atau mengubah database aplikasi klien. Jangan menyimpan `client_secret` atau code sekali pakai sebagai data akun.

Perubahan URL peluncuran hanya menambah kolom Portal. Perubahan policy/session pada klien tidak memerlukan DDL Portal tambahan. Backup dan verifikasi tiap migration pada database target sebelum mengaktifkan SSO.

## 11. Error dan batasan

| HTTP | Error | Arti/tindakan |
|---|---|---|
| 400 | invalid_request | Parameter wajib/format salah; periksa request dan PKCE |
| 400 | invalid_grant | Code, secret, callback, verifier, masa berlaku, atau status klien tidak valid |
| 403 | access_denied | User tidak diizinkan masuk aplikasi |
| 405 | method_not_allowed | Gunakan metode HTTP yang didokumentasikan |
| 503 | temporarily_unavailable | Portal gagal membuat/menyimpan code; periksa layanan dan coba kembali |

Belum tersedia: introspection/revoke endpoint, access/refresh token, OIDC discovery/JWKS, userinfo terpisah, logout lintas aplikasi, sinkronisasi role/module, provisioning/deprovisioning API, audit API terpusat, atau pencabutan session klien aktif. Respons token adalah klaim identitas sekali pakai untuk bootstrap session lokal, bukan bearer token untuk API portal lain.

## 12. Referensi implementasi portal

- Route: application/config/routes.php
- Authorize/token: application/controllers/Sso.php
- Pembuatan/pertukaran code: application/models/M_PortalSso.php
- Pemeriksaan izin aplikasi: application/models/M_PortalAccess.php
- Registrasi klien admin: application/controllers/admin/C_PortalSsoClients.php
- Akses per user admin: application/controllers/admin/C_PortalAccess.php
- Skema SSO: `docs/database/portal_sso_20261022.md`, `docs/database/portal_sso_20261022.sql`, dan `docs/database/portal_sso_launch_uri_20261024.md`
- Persyaratan database mapping klien: `docs/database/portal-sso-prasyarat-database-klien-20261024.md`
- Panduan ringkas administrator/pengguna dan uji endpoint tampil di route `admin/api-documentation`.

