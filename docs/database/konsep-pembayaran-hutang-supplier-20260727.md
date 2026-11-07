# Konsep Database dan Jurnal Pembayaran Hutang Supplier

Tanggal: 2026-07-27  
Scope: database, tabel, relasi, dan jurnal untuk module pembayaran hutang perusahaan ke supplier/pemasok.

## Ringkasan Hasil Scanning Database Lokal

Database aktif: `kiucoid_karismaerp_local`.

Tabel yang sudah tersedia:

- `tb_lpb`
- `tb_lpb_detail`
- `tb_lpb_batch`
- `tbpo_po`
- `tbpo_detail_po`
- `tb_pre_po`
- `tb_pre_po_invoice_adjustment`
- `tb_retur_pembelian`
- `tb_retur_pembelian_detail`
- `tbkeu_jurnal`
- `tbkeu_jurnal_detail`
- `tbkeu_mapping_akun`
- `tbkeu_pembayaran`
- `tbkeu_pembayaran_alokasi`
- `tbkeu_pembayaran_faktur`

Jumlah data saat scan:

| Tabel | Jumlah Row |
| --- | ---: |
| `tb_lpb` | 11 |
| `tb_lpb_detail` | 12 |
| `tb_retur_pembelian` | 6 |
| `tb_retur_pembelian_detail` | 6 |
| `tbkeu_pembayaran` | 0 |
| `tbkeu_pembayaran_alokasi` | 0 |
| `tbkeu_pembayaran_faktur` | 0 |
| `tbkeu_jurnal` | 14 |

Kesimpulan: struktur AP payment sudah ada, tetapi belum dipakai oleh transaksi pembayaran supplier lokal.

## Tabel Yang Dipertahankan

### `tbkeu_pembayaran`

Dipertahankan sebagai header pembayaran AR/AP.

Kolom penting:

- `payment_type`: `CUSTOMER_PAYMENT` atau `SUPPLIER_PAYMENT`
- `nomor_pembayaran`
- `tanggal_pembayaran`
- `source_module`
- `source_type`
- `source_id`
- `source_no`
- `id_customer`
- `id_supplier`
- `amount`
- `allocated_amount`
- `unapplied_amount`
- `status`: `DRAFT`, `POSTED`, `VOID`
- `id_jurnal`

Untuk supplier payment, isi yang disarankan:

- `payment_type = SUPPLIER_PAYMENT`
- `source_module = KEUANGAN`
- `source_type = SUPPLIER_PAYMENT`
- `id_supplier` wajib terisi jika supplier dapat di-resolve.
- `status = POSTED` setelah jurnal berhasil.

### `tbkeu_pembayaran_alokasi`

Dipertahankan sebagai detail alokasi pembayaran ke invoice/LPB.

Kolom penting:

- `id_pembayaran`
- `nomor_baris`
- `invoice_source_module`
- `invoice_source_type`
- `invoice_source_id`
- `invoice_no`
- `amount_allocated`
- `keterangan`

Untuk supplier payment, isi yang disarankan:

- `invoice_source_module = LOGISTIK`
- `invoice_source_type = LPB_FINAL` atau `RETUR_PEMBELIAN` sesuai kebutuhan alokasi
- `invoice_source_id = tb_lpb.id_lpb`
- `invoice_no = nomor_lpb` atau nomor invoice supplier yang disepakati sebagai dokumen hutang

Catatan penting: `Accounting_service::create_payment()` saat ini mengecek outstanding berdasarkan `invoice_no` terhadap `tbkeu_jurnal_detail.nomor_dokumen`.

## Tabel Source Operasional

### `tb_lpb`

Kolom penting yang sudah ada:

- `id_lpb`
- `kd_po`
- `no_po`
- `no_invoice`
- `tanggal_invoice`
- `kode_faktur_pajak`
- `tanggal_faktur_pajak`
- `nomor_lpb`
- `status_lpb`
- `gudang_id`
- `source_type`
- `manual_ref_no`

Peran:

- source utama tagihan/hutang supplier setelah barang diterima;
- nomor dokumen posting saat ini memakai `nomor_lpb`;
- invoice supplier disimpan di `no_invoice` dan `tanggal_invoice`.

### `tb_lpb_detail`

Kolom penting yang sudah ada:

- `id_detail_lpb`
- `id_lpb`
- `kd_barang`
- `qty_diterima`
- `harga_satuan`
- `total_harga`
- `no_lot`
- `expired_date`
- `harga_verified_by`
- `harga_verified_at`

Peran:

- dasar nilai DPP LPB;
- harus lengkap sebelum pembayaran;
- harus menjadi basis tampilan detail tagihan.

### `tb_retur_pembelian`

Kolom penting yang sudah ada:

- `id_retur_pembelian`
- `no_retur_pembelian`
- `id_lpb`
- `kd_supplier`
- `status`
- `jenis_penyelesaian`
- `total_dpp`
- `total_ppn`
- `grand_total`
- `id_jurnal`
- `id_jurnal_reversal`

Peran:

- pengurang hutang bila `status = POSTED`, `jenis_penyelesaian = POTONG_HUTANG`, dan `reversed_at IS NULL`;
- jangan dihitung sebagai pengurang hutang jika `VOID` atau `POSTING_EXCEPTION`.

### `tbkeu_jurnal` dan `tbkeu_jurnal_detail`

Peran:

- sumber utama saldo hutang, bukan tabel stock ledger;
- hanya jurnal `POSTED` yang dihitung;
- jurnal yang sudah `reversed_at` harus dikeluarkan dari outstanding.

## Temuan Akun dan Mapping

Mapping aktif yang ditemukan:

| Event | Role | Side | Count |
| --- | --- | --- | ---: |
| `GOODS_RECEIPT` | `INVENTORY` | `DEBIT` | 1 |
| `GOODS_RECEIPT` | `GRNI` | `KREDIT` | 1 |
| `PURCHASE_INVOICE` | `GRNI` | `DEBIT` | 1 |
| `PURCHASE_INVOICE` | `VAT_INPUT` | `DEBIT` | 1 |
| `PURCHASE_INVOICE` | `ACCOUNT_PAYABLE` | `KREDIT` | 1 |
| `SUPPLIER_PAYMENT` | `ACCOUNT_PAYABLE` | `DEBIT` | 1 |
| `SUPPLIER_PAYMENT` | `CASH_BANK` | `KREDIT` | 1 |
| `PURCHASE_RETURN` | `ACCOUNT_PAYABLE` | `DEBIT` | 1 |
| `PURCHASE_RETURN` | `INVENTORY` | `KREDIT` | 1 |
| `PURCHASE_RETURN` | `VAT_INPUT` | `KREDIT` | 1 |

Temuan kritis:

- Akun `2100` bernama `Hutang Usaha`, `tipe_kontrol = HUTANG`, status aktif.
- Akun `21098` dipakai oleh jurnal LPB/retur sebagai Hutang Usaha, tetapi pada database lokal:
  - `nama_akun` kosong;
  - `tipe_kontrol = NONE`;
  - `saldo_normal = KREDIT`;
  - `tipe_akun = POSTING`.

Dampak:

- Query outstanding hutang berbasis `tipe_kontrol = HUTANG` tidak membaca jurnal LPB/retur yang memakai `21098`.
- `Accounting_service::create_payment(SUPPLIER_PAYMENT)` bisa menolak alokasi karena outstanding invoice terbaca nol.

## Rekomendasi Perbaikan Database Sebelum Development

Ada dua opsi. Pilih satu sebelum coding modul pembayaran supplier.

### Opsi DB-A - Perbaiki Master Akun 21098

Jika Finance menganggap `21098` adalah akun hutang supplier yang benar:

- isi `nama_akun` menjadi nama akun resmi;
- ubah `tipe_kontrol` menjadi `HUTANG`;
- pastikan `tipe_akun = POSTING`;
- pastikan `is_active = 1`;
- pastikan `is_transaction_eligible = 1` jika kolom tersedia;
- mapping `ACCOUNT_PAYABLE` bisa diarahkan ke `21098`.

Kelebihan:

- jurnal historis LPB/retur langsung terbaca sebagai hutang.

Risiko:

- harus disetujui Finance karena mengubah makna akun existing.

### Opsi DB-B - Gunakan Akun Mapping ACCOUNT_PAYABLE Aktif

Jika Finance ingin akun hutang utama tetap `2100`:

- ubah posting LPB/retur agar memakai mapping `ACCOUNT_PAYABLE`, bukan hardcode `21098`;
- untuk jurnal historis, lakukan mapping ulang atau jurnal koreksi/reclass sesuai arahan Finance;
- jangan update detail jurnal posted secara langsung tanpa approval karena posted jurnal harus immutable.

Kelebihan:

- mengikuti prinsip `tbkeu_mapping_akun`;
- tidak bergantung ke akun kosong.

Risiko:

- perlu strategi migrasi/reclass untuk data lama agar outstanding konsisten.

## Formula Outstanding Supplier

Outstanding per dokumen harus dihitung dari jurnal, bukan dari total LPB saja.

Konsep:

```sql
Outstanding Hutang =
  SUM(kredit akun HUTANG)
  - SUM(debit akun HUTANG)
```

Filter wajib:

- `tbkeu_jurnal.status = 'POSTED'`
- `tbkeu_jurnal.reversed_at IS NULL`
- `tbkeu_akun.tipe_kontrol = 'HUTANG'`
- group by `id_supplier`, `nomor_dokumen`

Jika memakai `nomor_lpb` sebagai dokumen:

- LPB `GOODS_RECEIPT` menambah hutang pada `nomor_dokumen = nomor_lpb`.
- Payment `SUPPLIER_PAYMENT` mengurangi hutang pada `nomor_dokumen = nomor_lpb`.

Jika memakai `no_invoice` supplier sebagai dokumen:

- posting LPB/Purchase Invoice harus menyimpan `nomor_dokumen = no_invoice`.
- semua alokasi payment harus memakai nomor invoice yang sama.

Keputusan yang direkomendasikan:

- fase pertama memakai `nomor_lpb` sebagai key accounting karena jurnal existing `GOODS_RECEIPT` sudah memakai `nomor_lpb`;
- tampilkan `no_invoice` supplier sebagai informasi bisnis;
- fase lanjutan dapat membuat purchase invoice formal bila ingin AP murni per invoice supplier.

## Jurnal Standar

### GOODS_RECEIPT / LPB

Jika kebijakan saat ini langsung mengakui hutang saat LPB:

| Debit | Kredit |
| --- | --- |
| Persediaan |  |
| PPN Masukan jika BKP |  |
|  | Hutang Usaha |

Jika kebijakan memakai GRNI:

| Debit | Kredit |
| --- | --- |
| Persediaan |  |
|  | GRNI |

Lalu saat invoice supplier:

| Debit | Kredit |
| --- | --- |
| GRNI |  |
| PPN Masukan |  |
|  | Hutang Usaha |

Catatan: dokumen lama project menyebut mapping `GOODS_RECEIPT` ke `GRNI`, tetapi implementasi lokal `Accounting_source_service::post_goods_receipt()` memakai kombinasi persediaan, PPN, dan akun `21098`. Ini harus diputuskan ulang agar modul pembayaran tidak salah dasar.

### PURCHASE_RETURN / Retur Pembelian Potong Hutang

| Debit | Kredit |
| --- | --- |
| Hutang Usaha |  |
|  | Persediaan |
|  | PPN Masukan jika BKP |

Hanya berlaku untuk:

- `tb_retur_pembelian.status = POSTED`
- `jenis_penyelesaian = POTONG_HUTANG`
- `reversed_at IS NULL`

### SUPPLIER_PAYMENT / Pembayaran Supplier

| Debit | Kredit |
| --- | --- |
| Hutang Usaha |  |
|  | Kas/Bank |

Detail hutang pada jurnal payment harus dipecah per invoice/LPB agar outstanding turun di dokumen yang benar.

## Kebutuhan Tambahan Schema

Untuk fase pertama, tidak wajib membuat tabel baru karena `tbkeu_pembayaran` dan `tbkeu_pembayaran_alokasi` sudah ada.

Tambahan yang disarankan:

### 1. Payment Method / Account Selection

Jika belum ada master akun kas/bank yang ergonomis di UI, cukup baca dari `tbkeu_akun`:

- `tipe_kontrol IN ('KAS', 'BANK')`
- `tipe_akun = 'POSTING'`
- `is_active = 1`

Jika ingin kontrol lebih ketat, tambah tabel mapping metode pembayaran:

```sql
CREATE TABLE tbkeu_payment_method (
  id_payment_method INT AUTO_INCREMENT PRIMARY KEY,
  payment_direction ENUM('IN','OUT') NOT NULL,
  kode_method VARCHAR(50) NOT NULL,
  nama_method VARCHAR(100) NOT NULL,
  id_akun_kas_bank BIGINT UNSIGNED NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
);
```

Fase pertama boleh tanpa tabel ini jika UI langsung memilih akun kas/bank.

### 2. Attachment Bukti Bayar

Optional fase lanjut:

```sql
CREATE TABLE tbkeu_pembayaran_attachment (
  id_attachment BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pembayaran BIGINT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  mime_type VARCHAR(100) NULL,
  uploaded_by BIGINT NULL,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tbkeu_payment_attachment_payment
    FOREIGN KEY (id_pembayaran)
    REFERENCES tbkeu_pembayaran(id_pembayaran)
    ON UPDATE CASCADE
);
```

### 3. Log Pembayaran

Optional tetapi direkomendasikan untuk audit:

```sql
CREATE TABLE tbkeu_pembayaran_log (
  id_log BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pembayaran BIGINT UNSIGNED NOT NULL,
  action VARCHAR(30) NOT NULL,
  before_json LONGTEXT NULL,
  after_json LONGTEXT NULL,
  reason VARCHAR(500) NULL,
  created_by BIGINT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tbkeu_payment_log_payment
    FOREIGN KEY (id_pembayaran)
    REFERENCES tbkeu_pembayaran(id_pembayaran)
    ON UPDATE CASCADE
);
```

## Query Draft Outstanding Supplier

Query ini hanya valid setelah akun hutang benar-benar punya `tipe_kontrol = HUTANG`.

```sql
SELECT
  d.id_supplier,
  s.nama_suplier,
  d.nomor_dokumen,
  SUM(d.kredit) AS total_hutang,
  SUM(d.debit) AS total_pengurang,
  SUM(d.kredit - d.debit) AS outstanding
FROM tbkeu_jurnal_detail d
JOIN tbkeu_jurnal j
  ON j.id_jurnal = d.id_jurnal
 AND j.status = 'POSTED'
 AND j.reversed_at IS NULL
JOIN tbkeu_akun a
  ON a.id_akun = d.id_akun
 AND a.tipe_kontrol = 'HUTANG'
LEFT JOIN tbpo_suplier s
  ON s.id_suplier = d.id_supplier
GROUP BY d.id_supplier, s.nama_suplier, d.nomor_dokumen
HAVING outstanding > 0
ORDER BY s.nama_suplier, d.nomor_dokumen;
```

## Validasi Sebelum Payment Diposting

Checklist database:

- Schema runtime accounting ready.
- Periode fiskal tanggal pembayaran berstatus `OPEN`.
- Mapping `SUPPLIER_PAYMENT / ACCOUNT_PAYABLE / DEBIT` aktif dan menuju akun posting.
- Mapping `SUPPLIER_PAYMENT / CASH_BANK / KREDIT` aktif dan menuju akun posting.
- Dokumen yang dibayar punya outstanding > 0.
- Supplier dokumen sama dengan supplier header pembayaran.
- Total alokasi <= amount.
- Alokasi per dokumen <= outstanding.
- Tidak ada jurnal payment dengan `idempotency_key` sama.

## Catatan Tidak Ada Schema Baru Wajib

Untuk fase MVP, tidak ada tabel wajib baru. Fokus database adalah:

1. perbaikan/mutasi master akun hutang;
2. memastikan mapping akun supplier payment benar;
3. memakai `tbkeu_pembayaran` dan `tbkeu_pembayaran_alokasi`;
4. menambah attachment/log hanya jika disetujui sebagai kebutuhan audit.
