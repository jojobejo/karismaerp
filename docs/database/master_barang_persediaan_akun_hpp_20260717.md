# Database Master Barang Persediaan, Akun, dan HPP

## Ringkasan
Migration `docs/database/master_barang_persediaan_akun_hpp_20260717.sql` menambahkan struktur baru pada `tbpo_barang` untuk menyimpan sifat barang, metode HPP, dan kode akun per barang.

## Tabel Terdampak
- `tbpo_barang`
- `tbkeu_akun` sebagai tabel referensi dropdown kode akun

## Kolom Baru `tbpo_barang`
| Kolom | Tipe | Default | Fungsi |
| --- | --- | --- | --- |
| `is_inventori` | `ENUM('T','F')` | `T` | Status penggunaan barang sebagai persediaan/inventori. |
| `is_beli` | `ENUM('T','F')` | `T` | Status penggunaan barang pada pembelian. |
| `is_jual` | `ENUM('T','F')` | `T` | Status penggunaan barang pada penjualan. |
| `hpp_average` | `ENUM('T','F')` | `T` | Metode HPP Average. |
| `hpp_fifo` | `ENUM('T','F')` | `F` | Metode HPP FIFO. |
| `hpp_lifo` | `ENUM('T','F')` | `F` | Metode HPP LIFO. |
| `kode_akun_harga_pokok` | `VARCHAR(30)` | `51030` | Kode akun Harga Pokok. |
| `kode_akun_penjualan` | `VARCHAR(30)` | `41032` | Kode akun Penjualan. |
| `kode_akun_persediaan` | `VARCHAR(30)` | `14030` | Kode akun Persediaan. |
| `kode_akun_pengiriman_beli` | `VARCHAR(30)` | `51032` | Kode akun Pengiriman Beli. |
| `kode_akun_pengiriman_jual` | `VARCHAR(30)` | `64030` | Kode akun Pengiriman Jual. |
| `kode_akun_retur_penjualan` | `VARCHAR(30)` | `41034` | Kode akun Retur Penjualan. |

## Aturan Data
- Nilai `T` berarti dapat digunakan.
- Nilai `F` berarti tidak dapat digunakan.
- Untuk `Sifat`, UI menampilkan checkbox tercentang saat field bernilai `F`.
- Untuk HPP, hanya satu field yang boleh bernilai `T`.
- Kode akun mengacu ke `tbkeu_akun.kode_akun`.

## Dampak Data Lama
- Migration mengisi default aman agar barang lama tetap aktif untuk inventori, beli, dan jual.
- Barang lama memakai metode HPP `Average` sebagai default.
- Kode akun default mengikuti tampilan lama pada tab `Kode Akun dan HPP`.

## Rollback
Rollback manual tersedia di bagian bawah file SQL. Jalankan hanya bila fitur ini dibatalkan, karena kolom yang dihapus akan menghapus konfigurasi per barang.
