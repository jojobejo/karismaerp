# Perubahan Login Route Auth Menggunakan `tb_users`

Tanggal: 2026-10-29

## Ringkasan

Halaman route `auth` tidak lagi menampilkan tautan **Lupa password?** maupun keterangan **Koneksi aman untuk akun perusahaan Anda.** Form username dan password tetap tersedia.

Proses login pada route `process` sekarang hanya mencari akun berdasarkan username di tabel `tb_users`. Jika username tidak ditemukan di tabel tersebut, login ditolak; data `tb_karyawan` tidak digunakan sebagai sumber autentikasi untuk route ini.

## Alur Penggunaan

1. Buka halaman `/auth`.
2. Masukkan username dan password akun yang terdaftar pada `tb_users`.
3. Sistem memeriksa status akun dan memvalidasi password tersimpan.
4. Login yang berhasil membuat session aplikasi dan melanjutkan ke portal, termasuk proses SSO tertunda bila ada.

Validasi mendukung hash password PHP dan password plaintext lama. Jika autentikasi plaintext berhasil, mekanisme yang sudah ada akan menyimpan hash password pada `tb_users`; login berhasil juga memperbarui `last_login` jika kolom itu tersedia.

## Berkas Perubahan

- `application/views/content/login/body.php`: menghapus dua elemen teks dari halaman login.
- `application/controllers/Auth.php`: membatasi pencarian kredensial pada akun `tb_users`.

Fitur permintaan reset password sisi administrator tetap tersedia. Perubahan ini tidak mengubah proses/reset password administrator.

## Validasi

Belum dilakukan pengujian login melalui browser atau database pada perubahan ini. Lakukan UAT dengan akun aktif `tb_users`, akun nonaktif, password salah, serta username yang hanya terdapat di `tb_karyawan`.
