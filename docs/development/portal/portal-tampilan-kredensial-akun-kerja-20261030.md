# Pengembangan Tampilan Kredensial Akun Kerja Portal

## Perubahan

Portal Apps memuat kredensial akun kerja hanya jika admin telah menetapkan akun untuk kombinasi aplikasi, sumber autentikasi, dan ID pengguna yang sedang login. Model mengambil username serta password terenkripsi dari join tabel assignment dan katalog akun, lalu mendekripsi password AES-256-GCM menggunakan kunci SSO lokal/environment yang dipakai pada proses impor. View menampilkan keduanya dengan escaping HTML.

Jika assignment tidak ditemukan, kunci tidak tersedia, atau dekripsi gagal, plaintext tidak dikirim ke view. Pengguna yang assignment-nya valid tetapi password tidak bisa dibuka mendapat pesan untuk menghubungi admin.

## Perubahan database

Tidak ada perubahan skema database. Tidak diperlukan migrasi SQL.

## Pemeriksaan lanjutan

UAT browser terautentikasi perlu mencakup pengguna yang memiliki assignment, pengguna tanpa assignment, dan kondisi kunci enkripsi tidak tersedia/salah. UAT tersebut belum dilakukan.
