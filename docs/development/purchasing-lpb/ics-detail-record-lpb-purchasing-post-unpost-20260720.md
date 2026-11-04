# Development ICS Detail Record LPB - Purchasing POST/UNPOST

Tanggal: 2026-07-20
Route: `ics/detail_record_lpb`
Akses: Purchasing

## Ringkasan

Perubahan ini memperjelas workflow Purchasing pada detail record LPB:

- Tombol pensil kuning pada tabel Purchasing tetap membuka modal update harga detail LPB.
- Saat detail LPB berstatus `UNPOST`, kolom `#` tampil sebagai tombol pensil kuning untuk edit detail.
- Saat detail LPB berstatus `POST`, kolom `#` disembunyikan.
- Tombol biru icon mata ditambahkan pada header aksi Purchasing untuk menampilkan log aktivitas detail PO/LPB.
- Tombol `Update Invoice` dan `Update Faktur` dibuat disabled saat status LPB `POST`.
- Tombol bawah tabel Purchasing tidak lagi tampil sebagai tombol pensil `Edit` atau tombol merah `Unpost`; tombol dibuat konsisten sebagai `Rekam`.
- Jika semua harga sudah diverifikasi dan status masih `UNPOST`, tombol `Rekam` mengubah status LPB menjadi `POST`.
- Jika tombol `Rekam` masih harus melakukan bulk verifikasi harga detail, proses tersebut sekarang langsung dilanjutkan dengan update header `tb_lpb.status_lpb` menjadi `POST` dalam transaksi yang sama.
- Jika status sudah `POST`, tombol merah `UNPOST` ditampilkan di bawah tabel Purchasing untuk mengubah status record kembali menjadi `UNPOST`.

## Modal Update Harga

Modal dari tombol pensil kuning menampilkan input:

- `Kode Barang - Nama Barang`
- `Harga Satuan Sebelumnya`
- `Total Harga Sebelumnya`
- `Qty LPB`
- `Harga Satuan Baru`
- `Total Harga Baru`

Alert modal disesuaikan menjadi:

`harga yang tersimpan saat ini dihapus.`

## Guard Server

Endpoint berikut menolak update jika LPB masih `POST`:

- `ics/ajax_update_lpb_detail_price`
- `ics/ajax_accept_lpb_detail_price`
- `ics/ajax_bulk_accept_lpb_detail_price`
- `ics/ajax_update_invoice`
- `ics/ajax_update_faktur_pajak`

Update baru dapat dilakukan setelah LPB di-`UNPOST`.

## Rekam Purchasing

Endpoint `ics/ajax_bulk_accept_lpb_detail_price` sekarang melakukan dua hal dalam satu transaksi:

- Accept/verifikasi harga semua detail yang dikirim.
- Update `tb_lpb.status_lpb` menjadi `1` (`POST`) setelah seluruh detail valid.

Jika detail yang dikirim berasal dari LPB berbeda, transaksi dibatalkan.

## File Aplikasi

- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`
