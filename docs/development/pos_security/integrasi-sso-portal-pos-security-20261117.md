# Integrasi SSO Portal KARISMA ERP — Aplikasi POS Security & HRD

- **Tanggal Development**: 17 November 2026
- **Kode Aplikasi (`app_code`)**: `pos_security`
- **URL Peluncuran Production**: `https://hrd.karismaerp.com/sso/login`
- **Callback Terdaftar (`redirect_uri`)**: `https://hrd.karismaerp.com/sso/callback`
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
   - Memvalidasi klaim identitas (`app_code = pos_security`, `state`, `user.sub`, `user.auth_source`, `user.user_id`, dan `client_account_username`).

3. **Pemetaan Akun Lokal & Konfirmasi Histori Login**:
   - Mencari tepat satu akun lokal pada tabel `tb_user` yang cocok persis dengan `client_account_username` (tanpa fallback ke username Portal dan tanpa auto-provisioning).
   - Memeriksa status aktif akun lokal dan level akses yang didukung (`akses_lv`).
   - Menyelaraskan tautan subject Portal (`external_sub`) ke akun lokal pada tabel `tb_sso_identity`.
   - Mengirim konfirmasi ke `POST https://portal.karismaerp.com/api/sso/login-event` dengan `event_id` 64 karakter heksadesimal. Jika konfirmasi gagal/ditolak, session lokal dibatalkan.
   - Merotasi session ID, menetapkan session lokal, mencatat `tb_login_log`, dan mengarahkan pengguna sesuai departemen/role lokal (`hrd_lap_paket_pos`, `hrd_lap_expedisi`, atau `dashboard`).

## 2. Daftar Berkas yang Ditambahkan / Diperbarui

| Berkas | Keterangan |
|---|---|
| `application/config/portal_sso.php` | Konfigurasi `portal_base_url`, `client_id` (`pos_security`), `client_secret`, `launch_uri`, `redirect_uri`, timeout, dan dukungan variabel environment. |
| `application/libraries/Portal_sso.php` | Adapter PKCE S256, pertukaran token `/api/sso/token`, konfirmasi `/api/sso/login-event`, diagnostik `/api/sso/check`, dan manajemen katalog `/api/client/modules`. |
| `application/controllers/Auth.php` | Implementasi `sso_login()`, `sso_callback()`, `session_info()`, rotasi session ID, dan pembersihan identitas saat pergantian akun/logout. |
| `application/models/M_Auth.php` | Validasi akun lokal `resolve_sso_local_account()`, sinkronisasi tautan identitas `sync_sso_identity_link()`, dan audit log `log_login()`. |
| `application/config/routes.php` | Pendaftaran route `sso/login`, `sso/callback`, `auth/sso/start`, `auth/sso/callback`, `auth/logout`, dan `api/session`. |
| `application/config/config.php` | Hardening cookie session (`cookie_httponly = TRUE`, `sess_regenerate_destroy = TRUE`, `cookie_secure` otomatis saat HTTPS). |
| `application/controllers/Dashboard.php`, `hrd/C_Hrd.php`, `logistik/C_Logistik.php`, `inventaris/C_Inventaris.php`, `kpi/C_Kpi.php`, `maintenance/C_Maintenance.php`, `requestdesign/C_requestdesign.php`, `pelanggan/C_Pelanggan.php` | Penegakan autentikasi dan otorisasi server-side (`deny-by-default`) pada seluruh route dan endpoint AJAX yang dilindungi. |
| `application/views/content/login/body.php` | Penambahan tombol **Masuk melalui SSO Portal** menuju `/sso/login` dan sanitasi output flashdata. |
| `database/sql/pos_security/portal_sso_identity_pos_security.sql` | Script DDL pembuatan tabel `tb_sso_identity` dan `tb_login_log`. |

## 3. Konfigurasi Environment Server (Opsional Override)

Aplikasi membaca variabel environment berikut jika diatur di server (dengan fallback ke konfigurasi production `pos_security`):

- `PORTAL_BASE_URL` / `KARISMAERP_SSO_PORTAL_BASE_URL` (default: `https://portal.karismaerp.com`)
- `PORTAL_CLIENT_ID` / `KARISMAERP_SSO_CLIENT_ID` (default: `pos_security`)
- `PORTAL_CLIENT_SECRET` / `KARISMAERP_SSO_CLIENT_SECRET`
- `PORTAL_LAUNCH_URL` / `KARISMAERP_SSO_LAUNCH_URI` (default: `https://hrd.karismaerp.com/sso/login`)
- `PORTAL_CALLBACK_URL` / `KARISMAERP_SSO_REDIRECT_URI` (default: `https://hrd.karismaerp.com/sso/callback`)
- `PORTAL_CA_BUNDLE` / `KARISMAERP_SSO_CA_BUNDLE` (opsional path berkas CA `.pem` untuk cURL TLS)
