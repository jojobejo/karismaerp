-- ========================================================
-- Migrasi Database: Penambahan Master Gudang Baru
-- Tanggal: 31 Oktober 2026
-- Tabel Acuan: tb_gudang
-- Keterangan: Menambahkan 14 gudang baru berdasarkan daftar referensi sistem.
-- ========================================================

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gdg. Ajung', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. Ajung');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gdg. Ajung 148', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. Ajung 148');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gdg. Belakang1', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. Belakang1');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gdg. Belakang2', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. Belakang2');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gdg. Belakang3', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. Belakang3');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gdg. IT', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. IT');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gdg. Imam Bonjol', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. Imam Bonjol');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gdg. Petugas MIA', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. Petugas MIA');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gdg. Sample', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. Sample');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gdg. Supplier', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gdg. Supplier');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gudang Titipan Direksi', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gudang Titipan Direksi');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gudang Titipan Kanvas', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gudang Titipan Kanvas');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gudang Titipan Purchasing', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gudang Titipan Purchasing');

INSERT INTO `tb_gudang` (`nama_gudang`, `tipe`, `is_active`, `created_at`) 
SELECT 'Gudang Titipan Sales', 'INDUK', 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_gudang` WHERE `nama_gudang` = 'Gudang Titipan Sales');

