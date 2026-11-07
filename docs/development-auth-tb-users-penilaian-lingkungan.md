# Development Auth tb_users ke Penilaian Lingkungan

Tanggal development: 2026-06-30

## Tujuan

Login ERP dikembangkan agar dapat memakai data dari tabel `tb_users`. Jika user berhasil login dan memiliki `jobdesk_hrd = inputer_laporan`, sistem langsung mengarahkan user ke modul `penilaian_lingkungan`.

## Alur Login Baru

1. Form login tetap memakai controller `Auth/process`.
2. Model `M_Auth::get_auth_user()` mencari username ke `tb_users` terlebih dahulu.
3. Jika username tidak ada di `tb_users`, sistem fallback ke auth lama `tb_karyawan`.
4. Password diverifikasi dengan dua mode:
   - hash modern PHP (`password_hash` / `password_verify`);
   - plaintext legacy untuk data lama yang belum dimigrasikan.
5. Jika password legacy plaintext berhasil diverifikasi, sistem otomatis mengganti isi kolom `password` menjadi hash PHP (`PASSWORD_DEFAULT`) pada tabel sumber login.
6. Setelah login berhasil, session standar aplikasi tetap diisi:
   - `id`
   - `auth_source`
   - `nik`
   - `username`
   - `departemen`
   - `lv`
   - `akses_lv`
   - `akses_lv_id`
   - `jobdesk`
   - `jobdesk_hrd`
   - `nama`
   - `nama_user`
   - `tim`
   - `wilayah`
   - `logged_in`
7. Jika `auth_source = tb_users` dan `jobdesk_hrd = inputer_laporan`, redirect masuk ke route `penilaian_lingkungan`.
8. Jika user `tb_users` punya `default_redirect`, sistem memakai route tersebut.
9. Jika bukan user `tb_users`, flow redirect lama berbasis `tb_karyawan.jobdesk` tetap berjalan.

## File Yang Diubah

- `application/controllers/Auth.php`
  - Mengganti flow login agar memakai `M_Auth::get_auth_user()`.
  - Menambahkan redirect khusus `jobdesk_hrd = inputer_laporan`.
  - Menstandarkan session untuk user `tb_users`.
- `application/models/M_Auth.php`
  - Menambahkan lookup user dari `tb_users`.
  - Menambahkan fallback lookup dari `tb_karyawan`.
  - Menambahkan verifikasi password hash dan plaintext legacy.
  - Menambahkan auto-hash password legacy setelah login sukses.
  - Menambahkan update `last_login` dinamis sesuai sumber tabel.

## Catatan Operasional

- Untuk data baru, isi `tb_users.password` dengan hasil `password_hash()`.
- Data password plaintext legacy tetap bisa login. Setelah login sukses, sistem otomatis menyimpan ulang password tersebut sebagai hash.
- Role HRD baru dikendalikan oleh kolom `tb_users.jobdesk_hrd`.
- Nilai yang sudah didukung saat ini: `inputer_laporan`.
- Modul tujuan untuk `inputer_laporan`: `penilaian_lingkungan`.
