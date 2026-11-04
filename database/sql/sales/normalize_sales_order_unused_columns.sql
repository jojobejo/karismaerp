-- Normalisasi kolom lama Sales Order.
-- Jalankan setelah kode aplikasi tidak lagi memakai kolom-kolom ini.

ALTER TABLE tbso_sales_order
    DROP COLUMN IF EXISTS no_faktur,
    DROP COLUMN IF EXISTS approved_by,
    DROP COLUMN IF EXISTS approve_by,
    DROP COLUMN IF EXISTS siap_loading_note;

ALTER TABLE tbso_sales_order_detail
    DROP COLUMN IF EXISTS ref_no,
    DROP COLUMN IF EXISTS no_faktur,
    DROP COLUMN IF EXISTS is_nego,
    DROP COLUMN IF EXISTS approve_by,
    DROP COLUMN IF EXISTS qty_delivered;
