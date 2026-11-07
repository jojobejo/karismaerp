-- Migrasi fasilitas per user dan kontrol nominal LPB
-- Tanggal: 2026-08-31
-- Catatan: Jalankan pada database target sebelum deploy fitur master/user-facility.

CREATE TABLE IF NOT EXISTS `tb_user_facility` (
  `id_user_facility` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `facility_key` VARCHAR(120) NOT NULL,
  `facility_label` VARCHAR(180) NOT NULL,
  `module_key` VARCHAR(80) NOT NULL DEFAULT 'general',
  `facility_group` VARCHAR(80) NOT NULL DEFAULT 'Umum',
  `is_allowed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id_user_facility`),
  UNIQUE KEY `uniq_user_facility` (`user_id`, `facility_key`),
  KEY `idx_facility_key` (`facility_key`),
  KEY `idx_module_key` (`module_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tb_menu` (`nama_menu`, `url_menu`, `icon_menu`, `urutan`, `is_active`)
SELECT 'Fasilitas Per User', 'master/user-facility', 'fas fa-user-lock', 295, 1
WHERE EXISTS (
  SELECT 1
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tb_menu'
    AND COLUMN_NAME = 'url_menu'
)
AND NOT EXISTS (
  SELECT 1
  FROM `tb_menu`
  WHERE `url_menu` = 'master/user-facility'
);

SET @menu_user_facility_id := (
  SELECT `id`
  FROM `tb_menu`
  WHERE `url_menu` = 'master/user-facility'
  ORDER BY `id` DESC
  LIMIT 1
);

INSERT INTO `tb_akses_menu` (
  `akses_lv_id`,
  `id_menu`,
  `menu_id`,
  `can_view`,
  `can_add`,
  `can_edit`,
  `can_delete`,
  `can_approve`,
  `can_print`
)
SELECT
  al.`id`,
  @menu_user_facility_id,
  @menu_user_facility_id,
  1,
  1,
  1,
  0,
  1,
  1
FROM `tb_akses_level` al
WHERE @menu_user_facility_id IS NOT NULL
  AND al.`kode_level` IN ('SUPERADMIN', 'ADMIN')
  AND NOT EXISTS (
    SELECT 1
    FROM `tb_akses_menu` am
    WHERE am.`akses_lv_id` = al.`id`
      AND (am.`id_menu` = @menu_user_facility_id OR am.`menu_id` = @menu_user_facility_id)
  );
