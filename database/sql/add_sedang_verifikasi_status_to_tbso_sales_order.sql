ALTER TABLE tbso_sales_order
MODIFY COLUMN status ENUM('draft','open','sedang_verifikasi','completed','cancelled') NOT NULL DEFAULT 'draft';
