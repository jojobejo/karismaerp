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
-- Table structure for table `tbrp_spr_header`
--

DROP TABLE IF EXISTS `tbrp_spr_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbrp_spr_header` (
  `id_spr` int unsigned NOT NULL AUTO_INCREMENT,
  `no_spr` varchar(30) NOT NULL,
  `tipe_retur` enum('biasa','replace','service','konsinyasi') NOT NULL DEFAULT 'biasa',
  `is_jagung` tinyint(1) NOT NULL DEFAULT '0',
  `tanggal` date NOT NULL,
  `kd_customer` varchar(30) DEFAULT NULL,
  `nama_customer` varchar(200) DEFAULT NULL,
  `alamat` varchar(300) DEFAULT NULL,
  `nama_sales` varchar(150) DEFAULT NULL,
  `catatan` text,
  `status` enum('draft','diajukan','menunggu_kadepub','diverifikasi_koor','dicek_admretur','disetujui_kadep','selesai','ditolak') NOT NULL DEFAULT 'draft',
  `mngsc_by` varchar(150) DEFAULT NULL,
  `mngsc_at` datetime DEFAULT NULL,
  `mngsc_catatan` text,
  `kadepub_by` varchar(150) DEFAULT NULL,
  `kadepub_at` datetime DEFAULT NULL,
  `kadepub_catatan` text,
  `admretur_by` varchar(150) DEFAULT NULL,
  `admretur_at` datetime DEFAULT NULL,
  `admretur_catatan` text,
  `kadep_sc_by` varchar(150) DEFAULT NULL,
  `kadep_sc_at` datetime DEFAULT NULL,
  `kadep_sc_catatan` text,
  `logistik_by` varchar(150) DEFAULT NULL,
  `logistik_at` datetime DEFAULT NULL,
  `logistik_catatan` text,
  `create_by` varchar(150) DEFAULT NULL,
  `create_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_by` varchar(150) DEFAULT NULL,
  `update_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `admin_stock_at` datetime DEFAULT NULL,
  `admin_stock_by` varchar(150) DEFAULT NULL,
  `admin_stock_catatan` text,
  `koor_sc_at` datetime DEFAULT NULL,
  `koor_sc_by` varchar(150) DEFAULT NULL,
  `koor_sc_catatan` text,
  PRIMARY KEY (`id_spr`),
  UNIQUE KEY `uq_no_spr` (`no_spr`),
  KEY `idx_status` (`status`),
  KEY `idx_create_by` (`create_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbrp_spr_header`
--

LOCK TABLES `tbrp_spr_header` WRITE;
/*!40000 ALTER TABLE `tbrp_spr_header` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbrp_spr_header` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbrp_spr_detail`
--

DROP TABLE IF EXISTS `tbrp_spr_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbrp_spr_detail` (
  `id_spr_detail` int unsigned NOT NULL AUTO_INCREMENT,
  `id_spr` int unsigned NOT NULL,
  `no_urut` tinyint unsigned NOT NULL DEFAULT '1',
  `kd_barang` varchar(50) DEFAULT NULL,
  `nama_barang` varchar(250) DEFAULT NULL,
  `no_faktur` varchar(80) DEFAULT NULL,
  `no_batch` varchar(80) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL DEFAULT '0.00',
  `qty` decimal(12,3) DEFAULT '0.000',
  `alasan_brg_bermasalah` tinyint(1) NOT NULL DEFAULT '0',
  `alasan_brg_bermasalah_opt` enum('','replace','not_replace') NOT NULL DEFAULT '',
  `alasan_expired` tinyint(1) NOT NULL DEFAULT '0',
  `alasan_expired_opt` enum('','replace','not_replace') NOT NULL DEFAULT '',
  `alasan_tidak_laku` tinyint(1) NOT NULL DEFAULT '0',
  `alasan_tes_market` tinyint(1) NOT NULL DEFAULT '0',
  `alasan_bad_debt` tinyint(1) NOT NULL DEFAULT '0',
  `alasan_harga_tidak_sesuai` tinyint(1) NOT NULL DEFAULT '0',
  `alasan_spr_intern` tinyint(1) NOT NULL DEFAULT '0',
  `alasan_lainlain` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id_spr_detail`),
  KEY `idx_id_spr` (`id_spr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbrp_spr_detail`
--

LOCK TABLES `tbrp_spr_detail` WRITE;
/*!40000 ALTER TABLE `tbrp_spr_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbrp_spr_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbrp_retur_penjualan_header`
--

DROP TABLE IF EXISTS `tbrp_retur_penjualan_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbrp_retur_penjualan_header` (
  `id_retur` int unsigned NOT NULL AUTO_INCREMENT,
  `no_retur` varchar(40) NOT NULL,
  `tipe_retur` enum('biasa','replace','service','konsinyasi') NOT NULL DEFAULT 'biasa',
  `total_nilai_retur` decimal(15,2) NOT NULL DEFAULT '0.00',
  `sisa_saldo_retur` decimal(15,2) NOT NULL DEFAULT '0.00',
  `id_spr` int unsigned NOT NULL,
  `no_spr` varchar(30) NOT NULL,
  `gudang_id` int DEFAULT NULL,
  `tanggal_retur` date NOT NULL,
  `kd_customer` varchar(30) DEFAULT NULL,
  `nama_customer` varchar(200) DEFAULT NULL,
  `alamat` varchar(300) DEFAULT NULL,
  `nama_sales` varchar(150) DEFAULT NULL,
  `catatan_logistik` text,
  `status_retur` enum('menunggu_verifikasi','retur_menunggu_kadepub','retur_menunggu_mngacc','retur_menunggu_mngsc','retur_menunggu_mngse','retur_menunggu_kadepsc','retur_menunggu_dirop','retur_menunggu_dirut','menunggu_collection','menunggu_kasir','selesai','ditolak') NOT NULL DEFAULT 'menunggu_verifikasi',
  `is_revisi` tinyint(1) NOT NULL DEFAULT '0',
  `admretur_by_retur` varchar(150) DEFAULT NULL,
  `admretur_at_retur` datetime DEFAULT NULL,
  `catatan_admretur` text,
  `collection_by` varchar(150) DEFAULT NULL,
  `collection_at` datetime DEFAULT NULL,
  `catatan_collection` text,
  `no_faktur_potong` varchar(500) DEFAULT NULL,
  `create_by_retur` varchar(150) DEFAULT NULL,
  `create_at_retur` datetime DEFAULT CURRENT_TIMESTAMP,
  `update_by_retur` varchar(150) DEFAULT NULL,
  `update_at_retur` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `kasir_by` varchar(150) DEFAULT NULL,
  `kasir_at` datetime DEFAULT NULL,
  `catatan_kasir` text,
  `kadepub_by_retur` varchar(150) DEFAULT NULL,
  `kadepub_at_retur` datetime DEFAULT NULL,
  `catatan_kadepub_retur` text,
  `mngacc_by_retur` varchar(150) DEFAULT NULL,
  `mngacc_at_retur` datetime DEFAULT NULL,
  `catatan_mngacc_retur` text,
  `koorsc_by_retur` varchar(150) DEFAULT NULL,
  `koorsc_at_retur` datetime DEFAULT NULL,
  `catatan_koorsc_retur` text,
  `mngse_by_retur` varchar(150) DEFAULT NULL,
  `mngse_at_retur` datetime DEFAULT NULL,
  `catatan_mngse_retur` text,
  `kadepsc_by_retur` varchar(150) DEFAULT NULL,
  `kadepsc_at_retur` datetime DEFAULT NULL,
  `catatan_kadepsc_retur` text,
  `dirop_by_retur` varchar(150) DEFAULT NULL,
  `dirop_at_retur` datetime DEFAULT NULL,
  `catatan_dirop_retur` text,
  `dirut_by_retur` varchar(150) DEFAULT NULL,
  `dirut_at_retur` datetime DEFAULT NULL,
  `catatan_dirut_retur` text,
  `mngsc_by_retur` varchar(150) DEFAULT NULL,
  `mngsc_at_retur` datetime DEFAULT NULL,
  `catatan_mngsc_retur` text,
  `admin_stock_at_retur` datetime DEFAULT NULL,
  `admin_stock_by_retur` varchar(150) DEFAULT NULL,
  `catatan_admin_stock` text,
  PRIMARY KEY (`id_retur`),
  UNIQUE KEY `uq_no_retur` (`no_retur`),
  KEY `idx_id_spr_retur` (`id_spr`),
  KEY `idx_status_retur` (`status_retur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbrp_retur_penjualan_header`
--

LOCK TABLES `tbrp_retur_penjualan_header` WRITE;
/*!40000 ALTER TABLE `tbrp_retur_penjualan_header` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbrp_retur_penjualan_header` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbrp_retur_penjualan_detail`
--

DROP TABLE IF EXISTS `tbrp_retur_penjualan_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbrp_retur_penjualan_detail` (
  `id_retur_detail` int unsigned NOT NULL AUTO_INCREMENT,
  `id_retur` int unsigned NOT NULL,
  `id_spr_detail` int unsigned DEFAULT NULL,
  `no_urut` tinyint unsigned NOT NULL DEFAULT '1',
  `kd_barang` varchar(50) DEFAULT NULL,
  `nama_barang` varchar(250) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `no_faktur` varchar(80) DEFAULT NULL,
  `no_batch` varchar(80) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `qty_retur` decimal(12,3) NOT NULL DEFAULT '0.000',
  `harga_satuan` decimal(15,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_retur_detail`),
  KEY `idx_id_retur` (`id_retur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbrp_retur_penjualan_detail`
--

LOCK TABLES `tbrp_retur_penjualan_detail` WRITE;
/*!40000 ALTER TABLE `tbrp_retur_penjualan_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbrp_retur_penjualan_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_konsinyasi_settlement`
--

DROP TABLE IF EXISTS `tb_konsinyasi_settlement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_konsinyasi_settlement` (
  `id_settlement` int NOT NULL AUTO_INCREMENT,
  `no_settlement` varchar(50) NOT NULL,
  `tanggal_settlement` date NOT NULL,
  `kd_suplier` varchar(50) NOT NULL,
  `nama_suplier` varchar(150) NOT NULL,
  `gudang_id` int NOT NULL DEFAULT '13',
  `id_so` int DEFAULT NULL,
  `no_so` varchar(50) DEFAULT NULL,
  `id_faktur` int DEFAULT NULL,
  `id_pembayaran` int DEFAULT NULL,
  `no_faktur` varchar(50) DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `id_lpb_asal` int DEFAULT NULL,
  `nomor_lpb_asal` varchar(50) DEFAULT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nama_barang` varchar(200) NOT NULL,
  `no_lot` varchar(100) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `qty_terjual` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_retur` decimal(15,3) NOT NULL DEFAULT '0.000',
  `qty_net` decimal(15,3) NOT NULL DEFAULT '0.000',
  `satuan` varchar(30) DEFAULT NULL,
  `hrg_jual` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tipe_pajak` enum('EXCLUDE','INCLUDE','NON_PPN') NOT NULL DEFAULT 'EXCLUDE',
  `hrg_satuan_input` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal_jual` decimal(15,2) NOT NULL DEFAULT '0.00',
  `hrg_beli_satuan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal_beli` decimal(15,2) NOT NULL DEFAULT '0.00',
  `ppn_persen` decimal(5,2) NOT NULL DEFAULT '0.00',
  `nilai_ppn` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_tagihan_beli` decimal(15,2) NOT NULL DEFAULT '0.00',
  `no_invoice_supplier` varchar(100) DEFAULT NULL,
  `tgl_invoice_supplier` date DEFAULT NULL,
  `status` enum('DI_KIOS','PENDING','LAKU','BILLED','CANCELLED') NOT NULL DEFAULT 'DI_KIOS',
  `id_jurnal_pembelian` bigint unsigned DEFAULT NULL,
  `settled_at` datetime DEFAULT NULL,
  `settled_by` varchar(50) DEFAULT NULL,
  `catatan` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_settlement`),
  KEY `idx_status` (`status`),
  KEY `idx_suplier` (`kd_suplier`),
  KEY `idx_so` (`no_so`),
  KEY `idx_faktur` (`no_faktur`),
  KEY `idx_pembayaran` (`id_pembayaran`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_konsinyasi_settlement`
--

LOCK TABLES `tb_konsinyasi_settlement` WRITE;
/*!40000 ALTER TABLE `tb_konsinyasi_settlement` DISABLE KEYS */;
INSERT INTO `tb_konsinyasi_settlement` VALUES (5,'KONS-SET-261012-0001','2026-10-12','PUPUK03','Pupuk Karya Polowijo, PT',13,5,'SO/121026/0001',5,NULL,'KINV1210260001','Agus Prasetyo',4,'2600002K','QROUN011','Round Up 486 SL 12 X 1 ltr','Q1214','2027-10-08',120.000,0.000,120.000,'PCS',80000.00,'EXCLUDE',0.00,9600000.00,0.00,0.00,0.00,0.00,0.00,NULL,NULL,'DI_KIOS',NULL,NULL,NULL,NULL,'2026-10-12 09:10:51'),(6,'KONS-SET-261012-0002','2026-10-12','PUPUK03','Pupuk Karya Polowijo, PT',13,5,'SO/121026/0001',5,3,'KINV1210260001-1','Agus Prasetyo',3,'2600001K','QROUN011','Round Up 486 SL 12 X 1 ltr','4455','2027-10-08',20.000,0.000,20.000,'PCS',80000.00,'INCLUDE',80000.00,1600000.00,72072.07,1441441.44,11.00,158558.56,1600000.00,'INV031','2026-10-12','BILLED',25,'2026-10-12 09:25:28','admin','','2026-10-12 09:10:51'),(7,'KONS-SET-261012-4822','2026-10-12','PUPUK03','Pupuk Karya Polowijo, PT',13,5,'SO/121026/0001',5,NULL,'KINV1210260001','Agus Prasetyo',3,'2600001K','QROUN011','Round Up 486 SL 12 X 1 ltr','4455','2027-10-08',100.000,0.000,100.000,'PCS',80000.00,'EXCLUDE',0.00,8000000.00,0.00,0.00,0.00,0.00,0.00,NULL,NULL,'DI_KIOS',NULL,NULL,NULL,'Sisa titipan di kios setelah pelunasan 20 PCS','2026-10-12 09:12:37');
/*!40000 ALTER TABLE `tb_konsinyasi_settlement` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tberp_stock_batch`
--

LOCK TABLES `tberp_stock_batch` WRITE;
/*!40000 ALTER TABLE `tberp_stock_batch` DISABLE KEYS */;
INSERT INTO `tberp_stock_batch` VALUES (1,'QROUN011','2','1123','2027-12-01',6000.000,0.000,'2026-10-07 12:51:11','2026-10-07 15:58:03'),(2,'QSPON08','2','1123','2026-10-08',1000.000,0.000,'2026-10-08 08:45:22','2026-10-08 08:45:22'),(3,'QROUN011','13','4455','2027-10-08',1880.000,0.000,'2026-10-08 10:02:31','2026-10-12 09:10:51'),(4,'QSPON081','13','Q4414','2026-10-08',2000.000,0.000,'2026-10-08 10:05:30','2026-10-08 10:05:30'),(5,'QROUN011','13','Q1214','2027-10-08',5880.000,0.000,'2026-10-08 10:05:30','2026-10-12 09:10:51'),(6,'QROUN011','2','11363','2027-08-01',1000.000,0.000,'2026-10-09 09:09:28','2026-10-09 09:22:57'),(7,'QROUN011','2','8899','2027-04-01',2000.000,0.000,'2026-10-09 09:10:16','2026-10-09 09:10:16'),(8,'QROUN011','15','4455','2027-10-08',20.000,0.000,'2026-10-12 09:25:28','2026-10-12 09:25:28');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tberp_stock_ledger`
--

LOCK TABLES `tberp_stock_ledger` WRITE;
/*!40000 ALTER TABLE `tberp_stock_ledger` DISABLE KEYS */;
INSERT INTO `tberp_stock_ledger` VALUES (1,'QROUN011','2','1123','2027-12-01',6000.000,'IN','SKPO071026PUPUK030002','PO_RECEIVED','2026-10-07 12:51:11'),(6,'QSPON08','2','1123','2026-10-08',1000.000,'IN','LPBM2610080001','LPB_MANUAL','2026-10-08 08:45:22'),(7,'QROUN011','13','4455','2026-10-08',2000.000,'IN','SKPO081026PUPUK030002','PO_RECEIVED','2026-10-08 10:02:31'),(8,'QSPON081','13','Q4414','2026-10-08',2000.000,'IN','SKPO081026PUPUK030001','PO_RECEIVED','2026-10-08 10:05:30'),(9,'QROUN011','13','Q1214','2026-10-08',6000.000,'IN','SKPO081026PUPUK030001','PO_RECEIVED','2026-10-08 10:05:30'),(14,'QROUN011','2','11363','2027-08-01',1000.000,'IN','LPBM2610090001','LPB_MANUAL','2026-10-09 09:09:28'),(15,'QROUN011','2','8899','2027-04-01',2000.000,'IN','LPBM2610090002','LPB_MANUAL','2026-10-09 09:10:16'),(18,'QROUN011','13','Q1214','2027-10-08',120.000,'RESERVE','SO/121026/0001','SALES_ORDER','2026-10-12 09:05:30'),(19,'QROUN011','13','4455','2027-10-08',120.000,'RESERVE','SO/121026/0001','SALES_ORDER','2026-10-12 09:05:30'),(20,'QROUN011','13','Q1214','2027-10-08',120.000,'OUT','SO/121026/0001','FAKTUR PENJUALAN','2026-10-12 09:10:51'),(21,'QROUN011','13','4455','2027-10-08',120.000,'OUT','SO/121026/0001','FAKTUR PENJUALAN','2026-10-12 09:10:51');
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

-- Dump completed on 2026-10-12 11:03:01
