-- KARISMA ERP - Accounting Journal Master Options Upgrade
-- Tanggal: 2026-07-13
-- Scope: master pendukung module jurnal untuk Klasifikasi, Saldo Normal, Tipe Kontrol, dan Parent/Subclass.
-- Aman terhadap tabel Purchase Order terlarang:
-- tbpo_transaksi, tbpo_transaksi_tmp, tbpo_transaksi_trashbin, tbpo_akun_tr
-- tidak dibaca, tidak ditulis, tidak diubah, dan tidak menjadi dependency.

SET NAMES utf8mb4;

-- =========================================================
-- MIGRATION UP
-- =========================================================

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

ALTER TABLE `tbkeu_klasifikasi_akun`
  MODIFY `saldo_normal` VARCHAR(20) NOT NULL;

ALTER TABLE `tbkeu_akun`
  MODIFY `saldo_normal` VARCHAR(20) NOT NULL,
  MODIFY `tipe_kontrol` VARCHAR(50) NOT NULL DEFAULT 'NONE';

-- =========================================================
-- SEED MASTER SALDO NORMAL
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

-- =========================================================
-- SEED MASTER TIPE KONTROL
-- =========================================================

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
-- SQL AUDIT READ-ONLY
-- =========================================================

SELECT a.saldo_normal, COUNT(*) AS total
FROM tbkeu_akun a
LEFT JOIN tbkeu_saldo_normal s ON s.kode_saldo = a.saldo_normal
WHERE s.kode_saldo IS NULL
GROUP BY a.saldo_normal;

SELECT a.tipe_kontrol, COUNT(*) AS total
FROM tbkeu_akun a
LEFT JOIN tbkeu_tipe_kontrol t ON t.kode_tipe_kontrol = a.tipe_kontrol
WHERE t.kode_tipe_kontrol IS NULL
GROUP BY a.tipe_kontrol;

SELECT k.saldo_normal, COUNT(*) AS total
FROM tbkeu_klasifikasi_akun k
LEFT JOIN tbkeu_saldo_normal s ON s.kode_saldo = k.saldo_normal
WHERE s.kode_saldo IS NULL
GROUP BY k.saldo_normal;

-- =========================================================
-- SQL VERIFICATION
-- =========================================================

SELECT 'tbkeu_saldo_normal' AS table_name, COUNT(*) AS total_rows FROM tbkeu_saldo_normal
UNION ALL
SELECT 'tbkeu_tipe_kontrol' AS table_name, COUNT(*) AS total_rows FROM tbkeu_tipe_kontrol;

-- =========================================================
-- MIGRATION DOWN
-- Jalankan hanya bila rollback master pendukung diperlukan.
-- =========================================================

-- DROP TABLE IF EXISTS `tbkeu_tipe_kontrol`;
-- DROP TABLE IF EXISTS `tbkeu_saldo_normal`;
-- ALTER TABLE `tbkeu_akun`
--   MODIFY `saldo_normal` ENUM('DEBIT','KREDIT') NOT NULL,
--   MODIFY `tipe_kontrol` ENUM(
--     'NONE','KAS','BANK','PIUTANG','HUTANG','PERSEDIAAN','GRNI',
--     'PAJAK_MASUKAN','PAJAK_KELUARAN','UANG_MUKA_CUSTOMER',
--     'UANG_MUKA_SUPPLIER','LABA_DITAHAN'
--   ) NOT NULL DEFAULT 'NONE';
-- ALTER TABLE `tbkeu_klasifikasi_akun`
--   MODIFY `saldo_normal` ENUM('DEBIT','KREDIT') NOT NULL;
