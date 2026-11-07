# Development Aplikasi Master Barang Komersil

## Ringkasan
Route `master_barang` dan alias `purchase/listBarang` sekarang memakai sumber data `tbpo_barang` untuk tampilan Master Barang Komersil.

## Perubahan Utama
- Judul halaman memakai `Master Barang` dengan tombol ikon home untuk kembali ke dashboard sesuai akses user.
- Halaman `master_barang` diubah dari tabel DataTables menjadi layout daftar barang di kiri dan form detail di kanan.
- Detail kanan mengikuti tiga tab:
  - `Informasi Barang`
  - `Kode Akun dan HPP`
  - `Info Lain`
  - `Gambar`
- Daftar kiri menampilkan kode barang, nama barang, dan nama supplier utama.
- Status barang ditampilkan sebagai pilihan `Status Barang : Aktif` dan `Tidak Aktif`; kedua checkbox dibuat saling eksklusif.
- Tab `Kode Akun dan HPP` menampilkan panel referensi seperti aplikasi lama: `Sifat`, metode `Harga Pokok`, dan daftar kode akun default.
- Form detail memuat data:
  - `kode_barang`
  - `nama_barang`
  - `satuan`
  - `kelompok_barang`
  - `kategori_barang`
  - `bhn_aktif`
  - `merk_barang`
  - `kd_suplier`
  - `stock_minimum`
  - `produk_fokus`
  - `panjang`
  - `lebar`
  - `tinggi`
  - `berat`
  - `isi`
  - `kemasan`
  - `is_active`
  - `is_lot`

## Hak Akses
- `ADMINKEU`, `ADMINKEUTC`, `ADMINPURCHASING`, atau user dengan `lv = 1` dapat:
  - memilih data
  - tambah data baru
  - edit semua field
  - hapus data lewat endpoint backend
- `LOGISTIK` hanya dapat:
  - membuka daftar barang
  - melihat semua tab
  - menyimpan perubahan pada tab `Info Lain` saja
- Untuk `LOGISTIK`, field di tab `Informasi Barang` otomatis baca-saja.
- Pada form baru, status default adalah `Aktif`.

## File yang Diubah
- `application/controllers/keuangan/C_Keuangan.php`
- `application/models/M_Keuangan.php`
- `application/views/content/keuangan/master_barang.php`
- `application/views/content/keuangan/ajax/ajax_master_barang.php`

## Cara Pakai
1. Buka route `master_barang` atau `purchase/listBarang`.
2. Cari barang dari kolom `Search`.
3. Klik salah satu item di panel kiri.
4. Ubah data pada form kanan sesuai hak akses user.
5. Klik `Rekam` untuk menyimpan.
6. Klik `Baru` untuk input barang baru jika user memiliki akses penuh.
7. Klik `Batal` untuk mengembalikan form ke data aktif.

## Catatan Implementasi
- Daftar supplier utama diambil dari `tb_suplier`.
- Preview tab `Gambar` masih memakai placeholder logo Karisma karena `tbpo_barang` belum memiliki field file gambar.
- Validasi duplikasi `kode_barang` dilakukan saat tambah dan edit.
- Tab `Kode Akun dan HPP` sudah dilanjutkan menjadi input Sifat, Harga Pokok, dan Kode Akun pada development `docs/development/master_barang_persediaan_akun_hpp_20260717.md`.
