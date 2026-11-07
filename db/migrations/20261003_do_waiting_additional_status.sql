-- Status 6: DO masih menunggu proses tambahan muatan pada trip yang sama.
UPDATE tb_do d
JOIN tb_delivery_trip t ON t.id_trip = d.id_trip
SET d.status = 6
WHERE d.status = 5
  AND t.status IN ('MENUNGGU_TAMBAHAN', 'PROSES_TAMBAHAN');
