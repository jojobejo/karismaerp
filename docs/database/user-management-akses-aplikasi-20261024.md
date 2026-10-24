# Catatan Database: Akses Aplikasi dari User Management

Perubahan navigasi dan alur pengelolaan hak akses aplikasi pada 24 Oktober 2026 menggunakan tabel dan kolom yang telah ada.

## Dampak database

**Tidak ada perubahan skema database.** Tidak ada tabel, kolom, indeks, atau data referensi baru. Penyimpanan izin tetap menggunakan `tb_portal_app_access` dengan pasangan `auth_source` dan `user_id` untuk mengidentifikasi akun, serta `app_code` dan `is_allowed` untuk pengaturan aplikasi.

Perubahan hanya mengarahkan ikon roda gigi di User Management ke halaman Akses Aplikasi dengan identitas `tb_karyawan` dan ID user yang dipilih. Migrasi database tidak diperlukan.
