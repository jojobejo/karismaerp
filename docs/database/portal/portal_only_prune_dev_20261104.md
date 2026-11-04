# Pembersihan database dev menjadi basis Portal

Tanggal eksekusi: 4 November 2026. Target tunggal: `kiucoid_karismaerp_dev`. Database `portalerp` dan dump acuan di `db/` tidak diubah.

## Backup dan ruang lingkup

Backup penuh sebelum penghapusan dibuat dengan `mysqldump --single-transaction --routines --triggers --events --databases kiucoid_karismaerp_dev` di luar repository:

`C:\xampp\mysql\backup\karismaerp-dev-portal\kiucoid_karismaerp_dev_before_portal_only_20261104_092537.sql`

Ukuran 44.472.138 byte; SHA-256 `8BF7DF980AD01412E271A91F1B307336823796303DD5483B6A9E4A9224E6C65F`. Penanda akhir dump diperiksa. Backup berisi data akun serta data bisnis dan harus dijaga aksesnya. Uji pemulihan belum dilakukan.

Sebelum pembersihan ada 187 tabel. [Skrip SQL yang dijalankan](portal_only_prune_20261104.sql) menyebut 172 tabel secara eksplisit untuk dihapus. Pemeriksaan metadata menemukan empat foreign key, semuanya berada di antara tabel yang dihapus; tidak ada foreign key dari atau ke tabel yang dipertahankan. Skrip menghapus data `tb_karyawan` dan data `tb_login_log` lama serta menyisakan hanya akun `tb_users.id=183` (`admin`). Katalog `tb_portal_app_directory` sebanyak 13 baris dipertahankan. Tabel Portal lain saat eksekusi kosong.

## Tabel yang dipertahankan

| Kegunaan | Tabel |
| --- | --- |
| Identitas dan login | `tb_users`, `tb_karyawan`, `tb_login_log`, `tb_password_reset_request` |
| Menu dan izin admin | `tb_menu`, `tb_akses_menu`, `tb_akses_level` |
| Modul Portal | `tb_portal_app_access`, `tb_portal_app_directory`, `tb_portal_app_module`, `tb_portal_sso_client`, `tb_portal_sso_client_account`, `tb_portal_sso_client_account_assignment`, `tb_portal_sso_code`, `tb_portal_sso_login_history` |

`tb_karyawan` dipertahankan sebagai sumber identitas yang didukung model Portal dan reset password, tetapi datanya dikosongkan. Tabel menu/izin admin dipertahankan untuk halaman Module Management dan guard yang sudah ada; ketiganya memang kosong sebelum pembersihan.

## Hasil verifikasi

Setelah SQL, database berisi tepat 15 tabel. `tb_users` berisi satu akun `admin` aktif; hash password yang ditetapkan sebelumnya masih terverifikasi. `tb_karyawan` kosong dan direktori aplikasi tetap 13 baris. Log login lama dihapus; tiga baris log baru muncul dari uji login setelah pembersihan. Tabel akses aplikasi dan klien SSO masih kosong.

Uji login HTTP 303 ke `/portal` berhasil. Halaman Portal, direktori, akses aplikasi untuk user `tb_users:183`, katalog modul untuk `kiu_po`, integrasi SSO, daftar akun klien, histori login, cek API SSO, dokumentasi API, reset password, User Management, dan Module Management merespons HTTP 200 tanpa pesan kesalahan database. Empat endpoint baca daftar menghasilkan JSON valid. Operasi tulis, SSO end-to-end, dan pemulihan backup belum diuji.

## Pemulihan

Skrip ini menghapus data dan tabel secara permanen. Jika perlu kembali ke keadaan sebelum pembersihan, hentikan penulisan ke database, buat backup keadaan terbaru, lalu pulihkan dump penuh di atas ke database target yang tepat. Pemulihan penuh akan menimpa perubahan yang dibuat setelah waktu backup; jangan langsung menjalankannya pada database yang sudah menerima data baru tanpa audit. Tidak ada rollback parsial yang aman untuk 172 tabel.

Tidak ada perubahan struktur pada 15 tabel yang dipertahankan. Sebanyak 172 tabel lain dihapus dari skema database dev.
