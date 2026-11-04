# Audit dan migrasi Portal pada `kiucoid_karismaerp_dev`

Tanggal pelaksanaan: 4 November 2026. Target eksekusi adalah database lokal `kiucoid_karismaerp_dev` pada MariaDB 10.4.27. Konfigurasi default `application/config/database.php` menunjuk database ini tanpa prefix; profil `KARISMA_DB_PROFILE=portalerp` memakai database `portalerp` dan tabel berprefiks secara terpisah. Tidak ada perubahan pada database `portalerp` dalam pekerjaan ini.

## Kondisi awal

| Komponen | Sebelum migrasi | Tindakan |
| --- | --- | --- |
| `tb_portal_sso_client` dan `tb_portal_sso_code` | Ada, masing-masing 0 baris; kolom `launch_uri` dan `client_secret_encrypted` sudah ada | Dipertahankan; migrasi awal dan kedua `ALTER TABLE` tidak diulang |
| `tb_portal_app_directory` | Ada, 13 baris | Dipertahankan; seed tidak diulang |
| `tb_portal_app_access` | Belum ada | Dibuat |
| `tb_portal_app_module` | Belum ada | Dibuat |
| `tb_portal_sso_client_account` | Belum ada | Dibuat |
| `tb_portal_sso_client_account_assignment` | Belum ada | Dibuat |
| `tb_portal_sso_login_history` | Belum ada | Dibuat |
| `tb_password_reset_request` | Belum ada | Dibuat |

## Backup dan migrasi

Backup penuh sebelum migrasi dibuat dengan `mysqldump --single-transaction --routines --triggers --databases kiucoid_karismaerp_dev`. Lokasi: `C:\xampp\mysql\backup\karismaerp-dev-portal\kiucoid_karismaerp_dev_before_portal_20261104_090621.sql` (44.463.895 byte; SHA-256 `A3615461B2441D66DA92171B330A64E820D024C7A8FB511D546A70BDD0B45879`). File backup berada di luar repository karena dapat memuat data akun dan data bisnis. Penanda akhir dump telah diperiksa; pemulihan belum diuji.

Skrip berikut dijalankan satu kali, berurutan, dengan database target eksplisit `kiucoid_karismaerp_dev`:

1. [`portal_app_access_20261021.sql`](portal_app_access_20261021.sql)
2. [`portal_app_module_20261024.sql`](portal_app_module_20261024.sql)
3. [`portal_sso_client_account_20261029.sql`](portal_sso_client_account_20261029.sql)
4. [`portal_sso_client_account_assignment_20261030.sql`](portal_sso_client_account_assignment_20261030.sql)
5. [`portal_sso_login_history_20261030.sql`](portal_sso_login_history_20261030.sql)
6. [`password_reset_request_20261021.sql`](../manajemen-user/password_reset_request_20261021.sql)

Skrip pembuatan tabel di atas menggunakan `CREATE TABLE IF NOT EXISTS`. Tidak ada impor akun, pembuatan user, penetapan izin, pengisian secret, atau pembaruan URL.

## Verifikasi sesudah migrasi

Keenam tabel baru ada dengan engine InnoDB dan collation `utf8mb4_unicode_ci`. Jumlah kolom dan indeks, termasuk indeks unik untuk izin per user/aplikasi, kode modul, akun klien, penetapan akun, dan event histori, sesuai skrip sumber. Keenam tabel baru berisi 0 baris. Tabel SSO klien dan kode tetap 0 baris; direktori aplikasi tetap 13 baris. Kolom `launch_uri` dan `client_secret_encrypted` tetap tersedia.

Verifikasi ini mencakup struktur dan hitungan baris. Login browser, pengaturan admin, reset password, dan alur SSO end-to-end belum diuji.

## Migrasi yang tidak diterapkan

- `portalerp_database_20261026.sql` membuat database `portalerp` terpisah dan tabel berprefiks; bukan target `kiucoid_karismaerp_dev`.
- `portalerp_account_data_20261026.sql` menyalin akun dari sumber yang tertulis `kiucoid_karismaerp` ke `portalerp`; tidak sesuai target dan memerlukan audit data serta identitas sumber sebelum impor.
- `portal_user_20261024.sql` tidak dijalankan karena [panduan setup Portal](portalerp_setup_20261026.md) menyatakan kode Portal aktif tidak membacanya.
- `portal_sso_launch_direct_20261030.sql` bergantung pada klien `kiu_po` dan nilai URL lama tertentu; tabel klien target masih kosong.
- Skrip rollback tidak dijalankan. Skrip seed direktori dan migrasi SSO awal tidak diulang karena struktur yang diperlukan sudah ada.

## Tindak lanjut operasional

Tabel baru belum berisi konfigurasi akses atau klien. Administrator perlu menetapkan aplikasi yang diizinkan, mendaftarkan klien SSO beserta callback dan URL peluncuran, lalu mengimpor dan menetapkan akun klien bila fitur itu dipakai. Kunci enkripsi SSO yang stabil perlu tersedia pada server. Jangan membuat mapping akun otomatis hanya dari nama atau username.

Jika harus memulihkan keadaan sebelum migrasi, hentikan penggunaan fitur Portal terlebih dahulu dan tinjau data yang muncul setelah waktu backup. Pemulihan dump penuh akan menimpa perubahan lain yang terjadi setelah backup. Untuk tabel baru yang masih kosong, rollback dapat mengikuti skrip rollback per fitur setelah pemeriksaan dependensi; jangan menjalankan `DROP` tanpa audit data terbaru.

## Catatan profil database terpisah

Audit baca-saja pada database `portalerp` menemukan empat tabel fitur Portal yang belum tersedia di sana: `portalerp_tb_portal_app_directory`, `portalerp_tb_portal_sso_client_account`, `portalerp_tb_portal_sso_client_account_assignment`, dan `portalerp_tb_portal_sso_login_history`. Temuan ini tidak mengubah target migrasi pekerjaan ini. Jika proses Apache/PHP memakai `KARISMA_DB_PROFILE=portalerp`, empat fitur tersebut masih memerlukan migrasi tersendiri pada database berprefiks dan backup terpisah.
