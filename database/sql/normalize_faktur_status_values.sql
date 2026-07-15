-- Normalisasi status faktur penjualan.
-- Status lama in_progress, selesai, dan completed tidak dipakai lagi.

UPDATE tbso_faktur_penjualan
SET status = 'selesai_do'
WHERE status IN ('in_progress', 'selesai', 'completed');

ALTER TABLE tbso_faktur_penjualan
MODIFY COLUMN status ENUM('draft', 'confirmed', 'proses_do', 'selesai_do', 'cancelled') NOT NULL DEFAULT 'draft';
