ALTER TABLE tbso_sales_order
ADD COLUMN IF NOT EXISTS kd_rute VARCHAR(50) NULL AFTER kd_customer;
