# UAT Module Akun dan Accounting

Tanggal: 2026-07-13  
Acuan: `docs/accounting/MASTER_SPECS.md`  
Tujuan: memastikan module akun dan accounting dapat digunakan sesuai prinsip standar akuntansi double-entry, audit trail, immutability, period control, mapping akun, dan laporan berbasis jurnal `POSTED`.

## Scope UAT

UAT dibagi menjadi dua kelompok.

Kelompok A dapat diuji pada implementasi saat ini:

- Akses module `Jurnal`.
- CRUD Chart of Accounts.
- Master pendukung akun.
- Validasi akun `HEADER` dan `POSTING`.
- Guard hapus/nonaktif akun.
- Format response AJAX accounting.
- Panel jurnal per akun jika tabel GL sudah dimigrasikan.

Kelompok B adalah UAT target sebelum go-live accounting penuh:

- Jurnal manual.
- Posting dan reversal.
- Periode fiskal.
- Mapping akun.
- Auto-posting sales, purchase, LPB, payment, retur, stock adjustment, mutasi.
- Exception dashboard.
- Laporan keuangan.

## Prasyarat UAT

Database:

1. Backup database test.
2. Jalankan migration:
   - `docs/database/accounting_jurnal_accounts_20260713.sql`
   - `docs/database/accounting_jurnal_master_options_20260713.sql`
   - `docs/database/accounting_general_ledger_journal_20260713.sql`
3. Jalankan SQL audit pada `application/libraries/Accounting/docs/database-analysis.md`.
4. Jangan menjalankan UAT pada database produksi sebelum hasil audit disetujui.

User:

- User admin.
- User keuangan.
- User non-keuangan untuk negative test akses.

Data minimal:

- Customer valid.
- Supplier valid.
- Barang dengan HPP valid.
- Barang dengan HPP nol untuk negative test.
- Periode fiskal berjalan `OPEN`.
- Periode fiskal lama `CLOSED`.
- Akun kas, bank, piutang, hutang, persediaan, GRNI, penjualan, HPP, PPN masukan, PPN keluaran.

## Acceptance Criteria Global

UAT dianggap lulus jika:

1. Tidak ada transaksi finansial yang membuat jurnal unbalanced.
2. Semua jurnal `POSTED` immutable.
3. Koreksi jurnal hanya melalui reversal.
4. Auto-posting memakai mapping akun, bukan hardcode kode akun.
5. Duplicate posting dicegah oleh idempotency key.
6. Periode `CLOSED` menolak create, post, dan reversal.
7. Semua gagal posting masuk exception dashboard tanpa jurnal parsial.
8. Laporan hanya membaca jurnal `POSTED`.
9. Empat tabel Purchase Order terlarang tidak disentuh accounting.

## UAT A - Chart of Accounts Saat Ini

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-A01 | Akses module oleh user finance | Login finance, buka `keuangan/jurnal` | Halaman Jurnal tampil | Pending |
| UAT-A02 | Akses ditolak untuk user non-finance | Login user biasa, buka `keuangan/jurnal` | HTTP 403 atau pesan akses ditolak | Pending |
| UAT-A03 | Schema belum tersedia | Buka halaman sebelum migration akun | Peringatan schema accounting tampil dan tombol CRUD disabled | Pending |
| UAT-A04 | Load daftar akun | Buka `jurnal/list` dari UI | Response JSON `success=true`, daftar akun muncul | Pending |
| UAT-A05 | Tambah akun HEADER | Buat akun `1999 Test Header` tipe `HEADER` | Akun tersimpan, tidak boleh jurnal manual | Pending |
| UAT-A06 | Tambah akun POSTING | Buat akun `1999.01 Test Posting` parent `1999` | Akun tersimpan sebagai child | Pending |
| UAT-A07 | Kode akun duplikat | Buat akun dengan kode yang sama | Ditolak dengan pesan kode sudah digunakan | Pending |
| UAT-A08 | Parent harus HEADER | Buat akun dengan parent akun POSTING | Ditolak | Pending |
| UAT-A09 | Self parent | Edit akun agar parent dirinya sendiri | Ditolak | Pending |
| UAT-A10 | Header dengan child tidak bisa jadi posting | Edit akun HEADER yang punya child menjadi POSTING | Ditolak | Pending |
| UAT-A11 | Nonaktifkan akun | Klik nonaktifkan pada akun test | `is_active=0`, akun tidak hilang dari histori | Pending |
| UAT-A12 | Hapus akun belum dipakai | Hapus akun test yang tidak punya jurnal/child | Akun terhapus | Pending |
| UAT-A13 | Hapus akun punya child | Hapus HEADER yang punya child | Ditolak, diarahkan nonaktifkan | Pending |
| UAT-A14 | Hapus akun sudah dipakai jurnal | Pakai akun pada detail jurnal test, lalu hapus | Ditolak | Pending |
| UAT-A15 | Master klasifikasi | Tambah/edit/hapus klasifikasi belum dipakai | Berhasil, pilihan form akun ikut update | Pending |
| UAT-A16 | Master saldo normal | Tambah/edit saldo normal | Berhasil jika belum dipakai, ditolak hapus jika dipakai | Pending |
| UAT-A17 | Master tipe kontrol | Tambah/edit tipe kontrol | Berhasil jika belum dipakai, ditolak hapus jika dipakai | Pending |
| UAT-A18 | Panel jurnal per akun | Pilih akun yang punya detail jurnal | Baris jurnal tampil dari `tbkeu_jurnal_detail` | Pending |
| UAT-A19 | Format AJAX gagal | Kirim request invalid ke endpoint store | JSON memuat `success`, `message`, `data`, `errors`, `meta` | Pending |
| UAT-A20 | Tabel PO terlarang tidak disentuh | Scan query implementasi accounting | Tidak ada query aktif ke tabel terlarang | Pending |

## UAT B - Target Accounting Go-Live

### B1. Periode Fiskal

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B01 | Buat periode unik | Buat periode `2026-07` | Berhasil satu kali | Pending |
| UAT-B02 | Duplicate periode | Buat ulang periode bulan sama | Ditolak unique `tahun+bulan`/kode | Pending |
| UAT-B03 | Close periode | Close periode setelah semua jurnal valid | Status menjadi `CLOSED`, log tersimpan | Pending |
| UAT-B04 | Posting pada periode closed | Posting jurnal tanggal periode closed | Ditolak `PERIOD_CLOSED` | Pending |
| UAT-B05 | Reopen periode | Reopen dengan alasan dan hak khusus | Status `OPEN`, alasan dan user tersimpan | Pending |

### B2. Jurnal Manual

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B06 | Draft jurnal balance | Buat jurnal debit kas 100.000 kredit pendapatan 100.000 | Draft tersimpan, selisih 0 | Pending |
| UAT-B07 | Draft jurnal tidak balance | Debit 100.000 kredit 90.000 | Tidak bisa post, error `JOURNAL_NOT_BALANCED` | Pending |
| UAT-B08 | Akun HEADER dipakai detail | Pilih akun HEADER pada detail | Ditolak | Pending |
| UAT-B09 | Akun nonaktif dipakai detail | Pilih akun nonaktif | Ditolak | Pending |
| UAT-B10 | Post jurnal draft | Klik POST jurnal balance | Status `POSTED`, posted_by/posted_at terisi | Pending |
| UAT-B11 | Edit jurnal posted | Ubah header/detail jurnal posted | Ditolak immutable | Pending |
| UAT-B12 | Reversal jurnal posted | Reverse jurnal posted dengan alasan | Jurnal reversal dibuat, debit/kredit terbalik, jurnal awal `REVERSED` | Pending |
| UAT-B13 | Reversal ganda | Reverse jurnal yang sudah reversed | Ditolak | Pending |

### B3. Mapping Akun

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B14 | Mapping global lengkap | Isi semua role wajib `SALES_INVOICE` | Status mapping complete | Pending |
| UAT-B15 | Mapping spesifik barang | Isi mapping inventory untuk barang tertentu | Resolver memilih mapping barang sebelum global | Pending |
| UAT-B16 | Mapping tidak ada | Hapus role wajib lalu posting transaksi | Posting gagal `ACCOUNT_MAPPING_NOT_FOUND` | Pending |
| UAT-B17 | Mapping akun nonaktif | Mapping diarahkan ke akun nonaktif | Posting gagal validasi akun | Pending |

### B4. Sales Invoice

Data contoh:

- Nilai DPP: 1.000.000
- PPN keluaran: 110.000
- Grand total: 1.110.000
- HPP: 700.000

Expected journal:

```text
Debit  Piutang Usaha          1.110.000
Kredit Pendapatan Penjualan   1.000.000
Kredit PPN Keluaran             110.000

Debit  Harga Pokok Penjualan    700.000
Kredit Persediaan Barang        700.000
```

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B18 | Posting faktur kredit valid | Confirm faktur kredit valid | Jurnal AR, revenue, VAT, COGS, inventory terbentuk dan balance | Pending |
| UAT-B19 | Posting faktur tunai valid | Confirm faktur tunai dengan kas/bank | Debit kas/bank, bukan piutang | Pending |
| UAT-B20 | HPP nol | Confirm faktur barang HPP nol | Posting gagal `HPP_NOT_FOUND`, tidak ada jurnal parsial | Pending |
| UAT-B21 | Duplicate posting | Post source faktur sama dua kali | Percobaan kedua ditolak `SOURCE_ALREADY_POSTED` | Pending |
| UAT-B22 | Customer invalid | Faktur customer tidak ada | Posting gagal `CUSTOMER_NOT_FOUND` | Pending |

### B5. Customer Receipt

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B23 | Pembayaran penuh | Bayar faktur 1.110.000 | Debit kas/bank, kredit piutang 1.110.000 | Pending |
| UAT-B24 | Pembayaran sebagian | Bayar 500.000 dari faktur | Piutang turun 500.000, outstanding sisa benar | Pending |
| UAT-B25 | Pembayaran lebih | Alokasi melebihi outstanding | Ditolak | Pending |
| UAT-B26 | BG diterima | Metode BG status pending | Debit BG Dalam Pencairan, kredit piutang | Pending |
| UAT-B27 | BG cair | Ubah BG cleared | Debit bank, kredit BG Dalam Pencairan | Pending |
| UAT-B28 | BG ditolak | Ubah BG bounced | Debit piutang, kredit BG Dalam Pencairan | Pending |

### B6. Goods Receipt / LPB

Data contoh:

- Barang diterima: 10 pcs
- Harga PO valid: 50.000
- Nilai persediaan: 500.000

Expected journal:

```text
Debit  Persediaan Barang 500.000
Kredit GRNI              500.000
```

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B29 | Finalize LPB valid | Finalize LPB dari PO harga valid | Jurnal inventory/GRNI terbentuk | Pending |
| UAT-B30 | Qty melebihi sisa PO | Input qty LPB melebihi sisa | Ditolak sebelum posting | Pending |
| UAT-B31 | Harga PO nol | Finalize LPB harga nol | Posting gagal `INVALID_AMOUNT`, exception tercatat | Pending |
| UAT-B32 | Supplier invalid | LPB/PO supplier tidak valid | Posting gagal `SUPPLIER_NOT_FOUND` | Pending |

### B7. Purchase Invoice

Data contoh:

- GRNI: 500.000
- PPN masukan: 55.000
- Grand total invoice: 555.000

Expected journal:

```text
Debit  GRNI           500.000
Debit  PPN Masukan     55.000
Kredit Hutang Usaha   555.000
```

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B33 | Invoice supplier valid | Confirm invoice terkait LPB | AP terbentuk dan GRNI turun | Pending |
| UAT-B34 | Invoice harga beda | Harga invoice beda dari PO | Selisih masuk purchase price variance | Pending |
| UAT-B35 | Nomor invoice duplikat per supplier | Input nomor invoice sama | Ditolak unique supplier + nomor invoice | Pending |

### B8. Supplier Payment

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B36 | Bayar supplier penuh | Confirm payment AP | Debit hutang, kredit kas/bank | Pending |
| UAT-B37 | Bayar supplier sebagian | Alokasi sebagian | Outstanding AP tersisa benar | Pending |
| UAT-B38 | Bayar melebihi outstanding | Alokasi lebih besar dari hutang | Ditolak | Pending |

### B9. Retur

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B39 | Retur penjualan kredit | Rekam retur barang kembali gudang | Retur penjualan, PPN, piutang, inventory, COGS terjurnal balance | Pending |
| UAT-B40 | Retur penjualan tanpa barang kembali | Retur hanya potong tagihan | Tidak menambah persediaan | Pending |
| UAT-B41 | Retur pembelian | Rekam retur ke supplier | AP/kas, inventory, PPN masukan terjurnal balance | Pending |

### B10. Stock Adjustment dan Mutasi

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B42 | Adjustment positif | Approve selisih stock masuk | Debit inventory, kredit stock gain | Pending |
| UAT-B43 | Adjustment negatif | Approve selisih stock keluar | Debit stock loss, kredit inventory | Pending |
| UAT-B44 | Mutasi gudang akun sama | Post mutasi antar gudang akun inventory sama | Tidak membuat jurnal finansial | Pending |
| UAT-B45 | Mutasi gudang akun beda | Post mutasi antar gudang akun inventory beda | Debit inventory tujuan, kredit inventory asal | Pending |

### B11. Exception Dashboard

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B46 | Exception mapping | Posting tanpa mapping | Row exception `OPEN` tercatat | Pending |
| UAT-B47 | Retry setelah mapping diperbaiki | Lengkapi mapping, klik retry | Posting sukses, exception resolved | Pending |
| UAT-B48 | Ignore exception | Tandai exception ignored dengan alasan | Status `IGNORED`, alasan tersimpan | Pending |

### B12. Laporan

| ID | Skenario | Langkah | Expected Result | Status |
| --- | --- | --- | --- | --- |
| UAT-B49 | Buku besar | Buka buku besar akun kas | Hanya jurnal `POSTED` yang tampil | Pending |
| UAT-B50 | Neraca saldo | Generate periode berjalan | Total debit sama dengan total kredit | Pending |
| UAT-B51 | Laba rugi | Generate laba rugi | Pendapatan dan beban dari klasifikasi LABA_RUGI | Pending |
| UAT-B52 | Neraca | Generate neraca | Harta = kewajiban + modal setelah laba berjalan | Pending |
| UAT-B53 | Aging piutang | Generate piutang | Outstanding dari jurnal AR dan alokasi pembayaran | Pending |
| UAT-B54 | Aging hutang | Generate hutang | Outstanding dari AP dan pembayaran supplier | Pending |
| UAT-B55 | Kas bank | Generate mutasi kas/bank | Mutasi hanya akun tipe kontrol KAS/BANK | Pending |

## Template Bukti UAT

Gunakan format ini untuk setiap test:

```text
ID UAT:
Tanggal:
Tester:
Role user:
Database:
Data dokumen:
Langkah:
Expected:
Actual:
Status: PASS / FAIL
Catatan:
Screenshot/log:
```

## Kriteria Lulus Module Akun untuk Standar Akuntansi

Module akun boleh dinyatakan siap sebagai fondasi akuntansi jika:

- COA lengkap dan seimbang secara klasifikasi laporan.
- Semua akun transaksi adalah `POSTING`.
- Akun `HEADER` tidak bisa dipakai transaksi.
- Akun aktif/nonaktif bekerja.
- Akun yang sudah dipakai jurnal tidak bisa dihapus.
- Jurnal hanya memakai akun aktif dan posting.
- Total debit/kredit selalu balance.
- Koreksi hanya reversal.
- Periode fiskal mengunci transaksi.
- Laporan hanya mengambil jurnal `POSTED`.

Module accounting penuh boleh go-live setelah seluruh UAT B minimal PASS.
