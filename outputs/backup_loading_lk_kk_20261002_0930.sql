-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: kiucoid_karismaerp_local
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `tb_loading_lk`
--

LOCK TABLES `tb_loading_lk` WRITE;
/*!40000 ALTER TABLE `tb_loading_lk` DISABLE KEYS */;
INSERT INTO `tb_loading_lk` (`id`, `kode`, `tgl`, `waktu_siap_loading`, `keterangan`, `pintu`, `waktu_do_selesai`, `waktu_cetak_do`, `waktu_mulai_siapkan`, `waktu_selesai_siapkan`, `nik_checker`, `nm_checker`, `waktu_mulai`, `waktu_selesai`, `progres`, `progres_siapkan`, `status`, `is_paused`, `is_paused_siapkan`, `paused_at_siapkan`, `total_pause_secs_siapkan`, `pernah_pause_siapkan`, `total_pause_secs`, `paused_at`, `pernah_pause`, `is_archived`, `archived_at`, `archived_by`, `created_by`, `created_role`, `created_at`, `updated_at`) VALUES (1,'LK2108260001','2026-08-21','2026-08-21 14:23:25','MD-1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'SIAP_LOADING',0,0,NULL,0,0,0,NULL,0,0,NULL,NULL,'admlog',NULL,'2026-08-21 14:23:25','2026-08-21 14:23:25'),(2,'LK2209260001','2026-09-22','2026-09-22 11:00:48','MLG',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'SIAP_LOADING',0,0,NULL,0,0,0,NULL,0,0,NULL,NULL,'Admin SC',NULL,'2026-09-22 11:00:48','2026-09-22 11:00:48'),(3,'LK2309260001','2026-09-23','2026-09-23 13:41:17','MD-2',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'SIAP_LOADING',0,0,NULL,0,0,0,NULL,0,0,NULL,NULL,'admlog',NULL,'2026-09-23 13:41:17','2026-09-23 13:41:17'),(4,'LK2409260001','2026-09-24','2026-09-24 15:23:30','P-2',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'SIAP_LOADING',0,0,NULL,0,0,0,NULL,0,0,NULL,NULL,'Admin SC',NULL,'2026-09-24 15:23:30','2026-09-24 15:23:30');
/*!40000 ALTER TABLE `tb_loading_lk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tb_loading_kk`
--

LOCK TABLES `tb_loading_kk` WRITE;
/*!40000 ALTER TABLE `tb_loading_kk` DISABLE KEYS */;
INSERT INTO `tb_loading_kk` (`id`, `kode`, `tgl`, `waktu_siap_loading`, `keterangan`, `pintu`, `waktu_do_selesai`, `waktu_cetak_do`, `waktu_mulai_siapkan`, `waktu_selesai_siapkan`, `nik_checker`, `nm_checker`, `waktu_mulai`, `waktu_selesai`, `progres`, `progres_siapkan`, `status`, `is_paused`, `total_pause_secs`, `is_paused_siapkan`, `paused_at_siapkan`, `total_pause_secs_siapkan`, `pernah_pause_siapkan`, `paused_at`, `pernah_pause`, `is_archived`, `archived_at`, `archived_by`, `created_by`, `created_at`, `updated_at`) VALUES (1,'KK2108260001','2026-08-21','2026-08-21 14:23:20','JLS',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'SIAP_LOADING',0,0,0,NULL,0,0,NULL,0,0,NULL,NULL,'admlog','2026-08-21 14:23:20','2026-08-21 14:23:20'),(2,'KK2309260001','2026-09-23','2026-09-23 13:41:22','PRB',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'SIAP_LOADING',0,0,0,NULL,0,0,NULL,0,0,NULL,NULL,'admlog','2026-09-23 13:41:22','2026-09-23 13:41:22'),(3,'KK2309260002','2026-09-23','2026-09-23 13:42:15','JBR',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'SIAP_LOADING',0,0,0,NULL,0,0,NULL,0,0,NULL,NULL,'Admin SC','2026-09-23 13:42:15','2026-09-23 13:42:15');
/*!40000 ALTER TABLE `tb_loading_kk` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-02  9:23:10
