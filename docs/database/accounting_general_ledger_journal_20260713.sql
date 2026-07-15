-- KARISMA ERP - Accounting General Ledger Journal
-- Tanggal: 2026-07-13
-- Scope: tabel jurnal finansial agar panel Form Jurnal pada route `jurnal` dapat menyajikan data.
-- Jalankan setelah `docs/database/accounting_jurnal_accounts_20260713.sql`.
-- Aman terhadap tabel Purchase Order terlarang:
-- tbpo_transaksi, tbpo_transaksi_tmp, tbpo_transaksi_trashbin, tbpo_akun_tr
-- tidak dibaca, tidak ditulis, tidak diubah, dan tidak menjadi dependency.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- MIGRATION UP
-- =========================================================

CREATE TABLE IF NOT EXISTS `tbkeu_periode_fiskal` (
  `id_periode` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_periode` VARCHAR(20) NOT NULL,
  `nama_periode` VARCHAR(100) NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `status` ENUM('OPEN','CLOSED') NOT NULL DEFAULT 'OPEN',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_periode`),
  UNIQUE KEY `uk_tbkeu_periode_kode` (`kode_periode`),
  KEY `idx_tbkeu_periode_tanggal` (`tanggal_mulai`, `tanggal_selesai`),
  KEY `idx_tbkeu_periode_status` (`status`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_periode_fiskal_log` (
  `id_log` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_periode` BIGINT UNSIGNED NOT NULL,
  `action` ENUM('OPEN','CLOSE','REOPEN') NOT NULL,
  `reason` VARCHAR(500) NOT NULL,
  `approval_by` BIGINT DEFAULT NULL,
  `approval_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_tbkeu_periode_log_periode` (`id_periode`),
  KEY `idx_tbkeu_periode_log_action` (`action`, `approval_at`),
  CONSTRAINT `fk_tbkeu_periode_log_periode`
    FOREIGN KEY (`id_periode`) REFERENCES `tbkeu_periode_fiskal` (`id_periode`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_jenis_jurnal` (
  `id_jenis_jurnal` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_jenis_jurnal` VARCHAR(20) NOT NULL,
  `nama_jenis_jurnal` VARCHAR(100) NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `is_manual` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_jenis_jurnal`),
  UNIQUE KEY `uk_tbkeu_jenis_jurnal_kode` (`kode_jenis_jurnal`),
  KEY `idx_tbkeu_jenis_jurnal_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_jurnal` (
  `id_jurnal` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_jurnal` VARCHAR(50) NOT NULL,
  `id_jenis_jurnal` SMALLINT UNSIGNED DEFAULT NULL,
  `tanggal_transaksi` DATE NOT NULL,
  `id_periode` BIGINT UNSIGNED DEFAULT NULL,
  `keterangan` VARCHAR(500) DEFAULT NULL,
  `source_module` VARCHAR(50) DEFAULT NULL,
  `source_type` VARCHAR(50) DEFAULT NULL,
  `source_id` VARCHAR(100) DEFAULT NULL,
  `source_no` VARCHAR(100) DEFAULT NULL,
  `posting_event` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('DRAFT','POSTED','REVERSED','VOID') NOT NULL DEFAULT 'DRAFT',
  `total_debit` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `total_kredit` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `reversal_of_journal_id` BIGINT UNSIGNED DEFAULT NULL,
  `idempotency_key` VARCHAR(255) DEFAULT NULL,
  `created_by` BIGINT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_by` BIGINT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `posted_by` BIGINT DEFAULT NULL,
  `posted_at` DATETIME DEFAULT NULL,
  `reversed_by` BIGINT DEFAULT NULL,
  `reversed_at` DATETIME DEFAULT NULL,
  `voided_by` BIGINT DEFAULT NULL,
  `voided_at` DATETIME DEFAULT NULL,
  `void_reason` VARCHAR(500) DEFAULT NULL,
  `lock_version` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_jurnal`),
  UNIQUE KEY `uk_tbkeu_jurnal_nomor` (`nomor_jurnal`),
  UNIQUE KEY `uk_tbkeu_jurnal_idempotency` (`idempotency_key`),
  UNIQUE KEY `uk_tbkeu_jurnal_source_event` (`source_module`, `source_type`, `source_id`, `posting_event`),
  KEY `idx_tbkeu_jurnal_tanggal` (`tanggal_transaksi`),
  KEY `idx_tbkeu_jurnal_periode` (`id_periode`),
  KEY `idx_tbkeu_jurnal_jenis` (`id_jenis_jurnal`),
  KEY `idx_tbkeu_jurnal_status` (`status`),
  KEY `idx_tbkeu_jurnal_source` (`source_module`, `source_type`, `source_id`, `source_no`),
  KEY `idx_tbkeu_jurnal_reversal` (`reversal_of_journal_id`),
  CONSTRAINT `fk_tbkeu_jurnal_jenis`
    FOREIGN KEY (`id_jenis_jurnal`) REFERENCES `tbkeu_jenis_jurnal` (`id_jenis_jurnal`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_tbkeu_jurnal_periode`
    FOREIGN KEY (`id_periode`) REFERENCES `tbkeu_periode_fiskal` (`id_periode`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_tbkeu_jurnal_reversal`
    FOREIGN KEY (`reversal_of_journal_id`) REFERENCES `tbkeu_jurnal` (`id_jurnal`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_jurnal_detail` (
  `id_jurnal_detail` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_jurnal` BIGINT UNSIGNED NOT NULL,
  `nomor_baris` SMALLINT UNSIGNED NOT NULL,
  `id_akun` BIGINT UNSIGNED NOT NULL,
  `keterangan` VARCHAR(500) DEFAULT NULL,
  `debit` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `kredit` DECIMAL(19,4) NOT NULL DEFAULT 0.0000,
  `id_customer` BIGINT DEFAULT NULL,
  `id_supplier` BIGINT DEFAULT NULL,
  `id_barang` BIGINT DEFAULT NULL,
  `id_gudang` BIGINT DEFAULT NULL,
  `id_departemen` BIGINT DEFAULT NULL,
  `tanggal_jatuh_tempo` DATE DEFAULT NULL,
  `nomor_dokumen` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_jurnal_detail`),
  UNIQUE KEY `uk_tbkeu_jurnal_detail_baris` (`id_jurnal`, `nomor_baris`),
  KEY `idx_tbkeu_jurnal_detail_akun` (`id_akun`),
  KEY `idx_tbkeu_jurnal_detail_dokumen` (`nomor_dokumen`),
  CONSTRAINT `fk_tbkeu_jurnal_detail_jurnal`
    FOREIGN KEY (`id_jurnal`) REFERENCES `tbkeu_jurnal` (`id_jurnal`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_tbkeu_jurnal_detail_akun`
    FOREIGN KEY (`id_akun`) REFERENCES `tbkeu_akun` (`id_akun`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- SEED MASTER JENIS JURNAL
-- =========================================================

INSERT INTO `tbkeu_jenis_jurnal`
  (`kode_jenis_jurnal`, `nama_jenis_jurnal`, `keterangan`, `is_manual`, `is_active`)
VALUES
  ('JU', 'Jurnal Umum', 'Jurnal manual umum', 1, 1),
  ('AUTO', 'Jurnal Otomatis', 'Jurnal yang dibuat oleh proses ERP', 0, 1),
  ('REV', 'Jurnal Reversal', 'Jurnal pembalik transaksi', 0, 1)
ON DUPLICATE KEY UPDATE
  `nama_jenis_jurnal` = VALUES(`nama_jenis_jurnal`),
  `keterangan` = VALUES(`keterangan`),
  `is_manual` = VALUES(`is_manual`),
  `is_active` = VALUES(`is_active`);

-- =========================================================
-- SEED PERIODE FISKAL 2026
-- Ubah/tambah periode sesuai kalender finance sebelum go-live.
-- =========================================================

INSERT INTO `tbkeu_periode_fiskal`
  (`kode_periode`, `nama_periode`, `tanggal_mulai`, `tanggal_selesai`, `status`, `is_active`)
VALUES
  ('2026-01', 'Januari 2026', '2026-01-01', '2026-01-31', 'OPEN', 1),
  ('2026-02', 'Februari 2026', '2026-02-01', '2026-02-28', 'OPEN', 1),
  ('2026-03', 'Maret 2026', '2026-03-01', '2026-03-31', 'OPEN', 1),
  ('2026-04', 'April 2026', '2026-04-01', '2026-04-30', 'OPEN', 1),
  ('2026-05', 'Mei 2026', '2026-05-01', '2026-05-31', 'OPEN', 1),
  ('2026-06', 'Juni 2026', '2026-06-01', '2026-06-30', 'OPEN', 1),
  ('2026-07', 'Juli 2026', '2026-07-01', '2026-07-31', 'OPEN', 1),
  ('2026-08', 'Agustus 2026', '2026-08-01', '2026-08-31', 'OPEN', 1),
  ('2026-09', 'September 2026', '2026-09-01', '2026-09-30', 'OPEN', 1),
  ('2026-10', 'Oktober 2026', '2026-10-01', '2026-10-31', 'OPEN', 1),
  ('2026-11', 'November 2026', '2026-11-01', '2026-11-30', 'OPEN', 1),
  ('2026-12', 'Desember 2026', '2026-12-01', '2026-12-31', 'OPEN', 1)
ON DUPLICATE KEY UPDATE
  `nama_periode` = VALUES(`nama_periode`),
  `tanggal_mulai` = VALUES(`tanggal_mulai`),
  `tanggal_selesai` = VALUES(`tanggal_selesai`),
  `status` = VALUES(`status`),
  `is_active` = VALUES(`is_active`);

-- =========================================================
-- SAMPLE JURNAL OPSIONAL UNTUK TEST UI
-- Default tidak aktif. Hapus tanda komentar jika butuh contoh data.
-- Syarat: akun 1100 Kas dan 4100 Penjualan tersedia dari migration akun.
-- =========================================================

-- INSERT INTO `tbkeu_jurnal`
--   (`nomor_jurnal`, `id_jenis_jurnal`, `tanggal_transaksi`, `id_periode`, `keterangan`, `source_module`, `source_type`, `source_id`, `source_no`, `posting_event`, `status`, `total_debit`, `total_kredit`, `idempotency_key`)
-- SELECT
--   'JU-202607-0001',
--   jj.id_jenis_jurnal,
--   '2026-07-13',
--   pf.id_periode,
--   'Sample jurnal test UI',
--   'ACCOUNTING',
--   'MANUAL',
--   'SAMPLE-20260713-0001',
--   'SAMPLE-0001',
--   'MANUAL_JOURNAL',
--   'POSTED',
--   100000.0000,
--   100000.0000,
--   'sample-accounting-journal-20260713-0001'
-- FROM tbkeu_jenis_jurnal jj
-- JOIN tbkeu_periode_fiskal pf ON pf.kode_periode = '2026-07'
-- WHERE jj.kode_jenis_jurnal = 'JU'
-- ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;
--
-- INSERT INTO `tbkeu_jurnal_detail`
--   (`id_jurnal`, `nomor_baris`, `id_akun`, `keterangan`, `debit`, `kredit`, `nomor_dokumen`)
-- SELECT j.id_jurnal, 1, a.id_akun, 'Kas diterima', 100000.0000, 0.0000, 'SAMPLE-0001'
-- FROM tbkeu_jurnal j
-- JOIN tbkeu_akun a ON a.kode_akun = '1100'
-- WHERE j.nomor_jurnal = 'JU-202607-0001'
-- ON DUPLICATE KEY UPDATE `debit` = VALUES(`debit`), `kredit` = VALUES(`kredit`);
--
-- INSERT INTO `tbkeu_jurnal_detail`
--   (`id_jurnal`, `nomor_baris`, `id_akun`, `keterangan`, `debit`, `kredit`, `nomor_dokumen`)
-- SELECT j.id_jurnal, 2, a.id_akun, 'Pendapatan sample', 0.0000, 100000.0000, 'SAMPLE-0001'
-- FROM tbkeu_jurnal j
-- JOIN tbkeu_akun a ON a.kode_akun = '4100'
-- WHERE j.nomor_jurnal = 'JU-202607-0001'
-- ON DUPLICATE KEY UPDATE `debit` = VALUES(`debit`), `kredit` = VALUES(`kredit`);

-- =========================================================
-- SQL AUDIT READ-ONLY
-- =========================================================

SELECT nomor_jurnal, total_debit, total_kredit
FROM tbkeu_jurnal
WHERE ROUND(total_debit, 4) <> ROUND(total_kredit, 4);

SELECT d.id_jurnal_detail, j.nomor_jurnal, a.kode_akun, a.nama_akun
FROM tbkeu_jurnal_detail d
JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
LEFT JOIN tbkeu_akun a ON a.id_akun = d.id_akun
WHERE a.id_akun IS NULL;

SELECT d.id_jurnal_detail, j.nomor_jurnal, a.kode_akun, a.nama_akun, a.tipe_akun, a.is_active
FROM tbkeu_jurnal_detail d
JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
JOIN tbkeu_akun a ON a.id_akun = d.id_akun
WHERE a.tipe_akun <> 'POSTING' OR a.is_active <> 1;

SELECT d.id_jurnal_detail, j.nomor_jurnal, d.debit, d.kredit
FROM tbkeu_jurnal_detail d
JOIN tbkeu_jurnal j ON j.id_jurnal = d.id_jurnal
WHERE d.debit < 0
   OR d.kredit < 0
   OR (d.debit > 0 AND d.kredit > 0)
   OR (d.debit = 0 AND d.kredit = 0);

-- =========================================================
-- SQL VERIFICATION
-- =========================================================

SELECT 'tbkeu_periode_fiskal' AS table_name, COUNT(*) AS total_rows FROM tbkeu_periode_fiskal
UNION ALL
SELECT 'tbkeu_jenis_jurnal' AS table_name, COUNT(*) AS total_rows FROM tbkeu_jenis_jurnal
UNION ALL
SELECT 'tbkeu_jurnal' AS table_name, COUNT(*) AS total_rows FROM tbkeu_jurnal
UNION ALL
SELECT 'tbkeu_jurnal_detail' AS table_name, COUNT(*) AS total_rows FROM tbkeu_jurnal_detail;

-- =========================================================
-- MIGRATION DOWN
-- Jalankan hanya jika rollback tabel General Ledger diperlukan.
-- =========================================================

-- SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE IF EXISTS `tbkeu_jurnal_detail`;
-- DROP TABLE IF EXISTS `tbkeu_jurnal`;
-- DROP TABLE IF EXISTS `tbkeu_jenis_jurnal`;
-- DROP TABLE IF EXISTS `tbkeu_periode_fiskal`;
-- SET FOREIGN_KEY_CHECKS = 1;
