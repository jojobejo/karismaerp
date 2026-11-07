# Penggunaan Master Barang Persediaan, Akun, dan HPP

## Modul
- `master_barang`
- `purchase/listBarang`

## Mengatur Sifat Barang
1. Buka tab `Kode Akun dan HPP`.
2. Pada section `Sifat`, centang pilihan yang ingin dinonaktifkan:
   - `Disimpan`
   - `Dibeli`
   - `Dijual`
3. Checkbox tercentang berarti nilai database `F`, yaitu barang tidak dapat digunakan untuk sifat tersebut.
4. Checkbox tidak tercentang berarti nilai database `T`, yaitu barang dapat digunakan.

## Mengatur Harga Pokok
1. Pilih salah satu metode:
   - `Average`
   - `FIFO`
   - `LIFO`
2. Aplikasi menjaga agar hanya satu metode yang aktif.
3. Jika user mencoba mengosongkan semua pilihan, pilihan terakhir akan tetap aktif.

## Mengatur Kode Akun
1. Pilih kode akun pada setiap baris:
   - `Harga Pokok`
   - `Penjualan`
   - `Persediaan`
   - `Pengiriman Beli`
   - `Pengiriman Jual`
   - `Retur Penjualan`
2. Dropdown membaca data dari `tbkeu_akun`.
3. Nilai yang disimpan adalah `tbkeu_akun.kode_akun`.
4. Nama akun di kolom kanan otomatis mengikuti kode akun yang dipilih.

## Menyimpan
Klik `Rekam` setelah perubahan selesai. Aplikasi akan menolak penyimpanan bila metode HPP tidak tepat satu atau kode akun tidak valid.
