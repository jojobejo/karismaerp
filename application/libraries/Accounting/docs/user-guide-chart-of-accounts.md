# User Guide - Chart of Accounts

Tanggal: 2026-07-13

## Tujuan

Dokumen ini menjelaskan penggunaan module `Jurnal` tahap awal untuk mengelola akun jurnal atau Chart of Accounts.

Dokumen user lengkap tersedia di:

`docs/penggunaan-jurnal-akun-keuangan-20260713.md`

## Akses Module

Route:

- `jurnal`
- `keuangan/jurnal`

User yang diizinkan:

- username `admin`;
- session admin dashboard;
- level `1` dengan jobdesk `ADMIN`;
- level `1` dengan jobdesk `ADMINKEU`;
- level `1` dengan jobdesk `ADMINKEUTC`.

## Fitur Utama

### Card Button Master Pendukung

Empat card button setelah title halaman:

- `Klasifikasi`
- `Saldo Normal`
- `Tipe Kontrol`
- `Parent / Subclass`

Setiap card membuka modal CRUD untuk mengelola master pendukung akun.

### Daftar Akun

Menampilkan seluruh akun. User dapat mencari berdasarkan kode, nama, atau klasifikasi.

### Form Akun

Digunakan untuk tambah dan edit akun.

Field:

- `Kode Akun`: kode unik akun.
- `Nama Akun`: nama akun.
- `Klasifikasi`: kelompok laporan.
- `Parent`: akun induk bertipe HEADER.
- `Saldo Normal`: DEBIT atau KREDIT.
- `Tipe Akun`: HEADER atau POSTING.
- `Tipe Kontrol`: fungsi bisnis akun.
- `Aktif`: status akun.
- `Boleh Jurnal Manual`: izin akun dipakai jurnal manual pada tahap berikutnya.

### Tombol Baru

Mengosongkan form untuk membuat akun baru.

### Tombol Simpan

Menyimpan akun baru atau mengupdate akun yang sedang dipilih.

### Tombol Nonaktifkan

Menonaktifkan akun tanpa menghapus data.

### Tombol Hapus

Menghapus akun jika akun belum dipakai jurnal dan tidak memiliki child account.

## Alur Singkat

1. Buka `dashboard`.
2. Pilih tab `KEUANGAN`.
3. Klik `Jurnal`.
4. Kelola master pendukung melalui card button bila perlu.
5. Buat akun HEADER untuk kelompok.
6. Buat akun POSTING di bawah HEADER.
7. Gunakan `Nonaktifkan` jika akun tidak dipakai lagi.

## Prinsip Penting

1. Akun HEADER bukan akun transaksi.
2. Akun POSTING adalah akun transaksi.
3. Parent akun harus HEADER.
4. Kode akun tidak boleh duplikat.
5. Akun yang sudah dipakai tidak boleh dihapus.
6. Akun yang tidak dipakai lagi dinonaktifkan.

## Batasan Tahap Ini

Module ini belum mencakup input jurnal debit/kredit, posting jurnal, periode fiskal, mapping akun, reversal, laporan, dan auto-posting transaksi ERP.
