# Flow UAT Modul dan Database Accounting

## Tujuan dan gate kelulusan

UAT membuktikan integritas transaksi, bukan hanya tampilan. Modul dinyatakan layak go-live bila seluruh skenario wajib lulus, seluruh query DB-01 s.d. DB-19 `PASS`, tidak ada exception `OPEN`, dan Finance serta IT menandatangani bukti UAT.

Lingkungan UAT harus memakai salinan database produksi yang dianonimkan. Jangan menjalankan skenario pembuatan/reversal pada database produksi.

## Persiapan

1. Backup database UAT.
2. Jalankan migration berurutan:
   - `sales_logistics_compatibility_20260715.sql`
   - `accounting_jurnal_accounts_20260713.sql`
   - `accounting_jurnal_master_options_20260713.sql`
   - `accounting_general_ledger_journal_20260713.sql`
   - `accounting_runtime_full_20260713.sql`
   - `accounting_hardening_20260715.sql`
3. Sesuaikan `@cutover_date` pada `accounting_uat_database_20260715.sql`.
4. Pastikan periode tanggal UAT `OPEN`, mapping wajib aktif, dan akun mapping bertipe `POSTING`.
5. Siapkan user Finance, Sales, Logistik, dan user tanpa akses.

## Skenario UAT modul

| ID | Aktor | Skenario | Ekspektasi wajib |
|---|---|---|---|
| M-01 | Finance | Buat jurnal manual balance | Tersimpan `DRAFT`; belum masuk laporan |
| M-02 | Finance | Post jurnal M-01 | Menjadi `POSTED`; log created+posted; masuk laporan sekali |
| M-03 | Finance | Jurnal tidak balance/satu baris nol | Ditolak dan tidak ada header/detail parsial |
| M-04 | Finance | Post draft setelah period ditutup | Ditolak `PERIOD_NOT_OPEN` |
| M-05 | Finance | Buat periode overlap | Ditolak `PERIOD_OVERLAP` |
| M-06 | Finance | Close period yang masih memiliki draft | Ditolak `PERIOD_HAS_DRAFT` |
| M-07 | Finance | Reversal tanpa alasan | Ditolak; tidak ada jurnal pembalik |
| M-08 | Finance | Reversal valid lalu ulangi | Satu reversal; percobaan kedua ditolak; neto akun nol |
| M-09 | Sales/Logistik | Konfirmasi DO dengan faktur final | Terbentuk `SALES_INVOICE` dan `GOODS_ISSUE` dari tabel faktur final |
| M-10 | Sales | Ulangi konfirmasi/event faktur yang sama | ID jurnal lama dikembalikan; tidak ada duplikasi |
| M-11 | Sales | Faktur cancelled/detail kosong | Tidak ada jurnal; exception menjelaskan sumber tidak postable |
| M-12 | Logistik | Finalisasi LPB seluruh harga tersedia | Satu `GOODS_RECEIPT`; nilai = qty × harga perolehan |
| M-13 | Logistik | Finalisasi LPB dengan satu harga kosong | LPB tersimpan; jurnal tidak dibuat; exception `LPB_COST_UNRESOLVED` |
| M-14 | Finance | Lengkapi harga M-13 lalu Retry | Exception resolved dan jurnal terbentuk tepat sekali |
| M-15 | Finance | Alokasi pembayaran <= outstanding | Payment, allocation, dan jurnal balance; aging invoice berkurang |
| M-16 | Finance | Alokasi melebihi outstanding | Ditolak `PAYMENT_EXCEEDS_OUTSTANDING`; tidak ada data parsial |
| M-17 | Security | User non-keuangan mengakses `/accounting` | HTTP 403 |
| M-18 | Security | POST tanpa/salah token accounting | HTTP 403 `INVALID_CSRF_TOKEN` |
| M-19 | Security | Panggil `/accounting/auto-post` | Route tidak tersedia; simulator hanya `accounting-test` |
| M-20 | Finance | Neraca saldo, GL, laba rugi, neraca | Debit=kredit; GL punya saldo berjalan; neraca kumulatif |

## Urutan eksekusi per event bisnis

1. Catat nomor sumber dan saldo laporan sebelum test.
2. Jalankan aksi dari UI modul asal, bukan insert langsung ke jurnal.
3. Catat response accounting, `id_jurnal`, `nomor_jurnal`, dan exception bila ada.
4. Cocokkan header jurnal dengan detail dan tabel final sumber.
5. Ulangi event yang sama untuk membuktikan idempotensi.
6. Jalankan SQL UAT database.
7. Lampirkan screenshot UI, hasil query, dan keputusan tester.

## UAT database

Jalankan:

```bash
/Applications/XAMPP/xamppfiles/bin/mysql -uroot NAMA_DB_UAT \
  < docs/database/accounting_uat_database_20260715.sql
```

Kelompok pemeriksaan:

- DB-01–DB-06: schema, balance, detail, idempotensi, orphan.
- DB-07–DB-10: periode dan reversal.
- DB-11–DB-12: mapping akun.
- DB-13–DB-14: rekonsiliasi faktur dan HPP.
- DB-15: pembayaran/alokasi.
- DB-16: rekonsiliasi qty on-hand stock. DB-16B menampilkan `REVIEW` mismatch reservation legacy untuk ditangani oleh UAT modul stok; ini bukan saldo general ledger.
- DB-17: exception queue.
- DB-18–DB-19: kelengkapan posting sejak cutover.
- DB-20: compatibility schema Sales Order/DO yang menjadi sumber accounting.

Query rekonsiliasi hanya membuktikan jurnal yang ada dan coverage sejak cutover. Backfill transaksi sebelum cutover harus menjadi proyek terpisah dengan approval Finance.

## Form bukti UAT

| Field | Isi |
|---|---|
| Environment / DB | |
| Commit / versi migration | |
| Cutover date | |
| ID skenario | |
| Dokumen sumber | |
| Jurnal / exception | |
| Expected vs actual | |
| Bukti screenshot/query | |
| Status PASS/FAIL | |
| Tester dan waktu | |

## Deployment dan rollback

1. Freeze transaksi singkat dan backup database.
2. Deploy source code, lalu jalankan migration hardening.
3. Jalankan SQL UAT DB; jangan buka akses bila ada `FAIL`.
4. Smoke test M-01, M-03, M-09 (dokumen UAT), M-12, dan M-18.
5. Aktifkan menu produksi `/accounting`; batasi `/accounting-test`.

Jika deployment gagal sebelum transaksi baru dibuat, kembalikan source code lalu jalankan `accounting_hardening_rollback_20260715.sql`. Jika jurnal baru sudah dibuat, jangan menghapusnya dengan rollback; hentikan posting, reversal transaksi yang salah, pulihkan aplikasi, dan audit seluruh source event sejak waktu deploy.
