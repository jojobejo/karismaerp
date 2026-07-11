-- Migration: Master rating 5 bintang untuk modul penilaian lingkungan.
-- Jalankan pada database Karisma ERP sebelum fitur bintang dipakai di produksi.

START TRANSACTION;

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

COMMIT;
