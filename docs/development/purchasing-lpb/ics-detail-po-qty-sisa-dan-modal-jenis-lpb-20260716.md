# Development ICS - Qty Sisa Detail PO dan Modal Jenis LPB

Tanggal: 2026-07-16

## Route `ics/detail_po`

Form card `Detail Barang PO` sekarang menampilkan kolom `Qty Sisa` di antara `Qty Order` dan `Qty Diterima`.

Data menggunakan field yang sudah tersedia dari query detail PO:

- `qty_kecil` untuk `Qty Order`
- `qty_kecil_sisa` untuk `Qty Sisa`
- `qty_kecil_diterima` untuk `Qty Diterima`

Penambahan kolom juga menyesuaikan index `DataTables` agar kolom status, draft, dan aksi tetap tidak memakai sorting.

## Route `ics/detail_record_lpb`

Modal `Edit Jenis PO / LPB` sekarang menyembunyikan field `Nomor LPB Saat Ini` apabila LPB belum memiliki nomor.

Saat user menyimpan jenis LPB pada kondisi nomor belum dibuat, tampilan tidak lagi menampilkan teks notifikasi `Nomor LPB belum dibuat`. Proses tetap langsung mengirim pilihan `jenis_lpb` ke endpoint update.

## File Aplikasi

- `application/views/content/logistik/ics/detail_po.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`
