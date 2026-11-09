-- =========================================================================
-- Migrasi Database: Penambahan Kolom Jurnal Penjualan pada tbso_faktur_z_pecah
-- Modul: Sales / Pecah Faktur Z (Faktur H)
-- Tanggal: 07 November 2026
-- =========================================================================

ALTER TABLE `tbso_faktur_z_pecah`
ADD COLUMN `id_jurnal_penjualan` BIGINT UNSIGNED DEFAULT NULL AFTER `nomor_jurnal_a`,
ADD COLUMN `nomor_jurnal_penjualan` VARCHAR(50) DEFAULT NULL AFTER `id_jurnal_penjualan`,
ADD COLUMN `id_jurnal_hpp` BIGINT UNSIGNED DEFAULT NULL AFTER `nomor_jurnal_penjualan`,
ADD COLUMN `nomor_jurnal_hpp` VARCHAR(50) DEFAULT NULL AFTER `id_jurnal_hpp`;
