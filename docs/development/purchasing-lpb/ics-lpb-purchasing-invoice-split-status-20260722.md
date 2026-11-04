# Development - ICS LPB Purchasing Invoice Split Status

Tanggal: 2026-07-22

## Route Terdampak

- `ics/icspo`
- `ics/detail_po`
- `ics/detail_record_lpb`

## Ringkasan

Perubahan ini menyesuaikan alur Purchasing untuk LPB yang sudah direkam dari draft temporary penerimaan.

1. `ics/icspo`
   - Tabel Purchasing tidak lagi menampilkan kolom `LPB Status`.
   - Ditambahkan kolom `Invoice` supaya nomor invoice utama terlihat langsung dari daftar Purchasing.
   - Status teknis LPB tetap dipakai di backend dan detail, tetapi tidak menjadi kolom daftar Purchasing.

2. `ics/detail_po`
   - Proses finalisasi draft temporary penerimaan tetap membuat LPB dengan `tb_lpb.status_lpb = 1`.
   - Response sukses diperjelas menjadi LPB tersimpan dengan status `POST`.

3. `ics/detail_record_lpb`
   - Ditambahkan panel `List Invoice LPB` sebelum card `Detail LPB` pada mode Purchasing.
   - Panel ini menampilkan semua LPB/invoice untuk `kd_po` yang sama, termasuk invoice hasil pecah.
   - Kolom status pada panel `List Invoice LPB` disembunyikan supaya list fokus pada nomor LPB, invoice, faktur, dan total qty.
   - Badge status LPB setelah title card `Detail LPB` disembunyikan.
   - Klik baris invoice akan membuka detail LPB sesuai invoice tersebut.
   - Tombol `Update Invoice`, `Update Faktur`, dan `Pecah Invoice` tetap aktif untuk Purchasing meskipun LPB berstatus `POST`.
   - Tombol `Rekam` Purchasing dihilangkan.
   - Proses update invoice, update faktur, dan pecah invoice tidak mengubah status LPB.
   - LPB hasil pecah invoice mewarisi status LPB asal dan metadata verifikasi harga tetap dipertahankan.

## File Diubah

- `application/views/content/logistik/ics/icspo.php`
- `application/views/content/logistik/ics/detail_record_lpb.php`
- `application/controllers/logistik/C_Ics.php`
- `application/models/M_Logistik.php`

## Catatan Teknis

- Guard status `UNPOST` dihapus hanya untuk endpoint finansial Purchasing:
  - `ajax_update_invoice`
  - `ajax_update_faktur_pajak`
  - `ajax_split_lpb_multiple_invoice`
- Guard status untuk update harga, update nomor/jenis LPB, update SJ, dan edit detail penerimaan tetap dipertahankan.
- `split_lpb_multiple_invoice()` sekarang memakai `status_lpb` dari LPB asal untuk header LPB hasil split.
- `harga_verified_by` dan `harga_verified_at` detail LPB tidak dikosongkan saat pecah invoice karena proses ini bukan proses verifikasi ulang harga.
