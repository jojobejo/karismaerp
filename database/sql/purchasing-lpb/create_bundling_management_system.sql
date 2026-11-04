-- ==========================================================
-- Migrasi Database Modul Paket Bundling KarismaERP
-- Tanggal: 2026-09-07
-- Deskripsi: Menambahkan tabel master formula, request purchasing,
--            realisasi assembly logistik, dan pembongkaran disassembly.
-- ==========================================================

-- 1. Tabel Master Formula Paket Bundling
CREATE TABLE IF NOT EXISTS `tberp_bundling_formula` (
  `id_formula` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_paket` VARCHAR(25) NOT NULL,
  `nama_paket` VARCHAR(255) NOT NULL,
  `satuan_paket` VARCHAR(50) DEFAULT 'Box',
  `keterangan` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_formula`),
  UNIQUE KEY `idx_kode_paket` (`kode_paket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Detail Komposisi Formula Paket Bundling
CREATE TABLE IF NOT EXISTS `tberp_bundling_formula_detail` (
  `id_detail` INT(11) NOT NULL AUTO_INCREMENT,
  `id_formula` INT(11) NOT NULL,
  `kode_barang_komponen` VARCHAR(25) NOT NULL,
  `nama_barang_komponen` VARCHAR(255) DEFAULT NULL,
  `qty_komponen` DECIMAL(15,3) NOT NULL DEFAULT 1.000,
  `satuan` VARCHAR(50) DEFAULT 'Pcs',
  PRIMARY KEY (`id_detail`),
  KEY `idx_formula_id` (`id_formula`),
  KEY `idx_komponen` (`kode_barang_komponen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Transaksi Request Paket Bundling oleh Purchasing
CREATE TABLE IF NOT EXISTS `tberp_bundling_request` (
  `id_request` INT(11) NOT NULL AUTO_INCREMENT,
  `no_request` VARCHAR(50) NOT NULL,
  `tanggal_request` DATE NOT NULL,
  `kode_paket` VARCHAR(25) NOT NULL,
  `nama_paket` VARCHAR(255) NOT NULL,
  `id_gudang_tujuan` INT(11) NOT NULL,
  `id_gudang_asal` INT(11) NOT NULL DEFAULT 2,
  `qty_request` DECIMAL(15,3) NOT NULL,
  `qty_realisasi` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `satuan` VARCHAR(50) DEFAULT 'Box',
  `status` ENUM('MENUNGGU_PROSES','PROSES_SEBAGIAN','SELESAI','BATAL') NOT NULL DEFAULT 'MENUNGGU_PROSES',
  `user_request` VARCHAR(50) NOT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_request`),
  UNIQUE KEY `idx_no_request` (`no_request`),
  KEY `idx_kode_paket_req` (`kode_paket`),
  KEY `idx_status_req` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Detail Kebutuhan Komponen Request Bundling
CREATE TABLE IF NOT EXISTS `tberp_bundling_request_detail` (
  `id_req_detail` INT(11) NOT NULL AUTO_INCREMENT,
  `id_request` INT(11) NOT NULL,
  `kode_barang_komponen` VARCHAR(25) NOT NULL,
  `nama_barang_komponen` VARCHAR(255) DEFAULT NULL,
  `qty_per_paket` DECIMAL(15,3) NOT NULL DEFAULT 1.000,
  `qty_total_kebutuhan` DECIMAL(15,3) NOT NULL,
  `qty_terpenuhi` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `satuan` VARCHAR(50) DEFAULT 'Pcs',
  PRIMARY KEY (`id_req_detail`),
  KEY `idx_request_id` (`id_request`),
  KEY `idx_req_komponen` (`kode_barang_komponen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Transaksi Realisasi Pembuatan / Assembly Paket oleh Logistik
CREATE TABLE IF NOT EXISTS `tberp_bundling_assembly` (
  `id_assembly` INT(11) NOT NULL AUTO_INCREMENT,
  `no_assembly` VARCHAR(50) NOT NULL,
  `id_request` INT(11) DEFAULT NULL,
  `no_request` VARCHAR(50) DEFAULT NULL,
  `tanggal` DATE NOT NULL,
  `kode_paket` VARCHAR(25) NOT NULL,
  `nama_paket` VARCHAR(255) NOT NULL,
  `id_gudang` INT(11) NOT NULL,
  `qty_assembly` DECIMAL(15,3) NOT NULL,
  `satuan` VARCHAR(50) DEFAULT 'Box',
  `no_lot_paket` VARCHAR(100) DEFAULT '-',
  `expired_date_paket` DATE DEFAULT NULL,
  `total_nilai_hpp` DECIMAL(18,2) DEFAULT 0.00,
  `hpp_per_paket` DECIMAL(18,2) DEFAULT 0.00,
  `user_input` VARCHAR(50) NOT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_assembly`),
  UNIQUE KEY `idx_no_assembly` (`no_assembly`),
  KEY `idx_assembly_request` (`id_request`),
  KEY `idx_assembly_paket` (`kode_paket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 6. Detail Komponen yang Digunakan pada Assembly
CREATE TABLE IF NOT EXISTS `tberp_bundling_assembly_detail` (
  `id_asm_detail` INT(11) NOT NULL AUTO_INCREMENT,
  `id_assembly` INT(11) NOT NULL,
  `kode_barang_komponen` VARCHAR(25) NOT NULL,
  `nama_barang_komponen` VARCHAR(255) DEFAULT NULL,
  `no_lot` VARCHAR(100) DEFAULT '-',
  `expired_date` DATE DEFAULT NULL,
  `qty_digunakan` DECIMAL(15,3) NOT NULL,
  `satuan` VARCHAR(50) DEFAULT 'Pcs',
  `hpp_satuan` DECIMAL(18,2) DEFAULT 0.00,
  `total_hpp` DECIMAL(18,2) DEFAULT 0.00,
  PRIMARY KEY (`id_asm_detail`),
  KEY `idx_asm_parent` (`id_assembly`),
  KEY `idx_asm_komponen` (`kode_barang_komponen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Transaksi Pembongkaran / Disassembly Paket (Untuk Penjualan Ecer)
CREATE TABLE IF NOT EXISTS `tberp_bundling_disassembly` (
  `id_disassembly` INT(11) NOT NULL AUTO_INCREMENT,
  `no_disassembly` VARCHAR(50) NOT NULL,
  `tanggal` DATE NOT NULL,
  `kode_paket` VARCHAR(25) NOT NULL,
  `nama_paket` VARCHAR(255) NOT NULL,
  `id_gudang` INT(11) NOT NULL,
  `qty_disassembly` DECIMAL(15,3) NOT NULL,
  `satuan` VARCHAR(50) DEFAULT 'Box',
  `no_lot_paket` VARCHAR(100) DEFAULT '-',
  `expired_date_paket` DATE DEFAULT NULL,
  `total_nilai_hpp` DECIMAL(18,2) DEFAULT 0.00,
  `alasan` TEXT NOT NULL,
  `user_input` VARCHAR(50) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_disassembly`),
  UNIQUE KEY `idx_no_disassembly` (`no_disassembly`),
  KEY `idx_dsb_paket` (`kode_paket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 8. Detail Komponen yang Dikembalikan saat Disassembly
CREATE TABLE IF NOT EXISTS `tberp_bundling_disassembly_detail` (
  `id_dsb_detail` INT(11) NOT NULL AUTO_INCREMENT,
  `id_disassembly` INT(11) NOT NULL,
  `kode_barang_komponen` VARCHAR(25) NOT NULL,
  `nama_barang_komponen` VARCHAR(255) DEFAULT NULL,
  `no_lot` VARCHAR(100) DEFAULT '-',
  `expired_date` DATE DEFAULT NULL,
  `qty_kembali` DECIMAL(15,3) NOT NULL,
  `satuan` VARCHAR(50) DEFAULT 'Pcs',
  `hpp_satuan` DECIMAL(18,2) DEFAULT 0.00,
  `total_hpp` DECIMAL(18,2) DEFAULT 0.00,
  PRIMARY KEY (`id_dsb_detail`),
  KEY `idx_dsb_parent` (`id_disassembly`),
  KEY `idx_dsb_komponen` (`kode_barang_komponen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 9. Pastikan Master Gudang Bundling Terdaftar
INSERT INTO `tb_gudang` (`id_gudang`, `nama_gudang`, `tipe`, `is_active`, `created_at`)
SELECT 12, 'Gdg. Bundling', 'INDUK', 1, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `tb_gudang` WHERE `id_gudang` = 12 OR `nama_gudang` LIKE '%Bundling%'
);
