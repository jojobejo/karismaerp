# Development List Revisi Harga LPB

Tanggal: 2026-09-01

## Ringkasan

Modul `List Revisi Harga LPB` ditambahkan pada route `ics/icspo` untuk mengawal revisi harga LPB yang barangnya sudah pernah terjual ke pelanggan. Modul ini memisahkan permintaan Purchasing, proses unpost faktur oleh Accounting, notifikasi balik ke Purchasing, dan penyelesaian revisi LPB.

## Route

- `ics/lpb_revision`
- `ics/lpb_revision/create`
- `ics/lpb_revision/detail`
- `ics/lpb_revision/unpost_faktur`
- `ics/lpb_revision/unpost_lpb`
- `ics/lpb_revision/finish`

Route didaftarkan di:

- `application/config/routes.php`
- `application/modules/kiupo/routes.php`

## File Aplikasi

- `application/models/M_LpbRevisionRequest.php`
- `application/controllers/logistik/C_Ics.php`
- `application/views/content/logistik/ics/icspo.php`
- `application/views/content/logistik/ics/lpb_revision_request.php`

## Alur Penggunaan

1. Purchasing membuka `ics/icspo`.
2. Klik tombol `List Revisi Harga LPB`. Badge pada tombol menampilkan jumlah request revisi yang masih terbuka.
3. Pada halaman baru, Purchasing melihat daftar LPB kandidat, yaitu LPB POST yang detail barang/lot/expired-nya sudah muncul pada faktur penjualan aktif.
4. Purchasing klik tombol kirim request, mengisi alasan revisi harga, lalu sistem menyimpan header request dan detail faktur penjualan terkait.
5. Accounting membuka detail request dan melakukan `Unpost Faktur` per nomor faktur.
6. Untuk faktur modern `tbso_faktur_penjualan/tbso_faktur_detail`, proses memakai `M_SalesOrder::repost_item_faktur()` agar item faktur kembali ke SO, stok kembali, dan jurnal faktur diregenerasi sesuai fungsi existing.
7. Untuk data legacy `tb_detail_do`, status detail DO diubah dari `4` menjadi `2` agar tidak lagi memblokir LPB. Jalur ini tetap ditandai lewat `source_table` pada detail request.
8. Jika semua faktur/item request sudah `UNPOSTED`, status request berubah menjadi `READY_LPB_UNPOST`. Ini menjadi notifikasi bagi Purchasing.
9. Purchasing melakukan `Unpost LPB` dari detail request. Sistem memakai `M_Logistik::update_lpb_status()` dan membuat reversal jurnal LPB aktif melalui service accounting existing.
10. Purchasing membuka detail LPB, merevisi harga/data LPB, lalu merekam LPB kembali menjadi POST dari workflow LPB existing.
11. Setelah LPB tersimpan kembali menjadi POST, Purchasing menandai request sebagai `REVISION_DONE`.

## Status Request

- `REQUESTED`: request baru dibuat Purchasing.
- `ACCOUNTING_PROCESS`: sebagian faktur sudah di-unpost Accounting.
- `READY_LPB_UNPOST`: seluruh faktur request sudah di-unpost, Purchasing dapat unpost LPB.
- `LPB_UNPOSTED`: LPB sudah di-unpost untuk proses revisi.
- `REVISION_DONE`: LPB sudah direvisi dan request selesai.
- `CANCELLED`: disiapkan sebagai status pembatalan bila nanti dibutuhkan.

## Validasi dan Batasan

- Request hanya dibuat untuk LPB berstatus POST.
- Request tidak dibuat jika LPB belum punya faktur penjualan aktif berdasarkan barang, lot, expired, dan gudang.
- Satu LPB hanya boleh memiliki satu request aktif.
- Penyelesaian request hanya bisa dilakukan setelah LPB kembali berstatus POST.
- UAT browser dengan akun Purchasing dan Accounting tetap wajib karena route membutuhkan sesi login aplikasi.
