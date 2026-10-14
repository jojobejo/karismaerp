-- Migrasi: Skema Tracking Stok FIFO LPB dan Alokasi HPP Konsinyasi
-- File: db/migrations/20261013_consignment_fifo_cogs.sql

CREATE TABLE IF NOT EXISTS `tb_konsinyasi_lpb_batch_stock` (
  `id_batch_stock` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_lpb` INT NOT NULL,
  `id_detail_lpb` INT NOT NULL,
  `nomor_lpb` VARCHAR(50) NOT NULL,
  `tgl_lpb` DATE NOT NULL,
  `kd_suplier` VARCHAR(50) DEFAULT NULL,
  `nama_suplier` VARCHAR(150) DEFAULT NULL,
  `gudang_id` INT NOT NULL DEFAULT 13,
  `kd_barang` VARCHAR(50) NOT NULL,
  `nama_barang` VARCHAR(200) NOT NULL,
  `no_lot` VARCHAR(100) DEFAULT NULL,
  `expired_date` DATE DEFAULT NULL,
  `qty_masuk` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `qty_terjual` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `qty_sisa` DECIMAL(15,3) NOT NULL DEFAULT 0.000,
  `satuan` VARCHAR(30) DEFAULT 'PCS',
  `harga_beli_satuan` DECIMAL(18,4) NOT NULL DEFAULT 0.0000,
  `status` ENUM('AVAILABLE','PARTIAL','EXHAUSTED') NOT NULL DEFAULT 'AVAILABLE',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_batch_stock`),
  UNIQUE KEY `uk_lpb_detail` (`id_lpb`, `id_detail_lpb`),
  KEY `idx_kd_barang_sisa` (`kd_barang`, `qty_sisa`, `tgl_lpb`, `id_lpb`),
  KEY `idx_lpb_batch` (`id_lpb`, `nomor_lpb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tb_konsinyasi_cogs_allocation` (
  `id_cogs_alloc` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pembayaran` INT NOT NULL,
  `id_faktur` INT NOT NULL,
  `no_faktur_konsinyasi` VARCHAR(50) NOT NULL,
  `id_batch_stock` INT UNSIGNED NOT NULL,
  `id_lpb` INT NOT NULL,
  `nomor_lpb` VARCHAR(50) NOT NULL,
  `kd_barang` VARCHAR(50) NOT NULL,
  `no_lot` VARCHAR(100) DEFAULT NULL,
  `expired_date` DATE DEFAULT NULL,
  `qty_allocated` DECIMAL(15,3) NOT NULL,
  `harga_pokok_satuan` DECIMAL(18,4) NOT NULL,
  `subtotal_cogs` DECIMAL(18,4) NOT NULL,
  `id_jurnal_gi` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cogs_alloc`),
  KEY `idx_alloc_pembayaran` (`id_pembayaran`),
  KEY `idx_alloc_faktur` (`id_faktur`),
  KEY `idx_alloc_batch_stock` (`id_batch_stock`),
  KEY `idx_alloc_jurnal_gi` (`id_jurnal_gi`),
  CONSTRAINT `fk_alloc_batch_stock` FOREIGN KEY (`id_batch_stock`) REFERENCES `tb_konsinyasi_lpb_batch_stock` (`id_batch_stock`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
