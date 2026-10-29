-- Katalog yang dapat diedit untuk kartu Application Directory pada Portal.
CREATE TABLE IF NOT EXISTS `tb_portal_app_directory` (
    `app_code` VARCHAR(80) NOT NULL,
    `app_name` VARCHAR(150) NOT NULL,
    `app_url` VARCHAR(500) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(63) NOT NULL,
    `accent` CHAR(7) NOT NULL DEFAULT '#2563eb',
    `is_hidden` TINYINT(1) NOT NULL DEFAULT 0,
    `updated_by` INT(11) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`app_code`),
    KEY `idx_portal_app_directory_hidden` (`is_hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed awal tidak menimpa pengaturan yang telah diubah jika skrip dijalankan ulang.
INSERT IGNORE INTO `tb_portal_app_directory`
    (`app_code`, `app_name`, `app_url`, `description`, `icon`, `accent`, `is_hidden`)
VALUES
    ('kiu_po', 'KIU PO', 'https://poerp.karismaerp.com', 'Purchase Order & Procurement Management.', 'fa-shopping-cart', '#2563eb', 0),
    ('warehouse', 'Warehouse', 'https://kiuwarehouse.karismaerp.com', 'Warehouse Management System.', 'fa-warehouse', '#0f766e', 0),
    ('kmt_corn', 'KMT CORN', 'https://kmtcorn.karismaerp.com', 'Sales & Operational Management.', 'fa-seedling', '#d97706', 0),
    ('kpi', 'KPI', 'https://kpi.karismaerp.com', 'Employee Performance Dashboard.', 'fa-chart-line', '#4338ca', 0),
    ('pos_security', 'POS Security', 'https://hrd.karismaerp.com', 'Security Activity Journal.', 'fa-shield-alt', '#0ea5e9', 0),
    ('katalog_produk', 'Katalog Produk', 'https://kiukatalog.karismaerp.com', 'Digital Product Catalog.', 'fa-book-open', '#7c3aed', 0),
    ('karisma_online', 'Karisma Online', 'https://karismaonline.karismaerp.com', 'Online Sales Platform.', 'fa-shopping-bag', '#db2777', 1),
    ('plafon_sales', 'Plafon Sales', 'https://plafon.karismaerp.com', 'Customer Credit Limit Monitoring.', 'fa-credit-card', '#0284c7', 0),
    ('stockopname', 'Stockopname', 'https://stockopname.karismaerp.com', 'Stock counting and inventory reconciliation.', 'fa-boxes', '#14b8a6', 0),
    ('penilaian_lingkungan', 'Penilaian Lingkungan Kerja', 'https://lingkungan.karismaerp.com/', 'Work Environment Assessment & Monitoring.', 'fa-leaf', '#16a34a', 0),
    ('overdue', 'Overdue', 'https://tagpd.karismaerp.com/', 'Overdue Invoices & Receivables Monitoring.', 'fa-file-invoice-dollar', '#e11d48', 0),
    ('kios_transfer', 'Kios Transfer', 'https://kiostransfer.karismaerp.com', 'Pencatatan metode pembayaran kios.', 'fa-cash-register', '#0891b2', 0),
    ('sapa_karisma', 'Sapa Karisma', 'https://sapakarisma.karismaerp.com', 'HRIS untuk manajemen SDM yang dikelola HRD.', 'fa-users-cog', '#7c3aed', 0);
