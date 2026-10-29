# Login Portal dan Pengelolaan Pengguna

Tanggal: 20 Oktober 2026

## Ringkasan

- Halaman awal aplikasi diarahkan ke login KARISMA ERP.
- Login menggunakan form AJAX, indikator proses, pesan koneksi, dan kontrol tampil/sembunyikan password.
- Tampilan menyesuaikan layar desktop dan ponsel, memakai logo Karisma dan palet biru gelap, biru, serta sian.
- Portal mensyaratkan sesi login. Akses tanpa sesi diarahkan kembali ke login.
- Navigasi manajemen hanya tampil untuk username `admin`, tepat di samping label **System Online** pada halaman `/portal`, dengan tautan User Management dan Module Management.
- Ikon/logo di sidebar menuju `/portal`. Jika sesi habis saat membuka portal atau dua modul administrasi, pengguna dialihkan ke login dengan notifikasi untuk login ulang.
- Pengelolaan pengguna tersedia melalui `master/user-management` dengan tabel pencarian, filter, tambah, ubah, hapus, status, dan reset password.

## Cara Penggunaan

1. Buka alamat aplikasi. Halaman login tampil sebagai halaman awal.
2. Masukkan username dan password akun perusahaan, lalu pilih **Masuk ke Portal**.
3. Setelah autentikasi berhasil, aplikasi menampilkan daftar aplikasi dari `Portal.php`.
4. Pengguna berwenang membuka **Master > User Management** atau route `master/user-management` untuk mencari, menambah, memperbarui, menonaktifkan, menghapus, dan mereset password pengguna.

## Implementasi

- `application/config/routes.php`: controller awal menjadi `Auth`.
- `application/controllers/Auth.php`: autentikasi menggunakan sumber akun yang sudah ada dan mengarahkan sesi berhasil ke portal.
- `application/models/M_Auth.php`: pencarian akun `tb_karyawan` diurutkan berdasarkan ID agar username duplikat memilih baris tertua secara konsisten.
- `application/views/partial/login/header.php`, `application/views/content/login/body.php`, dan `application/views/partial/login/footer.php`: antarmuka serta AJAX login.
- `application/controllers/Portal.php`: sesi diwajibkan sebelum menampilkan portal.
- `application/views/partial/main/sidebar.php`: grup navigasi admin menyediakan akses User Management dan Module Management (`master/menu`).
- `application/libraries/Permission.php`, helper autentikasi, serta controller Portal dan modul manajemen: menampilkan notifikasi ketika sesi tidak lagi aktif.
- `application/controllers/Portal.php` dan `application/views/portal/index.php`: navigasi administrasi khusus username `admin` ditempatkan di area System Online.
- `application/controllers/master/C_Menu.php` dan `application/views/content/master/menu/index.php`: halaman pengaturan modul navigasi menggunakan label Module Management.
- `application/controllers/master/C_Usermanagement.php` dan `application/models/master/M_Usermanagement.php`: CRUD pengguna pada `tb_users`, dengan sumber akses aplikasi `tb_users`.

## Batas Validasi

Catatan implementasi 29 Oktober 2026: modul User Management memakai `tb_users`; sebelumnya modul mengelola `tb_karyawan`. Login tetap dapat membaca kedua sumber akun melalui `M_Auth`. Perubahan modul belum menjalani uji browser atau CRUD terautentikasi. Lakukan validasi tersebut pada lingkungan aplikasi sebelum rilis. Rincian field dan cara penggunaan ada di `docs/development/user-management-tb-users-20261029.md`.
