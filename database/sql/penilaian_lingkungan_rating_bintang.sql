-- Migration: Tabel nilai bintang untuk modul penilaian lingkungan.
-- Jalankan pada database Karisma ERP sebelum fitur bintang dipakai di produksi.
-- Penilaian Lingkungan disimpan di tbhrd_nilai_lingkungan.
-- Laporan Issue tetap disimpan di tbhrd_environment_issues.

CREATE TABLE IF NOT EXISTS `tbhrd_nilai_lingkungan` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `rating_id` int(11) NOT NULL,
  `star_rating` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `report_datetime` datetime NOT NULL,
  `due_date` date DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tbhrd_nilai_lingkungan_location` (`location_id`),
  KEY `idx_tbhrd_nilai_lingkungan_rating` (`rating_id`),
  KEY `idx_tbhrd_nilai_lingkungan_status` (`status_id`),
  KEY `idx_tbhrd_nilai_lingkungan_star` (`star_rating`),
  KEY `idx_tbhrd_nilai_lingkungan_report_datetime` (`report_datetime`),
  CONSTRAINT `tbhrd_nilai_lingkungan_fk_location` FOREIGN KEY (`location_id`) REFERENCES `tbhrd_lokasi` (`id`),
  CONSTRAINT `tbhrd_nilai_lingkungan_fk_rating` FOREIGN KEY (`rating_id`) REFERENCES `tbhrd_issue_rating` (`id`),
  CONSTRAINT `tbhrd_nilai_lingkungan_fk_status` FOREIGN KEY (`status_id`) REFERENCES `tbhrd_issue_status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbhrd_issue_rating` (`id`, `name`, `score`) VALUES
(1, 'Nilai 1', 1),
(2, 'Nilai 2', 2),
(3, 'Nilai 3', 3),
(4, 'Nilai 4', 4),
(5, 'Nilai 5', 5)
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `score` = VALUES(`score`);

UPDATE `tbhrd_environment_issues`
SET `rating_id` = 5
WHERE `rating_id` IS NULL OR `rating_id` = 0;
