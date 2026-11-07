-- =========================================================================
-- MIGRASI DATABASE: PENERBITAN FAKTUR KONSINYASI MANDIRI
-- KARISMA ERP (PT. KARISMA INDOARGO UNIVERSAL)
-- Tanggal: 30 September 2026
-- =========================================================================

-- 1. Tambah kolom no_faktur_konsinyasi pada tabel tbkeu_pembayaran_faktur
--    (Kolom ini mencatat nomor faktur konsinyasi baru yang diterbitkan pada termin pembayaran)
ALTER TABLE `tbkeu_pembayaran_faktur`
  ADD COLUMN `no_faktur_konsinyasi` VARCHAR(50) NULL DEFAULT NULL AFTER `no_faktur`,
  ADD INDEX `idx_no_faktur_konsinyasi` (`no_faktur_konsinyasi`);

-- 2. Buat tabel register faktur konsinyasi mandiri: tb_konsinyasi_faktur
--    (Menyimpan faktur baru ber-suffix -1, -2, dst tanpa mengotori tbso_faktur_penjualan)
CREATE TABLE IF NOT EXISTS `tb_konsinyasi_faktur` (
  `id_faktur_konsinyasi` INT(11) NOT NULL AUTO_INCREMENT,
  `no_faktur_konsinyasi` VARCHAR(50) NOT NULL COMMENT 'Contoh: TINV2609260001-1, TINV2609260001-2',
  `id_faktur_induk` INT(11) NOT NULL COMMENT 'Relasi ke tbso_faktur_penjualan.id_faktur',
  `no_faktur_induk` VARCHAR(50) NOT NULL COMMENT 'Contoh: TINV2609260001',
  `id_pembayaran` INT(11) NOT NULL COMMENT 'Relasi ke tbkeu_pembayaran_faktur.id_pembayaran',
  `id_settlement` INT(11) DEFAULT NULL COMMENT 'Relasi ke tb_konsinyasi_settlement.id_settlement',
  `id_so` INT(11) DEFAULT NULL COMMENT 'Relasi ke tbso_sales_order.id_so',
  `no_so` VARCHAR(50) DEFAULT NULL COMMENT 'Nomor SO asal titip jual',
  `termin_ke` INT(11) NOT NULL DEFAULT 1 COMMENT 'Urutan pembayaran/termin pelunasan kios',
  `tanggal_faktur` DATE NOT NULL COMMENT 'Tanggal pembayaran / penerbitan faktur',
  `kd_customer` VARCHAR(50) DEFAULT NULL,
  `nama_customer` VARCHAR(150) DEFAULT NULL,
  `kd_suplier` VARCHAR(50) DEFAULT NULL,
  `nama_suplier` VARCHAR(150) DEFAULT NULL,
  `gudang_id` INT(11) DEFAULT 13 COMMENT 'Default 13 (Gudang Konsinyasi)',
  `kd_barang` VARCHAR(50) NOT NULL,
  `nama_barang` VARCHAR(200) NOT NULL,
  `no_lot` VARCHAR(100) DEFAULT NULL,
  `expired_date` DATE DEFAULT NULL,
  `qty` DECIMAL(15,3) NOT NULL DEFAULT 0.000 COMMENT 'Kuantitas barang yang dibeli kios pada termin ini',
  `satuan` VARCHAR(30) DEFAULT 'PCS',
  `hrg_satuan` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga jual satuan ke kios',
  `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total nominal realisasi penjualan (qty * hrg_satuan)',
  `jumlah_bayar` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Nominal pembayaran kasir/bank',
  `metode_pembayaran` VARCHAR(50) DEFAULT NULL COMMENT 'Akun kas/bank atau metode pelunasan',
  `status` ENUM('LAKU','BILLED','CANCELLED') NOT NULL DEFAULT 'LAKU',
  `created_by` VARCHAR(100) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_faktur_konsinyasi`),
  UNIQUE KEY `idx_no_faktur_konsinyasi` (`no_faktur_konsinyasi`),
  KEY `idx_faktur_induk` (`id_faktur_induk`),
  KEY `idx_pembayaran` (`id_pembayaran`),
  KEY `idx_settlement` (`id_settlement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Data Historis / Sinkronisasi Transaksi Konsinyasi yang Sudah Terbit:
-- Update tabel pembayaran untuk termin 1, 2, 3 faktur TINV2609260001
UPDATE `tbkeu_pembayaran_faktur` SET `no_faktur_konsinyasi` = 'TINV2609260001-1' WHERE `id_pembayaran` = 6;
UPDATE `tbkeu_pembayaran_faktur` SET `no_faktur_konsinyasi` = 'TINV2609260001-2' WHERE `id_pembayaran` = 8;
UPDATE `tbkeu_pembayaran_faktur` SET `no_faktur_konsinyasi` = 'TINV2609260001-3' WHERE `id_pembayaran` = 10;

-- Update relasi settlement terkait
UPDATE `tb_konsinyasi_settlement` SET `no_faktur` = 'TINV2609260001-1', `id_pembayaran` = 6 WHERE `id_settlement` = 3;
UPDATE `tb_konsinyasi_settlement` SET `no_faktur` = 'TINV2609260001-2', `id_pembayaran` = 8 WHERE `id_settlement` = 7;
UPDATE `tb_konsinyasi_settlement` SET `no_faktur` = 'TINV2609260001-3', `id_pembayaran` = 10 WHERE `id_settlement` = 8;
UPDATE `tb_konsinyasi_settlement` SET `no_faktur` = 'TINV2609260001', `id_pembayaran` = NULL WHERE `id_settlement` = 9;

-- Masukkan arsip faktur konsinyasi ke tabel tb_konsinyasi_faktur
INSERT INTO `tb_konsinyasi_faktur` (
  `no_faktur_konsinyasi`, `id_faktur_induk`, `no_faktur_induk`, `id_pembayaran`, `id_settlement`,
  `id_so`, `no_so`, `termin_ke`, `tanggal_faktur`, `kd_customer`, `nama_customer`,
  `kd_suplier`, `nama_suplier`, `gudang_id`, `kd_barang`, `nama_barang`,
  `no_lot`, `expired_date`, `qty`, `satuan`, `hrg_satuan`, `subtotal`, `jumlah_bayar`,
  `metode_pembayaran`, `status`, `created_by`, `created_at`
) VALUES
('TINV2609260001-1', 12, 'TINV2609260001', 6, 3, 10, 'SO/260926/0001', 1, '2026-09-26', 'PUTR53', 'Abdul Rohman', 'NUFAR01', 'PT.Nufarm Indonesia', 13, 'QROUN01', 'Round Up 486 SL 12 X 1 ltr', '9912', '2027-01-26', 50.000, 'Btl', 80000.00, 4000000.00, 4000000.00, 'Q Kas', 'BILLED', 'admin', '2026-09-26 19:33:48'),
('TINV2609260001-2', 12, 'TINV2609260001', 8, 7, 10, 'SO/260926/0001', 2, '2026-09-29', 'PUTR53', 'Abdul Rohman', 'NUFAR01', 'PT.Nufarm Indonesia', 13, 'QROUN01', 'Round Up 486 SL 12 X 1 ltr', '9912', '2027-01-26', 10.000, 'Btl', 80000.00, 800000.00, 800000.00, 'Q Kas', 'LAKU', 'admin', '2026-09-29 05:30:52'),
('TINV2609260001-3', 12, 'TINV2609260001', 10, 8, 10, 'SO/260926/0001', 3, '2026-09-30', 'PUTR53', 'Abdul Rohman', 'NUFAR01', 'PT.Nufarm Indonesia', 13, 'QROUN01', 'Round Up 486 SL 12 X 1 ltr', '9912', '2027-01-26', 5.000, 'Btl', 80000.00, 400000.00, 400000.00, 'A BCA (Annelia)', 'LAKU', 'admin', '2026-09-30 05:14:38')
ON DUPLICATE KEY UPDATE
  `subtotal` = VALUES(`subtotal`),
  `qty` = VALUES(`qty`),
  `status` = VALUES(`status`);
