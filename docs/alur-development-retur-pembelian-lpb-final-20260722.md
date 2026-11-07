# Alur Development Retur Pembelian LPB Final

Tanggal: 2026-07-22

## Alur Teknis

1. User membuka `ics/retur/pembelian`.
2. View mencari LPB final melalui endpoint `lpb_select2`.
3. Sistem hanya menampilkan LPB yang memiliki `nomor_lpb`.
4. User memilih LPB, lalu endpoint `lpb_detail` mengambil item dari `tb_lpb_detail`, batch dari `tb_lpb_batch`, dan stok dari `tberp_stock_batch`.
5. User mengisi qty retur dan alasan.
6. Endpoint `create_draft` memvalidasi qty, batch, stok, harga, lalu menyimpan header/detail.
7. Draft dapat di-submit.
8. Purchasing memverifikasi supplier, harga, alasan retur, dan jenis penyelesaian.
9. Accounting memverifikasi dampak Hutang Usaha dan PPN.
10. Posting membuat stock ledger `RBELI`, mengurangi batch stock, dan membuat jurnal `PURCHASE_RETURN`.
11. Jika posting tidak aman, sistem mencatat `tbkeu_posting_exception`.
12. Jika dokumen posted dibatalkan, sistem membuat reversal jurnal dan ledger pembalik, lalu status menjadi `VOID`.

## Status Dokumen

- `DRAFT`
- `SUBMITTED`
- `PURCHASING_VERIFIED`
- `ACCOUNTING_VERIFIED`
- `POSTED`
- `POSTING_EXCEPTION`
- `VOID`

## Rule Jurnal Saat Ini

Kelompok dagang `2`:

- Debit `21098` Hutang Usaha
- Kredit `14010` Persediaan #1
- Kredit `13017` PPN Masukan / PPN M Ymh Diterima

Kelompok dagang `3`:

- Debit `21098` Hutang Usaha
- Kredit `14011` Persediaan Brg Dagangan BKPS

Kelompok dagang lain belum diposting otomatis.

## Batasan Aman

Jenis penyelesaian selain `POTONG_HUTANG` belum diposting otomatis karena butuh mapping akun final untuk klaim supplier, kas/bank refund, atau replacement.
