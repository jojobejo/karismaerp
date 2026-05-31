ALTER TABLE tbso_sales_order
MODIFY COLUMN status ENUM('draft','open','sedang_verifikasi','siap_faktur','completed','cancelled')
NOT NULL DEFAULT 'draft';

ALTER TABLE tbso_sales_order_detail
ADD COLUMN IF NOT EXISTS qty_siap_faktur DECIMAL(12,3) NULL AFTER qty_faktur,
ADD COLUMN IF NOT EXISTS qty_tidak_terkirim DECIMAL(12,3) NOT NULL DEFAULT 0 AFTER qty_siap_faktur,
ADD COLUMN IF NOT EXISTS verifikasi_loading_status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER qty_tidak_terkirim,
ADD COLUMN IF NOT EXISTS verifikasi_loading_note TEXT NULL AFTER verifikasi_loading_status,
ADD COLUMN IF NOT EXISTS verifikasi_loading_by VARCHAR(50) NULL AFTER verifikasi_loading_note,
ADD COLUMN IF NOT EXISTS verifikasi_loading_at DATETIME NULL AFTER verifikasi_loading_by;
