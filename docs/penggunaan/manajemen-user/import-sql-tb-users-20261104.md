# Cara menggunakan Import SQL User

1. Masuk sebagai `admin`, lalu buka `master/user-management`.
2. Klik **Import SQL User** dan pilih dump phpMyAdmin `tb_users` atau file `INSERT` `.sql` UTF-8 maksimal 2 MB.
3. Klik **Periksa SQL**. Tinjau jumlah baris baru, baris yang dilewati, NIK ganda, dan departemen kosong.
4. Jika hasilnya sesuai, klik **Import User** dan konfirmasi. Berkas yang diterapkan harus sama dengan yang diperiksa; pemeriksaan berlaku 10 menit.
5. Muat ulang daftar user dan periksa akun yang baru masuk.

File harus berisi `INSERT INTO tb_users ... VALUES` dengan daftar kolom yang sesuai tabel. Contoh:

```sql
INSERT INTO `tb_users`
  (`username`, `password`, `nama_lngkp`, `nik`, `bagian`, `departement`, `jabatan`, `level`, `status`)
VALUES
  ('user_baru', 'password_awal_kuat', 'Nama Pengguna', 'NIK001', 'IT', 'IT', 'STAFF', 2, 1);
```

Nilai password teks biasa minimal enam karakter akan di-hash saat impor. Hash PHP yang dikenali tetap digunakan. Akun dengan username yang sudah ada tidak ditimpa, termasuk `admin`. ID dan waktu dari dump dipertahankan untuk akun baru; benturan ID dengan akun berbeda membatalkan impor. NIK ganda serta departemen kosong pada data lama ditampilkan sebagai catatan pratinjau.

Dump phpMyAdmin seperti `tb_users (1).sql` dapat langsung dipilih. Definisi tabel, indeks, dan perintah pengaturan sesi dalam dump **tidak dijalankan**; hanya baris `INSERT` user yang dipakai. File berisi `DROP`, `UPDATE`, `DELETE`, atau perintah untuk tabel lain tetap ditolak. Minta pengguna mengganti password awal setelah login pertama.
