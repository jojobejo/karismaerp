# Panduan Penggunaan Accounting Produksi

Tanggal update: 2026-07-14

## Akses Modul

- URL utama: `accounting`
- Alias keuangan: `keuangan/accounting`
- Alias lama `accounting-test` masih tersedia untuk kompatibilitas, tetapi operasional harian memakai route produksi.

## Urutan Penggunaan Harian

1. Pastikan schema accounting sudah `READY` pada panel validasi runtime.
2. Pastikan periode fiskal transaksi berstatus `OPEN`.
3. Pastikan mapping akun untuk event bisnis sudah aktif.
4. Input jurnal manual sebagai `DRAFT` bila diperlukan.
5. Buka detail jurnal dan klik `Posting Draft` setelah debit/kredit balance.
6. Gunakan reversal untuk koreksi jurnal `POSTED`.
7. Pantau `Posting Exception`; lakukan `Retry`, `Resolve`, atau `Ignore` dengan catatan.
8. Tarik laporan dari menu laporan: buku besar, neraca saldo, laba rugi, neraca, piutang, hutang, kas/bank.

## Jurnal Manual

- Jurnal manual tersimpan sebagai `DRAFT`.
- Minimal dua baris.
- Total debit harus sama dengan total kredit.
- Akun yang bisa dipilih hanya akun `POSTING`, aktif, dan eligible transaksi.
- Jurnal `POSTED` tidak diedit atau dihapus. Koreksi wajib lewat reversal.

## Posting

- Posting manual dilakukan dari modal detail jurnal.
- Posting otomatis memakai event final bisnis:
  - `SALES_INVOICE`
  - `PURCHASE_INVOICE`
  - `GOODS_RECEIPT`
  - `CUSTOMER_PAYMENT`
  - `SUPPLIER_PAYMENT`
  - `SALES_RETURN`
  - `PURCHASE_RETURN`
  - `STOCK_TRANSFER`
  - `STOCK_ADJUSTMENT_IN`
  - `STOCK_ADJUSTMENT_OUT`
- Semua posting memakai `idempotency_key` agar transaksi yang sama tidak membentuk jurnal dobel.

## Periode Fiskal

- Periode baru dibuat melalui panel `Periode Fiskal`.
- Open, close, dan reopen wajib memakai alasan approval.
- Periode `CLOSED` menolak posting baru.
- Reopen dipakai hanya untuk koreksi dengan approval.
- Semua aksi periode dicatat di `tbkeu_periode_fiskal_log`.

## Pembayaran dan Alokasi

- Payment customer menurunkan piutang dan menaikkan kas/bank.
- Supplier payment menurunkan hutang dan menurunkan kas/bank.
- Alokasi tidak boleh melebihi nominal pembayaran.
- Sisa pembayaran disimpan sebagai `unapplied_amount`.
- Detail alokasi tersimpan di `tbkeu_pembayaran_alokasi`.

## Saldo Awal

- Input saldo awal per akun pada tanggal cut-off.
- Satu akun hanya boleh debit atau kredit.
- Total saldo awal satu tanggal harus balance sebelum migrasi.
- Migrasi saldo awal membuat jurnal `OPENING_BALANCE` berstatus `POSTED`.
- Saldo awal yang sudah dimigrasikan tidak dapat diubah dari UI/service.

## Exception Posting

- `Retry`: menjalankan ulang payload exception.
- `Resolve`: menutup exception yang sudah diselesaikan manual.
- `Ignore`: menandai exception tidak akan diproses.
- Resolve/ignore wajib memakai catatan.

## Laporan

Semua laporan membaca jurnal `POSTED` saja:

- Buku besar.
- Neraca saldo.
- Laba rugi.
- Neraca.
- Piutang.
- Hutang.
- Kas/bank.

## Integrasi Event Final

Hook produksi non-blocking sudah dipasang pada:

- DO confirm sales action `siap` -> event `SALES_INVOICE`.
- LPB final -> event `GOODS_RECEIPT`.

Jika nominal/mapping belum lengkap, jurnal tidak dipaksakan. Payload gagal masuk ke dashboard exception agar bisa diperbaiki dan di-retry.

