ALTER TABLE tbkeu_pembayaran_faktur
ADD COLUMN IF NOT EXISTS tanggal_bg_cair DATE NULL AFTER metode_pembayaran,
ADD COLUMN IF NOT EXISTS status_bg VARCHAR(20) NOT NULL DEFAULT 'not_bg' AFTER tanggal_bg_cair,
ADD COLUMN IF NOT EXISTS bg_cair_by VARCHAR(100) NULL AFTER status_bg,
ADD COLUMN IF NOT EXISTS bg_cair_at DATETIME NULL AFTER bg_cair_by;

UPDATE tbkeu_pembayaran_faktur
SET status_bg = CASE
    WHEN LOWER(COALESCE(metode_pembayaran, '')) = 'bg' THEN 'pending'
    ELSE 'not_bg'
END
WHERE status_bg IS NULL OR status_bg = '';
