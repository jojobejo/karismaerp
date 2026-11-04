CREATE TABLE IF NOT EXISTS `tb_lpb_revision_request` (
    `id_request` INT(11) NOT NULL AUTO_INCREMENT,
    `no_request` VARCHAR(50) NOT NULL,
    `id_lpb` INT(11) NOT NULL,
    `nomor_lpb` VARCHAR(50) DEFAULT NULL,
    `kd_po` VARCHAR(100) DEFAULT NULL,
    `no_po` VARCHAR(100) DEFAULT NULL,
    `kd_supplier` VARCHAR(100) DEFAULT NULL,
    `nama_supplier` VARCHAR(255) DEFAULT NULL,
    `gudang_id` VARCHAR(30) DEFAULT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'REQUESTED',
    `alasan_revisi` TEXT DEFAULT NULL,
    `total_faktur` INT(11) NOT NULL DEFAULT 0,
    `total_item` INT(11) NOT NULL DEFAULT 0,
    `total_qty_terjual` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `requested_by` VARCHAR(100) DEFAULT NULL,
    `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `accounting_by` VARCHAR(100) DEFAULT NULL,
    `accounting_at` DATETIME DEFAULT NULL,
    `purchasing_by` VARCHAR(100) DEFAULT NULL,
    `purchasing_at` DATETIME DEFAULT NULL,
    `completed_by` VARCHAR(100) DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id_request`),
    UNIQUE KEY `uk_no_request` (`no_request`),
    KEY `idx_lpb_revision_lpb` (`id_lpb`),
    KEY `idx_lpb_revision_status` (`status`),
    KEY `idx_lpb_revision_requested_at` (`requested_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tb_lpb_revision_request_detail` (
    `id_detail` INT(11) NOT NULL AUTO_INCREMENT,
    `id_request` INT(11) NOT NULL,
    `id_lpb` INT(11) NOT NULL,
    `id_detail_lpb` INT(11) DEFAULT NULL,
    `source_table` VARCHAR(50) NOT NULL,
    `source_pk` VARCHAR(50) DEFAULT NULL,
    `id_faktur` INT(11) DEFAULT NULL,
    `id_faktur_detail` INT(11) DEFAULT NULL,
    `no_faktur` VARCHAR(50) NOT NULL,
    `tanggal_faktur` DATE DEFAULT NULL,
    `status_faktur_before` VARCHAR(30) DEFAULT NULL,
    `kd_barang` VARCHAR(100) NOT NULL,
    `nama_barang` VARCHAR(255) DEFAULT NULL,
    `no_lot` VARCHAR(100) DEFAULT NULL,
    `expired_date` DATE DEFAULT NULL,
    `qty_lpb` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `qty_terjual` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    `status` VARCHAR(30) NOT NULL DEFAULT 'REQUESTED',
    `unpost_by` VARCHAR(100) DEFAULT NULL,
    `unpost_at` DATETIME DEFAULT NULL,
    `catatan_accounting` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_detail`),
    KEY `idx_lpb_revision_detail_request` (`id_request`),
    KEY `idx_lpb_revision_detail_faktur` (`no_faktur`),
    KEY `idx_lpb_revision_detail_source` (`source_table`, `source_pk`),
    KEY `idx_lpb_revision_detail_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `tb_lpb_revision_request_log` (
    `id_log` INT(11) NOT NULL AUTO_INCREMENT,
    `id_request` INT(11) NOT NULL,
    `action_type` VARCHAR(50) NOT NULL,
    `status_before` VARCHAR(30) DEFAULT NULL,
    `status_after` VARCHAR(30) DEFAULT NULL,
    `keterangan` TEXT DEFAULT NULL,
    `data_before` LONGTEXT DEFAULT NULL,
    `data_after` LONGTEXT DEFAULT NULL,
    `dilakukan_oleh` VARCHAR(100) DEFAULT NULL,
    `dilakukan_pada` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_log`),
    KEY `idx_lpb_revision_log_request` (`id_request`),
    KEY `idx_lpb_revision_log_action` (`action_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tb_users` (`username`, `password`, `level`, `nama_lngkp`, `nik`, `bagian`, `departement`, `jabatan`, `atasan`, `penilai`, `status`, `status_karyawan`, `default_redirect`, `created_at`, `updated_at`)
SELECT 'purchasing_lpb_revision', '$2y$10$nM3xaRzksnG8pYUwwFFay.HYR29Ue6iTzaknV8cW76Q38quVS255y', 1, 'Purchasing Revisi LPB', 'PUR-LPB-REV', 'ADMINPURCHASING', 'PURCHASING', 'PURCHASING', '-', '-', 1, 'AKTIF', 'ics/lpb_revision', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_users` WHERE `username` = 'purchasing_lpb_revision');

INSERT INTO `tb_users` (`username`, `password`, `level`, `nama_lngkp`, `nik`, `bagian`, `departement`, `jabatan`, `atasan`, `penilai`, `status`, `status_karyawan`, `default_redirect`, `created_at`, `updated_at`)
SELECT 'accounting_lpb_revision', '$2y$10$hZ75XOWO87uPWHOrIWQlj.qJXo.3HaK3KGlWjNgFXb9spD8LU3/0m', 1, 'Accounting Revisi LPB', 'ACC-LPB-REV', 'ACCOUNTING', 'ACCOUNTING', 'ACCOUNTING', '-', '-', 1, 'AKTIF', 'ics/lpb_revision', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `tb_users` WHERE `username` = 'accounting_lpb_revision');
