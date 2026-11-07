-- Menyimpan kuantitas aktual yang berhasil dimuat oleh checker.
ALTER TABLE tbso_sales_order_detail
    ADD COLUMN qty_checker_loaded DECIMAL(15,3) NULL
    AFTER checker_loaded;
