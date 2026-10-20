# Riwayat Pengembangan Login, Portal, dan Navigasi Admin

**Tanggal pencatatan:** 20 Oktober 2026  
**Area:** autentikasi, portal aplikasi, navigasi administrasi, dan pengelolaan pengguna.

## Riwayat Perubahan

### 1. Login menjadi gerbang portal

- Halaman awal diarahkan ke login KARISMA ERP.
- Halaman login diperbarui dengan identitas visual Karisma, tata letak responsif, tombol tampilkan password, indikator proses, dan submit AJAX.
- Setelah autentikasi berhasil, pengguna diarahkan ke `/portal` untuk melihat daftar aplikasi.
- Akses portal tanpa sesi login diarahkan kembali ke halaman login.

### 2. Sumber akun dan pengelolaan pengguna

- Sesi pengguna bersumber dari akun pada `tb_karyawan`; modul `master/user-management` yang telah tersedia juga mengelola akun pada tabel tersebut.
- Pada database lokal `kiucoid_karismaerp`, akun `admin` ID 46 diaktifkan dan password disetel ke `1234`, tersimpan menggunakan `password_hash`.
- Username `admin` telah ditemukan pada lebih dari satu baris. Pencarian akun `tb_karyawan` dibuat deterministik dengan memilih ID terendah. Tidak ada akun yang ditambahkan atau dihapus.

### 3. Navigasi khusus admin

- Sidebar menampilkan grup navigasi manajemen hanya jika username sesi adalah `admin`.
- Grup navigasi berisi User Management (`master/user-management`) dan Module Management (`master/menu`).
- Halaman `master/menu` diberi label Module Management.
- Pada halaman `/portal`, navigasi admin ditempatkan di samping label **System Online** dan hanya dirender untuk username `admin`.
- Ikon/logo sidebar mengarah ke `/portal`.

### 4. Penanganan sesi habis dan perbaikan bug

- Portal, User Management, Module Management, helper autentikasi, dan pemeriksaan akses mengarahkan pengguna tanpa sesi ke login dengan notifikasi **“Sesi Anda berakhir. Silakan login ulang.”**
- Error sidebar `Undefined variable: allowed_roles` diperbaiki dengan mengembalikan deklarasi daftar role yang masih digunakan oleh menu role lain.

## Pemeriksaan

- Pemeriksaan sintaks PHP berhasil untuk controller, helper, library, dan view yang diubah.
- Hash password akun lokal berhasil diverifikasi terhadap password awal yang diminta.
- Login, tampilan navigasi berdasarkan username, dan alur sesi habis belum diuji melalui browser.

## Dokumentasi Terkait

- Rincian implementasi dan penggunaan: `docs/development/login-portal-user-management.md`.
- Perubahan data akun serta keterangan tidak ada perubahan skema: `docs/database/login-portal-user-management.md`.
