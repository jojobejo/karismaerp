# Catatan Database Login Route Auth Menggunakan `tb_users`

Tanggal: 2026-10-29

## Dampak Database

Tidak ada perubahan skema atau data database.

Route login `auth` membaca `username`, `password`, `status`, dan `id` dari `tb_users`. Kolom pendukung session seperti nama, level, departemen, jobdesk, redirect, dan `last_login` tetap dibaca atau diperbarui secara dinamis apabila tersedia pada skema aktif.

Password tetap diverifikasi menggunakan `password_verify()` untuk hash PHP atau pemeriksaan kompatibilitas untuk password plaintext lama. Ketika password plaintext berhasil dipakai, kode yang telah tersedia menggantinya dengan hash PHP pada kolom `tb_users.password`.

Tidak diperlukan SQL migrasi, perubahan tabel, maupun impor database untuk perubahan ini.
