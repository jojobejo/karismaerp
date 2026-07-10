Anda bertugas membangun modul akuntansi dan general ledger pada aplikasi KARISMA ERP berbasis CodeIgniter 3 dan MariaDB 10.4.

Gunakan database `kiucoid_karismaerp_local_master.sql` sebagai acuan utama path `docs/database/kiucoid_karismaerp_local_master.sql`. Analisis struktur database dan source code aplikasi sebelum melakukan perubahan.

## Batas scope utama

Modul akuntansi yang dibangun harus berdiri sebagai domain keuangan tersendiri.

Tabel berikut berada di luar scope dan tidak boleh digunakan, diubah, dimigrasikan, direlasikan, atau dijadikan sumber jurnal:

- `tbpo_transaksi`
- `tbpo_transaksi_tmp`
- `tbpo_transaksi_trashbin`
- `tbpo_akun_tr`

Keempat tabel tersebut merupakan bagian dari aplikasi Purchase Order tersendiri dan tidak memiliki hubungan dengan general ledger KARISMA ERP.

Jangan melakukan:

- perubahan struktur;
- penambahan foreign key;
- penambahan kolom jurnal;
- migrasi data akun;
- pemakaian kode akun;
- integrasi posting;
- refactor controller atau model;

terhadap tabel-tabel tersebut.

## Tujuan utama

Bangun modul:

1. Chart of Accounts/kode akun.
2. Jurnal umum.
3. Detail jurnal debit dan kredit.
4. Periode fiskal.
5. Mapping akun.
6. Auto-posting transaksi ERP.
7. Audit trail.
8. Reversal jurnal.
9. Laporan buku besar.
10. Neraca saldo.
11. Laba rugi.
12. Neraca.
13. Laporan piutang.
14. Laporan hutang.
15. Mutasi kas dan bank.
16. Dashboard exception transaksi gagal posting.

## Prinsip arsitektur

Gunakan pemisahan domain berikut:

- modul penjualan menghasilkan source transaction;
- modul pembelian menghasilkan source transaction;
- modul penerimaan barang menghasilkan source transaction;
- modul pembayaran menghasilkan source transaction;
- modul persediaan menghasilkan source transaction;
- modul accounting memproses source transaction menjadi jurnal;
- laporan keuangan hanya membaca jurnal berstatus `POSTED`.

Jangan menjadikan tabel transaksi operasional sebagai general ledger.

Jangan menyimpan debit dan kredit langsung pada tabel penjualan, pembelian, stock, LPB, atau pembayaran.

Semua jurnal disimpan pada:

- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`

## Batasan teknis

- Jangan mengubah mekanisme login dan session yang sedang berjalan.
- Jangan drop atau rename tabel existing.
- Jangan menghapus data existing.
- Jangan mengubah fungsi tabel Purchase Order.
- Jangan hardcode kode akun pada controller, model, library, helper, JavaScript, atau view.
- Semua lookup akun menggunakan `tbkeu_mapping_akun`.
- Gunakan database transaction untuk seluruh proses posting.
- Gunakan idempotency key agar satu source transaction tidak diposting dua kali.
- Gunakan `DECIMAL(19,4)` untuk nominal keuangan baru.
- Jangan gunakan `FLOAT` atau `DOUBLE` untuk jurnal baru.
- Gunakan InnoDB.
- Gunakan `utf8mb4`.
- Pertahankan style, layout, komponen, sidebar, dan template aplikasi existing.
- Seluruh request AJAX harus mengembalikan JSON konsisten.
- Seluruh jurnal yang sudah `POSTED` bersifat immutable.
- Koreksi jurnal hanya melalui reversal.
- Jangan menambahkan foreign key pada tabel legacy sebelum dilakukan audit orphan record.

---

# TAHAP 1 — ANALISIS DATABASE DAN SOURCE CODE

Analisis seluruh database dan source code yang berkaitan dengan:

## Penjualan

- `tbso_sales_order`
- `tbso_sales_order_detail`
- `tbso_faktur_penjualan`
- `tbso_faktur_detail`
- `tb_customer`
- `tbkeu_pembayaran_faktur`
- tabel retur penjualan;
- tabel pembatalan faktur;
- tabel pembayaran customer.

## Pembelian dan penerimaan barang

- `tbpo_po`
- `tbpo_detail_po`
- `tb_pre_po`
- `tb_pre_po_invoice_adjustment`
- `tb_pre_po_diskon_history`
- `tb_po_received`
- `tb_lpb`
- `tb_lpb_detail`
- `tb_lpb_batch`
- `tb_lpb_log`
- `tb_suplier`
- `tbpo_suplier`

Tabel Purchase Order boleh digunakan sebagai source document pembelian, tetapi jangan melibatkan:

- `tbpo_transaksi`
- `tbpo_transaksi_tmp`
- `tbpo_transaksi_trashbin`
- `tbpo_akun_tr`

## Persediaan

- `tberp_stock_ledger`
- `tberp_stock_batch`
- `tb_master_barang_all`
- `tb_mutasi`
- `tb_detail_mutasi`
- tabel stock adjustment;
- tabel stock opname;
- tabel retur barang.

`tberp_stock_ledger` tetap berfungsi sebagai ledger kuantitas stock, bukan ledger keuangan.

## Master pendukung

- `tb_customer`
- `tb_suplier`
- `tbpo_suplier`
- `tb_master_barang_all`
- `tb_gudang`
- `tb_gudang_wilayah`
- `tb_departemen`
- `tb_karyawan`
- `tb_menu`
- `tb_akses_menu`
- `tb_akses_level`

## Hasil analisis yang wajib dibuat

Dokumentasikan:

1. Proses pembuatan Sales Order.
2. Proses pembuatan faktur penjualan.
3. Proses konfirmasi faktur.
4. Proses stock keluar.
5. Proses pembayaran customer.
6. Proses retur penjualan.
7. Proses pembuatan Purchase Order.
8. Proses LPB/penerimaan barang.
9. Proses input invoice supplier.
10. Proses pembayaran supplier.
11. Proses retur pembelian.
12. Proses mutasi stock.
13. Proses stock adjustment.
14. Proses pembatalan transaksi.
15. Titik source transaction yang paling aman untuk posting jurnal.

Simpan hasil analisis pada:

`application/libraries/Accounting/docs/database-analysis.md`

## SQL audit yang wajib dibuat

Buat query untuk memeriksa:

- duplicate kode barang;
- duplicate customer;
- duplicate supplier;
- duplicate nomor faktur;
- duplicate nomor LPB;
- orphan faktur detail;
- orphan Sales Order detail;
- orphan LPB detail;
- orphan LPB batch;
- orphan pembayaran;
- HPP nol;
- harga jual nol;
- nominal negatif;
- tanggal yang masih menggunakan TEXT;
- tanggal yang tidak dapat dikonversi;
- perbedaan tipe data `gudang_id`;
- perbedaan supplier antara `tb_suplier` dan `tbpo_suplier`;
- faktur tanpa detail;
- LPB tanpa detail;
- pembayaran melebihi nilai faktur;
- faktur tanpa customer valid;
- PO tanpa supplier valid.

Jangan memperbaiki data secara otomatis sebelum membuat laporan temuan.

---

# TAHAP 2 — STRUKTUR DATABASE ACCOUNTING

Gunakan prefix:

`tbkeu_`

## 1. `tbkeu_klasifikasi_akun`

Kolom:

- `id_klasifikasi` TINYINT UNSIGNED primary key
- `kode_klasifikasi` VARCHAR(10) unique
- `nama_klasifikasi` VARCHAR(100)
- `alias_klasifikasi` VARCHAR(100)
- `jenis_laporan` ENUM('NERACA','LABA_RUGI')
- `saldo_normal` ENUM('DEBIT','KREDIT')
- `urutan` SMALLINT UNSIGNED
- `is_active` TINYINT(1)
- `created_at`
- `updated_at`

Seed data:

1. Harta / Asset
2. Kewajiban / Liabilities
3. Modal / Equity
4. Pendapatan / Revenues
5. Beban Atas Pendapatan / Cost of Revenues
6. Beban Operasional / Operating Expenses
7. Beban Non Operasional / Non Operating Expenses
8. Pendapatan Lain / Other Revenues
9. Beban Lain / Other Expenses

Aturan saldo normal:

- Harta: DEBIT
- Kewajiban: KREDIT
- Modal: KREDIT
- Pendapatan: KREDIT
- Beban Atas Pendapatan: DEBIT
- Beban Operasional: DEBIT
- Beban Non Operasional: DEBIT
- Pendapatan Lain: KREDIT
- Beban Lain: DEBIT

## 2. `tbkeu_akun`

Kolom:

- `id_akun` BIGINT UNSIGNED auto increment
- `kode_akun` VARCHAR(30)
- `nama_akun` VARCHAR(150)
- `id_klasifikasi` TINYINT UNSIGNED
- `parent_id` BIGINT UNSIGNED nullable
- `level_akun` TINYINT UNSIGNED
- `saldo_normal` ENUM('DEBIT','KREDIT')
- `tipe_akun` ENUM('HEADER','POSTING')
- `tipe_kontrol` ENUM(
  'NONE',
  'KAS',
  'BANK',
  'PIUTANG',
  'HUTANG',
  'PERSEDIAAN',
  'GRNI',
  'PAJAK_MASUKAN',
  'PAJAK_KELUARAN',
  'UANG_MUKA_CUSTOMER',
  'UANG_MUKA_SUPPLIER',
  'LABA_DITAHAN'
  )
- `allow_manual_journal` TINYINT(1)
- `is_active` TINYINT(1)
- `created_by`
- `created_at`
- `updated_by`
- `updated_at`

Constraint:

- unique `kode_akun`;
- foreign key `id_klasifikasi`;
- foreign key self-reference `parent_id`;
- akun tidak boleh menjadi parent bagi dirinya sendiri;
- akun `HEADER` tidak boleh digunakan pada jurnal;
- akun nonaktif tidak boleh digunakan untuk transaksi baru;
- akun yang sudah pernah digunakan jurnal tidak boleh dihapus;
- akun hanya dapat dinonaktifkan.

## 3. `tbkeu_periode_fiskal`

Kolom:

- `id_periode` BIGINT UNSIGNED
- `kode_periode` VARCHAR(10)
- `tahun` SMALLINT UNSIGNED
- `bulan` TINYINT UNSIGNED
- `tanggal_mulai` DATE
- `tanggal_selesai` DATE
- `status` ENUM('OPEN','CLOSED')
- `closed_by`
- `closed_at`
- `reopened_by`
- `reopened_at`
- `reopen_reason`
- `created_at`
- `updated_at`

Unique:

- `kode_periode`
- `tahun + bulan`

Aturan:

- hanya satu periode untuk satu bulan;
- jurnal tidak boleh dibuat pada periode `CLOSED`;
- jurnal tidak boleh diposting pada periode `CLOSED`;
- reversal juga tidak boleh dilakukan pada periode `CLOSED`;
- reopen periode harus memiliki hak akses khusus dan alasan.

## 4. `tbkeu_jenis_jurnal`

Kolom:

- `id_jenis_jurnal`
- `kode_jurnal`
- `nama_jurnal`
- `prefix_nomor`
- `source_module`
- `is_auto_post`
- `is_active`
- `created_at`
- `updated_at`

Seed:

- GJ — General Journal
- SJ — Sales Journal
- PJ — Purchase Journal
- CR — Cash Receipt
- CD — Cash Disbursement
- IJ — Inventory Journal
- RJ — Reversal Journal

## 5. `tbkeu_nomor_dokumen`

Kolom:

- `id`
- `id_jenis_jurnal`
- `tahun`
- `bulan`
- `last_number`
- `format_nomor`
- `created_at`
- `updated_at`

Unique:

`id_jenis_jurnal + tahun + bulan`

Contoh format:

`SJ/2026/07/000001`

Gunakan:

`SELECT ... FOR UPDATE`

untuk mencegah nomor jurnal ganda pada concurrent request.

## 6. `tbkeu_jurnal`

Kolom:

- `id_jurnal` BIGINT UNSIGNED
- `nomor_jurnal` VARCHAR(50)
- `id_jenis_jurnal`
- `tanggal_transaksi` DATE
- `id_periode`
- `keterangan` VARCHAR(500)
- `source_module` VARCHAR(50)
- `source_type` VARCHAR(50)
- `source_id` VARCHAR(100)
- `source_no` VARCHAR(100)
- `posting_event` VARCHAR(50)
- `status` ENUM('DRAFT','POSTED','REVERSED','VOID')
- `total_debit` DECIMAL(19,4)
- `total_kredit` DECIMAL(19,4)
- `reversal_of_journal_id` BIGINT UNSIGNED nullable
- `idempotency_key` VARCHAR(255)
- `created_by`
- `created_at`
- `updated_by`
- `updated_at`
- `posted_by`
- `posted_at`
- `reversed_by`
- `reversed_at`
- `voided_by`
- `voided_at`
- `void_reason`
- `lock_version` INT UNSIGNED default 0

Unique:

- `nomor_jurnal`
- `idempotency_key`
- `source_module + source_type + source_id + posting_event`

Index:

- tanggal transaksi;
- periode;
- status;
- source module;
- source type;
- source ID;
- source number;
- reversal ID.

## 7. `tbkeu_jurnal_detail`

Kolom:

- `id_jurnal_detail` BIGINT UNSIGNED
- `id_jurnal` BIGINT UNSIGNED
- `nomor_baris` SMALLINT UNSIGNED
- `id_akun` BIGINT UNSIGNED
- `keterangan` VARCHAR(500)
- `debit` DECIMAL(19,4) default 0
- `kredit` DECIMAL(19,4) default 0
- `id_customer` BIGINT nullable
- `id_supplier` BIGINT nullable
- `id_barang` BIGINT nullable
- `id_gudang` BIGINT nullable
- `id_departemen` BIGINT nullable
- `tanggal_jatuh_tempo` DATE nullable
- `nomor_dokumen` VARCHAR(100) nullable
- `created_at`
- `updated_at`

Unique:

`id_jurnal + nomor_baris`

Constraint:

- debit tidak boleh negatif;
- kredit tidak boleh negatif;
- satu baris tidak boleh memiliki debit dan kredit sekaligus;
- salah satu debit atau kredit harus lebih besar dari nol;
- akun harus bertipe `POSTING`;
- akun harus aktif;
- journal detail tidak boleh diubah setelah jurnal `POSTED`.

Gunakan `ON DELETE RESTRICT`.

## 8. `tbkeu_mapping_akun`

Kolom:

- `id_mapping`
- `event_code`
- `role_code`
- `scope_type` ENUM(
  'GLOBAL',
  'BARANG',
  'KATEGORI_BARANG',
  'SUPPLIER',
  'CUSTOMER',
  'GUDANG',
  'DEPARTEMEN',
  'METODE_PEMBAYARAN'
  )
- `scope_key`
- `id_akun`
- `priority`
- `is_active`
- `created_by`
- `created_at`
- `updated_by`
- `updated_at`

Unique:

`event_code + role_code + scope_type + scope_key`

Aturan resolusi akun:

1. Mapping paling spesifik.
2. Mapping berdasarkan barang/customer/supplier/gudang.
3. Mapping kategori.
4. Mapping global.
5. Jika tidak ditemukan, posting harus gagal.
6. Jangan menggunakan fallback kode akun hardcode.

## 9. `tbkeu_jurnal_log`

Kolom:

- `id_log`
- `id_jurnal`
- `action`
- `old_data_json`
- `new_data_json`
- `performed_by`
- `ip_address`
- `user_agent`
- `created_at`

Action:

- CREATE
- UPDATE
- DELETE_DRAFT
- POST
- REVERSE
- VOID
- FAILED_POSTING
- REOPEN_PERIOD
- CLOSE_PERIOD

## 10. `tbkeu_posting_exception`

Kolom:

- `id_exception`
- `source_module`
- `source_type`
- `source_id`
- `source_no`
- `posting_event`
- `exception_code`
- `exception_message`
- `payload_json`
- `status` ENUM('OPEN','RESOLVED','IGNORED')
- `resolved_by`
- `resolved_at`
- `resolution_note`
- `created_at`
- `updated_at`

Exception code minimal:

- ACCOUNT_MAPPING_NOT_FOUND
- HPP_NOT_FOUND
- PERIOD_CLOSED
- JOURNAL_NOT_BALANCED
- SOURCE_NOT_FOUND
- SOURCE_ALREADY_POSTED
- CUSTOMER_NOT_FOUND
- SUPPLIER_NOT_FOUND
- INVALID_AMOUNT
- INVALID_SOURCE_STATUS
- INVALID_TRANSACTION_DATE
- DATABASE_ERROR

## 11. `tbkeu_faktur_pembelian`

Buat struktur faktur supplier yang terpisah dari LPB.

Kolom minimal:

- `id_faktur_pembelian`
- `nomor_internal`
- `nomor_invoice_supplier`
- `id_supplier`
- `kd_supplier`
- `nama_supplier_snapshot`
- `tanggal_invoice`
- `tanggal_jatuh_tempo`
- `id_po` nullable
- `no_po` nullable
- `id_lpb` nullable
- `no_lpb` nullable
- `subtotal`
- `diskon`
- `dpp`
- `pajak`
- `biaya_lain`
- `grand_total`
- `status` ENUM(
  'DRAFT',
  'CONFIRMED',
  'PARTIAL_PAID',
  'PAID',
  'CANCELLED'
  )
- `journal_status` ENUM(
  'NOT_POSTED',
  'POSTED',
  'REVERSED',
  'FAILED'
  )
- `created_by`
- `created_at`
- `updated_by`
- `updated_at`

Unique:

- nomor internal;
- kombinasi supplier dan nomor invoice supplier.

## 12. `tbkeu_faktur_pembelian_detail`

Kolom minimal:

- `id_detail`
- `id_faktur_pembelian`
- `id_lpb_detail` nullable
- `id_po_detail` nullable
- `id_barang`
- `kd_barang`
- `nama_barang_snapshot`
- `qty`
- `satuan`
- `harga_satuan`
- `diskon`
- `dpp`
- `pajak`
- `total`
- `created_at`
- `updated_at`

## 13. `tbkeu_pembayaran`

Kolom:

- `id_pembayaran`
- `nomor_pembayaran`
- `tanggal_pembayaran`
- `tipe_pembayaran` ENUM(
  'CUSTOMER_RECEIPT',
  'SUPPLIER_PAYMENT',
  'OTHER_RECEIPT',
  'OTHER_PAYMENT'
  )
- `id_customer` nullable
- `id_supplier` nullable
- `metode_pembayaran` ENUM(
  'CASH',
  'TRANSFER',
  'GIRO',
  'CHEQUE',
  'OTHER'
  )
- `id_akun_kas_bank`
- `jumlah_pembayaran`
- `nomor_referensi`
- `tanggal_bg_cair` nullable
- `status_bg` ENUM(
  'NOT_BG',
  'PENDING',
  'CLEARED',
  'BOUNCED'
  )
- `status` ENUM(
  'DRAFT',
  'CONFIRMED',
  'POSTED',
  'REVERSED',
  'CANCELLED'
  )
- `keterangan`
- `created_by`
- `created_at`
- `updated_by`
- `updated_at`
- `confirmed_by`
- `confirmed_at`

## 14. `tbkeu_pembayaran_alokasi`

Kolom:

- `id_alokasi`
- `id_pembayaran`
- `document_type` ENUM(
  'SALES_INVOICE',
  'PURCHASE_INVOICE',
  'CUSTOMER_ADVANCE',
  'SUPPLIER_ADVANCE'
  )
- `document_id`
- `document_no`
- `nilai_alokasi`
- `created_at`
- `updated_at`

Constraint:

- total alokasi tidak boleh melebihi jumlah pembayaran;
- nilai alokasi tidak boleh melebihi outstanding dokumen;
- satu pembayaran dapat membayar beberapa faktur;
- satu faktur dapat dibayar beberapa kali.

## 15. `tbkeu_saldo_awal_akun`

Kolom:

- `id_saldo_awal`
- `id_periode`
- `id_akun`
- `debit`
- `kredit`
- `keterangan`
- `status` ENUM('DRAFT','POSTED')
- `created_by`
- `created_at`
- `posted_by`
- `posted_at`

Saldo awal yang diposting harus menghasilkan jurnal pembukaan.

Jangan menjadikan tabel saldo awal sebagai sumber laporan permanen. Setelah diposting, sumber laporan tetap jurnal.

---

# TAHAP 3 — SEED CHART OF ACCOUNTS

Buat seed akun dasar yang dapat dikembangkan user.

Struktur minimal:

## Harta

- Kas
- Bank
- Piutang Usaha
- BG Dalam Pencairan
- Uang Muka Pembelian
- Persediaan Barang
- PPN Masukan
- Biaya Dibayar di Muka
- Aktiva Tetap
- Akumulasi Penyusutan

## Kewajiban

- Hutang Usaha
- GRNI/Barang Diterima Belum Ditagih
- Hutang Pajak
- PPN Keluaran
- Uang Muka Penjualan
- Hutang Biaya

## Modal

- Modal Disetor
- Laba Ditahan
- Laba Tahun Berjalan
- Prive/Dividen

## Pendapatan

- Penjualan
- Potongan Penjualan
- Retur Penjualan

## Beban Atas Pendapatan

- Harga Pokok Penjualan
- Selisih Stock
- Beban Barang Rusak
- Beban Barang Expired

## Beban Operasional

- Beban Gaji
- Beban Transportasi
- Beban Operasional Gudang
- Beban Administrasi
- Beban Penjualan
- Beban Penyusutan

## Pendapatan dan beban lain

- Pendapatan Bunga
- Pendapatan Lain
- Beban Bank
- Beban Bunga
- Beban Lain

Kode akun seed harus mudah diubah melalui migration/seed configuration.

Jangan menghubungkan seed dengan tabel `tbpo_akun_tr`.

---

# TAHAP 4 — ACCOUNT MAPPING

Buat seed event dan role berikut.

## SALES_INVOICE

Role:

- ACCOUNT_RECEIVABLE
- SALES_REVENUE
- SALES_DISCOUNT
- VAT_OUTPUT
- COGS
- INVENTORY

## CUSTOMER_RECEIPT

Role:

- CASH_BANK
- ACCOUNT_RECEIVABLE
- BG_RECEIVABLE

## GOODS_RECEIPT

Role:

- INVENTORY
- GRNI

## PURCHASE_INVOICE

Role:

- GRNI
- ACCOUNT_PAYABLE
- VAT_INPUT
- PURCHASE_PRICE_VARIANCE
- EXPENSE

## SUPPLIER_PAYMENT

Role:

- ACCOUNT_PAYABLE
- CASH_BANK
- BG_PAYABLE

## SALES_RETURN

Role:

- SALES_RETURN
- VAT_OUTPUT
- ACCOUNT_RECEIVABLE
- CASH_BANK
- INVENTORY
- COGS

## PURCHASE_RETURN

Role:

- ACCOUNT_PAYABLE
- CASH_BANK
- INVENTORY
- VAT_INPUT
- PURCHASE_RETURN

## STOCK_ADJUSTMENT_IN

Role:

- INVENTORY
- STOCK_GAIN

## STOCK_ADJUSTMENT_OUT

Role:

- STOCK_LOSS
- INVENTORY

## CUSTOMER_ADVANCE

Role:

- CASH_BANK
- CUSTOMER_ADVANCE

## SUPPLIER_ADVANCE

Role:

- SUPPLIER_ADVANCE
- CASH_BANK

---

# TAHAP 5 — ACCOUNTING POSTING SERVICE

Buat library:

`application/libraries/Accounting/AccountingPostingService.php`

Buat service:

- `JournalNumberService.php`
- `AccountMappingService.php`
- `JournalValidationService.php`
- `AccountingReportService.php`
- `AccountingExceptionService.php`
- `FiscalPeriodService.php`
- `OutstandingService.php`

Buat exception class:

- `AccountingException.php`
- `AccountMappingNotFoundException.php`
- `ClosedPeriodException.php`
- `JournalNotBalancedException.php`
- `DuplicatePostingException.php`

## Method minimal

```php
postSalesInvoice($idFaktur)
reverseSalesInvoice($idFaktur, $reason)

postCustomerReceipt($idPembayaran)
reverseCustomerReceipt($idPembayaran, $reason)

postGoodsReceipt($idLpb)
reverseGoodsReceipt($idLpb, $reason)

postPurchaseInvoice($idFakturPembelian)
reversePurchaseInvoice($idFakturPembelian, $reason)

postSupplierPayment($idPembayaran)
reverseSupplierPayment($idPembayaran, $reason)

postSalesReturn($sourceId)
reverseSalesReturn($sourceId, $reason)

postPurchaseReturn($sourceId)
reversePurchaseReturn($sourceId, $reason)

postStockAdjustment($sourceId)
reverseStockAdjustment($sourceId, $reason)

postManualJournal(array $header, array $lines)
reverseJournal($idJurnal, $reason)
```

## Flow setiap proses posting

1. Mulai database transaction.
2. Ambil dan lock source transaction.
3. Validasi source transaction tersedia.
4. Validasi status source transaction.
5. Validasi tanggal transaksi.
6. Cari periode fiskal.
7. Validasi periode `OPEN`.
8. Buat idempotency key.
9. Periksa apakah transaksi sudah pernah diposting.
10. Ambil account mapping.
11. Validasi seluruh akun.
12. Hitung nilai jurnal.
13. Validasi debit sama dengan kredit.
14. Generate nomor jurnal.
15. Simpan header jurnal.
16. Simpan seluruh detail jurnal.
17. Ubah status jurnal menjadi `POSTED`.
18. Simpan audit log.
19. Tandai source transaction sebagai posted bila tersedia kolom integrasi.
20. Commit.
21. Jika gagal, rollback.
22. Simpan posting exception.

Jangan menyimpan jurnal parsial.

---

# TAHAP 6 — ATURAN POSTING

## Faktur penjualan kredit

Saat faktur penjualan dikonfirmasi:

```text
Debit  Piutang Usaha
Kredit Pendapatan Penjualan
Kredit PPN Keluaran
```

Jurnal HPP:

```text
Debit  Harga Pokok Penjualan
Kredit Persediaan Barang
```

Nilai:

- Piutang = grand total.
- Pendapatan = DPP setelah diskon.
- PPN = pajak.
- HPP = total qty × HPP per barang.

Sumber HPP secara berurutan:

1. `tbso_faktur_detail.hrg_pokok`
2. `tbso_sales_order_detail.hrg_pokok`
3. `tb_master_barang_all.hpp`

Jika seluruh nilai HPP nol atau tidak tersedia:

- jangan auto-post;
- buat `HPP_NOT_FOUND`;
- tampilkan transaksi pada exception dashboard.

## Faktur penjualan tunai

```text
Debit  Kas/Bank
Kredit Pendapatan Penjualan
Kredit PPN Keluaran

Debit  Harga Pokok Penjualan
Kredit Persediaan Barang
```

Akun kas/bank ditentukan oleh metode pembayaran melalui mapping.

## Pembayaran customer

```text
Debit  Kas/Bank
Kredit Piutang Usaha
```

Untuk pembayaran sebagian, kredit piutang hanya sebesar nilai alokasi.

## BG customer

Saat BG diterima:

```text
Debit  BG Dalam Pencairan
Kredit Piutang Usaha
```

Saat BG cair:

```text
Debit  Bank
Kredit BG Dalam Pencairan
```

Saat BG ditolak/bounced:

```text
Debit  Piutang Usaha
Kredit BG Dalam Pencairan
```

## LPB/penerimaan barang

Saat barang diterima:

```text
Debit  Persediaan Barang
Kredit GRNI
```

Nilai persediaan diambil dari harga PO atau nilai penerimaan yang telah diverifikasi.

Jika harga belum tersedia, jangan menghasilkan jurnal dengan nilai nol. Masukkan ke exception.

## Faktur supplier

Saat invoice supplier dikonfirmasi:

```text
Debit  GRNI
Debit  PPN Masukan
Debit/Kredit Selisih Harga Pembelian
Kredit Hutang Usaha
```

Jika invoice supplier tidak terkait barang/LPB, gunakan akun beban berdasarkan mapping.

## Pembayaran supplier

```text
Debit  Hutang Usaha
Kredit Kas/Bank
```

## Retur penjualan

```text
Debit  Retur Penjualan
Debit  PPN Keluaran
Kredit Piutang Usaha/Kas

Debit  Persediaan Barang
Kredit Harga Pokok Penjualan
```

Hanya stock yang secara fisik kembali dan diterima gudang yang boleh menambah persediaan.

## Retur pembelian

```text
Debit  Hutang Usaha/Kas
Kredit Persediaan Barang
Kredit PPN Masukan
```

## Stock adjustment positif

```text
Debit  Persediaan Barang
Kredit Pendapatan/Selisih Stock
```

## Stock adjustment negatif

```text
Debit  Beban/Selisih Stock
Kredit Persediaan Barang
```

## Mutasi antar gudang

Jika hanya perpindahan lokasi dalam satu entitas dan akun persediaannya sama:

- tidak membuat jurnal keuangan;
- hanya membuat stock ledger.

Jika setiap gudang menggunakan akun persediaan berbeda:

```text
Debit  Persediaan Gudang Tujuan
Kredit Persediaan Gudang Asal
```

Keputusan mengikuti mapping akun per gudang.

---

# TAHAP 7 — REVERSAL DAN IMMUTABILITY

Jurnal berstatus `POSTED`:

- tidak boleh diedit;
- tidak boleh dihapus;
- detailnya tidak boleh diubah;
- tanggalnya tidak boleh diubah;
- source reference tidak boleh diubah.

Koreksi dilakukan melalui reversal.

Flow reversal:

1. Validasi jurnal berstatus `POSTED`.
2. Validasi belum pernah direversal.
3. Validasi periode reversal `OPEN`.
4. Buat jurnal baru jenis `RJ`.
5. Balik seluruh debit menjadi kredit.
6. Balik seluruh kredit menjadi debit.
7. Isi `reversal_of_journal_id`.
8. Ubah jurnal awal menjadi `REVERSED`.
9. Simpan alasan.
10. Simpan pengguna.
11. Simpan audit log.
12. Commit dalam satu database transaction.

Jangan menghapus jurnal asli.

---

# TAHAP 8 — CONTROLLER, MODEL, ROUTE, DAN VIEW

## Controller

Buat:

- `application/controllers/keuangan/C_Akun.php`
- `application/controllers/keuangan/C_Jurnal.php`
- `application/controllers/keuangan/C_PeriodeFiskal.php`
- `application/controllers/keuangan/C_MappingAkun.php`
- `application/controllers/keuangan/C_FakturPembelian.php`
- `application/controllers/keuangan/C_Pembayaran.php`
- `application/controllers/keuangan/C_PostingException.php`
- `application/controllers/keuangan/C_LaporanKeuangan.php`

## Model

Buat:

- `application/models/keuangan/M_Akun.php`
- `application/models/keuangan/M_Jurnal.php`
- `application/models/keuangan/M_PeriodeFiskal.php`
- `application/models/keuangan/M_MappingAkun.php`
- `application/models/keuangan/M_FakturPembelian.php`
- `application/models/keuangan/M_Pembayaran.php`
- `application/models/keuangan/M_PostingException.php`
- `application/models/keuangan/M_LaporanKeuangan.php`

## Route minimal

```php
$route['keuangan/akun'] = 'keuangan/C_Akun/index';
$route['keuangan/jurnal'] = 'keuangan/C_Jurnal/index';
$route['keuangan/jurnal/detail/(:num)'] = 'keuangan/C_Jurnal/detail/$1';
$route['keuangan/jurnal/store'] = 'keuangan/C_Jurnal/store';
$route['keuangan/jurnal/post/(:num)'] = 'keuangan/C_Jurnal/post/$1';
$route['keuangan/jurnal/reverse/(:num)'] = 'keuangan/C_Jurnal/reverse/$1';

$route['keuangan/periode'] = 'keuangan/C_PeriodeFiskal/index';
$route['keuangan/periode/close'] = 'keuangan/C_PeriodeFiskal/close';
$route['keuangan/periode/reopen'] = 'keuangan/C_PeriodeFiskal/reopen';

$route['keuangan/mapping-akun'] = 'keuangan/C_MappingAkun/index';

$route['keuangan/faktur-pembelian'] = 'keuangan/C_FakturPembelian/index';
$route['keuangan/pembayaran'] = 'keuangan/C_Pembayaran/index';

$route['keuangan/posting-exception'] = 'keuangan/C_PostingException/index';

$route['keuangan/laporan/jurnal-umum'] = 'keuangan/C_LaporanKeuangan/jurnal_umum';
$route['keuangan/laporan/buku-besar'] = 'keuangan/C_LaporanKeuangan/buku_besar';
$route['keuangan/laporan/neraca-saldo'] = 'keuangan/C_LaporanKeuangan/neraca_saldo';
$route['keuangan/laporan/laba-rugi'] = 'keuangan/C_LaporanKeuangan/laba_rugi';
$route['keuangan/laporan/neraca'] = 'keuangan/C_LaporanKeuangan/neraca';
$route['keuangan/laporan/piutang'] = 'keuangan/C_LaporanKeuangan/piutang';
$route['keuangan/laporan/hutang'] = 'keuangan/C_LaporanKeuangan/hutang';
$route['keuangan/laporan/kas-bank'] = 'keuangan/C_LaporanKeuangan/kas_bank';
```

---

# TAHAP 9 — RESPONSE AJAX

Gunakan format standar:

```json
{
	"success": true,
	"message": "Jurnal berhasil diposting.",
	"data": {},
	"errors": {},
	"meta": {
		"request_id": "",
		"timestamp": ""
	}
}
```

Response gagal:

```json
{
	"success": false,
	"message": "Jurnal gagal diposting.",
	"data": null,
	"errors": {
		"code": "ACCOUNT_MAPPING_NOT_FOUND",
		"details": []
	},
	"meta": {
		"request_id": "",
		"timestamp": ""
	}
}
```

Gunakan HTTP status yang sesuai:

- 200 untuk berhasil;
- 201 untuk create berhasil;
- 400 untuk input invalid;
- 403 untuk akses ditolak;
- 404 untuk source tidak ditemukan;
- 409 untuk duplicate posting atau periode closed;
- 422 untuk jurnal tidak balance;
- 500 untuk database error.

---

# TAHAP 10 — UI MODULE ACCOUNTING

Pertahankan desain existing KARISMA ERP.

## Chart of Accounts

Tampilkan:

- tree akun;
- kode akun;
- nama akun;
- klasifikasi;
- saldo normal;
- tipe HEADER/POSTING;
- tipe kontrol;
- status aktif;
- child account;
- jumlah transaksi;
- tombol tambah;
- tombol edit;
- tombol nonaktifkan.

Akun yang sudah digunakan jurnal tidak memiliki tombol delete.

## Jurnal umum

Header:

- nomor jurnal;
- tanggal;
- jenis jurnal;
- periode;
- keterangan;
- source module;
- source document;
- status.

Detail:

- nomor baris;
- kode akun;
- nama akun;
- keterangan;
- debit;
- kredit;
- customer;
- supplier;
- barang;
- gudang;
- departemen.

Tampilkan:

- total debit;
- total kredit;
- selisih;
- status balance;
- status periode.

Tombol POST hanya aktif jika:

- jurnal `DRAFT`;
- debit sama dengan kredit;
- seluruh akun valid;
- periode `OPEN`;
- total jurnal lebih besar dari nol.

## Mapping akun

Buat halaman konfigurasi:

- event;
- role;
- scope type;
- scope key;
- akun;
- prioritas;
- status.

Tampilkan warning apabila event wajib belum lengkap.

## Posting preview

Sebelum auto-post, tampilkan:

- nomor source document;
- tanggal;
- akun debit;
- akun kredit;
- nominal;
- mapping yang dipakai;
- HPP;
- warning;
- total debit;
- total kredit;
- status balance.

## Exception dashboard

Tampilkan:

- source module;
- source type;
- source number;
- tanggal;
- exception code;
- pesan;
- jumlah retry;
- status;
- tombol lihat detail;
- tombol retry;
- tombol tandai resolved.

---

# TAHAP 11 — HAK AKSES

Integrasikan dengan:

- `tb_menu`
- `tb_akses_menu`
- `tb_akses_level`

Permission minimal:

- view chart of accounts;
- create account;
- edit account;
- deactivate account;
- view journal;
- create manual journal;
- edit draft journal;
- post journal;
- reverse journal;
- view fiscal period;
- close fiscal period;
- reopen fiscal period;
- configure account mapping;
- create purchase invoice;
- verify purchase invoice;
- create payment;
- confirm payment;
- view posting exception;
- retry posting;
- view reports;
- export reports.

Jangan hardcode akses berdasarkan username, NIK, jobdesk, atau nama karyawan.

---

# TAHAP 12 — LAPORAN

Semua laporan hanya membaca:

- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`

dengan status jurnal:

`POSTED`

Jurnal `DRAFT`, `VOID`, dan jurnal asal berstatus `REVERSED` tidak boleh dihitung sebagai saldo aktif.

## Jurnal Umum

Tampilkan:

- tanggal;
- nomor jurnal;
- source;
- akun;
- keterangan;
- debit;
- kredit.

## Buku Besar

Filter:

- akun;
- tanggal;
- periode;
- customer;
- supplier;
- barang;
- gudang;
- departemen.

Rumus akun saldo normal debit:

```text
Saldo akhir = saldo awal + debit - kredit
```

Rumus akun saldo normal kredit:

```text
Saldo akhir = saldo awal + kredit - debit
```

## Neraca Saldo

Tampilkan:

- kode akun;
- nama akun;
- saldo awal debit;
- saldo awal kredit;
- mutasi debit;
- mutasi kredit;
- saldo akhir debit;
- saldo akhir kredit.

Validasi:

```text
Total debit = total kredit
```

## Laba Rugi

Sumber:

- Pendapatan
- Beban Atas Pendapatan
- Beban Operasional
- Beban Non Operasional
- Pendapatan Lain
- Beban Lain

Rumus:

```text
Penjualan Bersih
- Harga Pokok Penjualan
= Laba Kotor

Laba Kotor
- Beban Operasional
= Laba Operasional

Laba Operasional
+ Pendapatan Lain
- Beban Non Operasional
- Beban Lain
= Laba Bersih
```

## Neraca

Sumber:

- Harta
- Kewajiban
- Modal

Validasi:

```text
Harta = Kewajiban + Modal
```

Laba berjalan harus masuk dalam komponen ekuitas.

## Aging Piutang

Gunakan:

- faktur penjualan;
- alokasi pembayaran;
- retur;
- credit note;
- uang muka customer.

Bucket:

- belum jatuh tempo;
- 1–30 hari;
- 31–60 hari;
- 61–90 hari;
- lebih dari 90 hari.

## Aging Hutang

Gunakan:

- faktur pembelian;
- alokasi pembayaran supplier;
- retur pembelian;
- debit note;
- uang muka supplier.

## Kas dan Bank

Tampilkan:

- saldo awal;
- penerimaan;
- pengeluaran;
- saldo akhir;
- transaksi per rekening;
- sumber transaksi;
- nomor referensi.

## Audit source document

Tampilkan relasi:

```text
Source document
-> Posting event
-> Nomor jurnal
-> Detail akun
-> Reversal journal
```

---

# TAHAP 13 — EXPORT DAN PRINT

Sediakan:

- export Excel;
- export PDF;
- print preview.

Laporan wajib memiliki:

- nama perusahaan;
- nama laporan;
- periode;
- tanggal cetak;
- user pencetak;
- nomor halaman;
- total.

Jangan menghitung ulang laporan menggunakan JavaScript.

Seluruh perhitungan dilakukan di backend.

---

# TAHAP 14 — PENGUJIAN

Buat pengujian untuk:

1. Create akun HEADER.
2. Create akun POSTING.
3. Duplicate kode akun ditolak.
4. Akun HEADER tidak dapat digunakan jurnal.
5. Akun nonaktif tidak dapat digunakan jurnal baru.
6. Jurnal manual balance berhasil.
7. Jurnal tidak balance ditolak.
8. Debit dan kredit dalam satu baris ditolak.
9. Nominal negatif ditolak.
10. Posting pada periode CLOSED ditolak.
11. Duplicate source posting ditolak.
12. Concurrent journal numbering tidak menghasilkan nomor sama.
13. Faktur kredit menghasilkan piutang.
14. Faktur tunai menghasilkan kas/bank.
15. Penjualan menghasilkan jurnal HPP.
16. HPP nol menghasilkan exception.
17. LPB menghasilkan Persediaan dan GRNI.
18. Faktur supplier menghasilkan Hutang dan GRNI.
19. Selisih harga pembelian tercatat.
20. Pembayaran customer mengurangi piutang.
21. Pembayaran sebagian menghasilkan outstanding.
22. Pembayaran supplier mengurangi hutang.
23. Satu pembayaran dapat membayar beberapa faktur.
24. BG pending tidak masuk akun bank.
25. BG cair masuk akun bank.
26. BG bounced mengembalikan piutang.
27. Jurnal POSTED tidak dapat diedit.
28. Reversal membalik debit dan kredit.
29. Jurnal tidak dapat direversal dua kali.
30. Trial balance seimbang.
31. Neraca seimbang.
32. Laba rugi sesuai periode.
33. Aging piutang sesuai outstanding.
34. Aging hutang sesuai outstanding.
35. Database rollback bekerja saat detail gagal.
36. `tbpo_transaksi` tidak berubah.
37. `tbpo_transaksi_tmp` tidak berubah.
38. `tbpo_transaksi_trashbin` tidak berubah.
39. `tbpo_akun_tr` tidak berubah.
40. Tidak ada query modul accounting yang membaca atau menulis empat tabel tersebut.

---

# TAHAP 15 — MIGRATION SAFETY

Buat:

- migration up;
- migration down;
- seed klasifikasi;
- seed jenis jurnal;
- seed chart of accounts;
- seed account mapping template;
- SQL audit;
- SQL verification.

Sebelum migration:

1. Backup database.
2. Hitung jumlah row setiap tabel source.
3. Simpan total nominal transaksi utama.
4. Periksa duplicate key.
5. Periksa orphan data.
6. Periksa ukuran kolom.
7. Periksa kompatibilitas MariaDB 10.4.

Setelah migration:

1. Verifikasi seluruh tabel baru.
2. Verifikasi index.
3. Verifikasi foreign key.
4. Verifikasi seed.
5. Verifikasi tidak ada perubahan tabel di luar scope.
6. Verifikasi data existing tetap sama.
7. Verifikasi empat tabel Purchase Order yang dikecualikan tidak berubah.

---

# TAHAP 16 — DOKUMENTASI

Simpan dokumentasi pada:

- `application/libraries/Accounting/docs/README.md`
- `application/libraries/Accounting/docs/database-analysis.md`
- `application/libraries/Accounting/docs/database-schema.md`
- `application/libraries/Accounting/docs/chart-of-accounts.md`
- `application/libraries/Accounting/docs/account-mapping.md`
- `application/libraries/Accounting/docs/posting-flow.md`
- `application/libraries/Accounting/docs/report-formulas.md`
- `application/libraries/Accounting/docs/deployment.md`
- `application/libraries/Accounting/docs/rollback.md`

Dokumentasi wajib menjelaskan:

1. ERD modul accounting.
2. Fungsi setiap tabel.
3. Foreign key.
4. Index.
5. Chart of Accounts.
6. Account mapping.
7. Source transaction.
8. Posting event.
9. Rumus setiap jurnal.
10. Alur reversal.
11. Alur period closing.
12. Alur laporan.
13. Error handling.
14. Idempotency.
15. Concurrent posting.
16. Deployment.
17. Rollback.
18. Tabel yang berada di luar scope.

Tuliskan secara eksplisit:

```text
tbpo_transaksi, tbpo_transaksi_tmp, tbpo_transaksi_trashbin,
dan tbpo_akun_tr merupakan domain aplikasi Purchase Order tersendiri.

Modul accounting KARISMA ERP tidak boleh membaca, menulis,
mengubah, memigrasikan, atau membuat dependency terhadap tabel tersebut.
```

---

# OUTPUT WAJIB CODEX

Setelah implementasi, hasilkan:

1. Ringkasan analisis database.
2. Daftar masalah data existing.
3. Daftar asumsi bisnis.
4. Daftar file baru.
5. Daftar file yang diubah.
6. SQL migration up.
7. SQL migration down.
8. SQL seed.
9. SQL audit.
10. SQL verification.
11. ERD.
12. Matrix source transaction versus jurnal.
13. Matrix event versus account role.
14. Daftar mapping akun wajib.
15. Dokumentasi endpoint.
16. Dokumentasi AJAX response.
17. Dokumentasi hak akses.
18. Dokumentasi laporan.
19. Hasil testing.
20. Langkah deployment.
21. Langkah rollback.
22. Bukti bahwa tabel Purchase Order yang dikecualikan tidak berubah.

---

# DEFINITION OF DONE

Pekerjaan dianggap selesai apabila:

- Chart of Accounts telah tersedia;
- akun memiliki hierarki;
- akun HEADER dan POSTING terpisah;
- jurnal menggunakan double-entry;
- tidak ada jurnal POSTED yang tidak balance;
- jurnal POSTED tidak dapat diedit;
- reversal dapat ditelusuri;
- periode CLOSED tidak dapat menerima transaksi;
- source transaction tidak dapat diposting dua kali;
- kode akun tidak di-hardcode;
- mapping akun dapat dikonfigurasi;
- faktur penjualan dapat ditelusuri ke jurnal;
- LPB dapat ditelusuri ke jurnal;
- invoice supplier dapat ditelusuri ke jurnal;
- pembayaran dapat dialokasikan ke beberapa faktur;
- piutang dapat dihitung;
- hutang dapat dihitung;
- buku besar berasal dari jurnal detail;
- trial balance seimbang;
- neraca seimbang;
- laba rugi menggunakan klasifikasi akun;
- audit trail tersedia;
- exception posting dapat dipantau;
- data existing tidak rusak;
- mekanisme login dan session tidak berubah;
- `tbpo_transaksi` tidak berubah;
- `tbpo_transaksi_tmp` tidak berubah;
- `tbpo_transaksi_trashbin` tidak berubah;
- `tbpo_akun_tr` tidak berubah;
- modul accounting tidak memiliki dependency terhadap empat tabel tersebut.
