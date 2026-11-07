-- Gabungkan DO tambahan yang terlanjur terbentuk ke DO pertama pada trip yang sama.
DROP TEMPORARY TABLE IF EXISTS tmp_merge_delivery_order;
CREATE TEMPORARY TABLE tmp_merge_delivery_order AS
SELECT tambahan.kd_do AS kd_do_lama, utama.kd_do AS kd_do_utama
FROM tb_do tambahan
JOIN (
    SELECT d.id_trip, MIN(d.id) id_utama
    FROM tb_do d
    WHERE d.id_trip IS NOT NULL AND d.status = 5
    GROUP BY d.id_trip
    HAVING COUNT(*) > 1
) grup ON grup.id_trip = tambahan.id_trip AND tambahan.id <> grup.id_utama
JOIN tb_do utama ON utama.id = grup.id_utama;

UPDATE tb_detail_do d JOIN tmp_merge_delivery_order m ON m.kd_do_lama = d.kd_do SET d.kd_do = m.kd_do_utama;
UPDATE tb_log_confirm_sales d JOIN tmp_merge_delivery_order m ON m.kd_do_lama = d.kd_do SET d.kd_do = m.kd_do_utama;
UPDATE tb_log_do d JOIN tmp_merge_delivery_order m ON m.kd_do_lama = d.kd_do SET d.kd_do = m.kd_do_utama;
UPDATE tb_ics_do d JOIN tmp_merge_delivery_order m ON m.kd_do_lama = d.kd_do SET d.kd_do = m.kd_do_utama;
UPDATE tb_pnd_do d JOIN tmp_merge_delivery_order m ON m.kd_do_lama = d.kd_do SET d.kd_do = m.kd_do_utama;
UPDATE tb_tmp_detaildo d JOIN tmp_merge_delivery_order m ON m.kd_do_lama = d.kd_do SET d.kd_do = m.kd_do_utama;
UPDATE stockopname_pending d JOIN tmp_merge_delivery_order m ON m.kd_do_lama = d.kd_do SET d.kd_do = m.kd_do_utama;
UPDATE tb_detail_do d
JOIN (
    SELECT kd_do, kd_faktur,
           DENSE_RANK() OVER (PARTITION BY kd_do ORDER BY MIN(create_at), kd_faktur) AS urutan_baru
    FROM tb_detail_do
    GROUP BY kd_do, kd_faktur
) urut ON urut.kd_do = d.kd_do AND urut.kd_faktur = d.kd_faktur
SET d.norut = urut.urutan_baru;
DELETE d FROM tb_tmp_do d JOIN tmp_merge_delivery_order m ON m.kd_do_lama = d.kd_do;
DELETE d FROM tb_do d JOIN tmp_merge_delivery_order m ON m.kd_do_lama = d.kd_do;
DROP TEMPORARY TABLE tmp_merge_delivery_order;
