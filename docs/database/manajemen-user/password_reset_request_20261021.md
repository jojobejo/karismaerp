# Database: antrean permintaan reset password

Tanggal: 21 Oktober 2026

## Perubahan

Penambahan tabel `tb_password_reset_request` untuk mencatat permintaan reset dari akun pada `tb_users` maupun `tb_karyawan`. Referensi akun menggunakan pasangan `auth_source` dan `user_id` karena kedua tabel merupakan sumber autentikasi berbeda. Status `PENDING` berubah menjadi `COMPLETED` setelah password akun berhasil diperbarui.

## Penerapan

Jalankan SQL `password_reset_request_20261021.sql` pada database target setelah membuat backup. Skrip menggunakan `CREATE TABLE IF NOT EXISTS`, tidak mengubah tabel atau data lama. Password reset tetap disimpan dalam bentuk hash di tabel akun sumber.

## Dampak dan rollback

Perubahan skema: satu tabel baru. Rollback hanya bila antrean tidak lagi diperlukan dan setelah data permintaan diarsipkan: `DROP TABLE tb_password_reset_request;`. Tidak ada rollback otomatis pada file migrasi.
