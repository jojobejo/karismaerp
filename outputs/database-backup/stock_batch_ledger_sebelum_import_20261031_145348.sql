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
-- Table structure for table `tberp_stock_batch`
--

DROP TABLE IF EXISTS `tberp_stock_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tberp_stock_batch` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `kd_barang` varchar(50) DEFAULT NULL,
  `gudang_id` varchar(30) DEFAULT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `qty_on_hand` decimal(15,3) DEFAULT '0.000',
  `qty_reserved` decimal(15,3) DEFAULT '0.000',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_batch` (`kd_barang`,`gudang_id`,`no_lot`,`expired_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1816 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tberp_stock_batch`
--

LOCK TABLES `tberp_stock_batch` WRITE;
/*!40000 ALTER TABLE `tberp_stock_batch` DISABLE KEYS */;
/*!40000 ALTER TABLE `tberp_stock_batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tberp_stock_ledger`
--

DROP TABLE IF EXISTS `tberp_stock_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tberp_stock_ledger` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `kd_barang` varchar(50) DEFAULT NULL,
  `gudang_id` varchar(30) DEFAULT NULL,
  `no_lot` varchar(50) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `qty` decimal(15,3) DEFAULT NULL,
  `tipe` enum('SALDO_AWAL','IN','RESERVE','RELEASE','OUT','RBELI','RJUAL','MUTASI','ADJIN','ADJOUT') DEFAULT NULL,
  `ref_no` varchar(50) DEFAULT NULL,
  `ref_type` varchar(30) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tberp_stock_ledger`
--

LOCK TABLES `tberp_stock_ledger` WRITE;
/*!40000 ALTER TABLE `tberp_stock_ledger` DISABLE KEYS */;
/*!40000 ALTER TABLE `tberp_stock_ledger` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-31 14:53:48
