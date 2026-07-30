-- Struktur dan data login direksi untuk readonly dashboard_penilaian.
-- Password login semua akun: direktur89
-- Password disimpan sebagai bcrypt agar tidak tersimpan plaintext.
-- Script ini aman dijalankan ulang di phpMyAdmin.

SET @password_direksi := '$2y$10$XT0mb3R82esehPYBtWWHWO0gsl4Hh5C9ZhBJ0J.Zd1o8VpmOiMQgq';
SET @now_direksi := NOW();

-- 1) Tambahan struktur tb_users jika kolom belum tersedia.
SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `password` varchar(255) NOT NULL DEFAULT '''' AFTER `username`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'password'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `level` int(11) NOT NULL DEFAULT 1 AFTER `password`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'level'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `jobdesk_hrd` varchar(100) DEFAULT NULL AFTER `level`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'jobdesk_hrd'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `nama_lngkp` varchar(255) NOT NULL DEFAULT '''' AFTER `jobdesk_hrd`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'nama_lngkp'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `nik` varchar(255) NOT NULL DEFAULT ''-'' AFTER `nama_lngkp`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'nik'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `bagian` varchar(255) NOT NULL DEFAULT '''' AFTER `nik`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'bagian'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `departement` varchar(255) NOT NULL DEFAULT '''' AFTER `bagian`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'departement'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `jabatan` varchar(255) NOT NULL DEFAULT '''' AFTER `departement`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'jabatan'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `atasan` varchar(255) NOT NULL DEFAULT '''' AFTER `jabatan`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'atasan'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `penilai` varchar(255) NOT NULL DEFAULT '''' AFTER `atasan`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'penilai'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 1 AFTER `penilai`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'status'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `default_redirect` varchar(180) DEFAULT NULL AFTER `status`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'default_redirect'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `last_login` datetime DEFAULT NULL AFTER `default_redirect`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'last_login'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `last_login`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'created_at'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'ALTER TABLE tb_users ADD COLUMN `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`', 'DO 0')
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'updated_at'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'CREATE INDEX idx_tb_users_username ON tb_users (`username`)', 'DO 0')
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'username'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'CREATE INDEX idx_tb_users_jobdesk_hrd ON tb_users (`jobdesk_hrd`)', 'DO 0')
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'jobdesk_hrd'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(COUNT(*) = 0, 'CREATE INDEX idx_tb_users_status ON tb_users (`status`)', 'DO 0')
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_users' AND COLUMN_NAME = 'status'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Update akun jika username sudah ada.
UPDATE tb_users
SET password = @password_direksi,
    level = 5,
    jobdesk_hrd = 'direksi_readonly',
    nama_lngkp = 'Direktur 1',
    nik = '-',
    bagian = 'Direksi',
    departement = 'Direksi',
    jabatan = 'Direksi',
    atasan = '-',
    penilai = '-',
    status = 1,
    default_redirect = 'dashboard_penilaian',
    updated_at = @now_direksi
WHERE username = 'direktur1';

UPDATE tb_users
SET password = @password_direksi,
    level = 5,
    jobdesk_hrd = 'direksi_readonly',
    nama_lngkp = 'Direktur 2',
    nik = '-',
    bagian = 'Direksi',
    departement = 'Direksi',
    jabatan = 'Direksi',
    atasan = '-',
    penilai = '-',
    status = 1,
    default_redirect = 'dashboard_penilaian',
    updated_at = @now_direksi
WHERE username = 'direktur2';

UPDATE tb_users
SET password = @password_direksi,
    level = 5,
    jobdesk_hrd = 'direksi_readonly',
    nama_lngkp = 'Direktur 3',
    nik = '-',
    bagian = 'Direksi',
    departement = 'Direksi',
    jabatan = 'Direksi',
    atasan = '-',
    penilai = '-',
    status = 1,
    default_redirect = 'dashboard_penilaian',
    updated_at = @now_direksi
WHERE username = 'direktur3';

-- 3) Insert akun jika username belum ada.
INSERT INTO tb_users
    (username, password, level, jobdesk_hrd, nama_lngkp, nik, bagian, departement, jabatan, atasan, penilai, status, default_redirect, created_at, updated_at)
SELECT
    'direktur1', @password_direksi, 5, 'direksi_readonly', 'Direktur 1', '-', 'Direksi', 'Direksi', 'Direksi', '-', '-', 1, 'dashboard_penilaian', @now_direksi, @now_direksi
WHERE NOT EXISTS (SELECT 1 FROM tb_users WHERE username = 'direktur1');

INSERT INTO tb_users
    (username, password, level, jobdesk_hrd, nama_lngkp, nik, bagian, departement, jabatan, atasan, penilai, status, default_redirect, created_at, updated_at)
SELECT
    'direktur2', @password_direksi, 5, 'direksi_readonly', 'Direktur 2', '-', 'Direksi', 'Direksi', 'Direksi', '-', '-', 1, 'dashboard_penilaian', @now_direksi, @now_direksi
WHERE NOT EXISTS (SELECT 1 FROM tb_users WHERE username = 'direktur2');

INSERT INTO tb_users
    (username, password, level, jobdesk_hrd, nama_lngkp, nik, bagian, departement, jabatan, atasan, penilai, status, default_redirect, created_at, updated_at)
SELECT
    'direktur3', @password_direksi, 5, 'direksi_readonly', 'Direktur 3', '-', 'Direksi', 'Direksi', 'Direksi', '-', '-', 1, 'dashboard_penilaian', @now_direksi, @now_direksi
WHERE NOT EXISTS (SELECT 1 FROM tb_users WHERE username = 'direktur3');

-- 4) Validasi hasil.
SELECT id, username, level, jobdesk_hrd, nama_lngkp, jabatan, status, default_redirect
FROM tb_users
WHERE username IN ('direktur1', 'direktur2', 'direktur3')
ORDER BY username;
