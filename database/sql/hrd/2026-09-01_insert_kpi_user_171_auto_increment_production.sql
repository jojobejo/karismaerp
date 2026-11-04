-- Production insert for KPI master data to existing tb_users.id 171.
-- Generated: 2026-09-01 11:16:43 Asia/Bangkok
-- Info: Tidak membuat user baru di tb_users. Data KPI langsung dikaitkan ke user id 171.

-- USE `kiucoid_kpi`; -- Aktifkan jika database target bernama kiucoid_kpi, atau biarkan non-aktif untuk menggunakan database aktif saat ini

DELIMITER $$

DROP PROCEDURE IF EXISTS insert_kpi_user_171_auto_increment_production $$
CREATE PROCEDURE insert_kpi_user_171_auto_increment_production()
BEGIN
    DECLARE v_source_user_id INT DEFAULT 171;
    DECLARE v_target_user_id INT DEFAULT 171;
    DECLARE v_check_count INT DEFAULT 0;
    DECLARE v_tb_kpi_808 INT;
    DECLARE v_tb_kpi_809 INT;
    DECLARE v_tb_kpi_810 INT;
    DECLARE v_tb_kpi_811 INT;
    DECLARE v_tb_kpi_812 INT;
    DECLARE v_tb_kpi_813 INT;
    DECLARE v_tbsim_kpi_1348 INT;
    DECLARE v_tbsim_kpi_1349 INT;
    DECLARE v_tbsim_kpi_1350 INT;
    DECLARE v_tbsim_kpi_1351 INT;
    DECLARE v_tbsim_kpi_1352 INT;
    DECLARE v_tbsim_kpi_1353 INT;
    DECLARE v_tb_whats_1232 INT;
    DECLARE v_tb_whats_1233 INT;
    DECLARE v_tb_whats_1234 INT;
    DECLARE v_tb_whats_1235 INT;
    DECLARE v_tb_whats_1237 INT;
    DECLARE v_tb_whats_1238 INT;
    DECLARE v_tb_whats_1239 INT;
    DECLARE v_tb_whats_1240 INT;
    DECLARE v_tb_whats_1241 INT;
    DECLARE v_tb_whats_1242 INT;
    DECLARE v_tb_whats_1243 INT;
    DECLARE v_tb_hows_2505 INT;
    DECLARE v_tb_hows_2506 INT;
    DECLARE v_tb_hows_2507 INT;
    DECLARE v_tb_hows_2508 INT;
    DECLARE v_tb_hows_2509 INT;
    DECLARE v_tb_hows_2510 INT;
    DECLARE v_tb_hows_2511 INT;
    DECLARE v_tb_hows_2512 INT;
    DECLARE v_tb_hows_2513 INT;
    DECLARE v_tb_hows_2514 INT;
    DECLARE v_tb_hows_2515 INT;
    DECLARE v_tb_hows_2516 INT;
    DECLARE v_tb_hows_2517 INT;
    DECLARE v_tb_hows_2519 INT;
    DECLARE v_tb_hows_2520 INT;
    DECLARE v_tb_hows_2521 INT;
    DECLARE v_tb_hows_2522 INT;
    DECLARE v_tb_hows_2523 INT;
    DECLARE v_tb_hows_2524 INT;
    DECLARE v_tbsim_whats_1998 INT;
    DECLARE v_tbsim_whats_1999 INT;
    DECLARE v_tbsim_whats_2000 INT;
    DECLARE v_tbsim_whats_2001 INT;
    DECLARE v_tbsim_whats_2002 INT;
    DECLARE v_tbsim_whats_2003 INT;
    DECLARE v_tbsim_whats_2004 INT;
    DECLARE v_tbsim_whats_2005 INT;
    DECLARE v_tbsim_whats_2006 INT;
    DECLARE v_tbsim_whats_2007 INT;
    DECLARE v_tbsim_whats_2008 INT;
    DECLARE v_tbsim_whats_2009 INT;
    DECLARE v_tbsim_hows_4013 INT;
    DECLARE v_tbsim_hows_4014 INT;
    DECLARE v_tbsim_hows_4015 INT;
    DECLARE v_tbsim_hows_4016 INT;
    DECLARE v_tbsim_hows_4017 INT;
    DECLARE v_tbsim_hows_4018 INT;
    DECLARE v_tbsim_hows_4019 INT;
    DECLARE v_tbsim_hows_4020 INT;
    DECLARE v_tbsim_hows_4021 INT;
    DECLARE v_tbsim_hows_4022 INT;
    DECLARE v_tbsim_hows_4023 INT;
    DECLARE v_tbsim_hows_4024 INT;
    DECLARE v_tbsim_hows_4025 INT;
    DECLARE v_tbsim_hows_4026 INT;
    DECLARE v_tbsim_hows_4027 INT;
    DECLARE v_tbsim_hows_4028 INT;
    DECLARE v_tbsim_hows_4029 INT;
    DECLARE v_tbsim_hows_4030 INT;
    DECLARE v_tbsim_hows_4031 INT;
    DECLARE v_tbsim_hows_4032 INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    -- Validasi: Pastikan user dengan id 171 ada di tb_users
    SELECT COUNT(*) INTO v_check_count
      FROM tb_users
     WHERE id = 171;

    IF v_check_count = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Guard gagal: user dengan id 171 tidak ditemukan di tb_users.';
    END IF;

    SET v_target_user_id = 171;

    START TRANSACTION;

    -- Bersihkan data KPI lama milik user 171 (jika ada) agar tidak terjadi duplikasi
    DELETE iw FROM tb_indikator_whats iw JOIN tb_whats w ON w.id_what = iw.id_what WHERE w.id_user = v_target_user_id;
    DELETE ih FROM tb_indikator_hows ih JOIN tb_hows h ON h.id_how = ih.id_how WHERE h.id_user = v_target_user_id;
    DELETE iw FROM tbsim_indikator_whats iw JOIN tbsim_whats w ON w.id_what = iw.id_what WHERE w.id_user = v_target_user_id;
    DELETE ih FROM tbsim_indikator_hows ih JOIN tbsim_hows h ON h.id_how = ih.id_how WHERE h.id_user = v_target_user_id;

    DELETE FROM tb_whats WHERE id_user = v_target_user_id;
    DELETE FROM tb_hows WHERE id_user = v_target_user_id;
    DELETE FROM tbsim_whats WHERE id_user = v_target_user_id;
    DELETE FROM tbsim_hows WHERE id_user = v_target_user_id;

    DELETE FROM tb_kpi WHERE id_user = v_target_user_id;
    DELETE FROM tbsim_kpi WHERE id_user = v_target_user_id;
    DELETE FROM tb_bobotkpi WHERE id_user = v_target_user_id;
    DELETE FROM tbsim_bobotkpi WHERE id_user = v_target_user_id;

    INSERT INTO `tb_bobotkpi` (`id_user`, `bobotwhat`, `bobothow`) VALUES
    (v_target_user_id, '60', '40');
    INSERT INTO `tbsim_bobotkpi` (`id_user`, `bobotwhat`, `bobothow`) VALUES
    (v_target_user_id, '60', '40');

    INSERT INTO `tb_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Pembuatan dan Pengembangan KARISMA HRIS', '40', 'Pembuatan dan Pengembangan KARISMA HRIS', '40');
    SET v_tb_kpi_808 = LAST_INSERT_ID();
    INSERT INTO `tb_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Pengembangan & Support KarismaERP', '20', 'Pengembangan & Support KarismaERP', '20');
    SET v_tb_kpi_809 = LAST_INSERT_ID();
    INSERT INTO `tb_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Stabilitas & Performa Aplikasi', '15', 'Stabilitas & Performa Aplikasi', '15');
    SET v_tb_kpi_810 = LAST_INSERT_ID();
    INSERT INTO `tb_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Pemeliharaan Sistem', '10', 'Pemeliharaan Sistem', '10');
    SET v_tb_kpi_811 = LAST_INSERT_ID();
    INSERT INTO `tb_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Kehadiran & Kedisiplinan Kerja', '10', 'Penilaian absensi oleh HRD', '10');
    SET v_tb_kpi_812 = LAST_INSERT_ID();
    INSERT INTO `tb_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Maintenance Hardware', '5', 'Membantu Maintenance Hardware', '5');
    SET v_tb_kpi_813 = LAST_INSERT_ID();

    INSERT INTO `tbsim_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Pembuatan dan Pengembangan KARISMA HRIS', '40', 'Pembuatan dan Pengembangan KARISMA HRIS', '40');
    SET v_tbsim_kpi_1348 = LAST_INSERT_ID();
    INSERT INTO `tbsim_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Pengembangan & Support KarismaERP', '20', 'Pengembangan & Support KarismaERP', '20');
    SET v_tbsim_kpi_1349 = LAST_INSERT_ID();
    INSERT INTO `tbsim_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Stabilitas & Performa Aplikasi', '15', 'Stabilitas & Performa Aplikasi', '15');
    SET v_tbsim_kpi_1350 = LAST_INSERT_ID();
    INSERT INTO `tbsim_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Pemeliharaan Sistem', '10', 'Pemeliharaan Sistem', '10');
    SET v_tbsim_kpi_1351 = LAST_INSERT_ID();
    INSERT INTO `tbsim_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Kehadiran & Kedisiplinan Kerja', '10', 'Penilaian absensi oleh HRD', '10');
    SET v_tbsim_kpi_1352 = LAST_INSERT_ID();
    INSERT INTO `tbsim_kpi` (`id_user`, `poin`, `bobot`, `poin2`, `bobot2`) VALUES
    (v_target_user_id, 'Bantuan Perbaikan Perangkat Kerja', '5', 'Bantuan Perbaikan Perangkat Kerja', '5');
    SET v_tbsim_kpi_1353 = LAST_INSERT_ID();

    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_808, 'A', 'Penyelesaian KARISMA HRIS', '60', '0.00', 'Belum menghasilkan hasil yang dapat digunakan', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1232 = LAST_INSERT_ID();
    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_808, 'A', 'Kesiapan KARISMA HRIS digunakan', '30', '0.00', 'Aplikasi tidak dapat digunakan', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1233 = LAST_INSERT_ID();
    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_808, 'A', 'Dokumentasi dan Panduan KARISMA HRIS', '10', '0.00', 'Tidak ada dokumentasi', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1234 = LAST_INSERT_ID();
    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_809, 'A', 'Pengembangan dan Penyempurnaan KarismaERP', '65', '0.00', 'Tidak terdapat hasil pekerjaan', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1235 = LAST_INSERT_ID();
    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_809, 'A', 'Catatan Perubahan KarismaERP', '10', '0.00', 'Tidak membuat laporan', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1237 = LAST_INSERT_ID();
    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_810, 'A', 'Menjaga Aplikasi Tetap Berjalan Dengan Baik', '80', '0.00', 'Aplikasi tidak dapat digunakan dengan baik', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1238 = LAST_INSERT_ID();
    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_810, 'A', 'Laporan Kondisi Aplikasi', '20', '0.00', 'Tidak membuat laporan', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1239 = LAST_INSERT_ID();
    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_811, 'A', 'Pemeliharaan Berkala', '80', '0.00', 'Tidak ada pemeliharaan', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1240 = LAST_INSERT_ID();
    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_811, 'A', 'Laporan Pemeliharaan Sistem', '20', '0.00', 'Tidak membuat laporan', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1241 = LAST_INSERT_ID();
    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_812, 'A', 'Absensi', '100', '0.00', 'Cuti 0 hr, Absen 0 hr', '115', '115', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1242 = LAST_INSERT_ID();
    INSERT INTO `tb_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_what`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_813, 'A', 'Bantuan Perbaikan Perangkat Kerja', '100', '0.00', 'Nilai skill 3', '90', '90', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_whats_1243 = LAST_INSERT_ID();

    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_808, 'A', 'Menjalankan pembuatan KARISMA HRIS sesuai urutan pekerjaan, target waktu dan kebutuhan yang telah disepakati.', '40', '0.00', 'Tidak terdapat perkembangan pekerjaan yang terukur', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2505 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_808, 'A', 'Melakukan pengecekan setiap bagian aplikasi sebelum digunakan dan segera memperbaiki apabila ditemukan masalah.', '30', '0.00', 'Tidak dilakukan pengecekan', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2506 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_808, 'A', 'Memastikan setiap bagian KARISMA HRIS saling mendukung dan data yang digunakan sesuai kebutuhan perusahaan.', '20', '0.00', 'Tidak ada integrasi atau kesesuaian data yang dapat digunakan', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2507 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_808, 'A', 'Membuat catatan pekerjaan, panduan penggunaan dan laporan perkembangan KARISMA HRIS.', '10', '0.00', 'Tidak membuat catatan atau dokumentasi', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2508 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_809, 'A', 'Memahami kebutuhan pengguna sebelum melakukan perubahan atau penambahan KarismaERP.', '30', '0.00', 'Tidak melakukan analisis kebutuhan pengguna', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2509 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_809, 'A', 'Membuat, memeriksa dan memastikan hasil penyempurnaan KarismaERP dapat digunakan dengan baik.', '40', '0.00', 'Tidak ada hasil pekerjaan yang dapat digunakan', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2510 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_809, 'A', 'Menangani laporan masalah KarismaERP sampai dapat digunakan kembali.', '20', '0.00', 'Masalah tidak ditangani', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2511 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_809, 'A', 'Mencatat perubahan dan hasil pekerjaan KarismaERP.', '10', '0.00', 'Tidak membuat catatan atau dokumentasi', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2512 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_810, 'A', 'Melakukan pengecekan aplikasi secara rutin dan segera melakukan perbaikan apabila ditemukan masalah.', '50', '0.00', 'Tidak melakukan pengecekan aplikasi', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2513 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_810, 'A', 'Melakukan backup data aplikasi secara rutin 1 bulan sekali dan dilaporkan kepada atasan dan kader', '30', '0.00', 'Belum dinilai', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2514 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_810, 'A', 'Melaporkan performa aplikasi yang berjalan dan penanganan aplikasi yang sudah dilakukan perbaikan/perawatan kepada atasan', '20', '0.00', 'Belum dinilai', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2515 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_811, 'A', 'Menyelesaikan pekerjaan troubleshooting cepat dan tepat waktu Max H+1', '70', '0.00', 'Belum dinilai', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2516 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_811, 'A', 'Membuat laporan troubleshooting kepada atasan Max H+1', '30', '0.00', 'Belum dinilai', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2517 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_812, 'A', 'Menjalankan SOP Ijin tidak masuk', '25', '0.00', '100% taat ', '115', '28.75', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2519 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_812, 'A', 'Tidak pernah absen briefing', '25', '0.00', '100% hadir', '115', '28.75', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2520 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_812, 'A', 'Tidak pernah absen senam Sabtu', '25', '0.00', '100% hadir', '115', '28.75', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2521 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_812, 'A', 'Hadir briefing Tepat waktu', '25', '0.00', '100% Tepat Waktu', '115', '28.75', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2522 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_813, 'A', 'Perbaikan maintenance tanpa kesalahan', '50', '0.00', 'Nilai skill 3,5', '100', '50', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2523 = LAST_INSERT_ID();
    INSERT INTO `tb_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`, `is_edited`, `edited_by`, `edited_at`, `original_p_how`, `original_bobot`, `original_hasil`, `original_nilai`, `original_total`, `original_target_omset`) VALUES
    (v_target_user_id, v_tb_kpi_813, 'A', 'Penilaian pekerjaan hardware', '50', '0.00', 'Nilai skill 3,5', '100', '50', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
    SET v_tb_hows_2524 = LAST_INSERT_ID();

    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1348, 'A', 'Penyelesaian KARISMA HRIS', '55', '0.00', 'Belum menghasilkan hasil yang dapat digunakan', '0', '0');
    SET v_tbsim_whats_1998 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1348, 'A', 'Kesiapan KARISMA HRIS digunakan', '30', '0.00', 'Aplikasi tidak dapat digunakan', '0', '0');
    SET v_tbsim_whats_1999 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1348, 'A', 'Dokumentasi dan Panduan KARISMA HRIS', '15', '0.00', 'Tidak ada dokumentasi', '0', '0');
    SET v_tbsim_whats_2000 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1349, 'A', 'Pengembangan dan Penyempurnaan KarismaERP', '65', '0.00', '60-79% selesai', '60', '39');
    SET v_tbsim_whats_2001 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1349, 'A', 'Penanganan Masalah KarismaERP', '25', '0.00', 'Beberapa pekerjaan terlambat', '80', '20');
    SET v_tbsim_whats_2002 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1349, 'A', 'Catatan Perubahan KarismaERP', '10', '0.00', 'Laporan kurang lengkap atau sering terlambat', '60', '6');
    SET v_tbsim_whats_2003 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1350, 'A', 'Menjaga Aplikasi Tetap Berjalan Dengan Baik', '70', '0.00', 'Terdapat gangguan kecil tetapi tidak menghambat pekerjaan', '90', '63');
    SET v_tbsim_whats_2004 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1350, 'A', 'Laporan Kondisi Aplikasi', '30', '0.00', 'Laporan lengkap dan selesai lebih cepat dari jadwal', '110', '33');
    SET v_tbsim_whats_2005 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1351, 'A', 'Pemeliharaan Berkala', '70', '0.00', '90-94% selesai', '90', '63');
    SET v_tbsim_whats_2006 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1351, 'A', 'Laporan Pemeliharaan Sistem', '30', '0.00', 'Laporan hampir lengkap dan hanya ada kekurangan kecil', '90', '27');
    SET v_tbsim_whats_2007 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1352, 'A', 'Kehadiran & Kedisiplinan Kerja', '100', '0.00', 'Kehadiran, ketepatan waktu, briefing, izin/cuti, dan kegiatan wajib terlaksana sangat baik tanpa pelanggaran', '115', '115');
    SET v_tbsim_whats_2008 = LAST_INSERT_ID();
    INSERT INTO `tbsim_whats` (`id_user`, `id_kpi`, `tipe_what`, `p_what`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1353, 'A', 'Bantuan Perbaikan Perangkat Kerja', '100', '0.00', 'Seluruh pekerjaan selesai sangat baik dan masalah yang sama tidak berulang', '115', '115');
    SET v_tbsim_whats_2009 = LAST_INSERT_ID();

    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1348, 'A', 'Menjalankan pembuatan KARISMA HRIS sesuai urutan pekerjaan, target waktu dan kebutuhan yang telah disepakati.', '40', '0.00', 'Tidak terdapat perkembangan pekerjaan yang terukur', '0', '0');
    SET v_tbsim_hows_4013 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1348, 'A', 'Melakukan pengecekan setiap bagian aplikasi sebelum digunakan dan segera memperbaiki apabila ditemukan masalah.', '30', '0.00', 'Tidak dilakukan pengecekan', '0', '0');
    SET v_tbsim_hows_4014 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1348, 'A', 'Memastikan setiap bagian KARISMA HRIS saling mendukung dan data yang digunakan sesuai kebutuhan perusahaan.', '20', '0.00', 'Tidak ada integrasi atau kesesuaian data yang dapat digunakan', '0', '0');
    SET v_tbsim_hows_4015 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1348, 'A', 'Membuat catatan pekerjaan, panduan penggunaan dan laporan perkembangan KARISMA HRIS.', '10', '0.00', 'Tidak membuat catatan atau dokumentasi', '0', '0');
    SET v_tbsim_hows_4016 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1349, 'A', 'Memahami kebutuhan pengguna sebelum melakukan perubahan atau penambahan KarismaERP.', '30', '0.00', 'Analisis kebutuhan kurang lengkap', '60', '18');
    SET v_tbsim_hows_4017 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1349, 'A', 'Membuat, memeriksa dan memastikan hasil penyempurnaan KarismaERP dapat digunakan dengan baik.', '40', '0.00', 'Pekerjaan kurang stabil atau sering perlu perbaikan ulang', '60', '24');
    SET v_tbsim_hows_4018 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1349, 'A', 'Menangani laporan masalah KarismaERP sampai dapat digunakan kembali.', '20', '0.00', 'Penanganan masalah sering terlambat atau kurang tepat', '40', '8');
    SET v_tbsim_hows_4019 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1349, 'A', 'Mencatat perubahan dan hasil pekerjaan KarismaERP.', '10', '0.00', 'Dokumentasi sangat kurang dan sulit digunakan', '40', '4');
    SET v_tbsim_hows_4020 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1350, 'A', 'Melakukan pengecekan aplikasi secara rutin dan segera melakukan perbaikan apabila ditemukan masalah.', '50', '0.00', 'Pengecekan dilakukan tetapi beberapa bagian terlewat', '80', '40');
    SET v_tbsim_hows_4021 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1350, 'A', 'Melakukan perbaikan terhadap bagian aplikasi yang lambat atau mengganggu pekerjaan pengguna.', '30', '0.00', 'Perbaikan kurang efektif dan masalah cukup sering berulang', '60', '18');
    SET v_tbsim_hows_4022 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1350, 'A', 'Mencatat masalah, penyebab dan tindakan perbaikan agar masalah yang sama tidak sering terjadi kembali.', '20', '0.00', 'Dokumentasi sangat kurang dan sulit digunakan', '40', '8');
    SET v_tbsim_hows_4023 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1351, 'A', 'Melakukan pengecekan aplikasi dan data sesuai jadwal.', '50', '0.00', 'Pengecekan tidak konsisten', '60', '30');
    SET v_tbsim_hows_4024 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1351, 'A', 'Memastikan data penting memiliki salinan cadangan dan dapat digunakan apabila dibutuhkan.', '30', '0.00', 'Backup lengkap dan verifikasi selesai lebih cepat dari jadwal', '110', '33');
    SET v_tbsim_hows_4025 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1351, 'A', 'Mencatat pekerjaan pemeliharaan, masalah yang ditemukan dan hasil perbaikannya.', '20', '0.00', 'Dokumentasi cukup lengkap tetapi perlu beberapa perbaikan', '80', '16');
    SET v_tbsim_hows_4026 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1352, 'A', 'Menjalankan SOP Ijin tidak masuk', '25', '0.00', 'Kehadiran dan ketepatan waktu sangat baik tanpa pelanggaran', '115', '57.5');
    SET v_tbsim_hows_4027 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1352, 'A', 'Mengikuti briefing', '20', '0.00', 'Selalu mengikuti briefing dengan disiplin dan aktif mendukung kelancaran informasi', '115', '23');
    SET v_tbsim_hows_4028 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1352, 'A', 'Menjalankan prosedur izin/cuti', '20', '0.00', 'Seluruh izin/cuti mengikuti prosedur dengan lengkap, cepat, dan terdokumentasi', '115', '23');
    SET v_tbsim_hows_4029 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1352, 'A', 'Mengikuti kegiatan perusahaan yang diwajibkan', '10', '0.00', 'Seluruh kegiatan wajib diikuti sangat baik dan aktif mendukung pelaksanaan kegiatan', '115', '11.5');
    SET v_tbsim_hows_4030 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1353, 'A', 'Menangani laporan masalah perangkat secara cepat dan tepat.', '60', '0.00', 'Hampir seluruh laporan perangkat selesai lebih cepat dari target', '110', '66');
    SET v_tbsim_hows_4031 = LAST_INSERT_ID();
    INSERT INTO `tbsim_hows` (`id_user`, `id_kpi`, `tipe_how`, `p_how`, `bobot`, `target_omset`, `hasil`, `nilai`, `total`) VALUES
    (v_target_user_id, v_tbsim_kpi_1353, 'A', 'Memastikan perangkat dapat digunakan kembali dan mencatat pekerjaan perbaikan yang telah dilakukan.', '40', '0.00', 'Perangkat kembali digunakan lebih cepat dan dokumentasi lengkap', '110', '44');
    SET v_tbsim_hows_4032 = LAST_INSERT_ID();

    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1232, 'Seluruh target selesai dan terdapat tambahan pengembangan yang bermanfaat', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1232, 'Seluruh target selesai lebih cepat atau hasil melebihi target', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1232, 'Seluruh target yang ditetapkan selesai sesuai waktu', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1232, 'Penyelesaian mencapai 90-99%', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1232, 'Penyelesaian mencapai 80-89%', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1232, 'Penyelesaian mencapai 60-79%', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1232, 'Penyelesaian mencapai 40-59%', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1232, 'Belum menghasilkan hasil yang dapat digunakan', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1233, 'Aplikasi berjalan sangat baik, tidak ada masalah utama dan terdapat peningkatan tambahan', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1233, 'Aplikasi berjalan baik dan hanya terdapat masalah kecil', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1233, 'Aplikasi dapat digunakan sesuai kebutuhan tanpa masalah yang menghambat pekerjaan', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1233, 'Masih terdapat beberapa bagian yang perlu diperbaiki', '80.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1233, 'Aplikasi dapat digunakan tetapi masih sering mengalami masalah', '60.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1233, 'Aplikasi belum siap digunakan secara penuh', '40.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1233, 'Aplikasi tidak dapat digunakan', '0.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1234, 'Dokumentasi lengkap, panduan tersedia dan sudah dilakukan penjelasan kepada pengguna', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1234, 'Dokumentasi dan panduan 100% lengkap', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1234, 'Seluruh fungsi utama sudah memiliki dokumentasi', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1234, 'Dokumentasi mencapai 90%', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1234, 'Dokumentasi mencapai 80%', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1234, 'Dokumentasi mencapai 60%', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1234, 'Dokumentasi kurang dari 60%', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1234, 'Tidak ada dokumentasi', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1235, '>3 Modul', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1235, '3 Modul', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1235, '2 Modul', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1235, '1 Modul', '80.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1235, 'Tidak terdapat hasil pekerjaan', '0.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1237, 'Laporan sangat lengkap, tepat waktu, dan berisi tindak lanjut yang jelas', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1237, 'Laporan lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1237, 'Laporan lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1237, 'Laporan hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1237, 'Laporan cukup lengkap tetapi masih perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1237, 'Laporan kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1237, 'Laporan sangat kurang dan sulit digunakan sebagai acuan', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1237, 'Tidak membuat laporan', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1238, 'Tidak ada gangguan utama dan terdapat peningkatan kualitas aplikasi', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1238, 'Tidak ada gangguan utama selama periode penilaian', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1238, 'Maksimal terdapat 1 gangguan dan dapat segera diselesaikan', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1238, 'Terdapat gangguan kecil tetapi tidak menghambat pekerjaan', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1238, 'Terdapat beberapa gangguan', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1238, 'Gangguan cukup sering terjadi', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1238, 'Gangguan sering menghambat pekerjaan', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1238, 'Aplikasi tidak dapat digunakan dengan baik', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1239, 'Laporan sangat lengkap, tepat waktu, dan berisi tindak lanjut yang jelas', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1239, 'Laporan lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1239, 'Laporan lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1239, 'Laporan hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1239, 'Laporan cukup lengkap tetapi masih perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1239, 'Laporan kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1239, 'Laporan sangat kurang dan sulit digunakan sebagai acuan', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1239, 'Tidak membuat laporan', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1240, 'Terjadwal & terdokumenasi & terbackup', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1240, 'Terjadwal & terdokumenasi', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1240, 'Terjadwal tapi tidak lengkap', '90.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1240, 'Tidak konsisten', '60.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1240, 'Tidak ada pemeliharaan', '0.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1241, 'Laporan sangat lengkap, tepat waktu, dan berisi tindak lanjut yang jelas', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1241, 'Laporan tepat waktu & lengkap', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1241, 'Laporan tepat waktu tapi kurang lengkap', '90.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1241, 'Laporan  terlambat lengkap', '80.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1241, 'Laporan  terlambat tidak lengkap', '60.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1241, 'Tidak membuat laporan', '0.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1242, 'Cuti 0 hr, Absen 0 hr', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1242, 'Absen 1 hari (Cuti/Izin/Sakit)', '111.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1242, 'Absen 2 hari (Cuti/Izin/Sakit)', '110.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1242, 'Absen 3 hari (Cuti/Izin/Sakit)', '109.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1242, 'Absen 4 hari (Cuti/Izin/Sakit)', '108.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1242, 'Absen 5 hari (Cuti/Izin/Sakit)', '107.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1242, 'Absen 6 hari (Cuti/Izin/Sakit)', '106.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1242, 'Absen 7 hari (Cuti/Izin/Sakit)', '105.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1242, 'Absen 8 hari (Cuti/Izin/Sakit)', '104.00', '9', '2026-08-27 12:45:14', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1242, 'Absen 9 hari (Cuti/Izin/Sakit)', '103.00', '10', '2026-08-27 12:45:14', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1243, 'Nilai skill 4', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1243, 'Nilai skill 3,5', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1243, 'Nilai skill 3', '90.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1243, 'Nilai skill <3', '80.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_whats_1243, 'Nilai skill <1', '0.00', '5', '2026-08-27 12:35:40', '0', NULL, NULL, NULL, NULL);

    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2505, 'Seluruh pekerjaan selesai lebih cepat dan terdapat tambahan hasil yang bermanfaat', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2505, 'Seluruh pekerjaan selesai tepat waktu tanpa pekerjaan tertunda', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2505, 'Minimal 95% pekerjaan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2505, '85-94% pekerjaan selesai sesuai jadwal', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2505, '75-84% pekerjaan selesai sesuai jadwal', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2505, '60-74% pekerjaan selesai sesuai jadwal', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2505, 'Kurang dari 60% pekerjaan selesai', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2505, 'Tidak terdapat perkembangan pekerjaan yang terukur', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2506, 'Pengecekan lengkap, tidak terdapat masalah utama dan terdapat peningkatan tambahan', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2506, 'Pengecekan lengkap dan hanya ditemukan masalah kecil', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2506, 'Seluruh bagian utama sudah diperiksa dan dapat digunakan', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2506, 'Terdapat masalah tetapi seluruhnya dapat diselesaikan tepat waktu', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2506, 'Masih terdapat beberapa perbaikan kecil', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2506, 'Pengecekan dilakukan tetapi masih terdapat masalah berulang', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2506, 'Pengecekan belum lengkap', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2506, 'Tidak dilakukan pengecekan', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2507, 'Seluruh bagian HRIS saling mendukung sangat baik dan data sesuai kebutuhan perusahaan', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2507, 'Integrasi data berjalan baik dan melebihi kebutuhan awal', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2507, 'Seluruh bagian utama HRIS saling mendukung dan data sesuai kebutuhan', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2507, 'Sebagian kecil data atau alur perlu penyesuaian tetapi tidak menghambat pekerjaan', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2507, 'Beberapa bagian HRIS masih perlu disesuaikan', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2507, 'Integrasi dilakukan tetapi masih sering terdapat masalah data atau alur', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2507, 'Integrasi belum lengkap dan banyak bagian belum saling mendukung', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2507, 'Tidak ada integrasi atau kesesuaian data yang dapat digunakan', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2508, 'Catatan pekerjaan, dokumentasi, dan laporan lengkap, tepat waktu, serta mudah ditindaklanjuti', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2508, 'Dokumentasi lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2508, 'Dokumentasi lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2508, 'Dokumentasi hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2508, 'Dokumentasi cukup lengkap tetapi perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2508, 'Dokumentasi kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2508, 'Dokumentasi sangat kurang dan sulit digunakan', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2508, 'Tidak membuat catatan atau dokumentasi', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2509, 'Kebutuhan pengguna dipahami sangat baik dan menghasilkan solusi yang lebih bermanfaat', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2509, 'Kebutuhan pengguna dipahami lengkap lebih cepat dari target', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2509, 'Kebutuhan pengguna dipahami sesuai kebutuhan pekerjaan', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2509, 'Kebutuhan pengguna hampir lengkap dipahami', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2509, 'Sebagian kebutuhan pengguna masih perlu diperjelas', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2509, 'Analisis kebutuhan kurang lengkap', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2509, 'Analisis kebutuhan sering tidak sesuai dengan masalah pengguna', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2509, 'Tidak melakukan analisis kebutuhan pengguna', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2510, 'Pekerjaan dibuat dan diperiksa sangat baik serta memberikan manfaat tambahan', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2510, 'Pekerjaan selesai lebih cepat dan hasil melebihi kebutuhan awal', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2510, 'Pekerjaan dibuat, diperiksa, dan dapat digunakan sesuai kebutuhan', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2510, 'Pekerjaan hampir sesuai dan hanya perlu perbaikan kecil', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2510, 'Pekerjaan dapat digunakan tetapi masih perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2510, 'Pekerjaan kurang stabil atau sering perlu perbaikan ulang', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2510, 'Pekerjaan belum dapat digunakan dengan baik', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2510, 'Tidak ada hasil pekerjaan yang dapat digunakan', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2511, 'Seluruh laporan masalah tertangani sangat cepat dan masalah tidak berulang', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2511, 'Hampir seluruh masalah selesai lebih cepat dari target', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2511, 'Seluruh masalah selesai sesuai target dan aplikasi dapat digunakan kembali', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2511, 'Sebagian kecil penyelesaian terlambat tetapi dampak dapat dikendalikan', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2511, 'Beberapa masalah terlambat diselesaikan', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2511, 'Banyak masalah melewati waktu penyelesaian', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2511, 'Penanganan masalah sering terlambat atau kurang tepat', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2511, 'Masalah tidak ditangani', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2512, 'Catatan pekerjaan, dokumentasi, dan laporan lengkap, tepat waktu, serta mudah ditindaklanjuti', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2512, 'Dokumentasi lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2512, 'Dokumentasi lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2512, 'Dokumentasi hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2512, 'Dokumentasi cukup lengkap tetapi perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2512, 'Dokumentasi kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2512, 'Dokumentasi sangat kurang dan sulit digunakan', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2512, 'Tidak membuat catatan atau dokumentasi', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2513, 'Pengecekan rutin sangat lengkap dan terdapat tindakan pencegahan tambahan', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2513, 'Pengecekan rutin lengkap dan masalah diselesaikan lebih cepat', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2513, 'Pengecekan rutin dilakukan dan masalah diselesaikan sesuai kebutuhan', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2513, 'Pengecekan hampir lengkap dan masalah kecil dapat diselesaikan', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2513, 'Pengecekan dilakukan tetapi beberapa bagian terlewat', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2513, 'Pengecekan tidak konsisten dan masalah sering terlambat diketahui', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2513, 'Pengecekan sangat kurang', '40.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2513, 'Tidak melakukan pengecekan aplikasi', '0.00', '8', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2514, '<H+2', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2514, 'H+3', '110.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2514, 'H+4', '100.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2514, 'H+5', '90.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2514, 'H+6', '80.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2514, 'H+7', '70.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2514, '>H+7', '50.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2515, '<H+2', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2515, 'H+3', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2515, 'H+4', '90.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2515, 'H+5', '80.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2515, 'H+6', '70.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2515, 'H+7', '60.00', '6', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2515, '>H+7', '50.00', '7', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2516, '<H+2', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2516, 'H+2', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2516, 'H+3', '85.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2516, 'H+4', '50.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2516, '>H+4', '40.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2517, '<H+2', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2517, 'H+2', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2517, 'H+3', '85.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2517, 'H+4', '50.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2517, '>H+4', '20.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2519, '100% taat ', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2519, 'Ada pelanggaran', '0.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2520, '100% hadir', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2520, '<2X tdk hadir dgn Izin', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2520, '1-2 tdk hadir', '90.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2520, '3-4 tdk hadir', '80.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2520, '>4 tdk hadir', '0.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2521, '100% hadir', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2521, '<2 tdk hadir dgn izin', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2521, '1-2 tdk hadir', '90.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2521, '3-4 tdk hadir', '80.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2521, '>4 tdk hadir', '0.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2522, '100% Tepat Waktu', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2522, '1-2 X Terlambat', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2522, '3-4 X Terlambat', '90.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2522, '>4 X Terlambat', '50.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2523, 'Nilai skill4', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2523, 'Nilai skill 3,5', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2523, 'Nilai skill 3', '90.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2523, 'Nilai skill <3', '80.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2523, 'Nilai skill <1', '0.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2524, 'Nilai skill 4', '115.00', '1', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2524, 'Nilai skill 3,5', '100.00', '2', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2524, 'Nilai skill 3', '90.00', '3', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2524, 'Nilai skill <3', '80.00', '4', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);
    INSERT INTO `tb_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`, `is_edited`, `edited_by`, `edited_at`, `original_keterangan`, `original_nilai`) VALUES
    (v_tb_hows_2524, 'Nilai skill <1', '0.00', '5', '2026-08-27 01:05:17', '0', NULL, NULL, NULL, NULL);

    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1998, 'Seluruh target selesai dan terdapat tambahan pengembangan yang bermanfaat', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1998, 'Seluruh target selesai lebih cepat atau hasil melebihi target', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1998, 'Seluruh target yang ditetapkan selesai sesuai waktu', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1998, 'Penyelesaian mencapai 90-99%', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1998, 'Penyelesaian mencapai 80-89%', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1998, 'Penyelesaian mencapai 60-79%', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1998, 'Penyelesaian mencapai 40-59%', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1998, 'Belum menghasilkan hasil yang dapat digunakan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1999, 'Aplikasi berjalan sangat baik, tidak ada masalah utama dan terdapat peningkatan tambahan', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1999, 'Aplikasi berjalan baik dan hanya terdapat masalah kecil', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1999, 'Aplikasi dapat digunakan sesuai kebutuhan tanpa masalah yang menghambat pekerjaan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1999, 'Terdapat masalah namun dapat segera diselesaikan', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1999, 'Masih terdapat beberapa bagian yang perlu diperbaiki', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1999, 'Aplikasi dapat digunakan tetapi masih sering mengalami masalah', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1999, 'Aplikasi belum siap digunakan secara penuh', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_1999, 'Aplikasi tidak dapat digunakan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2000, 'Dokumentasi lengkap, panduan tersedia dan sudah dilakukan penjelasan kepada pengguna', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2000, 'Dokumentasi dan panduan 100% lengkap', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2000, 'Seluruh fungsi utama sudah memiliki dokumentasi', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2000, 'Dokumentasi mencapai 90%', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2000, 'Dokumentasi mencapai 80%', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2000, 'Dokumentasi mencapai 60%', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2000, 'Dokumentasi kurang dari 60%', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2000, 'Tidak ada dokumentasi', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2001, 'Seluruh target selesai dan terdapat tambahan perbaikan yang memberikan manfaat nyata', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2001, 'Seluruh target selesai dengan hasil melebihi kebutuhan awal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2001, 'Seluruh target pekerjaan selesai sesuai rencana', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2001, '90-99% selesai', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2001, '80-89% selesai', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2001, '60-79% selesai', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2001, '40-59% selesai', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2001, 'Tidak terdapat hasil pekerjaan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2002, 'Seluruh masalah tertangani sangat cepat dan masalah yang sama tidak terjadi kembali', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2002, 'Hampir seluruh masalah selesai lebih cepat dari target', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2002, 'Seluruh masalah selesai sesuai waktu yang ditentukan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2002, 'Sebagian kecil penyelesaian terlambat', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2002, 'Beberapa pekerjaan terlambat', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2002, 'Banyak pekerjaan melewati waktu penyelesaian', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2002, 'Penanganan masalah sering terlambat', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2002, 'Masalah tidak ditangani', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2003, 'Laporan sangat lengkap, tepat waktu, dan berisi tindak lanjut yang jelas', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2003, 'Laporan lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2003, 'Laporan lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2003, 'Laporan hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2003, 'Laporan cukup lengkap tetapi masih perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2003, 'Laporan kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2003, 'Laporan sangat kurang dan sulit digunakan sebagai acuan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2003, 'Tidak membuat laporan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2004, 'Tidak ada gangguan utama dan terdapat peningkatan kualitas aplikasi', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2004, 'Tidak ada gangguan utama selama periode penilaian', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2004, 'Maksimal terdapat 1 gangguan dan dapat segera diselesaikan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2004, 'Terdapat gangguan kecil tetapi tidak menghambat pekerjaan', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2004, 'Terdapat beberapa gangguan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2004, 'Gangguan cukup sering terjadi', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2004, 'Gangguan sering menghambat pekerjaan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2004, 'Aplikasi tidak dapat digunakan dengan baik', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2005, 'Laporan sangat lengkap, tepat waktu, dan berisi tindak lanjut yang jelas', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2005, 'Laporan lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2005, 'Laporan lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2005, 'Laporan hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2005, 'Laporan cukup lengkap tetapi masih perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2005, 'Laporan kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2005, 'Laporan sangat kurang dan sulit digunakan sebagai acuan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2005, 'Tidak membuat laporan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2006, 'Seluruh pemeliharaan terlaksana dan terdapat tambahan tindakan pencegahan', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2006, 'Seluruh pemeliharaan selesai sesuai jadwal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2006, 'Minimal 95% pekerjaan pemeliharaan selesai', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2006, '90-94% selesai', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2006, '80-89% selesai', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2006, '60-79% selesai', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2006, 'Kurang dari 60% selesai', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2006, 'Tidak dilakukan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2007, 'Laporan sangat lengkap, tepat waktu, dan berisi tindak lanjut yang jelas', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2007, 'Laporan lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2007, 'Laporan lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2007, 'Laporan hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2007, 'Laporan cukup lengkap tetapi masih perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2007, 'Laporan kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2007, 'Laporan sangat kurang dan sulit digunakan sebagai acuan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2007, 'Tidak membuat laporan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2008, 'Kehadiran, ketepatan waktu, briefing, izin/cuti, dan kegiatan wajib terlaksana sangat baik tanpa pelanggaran', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2008, 'Kedisiplinan kerja sangat baik dan melebihi standar yang ditetapkan', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2008, 'Kehadiran dan kedisiplinan kerja sesuai aturan perusahaan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2008, 'Terdapat kekurangan kecil tetapi tidak mengganggu pekerjaan', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2008, 'Beberapa aturan kedisiplinan belum konsisten dijalankan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2008, 'Kedisiplinan kurang dan perlu banyak perbaikan', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2008, 'Kedisiplinan sering tidak sesuai aturan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2008, 'Tidak menjalankan aturan kehadiran dan kedisiplinan kerja', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2009, 'Seluruh pekerjaan selesai sangat baik dan masalah yang sama tidak berulang', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2009, 'Hampir seluruh pekerjaan selesai lebih cepat', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2009, 'Seluruh pekerjaan selesai sesuai target', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2009, '90% pekerjaan sesuai target', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2009, '80% pekerjaan sesuai target', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2009, '60% pekerjaan sesuai target', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2009, 'Kurang dari 60% pekerjaan sesuai target', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_whats` (`id_what`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_whats_2009, 'Tidak menjalankan pekerjaan', '0.00', '8', '2026-08-27 01:05:17');

    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4013, 'Seluruh pekerjaan selesai lebih cepat dan terdapat tambahan hasil yang bermanfaat', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4013, 'Seluruh pekerjaan selesai tepat waktu tanpa pekerjaan tertunda', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4013, 'Minimal 95% pekerjaan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4013, '85-94% pekerjaan selesai sesuai jadwal', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4013, '75-84% pekerjaan selesai sesuai jadwal', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4013, '60-74% pekerjaan selesai sesuai jadwal', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4013, 'Kurang dari 60% pekerjaan selesai', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4013, 'Tidak terdapat perkembangan pekerjaan yang terukur', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4014, 'Pengecekan lengkap, tidak terdapat masalah utama dan terdapat peningkatan tambahan', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4014, 'Pengecekan lengkap dan hanya ditemukan masalah kecil', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4014, 'Seluruh bagian utama sudah diperiksa dan dapat digunakan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4014, 'Terdapat masalah tetapi seluruhnya dapat diselesaikan tepat waktu', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4014, 'Masih terdapat beberapa perbaikan kecil', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4014, 'Pengecekan dilakukan tetapi masih terdapat masalah berulang', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4014, 'Pengecekan belum lengkap', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4014, 'Tidak dilakukan pengecekan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4015, 'Seluruh bagian HRIS saling mendukung sangat baik dan data sesuai kebutuhan perusahaan', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4015, 'Integrasi data berjalan baik dan melebihi kebutuhan awal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4015, 'Seluruh bagian utama HRIS saling mendukung dan data sesuai kebutuhan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4015, 'Sebagian kecil data atau alur perlu penyesuaian tetapi tidak menghambat pekerjaan', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4015, 'Beberapa bagian HRIS masih perlu disesuaikan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4015, 'Integrasi dilakukan tetapi masih sering terdapat masalah data atau alur', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4015, 'Integrasi belum lengkap dan banyak bagian belum saling mendukung', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4015, 'Tidak ada integrasi atau kesesuaian data yang dapat digunakan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4016, 'Catatan pekerjaan, dokumentasi, dan laporan lengkap, tepat waktu, serta mudah ditindaklanjuti', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4016, 'Dokumentasi lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4016, 'Dokumentasi lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4016, 'Dokumentasi hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4016, 'Dokumentasi cukup lengkap tetapi perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4016, 'Dokumentasi kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4016, 'Dokumentasi sangat kurang dan sulit digunakan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4016, 'Tidak membuat catatan atau dokumentasi', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4017, 'Kebutuhan pengguna dipahami sangat baik dan menghasilkan solusi yang lebih bermanfaat', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4017, 'Kebutuhan pengguna dipahami lengkap lebih cepat dari target', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4017, 'Kebutuhan pengguna dipahami sesuai kebutuhan pekerjaan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4017, 'Kebutuhan pengguna hampir lengkap dipahami', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4017, 'Sebagian kebutuhan pengguna masih perlu diperjelas', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4017, 'Analisis kebutuhan kurang lengkap', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4017, 'Analisis kebutuhan sering tidak sesuai dengan masalah pengguna', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4017, 'Tidak melakukan analisis kebutuhan pengguna', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4018, 'Pekerjaan dibuat dan diperiksa sangat baik serta memberikan manfaat tambahan', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4018, 'Pekerjaan selesai lebih cepat dan hasil melebihi kebutuhan awal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4018, 'Pekerjaan dibuat, diperiksa, dan dapat digunakan sesuai kebutuhan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4018, 'Pekerjaan hampir sesuai dan hanya perlu perbaikan kecil', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4018, 'Pekerjaan dapat digunakan tetapi masih perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4018, 'Pekerjaan kurang stabil atau sering perlu perbaikan ulang', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4018, 'Pekerjaan belum dapat digunakan dengan baik', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4018, 'Tidak ada hasil pekerjaan yang dapat digunakan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4019, 'Seluruh laporan masalah tertangani sangat cepat dan masalah tidak berulang', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4019, 'Hampir seluruh masalah selesai lebih cepat dari target', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4019, 'Seluruh masalah selesai sesuai target dan aplikasi dapat digunakan kembali', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4019, 'Sebagian kecil penyelesaian terlambat tetapi dampak dapat dikendalikan', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4019, 'Beberapa masalah terlambat diselesaikan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4019, 'Banyak masalah melewati waktu penyelesaian', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4019, 'Penanganan masalah sering terlambat atau kurang tepat', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4019, 'Masalah tidak ditangani', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4020, 'Catatan pekerjaan, dokumentasi, dan laporan lengkap, tepat waktu, serta mudah ditindaklanjuti', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4020, 'Dokumentasi lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4020, 'Dokumentasi lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4020, 'Dokumentasi hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4020, 'Dokumentasi cukup lengkap tetapi perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4020, 'Dokumentasi kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4020, 'Dokumentasi sangat kurang dan sulit digunakan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4020, 'Tidak membuat catatan atau dokumentasi', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4021, 'Pengecekan rutin sangat lengkap dan terdapat tindakan pencegahan tambahan', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4021, 'Pengecekan rutin lengkap dan masalah diselesaikan lebih cepat', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4021, 'Pengecekan rutin dilakukan dan masalah diselesaikan sesuai kebutuhan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4021, 'Pengecekan hampir lengkap dan masalah kecil dapat diselesaikan', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4021, 'Pengecekan dilakukan tetapi beberapa bagian terlewat', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4021, 'Pengecekan tidak konsisten dan masalah sering terlambat diketahui', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4021, 'Pengecekan sangat kurang', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4021, 'Tidak melakukan pengecekan aplikasi', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4022, 'Perbaikan performa sangat baik dan memberi peningkatan nyata bagi pengguna', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4022, 'Perbaikan selesai lebih cepat dan hasil melebihi kebutuhan', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4022, 'Bagian aplikasi yang lambat atau mengganggu berhasil diperbaiki sesuai target', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4022, 'Sebagian kecil perbaikan masih perlu penyempurnaan', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4022, 'Perbaikan dilakukan tetapi beberapa gangguan masih muncul', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4022, 'Perbaikan kurang efektif dan masalah cukup sering berulang', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4022, 'Perbaikan tidak menyelesaikan masalah utama', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4022, 'Tidak melakukan perbaikan performa', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4023, 'Catatan pekerjaan, dokumentasi, dan laporan lengkap, tepat waktu, serta mudah ditindaklanjuti', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4023, 'Dokumentasi lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4023, 'Dokumentasi lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4023, 'Dokumentasi hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4023, 'Dokumentasi cukup lengkap tetapi perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4023, 'Dokumentasi kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4023, 'Dokumentasi sangat kurang dan sulit digunakan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4023, 'Tidak membuat catatan atau dokumentasi', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4024, 'Pengecekan aplikasi dan data sesuai jadwal, lengkap, dan disertai tindakan pencegahan tambahan', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4024, 'Pengecekan selesai lebih cepat dan lengkap', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4024, 'Pengecekan aplikasi dan data selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4024, 'Pengecekan hampir sesuai jadwal dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4024, 'Pengecekan cukup berjalan tetapi beberapa jadwal terlewat', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4024, 'Pengecekan tidak konsisten', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4024, 'Pengecekan sangat kurang', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4024, 'Tidak melakukan pengecekan sesuai jadwal', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4025, 'Seluruh data penting memiliki salinan cadangan yang terverifikasi dan siap digunakan', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4025, 'Backup lengkap dan verifikasi selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4025, 'Data penting memiliki backup dan dapat digunakan bila dibutuhkan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4025, 'Backup tersedia dengan kekurangan kecil yang tidak menghambat pemulihan', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4025, 'Backup tersedia tetapi belum sepenuhnya lengkap', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4025, 'Backup kurang konsisten atau jarang diverifikasi', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4025, 'Backup sangat kurang dan berisiko tidak dapat digunakan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4025, 'Tidak memastikan salinan cadangan data penting', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4026, 'Catatan pekerjaan, dokumentasi, dan laporan lengkap, tepat waktu, serta mudah ditindaklanjuti', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4026, 'Dokumentasi lengkap dan selesai lebih cepat dari jadwal', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4026, 'Dokumentasi lengkap dan selesai sesuai jadwal', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4026, 'Dokumentasi hampir lengkap dan hanya ada kekurangan kecil', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4026, 'Dokumentasi cukup lengkap tetapi perlu beberapa perbaikan', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4026, 'Dokumentasi kurang lengkap atau sering terlambat', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4026, 'Dokumentasi sangat kurang dan sulit digunakan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4026, 'Tidak membuat catatan atau dokumentasi', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4027, 'Kehadiran dan ketepatan waktu sangat baik tanpa pelanggaran', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4027, 'Kehadiran dan ketepatan waktu melebihi standar yang ditetapkan', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4027, 'Kehadiran dan ketepatan waktu sesuai aturan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4027, 'Terdapat kekurangan kecil pada ketepatan waktu', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4027, 'Ketepatan waktu belum konsisten', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4027, 'Kehadiran atau ketepatan waktu sering perlu diperbaiki', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4027, 'Kehadiran atau ketepatan waktu sering tidak sesuai aturan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4027, 'Tidak menjalankan aturan kehadiran dan ketepatan waktu', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4028, 'Selalu mengikuti briefing dengan disiplin dan aktif mendukung kelancaran informasi', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4028, 'Mengikuti briefing sangat baik dan melebihi standar kehadiran', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4028, 'Mengikuti briefing sesuai jadwal yang berlaku', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4028, 'Hampir seluruh briefing diikuti dengan baik', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4028, 'Beberapa briefing tidak diikuti atau terlambat', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4028, 'Briefing sering tidak diikuti dengan konsisten', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4028, 'Briefing sangat sering terlewat', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4028, 'Tidak mengikuti briefing', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4029, 'Seluruh izin/cuti mengikuti prosedur dengan lengkap, cepat, dan terdokumentasi', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4029, 'Prosedur izin/cuti dijalankan sangat baik dan lebih rapi dari standar', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4029, 'Prosedur izin/cuti dijalankan sesuai aturan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4029, 'Ada kekurangan kecil tetapi prosedur tetap terpenuhi', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4029, 'Beberapa prosedur perlu diperbaiki', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4029, 'Prosedur izin/cuti kurang konsisten dijalankan', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4029, 'Prosedur izin/cuti sering tidak sesuai aturan', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4029, 'Tidak menjalankan prosedur izin/cuti', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4030, 'Seluruh kegiatan wajib diikuti sangat baik dan aktif mendukung pelaksanaan kegiatan', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4030, 'Kegiatan wajib diikuti sangat baik dan melebihi standar', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4030, 'Kegiatan wajib perusahaan diikuti sesuai aturan', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4030, 'Hampir seluruh kegiatan wajib diikuti dengan baik', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4030, 'Beberapa kegiatan wajib belum konsisten diikuti', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4030, 'Kegiatan wajib sering tidak diikuti', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4030, 'Kegiatan wajib sangat sering terlewat', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4030, 'Tidak mengikuti kegiatan wajib perusahaan', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4031, 'Seluruh laporan perangkat ditangani sangat cepat, tepat, dan masalah tidak berulang', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4031, 'Hampir seluruh laporan perangkat selesai lebih cepat dari target', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4031, 'Laporan perangkat ditangani cepat dan tepat sesuai target', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4031, 'Sebagian kecil penanganan terlambat tetapi perangkat tetap dapat digunakan', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4031, 'Beberapa penanganan perangkat terlambat', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4031, 'Banyak penanganan perangkat melewati target', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4031, 'Penanganan perangkat sering terlambat atau kurang tepat', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4031, 'Tidak menangani laporan masalah perangkat', '0.00', '8', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4032, 'Perangkat kembali digunakan dengan baik dan catatan perbaikan lengkap serta mudah ditindaklanjuti', '115.00', '1', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4032, 'Perangkat kembali digunakan lebih cepat dan dokumentasi lengkap', '110.00', '2', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4032, 'Perangkat dapat digunakan kembali dan pekerjaan perbaikan tercatat sesuai target', '100.00', '3', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4032, 'Perangkat dapat digunakan dengan catatan perbaikan yang hampir lengkap', '90.00', '4', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4032, 'Perangkat dapat digunakan tetapi catatan perbaikan perlu dilengkapi', '80.00', '5', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4032, 'Perangkat dapat digunakan sebagian atau dokumentasi kurang lengkap', '60.00', '6', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4032, 'Hasil perbaikan kurang jelas dan catatan sangat minim', '40.00', '7', '2026-08-27 01:05:17');
    INSERT INTO `tbsim_indikator_hows` (`id_how`, `keterangan`, `nilai`, `urutan`, `created_at`) VALUES
    (v_tbsim_hows_4032, 'Tidak memastikan perangkat dapat digunakan atau tidak mencatat perbaikan', '0.00', '8', '2026-08-27 01:05:17');

    SELECT COUNT(*) INTO v_check_count FROM `tb_kpi` WHERE id_user = v_target_user_id;
    IF v_check_count <> 6 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Guard gagal: jumlah tb_kpi tidak sesuai hasil export.';
    END IF;
    SELECT COUNT(*) INTO v_check_count FROM `tb_whats` WHERE id_user = v_target_user_id;
    IF v_check_count <> 11 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Guard gagal: jumlah tb_whats tidak sesuai hasil export.';
    END IF;
    SELECT COUNT(*) INTO v_check_count FROM `tb_hows` WHERE id_user = v_target_user_id;
    IF v_check_count <> 19 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Guard gagal: jumlah tb_hows tidak sesuai hasil export.';
    END IF;
    SELECT COUNT(*) INTO v_check_count FROM `tbsim_kpi` WHERE id_user = v_target_user_id;
    IF v_check_count <> 6 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Guard gagal: jumlah tbsim_kpi tidak sesuai hasil export.';
    END IF;
    SELECT COUNT(*) INTO v_check_count FROM `tbsim_whats` WHERE id_user = v_target_user_id;
    IF v_check_count <> 12 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Guard gagal: jumlah tbsim_whats tidak sesuai hasil export.';
    END IF;
    SELECT COUNT(*) INTO v_check_count FROM `tbsim_hows` WHERE id_user = v_target_user_id;
    IF v_check_count <> 20 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Guard gagal: jumlah tbsim_hows tidak sesuai hasil export.';
    END IF;
    SELECT COUNT(*) INTO v_check_count FROM `tb_indikator_whats` iw JOIN `tb_whats` w ON w.id_what = iw.id_what WHERE w.id_user = v_target_user_id;
    IF v_check_count <> 78 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Guard gagal: jumlah tb_indikator_whats tidak sesuai hasil export.';
    END IF;
    SELECT COUNT(*) INTO v_check_count FROM `tb_indikator_hows` ih JOIN `tb_hows` h ON h.id_how = ih.id_how WHERE h.id_user = v_target_user_id;
    IF v_check_count <> 122 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Guard gagal: jumlah tb_indikator_hows tidak sesuai hasil export.';
    END IF;
    SELECT COUNT(*) INTO v_check_count FROM `tbsim_indikator_whats` iw JOIN `tbsim_whats` w ON w.id_what = iw.id_what WHERE w.id_user = v_target_user_id;
    IF v_check_count <> 96 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Guard gagal: jumlah tbsim_indikator_whats tidak sesuai hasil export.';
    END IF;
    SELECT COUNT(*) INTO v_check_count FROM `tbsim_indikator_hows` ih JOIN `tbsim_hows` h ON h.id_how = ih.id_how WHERE h.id_user = v_target_user_id;
    IF v_check_count <> 160 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Guard gagal: jumlah tbsim_indikator_hows tidak sesuai hasil export.';
    END IF;

    COMMIT;

    SELECT v_target_user_id AS user_id;
END $$

DELIMITER ;

CALL insert_kpi_user_171_auto_increment_production();
DROP PROCEDURE IF EXISTS insert_kpi_user_171_auto_increment_production;
