# Accounting Runtime Transaksi Penuh

Tanggal: 2026-07-13

## File Implementasi

- Service runtime: `application/libraries/Accounting_service.php`
- Controller testing/dashboard: `application/controllers/keuangan/C_Accounting.php`
- View testing: `application/views/content/keuangan/accounting_runtime_test.php`
- Route: `accounting-test` dan alias `keuangan/accounting-test`
- SQL runtime: `docs/database/accounting_runtime_full_20260713.sql`

## Urutan SQL

Jalankan berurutan:

1. `docs/database/accounting_jurnal_accounts_20260713.sql`
2. `docs/database/accounting_jurnal_master_options_20260713.sql`
3. `docs/database/accounting_general_ledger_journal_20260713.sql`
4. `docs/database/accounting_runtime_full_20260713.sql`

Pada database lokal `kiucoid_karismaerp_local`, file runtime sudah dijalankan dan menghasilkan:

- 28 mapping akun aktif.
- 10 dummy source bisnis distributor agro di `tbkeu_dummy_source`.
- Tabel `tbkeu_mapping_akun`.
- Tabel `tbkeu_jurnal_log`.
- Tabel `tbkeu_posting_exception`.
- Tabel `tbkeu_nomor_dokumen`.
- Tabel `tbkeu_saldo_awal_akun`.

## Halaman Manual Testing

Buka:

```text
accounting-test
```

Fungsi pada halaman:

- Input jurnal manual sebagai `DRAFT`.
- Posting jurnal manual dari `DRAFT` ke `POSTED`.
- Auto-post dummy distributor agro untuk sales, purchase, LPB, payment, retur, mutasi, dan stock adjustment.
- Reversal jurnal `POSTED`.
- Daftar jurnal.
- Dashboard exception posting.
- Laporan buku besar, neraca saldo, laba rugi, neraca, piutang, hutang, dan kas/bank.

## Aturan Runtime

Semua posting melewati `Accounting_service`.

Aturan yang dijaga:

- Jurnal minimal dua baris.
- Setiap baris hanya boleh debit atau kredit.
- Total debit wajib sama dengan total kredit.
- Nominal tidak boleh negatif.
- Akun transaksi wajib `POSTING`.
- Akun transaksi wajib aktif.
- Jika kolom `is_transaction_eligible` tersedia, nilainya wajib `1`.
- Manual journal hanya boleh memakai akun dengan `allow_manual_journal = 1`.
- Jurnal `POSTED` tidak diedit langsung oleh service.
- Koreksi dilakukan dengan reversal.
- Laporan hanya membaca jurnal `POSTED`.
- Auto-posting selalu resolve akun dari `tbkeu_mapping_akun`.
- Posting auto menggunakan database transaction.
- Jika posting auto gagal, transaction rollback dan error masuk `tbkeu_posting_exception`.

## Method Service

Manual:

```php
$this->load->library('Accounting_service');
$result = $this->accounting_service->input_manual_journal($payload, $userId);
$result = $this->accounting_service->validate_and_post_journal($idJurnal, $userId);
$result = $this->accounting_service->reversal_journal($idJurnal, $reason, $userId);
```

Auto-posting:

```php
$this->accounting_service->post_sales($payload, $userId);
$this->accounting_service->post_purchase($payload, $userId);
$this->accounting_service->post_lpb($payload, $userId);
$this->accounting_service->post_payment($payload, $userId);
$this->accounting_service->post_retur($payload, $userId);
$this->accounting_service->post_mutasi($payload, $userId);
$this->accounting_service->post_stock_adjustment($payload, $userId);
```

Generic:

```php
$this->accounting_service->post_auto('SALES_INVOICE', $payload, $userId);
```

Payload minimum:

```php
$payload = [
    'tanggal_transaksi' => '2026-07-13',
    'source_module' => 'SALES',
    'source_type' => 'INVOICE',
    'source_id' => '123',
    'source_no' => 'INV-AGRO-20260713-001',
    'idempotency_key' => 'SALES-INVOICE-AGRO-123',
    'amount' => 8880000,
    'tax' => 976800,
    'cogs' => 6144000,
    'keterangan' => 'Faktur penjualan Herbisida GulmaClean 1L ke Koperasi Tani Subur Jaya',
];
```

## Dummy Bisnis Distributor Agro

Dummy source memakai variabel SQL pada `docs/database/accounting_runtime_full_20260713.sql` agar nilai bisnis mudah diganti.

Contoh skenario:

- `SALES_INVOICE`: penjualan `Herbisida GulmaClean 1L` 48 botol ke `Koperasi Tani Subur Jaya`.
- `GOODS_RECEIPT`: LPB `Pupuk NPK GrowMax 25kg` 120 sak dari `PT Agro Saprodi Nusantara`.
- `PURCHASE_INVOICE`: invoice supplier untuk pembelian pupuk.
- `CUSTOMER_PAYMENT`: pelunasan faktur penjualan agro.
- `SUPPLIER_PAYMENT`: pembayaran supplier saprodi.
- `SALES_RETURN`: retur kemasan rusak obat pertanian.
- `PURCHASE_RETURN`: retur batch rusak benih jagung.
- `STOCK_TRANSFER`: mutasi fungisida dari gudang induk ke gudang rusak.
- `STOCK_ADJUSTMENT_OUT`: adjustment keluar sprayer rusak/expired.
- `STOCK_ADJUSTMENT_IN`: adjustment masuk selisih opname herbisida.

## Event Auto Posting

Event yang tersedia:

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

Semua akun dicari dengan kombinasi:

- `source_module`
- `source_type`
- `posting_event`
- `account_role`
- `entry_side`

Wildcard `*` boleh dipakai di mapping.

## Catatan Integrasi

Service sudah siap dipanggil oleh controller/model sales, purchase, LPB, payment, retur, mutasi, dan stock adjustment.

Integrasi langsung ke controller operasional belum dipasang otomatis karena audit sebelumnya menemukan beberapa source final yang belum seragam:

- Final event penjualan masih perlu dikunci antara faktur baru dan DO existing.
- Source pembayaran existing belum sepenuhnya memuat nominal/metode/alokasi.
- Harga LPB perlu dipastikan dari PO/adjustment yang authoritative.
- Stock adjustment perlu status final approval.

Untuk sementara, halaman `accounting-test` menjadi alat UAT/manual testing tanpa mengganggu flow operasional existing.
