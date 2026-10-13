# Unpost Retur Penjualan pada Admin Transaksi

## Tujuan

Fitur unpost retur penjualan pada halaman Admin Transaksi digunakan untuk menghapus jurnal akuntansi retur agar jurnal dapat diposting ulang.

## Perilaku

- Jurnal dengan sumber `SALES` dan tipe `RETUR_PENJUALAN` dibersihkan.
- `status_retur` diubah menjadi `draft` agar transaksi terlihat sebagai belum diposting dan dapat diposting ulang.
- Aktivitas unpost memperbarui kolom audit `update_by_retur` dan `update_at_retur`.
- Tombol repost dapat digunakan untuk membentuk kembali jurnal retur penjualan.

## Catatan Teknis

Tabel `tbrp_retur_penjualan_header` menggunakan pasangan kolom audit `update_by_retur` dan `update_at_retur`, bukan `updated_at`. Penggunaan `updated_at` akan menghasilkan galat MariaDB 1054 karena kolom tersebut tidak tersedia. Enum `status_retur` juga harus memuat nilai `draft`.
