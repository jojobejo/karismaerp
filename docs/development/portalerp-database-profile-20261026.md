# Development: profil koneksi Portal `portalerp`

Tanggal: 26 Oktober 2026

## Perubahan kode

File `application/config/database.php` mendukung environment `KARISMA_DB_PROFILE=portalerp`. Pada profil tersebut, database menjadi `portalerp` dan prefix CI3 menjadi `portalerp_`. Tanpa environment tersebut, koneksi default tetap `kiucoid_karismaerp` tanpa prefix sehingga penggunaan ERP yang sudah ada tidak dialihkan.

Perbaikan 26 Oktober 2026: pemilihan database dan prefix sebelumnya belum aktif bersamaan. Kode sempat mengarah ke `portalerp` tanpa prefix, sehingga query tabel logis seperti `tb_users` tidak menemukan tabel fisik `portalerp_tb_users`. Error database dikirim sebagai HTML dan dapat muncul di browser sebagai `Unexpected token '<'` ketika respons endpoint diharapkan berupa JSON. Profil sekarang kembali eksplisit melalui `KARISMA_DB_PROFILE`.

Tidak diperlukan penggantian string tabel pada model/controller. Pemanggilan Query Builder seperti `get('tb_users')`, `insert('tb_portal_sso_code')`, `table_exists('tb_portal_app_access')`, dan `field_exists('launch_uri', 'tb_portal_sso_client')` menggunakan nama logis; CI3 menambahkan prefix. `auth_source` tetap literal `tb_karyawan` atau `tb_users`, karena merupakan identitas sumber dan dibandingkan dengan nilai tersebut oleh Auth/SSO. Tidak ditemukan raw SQL untuk tabel-tabel Portal dalam jalur yang diaudit. Tidak ada file aplikasi lain yang diubah.

## Cara menggunakan

Atur environment `KARISMA_DB_PROFILE=portalerp` untuk proses Apache/PHP yang menjalankan Portal dan restart proses agar variabel terbaca. Import dahulu `docs/database/portalerp_database_20261026.sql`; pemindahan akun bersifat terpisah dan dijelaskan di `docs/database/portalerp_setup_20261026.md`. Pastikan URL dan kunci SSO diatur untuk deployment.

Database minimal hanya memuat akun dan tabel Portal yang diaudit. Halaman admin yang memakai guard `master/user-management` masih membutuhkan tabel role/menu/permission ERP yang tidak disalin. Modul ERP lain juga berada di luar cakupan profil database ini.

## Verifikasi implementasi

Review statis menunjukkan seluruh akses Portal yang ditinjau memakai Query Builder/metadata CI3. Pengujian koneksi, import, login dan UAT belum dilakukan; ikuti checklist di dokumen setup database sebelum menyatakan deployment siap.
