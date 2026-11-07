# Development Code MVC Retur Pembelian LPB Final

Tanggal: 2026-07-22

## Tujuan

Membuat workflow retur pembelian yang bersumber dari LPB final, bukan lagi dari staging `tb_ics_po`. Modul ini menjaga validasi batch, stok fisik, approval Purchasing/Accounting, posting stock ledger `RBELI`, jurnal `PURCHASE_RETURN`, dan reversal jika dokumen sudah posted.

## File MVC

- Controller: `application/controllers/logistik/C_Ics.php`
- Model baru: `application/models/M_ReturPembelian.php`
- View form/list: `application/views/content/logistik/ics/returform_pembelian.php`
- View JavaScript: `application/views/content/logistik/ics/ajax_retur_pembelian.php`
- Route: `application/config/routes.php`

## Route Baru

- `ics/retur/pembelian/lpb_select2`
- `ics/retur/pembelian/lpb_detail`
- `ics/retur/pembelian/create_draft`
- `ics/retur/pembelian/submit`
- `ics/retur/pembelian/verify_purchasing`
- `ics/retur/pembelian/verify_accounting`
- `ics/retur/pembelian/post`
- `ics/retur/pembelian/void`

## Validasi Utama

- LPB wajib memiliki `tb_lpb.nomor_lpb`.
- Item wajib berasal dari `tb_lpb_detail`.
- Lot dan expired wajib cocok dengan `tb_lpb_batch`.
- Qty retur tidak boleh melebihi `qty_diterima - retur sebelumnya`.
- Qty retur tidak boleh melebihi `tberp_stock_batch.qty_on_hand`.
- Harga satuan wajib valid sebelum draft dapat dibuat.
- Posting otomatis hanya untuk `jenis_penyelesaian = POTONG_HUTANG`.
- Kelompok dagang `2` dan `3` dapat diposting otomatis.
- Kelompok dagang selain `2` dan `3` masuk `tbkeu_posting_exception`.

## Posting

Saat status `ACCOUNTING_VERIFIED` diposting:

- `tberp_stock_batch.qty_on_hand` dikurangi.
- `tberp_stock_ledger` ditulis dengan `tipe = RBELI`.
- `Accounting_service::post_retur()` membuat jurnal `PURCHASE_RETURN`.
- Header dikunci secara status menjadi `POSTED`.

## Void Setelah Posting

Dokumen `POSTED` tidak dihapus. Aksi void:

- membuat jurnal reversal lewat `Accounting_service::reversal_journal()`;
- menambah kembali stock batch;
- menulis ledger pembalik `RBELI` dengan qty negatif;
- mengubah status header menjadi `VOID`;
- mencatat log before/after JSON.
