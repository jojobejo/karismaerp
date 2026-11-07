-- Menambahkan status trip setelah loading selesai dan sebelum faktur/DO terbentuk.
ALTER TABLE `tb_delivery_trip`
    MODIFY COLUMN `status` ENUM(
        'DRAFT',
        'VERIFIKASI',
        'SIAP_LOADING',
        'PROSES_LOADING',
        'PROSES_FAKTUR',
        'MENUNGGU_TAMBAHAN',
        'PROSES_TAMBAHAN',
        'DITUTUP',
        'BERANGKAT',
        'SELESAI'
    ) NOT NULL DEFAULT 'DRAFT';

-- Memperbaiki trip lama yang aktivitas loading terakhirnya sudah selesai.
UPDATE `tb_delivery_trip` AS `trip`
SET `trip`.`status` = 'PROSES_FAKTUR'
WHERE `trip`.`status` = 'PROSES_LOADING'
  AND (
      EXISTS (
          SELECT 1
          FROM `tb_loading_lk` AS `lk`
          WHERE `lk`.`id_trip` = `trip`.`id_trip`
            AND `lk`.`status` = 'DONE'
      )
      OR EXISTS (
          SELECT 1
          FROM `tb_loading_kk` AS `kk`
          WHERE `kk`.`id_trip` = `trip`.`id_trip`
            AND `kk`.`status` = 'DONE'
      )
  );
