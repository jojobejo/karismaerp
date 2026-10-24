# Jabatan pada User Management

## Perubahan

Halaman `master/user-management` menampilkan kolom Jabatan pada tabel pengguna dan menyediakan pilihan jabatan pada modal tambah/ubah user. Pilihan berasal dari `tb_jabatan` dan hanya menampilkan data berstatus aktif. Server memvalidasi pilihan terhadap master aktif sebelum menyimpan.

Nilai jabatan disimpan sebagai nama jabatan pada `tb_karyawan.jabatan` agar konsisten dengan pola nilai teks `departemen` dan `jobdesk` pada modul ini. Perubahan kolom database disiapkan terpisah; lihat dokumentasi database.

Saat mengedit user, nilai `jabatan` dari detail dipilih otomatis pada form. Nilai jabatan untuk akun yang sudah ada sebelum kolom database ditambahkan masih kosong; pilih satu kali dan simpan agar menjadi nilai default pada edit berikutnya.

Modal **Tambah User** dan **Ubah User** memakai form serta aksi submit yang berbeda. Form tambah mewajibkan password minimal enam karakter dan menggunakan endpoint `save`. Form ubah tidak menampilkan field password dan menggunakan endpoint `update/{id}`; password yang sudah tersimpan dipertahankan. Penggantian password tetap dilakukan melalui aksi **Reset Password**. Validasi NIK dan username saat edit mengecualikan ID user yang sedang diubah.

## Cara penggunaan

1. Buka **Master > User Management**.
2. Pastikan jabatan yang akan dipakai sudah dibuat dan berstatus aktif pada **Setting Master Kantor > Jabatan**.
3. Klik **Tambah User** untuk membuat akun. Isi NIK, nama, username, password, serta data wajib lainnya, lalu klik **Simpan User**.
4. Klik ikon pensil pada baris user untuk mengubah akun. Form edit tidak meminta password; pertahankan NIK jika tidak berubah, lalu klik **Simpan Perubahan**.
5. Untuk mengganti password, gunakan ikon kunci **Reset Password** pada baris user.

Jabatan nonaktif tidak dapat dipilih untuk user baru maupun saat memperbarui user.

## Validasi

Query DataTables menghitung jumlah hasil dengan query terpisah yang tidak membawa daftar kolom data, lalu membangun ulang query daftar. Kolom jabatan juga tetap kompatibel saat migrasi belum tersedia.

Pemeriksaan lint PHP berhasil. UAT browser terautentikasi belum dilakukan.
