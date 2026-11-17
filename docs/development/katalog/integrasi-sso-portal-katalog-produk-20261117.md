# Integrasi SSO Portal KARISMA ERP — Aplikasi Katalog Produk

- **Tanggal Development**: 17 November 2026
- **Kode Aplikasi (`app_code`)**: `katalog_produk`
- **URL Peluncuran Production**: `https://kiukatalog.karismaerp.com/sso/login`
- **Callback Terdaftar (`redirect_uri`)**: `https://kiukatalog.karismaerp.com/sso/callback`
- **Base URL Portal**: `https://portal.karismaerp.com`

## 1. Ringkasan Arsitektur & Protokol

Integrasi SSO mengikuti spesifikasi resmi pada `https://portal.karismaerp.com/admin/api-documentation` dengan protokol **Authorization Code + PKCE (`S256`)**:

1. **Peluncuran (`GET /sso/login` / `GET /auth/sso/start`)**:
   - Membersihkan atribut identitas session lokal lama dan merotasi session ID sebelum memulai transaksi baru.
   - Menghasilkan `state` acak (43 karakter Base64URL) dan `code_verifier` acak (43 karakter Base64URL), serta `code_challenge = BASE64URL(SHA256(code_verifier))` dengan `code_challenge_method = S256`.
   - Menyimpan transaksi SSO di session server (`portal_sso`) dengan masa berlaku **120 detik**.
   - Mengarahkan browser pengguna ke `GET https://portal.karismaerp.com/sso/authorize`.

2. **Callback & Pertukaran Code (`GET /sso/callback` / `GET /auth/sso/callback`)**:
   - Mengonsumsi transaksi SSO dari session terlebih dahulu (sekali pakai) sebelum menukar `code`.
   - Memvalidasi masa berlaku transaksi dan mencocokkan `state` menggunakan `hash_equals()`.
   - Menukar `code` dari backend klien ke `POST https://portal.karismaerp.com/api/sso/token` (`application/x-www-form-urlencoded`) menggunakan `client_id`, `client_secret`, `code`, `redirect_uri`, dan `code_verifier`.
   - Memvalidasi klaim identitas (`app_code = katalog_produk`, `state`, `user.sub`, `user.auth_source`, `user.user_id`, dan `client_account_username`).

3. **Pemetaan Akun Lokal & Konfirmasi Histori Login**:
   - Mencari tepat satu akun lokal pada tabel `tb_user` yang cocok persis dengan `client_account_username` (tanpa fallback ke username Portal dan tanpa auto-provisioning).
   - Memeriksa status aktif (`user_status = 1`) dan level hak akses yang didukung (`1` s/d `5`).
   - Menyelaraskan tautan subject Portal (`external_sub`) ke akun lokal pada tabel `tb_sso_identity`.
   - Mengirim konfirmasi ke `POST https://portal.karismaerp.com/api/sso/login-event` dengan `event_id` 64 karakter heksadesimal. Jika konfirmasi gagal/ditolak, session lokal dibatalkan.
   - Merotasi session ID, menetapkan session lokal, mencatat `tb_login_log`, dan mengarahkan pengguna sesuai `hak_akses` lokal (`dashboard`, `sales`, `keuangan`, atau `viewer`).

## 2. Daftar Berkas yang Ditambahkan / Diperbarui

| Berkas | Keterangan |
|---|---|
| `application/config/portal_sso.php` | Konfigurasi `portal_base_url`, `client_id` (`katalog_produk`), `client_secret`, `launch_uri`, `redirect_uri`, timeout, dan dukungan variabel environment. |
| `application/libraries/Portal_sso.php` | Adapter PKCE S256, pertukaran token `/api/sso/token`, konfirmasi `/api/sso/login-event`, diagnostik `/api/sso/check`, dan manajemen katalog `/api/client/modules`. |
| `application/controllers/Login.php` | Implementasi `sso_login()`, `sso_callback()`, `session_info()`, rotasi session ID, dan pembersihan identitas saat pergantian akun/logout. |
| `application/models/Loginmodel.php` | Validasi akun lokal `resolve_sso_local_account()`, sinkronisasi tautan identitas `sync_sso_identity_link()`, dan audit log `log_login()`. |
| `application/config/routes.php` | Pendaftaran route `sso/login`, `sso/callback`, `auth/sso/start`, `auth/sso/callback`, `auth/logout`, dan `api/session`. |
| `application/config/config.php` | Hardening cookie session (`cookie_httponly = TRUE`, `sess_regenerate_destroy = TRUE`, `cookie_secure` otomatis saat HTTPS). |
| `application/controllers/Katalog.php`, `Sales.php`, `SalesOnline.php`, `Keuangan.php`, `Viewer.php`, `User.php`, `Pricelist.php` | Penegakan autentikasi dan otorisasi server-side (`deny-by-default`) pada seluruh route dan endpoint AJAX. |
| `application/views/partial/login/body.php` | Penambahan tombol **Masuk melalui SSO Portal** menuju `/sso/login` dan sanitasi output flashdata. |
| `database/sql/katalog/portal_sso_identity_katalog_produk.sql` | Script DDL pembuatan tabel `tb_sso_identity` dan `tb_login_log`. |

## 3. Konfigurasi Environment Server (Opsional Override)

Aplikasi membaca variabel environment berikut jika diatur di server (dengan fallback ke konfigurasi production `katalog_produk`):

- `PORTAL_BASE_URL` / `KARISMAERP_SSO_PORTAL_BASE_URL` (default: `https://portal.karismaerp.com`)
- `PORTAL_CLIENT_ID` / `KARISMAERP_SSO_CLIENT_ID` (default: `katalog_produk`)
- `PORTAL_CLIENT_SECRET` / `KARISMAERP_SSO_CLIENT_SECRET`
- `PORTAL_LAUNCH_URL` / `KARISMAERP_SSO_LAUNCH_URI` (default: `https://kiukatalog.karismaerp.com/sso/login`)
- `PORTAL_CALLBACK_URL` / `KARISMAERP_SSO_REDIRECT_URI` (default: `https://kiukatalog.karismaerp.com/sso/callback`)
- `PORTAL_CA_BUNDLE` / `KARISMAERP_SSO_CA_BUNDLE` (opsional path berkas CA `.pem` untuk cURL TLS)
