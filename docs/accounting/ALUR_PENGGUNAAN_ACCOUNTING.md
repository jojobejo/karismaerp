# Alur Tata Cara Penggunaan Module Akun dan Accounting

Tanggal: 2026-07-13  
Acuan: `docs/accounting/MASTER_SPECS.md`

## Status Penggunaan Saat Ini

Module yang bisa digunakan saat ini adalah `Jurnal` sebagai pengelolaan Chart of Accounts.

Route:

- `jurnal`
- `keuangan/jurnal`

Fungsi yang sudah siap dipakai:

- Membuka daftar akun.
- Menambah akun `HEADER`.
- Menambah akun `POSTING`.
- Mengubah akun.
- Menonaktifkan akun.
- Menghapus akun jika belum dipakai jurnal dan tidak punya child.
- Mengelola master pendukung: klasifikasi, saldo normal, tipe kontrol, parent/subclass.
- Melihat baris jurnal per akun jika tabel `tbkeu_jurnal` dan `tbkeu_jurnal_detail` sudah tersedia.

Fungsi yang belum siap sebagai transaksi accounting penuh:

- Input jurnal debit/kredit manual lengkap.
- Posting jurnal.
- Reversal jurnal.
- Mapping akun.
- Auto-posting sales, purchase, LPB, payment, retur, mutasi, stock adjustment.
- Laporan buku besar, neraca saldo, laba rugi, neraca, piutang, hutang, kas/bank.

## Prinsip Akuntansi yang Harus Dijaga

1. Semua transaksi finansial masuk ke `tbkeu_jurnal` dan `tbkeu_jurnal_detail`.
2. Setiap jurnal harus double-entry: total debit sama dengan total kredit.
3. Akun `HEADER` hanya untuk struktur, tidak boleh dipakai transaksi.
4. Akun `POSTING` adalah akun yang boleh dipakai transaksi.
5. Akun yang sudah digunakan jurnal tidak boleh dihapus.
6. Akun tidak aktif tidak boleh dipakai transaksi baru.
7. Jurnal `POSTED` tidak boleh diedit atau dihapus.
8. Koreksi jurnal harus lewat reversal.
9. Laporan keuangan hanya membaca jurnal `POSTED`.
10. Kode akun tidak boleh di-hardcode di controller, model, view, helper, library, atau JavaScript.
11. Auto-posting wajib memakai `tbkeu_mapping_akun`.

## Persiapan Database

Jalankan migration berurutan pada database local/staging:

1. `docs/database/accounting_jurnal_accounts_20260713.sql`
2. `docs/database/accounting_jurnal_master_options_20260713.sql`
3. `docs/database/accounting_general_ledger_journal_20260713.sql`

Setelah migration, verifikasi tabel minimal:

- `tbkeu_klasifikasi_akun`
- `tbkeu_akun`
- `tbkeu_saldo_normal`
- `tbkeu_tipe_kontrol`
- `tbkeu_periode_fiskal`
- `tbkeu_jenis_jurnal`
- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`

## Alur Penggunaan Chart of Accounts Saat Ini

### 1. Login

1. Login ke KARISMA ERP.
2. Gunakan user yang punya akses admin/keuangan.
3. Buka `dashboard`.
4. Pilih tab `KEUANGAN`.
5. Klik module `Jurnal`.

URL langsung:

```text
jurnal
```

atau:

```text
keuangan/jurnal
```

### 2. Cek Master Pendukung

Sebelum membuat akun, cek card master:

- `Klasifikasi`
- `Saldo Normal`
- `Tipe Kontrol`
- `Parent / Subclass`

Pastikan klasifikasi minimal tersedia:

- Harta
- Kewajiban
- Modal
- Pendapatan
- Beban Atas Pendapatan
- Beban Operasional
- Beban Non Operasional
- Pendapatan Lain
- Beban Lain

Pastikan saldo normal:

- `DEBIT`
- `KREDIT`

Pastikan tipe kontrol minimal:

- `NONE`
- `KAS`
- `BANK`
- `PIUTANG`
- `HUTANG`
- `PERSEDIAAN`
- `GRNI`
- `PAJAK_MASUKAN`
- `PAJAK_KELUARAN`
- `UANG_MUKA_CUSTOMER`
- `UANG_MUKA_SUPPLIER`
- `LABA_DITAHAN`

### 3. Buat Akun HEADER

Gunakan akun `HEADER` untuk kelompok.

Contoh:

| Kode | Nama | Klasifikasi | Saldo Normal | Tipe |
| --- | --- | --- | --- | --- |
| 1000 | Harta | Harta | DEBIT | HEADER |
| 2000 | Kewajiban | Kewajiban | KREDIT | HEADER |
| 3000 | Modal | Modal | KREDIT | HEADER |
| 4000 | Pendapatan | Pendapatan | KREDIT | HEADER |
| 5000 | Beban Atas Pendapatan | Beban Atas Pendapatan | DEBIT | HEADER |

Cara:

1. Klik `Tambah Akun Jurnal`.
2. Isi kode dan nama akun.
3. Pilih klasifikasi.
4. Kosongkan parent untuk kelompok utama.
5. Pilih `Tipe Akun = HEADER`.
6. Pastikan `Boleh Jurnal Manual` tidak aktif.
7. Klik `Simpan`.

### 4. Buat Akun POSTING

Gunakan akun `POSTING` untuk transaksi.

Contoh:

| Kode | Nama | Parent | Saldo Normal | Tipe Kontrol |
| --- | --- | --- | --- | --- |
| 1100 | Kas | 1000 Harta | DEBIT | KAS |
| 1200 | Bank | 1000 Harta | DEBIT | BANK |
| 1300 | Piutang Usaha | 1000 Harta | DEBIT | PIUTANG |
| 1400 | Persediaan Barang | 1000 Harta | DEBIT | PERSEDIAAN |
| 2100 | Hutang Usaha | 2000 Kewajiban | KREDIT | HUTANG |
| 2200 | GRNI | 2000 Kewajiban | KREDIT | GRNI |
| 4100 | Penjualan | 4000 Pendapatan | KREDIT | NONE |
| 5100 | Harga Pokok Penjualan | 5000 Beban Atas Pendapatan | DEBIT | NONE |

Cara:

1. Klik `Tambah Akun Jurnal`.
2. Isi kode dan nama akun.
3. Pilih klasifikasi.
4. Pilih parent akun `HEADER`.
5. Pilih `Tipe Akun = POSTING`.
6. Pilih tipe kontrol sesuai fungsi akun.
7. Aktifkan `Boleh Jurnal Manual` hanya untuk akun yang boleh dipakai jurnal manual.
8. Klik `Simpan`.

### 5. Edit Akun

1. Cari akun di panel daftar.
2. Klik ikon edit atau tombol detail akun.
3. Ubah nama, parent, klasifikasi, status, atau tipe kontrol.
4. Klik `Simpan`.

Larangan:

- Jangan ubah akun yang punya child menjadi `POSTING`.
- Jangan ubah parent menjadi dirinya sendiri.
- Jangan memakai akun `HEADER` sebagai akun transaksi.

### 6. Nonaktifkan Akun

Gunakan nonaktif jika akun sudah tidak dipakai.

1. Pilih akun.
2. Klik `Nonaktifkan`.
3. Pastikan akun tidak dipakai untuk transaksi baru.

Catatan:

- Histori jurnal lama tetap aman.
- Akun yang sudah dipakai jurnal tidak perlu dihapus.

### 7. Hapus Akun

Hapus hanya boleh untuk akun yang:

- Belum pernah dipakai di `tbkeu_jurnal_detail`.
- Tidak punya child account.

Jika akun sudah dipakai atau punya child, gunakan `Nonaktifkan`.

## Alur Target Setelah GL Lengkap

Bagian ini menjadi acuan pemakaian setelah service posting, mapping akun, pembayaran, invoice supplier, reversal, dan laporan selesai dibangun.

### 1. Setup Awal Accounting

1. Jalankan seluruh migration `tbkeu_*`.
2. Buat periode fiskal bulanan.
3. Buka periode berjalan.
4. Susun Chart of Accounts.
5. Isi mapping akun untuk semua event wajib.
6. Isi saldo awal akun.
7. Posting saldo awal menjadi jurnal pembukaan.

### 2. Mapping Akun

Konfigurasi mapping minimal:

| Event | Role wajib |
| --- | --- |
| SALES_INVOICE | ACCOUNT_RECEIVABLE, SALES_REVENUE, SALES_DISCOUNT, VAT_OUTPUT, COGS, INVENTORY |
| CUSTOMER_RECEIPT | CASH_BANK, ACCOUNT_RECEIVABLE, BG_RECEIVABLE |
| GOODS_RECEIPT | INVENTORY, GRNI |
| PURCHASE_INVOICE | GRNI, ACCOUNT_PAYABLE, VAT_INPUT, PURCHASE_PRICE_VARIANCE, EXPENSE |
| SUPPLIER_PAYMENT | ACCOUNT_PAYABLE, CASH_BANK, BG_PAYABLE |
| SALES_RETURN | SALES_RETURN, VAT_OUTPUT, ACCOUNT_RECEIVABLE, CASH_BANK, INVENTORY, COGS |
| PURCHASE_RETURN | ACCOUNT_PAYABLE, CASH_BANK, INVENTORY, VAT_INPUT, PURCHASE_RETURN |
| STOCK_ADJUSTMENT_IN | INVENTORY, STOCK_GAIN |
| STOCK_ADJUSTMENT_OUT | STOCK_LOSS, INVENTORY |
| CUSTOMER_ADVANCE | CASH_BANK, CUSTOMER_ADVANCE |
| SUPPLIER_ADVANCE | SUPPLIER_ADVANCE, CASH_BANK |

Jika mapping tidak lengkap, posting harus gagal dan masuk dashboard exception.

### 3. Jurnal Manual

1. Buka `keuangan/jurnal`.
2. Klik tambah jurnal manual.
3. Isi tanggal, periode, jenis jurnal, keterangan.
4. Tambah baris debit/kredit memakai akun `POSTING` aktif.
5. Pastikan total debit sama dengan total kredit.
6. Simpan sebagai `DRAFT`.
7. Review.
8. Klik `POST`.
9. Setelah `POSTED`, jurnal tidak bisa diedit.

### 4. Penjualan Kredit

1. Sales membuat SO.
2. Logistik memproses DO/faktur.
3. Sales melakukan konfirmasi final.
4. Accounting service membuat jurnal otomatis.
5. User finance cek jurnal di laporan jurnal umum.

Expected journal:

```text
Debit  Piutang Usaha
Kredit Pendapatan Penjualan
Kredit PPN Keluaran

Debit  Harga Pokok Penjualan
Kredit Persediaan Barang
```

### 5. Pembayaran Customer

1. Finance input pembayaran.
2. Pilih customer.
3. Pilih kas/bank atau BG.
4. Alokasikan ke faktur.
5. Confirm pembayaran.
6. Posting otomatis mengurangi piutang.

Expected journal:

```text
Debit  Kas/Bank
Kredit Piutang Usaha
```

### 6. Penerimaan Barang / LPB

1. Admin PO membuka PO.
2. Input draft penerimaan.
3. Finalize LPB.
4. Sistem menambah stock kuantitas.
5. Accounting service posting inventory dan GRNI.

Expected journal:

```text
Debit  Persediaan Barang
Kredit GRNI
```

### 7. Invoice Supplier

1. Finance input invoice supplier.
2. Link ke PO/LPB bila invoice barang.
3. Validasi DPP, diskon, pajak, dan grand total.
4. Confirm invoice.
5. Posting AP.

Expected journal:

```text
Debit  GRNI
Debit  PPN Masukan
Debit/Kredit Selisih Harga Pembelian
Kredit Hutang Usaha
```

### 8. Pembayaran Supplier

1. Finance input pembayaran supplier.
2. Pilih invoice supplier.
3. Alokasikan pembayaran.
4. Confirm pembayaran.
5. Posting otomatis menurunkan hutang dan kas/bank.

Expected journal:

```text
Debit  Hutang Usaha
Kredit Kas/Bank
```

### 9. Retur

Retur penjualan:

```text
Debit  Retur Penjualan
Debit  PPN Keluaran
Kredit Piutang Usaha / Kas Bank

Debit  Persediaan Barang
Kredit Harga Pokok Penjualan
```

Retur pembelian:

```text
Debit  Hutang Usaha / Kas Bank
Kredit Persediaan Barang
Kredit PPN Masukan
```

### 10. Tutup Periode

1. Pastikan semua transaksi periode sudah diposting.
2. Pastikan dashboard exception kosong atau sudah resolved.
3. Jalankan neraca saldo.
4. Pastikan debit sama dengan kredit.
5. Generate laporan laba rugi dan neraca.
6. Close periode.

Setelah periode `CLOSED`:

- Jurnal baru tidak boleh dibuat pada periode tersebut.
- Jurnal tidak boleh diposting pada periode tersebut.
- Reversal tidak boleh masuk periode tersebut.
- Reopen harus memiliki hak akses dan alasan.

## Checklist Siap Go-Live Module Akun

- Semua akun utama tersedia dan aktif.
- Akun `HEADER` tidak dipakai jurnal.
- Akun `POSTING` punya saldo normal benar.
- Tipe kontrol akun kas, bank, piutang, hutang, persediaan, GRNI, pajak benar.
- Mapping akun semua event wajib lengkap.
- Periode fiskal berjalan `OPEN`.
- Audit data source bersih dari duplicate/orphan kritis.
- HPP dan harga jual tidak nol untuk transaksi yang akan diposting.
- Tidak ada jurnal unbalanced.
- Laporan hanya mengambil jurnal `POSTED`.
