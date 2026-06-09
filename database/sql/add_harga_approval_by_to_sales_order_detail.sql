ALTER TABLE tbso_sales_order_detail
    ADD COLUMN IF NOT EXISTS harga_approval_by VARCHAR(50) NULL
        AFTER hrg_pokok;
