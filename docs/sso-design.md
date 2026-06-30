# Desain SSO Karisma ERP

Sumber identitas utama tetap `tb_karyawan`. Implementasi ini menambah lapisan SSO di atas auth lokal yang sudah ada, bukan menggantikannya.

## Alur

1. Aplikasi klien mengarahkan user ke `sso/authorize`.
2. Jika user belum login di portal, portal mengirim ke `Auth` dengan parameter `return_to`.
3. Setelah login sukses, portal kembali ke `sso/authorize`.
4. Portal mengirim `authorization code` singkat umur pakainya ke `redirect_uri` aplikasi klien.
5. Aplikasi klien menukar code menjadi `access_token` lewat `sso/token`.
6. Aplikasi klien membuat session lokal sendiri dari payload user yang diterima.

## Perubahan database

File: `database/sso_schema.sql`

- `tb_sso_clients`: daftar aplikasi yang boleh memakai SSO.
- `tb_sso_auth_codes`: kode satu kali pakai untuk authorization flow.
- `tb_sso_sessions`: token sesi aktif untuk introspection dan revoke.

Tidak ada perubahan wajib pada `tb_karyawan`.

## Perubahan controller

File: `application/controllers/Sso.php`

- `authorize`: validasi client dan redirect URI, lalu mengirim code ke aplikasi tujuan.
- `token`: menukar code menjadi access token dan payload user.
- `introspect`: memeriksa apakah token masih aktif.
- `revoke`: mencabut token aktif.
- `logout`: mencabut token dan sesi portal.

File: `application/controllers/Auth.php`

- Menyimpan dan meneruskan `return_to` agar login dapat kembali ke alur SSO.
- Logout lokal ikut mencabut sesi SSO portal jika ada.

## Perubahan model

File: `application/models/M_Sso.php`

- Mengelola registry client.
- Membuat dan memverifikasi authorization code.
- Membuat, introspect, dan revoke access token.
- Menyusun payload user dari `tb_karyawan`.

File: `application/models/M_Auth.php`

- Menambah fungsi revoke sesi SSO yang terkait dengan session portal.

## Perubahan view

File: `application/views/content/login/body.php`

- Menyimpan hidden field `return_to` pada form login.
- Menambahkan catatan bahwa portal ini menjadi identity provider SSO.

File: `application/views/portal/index.php`

- Menambahkan tombol `Login SSO` pada landing portal.

## Konfigurasi

File: `application/config/sso.php`

- `KARISMA_SSO_SECRET` dipakai untuk tanda tangan payload SSO.
- TTL code dan session dipisah agar code cepat kedaluwarsa.

File: `application/config/config.php`

- Session regeneration dibuat lebih aman.
- Cookie di-hardening dengan `httponly` aktif dan `secure` mengikuti HTTPS.

## Catatan implementasi

- SSO ini tidak berbagi cookie session antar aplikasi.
- Tiap aplikasi tetap menyimpan session lokal sendiri.
- Aplikasi klien harus melakukan mapping user berdasarkan `nik` atau `id_karyawan`.
- Redirect URI harus di-whitelist pada tabel `tb_sso_clients`.
