# PANDUAN TEKNIS & ANALISIS MENYELURUH INPUT SALDO AWAL (ZAHIR KE KARISMAERP)

Dokumen ini merupakan panduan implementasi teknis basis data (*database-level implementation*) dan prosedur operasional bagi Tim IT, Data Analyst, dan Tim Akuntansi KarismaERP untuk memasukkan **Saldo Awal (Opening Balance)** dari sistem Zahir Accounting ke basis data **KarismaERP** secara akurat, aman, dan tanpa menimbulkan duplikasi pencatatan.

---

## 1. Konsep Utama: Arsitektur Saldo Awal Dua Tingkat (Dual-Layer Opening Balance)

Kesalahan paling fatal dalam migrasi sistem akuntansi adalah **hanya menginput Jurnal Saldo Awal gelondongan di General Ledger (GL)**. 
- Jika Piutang Usaha dimasukkan `Debit Rp 1.500.000.000` di jurnal umum, neraca akan terlihat benar. Namun ketika pelanggan membayar lewat kasir atau bank, KarismaERP **tidak dapat memilih faktur mana yang dilunasi**, karena tidak ada nomor faktur individual di sistem.
- Begitu pula dengan Hutang dan Persediaan barang dagang.

Oleh karena itu, KarismaERP menerapkan **Arsitektur Dua Tingkat (Dual-Layer)**:

```
┌────────────────────────────────────────────────────────────────────────┐
│  LEVEL 1: GENERAL LEDGER (GL)                                          │
│  Tabel: `tbkeu_saldo_awal_akun` & `tbkeu_jurnal`                       │
│  - Membentuk Neraca Saldo Awal Perusahaan                              │
│  - Total Debit = Total Kredit                                          │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
               ┌────────────────────┼───────────────────┐
               ▼                    ▼                   ▼
┌─────────────────────────┐ ┌───────────────┐ ┌──────────────────────────┐
│ LEVEL 2A: SUBLEDGER AR  │ │LEVEL 2B: AP   │ │ LEVEL 2C: INVENTORY      │
│ Rincian Invoice Customer│ │Rincian Tagihan│ │ Rincian Qty Fisik & HPP  │
│ Unpaid per Faktur Zahir │ │Unpaid Supplier│ │ per Barang, Gudang & Lot │
└─────────────────────────┘ └───────────────┘ └──────────────────────────┘
```

> [!IMPORTANT]
> **Aturan Pencegahan Jurnal Ganda (*Anti-Double Posting Rule*)**:
> Input detail transaksi saldo awal (Faktur Piutang, Faktur Hutang, dan Stok Fisik) **TIDAK BOLEH** memicu auto-posting jurnal harian biasa! 
> Jurnal GL untuk saldo awal hanya dibentuk **satu kali** melalui mekanisme resmi **Jurnal Memorial Saldo Awal**.

---

## 2. Struktur Tabel Basis Data KarismaERP yang Terlibat

Berdasarkan arsitektur database KarismaERP saat ini, tabel-tabel yang digunakan adalah:

### A. Tabel General Ledger Saldo Awal
1. **`tbkeu_akun`**: Master Bagan Akun (COA).
2. **`tbkeu_saldo_awal_akun`**:
   ```sql
   CREATE TABLE `tbkeu_saldo_awal_akun` (
     `id_saldo_awal` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
     `id_akun` bigint(20) UNSIGNED NOT NULL,
     `tanggal_saldo` date NOT NULL,
     `debit` decimal(19,4) NOT NULL DEFAULT 0.0000,
     `kredit` decimal(19,4) NOT NULL DEFAULT 0.0000,
     `keterangan` varchar(255) DEFAULT NULL,
     `is_migrated` tinyint(1) NOT NULL DEFAULT 0,
     `id_jurnal` bigint(20) UNSIGNED DEFAULT NULL,
     `created_by` bigint(20) DEFAULT NULL,
     `created_at` datetime DEFAULT current_timestamp(),
     `updated_by` bigint(20) DEFAULT NULL,
     `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
     `migrated_by` bigint(20) DEFAULT NULL,
     `migrated_at` datetime DEFAULT NULL,
     PRIMARY KEY (`id_saldo_awal`),
     KEY `idx_saldo_awal_akun` (`id_akun`),
     KEY `idx_saldo_awal_tanggal` (`tanggal_saldo`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
   ```
3. **`tbkeu_jurnal` & `tbkeu_jurnal_detail`**: Menampung jurnal memorial resmi saldo awal.

### B. Tabel Subledger Piutang Usaha (AR Subledger)
Untuk memastikan kasir dan collection dapat menagih dan melunasi faktur tempo Zahir, data faktur open dimasukkan ke tabel faktur penjualan dengan penanda khusus saldo awal:
1. **`tbso_faktur_penjualan`**:
   - `no_faktur`: Nomor faktur asli dari Zahir (misal: `INV-ZAHIR-2026-00123`).
   - `tanggal_faktur`: Tanggal faktur asli saat diterbitkan di Zahir.
   - `tanggal_jatuh_tempo`: Jatuh tempo asli faktur.
   - `kd_customer`: Kode pelanggan di KarismaERP (`tb_customer`).
   - `total_tagihan`: Sisa piutang per tanggal cut-off.
   - `status`: `'POSTED'` atau `'OPEN'`.
   - `keterangan`: `'MIGRASI SALDO AWAL ZAHIR'`.
2. **`tbso_faktur_detail`**:
   - Berisi 1 baris item ringkasan (dummy/rekap saldo awal faktur terkait).
3. **Alternatif Tabel Dedicated**: `tbkeu_saldo_awal_ar` (seperti yang dirancang di blueprint jika ingin dipisah sebelum disinkronkan ke modul pembayaran).

### C. Tabel Subledger Hutang Usaha (AP Subledger)
Untuk memastikan tim keuangan dapat membayar tagihan supplier yang berasal dari Zahir:
1. **`tbkeu_saldo_awal_ap`** (atau `tb_lpb` dengan flag `is_opening_balance = 1`):
   - `id_supplier`: Kode/ID Supplier KarismaERP (`tb_suplier` / `tbpo_suplier`).
   - `nomor_faktur_zahir`: Nomor invoice asli dari vendor/Zahir.
   - `tanggal_faktur`: Tanggal faktur supplier.
   - `tanggal_jatuh_tempo`: Jatuh tempo pembayaran.
   - `sisa_hutang`: Sisa kewajiban yang belum dibayar per tanggal cut-off.

### D. Tabel Subledger Persediaan Barang (Inventory Ledger)
Untuk memastikan stok fisik dan kartu stok di KarismaERP langsung berisi kuantitas dan nilai HPP per gudang:
1. **`tberp_stock_ledger`**:
   - `id_barang`: ID master barang (`tb_master_barang`).
   - `id_gudang`: ID gudang penyimpanan (`tb_gudang`).
   - `trans_type`: `'OPENING_BALANCE'`.
   - `trans_no`: `'OB-STOCK-[TANGGAL]'`.
   - `qty_in`: Kuantitas saldo awal dari Zahir.
   - `qty_out`: 0.
   - `hpp`: Harga Pokok Satuan (*Cost per Unit*) dari Zahir.
   - `total_cost`: $Qty \times HPP$.
2. **`tberp_stock_batch`**:
   - Mencatat batch, nomor lot, dan *expired date* per item (jika ada).

---

## 3. Template Format Data Ekstraksi dari Zahir

Sebelum menginput data ke database, siapkan 4 file spreadsheet / CSV berikut dari Zahir:

### Template 1: `saldo_awal_gl.csv` (Akun Neraca)
| kode_akun | nama_akun | debit | kredit |
| :--- | :--- | :---: | :---: |
| 11010 | Kas Besar Kantor | 25,000,000.00 | 0.00 |
| 11020 | Bank BCA Operasional | 150,000,000.00 | 0.00 |
| 11030 | Bank Mandiri | 85,000,000.00 | 0.00 |
| 11050 | Piutang Usaha Dagang | 450,000,000.00 | 0.00 |
| 11070 | Persediaan Barang Dagang | 850,000,000.00 | 0.00 |
| 12010 | Aktiva Tetap - Kendaraan | 350,000,000.00 | 0.00 |
| 12011 | Akum. Penyusutan Kendaraan | 0.00 | 120,000,000.00 |
| 21010 | Hutang Usaha Supplier | 0.00 | 600,000,000.00 |
| 21030 | Hutang Pajak PPN | 0.00 | 40,000,000.00 |
| 31010 | Modal Disetor | 0.00 | 800,000,000.00 |
| 32010 | Laba Ditahan (Retained Earnings) | 0.00 | 350,000,000.00 |
| **TOTAL** | *(Wajib Seimbang)* | **1,910,000,000.00** | **1,910,000,000.00** |

### Template 2: `saldo_awal_piutang_ar.csv` (Faktur Belum Lunas)
| no_faktur_zahir | kd_customer | tanggal_faktur | jatuh_tempo | total_faktur | sisa_piutang |
| :--- | :--- | :---: | :---: | :---: | :---: |
| FP-2026/08/0012 | CUST-0042 | 2026-08-15 | 2026-09-15 | 75,000,000.00 | 25,000,000.00 |
| FP-2026/08/0088 | CUST-0105 | 2026-08-28 | 2026-09-28 | 150,000,000.00 | 150,000,000.00 |
| FP-2026/08/0115 | CUST-0320 | 2026-08-30 | 2026-09-30 | 275,000,000.00 | 275,000,000.00 |
| **TOTAL** | *(Wajib Sama dengan Akun Piutang 11050)* | | | | **450,000,000.00** |

### Template 3: `saldo_awal_hutang_ap.csv` (Tagihan Supplier Belum Lunas)
| no_invoice_supplier | id_supplier | tanggal_invoice | jatuh_tempo | total_invoice | sisa_hutang |
| :--- | :--- | :---: | :---: | :---: | :---: |
| INV-SUPP-8821 | SUPP-0012 | 2026-08-10 | 2026-09-10 | 200,000,000.00 | 200,000,000.00 |
| INV-SUPP-9014 | SUPP-0045 | 2026-08-20 | 2026-09-20 | 400,000,000.00 | 400,000,000.00 |
| **TOTAL** | *(Wajib Sama dengan Akun Hutang 21010)* | | | | **600,000,000.00** |

### Template 4: `saldo_awal_stok.csv` (Persediaan Fisik per Gudang)
| id_barang | id_gudang | batch_no | expired_date | qty_fisik | hpp_satuan | total_nilai |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| BRG-001 | 1 (Utama) | B2608A | 2028-08-01 | 1,000 | 250,000.00 | 250,000,000.00 |
| BRG-002 | 1 (Utama) | B2608B | 2028-12-01 | 2,000 | 150,000.00 | 300,000,000.00 |
| BRG-003 | 2 (Transit) | B2607X | 2027-06-01 | 1,500 | 200,000.00 | 300,000,000.00 |
| **TOTAL** | *(Wajib Sama dengan Akun Persediaan 11070)* | | | | | **850,000,000.00** |

---

## 4. Prosedur Eksekusi Database Step-by-Step

Misalkan tanggal cut-off yang disepakati adalah **31 Agustus 2026** (transaksi baru di KarismaERP dimulai tanggal **1 September 2026**).

### Langkah 1: Persiapan Periode Fiskal Saldo Awal
Pastikan di `tbkeu_periode_fiskal` telah terdaftar periode awal untuk menampung saldo:
```sql
INSERT INTO `tbkeu_periode_fiskal` 
(`kode_periode`, `nama_periode`, `tanggal_mulai`, `tanggal_selesai`, `status`, `is_active`)
VALUES 
('2026-08', 'Agustus 2026 (Cut-Off Saldo Awal)', '2026-08-01', '2026-08-31', 'OPEN', 1);

SET @ID_PERIODE_SALDO = LAST_INSERT_ID();
```

---

### Langkah 2: Memasukkan Saldo Awal Akuntansi (General Ledger)
Masukkan seluruh akun neraca ke `tbkeu_saldo_awal_akun`:
```sql
-- Pastikan tabel bersih sebelum import
DELETE FROM `tbkeu_saldo_awal_akun` WHERE `tanggal_saldo` = '2026-08-31';

-- Contoh query insert batch saldo awal akun
INSERT INTO `tbkeu_saldo_awal_akun` 
(`id_akun`, `tanggal_saldo`, `debit`, `kredit`, `keterangan`, `is_migrated`, `created_by`, `created_at`)
VALUES
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '11010'), '2026-08-31', 25000000.0000, 0.0000, 'Saldo Awal Kas Besar', 0, 1, NOW()),
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '11020'), '2026-08-31', 150000000.0000, 0.0000, 'Saldo Awal Bank BCA', 0, 1, NOW()),
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '11030'), '2026-08-31', 85000000.0000, 0.0000, 'Saldo Awal Bank Mandiri', 0, 1, NOW()),
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '11050'), '2026-08-31', 450000000.0000, 0.0000, 'Saldo Awal Piutang Usaha', 0, 1, NOW()),
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '11070'), '2026-08-31', 850000000.0000, 0.0000, 'Saldo Awal Persediaan Barang', 0, 1, NOW()),
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '12010'), '2026-08-31', 350000000.0000, 0.0000, 'Saldo Awal Aktiva Kendaraan', 0, 1, NOW()),
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '12011'), '2026-08-31', 0.0000, 120000000.0000, 'Akum. Penyusutan Kendaraan', 0, 1, NOW()),
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '21010'), '2026-08-31', 0.0000, 600000000.0000, 'Saldo Awal Hutang Supplier', 0, 1, NOW()),
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '21030'), '2026-08-31', 0.0000, 40000000.0000, 'Saldo Awal Hutang Pajak PPN', 0, 1, NOW()),
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '31010'), '2026-08-31', 0.0000, 800000000.0000, 'Saldo Awal Modal Disetor', 0, 1, NOW()),
((SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '32010'), '2026-08-31', 0.0000, 350000000.0000, 'Saldo Awal Laba Ditahan', 0, 1, NOW());
```

---

### Langkah 3: Posting Jurnal Memorial Saldo Awal ke GL
Setelah dipastikan $\sum \text{Debit} = \sum \text{Kredit}$, buat satu nomor jurnal memorial saldo awal di `tbkeu_jurnal`:
```sql
START TRANSACTION;

-- 1. Buat Header Jurnal Saldo Awal
INSERT INTO `tbkeu_jurnal` (
  `nomor_jurnal`, `id_jenis_jurnal`, `tanggal_transaksi`, `id_periode`,
  `keterangan`, `source_module`, `source_type`, `source_id`, `source_no`,
  `posting_event`, `status`, `total_debit`, `total_kredit`,
  `idempotency_key`, `created_by`, `created_at`, `posted_by`, `posted_at`
) VALUES (
  'JU-OPENING-20260831',
  (SELECT id_jenis_jurnal FROM tbkeu_jenis_jurnal WHERE kode_jenis_jurnal = 'JU' LIMIT 1),
  '2026-08-31',
  @ID_PERIODE_SALDO,
  'JURNAL MEMORIAL SALDO AWAL CUT-OFF ZAHIR PER 31 AGUSTUS 2026',
  'ACCOUNTING', 'OPENING_BALANCE', 'OB-20260831', 'OB-20260831',
  'OPENING_BALANCE_MIGRATION', 'POSTED',
  1910000000.0000, 1910000000.0000,
  'IDEMP-OPENING-BALANCE-20260831',
  1, NOW(), 1, NOW()
);

SET @ID_JURNAL_OB = LAST_INSERT_ID();

-- 2. Buat Detail Jurnal dari tbkeu_saldo_awal_akun
INSERT INTO `tbkeu_jurnal_detail` (
  `id_jurnal`, `nomor_baris`, `id_akun`, `keterangan`, `debit`, `kredit`, `created_at`
)
SELECT 
  @ID_JURNAL_OB,
  ROW_NUMBER() OVER(ORDER BY sa.id_saldo_awal ASC) AS nomor_baris,
  sa.id_akun,
  sa.keterangan,
  sa.debit,
  sa.kredit,
  NOW()
FROM `tbkeu_saldo_awal_akun` sa
WHERE sa.tanggal_saldo = '2026-08-31';

-- 3. Update status saldo awal menjadi migrated
UPDATE `tbkeu_saldo_awal_akun`
SET `is_migrated` = 1, `id_jurnal` = @ID_JURNAL_OB, `migrated_at` = NOW(), `migrated_by` = 1
WHERE `tanggal_saldo` = '2026-08-31';

COMMIT;
```

---

### Langkah 4: Memasukkan Subledger Piutang Usaha (AR Subledger)
Agar faktur-faktur tempo dari Zahir dapat ditagih di KarismaERP, masukkan data dari file `saldo_awal_piutang_ar.csv` ke tabel transaksi penjualan dengan status khusus:

```sql
START TRANSACTION;

-- Contoh Insert Faktur 1
INSERT INTO `tbso_faktur_penjualan` (
  `no_faktur`, `no_so`, `kd_customer`, `customer_name`,
  `tanggal_faktur`, `tanggal_jatuh_tempo`, `tempo`,
  `total_tagihan`, `status`, `cara_pembayaran`, `keterangan`, `created_at`
) VALUES (
  'FP-2026/08/0012', 'SO-OB-ZAHIR', 'CUST-0042', 'Toko Berkah Abadi',
  '2026-08-15', '2026-09-15', 30,
  25000000.00, 'POSTED', 'kredit', 'SALDO AWAL PIUTANG ZAHIR', '2026-08-31 23:59:59'
);

SET @ID_FAKTUR_AR = LAST_INSERT_ID();

-- Insert dummy line item agar detail faktur tidak kosong saat diprint/dicek
INSERT INTO `tbso_faktur_detail` (
  `id_faktur`, `id_barang`, `nama_barang`, `qty`, `harga_satuan`, `total_harga`
) VALUES (
  @ID_FAKTUR_AR, 0, 'SALDO AWAL PIUTANG USAHA (ZAHIR)', 1, 25000000.00, 25000000.00
);

-- Ulangi untuk faktur FP-2026/08/0088 dan FP-2026/08/0115 ...

COMMIT;
```

---

### Langkah 5: Memasukkan Subledger Hutang Usaha (AP Subledger)
Masukkan rincian hutang supplier dari Zahir ke tabel `tbkeu_saldo_awal_ap`:

```sql
INSERT INTO `tbkeu_saldo_awal_ap` (
  `id_supplier`, `nomor_faktur_zahir`, `tanggal_faktur`, `tanggal_jatuh_tempo`,
  `nilai_faktur`, `sudah_dibayar`, `sisa_hutang`, `keterangan`, `is_applied_to_gl`
) VALUES
(12, 'INV-SUPP-8821', '2026-08-10', '2026-09-10', 200000000.0000, 0.0000, 200000000.0000, 'Saldo Awal Hutang Zahir', 1),
(45, 'INV-SUPP-9014', '2026-08-20', '2026-09-20', 400000000.0000, 0.0000, 400000000.0000, 'Saldo Awal Hutang Zahir', 1);
```

---

### Langkah 6: Memasukkan Subledger Stok Fisik & HPP Persediaan
Masukkan kuantitas dan harga perolehan (HPP) ke tabel buku stok gudang:

```sql
START TRANSACTION;

-- Barang 1
INSERT INTO `tberp_stock_ledger` (
  `id_barang`, `id_gudang`, `trans_date`, `trans_type`, `trans_no`,
  `qty_in`, `qty_out`, `hpp`, `total_cost`, `keterangan`, `created_at`
) VALUES (
  101, 1, '2026-08-31', 'OPENING_BALANCE', 'OB-STOCK-20260831',
  1000.0000, 0.0000, 250000.0000, 250000000.0000, 'Saldo Awal Persediaan dari Zahir', NOW()
);

-- Catat Batch dan Expired Date
INSERT INTO `tberp_stock_batch` (
  `id_barang`, `id_gudang`, `batch_no`, `expired_date`, `qty`, `created_at`
) VALUES (
  101, 1, 'B2608A', '2028-08-01', 1000.0000, NOW()
);

-- Ulangi untuk Barang 2 dan Barang 3 ...

COMMIT;
```

---

## 5. Script SQL Validasi Matematis & Rekonsiliasi Saldo Awal

Sebelum operasional dinyatakan aktif, jalankan script audit berikut di MariaDB/phpMyAdmin. Seluruh nilai selisih **HARUS BERNILAI 0 (NOL)**:

```sql
-- ====================================================================
-- AUDIT CHECK 1: Keseimbangan Debit vs Kredit Jurnal Saldo Awal GL
-- ====================================================================
SELECT 
  'JURNAL SALDO AWAL GL' AS komponen,
  SUM(debit) AS total_debit,
  SUM(kredit) AS total_kredit,
  ABS(SUM(debit) - SUM(kredit)) AS selisih,
  CASE 
    WHEN SUM(debit) = SUM(kredit) THEN 'OK / BALANCE' 
    ELSE 'GAGAL - TIDAK SEIMBANG' 
  END AS status_audit
FROM `tbkeu_jurnal_detail`
WHERE `id_jurnal` = @ID_JURNAL_OB;

-- ====================================================================
-- AUDIT CHECK 2: Rekonsiliasi GL Piutang vs Subledger Faktur AR
-- ====================================================================
SELECT 
  'REKONSILIASI PIUTANG' AS komponen,
  (SELECT debit FROM tbkeu_jurnal_detail WHERE id_jurnal = @ID_JURNAL_OB AND id_akun = (SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '11050')) AS saldo_gl_piutang,
  (SELECT SUM(total_tagihan) FROM tbso_faktur_penjualan WHERE keterangan LIKE '%SALDO AWAL%') AS subledger_faktur_ar,
  (
    (SELECT debit FROM tbkeu_jurnal_detail WHERE id_jurnal = @ID_JURNAL_OB AND id_akun = (SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '11050'))
    -
    (SELECT SUM(total_tagihan) FROM tbso_faktur_penjualan WHERE keterangan LIKE '%SALDO AWAL%')
  ) AS selisih_piutang;

-- ====================================================================
-- AUDIT CHECK 3: Rekonsiliasi GL Hutang vs Subledger Faktur AP
-- ====================================================================
SELECT 
  'REKONSILIASI HUTANG' AS komponen,
  (SELECT kredit FROM tbkeu_jurnal_detail WHERE id_jurnal = @ID_JURNAL_OB AND id_akun = (SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '21010')) AS saldo_gl_hutang,
  (SELECT SUM(sisa_hutang) FROM tbkeu_saldo_awal_ap) AS subledger_hutang_ap,
  (
    (SELECT kredit FROM tbkeu_jurnal_detail WHERE id_jurnal = @ID_JURNAL_OB AND id_akun = (SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '21010'))
    -
    (SELECT SUM(sisa_hutang) FROM tbkeu_saldo_awal_ap)
  ) AS selisih_hutang;

-- ====================================================================
-- AUDIT CHECK 4: Rekonsiliasi GL Persediaan vs Subledger Stok Gudang
-- ====================================================================
SELECT 
  'REKONSILIASI PERSEDIAAN' AS komponen,
  (SELECT debit FROM tbkeu_jurnal_detail WHERE id_jurnal = @ID_JURNAL_OB AND id_akun = (SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '11070')) AS saldo_gl_persediaan,
  (SELECT SUM(total_cost) FROM tberp_stock_ledger WHERE trans_type = 'OPENING_BALANCE') AS subledger_nilai_stok,
  (
    (SELECT debit FROM tbkeu_jurnal_detail WHERE id_jurnal = @ID_JURNAL_OB AND id_akun = (SELECT id_akun FROM tbkeu_akun WHERE kode_akun = '11070'))
    -
    (SELECT SUM(total_cost) FROM tberp_stock_ledger WHERE trans_type = 'OPENING_BALANCE')
  ) AS selisih_persediaan;
```

---

## 6. Prosedur Penguncian & Tutup Buku Periode Saldo Awal

Setelah hasil validasi menunjukkan seluruh selisih bernilai 0:
1. **Kunci Periode Saldo Awal (Agustus 2026)**:
   ```sql
   UPDATE `tbkeu_periode_fiskal` 
   SET `status` = 'CLOSED', `is_closing_locked` = 1, `closed_at` = NOW(), `closed_by` = 1
   WHERE `kode_periode` = '2026-08';
   ```
2. **Buka Periode Operasional Baru (September 2026)**:
   ```sql
   INSERT INTO `tbkeu_periode_fiskal` 
   (`kode_periode`, `nama_periode`, `tanggal_mulai`, `tanggal_selesai`, `status`, `is_active`)
   VALUES 
   ('2026-09', 'September 2026 (Go-Live KarismaERP)', '2026-09-01', '2026-09-30', 'OPEN', 1);
   ```

Dengan selesainya langkah di atas:
- Saldo awal telah terkunci aman di periode Agustus 2026 dan **tidak dapat diubah atau dihapus**.
- Buku besar per 1 September 2026 otomatis menampilkan saldo awal dari hasil *carry forward*.
- Bagian kasir/penagihan dapat langsung memproses pelunasan faktur saldo awal piutang.
- Bagian purchasing/keuangan dapat langsung mencicil atau melunasi hutang supplier saldo awal.
- Gudang langsung memiliki stok fisik yang siap dijual dengan perhitungan HPP bergerak (*moving average*) yang presisi.
