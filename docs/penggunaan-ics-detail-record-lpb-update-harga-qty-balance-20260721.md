# Penggunaan - ICS Detail Record LPB Update Harga dan Qty Balance

Tanggal: 2026-07-21

## Route

`ics/detail_record_lpb`

## Cara Penggunaan

1. Buka `ics/detail_record_lpb` untuk PO yang memiliki LPB.
2. Pilih LPB dengan status `UNPOST`.
3. Pada tampilan Purchasing/Admin, klik tombol edit pada baris detail LPB.
4. Modal `Update Harga Detail LPB` akan tampil.
5. Ubah `Qty LPB` jika diperlukan.
6. Ubah `Harga Satuan Baru`.
7. Pastikan `Total Harga Baru` sudah sesuai.
8. Klik `Simpan Harga & Qty`.

## Notifikasi Validasi

Jika Qty LPB baru membuat total kode barang melebihi total LPB/qty diterima pada detail LPB, sistem menolak simpan dan menampilkan pesan validasi.

Jika Qty LPB berhasil disimpan tetapi total kode barang masih belum balance dengan total LPB/qty diterima awal, sistem menampilkan notifikasi warning.

## Catatan

Update ini belum berarti harga sudah diverifikasi.

Setelah harga dan qty benar, Purchasing/Admin tetap perlu menjalankan proses Accept sesuai workflow yang sudah ada.
