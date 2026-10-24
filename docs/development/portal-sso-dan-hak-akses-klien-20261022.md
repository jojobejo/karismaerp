# SSO Portal dan Hak Akses Aplikasi Klien

## Tujuan dan batas implementasi

Panduan API lengkap, contoh integrasi sisi klien, data onboarding, route admin, dan batas hak akses tersedia di `docs/development/panduan-api-integrasi-portal-sso-hak-akses-20261022.md`.

Portal menerbitkan identitas pengguna untuk aplikasi klien tanpa membagikan cookie session atau password portal. Implementasi ini menambahkan registrasi callback klien, pemeriksaan izin per user/aplikasi, alur authorization code sekali pakai dengan PKCE S256, pertukaran kode dari server ke server, rotasi/penonaktifan secret, dan endpoint klaim identitas minimum.

Repositori ini hanya berisi portal. Integrasi callback, pembuatan session, provisioning akun, role, serta izin modul/data pada setiap aplikasi klien harus dibuat dan diuji pada repositori klien. Endpoint saat ini tidak memberikan role aplikasi klien dan tidak mencabut session klien yang sudah aktif.

## Berkas implementasi

- `application/controllers/Sso.php`: endpoint authorize dan token.
- `application/models/M_PortalSso.php`: registrasi klien, pembuatan dan pertukaran kode.
- `application/controllers/admin/C_PortalSsoClients.php` dan `application/views/content/admin/portal_sso_clients.php`: administrasi klien.
- `application/models/M_PortalAccess.php`: pengecekan izin SSO; user tanpa penetapan akses ditolak.
- `application/controllers/Auth.php`: setelah login, melanjutkan request SSO tervalidasi dan merotasi ID session.
- `application/config/routes.php`: route SSO dan administrasi.

Fitur edit dan hapus pada daftar integrasi dijelaskan di `docs/development/portal-sso-klien-edit-hapus-20261024.md`.

## Cara aktivasi admin

1. Terapkan database sesuai `docs/database/portal_sso_20261022.md`.
2. Buka **Portal → Integrasi SSO Aplikasi Klien**.
3. Pilih kode aplikasi dan masukkan callback HTTPS klien secara tepat. Satu kode aplikasi memakai satu callback terdaftar.
4. Simpan client ID (kode aplikasi) dan secret ke secret manager di server klien. Setelah migration secret reveal diterapkan, admin juga dapat membuka secret melalui ikon key dan PIN. Secret lama yang dibuat sebelum migration tetap tidak dapat dipulihkan; rotasikan sekali.
5. Buka **Akses Aplikasi**, pilih akun, lalu simpan aplikasi yang boleh diakses. Tanpa penetapan ini, endpoint authorize menolak user.
6. Nonaktifkan registrasi untuk menghentikan login baru ke klien. Ini tidak mengakhiri session klien yang sudah diterbitkan.

Kartu aplikasi pada route `portal` mengambil URL peluncuran dari `tb_portal_sso_client.launch_uri` untuk `app_code` yang aktif. URL ini membuka halaman awal aplikasi klien; aplikasi lalu memulai authorization-code flow. Callback SSO disimpan terpisah di `redirect_uri` dan hanya digunakan untuk menerima `code` dan `state`. Pengaturan `launch_uri` dijelaskan pada `docs/database/portal_sso_launch_uri_20261024.md`. Hak tampil kartu tetap dikendalikan oleh Akses Aplikasi.

## Kontrak integrasi sisi klien

Klien adalah confidential web client yang memiliki backend. Saat user membuka aplikasi, klien membuat `state` acak dan `code_verifier` acak (43–128 karakter PKCE), menyimpan keduanya sementara pada session server, lalu menghitung `code_challenge = BASE64URL(SHA256(code_verifier))` tanpa padding. Klien redirect browser ke:

```text
GET {portal}/sso/authorize?client_id={app_code}&redirect_uri={callback_url}&state={state}&code_challenge={challenge}&code_challenge_method=S256
```

Jika belum login portal, user login terlebih dahulu dan portal melanjutkan request yang telah divalidasi. Portal mengarahkan kembali ke callback dengan `code` dan `state`. Klien wajib membandingkan state dengan nilai yang disimpan sendiri sebelum menukar kode.

Backend klien mengirim `application/x-www-form-urlencoded` melalui HTTPS ke `POST {portal}/api/sso/token` dengan `client_id`, `client_secret`, `code`, `redirect_uri`, dan `code_verifier`. Respons sukses berisi identitas stabil `sub` (`auth_source:id`), `user_id`, `auth_source`, `username`, `name`, `app_code`, dan state. Kode berumur 120 detik dan hanya dapat dipakai sekali. Secret harus dikirim dari backend, bukan browser. Jangan menaruh secret/kode di log aplikasi.

Klien perlu membuat atau memperbarui akun lokal berdasarkan `sub` (bukan username saja), membuat session lokal, dan menegakkan izin lokal pada setiap endpoint. Hak akses modul, tindakan, dan data tetap milik aplikasi klien. Perubahan akses portal menghentikan penerbitan login baru, tetapi belum menyediakan back-channel logout atau pencabutan session yang sudah ada; terapkan masa session klien yang sesuai atau tambahkan mekanisme revokasi pada fase berikutnya.

## Validasi dan risiko yang tersisa

- Callback dibatasi HTTPS dan pencocokan string persis; pastikan host/path callback benar sebelum mengaktifkan user.
- Hash SHA-256 dipakai untuk verifikasi secret. Salinan ciphertext dapat dibuka melalui ikon key setelah validasi PIN. Kunci enkripsi persisten `PORTAL_SSO_ENCRYPTION_KEY` wajib dikonfigurasi; nilai PIN default `898989` harus diganti untuk deployment bersama/production melalui `PORTAL_SSO_KEY_PIN`.
- PKCE S256, state, kode sekali pakai, sesi lokal klien, serta pemeriksaan akses portal adalah tanggung jawab gabungan portal dan klien.
- Ini merupakan fondasi protokol integrasi internal, bukan implementasi penuh OpenID Connect: tidak ada discovery/JWKS, JWT/access token, consent, scope API, userinfo terpisah, audit terpusat, atau logout lintas aplikasi.
- `csrf_protection` global CodeIgniter saat ini nonaktif; form administrasi klien memakai token session khusus, tetapi hardening CSRF global masih perlu direncanakan terpisah.
- Belum dilakukan UAT end-to-end karena repositori callback klien dan kredensial test tidak tersedia.
