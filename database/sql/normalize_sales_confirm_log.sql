-- Normalisasi log konfirmasi sales.
-- Jalankan setelah kode aplikasi sudah memakai tb_log_confirm_sales sebagai sumber log.

INSERT INTO tb_log_confirm_sales (kd_do, action, note, confirm_by, confirm_at)
SELECT
    d.kd_do,
    d.sales_confirm_status,
    d.sales_confirm_note,
    d.sales_confirm_by,
    COALESCE(d.sales_confirm_at, d.tgl_create, NOW())
FROM tb_do d
WHERE d.sales_confirm_status IN ('siap', 'belum_siap')
  AND NOT EXISTS (
      SELECT 1
      FROM tb_log_confirm_sales l
      WHERE l.kd_do = d.kd_do
        AND l.action = d.sales_confirm_status
        AND COALESCE(l.note, '') = COALESCE(d.sales_confirm_note, '')
        AND COALESCE(l.confirm_by, '') = COALESCE(d.sales_confirm_by, '')
        AND l.confirm_at = COALESCE(d.sales_confirm_at, d.tgl_create, NOW())
  );

ALTER TABLE tb_log_confirm_sales
    ADD INDEX idx_log_confirm_sales_kd_do_confirm_at (kd_do, confirm_at, id);

ALTER TABLE tb_do
    DROP COLUMN sales_confirm_status,
    DROP COLUMN sales_confirm_by,
    DROP COLUMN sales_confirm_at,
    DROP COLUMN sales_confirm_note;
