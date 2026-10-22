# Pengembangan Master Organisasi

## Cakupan

Ditambahkan halaman `master/organisasi` untuk mengelola departemen, jobdesk, dan jabatan melalui satu antarmuka bertab. Tombol **Master Organisasi** tersedia sebaris dengan **Tambah User** di halaman User Management.

CRUD departemen menggunakan tabel `tb_departemen`; CRUD jobdesk menggunakan `tb_jobdesk` yang sudah dipakai sistem; CRUD jabatan menggunakan tabel `tb_jabatan` yang dibuat melalui skrip database terpisah. Endpoint modul dilindungi izin `master/user-management`.

## Cara penggunaan

1. Buka **Master > User Management**.
2. Klik **Master Organisasi** di sebelah **Tambah User**.
3. Pilih tab Departemen, Jobdesk, atau Jabatan.
4. Gunakan tombol tambah untuk membuat data, ikon pensil untuk mengubah, dan ikon tempat sampah untuk menghapus.

## Catatan implementasi

Tabel master jabatan belum tersedia dalam skema acuan; perubahan skema disediakan pada `docs/database/master_jabatan_20261021.sql` dan telah diterapkan ke database lokal `kiucoid_karismaerp`. CRUD menolak nama kosong dan nama duplikat. Kolom `jabatan` lama di `tb_users` tidak otomatis diubah atau dipindahkan. Halaman master jabatan mengelola tabel referensi baru.

## Status validasi

UAT browser dengan autentikasi dan eksekusi migrasi database belum dilakukan.
