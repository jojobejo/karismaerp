-- =========================================================================
-- Migrasi Database: Penambahan Kolom nominal_terpakai_awal pada tb_customer_acak
-- Modul: Sales / Pecah Faktur Z (Faktur H)
-- Tanggal: 07 November 2026
-- =========================================================================

-- Tambahkan kolom nominal_terpakai_awal jika belum ada
ALTER TABLE `tb_customer_acak`
ADD COLUMN `nominal_terpakai_awal` DECIMAL(18,2) NOT NULL DEFAULT '0.00' AFTER `npwp`;

