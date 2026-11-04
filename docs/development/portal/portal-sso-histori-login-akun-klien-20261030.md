# Histori Login Akun Klien Portal SSO

## Tujuan

Admin dapat memantau pemakaian akun klien yang ditetapkan untuk user Portal, termasuk aplikasi tujuan dan waktu login. KIU PO mengirim konfirmasi server-ke-server sesudah session lokal berhasil dibuat. Portal menerima event hanya jika aplikasi, user, serta username masih cocok dengan penetapan akun aktif.

## Alur

1. KIU PO menerima authorization code dan klaim `client_account_username` dari Portal.
2. KIU PO memvalidasi akun lokal, role, dan membentuk session lokal.
3. KIU PO mengirim `POST /api/sso/login-event` memakai client secret. Event memakai ID acak agar retry event yang sama idempoten.
4. Portal memeriksa client aktif, format subject, dan penetapan akun yang cocok, lalu menyimpan histori.
5. KIU PO mengarahkan user ke Dashboard hanya setelah Portal mengonfirmasi event. Jika pencatatan gagal, session dibersihkan dan login dihentikan.

Endpoint histori memerlukan HTTPS dan client secret yang valid. Penyimpanan tidak mencakup password atau ID session. Kolom audit berisi aplikasi, identitas Portal, snapshot username akun, waktu, IP, dan user-agent.

## Tampilan Admin

Buka `admin/portal-sso-clients/login-history`. Halaman menyediakan jumlah login, login hari ini, jumlah akun unik, filter aplikasi, pencarian user/akun/IP, serta 500 event terbaru. Link tersedia dari halaman Integrasi SSO dan Daftar Akun Klien.

Event hanya tercatat untuk klien yang mengirim konfirmasi endpoint ini. Dalam workspace ini, KIU PO mengirim konfirmasi; klien lain perlu menerapkan kontrak endpoint yang sama setelah session lokal dibuat.

## Berkas perubahan

- `application/controllers/Sso.php`: endpoint konfirmasi login.
- `application/models/M_PortalSsoLoginHistory.php`: validasi assignment, idempotensi, histori, ringkasan.
- `application/controllers/admin/C_PortalSsoClients.php`: halaman admin.
- `application/views/content/admin/portal_sso_login_history.php`: tampilan histori.
- `application/config/routes.php`: route API dan admin.
- Repo KIU PO: `application/libraries/Portal_sso.php` dan `application/controllers/Auth.php` mengirim konfirmasi.

## Status verifikasi

UAT login browser, uji kredensial akun produksi, dan pengecekan database setelah migrasi belum dilakukan. Sebelum fitur dipakai, terapkan migrasi database histori di repo ini dan pastikan konfigurasi client secret KIU PO valid.
