ALTER TABLE tbso_sales_order
MODIFY COLUMN status ENUM('draft','open','sedang_verifikasi','siap_faktur','completed','cancelled') NOT NULL DEFAULT 'draft';
