-- ==============================================================================
-- Migrasi Database: Modul Tutup Buku & Cut-Off KarismaERP
-- Tanggal: 2026-10-05
-- Acuan Analisis: docs/accounting/ANALISIS_GAP_DATABASE_MODUL_TUTUP_BUKU_20261005.md
-- ==============================================================================

-- 1. Penambahan Kolom pada tbkeu_periode_fiskal jika belum ada
ALTER TABLE `tbkeu_periode_fiskal`
  ADD COLUMN `tipe_periode` ENUM('MONTHLY','YEAR_END') NOT NULL DEFAULT 'MONTHLY' AFTER `nama_periode`;

-- 2. Tabel Header Snapshot Tutup Buku (Closing Header dengan Multi-Versioning)
CREATE TABLE IF NOT EXISTS `tbkeu_closing_period` (
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

-- 3. Snapshot Saldo Akhir GL per Akun (Frozen General Ledger)
CREATE TABLE IF NOT EXISTS `tbkeu_closing_balance_account` (
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

-- 4. Snapshot Persediaan Akhir per Gudang dan Item (Frozen Inventory Subledger)
CREATE TABLE IF NOT EXISTS `tbkeu_closing_balance_inventory` (
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

-- 5. Snapshot Piutang Usaha per Faktur Terbuka (Frozen AR Subledger)
CREATE TABLE IF NOT EXISTS `tbkeu_closing_balance_ar` (
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

-- 6. Snapshot Hutang Usaha per Tagihan/LPB Terbuka (Frozen AP Subledger)
CREATE TABLE IF NOT EXISTS `tbkeu_closing_balance_ap` (
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

-- 7. Header Sesi Validasi Pre-Closing (Validation Run)
CREATE TABLE IF NOT EXISTS `tbkeu_closing_validation_run` (
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

-- 8. Detail Item Pemeriksaan Pre-Closing (Validation Details)
CREATE TABLE IF NOT EXISTS `tbkeu_closing_validation_log` (
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

-- 9. Tabel Workflow Permohonan & Otorisasi Buka Periode (Reopen Request)
CREATE TABLE IF NOT EXISTS `tbkeu_reopen_request` (
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

-- 10. Konfigurasi default mapping untuk Year-End Closing
INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `scope_type`, `scope_key`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT 'ACCOUNTING', 'YEAR_END_CLOSING', 'GLOBAL', '*', 'YEAR_END_CLOSING', 'RETAINED_EARNINGS', 'KREDIT', id_akun, 100, 1, 'Akun Laba Ditahan untuk Tutup Buku Tahunan'
FROM `tbkeu_akun` 
WHERE `kode_akun` = '32010' 
  AND NOT EXISTS (
    SELECT 1 FROM `tbkeu_mapping_akun` 
    WHERE `posting_event` = 'YEAR_END_CLOSING' AND `account_role` = 'RETAINED_EARNINGS'
  )
LIMIT 1;

INSERT INTO `tbkeu_mapping_akun` (`source_module`, `source_type`, `scope_type`, `scope_key`, `posting_event`, `account_role`, `entry_side`, `id_akun`, `priority`, `is_active`, `keterangan`)
SELECT 'ACCOUNTING', 'YEAR_END_CLOSING', 'GLOBAL', '*', 'YEAR_END_CLOSING', 'CURRENT_YEAR_EARNINGS', 'DEBIT', id_akun, 100, 1, 'Akun Ikhtisar Laba Rugi / Tahun Berjalan'
FROM `tbkeu_akun` 
WHERE `kode_akun` = '32020' 
  AND NOT EXISTS (
    SELECT 1 FROM `tbkeu_mapping_akun` 
    WHERE `posting_event` = 'YEAR_END_CLOSING' AND `account_role` = 'CURRENT_YEAR_EARNINGS'
  )
LIMIT 1;
