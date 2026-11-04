# Development Aplikasi Master Barang Persediaan, Akun, dan HPP

## Ringkasan
Route `master_barang` dan alias `purchase/listBarang` sekarang menyimpan pengaturan sifat barang, metode HPP, dan kode akun per barang pada `tbpo_barang`.

## Route dan File
- Route: `master_barang`
- Alias route: `purchase/listBarang`
- Controller: `application/controllers/keuangan/C_Keuangan.php`
- Model: `application/models/M_Keuangan.php`
- View: `application/views/content/keuangan/master_barang.php`
- AJAX: `application/views/content/keuangan/ajax/ajax_master_barang.php`

## Perubahan Aplikasi
- Tab `Kode Akun dan HPP` tidak lagi read-only.
- Section `Sifat` membaca dan menyimpan:
  - `is_inventori`
  - `is_beli`
  - `is_jual`
- Untuk section `Sifat`, nilai `F` ditampilkan sebagai checkbox tercentang sesuai permintaan user. Nilai `T` berarti barang dapat digunakan.
- Section `Harga Pokok` membaca dan menyimpan:
  - `hpp_average`
  - `hpp_fifo`
  - `hpp_lifo`
- Section `Harga Pokok` dipaksa memilih tepat satu metode. Validasi dilakukan di frontend dan backend.
- Section `Kode Akun` memakai dropdown dari `tbkeu_akun`, dengan nilai simpan berdasarkan `kode_akun`.
- Deskripsi akun di kanan dropdown berubah mengikuti kode akun yang dipilih.

## Field Kode Akun Barang
- `kode_akun_harga_pokok`
- `kode_akun_penjualan`
- `kode_akun_persediaan`
- `kode_akun_pengiriman_beli`
- `kode_akun_pengiriman_jual`
- `kode_akun_retur_penjualan`

## Validasi Backend
- HPP wajib tepat satu dari `Average`, `FIFO`, atau `LIFO`.
- Kode akun yang dipilih harus ada di `tbkeu_akun`.
- Bila field baru belum ada di database, model memberi fallback agar halaman tetap dapat dibuka sebelum migration dijalankan.
- User `LOGISTIK` tetap hanya dapat menyimpan tab `Info Lain`; perubahan Sifat, HPP, dan Kode Akun hanya untuk akses full edit.

## Cara Pakai
1. Buka route `master_barang` atau `purchase/listBarang`.
2. Pilih barang dari daftar kiri.
3. Buka tab `Kode Akun dan HPP`.
4. Centang `Disimpan`, `Dibeli`, atau `Dijual` bila barang tidak dapat digunakan untuk sifat tersebut.
5. Pilih tepat satu metode HPP: `Average`, `FIFO`, atau `LIFO`.
6. Pilih kode akun pada setiap baris `Kode Akun`.
7. Klik `Rekam`.

## Catatan Implementasi
- Default data lama:
  - `is_inventori`, `is_beli`, `is_jual` = `T`
  - `hpp_average` = `T`
  - `hpp_fifo`, `hpp_lifo` = `F`
- Default kode akun mengikuti tampilan lama:
  - Harga Pokok: `51030`
  - Penjualan: `41032`
  - Persediaan: `14030`
  - Pengiriman Beli: `51032`
  - Pengiriman Jual: `64030`
  - Retur Penjualan: `41034`
