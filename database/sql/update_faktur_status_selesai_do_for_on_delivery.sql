-- Samakan status faktur untuk DO yang sudah On Delivery.
-- Jalankan sekali untuk data lama yang sebelumnya masih berstatus in_progress/proses_do.

UPDATE tbso_faktur_penjualan f
JOIN (
    SELECT DISTINCT d.kd_faktur
    FROM tb_detail_do d
    JOIN tb_do h ON h.kd_do = d.kd_do
    WHERE h.status = '5'
) od ON od.kd_faktur = f.no_faktur
SET f.status = 'selesai_do'
WHERE f.status <> 'selesai_do';
