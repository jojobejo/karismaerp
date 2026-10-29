# Catatan Database Application Directory

Tanggal: 2026-10-29

## Skema

Fitur penyuntingan direktori menggunakan tabel baru `tb_portal_app_directory`. Skrip `portal_app_directory_20261029.sql` membuat tabel tersebut dan mengisi seed 13 aplikasi. `INSERT IGNORE` menjaga nilai yang sudah diedit apabila seed dijalankan ulang.

Kolom katalog menyimpan kode aplikasi, judul, URL HTTPS, deskripsi, ikon, warna aksen, status tersembunyi, ID pengguna yang terakhir mengubah, serta waktu pembuatan/perubahan. Kode aplikasi menjadi primary key. Tidak ada perubahan pada tabel akun atau tabel hak akses aplikasi.

## Penerapan

Skrip `docs/database/portal_app_directory_20261029.sql` telah diterapkan pada database development lokal `kiucoid_karismaerp_local`; verifikasi menghasilkan 13 baris aplikasi. Untuk environment lain, jalankan skrip tersebut satu kali pada database yang dipakai aplikasi. Halaman tetap menampilkan nilai bawaan source sebelum tabel tersedia, tetapi penyimpanan perubahan memerlukan tabel ini.

## Rollback

Jika fitur belum dipakai dan perlu dibatalkan, hapus tabel `tb_portal_app_directory`. Penghapusan tabel menghilangkan semua penyesuaian katalog yang sudah disimpan; ekspor tabel terlebih dahulu bila penyesuaian perlu dipertahankan.
