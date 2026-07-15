# Accounting Readiness Report — 15 Juli 2026

## Status

**Technical readiness: PASS untuk pilot/UAT. Business go-live: menunggu sign-off Finance dan eksekusi skenario M-01–M-20.**

Modul jurnal tidak lagi bergantung pada nominal detail DO, jurnal reversal kembali terbaca benar, nomor jurnal aman concurrency, periode dan pembayaran memiliki close-control, serta simulator terpisah dari route produksi.

## Bukti yang sudah dijalankan

- PHP lint: 367 file dalam `application` tanpa syntax error pada PHP 7.3.
- Migration `sales_logistics_compatibility_20260715.sql`: UP dan rollback lulus pada schema salinan.
- Migration `accounting_hardening_20260715.sql`: UP dan rollback lulus pada schema salinan.
- Database lokal: DB-01 sampai DB-20 `PASS`.
- Posting exception `OPEN`: 0. Satu exception historis `CLI_TEST` ditutup `IGNORED` dengan resolution note.
- Qty on-hand batch vs stock ledger: `PASS`.
- Tidak ditemukan referensi empat tabel PO terlarang pada file accounting/integrasi yang ditambahkan.

## Review yang masih terbuka

1. DB-16B menemukan 88 batch dengan `qty_reserved` berbeda dari net `RESERVE/RELEASE` legacy. Ini domain availability/reservation stock, bukan saldo general ledger. Rekonsiliasi harus dilakukan sebelum modul stok dijadikan single source of truth.
2. LPB sebelum cutover memiliki harga perolehan nol/tidak tersedia. Tidak dilakukan backfill otomatis. Finance/Purchasing harus menetapkan nilai dan approval bila transaksi lama akan dimigrasikan.
3. DB-13/DB-14 hanya memeriksa jurnal yang sudah terbentuk; DB-18/DB-19 mengontrol coverage mulai `@cutover_date`.
4. Klik-through UAT M-01–M-20 dan tanda tangan Finance/IT belum dapat digantikan oleh static/database test.

## Keputusan go-live yang disarankan

- **Boleh:** pilot jurnal manual, laporan, faktur/GOODS_ISSUE, dan LPB exception workflow di lingkungan UAT.
- **Belum boleh:** financial close produksi sebelum semua M-01–M-20 lulus, mapping akun disetujui Finance, dan exception queue nol.
- **Terpisah:** rekonsiliasi 88 stock reservation mismatch harus masuk backlog/UAT modul stok; tidak boleh diperbaiki massal tanpa keputusan status SO/DO sumber.

Backup lokal sebelum migration tersimpan sementara di:

- `/tmp/karismaerp_accounting_before_hardening_20260715.sql`
- `/tmp/karismaerp_sales_before_compatibility_20260715.sql`

Untuk server UAT/produksi, buat backup baru; jangan mengandalkan file `/tmp` lokal tersebut.

