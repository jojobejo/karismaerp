# User Management memakai `tb_users`

Tanggal: 29 Oktober 2026

## Ringkasan

Route `master/user-management` sekarang membaca dan mengelola akun dari `tb_users`. Daftar akun, pencarian, filter, detail, tambah, ubah, hapus, ubah status, dan reset password seluruhnya menggunakan ID pada `tb_users`. Tombol **Akses Aplikasi** membawa `source=tb_users` agar pengaturan Portal memilih akun yang sama.

Kolom utama halaman mengikuti pemetaan: Nama=`nama_lngkp`, Username=`username`, NIK=`nik`, Bagian=`bagian`, Departemen=`departemen`, dan Jabatan=`jabatan`. Field level, status, akses aplikasi, dan aksi tetap tersedia sebagai kontrol akun. Jobdesk HRD dihapus dari tampilan dan form; kolom `jobdesk_hrd` yang mungkin sudah berisi data tidak diubah oleh modul ini. Penamaan departemen memakai `departemen`; model mendukung nama kolom lama `departement` pada skema acuan Agustus. Password baru serta reset password disimpan menggunakan `password_hash` agar dapat diverifikasi oleh autentikasi yang tersedia.

## Cara Penggunaan

1. Masuk ke KARISMA ERP dengan akun yang memiliki izin `master/user-management`.
2. Buka **Master > User Management** atau route `master/user-management`.
3. Gunakan pencarian atau filter Departemen, Level, dan Status untuk menemukan akun.
4. Pilih **Tambah User** untuk membuat akun baru. Isi identitas, bagian organisasi, level, dan password.
5. Gunakan tombol ubah untuk memperbarui data, kunci untuk mereset password, tombol daya untuk mengaktifkan/menonaktifkan, dan tempat sampah untuk menghapus akun.
6. Pilih ikon gear pada kolom **Akses Aplikasi** untuk mengatur aplikasi Portal bagi akun `tb_users` tersebut.

## Implementasi

- `application/models/master/M_Usermanagement.php`: sumber CRUD dipatok ke `tb_users` dan field disesuaikan dengan skema tabel.
- `application/controllers/master/C_Usermanagement.php`: validasi input identitas dan level, serta aksi CRUD.
- `application/views/content/master/usermanagement/index.php` dan `user_fields.php`: filter, tabel, dan form berbasis field `tb_users`.
- `application/config/routes.php`: route `master/user-management` dan endpoint CRUD yang sudah ada tetap digunakan.

## Batas Validasi

Tidak dilakukan uji browser atau uji CRUD terautentikasi. Perlu validasi pada lingkungan dengan skema dan data aktual, khususnya nilai `level`, duplikasi username/NIK, serta efek penghapusan akun yang mungkin direferensikan proses lain.
