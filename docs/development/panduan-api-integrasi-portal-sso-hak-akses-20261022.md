# Panduan API Integrasi Portal: SSO dan Hak Akses

## 1. Tujuan dan cakupan

Panduan ini menjelaskan kontrak integrasi yang saat ini tersedia di portal KARISMA ERP untuk login aplikasi klien dan pengaturan izin masuk pengguna per aplikasi.

Portal tidak membagikan cookie/session portal atau password pengguna. Backend aplikasi klien menukar authorization code dengan portal, memverifikasi identitas, lalu membuat session lokal. Protokol ini adalah integrasi internal berbasis authorization code + PKCE S256; ini belum implementasi OpenID Connect penuh.

Hak akses yang dikelola portal saat ini adalah izin masuk ke aplikasi klien. Role, menu/modul, aksi, dan cakupan data di dalam aplikasi tetap harus dikelola dan ditegakkan oleh aplikasi klien. Portal belum mengirim role/permission klien dan belum menyediakan API provisioning user atau role ke aplikasi klien.

## 2. Route dan pemakai

| Fungsi | Route | Pemakai |
|---|---|---|
| Memulai autentikasi dan meminta authorization code | GET /sso/authorize | Browser pengguna, atas nama aplikasi klien |
| Menukar code menjadi klaim identitas | POST /api/sso/token | Backend aplikasi klien saja |
| Membaca izin aplikasi untuk satu akun | GET /admin/portal-app-access/detail?source=...&user_id=... | AJAX halaman admin portal |
| Menyimpan izin aplikasi akun | POST /admin/portal-app-access/save | AJAX halaman admin portal |
| Registrasi/perbarui klien | POST /admin/portal-sso-clients/save | AJAX administrator portal |
| Buat kunci enkripsi lokal | POST /admin/portal-sso-clients/generate-encryption-key | AJAX administrator portal |
| Aktifkan/nonaktifkan klien | POST /admin/portal-sso-clients/toggle/{app_code} | AJAX administrator portal |
| Rotasi secret klien | POST /admin/portal-sso-clients/rotate/{app_code} | AJAX administrator portal |
| Tampilkan ulang secret | POST /admin/portal-sso-clients/reveal/{app_code} | AJAX administrator portal dengan PIN |

## 2A. API operasional dari modul ERP

Selain SSO, project menyediakan endpoint baca untuk data Purchasing/Master Barang dan Logistik. Route ini berbeda dari API SSO dan pada implementasi saat ini tidak memakai autentikasi client_id/client_secret atau session portal.

| Modul | Metode dan route | Parameter | Respons ringkas |
|---|---|---|---|
| Master Barang / Purchasing | GET /api/v1/barang | search, kode_barang, kd_suplier, limit (default 100, maksimum 500), offset | status, total_data, total_filter, limit, offset, data |
| Purchase Order / Pre-PO | GET /api/v1/pre-po | Tidak ada; hanya baris bertanggal transaksi | status, total_data, data PO dan histori diskon |
| Logistik / Stok per Gudang | GET /ics/api/stock_per_gudang?gudang={kode} | gudang opsional | status, data stok per gudang |

Endpoint barang dan Pre-PO mengirim `Access-Control-Allow-Origin: *`. Endpoint stok belum menambahkan mekanisme autentikasi. Batasi akses jaringan dan jangan menganggap SSO melindungi endpoint data ini; autentikasi/otorisasi perlu ditambahkan sebelum data dibuka ke pihak eksternal.

User Management, Master Organisasi, Jobdesk, Akses Level, Master Menu, Akses Aplikasi, dan Integrasi SSO dikelola melalui halaman ERP dengan session dan hak admin. Route AJAX halaman tersebut adalah endpoint UI internal, bukan API provisioning/integrasi untuk aplikasi klien. Modul Sales, Keuangan, Stock Opname, HRD, Inventaris, Purchasing, dan Logistik tidak otomatis menyediakan API publik hanya karena memiliki controller atau route.

Kartu aplikasi Portal memakai **Callback Terdaftar** dari Integrasi SSO yang aktif (`tb_portal_sso_client.redirect_uri`). Hak tampil kartu tetap mengikuti pengaturan hak aplikasi user. Integrasi belum terdaftar atau nonaktif tidak memiliki redirect; klik kartu meminta user menghubungi admin. Setiap `app_code` memakai satu callback HTTPS yang dikelola melalui menu `admin/portal-sso-clients`.

Route admin memakai session portal dan kewenangan admin. Route tersebut bukan API integrasi untuk aplikasi klien. Route master/user-management mengelola akun ERP dan bukan API provisioning user pada aplikasi klien.

Gunakan base URL portal yang diberikan tim operasional, misalnya {PORTAL_BASE_URL}/sso/authorize. Portal dan aplikasi klien wajib menggunakan HTTPS.

## 3. Katalog app_code

client_id adalah kode aplikasi yang dipilih dan didaftarkan administrator portal.

| app_code | Nama aplikasi |
|---|---|
| kiu_po | KIU PO |
| warehouse | Warehouse |
| kmt_corn | KMT CORN |
| kpi | KPI |
| pos_security | POS Security |
| katalog_produk | Katalog Produk |
| plafon_sales | Plafon Sales |
| stockopname | Stockopname |
| penilaian_lingkungan | Penilaian Lingkungan Kerja |
| overdue | Overdue |
| kios_transfer | Kios Transfer |
| sapa_karisma | Sapa Karisma |

Satu app_code memiliki satu callback terdaftar pada satu waktu. Callback harus HTTPS dan nilainya harus sama persis pada authorize dan token.

## 4. Tata cara penggunaan oleh administrator portal

1. Pastikan schema SSO sudah diterapkan sesuai `docs/database/portal_sso_20261022.md`.
2. Pastikan user portal sudah tersedia dan aktif pada sumber akun yang benar (`tb_karyawan` atau `tb_users`).
3. Buka **Integrasi SSO**, pilih `app_code`, masukkan callback HTTPS milik aplikasi klien, lalu simpan.
4. Salin secret ke secret manager backend klien. Admin dapat menampilkan ulang secret melalui ikon key setelah PIN diverifikasi; secret tetap tidak boleh disimpan pada browser klien, file konfigurasi publik, atau Git.
5. Buka **Akses Aplikasi**, pilih user portal, atur aplikasi yang diizinkan, lalu tekan **Simpan akses**. Tanpa konfigurasi akses yang disimpan, request SSO user ditolak.
6. Tim aplikasi klien mengimplementasikan callback, validasi state, pertukaran code server-to-server, pemetaan user lokal, dan otorisasi internal.
7. Jalankan UAT bersama user yang diizinkan dan ditolak sebelum membuka akses production.

Untuk membuka tampilan ulang secret, terapkan migration `docs/database/portal_sso_secret_reveal_20261022.sql` dan atur `PORTAL_SSO_ENCRYPTION_KEY` yang persisten pada environment server. PIN awal `898989` dapat diganti dengan `PORTAL_SSO_KEY_PIN`. Klien lama sebelum migration tambahan perlu dirotasi karena hanya memiliki hash dan tidak bisa dipulihkan. Jika secret bocor, lakukan rotasi dan perbarui server klien. Menonaktifkan klien menghentikan login baru dan pertukaran code, tetapi tidak mematikan session lokal yang sudah dimiliki pengguna.

## 5. Alur login aplikasi klien

### 5.1 Siapkan request di backend klien

Saat pengguna membuka aplikasi klien tanpa session lokal, backend membuat:
- state acak dengan panjang 16–512 karakter;
- code_verifier acak 43–128 karakter dari A-Z, a-z, 0-9, titik, underscore, tilde, atau tanda minus;
- code_challenge = BASE64URL(SHA256(code_verifier)), tanpa padding. Hasilnya 43 karakter;
- simpan state dan code_verifier sementara di session server klien.

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
    "app_code": "kiu_po",
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
| app_code | Admin portal | Pilih dari katalog resmi pada bagian 3 |
| Domain environment | Tim klien/infra | URL development/UAT dan production; HTTPS |
| Callback URL | Tim klien | Route callback backend stabil; didaftarkan persis |
| client_id | Portal | Sama dengan app_code |
| client_secret | Portal dan tim klien | Ditampilkan saat registrasi/rotasi atau melalui ikon key dengan PIN; simpan di secret manager, jangan commit |
| Kunci akun lokal | Tim klien | Simpan sub sebagai external identity key; tetapkan auto-provision atau akun harus disiapkan terlebih dahulu |
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

## 8. Route admin dan format AJAX

### Akses aplikasi
GET /admin/portal-app-access/detail?source=tb_karyawan&user_id=123 mengembalikan status, message, dan data berisi user, katalog apps, callback terdaftar untuk integrasi aktif, denied_apps, serta configured.

POST /admin/portal-app-access/save menerima auth_source, user_id, dan allowed_apps[]. Hak akses user menentukan tile mana yang dapat dilihat. URL tile tidak disimpan pada tabel akses user; portal menggunakan callback `redirect_uri` dari klien SSO aktif dengan app_code yang sama. Respons berbentuk { "status": true|false, "message": "...", "data": [] }.

### Integrasi klien
POST /admin/portal-sso-clients/save menerima form_token, app_code, dan redirect_uri. Secret dibuat portal; database menyimpan hash SHA-256 untuk verifikasi dan ciphertext AES-256-GCM untuk tampilan ulang di portal.

POST /admin/portal-sso-clients/toggle/{app_code} menerima form_token dan enabled (1/0). POST /admin/portal-sso-clients/rotate/{app_code} menerima form_token. POST /admin/portal-sso-clients/reveal/{app_code} menerima form_token dan pin. Token form diperbarui melalui data.form_token setelah tiap aksi. Route ini hanya untuk UI administrator portal; endpoint reveal memberlakukan lock lima menit setelah lima PIN salah dalam satu sesi.

Endpoint admin bukan endpoint public. Guard session dan kewenangan admin tetap berlaku. Konfigurasi CSRF global CI saat ini nonaktif; halaman Integrasi SSO memiliki form token khusus, sedangkan penyimpanan Akses Aplikasi belum memakai token endpoint khusus. Pertahankan akses pada konteks session admin tepercaya dan rencanakan hardening CSRF sebelum memperluas pemakaiannya.

## 9. Error dan batasan

| HTTP | Error | Arti/tindakan |
|---|---|---|
| 400 | invalid_request | Parameter wajib/format salah; periksa request dan PKCE |
| 400 | invalid_grant | Code, secret, callback, verifier, masa berlaku, atau status klien tidak valid |
| 403 | access_denied | User tidak diizinkan masuk aplikasi |
| 405 | method_not_allowed | Gunakan metode HTTP yang didokumentasikan |
| 503 | temporarily_unavailable | Portal gagal membuat/menyimpan code; periksa layanan dan coba kembali |

Belum tersedia: introspection/revoke endpoint, access/refresh token, OIDC discovery/JWKS, userinfo terpisah, logout lintas aplikasi, sinkronisasi role/module, provisioning/deprovisioning API, audit API terpusat, atau pencabutan session klien aktif. Respons token adalah klaim identitas sekali pakai untuk bootstrap session lokal, bukan bearer token untuk API portal lain.

## 10. Referensi implementasi portal

- Route: application/config/routes.php
- Authorize/token: application/controllers/Sso.php
- Pembuatan/pertukaran code: application/models/M_PortalSso.php
- Pemeriksaan izin aplikasi: application/models/M_PortalAccess.php
- Registrasi klien admin: application/controllers/admin/C_PortalSsoClients.php
- Akses per user admin: application/controllers/admin/C_PortalAccess.php
- Skema SSO: docs/database/portal_sso_20261022.md dan docs/database/portal_sso_20261022.sql

