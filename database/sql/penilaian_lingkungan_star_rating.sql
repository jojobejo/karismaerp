-- Migration: tambah nilai bintang modul penilaian lingkungan
-- Tanggal: 2026-07-11

ALTER TABLE `tbhrd_environment_issues`
    ADD COLUMN IF NOT EXISTS `star_rating` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rating_id`;

ALTER TABLE `tbhrd_environment_issues`
    ADD INDEX IF NOT EXISTS `idx_tbhrd_environment_issues_star_rating` (`star_rating`);
