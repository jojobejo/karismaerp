# UAT Module Jurnal Akuntansi Akurat dan Layak Guna

Tanggal: 2026-07-15  
Target: Finance, IT, dan user operasional terkait.

UAT ini memastikan module Jurnal/Accounting layak dipakai sebagai General Ledger berbasis double-entry.

## Syarat Awal

- Database sudah menjalankan migration accounting lengkap.
- User UAT memiliki akses admin/keuangan.
- Periode fiskal bulan UAT sudah dibuat.
- Chart of Accounts minimal tersedia.
- Mapping akun wajib sudah diisi.
- Data transaksi UAT tersedia untuk Sales Invoice, Goods Issue, LPB, dan Payment.

## Prinsip Kelulusan

1. Tidak ada jurnal tidak balance.
2. Tidak ada jurnal parsial saat posting gagal.
3. Jurnal `POSTED` tidak bisa diedit.
4. Reversal membuat jurnal pembalik, bukan menghapus jurnal asli.
5. Laporan hanya membaca jurnal `POSTED`.
6. Mapping akun tidak memakai kode akun hardcoded di controller/model/view/helper/library/JavaScript.
7. Exception `OPEN` harus ditindaklanjuti sebelum close period.

## UAT A - Chart of Accounts

| ID | Skenario | Langkah | Expected Result |
| --- | --- | --- | --- |
| A-01 | Buka COA | Buka `jurnal` | Daftar akun tampil |
| A-02 | Tambah akun HEADER | Buat akun kelompok Harta/Pendapatan | Akun tersimpan, tidak bisa dipakai jurnal manual |
| A-03 | Tambah akun POSTING | Buat akun Kas/Piutang/Penjualan/HPP | Akun tersimpan dan eligible transaksi |
| A-04 | Parent valid | Pilih parent bertipe HEADER | Akun tersimpan |
| A-05 | Parent invalid | Pilih parent POSTING | Ditolak |
| A-06 | Kode akun duplikat | Simpan kode yang sudah ada | Ditolak |
| A-07 | Nonaktif akun | Nonaktifkan akun lama | Tidak bisa dipakai transaksi baru |
| A-08 | Hapus akun dipakai | Hapus akun yang sudah ada jurnal | Ditolak |

## UAT B - Mapping Akun

| ID | Skenario | Langkah | Expected Result |
| --- | --- | --- | --- |
| B-01 | Mapping wajib lengkap | Buka `accounting`, cek Validasi Runtime | Mapping wajib `READY` |
| B-02 | Missing mapping | Nonaktifkan salah satu mapping UAT | Status mapping menjadi `REVIEW` |
| B-03 | Mapping ke HEADER | Arahkan mapping ke akun HEADER | Status invalid/mapping gagal dipakai posting |
| B-04 | Mapping ke akun nonaktif | Nonaktifkan akun mapping | Status invalid/mapping gagal dipakai posting |
| B-05 | Scope global | Pakai mapping `GLOBAL/*` | Auto-post berhasil |
| B-06 | Scope gudang | Tambah mapping warehouse tertentu | Mapping spesifik diprioritaskan |

## UAT C - Periode Fiskal

| ID | Skenario | Langkah | Expected Result |
| --- | --- | --- | --- |
| C-01 | Buat periode | Buat periode tanggal valid dengan alasan | Periode `OPEN` |
| C-02 | Periode overlap | Buat periode bertabrakan | Ditolak |
| C-03 | Posting tanggal open | Posting jurnal di periode open | Berhasil |
| C-04 | Posting tanggal closed | Close periode lalu posting jurnal | Ditolak |
| C-05 | Close dengan draft | Sisakan jurnal `DRAFT`, close periode | Ditolak |
| C-06 | Close dengan exception | Sisakan exception `OPEN`, close periode | Ditolak |
| C-07 | Reopen | Reopen periode closed dengan alasan | Berhasil dan tercatat log |

## UAT D - Jurnal Manual

| ID | Skenario | Langkah | Expected Result |
| --- | --- | --- | --- |
| D-01 | Draft balance | Debit Kas 100.000, Kredit Pendapatan 100.000 | Draft tersimpan |
| D-02 | Draft tidak balance | Debit 100.000, Kredit 90.000 | Ditolak |
| D-03 | Baris nol | Isi baris debit/kredit nol | Ditolak |
| D-04 | Double side | Isi debit dan kredit pada satu baris | Ditolak |
| D-05 | Akun HEADER | Pilih akun HEADER | Ditolak |
| D-06 | Posting draft | Posting draft balance | Status menjadi `POSTED` |
| D-07 | Edit posted | Coba ubah jurnal posted | Tidak tersedia/tidak boleh |
| D-08 | Idempotency duplicate | Pakai key sama dua kali | Tidak membuat jurnal ganda |

## UAT E - Auto-Jurnal Sales dan Goods Issue

| ID | Skenario | Langkah | Expected Result |
| --- | --- | --- | --- |
| E-01 | Faktur final | Konfirmasi DO/faktur final | Terbentuk `SALES_INVOICE` |
| E-02 | Barang keluar | Konfirmasi DO/faktur final yang sama | Terbentuk `GOODS_ISSUE` |
| E-03 | Rekonsiliasi sales | Bandingkan total faktur dan jurnal | Nilai sama |
| E-04 | Rekonsiliasi HPP | Bandingkan qty x hrg_pokok dan jurnal | Nilai sama |
| E-05 | Repost event sama | Jalankan ulang trigger | Tidak membuat jurnal ganda |
| E-06 | Faktur batal | Posting faktur cancelled | Ditolak dan/atau exception |

## UAT F - LPB / Goods Receipt

| ID | Skenario | Langkah | Expected Result |
| --- | --- | --- | --- |
| F-01 | LPB harga lengkap | Finalisasi LPB dengan harga perolehan | Terbentuk `GOODS_RECEIPT` |
| F-02 | LPB harga kosong | Finalisasi LPB harga nol/tidak tersedia | Masuk Posting Exception |
| F-03 | Retry LPB | Lengkapi harga, retry exception | Jurnal terbentuk dan exception resolved |
| F-04 | Rekonsiliasi nilai | Bandingkan qty diterima x harga | Nilai jurnal sama |

## UAT G - Payment Allocation

| ID | Skenario | Langkah | Expected Result |
| --- | --- | --- | --- |
| G-01 | Customer payment penuh | Alokasi ke invoice sesuai outstanding | Debit Kas/Bank, Kredit Piutang |
| G-02 | Customer payment parsial | Alokasi sebagian invoice | Outstanding invoice berkurang |
| G-03 | Over allocation | Alokasi melebihi outstanding | Ditolak |
| G-04 | Invoice ganda | Invoice sama dua kali dalam satu payment | Ditolak |
| G-05 | Supplier payment | Bayar hutang supplier | Debit Hutang, Kredit Kas/Bank |
| G-06 | Unapplied payment | Payment lebih besar dari alokasi | Unapplied tercatat dan menghambat close period |

## UAT H - Reversal dan Exception

| ID | Skenario | Langkah | Expected Result |
| --- | --- | --- | --- |
| H-01 | Reversal posted | Reverse jurnal posted dengan alasan | Jurnal pembalik terbentuk |
| H-02 | Reversal ganda | Reverse jurnal yang sudah direversal | Ditolak |
| H-03 | Reversal draft | Reverse jurnal draft | Ditolak |
| H-04 | Exception retry | Retry setelah akar masalah selesai | Exception resolved |
| H-05 | Exception ignore | Ignore data uji dengan catatan | Status `IGNORED` |
| H-06 | Exception tanpa catatan | Resolve/ignore tanpa note | Ditolak |

## UAT I - Laporan

| ID | Skenario | Langkah | Expected Result |
| --- | --- | --- | --- |
| I-01 | Buku besar | Pilih akun dan periode | Saldo berjalan tampil dari jurnal `POSTED` |
| I-02 | Neraca saldo | Generate periode UAT | Total debit = total kredit |
| I-03 | Laba rugi | Generate periode UAT | Pendapatan, HPP, beban, laba bersih benar |
| I-04 | Neraca | Generate cut-off UAT | Aset = Kewajiban + Ekuitas + Laba Berjalan |
| I-05 | Piutang | Generate laporan piutang | Piutang turun setelah payment |
| I-06 | Hutang | Generate laporan hutang | Hutang turun setelah supplier payment |
| I-07 | Kas/Bank | Generate laporan kas/bank | Mutasi kas/bank sesuai jurnal |

## UAT Database

Jalankan:

```sql
SOURCE docs/database/accounting_uat_database_20260715.sql;
```

Hasil ideal:

- `DB-01` sampai `DB-15`: `PASS`.
- `DB-16`: `PASS`.
- `DB-16B`: boleh `REVIEW` jika hanya domain reservation stock.
- `DB-17`: `PASS` sebelum close period.
- `DB-18` dan `DB-19`: `PASS` setelah cutover auto-posting aktif.
- `DB-20`: `PASS` jika migration compatibility sales/logistik sudah diterapkan.

## Sign-off

| Area | PIC | Status | Catatan |
| --- | --- | --- | --- |
| Finance - COA dan Mapping |  |  |  |
| Finance - Jurnal Manual |  |  |  |
| Sales/Logistik - Faktur dan DO |  |  |  |
| Logistik/Purchasing - LPB |  |  |  |
| Finance - Payment Allocation |  |  |  |
| Finance - Laporan |  |  |  |
| IT - Database UAT |  |  |  |

Module layak pilot jika seluruh skenario prioritas A sampai I lulus atau memiliki keputusan tertulis untuk ditunda tanpa mengganggu General Ledger.

## Hasil Eksekusi Lokal 2026-07-15

Perintah:

```bash
/Applications/XAMPP/xamppfiles/bin/mysql -uroot kiucoid_karismaerp_local < docs/database/accounting_uat_database_20260715.sql
```

Ringkasan hasil:

| Test | Status | Catatan |
| --- | --- | --- |
| DB-01 s/d DB-15 | PASS | Core schema, jurnal, mapping, sales, HPP, dan payment allocation valid. |
| DB-16 | FAIL | Ada 5 batch dengan `qty_on_hand` berbeda dari stock ledger. Ini domain stok/logistik, bukan saldo General Ledger. |
| DB-16B | PASS | Reserved stock sesuai ledger reservation. |
| DB-17 | REVIEW | Ada 2 exception OPEN untuk LPB id 5 dan 6 karena harga perolehan belum lengkap (`LPB_COST_UNRESOLVED`). |
| DB-18 | PASS | Faktur sejak cutover sudah memiliki jurnal sales dan goods issue. |
| DB-19 | PASS | LPB sejak cutover sudah posted atau masuk exception. |
| DB-20 | PASS | Compatibility Sales/DO lengkap. |

Tindak lanjut:

- Finance/Purchasing melengkapi harga LPB id 5 dan 6, lalu jalankan retry dari Posting Exception.
- Tim stok/logistik meninjau 5 batch mismatch DB-16 sebelum stock ledger dijadikan single source of truth untuk opname/adjustment.
