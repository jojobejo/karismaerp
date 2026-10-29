-- Salin kolom autentikasi/identitas terpilih dari database aplikasi sumber.
-- Sumber mengikuti nilai database awal di application/config/database.php.
-- Jalankan hanya setelah memeriksa nama database sumber dan membuat backup.
-- Skrip ini mengubah data tujuan saja; tidak mengubah database sumber.
-- Periksa juga duplikasi ID/username sebelum menggunakannya di lingkungan aktif.

INSERT INTO `portalerp`.`portalerp_tb_karyawan`
  (`id`, `nik`, `nm_karyawan`, `departemen`, `jobdesk`, `username`, `password`,
   `tim`, `wilayah`, `jobdesk_id`, `akses_lv_id`, `status`, `last_login`, `akses_lv`)
SELECT
  `id`, `nik`, `nm_karyawan`, `departemen`, `jobdesk`, `username`, `password`,
  `tim`, `wilayah`, `jobdesk_id`, `akses_lv_id`, `status`, `last_login`, `akses_lv`
FROM `kiucoid_karismaerp`.`tb_karyawan`
ON DUPLICATE KEY UPDATE
  `nik` = VALUES(`nik`), `nm_karyawan` = VALUES(`nm_karyawan`),
  `departemen` = VALUES(`departemen`), `jobdesk` = VALUES(`jobdesk`),
  `username` = VALUES(`username`), `password` = VALUES(`password`),
  `tim` = VALUES(`tim`), `wilayah` = VALUES(`wilayah`),
  `jobdesk_id` = VALUES(`jobdesk_id`), `akses_lv_id` = VALUES(`akses_lv_id`),
  `status` = VALUES(`status`), `last_login` = VALUES(`last_login`),
  `akses_lv` = VALUES(`akses_lv`);

INSERT INTO `portalerp`.`portalerp_tb_users`
  (`id`, `username`, `password`, `level`, `jobdesk_hrd`, `nama_lngkp`, `nik`,
   `departement`, `jabatan`, `status`, `default_redirect`, `last_login`, `created_at`, `updated_at`)
SELECT
  `id`, `username`, `password`, `level`, `jobdesk_hrd`, `nama_lngkp`, `nik`,
  `departement`, `jabatan`, `status`, `default_redirect`, `last_login`, `created_at`, `updated_at`
FROM `kiucoid_karismaerp`.`tb_users`
ON DUPLICATE KEY UPDATE
  `username` = VALUES(`username`), `password` = VALUES(`password`),
  `level` = VALUES(`level`), `jobdesk_hrd` = VALUES(`jobdesk_hrd`),
  `nama_lngkp` = VALUES(`nama_lngkp`), `nik` = VALUES(`nik`),
  `departement` = VALUES(`departement`), `jabatan` = VALUES(`jabatan`),
  `status` = VALUES(`status`), `default_redirect` = VALUES(`default_redirect`),
  `last_login` = VALUES(`last_login`), `created_at` = VALUES(`created_at`),
  `updated_at` = VALUES(`updated_at`);
