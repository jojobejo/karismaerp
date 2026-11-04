# Development Guard UNPOST LPB Jika Barang Sudah Terjual

## Ringkasan

Pada route `ics/detail_record_lpb`, aksi UNPOST LPB sekarang divalidasi lebih ketat. LPB tidak boleh di-UNPOST apabila detail barang, lot, expired date, dan gudang LPB masih dipakai oleh faktur penjualan aktif.

## Latar Belakang

Sebelumnya faktur LPB masih dapat di-UNPOST meskipun batch barang dari LPB tersebut sudah keluar melalui faktur penjualan. Kondisi ini berisiko membuat data pembelian dibuka kembali, sementara transaksi penjualan yang bergantung pada batch tersebut masih aktif.

## Perubahan Aplikasi

File yang diubah:

- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`

Perubahan teknis:

- Menambahkan pengecekan sebelum `ajax_unpost_lpb()` mengubah `status_lpb` menjadi `UNPOST`.
- Menambahkan helper `get_lpb_sales_unpost_blockers()` di `M_Logistik`.
- Helper memeriksa pemakaian batch LPB pada:
  - `tbso_faktur_penjualan` dan `tbso_faktur_detail` untuk faktur penjualan modern yang belum `cancelled`.
  - `tb_detail_do` untuk alur DO lama dengan `status = 4`.
- Jika ditemukan faktur aktif, sistem mengembalikan pesan error berisi contoh nomor faktur dan instruksi agar faktur penjualan barang tersebut di-unpost terlebih dahulu.
- Guard yang sama juga dipasang di `update_lpb_status()` sebagai pengaman apabila ada caller lain yang mencoba mengubah LPB langsung lewat model.

## Cara Penggunaan

1. Buka `ics/detail_record_lpb`.
2. Pilih LPB berstatus `POST`.
3. Klik `UNPOST`.
4. Isi keterangan UNPOST.
5. Jika barang dari LPB sudah terjual pada faktur aktif, sistem akan menolak UNPOST dan menampilkan nomor faktur terkait.
6. Unpost faktur penjualan yang disebutkan oleh sistem.
7. Ulangi UNPOST LPB setelah faktur penjualan sudah dibatalkan/unpost.

## Catatan Validasi

- Validasi dilakukan berdasarkan kombinasi `kd_barang`, `no_lot`, `expired_date`, dan `gudang_id` bila tersedia.
- Faktur penjualan dengan status `cancelled` tidak memblokir UNPOST LPB.
- Untuk data DO lama, guard membaca `tb_detail_do.status = 4` sebagai indikasi barang sudah keluar/terjual.

