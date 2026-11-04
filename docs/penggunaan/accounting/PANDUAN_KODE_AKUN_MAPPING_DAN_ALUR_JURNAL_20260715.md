# Panduan Kode Akun, Mapping, dan Alur Jurnal Accounting

Tanggal: 2026-07-15  
Scope: module Jurnal/Accounting KARISMA ERP.

Dokumen ini dipakai sebagai pegangan Finance dan IT saat menyiapkan Chart of Accounts, mapping akun, jurnal manual, auto-jurnal, dan laporan Neraca/Laba Rugi.

## Prinsip Utama

1. Module `Jurnal` adalah master Chart of Accounts.
2. Transaksi accounting resmi disimpan di `tbkeu_jurnal` dan `tbkeu_jurnal_detail`.
3. Laporan hanya membaca jurnal berstatus `POSTED`.
4. Jurnal `POSTED` tidak diedit dan tidak dihapus.
5. Koreksi jurnal dilakukan melalui reversal.
6. Auto-jurnal tidak boleh memakai kode akun hardcoded di source code.
7. Auto-jurnal wajib membaca akun dari `tbkeu_mapping_akun`.
8. Setiap jurnal wajib double-entry: total debit sama dengan total kredit.

## Struktur Kode Akun

Kode akun memakai pola umum berikut. Finance boleh menyesuaikan detail akun, tetapi klasifikasi dan saldo normal harus tetap konsisten.

| Kelompok | Contoh Prefix | Saldo Normal | Masuk Laporan |
| --- | --- | --- | --- |
| Harta/Aset | 1xxx | DEBIT | NERACA |
| Kewajiban/Hutang | 2xxx | KREDIT | NERACA |
| Modal/Ekuitas | 3xxx | KREDIT | NERACA |
| Pendapatan | 4xxx | KREDIT | LABA_RUGI |
| Beban Atas Pendapatan/HPP | 5xxx | DEBIT | LABA_RUGI |
| Beban Operasional | 6xxx | DEBIT | LABA_RUGI |
| Beban Non Operasional | 7xxx | DEBIT | LABA_RUGI |
| Pendapatan Lain | 8xxx | KREDIT | LABA_RUGI |
| Beban Lain | 9xxx | DEBIT | LABA_RUGI |

## Tipe Akun

| Tipe | Fungsi | Boleh Dipakai Jurnal |
| --- | --- | --- |
| `HEADER` | Kelompok akun, misalnya Harta atau Pendapatan | Tidak |
| `POSTING` | Akun transaksi, misalnya Kas, Piutang, Persediaan, Penjualan, HPP | Ya |

Aturan:

- Akun `HEADER` tidak boleh dipakai pada jurnal.
- Akun `POSTING` harus aktif dan transaction eligible.
- Akun yang sudah pernah dipakai jurnal tidak dihapus; gunakan nonaktif.
- Akun manual hanya boleh dipakai jika `allow_manual_journal = 1`.

## Mapping Akun Wajib

Mapping global minimal sebelum pilot accounting:

| Event | Role | Side |
| --- | --- | --- |
| `SALES_INVOICE` | `ACCOUNT_RECEIVABLE` | DEBIT |
| `SALES_INVOICE` | `SALES_REVENUE` | KREDIT |
| `SALES_INVOICE` | `VAT_OUTPUT` | KREDIT |
| `GOODS_ISSUE` | `COGS` | DEBIT |
| `GOODS_ISSUE` | `INVENTORY` | KREDIT |
| `GOODS_RECEIPT` | `INVENTORY` | DEBIT |
| `GOODS_RECEIPT` | `GRNI` | KREDIT |
| `PURCHASE_INVOICE` | `GRNI` | DEBIT |
| `PURCHASE_INVOICE` | `VAT_INPUT` | DEBIT |
| `PURCHASE_INVOICE` | `ACCOUNT_PAYABLE` | KREDIT |
| `CUSTOMER_PAYMENT` | `CASH_BANK` | DEBIT |
| `CUSTOMER_PAYMENT` | `ACCOUNT_RECEIVABLE` | KREDIT |
| `SUPPLIER_PAYMENT` | `ACCOUNT_PAYABLE` | DEBIT |
| `SUPPLIER_PAYMENT` | `CASH_BANK` | KREDIT |

Mapping dapat dibuat lebih spesifik dengan `scope_type` dan `scope_key`, misalnya per gudang. Jika tidak ada mapping spesifik, sistem memakai mapping `GLOBAL/*`.

## Alur Penggunaan Harian

1. Finance membuka `jurnal` untuk menyiapkan Chart of Accounts.
2. Finance membuka `accounting` untuk mengecek schema, mapping wajib, periode fiskal, jurnal, payment, dan exception.
3. Finance membuat periode fiskal bulanan dan memastikan statusnya `OPEN`.
4. Finance mengisi/mereview mapping akun wajib sampai status mapping `READY`.
5. User operasional membuat transaksi final dari modul Sales/Logistik/LPB.
6. Sistem membuat auto-jurnal dari event final.
7. Jika gagal, sistem mencatat `Posting Exception`.
8. Finance memperbaiki akar masalah, lalu melakukan `Retry`.
9. Finance menarik Buku Besar, Neraca Saldo, Laba Rugi, dan Neraca dari jurnal `POSTED`.
10. Sebelum tutup periode, exception `OPEN`, jurnal `DRAFT`, dan pembayaran belum dialokasikan harus diselesaikan.

## Alur Auto-Jurnal

```text
Faktur final
  -> SALES_INVOICE: Piutang / Penjualan / PPN Keluaran
  -> GOODS_ISSUE: HPP / Persediaan

LPB final
  -> validasi harga perolehan
  -> GOODS_RECEIPT: Persediaan / GRNI

Payment customer
  -> CUSTOMER_PAYMENT: Kas/Bank / Piutang

Payment supplier
  -> SUPPLIER_PAYMENT: Hutang / Kas/Bank
```

## Laporan

| Laporan | Sumber | Perilaku Tanggal |
| --- | --- | --- |
| Buku Besar | Detail jurnal `POSTED` | Dalam rentang tanggal |
| Neraca Saldo | Akun posting + jurnal `POSTED` | Dalam rentang tanggal |
| Laba Rugi | Klasifikasi `LABA_RUGI` | Dalam rentang tanggal |
| Neraca | Klasifikasi `NERACA` | Kumulatif sampai tanggal akhir |
| Piutang | Akun tipe kontrol `PIUTANG` | Dalam rentang tanggal |
| Hutang | Akun tipe kontrol `HUTANG` | Dalam rentang tanggal |
| Kas/Bank | Akun tipe kontrol `KAS`/`BANK` | Dalam rentang tanggal |

## Checklist Sebelum Pilot

- Schema runtime accounting lengkap.
- Periode fiskal berjalan sudah `OPEN`.
- Mapping wajib status `READY`.
- Tidak ada akun mapping yang nonaktif atau bertipe `HEADER`.
- Saldo awal sudah balance sebelum migrasi.
- Jurnal manual balance berhasil `POSTED`.
- Auto-jurnal Sales Invoice, Goods Issue, dan LPB sudah diuji.
- Payment allocation tidak melebihi outstanding invoice.
- Laporan Neraca Saldo balance.
- Laporan Laba Rugi dan Neraca sudah direview Finance.
