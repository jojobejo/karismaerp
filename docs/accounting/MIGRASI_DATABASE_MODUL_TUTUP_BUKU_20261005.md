# DOKUMENTASI MIGRASI DATABASE: MODUL TUTUP BUKU & CUT-OFF

Tanggal: 5 Oktober 2026  
Status Eksekusi: **Sukses Dijalankan pada Basis Data Lokal (`kiucoid_karismaerp_local`)**  
File Skrip SQL: `db/migrations/20261005_modul_tutup_buku.sql`

---

## 1. Ringkasan Perubahan Basis Data

Migrasi ini mengimplementasikan struktur tabel baru dan penambahan kolom pada tabel yang sudah ada untuk menopang mesin tutup buku periodik, multi-versi snapshot, validasi pra-closing, dan audit trail yang tidak dapat diubah (*immutable*):

| No | Nama Objek | Tipe | Tujuan & Fungsi |
|:--:|:---|:---:|:---|
| 1 | `tbkeu_periode_fiskal` | ALTER TABLE | Penambahan kolom `tipe_periode` ENUM('MONTHLY','YEAR_END') untuk menandai jenis periode fiskal. |
| 2 | `tbkeu_closing_period` | CREATE TABLE | Header transaksi tutup buku dengan nomor versi berjenjang (`closing_version`), segel digital (`checksum` SHA-256), total agregat moneter, dan status alur kerja. |
| 3 | `tbkeu_closing_balance_account` | CREATE TABLE | Snapshot beku (*frozen*) saldo buku besar (GL) per akun pada tanggal cut-off. |
| 4 | `tbkeu_closing_balance_inventory` | CREATE TABLE | Snapshot beku kuantitas dan nilai HPP persediaan per gudang, batch/lot, dan tanggal kedaluwarsa. |
| 5 | `tbkeu_closing_balance_ar` | CREATE TABLE | Snapshot beku saldo piutang usaha per nomor faktur pelanggan yang masih memiliki sisa tagihan. |
| 6 | `tbkeu_closing_balance_ap` | CREATE TABLE | Snapshot beku saldo hutang usaha per nomor dokumen/LPB pemasok yang masih memiliki sisa tagihan. |
| 7 | `tbkeu_closing_validation_run` | CREATE TABLE | Header sesi eksekusi validasi pra-closing beserta metrik anomali blocking/warning. |
| 8 | `tbkeu_closing_validation_log` | CREATE TABLE | Log rinci per item aturan validasi pre-closing (nilai harapan, nilai aktual, dan selisih nominal). |
| 9 | `tbkeu_reopen_request` | CREATE TABLE | Tabel pencatatan permohonan pembukaan kembali periode tertutup dan riwayat otorisasi manajerial. |
| 10 | `tbkeu_mapping_akun` | INSERT DATA | Pendaftaran default role akun ekuitas `RETAINED_EARNINGS` (32010) dan `CURRENT_YEAR_EARNINGS` (32020) untuk Tutup Buku Tahunan. |

---

## 2. Rincian Skema DDL Lengkap

```sql
-- 1. Penambahan Kolom pada tbkeu_periode_fiskal
ALTER TABLE `tbkeu_periode_fiskal`
  ADD COLUMN `tipe_periode` ENUM('MONTHLY','YEAR_END') NOT NULL DEFAULT 'MONTHLY' AFTER `nama_periode`;

-- 2. Header Tutup Buku (Multi-Versioning per Periode)
CREATE TABLE `tbkeu_closing_period` (
  `id_closing` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_periode` BIGINT UNSIGNED NOT NULL,
  `kode_periode` VARCHAR(20) NOT NULL,
  `closing_version` INT UNSIGNED NOT NULL DEFAULT 1,
  `tipe_closing` ENUM('BULANAN','TAHUNAN') NOT NULL DEFAULT 'BULANAN',
  `tanggal_closing` DATE NOT NULL,
  `cutoff_at` DATETIME NOT NULL,
  `waktu_eksekusi` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `eksekutor_user_id` BIGINT NOT NULL,
  `approved_by` BIGINT DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `status` ENUM('DRAFT','VALIDATING','READY_TO_APPROVE','APPROVED','EXECUTING','COMPLETED','REOPENED','SUPERSEDED','FAILED') NOT NULL DEFAULT 'COMPLETED',
  `total_debit_gl` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `total_kredit_gl` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `total_laba_bersih_periode` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `total_nilai_persediaan` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `total_piutang_berjalan` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `total_hutang_berjalan` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `id_jurnal_penutup` BIGINT UNSIGNED DEFAULT NULL,
  `checksum` VARCHAR(64) DEFAULT NULL,
  `catatan` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_closing`),
  UNIQUE KEY `uk_closing_periode_version` (`id_periode`, `closing_version`),
  KEY `idx_closing_periode` (`id_periode`),
  KEY `idx_closing_tanggal` (`tanggal_closing`),
  KEY `idx_closing_status` (`status`),
  CONSTRAINT `fk_closing_periode_hdr` FOREIGN KEY (`id_periode`) 
    REFERENCES `tbkeu_periode_fiskal` (`id_periode`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Snapshot Saldo GL per Akun
CREATE TABLE `tbkeu_closing_balance_account` (
  `id_closing_acc` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_closing` BIGINT UNSIGNED NOT NULL,
  `id_akun` BIGINT UNSIGNED NOT NULL,
  `kode_akun` VARCHAR(30) NOT NULL,
  `nama_akun` VARCHAR(150) NOT NULL,
  `jenis_laporan` ENUM('NERACA','LABA_RUGI') NOT NULL,
  `saldo_normal` ENUM('DEBIT','KREDIT') NOT NULL,
  `saldo_awal` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `mutasi_debit` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `mutasi_kredit` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `saldo_akhir` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_closing_acc`),
  UNIQUE KEY `uk_closing_akun` (`id_closing`, `id_akun`),
  KEY `idx_closing_acc_akun` (`id_akun`),
  KEY `idx_closing_acc_header` (`id_closing`),
  CONSTRAINT `fk_closing_acc_hdr` FOREIGN KEY (`id_closing`) 
    REFERENCES `tbkeu_closing_period` (`id_closing`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Snapshot Persediaan Akhir
CREATE TABLE `tbkeu_closing_balance_inventory` (
  `id_closing_inv` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_closing` BIGINT UNSIGNED NOT NULL,
  `id_barang` BIGINT DEFAULT NULL,
  `kd_barang` VARCHAR(50) NOT NULL,
  `nama_barang` VARCHAR(255) DEFAULT NULL,
  `id_gudang` BIGINT DEFAULT NULL,
  `gudang_id` VARCHAR(30) NOT NULL,
  `batch_no` VARCHAR(100) DEFAULT '-',
  `expired_date` DATE DEFAULT NULL,
  `qty_akhir` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `hpp_rata_rata` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `total_nilai_stok` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_closing_inv`),
  KEY `idx_closing_inv_hdr` (`id_closing`),
  KEY `idx_closing_inv_barang` (`kd_barang`),
  KEY `idx_closing_inv_gudang` (`gudang_id`),
  CONSTRAINT `fk_closing_inv_hdr` FOREIGN KEY (`id_closing`) 
    REFERENCES `tbkeu_closing_period` (`id_closing`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Snapshot Piutang Terbuka (AR)
CREATE TABLE `tbkeu_closing_balance_ar` (
  `id_closing_ar` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_closing` BIGINT UNSIGNED NOT NULL,
  `id_customer` BIGINT DEFAULT NULL,
  `kd_customer` VARCHAR(50) NOT NULL,
  `customer_name` VARCHAR(150) DEFAULT NULL,
  `no_faktur` VARCHAR(50) NOT NULL,
  `tanggal_faktur` DATE NOT NULL,
  `tanggal_jatuh_tempo` DATE DEFAULT NULL,
  `total_tagihan` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `total_bayar` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `sisa_piutang` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_closing_ar`),
  KEY `idx_closing_ar_hdr` (`id_closing`),
  KEY `idx_closing_ar_cust` (`kd_customer`),
  KEY `idx_closing_ar_faktur` (`no_faktur`),
  CONSTRAINT `fk_closing_ar_hdr` FOREIGN KEY (`id_closing`) 
    REFERENCES `tbkeu_closing_period` (`id_closing`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Snapshot Hutang Terbuka (AP)
CREATE TABLE `tbkeu_closing_balance_ap` (
  `id_closing_ap` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_closing` BIGINT UNSIGNED NOT NULL,
  `id_supplier` BIGINT DEFAULT NULL,
  `kd_suplier` VARCHAR(50) NOT NULL,
  `nama_suplier` VARCHAR(255) DEFAULT NULL,
  `nomor_dokumen` VARCHAR(100) NOT NULL,
  `tanggal_faktur` DATE NOT NULL,
  `tanggal_jatuh_tempo` DATE DEFAULT NULL,
  `total_tagihan` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `total_bayar` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `sisa_hutang` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_closing_ap`),
  KEY `idx_closing_ap_hdr` (`id_closing`),
  KEY `idx_closing_ap_supp` (`kd_suplier`),
  KEY `idx_closing_ap_doc` (`nomor_dokumen`),
  CONSTRAINT `fk_closing_ap_hdr` FOREIGN KEY (`id_closing`) 
    REFERENCES `tbkeu_closing_period` (`id_closing`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Sesi Validasi Pre-Closing (Validation Run)
CREATE TABLE `tbkeu_closing_validation_run` (
  `id_validation_run` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_periode` BIGINT UNSIGNED NOT NULL,
  `tipe_closing` ENUM('BULANAN','TAHUNAN') NOT NULL DEFAULT 'BULANAN',
  `run_by` BIGINT NOT NULL,
  `run_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_overall` ENUM('PASSED','FAILED') NOT NULL,
  `blocking_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `warning_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `info_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_validation_run`),
  KEY `idx_val_run_periode` (`id_periode`),
  CONSTRAINT `fk_val_run_periode` FOREIGN KEY (`id_periode`) 
    REFERENCES `tbkeu_periode_fiskal` (`id_periode`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Log Detail Item Validasi Pra-Tutup Buku
CREATE TABLE `tbkeu_closing_validation_log` (
  `id_val_log` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_validation_run` BIGINT UNSIGNED NOT NULL,
  `id_periode` BIGINT UNSIGNED NOT NULL,
  `rule_code` VARCHAR(50) NOT NULL,
  `rule_title` VARCHAR(150) NOT NULL,
  `tingkat_keparahan` ENUM('BLOCKING','WARNING','INFO') NOT NULL DEFAULT 'BLOCKING',
  `status` ENUM('PASSED','FAILED') NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `expected_amount` DECIMAL(19,4) DEFAULT NULL,
  `actual_amount` DECIMAL(19,4) DEFAULT NULL,
  `difference_amount` DECIMAL(19,4) DEFAULT NULL,
  `jumlah_anomali` INT UNSIGNED NOT NULL DEFAULT 0,
  `data_referensi` LONGTEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_val_log`),
  KEY `idx_val_log_run` (`id_validation_run`),
  KEY `idx_val_log_periode` (`id_periode`),
  KEY `idx_val_log_rule` (`rule_code`),
  CONSTRAINT `fk_val_log_run` FOREIGN KEY (`id_validation_run`) 
    REFERENCES `tbkeu_closing_validation_run` (`id_validation_run`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. Workflow Permohonan & Otorisasi Buka Periode (Reopen)
CREATE TABLE `tbkeu_reopen_request` (
  `id_reopen` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_periode` BIGINT UNSIGNED NOT NULL,
  `id_closing` BIGINT UNSIGNED NOT NULL,
  `alasan_pembukaan` TEXT NOT NULL,
  `diajukan_oleh` BIGINT NOT NULL,
  `diajukan_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `disetujui_manager_by` BIGINT DEFAULT NULL,
  `disetujui_manager_at` DATETIME DEFAULT NULL,
  `disetujui_direktur_by` BIGINT DEFAULT NULL,
  `disetujui_direktur_at` DATETIME DEFAULT NULL,
  `status_approval` ENUM('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `batas_waktu_reopen` DATETIME DEFAULT NULL,
  `catatan_koreksi` TEXT DEFAULT NULL,
  `reopened_closing_version` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reopen`),
  KEY `idx_reopen_periode` (`id_periode`),
  KEY `idx_reopen_closing` (`id_closing`),
  KEY `idx_reopen_status` (`status_approval`),
  CONSTRAINT `fk_reopen_periode_fiskal` FOREIGN KEY (`id_periode`) 
    REFERENCES `tbkeu_periode_fiskal` (`id_periode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_reopen_closing_hdr` FOREIGN KEY (`id_closing`) 
    REFERENCES `tbkeu_closing_period` (`id_closing`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 3. Catatan Penting Keamanan & Integritas Basis Data

1. **Pencegahan Penghapusan Berseri (No `ON DELETE CASCADE` pada Snapshot)**:
   - Sesuai dengan analisis gap dokumen `ANALISIS_GAP_DATABASE_MODUL_TUTUP_BUKU_20261005.md`, tabel snapshot saldo (`tbkeu_closing_balance_*`) sengaja **TIDAK** menggunakan `ON DELETE CASCADE`. Bukti snapshot audit tidak boleh hilang secara tidak sengaja.
2. **Kesesuaian Identitas Master**:
   - Kolom barang dan gudang menyimpan pasangan kode dan ID (`kd_barang`, `id_barang`, `gudang_id`, `id_gudang`) agar kompatibel penuh dengan master KarismaERP saat ini tanpa bentrok tipe data numerik vs string.
3. **Multi-Versioning**:
   - Primary constraint header closing menggunakan `UNIQUE KEY (id_periode, closing_version)`, sehingga periode yang dibuka kembali (reopen) dapat ditutup ulang menghasilkan versi closing baru (v2, v3, dst.) tanpa menghapus riwayat versi sebelumnya.
