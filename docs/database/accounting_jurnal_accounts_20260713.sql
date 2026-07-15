-- KARISMA ERP - Accounting Journal Accounts
-- Tanggal: 2026-07-13
-- Scope: tahap awal modul jurnal untuk Chart of Accounts.
-- Aman terhadap tabel Purchase Order terlarang:
-- tbpo_transaksi, tbpo_transaksi_tmp, tbpo_transaksi_trashbin, tbpo_akun_tr
-- tidak dibaca, tidak ditulis, tidak diubah, dan tidak menjadi dependency.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- MIGRATION UP
-- =========================================================

CREATE TABLE IF NOT EXISTS `tbkeu_klasifikasi_akun` (
  `id_klasifikasi` TINYINT UNSIGNED NOT NULL,
  `kode_klasifikasi` VARCHAR(10) NOT NULL,
  `nama_klasifikasi` VARCHAR(100) NOT NULL,
  `alias_klasifikasi` VARCHAR(100) DEFAULT NULL,
  `jenis_laporan` ENUM('NERACA','LABA_RUGI') NOT NULL,
  `saldo_normal` VARCHAR(20) NOT NULL,
  `urutan` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_klasifikasi`),
  UNIQUE KEY `uk_tbkeu_klasifikasi_kode` (`kode_klasifikasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_akun` (
  `id_akun` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_akun` VARCHAR(30) NOT NULL,
  `nama_akun` VARCHAR(150) NOT NULL,
  `id_klasifikasi` TINYINT UNSIGNED NOT NULL,
  `parent_id` BIGINT UNSIGNED DEFAULT NULL,
  `level_akun` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `saldo_normal` VARCHAR(20) NOT NULL,
  `tipe_akun` ENUM('HEADER','POSTING') NOT NULL DEFAULT 'POSTING',
  `tipe_kontrol` VARCHAR(50) NOT NULL DEFAULT 'NONE',
  `allow_manual_journal` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` BIGINT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_by` BIGINT DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_akun`),
  UNIQUE KEY `uk_tbkeu_akun_kode` (`kode_akun`),
  KEY `idx_tbkeu_akun_klasifikasi` (`id_klasifikasi`),
  KEY `idx_tbkeu_akun_parent` (`parent_id`),
  KEY `idx_tbkeu_akun_tipe_status` (`tipe_akun`, `is_active`),
  CONSTRAINT `fk_tbkeu_akun_klasifikasi`
    FOREIGN KEY (`id_klasifikasi`) REFERENCES `tbkeu_klasifikasi_akun` (`id_klasifikasi`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_tbkeu_akun_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `tbkeu_akun` (`id_akun`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_saldo_normal` (
  `kode_saldo` VARCHAR(20) NOT NULL,
  `nama_saldo` VARCHAR(50) NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `urutan` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`kode_saldo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tbkeu_tipe_kontrol` (
  `kode_tipe_kontrol` VARCHAR(50) NOT NULL,
  `nama_tipe_kontrol` VARCHAR(100) NOT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `urutan` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`kode_tipe_kontrol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- SEED MASTER SALDO NORMAL DAN TIPE KONTROL
-- =========================================================

INSERT INTO `tbkeu_saldo_normal`
  (`kode_saldo`, `nama_saldo`, `keterangan`, `urutan`, `is_active`)
VALUES
  ('DEBIT', 'Debit', 'Saldo normal debit', 10, 1),
  ('KREDIT', 'Kredit', 'Saldo normal kredit', 20, 1)
ON DUPLICATE KEY UPDATE
  `nama_saldo` = VALUES(`nama_saldo`),
  `keterangan` = VALUES(`keterangan`),
  `urutan` = VALUES(`urutan`),
  `is_active` = VALUES(`is_active`);

INSERT INTO `tbkeu_tipe_kontrol`
  (`kode_tipe_kontrol`, `nama_tipe_kontrol`, `keterangan`, `urutan`, `is_active`)
VALUES
  ('NONE', 'None', 'Akun biasa tanpa kontrol khusus', 10, 1),
  ('KAS', 'Kas', 'Akun kas tunai', 20, 1),
  ('BANK', 'Bank', 'Akun rekening bank', 30, 1),
  ('PIUTANG', 'Piutang', 'Akun piutang customer', 40, 1),
  ('HUTANG', 'Hutang', 'Akun hutang supplier', 50, 1),
  ('PERSEDIAAN', 'Persediaan', 'Akun persediaan barang', 60, 1),
  ('GRNI', 'GRNI', 'Barang diterima belum ditagih supplier', 70, 1),
  ('PAJAK_MASUKAN', 'Pajak Masukan', 'Akun PPN masukan', 80, 1),
  ('PAJAK_KELUARAN', 'Pajak Keluaran', 'Akun PPN keluaran', 90, 1),
  ('UANG_MUKA_CUSTOMER', 'Uang Muka Customer', 'Uang muka dari customer', 100, 1),
  ('UANG_MUKA_SUPPLIER', 'Uang Muka Supplier', 'Uang muka ke supplier', 110, 1),
  ('LABA_DITAHAN', 'Laba Ditahan', 'Akun laba ditahan', 120, 1)
ON DUPLICATE KEY UPDATE
  `nama_tipe_kontrol` = VALUES(`nama_tipe_kontrol`),
  `keterangan` = VALUES(`keterangan`),
  `urutan` = VALUES(`urutan`),
  `is_active` = VALUES(`is_active`);

-- =========================================================
-- SEED KLASIFIKASI
-- =========================================================

INSERT INTO `tbkeu_klasifikasi_akun`
  (`id_klasifikasi`, `kode_klasifikasi`, `nama_klasifikasi`, `alias_klasifikasi`, `jenis_laporan`, `saldo_normal`, `urutan`, `is_active`)
VALUES
  (1, '1', 'Harta', 'Asset', 'NERACA', 'DEBIT', 10, 1),
  (2, '2', 'Kewajiban', 'Liabilities', 'NERACA', 'KREDIT', 20, 1),
  (3, '3', 'Modal', 'Equity', 'NERACA', 'KREDIT', 30, 1),
  (4, '4', 'Pendapatan', 'Revenues', 'LABA_RUGI', 'KREDIT', 40, 1),
  (5, '5', 'Beban Atas Pendapatan', 'Cost of Revenues', 'LABA_RUGI', 'DEBIT', 50, 1),
  (6, '6', 'Beban Operasional', 'Operating Expenses', 'LABA_RUGI', 'DEBIT', 60, 1),
  (7, '7', 'Beban Non Operasional', 'Non Operating Expenses', 'LABA_RUGI', 'DEBIT', 70, 1),
  (8, '8', 'Pendapatan Lain', 'Other Revenues', 'LABA_RUGI', 'KREDIT', 80, 1),
  (9, '9', 'Beban Lain', 'Other Expenses', 'LABA_RUGI', 'DEBIT', 90, 1)
ON DUPLICATE KEY UPDATE
  `nama_klasifikasi` = VALUES(`nama_klasifikasi`),
  `alias_klasifikasi` = VALUES(`alias_klasifikasi`),
  `jenis_laporan` = VALUES(`jenis_laporan`),
  `saldo_normal` = VALUES(`saldo_normal`),
  `urutan` = VALUES(`urutan`),
  `is_active` = VALUES(`is_active`);

-- =========================================================
-- SEED CHART OF ACCOUNTS DASAR
-- Kode akun seed dapat diubah lewat file seed ini sebelum produksi.
-- =========================================================

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_active`)
VALUES
  ('1000', 'Harta', 1, NULL, 1, 'DEBIT', 'HEADER', 'NONE', 0, 1),
  ('2000', 'Kewajiban', 2, NULL, 1, 'KREDIT', 'HEADER', 'NONE', 0, 1),
  ('3000', 'Modal', 3, NULL, 1, 'KREDIT', 'HEADER', 'NONE', 0, 1),
  ('4000', 'Pendapatan', 4, NULL, 1, 'KREDIT', 'HEADER', 'NONE', 0, 1),
  ('5000', 'Beban Atas Pendapatan', 5, NULL, 1, 'DEBIT', 'HEADER', 'NONE', 0, 1),
  ('6000', 'Beban Operasional', 6, NULL, 1, 'DEBIT', 'HEADER', 'NONE', 0, 1),
  ('7000', 'Beban Non Operasional', 7, NULL, 1, 'DEBIT', 'HEADER', 'NONE', 0, 1),
  ('8000', 'Pendapatan Lain', 8, NULL, 1, 'KREDIT', 'HEADER', 'NONE', 0, 1),
  ('9000', 'Beban Lain', 9, NULL, 1, 'DEBIT', 'HEADER', 'NONE', 0, 1)
ON DUPLICATE KEY UPDATE
  `nama_akun` = VALUES(`nama_akun`),
  `id_klasifikasi` = VALUES(`id_klasifikasi`),
  `saldo_normal` = VALUES(`saldo_normal`),
  `tipe_akun` = VALUES(`tipe_akun`),
  `tipe_kontrol` = VALUES(`tipe_kontrol`),
  `allow_manual_journal` = VALUES(`allow_manual_journal`),
  `is_active` = VALUES(`is_active`);

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_active`)
SELECT '1100', 'Kas', 1, h.id_akun, 2, 'DEBIT', 'POSTING', 'KAS', 1, 1 FROM tbkeu_akun h WHERE h.kode_akun = '1000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `tipe_kontrol` = VALUES(`tipe_kontrol`);

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_active`)
SELECT '1200', 'Bank', 1, h.id_akun, 2, 'DEBIT', 'POSTING', 'BANK', 1, 1 FROM tbkeu_akun h WHERE h.kode_akun = '1000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `tipe_kontrol` = VALUES(`tipe_kontrol`);

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_active`)
SELECT '1300', 'Piutang Usaha', 1, h.id_akun, 2, 'DEBIT', 'POSTING', 'PIUTANG', 0, 1 FROM tbkeu_akun h WHERE h.kode_akun = '1000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `tipe_kontrol` = VALUES(`tipe_kontrol`);

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_active`)
SELECT '1400', 'Persediaan Barang', 1, h.id_akun, 2, 'DEBIT', 'POSTING', 'PERSEDIAAN', 0, 1 FROM tbkeu_akun h WHERE h.kode_akun = '1000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `tipe_kontrol` = VALUES(`tipe_kontrol`);

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_active`)
SELECT '2100', 'Hutang Usaha', 2, h.id_akun, 2, 'KREDIT', 'POSTING', 'HUTANG', 0, 1 FROM tbkeu_akun h WHERE h.kode_akun = '2000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `tipe_kontrol` = VALUES(`tipe_kontrol`);

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_active`)
SELECT '2200', 'GRNI/Barang Diterima Belum Ditagih', 2, h.id_akun, 2, 'KREDIT', 'POSTING', 'GRNI', 0, 1 FROM tbkeu_akun h WHERE h.kode_akun = '2000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`), `tipe_kontrol` = VALUES(`tipe_kontrol`);

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_active`)
SELECT '4100', 'Penjualan', 4, h.id_akun, 2, 'KREDIT', 'POSTING', 'NONE', 0, 1 FROM tbkeu_akun h WHERE h.kode_akun = '4000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`);

INSERT INTO `tbkeu_akun`
  (`kode_akun`, `nama_akun`, `id_klasifikasi`, `parent_id`, `level_akun`, `saldo_normal`, `tipe_akun`, `tipe_kontrol`, `allow_manual_journal`, `is_active`)
SELECT '5100', 'Harga Pokok Penjualan', 5, h.id_akun, 2, 'DEBIT', 'POSTING', 'NONE', 0, 1 FROM tbkeu_akun h WHERE h.kode_akun = '5000'
ON DUPLICATE KEY UPDATE `nama_akun` = VALUES(`nama_akun`), `parent_id` = VALUES(`parent_id`);

-- =========================================================
-- SQL AUDIT READ-ONLY
-- =========================================================

SELECT kode_akun, COUNT(*) AS total
FROM tbkeu_akun
GROUP BY kode_akun
HAVING COUNT(*) > 1;

SELECT a.id_akun, a.kode_akun, a.nama_akun
FROM tbkeu_akun a
LEFT JOIN tbkeu_klasifikasi_akun k ON k.id_klasifikasi = a.id_klasifikasi
WHERE k.id_klasifikasi IS NULL;

SELECT c.id_akun, c.kode_akun, c.nama_akun, c.parent_id
FROM tbkeu_akun c
LEFT JOIN tbkeu_akun p ON p.id_akun = c.parent_id
WHERE c.parent_id IS NOT NULL AND p.id_akun IS NULL;

SELECT id_akun, kode_akun, nama_akun
FROM tbkeu_akun
WHERE parent_id = id_akun;

SELECT id_akun, kode_akun, nama_akun
FROM tbkeu_akun
WHERE tipe_akun = 'HEADER' AND allow_manual_journal = 1;

-- =========================================================
-- SQL VERIFICATION
-- =========================================================

SELECT 'tbkeu_klasifikasi_akun' AS table_name, COUNT(*) AS total_rows FROM tbkeu_klasifikasi_akun
UNION ALL
SELECT 'tbkeu_akun' AS table_name, COUNT(*) AS total_rows FROM tbkeu_akun
UNION ALL
SELECT 'tbkeu_saldo_normal' AS table_name, COUNT(*) AS total_rows FROM tbkeu_saldo_normal
UNION ALL
SELECT 'tbkeu_tipe_kontrol' AS table_name, COUNT(*) AS total_rows FROM tbkeu_tipe_kontrol;

SELECT
  k.kode_klasifikasi,
  k.nama_klasifikasi,
  COUNT(a.id_akun) AS total_akun
FROM tbkeu_klasifikasi_akun k
LEFT JOIN tbkeu_akun a ON a.id_klasifikasi = k.id_klasifikasi
GROUP BY k.id_klasifikasi, k.kode_klasifikasi, k.nama_klasifikasi
ORDER BY k.urutan;

-- Bukti scope: tidak ada DDL/DML terhadap tabel Purchase Order terlarang dalam file ini.

-- =========================================================
-- MIGRATION DOWN
-- Jalankan hanya jika rollback modul awal jurnal diperlukan.
-- =========================================================

-- SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE IF EXISTS `tbkeu_akun`;
-- DROP TABLE IF EXISTS `tbkeu_tipe_kontrol`;
-- DROP TABLE IF EXISTS `tbkeu_saldo_normal`;
-- DROP TABLE IF EXISTS `tbkeu_klasifikasi_akun`;
-- SET FOREIGN_KEY_CHECKS = 1;
