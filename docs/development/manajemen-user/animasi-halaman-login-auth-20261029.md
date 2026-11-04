# Animasi Halaman Login Auth

Tanggal: 2026-10-29

## Perubahan

Halaman `/auth` memperoleh animasi dekoratif pada panel visual: cahaya latar bergerak lembut, grid dan lingkaran orbit bergerak perlahan, ikon modul mengambang, serta konten muncul dengan transisi naik dan pudar. Panel form juga muncul secara halus.

Animasi dekoratif disembunyikan pada layar ponsel sempit. Pengaturan sistem `prefers-reduced-motion` mematikan animasi untuk pengguna yang memilih gerakan minimal.

Referensi tombol lupa password di JavaScript juga dihapus karena tombolnya telah dihilangkan dari halaman pada perubahan sebelumnya. Ini mencegah error JavaScript yang menghentikan handler login dan tombol tampil/sembunyikan password.

## Berkas Aplikasi

- `application/views/content/login/body.php`: menambahkan elemen dekorasi orbit dan ikon yang tidak dibaca pembaca layar.
- `application/views/partial/login/header.php`: menambahkan animasi CSS, tata letak responsif, dan dukungan gerakan minimal.
- `application/views/partial/login/footer.php`: menghapus event handler tombol lupa password yang elemennya sudah tidak tersedia.

Tidak ada perubahan pada proses autentikasi atau tampilan form kredensial.

## Validasi

Belum dilakukan pengujian browser pada berbagai ukuran layar. Periksa desktop, tablet, ponsel, serta pengaturan gerakan minimal sebelum rilis.
